<style>
#global-present-list{
  overflow-y: scroll;
}


</style>

<?php


// presentAdminHeadlist_view.php
 error_reporting(E_ALL);
ini_set('display_errors', 1);

  echo '<script src="views/js/logo.js?'.rand(10,100).'"></script>';

 echo $searchMasin = "<div style=\"width:95%; height:30px;  \">


<div class=\"searchPresentContainer\">

<table width=100%>
 <tr>
 <td > <input type='radio' class='presenttypesearch' name='presenttypesearch' id='searchPresents' value='searchPresents' checked><label>Standard</label>   <input type='radio' id='searchVariants' class='presenttypesearch' name='presenttypesearch' value='searchVariants'><label style='margin-right:5px;'>Fra PIM</label></td>
 <td ><span id=\"searchPresentMsg\" style=\"color:red; display:none;\">S&oslash;ger</span> <input  id=\"searchPresentHeadview\"  onclick=\"regEventAction('event_AdminGaverSog')\"  style=\"   padding: 5px; width:200px; font-size:0.9em; height=32px; \" type=\"text\" /><button   onclick=\"presentFront.sog()\" style=\"font-size:0.9em; height:31px; \">S&oslash;g</button><button   onclick=\"presentFront.sogDeleted()\" style=\"font-size:0.9em; height:31px;margin-left:15px; \">S&oslash;g slettede</button></td>
 <td> <button  onclick=\"presentFront.sogAll()\" style=\"font-size:0.9em; height:31px; \">Vis alle Standard Gaver</button></td>
  <td> <a target='_blank' href='".GFConfig::BACKEND_URL."thirdparty/klippe'>Klippeværktøj</a></td>
 <td> <button  onclick=\"logo.showLogoAdmin()\" style=\"font-size:0.9em; height:31px; \">Logo Admin</button> </td>
 </tr>
 </table>

  <hr />
</div>
</div><br />
<div id=\"global-present-list\">

";

$go = false;


//print_r( $presents);

/*        "used_on_shop"
        "has_orders"
        "has_variants"
        "is_variant":
*/
// build pressent list (menu GaveAdmin)

//print_r($presents);

if(sizeofgf($presents) == 0){
    echo "<h1>Ingen gaver matchede din søgning</h1>";
}
foreach($presents as $present)
{
   $go = false;


   if($present->attributes["copy_of"] != "0" && $copy == "nocopy"   ){
        $go = true;
   } elseif($present->attributes["copy_of"] == "0" && $copy != "copy" ){
        $go = true;
   }


   $pimPresent = "(PIM) ";

   if($present->pim_id == "" || $present->pim_id == null || $present->pim_id == "null"){
       $pimPresent = "";

   }

    //echo $present->Present->attributes["id"];
    /*
     print_R($present->descriptions[0] );
     $present->present_no ."<br />";
     $present->name ."<br />";
     $present->descriptions[0]->attributes["id"] ;
     $present->descriptions[0]->attributes["present_id"] ;
     $present->descriptions[0]->attributes["short_description"] ;
     $present->descriptions[0]->attributes["caption"] ;
     $present->present_media[0]->media->path;
     */
     if($go == true){
	 $presentMedia ="";
     if(is_array($present->present_media) && countgf($present->present_media) > 0){
    //if($present->present_media[0]->media_path !== null ){
        $presentMedia =  $present->present_media[0]->media_path;
     }

    echo "<div id=\"presentAdminContainer\">";
    $html = "<div class=\"flip-container\" data-id=\"".$present->id."\" onmouseover=\"loadPresentNumber(".$present->id.")\"  id=\"".$present->id."\" ontouchstart=\"this.classList.toggle('hover');\">
	<div class=\"flipper\">
		<div class=\"front\">";

            if($present->has_variants() == true ){
              $html.= "<img src=\"views/media/icon/flow.png\" width=\"25\" class=\"isVariant\" />";
            }
            if($present->is_variant() == true){
             $html.= "<img src=\"views/media/icon/flow2.png\" width=\"25\" class=\"isVariant\" /> <script> $(\"#gaveAdminBack\").show() </script> ";

            }



           $html.= "<b><p class=\"present_no\"   id=\"present_no_".$present->id."\"  >".$pimPresent.$present->attributes["nav_name"]."</p></b>

            <div class=\"present_name\" style=\"display:none;\" >".$present->attributes["name"]."</div>
            <hr />
                <div class=\"flip-img\" style=\"background-image: url(views/media/user/".$presentMedia.".jpg);\">
            </div>



        </div>
		<div class=\"back\">
		   <div style=\"overflow-y:auto;  text-align: left; height: 240px; \">
		          <div id=\"present_sync_".$present->id."\" ></div>
                  <div id=\"present_all_no_".$present->id."\" ></div>
                
              
                  
                  <strong><p class=\"caption\"  id=\"caption_".$present->id."\" >".$present->descriptions[0]->attributes["caption"]." </p></strong>
                  <div id=\"short_description_".$present->id."\" ><p class=\"short_description\">".base64_decode( $present->descriptions[0]->attributes["short_description"] )."</p></div>
                  <div class=\"long_description_".$present->id."\" style=\"display:none;\">".base64_decode( $present->descriptions[0]->attributes["long_description"] )."</div>

           </div>
           <hr />



           <table border=0 width=\"220\">
                   <tr align=\"right\" valign=\"top\" width=\"150\">";

            if($present->has_variants() == true){
                    $html.= " <td><img title=\"Vi gavens varianter\"  onclick=\"copy.searchCopy('".$present->id."' )\"  src=\"views/media/icon/Fine Print-48.png\" class=\"mouse  \" width=\"30\" height=\"30\"></td>

                    <td></td>";
            }
                $variant = "not";
                if($present->has_variants() == true){
                    $variant = "not";
                }


              $html.= "
                    <td><img title=\"Vis hvilke shop gaven benyttes i\" onclick=\"presentFront.showGiftUsedInShops('".$present->id."' )\"  src=\"views/media/icon/gave.png\" class=\"mouse noShop\" width=\"25\" height=\"25\"></td>
                    <td><img title=\"preview\" onclick=\"presentFront.preview('".$presentMedia."','".$present->id."','".$present->attributes["present_no"]."','".$present->attributes["name"]."','".$variant."' )\"  src=\"views/media/icon/if_Preview_40079.png\" class=\"mouse noShop\" width=\"25\" height=\"25\"></td>
                    <td><img title=\"Tilfoj til shoppen\" onclick=\"addToShop('".$presentMedia."','".$present->id."','".$present->attributes["present_no"]."','".$present->attributes["name"]."','".$variant."' )\"  src=\"views/media/icon/1373253494_plus_64.png\" style=\"display:none;\" class=\"mouse Shop\" width=\"25\" height=\"25\"></td>
                    ";
                    if($present->pim_id == 0) {
                        $html .= "<td><img title=\"Opret ny copi\" onclick=\"copy.makeNewCopy('" . $present->id . "','" . $present->attributes["nav_name"] . "' )\"  src=\"views/media/icon/Copy-48.png\"  class=\"mouse noShop \" width=\"25\" height=\"25\"></td>";
                    }
                    $html .= "    <td><img title=\"Vis detaljer\"  onclick=\"presentFront.showDetalDescription('".$present->id."' )\" src=\"views/media/system/document.ico\" class=\"mouse\" width=\"25\" height=\"25\" ></td>
                    <td><img title=\"Redigere gaven\" onclick=\"gaveAdmin.editGift('".$present->id."' )\"  src=\"views/media/system/pencil.ico\" class=\"mouse noShop\" width=\"25\" height=\"25\"></td>
                    ";
                    if( $present->deleted == 1 ){
                        $html.= "<td><img title=\"Slet gaven\" onclick=\"presentFront.undoDelete('".$present->id."' )\"  src=\"views/media/system/undo.png\" class=\"mouse noShop\" width=\"25\" height=\"25\"></td>";
                    } else {
                        $html.= "<td><img title=\"Slet gaven\" onclick=\"presentFront.deleteItem('".$present->id."' )\"  src=\"views/media/system/trash.ico\" class=\"mouse noShop\" width=\"25\" height=\"25\"></td>";
                    }

           $html.= " </tr>

           </table>
		</div>
	</div>
</div>" ;

echo $html;

}
echo "</div>";
}
echo "</div>";
?>

<script>

$("#overskriftValgtShop").html("");
// styrer scroll højden på gaveFront listen
   $( document ).ready(function() {
   $("#global-present-list").height(( $(window).height() -200)) ;
   $( window ).resize(function() {
        $("#global-present-list").height(( $(window).height() -200)) ;
   })


});




/*
    $(document).keypress(function (e) {
    if (e.which == 13) {
        presentFront.sog()
    }
});
*/
</script>
<style>
#presentDetailBox{
   top:100px;
        position:absolute;
        float:left;
        overflow: hidden;
        background: white;
        width: 97%;
       height: calc(100% - 100px);
        margin-top:-60px;
        display:none;
        background-color: #EEEEEE;

}
#presentDetailSaveBtn{
  float: right;
  margin-right:10px;
}



 #logoAdminBox{
   top:100px;
        position:absolute;
        float:left;
        overflow: hidden;
        background: white;
        width: 97%;
       height: calc(100% - 100px);
        margin-top:-60px;
        display:none;
        background-color: #EEEEEE;

}


#logoAdminBoxHead{

        height: 50px;
        border: 1px solid #a29415;
}
#logoAdminCallToAction h3{
    float: left;

}
#logoAdminBoxContent table {
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;

	}
#logoAdminBoxContent tr {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
#logoAdminBoxContent td {
		border:1px solid #C0C0C0;
		padding:5px;
    }
#logoAdminBoxContent tr:nth-child(even) {background-color: white;}

#logoAdminBoxContentLeft{
    height: 800px;
    overflow-y: scroll;
    font-size: 14px !important;
}



</style>
<div id="presentDetailBox">




</div>



    <div id="logoAdminBox">

        <div id="logoAdminBoxHead">
        <table width=100% border=0>
        <tr bgcolor="#FFCC66">
        <td width="30%" bg><center><h3>Logo administation</h3></center></td>
        <td width="50%"></td>

         <td width="5%" id="logoAdminCallToAction"><img style="cursor: pointer;" onclick="logo.hideLogoAdmin()" src="views/media/icon/1373253296_delete_64.png" height="25" width="25" alt="" />  </td>
       <!-- <td width="5%">sdg <img style="cursor: pointer;" onclick="valgshopGaver.closeEdit()" src="views/media/icon/1373253296_delete_64.png" height="25" width="25" alt="" /> </td> -->
        </tr>

        </table>
        <h3></h3></div>
        <div id="logoAdminBoxContent">
        <table width=90% height=100%>
        <tr height=50>
            <td>
            <fieldset>
              <legend><b>Søg</b></legend>
                <input type="text" size="40" id="sogLogo">
            </fieldset>

            </td>           <td><div id="logAdmin-msg" style="color:green;font-size:20px;"></div> </td>
        </tr>
        <tr>

            <td valign=top width=50%>
            <fieldset>
              <legend><b>LOGO</b></legend>
               <div class="inline" id="logoAdminBoxContentLeft"></div>
              </fieldset>
            </td>

            <td valign=top width=50%><div class="inline" id="logoAdminBoxContentRight">
              <fieldset>
              <legend><b>Opret nyt Logo</b></legend>
              <br />
              <div style="display:none;">
               <div style="width: 100px; ">Logo st&oslash;rrelse: </div><select id='elementSize' > <option value='1'>Lille</option>  <option value='2' selected>Medium</option>  <option value='3' >Stor</option>  <option value='4'>St&oslash;rst</option></select><br /> <br />
               </div>
                 <div  style="width: 100px; display: inline-block;">S&oslash;geord: </div><input id="logoAdminSearchWords" type="text"  size="50" />  <br /> <br />
                <div  id="dropzoneCreateContainer">
                    <form  id="dropzoneCreate" action="upload.php" class="dropzone"></form></br>
                </div>

                <button onclick="logo.createAdmin()">Benyt og gem logo</button> <br />  <br />
            </fieldset>



            </div>  </td>
        </tr>

        </table>


        </div>
    </div>

<div id="logoDialog" title="Detaljeret beskrivelse" style="display:none;">
 <div id="previewPresent" title="Preview present" style="display:none;"></div>
</div>


<div id="logoAdmin" style="display:none"></div>
<script>
var old = "";
function loadPresentNumber(id){

    //function bliver kaldt mange gange der tjekkes der for at kun et requist sendes:
    if(old != id){
      old = id;
          $.getJSON("index.php?rt=present/getPresentsModel",{id:id}, function(res){
             var list = [];
             $.each(res.data, function(index, value){
                    list.push(value.attributes.model_present_no);
                 });
                 $("#present_all_no_"+id).html(list.join(" | ") + "<hr>" );
              loadSyncDato(id)

        });
    }
 }
 function loadSyncDato(id)
 {
     $.getJSON("index.php?rt=present/getPimSyncDato",{id:id}, function(res){
       if(res.data[0]["attributes"].pim_id == 0) return;
       let showData = res.data[0]["attributes"].formatted_pim_sync_time === null ? "N/A":res.data[0]["attributes"].formatted_pim_sync_time;
       $("#present_sync_"+id).html("Pim sync: "+showData);




     });
 }

</script>