<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");

$List = [];
$minusList = [0];

$db = new Dbsqli();
$db->setKeepOpen();


$fileData = function() {
        $file = fopen(__DIR__ . '/mails.txt', 'r');

        if (!$file)
                die('file does not exist or cannot be opened');

        while (($line = fgets($file)) !== false) {
                yield $line;
        }
        fclose($file);
};

 $userIDlist = [];
 $userTeleList = [];
  foreach ($fileData() as $line) {
      $sql = "SELECT * FROM `gaveklubben_2022` WHERE `email` LIKE '".$line."'";
      $sql = str_replace(array("\n", "\r"), "", $sql);
      $rs = $db->get($sql);
      if(sizeof($rs["data"]) > 0){

          echo $sql = "UPDATE gaveklubben_2022 SET unsub = CURRENT_TIMESTAMP WHERE id = ".$rs["data"][0]["id"];
        //  $rs = $db->set($sql);
      }

    }















