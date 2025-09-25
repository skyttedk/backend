<?php

namespace GFUnit\postnord\sync;

class HandleDownloads
{

    private $outputMessages = false;

    public function __construct($output=true)
    {
        $this->outputMessages = $output;
    }

    public function runHandler()
    {

        $this->log("Handle downloaded files");

        // Get downloaded files not processed
        $downloadedFiles = $this->getDownloadedFiles();
        $this->log("Found: ".countgf( $downloadedFiles));

        $count = 0;
        foreach($downloadedFiles as $file) {

            $this->handleDownloadedFile($file);
            $count++;
            \System::connection()->commit();
            \System::connection()->transaction();
            
        }


    }

    private function handleDownloadedFile($file)
    {

        // Get file content
        $content = $file->file_content;

        // Load xml
        $xml = simplexml_load_string($content);
        if ($xml === false) {

            // Send errors
            $errors = "";
            foreach(libxml_get_errors() as $error) {
                $errors .= "<br>". $error->message;
            }

            // Set error on handler
            return $this->setFileError($file,"XML Parse error ","File: ".$file->file_name."<br>Errors: ".$errors."<br><br>File content:<br>".htmlspecialchars($content));

        }

        // To json and back again
        $json = json_encode($xml);
        $docData = json_decode($json,TRUE);

        $this->log("Handle file #".$file->id.": ".$file->file_name);

        // Check data
        if($docData == null) {
            return $this->setFileError($file,"XML to data: null","File: ".$file->file_name."<br>Errors: ".json_last_error_msg()."<br><br>File content:<br>".htmlspecialchars($content));
        }
        else if(!isset($docData["MessType"]) || trimgf($docData["MessType"]) == "") {
            return $this->setFileError($file,"Missing MessType","File: ".$file->file_name."<br>Errors: ".json_last_error_msg()."<br><br>File content:<br>".htmlspecialchars($content));
        }
        else if(!isset($docData["CreationDate"]) || trimgf($docData["CreationDate"]) == "") {
            return $this->setFileError($file,"Missing CreationDate","File: ".$file->file_name."<br>Errors: ".json_last_error_msg()."<br><br>File content:<br>".htmlspecialchars($content));
        }
        else if(!isset($docData["CreationTime"]) || trimgf($docData["CreationTime"]) == "") {
            return $this->setFileError($file,"Missing CreationDate","File: ".$file->file_name."<br>Errors: ".json_last_error_msg()."<br><br>File content:<br>".htmlspecialchars($content));
        }

        // Find message type
        switch($docData["MessType"]) {
            case "STOCKBALANCEREPORT":
                return $this->handleStockBalanceReport($file,$docData);
            case "OUTBOUNDREPORT":
                return $this->handleOutboundReport($file,$docData);
            case "ADJUSTMENTREPORT":
                return $this->handleAdjustmentReport($file,$docData);
            default:
                return $this->setFileError($file,"Unknown MessType: ".$docData["MessType"],"File: ".$file->file_name."<br>Errors: ".json_last_error_msg()."<br><br>File content:<br>".htmlspecialchars($content));
        }

        return true;

    }

    private function getFileTime($docData) {
        return strtotime($docData["CreationDate"]." ".$docData["CreationTime"]);
    }

    /**
     * OUTBOUND REPORT
     */


    private function handleOutboundReport($file,$docData)
    {

        $this->log("Handle outbound report");

        // Check items
        if(!isset($docData["Orders"]) || !is_array($docData["Orders"]) || countgf($docData["Orders"]) == 0) {
            return $this->setFileError($file,"No orders","Outbound order report, no orders to handle.<br>File: ".$file->file_name."<br><br>File content:<br><pre>".print_r($docData,true)."</pre>");
        }
        if(!isset($docData["Orders"]["Order"]) || !is_array($docData["Orders"]["Order"]) || countgf($docData["Orders"]["Order"]) == 0) {
            return $this->setFileError($file,"No orders order","Outbound order report, no orders order to handle.<br>File: ".$file->file_name."<br><br>File content:<br><pre>".print_r($docData,true)."</pre>");
        }

        if(!isset($docData["Orders"]["Order"][0])) {
            $docData["Orders"]["Order"] = array($docData["Orders"]["Order"]);
        }

        // Handle items
        $okCount = 0;
        $errorCount = 0;
        $errorList = array();

        $this->log("Found ".countgf($docData["Orders"]["Order"])." items in report");
        foreach($docData["Orders"]["Order"] as $item) {
            if($this->handleOutboundReportOrder($file,$docData,$item)) {
                $okCount++;
            } else {
                $errorCount++;
                $errorList[] = $this->lastItemError;
            }
        }

        $this->log("Handled report with ".$okCount." ok updates and ".$errorCount." errors:<br>".implode("<br>",$errorList));

        // Set file handled
        $file->is_handled = 1;
        $file->save();

        return true;

    }

    private function handleOutboundReportOrder($file,$docData,$item) {


        // Get data from order
        $shipmentid = intval($item["OrderHeader"]["OrderNo"]);
        $shipTime = strtotime($item["OrderHeader"]["ShipmentDate"]." ".$item["OrderHeader"]["ShipmentTime"]);

        // Make sure shipments is a list
        if(!isset($item["Shipments"]["Shipment"][0])) {
            $item["Shipments"]["Shipment"] = array($item["Shipments"]["Shipment"]);
        }

        // Make sure orderlines i a list
        if(!isset($item["OrderLines"]["OrderLine"][0])) {
            $item["OrderLines"]["OrderLine"] = array($item["OrderLines"]["OrderLine"]);
        }

        // Find objects
        $shipment = \Shipment::find($shipmentid);
        if($shipment == null) {
            $this->lastItemError = "Could not find shipment id ".$shipmentid;
            return false;
        }

        $shopuser = \ShopUser::find('first',array("conditions" => array("username" => $shipment->from_certificate_no,"is_giftcertificate" => 1)));
        if($shopuser == null) {
            $this->lastItemError = "Could not find shopuser username ".$shipment->from_certificate_no;
            return false;
        }

        // Handle all  shipments and lines
        foreach($item["Shipments"]["Shipment"] as $pnship) {
            foreach($item["OrderLines"]["OrderLine"] as $pnorder) {

                $packageNo = array();

                foreach($pnship["Packages"] as $pnpack) {

                    if(isset($pnpack["PackageNo"]) && is_string($pnpack["PackageNo"])) $packageNo[] = $pnpack["PackageNo"];
                    else if (is_string($pnpack)) $packageNo[] = $pnpack;
                    else if(is_array($pnpack) && isset($pnpack[0])) {
                        foreach ($pnpack as $pnpacknr) {
                            $packageNo[] = $pnpacknr;
                        }
                    }
                    else {
                        print_r($pnpack);
                        exit();
                    }

                }

                // Create report
                $pnReport = new \PostnordOrderReport();
                $pnReport->created_date = date('d-m-Y H:i:s');
                $pnReport->shipment_date = date('d-m-Y H:i:s',$shipTime);
                $pnReport->shipment_id = $shipment->id;
                $pnReport->shop_user_id = $shopuser->id;
                $pnReport->order_id = $shipment->to_certificate_no;
                $pnReport->username = $shopuser->username;
                $pnReport->ftp_download_id = $file->id;

                $pnReport->shipment_no = is_array($pnship["ShipmentNo"]) ? "" : $pnship["ShipmentNo"];
                $pnReport->package_no = implode(",",$packageNo);
                $pnReport->delivery_method = $pnship["DeliveryMethod"];

                $pnReport->itemno = $pnorder["ItemNo"];
                $pnReport->quantity = $pnorder["DeliverQty"];

                $pnReport->save();

                $this->log(" - Order ".$shipmentid." registered with item ".$pnorder["ItemNo"]." in package no: ".$pnReport->shipment_no);
            }
        }

        // Update stock
        foreach($item["OrderLines"]["OrderLine"] as $pnorder) {
            $this->adjustVarenrStock($pnorder["ItemNo"],-1*intval($pnorder["DeliverQty"]),-1*intval($pnorder["DeliverQty"]),$file,$this->getFileTime($docData),"order");
        }

        // Update shipment
        if($shipment->shipment_state == 2) {
            $shipment->shipment_state = 6;
            $shipment->save();
        } else {
            $this->log("Did not update shipment [".$shipment->id."] state, state was ".$shipment->shipment_state);
        }

        return true;

    }

    /**
     * ADJUSTMENT REPORT
     */

    private function handleAdjustmentReport($file,$docData)
    {
        $this->log("Handle adjustment report");

        // Check items
        if(!isset($docData["Transactions"]) || !is_array($docData["Transactions"]) || countgf($docData["Transactions"]) == 0) {
            return $this->setFileError($file,"No items","Adjustment report, no transactions to handle.<br>File: ".$file->file_name."<br><br>File content:<br><pre>".print_r($docData,true)."</pre>");
        }
        if(!isset($docData["Transactions"]["Transaction"]) || !is_array($docData["Transactions"]["Transaction"]) || countgf($docData["Transactions"]["Transaction"]) == 0) {
            return $this->setFileError($file,"No items item","Adjustment report, no transactions transaction to handle.<br>File: ".$file->file_name."<br><br>File content:<br><pre>".print_r($docData,true)."</pre>");
        }

        if(!isset($docData["Transactions"]["Transaction"][0])) {
            $docData["Transactions"]["Transaction"] = array($docData["Transactions"]["Transaction"]);
        }

        // Handle items
        $okCount = 0;
        $errorCount = 0;
        $errorList = array();

        $this->log("Found ".countgf($docData["Transactions"]["Transaction"])." transactions in report");
        foreach($docData["Transactions"]["Transaction"] as $item) {
            if($this->handleAdjustmentReportTransaction($file,$docData,$item)) {
                $okCount++;
            } else {
                $errorCount++;
                $errorList[] = $this->lastItemError;
            }
        }

        $this->log("Handled report with ".$okCount." ok updates and ".$errorCount." errors:<br>".implode("<br>",$errorList));

        // Set file handled
        $file->is_handled = 1;
        $file->save();

        return true;


    }


    /**
     * ADJUSTMENT REPORT
     */

    private function handleAdjustmentReportTransaction($file,$docData,$item)
    {

        // Check varenr
        if(!isset($item["ItemNo"]) || trimgf($item["ItemNo"]) == "") {
            $this->log("Missing item no");
            $this->lastItemError = "No ItemNo";
            return false;
        }

        // Find varenr
        $itemno = trimgf($item["ItemNo"]);
        $quantity = intval($item["Qty"]);

        // Update stock
        $this->adjustVarenrStock($itemno,$quantity,0,$file,$this->getFileTime($docData),"adjustment");

        return true;

    }


    private function adjustVarenrStock($itemno,$adjustment,$reserved,$file,$processTime,$type) {

        $pnVarenr = \PostnordVarenr::find('first',array('conditions' => array("varenr" => $itemno)));

        // Create varenr
        if($pnVarenr == null) {

            $pnVarenr = new \PostnordVarenr();
            $pnVarenr->varenr = $itemno;
            $pnVarenr->created_date =  date('d-m-Y H:i:s');
            $pnVarenr->lastsend_file_id = 0;
            $pnVarenr->lastsend_doc = "";
            $pnVarenr->lastreceive_date = date('d-m-Y H:i:s');
            $pnVarenr->lastreceive_doc = "";
            $pnVarenr->lastreceive_file_id = $file->id;
            $pnVarenr->language_id = 1;
            $pnVarenr->state = 2;
            $pnVarenr->current_stock = 0;
            $pnVarenr->current_reserved = 0;
            $pnVarenr->sent_since_update = 0;
            $pnVarenr->save();

        }

        $this->log(" - Adjusted stock on ".$itemno." from ".$pnVarenr->current_stock." to ".($pnVarenr->current_stock + $adjustment)." and reserved from ".$pnVarenr->current_reserved." to ".($pnVarenr->current_reserved + $reserved));

        // Create new log entry
        $pnLog = new \PostnordVarenrLog();
        $pnLog->itemno = $itemno;
        $pnLog->postnord_varenr_id = $pnVarenr->id;
        $pnLog->ftp_download_id = $file->id;
        $pnLog->process_date =  date('d-m-Y H:i:s',$processTime);
        $pnLog->type = $type;
        $pnLog->stockadjustment = $adjustment;
        $pnLog->stockcount = $pnVarenr->current_stock + $adjustment;
        $pnLog->reserved = $pnVarenr->current_reserved + $reserved;
        $pnLog->created_date = date('d-m-Y H:i:s');
        $pnLog->save();

        // Update varenr
        $pnVarenr->current_stock = $pnVarenr->current_stock + $adjustment;
        $pnVarenr->current_reserved = $pnVarenr->current_reserved + $reserved;

        if($type == "order") {
            $pnVarenr->sent_since_update = $pnVarenr->sent_since_update+1;
        } else {
            $pnVarenr->sent_since_update = 0;
        }

        $pnVarenr->save();



    }

    /**
     * STOCK BALANCE REPORT
     */

    private $lastItemError = "";

    private function handleStockBalanceReport($file,$docData)
    {

        $this->log("Handle stock balance report");

        // Check items
        if(!isset($docData["Items"]) || !is_array($docData["Items"]) || countgf($docData["Items"]) == 0) {
            return $this->setFileError($file,"No items","StockBalance report, no items to handle.<br>File: ".$file->file_name."<br><br>File content:<br><pre>".print_r($docData,true)."</pre>");
        }
        if(!isset($docData["Items"]["Item"]) || !is_array($docData["Items"]["Item"]) || countgf($docData["Items"]["Item"]) == 0) {
            return $this->setFileError($file,"No items item","StockBalance report, no items item to handle.<br>File: ".$file->file_name."<br><br>File content:<br><pre>".print_r($docData,true)."</pre>");
        }

        if(!isset($docData["Items"]["Item"][0])) {
            $docData["Items"]["Item"] = array($docData["Items"]["Item"]);
        }

        // Handle items
        $okCount = 0;
        $errorCount = 0;
        $errorList = array();

        $this->log("Found ".countgf($docData["Items"]["Item"])." items in report");
        foreach($docData["Items"]["Item"] as $item) {
            if($this->handleStockBalanceItem($file,$docData,$item)) {
                $okCount++;
            } else {
                $errorCount++;
                $errorList[] = $this->lastItemError;
            }
        }

        $this->log("Handled report with ".$okCount." ok updates and ".$errorCount." errors:<br>".implode("<br>",$errorList));

        // Set file handled
        $file->is_handled = 1;
        $file->save();

        return true;

        //echo "<pre>".print_r($docData,true)."</pre>";


    }

    private function handleStockBalanceItem($file,$docData,$item) {

        //echo "<pre>".print_r($item,true)."</pre>";

        // Check varenr
        if(!isset($item["ItemNo"]) || trimgf($item["ItemNo"]) == "") {
            $this->log("Missing item no");
            $this->lastItemError = "No ItemNo";
            return false;
        }

        // Find varenr
        $itemno = trimgf($item["ItemNo"]);
        $pnVarenr = \PostnordVarenr::find('first',array('conditions' => array("varenr" => $itemno)));

        // Create varenr
        if($pnVarenr == null) {

            $pnVarenr = new \PostnordVarenr();
            $pnVarenr->varenr = $itemno;
            $pnVarenr->created_date =  date('d-m-Y H:i:s');
            $pnVarenr->lastsend_file_id = 0;
            $pnVarenr->lastsend_doc = "";
            $pnVarenr->lastreceive_date = date('d-m-Y H:i:s');
            $pnVarenr->lastreceive_doc = json_encode($item);
            $pnVarenr->lastreceive_file_id = $file->id;
            $pnVarenr->language_id = 1;
            $pnVarenr->state = 2;
            $pnVarenr->current_stock = 0;
            $pnVarenr->current_reserved = 0;
            $pnVarenr->sent_since_update = 0;
            $pnVarenr->save();
        }

        // Create new log entry
        $pnLog = new \PostnordVarenrLog();
        $pnLog->itemno = $itemno;
        $pnLog->postnord_varenr_id = $pnVarenr->id;
        $pnLog->ftp_download_id = $file->id;
        $pnLog->process_date =  date('d-m-Y H:i:s',$this->getFileTime($docData));
        $pnLog->type = "stockbalance";
        $pnLog->stockadjustment = 0;
        $pnLog->stockcount = intval($item["ItemQty"]);
        $pnLog->reserved = intval($item["ReservedQty"]);
        $pnLog->created_date = date('d-m-Y H:i:s');
        $pnLog->save();

        // Update varenr
        $pnVarenr->current_stock = intval($item["ItemQty"]);
        $pnVarenr->current_reserved = intval($item["ReservedQty"]);
        $pnVarenr->sent_since_update = 0;
        $pnVarenr->save();

        $this->log(" - ".$itemno." has ".intval($item["ItemQty"])." in stock and ".intval($item["ReservedQty"])." reserved");

        return true;

    }

    /**
     * MAIL HOME
     */

    private function setFileError($file,$subject,$content)
    {
        $this->log("ERROR: ".$subject);

        $this->mailProblem($subject,$content);

        // Set download status
        $file->error_message = $subject;
        $file->is_handled = 3;
        $file->save();

        return false;
    }

    private function log($message) {
        if($this->outputMessages) {
            echo $message."<br>";
        }
    }

    function mailProblem($subject,$content)
    {

        $this->log("Send mail home: ".$subject);

        $body ="";
        $modtager = "sc@interactive.dk";
        $message = "Handle postnord download problem:<br>".$content;
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";
        //$result = mail($modtager, $subject, $message, $headers);
    }

    /**
     * GET DOWNLOADED FILES
     */

    private function getDownloadedFiles($sort=true)
    {
        $files = \FtpDownload::find("all",array("conditions" => array("is_handled" => 0)));
        if($sort) $files = $this->sortDownloadedFiles($files);
        return $files;
    }

    private function sortDownloadedFiles($fileList) {

        // Unpack
        $newList = array();
        foreach($fileList as $file) {
            $newList[] = array("time" => $this->dateFromFilename($file->file_name),"file" => $file);
        }

        // Sort by time
        for($i=0;$i<count($newList);$i++) {
            for($j=$i+1;$j<count($newList);$j++) {
                if($newList[$i]["time"] > $newList[$j]["time"]) {
                    $tmp = $newList[$i];
                    $newList[$i] = $newList[$j];
                    $newList[$j] = $tmp;
                }
            }
        }

        // Back to list
        $fileList = array();
        foreach($newList as $file) {
            $fileList[] = $file["file"];
        }

        // Return
        return $fileList;

    }

    private function dateFromFilename($filename) {

        $fileParts = explode("_",$filename);
        if(count($fileParts) != 4) return 0;

        // Parse date
        $year = substr($fileParts[1],0,4);
        $month = substr($fileParts[1],4,2);
        $day = substr($fileParts[1],6,2);

        // Parse time
        $hour = substr($fileParts[2],0,2);
        $minute = substr($fileParts[2],2,2);
        $second = substr($fileParts[2],4,2);

        // Make time
        $time = mktime($hour,$minute,$second,$month,$day,$year);
        //echo $filename." - ".date("r",$time)."<br>";

        return $time;
    }


}