<?php
// Model OrderAttribute
// Date created  Mon, 16 Jan 2017 15:29:06 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) order_id                      int(11)             NO
//   (   ) shop_id                       int(11)             NO
//   (   ) shopuser_id                   int(11)             NO
//   (   ) company_id                    int(11)             NO
//   (   ) attribute_id                  int(11)             NO
//   (   ) attribute_name                varchar(50)         NO
//   (   ) attribute_value               varchar(250)        YES
//   (   ) attribute_index               int(11)             YES
//   (   ) is_username                   tinyint(4)          YES
//   (   ) is_password                   tinyint(4)          YES
//   (   ) is_name                       tinyint(4)          YES
//   (   ) is_email                      tinyint(4)          YES
//   (   ) list_selection                varchar(250)        YES
//***************************************************************
class OrderAttribute extends BaseModel {
    static $table_name  = "order_attribute";
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
      	testRequired($this,'order_id');
		testRequired($this,'shop_id');
		testRequired($this,'shopuser_id');
		testRequired($this,'company_id');
		testRequired($this,'attribute_id');
		testRequired($this,'attribute_name');

        //Check parent records exists here
		testMaxLength($this,'attribute_name',50);
		testMaxLength($this,'attribute_value',250);
		testMaxLength($this,'list_selection',250);

        $this->attribute_name = trimgf($this->attribute_name);
		$this->attribute_value = trimgf($this->attribute_value);
		$this->list_selection = trimgf($this->list_selection);

    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createOrderAttribute($data) {
        $orderattribute = new OrderAttribute($data);
        $orderattribute->save();
        return($orderattribute);
    }

    static public function readOrderAttribute($id) {
        $orderattribute = OrderAttribute::find($id);
        return($orderattribute);
    }

    static public function updateOrderAttribute($data) {
        $orderattribute = OrderAttribute::find($data['id']);
        $orderattribute->update_attributes($data);
        $orderattribute->save();
        return($orderattribute);
    }

    static public function deleteOrderAttribute($id,$realDelete=true) {

        if($realDelete) {
            $orderattribute = OrderAttribute::find($id);
    		$orderattribute->delete();
          } else {  //Soft delete
            $orderattribute->deleted = 1;
            $orderattribute->save();
          }
    }




}
?>