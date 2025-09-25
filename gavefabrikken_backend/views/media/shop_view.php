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

.rotate {

/* Safari */
-webkit-transform: rotate(-70deg);

/* Firefox */
-moz-transform: rotate(-70deg);

/* IE */
-ms-transform: rotate(-70deg);

/* Opera */
-o-transform: rotate(-70deg);

/* Internet Explorer */
filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);

}
  #feltDeffContainer { list-style-type: none; margin: 0; padding: 0; width: 970px; font-size: 12px; height: 200px; }
  #feltDeffContainer li { margin: 0 5px 5px 5px; padding: 5px;   height: 65px; text-align: left; color:black; font-weight: normal; }
textarea {
    resize: none;
}
.feltdeffLang{
    width: 600px;
}
.feltdeffLang input{
    width: 400px;
}
.feltdeffLang td{
    padding:5px;
}
.giftIsNoActive {
    opacity: 0.3;
    filter: alpha(opacity=30);
}
.shopStateSelected {
    background-color: #007fff;
    color:white;
}
.shopState{
    display: inline-block;
    padding: 3px;
    border-radius: 6px;
}
.shopState,.shopState *:hover{
    cursor: pointer;
}
.shopState:hover{
 background-color: #007fff;
}


.localisationSelected {
    background-color: #007fff;
    color:white;
}
.localisation{
    display: inline-block;
    padding: 3px;
    border-radius: 6px;
}
.localisation,.localisation *:hover{
    cursor: pointer;
}
.localisation:hover{
 background-color: #007fff;
}



 </style>

<div id="shopTabs" style="  overflow: hidden; font-size: 12px;">
	<ul id="shopTabsItems" >
	   	<li><a href="#shoptabs-1" class="headline"  >Stamdata</a></li>
		<li><a href="#shoptabs-2" class="headline" onclick="company.setTinyActiveOnStamdata()" >Forside</a></li>
	 <!--   <li><a href="#tabs-3" class="headline">Gaver_old</a></li> -->
           <li ><a href="#shoptabs-10" class="headline gaver" onclick="valgshopGaverTab()">GAVER I SHOPPEN</a></li>
           <li ><a href="#shoptabs-12" class="headline" onclick="initDbCalc()">DB</a></li>
		<li><a href="#shoptabs-4" class="headline" onclick="shopSettings.init()">Indstillinger</a></li>
		<li><a href="#shoptabs-5" class="headline" id="menuFeltDeff001" onclick="feltDeff.showLangFields()">Felt definition</a></li>
		<li><a href="#shoptabs-6" class="headline" id="menuUserAdmin001" onclick="userData.openApp()">Brugerindl&oelig;sning</a></li>
		<li><a href="#shoptabs-7" class="headline" onclick="rapport.updateList()" >Rapporter</a></li>
		<li><a href="#shoptabs-8" class="headline" id="menuGavevalg001" onclick="gavevalg.goto()">Gavevalg</a></li>
		<li><a href="#shoptabs-9" class="headline" onclick="sm.load()">Lageroverv&aring;gning</a></li>
        <li><a href="#shoptabs-11" class="headline" >Pr&oelig;sentation</a></li>
        
	</ul>

     <div id="shoptabs-10" style="text-align: left;">
               <?php include("gaveEjvalgte.php"); ?>
     <?php include("gavealias.php"); ?>
        <?php include("views/valgshop-gaver_view.php") ?>
        <?php include("views/pimPresentSync_view.php") ?>


     </div>


    <div id="shoptabs-1" >
         <div id="stamdata" style="max-width: 1200px; overflow-y: auto; ">
        <table width=100% >


         <tr>
    <td  width=50% valign=top>
    <table >
    <tr> <td><b>Shoppens lokalisering</b></td><td></td> </tr>
    <tr><td colspan="2">
        <div class="localisation">
            <input type="radio" name="localisation" data-id="1"  id="localisation-1">
            <label for="localisation-1">Dansk</label>
        </div>
        <div class="localisation">
            <input type="radio" name="localisation" data-id="4"  id="localisation-4">
            <label for="localisation-4">Norsk</label>
        </div>
        <div class="localisation">
            <input type="radio" name="localisation" data-id="5" id="localisation-5">
            <label for="localisation-5">Svensk</label>
        </div>
        </td></tr>


    <tr> <td><b>Shop status:</b></td><td></td> </tr>
    <tr><td colspan="2">
        <div class="shopState">
            <input type="radio" name="shopState" data-id="2"  id="shopState-offer">
            <label for="shopState-offer">OPL&OElig;G</label>
        </div>
        <div class="shopState">
            <input type="radio" name="shopState" data-id="1"  id="shopState-shop">
            <label for="shopState-shop">SHOP</label>
        </div>
        <div class="shopState">
            <input type="radio" name="shopState" data-id="3" id="shopState-deny">
            <label for="shopState-deny">TABT</label>
        </div>
        </td></tr>
        <tr><td colspan="2"></td></tr>

            <tr> <td><b>Stamdata:</b></td><td></td> </tr>
            <tr><td>Virksomhedsnavn:</td><td><input id="shopName" value="" style="width:300px" type="text"/></td></tr>
            <tr><td>Kontakt:</td><td><input id="shopKontakt" value="" style="width:300px" type="text"/></td></tr>
            <tr><td>Telefon:</td><td><input id="shopTelefon" value="" style="width:300px" type="text"/></td></tr>
            <tr><td>Email:</td><td><input id="shopEmail" value="" style="width:300px" type="text"/></td></tr>
            <tr><td>CVR:</td><td><input id="shopCVR" value="" style="width:300px" type="text"/></td></tr>
            <tr><td>Link:</td><td><input id="shopLink" value="" style="width:300px" type="text"/></td></tr>
            <tr><td>Admin Brugernavn:</td><td><input id="shopUsername" value="" style="width:300px" type="text"/></td></tr>
            <tr><td>Admin Adgangskode:</td><td><input id="shopPassword" value="" style="width:300px" type="text"/></td> </tr>
            <tr><td><button style="color:red;" id="delete-shop" onclick="company.deleteShop()">SLET SHOP</button></td></tr>
            </table>



         </td>
         <td width=50% valign=top>

            <table  >
           <tr> <td><b>Intern GF oplysninger:</b></td><td></td> </tr>
           <tr> <td>SO_NR:</td><td><input id="so_no" value="" style="width:300px" type="text"/></td></tr>
           <tr> <td>S&oelig;lger:</td><td><input id="salesPerson" value="" style="width:300px" type="text"/></td> </tr>
           <tr> <td>Gaveansvarlig:</td><td><input id="giftResponsible" value="" style="width:300px" type="text"/></td> </tr>
           <tr> <td><b>Links der skal sendes til kunden:</b></td><td></td></tr>
           <tr> <td>Link til shoppen:</td><td><input id="shopRealLink" value="" style="width:300px" type="text"/></td> </tr>
           <tr> <td>Link til kundens backend:</td><td><input id="shopLinkBackend" value="" style="width:300px" type="text"/></td> </tr>
           <tr> <td><b>Egne Links:</b></td><td></td> </tr>
           <tr> <td>Link til shoppen:</td><td><span id="shopOwnLink" style="width:300px"></span></td></tr>
          <tr>  <td>Link til kundens backend:</td><td><span id="shopOwnLinkBackend" style="width:300px"></span></td></tr>



            </table>
            </td>
              </tr>
         </table>
          </div>
    </div>
	<div id="shoptabs-2">

            <table width=100%>
            <tr>
            <td width=40% valign=top>
                          <fieldset>
                <legend><b>Upload logo</b></legend>
                       <form action="upload.php" class="dropzone" style="width: 99%; height:250px;"></form>
                </fieldset>
                <fieldset >
                <legend><b>Valgt logo</b></legend>
                   <div style="height:150px;" id="selectedLogo" class="selectedLogoForside"></div>
                </fieldset>

            </td>
            <td width=5%></td>
            <td width=55% valign=top>
            <fieldset><legend><b>Beskrivelse</b></legend>
             <div id="shopDescriptionTabs" >
	        <ul>
		        <li><a href="#tabsDes-1" class="headline">Dansk</a></li>
		        <li><a href="#tabsDes-2" class="headline">Engelsk</a></li>
		        <li><a href="#tabsDes-3" class="headline">Tysk</a></li>
		        <li><a href="#tabsDes-4" class="headline">Norsk</a></li>
		        <li><a href="#tabsDes-5" class="headline">Svensk</a></li>
	        </ul> <br / >
                <div id="tabsDes-1">  <textarea class="shopDescriptionText" id="shopDa"></textarea><br />    </div>
           	    <div id="tabsDes-2"> <textarea class="shopDescriptionText" id="shopEn"></textarea><br /> </div>
	            <div id="tabsDes-3">  <textarea class="shopDescriptionText" id="shopDe"></textarea><br /> </div>
	            <div id="tabsDes-4">  <textarea class="shopDescriptionText" id="shopNo"></textarea><br /> </div>
    	        <div id="tabsDes-5"> <textarea class="shopDescriptionText" id="shopSv"></textarea> </div>

              </div>
              </fieldset>
            </td>
            </tr>


            </table>



    </div>
    <!--
	<div id="tabs-3" >
        <?php include("gaveEjvalgte.php"); ?>
        <?php include("gavealias.php"); ?>

        <div style="height: 30px; width: 100%; text-align: right;" id="toogleSelectPresentView" >
            <button onclick="toogleSelectPresentView('selectGift')">Vis valgte gaver</button>
        </div>
        <div style="width: 100%; height: 650px; overflow-y: auto; display:none;" id="selectPresent">
            <br />
            <ul id="sortable"></ul>
        </div>
        <div style="width: 100%; height: 650px; overflow-y: auto; " id="shopPresentSelect">  </div>
    </div>
       -->
    <div id="shoptabs-4" style="text-align: left;"> <br />
        <?php include("shopSettings_view.php"); ?>

    </div>
    <div id="shoptabs-5"  style="text-align: left;">
        <div id="tabsFeltDeff" style="overflow-y: scroll;">
        <ul>
	        <li><a href="#tabsFd-1" class="headline">Dansk</a></li>
	        <li><a href="#tabsFd-2" class="headline" onclick="feltDeff.showLangFields()">Engelsk</a></li>
	        <li><a href="#tabsFd-3" class="headline" onclick="feltDeff.showLangFields()">Tysk</a></li>
	        <li><a href="#tabsFd-4" class="headline" onclick="feltDeff.showLangFields()">Norsk</a></li>
	        <li><a href="#tabsFd-5" class="headline" onclick="feltDeff.showLangFields()">Svensk</a></li>
            <div style="float: right; margin-right: 5px;">

            <img  class="icon" src="views/media/icon/1373253494_plus_64.png"  onclick="feltDeff.addNew()" alt="Opret nyt felt" height="25" width="25" style="margin-right: 5px; margin-top:-6px;" />
                      <img  class="icon" src="views/media/icon/16278993-update-icon-glossy-orange-button.jpg" alt="Opdatere felter"  onclick="feltDeff.saveItem()" height="30" width="30" />
            </div>


        </ul>
        <div id="tabsFd-1" >
        <br />
        
          <div id="feltdefWarnMessage" style="max-width: 940px; display: none; padding: 10px; font-size: 1.2em; margin-bottom: 40px; background: rgba(200,100,100,0.5);"></div>

            <div style="width:910;  height:40px; text-align: left;">
             <div class="sidebyside rotate" style="width: 170px; text-align: center; ">Felt navn</div>
             <div class="sidebyside rotate" style="width: 30px; text-align: center;margin-left: 18px;  ">Login</div>
             <div class="sidebyside rotate" style="width: 30px;text-align: center;margin-left: 15px;  ">Kode</div>
             <div class="sidebyside rotate" style="width: 30px; text-align: center; margin-left: 15px; ">Email</div>
             <div class="sidebyside rotate" style="width: 30px; text-align: center;margin-left: 10px;  ">Navn</div>
             <div class="sidebyside rotate" style="width: 30px; text-align: center; margin-left: 15px; ">L&aring;st</div>
             <div class="sidebyside rotate" style="width: 30px; text-align: center; margin-left: 15px; ">Udfyldes</div>
             <div class="sidebyside rotate" style="width: 30px; text-align: center; margin-left: 15px; ">Vis</div>
             <div class="sidebyside rotate" style="width: 300px; text-align: center; margin-left: 30px;  ">Liste</div>
            </div>

                <ul id="feltDeffContainer"></ul>

        </div>
        <div id="tabsFd-2">
            <div id="feltdeffEn" class="feltdeffLang" ></div>
        </div>
        <div id="tabsFd-3">
            <div id="feltdeffDe"  class="feltdeffLang" ></div>
        </div>
        <div id="tabsFd-4">
            <div id="feltdeffNo"  class="feltdeffLang" ></div>
        </div>
        <div id="tabsFd-5">
            <div id="feltdeffSv"  class="feltdeffLang" ></div>
        </div>
</div>





  </div>
    <div id="shoptabs-6">

    </div>
    <div id="shoptabs-7">
    <?php include("rapport_view.php");  ?>
    </div>
    <div id="shoptabs-8">

    </div>
    <div id="shoptabs-9">
       <?php include("storageMonitoring_view.php");  ?>
    </div>
      <div id="shoptabs-11">
      <?php include("ptShop_view.php"); ?>

    </div>
      <div id="shoptabs-12">
   <?php include("dbCalc_view.php"); ?>

    </div>


</div>
<div id="previewPresent" style="display: none;"></div>
<div id="dialog-feltDeff" style="display: none;"></div>

<?php
    echo '<script src="views/js/presentsInShop.js?'.rand(10,1000).'"></script>';
    echo '<script src="views/js/voucher.js?'.rand(10,1000).'"></script>';
    echo '<script src="views/js/pimPresentSync.js?'.rand(10,1000).'"></script>';




?>


