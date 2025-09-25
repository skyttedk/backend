<?php

namespace GFUnit\development\fixscripts;

class ShipmentCheck
{

    private $stats;

    public function run()
    {

         echo "RUN FIX SHIPMENT SCRIPT<br>";

         $sql = "SELECT * FROM `shipment` WHERE shipment_sync_date > '2021-10-26 12:30:38' && companyorder_id in (select id from company_order where shop_id in (select shop_id from cardshop_settings where language_code =5)) ORDER BY `shipment`.`shipment_sync_date` DESC";
         $shipmentList = \Shipment::find_by_sql($sql);

         foreach($shipmentList as $shipment) {
             $this->handleShipment($shipment);
         }

        echo "<pre>".print_r($this->stats,true)."</pre>";

    }

    private function handleShipment($shipment)
    {

        $postcode = $this->formatSEPostalCode($shipment->shipto_postcode);
        $phone = $this->formatSEPhoneNumber($shipment->shipto_phone);

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        echo "<br><br><hr><br><h2>".$shipment->id." - ".$companyOrder->order_no."</h2>";
        echo "<br>Postcode: ".$shipment->shipto_postcode." | ".$postcode." ".($shipment->shipto_postcode != $postcode ? " - CHANGED" : "");
        echo "<br>Phone: ".$shipment->shipto_phone." | ".$phone." ".($shipment->shipto_phone != $phone ? " - CHANGED" : "");
    }

    private function formatSEPostalCode($postalCode) {
        $postalCode = str_replace(" ","",trimgf($postalCode));
        if(strlen($postalCode) == 5) {
            $postalCode = substr($postalCode,0,3)." ".substr($postalCode,3);
        }
        return $postalCode;
    }

    private function formatSEPhoneNumber($phone) {
        $phone = trimgf(str_replace(array(" ","-"),"",$phone));
        if($phone != "") {
            if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
                if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                $phone = "+46".$phone;
            }
        }
        return $phone;
    }

/*
 * PREV SCRIPT FROM BEFORE 28/10 2021 - SC
 */
    /*
    private $stats;

    public function run()
    {

        echo "RUN FIX SHIPMENT SCRIPT<br>";
        $this->stats = array("totalcount" => 0,"processed" => 0,"order_noshipments" => 0,"order_countmismatch" => 0,"shipment_count_mismatch" => 0,"shipment_count_ok" => 0);

        $companyOrderList = \CompanyOrder::find("all");
        echo "Found ".countgf($companyOrderList)."<br><br>";
        $this->stats["totalcount"] = countgf($companyOrderList);

        foreach($companyOrderList as $i => $companyOrder) {

            echo "<br>";
            $this->processOrder($companyOrder);
            $this->stats["processed"]++;

        }


        echo "<pre>".print_r($this->stats,true)."</pre>";

    }

    public function log($message)
    {

        echo $message."<br>";

    }

    private function processOrder(\CompanyOrder $companyOrder) {

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

            $this->log(" - No existing shipments, create - ".$companyOrder->company_name);
            $this->stats["order_noshipments"]++;

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
            $shipment->shipto_phone = $company->contact_phone;

            // Save and add to list
            $shipment->save();
            $shipmentList[] = $shipment;

        }
        else {

            // Process shipments
            $this->log(" - Found ".countgf($shipmentList)." existing shipments");
            $problemShipments = array();
            $shipmentCards = 0;

            foreach($shipmentList as $shipment) {

                // Check card numbers
                if($shipment->quantity == 0 || ($shipment->to_certificate_no)-intval($shipment->from_certificate_no)+1 <= 0) {
                    echo "Mismatch in card quantity: ".$shipment->from_certificate_no." - ".$shipment->to_certificate_no." (".$shipment->quantity.")";
                    $this->stats["shipment_count_mismatch"]++;
                }
                else {

                    // Add to count
                    $shipmentCards += ($shipment->to_certificate_no)-intval($shipment->from_certificate_no)+1;
                    $this->stats["shipment_count_ok"]++;
                }

            }

            // Check total type
            if($shipmentCards != $activeShopUserCount && $shipmentCards != $allShopUserCount) {
                $this->stats["order_countmismatch"]++;
                echo "MISMATCH ON COUNT: Shipment cards: ".$shipmentCards." - Active shopusers: ".$activeShopUserCount." - All shopussers: ".$allShopUserCount."<br>";
            }

        }

    }
*/

}