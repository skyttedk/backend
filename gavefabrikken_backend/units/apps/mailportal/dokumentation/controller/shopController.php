<?php

// Controller shop
// Date created  Sun, 03 Apr 2016 21:00:47 +0200
// Created by Bitworks
class ShopController Extends baseController {

  public function Index() {
  }

  public function updateBasked(){


      $ShopUser = ShopUser::find($_POST['basketID']);
      $ShopUser->basket_id = $_POST["userID"];
      $ShopUser->save();
      response::success(make_json("shop", $ShopUser));

  }


  public function update(){
      $result = Shop::updateSingle($_POST);
      $res = array();
      response::success(make_json("shop", $res));

  }
  public function checkShopSettings(){
    $return = ["status"=>"1","data"=>""];
    echo  json_encode($return);
  }


  public function show() {
  //  $shops = Shop::all();
  //  $this->registry->template->shops = $shops;
      $this->registry->template->userPermission = "";


          $this->registry->template->userPermission = UserTabPermission::find('all', array('conditions' => "systemuser_id = ".intval($_SESSION["syslogin"])));


      $this->registry->template->show('shop_view');
  }


   public function showNew() {
    $shops = Shop::all();
       $sysuser =   router::$systemUser;
       $show_to_saleperson = false;
       $sysuserPremission = UserTabPermission::all(array('conditions' => 'systemuser_id = '.$sysuser->attributes["id"].' and tap_id = 1000' ));
       if(sizeof($sysuserPremission) > 0){
           $show_to_saleperson = true;
       }
    $this->registry->template->show_to_saleperson = $show_to_saleperson;
    $this->registry->template->show('shopNew_view');
  }

  public function read() {
    $shop = shop::readShop($_POST['id']);
    // $options = array('include' => array('company', 'descriptions', 'presents', 'attributes_','reports'));
    $options = array('include' => array('descriptions', 'presents', 'attributes_','reports','addresses'));
    response::success(make_json("shop", $shop, $options));
  }

  public function read_debug() {
    $shop = shop::readShop($_POST['id']);
//    $options = array('include' => array('company', 'descriptions', 'presents', 'attributes_','reports'));
//    $options = array('include' => array('descriptions', 'presents', 'attributes_','reports','company'));
    $options = array('include' => array('descriptions', 'presents', 'attributes_','reports'));
   // $shop->company = '123';
    //dump($shop->company_shops);
    $c = null;
    foreach($shop->company_shops as $companyshop) {
      $company = Company::find($companyshop->company_id);
      if($company->is_gift_certificate==0) {
        response::success(make_json("shop", $company));
      }
//        return ($company)



    }

//    response::success(make_json("shop", $shop, $options));
  }
  /* 2017 funcitons */

 public function alarm(){

    $query = http_build_query(array(
    'token' => 'rhSukWyyQLyFyMRXlbNBmW3LFHb15wfLXwACjUIEWnn4DBU_jrZMAORCeSCrotwf',
    'sender' => 'Alarm',
    'message' => 'Fejl: opdatere valgshop: code: '.$_POST["code"].' shop: '.$_POST["shop"].' dato: '.date("Y-m-d h:i:sa") ,
    'recipients.0.msisdn' => '004553746555',
    ));
    // Send it
   // $result = file_get_contents('https://gatewayapi.com/rest/mtsms?' . $query);
     echo "hej";


 }

  public function readFull() {
      // implementer sikkerhed her.

      //2. tjek at id = shop id på user.

    $shop = shop::readShop($_POST['id']);
    $options = array('include' => array('descriptions', 'presents', 'attributes_'));
    response::success(make_json("shop", $shop,$options));
  }
   // ulrich show / hide
    public function readFull_v2() {

      // implementer sikkerhed her.
    if(intvalgf($_POST["id"]) <= 0){
       throw new exception('link_error');
    }
     $shopuser = ShopUser::find_by_token_and_shop_id($_POST['token'], intvalgf($_POST['id']));


    // print_R($shopuser);


/*
     if(count((array)$shopuser) == 0){
       throw new exception('link_error');
     }
*/

        if(!sizeofgf($shopuser) > 0){
            $shopuser = ShopUser::find_by_token($_POST['token']);
            if(!sizeofgf($shopuser) > 0) {
                // Do not trust user inputted id
                //$shop = shop::readShop($_POST['id']);
                throw new exception("unknown");
            } else {
                $shop = shop::find($shopuser->shop_id);
                throw new exception($shop->link);
            }

        }

       //print_r($shopuser);
      //2. tjek at id = shop id på user.
     $shop = shop::readShop($_POST['id']);

         $allways = [];
         $allwaysClose = [];
       //  $shop->attributes["link"] = "";
         $shop->attributes["demo_password"] = "";
         $shop->attributes["demo_user_id"] = "";
         $shop->attributes["blocked_text"] = "";
         $shop->attributes["pt_saleperson"] = "";
         $shop->attributes["pt_shopname"] = "";
         $shop->attributes["ptupdate"] = "";
         $shop->attributes["kundepanel_email_regel"] = "";
         $shop->attributes["rapport_email"] = "";
         $shop->attributes["receipt_recipent"] = "";
         $shop->attributes["close_date"] = "";
         $shop->attributes["report_attributes"] = "";
         $shop->attributes["demo_username"] = "";
         $shop->attributes["receipt_link"] = "";
         $shop->attributes["modified_datetime"] = "";
         $shop->attributes["allways"] = $allways;
         $shop->attributes["allwaysclose"] = $allwaysClose;
         if($shopuser->username == "free"){
            $rules = [];
         } else {
              $rules = shop_present_company_rules::find_by_sql("select distinct present_id, rules from shop_present_company_rules inner join shop_user on shop_present_company_rules.company_id = shop_user.company_id where rules > 0 and token = '".$_POST['token']."'");

              $rs = company::find_by_sql("SELECT pid FROM `company` where `id` in (SELECT company_id FROM `shop_user` WHERE `token` LIKE '".$_POST['token']."')");
              if(sizeofgf($rs) > 0){
                $pid = $rs[0]->attributes["pid"];
                if($pid != 0){
                    $rules = shop_present_company_rules::find_by_sql("select distinct present_id, rules from shop_present_company_rules where rules > 0 and company_id = ".$pid);
                }
              }





         }




         $PressentOptionsRs = shop_present_company_rules::find_by_sql("SELECT present_model_options.present_id,present_model_options.expire_data,present_model_options.visibility FROM `present_model_options` INNER JOIN present on present_model_options.present_id = present.id WHERE present.shop_id = ".$_POST['id']);


         $PressentOptionsData = [];
         foreach($PressentOptionsRs as $PressentOptions){
            $PressentOptionsData[$PressentOptions->attributes["present_id"]][$PressentOptions->attributes["expire_data"]] = $PressentOptions->attributes["visibility"] ;

         }
         // den if sætning skal fjernet når vi går i produktion
       

                 if(sizeofgf($rules) > 0 ){

                    foreach($rules as $item){
                        if($item->attributes["rules"] == 1){
                            $allways[] = $item->attributes["present_id"];
                        }
                        if($item->attributes["rules"] == 2){
                            $allwaysClose[] = $item->attributes["present_id"];
                        }
                    }
                    $shop->attributes["allways"] = $allways;
                    $shop->attributes["allwaysclose"] = $allwaysClose;
                }


        $shop->attributes["optionsData"] = $PressentOptionsData;
        $options = array('include' => array('descriptions', 'presents', 'attributes_'));
        response::success(make_json("shop", $shop,$options));
  }




  //Gælder kun for valgshops
  public function readSimple() {
     
    $link = $_POST['link'];
    $shop = Shop::find_by_sql("SELECT * FROM shop where LOWER(link) = '".$link."'");
    if(count($shop)==1)
    {
      $shop = $shop[0];
      $result = Array();
      $result['image_path'] = $shop->image_path;
      $result['descriptions'] = json_encode($shop->descriptions);
      $result['language'] = $shop->language_settings;
      $result['blocked'] = $shop->blocked;
      $result['blocked_text'] = $shop->blocked_text;
      $result['active'] = $shop->active;
      $result['is_demo'] = $shop->is_demo;
      $result['login_design'] = $shop->login_design;
      $result['has_login_splash'] = $shop->has_login_splash;
      $result['login_strict'] = $shop->login_check_strict;
      $result['show_tree_front'] = $shop->show_tree_front;
      $result['localisation'] = $shop->localisation;


     response::success(json_encode($result));
    } else {
      throw new exception('link_error');
    }
   }

  public function getCardAmount(){

    $shopusers = ShopUser::all(array('company_id' => $_POST["id"]));
    $l = countgf($shopusers);
    $dummy = [];
    response::success($l);
  }

  public function getTestBruger(){
      $shopusers = ShopUser::all(array('shop_id' => '65','is_demo' => 0));
      $options = array('include' => array('orders'));
      var_dump($shopusers);
  }


  public function getUsers() {
      $shopusers = ShopUser::all(array('shop_id' => $_POST['id'],'is_demo' => 0));
      $options = array('include' => array('orders'));
      response::success(make_json("users",$shopusers,$options));
  }

  public function getUsersSQL() {

    ini_set('memory_limit','512M');

    $shopId = $_POST['id'];
    $shopusers = ShopUser::find_by_sql("
      SELECT
     `shop_user`.id
     , `shop_user`.shop_id
     , `user_attribute`.*
     , `shop_user`.`shop_id`
     , `shop_attribute`.`shop_id`
     , `shop_user`.`id`
     , `shop_attribute`.`index`
     FROM
     `user_attribute`
     RIGHT JOIN `shop_user`
         ON (`user_attribute`.`shopuser_id` = `shop_user`.`id`)
     INNER JOIN `shop_attribute`
         ON (`user_attribute`.`attribute_id` = `shop_attribute`.`id`)
    WHERE (`shop_user`.`shop_id` = $shopId AND `shop_user`.`is_demo` = 0)
      ORDER BY `shop_user`.`id` ASC, `shop_attribute`.`index` ASC;
     ");
    
    response::success(json_encode($shopusers));
    
  }

  public function searchUsers() {
    $shopid = $_POST['shop_id'];
    $what = $_POST['what'];
    $shopusers = ShopUser::find('all',array('conditions' => array(
        "is_demo = 0 and shop_id = $shopid  and  username like '%$what%'"
   	)));
    $options = array('include' => array('orders'));
    response::success(make_json("users",$shopusers,$options));
  }
  public function getUsersBatch() {
      $shopusers = ShopUser::find('all',array(
            'conditions' => array('shop_id' => $_POST['shop_id'],'is_demo' => 0),
            'limit' => 100,
            'offset' => $_POST['offset']
        ));
      $options = array('include' => array('orders'));
      response::success(make_json("users",$shopusers,$options));
  }

  public function getToUsersHowHasNotPickedUpPresents() {
    $orders = Order::all(array('shop_id' => $_POST['shop_id'],'registered' => 0));
    $result = [];
        foreach($orders as $order)
        {
           $shopuser = ShopUser::find($order->shopuser_id);
           array_push($result,$shopuser);
        }
        response::success(make_json("result",$result));
    }

    public function sendMailsToUsersHowHasNotPickedUpPresents() {

      $i=0;
      $orders = Order::all(array('shop_id' => $_POST['shop_id'],'registered' => 0));

      foreach($orders as $order)
      {
          //find email
          $userattributes = UserAttribute::all(array('shopuser_id' => $order->shopuser_id));
          foreach($userattributes as $attribute)
    	   {
		    if($attribute->is_email)
		      $email = $attribute->attribute_value;
           }
           //dan link
           $shop = Shop::find($order->shop_id);
           $link ='http://system.gavefabrikken.dk/gavevalg/'.$shop->link;
           // opret mail
           $mailqueue = new MailQueue();
		   $mailqueue->sender_name  = 'Gavefabrikken';
		   $mailqueue->sender_email = 'info@gavefabrikken.one';
 		   $mailqueue->recipent_email = $email;
   		   $mailqueue->subject ='Du skal hente din gave';
           $mailqueue->user_id =  $order->shopuser_id;
		   $mailqueue->body ="Du mangler at afhente din gave.";
 		   $mailqueue->save();

      }



        $dummy = [];
      response::success(make_json("result",$dummy));
  }



  public function getShopAttributes() {
      $shopAttributes = ShopAttribute::find('all',array('order' => '`index` asc','conditions' => array('shop_id' => $_POST['id'])));
      $resultJSON = make_json("attributes",$shopAttributes);
      $result = json_decode($resultJSON);
      $shop = Shop::find($_POST['id']);
      $result->shop_id = $shop->id;
      $result->company_id =$shop->company[0]->id;
      response::success(json_encode($result));
    }



  public function getShopCompanies() {
      //Hent liste over virksomheder tilknyttet en shop
   	  $shop = Shop::find($_POST['shop_id']);
      $options = array('include' => array('company'));
      response::success(make_json("shop", $shop, $options));

  }

  public function getShopPresents() {
   	  $shop = Shop::find($_POST['shop_id']);
      $presents = [];
      foreach($shop->presents as $present){
        $p['id'] = $present->present_id;
        $present = Present::find($present->present_id);
        $p['name'] = $present->name;
        $p['variant_list'] = $present->variant_list;
        array_push($presents,$p);
        unset($p);
      }
      response::success(json_encode($presents));
  }
    public function getShopPresents2() {
   	  $shop = Shop::find($_POST['shop_id']);
      $presents = [];
      foreach($shop->presents as $present){
        $p['id'] = $present->present_id;
        $present = Present::find($present->present_id);
        $p['name'] = $present->name;
        $p['variant_list'] = $present->variant_list;
        array_push($presents,$p);
        unset($p);
      }
      response::success(json_encode($presents));
  }




  public function getShopPresentsNew() {
        $shop_id = $_POST["shop_id"];
        $presents = PresentModel::find_by_sql(" select * from present_model where
            present_id in ( SELECT id FROM `present` WHERE `shop_id` = ".intval($shop_id)."   and deleted = 0 and id
		in( SELECT present_id FROM `shop_present` WHERE `shop_id` = ".intval($shop_id)."  AND `is_deleted` = 0 and active = 1 )
		) and language_id = 1  and is_deleted = 0 and active = 0 order by present_id, model_id
            ");




        response::success(json_encode($presents));
  }
  public function deleteShop() {
    $shop = shop::deleteShop($_POST["id"]);
    response::success(make_json("shop", $shop));
  }
  //Create Variations of readAll
  public function readAll() {
    /*
     $shops = shop::all();
    $options = array();
    response::success(make_json("shops", $shops, $options));
    */
  }

  //---------------------------------------------------------------------------------------
  // Custom Controller Actions
  //---------------------------------------------------------------------------------------

      // returnere soft_close
    /*
  public function getSoftCloseOld(){
    $shop_id = $_POST['shop_id'];

    $rs = Shop::find_by_sql("SELECT id, soft_close,edit_allowed FROM shop WHERE id = ".$shop_id );


    // $data = array("soft_close" => $rs[0]->attributes["soft_close"]);
   // response::success(make_json("shops",$data));
   // $dummy = array('status'=>$data);
    response::success(make_json("shops",$rs));
  }
    */

    public function getSoftClose(){
        $shop_id = $_POST['shop_id'];

        $rs = Shop::find_by_sql("SELECT id, soft_close,edit_allowed FROM shop WHERE id = ".$shop_id );

        $response = array(
            "status" => 1,
            "data" => array(
                "shops" => array(
                    0 => array("soft_close" => $rs[0]->soft_close,"edit_allowed" => $rs[0]->edit_allowed)
                )
            )
        );

        echo json_encode($response);

        // $data = array("soft_close" => $rs[0]->attributes["soft_close"]);
        // response::success(make_json("shops",$data));
        // $dummy = array('status'=>$data);
        //response::success(make_json("shops",$rs));
    }


  //Returnerer alle valgshops
  public function readCompanyShops() {
    $shops = Shop::all(array("is_company" => 1)); // // deleted false
    $options = array('include' => array('company', 'descriptions', 'presents', 'attributes_', 'users','deactivated_models'));
    response::success(make_json("shops", $shops, $options));
  }

  //Returnerer kun id og navn
  public function readCompanyShopsSimple() {
    // 2019: id tilføjet
    $shops = Shop::find('all', array('order' => 'name asc','conditions' => array('deleted' => 0,'id' => $_POST["shopId"])));
    $options = array('only' => array('id', 'name'));
    response::success(make_json("shops", $shops, $options));
  }

  //Returnerer kun id og navn
  public function searchCompanyShopsSimple() {
    $what = $_POST['name'];
    $all = "";
    if(isset($_POST["all"])){
      $all = $_POST["all"];
    }
    if($all == ""){
      if($what) {
      $shops = Shop::find('all', array(
          'order' => 'name asc',
          'conditions' => array("deleted = 0 AND name like '%$what%'" )
          ));
      } else {
          $shops = Shop::find('all', array('order' => 'name asc','conditions' => array('deleted' => 0)));
      }
      $options = array('only' => array('id', 'name'));
      response::success(make_json("shops", $shops, $options));
    } else {
            if($what) {
      $shops = Shop::find('all', array(
          'order' => 'name asc',
          'conditions' => array(" name like '%$what%'" )
          ));
      } else {
          $shops = Shop::find('all', array('order' => 'name asc'));
      }
      $options = array('only' => array('id', 'name'));
      response::success(make_json("shops", $shops, $options));
    }
  }

  //Returnerer alle gavekortshops

  public function readGiftcertificateShops() {
    $shops = Shop::all(array("is_gift_certificate" => 1));
    response::success(make_json("shops", $shops));
  }

  //Opret gavekortshop
  public function createGiftcertificateShop() {
    $data = $_POST;
    $shop = shop::createGiftcertificateShop($data);
    response::success(make_json("shop", $shop));
  }

  //Opret valgshop
  public function createCompanyShop() {
    $shop = shop::createCompanyShop($_POST);
    response::success(make_json("shop", $shop));
  }

  public function updateCompanyShop() {
    $shop = shop::updateCompanyShop($_POST);
    response::success(make_json("shop", $shop));
  }


//   ***************   present function *****
   public function previewPresent() {
    $data = $_POST;
    $shopPresent = shop::previewPresent($data);
    response::success(make_json("present", $shopPresent));
  }

  //Add present
  public function addPresent() {
    $data = $_POST;
    $shopPresent = shop::addPresent($data);
    response::success(make_json("present", $shopPresent));
  }

  //Add present
  public function removePresent() {
    $data = $_POST;
    $shopPresent = shop::removePresent($data);
    response::success(make_json("present", $shopPresent));
  }

  public function activatePresent() {
    $shopPresent = shop::activatePresent($_POST['id']);
    response::success(make_json("present", $shopPresent));
  }

  public function deactivatePresent() {
    $shopPresent = shop::deactivatePresent($_POST['id']);
    response::success(make_json("present", $shopPresent));
  }


  //&nbsp;Model activation functions
  static public function activateModel() {
    // shop_id
    // present_id
    // model_id
    $data = $_POST;
    $shopmodel = ShopModel::create($data);
    response::success(make_json("shop", $shopmodel));
  }


  static public function deactivateModel() {
    $shop_id    =   $_POST['shop_id'];
    $present_id =   $_POST['present_id'];
    $model_id   =   $_POST['model_id'];
    $shopmodels = ShopModel::all(array('shop_id' => $shop_id, 'present_id' => $present_id, 'model_id' => $model_id));
     foreach($shopmodels as $model) {
       $model->delete();
     }
    response::success(make_json("shop", $shopmodel));
  }



  // User functions
  public function removeShopUser()
  {
    Shop::removeShopUser($_POST['user_id']);
    response::success(make_json("shop", ""));
  }

/*
  static function addShopUser() {

     // denne function kaldes som en batch funktion, og vi ønsker ikke at der skal komme fejl meddelseler
     // for hver bruger som oprettes

    $data = $_POST["value"];
    foreach($data as $item){
        $shopUser = Shop::addShopUser($item);
        $options = array('include' => array('attributes_'));
    }
    $dummy = [];
    response::success(make_json("result",$dummy));

  }
  */

    static function addShopUser() {
        $data = $_POST;
        $shopUser = Shop::addShopUser($data);
        $options = array('include' => array('attributes_'));
        response::success(make_json("shopuser",$shopUser,$options));


    }


  static function updateShopUser() {
    $data = $_POST;
    $shopUser = Shop::updateShopUser($data);
    $options = array('include' => array('attributes_'));
    response::success(make_json("shopuser",$shopUser,$options));
  }

  static function updateShopUserDelivery() {
    $data = $_POST;
    ShopUser::updateShopUserDelivery($data);
    response::success(make_json("result", ""));
  }

  static function moveShopUser() {

    $userid = $_POST['user_id'];
    $shopuser = ShopUser::find($_POST['user_id']);
    $fromshop = Shop::find($shopuser->shop_id);

    $fromcompanyid =  $shopuser->company_id;
    $tocompanyid   =  $_POST['company_id'];

    if($fromshop->is_gift_certificate==0) {
      throw new exception('Kun gavekort kan flyttes!') ;
    }

    //update shop user
 //   $shopuser->shop_id    = $toshop->id;
    $shopuser->company_id = $tocompanyid;
    $shopuser->save();

    //Update user attributes
    $userattributes = UserAttribute::find('all',array(
      'conditions' => array('shopuser_id = ?',$shopuser->id)
    ));

    foreach($userattributes as $userattribute) {
     // $userattribute->shop_id = $toshop->id;
      $userattribute->company_id =$tocompanyid;
      $userattribute->save();
    }

   // update order attributes
   $orderattributes = OrderAttribute::find('all',array(
     'conditions' => array('shopuser_id = ?',$shopuser->id)
   ));

   foreach($orderattributes as $orderattribute) {
      //$orderattribute->shop_id = $toshop->id;
      $orderattribute->company_id =$tocompanyid;
      $orderattribute->save();
   }

  // update history order attributes
   $orderhistoryattributes = OrderHistoryAttribute::find('all',array(
    'conditions' => array('shopuser_id = ?',$shopuser->id)
   ));

   foreach($orderhistoryattributes as $orderhistoryattribute) {
      //$orderhistoryattribute->shop_id = $toshop->id;
      $orderhistoryattribute->company_id =$tocompanyid;
      $orderhistoryattribute->save();
   }

   //update order
   $orders = Order::find('all',array(
    'conditions' => array('shopuser_id = ?',$shopuser->id)
   )) ;
   foreach($orders as $order) {
      //$order->shop_id = $toshop->id;
      $order->company_id =$tocompanyid;
      $order->save();
   }

   //update history
   $orderhistories = OrderHistory::find('all',array(
    'conditions' => array('shopuser_id = ?',$shopuser->id)
   )) ;
   foreach($orderhistories as $orderhistory) {
      //$orderhistory->shop_id = $toshop->id;
      $orderhistory->company_id =$tocompanyid;
      $orderhistory->save();
   }
   // throw new exception(count($orderhistories));
   response::success(make_json("shopuser",$shopuser));
  }


  //Model functions

   // Attribute functions
    static function addAttribute(){
       $data = $_POST;
       $shop = Shop::find($_POST['shop_id']);
       $attribute =Shop::addAttribute($data);
       response::success(make_json("shopuser", $attribute));
    }

    static function updateAttribute(){
        $data = $_POST;
        $shop = Shop::find($_POST['shop_id']);
        $attribute =Shop::updateAttribute($data);
        $options = array('order' => 'index desc');
        response::success(make_json("shopuser", $attribute,$options));
    }

    static function removeAttribute(){
        $shopattribute = ShopAttribute::find($_POST['id']);
        $shop = Shop::find($shopattribute->shop_id);

       // if($shop->has_users())
       //   throw new Exception('Du kan ikke fjerne felter, da der allerede er tilknyttet brugere til shoppen!');

        $attribute =Shop::removeAttribute($_POST['id']);
        response::success(make_json("shopuser", $attribute));
    }

    static function getShopAttributeWarnings()
    {

        // Load shop attributes
        $shopAttributes = ShopAttribute::find('all',array('order' => '`index` asc','conditions' => array('shop_id' => $_POST['id'])));
        $warningList = array();

        $usernameCount = 0; $passwordCount = 0; $emailCount = 0; $nameCount = 0;
        foreach($shopAttributes as $attribute) {
            if($attribute->is_username > 0) $usernameCount++;
            if($attribute->is_password > 0) $passwordCount++;
            if($attribute->is_email > 0) $emailCount++;
            if($attribute->is_name > 0) $nameCount++;
        }

        if($usernameCount == 0) $warningList[] = "Der er ikke oprettet et felt til brugernavn";
        if($usernameCount > 1) $warningList[] = "Der må ikke oprettes mere end 1 felt til brugernavn";

        if($passwordCount == 0) $warningList[] = "Der er ikke oprettet et felt til adgangskode";
        if($passwordCount > 1) $warningList[] = "Der må ikke oprettes mere end 1 felt til adgangskode";

        if($emailCount == 0) $warningList[] = "Der er ikke oprettet et felt til e-mail";
        if($emailCount > 1) $warningList[] = "Der må ikke oprettes mere end 1 felt til e-mail";

        if($nameCount == 0) $warningList[] = "Der er ikke oprettet et felt til navn";
        if($nameCount > 1) $warningList[] = "Der må ikke oprettes mere end 1 felt til navn (lav ikke separate for- og efternavne felter)";

        $response = "";
        if(count($warningList) > 0) {
            $response = "<b>Advarsler</b><br><ul><li>".implode("</li><li>",$warningList)."</li></ul>";
        }

        response::success(json_encode(array()),utf8_decode($response));
    }

    static public function getPresentProperties() {
        $properties = ShopPresent::getPresentProperties($_POST['id']);
        $result = Array();
        $result['properties'] = $properties;
        response::success(json_encode($result));
    }

    static public function setPresentProperties() {
        ShopPresent::setPresentProperties($_POST['id'],$_POST['data']);
        $result = Array();
        response::success(json_encode($result));
    }

   static public function setShopPresentIndexes() {

        ShopPresent::setShopPresentIndexes($_POST['data']);
        $dummy = Array();
        response::success(json_encode($dummy));
   }

   // Åben shop så der kan udleveres gaver
   static public function   openForRegistration() {
        $shop = Shop::find($_POST['shop_id']);
        $shop->open_for_registration = 1;
        $shop->save();
        $dummy = Array();
        response::success(json_encode($dummy));
   }
   //kundepanel_email_regel
      // Luk shop så der kan udleveres gaver
   static public function setKundepanelEmailRegel() {
        $shop = Shop::find($_POST['shop_id']);
        $shop->kundepanel_email_regel = $_POST["kundepanel_email_regel"];
        $shop->save();
        $dummy = Array();
        response::success(json_encode($dummy));
   }

   // check om der kan udleveres gaver
   static public function getKundepanelEmailRegel() {
        $shop = Shop::find($_POST['shop_id']);
        $dummy = Array();
        $dummy['kundepanel_email_regel'] = $shop->kundepanel_email_regel;
        response::success(json_encode($dummy));
   }

   static public function setRegistrationOption() {
        $shop = Shop::find($_POST['shop_id']);
        $shop->registration_option = $_POST["registration_option"];
        $shop->save();
        $dummy = Array();
        response::success(json_encode($dummy));
   }

   // check om der kan udleveres gaver
   static public function getRegistrationOption() {
        $shop = Shop::find($_POST['shop_id']);
        $dummy = Array();
        $dummy['registration_option'] = $shop->registration_option;
        response::success(json_encode($dummy));
   }

   // Luk shop så der kan udleveres gaver
   static public function   closeForRegistration() {
        $shop = Shop::find($_POST['shop_id']);
        $shop->open_for_registration = 0;
        $shop->save();
        $dummy = Array();
        response::success(json_encode($dummy));
   }

    static public function setQrMenuSettings() {
        $shop = Shop::find($_POST['shop_id']);
        $shop->	qr_menu_settings = $_POST["qrMenuSettings"];
        $shop->save();
        $dummy = Array();
        response::success(json_encode($dummy));
   }





   // check om der kan udleveres gaver
   static public function kundepanelSettings() {
        $shop = Shop::find($_POST['shop_id']);
        //$shop->save();
       // $dummy = Array();
       // $dummy['open_for_registration'] = $shop->open_for_registration;
        response::success(json_encode($shop));
   }

   // Testing Functions
   static public function testLogins() {
       $dummy=[];
       $ok = 0;
       $fail = 0;
       $fails = [];

       $shop = Shop::find($_POST['shop_id']);
       $shopusers = ShopUser::find('all',array(
         'conditions' => array('shop_id = ?',$_POST['shop_id']),

       ));
       foreach($shopusers as $shopuser) {
        try {
           $token = ShopUser::Login($shop->id,$shopuser->username,$shopuser->password);
           $shopuser2 = ShopUser::getByToken($token);
           $ok +=1;
          } catch(Exception $ex)  {
            $fail +=1;
            $fails[] = $shopuser->username;
          };
       }
       $dummy['login_ok'] = $ok;
       $dummy['login_fail'] = $fail;
       $dummy['fails'] = $fails;

       response::success(json_encode($dummy));
    }

   static public function testGiftSelections() {


       $dummy=[];
       $ok = 0;
       $fail = 0;
       $fails = [];
       $shop = Shop::find($_POST['shop_id']);
       $shopusers = ShopUser::find('all',array(
         'conditions' => array('shop_id = ? AND is_demo = ?',$_POST['shop_id'],1),
       ));
       if(count($shopusers)==0)   {
         throw new exception('Demobruger ikke fundet');
       }

       foreach($shop->presents as $shoppresent){
           $present = Present::find($shoppresent->present_id);
           try {
               if($present->variant_list =="[]") {
                 ShopController::testCreateOrder($shop,$shopusers[0],$present,$shop->attributes_,"","");
                 $ok +=1;
               }  else {
                    $variantlist = json_decode($present->variant_list);
                      foreach($variantlist as $variant) {
                           foreach($variant->feltData as $var)
                           {
                              if(isset($var->variant)) {
                                 $variant = $var->variant;
                              }
                              if(isset($var->variantNr)) {
                                 $variantno = $var->variantNr;
                              }
                           }
                       // $dummy[]= $present->id.' - '.$variant.' - '.$variantno;
                        ShopController::testCreateOrder($shop,$shopusers[0],$present,$shop->attributes_,$variant,$variantno);
                        $ok +=1;
                   }
               }

           }   catch(Exception $ex)  {
            $fail +=1;
            $fails[] = $ex->getMessage();

          };
       }

     //  throw new exception('Demobruger ikke fundet');
       $dummy['present_ok'] = $ok;
       $dummy['present_fail'] = $fail;
       $dummy['fails'] = $fails;
       response::success(json_encode($dummy));
    }

    static public function testCreateOrder($shop,$demouser,$present,$attributes,$variant,$variantno) {

        // 1. Understører ikke variant valg
        // 2. understøtter vi liste valg ?? hmm det gør vi vel ??

        $data['shopId'] = $shop->id;
        $data['userId'] = $demouser->id;
        $data['presentsId'] = $present->id;
        $data['model'] = $variant;
        $data['modelData'] = $variantno;
        $attributes = [];
        $userattributes = UserAttribute::all(array('shopuser_id' => $demouser->id));
        foreach($userattributes  as $userattribute)
        {
            $obj = new stdClass;
            $obj->feltKey = $userattribute->attribute_id;
            $obj->feltVal = $userattribute->attribute_value;
            if(empty($obj->feltVal))
              $obj->feltVal ='dummy';

            $attributes[] = $obj;
        }
        $data['_attributes'] =json_encode((array)$attributes);
        $order = Order::createOrder($data);

    }

    static function createTestMail() {
       $shop = Shop::find($_POST['shop_id']);
       $mailqueue = new MailQueue();
       $mailqueue->sender_name  ="Gavefabrikken";
       $mailqueue->recipent_email = $_POST['recipent_email'];
 	   $mailqueue->subject ="Testmail";
       $mailqueue->body = 'Dette en en testmail fra Gavefabrikken';
       $mailqueue->mailserver_id = $shop->mailserver_id;
       $dummy = [];
       response::success(json_encode($dummy));
    }

    public function getShopToExpire() {
      $shops  = Shop::find('all',array('conditions' => array('shop_id'=>$shop_id)));
    }

    public function shopStorageMonitoringSchedule($shopId="260") {
        include("model/storageMonitoringSchedule.class.php");
        $shopData = $this->getPresentStatsShop("function",$shopId);
        $smc = new StorageMonitoringSchedule;
        $smc->setShopData($shopData);
        $smc->checkForExceededPresents();
        $dummy = [];
        response::success(json_encode($dummy));
    }

    public function resendReceipts() {
        $i = 0;
        $shop = Shop::find($_POST['shop_id']);
        if($shop->is_gift_certificate==1)
          throw new exception('Denne funktion er kun gyldig for valgshops');
        $shoporders = Order::find('all',array('conditions' =>array('shop_id' => $_POST['shop_id'])) );
        foreach($shoporders as $shoporder) {
            $mail = MailQueue::find('all',array('conditions' =>array('order_id' => $shoporder->id) ));
            if(count($mail) > 0 ) {
              $mail = MailQueue::find($mail[0]->id);
              $mail->sent = 0;
              $mail->error = 0;
              $mail->save();
              $i++;
           } else {
           }
        }
       $result = [$i];
       response::success(json_encode($result));
    }

    public function getPresentStatsShopNew($responseData="web",$shopId="") {
       $responseData == "web" ? $shop_id = $_POST['shop_id'] : $shop_id = $shopId;
         $idNotInShop = [];
         $mapping = [];
         $problemGift = [];
         $giftFromShop_present_list = [];
         $rapportEmailData = "";
         $rapportEmail = ShopPresent::find_by_sql("select rapport_email from shop where id = '".$shop_id."'");
         if(sizeofgf($rapportEmail) > 0){
           $rapportEmailData =  $rapportEmail[0]->attributes["rapport_email"];
         }
         $giftFromShop_present =  ShopPresent::find_by_sql("select present_id, active, is_deleted from shop_present where shop_id = '".$shop_id."' and deleted = 0");
         for($i=0;sizeofgf($giftFromShop_present) > $i;$i++){
           $giftFromShop_present_list[$i]["present_id"] = $giftFromShop_present[$i]->attributes["present_id"];
           $giftFromShop_present_list[$i]["is_deleted"] = $giftFromShop_present[$i]->attributes["is_deleted"];
           $giftFromShop_present_list[$i]["active"] = $giftFromShop_present[$i]->attributes["active"];
         }
         $giftFromOrder =  ShopPresent::find_by_sql("SELECT DISTINCT `present_id` FROM `order` WHERE `shop_id` = '".$shop_id."'" );
            foreach ( $giftFromOrder as $orderId ){
            $found = false;
            foreach ($giftFromShop_present_list as $fromShopId["present_id"]){
                if($orderId->present_id == $fromShopId*1){
                   // echo  $orderId->present_id."--";
                    $found = true;
                }
            }

            if($found == false){
                $unikId = ShopPresent::find_by_sql("SELECT id FROM `present` WHERE `copy_of` = ". $orderId->present_id ." and shop_id = ".$shop_id );
                                //$antal = ShopPresent::find_by_sql("SELECT count(id) as antal  FROM `order` WHERE ".$shop_id." = 282 AND `present_id` = ".$orderId->present_id  );
                if(sizeofgf($unikId) > 0){
                    $mapping[$unikId[0]->attributes["id"]] = $orderId->present_id;
                } else {
                    $problemGift[] = $orderId->present_id;
                }


            }

         }
        $result = array();
        $shoppresents  = ShopPresent::find('all',array('conditions' => array('shop_id'=>$shop_id)));



    }

    public function getPresentStatsShop($responseData="web",$shopId="") {

        $responseData == "web" ? $shop_id = $_POST['shop_id'] : $shop_id = $shopId;


         $idNotInShop = [];
         $mapping = [];
         $problemGift = [];
         $giftFromShop_present_list = [];
         $rapportEmailData = "";
         $rapportEmail = ShopPresent::find_by_sql("select rapport_email from shop where id = '".$shop_id."'");
         if(sizeofgf($rapportEmail) > 0){
           $rapportEmailData =  $rapportEmail[0]->attributes["rapport_email"];
         }

         $giftFromShop_present =  ShopPresent::find_by_sql("select present_id from shop_present where shop_id = '".$shop_id."' and is_deleted = 0");
         for($i=0;sizeofgf($giftFromShop_present) > $i;$i++){
            $giftFromShop_present_list[] = $giftFromShop_present[$i]->attributes["present_id"];
         }
         $giftFromOrder =  ShopPresent::find_by_sql("SELECT DISTINCT `present_id` FROM `order` WHERE `shop_id` = '".$shop_id."'" );


         foreach ( $giftFromOrder as $orderId ){
            $found = false;
            foreach ($giftFromShop_present_list as $fromShopId){
                if($orderId->present_id == $fromShopId*1){
                   // echo  $orderId->present_id."--";
                    $found = true;
                }
            }

            if($found == false){
                $unikId = ShopPresent::find_by_sql("SELECT id FROM `present` WHERE `copy_of` = ". $orderId->present_id ." and shop_id = ".$shop_id );
                                //$antal = ShopPresent::find_by_sql("SELECT count(id) as antal  FROM `order` WHERE ".$shop_id." = 282 AND `present_id` = ".$orderId->present_id  );
                if(sizeofgf($unikId) > 0){
                    $mapping[$unikId[0]->attributes["id"]] = $orderId->present_id;
                } else {
                    $problemGift[] = $orderId->present_id;
                }


            }

         }
        $result = array();
        $shoppresents  = ShopPresent::find('all',array('conditions' => array('shop_id'=>$shop_id,'is_deleted'=>0)));


        foreach($shoppresents as $shoppresent)  {
           $present = Present::find($shoppresent->present_id);
           $presentmodels  = PresentModel::find('all',array('conditions' => array(
            'present_id'=>$shoppresent->present_id,
            'language_id' => 1, 'is_deleted' => 0
           )));

        
           if(count($presentmodels)==0) {
                      
                           /*
              $presentreservation = PresentReservation::hasReservation($shop_id,$present->id,'');
              $record = array();
              $record['present_id']        =  $shoppresent->present_id;
              $record['present_name']      =  $presentmodels->model_name;
              $record['present_model_id']  =  $presentmodels->model_id;
              $record['present_model_no']  =  $presentmodels->model_no;
              $record['present_varenr']  =    $presentmodels->model_present_no;
              $record['present_properties']  = $shoppresent->properties;
              $record['present_properties_id'] = $shoppresent->id;
              $newPresentId =  $shoppresent->present_id;
              
              foreach ($mapping as $key => $val){
                    if($key == $shoppresent->present_id){
                     $newPresentId = $shoppresent->present_id.",".$val;
                   }
              }

              $record['order_count']   =  Order::countPresentOnOrders($shop_id,$newPresentId,'');
              if(isset($presentreservation)) {
                  $record['do_close'] = $presentreservation->do_close;
                  $record['reservation_id'] = $presentreservation->id;
                  $record['reserved_quantity']  = $presentreservation->quantity;
                  $record['replacement_present_name'] = $presentreservation->replacement_present_name;
                  $record['replacement_present_id'] = $presentreservation->replacement_present_id;
                  $record['warning_level']  = $presentreservation->warning_level;
              }  else {
                  $record['do_close'] = "";
                  $record['replacement_present_id'] = "";
                  $record['replacement_present_name'] = "";
                  $record['reservation_id'] = '';
                  $record['reserved_quantity']  = '';
                  $record['warning_level']  = '';
              }

              $result[] = $record;
              */
           }   else {
                 foreach($presentmodels as $presentmodel)  {

                    $presentreservation = PresentReservation::hasReservation($shop_id,$present->id,$presentmodel->model_id);
                    $record = array();
                    $record['present_id']          =  $shoppresent->present_id;
                    $record['present_name']        =  $presentmodel->model_name;
                    $record['present_model_id']    =  $presentmodel->model_id;
                    $record['model_present_no']    =  $presentmodel->model_present_no ;
                    if($presentmodel->model_no == ""){
                       $record['model_present_name']  =  $presentmodel->model_name;
                    } else {
                        $record['model_present_name']  =  $presentmodel->model_name." / ".$presentmodel->model_no;
                    }


                    $newPresentId =  $shoppresent->present_id;
                    foreach ($mapping as $key => $val){
                        if($key == $shoppresent->present_id){
                            $newPresentId = $shoppresent->present_id.",".$val;
                        }
                    }



                    $record['present_is_deletet']  =  $presentmodel->is_deleted;
                    $record['present_is_active']   =   $presentmodel->active;
                    $record['present_total_is_deletet']  =  $shoppresent->is_deleted;
                    $record['present_total_is_active']   =   $shoppresent->active;


                    $record['present_properties_id'] = $shoppresent->id;
                    $record['order_count']   =  Order::countPresentOnOrders($shop_id,$newPresentId,$presentmodel->model_id);
                    if(isset($presentreservation)) {
                       $record['do_close'] = $presentreservation->do_close;
                       $record['reservation_id'] = $presentreservation->id;
                       $record['reserved_quantity']  = $presentreservation->quantity;
                       $record['replacement_present_name'] = $presentreservation->replacement_present_name;
                       $record['replacement_present_id'] = $presentreservation->replacement_present_id;
                       $record['warning_level']  = $presentreservation->warning_level;
                     } else {
                        $record['do_close'] = "";
                        $record['replacement_present_id'] = "";
                        $record['replacement_present_name'] = "";
                        $record['reservation_id'] = '';
                        $record['reserved_quantity']  = '';
                        $record['warning_level']  = '';
                     }

                    $result[] = $record;
                 }
           }
        }
        if($responseData == "web"){
      //  print_R($problemGift);
        $return = [];
        $return = array("data"=>$result,"problem"=>$problemGift,"email"=>$rapportEmailData);

        response::success(json_encode($return));
        } else {
            return $result;
        }

    }

   /**
    * LOCATION FUNCTIONALITY
    */

  public function updateEmailNotification()
  {
    $shop = Shop::updateSingle($_POST);
    response::success(make_json("shop", $shop));
  }

  public function locationeditor()
  {
      $shop = shop::readShop(isset($_POST['id']) ? $_POST['id'] : 0);
      $this->registry->template->shop = $shop;
      $this->registry->template->show("locationeditor");
  }

  public function locationattributes()
  {
			$vals = UserAttribute::find('all', array('conditions' => "shop_id = ".intval($_POST['id'])." && attribute_id = ".intval($_POST['attribute_id'])));
			$valueMap = array();
			if(count($vals) > 0) {
				foreach($vals as $val) {
					if(!in_array($val->attribute_value,$valueMap) && trimgf($val->attribute_value) != "") {
						$valueMap[] = $val->attribute_value;
					}
				}
			}
			echo json_encode(array("list" => $valueMap));
  }

  /**
   * FORDERLINGSRAPPORT
   */

   public function fordelingreport()
   {
                            ini_set('memory_limit','2048M');
      $rapport = new shopForedelingRapport();
      $type = isset($_GET["type"]) ? $_GET["type"] : "";
      $rapport->run($_GET["shopID"],$type);
   }
   
   public function klubmails()
   {
        $shopid = intval($_GET["shopID"]);
        if($shopid <= 0) return;
        
        $shop = Shop::find($shopid);
        
        $attributeid = 0;
        if($shopid == 953) $attributeid = 5159;
        
        $list = UserAttribute::find_by_sql("SELECT attribute_value FROM `user_attribute` WHERE shop_id = ".$shopid." && shopuser_id NOT IN (SELECT id FROM shop_user where (blocked = 1 or is_demo = 1) && shop_id = ".$shopid.") && `shopuser_id` in(SELECT `shopuser_id` FROM `user_attribute` WHERE ".($attributeid > 0 ? ("attribute_id = ".intval($attributeid)." and ") : "")." `attribute_value` LIKE 'ja' and shop_id in ( ".$shopid." ) ) and `is_email` = 1");
   
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="'.$shop->name.'-gaveklub-mails-'.date("d-m-Y").'.csv"');
   
        foreach($list as $mail)
        {
          echo $mail->attribute_value."\n";
        }
        
   
   }

}
?>