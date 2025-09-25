<?php

namespace GFUnit\navision\syncorder;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CountryHelper;
use GFCommon\Model\Navision\CustomerObj;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\CustomerXML;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\OrderWS;
use GFCommon\Model\Navision\OrderXML;

class OrderSync
{

    /**
     * COMPANY STATES
     * - 0: Oprettet, ikke synkroniseret
     * - 1: Klar til sync
     * - 2: Afventer godkendelse
     * - 3: Godkendt
     * - 4: Synkroniseret
     * - 5: Kort afsendt (fysisk eller e-mail)
     * - 6: Synkronisering fejlet
     * - 7: Skal annulleres
     * - 8: Annulleret
     * - 9: Klar til afslutning
     * - 10: Afsluttet (fragt, momskørsel og slutfakturering er lavet)
     * - 11: Arkiveret
     *
     *
     * Other fields that impacts nav sync
     * nav_on_hold - Sync to navision but set onhold parameter on nav_order
     * nav_synced = 0 means it needs to be synced, 1 is ok, 3 is error state
     * nav_done - Not needed anymore
     *
     */

    private $waitingCompanyOrderList = null;
    private $outputMessages = false;
    private $blockMessages = array();
    private $isTechBlock = false;

    public function __construct($output = false)
    {
        //\GFCommon\Model\Navision\NavClient::setNavDevMode(true);
        $this->outputMessages = $output;
    }

    public function getOrdersForSync()
    {
        $this->loadWaiting();
        return $this->waitingCompanyOrderList;
    }

    public function countWaiting()
    {
        $this->loadWaitingJob();
        return countgf($this->waitingCompanyOrderList);
    }

    public function syncAll()
    {
        $this->loadWaiting();
        $this->log("Start sync all, loaded ".$this->countWaiting());
        $processed = array();

        foreach($this->waitingCompanyOrderList as $index => $companyorder) {
            if(!in_array($companyorder->id,$processed)) {
                $this->syncCompanyOrder($companyorder);
                $processed[] = $companyorder->id;
            }
            if($index > 50) return;
            //return;
        }

        $this->waitingCompanyOrderList = null;
    }

    public function syncAllJob()
    {

        // CURRENTLY DISABLED, ENABLE AGAIN WHEN READY
        //return;

        $this->loadWaitingJob();
        $this->log("Start sync all, loaded ".$this->countWaiting());
        $processed = array();

        foreach($this->waitingCompanyOrderList as $index => $companyorder) {
            echo $companyorder->company_name."<br>";
        }
        
        foreach($this->waitingCompanyOrderList as $index => $companyorder) {
            if(!in_array($companyorder->id,$processed)) {
                if($index > 50) break;
                $this->syncCompanyOrder($companyorder);
                $processed[] = $companyorder->id;
            }
        }

        $this->waitingCompanyOrderList = null;
    }

    public function showNext()
    {
        $this->loadWaiting();
        echo "Waiting for sync: ".countgf($this->waitingCompanyOrderList)."<br>";
        foreach($this->waitingCompanyOrderList as $companyorder) {
            echo $companyorder->order_no.": ".$companyorder->company_name." (".$companyorder->id.") - ".$companyorder->order_state." - ".$companyorder->salesperson." - ".$companyorder->shop_name."<br>";
        }
    }

    public function syncCompanyOrder(\CompanyOrder $co)
    {

        $companyOrder = \CompanyOrder::find($co->id);
        $company = \Company::find($co->company_id);

        $this->logNewSync();
        $this->log("Start syncing ".$co->order_no." (".$co->id.") - ".$co->company_name." - ".$co->shop_name);
        $this->blockMessages = array();
        $this->isTechBlock = false;
        $handleOrder = true;

        // Check company
        if($companyOrder->id != $co->id || !in_array($companyOrder->order_state,array(1,3,4,5,7,9)) || $companyOrder->nav_synced == 4) {
            $this->log("Abort sync, id or state mismatch");
            return;
        }

        // Load block messages for order
        $this->loadCompanyOrderBlockMessages($companyOrder->id);
        if($this->isBlocked()) {
            $this->log("Company order is currently blocked, skip.");
            return;
        }

        // Check company
        if(!$this->checkCompanyState($companyOrder,$company)) {
            $this->log("Company state error");
            if($companyOrder->order_state == 1) {

                if($this->hasBlock) {
                    $companyOrder->order_state = 2;
                }
                $companyOrder->save();
            }
            $handleOrder = false;
        }

        // Check for multiple values
        /*
        $hasMultipleValues = trimgf($companyOrder->card_values) != "" && substr_count($companyOrder->card_values, ",");
        if($hasMultipleValues) {

            $allowedShops = array(7121);

            if(!in_array($companyOrder->id,$allowedShops) && $this->blockOrder($companyOrder,"COMPANYORDER_MULTIPLE_VALUES","Har flere værdier, ikke tilladt endnu (".$companyOrder->card_values.")",true,true)) {
                $this->log("Order has multiple values, not allowed yet!");
                $handleOrder = false;
            } else {
                $this->log("Order has multiple values, allowed!");
            }
        }
        */

        // Check salesperson
        if(trimgf($companyOrder->salesperson) == "") {
            $this->log("Order does not have a saleperson");
            $this->blockOrder($companyOrder,"COMPANYORDER_SALESPERSON_MISSING","Mangler sælgerkode",false,false);
            $handleOrder = false;
        }

        // Check country
        if(trimgf($companyOrder->ship_to_country) != "") {
            $country = CountryHelper::countryToCode($companyOrder->ship_to_country);
            if($country == null) {
                $this->log("Ship to country: ".$companyOrder->ship_to_country." not recognized");
                $this->blockOrder($companyOrder,"COMPANYORDER_COUNTRY_INVALID","Leverings land: ".$companyOrder->ship_to_country." kan ikke genkendes",false,false);
                $handleOrder = false;
            }
        }

        // Check shop settings
        if(!$this->checkShop($companyOrder,$company)) {
            $this->log("Shop check state error");
            if($companyOrder->order_state == 1 && $this->hasBlock) {
                $companyOrder->order_state = 2;
                $companyOrder->save();
            }
            $handleOrder = false;
        }

        // Check order items
        if($this->isOrderItemsBlocked($companyOrder,$company) == true) {
            $this->log("Blocked items error");
            if($companyOrder->order_state == 1 && $this->hasBlock) {
                $companyOrder->order_state = 2;
                $companyOrder->save();
            }
            $handleOrder = false;
        }

        // Check e-mail
        if(trimgf($companyOrder->contact_email) != "" && filter_var($companyOrder->contact_email,FILTER_VALIDATE_EMAIL) == false) {
            $this->log("Error in contact e-mail: ".$companyOrder->contact_email);
            $this->blockOrder($companyOrder,"COMPANYORDER_EMAIL_INVALID","Fejl i kontaktperson e-mail: ".$companyOrder->contact_email,false,false);
            $handleOrder = false;
        }

        if($handleOrder == true) {

            // Handle new order
            if($companyOrder->order_state == 1)
            {
                $this->log("Sync new order");
                $this->processNewOrder($companyOrder,$company);
            }

            // Order has been approved
            else if($companyOrder->order_state == 3) {
                $this->log("Sync approved order");
                $this->processApprovedOrder($companyOrder,$company);
            }

            // Already synced, sync updates
            else if($companyOrder->order_state == 4) {
                $this->log("Sync existing order (cards not sent)");
                $this->updateSync($companyOrder,$company);
            }

            // Already synced, sync updates
            else if($companyOrder->order_state == 5) {
                $this->log("Sync updates (cards sent)");
                $this->updateSync($companyOrder,$company);
            }

            // Sync to block order
            else if($companyOrder->order_state == 7) {
                $this->log("Sync to block / cancel order");
                $this->syncBlockOrder($companyOrder,$company);
            }

            // Make final sync
            else if($companyOrder->order_state == 9) {
                $this->log("Make final sync");
                $this->syncFinalOrder($companyOrder,$company);
            }

            // Else
            else {
                $this->log("Should not have been synced (invalid sync state ".$companyOrder->order_state.")");
                return;
            }
        }


        // Commit after each sync
        \system::connection()->commit();
        \System::connection()->transaction();
    }

    private function checkString($val) {

        $val = trimgf(mb_strtolower($val));

        // Remove double space
        $val = str_replace("  "," ",$val,$count);

        // Remove special adress ambiguities
        $val = str_replace(array(". sal",".th",". th",".tv",". tv","a/s","aps","i/s"),"",$val);

        // Match å with aa
        $val = str_replace(array("å",utf8_encode("å"),utf8_decode("å")),"aa",$val);
        $val = str_replace(array(" og "," och"),"&",$val);

        // Remove special characters
        $val = str_replace(array(",",".","-","_","(",")","=","#","\"","'","*","|","?","!"," "),"",$val,$count);

        return $val;

    }

    /**
     * Process a new order that has not been synced or approved
     * @param $companyOrder
     * @param $company
     */
    private function processNewOrder($companyOrder,$company)
    {

        if($this->isTestOrder($companyOrder) == true) {
            $this->blockOrder($companyOrder,"COMPANYORDER_SUSPECTED_TEST","Ordre ligner test ordre.",false,true);
            $companyOrder->order_state = 2;
            $companyOrder->save();
        }

        // Sync to nav
        else if($this->syncToNavision($companyOrder,$company)) {
            $companyOrder->order_state = 4;
            $companyOrder->save();
        }
        // Error in nav sync
        else if($this->hasBlock){
            $companyOrder->order_state = 2;
            $companyOrder->save();
        }

    }

    private function isTestOrder(\CompanyOrder $companyOrder) {

        if(strstr($companyOrder->company_name,"test")) {
            return true;
        }

        if(strstr($companyOrder->cvr,"11111111")) {
            return true;
        }

        if(strstr($companyOrder->ean,"11111111")) {
            return true;
        }

        if(strstr($companyOrder->ship_to_company,"test")) {
            return true;
        }

        if(strstr($companyOrder->contact_name,"test")) {
            return true;
        }

        if(strstr($companyOrder->contact_email,"@gavefabrikken.dk") || strstr($companyOrder->contact_email,"@interactive.dk") || strstr($companyOrder->contact_email,"ka@wogw.dk")) {
            return true;
        };

        return false;

    }

    /**
     * Process a new order that has been synced or approved
     * @param $companyOrder
     * @param $company
     */
    private function processApprovedOrder($companyOrder,$company)
    {
        /*
        if(!$this->isOrderItemsBlocked($companyOrder,$company)) {
            $companyOrder->order_state = 2;
            $companyOrder->save();
            return false;
        }
        */

        // Sync to nav
        if($this->syncToNavision($companyOrder,$company)) {
            $companyOrder->order_state = 4;
            $companyOrder->save();
        }
        // Error in nav sync
        else if($this->hasBlock) {
            $companyOrder->order_state = 2;
            $companyOrder->save();
        }
    }

    /**
     * Process an existing order that has been synced before
     * @param $companyOrder
     * @param $company
     */
    private function updateSync($companyOrder,$company)
    {
        // Sync to nav
        if($this->syncToNavision($companyOrder,$company)) {
            $companyOrder->save();
        }
    }

    /**
     * Process blocked order
     * @param $companyOrder
     * @param $company
     */
    private function syncBlockOrder($companyOrder,$company)
    {
        // Sync to nav
        if($this->syncToNavision($companyOrder,$company)) {
            $companyOrder->order_state = 8;
            $companyOrder->save();
        }
        // Error in nav sync
        else {
            $companyOrder->order_state = 7;
            $companyOrder->save();
        }
    }

    /**
     * Process final sync
     * @param $companyOrder
     * @param $company
     */
    private function syncFinalOrder($companyOrder,$company)
    {

        if($company->manual_freight == 1) {
            echo "Company order ".$companyOrder->id." has manual freight, skip.<br>";
            return;
        }

        // Sync to nav
        if($this->syncToNavision($companyOrder,$company)) {
            if($companyOrder->order_state == 9) {
                $companyOrder->order_state = 10;
                $companyOrder->save();
            }
        }
        // Error in nav sync
        else {
            //$companyOrder->order_state = 9;
            //$companyOrder->save();
        }
    }

    /**
     * CHECK PRODUCT ITEMS
     */

    private function isOrderItemsBlocked($companyOrder,$company)
    {

        // Luksusgavekort fix
        if(strstr($companyOrder->shop_name,"Luksusgavekortet") && $companyOrder->expire_date->format("Y-m-d") == "2022-12-31") {
            $companyOrder->expire_date = "2023-12-31";
        }
        
        // Load shopsettings
        $shopSettings = new \GFBiz\Model\Cardshop\CardshopSettingsLogic($companyOrder->shop_id);
        $priceSettings = $shopSettings->getSettings();
        $expireDate = $shopSettings->getWeekByExpireDate($companyOrder->expire_date->format("Y-m-d"));

      
        if($expireDate == null) {
            
            if ($this->blockOrder($companyOrder, "COMPANYORDER_SYNC_ERROR", "Kan ikke finde expire date til ordre", true, false)) {
                return true;
            }
        }
        
        // Load order items
        $orderItems = \CompanyOrderItem::getCompanyOrderMap($companyOrder->id);
        $hasBlocks = false;

        // Load all orders by company
        $companyOrderList = \CompanyOrder::find('all',array('conditions' => array("company_id" => $company->id,"shop_id" => $companyOrder->shop_id,"expire_date" => ($companyOrder->expire_date->format("Y-m-d")))));


        // On multiple orders check dot, carryup and giftwrap
        if(count($companyOrderList) > 1) {

            // Get all items
            $companyOrderIDList = array();
            foreach($companyOrderList as $co) $companyOrderIDList[] = $co->id;
            $companyOrderItemsList = \CompanyOrderItem::find('all',array("conditions" => array("companyorder_id" => $companyOrderIDList)));

            // Create map
            $companyOrderItemMap = array();
            foreach($companyOrderItemsList as $coi) {
                if(!isset($companyOrderItemMap[$coi->companyorder_id])) {
                    $companyOrderItemMap[$coi->companyorder_id] = array();
                }
                $companyOrderItemMap[$coi->companyorder_id][$coi->type] = $coi;
            }

            // Check dot
            $dotPaid = false;
            foreach ($companyOrderList as $co) {
                if($co->dot == 1 && isset($companyOrderItemMap[$co->id]) && isset($companyOrderItemMap[$co->id]["DOT"]) && $companyOrderItemMap[$co->id]["DOT"]->quantity > 0) {
                    $dotPaid = true;
                    break;
                }
            }

            // Set dot on all
            if($dotPaid) {
                foreach ($companyOrderList as $co) {

                    $updatedOrder = false;

                    // Update dot
                    if($co->dot == 0) {

                        if($co->id == $companyOrder->id) {
                            $companyOrder->dot = 1;
                        }

                        $co->dot = 1;
                        $co->nav_synced = 0;
                        $updatedOrder = true;

                        $this->log("<br>ENABLED DOT ON ORDER ".$co->id."");

                    }

                    // Update dot item
                    if(isset($companyOrderItemMap[$co->id]) && isset($companyOrderItemMap[$co->id]["DOT"]) && $companyOrderItemMap[$co->id]["DOT"]->quantity == 0) {
                        $companyOrderItemMap[$co->id]["DOT"]->quantity = 1;
                        $companyOrderItemMap[$co->id]["DOT"]->price = 0;
                        $companyOrderItemMap[$co->id]["DOT"]->save();
                        $co->nav_synced = 0;
                        $updatedOrder = true;

                        // Add release block message
                        $bm = new \BlockMessage();
                        $bm->company_id = $co->company_id;
                        $bm->company_order_id = $co->id;
                        $bm->block_type = "COMPANYORDER_ITEM_DOT_PRICE";
                        $bm->description = "DOT enabled on order because it is paid for on another order. This block is to approve that DOT is free on this order and automatically relaeased.";
                        $bm->release_status = 1;
                        $bm->tech_block = 0;
                        $bm->release_date = date('d-m-Y H:i:s');
                        $bm->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
                        $bm->release_message = "Action: autoapprove";
                        $bm->save();

                        $this->log("<br>ENABLED DOT ON ORDER ITEM ".$companyOrderItemMap[$co->id]["DOT"]->id."");

                    }

                    if($updatedOrder) {
                        $co->save();
                    }

                }
            }





            // Check carryup
            $carryupPaid = false;
            foreach ($companyOrderList as $co) {
                if($co->gift_spe_lev == 1 && isset($companyOrderItemMap[$co->id]) && isset($companyOrderItemMap[$co->id]["CARRYUP"]) && $companyOrderItemMap[$co->id]["CARRYUP"]->quantity > 0) {
                    $carryupPaid = true;
                    break;
                }
            }

            // Set carryup on all
            if($carryupPaid) {
                foreach ($companyOrderList as $co) {

                    $updatedOrder = false;

                    // Update carryup
                    if($co->gift_spe_lev == 0) {

                        if($co->id == $companyOrder->id) {
                            $companyOrder->gift_spe_lev = 1;
                        }

                        $co->gift_spe_lev = 1;
                        $co->nav_synced = 0;
                        $updatedOrder = true;

                        $this->log("<br>ENABLED CARRYUP ON ORDER ".$co->id."");


                    }

                    // Update dot item
                    if(isset($companyOrderItemMap[$co->id]) && isset($companyOrderItemMap[$co->id]["CARRYUP"]) && $companyOrderItemMap[$co->id]["CARRYUP"]->quantity == 0) {

                        $companyOrderItemMap[$co->id]["CARRYUP"]->quantity = 1;
                        $companyOrderItemMap[$co->id]["CARRYUP"]->price = 0;
                        $companyOrderItemMap[$co->id]["CARRYUP"]->save();
                        $co->nav_synced = 0;
                        $updatedOrder = true;


                        // Add release block message
                        $bm = new \BlockMessage();
                        $bm->company_id = $co->company_id;
                        $bm->company_order_id = $co->id;
                        $bm->block_type = "COMPANYORDER_ITEM_CARRYUP_PRICE";
                        $bm->description = "Opbæring er aktiveret på ordre da det er betalt på en anden ordre, denne blokkering angiver at handlingen er foretaget.";
                        $bm->release_status = 1;
                        $bm->tech_block = 0;
                        $bm->release_date = date('d-m-Y H:i:s');
                        $bm->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
                        $bm->release_message = "Action: autoapprove";
                        $bm->save();

                        $this->log("<br>ENABLED CARRYUP ON ORDER ITEM ".$companyOrderItemMap[$co->id]["CARRYUP"]->id."");

                    }

                    if($updatedOrder) {
                        $co->save();
                    }

                }
            }

/*** IMPORTANT - TEMP DISABLED IN START 2022, ENABLE AGAIN AFTER FEBRUARY - SC - 06/01 2021
            // Check for giftwrap problems
            $giftwrapCheckSQL = "select shop_user.company_id, SUM(IF(company_order.giftwrap = 1, 1,0)) as giftwrapcount, SUM(IF(company_order.giftwrap = 1, 0,1)) as nogiftwrapcount from shop_user, company_order WHERE shop_user.company_order_id = company_order.id && company_order.company_id = ".$companyOrder->company_id." && company_order.shop_id = ".$companyOrder->shop_id." && company_order.expire_date = '".$companyOrder->expire_date->format("Y-m-d")."' && company_order.order_state not in (7,8) GROUP BY shop_user.company_id HAVING giftwrapcount > 0 && nogiftwrapcount > 0";
            $giftwrapCheckResult = \CompanyOrder::find_by_sql($giftwrapCheckSQL);
            if(count($giftwrapCheckResult) > 0) {
                $this->blockOrder($companyOrder,"COMPANYORDER_ITEM_GIFTWRAP_COMPANY_MISMATCH","Kunden har flere ordre med forskellige valg af indpakning, skal være ens for samme kunde / koncept / leveringsuge",false,false);
                $hasBlocks = true;
            }
*/
            /*
            // Check giftwrap
            $giftwrap = null;
            $giftwrapBlocked = false;
            foreach($companyOrderList as $companyOrderCheck) {
                if($companyOrderCheck->order_state != 8) {
                    if($giftwrap === null) {
                        $giftwrap = $companyOrderCheck->giftwrap;
                    }
                    else if($giftwrap != $companyOrderCheck->giftwrap) {
                        if($giftwrapBlocked == false) {
                            $this->blockOrder($companyOrder,"COMPANYORDER_ITEM_GIFTWRAP_COMPANY_MISMATCH","Kunden har flere ordre med forskellige valg af indpakning, skal være ens for samme kunde / koncept / leveringsuge",false,false);
                            $hasBlocks = true;
                            $giftwrapBlocked = true;
                        }
                    }
                }
            }
            */


        }


        /*
        $giftwrap = null;
        $giftwrapBlocked = false;

        foreach($companyOrderList as $companyOrderCheck) {


            // UPDATE DOT

            // Process dot
            if($dot === null) {
                $dot = $companyOrderCheck->dot;
            }
            else if($dot != $companyOrderCheck->dot) {
                if($dotBlocked == false) {
                    $this->blockOrder($companyOrder,"COMPANYORDER_ITEM_DOT_COMPANY_MISMATCH","Company has multiple order with different values on DOT",true,false);
                    $hasBlocks = true;
                    $dotBlocked = true;
                }
            }

            // UPDATE CARRYUP

            // Process carryup
            if($carryup === null) {
                $carryup = $companyOrderCheck->gift_spe_lev;
            }
            else if($carryup != $companyOrderCheck->gift_spe_lev) {
                if($carryupBlocked == false) {
                    $this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARRYUUP_COMPANY_MISMATCH","Company has multiple order with different values on carryup",true,false);
                    $hasBlocks = true;
                    $carryupBlocked = true;
                }
            }


            // Process giftwrap
            if($giftwrap === null) {
                $giftwrap = $companyOrderCheck->giftwrap;
            }
            else if($giftwrap != $companyOrderCheck->giftwrap) {
                if($giftwrapBlocked == false) {
                    $this->blockOrder($companyOrder,"COMPANYORDER_ITEM_GIFTWRAP_COMPANY_MISMATCH","Company has multiple order with different values on giftwrap",true,false);
                    $hasBlocks = true;
                    $giftwrapBlocked = true;
                }
            }

        }
        */

        // CONCEPT - Line missing
        if(!isset($orderItems["CONCEPT"])) {
            $this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CONCEPT_MISSING","Mangler concept linje",true,false);
            $hasBlocks = true;
        }
        else
        {

            if(intval($orderItems["CONCEPT"]->price) == 0) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CONCEPT_ZERO","Pris på gavekort må ikke være 0, giv samme antal gratis kort i stedet.",false,true)) {
                    $hasBlocks = true;
                }
            }

            // CONCEPT - Check for non default price
            if($priceSettings->card_price != $orderItems["CONCEPT"]->price && $shopSettings->getSettings()->card_values == null) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CONCEPT_PRICE","Ændret pris på concept linje: ".($orderItems["CONCEPT"]->price/100),false,true)) {
                    $hasBlocks = true;
                }
            }

            // CONCEPT - Check for free cards
            if($companyOrder->free_cards > 0) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CONCEPT_FREECARDS","Antal gratis kort: ".$companyOrder->free_cards,false,true)) {
                    $hasBlocks = true;
                }
            }

        }


        // PRIVATEDELIVERY - Check for private delivery fee but not private delivery
        if($priceSettings->language_code != 5) {
            if (isset($orderItems["PRIVATEDELIVERY"]) && $orderItems["PRIVATEDELIVERY"]->quantity > 0 && $expireDate->isDelivery() == false) {
                if ($this->blockOrder($companyOrder, "COMPANYORDER_ITEM_PRIVATEDELIVERY_INVALIDFEE", "Privatlevering opkræves på ordre uden privatlevering", false, true)) {
                    $hasBlocks = true;
                }
            }
        }


        // PRIVATEDELIVERY - Check for private delivery but no fee
        if($priceSettings->language_code != 5) {
            if ((!isset($orderItems["PRIVATEDELIVERY"]) || $orderItems["PRIVATEDELIVERY"]->quantity == 0) && $expireDate->isDelivery() == true) {
                if ($this->blockOrder($companyOrder, "COMPANYORDER_ITEM_PRIVATEDELIVERY_NOFEE", "Privatleveringsordre har ikke privatleverings gebyr", false, true)) {
                    $hasBlocks = true;
                }
            }
        }

        // PRIVATEDELIVERY SE - WARN FOR ANY ORDER WITHOUT PRIVATEDELIVERY
        if($priceSettings->language_code == 5) {
            if ((!isset($orderItems["PRIVATEDELIVERY"]) || $orderItems["PRIVATEDELIVERY"]->quantity == 0)) {
                if ($this->blockOrder($companyOrder, "COMPANYORDER_ITEM_PRIVATEDELIVERY_NOFEE", "Ordre har ikke leverings gebyr", false, true)) {
                    $hasBlocks = true;
                }
            }
        }

        if($priceSettings->language_code == 4) {

            // PRIVATE DELIVERY - Check for non default private delivery fee
            if (isset($orderItems["PRIVATEDELIVERY"]) && $orderItems["PRIVATEDELIVERY"]->quantity > 0 && $priceSettings->privatedelivery_price != $orderItems["PRIVATEDELIVERY"]->price) {
                if ($this->blockOrder($companyOrder, "COMPANYORDER_ITEM_PRIVATEDELIVERY_PRICE", "Pris ændret for privatlevering: " . ($orderItems["PRIVATEDELIVERY"]->price / 100), false, true)) {
                    $hasBlocks = true;
                }
            }




        }

/*
        if($companyOrder->shop_id == 1832 and strstr("440", $companyOrder->card_values)) {
            if ((!isset($orderItems["BONUSPRESENTS"]) || $orderItems["BONUSPRESENTS"]->quantity == 0 || $orderItems["BONUSPRESENTS"]->price == 0)) {
                if ($this->blockOrder($companyOrder, "COMPANYORDER_ITEM_BONUSPRICE_MISSING", "Ingen pris på bonusgaver, tjek om det er korrekt.", true, true)) {
                    $hasBlocks = true;
                }
            }
        }
*/

        if($priceSettings->language_code == 4) {

                // CARDFEE - Check for non default price
                if(isset($orderItems["CARDFEE"]) && $orderItems["CARDFEE"]->quantity > 0 && $orderItems["CARDFEE"]->price != $priceSettings->cardfee_price) {
                    if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARDFEE_PRICE","Kortgebyr pris ændret til: ".($orderItems["CARDFEE"]->price/100),false,true)) {
                        $hasBlocks = true;
                    }
                }


        // CARDDELIVERY - Check for fee on e-mail cards

            if(isset($orderItems["CARDDELIVERY"]) && $orderItems["CARDDELIVERY"]->quantity > 0 && $companyOrder->is_email == 1) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARDDELIVERY_EMAILCARDS","Der er angivet kort leveringsgebyr på ordre med e-mail kort",false,true)) {
                    $hasBlocks = true;
                }
            }
        }

        /*
                // CARDDELIVERY - Check for no fee on physical cards
                if((!isset($orderItems["CARDDELIVERY"]) || $orderItems["CARDDELIVERY"]->quantity == 0) && $companyOrder->is_email == 0) {
                    if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARDDELIVERY_NOFEE","Physical cards have no delivery fee.",false,true)) {
                        $hasBlocks = true;
                    }
                }

                // CARDDELIVERY - Check for non default fee
                if(isset($orderItems["CARDDELIVERY"]) && $orderItems["CARDDELIVERY"]->quantity > 0 && $orderItems["CARDDELIVERY"]->price != $priceSettings->carddelivery_price) {
                    if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARDDELIVERY_PRICE","Changed price on card delivery fee: ".($orderItems["CARDDELIVERY"]->price/100),false,true)) {
                        $hasBlocks = true;
                    }
                }
        */
        // Load card shipments
        /*
        $cardshipments = \Shipment::find_by_sql("SELECT * FROM shipment WHERE shipment_type = 'giftcard' && companyorder_id = ".intval($companyOrder->id));
        $cardShipmentCount = countgf($cardshipments);
        if($cardShipmentCount == 0) $cardShipmentCount = 1;
        if(isset($orderItems["CARDDELIVERY"]) && $orderItems["CARDDELIVERY"]->quantity > 0 && $orderItems["CARDDELIVERY"]->quantity != $cardShipmentCount) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARDDELIVERY_MISMATCH",$orderItems["CARDDELIVERY"]->quantity." delivery fees on order but ".$cardShipmentCount." delivery addresses added",false,true)) {
                $hasBlocks = true;
            }
        }
        */

        // CARRYUP - Check for carry up on non carry up order
        if(isset($orderItems["CARRYUP"]) && $orderItems["CARRYUP"]->quantity > 0 && $companyOrder->gift_spe_lev == 0) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARRYUP_NOTSELECTED","Opbæring valgt på ordre men er ikke sat på ordren.",false,true)) {
                $hasBlocks = true;
            }
        }

        // CARRYUP - Check if on order but no fee
        if((!isset($orderItems["CARRYUP"]) || $orderItems["CARRYUP"]->quantity == 0) && $companyOrder->gift_spe_lev == 1) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARRYUP_NOFEE","Opbæringsgebyr fravalgt på ordre med opbæring.",false,true)) {
                $hasBlocks = true;
            }
        }

        // CARRYUP - Check for non default proce
        if(isset($orderItems["CARRYUP"]) && $orderItems["CARRYUP"]->quantity > 0 && $orderItems["CARRYUP"]->price != $priceSettings->carryup_price) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_CARRYUP_PRICE","Pris på opbæring er ændret: ".($orderItems["CARRYUP"]->price/100),false,true)) {
                $hasBlocks = true;
            }
        }


        // DOT - Check if on order but no fee
        if(isset($orderItems["DOT"]) && $orderItems["DOT"]->quantity > 0 && $companyOrder->dot == 0) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_DOT_NOTSELECTED","DOT gebyr på ordren, men DOT er ikke aktiveret.",false,true)) {
                $hasBlocks = true;
            }
        }

        // DOT - Check if fee but not on order
        if((!isset($orderItems["DOT"]) || $orderItems["DOT"]->quantity == 0) && $companyOrder->dot == 1) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_DOT_NOFEE","DOT valgt, men ordre opkræves ikke DOT gebyr",false,true)) {
                $hasBlocks = true;
            }
        }
        // DOT - Check for non default price
        if(isset($orderItems["DOT"]) && $orderItems["DOT"]->quantity > 0 && $orderItems["DOT"]->price != $priceSettings->dot_price) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_DOT_PRICE","Pris ændret på dot gebyr: ".($orderItems["DOT"]->price/100),false,true)) {
                $hasBlocks = true;
            }
        }


        // GIFTWRAP - Check if on order but no fee
        if(isset($orderItems["GIFTWRAP"]) && $orderItems["GIFTWRAP"]->quantity > 0 && $companyOrder->giftwrap == 0) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_GIFTWRAP_NOTSELECTED","Indpakning opkræves men indpakning er ikke aktiveret på ordre",false,true)) {
                $hasBlocks = true;
            }
        }

        // GIFTWRAP - Check if has fee but not on order
        if((!isset($orderItems["GIFTWRAP"]) || $orderItems["GIFTWRAP"]->quantity == 0) && $companyOrder->giftwrap == 1) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_GIFTWRAP_NOFEE","Indpakning valgt men bliver ikke opkrævet.",false,true)) {
                $hasBlocks = true;
            }
        }

        // GIFTWRAP - On delivery order
        if($companyOrder->giftwrap == 1 && $expireDate->isDelivery() == true) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_GIFTWRAP_PRIVATE","Indpakning er ikke understøttet på privatleveringer.",false,true)) {
                $hasBlocks = true;
            }
        }

        // GIFTWRAP - Check non default price
        if($priceSettings->language_code == 4) {

            if(isset($orderItems["GIFTWRAP"]) && $orderItems["GIFTWRAP"]->quantity > 0 && $orderItems["GIFTWRAP"]->price != $priceSettings->giftwrap_price) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_GIFTWRAP_PRICE","Prisen på indpakning er ændret til: ".($orderItems["GIFTWRAP"]->price/100),false,true)) {
                    $hasBlocks = true;
                }
            }

            // INVOICEFEEINITIAL - Check if not default
            if(isset($orderItems["INVOICEFEEINITIAL"]) && $orderItems["INVOICEFEEINITIAL"]->price != $priceSettings->invoiceinitial_price) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_INVOICEFEEINITIAL_CHANGED","Changed initial invoice fee: ".$orderItems["INVOICEFEEINITIAL"]->quantity." x ".($orderItems["INVOICEFEEINITIAL"]->price/100),false,true)) {
                    $hasBlocks = true;
                }
            }


            // INVOICEFEEFINAL - Check if not default
            if(isset($orderItems["INVOICEFEEFINAL"]) && $orderItems["INVOICEFEEFINAL"]->price != $priceSettings->invoicefinal_price) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_INVOICEFEEFINAL_CHANGED","Changed initial invoice fee: ".$orderItems["INVOICEFEEFINAL"]->quantity." x ".($orderItems["INVOICEFEEFINAL"]->price/100),false,true)) {
                    $hasBlocks = true;
                }
            }

            // MINORDERFEE - Check if not default
            if(isset($orderItems["MINORDERFEE"]) && $orderItems["MINORDERFEE"]->price != $priceSettings->minorderfee_price) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_MINORDERFEE_CHANGED","Changed minimum order fee: ".$orderItems["MINORDERFEE"]->quantity." x ".($orderItems["MINORDERFEE"]->price/100),false,true)) {
                    $hasBlocks = true;
                }
            }

            // NAMELABELS - Check if not default
            if(isset($orderItems["NAMELABELS"]) && $orderItems["NAMELABELS"]->price != $priceSettings->namelabels_price) {
                if($this->blockOrder($companyOrder,"COMPANYORDER_ITEM_NAMELABELFEE_CHANGED","Namelabel fee: ".$orderItems["NAMELABELS"]->quantity." x ".($orderItems["NAMELABELS"]->price/100),false,true)) {
                    $hasBlocks = true;
                }
            }

        }


        // Salenote, block
        if(trimgf($companyOrder->salenote) != "" && mb_strtolower(trimgf($companyOrder->salenote)) != "tillægsordre" && mb_strtolower(trimgf($companyOrder->salenote)) != utf8_encode("tillægsordre") && mb_strtolower(trimgf($companyOrder->salenote)) != utf8_decode("tillægsordre")) {
            if($this->blockOrder($companyOrder,"COMPANYORDER_SALENOTE","Note på ordre: ".$companyOrder->salenote,false,true)) {
                $hasBlocks = true;
            }
        }


        // special delivery deal text (no only)
        if($priceSettings->language_code == 4) {
            if (trimgf($companyOrder->spdealtxt) != "") {
                if ($this->blockOrder($companyOrder, "COMPANYORDER_SALENOTE", "Fragtaftale på ordre: " . $companyOrder->spdealtxt, false, true)) {
                    $hasBlocks = true;
                }
            }
        }


        // Check for prepayment_date
        if($companyOrder->prepayment == 0) {
            if ($this->blockOrder($companyOrder, "COMPANYORDER_NOPREPAYMENT", "Forudfakturering er slået fra på ordren", false, true)) {
                $hasBlocks = true;
            }
        }

        // Check for prepayment_date
        if($companyOrder->prepayment == 1 && $companyOrder->prepayment_date != null) {
            if ($this->blockOrder($companyOrder, "COMPANYORDER_PREPAYMENTDATE", "Der er angivet en forudfakturerings dato til: " . $companyOrder->prepayment_date->format('Y-m-d').".".($companyOrder->prepayment == 0 ? " Men ordren har ikke forudfakturering aktiveret!" : ""), false, true)) {
                $hasBlocks = true;
            }
        }


        /*
        // Check for duedate
        if($companyOrder->prepayment_duedate != null) {
            if ($this->blockOrder($companyOrder, "COMPANYORDER_PREPAYMENTDUEDATE", "Der er angivet en betalingsfrist for forudfakturering til: " . $companyOrder->prepayment_duedate->format('Y-m-d').".".($companyOrder->prepayment == 0 ? " Men ordren har ikke forudfakturering aktiveret!" : ""), false, true)) {
                $hasBlocks = true;
            }
        }
           */
        return $hasBlocks;

    }

    /**
     * DO THE ACTUAL SYNC
     */

    private function checkCompanyState($companyOrder,$company)
    {

        $isOk = true;

        // Check receiving company is not blocked
        $companyBlocks = \BlockMessage::find_by_sql("SELECT * FROM `blockmessage` WHERE `company_id` = ".$company->id." and shipment_id = 0 AND `release_status` = 0");
        if(countgf($companyBlocks) > 0) {
            return false;
        }

        // Check company has language code
        if($company->language_code <= 0) {
            $this->blockOrder($companyOrder,"COMPANYORDER_MISSING_LANGUAGE","Company is missing language.",true,false);
            $isOk = false;
        }

        // Check company has navision customer no
        if($company->nav_customer_no <= 0) {
            $this->blockOrder($companyOrder,"COMPANYORDER_NO_COMPANYSYNC","Company is not synced to nav, please sync to process order.",true,false);
            $isOk = false;
        }

        // Check company state
        if($company->company_state == 4) {
            $this->blockOrder($companyOrder,"COMPANYORDER_COMPANY_STATE","Company state is set to blocked.",true,false);
            $isOk = false;
        }

        return $isOk;
    }

    private function checkShop($companyOrder,$company)
    {

        // Cardshop settings
        $shopSettings = \CardshopSettings::find('first',array("conditions" => array("shop_id" => $companyOrder->shop_id)));

        // Check settings
        if($shopSettings == null) {
            $this->blockOrder($companyOrder,"COMPANYORDER_SHOP_ERROR","Could not find shop settings.",true,false);
            return false;
        }

        // Check language
        if($company->language_code != $shopSettings->language_code) {
            $this->blockOrder($companyOrder,"COMPANYORDER_LANGUAGE_DIFF","Company is missing language.",true,false);
            return false;
        }

        return true;

    }

    /**
     * NAVISION SYNCRONIZATION
     */

    private function syncToNavision($companyOrder,$company)
    {

        // Check company
        if(!$this->checkCompanyState($companyOrder,$company)) {
            return false;
        }

        // Check shop settings
        if(!$this->checkShop($companyOrder,$company)) {
            return false;
        }

        // Check se private delivery limitations - do not sync before completed deliveries above quantity minus free cards
        $isSePrivateDeliveryClosing = false;
        if($company->language_code == 5 && $companyOrder->order_state == 9) {

            // Load shopsettings
            $shopSettings = new \GFBiz\Model\Cardshop\CardshopSettingsLogic($companyOrder->shop_id);
            $expireDate = $shopSettings->getWeekByExpireDate($companyOrder->expire_date->format("Y-m-d"));
            if($expireDate->isDelivery()) {

                $this->log("Is swedish private delivery closing!");
                $isSePrivateDeliveryClosing = true;
                //$readyChoices = \ShopUser::find("all",array('conditions' => array("blocked" => 0, "is_demo" => 0,'delivery_state' => 2,'company_order_id' => intval($companyOrder->id))));
                //if(count($readyChoices) == 0 && countgf($readyChoices) <= $companyOrder->free_cards) {
                //    return false;
                //}
            }

        }

        // Check if order has sync
        $lastSync = \GFCommon\model\navision\OrderSync::getLastSyncDocument($companyOrder);
        $isRetry = false;
        $currentRevision = 1;
        $lastCallID = 0;

        // If has last sync, find if it is a retry and find last revision
        if($lastSync != null) {
            $currentRevision = $lastSync->revision;
            if($lastSync->status == 2) {
                $isRetry = true;
                $this->log("- Sync is a retry");
            }

            // Find revision to sync
            $syncRevision = $isRetry ? $currentRevision : $currentRevision+1;
            $this->log("- Sync revision: ".$syncRevision);

        } else {
            $syncRevision = 1;
            $this->log("- Sync revision: ".$syncRevision." - initial");
        }

        // If first sync, check customer in navision
        if($syncRevision == 1) {

            $companyCheckError = "";
            $companyCheckOk = true;
            try {
                $customerClient = $this->getCustomerWS($company->language_code);
                $customerObject = $customerClient->getByCustomerNo($company->nav_customer_no);
                if($customerObject == null) {
                    $companyCheckOk = false;
                    $companyCheckError = "Could not find debitor no ".$company->nav_customer_no." in navision";
                } else if($customerObject->isBlocked()) {
                    $companyCheckOk = false;
                    $companyCheckError = "Debitor no ".$company->nav_customer_no." is blocked in navision";
                } else if($this->checkString($customerObject->getContact()) != $this->checkString($companyOrder->contact_name) && $this->checkString($customerObject->getContact()) != utf8_encode($this->checkString($companyOrder->contact_name)) && utf8_encode($this->checkString($customerObject->getContact())) != $this->checkString($companyOrder->contact_name) && !$this->hasApproved("COMPANYORDER_CONTACT_MISMATCH") && ($company->language_code == 4)) {
                    if($this->checkString($customerObject->getContact()) != $this->checkString($companyOrder->contact_name)) $this->log(" - Contact mismatch 1 ");
                    if($this->checkString($customerObject->getContact()) != utf8_encode($this->checkString($companyOrder->contact_name))) $this->log(" - Contact mismatch 2");
                    if(utf8_encode($this->checkString($customerObject->getContact())) != $this->checkString($companyOrder->contact_name)) $this->log(" - Contact mismatch 3");
                    $this->log(" - Contact check nav: ".$customerObject->getContact()." vs. cardshop: ".$companyOrder->contact_name);
                    $this->blockOrder($companyOrder,"COMPANYORDER_CONTACT_MISMATCH",$companyCheckError,false,true);
                } else {

                    $company->excempt_invoicefee = $customerObject->isExcemptFromInvoiceFee() ? 1 : 0;
                    $company->excempt_envfee = $customerObject->isExcemptFromEnvFee() ? 1 : 0;
                    $company->save();

                    /*
                    $companyOrder->excempt_invoicefee = $company->excempt_invoicefee;
                    $companyOrder->excempt_envfee = $company->excempt_envfee;
                    $companyOrder->save();
                    */

                }

            } catch (\Exception $e) {
                $companyCheckOk = false;
                $companyCheckError = "Error from navision, could not check debitor: ".$e->getMessage();
            }

            if(!$companyCheckOk) {
                $this->blockOrder($companyOrder,"COMPANYORDER_COMPANY_BLOCKED",$companyCheckError,false,false);
            }

        }

        // Get nav client
        try {
            $this->log("Create client for version check..");
            $client = $this->getOrderWS($company->language_code);
        } catch(\Exception $e) {
            $this->log("Coult nod create nav client: ".$e->getMessage());
            return false;
        }

        // Check revision
        $this->log("Sync revision: ".$syncRevision);

        if($client->checkOrderVersion($companyOrder->order_no)) {
            $lastVersion = $client->getLastOrderVersion();
            if($lastVersion +1 != $syncRevision) {
                $this->log("- Mismatch in order version, last nav sync revision: ".$lastVersion.", : expected to sync ".$syncRevision."");
                //$this->blockOrder($companyOrder,"COMPANYORDER_REVISION_ERROR","last nav sync revision: ".$lastVersion.", : expected to sync ".$syncRevision."",true,false);
                //return;

                $syncRevision = $lastVersion+1;
                $this->log("Last revision was: ".$lastVersion." set new revision to ".$syncRevision."");
            }
        }

        // Generate order document
        try {


            $orderXML = new \GFCommon\model\navision\OrderXML($companyOrder,$syncRevision);
            $xml = $orderXML->getXML();

            if($orderXML->getActiveCards() == 0 && $companyOrder->order_state != 7) {

                if($companyOrder->order_state <= 3) {
                    $companyOrder->order_state = 8;
                    $companyOrder->is_cancelled = 1;
                } else {
                    $companyOrder->order_state = 8;
                    $companyOrder->is_cancelled = 1;
                }

            }

        } catch (\Exception $e) {

            if(strstr($e->getMessage(),"Cant rollback order in first version")) {

                $this->log("Order is closed before first sync");

                $companyOrder->order_state = 8;
                $companyOrder->is_cancelled = 1;
                $companyOrder->save();

                // Get shipments and update
                $shipmentList = \Shipment::find("all",array("conditions" => array("companyorder_id" => $companyOrder->id)));
                foreach($shipmentList as $shipment) {
                    if(in_array($shipment->shipment_state,array(0,1,5))) {
                        $shipment->shipment_state = 4;
                        $shipment->deleted_date = date('d-m-Y H:i:s');
                        $shipment->save();
                    }
                }

            }
            else {
                $this->blockOrder($companyOrder,"COMPANYORDER_XML_ERROR","Company order xlm error: ".$e->getMessage(),true,false);
            }

            return false;
        }

       
        // Check if same as last revision
        $lastXML = "";
        $orderdocs = \NavisionOrderDoc::find_by_sql("SELECT * FROM navision_order_doc WHERE company_order_id = ".intval($companyOrder->id)." ORDER BY revision DESC LIMIT 1");
        if(is_array($orderdocs) && countgf($orderdocs) > 0) {
            if(!in_array($orderdocs[0]->status,array(0,2,3))) {
                $lastXML = $orderdocs[0]->xmldoc;
                $lastXML = str_replace("<suppressconfirmation>true</suppressconfirmation>","<suppressconfirmation>false</suppressconfirmation>",$lastXML);
                if($xml == $lastXML || str_replace("<version>".$syncRevision."</version>","<version>".$orderdocs[0]->revision."</version>",$xml) == $lastXML) {
                    $this->log("XML doc is same as last synced doc, do not send to nav but mark as synced.");
                    $companyOrder->nav_synced = 1;
                    $companyOrder->save();
                    return true;
                }
            }
        }

        // Use last doc on retry or create a new one
        if($isRetry) {
            $syncDocument = \NavisionOrderDoc::find($lastSync->id);
        } else {
            $syncDocument = new \NavisionOrderDoc();
            $syncDocument->company_order_id = $companyOrder->id;
            $syncDocument->xmldoc = "";
            $syncDocument->status = 1;
            $syncDocument->revision = $syncRevision;
            $syncDocument->error = "";
            $syncDocument->retry = 0;
            $syncDocument->navision_call_log_id = 0;
            $syncDocument->order_no = $companyOrder->order_no;
            $syncDocument->save();
        }

        // Determine if orderconfirmation should be set
        $sendOrderConfirmation = false;
        if($lastXML == "" || $syncRevision == 1 || $companyOrder->force_orderconf > 0) {
            $sendOrderConfirmation = true;
            $this->log("Send order confirmation on first order or forced");
        } else {
            $sendOrderConfirmation = $this->checkSendOrderConfirmation($lastXML,$xml);
            if($sendOrderConfirmation) $this->log("Send order confirmation on order check");
        }

        if(!$sendOrderConfirmation) {
            $xml = str_replace("<suppressconfirmation>false</suppressconfirmation>","<suppressconfirmation>true</suppressconfirmation>",$xml);
        }

        // Masterswitch on suppress, for special occations
        $masterForceSuppressConfirmation = null;
        if($masterForceSuppressConfirmation !== null) {
            if($masterForceSuppressConfirmation) {
                $xml = str_replace("<suppressconfirmation>false</suppressconfirmation>","<suppressconfirmation>true</suppressconfirmation>",$xml);
                $this->log("Supress confirmation set to true, master force");
            } else {
                $xml = str_replace("<suppressconfirmation>true</suppressconfirmation>","<suppressconfirmation>false</suppressconfirmation>",$xml);
                $this->log("Supress confirmation set to false, master force");
            }
        }

        $this->log("<pre>".htmlentities($xml)."</pre>");



        // Send order to navision
        try {

            // Send order to navision
            try {
                $syncDocument->xmldoc = $xml;
                $orderResponse = $client->uploadOrderDoc($xml);
                $lastCallID = $client->getLastCallID();

                if($orderResponse) {
                    if($client->getLastOrderResponse() != "OK") {
                        throw new \Exception("Order synced but navision responded with non ok answer: ".$client->getLastOrderResponse());
                    } else {
                        $this->log("Order synced ok: ".$client->getLastOrderResponse());
                    }
                } else {
                    throw new \Exception("Could not upload order doc: ".$client->getLastError());
                }
            } catch (Exception $e) {

                
                $lastCallID = $client->getLastCallID();

                if($client->checkOrderVersion($companyOrder->order_no)) {
                    $lastVersion = $client->getLastOrderVersion();
                    if($lastVersion != $syncRevision) {
                        throw new \Exception("Error syncing document to navision: ".$e->getMessage());
                    }
                } else {
                    throw new \Exception("Error syncing document to navision: ".$e->getMessage());
                }

                // Possivle false positive (version has increased)
                NavDebugTools::mailProblem("NavClient possible false-positive [id ".$this->getLastCallID()."]","Nav client error given but order has been marked as synced.<br>Order: ".$companyOrder->order_no."<br><pre>".$e->getMessage()."</pre>");

            }

            // Set nav call id on doc
            $this->log("Sync had call log id: ".$client->getLastCallID());
            $syncDocument->navision_call_log_id = $client->getLastCallID();

            // Save sync document
            $syncDocument->status = 1;
            $syncDocument->save();

            // Update company order
            $companyOrder->nav_synced = ($orderXML->shouldResync() ? 0 : 1);
            $companyOrder->nav_lastsync = date('d-m-Y H:i:s');
            $companyOrder->force_orderconf = 0;

            \ActionLog::logAction("OrderNavSync", ($syncDocument->revision == 1 ? "Ordre oprettet i navision" : "Opdatering til ordre sendt til navision").": ".$companyOrder->order_no, "",0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,0);

            // If se private delivery
            if($isSePrivateDeliveryClosing == true) {

                $cardidlist = array();
                $syncedShopUsers = \ShopUser::find("all",array('conditions' => array("blocked" => 0, "is_demo" => 0,'delivery_state' => 2,'company_order_id' => intval($companyOrder->id))));
                foreach($syncedShopUsers as $shopUserSynced) {
                    $shopUserSynced->delivery_state = 5;
                    $shopUserSynced->save();
                    $cardidlist[] = $shopUserSynced->id;
                }

                $choiceDoc = new \NavisionChoiceDoc();
                $choiceDoc->company_order_id = $companyOrder->id;
                $choiceDoc->order_no = $companyOrder->order_no;
                $choiceDoc->xmldoc = "";
                $choiceDoc->version = 0;
                $choiceDoc->cardcount = countgf($cardidlist);
                $choiceDoc->status = 1;
                $choiceDoc->error = "";
                $choiceDoc->navision_call_log_id = $client->getLastCallID();
                $choiceDoc->shopuserlist = implode(",",$cardidlist);
                $choiceDoc->lastversion = 0;

                if(count($syncedShopUsers) == $orderXML->getActiveCards()) {
                    $companyOrder->order_state = 12;
                    $choiceDoc->lastversion = 1;
                } else {
                    $companyOrder->order_state = 10;
                }

                $choiceDoc->save();

            }

            $companyOrder->save();

            return true;

        } catch(\Exception $e) {

            // Output exception
            $this->log("Order sync exception: |".$e->getMessage()."|");

            // Determine retry
            $shouldRetry = true;
            if($syncDocument->retry == 5) $shouldRetry = false;

            if($e->getMessage() == "Exception calling Upload: is blocked") {
                $shouldRetry = false;
            }

            // Update sync document
            $syncDocument->status = ($shouldRetry ? 2 : 3);
            $syncDocument->retry++;
            $syncDocument->error = $e->getMessage();
            $syncDocument->navision_call_log_id = $lastCallID;
            $syncDocument->save();

            // Update company order
            $companyOrder->nav_synced = ($shouldRetry ? 3 : 4);
            $companyOrder->nav_lastsync = date('d-m-Y H:i:s');
            if($companyOrder->freight_state === null) {
                $companyOrder->freight_state = 0;
            }
            $companyOrder->save();

            // Set prepayment
            if(strstr($e->getMessage(), "Once set, <prepayment> cannot be unset")) {
                $companyOrder->prepayment = 1;
                $companyOrder->save();
                return $this->syncToNavision($companyOrder, $company);
            }

            if($e->getMessage() == "Exception calling Upload: Unknown <salesperson>") {
                $this->blockOrder($companyOrder,"COMPANYORDER_SALEPERSON","Ukendt salgsperson: ".$companyOrder->salesperson,false,false);
                return false;
            }

            if(strstr($e->getMessage(),'Exception calling Upload: "Contact" is missing on this <customerno> in NAV. This field is mandatory for EAN customers!')) {
                $this->blockOrder($companyOrder,"COMPANY_EANCONTACT_MISSING","Kontaktperson er påkrævet for ean kunder, opdater i navision.",false,false);
                return false;
            }

            if(strstr($e->getMessage(),'Order has pending operations in a fault state.')) {
                $this->blockOrder($companyOrder,"COMPANYORDER_NAV_FIXFAULT","Der er et problem i navision der skal løses.",false,false);
                return false;
            }


            if(strstr($e->getMessage(),"Item with") && strstr($e->getMessage(),"is blocked")) {
                $this->blockOrder($companyOrder,"COMPANYORDER_ITEM_BLOCKED","Debitor er blokkeret i navision",true,false);
                return false;
            }

            if(strstr($e->getMessage(),"is blocked")) {
                $this->blockOrder($companyOrder,"COMPANYORDER_COMPANY_BLOCKED","Debitor er blokkeret i navision",false,false);
                return false;
            }

            // Create block message if should not retry
            else if($shouldRetry == false) {
                $this->blockOrder($companyOrder,"COMPANYORDER_SYNC_ERROR","".$e->getMessage(),($e->getMessage() == "is blocked" ? false : true),false);
            }

            return false;

        }

    }

    /**
     * BLOCK HELPERS
     */

    private $hasBlock = false;

    private function blockOrder(\CompanyOrder $companyOrder,$blockType,$description,$isTech,$checkExisting=true)
    {
        if($checkExisting == false || ($checkExisting == true && !$this->hasApproved($blockType))) {
            $this->log(($isTech ? "TECH-" : "")."BLOCK: ".$blockType.": ".$description);
            \BlockMessage::createCompanyOrderBlock($companyOrder->company_id,$companyOrder->id,$blockType,$description,$isTech,$this->syncMessages);
            $this->hasBlock = true;
            return true;
        }
        return false;
    }

    private $blockList = null;
    private $blockActive = false;
    private function loadCompanyOrderBlockMessages($company_order_id)
    {
        $this->blockActive = false;
        $this->blockList = \BlockMessage::find('all',array("conditions" => array("company_order_id" => $company_order_id)));
        if(is_array($this->blockList) && countgf($this->blockList) > 0) {
            foreach($this->blockList as $block) {
                if($block->release_status != 1) {
                    $this->blockActive = true;
                    break;
                }
            }
        }
    }

    private function isBlocked()
    {
        return $this->blockActive;
    }

    private function hasApproved($type)
    {
        if(is_array($this->blockList) && countgf($this->blockList) > 0) {
            foreach($this->blockList as $block) {
                if($block->release_status == 1 && $block->block_type == $type) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * CHECK ORDER CONFIRMATION
     */

    private function checkSendOrderConfirmation($lastXML,$currentXML)
    {
        try {

            $lastData = $this->xmlToArray($lastXML);
            $currentData = $this->xmlToArray($currentXML);

            $triggerFields = array("week", "shop_id", "private_delivery");
            foreach($triggerFields as $field) {
                if(isset($lastData["order"][$field]) != isset($currentData["order"][$field])) {
                    $this->log("DIFF on isset ".$field);
                    return true;
                } else if(isset($lastData["order"][$field]) && $lastData["order"][$field] != $currentData["order"][$field]) {
                    $this->log("DIFF on value ".$field);
                    return true;
                }
            }

            // Sum amount on last
            $lastAmount = 0;
            foreach($lastData["order"]["lines"]["line"] as $line) {
                if($line["price"] != "-999.00") {
                    $lastAmount += floatval($line["price"])*floatval($line["quantity"]);
                }
            }

            // Sum amount on current
            $currentAmount = 0;
            foreach($currentData["order"]["lines"]["line"] as $line) {
                if($line["price"] != "-999.00") {
                    $currentAmount += floatval($line["price"])*floatval($line["quantity"]);
                }
            }

            $this->log("Amount check ".$lastAmount." - ".$currentAmount."");
            if($lastAmount != $currentAmount) {
                $this->log("DIFF on amount ".$lastAmount." - ".$currentAmount."");
                return true;
            }

            return false;

        }
        catch(\Exception $e) {
            $this->log("FEJL I CHECK ORDRE BEKRÆFTELSES: ".$e->getMessage());
            return true;
        }
    }

    private function xmlToArray($xml) {

        $xmldata = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xmldata);
        return json_decode($json,TRUE);

    }

    /**
     * HELPERS
     */

    private $orderWs = array();
    private $messages = array();
    private $syncMessages = array();

    /**
     * @param $countryCode
     * @return OrderWS|mixed
     * @throws \Exception
     */
    private function getOrderWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create order service with no nav country");
        }
        if(isset($this->orderWs[intval($countryCode)])) {
            return $this->orderWs[intval($countryCode)];
        }
        $this->orderWs[intval($countryCode)] = new \GFCommon\Model\Navision\OrderWS(intval($countryCode));
        return $this->orderWs[intval($countryCode)];
    }

    private $customerWS = array();

    /**
     * @param $countryCode
     * @return CustomerWS
     * @throws \Exception
     */
    private function getCustomerWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create customer service with no nav country");
        }
        if(isset($this->customerWS[intval($countryCode)])) {
            return $this->customerWS[intval($countryCode)];
        }
        $this->customerWS[intval($countryCode)] = new \GFCommon\Model\Navision\CustomerWS(intval($countryCode));
        return $this->customerWS[intval($countryCode)];
    }

    private function logNewSync() {
        $this->hasBlock = false;
        $this->syncMessages = array();
    }

    private function log($message) {
        if($this->outputMessages) {
            echo $message."<br>";
        }
        $this->messages[] = $message;
        $this->syncMessages[] = $message;
    }

    private function loadWaiting()
    {
        $languages = array(1,4,5); // ,4,5
        $lockToSalesPerson = "";

        $salesLockSQL = "";
        if($lockToSalesPerson != null && $lockToSalesPerson != null) {
            $salesLockSQL = " && salesperson = '".$lockToSalesPerson."'";
        }

        $extraCompanyOrderLockSQL = "";
        //$extraCompanyOrderLockSQL = " && company_order.id NOT IN (150,196,209,213,214,215,216,217,218,279,298,458,463,536,662,665,683,694,712,718,737,738,814,817,821,828,847,848,849,867,868,872,875,956,958,963,966,994,1000,1021,1022,1024,1057,1073,1114,1122,1138,1170,1359,1360,1362,1364,1368,1369,1387,1408,1445,1521,1538,1586,1591,1628,1711,1727,1735,1744,1762,1778,1923,1956,1963,1982,1998,2034,2038,2046,2050,2072,2081,2091,2092,2111,2234,2277,2423,2424,2456,2470,2504,2506,2508,2595,2603,2611,2647,2678,2710,2783,2787,2792,2795,2796,2798,2800,2801,2802,2804,2811,2813,2815,2819,2825,2830,2840,2841,2893,2894,2895,2896,2897,2898,2899,2900,2901,2902,2903,2904,2905,2906,2915,2938,2991,3001,3033,3125,3127,3269,3272,3307,3313,3388,3403,3411,3442,3518,3542,3574)";

        if($this->waitingCompanyOrderList != null) return;
        $this->waitingCompanyOrderList = \CompanyOrder::find_by_sql("SELECT company_order.* FROM company_order, cardshop_settings, company WHERE company.id = company_order.company_id ".$extraCompanyOrderLockSQL." && company.nav_customer_no > 0 && company_order.order_state IN (1,3,4,5,7,9) && company_order.nav_synced in (0,3) && (company_order.order_state != 1 || (company_order.order_state = 1 && (company_order.force_syncnow = 1 || ((company_order.salesperson != 'IMPORT' && NOW() > DATE_ADD(company_order.created_datetime, INTERVAL cardshop_settings.ordercs_syncwait HOUR)) || (company_order.salesperson = 'IMPORT' && NOW() > DATE_ADD(company_order.created_datetime, INTERVAL cardshop_settings.orderweb_syncwait HOUR)))))) && company_order.shop_id = cardshop_settings.shop_id && cardshop_settings.language_code IN (".implode(",",$languages).") && company_order.id NOT IN (select company_order_id FROM blockmessage WHERE release_status = 0) ".$salesLockSQL." ORDER BY ID ASC");
    }

    // Used by automatic job queue, only add things that should go automatically into production
    private function loadWaitingJob()
    {
        $languages = array(1,4,5);  // ,4,5
        $this->waitingCompanyOrderList = \CompanyOrder::find_by_sql("SELECT company_order.* FROM company_order, cardshop_settings, company WHERE  company.id = company_order.company_id && company.nav_customer_no > 0 && company_order.order_state IN (1,3,4,5,7,9) && company_order.nav_synced in (0,3) && (company_order.order_state != 1 || (company_order.order_state = 1 && (company_order.force_syncnow = 1 || ((company_order.salesperson != 'IMPORT' && NOW() > DATE_ADD(company_order.created_datetime, INTERVAL cardshop_settings.ordercs_syncwait HOUR)) || (company_order.salesperson = 'IMPORT' && NOW() > DATE_ADD(company_order.created_datetime, INTERVAL cardshop_settings.orderweb_syncwait HOUR)))))) && company_order.shop_id = cardshop_settings.shop_id && cardshop_settings.navsync_orders = 1 &&  cardshop_settings.language_code IN (".implode(",",$languages).") && company_order.id NOT IN (select company_order_id FROM blockmessage WHERE release_status = 0) ORDER BY ID ASC");
    }

}
