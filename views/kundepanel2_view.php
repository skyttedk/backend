<?php
if(isset($_GET["fromback"])){
echo "<a href=\"../gavefabrikken_backend/index.php?rt=mainaa&editShopID=".$_GET["shopId"]." \">Tilbage</a>";

}


  ?>

<!DOCTYPE html>

<html>

<head>
  <title>Kundepanel</title>
   <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<script src="views/lib/jquery.min.js"></script>
<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<link href="views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">
<script src="views/js/main.js"></script>

<script language="javascript" type="text/javascript" src="views/lib/jqplot/jquery.jqplot.min.js"></script>
<script language="javascript" type="text/javascript" src="views/lib/jqplot/plugins/jqplot.pieRenderer.js "></script>
<script src="http://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<link href="http://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="views/lib/jqplot/jquery.jqplot.css" />
 <link type="text/css" rel="stylesheet" href="syntaxhighlighter/styles/shCoreDefault.min.css" />
    <link type="text/css" rel="stylesheet" href="syntaxhighlighter/styles/shThemejqPlot.min.css" />

<style>
body{
    font-family: "Trebuchet MS", Helvetica, sans-serif;
    font-size: 12px;
}
.qr_admin1{
    display: none;
}
.ba_admin1{
    display: none;

}
#gavevalgContainer{

}
.levering_rap {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 5px;
    cursor: pointer;
}


.levering_active {
    background-color: #f44336; /* Green */
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 5px;
    cursor: pointer;
}
.levering_not_active {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 5px;
    cursor: pointer;
}
.levering_rap:hover {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}
.levering_not_active:hover {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}
.levering_active:hover {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}


#userTable{
font-size: 12px;

}









</style>


 <script src="views/js/gavevalgExt_Datatables.js"></script>
  <script src="views/js/gavevalgExtQR.js"></script>


<script>
var _editShopID = "<?php echo $_GET["shopId"];  ?>";
var _calcHeight = 0;
 $(function() {
     $( "#tabs" ).tabs();
    var h = $( document ).height();
    _calcHeight = h-330;
    $("#gavevalgContainer").height(h-200);
    gavevalg.init();

  /*
    $( window ).resize(function() {
        var h = $( document ).height();
        $("#gavevalgContainer").height(h-280);
    });
    if(_editShopID == "65"){
        $(".levering_rap").show();
    }
   */




})

function buildPie(data)
{
     jQuery.jqplot.config.enablePlugins = true;
     plot9 = jQuery.jqplot('chart9',
    data ,
    {
      title: ' ',
      seriesDefaults: {shadow: true, renderer: jQuery.jqplot.PieRenderer, rendererOptions: {
          fill: true,
          sliceMargin: 4,
          showDataLabels: true
          }
      },
      legend: { show:true }
    }
  );
}
function showSysMsg(txt)
{
   $("#sysMsg").html(txt);
   $("#sysMsg").fadeIn(3000,function(){
        $("#sysMsg").fadeOut(1500);
   });
}
function kundepanel_show_data(){

    $("input").attr('readonly', false);
    $("#kundepanelSog").attr('readonly', false);
    $(".qr_admin").hide()
    $(".ba_admin").show()
    $(".gaveudlevering").hide()


}
function kundepanel_show_udlevering(){
    $("input").attr('readonly', true);
    $("#kundepanelSog").attr('readonly', false);
    $(".ba_admin").hide()
    $(".qr_admin").show()
    $(".gaveudlevering").show()

}

function registration(){
    if($( ".gaveudlevering" ).hasClass( "levering_not_active" ) ){
        ajax({"shop_id":_editShopID},"shop/openForRegistration","openForRegistrationResponse");
         $(".qr_admin").show();
    } else {
        ajax({"shop_id":_editShopID},"shop/closeForRegistration","closeForRegistrationResponse");

          $(".qr_admin").hide();
    }
}

function closeForRegistrationResponse(){
   $( ".gaveudlevering" ).removeClass("levering_active");
   $( ".gaveudlevering" ).addClass("levering_not_active");
   $( ".gaveudlevering" ).html("Aktiver gaveudlevering");

}
function openForRegistrationResponse(){
   $( ".gaveudlevering" ).removeClass("levering_not_active");
   $( ".gaveudlevering" ).addClass("levering_active");
   $( ".gaveudlevering" ).html("Stop gaveudlevering");
}
function initRegistration(response){
    if(response.status == "1"){
         $("#sysMsg").hide();
        if(response.data.open_for_registration == "0"){
            closeForRegistrationResponse();
             $(".qr_admin").hide();
       } else {
           openForRegistrationResponse();
            $(".qr_admin").show();
       }


    }  else {
        alert("Der er sket en fejl!")
    }
}
function doSog()
{
    $("#sysMsg").html("<span>Søger</span>");
    $("#sysMsg").show();
    setTimeout(doGoSog, 500)



}
function doGoSog()
{
    var sog = $("#kundepanelSog").val();
    $( ".gavevalg  tr" ).show();
        var hit;
        if(sog != ""){
            sog = sog.toLowerCase()
            $( ".gavevalg  tr" ).each(function( index,eleTr ) {
            if(index > 0){
              hit = false;
              $( eleTr ).find("input").each(function( index2,eleTr2 ) {
                  if(hit != true){
                      var str = $(eleTr2).val();
                      str = str.toLowerCase();
                      if(str.indexOf( sog ) != -1){
                          console.log(str)
                          hit = true;
                      }
                  }
              })
              if(hit == false){
                 $(eleTr).hide();
              }
            }
        });
    }
       $("#sysMsg").hide();
}

function sogInit(){
     $("#kundepanelSog").val("") ;


}
function udleveringsrapport()
{
  window.open('../gavefabrikken_backend/index.php?rt=registrer/pullapphistory&companyid=95&shopid=65', '_blank');
}

function hideThings()
{
   $("#userTable_length").hide();
}

</script>


</head>

<body>

<div id="tabs">
  <ul>
<!--    <li><a href="#tabs-1" onclick="gavevalg.init()">Statistik</a></li> -->
    <li><a href="#tabs-1" >Statistik</a></li>
    <li><a href="#tabs-2" onclick="sogInit()">Administration</a></li>


    <span style="margin-left:50px; float:left;" id="sysMsg">Indlæser</span>
  </ul>
  <div id="tabs-1" >
    <div id="chart9" style="width: 800px; height: 500px;"  ></div>
  </div>
  <div id="tabs-2">
      <table>
      <tr>
      <td>    <button class="levering_not_active gaveudlevering" onclick="registration()">Aktiver gaveudlevering</button></td>
      <td>    <button class="levering_rap" style="display:none;"  onclick="udleveringsrapport()">Download udleveringsrapport</button></td>
      </tr>

      </table>


    <?php include("gavevalg_view.php");  ?>
  </div>


</div>
<div style="display: none;" id="kundeDialog"></div>


</body>
</html>