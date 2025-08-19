<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
            include("sms/db/db.php");



$db = new Dbsqli();
$db->setKeepOpen();




// hent pearent list

$sql = "select * from present where copy_of = 0 and pim_id > 0  ";
$i=0;
$parentList = $db->get($sql);

foreach($parentList["data"] as $parent){
    $parentModel = getParentModel($parent["id"],$db);
    if(sizeof($parentModel) == 0){
        continue;
    }
    $i++;
    foreach($parentModel["data"] as $pmodel){
            $model_id = $pmodel["model_id"];
            $model_present_no  =   $pmodel["model_present_no"];

            if(trim($model_present_no) == ""){
                                    echo $model_id."<br>";
            echo $model_present_no."<br>";
               continue;
            }
      echo $updataSql = "update present_model set model_present_no ='".$model_present_no."' where original_model_id= ".$model_id." and language_id = 1";
        echo "<br>" ;
      $db->set($updataSql);
    }
}
    echo $i;

function getParentModel($pid,$db){
    $sql = "select * from present_model where language_id = 1 and present_id =".$pid;
    return   $db->get($sql);
}

//original_model_id
//model_present_no