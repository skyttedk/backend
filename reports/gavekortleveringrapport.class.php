<?php
/*
  rapport over gavekort med leveringsadresse
*/

use GFBiz\Model\Cardshop\ShopMetadata;

class gavekortLeveringRapport Extends reportBaseController{


    public function runse() 
    {

        $isDebug = false;
        $errorlist = array();

        if($_GET['token']!="fj4kdVd21") {
            exit();
        }

        // Shop
        $shoplist = ShopMetadata::getSEShops();

        // Load alias map from shop
        $modelList = PresentModel::find_by_sql("SELECT present.id, present_model.id, present_model.model_id, present.name, present.nav_name, present.alias, present_model.model_present_no, present_model.model_name, present_model.model_no, present.shop_id, present_model.aliasletter, present_model.fullalias FROM `present_model`, present WHERE present.shop_id IN (".implode(",",$shoplist).") && present_model.present_id = present.id && present_model.language_id = 1");
        $modelMap = array();

        foreach($modelList as $model) {
            if(intval($model->model_id) <= 0) $errorlist[] = "Model id not above 0 in present ".$model->id;
            $modelMap[intval($model->model_id)] = $model;
        }

        // Init phpexcel
        $phpExcel = new PHPExcel();
        $phpExcel->removeSheetByIndex(0);

        /*
         * FIRST ARK - PRIVATLEVERINGER
         */

        // Write header
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->setTitle("Privatlevering");
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(12);
        $sheet->getColumnDimension('M')->setWidth(12);
        $sheet->getColumnDimension('N')->setWidth(20);

        $sheet->setCellValueByColumnAndRow(1, 1, "private name");
        $sheet->setCellValueByColumnAndRow(2, 1, "ship_to_address");
        $sheet->setCellValueByColumnAndRow(3, 1, "ship_to_address2");
        $sheet->setCellValueByColumnAndRow(4, 1, "postnr");
        $sheet->setCellValueByColumnAndRow(5, 1, "bynavn");
        $sheet->setCellValueByColumnAndRow(6, 1, "land");
        $sheet->setCellValueByColumnAndRow(7, 1, "telefon");
        $sheet->setCellValueByColumnAndRow(8, 1, "email");
        $sheet->setCellValueByColumnAndRow(9, 1, "gave");
        $sheet->setCellValueByColumnAndRow(10, 1, "model");
        $sheet->setCellValueByColumnAndRow(11, 1, "alias");
        $sheet->setCellValueByColumnAndRow(12, 1, "kort nr");
        $sheet->setCellValueByColumnAndRow(13, 1, "bs nr");
        $sheet->setCellValueByColumnAndRow(14, 1, "virksomhed");
        $sheet->getStyle("A1:N1")->getFont()->setBold(true);
        $row=2;

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
        WHERE ( `shop_user`.shop_id in (".implode(",",$shoplist).") && `shop_user`.`blocked` =0 AND `shop_user`.`is_delivery` = 1 AND `shop_user`.`delivery_print_date` IS NULL) and company_order.id = shop_user.company_order_id
        ORDER BY company_order.cvr, company_order.order_no, `order`.`present_name`, `order`.`present_model_name`";

        $shopuserorders = ShopUser::find_by_sql($sql);
        $orderList = array();

        // Process each order
        foreach($shopuserorders as $shopuserorder) {

            // Get shopuser data
            $shopuserData = $this->getUserData($shopuserorder->id,$shopuserorder->shop_id);

            // Get present data
            $presentData = array("present_name" => "UKENDT", "present_model" => "UKENDT", "alias" => "UKENDT");
            if(isset($modelMap[intval($shopuserorder->present_model_id)])) {

                $presentModel = $modelMap[intval($shopuserorder->present_model_id)];
                $presentData["present_name"] = $presentModel->model_name;
                $presentData["present_model"] = $presentModel->model_no;

                if(trimgf($presentModel->fullalias) == "") {
                    $errorlist[] = "No alias for model: ".$shopuserorder->present_model_id;
                    $presentData["alias"] = "Ukendt";
                } else {
                    $presentData["alias"] = $this->fullalias($shopuserorder->shop_id,$presentModel->fullalias);
                }
            }
            else {
                $errorlist[] = "Could not find model id: ".$shopuserorder->present_model_id." in models for shopuser: ".$shopuserorder->id;
            }

            $orderList[] = array("orderdata" => $shopuserorder, "userdata" => $shopuserData, "presentdata" => $presentData);

        }

        // Order data
        for($i=0;$i<count($orderList);$i++) {
            for($j=$i+1;$j<count($orderList);$j++) {

                if(
                    intval($orderList[$i]["presentdata"]["alias"]) > intval($orderList[$j]["presentdata"]["alias"]) ||
                    (intval($orderList[$i]["presentdata"]["alias"]) == intval($orderList[$j]["presentdata"]["alias"]) && strcmp($orderList[$i]["presentdata"]["alias"],$orderList[$j]["presentdata"]["alias"]) > 0) ||
                    (trimgf($orderList[$i]["presentdata"]["alias"]) == trimgf($orderList[$j]["presentdata"]["alias"]) && strcmp($orderList[$i]["orderdata"]["company_name"],$orderList[$j]["orderdata"]["company_name"]) > 0)
                ) {
                    $tmp = $orderList[$i];
                    $orderList[$i] = $orderList[$j];
                    $orderList[$j] = $tmp;
                }

            }
        }

       // Print
        foreach($orderList as $order)
        {
            $shopuserorder = $order["orderdata"];
            $shopuserData = $order["userdata"];
            $presentData = $order["presentdata"];
            $sheet->setCellValueByColumnAndRow(1, $row, $shopuserData["name"]);
            $sheet->setCellValueByColumnAndRow(2, $row, $shopuserData["address"]);
            $sheet->setCellValueByColumnAndRow(3, $row, $shopuserData["address2"]);
            $sheet->setCellValueByColumnAndRow(4, $row, $shopuserData["postnr"]);
            $sheet->setCellValueByColumnAndRow(5, $row, $shopuserData["bynavn"]);
            $sheet->setCellValueByColumnAndRow(6, $row, $shopuserData["land"]);
            $sheet->setCellValueByColumnAndRow(7, $row, $shopuserData["telefon"]);
            $sheet->setCellValueByColumnAndRow(8, $row, $shopuserData["email"]);
            $sheet->setCellValueByColumnAndRow(9, $row, $presentData["present_name"]);
            $sheet->setCellValueByColumnAndRow(10, $row, $presentData["present_model"]);
            $sheet->setCellValueByColumnAndRow(11, $row, $presentData["alias"]);
            $sheet->setCellValueByColumnAndRow(12, $row, $shopuserorder->username);
            $sheet->setCellValueByColumnAndRow(13, $row, $shopuserorder->order_no);
            $sheet->setCellValueByColumnAndRow(14, $row, $shopuserorder->company_name);
            $row++;
        }

        /*
         * THIRD SHEET - Warning sheet
         */

        if(count($errorlist) > 0) {

            $sheet = $phpExcel->createSheet();
            $sheet->setTitle("Warnings (".countgf($errorlist).")");
            $sheet->getColumnDimension('A')->setWidth(40);
            $sheet->setCellValueByColumnAndRow(1, 1, "Fejlbeskeder i tr�k");
            $row = 2;

            foreach($errorlist as $error) {
                $sheet->setCellValueByColumnAndRow(1, $row,$error);
                $row++;
            }

        }

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="gavermedlevering-.xlsx"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);
        $objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter->save('php://output');
        exit();

    }


    public function runnewprivateDelivery($lang)
    {

        $isDebug = false;
        $errorlist = array();

        if($_GET['token']!="fj4kdVd21") {
            exit();
        }

        // Shop
        if($lang == "da") {
            $shoplist = ShopMetadata::getDAShops();
        } else if($lang == "se") {
            $shoplist = ShopMetadata::getSEShops();
        } else if($lang == "no") {
            $shoplist = ShopMetadata::getNOShops();
        } else {
            $shoplist = ShopMetadata::getAllShopList();
        }


        // Check deadline
        $deadline = isset($_GET["deadline"]) ? $_GET["deadline"] : "";
        $expireDate = expireDate::getByExpireDate($deadline);
        if($expireDate == null) { echo "Ugyldig deadline"; return; }

        // Load alias map from shop
        $modelList = PresentModel::find_by_sql("SELECT present.id, present_model.id, present_model.model_id, present.name, present.nav_name, present.alias, present_model.model_present_no, present_model.model_name, present_model.model_no, present.shop_id, present_model.aliasletter, present_model.fullalias FROM `present_model`, present WHERE present.shop_id IN (".implode(",",$shoplist).") && present_model.present_id = present.id && present_model.language_id = 1");
        $modelMap = array();

        foreach($modelList as $model) {
            if(intval($model->model_id) <= 0) $errorlist[] = "Model id not above 0 in present ".$model->id;
            $modelMap[intval($model->model_id)] = $model;
        }

        // Init phpexcel
        $phpExcel = new PHPExcel();
        $phpExcel->removeSheetByIndex(0);

        /*
         * FIRST ARK - PRIVATLEVERINGER
         */

        // Write header
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->setTitle("Privatlevering");
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(12);
        $sheet->getColumnDimension('M')->setWidth(12);
        $sheet->getColumnDimension('N')->setWidth(20);

        $sheet->setCellValueByColumnAndRow(1, 1, "private name");
        $sheet->setCellValueByColumnAndRow(2, 1, "ship_to_address");
        $sheet->setCellValueByColumnAndRow(3, 1, "ship_to_address2");
        $sheet->setCellValueByColumnAndRow(4, 1, "postnr");
        $sheet->setCellValueByColumnAndRow(5, 1, "bynavn");
        $sheet->setCellValueByColumnAndRow(6, 1, "land");
        $sheet->setCellValueByColumnAndRow(7, 1, "telefon");
        $sheet->setCellValueByColumnAndRow(8, 1, "email");
        $sheet->setCellValueByColumnAndRow(9, 1, "gave");
        $sheet->setCellValueByColumnAndRow(10, 1, "model");
        $sheet->setCellValueByColumnAndRow(11, 1, "alias");
        $sheet->setCellValueByColumnAndRow(12, 1, "kort nr");
        $sheet->setCellValueByColumnAndRow(13, 1, "bs nr");
        $sheet->setCellValueByColumnAndRow(14, 1, "virksomhed");
        $sheet->setCellValueByColumnAndRow(15, 1, "shop");
        $sheet->getStyle("A1:N1")->getFont()->setBold(true);
        $row=2;

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
        WHERE ( `shop_user`.shop_id in (".implode(",",$shoplist).") && shop_user.expire_date = '".$deadline."' && `shop_user`.`blocked` =0
            AND `shop_user`.`is_delivery` = 1 
            AND `shop_user`.`delivery_print_date` IS NULL
             ) and company_order.id = shop_user.company_order_id
        ORDER BY company_order.cvr, company_order.order_no, `order`.`present_name`, `order`.`present_model_name`";
        $shopuserorders = ShopUser::find_by_sql($sql);


        foreach($shopuserorders as $shopuserorder) {

            // Get shopuser data
            $shopuserData = $this->getUserData($shopuserorder->id,$shopuserorder->shop_id);
            $presentData = array("present_name" => "UKENDT", "present_model" => "UKENDT", "alias" => "UKENDT");

            if(isset($modelMap[intval($shopuserorder->present_model_id)])) {

                $presentModel = $modelMap[intval($shopuserorder->present_model_id)];

                $presentData["present_name"] = $presentModel->model_name;
                $presentData["present_model"] = $presentModel->model_no;

                if(trimgf($presentModel->fullalias) == "") {
                    $errorlist[] = "No alias for model: ".$shopuserorder->present_model_id;
                    $presentData["alias"] = "Ukendt";
                } else {
                    $presentData["alias"] = $this->fullalias($shopuserorder->shop_id,$presentModel->fullalias);
                }
            }
            else {
                $errorlist[] = "Could not find model id: ".$shopuserorder->present_model_id." in models for shopuser: ".$shopuserorder->id;
            }

            $sheet->setCellValueByColumnAndRow(1, $row, $shopuserData["name"]);
            $sheet->setCellValueByColumnAndRow(2, $row, $shopuserData["address"]);
            $sheet->setCellValueByColumnAndRow(3, $row, $shopuserData["address2"]);
            $sheet->setCellValueByColumnAndRow(4, $row, $shopuserData["postnr"]);
            $sheet->setCellValueByColumnAndRow(5, $row, $shopuserData["bynavn"]);
            $sheet->setCellValueByColumnAndRow(6, $row, $shopuserData["land"]);
            $sheet->setCellValueByColumnAndRow(7, $row, $shopuserData["telefon"]);
            $sheet->setCellValueByColumnAndRow(8, $row, $shopuserData["email"]);
            $sheet->setCellValueByColumnAndRow(9, $row, $presentData["present_name"]);
            $sheet->setCellValueByColumnAndRow(10, $row, $presentData["present_model"]);
            $sheet->setCellValueByColumnAndRow(11, $row, $presentData["alias"]);
            $sheet->setCellValueByColumnAndRow(12, $row, $shopuserorder->username);
            $sheet->setCellValueByColumnAndRow(13, $row, $shopuserorder->order_no);
            $sheet->setCellValueByColumnAndRow(14, $row, $shopuserorder->company_name);
            $sheet->setCellValueByColumnAndRow(15, $row, $this->getShopName($shopuserorder->shop_id));
            $row++;

        }

        /*
         * SECOND SHEET - AUTOGAVER
         */

        $sheet = $phpExcel->createSheet();
        $sheet->setTitle("Autovalg");
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(12);
        $sheet->getColumnDimension('M')->setWidth(12);
        $sheet->getColumnDimension('N')->setWidth(20);

        $sheet->setCellValueByColumnAndRow(1, 1, "virksomhed levering");
        $sheet->setCellValueByColumnAndRow(2, 1, "ship_to_address");
        $sheet->setCellValueByColumnAndRow(3, 1, "ship_to_address2");
        $sheet->setCellValueByColumnAndRow(4, 1, "postnr");
        $sheet->setCellValueByColumnAndRow(5, 1, "bynavn");
        $sheet->setCellValueByColumnAndRow(6, 1, "land");
        $sheet->setCellValueByColumnAndRow(7, 1, "telefon");
        $sheet->setCellValueByColumnAndRow(8, 1, "email");
        $sheet->setCellValueByColumnAndRow(9, 1, "gave");
        $sheet->setCellValueByColumnAndRow(10, 1, "model");
        $sheet->setCellValueByColumnAndRow(11, 1, "alias");
        $sheet->setCellValueByColumnAndRow(12, 1, "kort nr");
        $sheet->setCellValueByColumnAndRow(13, 1, "bs nr");
        $sheet->setCellValueByColumnAndRow(14, 1, "Virksomhed");
        $sheet->setCellValueByColumnAndRow(15, 1, "shop");
        $sheet->getStyle("A1:N1")->getFont()->setBold(true);
        $row=2;

        // Make sql for users with orders
        $sql = "SELECT
            `shop_user`.`username`
            , `shop_user`.`id`
            , `shop_user`.`blocked`
            , `shop_user`.`is_delivery`
            , `shop_user`.`delivery_printed`
            , `shop_user`.`expire_date`,
            shop_user.shop_id,
            company_order.id,
            company_order.order_no,
            company_order.company_name,
            company_order.ship_to_company,
            company_order.ship_to_address,
            company_order.ship_to_address_2,
            company_order.ship_to_postal_code,
            company_order.ship_to_city,
            company_order.contact_email,
            company_order.contact_phone
        FROM
            company_order, `shop_user`
        WHERE ( `shop_user`.shop_id in (".implode(",",$shoplist).") && shop_user.expire_date = '".$deadline."' && `shop_user`.`blocked` =0
            AND `shop_user`.`is_delivery` = 1 
            AND `shop_user`.`delivery_print_date` IS NULL
             ) and company_order.id = shop_user.company_order_id and shop_user.id NOT IN (SELECT shopuser_id FROM `order`)
        ORDER BY company_order.cvr, company_order.order_no, `shop_user`.`username`";
        $shopusernoorders = ShopUser::find_by_sql($sql);

        foreach($shopusernoorders as $shopusernoorder) {

            // Get shopuser data
            $shopuserData = array(
                "name" => $shopusernoorder->company_name,
                "address" => $shopusernoorder->ship_to_address,
                "address2" => $shopusernoorder->ship_to_address_2,
                "postnr" => $shopusernoorder->ship_to_postal_code,
                "bynavn" => $shopusernoorder->ship_to_city,
                "land" => $this->getCountry($shopusernoorder->shop_id),
                "telefon" => $shopusernoorder->contact_phone,
                "email" => $shopusernoorder->contact_email
            );

            $presentData = array("present_name" => "Autogave", "present_model" => "", "alias" => $this->fullalias($shopusernoorder->shop_id,"0"));

            $sheet->setCellValueByColumnAndRow(1, $row, $shopuserData["name"]);
            $sheet->setCellValueByColumnAndRow(2, $row, $shopuserData["address"]);
            $sheet->setCellValueByColumnAndRow(3, $row, $shopuserData["address2"]);
            $sheet->setCellValueByColumnAndRow(4, $row, $shopuserData["postnr"]);
            $sheet->setCellValueByColumnAndRow(5, $row, $shopuserData["bynavn"]);
            $sheet->setCellValueByColumnAndRow(6, $row, $shopuserData["land"]);
            $sheet->setCellValueByColumnAndRow(7, $row, $shopuserData["telefon"]);
            $sheet->setCellValueByColumnAndRow(8, $row, $shopuserData["email"]);
            $sheet->setCellValueByColumnAndRow(9, $row, $presentData["present_name"]);
            $sheet->setCellValueByColumnAndRow(10, $row, $presentData["present_model"]);
            $sheet->setCellValueByColumnAndRow(11, $row, $presentData["alias"]);
            $sheet->setCellValueByColumnAndRow(12, $row, $shopusernoorder->username);
            $sheet->setCellValueByColumnAndRow(13, $row, $shopusernoorder->order_no);
            $sheet->setCellValueByColumnAndRow(14, $row, $shopusernoorder->company_name);
            $sheet->setCellValueByColumnAndRow(15, $row, $this->getShopName($shopusernoorder->shop_id));
            $row++;

        }


        /*
         * THIRD SHEET - Warning sheet
         */

        if(count($errorlist) > 0) {

            $sheet = $phpExcel->createSheet();
            $sheet->setTitle("Warnings (".countgf($errorlist).")");
            $sheet->getColumnDimension('A')->setWidth(40);
            $sheet->setCellValueByColumnAndRow(1, 1, "Fejlbeskeder i tr�k");
            $row = 2;

            foreach($errorlist as $error) {
                $sheet->setCellValueByColumnAndRow(1, $row,$error);
                $row++;
            }

        }

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="gavermedlevering-'.$deadline.'.xlsx"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(0);
        $objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter->save('php://output');
        exit();

    }

    private function getUserData($shopuserid,$shopid)
    {

        $nameAttributes = ShopMetadata::getNameAttrList();
        $adress1Attributes = ShopMetadata::getAddress1AttrList();
        $adress2Attributes = ShopMetadata::getAddress2AttrList();
        $postnrAttributes = ShopMetadata::getZipAttrList();
        $bynavnAttributes = ShopMetadata::getCityAttrList();
        $emailAttributes = ShopMetadata::getEmailAttrList();
        $phoneAttributes = ShopMetadata::getPhoneAttrList();

        /*
        $nameAttributes = array(93,722,727,2928,1228);
        $adress1Attributes = array(10755,10759,10763,10767,10751);
        $adress2Attributes = array(10752,10756,10760,10764,10768);
        $postnrAttributes = array(10753,10757,10761,10765,10769);
        $bynavnAttributes = array(10754,10758,10762,10766,10770);
        $emailAttributes = array(92,2929,1229,723,728);
        $phoneAttributes = array(4301,4302,4303,4304,4305);
        */

        $shopuserData = array(
            "name" => "-",
            "address" => "-",
            "address2" => "-",
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
            if(in_array($attribute->attribute_id,$postnrAttributes)) $shopuserData["postnr"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$bynavnAttributes)) $shopuserData["bynavn"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$emailAttributes)) $shopuserData["email"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$phoneAttributes)) $shopuserData["telefon"] = $attribute->attribute_value;
            
        }
        
        return $shopuserData;
    }


    private $shopmap = null;
    private function getShopName($shopid) {
        if($this->shopmap == null) {
            $cardshopsettings = CardshopSettings::find('all');
            $this->shopmap = array();
            foreach($cardshopsettings as $css) {
                $this->shopmap[$css->shop_id] = $css->concept_code;
            }
        }
        return isset($this->shopmap[$shopid]) ? $this->shopmap[$shopid] : "Ukendt";
    }

    private function getCountry($shopid) {

        return ShopMetadata::getShopCountry($shopid);
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
        } else if ($shopid == 2550) {
            $valueAlias = "Norge";
        } else if ($shopid == 2549) {
            $valueAlias = "Sverige";
        }

        return $valueAlias;
        */
    }

    private function getvaluealias($shopid)
    {

        return ShopMetadata::getShopValueAlias($shopid);
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
        } else if ($shopid == 2550) {
            $valueAlias = "2";
        } else if ($shopid == 2549) {
            $valueAlias = "BRA-";
        } else if ($shopid == 575) {
            $valueAlias = "D-";
        } else if ($shopid == 248) {
            $valueAlias = "8";
        } else if ($shopid == 1832) {
            $valueAlias = "S3-";
        } else if ($shopid == 1981) {
            $valueAlias = "S8-";
        } if ($shopid == 2558) {
            $valueAlias = "S12-";
        }

        return $valueAlias;
*/
    }

    private function fullalias($shopid, $alias)
    {
        return $this->getvaluealias($shopid) . (strlen(intval($alias)) == 1 ? "0" : "") . $alias;
    }

    public function runno() {



        if($_GET['token']!="fj4kdVd21") {
            exit();
        }
      
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gavermedlevering-'.date("d-m-Y").'.csv');

        // Check deadline
        $deadline = isset($_GET["deadline"]) ? $_GET["deadline"] : "";
        $expireDate = expireDate::getByExpireDate($deadline);
        if($expireDate == null) { echo "Ugyldig deadline"; return; }
        
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        $shopusers = ShopUser::find_by_sql("SELECT
            `shop_user`.`username`
            , `shop_user`.`id`
            , `shop_user`.`blocked`
            , `shop_user`.`is_delivery`
            , `shop_user`.`delivery_printed`
            , `shop_user`.`expire_date`
            , `order`.`present_name`
            , `order`.`present_model_name`
            , `order`.`shop_id`
        FROM
            `order`
            INNER JOIN `shop_user`
                ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ( `shop_user`.shop_id in (574,57,272,58,59) && shop_user.expire_date = '".$deadline."' && `shop_user`.`blocked` =0
            AND `shop_user`.`is_delivery` = 1 
            AND `shop_user`.`delivery_print_date` IS NULL
             )
        ORDER BY `order`.`shop_id` ,`order`.`present_name` ASC;");

        //Header
        $data = [];
        $linenr = 0;
        $pullDate = time();

        $nameIndex = 0;
        $emailIndex = 1;
        $phoneIndex = 2;
        $addressIndex = 3;
        $zipIndex = 4;
        $cityIndex = 5;
        $countryIndex = 6;
        $vareIndex =  7;
        $modelIndex = 8;

        fwrite($output,'Navn;');
        fwrite($output,'Email;');
        fwrite($output,'Mobil;');
        fwrite($output,'Adresse;');
        fwrite($output,'Postnr;');
        fwrite($output,'By;');
        fwrite($output,'Land;');
        fwrite($output,'Varebeskrivelse;');
        fwrite($output,'Farve/model/version;');
        fwrite($output,"\n");

        if(count($shopusers)>0) {

            $linenr++;
            $data[$linenr][0] = "";
            $data[$linenr][1] = "";
            $data[$linenr][2] = "";
            $data[$linenr][3] = "";
            $data[$linenr][4] = "";
            $data[$linenr][5] = "";
            $data[$linenr][6] = "Norge";
            $data[$linenr][7] = "";
            $data[$linenr][8] = "";

            foreach($shopusers as $shopuser)
            {

                $data[$linenr][$vareIndex] = $this->encloseWithQuotes(utf8_decode($shopuser->present_name));
                $data[$linenr][$modelIndex] = $this->encloseWithQuotes(utf8_decode(str_replace('###',' - ',$shopuser->present_model_name)));

                $userattributes = UserAttribute::all(array('shopuser_id' => $shopuser->id));

                foreach($userattributes as $attribute)
                {
                    if($attribute->is_name==1){
//                      fwrite($output,$this->encloseWithQuotes(utf8_decode($attribute->attribute_value)).';');
                        $data[$linenr][$nameIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                    }
                }
                foreach($userattributes as $attribute)
                {
                    if($attribute->is_email==1){
                        $data[$linenr][5] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
//                      fwrite($output,$this->encloseWithQuotes(utf8_decode($attribute->attribute_value)).';');
                    }
                }

                foreach($userattributes as $attribute)
                {

                    if($attribute->is_password==0 AND $attribute->is_username==0 AND $attribute->is_email==0 AND $attribute->is_name==0){
                        // var_dump($attribute);
                        $inCsv = false;

                        // Fra danske
                        // adresse      751      588     596     604
                        // address2    752      589     597     605
                        // post       753     590  598    606
                        // by   754      591      599      607
                        // telf        767         761         763        765

                        // Adresse
                        if($attribute->attribute_id == "10755" || $attribute->attribute_id == "10759" || $attribute->attribute_id == "10763" || $attribute->attribute_id == "10767" || $attribute->attribute_id == "10751"){
                            $data[$linenr][$addressIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                        }

                        // Adresse 2
                        if($attribute->attribute_id == "10752" || $attribute->attribute_id == "10756" || $attribute->attribute_id == "10760" || $attribute->attribute_id == "10764"  || $attribute->attribute_id == "10768"){
                            $val = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                            if(trimgf($val) != "")$data[$linenr][$addressIndex] .= (($data[$linenr][$addressIndex] != "") ? "," : "").$val;
                        }
                        if($attribute->attribute_id == "10753" || $attribute->attribute_id == "10757" || $attribute->attribute_id == "10761" || $attribute->attribute_id == "10765" || $attribute->attribute_id == "10769"){
                            $data[$linenr][$zipIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                        }
                        if($attribute->attribute_id == "10754" || $attribute->attribute_id == "10758" || $attribute->attribute_id == "10762" || $attribute->attribute_id == "10766" || $attribute->attribute_id == "10770"){
                            $data[$linenr][$cityIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                        }
                        if($attribute->attribute_id == "4301" || $attribute->attribute_id == "4302" || $attribute->attribute_id == "4303" || $attribute->attribute_id == "4304" || $attribute->attribute_id == "4305"){
                            $data[$linenr][$phoneIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                        }

                    }
                }

                fwrite($output,$data[$linenr][0].';');
                fwrite($output,$data[$linenr][1].';');
                fwrite($output,$data[$linenr][2].';');
                fwrite($output,$data[$linenr][3].';');
                fwrite($output,$data[$linenr][4].';');
                fwrite($output,$data[$linenr][5].';');
                fwrite($output,$data[$linenr][6].';');
                fwrite($output,$data[$linenr][7].';');
                fwrite($output,$data[$linenr][8].';');
                fwrite($output,"\n");

                // Update shopuser
                $shopuser2 = ShopUser::find($shopuser->id);
                // $shopuser2->delivery_printed = 1;
                // $shopuser2->delivery_printed = 1;
                $shopuser2->delivery_print_date = date('d-m-Y H:i:s',$pullDate);
                //$shopuser2->save();

            }
        }

        fwrite($output,"\n");
        //System::connection()->commit();

    }
    
    public function run() {

      if($_GET['token']!="dit574")
          die('dead');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gavermedlevering-'.date("d-m-Y").'.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        $shopusers = ShopUser::find_by_sql("SELECT
            `shop_user`.`username`
            , `shop_user`.`id`
            , `shop_user`.`blocked`
            , `shop_user`.`is_delivery`
            , `shop_user`.`delivery_printed`
            , `shop_user`.`expire_date`
            , `order`.`present_name`
            , `order`.`present_model_name`
            , `order`.`shop_id`
        FROM
            `order`
            INNER JOIN `shop_user`
                ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ( `shop_user`.`blocked` =0
            AND `shop_user`.`is_delivery` = 1 
            AND `shop_user`.`delivery_print_date` IS NULL
             )
        ORDER BY `order`.`shop_id` ,`order`.`present_name` ASC;");

        //AND `shop_user`.`delivery_printed` = 0

       //Header
       $data = [];
       $linenr = 0;
       $pullDate = time();
                 
        $nameIndex = 0;
        $emailIndex = 1;
        $phoneIndex = 2;
        $addressIndex = 3;
        $zipIndex = 4;
        $cityIndex = 5;
        $countryIndex = 6;
        $vareIndex =  7;
        $modelIndex = 8;                 
                 
       fwrite($output,'Navn;');
       fwrite($output,'Email;');
       fwrite($output,'Mobil;');
       fwrite($output,'Adresse;');
       fwrite($output,'Postnr;');
       fwrite($output,'By;');
       fwrite($output,'Land;');                              
       fwrite($output,'Varebeskrivelse;');
       fwrite($output,'Farve/model/version;');
       fwrite($output,"\n");

       if(count($shopusers)>0) {
       
               $linenr++;
             $data[$linenr][0] = "";
             $data[$linenr][1] = "";
             $data[$linenr][2] = "";
             $data[$linenr][3] = "";
             $data[$linenr][4] = "";
             $data[$linenr][5] = "";
             $data[$linenr][6] = "Danmark";
             $data[$linenr][7] = "";
             $data[$linenr][8] = "";

          
          foreach($shopusers as $shopuser)
          {

               $shop = Shop::find($shopuser->shop_id);
        
                $data[$linenr][$vareIndex] = $this->encloseWithQuotes(utf8_decode($shopuser->present_name));
                $data[$linenr][$modelIndex] = $this->encloseWithQuotes(utf8_decode(str_replace('###',' - ',$shopuser->present_model_name)));
              
                // F?rst skal v?re navn
               $userattributes = UserAttribute::all(array('shopuser_id' => $shopuser->id));

                foreach($userattributes as $attribute)
        	     {
                    if($attribute->is_name==1){
//                      fwrite($output,$this->encloseWithQuotes(utf8_decode($attribute->attribute_value)).';');
                           $data[$linenr][$nameIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                    }
                 }
                foreach($userattributes as $attribute)
        	     {
                    if($attribute->is_email==1){
                      $data[$linenr][5] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
//                      fwrite($output,$this->encloseWithQuotes(utf8_decode($attribute->attribute_value)).';');
                    }
                 }

               foreach($userattributes as $attribute)
        	     {

                    if($attribute->is_password==0 AND $attribute->is_username==0 AND $attribute->is_email==0 AND $attribute->is_name==0){
                       // var_dump($attribute);
                       $inCsv = false;

                       // adresse      751      588     596     604
                       // address2    752      589     597     605
                       // post       753     590  598    606
                       // by   754      591      599      607
                       // telf        767         761         763        765

                       if($attribute->attribute_id == "751" || $attribute->attribute_id == "588" || $attribute->attribute_id == "596" || $attribute->attribute_id == "604"){
                           $data[$linenr][$addressIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                       }
                       if($attribute->attribute_id == "752" || $attribute->attribute_id == "589" || $attribute->attribute_id == "597" || $attribute->attribute_id == "605"){
                           $val = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value)); 
                           if(trimgf($val) != "")$data[$linenr][$addressIndex] .= (($data[$linenr][$addressIndex] != "") ? "," : "").$val;
                       }
                       if($attribute->attribute_id == "753" || $attribute->attribute_id == "590" || $attribute->attribute_id == "598" || $attribute->attribute_id == "606"){
                           $data[$linenr][$zipIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                       }
                       if($attribute->attribute_id == "754" || $attribute->attribute_id == "591" || $attribute->attribute_id == "599" || $attribute->attribute_id == "607"){
                           $data[$linenr][$cityIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));
                       }
                       if($attribute->attribute_id == "767" || $attribute->attribute_id == "761" || $attribute->attribute_id == "763" || $attribute->attribute_id == "765"){
                           $data[$linenr][$phoneIndex] = $this->encloseWithQuotes(utf8_decode($attribute->attribute_value));   
                       }

         //             fwrite($output,$this->encloseWithQuotes(utf8_decode($attribute->attribute_value)).';');
                    }
                 }

                fwrite($output,$data[$linenr][0].';');
                fwrite($output,$data[$linenr][1].';');
               fwrite($output,$data[$linenr][2].';');
               fwrite($output,$data[$linenr][3].';');
               fwrite($output,$data[$linenr][4].';');
                fwrite($output,$data[$linenr][5].';');
                fwrite($output,$data[$linenr][6].';');
                fwrite($output,$data[$linenr][7].';');
                fwrite($output,$data[$linenr][8].';');




                fwrite($output,"\n");
                $shopuser2 = ShopUser::find($shopuser->id);
                // $shopuser2->delivery_printed = 1;
                // $shopuser2->delivery_printed = 1;
                $shopuser2->delivery_print_date = date('d-m-Y H:i:s',$pullDate);
                $shopuser2->save();

           }
       }

       fwrite($output,"\n");
    System::connection()->commit();

 }
function encloseWithQuotes($value)
{
    if (empty($value)) {
        return "";
    }
    return $value = str_replace('"', '""', $value);
    //return '="'.$value.'"';
}}
?>