<?php

namespace GFUnit\vlager\utils;
use GFBiz\units\UnitController;

class VLagerCounter
{

    private $vlagerid;

    private $vlager;

    private $lockShopID;

    public function __construct($vlagerid,$lockShopID=0)
    {

        $this->lockShopID = $lockShopID;
        $this->vlagerid = $vlagerid;
        $this->vlager = \VLager::find(intvalgf($this->vlagerid));
        $this->loadData();

    }

    public function getPrimaryItems() {

        return $this->itemMap;

    }

    public function getVLagerObj() {
        return $this->vlager;
    }

    public function getVLagerID() {
        return $this->vlagerid;
    }
    
    public function isBOMItem($itemno) {
        return isset($this->bomMap[$this->normalizeItemNo($itemno)]);
    }

    public function getChildItems($itemno) {
        return $this->bomMap[$this->normalizeItemNo($itemno)] ?? null;
    }

    public function getAvailable($itemno) {
        return $this->vlagerItemMap[$this->normalizeItemNo($itemno)] ?? 0;
    }

    public function getRealItemNo($itemno) {
        return $this->realItemNoMap[$this->normalizeItemNo($itemno)] ?? $itemno;
    }


    private $unknownNames = [];

    private function getUnknownName($itemno) {

        // Check if isset in unknownNames
        if(isset($this->unknownNames[$itemno])) {
            return $this->unknownNames[$itemno];
        }

        // Load from db
        $items = \NavisionItem::find_by_sql("SELECT * FROM navision_item WHERE no = '".$itemno."' order by language_id ASC, blocked ASC, deleted ASC");
        if(count($items) == 0) {
            $this->unknownNames[$itemno] = "Unknown item (".$itemno.")";
            return $this->unknownNames[$itemno];
        }

        // Get first item and set name to ->description
        $item = $items[0];
        $this->unknownNames[$itemno] = $item->description;
        return $this->unknownNames[$itemno];

    }

    public function getItemName($itemno) {
        return $this->itemNames[$this->normalizeItemNo($itemno)] ?? $this->getUnknownName($itemno);
    }

    public function getIncoming($itemno) {
        return $this->incomingCounter[$this->normalizeItemNo($itemno)] ?? 0;
    }

    public function getOutgoing($itemno) {
        return $this->shipmentCounterSending[$this->normalizeItemNo($itemno)] ?? 0;
    }

    public function getSent($itemno) {
        return $this->shipmentCounterSent[$this->normalizeItemNo($itemno)] ?? 0;
    }

    public function getWaitingGF($itemno) {
        return $this->shipmentCounterWaiting[$this->normalizeItemNo($itemno)] ?? 0;
    }

    public function getPipeline($itemno) {
        return $this->shipmentCounterPipeline[$this->normalizeItemNo($itemno)] ?? 0;
    }

    public function getSelectedLast7Days($itemno) {
        return $this->last7DaysMap[$this->normalizeItemNo($itemno)] ?? 0;
    }

     public function getPrognose($itemno) {
        return $this->prognosisMap[$this->normalizeItemNo($itemno)] ?? 0;
    }


    /**
     * CALCULATED FIELDS
     */

    public function getAvailableAndIncoming($itemno) {

        if($this->isBOMItem($itemno)) {
            return $this->getSampakAvailableAndIncoming($itemno);
        }

        return $this->getAvailable($itemno)+$this->getIncoming($itemno);
    }

    public function getWarehouseMissing($itemno) {

        $waiting = $this->getWaitingGF($itemno);
        $available = $this->getAvailableAndIncoming($itemno);

        if($available >= $waiting) {
            return 0;
        } else {
            return $waiting-$available;
        }
    }

    public function getWarehouseCanSend($itemno) {


        if($this->isBOMItem($itemno)) {
            return $this->getSampakCanSend($itemno);
        }

        $waiting = $this->getWaitingGF($itemno);
        $available = $this->getAvailableAndIncoming($itemno);

        if($available >= $waiting) {
            return $waiting;
        } else {
            return $available;
        }

    }

    public function getWarehouseAvailableNow($itemno) {

        if($this->isBOMItem($itemno)) {
            return $this->getSampakAvailable($itemno); // UPDATE
        }

        return $this->getAvailable($itemno);


    }

    public function getNextShippingSuggestion($itemno) {

        $warehouseMissing = $this->getWarehouseMissing($itemno);

        $available = $this->getAvailableAndIncoming($itemno);
        $waiting = $this->getWaitingGF($itemno);

        $adjust = 0;
        if($available > $waiting) {
            $adjust = $available - $waiting;
        }


        $suggestion = intval(($warehouseMissing > 0 ? $warehouseMissing : 0) + 0.25*$this->getPipeline($itemno)*$this->getPrognose($itemno))-$adjust;
        return $suggestion <= 0 ? 0 : $suggestion;
    }

    /**
     * SAMPAK CALCULATING FUNCTIONS
     */
    
    public function getChildQuantityPer($itemNoChild,$itemNoParent)
    {
        // Get childs
        $childs = $this->getChildItems($itemNoParent);
        foreach($childs as $child) {
            if($child["itemno"] == $itemNoChild) {
                return $child["quantityper"];
            }
        }

        return 0;
    }

    public function getSampakChildWaiting($itemNoChild,$itemNoParent)
    {

        // Find quantity per
        $quantityPer = $this->getChildQuantityPer($itemNoChild, $itemNoParent);

        // Find waiting parents
        $parentWaiting = $this->getWaitingGF($itemNoParent);

        return $parentWaiting*$quantityPer;
        
    }
    
    public function getSampakCanSend($itemno) {

        $maxNo = null;
        $childItems = $this->getChildItems($itemno);

        foreach ($childItems as $childData) {

            $childItemno = $childData["itemno"];
            $subQuantity = $childData["quantityper"];
            $subLager = $this->getAvailable($childItemno);
            $canMake = $subQuantity == 0 ? 0 : floor($subLager / $subQuantity);

            if ($maxNo === null || $maxNo > $canMake) {
                $maxNo = $canMake;
            }
        }

        if ($maxNo < 0) $maxNo = 0;

        $waiting = $this->getWaitingGF($itemno);
        $available = $maxNo;

        if($available >= $waiting) {
            return $waiting;
        } else {
            return $available;
        }

    }

    public function getSampakAvailable($itemno) {

        $maxNo = null;
        $childItems = $this->getChildItems($itemno);

        foreach ($childItems as $childData) {
            $childItemno = $childData["itemno"];
            $subQuantity = $childData["quantityper"];
            $subLager = $this->getAvailable($childItemno);
            $canMake = $subQuantity == 0 ? 0 : floor($subLager / $subQuantity);
            if ($maxNo === null || $maxNo > $canMake) {
                $maxNo = $canMake;
            }
        }

        if ($maxNo < 0) $maxNo = 0;
        return $maxNo;

    }

    public function getSampakAvailableAndIncoming($itemno) {

        $maxNo = null;
        $childItems = $this->getChildItems($itemno);

        foreach ($childItems as $childData) {
            $subQuantity = $childData["quantityper"];
            $childItemNo = $childData["itemno"];

            $subLager = $this->getAvailableAndIncoming($childItemNo);

            $canMake = $subQuantity == 0 ? 0 : floor($subLager / $subQuantity);
            if ($maxNo === null || $maxNo > $canMake) {
                $maxNo = $canMake;
            }
        }

        if ($maxNo < 0) $maxNo = 0;
        return $maxNo;

    }


    public function getSampakChildCanSend($chilItemNo,$parentItemNo) {
        return $this->getSampakCanSend($parentItemNo)*$this->getChildQuantityPer($chilItemNo, $parentItemNo);
    }

    public function getSampakWarehouseMissing($childItemNo,$parentItemNo) {

        $waiting = $this->getWaitingGF($parentItemNo)*$this->getChildQuantityPer($childItemNo, $parentItemNo);
        $available = $this->getAvailableAndIncoming($childItemNo);

        if($available >= $waiting) {
            return 0;
        } else {
            return $waiting-$available;
        }

    }

    public function getSampakGFPipeline($childItemNo,$parentItemNo) {
        return $this->getPipeline($parentItemNo)*$this->getChildQuantityPer($childItemNo, $parentItemNo);
    }

    public function getSampakSelectedLast7Days($childItemNo,$parentItemNo) {
        return $this->getSelectedLast7Days($parentItemNo)*$this->getChildQuantityPer($childItemNo, $parentItemNo);
    }

    public function getSampakPrognose($childItemNo,$parentItemNo) {
        return $this->getPrognose($parentItemNo)*$this->getChildQuantityPer($childItemNo, $parentItemNo);
    }

    public function getSampakNextSuggestion($childItemNo,$parentItemNo) {
        return $this->getNextShippingSuggestion($parentItemNo)*$this->getChildQuantityPer($childItemNo, $parentItemNo);
    }

    public function getSampakOutgoing($childItemNo,$parentItemNo) {
        return $this->getOutgoing($parentItemNo)*$this->getChildQuantityPer($childItemNo, $parentItemNo);
    }

    public function getSampakSent($childItemNo,$parentItemNo) {
        return $this->getSent($parentItemNo)*$this->getChildQuantityPer($childItemNo, $parentItemNo);
    }


    /*
     * GET TOTALS
     */

    private $totalVarenrOriginList = array();

    public function getTotalVarenrList()
    {
        $varenrlist = array_keys($this->totalVarenrOriginList);
        sort($varenrlist);
        return $varenrlist;
    }



    /**
     * Load data from database
     */

    private $vlagerItemMap = array();
    private $itemidList = array();

    private $itemMap = array();

    private $itemNames = array();

    private $realItemNoMap = array();

    private $bomMap = array();

    private $inBomMap = array();

    private $shipmentCounterSent = array();

    private $shipmentCounterPipeline = array();

    private $shipmentCounterSending = array();

    private $shipmentCounterWaiting = array();

    private $incomingCounter = array();

    private $prognosisMap = array();

    private $last7DaysMap = array();

    private function loadData()
    {

        $itemNoList = array();
        $bomChilds = array();

        // Load all shipments in pipelie
        $shipmentSQL = "SELECT count(DISTINCT id) as antal, itemno, shipment_state, IF(shipped_date IS NULL, 0, 1) as is_shipped FROM `shipment` where companyorder_id in (select id from company_order where ".($this->lockShopID > 0 ? "shop_id = ".intval($this->lockShopID)." && " : "")." shop_id in (select shop_id from cardshop_settings where privatedelivery_handler = '".$this->vlager->code."') and order_state not in (7,8)) and shipment_state != 4 and shipment_type in ('privatedelivery','directdelivery') group by itemno, shipment_state, is_shipped;";
        $shipmentData = \Shipment::find_by_sql($shipmentSQL);

        foreach($shipmentData as $shipRow) {

            // Fetch data to vars
            $itemno = $this->normalizeItemNo($shipRow->itemno);
            $antal = $shipRow->antal;
            $state = $shipRow->shipment_state;
            $isShipped = $shipRow->is_shipped;

            if($isShipped == 1) {
                if(!isset($this->shipmentCounterSent[$itemno])) {
                    $this->shipmentCounterSent[$itemno] = 0;
                }
                $this->shipmentCounterSent[$itemno] += $antal;
            } else if($state == 9 || $state == 3) {
                if(!isset($this->shipmentCounterPipeline[$itemno])) {
                    $this->shipmentCounterPipeline[$itemno] = 0;
                }
                $this->shipmentCounterPipeline[$itemno] += $antal;
            } else if($state == 0 || $state == 1) {
                if(!isset($this->shipmentCounterWaiting[$itemno])) {
                    $this->shipmentCounterWaiting[$itemno] = 0;
                }
                $this->shipmentCounterWaiting[$itemno] += $antal;
            } else if($state == 2 || $state == 5 || $state == 6) {
                if(!isset($this->shipmentCounterSending[$itemno])) {
                    $this->shipmentCounterSending[$itemno] = 0;
                }
                $this->shipmentCounterSending[$itemno] += $antal;
            }

            // Add to itemno list if not already there
            if(!in_array($itemno, $itemNoList)) {
                $itemNoList[] = $itemno;
            }

        }


        // Load last 7 days and prognosis
        $prognosisSQL = "SELECT 
    pm.model_present_no,
    SUM(CASE WHEN order_timestamp >= CURDATE() - INTERVAL 7 DAY THEN 1 ELSE 0 END) AS last_1_7,
    SUM(CASE WHEN order_timestamp < CURDATE() - INTERVAL 7 DAY AND order_timestamp >= CURDATE() - INTERVAL 14 DAY THEN 1 ELSE 0 END) AS last_8_14,
    SUM(CASE WHEN order_timestamp >= CURDATE() - INTERVAL 7 DAY THEN 1 ELSE 0 END) + 
    0.5 * (SUM(CASE WHEN order_timestamp >= CURDATE() - INTERVAL 7 DAY THEN 1 ELSE 0 END) - 
           SUM(CASE WHEN order_timestamp < CURDATE() - INTERVAL 7 DAY AND order_timestamp >= CURDATE() - INTERVAL 14 DAY THEN 1 ELSE 0 END)) AS prognose
FROM 
    `order` o
JOIN 
    present_model pm ON o.present_model_id = pm.model_id
WHERE 
    pm.language_id = 1 
    AND ".($this->lockShopID > 0 ? "o.shop_id = ".intval($this->lockShopID)." && " : "")." o.shop_id IN (SELECT shop_id FROM cardshop_settings WHERE privatedelivery_handler = '".$this->vlager->code."')
GROUP BY 
    pm.model_present_no;";

        $prognosisSQL = \Order::find_by_sql($prognosisSQL);
        foreach($prognosisSQL as $prognosisRow) {

            $itemno = $this->normalizeItemNo($prognosisRow->model_present_no);
            $this->prognosisMap[$itemno] = $prognosisRow->prognose;
            $this->last7DaysMap[$itemno] = $prognosisRow->last_1_7;

            // Add to itemno list if not already there
            if(!in_array($itemno, $itemNoList)) {
                $itemNoList[] = $itemno;
            }
        }

        // Load in pipeline
        $pipelineSQL = "SELECT count(distinct o.id) as antal, pm.model_present_no  FROM shop_user su, `order` o, present_model pm WHERE ".($this->lockShopID > 0 ? "o.shop_id = ".intval($this->lockShopID)." && " : "")." o.shopuser_id = su.id and su.delivery_state not in (1,2) and su.blocked = 0 and su.shutdown = 0 and pm.language_id = 1 and o.present_model_id = pm.model_id and o.shop_id in (select shop_id from cardshop_settings where privatedelivery_handler = '".$this->vlager->code."') group by pm.model_present_no;";
        $pipelineItems = \Order::find_by_sql($pipelineSQL);
        foreach($pipelineItems as $piperow) {

            $itemno = $this->normalizeItemNo($piperow->model_present_no);
            if(!isset($this->shipmentCounterPipeline[$itemno])) {
                $this->shipmentCounterPipeline[$itemno] = 0;
            }
            $this->shipmentCounterPipeline[$itemno] += $piperow->antal;

            // Add to itemno list if not already there
            if(!in_array($itemno, $itemNoList)) {
                $itemNoList[] = $itemno;
            }

        }


        // Unique itemno list
        $this->itemidList = array_unique($itemNoList);

        // Process all items
        foreach($this->itemidList as $itemno) {

            // Check in nav and save to list
            $varenrList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = ".$this->vlager->id." AND `no` LIKE '".$itemno."' AND `deleted` IS NULL");
            $varenr = countgf($varenrList) > 0 ? $varenrList[0] : null;

            if($varenr == null) {
                throw new \Exception("Item not found in NAV: ".$itemno);
            }

            $this->itemMap[$itemno] = $varenr->description;
            $this->itemNames[$itemno] = $varenr->description;

            $this->realItemNoMap[$itemno] = $varenr->no;


            // Lookup all itemnos in nav bom items
            $bomItems = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE `language_id` = ".$this->vlager->id." AND `parent_item_no` LIKE '".$itemno."' AND `deleted` IS NULL");
            if (count($bomItems) > 0) {
                $bomData = array();
                foreach ($bomItems as $bomItem) {
                    $bomData[] = array("itemno" => $bomItem->no,"name" => $bomItem->description, "quantityper" => $bomItem->quantity_per);
                    $bomChilds[] = $this->normalizeItemNo($bomItem->no);

                    if(!isset($this->inBomMap[$this->normalizeItemNo($bomItem->no)])) {
                        $this->inBomMap[$this->normalizeItemNo($bomItem->no)] = array();
                    }
                    $this->realItemNoMap[$bomItem->no] = $bomItem->no;
                    $this->inBomMap[$this->normalizeItemNo($bomItem->no)][] = $itemno;
                    
                    $this->itemNames[$this->normalizeItemNo($bomItem->no)] = $bomItem->description;
                }
                $this->bomMap[$itemno] = $bomData;
            }

        }

        // Load vlager stock
        $this->reloadItemStock();

        // Load incoming items
        $incomingSQL = "select itemno, sum(quantity_order) as quantity from vlager_incoming_line where vlager_incoming_id in (SELECT id FROM `vlager_incoming` where vlager_id = 1 and received IS NULL) group by itemno";
        $incomingItems = \VLagerIncomingLine::find_by_sql($incomingSQL);
        foreach($incomingItems as $incoming) {
            $this->incomingCounter[$this->normalizeItemNo($incoming->itemno)] = $incoming->quantity;
        }

        // Make totals origin varenr list
        $this->totalVarenrOriginList = array();
        foreach($this->getPrimaryItems() as $itemno => $itemname) {
            $this->totalVarenrOriginList[$itemno] = array("PARENT");
        }

        foreach($this->inBomMap as $childItemNo => $parentItems) {
            if(!isset($this->totalVarenrOriginList[$childItemNo])) $this->totalVarenrOriginList[$childItemNo] = array();
            foreach($parentItems as $parentItemNo) {
                $this->totalVarenrOriginList[$childItemNo][] = $parentItemNo;;
            }
        }

    }

    public function reloadItemStock() {

        // Load current available
        $vlagerItems = \VLagerItem::find_by_sql("SELECT * FROM vlager_item where vlager_id = " . intvalgf($this->vlagerid));
        $this->vlagerItemMap = array();

        foreach($vlagerItems as $vlagerItem) {

            // If not in itemidlist and not in bomChilds, throw an error
            if(!in_array($this->normalizeItemNo($vlagerItem->itemno), $this->itemidList) ) {
                $this->itemMap[$this->normalizeItemNo($vlagerItem->itemno)] = "UNKNOWN ITEM!";
                $this->realItemNoMap[$this->normalizeItemNo($vlagerItem->itemno)] = $vlagerItem->itemno;
            }

            if(isset($this->vlagerItemMap[$this->normalizeItemNo($vlagerItem->itemno)])) throw new \Exception("Duplicate itemno in VLagerItem");
            $this->vlagerItemMap[$this->normalizeItemNo($vlagerItem->itemno)] = $vlagerItem->quantity_available;

        }

    }


    private function normalizeItemNo($itemno) {
        return trimgf(strtolower($itemno));
    }



    /**
     * GET SHIPMENTS WAITING
     */

    private $shipmentWaitingList = array();
    private $shipmentNoWaiting = array();

    public function loadShipmentsWaiting($released=true) {

        // Load all shipments in pipelie
        $shipmentSQL = "SELECT * FROM `shipment` where handler != 'sespahotel' and companyorder_id in (select id from company_order where shop_id in (select shop_id from cardshop_settings where privatedelivery_handler = '".$this->vlager->code."') and order_state not in (7,8)) and shipment_state = 1 and shipment_type in ('privatedelivery','directdelivery') and id not in (select shipment_id from blockmessage where release_status = 0) order by created_date asc";
        $this->shipmentWaitingList = \Shipment::find_by_sql($shipmentSQL);
        $this->shipmentNoWaiting = array();

        foreach($this->shipmentWaitingList as $shipment) {
            $itemno = $shipment->itemno;
            if(!isset($this->shipmentNoWaiting[$itemno])) {
                $this->shipmentNoWaiting[$itemno] = 0;
            }
            $this->shipmentNoWaiting[$itemno] += $shipment->quantity;
        }

    }

    public function getShipmentsWaiting() {
        return $this->shipmentNoWaiting;
    }

    public function getWaitingShipmentsByItemNo($itemno) {

        $shipmentlist = [];
        foreach($this->shipmentWaitingList as $shipment) {
            if(trim(strtolower($shipment->itemno)) == trim(strtolower($itemno))) {
                $shipmentlist[] = $shipment;
            }
        }
        return $shipmentlist;
    }

    public function getShipmentNoWaiting($random=false) {

        $shipmentNoList = array_keys($this->shipmentNoWaiting);

        if($random) {
            shuffle($shipmentNoList);
        } else {
            sort($shipmentNoList);
        }

        return $shipmentNoList;

    }

    public function getVLagerShops()
    {
        $sql = "SELECT * FROM cardshop_settings WHERE privatedelivery_handler = '".$this->vlager->code."'";
        return \CardshopSettings::find_by_sql($sql);
    }

}
