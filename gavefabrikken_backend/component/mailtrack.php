<?php


ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit', '2048M');
error_reporting(E_ALL);

include("sms/db/db.php");

$token= $_POST["id"];
if (!is_int((int)$token)) {
    die("Stop");
}
$companyID = 100;
$db = new Dbsqli();
$db->setKeepOpen();
$sql = "INSERT INTO mail_track (token, company_id) VALUES ('$token',$companyID )";
$rs = $db->set($sql);

?>