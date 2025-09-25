<?php
include("db/db.php");
 $db = new Dbsqli();
$handle = fopen("nummer.txt", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {

    $toFirst =  substr($line, 0, 2);
    $toLast = strlen($line)-2;
    $last =  substr($line, 0, $toLast);

    if($toFirst !="19" && $toFirst !="29" && is_numeric($last) ){
      $line = trimgf($line);

      if(strlen($line) == 8){
      $query = "insert into sms_user (tlf,grp_id) values ('".$line."',9)";
      $result = $db->set($query);


      }




    }


    //   echo $line;
    // echo     $query = "insert into sms_unsubscribe_mail (mail) values ('".$line."')";
    //  $result = $db->set($query);

    }

    fclose($handle);
} else {
    // error opening the file.
}





