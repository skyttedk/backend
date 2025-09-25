<?php
// Model PresentMedia
// Date created  Mon, 16 Jan 2017 15:29:24 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) present_id                    int(11)             NO
//   (   ) media_path                    varchar(1024)       NO
//   (   ) index                         int(11) unsigned    YES
//***************************************************************
class PresentMedia extends BaseModel {
	static $table_name  = "present_media";
	static $primary_key = "id";

    //Relations
    static $belongs_to = array(array('present'));

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
	function onAfterDestroy()  {}
    function validateFields() {
      	testRequired($this,'present_id');
        testRequired($this,'media_path');

        //Check parent records exists here
        Present::find($this->present_id);  //sikre at parent findes

    }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
	static public function createPresentMedia($data) {
		$presentmedia = new PresentMedia($data);
        $presentmedia->save();
        return($presentmedia);
	}

	static public function readPresentMedia($id) {
		$presentmedia = PresentMedia::find($id);
        return($presentmedia);
	}

	static public function updatePresentMedia($data) {
		$presentmedia = PresentMedia::find($data['id']);
		$presentmedia->update_attributes($data);
        $presentmedia->save();
        return($presentmedia);
	}

	static public function deletePresentMedia($id,$realDelete=true) {

	    if($realDelete) {
            $presentmedia = PresentMedia::find($id);
    		$presentmedia->delete();
          } else {  //Soft delete
            $presentmedia->deleted = 1;
		    $presentmedia->save();
          }
    }







}
?>