<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");

$unsuList = [];


$db = new Dbsqli();
$db->setKeepOpen();
 // SELECT * FROM `mail_queue` WHERE `recipent_email` LIKE 'ADMA@TOPSOE.COM' ORDER BY `mail_queue`.`id` DESC

   $sql = "SELECT * FROM `order` WHERE `shop_id` = 2210 limit 20000";
   $orderList = $db->get($sql);

   foreach($orderList["data"] as $list){
     $list["user_email"];
     $sql = " SELECT id,recipent_email,subject FROM `mail_queue` WHERE `recipent_email` LIKE '".$list["user_email"]."' ORDER BY `mail_queue`.`id` DESC ";
     $mail = $db->get($sql);
//     print_R($mail);

     $subject = $mail["data"][0]["subject"];
     $search = 'Kvitter';
     $is_receipt = false;
     if(preg_match("/{$search}/i", $subject)) {
        $is_receipt = true;
     }
     $search = 'receipt';
     if(preg_match("/{$search}/i", $subject)) {
        $is_receipt = true;
     }
     if($is_receipt == true){

        echo $subject;
        echo "<br>";
        echo $mail["data"][0]["recipent_email"];
        echo "<br>";
        echo $id = $mail["data"][0]["id"];
        echo "<br>";
      //  echo $sqlUpdate = "update `mail_queue` set `sent` = 0,mailserver_id = 4  WHERE  `id` = ".$id;
        $db->Set($sqlUpdate);
        echo "<br><hr><br>";
     } else {
        echo "------------------ej sendt--------------------";
        echo "<br>";
        echo $subject;
        echo "<br>";
        echo $mail["data"][0]["subject"];
        echo "<br>";
        echo $mail["data"][0]["id"];
        echo "<br><hr><br>";
     }


   }

   echo "end";




?>