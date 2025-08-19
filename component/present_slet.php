<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();
$sql = "  SELECT p.id 
    FROM `present` p
    LEFT JOIN (
        SELECT DISTINCT `copy_of` 
        FROM `present` 
        WHERE `id` IN (SELECT `present_id` FROM `order`) 
          AND `copy_of` != 0
    ) AS subq ON p.id = subq.copy_of
    WHERE subq.copy_of IS NULL
      AND p.copy_of = 0
      AND p.`created_datetime` < '2023-12-20 11:27:10'
      AND p.`modified_datetime` < '2023-12-20 11:27:10'
      AND p.`active` = 1
      AND p.`deleted` = 0";
$res =  $db->get($sql);
//print_r($res);
foreach ($res["data"] as $item){

    echo $sqlUpdate = "UPDATE `present` SET deleted = 0, active = 0,nav_name_en = 1 where id = ".$item["id"] ;
    echo "<br>";
    $db->set($sqlUpdate);
}