<?php
// presentAdminHeadlist_variant_view.php
 error_reporting(E_ALL);
ini_set('display_errors', 1);
// <button id=\"gaveAdminBack\" style=\"float:left; color:red; display:none;\" onclick=\"gaveAdmin.show()\">TILBAGE</button>
echo $searchMasin = "<div style=\"width:95%; height:30px;  \">
<div class=\"searchPresentContainer\">

<table width=700>
 <tr>
 <td> <input type='radio' class='presenttypesearch' name='presenttypesearch' id='searchPresents' value='searchPresents' ><label>Standard</label>   <input type='radio' checked id='searchVariants' class='presenttypesearch' name='presenttypesearch' value='searchVariants'><label style='margin-right:5px;'>Fra PIM</label></td>
 <td> <input id=\"searchPresentHeadview\"   style=\"   padding: 5px; width:200px; font-size:0.9em; height=32px; \" onclick=\"setEnterFocus('present')\"  type=\"text\" /><button   onclick=\"presentFront.sog()\" style=\"font-size:0.9em; height:31px; \">S&oslash;g</button></td>
 <td> <button  onclick=\"presentFront.sogAll()\" style=\"font-size:0.9em; height:31px; \">Vis alle Standard Gaver</button></td>
 </tr>
 </table>

  <hr />
</div>
</div><br />

";

$go = false;


//print_r( $presents);

/*        "used_on_shop"
        "has_orders"
        "has_variants"
        "is_variant":
*/

foreach($presents as $present)
{
   $go = false;


   if($present->attributes["copy_of"] != "0"   ){
        $go = true;
   }
    $go = true;
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

    $html = "<div class=\"flip-container\" style=\"display:none;\" data-id=\"".$present->id."\"  id=\"".$present->id."\" ontouchstart=\"this.classList.toggle('hover');\">
	<div class=\"flipper\">
		<div class=\"front\">";

            if($present->has_variants() == true ){
              $html.= "<img src=\"views/media/icon/flow.png\" width=\"25\" class=\"isVariant\" />";
            }
            if($present->is_variant() == true){
             $html.= "<img src=\"views/media/icon/flow2.png\" width=\"25\" class=\"isVariant\" /> <script> $(\"#gaveAdminBack\").show() </script> ";

            }



           $html.= "<b><p class=\"present_no\">".$present->attributes["present_no"]."</p></b>

            <div class=\"present_name\">".$present->attributes["name"]."</div>
            <hr />
                <div class=\"flip-img\" style=\"background-image: url(views/media/user/".$presentMedia.".jpg);\">
            </div>



        </div>
		<div class=\"back\">
		   <div style=\"overflow-y:auto;  text-align: left; height: 240px; \">
                  <strong><p class=\"caption\">".$present->descriptions[0]->attributes["caption"]."�</p></strong>
                  <p class=\"short_description\">".base64_decode( $present->descriptions[0]->attributes["short_description"] )."</p>
                  <div class=\"long_description_".$present->id."\" style=\"display:none;\">".base64_decode( $present->descriptions[0]->attributes["long_description"] )."</div>

           </div>
           <hr />



           <table border=0 width=\"220\">
                   <tr align=\"right\" valign=\"top\" width=\"150\">";

            if($present->has_variants() == true){
                    $html.= " <td><img onclick=\"copy.searchCopy('".$present->id."' )\"  src=\"views/media/icon/Fine Print-48.png\" class=\"mouse Shop \" width=\"30\" height=\"30\"></td>

                    <td></td>";
            }
                $variant = "not";
                if($present->has_variants() == true){
                    $variant = "not";
                }


              $html.= "<td><img onclick=\"addToShop('".$presentMedia."','".$present->id."','".$present->attributes["present_no"]."','".$present->attributes["name"]."','".$variant."' )\"  src=\"views/media/icon/1373253494_plus_64.png\" style=\"display:none;\" class=\"mouse Shop\" width=\"30\" height=\"30\"></td>
                    <td><img onclick=\"copy.makeNewCopy('".$present->id."','".$present->attributes["name"]."' )\"  src=\"views/media/icon/Copy-48.png\"  class=\"mouse noShop \" width=\"30\" height=\"30\"></td>
                    <td><img  onclick=\"presentFront.showDetalDescription('".$present->id."' )\" src=\"views/media/system/document.ico\" class=\"mouse noShop\" width=\"30\" height=\"30\" ></td>
                    <td><img onclick=\"gaveAdmin.editGift('".$present->id."' )\"  src=\"views/media/system/pencil.ico\" class=\"mouse noShop\" width=\"30\" height=\"30\"></td>
                    <td><img onclick=\"presentFront.deleteItem('".$present->id."' )\"  src=\"views/media/system/trash.ico\" class=\"mouse noShop\" width=\"30\" height=\"30\"></td>";





           $html.= " </tr>

           </table>
		</div>
	</div>
</div>" ;

echo $html;

}
}

?>

<script>
$("#overskriftValgtShop").html("");
/*
    $(document).keypress(function (e) {
    if (e.which == 13) {
        presentFront.sog()
    }
});
*/
</script>
<div id="logoDialog" title="Detaljeret beskrivelse" style="display:none;">

</div>