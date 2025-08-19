<?php

namespace GFUnit\vlager\panel;
use GFBiz\units\UnitController;
use GFUnit\vlager\utils;
use GFUnit\vlager\utils\Template;
use GFUnit\vlager\utils\VLager;
use GFUnit\vlager\utils\VLagerCounter;

class Liste
{

    private $vlager;
    private $shopid;

    public function __construct($vlager,$shopid=0)
    {

        $this->vlager = $vlager;
        $this->shopid = $shopid;

    }

    public function downloadList() {
    // Create new spreadsheet
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Setup counter
    $vlCounter = new VLagerCounter($this->vlager,$this->shopid);

    // Headers
    $headers = [
        'A1' => 'Varenr',
        'B1' => 'Beskrivelse',
        'C1' => 'SAM',
        'D1' => 'På vej',
        'E1' => 'På lager',
        'F1' => 'Tilgængelig',
        'G1' => 'Venter ved GF',
        'H1' => 'Kan sendes',
        'I1' => 'Mangler på lager',
        'J1' => 'Pipeline GF',
        'K1' => 'Sidste 7 dage',
        'L1' => 'Prognose',
        'M1' => 'Forslag',
        'N1' => 'På vej ud',
        'O1' => 'Sendt'
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

    // Add data
    $primaryItems = $vlCounter->getPrimaryItems();

    // First BOM items
    foreach ($primaryItems as $itemno => $name) {
        if($vlCounter->isBOMItem($itemno)) {
            $row = $this->addItemToExcel($sheet, $vlCounter, $itemno, $name, $row);
        }
    }

    // Then non-BOM items
    foreach ($primaryItems as $itemno => $name) {
        if(!$vlCounter->isBOMItem($itemno)) {
            $row = $this->addItemToExcel($sheet, $vlCounter, $itemno, $name, $row);
        }
    }

    // Auto-size columns
    foreach(range('A','O') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Output file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    $vlagerObj = \VLager::find($this->vlager);

    $shopName = "";
    if($this->shopid > 0) {
        $settings = \CardshopSettings::find_by_sql("select * from cardshop_settings where shop_id = ".$this->shopid);
        $shopName = $settings[0]->concept_code;
    }

    // Headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="vlager-rapport-'.$vlagerObj->code.'-'.($shopName != "" ? $shopName.'-' : '').date("dmY").'.xlsx"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

private function addItemToExcel($sheet, $vlCounter, $itemno, $name, $row) {
    $isSampak = $vlCounter->isBOMItem($itemno);
    $availableAndIncomging = $vlCounter->getAvailableAndIncoming($itemno);
    $warehouseMissing = $vlCounter->getWarehouseMissing($itemno);
    $suggestion = $vlCounter->getNextShippingSuggestion($itemno);
    $canSend = $vlCounter->getWarehouseCanSend($itemno);

    // Parent row style
    if($isSampak) {
        $sheet->getStyle("A$row:O$row")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle("A$row:O$row")->getBorders()
            ->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheet->getStyle("A$row:O$row")->getBorders()
            ->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
    }

    // I addItemToExcel funktionen, lige efter parent row styling:
$sheet->getStyle("I$row")->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('FFFFE0');
$sheet->getStyle("M$row")->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('ADD8E6');

    // Add parent row data
    $sheet->setCellValue("A$row", $vlCounter->getRealItemNo($itemno));
    $sheet->setCellValue("B$row", $name);
    $sheet->setCellValue("C$row", $isSampak ? "JA" : "NEJ");
    $sheet->setCellValue("D$row", ($vlCounter->getIncoming($itemno) > 0 || !$isSampak ? $vlCounter->getIncoming($itemno) : "-"));
    $sheet->setCellValue("E$row", ($vlCounter->getAvailable($itemno) > 0 || !$isSampak ? $vlCounter->getAvailable($itemno) : "-"));
    $sheet->setCellValue("F$row", ($availableAndIncomging > 0 || !$isSampak ? $availableAndIncomging : "-"));
    $sheet->setCellValue("G$row", $vlCounter->getWaitingGF($itemno));
    $sheet->setCellValue("H$row", $canSend);
    $sheet->setCellValue("I$row", ($warehouseMissing > 0 ? $warehouseMissing : "-"));
    $sheet->setCellValue("J$row", $vlCounter->getPipeline($itemno));
    $sheet->setCellValue("K$row", $vlCounter->getSelectedLast7Days($itemno));
    $sheet->setCellValue("L$row", $vlCounter->getPrognose($itemno));
    $sheet->setCellValue("M$row", $suggestion);
    $sheet->setCellValue("N$row", $vlCounter->getOutgoing($itemno));
    $sheet->setCellValue("O$row", $vlCounter->getSent($itemno));

    $row++;

    // Add child rows if BOM item
    if($isSampak) {
        $childItems = $vlCounter->getChildItems($itemno);
        foreach ($childItems as $childItemno => $childData) {
            // Child row style
            $sheet->getStyle("A$row:O$row")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F0F0F0');

                // I addItemToExcel funktionen, lige efter parent row styling:
$sheet->getStyle("I$row")->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('FFFFE0');
$sheet->getStyle("M$row")->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setRGB('ADD8E6');


            // Add child row data
            $sheet->setCellValue("A$row", $vlCounter->getRealItemNo($childData['itemno']));
            $sheet->setCellValue("B$row", $childData['name']);
            $sheet->setCellValue("C$row", "Antal: " . $childData['quantityper']);
            $sheet->setCellValue("D$row", $vlCounter->getIncoming($childData['itemno']));
            $sheet->setCellValue("E$row", $vlCounter->getAvailable($childData['itemno']));
            $sheet->setCellValue("F$row", $vlCounter->getAvailableAndIncoming($childData['itemno']));
            $sheet->setCellValue("G$row", $vlCounter->getSampakChildWaiting($childData['itemno'], $itemno));
            $sheet->setCellValue("H$row", $vlCounter->getSampakChildCanSend($childData['itemno'], $itemno));
          $warehouseMissing = $vlCounter->getSampakWarehouseMissing($childData['itemno'], $itemno);
                $sheet->setCellValue("I$row", ($warehouseMissing > 0 ? $warehouseMissing : "-"));
                $sheet->setCellValue("J$row", $vlCounter->getSampakGFPipeline($childData['itemno'], $itemno));
                $sheet->setCellValue("K$row", $vlCounter->getSampakSelectedLast7Days($childData['itemno'], $itemno));
                $sheet->setCellValue("L$row", $vlCounter->getSampakPrognose($childData['itemno'], $itemno));
                $sheet->setCellValue("M$row", $vlCounter->getSampakNextSuggestion($childData['itemno'], $itemno));
                $sheet->setCellValue("N$row", $vlCounter->getSampakOutgoing($childData['itemno'], $itemno));
                $sheet->setCellValue("O$row", $vlCounter->getSampakSent($childData['itemno'], $itemno));
                $row++;
            }
        }
        return $row;
    }

    public function showList()
    {

        Template::templateTop();
        Template::outputFrontendHeader("/gavefabrikken_backend/index.php?rt=unit/vlager/panel/front/".$this->vlager,\VLager::find($this->vlager));
        $vlCounter = new VLagerCounter($this->vlager,$this->shopid);

        // Generate a good looking bootstrap table
        ?><style>
        .table thead th {
            position: sticky;
            top: 0;
            background-color: #fff; /* Ændr farve efter behov */
            z-index: 10; /* Sørger for at headeren er ovenpå */
        }
        .container {
            max-height: calc(100vh - 100px); /* Træk headerens højde fra, fx 100px */
            overflow-y: auto;
        }


    </style>
        <div class="container" style="max-width: 100%;">

        <div style="padding-top: 15px; padding-bottom: 10px;">
        <div style="float: right;"><a href="/gavefabrikken_backend/index.php?rt=unit/vlager/panel/download/<?php echo $this->vlager; ?>/<?php echo $this->shopid; ?>">Download denne liste</a></div>
        <b>Vælg koncepter</b>: <a href='/gavefabrikken_backend/index.php?rt=unit/vlager/panel/liste/<?php echo $this->vlager; ?>'>ALLE</a>

        <?php

        $shops = $vlCounter->getVLagerShops();
        foreach($shops as $shop) {
            echo " | <a href='/gavefabrikken_backend/index.php?rt=unit/vlager/panel/liste/".$this->vlager."/".$shop->shop_id."'>".$shop->concept_code."</a>";
        }


        ?>

        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>Varenr</th>
                        <th>Beskrivelse</th>
                        <th>SAM</th>
                        <th>På vej</th>
                        <th>På lager</th>
                        <th>Tilgængelig</th>
                        <th>Venter ved GF</th>
                        <th>Kan sendes</th>
                        <th style="background: lightyellow;">Mangler på lager</th>
                        <th>Pipeline GF</th>
                        <th>Sidste 7 dage</th>
                        <th>Prognose</th>
                        <th style="background: lightblue;">Forslag</th>
                        <th>På vej ud</th>
                        <th>Sendt</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    $primaryItems = $vlCounter->getPrimaryItems();
                    foreach ($primaryItems as $itemno => $name) {
                        if($vlCounter->isBOMItem($itemno)) {
                            $this->printParentItemRow($vlCounter,$itemno,$name);
                        }
                    }

                    foreach ($primaryItems as $itemno => $name) {
                        if(!$vlCounter->isBOMItem($itemno)) {
                            $this->printParentItemRow($vlCounter,$itemno,$name);
                        }
                    }


                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php


        Template::templateBottom();


    }

    private function printParentItemRow($vlCounter,$itemno,$name) {


        $isSampak = $vlCounter->isBOMItem($itemno);
        $availableAndIncomging = $vlCounter->getAvailableAndIncoming($itemno);
        $warehouseMissing = $vlCounter->getWarehouseMissing($itemno);
        $suggestion = $vlCounter->getNextShippingSuggestion($itemno);
        $canSend = $vlCounter->getWarehouseCanSend($itemno);

        ?><tr style="<?php if($isSampak) echo "background: #E0E0E0; border-bottom: 2px solid #555555;border-top: 2px solid #555555;"; ?>">
            <td><?php echo $vlCounter->getRealItemNo($itemno); ?></td>
            <td><?php echo $name; ?></td>
            <td><?php echo $isSampak ? "JA":"NEJ"; ?></td>
            <td><?php echo ($vlCounter->getIncoming($itemno) > 0 || !$isSampak ? $vlCounter->getIncoming($itemno) : "-"); ?></td>
            <td><?php echo ($vlCounter->getAvailable($itemno) > 0 || !$isSampak ? $vlCounter->getAvailable($itemno) : "-"); ?></td>
            <td><?php echo ($availableAndIncomging > 0 || !$isSampak ? $availableAndIncomging : $availableAndIncomging); ?></td>
            <td><?php echo $vlCounter->getWaitingGF($itemno); ?></td>
            <td><?php echo $canSend; ?></td>
            <td style="background: lightyellow;"><?php echo ($warehouseMissing > 0 ? $warehouseMissing : "-"); ?></td>
            <td><?php echo $vlCounter->getPipeline($itemno); ?></td>
            <td><?php echo $vlCounter->getSelectedLast7Days($itemno); ?></td>
            <td><?php echo $vlCounter->getPrognose($itemno); ?></td>
            <td style="background: lightblue;"><?php echo $suggestion; ?></td>
            <td><?php echo $vlCounter->getOutgoing($itemno); ?></td>
            <td><?php echo $vlCounter->getSent($itemno); ?></td>
        </tr><?php

        if($vlCounter->isBOMItem($itemno)) {
            $chilItems = $vlCounter->getChildItems($itemno);
            foreach ($chilItems as $childItemno => $childData) {
                $this->printChildItemRow($vlCounter,$childData,$itemno);
            }
        }

    }

    private function printChildItemRow($vlCounter,$childData,$parentItemNo) {

        // Extract child data
        $childName = $childData['name'];
        $childItemno = $childData['itemno'];
        $childQuantityPer = $childData['quantityper'];

        // Calculate child values
        $availableAndIncomging = $vlCounter->getAvailableAndIncoming($childItemno);
        $waitingGF = $vlCounter->getSampakChildWaiting($childItemno,$parentItemNo);
        $canSend = $vlCounter->getSampakChildCanSend($childItemno,$parentItemNo);
        $warehouseMissing = $vlCounter->getSampakWarehouseMissing($childItemno,$parentItemNo);

        ?><tr style="background: #F0F0F0;">
        <td><?php echo $vlCounter->getRealItemNo($childItemno); ?></td>
        <td><?php echo $childName; ?></td>
        <td><?php echo "Antal: ".$childQuantityPer; ?> </td>
        <td><?php echo $vlCounter->getIncoming($childItemno); ?></td>
        <td><?php echo $vlCounter->getAvailable($childItemno); ?></td>
        <td><?php echo $availableAndIncomging; ?></td>
        <td><?php echo $waitingGF; ?></td>
        <td><?php echo $canSend; ?></td>
        <td style="background: lightyellow;"><?php echo ($warehouseMissing > 0 ? $warehouseMissing : "-"); ?></td>
        <td><?php echo $vlCounter->getSampakGFPipeline($childItemno,$parentItemNo); ?></td>
        <td><?php echo $vlCounter->getSampakSelectedLast7Days($childItemno,$parentItemNo); ?></td>
        <td><?php echo $vlCounter->getSampakPrognose($childItemno,$parentItemNo); ?></td>
        <td style="background: lightblue;"><?php echo $vlCounter->getSampakNextSuggestion($childItemno,$parentItemNo); ?></td>
        <td><?php echo $vlCounter->getSampakOutgoing($childItemno,$parentItemNo); ?></td>
        <td><?php echo $vlCounter->getSampakSent($childItemno,$parentItemNo); ?></td>
        </tr><?php

    }


}