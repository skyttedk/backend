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
//https://gavefabrikken.dk//gavefabrikken_backend/component/readlog.php?token=fsd4io3py875t908ughhoidsdlfgj2093tuyg0thjio


$sql ="SELECT * FROM `system_log` WHERE `id` BETWEEN ".$_GET['start']." and ".$_GET['end'];

$rs = $db->get($sql);
echo json_encode($rs);
























