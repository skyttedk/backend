<?php

use GFCommon\Model\Access\BackendPermissions;

class CardShopPlukReport
{

    public function getError()
    {return $this->error;}

    /**************** REPORT DISPATCHER *************************/

    public function pluk()
    {

        if (BackendPermissions::session()->hasPermission(BackendPermissions::PERMISSION_KORT_PLUKLISTER) == false) {
            echo "Du har ikke rettigheder til at se denne side";
            return;
        }

        ini_set('memory_limit', '2000M');
        set_time_limit(40 * 60);

        // FIND INPUTS
        $action = isset($_POST["action"]) ? $_POST["action"] : "";
        $shopid = intval(isset($_POST["shopid"]) ? $_POST["shopid"] : "");
        $expire = trimgf(isset($_POST["expire"]) ? $_POST["expire"] : "");

        $wrapped = trimgf(isset($_POST["wrapped"]) ? $_POST["wrapped"] : "");
        $useWrapped = ($wrapped === "1" || $wrapped === "0");
        $isWrapped = ($wrapped === "1");

        $carryup = trimgf(isset($_POST["carryup"]) ? $_POST["carryup"] : "");
        $useCarryup = ($carryup === "1" || $carryup === "0");
        $isCarryup = ($carryup === "1");

        // DISPATCH ACTIONS
        if ($action == "presentlist") {
            return $this->presentlist($shopid, $expire, $useWrapped, $isWrapped, $useCarryup, $isCarryup);
        } else if ($action == "fetch") {
            return $this->plukliste($shopid, $expire, $useWrapped, $isWrapped, $useCarryup, $isCarryup);
        } else if ($action == "reminderlist") {
            return $this->remindermaillist($shopid, $expire);
        } else if ($action == "customlist") {
            return $this->customList($shopid, $expire);
        }

    }

    /******************** PLUKLISTE ***************************/

    private function plukliste($shopid, $expire, $useWrapped, $isWrapped, $useCarryup, $isCarryup)
    {

        ob_start();

        $isDebug = false;

        // Load basic info
        $language = $this->shoptolang($shopid);
        $valueAlias = $this->getvaluealias($shopid);
        $autovalgName = $this->getautovalgname($shopid);
        $shop = Shop::find($shopid);

        if ($isDebug) {
            echo "<pre>" . print_r(array("shopid" => $shopid, "expire" => $expire, "useWrapped" => $useWrapped, "isWrapped" => $isWrapped, "useCarryup" => $useCarryup, "isCarryup" => $isCarryup, "language" => $language, "valueAlias" => $valueAlias, "autovalgName" => $autovalgName), true) . "</pre><br>";
        }

        // Load gift wrap
        $companyOrders = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE is_cancelled = 0 && giftwrap = 1");
        $giftWrapMap = array();
         $giftWrapOrderMap = array();
        foreach ($companyOrders as $co) {
            $giftWrapMap[$co->company_id] = true;
            $giftWrapOrderMap[$co->id] = true;
        }

        // Load spe_lev map (carryup)
        $companyOrders = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE is_cancelled = 0 && (gift_spe_lev = 1 OR (spdealtxt != '' and spdealtxt IS NOT NULL))");
        $carryUpMap = array();          
        foreach ($companyOrders as $co) {
            $carryUpMap[$co->company_id] = true;
        }

        // DO NOT USE RAPPORT NOTE ANYMORE: (TRIM(TRIM('\n' FROM rapport_note)) != '' AND rapport_note IS NOT NULL) OR
        $carryUpCompanies = Company::find_by_sql("SELECT * FROM `company` WHERE  (TRIM(TRIM('\n' FROM internal_note)) != '' AND internal_note IS NOT NULL)");
        foreach ($carryUpCompanies as $ccom) {
            $carryUpMap[$ccom->id] = true;
        }

        if ($isDebug) {
            echo "Loaded companyorders: " . countgf($companyOrders) . " loaded, " . countgf($giftwrapMap) . " giftwrap set, " . countgf($carryUpMap) . " carry up set<br>";
        }

        // Load shop users
        $shopuserlist = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE shop_id = " . intval($shopid) . " && blocked = 0 && is_demo = 0 && expire_date = '" . $expire . "'");
        $companyusers = array();
        $companyidlist = array();

        // Divide shopusers into companies
        foreach ($shopuserlist as $shopuser) {
        
         $isWrapCard = (isset($giftWrapMap[$shopuser->company_id]) || isset($giftWrapOrderMap[$shopuser->company_order_id]));


            if (
                ($useWrapped == false || ($useWrapped == true && (($isWrapped == true && $isWrapCard) || ($isWrapped == false && !$isWrapCard)))) &&
                ($useCarryup == false || ($useCarryup == true && (($isCarryup == true && isset($carryUpMap[$shopuser->company_id])) || ($isCarryup == false && !isset($carryUpMap[$shopuser->company_id])))))
            ) {
                if (!in_array($shopuser->company_id, $companyidlist)) {
                    $companyidlist[] = $shopuser->company_id;
                }

                if (!isset($companyusers[$shopuser->company_id])) {
                    $companyusers[$shopuser->company_id] = array();
                }

                $companyusers[$shopuser->company_id][$shopuser->id] = array("shopuser" => $shopuser, "alias" => $valueAlias . "00");
            }
        }

        if ($isDebug) {
            echo "Loaded shopusers: " . countgf($shopuserlist) . " loaded, divided into " . countgf($companyusers) . " companies<br>";
        }

        // Output null list
        if (count($companyidlist) == 0) {
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment;filename="NULLLIST_' . $shopid . '_' . $shop->alias . '_' . $expire . '' . ($useWrapped ? ($isWrapped ? "_medindpak" : "_udenindpak") : "") . ($useCarryup ? ($isCarryup ? "_medspeclev" : "_udenspeclev") : "") . '.csv"');
            header('Cache-Control: max-age=0');
            echo "Ingen virksomheder;\n";
        }

        // Load companies
        $companyList = Company::find_by_sql('SELECT * FROM company WHERE id IN (' . implode(",", $companyidlist) . ') && onhold = 0 ORDER BY ' . ($language == 4 ? "CAST(ship_to_postal_code as unsigned) DESC, " : "") . ' name ASC, ship_to_company ASC');
        $companyMap = array();
        $dealCompany = array();
        $dealMap = array();

        if ($isDebug) {
            echo "Loaded companies: " . countgf($companyList) . "<br>";
        }

        foreach ($companyList as $company) {
            $companyMap[$company->id] = $company;
            /*
            if(trimgf($company->rapport_note) != "")
            {
            if(!isset($dealMap[$company->id])) $dealMap[$company->id] = array();
            $dealMap[$company->id][] = trimgf($company->rapport_note);
            $dealCompany[$company->cvr] = true;
            }
             */
            if (trimgf($company->internal_note) != "") {
                if (!isset($dealMap[$company->id])) {
                    $dealMap[$company->id] = array();
                }

                $dealMap[$company->id][] = trimgf($company->internal_note);
                $dealCompany[$company->cvr] = true;
            }
        }

        if ($isDebug) {
            echo "Deal companies, from rapport note: " . countgf($dealMap) . "<br>";
        }

        // load spdealtxt from company orders
        $companyOrders = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE is_cancelled = 0 && shop_id = " . $shopid . " && company_id IN (" . implode(",", $companyidlist) . ")");
        foreach ($companyOrders as $co) {
            if (trimgf($co->spdealtxt) != "") {
                if (!isset($dealMap[$co->company_id])) {
                    $dealMap[$co->company_id] = array();
                }

                $dealMap[$co->company_id][] = trimgf($co->spdealtxt);
                $dealCompany[$co->cvr] = true;
            }
            if ($co->gift_spe_lev == 1) {
                if (!isset($dealMap[$co->company_id])) {
                    $dealMap[$co->company_id] = array();
                }

                $dealMap[$co->company_id][] = "Opbæring tilvalgt.";
                $dealCompany[$co->cvr] = true;
            }
        }

        if ($isDebug) {
            echo "Deal companies, from order note: " . countgf($dealMap) . "<br>";
        }

        // Put companies with deal text in top
        $dealList = array();
        $nondealList = array();
        foreach ($companyList as $company) {
            if (isset($dealCompany[$company->cvr])) {
                $dealList[] = $company;
            } else {
                $nondealList[] = $company;
            }

        }
        $companyList = array_merge($dealList, $nondealList);

        // LOAD PRESENTS AND MODELS

        $presentmodelmap = array();
        $productList = array();
        $presentidlist = array();
        $presentmodelidlist = array();
        $aliasmap = array();
        $productmap = array();

        // Process models
        $presentmodellist = PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE language_id = " . $this->shoptolang($shopid) . " && present_id IN (SELECT id FROM `present` WHERE `shop_id` = " . intval($shopid) . ") ORDER BY `present_model`.`aliasletter` ASC");
        foreach ($presentmodellist as $model) {
            if (!isset($presentmodelmap[$model->present_id])) {
                $presentmodelmap[$model->present_id] = array();
            }

            $presentmodelmap[$model->present_id][] = $model;
            if (!in_array($model->present_id, $presentidlist)) {
                $presentidlist[] = $model->present_id;
            }

            if (!in_array($model->model_id, $presentmodelidlist)) {
                $presentmodelidlist[] = $model->model_id;
            }

        }

        if ($isDebug) {
            echo "Loaded models: " . countgf($presentmodellist) . " loaded, divided into " . countgf($presentmodelmap) . " presents<br>";
        }

        // Process presents and models into a productlist
        $presentlist = Present::find_by_sql("SELECT * FROM present where shop_id = " . intval($shopid) . " && id not in (SELECT present_id FROM shop_present WHERE shop_id = " . intval($shopid) . " && is_deleted = 1 && present_id NOT IN (select present_id FROM `order` WHERE shop_id = " . intval($shopid) . ")) ORDER BY alias");

        foreach ($presentlist as $present) {
            if (isset($presentmodelmap[$present->id]) && countgf($presentmodelmap[$present->id]) > 0) {

                foreach ($presentmodelmap[$present->id] as $model) {

                    $isActive = true;

                    if ($model->language_id == 1) {
                        $isActive = $model->active == 0;
                    } else {
                        $dkmodel = PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id = " . intval($model->model_id) . " && language_id = 1");
                        $isActive = $dkmodel[0]->active == 0;
                    }

                    if (trimgf($model->fullalias) == "" && $isActive) {
                        //$this->addPlukWarning('Present '.$model->present_id." / model ". $model->model_id." has no alias - ".$present->nav_name);
                    }

                    $aliasmap[$model->model_id] = $this->fullalias($shopid, $model->fullalias);
                    $product = array(
                        //"present_name" => $present->nav_name,
                        "present_name" => $model->model_name,
                        "model_name" => $model->model_no,
                        "varenr" => $model->model_present_no,
                        "alias" => $model->fullalias,
                        "fullalias" => $this->fullalias($shopid, $model->fullalias),
                        "active" => $isActive,
                        "present" => $present,
                        "model" => $model,
                    );

                    $productmap[$model->model_id] = $product;
                    $productList[] = $product;

                }

            } else {
                //$this->addPlukWarning("The present ".$present->id." has no models - ".$present->nav_name);
            }
        }

        if ($isDebug) {
            echo "Loaded productlist: " . countgf($productList) . "<br>";
        }

        //if($isDebug) echo "<pre>".print_r($productList,true)."</pre>";

        // LOAD AND PROCESS ORDERS

        $selectedPresentList = array();
        $selectedModelList = array();
        $aliasCountMap = array();

        $notFoundOrders = array();
        $notFoundUsers = array();

        // ONLY SET THIS IF ONLY SPECIFIC PRESENTS SHOULD BE PULLED
        $filterPresent = array(); //array(10751,10794,11343,10795,11342);
        $orderList = Order::find_by_sql("SELECT * FROM `order` WHERE shop_id = " . $shopid . " && " . (count($filterPresent) > 0 ? "present_id IN (" . implode(",", $filterPresent) . ") &&" : "") . " shopuser_id IN (select id from shop_user WHERE expire_date = '" . $expire . "' && is_demo = 0 && blocked = 0 && shop_id = " . $shopid . ") && is_demo = 0 && company_id IN (" . implode(",", $companyidlist) . ")");

        foreach ($orderList as $order) {
            if (isset($companyusers[$order->company_id]) && isset($companyusers[$order->company_id][$order->shopuser_id])) {
                // Add to company user
                if (!isset($companyusers[$order->company_id][$order->shopuser_id]["order"])) {
                    $companyusers[$order->company_id][$order->shopuser_id]["order"] = $order;
                    if (!in_array($order->present_id, $selectedPresentList)) {
                        $selectedPresentList[] = $order->present_id;
                    }

                    if (!in_array($order->present_model_id, $selectedModelList)) {
                        $selectedModelList[] = $order->present_model_id;
                    }

                    if (!isset($aliasmap[$order->present_model_id])) {
                        $this->addPlukWarning("Order " . $order->id . " - could not find present model id in productlist: " . $order->present_model_id . " (present: " . $order->present_id . ")");
                    } else {
                        if (!isset($aliasCountMap[$aliasmap[$order->present_model_id]])) {
                            $aliasCountMap[$aliasmap[$order->present_model_id]] = 0;
                        }

                        $aliasCountMap[$aliasmap[$order->present_model_id]]++;
                        $companyusers[$order->company_id][$order->shopuser_id]["alias"] = $aliasmap[$order->present_model_id];

                    }

                } else {
                    $this->addPlukWarning("Order id " . $order->id . " could not add to user " . $order->shopuser_id . " - already has order - " . $companyusers[$order->company_id][$order->shopuser_id]["order"]->id);
                }
            } else {
                $this->addPlukWarning("Order id " . $order->id . " could not find company or companyuser - Company " . $order->company_id . " " . (isset($companyusers[$order->company_id]) ? "FOUND" : "NOT FOUND") . " / User " . $order->shopuser_id);
                $notFoundOrders[] = $order->id;
                $notFoundUsers[] = $order->shopuser_id;
            }
        }

        if ($isDebug) {
            echo "NOT FOUND ORDERS: (" . implode(",", $notFoundOrders) . ")<br>";
        }

        if ($isDebug) {
            echo "NOT FOUND USERS: (" . implode(",", $notFoundUsers) . ")<br>";
        }

        // Count users with no orders
        $noOrderUsers = 0;
        foreach ($companyusers as $culi => $cul) {
            foreach ($cul as $cui => $cu) {
                if (!isset($cu["order"]) && isset($companyMap[$cu["shopuser"]->company_id])) {
                    $noOrderUsers++;
                }

                if (count($filterPresent) > 0) {
                    if (!isset($cu["alias"]) || !isset($cu["order"]) || !in_array($cu["order"]->present_id, $filterPresent)) {
                        unset($companyusers[$culi][$cui]);
                    }
                }

            }

        }

        if ($isDebug) {
            echo "Loaded orders: " . countgf($orderList) . " loaded, there is " . $noOrderUsers . " shopusers without orders<br>";
        }

        // Check selected presents is in productlist
        if (count($selectedPresentList) > 0) {
            foreach ($selectedPresentList as $presentid) {
                $hasPresent = false;
                foreach ($productList as $product) {
                    if ($product["present"]->id == $presentid) {
                        $hasPresent = true;
                    }
                }
                if ($hasPresent == false) {
                    $this->addPlukWarning("Present id " . $presentid . " has been selected, but is not in the product-list");
                }

            }
        }

        // Check selected models is in productlist
        if (count($selectedModelList) > 0) {
            foreach ($selectedModelList as $modelid) {
                $hasModel = false;
                foreach ($productList as $product) {
                    if ($product["model"]->model_id == $modelid) {
                        $hasModel = true;
                    }
                }
                if ($hasModel == false) {
                    $this->addPlukWarning("Model id " . $modelid . " has been selected, but is not in the product-list");
                }

            }
        }

        // Get shopusers outside period

        $shopuserNegativeList = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE shop_id = " . $shopid . " && blocked = 0 && is_demo = 0 && expire_date != '" . $expire . "'");
        $shopuserNegativeMap = array();
        foreach ($shopuserNegativeList as $su) {
            if (!isset($shopuserNegativeMap[$su->company_id])) {
                $shopuserNegativeMap[$su->company_id] = 0;
            }

            $shopuserNegativeMap[$su->company_id]++;
        }

        if ($isDebug) {
            echo "READY TO MAKE LIST<br>";
        }

        // Init phpexcel
        $phpExcel = new PHPExcel();
        $phpExcel->removeSheetByIndex(0);

        // PRODUCT LIST

        // Write header
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $sheet->setTitle("Produktliste");
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->setCellValueByColumnAndRow(1, 1, "Gavenr.");
        $sheet->setCellValueByColumnAndRow(2, 1, "Gavenavn");
        $sheet->setCellValueByColumnAndRow(3, 1, "Gavemodel");
        $sheet->setCellValueByColumnAndRow(4, 1, "Antal valgt");
        $sheet->getStyle("A1:H1")->getFont()->setBold(true);

        $row = 2;
        $sheet->setCellValueByColumnAndRow(1, $row, $valueAlias . "00");
        $sheet->setCellValueByColumnAndRow(2, $row, $autovalgName);
        $sheet->setCellValueByColumnAndRow(3, $row, "");
        $sheet->setCellValueByColumnAndRow(4, $row, $noOrderUsers);
        $row++;

        $totalCount = $noOrderUsers;

        // Output total count of all products
        foreach ($productList as $product) {
            $sheet->setCellValueByColumnAndRow(1, $row, $product["fullalias"]);
            $sheet->setCellValueByColumnAndRow(2, $row, $product["present_name"]);
            $sheet->setCellValueByColumnAndRow(3, $row, $product["model_name"]);
            $sheet->setCellValueByColumnAndRow(4, $row, isset($aliasCountMap[$product["fullalias"]]) ? $aliasCountMap[$product["fullalias"]] : 0);
            $totalCount += (isset($aliasCountMap[$product["fullalias"]]) ? $aliasCountMap[$product["fullalias"]] : 0);
            $row++;
        }

        // Output sum
        $sheet->setCellValueByColumnAndRow(1, $row, "");
        $sheet->setCellValueByColumnAndRow(2, $row, "");
        $sheet->setCellValueByColumnAndRow(3, $row, "Total antal");
        $sheet->setCellValueByColumnAndRow(4, $row, $totalCount);
        $row++;

        /* START ORDRELISTE */

        // Write order list
        $sheet = $phpExcel->createSheet();
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

        $sheet->setCellValueByColumnAndRow(1, 1, $shop->name . " (id " . $shopid . "): ordreliste");
        $sheet->setCellValueByColumnAndRow(3, 1, "Budget: " . $shop->card_value);
        $sheet->setCellValueByColumnAndRow(5, 1, "Deadline: " . $expire);

        $BackBlue = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'e9f0f2')
            ),
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $sheet->getStyle('A1:H1')->applyFromArray($BackBlue);

        $row = 2;
        $userCount = 0;
        $companyAutogave = array();
        $companyCountMap = array();
        $companyCountTotal = array();

        foreach ($companyList as $company) {

            if (isset($companyusers[$company->id]) && countgf($companyusers[$company->id]) > 0) {

                $sheet->setBreak('A' . $row, PHPExcel_Worksheet::BREAK_ROW);
                $userList = array();

                foreach ($companyusers[$company->id] as $cu) {
                    $userList[] = $cu;
                }

                // Order by alias
                for ($i = 0; $i < countgf($userList); $i++) {
                    for ($j = $i + 1; $j < countgf($userList); $j++) {
                        if (intval($userList[$i]["alias"]) > intval($userList[$j]["alias"]) || (intval($userList[$i]["alias"]) == intval($userList[$j]["alias"]) && (strcmp($userList[$i]["alias"], $userList[$j]["alias"]) > 0))) {
                            $tmp = $userList[$i];
                            $userList[$i] = $userList[$j];
                            $userList[$j] = $tmp;
                        }
                    }
                }

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
                if ($useWrapped) {
                    $sheet->setCellValueByColumnAndRow(9, $row, "Indpakket");
                }

                if ($useCarryup) {
                    $sheet->setCellValueByColumnAndRow(10, $row, "Speciallevering");
                }

                $row++;

                $sheet->setCellValueByColumnAndRow(1, $row, $company->name);
                $sheet->setCellValueByColumnAndRow(2, $row, $company->bill_to_address . (trimgf($company->bill_to_address_2) == "" ? "" : ", " . $company->bill_to_address_2));
                $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->bill_to_postal_code,$shop->id));
                $sheet->setCellValueByColumnAndRow(4, $row, $company->bill_to_city);
                $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                 $sheet->setCellValueByColumnAndRow(8, $row, $company->contact_email);
                if ($useWrapped) {
                    $sheet->setCellValueByColumnAndRow(9, $row, $isWrapped ? "Ja" : "Nej");
                }

                if ($useCarryup) {
                    $sheet->setCellValueByColumnAndRow(10, $row, $isCarryup ? "Ja" : "Nej");
                }

                $row++;

                $companyName = (trimgf($company->ship_to_company) != "" && $language == 4) ? $company->ship_to_company : $company->name;

                $sameAddress = true;
                if ($company->name != $companyName) {
                    $sameAddress = false;
                }

                if (($company->bill_to_address . (trimgf($company->bill_to_address_2) == "" ? "" : ", " . $company->bill_to_address_2)) != ($company->ship_to_address . (trimgf($company->ship_to_address_2) == "" ? "" : ", " . $company->ship_to_address_2))) {
                    $sameAddress = false;
                }

                if ($company->bill_to_postal_code != $company->ship_to_postal_code) {
                    $sameAddress = false;
                }

                if ($company->bill_to_city != $company->ship_to_city) {
                    $sameAddress = false;
                }

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

                if ($sameAddress == false) {
                    $sheet->setCellValueByColumnAndRow(1, $row, trimgf($company->ship_to_company) == "" ? $company->name : $company->ship_to_company);
                    $sheet->setCellValueByColumnAndRow(2, $row, $company->ship_to_address . (trimgf($company->ship_to_address_2) == "" ? "" : ", " . $company->ship_to_address_2));
                    $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->ship_to_postal_code,$shop->id));
                    $sheet->setCellValueByColumnAndRow(4, $row, $company->ship_to_city);
                    $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                    $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                    $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                     $sheet->setCellValueByColumnAndRow(8, $row, $company->contact_email);
                    $row++;
                } else {
                    $sheet->setCellValueByColumnAndRow(1, $row, $company->name);
                    $sheet->setCellValueByColumnAndRow(2, $row, $company->bill_to_address . (trimgf($company->bill_to_address_2) == "" ? "" : ", " . $company->bill_to_address_2));
                    $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->bill_to_postal_code,$shop->id));
                    $sheet->setCellValueByColumnAndRow(4, $row, $company->bill_to_city);
                    $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                    $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                    $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                     $sheet->setCellValueByColumnAndRow(8, $row, $company->contact_email);
                }

                $row++;

                // Output notes
                $noteCount = 0;
                if (isset($dealMap[$company->id]) && countgf($dealMap[$company->id]) > 0) {
                    foreach ($dealMap[$company->id] as $note) {
                        $sheet->getStyle("B" . $row . ":G" . $row)->getFont()->setBold(true);
                        $sheet->getStyle('B' . $row . ':G' . $row)->applyFromArray($BackBlue);
                        $sheet->setCellValueByColumnAndRow(1, $row, $note);
                        $row++;
                        $noteCount++;
                    }
                }

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
                if ($useWrapped) {
                    $sheet->setCellValueByColumnAndRow(9, $row, "Indpakket");
                }

                if ($useCarryup) {
                    $sheet->setCellValueByColumnAndRow(10, $row, "Speciallevering");
                }

                $sheet->getStyle("A" . $row . ":I" . $row)->getFont()->setBold(true);
                $row++;

                // Output orders
                foreach ($userList as $cu) {

                    $giftCertNo = isset($cu["order"]) ? $cu["order"]->gift_certificate_no : $cu["shopuser"]->username;
                    $alias = $cu["alias"];

                    if (isset($cu["order"]) && isset($cu["alias"])) {
                        if (isset($productmap[$cu["order"]->present_model_id])) {
                            $presentName = $productmap[$cu["order"]->present_model_id]["present_name"];
                            $modelName = $productmap[$cu["order"]->present_model_id]["model_name"];
                        } else {
                            $presentName = "Ukendt gave";
                            $modelName = "Ukendt model";
                        }
                    } else {
                        $presentName = $autovalgName;
                        $modelName = "";
                    }

                    $sheet->setCellValueByColumnAndRow(1, $row, $company->name);
                    $sheet->setCellValueByColumnAndRow(2, $row, $giftCertNo);
                    $sheet->setCellValueByColumnAndRow(3, $row, $alias);
                    $sheet->setCellValueByColumnAndRow(4, $row, $presentName);
                    $sheet->setCellValueByColumnAndRow(5, $row, $modelName);
                    $sheet->setCellValueByColumnAndRow(6, $row, isset($cu["order"]) ? $cu["order"]->user_name : "Gavekort " . $cu["shopuser"]->username);
                    $sheet->setCellValueByColumnAndRow(7, $row, isset($cu["order"]) ? $cu["order"]->user_email : "");
                    $sheet->setCellValueByColumnAndRow(8, $row, $cu["shopuser"]->expire_date->format("Y-m-d"));
                    if ($useWrapped) {
                        $sheet->setCellValueByColumnAndRow(9, $row, $isWrapped ? "Ja" : "Nej");
                    }

                    if ($useCarryup) {
                        $sheet->setCellValueByColumnAndRow(10, $row, $isCarryup ? "Ja" : "Nej");
                    }

                    $row++;
                    $userCount++;

                    if (!isset($companyCountMap[$company->id])) {
                        $companyCountMap[$company->id] = array();
                    }

                    if (!isset($companyCountMap[$company->id][$alias])) {
                        $companyCountMap[$company->id][$alias] = 0;
                    }

                    $companyCountMap[$company->id][$alias]++;

                    if (!isset($companyCountTotal[$company->id])) {
                        $companyCountTotal[$company->id] = 0;
                    }

                    $companyCountTotal[$company->id]++;

                    if (!isset($cu["order"])) {
                        if (!isset($companyAutogave[$company->id])) {
                            $companyAutogave[$company->id] = 0;
                        }

                        $companyAutogave[$company->id]++;
                    }

                }

                $row++;
            }

        }

        // ENDORDRELISTE START VIRKSOMHEDER

        // Company list
        $sheet = $phpExcel->createSheet();
        $sheet->setTitle("Virksomheder");

        $row = 1;
        $sheet->getStyle("A" . $row . ":Z" . $row)->getFont()->setBold(true);
        $sheet->setCellValueByColumnAndRow(1, $row, "Virksomhed");
        $sheet->setCellValueByColumnAndRow(2, $row, "CVR");
        $sheet->setCellValueByColumnAndRow(3, $row, "Antal gavekort");
        $sheet->setCellValueByColumnAndRow(4, $row, "Antal autogave");
        $sheet->setCellValueByColumnAndRow(5, $row, "Gavekort udenfor denne periode");
        if ($useWrapped) {
            $sheet->setCellValueByColumnAndRow(6, $row, "Indpakning");
        }

        if ($useCarryup) {
            $sheet->setCellValueByColumnAndRow(7, $row, "Speciallevering");
        }

        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $row++;

        foreach ($companyList as $company) {
            if (isset($companyusers[$company->id])) {

                $autogaveCount = 0;
                if (isset($companyAutogave[$company->id]) && $company->id > 0) {
                    $autogaveCount = $companyAutogave[$company->id];
                }

                $sheet->setCellValueByColumnAndRow(1, $row, $company->name);
                $sheet->setCellValueByColumnAndRow(2, $row, $company->cvr);
                $sheet->setCellValueByColumnAndRow(3, $row, countgf($companyusers[$company->id]));
                $sheet->setCellValueByColumnAndRow(4, $row, $autogaveCount);
                $sheet->setCellValueByColumnAndRow(5, $row, isset($shopuserNegativeMap[$company->id]) ? $shopuserNegativeMap[$company->id] : 0);
                if ($useWrapped) {
                    $sheet->setCellValueByColumnAndRow(6, $row, $isWrapped ? "Ja" : "Nej");
                }

                if ($useCarryup) {
                    $sheet->setCellValueByColumnAndRow(7, $row, $isCarryup ? "Ja" : "Nej");
                }

                $row++;

                $totalCount = 0;
                if (isset($companyCountTotal[$company->id])) {
                    $totalCount = $companyCountTotal[$company->id];
                }

                if ($totalCount != countgf($companyusers[$company->id])) {
                    $this->addPlukWarning("Virksomheden: " . $company->name . " (id = " . $company->id . ") har kun " . $totalCount . " med i listen, men der ligger " . countgf($companyusers[$company->id]) . " ordre");
                }

            }

        }

        // ENDVIRKSOMHEDER START GAVEVALG

        // Company list
        $sheet = $phpExcel->createSheet();
        $sheet->setTitle("Gavevalg");

        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(48);
        $sheet->getColumnDimension('C')->setWidth(49);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(12);

        $sheet->setCellValueByColumnAndRow(1, 1, $shop->name . " (id " . $shopid . "): ordreliste");
        $sheet->setCellValueByColumnAndRow(3, 1, "Budget: " . $shop->card_value);
        $sheet->setCellValueByColumnAndRow(5, 1, "Deadline: " . $expire);

        $BackBlue = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'e9f0f2')
            ),
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ),
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $sheet->getStyle('A1:H1')->applyFromArray($BackBlue);
        $row = 2;

        foreach ($companyList as $company) {

            if (isset($companyusers[$company->id])) {

                $sheet->setBreak('A' . $row, PHPExcel_Worksheet::BREAK_ROW);
                $userList = array();
                foreach ($companyusers[$company->id] as $cu) {
                    $userList[] = $cu;
                }

                // Order by alias
                for ($i = 0; $i < countgf($userList); $i++) {
                    for ($j = $i + 1; $j < countgf($userList); $j++) {
                        if (intval($userList[$i]["alias"]) > intval($userList[$j]["alias"]) || (intval($userList[$i]["alias"]) == intval($userList[$j]["alias"]) && (strcmp($userList[$i]["alias"], $userList[$j]["alias"]) > 0))) {
                            $tmp = $userList[$i];
                            $userList[$i] = $userList[$j];
                            $userList[$j] = $tmp;
                        }
                    }
                }

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
                if ($useWrapped) {
                    $sheet->setCellValueByColumnAndRow(9, $row, "Indpakket");
                }

                if ($useCarryup) {
                    $sheet->setCellValueByColumnAndRow(10, $row, "Speciallevering");
                }

                $row++;

                $sheet->setCellValueByColumnAndRow(1, $row, $company->name);
                $sheet->setCellValueByColumnAndRow(2, $row, $company->bill_to_address . (trimgf($company->bill_to_address_2) == "" ? "" : ", " . $company->bill_to_address_2));
                $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->bill_to_postal_code,$shop->id));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('0000');
                $sheet->setCellValueByColumnAndRow(4, $row, $company->bill_to_city);
                $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                if ($useWrapped) {
                    $sheet->setCellValueByColumnAndRow(9, $row, $isWrapped ? "Ja" : "Nej");
                }

                if ($useCarryup) {
                    $sheet->setCellValueByColumnAndRow(10, $row, $isCarryup ? "Ja" : "Nej");
                }

                $row++;

                //$companyName = (trimgf($company->ship_to_company) != "" && $language == 4) ? $company->ship_to_company : $company->name;
                $companyName = $company->name;
                $sameAddress = true;
                if ($company->name != $companyName) {
                    $sameAddress = false;
                }

                if (($company->bill_to_address . (trimgf($company->bill_to_address_2) == "" ? "" : ", " . $company->bill_to_address_2)) != ($company->ship_to_address . (trimgf($company->ship_to_address_2) == "" ? "" : ", " . $company->ship_to_address_2))) {
                    $sameAddress = false;
                }

                if ($company->bill_to_postal_code != $company->ship_to_postal_code) {
                    $sameAddress = false;
                }

                if ($company->bill_to_city != $company->ship_to_city) {
                    $sameAddress = false;
                }

                $sheet->getStyle("A" . $row . ":Z" . $row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(1, $row, "Levering");
                $sheet->setCellValueByColumnAndRow(2, $row, "Leveringsadresse");
                $sheet->setCellValueByColumnAndRow(3, $row, "Levering postnr");
                $sheet->setCellValueByColumnAndRow(4, $row, "Levering by");
                $sheet->setCellValueByColumnAndRow(5, $row, "Kontakt Navn");
                $sheet->setCellValueByColumnAndRow(6, $row, "Kontakt telefon");
                $sheet->setCellValueByColumnAndRow(7, $row, "CVR");
                //if($useWrapped) $sheet->setCellValueByColumnAndRow(8,$row,"Indpakket");
                $row++;

                if ($sameAddress == false) {

                    $sheet->setCellValueByColumnAndRow(1, $row, trimgf($company->ship_to_company) == "" ? $company->name : $company->ship_to_company);
                    $sheet->setCellValueByColumnAndRow(2, $row, $company->ship_to_address . (trimgf($company->ship_to_address_2) == "" ? "" : ", " . $company->ship_to_address_2));
                    $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->ship_to_postal_code,$shop->id));
                    //$sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode('0000');
                    $sheet->setCellValueByColumnAndRow(4, $row, $company->ship_to_city);
                    $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                    $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                    $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                    //if($useWrapped) $sheet->setCellValueByColumnAndRow(8,$row,$isWrapped ? "Ja" : "Nej");
                    $row++;

                } else {
                    $sheet->setCellValueByColumnAndRow(1, $row, $company->name);
                    $sheet->setCellValueByColumnAndRow(2, $row, $company->bill_to_address . (trimgf($company->bill_to_address_2) == "" ? "" : ", " . $company->bill_to_address_2));
                   $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->bill_to_postal_code,$shop->id));
                    $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('0000');
                    $sheet->setCellValueByColumnAndRow(4, $row, $company->bill_to_city);
                    $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                    $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                    $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                    $row++;

                }

                $row++;
                $noteCount = 0;

                if (isset($dealMap[$company->id]) && countgf($dealMap[$company->id]) > 0) {
                    foreach ($dealMap[$company->id] as $note) {
                        $sheet->getStyle("B" . $row . ":F" . $row)->getFont()->setBold(true);
                        $sheet->getStyle('B' . $row . ':F' . $row)->applyFromArray($BackBlue);
                        $sheet->setCellValueByColumnAndRow(2, $row, $note);
                        $row++;
                        $noteCount++;
                    }
                }

                if ($noteCount > 0) {
                    $row++;
                }

                $sheet->setCellValueByColumnAndRow(1, $row, "Gavenr.");
                $sheet->setCellValueByColumnAndRow(2, $row, "Gavenavn");
                $sheet->setCellValueByColumnAndRow(3, $row, "Gavemodel");
                $sheet->setCellValueByColumnAndRow(4, $row, "Antal valgt");
                $sheet->getStyle("A" . $row . ":H" . $row)->getFont()->setBold(true);
                $row++;

                $rowCount = 0;

                foreach ($productList as $product) {
                    $count = 0;
                    if (isset($companyCountMap[$company->id]) && isset($companyCountMap[$company->id][$product["fullalias"]])) {
                        $count = $companyCountMap[$company->id][$product["fullalias"]];
                    }
                    $rowCount += $count;

                    $sheet->setCellValueByColumnAndRow(1, $row, $product["fullalias"]);
                    $sheet->setCellValueByColumnAndRow(2, $row, $product["present_name"]);
                    $sheet->setCellValueByColumnAndRow(3, $row, $product["model_name"]);
                    $sheet->setCellValueByColumnAndRow(4, $row, $count);
                    $row++;
                }

                $sheet->setCellValueByColumnAndRow(3, $row, "Total antal");
                $sheet->setCellValueByColumnAndRow(4, $row, $rowCount);
                $row++;
                $row++;

            }
        }

        // PLUKLIST WARNING SHEET

        // Add warning list
        if (count($this->plukWarningList) > 0) {
            $sheet = $phpExcel->createSheet();
            $sheet->setTitle("Warnings");
            for ($i = 0; $i < countgf($this->plukWarningList); $i++) {
                $sheet->setCellValueByColumnAndRow(2, $i + 1, utf8_encode($this->plukWarningList[$i]));
                if ($isDebug) {
                    echo "PLUK WARNING: " . $this->plukWarningList[$i];
                }

            }
        }

        if ($isDebug) {
            return;
        }

        ob_end_clean();

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="kortshop_' . $shopid . '_' . $shop->alias . '_' . $expire . '' . ($useWrapped ? ($isWrapped ? "_medindpak" : "_udenindpak") : "") . ($useCarryup ? ($isCarryup ? "_medspeclev" : "_udenspeclev") : "") . '.xlsx"');
        header('Cache-Control: max-age=0');
        $phpExcel->setActiveSheetIndex(1);
        $objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
        $objWriter->save('php://output');
        exit();

    }

    /***************** PRESENT LIST *******************/

    private function presentlist($shopid, $expire, $useWrapped, $isWrapped, $useCarryup, $isCarryup)
    {

        $shop = Shop::find($shopid);

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="presentlist-' . $shop->name . '.csv"');

        // Get presents
        $presentlist = Present::find_by_sql("SELECT * FROM present where shop_id = " . intval($shopid) . " && (id not in (SELECT present_id FROM shop_present WHERE shop_id = " . intval($shopid) . " &&  (is_deleted = 1 || active = 0)) || alias > 0) ORDER BY alias");

        // Load models
        $presentmodellist = PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE language_id = " . $this->shoptolang($shopid) . " && present_id IN (SELECT id FROM `present` WHERE `shop_id` = " . intval($shopid) . ") ORDER BY `present_model`.`aliasletter` ASC");
        $presentmodelmap = array();

        foreach ($presentmodellist as $model) {
            if (!isset($presentmodelmap[$model->present_id])) {
                $presentmodelmap[$model->present_id] = array();
            }

            $presentmodelmap[$model->present_id][] = $model;
        }

        echo "Gavenr;Varenr;Gave navn;Model navn;Note\n";

        foreach ($presentlist as $present) {
            if (count($presentmodelmap[$present->id]) > 0) {
                foreach ($presentmodelmap[$present->id] as $model) {
                    $isActive = true;
                    $dkModel = null;

                    if ($model->language_id == 1) {
                        $dkModel = $model;
                        $isActive = $model->active == 0;
                    } else {

                        $dkModel = PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id = " . intval($model->model_id) . " && language_id = 1");
                        $dkModel = $dkModel[0];
                        $isActive = $dkModel->active == 0;
                    }

                    if ($isActive || trimgf($dkModel->fullalias) != "") {
                        echo utf8_decode($this->fullalias($shopid, $model->fullalias) . ";" . str_replace(";", ",", $dkModel->model_present_no) . ";" . $model->model_name . ";" . $model->model_no . ";" . ($model->active == 1 ? "Deaktiveret" : "") . "\n");
                    }
                }
            } else {
                echo "Gaven: " . $present->nav_name . " har ingen modeller!";
            }
        }

    }

    /************* REMINDER list *****************/

    private function findtoken()
    {
        $token = NewGUID();
        $company = Company::find_by_sql("SELECT * FROM company WHERE token LIKE '" . $token . "'");
        if (count($company) > 0) {
            return findtoken();
        } else {
            return $token;
        }

    }

    public function settokens()
    {

        $companylist = Company::find_by_sql("SELECT * FROM company WHERE token = '' OR Token IS NULL");
        $set = array();
        foreach ($companylist as $company) {
            $c = Company::find($company->id);
            if (trimgf($c->token) == "" && $c->id > 0) {

                $c->token = $this->findtoken();
                $c->save();
                $set[] = $c->id;

            }
        }

        System::connection()->commit();
        echo json_encode($set);

    }

    public function remindermailfromquery()
    {
       // https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=cardshoppluk/reminder&shops=52,4662,575,4668,7121&deadline=2024-10-27&ulle

        $shopinput = isset($_GET["shops"]) ? trimgf($_GET["shops"]) : "";
        $deadline = isset($_GET["deadline"]) ? trimgf($_GET["deadline"]) : "";
        $dummy    = isset($_GET["dummy"]) ? trimgf($_GET["dummy"]) : "";



        if(isset($_GET["ulle"])){
           $this->remindermaillistFilted2023($shopinput, $deadline,$dummy);
        } else {
            $this->remindermaillist($shopinput, $deadline,$dummy);
        }

    }
    public function remindermaillistFilted2023($shopinput, $deadline){
         $expireDate = expireDate::getByExpireDate($deadline);
        if ($expireDate == null) {echo "Ugyldig deadline";return;}

        $shoplist = array();
        $inputsplit = explode(",", $shopinput);
        if (count($inputsplit) > 0) {
            foreach ($inputsplit as $input) {
                if (intval($input) > 0) {
                    $shoplist[] = intval($input);
                }
            }
        }

        if (count($shoplist) == 0) {echo "Ingen shops angivet..";return;}

        $shops = $shoplist;

       // Hent firmaer alle kort med deadline
        $parentCompanys = [];
        $parentList = [];
        $childCompanys = [];
        $childNoParents = [];
        $childNoParentList = [];
        $listOfCompanysWithSameContackPerson = [];
        $dubletEmailButNotSameCvrEan = [];
        $companys = Company::find_by_sql( "SELECT * FROM `company` WHERE `id` in (
                                SELECT DISTINCT shop_user.`company_id`  FROM `shop_user` 
                                inner join company_order on  company_order.id =  shop_user.company_order_id
                                WHERE 
                                shop_user.`expire_date` = '".$deadline."' &&
                                shop_user.`is_demo` = 0 &&
                                shop_user.`blocked` = 0 &&
                                shop_user.`shutdown` = 0 &&
                                company_order.is_cancelled = 0 &&
                                order_state in (4,5,10) &&
                                shop_user.shop_id in (" . implode(",", $shops) . ") &&
                                shop_user.is_giftcertificate = 1 
                                
                                
                                )  ORDER BY `company`.`id` ASC "
                            );


        // Split op i parent og child lister
        foreach ($companys as $company){
            $company = $company->attributes;
            if($company["pid"] == 0){
                $parentCompanys["c_".strval($company["id"])][] = $company;
                $parentList[] = "c_".strval($company["id"]);
            } else {
                $childCompanys["c_".strval($company["id"])][] = $company;
            }
        }
//        print_R($childCompanys);
 //       die("end");
        // find hvilke child der ikke har en parent i listen

        foreach ($childCompanys as $childCompany){

            if(in_array("c_".strval($childCompany[0]["pid"]),$parentList)){

            } else {
                $childNoParentList[] = $childCompany[0]["pid"];

            }
        }

        $childNoParentList = array_unique($childNoParentList);
        // hent parent ud fra de childs der ikke havde en parent
        if(sizeof($childNoParentList) > 0) {
            $missingParents = Company::find_by_sql("SELECT * FROM `company` WHERE id in ( " . implode(",", $childNoParentList) . ")");

            foreach ($missingParents as $company) {
                $company = $company->attributes;
                // print_R($company);
                $parentCompanys["c_" . strval($company["id"])][] = $company;
            }

        // find liste over virksomheder med samme kontaktperson

        $groupedCompanies = [];
        foreach ($parentCompanys as $key=>$val) {
            $contact = strtolower(trim($val[0]['contact_email']));
            if (in_array($contact,$groupedCompanies)){
                $listOfCompanysWithSameContackPerson[] = $val[0]['id'];
            }
            $groupedCompanies[] = $contact;
        }
        $dubletEmailButNotSameCvrEan = Company::find_by_sql(" SELECT * FROM `company` WHERE id in (".implode(",", $listOfCompanysWithSameContackPerson).") GROUP by `contact_email`,`cvr`,`ean` ");
        }
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="reminderlist-' . $deadline . '.csv"');
        // Define headers
        $header = array("CompanyID","EAN", "Virksomhed", "CVR", "Faktura adresse", "Faktura postnr", "Faktura by", "Levering virksomhed", "Levering adresse", "Levering postnr", "Levering by", "Kontaktperson", "Telefon", "E-mail",  "Token","Saleperson");
        echo implode(';', $header) . "\n";


        foreach ($parentCompanys as $pcFinal) {

            $data = [];
            $pcFinal = $pcFinal[0];
            if(in_array($pcFinal["id"],$listOfCompanysWithSameContackPerson)){
                continue;
            }
            $data[] = $pcFinal["id"];
            $data[] = $pcFinal["ean"];
            $data[] = $this->cleanCSVField($pcFinal["name"]);
            $data[] = $pcFinal["cvr"];
            $data[] = $this->cleanCSVField($pcFinal["bill_to_address"]);
            $data[] = $pcFinal["ship_to_postal_code"];
            $data[] = $this->cleanCSVField($pcFinal["ship_to_city"]);
            $data[] = $this->cleanCSVField($pcFinal["ship_to_company"]);
            $data[] = $this->cleanCSVField($pcFinal["ship_to_address"]);
            $data[] = $pcFinal["ship_to_postal_code"];
            $data[] = $pcFinal["ship_to_city"];
            $data[] = $this->cleanCSVField($pcFinal["contact_name"]);
            $data[] = $pcFinal["contact_phone"];
            $data[] = $pcFinal["contact_email"];
            $data[] = $pcFinal["token"];
            $data[] = $pcFinal["sales_person"];

            $output =  implode(';', $data) . "\n";
           echo utf8_decode($output);
      }


        foreach ($dubletEmailButNotSameCvrEan as $pcFinal) {

            $data = [];
            $pcFinal = $pcFinal->attributes;
            $data[] = $pcFinal["id"];
            $data[] = $pcFinal["ean"];
            $data[] = $this->cleanCSVField($pcFinal["name"]);
            $data[] = $pcFinal["cvr"];
            $data[] = $this->cleanCSVField($pcFinal["bill_to_address"]);
            $data[] = $pcFinal["ship_to_postal_code"];
            $data[] = $this->cleanCSVField($pcFinal["ship_to_city"]);
            $data[] = $this->cleanCSVField($pcFinal["ship_to_company"]);
            $data[] = $this->cleanCSVField($pcFinal["ship_to_address"]);
            $data[] = $pcFinal["ship_to_postal_code"];
            $data[] = $pcFinal["ship_to_city"];
            $data[] = $this->cleanCSVField($pcFinal["contact_name"]);
            $data[] = $pcFinal["contact_phone"];
            $data[] = $pcFinal["contact_email"];
            $data[] = $pcFinal["token"];
            $data[] = $pcFinal["sales_person"];

            $output =  implode(';', $data) . "\n";
            echo utf8_decode($output);
        }




//view-source:https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=cardshoppluk/reminder&token=sdfkfk3DD3kdsj3xSS34Df&shops=4668&deadline=2023-11-12&ulle






    }
    public function cleanCSVField( $text){
        $text = str_replace(["\n","\r","&amp;"], "", $text); // Fjerner LF

       return str_replace(";", ",", $text);
    }



    // ******************* denne er  samle listen 2021 **********************************
    public function remindermaillistFilted($shopinput, $deadline,$dummy="")
    {
        $path = GFConfig::BACKEND_PATH."component/cardpluk";
        $emailToRemove = [];
        /*
        $file = fopen($path.'/norge_lort_sendt.csv', 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            $emailToRemove[] = strtolower($line[0]);
        }
        fclose($file);
        */
        $shoplist = array();
        $inputsplit = explode(",", $shopinput);
        if (count($inputsplit) > 0) {
            foreach ($inputsplit as $input) {
                if (intval($input) > 0) {
                    $shoplist[] = intval($input);
                }
            }
        }

        if (count($shoplist) == 0) {echo "Ingen shops angivet..";return;}

        $shops = $shoplist;

        // Check deadline
        $expireDate = expireDate::getByExpireDate($deadline);


        if ($expireDate == null) {echo "Ugyldig deadline";return;}

        $removeDuplicateMails = false;

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="reminderlist-' . $deadline . '.csv"');

        $sumCompany = 0;
        $sumUsers = 0;
        $sumSelected = 0;
        $sumNotSelected = 0;
        $usedEmails = array();

        // Define headers
        $header = array("CompanyID","EAN", "Virksomhed", "CVR", "Faktura adresse", "Faktura postnr", "Faktura by", "Levering virksomhed", "Levering adresse", "Levering postnr", "Levering by", "Kontaktperson", "Telefon", "E-mail", "Antal kort", "Antal valgt", "Antal ikke valgt", "Token","Saleperson");
        echo implode(';', $header) . "\n";

        // Find companies
        $companylist = ShopUser::find_by_sql("
                SELECT company_id, count(id) as users FROM shop_user WHERE 
                    shop_id IN (" . implode(",", $shops) . ") && 
                    is_demo = 0 && 
                    expire_date = '" . $deadline . "' 
                    && is_giftcertificate = 1 && 
                    blocked = 0 && 
                    shutdown = 0 && 
                    company_id NOT IN (12441,12456,12468,12480,12500,12501,12505,12510,12512,12514,12515,12519,12520,12522,12523,12529,12530,12531,12532,12533,12535,12536,12537,12538,12539,12540,12541,12542,12543,12544,12546,12547,12549,12550,12551,12552,12553,12554,12555,12556,12557,12558,12559,12561,12562,12563,12564,12565,12566,12568,12569,12571,12572,12573,12574,12575,12576,12577,12578,12579,12580,12581,12583,12587,12588,12618,12689,12702,12994,13014,13037,13095,13098,13441,13521,13554,13555,13700,13088,18581,19308,19309,19310,12957) GROUP BY company_id ");

        //echo "SELECT company_id, count(id) as users FROM shop_user WHERE shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0 && company_id NOT IN (12441,12456,12468,12480,12500,12501,12505,12510,12512,12514,12515,12519,12520,12522,12523,12529,12530,12531,12532,12533,12535,12536,12537,12538,12539,12540,12541,12542,12543,12544,12546,12547,12549,12550,12551,12552,12553,12554,12555,12556,12557,12558,12559,12561,12562,12563,12564,12565,12566,12568,12569,12571,12572,12573,12574,12575,12576,12577,12578,12579,12580,12581,12583,12587,12588,12618,12689,12702,12994,13014,13037,13095,13098,13441,13521,13554,13555,13700,13088,18581,19308,19309,19310,12957) GROUP BY company_id";
        //echo "<br>".countgf($companylist); exit();

        // Go through companies
        $cvrList = [];
        foreach ($companylist as $companycount) {

            // Load company and orders
            $salesperson = "";
            $c = Company::find($companycount->company_id);

            $orders = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE id NOT IN (SELECT shopuser_id FROM `order`) && company_id = " . $c->id . " && shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0 && shutdown = 0");
            $cOrder = CompanyOrder::find_by_sql("select * from company_order where company_id =".$c->id);
            foreach($cOrder as $orderInfo){
               $salesperson.=$orderInfo->salesperson." - ";
            }
                /****   vi mangler at tage højde for når en parent er tom eller har kort til en deadline, men child har kort til deadline, vi skal da finde parent og s ********/
            // Get cound
            $totalUsers = $companycount->users;
            $totalNotSelected = countgf($orders);
            $totalOrders = $totalUsers - $totalNotSelected;

            // Check negative number
            if ($totalOrders < 0) {echo "COUNT ERROR IN  " . $c->id;exit();}

            // Update total sum
            $sumCompany++;
            $sumUsers += $totalUsers;
            $sumSelected += $totalOrders;
            $sumNotSelected += $totalNotSelected;

            // Find mail and check

           //  if($isUsed == false){
           $c->cvr = str_replace(' ', '', $c->cvr);
       //     if (in_array("cvr_".$c->cvr."_".strtolower($c->contact_email),$cvrList) == false && !in_array($this->helperReplace(strtolower($c->contact_email)),$emailToRemove) ) {
            // hvis cvr og kontakt email bliver den samlet                  && $this->diffInContactperson(strtolower($c->contact_email),$companycount->company_id) == false 
            if (in_array("cvr_".$c->cvr."_".strtolower($c->contact_email),$cvrList) == false && $this->hasChild($companycount->company_id) == false && $this->diffInContactperson(strtolower($c->contact_email),$companycount->company_id) == false  ) {
            // dummy
            if(true){
             //      if (strpos(strtolower($this->helperReplace($c->ship_to_company)), 'handel') !== false  && strpos(strtolower($this->helperReplace($c->contact_email)), '111111') === false ) {
            //    if ( $c->pid == 0 || $c->pid == "0" ) {
                $cvrList[] = "cvr_".$c->cvr."_".$c->contact_email;
                // Add data to file



/*
                if(strlen($c->ean) < 10){
                    echo strlen($c->ean);
                    return;
                }
  */
              //   if($c->cvr == "965141618") die("adsfas");

                $data = array(
                    $c->id,
                    $this->helperReplace($c->ean),
                    $this->helperReplace($c->name), $this->helperReplace($c->cvr), $this->helperReplace($c->bill_to_address), $this->helperReplace($c->bill_to_postal_code), $this->helperReplace($c->bill_to_city), $this->helperReplace($c->ship_to_company), $this->helperReplace($c->ship_to_address), $this->helperReplace($c->ship_to_postal_code), $this->helperReplace($c->ship_to_city),
                    $this->helperReplace($c->contact_name), $this->helperReplace($c->contact_phone), $this->helperReplace($c->contact_email),
                    $totalUsers,
                    $totalOrders,
                    $totalNotSelected,
                    $c->token,
                    $salesperson
                );

                // Fix encoding and add
                foreach ($data as $key => $val) {
                    $data[$key] = utf8_decode($val);
                }

                echo implode(';', $data) . "\n";
             }
            } else {
                //echo "REMOVED DUPLICATE: ".$c->id." / ".$c->contact_email." / ".$c->token;
            }

        }

        $sum = array(
            "TOTAL SUM", "ANTAL KUNDER: $sumCompany", "ANTAL KORT: $sumUsers", "ANTAL VALGT: $sumSelected", "ANTAL IKKE VALGT: $sumNotSelected"
        );

        echo implode(';', $sum) . "\n";

    }

    public function hasChild($companyID)
    {
        $rs = Company::find_by_sql("select id from company where pid =".$companyID);
        if(sizeofgf($rs) > 0 ){
            return true;
        } else {
           return false;
        }
    }
    public function diffInContactperson($contact,$companyID){
        $rsCount =CompanyOrder::find_by_sql("select * from company_order where  company_id = ".$companyID." and is_cancelled = 0  ");
        $rs =CompanyOrder::find_by_sql("select * from company_order where contact_email = '".strtolower($contact)."' and company_id = ".$companyID."  and is_cancelled = 0  ");
        if(sizeofgf($rs) > 0 ){
            if(sizeofgf($rs) == sizeofgf($rsCount) ){
                return false;
            } else {
                return true;
            }
        } else {
           return true;
        }
    }






    public function remindermaillist($shopinput, $deadline,$dummy="")
    {

        $shoplist = array();
        $inputsplit = explode(",", $shopinput);
        if (count($inputsplit) > 0) {
            foreach ($inputsplit as $input) {
                if (intval($input) > 0) {
                    $shoplist[] = intval($input);
                }
            }
        }

        if (count($shoplist) == 0) {echo "Ingen shops angivet..";return;}

        $shops = $shoplist;

        // Check deadline
        $expireDate = expireDate::getByExpireDate($deadline);


        if ($expireDate == null) {echo "Ugyldig deadline";return;}

        $removeDuplicateMails = false;

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="reminderlist-' . $deadline . '.csv"');

        $sumCompany = 0;
        $sumUsers = 0;
        $sumSelected = 0;
        $sumNotSelected = 0;
        $usedEmails = array();

        // Define headers
        $header = array("CompanyID", "Virksomhed", "CVR", "Faktura adresse", "Faktura postnr", "Faktura by", "Levering virksomhed", "Levering adresse", "Levering postnr", "Levering by", "Kontaktperson", "Telefon", "E-mail", "Antal kort", "Antal valgt", "Antal ikke valgt", "Token");
        echo implode(';', $header) . "\n";

        // Find companies
        $companylist = ShopUser::find_by_sql("SELECT company_id, count(id) as users FROM shop_user WHERE shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0 && shutdown = 0 && company_id NOT IN (12441,12456,12468,12480,12500,12501,12505,12510,12512,12514,12515,12519,12520,12522,12523,12529,12530,12531,12532,12533,12535,12536,12537,12538,12539,12540,12541,12542,12543,12544,12546,12547,12549,12550,12551,12552,12553,12554,12555,12556,12557,12558,12559,12561,12562,12563,12564,12565,12566,12568,12569,12571,12572,12573,12574,12575,12576,12577,12578,12579,12580,12581,12583,12587,12588,12618,12689,12702,12994,13014,13037,13095,13098,13441,13521,13554,13555,13700,13088,18581,19308,19309,19310,12957) GROUP BY company_id");

        //echo "SELECT company_id, count(id) as users FROM shop_user WHERE shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0 && company_id NOT IN (12441,12456,12468,12480,12500,12501,12505,12510,12512,12514,12515,12519,12520,12522,12523,12529,12530,12531,12532,12533,12535,12536,12537,12538,12539,12540,12541,12542,12543,12544,12546,12547,12549,12550,12551,12552,12553,12554,12555,12556,12557,12558,12559,12561,12562,12563,12564,12565,12566,12568,12569,12571,12572,12573,12574,12575,12576,12577,12578,12579,12580,12581,12583,12587,12588,12618,12689,12702,12994,13014,13037,13095,13098,13441,13521,13554,13555,13700,13088,18581,19308,19309,19310,12957) GROUP BY company_id";
        //echo "<br>".countgf($companylist); exit();

        // Go through companies
        foreach ($companylist as $companycount) {

            // Load company and orders
            $c = Company::find($companycount->company_id);
            $orders = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE id NOT IN (SELECT shopuser_id FROM `order`) && company_id = " . $c->id . " && shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0 && shutdown = 0");

            // Get cound
            $totalUsers = $companycount->users;
            $totalNotSelected = countgf($orders);
            $totalOrders = $totalUsers - $totalNotSelected;

            // Check negative number
            if ($totalOrders < 0) {echo "COUNT ERROR IN  " . $c->id;exit();}

            // Update total sum
            $sumCompany++;
            $sumUsers += $totalUsers;
            $sumSelected += $totalOrders;
            $sumNotSelected += $totalNotSelected;

            // Find mail and check
            $mail = mb_strtolower(trimgf($c->contact_email));
            $isUsed = in_array($mail, $usedEmails);
            if (!$isUsed) {
                $usedEmails[] = $mail;
            }
           //  if($isUsed == false){
            if (strpos(strtolower($this->helperReplace($c->ship_to_company)), 'handel') !== false) {

                // Add data to file
             //   if($c->contact_email == "11111111@11111111.dk" && $dummy != "") return;

               // if(!strpos(strtolower($this->helperReplace($c->ship_to_company)), 'handel') !== false) return;


                $data = array(
                    $c->id,
                    $this->helperReplace($c->name), $this->helperReplace($c->cvr), $this->helperReplace($c->bill_to_address), $this->helperReplace($c->bill_to_postal_code), $this->helperReplace($c->bill_to_city), $this->helperReplace($c->ship_to_company), $this->helperReplace($c->ship_to_address), $this->helperReplace($c->ship_to_postal_code), $this->helperReplace($c->ship_to_city),
                    $this->helperReplace($c->contact_name), $this->helperReplace($c->contact_phone), $this->helperReplace($c->contact_email),
                    $totalUsers,
                    $totalOrders,
                    $totalNotSelected,
                    $c->token
                );

                // Fix encoding and add
                foreach ($data as $key => $val) {
                    $data[$key] = utf8_decode($val);
                }

                echo implode(';', $data) . "\n";

            } else {
                //echo "REMOVED DUPLICATE: ".$c->id." / ".$c->contact_email." / ".$c->token;
            }

        }

        $sum = array(
            "TOTAL SUM", "ANTAL KUNDER: $sumCompany", "ANTAL KORT: $sumUsers", "ANTAL VALGT: $sumSelected", "ANTAL IKKE VALGT: $sumNotSelected"
        );

        echo implode(';', $sum) . "\n";

    }

    /*
     * HELPERS
     */

    const CONTROLLER = "cardshoppluk";
    public function getUrl($method = "")
    {return "../gavefabrikken_backend/index.php?rt=" . self::CONTROLLER . "/" . $method . "&token=dfk4dkfSdvj3fj3j4Fgnjafdopd643&";}
    private $error = "";

    private function getautovalgname($shopid)
    {

        $autovalgName = "Gave ikke valgt";
        /*
        if($shopid == 287) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 290) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 310) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 265) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 54) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 55) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 56) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 53) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 52) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 57) $autovalgName = "Autovalg";
        if($shopid == 58) $autovalgName = "Autovalg";
        if($shopid == 59) $autovalgName = "Autovalg";
         */
        return $autovalgName;
    }
    private function helperReplace($str)
    {
      $str = str_replace(';',"-",$str);
      $str = str_replace('\n',"-",$str);
      $str = str_replace("\r\n" ,"-",$str);
      return str_replace(PHP_EOL,'' , $str);
    }

    private function shoptolang($shopid)
    {

        return in_array($shopid, array(272, 57, 58, 59, 574)) ? 4 : 1;
    }

    private function getvaluealias($shopid)
    {

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
            $valueAlias = "S4-";
        }
        else if ($shopid == 1981) {
            $valueAlias = "S8-";
        }
        else if ($shopid == 4793) {
            $valueAlias = "S3-";
        }
        else if ($shopid == 5117) {
            $valueAlias = "S6-";
        }
        else if ($shopid == 9495) {
            $valueAlias = "S4AI-";
        }
        else if ($shopid == 8271) {
            $valueAlias = "SOM-";
        }

        return $valueAlias;
    }

    private function fullalias($shopid, $alias)
    {
        return $this->getvaluealias($shopid) . (strlen(intval($alias)) == 1 ? "0" : "") . $alias;
    }

    private $plukWarningList = array();
    private function addPlukWarning($message)
    {
        if (trimgf($message) == "") {
            return;
        }

        //echo "PLUK WARNING: <b>".$message."</b><br>";
        if (in_array(trimgf($message), $this->plukWarningList)) {
            return;
        }

        $this->plukWarningList[] = trimgf($message);
    }

    private function debug($text)
    {
        if (self::DEBUG_MODE == true) {
            echo $text . "<br>\r\n";
        }
    }
    
    
    private function formatPostalCode($postalCode,$shopid) {

        if($shopid == 1832 || $shopid == 1981 || $shopid == 5117 || $shopid == 4793 || $shopid == 8271 || $shopid == 9495) {
            if(strlen(trimgf($postalCode)) == 5) {
                $postalCode = trimgf($postalCode);
                $postalCode = substr($postalCode,0,3)." ".substr($postalCode,3);
            }
            return $postalCode;
        }
        return $postalCode;

    }
    
    
    /**
     * CUSTOM LIST
     */

    private function customList($shopid, $expire)
    {

    /*
    // HAS SAME BS number multiple times, each per company / deadline
        $sql = "SELECT 
	company_order.order_no,
	company.name as company_name,
	company.cvr as company_cvr,
	company.ean as company_ean,
	company.bill_to_address,
	company.bill_to_address_2,
	company.bill_to_postal_code,
	company.bill_to_city,
	company.bill_to_country,
	company.bill_to_email,
	company_order.company_name as sales_company,
	company_order.shop_name as sales_shop,
	company_order.salesperson as sales_person,
	company_order.salenote as sales_note,
	company.internal_note,
	company.rapport_note,
	company_order.quantity as sales_quantity,
	company_order.expire_date as sales_expiredate,
	company_order.certificate_no_begin,
	company_order.certificate_no_end,
	company_order.certificate_value,
	company_order.is_email,
	company_order.is_appendix_order,
	company_order.giftwrap as gift_wrap,
	company_order.gift_spe_lev as gift_carryup,
	company_order.earlyorderList as earlypresents,
	company.ship_to_company,
	company.ship_to_attention,
	company.ship_to_address,
	company.ship_to_address_2,
	company.ship_to_postal_code,
	company.ship_to_city,
	company.ship_to_country,
	company_order.spdealtxt as ship_dealtext,
	company.contact_name,
	company.contact_email,
	company.contact_phone,
	company_order.navsync_status,
	company_order.navsync_response as navsync_debitorid,
	shop_user.expire_date as card_expiredate,
	IF(company_order.expire_date = shop_user.expire_date,0,1) as has_moved_deadline,
	IF(company_order.company_id = company.id,0,1) as has_moved_company,
	count(shop_user.id) as cards_totalcount,
	sum(IF(blocked=0,1,0)) as cards_activecount,
	sum(IF(blocked=1,1,0)) as cards_closedcount
FROM company, company_order, shop_user WHERE 
	company_order.is_cancelled = 0 &&
	shop_user.company_order_id = company_order.id &&
	((shop_user.shop_id = ".intval($shopid)." && (shop_user.expire_date = '".$expire."')) || (company_order.shop_id = ".intval($shopid)." && (company_order.expire_date = '".$expire."'))) &&
	is_giftcertificate = 1 &&
	shop_user.company_id = company.id
GROUP BY shop_user.expire_date, company_order.expire_date, shop_user.company_id, shop_user.company_order_id
ORDER BY company_order.order_no ASC";

*/

$sql = "SELECT 
	company_order.order_no,
	company.name as company_name,
	company.cvr as company_cvr,
	company.ean as company_ean,
	company.bill_to_address,
	company.bill_to_address_2,
	company.bill_to_postal_code,
	company.bill_to_city,
	company.bill_to_country,
	company.bill_to_email,
	company_order.company_name as sales_company,
	company_order.shop_name as sales_shop,
	company_order.salesperson as sales_person,
	company_order.salenote as sales_note,
	company.internal_note,
	company.rapport_note,
	company_order.quantity as sales_quantity,
	company_order.expire_date as sales_expiredate,
	company_order.certificate_no_begin,
	company_order.certificate_no_end,
	company_order.certificate_value,
	company_order.is_email,
	company_order.is_appendix_order,
	company_order.giftwrap as gift_wrap,
	company_order.gift_spe_lev as gift_carryup,
	company_order.earlyorderList as earlypresents,
	company.ship_to_company,
	company.ship_to_attention,
	company.ship_to_address,
	company.ship_to_address_2,
	company.ship_to_postal_code,
	company.ship_to_city,
	company.ship_to_country,
	company_order.spdealtxt as ship_dealtext,
	company.contact_name,
	company.contact_email,
	company.contact_phone,
	company_order.navsync_status,
	company_order.navsync_response as navsync_debitorid,
	count(shop_user.id) as cards_totalcount,
	sum(IF(blocked=0,1,0)) as cards_activecount,
	sum(IF(blocked=1,1,0)) as cards_closedcount,
	sum(IF(shop_user.expire_date!=company_order.expire_date,1,0)) as cards_moveddeadline,
	sum(IF(shop_user.company_id!=company_order.company_id,1,0)) as cards_movedcompany
FROM company, company_order, shop_user WHERE 
	company_order.is_cancelled = 0 &&
	shop_user.company_order_id = company_order.id &&
	((shop_user.shop_id = ".intval($shopid)." && shop_user.expire_date = '".$expire."') || (company_order.shop_id = ".intval($shopid)." && company_order.expire_date = '".$expire."')) &&
	is_giftcertificate = 1 &&
	company_order.company_id = company.id
GROUP BY company_order.id 
ORDER BY company_order.order_no ASC";

        $results = Dbsqli::getSql2($sql);

        if(!is_array($results) || countgf($results) == 0) {

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=NULLLIST-'.$shopid.'-'.$expire.'-'.date("dmYHi").'.csv');
            echo "Ingen resultater";
            exit();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=customlist-'.$shopid.'-'.$expire.'-'.date("dmYHi").'.csv');

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

}