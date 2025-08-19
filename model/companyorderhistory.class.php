<?php
// Model CompanyOrderHistory
// Date created  Thu, 03 Aug 2017 14:11:17 +0200
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (UNI) order_no                      varchar(100)        YES
//   (   ) company_id                    int(11)             NO
//   (   ) company_name                  varchar(100)        YES
//   (   ) shop_id                       int(11)             NO
//   (   ) shop_name                     varchar(100)        YES
//   (   ) salesperson                   text                YES
//   (   ) salenote                      varchar(1024)       NO
//   (   ) quantity                      int(11)             NO
//   (   ) expire_date                   date                YES
//   (   ) is_email                      tinyint(4)          YES
//   (   ) certificate_no_begin          varchar(250)        YES
//   (   ) certificate_no_end            varchar(250)        YES
//   (   ) certificate_value             int(11)             YES
//   (   ) is_printed                    tinyint(4)          YES
//   (   ) is_shipped                    tinyint(4)          YES
//   (   ) is_invoiced                   tinyint(4)          YES
//   (   ) ship_to_address               varchar(100)        YES
//   (   ) ship_to_address_2             varchar(100)        YES
//   (   ) ship_to_postal_code           varchar(10)         YES
//   (   ) ship_to_city                  varchar(100)        YES
//   (   ) contact_name                  varchar(100)        YES
//   (   ) contact_email                 varchar(100)        YES
//   (   ) contact_phone                 varchar(20)         YES
//   (   ) spdeal                        varchar(10)         NO
//   (   ) spdealTxt                     text                NO
//   (   ) is_cancelled                  tinyint(4)          YES
//   (   ) cvr                           varchar(15)         YES
//   (   ) ean                           varchar(50)         NO
//   (   ) is_appendix_order             tinyint(4)          YES
//   (   ) freight_calculated            tinyint(4)          YES
//   (   ) created_datetime              datetime            YES
//   (   ) modified_datetime             datetime            YES
//***************************************************************

class CompanyOrderHistory extends ActiveRecord\Model {
    static $table_name  = "company_order_history";
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
        $this->modified_datetime = date('d-m-Y H:n:s');
        $this->validateFields();
    }
    function onAfterCreate()  {}

    function onBeforeUpdate() {
        $this->modified_datetime = date('d-m-Y H:n:s');
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
        testMaxLength($this,'order_no',100);
testMaxLength($this,'company_name',100);
testMaxLength($this,'shop_name',100);
testMaxLength($this,'salenote',1024);
testMaxLength($this,'certificate_no_begin',250);
testMaxLength($this,'certificate_no_end',250);
testMaxLength($this,'ship_to_address',100);
testMaxLength($this,'ship_to_address_2',100);
testMaxLength($this,'ship_to_postal_code',10);
testMaxLength($this,'ship_to_city',100);
testMaxLength($this,'contact_name',100);
testMaxLength($this,'contact_email',100);
testMaxLength($this,'contact_phone',20);
testMaxLength($this,'spdeal',10);
testMaxLength($this,'cvr',15);
testMaxLength($this,'ean',50);

        $this->order_no = trimgf($this->order_no);
$this->company_name = trimgf($this->company_name);
$this->shop_name = trimgf($this->shop_name);
$this->salenote = trimgf($this->salenote);
$this->certificate_no_begin = trimgf($this->certificate_no_begin);
$this->certificate_no_end = trimgf($this->certificate_no_end);
$this->ship_to_address = trimgf($this->ship_to_address);
$this->ship_to_address_2 = trimgf($this->ship_to_address_2);
$this->ship_to_postal_code = trimgf($this->ship_to_postal_code);
$this->ship_to_city = trimgf($this->ship_to_city);
$this->contact_name = trimgf($this->contact_name);
$this->contact_email = trimgf($this->contact_email);
$this->contact_phone = trimgf($this->contact_phone);
$this->spdeal = trimgf($this->spdeal);
$this->cvr = trimgf($this->cvr);
$this->ean = trimgf($this->ean);

    }



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createCompanyOrderHistory($data) {
        $companyorderhistory = new CompanyOrderHistory($data);
        $companyorderhistory->save();
        return($companyorderhistory);
    }

    static public function readCompanyOrderHistory($id) {
        $companyorderhistory = CompanyOrderHistory::find($id);
        return($companyorderhistory);
    }

    static public function updateCompanyOrderHistory($data) {
        $companyorderhistory = CompanyOrderHistory::find($data['id']);
        $companyorderhistory->update_attributes($data);
        $companyorderhistory->save();
        return($companyorderhistory);
    }

    static public function deleteCompanyOrderHistory($id,$realDelete=false) {

        if($realDelete) {
            $companyorderhistory = CompanyOrderHistory::find($id);
    		$companyorderhistory->delete();
          } else {  //Soft delete
            $companyorderhistory->deleted = 1;
            $companyorderhistory->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------






}
?>

