<?php

$baseurl = "https://system.gavefabrikken.dk/gavefabrikken_backend/";
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


#csv{
   opacity: 0.5;
}
.flex-container {
    display: flex;
    flex-wrap:nowrap;
    overflow: auto;



}
.header-title{
    font-size: 24px;
    font-weight: bold;
}
.v-flex{
    width: 300px;

}
.v-flex > div{
    text-align: center;
    padding: 6px;
    font-size: 14px;
    margin: 1px;

}

.v-flex > div:nth-child(even){
    background-color: #DCDCDC !important;
}
.flex-container > div {
}
.header-title{
    min-width: 150px;
    height: 50px;
}
.consept{
   color:mediumblue;
    font-weight: bold;
}
.css-db{
    position: absolute;
    top: 120px;
    left: 400px;
    font-size: 12px;
    font-weight: bold;
}
.css-db tr{
    height: 10px;
}
.css-red{
    color: red;
}
</style>











     <script src="lib/jquery.min.js"></script>
<script src="lib/jquery-ui/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<link href="lib/jquery-ui/jquery-ui.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" rel="stylesheet">

    <script src="js/main.js?v1"></script>
  <script>
 var _ajaxPath = "../../gavefabrikken_backend/index.php?rt=";
  var _sysId   = '40';

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
      
     //  loadCardSale("tabs-14")
         /*
         $.post(_ajaxPath+"stats2020/getAllStats",{"shop_id":'52'}, function(data, textStatus) {
                $("#tabs-1").html(data)
               // getStuckData()
              //  getTotalCardSold()
          });
         */

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
     $("#deadline").hide();
     $('#csv').css('opacity', '0.5');
     _shop_id = "";
     $("#"+tab_id).html("Systemet Arbejder")
     $( "#sysmsg" ).html("")
     _tab_id = tab_id;

     if( tab_id ==  "tabs-100"){
         // Åbn en ny tab med card sale data
         $.post(_ajaxPath+"statCardSales/cardSale",{}, function(data, textStatus) {
             // Åbn ny tab og indsæt data direkte
             window.open('https://system.gavefabrikken.dk/gavefabrikken_backend/controller/statCardSalesController.php?token=fsdklj43dfgiDFGo90HFHDFG5g_4eu8', '_blank');

         });
     } else {
         $.post(_ajaxPath+"stats2021/cardSale",{}, function(data, textStatus) {
             $("#"+tab_id).html(data)
             //getStuckData()
         });
     }
 }

function loadTab(shop_id,tab_id)
{

   $("#deadlineContainer").html("");
   loadStat(shop_id,tab_id);
}



function loadStat(shop_id,tab_id)
{

    let deadline =  $("#deadlineContainer").html() == "" ? "alle" : $("#deadline").val();


    $(".statsContent").html("Henter data");
    $('#csv').css('opacity', '1.0');
    $("#"+tab_id).html("Systemet Arbejder")
          $( "#sysmsg" ).html("")
          _tab_id = tab_id;
          _shop_id = shop_id;
            let alias = $("#alias").is(':checked') ? 1:0;
          $.post(_ajaxPath+"stats2021/getAllStats",{deadline:deadline,shop_id:shop_id,alias:alias}, function(data, textStatus) {
              $(".sct").html("")
              $("#"+tab_id).html("");
                $("#"+tab_id).html(data)
                if($("#deadlineContainer").html() == ""){
                    loadDeadlines(shop_id);
                }


          });
}
async function loadDeadlines(shop_id)
{

    $.post(_ajaxPath+"stats2021/getDeadlines",{shop_id:shop_id}, function(data, textStatus) {
        $("#deadlineContainer").html(data);

                if(shop_id == 0){
                    $("#deadline").hide();
                        } else {
                        $("#deadline").show();
                        }
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
     window.location.href = "<?php echo $baseurl; ?>index.php?rt=page/shopMain";
 }
 function goToImport()
 {
    window.location.href = "<?php echo $baseurl; ?>index.php?rt=page/companyCardImport&login=sdfiuwhife";
 }
 function goTosale()
 {
    window.location.href = "<?php echo $baseurl; ?>views/stats_view.php?token=sdlfhiekhlsk23232948yruifkgfddsfgsdfghkwsjlzdsfae23f2hd";
 }

 function goToCardShop()
 {
    window.location.href = "<?php echo $baseurl; ?>index.php?rt=page/cardShop&token=asdf43sdha4f34o ";
 }
 function goToPluk()
 {
    window.location.href = "<?php echo $baseurl; ?>index.php?rt=sch/  ";
 }

function csv(){
          let alias = $("#alias").is(':checked') ? 1:0;
          if(_shop_id == "") return;
          var url = _ajaxPath+"stats2021/getCsvFile&deadline="+$( "#deadline" ).val()+"&shop_id="+_shop_id+"&alias="+alias;
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

<h3>GF - GAVEKORT STATS 2025</h3>
<div style="position:fixed; top:40px; right: 20px;display:none">
    <img src="<?php echo $baseurl; ?>views/media/icon/dk_64.png" width="50" alt="" />
    <img src="<?php echo $baseurl; ?>views/media/icon/no_64.png" width="50" alt="" />
    <img src="<?php echo $baseurl; ?>views/media/icon/sw_64.png" width="50" alt="" />

</div>
<br />
<div id="deadlineContainer" >

</div>
<!--
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
-->

  <label>Vis Alias</label><input type="checkbox" onchange="newDeadline()" id="alias" />
  <br /><br />
<button id="csv" onclick="csv()" >Download csv file</button>
<br />
<div id="tabs">
  <ul style="font-size: 11px;">
    <li><a href="#tabs-15" >Info</a></li>
      <li><a href="#tabs-14" onclick="loadCardSale('tabs-14')">Kort salg</a></li>
     <!-- <li><a href="#tabs-100" onclick="loadCardSale('tabs-100')">##</a></li> -->
    <li class="dk-tab tab"><a href="#tabs-0" onclick="loadTab('0','tabs-0')">Alle gavevalg</a></li>

     <li class="dk-tab tab"><a href="#tabs-1" onclick="loadTab('7121','tabs-1')">JGV</a></li>
    <li class="dk-tab tab"><a href="#tabs-1" onclick="loadTab('52','tabs-1')">DK-jgk-560</a></li>
    <li class="dk-tab tab"><a href="#tabs-1" onclick="loadTab('4668','jgk-720')">DK-jgk-720</a></li>
    <li class="dk-tab tab"><a href="#tabs-2" onclick="loadTab('54','tabs-2')">DK-24G-400</a></li>
    <li class="dk-tab tab"><a href="#tabs-3" onclick="loadTab('55','tabs-3')">DK-24G-560</a></li>
    <li class="dk-tab tab"><a href="#tabs-4" onclick="loadTab('56','tabs-4')">DK-24G-640</a></li>
    <li class="dk-tab tab"><a href="#tabs-5" onclick="loadTab('53','tabs-5')">DK-Guld-800</a></li>
    <li class="dk-tab tab"><a href="#tabs-5" onclick="loadTab('2395','tabs-20')">DK-Guld-1040</a></li>
    <li class="dk-tab tab"><a href="#tabs-10" onclick="loadTab('290','tabs-10')">DK-Dr&oslash;m-200</a></li>
    <li class="dk-tab tab"><a href="#tabs-11" onclick="loadTab('310','tabs-11')">DK-Dr&oslash;m-300</a></li>
    <li class="dk-tab tab"><a href="#tabs-12" onclick="loadTab('575','tabs-12')">DK-DESIGN-640</a></li>
    <li class="dk-tab tab"><a href="#tabs-12" onclick="loadTab('4662','DK-DESIGN-640')">DK-DESIGN-960</a></li>

    <li class="no-tab tab"><a href="#tabs-9" onclick="loadTab('574','tabs-9')">NO-GULL-1000</a></li>
    <li class="no-tab tab"><a href="#tabs-23" onclick="loadTab('2550','tabs-23')">NO-GULL-1200</a></li>
    <li class="no-tab tab"><a href="#tabs-32" onclick="loadTab('4740','tabs-32')">NO-GULL-2000</a></li>
    <li class="no-tab tab"><a href="#tabs-13" onclick="loadTab('272','tabs-13')">NO-Jgk-300</a></li>
    <li class="no-tab tab"><a href="#tabs-6" onclick="loadTab('57','tabs-6')">NO-Jgk-400</a></li>
    <li class="no-tab tab"><a href="#tabs-7" onclick="loadTab('58','tabs-7')">NO-Jgk-600</a></li>
    <li class="no-tab tab"><a href="#tabs-8" onclick="loadTab('59','tabs-8')">NO-Jgk-800</a></li>

    <li class="sv-tab tab"><a href="#tabs-31" onclick="loadTab('4793','tabs-31')">SE-300</a></li>
    <li class="sv-tab tab"><a href="#tabs-15" onclick="loadTab('1832','tabs-15')">SE-400 </a></li>
      <li class="sv-tab tab"><a href="#tabs-30" onclick="loadTab('1832_440','tabs-30')">SE-440</a></li>
      <li class="sv-tab tab"><a href="#tabs-24" onclick="loadTab('5117','tabs-24')">SE-600</a></li>
      <li class="sv-tab tab"><a href="#tabs-16" onclick="loadTab('1981','tabs-16')">SE-800</a></li>
      <li class="sv-tab tab"><a href="#tabs-50" onclick="loadTab('8271','tabs-50')">SE-som</a></li>

    <span id="sysmsg" style="margin-left:100px;"></span>


  </ul>
    <div id="tabs-15">Man kan nu se DB for de enkelte kort, dog ikke for fanen "Alle gavevalg"</div>
  <div class="sct" id="tabs-14"></div>
 <!--   <div class="sct" id="tabs-100"></div> -->
  <div class="sct" id="tabs-0"></div>
  <div class="sct" id="tabs-1"></div>
  <div class="sct" id="tabs-2"></div>
  <div class="sct" id="tabs-3"></div>
  <div class="sct" id="tabs-4"></div>
  <div class="sct" id="tabs-5"></div>
  <div class="sct"id="tabs-6"></div>
  <div  class="sct" id="tabs-7"></div>
  <div  class="sct" id="tabs-8"></div>

  <div class="sct" id="tabs-9"></div>
  <div class="sct" id="tabs-13"></div>
  <div class="sct" id="tabs-10"></div>
  <div class="sct" id="tabs-11"></div>
  <div class="sct" id="tabs-12"></div>
  <div class="sct" id="tabs-15"></div>
  <div class="sct" id="tabs-16"></div>
  <div class="sct" id="tabs-20"></div>


  <div class="sct"id="tabs-31"></div>
  <div class="sct"id="tabs-30"></div>
  <div class="sct"id="tabs-22"></div>
  <div class="sct" id="tabs-23"></div>
  <div class="sct" id="tabs-24"></div>
  <div class="sct" id="tabs-25"></div>
  <div class="sct" id="jgk-720"></div>
  <div class="sct" id="DK-DESIGN-640"></div>
  <div class="sct" id="tabs-32"></div>
  <div class="sct" id="SE-300"></div>
    <div class="sct" id="tabs-50"></div>


</div>
</body>
</html>

<?php die(); ?>