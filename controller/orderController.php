<?php
// Controller Order
// Date created  Thu, 26 May 2016 19:44:28 +0200
// Created by Bitworks
include ("thirdparty/php-image-magician/php_image_magician.php");
class OrderController Extends baseController {
    public function Index() {
    }
    private function validatetodkenShop($shopID,$token)
    {

        if($token == "NJycUpZGVhMJvQ7Kmb88uXRgX6VMhpvUcEBPj9NhmJ2tjxQB") return true;
        $rs = ShopUser::find_by_token($token);
        if($rs){
            if($rs->is_demo == 1 && $rs->shop_id == $shopID){
                return true;
            }
        } else {
            http_response_code(400);
            return;
        }

    }
    // Opret ny ordre
    public function create() {
       $data = $_POST;


       $order = Order::createOrder ($data);

        $shopuser = ShopUser::find($order->shopuser_id);
        ActionLog::logShopUserAction("ShopUserOrder", $shopuser->username." har valgt ".$order->present_model_present_no." på ordrenr ".$order->order_no,"",$shopuser,$order->id);

        
        $this->createReceipt($order->id,$order->language_id);
       $options = array('include' => array('attributes_'));
       response::success(make_json("order", $order,$options));
    }

   function updateShopUserCustomerPanel() {

    $this->validatetodkenShop($_POST['shop_id'],$_POST['token']);
    $data = $_POST;
    $skipEmail =  1;//$_POST['skip_email'];
    $shopUser = Shop::updateShopUser($data);
    $order = Order::find_by_shopuser_id($_POST['user_id']);

    if($order) {
        $data = [];
        $data['user_id'] =$_POST['user_id'];
        if($skipEmail == 0){
              $this->resendOrderMail($data);
        } else {
          $dummy = [];
          response::success(json_encode($dummy));
        }

    } else {
       $options = array('include' => array('attributes_'));
       response::success(make_json("shopuser",$shopUser,$options));
     }
   }
   public function orderUseAlias(){
        $gavealias =  $_POST["gavealias"];
        $shopId   =  $_POST["shop_id"];
        $userId   =  $_POST["user_id"];
        $_saveIndex = $_POST["_saveIndex"];
        $presentData = PresentModel::find_by_sql("SELECT `present_id`,`model_id` FROM `present` inner join present_model ON present.id = present_model.present_id
                                                    WHERE shop_id = ".$shopId." and fullalias ='".$gavealias."' and `language_id` = 1 ");


        if(sizeofgf($presentData) > 0){

            $data = [];
            $data['shopId'] = $shopId;
            $data['userId'] = $userId;
            $data['presentsId'] = $presentData[0]->attributes["present_id"];
            $data['model_id'] =   $presentData[0]->attributes["model_id"];
            $data['model'] =  '';
            $data['modelData'] = '';
            $this->changePresent($data);

        } else {
             throw new exception('Fandt ikke gavealias paa linje: '.$_saveIndex.' , indlaesning stoppet, tjek dine data og proev igen');
        }

   }
  public function updateUserDeliveryInfo() {

    $attributes = (array)json_decode($_POST['data']);
    foreach($attributes as $attribute) {
        $userattribute = UserAttribute::find_by_shopuser_id_and_attribute_id($attribute->shopuser_id,$attribute->attribute_id);
        $userattribute->attribute_value =$attribute->attribute_value;
        $userattribute->save();
        $orderattribute =  OrderAttribute::find_by_shopuser_id_and_attribute_id($attribute->shopuser_id,$attribute->attribute_id);
        $orderattribute->attribute_value =$attribute->attribute_value;
        $orderattribute->save();
       }
       $result = [];
       response::success(json_encode($result));
  }

  public function getUsersOrderAndAlias (){
     $shopId = $_POST['id'];
    $orders = Order::find_by_sql("
     select distinct( `order`.shopuser_id ), fullalias  from `order`
     join present_model on `order`.present_id = present_model.present_id and `order`.present_model_id = present_model.model_id  where shop_id = ".$shopId);
    response::success(json_encode($orders));



  }



  public function getUserDeliveryInfo() {

    $order = Order::find_by_shopuser_id($_POST['user_id']);

    //.......
    //     $order = Order::find('all', array(
    //       'conditions' => 'shopuser_id = '.$_POST['user_id'] ,
    //       'order' => 'index desc'
    //     ));

    $data = [];
    if(count($order)==1) {
       foreach($order->attributes_ as $orderattribute) {
          $shopattribute = ShopAttribute::find($orderattribute->attribute_id);
          if($shopattribute->is_delivery==1) {
            array_push($data,$orderattribute);
          }
          if($shopattribute->is_name==1) {
            array_push($data,$orderattribute);
          }
       }
       response::success(json_encode($data));
     } else {
       response::success(json_encode($data));
     }
  }


  // kan bruges af ulrich til at hent og vise en kvittering
  public function getReceipt()
   {
       $mail = MailQueue::find('all',array('conditions' =>array('order_id' => $_REQUEST['order_id'])) );
       $result = [];
       if($mail[0])
         $result['receipt'] = base64_encode($mail[0]->body);

       response::success(json_encode($result));

   }

   public function resendReceipt() {
       $order = Order::find($_POST['order_id']);
      // $mail = MailQueue::find('all',array('conditions' =>array('order_id' => $_POST['order_id'])) );
       if(count($order) > 0 ) {
         $this->createReceipt($order->id,$order->language_id);
       } else {
         throw new exception('Ordre blev ikke fundet');
       }
       $result = [];
       response::success(json_encode($result));
   }

   // Kaldes fra backend, og generer en ny ordre

    public function kundepanelresend($data = null) {
        $this->validatetodkenShop($_POST['shop_id'],$_POST['token']);
        if($data) {
            $postdata = $data;
        } else {
            $postdata = $_POST;
        }

        $order = Order::find_by_shopuser_id($postdata['user_id']);
        // print_R($order);

        $data = [];
        $data['shopId']   = $order->shop_id;
        $data['userId']   = $postdata['user_id'];
        $data['presentsId'] =  $order->present_id;
        $data['model_id'] =  $order->present_model_id;


        if($order->present_model_present_no) {

            if (!strpos($order->present_model_present_no, "###") !== false) {
                $order->present_model_present_no."###";
            }


            $tmp = explode("###", $order->present_model_name);

            $data['modelName'] = $tmp[0];
            $data['model']     = array_key_exists(1, $tmp) ? $tmp[1]:"";
            $data['modelId'] =  $order->present_model_present_no;
        }  else {
            $data['modelName'] = '';
            $data['model']     = '';
            $data['modelId']   = '';
        }

        $this->changePresent($data);
    }


   public function resendOrderMail($data = null) {

         if($data) {
           $postdata = $data;
         } else {
           $postdata = $_POST;
         }

        $order = Order::find_by_shopuser_id($postdata['user_id']);
        // print_R($order);

        $data = [];
        $data['shopId']   = $order->shop_id;
        $data['userId']   = $postdata['user_id'];
        $data['presentsId'] =  $order->present_id;
        $data['model_id'] =  $order->present_model_id;
    

        if($order->present_model_present_no) {

            if (!strpos($order->present_model_present_no, "###") !== false) {
               $order->present_model_present_no."###";
            }


          $tmp = explode("###", $order->present_model_name);

          $data['modelName'] = $tmp[0];
          $data['model']     = array_key_exists(1, $tmp) ? $tmp[1]:"";
          $data['modelId'] =  $order->present_model_present_no;
        }  else {
          $data['modelName'] = '';
          $data['model']     = '';
          $data['modelId']   = '';
        }

        $this->changePresent($data);
  }

     public function getOrderSummary(){


        $orderSum = Order::find_by_sql("SELECT count( `order`.id) as antal, present_model.model_no,fullalias,  present_model.model_name FROM `order` inner JOIN present_model ON present_model.model_id = `order`.present_model_id WHERE shop_id = '".$_POST['shopid']."'  and present_model.language_id = 1 GROUP by `order`.`present_model_id` order by antal DESC");
        response::success(json_encode($orderSum));
   }



    // �ndre gave/Generer ny ordre
    public  function autoSelectNoPresents() {

       $dummy = [];
      //$orderattributes =    OrderAttribute::all();
      $orderattributes = OrderAttribute::find('all',array('conditions' => array('shop_id'=>$_POST['shopid'])));
       foreach($orderattributes as $orderattribute) {
          $shopattribute = ShopAttribute::find($orderattribute->attribute_id);
          $orderattribute->attribute_index = $shopattribute->index;
          $orderattribute->save();
       }


      //  $shop_id
        $shopusers = ShopUser::find('all',array('conditions' => array('shop_id'=>$_POST['shopid'],'is_demo' => 0)));
        $system = System::first();
        $shopattributes_mandatory = [];

        $shopattributes = ShopAttribute::find('all',array('conditions' => array('shop_id'=>$_POST['shopid'])));
        foreach($shopattributes as $shopattribute) {
            if($shopattribute->is_mandatory==true) {
              $shopattribute->is_mandatory = false;
              $shopattribute->save();
              $shopattributes_mandatory[] =  $shopattribute;
            }
        }
        foreach($shopusers as $shopuser) {
              if(!$shopuser->has_Orders())   {
                $data = [];
                $data['shopId'] = $_POST['shopid'];
                $data['userId'] = $shopuser->id;
                $data['presentsId'] = $system->dummy_present;
                $data['model'] = '';
                $data['modelData'] = '';
                $attributes = [];
                $userattributes = UserAttribute::all(array('shopuser_id' => $shopuser->id));
                foreach($userattributes  as $userattribute)
                {
                    $obj = new stdClass;
                    $obj->feltKey = $userattribute->attribute_id;
                    $obj->feltVal = $userattribute->attribute_value;
                    $attributes[] = $obj;
                    $data['_attributes'] =json_encode((array)$attributes);
                    $order = Order::createOrder($data);
                }
            }
        foreach($shopattributes_mandatory as $shopattribute) {
              $shopattribute->is_mandatory = true;
              $shopattribute->save();
        }
     }

       response::success(json_encode($dummy));

   }

    public  function autoSelectSpecificPresents() {

       $dummy = [];
      //$orderattributes =    OrderAttribute::all();
      $orderattributes = OrderAttribute::find('all',array('conditions' => array('shop_id'=>$_POST['shopid'])));
       foreach($orderattributes as $orderattribute) {
          $shopattribute = ShopAttribute::find($orderattribute->attribute_id);
          $orderattribute->attribute_index = $shopattribute->index;
          $orderattribute->save();
       }







      //  $shop_id
        $shopusers = ShopUser::find('all',array('conditions' => array('shop_id'=>$_POST['shopid'],'is_demo' => 0)));
        $system = System::first();
        $shopattributes_mandatory = [];

        $shopattributes = ShopAttribute::find('all',array('conditions' => array('shop_id'=>$_POST['shopid'])));
        foreach($shopattributes as $shopattribute) {
            if($shopattribute->is_mandatory==true) {
              $shopattribute->is_mandatory = false;
              $shopattribute->save();
              $shopattributes_mandatory[] =  $shopattribute;
            }
        }
        // hack for at der kun bliver kørt en af gang, da fx 70 ordre aldrig vil få en chance for at blive gennemfør i et hug 
        $onlyOne = 0;
        foreach($shopusers as $shopuser) {
              if(!$shopuser->has_Orders() && $onlyOne == 0)   {


                $onlyOne = 1;
                $data = [];
                $data['shopId'] = $_POST['shopid'];
                $data['userId'] = $shopuser->id;
                $data['presentsId'] = $_POST['present_id'];
                $data['model_id'] = $_POST['model_id'];
                $data['model'] =  '';
                $data['modelData'] = '';
                if(isset($data['model_id']))  {
                  if($data['model_id']>0) {
                    $presentmodel = PresentModel::all(array('conditions' => array('model_id' => $data['model_id'],'language_id' => 1)))[0];
                    $data['model'] =  $presentmodel->model_name.'###'.$presentmodel->model_no;
                    $data['modelData'] = $presentmodel->model_present_no;
          	      }
                }
                $attributes = [];
                $userattributes = UserAttribute::all(array('shopuser_id' => $shopuser->id));
                foreach($userattributes  as $userattribute)
                {
                    $obj = new stdClass;
                    $obj->feltKey = $userattribute->attribute_id;
                    $obj->feltVal = $userattribute->attribute_value;
                 	 if($userattribute->is_email) {
                       if($obj->feltVal=="") {
                           $obj->feltVal = "<no email>";
                       }
                	 }
                    $attributes[] = $obj;
                }
                $data['_attributes'] =json_encode((array)$attributes);
                $order = Order::createOrder($data);
                $this->createReceipt($order->id,$order->language_id,1);
                //Create a shopuserautoselectentry
                $autoselect = new ShopUserAutoselect();
                $autoselect->shop_id = $shopuser->shop_id;
                $autoselect->company_id  = $shopuser->company_id;
                $autoselect->shopuser_id  = $shopuser->id;
                $autoselect->present_id  = $order->present_id;
                $autoselect->present_model_id  = $order->present_model_id;
                $autoselect->save();
            }
        foreach($shopattributes_mandatory as $shopattribute) {
              $shopattribute->is_mandatory = true;
              $shopattribute->save();
        }
     }
       $dummy["orderStatus"] = $onlyOne;
       response::success(json_encode($dummy));

   }


    public  function kundepanelcp($data = null,$skipemail=0) {
        $this->validatetodkenShop($_POST['shopId'],$_POST['token']);
        if($data) {
            $postdata = $data;
        } else {
            $postdata = $_POST;
        }

        $data['shopId'] = $postdata['shopId'];
        $data['userId'] = $postdata['userId'];
        $data['presentsId'] = $postdata['presentsId'];
        $data['model_id'] = $postdata['model_id'];
        $data['model'] =  '';
        $data['modelData'] = '';

        if(isset($data['model_id']))  {
            if($data['model_id']>0) {
                $presentmodel = PresentModel::all(array('conditions' => array('model_id' => $data['model_id'],'language_id' => 1)))[0];
                $data['model'] =  $presentmodel->model_name.'###'.$presentmodel->model_no;
                $data['modelData'] = $presentmodel->model_present_no;
            }
        }

        $attributes = [];
        $userattributes = UserAttribute::all(array('shopuser_id' => $postdata['userId']));
        foreach($userattributes  as $userattribute)
        {
            $obj = new stdClass;
            $obj->feltKey = $userattribute->attribute_id;
            $obj->feltVal = $userattribute->attribute_value;
            $attributes[] = $obj;
        }

        $data['_attributes'] =json_encode((array)$attributes);
        $order = Order::createOrder($data);
        if($data['shopId'] == 4873){
            $this->createReceipt($order->id,$order->language_id);
        }


        if(isset($postdata['skip_email']))
            $skipemail = $postdata['skip_email'];
        // $skipemail = 1; // 0410 ulrich
        if($skipemail==0)
            $this->createReceipt($order->id,$order->language_id);

        $shopUser = ShopUser::find($order->shopuser_id);

        // Log
        \ActionLog::logAction("OrderChange", "Gavevalg ændret for: ".$order->user_username. " - til ".$data['model']." (".$data['modelData'].")","",0,$order->shop_id,$order->company_id,$shopUser->company_order_id,$shopUser->id,$order->id,0);


        $dummy = [];
        response::success(json_encode($dummy));
    }


    public  function changePresent($data = null,$skipemail=0) {

         if($data) {
           $postdata = $data;
         } else {
           $postdata = $_POST;
         }

        $data['shopId'] = $postdata['shopId'];
        $data['userId'] = $postdata['userId'];
        $data['presentsId'] = $postdata['presentsId'];
        $data['model_id'] = $postdata['model_id'];
        $data['model'] =  '';
        $data['modelData'] = '';

       if(isset($data['model_id']))  {
          if($data['model_id']>0) {
            $presentmodel = PresentModel::all(array('conditions' => array('model_id' => $data['model_id'],'language_id' => 1)))[0];
            $data['model'] =  $presentmodel->model_name.'###'.$presentmodel->model_no;
            $data['modelData'] = $presentmodel->model_present_no;
  	      }
        }

        $attributes = [];
        $userattributes = UserAttribute::all(array('shopuser_id' => $postdata['userId']));
        foreach($userattributes  as $userattribute)
        {
            $obj = new stdClass;
            $obj->feltKey = $userattribute->attribute_id;
            $obj->feltVal = $userattribute->attribute_value;
            $attributes[] = $obj;
        }

        $data['_attributes'] =json_encode((array)$attributes);
        $order = Order::createOrder($data);
        if($data['shopId'] == 4873){
            $this->createReceipt($order->id,$order->language_id);
        }


        if(isset($postdata['skip_email']))
          $skipemail = $postdata['skip_email'];
           // $skipemail = 1; // 0410 ulrich
        if($skipemail==0)
          $this->createReceipt($order->id,$order->language_id);

        $shopUser = ShopUser::find($order->shopuser_id);

        // Log
        \ActionLog::logAction("OrderChange", "Gavevalg ændret for: ".$order->user_username. " - til ".$data['model']." (".$data['modelData'].")","",0,$order->shop_id,$order->company_id,$shopUser->company_order_id,$shopUser->id,$order->id,0);


        $dummy = [];
        response::success(json_encode($dummy));
    }


     public function createReceipt2() {

       //$order = Order::find(9927);
       //$orderdetails = Order::getOrderDetails($orderId,$languageId);

      // $shopattribute = ShopAttribute::find(1302);
      $orderdetails = Order::getOrderDetails('11454','2');
 //     response::success(make_json("order",$orderdetails));
              response::success(json_encode($orderdetails));
      // $orderdetails = Order::getAttributeName($shopattribute,2);
      // $orderdetails = Order::getAttributeName($shopattribute,5);
      // echo $orderdetails;


     }

    //Generer html kvittering og send den  
     public function createReceipt($orderId,$languageId,$send=0) {


        $order = Order::find($orderId);
        $shopuser = ShopUser::find($order->shopuser_id);
        $shop = Shop::find($order->shop_id);




        $orderhistory = OrderHistory::find('all',array('conditions' => array('shopuser_id = ? and order_no <> ?', $shopuser->id,$order->order_no)));
        $orderdetails = Order::getOrderDetails($orderId,$languageId);
        $description = base64_decode($orderdetails['present_description']);
        $orderdetails['present_model_name'] = str_replace("###"," - ", $orderdetails['present_model_name']);

        $mailtempate = MailTemplate::getTemplate($order->shop_id,$languageId);
        if($mailtempate != null) {
            $maintemplate  = $mailtempate->template_receipt;
        } else {
            $maintemplate = "";
        }

        $maintemplate = str_replace('{ORDERNO}',$orderdetails['order_no'],$maintemplate);
        $maintemplate = str_replace('{DATE}',$orderdetails['date_stamp'],$maintemplate);
        $maintemplate = str_replace('{PRESENT_NAME}',$orderdetails['present_caption'],$maintemplate);

        // KVITTERING CUSTOM TILFØJELSE

        $receiptTxt = "";
        $receipt = Receipt::find_by_sql("SELECT * FROM `receipt_custom_part` where id = ( SELECT msg1 FROM `present_model` WHERE `model_id` = '".$orderdetails['present_model_id']."' and `language_id` = 1  )");
        if(sizeofgf($receipt) > 0){
            if($languageId == "1"){
                   $receiptTxt = "<tr><td colspan='2'><hr><br>".$receipt[0]->attributes["da"]."<br><br></td></tr>";
            } else {
                   $receiptTxt = "<tr><td colspan='2'><hr><br>".$receipt[0]->attributes["en"]."<br><br></td></tr>";
            }
            $maintemplate = str_replace('{RECEIPT_POS2}',$receiptTxt,$maintemplate);
        } else {
            $maintemplate = str_replace('{RECEIPT_POS2}',"",$maintemplate);
        }



        // KVITTERING CUSTOM indset ugenr tag:  #ugenr#     18033772
        if($orderdetails['shop_is_gift_certificate'] == "1"){
            // hvis hjemmelevering
            if($shopuser->is_delivery == "1" ){

                if($order->shop_id == 1832 || $order->shop_id == 1981 || $order->shop_id == 2558 || $order->shop_id == 5117 || $order->shop_id == 4793) {
                    $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Ditt presentval kan ändras i 24 timmar. Ditt val kommer då att överföras till packeriet och det kommer inte att vara möjligt att göra några ändringar därefter.<br /><br />Vi skickar vanligtvis ditt paket inom 10 arbetsdagar, dock tidigast från 1 december 2024.<br /><br />Observera att extra leveranstider på upp till 20 dagar kan upplevas under högsäsong (december, januari och februari)<br /><br />Har du beställt ett upplevelsepresentkort, vistelse eller kryssning så skickas det slutgiltiga presentkortet till dig. Detta kvitto kan därför inte användas som presentkort.</p><br></td></tr>';
                } else if($order->shop_id == 8271 ) {
                    $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Ditt presentval kan ändras i 24 timmar. Ditt val kommer då att överföras till packeriet och det kommer inte att vara möjligt att göra några ändringar därefter.<br /><br />Vi skickar ditt paket inom 10 arbetsdagar, och levereras till närmaste paketbutik.<br /><br />Har du beställt ett upplevelsepresentkort, vistelse eller kryssning så skickas det slutgiltiga presentkortet till dig. Detta kvitto kan därför inte användas som presentkort.</p><br></td></tr>';
                } else if($order->shop_id == 574 || $order->shop_id == 57 || $order->shop_id == 272 || $order->shop_id == 58 || $order->shop_id == 59  || $order->shop_id == 2550  || $order->shop_id == 2549) {
                    $rs = ExpireDate::find_by_sql("SELECT week_no FROM `expire_date` where expire_date = (SELECT expire_date FROM `shop_user` WHERE shop_id = ".$order->shop_id." && `username` = '".$orderdetails['user_username']."') ");
                    $weeknr = "";
                    if(sizeofgf($rs) > 0){
                        $weeknr =  $rs[0]->attributes["week_no"];
                    }
                    $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Din gave vil bli sendt fra oss i uke '.$weeknr.', og vil hurtigst mulig bli levert til ditt n&aelig;rmeste utleveringssted. (post i butikk)</p>
                        <p>Skulle du ombestemme deg, kan du endre ditt valg helt frem til deadline. Du logger bare p&aring; gavevalg.no igjen og velger p&aring; nytt.</p><br><br></td></tr>';
                }
                else if(in_array($order->shop_id,array(8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366))) {

                    // NO LUKSUS:
                    $hjemmelevHtml = "";

                }
                else {

                    // LUKSUS
                    if(in_array($order->shop_id,array(2961,2960,2962,2963))) {
                        $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Dit gavevalg kan &aelig;ndres i 24 timer. Derefter overf&oslash;res dit valg til pakkeriet, og det vil herefter ikke v&aelig;re muligt at foretage &aelig;ndringer.</p><p>Der kan opleves ekstra leveringstid op til 20 arbejdsdage i december, januar og februar &nbsp;(normal leveringstid 10 arbejdsdage)</p><p>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det endelige gavekort blive sendt til dig. Denne kvittering kan derfor ikke benyttes som gavebevis.</p><p>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring; <a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/" mc:disable-tracking>Gaveklubben</a></p><br><br></td></tr>';
                    }
                    // OTHER DK
                    else {


                        $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Dit gavevalg kan &aelig;ndres i 24 timer. Derefter overf&oslash;res dit valg til pakkeriet, og det vil herefter ikke v&aelig;re muligt at foretage &aelig;ndringer.</p><p>Din pakke bliver sendt med GLS og leveret til n&aelig;rmeste Pakkeshop.&nbsp;</p> <p>Vi afsender normalt din pakke indenfor 10 arbejdsdage dog tidligst den 2. december 2024.</p>                                   <p>Bem&aelig;rk at der kan opleves ekstra leveringstid op til 20 dage i h&oslash;js&aelig;sonen (december, januar og februar)</p>                                    <p>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det endelige gavekort blive sendt til dig. Denne kvittering kan derfor ikke benyttes som gavebevis.</p> <p>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring;&nbsp;<a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/" target="_blank">GAVEKLUBBEN&trade;</a></p><br><br></td></tr>';
               try {
                   if($order->company_id == 61560){
                       $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Dit gavevalg kan &aelig;ndres i 24 timer. Derefter overf&oslash;res dit valg til pakkeriet, og det vil herefter ikke v&aelig;re muligt at foretage &aelig;ndringer.</p><p>Din pakke bliver sendt med Postnord og leveret til n&aelig;rmeste Pakkeshop.&nbsp;</p> <p>Vi afsender normalt din pakke indenfor 10 arbejdsdage dog tidligst den 2. december 2024.</p>                                   <p>Bem&aelig;rk at der kan opleves ekstra leveringstid op til 20 dage i h&oslash;js&aelig;sonen (december, januar og februar)</p>                                    <p>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det endelige gavekort blive sendt til dig. Denne kvittering kan derfor ikke benyttes som gavebevis.</p> <p>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring;&nbsp;<a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/" target="_blank">GAVEKLUBBEN&trade;</a></p><br><br></td></tr>';
                   }
               } catch (ExceptionType $e) {
                   // Code to handle the exception
               }
                    }

                    // OLD TEXT
                    // $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p style="margin: 0cm 0cm 0.0001pt; font-size: 11pt; font-family: Calibri, sans-serif;"><strong>Dit gavevalg kan &aelig;ndres i 24 timer. Derefter overf&oslash;res dit valg til pakkeriet, og det vil herefter ikke v&aelig;re muligt at foretage &aelig;ndringer.<br /><br /><span style="font-size: 1.2em;">Din gave leveres i l&oslash;bet af 5-10 hverdage &ndash; dog tidligst fra 1. december 2021.<span></strong><br /><br /><strong>Havde du sv&aelig;rt ved at v&aelig;lge? </strong></p><p style="margin: 0cm 0cm 0.0001pt; font-size: 11pt; font-family: Calibri, sans-serif;"><strong>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring; <u><span style="color: #44546a;">Gaveklubben&trade;</span></u><span style="color: #44546a;"> -<a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk"> KLIK HER</a></span></strong></p><br><br></td></tr>';

                }

                $maintemplate = str_replace('{RECEIPT_POS1}',$hjemmelevHtml,$maintemplate);

            } else {
                if($order->shop_id == "57" || $order->shop_id == "58" || $order->shop_id == "59" || $order->shop_id == "272" || $order->shop_id == "574" || $order->shop_id == "2550" || $order->shop_id == "2549"){
                    $rs = ExpireDate::find_by_sql("SELECT week_no FROM `expire_date` where expire_date = (SELECT expire_date FROM `shop_user` WHERE shop_id = ".$order->shop_id." && `username` = '".$orderdetails['user_username']."') ");
                    $weeknr = "";
                    if(sizeofgf($rs) > 0){
                        $weeknr =  $rs[0]->attributes["week_no"];
                    }
                    $standartLevHtml = '<tr><td colspan="2"><hr><br><p><strong>Din gave vil bli levert til din arbeidsplass i uke '.$weeknr.' </strong></p>
                                         <p>Skulle du ombestemme deg, kan du endre ditt valg helt frem til deadline. Du logger bare p&aring; gavevalg.no igjen og velger p&aring; nytt.</p>
                                         <p>Har du valgt en opplevelse p&aring; gavekort, vil du motta et gavekort som gave. Denne kvitteringen er ikke gyldig som gavekortbevis.</p><br><br></td></tr>';
                    $maintemplate = str_replace('{RECEIPT_POS1}',$standartLevHtml,$maintemplate);
                }
                else if(in_array($order->shop_id,array(8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366))) {

                    // NO LUKSUS:
                    $standartLevHtml = "";
                    $maintemplate = str_replace('{RECEIPT_POS1}',$standartLevHtml,$maintemplate);

                }
                else if($order->shop_id == "1832" || $order->shop_id == "1981" || $order->shop_id == "2558" || $order->shop_id == "5117"  || $order->shop_id == "4793") {

                    $standartLevHtml = '<tr><td colspan="2"><hr><br><p>Din g&aring;va levereras till din arbetsplats efter &ouml;verenskommelse med den g&aring;voansvariga.<br /><br />Har du best&auml;llt ett presentkort f&ouml;r en hotellvistelse eller M&aring; bra-upplevelse kommer v&auml;rdebeviset inom 14 dagar att skickas till dig antingen p&aring; mejl eller posten.</p><br><br></td></tr>';
                    $maintemplate = str_replace('{RECEIPT_POS1}',$standartLevHtml,$maintemplate);

                }
                else {



              $rs = ExpireDate::find_by_sql("SELECT week_no FROM `expire_date` where expire_date = (SELECT expire_date FROM `shop_user` WHERE shop_id = ".$order->shop_id." && `username` = '".$orderdetails['user_username']."') ");
               $weeknr = "";
              if(sizeofgf($rs) > 0){
                 $weeknr =  $rs[0]->attributes["week_no"];
              }

//              $standartLevHtml = '<tr><td colspan="2"><hr><br><p><strong>Din gave vil blive leveret i uge '.$weeknr.' </strong></p><p><strong>&nbsp;</strong><strong>Skulle du ombestemme dig, kan du &aelig;ndre dit valg helt frem til deadline. Du logger blot p&aring; valgshoppen igen og v&aelig;lger p&aring; ny. </strong></p>
  //            <p><strong>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det rigtige gavekort blive sendt til din arbejdsplads p&aring; lige fod med dine kollegaers gaver. Denne kvittering kan derfor ikke bruges som gavebevis. &nbsp;</strong></p>
    //           <p style="margin: 0cm 0cm 0.0001pt; font-size: 11pt; font-family: Calibri, sans-serif;"><strong>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring; <u><span style="color: #44546a;">Gaveklubben&trade;</span></u><span style="color: #44546a;"> -<a target="_blank" href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk"> KLIK HER</a></span></strong></p><br><br></td></tr>';



                $standartLevHtml = '<tr><td colspan="2"><hr><br><p><strong>Din gave vil blive leveret til din arbejdsplads i uge: '.$weeknr.' </strong></p><p>Skulle du ombestemme dig, kan du &aelig;ndre dit valg helt frem til deadline. Du logger blot p&aring; gaveshoppen igen og v&aelig;lger p&aring; ny.</p>
                <p>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det endelige gavekort blive sendt til din arbejdsplads. Denne kvittering kan derfor ikke benyttes som gavebevis.</p>
                <p>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring; <a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/alle-varer.html" mc:disable-tracking>Gaveklubben</a></p><br><br></td></tr>';

                if($languageId == 2) {
                    $standartLevHtml = '<tr><td colspan="2"><hr><br><p><strong>Your gift will be delivered to your workplace during week: ' . $weeknr . ' </strong></p><p>Should you change your mind before the deadline, simply log in to the gift shop again and select a new gift.</p>
                    <p>If you have ordered a gift card with an experience, stay or cruise, the final gift card will be sent to your workplace. Please note that this receipt cannot be used as a gift certificate.</p>
                    <p>Remember that you can purchase additional design products at special prices on <a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/alle-varer.html" mc:disable-tracking>Gaveklubben</a></p><br><br></td></tr>';
                }

                $maintemplate = str_replace('{RECEIPT_POS1}',$standartLevHtml,$maintemplate);
                 }

            }
        } else {

              $maintemplate = str_replace('{RECEIPT_POS1}',"",$maintemplate);

        }

        $maintemplate = str_replace('{PRESENT_DESCRIPTION}',$description,$maintemplate);
       // $maintemplate = str_replace('{PRESENT_IMAGE}',$orderdetails['present_image'],$maintemplate);
        $receipt_exists = "";
        // find model image and insert

        $presentsM = PresentModel::find_by_sql("select media_path from present_model where model_id = ".$orderdetails['present_model_id']." and language_id = 1 ");
        //$result['present_model_id']  = $presentModel->media_path;

        $modelImage = $presentsM[0]->attributes["media_path"];

        if(strpos($modelImage,"blank") > -1 ){
            $modelImage = GFConfig::BACKEND_URL."/mail/image/".$orderdetails['present_image'];
        }
         if($modelImage == "none.jpg"){
             $sql  = "SELECT *  FROM `present_media` WHERE `present_id` = ".$order->present_id." ORDER BY `present_media`.`index`  ASC limit 1";
             $PresentMedia = PresentMedia::find_by_sql($sql);
             $PresentMediaImg = $PresentMedia[0]->attributes["media_path"];
             $modelImage = "http://system.gavefabrikken.dk/gavefabrikken_backend/views/media/user/".$PresentMediaImg.".jpg";

         }
         //$fileName = pathinfo($modelImage, PATHINFO_FILENAME);
         // background-image: url("https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/user/lyss46tcbbc8skggdfh11654764149.jpg"); width: 549px; height: 520px;
        $rendomFilename = self::generateRandomString(30);
         $fullPath = $_SERVER["DOCUMENT_ROOT"]."/gavefabrikken_backend/views/media/mail/".$rendomFilename. "_small.jpg";
         $realPath = "https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/mail/".$rendomFilename. "_small.jpg";


         if(strstr($modelImage,"http") == false){
             $modelImage = "http://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/".$modelImage;
         }



         $image = @imagecreatefromjpeg($modelImage);
         if (!$image) {
             return null;
         }

         $width = imagesx($image);
         $height = imagesy($image);
         $ration = $width / $height;
         $new_height = 250 / $ration;
         $new_height = (int) $new_height;
         $new_width = 250;
         $imageResized = imagescale($image, $new_width, $new_height);
         $write = imagejpeg($imageResized, $fullPath);
         imagedestroy($imageResized);

         if ($write) {
             $modelImageStr = "<img src=\"".$realPath."\" alt=\"gave\" style=\"width: 250px\" />";
            $maintemplate = str_replace('{MODEL_IMAGE}',$modelImageStr ,$maintemplate);
         }

        $maintemplate = str_replace('{MODEL_NAME}',utf8_encode($orderdetails['present_model_name']),$maintemplate);


        // QR logic
        $htmlQR = "";
        if($orderdetails['shop_is_gift_certificate'] == 0 && $shop->show_qr == 1){

            $pathQr = GFConfig::BACKEND_URL."thirdparty/phpqrcode/index.php?value=".$orderdetails['order_no'];

            //$typeQr = pathinfo($pathQr, PATHINFO_EXTENSION);
            //$dataQr = file_get_contents($pathQr);
            //$baseQr = 'data:image/png;base64,' . base64_encode($dataQr);
            $htmlQR =  "<tr>
            <td colspan='2'>
            <table border=0 width=100%>
                    <tr>
                     <td ></td>
                  <td >

                  <img src='".$pathQr."' />
                  </td>
                  <td ></td>
                  </tr>

            </table>
            </td>
        </tr>";
        } else {
           $htmlQR = "";
        }
       
        // Shops where qr code is disabled
        $qrDisabledShops = array(1821);
        if(in_array($shop->id,$qrDisabledShops)) {
            $htmlQR = "";
        }

        
       $maintemplate = str_replace('{qr}',$htmlQR ,$maintemplate);
         $orderhistory = null;


         if($orderhistory == null){
             $orderhistory = [];
         }

        if(count($orderhistory)>0)  {
          $orderno = end($orderhistory)->order_no;
          $receipt_exists  = $mailtempate->template_receipt_exists;
          $receipt_exists = str_replace('{ORDER_NO}',$orderno,$receipt_exists);
         }

        $maintemplate = str_replace('{RECEIPT_EXISTS}',$receipt_exists,$maintemplate);

        $deliveryinfo = "";
        $maintemplate = str_replace('{DELIVERY_INFO}',$deliveryinfo,$maintemplate);

        //Append User Attributes
        $userattributes = "";
        foreach($orderdetails['attributes'] as $key => $val) {
           // $key_ = utf8_decode($key);
          //  $val_ = utf8_decode($val);
            if($val!="")
              $userattributes .= "<tr><td align='left' >$key</td><td align='right'>$val</td></tr>";
        }

        $maintemplate = str_replace('{USER_DETAILS}',$userattributes,$maintemplate);

        $extra = "";
        if($shop->id==297) {
            if($languageId==1)
            {
                //$extra = "</table><hr /><table><tr><td align='left' colspan=2><br>Støt UNICEFs projekter for børn verden rundt: Giv en personlig donation til UNICEF via MobilePay på +45 20 11 47 00 (skriv 'Novozymes' i kommentarfeltet). Tak!</td></tr></table>";
            }else if($languageId==2)  {
                //$extra = "</table><hr /><table><tr><td align='left' colspan=2><br>Help support UNICEF's work with children around the globe: Make a private donation to UNICEF via MobilePay on +45 20 11 47 00  (Write 'Novozymes' in the comment field). Thank you!</td></tr></table>";
            }
            $extra = utf8_decode($extra);
        }



        if($shop->shipment_date != "" && $shop->id != 297 && $shop->id != 4512){
            //$maintemplate = str_replace('{SHIPMENT_DATE}',$shop->shipment_date->format('d-m-Y'),$maintemplate);
            if($languageId==1)
            {
                $extra = "</table><hr /><table width=300><tr><td align='left' width=150>Leveringsdato:</td><td align='left' width=150>".$shop->shipment_date->format('d-m-Y')."</td></tr></table>";
                if($shop->id == 4424 || $shop->id == 5440 ){
                    $extra = ' </table><br /><hr /><table width=90%><tr><td><div><p>Din gave leveres til n&aelig;rmeste ledige pakkeshop i l&oslash;bet af uge 46 og 47. <br /> <br /> Ved reklamation eller sp&oslash;rgsm&aring;l til bestillingen skal du kontakte GaveFabrikken p&aring; <a mc:disable-tracking href="mailto:julegaveservice@gavefabrikken.dk">julegaveservice@gavefabrikken.dk</a>.&nbsp;</p></div>
                            </td></tr></table><br><br><br>';
                }
                if($shop->id == 5452 || $shop->id == 5455 ){
                    $extra = ' </table><br /><hr /><table width=90%><tr><td><div><p>Din gave leveres til n&aelig;rmeste ledige pakkeshop i l&oslash;bet af uge 50 og 51. <br /> <br /> Ved reklamation eller sp&oslash;rgsm&aring;l til bestillingen skal du kontakte GaveFabrikken p&aring; <a mc:disable-tracking href="mailto:julegaveservice@gavefabrikken.dk">julegaveservice@gavefabrikken.dk</a>.&nbsp;</p></div>
                            </td></tr></table><br><br><br>';
                }
                if($shop->id == 5456 || $shop->id == 5457 ){
                    $extra = ' </table><br /><hr /><table width=90%><tr><td><div><p>Din gave leveres til n&aelig;rmeste ledige pakkeshop i l&oslash;bet af uge 3 og 4. <br /> <br /> Ved reklamation eller sp&oslash;rgsm&aring;l til bestillingen skal du kontakte GaveFabrikken p&aring; <a mc:disable-tracking href="mailto:julegaveservice@gavefabrikken.dk">julegaveservice@gavefabrikken.dk</a>.&nbsp;</p></div>
                            </td></tr></table><br><br><br>';
                }




            }else if($languageId==2)  {
                $extra = "</table><hr /><table width=90%><tr><td align='left' width=150>Delivery Date:</td><td align='left' width=150>".$shop->shipment_date->format('d-m-Y')."</td></tr></table>";
                if($shop->id == 4424 || $shop->id == 5440 ){
                    $extra = ' </table><br /><hr /><table width=90%><tr><td><div><p>Your gift will be delivered to the nearest available parcel shop during weeks 46 and 47.</p>
                                    <p>For complaints or questions about the order, please contact GaveFabrikken at <a href="mailto:julegaeservice@gavefabrikken.dk" disable-tracking >julegaeservice@gavefabrikken.dk</a>.</p></div>
                            </td></tr></table><br><br><br>';
                }
                if($shop->id == 5452 || $shop->id == 5455 ){
                    $extra = ' </table><br /><hr /><table width=90%><tr><td><div><p>Your gift will be delivered to the nearest available parcel shop during weeks 50 and 51.</p>
                                    <p>For complaints or questions about the order, please contact GaveFabrikken at <a href="mailto:julegaeservice@gavefabrikken.dk" disable-tracking >julegaeservice@gavefabrikken.dk</a>.</p></div>
                            </td></tr></table><br><br><br>';
                }
                if($shop->id == 5456 || $shop->id == 5457 ){
                    $extra = ' </table><br /><hr /><table width=90%><tr><td><div><p>Your gift will be delivered to the nearest available parcel shop during weeks 3 and 4.</p>
                                    <p>For complaints or questions about the order, please contact GaveFabrikken at <a href="mailto:julegaeservice@gavefabrikken.dk" disable-tracking >julegaeservice@gavefabrikken.dk</a>.</p></div>
                            </td></tr></table><br><br><br>';
                }


            }
            $extra = utf8_decode($extra);


        }

        if($shop->id == 6351){
            $maintemplate.=  '<br><br><br><hr><p><em>Ved eventuelle sp&oslash;rgsm&aring;l eller reklamation kan du skrive til mailen </em><a href="mailto:sly@gavefabrikken.dk"><em>sly@gavefabrikken.dk</em></a></p><p><em>Kunne du t&aelig;nke dig at supplere dit valg? </em></p><p><em>P&aring; GaveFabrikkens gaveshop har du mulighed for at bestille eksklusive produkter til fordelagtige priser. Interesseret s&aring; se mere </em><a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/alle-varer.html" mc:disable-tracking><em>www.gaveklubben.dk</em></a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p><em>For any questions or complaints, you can write to the email sly@gavefabrikken.dk</em></p><p><em>Would you like to add to your choice?</em></p><p><em>At GaveFabrikken\'s gift shop you have the opportunity to order exclusive products at favorable prices. Interested, see more </em><a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/alle-varer.html" mc:disable-tracking><em>www.gaveklubben.dk</em></a></p><br><br>';

        }

        if($shop->id == 1648000){
            $extra =  '<tr><td colspan=2><br><br><p><b>Julegaven kan afhentes, mod forevisning af denne kvittering, i CAU’s hus på Amager Strandvej 418 fra 30. november til 18. december i tidsrummet 9-15</b></p></td></tr>';
            $extra = utf8_decode($extra);
        }

// jp $languageId
         if($shop->id == 7045){

             $jpTXT = "";


             $jp_home = [183775,183770,183774,183780,183782,183803,183814,183814,183815,190609,190667,191312,191312,192493,192529,197818,197818,198711];
             $jp_shop = [183783,183776,183776,195511,195511,183798];
             $jp_itemID = $orderdetails["present_id"];
             if (in_array($jp_itemID, $jp_home, true)) {
                 if($languageId == 1){
                     $jpTXT = '<hr /><p>Din gave vil blive leveret med daoHOME fra midten af december og frem mod jul.<br /><br />Har du sp&oslash;rgsm&aring;l af teknisk karakter eller vedr. levering kan det ske til GaveFabrikken p&aring;&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Sp&oslash;rgsm&aring;l af intern karakter vedr. julegaver i JP/Politikens Hus kan rettes til Koncern HR p&aring; <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a></p>';
                 } else {
                     $jpTXT = '<hr /><p>Your gift will be delivered with daoHOME from mid-December until Christmas.<br /><br />If you have any technical questions regarding the giftshop or delivery, please contact Gavefabrikken on&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Internal questions regarding Christmas gifts in JP/Politikens Hus, please contact HR on <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a>.</p>';
                 }

             }
             if (in_array($jp_itemID, $jp_shop, true)) {
                 if($languageId == 1){
                     $jpTXT = '<hr /><p>Din gave bliver leveret til en daoSHOP n&aelig;r dig fra midten af december og frem mod jul. Du vil modtage besked, n&aring;r gaven kan afhentes.<br /><br />Har du sp&oslash;rgsm&aring;l af teknisk karakter eller vedr. levering kan det ske til GaveFabrikken p&aring;&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Sp&oslash;rgsm&aring;l af intern karakter vedr. julegaver i JP/Politikens Hus kan rettes til Koncern HR p&aring; <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a></p>';
                 } else {
                     $jpTXT = '<hr /><p>Your gift will be delivered to a daoSHOP near you from mid-December until Christmas. You will receive a message when your gift is ready to be picked up. <br /><br />If you have any technical questions regarding the giftshop or delivery, please contact Gavefabrikken on&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Internal questions regarding Christmas gifts in JP/Politikens Hus, please contact HR on <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a>.</p>';
                 }

             }
             $extra =  '<tr><td colspan=2><br><br>'.$jpTXT.'<br><br><br><br></td></tr>';
             $extra = utf8_decode($extra);
             if($jp_itemID == 183819){
                 if($languageId == 1){
                     $jpTXT = '<hr /><p>Tak for din donation til Ukrainian Media Fund</p>';
                 } else {
                     $jpTXT = '<hr /><p>Thank you for your donation to Ukrainian Media Fund</p>';
                 }
                 $extra =  '<tr><td colspan=2><br><br>'.$jpTXT.'<br><br><br><br></td></tr>';
                 $extra = utf8_decode($extra);

             }
             $maintemplate = str_replace('{EXTRA}',$extra,$maintemplate);

         }

         if($shop->id == 8070){

             $jpTXT = "";

             $jp_home = [219496,219495,219487,219500,219489,219491,219488,219505,219499,219506,219501,219497,219492];
             $jp_shop = [219504,219494,219493,219490];

             $jp_itemID = $orderdetails["present_id"];
             if (in_array($jp_itemID, $jp_home, true)) {
                 if($languageId == 1){
                     $jpTXT = '<hr /><p>Din gave vil blive leveret med daoHOME fra midten af december og frem mod jul.<br /><br />Har du sp&oslash;rgsm&aring;l af teknisk karakter eller vedr. levering kan det ske til GaveFabrikken p&aring;&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Sp&oslash;rgsm&aring;l af intern karakter vedr. julegaver i JP/Politikens Hus kan rettes til Koncern HR p&aring; <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a></p>';
                 } else {
                     $jpTXT = '<hr /><p>Your gift will be delivered with daoHOME from mid-December until Christmas.<br /><br />If you have any technical questions regarding the giftshop or delivery, please contact Gavefabrikken on&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Internal questions regarding Christmas gifts in JP/Politikens Hus, please contact HR on <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a>.</p>';
                 }

             }
             if (in_array($jp_itemID, $jp_shop, true)) {
                 if($languageId == 1){
                     $jpTXT = '<hr /><p>Din gave bliver leveret til en daoSHOP n&aelig;r dig fra midten af december og frem mod jul. Du vil modtage besked, n&aring;r gaven kan afhentes.<br /><br />Har du sp&oslash;rgsm&aring;l af teknisk karakter eller vedr. levering kan det ske til GaveFabrikken p&aring;&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Sp&oslash;rgsm&aring;l af intern karakter vedr. julegaver i JP/Politikens Hus kan rettes til Koncern HR p&aring; <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a></p>';
                 } else {
                     $jpTXT = '<hr /><p>Your gift will be delivered to a daoSHOP near you from mid-December until Christmas. You will receive a message when your gift is ready to be picked up. <br /><br />If you have any technical questions regarding the giftshop or delivery, please contact Gavefabrikken on&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Internal questions regarding Christmas gifts in JP/Politikens Hus, please contact HR on <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a>.</p>';
                 }

             }
             $extra =  '<tr><td colspan=2><br><br>'.$jpTXT.'<br><br><br><br></td></tr>';
             $extra = utf8_decode($extra);
             if($jp_itemID == 219498){
                 if($languageId == 1){
                     $jpTXT = '<hr /><p>Tak for din donation til Ukrainian Media Fund</p>';
                 } else {
                     $jpTXT = '<hr /><p>Thank you for your donation to Ukrainian Media Fund</p>';
                 }
                 $extra =  '<tr><td colspan=2><br><br>'.$jpTXT.'<br><br><br><br></td></tr>';
                 $extra = utf8_decode($extra);

             }
             $maintemplate = str_replace('{EXTRA}',$extra,$maintemplate);

         }

         if($shop->id == 6007){
             $jpTXT = "";
             $jp_home = [145480,145487,145486,145485,145483,145484,145495,145497];
             $jp_shop = [145476,145477,145493,145496,145494,145490,145500,145482,145499,145501];
             $jp_itemID = $orderdetails["present_id"];
             if (in_array($jp_itemID, $jp_home, true)) {
                 $jpTXT = '<tr><td><hr>Din gave vil blive leveret med daoHOME fra midten af december og frem mod jul.</p><p>Sp&oslash;rgsm&aring;l af teknisk karakter til gaveshoppen eller levering kan ske til GaveFabrikken p&aring;&nbsp;+45 70 70 20 27 eller&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Sp&oslash;rgsm&aring;l af intern karakter vedr. julegaver i JP/Politikens Hus kan rettes til Koncern HR p&aring; <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a>.</td><';
             }
             if (in_array($jp_itemID, $jp_shop, true)) {
                 $jpTXT = '<hr>Din gave bliver leveret til en daoSHOP n&aelig;r dig fra midten af december og frem mod jul. Du vil modtage besked, n&aring;r gaven kan afhentes.</p><p>Sp&oslash;rgsm&aring;l af teknisk karakter til gaveshoppen eller levering kan ske til GaveFabrikken p&aring;&nbsp;+45 70 70 20 27 eller&nbsp;<a href="mailto:jppol@gavefabrikken.dk">jppol@gavefabrikken.dk</a>. Sp&oslash;rgsm&aring;l af intern karakter vedr. julegaver i JP/Politikens Hus kan rettes til Koncern HR p&aring; <a href="mailto:julegaver@jppol.dk">julegaver@jppol.dk</a>.';
             }
             $extra =  '<tr><td colspan=2><br><br><p>'.$jpTXT.'</p></td></tr>';
             $extra = utf8_decode($extra);
         }



        $maintemplate = str_replace('{EXTRA}',$extra,$maintemplate);






        /*  rambøll hack */
         if($shop->id == 599){
         $ramText1 = '<div style=" text-align: left; width:800px; margin-left:40px;"><br> <br><p style="margin: 0cm 0cm 0.0001pt; font-size: 11pt; font-family: Calibri, sans-serif; color: black;"><strong><span style="font-size: 12.0pt; font-family: Verdana, sans-serif; color: #00b0f0;">Collect your Christmas present on the 12<sup>th</sup> of December 2018</span></strong></p>
                            <p>Dear colleague<br>
                            You can pick up your Christmas present on the 12<sup>th</sup> of December 2018 from 10:00 to 14:00.</p>
                            <p>We will be using QR codes for pick up. This way you don\'t have to print the receipt.&nbsp;<br>
                            You can show the receipt on your smartphone and we will scan your code from the phone.<br />If you do not have a smartphone please print and bring the receipt with you when picking up your present.</p>
                            <p><br /><strong>NOTE:</strong> If you are not able to make it on this day, please arrange for a colleague to pick up the present for you.<br>
                            All presents must be picked up before Christmas. Remaining presents will be returned.&nbsp;</p>
                            <p><br />The Christmas Present Committee</p></div>';
         $ramText2 =   '<br><br><div style=" text-align: left; width:800px; margin-left:40px;"><p style="margin-right: 0cm; margin-left: 0cm; font-size: 11pt; font-family: Calibri, sans-serif; color: black;"><strong><span style="font-family: Verdana, sans-serif; color: #00b0f0;">Would you like to buy an addition to your company Christmas present?</span></strong></p>
                            <p>Here is the opportunity for you to buy exclusive products at favorable prices <br > at Ramboll Private Shop for a short period from 12<sup>th</sup>. December &ndash; 31<sup>th</sup>. January 2019:</p>
                             <p><a target="_blanck" href="https://www.shopgavefabrikken.dk/vipshop/907/"><strong><u>Link to shop</u></strong></a></p></div><br><br>';

            $maintemplate = str_replace('{text1}',$ramText1,$maintemplate);
            $maintemplate = str_replace('{text2}',$ramText2,$maintemplate);
         } else {
            $maintemplate = str_replace('{text1}',"",$maintemplate);
            $maintemplate = str_replace('{text2}',"",$maintemplate);
         }



        $maildata = [];
        $maildata['sender_email'] = $mailtempate->sender_receipt;

        // Special rule for Tryg DK/NO/SE 2022, use username (username is email)
        if(in_array($shopuser->shop_id,array(3083,3471,3834))) {
            $maildata['recipent_email'] = $shopuser->username;
        }
        else {
            $maildata['recipent_email'] =$orderdetails['email'];
        }
        $maildata['order_id'] = $orderdetails['order_id'];
        $maildata['subject']= $mailtempate->subject_receipt;
        $maildata['body'] = $maintemplate;
        $maildata['sent'] = $send;
        // Set mailserver
//        $maildata['mailserver_id'] = $shop->mailserver_id;
         $maildata['mailserver_id'] = 4;

          if($shop->id == 1832 || $shop->id == 1981 || $shop->id == 4793 || $shop->id == 5117 || $shop->id == 8271) {
             // retter tekst på gaveklubben:
              if($languageId == 5){
                  $maildata['body']  = str_replace("Gaveklubben tilmelding", "Gåvoklubben medlem", $maildata['body']);
              }
              $maildata['mailserver_id'] = 5;
         }

         try {
             if (strpos( $maildata['recipent_email'], "getinge.com") !== false) {
                 $maildata['mailserver_id'] = 8;
            }
         } catch (Exception $e) {

         }


        MailQueue::createMailQueue($maildata,1);

//                throw new exception('smork')  ;

     }



      //tror at denne bruges fra company..
      public function cancelCompanyOrder()  {
        $companyorder = CompanyOrder::find($_POST['company_id']);
        if($companyorder->is_cancelled)
        {
           throw new exception('Order er allerede annulleret');
        }
        if($companyorder->is_shipped)
        {
           throw new exception('Ordren kan ikke anulleres, da den allerede er sendt.');
        }


        $companyorder->is_cancelled = true;
        $companyorder->save();
        $shopusers = ShopUser::find('all',array(
         'conditions' => array('company_id = ? and username BETWEEN  ? AND ? ',
                     $companyorder->company_id,
                     $companyorder->certificate_no_begin,
                     $companyorder->certificate_no_end )
         )
         );
        foreach($shopusers as $shopuser) {
            $shopuser->blocked = true;
            $shopuser->save();
        }
        $dummy[] = [];
        response::success(json_encode($dummy));
   }

    //Registrer at en gave er udlevet
    public function register() {
      header('Location:/gavefabrikken_backend/index.php?rt=registrer/register&orderno='.@$_REQUEST["orderno"]);
      return;
      $Order  =  Order::find_by_order_no($_REQUEST['orderno']);
      if($Order) {
      $shop = Shop::find($Order->shop_id);
      if($Order && $shop->open_for_registration)  {
          $present = present::find($Order->present_id);

      echo '<html>';
      echo '<head><style>body {
          font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
            font-size:20px;
          line-height: 2;
         </style>';
      echo '</head>';
      echo '<body>';
      echo '<center><div style="font-size: 6vw;">Gaveregistrering</div><br><hr />' ;

      if($Order->registered==0) {

        echo '<img   src="../../gavefabrikken_backend/views/media/user/'.$present->present_media[0]->media_path.'.jpg" alt="" /><br><br>';

      if($Order->user_name !="")
        echo '<table width=90%  style="font-size: 4vw;"><tr><td width=25%>Navn:</td><td width=75%>'.$Order->user_name.'</td></tr>';

        if($Order->present_model_name!="")
          echo '<tr><td><label>Model:</label></td><td>'.$Order->present_model_name.'</td></tr>';


        echo '<tr><td>Email:</td><td>'.$Order->user_email.'</td></tr>';
        echo '<tr><td>Gave:</td><td>'.utf8_decode($Order->present_name).'</td></tr></table>';

           $orderno = $_REQUEST['orderno'];

        echo '<br> <br><input style="width:50%;height:100px;font-size: 4vw;" type="button" onclick="location.href=\''.GFConfig::BACKEND_URL.'index.php?rt=order/doRegister&orderno='.$orderno.'\';" value="Registrer" /><br>';


      } else
      {
         echo '<div style="font-size: 6vw;">Ordrenr. '.$_REQUEST['orderno'].' er udleveret d.'.$Order->registered_date->format('d-m-Y')."</div>";
      }


      echo '</body>';
      echo '</html>';
      }  else {
      echo '<html>';
      echo '<head><style>body {
                  font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
                  font-size:20px;
                  line-height: 2;
             </style>';
      echo '</head>';
      echo '<body>';
      echo '<center><div style="font-size: 6vw;">Gaveudleveringen er lukket</div><br><hr />' ;
      echo '</body></html>';
      }
      }  else {
            echo '<html>';
      echo '<head><style>body {
                  font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
                  font-size:20px;
                  line-height: 2;
             </style>';
      echo '</head>';
      echo '<body>';
      echo '<center><div style="font-size: 6vw;">Ordrenr. '.$_REQUEST['orderno'].' blev ikke fundet!</div><br><hr />' ;
      echo '</body></html>';

      }





    }
    // bruges af skanner appp
    public function doRegister() {
      $Order  = Order::find_by_order_no($_REQUEST['orderno']);
      $Order->registered = 1;
      $Order->registered_date = date('d-m-Y H:i:s');
      $Order->save();
      System::connection()->commit();
      echo '<div style="font-size: 6vw;">Ordernr. '.$_REQUEST['orderno'].' blev registreret</div>';
    }


    //bruges af kundepanel til at registrere at en gave er udleveret ti lkunde
      public function RegisterOrder() {
      $order = Order::find_by_shopuser_id($_POST['user_id']);

      if($order){
        if($order->registered == 0)  {
          $order->registered = 1;
          $order->registered_date = date('d-m-Y H:i:s');
          $order->save();
          $dummy = [];
          response::success(make_json("result", $dummy));
        }
        else
        {
            throw new exception ('Denne gave er allerede udleveret');
        }
    } else {
           throw new exception ('Denne bruger har endnu ikke valgt gave');
       }
    }
    
    
   public function getkvitteringhtml()
   {
       // Get inputs
      $userid = isset($_REQUEST["user_id"]) ? intval($_REQUEST["user_id"]) : 0;
      $orderid = isset($_REQUEST["order_id"]) ? intval($_REQUEST["order_id"]) : 0;
      
      // Find ind mailqueue
      $mail = MailQueue::find_by_sql("SELECT * FROM mail_queue WHERE order_id > 0 && order_id = ".$orderid." ORDER BY id DESC");
      
      // Find order
      $order = Order::find_by_sql("SELECT * FROM `order` WHERE shopuser_id > 0 && shopuser_id = ".$userid." && id > 0 && id = ".$orderid);
                   
      // Check if data is valid                             
      if($userid <= 0 || $orderid <= 0 || countgf($mail) == 0 || countgf($order) == 0)
      {
        ?><html>
  <head>
    <meta charset='UTF-8'>
    <title>Kvittering.</title>
      <style type="text/css">
        td { width:30%; }
		.base{ width:150px; } </style>
  </head>


<body>


  <table width='99%'>
		<tr><td colspan=2><center><h2>Kvittering for gavevalg</h2><br><br></center></td></tr>
		<tr><td colspan=2><center>Kan ikke finde en kvittering for dit valg.<br><br></center></td></tr>
    </table>

  </body>
</html><?php
      }
      // Output mail
      else
      {
         echo '<button onclick="window.print()">PRINT</button><style> @media print {button  { display: none !important; } body{   text-align: center; } tr:nth-child(2){ display:none;}  img{ height:200px;   } </style>'. $mail[0]->body;
      }

   }

    public function getOrderNO() {
         $dummy = [];
         $order = Order::find_by_shopuser_id($_POST['user_id']);
       

         if($order) {
           $dummy['orderno'] = $order->order_no;
         }
         response::success(json_encode($dummy));
    }

      private static function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  }
?>
