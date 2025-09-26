<?php
if (session_status() == PHP_SESSION_NONE) session_start();
if($_GET["token"] != "sdlfhiekhlsk23232948yruifkgfddsfgsdfghkwsjlzdsfae23f2hddddd" ){
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

    <script src="js/main.js"></script>
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
          $.post(_ajaxPath+"stats/getAllStats",{"shop_id":'52'}, function(data, textStatus) {
                $("#tabs-1").html(data)
                getStuckData()
          });


 }


 /*
  $( function() {


  } );
 */
var _shop_id = "52";
var _tab_id = "tabs-1";

function loadAllStat(shop_id,tab_id)
{
          $( "#sysmsg" ).html("")
          $("#"+tab_id).html("Systemet Arbejder")

          _tab_id = tab_id;
          _shop_id = shop_id;
          $.post(_ajaxPath+"stats/getAllStats",{"shop_id":shop_id}, function(data, textStatus) {
                $("#"+tab_id).html(data)
                getStuckData()
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
          $.post(_ajaxPath+"stats/getStats",{"deadline":$( "#deadline" ).val(),"shop_id":shop_id}, function(data, textStatus) {
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
    $.post(_ajaxPath+"stats/updateStats",postData, function(data, textStatus) {
             $( "#sysmsg" ).html("værdien: "+data+" er gemt")
    });
}

function getStuckData()
{
    var postData = {
        shopId:_shop_id,
        deadline:$( "#deadline" ).val(),
    }
    $.post(_ajaxPath+"stats/getStatsData",postData, function(data, textStatus) {
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

      --
    <br />
<br />
<select id="deadline" onchange="newDeadline() ">
  <option value="alle">alle</option>
  <option value="2017-11-05">2016-11-06</option>                    
  <option value="2017-11-19">2016-11-19</option>

  <option value="2019-01-01">2019-01-01</option>
</select>
<br />
<br />
<div id="tabs">
  <ul>
    <li><a href="#tabs-1" onclick="loadStat('52','tabs-1')">Julegavekortet</a></li>
    <li><a href="#tabs-2" onclick="loadStat('54','tabs-2')">24gaver - 400</a></li>
    <li><a href="#tabs-3" onclick="loadStat('55','tabs-3')">24gaver - 560</a></li>
    <li><a href="#tabs-4" onclick="loadStat('56','tabs-4')">24gaver - 640</a></li>
    <li><a href="#tabs-5" onclick="loadStat('53','tabs-5')">Guld</a></li>
    <li><a href="#tabs-6" onclick="loadStat('57','tabs-6')">Jgk - 400 - norge</a></li>
    <li><a href="#tabs-7" onclick="loadStat('58','tabs-7')">Jgk - 600 - norge</a></li>
    <li><a href="#tabs-8" onclick="loadStat('59','tabs-8')">Jgk - 800 - norge</a></li>
    <span id="sysmsg" style="margin-left:100px;"></span>


  </ul>
  <div id="tabs-1">

  </div>
  <div id="tabs-2">

  </div>
  <div id="tabs-3">

  </div>
  <div id="tabs-4">

  </div>
  <div id="tabs-5">

  </div>

  <div id="tabs-6">

  </div>
  <div id="tabs-7">

  </div>
  <div id="tabs-8">

  </div>



</div>
</body>
</html>