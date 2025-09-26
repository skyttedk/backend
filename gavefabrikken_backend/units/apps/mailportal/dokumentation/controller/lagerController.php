<?php
 if (session_status() == PHP_SESSION_NONE) session_start();
// Controller SystemLog
// Date created  Wed, 13 Apr 2016 20:48:50 +0200
// Created by Bitworks
class lagerController Extends baseController {

  public function Index() {
      $this->registry->template->show('lager_view');
  }
 /*
  public function getAllOrders(){
      Dbsqli::getSql("select order_no,company_name,company_cvr, from order where shop_is_gift_certificate = 1   ");
  }
  */
}

?>