<?php
include("db/db.php");
 $db = new Dbsqli();
$handle = fopen("mail.txt", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
     //   echo $line;
       echo     $query = "insert into sms_unsubscribe_mail (mail) values ('".$line."')";

     $result = $db->set($query);

    }

    fclose($handle);
} else {
    // error opening the file.
}






