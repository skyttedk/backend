<?php

namespace GFBiz\Navision;


use GFCommon\Model\Navision\BOMItemsWS;
use GFCommon\Model\Navision\ItemsWS;
use GFCommon\Model\Navision\SalesPricesWS;

class ItemSync
{


    
    public function __construct() {

    }

    public function runItemSync($language_id,$item_no,$runBomSync=true,$forceIsHandled=null,$runPriceSync=false) {

        $this->log("Sync item: ".$item_no." on lang ".$language_id);
        
        if(!in_array($language_id, array(1,4))) {
            $this->log("Sync item: EXCEPTION - no language valid");
            throw new \Exception("Ikke gyldigt language_id");
        }

        if(trim($item_no) == "") {
            $this->log("Sync item: EXCEPTION - no item no valid");
            throw new \Exception("Der er ikke angivet et varenr");
        }

        // Load item from nav
        $client = new ItemsWS($language_id);
        $item = $client->getItem($item_no);

        // Load item from db
        $navisionItemList = \NavisionItem::find("all",array("conditions" => array("no" => $item_no,"language_id" => $language_id)));
        if(count($navisionItemList) == 0) $navisionItem = null;
        else $navisionItem = $navisionItemList[0];
        $this->log("Sync item: ".($navisionItem == null ? "not foudn in navision" : "found in navision"));
        
        // Update or create existing
        if($item != null) {
            
            // Create
            if($navisionItem == null)  {
                $this->log("Sync item: not found in db, create new item");
                $navisionItem = new \NavisionItem();
                $navisionItem->language_id = $language_id;
                $navisionItem->created = date('d-m-Y H:i:s');
                $navisionItem->deleted = null;
            } else {
                $this->log("Sync item: found ind db update ".$navisionItem->id);
            }

            // Get previous data
            $prevData = json_encode($navisionItem);

            // Update all fields
            $navisionItem->no = $item->getItemNo();
            $navisionItem->description = $item->getDescription();
            $navisionItem->nav_key = $item->getNavKey();
            $navisionItem->type = $item->getType();
            $navisionItem->unit_price = $item->getUnitPrice();
            $navisionItem->base_unit_of_measure = $item->getBaseUnitOfMeasure();
            $navisionItem->assembly_bom = $item->getAssemblyBOM();
            $navisionItem->vat_prod_posting_group = $item->getVATProdPostingGroup();
            $navisionItem->gen_prod_posting_group = $item->getGenProdPostingGroup();
            $navisionItem->inventory_posting_group = $item->getInventoryPostingGroup();
            $navisionItem->item_category_code = $item->getCategoryCode();
            $navisionItem->product_group_code = $item->getProductGroupCode();
            $navisionItem->gross_weight = $item->getGrossWeight();
            $navisionItem->net_weight = $item->getNetWeight();
            $navisionItem->units_per_parcel = $item->getUnitsPerParcel();
            $navisionItem->unit_volume = $item->getUnitVolume();
            $navisionItem->reference_no = $item->getReferenceNo();
            $navisionItem->unit_cost = $item->getUnitCost();
            $navisionItem->price_profit_calculation = $item->getPriceProfitCalculation();
            $navisionItem->profit_percent = $item->getProfitPercent();
            $navisionItem->costing_method = $item->getCostingMethod();
            $navisionItem->standard_cost = $item->getStandardCost();
            $navisionItem->last_direct_cost = $item->getLastDirectCost();
            $navisionItem->indirect_cost_percent = $item->getIndirectCostPercent();
            $navisionItem->crossreference_no = $item->getCrossReferenceNo();
            $navisionItem->blocked = $item->getBlocked() ? 1 : 0;
            $navisionItem->length = $item->getLength();
            $navisionItem->width = $item->getWidth();
            $navisionItem->height = $item->getHeight();
            $navisionItem->cubage = $item->getCubage();
            $navisionItem->countryoforigin = $item->getCountryOfOrigin();
            $navisionItem->vejl_pris = $item->getVejledendePris();

            // Update deleted
            if($navisionItem->deleted == null && $navisionItem->blocked == 1) {
                $navisionItem->deleted = date('d-m-Y H:i:s');
            } else if($navisionItem->deleted != null && $navisionItem->blocked == 0) {
                $navisionItem->deleted = null;
            }

            // Get current data
            $curData = json_encode($navisionItem);

            // Updates on changes
            if($curData != $prevData) {
                $navisionItem->is_handled = 0;
                $navisionItem->updated_at = date('d-m-Y H:i:s');
            }

            // If force set is handled
            if($forceIsHandled !== null) {
                $navisionItem->is_handled = $forceIsHandled;
                $navisionItem->updated_at = date('d-m-Y H:i:s');
            }

            $navisionItem->save();

            // Run bom sync
            if($runBomSync) {
                $this->log("Sync item: run bom sync");
                $this->runBOMSync($language_id, $item_no);
            }

            if($runPriceSync) {
                $this->log("Sync item: run salesprice sync");
                $this->runSalesPriceSync($language_id, $item_no);
            }

        } 
        
        // Delete existing
        else if($item == null && $navisionItem != null) {
            $navisionItem->deleted = date('d-m-Y H:i:s');
            $navisionItem->save();
        }
        
        return $navisionItem;
        
    }

    /**
     * @param $language_id
     * @param $item_no
     * @param $updateChilds
     * @return void
     */
    public function runBOMSync($language_id,$item_no,$updateChilds=true) {

        $this->log("Sync bom: ".$item_no." on lang ".$language_id);
        
        if(!in_array($language_id, array(1,4))) {
            $this->log("Sync bom: EXCEPTION - no language valid");
            throw new \Exception("Ikke gyldigt language_id");
        }

        if(trim($item_no) == "") {
            $this->log("Sync bom: EXCEPTION - no item no valid");
            throw new \Exception("Der er ikke angivet et varenr");
        }

        // Load bom from parent item
        $client = new BOMItemsWS($language_id);
        $parentItems = $client->getAllChildItems($item_no);
        $this->log("Sync bom: Found ".count($parentItems)." childs in navision.");
        
        
        // Load item from db
        $navisionItemList = \NavisionBomItem::find("all",array("conditions" => array("parent_item_no" => $item_no,"language_id" => $language_id)));
        $navisionItemMap = array();
        foreach($navisionItemList as $bomItem) {
            $navisionItemMap[$bomItem->nav_key] = $bomItem;
        }
        $this->log("Sync bom: found ".count($navisionItemList)." childs in db");

        // No data to process
        if((!is_array($parentItems) || count($parentItems) == 0) && (!is_array($navisionItemList) || count($navisionItemList) == 0)) {
            $this->log("Sync bom: nothing to do, abort!");
            return;
        }

        // Process items from service
        foreach($parentItems as $parentItem) {

            if(!isset($navisionItemMap[$parentItem->getNavKey()])) {
                $this->log("Sync bom: create child ".$parentItem->getItemNo());
                $itemObj = new \NavisionBomItem();
                $itemObj->language_id = $language_id;
                $itemObj->created = date('d-m-Y H:i:s');
                $itemObj->deleted = null;
            } else {
                $this->log("Sync bom: update child ".$parentItem->getItemNo());
                $itemObj = $navisionItemMap[$parentItem->getNavKey()];
                $itemObj->deleted = null;
                unset($navisionItemMap[$parentItem->getNavKey()]);
            }

            // Update all fields
            $itemObj->no = $parentItem->getItemNo();
            $itemObj->parent_item_no = $parentItem->getParentItemNo();
            $itemObj->nav_key = $parentItem->getNavKey();
            $itemObj->assembly_bom = $parentItem->getAssemblyBOM();
            $itemObj->description = $parentItem->getDescription();
            $itemObj->unit_of_measure_code = $parentItem->getUnitofMeasureCode();
            $itemObj->quantity_per = $parentItem->getQuantityper();
            $itemObj->save();

            // Update items
            if($updateChilds) {
                $this->log("Sync bom: run update on child ".$itemObj->no);
                $this->runItemSync($language_id, $itemObj->no,false);
            }
            
        }

        // Delete unused items
        foreach($navisionItemMap as $deleteItem) {
            if($deleteItem->deleted == null) {
                $this->log("Sync bom: remove item ".$deleteItem->no);
                $deleteItem->deleted = date('d-m-Y H:i:s');
                $deleteItem->save();
            }
        }

    }


    /**
     * Update salesprices for a specific itemno for a specific country
     * @param $language_id Language id of navision instance to update
     * @param $item_no Item no to update data for
     * @return \NavisionSalesPrice[]
     */
    public function runSalesPriceSync($language_id,$item_no) {

        $this->log("Sync salesprice: ".$item_no." on lang ".$language_id);

        if(!in_array($language_id, array(1,4))) {
            $this->log("Sync salesprice: EXCEPTION - no language valid");
            throw new \Exception("Ikke gyldigt language_id");
        }

        if(trim($item_no) == "") {
            $this->log("Sync salesprice: EXCEPTION - no item no valid");
            throw new \Exception("Der er ikke angivet et varenr");
        }

        // Load salesprice from navision
        $client = new SalesPricesWS($language_id);
        $salesPrices = $client->getItemSalesPrices($item_no);
        $this->log("Sync salesprice: Found ".count($salesPrices)." salesprices in navision.");

        // Load item from db
        $navSalespriceList = \NavisionSalesPrice::find("all",array("conditions" => array("item_no" => $item_no,"language_id" => $language_id)));
        $navisionPriceMap = array();
        foreach($navSalespriceList as $bomItem) {
            $navisionPriceMap[$bomItem->nav_key] = $bomItem;
        }
        $this->log("Sync salesprice: found ".count($navSalespriceList)." prices in db");

        // No data to process
        if((!is_array($salesPrices) || count($salesPrices) == 0) && (!is_array($navSalespriceList) || count($navSalespriceList) == 0)) {
            $this->log("Sync salesprice: nothing to do, abort!");
            return array();
        }

        $returnList = array();
        
        // Process items from service
        foreach($salesPrices as $salesPrice) {

            if(!isset($navisionPriceMap[$salesPrice->getNavKey()])) {
                $this->log("Sync salesprice: create salesprice ".$salesPrice->getSalesCode()." - ".$salesPrice->getUnitPrice());
                $navSalesPrice = new \NavisionSalesPrice();
                $navSalesPrice->language_id = $language_id;
                $navSalesPrice->created = date('d-m-Y H:i:s');
                $navSalesPrice->deleted = null;
            } else {
                $this->log("Sync salesprice: update price ".$salesPrice->getSalesCode()." - ".$salesPrice->getUnitPrice());
                $navSalesPrice = $navisionPriceMap[$salesPrice->getNavKey()];
                $navSalesPrice->deleted = null;
                unset($navisionPriceMap[$salesPrice->getNavKey()]);
            }

            // Update all fields
            $navSalesPrice->item_no = $salesPrice->getItemNo();
            $navSalesPrice->nav_key = $salesPrice->getNavKey();
            $navSalesPrice->sales_type = $salesPrice->getSalesType();
            $navSalesPrice->sales_code = $salesPrice->getSalesCode();
            $navSalesPrice->minimum_quantity = $salesPrice->getMinimumQuantity();
            $navSalesPrice->starting_date = $salesPrice->getStartingDate() == "0001-01-01" ? null : $salesPrice->getStartingDate();
            $navSalesPrice->ending_date = $salesPrice->getEndingDate() == "0001-01-01" ? null : $salesPrice->getEndingDate();
            $navSalesPrice->unit_of_measure = $salesPrice->getUnitofMeasureCode();
            $navSalesPrice->unit_price = $salesPrice->getUnitPrice();
            $navSalesPrice->price_includes_vat = $salesPrice->getPriceIncludesVAT() == true ? 1 : 0;

            // Update deleted
            if($navSalesPrice->deleted != null) {
                $navSalesPrice->deleted = null;
            }
            
            $navSalesPrice->save();
            $returnList[] = $navSalesPrice;
            
        }

        // Delete unused items
        foreach($navisionPriceMap as $deleteItem) {
            if($deleteItem->deleted == null) {
                $this->log("Sync salesprice: remove item ".$deleteItem->sales_code." - ".$deleteItem->unit_price);
                $deleteItem->deleted = date('d-m-Y H:i:s');
                $deleteItem->save();
            }
        }
        
        return $returnList;
        
    }

    /* HELPERS */

    /**
     * If output of logging is enabled
     * @var bool
     */
    private $outputLog = true;

    /**
     * Logging functionality
     * @param $message
     * @return void
     */
    private function log($message) {
        if(!$this->outputLog) return;
        echo "SYNCLOG: ".$message."<br>";
    }

}