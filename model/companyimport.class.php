<?php
// Model CompanyImport
// Date created  Thu, 12 Jan 2017 21:49:44 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) name                          varchar(100)        NO
//   (   ) phone                         varchar(20)         YES
//   (   ) cvr                           varchar(15)         NO
//   (   ) bill_to_address               varchar(100)        YES
//   (   ) bill_to_address_2             varchar(100)        YES
//   (   ) bill_to_postal_code           varchar(100)        YES
//   (   ) bill_to_city                  varchar(100)        YES
//   (   ) bill_to_country               varchar(100)        YES
//   (   ) ship_to_attention             varchar(100)        YES
//   (   ) ship_to_address               varchar(100)        YES
//   (   ) ship_to_address_2             varchar(100)        YES
//   (   ) ship_to_postal_code           varchar(10)         YES
//   (   ) ship_to_city                  varchar(100)        YES
//   (   ) ship_to_country               varchar(100)        YES
//   (   ) contact_name                  varchar(100)        YES
//   (   ) contact_phone                 varchar(20)         YES
//   (   ) contact_email                 varchar(45)         YES
//   (   ) shop_id                       int(11)             YES
//   (   ) shop_name                     varchar(100)        YES
//   (   ) expire_date                   date                NO
//   (   ) quantity                      int(11)             YES
//   (   ) value                         int(11)             YES
//   (   ) shipment_method               varbinary(10)       NO
//   (   ) imported                      tinyint(4)          YES
//   (   ) deleted                       tinyint(4)          YES
//   (   ) standby                       tinyint(4)          YES
//   (   ) salesperson                   varchar(100)        YES
//   (   ) noter                         text                YES
//   (   ) noter_intern                  text                YES
//***************************************************************
class CompanyImport extends ActiveRecord\Model {
  static $table_name = "company_import";
  static $primary_key = "";
  static $before_save = array('onBeforeSave');
  static $after_save = array('onAfterSave');
  static $before_create = array('onBeforeCreate');
  static $after_create = array('onAfterCreate');
  static $before_update = array('onBeforeUpdate');
  static $after_update = array('onAfterUpdate');
  static $before_destroy = array('onBeforeDestroy'); // virker ikke
  static $after_destroy = array('onAfterDestroy');
// Trigger functions
  function onBeforeSave() {
  }
  function onAfterSave() {
  }
  function onBeforeCreate() {
    $this->created_datetime = date('d-m-Y H:i:s');
    $this->modified_datetime = date('d-m-Y H:i:s');
    $this->validateFields();
  }
  function onAfterCreate() {
  }
  function onBeforeUpdate() {
    $this->modified_datetime = date('d-m-Y H:i:s');
    $this->validateFields();
  }
  function onAfterUpdate() {
  }
  function onBeforeDestroy() {
  }
  function onAfterDestroy() {
  }
  function validateFields() {
  }
//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
  static public function createCompanyImport($data) {
    $companyimport = new CompanyImport($data);
    $companyimport->save();
    return ($companyimport);
  }
  static public function readCompanyImport($id) {
    $companyimport = CompanyImport::find($id);
    return ($companyimport);
  }
  static public function updateCompanyImport($data) {
    $companyimport = CompanyImport::find($data['id']);
    $companyimport->update_attributes($data);
    $companyimport->save();
    return ($companyimport);
  }
  static public function deleteCompanyImport($id, $realDelete = false) {
    if ($realDelete) {
      $companyimport = CompanyImport::find($id);
      $companyimport->delete();
    }
    else { //Soft delete
      $companyimport->deleted = 1;
      $companyimport->save();
    }
  }
}
?>

