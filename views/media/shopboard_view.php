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
  <title>GF - SHOP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <link href="views/lib/jquery-ui/jquery-ui.css" rel="stylesheet" />
  <link href="http://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />
<style>



#dialog-shopboard{
    font-size: 12px;

}

#shopboard_tabs{
    font-size: 10px;

}
#shopboard_tabs a{
 color: black;
 }
	#dialog-shopboard{
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;

	}
	#dialog-shopboard th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
	#dialog-shopboard td {
		border:1px solid #C0C0C0;
		padding:5px;
	}
    #dialog-shopboard input, textarea, select{
      width: 100%;
    }
    .shopboard-container{
      overflow: scroll;
      width: 100%;
    }
	.shopboard-container {
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;

	}
	.shopboard-container th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
	.shopboard-container td {
		border:1px solid #C0C0C0;
		padding:5px;
	}
    #table-status th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
    #table-status td {
		border:1px solid #C0C0C0;
		padding:5px;
	}
    #table-status{
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;

	}


.rotate {
 font-weight:normal;
 font-size:12px;
 height:70px;

/* Safari */
-webkit-transform: rotate(-40deg);

/* Firefox */
-moz-transform: rotate(-40deg);

/* IE */
-ms-transform: rotate(-40deg);

/* Opera */
-o-transform: rotate(-40deg);

/* Internet Explorer */
filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=1);

}
#tabs-9 .faneColor1{  background-color: #B8B894; }
#tabs-9 .faneColor2{  background-color: #FFD11A; }
#tabs-9 .faneColor3{  background-color: #FFFF33; }
#tabs-9 .faneColor4{  background-color: #66FF33; }
#tabs-9 .faneColor5{  background-color: #3399FF; }
#tabs-9 .faneColor6{  background-color: #D147A3; }
#tabs-9 .faneColor7{  background-color: #009999; }
#tabs-9 .faneColor8{  background-color: #FF3333; }


#shopboardTable .faneColor1{  background-color: #B8B894; }
#shopboardTable .faneColor2{  background-color: #FFD11A; }
#shopboardTable .faneColor3{  background-color: #FFFF33; }
#shopboardTable .faneColor4{  background-color: #66FF33; }
#shopboardTable .faneColor5{  background-color: #3399FF; }
#shopboardTable .faneColor6{  background-color: #D147A3; }
#shopboardTable .faneColor7{  background-color: #009999; }
#shopboardTable .faneColor8{  background-color: #FF3333; }

.shopboard-switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.shopboard-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.shopboard-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.shopboard-slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .shopboard-slider {
  background-color: #2196F3;
}

input:focus + .shopboard-slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .shopboard-slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.shopboard-slider.shopboard-round {
  border-radius: 34px;
}

.shopboard-slider.shopboard-round:before {
  border-radius: 50%;
}


</style>
<script src="views/lib/jquery.min.js"></script>
<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<script src="thirdparty/table/jquery.dynatable.js"></script>
<script src="views/js/main.js"></script>
<?php
  echo "<script src='views/js/shopboard.js?v='".rand(10,100)."'></script>";
?>
<script src="http://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>


<script>

var tableHeight;
$( document ).ready(function() {

    controlHeight()
    shopboard.init();
    $( "#shopboard_tabs" ).find("a").click(function() {
        $( "#shopboard_tabs" ).find("a").css("font-size","12px");
        $(this).css("font-size","15px");
    });
                   jQuery.extend( jQuery.fn.dataTableExt.oSort, {

    "date-uk-pre": function ( a ) {
       alert("safdasdf")
        if (a == null || a == "") {
            return 0;
        }
        var ukDatea = a.split('-');
        return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
    },

    "date-uk-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },

    "date-uk-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
} );
});

function controlHeight(){
  var docHeight = $( document ).height();
  $("#shopboard_tabs").height(docHeight-50);
  tableHeight = docHeight-280
}
$( window ).resize(function() {
  controlHeight();

})

</script>
 </head>

<body>
<div id="shopboard_tabs" >

 <ul >
    <li ><a style="background-color: #B8B894;font-size: 15px; " href="#tabs-1" onclick="shopboard.loadTabsData('1')">Nye</a></li>
    <li><a style="background-color: #FFD11A;" href="#tabs-2" onclick="shopboard.loadTabsData('2')">Demo</a></li>
    <li><a  style="background-color: #FFFF33;" href="#tabs-3" onclick="shopboard.loadTabsData('3')">Afventer info</a></li>
    <li><a  style="background-color: #66FF33;" href="#tabs-4" onclick="shopboard.loadTabsData('4')">Shop i gang</a></li>
    <li><a  style="background-color: #3399FF;" href="#tabs-5" onclick="shopboard.loadTabsData('5')">Shop lukket</a></li>
    <li><a  style="background-color: #D147A3;" href="#tabs-6" onclick="shopboard.loadTabsData('6')">Fordelingsliste</a></li>
    <li><a  style="background-color: #009999;" href="#tabs-7" onclick="shopboard.loadTabsData('7')">Indkøb</a></li>
    <li><a  style="background-color: #FF3333;" href="#tabs-8" onclick="shopboard.loadTabsData('8')">Shop afsluttet</a></li>
    <li><a  style="background-color: #33FFAD;" href="#tabs-9" onclick="shopboard.loadTabsData('alle')">Alle</a></li>
    <div style="display: inline-block; margin-left:200px;" >        <img style="cursor: pointer;" onclick="shopboard.addNew()"  src="views/media/icon/1373253494_plus_64.png" height="30" alt="" /></div>
    <div style="width: 100px;display: inline-block; margin-left:20px; font-size: 14px; "><select id="user" onchange="shopboard.changeUser()">
         <option value='alle'>Alle</option> <option value='KT'>KT</option> <option value='CLE'>CLE</option>             <option value='RTT'>RTT</option>            <option value='JHC'>JHC</option>  <option value='ADU'>ADU</option>    <option value='SLY'>SLY</option>
            </select> </div>
    <img style="float: right; margin-right: 50px;cursor: pointer;" src="views/media/icon/excel.png " height="30"  alt="" onclick="shopboard.csv()" />
    <img style="float: right; margin-right: 50px;cursor: pointer;" src="views/media/icon/16278993-update-icon-glossy-orange-button.jpg" height="30"  alt="" onclick="shopboard.updateTable()" />

  </ul>
  <div id="tabs-1" class="statusTabs">

  </div>
  <div id="tabs-2" class="statusTabs">
  </div>
  <div id="tabs-3"  class="statusTabs">
  </div>
  <div id="tabs-4"  class="statusTabs">
  </div>
  <div id="tabs-5"  class="statusTabs">
  </div>
  <div id="tabs-6"  class="statusTabs">
  </div>
  <div id="tabs-7"  class="statusTabs">
  </div>
  <div id="tabs-8"  class="statusTabs">
  </div>
  <div id="tabs-9"  class="statusTabs">
  </div>
</div>




<!-- medal -->
<div id="dialog-shopboard" title="Shopboard" style="display: none; ">
<table width="560"  >

<tr>
    <td>shop navn</td>
    <td><input id="shop_navn" type="text" /></td>
</tr>
<tr>
    <td>Sælger</td>
    <td>
        <select id="salger">
              <option value="alle">Alle</option>
              <option value="EDC">EDC</option>
              <option value="KM">KM</option>

              <option value="MM">MM</option>

              <option value="SG">SG</option>
              <option value="TE">TE</option>
              <option value="BM">BM</option>
              <option value="BMO">BMO</option>

            <option value="CJN">CJN</option>
            <option value="MHB">MHB</option>
            <option value="DG">DG</option>
            <option value="MVO">MVO</option>
            <option value="GP">GP</option>
            <option value="DLA">DLA</option>
            <option value="SL">SL</option>

        </select>
    </td>
</tr>
<tr>
    <td>Valgshopansvarlig</td>
    <td>
         <select id="valgshopansvarlig">
         <option value='alle'>Alle</option><option value='KT'>KT</option> <option value='CLE'>CLE</option>            <option value='RTT'>RTT</option>

            </select>
    </td>
</tr>
<tr>
    <td>Ordretype</td>
    <td>
         <select id="ordretype">
              <option value="valgshop">Valgshop</option>
              <option value="papirvalg">Papirvalg</option>
         </select>
    </td>
</tr>
<tr>
    <td>salgsordrenummer</td>
    <td><input id="salgsordrenummer" type="text" /></td>
</tr>

<tr>
    <td>kontaktperson</td>
    <td><input id="kontaktperson" type="text" /></td>
</tr>
<tr>
    <td>mail</td>
    <td><input id="mail" type="text" /></td>
</tr>
<tr>
    <td>telefon</td>
    <td><input id="telefon" type="text" /></td>
</tr>
<tr>
    <td>antal gaver</td>
    <td><input id="antal_gaver" type="text" /></td>
</tr>
<tr>
    <td>antal gavevalg</td>
    <td><input id="antal_gavevalg" type="text" /></td>
</tr>
<tr>
<td>Indpakning</td>
    <td ><label class='shopboard-switch'>
        <input id="indpakning" type='checkbox'  />  <span class='shopboard-slider shopboard-round'></span></label>
    </td>
</tr>
<tr>
    <td>navn på gaver</td>
    <td ><label class='shopboard-switch'>
        <input id="navn_paa_gaver" type='checkbox'  />  <span class='shopboard-slider shopboard-round'></span></label>
    </td>

</tr>

<tr>
    <td>julekort</td>
    <td ><label class='shopboard-switch'>
        <input id="julekort" type='checkbox'  />  <span class='shopboard-slider shopboard-round'></span></label>
    </td>
</tr>


<tr>
    <td>flere leveringsadresser</td>
    <td ><label class='shopboard-switch'>
        <input id="flere_leveringsadresser" type='checkbox'  />  <span class='shopboard-slider shopboard-round'></span></label>
    </td>

</tr>

<tr>
<td>Privatlevering</td>
    <td ><label class='shopboard-switch'>
        <input id="privatlevering" type='checkbox'  />  <span class='shopboard-slider shopboard-round'></span></label>
    </td>
</tr>
<td>Reserveret</td>
    <td ><label class='shopboard-switch'>
        <input id="reserveret" type='checkbox'  />  <span class='shopboard-slider shopboard-round'></span></label>
    </td>
</tr>

<tr>
    <td>udland</td>
    <td><input type="text" id="udland"></td>
</tr>
<tr>
<td>Info</td>
    <td>
        <textarea id="info" rows="10" > </textarea>
    </td>
</tr>

<tr>
    <td>autogave</td>
    <td><input type="text" id="autogave"></td>
</tr>
<tr>
    <td>sprog lag</td>
    <td ><label class='shopboard-switch'>
        <input id="sprog_lag" type='checkbox'  />  <span class='shopboard-slider shopboard-round'></span></label>
    </td>

</tr>
<tr>
    <td>reminder</td>
    <td><input type="text" id="reminder"></td>
</tr>
<tr>
    <td>log på med</td>
    <td><input type="text" id="login"></td>
</tr>
<tr>
    <td>Demo shop</td>
    <td><input type="text" id="demoshop"></td>
</tr>


<tr>
    <td>shop åbner</td>
    <td><input type="text" id="shop_aabner"></td>
</tr>
<tr>
    <td>shop lukker</td>
    <td><input type="text" id="shop_lukker"></td>
</tr>
<tr>
    <td>levering</td>
    <td><input type="text" id="levering"></td>
</tr>
<tr>
    <td>pakkeri</td>
    <td><input type="text" id="pakkeri"></td>
</tr>
</table>



</div>


<!--   status -->


<div id="dialog-status" title="Shop status" style="display: none; ">
<br /><br />

<table width=95% >
<!--  <tr><td></td><td> <input type="radio" name="status" value=""> </td></tr>  -->
 <tr>
    <th><div class="rotate">Nye</div></th>
    <th><div class="rotate">Demo</div></th>
    <th><div class="rotate">Afventer info</div></th>
    <th><div class="rotate">Shop i gang</div></th>
    <th><div class="rotate">Shop lukket</div></th>
    <th><div class="rotate">Fordelingsliste</div></th>
    <th><div class="rotate">Indkøb</div></th>
    <th><div class="rotate">Shop afsluttet</div></th>

  </tr>
   <tr border=1 id="statusMenu">
    <td><input id="status1" type="radio" name="status" value="1"></td>
    <td><input id="status2" type="radio" name="status" value="2"></td>
    <td><input id="status3" type="radio" name="status" value="3"></td>
    <td><input id="status4" type="radio" name="status" value="4"></td>
    <td><input id="status5" type="radio" name="status" value="5"></td>
    <td><input id="status6" type="radio" name="status" value="6"></td>
    <td><input id="status7" type="radio" name="status" value="7"></td>
    <td><input id="status8" type="radio" name="status" value="8"></td>
  </tr>


</table>



</div>



</body>

</html>


















