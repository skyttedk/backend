<?php
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class receiptController Extends baseController {
  public function Index() {

  }
  public function getStandartText()
  {
       $receipt = Receipt::find_by_sql("SELECT * FROM receipt_custom_part");
       response::success(json_encode($receipt));
  }
  public function updateStandartText()
  {
      $presentModel = PresentModel::find($_POST['id']);
      $presentModel->msg1 = $_POST["msg1"];
      $presentModel->save();
       $dummy = [];
      response::success(json_encode($dummy));
 }
 public function getStandartTextById(){

   $receiptTxt = Receipt::find_by_sql("SELECT * FROM `receipt_custom_part` where id = ( SELECT msg1 FROM `present_model` WHERE `model_id` = '".$_POST["model_id"]."' and `language_id` = 1  )");
   response::success(json_encode($receiptTxt));
 }
 public function findReceiptByNumber(){
    $s = $_POST["search"];
    $receiptList = OrderHistory::find_by_sql(  "select company_name, shop_is_company ,order_no,order_timestamp,present_model_name,user_email,user_name from order_history where shopuser_id in ( select shopuser_id from order_history where order_no = '".addslashes($s)."' ) order by id DESC" );
     response::success(json_encode($receiptList));

 }


}

?>