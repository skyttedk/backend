<?php
// Model Media
// Date created  Mon, 16 Jan 2017 15:27:14 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) type                          int(11)             YES
//   (   ) caption                       varchar(100)        YES
//   (   ) description                   varchar(1024)       YES
//   (   ) path                          varchar(1024)       NO
//   (   ) width                         int(11)             YES
//   (   ) height                        int(11)             YES
//   (   ) active                        tinyint(1)          YES
//   (   ) presentmedia_id               int(11)             NO
//***************************************************************
abstract class mediaType
{
    const picture = 0;
    const logo = 1;
    const video = 2;
}

class Media extends BaseModel {
	static $table_name  = "media";
	static $primary_key = "id";

	static $before_create =  array('onBeforeCreate');
	static $before_update =  array('onBeforeUpdate');

	// Trigger functions

	function onBeforeCreate() {
       $this->validateFields();
	}

	function onBeforeUpdate() {
       $this->validateFields();
	}

    function validateFields() {
      	testRequired($this,'path');

		testMaxLength($this,'caption',100);
        testMaxLength($this,'description',1024);
        testMaxLength($this,'path',1024);

        $this->caption = trimgf($this->caption);
        $this->description = trimgf($this->description);
        $this->path = trimgf($this->path);
    }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

	static public function createMedia($data) {
		$media = new Media($data);
        $media->save();
        return($media);
	}

	static public function readMedia($id) {
		$media = Media::find($id);
        return($media);
	}

	static public function updateMedia($data) {
		$media = Media::find($data['id']);
		$media->update_attributes($data);
        $media->save();
        return($media);
	}

	static public function deleteMedia($id,$realDelete=true) {

	    if($realDelete) {
            $media = Media::find($id);
    		$media->delete();
          } else {  //Soft delete
            $media->deleted = 1;
		    $media->save();
          }
    }



}
?>