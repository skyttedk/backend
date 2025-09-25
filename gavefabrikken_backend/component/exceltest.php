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
        `copy_of` = 0  AND `deleted` = 0
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
    `present_model`.language_id = 1 AND `present_model`.active = 0 and

`model_present_no`  in (
'89006',
'SAM3289',
'SAM3290',
'SAM3291',
'SAM3292',
'SAM3293',
'SAM3211',
'34624',
'34625',
'7261',
'1016891',
'30-LG01-10,04A,04CBCB',
'30-LG0022KF',
'220159',
'220158',
'SAM3233',
'SAM3210',
'201462',
'SAM3219',
'SAM3220',
'701001',
'BDST19120-1',
'REVSD4C-XJ',
'REVJ12C-QW',
'SAM3257',
'SAM3258',
'SAM3259',
'210150',
'210148',
'SAM3129',
'SAM3089',
'200149',
'SAM2125',
'210133',
'210121',
'SAM3018',
'210110',
'210202',
'210137',
'20811701BTB',
'20811794BTB',
'210147',
'210164',
'210161',
'221',
'221-2',
'221-1',
'222',
'222-2',
'222-1',
'43630',
'43636',
'43640',
'43646',
'20811791BTB',
'20811723BTB',
'20811794BTB',
'180120',
'210174',
'210173',
'210175',
'821488',
'14557',
'SAM3157',
'14549',
'14538',
'SAM2032',
'SAM2031',
'SAM2030',
'821494',
'82049925',
'210117',
'B50131110',
'J25310201',
'5062AS',
'NTR912',
'NTT912',
'210146',
'DES241G',
'210108',
'200105',
'N401-3090DU',
'SAM1878',
'SAM1880',
'9006',
'9005',
'7085',
'7112',
'200172',
'CD02',
'7210906010303',
'94202411',
'94202436',
'204938',
'204949',
'551771',
'551772',
'94202412',
'202424',
'94202413',
'204920',
'204924',
'202728',
'204928',
'210112',
'210111',
'210129',
'190109',
'210138',
'1054778',
'1054777',
'1062901',
'1016473',
'1062516',
'SAM2089',
'200140',
'4605120',
'10019193',
'SAM2058',
'10019190',
'10018796',
'10019430',
'10018804',
'20000939',
'3586663',
'10606-TW5168-F48',
'10104-TW5167-A46',
'210305',
'10597-64SS21-A46',
'10597-64SS21-A47',
'BTB-10051',
'BTB-10052',
'10597-19SS21-A46',
'10597-19SS21-A47',
'200141',
'SAM2046',
'SAM3025',
'SAM3026',
'210143',
'3250070',
'30-RLAC1112',
'599000',
'507594',
'190112',
'210101',
'200153',
'210104',
'210151',
'2008-10',
'2009-10',
'210114',
'210113',
'210128',
'210156',
'190117',
'SAM1351',
'190118',
'200155',
'SAM2085',
'SAM2086',
'SAM2092',
'SAM2088',
'SAM2087',
'210177',
'SAM3016',
'210169',
'210141',
'210209',
'210201',
'140320017',
'210124',
'210168',
'200112',
'210160',
'210125',
'190129',
'160101',
'200114',
'KFXWT112',
'XKFXWT90',
'XKFXKE101',
'XKFXRT02',
'XKFXHT02',
'30-LG0013',
'40-5CKOLIVEN',
'30-LG0101',
'210109',
'983462',
'983467',
'L37.05.05.02.02.04',
'L41.05.05.02.02.04',
'SAM2072',
'SAM2036',
'SAM2037',
'201460',
'201461',
'201461',
'12266',
'12267',
'4661139',
'4661119',
'200108',
'210116',
'1002',
'1001',
'201023',
'12867',
'15791',
'14272',
'14989',
'14990',
'200143',
'200148',
'SAM2039',
'210157',
'2072BTB',
'20729401BTB',
'153601',
'1058880',
'1058886',
'Z00602-1',
'Z00601-1',
'1061131',
'1017352',
'1016871',
'1017341',
'1017380',
'607001',
'00-1230-5322',
'200101',
'210178',
'170101',
'200123',
'190131',
'190119',
'SAM1475',
'SAM1476',
'200102',
'200103',
'200104',
'210165',
'210166',
'210127',
'210158',
'210203',
'200124',
'210153',
'210154',
'SAM2034',
'SAM2035',
'SAM2033',
'B-19-3',
'B-21-22',
'B-21-20',
'B-21-21',
'200128',
'210105',
'200154',
'200129',
'210142',
'200131',
'190114',
'210171',
'210131',
'210126',
'200130',
'210155',
'210130',
'20739501BTB',
'20755001BTB',
'20752201BTB',
'20759401BTB',
'210136',
'200134',
'200139',
'200142',
'210135',
'20703001',
'20723001',
'20753001BTB',
'210140',
'210119',
'SAM3083',
'210319',
'210320',
'210321',
'210322',
'210323',
'210324',
'210328'

    
    
    
    
    )


ORDER BY `present_model`.`id` DESC "
;

$rs = $db->get($sql);




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


$html = "<table border='1' width='800'>";
$html.= "       <td>Varenr</td>
                <td>NAV navn</td>
                <td>Overskrift</td>
                <td>Lang beskrivelse</td>
                <td>Kort beskrivelse</td>
                <td>Leverandør</td>
                <td>kunhos</td>
                <td>omtanke</td>";

foreach($rs["data"] as $key=>$val ){
    if(in_array($val["model_present_no"],$dub)){

    } else {
        array_push($dub,$val["model_present_no"]);
        if($val["model_present_no"] != "" && strpos($val["model_present_no"],"*") === false && $val["model_present_no"] != "?"  )
        {




            $omtanke = $val["model_name"] == 0 ? "": "sandt";
            $kunhos  = $val["kunhos"] == "" ? "": "sandt";

            $html.= "
            <tr>
                <td>".$val["model_present_no"]."</td>
                <td>".$val["nav_name"]."</td>
                <td>".$val["model_name"] ." - " .$val["model_no"]."</td>
                <td><textarea rows='30' cols='50'>".html(base64_decode($val["long_description"]))."</textarea></td>
                <td><textarea rows='20' cols='50'>".html(base64_decode($val["short_description"]) )."</textarea></td>
                <td>". $val["vendor"]."</td>

                <td>".$kunhos."</td>
                <td>".$omtanke."</td>
            </tr>    
            ";


        }
    }
}
echo $html;
