<?php

namespace GFUnit\navision\cronjob;
use GFBiz\units\UnitController;
use GFUnit\navision\synccompany\CompanySync;
use GFUnit\navision\syncorder\OrderSync;
use GFUnit\navision\syncshipment\ShipmentSync;


class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function testsecardclose() {
        
        $dailycheck = new DailyCheck();
        $dailycheck->testsecardclose();

    }

    public function dailysync() {


        $dailycheck = new DailyCheck();
        $dailycheck->runDailyCheck();


    }

    public function navsync($output=0)
    {



/*
        if($_GET["override"] ?? "" == "1") {

        } else {
            return;
        }
*/

        
        \GFCommon\DB\CronLog::startCronJob("CSNavSync");


        ini_set('memory_limit', '2056M');

        // Do not run navision jobs in this period

        if(in_array(intval(date("H")),array(3,4))) {
            echo "Do not run between 3-6<br>";
            exit();
        }


        echo "Starting nav sync:<br><br>";
        
        //return;

        // Companies
        echo "Syncing companies:<br>";
        $companySyncModel = new CompanySync(intval($output) == 1);
        $companySyncModel->syncAllJob();

        // Orders
        echo "<br><hr><br>Syncing orders:<br>";
        $orderSyncModel = new OrderSync(intval($output) == 1);
        $orderSyncModel->syncAllJob();


        // Shipments

        echo "<br><hr><br>Syncing shipments:<br>";
        $shipmentSyncModel = new ShipmentSync(intval($output) == 1);
        $shipmentSyncModel->syncAllJob();


        // Acknowledge queue run
        try {
            $this->acknowledgeRun(1);
            $this->acknowledgeRun(4);
            $this->acknowledgeRun(5);
        } catch (\Exception $e) {
            echo "Error acknowledge: ".$e->getMessage();
        }

        // Update earlyorders not ready to send
        $this->updateEarlyOrdersState();

        // Update last run time
        $this->updateLastRunFile();

        \GFCommon\DB\CronLog::endCronJob(1,"OK");
        \response::silentsuccess();

        if($output == 1) {
            // Output script to reload the page
            //echo "<script>setTimeout(function(){location.reload();},1000);</script>";
        }

    }

    private function updateEarlyOrdersState() {

        $earlyorderList = \Shipment::find_by_sql("SELECT * FROM `shipment` WHERE `shipment_type` LIKE 'earlyorder' && shipment_state = 0 && created_date < now() - INTERVAL 1 DAY");
        $earlyorderUpdates = 0;

        foreach($earlyorderList as $earlyorder) {
            $shipment = \Shipment::find($earlyorder->id);
            if($shipment->id > 0 && $shipment->shipment_type == 'earlyorder' && $shipment->shipment_state == 0) {
                $shipment->shipment_state = 1;
                $shipment->save();
                $earlyorderUpdates++;
            }
        }

        echo "<br>Updated ".$earlyorderUpdates." earlyorders<br>";

    }

    private function updateLastRunFile() {
        file_put_contents("units/navision/cronjob/last-nav-cron-run.log",time());
    }

    private function acknowledgeRun($language_code) {
        $client = new \GFCommon\Model\Navision\OrderWS(intval($language_code));
        $client->acknowledge();
    }

    public function cleancalllog() {


        \GFCommon\DB\CronLog::startCronJob("NavCleanLog");

        $output = "Cleanup nav_call_log, start at ".date("d-m-Y H:i:s");
        $time = time();

        // Remove all old calls over 6 months
        ExecuteSQL("DELETE FROM navision_call_log where created < NOW() - INTERVAL 6 MONTH");
        $output .= "TICK: ".(time()-$time)."<br>";

        // Remove intensive calls over 1 month old
        ExecuteSQL("DELETE FROM navision_call_log where service = 'Read' AND url LIKE '%Page/GKOrderStatusWS' AND created < NOW() - INTERVAL 1 MONTH");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'ReadMultiple' AND url LIKE '%Page/GKExternalShipStatusWS' AND created < NOW() - INTERVAL 14 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'GetInventoryByType' AND url LIKE '%/Codeunit/MagentoWS' AND created < NOW() - INTERVAL 7 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'GetAvailableInventory' AND url LIKE '%/Codeunit/MagentoWS' AND created < NOW() - INTERVAL 7 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'Read' AND url LIKE '%/Page/CSShipmentsWS' AND created < NOW() - INTERVAL 14 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'Read' AND url LIKE '%Page/GKItemsWS' AND created < NOW() - INTERVAL 14 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'Acknowledge' AND created < NOW() - INTERVAL 7 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'ReadMultiple' AND url LIKE '%/Page/SalesPricesWS' AND created < NOW() - INTERVAL 7 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'GetMail' AND url LIKE '%/Codeunit/GFMailWS' AND created < NOW() - INTERVAL 1 MONTH");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'ReadMultiple' AND url LIKE '%/Page/GFMailListWS' AND created < NOW() - INTERVAL 1 MONTH");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'CheckOrderVersion' AND url LIKE '%/Codeunit/GKOrderWS' AND created < NOW() - INTERVAL 1 MONTH");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'Read' AND url LIKE '%/Page/GKCustomersWS' AND created < NOW() - INTERVAL 14 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'ReadMultiple' AND url LIKE '%/Page/GKCustomersWS' AND created < NOW() - INTERVAL 7 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'ReadMultiple' AND url LIKE '%/Page/GKItemsWS' AND created < NOW() - INTERVAL 7 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where service = 'ReadMultiple' AND url LIKE '%/Page/MagItemRefresh' AND created < NOW() - INTERVAL 7 DAY");
        $output .= "TICK: ".(time()-$time)."<br>";
        
        /*

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 5 DAY && service = 'ReadMultiple' && url LIKE '%/Page/SalesPricesWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 14 DAY && service = 'GetMail' && url LIKE '%/Codeunit/GFMailWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 14 DAY && service = 'CheckOrderVersion' && url LIKE '%/Codeunit/GKOrderWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 7 DAY && service = 'ReadMultiple' && url LIKE '%/Page/GFMailListWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 7 DAY && service = 'ReadMultiple' && url LIKE '%/Page/GKBOMItemsWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 14 DAY && service = 'Read' && url LIKE '%/Page/GKCustomersWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 7 DAY && service = 'ReadMultiple' && url LIKE '%/Page/GKCustomersWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 7 DAY && service = 'ReadMultiple' && url LIKE '%/Page/GKItemsWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 30 DAY && service = 'Read' && url LIKE '%/Page/GKOrderStatusWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 7 DAY && service = 'ReadMultiple' && url LIKE '%/Page/GKExternalShipStatusWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("UPDATE navision_call_log set response = 'REMOVED' where response != 'REMOVED' && created < NOW() - INTERVAL 30 DAY && service = 'GetShipmentDocument' && url LIKE '%/Codeunit/GKOrderWS'");
        $output .= "TICK: ".(time()-$time)."<br>";

        ExecuteSQL("DELETE FROM navision_call_log where created < NOW() - INTERVAL 30 DAY && service = 'Acknowledge' && url LIKE '%/Codeunit/GKOrderWS'");
        $output .= "TICK: ".(time()-$time)."<br>";
        */

        if(time()-$time > 60*40 || date('w') === '1') {
            $output .= "Truncating is getting a bit long, migth want to look at it.";
            mailgf("sc@interactive.dk", "Cleanup nav_call_log", $output,null);
        }

        \GFCommon\DB\CronLog::endCronJob(1,"OK",null,$output);
        
    }

}