<?php


set_time_limit ( 4000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();


$sql =" select attribute_value,user_attribute.shopuser_id, `order`.`order_timestamp` from user_attribute

inner join `order` on user_attribute.shopuser_id = `order`.`shopuser_id`

where is_email = 1 and user_attribute.shopuser_id in (
SELECT shopuser_id FROM `user_attribute`
inner join shop_attribute on
user_attribute.attribute_id = shop_attribute.id
WHERE `attribute_value` LIKE 'ja' and user_attribute.shop_id not in (57,
58,
59,
263,
272,
566,
574,
577,
889,
891,
892,
893,
895,
910,
940,
947,
952,
969,
970,
971,
972,
978,
979,
982,
987,
1011,
1025,
1032,
1046,
1057,
1175,
953,
1151,
1155,
1154

) and shop_attribute.name =  'Gaveklubben tilmelding')" ;

$rs = $db->get($sql);

foreach($rs["data"] as $item){

$sql = "insert into gaveklubben ( shopuser_id, email,subscribe_date, season ) values (".$item["shopuser_id"].",'".$item["attribute_value"]."','".$item["order_timestamp"]."','2019')";
 $rs = $db->set($sql);
}






