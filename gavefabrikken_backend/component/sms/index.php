<?php
//include("site/index.php");


error_reporting(E_ALL);
ini_set('display_errors', 1);
include("db/db.php");
  if ($file = fopen("db/test2.txt", "r")) {
    while(!feof($file)) {
     $line = fgets($file);
     $userID = getUserId(trim($line));
     $mail = getEmail($userID);
     echo $mail."<br />";
    }
    fclose($file);
}


//getUserId('1076317');
//getEmail('94573');

function getEmail($userId)
{
    $db = new Dbsqli();
    $query = "SELECT attribute_value FROM `user_attribute` WHERE `shopuser_id` = '".$userId."' and is_email = '1'";
    $result = $db->get($query);
    return $result["data"][0]["attribute_value"];
}
function getUserId($login)
{
    $db = new Dbsqli();
    $query = "SELECT shopuser_id FROM `user_attribute` WHERE `attribute_value` = '".$login."'";
    $result = $db->get($query);
    return $result["data"][0]["shopuser_id"];

}


/*
$db = new Dbsqli();

$query = "INSERT INTO `gavefabrikken`.`newsletter_reg` (`email`, `company_name`) VALUES ('us@bitworks.dk', 'bitworks2')";
print_R($db->set($query));

$query = "select * from newsletter_reg";
print_R($db->get($query));
*/

//$query = "select * from newsletter_reg";
//$orm = new Orm();


?>

