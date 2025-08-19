<?php

namespace GFUnit\privatedelivery\sedsv;

use PhpOffice\PhpSpreadsheet\IOFactory;

class UploadOrderStatus
{


    private $shipmentMap;

    public function processUpload()
    {

        // Get upload data
        $uploadData = $this->processUploadAndGetData();


        // Check upload data
        try {
            $this->checkFileFormat($uploadData);
        } catch (\Exception $e) {
            echo "Unable to process upload file: ".$e->getMessage();
            die();
        }


        echo "Data ok - process ".count($uploadData)." lines";

        // Create shipment map
        $shipmentList = \Shipment::find_by_sql("SELECT * FROM shipment where shipment_type = 'privatedelivery' && handler = 'mydsv'");
        $this->shipmentMap = array();
        foreach($shipmentList as $shipment) {
            $this->shipmentMap[$shipment->to_certificate_no] = $shipment;
        }

        // Process all lines after 0
        foreach($uploadData as $key => $line) {
            if($key == 0) {
                continue;
            }

            try {
                $this->processOrderLine($line);
            } catch (\Exception $e) {
                echo "<br>Unable to process line " . $key . ": " . $e->getMessage() . "<br>Exception occured at: " . $e->getFile() . "(" . $e->getLine() . ")<br>";
                exit();
            }

        }

        \System::connection()->commit();

/*
        echo "<h2>Excel data</h2>";
        echo "<pre>".print_r($uploadData, true)."</pre>";
*/

    }

    private function processOrderLine($data) {


        //$updateTime = mktime(0,0,0,12,12,2023);
        $updateTime = time();

        // Extract data
        $orderid = intvalgf($data[1]);
        $status = $data[2];
        $shippeddate = $this->toUnixtime($data[3]);
        $dsvcreateddate = $this->toUnixtime($data[14]);


        echo "<br>Handle: ".$orderid." - ".$status." - ".$shippeddate."";

        if($orderid == 0) return;

        // Load order
        $order = \Order::find_by_order_no($orderid);
        if($order == null) {
            echo " - COULD NOT FIND ORDER! - ABORTING"; return;
        }

        if(!isset($this->shipmentMap[$order->id])) {
            echo " - FOUND NO SHIPMENT ON ORDER ID: ".$order->id." CAN BE REMOVED SHORTAGE - ABORTING";
            return;
        }

        $shipment = $this->shipmentMap[$order->id];

        // Process shipment
        echo " - OK";

        // Load dsvstatus by shipment_id
        $dsvStatus = \DSVStatus::find_by_shipment_id($shipment->id);

        if($dsvStatus == null) {
            $dsvStatus = new \DSVStatus();
            $dsvStatus->shipment_id = $shipment->id;
            $dsvStatus->order_id = $order->id;
            if($dsvcreateddate > 0) {
                $dsvStatus->dsv_created = date('d-m-Y H:i:s', $dsvcreateddate);
            }
            $dsvStatus->created = date('d-m-Y H:i:s',$updateTime);
        }

        $dsvStatus->last_status = $status;

        // 10 - released
        if($dsvStatus->released == null && in_array($status,array("Released","Allocated","Picked","Shipped","Complete"))) {
            $dsvStatus->released = date('d-m-Y H:i:s',$updateTime);
        }

        // 15 - allocated
        if($dsvStatus->allocated == null && in_array($status,array("Allocated","Picked","Shipped","Complete"))) {
            $dsvStatus->allocated = date('d-m-Y H:i:s',$updateTime);
        }

        // 25 - picked
        if($dsvStatus->picked == null && in_array($status,array("Picked","Shipped","Complete"))) {
            $dsvStatus->picked = date('d-m-Y H:i:s',$updateTime);
        }

        // 40 - complete
        if($dsvStatus->completed == null && in_array($status,array("Shipped","Complete"))) {
            $dsvStatus->completed = date('d-m-Y H:i:s',$updateTime);
        }

        // 60 - shipped
        if($dsvStatus->shipped == null && in_array($status,array("Shipped"))) {
            $dsvStatus->shipped = date('d-m-Y H:i:s',$updateTime);
        }

        // Set / clear hold
        if($status == "Hold" && $dsvStatus->hold == null) {
            $dsvStatus->hold = date('d-m-Y H:i:s',$updateTime);
        } else if($status != "Hold" && $dsvStatus != null) {
            $dsvStatus->hold = null;
        }

        if($shippeddate > 0 && $dsvStatus->shipped_date == null) {
            $dsvStatus->shipped_date = date('d-m-Y H:i:s', $shippeddate);
        }

        // If dsv status exists
        if($dsvStatus->id > 0) {

            // Find last status log for dsvstatus
            $statusLog = \DSVStatusLog::find_by_sql("SELECT * FROM dsvstatus_log WHERE dsvstatus_id = ".$dsvStatus->id." ORDER BY id DESC LIMIT 1");

            // If line_data is same as current, return
            if($statusLog != null && $statusLog[0]->line_data == json_encode($data)) {
                echo " - NO CHANGE";
                return;
            }

        }

        $dsvStatus->save();
        echo " - SAVE";

        // Update status log
        $statusLog = new \DSVStatusLog();
        $statusLog->dsvstatus_id = $dsvStatus->id;
        $statusLog->shipment_id = $shipment->id;
        $statusLog->order_id = $orderid;
        $statusLog->status = $status;
        $statusLog->created = date('d-m-Y H:i:s',$updateTime);
        $statusLog->line_data = json_encode($data);
        $statusLog->save();


        
    }

    private function toUnixtime($dateString) {
        if(trimgf($dateString) == "") return 0;

        $dateParts = explode("-", $dateString);
        if(count($dateParts) != 3) {
            throw new \Exception("Unable to convert date to unixtime: ".$dateString);
        }

        $unixtime = mktime(0, 0, 0, $dateParts[1], $dateParts[0], $dateParts[2]);
        return $unixtime;

    }

    private function checkFileFormat($data)
    {

        if(!is_array($data)) {
            throw new \Exception("Data is not an array");
        }

        if(count($data) < 2) {
            throw new \Exception("Data array is empty");
        }

        if(!is_array($data[0])) {
            throw new \Exception("Header is not an array");
        }

        if(count($data[0]) != 16) {
            throw new \Exception("Header does not contain 16 columns (".count($data[0]).")");
        }

        // Check headers

        $expectedHeaders = array(
          
            0 => "Date",
            1 => "Order ID",
            2 => "Status",
            3 => "Shipped Date",
            4 => "Finish Date",
            5 => "Customer ID",
            6 => "Consignee",
            7 => "Consignment",
            8 => "Place",
            9 => "Country",
            10 => "Header host order reference",
            11 => "Header purchase order",
            12 => "Owner ID",
            13 => "Shipment Group",
            14 => "Creation Date",
            15 => "Creation Time"

        );

        // Check header is expected names or warn on unexpected names
        foreach($data[0] as $key => $header) {
            if(trim($header) != trim($expectedHeaders[$key])) {
                
                throw new \Exception("Unexpected header name: ".$header." (expected: ".$expectedHeaders[$key].")");
            }
        }


        // Process all lines after index 0, make sure there is 13 items, index 1 and 2 cant be empty string
        foreach($data as $key => $line) {
            if($key == 0) {
                continue;
            }
            if(count($line) != 16) {
                throw new \Exception("Line ".$key." does not contain 17 columns");
            }
            if($line[1] == "") {
                throw new \Exception("Line ".$key." Order ID is empty");
            }
           /*
            if(intvalgf($line[1]) == 0) {
                throw new \Exception("Line ".$key." Order ID is not a number");
            }
            */
            if($line[2] == "") {
                throw new \Exception("Line ".$key." Status is empty");
            }

            if(!in_array($line[2], array("Allocated","Released","Picked","Shipped","Complete","Hold"))) {
                throw new \Exception("Line ".$key." Status is not valid [".$line[3]."]");
            }

        }

    }

    private function processUploadAndGetData()
    {

        // Check file upload
        if($_FILES['fileToUpload']["error"] != 0) {
            echo "Error uploading file";
            die();
        }

        // Check file is .xlsx
        $fileParts = pathinfo($_FILES['fileToUpload']["name"]);
        if($fileParts['extension'] != "xlsx") {
            echo "File must be .xlsx";
            die();
        }

        // Check file is not empty
        if($_FILES['fileToUpload']["size"] == 0) {
            echo "File is empty";
            die();
        }

        // Check file is uploaded
        if(!is_uploaded_file($_FILES['fileToUpload']["tmp_name"])) {
            echo "File not uploaded";
            die();
        }


        try {
            $uploadData = $this->loadDataFromExcel($_FILES['fileToUpload']["tmp_name"]);
            unlink($_FILES['fileToUpload']["tmp_name"]);
            return $uploadData;

        } catch (\Exception $e) {

            echo "Unable to load upload file: ".$e->getMessage();
            if(file_exists($_FILES['fileToUpload']["tmp_name"])) {
                unlink($_FILES['fileToUpload']["tmp_name"]);
            }
            die();
        }

    }

    private function loadDataFromExcel($file) {

        $spreadsheet = IOFactory::load($file);

        // Hent det første ark
        $sheet = $spreadsheet->getSheet(0);

        // Læs alle data fra arket
        $data = $sheet->toArray();

        return $data;

    }

}
