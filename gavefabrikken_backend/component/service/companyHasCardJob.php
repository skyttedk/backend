<?php

include_once "../../includes/config.php";

if($_GET["token"] != "sdafj43oy893tyospidfyh349"){
  die("error");
}

set_time_limit ( 3000 );
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("db.php");

$db = new Dbsqli();
$db->setKeepOpen();
       //  [BACKENDURL]]component/service/companyHasCardJob.php
       $sql = "SELECT id FROM `company` where deleted = 0 and active = 1  ";
       $rs = $db->get($sql);
        foreach($rs["data"] as $item){
            $sql = "select count(id) as antal from shop_user where company_id = ".$item["id"]." and blocked = 0";
            $rsCompanyCards = $db->get($sql);
            $sqlUpdate = "update `company` set `hasCard` = ".$rsCompanyCards["data"][0]["antal"]." where `id` = ". $item["id"];
            $rs = $db->set($sqlUpdate);


        }
        echo "fine";
