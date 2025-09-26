<?php

// Model MagentoStockTotal
// Date created  Wed, 11 Jan 2017 14:14:58 +0100
// Created by Bitworks

class MagentoStockTotal extends ActiveRecord\Model {

    static $table_name = "magento_stock_total";
    static $primary_key = "id";
    static $before_save = array('onBeforeSave');
    static $after_save = array('onAfterSave');
    static $before_create = array('onBeforeCreate');
    static $after_create = array('onAfterCreate');
    static $before_update = array('onBeforeUpdate');
    static $after_update = array('onAfterUpdate');
    static $before_destroy = array('onBeforeDestroy'); // virker ikke
    static $after_destroy = array('onAfterDestroy');

    // Trigger functions
    function onBeforeSave() {
    }

    function onAfterSave() {
    }

    function onBeforeCreate() {
        $this->validateFields();
    }

    function onAfterCreate() {
    }

    function onBeforeUpdate() {
        $this->validateFields();
    }

    function onAfterUpdate() {
    }

    function onBeforeDestroy() {
    }

    function onAfterDestroy() {
    }

    function validateFields() {
    }
    //---------------------------------------------------------------------------------------
    // Static CRUD Methods
    //---------------------------------------------------------------------------------------

    static public function createMagentoStockTotal($data) {
        $magentoStockTotal = new MagentoStockTotal($data);
        $magentoStockTotal->save();
        return ($magentoStockTotal);
    }

    static public function readMagentoStockTotal($id) {
        $magentoStockTotal = MagentoStockTotal::find($id);
        return ($magentoStockTotal);
    }

    static public function updateMagentoStockTotal($data) {
        $magentoStockTotal = MagentoStockTotal::find($data['id']);
        $magentoStockTotal->update_attributes($data);
        $magentoStockTotal->save();
        return ($magentoStockTotal);
    }

    static public function deleteMagentoStockTotal($id, $realDelete = false) {
        $magentoStockTotal = MagentoStockTotal::find($id);
        if ($realDelete) {
            $magentoStockTotal->delete();
        }
        else { //Soft delete
            $magentoStockTotal->deleted = 1;
            $magentoStockTotal->save();
        }
    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

    static public function getMaxAvailableStockSAM($samno)
    {
        $result = [];

        // quantity_per
        // no



        $list = NavisionBomitem::find('all', array(
            'conditions' => array(
                'parent_item_no = ? AND language_id = 1 AND `deleted` IS NULL',
                $samno
            )

        ));
        if(!$list) return false;
        foreach ($list as $item){
            $stock =  MagentoStockTotal::find_by_itemno($item->no);

            if($stock){
                if ($item->quantity_per != 0) {
                    $avaliable = ceil($stock->available / $item->quantity_per);
                } else {
                    $avaliable = 0;
                }
                $result[] = $avaliable;

            } else {
                return false;
            }

            if($samno =="sam4463") {
            //    print_r($result);
            }
        }
        if (!empty($result)) {
            return min($result);
        } else {
            return false;
        }
    }


}
