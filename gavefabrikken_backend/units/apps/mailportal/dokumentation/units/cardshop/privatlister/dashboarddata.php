<?php

namespace GFUnit\cardshop\privatlister;

class DashboardData {

    private $pdModel;

    public function __construct() {

        $this->pdModel = new \GFUnit\navision\syncprivatedelivery\PrivateDeliverySync();

    }


    public function getShopLangOptions()
    {

        $shophtml = "";
        $langcodes = array();
        $cslist = \CardshopSettings::find_by_sql("select * from cardshop_settings where privatedelivery_use > 0 && private_close is not null order by language_code asc, concept_code asc");
        foreach ($cslist as $csrow) {
            if(!in_array($csrow->language_code,$langcodes)) $langcodes[] = $csrow->language_code;
            $shophtml .= "<option value='shop_".$csrow->shop_id."'>".$csrow->concept_code."</option>";
        }

        $html = "<option value=''>Alle</option>";
        $html .= "<optgroup label='Sprog'>";
        foreach($langcodes as $langcode) {
            $html .= "<option value='lang_".$langcode."'>".\GFCommon\Model\Navision\CountryHelper::countryToCode($langcode)." (".($this->pdModel->isLangActive($langcode) ? "aktiv" : "deaktiveret").")</option>";
        }
        $html .= "</optgroup>";
        $html .= "<optgroup label='Shops'>";
        $html .= $shophtml;
        $html .= "</optgroup>";
        return $html;
    }

    public function getShopLangCriteria($type,$id) {
        if($type == "shop") {
            return "shop_user.shop_id = ".intval($id);
        } else if($type == "lang") {
            $cslist = \CardshopSettings::find_by_sql("select shop_id from cardshop_settings where language_code = ".intval($id));
            $shopid = array();
            foreach($cslist as $csrow) $shopid[] = $csrow->shop_id;
            if(count($shopid) == 0) return "shop_user.shop_id = -1000";
            return "shop_user.shop_id in (".implode(",",$shopid).")";
        } else {
            return "shop_user.shop_id > 0";
        }
    }

    public function getPrivateDeliveryGroups($type,$id)
    {
        $sql = "SELECT count(`order`.`id`) as pdcount, shop_user.delivery_state FROM `order` INNER JOIN `shop_user` ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ".$this->pdModel->getPrivateDeliveryCriteria()." && ".$this->getShopLangCriteria($type,$id)."
        GROUP BY shop_user.delivery_state ORDER BY shop_user.delivery_state";
        return \ShopUser::find_by_sql($sql);
    }

    public function getPrivateDeliveryNoChoiceCount($type,$id) {

        $sql = "SELECT count(id) as pdcount from shop_user where is_delivery = 1 && blocked = 0 && shutdown = 0 && id not in (select shopuser_id from `order`) && ".$this->getShopLangCriteria($type,$id)."";
        $res = \ShopUser::find_by_sql($sql);
        return $res[0]->pdcount;

    }

    public function getPrivateDeliveryData($type,$id,$state) {


        // Get shop users
        $sql = "SELECT shop_user.*, `order`.id as order_id, `order`.order_no, `order`.present_id, `order`.present_model_id, `order`.order_timestamp FROM `order` INNER JOIN `shop_user` ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ".$this->pdModel->getPrivateDeliveryCriteria()." && shop_user.delivery_state = ".intval($state)." && ".$this->getShopLangCriteria($type,$id);
        $shopUserList = \ShopUser::find_by_sql($sql);

        $retData = array();

        foreach($shopUserList as $shopUser) {

            $row = array(
                "shopuser" => $shopUser,
                "companyorder" => \CompanyOrder::find($shopUser->company_order_id),
                "userdata" => $this->pdModel->getUserData($shopUser->id,$shopUser->shop_id),
                "presentmodel" => \PresentModel::find("first",array("conditions" => array("model_id" => $shopUser->present_model_id, "present_id" => $shopUser->present_id, "language_id" => 1)))
            );

            $retData[] = $row;
        }

        return $retData;


    }


}