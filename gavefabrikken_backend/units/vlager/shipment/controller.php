<?php

namespace GFUnit\vlager\shipment;
use GFBiz\units\UnitController;
use GFUnit\navision\homerunner\HomerunnerClient;
use GFUnit\navision\homerunner\OrderData;
use GFUnit\vlager\utils\Navision;
use GFUnit\vlager\utils\Template;
use GFUnit\vlager\utils\VLager;
use GFUnit\vlager\utils\VLagerCounter;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {
    }

    public function runhookjob($output=0)
    {

        $cwhm = new CheckWebhook();
        $cwhm->runWebhookCheck($output);

    }

    private $output = 0;
    private $logLines = array();
    private $errorCount = 0;

    private function setOutput($output) {
        $this->output = $output;
    }

    public function runjob($vlager,$output=0) {

        $this->setOutput($output);
        $this->log("START SHIPMENT PROCESSING JOB - PROCESS ALL");

        $vlCounter = new VLagerCounter($vlager,0);
        $vlCounter->loadShipmentsWaiting();
        $waitingShipmentNoList = $vlCounter->getShipmentNoWaiting(true);
        $this->log("Waiting " . count($waitingShipmentNoList) . " items");

        $startTime = time();
        $shipmentsProcessed = 0;

        foreach($waitingShipmentNoList as $itemno) {

            $this->log("");
            $this->log("=====================================");
            $this->log("Start processing itemno ".$itemno);
            $this->log("=====================================");
            $this->log("");

            $shipments = $vlCounter->getWaitingShipmentsByItemNo($itemno);

            foreach($shipments as $shipment) {

                $this->processShipment($vlCounter, $shipment);
                $this->reloadItemStock($vlCounter,$itemno);

                // Commit og start transaktion igen
                \System::connection()->commit();
                \System::connection()->transaction();

                $shipmentsProcessed++;

                // If more than 20 seconds or more than 20 shipments, end run
                $timeDiff = time() - $startTime;
                if($timeDiff > 50 || $shipmentsProcessed > 200) {
                    $this->log("LIMIT REACHED - STOPPING RUN");

                    // Output script to reload page
                    //echo "<script>setTimeout(function(){location.reload();}, 2000);</script>";
                    $this->endRun();

                    return;
                }

            }
        }


        $this->endRun();
    }

    private function reloadItemStock($counter,$itemno="") {

        if($itemno != "") {
            $old = $counter->getWarehouseAvailableNow($itemno);
        }

        $counter->reloadItemStock();
        $new = $counter->getWarehouseAvailableNow($itemno);

        if($itemno != "") {
            $this->log(" -- Reloaded item stock for ".$itemno." - ".$old." -> ".$new);
        } else {
            $this->log(" -- Reloaded item stock");
        }

    }

    public function runitemall($vlager,$itemno,$output=0) {

        $this->setOutput($output);
        $this->log("START SHIPMENT PROCESSING JOB - PROCESS ALL ".$itemno);

        if(trimgf($itemno) == "") {
            $this->error("Itemno is missing");
            $this->endRun();
            return;
        }

        $vlCounter = new VLagerCounter($vlager,0);
        $vlCounter->loadShipmentsWaiting();
        $shipments = $vlCounter->getWaitingShipmentsByItemNo($itemno);

        foreach($shipments as $shipment) {
            $this->processShipment($vlCounter, $shipment);
            $this->reloadItemStock($vlCounter,$itemno);

            // Commit og start transaktion igen
            \System::connection()->commit();
            \System::connection()->transaction();

        }

        $this->endRun();
    }

    public function runitemsingle($vlager,$itemno,$output=0) {

        $this->setOutput($output);
        $this->log("START SHIPMENT PROCESSING JOB - PROCESS SINGLE ".$itemno);

        if(trimgf($itemno) == "") {
            $this->error("Itemno is missing");
            $this->endRun();
            return;
        }

        $vlCounter = new VLagerCounter($vlager,0);
        $vlCounter->loadShipmentsWaiting();
        $shipments = $vlCounter->getWaitingShipmentsByItemNo($itemno);

        if(count($shipments) > 0) {
            $shipment = $shipments[0];
            $this->processShipment($vlCounter, $shipment);

            // Commit og start transaktion igen
            \System::connection()->commit();
            \System::connection()->transaction();

            $this->reloadItemStock($vlCounter,$itemno);
        }

        $this->endRun();
    }


    private $itemNoStops = [];

    private function stopRunOnItemNo($itemNo) {
        $this->log("Stop run on itemno: ".$itemNo);
        $this->itemNoStops[] = strtolower(trimgf($itemNo));
    }

    private function hasItemNoStopped($itemNo) {
        return in_array(strtolower(trimgf($itemNo)), $this->itemNoStops);
    }

    private function processShipment(VLagerCounter $counter,\Shipment $shipment) {

        $shipment = \Shipment::find($shipment->id);
        
        $this->log("Start processing shipment ".$shipment->id." itemno ".$shipment->itemno." created on ".$shipment->created_date->format("Y-m-d H:i:s"));

        $itemno = $shipment->itemno;
        $quantity = $shipment->quantity;

        // Check if itemno is stopped in runner
        if($this->hasItemNoStopped($itemno)) {
            $this->log(" - Itemno is stopped");
            return;
        }

        if(strstr($itemno,"240102")) {
            $this->error(" - Itemno 240102 is blocked, aborting!");
            $this->createShipmentBlock($shipment, "Itemno 240102 is blocked, aborting!", true);
            return;
        }

        // Check shipment
        if($shipment->shipment_state != 1) {
            $this->error(" - Shipment is not in state 1 - should not be in this run");
            $this->stopRunOnItemNo($itemno);
            return;
        }
        
        if($shipment->handler != $counter->getVLagerObj()->code) {

            if($shipment->handler == "navision") {
                $this->log(" - Shipment handler is navision, ignore and continue");
                return;
            }

            $this->error(" - Shipment handler (".$shipment->handler.") is not the same as vlager (".$counter->getVLagerObj()->code.")");
            $this->createShipmentBlock($shipment, "Shipment handler (".$shipment->handler.") is not the same as vlager (".$counter->getVLagerObj()->code.")", false);
            return;
        }

        if($counter->getVLagerObj()->active != 1) {
            $this->error(" - VLager is not active");
            return;
        }

        // Check it has data
        $dataErrorList = [];
        if(trimgf($shipment->shipto_name) == "") {
            $dataErrorList[] = "Ship to name is empty";
        }
        if(strlen(trimgf($shipment->shipto_name)) <= 2) {
            $dataErrorList[] = "Ship to name is too short";
        }

        if(trimgf($shipment->shipto_contact) == "") {
            $dataErrorList[] = "Ship to contact is empty";
        }
        if(strlen(trimgf($shipment->shipto_contact)) <= 2) {
            $dataErrorList[] = "Ship to contact is too short";
        }

        if(trimgf($shipment->shipto_address) == "") {
            $dataErrorList[] = "Ship to address is empty";
        }
        if(strlen(trimgf($shipment->shipto_address)) <= 2) {
            $dataErrorList[] = "Ship to address is too short";
        }

        if(trimgf($shipment->shipto_postcode) == "") {
            $dataErrorList[] = "Ship to postcode is empty";
        }
        if(strlen(trimgf($shipment->shipto_postcode)) <= 2) {
            $dataErrorList[] = "Ship to postcode is too short";
        }

        if(trimgf($shipment->shipto_city) == "") {
            $dataErrorList[] = "Ship to city is empty";
        }

        if(trimgf($shipment->shipto_country) == "") {
            $dataErrorList[] = "Ship to country is empty";
        }

        if(trimgf($shipment->shipto_phone) == "") {
            $dataErrorList[] = "Ship to phone is empty";
        }
        if(strlen(trimgf($shipment->shipto_phone)) <= 2) {
            $dataErrorList[] = "Ship to phone is too short";
        }

        // Special country rules
        $country = strtoupper(trimgf($shipment->shipto_country));
        $isSE = ($country == 5 || $country == "SE" || $country == "SVERIGE");
        $isDK = ($country == 1 || $country == "DK" || $country == "DANMARK");
        $isNO = ($country == 4 || $country == "NO" || $country == "NORGE");

        $zip = str_replace(array(" ","-"), "", $shipment->shipto_postcode);
        $phone = str_replace(array(" ","-","+"), "", $shipment->shipto_phone);

        if($isSE) {

            if(strlen($zip) != 5) {
                $dataErrorList[] = "Ship to postcode is not 5 digits";
            }

            if(strlen($phone) < 10) {
                $dataErrorList[] = "Ship to phone is not 10 digits";
            }

        }

        if($isDK) {
            if(strlen($zip) != 4) {
                $dataErrorList[] = "Ship to postcode is not 4 digits";
            }
            if(strlen($phone) < 8) {
                $dataErrorList[] = "Ship to phone is not 8 digits";
            }
        }

        if($isNO) {
            if(strlen($zip) != 4) {
                $dataErrorList[] = "Ship to postcode is not 4 digits";
            }
            if(strlen($phone) < 8) {
                $dataErrorList[] = "Ship to phone is not 8 digits";
            }
        }

        // Special vlager rules for userdata
        if($counter->getVLagerID() == 1) {
            if(!$isSE) {
                $dataErrorList[] = "Ship to country is not Sweden, do not handle with default vlager";
            }
        }

        // Check for errors
        if(count($dataErrorList) > 0) {
            $this->error(" - Shipment data is missing: <br>- ".implode("<br>- ",$dataErrorList));
            $this->createShipmentBlock($shipment, "Shipment data is missing: <br>- ".implode("<br>- ",$dataErrorList), false);
            return;
        }

        // Check its shopuser is still active and well
        try {
            $order = \Order::find($shipment->to_certificate_no);
        } catch (\Exception $e) {
            $this->error(" - Order not found");
            $this->createShipmentBlock($shipment, "Shipments order not found: ".$shipment->to_certificate_no, true);
            return;
        }

        if($order->user_username != $shipment->from_certificate_no) {
            $this->error(" - Order user is not the same as shipment user");
            $this->createShipmentBlock($shipment, "Order user is not the same as shipment user: ".$shipment->to_certificate_no, true);
            return;
        }

        try {
            $shopUser = \ShopUser::find($order->shopuser_id);
        } catch (\Exception $e) {
            $this->error(" - ShopUser not found");
            $this->createShipmentBlock($shipment, "ShopUser not found: ".$order->shopuser_id, true);
            return;
        }

        if($shopUser->username != $shipment->from_certificate_no) {
            $this->error(" - ShopUser is not the same as shipment user");
            $this->createShipmentBlock($shipment, "ShopUser is not the same as shipment user", true);
            return;
        }

        if($shopUser->blocked == 1) {
            $this->error(" - ShopUser is blocked");
            $this->createShipmentBlock($shipment, "ShopUser is blocked/credited", false);
            return;
        }

        if($shopUser->shutdown == 1) {
            $this->error(" - ShopUser is shutdown");
            $this->createShipmentBlock($shipment, "ShopUser is shutdown/closed without credit", false);
            return;
        }

        if($shopUser->is_replaced == 1) {
            $this->error(" - ShopUser is replaced");
            $this->createShipmentBlock($shipment, "ShopUser has been replaced", false);
            return;
        }

        $this->log(" - Shipment is ok");

        // Check if has been sent before
        try {
            $checkSql = "SELECT * FROM homerunner_log where input LIKE '%Order no: ".$shipment->id."%' and response_code = 201";
            if(count(\HomerunnerLog::find_by_sql($checkSql)) > 0) {
                $this->error(" - Shipment has been sent before");
                $this->createShipmentBlock($shipment, "Shipment has been sent before, check db", true);
                return;
            }
        } catch (\Exception $e) {
            $this->error(" - Error checking if shipment has been sent before");
            $this->createShipmentBlock($shipment, "Error checking if shipment has been sent before: ".$e->getMessage(), true);
            return;
        }

        // Check if it is available in warehouse
        $counter->reloadItemStock();

        // Check availability
        $available = $counter->getWarehouseAvailableNow($itemno);
        if($quantity > $available) {
            $this->error(" - Shipment quantity is higher than available");
            $this->stopRunOnItemNo($itemno);
            return;
        }

        // It is ok and it is available
        $shipmentItems = null;

        // Make order xml for homerunner
        try {

            $orderJSON = OrderData::shipmentToJSON($shipment);
            $shipmentItems = OrderData::getShipmentJSONItems();

        } catch (\Exception $e) {
            $this->error(" - Error creating order XML: ".$e->getMessage());
            return;
        }
        
        if($shipmentItems == null || count($shipmentItems) == 0) {
            $this->error(" - Error creating order XML: No items");
            return;
        }

        // Check if it is a BOM item
        if($counter->isBOMItem($itemno)) {
            $this->log(" - Shipment item is BOM");
        }
        else {
            $this->log(" - Shipment item is not BOM");
            if(count($shipmentItems) != 1) {
                $this->error(" - Error creating order XML: non BOM item has more than 1 item");
                return;
            }
        }

        // Send to homerunner
        try {
            
            $client = new HomerunnerClient();
            $response = $client->createOrder($orderJSON);

            $this->log("HOMERUNNER RESPONSE");
            $this->log(print_r($response,true));

            if(isset($response["order_id"]) && trimgf($response["order_id"]) != "") {
                $shipment->nav_order_no = $response["order_id"];
            }

            if(isset($response["order_id"]) && trimgf($response["order_id"]) != "") {
                $shipment->nav_order_no = $response["order_id"];
            }

            if(isset($response["shipments"]) && isset($response["shipments"][0]) && isset($response["shipments"][0]["package_number"])) {
                $shipment->consignor_labelno = $response["shipments"][0]["package_number"];
            }

            //throw new \Exception("Send to homerunner stopped, not approved yet - but seems good!");
            // EXTRACT EXTERNAL ORDER ID AND SAVE TO SHIPMENT!

        } catch (\Exception $e) {
            
            // Handle error
            $this->error(" - Error sending to homerunner: ".$e->getMessage());
            $this->createShipmentBlock($shipment, "HomeRunner service error: ".$e->getMessage(), false);
            return;
            
        }
        
        // Update shipment
        $shipment->shipment_state = 2;
        $shipment->shipment_sync_date = date('d-m-Y H:i:s');
        $shipment->save();

        // Update shopuser
        $shopUser->delivery_state = 2;
        $shopUser->save();

        $this->log(" - Updated shipment and shopuser");

        // Save in vlager item
        foreach($shipmentItems as $itemno => $quantity) {
            if(VLager::updateLagerItem($counter->getVLagerID(), $itemno, -1*$quantity, "Afsendt gavekort ".$shipment->from_certificate_no,0,$shipment->id)) {
                $this->log(" - Updated VLager item ".$itemno." with -".$quantity);
            } else {
                $this->error(" - Error updating VLager item ".$itemno." with -".$quantity);
                $this->stopRunOnItemNo($itemno);
            }
        }

        $this->log(" - Finished processing shipment ".$shipment->id." - OK");
        


    }

    private function log($message) {
        if($this->output) {
            echo $message."<br>";
        }
        $this->logLines[] = $message;
    }

    private function error($message) {
        if($this->output) {
            echo "<span style='color: red;'>".$message."</span><br>";
        }
        $this->logLines[] = "<span style='color: red;'>".$message."</span>";
        $this->errorCount++;
    }

    private function createShipmentBlock($shipment,$message,$isTech) {

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        \BlockMessage::createShipmentBlock($companyOrder->company_id, 0, $shipment->id, "SHIPMENT_PRIVATE_CHECK",$message,$isTech);
        $this->error(" - Shipment block created!");
    }

    private function resetLogs() {
        $this->logLines = array();
        $this->errorCount = 0;
    }

    private function endRun() {
        if($this->errorCount > 0) {
            //mailgf('sc@interactive.dk', "VLAGER - SHIPMENT - Process error", implode("\n",$this->logLines));
            exit();
        }
    }

    public function outbound($vlager)
    {

        Template::templateTop();
        Template::outputFrontendHeader("/gavefabrikken_backend/index.php?rt=unit/vlager/panel/front/".$vlager,\VLager::find($vlager));

        $vlCounter = new VLagerCounter($vlager,0);
        $vlCounter->loadShipmentsWaiting();

        ?><table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th>Varenr</th>
            <th>Beskrivelse</th>
            <th>Sampak</th>
            <th>Klar til afsendelse</th>
            <th>Kan sendes</th>
            <th>Send</th>
        </tr>
        </thead>
        <tbody><?php

        $waitingShipmentNoList = $vlCounter->getShipmentNoWaiting(true);
        foreach($waitingShipmentNoList as $itemno) {

            $shipments = $vlCounter->getWaitingShipmentsByItemNo($itemno);
            $isBOM = $vlCounter->isBOMItem($itemno);
            $available = $vlCounter->getWarehouseAvailableNow($itemno);

            $rowColor = "#FFFFFF";

            // If all can be send, green, none red, else yellow
            if(count($shipments) <= $available) {
                $rowColor = "#C8E6C9";
            } else if($available == 0) {
                $rowColor = "#FFCDD2";
            } else {
                $rowColor = "#FFF9C4";
            }

            ?><tr style="background: <?php echo $rowColor; ?>">
                <td><?php echo $itemno; ?></td>
                <td><?php echo $vlCounter->getItemName($itemno); ?></td>
                <td><?php echo $isBOM ? "JA" : "NEJ"; ?></td>
                <td><?php echo count($shipments); ?></td>
                <td><?php echo $available; ?></td>
                <td><a href="index.php?rt=unit/vlager/shipment/runitemsingle/<?php echo $vlager; ?>/<?php echo $itemno; ?>/1">KØR 1</a> | <a href="index.php?rt=unit/vlager/shipment/runitemall/<?php echo $vlager; ?>/<?php echo $itemno; ?>/1">KØR ALLE</a></td>
            </tr><?php
        }

        ?></tbody>
        </table><?php

        Template::templateBottom();

    }

    public function report($vlager=0)
    {

        $vlagerObj = \VLager::find($vlager);

        // Action set
        if(($_POST["action"] ?? "") == "download") {

            // Parse inputs
            $dateSelection = $_POST["dateSelection"] ?? "lastSeen";
            $fromDate = $_POST["fromDate"] ?? "";
            $toDate = $_POST["toDate"] ?? "";
            $concept = $_POST["concept"] ?? "";

            if($dateSelection == "lastSeen") {
                if($vlagerObj->ecoreport_datetime == null) {
                    $vlagerObj->ecoreport_datetime = new \DateTime();
                    $vlagerObj->ecoreport_datetime->modify("-1 week");
                }
                $fromDate = $vlagerObj->ecoreport_datetime->format("Y-m-d H:i:s");
                $toDate = date("Y-m-d H:i:s");
            }

            // Make report sql
            $sql = "SELECT vi.itemno as ItemNo, cardshop_settings.concept_code as Concept, -1*sum(vil.quantity) as Quantity
            FROM vlager_item_log vil, vlager_item vi, shipment, company_order, cardshop_settings
            where vil.vlager_item_id = vi.id and shipment_id > 0 and vil.shipment_id = shipment.id and shipment.companyorder_id = company_order.id and company_order.shop_id = cardshop_settings.shop_id and vil.log_time > '".$fromDate."' and vil.log_time < '".$toDate."' ".($concept > 0 ? " and cardshop_settings.shop_id = ".$concept : "")."
            GROUP BY cardshop_settings.concept_code, vi.itemno order by cardshop_settings.concept_code, vi.itemno";

            // Load data
            $lagerItems = \VLagerItemLog::find_by_sql($sql);


            // Create excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $headers = [
                'A1' => 'Concept',
                'B1' => 'ItemNo',
                'C1' => 'Quantity'
            ];

            // Set headers and style
            foreach($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Style header row
            $sheet->getStyle('A1:O1')->getFont()->setBold(true);
            $sheet->getStyle('I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFE0');
            $sheet->getStyle('M1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('ADD8E6');

            // Current row
            $row = 2;
            foreach($lagerItems as $lager) {

                $sheet->setCellValue('A'.$row, $lager->concept);
                $sheet->setCellValue('B'.$row, $lager->itemno);
                $sheet->setCellValue('C'.$row, $lager->quantity);
                $row++;
            }

            // Output file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $shopName = "";
            if($concept > 0) {
                $settings = \CardshopSettings::find_by_sql("select * from cardshop_settings where shop_id = ".$concept);
                $shopName = $settings[0]->concept_code;
            }

            // Headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="vlager-transfered-'.$vlagerObj->code.'-'.($shopName != "" ? $shopName.'-' : '').''.(date("dmY",strtotime($fromDate))).'-'.(date("dmY",strtotime($fromDate))).'.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');

            // Update vlagerobj
            if($dateSelection == "lastSeen") {
                $vlagerObj->ecoreport_datetime = new \DateTime();
                $vlagerObj->save();
                \system::connection()->commit();
            }

            /*
            echo "<pre>";
            print_r($_POST);
            echo "</pre>";

            echo $sql;

            $vil = \VLagerItemLog::find_by_sql($sql);
            echo "<pre>";
            print_r($vil);
            echo "</pre>";
            */

            exit();
        }

        Template::templateTop();
        Template::outputFrontendHeader("/gavefabrikken_backend/index.php?rt=unit/vlager/shipment/report/".$vlager,\VLager::find($vlager));

        $vlagerObj = \VLager::find($vlager);

        $vlCounter = new VLagerCounter($vlager,0);
        $vlCounter->loadShipmentsWaiting();

        $lastEcoDatetime = $vlagerObj->ecoreport_datetime;
        if($lastEcoDatetime == null) {
            $lastEcoDatetime = new \DateTime();
            $lastEcoDatetime->modify("-1 week");
        }

        ?><br><br>
        <div style="padding: 50px;">
            <h2>Økonomi rapport: <?php echo $vlagerObj->name; ?></h2>
        <form method="post" action="/gavefabrikken_backend/index.php?rt=unit/vlager/shipment/report/<?php echo $vlager; ?>">
            <!-- Radio buttons for selection -->
            <div class="form-check">
                <input class="form-check-input" type="radio" name="dateSelection" id="lastSeen" value="lastSeen" checked>
                <label class="form-check-label" for="lastSeen">
                    Siden sidst: <?php echo $lastEcoDatetime->format("Y-m-d H:i"); ?>
                </label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="dateSelection" id="dateIntervalRadio" value="dateInterval">
                <label class="form-check-label" for="dateIntervalRadio">
                    Bestemt dato interval
                </label>
            </div>

            <!-- Date pickers, initially hidden -->
            <div id="dateInterval" class="mt-3" style="display: none;">
                <div class="mb-3">
                    <label for="fromDate" class="form-label">Fra:</label>
                    <input type="datetime-local" class="form-control" id="fromDate" name="fromDate" value="<?php

                    // echo datetime in format yyyy-mm-ddThh:mm
                    echo $lastEcoDatetime->format("Y-m-d\TH:i");

                    ?>">
                </div>
                <div class="mb-3">
                    <label for="toDate" class="form-label">Til:</label>
                    <input type="datetime-local" class="form-control" id="toDate" name="toDate" value="<?php
                    $toDate = time();

                    // echo datetime in format yyyy-mm-ddThh:mm
                    echo date("Y-m-d\TH:i", $toDate);

                    ?>">
                </div>
            </div>

            <!-- Concept selector -->
            <div class="mb-3">
                <label for="concept" class="form-label">Koncept:</label>
                <select id="concept" name="concept" class="form-select">
                    <option value="">Alle</option>
                    <?php

                    $shops = $vlCounter->getVLagerShops();
                    foreach($shops as $shop) {
                        echo "<option value='".$shop->shop_id."'>".$shop->concept_code."</option>";
                    }

                    ?>
                </select>
            </div>

            <!-- Hidden action field -->
            <input type="hidden" name="action" value="download">

            <button type="submit" class="btn btn-primary">Download</button>

        </form>
        </div>
        <script>
            // JavaScript to toggle date pickers visibility
            document.querySelectorAll('input[name="dateSelection"]').forEach((radio) => {
                radio.addEventListener('change', function() {
                    document.getElementById('dateInterval').style.display = this.value === 'dateInterval' ? 'block' : 'none';
                });
            });
        </script>
        <?php

        Template::templateBottom();
    }


    public function processhomerunnerlog()
    {

        // Run script for 10 seconds, make check to stop after 10 seconds
        $startTime = time();
        $runTime = 20;
        $run = true;

        while($run) {

            // Check if time is up
            if(time() - $startTime > $runTime) {
                $run = false;
            }

            // Load homerunner logs
            $logs = \HomerunnerLog::find_by_sql("select * from homerunner_log where shipment_id is null limit 50");

            // Process logs
            foreach($logs as $log) {

                // Process log
                $this->processHomerunnerLogItem($log);

            }


            // Sleep for 1 second
            sleep(1);

        }

        \System::connection()->commit();

    }

    private function processHomerunnerLogItem($log) {

        echo "Checking ".$log->id."<br>";
        echo $log->input."<br>";

        // Find log
        $hrlog = \HomerunnerLog::find($log->id);

        // Empty, no shipment
        if($hrlog->input == null || trimgf($hrlog->input) == "" || trimgf($hrlog->input) == "null") {
            $hrlog->shipment_id = 0;
            $hrlog->save();
            echo "Settings shipment to 0";
            return;
        }

        $json = json_decode($log->input,true);
        if($json == null) {
            echo "COULD NOT DECODE CONTENT";
            return;
        }

        $json = json_decode($json,true);

        echo "<pre>".print_r($json,true)."</pre>";

        if(isset($json["order_number"]) && intvalgf($json["order_number"]) > 0) {
            $hrlog->shipment_id = intvalgf($json["order_number"]);
            $hrlog->save();
            echo "Settings shipment to ".intvalgf($json["order_number"]);
            return;
        }

        echo "Could not find order number: ".$json["order_number"]."<br>";


    }


}