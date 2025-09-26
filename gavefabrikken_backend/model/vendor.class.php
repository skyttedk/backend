<?php
// Model Vendor
// Date created  Mon, 16 Jan 2017 15:30:07 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) name                          varchar(100)        YES
//***************************************************************
class Vendor extends ActiveRecord\Model {
	static $table_name  = "vendor";
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
      	
        //Check parent records exists here
		testMaxLength($this,'name',100);

        $this->name = trimgf($this->name);

    }



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

	static public function createVendor($data) {
		$vendor = new Vendor($data);
        $vendor->save();
        return($vendor);
	}

	static public function readVendor($id) {
		$vendor = Vendor::find($id);
        return($vendor);
	}

	static public function updateVendor($data) {
		$vendor = Vendor::find($data['id']);
		$vendor->update_attributes($data);
        $vendor->save();
        return($vendor);
	}

	static public function deleteVendor($id,$realDelete=true) {

	    if($realDelete) {
            $vendor = Vendor::find($id);
    		$vendor->delete();
          } else {  //Soft delete
            $vendor->deleted = 1;
		    $vendor->save();
          }
    }


}
?>