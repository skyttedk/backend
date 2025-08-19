<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();
$counter = 0;
$counterSetToDeactive = 0;
$handle = fopen("atslette.txt", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
          $counter++;
           $sql = "select id from klubben where active=1 and no_good = 0 and  mail like '%".trimgf($line)."%'";
           $rs = $db->get($sql);
           if(sizeofgf($rs["data"]) > 0){
            echo  $counterSetToDeactive++;
            echo "<br>";

            $sql2 = "update klubben set active = 0 where mail like '%".trimgf($line)."%'";
      //      $db->set($sql2);

           }
    }

    fclose($handle);
} else {
    // error opening the file.
}
echo $counter;
echo "<br>";
echo $counterSetToDeactive;







?>