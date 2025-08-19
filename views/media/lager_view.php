<?php
if(isset($_GET["login"])){
  if($_GET["login"] != "dsfkjsadhferuifghriuejf3434fhsudif"){
      echo "Ingen adgang";
       die();
  }

} else {
    echo "ingen adgang";
    die();
}

?>

<!DOCTYPE html>

<html>

<head>
  <title>GF - LAGER</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>


<style>
body {
    padding:0px;
    margin:0px;
    width: 100%;
    height: 100%;
  	font-family: "Helvetica Neue", Helvetica, sans-serif;

    font-size: 1em;
}
input[type=checkbox] {
  transform: scale(1.5);
}
.onlyPrint{
  display: none;
}
.button {
    background-color: #4CAF50; /* Green */
    border: none;
    color: black;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    cursor: pointer;
}
.button2 {
    background-color: #FFFF33 /* Green */
    border: none;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    cursor: pointer;
}

.button:hover  {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}
@media print {
    .footer {page-break-after: always;}
    #mainContainer{
      border:1px white solid;
    }
    .notInPrint{
      display: none;
    }
    .onlyPrint{
      display: block;
    }

}
@page {
    size: auto;   /* auto is the initial value */
    margin: 0;  /* this affects the margin in the printer settings */
}
</style>

<script src="views/lib/jquery.min.js"></script>
<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<link href="views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">
<script src="views/js/main.js"></script>
<script src="views/js/lager.js"></script>

<script>
$( document ).ready(function() {
    lager.loadCompanyList();

      $( "#bizType" ).buttonset();


});

function viskort(kort){
    $(".lagerMenu").hide();

    if(kort == "jgk"){
        $(".jgk").show();
    }
    if(kort == "24gaver"){
        $(".24gaver").show();
    }
    if(kort == "guld"){
        $(".guld").show();
    }
    if(kort == "norge"){
        $(".norge").show();
    }

}

function showAll()
{
      $("#mainContainer").html("<h1>Henter alle printede eller godkendte</h1>");  
    ajax({},"company/getCompanyOrdersAll","showAllResponce","");
}
function showAllResponce(response)
{
      var  html = "";
              var  deal = ["11563392","32479081","31299004","31480469","56000828","29190909","32023908","12445040","21578894","33612192","29190909","47289114","31178266","12445040","25229002"];
     $.each( response.data.companyorders, function( key, value ) {

            var deleveryWeek = "";

            if(value.expire_date == "2016-10-28"){ deleveryWeek = "Uge 48"; }
            if(value.expire_date == "2016-11-06"){ deleveryWeek = "Uge 48"; }

            if(value.expire_date == "2016-11-11"){ deleveryWeek = "Uge 50"; }
            if(value.expire_date == "2016-11-20"){ deleveryWeek = "Uge 50"; }

            if(value.expire_date == "2016-12-31"){ deleveryWeek = "Uge 4 (2017)"; }
            if(value.expire_date == "2018-01-01"){ deleveryWeek = "2018-01-01"; }





            if(value.shop_name == "Julegavekortet.dk"){
                html+= "<div id=\"post_"+value.id+"\" class=\"lagerMenu\"><div class=\"onlyPrint\" style=\"width:350px; height:100px;\"></div><table width=600 border=0>"
            }
            if(value.shop_name.indexOf("24") > -1 ){
                html+= "<div id=\"post_"+value.id+"\" class=\"lagerMenu\" ><div class=\"onlyPrint\" style=\"width:350px; height:100px;\"></div><table width=600 border=0>"
            }
            if(value.shop_name.indexOf("uld") > -1 ){
                html+= "<div id=\"post_"+value.id+"\" class=\"lagerMenu\" ><div class=\"onlyPrint\" style=\"width:350px; height:100px;\"></div><table width=600 border=0>"
            }
            if(value.shop_name.indexOf(".no") > -1 ){
                html+= "<div id=\"post_"+value.id+"\" class=\"lagerMenu\" ><div class=\"onlyPrint\" style=\"width:350px; height:100px;\"></div><table width=600 border=0>"
            }






            html+= "<u><h3>"+value.shop_name+"</h3></u>";
            if(value.shop_name == "24gaver.dk"){
                   html+= "<h4>Gavekort DKK. "+value.certificate_value+",-</h4>";
            }
            if(value.shop_name.indexOf(".no") > -1){
                   html+= "<h4>Gavekort  "+value.certificate_value+",-</h4>";
            }
            html+= "<b>"+deleveryWeek+"</b>";

            html+= "<div id=\"post_"+value.id+"\"><div class=\"onlyPrint\" style=\"width:100px; height:100px;\"></div><table width=600 border=0>"
            if(value.is_printed == "1"){
                html+="<tr class=\"notInPrint\" > <td width=250></td> <td width=250></td> <td><input class=\"actionCheckbox\" id=\""+value.id+"\" type=\"checkbox\" /> <label  id=\"status_"+value.id+"\">Printet</label></td> </tr>";
            } else {
                html+="<tr class=\"notInPrint\" > <td width=250></td> <td width=250></td> <td><input class=\"actionCheckbox\" id=\""+value.id+"\" type=\"checkbox\" /> <label  id=\"status_"+value.id+"\"/label> </td> </tr>";
            }

            html+="<tr> <td><b>Ordernr.:</b></td> <td><b>"+value.order_no+"</b></td> <td></td> </tr>";
            if(value.is_cancelled == "1"){
                html+="<tr> <td><h1 style=\"color:red\">ANNULERET</h1></td><td><h1 style=\"color:red\">ANNULERET</h1></td><td><h1 style=\"color:red\">ANNULERET</h1></td> </tr>";
            } else if(deal.indexOf(value.cvr) > -1){
                html+="<tr> <td><h1 style=\"color:red\">SPECIAL</h1></td><td><h1 style=\"color:red\">AFTALE</h1></td><td><h1 style=\"color:red\">IKKE SEND</h1></td> </tr>";
            } else{
                html+="<tr> <td></td> <td><br /></td> <td></td> </tr>";
            }

            html+="<tr> <td>Virksomhed:</td> <td>"+value.company_name+"</td> <td></td> </tr>";
            html+="<tr> <td>Cvr:</td> <td>"+value.cvr+"</td> <td></td> </tr>";
            html+="<tr> <td>Vej:</td> <td>"+value.ship_to_address+"</td> <td></td> </tr>";
            html+="<tr> <td>Postnummer:</td> <td>"+value.ship_to_postal_code+"</td> <td></td> </tr>";
            html+="<tr> <td>By:</td> <td>"+value.ship_to_city+"</td> <td></td> </tr>";
            html+="<tr> <td></td> <td><br /></td> <td></td> </tr>";
            html+="<tr> <td>Kontaktperson:</td> <td>"+value.contact_name+"</td> <td></td> </tr>";
            html+="<tr> <td>E-mail:</td> <td>"+value.contact_email+"</td> <td></td> </tr>";
            html+="<tr> <td>Tlf.nr.:</td> <td>"+value.contact_phone+"</td> <td></td> </tr>";
            html+= "</table>";
            html+="<br /><table  width=600 border=0>";
            html+="<tr> <td width=200>Gavekort</td> <td width=200>Start</td> <td width=200>Slut</td> <td></td>  </tr>";
            html+="<tr> <td>Antal: "+value.quantity+"</td> <td>"+value.certificate_no_begin+"</td> <td>"+value.certificate_no_end+"</td> </tr>";
            html+="</table><div class=\"footer\"></div><hr /></div>";

      });
      $("#mainContainer").html(html);
}


 function goToGaveAdmin()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/shopMain#gave";
 }
function goToSystem()
{
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/shopMain#system";
}
function goToValgshop()
{
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/shopMain";
}
function goToGavekort()
{
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/cardShop&token=asdf43sdha4f34o";

}

 function showArkiv()
 {
       window.open("<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/showArkiv&token=dsfkjsadhferuifghsdfssudif", "_blank");
 }




</script>

</head>

<body>
<center>

<div class="notInPrint" id="total"></div>

<br />     <br />  <br />
<div class="notInPrint" style="width: 1000px; height:40px; text-align:right;">
<table width=800 >
<tr>
<td colspan="4"><button class="button2"  onclick="showAll()" style="color:black;" >Vis alle som er printet eller sendt</button></td>
</tr>
<tr>
<td><button class="button"  onclick="viskort('jgk')" >Vis Julegavekort</button></td>
<td><button class="button"  onclick="viskort('24gaver')" >Vis 24Gaver</button></td>
<td><button class="button"  onclick="viskort('guld')" >Vis Guldgavekortet</button></td>
<td><button class="button"  onclick="viskort('norge')" >Vis NORGE</button></td>
<td width=100></td>
<td></td>
<td><button onclick="lager.printIt()">PRINT</button></td>
<td><button onclick="lager.shipIt()">UDLEVERET</button></td>
<td><button onclick="lager.loadCompanyList()">OPDATERE</button></td>
<td><button onclick="lager.selectAll()">VÆLG ALLE</button></td>
</tr>

</table>

</div><br /> <br /> <br />
<hr  class="notInPrint" style="border: 2px red solid;" />
<div id="mainContainer" style="width: 800px; border:1px black solid; ">


</div>


</center>


</body>

</html>