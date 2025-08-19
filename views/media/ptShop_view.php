<style type="text/css">

#upload-button {
	width: 150px;
	display: block;
	margin: 20px auto;
}

#file-to-upload {
	display: none;
}

#pdf-main-container {
	width: 1000px;
	margin: 20px auto;
}

#pdf-loader {
	display: none;
	text-align: center;
	color: #999999;
	font-size: 13px;
	line-height: 100px;
	height: 100px;
}

#pdf-contents {
	display: none;
}

#pdf-meta {
	overflow: hidden;
	margin: 0 0 20px 0;
}

#pdf-buttons {
	float: left;
}

#page-count-container {
	float: right;
}

#pdf-current-page {
	display: inline;
}

#pdf-total-pages {
	display: inline;
}

#pdf-canvas {
	border: 1px solid rgba(0,0,0,0.2);
	box-sizing: border-box;
}

#page-loader {
	height: 100px;
	line-height: 100px;
	text-align: center;
	display: none;
	color: #999999;
	font-size: 13px;
}

#download-image {
	width: 150px;
	display: block;
	margin: 20px auto 0 auto;
	font-size: 13px;
	text-align: center;
}
#pdfLink{
 text-decoration: underline;
 cursor: pointer;
}
#ptDeletePdf{
    float: right;
    background-color: red;
    color:white;
    display:none;
}
.ptMenu{
    width:97%;
    height: 40px;
    text-align: left;

}
.ptMenu .ptSetMultiplePrice{
    padding:3px;
    cursor: pointer;
}

</style>

<link rel="stylesheet" type="text/css" href="views/css/ptShop.css?v=<?php echo rand(0, 100); ?>">

<div class="pt-shop">
  <div class="left">
             <fieldset>
  <legend>Opret / opdatere pdf</legend>
  <!-- <div>PDF er pt. under udvikling og kan ikke benyttes. <br>Hvis du har sp&oslash;rgsm&aring;l, s&aring; ring til mig (ulrich, 53746555) eller send en mail. <br> de pdf'er som tidligere er oprettet, er ikke p&aring;virket</div> -->


  <button id="ptMakePdf">Opret ny pdf</button>
      <button id="ptDeletePdf">Slet PDF</button>
       <hr>
      <div id="pdfLink">Ingen pdf</div>


<!--        <a id="ptLoadPdf" >Upload presentation</a>  -->


     <!--
        <form class="uploadFile" enctype="multipart/form-data">

      <br><br>
        <input  name="file" type="file" />
        <input  id="ptShop-upload" type="button" value="Upload presentation" />   <progress  id="ptShop-progress"></progress>
        </form>
        -->

    </fieldset>
    <br>
    <fieldset>
      <legend>Presentations links</legend>

<!--
<label>Medtage ekstra billeder: </label><input id="exstraImg" type="checkbox" />
-->
<button id="openShop">&Aring;ben pr&oelig;sentation</button>
<br>
  <br>
  <label  style="width: 200px;" >Link til kunden: </label><br><input onClick="this.select();"  id="ptLink" type="text" value="" />
  <br><br>
      <label style="width: 200px;">PDF Link til kunden: </label><br><input   id="ptPdfLink" type="text" value="" />
  <br>
    </fieldset><br>

     <fieldset>
      <legend>S&oelig;lger</legend>
      <div class="salesman"></div>
    </fieldset>
  </div>
  <div class="right">
  <fieldset>
      <legend>Priser</legend>
     <div class="ptMenu">
    <img class="ptSetMultiplePrice" height="30" src="views/media/icon/Edit_prices-512.png" alt="" /><span id="ptPrisMsg" style="color:red; display:none; font-size:16px;"></span>
</div>
   </fieldset><br>
<fieldset>
      <legend>Sprog (under udvikling)</legend>
      <input class="pt-layout-language-eng" type="checkbox" /><label > Engelsk</label> <br>
    </fieldset>
      <br>

<fieldset>
      <legend>Layout</legend>
      <input class="pt-green-layout" type="checkbox" /><label > Vis det gr&oslash;nne design</label> <br>
    </fieldset>
      <br>
  <fieldset>
      <legend>Forside</legend>
      <br>
      <label>Firma navn: </label>     <input style="width: 50%;" id="pt_title" type="text" /><button id="ptShopTitle">Opdatere title</button> <hr>
      <table>

      <tr><td><b>Danmark: </b></td><td><input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage1" value="1"><label style="width:200px;"> Julen 2022</label> |
      <input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage2" value="2"><label style="width:200px;"> Gaver 2022</label> |
      <input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage2" value="7"><label style="width:200px;"> Sommer 2022</label></td></tr>
      <tr><td colspan="2"><hr></td> </tr>
      <tr><td><b>Norge: </b></td><td><input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage10" value="10"><label style="width:200px;"> Julen 2022 </label> |
       <input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage3" value="3"><label style="width:200px;"> Gaver 2022 </label> |
       <input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage11" value="11"><label style="width:200px;"> Sommer 2022  </label>
      </td></tr>
      <tr><td colspan="2"><hr></td> </tr>
      <tr><td><b>Sverige: </b></td><td><input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage5" value="5"><label style="width:200px;"> Jul 2022</label> |
      <input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage6" value="6"><label style="width:200px;"> Gaver 2022</label> |
      <input type="radio" name="frontpage" class="pt-frontpage" id="pt-frontpage6" value="8"><label style="width:200px;"> Sommer 2022</label></td></tr>
      </table>



    </fieldset>
    <br>
     <fieldset>
      <legend>Slides</legend>
      <input class="pt-mereAtGive" type="checkbox" /><label> Vis mere at give til hinanden </label> <br>
      <input class="pt-plantTree" type="checkbox" /><label> Vis Plant tr&oelig;er  </label><br>
      <input class="pt-bag" type="checkbox" /><label> Vis Indpakningssiden  </label><br>
      <input class="pt-voucher-page" type="checkbox" /><label> Vis Vouchersiden (kun dk) </label><br>
      <input class="pt-saleperson-page" type="checkbox" /><label> Vis bagsiden  </label>
    </fieldset>


  </div>
</div>
    <br>
    <br>
<div id="ptShopPriceDialog" title="" style="display:none; ">
    <div><u>Kun dk priser</u></div>
    <div style="color:red">Hvis feltet er blankt, bliver den p&aring;g&oelig;ldende pris ikke vist i pr&oelig;sentationen </div>
    <div style="height: 400px; overflow-y: auto;">
        <table id="ptShopPrice"></table>
    </div>


</div>


<script src="views/js/base64.js?v=<?php echo rand(0, 100); ?>"></script>
<script src="views/js/ptShop.js?v=<?php echo rand(0, 100); ?>"></script>
<script src="views/js/ptShopPrices.js?v=<?php echo rand(0, 100); ?>"></script>
<script>






</script>