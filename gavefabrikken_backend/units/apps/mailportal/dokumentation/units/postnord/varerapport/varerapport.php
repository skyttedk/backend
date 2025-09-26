<?php

namespace GFUnit\postnord\varerapport;

class Varerapport
{

    private $waitingItems;

    public function __construct()
    {

    }
    
    public function downloadReport()
    {

        // Process downloaded files from postnord first
        $downloadHandler = new \GFUnit\postnord\sync\HandleDownloads(false);
        $downloadHandler->runHandler();

        $showType = isset($_POST["showitem"]) ? intval($_POST["showitem"]) : 0;

        // Load shipments
        $this->loadShipmentData();
        
        // Load postnord varer
        $pnVareList = \PostnordVarenr::find_by_sql('select * from postnord_varenr order by varenr');



        // Init phpexcel
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $phpExcel->removeSheetByIndex(0);

        // Write header styles
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->setTitle("Postnord lager rapport ".date("dmY"));
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(34);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);

        // Write headers
        $row = 1;
        $sheet->setCellValueByColumnAndRow(1, $row, "Varenr");
        $sheet->setCellValueByColumnAndRow(2, $row, "SAM");
        $sheet->setCellValueByColumnAndRow(3, $row, "Beskrivelse");
        $sheet->setCellValueByColumnAndRow(4, $row, "Venter i cardshop");
        $sheet->setCellValueByColumnAndRow(5, $row, "Postnord lager");
        $sheet->setCellValueByColumnAndRow(6, $row, "Postnord reserveret");
        $sheet->setCellValueByColumnAndRow(7, $row, "Postnord ledige");
        $sheet->setCellValueByColumnAndRow(8, $row, "Postnord mangler");
        $row++;

        $pnVareAlias = array();
        foreach($pnVareList as $pnVare) {
            if(trimgf($pnVare->navalias) != "") {
                $pnVareAlias[$pnVare->varenr] = $pnVare;
            }
        }

        foreach($pnVareList as $pnVare) {

            if(trimgf($pnVare->navalias) == "") {

                $showLine = true;

                // Check alias
                $useAlias = trimgf($pnVare->postnordalias) != "";
                $aliasVare = isset($pnVareAlias[$pnVare->postnordalias]) ? $pnVareAlias[$pnVare->postnordalias] : null;
                if($aliasVare == null) $useAlias = false;

                $waitingCount = isset($this->waitingItems[ trimgf(strtolower($pnVare->varenr))]) ? $this->waitingItems[ trimgf(strtolower($pnVare->varenr))] : '?';
                if($useAlias) $waitingCount += isset($this->waitingItems[ trimgf(strtolower($aliasVare->varenr))]) ? $this->waitingItems[ trimgf(strtolower($aliasVare->varenr))] : 0;

                $pnFree = ($pnVare->current_stock-($pnVare->current_reserved + $pnVare->sent_since_update));
                if($useAlias) $pnFree = ($aliasVare->current_stock-($aliasVare->current_reserved + $aliasVare->sent_since_update));

                $navItemList = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$pnVare->varenr."' && deleted is null");
                $navItem = (count($navItemList) == 0 ? null : $navItemList[0]);

                $data = array(
                    $pnVare->varenr,
                    isset($this->samNr[$pnVare->varenr]) ? implode(", ",$this->samNr[$pnVare->varenr]) : "",
                    $navItem == null ? "KAN IKKE FINDES I NAV!" : $navItem->description,
                    $waitingCount,
                    $pnVare->current_stock,
                    $pnVare->current_reserved + $pnVare->sent_since_update,
                    $pnFree,
                    $pnFree > $waitingCount ? 0 : ($waitingCount-$pnFree),
                    ($useAlias ? "Bruger alias ved postnord: ".$aliasVare->varenr : "")
                );



                if($showType == 0 && $data[7] <= 0) $showLine = false;
                if($showType == 2 && $data[3] == 0 && $data[4] == 0 && $data[5] == 0 && $data[6] == 0 && $data[7] == 0 ) $showLine = false;

                if($showLine) {
                    foreach($data as $ci => $col) {
                        $sheet->setCellValueByColumnAndRow($ci+1, $row, $col);
                    }
                    $row++;
                }
            }

        }

        // Output excel file
        //header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="postnord_lager_rapport-'.date("dmY").'.xlsx"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($phpExcel);
        $objWriter->save('php://output');
        exit();

    }

    private $samNr = array();

    private function loadShipmentData()
    {

        // Create syncrunner that holds sync details
        $syncModel = new \GFUnit\postnord\sync\SyncRunner(false,false);

        // Get shipments
        $shipmentList = $syncModel->getWaitingShipments();

        $this->log("Loaded ".countgf($shipmentList)." shipments");
        $this->waitingItems = array();

        foreach($shipmentList as $shipment) {

            // Get varenr
            try {
                $itemList = $shipment->getVarenrCountList(true,true,true);
            } catch (\Exception $e) { echo "FEJL I VARENR COUNT: ".$e->getMessage()." - kontakt teknisk support!"; exit(); $itemList = array(); }

            
            foreach($itemList as $item) {

                if(!isset($this->samNr[$item["itemno"]])) $this->samNr[$item["itemno"]] = array();
                if(!in_array($shipment->itemno,$this->samNr[$item["itemno"]]) && $shipment->itemno != $item["itemno"]) {
                    $this->samNr[$item["itemno"]][] = $shipment->itemno;
                }

                if(!isset($this->waitingItems[trimgf(strtolower($item["itemno"]))])) {
                    $this->waitingItems[trimgf(strtolower($item["itemno"]))] = 0;
                }

                $this->waitingItems[trimgf(strtolower($item["itemno"]))] += $item["quantity"];

            }


        }



    }

    private function log($message) {

        //echo $message."<br>";

    }

}
