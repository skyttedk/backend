<?php

// https://system.gavefabrikken.dk/gavefabrikken_backend/component/gensende_kvittering_pr_shop.php?token=dfsalkfj498y9hskdfh7488ifhus
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if($_GET["token"] != "dfsalkfj498y9hskdfh7488ifhus"){
   // die("Ingen adgang");
}
echo "gensend";

include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$shopID = 7376;

$sql = "SELECT `shop_user`.id,`order`. id as order_id  FROM `shop_user` 
         inner join `order` on `order`.shopuser_id = shop_user.id 
         WHERE `order`.`shop_id` = ".$shopID." AND `blocked` = 0 AND `shutdown` = 0
            
         ";
$rs =  $db->get($sql);

print_R($rs);






