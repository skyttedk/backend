<?php
class shopSettingsController Extends baseController {

  public function Index() {
//    $shops = Shop::all();
//    $this->registry->template->shops = $shops;
//    $this->registry->template->show('shoplist');
      echo "shopSettingsController";
  }
  public function read(){
              $shop = shop::readShop($_POST['id']);
      print_r($shop);

  }

}

?>