<?php
 if (session_status() == PHP_SESSION_NONE) session_start();
// Controller SystemLog
// Date created  Wed, 13 Apr 2016 20:48:50 +0200
// Created by Bitworks
class LoginController Extends baseController {

  public function Index() {

      die("Vi er ved at opdatere serveren. Vi forventer, at være færdige senest kl. 9.00");
    $this->registry->template->show('login2f_view');
  }

  public function login2f() {
      $this->registry->template->show('login2f_view');
  }

  public function logout()
  {
      unset($_SESSION["syslogin"]);
    
        unset($_SESSION["systemuser_login"]);
        unset($_SESSION["systemuser_token"]);
      $dummy['link']  = '';
      response::success(json_encode($dummy));
  }
  public function apiLogin(){

      $shopuser = ShopUser::find_by_username_and_shop_id(strtolower($_POST['username']), $_POST['shop_id']);
      $pw = $shopuser->password;
      $token = ShopUser::Login($_POST['shop_id'],$_POST['username'],$pw);
      $shopUser = ShopUser::getByToken($token);
      $options = array('except' => array('password'));
      response::success(make_json("result", $shopUser,$options));


  }
  public function apiLoginError(){

  }
  public function verify(){

       $systemuser = SystemUser::Login($_POST['username'],$_POST['pw']);
       if($systemuser->id != false){
            $_SESSION["syslogin"] = $systemuser->id;
            //$result = array("status"=>1,"data"=>$systemuser->id);
            //echo json_encode($result);
            echo('1');
       }
  }

  public function loginSystemUser() {
      $systemuser = SystemUser::Login($_POST['username'],$_POST['password']);
      $_SESSION["syslogin"] = $systemuser->id;
      $options = array('include' => array('permissions'));
      response::success(make_json("result", $systemuser,$options));
  }
  
  public function loginSystemUser2f() {

      $loginModel = new \GFBiz\Login\SystemUserLogin();
      $loginModel->performLogin();

  }
  
    public function loginShopUserHor() {

   $radom = generateRandomString();

   $attributes_ = '[{"id":"7059","value":""},{"id":"7057","value":"'.$radom.'"},{"id":"7058","value":"'.$radom.'"},{"id":"7060","value":""},{"id":"7070","value":""}]';
   $data =  '{"userId":null,"shopId":"1229","companyId":27529}';

   $form["attributes_"] = $attributes_;
   $form["data"] = $data;
   $shopUser = Shop::addShopUser2($form);
   $options = array('include' => array('attributes_'));
   response::success(make_json("shopuser",$shopUser,$options));


}


public function loginShopMultiPresent() {

      $userID = trimgf($_POST['userID']);
      $shopID = trimgf($_POST['shopID']);

      //xylem
      $shop2ID = "3041";
      $userAttUsername = UserAttribute::find('all', array('conditions' => array('shopuser_id=? and shop_id =? and is_username=? ',$userID, $shopID,1)));
      $userAttPassword = UserAttribute::find('all', array('conditions' => array('shopuser_id=? and shop_id =? and is_password=? ',$userID, $shopID,1)));


      $username = $userAttUsername[0]->attribute_value;
      $password = $userAttPassword[0]->attribute_value;

      $token = ShopUser::Login($shop2ID,$username,$password);
      $shopUser = ShopUser::getByToken($token);
      $options = array('except' => array('password'));
      response::success(make_json("result", $shopUser,$options));
   }


 public function loginShopUserxylem() {

      $username = trimgf($_POST['username']);
      $password = trimgf($_POST['password']);
      $password2 = trimgf($_POST['password2']);



      $userAtt = UserAttribute::find('all', array('conditions' => array('attribute_value=? and attribute_id =? and shop_id =?  ',$password2, 17796, 2939)));

      if(sizeofgf($userAtt) == 0){
        echo '{"status":"0","data":{},"message":"Ugyldig login.(0)"}';
        return;
      }
      $shopuser_id = $this->rxylemFindShopUserID($userAtt,$username);

      $userAttUsername = UserAttribute::find('all', array('conditions' => array('shopuser_id=? and is_username =? and shop_id =?', $shopuser_id, 1, 2939)));
      if(sizeofgf($userAttUsername) == 0){
        echo '{"status":"0","data":{},"message":"Ugyldig login.(0)"}';
        return;
      }

      if($userAttUsername[0]->attributes["attribute_value"] != $username){
        echo '{"status":"0","data":{},"message":"Ugyldig login.(0)"}';
        return;
      }

      $token = ShopUser::Login($_POST['shop_id'],$username,$password);
      $shopUser = ShopUser::getByToken($token);
      $options = array('except' => array('password'));
      response::success(make_json("result", $shopUser,$options));
  }
  private function rxylemFindShopUserID($list,$username)
  {
       // print_R($list);
        $returnVal = false;
        foreach($list as $ele){
         $shopUser =  $ele->attributes["shopuser_id"];
         $rs = UserAttribute::find('all', array('conditions' => array('shopuser_id=? and attribute_value=? and is_username =? and shop_id =?  ',$shopUser,$username, 1, 2939)));
         if(sizeofgf($rs) > 0){
            $returnVal = $shopUser; //$rs[0]->attributes["attribute_value"];
         }
       }
       return $returnVal;
  }

  public function loginShopUser() {


      $shopsWithSingleUserLogin = array(3500);

      // Login with a single user to create a new random shopuser
      if(in_array(intval($_POST['shop_id']),$shopsWithSingleUserLogin) && $_POST['username'] != "admin") {

          // Get user
          $token = ShopUser::Login($_POST['shop_id'],$_POST['username'],$_POST['password']);
          $shopUser = ShopUser::getByToken($token);

          // Create new shopuser
          $random = "su".generateRandomString(20);

          $attributeList = ShopAttribute::find_by_sql("select * from shop_attribute where shop_id = ".intval($_POST['shop_id']));
          $attributes = array();
          foreach($attributeList as $attribute) {

              $newAttr = array(
                  "id" => $attribute->id,
                  "value" => ""
              );

              if($attribute->is_username == 1 || $attribute->is_password == 1) {
                  $newAttr["value"] = $random;
              }

              $attributes[] = $newAttr;
          }

          $form["attributes_"] = json_encode($attributes);
          $form["data"] = '{"userId":null,"shopId":"'.$shopUser->shop_id.'","companyId":'.$shopUser->company_id.'}';
          $shopUserNew = Shop::addShopUser2($form);

          // Log in and return new shopuser
          $token = ShopUser::Login($_POST['shop_id'],$random,$random);
          $shopUserNewLogin = ShopUser::getByToken($token);
          $options = array('except' => array('password'));
          response::success(make_json("result", $shopUserNewLogin,$options));

      }
      // Normal login
      else {
          $token = ShopUser::Login($_POST['shop_id'],$_POST['username'],$_POST['password']);
          $shopUser = ShopUser::getByToken($token);
          $options = array('except' => array('password'));
          response::success(make_json("result", $shopUser,$options));
      }

/*      // 2021 login code, remove later
      // Switch username and password for ff skagen valgshop, 2020 only, remove later
      if($_POST["shop_id"] == 1973 && $_POST["username"] != "admin") {
          $tmp = $_POST["username"];
          $_POST["username"] = $_POST["password"];
          $_POST["password"] = $tmp;
      }

    if($_POST['shop_id'] == "2742" ){
          if($_POST['username'] == "admin" ){
              $token = ShopUser::Login($_POST['shop_id'],$_POST['username'],$_POST['password']);
              $shopUser = ShopUser::getByToken($token);
              $options = array('except' => array('password'));
              response::success(make_json("result", $shopUser,$options));
          } else if($_POST["username"] == "jul2021" || $_POST["password"] == "jul2021") {

              $radom = generateRandomString();
              $attributes_ = '[{"id":"17457","value":""},{"id":"15386","value":"'.$radom.'"},{"id":"15387","value":"'.$radom.'"},{"id":"15389","value":""},{"id":"15390","value":""},{"id":"15388","value":""}]';
              $data =  '{"userId":null,"shopId":"2742","companyId":38483}';

              $form["attributes_"] = $attributes_;
              $form["data"] = $data;
              $shopUser = Shop::addShopUser2($form);
              //  system::connection()->commit();
              $token = ShopUser::Login($_POST['shop_id'],$radom,$radom);
              $shopUser = ShopUser::getByToken($token);
              $options = array('except' => array('password'));
              response::success(make_json("result", $shopUser,$options));

          }
          else {
              throw new Exception('Ugyldig login.');
          }

      } else if($_POST['shop_id'] == "2971" ){
          if($_POST['username'] == "admin" ){
              $token = ShopUser::Login($_POST['shop_id'],$_POST['username'],$_POST['password']);
              $shopUser = ShopUser::getByToken($token);
              $options = array('except' => array('password'));
              response::success(make_json("result", $shopUser,$options));
          } else if(strtolower($_POST["username"]) == "lw2021" || strtolower($_POST["password"]) == "lw2021") {

              $radom = generateRandomString();
              $attributes_ = '[{"id":"17081","value":""},{"id":"17078","value":"'.$radom.'"},{"id":"17079","value":"'.$radom.'"},{"id":"17082","value":""},{"id":"17708","value":""},{"id":"17080","value":""},{"id":"17709","value":""}]';
              $data =  '{"userId":null,"shopId":"2971","companyId":39946}';

              $form["attributes_"] = $attributes_;
              $form["data"] = $data;
              $shopUser = Shop::addShopUser2($form);
              //  system::connection()->commit();
              $token = ShopUser::Login($_POST['shop_id'],$radom,$radom);
              $shopUser = ShopUser::getByToken($token);
              $options = array('except' => array('password'));
              response::success(make_json("result", $shopUser,$options));

          }
          else {
              throw new Exception('Ugyldig login.');
          }

      }
      else {
    
      $token = ShopUser::Login($_POST['shop_id'],$_POST['username'],$_POST['password']);
      $shopUser = ShopUser::getByToken($token);
      $options = array('except' => array('password'));
      response::success(make_json("result", $shopUser,$options));
     }
*/

  }

  public function checkArchiveUser() {
      $result = [];
      $archiveuserid = ShopUserArchive::isArchiveUser($_POST['username'],$_POST['password']);

      if($archiveuserid > 0) {
        $shopuserarchive = ShopUserArchive::find($archiveuserid);


        $result['shop_id'] = $shopuserarchive->shop_id;
        $result['company_id'] = $shopuserarchive->company_id;

        $result['is_archive_user'] = 1;
        $validuser = ShopUserArchive::Login($_POST['username'],$_POST['password']);

        if($validuser=="") {
          $result['is_login_valid'] = 1;
        } else  {

            $result['is_login_valid'] = 0;
            $result['reason'] = $validuser;

          }
      } else {
        $result['is_archive_user'] = 0;

      }
      //throw new exception($validuser);
      response::success(json_encode($result));
  }

  public function loginShopUserByToken() {
      $token = $_POST['token'];
      $shopUser = ShopUser::getByToken($token);
      $options = array('except' => array('password'));
      response::success(make_json("result", $shopUser,$options));
  }

  public function getUserShoplink() {
       $dummy = [];
       $dummy['link']  = '';
       $shopuser = ShopUser::find_by_username_and_password(strtolower($_POST['username']), $_POST['password']);
       if($shopuser) {
         $shop = Shop::find($shopuser->shop_id);
         $dummy['link']  = $shop->link;
       }
       response::success(json_encode($dummy));
  }
  //  lag en getUserShoplink
  //  Som u dfra brugernavn, kodeord returnerer shoplink


  public function getUserId() {
      $shopuser = ShopUser::find('all', array('conditions' => array('username=? and password =? and shop_id =?', $_POST['username'], $_POST['password'], $_POST['shop_id'])));

      $order = Order::find_by_shopuser_id($shopuser[0]->id);

      $result = [];
      $result['user_id'] = $shopuser[0]->id;
      if(count($shopuser[0]->orders)>0) {
        $result['order_id']  = $shopuser[0]->orders[0]->id;
      } else {
        $result['order_id']  = '';
      }

     response::success(json_encode($result));
    }
      // returnere soft_close


}
function generateRandomString($length = 25) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>