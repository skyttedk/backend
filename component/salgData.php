<?php

echo "hej";
$data = [];
include("sms/db/db_2016.php");

 $db_2016 = new Dbsqli_2016();
$db_2016->setKeepOpen();

// udtræk 2016
$sql = "SELECT * FROM `company` WHERE id in ( SELECT DISTINCT `company_id`  FROM `shop_user` WHERE shop_id in (52,53,54,55,56,287,290,310, 265)) order by cvr limit 20 ";
$db_2016 = $db_2016->get($sql);

//print_R($db_2016);


foreach($db_2016["data"] as $key=>$val){
$data[$val["cvr"]][] = $val;


}
print_r($data);







?>