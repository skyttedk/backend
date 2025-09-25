<?php

namespace GFUnit\navision\syncshipment;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerObj;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\CustomerXML;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\OrderWS;
use GFCommon\Model\Navision\OrderXML;
use GFCommon\Model\Navision\ShipmentXML;
use GFCommon\Model\Navision\Shipment2XML;

class ShipmentSync
{

    private $waitingShipmentList = null;
    private $outputMessages = false;
    private $blockMessages = array();
    private $isTechBlock = false;


    public function __construct($output = false)
    {
        //\GFCommon\Model\Navision\NavClient::setNavDevMode(true);
        $this->outputMessages = $output;
    }

    public function getShipmentsForSync()
    {
        $this->loadWaiting();
        return $this->waitingShipmentList;
    }

    public function countWaiting()
    {
        $this->loadWaiting();
        return countgf($this->waitingShipmentList);
    }

    public function syncAll()
    {

        $this->log("Start shipment sync - not job");

        echo "STOPPED! - you sure you know what you are doing?";
        exit();
        
        // Preprocess checks
        $shiptoCheck = new ShiptoCheck();
        $shiptoCheck->run($this->outputMessages);

        // Process orders ready to sync
        //$this->checkOrdersToShip();

        // Process private delivery ready to ship
        // TODO - Load shop_users not shipped but ready

        // Load waiting shipments
        $this->loadWaiting();
        $this->log("Loaded ".$this->countWaiting()." shipment to sync");



        // Sync shipments
        $counter = 0;
        foreach($this->waitingShipmentList as $shipment) {

            $this->syncShipment($shipment);
            $counter++;
            // Commit database
            \system::connection()->commit();
            \System::connection()->transaction();
            //return;
            if($counter >= 100)return;

        }
        $this->waitingShipmentList = null;
    }

    public function syncAllJob()
    {

        $this->log("Start shipment sync");

        // Preprocess checks
        $shiptoCheck = new ShiptoCheck();
        $shiptoCheck->run($this->outputMessages);

        // Load waiting shipments
        $this->loadWaitingJob();
        $this->log("Loaded ".$this->countWaiting()." shipment to sync");
/*
        echo "<pre>";
        print_r($this->waitingShipmentList);
        echo "</pre>";
        return;
*/

        // Sync shipments
        foreach($this->waitingShipmentList as $index => $shipment) {

            $this->syncShipment($shipment);

            // Commit database
            \system::connection()->commit();
            \System::connection()->transaction();

            if($index >= 20) break;
        }

        $this->waitingShipmentList = null;
    }


    public function showNext()
    {
        $this->loadWaiting();
        echo "Waiting for sync: ".countgf($this->waitingShipmentList)."<br>";
        foreach($this->waitingShipmentList as $shipment) {
            $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
            echo $companyOrder->order_no.": ".$companyOrder->company_name." | <b>".$shipment->id."</b>: ".$companyOrder->shop_name." - ".$shipment->shipment_type." -".$shipment->shipto_state." (".($shipment->isshipment == 1 ? "physical" : "email").") - ".$shipment->shipto_name."<br><hr><br>";
        }
    }

    // Check if orders are ready to ship but dont have shipments
    private function checkOrdersToShip()
    {

        // Load company orders ready to ship
        $companyOrderList = \CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE shipment_on_hold = 0 && ((order_state = 4 && is_email = 0) || (order_state = 5 && is_email = 1))");
        $this->log("Found ".countgf($companyOrderList)." orders to check for shipping");

        // Process each company order
        foreach($companyOrderList as $companyOrder) {
            $this->checkCompanyOrderShipment($companyOrder);
        }

        // Commit database
        \system::connection()->commit();
        \System::connection()->transaction();

    }

    // Check / create shiptments on orders
    private function checkCompanyOrderShipment(\CompanyOrder $companyOrder)
    {

        // Log
        $this->log("Check company order ".$companyOrder->order_no." (".$companyOrder->id.")");

        // Count shopusers
        $shopuserList = \ShopUser::find("all",array("conditions" => array("company_order_id" => $companyOrder->id)));
        $activeShopUserCount = 0;
        $allShopUserCount = 0;
        $minNumber = 0;
        $maxNumber = 0;

        // Count shopusers
        foreach($shopuserList as $shopuser) {
            $allShopUserCount++;
            if($shopuser->blocked == 0) {

                $activeShopUserCount++;

                if($minNumber == 0 || intval($shopuser->username) < $minNumber) {
                    $minNumber = intval($shopuser->username);
                }

                if($maxNumber == 0 || intval($shopuser->username) > $maxNumber) {
                    $maxNumber = intval($shopuser->username);
                }

            }
        }

        // Load existing shipment
        $shipmentList = \Shipment::find('all',array("conditions" => array("companyorder_id" => $companyOrder->id,"shipment_type" => "giftcard")));
        $this->log(" - ".$allShopUserCount." cards / ".$activeShopUserCount." active cards (".$minNumber." - ".$maxNumber.")");

        // No shipments, create it
        if(count($shipmentList) == 0) {

            $this->log(" - No existing shipments, create");

            // Load company
            $company = \Company::find($companyOrder->company_id);

            // Create shipment
            $shipment = new \Shipment();
            $shipment->companyorder_id = $companyOrder->id;
            $shipment->shipment_type = "giftcard";
            $shipment->quantity = ($maxNumber-$minNumber)+1;
            $shipment->itemno = "";
            $shipment->description = "";
            $shipment->isshipment = ($companyOrder->is_email == 1 ? 0 : 1);
            $shipment->from_certificate_no = $minNumber;
            $shipment->to_certificate_no = $maxNumber;
            $shipment->shipment_state = 1;

            $shipment->shipto_name = trimgf($company->ship_to_company) == "" ? $company->name : $company->ship_to_company;
            $shipment->shipto_address = $company->ship_to_address;
            $shipment->shipto_address2 = $company->ship_to_address_2;
            $shipment->shipto_postcode = $company->ship_to_postal_code;
            $shipment->shipto_city = $company->ship_to_city;
            $shipment->shipto_country = $company->ship_to_country;
            $shipment->shipto_contact = $company->ship_to_attention;
            $shipment->shipto_email = $company->contact_email;
            $shipment->shipto_phone = str_replace(array(" ","-"),"",trim($company->contact_phone));

            // Save and add to list
            $shipment->save();
            $shipmentList[] = $shipment;

        }

        // Process shipments
        $this->log(" - Found ".countgf($shipmentList)." existing shipments");
        $problemShipments = array();
        $shipmentCards = 0;

        foreach($shipmentList as $shipment) {

            // Check card numbers
            if($shipment->quantity == 0 || ($shipment->to_certificate_no)-intval($shipment->from_certificate_no)+1 <= 0) {
                $this->blockShipment($shipment,"SHIPMENT_QUANTITY_WARNING","Cards in shipment ".$shipment->id." is ".$shipment->quantity." (quantity) - card interval is ".$shipment->from_certificate_no." to ".$shipment->to_certificate_no.".",true);
                $problemShipments[] = $shipment->id;
            }
            else {

                // Add to count
                $shipmentCards += ($shipment->to_certificate_no)-intval($shipment->from_certificate_no)+1;

                // Set state to 1
                $shipment->shipment_state = 1;
                $shipment->save();

            }

        }

        // If any has problems, block all and return
        if(count($problemShipments) > 0) {
            foreach($shipmentList as $shipment) {
                if(!in_array($shipment->id,$problemShipments)) {
                    $this->blockShipment($shipment,"SHIPMENT_OTHER_BLOCKED","Card shipment blocked because other shipment on same order has problems fix and release all to send shipments.",true);
                }
            }
            return;
        }

        // Check total type
        if($shipmentCards != $activeShopUserCount && $shipmentCards != $allShopUserCount) {
            foreach($shipmentList as $shipment) {
                $this->blockShipment($shipment,"SHIPMENT_COUNT_WARNING","Cards in company order (".$companyOrder->order_no.") shipment is ".$shipmentCards.", should be ".$allShopUserCount." (all cards) or ".$activeShopUserCount." (active cards)",true);
            }
        }

    }

    public function syncShipment(\Shipment $s)
    {

        // Start new log
        $this->logNewSync();
        $this->log("Start syncing ".$s->id." - ".$s->shipment_type." (company order: ".$s->companyorder_id.")");



        // Load shipment as new element
        $shipment = \Shipment::find($s->id);

        // Check for blocks not released
        $blockMessages = \BlockMessage::find('all',array("conditions" => array("shipment_id" => $shipment->id,"release_status" => 0)));
        if(count($blockMessages) > 0) {
            $this->log("Shipment has non-release blockmessages, abort sync");
            return;
        }

        // Check shipment
        if($shipment->id != $s->id || !in_array($shipment->shipment_state,array(1,5))) {
            $this->blockShipment($shipment,"SHIPMENT_UNKNOWN_STATE","Unknown shipment state or id mismatch.",1);
            return;
        }

        // Check order
        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        $company = \Company::find($companyOrder->company_id);

        // Check shipment order status
        $validStates = array(4,5);
        $blockStates = array(7,8);
        if(!in_array($companyOrder->order_state,$validStates)) {
            if(in_array($companyOrder->order_state,$blockStates)) {
                $shipment->shipment_state = 4;
                $shipment->save();
                //$this->blockShipment($shipment,"SHIPMENT_INVALID_ORDERSTATE","Invalid order state for shipments: ".$companyOrder->order_state,1);
                return;
            }
        }


        // Verify shipment handler
        $cardshopSettings = \CardshopSettings::find(array("conditions" => array("shop_id" => $companyOrder->shop_id)));
        if($shipment->shipment_type == "earlyorder") {
            if($shipment->handler != $cardshopSettings->earlyorder_handler && $cardshopSettings->earlyorder_handler != "autodetect") {
                $shipment->handler = $cardshopSettings->earlyorder_handler;
                $shipment->save();
            }
        } else if($shipment->shipment_type == "privatedelivery"){
            if($shipment->handler != $cardshopSettings->privatedelivery_handler) {
                //$shipment->handler = $cardshopSettings->privatedelivery_handler;
                //$shipment->save();
            }
        }

        // Check if handler is navision
        if($shipment->handler != "navision") {
            $this->log("Abort nav sync, handler is: ".$shipment->handler);
            return;
        }

        // Check shipment order not onhold
        if($companyOrder->shipment_on_hold == 1) {
            $this->log("Shipment is on hold on order, do not sync");
            return;
        }

        // Check valid address
        if(trimgf($shipment->shipto_name) == "" || trimgf($shipment->shipto_address) == "" || trimgf($shipment->shipto_postcode) == "" || trimgf($shipment->shipto_city) == "") {
            $this->blockShipment($shipment,"SHIPMENT_INVALID_ADDRESS","Navn, adresse, postnr eller by er ikke angivet.",false);
            return;
        }
/*
        // Check valid email
        if(trimgf($shipment->shipto_email) != "" && !filter_var($shipment->shipto_email, FILTER_VALIDATE_EMAIL)) {
            $this->blockShipment($shipment,"SHIPMENT_EMAIL_INVALID","Leverance e-mail ikke gyldig: ".$shipment->shipto_email,true);
            return;
        }
*/

        // Sync giftcard
        if($shipment->shipment_type == "giftcard") {

            // Check if all shopusers are blocked
            $activeShopUsers = \ShopUser::find_by_sql("SELECT * FROM shop_user WHERE company_order_id = ".$companyOrder->id." && username >= ".$shipment->from_certificate_no." && username <= ".$shipment->to_certificate_no." && blocked = 0");
            if(count($activeShopUsers) == 0) {
                $this->blockShipment($shipment,"SHIPMENT_CARDS_BLOCKED","Alle kort i gavekort leverancen er lukket, godkendelse er blokkeret.",true);
                $companyOrder->order_state = 4;
                $companyOrder->save();
                return;
            }

            // If giftcard, check if it should use another navision to print
            if($shipment->shipment_state == 1 && $shipment->handle_country == 0 && $shipment->isshipment == 1) {

                if($cardshopSettings->shipment_print_language > 0) {
                    $shipment->handle_country = $cardshopSettings->shipment_print_language;
                    $shipment->save();
                    $this->log("HANDLE COUNTRY SET TO: ".$shipment->handle_country."<br>");
                }
            }

            // Check other shipments in db not overlapping on numbers
            $collisionSQL = "SELECT * FROM `shipment` WHERE ((from_certificate_no <= ".$shipment->to_certificate_no." && to_certificate_no >= ".$shipment->to_certificate_no.") || (from_certificate_no <= ".$shipment->from_certificate_no." && to_certificate_no >= ".$shipment->from_certificate_no.")) && shipment_type = 'giftcard' && shipto_state != 1 && id != ".$shipment->id;
            $collisionShipments = \Shipment::find_by_sql($collisionSQL);
            if(count($collisionShipments) > 0) {
                //$this->blockShipment($shipment,"SHIPMENT_NUMBER_COLLISION","Shipment cards ".$shipment->from_certificate_no." - ".$shipment->to_certificate_no." collides with ".countgf($collisionShipments)." other shipments",true);
                return;
            }

            // Sync to navision
            if($this->syncShipmentToNav($shipment,$company,$companyOrder))
            {

                // Check if any shipments left, or update order to state 5
                if($companyOrder->order_state == 4) {
                    $missingShipmentList = \Shipment::find_by_sql("SELECT * FROM shipment WHERE shipment_type = 'giftcard' && shipment_state != 2 && companyorder_id = ".intval($companyOrder->id));
                    if(count($missingShipmentList) == 0) {
                        $companyOrder->order_state = 5;
                        $companyOrder->save();
                    }
                }

            }

        }

        // Sync earlyorder
        else if($shipment->shipment_type == "earlyorder") {

            if($cardshopSettings->earlyorder_print_language > 0) {
                $shipment->handle_country = $cardshopSettings->earlyorder_print_language;



                $shipment->save();
                $this->log("HANDLE COUNTRY SET TO: ".$shipment->handle_country."<br>");
            }

            // Sync to navision
            $this->syncShipmentToNav($shipment,$company,$companyOrder);
        }

        // Sync directdelivery
        else if($shipment->shipment_type == "directdelivery") {

            // Sync to navision
            $this->syncShipmentToNav($shipment,$company,$companyOrder);
        }

        // Sync private delivery
        else if($shipment->shipment_type == "privatedelivery") {

            // Load shopuser
            $shopuserList = \ShopUser::find_by_sql("SELECT * FROM shop_user where Username LIKE '".$shipment->from_certificate_no."' && is_giftcertificate = 1");
            if($shopuserList == null || count($shopuserList) == 0) {
                $this->blockShipment($shipment,"SHIPMENT_PRIVATE_CHECK","No shopuser found with username: ".$shipment->from_certificate_no);
                return;
            }
            $shopUser = $shopuserList[0];

            // Check not blocked
            if($shopUser->blocked == 1 || $shopUser->shutdown == 1) {
                $this->blockShipment($shipment,"SHIPMENT_PRIVATE_CHECK","Shopuser is blocked: ".$shipment->from_certificate_no);
                return;
            }

            // Sync
            $this->syncShipmentToNav($shipment,$company,$companyOrder,false);

        }

        // Unknown type
        else {
            $this->blockShipment($shipment,"SHIPMENT_UNKNOWN_TYPE","Unknown shipment type: ".$shipment->shipment_type);

        }



    }

    /**
     * SYNC TO NAVISION
     */

    private function syncShipmentToNav($shipment,$company,$companyOrder,$updateFromOrder=true)
    {


        // Check for default child values in old cardshop version and reset
        if($shipment->shipto_contact == "11111111") $shipment->shipto_contact = "";
        if($shipment->shipto_phone == "11111111") $shipment->shipto_phone = "";
        if($shipment->shipto_email == "11111111@11111111.dk") $shipment->shipto_email = "";

        if($updateFromOrder == true) {

            // Check data on shipment
            if(trimgf($shipment->shipto_contact) == "" || trimgf($shipment->shipto_phone) == "" || trimgf($shipment->shipto_email) == "") {

                // Find first shopuser and find if in child
                $shopuser = \ShopUser::find("first",array("conditions" => array("username" => $shipment->from_certificate_no,"company_order_id" => $shipment->companyorder_id,"is_giftcertificate" => 1)));
                if($shipment->shipment_type == 'giftcard' && $shopuser != null && $shopuser->id > 0 && $shopuser->company_id > 0 && $shopuser->company_id != $company->id) {

                    $childCompany = \Company::find($shopuser->company_id);

                    if(trimgf($shipment->shipto_contact) == "") {
                        if(trimgf($childCompany->ship_to_attention) != "") {

                            $shipment->shipto_contact = $childCompany->ship_to_attention;
                            $this->log("SHIPMENTUPDATE: Set contact name from child attention: ".$shipment->shipto_contact."");
                        } else if(trimgf($childCompany->contact_name) != "" && trimgf($childCompany->contact_name) != "11111111") {
                            $shipment->shipto_contact = $childCompany->contact_name;
                            $this->log("SHIPMENTUPDATE: Set contact name from child contact: ".$shipment->shipto_contact."");
                        }
                    }

                    if(trimgf($shipment->shipto_phone) == "" && trimgf($childCompany->contact_phone) != "" && trimgf($childCompany->contact_phone) != "11111111") {
                        $shipment->shipto_phone = str_replace(array(" ","-"),"",trim(trimgf($childCompany->contact_phone)));
                        $this->log("SHIPMENTUPDATE: Set contact phone from child: ".$shipment->shipto_phone."");
                    }

                    if(trimgf($shipment->shipto_email) == "" && trimgf($childCompany->contact_email) != "" && trimgf($childCompany->contact_email) != "11111111@11111111.dk") {
                        $shipment->shipto_email = trimgf($childCompany->contact_email);
                        $this->log("SHIPMENTUPDATE: Set contact email from child: ".$shipment->shipto_email."");
                    }

                }

                // Use parent to update shipment
                if(trimgf($shipment->shipto_contact) == "") {
                    if(trimgf($company->ship_to_attention) != "") {
                        $shipment->shipto_contact = $company->ship_to_attention;
                        $this->log( "SHIPMENTUPDATE: Set contact name from parent attention: ".$shipment->shipto_contact."");
                    } else if(trimgf($company->contact_name) != "" && trimgf($company->contact_name) != "11111111") {
                        $shipment->shipto_contact = $company->contact_name;
                        $this->log( "SHIPMENTUPDATE: Set contact name from parent contact: ".$shipment->shipto_contact."");
                    }
                }

                if(trimgf($shipment->shipto_phone) == "" && trimgf($company->contact_phone) != "" && trimgf($company->contact_phone) != "11111111") {
                    $shipment->shipto_phone = str_replace(array(" ","-"),"",trim(trimgf($company->contact_phone)));
                    $this->log( "SHIPMENTUPDATE: Set contact phone from parent contact: ".$shipment->shipto_phone."");
                }

                if(trimgf($shipment->shipto_email) == "" && trimgf($company->contact_email) != "" && trimgf($company->contact_email) != "11111111@11111111.dk") {
                    $shipment->shipto_email = trimgf($company->contact_email);
                    $this->log( "SHIPMENTUPDATE: Set contact email from parent contact: ".$shipment->shipto_email."");
                }

                // Save shipment
                $shipment->save();

            }
        }

        // shipto master, look for shipto address
        $shiptoMaster = null;
        if($shipment->shipto_state == 2) {
            $shiptoMaster = \Shipment::find('first',array("conditions" => array("companyorder_id" => $shipment->companyorder_id,"shipment_type" => "giftcard","shipto_state" => 1)));
        }

        // Handle nav country to send to
        $completeState = 2;
        $syncToCountry = $company->language_code;
        if($shipment->handle_country > 0)  {
            if($shipment->shipment_state < 5) {
                $syncToCountry = $shipment->handle_country;
            }
            else {
                $completeState = 6;
            }
        }

        // Create shipment xml
        try {

            /*
            if($syncToCountry == 5) {
                $this->log("Generate shipment xml v1");
                $xmlModel = new ShipmentXML($shipment,$shiptoMaster);
                $xmlDoc = $xmlModel->getXML();
            } else {

            }*/

            $this->log("Generate shipment xml v2");
            $xmlModel = new Shipment2XML($shipment,$shiptoMaster);
            $xmlDoc = $xmlModel->getXML();
            
            $this->log("<pre>".htmlentities($xmlDoc)."</pre>");
        }
        catch(\Exception $e)
        {
            $this->blockShipment($shipment,"SHIPMENT_XML_ERROR",$e->getMessage(),true);
            return false;
        }

/*
        echo "<pre>".htmlentities($xmlDoc)."</pre>";
        echo "<br>STOP NOW BEFORE SYNCING TO COUNTRY: ".$syncToCountry."!";
        exit();
*/

        if($shipment->shipment_state == 1 && $shipment->shipment_sync_date != null) {
            $this->blockShipment($shipment,"SHIPMENT_HAS_SYNCDATE","Syncdate already set, check it has not been synced already",true);
            return false;
        }
        
        // Sync shipment to nav
        try {

            // Get nav client
            $this->log("Prepare nav sync to ".$syncToCountry);


/*
            echo "SHIPMENT XML<br>";
            echo "<pre>";
            echo htmlentities($xmlDoc);
            echo "</pre>";
            echo "SEND TO COUNTRY: ".$syncToCountry;
            exit();
*/
            $client = $this->getOrderWS($syncToCountry);
            
            // Send order to navision
            try {
                $shipmentResponse = $client->uploadShipmentDoc($xmlDoc);
                $lastCallID = $client->getLastCallID();
                $this->log("Sync call to nav (call id ".$lastCallID."): ".$client->getLastOrderResponse());

                if($shipmentResponse) {
                    if($client->getLastOrderResponse() != "OK") {
                        throw new \Exception("Shipment synced but navision responded with non ok answer: ".$client->getLastOrderResponse());
                    } else {
                        $this->log("Order synced ok: ".$client->getLastOrderResponse());
                    }
                } else {
                    throw new \Exception("Could not upload order doc: ".$client->getLastError());
                }
            } catch (Exception $e) {
                $lastCallID = $client->getLastCallID();
                throw new \Exception("Error syncing document to navision (call id ".$lastCallID."): ".$e->getMessage());
            }

            // Update company order
            $this->log("SET SHIPMENT STATE: ".$completeState."");
            $shipment->shipment_state = $completeState;
            $shipment->shipment_sync_date = date('d-m-Y H:i:s');
            $shipment->save();

            if($shipment->shipment_type == "giftcard") {
                \ActionLog::logAction("ShipmentSynced", "Gavekort forsendelse for ".$companyOrder->order_no." sendt til ".$shipment->handler.($shipment->isshipment == 0 ? " (ikke fysisk levering)" : ""),"Gavekort: ".$shipment->from_certificate_no." til ".$shipment->to_certificate_no,0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,$shipment->id);
            } else if($shipment->shipment_type == "privatedelivery" || $shipment->shipment_type == "directdelivery") {
                try {
                    $cardOrder = \Order::find($shipment->to_certificate_no);
                    \ActionLog::logAction("ShipmentSynced", "Gavevalg forsendelse for ".$companyOrder->order_no." - kort ".$shipment->from_certificate_no." sendt til ".$shipment->handler.($shipment->isshipment == 0 ? " (ikke fysisk levering)" : ""),"Varenr: ".$shipment->itemno,0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,$cardOrder->shopuser_id,$cardOrder->id,$shipment->id);
                }
                catch(\Exception $e) {
                    $cardOrder = null;
                }

            } else  if($shipment->shipment_type == "earlyordre") {

                $details = "";
                if($shipment->quantity > 1) {
                    $details = "Varenr: ".$shipment->itemno." - ".$shipment->quantity." stk, ";
                }
                if($shipment->quantity2 > 1) {
                    $details .= "Varenr: ".$shipment->itemno2." - ".$shipment->quantity2." stk, ";
                }
                if($shipment->quantity3 > 1) {
                    $details .= "Varenr: ".$shipment->itemno3." - ".$shipment->quantity3." stk, ";
                }
                if($shipment->quantity4 > 1) {
                    $details .= "Varenr: ".$shipment->itemno4." - ".$shipment->quantity4." stk, ";
                }
                if($shipment->quantity5 > 1) {
                    $details .= "Varenr: ".$shipment->itemno5." - ".$shipment->quantity5." stk, ";
                }

                \ActionLog::logAction("ShipmentSynced", "Earlyordre forsendelse for ".$companyOrder->order_no." sendt til ".$shipment->handler.($shipment->isshipment == 0 ? " (ikke fysisk levering)" : ""),$details,0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,$shipment->id);
            }

            return true;

        } catch(\Exception $e) {

            // Output exception
            $this->log("Order sync exception: ".$e->getMessage());

            $isTechBlock = true;

            // If message contains "Invalid <shipto_email> specified", set to non tech block
            if(str_contains($e->getMessage(), "Invalid <shipto_email> specified")) {
                $isTechBlock = false;
            }

            // Block shipment
            $this->blockShipment($shipment,"SHIPMENT_SYNC_ERROR","Nav error: ".$e->getMessage(),$isTechBlock);

            return false;
        }
    }

    /**
     * BLOCK
     */

    private function blockShipment(\Shipment $shipment,$blockType,$description,$isTech=0)
    {
        $this->log(($isTech ? "TECH-" : "")."BLOCK: ".$blockType.": ".$description);
        $shipment->shipment_state = 3;
        $shipment->save();
        \BlockMessage::createShipmentBlock(0,$shipment->companyorder_id,$shipment->id,$blockType,$description,$isTech,$this->syncMessages);
    }

    /**
     * HELPERS
     */

    private $orderWs = array();
    private $messages = array();
    private $syncMessages = array();

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

    private function logNewSync() {
        $this->syncMessages = array();
    }

    private function log($message) {
        if($this->outputMessages) {
            echo $message."<br>\r\n";
        }
        $this->messages[] = $message;
        $this->syncMessages[] = $message;
    }

    private function loadWaiting()
    {


        $languages = array(1,4,5); // ,4,5
        $lockToSalesPerson = "";

        if($this->waitingShipmentList != null) return;

        $salesLockSQL = "";
        if($lockToSalesPerson != null && $lockToSalesPerson != null) {
            $salesLockSQL = " && company_order.salesperson = '".$lockToSalesPerson."'";
        }

        $shipEarlyOrders = false;

        // && !(shipment_type = 'earlyorder' && company.language_code = 5)
        // send forcesyncnow earlyorders by adding:  or (shipment_type = 'earlyorder' && shipment.force_syncnow = 1)
        // Old sql: "SELECT shipment.* FROM shipment, cardshop_settings, company_order, company WHERE ".(!$shipEarlyOrders ? "(shipment_type != 'earlyorder'  or (shipment_type = 'earlyorder' && shipment.force_syncnow = 1)) && " : "")." ((shipment_type != 'earlyorder' ) OR (shipment_type = 'earlyorder' && cardshop_settings.navsync_earlyorders = 1)) && (handler = 'navision' || handler = '')  && (shipment.shipto_state > 0 || shipment.shipment_type != 'giftcard') && company_order.company_id = company.id && company.language_code IN (".implode(",",$languages).") && shipment.shipment_state = 1 && shipment.companyorder_id = company_order.id && company_order.order_state > 3 && company_order.shipment_on_hold = 0 && company_order.shop_id = cardshop_settings.shop_id && (shipment.force_syncnow = 1 || (shipment.force_syncnow = 0 && (company_order.nav_lastsync IS NULL OR NOW() > DATE_ADD(company_order.nav_lastsync, INTERVAL cardshop_settings.shipment_syncwait HOUR))))".$salesLockSQL." ORDER BY series_master desc, from_certificate_no"

        $sql = "SELECT shipment.* 
FROM shipment, cardshop_settings, company_order, company 
WHERE
 (
    shipment_type NOT IN ('directdelivery', 'privatedelivery') 
    OR (
      shipment_type IN ('directdelivery', 'privatedelivery') 
      AND cardshop_settings.navsync_privatedelivery = 1
    )
  )
  and cardshop_settings.navsync_shipments = 1
  AND (
    (shipment_type != 'earlyorder') 
    OR (
      shipment_type = 'earlyorder' 
      AND (shipment.force_syncnow = 1 or cardshop_settings.navsync_earlyorders = 1)
    )
  )
  
  AND (handler = 'navision' OR handler = '') 
  AND (shipment.shipto_state > 0 OR shipment.shipment_type != 'giftcard') 
  AND company_order.company_id = company.id 
  AND company.language_code IN (".implode(",",$languages).") 
  AND shipment.shipment_state = 1 
  AND shipment.companyorder_id = company_order.id 
  AND company_order.order_state > 3 
  AND company_order.shipment_on_hold = 0 
  AND company_order.shop_id = cardshop_settings.shop_id 
  AND (
    shipment.force_syncnow = 1 
    OR (
      shipment.force_syncnow = 0 
      AND (
        company_order.nav_lastsync IS NULL 
        OR NOW() > DATE_ADD(company_order.nav_lastsync, INTERVAL cardshop_settings.shipment_syncwait HOUR)
      )
    )
  )  
ORDER BY series_master desc, from_certificate_no";

        $this->waitingShipmentList = \Shipment::find_by_sql($sql);
    }

    // Used by automatic job queue, only add things that should go automatically into production
    private function loadWaitingJob()
    {


        $shipEarlyOrders = false;
        $languages = array(1,4,5); // ,4,5

        //&& !(shipment_type = 'earlyorder' && company.language_code = 5)
        // old sql: SELECT shipment.* FROM shipment, cardshop_settings, company_order, company WHERE shipment_type != 'earlyorder' &&  (shipment_type not in ('directdelivery','privatedelivery') or (shipment_type in ('directdelivery','privatedelivery') and cardshop_settings.navsync_privatedelivery = 1)) &&  ".(!$shipEarlyOrders ? "(shipment_type != 'earlyorder'  or (shipment_type = 'earlyorder' && shipment.force_syncnow = 1)) && " : "")."  ((shipment_type != 'earlyorder' && cardshop_settings.navsync_shipments = 1) OR (shipment_type = 'earlyorder' && cardshop_settings.navsync_earlyorders = 1)) && (handler = 'navision' || handler = '')  && (shipment.shipto_state > 0 || shipment.shipment_type != 'giftcard') && company_order.company_id = company.id && company.language_code IN (".implode(",",$languages).") && shipment.shipment_state = 1 && shipment.companyorder_id = company_order.id && company_order.order_state > 3 && company_order.shipment_on_hold = 0 && company_order.shop_id = cardshop_settings.shop_id && (shipment.force_syncnow = 1 || (shipment.force_syncnow = 0 && (company_order.nav_lastsync IS NULL OR NOW() > DATE_ADD(company_order.nav_lastsync, INTERVAL cardshop_settings.shipment_syncwait HOUR)))) ORDER BY series_master desc, from_certificate_no

        $sql = "SELECT shipment.* 
FROM shipment, cardshop_settings, company_order, company 
WHERE
 (
    shipment_type NOT IN ('directdelivery', 'privatedelivery') 
    OR (
      shipment_type IN ('directdelivery', 'privatedelivery') 
      AND cardshop_settings.navsync_privatedelivery = 1
    )
  )
  and cardshop_settings.navsync_shipments = 1
  AND (
    (shipment_type != 'earlyorder') 
    OR (
      shipment_type = 'earlyorder' 
      AND (shipment.force_syncnow = 1 or cardshop_settings.navsync_earlyorders = 1)
    )
  )
  and shipment.id not in (select shipment_id from blockmessage where release_status = 0)
  AND (handler = 'navision' OR handler = '') 
  AND (shipment.shipto_state > 0 OR shipment.shipment_type != 'giftcard') 
  AND company_order.company_id = company.id 
  AND company.language_code IN (".implode(",",$languages).") 
  AND shipment.shipment_state = 1 
  AND shipment.companyorder_id = company_order.id 
  AND company_order.order_state > 3 
  AND company_order.shipment_on_hold = 0 
  AND company_order.shop_id = cardshop_settings.shop_id 
  AND (
    shipment.force_syncnow = 1 
    OR (
      shipment.force_syncnow = 0 
      AND (
        company_order.nav_lastsync IS NULL 
        OR NOW() > DATE_ADD(company_order.nav_lastsync, INTERVAL cardshop_settings.shipment_syncwait HOUR)
      )
    )
  )  
ORDER BY series_master desc, from_certificate_no";

        $this->waitingShipmentList = \Shipment::find_by_sql($sql);

    }



}

