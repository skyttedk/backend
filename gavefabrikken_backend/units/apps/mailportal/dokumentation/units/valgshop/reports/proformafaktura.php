<?php

namespace GFUnit\valgshop\reports;
use GFBiz\units\UnitController;
use GFBiz\valgshop\ValgshopFordeling;
use GFCommon\Model\Navision\SalesHeaderWS;

class ProformaFaktura
{

    private $shop;

    public function __construct($shopid)
    {
        $this->shop = \Shop::find(intvalgf($shopid));
    }

    public function showDownloadForm() {

        $adressList = \ShopAddress::find('all', array('conditions' => array('shop_id = ?', $this->shop->id)));

        ?><div id="proformafakturadownload">
        <h3><?php echo $this->shop->name; ?></h3>
        <div style="padding: 10px;">
            Hent proforma faktura på denne valgshop. Hent for alle eller vælg de specifikke adresser der skal hentes for.
        </div>
        <div style="padding: 10px;">
            Beløb pr. gave: <input type="text" name="present_amount" size="6" value=""> kr.<br>
        </div>
        <div style="padding: 10px;">
            <label><input type="radio" name="addressOption" value="0" checked onclick="toggleAddress(false)"> Alle gavevalg</label><br>
            <label><input type="radio" name="addressOption" value="1" onclick="toggleAddress(true)"> Vælg adresser</label>
        </div>
        <div id="addressList" style="padding: 10px; display: none;">
            <b>Adresser</b><br>
            <a href="#" onclick="toggleAllAddresses(true)">Vælg alle</a> | <a href="#" onclick="toggleAllAddresses(false)">Fravælg alle</a><br><?php
            foreach ($adressList as $adress) {
                echo "<div style='padding: 5px; padding-left: 0px;'><label><input class='addressCheckbox' type='checkbox' name='adress_".$adress->id."' value='1'>".$adress->name.", ".$adress->address.", ".$adress->country."</label></div>";
            }
            ?></div>
            <input type="hidden" name="shopid" value="<?php echo $this->shop->id; ?>">
        </div>

        <script type="text/javascript">
            function toggleAddress(show) {
                document.getElementById('addressList').style.display = show ? 'block' : 'none';
            }

            function toggleAllAddresses(select) {
                var checkboxes = document.getElementsByClassName('addressCheckbox');
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = select;
                }
            }

            function returnProformaFormValuesAsURL() {
                var url = '';
                var inputs = $('#proformafakturadownload').find('input');
                for (var i = 0; i < inputs.length; i++) {
                    if (inputs[i].type === 'radio' || inputs[i].type === 'checkbox') {
                        if (inputs[i].checked) {
                            url += inputs[i].name + '=' + inputs[i].value + '&';
                        }
                    } else {
                        url += inputs[i].name + '=' + inputs[i].value + '&';
                    }
                }
                return url.slice(0, -1);
            }
            
        </script>
        <?php
    }

    public function downloadFaktura()
    {

        $this->downloadFakturaV2();
        return;
        /*
        // New version
        if(\router::$systemUser->id == 50) {

        }


        $fordelingModel = new ValgshopFordeling($this->shop->id);

        $useAll = !isset($_GET["addressOption"]) || intval($_GET["addressOption"]) == 0;
        $amount = isset($_GET["present_amount"]) ? intval($_GET["present_amount"]) : 0;

        $vareNrMap = array();
        $this->phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $adressList = \ShopAddress::find('all', array('conditions' => array('shop_id = ?', $this->shop->id)));
        foreach($adressList as $adress) {
            if ($useAll || (isset($_GET['adress_'.$adress->id]) && intval($_GET['adress_'.$adress->id]) == 1)) {

                $adressData = $fordelingModel->getPresentDataForAdressID($adress->id);
                $localVareNrMap = array();

                foreach($adressData as $adressVare) {

                    $varenr = $adressVare["varenr"];
                    $antal = $adressVare["count"];

                    if (!isset($vareNrMap[$varenr])) {
                        $vareNrMap[$varenr] = 0;
                    }
                    $vareNrMap[$varenr] += $antal;

                    if (!isset($localVareNrMap[$varenr])) {
                        $localVareNrMap[$varenr] = 0;
                    }
                    $localVareNrMap[$varenr] = +$antal;

                }

                $this->writeSheet($adress->address.", ".$adress->zip.", ".$adress->country, $localVareNrMap, $amount,$adress);
            }
        }


        $this->writeSheet("Total", $vareNrMap,$amount,$adress);

        $this->phpExcel->removeSheetByIndex(0);
        $this->phpExcel->setActiveSheetIndex(0);

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="proformafaktura_' . $this->shop->id . '_' . $this->shop->alias . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->phpExcel);
        $objWriter->save('php://output');
        exit();
        */

    }

    private $usedNames = array();

    private function writeSheet($name,$vareNrMap,$amount,$adress) {

        foreach($vareNrMap as $varenr => $count) {
            $bomItems = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE `language_id` = 1 AND `parent_item_no` LIKE '".$varenr."' AND `deleted` IS NULL");
            if (count($bomItems) > 0) {
                foreach ($bomItems as $bomItem) {
                    if(isset($vareNrMap[$bomItem->no])) {
                        $vareNrMap[$bomItem->no] += $count * $bomItem->quantity_per;
                    } else {
                        $vareNrMap[$bomItem->no] = $count * $bomItem->quantity_per;
                    }
                }
                unset($vareNrMap[$varenr]);
            }
        }



        $title = str_replace(array("*",":","/","\\","?","[","]"), '', $name);
        $title = trimgf($title);
        $title = substr($title,0,20);
        $title = preg_replace('/[^'.utf8_encode("aoaAOA").'0-9a-zA-Z_\s]/', '', $title);
        if(trimgf($title) == "") $title = "Ukendt";

        if(isset($this->usedNames[strtolower($title)])) {
            $this->usedNames[strtolower($title)]++;
            $title = substr($title,0,17) . "(".$this->usedNames[strtolower($title)].")";
        } else {
            $this->usedNames[strtolower($title)] = 1;

        }

        // Write header
        $sheet = $this->phpExcel->createSheet();
        $this->phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->setTitle($title);

        $row = 3;

        $sheet->setCellValueByColumnAndRow(1, $row, "Delivery address");
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row,$adress->name);
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row,$adress->address);
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row,$adress->zip);
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row,$adress->city);
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row,$adress->country);
        $row++;
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row, "Att:".$adress->att);
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row, "Phone no: ".$adress->phone);
        $row++;

/*
        $row++;
        $row++;
        $row++;
        $row++;
        $row++;
*/
        $row++;
        $row++;


        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(5);
        $sheet->getColumnDimension('I')->setWidth(11);
        $sheet->getColumnDimension('J')->setWidth(13);
        $sheet->setCellValueByColumnAndRow(1, $row, "Cat no");
        $sheet->setCellValueByColumnAndRow(2,  $row, "Item Description");
        $sheet->setCellValueByColumnAndRow(3,  $row, "weight kg. net");
        $sheet->setCellValueByColumnAndRow(4,  $row, "weight kg. gross");
        $sheet->setCellValueByColumnAndRow(5,  $row, "HS-code");
        $sheet->setCellValueByColumnAndRow(6,  $row, "Country of Origin");
        $sheet->setCellValueByColumnAndRow(7,  $row, "Colli Type");
        $sheet->setCellValueByColumnAndRow(8,  $row, "Qty");
        $sheet->setCellValueByColumnAndRow(9,  $row, "Unit value");
        $sheet->setCellValueByColumnAndRow(10,  $row, "Total value");
        $sheet->getStyle("A". $row.":J". $row)->getFont()->setBold(true);
        $row++;

        foreach($vareNrMap as $varenr => $count) {

            $navisionItemList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$varenr."' AND `deleted` IS NULL");

            if(count($navisionItemList) > 0) {

                $item = $navisionItemList[0];
                $itemDesc = $item->description;
                $weightGross = $item->gross_weight;
                $weightNet = $item->net_weight;
                $tariffNo = $item->tariff_no;
                $countryOfOrigin = $item->countryoforigin;
                $unitCost = $item->unit_cost;

            } else {
                if($varenr == "") {
                    $varenr = "IKKE-VALGT";
                }
                $itemDesc = "Not in nav:";
                $weightGross = "";
                $weightNet = "";
                $tariffNo = "";
                $countryOfOrigin = "";
                $unitCost = 0;
            }

            $sheet->setCellValueByColumnAndRow(1, $row, $varenr);
            $sheet->setCellValueByColumnAndRow(2, $row, $itemDesc);
            $sheet->setCellValueByColumnAndRow(3, $row, $weightNet);
            $sheet->setCellValueByColumnAndRow(4, $row, $weightGross);
            $sheet->setCellValueByColumnAndRow(5, $row, $tariffNo);
            $sheet->setCellValueByColumnAndRow(6, $row,  $countryOfOrigin);
            $sheet->setCellValueByColumnAndRow(7, $row, "box");
            $sheet->setCellValueByColumnAndRow(8, $row, $count);
            $sheet->setCellValueByColumnAndRow(9, $row, $amount);
            $sheet->setCellValueByColumnAndRow(10, $row, $amount*$count);
            $row++;

        }


    }

    private $phpExcel = null;

    /**
     * VERSION2
     */

    private function loadNavInvoiceData($soNo) {

        $orderData = null;
        try {

            $SalesHeaderWS = new SalesHeaderWS();
            $result = $SalesHeaderWS->getHeader("ORDER", $soNo);

            if($result == null) {
                echo "NO SALES ORDER FOUND";
                throw new \Exception("No salesorder found");
            }

            $orderData = $result->getDataArray();
            return array(
                "name" => $orderData["Bill_to_Name"] ?? "-",
                "address" => $orderData["Bill_to_Address"] ?? "-",
                "zipcity" => ($orderData["Bill_to_Post_Code"] ?? "")." ".($orderData["Bill_to_City"] ?? ""),
                "country" => $orderData["Bill_to_Country_Region_Code"] ?? "",
                "phone" => $orderData["Sell_to_Contact_Phone_No"] ?? "",
                "att" => $orderData["Bill_to_Contact"] ?? "",
                "no" => $orderData["Bill_to_Customer_No"] ?? "",
                "orderno" => $soNo
            );

        } catch (\Exception $e) {
            //echo "NAV INVOICE DATA EXCEPTION: ".$e->getMessage();
            return null;
        }
    }
    
    private function loadCSInvoiceData($company) {
        return array(
            "name" => $company->name,
            "address" => $company->bill_to_address,
            "zipcity" => $company->bill_to_postal_code." ".$company->bill_to_city,
            "country" => $company->bill_to_country,
            "phone" => $company->contact_phone,
            "att" => $company->contact_name,
            "no" => "-",
            "orderno" => "-"
        );
    }
    
    public function downloadFakturaV2() {

        $fordelingModel = new ValgshopFordeling($this->shop->id);

        $useAll = !isset($_GET["addressOption"]) || intval($_GET["addressOption"]) == 0;
        $amount = isset($_GET["present_amount"]) ? intval($_GET["present_amount"]) : 0;

        $vareNrMap = array();
        $this->phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Load invoice data
        $invoiceData = null;
        $companyShop = \CompanyShop::find_by_sql("SELECT * FROM `company_shop` WHERE `shop_id` = ".intval($this->shop->id));

        if(count($companyShop) > 0) {
            $company = \Company::find($companyShop[0]->company_id);
            $invoiceData = $this->loadNavInvoiceData($company->so_no);
            if($invoiceData === null) {
                $invoiceData = $this->loadCSInvoiceData($company);
            }
        }

        if($invoiceData == null) {
            $invoiceData = array(
                "name" => "Kunne ikke finde kunde",
                "address" => "-",
                "zipcity" => "-",
                "country" => "-",
                "phone" => "-",
                "att" => "-",
                "no" => "-",
                "orderno" => "-"
            );
        }

        $invoiceNo = 1;

        // Load adresselist
        $adressList = \ShopAddress::find('all', array('conditions' => array('shop_id = ?', $this->shop->id)));
        foreach($adressList as $adress) {
            if ($useAll || (isset($_GET['adress_'.$adress->id]) && intval($_GET['adress_'.$adress->id]) == 1)) {

                $adressData = $fordelingModel->getPresentDataForAdressID($adress->id);
                $localVareNrMap = array();

                foreach($adressData as $adressVare) {

                    $varenr = $adressVare["varenr"];
                    $antal = $adressVare["count"];

                    if (!isset($vareNrMap[$varenr])) {
                        $vareNrMap[$varenr] = 0;
                    }
                    $vareNrMap[$varenr] += $antal;

                    if (!isset($localVareNrMap[$varenr])) {
                        $localVareNrMap[$varenr] = 0;
                    }
                    $localVareNrMap[$varenr] = +$antal;

                }

                $this->writeSheetV2($adress->address.", ".$adress->zip.", ".$adress->country, $localVareNrMap, $amount,$adress,$invoiceData,$invoiceNo,$adress->vatno);
                $invoiceNo++;
            }
        }



        $this->writeSheetV2("Total", $vareNrMap,$amount,$adress,$invoiceData,$invoiceNo,$adress->vatno);

 
        $this->phpExcel->removeSheetByIndex(0);
        $this->phpExcel->setActiveSheetIndex(0);

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="proformafaktura_' . $this->shop->id . '_' . $this->shop->alias . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->phpExcel);
        $objWriter->save('php://output');
        exit();

    }

    private function writeSheetV2($name,$vareNrMap,$amount,$adress,$invoiceData,$invoiceNo=1,$vatno="",$soNo="") {

        foreach($vareNrMap as $varenr => $count) {
            $bomItems = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE `language_id` = 1 AND `parent_item_no` LIKE '".$varenr."' AND `deleted` IS NULL");
            if (count($bomItems) > 0) {
                foreach ($bomItems as $bomItem) {
                    if(isset($vareNrMap[$bomItem->no])) {
                        $vareNrMap[$bomItem->no] += $count * $bomItem->quantity_per;
                    } else {
                        $vareNrMap[$bomItem->no] = $count * $bomItem->quantity_per;
                    }
                }
                unset($vareNrMap[$varenr]);
            }
        }



        $title = str_replace(array("*",":","/","\\","?","[","]"), '', $name);
        $title = trimgf($title);
        $title = substr($title,0,20);
        $title = preg_replace('/[^'.utf8_encode("aoaAOA").'0-9a-zA-Z_\s]/', '', $title);
        if(trimgf($title) == "") $title = "Ukendt";

        if(isset($this->usedNames[strtolower($title)])) {
            $this->usedNames[strtolower($title)]++;
            $title = substr($title,0,17) . "(".$this->usedNames[strtolower($title)].")";
        } else {
            $this->usedNames[strtolower($title)] = 1;

        }

        // Background
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
        ];

        $this->phpExcel->getDefaultStyle()->applyFromArray($styleArray);
        $this->phpExcel->getDefaultStyle()->getFont()->setSize(8);

        // Write header
        $sheet = $this->phpExcel->createSheet();
        $this->phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->setTitle($title);


        // Række højder
        $sheet->getRowDimension('1')->setRowHeight(67);

        for ($i = 2; $i <= 20; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(14);
        }

        // Kolonne bredder
        $columnWidths = [12, 35, 14, 8, 8, 9,9, 7, 8, 10];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I','J'];
        foreach ($columns as $key => $column) {
            $sheet->getColumnDimension($column)->setWidth($columnWidths[$key]);
        }


        // Logo
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(dirname(__FILE__).DIRECTORY_SEPARATOR."gflogo.jpg"); // Sæt stien til dit billede her
        $drawing->setHeight(75); // Højden kan justeres efter behov
        $drawing->setCoordinates('D1'); // Du kan ændre koordinaterne til det sted, hvor du vil placere billedet
        $drawing->setOffsetX(10); // Du kan justere den horisontale position
        $drawing->setOffsetY(10); // Du kan justere den vertikale position
        $drawing->setWorksheet($sheet);



        // Invoice adress
        $sheet->setCellValue('A2', 'Invoice address');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(8);

        $sheet->setCellValue('A3', $invoiceData["name"]);
        $sheet->setCellValue('A4', $invoiceData["address"]);
        $sheet->setCellValue('A5', $invoiceData["zipcity"]);
        $sheet->setCellValue('A6', $invoiceData["country"]);
        $sheet->setCellValue('A8', $invoiceData["att"]);
        $sheet->setCellValue('A9', $invoiceData["phone"]);


        // Delivery address
        $sheet->setCellValue('C2', 'Delivery address');
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(8);

        $sheet->setCellValue('C3', $adress->name);
        $sheet->setCellValue('C4',$adress->address);
        $sheet->setCellValue('C5', $adress->zip." ". $adress->city);
        $sheet->setCellValue('C6', $adress->country);
        $sheet->setCellValue('C8', 'ATT: '.$adress->att);
        $sheet->setCellValue('C9', 'Phone: '.$adress->phone);



        // Right details
        $sheet->setCellValue('E12', 'Invoice');
        $sheet->getStyle('E12')->getFont()->setBold(true)->setSize(12);
        $sheet->setCellValue('E13', 'PINV'.sprintf('%04d', $invoiceNo).' - For Customs purpose only');
        $sheet->getStyle('E13')->getFont()->setBold(true)->setSize(12);

        $sheet->setCellValue('E15', 'Date');
        $sheet->setCellValue('G15', date("d-m-Y"));

        $sheet->setCellValue('E16', 'Customer nr.');
        $sheet->setCellValue('G16', $invoiceData["no"]);

        $sheet->setCellValue('E17', 'VAT.nr');
        $sheet->setCellValue('G17', $vatno);

        $sheet->setCellValue('E18', 'Sales Order Number');
        $sheet->setCellValue('G18', $invoiceData["orderno"]);

        $sheet->setCellValue('E19', 'Vendor VAT.nr');
        $sheet->setCellValue('G19', 'DK31332702');

        $sheet->setCellValue('E20', 'INCO term');
        $sheet->setCellValue('G20', 'DDP');

        // Items
        $row = 23;

        $titles = ['Item no', 'Description', 'HS Kode', 'Country of origin', 'Colli type','Net weight', 'Gross weight', 'Qty', 'Price', 'Amount'];


        $sheet->getRowDimension($row)->setRowHeight(20);

        foreach ($titles as $key => $title) {
            $cell = $columns[$key] . $row;
            $sheet->setCellValue($cell, $title);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
        }


        $sheet->getStyle('D'.$row)->getAlignment()->setWrapText(true);
        $row++;

        $totalAmount = 0;
        $totalNetWeight = 0;
        $totalGrossWeight = 0;

        $rowStart = $row;
        $rowEnd = $row;

        // Items
        foreach($vareNrMap as $varenr => $count) {

            $sheet->getRowDimension($row)->setRowHeight(14);
            $navisionItemList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$varenr."' AND `deleted` IS NULL");

            if(count($navisionItemList) > 0) {

                $item = $navisionItemList[0];
                $itemDesc = $item->description;
                $weightGross = $item->gross_weight;
                $weightNet = $item->net_weight;
                $tariffNo = $item->tariff_no;
                $countryOfOrigin = $item->countryoforigin;
                $unitCost = $item->unit_cost;

            } else {
                if($varenr == "") {
                    $varenr = "IKKE-VALGT";
                }
                $itemDesc = "Not in nav:";
                $weightGross = "";
                $weightNet = "";
                $tariffNo = "";
                $countryOfOrigin = "";
                $unitCost = 0;
            }

            $sheet->setCellValueByColumnAndRow(1, $row, $varenr);
            $sheet->setCellValueByColumnAndRow(2, $row, $itemDesc);
            $sheet->setCellValueByColumnAndRow(3, $row, $tariffNo);
            $sheet->setCellValueByColumnAndRow(4, $row,  $countryOfOrigin);
            $sheet->setCellValueByColumnAndRow(5, $row, "box");
            $sheet->setCellValueByColumnAndRow(6, $row, floatval($weightNet) > 0 ? floatval($weightNet)*$count : 0);
            $sheet->setCellValueByColumnAndRow(7, $row, floatval($weightGross) > 0 ? floatval($weightGross)*$count : 0);
            $sheet->setCellValueByColumnAndRow(8, $row, $count);
            $sheet->setCellValueByColumnAndRow(9, $row, $amount);

            //$sheet->setCellValueByColumnAndRow(10, $row, $amount*$count);
            $sheet->setCellValueByColumnAndRow(10, $row, "=H".$row."*I".$row);
            $row++;
            $rowEnd++;

            $totalAmount += $amount*$count;

            $totalNetWeight += floatval($weightNet) > 0 ? floatval($weightNet)*$count : 0;
            $totalGrossWeight += floatval($weightGross) > 0 ? floatval($weightGross)*$count : 0;

        }

        $row += 3;

        // Opret en reference til cellestilnummerformat for tal
        $numberFormat = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00;

// Anvend formatet på kolonne F og H for det pågældende rækkeområde
        $sheet->getStyle("F".$rowStart.":F".$rowEnd)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle("G".$rowStart.":G".$rowEnd)->getNumberFormat()->setFormatCode($numberFormat);

        // Opret en reference til cellestilnummerformat for tal
        $numberFormat = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER;

// Anvend formatet på kolonne F og H for det pågældende rækkeområde
        $sheet->getStyle("H".$rowStart.":H".$rowEnd)->getNumberFormat()->setFormatCode($numberFormat);


        // Weight total
        $sheet->setCellValue("C".$row, "Net weight total (KG)");
        $sheet->setCellValue("C".$row+1, "Gross weight total (KG)");

        //$sheet->setCellValue("E".$row, round($totalNetWeight,2)." KG");
        $sheet->setCellValue("E".$row, "=ROUND(SUM(F".$rowStart.":F".$rowEnd."), 2)");


        //$sheet->setCellValue("E".$row+1,  round($totalGrossWeight,2)." KG");
        $sheet->setCellValue("E".$row+1, "=ROUND(SUM(G".$rowStart.":G".$rowEnd."), 2)");



        // Price total
        //$sheet->setCellValue("G".$row, "Netto");
        $sheet->setCellValue("G".$row+1, "Total DKK");

        //$sheet->setCellValue("I".$row, round($totalAmount));
        //$sheet->setCellValue("I".$row+1,round($totalAmount));
        $sheet->setCellValue("I".$row+1, "=ROUND(SUM(J".$rowStart.":J".$rowEnd."),2)");


        $row += 5;
        $sheet->setCellValue("C".$row,"GaveFabrikken, Carl Jacobsens vej 20, 2500 Valby, Denmark");

        $sheet->getStyle("C".$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    }


}