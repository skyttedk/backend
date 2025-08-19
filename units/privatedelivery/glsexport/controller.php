<?php

namespace GFUnit\privatedelivery\glsexport;

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
        else if(isset($_GET["action"]) && isset($_GET["shopid"]) && intval($_GET["shopid"]) > 0 && $_GET["action"] == "sumlisttotal") {
            $this->getSumListTotal(intval($_GET["shopid"]));
        }
        else if(isset($_GET["action"]) && isset($_GET["shopid"]) && intval($_GET["shopid"]) > 0 && $_GET["action"] == "sumlisttotalcsv") {
            $this->getSumListTotalCSV(intval($_GET["shopid"]));
        }
        else {
            $this->showList();
        }
    }


    private function getWaiting($shopid) {
        $shopSQL = (intval($shopid) > 0) ? " && shop_user.shop_id = ".intval($shopid) : "";
        $sql = "SELECT shipment.*, shop_user.*, shop_user.id as shopuser_id, shipment.id as shipment_id FROM shipment, shop_user where shipment.handler = 'glsexport' && shipment.shipment_type = 'privatedelivery' && shipment.shipment_state = 1 && shop_user.delivery_state = 1 && navsync_response = shipment.id ".$shopSQL;
        echo $sql;
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
        $sql = "SELECT shipment.shipment_sync_date, shop_user.shop_id, count(shipment.id) as shipmentcount FROM shipment, shop_user where shipment.shipment_state IN (2,5) && shop_user.delivery_state = 2 && shipment.handler = 'glsexport' && shipment.shipment_type = 'privatedelivery' && navsync_response = shipment.id ".$shopSQL. " GROUP BY shipment.shipment_sync_date, shop_user.shop_id ORDER BY shipment.shipment_sync_date DESC";
        
        return \ShopUser::find_by_sql($sql);
    }

    private function getShipmentsInFile($shopid,$filedate) {
        $shopSQL =  " && shop_user.shop_id = ".intval($shopid);
        $dateSQL = " && shipment.shipment_sync_date = '$filedate' ";
        $sql = "SELECT shipment.*, shop_user.*, shipment.id as shipment_id, shop_user.id as shopuser_id FROM shipment, shop_user where shipment.shipment_state IN (2,5) && shop_user.delivery_state = 2 && shipment.handler = 'glsexport' && shipment.shipment_type = 'privatedelivery' && navsync_response = shipment.id ".$shopSQL.$dateSQL ."";
        return \ShopUser::find_by_sql($sql);
    }

    private function getShipmentsInFilesTotal($shopid) {
        $shopSQL =  " && shop_user.shop_id = ".intval($shopid);
        $dateSQL = " && shipment.shipment_sync_date IS NOT NULL && shipment.shipment_sync_date < '2022-12-12 00:00:00' ";
        $sql = "SELECT shipment.*, shop_user.*, shipment.id as shipment_id, shop_user.id as shopuser_id FROM shipment, shop_user where shipment.shipment_state IN (2,5) && shop_user.delivery_state = 2 && shipment.handler = 'glsexport' && shipment.shipment_type = 'privatedelivery' && navsync_response = shipment.id ".$shopSQL.$dateSQL ."";
        return \ShopUser::find_by_sql($sql);
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
                            <td style="padding: 10px;"><?php if($waitcount > 0) { ?><button type="button" onclick="document.location='?rt=unit/privatedelivery/glsexport/dispatch&action=createfile&shopid=<?php echo $shopid; ?>';">dan fil</button><?php } ?></td>
                        <?php } ?>
                    </tr>
                </table>

            </form></div>
        <table style="width: 100%; max-width: 1000px;" cellpadding="5" cellspacing="5">
            <tr style="font-weight: bold;"><td>Shop ID</td><td>Shop name</td><td>Dato</td><td>Antal</td><td>Labelliste</td><td>Sumliste</td></tr><?php

            $allList = array();

            foreach($fileList as $file) {

                if(!isset($allList[$file->shop_id])) {
                    $allList[$file->shop_id] = $shopmap[$file->shop_id]->concept_code;
                }

                echo "<tr>
                    <td>".$file->shop_id."</td>
                    <td>".$shopmap[$file->shop_id]->concept_code."</td>
                    <td>".$file->shipment_sync_date."</td>
                    <td>".$file->shipmentcount."</td>
                    <td><a href='?rt=unit/privatedelivery/glsexport/dispatch&action=labellist&shopid=".$file->shop_id."&filedate=".$file->shipment_sync_date."'>hent labels</a></td>
                    <td><a href='?rt=unit/privatedelivery/glsexport/dispatch&action=sumlist&shopid=".$file->shop_id."&filedate=".$file->shipment_sync_date."'>hent sumliste</a></td>
                    
                </tr>";
            }
            ?></table>

        <br><br>
        <h2>Total sumlister pr. koncept</h2>
        <ul>
            <?php foreach($allList as $shopID => $shopName) {
              echo "<li><a href='?rt=unit/privatedelivery/glsexport/dispatch&action=sumlisttotal&shopid=".$shopID."'>".$shopName."</a> | <a href='?rt=unit/privatedelivery/glsexport/dispatch&action=sumlisttotalcsv&shopid=".$shopID."'>csv</a></li>";
            } ?>
        </ul>
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



    private function getSumListTotalCSV($shop_id)
    {

        $shop = \Shop::find($shop_id);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="privatlevering-'.$shop->name.'-total.csv"');
        header('Cache-Control: max-age=0');


        echo "Sumliste;".$shop->name."\r\n";
        echo "Varenr;Gave navn;Model;Antal\r\n";

            // Get shopuser orders
            $aliasList = array();
            $aliasCount = array();
            $presentInfo = array();

            $shipmentList = $this->getShipmentsInFilesTotal($shop_id);
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

                echo $present["varenr"].";".$present["name"].";".$present["modelname"].";".$aliasCount[$fullAlias]."\r\n";

            }

            echo "Total antal;;;".$totalCount."\r\n";

    }


    private function getSumListTotal($shop_id)
    {

        $shop = \Shop::find($shop_id);

        ob_start();

        ?><h2>Privatlevering sumliste - <?php echo $shop->name; ?></h2>
        <b>Batch: <?php echo $shop_id; ?> / total</b>
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

            $shipmentList = $this->getShipmentsInFilesTotal($shop_id);
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
        $mpdf->Output("Sumliste-".$shop->name."-total.pdf","D");

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

        $showComplaint = true;

        $date1 = date("Y-m-d",(time()))."-08:30:00";
        $date2 = date("Y-m-d",(time()))."-16:00:00";

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

            $phone = trim(str_replace(array("+"," ","-"),"",$userData["telefon"]));

            if($phone != "") {

                if($userData["land"] == "Sverige") {

                    if(substr($phone,0,2) == "46") {
                        $phone = substr($phone,2);
                    }
                    
                    /*
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);

                    if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
                        if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                        if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                        //$phone = "+46".$phone;
                    }
                */
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

                    /*
                    if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "45" || substr($phone,0,3) == "+45")) {
                        if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                        $phone = $phone;
                    }
                    */
                }

            }

            // Find varenr from navision
            $orderList = \Order::find_by_sql("SELECT * FROM `order` where shopuser_id = ".$userorder->shopuser_id);
            if(count($orderList) > 0) {
                $order = $orderList[0];

                $presentModelList = \PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id = " . $order->present_model_id . " && language_id = 1");
                $presentModel = $presentModelList[0];

                try {
                    // Write data row
                    $sheet->setCellValueByColumnAndRow(1, $row, ($userData["name"]));
                    $sheet->setCellValueByColumnAndRow(2, $row, $userData["email"]);
                    $sheet->setCellValueByColumnAndRow(3, $row, $phone);
                    $sheet->setCellValueByColumnAndRow(4, $row, $userData["address"] . ((trimgf($userData["address2"]) != "" && mb_strtolower(trimgf($userData["address"])) != mb_strtolower(trimgf($userData["address2"]))) ? ", " . $userData["address2"] : ""));
                    $sheet->setCellValueByColumnAndRow(5, $row, str_replace(" ", "", $userData["postnr"]));
                    $sheet->setCellValueByColumnAndRow(6, $row, $userData["bynavn"]);
                    $sheet->setCellValueByColumnAndRow(7, $row, $userData["land"]);
                    $sheet->setCellValueByColumnAndRow(8, $row, $presentModel->model_present_no . ": " . $userorder->description);
                    $sheet->setCellValueByColumnAndRow(10, $row, $date1);
                    $sheet->setCellValueByColumnAndRow(11, $row, $date2);
                    $sheet->setCellValueByColumnAndRow(12, $row, $userorder->shipment_id);

                } catch (\Exception $e) {

                    echo "ERROR: " . $e->getMessage() . "\n";
                    echo "<pre>";
                    var_dump($userData);
                    echo "</pre>";
                    exit();
                }

                if ($showComplaint) {
                    $complaint = \OrderPresentComplaint::find("all", array("conditions" => array("shopuser_id" => $userorder->shopuser_id)));
                    if (count($complaint) > 0) {
                        $sheet->setCellValueByColumnAndRow(13, $row, "!");
                        $sheet->setCellValueByColumnAndRow(14, $row, str_replace("\n", " - ", urldecode($complaint[0]->complaint_txt)));
                    }
                }

                $row++;
            }
            else {
                echo "NO ORDER FROM SHOPUSER: ".$userorder->shopuser_id."\n";
               
            }

        }

        // Output excel file
        //header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="privatlevering-'.$shop->name.'-'.substr($file_date,0,10).'.csv"');
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

    private function formatPostalCode($postalCode,$shopid) {

        if($shopid == 1832 || $shopid == 1981 || $shopid == 5117 || $shopid == 4793) {
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