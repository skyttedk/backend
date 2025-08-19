<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();



$sql = "SELECT DISTINCT (present_model.model_id),present_model.model_name,present_model.model_no, present_model.model_present_no  FROM `order`
inner join present_model  on present_model.model_id =  `order`.`present_model_id`
WHERE `order`.shop_id in(57,58,59,272,574) and present_model.language_id = 1 and `order`.gift_certificate_end_date = '2020-11-01'" ;



$rs = $db->get($sql);
foreach($rs["data"] as $key=>$val){
    $sql2 = "select moms from moms where varenr = '".$val["model_present_no"]."'";
    $moms = $db->get($sql2);

    if(sizeofgf($moms["data"]) == 0){
      echo "fejl;".$val["model_id"].";". $val["model_present_no"]. ";". utf8_decode($val["model_name"]). ";". utf8_decode($val["model_no"])."<br>" ;
    } else {
        $momsTal = -2;
        echo $moms["data"][0]["moms"];

       if(strpos($moms["data"][0]["moms"],"0")  !== false){
         $momsTal = 0;
       }
       if(strpos($moms["data"][0]["moms"],"15")  !== false){
         $momsTal = 15;
       }
       if(strpos($moms["data"][0]["moms"],"25")  !== false) {
        $momsTal = 25;
       }
       if(strpos(strtolower($moms["data"][0]["moms"]),"fri")  !== false) {
        $momsTal = 0;
       }
        if($moms["data"][0]["moms"] == -1) {
        $momsTal = -1;
       }

       echo $moms["data"][0]["moms"]."<br><br>";
      echo $updateSql = "update present_model set moms = ".$momsTal." where model_id = ".$val["model_id"];
      echo "<br><br>";
     $db->set($updateSql);

    }




}



