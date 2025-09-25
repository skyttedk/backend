<?php

namespace GFUnit\navision\syncprivatedelivery;
use GFBiz\Model\Cardshop\ShopMetadata;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerObj;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\CustomerXML;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\OrderWS;
use GFCommon\Model\Navision\OrderXML;
use GFCommon\Model\Navision\ShipmentXML;

class PrivateDeliverySync
{

    private $outputMessages = false;
    private $maxInRun = 500;
    private $syncLanguages = array(1,5);
    private $minHoursOld = 26;
    private $deliveryStateMap = array();
    private $seSpaHotel = array('200325','200326','200327','sam1950','200327','sam1950','200325'); // Important, keep letters in lowercase
    private $seDonation = array('190704','200704','190701');
    private $daDonation = array('200424***11030077','190703','190701','190702','190712','190704');
    private $replacementCardCompanyList;

    public function __construct($output = false)
    {
        //\GFCommon\Model\Navision\NavClient::setNavDevMode(true);
        $this->outputMessages = $output;

        // Get replacement company id's from cardshop_settings
        $this->replacementCardCompanyList = array(44780,52468,45363,52469,52470,69437,69439,69451);
        $cardshopSettingsList = \CardshopSettings::find('all');
        foreach($cardshopSettingsList as $cardshopSettings) {
            if($cardshopSettings->replacement_company_id > 0 && !in_array($cardshopSettings->replacement_company_id,$this->replacementCardCompanyList)) {
                $this->replacementCardCompanyList[] = $cardshopSettings->replacement_company_id;
            }
        }

    }

    public function isLangActive($lang) {
        return in_array($lang,$this->syncLanguages);
    }

    public function runSync() {


        $this->log("Start manual private delivery sync");

        // Get next delivery
        $nextDeliveryList = $this->getNextDelivery();
        $this->log("Found ".countgf($nextDeliveryList)." shop users to sync");

        // Run
        foreach($nextDeliveryList as $nextDelivery) {
            $this->runPrivateDelivery($nextDelivery->id,$nextDelivery->order_id);
            $this->log("<br><hr><br>");
            \system::connection()->commit();
            \System::connection()->transaction();

        }

        $this->log("Done checking members: <pre>".print_r($this->deliveryStateMap,true)."</pre>");
        $this->log("Check shipments needing sync");

        \response::silentsuccess();

    }

    public function getNextDelivery() {

        $sql = "SELECT
            `shop_user`.`username`, `shop_user`.`id`,`order`.`id` as order_id, `shop_user`.`blocked`, `shop_user`.`is_delivery`, `shop_user`.`delivery_printed`, `shop_user`.`expire_date`, `order`.`present_name`, `order`.`present_model_name`, `order`.`shop_id`, `order`.order_timestamp
        FROM `order` INNER JOIN `shop_user` ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE `shop_user`.`delivery_print_date` IS NULL && (`shop_user`.`delivery_state` = 0 or (shop_user.navsync_date IS NOT NULL AND shop_user.navsync_date < NOW()))  && ".$this->getPrivateDeliveryCriteria()."
        ORDER BY `order`.order_timestamp ASC LIMIT ".intval($this->maxInRun);


        return \ShopUser::find_by_sql($sql);


    }

    public function getPrivateDeliveryCriteria() {

        $sql = "( `shop_user`.`blocked` =0 AND `shop_user`.`shutdown` =0
            AND `shop_user`.`is_delivery` = 1 AND shop_user.is_giftcertificate = 1
            AND (`order`.order_timestamp < (NOW() - INTERVAL ".intval($this->minHoursOld)." HOUR))
             ) AND shop_user.shop_id in (select shop_id from cardshop_settings where language_code in (".implode(",",$this->syncLanguages)."))";

        return $sql;

    }

    /**
     * RUN SINGLE SYNC
     */

    private function runPrivateDelivery($shopUserID,$OrderID)
    {

        $this->syncMessages = array();
        $this->log("Run sync on ".$shopUserID);
        $isSESpaHotel = false;
        $isDADonation = false;
        $isSEDonation = false;

        $missingUserDataErrors = array();

        // SHOP USER BLOCK
        try {

            // Load shopuser
            $shopuser = \ShopUser::find($shopUserID);

            if($shopuser == null || $shopUserID <= 0 || $shopuser->id <= 0 || $shopuser->id != intval($shopUserID))
            {
                return $this->setShopUserDeliveryState(null, 10, "Could not find shopuser ".$shopUserID);
            }
            else if($shopuser->delivery_state > 0 && !in_array($shopuser->delivery_state,ErrorCodes::getRetryStates()))
            {
                return $this->setShopUserDeliveryState(null, 11, "Cant sync shopuser ".$shopUserID." delivery_state is: ".$shopuser->delivery_state);
            }
            else if($shopuser->is_replaced == 1)
            {
                return $this->setShopUserDeliveryState($shopuser, 16, "Cant sync shopuser ".$shopUserID.", shopuser card replaced",false,24*3);
            }
            else if($shopuser->blocked == 1)
            {
                return $this->setShopUserDeliveryState($shopuser, 12, "Cant sync shopuser ".$shopUserID.", shopuser blocked",false,48);
            }
            else if($shopuser->shutdown == 1)
            {
                return $this->setShopUserDeliveryState($shopuser, 13, "Cant sync shopuser ".$shopUserID.", shopuser shutdown",false,24);
            }
            else if($shopuser->is_demo == 1)
            {
                return $this->setShopUserDeliveryState($shopuser, 14, "Cant sync shopuser ".$shopUserID.", is demo");
            }
            else if($shopuser->is_delivery == 0)
            {
                return $this->setShopUserDeliveryState($shopuser, 15, "Cant sync shopuser ".$shopUserID.", is not delivery",false,72);
            }

        } catch (\Exception $e) {
            return $this->setShopUserDeliveryState($shopuser, 19, "Cant sync shopuser ".$shopUserID.", shopuser block exception: ".$e->getMessage()." - ".$e->getFile()." - ".$e->getLine(),true,6);
        }

        // ORDER BLOCK
        try {

            // Load order
            $orderList = \Order::all(array('conditions' => array('shopuser_id' => $shopuser->id), 'limit' => 1));
            if(count($orderList) == 0) {
                return $this->setShopUserDeliveryState($shopuser, 21, "Cant sync shopuser ".$shopUserID.", no order is found",false,72);
            }

            // Check order
            $order = $orderList[0];
            if($order->id != $OrderID) {
                return $this->setShopUserDeliveryState($shopuser, 22, "Cant sync shopuser ".$shopUserID.", extracted order ".$order->id." not the same as orderid from  initial query ".$OrderID);
            }
            else if($order->shopuser_id != $shopuser->id) return $this->setShopUserDeliveryState($shopuser, 23, "Cant sync shopuser ".$shopUserID.", error in shopuser id");
            else if($order->shop_id != $shopuser->shop_id) return $this->setShopUserDeliveryState($shopuser, 24, "Cant sync shopuser ".$shopUserID.", error in shop id");
            //else if($order->company_id != $shopuser->company_id) return $this->setShopUserDeliveryState($shopuser, 25, "Cant sync shopuser ".$shopUserID.", error in company id");
            //else if($order->is_delivery != 1) return $this->setShopUserDeliveryState($shopuser, 26, "Cant sync shopuser ".$shopUserID.", order is not set to delivery");
            else if($order->is_demo != 0) return $this->setShopUserDeliveryState($shopuser, 27, "Cant sync shopuser ".$shopUserID.", order is set to demo");

        } catch (\Exception $e) {
            return $this->setShopUserDeliveryState($shopuser, 29, "Cant sync shopuser ".$shopUserID.", order block exception: ".$e->getMessage(),true);
        }

        // MISC CHECK BLOCK
        try {

            // Load shop
            $shop = \Shop::find($shopuser->shop_id);
            if($shop->id <= 0) return $this->setShopUserDeliveryState($shopuser, 31, "Cant sync shopuser ".$shopUserID.", could not find shop");

            // Load cardshop_settings
            $shopSettings = \CardshopSettings::getByShopID($shopuser->shop_id);
            if(!in_array($shopSettings->language_code,$this->syncLanguages)) {
                return $this->setShopUserDeliveryState($shopuser, 33, "Cant sync shopuser ".$shopUserID.", not in language");
            }
            // Get company_order
            $companyOrder = \CompanyOrder::find($shopuser->company_order_id);
            if($companyOrder->id == 0 || $companyOrder->id != $shopuser->company_order_id) {
                return $this->setShopUserDeliveryState($shopuser,35,"Cant sync shopuser ".$shopUserID.", cant find company order");
            }

            if(in_array($companyOrder->order_state,array(7,8))) {
                return $this->setShopUserDeliveryState($shopuser,36,"Cant sync shopuser ".$shopUserID.", order is deleted",false,36);
            }


            // Special date rules - dates cards can be released
            $usernameRules = array(
                "31600613" => 1642201200
            );

            if(isset($usernameRules[$shopuser->username])) {
                if($usernameRules[$shopuser->username] > time()) {
                    return $this->setShopUserDeliveryState($shopuser,37,"Shopuser ".$shopUserID.", delivery pushed to ".date("d.m.Y H:i:s",$usernameRules[$shopuser->username]),false,36);
                }
            }

            if(in_array($companyOrder->order_state,array(0,1,2,3)) && !in_array($companyOrder->company_id,$this->replacementCardCompanyList)) {
                return $this->setShopUserDeliveryState($shopuser,38,"Cant sync shopuser ".$shopUserID.", order not synced to nav yet",false,24);
            }

        } catch (\Exception $e) {
            return $this->setShopUserDeliveryState($shopuser, 39, "Cant sync shopuser ".$shopUserID.", misc block exception: ".$e->getMessage(),true);
        }

        // PRESENT CHECK
        try {

            // Check varenr
            $sql = "select * from present_model where model_id = ".$order->present_model_id." && present_id = ".$order->present_id." && language_id = 1";
            $presentModelList = \PresentModel::find_by_sql($sql);
            if(count($presentModelList) == 0) {
                return $this->setShopUserDeliveryState($shopuser, 41, "Cant sync shopuser ".$shopUserID.", cant find present model");
            }

            $presentModel = $presentModelList[0];
            if(trimgf($presentModel->model_present_no) == "") {
                return $this->setShopUserDeliveryState($shopuser, 42, "Cant sync shopuser ".$shopUserID.", missing varenr");
            }

            $isSESpaHotel = $shopSettings->language_code == 5 && in_array(trimgf(strtolower($presentModel->model_present_no)),$this->seSpaHotel);
            $isDADonation = $shopSettings->language_code == 1 && in_array(trimgf(strtolower($presentModel->model_present_no)),$this->daDonation);
            $isSEDonation = $shopSettings->language_code == 5 && in_array(trimgf(strtolower($presentModel->model_present_no)),$this->seDonation);

            if($isDADonation) {
                return $this->setShopUserDeliveryState($shopuser, 101, "Is da donation, stop processing");
            }

            if($isSEDonation) {
                return $this->setShopUserDeliveryState($shopuser, 101, "Is se donation, stop processing");
            }


            $navLang = $shopSettings->language_code;
            if($navLang == 5) $navLang = 1;
            $navItem = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = ".$navLang." AND `no` LIKE '".$presentModel->model_present_no."' AND `deleted` IS NULL");
            if(count($navItem) == 0) {
             //   return $this->setShopUserDeliveryState($shopuser, 43, "Cant sync shopuser ".$shopUserID.", varenr not in nav cache: ".$presentModel->model_present_no);
            }

        } catch (\Exception $e) {
            return $this->setShopUserDeliveryState($shopuser, 49, "Cant sync shopuser ".$shopUserID.", present block exception: ".$e->getMessage(),true);
        }

        // USER DATA
        try {

            //Load user settings
            $userData = $this->getUserData($shopUserID,$shopuser->shop_id);
            $this->log("User data <pre>".print_r($userData,true)."</pre>");
            echo "User data <pre>".print_r($userData,true)."</pre>";


            // Check for test users
            if($this->isTestData($userData)) {
                //return $this->setShopUserDeliveryState($shopuser, 50, "Cant sync shopuser ".$shopUserID.", looks like test user");
            }

            // Check for valid userdata
            if(trimgf($userData["name"]) == "") {
                $missingUserDataErrors[] = $shopuser->username." har ikke angivet navn.";
                //return $this->setShopUserDeliveryState($shopuser, 51, "Cant sync shopuser ".$shopUserID.", missing name");
            }
            else if(trimgf($userData["address"]) == "") {
                return $this->setShopUserDeliveryState($shopuser, 52, "Cant sync shopuser ".$shopUserID.", missing address");
            }
            else if(trimgf($userData["postnr"]) == "") {
                return $this->setShopUserDeliveryState($shopuser, 53, "Cant sync shopuser ".$shopUserID.", missing postal code");
            }
            else if(trimgf($userData["bynavn"]) == "") {
                return $this->setShopUserDeliveryState($shopuser, 54, "Cant sync shopuser ".$shopUserID.", missing city");
            }
            else if(trimgf($userData["land"]) == "") {
                return $this->setShopUserDeliveryState($shopuser, 55, "Cant sync shopuser ".$shopUserID.", missing country");
            }
            else if(trimgf($userData["telefon"]) == "" && $isSESpaHotel == false) {
                $missingUserDataErrors[] = $shopuser->username." mangler telefon nr.";
                //return $this->setShopUserDeliveryState($shopuser, 56, "Cant sync shopuser ".$shopUserID.", missing phone");
            }
            else if(trimgf($userData["email"]) == "") {
                $missingUserDataErrors[] = $shopuser->username." mangler e-mail";
                //return $this->setShopUserDeliveryState($shopuser, 57, "Cant sync shopuser ".$shopUserID.", missing e-mail");
            }
            else if($this->validEmail($userData["email"]) == false) {
                $missingUserDataErrors[] = $shopuser->username." e-mail er ikke gyldig";
                //return $this->setShopUserDeliveryState($shopuser, 58, "Cant sync shopuser ".$shopUserID.", e-mail invalid");
            }

        } catch (\Exception $e) {
            return $this->setShopUserDeliveryState($shopuser, 59, "Cant sync shopuser ".$shopUserID.", present block exception: ".$e->getMessage()." - ".$e->getFile()." @ ".$e->getLine(),true);
        }

        // CHECK READY FOR SHIPMENT
        try {

            $company = \Company::find($companyOrder->company_id);

            // Set try again
            $masterAllow = ($companyOrder->allow_delivery == 1 || $company->allow_delivery == 1);

            // If allow delivery = -1, then permanently do not deliver
            if($companyOrder->allow_delivery == -1 || $company->allow_delivery == -1) {
                return $this->setShopUserDeliveryState($shopuser, 100, "Shopuser ".$shopUserID." on non-delivery order.",false);
            }

            // Replacement orders, allow always
            if(in_array($companyOrder->company_id,$this->replacementCardCompanyList)) {
                $masterAllow = true;
            }

            if($masterAllow) $this->log("- Master allow set on order!");

            if($companyOrder->shipment_on_hold == 1 && !$masterAllow) {
                return $this->setShopUserDeliveryState($shopuser, 61, "Cant sync shopuser ".$shopUserID." now, shipments on hold on order, try again later",false,5);
            }
            else if($companyOrder->order_state <= 3 && !$masterAllow) {
                return $this->setShopUserDeliveryState($shopuser, 62, "Cant sync shopuser ".$shopUserID." now, order not created in navision, try again later",false,5);
            }
            else if(!$masterAllow) {
                $client = $this->getOrderWS($shopSettings->language_code);
                $orderStatus = $client->getStatus($companyOrder->order_no);
                if($orderStatus == null) {
                    return $this->setShopUserDeliveryState($shopuser, 63, "Cant sync shopuser ".$shopUserID." now, no navision order data response, try again later",false,3);
                }
                else if(intval($orderStatus->getRemPrepaymentAmountLCY()) > 0) {
                    return $this->setShopUserDeliveryState($shopuser, 64, "Cant sync shopuser ".$shopUserID." now, due amount not paid, try again later",false,12);
                } else {

                    // No prepayment amount and prepayment set, then block (we wait for prepayment)
                    if(intval($orderStatus->getPrepaymentAmount()) == 0 && $companyOrder->prepayment == 1) {
                        return $this->setShopUserDeliveryState($shopuser, 64, "Cant sync shopuser ".$shopUserID." now, due amount not paid, try again later",false,12);
                    }

                    $this->log("- NAV payment check OK!");

                }
            }

            // If ok to send, check any others with not paid state 64:
            $checkUserList = \ShopUser::find_by_sql("SELECT * FROM shop_user where company_order_id = ".$shopuser->company_order_id." && delivery_state = 64 && id != ".$shopuser->id);
            if(count($checkUserList) > 0) {
                foreach($checkUserList as $checkUser) {
                    $checkUserUpdate = \ShopUser::find($checkUser->id);
                    if($checkUser->delivery_state == 64 && $checkUser->company_order_id == $shopuser->company_order_id && $checkUser->id != $shopuser->id) {
                        $checkUserUpdate->delivery_state = 0;
                        $checkUserUpdate->save();
                    }
                }
            }

        } catch (\Exception $e) {
            return $this->setShopUserDeliveryState($shopuser, 69, "Cant sync shopuser ".$shopUserID.", shipment ready block exception: ".$e->getMessage(),true);
        }

        // CREATE SHIPMENT
        try {

            $shipment = new \Shipment();
            $shipment->companyorder_id = $companyOrder->id;
            $shipment->created_date = date('d-m-Y H:i:s');
            $shipment->shipment_type = "privatedelivery";
            $shipment->handler = $shopSettings->privatedelivery_handler;
            $shipment->quantity = 1;
            $shipment->itemno = $presentModel->model_present_no;

            $shipment->description = mb_substr($presentModel->model_name,0,48);

            if(($shipment->handler == "glsexport" || $shipment->handler == "mydsv" || $shipment->handler == "navision" || $shipment->handler == "dpse") && $isSESpaHotel) {
                $shipment->handler = "sespahotel";
            }

            $shipment->itemno2 = "";
            $shipment->quantity2 = 0;
            $shipment->description2 = "";

            $shipment->itemno3 = "";
            $shipment->quantity3 = 0;
            $shipment->description3 = "";

            $shipment->itemno4 = "";
            $shipment->quantity4 = 0;
            $shipment->description4 = "";

            $shipment->itemno5 = "";
            $shipment->quantity5 = 0;
            $shipment->description5 = "";

            $shipment->isshipment = 1;
            $shipment->from_certificate_no = $shopuser->username;
            $shipment->to_certificate_no = $order->id;

            $shipment->shipto_name = $userData["name"];
            $shipment->shipto_address = $userData["address"];
            $shipment->shipto_address2 = $userData["address2"];
            $shipment->shipto_postcode = $userData["postnr"];
            $shipment->shipto_city = $userData["bynavn"];
            $shipment->shipto_country = $userData["land"];
            $shipment->shipto_contact = $userData["name"];
            $shipment->shipto_email = $userData["email"];
            $shipment->shipto_phone = str_replace(array(" ","-"),"",trim(trim(utf8_encode($userData["telefon"]))));

            $shipment->shipment_note = "";
            $shipment->gls_shipment = 0;
            $shipment->shipment_state = 1;
            $shipment->series_master = 0;
            $shipment->shipto_state = 8;

            if(trimgf($companyOrder->default_delivery_country) != "") {
                $shipment->shipto_country = $companyOrder->default_delivery_country;
            }
            
            // Check for foreign country, set shipment state 9 and wait for manual check
            $matchList = array("Norge","Norway","Finland","Tyskland","Germany");
            if($shipment->shipto_country == "Danmark") { $matchList[] = "Sverige"; $matchList[] = "Sweden"; }
            else { $matchList[] = "Danmark"; $matchList[] = "Denmark"; }
            if($this->stringMatchesArray($shipment->shipto_city,$matchList) || $this->stringMatchesArray($shipment->shipto_address2,$matchList)) {
                $shipment->shipment_state = 9;

            }

            $shipment->handle_country = $shopSettings->language_code;
            if($shopSettings->shipment_print_language > 0) {
                $shipment->handle_country = $shopSettings->shipment_print_language;
                $this->log("HANDLE COUNTRY SET TO: ".$shipment->handle_country);
            }

            // Check if shipment exists
            $checkShipment = \Shipment::find_by_sql("SELECT * FROM shipment WHERE shipment_type = 'privatedelivery' && from_certificate_no = '".$shopuser->username."' && to_certificate_no = '".$order->id."'");
            if(count($checkShipment) > 0 ) {
                return $this->setShopUserDeliveryState($shopuser, 71, "Cant sync shopuser ".$shopUserID.", shipment already exists in shipment list for same order",false);
            }

            // If replacement order and is norweigan, then set country to norway
            if($shopuser->replacement_id > 0 && $shopSettings->language_code == 1) {
                try {
                    $replacedCard = \ShopUser::find($shopuser->replacement_id);
                    if($replacedCard != null && $replacedCard->id > 0 && $shopuser->shop_id != $replacedCard->shop_id) {
                        $replacedSettings = \CardshopSettings::getByShopID($replacedCard->shop_id);
                        if($replacedSettings->language_code == 4) {
                            $shipment->shipto_country = "Norge";
                        }
                    }
                } catch (\Exception $e) {
                    $this->log(" - Problem checking if Norweigan replacement card: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            return $this->setShopUserDeliveryState($shopuser, 79, "Cant sync shopuser ".$shopUserID.", prepare shipment block exception: ".$e->getMessage(),true);
        }

        // GENERATE SHIPMENT XML
        try {

            $this->log("Generate shipment xml");
            $xmlModel = new ShipmentXML($shipment,null);
            $xmlModel->enableTestOnly();
            $xmlDoc = $xmlModel->getXML();
            $this->log("<pre>".htmlentities($xmlDoc)."</pre>");

        } catch (\Exception $e) {

            if(trimgf($e->getMessage()) == "Invalid se postal code, not 5 digits" || trimgf($e->getMessage()) == "Invalid dk postal code, not 4 digits") {
                $shipment->shipment_state = 9;
            } else {
                return $this->setShopUserDeliveryState($shopuser, 89, "Cant sync shopuser ".$shopUserID.", shipment xml generation block exception: ".$e->getMessage(),true);
            }


        }

        // SYNC SHIPMENT TO NAV
        try {

            // Save shipment
            $shipment->save();

            // Mark shopuser as processed
            $this->setShopUserDeliveryState($shopuser, 1, "Shipment created: ".$shipment->id,false);
            $shopuser->delivery_state = 1;
            $shopuser->delivery_print_date = date('d-m-Y H:i:s');
            $shopuser->navsync_response = $shipment->id;
            $shopuser->save();

            // Log
            $this->log(" - Created shipment ".$shipment->id);
            $this->log("Shipment created and shopuser updated (state 1)");

            if($shipment->shipment_state == 9) {
                $this->log(" - Shipment state 9, manual check needed");
                \BlockMessage::createShipmentBlock($shopuser->company_id, $companyOrder->id, $shipment->id, "FOREIGN_DELIVERY","Kortnr ".$shopuser->username." ligner en udenlands levering, tjek adresse og land og godkend hvis ok, ellers bloker for levering.");
                $shipment->shipment_state = 1;
                $shipment->save();
            }

            if(count($missingUserDataErrors) > 0) {
                $this->log(" - Has ".count($missingUserDataErrors)." missing user data errors");
                foreach($missingUserDataErrors as $error) {
                    \BlockMessage::createShipmentBlock($shopuser->company_id, $companyOrder->id, $shipment->id, "MISSING_USER_DATA",$error);
                }
            }

            // If hardcoded companyorders or companies make a manuel error message
            $manualCheckCompanyIDList = array();
            $manualCheckCompanyOrderIDList = array(53446,62893);

            if(in_array($shopuser->company_id, $manualCheckCompanyIDList) || in_array($shopuser->company_order_id, $manualCheckCompanyOrderIDList)) {
                \BlockMessage::createShipmentBlock($shopuser->company_id, $companyOrder->id, $shipment->id, "MANUAL_CHECK","Manuel check af levering til kortnr ".$shopuser->username.", tjek adresse og land og godkend hvis ok, ellers ret adresse.");
            }


        } catch (\Exception $e) {
            //echo $e->getMessage()." - ".$e->getFile()." - ".$e->getLine()."<br>";
            return $this->setShopUserDeliveryState($shopuser, 99, "Cant sync shopuser ".$shopUserID.", navsync block exception [".$shipment->description."]: ".$e->getMessage()."<pre>".print_r($shipment,true)."</pre>",true);
        }

        return true;

    }

    private function stringMatchesArray($string,$arrayMatch) {

        if(!is_array($arrayMatch)) return false;
        if(count($arrayMatch) == 0) return false;
        $stringCheck = strtolower($string);
        foreach($arrayMatch as $matchStr) {
            $matchStrLow = strtolower($matchStr);
            if(strstr($stringCheck,$matchStrLow) !== false) {
                return true;
            }
        }
        return false;
    }

    private function setShopUserDeliveryState($shopUser,$state,$message,$sendMail=false,$tryAgainTime=null)
    {


        // Count states all shops
        if(!isset($this->deliveryStateMap["all"])) $this->deliveryStateMap["all"] = array();
        if(!isset($this->deliveryStateMap["all"][$state])) $this->deliveryStateMap["all"][$state] = 0;
        $this->deliveryStateMap["all"][$state]++;

        // Count states per shop
        if($shopUser != null) {
            if(!isset($this->deliveryStateMap["shop_".$shopUser->shop_id])) $this->deliveryStateMap["shop_".$shopUser->shop_id] = array();
            if(!isset($this->deliveryStateMap["shop_".$shopUser->shop_id][$state])) $this->deliveryStateMap["shop_".$shopUser->shop_id][$state] = 0;
            $this->deliveryStateMap["shop_".$shopUser->shop_id][$state]++;
        }

        // Output log
        $this->log("- SET DELIVERY STATE ".$state.": ".$message);

        // No shopuser
        if($shopUser == null) {
            $sendMail = true;
        }
        // Shop user
        else {

            // Try again
            if($tryAgainTime != null && $tryAgainTime > 0) {
                $shopUser->delivery_state = $state;
                $shopUser->navsync_date = date('d-m-Y H:i:s',time()+(60*60*$tryAgainTime));
                $this->log("Retry again at: ".date('d-m-Y H:i:s',time()+(60*60*$tryAgainTime)));
            }
            // Set state (do not try again)
            else {
                $shopUser->navsync_date = null;
                $shopUser->delivery_state = $state;
            }

            $shopUser->save();

        }

        // Send error e-mail
        if($sendMail) {
            if($shopUser == null) {
                $this->mailLog("unknown shopuser - state ".$state,"Unknown shopuser, state: ".$state."<br>".$message."<br><br>");
            } else {
                $this->mailLog("[".$shopUser->id."] ".$shopUser->username." - state ".$state,"Unknown shopuser, state: ".$state."<br>".$message."<br><br>");
            }
        }

        return false;
    }

    private function isTestData($userData) {
        if(strstr($userData["email"],"@interactive.dk")) return true;
        else if(strstr($userData["email"],"@gavefabrikken.dk")) return true;
        else if(strstr($userData["email"],"@bitworks.dk")) return true;
        else if(strstr($userData["email"],"@gavefabrikken.no")) return true;
        else if(strstr($userData["email"],"@presentbolaget.se")) return true;
        return false;
    }

    /**
     * NAVISION
     */

    private $orderWs = array();

    private function getOrderWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create order service with no nav country");
        }
        if(isset($this->orderWs[intval($countryCode)])) {
            return $this->orderWs[intval($countryCode)];
        }
        $this->orderWs[intval($countryCode)] = new \GFCommon\Model\Navision\OrderStatusWS(intval($countryCode));
        return $this->orderWs[intval($countryCode)];
    }

    /**
     * LOAD USER DATA
     */

    public function getUserData($shopuserid,$shopid)
    {

        $nameAttributes = ShopMetadata::getNameAttrList();
        $adress1Attributes = ShopMetadata::getAddress1AttrList();
        $adress2Attributes = ShopMetadata::getAddress2AttrList();
        $postnrAttributes = ShopMetadata::getZipAttrList();
        $bynavnAttributes = ShopMetadata::getCityAttrList();
        $emailAttributes = ShopMetadata::getEmailAttrList();
        $phoneAttributes = ShopMetadata::getPhoneAttrList();

        $shopuserData = array(
            "name" => "-",
            "address" => "-",
            "address2" => "-",
            "postnr" => "-",
            "bynavn" => "-",
            "land" => $this->getCountry($shopid),
            "telefon" => "-",
            "email" => "-"
        );

        $userAttributes = \UserAttribute::find_by_sql("SELECT * FROM user_attribute WHERE shopuser_id = ".$shopuserid);
        foreach($userAttributes as $attribute) {
            if(in_array($attribute->attribute_id,$nameAttributes)) $shopuserData["name"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$adress1Attributes)) $shopuserData["address"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$adress2Attributes)) $shopuserData["address2"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$postnrAttributes)) $shopuserData["postnr"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$bynavnAttributes)) $shopuserData["bynavn"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$emailAttributes)) $shopuserData["email"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$phoneAttributes)) $shopuserData["telefon"] = $attribute->attribute_value;
        }

        if(trimgf($shopuserData["address"]) == "" && trimgf($shopuserData["address2"]) != "") {
            $shopuserData["address"] = $shopuserData["address2"];
            $shopuserData["address2"] = "";
        }
        $shopuserData["email"] = trimgf(str_replace(array(" ",",","@@"),array("",".","@"),$shopuserData["email"]));

        return $shopuserData;
    }

    private function getCountry($shopid)
    {
        return ShopMetadata::getShopCountry($shopid);
    }

    /**
     * HELPERS
     */

    private $messages = array();
    private $syncMessages = array();

    private function log($message) {
        if($this->outputMessages) {
            echo $message."<br>\r\n";
        }
        $this->messages[] = $message;
        $this->syncMessages[] = $message;
    }

    protected function mailLog($subject,$content)
    {

        $modtager = "sc@interactive.dk";
        $message = "Private delivery log<br><br>".$content."\r\n<br>\r\n<br>Sync log:<br>\r\n".implode("<br>",$this->syncMessages)."";
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";

        $result = mailgf($modtager,"pdlog: ".$subject, $message, $headers);

    }


    /* VALIDATE EMAIL */


    private function validEmail($email)
    {
        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex) {
            $isValid = false;
        } else {
            $domain = substr($email, $atIndex + 1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64) {
                // local part length exceeded
                $isValid = false;
            } else if ($domainLen < 1 || $domainLen > 255) {
                // domain part length exceeded
                $isValid = false;
            } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
                // local part starts or ends with '.'
                $isValid = false;
            } else if (strstr($local,"..") !== false) {
                // local part has two consecutive dots
                $isValid = false;
            }  else if (strstr($domain,"..") !== false) {
                // domain part has two consecutive dots
                $isValid = false;
            } else if (strstr($domain,".") == false) {
                // domain has no dot
                $isValid = false;
            }

            $domainSplit = explode(".",$domain);
            if(strlen(trimgf($domainSplit[0])) == 0) $isValid = false;
            if(!isset($domainSplit[1]) || strlen(trimgf($domainSplit[1])) == 0) $isValid = false;

        }
        return $isValid;

        /*
        return filter_var($email, FILTER_VALIDATE_EMAIL);

        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex) {
            $isValid = false;
        } else {
            $domain = substr($email, $atIndex + 1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64) {
                // local part length exceeded
                $isValid = false;
            } else if ($domainLen < 1 || $domainLen > 255) {
                // domain part length exceeded
                $isValid = false;
            } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
                // local part starts or ends with '.'
                $isValid = false;
            } else if (preg_match('/\\.\\./', $local)) {
                // local part has two consecutive dots
                $isValid = false;
            } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                // character not valid in domain part
                $isValid = false;
            } else if (preg_match('/\\.\\./', $domain)) {
                // domain part has two consecutive dots
                $isValid = false;
            } else if
            (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                str_replace("\\\\", "", $local))) {
                // character not valid in local part unless
                // local part is quoted
                if (!preg_match('/^"(\\\\"|[^"])+"$/',
                    str_replace("\\\\", "", $local))) {
                    $isValid = false;
                }
            }

        }
        return $isValid;
        */
    }


}