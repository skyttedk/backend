<?php

namespace GFUnit\development\fixscripts;

class ShipmentChildCheck
{

    /**
     * SHIPTO_STATE
     * 0: Not processed
     * 1: Is Shipto adress - do not ship directly but use as shipto address for series
     * 2: Is ship to master, might use a ship to adress
     * 3: Is ship to child
     * 10: Ship to not relevant
     * 20: Deleted but might be revived
     */

    private $stats;

    public function run() {

        $sql = "SELECT companyorder_id FROM `shipment` where (shipto_state = 0 or shipto_state = 20) && shipment_type = 'giftcard' GROUP BY companyorder_id having NOW() > DATE_ADD(MAX(created_date), INTERVAL 1 HOUR) or MAX(force_syncnow) = 1";
        $companyorderlist = \Shipment::find_by_sql($sql);
exit();
        foreach($companyorderlist as $companyorder) {
            //$this->preProcessOrder($companyorder->companyorder_id);
            $this->checkOrder($companyorder->id,count($companyorderlist));
            exit();
        }

        //\System::connection()->commit();

        echo "<br><br><br><pre>".print_r($this->stats,true)."</pre>";

    }


    private function preProcessOrder($companyOrderID)
    {

        // Get company order
        $companyOrder = \CompanyOrder::find($companyOrderID);

        // Load shipments
        $shipmentList = \Shipment::find('all',array("conditions" => array("shipment_type" => "giftcard","companyorder_id" =>$companyOrder->id)));

        // Activate / deactivate shipments
        $orderDeleted = ($companyOrder->order_state == 7 || $companyOrder->order_state == 8);

        $seriesMasterID = 0;
        $hasUUID = false;

        // Process shipments
        foreach($shipmentList as $shipment) {

            // Activate / deactivate
            if($shipment->shipto_state == 0 && $orderDeleted == true) {
                $shipment->shipto_state = 20;
                $shipment->save();
            } else if($shipment->shipto_state == 20 && $orderDeleted == false) {
                $shipment->shipto_state = 0;
                $shipment->save();
            }

            // Set series master id
            if($shipment->series_master == 1) {
                if($seriesMasterID != 0) {
                    echo "SERIES MASTER ID ALREADY SET, ABORT!!";
                    exit();
                }
                $seriesMasterID = $shipment->id;
            }

            if(trimgf($shipment->series_uuid) != "") {
                $hasUUID = true;
            }

        }

        if($orderDeleted == true) {
            $this->incStatus("shipto_deleted");
            return;
        }

        $this->incStatus("deadline-".$companyOrder->expire_date->format("d-m-Y"));

        echo "<br><h2>PREPROCESSING ".$companyOrder->id.": ".$companyOrder->order_no." [<b>".$companyOrder->order_state."</b>] - ".utf8_decode($companyOrder->company_name)." - deadline ".$companyOrder->expire_date->format("d-m-Y")."</h2><br>";

        // If e-mail, set all to state 10 - not relevant
        if($companyOrder->is_email == 1) {
            echo "MAIL CARD - SET shopto_state to 10";
            $this->incStatus("shipto_proc_mailcards");
            $this->updateShipmentShipToStateByOrderID($companyOrder->id,10);
            return;
        }

        $shopuserList = \ShopUser::find_by_sql("SELECT * FROM shop_user WHERE company_order_id = ".$companyOrder->id);
        $certMap = array();

        foreach($shopuserList as $shopuser) {
            if(!isset($certMap[$shopuser->company_id]))  {
                $certMap[$shopuser->company_id] = array();
            }
            $certMap[$shopuser->company_id][] = $shopuser->username;
        }

        // 1 company to send to
        if(count($certMap) == 1) {

            // Cards only on parent
            if(isset($certMap[$companyOrder->company_id])) {

                // Only 1 shipment
                if(count($shipmentList) == 1) {

                    // Make sure shipment has all cards
                    if($shipmentList[0]->from_certificate_no != $companyOrder->certificate_no_begin || $shipmentList[0]->to_certificate_no != $companyOrder->certificate_no_end) {
                        $shipmentList[0]->from_certificate_no = $companyOrder->certificate_no_begin;
                        $shipmentList[0]->to_certificate_no = $companyOrder->certificate_no_end;
                        $shipmentList[0]->quantity = $companyOrder->quantity;
                        echo "MISMATCH IN CARDS ON SHIPMENT ".$shipmentList[0]->id."<br>";
                        exit();
                    }

                    // Set shipto_state to 10
                    $this->incStatus("shipto_proc_onetoone_parent");
                    $this->updateShipmentShipToStateByOrderID($companyOrder->id,10);

                }
                // More shipments
                else {

                    echo "ONE COMPANY TO MULTIPLE SHIPMENTS PARENT - INVESTIGATE!!!";
                    $this->incStatus("shipto_proc_onetomulti_parent");

                    foreach($shipmentList as $shipment) {
                        echo "<br>-".$shipment->id."";
                    }

                    echo "<br>====================================================<br>";
                }
            }
            // Cards only on child
            else {

                // Send to 1 shipment
                if(count($shipmentList) == 1) {
                    echo "ONE COMPANY TO ONE SHIPMENT CHILD - INVESTIGATE";
                    $this->incStatus("shipto_proc_onetoone_child");
                }

                // Send to multiple shipments
                else {
                    //
                    echo "ONE COMPANY TO MULTIPLE SHIPMENTS CHILD - INVESTIGATE";
                    $this->incStatus("shipto_proc_onetomulti_child");
                }
            }

        }
        // Multiple companies
        else {

            // Only 1 shipment for all cards
            if(count($shipmentList) == 1) {

                echo "ONE SHIPMENT, MORE THAN ONE COMPANY - INVESTIGATE";
                $this->incStatus("shipto_proc_multitoone");

                // Has parent
                if(isset($certMap[$companyOrder->company_id])) {

                    // Generate uuid
                    $seriesUUID = $this->guidv4();

                    // Update series master
                    $shipment = $shipmentList[0];
                    $shipment->shipto_state = 2;
                    $shipment->series_master = 1;
                    $shipment->series_uuid = $seriesUUID;

                    // Update parent cards
                    $quantity = countgf($certMap[$companyOrder->company_id]);
                    $certStart = min($certMap[$companyOrder->company_id]);
                    $certEnd = max($certMap[$companyOrder->company_id]);
                    $shipment->quantity = $quantity;
                    $shipment->from_certificate_no = $certStart;
                    $shipment->to_certificate_no = $certEnd;
                    $shipment->shipment_note = '=== SERIE MASTER: '.$companyOrder->order_no."===\r\n".$shipment->shipment_note;

                    $shipment->save();

                    // Create all child shipments
                    $childShipments = $this->createChildShipments($certMap,$companyOrder,$shipment,$seriesUUID);

                    // Make the 1 child the series master
                    foreach($childShipments as $index => $cshipment) {
                        $cshipment->save();
                    }

                    echo "HAS PARENT - CREATE CHILD ONLY";
                    $this->incStatus("shipto_proc_multitoone-parent");


                } else {

                    // Generate uuid
                    $seriesUUID = $this->guidv4();

                    // Get shipment and set to shipto adress
                    $shipment = $shipmentList[0];
                    $shipment->series_uuid = $seriesUUID;
                    $shipment->series_master = 0;
                    $shipment->shipto_state = 1;
                    $shipment->save();

                    // Create all child shipments
                    $childShipments = $this->createChildShipments($certMap,$companyOrder,$shipment,$seriesUUID);

                    // Make the 1 child the series master
                    foreach($childShipments as $index => $cshipment) {
                        if($index == 0) {
                            $cshipment->shipment_note = '=== SERIE MASTER: '.$companyOrder->order_no."===";
                            $cshipment->series_master = 1;
                            $cshipment->shipto_state = 2;
                        }
                        $cshipment->save();
                    }

                    echo "<br>NO PARENT IN CARDS<br>";
                    $this->incStatus("shipto_proc_multitoone-noparent");
                }

            }
            // Multiple shipments
            else {

                if($seriesMasterID > 0 || $hasUUID == true) {
                    echo "MULTIPLE COMPANY AND SHIPMENTS - USING IDS - INVESTIGATE";
                    $this->incStatus("shipto_proc_multimulti_series");
                    $this->checkOrder($companyOrder->id,countgf($certMap));
                }
                else {
                    echo "MULTIPLE COMPANY AND SHIPMENTS - ONE TO ONE - INVESTIGATE";
                    $this->incStatus("shipto_proc_multimulti");

                    $shipExpectation = array();
                    foreach($certMap as $company_id => $certList) {
                        echo "<br>COMPANY: ".$company_id." - ".countgf($certList)." cards - ".min($certList)." - ".max($certList)."<br>";
                        $shipExpectation[min($certList)] = array("count" => countgf($certList),"min" => min($certList), "max" => max($certList));
                    }

                    echo "<pre>".print_r($shipExpectation,true)."</pre>";

                    $shipmentValid = true;
                    foreach($shipmentList as $shipment) {
                        if(!isset($shipExpectation[$shipment->from_certificate_no])) {
                            $shipmentValid = false;
                            echo "<br>SHIPMENT MISMATCH - START NO NOT FOUND: ".$shipment->id."<br>";
                            $this->incStatus("ship-mismatch-notfound");
                            //$this->checkOrder($companyOrder->id,countgf($certMap));
                        }
                        else if($shipExpectation[$shipment->from_certificate_no]["count"] != $shipment->quantity) {
                            $shipmentValid = false;
                            echo "<br>SHIPMENT MISMATCH - COUNT MISMATCH: ".$shipment->id."<br>";
                            $this->incStatus("ship-mismatch-countmismatch");
                            //$this->checkOrder($companyOrder->id,countgf($certMap));
                        }
                        else if($shipExpectation[$shipment->from_certificate_no]["max"] != $shipment->to_certificate_no) {
                            $shipmentValid = false;
                            echo "<br>SHIPMENT MISMATCH - END NO NOT FOUND: ".$shipment->id."<br>";
                            $this->incStatus("ship-mismatch-endno-mismatch");
                            //$this->checkOrder($companyOrder->id,countgf($certMap));
                        }
                    }

                    // Valid shipment, send now
                    if($shipmentValid == true) {
                        foreach($shipmentList as $shipment) {
                            $shipment->shipto_state = 10;
                            $shipment->save();
                        }
                    }

                }


            }
        }

        echo "<br>------<br>";

    }

    private function createChildShipments($certMap,$companyOrder,$shipment,$seriesUUID) {

        $shipmentList = array();

        // Create childs
        foreach($certMap as $companyid => $certList) {
            if($companyid != $companyOrder->company_id) {
                $shipmentList[] = $this->createChildShipment($companyid,$certList,$companyOrder,$shipment,$seriesUUID);
            }
        }

        return $shipmentList;

    }

    private function createChildShipment($companyid,$certList,$companyOrder,$shipment,$seriesUUID) {

        echo "<br>CREATE CHILD SHIPMENT: ".$companyid." - ".countgf($certList)." cards - ".min($certList)." - ".max($certList)."<br>";

        $child = \Company::find($companyid);
        if($child->pid != $companyOrder->company_id) {
            echo "THIS IS NOT A CHILD!<br>";
           exit();
        }

        $quantity = countgf($certList);
        $certStart = min($certList);
        $certEnd = max($certList);

        $childShipment = new \Shipment();
        $childShipment->companyorder_id = $companyOrder->id;
        $childShipment->created_date = $shipment->created_date;
        $childShipment->shipment_type = 'giftcard';
        $childShipment->quantity = $quantity;
        $childShipment->itemno = '';
        $childShipment->description = '';
        $childShipment->itemno2 = '';
        $childShipment->quantity2 = '';
        $childShipment->description2 = '';
        $childShipment->itemno3 = '';
        $childShipment->quantity3 = '';
        $childShipment->description3 = '';
        $childShipment->itemno4 = '';
        $childShipment->quantity4 = '';
        $childShipment->description4 = '';
        $childShipment->itemno5 = '';
        $childShipment->quantity5 = '';
        $childShipment->description5 = '';
        $childShipment->isshipment = ($companyOrder->is_email == 1 ? 0 : 1);
        $childShipment->from_certificate_no = $certStart;
        $childShipment->to_certificate_no = $certEnd;
        $childShipment->shipto_name = trimgf($child->ship_to_company) == "" ? $child->name : $child->ship_to_company;

        if(trimgf($child->ship_to_address) != "") {
            $childShipment->shipto_address = $child->ship_to_address;
            $childShipment->shipto_address2 = $child->ship_to_address_2;
            $childShipment->shipto_postcode = $child->ship_to_postal_code;
            $childShipment->shipto_city = $child->ship_to_city;
            $childShipment->shipto_country = $child->ship_to_country;
        } else {
            $childShipment->shipto_address = $child->bill_to_address;
            $childShipment->shipto_address2 = $child->bill_to_address_2;
            $childShipment->shipto_postcode = $child->bill_to_postal_code;
            $childShipment->shipto_city = $child->bill_to_city;
            $childShipment->shipto_country = $child->bill_to_country;
        }


        $childShipment->shipto_contact = $child->contact_name;
        $childShipment->shipto_email = $child->contact_email;
        $childShipment->shipto_phone = $child->contact_phone;
        $childShipment->shipment_note = '=== SERIE: '.$companyOrder->order_no." ===";
        $childShipment->gls_shipment = 0;
        $childShipment->handle_country = 0;
        $childShipment->shipment_state = 1;
        $childShipment->shipment_sync_date = null;
        $childShipment->deleted_date = null;
        $childShipment->force_syncnow = 0;
        $childShipment->series_master = 0;
        $childShipment->series_uuid = $seriesUUID;
        $childShipment->shipto_state = 3;

        return $childShipment;

    }

    private function updateShipmentShipToStateByOrderID($companyorderid,$state) {
        $shipmentList = \Shipment::find('all',array('conditions' => array("companyorder_id" => $companyorderid)));
        foreach($shipmentList as $shipment) {
            echo "UPDATE COMPANY ".$companyorderid." shipment ".$shipment->id." => ".$state;
            $shipment->shipto_state = $state;
            $shipment->save();
        }
    }

    /**
     * HELPER FUNCTIONS
     */

    private function incStatus($key) {
        if(!isset($this->stats[$key])) $this->stats[$key] = 0;
        $this->stats[$key]++;
    }

    private  function guidv4($data = null) {

        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * FIRST CHECK RUN
     */

    public function runCheck() {
        /*
      return;

      echo "RUN CHECK CHILD SCRIPT<br>";
      $this->stats = array("orders" => 0,"testnames"=> array(),"testids" => array());

      $companyOrderList = \ShopUser::find_by_sql("SELECT company_order_id, countgf(distinct company_id) as companycount FROM `shop_user` where company_order_id IS NOT NULL group by company_order_id having countgf(distinct company_id) > 1");
      echo "Found ".countgf($companyOrderList)." orders:";
      $this->stats["orders"] = countgf($companyOrderList);

      foreach($companyOrderList as $i => $companyOrder) {
          $this->checkOrder($companyOrder->company_order_id,$companyOrder->companycount);

      }

      //$this->stats["testids"] = implode(",",$this->stats["testids"]);

      echo "<br><br><hr><br><br>ABORT SCRIPT<br>";
      */
    }

    public function checkOrder($companyOrderID,$companycount)
    {

        $companyOrder = \CompanyOrder::find($companyOrderID);
        $cardshopsettings = \CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings WHERE shop_id = ".$companyOrder->shop_id);
        $cardshopsettings = $cardshopsettings[0];

        $state = "Unknown";

        $testNames = array("Holtet Services AS","aaa","bundy","test");
        $isTest = false;
        foreach($testNames as $name) {
            if(strstr(strtolower($companyOrder->company_name),strtolower($name))) {
                $state = "Test";
                $isTest = true;
                //$this->stats["testnames"][] = $companyOrder->company_name;
                //$this->stats["testids"][] = $companyOrder->id;
            }
        }

        $shipmentList = \Shipment::find_by_sql("SELECT * FROM shipment WHERE companyorder_id = ".$companyOrder->id." && shipment_type = 'giftcard' order by from_certificate_no asc");
        $shipstates = array(0,0,0,0,0,0,0);

        foreach($shipmentList as $shipment) {
            $shipstates[$shipment->shipment_state]++;
        }

        foreach($shipstates as $index => $scount) {
            if($scount == 0) {
                unset($shipstates[$index]);
            }
        }

        if(count($shipstates) > 1) {
            $state = "MIXED";
        }

        else if(isset($shipstates[2]) || isset($shipstates[5]) || isset($shipstates[6])) {
            $state = "SENT";
        }

        else if(isset($shipstates[1])) {
            $state = "WAITING";
        }

        else if(isset($shipstates[3])) {
            $state = "ERROR";
        }

        else if(isset($shipstates[4])) {
            $state = "BLOCKED";
        }

        $state = $state . "-". $cardshopsettings->language_code;
        if($isTest) $state = "test-".$state;



        if($isTest == false && $companyOrder->order_state != 8 && $state != "SENT-4" && $state != "SENT-1") {
            echo "<br><hr><br>";
            echo "Companyorder ".$companyOrder->id.": ".$companyOrder->order_no." [<b>".$companyOrder->order_state."</b>]<br>";
            echo $companyOrder->company_name." (".$companyOrder->company_id.") ".$companyOrder->shop_name." x ".$companyOrder->quantity." @ ".$companyOrder->expire_date->format("Y-m-d")." - ".($companyOrder->is_email ? "EMAIL" : "PHYSICAL")." [".$companycount." companies]<br><br>";
            ?><table border="1" style="width: 1000px;"><tr><td>ID</td><td>type</td><td>quantity</td><td>from certificate</td><td>to certificate</td><td>state</td><td>series master</td><td>series uuid</td><td>note</td></tr><?php
            foreach($shipmentList as $shipment) {
                echo "<tr><td>".$shipment->id."</td><td>".$shipment->shipment_type."</td><td>".$shipment->quantity."</td><td>".$shipment->from_certificate_no."</td><td>".$shipment->to_certificate_no."</td><td>".$shipment->shipment_state."</td><td>".$shipment->series_master."</td><td>".$shipment->series_uuid."</td><td>".$shipment->shipment_note."</td></tr>";
            }
            ?></table><br><?php

            $shopuserList = \ShopUser::find_by_sql("SELECT * FROM shop_user WHERE company_order_id = ".$companyOrder->id."");
            $certificates = array();

            foreach($shopuserList as $shopuser) {

                if(!isset($certificates[$shopuser->company_id]))  {
                    $certificates[$shopuser->company_id] = array();
                }

                $certificates[$shopuser->company_id][] = $shopuser->username;

            }

            echo "Parent id: ".$companyOrder->company_id."<br>";
            echo "Parent ".(isset($certificates[$companyOrder->company_id]) ? "HAS DELIVERY" : "DOES NOT HAVE DELIVERY")."<br>";

            echo "<pre>".print_r($certificates,true)."</pre>";


            echo "STATE: <b>".($isTest ? "TEST-" : "").$state."</b>";

            if(!isset($this->stats["state-".$state])) {
                $this->stats["state-".$state] = 0;
            }
            $this->stats["state-".$state]++;

        }




    }


}