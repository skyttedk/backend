<?php
// Model OrderPresentEntry
// Date created  Mon, 16 Jan 2017 15:29:12 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) order_id                      int(11)             NO
//   (   ) order_no                      varchar(20)         NO
//   (   ) order_timestamp               datetime            NO
//   (   ) shop_id                       int(11)             NO
//   (   ) shop_is_gift_certificate      tinyint(4)          YES
//   (   ) shop_is_company               tinyint(4)          YES
//   (   ) company_id                    int(11)             NO
//   (   ) company_name                  varchar(100)        NO
//   (   ) company_cvr                   varchar(15)         NO
//   (   ) company_pick_group            varchar(15)         YES
//   (   ) shopuser_id                   int(11)             NO
//   (   ) user_username                 varchar(250)        NO
//   (   ) user_email                    varchar(250)        NO
//   (   ) user_name                     varchar(250)        NO
//   (   ) present_id                    int(11)             NO
//   (   ) present_no                    varchar(250)        NO
//   (   ) present_name                  varchar(100)        NO
//   (   ) present_internal_name         varchar(100)        NO
//   (   ) present_vendor                varchar(100)        YES
//   (   ) present_copy_of               int(11)             YES
//   (   ) present_shop_id               int(11)             YES
//   (   ) present_model_id              int(11)             YES
//   (   ) present_model_name            varchar(250)        YES
//   (   ) gift_certificate_no           varchar(20)         YES
//   (   ) gift_certificate_value        int(11)             YES
//   (   ) gift_certificate_week_no      int(11)             YES
//   (   ) gift_certificate_start_date   date                YES
//   (   ) gift_certificate_end_date     date                YES
//***************************************************************

class OrderPresentEntry extends ActiveRecord\Model {
    static $table_name  = "order_present_entry";
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
      testRequired($this,'order_no');
      testRequired($this,'order_timestamp');
      testRequired($this,'shop_id');
      testRequired($this,'company_id');
      testRequired($this,'company_name');
      //testRequired($this,'company_cvr');
      testRequired($this,'shopuser_id');
      testRequired($this,'user_username');
      testRequired($this,'user_email');
      testRequired($this,'present_id');
      testRequired($this,'present_no');
      testRequired($this,'present_name');
      testRequired($this,'present_internal_name');


      testMaxLength($this,'order_no',20);
      testMaxLength($this,'company_name',100);
      testMaxLength($this,'company_cvr',15);
      testMaxLength($this,'company_pick_group',15);
      testMaxLength($this,'user_username',250);
      testMaxLength($this,'user_email',250);
      testMaxLength($this,'user_name',250);
      testMaxLength($this,'present_no',250);
      testMaxLength($this,'present_name',100);
      testMaxLength($this,'present_internal_name',100);
      testMaxLength($this,'present_vendor',100);
      testMaxLength($this,'present_model_name',250);
      testMaxLength($this,'gift_certificate_no',20);

      $this->order_no = trimgf($this->order_no);
      $this->company_name = trimgf($this->company_name);
      $this->company_cvr = trimgf($this->company_cvr);
      $this->company_pick_group = trimgf($this->company_pick_group);
      $this->user_username = trimgf($this->user_username);
      $this->user_email = trimgf($this->user_email);
      $this->user_name = trimgf($this->user_name);
      $this->present_no = trimgf($this->present_no);
      $this->present_name = trimgf($this->present_name);
      $this->present_internal_name = trimgf($this->present_internal_name);
      $this->present_vendor = trimgf($this->present_vendor);
      $this->present_model_name = trimgf($this->present_model_name);
      $this->gift_certificate_no = trimgf($this->gift_certificate_no);

    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createOrderPresentEntry($data) {
        $orderpresententry = new OrderPresentEntry($data);
        $orderpresententry->save();
        return($orderpresententry);
    }

    static public function readOrderPresentEntry($id) {
        $orderpresententry = OrderPresentEntry::find($id);
        return($orderpresententry);
    }

    static public function updateOrderPresentEntry($data) {
        $orderpresententry = OrderPresentEntry::find($data['id']);
        $orderpresententry->update_attributes($data);
        $orderpresententry->save();
        return($orderpresententry);
    }

    static public function deleteOrderPresentEntry($id,$realDelete=true) {

        if($realDelete) {
            $orderpresententry = OrderPresentEntry::find($id);
    		$orderpresententry->delete();
          } else {  //Soft delete
            $orderpresententry->deleted = 1;
            $orderpresententry->save();
          }
    }

}
?>