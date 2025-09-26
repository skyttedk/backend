<?php

namespace GFUnit\cardshop\pluklister;

class GaveCheck
{

    public function run($lang)
    {

        // Present data
        $sql = "SELECT count(`order`.id) as count, `order`.shop_id, `order`.present_model_id, present_model.model_name, present_model.model_no, present_model.model_present_no, present_model.fullalias, present.id
        FROM `order`, present_model, present
        where `order`.present_model_id = present_model.model_id && present_model.language_id = 1 && `order`.shop_id in (select shop_id from cardshop_settings) && `order`.present_id = present.id
        group by `order`.shop_id, `order`.present_model_id order by present.shop_id asc, present_model.fullalias asc";

        $presentdata = \PresentModel::find_by_sql($sql);

        $shopPresentMap = array();
        foreach($presentdata as $present) {
            if(!isset($shopPresentMap[$present->shop_id])) $shopPresentMap[$present->shop_id] = array();
            $shopPresentMap[$present->shop_id][] = $present;
        }

        // Shops
        $langCri = "language_code > 0";
        if(intval($lang) > 0) $langCri = "language_code = ".intval($lang);
        $cardshops = \CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings where ".$langCri." order by language_code asc");

        ?><table><?php

        foreach($cardshops as $shop) {
            $this->processShop($shop,isset($shopPresentMap[$shop->shop_id]) ? $shopPresentMap[$shop->shop_id] : array());
        }

        ?></table><?php

    }

    private function processShop($shop,$shopList)
    {

        ?><tr>
        <td colspan="4" style="border-bottom: 2px solid black;"><div style="padding: 10px; font-size: 22px; font-weight: bold;"><?php echo $shop->concept_code; ?></div></td>
    </tr><tr>
        <td>Alias</td>
        <td>Navn</td>
        <td>Model</td>
        <td>Varenr</td>
        <td>Sampak varer</td>
        <td>Antal valg</td>
        <td>Nav status</td>
    </tr><?php

        foreach($shopList as $present) {

            $varenrData = $this->getVarenrData($shop->language_code,$present->model_present_no);
            $color = "";


            ?><tr style="background: <?php echo $varenrData[2]; ?>;">
                <td><?php echo $present->fullalias; ?></td>
                <td><?php echo utf8_decode($present->model_name); ?></td>
                <td><?php echo utf8_decode($present->model_no); ?></td>
                <td><?php echo $present->model_present_no; ?></td>
                <td><?php echo implode(", ",$this->getSampakVarenrList($shop->language_code,$present->model_present_no)); ?></td>
                <td><?php echo $present->count; ?></td>
                <td><?php echo $varenrData[0].": ".$varenrData[1]; ?></td>
                <td><?php echo $present->present_model_id; ?></td>
            </tr><?php
            
        }

    }

    protected function getVarenrData($languageCode,$varenr) {

        $navitems = \NavisionItem::find('all',array("conditions" => array("no" => $varenr,"language_id" => $languageCode)));

        if($navitems == null || countgf($navitems) == 0) {
            return array(0,"Findes ikke i nav","#FF0000");
        }

        foreach($navitems as $navitem) {
            if($navitem->deleted == null && $navitem->blocked == 0) return array(1,"OK","none");
        }

        foreach($navitems as $navitem) {
            if($navitem->deleted == null && $navitem->blocked == 1) return array(2,"Blokkeret","#FFAA00");
        }

        return array(3,"Slettet","#FF00AA");

    }

    protected function getSampakVarenrList($languageCode,$varenr) {

        $list = array();

        $languageid = intval($languageCode);
        if(substr(trimgf(strtolower($varenr)),0,3) == "sam" && $languageid == 4) {
            $languageid = 1;
        }

        $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = ".$languageid." && parent_item_no = '".$varenr."' && deleted is null");
        foreach($navbomItemList as $item) {
            if(!in_array($item->no,$list)) {
                $list[] = ($item->quantity_per > 1 ? "(".$item->quantity_per." ".$item->unit_of_measure_code.")" : "").$item->no;
            }
        }

        return $list;
    }

}