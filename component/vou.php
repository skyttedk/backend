<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

/*
$sql = "select shop_navn from shop_board where active = 1 and fk_shop = ''";
$rs = $db->get($sql);
*/
$firmaerFundetid = "";
$firmaerFundetnavn = "";
$firmaerEjFundet = "";
$handle = fopen("vou.txt", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {

     $myvalue = trimgf($line);
     $arr = explode(' ',trimgf($myvalue));
     $arr[0];


     echo   $sql = "SELECT * FROM `company` WHERE name like '%".$arr[0]." ".$arr[1]."%'";
     echo "<br>";
      //  $sql = "SELECT * FROM `company` WHERE name like '".utf8_encode(trimgf($line))."'";
        $rs = $db->get($sql);
    //    print_r($rs["data"]);
        if(sizeofgf($rs["data"]) > 0){
        print_r($rs["data"]);
        $firmaerFundetnavn.= trimgf($line)."<br>"; ;
         foreach($rs["data"] as $element){
            $firmaerFundetid.= $element["id"].",<br>";
          }

        }
        else {
          $firmaerEjFundet.=$line."<br>";
        }
    }

    fclose($handle);
} else {
    // error opening the file.
}
echo $firmaerFundetid;
echo "<hr>";
echo $firmaerFundetnavn;
echo "<hr>";
echo "<hr>";
echo "<hr>";
echo "<hr>";
echo $firmaerEjFundet;

