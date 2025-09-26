<?php
// Model ExpireDate
// Date created  Mon, 16 Jan 2017 15:26:59 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) expire_date                   date                NO
//   (   ) week_no                       int(11)             YES
//   (   ) display_date                  varchar(10)         NO
//   (   ) blocked                       tinyint(1)          YES
//   (   ) is_delivery                   tinyint(1)          YES
//   (   ) item_name_format              varchar(200)        No
//   (   ) item_no_format                varchar(200)        No
//***************************************************************
class ExpireDate extends ActiveRecord\Model {
    static $table_name  = "expire_date";
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

    function onAfterUpdate()  {}
    function onBeforeDestroy() {}
    function onAfterDestroy()  {

    }
    function validateFields() {
      	testRequired($this,'expire_date');
        testRequired($this,'display_date');

        testMaxLength($this,'display_date',10);
        $this->display_date = trimgf($this->display_date);

    }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
   static public function createExpireDate($data) {
        $expiredate = new ExpireDate($data);
        $expiredate->save();
        return($expiredate);
    }
    static public function readExpireDate($id) {
        $expiredate = ExpireDate::find($id);
        return($expiredate);
    }

    static public function updateExpireDate($data) {
        $expiredate = ExpireDate::find($data['id']);
        $expiredate->update_attributes($data);
        $expiredate->save();
        return($expiredate);
    }

    static public function deleteExpireDate($id,$realDelete=true) {

        if($realDelete) {
            $expiredate = ExpireDate::find($id);
    		$expiredate->delete();
          } else {  //Soft delete
            $expiredate->deleted = 1;
            $expiredate->save();
          }
    }


   // *** Utility functions *** taken from bitworks.php

   static public function  getWeekNo($date) {
     //$expireDate = $date->format('Y-m-d');
   }

   public function toString() {
     return($this->display_date);
   }

   static public function  getByExpireDate($date) {
     $expireDate = expireDate::find_by_expire_date($date);
     if(!$expireDate) {
       $expireDate = expireDate::find_by_display_date($date);
       return($expireDate);
      } else {
     return($expireDate);
     }
   }

}


