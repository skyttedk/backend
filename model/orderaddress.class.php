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
//***************************************************************
class OrderAddress extends ActiveRecord\Model {
    static $table_name  = "order_address";
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
      //Delete records in child tables here
      //Example:
      //<child_table>::table()->delete(array('parent_table_id' => id));

    }
    function validateFields() {

        //Check parent records exists here
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

    static public function createOrderAddress($data) {
        $orderaddress = new OrderAddress($data);
        $orderaddress->save();
        return($orderaddress);
    }

    static public function readOrderAddress($id) {
        $orderaddress = OrderAddress::find($id);
        return($orderaddress);
    }

    static public function updateOrderAddress($data) {
        $orderaddress = OrderAddress::find($data['id']);
        $orderaddress->update_attributes($data);
        $orderaddress->save();
        return($orderaddress);
    }

    static public function deleteOrderAddress($id,$realDelete=true) {

        if($realDelete) {
            $orderaddress = OrderAddress::find($id);
    		$orderaddress->delete();
          } else {  //Soft delete
            $orderaddress->deleted = 1;
            $orderaddress->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------






}
?>

