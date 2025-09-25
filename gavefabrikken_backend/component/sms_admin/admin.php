<?php
set_time_limit ( 4000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

include ("service/emailHandle.php");
// https://system.gavefabrikken.dk/gavefabrikken_backend/component/sms_admin/admin.php
// henter list af telefonnr der skal fjernes fra


$handle = new emailHandle;

$handle->setEmailListPath(getcwd()."/service/list.txt");


echo $handle->getUnsubTlf();



?>