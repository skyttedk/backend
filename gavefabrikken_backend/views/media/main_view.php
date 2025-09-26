<?php
 if (session_status() == PHP_SESSION_NONE) session_start();


if(!isset($_SESSION["syslogin"])){
header("Location: index.php?rt=login");
die();
}

$localroute = "";
$shopStartjs = "";
if(isset($_GET["editShopID"])){
    $shopStartjs = $_GET["editShopID"];
}
if(isset($_GET["localroute"])){
    $localroute = $_GET["localroute"];
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
$forceJsLoad =  generateRandomString();

?>

<!DOCTYPE html>

<html>

<head>
  <title>GF - SYSTEM </title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta http-equiv="pragma" content="no-cache">

<style>
html{
  overflow: none;
}

body {
    padding:0px;
    margin:0px;
    width: 100%;

  	font-family: "Helvetica Neue", Helvetica, sans-serif;
    background: #F7F7F7;
    font-size: 0.8em;
    overflow: none
}
button {
    cursor: pointer;
}

.main{
    width: 96%;

     display: block;
overflow: auto;
}

.header{
    width: 100%;
    height: 50px;

}

#bizType{
    float: left;

}

.menu{
    width: 100%;
    height: 50px;


}
#partSystem{
    float: right;

}
#mainSearchContainer {
    float: left;
    width: 200px;
    padding-top:15px;

}

#content{
    width: 100%;
 	color: #444;

    overflow-x:hidden;
	-webkit-font-smoothing: antialiased;
   
}
.trail{
    width: 100%;
    height: 30px;
}
#trailContainer{
    float: right;
    font-size: 0.75em;

}


.sort-img{
    background-size:     contain;                      /* <------ */
    background-repeat:   no-repeat;
    background-position: center center;

    background-color: white;
    height: 150px;
    width: 150px;



}

.logo-img{
    background-size:     contain;                      /* <------ */
    background-repeat:   no-repeat;
    background-position: center center;
    border:1px solid white;
    height: 100px;
    width: 80px;

     float: left;
     margin-left: 15px;
      margin-top: 15px;
      border:1px solid black;
}
.logo-container{
    background-color: #F4F4F4;
	perspective: 1000;
    float: left;
     margin-left: 15px;
      margin-top: 15px;
}

/* entire container, keeps perspective */
.flip-img{
    background-size:     contain;                      /* <------ */
    background-repeat:   no-repeat;
    background-position: center center;
    border:1px solid white;
    height: 200px;
    width: 95%;
}



.flip-container {

    background-color: #F4F4F4;
	perspective: 1000;
    float: left;
     margin-left: 15px;
      margin-top: 15px;
}
	/* flip the pane when hovered */
	.flip-container:hover .flipper, .flip-container.hover .flipper {
		transform: rotateY(180deg);
	}

.flip-container, .front, .back {
	width: 225px;
	height: 300px;
    padding:5px;
}

/* flip speed goes here */
.flipper {
	transition: 0.6s;
	transform-style: preserve-3d;

	position: relative;
}

/* hide back of pane during swap */
.front, .back {
	backface-visibility: hidden;
    background-color: white;
	position: absolute;
	top: 0;
	left: 0;
}

/* front pane, placed above back */
.front {
	z-index: 2;
	/* for firefox 31 */
	transform: rotateY(0deg);
}

/* back, initially hidden pane */
.back {
	transform: rotateY(180deg);
}
#presentsTabs .headline{
    font-size: 0.8em;
}
#presentsTabs label{
    font-weight: bold;
}
#presentDescriptionTabs label{
    font-weight: bold;
}

#presentDescriptionTabs .headline{
    font-size: 0.7em;
}

#sortable_inShop { list-style-type: none; margin: 0; padding: 0; width: 800px; }
#sortable_inShop { margin: 3px 3px 3px 0; padding: 1px; float: left; width: 150px; height: 170px; font-size: 4em; text-align: center; }


#sortable { list-style-type: none; margin: 0; padding: 0; width: 800px; }
#sortable li { margin: 3px 3px 3px 0; padding: 1px; float: left; width: 150px; height: 170px; font-size: 4em; text-align: center; }

#presentVariantTabs table{
    width: 850px;
    border:1px solid gray;
}
#presentVariantTabs td{
    padding: 5px;
}
.tableStyle1 {
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;
	}
.tableStyle1 th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;                                                                                                                                                                                                                                              ../log.php
	}
.tableStyle1 td {
		border:1px solid #C0C0C0;
		padding:5px;
	}
.tableStyle1 input {
    width: 95%;
    margin-left:5px;
    margin-right: 5px;
}
.icon{
    cursor: pointer;
}
.companyMenuItem{
    height: 20px;
    margin:5px;
    border:1px solid black;
    padding:5px;
    cursor: pointer;
}
.companyMenuItem:hover{
    font-size: 20px;
    background-color: #2293F7;
    color:white;

}
#selectedLogo{
    background-repeat: no-repeat;
    background-size: contain;
}
.isVariant{
position:absolute;
right:0px;
top:0px;
}
.mouse{
   cursor: pointer;
}
.mouse:hover { transform: scale(1.2); }
 .redColor{
	background-color:#1CCC3C !important;
	color:white;
}
  .progress-label {
    position: absolute;
    left: 50%;
    top: 4px;
    font-weight: bold;
    text-shadow: 1px 1px 0 #fff;
  }
  .progressbar{
      width: 300px;
      height: 30px;
  }
  .uploadGf{
      display: none;
  }

  .safeLayerTimer{
    background:url('views/media/system/gears.gif');
    background-size: cover;
    position: absolute;
    width: 100px;
    height: 100px;
    left:20px;
    top:20px;
    z-index: 999;
    display: none;
  }

  .safeLayer{
       opacity: 0.2;
    filter: alpha(opacity=20); /* For IE8 and earlier */
  background-color: #D8D8D8;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 99;
    display: none;
  }
 iframe{
    border:0px;
 }

</style>
<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.6.0/pure-min.css">
<link rel="stylesheet" type="text/css" href="views/css/infoboard.css">
<!--
<link rel="stylesheet" type="text/css" href="http://docs.handsontable.com/pro/bower_components/handsontable-pro/dist/handsontable.full.min.css">
<script src="http://docs.handsontable.com/pro/bower_components/handsontable-pro/dist/handsontable.full.min.js"></script>
-->
 <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/handsontable/2.0.0/handsontable.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/handsontable/2.0.0/handsontable.min.js"></script>


<script src="views/lib/jquery.min.js"></script>


<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<link href="views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">


<script src="views/js/main.js"></script>

<?php
echo '<script src="views/js/infoboard.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/biz.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/drop.js?v='.$forceJsLoad.'"></script>';
echo '<script src="views/js/presents.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/systemUser.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/company.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/copy.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/feltDeff.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/userData.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/rapport.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/presentsOptions.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/inShop.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/gavevalg.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/shop.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/shopSettings.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/shopboard.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/monitoringItemnr.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/gaveAdmin.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/utf8.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/helper.js?'.$forceJsLoad.'"></script>';
echo '<script src="views/js/searchCompany.js?'.$forceJsLoad.'"></script>';


?>
<!--
<script src="views/js/drop.js"></script>
<script src="views/js/presents.js"></script>
<script src="views/js/systemUser.js"></script>
<script src="views/js/company.js"></script>
<script src="views/js/copy.js"></script>
<script src="views/js/feltDeff.js"></script>
<script src="views/js/userData.js"></script>
<script src="views/js/rapport.js"></script>
<script src="views/js/presentsOptions.js"></script>
<script src="views/js/inShop.js"></script>
 <script src="views/js/gavevalg.js"></script>
<script src="views/js/shop.js"></script>
<script src="views/js/shopSettings.js"></script>
<script src="views/js/shopboard.js"></script>
<script src="views/js/gaveAdmin.js"></script>
<script src="views/js/utf8.js"></script>
<script src="views/js/helper.js"></script>
-->
<link rel="stylesheet" href="https://rawgit.com/enyo/dropzone/master/dist/dropzone.css">


<script>
       //     $(".safeLayerTimer").hide();
       //     $(".safeLayer").hide();

var _localroute  = "<?php echo $localroute; ?>";
var _editShopID = "<?php echo $shopStartjs; ?>";
var _sysId   = "<?php echo $_SESSION["syslogin"]; ?>";
var _dropTarget = "";
var _enterFocus = "";

$(document).keypress(function(e) {

if(e.which == 13) {
        if(_enterFocus == "event_valgshopSog"){
            company.search();
        }
        if(_enterFocus == "event_AdminGaverSog"){
           presentFront.sog();
        }
    }


});

function regEventAction(action){
    _enterFocus = action;
}

 var pinger;
 $(function() {
   // pinger = setInterval(ping, 60000);
    mainTabControl()

 });
 function ping()
 {
    //ajax(data,"ping","","");
 }





 function mainTabControl(){
       var data = {};
       data['systemuser_id'] = _sysId;
       ajax(data,"tab/loadFrontPermission","","#bizType");
       // Vigtigt i det html der kommer tilbage fra   loadFrontPermission, ligger der js-script der kalder company js funktionen.
 }
 function mainTabControlResponse(status){
        if(status == "hide") { $("#frontMenu").hide();  }
        $( "#bizType" ).buttonset();
        company.init();
 }
 function showValgshop(){
      window.location.href = "index.php?rt=mainaa&sysid="+_sysId;
 }
 function showArkiv()
 {
       window.open("<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/showArkiv&token=dsfkjsadhferuifghsdfssudif", "_blank");
 }


function test(){
        $("#content").html($( "#boxit" ).html());
        $( "#boxit" ).html("");
}


/*
function setInterKeyAction(htmlElement){
    _enterFocus =  htmlElement+"()";
    $(document).keypress(function(e) {
        if(e.which == 13) {
       //    eval(_enterFocus);
        }
    });
}
*/

</script>


</head>

<body>
 <div class="safeLayerTimer"></div>
<div class="safeLayer"> </div>

<div id="userUploadMsg" style="display:none; width: 200px; text-align:center; padding: 5px; height: 20px; border:1px solid black;"></div>
<div id="userErrorMsg" style="display:none; width: 400px; text-align:center; color:red; padding: 5px; height: 20px; border:1px solid black;"></div>
<button class="uploadGf" onclick="userData.closeApp()">Close</button><button id="uploadSave" class="uploadGf" onclick="userData.saveItem()">Save</button><button  class="uploadGf" onclick="userData.resetApp()">Refresh</button><button class="uploadGf" onclick="userData.deleteAllCheck()">SLET ALLE</button> <button  style="display:none;" id="goon" onclick="userData.goon()">Fortsæt</button>

<div id="userUploadContainer" ></div>

<center>
<div class="main">
    <div class="header">
        <div id="bizType">

       </div>
    </div>



    <table width=100% border=1 id="frontMenu" >
    <tr>
    <td width=35% align=left>
        <input type="text" placeholder="S&oslash;g efter virksomhed"  id="menuSearch" onclick="this.select(); regEventAction('event_valgshopSog')"   /><button onclick="company.search()">S&oslash;g</button><button onclick="company.searchClose()">Luk</button> <input id="sogAllShops" style="margin-left:20" type="checkbox" /><label>Medtag lukkede</label>
        <div id="companyList" style="width:400px; z-index: 1000; display:none; height: 300px; border:1px solid black; position: absolute; background-color: white; overflow-y: auto; top:120px;  ">

      </div>
    </td>
    <td width=25% >
       <div id="overskriftValgtShop" style="font-weight: bold; margin-left:5px;"></div>
    </td>
    <td width=25% align=right>
         <input type="text" id="receiptSearch" onClick="this.select();"  placeholder="S&oslash;g kvitteringsnr og email" />     <button onclick="searchCompany.receiptNumber()" class="button">S&oslash;g </button>
    </td>
    <td width=20% align=right>
            <div class=trail>
            <div id="trailContainer">
                <button onclick="company.createNew()" class="button">Opret ny valgshop</button>
            </div>
        </div>
    </td>
    </tr>
    </table>





    <hr style="color:white;" />
    <div id="content">  <center>
    <h1></h1>
         <img width=500 src="<?php echo GFConfig::BACKEND_URL; ?>views/media/icon/3dgifmaker92938.gif" alt="" />
<!--    <div style="height: 400px; overflow: auto; width: 500px; border:1px solid black; padding: 10px; text-align: left; font-size: 14px;">





     </div>
    -->
    </div>




  </center></div>

</div>
</center>

<div id="dialog-message" title="" style="display:none;"></div>
<div id="dialog-searchCompany" title="" style="display:none;"> Søger</div>
<div id="boxit" style="display:none;"></div>
<div id="sysworkerIcon" style=" display:none;   position: absolute; left: 0; top:0; "><img src="views/media/icon/save-as-xxl.png" width=50 height=50 alt="" /> </div>
</body>
</html>