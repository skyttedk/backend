<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();
       $sql = "SELECT * FROM `system_log` WHERE `created_datetime` > '2019-11-29 00:00:00' AND `error_message` LIKE '23000, 1062, Duplicate entry \'1000000\' for key \'order_no_int\'' ";
      $res =  $db->get($sql);
     // print_r($res);
     $email = [];
      foreach($res["data"] as $item){
         echo extract_emails($item["data"])[0]."<br>";

      }
      //$j = extract_emails($res["data"][0]["data"]);
     //print_r($j);

 function extract_emails($str){
    // This regular expression extracts all emails from a string:
    $regexp = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';
    preg_match_all($regexp, $str, $m);

    return isset($m[0]) ? $m[0] : array();
}

