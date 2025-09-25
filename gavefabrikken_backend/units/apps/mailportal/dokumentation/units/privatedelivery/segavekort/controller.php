<?php

namespace GFUnit\privatedelivery\segavekort;

use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index() {
        $this->dispatch();
    }

    public function seordre() {


        // Virksomheds ordre liste til sverige
        $dayOfWeek = date("w");
        $dayOffset = ($dayOfWeek <= 5 ? -1*($dayOfWeek+1) : 0);
        //$dayOffset = -365;
        $endDate = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $startDate = mktime(0,0,0,date("m"),date("d")+$dayOffset-7,date("Y"));
        //$sql = "SELECT company.name as Virksomhed, company.cvr as CVR, company_order.shop_name as Korttype, company_order.quantity as Antal FROM `company_order`, company where company_order.created_datetime >= '".date("Y-m-d H:i:s",$startDate)."' && company_order.created_datetime < '".date("Y-m-d H:i:s",$endDate)."' && company.id = company_order.company_id && company_order.order_state not in (7,8) && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) order by company.name asc";
        $sql = "SELECT company.name as Virksomhed, company.cvr as CVR , company.contact_name, company.contact_phone, company.contact_email, company_order.shop_name as Korttype, company_order.quantity as Antal, company.bill_to_address, company.bill_to_postal_code, company.bill_to_city, company.bill_to_country, company.bill_to_email, company.ship_to_address, company.ship_to_postal_code, company.ship_to_city, company.ship_to_country FROM `company_order`, company where company_order.salesperson like 'IMPORT' && company_order.created_datetime >= '".date("Y-m-d H:i:s",$startDate)."' && company_order.created_datetime < '".date("Y-m-d H:i:s",$endDate)."' && company.id = company_order.company_id && company_order.order_state not in (1,2,3,7,8) && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) order by company.name asc";

        $results = \Dbsqli::getSql2($sql);

        //echo date("r",$startDate)." - ".date("r",$endDate); return;

        if(!is_array($results) || countgf($results) == 0) {
            echo "Ingen resultater"; exit();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=svensksalg-'.date("dm",$startDate)."-".date("dm",$endDate-1).'.csv');
        foreach($results[0] as $key => $val) {
            echo $key.";";
        }
        echo "\n";

        foreach($results as $row)
        {
            foreach($row as $key => $val) {
                echo utf8_decode(trimgf(str_replace(array("\r","\n",";"),array(""," ",""),$val)).";");
            }
            echo "\n";
        }

    }
    public function sebestilt() {


        // Virksomheds ordre liste til sverige
        $dayOfWeek = date("w");
        $dayOffset = ($dayOfWeek <= 5 ? -1*($dayOfWeek+1) : 0);
        //$dayOffset = -365;
        $endDate = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $startDate = mktime(0,0,0,date("m"),date("d")+$dayOffset-7,date("Y"));
        //$sql = "SELECT company.name as Virksomhed, company.cvr as CVR, company_order.shop_name as Korttype, company_order.quantity as Antal FROM `company_order`, company where company_order.created_datetime >= '".date("Y-m-d H:i:s",$startDate)."' && company_order.created_datetime < '".date("Y-m-d H:i:s",$endDate)."' && company.id = company_order.company_id && company_order.order_state not in (7,8) && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) order by company.name asc";
        $sql = "SELECT company.name as Virksomhed, company.cvr as CVR , company.contact_name, company.contact_phone, company.contact_email, company_order.shop_name as Korttype, company_order.quantity as Antal, company.bill_to_address, company.bill_to_postal_code, company.bill_to_city, company.bill_to_country, company.bill_to_email, company.ship_to_address, company.ship_to_postal_code, company.ship_to_city, company.ship_to_country FROM `company_order`, company where company_order.salesperson like 'IMPORT' && company_order.created_datetime >= '".date("Y-m-d H:i:s",$startDate)."' && company_order.created_datetime < '".date("Y-m-d H:i:s",$endDate)."' && company.id = company_order.company_id && company_order.order_state not in (7,8) && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) order by company.name asc";

        $results = \Dbsqli::getSql2($sql);

        //echo date("r",$startDate)." - ".date("r",$endDate); return;

        if(!is_array($results) || countgf($results) == 0) {
            echo "Ingen resultater"; exit();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=svensksalg-'.date("dm",$startDate)."-".date("dm",$endDate-1).'.csv');
        foreach($results[0] as $key => $val) {
            echo $key.";";
        }
        echo "\n";

        foreach($results as $row)
        {
            foreach($row as $key => $val) {
                echo utf8_decode(trimgf(str_replace(array("\r","\n",";"),array(""," ",""),$val)).";");
            }
            echo "\n";
        }

    }

    public function dispatch()
    {
        if(isset($_GET["action"]) && isset($_GET["shopid"]) && intval($_GET["shopid"]) > 0 && $_GET["action"] == "createfile") {
            $this->createNewList(intval($_GET["shopid"]));
        }
        else if(isset($_GET["action"]) && isset($_GET["filedate"]) && isset($_GET["shopid"]) && intval($_GET["shopid"]) > 0 && $_GET["action"] == "labellist") {
            $this->getLabelList(intval($_GET["shopid"]),$_GET["filedate"]);
        }
        else if(isset($_GET["action"]) && isset($_GET["filedate"]) && isset($_GET["shopid"]) && intval($_GET["shopid"]) > 0 && $_GET["action"] == "sumlist") {
            $this->getSumList(intval($_GET["shopid"]),$_GET["filedate"]);
        }
        else {
            $this->showList();
        }
    }

    private function showList($message="")
    {
        $fileList = $this->getFileList();
        $waitingMap = $this->countWaiting();
        $shopmap = \CardshopSettings::getShopIDMap();

        ?><h2>Privatleveringer - Sverige</h2>
        <div><form method="post" action="">

                <?php if($message != "") echo "<div style='text-align: center; font-size: 2em;'>".$message."</div>"; ?>

                <table>
                    <tr>
                        <td style="padding: 10px;">Shop</td>
                        <?php foreach($waitingMap as $shopid => $waitcount) { ?>
                            <td style="padding: 10px;"><?php echo $shopmap[$shopid]->concept_code; ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Antal klar</td>
                        <?php foreach($waitingMap as $shopid => $waitcount) { ?>
                            <td style="padding: 10px;"><?php echo $waitcount; ?></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td style="padding: 10px;">Dan fil</td>
                        <?php foreach($waitingMap as $shopid => $waitcount) { ?>
                            <td style="padding: 10px;"><?php if($waitcount > 0) { ?><button type="button" onclick="document.location='?rt=unit/privatedelivery/segavekort/dispatch&action=createfile&shopid=<?php echo $shopid; ?>';">dan fil</button><?php } ?></td>
                        <?php } ?>
                    </tr>
                </table>

            </form></div>
        <table style="width: 100%; max-width: 1000px;" cellpadding="5" cellspacing="5">
            <tr style="font-weight: bold;"><td>Shop ID</td><td>Shop name</td><td>Dato</td><td>Antal</td><td>Labelliste</td><td>Sumliste</td></tr><?php

            foreach($fileList as $file) {
                echo "<tr>
                    <td>".$file->shop_id."</td>
                    <td>".$shopmap[$file->shop_id]->concept_code."</td>
                    <td>".$file->shipment_sync_date."</td>
                    <td>".$file->shipmentcount."</td>
                    <td><a href='?rt=unit/privatedelivery/segavekort/dispatch&action=labellist&shopid=".$file->shop_id."&filedate=".$file->shipment_sync_date."'>hent labels</a></td>
                    <td><a href='?rt=unit/privatedelivery/segavekort/dispatch&action=sumlist&shopid=".$file->shop_id."&filedate=".$file->shipment_sync_date."'>hent sumliste</a></td>
                    
                </tr>";
            }
            ?></table>
        <?php

    }

    private function createNewList($shopid)
    {

        $notProcessed = $this->getWaiting($shopid);
        if(count($notProcessed) == 0) return $this->showList("<span style='color: red;'>Der er ikke nogle nye valg i den valgte shop.</span>");

        // Navsync identifier
        $pullDate = time();
        $count = 0;

        echo "<div style='display: none;'>";

        // Foreach, find by id and set navsync_response and delivery_print_date
        foreach($notProcessed as $shopuserRow) {

            $shopuser = \ShopUser::find($shopuserRow->shopuser_id);
            $shipment = \Shipment::find($shopuserRow->shipment_id);

            if($shopuser instanceof \ShopUser && $shopuser->id > 0 && $shopuser->delivery_state == 1) {
                if($shipment instanceof \Shipment && $shipment->id > 0 && $shipment->shipment_state == 1 && $shipment->from_certificate_no == $shopuser->username) {

                    $shopuser->delivery_state = 2;
                    $shopuser->save();
                    $count++;

                    $shipment->shipment_state = 2;
                    $shipment->shipment_sync_date = date('d-m-Y H:i:s',$pullDate);
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
            $message = "<span style='color: red;'>Oprettede filer med " . $count . " ordre</span><br>";
            \System::connection()->commit();
        }

        $this->showList($message);
    }


    private function getSumList($shop_id,$file_date)
    {

        $shop = \Shop::find($shop_id);

        ob_start();

        ?><h2>Privatlevering sumliste - <?php echo $shop->name; ?></h2>
        <b>Batch: <?php echo $shop_id." / ".$file_date; ?></b>
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

            $shipmentList = $this->getShipmentsInFile($shop_id,$file_date);
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
        $mpdf->Output("Sumliste-".$shop->name."-".substr($file_date,0,10).".pdf","D");

        echo $content;
    }

    private function getLabelList($shop_id,$file_date)
    {

        $shop = \Shop::find($shop_id);


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
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(21);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(38);
        $sheet->getColumnDimension('J')->setWidth(22);

        $sheet->setCellValueByColumnAndRow(1, 1, "Namn:");
        $sheet->setCellValueByColumnAndRow(2, 1, "Adress:");
        $sheet->setCellValueByColumnAndRow(3, 1, "Postnr:");
        $sheet->setCellValueByColumnAndRow(4, 1, "Stad:");
        $sheet->setCellValueByColumnAndRow(5, 1, "Mail:");
        $sheet->setCellValueByColumnAndRow(6, 1, "Mobil:");
        $sheet->setCellValueByColumnAndRow(7, 1, "Produkt:");
        $sheet->getStyle("A1:N1")->getFont()->setBold(true);

        $row=2;

        // Get shopuser orders
        $shipmentList = $this->getShipmentsInFile($shop_id,$file_date);
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
/*
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
*/
            // Write data row
            $sheet->setCellValueByColumnAndRow(1, $row, $userData["name"]);
            $sheet->setCellValueByColumnAndRow(2, $row, $userData["address"].((trimgf($userData["address2"]) != "" && mb_strtolower(trimgf($userData["address"])) != mb_strtolower(trimgf($userData["address2"])))? ", ".$userData["address2"] : ""));
            $sheet->setCellValueByColumnAndRow(3, $row, $userData["postnr"]);
            $sheet->setCellValueByColumnAndRow(4, $row, $userData["bynavn"]);
            $sheet->setCellValueByColumnAndRow(5, $row, $userData["email"]);
            $sheet->setCellValueByColumnAndRow(6, $row, $phone);
            $sheet->setCellValueByColumnAndRow(7, $row, $userorder->itemno.": ".$userorder->description);
            $row++;

        }

        // Output excel file
        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="privatlevering-segave-'.$shop->name.'-'.substr($file_date,0,10).'.xlsx"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($phpExcel);
        $objWriter->save('php://output');
        exit();

        /*
        //header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="privatlevering-segave-'.$shop->name.'-'.substr($file_date,0,10).'.csv"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);

        //$objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
        exit();
*/
    }


    private function getWaiting($shopid) {
        $shopSQL = (intval($shopid) > 0) ? " && shop_user.shop_id = ".intval($shopid) : "";
        $sql = "SELECT shipment.*, shop_user.*, shop_user.id as shopuser_id, shipment.id as shipment_id FROM shipment, shop_user where shipment.handler = 'sespahotel' && shipment.shipment_type = 'privatedelivery' && shipment.shipment_state = 1 && shop_user.delivery_state = 1 && navsync_response = shipment.id ".$shopSQL;
        return \ShopUser::find_by_sql($sql);
    }

    private function countWaiting() {
        $list = $this->getWaiting(0);
        $map = array();
        foreach($list as $shopuser) {

            if(!isset($map[$shopuser->shop_id])) $map[$shopuser->shop_id] = 0;
            $map[$shopuser->shop_id]++;
        }
        return $map;
    }

    private function getFileList($shopid=0) {
        $shopSQL = (intval($shopid) > 0) ? " && shop_user.shop_id = ".intval($shopid) : "";
        $sql = "SELECT shipment.shipment_sync_date, shop_user.shop_id, count(shipment.id) as shipmentcount FROM shipment, shop_user where shipment.shipment_state IN (2,5) && shop_user.delivery_state in (2,5) && shipment.handler = 'sespahotel' && shipment.shipment_type = 'privatedelivery' && navsync_response = shipment.id ".$shopSQL. " GROUP BY shipment.shipment_sync_date, shop_user.shop_id ORDER BY shipment.shipment_sync_date DESC";
        return \ShopUser::find_by_sql($sql);
    }

    private function getShipmentsInFile($shopid,$filedate) {
        $shopSQL =  " && shop_user.shop_id = ".intval($shopid);
        $dateSQL = " && shipment.shipment_sync_date = '$filedate' ";
        $sql = "SELECT shipment.*, shop_user.*, shipment.id as shipment_id FROM shipment, shop_user where shipment.shipment_state IN (2,5) && shop_user.delivery_state in (2,5) && shipment.handler = 'sespahotel' && shipment.shipment_type = 'privatedelivery' && navsync_response = shipment.id ".$shopSQL.$dateSQL ."";
        return \ShopUser::find_by_sql($sql);
    }

    private function formatPostalCode($postalCode,$shopid) {

        if($shopid == 1832 || $shopid == 1981 || $shopid == 4793 || $shopid == 5117) {
            if(strlen(trimgf($postalCode)) == 5) {
                $postalCode = trimgf($postalCode);
                $postalCode = substr($postalCode,0,3)." ".substr($postalCode,3);
            }
            return $postalCode;
        }

    }

    private function getCountry($shopid) {
        if ($shopid == 52) {
            $valueAlias = "Danmark";
        } else if ($shopid == 54) {
            $valueAlias = "Danmark";
        } else if ($shopid == 55) {
            $valueAlias = "Danmark";
        } else if ($shopid == 56) {
            $valueAlias = "Danmark";
        } else if ($shopid == 53) {
            $valueAlias = "Danmark";
        } else if ($shopid == 265) {
            $valueAlias = "Danmark";
        } else if ($shopid == 287) {
            $valueAlias = "Danmark";
        } else if ($shopid == 290) {
            $valueAlias = "Danmark";
        } else if ($shopid == 310) {
            $valueAlias = "Danmark";
        } else if ($shopid == 272) {
            $valueAlias = "Norge";
        } else if ($shopid == 57) {
            $valueAlias = "Norge";
        } else if ($shopid == 58) {
            $valueAlias = "Norge";
        } else if ($shopid == 59) {
            $valueAlias = "Norge";
        } else if ($shopid == 574) {
            $valueAlias = "Norge";
        } else if ($shopid == 575) {
            $valueAlias = "Danmark";
        } else if ($shopid == 248) {
            $valueAlias = "Danmark";
        } else if ($shopid == 1832) {
            $valueAlias = "Sverige";
        } else if ($shopid == 1981) {
            $valueAlias = "Sverige";
        } else if ($shopid == 4793) {
            $valueAlias = "Sverige";
        } else if ($shopid == 5117) {
            $valueAlias = "Sverige";
        }

        return $valueAlias;
    }

}