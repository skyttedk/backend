<?php

namespace GFUnit\lister\valgshopadresser;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct() {
        parent::__construct(__FILE__);
    }

    public function index() {

        $sql = "SELECT LOWER(TRIM(pakkeri)) as pakkeri, count(id) as shops FROM `shop_board` where active = 1 && pakkeri != '' group by LOWER(TRIM(pakkeri));";
        $list = \Shopboard::find_by_sql($sql);

        ?><table><tr><td>Pakkeri</td><td>Antal shops</td><td>Hent liste</td></tr><?php

        foreach($list as $items) {
            ?><tr><td><?php echo $items->pakkeri; ?></td><td><?php echo $items->shops; ?></td><td><a href="index.php?rt=unit/lister/valgshopadresser/download/<?php echo urlencode($items->pakkeri); ?>">Hent liste</a></td></tr><?php
        }

        ?></table><?php


    }

    public function download($pakkeri="") {

        if(trim($pakkeri) == "") {
            echo "Der er ikke valgt en pakkeri";
            return;
        }

        $sql = "select shop_address.*, shop.name as shop_name from shop, shop_address where shop.id = shop_address.shop_id && shop.id in (SELECT fk_shop FROM `shop_board` where active = 1 && pakkeri LIKE '".trim($pakkeri)."') order by shop_name ASC, shop_address.name ASC";
        $shopAddressList = \ShopAddress::find_by_sql($sql);


       // echo "DOWNLOAD LISTE!";

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

        foreach($shopAddressList as $row) {
            $outsheet->setCellValueByColumnAndRow(1,$outRow,$row->name);
            $outsheet->setCellValueByColumnAndRow(2,$outRow,$row->address);
            $outsheet->setCellValueByColumnAndRow(3,$outRow,"");
            $outsheet->setCellValueByColumnAndRow(4,$outRow,$row->zip);
            $outsheet->setCellValueByColumnAndRow(5,$outRow,$row->city);
            $outsheet->setCellValueByColumnAndRow(6,$outRow,$row->country);
            $outsheet->setCellValueByColumnAndRow(7,$outRow,"");
            $outsheet->setCellValueByColumnAndRow(8,$outRow,$row->att);
            $outsheet->setCellValueByColumnAndRow(9,$outRow,$row->phone);
            $outsheet->setCellValueByColumnAndRow(10,$outRow,"");
            $outsheet->setCellValueByColumnAndRow(11,$outRow,$row->shop_name);
            $outRow++;
        }

        // Send http headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="pakkeri-adresseliste-valgshops'.$pakkeri.'.xlsx"');
        header('Cache-Control: max-age=0');


        // Output as xlsx file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($phpExcel);
        $writer->save("php://output");


    }


}