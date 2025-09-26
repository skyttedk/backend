<?php

 if (session_status() == PHP_SESSION_NONE) {
     session_start();
 }

$canEdit = "0";

if($_SESSION["syslogin"] == 46 || $_SESSION["syslogin"] == 49){
   $canEdit = "3";


} else if($_GET["token"] == "asdf43sdha4fdasdf34olif"){
    $_SESSION["syslogin"] = "40";
    $canEdit = "0";
} else if($_GET["token"] == "df4elhkshrfyufvh"){
    $_SESSION["syslogin"] = "40";
    $canEdit = "2";
} else  if($_GET["token"] == "asdf43sdha4f34o"){
   $canEdit = "1";

    $canEdit = "1";
} else {
    die("Ingen adgang");
}

?>

<!DOCTYPE html>


<html>

<head>
  <title>Cardshops</title>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<style>
body {
    padding:0px;
    margin:0px;
    width: 100%;
    height: 100%;
  	font-family: "Helvetica Neue", Helvetica, sans-serif;
    background: #F7F7F7;
    font-size: 0.8em;
}
 .main{
    width: 1050px;


}

	.table1 {
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;
	}
	.table1 th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
	.table1 td {
		border:1px solid #C0C0C0;
		padding:5px;
	}
    tr:nth-child(even) {
    background-color: #dddddd;
    }
    #accordionCard{
        font-size: 0.75em;
    }

    #accordionCard{
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;
	}
    #accordionCard th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
	#accordionCard td {
		border:1px solid #C0C0C0;
		padding:5px;
	}
 .stamDataFormular{
     width: 300px;
 }
 #dialog_message_AddNewCard option{
     padding:7px;
 }
 #dialog_message_AddNewCard input{

}
#currentSogListContainer{
    width: 98%;
    height: 500px;
    overflow-y: auto;

}
.cardsogList{

    width: 98%;
    border: 1px solid #B7B7B7;
    padding:2px;
    margin-bottom: 3px;
    cursor: pointer;
    background-color: #F1F1F1;
}
.cardsogList.childnode{

   width: 90%;
   border: 1px solid #B7B7B7;
   padding:2px;
   margin-bottom: 3px;
   margin-left:20px;
   cursor: pointer;
}

.cardsogList:hover{
    background-color:#B7B7B7;
    color:white;
    cursor: pointer;
}
.cardsogSelected{
  background-color:#B7B7B7;
}

.stamDataFormularShow{
  width: 95%;
}
#sogFirma{
  font-size: 11px;
}
.noHasCard{
  color:red;
}
 #spr-container input[type=checkbox]
{
  /* Double-sized Checkboxes */
  -ms-transform: scale(2); /* IE */
  -moz-transform: scale(2); /* FF */
  -webkit-transform: scale(2); /* Safari and Chrome */
  -o-transform: scale(2); /* Opera */
  transform: scale(2);
  padding: 10px;
}
#spr-container{
  font-size: 14px !important;
}
#spr-container {
  height: 500px;
}
.cardAccess{
  margin-left: 30px;
}
.goToParentCompany{
  display: none;
}
#tabsCardCompany li a{
    font-size: 10px;
}

<?php
if ($canEdit == "3"){
echo ".cardAccess{  display: none !important; }";
}
?>

</style>

<script src="https://cdn.tiny.cloud/1/p9sl5dai9f0l16tlqbpw4joxwk8mgjjxx6vvm7iqtmh5m99a/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script src="views/lib/jquery.min.js"></script>
<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<link href="views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">
<script src="views/js/main.js"></script>
<script src="views/js/cardCompany.js?v=<?php echo rand(0, 100); ?>"></script>
<script src="views/js/cardStamdata.js?v=<?php echo rand(0, 100); ?>"></script>
<script src="views/js/cardAddNewCard.js?v=<?php echo rand(0, 100); ?>"></script>
<script src="views/js/cardshopnote.js?v=<?php echo rand(0, 100); ?>"></script>
<script src="views/js/shopPresentRules.js?v=<?php echo rand(0, 100); ?>"></script>
<script src="views/js/cardCompanyLayout.js?v=<?php echo rand(0, 100); ?>"></script>


<script>
 var canEdit = "<?php echo $canEdit; ?>";
 var _sysId   = "40" //'<?php echo $_SESSION["syslogin"]; ?>';



 $(function() {
  // alert(canEdit)
 /*
 $( "#bizType" ).buttonset();
        var data = {};
       data['systemuser_id'] = _sysId;
       ajax(data,"tab/loadGiftshopPermission","initTabs","#bizType");
       if(canEdit == "0"){
         $("#bizType").hide();
       }
       if(canEdit == "2"){
         $("#bizType").hide();
         canEdit = "1";
       }
       */
 });

 function initTabs()
 {
     $( "#bizType" ).buttonset();
 }


 function goToShops()
 {
     window.location.href = "index.php?rt=page/shopMain";
 }
 function goToImport()
 {
    window.location.href = "index.php?rt=page/companyCardImport&login=sdfiuwhife";
 }
 function goTosale()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>views/stats_view.php?token=sdlfhiekhlsk23232948yruifkgfddsfgsdfghkwsjlzdsfae23f2hd";
 }
  function goToPluk()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=sch/  ";
 }
 function toggleSogMenu(){
   /*
   if($("#currentSogList").is(":visible")){
        $("#sogMenu").html("&Aring;BEN MENU");

        $("#currentSogList").hide();
   } else {
        $("#sogMenu").html("LUK MENU");
        $("#currentSogList").show();
   }
   */
 }


</script>


</head>

<body>

<center>
<div class="main">
    <div class="header">
        <div id="bizType">
<!--
         <input type="radio" id="radio2" name="radio" checked ><label for="radio2" onclick="goToCardShop()" >Gavekort-shops</label>
           <input type="radio" id="radio3" name="radio" ><label for="radio3"  onclick="goToImport()" >Gavekort - ventene Bestillinger </label>
            <input type="radio" id="radio4" name="radio" ><label for="radio4" onclick="goTosale()">Gavekort - salgsstatistik / lagerstyring</label>
            <input type="radio" id="radio5" name="radio"  ><label for="radio5" onclick="goToPluk()">Gavekort - plukliste</label>
-->
         </div>
    </div>
</center>
<br />
<div style="height: 30px; border:1px solid;">
<div style="margin-top:3px;">
    <button id="sogMenu" style=" margin-right:10px;" onclick="toggleSogMenu()">LUK MENU</button>
    <input  type="radio" name="sogOption" class="sogOption" value="kortnr"><span style="margin-right: 5px;">Kortnr.</span>
    <input type="radio" name="sogOption" class="sogOption" value="sogWithReceipt"><span  style="margin-right: 5px;">Kvitterings nr.</span>
    <input type="radio" name="sogOption" class="sogOption" value="firma" checked="checked"><span  style="margin-right: 20px;">Firma.</span>
    <input class="sogCardShops" style="width:150px;"  type="txt" /><button onclick="cardCompany.sog()">S&oslash;g</button>
    <img title="Opret nyt firma" onclick="cardStamdata.showMedal()" src="views/media/icon/1373253494_plus_64.png" width=25 style="margin-right:5px;float: right; cursor: pointer;" />
    <?php if(\GFCommon\Model\Access\BackendPermissions::session()->hasPermission(\GFCommon\Model\Access\BackendPermissions::PERMISSION_KORT_PLUKLISTER)) { ?><div style="float: right; padding-top: 2px; padding-left: 10px; padding-right: 20px;"  ><button type="button"  onclick="cardStamdata.showPluklist()"  >Hent pluklister</  button></  div><?php } ?>
</div>
</div>

<table width=100% border=1 >

<tr height=550>
     <td valign="top" align="left" width=350><div id="currentSogList" style="width: 350px; height: 550px; border:1px solid black; background-color:white;  "></div></td>
     <td valign="top" align="left"  id="currentSogContent" valign="top" valign="top"></td>
</tr>


</table>


</div>
<div id="dialog_message" class="table1" title="" style="display:none;"></div>
<?php include("dialogMessageStamData.php"); ?>
<?php include("dialogMessageAddNewCard.php"); ?>
<?php include("dialogCardShopMultiCreate.php"); ?>
<?php include("dialogMultiMoveToChild.php"); ?> 
<div class="modalMsg" style="display: none;"></div>
</body>
</html>