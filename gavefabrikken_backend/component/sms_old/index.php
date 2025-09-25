<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("db/orm.php");

/*
$db = new Dbsqli();

$query = "INSERT INTO `gavefabrikken`.`newsletter_reg` (`email`, `company_name`) VALUES ('us@bitworks.dk', 'bitworks2')";
print_R($db->set($query));

$query = "select * from newsletter_reg";
print_R($db->get($query));
*/

$query = "select * from newsletter_reg";
$orm = new Orm();
$orm->read($query);


?>
