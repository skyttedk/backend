<?php

use GFCommon\Model\Access\BackendPermissions;

class sehotelspa
{

    public function dispatch()
    {
        if(BackendPermissions::session()->hasPermission(BackendPermissions::PERMISSION_KORTSHOP) == false) {
            echo "Du har ikke rettigheder til at se denne side";
            return;
        }

        if(isset($_POST["action"]) && $_POST["action"] == "createfile") {
            //$this->createNewList();
        }
        else if(isset($_GET["filetype"]) && isset($_GET["filebatch"]) && $_GET["filetype"] == "hotelspa") {
            $this->getSpecialList($_GET["filebatch"]);
        }
        else {
            //$this->showList();
        }

    }

    private function getHotelSpaSQL() {
        return "((`present`.shop_id = 1832 && present_model.fullalias = 31) || (`present`.shop_id = 1832 && present_model.fullalias = 32) || (`present`.shop_id = 1981 && present_model.fullalias = 32)  || (`present`.shop_id = 1981 && present_model.fullalias = 33))";
    }

    private function getSpecialList($batchname)
    {


        // Init phpexcel
        $phpExcel = new PHPExcel();
        $phpExcel->removeSheetByIndex(0);

        // Write header
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
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
        $sheet->getColumnDimension('K')->setWidth(34);

        $sheet->setCellValueByColumnAndRow(1, 1, "Namn:");
        $sheet->setCellValueByColumnAndRow(2, 1, "Adress:");
        $sheet->setCellValueByColumnAndRow(3, 1, "Postnr:");
        $sheet->setCellValueByColumnAndRow(4, 1, "Stad:");
        $sheet->setCellValueByColumnAndRow(5, 1, "Mail:");
        $sheet->setCellValueByColumnAndRow(6, 1, "Mobil:");
        $sheet->setCellValueByColumnAndRow(7, 1, "Produkt:");
        $sheet->setCellValueByColumnAndRow(8, 1, "Virksomhed:");
        $sheet->getStyle("A1:N1")->getFont()->setBold(true);

        $row=2;

        // Get shopuser orders
        $shopuserorders = $this->getShopUserOrdersInBatch($batchname);
        foreach($shopuserorders as $userorder)
        {

            // Get user data
            $userData = $this->getUserData($userorder["shopuser_id"],$userorder["shop_id"]);

            // Find company
            $company = \Company::find($userorder["company_id"]);
            $userData["address"] = $company->ship_to_address;
            $userData["address2"] = $company->ship_to_address_2;
            $userData["postnr"] = $company->ship_to_postal_code;
            $userData["bynavn"] = $company->ship_to_city;

            $phone = trimgf(str_replace(array(" ","-"),"",$userData["telefon"]));
            if($phone != "") {
                if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
                    if(substr($phone,0,1) === "0") $phone = substr($phone,1);
                    $phone = "+46".$phone;
                }
            }



            // Write data row
            $sheet->setCellValueByColumnAndRow(1, $row, $userData["name"]);
            $sheet->setCellValueByColumnAndRow(2, $row, $userData["address"].((trimgf($userData["address2"]) != "" && mb_strtolower(trimgf($userData["address"])) != mb_strtolower(trimgf($userData["address2"])))? ", ".$userData["address2"] : ""));
            $sheet->setCellValueByColumnAndRow(3, $row, $userData["postnr"]);
            $sheet->setCellValueByColumnAndRow(4, $row, $userData["bynavn"]);
            $sheet->setCellValueByColumnAndRow(5, $row, $userData["email"]);
            $sheet->setCellValueByColumnAndRow(6, $row, $phone);
            $sheet->setCellValueByColumnAndRow(7, $row, $this->getFullAlias($userorder["shop_id"],$userorder["fullalias"]).": ".$userorder["model_name"].(trimgf($userorder["model_no"]) == "" ? "" : ", ".trimgf( $userorder["model_no"])));
            $sheet->setCellValueByColumnAndRow(8, $row, $company->ship_to_company);
            $row++;

        }

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="hotelspa-'.$batchname.'-.xlsx"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);
        $objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter->save('php://output');
        exit();

    }

    private function getFullAlias($shopid,$alias)
    {
        if ($shopid == 1832) {
            return "S3-".$alias;
        } else if ($shopid == 1981) {
            return "S8-".$alias;
        } else if ($shopid == 5117) {
            return "S6-".$alias;
        }else if ($shopid == 4793) {
            return "S3-".$alias;
        } else if ($shopid == 8271) {
            return "SOM-".$alias;
        } else {
            return "#-".$alias;
        }
    }

    private function formatPostalCode($postalCode,$shopid) {

        if($shopid == 1832 || $shopid == 1981 || $shopid == 5117 || $shopid == 4793 || $shopid == 8271) {
            if(strlen(trimgf($postalCode)) == 5) {
                $postalCode = trimgf($postalCode);
                $postalCode = substr($postalCode,0,3)." ".substr($postalCode,3);
            }
            return $postalCode;
        }

    }

    private function getUserData($shopuserid,$shopid)
    {

        $nameAttributes = array(93,722,727,2928,1228,11057,10085,14457,29576);
        $adress1Attributes = array(10755,10759,10763,10767,10751,11668,10747,16275,29589);
        $adress2Attributes = array(10752,10756,10760,10764,10768,11669,10748,16276,29590);
        $postnrAttributes = array(10753,10757,10761,10765,10769,11670,10749,16277,29591);
        $bynavnAttributes = array(10754,10758,10762,10766,10770,11671,10750,16278,29592);
        $emailAttributes = array(92,2929,1229,723,728,11058,10086,14458,29577);
        $phoneAttributes = array(4301,4302,4303,4304,4305,11667,11672,16872,29593);

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
        } else if ($shopid == 8271) {
            $valueAlias = "Sverige";
        } else if ($shopid == 2558) {
            $valueAlias = "Sverige";
        } else if ($shopid == 5117) {
            $valueAlias = "Sverige";
        }

        return $valueAlias;
    }

    private function checkCardDeliveryStatus()
    {
        //SELECT shop_user.* FROM `expire_date`, shop_user WHERE shop_user.expire_date = expire_date.expire_date && shop_user.is_giftcertificate = 1 && expire_date.is_delivery != shop_user.is_delivery ORDER BY `id` DESC
        $sql = "UPDATE `expire_date`, shop_user SET shop_user.is_delivery = expire_date.is_delivery WHERE shop_user.expire_date = expire_date.expire_date && shop_user.is_giftcertificate = 1 && expire_date.is_delivery != shop_user.is_delivery";
        Dbsqli::setSql2($sql);
    }

    private function showList()
    {

        $inWaiting = $this->getInWaiting();
        $this->checkCardDeliveryStatus();
        $notProcessed = $this->getNotProcessed();
        $fileList = $this->getFileList();

        ?><h2>Privatleveringer - hotel og spa - Sverige</h2>
        <div><form method="post" action="">
                Antal klar til pak: <?php echo countgf($notProcessed); ?>, antal afventende: <?php echo countgf($inWaiting) ?><br>
                <br><?php if(count($notProcessed) > 0) { ?><button >Dan fil til <?php echo countgf($notProcessed); ?> ordre</button><?php } ?>
                <input type="hidden" name="action" value="createfile">
            </form></div>
        <table style="width: 100%; max-width: 1000px;" cellpadding="5" cellspacing="5">
            <tr style="font-weight: bold;"><td>Dato</td><td>Antal</td><td>300</td><td>800</td><td>Kode</td><td>Labelliste</td><td>Sumliste</td></tr>
            <?php

            foreach($fileList as $file) {
                echo "<tr>
                    <td>".$file["delivery_print_date"]."</td>
                    <td>".$file["total"]."</td>
                    <td>".$file["shop300"]."</td>
                    <td>".$file["shop400"]."</td>
                    <td>".$file["shop600"]."</td>
                    <td>".$file["shop800"]."</td>
                    <td>".$file["navsync_response"]."</td>
                    <td><a href='index.php?rt=cardshoppluk/sehotelspa&filetype=hotelspa&filebatch=".$file["navsync_response"]."'>hent hotel/spa liste</a></td>
                </tr>";
            }
            ?>
        </table>
        <?php

    }

    private function createNewList()
    {

        $notProcessed = $this->getNotProcessed();
        if(count($notProcessed) == 0) return $this->showList();

        // Navsync identifier
        $pullDate = time();
        $batchid = "sehotelspa".date("ymdhis",$pullDate);
        $count = 0;

        echo "<div style='display: none;'>";

        // Foreach, find by id and set navsync_response and delivery_print_date
        foreach($notProcessed as $shopuserRow) {

            $shopuser = ShopUser::find($shopuserRow->id);
            if($shopuser instanceof ShopUser && $shopuser->id > 0) {

                $shopuser->delivery_print_date = date('d-m-Y H:i:s',$pullDate);
                $shopuser->navsync_response = $batchid;
                $shopuser->save();
                $count++;

                //echo "".$shopuser->id." - ".$shopuser->username." - ".$batchid."<br>";
            }

        }
        echo "</div>";

        if($count > 0) {
            echo "<h2>Oprettede fil med " . $count . " ordre: " . $batchid . "</h2><br>";
            System::connection()->commit();
        }

        $this->showList();
    }


    private function getShopUserOrdersInBatch($batchname)
    {
        $sql = "SELECT `shop_user`.id as shopuser_id, shop_user.shop_id, shop_user.username, `order`.id as order_id, present_model.model_name, present_model.model_no, present_model.model_present_no, present_model.model_id, present_model.fullalias, `order`.order_timestamp, shop_user.delivery_print_date, shop_user.company_id  FROM `shop_user`, `order`, present_model WHERE `shop_user`.navsync_response = '".$batchname."' && shop_user.shop_id IN (1832,1981,2558,5117,4793,8271) && `shop_user`.`blocked` =0 AND `shop_user`.`is_delivery` = 0 AND shop_user.id = `order`.shopuser_id && `order`.present_model_id = present_model.model_id && present_model.language_id = 1 ORDER BY shop_id asc, fullalias ASC";
        return Dbsqli::getSql2($sql);
    }

    private function getFileList()
    {
        $sql = "SELECT count(id) as total, sum(if(shop_id=4793,1,0)) as shop300 , sum(if(shop_id=5117,1,0)) as shop600 , sum(if(shop_id=1832,1,0)) as shop400 , sum(if(shop_id=1981,1,0)) as shop800, navsync_response, delivery_print_date FROM `shop_user` WHERE navsync_response LIKE 'sehotelspa%' && shop_user.shop_id IN (1832,1981,5117) && `shop_user`.`blocked` =0 AND `shop_user`.`is_delivery` = 1 AND `shop_user`.`delivery_print_date` IS NOT NULL GROUP BY navsync_response ORDER BY delivery_print_date DESC";
        return Dbsqli::getSql2($sql);
    }

    private function getNotProcessed()
    {

        // Shop
        $shoplist = array(1832,1981,5117,4793,8271);


        // Make sql for users with orders
        $sql = "SELECT
            `shop_user`.`username`
            , `shop_user`.`id`
            , `shop_user`.`blocked`
            , `shop_user`.`is_delivery`
            , `shop_user`.`delivery_printed`
            , `shop_user`.`expire_date`
            , `order`.`present_model_id`
            , `order`.`present_name`
            , `order`.`present_model_name`
            , `order`.`shop_id`,
            company_order.order_no,
            company_order.company_name
        FROM
            company_order, `order`
            INNER JOIN `shop_user`
                ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ( `shop_user`.shop_id in (".implode(",",$shoplist).") && `shop_user`.`blocked` =0 AND `shop_user`.`is_delivery` = 1 AND `shop_user`.`delivery_print_date` IS NULL) and company_order.id = shop_user.company_order_id AND (`order`.order_timestamp < (NOW() - INTERVAL 26 HOUR))
        && `order`.present_model_id IN (SELECT model_id FROM present_model, present WHERE present.id = present_model.present_id && ".$this->getHotelSpaSQL().")
        ORDER BY company_order.cvr, company_order.order_no, `order`.`present_name`, `order`.`present_model_name`";
        $shopuserorders = ShopUser::find_by_sql($sql);

        return $shopuserorders;

    }

    private function getInWaiting() {

        // Shop
        $shoplist = array(1832,1981,5117,4793,8271);

        // Make sql for users with orders
        $sql = "SELECT
            `shop_user`.`username`
            , `shop_user`.`id`
            , `shop_user`.`blocked`
            , `shop_user`.`is_delivery`
            , `shop_user`.`delivery_printed`
            , `shop_user`.`expire_date`
            , `order`.`present_model_id`
            , `order`.`present_name`
            , `order`.`present_model_name`
            , `order`.`shop_id`,
            company_order.order_no,
            company_order.company_name
        FROM
            company_order, `order`
            INNER JOIN `shop_user`
                ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ( `shop_user`.shop_id in (".implode(",",$shoplist).") && `shop_user`.`blocked` =0 AND `shop_user`.`is_delivery` = 1 AND `shop_user`.`delivery_print_date` IS NULL) and company_order.id = shop_user.company_order_id AND (`order`.order_timestamp > (NOW() - INTERVAL 26 HOUR))
        && `order`.present_model_id IN (SELECT model_id FROM present_model, present WHERE present.id = present_model.present_id && ".$this->getHotelSpaSQL().")
        ORDER BY company_order.cvr, company_order.order_no, `order`.`present_name`, `order`.`present_model_name`";
        $shopuserorders = ShopUser::find_by_sql($sql);

        return $shopuserorders;

    }

}