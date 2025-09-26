<?php

namespace GFUnit\development\fixscripts;

class CreateMissingGFShip
{

    private $stats;

    public function run()
    {

        if(isset($_POST["companyorderid"]) && trimgf($_POST["companyorderid"]) != "") {

            echo "<b>Start shipment create ".$_POST["companyorderid"]."</b><br>";

            $orderinput = trimgf($_POST["companyorderid"]);
            if(intval($orderinput) > 0) {
                $order = \CompanyOrder::find(intval($orderinput));
            }

            if($order == null || $order->id == 0) {
                echo "KUNNE IKKE FINDE ORDREN: ".$orderinput." - PRØV IGEN!";
                exit();
            }

            echo "Fandt ordren ".$order->id." - ".$order->order_no."<br>";
            echo $order->company_name."<br>";
            echo "Oprettet shipments<br>";
            
            $companyOrder = $order;

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
                $shipment->quantity = ($maxNumber - $minNumber) + 1;
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

                echo "Creates shipment ".$shipment->id."<br>";
            }

            echo "Completed: , created ".count($shipmentList)." shipments";
            \System::connection()->commit();

            echo "<br><br>";
        }

        ?><form method="post" action="">
        companyorder id: <input type="text" value="" name="companyorderid">  <button>Opret manglende shipment</button>
        </form><?php

    }

    private function log($message) {
        echo $message;
    }

}
