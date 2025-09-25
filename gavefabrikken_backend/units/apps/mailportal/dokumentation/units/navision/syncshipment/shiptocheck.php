<?php

namespace GFUnit\navision\syncshipment;

class ShiptoCheck
{

    private $stats;
    private $output;

    public function run($output=false) {

        $this->stats = array();
        $this->output = $output;
        $sql = "SELECT companyorder_id FROM `shipment` where (shipto_state = 0 or shipto_state = 20) && shipment_type = 'giftcard' GROUP BY companyorder_id having NOW() > DATE_ADD(MAX(created_date), INTERVAL 1 HOUR) or MAX(force_syncnow) = 1";
        $companyorderlist = \Shipment::find_by_sql($sql);

        if($output == true) {
            echo "Preprocessing shipments, found ".countgf($companyorderlist)." orders to process<br>";
        }

        foreach($companyorderlist as $companyorder) {
            $this->preProcessOrder($companyorder->companyorder_id);
        }

        if($output == true) {
            echo "<br>Preprocessing done:<br><pre>" . print_r($this->stats, true) . "</pre>";
        }

        \System::connection()->commit();
        \System::connection()->transaction();

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
                    if($this->output) echo "SERIES MASTER ID ALREADY SET, ABORT!!";
                    $this->mailLog("Check order for multiple masters","Company order id: ".$companyOrder->id."<br>Master: ".$seriesMasterID.", but ".$shipment->id." also master");
                    return;
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

        if($this->output) {
            echo "<br><h2>PREPROCESSING ".$companyOrder->id.": ".$companyOrder->order_no." [<b>".$companyOrder->order_state."</b>] - ".utf8_decode($companyOrder->company_name)." - deadline ".$companyOrder->expire_date->format("d-m-Y")."</h2><br>";
        }

        // If e-mail, set all to state 10 - not relevant
        if($companyOrder->is_email == 1) {
            if($this->output) echo "MAIL CARD - SET shopto_state to 10";
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
            $certMap[$shopuser->company_id][] = intval($shopuser->username);
        }

        // Check if there is holes in delivery
        $hasSplitError = false;
        $splitErrorLog = "";
        foreach($certMap as $company_id => $usernameList) {

            $min = min($usernameList);
            $max = max($usernameList);
            $countList = countgf($usernameList);
            $countNumbers = $max - $min + 1;

            // Add to split log
            $splitErrorLog .= $min. " - ".$max.": ".$countList." counted, interval is ".$countNumbers;
            if($countList != $countNumbers) {
                $hasSplitError = true;
                $splitErrorLog .= " - INCORRECT<br>".implode(",",$usernameList)."<br>";
            }
            $splitErrorLog .= "<br>";

        }

        // If has error, put on sidetrack and mail home
        if($hasSplitError == true) {
            $this->updateShipmentShipToStateByOrderID($companyOrder->id,30);
            $this->mailLog("Error in card intervals","Company order id: ".$companyOrder->id."<br>".$splitErrorLog);
            return;
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
                        if($this->output) echo "MISMATCH IN CARDS ON SHIPMENT ".$shipmentList[0]->id."<br>";
                        $this->mailLog("Cards mismatch on shipment","Company order id: ".$companyOrder->id."<br>Shipment: ".$shipmentList[0]->id."");
                        return;
                    }

                    // Set shipto_state to 10
                    $this->incStatus("shipto_proc_onetoone_parent");
                    $this->updateShipmentShipToStateByOrderID($companyOrder->id,10);

                }
                // More shipments
                else {
                    if($this->output) echo "ONE COMPANY TO MULTIPLE SHIPMENTS PARENT - INVESTIGATE!!!";
                    $this->incStatus("shipto_proc_onetomulti_parent");
                    $this->mailLog("Unhandled shipment type: onetomulti_parent","Company order id: ".$companyOrder->id."<br>1 Company to send to, but there is more than 1 shipment.");
                    if($this->output) echo "<br>====================================================<br>";
                }
            }
            // Cards only on child
            else {

                // Send to 1 shipment
                if(count($shipmentList) == 1) {
                    //if($this->output) echo "ONE COMPANY TO ONE SHIPMENT CHILD - INVESTIGATE";
                    //$this->mailLog("Unhandled shipment type: onetoone_child","Company order id: ".$companyOrder->id."<br>1 Company to send to, child and only 1 shipment.");
                    $shipmentList[0]->from_certificate_no = $companyOrder->certificate_no_begin;
                    $shipmentList[0]->to_certificate_no = $companyOrder->certificate_no_end;
                    $shipmentList[0]->quantity = $companyOrder->quantity;
                    $shipmentList[0]->shipto_state = 10;
                    $shipmentList[0]->save();
                    $this->incStatus("shipto_proc_onetoone_child");
                }

                // Send to multiple shipments
                else {
                    if($this->output) echo "ONE COMPANY TO MULTIPLE SHIPMENTS CHILD - INVESTIGATE";
                    $this->mailLog("Unhandled shipment type: onetomulti_child","Company order id: ".$companyOrder->id."<br>1 Company to send to, child and multiple shipments.");
                    $this->incStatus("shipto_proc_onetomulti_child");
                }
            }

        }
        // Multiple companies
        else {

            // Only 1 shipment for all cards
            if(count($shipmentList) == 1) {

                if($this->output) echo "ONE SHIPMENT, MORE THAN ONE COMPANY - INVESTIGATE";
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

                    if($this->output) echo "HAS PARENT - CREATE CHILD ONLY";
                    $this->incStatus("shipto_proc_multitoone-parent");


                } else {

                    // Generate uuid
                    $seriesUUID = $this->guidv4();

                    // Get shipment and set to shipto adress
                    $shipment = $shipmentList[0];
                    $shipment->series_uuid = $seriesUUID;
                    $shipment->series_master = 0;
                    $shipment->shipto_state = 1;
                    $shipment->shipment_state = 7;
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

                    if($this->output) echo "<br>NO PARENT IN CARDS<br>";
                    $this->incStatus("shipto_proc_multitoone-noparent");
                }

            }
            // Multiple shipments
            else {

                if($seriesMasterID > 0 || $hasUUID == true) {
                    if($this->output) echo "MULTIPLE COMPANY AND SHIPMENTS - USING IDS - INVESTIGATE";
                    $this->mailLog("Unhandled shipment type: multimulti_series ".$seriesMasterID." - ".($hasUUID ? "yes" : "no"),"Company order id: ".$companyOrder->id."<br>Multiple companies and multiple shipments, already has series, check them");
                    $this->incStatus("shipto_proc_multimulti_series");
                }
                else {

                    if($this->output) echo "MULTIPLE COMPANY AND SHIPMENTS - ONE TO ONE - INVESTIGATE";
                    $this->incStatus("shipto_proc_multimulti");

                    $shipExpectation = array();
                    foreach($certMap as $company_id => $certList) {
                        if($this->output) echo "<br>COMPANY: ".$company_id." - ".countgf($certList)." cards - ".min($certList)." - ".max($certList)."<br>";
                        $shipExpectation[min($certList)] = array("count" => countgf($certList),"min" => min($certList), "max" => max($certList));
                    }

                    if($this->output) {
                        echo "<pre>".print_r($shipExpectation,true)."</pre>";
                    }

                    $problems = array();

                    $shipmentValid = true;
                    foreach($shipmentList as $shipment) {
                        if(!isset($shipExpectation[$shipment->from_certificate_no])) {
                            $shipmentValid = false;
                            if($this->output) echo "<br>SHIPMENT MISMATCH - START NO NOT FOUND: ".$shipment->id."<br>";
                            $problems[] = "<br>SHIPMENT MISMATCH - START NO NOT FOUND: ".$shipment->id."<br>";
                            $this->incStatus("ship-mismatch-notfound");

                        }
                        else if($shipExpectation[$shipment->from_certificate_no]["count"] != $shipment->quantity) {
                            $shipmentValid = false;
                            if($this->output) echo "<br>SHIPMENT MISMATCH - COUNT MISMATCH: ".$shipment->id."<br>";
                            $problems[] = "<br>SHIPMENT MISMATCH - COUNT MISMATCH: ".$shipment->id."<br>";
                            $this->incStatus("ship-mismatch-countmismatch");
                        }
                        else if($shipExpectation[$shipment->from_certificate_no]["max"] != $shipment->to_certificate_no) {
                            $shipmentValid = false;
                            if($this->output) echo "<br>SHIPMENT MISMATCH - END NO NOT FOUND: ".$shipment->id."<br>";
                            $problems[] = "<br>SHIPMENT MISMATCH - END NO NOT FOUND: ".$shipment->id."<br>";
                            $this->incStatus("ship-mismatch-endno-mismatch");
                        }
                    }

                    // Valid shipment, send now
                    if($shipmentValid == true) {
                        foreach($shipmentList as $shipment) {
                            $shipment->shipto_state = 10;
                            $shipment->save();
                        }
                    } else {
                        $this->mailLog("Problem in multi_multi series","Company order id: ".$companyOrder->id."<br><pre>".print_r($problems,true)."</pre>");
                    }

                }


            }
        }

        if($this->output) {
            echo "<br>------<br>";
        }

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

        if($this->output) {
            echo "<br>CREATE CHILD SHIPMENT: ".$companyid." - ".countgf($certList)." cards - ".min($certList)." - ".max($certList)."<br>";
        }

        $child = \Company::find($companyid);
        if($child->pid != $companyOrder->company_id) {
            if($this->output) echo "THIS IS NOT A CHILD!<br>";
            $this->mailLog("Cards moved to non-child - PROCESS BLOCKED!","Company order id: ".$companyOrder->id."<br>Has orders on company ".$child->id." that is not a child to ".$companyOrder->company_id);
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
            if($this->output) echo "UPDATE COMPANY ".$companyorderid." shipment ".$shipment->id." => ".$state;
            $shipment->shipto_state = $state;
            $shipment->save();
        }
    }

    /**
     * HELPER FUNCTIONS
     */

    protected function mailLog($subject,$content)
    {
        $modtager = "sc@interactive.dk";
        $message = "Shipment preprocess mail log<br><br>".$content."\r\n<br>\r\n<br>Data:<br>\r\n<pre>".print_r($this->stats,true)."</pre>";
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";
        $result = mailgf($modtager,"shippreproc: ".$subject, $message, $headers);
    }

    private function incStatus($key) {
        if(!isset($this->stats[$key])) $this->stats[$key] = 0;
        $this->stats[$key]++;
    }

    private  function guidv4($data = null) {

        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}