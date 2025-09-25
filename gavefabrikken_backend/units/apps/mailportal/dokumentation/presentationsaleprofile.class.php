<?php
// Model Vendor
// Date created  Mon, 16 Jan 2017 15:30:07 +0100
// Created by Bitworks
//***************************************************************

//***************************************************************
class PresentationSaleProfile extends BaseModel {
    static $table_name  = "presentation_sale_profile";
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

    }
}
?>