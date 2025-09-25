<?php
if(!isset($_GET["token"]) || $_GET["token"] != "sdafjsdaoif8903y4hoisa84rty3487gowae"){
    die("Ingen adgang");
}

set_time_limit ( 4000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$data = ["210111",
    "210138",
    "210151",
    "210152",
    "220105",
    "220120",
    "220121",
    "220162",
    "220163",
    "220116",
    "230155",
    "200140",
    "200141",
    "230131",
    "230130",
    "210101",
    "210104",
    "220131",
    "230134",
    "220115",
    "210185-2",
    "210114",
    "190118",
    "220108",
    "230162",
"230139",
"230141",
"150205",
"190117",
"210128",
"210156",
"220106",
"220128",
"SAM3777",
"SAM3778",
"210191",
"220155",
"210186",
"210187",
"210188",
"210189",
"210190",
"160101",
"220114",
"230123",
"180105",
"180111",
"180135",
"190128",
"190129",
"200112",
"200113",
"200114",
"210125",
"220107",
"220112",
"220119",
"220129",
"220138",
"220113",
"220122",
"220127",
"220130",
"220135",
"220145",
"200110",
"220156",
"190119",
"200102",
"200103",
"200104",
"210165",
"210166",
"220137",
"190106",
"190124",
"200128",
"200129",
"200154",
"210105",
"220118",
"220172",
"210130",
"210132",
"210142",
"210171",
"220101",
"220125",
"220159",
"220160",
"220176",
"220178",
"220157",
"230101",
"230104",
"230105",
"230106",
"230114",
"230116",
"230118",
"200134",
"200138",
"200139",
"200142",
"210135",
"220154",
"210119",
"210140"];
$output = [];
$sql = "SELECT";
foreach ($data as $item ){

  $sql = "SELECT *  FROM `present_model` WHERE `language_id` = 1 AND `model_present_no` LIKE '$item' and `original_model_id` = 0";
  $rs = $db->get($sql);
  if(sizeof($rs["data"]) == 0 ){
   continue;
  }
   foreach ($rs["data"] as $ele){
       $sql ="SELECT * FROM `present_description` WHERE `present_id` = ".$ele["present_id"]." and language_id in(1,2)";
       $rs = $db->get($sql);

       $d = $rs["data"];
       $short_description_da  = base64_decode($d[0]["$short_description"]);
       $short_description_eng = base64_decode($d[1]["$short_description"]);
       $short_description_da =  str_replace("###","",$short_description_da);
       $short_description_eng =  str_replace("###","",$short_description_eng);

       $long_description_da  =   Strip_tags($short_description_da."<br>".base64_decode($d[0]["long_description"]));
       $long_description_eng  =  Strip_tags($short_description_eng."<br>".base64_decode($d[1]["long_description"]));
       
       $output[] = array(
           "id"=>$d[0]["id"],
           "itemnr"=>$item,
           "caption_da"=>$d[0]["caption"],
           "long_description_da"=>"$long_description_da",
           "caption_eng"=>$d[1]["caption"],
           "long_description_eng"=>"$long_description_eng"
       );
    }

}
echo "<table border='1'>";
foreach ($output as $o){
    echo "<tr>
            <td>".$o["id"]."</td>
            <td>".$o["itemnr"]."</td>
            <td>".$o["caption_da"]."</td>
            <td>".$o["long_description_da"]."</td>
            <td>".$o["caption_eng"]."</td>
            <td>".$o["long_description_eng"]."</td>
          </tr>";
}
echo "</table>";
//$rs = $db->get($sql);

