

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
 .saleman-only-show {
     display: none;
 }
 <?php
 if($show_to_saleperson == true){
     echo ".salemane-not-show { display:none;  }";
     echo ".saleman-only-show { display:block;  }";
 }
 ?>


 </style>


<div id="shopTabs" style="height: 550px; overflow: hidden;">
	<ul>
		<li><a href="#tabs-1" class="headline">Stamdata</a></li>



	</ul>
	<div id="tabs-1" style="height: 340px;">
      <div style="width:600px; height:450px;  float:left;padding:2px; text-align: left; overflow-y: auto;">
        <br />
        <div style="width:250px;float:left"><label><span class="salemane-not-show">*</span>Virksomhedsnavn:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopName" value="" style="width:300px" type="text"/></div><br />

        <div style="width:250px;float:left"><label>Kontaktperson:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopKontakt" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><label>Kontakt Tlf.:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopTelefon" value="" style="width:300px" type="text"/></div><br />
        <div style="width:250px;float:left"><label>Kontakt Email:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopEmail" value="" style="width:300px" type="text"/></div><br />
          <div style="width:250px;float:left"><label><span class="salemane-not-show">*</span>CVR:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopCVR" value="" style="width:300px" type="text"/></div><br />
         <div style="width:250px;float:left"><label>Sœlger initialer:</label></div><div style="float:left;width:300px;height:30px;"><input id="saleperson" value="" style="width:300px" type="text"/></div><br />
          <div class="saleman-only-show" style="width:250px;float:left; color: red;"><div>ALT skal udfyldes</div> </div><br />
          <div class="salemane-not-show">

              <div style="width:250px;float:left"><label>*Link:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopLink" value="" style="width:300px" type="text"/></div><br />
              <div style="width:250px;float:left"><label>*Admin Brugernavn:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopUsername" value="admin" style="width:300px" type="text"/></div><br />
              <div  style="width:250px;float:left"><label>*Admin Adgangskode:</label></div><div style="float:left;width:300px;height:30px;"><input id="shopPassword" value="" style="width:300px" type="text"/></div><br />
              <div style="width:250px;float:left"><div>* skal udfyldes</div> </div><br />
        </div>


      </div>

    </div>


</div>
<script>

var show_to_saleperson =  <?php { echo $show_to_saleperson; } ?>


$( document ).ready(function() {

    $( "#shopTabs" ).tabs();
    var pw = "";
    var randomStr = "123456789wertyupasdfghjkzxcvb";
    for(var i=0;6>i;i++){
       pw+= randomStr[randomIntFromInterval(0,25)];
    }
    $("#shopPassword").val(pw)
    <?php
    if($show_to_saleperson == true){
        echo "$('#shopLink').val(pw) ";
    }
    ?>

})
function randomIntFromInterval(min,max)
{
    return Math.floor(Math.random()*(max-min+1)+min);
}

</script>