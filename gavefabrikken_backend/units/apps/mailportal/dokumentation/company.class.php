<?php
// Model Company
// Date created  Thu, 12 Jan 2017 21:47:42 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) name                          varchar(100)        NO
//   (   ) phone                         varchar(20)         YES
//   (   ) website                       varchar(100)        YES
//   (   ) language_code                 varchar(2)          YES
//   (   ) cvr                           varchar(15)         NO
//   (   ) username                      varchar(45)         NO
//   (   ) password                      varchar(45)         NO
//   (   ) footer                        varchar(1024)       YES
//   (   ) logo                          int(11)             YES
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
//   (   ) active                        tinyint(1)          YES
//   (   ) deleted                       tinyint(1)          YES
//   (   ) pick_group                    varchar(15)         YES
//   (   ) is_gift_certificate           tinyint(1)          YES
class Company extends BaseModel {
  static $table_name = "company";
  static $primary_key = "id";
  //Relations
  static $has_many = array(array('company_shops'));
  static $before_save = array('onBeforeSave');
  static $after_save = array('onAfterSave');
  static $before_create = array('onBeforeCreate');
  static $after_create = array('onAfterCreate');
  static $before_update = array('onBeforeUpdate');
  static $after_update = array('onAfterUpdate');
  static $before_destroy = array('onBeforeDestroy'); // virker ikke
  static $after_destroy = array('onAfterDestroy');

  public function has_users() {
    $shopusers = ShopUser::all(array('company_id' => $this->id, 'is_demo' => 0));
    return (count($shopusers) > 0);
  }

  // Trigger functions
  function onBeforeSave() {
  }

  function onAfterSave() {
  }

  function onBeforeCreate() {
    if ($this->is_gift_certificate == 1) {
      $this->username = preg_replace('/\s+/', '', $this->contact_email);
      $this->password = preg_replace('/\s+/', '', $this->cvr);
    }

      if (empty(trimgf($this->ship_to_company))) {
          $this->ship_to_company = $this->name;
      }

    if (empty(trimgf($this->ship_to_address))) {
      $this->ship_to_address = $this->bill_to_address;
      $this->ship_to_address_2 = $this->bill_to_address_2;
    }

    if (empty(trimgf($this->ship_to_postal_code))) {
      $this->ship_to_postal_code = $this->bill_to_postal_code;
    }

    if (empty(trimgf($this->ship_to_city))) {
      $this->ship_to_city = $this->bill_to_city;
    }

    $this->validateFields();
  }

  function onAfterCreate() {
  }

  function onBeforeUpdate() {
    if ($this->is_gift_certificate == 1) {
      $this->username = preg_replace('/\s+/', '', $this->contact_email);
      $this->password = preg_replace('/\s+/', '', $this->cvr);
    }
    $this->validateFields();
  }

  function onAfterUpdate() {
  }

  function onBeforeDestroy() {
  }

  function onAfterDestroy() {
    $shopusers = ShopUser::all(array('company_id' => $this->id, 'is_demo' => 0));
    foreach ($shopusers as $shopuser)
    {
       $shopuser->delete(true);
    }
  }

  function validateFields() {
    testRequired($this, 'name');
    testRequired($this, 'cvr');
    testRequired($this, 'username');
    testRequired($this, 'password');
    //Check parent records exists here
    testMaxLength($this, 'name', 100);
    testMaxLength($this, 'phone', 20);
    testMaxLength($this, 'website', 100);
    testMaxLength($this, 'language_code', 2);
    testMaxLength($this, 'cvr', 15);
    testMaxLength($this, 'username', 50);
    testMaxLength($this, 'password', 45);
    testMaxLength($this, 'footer', 1024);
    testMaxLength($this, 'logo', 100);
    testMaxLength($this, 'ship_to_attention', 100);
    testMaxLength($this, 'ship_to_address', 100);
    testMaxLength($this, 'ship_to_address_2', 100);
    testMaxLength($this, 'ship_to_postal_code', 10);
    testMaxLength($this, 'ship_to_city', 100);
    testMaxLength($this, 'ship_to_country', 100);
    testMaxLength($this, 'contact_name', 100);
    testMaxLength($this, 'contact_phone', 20);
    testMaxLength($this, 'contact_email', 50);
    testMaxLength($this, 'ship_to_company', 100);
    $this->name = trimgf($this->name);
    $this->phone = trimgf($this->phone);
    $this->website = trimgf($this->website);
    $this->language_code = trimgf($this->language_code);
    $this->ean = trimgf($this->ean);
    $this->cvr = trimgf($this->cvr);
    $this->username = trimgf($this->username);
    $this->password = trimgf($this->password);
    $this->footer = trimgf($this->footer);
    $this->logo = trimgf($this->logo);
    $this->ship_to_company = trimgf($this->ship_to_company);
    $this->ship_to_attention = trimgf($this->ship_to_attention);
    $this->ship_to_address = trimgf($this->ship_to_address);
    $this->ship_to_address_2 = trimgf($this->ship_to_address_2);
    $this->ship_to_postal_code = trimgf($this->ship_to_postal_code);
    $this->ship_to_city = trimgf($this->ship_to_city);
    $this->ship_to_country = trimgf($this->ship_to_country);
    $this->contact_name = trimgf($this->contact_name);
    $this->contact_phone = trimgf($this->contact_phone);
    $this->contact_email = trimgf($this->contact_email);

  }

  public static function stateTextList($num=null)
  {

      $stateList = array(
          0 => "Archived",
          1 => "Created",
          2 => "Approval",
          3 => "Wait sync",
          4 => "Blocked",
          5 => "Synced",
          6 => "Sync fail",
          7 => "Child (no sync)"
      );

      if($num == null) return $stateList;
      else if(isset($stateList[$num])) return $stateList[$num];
      else return "Unknown";
  }

   public function getStateText()
   {
       return self::stateTextList($this->company_state);
   }

  //---------------------------------------------------------------------------------------
  // Static CRUD Methods
  //---------------------------------------------------------------------------------------

  static public function createCompany($data) {
    $company = new Company($data);
    $company->save();
    return ($company);
  }

  static public function readCompany($id) {
    $company = Company::find($id);
    return ($company);
  }

  static public function updateCompany($data) {
    $company = Company::find($data['id']);
    $company->update_attributes($data);
    $company->save();
    return ($company);
  }

  static public function deleteCompany($id) {
    $company = Company::find($id);
    if ($company->has_users()) {
      throw new exception('Virksomhed kan ikke slettes, da der er tilknyttet brugere');
    }
    $company->deleted = 1;
    $company->save();
  }
  //---------------------------------------------------------------------------------------
  // Custom Methods
  //---------------------------------------------------------------------------------------

}
?>