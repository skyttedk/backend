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


$fileData = function() {
 //   $file = fopen(__DIR__ . '/til2021.csv', 'r');

    if (!$file)
        die('file does not exist or cannot be opened');

    while (($line = fgets($file)) !== false) {
        yield $line;
    }

    fclose($file);
};

$job = new AddTlf();
$result = $job->getAllMembers();

foreach($result["data"] as $ele){
    $shopuserID = $ele["shopuser_id"];
    $rs = $job->getTlfnr($shopuserID);

    if(isset($rs["data"][0]["attribute_value"])){
        $tlf = $rs["data"][0]["attribute_value"];
        $ID = $rs["data"][0]["shopuser_id"];

        if($ID != $shopuserID){
            echo $ID ."--". $shopuserID."<br>";
            $job->updateTlf($ID,"none");
        } else {
            $job->updateTlf($ID,$tlf);
        }
    }
}
echo "done";

class AddTlf
{
  private $db;
    function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }
    function getAllMembers(){
        $sql = "select * from klubben";
        return $this->db->get($sql);
    }
    function getTlfnr($userID){
        $sql = "SELECT `attribute_value`,`shopuser_id`  FROM `user_attribute` WHERE `shopuser_id` = ".$userID." and attribute_id = (
                SELECT `id` FROM `shop_attribute` where id in(SELECT attribute_id FROM `user_attribute` WHERE `shopuser_id` = ".$userID.")
                and shop_attribute.name like('%mobi%') )";
        return $this->db->get($sql);
    }
    function updateTlf($shopuserID,$tlf)
    {
       $tlf = preg_replace('/\s+/', '', $tlf);
       $sql = "update klubben set telefon = '".$tlf."' where shopuser_id= ".$shopuserID;
       $this->db->set($sql);
    }
    function cleanTlf($tlf){

    }


}



/*
$count = 0;
$not = 0;
$total = 0;
foreach ($fileData() as $line) {
  $pieces = explode(";",$line );
    $email = trimgf($pieces[0]);
    $sql = "SELECT * FROM `user_attribute` WHERE `attribute_value` LIKE '".$email."'";
    $rsShopUser = $db->get($sql);

    if(sizeofgf($rsShopUser["data"]) > 0){
          $count++;
    } else {
        $not++;
       $blackSql = "INSERT INTO `blacklist` ( `email`) VALUES ( '".$email."-".$count."-".$not."')";
       $rsShopUser = $db->set($blackSql);

    }
    $total++;
}
echo $count."<br>";
echo $not."<br>";
echo $total."<br>";


*/
?>

