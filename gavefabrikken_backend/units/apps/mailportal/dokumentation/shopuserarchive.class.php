<?php
// Model ShopUserArchive
// Date created  Sun, 11 Jun 2017 21:41:31 +0200
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) shop_id                       int(11)             YES
//   (   ) company_id                    int(11)             YES
//   (   ) username                      varchar(250)        YES
//   (   ) password                      varchar(250)        YES
//   (   ) expire_date                   datetime            YES
//   (   ) blocked                       tinyint(4)          YES
//***************************************************************

class ShopUserArchive extends ActiveRecord\Model {
    static $table_name  = "shop_user_archive";
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
        testMaxLength($this,'username',250);
testMaxLength($this,'password',250);

        $this->username = trimgf($this->username);
$this->password = trimgf($this->password);

    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createShopUserArchive($data) {
        $shopuserarchive = new ShopUserArchive($data);
        $shopuserarchive->save();
        return($shopuserarchive);
    }

    static public function readShopUserArchive($id) {
        $shopuserarchive = ShopUserArchive::find($id);
        return($shopuserarchive);
    }

    static public function updateShopUserArchive($data) {
        $shopuserarchive = ShopUserArchive::find($data['id']);
        $shopuserarchive->update_attributes($data);
        $shopuserarchive->save();
        return($shopuserarchive);
    }

    static public function deleteShopUserArchive($id,$realDelete=true) {

        if($realDelete) {
            $shopuserarchive = ShopUserArchive::find($id);
    		$shopuserarchive->delete();
          } else {  //Soft delete
            $shopuserarchive->deleted = 1;
            $shopuserarchive->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------



   static public function isArchiveUser( $username, $password) {

    $shopUsers = ShopUserArchive::find('all', array('conditions' => array('LOWER(username)=? and LOWER(password) =?', lowercase($username), lowercase($password))));
    if (count($shopUsers) > 0)
      return ($shopUsers[0]->id);
    else
      return (0);
   }

   static public function Login( $username, $password) {

    $result = "";
    //vi skal have shopid med her ogs�
    $shopUsers = ShopUserArchive::find('all', array('conditions' => array('LOWER(username)=? and LOWER(password) =?', lowercase($username), lowercase($password))));

    if (count($shopUsers) <> 1)
     $result = 'Ugyldig login.('.countgf($shopUsers).')';
    else {
      $shopUser = $shopUsers[0];

      /*Tjek om shoppen er �ben  */
      $shop = Shop::find($shopUser->shop_id);
      if($shop->active==0) {
           if($shop->is_demo==0) {
                $result = 'closed';
            }
      }

      if($shopUser->blocked==1) {
        $result = 'Gavekortet er spaerret';
      }

      $today = date("Y-m-d");   // hvis demobruger skal der l�gge 14 dage til
      if($shop->is_demo==0)
      {
        $expiredate = ExpireDate::find_by_expire_date($shopUser->expire_date);
        if($expiredate->blocked==1)
          {
           $result = 'closed';
          }

        if($shopUser->delivery_printed==1)   {
               $result = 'closed';    
         }


       }

      return ($result);
    }
  }



}
?>

