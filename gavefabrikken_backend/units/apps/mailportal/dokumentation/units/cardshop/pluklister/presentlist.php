<?php

namespace GFUnit\cardshop\pluklister;

class PresentList extends PlukReport
{


    public function run() {


        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="presentlist-' . $this->shop->name . '.csv"');

        // Get presents
        $presentlist = \Present::find_by_sql("SELECT * FROM present where shop_id = " . intval($this->shopid) . " && (id not in (SELECT present_id FROM shop_present WHERE shop_id = " . intval($this->shopid) . " &&  (is_deleted = 1 || active = 0)) || alias > 0) ORDER BY alias");

        // Load models
        $presentmodellist = \PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE language_id = " . $this->shoptolang($this->shopid) . " && present_id IN (SELECT id FROM `present` WHERE `shop_id` = " . intval($this->shopid) . ") ORDER BY `present_model`.`aliasletter` ASC");
        $presentmodelmap = array();

        foreach ($presentmodellist as $model) {
            if (!isset($presentmodelmap[$model->present_id])) {
                $presentmodelmap[$model->present_id] = array();
            }

            $presentmodelmap[$model->present_id][] = $model;
        }

        echo "Gavenr;Varenr;Varenr sampak;Gave navn;Model navn;EAN No;Internt id;Note;\n";

        foreach ($presentlist as $present) {
            if (count($presentmodelmap[$present->id]) > 0) {
                foreach ($presentmodelmap[$present->id] as $model) {
                    $isActive = true;
                    $dkModel = null;

                    if ($model->language_id == 1) {
                        $dkModel = $model;
                        $isActive = $model->active == 0;
                    } else {
                        $dkModel = \PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id = " . intval($model->model_id) . " && language_id = 1");
                        $dkModel = $dkModel[0];
                        $isActive = $dkModel->active == 0;
                    }

                    if ($isActive || trimgf($dkModel->fullalias) != "") {
                        echo utf8_decode($this->fullalias($this->shopid, $model->fullalias) .";". str_replace(";", ",", $dkModel->model_present_no) . ";" .implode(",",$this->getSampakVarenrList($dkModel->model_present_no)). ";" . $model->model_name . ";" . $model->model_no . ";EAN:" . implode(", ",$this->getEANNo($dkModel->model_present_no)) . ";".$model->present_id.";" . ($model->active == 1 ? "Deaktiveret" : "").";".($this->getNavisionStatus($dkModel->model_present_no) ) . "\n");
                    }
                }
            } else {
                echo "Gaven: " . $present->nav_name . " har ingen modeller!";
            }
        }
        
    }

    protected function getNavisionStatus($itemno) {

        if(trimgf($itemno) == "") {
            return "Mangler varenr";
        }

        if($this->shopSettings != null) {
        $languageid = intval($this->shopSettings->language_code);
        if(substr(trimgf(strtolower($itemno)),0,3) == "sam" && $languageid == 4) {
            $languageid = 1;
        }
        } else {
            $languageid = 1;
        }

        if($languageid == 5) {
            $languageid = 1;
        }

        $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '".$itemno."' && language_id = ".$languageid." && deleted is null");
        if(countgf($navisionItem) == 0) {
            return "Mangler i NAV";
        }

        if($navisionItem[0]->blocked == 1) {
            return "Blokkeret i nav";
        }

        return "";

    }

    protected function getEANNo($varenr) {

        $list = array();

        if($this->shopSettings != null) {
            $languageid = intval($this->shopSettings->language_code);
            if(substr(trimgf(strtolower($varenr)),0,3) == "sam" && $languageid == 4) {
                $languageid = 1;
            }
        } else $languageid = 1;

        $itemList = array($varenr);

        // Load nav bom items
        $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = ".$languageid." && parent_item_no = '".$varenr."' && deleted is null");
        if(count($navbomItemList) > 0) {
            $itemList = array();
            foreach($navbomItemList as $item) {
                if(!in_array($item->no,$itemList)) {
                    $itemList[] = $item->no;
                }
            }
        }

        foreach($itemList as $itemNo) {

            $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '".$itemNo."' && deleted is null");
            foreach($navisionItem as $navItem) {
                if(trimgf($navItem->crossreference_no) != "") {
                    $list[] = $navItem->crossreference_no;
                    break;
                }
            }

        }

        return $list;

    }

}