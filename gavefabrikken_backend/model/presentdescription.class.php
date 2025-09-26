<?php
// Model PresentDescription
// Date created  Mon, 16 Jan 2017 15:29:20 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (PRI) present_id                    int(11)             NO
//   (MUL) language_id                   int(11)             NO
//   (   ) caption                       varchar(100)        YES
//   (   ) short_description             mediumtext          YES
//   (   ) long_description              mediumtext          YES
//***************************************************************
class PresentDescription extends BaseModel {
  static $table_name = "present_description";
  static $primary_key = "id";
//Relations

static $belongs_to = array(array('present'));

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
    testRequired($this, 'present_id');
    testRequired($this, 'language_id');

    //Check parent records exists here
    Present::find($this->present_id);
    Language::find($this->language_id);

    testMaxLength($this, 'caption', 100);

    $this->caption = trimgf($this->caption);
    $this->short_description = trimgf($this->short_description);
    $this->long_description = trimgf($this->long_description);
  }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
  static public function createPresentdescription($data) {
    $presentdescription = new PresentDescription($data);
    $presentdescription->save();
    return ($presentdescription);
  }
  static public function readPresentdescription($id) {
    $presentdescription = PresentDescription::find($id);
    return ($presentdescription);
  }
  static public function updatePresentdescription($data) {
    $presentdescription = PresentDescription::find($data['id']);
    $presentdescription->update_attributes($data);
    $presentdescription->save();
    return ($presentdescription);
  }
  static public function deletePresentdescription($id, $realDelete = true) {
    if ($realDelete) {
      $presentdescription = PresentDescription::find($id);
      $presentdescription->delete();
    }
    else { //Soft delete
      $presentdescription->deleted = 1;
      $presentdescription->save();
    }
  }


}
?>