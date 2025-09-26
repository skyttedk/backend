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
class ShopApproval extends ActiveRecord\Model {
    static $table_name  = "shop_approval";
    static $primary_key = "id";

}
?>