<?php
set_time_limit ( 4000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$email = [];

$sql = "SELECT tlf FROM `sms_unsubscribe` limit 100";

$rs = $db->get($sql);

foreach($rs["data"] as $ele){
    if(strlen($ele["tlf"]) > 7){
    $sql2 = "SELECT `attribute_value` from `user_attribute_1212` WHERE `shopuser_id` in
    ( SELECT `shopuser_id` FROM `user_attribute_1212` where `attribute_value` like('%".$ele["tlf"]."%') and `is_username` = 0 and `is_password` = 0 and `is_email` = 0 and `is_name` = 0)
    and `is_email` = 1";
    $rs2 = $db->get($sql2);
        foreach($rs2["data"] as $item){
          if($item["attribute_value"]  != ""){
              array_push($email,$item["attribute_value"] );
          }

        }
    }


}
print_r($email);