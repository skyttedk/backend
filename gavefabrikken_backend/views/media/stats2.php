<?php
header("Location: ".GFConfig::BACKEND_URL."views/stats2020.php?token=fsdklj43dfgiDFGo90HFHDFG5g_4eu8");
die("");




if (session_status() == PHP_SESSION_NONE) session_start();
$_SESSION["syslogin".GFConfig::SALES_SEASON] = 40;
if($_GET["token"] != "fsdklj43dfgiDFGo90HFHDFG5g_4eu8" ){
  die("Ingen adgang");
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











     <script src="lib/jquery.min.js"></script>
<script src="lib/jquery-ui/jquery-ui.js"></script>
<link href="lib/jquery-ui/jquery-ui.css" rel="stylesheet">

    <script src="js/main.js?v1"></script>
  <script>
 var _ajaxPath = "../../gavefabrikken_backend/index.php?rt=";
  var _sysId   = '<?php echo $_SESSION["syslogin"]; ?>';

  $(function() {

       var data = {};
       data['systemuser_id'] = _sysId;

       $.post(_ajaxPath+"tab/loadGiftshopPermission",data, function(data ) {
                $("#bizType").html(data)
       });

 });

 function initTabs()
 {
   $( "#bizType" ).buttonset();

  $( "#tabs" ).tabs();
        $( "#tabs" ).tabs({ active: 0 });
       $("#tabs-1").html("Systemet Arbejder")
          $.post(_ajaxPath+"stats2019/getAllStats",{"shop_id":'52'}, function(data, textStatus) {
                $("#tabs-1").html(data)
               // getStuckData()
              //  getTotalCardSold()
          });


 }


 /*
  $( function() {


  } );
 */

function getTotalCardSold()
{
    $.post(_ajaxPath+"stats2019/totalCard",{}, function(data, textStatus) {
        $("#totalCardsSold").html(data);
    });

}

var _shop_id = "52";
var _tab_id = "tabs-1";

function loadAllStat(shop_id,tab_id)
{
          $( "#sysmsg" ).html("")
          $("#"+tab_id).html("Systemet Arbejder")

          _tab_id = tab_id;
          _shop_id = shop_id;
          $.post(_ajaxPath+"stats2019/getAllStats",{"shop_id":shop_id}, function(data, textStatus) {
                $("#"+tab_id).html(data)
                getStuckData()
          });
}

function loadCardSale(tab_id){
    $("#"+tab_id).html("Systemet Arbejder")
          $( "#sysmsg" ).html("")
          _tab_id = tab_id;
          $.post(_ajaxPath+"stats2019/cardSale",{}, function(data, textStatus) {
                $("#"+tab_id).html(data)
                //getStuckData()
          });

}


function loadStat(shop_id,tab_id)
{
    $("#"+tab_id).html("Systemet Arbejder")
    if($( "#deadline" ).val() == "alle"){
         loadAllStat(shop_id,tab_id)
    } else {
          $( "#sysmsg" ).html("")
          _tab_id = tab_id;
          _shop_id = shop_id;
          $.post(_ajaxPath+"stats2019/getStats",{"deadline":$( "#deadline" ).val(),"shop_id":shop_id}, function(data, textStatus) {
                $("#"+tab_id).html(data)
                //getStuckData()
          });
    }
}
function newDeadline()
{
    if($( "#deadline" ).val() == "alle"){
        loadAllStat(_shop_id,_tab_id);
    } else {
        loadStat(_shop_id,_tab_id);
    }

}
function updateStuck(presentId,unikId,modelBase64)
{
    var postData = {
        shopId:_shop_id,
        deadline:$( "#deadline" ).val(),
        quantity:$("#"+unikId).val(),
        present_id:presentId,
        model_id:unikId,
        model_id_base64:modelBase64
    }
    $.post(_ajaxPath+"stats2019/updateStats",postData, function(data, textStatus) {
             $( "#sysmsg" ).html("værdien: "+data+" er gemt")
    });
}

function getStuckData()
{
    var postData = {
        shopId:_shop_id,
        deadline:$( "#deadline" ).val(),
    }
    $.post(_ajaxPath+"stats2019/getStatsData",postData, function(data, textStatus) {
        var jsonData = jQuery.parseJSON(data);
        $.each(jsonData, function(i, obj) {
          $("#"+obj.model_id).val(obj.quantity);

          var instuck = (obj.quantity*1) - 50;
          var regStuckNr = $("#val_"+obj.model_id).html()*1;


          if( regStuckNr > instuck ){

                 $("#"+obj.model_id).css('border-color', 'red');
          }

        });

    });

}
 function goToShops()
 {
     window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/shopMain";
 }
 function goToImport()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/companyCardImport&login=sdfiuwhife";
 }
 function goTosale()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>views/stats_view.php?token=sdlfhiekhlsk23232948yruifkgfddsfgsdfghkwsjlzdsfae23f2hd";
 }

 function goToCardShop()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/cardShop&token=asdf43sdha4f34o ";
 }
 function goToPluk()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=sch/  ";
 }


</script>




</head>

<body>

  <center>
    <div class="header" style="display:none;">
        <div id="bizType" >

        </div>
    </div>
      </center>

<h3>Total antal kort: <span id="totalCardsSold"></span></h3>

<br />
<select id="deadline" onchange="newDeadline() ">
  <option value="alle">alle</option>
  <option value="2020-11-01">uge 48 </option>
  <option value="2020-11-08">uge 49</option>
  <option value="2020-11-15">uge 50 </option>
  <option value="2020-11-29">uge 51 (2020-11-29) </option>
  <option value="2020-11-22">uge 51 (2020-11-22) </option>
  <option value="2020-12-31">uge 4(2021)</option>
  <option value="2022-01-01">2022-01-01 (hjemmelevering)</option>
</select>
<br />
<br />
<div id="tabs">
  <ul style="font-size: 11px;">
    <li><a href="#tabs-14" onclick="loadCardSale('tabs-14')">Kort salg</a></li>
    <li><a href="#tabs-0" onclick="loadStat('0','tabs-0')">Alle gavevalg</a></li>
    <li><a href="#tabs-1" onclick="loadStat('52','tabs-1')">Julegavekortet</a></li>
    <li><a href="#tabs-2" onclick="loadStat('54','tabs-2')">24gaver-400</a></li>
    <li><a href="#tabs-3" onclick="loadStat('55','tabs-3')">24gaver-560</a></li>
    <li><a href="#tabs-4" onclick="loadStat('56','tabs-4')">24gaver-640</a></li>
    <li><a href="#tabs-5" onclick="loadStat('53','tabs-5')">Guld</a></li>

    <li><a href="#tabs-10" onclick="loadStat('290','tabs-10')">Dr&oslash;m-200</a></li>
    <li><a href="#tabs-11" onclick="loadStat('310','tabs-11')">Dr&oslash;m-300</a></li>
    <li><a href="#tabs-12" onclick="loadStat('575','tabs-12')">DESIGN</a></li>
    <li><a href="#tabs-9" onclick="loadStat('574','tabs-9')">GULL-GAVEKORTET-norge</a></li>
    <li><a href="#tabs-13" onclick="loadStat('272','tabs-13')">Jgk-300-norge</a></li>
    <li><a href="#tabs-6" onclick="loadStat('57','tabs-6')">Jgk-400-norge</a></li>
    <li><a href="#tabs-7" onclick="loadStat('58','tabs-7')">Jgk-600-norge</a></li>
    <li><a href="#tabs-8" onclick="loadStat('59','tabs-8')">Jgk-800-norge</a></li>



    <span id="sysmsg" style="margin-left:100px;"></span>


  </ul>
  <div id="tabs-14"></div>
  <div id="tabs-0"></div>
  <div id="tabs-1"></div>
  <div id="tabs-2"></div>
  <div id="tabs-3"></div>
  <div id="tabs-4"></div>
  <div id="tabs-5"></div>
  <div id="tabs-6"></div>
  <div id="tabs-7"></div>
  <div id="tabs-8"></div>

  <div id="tabs-9"></div>
  <div id="tabs-13"></div>
  <div id="tabs-10"></div>
  <div id="tabs-11"></div>
   <div id="tabs-12"></div>


</div>
</body>
</html>