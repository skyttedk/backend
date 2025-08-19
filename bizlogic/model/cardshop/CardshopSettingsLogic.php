<?php

namespace GFBiz\Model\Cardshop;

use ActiveRecord\DateTime;

class ShopExpireDate
{


    private $shopID;
    private $expireDate;
    private $openDate;
    private $closeDate;
    private $closeWebsale;
    private $closeSale;
    private $physicalCloseDays;
    private $special;
    private $useEnvFee;

    private $cardValues;

    public function __construct($shopID,$expireDate,$openDate,$closeDate,$closeWebsale,$closeSale,$physicalCloseDays,$special="",$useEnvFee=0,$cardValues="")
    {

        //if(in_array($shopID,array(2960,2962,2963,2961,2999)) && trimgf($special) == "") {
        //    $special = "mailonly";
        //}

        $this->shopID = $shopID;
        $this->expireDate = $expireDate;
        $this->openDate = $openDate;
        $this->closeDate = $closeDate;
        $this->closeWebsale = $closeWebsale;
        $this->closeSale = $closeSale;
        $this->physicalCloseDays = $physicalCloseDays;
        $this->special = $special;
        $this->useEnvFee = $useEnvFee;
        $this->cardValues = $cardValues;
    }

    /** @return bool */
    public function isValidWeek()
    {
        if($this->shopID == 0 || !($this->expireDate instanceof \ExpireDate) || $this->openDate == null || $this->closeDate == null || $this->expireDate->blocked == 1) {
            return false;
        }
        return true;
    }

    private $langCode = 0;
    public function setLanguageCode($lang) {
        $this->langCode = intval($lang);
    }

    /** @return int */
    public function getShopID() { return $this->shopID; }

    /** @return \ExpireDate */
    public function getExpireDate() { return $this->expireDate; }

    /** @return string */
    public function getExpireDateText() { return $this->expireDate->expire_date->format("Y-m-d"); }
    public function getAlternativeExpireDateText() { return $this->expireDate->expire_date->format("d-m-Y"); }

    /** @return DateTime */
    public function getOpenDate() { return $this->openDate; }

    /** @return DateTime */
    public function getCloseDate() { return $this->closeDate; }

    /** @return DateTime */
    public function getCloseWebSale() { return $this->closeWebsale; }

    /** @return DateTime */
    public function getCloseSale() { return $this->closeSale; }

    /** @return bool */
    public function isOpen($checkStart = true) {
        $now = new DateTime('now');
        return (($checkStart == false || $now > $this->getOpenDate()) && $now < $this->getCloseDate());
    }

    /** @return bool */
    public function isSaleOpen() {
        return new DateTime('now') < $this->getCloseSale();
    }

    /** @return bool */
    public function isWebsaleOpen() {
        return new DateTime('now') < $this->getCloseWebSale();
    }

    /** @return bool */
    public function isEmailWebsaleOpen() {
        return new DateTime('now') < $this->getCloseWebSale();
    }

    /** @return bool */
    public function isPhysicalWebsaleOpen() {
        $now = new DateTime('now');
        $now->sub(new \DateInterval("P".intval($this->physicalCloseDays)."D"));
        return new $now < $this->getCloseWebSale();
    }

    /** @return int */
    public function getWeekNo() { return $this->getExpireDate()->week_no; }

    /** @return DateTime */
    public function getDate() { return $this->getExpireDate()->expire_date; }

    /** @return bool */
    public function isDelivery() { return $this->getExpireDate()->is_delivery == 1; }

    public function getCardValues() {
        if($this->cardValues == null || trim($this->cardValues) == "") {
            return null;
        }

        $values = explode(",",$this->cardValues);
        $checkedValues = array();

        foreach($values as $value) {
            $value = trim($value);
            if(intvalgf($value) > 0) {
                $checkedValues[] = intvalgf($value);
            }
        }

        if(count($checkedValues) == 0) return null;
        return $checkedValues;

    }

    public function toJsonArray()
    {

        $weekNo = $this->expireDate->week_no;

        if(intval($weekNo) == 5 && $this->langCode == 4) {
            $weekNo = 4;
        }

        return array(
            "shop_id" => $this->shopID,
            "week_no" => $weekNo,
            "expire_date" => $this->expireDate->expire_date->format('d-m-Y'),
            "display_date" => $this->expireDate->display_date,
            "open_date" => $this->getOpenDate()->format('d-m-Y H:m:s'),
            "close_date" => $this->getCloseDate()->format('d-m-Y H:m:s'),
            "is_open" => $this->isOpen(),
            "websale_close_date" => $this->getCloseWebSale()->format('d-m-Y H:m:s'),
            "websale_is_open" => $this->isWebsaleOpen(),
            "sale_close_date" => $this->getCloseSale()->format('d-m-Y H:m:s'),
            "sale_is_open" => $this->isSaleOpen(),
            "special" => $this->special,
            "is_delivery" => $this->expireDate->is_delivery,
            "use_envfee" => $this->useEnvFee,
            "card_values" => $this->getCardValues()
        );
    }

}


class ShopProductLine
{

    /**
     * UseType
     * 0: do not use
     * 1: use, optional and disabled by default
     * 2: use, optional and enabled by default
     * 3: use, mandatory
     */

    private $shop_id;
    private $useType;
    private $code;
    private $name;
    private $price;
    private $extraData;

    public function __construct($shop_id,$useType,$code,$name,$price,$extraData=null)
    {
        $this->shop_id = $shop_id;
        $this->useType = $useType;
        $this->code = $code;
        $this->name = $name;
        $this->price = $price;
        $this->extraData = $extraData;
        if($this->extraData == null) $this->extraData = array();
    }


    public function isUsed() { return $this->useType > 0; }
    public function isOptional() { return $this->useType == 1 || $this->useType == 2; }
    public function isMandatory() { return $this->useType == 3; }
    public function isDefault() { return $this->useType == 3 || $this->useType == 2; }
    public function isPerCard() { return !isset($this->extraData["percard"]) ? false : $this->extraData["percard"]; }

    public function getShopID() { return $this->shop_id; }
    public function getCode() { return $this->code; }
    public function getName() { return $this->name; }
    public function getPrice() { return $this->price; }
    public function getExtraData() { return $this->extraData; }
    public function getExtraDataField($field) { return isset($this->extraData[$field]) ? $this->extraData[$field] : ""; }

    public function toJsonArray()
    {

        $metadata = $this->extraData;
        if(!is_array($metadata)) $metadata = array();
        $metadata["ismandatory"] = $this->isMandatory();
        $metadata["isdefault"] = $this->isDefault();

        return array(
            "shop_id" => $this->shop_id,
            "code" => $this->code,
            "name" => $this->name,
            "price" => $this->price/100,
            "metadata" => $metadata
        );
    }


}

class CardshopSettingsLogic
{

    public static function isCardshopAdmin($language_id = 0)
    {
        $currentUserID = \router::$systemUser->id;
        $dkAdmins = array(50,199,110,138,51,338);
        $seAdmins = array(124,162,153,304,291);
        $noAdmins = array(147,66,190,145,217,285,286,305);

        if($language_id == 1) {
            return in_array($currentUserID, $dkAdmins);
        } else if($language_id == 4) {
            return in_array($currentUserID, $noAdmins);
        } else if($language_id == 5) {
            return in_array($currentUserID, $seAdmins);
        }

        return in_array($currentUserID, $dkAdmins) || in_array($currentUserID, $noAdmins) || in_array($currentUserID, $seAdmins);

    }
    
    /**
     * STATIC SHARED DATA
     */

    protected static $expireDates = null;
    protected static function getAllExpireDates() {
        if(self::$expireDates != null) return self::$expireDates;
        self::$expireDates = \ExpireDate::find('all',array("order" => "expire_date asc"));
        return self::$expireDates;
    }

    /**
     * CLASS MEMBERS
     */

    private $shop;
    private $settings;
    private $weeks;
    private $products;

    /*
     * CONSTRUCTOR
     */

    public function __construct($shopid)
    {
        $this->loadData($shopid);
    }

    /**
     * PUBLIC GETTERS
     */

    public function getShop() { return $this->shop; }
    public function getSettings() { return $this->settings; }

    /** @return ShopExpireDate[] */
    public function getWeeks() { return $this->weeks; }

    /** @return ShopProductLine[] */
    public function getProducts() { return $this->products; }

    /** @return ShopProductLine[] */
    public function getProductMap() {
        $map = array();
        foreach($this->products as $product) $map[$product->getCode()] = $product;
        return $map;
    }

    /** @return ShopExpireDate */
    public function getWeekByExpireDate($expireDate)
    {
        foreach($this->getWeeks() as $week) {
            if($week->getExpireDateText() == (is_object($expireDate) ? $expireDate->format("Y-m-d") : $expireDate) || $week->getAlternativeExpireDateText() == (is_object($expireDate) ? $expireDate->format("Y-m-d") : $expireDate)) {
                return $week;
            }
        }
        return null;
    }

    /**
     * PRIVATE HELPERS
     */

    private function loadData($shopid)
    {

        // Load shop and shop settings
        $this->shop = \Shop::find(intval($shopid));
        $this->settings = \CardshopSettings::find("first",array("conditions" => array("shop_id" => intval($shopid))));

        // Load expire dates
        $this->weeks = array();
        $expireDates = self::getAllExpireDates();

        foreach($expireDates as $expireDate) {

            $weekNo = $expireDate->week_no;
            $weekObject = null;

            if($expireDate->is_special_private == 1) {
                if($this->settings->special_private1_expiredate != null && $this->settings->special_private1_expiredate == $expireDate->display_date) {
                    $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->special_private1_open,$this->settings->special_private1_close,$this->settings->special_private1_close_websale,$this->settings->special_private1_close_sale,$this->settings->physical_close_days,"mailonly",($this->settings->env_fee_percent > 0),$this->settings->card_values);
                } else if($this->settings->special_private2_expiredate != null && $this->settings->special_private2_expiredate == $expireDate->display_date) {
                    $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->special_private2_open,$this->settings->special_private2_close,$this->settings->special_private2_close_websale,$this->settings->special_private2_close_sale,$this->settings->physical_close_days,"mailonly",($this->settings->env_fee_percent > 0),$this->settings->card_values);
                }
            }
            else if($weekNo == 47) {
                $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->week_47_open,$this->settings->week_47_close,$this->settings->week_47_close_websale,$this->settings->week_47_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
            }
            else if($weekNo == 48) {
                $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->week_48_open,$this->settings->week_48_close,$this->settings->week_48_close_websale,$this->settings->week_48_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
            }
            else if($weekNo == 49) {
                $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->week_49_open,$this->settings->week_49_close,$this->settings->week_49_close_websale,$this->settings->week_49_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
            }
            else if($weekNo == 50) {
                if(($this->shop->id == 52 || $this->shop->id == 4668) && $expireDate->is_jgk_50 == 1) {
                    $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->week_50_open,$this->settings->week_50_close,$this->settings->week_50_close_websale,$this->settings->week_50_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
                } else if($this->shop->id != 52 && $this->shop->id != 4668 && $expireDate->is_jgk_50 == 0) {
                    $weekObject = new ShopExpireDate($this->shop->id, $expireDate, $this->settings->week_50_open, $this->settings->week_50_close, $this->settings->week_50_close_websale, $this->settings->week_50_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
                }
            }
            else if($weekNo == 51) {
                $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->week_51_open,$this->settings->week_51_close,$this->settings->week_51_close_websale,$this->settings->week_51_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
            }
            else if($weekNo == 98 && in_array($shopid,array(8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366))) {
                $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->private_open,$this->settings->private_close,$this->settings->private_close_websale,$this->settings->private_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
            }
            else if($weekNo == 5) {
                $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->week_04_open,$this->settings->week_04_close,$this->settings->week_04_close_websale,$this->settings->week_04_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
            }
            else if($weekNo == 0 && $expireDate->is_delivery == 1) {

                if($this->settings->private_expire_date == $expireDate->expire_date->format("d-m-Y")) {
                    $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->private_open,$this->settings->private_close,$this->settings->private_close_websale,$this->settings->private_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
                } else if($this->settings->private_expire_date_future == $expireDate->expire_date->format("d-m-Y")) {
                    $weekObject = new ShopExpireDate($this->shop->id,$expireDate,$this->settings->private_open,$this->settings->private_close,$this->settings->private_close_websale,$this->settings->private_close_sale,$this->settings->physical_close_days,"",($this->settings->env_fee_percent > 0),$this->settings->card_values);
                }
            }

            if($weekObject != null && $expireDate->blocked == 0 && $weekObject->isValidWeek()) {
                $weekObject->setLanguageCode($this->settings->language_code);
                $this->weeks[] = $weekObject;
            } else {
                //"INVALID WEEK: ".print_r($weekObject);
            }

        }

        // Load products
        $this->loadProductData();


    }

    private function loadProductData()
    {

        $this->products = array();

        // Card value
        $this->products[] = new ShopProductLine($this->shop->id,3,"CONCEPT","Gavekort pris",$this->settings->card_price,array("restrict" => "","percard" => true,"minquantity" => null,"requireon" => "allways","hideon" => ""));

        // Private delivery
        if($this->settings->privatedelivery_use > 0) {

            if($this->settings->language_code == 5) {
                $this->products[] = new ShopProductLine($this->shop->id,$this->settings->privatedelivery_use,"PRIVATEDELIVERY","Levering",$this->settings->privatedelivery_price,array("restrict" => "","percard" => true,"minquantity" => null,"requireon" => "","hideon" => ""));
            }
            else {
                $this->products[] = new ShopProductLine($this->shop->id,$this->settings->privatedelivery_use,"PRIVATEDELIVERY","Privatlevering",$this->settings->privatedelivery_price,array("restrict" => "privatedelivery","percard" => true,"minquantity" => null,"requireon" => "privatedelivery","hideon" => "companydelivery"));
            }
        }

        // Cardfee
        if($this->settings->cardfee_use > 0) {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->cardfee_use,"CARDFEE","Kort pris",$this->settings->cardfee_price,array("restrict" => "","minquantity" => $this->settings->cardfee_minquantity,"percard" => true,"requireon" => ("physicalcards"),"hideon" => ($this->settings->language_code  == 4 ? "" : "emailcards")));
        }


        // Minorderfee
        if($this->settings->minorderfee_use > 0) {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->minorderfee_use,"MINORDERFEE","Gebyr for lille ordre (under ".$this->settings->minorderfee_mincards.")",$this->settings->minorderfee_price,array("restrict" => "","minquantity" =>$this->settings->minorderfee_mincards,"percard" => false,"requireon" => "","hideon" => ""));
        }

        // Card delivery
        if($this->settings->carddelivery_use > 0) {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->carddelivery_use,"CARDDELIVERY","Forsendelse af kort",$this->settings->carddelivery_price,array("restrict" => "physicalcards","percard" => false,"minquantity" => null,"requireon" => "physicalcards","hideon" => "emailcards"));
        }

        // Carryup
        /*
        if($this->settings->carryup_use > 0) {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->carryup_use,"CARRYUP","Opbæring",$this->settings->carryup_price,array("restrict" => "","percard" => false,"minquantity" => null,"requireon" => "","hideon" => "privatedelivery"));
        }
        */

        // DOT
        /*
        if($this->settings->dot_use > 0) {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->dot_use,"DOT","DOT (angiv tid/dato i leveringsaftaler)",$this->settings->dot_price,array("restrict" => "","percard" => false,"minquantity" => null,"requireon" => "","hideon" => "privatedelivery"));
        }
        */

        // Giftwrap
        if($this->settings->giftwrap_use > 0) {

            if($this->settings->giftwrap_use > 1 && isset($_POST["expireDate"])) {
                $expireDate = $this->getWeekByExpireDate($_POST["expireDate"]);
                if($expireDate != null && $expireDate->isDelivery() && $this->settings->language_code == 1) {
                    $this->settings->giftwrap_use = 1;
                }
            }
            
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->giftwrap_use,"GIFTWRAP","Indpakning (pr kort)",$this->settings->giftwrap_price,array("restrict" => "","percard" => true,"minquantity" => null,"requireon" => "","hideon" => ""));
        }


        // Namelabels
        if($this->settings->namelabels_use > 0) {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->namelabels_use,"NAMELABELS","Navnelabels (pr kort)",$this->settings->namelabels_price,array("restrict" => "","percard" => true,"minquantity" => null,"requireon" => "","hideon" => ""));
        }


        // Invoice fee initial
        if($this->settings->invoiceinitial_use > 0) {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->invoiceinitial_use,"INVOICEFEEINITIAL","Fakturagebyr",$this->settings->invoiceinitial_price,array("restrict" => "","percard" => false,"minquantity" => null,"requireon" => "","hideon" => ""));
        }

        // Invoice fee final
        if($this->settings->invoicefinal_use > 0) {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->invoicefinal_use,"INVOICEFEEFINAL","Fakturagebyr slut",$this->settings->invoicefinal_price,array("restrict" => "","percard" => false,"minquantity" => null,"requireon" => "","hideon" => ""));
        }

        if(trimgf($this->settings->bonus_presents) != "") {
            $this->products[] = new ShopProductLine($this->shop->id,$this->settings->bonus_presents,"BONUSPRESENTS","Bonusgaver",$this->settings->bonus_presents*100,array("restrict" => "","percard" => true,"minquantity" => null,"requireon" => "","hideon" => ""));
        }



    }
    
}
