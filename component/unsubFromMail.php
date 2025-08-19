<?php


include("sms/db/db.php");
echo "unsu";
$unsuList = [];


$db = new Dbsqli();
$db->setKeepOpen();

$sql = "select * from sms_unsubscribe ";
$rs = $db->get($sql);



select * from user_attribute where shopuser_id in (
	select shopuser_id from user_attribute where   shopuser_id in (
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
953
) and shop_attribute.name =  "Gaveklubben tilmelding" ) ) and  user_attribute.is_email = 0 and user_attribute.is_username = 0 and user_attribute.is_password = 0 and  user_attribute.is_name = 0
