<?php

namespace GFUnit\valgshop\navision;
use GFBiz\units\UnitController;
use GFBiz\valgshop\OrderBuilder;
use GFBiz\valgshop\ShopOrderModel;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\VSOrderWS;


class NavSyncJob
{


    private $output = false;
    private $logs = [];

    private $MAX_SYNC_COUNT = 1;

    private $DEBUG = true;

    private $DRY_RUN = false;

    private $NAV_DEV = false;

    public function __construct()
    {

    }

    public function showWaiting()
    {

        $this->output = true;
        $this->log("Tjekker afventende ordre");

        // Make sure all vs_states are created
        $this->checkVSStates();

        // Update needs sync
        $this->updateNeedsSync();

        // Get next order to process
        $this->loadWaitingShops();

        $this->log("Found ".count($this->waitingSyncShopIDs)." waiting shops to sync");

        foreach($this->waitingSyncShopIDs as $shopID) {

            $shop = \Shop::find($shopID);
            echo " - Shop: ".$shop->id." - ".$shop->name."<br>";
        }

    }

    public function run($output=false)
    {




        $this->output = $output;
        $this->logs = [];

        $this->log("Starting Valgshop NavSync");

        // Make sure all vs_states are created
        $this->checkVSStates();

        // Update needs sync
        $this->updateNeedsSync();

        // Get next order to process
        $this->loadWaitingShops();
        $this->log("Found ".count($this->waitingSyncShopIDs)." waiting shops to sync");

        // Sync shops
        foreach($this->waitingSyncShopIDs as $index => $shopID) {

            if($shopID == 7649) {
                $this->syncShopID($shopID);
                if($index >= $this->MAX_SYNC_COUNT) {
                    break;
                }
            }
        }

        $this->acknowledgeRun(1);

    }


    private function acknowledgeRun($language_code) {
        echo "<br>Run acknowledge on ".$language_code."<br>";
        $client = new \GFCommon\Model\Navision\VSOrderWS(intval($language_code));
        $client->acknowledge();
    }

    /**
     * SYNC SHOP
     */

    private function syncShopID($ShopID) {

        try {

            $shopSync = new NavSyncShop($ShopID,$this->output,$this->NAV_DEV,$this->DRY_RUN);
            $shopSync->runSync();
        }
        catch(\Exception $e) {
            $this->log("Error: ".$e->getMessage().($this->DEBUG ? "File: ".$e->getFile()." Line: ".$e->getLine()."<br>".$e->getTraceAsString() : ""));
        }
    }

    /**
     * GET ORDER TO SYNC
     */

    private $waitingSyncShopIDs = [];

    public function loadWaitingShops() {

        $syncLanguages = array(1);

        $sql = "SELECT s.id FROM `shop_approval` sa, shop s, navision_vs_state ns WHERE
        sa.shop_id = s.id and sa.orderdata_approval = 2 AND s.id not in (select shop_id from shop_block_message where release_status = 0) and s.localisation in (".implode(",",$syncLanguages).") and
        ns.shop_id = s.id and ns.state in (0,1,3,5) and ns.on_hold = 0 and (ns.needs_sync = 1 OR ns.state in (0,3));";

        $this->waitingSyncShopIDs = [];
        $waiting = \Shop::find_by_sql($sql);
        if(countgf($waiting) > 0) {
            foreach($waiting as $shop) {
                $this->waitingSyncShopIDs[] = $shop->id;
            }
        }

        return $this->waitingSyncShopIDs;

    }



    /**
     * CREATE VS STATES
     */

    private function checkVSStates()
    {

        $this->log("Checking VS States");

        try {

            // Find shop approvals
            $sql = "SELECT * FROM `shop_approval` where orderdata_approval = 2 && shop_id not in (select shop_id from navision_vs_state);";
            $shopApprovalList = \ShopApproval::find_by_sql($sql);

            // Create shop approvals
            if(countgf($shopApprovalList) > 0) {
                foreach($shopApprovalList as $shopApproval) {
                    $this->log("- Creating VS State for shop_id: ".$shopApproval->shop_id);
                    $vsState = new \NavisionVsState();
                    $vsState->shop_id = $shopApproval->shop_id;
                    $vsState->save();
                }

                $this->log("- Created ".count($shopApprovalList)." VS States");

                // Commit and open new transaction
                \system::connection()->commit();
                \System::connection()->transaction();

            }

        } catch (\Exception $e) {
            $this->log("Error: ".$e->getMessage());
            return false;
        }

    }

    /**
     * HELPERS
     */

    private function log($string) {

        $this->logs[] = $string;
        if($this->output || $this->DEBUG) {
            echo $string."<br>\r\n";
        }
    }

    public function updateNeedsSync()
    {

        // Updates shops where run_date and check_date is null or where updated on shop or shop_metadata is newer than last run date or check date
        $sql = "UPDATE navision_vs_state nvs
JOIN shop s ON nvs.shop_id = s.id
JOIN shop_metadata sm ON s.id = sm.shop_id
SET nvs.needs_sync = 1
WHERE 
(nvs.last_run_date IS NULL AND nvs.last_run_check IS NULL) OR
sm.updated_datetime > GREATEST(COALESCE(nvs.last_run_date, '1900-01-01'), COALESCE(nvs.last_run_check, '1900-01-01'))
OR s.updated_datetime > GREATEST(COALESCE(nvs.last_run_date, '1900-01-01'), COALESCE(nvs.last_run_check, '1900-01-01'));";

        \Dbsqli::setSql2($sql);

    }


}