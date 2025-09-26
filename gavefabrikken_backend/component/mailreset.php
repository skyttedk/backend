<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');


include("sms/db/db.php");

  $i = 0;
 $outputArr = [];
$db = new Dbsqli();
$db->setKeepOpen();

$sql = "SELECT attribute_value FROM `user_attribute` WHERE `shop_id` = 1854 AND `is_email` = 1 and attribute_value != ''   ";
$rs = $db->get($sql);

foreach ($rs["data"] as $item){



    $sql2 = "SELECT id FROM `mail_queue` WHERE `recipent_email` = '".$item["attribute_value"]."'  and subject = 'Kvittering for gavevalg' ORDER BY `mail_queue`.`id`  DESC";
    $rs2 = $db->get($sql2);
 //


       if(sizeofgf($rs2["data"]) < 0){
            $sqlupdate = "update mail_queue set sent = 0,send_group = '1854_pandora', subject = 'Genudsendelse af kvittering for gavevalg'  where id=".$rs2["data"][0]["id"];
            $db->set($sqlupdate);
            $i++;
       } else {
             $sql2 = "SELECT id FROM `mail_queue` WHERE `recipent_email` = '".$item["attribute_value"]."'  and subject = 'Gift receipt' ORDER BY `mail_queue`.`id`  DESC";
            $rs2 = $db->get($sql2);
             $sqlupdate = "update mail_queue set sent = 0,send_group = '1854_panend', subject = 'Gift receipt'  where id=".$rs2["data"][0]["id"];
            $db->set($sqlupdate);
            $i++;


       }








}
echo $i;
//print_r($rs);








?>