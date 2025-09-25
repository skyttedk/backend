<?php

namespace GFUnit\cardshop\pluklister;

class DashboardData {

    public function __construct() {

    }

    private $cardshopSettings = null;
    private $expireDates = null;

    public function getShopSettingsList()
    {
        if($this->cardshopSettings == null) {
            $this->cardshopSettings = \CardshopSettings::find('all',array("order" => "language_code asc, concept_parent asc, card_price asc"));;
        }
        return $this->cardshopSettings;
    }

    public function getExpireDates() {
        if($this->expireDates == null) {
            $this->expireDates = \ExpireDate::find('all',array("order" => "expire_date asc"));
        }
        return $this->expireDates;
    }

    public function getStatMatrix() {
        $statMatrix = array();
        $shopUserDeadlines = \ShopUser::find_by_sql("SELECT expire_date, shop_id, COUNT(id) as usercount FROM `shop_user` WHERE shop_id IN (select shop_id from cardshop_settings) && blocked = 0 && shutdown = 0  && is_demo = 0 && expire_date IS NOT NULL GROUP BY expire_date, shop_id ORDER BY expire_date ASC");
        foreach($shopUserDeadlines as $shopusercount)
        {
            $deadline = $shopusercount->expire_date->format("Y-m-d");
            $statMatrix[$shopusercount->shop_id][$deadline] = $shopusercount->attributes["usercount"];
        }
        return $statMatrix;
    }

}