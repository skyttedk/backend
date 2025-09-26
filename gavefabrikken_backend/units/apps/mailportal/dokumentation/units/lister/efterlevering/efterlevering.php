<?php

namespace GFUnit\lister\efterlevering;

use GFBiz\Model\Cardshop\ShopMetadata;
use GFCommon\Model\Navision\CountryHelper;

class Efterlevering
{

    private $includeUserData = false;

    public function dispatch()
    {

        ob_start();

        if(isset($_POST["action"]) && $_POST["action"] == "post") {

            $error = $this->checkForInputErrors();
            if($error != "") {
                $this->showFrontPage("<div style='color: red;'>".$error."</div>");
            } else if($_POST["do"] == "Download") {
                $this->downloadList();
            }else if($_POST["do"] == "Inkl. brugerdata") {
                $this->includeUserData = true;
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
        $shoplist = \Dbsqli::getSql2("SELECT * FROM shop WHERE id = ".intval($shopid));
        if(count($shoplist) == 0) return "UKENDT SHOP";
        return $shoplist[0]["name"];
    }

    private function getSelectedWeekNos() {
        $expireDateList = \ExpireDate::find_by_sql("SELECT * FROM expire_date ORDER BY expire_date ASC");
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


    private function getBSNrUse() {
        return isset($_POST["bsnruse"]) ? intvalgf($_POST["bsnruse"]) : "";
    }

    private function getBSNrListe($asArray) {
        $bsnrstring = isset($_POST["bsnrlist"]) ? trimgf($_POST["bsnrlist"]) : "";

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

        /*
            $sql = "SELECT shop_user.*, `order`.*, company.*, present.*, present_model.* FROM `shop_user`, `order`, company, present, present_model WHERE
            `order`.present_model_id = present_model.model_id && present_model.language_id = 1 && present.id = present_model.present_id && present_model.model_present_no LIKE '".$this->getVareNr()."' && present.shop_id = ".$this->getSelectedShop()." &&
            `order`.company_id = company.id &&
            `shop_user`.id = `order`.shopuser_id && shop_user.is_giftcertificate = 1 && shop_user.blocked = 0 && shop_user.shutdown = 0 &&
            `order`.shop_id = ".$this->getSelectedShop()." && shop_user.shop_id = ".$this->getSelectedShop()." && `order`.`gift_certificate_end_date` IN ( '".implode("','",$this->getSelectedDates())."')
            ORDER by shop_user.company_id ASC, `order`.company_name ASC, shop_user.username ASC";
        */

        /*
        $sql = "SELECT order.shopuser_id, order.shop_id, shop_user.username, company.name, company.ship_to_country, company.ship_to_address, company.ship_to_address_2, company.ship_to_postal_code, company.contact_phone, company.contact_email, company.ship_to_city, present_model.fullalias, present_model.model_name, present_model.model_no, present_model.model_present_no
            FROM `shop_user`, `order`, company, company_order, present, present_model WHERE company_order.company_id = company.id && shop_user.company_order_id = company_order.id && company_order.order_state in (4,5,9,10) &&
            `order`.present_model_id = present_model.model_id && present_model.language_id = 1 && present.id = present_model.present_id && present_model.model_present_no LIKE '".$this->getVareNr()."' && present.shop_id = ".$this->getSelectedShop()." &&
            `order`.company_id = company.id && shop_user.onpluk != 2 && is_replaced = 0 &&
            `shop_user`.id = `order`.shopuser_id && shop_user.is_giftcertificate = 1 && shop_user.blocked = 0 && shop_user.shutdown = 0 &&
            `order`.shop_id = ".$this->getSelectedShop()." && shop_user.shop_id = ".$this->getSelectedShop()." && `shop_user`.`expire_date` IN ( '".implode("','",$this->getSelectedDates())."')
            ORDER by shop_user.company_id ASC, `order`.company_name ASC, shop_user.username ASC";
        */

        $companyOrderLimit = "";
        $bsNrList = $this->getBSNrListe(true);
        if($this->getBSNrUse() > 0 && count($bsNrList) > 0) {

            $companyOrderLimit = " && shop_user.company_order_id ".($this->getBSNrUse() == 1 ? "in" : "not in")." (SELECT id FROM company_order WHERE order_no in ('".implode("','",$bsNrList)."')) ";
        }


        $sql = "SELECT order.shopuser_id, order.shop_id, shop_user.username, company.name, company.ship_to_country, company.ship_to_company, company.ship_to_address, company.ship_to_address_2, company.ship_to_postal_code, company.contact_phone, company.contact_email, company.ship_to_city, present_model.fullalias, present_model.model_name, present_model.model_no, present_model.model_present_no, company_order.company_id as company_id, shop_user.company_id as shopuser_company_id, shop_user.username, shop_user.password
            FROM `shop_user`, `order`, company, company_order, present, present_model WHERE 
             shop_user.company_order_id = company_order.id && company_order.order_state in (4,5,9,10) &&
            `order`.present_model_id = present_model.model_id && present_model.language_id = 1 && present.id = present_model.present_id && present_model.model_present_no LIKE '".$this->getVareNr()."' && present.shop_id = ".$this->getSelectedShop()." &&
            `order`.company_id = company.id && shop_user.onpluk != 2 && is_replaced = 0 &&
            `shop_user`.id = `order`.shopuser_id && shop_user.is_giftcertificate = 1 && shop_user.blocked = 0 && shop_user.shutdown = 0  &&
            `order`.shop_id = ".$this->getSelectedShop()." && shop_user.shop_id = ".$this->getSelectedShop()." && `shop_user`.`expire_date` IN ( '".implode("','",$this->getSelectedDates())."') ".$companyOrderLimit."
            ORDER by shop_user.company_id ASC, `order`.company_name ASC, shop_user.username ASC";


        //echo $sql;

        return \Dbsqli::getSql2($sql);

    }

    private function showFrontPage($message="")
    {

        header("Content-Type: text/html; charset=ISO-8859-1");

        $shoplist = \Shop::find_by_sql("SELECT shop.* FROM `shop`, cardshop_settings where shop.id = cardshop_settings.shop_id ORDER BY cardshop_settings.language_code ASC, `shop`.`name` ASC");
        $expireDateList = \ExpireDate::find_by_sql("SELECT * FROM expire_date ORDER BY expire_date ASC");

        ?><h2>Lav træk til efterlevering</h2>
        <form method="post" action="index.php?rt=unit/lister/efterlevering/index">
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
                    <td valign="top"><div><select name="bsnruse"><option value="0" <?php if($this->getBSNrUse() == 0) echo "selected"; ?>>brug ikke</option><option value="1" <?php if($this->getBSNrUse() == 1) echo "selected"; ?>>kun</option><option value="2" <?php if($this->getBSNrUse() == 2) echo "selected"; ?>>excl.</option></select><input type="text" size="30" name="bsnrlist" value="<?php echo $this->getBSNrListe(false); ?>"></div></td>
                </tr>
                <tr>
                    <td valign="top" colspan="2" style="text-align: right;">
                        <input type="hidden" name="action" value="post">
                        <input type="submit" name="do" value="Tjek antal">
                        <input type="submit" name="do" value="Download">
                        <input type="submit" name="do" value="Inkl. brugerdata">
                    </td>
                </tr>

            </table>

        </form><script>

        function checkCount() {

        }

    </script><?php

    }


    private function downloadList()
    {

        $this->downloadListGLS();
        exit();
        ob_end_clean();

        $orderList = $this->getShopuserOrderList();

        // Prepare
        $outRow = 1;
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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

        if($this->includeUserData) {
            $outsheet->setCellValueByColumnAndRow(12,$outRow,"ShopUserID");
            $outsheet->setCellValueByColumnAndRow(13,$outRow,"Username");
            $outsheet->setCellValueByColumnAndRow(14,$outRow,"Password");
        }

        $outRow++;

        // Get shopuser orders
        foreach($orderList as $order) {


            // Get user data
            $userData = $this->getUserData($order["shopuser_id"], $order["shop_id"]);

            // Find country
            $country = trimgf($order["ship_to_country"]);
            if ($country == "") $country = $userData["land"];

            $country = CountryHelper::codeToCountry(CountryHelper::countryToCode($country));



            // Check telefon
            $phone = trimgf($userData["telefon"]) == "" ? $order["contact_phone"] : $userData["telefon"];
            if($country == "Danmark") {
                if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "45" || substr($phone,0,3) == "+45")) {
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    $phone = "+45".$phone;
                }
            } else if($country == "Sverige") {
                if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    $phone = "+46".$phone;
                }
            }

            // Output row
            $outsheet->setCellValueByColumnAndRow(1, $outRow, str_replace(";"," ",html_entity_decode($order["ship_to_company"])));
            $outsheet->setCellValueByColumnAndRow(2, $outRow, trimgf($userData["email"]) == "" ? $order["contact_email"] : $userData["email"]);
            $outsheet->setCellValueByColumnAndRow(3, $outRow, $phone);
            $outsheet->setCellValueByColumnAndRow(4, $outRow, $order["ship_to_address"].((trimgf($order["ship_to_address_2"]) != "" && mb_strtolower(trimgf($order["ship_to_address"])) != mb_strtolower(trimgf($order["ship_to_address_2"])))? ", ".$order["ship_to_address_2"] : ""));
            $outsheet->setCellValueByColumnAndRow(5, $outRow,  $order["ship_to_postal_code"]);
            $outsheet->setCellValueByColumnAndRow(6, $outRow,  $order["ship_to_city"]);
            $outsheet->setCellValueByColumnAndRow(7, $outRow, $country);
            $outsheet->setCellValueByColumnAndRow(8, $outRow, $order["model_name"]);
            $outsheet->setCellValueByColumnAndRow(9, $outRow,  trimgf( $order["model_present_no"]));
            $outsheet->setCellValueByColumnAndRow(10, $outRow,  ucwords($userData["name"]));
            $outsheet->setCellValueByColumnAndRow(11, $outRow,  $userData["name"]." (kortnr ".$order["username"].")");

            if($this->includeUserData) {
                $outsheet->setCellValueByColumnAndRow(12, $outRow,  $userData["shopuserid"]);
                $outsheet->setCellValueByColumnAndRow(13, $outRow,  $userData["username"]);
                $outsheet->setCellValueByColumnAndRow(14, $outRow,  $userData["password"]);

            }

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
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
        exit();

    }


    private function downloadListGLS()
    {

        ob_end_clean();

        $orderList = $this->getShopuserOrderList();

        // Prepare
        $outRow = 1;
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $phpExcel->removeSheetByIndex(0);
        //$phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $outsheet = $phpExcel->createSheet();
        $outsheet->setTitle("Labels - efterlevering");


        $showDirectUnderCompany = true;
        $showChild = true;

        // Add headlines
        if($this->includeUserData)
        {
            $outsheet->setCellValueByColumnAndRow(1,$outRow,"Navn");
            $outsheet->setCellValueByColumnAndRow(2,$outRow,"E-mail");
            $outsheet->setCellValueByColumnAndRow(3,$outRow,"Mobil");
            $outsheet->setCellValueByColumnAndRow(4,$outRow,"Adresse");
            $outsheet->setCellValueByColumnAndRow(5,$outRow,"Postnr");
            $outsheet->setCellValueByColumnAndRow(6,$outRow,"By");
            $outsheet->setCellValueByColumnAndRow(7,$outRow,"Land");
            $outsheet->setCellValueByColumnAndRow(8,$outRow,"Gave/varebeskrivelse");
            $outsheet->setCellValueByColumnAndRow(9,$outRow,"Farve/variant");

            $outsheet->setCellValueByColumnAndRow(10,$outRow,"Dato");
            $outsheet->setCellValueByColumnAndRow(11,$outRow,"Dato");

            $outsheet->setCellValueByColumnAndRow(12,$outRow,"Att");
            $outsheet->setCellValueByColumnAndRow(13,$outRow,"ShopUserID");
            $outsheet->setCellValueByColumnAndRow(14,$outRow,"Username");
            $outsheet->setCellValueByColumnAndRow(15,$outRow,"Password");
            $outRow++;
        }

        // Get shopuser orders
        foreach($orderList as $order) {

            //echo "<pre>".print_r($order,true)."</pre>";
            if(($showDirectUnderCompany && $order["company_id"] == $order["shopuser_company_id"]) || ($showChild && $order["company_id"] != $order["shopuser_company_id"]))
            {

                // Get user data
                $userData = $this->getUserData($order["shopuser_id"], $order["shop_id"]);

                // Find country
                $country = trimgf($order["ship_to_country"]);
                if ($country == "") $country = $userData["land"];

                $country = CountryHelper::codeToCountry(CountryHelper::countryToCode($country));


                // Check telefon
                $phone = trimgf($userData["telefon"]) == "" ? $order["contact_phone"] : $userData["telefon"];
                if($country == "Danmark") {
                    if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "45" || substr($phone,0,3) == "+45")) {
                        if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                        $phone = "+45".$phone;
                    }
                } else if($country == "Sverige") {
                    if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
                        if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                        $phone = "+46".$phone;
                    }
                }

                $date1 = date("Y-m-d",(time()))."-08:30:00";
                $date2 = date("Y-m-d",(time()))."-16:00:00";



                // Output row
                $outsheet->setCellValueByColumnAndRow(1, $outRow, str_replace(";"," ",html_entity_decode($order["ship_to_company"])));
                $outsheet->setCellValueByColumnAndRow(2, $outRow, trimgf($userData["email"]) == "" ? $order["contact_email"] : $userData["email"]);
                $outsheet->setCellValueByColumnAndRow(3, $outRow, $phone);
                $outsheet->setCellValueByColumnAndRow(4, $outRow, $order["ship_to_address"].((trimgf($order["ship_to_address_2"]) != "" && mb_strtolower(trimgf($order["ship_to_address"])) != mb_strtolower(trimgf($order["ship_to_address_2"])))? ", ".$order["ship_to_address_2"] : ""));
                $outsheet->setCellValueByColumnAndRow(5, $outRow,  $order["ship_to_postal_code"]);
                $outsheet->setCellValueByColumnAndRow(6, $outRow,  $order["ship_to_city"]);
                $outsheet->setCellValueByColumnAndRow(7, $outRow, $country);
                $outsheet->setCellValueByColumnAndRow(8, $outRow, $order["model_present_no"].": ".$order["model_name"]);
                $outsheet->setCellValueByColumnAndRow(10,$outRow,$date1);
                $outsheet->setCellValueByColumnAndRow(11,$outRow,$date2);
                $outsheet->setCellValueByColumnAndRow(12, $outRow,  $userData["name"]." (kortnr ".$order["username"].")");
                $outsheet->setCellValueByColumnAndRow(13, $outRow,  $order["shopuser_id"]);

                if($this->includeUserData) {

                    $outsheet->setCellValueByColumnAndRow(14, $outRow,  $userData["username"]);
                    $outsheet->setCellValueByColumnAndRow(15, $outRow,  $userData["password"]);

                }

                $outRow++;

            }
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
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
        exit();

    }


    public function getUserData($shopuserid,$shopid)
    {

        $nameAttributes = ShopMetadata::getNameAttrList();
        $adress1Attributes = ShopMetadata::getAddress1AttrList();
        $adress2Attributes = ShopMetadata::getAddress2AttrList();
        $postnrAttributes = ShopMetadata::getZipAttrList();
        $bynavnAttributes = ShopMetadata::getCityAttrList();
        $emailAttributes = ShopMetadata::getEmailAttrList();
        $phoneAttributes = ShopMetadata::getPhoneAttrList();

        $shopUser = \ShopUser::find($shopuserid);

        $shopuserData = array(
            "name" => "-",
            "address" => "-",
            "address2" => "-",
            "postnr" => "-",
            "bynavn" => "-",
            "land" => $this->getCountry($shopid),
            "telefon" => "",
            "email" => "-",
            "username" => $shopUser->username,
            "password" => $shopUser->password,
            "shopuserid" => $shopUser->id
        );

        $userAttributes = \UserAttribute::find_by_sql("SELECT * FROM user_attribute WHERE shopuser_id = ".$shopuserid);
        foreach($userAttributes as $attribute) {
            if(in_array($attribute->attribute_id,$nameAttributes)) $shopuserData["name"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$adress1Attributes)) $shopuserData["address"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$adress2Attributes)) $shopuserData["address2"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$postnrAttributes)) $shopuserData["postnr"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$bynavnAttributes)) $shopuserData["bynavn"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$emailAttributes)) $shopuserData["email"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$phoneAttributes)) $shopuserData["telefon"] = $attribute->attribute_value;
        }

        // Check address
        if(trimgf($shopuserData["address"]) == "" && trimgf($shopuserData["address2"]) != "") {
            $shopuserData["address"] = $shopuserData["address2"];
            $shopuserData["address2"] = "";
        }

        // Check e-mail
        $shopuserData["email"] = trimgf(str_replace(array(" ",",","@@"),array("",".","@"),$shopuserData["email"]));

        // Check phone no
        $phone = trimgf(str_replace(array(" ","-"),"",$shopuserData["telefon"]));
        if($phone != "") {

            if($shopuserData["land"] == "Sverige") {
                if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    $phone = "+46".$phone;
                    $phone = str_replace(" ","",$phone);
                    if(substr($phone,0,1) != "+") $phone = "+".$phone;
                }
            }
            if($shopuserData["land"] == "Danmark") {
                if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "45" || substr($phone,0,3) == "+45")) {
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    $phone = "+45".$phone;
                    $phone = str_replace(" ","",$phone);
                    if(substr($phone,0,1) != "+") $phone = "+".$phone;
                }
            }

            $shopuserData["telefon"] = $phone;

        }

        // Postnr
        if($shopuserData["land"] == "Sverige") {
            if(strlen(trimgf($shopuserData["postnr"])) == 5) {
                $shopuserData["postnr"] = trimgf($shopuserData["postnr"]);
                $shopuserData["postnr"] = substr($shopuserData["postnr"],0,3)." ".substr($shopuserData["postnr"],3);
            }
        }

        return $shopuserData;
    }

    private function getCountry($shopid)
    {
        return ShopMetadata::getShopCountry($shopid);
    }







    /*
     *
    private function downloadList()
    {

        return $this->downloadList2();

        ob_end_clean();

        $orderList = $this->getShopuserOrderList();

        // Init phpexcel
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $phpExcel->removeSheetByIndex(0);

        // Write header
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
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
                    $phone = (in_array($order["shop_id"],array(1981,1832)) ? "+46" : "+45").$phone;
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
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($phpExcel);
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
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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
                        $phone = (in_array($order["shop_id"], array(1981, 1832)) ? "+46" : "+45") . $phone;
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
            $outsheet->setCellValueByColumnAndRow(10, $outRow,  ucwords($order["username"]));


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
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($phpExcel);
        $objWriter->setDelimiter(";");
        $objWriter->setEnclosure("");
        echo "\xEF\xBB\xBF";
        $objWriter->save('php://output');
        exit();


}
     *
     */

}