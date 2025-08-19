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
  <link href="views/lib/jquery-ui/jquery-ui.css" rel="stylesheet" />
  <link href="http://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />






<style>
body {
    padding:0px;
    margin:0px;
    width: 100%;
    font-size: 10px;
    height: 100%;
  	font-family: "Helvetica Neue", Helvetica, sans-serif;


}




    tr:nth-child(even) {
    background-color: #dddddd !important;
    }
  tr:hover {
    background-color: black !important;
    color:white;
}
.tabElement{

overflow: auto;
}
input[type=checkbox] {  zoom:1.3;
  transform:scale(1.3);
  -ms-transform:scale(1.3);
  -webkit-transform:scale(1.3);
  -o-transform:scale(1.3);
  -moz-transform:scale(1.3);
  transform-origin:0 0;
  -ms-transform-origin:0 0;
  -webkit-transform-origin:0 0;
  -o-transform-origin:0 0;
  -moz-transform-origin:0 0;
  -webkit-transform-origin:0 0;}
  .is_cancelled{
    color:red;
  }

</style>

<script src="views/lib/jquery.min.js"></script>
<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<script src="thirdparty/table/jquery.dynatable.js"></script>
<script src="views/js/main.js"></script>
<script src="views/js/lager2.js"></script>
<script src="http://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>


<script>
$( document ).ready(function() {
    lager2.init();
    controlHeight()

});

function controlHeight(){
  var docHeight = $( document ).height();
  $(".tabElement").height(docHeight-100);

}
$( window ).resize(function() {
  controlHeight();

})

</script>

</head>

<body>

 <div id="tabs" style="margin-left:20px;">
  <ul>
    <li><a href="#tabs-1" onclick="lager2.getNotReleased('1')">Ej frigivne</a></li>
    <li><a href="#tabs-2" onclick="lager2.getWaitingOrder('2')">Ventende ordre</a></li>
    <li><a href="#tabs-3" onclick="lager2.getPrintetedOrder('3')">Printede ordre</a></li>
    <li><a href="#tabs-4" onclick="lager2.getIsShipOrder('4')">Udleverede ordre</a> </li>
    <li><a href="#tabs-5" onclick="lager2.getDeletedOrder('5')">Slettede ordre</a></li>
    <div id="actionBar" style="display: inline-block; margin-left:100px;"></div>
      <div style="width: 100px;float: right; margin-right:70px;  "><select id="card" onchange="lager2.changeCard()">
              <option value="52">Julegavekort</option>
              <option value="53">Guldgavekort</option>
              <option value="54,55,56">24gaver</option>
              <option value="265">Julegave-TYPEN</option>
              <option value="57,58,59,272">Julegavekort-NORGE</option>
              <option value="287,290,310">Drømmegavekortet</option>
            </select> </div>
  </ul>
  <div id="tabs-1" class="tabElement" >
     <table width=95% id="notReleased" class="tabsData" style="font-size: 12px;"></table>
  </div>
  <div id="tabs-2"  class="tabElement">
      <label>Antal kort: </label><span id="countWaiting"></span>
     <table width=95% id="WaitingOrder" class="tabsData" style="font-size: 12px;"></table>
  </div>
  <div id="tabs-3"  class="tabElement">
          <label>Antal kort: </label><span id="countPrintedOrder"></span>
       <table width=95% id="isPrintedOrder" class="tabsData" style="font-size: 12px;"></table>
  </div>
  <div id="tabs-4"  class="tabElement">
      <label>Antal udleverede kort: </label><span id="countDeleved"></span>
          <table width=95% id="isShipOrder" class="tabsData" style="font-size: 12px;"></table>
  </div>
    <div id="tabs-5"  class="tabElement">
              <label>Antal kort: </label><span id="countDeletedOrder"></span>
       <table width=95% id="isDeletedOrder" class="tabsData" style="font-size: 12px;"></table>
  </div>
</div>




<form name="goPrint" id="goPrint" action="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=lager2/goPrint" target="_blank" method="post" >
    <input id="doPrintList" name="doPrintList" type="hidden" />
</form>
<form name="goPrintAll" id="goPrintAll" action="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=lager2/goPrintAll" target="_blank" method="post" >
      <input id="goPrintAllCard" name="goPrintAllCard" type="hidden" />
</form>
</body>

</html>