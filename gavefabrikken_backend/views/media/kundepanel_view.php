<?php

if(isset($_GET["fromback"])){
    echo "<a href=\"../gavefabrikken_backend/index.php?rt=mainaa&editShopID=".$_GET["shopId"]." \">Tilbage</a>";

}
$is_gf_user = "0";
if(isset($_GET["is_gf_user"])){
   $is_gf_user = "1";
}
$is_closed = "0";
if(isset($_GET["closed"])){
   $is_closed = "1";
}
$token = "";
if(isset($_GET["token"])){
   $token = $_GET["token"];
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
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />
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
  margin-top:30px;
    overflow-y: auto;
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
#sysMsg{
  color:red !important;
}
.notReady{
    opacity: 0.5;
    filter: alpha(opacity=50); /* For IE8 and earlier */
}
#htmlpagenation{
    position: absolute;
    top:50px;
    background-color: #66A3FF;
    padding:5px;
    width: 95%;

}
.sortColl:hover {
  text-decoration: underline;
  font-size: 14px;
}
.sortCollSelected{
    text-decoration: underline;
  font-size: 14px;
}
::placeholder {
  color: green;
}

.force-opaque::placeholder {
  opacity: 1;
}
</style>


 <script src="views/js/gavevalgExt.js?v=<?php echo rand();  ?>"></script>
  <script src="views/js/gavevalgExtQR.js?v=<?php echo rand();  ?>"></script>


<script>
var _editShopID = "<?php echo $_GET["shopId"];  ?>";
var _is_gf_user = "<?php echo $is_gf_user;  ?>";
var _is_closed = "<?php echo $is_closed ;  ?>";
var _token = "<?php echo $token;  ?>";
var _sendMailOnsave = true;
var _sendMail = true;
var _sysMsgTimer;


 $(function() {
     $( "#tabs" ).tabs();
    gavevalg.init();
    var h = $( document ).height();
    $("#gavevalgContainer").height(h-180);
    $( window ).resize(function() {
        var h = $( document ).height();
        $("#gavevalgContainer").height(h-180);
    });
    if(_editShopID == "65"){
        $(".levering_rap").show();
    }
    /* ======= settings init ================ */
 if(_editShopID == "2258"){
        $(".usacc").show();
    }



})

function buildPie(data)
{
     console.log(data)
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
   clearTimeout(_sysMsgTimer);
   $("#sysMsg").html(txt);
   $("#sysMsg").show()

   _sysMsgTimer = setTimeout(function(){
     $("#sysMsg").fadeOut()
   }, 3000);
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
/*
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
*/
$(document).keypress(function (e) {
    if (e.which == 13) {
        doSog()
    }
});
function doSog()
{
    $("#sysMsg").html("<span>Søger</span>");
    $("#sysMsg").show();
    var sog = $("#kundepanelSog").val();
    sogStr = sog.toLowerCase()
    gavevalg.sog(sogStr)
    //setTimeout(gavevalg.sog, 500)



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
       $("#kundepanelOptionBtn").show();

}
function udleveringsrapport()
{
  window.open('../gavefabrikken_backend/index.php?rt=registrer/pullapphistory&companyid=95&shopid=65', '_blank');
}
function shopKundepanelOptions(){
     $("#kundepanelIndstillinger").slideToggle( "slow", function() {
            $("#gavevalgContainer").fadeToggle( "fast", function() {

            });
  });
  qrLoadUser();
}
/*============== settings ================ */
function mailsettings(id){
  var postData = {
    shop_id:_editShopID,
    kundepanel_email_regel:id
  }
  if(id == 0){

          _sendMailOnsave = true;
          _sendMail = true;
  }
  if(id == 1){

          _sendMailOnsave = false;
          _sendMail = true;
  }
  if(id == 2){

          _sendMailOnsave = false;
          _sendMail = false;
  }


  ajax(postData,"shop/setKundepanelEmailRegel","mailsettigsResponse");
}
function mailsettigsResponse(response){
    showSysMsg("Mails indstillinger ændret")
}


function qrsettings(element){
    if($(element).attr("id") == "startRegistration"){
                 if( $('#startRegistration').is(":checked") ){
                       ajax({"shop_id":_editShopID},"shop/openForRegistration","msgOpenReg");
                 } else {
                      ajax({"shop_id":_editShopID},"shop/closeForRegistration","msgCloseReg");
                 }
    }
    if($(element).attr("id") == "allwaysShowRegistration"){
                 if( $('#allwaysShowRegistration').is(":checked") ){
                       ajax({"shop_id":_editShopID,"qrMenuSettings":"1"},"shop/setQrMenuSettings","QrMenuSettingsOpen");
                 } else {
                   ajax({"shop_id":_editShopID,"qrMenuSettings":"0"},"shop/setQrMenuSettings","QrMenuSettingsClose");
                 }
    }
}

function QrMenuSettingsOpen(){
        showSysMsg("Menuen for gaveudlevering med QR kode vises altid")
            $(".qr_admin").show();
}
function QrMenuSettingsClose(){
     showSysMsg("Menuen for gaveudlevering med QR kode skjules")
            $(".qr_admin").hide();
}


function msgOpenReg(){
            showSysMsg("Åben for gaveudlevering med QR kode")
            $(".qr_admin").show();
}

function msgCloseReg(){
            showSysMsg("Lukket for gaveudlevering med QR kode")
            $(".qr_admin").hide();
}

function initSettings(response){
     $("#sysMsg").hide();
     console.log(response.data.attributes.kundepanel_email_regel )

    if(response.status == "1"){
      console.log(response.data.attributes)
        if(response.data.attributes.localisation == "5"){
         //   $(".reminderMail").hide();
            $(".registration").parent().hide();
        }


       if(response.data.attributes.kundepanel_email_regel == "0") {
         $("#allwaysmail").prop("checked", true);
         _sendMailOnsave = true;
         _sendMail = true;
       }
       if(response.data.attributes.kundepanel_email_regel == "1") {
         $("#mailIsgift").prop("checked", true);
             _sendMailOnsave = false;
             _sendMail = true;
       }
       if(response.data.attributes.kundepanel_email_regel == "2") {
         $("#noMails").prop("checked", true);
             _sendMailOnsave = false;
             _sendMail = false;
       }

       if(response.data.attributes.qr_menu_settings == "1"){
            $("#allwaysShowRegistration").prop("checked", true);
            $(".qr_admin").show();
       } else {
            $("#allwaysShowRegistration").prop("checked", false);
            $(".qr_admin").hide();
       }

       if(response.data.attributes.open_for_registration == "1"){

            $("#startRegistration").prop("checked", true);
            $(".qr_admin").show();
       } else {
            $("#startRegistration").prop("checked", false);
            if(response.data.attributes.qr_menu_settings == "0"){
                $(".qr_admin").hide();
            }
       }

    }  else {
        alert("Der er sket en fejl!")
    }
}
// QR useradmin


function qrCreateNew()
{
        $(".newQrUSer").css("background-color", "white")
        if($("#qrName").val() == "" || $("#qrPassword").val() == "" || $("#qrPassword").val() == ""){
            $("#qrName").val() == "" ? $("#qrName").css("background-color", "red") : "";
            $("#qrUsername").val() == "" ? $("#qrUsername").css("background-color", "red") : "";
            $("#qrPassword").val() == "" ? $("#qrPassword").css("background-color", "red") : "";
            alert("You must fill in all the fields")
            return;
        }


        let postData = {
          "name":$("#qrName").val(),
          "username":$("#qrUsername").val(),
          "password":$("#qrPassword").val(),
          "shopID":_editShopID
        }
          $.ajax(
            {
            url: 'index.php?rt=kundepanel/qrNewUser',
            type: 'POST',
            dataType: 'json',
            data: postData
            }
          ).done(function(res) {
              if(res.status == 0){
                alert("Username is taken, please use email to create unique username")
                return;
              }
              $("#qrName").val(""),
              $("#qrUsername").val(""),
              $("#qrPassword").val(""),
              $(".qrUser").remove();
              alert("New QR-user created")
              qrLoadUser()
            }
          )
}
function qrLoadUser(){
        let postData = { "shopID":_editShopID };
        $.ajax(
            {
            url: 'index.php?rt=kundepanel/qrReadUser',
            type: 'POST',
            dataType: 'json',
            data: postData
            }
          ).done(function(res) {
              buildQrTable(res)
            }
          )
}
function qrUpdateUser(ele){
    let parentEle = $(ele).parents("tr");
    $(".newQrUSer").css("background-color", "white")
    if($(parentEle).children('td').eq(0).find("input").val() == "" || $(parentEle).children('td').eq(1).find("input").val() == "" || $(parentEle).children('td').eq(2).find("input").val() == ""){
        $(parentEle).children('td').eq(0).find("input").val() == "" ? $(parentEle).children('td').eq(0).find("input").css("background-color", "red") : "";
        $(parentEle).children('td').eq(1).find("input").val() == "" ? $(parentEle).children('td').eq(1).find("input").css("background-color", "red") : "";
        $(parentEle).children('td').eq(2).find("input").val() == "" ? $(parentEle).children('td').eq(2).find("input").css("background-color", "red") : "";
        alert("You must fill in all the fields")
        return;
    }

    let postData = {
      "name":$(parentEle).children('td').eq(0).find("input").val(),
      "username":$(parentEle).children('td').eq(1).find("input").val(),
      "password":$(parentEle).children('td').eq(2).find("input").val(),
      "ID":$(parentEle).attr("qr-user"),
      "shopID":$(parentEle).attr("shop-id")
    }
    $.ajax(
        {
        url: 'index.php?rt=kundepanel/qrUpdate',
        type: 'POST',
        dataType: 'json',
        data: postData
        }
      ).done(function(res) {
          if(res.status == 0){
           alert("There was an error, QR-user is not updated")
           return;
          }
          $(".qrUser").remove();
          alert("QR-user updated")
          qrLoadUser()
        }
      )

}
function qrDeleteUser(ele){
    let parentEle = $(ele).parents("tr");
    if(!confirm("Delete qr-user:"+$(parentEle).children('td').eq(0).find("input").val())) return
    let postData = {
      "ID":$(parentEle).attr("qr-user"),
      "shopID":$(parentEle).attr("shop-id")
    }
    $.ajax(
        {
        url: 'index.php?rt=kundepanel/qrDelete',
        type: 'POST',
        dataType: 'json',
        data: postData
        }
      ).done(function(res) {
          if(res.status == 0){
           alert("There was an error, QR-user is not deleted")
           return;
          }
          $(".qrUser").remove();
          alert("QR-user deleted")
          qrLoadUser()
        }
      )
}



function buildQrTable(data){
    $(".qrUser").remove();
    let html = data.map((i) => {
           return `
             <tr class='qrUser' shop-id='${i.shop_id}' qr-user='${i.id}'>
                <td><input  value='${i.name}' type='text' width='100%' class='qrName' /></td>
                <td><input  value='${i.username}' type='text' width='100%'  /></td>
                <td><input  value='${i.password}' type='text' width='100%' /></td>
                <td> <div style='width:65px'>
                    <img style='display: inline-block; margin-right:5px; cursor: pointer;' width='20' height='20' src='views/media/icon/1373253284_save_64.png' title='Opdatere bruger' onclick='qrUpdateUser(this)'>
                    <img style='display: inline-block; cursor: pointer;' width='20' height='20' src='views/media/icon/1373253296_delete_64.png' title='Fjern bruger' onclick='qrDeleteUser(this)'>
                    </div>
                </td>
             </tr>
           `
        }).join('');
        $("#qrTable").append(html);
         console.log(html)


}


 if($( ".gaveudlevering" ).hasClass( "levering_not_active" ) ){
        ajax({"shop_id":_editShopID},"shop/openForRegistration","openForRegistrationResponse");
         $(".qr_admin").show();
    } else {
          $(".qr_admin").hide();
   }



</script>


</head>

<body>
<div id="tabs">
  <ul>
    <li><a href="#tabs-1" onclick="gavevalg.init()">Statistik</a></li>
    <li><a href="#tabs-2" onclick="sogInit()">Administration</a></li>
    <span style="margin-left:50px; margin-top:3px; float:left;" ><input id="kundepanelSog" type="test" style="width: 200px;" /><button onclick="doSog()">Søg</button> </span><span id="pagenation"></span><span style="margin-left:50px; float:left;" id="sysMsg">Indlæser</span><img id="kundepanelOptionBtn" onclick="shopKundepanelOptions()" style="float: right; margin-right: 20px; cursor: pointer; display: none;" width=30 src="views/media/icon/gear_2-512.png" alt="" />
  </ul>
  <div id="tabs-1" >
  <br> <br> <br>
    <div id="chart9" style="width: 800px; height: 500px;"  ></div>
  </div>
  <div id="tabs-2">
  <div  id="kundepanelIndstillinger" style="display: none; font-size: 12px; ">


       <button  style="float: right; margin-right: 50px; cursor: pointer;" onclick="shopKundepanelOptions()">Luk</button>
       <br><br>
    <fieldset>

    <legend><b>Indstillinger:</b></legend>


           <fieldset>
            <legend >Gaveudlevering via scanning af QR kode på kvitteringerne :</legend>
             <table width=600>
             <tr><td><input type="checkbox" onclick="qrsettings(this)" class="registration" id="startRegistration"  ></td><td>Start gaveudlevering med QR kode scanning</td></tr>
             <tr ><td><input type="checkbox" onclick="qrsettings(this)" class="registration" id="allwaysShowRegistration" ></td><td>Vis altid panel med gaveudlevering, selvom gaveudlevering er stoppet</td></tr>

             </table>
            </fieldset>

  <br>


     <fieldset >
            <legend  >Afsendelse af kvitteringsmail ved ændring af stamdata:</legend>
              <table  width=600>

             <tr><td> <input type="radio"  onclick="mailsettings(0)" id="allwaysmail" name="kundepanelMail" /></td><td>Send altid en kvittering til brugeren, ved alle rettelser:</td></tr>
             <tr><td> <input type="radio"  onclick="mailsettings(1)" id="mailIsgift" name="kundepanelMail" /></td><td>Send kun en kvittering til brugeren, når gavevalg ændres:</td></tr>
             <tr><td> <input type="radio"  onclick="mailsettings(2)" id="noMails" name="kundepanelMail" /></td><td>Send <u><b>aldrig</b></u> en mail til brugeren</td></tr>

             </table>
            </fieldset>

    </fieldset>
    <br>
     <fieldset >
            <legend  >QR-udlevering brugeradministration</legend>
              <table  width=600 id="qrTable">
              <tr><th >Name</th><th>Username</th><th>Password</th><th></th></tr>
              <tr><td><input class="newQrUSer" type="text" width="100%" id="qrName" /></td>
              <td><input class="newQrUSer" id="qrUsername" type="text" width="100%" placeholder='Please use email' /></td>
              <td><input class="newQrUSer" id="qrPassword" type="text" width="100%" /></td><td width=60><img style=" cursor: pointer;" width="20" height="20" src="views/media/icon/1373253494_plus_64.png" title="Opret ny bruger" onclick="qrCreateNew()" ></td></tr>
             </table>
            </fieldset>
    </fieldset>
    <br><br>
  </div>

     <!--
      <table>
      <tr>
      <td>    <button class="levering_not_active gaveudlevering" onclick="registration()">Aktiver gaveudlevering</button></td>
      <td>    <button class="levering_rap" style="display:none;"  onclick="udleveringsrapport()">Download udleveringsrapport</button></td>
      </tr>

      </table>
       -->

    <?php include("gavevalg_view.php");  ?>
  </div>


</div>
<div style="display: none;" id="kundeDialog"></div>


</body>
</html>