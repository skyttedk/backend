<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();



die("asdfasdf");

$presents = $db->get("SELECT `model_present_no`,`model_id`,language_id FROM `present_model` WHERE `language_id` = 1 ");

foreach ($presents["data"] as $item){


}
echo "dane";
