<?php
class gfShopController Extends baseController
{


  public function Index() {
        echo "hej";
  }
  public function getPresentDescription() {
    $presentList = [];
    $mediaList = [];
    $returnData = [];
    $resPresent = Dbsqli::getSql2("SELECT present_description.caption,present_description.short_description,present_description.long_description,present_description.present_id,index_  FROM `present_description`
            inner join shop_present on
            shop_present.present_id =   present_description.present_id
            WHERE
            `shop_id` = 1235 and
            shop_present.active = 1 and
            present_description.language_id = 1
            order by shop_present.index_
            " );
            foreach($resPresent as $present){
              $presentList[] = $present["present_id"];
            }
            $resMedia = Dbsqli::getSql2("SELECT `present_id`,`media_path`  FROM `present_media` WHERE `present_id` in(".implode(",",$presentList)." ) order by `index`");
            foreach($resMedia as $media){
              $mediaList[$media["present_id"]][] = $media["media_path"];
            }
            $returnData["present"] = $resPresent;
            $returnData["media"] = $mediaList;
            response::success(json_encode($returnData));
  }
  public function getPresentMedia() {

  }


}

?>