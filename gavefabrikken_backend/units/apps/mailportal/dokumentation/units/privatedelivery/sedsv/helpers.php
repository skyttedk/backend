<?php

namespace GFUnit\privatedelivery\sedsv;


class Helpers
{

    private $lagerLines;
    private $lagerMap;
    private $shopidlist;
    private $shopmap;
    private $shopItemNoList;
    private $uniqueItems;
    private $totalItemNoList;
    private $navItemMap;
    private $navBomMap; // Key is itemno value is array with bom items
    private $itemInBom;  // Key is bom no and value is array with item nos
    private $bomItemsMap;
    private $shipmentCompleted;
    private $shipmentWaiting;
    private $shipmentOther;
    private $otherMap;

    private $selected7DaysMap = array();
    private $selected14DaysMap = array();

    private $problems = [];

    private $lockshopid = 0;

    public function __construct($lockshopid=0) {
        $this->lockshopid = intval($lockshopid);
        $this->loadData();
    }


    /**
     * GETTERS
     */
    
    public function getShops() {
        return $this->shopmap;
    }

    public function markUsed($itemno) {

    }

    public function isUsed($itemno) {

    }

    public function isSampakVarenr($itemno) {
        return isset($this->itemInBom[$itemno]);
    }

    public function getVarenrList() {
        return $this->totalItemNoList;
    }
    
    public function getItemsInSampak($itemno) {
        return isset($this->itemInBom[$itemno]) ? $this->itemInBom[$itemno] : array(); 
    }

    public function getNavVare($varenr) {
        return $this->navItemMap[$this->normalizeVarene($varenr)];
    }

    public function getBomItemQuantity($bomItemNo,$subItemNo) {
        return $this->bomItemsMap[$this->normalizeVarene($bomItemNo."-".$subItemNo)]->quantity_per;
    }

    public function getDSVLagerQuantity($varenr,$includingSinceLast=true) {
        
        return (isset($this->lagerMap[$this->normalizeVarene($varenr)]) ? $this->lagerMap[$this->normalizeVarene($varenr)] : 0) - ($includingSinceLast ? $this->getSentSinceLastLagerUpdate($varenr) : 0);
    }

    public function getLast7DaysSelected($varenr) {
        return isset($this->selected7DaysMap[$this->normalizeVarene($varenr)]) ? $this->selected7DaysMap[$this->normalizeVarene($varenr)] : 0;
    }

    public function getLast14DaysSelected($varenr) {
        return isset($this->selected14DaysMap[$this->normalizeVarene($varenr)]) ? $this->selected14DaysMap[$this->normalizeVarene($varenr)] : 0;
    }

    public function getSentSinceLastLagerUpdate($varenr) {
        $sinceLager = DSVLager::getLastUpdateDate();
        $count = 0;
        foreach($this->shipmentCompleted as $s) {
            if(trim(strtolower($s->itemno)) == trim(strtolower($varenr)) && $s->shipment_sync_date > $sinceLager) {
                $count++;
            }
        }
        
        return $count;
    }

    private $newShipments = null;

    public function getSentSinceLastLagerRapport($varenr) {

        if($this->newShipments == null) {
            $this->newShipments = $this->getAllShipmentsCreatedAfter(DSVLager::getLastGFRapport()->format('Y-m-d H:i:s'));
        }

        $count = 0;
        foreach($this->newShipments as $s) {
            if(trim(strtolower($s->itemno)) == trim(strtolower($varenr))) {
                $count++;
            }
        }

        return $count;

    }

    public function getForecast($shopid,$varenr,$forceNumber=false) {

        $this->loadForecastData();

        if(isset($this->forecastData[$shopid]) && isset($this->forecastData[$shopid][trim(strtolower($varenr))])) {
            return $this->forecastData[$shopid][trim(strtolower($varenr))];

        }

        if($forceNumber) {
            return 0;
        } else {
            return "Ikke nok data";
        }

    }

    public function getForecastAll($varenr,$forceNumber=false) {

        $this->loadForecastData();

        $hasData = false;
        $count = 0;
        foreach($this->shopidlist as $shopid) {
            if(isset($this->forecastData[$shopid]) && isset($this->forecastData[$shopid][trim(strtolower($varenr))])) {
                $count += $this->forecastData[$shopid][trim(strtolower($varenr))];
                $hasData = true;
            }
        }

        if($forceNumber) return $count;
        else return "Ikke nok data";

    }


    /**
     * ORDER PIPELINE
     */
    
    private $orderPipeline = null;
    
    public function getOrderPipelineValue($varenr) {
        
        if($this->orderPipeline == null) {
            $this->orderPipeline = DSVLager::getOrderPipeline();
        }
        
        if(isset($this->orderPipeline[strtolower(trim($varenr))])) {
            return $this->orderPipeline[strtolower(trim($varenr))];
        }
        return 0;
        
    }

    /**
     * LOADERS
     */

    private $forecastData = null;

    private function loadForecastData() {

        if($this->forecastData === null) {

            $this->forecastData = array();
            $sql = "SELECT * FROM `rm_shop_data` where shop_id  in (".implode(",",$this->shopidlist).") && updated_at > DATE_ADD(NOW(), INTERVAL -4 DAY) ORDER BY `rm_shop_data`.`updated_at` DESC;";
            $forecastData = \Dbsqli::getSql2($sql);

            if(is_array($forecastData) && countgf($forecastData) > 0) {
                foreach ($forecastData as $forecast) {

                    if(!isset($this->forecastData[$forecast["shop_id"]])) {
                        $this->forecastData[$forecast["shop_id"]] = array();
                    }

                    if(!isset($this->forecastData[$forecast["shop_id"]][trim(strtolower($forecast["item_nr"]))])) {
                        $this->forecastData[$forecast["shop_id"]][trim(strtolower($forecast["item_nr"]))] = $forecast["forecast"];
                    }


                }
            }

  //          echo $sql;

         //   var_dump($this->forecastData);

        }

        return 0;

    }


    private function checkDateCount($shipmentlist)
    {
        foreach($shipmentlist as $shipment) {

            if($shipment->created_date->getTimestamp() > time()-60*60*24*7) {
                if(!isset($this->selected7DaysMap[$shipment->itemno])) $this->selected7DaysMap[$shipment->itemno] = 0;
                $this->selected7DaysMap[$shipment->itemno]++;
            }

            if($shipment->created_date->getTimestamp() > time()-60*60*24*14) {
                if(!isset($this->selected14DaysMap[$shipment->itemno])) $this->selected14DaysMap[$shipment->itemno] = 0;
                $this->selected14DaysMap[$shipment->itemno]++;
            }

        }
    }

    private function loadData() {

        // Load lager items
        $this->lagerLines = DSVLager::getLagerLines();

        // Load lager map
        $this->lagerMap = DSVLager::getLagerQuantityMap();

        $varenrList = array_keys($this->lagerMap);

        // Load shops
        $shopList = \CardshopSettings::find_by_sql("select * from cardshop_settings where language_code = 5 ".($this->lockshopid > 0 ? "&& shop_id = ".$this->lockshopid : ""));
        $this->shopidlist = array();
        $this->shopmap = \CardshopSettings::getShopIDMap();
        foreach($shopList as $shop) {
            $this->shopidlist[] = $shop->shop_id;
        }

        // Load shop items
        $this->shopItemNoList = $this->loadPresentDataFromShops($this->shopidlist,true);
        foreach ($this->shopItemNoList as $varenr) {
            if(!in_array(trim(strtolower($varenr)),$varenrList)) {
                $varenrList[] = trim(strtolower($varenr));
            }
        }

        // Load privatedelivery completed
        $this->shipmentCompleted = $this->getDSVShipmentsHandled();

        // Load privatedelivery waiting
        $this->shipmentWaiting = $this->getDSVShipmentsReady();

        // Load privatedelivery other
        $this->shipmentOther = $this->getDSVShipmentOther();

        $this->checkDateCount($this->shipmentCompleted);
        $this->checkDateCount($this->shipmentWaiting);
        $this->checkDateCount($this->shipmentOther);

        // Add shipments to list
        foreach($this->shipmentCompleted as $shipment) {
            if(!in_array(trim(strtolower($shipment->itemno)),$varenrList)) {
                $varenrList[] = trim(strtolower($shipment->itemno));
            }
        }
        foreach($this->shipmentWaiting as $shipment) {
            if(!in_array(trim(strtolower($shipment->itemno)),$varenrList)) {
                $varenrList[] = trim(strtolower($shipment->itemno));
            }
        }
        foreach($this->shipmentOther as $shipment) {
            if(!in_array(trim(strtolower($shipment->itemno)),$varenrList)) {
                $varenrList[] = trim(strtolower($shipment->itemno));
            }
        }

        $this->totalItemNoList = $varenrList;

        // Find bom items
        $this->findUniqueItems($varenrList);

    }

    public function getShipmentsHandledCount($varenr) {
        $count = 0;
        foreach($this->shipmentCompleted as $s) {
            if(trim(strtolower($s->itemno)) == trim(strtolower($varenr))) {
                $count++;
            }
        }
        return $count;
    }

    public function getShipmentsWaitingCount($varenr) {
        $count = 0;
        foreach($this->shipmentWaiting as $s) {
            if(trim(strtolower($s->itemno)) == trim(strtolower($varenr))) {
                $count++;
            }
        }
        return $count;
    }
    
    
    
    private $dsvWaitMap = null;
    
    public function getShipmentsWaitingAtDSVCount($varenr) {
    
        if($this->dsvWaitMap == null) {
            $this->dsvWaitMap = DSVLager::getOrderQuantityMap();
        }
        
        if(isset($this->dsvWaitMap[trim(strtolower($varenr))])) {
            return $this->dsvWaitMap[trim(strtolower($varenr))];
        }

        return 0;
        
    }

    public function getDSVShipmentsReady() {

        return \Shipment::find_by_sql("SELECT *, shipment.id as shipment_id FROM `shipment`, `order` where `order`.id = shipment.to_certificate_no && shipment_type = 'privatedelivery' && handler = 'mydsv' && shipment_state = 1 ".($this->lockshopid > 0 ? " && shop_id in (".implode(",",$this->shopidlist).")" : "")." ORDER BY `order`.`order_timestamp` ASC");
    }


    public function getDSVShipmentsReadyOrderSorted() {
        return \Shipment::find_by_sql("SELECT shipment.*, `order`.order_no, `order`.`order_timestamp` FROM `shipment`, `order` where `order`.id = shipment.to_certificate_no && shipment_type = 'privatedelivery' && handler = 'mydsv' && shipment_state = 1  ".($this->lockshopid > 0 ? " && shop_id in (".implode(",",$this->shopidlist).")" : "")." ORDER BY `order`.`order_timestamp` ASC");
    }


    private function getDSVShipmentsHandled() {
        return \Shipment::find_by_sql("SELECT * FROM `shipment`, `order` where `order`.id = shipment.to_certificate_no && shipment_type = 'privatedelivery' && handler = 'mydsv' && shipment_state = 2 ".($this->lockshopid > 0 ? " && shop_id in (".implode(",",$this->shopidlist).")" : "")."");
    }

    private function getDSVShipmentOther() {
        return \Shipment::find_by_sql("SELECT * FROM `shipment`, `order` where `order`.id = shipment.to_certificate_no && shipment_type = 'privatedelivery' && handler = 'mydsv' && shipment_state NOT IN (1,2) ".($this->lockshopid > 0 ? " && shop_id in (".implode(",",$this->shopidlist).")" : "")."");
    }

    private function getAllShipmentsCreatedAfter($date) {

        return \Shipment::find_by_sql("SELECT * FROM `shipment`, `order` where shipment.created_date >= '".$date."' && `order`.id = shipment.to_certificate_no && shipment_type = 'privatedelivery' && handler = 'mydsv' ".($this->lockshopid > 0 ? " && shop_id in (".implode(",",$this->shopidlist).")" : "")."");
    }

    /**
     * UNPACK NAV ITEM NOS FROM ITEMS IN SHOP
     */

    private function findUniqueItems($itemNoList) {

        $this->navItemMap = array();
        $this->navBomMap = array();
        $this->itemInBom = array();
        $this->bomItemsMap = array();

        foreach($itemNoList as $itemNo) {

            // Load item from nav
            $itemData = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$itemNo."' && deleted is null");
            $bomItemList = \NavisionBomItem::find_by_sql("select * from navision_bomitem where language_id = 1 && parent_item_no like '".$itemNo."' && deleted is null");

            // Add item data to list
            if(countgf($itemData) > 0) {
                $this->navItemMap[$this->normalizeVarene($itemNo)] = $itemData[0];
                if(countgf($bomItemList) == 0) {
                    $this->addProblem($itemNo,"Could not find nav item no in items or bom items");
                }
            }

            // Has bom items
            if(countgf($bomItemList) > 0) {
                foreach($bomItemList as $bomItem) {

                    $this->bomItemsMap[$this->normalizeVarene($itemNo."-".$bomItem->no)] = $bomItem;

                    if(isset($this->navBomMap[$bomItem->no])) {
                        $this->navBomMap[$bomItem->no] = array();
                    }

                    $this->navBomMap[$bomItem->no][] = $itemNo;

                    if(!isset($this->itemInBom[$itemNo])) {
                        $this->itemInBom[$itemNo] = array();
                    }
                    $this->itemInBom[$itemNo][] = $bomItem->no;

                    $itemSubData = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '" . $bomItem->no . "' && deleted is null");
                    if (countgf($itemSubData) > 0) {
                        $this->navItemMap[$this->normalizeVarene($itemSubData[0]->no)] = $itemSubData[0];
                    } else {
                        $this->addProblem("Could not find item " . $bomItem->no . " in bom item " . $bomItem->parent_item_no);
                    }
                }
            }

        }


    }

    private function addProblem($varenr,$text) {
        if(!isset($this->problems[$varenr])) {
            $this->problems[$varenr] = array();
        }
        $this->problems[$varenr][] = $text;
    }
    /**
     * Load present data from shop
     */

    public function inShopID($varenr,$sampaklist,$shopid) {

/*
        if($varenr == "210193") {
            echo "<pre>";
            print_r($varenr);
            print_r($sampaklist);
            print_r($this->shopIDVarenr);
            echo "</pre>";
            exit();
        }
*/
        if(count($sampaklist) > 0) {
            foreach($sampaklist as $sampaknr) {
                if(isset($this->shopIDVarenr[$shopid]) && in_array(strtolower(trim($sampaknr)),$this->shopIDVarenr[$shopid])) {
                    return true;
                }
            }

        }

        if(isset($this->shopIDVarenr[$shopid]) && in_array(strtolower(trim($varenr)),$this->shopIDVarenr[$shopid])) {
            return true;
        }

        return false;
    }
    
    private $shopIDVarenr = array();

    public function shopIDListToNameString($idlist) {
        $names = array();
        foreach ($idlist as $shopid) {
            if(isset($this->shopmap[$shopid])) {
                $names[] = $this->shopmap[$shopid]->concept_code;
            }
        }
        return implode(", ",$names);
    }

    private function loadPresentDataFromShops($shopidlist,$itemNoOnly=false) {

        if(countgf($shopidlist) == 0) return array();
        $sql = "SELECT present.name, present.nav_name, present.internal_name, present_model.model_id, present_model.present_id, present_model.model_present_no, present_model.model_name, present_model.model_no, present_model.fullalias, present.shop_id FROM `present_model`, present where present.id = present_model.present_id && present_model.language_id = 1 && present.shop_id in (".implode(",",$shopidlist).") && present_model.fullalias != ''";
        $sql .= " && present_model.model_id in (SELECT present_model_id from `order` where shop_id in (".implode(",",$shopidlist)."))";

        $presentmodellist = \PresentModel::find_by_sql($sql);


        if($itemNoOnly == false) {
            return $presentmodellist;
        }

        $modelNoList = array();
        foreach($presentmodellist as $presentmodel) {

            $itemNo = $presentmodel->model_present_no;
            if(!in_array($itemNo,$modelNoList)) {
                $modelNoList[] = $itemNo;
            }

            if(!isset($this->shopIDVarenr[$presentmodel->shop_id])) {
                $this->shopIDVarenr[$presentmodel->shop_id] = array();
            }

            if(!in_array($itemNo,$this->shopIDVarenr[$presentmodel->shop_id])) {
                $this->shopIDVarenr[$presentmodel->shop_id][] = trim(strtolower($itemNo));
            }

        }


        // Load from waiting in shipments
        $sql = "SELECT shipment.itemno, company_order.shop_id FROM shipment, company_order WHERE shipment.companyorder_id = company_order.id && shipment.`handler` LIKE 'mydsv' && shipment.shipment_state = 1";
        $shipmentList = \Shipment::find_by_sql($sql);

        foreach($shipmentList as $shipment) {

            $itemNo = $shipment->itemno;
            if(!in_array($itemNo,$modelNoList)) {
                $modelNoList[] = $itemNo;
            }

            if(!isset($this->shopIDVarenr[$shipment->shop_id])) {
                $this->shopIDVarenr[$shipment->shop_id] = array();
            }

            if(!in_array($itemNo,$this->shopIDVarenr[$shipment->shop_id])) {
                $this->shopIDVarenr[$shipment->shop_id][] = trim(strtolower($itemNo));
            }

        }


        return $modelNoList;

    }

    private function normalizeVarene($varenr) {
        return trim(strtolower($varenr));
    }

}