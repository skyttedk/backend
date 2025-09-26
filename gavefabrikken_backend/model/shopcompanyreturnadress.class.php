<?php
// Model System
// Date created  Mon, 16 Jan 2017 15:29:57 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) order_nos_id                  int(11)             YES
//   (   ) present_nos_id                int(11)             YES
//   (   ) demo_order_nos_id             int(11)             YES
//   (   ) gift_certificate_nos_id       int(11)             YES
//   (   ) company_order_nos_id          int(11)             YES
//   (   ) is_production                 tinyint(1)          YES
//   (   ) full_trace                    tinyint(1)          YES
//   (   ) is_mailing                    tinyint(4)          YES
//   (   ) smtp_server                   varchar(50)         YES
//   (   ) smtp_username                 varchar(50)         YES
//   (   ) smtp_password                 varchar(50)         YES
//   (   ) smtp_port                     varchar(50)         YES
//   (   ) last_order_update             datetime            YES
//   (   ) dummy_present                 int(11)             YES
//***************************************************************
class ShopCompanyReturnAdress extends BaseModel {
    static $table_name  = "shop_company_return_adress";
    static $primary_key = "id";
}
?>