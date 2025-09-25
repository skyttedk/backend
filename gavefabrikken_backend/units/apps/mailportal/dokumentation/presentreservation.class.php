<?php
// Model PresentReservation
// Date created  Mon, 16 Jan 2017 15:29:27 +0100
// Created by Bitworks
//***************************************************************
//   (PRI)  id                            int(11)             NO
//   (MUL)  shop_id                       int(11)             YES
//   (   )  present_id                    int(11)             YES
//   (   )  model_id                      int(11)             YES
//   (   )  model_no                      varchar(250)        YES
//   (   )  warning_level                 decimal
//   (   )  quantity                      int(11)             YES
//   (   )  order_quantity                int(11)             YES
//***************************************************************

class PresentReservation extends ActiveRecord\Model {
    static $table_name  = "present_reservation";
    static $primary_key = "id";
    static $calculated_attributes = array("is_test");
    static $before_save =  array('onBeforeSave');
    static $after_save =  array('onAfterSave');
    static $before_create =  array('onBeforeCreate');
    static $after_create =  array('onAfterCreate');
    static $before_update =  array('onBeforeUpdate');
    static $after_update =  array('onAfterUpdate');
    static $before_destroy =  array('onBeforeDestroy');  // virker ikke
    static $after_destroy =  array('onAfterDestroy');
    public function ordercount() {
       $orders = Order::all(array('company_id' => $this->company_id,'is_demo'=>0,'present_id' => $this->present_id,'present_model_present_no' => $this->model_no));
       return(count($orders));
    }

    // Trigger functions
    function onBeforeSave() {}
    function onAfterSave()  {}

    function onBeforeCreate() {
        $this->validateFields();
    }
    function onAfterCreate()  {}

    function onBeforeUpdate() {
        $this->validateFields();
    }

    function onAfterUpdate()  {}
    function onBeforeDestroy() {}
    function onAfterDestroy()  {
    }

    function validateFields() {
        $shop= Shop::find($this->shop_id);
        $present= Present::find($this->present_id);
        if(isset($this->model_id)) {
        if(!$this->model_id==0) {

           $presentmodel = PresentModel::find_by_model_id_and_present_id($this->model_id,$this->present_id);
        /*
            if(count($presentmodel)==0) {
             throw new exception("Invalid model id $this->model_id for this present");
          }
        */
        }
        }

    }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createPresentReservation($data) {
        $presentreservation = new PresentReservation($data);
        $presentreservation->save();
        return($presentreservation);
    }

    static public function readPresentReservation($id) {
        $presentreservation = PresentReservation::find($id);
        return($presentreservation);
    }

    static public function updatePresentReservation($data) {
        $presentreservation = PresentReservation::find($data['id']);
        $presentreservation->update_attributes($data);
        $presentreservation->save();
        return($presentreservation);
    }

    static public function deletePresentReservation($id,$realDelete=true) {
        if($realDelete) {
            $presentreservation = PresentReservation::find($id);
    		$presentreservation->delete();
          } else {  //Soft delete
            $presentreservation->deleted = 1;
            $presentreservation->save();
          }
    }

    static public function hasReservation($shop_id,$present_id,$model_id) {

     if($model_id==0)
       $presentreservation = PresentReservation::find_by_shop_id_and_present_id($shop_id,$present_id);
     else
       $presentreservation = PresentReservation::find_by_shop_id_and_present_id_and_model_id($shop_id,$present_id,$model_id);

      return($presentreservation);

    }
//---------------------------------------------------------------------------------------
// Custon Methods
//---------------------------------------------------------------------------------------

    static public function getPresentExceededReservation2() {

        $list =  PresentReservation::find_by_sql("select *, localisation ,present_reservation.id as present_reservation_id, shop.name as shop_name, shop.is_gift_certificate  from present_reservation
         INNER JOIN (SELECT `present_model_id`,`shop_id`,count(id) c FROM `order` GROUP by `shop_id`,`present_model_id`) `orderNy` on `orderNy`.shop_id = present_reservation.shop_id and `orderNy`.present_model_id = present_reservation.model_id
         left join present_model on `present_model`.model_id = present_reservation.model_id inner join shop ON shop.id = present_reservation.shop_id
         inner join shop_present on present_reservation.present_id = shop_present.present_id WHERE present_model.`model_id` != 0 and present_model.language_id = '1'  and present_model.active = 0 and shop.is_demo = 0 AND shop.active = 1 AND shop.deleted = 0 and shop.soft_close = 0 and present_reservation.quantity > 0 and present_model.active = 0 and shop_present.active = 1 and shop_present.is_deleted = 0 HAVING (present_reservation.quantity > -1)
            and ( CAST(warning_level + `orderNy`.c as signed) > quantity )
            ORDER BY present_reservation.`shop_id`, `present_reservation`.`model_id` DESC ");

        return $list;

        /*     and localisation = ".$localisation."
         // Old sql, was slow, updated by SC 07/10 2020 to the above
           $list =  PresentReservation::find_by_sql("select *,shop.name as shop_name from present_reservation
          INNER JOIN (SELECT `present_model_id`,`shop_id`,count(id) c FROM `order` GROUP by `shop_id`,`present_model_id`) `orderNy`
          on
              `orderNy`.shop_id =  present_reservation.shop_id and
              `orderNy`.present_model_id = present_reservation.model_id

          left join (SELECT * FROM `present_model` WHERE `model_id` != 0 and present_model.language_id = '1' and present_model.active = 0 ) `present_modelNy`
          on
          `present_modelNy`.model_id = present_reservation.model_id
          inner join shop
          ON
          shop.id = present_reservation.shop_id
          inner join shop_present
          on
          present_reservation.present_id = shop_present.present_id
          WHERE
          shop.is_demo = 0 AND
                          shop.is_gift_certificate = 0 AND
                          shop.active = 1 AND
                          shop.deleted = 0 and
                          shop.soft_close = 0 and
                          present_reservation.quantity > 0 and
                          present_modelNy.active = 0 and
                          shop_present.active = 1 and
                          shop_present.is_deleted = 0

          HAVING (present_reservation.quantity > 0 and (( quantity * (warning_level /100)) <= `orderNy`.c ))
          ORDER BY present_reservation.`shop_id`,  `present_reservation`.`model_id` DESC");
      
         return $list;

        */

        /*
        foreach($list as $key=>$val){
          print_R($val=>PresentReservation);
          15286
          15274
        }

          print_r($list);
          */
    }






    static public function getPresentExceededReservation() {

           $list =  PresentReservation::find_by_sql("select *,shop.name as shop_name,present.copy_of as present_copy_of,  present.name as present_name, shop_present.properties,shop.name from present_reservation
          INNER JOIN (SELECT `present_id`,`present_model_id`,`shop_id`,count(id) c FROM `order` GROUP by `shop_id`,`present_id`,`present_model_id`) `orderNy`
          on
              `orderNy`.shop_id =  present_reservation.shop_id and
              `orderNy`.present_id = present_reservation.present_id and
              `orderNy`.present_model_id = present_reservation.model_id
          INNER JOIN present on
          	present.id = present_reservation.present_id

          left join (SELECT * FROM `present_model` WHERE `model_id` != 0 and present_model.language_id = '1') `present_modelNy`
          on
          `present_modelNy`.model_id = present_reservation.model_id
          inner JOIN shop_present
          ON
          shop_present.shop_id = present_reservation.shop_id AND
          shop_present.present_id = present_reservation.present_id
          inner join shop
          ON
          shop.id = present_reservation.shop_id
          WHERE
          shop.is_demo = 0 AND
                          shop.is_gift_certificate = 0 AND
                          shop.active = 1 AND
                          shop.deleted = 0 and
                          shop.soft_close = 0 and
                          present_reservation.quantity > 0

          HAVING (present_reservation.quantity > 0 and (( quantity * (warning_level /100)) <= `orderNy`.c )) or present_copy_of > 0
          ORDER BY present_reservation.`shop_id`,  `present_reservation`.`present_id` DESC");

         $data = [];
         $copy_of = [];
         $add_antal = [];

         foreach ($list as $item)
         {
            /*
            var_dump($item->attributes["shop_id"]);
            var_dump($item->attributes["shop_name"]);
            var_dump($item->attributes["present_name"]);
            var_dump($item->attributes["model_name"]);
            var_dump($item->attributes["model_present_no"]);
            var_dump($item->attributes["model_no"]);
            var_dump($item->attributes["c"]);
            var_dump($item->attributes["quantity"]);
            var_dump($item->attributes["warning_level"]);
            var_dump($item->attributes["copy_of"]);
            */
            if($item->attributes["copy_of"] != 0){
                $copy_of[$item->attributes["shop_id"]][$item->attributes["present_id"]] = $item->attributes["copy_of"];
            }
         }
         $keys = array_keys($copy_of);

         for($i=0;$i < sizeofgf($keys);$i++){
            $imploded = implode(',', $copy_of[$keys[$i]]);

            $rs = PresentReservation::find_by_sql("SELECT `present_id`,`present_model_id`,`shop_id`,count(id) c FROM `order` where `shop_id` = ".$keys[$i]." and `present_id` in (".$imploded.") GROUP by `present_id`,`present_model_id`");
            foreach($rs as $rsItem){
                $presentId = $rsItem->attributes["present_id"];
                $present_model_id = $rsItem->attributes["present_model_id"];
                $c= $rsItem->attributes["c"];
                $add_antal[$keys[$i]][$presentId][$present_model_id] = $c;
            }
         }
        // print_r($add_antal);
        // print_r($list);
         foreach ($list as $item)
         {
           /*
           echo ($item->attributes["shop_id"])."\n";
           echo ($item->attributes["present_model_id"])."\n";
           echo ($item->attributes["present_id"])."\n";
           echo ($item->attributes["copy_of"])."\n\n\n\n\n\n\n";
          */
            try {
              if($add_antal[$item->attributes["shop_id"]][$item->attributes["copy_of"]][$item->attributes["present_model_id"]] ){
                  $newAntal = $add_antal[$item->attributes["shop_id"]][$item->attributes["copy_of"]][$item->attributes["present_model_id"]];
                    $item->attributes["c"]+= $newAntal."\n";
              }
            }
            catch(Exception $e) { }


         }
         return $list;


    }

   static public function getGiftSelected(){
        return PresentReservation::find_by_sql("SELECT DATE(order_timestamp) as t, COUNT(id) as antal FROM `order` GROUP BY DATE(t) ORDER BY `t` DESC LIMIT 30") ;
   }

}
?>

