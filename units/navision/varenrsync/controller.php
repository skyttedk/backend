<?php

namespace GFUnit\navision\varenrsync;
use ActiveRecord\DateTime;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\ItemsWS;
use GFCommon\Model\Navision\BOMItemsWS;
use GFCommon\Model\Navision\SalesPricesWS;
use GFCommon\Model\Navision\SalesPersonWS;
use GFCommon\Model\Navision\LocationWS;
use GFCommon\Model\Navision\RenameLogWS;

ini_set('memory_limit', '2000M');
set_time_limit(40 * 60);

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /*
    public function testnewsync() {
        $this->synccountrylocations(1);
        $this->synccountrylocations(4);
        //$this->synccountrylocations(5);
        \system::connection()->commit();
    }
    */

    /******* RUN DAILY SYNC ********/

    private $runUpdateFailed = false;
    private $runUpdateFailedMessage = "";

    public function runupdate() {

        // Do not run navision jobs in this period
        if(in_array(intval(date("H")),array(0,1,2,3,4,5))) {
            exit();
        }

        \GFCommon\DB\CronLog::startCronJob("NavVarenrSync");

        // Sync danmark
        $this->updatecountry(1);
        $this->updatecountry(4);
        $this->updatecountryrename(1);
        $this->updatecountryrename(4);

        if($this->runUpdateFailed) {
            \GFCommon\DB\CronLog::endCronJob(2,$this->runUpdateFailedMessage);
        } else {
            \GFCommon\DB\CronLog::endCronJob(1,"OK");
        }

        \system::connection()->commit();

    }

    public function runupdatesalesprice() {

        // Do not run navision jobs in this period
        if(in_array(intval(date("H")),array(0,1,2,3,4,5))) {
            exit();
        }



        $this->updatecountrysalesprices(1);





    }

    private function updatecountrysalesprices($languageid)
    {
        // Load from service
        $client = new SalesPricesWS($languageid);
        $client->printInfoSheet();
        exit();
        $serviceItems = $client->getModifiedToday();
        echo " - Loaded ".countgf($serviceItems)." changed from navision<br>";
    }

    private function updatecountry($languageid) {
        echo "Sync varenr - language ".$languageid."<br>";
        $this->updatecountryitems($languageid);
    }

    private function updatecountryitems($languageid)
    {
        $countNew = 0;
        $countUpdated = 0;
        $countEmptyNo = 0;
        $countDoubleNo = 0;
        
        // Load existing items
        $itemList = \NavisionItem::find('all',array('conditions' => array("language_id" => $languageid)));
        $itemMap = array();
        foreach($itemList as $item) {
            $itemMap[mb_strtolower(trimgf($item->no))] = $item;
        }
        echo " - Found ".countgf($itemMap)." in db<br>";

        // Load from service
        try {
            $client = new ItemsWS($languageid);
            $serviceItems = $client->getModifiedToday();
            echo " - Loaded ".countgf($serviceItems)." changed from navision<br>";
        }
        catch (\Exception $e) {
            echo "Failed to load items: ".$e->getMessage();
            $this->runUpdateFailed = true;
            $this->runUpdateFailedMessage .= "Failed to load items: ".$e->getMessage().". ";
            return;
        }

        foreach($serviceItems as $item) {

            $no = trimgf("".mb_strtolower($item->getItemNo()))."";

            if($no != "" && !isset($usedItemNoList[$no])) {

                // Update
                if(isset($itemMap[$no])) {
                    $itemObj = $itemMap[$no];
                    $countUpdated++;
                    unset($itemMap[$no]);
                }

                // Create
                else {
                    $itemObj = new \NavisionItem();
                    $itemObj->language_id = $languageid;
                    $itemObj->created = date('d-m-Y H:i:s');
                    $itemObj->deleted = null;
                    $countNew++;
                }

                $prevData = json_encode($itemObj);

                // Update all fields
                $itemObj->no = $item->getItemNo();
                $itemObj->description = $item->getDescription();
                $itemObj->nav_key = $item->getNavKey();
                $itemObj->type = $item->getType();
                $itemObj->unit_price = $item->getUnitPrice();
                $itemObj->base_unit_of_measure = $item->getBaseUnitOfMeasure();
                $itemObj->assembly_bom = $item->getAssemblyBOM();
                $itemObj->vat_prod_posting_group = $item->getVATProdPostingGroup();
                $itemObj->gen_prod_posting_group = $item->getGenProdPostingGroup();
                $itemObj->inventory_posting_group = $item->getInventoryPostingGroup();
                $itemObj->item_category_code = $item->getCategoryCode();
                $itemObj->product_group_code = $item->getProductGroupCode();
                $itemObj->gross_weight = $item->getGrossWeight();
                $itemObj->net_weight = $item->getNetWeight();
                $itemObj->units_per_parcel = $item->getUnitsPerParcel();
                $itemObj->unit_volume = $item->getUnitVolume();
                $itemObj->reference_no = $item->getReferenceNo();

                $itemObj->unit_cost = $item->getUnitCost();
                $itemObj->price_profit_calculation = $item->getPriceProfitCalculation();
                $itemObj->profit_percent = $item->getProfitPercent();
                $itemObj->costing_method = $item->getCostingMethod();
                $itemObj->standard_cost = $item->getStandardCost();
                $itemObj->last_direct_cost = $item->getLastDirectCost();
                $itemObj->indirect_cost_percent = $item->getIndirectCostPercent();
                $itemObj->crossreference_no = $item->getCrossReferenceNo();
                $itemObj->blocked = $item->getBlocked() ? 1 : 0;

                $itemObj->length = $item->getLength();
                $itemObj->width = $item->getWidth();
                $itemObj->height = $item->getHeight();
                $itemObj->cubage = $item->getCubage();
                $itemObj->countryoforigin = $item->getCountryOfOrigin();
                $itemObj->vejl_pris = $item->getVejledendePris();
                $itemObj->is_external = $item->getIsExternal() ? 1 : 0;
                $itemObj->tariff_no = $item->getTariffNo();

                // Update deleted
                if($itemObj->deleted == null && $itemObj->blocked == 1) {
                    //$itemObj->deleted = date('d-m-Y H:i:s');
                } else if($itemObj->deleted != null && $itemObj->blocked == 0) {
                    $itemObj->deleted = null;
                }

                $curData = json_encode($itemObj);
                if($curData != $prevData) {
                    $itemObj->is_handled = 0;
                    $itemObj->updated_at = date('d-m-Y H:i:s');
                    //echo $itemObj->no." - is changed<br>";
                }

                $itemObj->save();
                $usedItemNoList[$no] = true;

            } else if($no == "") {
                $countEmptyNo++;
            } else if(isset($usedItemNoList[$no])) {
                $countDoubleNo++;
            }

        }

        echo "Created ".$countNew.", updated ".$countUpdated."<br><br>";

    }

    private function updatecountryrename($languageid)
    {

        // Load rename list
        try {
            $client = new RenameLogWS($languageid);
            $responseList = $client->getAllItems();
        }
        catch (\Exception $e) {
            echo "Failed to load rename items: ".$e->getMessage();
            exit();
        }

        $created = 0;
        $existing = 0;

        // Handle response
        foreach($responseList as $response)
        {

            $checkConditions = array(
                "language_id" => $languageid,
                "renamed_at" => date("Y-m-d H:i:s",strtotime($response->getRenamedat())),
                "old_no" => $response->getOldValue(),
                "new_no" => $response->getNewValue()
            );

            $checkName = \NavisionItemRename::find('first',array('conditions' => $checkConditions));

            if($checkName == null) {
                $renameItem = new \NavisionItemRename();
                $renameItem->language_id = $languageid;
                $renameItem->old_no = $response->getOldValue();
                $renameItem->new_no = $response->getNewValue();
                $renameItem->nav_key = $response->getNavKey();
                $renameItem->renamed_at = date("Y-m-d H:i:s",strtotime($response->getRenamedat()));
                $renameItem->renamed_by = $response->getRenamedby();
                $renameItem->table_id = $response->getTableID();
                $renameItem->save();
                $created++;
            } else {
                $existing++;
            }

        }

        echo "Created: ".$created.", updated: ".$existing;


    }

    /******* RUN TOTAL SYNC ********/


    public function testsync() {

        $this->synccountryitems(1);

    }

    public function runsync($language=0)
    {

        // Do not run navision jobs in this period
        if(in_array(intval(date("H")),array(3,4))) {
            exit();
        }

        $language = intvalgf($language);
        if(!in_array($language, array(1,4,5))) {
            echo "NO LANGUAGE - ABORT";
            return;
        }

        \GFCommon\DB\CronLog::startCronJob("NavVarenrSync".$language);

        // Sync danmark
        $this->synccountry($language);
        //$this->synccountry(4);
        //$this->synccountry(5);

        \system::connection()->commit();

        \GFCommon\DB\CronLog::endCronJob(1,"OK");
        
    }

    private function synccountry($languageid)
    {
        echo "Sync varenr - language ".$languageid."<br>";

        try {

            echo "START SYNCING ITEMS!<br>";
            $this->synccountryitems($languageid);
            echo "START SYNCING BOM ITEMS!<br>";
            $this->synccountrybomitems($languageid);
            echo "START SYNCING SALESPERSONS!<br>";
            $this->synccountrysalespersons($languageid);

        }
        catch (\Exception $e) {
            echo "ERROR: ".$e->getMessage()." @ ".$e->getFile()." line ".$e->getLine()."<br>";
           exit();
        }

        if($languageid != 5) {
            $this->syncsalesprices($languageid);
            $this->synccountrylocations($languageid);
        }

        \system::connection()->commit();
        \system::connection()->transaction();
    }

    private function synccountryitems($languageid)
    {

        echo "Syncing normal items<br>";

        // Load existing items
        $itemList = \NavisionItem::find('all',array('conditions' => array("language_id" => $languageid)));
        $itemMap = array();
        foreach($itemList as $item) {
            $itemMap[mb_strtolower(trimgf($item->no))] = $item;
        }
        echo " - Found ".countgf($itemMap)." in db<br>";

        // Load from service
        $client = new ItemsWS($languageid);
        $serviceItems = $client->getAllItems();
        echo " - Loaded ".countgf($serviceItems)." from navision<br>";

        if(count($serviceItems) == 0) {
            return;
            //throw new \Exception("No items loaded from navision, must be service problem.");
        }

        $countNew = 0;
        $countUpdated = 0;
        $countDeletd = 0;
        $countEmptyNo = 0;
        $countDoubleNo = 0;
        $usedItemNoList = array();

        foreach($serviceItems as $item) {

            $no = trimgf("".mb_strtolower($item->getItemNo()))."";

            if($no != "" && !isset($usedItemNoList[$no])) {

                // Update
                if(isset($itemMap[$no])) {
                    $itemObj = $itemMap[$no];
                    $countUpdated++;
                    unset($itemMap[$no]);

                }

                // Create
                else {
                    $itemObj = new \NavisionItem();
                    $itemObj->language_id = $languageid;
                    $itemObj->created = date('d-m-Y H:i:s');
                    $itemObj->deleted = null;
                    $countNew++;

                }

                $prevData = json_encode($itemObj);

                // Update all fields
                $itemObj->no = $item->getItemNo();
                $itemObj->description = $item->getDescription();
                $itemObj->nav_key = $item->getNavKey();
                $itemObj->type = $item->getType();
                $itemObj->unit_price = $item->getUnitPrice();
                $itemObj->base_unit_of_measure = $item->getBaseUnitOfMeasure();
                $itemObj->assembly_bom = $item->getAssemblyBOM();
                $itemObj->vat_prod_posting_group = $item->getVATProdPostingGroup();
                $itemObj->gen_prod_posting_group = $item->getGenProdPostingGroup();
                $itemObj->inventory_posting_group = $item->getInventoryPostingGroup();
                $itemObj->item_category_code = $item->getCategoryCode();
                $itemObj->product_group_code = $item->getProductGroupCode();
                $itemObj->gross_weight = $item->getGrossWeight();
                $itemObj->net_weight = $item->getNetWeight();
                $itemObj->units_per_parcel = $item->getUnitsPerParcel();
                $itemObj->unit_volume = $item->getUnitVolume();
                $itemObj->reference_no = $item->getReferenceNo();

                $itemObj->unit_cost = $item->getUnitCost();
                $itemObj->price_profit_calculation = $item->getPriceProfitCalculation();
                $itemObj->profit_percent = $item->getProfitPercent();
                $itemObj->costing_method = $item->getCostingMethod();
                $itemObj->standard_cost = $item->getStandardCost();
                $itemObj->last_direct_cost = $item->getLastDirectCost();
                $itemObj->indirect_cost_percent = $item->getIndirectCostPercent();
                $itemObj->crossreference_no = $item->getCrossReferenceNo();
                $itemObj->blocked = $item->getBlocked() ? 1 : 0;

                $itemObj->length = $item->getLength();
                $itemObj->width = $item->getWidth();
                $itemObj->height = $item->getHeight();
                $itemObj->cubage = $item->getCubage();
                $itemObj->countryoforigin = $item->getCountryOfOrigin();
                $itemObj->vejl_pris = $item->getVejledendePris();
                $itemObj->is_external = $item->getIsExternal() ? 1 : 0;
                $itemObj->tariff_no = $item->getTariffNo();

                if (trim($itemObj->crossreference_no) != "") {
                    $itemObj->crossreference_no = preg_replace('/[^\p{L}\p{N}\s\-\_\.]/u', '', $itemObj->crossreference_no);
                } else {
                    $itemObj->crossreference_no = "";
                }

                // Update deleted
                if($itemObj->deleted == null && $itemObj->blocked == 1) {
                    // DO NOT SET DELETED JUST WHEN BLOCKED
                    //$itemObj->deleted = date('d-m-Y H:i:s');
                } else if($itemObj->deleted != null) {
                    $itemObj->deleted = null;
                    //echo " removedelete on ".$itemObj->no."<br>";
                } else {
                    //echo " nodeletechange, delete is ".($itemObj->deleted == null ? "not set" : $itemObj->deleted->format("d-m-Y H:i:s"))." and blocked is ".$itemObj->blocked;
                }

                $curData = json_encode($itemObj);
                if($curData != $prevData) {
                    $itemObj->is_handled = 0;
                    $itemObj->updated_at = date('d-m-Y H:i:s');
                }


                $itemObj->save();
                $usedItemNoList[$no] = true;

            } else if($no == "") {
                $countEmptyNo++;
            } else if(isset($usedItemNoList[$no])) {
                $countDoubleNo++;
            }

        }

        // Delete unused items
        foreach($itemMap as $item) {
            if($item->deleted == null) {
                //echo "<br>Delete unused item ".$item->no;
                $item->deleted = date('d-m-Y H:i:s');
                $item->save();
                $countDeletd++;
            }
        }
        
        echo "Created ".$countNew.", updated ".$countUpdated.", deleted ".$countDeletd." (".$countEmptyNo." with no item no, ".$countDoubleNo." with same item no)<br><br>";

    }

    private function synccountrybomitems($languageid)
    {

        echo "Syncing BOM items<br>";

        // Load existing items
        $itemList = \NavisionBomItem::find('all',array('conditions' => array("language_id" => $languageid)));
        $itemMap = array();
        foreach($itemList as $item) {
            $itemMap[mb_strtolower(trimgf($item->nav_key))] = $item;
        }
        echo " - Found ".countgf($itemMap)." in db<br>";

        // Load from service
        $client = new BOMItemsWS($languageid);
        $serviceItems = $client->getAllItems();
        echo " - Loaded ".countgf($serviceItems)." from navision<br>";

        if(count($serviceItems) == 0) {
            return;
            //throw new \Exception("No items loaded from navision, must be service problem.");
        }

        $countNew = 0;
        $countUpdated = 0;
        $countDeletd = 0;
        $countEmptyNo = 0;
        $countDoubleNo = 0;
        $usedItemNoList = array();

        foreach($serviceItems as $item) {


            $no = trimgf("".mb_strtolower($item->getNavKey()))."";

            if($no != "" && !isset($usedItemNoList[$no])) {

                // Update
                if(isset($itemMap[$no])) {
                    $itemObj = $itemMap[$no];
                    $itemObj->deleted = null;
                    $countUpdated++;
                    unset($itemMap[$no]);
                }

                // Create
                else {
                    $itemObj = new \NavisionBomItem();
                    $itemObj->language_id = $languageid;
                    $itemObj->created = date('d-m-Y H:i:s');
                    $itemObj->deleted = null;
                    $countNew++;
                }

                // Update all fields
                $itemObj->no = $item->getItemNo();
                $itemObj->parent_item_no = $item->getParentItemNo();
                $itemObj->nav_key = $item->getNavKey();
                $itemObj->assembly_bom = $item->getAssemblyBOM();
                $itemObj->description = $item->getDescription();
                $itemObj->unit_of_measure_code = $item->getUnitofMeasureCode();
                $itemObj->quantity_per = $item->getQuantityper();

                $itemObj->save();
                $usedItemNoList[$no] = true;

            } else if($no == "") {
                $countEmptyNo++;
            } else if(isset($usedItemNoList[$no])) {
                $countDoubleNo++;
            }


        }

        // Delete unused items
        foreach($itemMap as $item) {
            if($item->deleted == null) {
                $item->deleted = date('d-m-Y H:i:s');
                $item->save();
                $countDeletd++;
            }
        }

        echo "Created ".$countNew.", updated ".$countUpdated.", deleted ".$countDeletd." (".$countEmptyNo." with no item no, ".$countDoubleNo." with same item no)<br><br>";


    }

    /**
     * SALESPRICES
     */


    public function testsalespricesync()
    {
        $this->syncsalesprices(4);
        \system::connection()->commit();
    }

    private function syncsalesprices($languageid)
    {

        echo "Syncing sales prices<br>";

        // Load existing items
        $salespriceList = \NavisionSalesPrice::find('all',array('conditions' => array("language_id" => $languageid)));
        $priceMap = array();
        foreach($salespriceList as $price) {
            $priceMap[mb_strtolower(trim($price->nav_key))] = $price;
        }
        echo " - Found ".count($priceMap)." in db<br>";

        // Load from service
        $client = new SalesPricesWS($languageid);
        $servicePrices = $client->getAllSalesPrices();
        echo " - Loaded ".count($servicePrices)." from navision<br>";

        if(count($servicePrices) == 0) {
            echo "No prices in nav, must be service problem!";
            return;
            //throw new \Exception("No items loaded from navision, must be service problem.");
        }

        $countNew = 0;
        $countUpdated = 0;
        $countDeletd = 0;
        $countEmptyNo = 0;
        $countDoubleNo = 0;
        $usedNavKeyList = array();

        foreach($servicePrices as $price) {

            $navKey = trim("".mb_strtolower($price->getNavKey()))."";

            //echo "SALESPRICE ITEM NO: ".$price->getItemNo()."<br>";

            if($navKey != "" && !isset($usedNavKeyList[$navKey])) {

                // Update
                if(isset($priceMap[$navKey])) {
                    $priceObj = $priceMap[$navKey];
                    $countUpdated++;
                    unset($priceMap[$navKey]);

                }

                // Create
                else {
                    $priceObj = new \NavisionSalesPrice();
                    $priceObj->language_id = $languageid;
                    $priceObj->created = date('d-m-Y H:i:s');
                    $priceObj->deleted = null;
                    $countNew++;

                }

                // Update all fields
                $priceObj->item_no = $price->getItemNo();
                $priceObj->nav_key = $price->getNavKey();
                $priceObj->sales_type = $price->getSalesType();
                $priceObj->sales_code = $price->getSalesCode();
                $priceObj->minimum_quantity = $price->getMinimumQuantity();
                $priceObj->starting_date = $price->getStartingDate() == "0001-01-01" ? null : $price->getStartingDate();
                $priceObj->ending_date = $price->getEndingDate() == "0001-01-01" ? null : $price->getEndingDate();
                $priceObj->unit_of_measure = $price->getUnitofMeasureCode();
                $priceObj->unit_price = $price->getUnitPrice();
                $priceObj->price_includes_vat = $price->getPriceIncludesVAT() == true ? 1 : 0;

                // Update deleted
                if($priceObj->deleted != null) {
                    $priceObj->deleted = null;

                }

                $priceObj->save();

                $usedNavKeyList[$navKey] = true;

            } else if($navKey == "") {
                $countEmptyNo++;
            } else if(isset($usedNavKeyList[$navKey])) {
                $countDoubleNo++;
            }

        }

        // Delete unused items
        foreach($priceMap as $price) {

            $price->deleted = date('d-m-Y H:i:s');
            $price->save();
            $countDeletd++;
        }

        echo "Created ".$countNew.", updated ".$countUpdated.", deleted ".$countDeletd." (".$countEmptyNo." with no key, ".$countDoubleNo." with same key)<br><br>";

    }

    private function synccountrysalespersons($languageid)
    {

        echo "Sync salespersons";

        // Load existing persons
        $personList = \NavisionSalesperson::find('all',array('conditions' => array("language_id" => intval($languageid))));

        $personMap = array();
        foreach($personList as $person) {
            $personMap[mb_strtolower(trimgf($person->code))] = $person;
        }
        echo " - Found ".countgf($personMap)." persons in db<br>";

        // Load from service
        $client = new SalesPersonWS($languageid);
        $servicePersons = $client->getAllSalesPerson();
        echo " - Loaded ".countgf($servicePersons)." persons from navision<br>";

        if(count($servicePersons) == 0) {
            return;
            //throw new \Exception("No items loaded from navision, must be service problem.");
        }

        $countNew = 0;
        $countUpdated = 0;
        $countDeletd = 0;
        $countEmptyNo = 0;
        $countDoubleNo = 0;
        $usedPersonCodeList = array();

        foreach($servicePersons as $person) {

            $code = trimgf("".mb_strtolower($person->getCode()))."";

            if($code != "" && !isset($usedPersonCodeList[$code])) {

                // Update
                if(isset($personMap[$code])) {
                    $personObj = $personMap[$code];
                    $countUpdated++;
                    unset($personMap[$code]);
                }

                // Create
                else {
                    $personObj = new \NavisionSalesperson();
                    $personObj->language_id = $languageid;
                    $personObj->created = date('d-m-Y H:i:s');
                    $personObj->deleted = null;
                    $countNew++;
                }

                // Update all fields
                $personObj->name = $person->getName();
                $personObj->code = $person->getCode();
                $personObj->title = $person->getJobTitle();
                $personObj->email = $person->getEmail();
                $personObj->phone = $person->getPhone();
                $personObj->nav_key = $person->getKey();

                // Update deleted
                $personObj->deleted = null;

                $personObj->save();
                $usedPersonCodeList[$code] = true;

            } else if($code == "") {
                $countEmptyNo++;
            } else if(isset($usedPersonCodeList[$code])) {
                $countDoubleNo++;
            }

        }

        // Delete unused items
        foreach($personMap as $item) {
            $item->deleted = date('d-m-Y H:i:s');
            $item->save();
            $countDeletd++;
        }

        echo "Created ".$countNew.", updated ".$countUpdated.", deleted ".$countDeletd." (".$countEmptyNo." with no code, ".$countDoubleNo." with same item no)<br><br>";

    }


    private function synccountrylocations($languageid)
    {

        echo "Syncing locations<br>";

        // Load existing items
        $locationList = \NavisionLocation::find('all',array('conditions' => array("language_id" => $languageid)));
        $locationMap = array();
        foreach($locationList as $location) {
            $locationMap[mb_strtolower(trimgf($location->code))] = $location;
        }
        echo " - Found ".countgf($locationMap)." in db<br>";

        // Load from service
        $client = new LocationWS($languageid);
        $serviceLocations = $client->getAllItems();
        echo " - Loaded ".countgf($serviceLocations)." from navision<br>";

        if(count($serviceLocations) == 0) {
            return;
            //throw new \Exception("No items loaded from navision, must be service problem.");
        }

        $countNew = 0;
        $countUpdated = 0;
        $countDeletd = 0;
        $countEmptyNo = 0;
        $countDoubleNo = 0;
        $usedCodeList = array();

        foreach($serviceLocations as $location) {

            $code = trimgf("".mb_strtolower($location->getCode()))."";

            if($code != "" && !isset($usedCodeList[$code])) {

                // Update
                if(isset($locationMap[$code])) {
                    $locationObj = $locationMap[$code];
                    $countUpdated++;
                    unset($locationMap[$code]);
                }

                // Create
                else {
                    $locationObj = new \NavisionLocation();
                    $locationObj->language_id = $languageid;
                    $locationObj->created = date('d-m-Y H:i:s');
                    $locationObj->deleted = null;
                    $countNew++;
                }

                // Update all fields
                $locationObj->code = $location->getCode();
                $locationObj->name = $location->getName();
                $locationObj->nav_key = $location->getNavKey();
                $locationObj->isprimary = $location->getIsPrimary() ? 1 : 0;
                $locationObj->blocked = $location->getIsBlocked() ? 1 : 0;

                // Update deleted
                if($locationObj->deleted == null && $locationObj->blocked == 1) {
                    $locationObj->deleted = date('d-m-Y H:i:s');
                } else if($locationObj->deleted != null && $locationObj->blocked == 0) {
                    $locationObj->deleted = null;
                }

                $locationObj->save();
                $usedCodeList[$code] = true;

            } else if($code == "") {
                $countEmptyNo++;
            } else if(isset($usedCodeList[$code])) {
                $countDoubleNo++;
            }

        }

        // Delete unused items
        foreach($locationMap as $location) {
            $location->deleted = date('d-m-Y H:i:s');
            $location->save();
            $countDeletd++;
        }

        echo "Created ".$countNew.", updated ".$countUpdated.", deleted ".$countDeletd." (".$countEmptyNo." with no code, ".$countDoubleNo." with same code)<br><br>";

    }


}