<?php
// Model ShopDescription
// Date created  Mon, 16 Jan 2017 15:29:40 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) shop_id                       int(11)             NO
//   (MUL) language_id                   int(11)             NO
//   (   ) description                   varchar(2048)       YES
//***************************************************************
class ShopDescription extends BaseModel {
	static $table_name  = "shop_description";
	static $primary_key = "id";

	static $before_create =  array('onBeforeCreate');
	static $after_create =  array('onAfterCreate');

	static $before_update =  array('onBeforeUpdate');
	static $after_update =  array('onAfterUpdate');

	static $before_destroy =  array('onBeforeDestroy');
	static $after_destroy =  array('onAfterDestroy');

	// Trigger functions
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
      	testRequired($this,'shop_id');
        testRequired($this,'language_id');

        //Check parent records exists here
        Shop::find($this->shop_id);
        Language::find($this->language_id);

        //Check parent records exists here
//		testMaxLength($this,'description',4000);

        $this->description = trimgf($this->description);
    }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

	static public function createShopDescription($data) {
		$shopdescription = new ShopDescription($data);
        $shopdescription->save();
        return($shopdescription);
	}

	static public function readShopDescription($id) {
		$shopdescription = ShopDescription::find($id);
        return($shopdescription);
	}

	static public function updateShopDescription($data) {
		$shopdescription = ShopDescription::find($data['id']);
		$shopdescription->update_attributes($data);
        $shopdescription->save();
        return($shopdescription);
	}

	static public function deleteShopDescription($id,$realDelete=true) {

	    if($realDelete) {
            $shopdescription = ShopDescription::find($id);
    		$shopdescription->delete();
          } else {  //Soft delete
            $shopdescription->deleted = 1;
		    $shopdescription->save();
          }
    }




}
?>