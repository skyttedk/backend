<?php

namespace GFUnit\cardshop\settings;
use ActiveRecord\DateTime;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function matrix()
    {
        $this->view("matrix");
    }

    public function shoplist($language_code=0)
    {
        $list = $this->getShopSettings($language_code);
        $outlist = array();

        foreach($list as $row) {
            $outlist[] = $this->toArray($row);
        }

        echo json_encode($outlist);
    }

    public function shopmap($language_code=0)
    {
        $list = $this->getShopSettings($language_code);
        $outlist = array();

        foreach($list as $row) {
            $outlist[$row->shop_id] = $this->toArray($row);
        }

        echo json_encode($outlist);
    }

    public function shopsinsameconcept($shopid)
    {

        $shopsettigns = \CardshopSettings::find('all',array("conditions" => array("shop_id" => intval($shopid))));
        if(count($shopsettigns) == 0) throw new \Exception("Cand find cardshop with id ".$shopid);

        $list = $this->getShopSettings(null,$shopsettigns[0]->concept_parent);
        $outlist = array();

        foreach($list as $row) {
            $outlist[] = $this->toArray($row);
        }

        echo json_encode($outlist);


    }


    private function toArray($settings) {
        $item = array();
        foreach($settings->attributes as $attr_name => $attr_val) {
            if($attr_val instanceof DateTime) $val = $attr_val->format("d-m-Y H:i:s");
            else $val = $attr_val;
            $item[$attr_name] = utf8_encode($val);
        }
        return $item;
    }

    private function getShopSettings($language_id=null,$concept_parent=null)
    {

        $critria = "";

        if(intval($language_id) > 0) {
            $critria .= " && cardshop_settings.language_code = ".intval($language_id);
        }

        if($concept_parent != null && trimgf($concept_parent) != "") {
            $critria .= " && concept_parent = '".$concept_parent."'";
        }

        return \CardshopSettings::find_by_sql("SELECT cardshop_settings.*, shop.name FROM cardshop_settings, shop where cardshop_settings.shop_id = shop.id ".$critria." order by card_price asc");

        //$conditions = array("shop_id > 0");
        //return \CardshopSettings::find("all",array("conditions" => $conditions,"order" => "concept_parent asc, concept_code asc"));
    }

}