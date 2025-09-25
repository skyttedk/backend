<?php
// Model ShopUserAutoselect
// Date created  Wed, 11 Oct 2017 20:24:23 +0200
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) shop_id                       int(11)             YES
//   (   ) company_id                    int(11)             YES
//   (   ) shopuser_id                   int(11)             YES
//   (   ) present_id                    int(11)             YES
//   (   ) present_model_id              int(11)             YES
//   (   ) created_datetime               datetime
//***************************************************************

class ShopUserAutoselect extends ActiveRecord\Model {
    static $table_name  = "shop_user_autoselect";
    static $primary_key = "id";

    //Relations
    //static $has_many = array(array('<child_table>'));
    //static $belongs_to = array(array('<parent_table>'));

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

        $this->created_datetime = date('d-m-Y H:n:s');             
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


    }



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createShopUserAutoselect($data) {
        $shopuserautoselect = new ShopUserAutoselect($data);
        $shopuserautoselect->save();
        return($shopuserautoselect);
    }

    static public function readShopUserAutoselect($id) {
        $shopuserautoselect = ShopUserAutoselect::find($id);
        return($shopuserautoselect);
    }

    static public function updateShopUserAutoselect($data) {
        $shopuserautoselect = ShopUserAutoselect::find($data['id']);
        $shopuserautoselect->update_attributes($data);
        $shopuserautoselect->save();
        return($shopuserautoselect);
    }

    static public function deleteShopUserAutoselect($id,$realDelete=true) {

        if($realDelete) {
            $shopuserautoselect = ShopUserAutoselect::find($id);
    		$shopuserautoselect->delete();
          } else {  //Soft delete
            $shopuserautoselect->deleted = 1;
            $shopuserautoselect->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------






}
?>

