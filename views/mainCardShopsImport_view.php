<?php

$is_norge = "";
if(isset($_GET["lang"])){
    $is_norge = "norge";
}


?>

<!DOCTYPE html>
<html>

<head>
  <title>Cardshops</title>
<style>
body {
    padding:0px;
    margin:0px;
    width: 100%;
    height: 100%;
  	font-family: "Helvetica Neue", Helvetica, sans-serif;
    background: #F7F7F7;
    font-size: 0.8em;
}
 .main{
    width: 1050px;


}

	.table1 {
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;
	}
	.table1 th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
	.table1 td {
		border:1px solid #C0C0C0;
		padding:5px;
	}

    #accordionCard{
        font-size: 0.75em;
    }

    #accordionCard{
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;
	}
    #accordionCard th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
	#accordionCard td {
		border:1px solid #C0C0C0;
		padding:5px;
	}
 .stamDataFormular{
     width: 320px;
 }
 #dialog_message_AddNewCard option{
     padding:7px;
 }
 #dialog_message_AddNewCard input{

}
#currentSogListContainer{
    width: 98%;
    height: 480px;
    overflow-y: auto;
}
.cardsogList{
    height: 25px;
    width: 90%;
    border: 1px solid #B7B7B7;
    padding:2px;
    margin-bottom: 3px;
    cursor: pointer;
}
.cardsogList:hover{
    background-color:#B7B7B7;
    color:white;
    zoom:110%;
    -webkit-transition: width 2s; /* Safari */
    transition: width 2s;
     cursor: pointer;


}

.later {
    background-color:#E6E01A;  /* Green */
    border: none;
    color: white;
    padding: 12px 28px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 5px;
    cursor: pointer;
}

.afvis {
    background-color: #f44336; /* Green */
    border: none;
    color: white;
    padding: 12px 28px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 5px;
    cursor: pointer;
}
.godkend {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 12px 28px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 5px;
    cursor: pointer;
}
.update {
    background-color: #5A86ED; /* Green */
    border: none;
    color: white;
    padding: 12px 28px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 5px;
    cursor: pointer;
    width:500px;
}
.history {
    background-color: #E5E5E5; /* Green */
    border: none;
    color: black;
    padding: 12px 28px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin-bottom: 5px;
    cursor: pointer;
    width:200px;
}


.afvis:hover, .godkend:hover, .later:hover , .history:hover, .update:hover    {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
}
#formDataToShow{
    font-size: 12px;

}
#formDataToShow input{
    font-size: 12px;

}
</style>

<script src="views/lib/jquery.min.js"></script>
<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<link href="views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">
<script src="views/js/main.js"></script>
<script src="views/js/mainCardShopsImport.js?v1"></script>

<script>
  $(function() {
    $( "#bizType" ).buttonset();
 //   companyImport.getTotal()
    companyImport.getCompanyImports();
       $(".afvis").show()
          $(".godkend").show()
             $(".later").show()

 });


 var is_norge = "<?php echo $is_norge; ?>";


 function goToShops()
 {
     window.location.href = "index.php?rt=page/shopMain";
 }
 function goToCardShop()
 {
    window.location.href = "index.php?rt=page/cardShop";
 }


</script>


</head>

<body>

<center>
<div class="main" >
    <div class="header">
        <div id="bizType">
           <!-- <input type="radio" id="radio1" name="radio" ><label for="radio1" onclick="goToShops()">Valgshops</label> -->
          <!--  <input type="radio" id="radio2" name="radio" ><label for="radio2"  onclick="goToCardShop()" >Gavekort-shops</label> -->
          <input type="radio" id="radio3" name="radio" checked><label for="radio3"  >Ventene Bestillinger </label>
         </div>
    </div>
    <br />

<table width=1000 border=1>
<tr height=400>
    <td width=400 id="mainContainer" valign=top></td>
    <td width=300 id="infoContainer"><div id="infoContainer" style="height: 600px; overflow: auto;"></div></td>
    <td width=300 ><div id="orderHistory" style="height: 600px; overflow: auto;"></div></td>
</tr>

<tr>
    <td height="55"><button class="godkend" style="margin-right: 130px;"  onclick="companyImport.doCreateOrder()">GODKEND</button><button onclick="companyImport.setToStandby()" class="later">HÅNDTERES SENERE</button></td><td align=right><button  onclick="companyImport.setToDelete()"  class="afvis">AFVIS</button></td><td align=right><button  onclick="companyImport.loadCompanyList()"  class="history">Vis godkendte</button></td>
</tr>


</table>
</center>


</div>

<div class="modalMsg" style="display: none;"></div>
</body>
</html>