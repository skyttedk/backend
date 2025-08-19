<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");

$List = [];


$db = new Dbsqli();
$db->setKeepOpen();

$sql = "SELECT `sales_person`,rr.antal_user as antall , company.id FROM `company` 

INNER JOIN
(
SELECT COUNT(shop_user.id) AS antal_user, shops.company_id FROM shop_user INNER JOIN( SELECT `shop_id`, company_id, COUNT(id) AS antal FROM `order` WHERE `shop_is_gift_certificate` = 0 AND `shop_is_company` = 1 GROUP BY company_id HAVING antal > 20 ) shops ON shop_user.shop_id = shops.shop_id WHERE shop_user.is_demo = 0 AND shop_user.blocked = 0 AND shop_user.shutdown = 0 GROUP BY shops.company_id

)rr on rr.company_id  = `company`.id";

$rs = $db->get($sql);

foreach ($rs["data"] as $item){
    if(isset($List[$item["sales_person"]]) == false){
        $List[$item["sales_person"]] = $item["antall"]*1;
    } else {
        $List[$item["sales_person"]]+= $item["antall"]*1;
    }


}

$total = 0;
foreach ($List as $key=>$val){
    echo $key.";".$val."<br>";
}
echo $total;