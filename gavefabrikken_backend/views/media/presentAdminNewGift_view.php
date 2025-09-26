<script src="thirdparty/tinymce/tinymce.min.js"></script>





 <style>
 input[type=checkbox] {
  /* All browsers except webkit*/
  transform: scale(1.2);

  /* Webkit browsers*/
  -webkit-transform: scale(1.2);
}
.videoList{
    width:630px;
    height: 150px;
    border: 1px solid black;
    padding:5px;
    margin-left:10px;
    margin-top:10px;

}
.videoList span{
    font-weight: bold;


}
.videoList img{
    position: relative;
    right:10px;
    margin-right:40px;

}
#editPresentlogoList{
  height: 500px;
  overflow-y:scroll;
}
.presentsVariant{
   height: 600px;
  overflow-y:scroll;

}
.tableStyle1 table {
  border-collapse: collapse;
  width: 100%;
}

.tableStyle1 th, .tableStyle1 td {
  text-align: left;
  padding: 8px;
}

.tableStyle1 tr:nth-child(even){background-color: #f2f2f2}

.tableStyle1 th {
  background-color: #4CAF50;
  color: white;
}



 </style>



 <button id="gaveAdminBack" style="float:left; color:red; " onclick="gaveAdmin.backToList()">TILBAGE</button> <br />  <br />

<div id="presentsTabs">
	<ul>
		<li><a href="#tabs-1" class="headline">Generele Informationer</a></li>
		<li><a href="#tabs-2" class="headline">Beskrivelse</a></li>
		<li><a href="#tabs-3" class="headline">Billeder / video</a></li>
        <li><a href="#tabs-5" class="headline"  onclick="showlogoDialog()">Logo</a></li>
		<li><a href="#tabs-4" class="headline" >Sampak</a></li>
      	<li><a href="#tabs-6" class="headline" onclick="loadPt()" >Pr&oelig;sentation</a></li>
        <div id="presentDetailSaveBtn"></div>
    </ul>

	<div id="tabs-1" style="height: 500px;">
      <div class="gaveAdminTab" style="width:700px;float:left;padding:2px">
        <br />
      <!--  <div style="width:150px;float:left"><label>Varenummer</label></div><div style="float:left;width:300px;height:30px;"><input id="presentsAdminNr" value="" style="width:300px" type="text"/></div><br /><br /> -->
      <!--  <div style="width:150px;float:left"><label>Sub varenr.</label></div><div style="float:left;width:300px; height:110px;" id="giVareList" >  </div> -->
     <!--  <div style="width:150px;float:left"><label>Internt varenavn</label></div><div style="float:left;width:300px;height:30px;"><input value="" style="width:300px" type="text "  id="presentsAdminName" /></div><br /><br />-->
        <div style="width:150px;float:left; text-align: left;"><label>Gave status</label></div><div style="float:left;width:140px;height:30px;">
              <input type="radio" id="presentState_a" name="state" value="a">
              <label for="a">A</label> |
              <input type="radio" id="presentState_b" name="state" value="b">
              <label for="b">B</label> |
              <input type="radio" id="presentState_c" name="state" value="c">
              <label for="b">C</label>
        </div> <br /><br />

        <div style="width:150px;float:left; text-align: left;"><label>NAV Varenavn</label></div><div style="float:left;width:300px;height:30px;"><input value="" style="width:400px" type="text "  id="NAVpresentsAdminName" /></div> <br /><br />
        <div style="width:150px;float:left; text-align: left;"><label>Leverand&oslash;r</label></div><div style="float:left;width:300px;height:30px;"><input id="presentsAdminlev" value="" style="width:300px" type="text"/></div><br /><br />
        <div style="width:150px;float:left; text-align: left;"><label>Varepris</label></div><div style="float:left;width:300px;height:30px;"><input value="0" style="width:300px" type="text"  id="presentsAdminPrice" /></div><br /><br />
        <div style="width:150px;float:left; text-align: left;"><label>budgetpris</label></div><div style="float:left;width:300px;height:30px;"><input value="0"  value="" style="width:300px" type="text"  id="prisentsAdminBudgetPrice" /></div> <br />  <br />
        <div style="width:150px;float:left; text-align: left;"><label>Vejl. Pris</label></div><div style="float:left;width:300px;height:30px;"><input value="0"  value="" style="width:300px" type="text"  id="prisentsAdminThePrice" /></div> <br />  <br />
        <div style="width:250px;float:left; text-align: left;"><label>KostPris fra NAV DANMARK</label></div><div style="float:left;width:300px;height:30px;"><input value="0"  style="width:300px" type="number" min="0" id="prisents_nav_price" /></div> <br />  <br />
        <div style="float:left;width:250px;height:30px;; text-align: left;"><label>Vis gave i s&oelig;lger modul DANMARK: </label></div><div style="float:left;width:30px;height:30px;"><input type="checkbox" id="show_to_saleperson" ></div> <br />  <br />
        <div style="float:left;width:150px;height:30px;; text-align: left;"><label>Gave med omtanke: </label></div><div style="float:left;width:30px;height:30px;"><input type="checkbox" id="oko_present" ></div> <br />  <br />
        <div style="width:150px;float:left; text-align: left;"><label>Moms sats</label></div><div style="float:left;width:50px;height:30px;">
         <select id="moms" "style=width:50px">
              <option value="25">25%</option>
              <option value="15">15%</option>
              <option value="0">0%</option>

        </select>
        </div> <br /> <br />  <hr>
        <div style="width:250px;float:left; text-align: left;"><label>KostPris fra NAV NORGE</label></div><div style="float:left;width:300px;height:30px;"><input value="0"   style="width:300px" type="number" min="0"  id="prisents_nav_price_no" /></div> <br />  <br />
        <div style="float:left;width:250px;height:30px;; text-align: left;"><label>Vis gave i s&oelig;lger modul NORGE: </label></div><div style="float:left;width:30px;height:30px;"><input type="checkbox" id="show_to_saleperson_no" ></div> <br />  <br />
         <br />  <br />

      </div>
    </div>
	<div id="tabs-2">
        <div class="gaveAdminTab" id="presentDescriptionTabs" >
	        <ul>
		        <li><a href="#tabsDes-1" class="headline">Dansk</a></li>
		        <li><a href="#tabsDes-2" class="headline">Engelsk</a></li>
		        <li><a href="#tabsDes-3" class="headline">Tysk</a></li>
		        <li><a href="#tabsDes-4" class="headline">Norsk</a></li>
		        <li><a href="#tabsDes-5" class="headline">Svensk</a></li>
	        </ul>
	        <div id="tabsDes-1">

            <div style="display: inline-block">
                <label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineDa" />
            </div>
            <div style="display: inline-block">
                <label>Overskrift pr&oelig;sentation:</label><br /><input type="text" size="50" id="presentsAdminHeadlineDaPT" />
            </div>

            <br /><hr />

              <table width="100%">
              <tr>
                  <td  width="44%"><b>Kort beskrivelse:</b> </td>
                  <td  width="2%"></td><td></td>
                  <td width="44%"><b> Detaljeret beskrivelse:</b></td>
              </tr>
              <tr>
                  <td> <textarea class="presentDescriptionText" id="presentsAdminShortDa"></textarea></td>
                 <td></td><td></td>
                 <td><textarea class="presentDescriptionText" id="presentsAdminLongDa"></textarea> </td>
              </tr>
              </table>
            </div>

	        <div id="tabsDes-2">

            <div style="display: inline-block">
                <label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineEn" />
            </div>
            <div style="display: inline-block">
                <label>Overskrift pr&oelig;sentation:</label><br /><input type="text" size="50" id="presentsAdminHeadlineEnPT" />
            </div>



              <table width="100%">
              <tr>
                  <td  width="44%"><b>Kort beskrivelse:</b> </td>
                  <td  width="2%"></td><td></td>
                  <td width="44%"><b> Detaljeret beskrivelse:</b></td>
              </tr>
              <tr>
                  <td> <textarea class="presentDescriptionText" id="presentsAdminShortEn"></textarea></td>
                 <td></td><td></td>
                 <td><textarea class="presentDescriptionText" id="presentsAdminLongEn"></textarea> </td>
              </tr>
              </table>
            </div>
	        <div id="tabsDes-3">

            <div style="display: inline-block">
                <label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineDe" />
            </div>
            <div style="display: inline-block">
                <label>Overskrift pr&oelig;sentation:</label><br /><input type="text" size="50" id="presentsAdminHeadlineDePT" />
            </div>


              <table width="100%">
              <tr>
                  <td  width="44%"><b>Kort beskrivelse:</b> </td>
                  <td  width="2%"></td><td></td>
                  <td width="44%"><b> Detaljeret beskrivelse:</b></td>
              </tr>
              <tr>
                  <td> <textarea class="presentDescriptionText" id="presentsAdminShortDe"></textarea></td>
                 <td></td><td></td>
                 <td><textarea class="presentDescriptionText" id="presentsAdminLongDe"></textarea> </td>
              </tr>
              </table>
            </div>

	        <div id="tabsDes-4">
            <div style="display: inline-block">
                <label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineNo" />
            </div>
            <div style="display: inline-block">
                <label>Overskrift pr&oelig;sentation:</label><br /><input type="text" size="50" id="presentsAdminHeadlineNoPT" />
            </div>


              <table width="100%">
              <tr>
                  <td  width="44%"><b>Kort beskrivelse:</b> </td>
                  <td  width="2%"></td><td></td>
                  <td width="44%"><b> Detaljeret beskrivelse:</b></td>
              </tr>
              <tr>
                  <td> <textarea class="presentDescriptionText" id="presentsAdminShortNo"></textarea></td>
                 <td></td><td></td>
                 <td><textarea class="presentDescriptionText" id="presentsAdminLongNo"></textarea> </td>
              </tr>
              </table>
            </div>
	        <div id="tabsDes-5">

            <div style="display: inline-block">
                <label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineSv" />
            </div>
            <div style="display: inline-block">
                <label>Overskrift pr&oelig;sentation:</label><br /><input type="text" size="50" id="presentsAdminHeadlineSvPT" />
            </div>

              <table width="100%">
              <tr>
                  <td  width="44%"><b>Kort beskrivelse:</b> </td>
                  <td  width="2%"></td><td></td>
                  <td width="44%"><b> Detaljeret beskrivelse:</b></td>
              </tr>
              <tr>
                  <td> <textarea class="presentDescriptionText" id="presentsAdminShortSv"></textarea></td>
                 <td></td><td></td>
                 <td><textarea class="presentDescriptionText" id="presentsAdminLongSv"></textarea> </td>
              </tr>
              </table>
            </div>

    <!--
            <div id="tabsDes-2"><label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineEn" /><br /><hr /><label>Kort beskrivelse:</label> <textarea class="presentDescriptionText" id="presentsAdminShortEn"></textarea><br /> <hr /><label>Detaljeret beskrivelse:</label> <textarea class="presentDescriptionText" id="presentsAdminLongEn"></textarea></div>

           <div id="tabsDes-3"><label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineDe" /><br /><hr /><label>Kort beskrivelse:</label> <textarea class="presentDescriptionText" id="presentsAdminShortDe"></textarea><br /> <hr /><label>Detaljeret beskrivelse:</label> <textarea class="presentDescriptionText" id="presentsAdminLongDe"></textarea></div>

	        <div id="tabsDes-4"><label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineNo" /><br /><hr /><label>Kort beskrivelse:</label> <textarea class="presentDescriptionText" id="presentsAdminShortNo"></textarea><br /> <hr /><label>Detaljeret beskrivelse:</label> <textarea class="presentDescriptionText" id="presentsAdminLongNo"></textarea></div>
	        <div id="tabsDes-5"><label>Overskrift:</label><br /><input type="text" size="50" id="presentsAdminHeadlineSv" /><br /><hr /><label>Kort beskrivelse:</label> <textarea class="presentDescriptionText" id="presentsAdminShortSv"></textarea> <br /><hr /><label>Detaljeret beskrivelse:</label> <textarea class="presentDescriptionText" id="presentsAdminLongSv"></textarea></div>
     -->
        </div>
    </div>
	<div id="tabs-3" >
        <div class="gaveAdminTab">

         <fieldset>
        <legend><b>Gave billeder </b></legend>
        <table width=100% border=0 height="200">
        <tr>
        <td width=30% valign="top" align="left" align="center" id="presentDropFrame">
            <button onclick="showVideoList()">Vis video liste</button> <br />
           <iframe src="views/uploadPresentImg_view.php" scrolling="no" width=100% height=200 frameborder="0"></iframe>
        </td>
        <td width=10%></td>
        <td width=60% valign="top">
        <br />
        <ul id="sortable">

        </ul>
        </td>
        </tr>
        </table>
            </fieldset>
        </div>
    </div>
    	<div id="tabs-5">
             <table width=100% valign="top" border=1>
        <tr>
        <td width="45%"  >
            <fieldset>
              <legend><b>S&oslash;g</b></legend>
                <input type="text" size="40" id="editPresentSogLogo">
            </fieldset>
        <fieldset>
        <legend><b>Logo list</b></legend>

          <div id="editPresentlogoListContainer">

          </div>


        </fieldset>
        <!--
           <fieldset>
            <legend><b>Opret nyt Logo</b></legend>
                <input type="text" id="logoCreateText" /><button onclick="logo.create()">Benyt og gem logo</button> <br />  <br />
                <form action="upload.php" class="dropzone"></form>
            </fieldset>
            -->
        </td>
        <td width="10%"></td>
        <td width="45%" valign="top">
        <fieldset>


        <legend><b>Valgt logo</b></legend>
        <!-- <button onclick="showlogoDialog()">Vis logo liste</button> --> <button onclick="resetLogo()">Nulstil logo</button>
        <!--
        <div style="height:175px;" id="selectedLogo">
             <div class="logo-img" data-id="logo/intet.jpg" style="background-image: url(views/media/logo/intet.jpg);"> </div>
        </div>
        -->

        <div style="height:175px;" id="selectedLogo"><select class="log-admin-size" style="display:none;"> <option value="1">Lille</option>  <option value="2" selected="">Medium</option>  <option value="3">Stor</option>  <option value="4">Størst</option></select><div class="logo-img" style="background-image: url(views/media/logo/fgpme344jmlmtbl00j5u.jpg);" data-id="logo/fgpme344jmlmtbl00j5u.jpg" logosize="2"> </div><br></div>






        </fieldset>
        </td>
        </tr>
        </table>
        <br><hr />
        </div>
    	<div id="tabs-4">



        <div id="presentVariantTabs" class="gaveAdminTab">
	        <ul>
		        <li><a href="#tabsVari-1" class="headline">Dansk</a></li>
		        <li><a href="#tabsVari-2" class="headline">Engelsk</a></li>
		        <li><a href="#tabsVari-3" class="headline">Tysk</a></li>
		        <li><a href="#tabsVari-4" class="headline">Norsk</a></li>
		        <li><a href="#tabsVari-5" class="headline">Svensk</a></li>
              <!--  <li style="float:right;"><img class="icon" src='views/media/icon/Upload_to_the_Cloud_50.png' title="Opret ny variant" onclick='variant.showUploadDialog()' height='25' width='25'></li>  -->
            </ul>
            <div id="tabsVari-1"><div id="tabsVari-dk" class="presentsVariant" data-id="1">
            <table class="tableStyle1" style="width: 950px;">
                <tr ><th>Produktnavn</th><th>Variant / farve (Valgfri)</th><th>Varenr./sampaknr.</th><th>Billede</th><th></th></tr>

                <tr id="variantNew">
                    <td><input  type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td ><img src="views/media/type/blank.jpg" /></td>
                    <td>
                        <img class="icon" src='views/media/icon/1373253494_plus_64.png' title="Opret ny variant" onclick='variant.addNew()' height='25' width='25' style="margin-left:5px;">
                        <img class="icon" src='views/media/icon/bill.png' title="V&oelig;lg billede" onclick='variant.showUploadDialog("0")' height='30' width='30'>
                    </td>

                </tr>
            </table>
            </div> </div>
    	    <div id="tabsVari-2"><div id="tabsVari-en" class="presentsVariant"  data-id="2"><table class="tableStyle1"><tr ><th>Gave navn</th><th>Model</th><th>Varenr./sampaknr.</th><th></th></tr></table></div></div>
	        <div id="tabsVari-3"><div id="tabsVari-de" class="presentsVariant" data-id="3"><table class="tableStyle1"><tr ><th>Gave navn</th><th>Model</th><th>Varenr./sampaknr.</th><th></th></tr></table></div></div>
	        <div id="tabsVari-4"><div id="tabsVari-no" class="presentsVariant" data-id="4"><table class="tableStyle1"><tr ><th>Gave navn</th><th>Model</th><th>Varenr./sampaknr.</th><th></th></tr></table></div></div>
	        <div id="tabsVari-5"><div id="tabsVari-sv" class="presentsVariant" data-id="5"><table class="tableStyle1"><tr ><th>Gave navn</th><th>Model</th><th>Varenr./sampaknr.</th><th></th></tr></table></div></div>

        </div>



    </div>
    	<div id="tabs-6">
            <?php include("ptAdmin_view.php"); ?>
        </div>

</div>
<div id="logoDialog_logo" title="Logo liste" style="display:none;">
  <table width=100%>
  <tr>
    <td>
    <input type="text" size="20" id="sogLogo" /><button onclick="logo.searchLogoList()">S&oslash;g</button>

    </td>
  </tr>
  <tr>
    <td id="logoList">
   <!--     <div class="logo-img" style="background-image: url(views/media/logo/18103373.png);"> </div>  -->


    </td>
  </tr>
  </table>
</div>
<div id="logoDialog" title="Logo liste" style="display:none;"></div>



<div id="logoDialog2" style="display:none"></div>

<script src="views/js/variant.js?4dd22d2"></script>
<script src="views/js/logo.js"></script>
<script>
var _dropTarget = "";

//var myDropzone = new Dropzone(".dropzone", { url: "index.php?rt=upload/logo"});

//tinymce.init({ selector:'textarea' });
$("#giVareList").html('<textarea rows="4" cols="40" id="presentsSubGiftList"></textarea >')
$(window).resize(function()
  {
    $(".gaveAdminTab").css("height", $(document).height()-300 +"px");
  }
)

$("#presentsTabs").tabs(
  {
  create: function(e, ui) {
      $(".gaveAdminTab").css("height", $(document).height()-300 +"px");
    }
  }
);

$("#presentDescriptionTabs").tabs(
);

$("#presentVariantTabs").tabs();
$("#sortable").sortable();
$("#sortable").disableSelection();

// data,callUrl,returnCall,returnElement
var _tempLogoFilename;







// data,callUrl,returnCall,returnElement

function showlogoDialog()
{
  $("#logoList").html("");
  ajax({type: "2"}, "media2/getAllOnType", "logo.showLogoList");
  /*
 $( "#logoDialog" ).dialog({
  resizable: true,
  width:700,
  height:400
 } );
 */
}
function resetLogo()
{
  var html = '<div class="logo-img" data-id="logo/intet.jpg" style="background-image: url(views/media/logo/intet.jpg);"> </div>';
  $("#selectedLogo").html(html);
}


var _selectVideoThump = "";
var videoList =[
  {name: "Donation", path: "40"},
  {name: "Bang og olufsen1", path: "1"},
  {name: "Bang og olufsen2", path: "5"},
  {name: "KitchenAid_2015", path: "2"},
  {name: "Royal Copenhagen1", path: "3"},
  {name: "Royal Copenhagen2", path: "4"},
  {name: "DAY", path: "6"},
  {name: "Comwell", path: "8"},
  {name: "Damask", path: "9"},
  {name: "Wallz", path: "10"},
  {name: "Wallz", path: "14"},
  {name: "Georg Jensen", path: "11"},
  {name: "Iittala", path: "12"},
  {name: "kahler kande", path: "13"},
  {name: "Fossil", path: "15"},
  {name: "Skagen ur", path: "16"},
  {name: "Bamix", path: "17"},
  {name: "Weber", path: "23"},
  {name: "KitchenAid_blender", path: "24"},
  {name: "KitchenAid_elkedel", path: "25"},
  {name: "KitchenAid_kaffemaskine", path: "26"},
  {name: "KitchenAid_kaffestempel", path: "27"},
  {name: "KitchenAid_Slice_toaster", path: "31"},
  {name: "Chokolade_vordingborg", path: "30"},
  {name: "DFDS_familie_paa_tur", path: "28"},
  {name: "DFDS_par_paa_tur", path: "29"},
  {name: "kettlebelts", path: "32"},
  {name: "Nespresso", path: "34"},
  {name: "Kay_Bojesen", path: "33"},
  {name: "Minihakker", path: "35"},
  {name: "Artisan_brygger", path: "36"},
  {name: "Coffee_to_go", path: "37"}



];
function showVideoList()
{


  $("#logoDialog2").html("");
  var html = "";
  for(var i = 0; videoList.length > i; i++)
  {
    html += "<div class=\"videoList\"><img style=\"margin-left:10px;\" data-id=\""+videoList[i].path+"\" width=25 height=25 onclick=\"selectVideo(this)\" src=\"views/media/icon/1373253494_plus_64.png\"  /><span>"+videoList[i].name+"</span><div class=\"thumpVideo\"  id=\"videoThump_"+videoList[i].path+"\"><img data-id=\""+videoList[i].path+"\" onclick=\"playThumpVideo(this)\" src=\"views/media/icon/Film-48.png\" /></div></div>"
  }
  $("#logoDialog2").html(html);

  $("#logoDialog2").dialog(
    {
    resizable: true,

    close: function(event, ui) {$(".thumpVideo").find('video').remove();},
    width: 700,
    height: 400
    }
  );
}
function playThumpVideo(element)
{
  $(".thumpVideo").find('video').remove();
  if(_selectVideoThump == "") {
    _selectVideoThump = $(element).attr("data-id")
  } else {
    var html = "<img data-id=\""+_selectVideoThump+"\" onclick=\"playThumpVideo(this)\" src=\"views/media/icon/Film-48.png\" />";
    $("#videoThump_"+_selectVideoThump).html(html);
    _selectVideoThump = $(element).attr("data-id")
  }
  var videoHtml = "<img data-id=\""+_selectVideoThump+"\" onclick=\"stopThumpVideo(this)\" src=\"views/media/icon/Stop-48.png\" /><video class=\"videoPlay\" controls=\"controls\" autoplay=\"autoplay\" style=\"height:150px; float:right; margin-top:-30px;\"><source src=\"views/media/video/"+$(element).attr("data-id")+"/Videolink.m4v \" type=\"video/mp4\"></video>";
  $("#videoThump_"+_selectVideoThump).html(videoHtml);
}
function stopThumpVideo(element)
{
  $(".thumpVideo").find('video').remove();
  $("#videoThump_"+$(element).attr("data-id")).html("");
  _selectVideoThump = "";
  var html = "<img data-id=\""+$(element).attr("data-id")+"\" onclick=\"playThumpVideo(this)\" src=\"views/media/icon/Film-48.png\" />";
  $("#videoThump_"+$(element).attr("data-id")).html(html);
}
function selectVideo(element)
{
  var html = '<li class="ui-state-default"><div style="position: absolute; margin-left:20px; color:red; font-size:0.9em;"  >'+$(element).attr("data-id")+'</div><div class="sort-img presentAdminImg" data-id="video_'+$(element).attr("data-id")+'" style="background-image: url(views/media/icon/Film-48.png);"><img style="position: relative;  left: 45px; top: -60px;" class="icon" src="views/media/icon/1373253296_delete_64.png"  onclick="removePresentImgFromList(this)" height="25" width="25"></div></li>';
  $("#sortable").append(html);
  $("#sortable").sortable();
  $("#sortable").disableSelection();
  alert("Video tilføjet listen")
}




function controlDropElemet(activeElementName)
{

  var obj = JSON.parse(activeElementName);

  _tempLogoFilename = obj.newName;
}
function addPresentImgToList(name)
{
  var html = '<button onclick=\"showVideoList()\">Vis video liste</button> <br /><br /> <iframe src="views/uploadPresentImg_view.php" scrolling="no" width=100% height=90% frameborder="0"></iframe>'
  $("#presentDropFrame").html("");
  $("#presentDropFrame").html(html);

  var html = '<li class="ui-state-default"><div class="sort-img presentAdminImg" data-id="'+name+'" style="background-image: url(views/media/user/'+name+'.jpg);"> <img style="position: relative;  left: 45px; top: -60px;" class="icon" src="views/media/icon/1373253296_delete_64.png"  onclick="removePresentImgFromList(this)" height="25" width="25"></div></li>';
  $("#sortable").append(html);
  $("#sortable").sortable();
  $("#sortable").disableSelection();
}
function removePresentImgFromList(element)
{
  $(element).parent().parent().remove()
}



function randomString(len, charSet) {
  charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  var randomString = '';
  for (var i = 0; i < len; i++) {
    var randomPoz = Math.floor(Math.random() * charSet.length);
    randomString += charSet.substring(randomPoz, randomPoz+1);
  }
  return randomString;
}

$(document).ready(function()
  {

    tinymce.init(
      {
      mode: "specific_textareas",
      editor_selector: "presentDescriptionText",
      height: 250,
      plugins:[
          'advlist autolink lists link image charmap print preview anchor',
          'searchreplace visualblocks code fullscreen',
          'insertdatetime media table contextmenu paste code'
        ],
      toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'

      }
    );
    if(gaveEditData_ == "") {
      gaveAdmin.preCreatePresent();
    }

  }
);
function loadPt(){
  new ptAdminClass(_selectedPresent).init();
}

</script>
