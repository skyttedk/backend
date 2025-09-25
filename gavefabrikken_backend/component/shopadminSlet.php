<?php
   error_reporting(E_ALL);

include("sms/db/db.php");


$outputArr = [];
$db = new Dbsqli();
$db->setKeepOpen();
if($_POST["action"] == "slet"){
  echo   $sql = "update `shop` set deleted = 1 where id=".$_POST["id"];
$rs = $db->set($sql);
              echo "ok";
}
if($_POST["action"] == "gendan"){
  echo   $sql = "update `shop` set deleted = 0 where id=".$_POST["id"];
$rs = $db->set($sql);
              echo "ok";
}


?>