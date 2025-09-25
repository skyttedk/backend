<?php

namespace GFUnit\cardshop\cards;
use GFBiz\units\UnitController;
use GFBiz\valgshop\ValgshopFordeling;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\SalesHeaderWS;

class ProformaFaktura
{

    private $companyid;
    private $shopuserlist;

    private $usedNames = array();
    private $phpExcel = null;


    public function __construct($shopuserlist,$companyid)
    {
        $this->companyid = $companyid;
        $this->shopuserlist = $shopuserlist;
    }


    public function downloadFakturaV2() {


        $vareNrMap = array();
        $this->phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $orderno = "CSORDER";


        $companyOrderMap = array();
        $cardshopSettingsMap = array();
        $vareNrMap = array();
        $amountMap = array();

        // Process each shopuser
        foreach($this->shopuserlist as $shopUser) {

            // Find order
            if(isset($companyOrderMap[$shopUser->company_order_id])) {
                $companyOrder = $companyOrderMap[$shopUser->company_order_id];
            } else {
                $companyOrder = \CompanyOrder::find($shopUser->company_order_id);
                $companyOrderMap[$shopUser->company_order_id] = $companyOrder;
            }

            $orderno = $companyOrder->order_no;

            // Find cardshop settings
            if(isset($cardshopSettingsMap[$shopUser->shop_id])) {
                $cardshopSettings = $cardshopSettingsMap[$shopUser->shop_id];
            } else {
                $cardshopSettings = \CardshopSettings::find_by_shop_id($shopUser->shop_id);
                $cardshopSettingsMap[$shopUser->shop_id] = $cardshopSettings;
            }

            // Find order
            $order = \Order::find_by_shopuser_id($shopUser->id);
            if($order == null) {
                
                $varenr = $cardshopSettings->default_present_itemno;

            } else {

                $presentModel = \PresentModel::find_by_sql("SELECT * FROM present_model where model_id = ".$order->present_model_id." AND language_id = 1");
                $varenr = $presentModel[0]->model_present_no;

            }

            if (!isset($vareNrMap[$varenr])) {
                $vareNrMap[$varenr] = 0;
            }
            $vareNrMap[$varenr]++;
            $amountMap[$varenr] = $cardshopSettings->card_price/100;

        }

        // Load invoice data
        $invoiceData = null;
        $company = \Company::find($this->companyid);
        $invoiceData = $this->loadNavInvoiceData($company->nav_customer_no,$company->language_code,$orderno);
        if($invoiceData === null) {
            $invoiceData = $this->loadCSInvoiceData($company,$orderno);
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

        // Construct adress
        $adress = array(
            "name" => $company->ship_to_company,
            "address" => $company->ship_to_address,
            "zip" => $company->ship_to_postal_code,
            "city" => $company->ship_to_city,
            "country" => $company->ship_to_country,
            "att" => $company->contact_name,
            "phone" => $company->contact_phone
        );


        $invoiceNo = 1;
        $this->writeSheetV2($company->ship_to_company, $vareNrMap, $amountMap,$adress,$invoiceData,$invoiceNo,$company->cvr);

        $this->phpExcel->removeSheetByIndex(0);
        $this->phpExcel->setActiveSheetIndex(0);

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="proformafaktura_' .$company->name.'.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->phpExcel);
        $objWriter->save('php://output');
        exit();

    }






    /**
     * VERSION2
     */

    private function loadNavInvoiceData($nav_customer_no,$language,$orderno) {

        $orderData = null;
        try {
            
            $CustomerWS = new CustomerWS($language);
            $result = $CustomerWS->getByCustomerNo($nav_customer_no);
            if($result == null) {
                throw new \Exception("No customer data found");
            }

            return array(
                "name" => $result->getName() ?? "-",
                "address" => $result->getAddress() ?? "-",
                "zipcity" => $result->getPostCode()." ".$result->getCity(),
                "country" => $result->getCountryCode(),
                "phone" => $result->getPhone(),
                "att" => $result->getContact(),
                "no" => $nav_customer_no,
                "orderno" => $orderno
            );

        } catch (\Exception $e) {
            //echo "NAV INVOICE DATA EXCEPTION: ".$e->getMessage();
            return null;
        }
    }

    private function loadCSInvoiceData($company,$orderno) {
        return array(
            "name" => $company->name,
            "address" => $company->bill_to_address,
            "zipcity" => $company->bill_to_postal_code." ".$company->bill_to_city,
            "country" => $company->bill_to_country,
            "phone" => $company->contact_phone,
            "att" => $company->contact_name,
            "no" => "-",
            "orderno" => $orderno
        );
    }



    private function writeSheetV2($name,$vareNrMap,$amountMap,$adress,$invoiceData,$invoiceNo=1,$vatno="",$soNo="") {

        foreach($vareNrMap as $varenr => $count) {
            $bomItems = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE `language_id` = 1 AND `parent_item_no` LIKE '".$varenr."' AND `deleted` IS NULL");
            if (count($bomItems) > 0) {
                foreach ($bomItems as $bomItem) {
                    if(isset($vareNrMap[$bomItem->no])) {
                        $vareNrMap[$bomItem->no] += $count * $bomItem->quantity_per;
                        $amountMap[$bomItem->no] = $amountMap[$varenr];
                    } else {
                        $vareNrMap[$bomItem->no] = $count * $bomItem->quantity_per;
                        $amountMap[$bomItem->no] = $amountMap[$varenr];
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

        $sheet->setCellValue('C3', $adress["name"]);
        $sheet->setCellValue('C4',$adress["address"]);
        $sheet->setCellValue('C5', $adress["zip"]." ". $adress["city"]);
        $sheet->setCellValue('C6', $adress["country"]);
        $sheet->setCellValue('C8', 'ATT: '.$adress["att"]);
        $sheet->setCellValue('C9', 'Phone: '.$adress["phone"]);




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


            $sheet->setCellValueByColumnAndRow(9, $row, $amountMap[$varenr]);

            //$sheet->setCellValueByColumnAndRow(10, $row, $amount*$count);
            $sheet->setCellValueByColumnAndRow(10, $row, "=H".$row."*I".$row);
            $row++;
            $rowEnd++;

            $totalAmount += $amountMap[$varenr]*$count;

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