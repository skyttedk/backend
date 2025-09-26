<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$handle = fopen("xylem.csv", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $lineArr = explode(";",$line);
       // echo $lineArr[2]." ".$lineArr[1]." ".$lineArr[0];
        $navn =  $lineArr[2]." ".$lineArr[1];
        $sql = "SELECT * FROM `user_attribute` WHERE `attribute_value` LIKE '".$lineArr[0]."' AND `shop_id` = 3041";
        $rs = $db->get($sql);

        foreach($rs["data"] as $userAtt){
            $shopUserID =  $userAtt["shopuser_id"];
            //echo $sql2 = "UPDATE `user_attribute` set `attribute_value` = '".$navn."' WHERE `shopuser_id` = ".$shopUserID." AND `is_name` = 1 AND `shop_id` = 3041";
           // echo $sql2 = "UPDATE `order_attribute` set `attribute_value` = '".$navn."' WHERE `shopuser_id` = ".$shopUserID." AND `is_name` = 1 AND `shop_id` = 3041";
           // echo $sql2 = "UPDATE  `order` set `user_name` = '".$navn."' WHERE `shopuser_id` = ".$shopUserID."  AND `shop_id` = 3041";

            echo "<br>";
            $db->set($sql2);
        }


        //       UPDATE `user_attribute` set `attribute_value` = 'Nina Ravelin' WHERE `shopuser_id` = 2132265 AND `is_name` = 1 AND `shop_id` = 2939
    }

    fclose($handle);
} else {
    // error opening the file.
}
echo "end";

?>