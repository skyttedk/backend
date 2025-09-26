<?php

namespace GFUnit\lister\rapporter;

class ParameterInputs
{

    /**
     * SHOP SELECTOR
     */

    public static function CSShopGetMultipleID() {

        $shoplist = array();

        $cardshopSettings = \CardshopSettings::find_by_sql("SELECT * FROM `cardshop_settings` ORDER BY `cardshop_settings`.`language_code` ASC, concept_code ASC");

        $selected = self::PostArray("cardshops");
        if(count($selected) == 0) throw new \Exception("No cardshops selected");

        foreach($selected as $shop) {
            if($shop == "all") {
                foreach($cardshopSettings as $settings) {
                    $shoplist[] = $settings->shop_id;
                }
            } else if(substr($shop,0,1) == "c") {
                $country = intval(substr($shop,1));
                foreach($cardshopSettings as $settings) {
                    if($settings->language_code == $country) $shoplist[] = $settings->shop_id;
                }
            } else if(substr($shop,0,1) == "s") {
                $shoplist[] = intval(substr($shop,1));
            }
        }

        // Remove duplicates
        $shoplist = array_unique($shoplist);
        return $shoplist;
    }

    public static function CSShopGetShopID() {
        if(!isset($_POST["cardshop"]) || intval($_POST["cardshop"]) == 0) throw new \Exception("No cardshop selected");
        return intval($_POST["cardshop"]);
    }

    public static function CSShopSelect() {

        ?><div class="reportparam reportparamcardshops">
            Shops:<br><select name="cardshops[]" multiple="multiple" size="35" style="width: 100%;">
                <option value="all">Alle</option>
                <optgroup label="Lande">
                    <option value="c1">Danmark</option>
                    <option value="c4">Norge</option>
                    <option value="c5">Sverige</option>
                </optgroup>
                <?php

                $cardshopSettings = \CardshopSettings::find_by_sql("SELECT * FROM `cardshop_settings` ORDER BY `cardshop_settings`.`language_code` ASC, concept_code ASC");
                $langs = array();
                $langMap = array(1 => "Danmark",4 => "Norge",5 => "Sverige");

                foreach($cardshopSettings as $settings) {
                    if(!isset($langs[$settings->language_code])) $langs[$settings->language_code] = array();
                    $langs[$settings->language_code][] = "<option value='s".$settings->shop_id."'>".$settings->concept_code."</option>";
                }

                foreach($langs as $lang_code => $options) {
                    echo "<optgroup label='".$langMap[$lang_code]."'>";
                    foreach($options as $option) {
                        echo $option;
                    }
                    echo "</optgroup>";
                }

                ?>

            </select>
        </div>
        <div class="reportparam reportparamcardshop">
        Shop:<br><select name="cardshop" style="width: 100%;"><?php

            foreach($cardshopSettings as $settings) {
                if(!isset($langs[$settings->language_code])) $langs[$settings->language_code] = array();
                $langs[$settings->language_code][] = "<option value='s".$settings->shop_id."'>".$settings->concept_code."</option>";
            }

            foreach($langs as $lang_code => $options) {
                echo "<optgroup label='".$langMap[$lang_code]."'>";
                foreach($options as $option) {
                    echo $option;
                }
                echo "</optgroup>";
            }

            ?>
        </select>
        </div><?php
    }



    /*
     * EXPIRE DATE SELECTOR
     */

    public static function CSExpireDatesGet() {

        $expiredatelist = array();

        $cardshopSettings = \ExpireDate::find_by_sql("SELECT * FROM `expire_date` ORDER BY `expire_date`.`expire_date` ASC");

        $selected = self::PostArray("expiredates");
        if(count($selected) == 0) throw new \Exception("No expiredates selected");

        foreach($selected as $expiredate) {
            if($expiredate == "all") {
                foreach($cardshopSettings as $settings) {
                    $expiredatelist[] = $settings->expire_date->format('Y-m-d');
                }
            } else if(trim($expiredate) != "") {
                $expiredatelist[] = intval(substr($expiredate,1));
            }
        }

        // Remove duplicates
        $expiredatelist = array_unique($expiredatelist);
        return $expiredatelist;
    }

    public static function CSExpireDateGet() {
        if(!isset($_POST["expiredate"]) || trim($_POST["expiredate"]) == "") throw new \Exception("No expiredate selected");
        return trim($_POST["expiredate"]);
    }

    public static function CSExpireDateSelect() {

        ?><div class="reportparam reportparamexpiredates">
        Deadline:<br><select name="expiredates[]" multiple="multiple" size="35" style="width: 100%;">
            <option value="all">Alle</option>

            <?php

            $expireDateList = \ExpireDate::find_by_sql("SELECT * FROM `expire_date` ORDER BY `expire_date`.`expire_date` ASC");
            $list = [];
            foreach($expireDateList as $expireDate) {

                $list[] = "<option value='".$expireDate->expire_date->format('Y-m-d')."'>".$expireDate->display_date." (uge ".$expireDate->week_no.")</option>";
            }

            foreach($list as $options) {


                    echo $options;


            }

            ?>

        </select>
        </div>
        <div class="reportparam reportparamexpiredate">
        Deadline:<br><select name="expiredate" style="width: 100%;"><?php

            $list = [];
            foreach($expireDateList as $expireDate) {
                $list[] = "<option value='".$expireDate->expire_date->format('Y-m-d')."'>".$expireDate->display_date." (uge ".$expireDate->week_no.")</option>";
            }

            foreach($list as $options) {

                    echo $options;

            }

            ?>
        </select>
        </div><?php
    }





    /*** POST DATA ***/

    /**
     * POST DATA
     */


    public static function Post($name)
    {
        return $_POST[$name];
    }

    public static function hasPost($name)
    {
        return isset($_POST[$name]);
    }

    public static function PostString($name,$trim=true)
    {
        if(!self::hasPost($name)) return "";
        $val = is_string($_POST[$name]) ? $_POST[$name] : "";
        return ($trim ? trim($val) : $val);
    }

    public static function PostDate($name)
    {
        return std_util_date::format(self::PostString($name));
    }

    public static function PostInt($name)
    {
        return intval($_POST[$name]);
    }

    public static function PostIntDefault($name,$default=0)
    {
        return !isset($_POST[$name]) ? $default : intval($_POST[$name]);
    }

    public static function PostFloat($name)
    {
        return floatval($_POST[$name]);
    }

    public static function PostDKFloat($name)
    {
        return std_util_number::DK2US($_POST[$name]);
    }

    public static function PostArray($name)
    {
        $data = $_POST[$name];
        if(!is_array($data)) return array();
        else return $data;
    }

    public static function PostBool($name)
    {
        if(!self::hasPost($name)) return false;
        return in_array($_POST[$name],array(1,true,"on","true","1"));
    }

    public static function PostIDList($name,$positiveOnly=true,$seperator=";")
    {
        $data = self::Post($name);
        $parts = explode($seperator,$data);
        $newparts = array();
        if(count($parts) == 0) return array();
        foreach($parts as $part)
        {
            if($positiveOnly == false || ($positiveOnly && intval($part) > 0))
            {
                $newparts[] = intval($part);
            }
        }
        return $newparts;
    }

    public static function IsPostJson($name)
    {
        json_decode(self::PostString($name));
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function PostJson($name)
    {
        return json_decode(self::PostString($name),true);
    }

}