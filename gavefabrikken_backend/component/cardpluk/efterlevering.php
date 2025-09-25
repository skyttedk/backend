<?php

use GFCommon\Model\Access\BackendPermissions;

class efterlevering
{

    public function dispatch()
    {
        if(BackendPermissions::session()->hasPermission(BackendPermissions::PERMISSION_KORT_PLUKLISTER) == false) {
            echo "Du har ikke rettigheder til at se denne side";
            return;
        }


        ob_start();

        if(isset($_POST["action"]) && $_POST["action"] == "post") {

            $error = $this->checkForInputErrors();
            if($error != "") {
                $this->showFrontPage("<div style='color: red;'>".$error."</div>");
            } else if($_POST["do"] == "Download") {
                $this->downloadList();
            } else {
                $this->checkInputs();
            }

        } else {
            $this->showFrontPage();
        }

        $content = ob_get_contents();
        ob_end_clean();

        echo utf8_decode($content);

    }

    private function downloadList()
    {

        return $this->downloadList2();

        ob_end_clean();

        $orderList = $this->getShopuserOrderList();

        // Init phpexcel
        $phpExcel = new PHPExcel();
        $phpExcel->removeSheetByIndex(0);

        // Write header
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->setTitle("Efterlevering");
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

        $date1 = date("Y-m-d",(time()+60*60*24))."-09:00:00";
        $date2 = date("Y-m-d",(time()+60*60*24))."-16:00:00";

        // Get shopuser orders
        foreach($orderList as $order)
        {

            // Get user data
            $userData = $this->getUserData($order["shopuser_id"],$order["shop_id"]);

            // Make phone pretty
            $phone = $userData["telefon"];
            if(trimgf($phone) == "") $phone = $order["contact_phone"];
            $phone = trimgf(str_replace(array(" ","-"),"",$phone));
            if($phone != "") {
                if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    $phone = (in_array($order["shop_id"],array(1981,1832,5117,4793,8271,9495)) ? "+46" : "+45").$phone;
                }
            }

            // Find country
            $country = trimgf($order["ship_to_country"]);
            if($country == "") $country = $userData["land"];

            // Write data row
            $sheet->setCellValueByColumnAndRow(1, $row,$order["name"].", att: ".$userData["name"]);
            $sheet->setCellValueByColumnAndRow(2, $row, trimgf($userData["email"]) == "" ? $order["contact_email"] : $userData["email"]);
            $sheet->setCellValueByColumnAndRow(3, $row, $phone);
            $sheet->setCellValueByColumnAndRow(4, $row, $order["ship_to_address"].((trimgf($order["ship_to_address_2"]) != "" && mb_strtolower(trimgf($order["ship_to_address"])) != mb_strtolower(trimgf($order["ship_to_address_2"])))? ", ".$order["ship_to_address_2"] : ""));
            $sheet->setCellValueByColumnAndRow(5, $row, $order["ship_to_postal_code"]);
            $sheet->setCellValueByColumnAndRow(6, $row, $order["ship_to_city"]);
            $sheet->setCellValueByColumnAndRow(7, $row, $country);
            $sheet->setCellValueByColumnAndRow(8, $row, $this->fullalias($order["shop_id"],$order["fullalias"]).": ".$order["model_name"].(trimgf($order["model_no"]) == "" ? "" : ", ".trimgf( $order["model_no"])));
            $sheet->setCellValueByColumnAndRow(10,$row,$date1);
            $sheet->setCellValueByColumnAndRow(11,$row,$date2);
            $row++;

        }

        // Generate filename
        $filename = "efterlev-".$this->getVareNr()."-uge-".implode("-",$this->getSelectedWeekNos())."-".$this->getShopName()."";

        // Output excel file
        //header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);

        //$objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter = new PHPExcel_Writer_CSV($phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
        exit();

    }


    private function downloadList2()
    {

        ob_end_clean();

        $noShops = array(272,57,58,59,574);

        $orderList = $this->getShopuserOrderList();


        // Prepare
        $outRow = 1;
        $phpExcel = new PHPExcel();
        $phpExcel->removeSheetByIndex(0);
        //$phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $outsheet = $phpExcel->createSheet();
        $outsheet->setTitle("Labels - efterlevering");

        $outsheet->getColumnDimension('A')->setWidth(27.89);
        $outsheet->getColumnDimension('B')->setWidth(34.78);
        $outsheet->getColumnDimension('C')->setWidth(18.74);
        $outsheet->getColumnDimension('D')->setWidth(71.33);
        $outsheet->getColumnDimension('E')->setWidth(9.20);
        $outsheet->getColumnDimension('F')->setWidth(20.89);
        $outsheet->getColumnDimension('G')->setWidth(9.56);
        $outsheet->getColumnDimension('H')->setWidth(37.67);
        $outsheet->getColumnDimension('I')->setWidth(22.33);
        $outsheet->getColumnDimension('J')->setWidth(27.89);

        $outsheet->setCellValueByColumnAndRow(1,$outRow,"Navn");
        $outsheet->setCellValueByColumnAndRow(2,$outRow,"E-mail");
        $outsheet->setCellValueByColumnAndRow(3,$outRow,"Mobil");
        $outsheet->setCellValueByColumnAndRow(4,$outRow,"Adresse");
        $outsheet->setCellValueByColumnAndRow(5,$outRow,"Postnr");
        $outsheet->setCellValueByColumnAndRow(6,$outRow,"By");
        $outsheet->setCellValueByColumnAndRow(7,$outRow,"Land");
        $outsheet->setCellValueByColumnAndRow(8,$outRow,"Gave/varebeskrivelse");
        $outsheet->setCellValueByColumnAndRow(9,$outRow,"Farve/variant");
        $outsheet->setCellValueByColumnAndRow(10,$outRow,"Att");

        $outRow++;

        // Get shopuser orders
        foreach($orderList as $order) {

            // Get user data
            $userData = $this->getUserData($order["shopuser_id"], $order["shop_id"]);

            // Make phone pretty
            $phone = $userData["telefon"];

            if(!in_array($order["shop_id"],$noShops))
            {
                if (trimgf($phone) == "") $phone = $order["contact_phone"];
                $phone = trimgf(str_replace(array(" ", "-"), "", $phone));
                if ($phone != "") {
                    if (!(substr($phone, 0, 1) == "+" || substr($phone, 0, 2) == "46" || substr($phone, 0, 3) == "+46")) {
                        if (substr($phone, 0, 1) === "0") $phone = substr($phone, 1);
                        $phone = (in_array($order["shop_id"], array(1981, 1832,5117,4793,8271,9495)) ? "+46" : "+45") . $phone;
                    }
                }
            }

            // Find country
            $country = trimgf($order["ship_to_country"]);
            if ($country == "") $country = $userData["land"];

            $outsheet->setCellValueByColumnAndRow(1, $outRow, $order["name"]);
            $outsheet->setCellValueByColumnAndRow(2, $outRow, trimgf($userData["email"]) == "" ? $order["contact_email"] : $userData["email"]);
            $outsheet->setCellValueByColumnAndRow(3, $outRow, $phone);
            $outsheet->setCellValueByColumnAndRow(4, $outRow, $order["ship_to_address"].((trimgf($order["ship_to_address_2"]) != "" && mb_strtolower(trimgf($order["ship_to_address"])) != mb_strtolower(trimgf($order["ship_to_address_2"])))? ", ".$order["ship_to_address_2"] : ""));
            $outsheet->setCellValueByColumnAndRow(5, $outRow,  $order["ship_to_postal_code"]);
            $outsheet->setCellValueByColumnAndRow(6, $outRow,  $order["ship_to_city"]);
            $outsheet->setCellValueByColumnAndRow(7, $outRow, $country);
            $outsheet->setCellValueByColumnAndRow(8, $outRow, $order["model_name"]);
            $outsheet->setCellValueByColumnAndRow(9, $outRow,  trimgf( $order["model_no"]));
            $outsheet->setCellValueByColumnAndRow(10, $outRow,  ucwords($userData["name"]));


            $outRow++;


        }

        // Generate filename
        $filename = "efterlev-".$this->getVareNr()."-uge-".implode("-",$this->getSelectedWeekNos())."-".$this->getShopName()."";

        // Output excel file
        //header('Content-Type: application/vnd.ms-excel');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);

        //$objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter = new PHPExcel_Writer_CSV($phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
        exit();

        /*
        // Generate filename
        $filename = "efterlev-".$this->getVareNr()."-uge-".implode("-",$this->getSelectedWeekNos())."-".$this->getShopName()."";

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);

        $objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter->save('php://output');
        */
    }

    private function checkInputs() {
        $orderList = $this->getShopuserOrderList();
        $this->showFrontPage("<div>Der er ".countgf($orderList)." gaver i de valgte kriterier.</div>");
    }

    private function getSelectedShop() {
        return isset($_POST["shopid"]) ? intval($_POST["shopid"]) : 0;
    }

    private function getSelectedDates()
    {
        return isset($_POST["weekno"]) && is_array($_POST["weekno"]) ? $_POST["weekno"] : array();
    }

    private function getShopName() {
        $shopid = $this->getSelectedShop();
        $shoplist = Dbsqli::getSql2("SELECT * FROM shop WHERE id = ".intval($shopid));
        if(count($shoplist) == 0) return "UKENDT SHOP";
        return $shoplist[0]["name"];
    }

    private function getSelectedWeekNos() {
        $expireDateList = ExpireDate::find_by_sql("SELECT * FROM expire_date ORDER BY expire_date ASC");
        $weeknolist = array();
        foreach($expireDateList as $expireDate) {
            if($this->isDateSelected($expireDate->expire_date->format("Y-m-d"))) $weeknolist[] = $expireDate->week_no;
        }
        return $weeknolist;
    }

    private function isDateSelected($date) {
        return in_array($date,$this->getSelectedDates());
    }

    private function getVareNr() {
        return isset($_POST["varenr"]) ? trimgf($_POST["varenr"]) : "";
    }

    private function getBSNrListe($asArray) {
        $bsnrstring = isset($_POST["bsnrlist"]) ? trimgf($_POST["minquantity"]) : "";

        if(trim($bsnrstring) == "") {
            return $asArray ? array():"";
        }

        if(!$asArray) return $bsnrstring;

        // Split into array, separator is , and make an array with trimmed uppercased values that must start with "BS"
        $bsnrlist = explode(",",$bsnrstring);
        $bsnrlist = array_map("strtoupper",array_map("trim",$bsnrlist));
        $bsnrlist = array_filter($bsnrlist,function($bsnr) {
            return substr($bsnr,0,2) == "BS";
        });
        return $bsnrlist;
    }

    private function checkForInputErrors() {
        if($this->getSelectedShop() == 0) return "Der er ikke valgt en shop";
        else if(count($this->getSelectedDates()) == 0) return "Der er ikke valgt nogen deadlines";
        else if($this->getVareNr() == "") return "Der er ikke angivet et varenr";
        else if(!isset($_POST["do"])) return "Ingen handling valgt";
        return "";
    }

    private function getShopuserOrderList() {

        $companyOrderLimit = "";
        $bsNrList = $this->getBSNrListe(true);
        if(count($bsNrList) > 0) {

            $companyOrderLimit = " && shop_user.company_order_id in (SELECT id FROM company_order WHERE order_no in ('".implode("','",$bsNrList)."')) ";
        }

        $sql = "SELECT order.shopuser_id, order.shop_id, shop_user.username, company.name, company.ship_to_country, company.ship_to_address, company.ship_to_address_2, company.ship_to_postal_code, company.contact_phone, company.contact_email, company.ship_to_city, present_model.fullalias, present_model.model_name, present_model.model_no FROM `shop_user`, `order`, company, present, present_model WHERE
            `order`.present_model_id = present_model.model_id && present_model.language_id = 1 && present.id = present_model.present_id && present_model.model_present_no LIKE '".$this->getVareNr()."' && present.shop_id = ".$this->getSelectedShop()." &&
            `order`.company_id = company.id &&
            `shop_user`.id = `order`.shopuser_id && shop_user.is_giftcertificate = 1 && shop_user.blocked = 0 && shop_user.shutdown = 0 &&
            `order`.shop_id = ".$this->getSelectedShop()." && shop_user.shop_id = ".$this->getSelectedShop()." && `shop_user`.`expire_date` IN ( '".implode("','",$this->getSelectedDates())."') ".$companyOrderLimit."
            ORDER by shop_user.company_id ASC, `order`.company_name ASC, shop_user.username ASC";

        echo $sql;
        return Dbsqli::getSql2($sql);

    }

    private function showFrontPage($message="")
    {

        $shoplist = Shop::find_by_sql("SELECT * FROM `shop` where is_gift_certificate = 1 && id NOT IN (262,569,287,247,248,264,263,265,251) ORDER BY shop.mailserver_id ASC, `shop`.`name` ASC");
        $expireDateList = ExpireDate::find_by_sql("SELECT * FROM expire_date ORDER BY expire_date ASC");

        ?><h2>Lav træk til efterlevering</h2>
        <form method="post" action="index.php?rt=cardshoppluk/efterlevering&do=1">
            <?php if($message != "") echo "<div style='font-size: 1.5em;'>".$message."</div><br>"; ?>
            <table>
                <tr>
                    <td valign="top">Vælg shop</td>
                    <td valign="top"><select name="shopid"><?php
                            foreach($shoplist as $shop)
                            {
                                echo "<option value='".$shop->id."' ".($this->getSelectedShop() == $shop->id ? "selected" : "").">".$shop->name." (beløb: ".$shop->card_value.", id: ".$shop->id.")</option>";
                            }
                            ?></select></td>
                </tr>
                <tr>
                    <td valign="top">Vælg ugenr</td>
                    <td valign="top"><?php
                        foreach($expireDateList as $expireDate) {
                            ?><div><label><input type="checkbox" name="weekno[]" value="<?php echo $expireDate->expire_date->format("Y-m-d"); ?>" <?php if($this->isDateSelected($expireDate->expire_date->format("Y-m-d"))) echo "checked"; ?>> Uge <?php echo $expireDate->week_no; ?> (<?php echo $expireDate->display_date; ?>) <?php echo ($expireDate->is_delivery == 1 ? " - er privatlevering" : ""); ?> </label></div><?php
                        }
                        ?></td>
                </tr>
                <tr>
                    <td valign="top">Varenr.</td>
                    <td valign="top"><div><input type="text" size="30" name="varenr" value="<?php echo $this->getVareNr(); ?>"></div></td>
                </tr>
                <tr>
                    <td valign="top">BS nr liste</td>
                    <td valign="top"><div><input type="text" size="30" name="bsnrlist" value="<?php echo $this->getBSNrListe(false); ?>"></div></td>
                </tr>


                <tr>
                    <td valign="top" colspan="2" style="text-align: right;">
                        <input type="hidden" name="action" value="post">
                        <input type="submit" name="do" value="Tjek antal">
                        <input type="submit" name="do" value="Download">
                    </td>
                </tr>

            </table>

        </form><script>

        function checkCount() {

        }

    </script><?php

    }



    private function formatPostalCode($postalCode,$shopid) {


        if(\GFBiz\Model\Cardshop\ShopMetadata::getShopLangCode($shopid) == 5) {
            if(strlen(trimgf($postalCode)) == 5) {
                $postalCode = trimgf($postalCode);
                $postalCode = substr($postalCode,0,3)." ".substr($postalCode,3);
            }
            return $postalCode;
        }
        return $postalCode;

    }

    private function getUserData($shopuserid,$shopid)
    {


        $nameAttributes = GFBiz\Model\Cardshop\ShopMetadata::getAttributeList("name");
        $adress1Attributes = GFBiz\Model\Cardshop\ShopMetadata::getAttributeList("address1");
        $adress2Attributes = GFBiz\Model\Cardshop\ShopMetadata::getAttributeList("address2");
        $postnrAttributes = GFBiz\Model\Cardshop\ShopMetadata::getAttributeList("zip");
        $bynavnAttributes = GFBiz\Model\Cardshop\ShopMetadata::getAttributeList("city");
        $emailAttributes = GFBiz\Model\Cardshop\ShopMetadata::getAttributeList("email");
        $phoneAttributes = GFBiz\Model\Cardshop\ShopMetadata::getAttributeList("phone");

        /*
        $nameAttributes = array(32,718,93,722,727,11057,10085,1288,1292,1332,587,595,603,1116,2928,2932,1199,1228);
        $adress1Attributes = array(10755,10759,10763,10767,588,596,604,1139,751,10751,11668,10747);
        $adress2Attributes = array(10752,10756,10760,10764,10768,589,597,605,1140,752,11669,10748);
        $postnrAttributes = array(10753,10757,10761,10765,10769,590,598,606,1141,11670,753,10749);
        $bynavnAttributes = array(11671,10750,10754,10758,10762,10766,10770,591,599,607,1142,754);
        $emailAttributes = array(11058,10086,1289,1293,31,1333,586,594,602,92,1117,2929,2933,1200,1229,719,723,728);
        $phoneAttributes = array(11667,11672,4301,4302,4303,4304,4305,582,761,763,765,767);
        */

        $shopuserData = array(
            "name" => $shopuserid,
            "address" => "-",
            "address2" => "",
            "postnr" => "-",
            "bynavn" => "-",
            "land" => $this->getCountry($shopid),
            "telefon" => "-",
            "email" => "-"
        );

        $userAttributes = UserAttribute::find_by_sql("SELECT * FROM user_attribute WHERE shopuser_id = ".$shopuserid);
        foreach($userAttributes as $attribute) {

            if(in_array($attribute->attribute_id,$nameAttributes)) $shopuserData["name"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$adress1Attributes)) $shopuserData["address"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$adress2Attributes)) $shopuserData["address2"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$postnrAttributes)) $shopuserData["postnr"] = $this->formatPostalCode($attribute->attribute_value,$shopid);
            if(in_array($attribute->attribute_id,$bynavnAttributes)) $shopuserData["bynavn"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$emailAttributes)) $shopuserData["email"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$phoneAttributes)) $shopuserData["telefon"] = $attribute->attribute_value;

        }

        return $shopuserData;
    }


    private function getCountry($shopid) {

        return \GFBiz\Model\Cardshop\ShopMetadata::getShopCountry($shopid);
        /*
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
        }

        return $valueAlias;
        */
    }

    private function getvaluealias($shopid)
    {

        $valueAlias = \GFBiz\Model\Cardshop\ShopMetadata::getShopValueAlias($shopid);
        return $valueAlias."";

        /*
        if ($shopid == 272) {
            $shopCertValue = 300;
        }

        if ($shopid == 52) {
            $valueAlias = "JK-";
        } else if ($shopid == 54) {
            $valueAlias = "4";
        } else if ($shopid == 55) {
            $valueAlias = "5";
        } else if ($shopid == 56) {
            $valueAlias = "6";
        } else if ($shopid == 53) {
            $valueAlias = "GK-";
        } else if ($shopid == 265) {
            $valueAlias = "JT-";
        } else if ($shopid == 287) {
            $valueAlias = "1";
        } else if ($shopid == 290) {
            $valueAlias = "2";
        } else if ($shopid == 310) {
            $valueAlias = "3";
        } else if ($shopid == 272) {
            $valueAlias = "3";
        } else if ($shopid == 57) {
            $valueAlias = "4";
        } else if ($shopid == 58) {
            $valueAlias = "6";
        } else if ($shopid == 59) {
            $valueAlias = "8";
        } else if ($shopid == 574) {
            $valueAlias = "1";
        } else if ($shopid == 575) {
            $valueAlias = "D-";
        } else if ($shopid == 248) {
            $valueAlias = "8";
        }

        else if ($shopid == 1832) {
            $valueAlias = "S3-";
        }
        else if ($shopid == 1981) {
            $valueAlias = "S8-";
        }

        return $valueAlias;
        */
    }

    private function fullalias($shopid, $alias)
    {
        return $this->getvaluealias($shopid) . (strlen(intval($alias)) == 1 ? "0" : "") . $alias;
    }

}