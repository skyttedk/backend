<?php

namespace GFApp\qrscanner\index;
use GFBiz\app\AppController;

class Controller extends AppController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }
    public function login(){
      $username = $_POST["username"];
      $pw  = $_POST["pw"];
      $result =   \Dbsqli::getSql2("
        select shop.id,app_users.token as app_users_token ,app_users.id as app_users_id  from shop
        inner join app_users on shop.id = app_users.shop_id
        where username = '".$username."' and password = '".$pw."' and app_users.active = 1");
      $return = sizeofgf($result) == 0 ? array("status"=>0) : array("status"=>1,"data"=>$result,"user"=>$username) ;
      echo json_encode($return);
    }


    public function getDeliveryStatus(){
        $shop_id = intval($_POST["shop_id"]);

        $result =   \Dbsqli::getSql2(" SELECT count(`order`.id) as antal, ( SELECT count(`order`.id) from `order` where shop_id = ".$shop_id."   ) as total from `order` where shop_id = ".$shop_id." and registered = 1 ");
        $return = array("status"=>1,"data"=>$result);
        echo json_encode($return);

    }

    public function doInvoiceSearch()
    {
           $searchTxt = $_POST["data"];
           $shop_id = intval($_POST["shop_id"]);
           $qrUser = intval($_POST["app_username"]);
           $token =  $_POST["token"];

           $result =   \Dbsqli::getSql2("SELECT  `order`.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path,present_model.fullalias FROM `order`
                        inner JOIN present_model on `order`.present_model_id = present_model.model_id and present_model.language_id = 1
                        WHERE shopuser_id in ( SELECT `shopuser_id` FROM  `order` WHERE `order_no` like('%".strtolower($searchTxt)."%') and `shop_is_gift_certificate` = 0 and shop_id = ".$shop_id." ) and `shop_is_gift_certificate` = 0  and shop_id = ".$shop_id."  order by id DESC");

            if($this->isOpen($token) == false){
                echo "<div>VIGTIGT! virksomheden har ikke aktiveret gaveudlevering eller du mangler rettigheder2</div>";
                return;
            }
            echo $this->makeHtml($result);
    }
    public function doEmailSearch()
    {
           $searchTxt = $_POST["data"];
           $shop_id = intval($_POST["shop_id"]);
           $qrUser = intval($_POST["app_username"]);
            $token =  $_POST["token"];

           $result =   \Dbsqli::getSql2("SELECT  `order`.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path,present_model.fullalias FROM `order`
                        inner JOIN present_model on `order`.present_model_id = present_model.model_id and present_model.language_id = 1
                        WHERE shopuser_id in ( SELECT `shopuser_id` FROM  `order` WHERE `user_email` like('%".strtolower($searchTxt)."%') and `shop_is_gift_certificate` = 0 and shop_id = ".$shop_id." ) and `shop_is_gift_certificate` = 0  and shop_id = ".$shop_id." order by id DESC");
            if($this->isOpen($token) == false){
            echo "<div>VIGTIGT! virksomheden har ikke aktiveret gaveudlevering eller du mangler rettigheder3</div>";
                return;
            }
            echo $this->makeHtml($result);
    }
    public function doNameSearch()
    {
            $searchTxt = $_POST["data"];
            $shop_id = intval($_POST["shop_id"]);
            $qrUser = intval($_POST["app_username"]);
             $token =  $_POST["token"];

           // Only search on attributes where is_searchable = 1
           $result =   \Dbsqli::getSql2("SELECT  `order`.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path,present_model.fullalias FROM `order`
                        inner JOIN present_model on `order`.present_model_id = present_model.model_id and present_model.language_id = 1
                        WHERE shopuser_id in (
                            SELECT `shopuser_id` FROM  `user_attribute` ua
                            INNER JOIN shop_attribute sa ON ua.attribute_id = sa.id
                            WHERE ua.`attribute_value` like ('%".strtolower($searchTxt)."%')
                            AND sa.is_searchable = 1
                            AND ua.shop_id = ".$shop_id."
                        ) and `shop_is_gift_certificate` = 0  and shop_id = ".$shop_id." order by id DESC");

           if($this->isOpen($token) == false){
                echo "<div>VIGTIGT! virksomheden har ikke aktiveret gaveudlevering eller du mangler rettigheder</div>";
                return;
            }

            echo $this->makeHtml($result);
    }
    private function makeHtml($data){
       // print_R($data);
          $html = "<table width=95% >";
        foreach($data as $ele){
            $mediaArr = explode("/", $ele["media_path"]);
            $lastElement = sizeof($mediaArr) == 1 ? 0 : (sizeof($mediaArr)-1);
            $mediaPath =  "https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/".$mediaArr[$lastElement];
            // Get user attributes that are visible on search
            $userAttributes = \Dbsqli::getSql2("SELECT ua.attribute_value, sa.name
                FROM user_attribute ua
                INNER JOIN shop_attribute sa ON ua.attribute_id = sa.id
                WHERE ua.shopuser_id = ".$ele["shopuser_id"]."
                AND sa.is_visible_on_search = 1
                AND sa.shop_id = ".$ele["shop_id"]."
                ORDER BY sa.index ASC");

            foreach($userAttributes as $attr) {
                $html.="<tr><td>".$attr['name']."</td><td>".$attr['attribute_value']."</td></tr>";
            }

            $html.="<tr><td></td><td><img width=100 src='".$mediaPath."'  /></td></tr>";
            $html.="<tr><td>Gave</td><td>".$ele["model_name"]."-".$ele["model_no"]."</td></tr>";
            $html.="<tr><td>Gave alias</td><td>".$ele["fullalias"]."</td></tr>";
            if($ele["registered"] == 1){
                 $time = strtotime($ele["registered_date"]);
                 $newformat = date('d-m-Y',$time);
                $html.="<tr><td colspan=2>
                <p style='color:red'>Gaven er udleveret! ".$newformat."</p><label>Note:&nbsp; </label><span id='note_".$ele["order_no"]."'>".$ele["registered_note"]."</span><br></td></tr>";
                $html.="<tr><td colspan=2><button class='sog-undo button button3' onclick='sogUndo(".$ele["order_no"].")'>Undo udlev</button> </td></tr>";

            } else {
                $html.="<tr><td ><button class='sog-reg button button1' onclick='sogApprove(".$ele["order_no"].")'>Udlevere gaven <br> og gem noten</button> </td><td> <textarea class='sogNote_".$ele["order_no"]."' id='sog-note_".$ele["order_no"]."' rows='4' cols='25'></textarea> </td></tr>";
            }

            $html.="<tr><td colspan=2><hr></td></tr>";
        }
        if(sizeofgf($data) == 0){
           return $html ="Intet resultat";
        } else {
          return $html.="</table>";
        }

    }
    public function getOrderFromHistory($shopID,$orderID,$qrUser){

            $sql = "INSERT INTO `app_log`
                (`app_username`, `shop_id`, `order_id`, `log_event`,  `log_description`, extradata)
                VALUES ('".$qrUser."',  ".$shopID.", '".$orderID."', 'old receipt scanned', '', '".$orderID."')";
               \Dbsqli::setSql2($sql);

                  $resiver = "";
        $gift = "";
        $email = "";
        $orderData = $this->getOrderData($shopID,$this->readOrderId($_POST["data"]));

        if(sizeofgf($orderData) != 0){
             $resiver =  $orderData[0]->user_name ;
             $email = $orderData[0]->user_email;
            if (strpos($orderData[0]->present_model_name, "###") !== false) {
                $pieces = explode("###", $orderData[0]->present_model_name);
                $gift = $pieces[0] . " - " . $pieces[1];
            } else {
                $gift = $orderData[0]->present_model_name;
            }
         }
         $this->log($qrUser,$shopID,$this->readOrderId($_POST["data"]),"Old receipt scanned","",$resiver,$email,$gift);



           $order_history = \orderHistory::find_by_sql("select * from order_history where order_no =".$orderID." and shop_id =".$shopID);
           if(sizeofgf($order_history) == 0){
                return false;
           } else {
              return $order_history[0]->shopuser_id;
           }
    }
    public function getOrderData($shopID,$orderID){

       return $order = \order::find_by_sql("select * from order_history where order_no =".$orderID." and shop_id =".$shopID);
    }

    public function getOrder(){
            $status = 1;
            $orderNr = "";
            $qrUser = intval($_POST["app_username"]);
            $shopID = intval($_POST["shop_id"]);
            $orderID = $this->readOrderId($_POST["data"]);
            $token =  $_POST["token"];

            if($this->validationCheck($orderID,$qrUser) == false){
                $this->log($qrUser,$shopID,$orderID,"Validation check error","","","","");
                $return = array("status"=>"-1","html"=>"");
                echo json_encode($return);
                return;
            }

            if($this->isOpen($token) == false){

                $this->log($qrUser,$shopID,$orderID,"Company not open for handed out","","","","");
                $return = array("status"=>"-2","html"=>"");
                echo json_encode($return);
                return;
            }

            $order = \order::find_by_sql("SELECT present_model.model_name,present_model.model_no,present_model.media_path,`order`.`user_name`,`order`.`user_email`,`order`.`registered_date`,`order`.`order_no`,`order`.registered, `order`.registered_note,present_model.fullalias   FROM `order`
                inner JOIN present_model on `order`.`present_model_id` = present_model.model_id and present_model.language_id = 1
                WHERE `order`.`order_no` =".$orderID." and `order`.shop_id = ".$shopID  );
       //      echo $order;
           $html = "";
           $note = "";
           $noteToTextarea = "";
           if(sizeofgf($order) == 0){
                $shopUser = $this->getOrderFromHistory($shopID,$orderID,$qrUser);

                if($shopUser == false) {
                    $return = array("status"=>"0","html"=>"");
                    echo json_encode($return);

                } else {
                        $status = "2";

                        $order = \order::find_by_sql("SELECT present_model.model_name,present_model.model_no,present_model.media_path,`order`.`user_name`,`order`.`user_email`,`order`.`registered_date`,`order`.`order_no`,`order`.registered, `order`.registered_note,present_model.fullalias   FROM `order`
                        inner JOIN present_model on `order`.`present_model_id` = present_model.model_id and present_model.language_id = 1
                        WHERE `order`.`shopuser_id` =".$shopUser." and `order`.shop_id = ".$shopID  );
                        $orderNr =  $order[0]->order_no;
                }

           }


           if($order[0]->registered == 1){
               $status = $status == "2" ? "4" : "3";

               if($order[0]->registered_note != "" ){
                   $noteToTextarea = $order[0]->registered_note;
                   $note = "<div class='noteTop'><b>Note:</b> ".str_replace("\n","<br />",$order[0]->registered_note)."</div>";
               }
               $html.= " <h3 style='color:red'>Gaven er udleveret! ".date_format($order[0]->registered_date, 'd-m-Y')."</h3>".$note."<br>";
           }
            $mediaArr = explode("/", $order[0]->media_path);
            $lastElement = sizeof($mediaArr) == 1 ? 0 : (sizeof($mediaArr)-1);
            $mediaPath =  "https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/".$mediaArr[$lastElement];
            $html.= "<img src='".$mediaPath."' width=50% />
            <table>
            <tr> <td>Ordrenr.:</td><td>".$order[0]->order_no."</td></tr>
            <tr> <td>Gave</td><td>".$order[0]->model_name." - ".$order[0]->model_no."</td></tr>
            <tr><td> alias</td><td>".$order[0]->fullalias."</td></tr>";

            // Get user attributes that are visible on search
            $userAttributes = \Dbsqli::getSql2("SELECT ua.attribute_value, sa.name
                FROM user_attribute ua
                INNER JOIN shop_attribute sa ON ua.attribute_id = sa.id
                WHERE ua.shopuser_id = (SELECT shopuser_id FROM `order` WHERE order_no = ".$orderID." AND shop_id = ".$shopID." LIMIT 1)
                AND sa.is_visible_on_search = 1
                AND sa.shop_id = ".$shopID."
                ORDER BY sa.index ASC");

            foreach($userAttributes as $attr) {
                $html.= "<tr> <td>".$attr['name'].":</td><td>".$attr['attribute_value']."</td></tr>";
            }

            $html.= "</table>
            <br>
            <textarea id='note' rows='4' style='width: 90%;'>".$noteToTextarea."</textarea>
            ";
            if($order[0]->registered == 1){

            }
        $html.= "<br><br><button class='updateNote' onclick='updateNote(".$orderID.")'>Opdater noten</button>";
            $html.="<br> <br><br><br>";
            $return = array("status"=>$status,"html"=>$html,"orderNr"=>$orderNr);
            echo json_encode($return);

    }

    public function updateNote(){
        sleep(1);
        $token = $_POST["token"];
        $note = $_POST["note"];
        $orderID = intval($_POST["orderID"]);
        if($this->isOpen($token) == false){
            $return = array("data"=>"no access");
            echo json_encode($return);
            return;
        }

        $sql = "update `order` set registered_note = '".$note."' where order_no = ".$orderID;
        $rs = \Dbsqli::setSql2($sql);

        $sql = "select registered_note from `order` where order_no=".$orderID;
        $rs = \Dbsqli::getSql2($sql);

        $return = array("data"=>$rs[0]["registered_note"]);
        echo json_encode($return);
    }

    public function reg()
    {

        $shop_id = intval($_POST["shop_id"]);
        $qrUser = intval($_POST["app_username"]);
        $resiver = "";
        $gift = "";
        $email = "";
        $orderData = $this->getOrderData($shop_id,$this->readOrderId($_POST["data"]));

        if(sizeofgf($orderData) != 0){
             $resiver =  $orderData[0]->user_name ;
             $email = $orderData[0]->user_email;
            if (strpos($orderData[0]->present_model_name, "###") !== false) {
                $pieces = explode("###", $orderData[0]->present_model_name);
                $gift = $pieces[0] . " - " . $pieces[1];
            } else {
                $gift = $orderData[0]->present_model_name;
            }

        }

     $this->log($qrUser,$shop_id,$this->readOrderId($_POST["data"]),"Gift handed out",$_POST["notedata"],$resiver,$email,$gift);


      $sql = "update `order` set registered = 1, registered_date = now(),registered_note = '".$_POST["notedata"]."' where order_no = '".$this->readOrderId($_POST["data"])."' and `order`.shop_id = ".$shop_id;
      \Dbsqli::setSql2($sql);
      $sql = "select order_no from `order` where order_no = '".$this->readOrderId($_POST["data"])."' and registered = 1 and `order`.shop_id = ".$shop_id;
      $rs = \Dbsqli::getSql2($sql);
      if(sizeofgf($rs) > 0){
        echo "1";
      } else {
        echo "0";
      }

    }
    private function log($qrUser,$shop_id,$order_id,$action,$noter,$resiver,$email,$gift)
    {
       $sql = "INSERT INTO `app_log`
        (`app_username`, `shop_id`, `order_id`, `log_event`,  `log_description`,recipient,email,gift_received)
        VALUES ('".$qrUser."',  ".$shop_id.", '".$order_id."', '".$action."', '".$noter."', '".$resiver."','".$email."',  '".$gift."')";
       \Dbsqli::setSql2($sql);
    }
    private function fixData($data){

    }
    private function validationCheck($orderNo,$app_username){
      $sqlApp = "SELECT `shop_id` FROM `app_users` WHERE `id` =".$app_username;
      $sqlAppRs = \Dbsqli::getSql2($sqlApp);

      $sqlOrder ="SELECT shop_id FROM order_history WHERE order_no = ".$orderNo;
      $sqlOrderRs = \Dbsqli::getSql2($sqlOrder);

      if($sqlAppRs[0]["shop_id"] == $sqlOrderRs[0]["shop_id"]){
        return true;
      } else {
        return false;
      }

    }
    private function isOpen($token){

           $sqlApp = "SELECT `shop_id` FROM `app_users` WHERE token = '".$token."'";

           $sqlAppRs = \Dbsqli::getSql2($sqlApp);

           if(sizeofgf($sqlAppRs) == 0){
             return false;
           }
           $sqlShop = "SELECT open_for_registration FROM shop WHERE `id` = ".$sqlAppRs[0]["shop_id"];
           $sqlShopRs = \Dbsqli::getSql2($sqlShop);
           if(sizeofgf($sqlShopRs) == 0){
             return false;
           }
           return $sqlShopRs[0]["open_for_registration"] == 1 ? true : false;
    }

    public function undoReg()
    {
       $shop_id = intval($_POST["shop_id"]);
       $qrUser = intval($_POST["app_username"]);
       $note = isset($_POST["note"]) ? $_POST["note"] : "";
       $resiver = "";
       $gift = "";
       $email = "";

       $orderData = $this->getOrderData($shop_id,$this->readOrderId($_POST["data"]));

        if(sizeofgf($orderData) != 0){
             $resiver =  $orderData[0]->user_name ;
             $email = $orderData[0]->user_email;
            if (strpos($orderData[0]->present_model_name, "###") !== false) {
                $pieces = explode("###", $orderData[0]->present_model_name);
                $gift = $pieces[0] . " - " . $pieces[1];
            } else {
                $gift = $orderData[0]->present_model_name;
            }
        }

        $this->log($qrUser,$shop_id,$this->readOrderId($_POST["data"]),"Delivery undo",$note,$resiver,$email,$gift);


      $sql = "update `order` set registered = 0, registered_date = now() where order_no = '".$this->readOrderId($_POST["data"])."' and `order`.shop_id = ".$shop_id;
      \Dbsqli::setSql2($sql);
      $sql = "select order_no from `order` where order_no = '".$this->readOrderId($_POST["data"])."' and registered = 0 and `order`.shop_id = ".$shop_id;
      $rs = \Dbsqli::getSql2($sql);
      if(sizeofgf($rs) > 0){
        echo "1";
      } else {
        echo "0";
     }
    }


    private function readOrderId($orderStr)
    {
            $pieces = explode("=", $orderStr);
            if(count($pieces) != 3){
               return "<h3>Kvitteringen blev ikke fundet</h3>";
            } else {
               return $pieces[2];
            }
    }



}
