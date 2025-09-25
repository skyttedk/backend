<?php
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
  font-size: 12px !important;
    display: none;
}

table {
    border-collapse: collapse !important;
    width: 100% !important;
}

th, td {
    text-align: left !important;
    padding: 8px !important;
}

tr:nth-child(even){background-color: #f2f2f2 !important}
#csv{
   opacity: 0.5;
}

</style>











     <script src="lib/jquery.min.js"></script>
<script src="lib/jquery-ui/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<link href="lib/jquery-ui/jquery-ui.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" rel="stylesheet">

    <script src="js/main.js?v2"></script>
  <script>
 var _ajaxPath = "../../gavefabrikken_backend/index.php?rt=";
  var _sysId   = '<?php echo $_SESSION["syslogin".GFConfig::SALES_SEASON]; ?>';

  $(function() {

       var data = {};
       data['systemuser_id'] = _sysId;

       $.post(_ajaxPath+"tab/loadGiftshopPermission",data, function(data ) {
                $("#bizType").html(data)
       });
      $("body").fadeIn(500);
 });

 function initTabs()
 {
   $( "#bizType" ).buttonset();

  $( "#tabs" ).tabs();
        $( "#tabs" ).tabs({ active: 0 });
       $("#tabs-1").html("Systemet Arbejder")
          $.post(_ajaxPath+"stats2020/getAllStats",{"shop_id":'52'}, function(data, textStatus) {
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
    $.post(_ajaxPath+"stats2020/totalCard",{}, function(data, textStatus) {
        $("#totalCardsSold").html(data);
    });

}

var _shop_id = "";
var _tab_id = "tabs-1";

function loadAllStat(shop_id,tab_id)
{
          $( "#sysmsg" ).html("")
          $("#"+tab_id).html("Systemet Arbejder")

          _tab_id = tab_id;
          _shop_id = shop_id;
          $.post(_ajaxPath+"stats2020/getAllStats",{"shop_id":shop_id}, function(data, textStatus) {
                $("#"+tab_id).html(data)
                getStuckData()

          });
}

function loadCardSale(tab_id){
    $('#csv').css('opacity', '0.5');
    _shop_id = "";
    $("#"+tab_id).html("Systemet Arbejder")
          $( "#sysmsg" ).html("")
          _tab_id = tab_id;
          $.post(_ajaxPath+"stats2020/cardSale",{}, function(data, textStatus) {
                $("#"+tab_id).html(data)
                //getStuckData()
          });

}


function loadStat(shop_id,tab_id)
{
    $(".statsContent").html("");
    $('#csv').css('opacity', '1.0');
    $("#"+tab_id).html("Systemet Arbejder")
          $( "#sysmsg" ).html("")
          _tab_id = tab_id;
          _shop_id = shop_id;

          $.post(_ajaxPath+"stats2020/getAllStats",{"deadline":$( "#deadline" ).val(),"shop_id":shop_id}, function(data, textStatus) {
                $("#"+tab_id).html("");
                $("#"+tab_id).html(data)
                 //getStuckData()
          });
}
function newDeadline()
{
    loadStat(_shop_id,_tab_id);

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
    $.post(_ajaxPath+"stats2020/updateStats",postData, function(data, textStatus) {
             $( "#sysmsg" ).html("v�rdien: "+data+" er gemt")
    });
}

function getStuckData()
{
    var postData = {
        shopId:_shop_id,
        deadline:$( "#deadline" ).val(),
    }
    $.post(_ajaxPath+"stats2020/getStatsData",postData, function(data, textStatus) {
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

function csv(){
          if(_shop_id == "") return;
          var url = _ajaxPath+"stats2020/getCsvFile&deadline="+$( "#deadline" ).val()+"&shop_id="+_shop_id;
          var win = window.open(url, '_blank');
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

<h3>GF - GAVEKORT STATS 2021</h3>

<br />
<select id="deadline" onchange="newDeadline() ">
  <option value="alle">alle</option>
  <option value="2020-11-01">2020-11-01, uge48 (Jgk,Design,Norge)</option>
  <option value="2020-11-08">2020-11-08, uge49 (24gaver,Guld,Dr�mme,Sverige)</option>
  <option value="2020-11-15">2020-11-15, uge50 (Jgk,Design,Norge)</option>
  <option value="2020-11-22">2020-11-22, uge51 (24gaver,Dr�mme,Guld,Sverige)</option>
  <option value="2020-11-29">2020-11-29, uge51 (Jgk)</option>
  <option value="2020-12-31">2020-12-31, uge04 (Alle korttyper)</option>
  <option value="2021-04-01">2021-04-01, Hjemmelevering (24gaver,Guld)</option>
  <option value="2020-11-07">2020-11-07, Hjemmelevering Norge</option>
  <option value="2021-01-03">2021-01-03, Hjemmelevering Norge</option>
  <option value="2021-12-31">2021-12-31, Hjemmelevering Sverige</option>

                                                      
</select>
<br /><br />
<button id="csv" onclick="csv()" >Download csv file</button>
<br /><br />
<div id="tabs">
  <ul style="font-size: 11px;">
   <!-- <li><a href="#tabs-14" onclick="loadCardSale('tabs-14')">Kort salg</a></li> -->
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
    <li><a href="#tabs-8" onclick="loadStat('1832','tabs-15')">Sverige - 300</a></li>
    <li><a href="#tabs-8" onclick="loadStat('1981','tabs-16')">Sverige - 800</a></li>

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
  <div id="tabs-15"></div>
  <div id="tabs-16"></div>
</div>
</body>
</html>