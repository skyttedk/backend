<?php

namespace GFUnit\cardshop\pluklister;

class PlukListe extends PlukReport
{

    private $language;
    private $valueAlias;
    private $autovalgName;
    private $autovalgVarenr;
    private $companyusers;
    private $companyidlist;
    private $companyorderidlist;
    private $companyList;
    private $companyMap;
    private $dealMap;
    private $productList;
    private $aliasmap;
    private $productmap;
    private $phpExcel;

    private $aliasCountMap;
    private $noOrderUsers;
    private $shopuserNegativeMap;

    private $companyAutogave;
    private $companyCountMap;
    private $companyCountTotal;


    private $namelabelMap;
    private $namelabelOrderMap;

    public function run() {


        $this->runDBUpdates();



        ob_start();

        //$this->isDebug = true;

        // Log start params
        $this->debugLog("<pre>" . print_r(array("shopid" => $this->shopid, "expire" => $this->expire, "useWrapped" => $this->useWrapped, "isWrapped" => $this->isWrapped, "isLabels" => $this->isLabels, "useCarryup" => $this->useCarryup, "isCarryup" => $this->isCarryup, "useLargeSmall" => $this->useLargeSmall, "isLarge" => $this->isLarge, "language" => $this->language, "valueAlias" => $this->valueAlias, "autovalgName" => $this->autovalgName), true) . "</pre>");

        // Load data
        $this->loadData();

        if ($this->isDebug) {
            $this->debugLog("<br>READY TO MAKE LIST<br>");
        }

        // Output null list
        if (count($this->companyidlist) == 0) {

            if (!$this->isDebug) {
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment;filename="NULLLIST_' . $this->shopid . '_' . $this->shop->alias . '_' . $this->expire . '' . ($this->useWrapped ? ($this->isLabels ? "_medlabels" : ($this->isWrapped ? "_medindpak" : "_udenindpak")) : "") . ($this->useCarryup ? ($this->isCarryup ? "_medspeclev" : "_udenspeclev") : ""). ($this->useLargeSmall ? ($this->isLarge ? "_over40" : "_under40") : "") . '.csv"');
                header('Cache-Control: max-age=0');
            }

            echo "Ingen virksomheder;\n";
            return;
        }


        $this->createPluklisteSheet();
        $this->createOrderListSheet();
        //$this->createOrderListSheet(true);
        $this->createCompanyListSheet();
        $this->createGavevalgSheet();
        $this->createPlukWarningSheet();

        if ($this->isDebug) {
            $this->debugLog("Debug done, finish before output");
            return;
        }

        ob_end_clean();

        $this->phpExcel->removeSheetByIndex(0);
        $this->phpExcel->setActiveSheetIndex(1);

        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="kortshop_' . $this->shopid . '_' . $this->shop->alias . '_' . $this->expire . '' . ($this->useWrapped ? ($this->isLabels ? "_medlabels" : ($this->isWrapped ? "_medindpak" : "_udenindpak")) : "") . ($this->useCarryup ? ($this->isCarryup ? "_medspeclev" : "_udenspeclev") : ""). ($this->useLargeSmall ? ($this->isLarge ? "_over40" : "_under40") : "") . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->phpExcel);
        $objWriter->save('php://output');
        exit();

    }



    private function runDBUpdates() {

        $sql  = "UPDATE `company_order` set name_label = 0 WHERE id not in (SELECT companyorder_id FROM `company_order_item` where type = 'NAMELABELS' && quantity > 0);";
        \Dbsqli::setSql2($sql);

        $sql = "UPDATE `company_order` set name_label = 1 WHERE id in (SELECT companyorder_id FROM `company_order_item` where type = 'NAMELABELS' && quantity > 0);";
        \Dbsqli::setSql2($sql);

    }



    /**************** CREATE PLUKLISTE SHEET *************************/

    private function createPluklisteSheet()
    {
        // Write header
        $sheet = $this->phpExcel->createSheet();
        $this->phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
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
        $sheet->setCellValueByColumnAndRow(1, $row, $this->valueAlias . "00");
        $sheet->setCellValueByColumnAndRow(2, $row, $this->autovalgVarenr);
        $sheet->setCellValueByColumnAndRow(3, $row, "");
        $sheet->setCellValueByColumnAndRow(4, $row, $this->autovalgName);
        $sheet->setCellValueByColumnAndRow(5, $row, "");
        $sheet->setCellValueByColumnAndRow(6, $row, $this->noOrderUsers);
        $row++;

        $totalCount = $this->noOrderUsers;

        // Output total count of all products
        foreach ($this->productList as $product) {
            $sheet->setCellValueByColumnAndRow(1, $row, $product["fullalias"]);
            $sheet->setCellValueByColumnAndRow(2, $row, $product["varenr"]);
            $sheet->setCellValueByColumnAndRow(3, $row, $product["varenrsam"]);
            $sheet->setCellValueByColumnAndRow(4, $row, $product["present_name"]);
            $sheet->setCellValueByColumnAndRow(5, $row, $product["model_name"]);
            $sheet->setCellValueByColumnAndRow(6, $row, isset($this->aliasCountMap[$product["fullalias"]]) ? $this->aliasCountMap[$product["fullalias"]] : 0);
            $totalCount += (isset($this->aliasCountMap[$product["fullalias"]]) ? $this->aliasCountMap[$product["fullalias"]] : 0);
            $row++;
        }

        // Output sum
        $sheet->setCellValueByColumnAndRow(1, $row, "");
        $sheet->setCellValueByColumnAndRow(2, $row, "");
        $sheet->setCellValueByColumnAndRow(3, $row, "");
        $sheet->setCellValueByColumnAndRow(4, $row, "");
        $sheet->setCellValueByColumnAndRow(5, $row, "Total antal");
        $sheet->setCellValueByColumnAndRow(6, $row, $totalCount);
        $row++;
    }


    /**************** DATA LOADERS *************************/

    private function createOrderListSheet($ordersOnly = false) {

        // Write order list
        $sheet = $this->phpExcel->createSheet();
        $sheet->setTitle("Ordreliste".($ordersOnly ? "-rå" : ""));

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(12);

        $sheet->setCellValueByColumnAndRow(1, 1, $this->shop->name . " (id " . $this->shopid . "): ordreliste");
        $sheet->setCellValueByColumnAndRow(3, 1, "Budget: " . $this->shop->card_value);
        $sheet->setCellValueByColumnAndRow(5, 1, "Deadline: " . $this->expire);



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

        $row = 2;
        $userCount = 0;
        $this->companyAutogave = array();
        $this->companyCountMap = array();
        $this->companyCountTotal = array();

        foreach ($this->companyList as $company) {

            if (isset($this->companyusers[$company->id]) && countgf($this->companyusers[$company->id]) > 0) {

                $compName = $company->name;
                $sheet->setBreak('A' . $row, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
                $userList = array();

                foreach ($this->companyusers[$company->id] as $cu) {
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

                // Print headlines only when not ordersonly
                if($ordersOnly == false) {

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
                    if ($this->useWrapped) {
                        $sheet->setCellValueByColumnAndRow(9, $row, "Indpakket");
                    }

                    if ($this->useCarryup) {
                        $sheet->setCellValueByColumnAndRow(10, $row, "Speciallevering");
                    }

                    if ($this->useLargeSmall) {
                        $sheet->setCellValueByColumnAndRow(11, $row, "Over / under 40 stk");
                    }

                    $row++;

                    $billToCompany = $company->pid > 0 ? $this->companyMap[$company->pid] : $company;
                    $compName = $billToCompany->name;
                    $sheet->setCellValueByColumnAndRow(1, $row, $billToCompany->name);
                    $sheet->setCellValueByColumnAndRow(2, $row, $billToCompany->bill_to_address . (trimgf($billToCompany->bill_to_address_2) == "" ? "" : ", " . $billToCompany->bill_to_address_2));
                    $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($billToCompany->bill_to_postal_code));
                    $sheet->setCellValueByColumnAndRow(4, $row, $billToCompany->bill_to_city);
                    $sheet->setCellValueByColumnAndRow(5, $row, $billToCompany->contact_name);
                    $sheet->setCellValueByColumnAndRow(6, $row, $billToCompany->contact_phone);
                    $sheet->setCellValueByColumnAndRow(7, $row, $billToCompany->cvr);
                    $sheet->setCellValueByColumnAndRow(8, $row, trimgf($billToCompany->bill_to_email) == "" ? $billToCompany->contact_email : $billToCompany->bill_to_email);
                    if ($this->useWrapped) {
                        $sheet->setCellValueByColumnAndRow(9, $row, $this->isLabels ? "Labels" : ($this->isWrapped ? "Ja" : "Nej"));
                    }

                    if ($this->useCarryup) {
                        $sheet->setCellValueByColumnAndRow(10, $row, $this->isCarryup ? "Ja" : "Nej");
                    }

                    if ($this->useLargeSmall) {
                        $sheet->setCellValueByColumnAndRow(11, $row, $this->isLarge ? "Over 40 gaver" : "Under 40 gaver");
                    }

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

                    // Check data
                    if($company->pid > 0) {
                        if($company->contact_name == "11111111") $company->contact_name = $billToCompany->contact_name;
                        if($company->contact_phone == "11111111") $company->contact_phone = $billToCompany->contact_phone;
                        if($company->cvr == "11111111") $company->cvr = $billToCompany->cvr;
                        if($company->contact_email == "11111111@11111111.dk") $company->contact_email = $billToCompany->contact_email;
                    }

                    $sheet->setCellValueByColumnAndRow(1, $row, trimgf($company->ship_to_company) == "" ? $company->name : $company->ship_to_company);
                    $sheet->setCellValueByColumnAndRow(2, $row, $company->ship_to_address . (trimgf($company->ship_to_address_2) == "" ? "" : ", " . $company->ship_to_address_2));
                    $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->ship_to_postal_code));
                    $sheet->setCellValueByColumnAndRow(4, $row, $company->ship_to_city);
                    $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                    $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                    $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                    $sheet->setCellValueByColumnAndRow(8, $row, $company->contact_email);

                    $row++;

                    // Output notes
                    $noteCount = 0;

                    if (isset($this->dealMap[$company->id]) && countgf($this->dealMap[$company->id]) > 0) {
                        foreach ($this->dealMap[$company->id] as $note) {
                            $sheet->getStyle("B" . $row . ":G" . $row)->getFont()->setBold(true);
                            $sheet->getStyle('B' . $row . ':G' . $row)->applyFromArray($BackBlue);
                            $sheet->setCellValueByColumnAndRow(2, $row, $note);
                            $row++;
                            $noteCount++;
                        }
                    }
                    if ($company->pid > 0 && isset($this->dealMap[$company->pid]) && countgf($this->dealMap[$company->pid]) > 0) {
                        foreach ($this->dealMap[$company->pid] as $note) {
                            $sheet->getStyle("B" . $row . ":G" . $row)->getFont()->setBold(true);
                            $sheet->getStyle('B' . $row . ':G' . $row)->applyFromArray($BackBlue);
                            $sheet->setCellValueByColumnAndRow(2, $row, $note);
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
                    if ($this->useWrapped) {
                        $sheet->setCellValueByColumnAndRow(9, $row, "Indpakket");
                    }

                    if ($this->useCarryup) {
                        $sheet->setCellValueByColumnAndRow(10, $row, "Speciallevering");
                    }

                    if ($this->useLargeSmall) {
                        $sheet->setCellValueByColumnAndRow(11, $row, "Over/under 40 gaver");
                    }

                    $sheet->getStyle("A" . $row . ":I" . $row)->getFont()->setBold(true);
                    $row++;


                }


                // Output orders
                foreach ($userList as $cu) {

                    $giftCertNo = isset($cu["order"]) ? $cu["order"]->gift_certificate_no : $cu["shopuser"]->username;
                    $alias = $cu["alias"];

                    if (isset($cu["order"]) && isset($cu["alias"])) {
                        if (isset($this->productmap[$cu["order"]->present_model_id])) {
                            $presentName = $this->productmap[$cu["order"]->present_model_id]["present_name"];
                            $modelName = $this->productmap[$cu["order"]->present_model_id]["model_name"];
                        } else {
                            $presentName = "Ukendt gave";
                            $modelName = "Ukendt model";
                        }
                    } else {
                        $presentName = $this->autovalgName;
                        $modelName = "";
                    }

                    if(!in_array($this->shopid,array(57,272))) {
                        $compName = $company->pid > 0 ? $company->ship_to_company : $company->name;
                    }

                    $sheet->setCellValueByColumnAndRow(1, $row, $compName);
                    $sheet->setCellValueByColumnAndRow(2, $row, $giftCertNo);
                    $sheet->setCellValueByColumnAndRow(3, $row, $alias);
                    $sheet->setCellValueByColumnAndRow(4, $row, $presentName);
                    $sheet->setCellValueByColumnAndRow(5, $row, $modelName);
                    $sheet->setCellValueByColumnAndRow(6, $row, isset($cu["order"]) ? $cu["order"]->user_name : "Gavekort " . $cu["shopuser"]->username);
                    $sheet->setCellValueByColumnAndRow(7, $row, isset($cu["order"]) ? $cu["order"]->user_email : "");
                    $sheet->setCellValueByColumnAndRow(8, $row, $cu["shopuser"]->expire_date->format("Y-m-d"));
                    if ($this->useWrapped) {
                        $sheet->setCellValueByColumnAndRow(9, $row, $this->isLabels ? "Labels" : ($this->isWrapped ? "Ja" : "Nej"));
                    }

                    if ($this->useCarryup) {
                        $sheet->setCellValueByColumnAndRow(10, $row, $this->isCarryup ? "Ja" : "Nej");
                    }

                    if ($this->useLargeSmall) {
                        $sheet->setCellValueByColumnAndRow(11, $row, $this->isLarge ? "Over 40 gaver" : "Under 40 gaver");
                    }


                    $row++;
                    $userCount++;

                    if (!isset($this->companyCountMap[$company->id])) {
                        $this->companyCountMap[$company->id] = array();
                    }

                    if (!isset($this->companyCountMap[$company->id][$alias])) {
                        $this->companyCountMap[$company->id][$alias] = 0;
                    }

                    $this->companyCountMap[$company->id][$alias]++;

                    if (!isset($this->companyCountTotal[$company->id])) {
                        $this->companyCountTotal[$company->id] = 0;
                    }

                    $this->companyCountTotal[$company->id]++;

                    if (!isset($cu["order"])) {
                        if (!isset($this->companyAutogave[$company->id])) {
                            $this->companyAutogave[$company->id] = 0;
                        }

                        $this->companyAutogave[$company->id]++;
                    }

                }

                if($ordersOnly == false) {
                    $row++;
                }

            }

        }

    }


    /**************** COMPANY LIST *************************/

    private function createCompanyListSheet()
    {
        $sheet = $this->phpExcel->createSheet();
        $sheet->setTitle("Virksomheder");

        $row = 1;
        $sheet->getStyle("A" . $row . ":Z" . $row)->getFont()->setBold(true);
        $sheet->setCellValueByColumnAndRow(1, $row, "Virksomhed");
        $sheet->setCellValueByColumnAndRow(2, $row, "CVR");
        $sheet->setCellValueByColumnAndRow(3, $row, "Antal gavekort");
        $sheet->setCellValueByColumnAndRow(4, $row, "Antal autogave");
        $sheet->setCellValueByColumnAndRow(5, $row, "Gavekort udenfor denne periode");
        $sheet->setCellValueByColumnAndRow(6, $row, "Child");
        if ($this->useWrapped) {
            $sheet->setCellValueByColumnAndRow(7, $row, "Indpakning");
        }

        if ($this->useCarryup) {
            $sheet->setCellValueByColumnAndRow(8, $row, "Speciallevering");
        }

        if ($this->useLargeSmall) {
            $sheet->setCellValueByColumnAndRow(9, $row, "Over / under 40 gaver");
        }


        $sheet->setCellValueByColumnAndRow(10, $row, "Leveringsadresse");
        $sheet->setCellValueByColumnAndRow(11, $row, "Leveringsadresse2");
        $sheet->setCellValueByColumnAndRow(12, $row, "Postnr");
        $sheet->setCellValueByColumnAndRow(13, $row, "By");

        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);

        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(10);
        $sheet->getColumnDimension('L')->setWidth(15);

        $row++;

        foreach ($this->companyList as $company) {
            if (isset($this->companyusers[$company->id])) {

                $autogaveCount = 0;
                if (isset($this->companyAutogave[$company->id]) && $company->id > 0) {
                    $autogaveCount = $this->companyAutogave[$company->id];
                }

                $sheet->setCellValueByColumnAndRow(1, $row, ($company->pid > 0 ? $company->ship_to_company : $company->name));
                $sheet->setCellValueByColumnAndRow(2, $row, $company->cvr);
                $sheet->setCellValueByColumnAndRow(3, $row, countgf($this->companyusers[$company->id]));
                $sheet->setCellValueByColumnAndRow(4, $row, $autogaveCount);
                $sheet->setCellValueByColumnAndRow(5, $row, isset($this->shopuserNegativeMap[$company->id]) ? $this->shopuserNegativeMap[$company->id] : 0);
                $sheet->setCellValueByColumnAndRow(6, $row, ($company->pid > 0 ? "child: ".$this->companyMap[$company->pid]->name : ""));

                if ($this->useWrapped) {
                    $sheet->setCellValueByColumnAndRow(7, $row, $this->isLabels ? "Labels" : ($this->isWrapped ? "Ja" : "Nej"));
                }

                if ($this->useCarryup) {
                    $sheet->setCellValueByColumnAndRow(8, $row, $this->isCarryup ? "Ja" : "Nej");
                }

                if ($this->useLargeSmall) {
                    $sheet->setCellValueByColumnAndRow(9, $row, $this->isLarge ? "Over 40 stk" : "Under 40 stk");
                }

                $sheet->setCellValueByColumnAndRow(10, $row, $company->ship_to_address);
                $sheet->setCellValueByColumnAndRow(11, $row, $company->ship_to_address_2);
                $sheet->setCellValueByColumnAndRow(12, $row, $company->ship_to_postal_code);
                $sheet->setCellValueByColumnAndRow(13, $row, $company->ship_to_city);

                $row++;

                $totalCount = 0;
                if (isset($this->companyCountTotal[$company->id])) {
                    $totalCount = $this->companyCountTotal[$company->id];
                }

                if ($totalCount != countgf($this->companyusers[$company->id])) {
                    $this->addPlukWarning("Virksomheden: " . $company->name . " (id = " . $company->id . ") har kun " . $totalCount . " med i listen, men der ligger " . countgf($this->companyusers[$company->id]) . " ordre");
                }

            }

        }
    }

    /**************** DATA LOADERS *************************/

    private function createGavevalgSheet() {

        $sheet = $this->phpExcel->createSheet();
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

        $sheet->setCellValueByColumnAndRow(1, 1, $this->shop->name . " (id " . $this->shopid . "): ordreliste");
        $sheet->setCellValueByColumnAndRow(3, 1, "Budget: " . $this->shop->card_value);
        $sheet->setCellValueByColumnAndRow(5, 1, "Deadline: " . $this->expire);

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
        $row = 2;

        foreach ($this->companyList as $company) {

            if (isset($this->companyusers[$company->id])) {

                $sheet->setBreak('A' . $row, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
                $userList = array();
                foreach ($this->companyusers[$company->id] as $cu) {
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
                if ($this->useWrapped) {
                    $sheet->setCellValueByColumnAndRow(9, $row, "Indpakket");
                }

                if ($this->useCarryup) {
                    $sheet->setCellValueByColumnAndRow(10, $row, "Speciallevering");
                }

                if ($this->useLargeSmall) {
                    $sheet->setCellValueByColumnAndRow(11, $row, "Over / under 40 stk");
                }

                $row++;

                $sheet->setCellValueByColumnAndRow(1, $row, $company->name);
                $sheet->setCellValueByColumnAndRow(2, $row, $company->bill_to_address . (trimgf($company->bill_to_address_2) == "" ? "" : ", " . $company->bill_to_address_2));
                $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->bill_to_postal_code));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('0000');
                $sheet->setCellValueByColumnAndRow(4, $row, $company->bill_to_city);
                $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                if ($this->useWrapped) {
                    $sheet->setCellValueByColumnAndRow(9, $row, $this->isLabels ? "Labels" : ($this->isWrapped ? "Ja" : "Nej"));
                }

                if ($this->useCarryup) {
                    $sheet->setCellValueByColumnAndRow(10, $row, $this->isCarryup ? "Ja" : "Nej");
                }

                if ($this->useLargeSmall) {
                    $sheet->setCellValueByColumnAndRow(11, $row, $this->isLarge ? "Over 40 gaver" : "Under 40 gaver");
                }

                $row++;

                //$companyName = (trimgf($company->ship_to_company) != "" && $this->language == 4) ? $company->ship_to_company : $company->name;
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
                //if($this->useWrapped) $sheet->setCellValueByColumnAndRow(8,$row,"Indpakket");
                $row++;

                if ($sameAddress == false) {

                    $sheet->setCellValueByColumnAndRow(1, $row, trimgf($company->ship_to_company) == "" ? $company->name : $company->ship_to_company);
                    $sheet->setCellValueByColumnAndRow(2, $row, $company->ship_to_address . (trimgf($company->ship_to_address_2) == "" ? "" : ", " . $company->ship_to_address_2));
                    $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->ship_to_postal_code));
                    //$sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode('0000');
                    $sheet->setCellValueByColumnAndRow(4, $row, $company->ship_to_city);
                    $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                    $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                    $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                    //if($this->useWrapped) $sheet->setCellValueByColumnAndRow(8,$row,$this->isWrapped ? "Ja" : "Nej");
                    $row++;

                } else {
                    $sheet->setCellValueByColumnAndRow(1, $row, $company->name);
                    $sheet->setCellValueByColumnAndRow(2, $row, $company->bill_to_address . (trimgf($company->bill_to_address_2) == "" ? "" : ", " . $company->bill_to_address_2));
                    $sheet->setCellValueByColumnAndRow(3, $row, $this->formatPostalCode($company->bill_to_postal_code));
                    $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('0000');
                    $sheet->setCellValueByColumnAndRow(4, $row, $company->bill_to_city);
                    $sheet->setCellValueByColumnAndRow(5, $row, $company->contact_name);
                    $sheet->setCellValueByColumnAndRow(6, $row, $company->contact_phone);
                    $sheet->setCellValueByColumnAndRow(7, $row, $company->cvr);
                    $row++;

                }

                $row++;
                $noteCount = 0;

                if (isset($this->dealMap[$company->id]) && countgf($this->dealMap[$company->id]) > 0) {
                    foreach ($this->dealMap[$company->id] as $note) {
                        $sheet->getStyle("B" . $row . ":F" . $row)->getFont()->setBold(true);
                        $sheet->getStyle('B' . $row . ':F' . $row)->applyFromArray($BackBlue);
                        $sheet->setCellValueByColumnAndRow(2, $row, $note);
                        $row++;
                        $noteCount++;
                    }
                }
                if ($company->pid > 0 && isset($this->dealMap[$company->pid]) && countgf($this->dealMap[$company->pid]) > 0) {
                    foreach ($this->dealMap[$company->pid] as $note) {
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

                foreach ($this->productList as $product) {
                    $count = 0;
                    if (isset($this->companyCountMap[$company->id]) && isset($this->companyCountMap[$company->id][$product["fullalias"]])) {
                        $count = $this->companyCountMap[$company->id][$product["fullalias"]];
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

    }

    /**************** WARNING SHEET *************************/

    private function createPlukWarningSheet()
    {
        // Add warning list
        if (count($this->plukWarningList) > 0) {
            $sheet = $this->phpExcel->createSheet();
            $sheet->setTitle("Warnings");
            for ($i = 0; $i < countgf($this->plukWarningList); $i++) {
                $sheet->setCellValueByColumnAndRow(2, $i + 1, ($this->plukWarningList[$i]));
                if ($this->isDebug) {
                    echo "PLUK WARNING: " . $this->plukWarningList[$i];
                }

            }
        }
    }


    /**************** DATA LOADERS *************************/

    private function loadData()
    {
        $this->loadBasic();
        $this->loadCompanyOrderDataPreCards();
        $this->loadShopUserData();
        if(count($this->companyidlist) > 0) {
            $this->loadCompanyOrderDataPostCards();
            $this->loadPresentAndModels();
            $this->loadCardOrders();
        }



        $this->initExcelObj();
    }

    private function loadBasic()
    {
        $this->language = $this->shoptolang($this->shopid);
        $this->valueAlias = $this->getValueAlias($this->shopid);
        $this->autovalgName = ($this->getAutovalgName());
        $this->autovalgVarenr = $this->getAutovalgVarenr();
        $this->debugLog("Loaded basic: language: ".$this->language.", valueAlias: ".$this->valueAlias.", autovalgName: ".$this->autovalgName.", autovalgVarenr: ".$this->autovalgVarenr);
    }


    private function loadCompanyOrderDataPreCards()
    {




        // Load gift wrap
        $companyOrders = \CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE shop_id = ".$this->shopid." && expire_date = '".$this->expire."' && order_state not in (7,8)  && giftwrap = 1");

        $this->giftwrapMap = array();
        $this->giftWrapOrderMap = array();

        foreach ($companyOrders as $co) {
            $this->giftwrapMap[$co->company_id] = true;
            $this->giftWrapOrderMap[$co->id] = true;
        }

        // Load spe_lev map (carryup)
        $companyOrders = \CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE shop_id = ".$this->shopid." && expire_date = '".$this->expire."' && order_state not in (7,8) && (dot = 1 OR gift_spe_lev = 1 OR (TRIM(spdealtxt) != '' and spdealtxt IS NOT NULL))");
        $this->carryUpMap = array();
        $this->carryUpOrderMap = array();

        foreach ($companyOrders as $co) {
            $this->carryUpMap[$co->company_id] = true;
            $this->carryUpOrderMap[$co->id] = true;
        }

        // Load labels
        $companyOrdersWLabels = \CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE shop_id = ".$this->shopid." && expire_date = '".$this->expire."' && order_state not in (7,8)  && name_label = 1");
        $this->namelabelMap = array();
        $this->namelabelOrderMap = array();

        foreach ($companyOrdersWLabels as $co) {
            $this->namelabelMap[$co->company_id] = true;
            $this->namelabelOrderMap[$co->id] = true;

            if(isset($this->giftwrapMap[$co->company_id])) {
                $this->addPlukWarning("Company id " . $co->company_id . " has both giftwrap and namelabels");
            } else if(isset($this->giftWrapOrderMap[$co->id])) {
                $this->addPlukWarning("Company order id " . $co->id . " has both giftwrap and namelabels");
            }

        }

        // Output count
        $this->debugLog("Loaded carryup: ".countgf($this->carryUpMap)." companies / ".countgf($this->carryUpOrderMap)." orders");
        $this->debugLog("Loaded giftwrap: ".countgf($this->giftwrapMap)." companies / ".countgf($this->giftWrapOrderMap)." orders");
        $this->debugLog("Loaded namelabel: ".countgf($this->namelabelMap)." companies / ".countgf($this->namelabelOrderMap)." orders");

    }

    private $largeSmallRemoved = array();

    private function loadShopUserData()
    {

        // Load shop users
        $shopuserlist = \ShopUser::find_by_sql("SELECT * FROM shop_user WHERE shop_id = " . intval($this->shopid) . " && blocked = 0 && shutdown = 0 && is_demo = 0 && expire_date = '" . $this->expire . "' && company_order_id IN (select id from company_order WHERE shop_id = ".$this->shopid." && order_state NOT IN (7,8))");
        $this->companyusers = array();
        $this->companyidlist = array();
        $this->companyorderidlist = array();
        $loadedUsers = 0;

        // Divide shopusers into companies
        foreach ($shopuserlist as $shopuser) {

            $isWrapCard = (isset($this->giftwrapMap[$shopuser->company_id]) || isset($this->giftWrapOrderMap[$shopuser->company_order_id]));
            $isNameLabelCard = (isset($this->namelabelMap[$shopuser->company_id]) || isset($this->namelabelOrderMap[$shopuser->company_order_id]));
            $isCarryupCard = (isset($this->carryUpMap[$shopuser->company_id]) || isset($this->carryUpOrderMap[$shopuser->company_order_id]));
            $validCard = (
                ($this->useWrapped == false || ($this->useWrapped == true && (($this->isLabels == true && $isNameLabelCard) || ($this->isWrapped == true && $isWrapCard) || ($this->isWrapped == false && $this->isLabels == false && !$isWrapCard && !$isNameLabelCard)))) &&
                ($this->useCarryup == false || ($this->useCarryup == true && (($this->isCarryup == true && $isCarryupCard) || ($this->isCarryup == false && !$isCarryupCard))))
            );

            if ($validCard) {

                if (!in_array($shopuser->company_id, $this->companyidlist)) {
                    $this->companyidlist[] = $shopuser->company_id;
                }

                if (!isset($this->companyusers[$shopuser->company_id])) {
                    $this->companyusers[$shopuser->company_id] = array();
                }

                if(!in_array($shopuser->company_order_id,$this->companyorderidlist)) {
                    $this->companyorderidlist[] = $shopuser->company_order_id;
                }

                $this->companyusers[$shopuser->company_id][$shopuser->id] = array("shopuser" => $shopuser, "alias" => $this->valueAlias . "00");
                $loadedUsers++;
            }
        }

        $this->debugLog("Loaded ".$loadedUsers." cards on ".countgf($this->companyusers)." companies");

        // Large small
        if($this->useLargeSmall) {

            if($this->isLarge) {
                foreach($this->companyusers as $companyid => $userlist) {
                    if(count($userlist) < 40) {
                        unset($this->companyusers[$companyid]);
                        $this->largeSmallRemoved[] = $companyid;
                    }
                }
            } else {
                foreach($this->companyusers as $companyid => $userlist) {
                    if(count($userlist) >= 40) {
                        unset($this->companyusers[$companyid]);
                        $this->largeSmallRemoved[] = $companyid;
                    }
                }
            }

        }


        $this->debugLog("After large/small sort, ".$loadedUsers." cards on ".countgf($this->companyusers)." companies");

    }

    private function loadCompanyOrderDataPostCards() {

        // Load company map
        $companyList = \Company::find_by_sql('SELECT * FROM company WHERE id IN (' . implode(",", $this->companyidlist) . ') ORDER BY ' . ($this->language == 4 ? "CAST(ship_to_postal_code as unsigned) DESC, " : "") . ' name ASC, ship_to_company ASC');

        $this->companyMap = array();
        foreach ($companyList as $company) {
            $this->companyMap[$company->id] = $company;
        }

        // Init deal data
        $dealCompany = array();
        $dealOrder = array();
        $this->dealMap = array();

        // Load company orders
        $companyOrders = \CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE id in (".implode(",",$this->companyorderidlist).")");
        foreach ($companyOrders as $co) {

            if ($co->gift_spe_lev == 1) {
                if (!isset($this->dealMap[$co->company_id])) {
                    $this->dealMap[$co->company_id] = array();
                }

                if(!in_array("Opbæring tilvalgt.",$this->dealMap[$co->company_id])) {
                    $this->dealMap[$co->company_id][] = "Opbæring tilvalgt.";
                }
                $dealCompany[$co->cvr] = true;
                $dealOrder[$co->id] = true;
            }

            if ($co->dot == 1) {
                if (!isset($this->dealMap[$co->company_id])) {
                    $this->dealMap[$co->company_id] = array();
                }

                if(!in_array("DOT tilvalgt.",$this->dealMap[$co->company_id])) {
                    $this->dealMap[$co->company_id][] = "DOT tilvalgt.";
                }
                $dealOrder[$co->id] = true;
                $dealCompany[$co->cvr] = true;
            }

            if (trimgf($co->spdealtxt) != "") {
                if (!isset($this->dealMap[$co->company_id])) {
                    $this->dealMap[$co->company_id] = array();
                }

                if(!in_array(trimgf($co->spdealtxt), $this->dealMap[$co->company_id])) {
                    $this->dealMap[$co->company_id][] = trimgf($co->spdealtxt);
                }
                $dealOrder[$co->id] = true;
                $dealCompany[$co->cvr] = true;
            }

        }

        $this->debugLog("Deal companies, from order note: " . countgf($this->dealMap) . " / ".countgf($dealOrder)." / ".countgf($dealCompany));



        // Put companies with deal text in top
        $dealList = array();
        $nondealList = array();
        foreach ($companyList as $company) {

            if($company->pid > 0 && !isset($this->companyMap[$company->pid])) {
                $this->companyMap[$company->pid] = \Company::find($company->pid);
            }

            if (isset($dealCompany[$company->cvr])) {
                $dealList[] = $company;
            } else if($company->pid > 0 && isset($dealCompany[$this->companyMap[$company->pid]->cvr])) {
                $dealList[] = $company;
            } else {
                $nondealList[] = $company;
            }

        }

        // Sort
        if($this->language == 4) {
            $nondealList = $this->norwayCompanySort($nondealList);
            $dealList = $this->norwayCompanySort($dealList);
        }

        // Merge
        $this->companyList = array_merge($dealList, $nondealList);

    }

    private function norwayCompanySort($companyList) {

        for($i=0;$i<count($companyList);$i++) {
            for($j=$i+1;$j<count($companyList);$j++) {

                $areaI = $this->getNorwayPostalCodeArea($companyList[$i]->ship_to_postal_code);
                $areaJ = $this->getNorwayPostalCodeArea($companyList[$j]->ship_to_postal_code);

                if($areaI < $areaJ) {
                    $tmp = $companyList[$i];
                    $companyList[$i] = $companyList[$j];
                    $companyList[$j] = $tmp;
                }

            }
        }

        return $companyList;

    }

    private function getNorwayPostalCodeArea($postalCode) {

        $postalCode = intval($postalCode);

        $areaList = array(
            1 => array(
                array(0001,1429),
                array(1470,1476),
                array(1480,1481),
                array(2000,2021)
            ),
            2 => array(
                array(1430,1459),
                array(1477,1478),
                array(1482,1970),
                array(2022,2409),
                array(2411,2411),
                array(2418,2418),
                array(2600,2606),
                array(2608,2609),
                array(2611,2611),
                array(2613,2615),
                array(2618,2624),
                array(2720,3519),
                array(3600,3999)
            ),
            3 => array(
                array(2410,2410),
                array(2412,2416),
                array(2420,2581),
                array(2607,2607),
                array(2610,2610),
                array(2612,2612),
                array(2616,2617),
                array(2625,2625),
                array(2630,2718),
                array(3520,3595),
                array(4000,7506)
            ),
            4 => array(
                array(7510,7999)
            ),
            5 => array(
                array(8000,9999)
            )

        );

        foreach($areaList as $areaCode => $postalList) {
            foreach($postalList as $postalInterval) {
                if($postalCode >= $postalInterval[0] && $postalCode <= $postalInterval[1]) {
                    return $areaCode;
                }
            }
        }

        return 0;

    }

    private function loadPresentAndModels() {

        // LOAD PRESENTS AND MODELS
        $presentmodelmap = array();
        $this->productList = array();
        $presentidlist = array();
        $presentmodelidlist = array();
        $this->aliasmap = array();
        $this->productmap = array();

        // Process models
        $presentmodellist = \PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE language_id = " . $this->shoptolang($this->shopid) . " && present_id IN (SELECT id FROM `present` WHERE `shop_id` = " . intval($this->shopid) . ") ORDER BY `present_model`.`aliasletter` ASC");
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

        $this->debugLog("Loaded models: " . countgf($presentmodellist) . " loaded, divided into " . countgf($presentmodelmap) . " presents");

        // Process presents and models into a productlist
        $presentlist = \Present::find_by_sql("SELECT * FROM present where shop_id = " . intval($this->shopid) . " && id not in (SELECT present_id FROM shop_present WHERE shop_id = " . intval($this->shopid) . " && is_deleted = 1 && present_id NOT IN (select present_id FROM `order` WHERE shop_id = " . intval($this->shopid) . ")) ORDER BY alias");
        foreach ($presentlist as $present) {
            if (isset($presentmodelmap[$present->id]) && countgf($presentmodelmap[$present->id]) > 0) {
                foreach ($presentmodelmap[$present->id] as $model) {

                    $isActive = true;
                    if ($model->language_id == 1) {
                        $isActive = $model->active == 0;
                    } else {
                        $dkmodel = \PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id = " . intval($model->model_id) . " && language_id = 1");
                        $isActive = $dkmodel[0]->active == 0;
                    }

                    if (trimgf($model->fullalias) == "" && $isActive) {
                        $this->addPlukWarning('Present '.$model->present_id." / model ". $model->model_id." has no alias - ".$present->nav_name);
                    }

                    $this->aliasmap[$model->model_id] = $this->fullalias($this->shopid, $model->fullalias);
                    $product = array(
                        "present_name" => $model->model_name,
                        "model_name" => $model->model_no,
                        "varenr" => $model->model_present_no,
                        "varenrsam" => implode(", ",$this->getSampakVarenrList($model->model_present_no)),
                        "alias" => $model->fullalias,
                        "fullalias" => $this->fullalias($this->shopid, $model->fullalias),
                        "active" => $isActive,
                        "present" => $present,
                        "model" => $model,
                    );

                    $this->productmap[$model->model_id] = $product;
                    $this->productList[] = $product;

                }

            } else {
                $this->addPlukWarning("The present ".$present->id." has no models - ".$present->nav_name);
            }
        }

        $this->debugLog("Loaded productlist: " . countgf($this->productList) . "");


    }


    private function loadCardOrders() {

        // LOAD AND PROCESS ORDERS
        $selectedPresentList = array();
        $selectedModelList = array();
        $this->aliasCountMap = array();

        $notFoundOrders = array();
        $notFoundUsers = array();

        // ONLY SET THIS IF ONLY SPECIFIC PRESENTS SHOULD BE PULLED
        $filterPresent = array();

        //$orderList = \Order::find_by_sql("SELECT * FROM `order` WHERE shop_id = " . $this->shopid . " && " . (count($filterPresent) > 0 ? "present_id IN (" . implode(",", $filterPresent) . ") &&" : "") . " shopuser_id IN (select id from shop_user WHERE expire_date = '" . $this->expire . "' && is_demo = 0 && shutdown = 0 && blocked = 0 && shop_id = " . $this->shopid . " && company_order_id IN (" . implode(",", $this->companyorderidlist) . ")) && is_demo = 0 ");
        $orderList = \Order::find_by_sql("SELECT `order`.*, shop_user.company_id as shopuser_company_id FROM `order`, shop_user WHERE shop_user.blocked = 0 && `order`.shopuser_id = shop_user.id && " . (count($filterPresent) > 0 ? "`order`.present_id IN (" . implode(",", $filterPresent) . ") &&" : "") . " shop_user.expire_date = '" . $this->expire . "' && shop_user.is_demo = 0 && shop_user.shutdown = 0 && shop_user.blocked = 0 && shop_user.shop_id = " . $this->shopid . " && shop_user.company_order_id IN (" . implode(",", $this->companyorderidlist) . ")");

        foreach ($orderList as $order) {
            if (isset($this->companyusers[$order->shopuser_company_id]) && isset($this->companyusers[$order->shopuser_company_id][$order->shopuser_id])) {

                // Add to company user
                if (!isset($this->companyusers[$order->shopuser_company_id][$order->shopuser_id]["order"])) {
                    $this->companyusers[$order->shopuser_company_id][$order->shopuser_id]["order"] = $order;
                    if (!in_array($order->present_id, $selectedPresentList)) {
                        $selectedPresentList[] = $order->present_id;
                    }

                    if (!in_array($order->present_model_id, $selectedModelList)) {
                        $selectedModelList[] = $order->present_model_id;
                    }

                    if (!isset($this->aliasmap[$order->present_model_id])) {
                        $this->addPlukWarning("Order " . $order->id . " - could not find present model id in productlist: " . $order->present_model_id . " (present: " . $order->present_id . ")");
                    } else {
                        if (!isset($this->aliasCountMap[$this->aliasmap[$order->present_model_id]])) {
                            $this->aliasCountMap[$this->aliasmap[$order->present_model_id]] = 0;
                        }

                        $this->aliasCountMap[$this->aliasmap[$order->present_model_id]]++;
                        $this->companyusers[$order->shopuser_company_id][$order->shopuser_id]["alias"] = $this->aliasmap[$order->present_model_id];

                    }

                } else {
                    $this->addPlukWarning("Order id " . $order->id . " could not add to user " . $order->shopuser_id . " - already has order - " . $this->companyusers[$order->shopuser_company_id][$order->shopuser_id]["order"]->id);
                }
            } else if(!in_array($order->shopuser_company_id,$this->largeSmallRemoved)) {
                $this->addPlukWarning("Order id " . $order->id . " could not find company or companyuser - Company " . $order->shopuser_company_id . " " . (isset($this->companyusers[$order->shopuser_company_id]) ? "FOUND" : "NOT FOUND") . " / User " . $order->shopuser_id);
                $notFoundOrders[] = $order->id;
                $notFoundUsers[] = $order->shopuser_id;
            }
        }

        $this->debugLog("NOT FOUND ORDERS: (" . implode(",", $notFoundOrders) . ")");
        $this->debugLog("NOT FOUND USERS: (" . implode(",", $notFoundUsers) . ")");

        // Count users with no orders
        $this->noOrderUsers = 0;
        foreach ($this->companyusers as $culi => $cul) {
            foreach ($cul as $cui => $cu) {
                if (!isset($cu["order"]) && isset($this->companyMap[$cu["shopuser"]->company_id])) {
                    $this->noOrderUsers++;
                }
                if (count($filterPresent) > 0) {
                    if (!isset($cu["alias"]) || !isset($cu["order"]) || !in_array($cu["order"]->present_id, $filterPresent)) {
                        unset($this->companyusers[$culi][$cui]);
                    }
                }
            }

        }

        $this->debugLog("Loaded orders: " . countgf($orderList) . " loaded, there is " . $this->noOrderUsers . " shopusers without orders");

        // Check selected presents is in productlist
        if (count($selectedPresentList) > 0) {
            foreach ($selectedPresentList as $presentid) {
                $hasPresent = false;
                foreach ($this->productList as $product) {
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
                foreach ($this->productList as $product) {
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
        $shopuserNegativeList = \ShopUser::find_by_sql("SELECT * FROM shop_user WHERE shop_id = " . $this->shopid . " && blocked = 0 && shutdown = 0 && is_demo = 0 && expire_date != '" . $this->expire . "'");
        $this->shopuserNegativeMap = array();
        foreach ($shopuserNegativeList as $su) {
            if (!isset($this->shopuserNegativeMap[$su->company_id])) {
                $this->shopuserNegativeMap[$su->company_id] = 0;
            }
            $this->shopuserNegativeMap[$su->company_id]++;
        }

    }

    private function initExcelObj()
    {
        // Init phpexcel
        $this->phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    }



    /********** EXCEL HELPERS ***************/

    private $plukWarningList = array();
    private function addPlukWarning($message)
    {
        if (trimgf($message) == "") {
            return;
        }

        echo "PLUK WARNING: <b>".$message."</b><br>";
        if (in_array(trimgf($message), $this->plukWarningList)) {
            return;
        }

        $this->plukWarningList[] = trimgf($message);
    }


}