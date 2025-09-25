<?php
// Controller PresentReservation
// Date created  Mon, 05 Sep 2016 20:36:22 +0200
// Created by Bitworks
class reservationController Extends baseController {
    public function Index() {
    }
    public function saveReservation(){

        $hasP = PresentReservation::find_by_sql("SELECT * FROM `present_reservation` WHERE `shop_id` = ".$_POST["shop_id"]." AND `present_id` = ".$_POST["present_id"]." AND `model_id` = ".$_POST["model_id"]);
        if(count($hasP) > 0){
            $presentreservation = PresentReservation::updatePresentReservation ($_POST);
            response::success(make_json("presentreservation", $presentreservation));
        } else {
            $presentreservation = PresentReservation::createPresentReservation ($_POST);
            response::success(make_json("presentreservation", $presentreservation));
        }

        if($_POST["do_close"] == 1){
            $sql = "UPDATE `present_reservation` set `is_close` = 0 WHERE `model_id` = ".$_POST["model_id"];
            Dbsqli::SetSql2($sql);
        }

    }




    public function create() {
        $presentreservation = PresentReservation::createPresentReservation ($_POST);
        response::success(make_json("presentreservation", $presentreservation));
    }
    public function read() {
        $presentreservation = PresentReservation::readPresentReservation ($_POST['id']);
        response::success(make_json("presentreservation", $presentreservation));
    }
    public function update() {
        $presentreservation = PresentReservation::updatePresentReservation ($_POST);
        response::success(make_json("presentreservation", $presentreservation));
    }
    public function delete() {
        $presentreservation = PresentReservation::deletePresentReservation ($_POST['id'],true);
        response::success(make_json("presentreservation", $presentreservation));
    }
    //Create Variations of readAll
    public function readAll() {
        $presentreservations = PresentReservation::all();
        //$options = array('only' => array('id', 'name', 'username', 'admin', 'active'));
        $options = array();
        response::success(make_json("presentreservations", $presentreservations, $options));
    }
//---------------------------------------------------------------------------------------
// Custom Controller Actions
//---------------------------------------------------------------------------------------

    public function getAllReservations() {
       $presentreservations = PresentReservation::all();
       foreach($presentreservations as $presentreservation) {
           $current_level =   ($presentreservation->quantity *  $presentreservation->warning_level) /100;
           $presentreservation->current_level = $current_level;
           if($current_level>=$presentreservation->warning_level) {
               $presentreservation->warning_issued = 1;
           }
           $presentreservation->save();
       }
       response::success(make_json("presentreservation", $presentreservation));
    }

    // hent resercatio p� en gave
    public function getPresentReservations() {
       $presentreservation = PresentReservation::all(array('present_id' => $_POST['present_id']));
       response::success(make_json("presentreservation", $presentreservation));
    }

    // hent alle reservationer
    public function getShopReservations() {
          $presentreservations = PresentReservation::all(array('shop_id' => $_POST['shop_id']));
          $i = 0;
          foreach($presentreservations as $presentreservation ) {
            $presentreservations[$i]->order_quantity = $presentreservation->ordercount();
          }
          response::success(make_json("presentreservation", $presentreservations));
    }

    public function test() {
        //TODO: funktionen skal kobles ind i ordre controller
        //      der skal afklares om det er pr. shop
        //Skal kaldes efter at der er dannes en ordre

       $dummy = [];

       //Vi skal have model id med over p� gavevalg. hvis vu skal bruge den

       $presentreservation = PresentReservation::hasReservation($_POST['shop_id'],$_POST['present_id'],$_POST['model_id']);
       if(isset($presentreservation)) {
          if($presentreservation->warning_issued==0) {
              // skal hewnte mode_no fra tabel,, da vi ikke har id p� order tabellen endnu
            $ordercount =  Order::countPresentOnOrders($_POST['shop_id'],$_POST['present_id'],$_POST['model_no']);
            $present = Present::find($_POST['present_id']);
            $shop = Shop::find($_POST['shop_id']);

            $current_level =   ($presentreservation->quantity *  $presentreservation->warning_level) /100;

            if($current_level>=$presentreservation->warning_level) {

                  $maildata = [];
                  $maildata['sender_email'] = 'info@gavefabrikken.dk';
                  $maildata['recipent_email'] ='sigurd.skytte@gmail.com';
                  $maildata['subject']= 'Reservationsadvarsel';
                  $body = '<html><head></head><body>';
                  $body.='<h4>Reservationsadvarsel</h4><br>';
                  $body.='shop:'.$shop->name.'<br>';
                  $body.='gave:'.$present->name.'<br>';
                  $body.='modelnr.:'.$_POST['model_no'].'<br>';
                  $body.='Antal p� ordre:'.$ordercount.'<br>';
                  $body.='Advarsel ved:'.$current_level.'<br>';
                  $body.= '</body></html>';
                  $maildata['body'] = $body;
                  $maildata['mailserver_id'] = 1;
                  MailQueue::createMailQueue($maildata);
                  $presentreservation->warning_issued = 1;
                  $presentreservation->save();
            }
          }
       }
      response::success(json_encode($dummy));
    }
    public function scheduleHandler(){
        $dummy = [];
        // hent alle gaver pr shops
        //$join = 'LEFT JOIN shop ON(shop.id = present_reservation.shop_id)';
        //$PresentReservation = PresentReservation::find('all',array('joins' => $join,'conditions' => array('shop.is_demo = ? AND shop.is_gift_certificate = ? AND active = ? AND deleted = ?',0,0,1,0),'having'=>'quantity > 250'));

        $presentToClose =   PresentReservation::find_by_sql("
        select present_reservation.*,`orderNy`.c orderCount from present_reservation
            inner join shop ON present_reservation.shop_id = shop.id
            inner join (
                SELECT `present_id`,`present_model_id`,`shop_id` ,count(id) c FROM `order`  GROUP by `present_id`,`present_model_id`
                ) `orderNy` ON present_reservation.shop_id = `orderNy`.shop_id
            where
                shop.is_demo = 0 AND
                shop.is_gift_certificate = 0 AND
                shop.active = 1 AND
                shop.deleted = 0
            HAVING present_reservation.quantity > 0 and ((orderCount * (warning_level /100)) > quantity )");

      /*
      foreach($shops as $shop ) {
            echo $shop->id;


        }
        */

        response::success(json_encode($presentToClose));
        // tjek om en gave har overskredet reservation kritterierne


        // tjek om det er en model eller gaver uden modeller


        // luk gave, hvis den ikke er lukket


        // tjek om der er erstatningsgave og �ben hvis den er lukket

//             response::success(make_json("presentreservation", $presentreservation));




    }
    public function closeReservationExceed()
    {


     $presentToClose =   PresentReservation::find_by_sql("
        select present_reservation.*,`orderNy`.c as order_count, `orderNy`.present_id as present_ID ,shop.soft_close,shop.id, shop.name,shop.localisation ,shop.rapport_email from present_reservation
            inner join shop ON present_reservation.shop_id = shop.id
            inner join (
                SELECT `present_id`,`present_model_id`,`shop_id` ,count(id) c FROM `order`  GROUP by `present_id`,`present_model_id`
                ) `orderNy` ON present_reservation.model_id = `orderNy`.present_model_id
                and
                    present_reservation.`present_id` = `orderNy`.`present_id`
                and
                    shop.is_demo = 0 AND
                    shop.is_gift_certificate = 0 AND
                    shop.active = 1 AND
                    shop.deleted = 0 and
                   
                    shop.soft_close = 0 and
                    do_close = 1 and
                    is_close = 0
                  HAVING present_reservation.quantity > 0 and order_count >= quantity limit 1");


            //Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','closeReservationExceed','" . $html . "' )");
          //     print_r($presentToClose);
            foreach($presentToClose as $model){
                 $groupid = rand(1, 30000);
                 $subject = "closeReservationExceed_".$groupid;
                 $body = "<html><body><pre><code>".json_encode($model)."</code></pre></body></html>";
                 Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','".$subject."','".$body. "' )");

                     // total fejl men kan ikke rettet n�r active = 0, s� betyder det modsat, at den er aktive
                     // set active = 0 deactive model
                    $body = " <br><br>";
                    $body.= "update `present_model` set active = 1  where `model_id` = ".$model->model_id;
                    $body.= "<br><br>";
                    $body.= "SELECT * FROM `present_model` WHERE `present_id` = ".$model->present_id." AND `language_id` = 1 AND `active` = 1";
                       // hvis rs er = size 0, s� betyder det at present har alle modeller lukket og hele gaven skal lukkes
                    $body.= "<br><br>";
                    $body.= "update `shop_present` set active = 0 WHERE `present_id` = ".$model->present_id;
                    $body.= "<br><br><hr>";
                    $body.= "UPDATE `present_reservation` set `is_close` = 1 WHERE `model_id` = ".$model->present_id;

                    Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','".$subject."','".$body. "' )");

                    $sql = "update `present_model` set active = 1  where `model_id` = ".$model->model_id;
                    Dbsqli::SetSql2($sql);
                    $sql = "SELECT * FROM `present_model` WHERE `present_id` = ".$model->present_id." AND `language_id` = 1 AND `active` = 0";
                    $rsModel =  Dbsqli::getSql2($sql);
                    if(sizeofgf($rsModel) == 0){
                       $sql = "update `shop_present` set active = 0 WHERE `present_id` = ".$model->present_id;
                       Dbsqli::SetSql2($sql);
                    }
                    $sql = "UPDATE `present_reservation` set `is_close` = 1 WHERE `model_id` = ".$model->model_id;
                    Dbsqli::SetSql2($sql);
                    if($model->rapport_email != ""){
                        $this->reservationNotification($model->name,$model->present_id,$model->model_id,$model->rapport_email);
                    }

            }
            echo "done22";
    }
    public function  reservationNotification($shopname, $presentID, $modelID,$email)
    {
         $sql = "SELECT * FROM `present_model` WHERE `model_id` = ".$modelID." AND `language_id` = 1";
         $rsModel =  Dbsqli::getSql2($sql);

         $sql = "SELECT * FROM `present` WHERE `id` = ".$presentID;
         $rsPresent =  Dbsqli::getSql2($sql);
//            <img width=300 src='".$rsModel[0]["media_path"]."'/>
         $body = "
           <div><b>Gave lukket i valgshop</b></div><br>
           <table width=500 cellspacing=5 cellpadding=5 border=1>
           <tr><td width=100>Shop: </td><td >".utf8_encode($shopname)."</td></tr>
           <tr><td>Gave: </td><td>".utf8_encode($rsPresent[0]["nav_name"])."</td></tr>
           <tr><td>Model: </td><td>".utf8_encode($rsModel[0]["model_name"])." - ".utf8_encode($rsModel[0]["model_no"])."</td></tr>
           <tr><td>Varenr: </td><td>".$rsModel[0]["model_present_no"]."</td></tr>
           </table><br>
            <img width=300 src=\"".$rsModel[0]["media_path"]."\"/>
         ";
         $subject = "Gave lukket i valgshop: ".utf8_encode($shopname);

         $html="<html><body>".$body."</body></html>";
         Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','".$email."','".$subject."','".$html. "' )");
    }



//            HAVING present_reservation.quantity > 0 and ((orderCount * (warning_level /100)) > quantity )");

 }
?>

