<?php

namespace GFUnit\postnord\sync;

class SyncRunner
{

    const HANDLE_MAX_SHIPMENTS = 3000;

    private $testMode;
    private $output;
    private $ftpClient;
    private $validatedItemNoList;
    private $updateItemNoList;
    private $pnVarenrItems;
    private $aliasMap;

    public function __construct($testMode=false,$output=false)
    {
        $this->testMode = $testMode;
        $this->output = $output;
    }

    public function runSync() {

        $this->log("Start postnord sync");
        $this->validatedItemNoList = array();
        $this->updateItemNoList = array();
        $this->pnVarenrItems = array();

        // Handle downloaded files
        $runner = new HandleDownloads($this->output);
        $runner->runHandler();

        // Handle previous send states
        $this->handleSentStates();

        // Load shipments
        $shipmentList = $this->getWaitingShipments();
        $this->log("Loaded: ".countgf($shipmentList)." to transfer, handle max: ".self::HANDLE_MAX_SHIPMENTS);

        // Get itemno aliases
        $aliasList = \PostnordVarenr::find_by_sql("SELECT * FROM `postnord_varenr` WHERE navalias != ''");
        $this->aliasMap = array();
        foreach($aliasList as $alias) {
            $this->aliasMap[$alias->navalias] = $alias->varenr;
        }
        
        // Ordre xml
        $shipmentIdList = array();
        $orderXML = "";

        // Process shipments
        foreach ($shipmentList as $index => $s) {
            $this->log("Process shipment ".$index.":  [".$s->id."] ".$s->shipment_type." - ".$s->shipto_name);
            $genXML = $this->processShipment($s);

            if($genXML != "") {
                $shipmentIdList[] = $s->id;
                $orderXML .= $genXML;
            }

            if($index+1 == self::HANDLE_MAX_SHIPMENTS) {
                break;
            }
        }

        $this->log("Wrong delivery state list: ".implode(",",$this->wrongDeliveryStateList));

        $this->log("Processed shipment ids (".countgf($shipmentIdList)."): ".implode(",",$shipmentIdList));
        //echo "<pre>".htmlspecialchars($orderXML)."</pre><br>";
        //exit();

        if($orderXML != "") {

            $this->log("Writing order xml to envelope");

            // Generate full xml and check
            try {
                $orderEnvelopeXml = OutboundOrderXML::generateEnvelopeXML($orderXML);
            } catch (\Exception $e) {
                $this->log(" - Error generating order envelope xml: ".$e->getMessage());
                $this->mailLog("Error generating shipment order xml","Error generating xml on order envelope: ".$e->getMessage()."<br><br>".$orderXML);
                return;
            }

            $this->log("Transfer order xml to ftp queue");

            // Prepare ftp queue
            $ftpQueue = array(
                "ftpserver_id" => ($this->testMode ? 1 : 2),
                "file_name" => "order-".date("dmYHis")."-".rand(10,99),
                "file_content" => $orderEnvelopeXml,
                "file_type" => "xml",
                "webhook_success" => "postnord",
                "note" => "postnord:shipment"
            );

            // Write to FTP queue
            try {
                $queue = \ftpqueue::createFtpQueue($ftpQueue);
            } catch (\Exception $e) {

                $this->log(" - ABORT shipment xml file could not be added to ftp queue - ".$e->getMessage());
                $this->mailLog("Shipment xml failed on ftp queue",$e->getMessage());
                return;
            }

            $this->log("Successfully added order xml to ftp queue id ".$queue->id);

        }

        // Update varenr missing from postnord
        $this->log("Found ".countgf($this->updateItemNoList)." item numbers to update.");
        if(count($this->updateItemNoList) > 0) {
            $this->processNewVarenrFile();
        }

        // Commit file
        \System::connection()->commit();
        \System::connection()->transaction();

      
    }

    /**
     * HANDLE WAITING VARENR
     */

    private function handleSentStates()
    {

        // Handle sent states for varenr
        $pnVareNrUpdateList = \PostnordVarenr::find_by_sql("SELECT postnord_varenr.*, ftp_queue.sent as ftpsent, ftp_queue.error as ftperror, ftp_queue.error_message as ftperrormessage, ftp_queue.sent_datetime as ftpsentdatetime FROM `postnord_varenr`, ftp_queue where postnord_varenr.state = 1 && postnord_varenr.lastsend_file_id = ftp_queue.id && ftp_queue.sent = 1");
        $this->log("Handle prev varenr sent, found: ".countgf($pnVareNrUpdateList));

        if(count($pnVareNrUpdateList) > 0) {

            $errorMessages = array();
            foreach($pnVareNrUpdateList as $vareNrUpdate) {

                $pnVarenr = \PostnordVarenr::find($vareNrUpdate->id);
                if($vareNrUpdate->ftperror == 0) {
                    $pnVarenr->state = 2;
                    $pnVarenr->save();
                } else {
                    $errorMessages[] = "Error transmitting ftp data for varenr: ".$pnVarenr->varenr." in ftp file no ".$pnVarenr->lastsend_file_id.": ".$vareNrUpdate->ftperrormessage;
                    $pnVarenr->state = 3;
                    $pnVarenr->error = $pnVarenr->ftperrormessage;
                    $pnVarenr->save();
                }

                // If any errors
                if(count($errorMessages) > 0) {
                    $this->log("- Found ".countgf($errorMessages)." errors in updates<br> - - ".implode("<br> - - ",$errorMessages));
                    $this->mailLog("Error in varenr state update"," - - ".implode("<br> - - ",$errorMessages));
                }

                // Commit updates
                \System::connection()->commit();
                \System::connection()->transaction();

            }

        }

    }


    /**
     * UPDATE VARENR IN POSTNORD
     */

    private function processNewVarenrFile()
    {

        // Generate varenr xml for each
        $vareXML = "";
        foreach($this->updateItemNoList as $itemNo) {
            $vareXML .= $this->updateVarenr($itemNo);
        }

        // If no xml, abort
        if($vareXML == "") {
            $this->log("ABORT Item update file, xml list empty");
            return;
        }

        // Generate envelope
        try {
            $xml = ItemUpdateXML::generateEnvelopeXML($vareXML);
            //echo "<pre>".htmlspecialchars($xml)."</pre><br>";
        } catch(\Exception $e) {
            $this->log(" - Abort generating items xml file: ".$e->getMessage());
            $this->mailLog("Varenr xml envelope failed",$e->getMessage());
            return;
        }

        // Test output file
        //header('Content-Type: text/text; charset=utf-8');
        //echo "<pre>".htmlspecialchars($xml)."</pre><br>";

        // Prepare ftp queue
        $ftpQueue = array(
            "ftpserver_id" => ($this->testMode ? 1 : 2),
            "file_name" => "itemupdate-".date("dmYHis")."-".rand(10,99),
            "file_content" => $xml,
            "file_type" => "xml",
            "webhook_success" => "postnord",
            "note" => "postnord:itemupdate"
        );

        // Write to FTP queue
        try {
            $queue = \ftpqueue::createFtpQueue($ftpQueue);
        } catch (\Exception $e) {
            $this->log(" - ABORT itemupdate file could not be added to ftp queue - ".$e->getMessage());
            $this->mailLog("Varenr xml failed on ftp queue",$e->getMessage());
            return;
        }

        // Update varenr
        foreach($this->pnVarenrItems as $pnVarenr) {
            $pnVarenr->lastsend_file_id = $queue->id;
            $pnVarenr->save();
        }

        // Output log
        $this->log("Wrote itemupdate xml file with ".countgf($this->pnVarenrItems)." items, to ftp queue id ".$queue->id);



    }

    private function updateVarenr($itemNo)
    {
        $this->log(" - Update varenr ".$itemNo);

        // Get from navision
        $items = \NavisionItem::find_by_sql("SELECT * FROM navision_item where no = '".$itemNo."' && deleted is null && language_id = 1");
        if(count($items) == 0) {
            $this->log(" - - ABORT: Could not find item no ".$itemNo." in navision");
            return "";
        }
        $item = $items[0];

        // Generate xml file
        $this->log(" - - Generate xml file");
        try {
            $xml = ItemUpdateXML::generateItemXML($item);
        } catch(\Exception $e) {
            $this->log(" - ABORT xml generation for item ".$item->no." - ".$e->getMessage());
            $this->mailLog("Varenr xml failed on itemno ".$item->no,$e->getMessage());
            return "";
        }

        // Create postnord varenr
        $pnVarenr = new \PostnordVarenr();
        $pnVarenr->varenr = $item->no;
        $pnVarenr->created_date = date('d-m-Y H:i:s');
        $pnVarenr->lastsend_doc = trimgf($xml);
        $pnVarenr->lastsend_date = date('d-m-Y H:i:s');
        $pnVarenr->language_id = $item->language_id;
        $pnVarenr->state = 1;
        $this->pnVarenrItems[] = $pnVarenr;

        //echo "<pre>".htmlspecialchars($xml)."</pre><br>";
        return $xml;

    }


    /**
     * GET WAITING SHIPMENTS
     */

    public function getWaitingShipments() {
        $list = \Shipment::find_by_sql("SELECT * FROM shipment WHERE shipment_state = 1 && handler = 'postnord' && companyorder_id in (select id from company_order where shop_id in (select shop_id from cardshop_settings where language_code = 1))");
        return $list;
    }

    /**
     * PROCESS SHIPMENT
     */

    private $wrongDeliveryStateList = array();

    private function processShipment($s)
    {

        // Load fresh version of shipment
        $shipment = \Shipment::find($s->id);

        // Check shipment
        if($shipment == null || $shipment->id == 0 || $shipment->handler != 'postnord' || $shipment->shipment_state != 1) {
            $this->log(" - Abort sync shipment, does not seem valid");
            return "";
        }

        // Get varenr
        try {
            $itemList = $shipment->getVarenrCountList(true,true,true);
        } catch (\Exception $e) {
            $this->log("- Abort, error resolving item numbers on order: ".$e->getMessage());
            $this->mailLog("varenr tjek failed on shipment ".$shipment->id,$e->getMessage());
            return "";
        }

        // Replace with alias
        foreach($itemList as $index => $item) {
            echo "Check alias for ".$item["itemno"]."<br>";
            if(isset($this->aliasMap[$item["itemno"]]) && trimgf($this->aliasMap[$item["itemno"]]) != "") {
                $itemList[$index]["itemno"] = $this->aliasMap[$item["itemno"]];
            }
        }

        // Check varenr
        foreach($itemList as $item) {
            if($this->checkVarenr($item["itemno"],$item["quantity"]) == false) {
                $this->log("- Abort, item no ".$item["itemno"]." not ready for sync yet.");
                return "";
            }
        }

        // Generate xml
        try {
            $orderXml = OutboundOrderXML::generateOrderXML($shipment,$itemList);
        } catch (\Exception $e) {
            $this->log(" - Error generating order xml: ".$e->getMessage());
            $this->mailLog("Error generating shipment order xml","Error generating xml on shipment ".$shipment->id.": ".$e->getMessage());
            return "";
        }

        //echo "<pre>".htmlspecialchars($orderEnvelopeXml)."</pre><br>";

        // Find shopuser
        $shopUser = null;
        if($shipment->shipment_type == "privatedelivery") {

            try {

                $order = \Order::find("first",array("conditions" => array("user_username" => $shipment->from_certificate_no, "id" => $shipment->to_certificate_no)));
                if($order == null) throw new \Exception("Could not find order width id: ".$shipment->to_certificate_no.", username: ".$shipment->from_certificate_no);
                $shopUser = \ShopUser::find($order->shopuser_id);

                if($shopUser == null) throw new \Exception("Could not find shopuser with shopuser id ".$order->shopuser_id);
                else if($shopUser->username != $shipment->from_certificate_no) throw new \Exception("Username does not match on shipment: ".$shopUser->username);
                else if($shopUser->delivery_state != 1) {
                    $this->wrongDeliveryStateList[] = $shopUser->id;
                    throw new \Exception("Shopuser [".$shopUser->id."], unexpected delivery state ".$shopUser->delivery_state);
                } else if($shopUser->blocked == 1 || $shopUser->shutdown == 1) {
                    $this->wrongDeliveryStateList[] = $shopUser->id;
                    throw new \Exception("Shopuser [".$shopUser->id."], unexpected block state ");
                }

                $shopUser->delivery_state = 2;
                $shopUser->save();

            } catch (\Exception $e) {
                $this->log(" - Error backtracking shopuser and order: ".$e->getMessage());
                $this->mailLog("Error backtracking shopuser and order","Error backtracking shopuser and order ".$shipment->id.": ".$e->getMessage()."<br>".$e->getFile()." @ ".$e->getLine());

                return "";
            }
        }

        // Update shipment
        $shipment->shipment_state = 2;
        $shipment->shipment_sync_date = date('d-m-Y H:i:s');
        $shipment->save();

        // Update sent on ordre
        foreach($itemList as $item) {
            $postnordVare = \PostnordVarenr::find("first", array("conditions" => array("varenr" => $item["itemno"])));
            $postnordVare->sent_since_update = $postnordVare->sent_since_update +$item["quantity"];
            $postnordVare->save();
        }
        // Return order xml
        return $orderXml;

    }

    private function blockShipment(\Shipment $shipment,$blockType,$description,$isTech=0)
    {
        $this->log(($isTech ? "TECH-" : "")."BLOCK: ".$blockType.": ".$description);
        $shipment->shipment_state = 3;
        $shipment->save();
        \BlockMessage::createShipmentBlock(0,$shipment->companyorder_id,$shipment->id,$blockType,$description,$isTech,$this->syncMessages);
    }

    /**
     * VARENR LIST
     */

    public function checkVarenr($itemNo,$quantity=1,$checkValidated = true) {

        // Already validated, then accept
        if($checkValidated == true && in_array($itemNo,$this->validatedItemNoList)) {
            return true;
        }

        // Already determined to update, then reject
        if($checkValidated == true && in_array($itemNo,$this->updateItemNoList)) {
            return false;
        }

        // Find postnord varer
        $postnordVare =  \PostnordVarenr::find("first",array("conditions" => array("varenr" => $itemNo)));

        // Does not exist, add to update list and reject
        if($postnordVare == null) {
            $this->updateItemNoList[] = $itemNo;
            return false;
        }

        // Check if in stock
        if(($postnordVare->current_stock - $postnordVare->current_reserved - $postnordVare->sent_since_update - $quantity) < 0) {
            // Not in stock, wait
            $this->log("Item ".$itemNo." is not in stock (need ".$quantity." units). Current stock: ".$postnordVare->current_stock.", reserved: ".$postnordVare->current_reserved.", sent since update: ".$postnordVare->sent_since_update.", total surplus: ".($postnordVare->current_stock - $postnordVare->current_reserved - $postnordVare->sent_since_update));
            return false;
        }

        // If state is ok, return true, otherwise wait for ok state
        if($postnordVare->state == 2) {
            return true;
        }

        return false;

    }


    /**
     * HELPER FUNCTIONALITY
     */

    private function log($message) {
        if($this->output == true) {
            echo $message."<br>";
        }
    }

    protected function mailLog($subject,$content)
    {
        $modtager = "sc@interactive.dk";
        $message = "Postnord log<br><br>".$content."\r\n<br>\r\n<br>Data:<br>\r\n<pre>".print_r($_POST,true)."</pre>";
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";
        $result = mailgf($modtager,"postnordsync: ".$subject, $message, $headers);
    }

}