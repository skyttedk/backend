<?php
set_time_limit ( 4000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$token = "fsd4io3py875t908ughhoidsdlfgj2093tuyg0thjio";
if(!$_GET["token"]){
  die("Du har ikke adgang token mangler");
}
if($_GET["token"] != "fsd4io3py875t908ughhoidsdlfgj2093tuyg0thjio")
{
  die("Du har ikke adgang");
}
if(!$_GET["deadline"]){
    die("Du mangler deadline");
}
if(!$_GET["type"]){
    die("Du mangler type");
}
$type = $_GET["type"];
$deadline = $_GET["deadline"];

// [BACKENDURL]/component/fak_rapport.php?token=fsd4io3py875t908ughhoidsdlfgj2093tuyg0thjio&type=faktura&deadline=2020-11-01


$sql ="SELECT

shop_user.shop_id,
shop.name,
shop_user.expire_date,
`order`.present_model_id,
`order`.present_model_name,

COUNT(shop_user.username) as antal
FROM `shop_user`
inner JOIN company_order on company_order.id = shop_user.company_order_id
left join `order` on shop_user.username = `order`.user_username
inner join shop on shop_user.shop_id = shop.id




WHERE
company_order.is_cancelled = 0 and
shop_user.is_giftcertificate = 1 and
shop_user.`expire_date` = '".$deadline."'  and
shop_user.`is_demo` = 0 and
shop_user.`blocked` = 0 and

shop_user.shop_id in (52,53,54,55,56,290,310,569,287,575)


GROUP BY `order`.present_model_id
order by shop_user.shop_id

" ;
//shop_user.shop_id in (52,53,54,55,56,290,310,569,287,575)

$rs = $db->get($sql);
$sumTotal = [];
$sumEjvalgt = [];
$sum = 0;

foreach($rs["data"] as $key=>$val){
    if(!$sumTotal[$rs["data"][$key]["order_no"]]) $sumTotal[$rs["data"][$key]["order_no"]];
    if($val["present_model_id"] == "" ){
       $rs["data"][$key]["present_model_id"] = "0";
       $rs["data"][$key]["model_name"] = "gave�ske";
       $rs["data"][$key]["model_no"] = "" ;
       $rs["data"][$key]["model_present_no"] = getGiftboxNr($rs["data"][$key]["shop_id"]);
       $sumEjvalgt[$rs["data"][$key]["order_no"]] =  $rs["data"][$key]["antal"];
       $sumTotal[$rs["data"][$key]["order_no"]]+= $rs["data"][$key]["antal"];
       $sum+= $rs["data"][$key]["antal"];

    } else {
     $sql2 = "select present_model.model_name,present_model.model_no, present_model.model_present_no from present_model where language_id = 1 and model_id=". $rs["data"][$key]["present_model_id"];
       $rs2 = $db->get($sql2);
       $rs["data"][$key]["model_name"] = utf8_decode($rs2["data"][0]["model_name"]);
       $rs["data"][$key]["model_no"] = utf8_decode($rs2["data"][0]["model_no"]);
       $rs["data"][$key]["model_present_no"] = $rs2["data"][0]["model_present_no"];
       $sumTotal[$rs["data"][$key]["order_no"]]+= $rs["data"][$key]["antal"];
       $sum+= $rs["data"][$key]["antal"];
    }
}

/*
 [order_no] => BS26067
            [shop_id] => 52
            [name] => Julegavekortet DK
            [expire_date] => 2020-11-01
            [present_model_id] => 7550
            [antal] => 1
            [model_name] => GJD weekend taske
            [model_no] => Gr�, 54 cm
            [model_present_no] => 190111
*/


//echo $sum;
//echo "<br><br>";
//print_r($rs["data"]);
//print_r($sumTotal);
//print_r($sumEjvalgt);

$csv = "BS-nummer;Shop id;Shop navn;Deadline;Model id;Gave navn;Model navn;Varenr;Antal valgte"."\n";
//print_r($rs["data"] );
foreach($rs["data"]  as $key=>$val)
{
    $diff = getOrderQuantity($val["order_no"],$db) -  $sumTotal[$val["order_no"]];
    //$diff = 0;
    $model_no = str_replace(";", " - ",$val["model_no"]);
    $csv.=
        $val["order_no"].";".
        $val["shop_id"].";".
        $val["name"].";".
        $val["expire_date"].";".
        $val["present_model_id"].";".
        $val["model_name"].";".
        $model_no.";".
        $val["model_present_no"].";".
        $val["antal"]."\n";
}


//echo "<br><br><br><br><br><br>--------------------------------------------------<br><br><br><br><br><br>";
if($type=="sumliste"){
$csv2 = "BS-nummer;Antal kort;Antal kort i order;Diff;Antal lukket kort;Flyttet kort"."\n";
foreach($sumTotal  as $key=>$val) {
    $orderSize = getOrderQuantity($key,$db);
    $blockedCard = 0;
    $diff = ($val*1) - ($orderSize*1); // hvis minus er der slettet eller flyttet kort fra den oprindelige ordre
    if($diff !=0){
       $blockedCard = getBlockCard($key,$db);
    }
    $moved =   $diff + $blockedCard;
    $csv2.= $key.";".$val.";".$orderSize.";".$diff.";".$blockedCard.";".$moved."\n";

}
}
if($type=="sumliste"){
 $filename = "sumliste_".$deadline.".csv";
  header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'');
        $output = fopen('php://output', 'w');
        fwrite($output,utf8_decode($csv2));
}
if($type== "faktura"){
    $filename = "faktura_".$deadline.".csv";
    header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'');
        $output = fopen('php://output', 'w');
        fwrite($output,$csv);
}




function getOrderQuantity($order_no,$db)
{
  $sql = "SELECT `quantity` FROM `company_order` WHERE `order_no` = '".$order_no."'";
  $rs = $db->get($sql);
  return $rs["data"][0]["quantity"];
}
function getBlockCard($order_no,$db){
  $sql = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `company_order_id` = (SELECT id from company_order where `order_no`= '".$order_no."') and blocked = 1";
  $rs = $db->get($sql);

  return $rs["data"][0]["antal"];

}
function getGiftboxNr($shopId){
//52,53,54,55,56,290,310,569,287,575

if($shopId == "52"){
    return "57904-1";
}
if($shopId == "53"){
    return "57908-1" ;
}
if($shopId == "54"){
    return "57905-1";
}
if($shopId == "55"){
    return "57904-1";
}
if($shopId == "56"){
    return "57903-1";
}
if($shopId == "290"){
    return  "57907";
}
if($shopId == "310"){
    return  "57906-1";
}
if($shopId == "575"){
    return  "1525FG";
}

}




























