<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include("db.php");
include("db_2018.php");



$db         = new Dbsqli();
$db_2017    = new Dbsqli_2017();

//
$db_2017 = new Dbsqli_2017();
$query = "SELECT * from company where is_gift_certificate = 1 and active = 1 and deleted = 0 and name NOT LIKE '%test%'  ";
$result = $db_2017->get($query);




$workingList = $result["data"][0];
foreach($result["data"] as $data ){

$Cvrsql = "select id from company where cvr like '%".$data["cvr"]."%'";

    $cvrResult =  $db->get($Cvrsql);
    if( countgf($cvrResult["data"] ) > 0 ){
      echo "er der ".$data["cvr"];
    } else {
        unset($data['id']);
        unset($data['rapport_note']);
        unset($data['internal_note']);
        $data["import_2017"] = "1";
        $sql = sprintf(
            'INSERT INTO company (%s) VALUES ("%s")',
            implode(',',array_keys($data)),
            implode('","',array_values($data))
        );
        $result = $db->set($sql);

    }

}

    //print_R($result);
echo  "end";

?>