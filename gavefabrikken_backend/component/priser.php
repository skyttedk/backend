<?php
if($_GET["token"] != "dsfklasklfdslkal2323!aslfsaal4lkmnhs"){
    die("stop");
}
if(!isset($_GET["lang"])){
    die("stop");
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");

$List = [];


$db = new Dbsqli();
$db->setKeepOpen();
// AND `show_to_saleperson` = 1
 $sql = "
SELECT p.*,
	COUNT(`present_model`.`present_id`) antal,
    present_model.model_present_no

FROM
    `present_model`
INNER JOIN(
    SELECT
        *
    FROM
        `present`
    WHERE
        `copy_of` = 0 AND active = 1 AND deleted = 0 and pim_id > 0 and shop_id = 0

) p
ON
    p.id = present_model.present_id AND `present_model`.language_id = 1 
GROUP by `present_model`.`present_id` HAVING antal = 1 ;
    ";

$presents = $db->get($sql);
$country = $_GET["lang"];





foreach ($presents["data"] as $present){

    $p =  trim($present["model_present_no"]);
    $nav = getNAVdata($p,$db,$country);

    if(sizeof($nav["data"]) > 0){
        if($country == 1){
            updatePrice($db,$nav["data"],$present,false);
        }
        if($country == 4){
            updatePrice($db,$nav["data"],$present,true);
        }
    }


}
echo "done";

function getNAVdata($no,$db,$country){
    $sql = "SELECT * 
FROM navision_item 
WHERE no = '".$no."' 
AND language_id IS NOT NULL 
AND deleted IS NULL and language_id =  ".$country;
    return $db->get($sql);
}
function updatePrice($db, $NavData, $present, $isNO = false) {
    $suffix = $isNO ? "_no" : "";

    $budgetPris = 0;
    $vejl_pris = 0;
    $unit_cost = 0;
    $budgetPrisShow = 'false';
    $vejl_prisShow = 'false';
    $PT_price = 0;
    $PT_budget = 0;

    $NAVbudgetPris = $NavData[0]["unit_price"];
    $NAVvejl_pris = $NavData[0]["vejl_pris"];
    $NAVunit_cost = $NavData[0]["unit_cost"];

    $budgetPris = $present["price_group" . $suffix];
    $vejl_pris = $present["indicative_price" . $suffix];
    $unit_cost = $present["price" . $suffix];

    $budgetPris = $budgetPris == "" ? 0 : $budgetPris;
    $vejl_pris = $vejl_pris == "" ? 0 : $vejl_pris;
    $unit_cost = $unit_cost == "" ? 0 : $unit_cost;

    if ($NAVbudgetPris != 0) {
        $budgetPris = $NAVbudgetPris;
    }
    if ($NAVvejl_pris != 0) {
        $vejl_pris = $NAVvejl_pris;
    }
    if ($NAVunit_cost != 0) {
        $unit_cost = $NAVunit_cost;
    }
    if ($budgetPris != 0) {
        $budgetPrisShow = 'true';
    }
    if ($vejl_pris != 0) {
        $vejl_prisShow = 'true';
    }
    $PT_price = $vejl_pris;
    $PT_budget = $budgetPris;
    $pt_price = $present["pt_price" . $suffix];

    if ($pt_price != "") {
        $array = json_decode($pt_price, true);
        if (!empty($array["pris"]) && !is_numeric($array["pris"])) {

            echo $PT_price = $array["pris"];
        }
        if (!empty($array["budget"]) && !is_numeric($array["budget"])) {

            echo $PT_budget = $array["budget"];
        }
    }
    if (is_numeric($PT_price)) {
        $PT_price = floor($PT_price);
    }
    if (is_numeric($PT_budget)) {
        $PT_budget = floor($PT_budget);
    }
    if ($PT_budget != 0) {
        $budgetPrisShow = 'true';
    }
    if ($PT_price != 0) {
        $vejl_prisShow = 'true';
    }


    $presentation = '{"pris":"' . $PT_budget . '","vis_pris":"' . $budgetPrisShow . '","budget":"' . $PT_price . '","vis_budget":"' . $vejl_prisShow . '","special":"","vis_special":"false"}';
    $sql = "update present set price_group" . $suffix . " = " . floor($budgetPris) . ",indicative_price" . $suffix . " = " . floor($vejl_pris) . ", price" . $suffix . "= " . $unit_cost . ", prisents_nav_price" . $suffix . "= " . $unit_cost . " ,pt_price" . $suffix . "= '" . $presentation . "' where id= " . $present["id"];
    $rs = $db->set($sql);

}

function updatePriceNO($db,$NavData,$present)
{
    //  echo $present["id"];
    // print_R($NavData);

    $budgetPris = 0;
    $vejl_pris = 0;
    $unit_cost = 0;
    $budgetPrisShow = 'false';
    $vejl_prisShow = 'false';
    $PT_price = 0;
    $PT_budget = 0;


    $NAVbudgetPris = $NavData[0]["unit_price"];
    $NAVvejl_pris = $NavData[0]["vejl_pris"];
    $NAVunit_cost = $NavData[0]["unit_cost"];

    $budgetPris = $present["price_group_no"];
    $vejl_pris = $present["indicative_price_no"];
    $unit_cost = $present["price_no"];

    $budgetPris = $budgetPris == "" ? 0 : $budgetPris;
    $vejl_pris = $vejl_pris == "" ? 0 : $vejl_pris;
    $unit_cost = $unit_cost == "" ? 0 : $unit_cost;

    if ($NAVbudgetPris != 0) {
        $budgetPris = $NAVbudgetPris;
    }
    if ($NAVvejl_pris != 0) {
        $vejl_pris = $NAVvejl_pris;
    }
    if ($NAVunit_cost != 0) {
        $unit_cost = $NAVunit_cost;
    }
    if ($budgetPris != 0) {
        $budgetPrisShow = 'true';
    }
    if ($vejl_pris != 0) {
        $vejl_prisShow = 'true';
    }
    $PT_price = $vejl_pris;
    $PT_budget = $budgetPris;
    $pt_price = $present["pt_price_no"];

    if ($pt_price != "") {
        $array = json_decode($pt_price, true);
        if (!empty($array["pris"]) && !is_numeric($array["pris"])) {
            echo "***************<br>";
            echo $PT_price = $array["pris"];
        }
        if (!empty($array["budget"]) && !is_numeric($array["budget"])) {
            echo "*************** <br>";
            echo $PT_budget = $array["budget"];
        }
    }
    if (is_numeric($PT_price)) {
        $PT_price = floor($PT_price);
    }
    if (is_numeric($PT_budget)) {
        $PT_budget = floor($PT_budget);
    }
    if ($PT_budget != 0) {
        $budgetPrisShow = 'true';
    }
    if ($PT_price != 0) {
        $vejl_prisShow = 'true';
    }
    echo "<br>";

// {"pris":"640","vis_pris":"true","budget":"1220","vis_budget":"true","special":"","vis_special":"false"}
    $presentation = '{"pris":"' . $PT_budget . '","vis_pris":"' . $budgetPrisShow . '","budget":"' . $PT_price . '","vis_budget":"' . $vejl_prisShow . '","special":"","vis_special":"false"}';
    // echo  $sql = "update present_test set price_group = ".$budgetPris.",indicative_price = ".$vejl_pris.", price= ".$unit_cost.", prisents_nav_price= ".$unit_cost."    ,pt_price= '".$presentation."' where id= ".$present["id"];

    echo $sql = "update present set price_group_no = " . floor($budgetPris) . ",indicative_price_no = " . floor($vejl_pris) . ", price_no= " . $unit_cost . ", prisents_nav_price_no= " . $unit_cost . "    ,pt_price_no= '" . $presentation . "' where id= " . $present["id"];
    echo "<br>";
    //  $rs =  $db->set($sql);
    // print_R($rs);
}



function updatePriceDK($db,$NavData,$present){
  //  echo $present["id"];
   // print_R($NavData);

    $budgetPris = 0;
    $vejl_pris = 0;
    $unit_cost = 0;
    $budgetPrisShow = 'false';
    $vejl_prisShow = 'false';
    $PT_price =0;
    $PT_budget =0;


    $NAVbudgetPris = $NavData[0]["unit_price"];
    $NAVvejl_pris = $NavData[0]["vejl_pris"];
    $NAVunit_cost = $NavData[0]["unit_cost"];

    $budgetPris = $present["price_group"];
    $vejl_pris = $present["indicative_price"];
    $unit_cost = $present["price"];

    $budgetPris = $budgetPris == "" ? 0:$budgetPris;
    $vejl_pris = $vejl_pris == "" ? 0:$vejl_pris;
    $unit_cost = $unit_cost == "" ? 0:$unit_cost;

    if($NAVbudgetPris != 0){
        $budgetPris =  $NAVbudgetPris;
    }
    if($NAVvejl_pris != 0){
        $vejl_pris =  $NAVvejl_pris;
    }
    if($NAVunit_cost != 0){
        $unit_cost =  $NAVunit_cost;
    }
    if($budgetPris != 0){
        $budgetPrisShow = 'true';
    }
    if($vejl_pris != 0){
        $vejl_prisShow = 'true';
    }
    $PT_price = $vejl_pris;
    $PT_budget = $budgetPris;
    $pt_price = $present["pt_price"];

    if($pt_price != ""){
        $array = json_decode($pt_price, true);
        if(!empty($array["pris"]) && !is_numeric($array["pris"])){
            echo "***************<br>";
           echo  $PT_price = $array["pris"];
        }
        if(!empty($array["budget"]) && !is_numeric($array["budget"])){
            echo "*************** <br>";
           echo $PT_budget = $array["budget"];
        }
    }
    if(is_numeric($PT_price)){
        $PT_price = floor($PT_price);
    }
    if(is_numeric($PT_budget)){
        $PT_budget = floor($PT_budget);
    }
    if($PT_budget != 0){
        $budgetPrisShow = 'true';
    }
    if($PT_price != 0){
        $vejl_prisShow = 'true';
    }
    echo "<br>";

// {"pris":"640","vis_pris":"true","budget":"1220","vis_budget":"true","special":"","vis_special":"false"}
     $presentation = '{"pris":"'.$PT_budget.'","vis_pris":"'.$budgetPrisShow.'","budget":"'.$PT_price.'","vis_budget":"'.$vejl_prisShow.'","special":"","vis_special":"false"}';
 // echo  $sql = "update present_test set price_group = ".$budgetPris.",indicative_price = ".$vejl_pris.", price= ".$unit_cost.", prisents_nav_price= ".$unit_cost."    ,pt_price= '".$presentation."' where id= ".$present["id"];

  echo  $sql = "update present set price_group = ".floor($budgetPris).",indicative_price = ".floor($vejl_pris).", price= ".$unit_cost.", prisents_nav_price= ".$unit_cost."    ,pt_price= '".$presentation."' where id= ".$present["id"];
echo "<br>";
 //  $rs =  $db->set($sql);
   // print_R($rs);





}





















