<?php
//ini_set('max_execution_time', 300); //300 seconds = 5 minutes
//ini_set('memory_limit','2048M');


include("sms/db/db.php");
echo "unsu";
$unsuList = [];


$db = new Dbsqli();
$db->setKeepOpen();

$sql = "select * from sms_unsubscribe ";
$rs = $db->get($sql);


$i = 0;
foreach($rs["data"] as $key=>$val){
    if(strlen($val["tlf"]) > 6){
     $sqlTjeck = "select tlf from sms_user where tlf like '%".$val["tlf"]."%'";
        $tjeckRs = $db->get($sqlTjeck);
        if(sizeofgf($tjeckRs["data"]) >0){
            echo $updata = "update sms_user set active = 0 where tlf like '%".$val["tlf"]."%'";
        $db->set($updata);
        $i++;
    }
}
}

echo $i;





?>