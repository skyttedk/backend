<?php

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
include("../thirdparty/phpexcel/PHPExcel.php");

$excel = new PHPExcel();
// https://gavefabrikken.dk//gavefabrikken_backend/component/exceltest.php
//selecting active sheet
$excel->setActiveSheetIndex(0);

//populate the data
$db = new Dbsqli();
$db->setKeepOpen();
$sql = "SELECT
 `present_model`.id,
    model_present_no,
p.nav_name,
model_name,
model_no,
l.caption,
l.long_description,
l.short_description,
p.vendor,
l.caption_presentation,
p.omtanke,
p.kunhos

FROM
    `present_model`
INNER JOIN(
    SELECT
        *
    FROM
        `present`
    WHERE
        `copy_of` = 0 AND  present.`created_datetime` > '2022-01-01 00:00:00' AND `active` = 1 AND `deleted` = 0
) p
ON
    present_model.present_id = p.id
INNER JOIN(
    SELECT
        *
    FROM
        `present_description`
    WHERE
        language_id = 1
) l
ON
    present_model.present_id = l.present_id
WHERE
    `present_model`.language_id = 1 AND `present_model`.active = 0 and `present_model`.model_present_no != 'sam' and 

`model_present_no`  in ('20758801BTB','20755401BTB','20708801','20705401','20759601BTB','20728801','20725401','46-01712','46-011912','46-013022','46-012112','46-013118','46-012512','SAM3213','46-012618','SAM3212','15817','SAM3225','15699','14942','15810','89006','SAM3224','15779','SAM3289','SAM3290','SAM3291','SAM3292','SAM3293','BEZ1096DK','BEZ2006DK','SAM3211','3192549','GF2210','GF2211','GF2212','GF2213','GF2214','GF2215','GF2216','SAM3214','SAM3215','10004','SAM3216','SAM3217','10504','12001','80450039','J10-204','CT4120','CT7105','CAT-PAKKE1','GA5030R','MC.155.21.137','PY.141.11.127','CAT-PAKKE2','AK.149.11.127','SJ.141.11.131','10705','10644','10654','10653','10645','10671','10660','10699','10672','10646','10669','10673','10674','10670','705223105028','705222103028','705223105038','705222103038','9111','7262','7260','8223','9014','9015','1040','94887296','887315','SAM3235','25858','25262','25263','23556','23560','23570','25885','23558','23564','23572','23559','23565','23914','26308','26310','23550','23553','23577','26393','26394','27480','26304','26306','23552','23555','27658','23921','SAM','27493','27500','27479','27495','27535','27530','15412','27574','15416','25983','J25310601','J25310701','D106','1062490','1062484','1017355','1062482','1058879','1061058','1058883','1058885','1061059','1061132','1062912','1062911','SAM3209','1061048','1017358','1062487','1020523','1062485','1016891','1018207','1065429','1065430','30-Lgwineset','30-LG0066','30-COCKTAIL','30-LG01-10,04A,04C','30-LG01-10,04A,04CBCB','30-LG0004','30-LG0022KF','66-LR-160','SAM3208','101-Z-SET8A-02','66-LR-134','220121','220125','220148','220105','220138','220117','220112','220131','220119','220120','220152','220159','220158','220147','220146','220109','220145','220130','220110','220102','220136','220154','220104','220137','SAM3260','220153','220106','220107','220126','220144','220133','220151','220135','220157','220166','220150','220128','220118','220167','220132','220108','220101','220129','220127','220122','220155','220116','220115','220103','220156','220111','220114','220113','220124','220172','SAM3232','10019648','10019593','10019313','10691','10692','220300','220301','220302','220303','220304','220305','10597-64SS21-A46','10597-64SS21-A47','10597-GREEN-200','10597-GREEN-220','10597-ARCHIVE-200','10597-ARCHIVE-220','43008','SAM3233','SAM3234','220310','220309','220307','220308','410718','RUB30C.410','RUB30C.001','907-1','907-5','907-4','907-3','907-2','5703957200220','5703957200190','5703957200183','5703957200213','5703957200206','220306','KFKE87','KFKE82','KFOZ10','5796RPV','5795RPV','5794COW','5793COW','12032','12036','SORT13HV','SORT15GR','SORT18OG','20673','20672','201477','201478','20671','201479','4343502','20670','4343501','201556','201555','4343500','SAM3221','25209','39134','2014462','39286','25536','SAM3218','702100','701001','ESPM01','NEWY22','BDST19120-1','BDST60120-1','REVSD4C-QW','REVDD12C-QW','REVDS12C-QW','REVJ12C-QW','DWMT73801-1','985','919','991','900-8','20001-0','222344953','599999953') 


ORDER BY `present_model`.`id` DESC "
;

$rs = $db->get($sql);


$row = 1;

$excel->getActiveSheet()
    ->setCellValue('A'.$row , 'id')
    ->setCellValue('B'.$row , 'Product no')
    ->setCellValue('C'.$row , 'Erp product name')
    ->setCellValue('D'.$row , 'Product name_DA')
    ->setCellValue('E'.$row , 'Product name_NO')
    ->setCellValue('F'.$row , 'Product name_SE')
    ->setCellValue('G'.$row , 'Description_DA')
    ->setCellValue('H'.$row , 'Description_NO')
    ->setCellValue('I'.$row , 'Description_SE')
    ->setCellValue('J'.$row , 'Short description_DA')
    ->setCellValue('K'.$row , 'Short description_SE')
    ->setCellValue('L'.$row , 'Short description_NO')
    ->setCellValue('M'.$row , 'Supplier')
    ->setCellValue('N'.$row , 'Tags')
    ->setCellValue('O'.$row , 'Budget price DA')
    ->setCellValue('P'.$row , 'Budget price NO')
    ->setCellValue('Q'.$row , 'Budget price SE')
    ->setCellValue('R'.$row , 'Storeview')
    ->setCellValue('S'.$row , 'Category')
    ->setCellValue('T'.$row , 'Height')
    ->setCellValue('U'.$row , 'Width')
    ->setCellValue('V'.$row , 'Length')
    ->setCellValue('W'.$row , 'Weight')
    ->setCellValue('X'.$row , 'Title_SE')
    ->setCellValue('Y'.$row , 'Title_NO')
    ->setCellValue('Z'.$row , 'Title_DA')
    ->setCellValue('AA'.$row , 'Kun hos GaveFabrikken_DA')
    ->setCellValue('AB'.$row , 'Kun hos GaveFabrikken_SE')
    ->setCellValue('AC'.$row , 'Kun hos GaveFabrikken_DA')
    ->setCellValue('AD'.$row , 'Gave med omtanke_DA')
    ->setCellValue('AE'.$row , 'Gave med omtanke_NO')
    ->setCellValue('AF'.$row , 'Gave med omtanke_SE')
    ->setCellValue('AG'.$row , 'Udsalgsprisberegner')
;

//increment the row

function html($str){

    $tags = array("<p>","</p>","<strong>","</strong>","</p>","<em>","</em>","<br />","<br>","<ul>","<li>","</li>","</a>","<a>","<p","<li","</ul>");
    $hastags = array("#p#","#/p#","#strong#","#/strong#","#/p#","#em#","#/em#","#br /#","#br#","#ul#","#li#","#/li#","#/a#","#a#","#p","#li","#/ul#");
    $onlyhastags = str_replace($tags, $hastags, $str);
    $nohtmlTags = strip_tags($onlyhastags);
    return  str_replace($hastags, $tags, $nohtmlTags);


}

function clean($str){
    return str_replace("###", "", $str);
}



//make table headers
$dub = [];



foreach($rs["data"] as $key=>$val ){
    if(in_array($val["model_present_no"],$dub)){

    } else {
        array_push($dub,$val["model_present_no"]);
        if($val["model_present_no"] != "" && strpos($val["model_present_no"],"*") === false && $val["model_present_no"] != "?"  )
        {



            $row++;
            $omtanke = $val["model_name"] == 0 ? "": "sandt";
            $kunhos  = $val["kunhos"] == "" ? "": "sandt";
            $excel->getActiveSheet()
                ->setCellValue('A'.$row , '')
                ->setCellValue('B'.$row , $val["model_present_no"])
                ->setCellValue('C'.$row , $val["nav_name"])
                ->setCellValue('D'.$row , $val["model_name"] ." - " .$val["model_no"])
                ->setCellValue('E'.$row , '')
                ->setCellValue('F'.$row , '')
                ->setCellValue('G'.$row , html(base64_decode($val["long_description"])) )
                ->setCellValue('H'.$row , '')
                ->setCellValue('I'.$row , '')
                ->setCellValue('J'.$row , html(base64_decode($val["short_description"]) ))
                ->setCellValue('K'.$row , '')
                ->setCellValue('L'.$row , '')
                ->setCellValue('M'.$row , $val["vendor"])
                ->setCellValue('N'.$row , '')
                ->setCellValue('O'.$row , '')
                ->setCellValue('P'.$row , '')
                ->setCellValue('Q'.$row , '')
                ->setCellValue('R'.$row , 'Gavevalg')
                ->setCellValue('S'.$row , '')
                ->setCellValue('T'.$row , '')
                ->setCellValue('U'.$row , '')
                ->setCellValue('V'.$row , '')
                ->setCellValue('W'.$row , '')
                ->setCellValue('X'.$row , '')
                ->setCellValue('Y'.$row , '')
                ->setCellValue('Z'.$row , clean($val["caption_presentation"]))
                ->setCellValue('AA'.$row , $kunhos)
                ->setCellValue('AB'.$row , '')
                ->setCellValue('AC'.$row , '')
                ->setCellValue('AD'.$row , $omtanke)
                ->setCellValue('AE'.$row , '')
                ->setCellValue('AF'.$row , '')
                ->setCellValue('AG'.$row , '')
            ;
        }
    }
}



//styling




//redirect to browser (download) instead of saving the result as a file
//this is for MS Office Excel xls format
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="import_gavevalg.xlsx"');
header('Cache-Control: max-age=0');

//write the result to a file
$file = PHPExcel_IOFactory::createWriter($excel,'Excel2007');
//output to php output instead of filename
$file->save('php://output');
