<?php
// Model UserAttribute
// Date created  Mon, 23 Jan 2017 09:56:42 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) shopuser_id                   int(11)             NO
//   (   ) attribute_id                  int(11)             NO
//   (   ) attribute_value               varchar(250)        YES
//   (   ) shop_id                       int(11)             NO
//   (   ) company_id                    int(11)             NO
//   (   ) is_username                   tinyint(1)          YES
//   (   ) is_password                   tinyint(1)          YES
//   (   ) is_email                      tinyint(1)          YES
//   (   ) is_name                       tinyint(1)          YES
//***************************************************************
class UserAttribute extends BaseModel {
  static $table_name = "user_attribute";
  static $primary_key = "id";
  //Relations
  static $belongs_to = array(array('user', 'class_name' => 'ShopUser'));
  static $before_save = array('onBeforeSave');
  static $after_save = array('onAfterSave');
  static $before_create = array('onBeforeCreate');
  static $after_create = array('onAfterCreate');
  static $before_update = array('onBeforeUpdate');
  static $after_update = array('onAfterUpdate');
  static $before_destroy = array('onBeforeDestroy'); // virker ikke
  static $after_destroy = array('onAfterDestroy');

  // Trigger functions
  function onBeforeSave() {
  }

  function onAfterSave() {
  }

  function onBeforeCreate() {
    $this->validateFields();
  }

  function onAfterCreate() {
  }

  function onBeforeUpdate() {
    $this->validateFields();
  }

  function onAfterUpdate() {
  }

  function onBeforeDestroy() {
  }

  function onAfterDestroy() {
  }

  function validateFields() {
    testRequired($this, 'shopuser_id');
    testRequired($this, 'attribute_id');
    testRequired($this, 'shop_id');
    testRequired($this, 'company_id');
    //Check parent records exists here
    testMaxLength($this, 'attribute_value', 250);
    $this->attribute_value = trimgf($this->attribute_value);

   // if($this->is_username==1)
   //  $this->attribute_value = strtolower(trimgf($this->attribute_value));

   //if($this->is_password==1)
   //   $this->attribute_value = strtolower(trimgf($this->attribute_value));


  }
  //---------------------------------------------------------------------------------------
  // Static CRUD Methods
  //---------------------------------------------------------------------------------------

  static public function createUserAttribute($data) {
    $userattribute = new UserAttribute($data);
    $userattribute->save();
    return ($userattribute);
  }

  static public function readUserAttribute($id) {
    $userattribute = UserAttribute::find($id);
    return ($userattribute);
  }

  static public function updateUserAttribute($data) {
    $userattribute = UserAttribute::find($data['id']);
    $userattribute->update_attributes($data);
    $userattribute->save();
    return ($userattribute);
  }

  static public function deleteUserAttribute($id, $realDelete = true) {
    if ($realDelete) {
      $userattribute = UserAttribute::find($id);
      $userattribute->delete();
    }
    else { //Soft delete
      $userattribute->deleted = 1;
      $userattribute->save();
    }
  }

}
?>