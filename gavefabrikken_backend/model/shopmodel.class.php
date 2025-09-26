<?php
// Model ShopModel
// Date created  Mon, 16 Jan 2017 15:29:43 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) shop_id                       int(11)             NO
//   (   ) present_id                    int(11)             NO
//   (   ) model_id                      int(11)             NO
//***************************************************************
class ShopModel extends BaseModel {
  static $table_name = "shop_model";
  static $primary_key = "id";
  static $before_create = array('onBeforeCreate');
  static $before_update = array('onBeforeUpdate');

  // Trigger functions
  function onBeforeCreate() {
    $this->validateFields();
  }

  function onBeforeUpdate() {
    $this->validateFields();
  }

  function validateFields() {
    testRequired($this, 'shop_id');
    testRequired($this, 'present_id');
    testRequired($this, 'model_id');
  }
  //---------------------------------------------------------------------------------------
  // Static CRUD Methods
  //---------------------------------------------------------------------------------------

  static public function createShopModel($data) {
    $shopmodel = new ShopModel($data);
    $shopmodel->save();
    return ($shopmodel);
  }

  static public function deleteShopModel($id) {
    $shopmodel = ShopModel::find($id);
    $shopmodel->delete();
  }

}
?>