<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();



if ($file = fopen("moms.csv", "r")) {
    while(!feof($file)) {
        $line = fgets($file);
        $linearr = explode(";",$line);
         $sql = "insert into moms (varenr,moms,beskrivelse) values('".$linearr[0]."','".$linearr[1]."','".$linearr[2]."' )";
       $db->set($sql);
        # do same stuff with the $line
    }
    fclose($file);
}