<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../thirdparty/phpexcel/PHPExcel.php");
//include("../thirdparty/phpexcel/PHPExcel/IOFactory.php");
include("sms/db/db.php");

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="hej.xlsx"');
	header('Cache-Control: max-age=0');
	header('Cache-Control: max-age=1');
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public');

$sheet = new PHPExcel();
$sheet->createSheet();
$sheet->setActiveSheetIndex(0)
      ->setCellValue('A1','hallo1')
      ->setCellValue('A2','hallo2');


  $file =  PHPExcel_IOFactory::createWriter($sheet, 'Excel2007');
  $file->save("php://output");

/*
$sheet->setTitle("Adresseliste");
$sheet->setCellValueByColumnAndRow(1, 1, "Navn");
$sheet->setCellValueByColumnAndRow(2, 2, "dk");
$sheet->setActiveSheetIndex(0);
  */

die("asdf");
 // https://gavefabrikken.dk//gavefabrikken_backend/component/exceltest.php
$db = new Dbsqli();
$db->setKeepOpen();
 // SELECT * FROM `mail_queue` WHERE `recipent_email` LIKE 'ADMA@TOPSOE.COM' ORDER BY `mail_queue`.`id` DESC

   $sql = "

SELECT
    present_model.model_present_no,
    p.nav_name,
    present_model.model_no,
    l.caption,
    present_model.model_name,
    l.long_description,
    l.short_description,
    p.vendor,
    p.pt_price,
    p.oko_present,
    p.omtanke
FROM
    `present_model`
INNER JOIN(
    SELECT
        *
    FROM
        `present`
    WHERE
        `copy_of` = 0  AND `active` = 1 AND `deleted` = 0
) p
ON
    present_model.present_id = p.id
INNER JOIN(
    SELECT
        *
    FROM
        `present_description`
    WHERE
        language_id = 5 and `long_description` != '###' and `long_description` != ''
) l
ON
    present_model.present_id = l.present_id
WHERE
    `present_model`.language_id = 5 AND `present_model`.active = 0 and  l.long_description != '###' and l.long_description != ''

";

   $data = [];
   $orderList = $db->get($sql);
   $t = "<table><tr><td>Varenr</td><td>Erp navn</td><td>Overskrift</td><td>Gave navn</td><td>nodel navn</td></tr>";
   foreach($orderList["data"] as $list){
      $tempArr = [];
      $tempArr["PRODUCT_NO"] = $list["model_present_no"];
      $tempArr["ERP_PRODUCT_NAME"] = utf8_decode($list["nav_name"]);
      $tempArr["VARIATION_VALUE"] = utf8_decode($list["model_no"]);
      $tempArr["VARIATION_VALUE_LANG_DA"] = utf8_decode($list["caption"]);
      $tempArr["VARIATION_VALUE_model"] = utf8_decode($list["model_name"]);
      $tempArr["DESCRIPTION_DA"] =  base64_decode($list["long_description"]);
      $tempArr["SHORT_DESCRIPTION_DA"] = base64_decode($list["short_description"]);
      if($list["pt_price"] != null){
            $j = json_decode($list["pt_price"]);
            $tempArr["BUDGET_PRICE_DA"] = $j->pris;

      } else { $tempArr["BUDGET_PRICE_DA"] = ""; }

      $tempArr["KUN_HOS_GAVEFABRIKKEN_DA"] = ($list["oko_present"] == true ) ? 1:0;
      $tempArr["GAVE_MED_OMTANKE_DA"] = ($list["oko_present"] == 1 ) ? 1:0;
      $t.="<tr><td>".$tempArr["PRODUCT_NO"]."</td>
          <td>".$tempArr["ERP_PRODUCT_NAME"]."</td>

      <td>".$tempArr["VARIATION_VALUE_LANG_DA"]."</td>
      <td>".$tempArr["VARIATION_VALUE_model"]."</td>
       <td>".$tempArr["VARIATION_VALUE"]."</td>


            </tr>";
      array_push($data,$tempArr);
   }
    echo $t.= "</table>";
    //print_R($data);




    /*
      $myfile = fopen("newfile1.txt", "w") or die("Unable to open file!");
     $txt = json_encode($data);

      fwrite($myfile,$txt );
    fclose($myfile);
    */



   //echo json_encode($orderList);


