<?php
// valgshop-gaveliste_view.php
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

$empty = "Intet Søgeresultet";
foreach($presents as $present){
   $empty = "";
   $go = false;

   $budgetPrice = "N/A";
   $costPrice = "N/A";

   $pim = $present->attributes["pim_id"] == 0 ? "":"(PIM) ";
   if($present->attributes["copy_of"] != "0" && $copy == "nocopy"   ){
        $go = true;
   } elseif($present->attributes["copy_of"] == "0" && $copy != "copy" ){
        $go = true;
   }
   $Instock =  $present->attributes["in_stock"] == "1" ? true:false;

//print_r($present);

    $presentTitle =  $present->attributes["nav_name"];
    if( $localisation != 4  ){
        $presentTitle =  $present->attributes["nav_name"];
        $budgetPrice = $present->attributes["price_group"] == "" ? "N/A" : $present->attributes["price_group"];
        $costPrice = $present->attributes["price"] == "" ? "N/A" : $present->attributes["price"];
    }
   if( $localisation == 4){
       $presentTitle =  $present->descriptions[3]->attributes["caption"];
       $budgetPrice = $present->attributes["price_group_no"] == "" ? "N/A" : $present->attributes["price_group_no"];
       $costPrice = $present->attributes["price_no"] == "" ? "N/A" : $present->attributes["price_no"];
   }
    $itemno = $present->models[0]->attributes["model_present_no"] != "" ? "<u>".$present->models[0]->attributes["model_present_no"]."</u>: " : "";

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

    $presentMedia ="";
    $InstockHtml = $Instock == false ? "<div style='color:red; float: left;margin-top: -15px;'>Varen er udsolgt</div>":"";
    if($go == true){


         if(is_array($present->present_media) && countgf($present->present_media) > 0){
            //if($present->present_media[0]->media_path !== null ){
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
            $html.= "<b><p class=\"present_no\">".$itemno.$pim.$presentTitle."</p></b>
            <hr />
             
                <div class=\"flip-img\" style=\"background-image: url(views/media/user/".$presentMedia.".jpg);\">
            </div>
             ".$InstockHtml."
            <div style='position: absolute;bottom: 10px;'>Budgetpris: ".$budgetPrice."   |   Kostpris: ".$costPrice."</div>
        </div>
		<div class=\"back\">
		   <div style=\"overflow-y:auto;  text-align: left; height: 240px; \">
                  <strong>".$itemno."<br><p class=\"caption\" style=\"display:none;\">1".$present->descriptions[$localisation-1]->attributes["caption"]." </p></strong>
                  <p class=\"short_description\" style=\"display:none;\">2".base64_decode( $present->descriptions[$localisation-1]->attributes["short_description"] )."</p>
                  <div class=\"long_description_".$present->id."\" >".base64_decode( $present->descriptions[$localisation-1]->attributes["long_description"] )."</div>
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
                    <td style='display: none'><img title=\"Vis detaljer\"  onclick=\"presentFront.showDetalDescription('".$present->id."' )\" src=\"views/media/system/document.ico\" class=\"mouse\" width=\"25\" height=\"25\" ></td>";
           $html.= " </tr>

           </table>
		</div>
	</div>
</div>" ;

$htmlAll.=$html;

}

}
if($htmlAll == ""){
   $htmlAll = "<p></p><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />";
}
echo $htmlAll;
?>

