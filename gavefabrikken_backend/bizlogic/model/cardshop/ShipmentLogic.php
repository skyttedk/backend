<?php

namespace GFBiz\Model\Cardshop;

use ActiveRecord\DateTime;

class ShipmentLogic
{
    
    const SHIPMENTTYPE_GIFTCARD = "giftcard";
    const SHIPMENTTYPE_EARLYORDER = "earlyorder";
    
    private $shipmentType;
    
    public function __construct($shipmentType)
    {
        $this->shipmentType = $shipmentType;
        if($this->shipmentType != self::SHIPMENTTYPE_GIFTCARD && $this->shipmentType != self::SHIPMENTTYPE_EARLYORDER) {
            throw new \Exception("Invalid shipmenttype: ".$shipmentType);
        }
    }

    /**
     * GENERAL HELPERS
     */
    
    public function getShipmentType() {
        return $this->shipmentType;
    }

    public function isCardShipment() {
        return $this->getShipmentType() == self::SHIPMENTTYPE_GIFTCARD;
    }

    public function isEarlyOrder() {
        return $this->getShipmentType() == self::SHIPMENTTYPE_EARLYORDER;
    }

    /**
     * LIST FUNCTIONALITY
     */
    
    public function getCompanyShipments($companyid)
    {
        return \Shipment::find_by_sql("SELECT shipment.*, company_order.order_no, company_order.order_state FROM shipment, company_order WHERE shipment.companyorder_id = company_order.id and shipment.shipment_type = '".$this->shipmentType."' && company_order.company_id = ".intval($companyid)."");
    }
    
    public function getCompanyOrderShipments($companyorderid)
    {
        return \Shipment::find_by_sql("SELECT shipment.*, company_order.order_no, company_order.order_state FROM shipment, company_order WHERE shipment.companyorder_id = company_order.id and shipment_type = '".$this->shipmentType."' && companyorder_id = ".intval($companyorderid)."");
    }
    
    public function outputList($shipmentList)
    {
        $mappedList = array();
        if(count($shipmentList) > 0) {
            foreach($shipmentList as $shipment) {
                $mappedList[] = $this->mapToJson($shipment);
            }
        }
        echo json_encode(array("status" => 1, "shipmentlist" => $mappedList),JSON_PRETTY_PRINT);
    }
    
    public function mapToJson(\Shipment $shipment)
    {
        $data = json_decode($shipment->to_json(),true);
        unset($data["description"]);
        unset($data["description2"]);
        unset($data["description3"]);
        unset($data["description4"]);
        unset($data["description5"]);
        return $data;
    }

    /**
     * UPDATE FUNCTIONS
     */
    
    public function createShipment($shipmentData) {

        // Set data
        $shipment = new \Shipment();
        $shipment->companyorder_id = intval($shipmentData["companyorder_id"]);
        $shipment->shipment_type = $this->shipmentType;

        $shipment->isshipment = 1;
        $shipment->shipment_state = 0;

        if(isset($shipmentData["gls_shipment"])) {
            $shipment->gls_shipment = (intval($shipmentData["gls_shipment"]) == 1 ? 1 : 0);
        }

        if(isset($shipmentData["shipment_note"])) {
            $shipment->shipment_note = trimgf($shipmentData["shipment_note"]);
        }

        // Early order data
        if($this->isEarlyOrder()) {

            $shipment->shipment_state = 1;
            $shipment->itemno = trimgf($shipmentData["itemno"]);
            $shipment->from_certificate_no = 0;
            $shipment->to_certificate_no = 0;
            $shipment->quantity = intval($shipmentData["quantity"]);

            if(isset($shipmentData["quantity2"])) {
                $shipment->quantity2 = intval($shipmentData["quantity2"]);
                $shipment->itemno2 = trimgf($shipmentData["itemno2"]);
            }

            if(isset($shipmentData["quantity3"])) {
                $shipment->quantity3 = intval($shipmentData["quantity3"]);
                $shipment->itemno3 = trimgf($shipmentData["itemno3"]);
            }

            if(isset($shipmentData["quantity4"])) {
                $shipment->quantity4 = intval($shipmentData["quantity4"]);
                $shipment->itemno4 = trimgf($shipmentData["itemno4"]);
            }

            if(isset($shipmentData["quantity5"])) {
                $shipment->quantity5 = intval($shipmentData["quantity5"]);
                $shipment->itemno5 = trimgf($shipmentData["itemno5"]);
            }

        }

        // Card shipment data
        if($this->isCardShipment()) {
            $shipment->itemno = "";
            $shipment->from_certificate_no = intval($shipmentData["from_certificate_no"]);
            $shipment->to_certificate_no = intval($shipmentData["to_certificate_no"]);
            $shipment->quantity = $shipment->to_certificate_no-$shipment->from_certificate_no+1;
        }

        // Shipment address
        $shipment->shipto_name = $shipmentData["shipto_name"];
        $shipment->shipto_address = $shipmentData["shipto_address"];
        $shipment->shipto_address2 = $shipmentData["shipto_address2"];
        $shipment->shipto_postcode = $shipmentData["shipto_postcode"];
        $shipment->shipto_city = $shipmentData["shipto_city"];
        $shipment->shipto_country = $shipmentData["shipto_country"];
        $shipment->shipto_contact = $shipmentData["shipto_contact"];
        $shipment->shipto_email = $shipmentData["shipto_email"];
        $shipment->shipto_phone = str_replace(array(" ","-"),"",trim($shipmentData["shipto_phone"]));

        // Check shipment
        $this->checkShipement($shipment);

        // Set series
        if($this->isCardShipment()) {
            if (isset($shipmentData["uselink"]) && ($shipmentData["uselink"] === true || $shipmentData["uselink"] == "true" || $shipmentData["uselink"] == 1)) {
                $shipment->series_master = ($shipmentData["series_master"] === true || $shipmentData["series_master"] == "true" || $shipmentData["series_master"] == 1) ? 1 : 0;
                $shipment->series_uuid = $shipmentData["series_uuid"];
            }
        }

        // Save
        $shipment->save();

        // Check cards
        if($this->isCardShipment()) {

            if(!$this->checkGiftCards($shipment->companyorder_id)) {
                throw new \Exception("Could not create shipment: ".implode(" - ",$this->getCardProblems()));
            }
        } else if($this->isEarlyOrder()) {
            $companyOrder = \CompanyOrder::find($shipmentData["companyorder_id"]);
            \ActionLog::logAction("EarlyorderCrated", "Earlyorder #".$shipment->id." oprettet til ".$companyOrder->order_no,"" , 0, $companyOrder->shop_id, $companyOrder->company_id, $companyOrder->id, 0, 0, $shipment->id);
        }

        // Commit and return shipment
        \system::connection()->commit();
        return $shipment;

    }

    private function checkValidItemNo($languageId,$itemNo) {

        if($languageId == 5) {
            $languageId = 1;
        }

        $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '".$itemNo."' && language_id = ".$languageId." && deleted is null");
        if(countgf($navisionItem) == 0) {
            return "Varenr ".$itemNo." mangler i navision";
        }

        if($navisionItem[0]->blocked == 1) {
            return "Varenr ".$itemNo." er blokkeret i navision";
        }

        return "";

    }

    public function updateShipment($shipmentId,$shipmentData)
    {

        // Load shipment
        $shipment = \Shipment::find(intval($shipmentId));
        $orgShipment = \Shipment::find(intval($shipmentId));

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        $cardshopSettings = \CardshopSettings::find_by_shop_id($companyOrder->shop_id);

        // Check state
        if($shipment->shipment_type != $this->getShipmentType()) throw new \Exception("Invalid shipment type");
        if($shipment->deleted_date != null) throw new \Exception("Shipment is deleted.");
        if($shipment->shipment_state > 1) throw new \Exception("Shipment synced, it cant be changed.");

        $items = 0;

        // Early order data
        if($this->isEarlyOrder()) {

            // Item 1
            if(isset($shipmentData["itemno"]) && trimgf($shipmentData["itemno"]) != "") {
                $shipment->itemno = trimgf($shipmentData["itemno"]);
                $shipment->quantity = intval($shipmentData["quantity"]);
                if($shipment->quantity <= 0) throw new \Exception("Item 1: quantity must be above 0");
                $checkResponse = $this->checkValidItemNo($cardshopSettings->language_code, $shipment->itemno);
                if($checkResponse != "") throw new \Exception("Item 1: ".$checkResponse);
                $items++;
            } else {
                $shipment->itemno = "";
                $shipment->quantity = 0;
            }

            // Item 2
            if(isset($shipmentData["itemno2"]) && trimgf($shipmentData["itemno2"]) != "") {
                $shipment->itemno2 = trimgf($shipmentData["itemno2"]);
                $shipment->quantity2 = intval($shipmentData["quantity2"]);
                if($shipment->quantity2 <= 0) throw new \Exception("Item 2: quantity must be above 0");
                $checkResponse = $this->checkValidItemNo($cardshopSettings->language_code, $shipment->itemno2);
                if($checkResponse != "") throw new \Exception("Item 2: ".$checkResponse);
                $items++;
            } else {
                $shipment->itemno2 = "";
                $shipment->quantity2 = 0;
            }

            // Item 3
            if(isset($shipmentData["itemno3"]) && trimgf($shipmentData["itemno3"]) != "") {
                $shipment->itemno3 = trimgf($shipmentData["itemno3"]);
                $shipment->quantity3 = intval($shipmentData["quantity3"]);
                if($shipment->quantity3 <= 0) throw new \Exception("Item 3: quantity must be above 0");
                $checkResponse = $this->checkValidItemNo($cardshopSettings->language_code, $shipment->itemno3);
                if($checkResponse != "") throw new \Exception("Item 3: ".$checkResponse);
                $items++;
            } else {
                $shipment->itemno3 = "";
                $shipment->quantity3 = 0;
            }

            // Item 4
            if(isset($shipmentData["itemno4"]) && trimgf($shipmentData["itemno4"]) != "") {
                $shipment->itemno4 = trimgf($shipmentData["itemno4"]);
                $shipment->quantity4 = intval($shipmentData["quantity4"]);
                if($shipment->quantity4 <= 0) throw new \Exception("Item 4: quantity must be above 0");
                $checkResponse = $this->checkValidItemNo($cardshopSettings->language_code, $shipment->itemno4);
                if($checkResponse != "") throw new \Exception("Item 4: ".$checkResponse);
                $items++;
            } else {
                $shipment->itemno4 = "";
                $shipment->quantity4 = 0;
            }

            // If no items
            if($items == 0) {
                throw new \Exception("No items provided, delete the order instead!");
            }

            if(isset($shipmentData["new_company_order_id"])) {
                $newCompanyOrderID = intval($shipmentData["new_company_order_id"]);
                if($newCompanyOrderID != $companyOrder->id) {
                    $newCompanyOrder = \CompanyOrder::find($newCompanyOrderID);
                    if($newCompanyOrder->order_state == 8) throw new \Exception("New order is cancelled");
                    if($newCompanyOrder->order_state >= 10) throw new \Exception("New order is closed");
                    if($newCompanyOrder->company_id != $companyOrder->company_id) throw new \Exception("New order is not for the same company");
                    $shipment->companyorder_id = $newCompanyOrderID;
                }
            }

        }

        // Card shipment data
        if($this->isCardShipment()) {
            if(isset($shipmentData["from_certificate_no"])) $shipment->from_certificate_no = intval($shipmentData["from_certificate_no"]);
            if(isset($shipmentData["to_certificate_no"])) $shipment->to_certificate_no = intval($shipmentData["to_certificate_no"]);
            $shipment->quantity = $shipment->to_certificate_no-$shipment->from_certificate_no+1;
        }

        // Shipment address
        if(isset($shipmentData["shipto_name"])) $shipment->shipto_name = $shipmentData["shipto_name"];
        if(isset($shipmentData["shipto_address"])) $shipment->shipto_address = $shipmentData["shipto_address"];
        if(isset($shipmentData["shipto_address2"])) $shipment->shipto_address2 = $shipmentData["shipto_address2"];
        if(isset($shipmentData["shipto_postcode"])) $shipment->shipto_postcode = $shipmentData["shipto_postcode"];
        if(isset($shipmentData["shipto_city"])) $shipment->shipto_city = $shipmentData["shipto_city"];
        if(isset($shipmentData["shipto_country"])) $shipment->shipto_country = $shipmentData["shipto_country"];
        if(isset($shipmentData["shipto_contact"])) $shipment->shipto_contact = $shipmentData["shipto_contact"];
        if(isset($shipmentData["shipto_email"])) $shipment->shipto_email = $shipmentData["shipto_email"];
        if(isset($shipmentData["shipto_phone"])) $shipment->shipto_phone = str_replace(array(" ","-"),"",trim($shipmentData["shipto_phone"]));


        // Check shipment
        $this->checkShipement($shipment);



        // Save
        $shipment->save();

        // Check cards
        if($this->isCardShipment()) {
            if($this->checkGiftCards($shipment->companyorder_id) == false) {
                throw new \Exception("Problems with card shipments block update");
            }
        }

        // Log action
        if($this->isEarlyOrder()) {

            $logDetails = "Ændringer i early order:\n";

            // Compare shipment and orgshipment and add to logdetails
            if($shipment->itemno != $orgShipment->itemno) $logDetails .= "Item 1: ".$orgShipment->itemno." -> ".$shipment->itemno."\n";
            if($shipment->quantity != $orgShipment->quantity) $logDetails .= "Quantity 1: ".$orgShipment->quantity." -> ".$shipment->quantity."\n";
            if($shipment->itemno2 != $orgShipment->itemno2) $logDetails .= "Item 2: ".$orgShipment->itemno2." -> ".$shipment->itemno2."\n";
            if($shipment->quantity2 != $orgShipment->quantity2) $logDetails .= "Quantity 2: ".$orgShipment->quantity2." -> ".$shipment->quantity2."\n";
            if($shipment->itemno3 != $orgShipment->itemno3) $logDetails .= "Item 3: ".$orgShipment->itemno3." -> ".$shipment->itemno3."\n";
            if($shipment->quantity3 != $orgShipment->quantity3) $logDetails .= "Quantity 3: ".$orgShipment->quantity3." -> ".$shipment->quantity3."\n";
            if($shipment->itemno4 != $orgShipment->itemno4) $logDetails .= "Item 4: ".$orgShipment->itemno4." -> ".$shipment->itemno4."\n";
            if($shipment->quantity4 != $orgShipment->quantity4) $logDetails .= "Quantity 4: ".$orgShipment->quantity4." -> ".$shipment->quantity4."\n";
            if($shipment->itemno5 != $orgShipment->itemno5) $logDetails .= "Item 5: ".$orgShipment->itemno5." -> ".$shipment->itemno5."\n";
            if($shipment->quantity5 != $orgShipment->quantity5) $logDetails .= "Quantity 5: ".$orgShipment->quantity5." -> ".$shipment->quantity5."\n";

            // Companre address fields
            if($shipment->shipto_name != $orgShipment->shipto_name) $logDetails .= "Shipto name: ".$orgShipment->shipto_name." -> ".$shipment->shipto_name."\n";
            if($shipment->shipto_address != $orgShipment->shipto_address) $logDetails .= "Shipto address: ".$orgShipment->shipto_address." -> ".$shipment->shipto_address."\n";
            if($shipment->shipto_address2 != $orgShipment->shipto_address2) $logDetails .= "Shipto address2: ".$orgShipment->shipto_address2." -> ".$shipment->shipto_address2."\n";
            if($shipment->shipto_postcode != $orgShipment->shipto_postcode) $logDetails .= "Shipto postcode: ".$orgShipment->shipto_postcode." -> ".$shipment->shipto_postcode."\n";
            if($shipment->shipto_city != $orgShipment->shipto_city) $logDetails .= "Shipto city: ".$orgShipment->shipto_city." -> ".$shipment->shipto_city."\n";
            if($shipment->shipto_country != $orgShipment->shipto_country) $logDetails .= "Shipto country: ".$orgShipment->shipto_country." -> ".$shipment->shipto_country."\n";
            if($shipment->shipto_contact != $orgShipment->shipto_contact) $logDetails .= "Shipto contact: ".$orgShipment->shipto_contact." -> ".$shipment->shipto_contact."\n";
            if($shipment->shipto_email != $orgShipment->shipto_email) $logDetails .= "Shipto email: ".$orgShipment->shipto_email." -> ".$shipment->shipto_email."\n";
            if($shipment->shipto_phone != $orgShipment->shipto_phone) $logDetails .= "Shipto phone: ".$orgShipment->shipto_phone." -> ".$shipment->shipto_phone."\n";

            // If changed companyorderid
            if($shipment->companyorder_id != $orgShipment->companyorder_id) {
                $logDetails .= "Order changed from ".$companyOrder->order_no." to ".$newCompanyOrder->order_no."\n";
            }

            \ActionLog::logAction("EarlyorderUpdated", "Earlyorder #".$shipment->id." opdateret", $logDetails, 0, $companyOrder->shop_id, $companyOrder->company_id, $companyOrder->id, 0, 0, $shipment->id);
        }

        // Commit and return shipment
        \system::connection()->commit();

        return $shipment;
    }

    private function checkShipement(\Shipment $shipment)
    {
        // Check company order
        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        if($shipment->shipment_type != $this->getShipmentType()) throw new \Exception("Invalid shipment type");
        if($companyOrder->id <= 0) throw new \Exception("Could not find companyorder");
        if($shipment->deleted_date != null) throw new \Exception("Shipment is deleted.");

        // Check card shipments
        if($this->isCardShipment()) {
            if($companyOrder->order_state >= 6) throw new \Exception("Cant add cards at this stage of the order");
            if($companyOrder->is_email == 1) throw new \Exception("Cant add card shipments to e-mail order");
            if($shipment->from_certificate_no > 0 && ($shipment->from_certificate_no < $companyOrder->certificate_no_begin || $shipment->from_certificate_no > $companyOrder->certificate_no_end)) {
                throw new \Exception("Certificate start number not in range of order card numbers");
            }
            if($shipment->to_certificate_no > 0 && ($shipment->to_certificate_no < $companyOrder->certificate_no_begin || $shipment->to_certificate_no > $companyOrder->certificate_no_end)) {
                throw new \Exception("Certificate end number not in range of order card numbers");
            }
            if($shipment->quantity <= 0 && $shipment->from_certificate_no > 0) throw new \Exception("Certificate quantity is zero or below");
            if($shipment->quantity > $companyOrder->quantity) throw new \Exception("Shipment quantity is above the total number of cards on the order");
        }

        // Check early order
        if($this->isEarlyOrder()) {

            if($companyOrder->order_state >= 8) throw new \Exception("Cant add cards at this stage of the order");
            if($shipment->quantity <= 0) throw new \Exception("Item 1: quantity must be above 0");
            if($shipment->itemno == "")  throw new \Exception("Item 1: No itemno provided");

            if($shipment->quantity2 < 0) throw new \Exception("Item 2: quantity cant be negative");
            else if($shipment->quantity2 > 0 && trimgf($shipment->itemno2) == "") throw new \Exception("Item 2: no itemno provided");

            if($shipment->quantity3 < 0) throw new \Exception("Item 3: quantity cant be negative");
            else if($shipment->quantity3 > 0 && trimgf($shipment->itemno3) == "") throw new \Exception("Item 3: no itemno provided");

            if($shipment->quantity4 < 0) throw new \Exception("Item 4: quantity cant be negative");
            else if($shipment->quantity4 > 0 && trimgf($shipment->itemno4) == "") throw new \Exception("Item 4: no itemno provided");

            if($shipment->quantity5 < 0) throw new \Exception("Item 5: quantity cant be negative");
            else if($shipment->quantity5 > 0 && trimgf($shipment->itemno5) == "") throw new \Exception("Item 5: no itemno provided");

        }

        // Check address
        if(trimgf($shipment->shipto_name) == "") {
            //throw new \Exception("No ship to name provided");
            $shipment->shipto_name = $companyOrder->ship_to_company;
            if(trimgf($shipment->shipto_name) == "") {
                $shipment->shipto_name = $companyOrder->company_name;
            }
        }
        if($shipment->shipto_address == "") throw new \Exception("No ship to address provided");
        if($shipment->shipto_postcode == "") throw new \Exception("No ship to postcode provided");
        if($shipment->shipto_city == "") throw new \Exception("No ship to city provided");

    }

    /**
     * DELETE
     */

    public function deleteShipment($shipmentId)
    {

        // Load shipment
        $shipment = \Shipment::find(intval($shipmentId));

        // Check state
        if($shipment->shipment_type != $this->getShipmentType()) throw new \Exception("Invalid shipment type");
        if($shipment->deleted_date != null) throw new \Exception("Shipment is deleted.");
        if($shipment->shipment_state > 1) throw new \Exception("Shipment synced, it cant be deleted.");


        $shipment->shipment_state = 4;
        $shipment->deleted_date = date('d-m-Y H:i:s');
        $shipment->save();

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        \Actionlog::logAction("EarlyorderDeleted", "Earlyorder #".$shipment->id." er slettet","" , 0, $companyOrder->shop_id, $companyOrder->company_id, $companyOrder->id, 0, 0, $shipment->id);

        return $shipment;
    }

    /**
     * REOPEN SHIPMENT
     */

    public function reopenShipment($shipmentId)
    {

        // Load shipment
        $shipment = \Shipment::find(intval($shipmentId));

        // Check state
        if($shipment->shipment_type != $this->getShipmentType()) throw new \Exception("Invalid shipment type");
        if($shipment->deleted_date == null) throw new \Exception("Shipment is not deleted.");
        if($shipment->shipment_state == 2) throw new \Exception("Shipment synced, it cant be reopend.");

        $shipment->shipment_state = 1;
        $shipment->deleted_date = null;
        $shipment->save();

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        \Actionlog::logAction("EarlyorderReopen", "Earlyorder #".$shipment->id." er gendannet","" , 0, $companyOrder->shop_id, $companyOrder->company_id, $companyOrder->id, 0, 0, $shipment->id);


        return $shipment;
    }

    /**
     * UPDATE SEND STATUS
     */
    
    public function sendShipment($shipmentid,$output=true)
    {
        $shipment = \Shipment::find(intval($shipmentid));
        if($shipment->shipment_type != $this->getShipmentType()) throw new \Exception("Invalid shipment type");
        if($shipment->shipment_state == 0) {
            $shipment->shipment_state = 1;
            $shipment->save();
            if($output) echo json_encode(array("status" => 1, "message" => "Shipment marked as ready to send"),JSON_PRETTY_PRINT);
            return true;
        }
        else if($shipment->shipment_state == 1) {
            if($output) echo json_encode(array("status" => 1, "message" => "Shipment already marked as ready to send"),JSON_PRETTY_PRINT);
            return true;
        }
        else {
            if($output) echo json_encode(array("status" => 0, "message" => "Shipment already sent or blocked"),JSON_PRETTY_PRINT);
            return false;
        }
    }
    
    public function cancelShipment($shipmentid,$output=true)
    {
        $shipment = \Shipment::find(intval($shipmentid));
        if($shipment->shipment_type != $this->getShipmentType()) throw new \Exception("Invalid shipment type");
        if($shipment->shipment_state == 1) {
            $shipment->shipment_state = 0;
            $shipment->save();
            if($output) echo json_encode(array("status" => 1, "message" => "Shipment marked as not ready to send"),JSON_PRETTY_PRINT);
            return true;
        }
        else if($shipment->shipment_state == 0) {
            if($output) echo json_encode(array("status" => 1, "message" => "Shipment already marked as not ready to send"),JSON_PRETTY_PRINT);
            return true;
        }
        else {
            if($output) echo json_encode(array("status" => 0, "message" => "Shipment already sent or blocked"),JSON_PRETTY_PRINT);
            return false;
        }

    }
    
    /**
     * SPECIAL FOR GIFTCARDS
     */

    public function createDefault($companyOrderID,$sendNow)
    {

        // If early order
        if(!$this->isCardShipment()) {
            throw new \Exception("Can only create default orders on card shipments");
        }

        // Check no active shipments
        $shipmentList = $this->getCompanyOrderShipments($companyOrderID);
        if(count($shipmentList) > 0) {
            throw new \Exception("Order already has shipments, cant create default");
        }

        // Load company
        $companyOrder = \CompanyOrder::find(intval($companyOrderID));
        $company = \Company::find($companyOrder->company_id);

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
        $shipment->shipment_state = ($sendNow == true ? 1 : 0);

        $shipment->shipto_name = trimgf($company->ship_to_company) == "" ? $company->name : $company->ship_to_company;
        $shipment->shipto_address = $company->ship_to_address;
        $shipment->shipto_address2 = $company->ship_to_address_2;
        $shipment->shipto_postcode = $company->ship_to_postal_code;
        $shipment->shipto_city = $company->ship_to_city;
        $shipment->shipto_country = $company->ship_to_country;
        $shipment->shipto_contact = $company->ship_to_attention;
        if($shipment->shipto_contact == "") {
            $shipment->shipto_contact = $company->contact_name;
        }

        $shipment->shipto_email = $company->contact_email;
        $shipment->shipto_phone = str_replace(array(" ","-"),"",trim($company->contact_phone));

        // Save and add to list
        $shipment->save();

        // Return shipment
        return $shipment;

    }

    private $cardProblems = null;
    private $lastCard = null;
    private $lastCardMax = null;

    public function getCardProblems() { return $this->cardProblems; }
    public function getNextCard() { return $this->lastCard+1; }
    public function getCardMax() { return $this->lastCardMax; }

    public function checkGiftCards($companyOrderID)
    {

        if(!$this->isCardShipment()) throw new \Exception("Invalid shipment type to check cards");

        // Load company order
        $companyOrder = \CompanyOrder::find($companyOrderID);
        $usedCards = array();
        $problems = array();

        $this->lastCard = $companyOrder->certificate_no_begin-1;
        $this->lastCardMax = $companyOrder->certificate_no_end;

        // Pull all card shipment orders
        $shipmentList = \Shipment::find_by_sql("SELECT * FROM shipment WHERE deleted_date IS NULL && shipment_type = 'giftcard' && from_certificate_no > 0 && series_master = 0 && companyorder_id = ".intval($companyOrderID));
        if(count($shipmentList) > 0) {
            foreach($shipmentList as $shipment) {

                // Check cards
                for($i=$shipment->from_certificate_no;$i<=$shipment->to_certificate_no;$i++) {
                    if($i < $companyOrder->certificate_no_begin) $problems[] = "Card ".$i." is below first card number.";
                    if($i > $companyOrder->certificate_no_end) $problems[] = "Card ".$i." is above first card number.";
                    if(isset($usedCards[$i])) $problems[] = "Card ".$i." in shipments multiple times.";
                    $usedCards[$i] = true;
                    if($this->lastCard < $i) $this->lastCard = $i;
                }
                

            }
        }

        if(count($usedCards) > $companyOrder->quantity) $problems[] = "Counted ".countgf($usedCards)." cards in order with only ".$companyOrder->quantity." cards.";
        $this->cardProblems = $problems;
        return countgf($problems) == 0;

    }
    
}