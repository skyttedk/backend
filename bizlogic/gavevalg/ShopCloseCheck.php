<?php

namespace GFBiz\Gavevalg;

use CardshopSettings;
use ExpireDate;

class ShopCloseCheck
{

    public static function isShopOpen($shopid,$expire_date) {

        $closeDate = self::getShopCloseDate($shopid,$expire_date);

        if($closeDate == null) {
            //echo "Error reading close date";
            return true;
        }

        return $closeDate->getTimestamp() > time();

    }

    /**
     * Returns the close date of the shop and expire_date
     * @param $shopid
     * @param $expire_date
     * @return DateTime
     */
    public static function getShopCloseDate($shopid,$expire_date)
    {

        // Load cardshop_settings
        $cardshopSettingsList = CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings WHERE shop_id = ".intval($shopid));
        if(countgf($cardshopSettingsList) == 0) {
            self::mailLog("Kunne ikke finde cardshop_settings til shop: ".$shopid);
            return null;
        } else if(countgf($cardshopSettingsList) > 1) {
            self::mailLog("Fandt flere cardshop_settings til shop: ".$shopid);
            return null;
        } else {
            $cardshopSettings = $cardshopSettingsList[0];
        }

        if(!is_string($expire_date)) {
            $expire_date = $expire_date->format('Y-m-d');
        }

        // Load cardshop expiredate
        $expireDateList = ExpireDate::find_by_sql("SELECT expire_date.* FROM `cardshop_expiredate`, expire_date where cardshop_expiredate.shop_id = ".intval($shopid)." && expire_date.expire_date = '".$expire_date."' && expire_date.id = cardshop_expiredate.expire_date_id");

        if(countgf($expireDateList) == 0) {

            $expireDateList = ExpireDate::find_by_sql("SELECT expire_date.* FROM  expire_date where expire_date.expire_date = '".$expire_date."'");
            if(countgf($expireDateList) == 0) {
                self::mailLog("Kunne ikke finde expire_dates til shop: " . $shopid . ", dato: " . $expire_date);
                return null;
            } else {
                $expireDateObj = $expireDateList[0];
            }
        }
        else if(countgf($expireDateList) > 1) {
           self::mailLog("Fandt mere end 1 expire_dates til shop: ".$shopid.", dato: ".$expire_date);
            return null;
        }
        else {
            $expireDateObj = $expireDateList[0];
        }

        // Find week no
        $weekNo = $expireDateObj->week_no;
        $isHomeDelivery = $expireDateObj->is_delivery == 1;
        $closeDate = null;

        // Find close date
        if($cardshopSettings->special_private1_expiredate == $expireDateObj->display_date) {
            $closeDate = $cardshopSettings->special_private1_close;
        } else if($cardshopSettings->special_private2_expiredate == $expireDateObj->display_date) {
            $closeDate = $cardshopSettings->special_private2_close;
        } else if($isHomeDelivery) {
            $closeDate = $cardshopSettings->private_close;
        } else if($weekNo == 47) {
            $closeDate = $cardshopSettings->week_47_close;
        } else if($weekNo == 48) {
            $closeDate = $cardshopSettings->week_48_close;
        } else if($weekNo == 49) {
            $closeDate = $cardshopSettings->week_49_close;
        } else if($weekNo == 50) {
            $closeDate = $cardshopSettings->week_50_close;
        } else if($weekNo == 51) {
            $closeDate = $cardshopSettings->week_51_close;
        } else if($weekNo == 4 || $weekNo == 5) {
            $closeDate = $cardshopSettings->week_04_close;
        }

        if($closeDate == null) {
            self::mailLog("Kunne ikke finde lukkedato ud fra shop og expire date. shop: ".$shopid.", dato: ".$expire_date);
            return null;
        }

        return $closeDate;

    }

    protected static function mailLog($message) {
        $modtager = 'sc@interactive.dk';
        mailgf($modtager, "Shop close check error - PROD", $message."\r\n<br>\r\n<br>DUMP DATA:\r\n<pre>".print_r($_POST,true)."</pre><br>\r\n<br>");
    }
    
}