<?php
if($_GET["token"] != "sdlfhiekhlsk23232948yruifkhkwsjlzdsfae23f2hd" ){
  die("Ingen adgang");
}

include("../model/dbsqli.class.php");
 $totalHtml = "";



//  julegavekort
$sql = "SELECT count(present_id) as antal,present_id, present_name, `present_model_name` FROM `order` where `shop_id` = 52 group by present_id,present_name,present_model_name  ";
$db = new Dbsqli;
$rs = $db->getSql2($sql);
$total = 0;
$jgk =  $html = "<table border=1 width=700>";
$jgk.=  "<tr><th>Id</th><th>Antal</th><th>Gave</th><th>Model</th></tr>";

 foreach ($rs as $item){
   $total+= $item["antal"];
    $jgk.= "<tr><td>".$item["present_id"]."</td><td>".$item["antal"]."</td><td>".$item["present_name"]."</td><td>".str_replace("###"," - ",$item["present_model_name"])."</td></tr>";
 }
$jgk.= "<tr><td></td><td>".$total."</td><td>TOTAL</td><td></td></tr>";
$jgk.= "</table>";

//  24gaver - 400
$sql = "SELECT count(present_id) as antal,present_id, present_name, `present_model_name` FROM `order` where `shop_id` = 54 group by present_name,present_model_name  ";
$db = new Dbsqli;
$rs = $db->getSql2($sql);
$total = 0;
$gave400 =  $html = "<table border=1 width=700>";
$gave400.=  "<tr><th>Id</th><th>Antal</th><th>Gave</th><th>Model</th></tr>";

 foreach ($rs as $item){
   $total+= $item["antal"];
    $gave400.= "<tr><td>".$item["present_id"]."</td><td>".$item["antal"]."</td><td>".$item["present_name"]."</td><td>".str_replace("###"," - ",$item["present_model_name"])."</td></tr>";
 }
$gave400.= "<tr><td></td><td>".$total."</td><td>TOTAL</td><td></td></tr>";
$gave400.= "</table>";


//  24gaver - 560
$sql = "SELECT count(present_id) as antal,present_id, present_name, `present_model_name` FROM `order` where `shop_id` = 55 group by present_name,present_model_name  ";
$db = new Dbsqli;
$rs = $db->getSql2($sql);
$total = 0;
$gave560 =  $html = "<table border=1 width=700>";
$gave560.=  "<tr><th>Id</th><th>Antal</th><th>Gave</th><th>Model</th></tr>";

 foreach ($rs as $item){
   $total+= $item["antal"];
    $gave560.= "<tr><td>".$item["present_id"]."</td><td>".$item["antal"]."</td><td>".$item["present_name"]."</td><td>".str_replace("###"," - ",$item["present_model_name"])."</td></tr>";
 }
$gave560.= "<tr><td></td><td>".$total."</td><td>TOTAL</td><td></td></tr>";
$gave560.= "</table>";


//  24gaver - 640
$sql = "SELECT count(present_id) as antal,present_id, present_name, `present_model_name` FROM `order` where `shop_id` = 56 group by present_name,present_model_name  ";
$db = new Dbsqli;
$rs = $db->getSql2($sql);
$total = 0;
$gave640 =  $html = "<table border=1 width=700>";
$gave640.=  "<tr><th>Id</th><th>Antal</th><th>Gave</th><th>Model</th></tr>";

 foreach ($rs as $item){
   $total+= $item["antal"];
    $gave640.= "<tr><td>".$item["present_id"]."</td><td>".$item["antal"]."</td><td>".$item["present_name"]."</td><td>".str_replace("###"," - ",$item["present_model_name"])."</td></tr>";
 }
$gave640.= "<tr><td></td><td>".$total."</td><td>TOTAL</td><td></td></tr>";
$gave640.= "</table>";

// guld
$sql = "SELECT count(present_id) as antal,present_id, present_name, `present_model_name` FROM `order` where `shop_id` = 53 group by present_name,present_model_name  ";
$db = new Dbsqli;
$rs = $db->getSql2($sql);
$total = 0;
$guld =  $html = "<table border=1 width=700>";
$guld.=  "<tr><th>Id</th><th>Antal</th><th>Gave</th><th>Model</th></tr>";

 foreach ($rs as $item){
   $total+= $item["antal"];
    $guld.= "<tr><td>".$item["present_id"]."</td><td>".$item["antal"]."</td><td>".$item["present_name"]."</td><td>".str_replace("###"," - ",$item["present_model_name"])."</td></tr>";
 }
$guld.= "<tr><td></td><td>".$total."</td><td>TOTAL</td><td></td></tr>";
$guld.= "</table>";


// jgkNo 400
$sql = "SELECT count(present_id) as antal,present_id, present_name, `present_model_name` FROM `order` where `shop_id` = 57 group by present_name,present_model_name  ";
$db = new Dbsqli;
$rs = $db->getSql2($sql);
$total = 0;
$gave400no =  $html = "<table border=1 width=700>";
$gave400no.=  "<tr><th>Id</th><th>Antal</th><th>Gave</th><th>Model</th></tr>";

 foreach ($rs as $item){
   $total+= $item["antal"];
    $gave400no.= "<tr><td>".$item["present_id"]."</td><td>".$item["antal"]."</td><td>".$item["present_name"]."</td><td>".str_replace("###"," - ",$item["present_model_name"])."</td></tr>";
 }
$gave400no.= "<tr><td></td><td>".$total."</td><td>TOTAL</td><td></td></tr>";
$gave400no.= "</table>";

// jgkNo 600
$sql = "SELECT count(present_id) as antal,present_id, present_name, `present_model_name` FROM `order` where `shop_id` = 58 group by present_name,present_model_name  ";
$db = new Dbsqli;
$rs = $db->getSql2($sql);
$total = 0;
$gave600no =  $html = "<table border=1 width=700>";
$gave600no.=  "<tr><th>Id</th><th>Antal</th><th>Gave</th><th>Model</th></tr>";

 foreach ($rs as $item){
   $total+= $item["antal"];
    $gave600no.= "<tr><td>".$item["present_id"]."</td><td>".$item["antal"]."</td><td>".$item["present_name"]."</td><td>".str_replace("###"," - ",$item["present_model_name"])."</td></tr>";
 }
$gave600no.= "<tr><td></td><td>".$total."</td><td>TOTAL</td><td></td></tr>";
$gave600no.= "</table>";

// jgkNo 800
$sql = "SELECT count(present_id) as antal,present_id, present_name,`present_model_name` FROM `order` where `shop_id` = 59 group by present_name,present_model_name  ";
$db = new Dbsqli;
$rs = $db->getSql2($sql);
$total = 0;
$gave800no =  $html = "<table border=1 width=700>";
$gave800no.=  "<tr><th>Id</th><th>Antal</th><th>Gave</th><th>Model</th></tr>";

 foreach ($rs as $item){
   $total+= $item["antal"];
    $gave800no.= "<tr><td>".$item["present_id"]."</td><td>".$item["antal"]."</td><td>".$item["present_name"]."</td><td>".str_replace("###"," - ",$item["present_model_name"])."</td></tr>";
 }
$gave800no.= "<tr><td></td><td>".$total."</td><td>TOTAL</td><td></td></tr>";
$gave800no.= "</table>";

//-------------------------------   kort salg -------------------------------------
//------ jgk -----
$sql = "SELECT sum(quantity) as antal, `shop_name`,`expire_date` FROM `company_order` WHERE `is_cancelled` = 0 and `shop_id` = 52 GROUP by `expire_date` ";
$db = new Dbsqli;
$rs_jgk = $db->getSql2($sql);
$totalHtml.= salg($rs_jgk, "Julegavekortet");
//---- 24gaver ------
$sql = "SELECT sum(quantity) as antal, `shop_name`,`expire_date` FROM `company_order` WHERE `is_cancelled` = 0 and `shop_id` = 54 GROUP by `expire_date` ";
$db = new Dbsqli;
$rs_24gaver1 = $db->getSql2($sql);
$totalHtml.= salg($rs_24gaver1, "24Gaver - 400");

$sql = "SELECT sum(quantity) as antal, `shop_name`,`expire_date` FROM `company_order` WHERE `is_cancelled` = 0 and `shop_id` = 55 GROUP by `expire_date` ";
$db = new Dbsqli;
$rs_24gaver2 = $db->getSql2($sql);
$totalHtml.= salg($rs_24gaver2, "24Gaver - 560");

$sql = "SELECT sum(quantity) as antal, `shop_name`,`expire_date` FROM `company_order` WHERE `is_cancelled` = 0 and `shop_id` = 56 GROUP by `expire_date` ";
$db = new Dbsqli;
$rs_24gaver3 = $db->getSql2($sql);
$totalHtml.= salg($rs_24gaver3, "24Gaver - 640");

// ------ guld ------
$sql = "SELECT sum(quantity) as antal, `shop_name`,`expire_date` FROM `company_order` WHERE `is_cancelled` = 0 and `shop_id` = 53 GROUP by `expire_date` ";
$db = new Dbsqli;
$rs_guld = $db->getSql2($sql);
$totalHtml.= salg($rs_guld, "Guldgavekortet");
// ----- norge -----
$sql = "SELECT sum(quantity) as antal, `shop_name`,`expire_date` FROM `company_order` WHERE `is_cancelled` = 0 and `shop_id` = 57 GROUP by `expire_date` ";
$db = new Dbsqli;
$rs_Nogaver1 = $db->getSql2($sql);
 $totalHtml.= salg($rs_Nogaver1, "Julegavekortet Norge - 400");

$sql = "SELECT sum(quantity) as antal, `shop_name`,`expire_date` FROM `company_order` WHERE `is_cancelled` = 0 and `shop_id` = 58 GROUP by `expire_date` ";
$db = new Dbsqli;
$rs_Nogaver2 = $db->getSql2($sql);
 $totalHtml.= salg($rs_Nogaver2, "Julegavekortet Norge - 600");

$sql = "SELECT sum(quantity) as antal, `shop_name`,`expire_date` FROM `company_order` WHERE `is_cancelled` = 0 and `shop_id` = 59 GROUP by `expire_date` ";
$db = new Dbsqli;
$rs_Nogaver3 = $db->getSql2($sql);
 $totalHtml.= salg($rs_Nogaver3, "Julegavekortet Norge - 800");




function salg($data, $title)
{

$total = 0;
$html = "";


 foreach ($data as $item){
   $total+= $item["antal"];
    $html.= "<tr><td>".$title."</td><td>".$item["expire_date"]."</td><td>".$item["antal"]."</td></tr>";
 }
$html.= "<tr><td><b>TOTAL</b></td><td></td><td><b>".$total."</b></td></tr>";



return $html;
}



?>






<!DOCTYPE HTML>

<html>

<head>
  <title>Stats</title>
<style>
body {
  font-size: 12px;
}

table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    text-align: left;
    padding: 8px;
}

tr:nth-child(even){background-color: #f2f2f2}
</style>
 <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">

  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#tabs" ).tabs();
  } );
  </script>


</head>

<body>
<a href="../gavefabrikken_backend/views/nicostats.php?token=sdlfhiekhlsk23232948yruifkhkwsjlzdsfae23f2hd"><h3>Skift til optælling opdelt på DEADLINE</h3></a>




<br />
<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Julegavekortet</a></li>
    <li><a href="#tabs-2" >24gaver - 400</a></li>
    <li><a href="#tabs-3">24gaver - 560</a></li>
    <li><a href="#tabs-4">24gaver - 640</a></li>
    <li><a href="#tabs-5">24gaver - Guld</a></li>
    <li><a href="#tabs-6">Jgk - 400 - norge</a></li>
    <li><a href="#tabs-7">Jgk - 600 - norge</a></li>
    <li><a href="#tabs-8">Jgk - 800 - norge</a></li>


    <li><a href="#tabs-9">Gavekort Statistik</a></li>

  </ul>
  <div id="tabs-1">
  <?php echo $jgk; ?>
  </div>
  <div id="tabs-2">
  <?php echo $gave400; ?>
  </div>
  <div id="tabs-3">
   <?php echo $gave560; ?>
  </div>
  <div id="tabs-4">
  <?php echo $gave640; ?>
  </div>
  <div id="tabs-5">
  <?php echo $guld ?>
  </div>

  <div id="tabs-6">
  <?php echo $gave400no; ?>
  </div>
  <div id="tabs-7">
  <?php echo $gave600no; ?>
  </div>
  <div id="tabs-8">
  <?php echo $gave800no; ?>
  </div>

  <div id="tabs-9">
   <?php echo $html.= "<table border=1 ><tr><th width=200>Kort</th><th width=200>Deadline</th><th>Antal</th></tr>".$totalHtml."</table>"; ?>
  </div>


</div>
</body>

</html>