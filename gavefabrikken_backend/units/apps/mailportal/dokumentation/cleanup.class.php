<?php
// Model Vendor
// Date created  Mon, 16 Jan 2017 15:30:07 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) object_type                   varchar(50)        YES
//   (   ) object_id                     int(11)             NO
//   (   ) deleted                       int(11)             NO
//***************************************************************
class CleanUp extends ActiveRecord\Model {
    static $table_name  = "clean_up";
    static $primary_key = "id";
    static $before_save =  array('onBeforeSave');
    static $after_save =  array('onAfterSave');
    static $before_create =  array('onBeforeCreate');
    static $after_create =  array('onAfterCreate');
    static $before_update =  array('onBeforeUpdate');
    static $after_update =  array('onAfterUpdate');
    static $before_destroy =  array('onBeforeDestroy');  // virker ikke
    static $after_destroy =  array('onAfterDestroy');

    // Trigger functions
    function onBeforeSave() {}
    function onAfterSave()  {}
    function onBeforeCreate() {
      $this->validateFields();
    }
    function onAfterCreate()  {}
    function onBeforeUpdate() {
      $this->validateFields();
    }
    function onAfterUpdate()   {}
    function onBeforeDestroy() {}
    function onAfterDestroy()  {}


    function validateFields() {
     testMaxLength($this,'object_type',50);
     testMaxLength($this,'object_name',100);
     $this->object_type = trimgf($this->object_type);
     $this->object_name = trimgf($this->object_name);
    }
}
?>