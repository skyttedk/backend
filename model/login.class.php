<?php
class Login
{


public function loginShopUser() {
  $token = ShopUser::Login($_POST['shop_id'],$_POST['username'], $_POST['password']);
  response::success(make_json("token", $token));
}

static public function testToken($type,$token) {

  //type = shop,backend,customer
  if($type == 'shop') {
	  $shopUser = ShopUser::getByToken($token);
	return($shopUser);
  } elseif ($type == 'backend')  {
 	    $systemUser =SystemUser::getByToken($token);
	    return($systemUser);
  }
}



}
?>