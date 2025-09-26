<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();



$html = "<table border=1>";
$sql = "select shop_navn from shop_board where active = 1 and fk_shop = ''";
$rs = $db->get($sql);
$total = 0;
$hit = 0;

foreach($rs["data"] as $key=>$val){


$total++;

   $sql1 = "select id,name from shop where name like '%".$val["shop_navn"]."%'";
   $rsShop = $db->get($sql1);

if( sizeofgf($rsShop["data"]) == 1){
   $html.="<tr><td>".$val["shop_navn"]."</td><td>".json_encode($rsShop["data"])."</td></tr>"    ;
}



/*
if( sizeofgf($rsShop["data"]) > 0){
   //print_r($rsShop);
   //echo sizeofgf($rsShop["data"]) ;
    //  echo "-------------------------------- <br><br><br><br><br><br>";
  $hit++;
}
*/
}
$html.= "</table>";





?>

<!DOCTYPE HTML>

<html>

<head>
  <title>Untitled</title>
</head>

<body>
<?php echo $html; ?>
</body>

</html>