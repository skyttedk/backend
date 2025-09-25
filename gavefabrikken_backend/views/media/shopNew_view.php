<script src="thirdparty/tinymce/tinymce.min.js"></script>
 <style>
 label{
    font-size: 1.1em;

 }
.noShop{
    display: none;
}
.Shop{
    display: block;
}
.sidebyside{
    display: inline-block;

}

  #feltDeffContainer { list-style-type: none; margin: 0; padding: 0; width: 970px; font-size: 12px; }
  #feltDeffContainer li { margin: 0 5px 5px 5px; padding: 5px;   height: 65px; text-align: left; color:black; font-weight: normal; }
 </style>


<div id="shopTabs" style="height: 550px; overflow: hidden;">
	<ul>
		<li><a href="#tabs-1" class="headline">Stamdata</a></li>



	</ul>
	<div id="tabs-1" style="height: 340px;">
      <div style="width:600px; height:450px;  float:left;padding:2px; text-align: left; overflow-y: auto;">
        <br />
        <div style="width:250px;float:left"><label>*Virksomhedsnavn:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopName" value="" style="width:300px" type="text"/></div><br />

        <div style="width:250px;float:left"><label>Kontakt:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopKontakt" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><label>Telefon:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopTelefon" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><label>Email:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopEmail" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><label>*CVR:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopCVR" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><label>*Link:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopLink" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><label>*Admin Brugernavn:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopUsername" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><label>*Admin Adgangskode:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopPassword" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><div>* skal udfyldes</div> </div><br />

      </div>

    </div>


</div>
<script>



$( document ).ready(function() {

    $( "#shopTabs" ).tabs();
    var pw = "";
    var randomStr = "123456789wertyupasdfghjkzxcvb";
    for(var i=0;6>i;i++){
       pw+= randomStr[randomIntFromInterval(0,29)];
    }
    $("#shopPassword").val(pw)

})
function randomIntFromInterval(min,max)
{
    return Math.floor(Math.random()*(max-min+1)+min);
}

</script>