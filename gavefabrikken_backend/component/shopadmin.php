<?php
if($_GET["token"] != "fuiegiusgfueyuyegfuisg"){
    die("Ingen adgang");
}

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
error_reporting(E_ALL);

include("sms/db/db.php");


$outputArr = [];
$db = new Dbsqli();
$db->setKeepOpen();

$sql = "SELECT * FROM `shop` ORDER BY deleted,name";
$rs = $db->get($sql);

//print_R($rs["data"]);

$html = "<table border=1 cellpadding=5>";
foreach($rs["data"] as $ele){
    if($ele["deleted"]==0) {
        $html.= "<tr><td>".utf8_decode($ele["name"])."</td><td><button onclick='change(this,".$ele["id"].",\"slet\")'>SLET</button></td></tr>";
    } else {
        $html.= "<tr><td>".utf8_decode($ele["name"])."</td><td><button onclick='change(this,".$ele["id"].",\"gendan\")'>GENDAN</button></td></tr>";
    }
}
$html.= "</table>";
echo $html;
?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

function change(ele,id,action){
    if(action=="slet"){
    r= confirm("Vil du slette?")
    } else {
        r= confirm("Vil du genoprette?")
    }


    if(!r) return;
    var ele = ele;
    $.post( "<?php echo GFConfig::BACKEND_URL; ?>component/shopadminSlet.php", { action: action, id:id })
    .done(function( data ) {
        $(ele).parent().parent().hide();
    });
}




</script>