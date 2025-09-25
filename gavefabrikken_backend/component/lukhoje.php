<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$cardId = 4501;

for($i=4321;$i<4501;$i++){
 //echo  $sql = "SELECT * FROM `shop_user` WHERE  `username` LIKE 'htk".$i."'";
 // echo  $sql = "update `shop_user` set 23500 = 0 WHERE  `username` LIKE 'htk".$i."'";
    $rs = $db->get($sql);
    print_r($rs);
}

