<?php

  // print_r($bi["data"]);
/*
 function makeSalepersonData($data){
    $html="";
    foreach($data as $ele){
        $html.= "<tr>";
        foreach($ele as $key=>$row) {
            $html.= "<td>" . $row . "</td>";
        }
        $html.= "</tr>";
    }
    return "<table>".$html."</table>";
   }
  */


?>
<!DOCTYPE HTML>

<html>

<head>
  <title>BI-GF</title>
<script src="views/lib/jquery.min.js"></script>
<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script src="views/js/main.js"></script>
<script src="views/js/bi.js?<?php echo mt_rand() ?> "></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
<script src="views/js/charts.js"></script>
<style>
#total{
    font-weight: bold;

}
table {
  border-collapse: collapse;
  border: 1px solid black;
}

th,td {
  border: 1px solid black;
  padding: 5px;
}

table.a {
  table-layout: auto;
  width: 180px;
}

table.b {
  table-layout: fixed;
  width: 180px;
}

table.c {
  table-layout: auto;
  width: 100%;
}

table.d {
  table-layout: fixed;
  width: 100%;
}
.dateContainer{
    margin:20px;
    padding: 10px;
    border:1px solid black;

}
.range-search{
    background-color: #00FF33;
}
.range-search:hover{
    background-color: #009900;
    color:white;
}
.total td{
  font-weight: bold;
}
 #myChart{

 }
</style>




<script type="text/javascript">




$( document ).ready(function() {
    bi.init();
});

</script>



</head>

<body>
<div>
  <div class="dateContainer">
      <label for="from">From</label>
      <input type="text" id="from" name="from" autocomplete="off" >
      <label for="to">to</label>
      <input type="text" id="to" name="to" autocomplete="off" >
      <input type="button" class="range-reset" value="Nulstil" />
      <input  type="button" class="range-search" value="Søg" />

  </div>
</div>

 <div id="biTabs">
  <ul>
    <li><a  href="#tabs-0">ALLE</a></li>
    <li><a  href="#tabs-1">DG Sælger-kort-shop</a></li>
    <li><a  href="#tabs-2">Sælger-kort-shop perform</a></li>
  </ul>
  <div id="tabs-0">
    <div id="tabs-0-data"></div>
  </div>
  <div id="tabs-1">
     <p>*webservice dækker over det salg kunderne selv har indtastet på vores salgssider.</p>
     <div id="tabs-1-data"></div>
  </div>
  <div id="tabs-2">
    <div style="width: 800px;  ">
          <canvas id="myChart"></canvas>

    </div>
  </div>
</div>

</body>

</html>