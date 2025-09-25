<?php
include("sms/db/db.php");

$List = [];


$db = new Dbsqli();
$db->setKeepOpen();

// $db->get("")

// update `present_2505` set show_to_saleperson_no = 1 WHERE `id` in( SELECT DISTINCT `present_id` FROM `present_model` WHERE `model_present_no` ) and `copy_of` = 0 and `deleted` = 0


$sql = "SELECT `no`,`description`,`unit_price` as budget,`unit_cost`,`vejl_pris`  FROM `navision_item` WHERE `language_id` = 4 and deleted IS NULL limit 50";
$res = $db->get($sql);
//print_R($res);
$updateID = [];
foreach ($res["data"] as $item){


    $sql = "SELECT DISTINCT `present_id` FROM `present_model` WHERE `model_present_no` like '".$item["no"]."'";
    $presentRs = $db->get($sql);
    foreach ($presentRs["data"] as $p){
        $updateID[] = $p["present_id"];
    }

    //print_R($present);  array_unique
}
$updateID =  array_unique($updateID);
foreach ($updateID as $ID){
 echo   $updateSql =  "update `present` set show_to_saleperson_no = 1 WHERE `id` = ".$ID." and `copy_of` = 0 and `deleted` = 0";
 echo "<br>";
    $db->set($updateSql);
}