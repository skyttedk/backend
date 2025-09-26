<?php
 error_reporting(E_ALL);
ini_set('display_errors', 1);

$go = false;


//print_r( $presents);

/*        "used_on_shop"
        "has_orders"
        "has_variants"
        "is_variant":
*/
$htmlAll = "";

foreach($presents as $present)
{
   $go = false;
   if($present->attributes["copy_of"] != "0" && $copy == "nocopy"   ){
        $go = true;
   } elseif($present->attributes["copy_of"] == "0" && $copy != "copy" ){
        $go = true;
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
    if(isset($present->present_media[0])){
      $presentMedia =  $present->present_media[0]->media_path;
    }

    $html = "<div class=\"flip-container\" id=\"presentFlipId_".$present->id."\" ontouchstart=\"this.classList.toggle('hover')\">
	<div class=\"flipper\">
		<div class=\"front\">";
            if($present->has_variants() == true ){
              $html.= "<img src=\"views/media/icon/flow.png\" width=\"25\" class=\"isVariant\" />";
            }
            if($present->is_variant() == true){
             $html.= "<img src=\"views/media/icon/flow2.png\" width=\"25\" class=\"isVariant\" /> <script> $(\"#gaveAdminBack\").show() </script> ";

            }
            $html.= "<b><p class=\"present_no\">".$present->attributes["nav_name"]."</p></b>
            <hr />
                <div class=\"flip-img\" style=\"background-image: url(views/media/user/".$presentMedia.".jpg);\">
            </div>
        </div>
		<div class=\"back\">
		   <div style=\"overflow-y:auto;  text-align: left; height: 240px; \">
                  <strong><p class=\"caption\">".$present->descriptions[0]->attributes["caption"]." </p></strong>
                  <p class=\"short_description\">".base64_decode( $present->descriptions[0]->attributes["short_description"] )."</p>
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
                    <td><img title=\"Vis hvilke shop gaven benyttes i\" onclick=\"valgshopGaver.useInOtherShops('".$present->id."' )\"  src=\"views/media/icon/gave.png\" class=\"mouse\" width=\"25\" height=\"25\"></td>
                    <td><img title=\"preview\" onclick=\"presentFront.preview('".$presentMedia."','".$present->id."','".$present->attributes["present_no"]."','".$present->attributes["name"]."','".$variant."' )\"  src=\"views/media/icon/if_Preview_40079.png\" class=\"mouse \" width=\"25\" height=\"25\"></td>
                    <td id=\"addPresent_".$present->id."\"><img title=\"Tilfoj til shoppen\" onclick=\"valgshopGaver.addToShop('".$present->id."')\"  src=\"views/media/icon/1373253494_plus_64.png\"  class=\"mouse \" width=\"25\" height=\"25\"></td>
                    <td><img title=\"Vis detaljer\"  onclick=\"presentFront.showDetalDescription('".$present->id."' )\" src=\"views/media/system/document.ico\" class=\"mouse\" width=\"25\" height=\"25\" ></td>";
           $html.= " </tr>

           </table>
		</div>
	</div>
</div>" ;

$htmlAll.=$html;

}

}
if($htmlAll == ""){
   $htmlAll = "<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />";
}
echo $htmlAll;
?>

