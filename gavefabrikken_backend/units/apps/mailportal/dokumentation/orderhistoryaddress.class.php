<?php
// Model OrderHistoryAddress
// Date created  Mon, 16 Jan 2017 15:29:10 +0100
// Created by Bitworks
//***************************************************************
//   (   ) id                            int(11)             NO
//   (   ) ship_to_name                  varchar(100)        YES
//   (   ) ship_to_address               varchar(100)        YES
//   (   ) ship_to_address_2             varchar(100)        YES
//   (   ) ship_to_postal_code           varchar(10)         YES
//   (   ) ship_to_city                  varchar(100)        YES
//   (   ) ship_to_country               varchar(100)        YES
//***************************************************************

class OrderHistoryAddress extends ActiveRecord\Model {
    static $table_name  = "order_history_address";
    static $primary_key = "";


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
      	testRequired($this,'id');

        testMaxLength($this,'ship_to_name',100);
testMaxLength($this,'ship_to_address',100);
testMaxLength($this,'ship_to_address_2',100);
testMaxLength($this,'ship_to_postal_code',10);
testMaxLength($this,'ship_to_city',100);
testMaxLength($this,'ship_to_country',100);

        $this->ship_to_name = trimgf($this->ship_to_name);
$this->ship_to_address = trimgf($this->ship_to_address);
$this->ship_to_address_2 = trimgf($this->ship_to_address_2);
$this->ship_to_postal_code = trimgf($this->ship_to_postal_code);
$this->ship_to_city = trimgf($this->ship_to_city);
$this->ship_to_country = trimgf($this->ship_to_country);

    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createOrderHistoryAddress($data) {
        $orderhistoryaddress = new OrderHistoryAddress($data);
        $orderhistoryaddress->save();
        return($orderhistoryaddress);
    }

    static public function readOrderHistoryAddress($id) {
        $orderhistoryaddress = OrderHistoryAddress::find($id);
        return($orderhistoryaddress);
    }

    static public function updateOrderHistoryAddress($data) {
        $orderhistoryaddress = OrderHistoryAddress::find($data['id']);
        $orderhistoryaddress->update_attributes($data);
        $orderhistoryaddress->save();
        return($orderhistoryaddress);
    }

    static public function deleteOrderHistoryAddress($id,$realDelete=true) {

        if($realDelete) {
            $orderhistoryaddress = OrderHistoryAddress::find($id);
    		$orderhistoryaddress->delete();
          } else {  //Soft delete
            $orderhistoryaddress->deleted = 1;
            $orderhistoryaddress->save();
          }
    }







}
?>

