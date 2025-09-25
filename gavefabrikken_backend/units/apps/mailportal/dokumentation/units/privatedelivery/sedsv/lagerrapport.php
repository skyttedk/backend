<?php

namespace GFUnit\privatedelivery\sedsv;


class LagerRapport
{

    private $varenrLines;
    private $helper;
    private $lockshopid;

    public function dispatch($lockshopid) {

        $this->lockshopid = intval($lockshopid);
        $this->loadRapportData();
        $this->outputRapport();

    }

    /**
     * OUTPUT RAPPORT
     */

    private $lastname = "";
    
    private function outputRapport()
    {

        // Init phpexcel
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $phpExcel->removeSheetByIndex(0);

        $shopList = \CardshopSettings::find_by_sql("select * from cardshop_settings where language_code = 5");
        
        $shopidlist = array();
        foreach($shopList as $shop) {
            if($this->lockshopid == 0 || $this->lockshopid == $shop->shop_id) {
                $shopidlist[] = $shop->shop_id;
            }
        }
        
        foreach($shopList as $shop) {
            if($this->lockshopid == 0 || $this->lockshopid == $shop->shop_id) {
                $sheet = $phpExcel->createSheet();
                $sheet->setTitle($shop->concept_code);
                $this->lastname = $shop->concept_code;
                $this->writeSheet($sheet, $shop->shop_id, $shopidlist);
            }
        }

        // Write header
        if($this->lockshopid == 0) {
            $sheet = $phpExcel->createSheet();
            $sheet->setTitle("Alle varenr",);
            $this->lastname = "alle";
            $this->writeSheet($sheet,0,$shopidlist);
        }

        // Download
        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="dsv-lagerrapport-'.$this->lastname.'-'.date("Y-m-d-H-i").'.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($phpExcel);
        $objWriter->save('php://output');
        exit();
    }


    


    private function writeSheet($sheet, $shop_id,$shopidlist) {

        $headlines = array(
            array("Varenr",12),
            array("Sampak",22),
            array("Beskrivelse",32),
            array("Afventer ved GF",12),
            array("DSV Lagerstatus",12),
            array("+/- ved DSV",12),
            //array("Siden sidste træk",12),
            array("Forslag",12),
            array("Valgt sidste 7 dage",12),
            array("Afventer betaling",12),
            //array("Valgt sidste 14 dage",12),
            array("Allerede afsendt",12),
            array("Shops",18),
        );

        $letters = "ABCDEFGHIJKLMNOPQRSTUVXYZ";

        foreach($headlines as $index => $headline) {
            $sheet->getColumnDimension(substr($letters,$index,1))->setWidth($headline[1]);
            $sheet->setCellValueByColumnAndRow($index+1,1,$headline[0]);
        }

        $row = 2;

        foreach($this->varenrLines as $varenr) {

            $inShops = array();
            foreach($shopidlist as $shid) {
                if($this->helper->inShopID($varenr["varenr"],$varenr["sampaklist"],$shid)) {
                    $inShops[] = $shid;
                }
            }

            if($shop_id == 0 || in_array($shop_id,$inShops)) {

                /*
                if($shop_id == 0) $forecastValue = $this->helper->getForecastAll($varenr["varenr"],false);
                else $forecastValue = $this->helper->getForecast($shop_id,$varenr["varenr"],false);
                */

                $waitingPayment = $varenr["pipeline"];
                $forecastValue = $this->beregnAnbefaletOverfoersel($varenr["dsvlager"], $varenr["waiting"], $varenr["last7"], $waitingPayment, 7);

                $sheet->setCellValueByColumnAndRow(1,$row,$varenr["varenr"]);
                $sheet->setCellValueByColumnAndRow(2,$row,$varenr["sampak"]);
                $sheet->setCellValueByColumnAndRow(3,$row,$varenr["description"]);
                $sheet->setCellValueByColumnAndRow(4,$row,$varenr["waiting"]);
                $sheet->setCellValueByColumnAndRow(5,$row,$varenr["dsvlager"]);
                $sheet->setCellValueByColumnAndRow(6,$row,$varenr["dsvstatus"]);
                //$sheet->setCellValueByColumnAndRow(7,$row,$varenr["sincelast"]);
                $sheet->setCellValueByColumnAndRow(7,$row,intval(ceil($forecastValue)));
                $sheet->setCellValueByColumnAndRow(8,$row,$varenr["last7"]);
                $sheet->setCellValueByColumnAndRow(9,$row,$waitingPayment);
                $sheet->setCellValueByColumnAndRow(10,$row,$varenr["processed"]);
                $sheet->setCellValueByColumnAndRow(11,$row,$this->helper->shopIDListToNameString($inShops));

                if($shop_id > 0 && count($inShops) > 1) {
                    $sheet->getStyle('A'.$row.':D'.$row.'')->getFont()->setBold(true);
                }
                
                $row++;

            }
        }

    }


    function beregnAnbefaletOverfoersel($lagerNu, $ordrerAfventer, $solgteVarer, $ordrerAfventerBetaling, $dageSidenSidsteRapport) {

        $konverteringsrate = 0.10; // Antagelse: 5% af afventende betalinger vil blive til ordre
        $sikkerhedsbufferProcent = 0.5; // 20% sikkerhedsbuffer af det forventede salg
        $maksimalLagerGraense = 500; // Maksimalt antal varer tilladt på lager hos fragtvirksomheden
        $dageTilNaesteRapport = 7; // Antagelse: Næste rapport trækkes om 3 dage

        // Beregn den nuværende mangel
        $mangel = $ordrerAfventer - $lagerNu;

        // Estimer dagligt salg baseret på historiske data
        $dagligtSalg = $solgteVarer / $dageSidenSidsteRapport;

        // Estimer forventet salg indtil næste rapport
        $forventetSalg = $dagligtSalg * $dageTilNaesteRapport * $sikkerhedsbufferProcent;

        // Estimer forventede nye ordre fra afventende betalinger
        $forventedeNyeOrdre = $ordrerAfventerBetaling * $konverteringsrate;


        // Beregn anbefalet overførsel
        $anbefaletOverfoersel = max(0, $mangel + $forventetSalg + $forventedeNyeOrdre);

        // Overvej maksimal grænse for lageret
        $anbefaletOverfoersel = min($anbefaletOverfoersel, $maksimalLagerGraense - $lagerNu);

        return $anbefaletOverfoersel;
    }

    
    
    /***
     * RAPPORT DATA
     * @return void
     */

    private function loadRapportData() {

        $this->helper = new Helpers($this->lockshopid);
        $this->varenrLines = array();

        $vareNrList = $this->helper->getVarenrList();

        foreach($vareNrList as $varenr) {
            if($this->helper->isSampakVarenr($varenr)) {
                $this->processVarenrSampak($varenr);
            }
        }

        foreach($vareNrList as $varenr) {
            if(!$this->helper->isSampakVarenr($varenr)) {
                $this->processVarenrSingle($varenr,null);
            }
        }

        //   echo "<pre>".print_r($this->varenrLines,true)."</pre>";

        $this->mergeVarenrLines();

    }

    private function mergeVarenrLines() {

        return;
        $newVarenrLines = array();
        $usedItems = array();

        foreach($this->varenrLines as $varenrLine) {

            if(isset($usedItems[$varenrLine["varenr"]])) {

                $newData = $newVarenrLines[$usedItems[$varenrLine["varenr"]]];
                $newData["sampak"] .= ", ".$varenrLine["sampak"];
                $newData["sampaklist"] = array_merge($newData["sampaklist"],$varenrLine["sampaklist"]);
                $newData["waiting"] += $varenrLine["waiting"];
                $newData["sincelast"] += $varenrLine["sincelast"];
                //$newData["dsvstatus"] = $varenrLine["dsvlager"] - $newData["waiting"];
                $newData["dsvstatus"] += $varenrLine["dsvstatus"];
                $newData["last7"] += $varenrLine["last7"];
                $newData["last14"] += $varenrLine["last14"];
                $newData["processed"] += $varenrLine["processed"];
                $newData["pipeline"] += $varenrLine["pipeline"];
                $newData["hidden"] = ($newData["hidden"] && $varenrLine["hidden"]);
                $newVarenrLines[$usedItems[$varenrLine["varenr"]]] = $newData;

            } else {

                $usedItems[$varenrLine["varenr"]] = count($newVarenrLines);
                $newVarenrLines[] = $varenrLine;

            }

        }

        foreach($newVarenrLines as $index => $varenrLine) {
            if($varenrLine["sampak"] == "SINGLE") {
                $newVarenrLines[$index]["sampak"] = "";
            }
        }

        usort($newVarenrLines, function ($a, $b) {
            return strcmp($a['varenr'] , $b['varenr']);
        });

        $this->varenrLines = $newVarenrLines;

    }


    private function processVarenrSingle($varenr, $sampakNr=null, $sampakHidden=false, $sampak7=0, $sampak14=0)
    {


        $isSingleLine = ($sampakNr == null);

        // Get vare
        try {
        $vare = $this->helper->getNavVare($varenr);
        }
        catch (\Exception $e) {
            return;
        }
        
        $prShipment = 1;
        if(!$isSingleLine) {

            $prShipment = $this->helper->getBomItemQuantity($sampakNr,$varenr);

            // Already sent
            $alreadySent = $this->helper->getShipmentsHandledCount($sampakNr);

            // Waiting
            $waitingCount = $this->helper->getShipmentsWaitingCount($sampakNr);

            $last7Days = $sampak7*$prShipment;
            $last14Days = $sampak14*$prShipment;

            $sinceLast = $prShipment*$this->helper->getSentSinceLastLagerRapport($sampakNr) ;

            $dsvWaiting = $this->helper->getShipmentsWaitingAtDSVCount($sampakNr) * $prShipment;

            $pipelineValue = $this->helper->getOrderPipelineValue($sampakNr) * $prShipment;


        }
        else {

            // Already sent
            $alreadySent = $this->helper->getShipmentsHandledCount($varenr);

            // Waiting
            $waitingCount = $this->helper->getShipmentsWaitingCount($varenr);

            $last7Days = $this->helper->getLast7DaysSelected($varenr);
            $last14Days = $this->helper->getLast14DaysSelected($varenr);

            $sinceLast = $this->helper->getSentSinceLastLagerRapport($varenr);

            $dsvWaiting = $this->helper->getShipmentsWaitingAtDSVCount($varenr);

            $pipelineValue = $this->helper->getOrderPipelineValue($varenr);

        }

        $dsvLager = $this->helper->getDSVLagerQuantity($varenr)-$dsvWaiting;
        $sinceLast = $this->helper->getSentSinceLastLagerRapport($varenr);
        //$dsvLager -= $sinceLast;

        $canSend = $waitingCount;
        if($dsvLager < $canSend) $canSend = $dsvLager;


        $hidden = false;
        if($isSingleLine) {

            if($dsvLager == 0 && $waitingCount == 0 && $alreadySent == 0) {
                $hidden = true;
            }

        } else {
            $hidden = $sampakHidden;
        }

        $vareNrData = array(
            "varenr" => $varenr,
            "sampak" => $isSingleLine ? "SINGLE" : $sampakNr."(".$prShipment."stk)",
            "sampaklist" => $isSingleLine ? array() : array($sampakNr),
            "description" => $vare->description,
            "prshipment" => $prShipment,
            "waiting" => $prShipment*$waitingCount,
            "dsvlager" => $dsvLager,
            "dsvstatus" => ($dsvLager-($prShipment*$waitingCount)-$sinceLast),
            "last7" => $last7Days,
            "last14" => $last14Days,
            "processed" => $alreadySent,
            "hidden" => $hidden,
            "sincelast" => $sinceLast,
            "pipeline" => $pipelineValue
        );

        $this->varenrLines[] = $vareNrData;

    }




    private function processVarenrSampak($sampakVarenr) {

        // Get vare
        $vare = $this->helper->getNavVare($sampakVarenr);

        // Already sent
        $alreadySent = $this->helper->getShipmentsHandledCount($sampakVarenr);

        // Waiting
        $waitingCount = $this->helper->getShipmentsWaitingCount($sampakVarenr);

        // Count max on lager
        $maxNo = null;
        $subVarenr = $this->helper->getItemsInSampak($sampakVarenr);
        foreach($subVarenr as $subNo) {

            $subQuantity = $this->helper->getBomItemQuantity($sampakVarenr,$subNo);
            $subLager = $this->helper->getDSVLagerQuantity($subNo);
            $canMake = $subQuantity == 0 ? 0 : floor($subLager/$subQuantity);
            if($maxNo === null || $maxNo > $canMake) {
                $maxNo = $canMake;
            }

        }

        $maxNo -= $this->helper->getShipmentsWaitingAtDSVCount($sampakVarenr);

        $canSend = $waitingCount;
        if($canSend > $maxNo) $canSend = $maxNo;

        $hidden = false;
        if($maxNo == 0 && $waitingCount == 0 && $alreadySent == 0) {
            $hidden = true;
        }

        $last7Days = $this->helper->getLast7DaysSelected($sampakVarenr);
        $last14Days = $this->helper->getLast14DaysSelected($sampakVarenr);

        // Output child lines
        foreach ($subVarenr as $varenr) {
            $this->processVarenrSingle($varenr,$sampakVarenr,$hidden,$last7Days,$last14Days);
        }

    }

}