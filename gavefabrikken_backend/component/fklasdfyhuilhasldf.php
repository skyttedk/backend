<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");

$db = new Dbsqli();
$db->setKeepOpen();

$recipent = $_POST["recipent"];
$html = $_POST["html"];
/*
$db->set("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','".$recipent."','ORDREBEKRÆFTELSE','" . $html . "' )");
$db->set("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','lone.dahl@plandent.dk','ORDREBEKRÆFTELSE','" . $html . "' )");
$db->set("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','ana@gavefabrikken.dk','ORDREBEKRÆFTELSE','" . $html . "' )");

*/

echo  "1";