<?php


/**
 *  REPORT LOCATION CLASS
 */
class reportLocation
{

    public $string = "";
    public $att;
    public $phone;
    public $company;
    public $adress;
    public $zipcity;
    public $country;
    public $valid;
    private $companyObj;
    public $sortOrder;

    public $vatno = "";
    public $email = "";

    public function getZip() {
        $parts = explode(" ",$this->zipcity);
        return isset($parts[0]) ? $parts[0] : "";
    }

    public function getCity() {
        $parts = explode(" ",$this->zipcity,2);
        return isset($parts[1]) ? $parts[1] : "";
    }

    public function __construct($input,$company=null)
    {



        $this->companyObj = $company;
        if($input === null) $input = "";

        $this->valid = false;
        if($input instanceof ShopAddress)
        {
            $this->att = $input->att;
            $this->phone = $input->phone;
            $this->company = $input->name;
            $this->adress = $input->address;
            $this->zipcity = $input->zip." ".$input->city;
            $this->country = $input->country;
            $this->valid = true;
            $this->sortOrder = $input->index;
            $this->string =  $this->company.", ".$this->adress.", ".$this->zipcity.", ".$this->country.", ".$this->att;
            $this->vatno = $input->vatno;
        }
        else
        {
            $this->string = $input;
            $lines = explode(",",$input);
            if(count($lines) > 0 && trimgf($input) != "") $this->valid = true;
            if(count($lines) > 0) $this->company = $lines[0];
            if(count($lines) > 1) $this->adress = $lines[1];
            if(count($lines) > 2) $this->zipcity = $lines[2];
            if(count($lines) > 3) $this->country = $lines[3];
            if(count($lines) > 4) $this->att = $lines[4];
            if(count($lines) > 5) $this->phone = $lines[5];

        }

        if(trimgf($this->phone) == "" && $this->companyObj != null) {
            $this->phone = $this->companyObj->contact_phone;
        }

        if($this->valid == true)
        {
            self::$knownLocations[trimgf($this->string)] = $this;
        }


    }

    private static $knownLocations = array();
    public static function getByString($string)
    {
        if(isset(self::$knownLocations[trimgf($string)])) return self::$knownLocations[trimgf($string)];
        else return new reportLocation(null);
    }


}

/**
 * SHOP FORDELING REPORT
 */
class shopForedelingRapport Extends reportBaseController
{

    // Member variables
    private $shop;
    private $userMap;
    private $attributeList;
    private $userDataMap;
    private $locationMap;
    private $orderMap;
    private $shoppresentMap;
    private $modelMap;
    private $presentMap;
    private $copyMap;
    private $error = "";
    private $warningList;
    private $isSumList;
    private $sumData;

    private $useItemNo = false;

    /**
     * RUN REPORT
     */

    private $isSumPDF = true;
    private $debug = false;
    private $usePartial = false;
    private $partialDate = null;
    private $isPrivatlev = false;
    private $isPrivatlevSum = false;

    private $supressHeaders = false;


    public function run($shopID,$listType="fordeling")
    {

        if(isset($_GET["supressheaders"]) && $_GET["supressheaders"]) {
            $this->supressHeaders = true;
        }

        ob_start();

        if($listType == "sum" || $listType == "sumexcel") {
            if($listType == "sumexcel") {
                $this->isSumPDF = false;
            }
            $this->isSumList = true;
        } else if($listType == "label") {
            $this->isSumList = false;
            $this->sameSheet = true;
            $this->labels = true;

            if(isset($_GET["witemno"]) && intval($_GET["witemno"]) == 1) {
                $this->useItemNo = true;
            }

        } else if($listType == "privatlevering") {
            $this->isPrivatlev = true;
        } else if($listType == "privatleveringsum") {
            $this->isPrivatlevSum = true;
        } else {
            $this->isSumList = false;
        }


        // Load data
        if(!$this->loadData($shopID))
        {
            ob_end_clean();
            echo "Kan ikke danne rapport: ".$this->error;
            return;
        }


        if($this->isPrivatlev == true) {
            $this->downloadPrivateDelivery();
            exit();
        }

        if($this->isPrivatlevSum == true) {
            $this->getPrivateSumList();
            exit();
        }

        if($listType == "check") {

            $this->debug = true;
            echo "TJEK HER!<br><pre>";

            $rows = $this->compileOrderList("location");
            echo "LOCATION MAP: ".count($this->locationMap);
            echo "<br>ORDERLIST: ".count($rows)."<br><br>";

            foreach($rows as $key => $row) {
                echo $key . " - ".count($row)."<br>";
            }

            //print_r($this->locationMap);
            //$rows = $this->compileOrderList("location");
            //print_r($rows);

            echo "</pre>";
            exit();

        }

        if($listType == "adresser") {
            $this->generateAdresseListe();
            return;
        }

        if($listType == "adresseimport") {
            $this->generateAdresseListeImport();
            return;
        }

        try
        {

            if($this->isSumList)
            {
                if($this->isSumPDF == true) {
                    $this->generateSumList();
                } else {
                    $this->generateSumListExcel();
                }
            }
            else
            {
                if(isset($_GET["likecs"]) && intval($_GET["likecs"]) == 1) {
                    $this->generateFordelingsRapportLikeCS();
                } else {
                    $this->generateFordelingsRapport();
                }
            }
        }
        catch(Exception $e)
        {
            echo "<pre>".print_r($e,true)."</pre>";
        }


    }

    /**
     * PRIVATLEVERINGSLISTE
     */

    private function downloadPrivateDelivery() {

        // Init phpexcel
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $phpExcel->removeSheetByIndex(0);

        // Write header
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->setTitle("Privatlevering");
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(71);
        $sheet->getColumnDimension('E')->setWidth(9);
        $sheet->getColumnDimension('F')->setWidth(21);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(38);
        $sheet->getColumnDimension('I')->setWidth(22);

        $row=1;
        $showComplaint = true;

        $date1 = date("Y-m-d",(time()))."-08:30:00";
        $date2 = date("Y-m-d",(time()))."-16:00:00";

        $privateDeliveryAttributes = array(
            4163 => array("name" => 24553, "email" => 24554, "phone" => 24675, "address" => 24676, "zip" => 24677,"city" => 24678,"country" => 24679)
            //4346 => array("name" => 25692, "email" => 25693, "phone" => 26840, "address" => 26832, "zip" => 26838,"city" => 26839,"country" => 0)
        );

        $attrMap = $privateDeliveryAttributes[$this->shop->id] ?: array("name" => 0, "email" => 0, "phone" => 0, "address" => 0, "zip" => 0,"city" => 0,"country" => 0);

        // Get shopuser orders
        foreach($this->userMap as $shopUser) {


            // Load order
            $order = \Order::find_by_sql("SELECT * FROM `order` WHERE `shopuser_id` = ".$shopUser->id);
            $userAttributes = \UserAttribute::find_by_sql("SELECT * FROM user_attribute where shopuser_id = ".$shopUser->id);
            $attributeMap = array();

            foreach($userAttributes as $attr) {
                $attributeMap[$attr->attribute_id] = $attr->attribute_value;
            }

            // Get user data
            $userData = array(
                "name" => $attributeMap[$attrMap["name"]] ?: "-",
                "address" => $attributeMap[$attrMap["address"]] ?: "-",
                "address2" => "",
                "postnr" => $attributeMap[$attrMap["zip"]] ?: "-",
                "bynavn" => $attributeMap[$attrMap["city"]] ?: "-",
                "land" => $attributeMap[$attrMap["country"]] ?: "-",
                "telefon" => $attributeMap[$attrMap["phone"]] ?: "-",
                "email" => $attributeMap[$attrMap["email"]] ?: "-"
            );

            $phone = trim(str_replace(array("+"," ","-"),"",$userData["telefon"]));

            if($phone != "") {

                if($userData["land"] == "Sverige") {

                    if(substr($phone,0,2) == "46") {
                        $phone = substr($phone,2);
                    }

                }

                if($userData["land"] == "Danmark") {

                    while(substr($phone,0,1) == "0" && strlen($phone) > 0) {
                        $phone = substr($phone,1);
                    }

                    if(substr($phone,0,2) == "45" && strlen($phone) > 8) {
                        $phone = substr($phone,2);
                    }

                    if(strlen($phone) > 8) {
                        $phone = substr($phone,0,8);
                    }

                }

            }

            // Find present
            $model = \PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE `model_id` = ".$order[0]->present_model_id." AND `language_id` = 1");

            // Write data row
            $sheet->setCellValueByColumnAndRow(1, $row, ($userData["name"]));
            $sheet->setCellValueByColumnAndRow(2, $row, $userData["email"]);
            $sheet->setCellValueByColumnAndRow(3, $row, $phone);
            $sheet->setCellValueByColumnAndRow(4, $row, $userData["address"].((trimgf($userData["address2"]) != "" && mb_strtolower(trimgf($userData["address"])) != mb_strtolower(trimgf($userData["address2"])))? ", ".$userData["address2"] : ""));
            $sheet->setCellValueByColumnAndRow(5, $row, str_replace(" ","",$userData["postnr"]));
            $sheet->setCellValueByColumnAndRow(6, $row, $userData["bynavn"]);
            $sheet->setCellValueByColumnAndRow(7, $row, $userData["land"]);
            $sheet->setCellValueByColumnAndRow(8, $row, $model[0]->model_present_no.": ".$model[0]->model_name);
            $sheet->setCellValueByColumnAndRow(10,$row,$date1);
            $sheet->setCellValueByColumnAndRow(11,$row,$date2);
            $sheet->setCellValueByColumnAndRow(12,$row,$shopUser->id);

            if($showComplaint) {
                $complaint = \OrderPresentComplaint::find("all",array("conditions" => array("shopuser_id" => $shopUser->id)));
                if(count($complaint) > 0) {
                    $sheet->setCellValueByColumnAndRow(13,$row,"!");
                    $sheet->setCellValueByColumnAndRow(14,$row,str_replace("\n"," - ",urldecode($complaint[0]->complaint_txt)));
                }
            }

            $row++;

        }

        // Output excel file
        //header('Content-Type: application/vnd.ms-excel');
        if(!$this->supressHeaders) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment;filename="privatlevering-'.$this->shop->name.'-'.$this->partialDate.'.csv"');
            header('Cache-Control: max-age=0');
        }
        $phpExcel->setActiveSheetIndex(0);

        //$objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
        exit();

    }


    private function getPrivateSumList()
    {

        ob_start();

        ?><h2>Privatlevering sumliste - <?php echo $this->shop->name; ?></h2>
        <b>Batch: <?php echo $this->shop->id." / ".$this->partialDate; ?></b>
        <table><thead>
            <tr>
                <th style="width: 100px;">Varenr.</th>
                <th>Gave navn</th>
                <th style="width: 120px;">Antal</th>
            </tr>
            </thead><tbody>
            <?php

            // Get shopuser orders
            $aliasList = array();
            $aliasCount = array();
            $presentInfo = array();


            // Get shopuser orders
            foreach($this->userMap as $shopUser) {

                // Load order
                $order = \Order::find_by_sql("SELECT * FROM `order` WHERE `shopuser_id` = ".$shopUser->id);

                // Find present
                $model = \PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE `model_id` = ".$order[0]->present_model_id." AND `language_id` = 1");

                if (!in_array($model[0]->model_present_no, $aliasList)) {
                    $aliasList[] = $model[0]->model_present_no;
                    $aliasCount[$model[0]->model_present_no] = 0;
                    $presentInfo[$model[0]->model_present_no] = array("varenr" => $model[0]->model_present_no, "name" => $model[0]->model_name,"modelname" => "");
                }
                $aliasCount[$model[0]->model_present_no]++;

            }

            natsort($aliasList);

            $totalCount = 0;
            foreach($aliasList as $fullAlias)
            {
                $present = $presentInfo[$fullAlias];

                if($this->showVarenr($present["varenr"])) {

                    $totalCount += $aliasCount[$fullAlias];
                    ?><tr>
                    <td style="padding-right: 10px;"><?php echo wordwrap($present["varenr"],21,"<br>",true); ?></td>
                    <td><?php echo trimgf(utf8_decode(htmlentities($present["name"]))); ?></td>
                    <td><?php echo trimgf(utf8_decode(htmlentities($present["modelname"]))); ?></td>
                    <td style="text-align: right;"><?php echo $aliasCount[$fullAlias]; ?></td>
                    </tr><?php
                }
            }

            ?></tbody></table>
        <div  style=" text-align: right; margin-top: 10px;">Total antal gaver: <?php echo $totalCount; ?></div><?php

        // Finish and output
        $content = ob_get_contents();
        ob_end_clean();
        error_reporting(E_ALL ^ E_NOTICE);
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML(utf8_encode($content));
        $mpdf->Output("Sumliste-".$this->shop->name."-".$this->partialDate.".pdf","D");

        echo $content;
    }

    private function showVarenr($itemno) {

        /* ELGIGANTEN - RIKKE - 15/12
        if($this->shop->id == 3086) {
            if($itemno == "SAM2017") return true;
            return false;
        }
        */

        return true;
    }

    /****
     * ADRESSE LISTE
     */

    private function generateAdresseListeImport()
    {
        // Init phpexcel
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $phpExcel->removeSheetByIndex(0);

        // Write header
        $outsheet = $phpExcel->createSheet();
        $outsheet->setTitle("contacts");
        $outRow = 1;


        $outsheet->getColumnDimension('B')->setWidth(20);
        $outsheet->getColumnDimension('C')->setWidth(25);
        $outsheet->getColumnDimension('D')->setWidth(24);
        $outsheet->getColumnDimension('E')->setWidth(11);
        $outsheet->getColumnDimension('F')->setWidth(16);
        $outsheet->getColumnDimension('G')->setWidth(12);
        $outsheet->getColumnDimension('H')->setWidth(11);
        $outsheet->getColumnDimension('I')->setWidth(14);
        $outsheet->getColumnDimension('J')->setWidth(15);


        $outsheet->setCellValueByColumnAndRow(1,$outRow,"Company name");
        $outsheet->setCellValueByColumnAndRow(2,$outRow,"Address 1");
        $outsheet->setCellValueByColumnAndRow(3,$outRow,"Address 2");
        $outsheet->setCellValueByColumnAndRow(4,$outRow,"Postcode");
        $outsheet->setCellValueByColumnAndRow(5,$outRow,"City");
        $outsheet->setCellValueByColumnAndRow(6,$outRow,"Country Code");
        $outsheet->setCellValueByColumnAndRow(7,$outRow,"State Code");
        $outsheet->setCellValueByColumnAndRow(8,$outRow,"Contact person");
        $outsheet->setCellValueByColumnAndRow(9,$outRow,"Phone");
        $outsheet->setCellValueByColumnAndRow(10,$outRow,"E-mail");
        $outsheet->setCellValueByColumnAndRow(11,$outRow,"Shop name");
        $outRow++;

        foreach($this->locationMap as $location) {


            $outsheet->setCellValueByColumnAndRow(1,$outRow,$location->company);
            $outsheet->setCellValueByColumnAndRow(2,$outRow,$location->adress);
            $outsheet->setCellValueByColumnAndRow(3,$outRow,"");
            $outsheet->setCellValueByColumnAndRow(4,$outRow,$location->getZip());
            $outsheet->setCellValueByColumnAndRow(5,$outRow,$location->getCity());
            $outsheet->setCellValueByColumnAndRow(6,$outRow,$location->country);
            $outsheet->setCellValueByColumnAndRow(7,$outRow,"");
            $outsheet->setCellValueByColumnAndRow(8,$outRow,$location->att);
            $outsheet->setCellValueByColumnAndRow(9,$outRow,$location->phone);
            $outsheet->setCellValueByColumnAndRow(10,$outRow,"");
            $outsheet->setCellValueByColumnAndRow(11,$outRow,$this->shop->name);
            $outRow++;
        }


        // Send http headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$this->shop->name."_"."adresseliste"."_".date("d-m-Y").'.xlsx"');
        header('Cache-Control: max-age=0');


        // Output as xlsx file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($phpExcel);
        $writer->save("php://output");
    }

    private function generateAdresseListe()
    {
        //echo "<pre>".print_r($this->locationMap,true)."</pre>";

        $this->objPHPExcel->removeSheetByIndex(0);

        $sheet = $this->objPHPExcel->createSheet();
        $sheet->setTitle("Adresseliste");

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);


        $sheet->setCellValueByColumnAndRow(1, 1, "Navn");
        $sheet->setCellValueByColumnAndRow(2, 1, "Adresse");
        $sheet->setCellValueByColumnAndRow(3, 1, "Postnr");
        $sheet->setCellValueByColumnAndRow(4, 1, "By");
        $sheet->setCellValueByColumnAndRow(5, 1, "Land");
        $sheet->setCellValueByColumnAndRow(6, 1, "Gaveansvarlig");
        $sheet->setCellValueByColumnAndRow(7, 1, "Telefon");

        $row = 2;
        foreach($this->locationMap as $location) {

            $sheet->setCellValueByColumnAndRow(1, $row, $location->company);
            $sheet->setCellValueByColumnAndRow(2, $row, $location->adress);
            $sheet->setCellValueByColumnAndRow(3, $row, $location->getZip());
            $sheet->setCellValueByColumnAndRow(4, $row, $location->getCity());
            $sheet->setCellValueByColumnAndRow(5, $row, $location->country);
            $sheet->setCellValueByColumnAndRow(6, $row, $location->att);
            $sheet->setCellValueByColumnAndRow(7, $row, $location->phone);
            $row++;
        }

        $this->objPHPExcel->setActiveSheetIndex(0);

        if($this->supressHeaders) {
            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
            $objWriter->save("php://output");
        } else {
            $this->save($this->shop->name."_"."adresseliste"."_".date("d-m-Y"));
        }

    }

    /**
     * SUM LISTE
     */

    private function generateSumList()
    {


        // Compile order list
        $rows = $this->compileOrderList("location");

        $this->sumHeader();
        $this->sumFrontPage();


        $indexCount = 0;


        // Make list for correct order
        $locationOrderedList = array();
        foreach($this->locationMap as $location) {
            $locationOrderedList[] = array("location" => $location->string, "order" => $location->sortOrder);
        }

        for($i=0;$i<count($locationOrderedList);$i++) {
            for($j=$i+1;$j<count($locationOrderedList);$j++) {
                if($locationOrderedList[$i]["order"] > $locationOrderedList[$j]["order"]) {
                    $tmp = $locationOrderedList[$i];
                    $locationOrderedList[$i] = $locationOrderedList[$j];
                    $locationOrderedList[$j] = $tmp;
                }
            }
        }

        $totalCount = countgf($rows);
        $processedLocations = array();





        foreach($rows as $locationName => $list)
        {
            if(!in_array($locationName,$processedLocations)) {

                $location = reportLocation::getByString($locationName,$this->company);
                if(!$location->valid)
                {
                    $indexCount++;
                    $processedLocations[] = $locationName;
                    echo "<h2>Ingen lokation angivet</h2><br>";
                    $this->sumLocationPage(null,$list,false);
                }

            }
        }


        foreach ($locationOrderedList as $orderedLocation) {

            $locationName = $orderedLocation["location"];
            if(!in_array($locationName,$processedLocations)) {


                if(isset($rows[$locationName])) {

                    $list = $rows[$locationName];
                    $processedLocations[] = $locationName;

                    $location = reportLocation::getByString($locationName,$this->company);
                    if($location->valid)
                    {
                        $indexCount++;
                        $this->sumLocationPage($location,$list,($totalCount == $indexCount));
                    }

                }
            }
        }

        foreach($rows as $locationName => $list)
        {
            if(!in_array($locationName,$processedLocations)) {
                $processedLocations[] = $locationName;

                $location = reportLocation::getByString($locationName,$this->company);
                if($location->valid)
                {
                    $indexCount++;
                    $this->sumLocationPage($location,$list,($totalCount == $indexCount));
                }

            }
        }

        $this->sumFooter();

        $content = ob_get_contents();
        ob_end_clean();

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->setFooter("Side {PAGENO} / {nb}");
        $mpdf->WriteHTML(utf8_encode($content));

        if($this->supressHeaders) {
            echo $mpdf->Output($this->shop->name."-sumliste-".date("d-m-Y").".pdf","S");
        } else {
            $mpdf->Output($this->shop->name."-sumliste-".date("d-m-Y").".pdf","D");
        }


    }

    private function sumHeader()
    {
        ?><html>
        <head>

            <style>
                table { width: 100%; border-collapse: collapse; border: 2px solid #aaa; font-size: 14px; }
                td, th { paddong: 3px; border: 2px solid #aaa; }
                th { text-align: center; font-weight: bold; background: #E9F0F2; }
                tr:nth-child(even) {background: #E9F0F2}
                tr:nth-child(odd) {background: #FFFFFF}

            </style>

        </head><body><?php
    }

    private function sumFooter()
    {
        ?></body></html><?php
    }

    private function sumFrontPage()
    {

        ?><h3>Sumliste <?php echo utf8_decode($this->shop->name); ?></h3><?php

        if(count($this->warningList) > 0) echo "<div style='float: right; font-size: 0.8em; background: #FF4136; color: white; padding: 5px; text-align: center;'>".countgf($this->warningList)." notifikationer (se fordelingsrapporten for detaljer)</div><br>";
        if($this->company != null)
        {
            echo "<table style='width: 100%; border: none;' border='0'>
          <tr>
            <td style='width: 33%; border: none;' valign=top><b>SO nr:</b><br>".$this->company->so_no."</td>
            <td style='width: 33%; border: none;' valign=top><b>Salger:</b><br>".$this->company->sales_person."</td>
            <td style='width: 33%; border: none;' valign=top><b>Ansvarlig:</b><br>".$this->company->gift_responsible."</td>
          </tr>
        </table>";

        }
        /*
        <table>
           <tr>
               <td>Ordrenummer: UKENDT</td>
               <td>Leveringsdato: UKENDT</td>
               <td>Salger: UKENDT</td>
           </tr>
           <tr>
             <td>Ansvarlig: UKENDT</td>
             <td>&nbsp;</td>
             <td>&nbsp;</td>
           </tr>
        </table>
        */
        ?><br>

        <table>
            <thead>
            <tr>
                <th style="width: 100px;">Varenr.</th>
                <th style="width: 150px;">Sampak varer</th>
                <th style="width: 80px;">Gave nr</th>
                <th>Beskrivelse</th>

                <th style="width: 120px;">Antal</th>
            </tr>
            </thead>
            <tbody>
            <?php

            $presentList = array();
            $totalCount = 0;

            foreach($this->sumData as $alias => $present)
            {
                $present["alias"] = $alias;
                $presentList[] = $present;
            }

            // Reorder list by alias
            for($i = 0; $i < countgf($presentList); $i++)
            {
                for($j = $i+1; $j < countgf($presentList); $j++)
                {
                    if(intval($presentList[$i]["alias"]) > intval($presentList[$j]["alias"]) || (intval($presentList[$i]["alias"]) == intval($presentList[$j]["alias"]) && (strcmp ( $presentList[$i]["alias"],$presentList[$j]["alias"] ) > 0)))
                    {
                        $tmp = $presentList[$i];
                        $presentList[$i] = $presentList[$j];
                        $presentList[$j] = $tmp;
                    }
                }
            }

            foreach($presentList as $present)
            {
                if($this->showVarenr($present["varenr"])) {
                    $totalCount += $present["count"];
                    ?><tr>
                    <td style="padding-right: 10px;"><?php echo wordwrap($present["varenr"],21,"<br>",true); ?></td>
                    <td style="padding-right: 10px;"><?php echo implode(", ",$this->getItemNoToSampak($present["varenr"])); ?></td>
                    <td><?php echo $present["alias"]; ?></td>
                    <td><?php echo trimgf(utf8_decode(htmlentities($present["description"]))); ?></td>

                    <td style="text-align: right;"><?php echo $present["count"]; ?></td>
                    </tr><?php
                }
            }



            ?>


            </tbody>
        </table>
        <div  style="page-break-after: always; text-align: right; margin-top: 10px;">Total antal gaver: <?php echo $totalCount; ?></div>
        <?php
    }

    private $cachedSampakLists = null;

    private function getItemNoToSampak($ItemNo) {

        // Check cache
        if($this->cachedSampakLists != null && isset($this->cachedSampakLists[$ItemNo])) {
            return $this->cachedSampakLists[$ItemNo];
        }

        $list = array();

        /*
        $languageid = intval($this->shopSettings->language_code);
        if(substr(trimgf(strtolower($varenr)),0,3) == "sam" && $languageid == 4) {
            $languageid = 1;
        }
        */
        $languageid = 1;

        $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = ".$languageid." && parent_item_no = '".$ItemNo."' && deleted is null");
        foreach($navbomItemList as $item) {
            if(!in_array($item->no,$list)) {
                $list[] = ($item->quantity_per > 1 ? "(".$item->quantity_per." ".$item->unit_of_measure_code.")" : "").$item->no;
            }
        }

        // Add to cache
        if($this->cachedSampakLists == null) {
            $this->cachedSampakLists = array();
        }
        $this->cachedSampakLists[$ItemNo] = $list;

        return $list;
    }

    private function sumLocationPage($location,$list,$lastPage=false)
    {

        if($location != null) {
            ?><table>
            <tr>
                <td style="width: 25%; font-weight: bold;">Leveringsadresse</td>
                <td><?php echo htmlentities($location->string); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Attention</td>
                <td><?php echo htmlentities($location->att); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Telefon</td>
                <td><?php echo htmlentities($location->phone); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Firmanavn</td>
                <td><?php echo htmlentities($location->company); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Adresse</td>
                <td><?php echo htmlentities($location->adress); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Postnummer / by</td>
                <td><?php echo htmlentities($location->zipcity); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Land</td>
                <td><?php echo htmlentities($location->country); ?></td>
            </tr>

            </table><br><?php
        }
        else $location = new reportLocation("",$this->company);

        if($this->company != null)
        {
            echo "<table style='width: 100%; border: none;' border='0'>
          <tr>
            <td style='width: 33%; border: none;' valign=top><b>SO nr:</b>".$this->company->so_no."</td>
            
          </tr>
        </table><br>";

        }

        ?><table style="">
        <thead>
        <tr>
            <th style="width: 100px;">Varenr.</th>
            <th style="width: 150px;">Sampak varer</th>
            <th style="width: 80px;">Gave nr</th>

            <th>Beskrivelse</th>

            <th style="width: 120px;">Antal</th>
        </tr>
        </thead>
        <tbody>
        <?php


        $presentList = array();

        foreach($this->sumData as $alias => $present)
        {
            $count = 0;
            if(isset($present["locationmap"][$location->string])) $count = $present["locationmap"][$location->string];

            $present["count"] = $count;
            $present["alias"] = strtolower($alias);
            $presentList[] = $present;
        }

        // Reorder list by alias
        for($i = 0; $i < countgf($presentList); $i++)
        {
            for($j = $i+1; $j < countgf($presentList); $j++)
            {
                if(intval($presentList[$i]["alias"]) > intval($presentList[$j]["alias"]) || (intval($presentList[$i]["alias"]) == intval($presentList[$j]["alias"]) && strcmp ( $presentList[$i]["alias"],$presentList[$j]["alias"] )  > 0))
                {
                    $tmp = $presentList[$i];
                    $presentList[$i] = $presentList[$j];
                    $presentList[$j] = $tmp;
                }
            }
        }

        $totalCount = 0;

        foreach($presentList as $present)
        {
            if($present["count"] > 0)
            {
                if($this->showVarenr($present["varenr"])) {
                    $totalCount += $present["count"];
                    ?><tr>
                    <td><?php echo wordwrap($present["varenr"],21,"<br>",true); ?></td>
                    <td style="padding-right: 10px;"><?php echo implode(", ",$this->getItemNoToSampak($present["varenr"])); ?></td>
                    <td><?php echo $present["alias"]; ?></td>
                    <td><?php echo trimgf(utf8_decode(htmlentities($present["description"]))); ?></td>

                    <td style="text-align: right;"><?php echo $present["count"]; ?></td>
                    </tr><?php
                }
            }
        }

        ?>


        </tbody>
        </table><div  style="<?php if(!$lastPage) { ?>page-break-after: always;<?php } ?> text-align: right; margin-top: 10px;">Total antal gaver: <?php echo $totalCount; ?></div><?php

    }

    private function sumLocationWarning($locationName,$lastPage)
    {
        ?><div style="<?php if(!$lastPage) { ?>page-break-after: always;<?php } ?>">
        <h3>ADVARSEL:<br>Lokationen <?php $locationName; ?> kunne ikke findes.</h3>
        </div><?php
    }


















    /**
     * SUM LISTE
     */

    private function generateSumListExcel()
    {

        // Compile order list
        $rows = $this->compileOrderList("location");

        $this->objPHPExcel->removeSheetByIndex(0);

        $this->addWarningSheet();

        $sheet = $this->objPHPExcel->createSheet();
        $sheet->setTitle("Sumliste");
        $this->sumFrontPageExcel($sheet);


        $indexCount = 0;


        // Make list for correct order
        $locationOrderedList = array();
        foreach($this->locationMap as $location) {
            $locationOrderedList[] = array("location" => $location->string, "order" => $location->sortOrder);
        }

        for($i=0;$i<count($locationOrderedList);$i++) {
            for($j=$i+1;$j<count($locationOrderedList);$j++) {
                if($locationOrderedList[$i]["order"] > $locationOrderedList[$j]["order"]) {
                    $tmp = $locationOrderedList[$i];
                    $locationOrderedList[$i] = $locationOrderedList[$j];
                    $locationOrderedList[$j] = $tmp;
                }
            }
        }

        $totalCount = countgf($rows);

        $processedLocations = array();

        foreach($rows as $locationName => $list)
        {
            if(!in_array($locationName,$processedLocations)) {
                $location = reportLocation::getByString($locationName,$this->company);
                if(!$location->valid)
                {
                    $processedLocations[] = $locationName;
                    //$unknownLocation = new reportLocation("Ukendt lokation".(trimgf($locationName) != "" ? "[".$locationName."]" : ""));
                    $this->sumLocationPageExcel(null,$list,($totalCount == $indexCount));
                    //$this->sumLocationWarning($locationName,($totalCount == $indexCount));
                }
            }
        }

        foreach ($locationOrderedList as $orderedLocation) {

            $locationName = $orderedLocation["location"];
            if(!in_array($locationName,$processedLocations)) {
                $indexCount++;

                if (isset($rows[$locationName])) {

                    $list = $rows[$locationName];
                    $processedLocations[] = $locationName;

                    $location = reportLocation::getByString($locationName, $this->company);
                    if ($location->valid) {
                        $this->sumLocationPageExcel($location, $list, ($totalCount == $indexCount));
                    }
                }
            }

        }

        foreach($rows as $locationName => $list)
        {
            if(!in_array($locationName,$processedLocations)) {

                $processedLocations[] = $locationName;
                $location = reportLocation::getByString($locationName,$this->company);
                if($location->valid)
                {
                    $this->sumLocationPageExcel($location,$list,($totalCount == $indexCount));
                }
            }
        }

        // Add warnings
        $this->writeWarningSheet();
        $content = ob_get_contents();
        ob_end_clean();

        $this->objPHPExcel->setActiveSheetIndex(0);

        if($this->supressHeaders) {
            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
            $objWriter->save("php://output");
        } else {
            $this->save($this->shop->name . "_sumliste_" . date("d-m-Y"));
        }
    }


    private function sumFrontPageExcel( $sheet)
    {
        $currentRow = 1;

        // Headline
        $sheet->setCellValueByColumnAndRow(1,$currentRow,"Sumliste ".($this->shop->name));
        $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
        $currentRow++;

        // Add warning
        if(count($this->warningList) > 0) {
            $sheet->setCellValueByColumnAndRow(1,$currentRow,countgf($this->warningList)." notifikationer (se warning ark for detaljer)");
            $currentRow++;
        }

        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(10);

        // Add company data
        if($this->company != null)
        {

            $currentRow++;
            $sheet->getStyle('A'.$currentRow.':C'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"SO nr:");
            $sheet->setCellValueByColumnAndRow(1,$currentRow+1,$this->company->so_no);

            $sheet->setCellValueByColumnAndRow(2,$currentRow,"Salger:");
            $sheet->setCellValueByColumnAndRow(2,$currentRow+1,$this->company->sales_person);

            $sheet->setCellValueByColumnAndRow(3,$currentRow,"Ansvarlig:");
            $sheet->setCellValueByColumnAndRow(3,$currentRow+1,$this->company->gift_responsible);

            $currentRow++;
            $currentRow++;
            $currentRow++;
        }

        $sheet->getStyle('A'.$currentRow.':D'.$currentRow.'')->getFont()->setBold(true);
        $sheet->setCellValueByColumnAndRow(1,$currentRow,"Varenr");
        $sheet->setCellValueByColumnAndRow(2,$currentRow,"Sampak varenr");
        $sheet->setCellValueByColumnAndRow(3,$currentRow,"Gave nr");
        $sheet->setCellValueByColumnAndRow(4,$currentRow,"Beskrivelse");
        $sheet->setCellValueByColumnAndRow(5,$currentRow,"Antal");
        $currentRow++;

        $presentList = array();
        $totalCount = 0;

        foreach($this->sumData as $alias => $present)
        {
            $present["alias"] = $alias;
            $presentList[] = $present;
        }

        // Reorder list by alias
        for($i = 0; $i < countgf($presentList); $i++)
        {
            for($j = $i+1; $j < countgf($presentList); $j++)
            {
                if(intval($presentList[$i]["alias"]) > intval($presentList[$j]["alias"]) || (intval($presentList[$i]["alias"]) == intval($presentList[$j]["alias"]) && (strcmp ( $presentList[$i]["alias"],$presentList[$j]["alias"] ) > 0)))
                {
                    $tmp = $presentList[$i];
                    $presentList[$i] = $presentList[$j];
                    $presentList[$j] = $tmp;
                }
            }
        }

        foreach($presentList as $present)
        {
            if($this->showVarenr($present["varenr"])) {
                $totalCount += $present["count"];
                $sheet->setCellValueByColumnAndRow(1, $currentRow, $present["varenr"]);
                $sheet->setCellValueByColumnAndRow(2, $currentRow, implode(", ", $this->getItemNoToSampak($present["varenr"])));
                $sheet->setCellValueByColumnAndRow(3, $currentRow, $present["alias"]);
                $sheet->setCellValueByColumnAndRow(4, $currentRow, $present["description"]);
                $sheet->setCellValueByColumnAndRow(5, $currentRow, $present["count"]);
                $currentRow++;
            }
        }

        $currentRow++;
        $sheet->setCellValueByColumnAndRow(1,$currentRow,"Total antal gaver");
        $sheet->setCellValueByColumnAndRow(2,$currentRow,$totalCount);

    }

    private function sumLocationPageExcel($location,$list,$lastPage=false)
    {

        if($location == null)
        {

            $title = "Mangler lokation";
        }
        else
        {
            $title = str_replace(array("*",":","/","\\","?","[","]"), '', $location->string);
        }

        // Create sheet
        $title = trimgf($title);
        $title = substr($title,0,20);
        $title = preg_replace('/[^'.utf8_encode("aoaAOA").'0-9a-zA-Z_\s]/', '', $title);

        if(trimgf($title) == "") $title = "Ukendt";

        if(isset($this->usedNames[strtolower($title)])) {
            $sheetTitle = substr($title,0,17) . "(".$this->usedNames[strtolower($title)].")";
            $this->usedNames[strtolower($title)]++;
        } else {
            $this->usedNames[strtolower($title)] = 1;
            $sheetTitle = $title;
        }

        // Create sheet
        $sheet = $this->objPHPExcel->createSheet();
        $sheet->setTitle($sheetTitle);

        // Set col widths
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(10);

        $currentRow = 1;

        if($location != null) {

            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"Leveringsadresse");
            $sheet->setCellValueByColumnAndRow(2,$currentRow,($location->string));
            $currentRow++;

            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"Attention");
            $sheet->setCellValueByColumnAndRow(2,$currentRow,($location->att));
            $currentRow++;

            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"Telefon");
            $sheet->setCellValueByColumnAndRow(2,$currentRow,($location->phone));
            $currentRow++;

            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"Firmanavn");
            $sheet->setCellValueByColumnAndRow(2,$currentRow,($location->company));
            $currentRow++;

            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"Adresse");
            $sheet->setCellValueByColumnAndRow(2,$currentRow,($location->adress));
            $currentRow++;

            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"Postnummer / by");
            $sheet->setCellValueByColumnAndRow(2,$currentRow,($location->zipcity));
            $currentRow++;

            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"Land");
            $sheet->setCellValueByColumnAndRow(2,$currentRow,($location->country));
            $currentRow++;
            $currentRow++;

        }
        else {
            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"Ingen lokation angivet");
            $currentRow += 2;

        }

        if($this->company != null)
        {
            $sheet->getStyle('A'.$currentRow.':A'.$currentRow.'')->getFont()->setBold(true);
            $sheet->setCellValueByColumnAndRow(1,$currentRow,"SO nr:");
            $sheet->setCellValueByColumnAndRow(1,$currentRow,$this->company->so_no);
            $currentRow++;
            $currentRow++;
        }

        $sheet->getStyle('A'.$currentRow.':D'.$currentRow.'')->getFont()->setBold(true);
        $sheet->setCellValueByColumnAndRow(1,$currentRow,"Varenr");
        $sheet->setCellValueByColumnAndRow(2,$currentRow,"Sampak varer");
        $sheet->setCellValueByColumnAndRow(3,$currentRow,"Gave nr");
        $sheet->setCellValueByColumnAndRow(4,$currentRow,"Beskrivelse");
        $sheet->setCellValueByColumnAndRow(5,$currentRow,"Antal");
        $currentRow++;

        $presentList = array();

        if($location === null) {
            $location = new reportLocation("",$this->company);
        }

        foreach($this->sumData as $alias => $present)
        {
            $count = 0;
            if(isset($present["locationmap"][$location->string])) $count = $present["locationmap"][$location->string];

            $present["count"] = $count;
            $present["alias"] = strtolower($alias);
            $presentList[] = $present;
        }

        // Reorder list by alias
        for($i = 0; $i < countgf($presentList); $i++)
        {
            for($j = $i+1; $j < countgf($presentList); $j++)
            {
                if(intval($presentList[$i]["alias"]) > intval($presentList[$j]["alias"]) || (intval($presentList[$i]["alias"]) == intval($presentList[$j]["alias"]) && strcmp ( $presentList[$i]["alias"],$presentList[$j]["alias"] )  > 0))
                {
                    $tmp = $presentList[$i];
                    $presentList[$i] = $presentList[$j];
                    $presentList[$j] = $tmp;
                }
            }
        }

        $totalCount = 0;

        foreach($presentList as $present)
        {
            if($present["count"] > 0)
            {
                if($this->showVarenr($present["varenr"])) {
                    $totalCount += $present["count"];
                    $sheet->setCellValueByColumnAndRow(1, $currentRow, $present["varenr"]);
                    $sheet->setCellValueByColumnAndRow(2, $currentRow, implode(", ", $this->getItemNoToSampak($present["varenr"])));
                    $sheet->setCellValueByColumnAndRow(3, $currentRow, $present["alias"]);
                    $sheet->setCellValueByColumnAndRow(4, $currentRow, $present["description"]);
                    $sheet->setCellValueByColumnAndRow(5, $currentRow, $present["count"]);
                    $currentRow++;
                }
            }
        }

        $currentRow++;
        $sheet->setCellValueByColumnAndRow(1,$currentRow,"Total antal gaver");
        $sheet->setCellValueByColumnAndRow(2,$currentRow,$totalCount);

    }










    /**
     * FORFDELINGSRAPPORT
     */

    private $csReportRow;

    private function generateFordelingsRapportLikeCS()
    {

        // Compile order list
        $rows = $this->compileOrderList("location");
        $this->csReportRow = 0;

        $this->objPHPExcel->removeSheetByIndex(0);
        $this->addWarningSheet();

        $this->createCSPlulisteSheet();

        // Write order list
        $sheet = $this->objPHPExcel->createSheet();
        $sheet->setTitle("Ordreliste");


        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(12);

        $sheet->setCellValueByColumnAndRow(1, 1, $this->shop->name . " (id " . $this->shop->id . "): ordreliste");
        $sheet->setCellValueByColumnAndRow(3, 1, "Budget: Valgshop");
        $sheet->setCellValueByColumnAndRow(5, 1, "Deadline: ".$this->shop->end_date->format("Y-m-d"));

        $BackBlue = array(
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => array('rgb' => 'e9f0f2')
            ),
            'borders' => array(
                'top' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ),
                'bottom' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                )
            )
        );

        $sheet->getStyle('A1:H1')->applyFromArray($BackBlue);
        $this->csReportRow = 3;

        // Make list for correct order
        $locationOrderedList = array();
        foreach($this->locationMap as $location) {
            $locationOrderedList[] = array("location" => $location->string, "order" => $location->sortOrder);
        }

        for($i=0;$i<count($locationOrderedList);$i++) {
            for($j=$i+1;$j<count($locationOrderedList);$j++) {
                if($locationOrderedList[$i]["order"] > $locationOrderedList[$j]["order"]) {
                    $tmp = $locationOrderedList[$i];
                    $locationOrderedList[$i] = $locationOrderedList[$j];
                    $locationOrderedList[$j] = $tmp;
                }
            }
        }

        $processedLocations = array();

        foreach($rows as $locationName => $list)
        {

            if(!in_array($locationName,$processedLocations)) {
                $location = reportLocation::getByString($locationName,$this->company);
                if(!$location->valid)
                {
                    $processedLocations[] = $locationName;
                    $this->handleLikeCSSheet($sheet,null,$list);
                    if(trimgf($locationName) != "") $this->addWarning("Kunne ikke finde lokationen: ".$locationName);
                }
            }
        }

        foreach ($locationOrderedList as $orderedLocation) {

            $locationName = $orderedLocation["location"];
            if(!in_array($locationName,$processedLocations)) {

                if (isset($rows[$locationName])) {

                    $list = $rows[$locationName];
                    $processedLocations[] = $locationName;

                    $location = reportLocation::getByString($locationName, $this->company);
                    if ($location->valid) {
                        $this->handleLikeCSSheet($sheet,$location, $list);
                    }

                }
            }

        }

//        echo "<pre>".print_r($processedLocations,true)."</pre>"; exit();
        //     echo "<pre>".print_r($rows,true)."</pre>"; exit();


        foreach($rows as $locationName => $list)
        {

            if(!in_array($locationName,$processedLocations)) {
                $processedLocations[] = $locationName;
                $location = reportLocation::getByString($locationName,$this->company);
                if($location->valid)
                {
                    $this->handleLikeCSSheet($sheet,$location,$list);
                }
            }
        }


        if(isset($_GET["debug"]) && $_GET["debug"] == 1) exit();

        // Add warnings
        $this->writeWarningSheet();
        $content = ob_get_contents();
        ob_end_clean();

        $this->objPHPExcel->setActiveSheetIndex(0);
        if($this->supressHeaders) {
            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
            $objWriter->save("php://output");
        } else {
            $this->save($this->shop->name . "_" . "plukliste" . "_" . date("d-m-Y"));
        }

    }


    private function handleLikeCSSheet($sheet,$location,$list)
    {

        /*
                echo "<h1>Handle location: ".$location->string."</h1><br>";
                echo $location->company."<br>";
                echo $location->adress."<br>";
                echo $location->zipcity."<br>";
                echo $location->country."<br>";
                echo "<div><table>";
        */

        $row = $this->csReportRow;
        if($location == null)
        {
            $location = new reportLocation("",$this->company);
        }

        $compName = $location->company;
        $sheet->setBreak('A' . $this->csReportRow, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        $zipCitySplit = explode(" ",trimgf($location->zipcity),2);
        if(count($zipCitySplit) == 1) $zipCitySplit[] = "";


        // Company details
        $row++;
        $sheet->getStyle("A" . $row . ":Z" . $row)->getFont()->setBold(true);
        $sheet->setCellValueByColumnAndRow(1, $row, "Virksomhed");
        $sheet->setCellValueByColumnAndRow(2, $row, "Adresse");
        $sheet->setCellValueByColumnAndRow(3, $row, "Postnr");
        $sheet->setCellValueByColumnAndRow(4, $row, "By");
        $sheet->setCellValueByColumnAndRow(5, $row, "Kontakt navn");
        $sheet->setCellValueByColumnAndRow(6, $row, "Kontakt telefon");
        $sheet->setCellValueByColumnAndRow(7, $row, "CVR");
        $sheet->setCellValueByColumnAndRow(8, $row, "Kontakt e-mail");
        $row++;


        $sheet->setCellValueByColumnAndRow(1, $row, $location->company);
        $sheet->setCellValueByColumnAndRow(2, $row, $location->adress);
        $sheet->setCellValueByColumnAndRow(3, $row, $zipCitySplit[0]);
        $sheet->setCellValueByColumnAndRow(4, $row, $zipCitySplit[1]);
        $sheet->setCellValueByColumnAndRow(5, $row, $location->att);
        $sheet->setCellValueByColumnAndRow(6, $row, $location->phone);
        $sheet->setCellValueByColumnAndRow(7, $row, trimgf($location->vatno) == "" ? $this->company->cvr : $location->vatno);
        $sheet->setCellValueByColumnAndRow(8, $row, $this->company->contact_email);

        $row++;

        $sheet->getStyle("A" . $row . ":Z" . $row)->getFont()->setBold(true);
        $sheet->setCellValueByColumnAndRow(1, $row, "Levering");
        $sheet->setCellValueByColumnAndRow(2, $row, "Leveringsadresse");
        $sheet->setCellValueByColumnAndRow(3, $row, "Levering postnr");
        $sheet->setCellValueByColumnAndRow(4, $row, "Levering by");
        $sheet->setCellValueByColumnAndRow(5, $row, "Kontakt Navn");
        $sheet->setCellValueByColumnAndRow(6, $row, "Kontakt telefon");
        $sheet->setCellValueByColumnAndRow(7, $row, "CVR");
        $sheet->setCellValueByColumnAndRow(8, $row, "Kontakt e-mail");
        $row++;

        $sheet->setCellValueByColumnAndRow(1, $row, $location->company);
        $sheet->setCellValueByColumnAndRow(2, $row, $location->adress);
        $sheet->setCellValueByColumnAndRow(3, $row, $zipCitySplit[0]);
        $sheet->setCellValueByColumnAndRow(4, $row, $zipCitySplit[0]);
        $sheet->setCellValueByColumnAndRow(5, $row, $location->att);
        $sheet->setCellValueByColumnAndRow(6, $row, $location->phone);
        $sheet->setCellValueByColumnAndRow(7, $row, trimgf($location->vatno) == "" ? $this->company->cvr : $location->vatno);
        $sheet->setCellValueByColumnAndRow(8, $row, $this->company->contact_email);

        $row++;
        $row++;
        $row++;

        // Order list headline
        $sheet->setCellValueByColumnAndRow(1, $row, "Firmanavn");
        $sheet->setCellValueByColumnAndRow(2, $row, "Gavekort nr");
        $sheet->setCellValueByColumnAndRow(3, $row, "Gave nr.");
        $sheet->setCellValueByColumnAndRow(4, $row, "Gave");
        $sheet->setCellValueByColumnAndRow(5, $row, "Model");
        $sheet->setCellValueByColumnAndRow(6, $row, "Navn");
        $sheet->setCellValueByColumnAndRow(7, $row, "E-mail");
        $sheet->setCellValueByColumnAndRow(8, $row, "Deadline");

        if($this->indpakValue != "") {
            $sheet->setCellValueByColumnAndRow(9, $row, "Indpakket");
        }
        if($this->specialValue != "") {
            $sheet->setCellValueByColumnAndRow(10, $row, "Speciallevering");
        }

        $sheet->getStyle("A" . $row . ":J" . $row)->getFont()->setBold(true);
        $row++;

        // Reorder list by alias
        for($i = 0; $i < countgf($list); $i++)
        {
            for($j = $i+1; $j < countgf($list); $j++)
            {
                if(intval($list[$i]["presentAlias"]) > intval($list[$j]["presentAlias"]) || (intval($list[$i]["presentAlias"]) == intval($list[$j]["presentAlias"]) && strcmp ( $list[$i]["presentAlias"],$list[$j]["presentAlias"] ) > 0))
                {
                    $tmp = $list[$i];
                    $list[$i] = $list[$j];
                    $list[$j] = $tmp;
                }
            }
        }


        $counter = 0;
        foreach($list as $index => $rowData)
        {
            $sheet->setCellValueByColumnAndRow(1, $row, $location->company);
            $sheet->setCellValueByColumnAndRow(2, $row, $rowData["user"]->username);
            $sheet->setCellValueByColumnAndRow(3, $row, "VS".$rowData["presentAlias"]);
            $sheet->setCellValueByColumnAndRow(4, $row, $rowData["presentName"]);
            $sheet->setCellValueByColumnAndRow(5, $row, $rowData["presentModel"]);
            $sheet->setCellValueByColumnAndRow(6, $row, $this->getName($rowData["user_id"]));
            $sheet->setCellValueByColumnAndRow(7, $row, $this->getEmail($rowData["user_id"]));
            $sheet->setCellValueByColumnAndRow(8, $row, $this->shop->end_date->format("Y-m-d"));

            if($this->indpakValue != "") {
                $sheet->setCellValueByColumnAndRow(9, $row, $this->indpakValue);
            }
            if($this->specialValue != "") {
                $sheet->setCellValueByColumnAndRow(10, $row,$this->specialValue);
            }

            $row++;

        }


        $this->csReportRow = $row;


    }



    private function createCSPlulisteSheet() {

        $sheet = $this->objPHPExcel->createSheet();
        $this->objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->objPHPExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->setTitle("Produktliste");
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(19);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(45);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->setCellValueByColumnAndRow(1, 1, "Gavenr.");
        $sheet->setCellValueByColumnAndRow(2, 1, "Varenr.");
        $sheet->setCellValueByColumnAndRow(3, 1, "Varenr sampak");
        $sheet->setCellValueByColumnAndRow(4, 1, "Gavenavn");
        $sheet->setCellValueByColumnAndRow(5, 1, "Gavemodel");
        $sheet->setCellValueByColumnAndRow(6, 1, "Antal valgt");
        $sheet->getStyle("A1:H1")->getFont()->setBold(true);

        $row = 2;
        $sheet->setCellValueByColumnAndRow(1, $row, "IKKE IMPLEMENTERET I VALGSHOP!");

    }



    /**
     * FORFDELINGSRAPPORT
     */

    private function generateFordelingsRapport()
    {

        // Compile order list
        $rows = $this->compileOrderList("location");

        $this->objPHPExcel->removeSheetByIndex(0);
        $this->addWarningSheet();

        // Make list for correct order
        $locationOrderedList = array();
        foreach($this->locationMap as $location) {
            $locationOrderedList[] = array("location" => $location->string, "order" => $location->sortOrder);
        }

        for($i=0;$i<count($locationOrderedList);$i++) {
            for($j=$i+1;$j<count($locationOrderedList);$j++) {
                if($locationOrderedList[$i]["order"] > $locationOrderedList[$j]["order"]) {
                    $tmp = $locationOrderedList[$i];
                    $locationOrderedList[$i] = $locationOrderedList[$j];
                    $locationOrderedList[$j] = $tmp;
                }
            }
        }

        $processedLocations = array();

        foreach($rows as $locationName => $list)
        {

            if(!in_array($locationName,$processedLocations)) {
                $location = reportLocation::getByString($locationName,$this->company);
                if(!$location->valid)
                {
                    $processedLocations[] = $locationName;
                    $this->handleSheet(null,$list);
                    if(trimgf($locationName) != "") $this->addWarning("Kunne ikke finde lokationen: ".$locationName);
                }
            }
        }

        foreach ($locationOrderedList as $orderedLocation) {

            $locationName = $orderedLocation["location"];
            if(!in_array($locationName,$processedLocations)) {

                if (isset($rows[$locationName])) {

                    $list = $rows[$locationName];
                    $processedLocations[] = $locationName;

                    $location = reportLocation::getByString($locationName, $this->company);
                    if ($location->valid) {
                        $this->handleSheet($location, $list);
                    }

                }
            }

        }

//        echo "<pre>".print_r($processedLocations,true)."</pre>"; exit();
        //     echo "<pre>".print_r($rows,true)."</pre>"; exit();


        foreach($rows as $locationName => $list)
        {

            if(!in_array($locationName,$processedLocations)) {
                $processedLocations[] = $locationName;
                $location = reportLocation::getByString($locationName,$this->company);
                if($location->valid)
                {
                    $this->handleSheet($location,$list);
                }
            }
        }

        if(isset($_GET["debug"]) && $_GET["debug"] == 1) exit();

        // Add warnings
        $this->writeWarningSheet();
        $content = ob_get_contents();
        ob_end_clean();

        $this->objPHPExcel->setActiveSheetIndex(0);
        if($this->supressHeaders) {
            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
            $objWriter->save("php://output");
        } else {
            $this->save($this->shop->name . "_" . ($this->labels == false ? "fordelingsrapport" : "labelliste") . "_" . date("d-m-Y"));
        }
    }

    private $warningSheet = null;

    private function addWarningSheet()
    {

        $this->warningSheet = $this->objPHPExcel->createSheet();
        $this->warningSheet->setTitle("Warnings");

    }

    private function writeWarningSheet()
    {
        if(count($this->warningList) == 0) {
            $this->objPHPExcel->removeSheetByIndex(0);
        } else {
            foreach($this->warningList as $index => $warning)
                $this->warningSheet->setCellValueByColumnAndRow(1,$index+1,($warning));
        }
    }


    private $usedNames = array();
    private $sameSheet = false;
    private $sameSheetRow = 1;
    private $sameSheetObj = null;
    private $labels = false;
    private $presentAliasFilter = null;

    private function handleSheet($location,$list)
    {

        /*
          echo "<h1>Handle location: ".$location->string."</h1><br>";
          echo $location->company."<br>";
          echo $location->adress."<br>";
          echo $location->zipcity."<br>";
          echo $location->country."<br>";
          echo "<div><table>";
          */

        if($location == null)
        {
            $location = new reportLocation("",$this->company);
            $title = "Mangler lokation";
        }
        else
        {
            $title = str_replace(array("*",":","/","\\","?","[","]"), '', $location->string);
        }

        // Create sheet
        $title = trimgf($title);
        $title = substr($title,0,20);
        $title = preg_replace('/[^'.utf8_encode("aoaAOA").'0-9a-zA-Z_\s]/', '', $title);

        //$invalidCharacters = array('*', ':', '/', '\\', '?', '[', ']',',','.');
        //$title = str_replace($invalidCharacters, '', $title);

        if(trimgf($title) == "") $title = "Ukendt";

        if(isset($this->usedNames[strtolower($title)])) {
            $sheetTitle = substr($title,0,17) . "(".$this->usedNames[strtolower($title)].")";
            $this->usedNames[strtolower($title)]++;
        } else {
            $this->usedNames[strtolower($title)] = 1;
            $sheetTitle = $title;
        }



        if(isset($_GET["debug"]) && $_GET["debug"] == 1) {
            echo "ADD SHEET: ".$sheetTitle." - ".$title."<br>";
        }

        $createSheet = true;
        if($this->sameSheet == true && $this->sameSheetObj != null) $createSheet = false;

        if($createSheet == true) {

            $row = 1;

            $sheet = $this->objPHPExcel->createSheet();
            $sheet->setTitle($sheetTitle);
            //echo $sheetTitle."<br>";

            // Set col widths
            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension('B')->setWidth(26);
            $sheet->getColumnDimension('C')->setWidth(32);
            $sheet->getColumnDimension('D')->setWidth(54);
            $sheet->getColumnDimension('E')->setWidth(29);
            $sheet->getColumnDimension('F')->setWidth(27);
            $sheet->getColumnDimension('G')->setWidth(9);
            $sheet->getColumnDimension('H')->setWidth(9);
            $sheet->getColumnDimension('I')->setWidth(9);
            $sheet->getColumnDimension('J')->setWidth(9);
            $sheet->getColumnDimension('K')->setWidth(9);
            $sheet->getColumnDimension('L')->setWidth(9);
            $sheet->getColumnDimension('M')->setWidth(9);
            $sheet->getColumnDimension('N')->setWidth(9);
            $sheet->getColumnDimension('O')->setWidth(9);
            $sheet->getColumnDimension('P')->setWidth(9);
            $sheet->getColumnDimension('Q')->setWidth(9);
            $sheet->getColumnDimension('R')->setWidth(9);
            $sheet->getColumnDimension('S')->setWidth(9);
            $sheet->getColumnDimension('T')->setWidth(9);

            $this->sameSheetObj = $sheet;
        }
        else
        {
            $sheet = $this->sameSheetObj;
            $row = $this->sameSheetRow + ($this->labels ? 1 : 4);
        }

        if($this->labels == false) {
            // Write adress header
            $sheet->setCellValueByColumnAndRow(1, $row+2, "Leveringsadresse");
            $sheet->setCellValueByColumnAndRow(1, $row+3, "Attention");
            $sheet->setCellValueByColumnAndRow(3, $row+3, "Telefon");
            $sheet->setCellValueByColumnAndRow(1, $row+4, "Firmanavn");
            $sheet->setCellValueByColumnAndRow(1, $row+5, "Adresse");
            $sheet->setCellValueByColumnAndRow(1, $row+6, "Postnummer / by");
            $sheet->setCellValueByColumnAndRow(1, $row+7, "Land");


            $sheet->setCellValueByColumnAndRow(2,$row+2,$location->string);
            $sheet->setCellValueByColumnAndRow(2,$row+3,$location->att);
            $sheet->setCellValueByColumnAndRow(4,$row+3,$location->phone);
            $sheet->setCellValueByColumnAndRow(2,$row+4,$location->company);
            $sheet->setCellValueByColumnAndRow(2,$row+5,$location->adress);
            $sheet->setCellValueByColumnAndRow(2,$row+6,$location->zipcity);
            $sheet->setCellValueByColumnAndRow(2,$row+7,$location->country);


            // Style adress header

            $BorderBox = array(
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    )
                )
            );
            $BorderSides = array(
                'borders' => array(
                    'left' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    )  ,
                    'right' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    )
                )
            );

            $BorderTopBottom = array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    )  ,
                    'bottom' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    )
                )
            );

            $BackWhite = array(
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('argb' => 'FFFFFFFF')
                )
            );

            $BackBlue = array(
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('argb' => 'FFE9F0F2')
                )
            );

            $BackRed = array(
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => array('argb' => 'FFFFA0A0')
                )
            );

            $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5+($this->attrUseEmail ? 1 : 0)+count($this->extraAttributes));

            $sheet->getStyle('A'.($row+2).':'.$colString.''.($row+7).'')->applyFromArray($BorderBox);
            $sheet->getStyle('A'.($row+2).':B'.($row+7).'')->getFont()->setBold(true);
            // BACKGROUND
            $sheet->getStyle('A'.($row+2).':'.$colString.''.($row+2).'')->applyFromArray($BackWhite);
            $sheet->getStyle('A'.($row+4).':'.$colString.''.($row+4).'')->applyFromArray($BackWhite);
            $sheet->getStyle('A'.($row+6).':'.$colString.''.($row+6).'')->applyFromArray($BackWhite);

            $sheet->getStyle('A'.($row+3).':'.$colString.''.($row+3).'')->applyFromArray($BackBlue);
            $sheet->getStyle('A'.($row+5).':'.$colString.''.($row+5).'')->applyFromArray($BackBlue);
            $sheet->getStyle('A'.($row+7).':'.$colString.''.($row+7).'')->applyFromArray($BackBlue);

            // Write person header
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++,$row+9,"Navn");
            if($this->attrUseEmail) $sheet->setCellValueByColumnAndRow($col++,$row+9,"Email");
            $sheet->setCellValueByColumnAndRow($col++,$row+9,"Leveringsadresse");
            foreach($this->extraAttributes as $index => $ea)
            {
                $sheet->setCellValueByColumnAndRow($col++,$row+9,$ea["name"]);
            }
            $sheet->setCellValueByColumnAndRow($col++,$row+9,"Produkt");
            $sheet->setCellValueByColumnAndRow($col++,$row+9,"Model");
            $sheet->setCellValueByColumnAndRow($col++,$row+9,"Gave nr.");


            // Write person header style

            $sheet->getStyle('A'.($row+9).':'.$colString.($row+9))->applyFromArray($BorderSides);
            $sheet->getStyle('A'.($row+9).':'.$colString.($row+9))->applyFromArray($BorderTopBottom);
            $sheet->getStyle('A'.($row+9).':'.$colString.($row+9))->getFont()->setBold(true);

            // BOLD
            $rowIndex = $row+10;

        }
        else {
            $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(5+($this->attrUseEmail ? 1 : 0)+count($this->extraAttributes));
            $rowIndex = $row;
        }

        // Reorder list by alias
        for($i = 0; $i < countgf($list); $i++)
        {
            for($j = $i+1; $j < countgf($list); $j++)
            {
                if(intval($list[$i]["presentAlias"]) > intval($list[$j]["presentAlias"]) || (intval($list[$i]["presentAlias"]) == intval($list[$j]["presentAlias"]) && strcmp ( $list[$i]["presentAlias"],$list[$j]["presentAlias"] ) > 0))
                {
                    $tmp = $list[$i];
                    $list[$i] = $list[$j];
                    $list[$j] = $tmp;
                }
            }
        }

        if($this->labels == true) {

            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(50);
            $sheet->getColumnDimension('C')->setWidth(16);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(16);
            $sheet->getColumnDimension('F')->setWidth(27);

            if($this->useItemNo) {
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, "Vare nr");
            } else {
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, "Gave nr");
            }

            $sheet->setCellValueByColumnAndRow(2, $rowIndex, "Gave navn");
            $sheet->setCellValueByColumnAndRow(3, $rowIndex, "Farve/Variant");
            $sheet->setCellValueByColumnAndRow(4, $rowIndex, "Navn");
            $sheet->setCellValueByColumnAndRow(5, $rowIndex, "Firmanavn");
            $sheet->setCellValueByColumnAndRow(6, $rowIndex, "Fordeling efter fx adresse, afd. Eller leder");
            $rowIndex++;

        }

        $counter = 0;
        foreach($list as $index => $row)
        {

            // Write row data
            $col = 1;

            if($this->showVarenr(isset($row["varenr"]) ? $row["varenr"]:"")) {
                if ($this->presentAliasFilter == null || $this->presentAliasFilter == $row["presentAlias"]) {

                    // Style row data
                    if ($this->labels == false) {

                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $this->getName($row["user_id"]));
                        if ($this->attrUseEmail) $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $this->getEmail($row["user_id"]));
                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $location->string);
                        foreach ($this->extraAttributes as $index => $ea) {
                            $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $this->getUserAttribute($row["user_id"], $ea["id"]));
                        }
                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row["presentName"]);
                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row["presentModel"]);
                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, "Gave nr. " . $row["presentAlias"]);


                        $sheet->getStyle('A' . $rowIndex . ':' . $colString . '' . $rowIndex)->applyFromArray($BorderSides);
                        /*
                                  echo $this->getName($row["user_id"])."<br><pre>";
                                  var_dump(array("closeDate" =>$this->shop->close_date,"timestamp" => $row["timestamp"])); exit();
                                  echo "</pre>";
                        */
                        if ($this->shop->close_date != null && $row["timestamp"] > $this->shop->close_date) {
                            $sheet->getStyle('A' . $rowIndex . ':' . $colString . '' . $rowIndex . '')->applyFromArray($BackRed);
                        } else {
                            $sheet->getStyle('A' . $rowIndex . ':' . $colString . '' . $rowIndex . '')->applyFromArray($counter % 2 == 0 ? $BackWhite : $BackBlue);
                        }

                        /*
                                    $sheet->getStyle('A'.$rowIndex.':'.$colString.$rowIndex)->getFill()
                                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                        ->getStartColor()->setARGB('ffa0a0');
                        */
                    } else {

                        //$col--;
                        if ($this->useItemNo) {
                            $sheet->setCellValueByColumnAndRow($col++, $rowIndex, isset($row["varenr"]) ? $row["varenr"] : "Ukendt varenr");
                        } else {
                            $sheet->setCellValueByColumnAndRow($col++, $rowIndex, "Gave nr. " . $row["presentAlias"]);
                        }

                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row["presentName"]);
                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $row["presentModel"]);

                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $this->getName($row["user_id"]));
                        if ($this->attrUseEmail) $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $this->getEmail($row["user_id"]));
                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $location->company);

                        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $location->adress.", ".$location->att);


                        foreach ($this->extraAttributes as $index => $ea) {
                            $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $this->getUserAttribute($row["user_id"], $ea["id"]));
                        }


                    }

                    // Increment row
                    $counter++;
                    $rowIndex++;

                }
            }
        }

        $this->sameSheetRow = $rowIndex;
        //echo "</table></div>";

    }

    /**
     * ERROR / WARNING FUNCTIONALITY
     */

    private function setError($error)
    {
        $this->error = $error;
        return false;
    }

    private function addWarning($message)
    {


        if($message == false || $message == null) return;
        if(trimgf("".$message) != "" && strlen(trimgf("".$message)) > 1)
            $this->warningList[] = $message;

        if(isset($_GET["debug"]) && $_GET["debug"] == "1")
        {
            echo "WARNING: ".$message; exit();
        }
    }

    /**
     * LOADING DATA
     */

    private $company;
    private $unknownPresents;
    private $unknownMap;
    private $extraAttributes = array();
    private $modelMap2 = array();
    private $modelMapName = array();

    private $selectedAttributes = array();
    private $primaryAddress = null;

    private $indpakValue = "";
    private $specialValue = "";


    private function loadData($shopID)
    {

        $this->warningList = array();

        // LOAD BASIC

        // LOAD SHOP
        $this->shop = shop::readShop(intval($shopID));
        if(!($this->shop instanceof shop) || $this->shop->id == 0) return $this->setError("Kunne ikke finde shop");

        // Check for partial date
        if($this->shop->partial_delivery == 1) {
            $this->usePartial = true;
            if(isset($_GET["partial_date"]) && trim($_GET["partial_date"]) != "") {

                $this->partialDate = $_GET["partial_date"];

            } else {

                $lastDate = ShopUser::find_by_sql("SELECT delivery_print_date FROM `shop_user` WHERE `shop_id` = ".$this->shop->id." group by delivery_print_date order by delivery_print_date desc");
                if($lastDate != null && count($lastDate) > 0 && $lastDate[0]->delivery_print_date != null) {
                    $this->partialDate = $lastDate[0]->delivery_print_date->format('d-m-Y H:m:s');
                }

                if($this->partialDate == null) {
                    $this->usePartial = false;
                }

            }
        } else {
            if($this->isPrivatlev || $this->isPrivatlevSum) {
                echo "Kan ikke hente privatleveringsliste til denne shop.";
                exit();
            }
        }

        // LOAD COMPANY
        $companyList = Company::find_by_sql("SELECT company.* FROM company, company_shop WHERE company_shop.shop_id = ".intval($this->shop->id)." AND company_shop.company_id = company.id");
        if(count($companyList) > 0 ) $this->company = $companyList[0];

        // Check adress choice
        //if($this->shop->location_type == 0) return $this->setError("Der er ikke foretaget et lokations/adresse valg pa denne shop.");

        // LOAD USERS
        $this->userMap = array();
        $users = ShopUser::all(array('shop_id' => $this->shop->id));
        foreach($users as $user) {

            if($user->blocked == 0) {
                if ($this->usePartial) {
                    if ($user->delivery_print_date != null && $this->partialDate == $user->delivery_print_date->format('d-m-Y H:m:s')) {
                        $this->userMap[$user->id] = $user;
                    }
                } else {
                    $this->userMap[$user->id] = $user;
                }
            }
        }

        // Load user attributes and process them
        //$this->attributeList = ShopAttribute::all(array('shop_id' => $this->shop->id));
        $this->attributeList = ShopAttribute::all(array('conditions' => array('shop_id' => $this->shop->id), 'order' => '`index` asc'));
        $this->processAttributes();

        // Update selected attributes
        if(isset($_GET["uea"]) && !$this->supressHeaders)
        {
            $this->shop->report_attributes = implode(",",$this->selectedAttributes);
            $this->shop->save();
            System::connection()->commit();
        }

        //echo "<pre>".print_r($this->extraAttributes,true)."</pre>"; exit();

        // Load user data
        $dataList = UserAttribute::find('all',array('conditions' => array('shop_id' => $this->shop->id)));
        $this->userData = array();
        foreach($dataList as $ua) $this->userData[$ua->shopuser_id][$ua->attribute_id] = $ua->attribute_value;

        // Load locations
        $addressList = ShopAddress::find('all', array('conditions' => "shop_id = ".intval($this->shop->id)));
        $this->locationMap = array();
        foreach($addressList as $address)
        {
            if($this->primaryAddress == null)  $this->primaryAddress = new reportLocation($address,$this->company);
            if(trimgf($address->locations) != "")
            {
                $lines = explode("\n",$address->locations);
                for($i=0;$i<count($lines);$i++)
                {
                    if(trimgf($lines[$i]) != "")
                    {
                        if(isset($this->locationMap[trimgf($lines[$i])])) $this->addWarning("Lokationen ".$lines[$i]." er tilknyttet mere end 1 adresse");
                        $this->locationMap[trimgf($lines[$i])] = new reportLocation($address,$this->company);
                    }
                }
            }
        }

        // Load presents
        $sortOrder = "DESC";
        if($this->shop->id == 460) $sortOrder = "ASC";

        $this->presentMap = array();
        $presentList = Present::find_by_sql("SELECT * FROM `present` WHERE shop_id = ".intval($this->shop->id)." ORDER BY id ".$sortOrder);
        $presentIDList = array();
        foreach($presentList as $present)
        {
            $this->presentMap[$present->id] = $present;
            $presentIDList[] = $present->id;
        }

        // Load present models
        $this->modelMap = array();
        $this->modelMap2 = array();

        if(count($presentIDList) == 0) { echo "Der er ikke tilknyttet nogen gaver til shoppen!"; return false; }

        $modelList = PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE present_id IN (".implode(",",$presentIDList).") ORDER BY language_id DESC");
        foreach($modelList as $model)
        {
            if($model->language_id == 1)
            {
                $modelNameList = array();
                $this->modelMap[$model->present_id][$model->model_id] = $model;
            }

            // Add model id's to map
            if(!isset($this->modelMap2[$model->model_id])) $this->modelMap2[$model->model_id] = array();
            $this->modelMap2[$model->model_id][] = $model->id;

        }

        if(isset($_GET["debug"]) && $_GET["debug"] == "1")
        {
            /*
                           echo "Debug output";
              echo "<pre>";
              print_r($this->presentMap);
              echo "</pre>";
              exit();
              */
        }

        // Load indpak and speciallevering
        $shopMetadata = \ShopMetadata::find_by_sql("select * from shop_metadata where shop_id = ".$this->shop->id);
        if(count($shopMetadata) > 0) {
            $shopMetadata = $shopMetadata[0];

            if($this->shop->localisation == 4) {

                if($shopMetadata->present_wrap == 1) {
                    $this->indpakValue = "Ja";
                } else {
                    $this->indpakValue = "Nei";
                }

                if($shopMetadata->handling_special == 1) {
                    $this->specialValue = "Ja";
                } else {
                    $this->specialValue = "Nei";
                }
                
            }

        }


        // Load orders
        $this->orderList = Order::all(array('shop_id' => $this->shop->id,"is_demo" => 0));
        $this->sumData = array();
        return true;

    }


    /**
     * COMPILE DATA LISTS
     */

    private function compileOrderList($split=null)
    {

        $this->sumData = array();
        $dataList = array();
        $processedUsers = array();

        foreach($this->orderList as $order)
        {

            if($this->debug) echo "PROCESS ORDER:<br>";

            $hasErrors = false;
            $processedUsers[] = $order->shopuser_id;
            $pml = array();
            $alias = "";

            // Check user
            if(!isset($this->userMap[$order->shopuser_id]) && !$this->usePartial)
            {
                $this->addWarning("Kunne ikke finde brugeren ".$order->shopuser_id." tilknyttet ordre ".$order->id);
                $hasErrors = true;
            }

            if(isset($this->userMap[$order->shopuser_id])) {

                // Find alias
                $alias = "";
                $presentName = "";
                $presentModel = "";
                $varenr = "";

                if(!isset($this->presentMap[$order->present_id]))
                {
                    $this->addWarning("Kunne ikke finde gaven med id ".$order->present_id." som er tilknyttet ordre ".$order->id);
                    $hasErrors = true;
                }

                else
                {
                    $present = $this->presentMap[$order->present_id];
                    $presentName = $present->nav_name;
                    $model_id = $order->present_model_id;

                    // NO ALIAS JSON DEFINED
                    if(intval($present->alias) == 0)
                    {
                        $this->addWarning("Kunne ikke finde gavealias pa gaven ".$present->name." (".$present->id.") som er tilknyttet ordre ".$order->id);
                        $hasErrors = true;
                    }

                    // ALIAS DEFINED
                    else
                    {
                        $alias = $present->alias;
                        if(!isset($this->modelMap[$present->id]) || !isset($this->modelMap[$present->id][$model_id]))
                        {
                            $this->addWarning("Kunne ikke finde model ".$model_id." pa gaven ".$present->name." (".$present->id.") som er tilknyttet ordre ".$order->id);
                            $hasErrors = true;
                        }
                        else
                        {
                            $model = $this->modelMap[$present->id][$model_id];
                            if(trimgf($model->fullalias) == "")
                            {
                                $this->addWarning("Kunne ikke finde gavealias pa model ".$model_id." til gaven ".$present->name." (".$present->id.") som er tilknyttet ordre ".$order->id);
                                $hasErrors = true;
                            }
                            else
                            {
                                $alias = $model->fullalias;
                                $varenr = $model->model_present_no;

                                $name = array();
                                if(trimgf($model->model_name) != "") $name[] = $model->model_name;
                                if(trimgf($model->model_no) != "") $name[] = $model->model_no;

                                if(count($name) == 2)
                                {
                                    $presentName = $name[0];
                                    $presentModel = $name[1];
                                }
                                else $presentName = implode(", ",$name);

                            }
                        }




                    }

                }

            }

            $alias = strtolower($alias);


            // Find location
            $location = $this->userLocation($order->shopuser_id);
            if($location == null) $hasErrors = true;

            // If no problems, add to list
            if($hasErrors == false && isset($this->userMap[$order->shopuser_id]))
            {

                if(trimgf($alias) == "") {
                    $this->addWarning("Gaven ".$presentName." ".$presentModel." mangler alias.");
                }

                // Compile data
                $row = array(
                    "user_id" => $order->shopuser_id,
                    "order" => $order,
                    "user" => $this->userMap[$order->shopuser_id],
                    "presentAlias" => strtolower($alias),
                    "presentName" => $presentName,
                    "presentModel" => $presentModel,
                    "location" => $location,
                    "modelParts" => countgf($pml),
                    "timestamp" => $order->order_timestamp,
                    "varenr" => $varenr
                );

                // Add to sum
                if($this->isSumList)
                {

                    $description = $presentName.", ".$presentModel;
                    //if($row["modelParts"] == 0) $description = $presentName;
                    //else if($row["modelParts"] == 2) $description = $presentModel;

                    // Add to sum
                    if(!isset($this->sumData[$alias])) $this->sumData[$alias] = array("count" => 0,"varenr" => $varenr,"description" => $description,"locationmap" => array());
                    $this->sumData[$alias]["count"]++;

                    if(!isset($this->sumData[$alias]["locationmap"][$location->string]))  $this->sumData[$alias]["locationmap"][$location->string] = 0;
                    $this->sumData[$alias]["locationmap"][$location->string]++;
                }

                // Add to location
                if($split == "location") {
                    $dataList[$location->string][] = $row;
                }
                else $dataList[] = $row;

            }

        }

        foreach($this->userMap as $userid => $user)
        {

            $hasErrors = false;

            if(!in_array($userid,$processedUsers) && $user->is_demo == 0)
            {

                if($this->debug) echo "PROCESS USER:<br>";

                $location = $this->userLocation($userid);
                if($location == null) $hasErrors = true;

                // If no problems, add to list
                if($hasErrors == false)
                {
                    $alias = "00";
                    $presentName = "IKKE VALGT";
                    $presentModel = "";

                    // Compile data
                    $row = array(
                        "user_id" => $userid,
                        "order" => null,
                        "user" => $user,
                        "presentAlias" => strtolower($alias),
                        "presentName" => $presentName,
                        "presentModel" => $presentModel,
                        "location" => $location,
                        "modelParts" => 0,
                        "timestamp" => null
                    );

                    // Add to sum
                    if($this->isSumList)
                    {
                        $description = $presentName.", ".$presentModel;
                        if($row["modelParts"] == 0) $description = $presentName;
                        else if($row["modelParts"] == 2) $description = $presentModel;

                        // Add to sum
                        if(!isset($this->sumData[$alias])) $this->sumData[$alias] = array("count" => 0,"varenr" => "","description" => $description,"locationmap" => array());
                        $this->sumData[$alias]["count"]++;

                        if(!isset($this->sumData[$alias]["locationmap"][$location->string]))  $this->sumData[$alias]["locationmap"][$location->string] = 0;
                        $this->sumData[$alias]["locationmap"][$location->string]++;
                    }

                    // Add to location
                    if($split == "location") $dataList[$location->string][] = $row;
                    else $dataList[] = $row;

                }
            }
        }

        if($this->isSumList)
        {
            ksort($this->sumData);
        }

        return $dataList;

    }


    /**
     * ADRESS / LOCATION FUNCTIONALITY
     */

    private function userLocation($userid)
    {
        // Location from dropdown
        if($this->shop->location_type == 1)
        {
            $locAttr = $this->getUserAttribute($userid,$this->shop->location_attribute_id);
            if(isset($this->locationMap[$locAttr]))
            {
                return $this->locationMap[$locAttr];
            }
            else
            {
                //if(trimgf($locAttr) == "") $this->addWarning("Ingen lokation angivet for: bruger ".$this->getName($userid)." -".$userid." - type 1.");
                //else $this->addWarning("Der findes ikke en adresse for lokationen: ".$locAttr." (bruger ".$this->getName($userid)." -".$userid." - type 1).");
                return new reportLocation("",$this->company);
            }
        }
        // Location from freetext
        else if($this->shop->location_type == 2)
        {
            $locAttr = $this->getUserAttribute($userid,$this->shop->location_attribute_id);
            if(isset($this->locationMap[$locAttr]))
            {
                return $this->locationMap[$locAttr];
            }
            else
            {
                //if(trimgf($locAttr) == "") $this->addWarning("Ingen lokation angivet for: bruger ".$this->getName($userid)." -".$userid." - type 2.");
                //else $this->addWarning("Der findes ikke en adresse for lokationen: ".$locAttr." (bruger ".$this->getName($userid)." -".$userid." - type 2).");
                return new reportLocation("",$this->company);
            }

        }
        // Location from text
        else if($this->shop->location_type == 3)
        {
            $location = $this->getUserAttribute($userid,$this->shop->location_attribute_id);
            //if($location == "") $this->addWarning("Brugeren ".$this->getName($userid)." (".$userid.") har ikke nogen lokation tilknyttet (type 3).");
            return new reportLocation($location,$this->company);
        }
        else if($this->shop->location_type == 4)
        {
            return $this->primaryAddress == null ? new reportLocation("",$this->company) : $this->primaryAddress;
            //foreach($this->locationMap as $loc) return  $loc;
            //return new reportLocation("");
        }
        else  return $this->primaryAddress == null ? new reportLocation("",$this->company) : $this->primaryAddress;
    }


    /**
     * USER ATTRIBUTE FUNCTIONALITY
     */

    private $attrUsername;
    private $attrEmail;
    private $attrName;
    private $attrUseEmail;

    private function processAttributes()
    {
        /*
          for($i = 0; $i < countgf($this->attributeList); $i++)
          {
            for($j = $i+1; $j < countgf($this->attributeList); $j++)
            {
              if($this->attributeList[$i]->index > $this->attributeList[$j]->index)
              {
                $tmp = $this->attributeList[$i];
                $this->attributeList[$i] = $this->attributeList[$j];
                $this->attributeList[$j] = $tmp;
              }
            }
          }
          */



        // Prepare input
        $attributeIDs = explode(",",trimgf(isset($_GET["uea"]) ? $_GET["uea"] : ""));

        $this->selectedAttributes = array();
        foreach($attributeIDs as $id)
        {
            if(intval($id) > 0) $this->selectedAttributes[] = intval($id);
        }

        foreach($this->attributeList as $at)
        {
            if($at->is_username == 1)
            {
                $this->attrUsername = $at->id;
                if(in_array($at->id,$attributeIDs))
                {
                    $this->extraAttributes[] = array("id" => $at->id, "name" => $at->name);
                }
            }
            else if($at->is_email == 1)
            {
                $this->attrEmail = $at->id;
                $this->attrUseEmail = in_array($at->id,$attributeIDs);
            }
            else if($at->is_name == 1) $this->attrName = $at->id;
            else {
                if(in_array($at->id,$attributeIDs))
                {
                    $this->extraAttributes[] = array("id" => $at->id, "name" => $at->name);
                }
            }
        }

    }

    protected function getUserAttribute($user_id,$attribute_id)
    {
        if($attribute_id > 0 && isset($this->userData[$user_id]) && isset($this->userData[$user_id][$attribute_id])) return $this->userData[$user_id][$attribute_id];
        else return "";
    }

    protected function getName($user_id)
    {
        if($this->attrName != null && isset($this->userData[$user_id]) && isset($this->userData[$user_id][$this->attrName]))
        {
            if($this->shop->id == 1575) {
                return $this->userData[$user_id][$this->attrName]." ".$this->getUserAttribute($user_id,10737);
            }
            return $this->userData[$user_id][$this->attrName];
        }
        else return "";
    }

    protected function getEmail($user_id)
    {
        if($this->attrName != null && isset($this->userData[$user_id]) && isset($this->userData[$user_id][$this->attrEmail])) return $this->userData[$user_id][$this->attrEmail];
        else return "";
    }

    protected function getUsername($user_id)
    {
        if($this->attrName != null && isset($this->userData[$user_id]) && isset($this->userData[$user_id][$this->attrUsername])) return $this->userData[$user_id][$this->attrUsername];
        else return "";
    }

}
