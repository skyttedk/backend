<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");

$unsuList = [];


$db = new Dbsqli();
$db->setKeepOpen();





class dublet
{

    // simgle gaver kigge på varenr.
    // der eksistere som både pim og lokal

    



}






class AddTlf
{
    private $db;
    function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }
    function getTlfnr($userID){
        $sql = "SELECT `attribute_value`,`shopuser_id`  FROM `user_attribute` WHERE `shopuser_id` = ".$userID." and attribute_id = (
                SELECT `id` FROM `shop_attribute` where id in(SELECT attribute_id FROM `user_attribute` WHERE `shopuser_id` = ".$userID.")
                and shop_attribute.name like('%mobi%') )";
  //      return $this->db->get($sql);
    }
}

