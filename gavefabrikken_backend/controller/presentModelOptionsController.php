<?php
class presentModelOptionsController Extends baseController {

  public function Index() {

  }
  public function loadOption()
  {
     $presentID = $_POST["presentID"];
     $shopID = $_POST["shopID"];

      //$deadlineRS = Dbsqli::getSql2( "SELECT DISTINCT expire_date  FROM `gift_certificate` where reservation_group in ( select reservation_group  from shop where id = ".intval($shopID).") or shop_id = ".intval($shopID)." order by expire_date");
      $deadlineRS = Dbsqli::getSql2("SELECT expire_date FROM `expire_date` where id in (select expire_date_id from cardshop_expiredate where shop_id = ".intval($shopID).") order by expire_date");
      $presentToHideRS = Dbsqli::getSql2( "SELECT expire_data  FROM `present_model_options` where present_id =".$presentID);
      
     $returnData = [];
     $hideWeeks = [];
     foreach($presentToHideRS as $tohide){
        $hideWeeks[] = $tohide["expire_data"];
     }
     foreach($deadlineRS as $deadline){
        if(in_array($deadline["expire_date"],$hideWeeks) ){
           $returnData[$deadline["expire_date"]] = $deadline["expire_date"];
        } else {
           $returnData[$deadline["expire_date"]] = 0;
        }

     }
     response::success(json_encode($returnData));
  }
  public function updateOption(){
      $presentID = $_POST["presentId"];
      $deadline= $_POST["deadline"];
      $optionRs = Dbsqli::getSql2( "SELECT id,visibility  FROM `present_model_options` where present_id = ".$presentID." and expire_data ='".$deadline."'");
      if(count($optionRs) > 0){
         $sql ="DELETE FROM `present_model_options` WHERE id=".$optionRs[0]["id"];
        Dbsqli::setSql2($sql);
      } else {
         Dbsqli::setSql2( "INSERT INTO present_model_options (present_id, expire_data, visibility) VALUES (".$presentID." , '".$deadline."', 1)");
      }
      response::success(json_encode([]));
  }

}

?>