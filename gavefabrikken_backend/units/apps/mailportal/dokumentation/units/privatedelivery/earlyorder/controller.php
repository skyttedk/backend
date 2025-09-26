<?php

namespace GFUnit\privatedelivery\earlyorder;

use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CountryHelper;

class Controller extends UnitController
{

    private $languageCode = 5;

    public function __construct()
    {
        parent::__construct(__FILE__);

    }


    public function dash() {

        echo "try me!";

    }

    public function exportmark() {


        $shipmentlist = $this->loadEarlyOrderShipments();

        $processed = 0;

        foreach($shipmentlist as $shipment) {

            $shipmentobj = \Shipment::find($shipment->id);
            if($shipmentobj->shipment_state == 1) {

                $processed++;
                echo $shipment->id."<br>";

                $shipmentobj->shipment_state = 2;
                $shipmentobj->shipment_sync_date = date('d-m-Y H:i:s');
                $shipmentobj->save();

            } else {
                echo "FAILED ON ".$shipment->id."<br>";
            }

        }
        echo "PROCESSED: ".$processed;
        \System::connection()->commit();
    }


    public function exportlabels() {


        // Get shipments
        $shipmentList = $this->loadEarlyOrderShipments();

        // Start output
        $this->initExcel();
        
        // Output shipments
        foreach($shipmentList as $shipment) {
            $this->outputShipmentRow($shipment);
        }
        
        // Download file
        $this->downloadFile();
        
    }

    public function exportsumlist() {

        // Get shipments
        $shipmentList = $this->loadEarlyOrderShipments();

        // Start output
        $this->initExcel();

        $varenrMap = array();
        $vareNameMap = array();

        // Output shipments
        foreach($shipmentList as $shipment) {
            $varenrList = $shipment->getVarenrCountList(false,false,true);
            foreach($varenrList as $varelinje) {
                if(!isset($varenrMap[$varelinje["itemno"]])) {
                    $varenrMap[$varelinje["itemno"]] = 0;
                    $vareNameMap[$varelinje["itemno"]] = $varelinje["name"];
                }
                $varenrMap[$varelinje["itemno"]] += $varelinje["quantity"];
            }
        }

        $vareNrList = array_keys($varenrMap);
        sort($vareNrList);

        $this->sheet->getColumnDimension('A')->setWidth(28);
        $this->sheet->getColumnDimension('B')->setWidth(50);
        $this->sheet->getColumnDimension('C')->setWidth(18);

        $row = 1;
        $this->sheet->setCellValueByColumnAndRow(1, $row, "Varenr");
        $this->sheet->setCellValueByColumnAndRow(2, $row, "Varer");
        $this->sheet->setCellValueByColumnAndRow(3, $row, "Antal");
        $row++;

        foreach($vareNrList as $varenr) {

            $antal  =$varenrMap[$varenr];

            $this->sheet->setCellValueByColumnAndRow(1, $row, $varenr);
            $this->sheet->setCellValueByColumnAndRow(2, $row, $vareNameMap[$varenr]);
            $this->sheet->setCellValueByColumnAndRow(3, $row, $antal);
            $row++;
        }


        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        //header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="earlyorder-sum-'.date("d-m-Y").'.xlsx"');
        header('Cache-Control: max-age=0');
        $this->phpExcel->setActiveSheetIndex(0);

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->phpExcel);
        //$objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($this->phpExcel);
        //$objWriter->setDelimiter(";");
        //$objWriter->setEnclosure("");
        //echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
    }

    private function loadEarlyOrderShipments() {

        // Define constraints
        $order_states = array(1,2,3,7,8);

        // Get shipments
        $sql = "SELECT * FROM `shipment` where shipment_type = 'earlyorder' && shipment_state = 1 && companyorder_id in (select id from company_order where order_state not in (".implode(",",$order_states).") && shop_id in (select shop_id from cardshop_settings where language_code = ".$this->languageCode.")) ORDER BY `shipment`.`shipto_name` ASC";
        $sql = "select * from shipment where id in (SELECT shipment_id  FROM `blockmessage` WHERE `release_status` = 0 AND `silent` = 0 AND `description` LIKE 'Nav error: Exception calling Upload: <orderno> has been completed') && companyorder_id in (select id from company_order where shop_id in (select shop_id from cardshop_settings where language_code = 1));";
        //$sql = "SELECT * FROM shipment where id in (123666,117147,120752)";
        //$sql = "SELECT * FROM shipment where shipment_type = 'earlyorder' && id in (SELECT shipment_id  FROM `blockmessage` WHERE `description` LIKE 'Nav error: Exception calling Upload: <orderno> has been completed' && shipment_id > 0 && release_status = 0)";
        //$sql = "select shipment.* from shipment, company_order where companyorder_id = company_order.id && shop_id in (select shop_id from cardshop_settings where language_code = 1) && order_state in (10) && shipment_type = 'earlyorder' && shipment_state = 2 && shipment.shipment_sync_date > company_order.nav_lastsync ORDER BY `shipment`.`id` ASC";
        //$sql = "select shipment.* from shipment, company_order where companyorder_id = company_order.id && shop_id in (select shop_id from cardshop_settings where language_code = 1) && order_state in (10) && shipment_type = 'earlyorder' && shipment_state = 1 ORDER BY `shipment`.`id` ASC";

        $shipmentList = \Shipment::find_by_sql($sql);

        return $shipmentList;

    }

    /**
     * SUM LISTE
     */

    /**
     * LABEL LIST
     */
    
    private $phpExcel;
    private $sheet;
    private $row;
    private $date1;
    private $date2;
    
    private function initExcel() {

        // Init phpexcel
        $this->phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $this->phpExcel->removeSheetByIndex(0);

        // Write header
        $this->sheet = $this->phpExcel->createSheet();
        $this->phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $this->sheet->setTitle("Privatlevering");
        $this->sheet->getColumnDimension('A')->setWidth(28);
        $this->sheet->getColumnDimension('B')->setWidth(34);
        $this->sheet->getColumnDimension('C')->setWidth(18);
        $this->sheet->getColumnDimension('D')->setWidth(71);
        $this->sheet->getColumnDimension('E')->setWidth(9);
        $this->sheet->getColumnDimension('F')->setWidth(21);
        $this->sheet->getColumnDimension('G')->setWidth(10);
        $this->sheet->getColumnDimension('H')->setWidth(38);
        $this->sheet->getColumnDimension('I')->setWidth(22);

        $this->row=1;

        $this->date1 = date("Y-m-d",(time()))."-08:30:00";
        $this->date2 = date("Y-m-d",(time()))."-16:00:00";

    }
    
    private function outputShipmentRow(\Shipment $shipment) {

        // Get user data
        $userData = array(
            "name" => $shipment->shipto_name,
            "address" => $shipment->shipto_address,
            "address2" => $shipment->shipto_address2,
            "postnr" => $shipment->shipto_postcode,
            "bynavn" => $shipment->shipto_city,
            "land" => CountryHelper::codeToCountry(CountryHelper::countryToCode($shipment->shipto_country)),
            "telefon" => $shipment->shipto_phone,
            "email" => $shipment->shipto_email
        );

        $phone = trimgf(str_replace(array(" ","-"),"",$userData["telefon"]));
        if($phone != "") {

            if($userData["land"] == "Sverige") {
                if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    $phone = "+46".$phone;
                }
            }
            if($userData["land"] == "Danmark") {
                if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "45" || substr($phone,0,3) == "+45")) {
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    $phone = "+45".$phone;
                }
            }

        }

        $varenrList = $shipment->getVarenrCountList(false,false,true);

        foreach($varenrList as $varer) {


            // Write data row
            $this->sheet->setCellValueByColumnAndRow(1, $this->row, ($userData["name"]));
            $this->sheet->setCellValueByColumnAndRow(2, $this->row, $userData["email"]);
            $this->sheet->setCellValueByColumnAndRow(3, $this->row, $phone);
            $this->sheet->setCellValueByColumnAndRow(4, $this->row, $userData["address"].((trimgf($userData["address2"]) != "" && mb_strtolower(trimgf($userData["address"])) != mb_strtolower(trimgf($userData["address2"])))? ", ".$userData["address2"] : ""));
            $this->sheet->setCellValueByColumnAndRow(5, $this->row, str_replace(" ","",$userData["postnr"]));
            $this->sheet->setCellValueByColumnAndRow(6, $this->row, $userData["bynavn"]);
            $this->sheet->setCellValueByColumnAndRow(7, $this->row, $userData["land"]);
            $this->sheet->setCellValueByColumnAndRow(8, $this->row, $varer["quantity"].": ".$varer["itemno"]." - ".$varer["name"]);
            $this->sheet->setCellValueByColumnAndRow(10,$this->row,$this->date1);
            $this->sheet->setCellValueByColumnAndRow(11,$this->row,$this->date2);
            $this->sheet->setCellValueByColumnAndRow(12,$this->row,$shipment->id);
            $this->row++;

        }

    }
    
    private function downloadFile() {

        // Output excel file
        //header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="earlyorder-'.date("d-m-Y").'.csv"');
        header('Cache-Control: max-age=0');
        $this->phpExcel->setActiveSheetIndex(0);

        //$objWriter = new PHPExcel_Writer_Excel2007($this->phpExcel);
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($this->phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
    }

}