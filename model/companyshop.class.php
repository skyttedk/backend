<?php
// Model CompanyShop
// Date created  Mon, 16 Jan 2017 15:26:57 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) company_id                    int(11)             NO
//   (MUL) shop_id                       int(11)             NO
//***************************************************************
class CompanyShop extends BaseModel {
  static $table_name = "company_shop";
  static $primary_key = "id";
  static $before_create = array('onBeforeCreate');
  
  //Relations
  static $belongs_to = array( 
							
								array('company'), 
								array('shop')
							
							);

  function onBeforeCreate() {
    $this->validateFields();
  }

  //Skal tjekke at shop id og company id findes
  function validateFields() {
    testRequired($this, 'company_id');
    testRequired($this, 'shop_id');

    //Check parent records exists here
    Company::find($this->company_id);
    Shop::find($this->shop_id);
  }
}
?>