<?php
// Controller PresentReservation
// Date created  Mon, 05 Sep 2016 20:36:22 +0200
// Created by Bitworks
class infoboardController extends baseController
{
    public function Index()
    {

    }
    public function getPresentExceededReservation()
    {
        $localisation = 1;
        $resultat = presentReservation::getPresentExceededReservation2();
      //  print_r($resultat);
        response::success(json_encode($resultat));
    }
    public function getGiftSelected()
    {
        $resultat = presentReservation::getGiftSelected();
        response::success(json_encode($resultat));
    }
    public function updateWarningLevel()
    {
        //echo $_POST["reservation_id"]."--".$_POST["warning_level"]."--".$_POST["quantity"];
        $reservation = PresentReservation::find($_POST['reservation_id']);
        $reservation->warning_level = $_POST["warning_level"];
        $reservation->quantity = $_POST["quantity"];
        $reservation->save();
        response::success(make_json("result", $reservation));

    }
    // ændret den 1810-2021
    public function getItemInShops_old()
    {
        $sql = "
        SELECT COUNT(`order`.`id`) as antal, shop.name, quantity FROM `order`
        inner join shop on `order`.shop_id = shop.id
        left join present_reservation on `order`.present_model_id = present_reservation.model_id
        WHERE `present_model_present_no` LIKE '".$_POST["itemId"]."' GROUP by `order`.shop_id";

        $rs = Dbsqli::getSql2($sql);
        response::success(json_encode($rs));

    //response::success(make_json("result", $rs));
   }





    public function getItemInShops()
    {

        $response = array();
        $sql = "
            SELECT COUNT(`order`.`id`) as antal FROM `order`  WHERE `present_model_present_no` LIKE '".$_POST["itemId"]."'";
        $rs = Dbsqli::getSql2($sql);
        $response["totalOrder"] = $rs;

       $sql = "
            SELECT count(`id`) as antal FROM shop_user
            inner join ( select distinct shop_id from `order` where `present_model_present_no` LIKE '".$_POST["itemId"]."') as p  on  shop_user.shop_id =  p.shop_id
            and  blocked = 0 and is_demo = 0";

        $rs = Dbsqli::getSql2($sql);
        $response["totalUser"] = $rs;

       $sql = "
            SELECT sum(quantity) as antal FROM present_reservation
            inner join ( select distinct shop_id, present_id,present_model_id from `order` where
             `present_model_present_no` LIKE '".$_POST["itemId"]."' and  `order_timestamp` > '2021-06-08 00:00:00' group by shop_id, present_id ,present_model_id) as p
            on
            present_reservation.shop_id =  p.shop_id and
            present_reservation.present_id =  p.present_id and
            present_reservation.model_id =  p.present_model_id

            ";
        $rs = Dbsqli::getSql2($sql);
        $response["totalReserve"] = $rs;

        response::success(json_encode($response));
   }
   private function getUserShop($shopID){
        $sql = "select count(id) as antal from shop_user where shop_id = ".$shopID." and blocked = 0 and is_demo = 0";
        $rs = Dbsqli::getSql2($sql);
        if(sizeofgf($rs) > 0){
            return $rs[0]["antal"];
        } else {
          return 0;
        }
   }
   private function getNumberOfReserved($modelID)
   {
        $sql = "SELECT * FROM `present_reservation` WHERE `model_id` = ".$modelID;
        $rs = Dbsqli::getSql2($sql);
        if(sizeofgf($rs) > 0){
            return $rs[0]["quantity"];
        } else {
          return 0;
        }


   }


}
?>



