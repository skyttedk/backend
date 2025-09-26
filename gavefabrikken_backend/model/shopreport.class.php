<?php
// Model ShopReport
// Date created  Mon, 16 Jan 2017 15:29:48 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) shop_id                       int(250)            NO
//   (   ) profile_data                  text                NO
//***************************************************************

class ShopReport extends ActiveRecord\Model {
	static $table_name  = "shop_report";
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
      	testRequired($this,'shop_id');
     testRequired($this,'profile_data');




    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

	static public function createShopReport($data) {
		$shopreport = new ShopReport($data);
        $shopreport->save();
        return($shopreport);
	}

	static public function readShopReport($id) {
		$shopreport = ShopReport::find($id);
        return($shopreport);
	}

	static public function updateShopReport($data) {
		$shopreport = ShopReport::find($data['id']);
		$shopreport->update_attributes($data);
        $shopreport->save();
        return($shopreport);
	}

	static public function deleteShopReport($id,$realDelete=true) {

	    if($realDelete) {
            $shopreport = ShopReport::find($id);
    		$shopreport->delete();
          } else {  //Soft delete
            $shopreport->deleted = 1;
		    $shopreport->save();
          }
    }



}
?>