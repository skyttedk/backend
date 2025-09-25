<?php
// Model ShopPresent
// Date created  Mon, 16 Jan 2017 15:29:45 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) shop_id                       int(11)             NO
//   (MUL) present_id                    int(11)             NO
//   (   ) properties                    text                YES
//   (   ) index_                        int(11)             YES
//   (   ) active                        tinyint(4)          YES
//***************************************************************
class ShopPresent extends BaseModel {
  static $table_name  = "shop_present";
  static $primary_key = "id";

  static $calculated_attributes = array("present");

  public function present() {
       $present = Present::find($this->present_id);

       if($present->present_media !== null && countgf($present->present_media) > 0) {
       //if(isset($present->present_media[0])) {
         $present->attributes['first_image_id'] =$present->present_media[0]->id;
         $present->attributes['first_image_media_path'] =$present->present_media[0]->media_path;
       } else {
         $present->attributes['first_image_id'] ='';;
         $present->attributes['first_image_media_path'] ='';
       }

	   $present->attributes['media'] = $present->present_media;
	   $present->attributes['descriptions'] = $present->descriptions;
       return($present);
   }

	static $before_create =  array('onBeforeCreate');
	static $before_update =  array('onBeforeUpdate');

	function onBeforeCreate() {
	    $this->validateFields();
	}

	function onBeforeUpdate() {
	  $this->validateFields();
	}

    function validateFields() {
      //Test Required Fields
      testRequired($this,'shop_id');
      testRequired($this,'present_id');
      //Test Table Relations
      Shop::find($this->shop_id);
      Present::find($this->present_id);
    }

    //*  Custom
	static public function getPresentProperties($id) {
		$shoppresent = ShopPresent::find($id);
		return($shoppresent->properties);
	}

	static public function setPresentProperties($id,$data) {
		$shoppresent = ShopPresent::find($id);
        $shoppresent->properties = $data;
        $dataObj = (array) json_decode($data);
        $dataObj["aktivOption"] == true ? $shoppresent->active = 1 : $shoppresent->active = 0;
		$shoppresent->save();
	}

	static public function setPresentPropertiesSchedule($id,$data) {
		$shoppresent = ShopPresent::find($id);
        $shoppresent->properties = $data;
        $dataObj = (array) json_decode($data);
        $dataObj["aktivOption"] == true ? $shoppresent->active = 1 : $shoppresent->active = 0;
		$shoppresent->save();

	}


   static public function setShopPresentIndexes($data) {
     $indexes = json_decode($data);
        foreach($indexes as $index)      {
       	  $shoppresent = ShopPresent::find($index->id);
          $shoppresent->index_ = $index->index;
   		$shoppresent->save();
        }
   }

}
?>