<?php
// Model ShopAttribute
// Date created  Mon, 16 Jan 2017 15:29:37 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) shop_id                       int(11)             NO
//   (MUL) index                         int(11)             YES
//   (   ) name                          varchar(50)         NO
//   (   ) data_type                     int(11)             NO
//   (   ) is_username                   tinyint(1)          YES
//   (   ) is_password                   tinyint(1)          YES
//   (   ) is_email                      tinyint(1)          YES
//   (   ) is_name                       tinyint(1)          YES
//   (   ) is_locked                     tinyint(1)          YES
//   (   ) is_mandatory                  tinyint(1)          YES
//   (   ) is_visible                    tinyint(1)          YES
//   (   ) is_searchable                 tinyint(1)          YES
//   (   ) is_visible_on_search          tinyint(1)          YES
//   (   ) is_list                       tinyint(1)          YES
//   (   ) is_delivery                   tinyint(4)          YES
//   (   ) list_data                     text                YES
//   (   ) languages                     text                YES
//***************************************************************
class ShopAttribute extends BaseModel {
    static $table_name  = "shop_attribute";
    static $primary_key = "id";

    //Relations
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

        //17-04-2017
       //Slet alle bruger attributter
       UserAttribute::table()->delete(array('attribute_id' => $this->id));

    }
    function validateFields() {
      	testRequired($this,'shop_id');
        testRequired($this,'name');
        testRequired($this,'data_type');

        //Check parent records exists here
        Shop::find($this->shop_id);
        testMaxLength($this,'name',50);
        $this->name = trimgf($this->name);
    }
//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createShopAttribute($data) {
        $shopattribute = new ShopAttribute($data);
        $shopattribute->save();
        return($shopattribute);
    }

    static public function readShopAttribute($id) {
        $shopattribute = ShopAttribute::find($id);
        return($shopattribute);
    }

    static public function updateShopAttribute($data) {
        $shopattribute = ShopAttribute::find($data['id']);
        $shopattribute->update_attributes($data);
        $shopattribute->save();
        return($shopattribute);
    }

    static public function deleteShopAttribute($id,$realDelete=true) {

        if($realDelete) {
            $shopattribute = ShopAttribute::find($id);
    		$shopattribute->delete();
          } else {  //Soft delete
            $shopattribute->deleted = 1;
            $shopattribute->save();
          }
    }

}

?>