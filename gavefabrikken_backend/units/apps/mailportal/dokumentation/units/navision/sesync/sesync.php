<?php

namespace GFUnit\navision\sesync;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerObj;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\CustomerXML;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\OrderWS;
use GFCommon\Model\Navision\OrderXML;
use GFCommon\Model\Navision\ShipmentXML;
use GFUnit\navision\syncshipment\ShiptoCheck;


class SESync
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

        $this->log("Start shipment sync");

        // Preprocess checks
        $shiptoCheck = new ShiptoCheck();
        $shiptoCheck->run($this->outputMessages);

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
            if($counter >= 10)return;

        }
        $this->waitingShipmentList = null;
    }

    public function showNext()
    {
        $this->loadWaiting();
        echo "Waiting for sync: ".countgf($this->waitingShipmentList)."<br>";
        foreach($this->waitingShipmentList as $shipment) {
            $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
            echo $companyOrder->order_no.": ".$companyOrder->company_name." | <b>".$shipment->id."</b>: ".$shipment->shipment_type." -".$shipment->shipto_state." (".($shipment->isshipment == 1 ? "physical" : "email").") - ".$shipment->shipto_name."<br>";
        }
    }


    /**
     * START SYNC
     */


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
        $validStates = array(1,2,3,4,5);
        $blockStates = array(7,8);
        if(!in_array($companyOrder->order_state,$validStates)) {
            if(in_array($companyOrder->order_state,$blockStates)) {
                $shipment->shipment_state = 4;
                $shipment->save();
                //$this->blockShipment($shipment,"SHIPMENT_INVALID_ORDERSTATE","Invalid order state for shipments: ".$companyOrder->order_state,1);
                return;
            }
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

        // Check valid email
        if(trimgf($shipment->shipto_email) != "" && filter_var($shipment->shipto_email,FILTER_VALIDATE_EMAIL) == false) {
            $this->blockShipment($shipment,"SHIPMENT_EMAIL_INVALID","Leverance e-mail ikke gyldig: ".$shipment->shipto_email,true);
            return;
        }

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

                $cardshopSettings = \CardshopSettings::find(array("conditions" => array("shop_id" => $companyOrder->shop_id)));
                echo $cardshopSettings->shipment_print_language."<br>";
                if($cardshopSettings->shipment_print_language > 0) {
                    $shipment->handle_country = $cardshopSettings->shipment_print_language;
                    $shipment->save();
                    $this->log("HANDLE COUNTRY SET TO: ".$shipment->handle_country."<br>");
                }
            }

            if($shipment->handle_country == 0) {
                echo "HANDLE COUNTRY NOT SET!";
                exit();
            }

            // Check other shipments in db not overlapping on numbers
            $collisionSQL = "SELECT * FROM `shipment` WHERE ((from_certificate_no <= ".$shipment->to_certificate_no." && to_certificate_no >= ".$shipment->to_certificate_no.") || (from_certificate_no <= ".$shipment->from_certificate_no." && to_certificate_no >= ".$shipment->from_certificate_no.")) && shipment_type = 'giftcard' && shipto_state != 1 && id != ".$shipment->id;
            $collisionShipments = \Shipment::find_by_sql($collisionSQL);
            if(count($collisionShipments) > 0) {
                $this->blockShipment($shipment,"SHIPMENT_NUMBER_COLLISION","Shipment cards ".$shipment->from_certificate_no." - ".$shipment->to_certificate_no." collides with ".countgf($collisionShipments)." other shipments",true);
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

        /*
        // Sync earlyorder
        else if($shipment->shipment_type == "earlyorder") {

            // Sync to navision
            $this->syncShipmentToNav($shipment,$company,$companyOrder);
        }

        // Sync private delivery
        else if($shipment->shipment_type == "privatedelivery") {

            // Load shopuser

            // Check not blocked

            // Check not set to sync

            // Sync

        }
        */
        // Unknown type
        else {
            $this->blockShipment($shipment,"SHIPMENT_UNKNOWN_TYPE","Unknown shipment type: ".$shipment->shipment_type);

        }



    }

    /**
     * SYNC TO NAVISION
     */

    private function syncShipmentToNav($shipment,$company,$companyOrder)
    {

        // Check for default child values in old cardshop version and reset
        if($shipment->shipto_contact == "11111111") $shipment->shipto_contact = "";
        if($shipment->shipto_phone == "11111111") $shipment->shipto_phone = "";
        if($shipment->shipto_email == "11111111@11111111.dk") $shipment->shipto_email = "";

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
                    $shipment->shipto_phone = trimgf($childCompany->contact_phone);
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
                $shipment->shipto_phone = trimgf($company->contact_phone);
                $this->log( "SHIPMENTUPDATE: Set contact phone from parent contact: ".$shipment->shipto_phone."");
            }

            if(trimgf($shipment->shipto_email) == "" && trimgf($company->contact_email) != "" && trimgf($company->contact_email) != "11111111@11111111.dk") {
                $shipment->shipto_email = trimgf($company->contact_email);
                $this->log( "SHIPMENTUPDATE: Set contact email from parent contact: ".$shipment->shipto_email."");
            }

            // Save shipment
            $shipment->save();

        }

        // shipto master, look for shipto address
        $shiptoMaster = null;
        if($shipment->shipto_state == 2) {

            $shiptoMaster = \Shipment::find('first',array("conditions" => array("companyorder_id" => $shipment->companyorder_id,"shipment_type" => "giftcard","shipto_state" => 1)));

        }

        // If has uuid, block temporarily
        /*if(trimgf($shipment->series_uuid) != "") {
            $this->blockShipment($shipment,"SHIPMENT_UUID","Has uuid: ".$shipment->series_uuid,true);
            return false;
        }*/

        // Create shipment xml
        try {
            $this->log("Generate shipment xml");
            $xmlModel = new ShipmentXML($shipment,$shiptoMaster);
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
        echo "<br>STOP NOW!";
        exit();
*/
        if($shipment->shipment_state == 1 && $shipment->shipment_sync_date != null) {
            $this->blockShipment($shipment,"SHIPMENT_HAS_SYNCDATE","Syncdate already set, check it has not been synced already",true);
            return false;
        }

        // Sync shipment to nav
        try {

            // Get nav client
            $this->log("Prepare nav sync");

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
            return true;

        } catch(\Exception $e) {

            // Output exception
            $this->log("Order sync exception: ".$e->getMessage());

            // Block shipment
            $this->blockShipment($shipment,"SHIPMENT_SYNC_ERROR","Nav error: ".$e->getMessage(),true);

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
        $sql = "select * from shipment where companyorder_id in (SELECT id FROM company_order WHERE shop_id IN (select shop_id from cardshop_settings where language_code = 5) && created_datetime > '2021-10-06 14:00:00') && isshipment = 1 && shipment_type = 'giftcard' && shipment_state = 1";
        $this->waitingShipmentList = \Shipment::find_by_sql($sql);
    }


}

