<?php

namespace GFUnit\privatedelivery\exportforeign;

use GFBiz\units\UnitController;

class Controller extends UnitController
{

    /**
     *
     * VIEW THE LISTS FIRST!
     * SELECT shipment.*, shop_user.*, shop_user.id as shopuser_id, shipment.id as shipment_id, cardshop_settings.language_code as language_code FROM shipment, shop_user, company_order, cardshop_settings where cardshop_settings.shop_id = company_order.shop_id && company_order.id = shipment.companyorder_id && shipment.shipment_type = 'privatedelivery' && shipment.shipment_state = 9 && shop_user.delivery_state = 1 && shop_user.navsync_response = shipment.id && cardshop_settings.language_code = 5
     *
     * PLEASE REVIEW LISTS AFTER A NEW PULL
     * IF SOME LANGUAGES ARE OK, PUT THEM BACK IN NORMAL POOL WITH THIS
    UPDATE shop_user set delivery_state = 1 where username in (select from_certificate_no from shipment where id in (LIST OF SHIPMENT IDS TO RESET));
    UPDATE shipment SET handler = 'glsexport', shipment_state = 1, shipment_sync_date = null where id in (LIST OF SHIPMENT IDS TO RESET)
     */

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index() {
        $this->dispatch();
    }

    public function dispatch()
    {
        if(isset($_GET["action"]) && isset($_GET["language_code"]) && intval($_GET["language_code"]) > 0 && $_GET["action"] == "createfile") {
            $this->createNewList(intval($_GET["language_code"]));
        }
        else if(isset($_GET["action"]) && isset($_GET["language_code"]) && intval($_GET["language_code"]) > 0 && $_GET["action"] == "viewdata") {
            $this->viewNewList(intval($_GET["language_code"]));
        }
        else if(isset($_GET["action"]) && isset($_GET["filedate"]) && isset($_GET["language_code"]) && intval($_GET["language_code"]) > 0 && $_GET["action"] == "labellist") {
            $this->getLabelList(intval($_GET["language_code"]),$_GET["filedate"]);
        }
        else if(isset($_GET["action"]) && isset($_GET["filedate"]) && isset($_GET["language_code"]) && intval($_GET["language_code"]) > 0 && $_GET["action"] == "sumlist") {
            $this->getSumList(intval($_GET["language_code"]),$_GET["filedate"]);
        }
        else {
            $this->showList();
        }
    }

    private function showList($message="")
    {
        $fileList = $this->getFileList();
        $waitingMap = $this->countWaiting();
        $languagemap = \Language::getLanguageMap();

        print_r($waitingMap);

        ?><h2>Privatleveringer - Udenlands leveringer</h2>
        <div><form method="post" action="">

                <?php if($message != "") echo "<div style='text-align: center; font-size: 2em;'>".$message."</div>"; ?>

                <table>
                    <tr>
                        <td style="padding: 10px;">Land</td>
                        <?php foreach($waitingMap as $language_code => $waitcount) { ?>
                            <td style="padding: 10px;"><?php echo $languagemap[$language_code]->name; ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Antal klar</td>
                        <?php foreach($waitingMap as $language_code => $waitcount) { ?>
                            <td style="padding: 10px;"><?php echo $waitcount; ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Dan fil</td>
                        <?php foreach($waitingMap as $language_code => $waitcount) { ?>
                            <td style="padding: 10px;"><?php if($waitcount > 0) { ?><button type="button" onclick="document.location='?rt=unit/privatedelivery/exportforeign/dispatch&action=createfile&language_code=<?php echo $language_code; ?>';">dan fil</button><?php } ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Se data</td>
                        <?php foreach($waitingMap as $language_code => $waitcount) { ?>
                            <td style="padding: 10px;"><?php if($waitcount > 0) { ?><button type="button" onclick="document.location='?rt=unit/privatedelivery/exportforeign/dispatch&action=viewdata&language_code=<?php echo $language_code; ?>';">se data</button><?php } ?></td>
                        <?php } ?>
                    </tr>
                </table>

            </form></div>
        <table style="width: 100%; max-width: 1000px;" cellpadding="5" cellspacing="5">
            <tr style="font-weight: bold;"><td>Shop ID</td><td>Shop name</td><td>Dato</td><td>Antal</td><td>Labelliste</td><td>Sumliste</td></tr><?php

            foreach($fileList as $file) {
                echo "<tr>
                    <td>".$file->shop_id."</td>
                    <td>".$languagemap[$file->language_code]->name."</td>
                    <td>".$file->shipment_sync_date."</td>
                    <td>".$file->shipmentcount."</td>
                    <td><a href='?rt=unit/privatedelivery/exportforeign/dispatch&action=labellist&language_code=".$file->language_code."&filedate=".$file->shipment_sync_date."'>hent labels</a></td>
                    <td><a href='?rt=unit/privatedelivery/exportforeign/dispatch&action=sumlist&language_code=".$file->language_code."&filedate=".$file->shipment_sync_date."'>hent sumliste</a></td>
                </tr>";
            }
            ?></table>
        <?php

    }

    private function viewNewList($language_code)
    {

        $notProcessed = $this->getWaiting($language_code,true);
        if(count($notProcessed) == 0) return $this->showList("<span style='color: red;'>Der er ikke nogle nye valg i det valgte land.</span>");

        // Navsync identifier
        $pullDate = time();
        $count = 0;

        echo "<table style='width: 100%;'>";

        echo "<tr>
<td>ship id</td>
<td>username</td>
<td>bs nr</td>
<td>company</td>
<td>shop</td>
<td>created</td>
<td>itemno</td>
<td>navn</td>
<td>adresse</td>
<td>adresse 2</td>
<td>postnr</td>
<td>by</td>
<td>land</td>
<td>email</td>
<td>telefon</td>

</tr>";

        // Foreach, find by id and set navsync_response and delivery_print_date
        foreach($notProcessed as $shopuserRow) {

            $shopuser = \ShopUser::find($shopuserRow->shopuser_id);
            $shipment = \Shipment::find($shopuserRow->shipment_id);

            echo "<tr>";

            if($shopuser instanceof \ShopUser && $shopuser->id > 0 && $shopuser->delivery_state == 1) {
                if($shipment instanceof \Shipment && $shipment->id > 0 && $shipment->shipment_state == 9 && $shipment->from_certificate_no == $shopuser->username) {

                    $companyorder = \CompanyOrder::find($shopuser->company_order_id);

                    $rowData = array(
                        $shopuser->id, $shopuser->username,
                        $companyorder->order_no, $companyorder->company_name,
                        $companyorder->shop_name,
                        $shipment->created_date->format("d-m-Y H:i"),
                        $shipment->itemno,
                        $shipment->shipto_name,
                        $shipment->shipto_address,
                        $shipment->shipto_address2,
                        $shipment->shipto_postcode,
                        $shipment->shipto_city,
                        $shipment->shipto_country,
                        $shipment->shipto_email,
                        $shipment->shipto_phone,
                    );

                    foreach($rowData as $row) {
                        echo "<td>".$row."</td>";
                    }

                }
                else {
                    echo "<td>Failed to verify shipment: ".$shopuserRow->shipment_id."</td>>";
                }

            }
            else {
                echo "<td>Failed to verify shopuser: ".$shopuserRow->shopuser_id."</td>>";
            }

            echo "</tr>";

        }
        echo "</table>";


        $this->showList("");
    }

    private function createNewList($language_code)
    {

        $notProcessed = $this->getWaiting($language_code);
        if(count($notProcessed) == 0) return $this->showList("<span style='color: red;'>Der er ikke nogle nye valg i det valgte land.</span>");

        // Navsync identifier
        $pullDate = time();
        $count = 0;

        echo "<div style='display: none;'>";

        // Foreach, find by id and set navsync_response and delivery_print_date
        foreach($notProcessed as $shopuserRow) {

            $shopuser = \ShopUser::find($shopuserRow->shopuser_id);
            $shipment = \Shipment::find($shopuserRow->shipment_id);

            if($shopuser instanceof \ShopUser && $shopuser->id > 0 && $shopuser->delivery_state == 1) {
                if($shipment instanceof \Shipment && $shipment->id > 0 && $shipment->shipment_state == 9 && $shipment->from_certificate_no == $shopuser->username) {

                    $shopuser->delivery_state = 2;
                    $shopuser->save();
                    $count++;

                    $shipment->shipment_state = 2;
                    $shipment->shipment_sync_date = date('d-m-Y H:i:s',$pullDate);
                    $shipment->handler = 'foreigngls';
                    $shipment->save();

                    //echo $shopuser->id.";".$shipment->id.";<br>";
                }
                else {
                    echo "Failed to verify shipment: ".$shopuserRow->shipment_id."<br>";
                }

            }
            else {
                echo "Failed to verify shopuser: ".$shopuserRow->shopuser_id."<br>";
            }

        }
        echo "</div>";

        if($count > 0) {
            $message = "<span style='color: red;'>Oprettede filer med " . $count . " ordre, husk at gennemse dem for fejl!</span><br>";
            \System::connection()->commit();
        }

        $this->showList($message);
    }


    private function getSumList($language_code,$file_date)
    {

        $language = \Language::find($language_code);


        ob_start();

        ?><h2>Privatlevering sumliste - <?php echo $language->name; ?></h2>
        <b>Batch: <?php echo $language_code." / ".$file_date; ?></b>
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

            $shipmentList = $this->getShipmentsInFile($language_code,$file_date);
            foreach($shipmentList as $userorder)
            {
                if (!in_array($userorder->itemno, $aliasList)) {
                    $aliasList[] = $userorder->itemno;
                    $aliasCount[$userorder->itemno] = 0;
                    $presentInfo[$userorder->itemno] = array("varenr" => $userorder->itemno, "name" => $userorder->description,"modelname" => "");
                }
                $aliasCount[$userorder->itemno]++;
            }

            natsort($aliasList);

            $totalCount = 0;
            foreach($aliasList as $fullAlias)
            {
                $present = $presentInfo[$fullAlias];
                $totalCount += $aliasCount[$fullAlias];

                ?><tr>
                <td style="padding-right: 10px;"><?php echo wordwrap($present["varenr"],21,"<br>",true); ?></td>
                <td><?php echo trimgf(utf8_decode(htmlentities($present["name"]))); ?></td>
                <td><?php echo trimgf(utf8_decode(htmlentities($present["modelname"]))); ?></td>
                <td style="text-align: right;"><?php echo $aliasCount[$fullAlias]; ?></td>
                </tr><?php
            }

            ?></tbody></table>
        <div  style=" text-align: right; margin-top: 10px;">Total antal gaver: <?php echo $totalCount; ?></div><?php

        // Finish and output
        $content = ob_get_contents();
        ob_end_clean();
        error_reporting(E_ALL ^ E_NOTICE);
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML(utf8_encode($content));
        $mpdf->Output("Sumliste-".$language->name."-".substr($file_date,0,10).".pdf","D");

        echo $content;
    }

    private function getLabelList($language_code,$file_date)
    {

        $language = \Language::find($language_code);

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
        /*
$sheet->setCellValueByColumnAndRow(1, 1, "Navn");
$sheet->setCellValueByColumnAndRow(2, 1, "E-mail");
$sheet->setCellValueByColumnAndRow(3, 1, "Mobil");
$sheet->setCellValueByColumnAndRow(4, 1, "Adresse");
$sheet->setCellValueByColumnAndRow(5, 1, "Postnr.");
$sheet->setCellValueByColumnAndRow(6, 1, "By");
$sheet->setCellValueByColumnAndRow(7, 1, "Land");
$sheet->setCellValueByColumnAndRow(8, 1, "Gave/varebeskrivelse");
$sheet->setCellValueByColumnAndRow(9, 1, "Farve/variant");
$sheet->getStyle("A1:N1")->getFont()->setBold(true);

*/


        $row=1;


        $date1 = date("Y-m-d",(time()))."-08:30:00";
        $date2 = date("Y-m-d",(time()))."-16:00:00";

        // Get shopuser orders
        $shipmentList = $this->getShipmentsInFile($language_code,$file_date);
        foreach($shipmentList as $userorder)
        {

            // Get user data
            $userData = array(
                "name" => $userorder->shipto_name,
                "address" => $userorder->shipto_address,
                "address2" => $userorder->shipto_address2,
                "postnr" => $userorder->shipto_postcode,
                "bynavn" => $userorder->shipto_city,
                "land" => $userorder->shipto_country,
                "telefon" => $userorder->shipto_phone,
                "email" => $userorder->shipto_email
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

            // Write data row
            $sheet->setCellValueByColumnAndRow(1, $row, ($userData["name"]));
            $sheet->setCellValueByColumnAndRow(2, $row, $userData["email"]);
            $sheet->setCellValueByColumnAndRow(3, $row, $phone);
            $sheet->setCellValueByColumnAndRow(4, $row, $userData["address"].((trimgf($userData["address2"]) != "" && mb_strtolower(trimgf($userData["address"])) != mb_strtolower(trimgf($userData["address2"])))? ", ".$userData["address2"] : ""));
            $sheet->setCellValueByColumnAndRow(5, $row, str_replace(" ","",$userData["postnr"]));
            $sheet->setCellValueByColumnAndRow(6, $row, $userData["bynavn"]);
            $sheet->setCellValueByColumnAndRow(7, $row, $userData["land"]);
            $sheet->setCellValueByColumnAndRow(8, $row, $userorder->itemno.": ".$userorder->description);
            $sheet->setCellValueByColumnAndRow(10,$row,$date1);
            $sheet->setCellValueByColumnAndRow(11,$row,$date2);
            $sheet->setCellValueByColumnAndRow(12,$row,$userorder->shipment_id);
            $row++;

        }

        // Output excel file
        //header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="privatlevering-'.$language->name.'-'.substr($file_date,0,10).'.csv"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);

        //$objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
        exit();
    }


    private function getWaiting($language_code,$showSQL=false) {
        $languageSQL = (intval($language_code) > 0) ? " && cardshop_settings.language_code = ".intval($language_code)."" : "";
        $sql = "SELECT shipment.*, shop_user.*, shop_user.id as shopuser_id, shipment.id as shipment_id, cardshop_settings.language_code as language_code FROM shipment, shop_user, company_order, cardshop_settings where cardshop_settings.shop_id = company_order.shop_id && company_order.id = shipment.companyorder_id && shipment.shipment_type = 'privatedelivery' && shipment.shipment_state = 9 && shop_user.delivery_state = 1 && shop_user.navsync_response = shipment.id ".$languageSQL;
        if($showSQL) echo "<br>SQL: ".$sql."<br><br>";
        return \ShopUser::find_by_sql($sql);
    }

    private function countWaiting() {
        $list = $this->getWaiting(0);
        $map = array();
        foreach($list as $shopuser) {

            if(!isset($map[$shopuser->language_code])) $map[$shopuser->language_code] = 0;
            $map[$shopuser->language_code]++;
        }
        return $map;
    }

    private function getFileList($languageid=0) {
        $languageSQL = (intval($languageid) > 0) ? " && cardshop_settings.language_code = ".intval($languageid)."" : "";
        $sql = "SELECT shipment.shipment_sync_date, shop_user.shop_id, count(shipment.id) as shipmentcount, cardshop_settings.language_code  FROM shipment, shop_user, company_order, cardshop_settings where cardshop_settings.shop_id = company_order.shop_id && company_order.id = shipment.companyorder_id && shipment.shipment_state IN (2,5) && shop_user.delivery_state = 2 && shipment.handler = 'foreigngls' && shipment.shipment_type = 'privatedelivery' && shop_user.navsync_response = shipment.id ".$languageSQL. " GROUP BY shipment.shipment_sync_date, cardshop_settings.language_code";
        return \ShopUser::find_by_sql($sql);
    }

    private function getShipmentsInFile($languageid,$filedate) {
        $languageSQL = (intval($languageid) > 0) ? " && shipment.companyorder_id in (select id from company_order where shop_id in (select shop_id from cardshop_settings where language_code = ".intval($languageid)."))" : "";
        $dateSQL = " && shipment.shipment_sync_date = '$filedate' ";
        $sql = "SELECT shipment.*, shop_user.*, shipment.id as shipment_id FROM shipment, shop_user where shipment.shipment_state IN (2,5) && shop_user.delivery_state = 2 && shipment.handler = 'foreigngls' && shipment.shipment_type = 'privatedelivery' && navsync_response = shipment.id ".$languageSQL.$dateSQL ."";
        return \ShopUser::find_by_sql($sql);
    }



}