<?php

namespace GFUnit\navision\magento;
use ActiveRecord\Model;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\MagItemRefreshWS;
use GFCommon\Model\Navision\MagentoWS;


class Controller extends UnitController
{

    private $magentoClient;

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function runsync() {


        \GFCommon\DB\CronLog::startCronJob("NavMagentoSync");

        // Call nav service to get updated items
        $client = new MagItemRefreshWS();
        $list = $client->getAllItems();

        echo "Found ".count($list)." items to refresh<br>";

        // Magento ws client
        $this->magentoClient = new MagentoWS();
        
        foreach($list as $item) {
            $this->syncItemStock($item);
            break;
           
        }

        // COMMIT
        \system::connection()->commit();
        \GFCommon\DB\CronLog::endCronJob(1,count($list)." items refreshed");
        
    }

    private function syncItemStock($item) {

        if(trim($item->getItemNo()) == "") {
            $this->mailProblem("Item with empty itemno: ".print_r($item,true));
            return;
        }

        try {

            $message = "Checkign item ".$item->getItemNo()." ";

            // Call total available
            $response = $this->magentoClient->getAvailableInventory($item->getItemNo(),true);
            $available = intval($response);

            // Call total available 1
            try {
                $available1 = intval($this->magentoClient->GetAvailableInventoryByType($item->getItemNo(), 1));
            } catch (\Exception $e) {
                echo "Error getting type 1 available for item " . $item->getItemNo() . " - " . $e->getMessage() . "<br>";
                $available1 = null;
            }

            // Call total available 2
            try {
                $available2 = intval($this->magentoClient->GetAvailableInventoryByType($item->getItemNo(), 2));
            } catch (\Exception $e) {
                echo "Error getting type 2 available for item " . $item->getItemNo() . " - " . $e->getMessage() . "<br>";
                $available2 = null;
            }

            $message .= "has ".$available.", ".$available1.", ".$available2." available, ";

            // Get current stock
            $stockItemList = \MagentoStockTotal::find("all",array("conditions" => array("itemno" => $item->getItemNo())));
            $stockItem = null;

            if(count($stockItemList) == 0) {
                $message .= "not found in db (create new stock row). ";
                $stockItem = new \MagentoStockTotal();
                $stockItem->itemno = $item->getItemNo();
                $stockItem->created_date = date('d-m-Y H:i:s');

            } else if(count($stockItemList) > 1) {
                $message .= "multiple stock rows found.";
                $this->mailProblem("Multiple items with item no".$item->getItemNo()." in stock table");
                return;
            } else {
                $stockItem = $stockItemList[0];
                $message .= "stock row found, updating from ".$stockItem->quantity;
            }

            $oldQuantity = $stockItem->quantity;
            $oldAvailable = $stockItem->available;
            $oldNoBlanket = $stockItem->noblanket;

            $stockItem->quantity = $available;
            $stockItem->available = $available1;
            $stockItem->noblanket = $available2;
            $stockItem->updated_date = date('d-m-Y H:i:s');
            $stockItem->save();

            // Create change log
            $stockChange = new \MagentoStockChange();
            $stockChange->itemno = $item->getItemNo();
            $stockChange->created_date = date('d-m-Y H:i:s');
            $stockChange->old_quantity = $oldQuantity;
            $stockChange->new_quantity = $available;
            $stockChange->old_available = $oldAvailable;
            $stockChange->new_available = $available1;
            $stockChange->old_noblanket = $oldNoBlanket;
            $stockChange->new_noblanket = $available2;
            $stockChange->save();

            $message .= " Save stock and stock change.<br>";
            echo $message;

        } catch (\Exception $e) {
            $this->mailProblem("Problem syncing magento item ".$item->getItemNo()." - ".$e->getMessage()."<br>".print_r($e,true));
        }

    }


    /**
     * SYNC ALL
     */

    public function runTotalSync() {

      
        // Load items
        $totalSync = \MagentoStockTotal::find("all");
        echo "Found ".count($totalSync)." items to check<br>";

        // Magento ws client
        $this->magentoClient = new MagentoWS();

        // Process items
        foreach($totalSync as $index =>  $item) {
            $this->runCheckSync($item);
            usleep(100);
        }

        \system::connection()->commit();

    }

    private function runCheckSync($item)
    {

        echo $item->itemno." current: [".$item->quantity." / ".$item->available." / ".$item->noblanket."]<br>";



        // Call total available 1
        try {
            $available1 = intval($this->magentoClient->GetAvailableInventoryByType($item->itemno, 1));
        } catch (\Exception $e) {
            echo "Error getting type 1 available for item " . $item->getItemNo() . " - " . $e->getMessage() . "<br>";
            $available1 = null;
        }

        // Call total available 2
        try {
            $available2 = intval($this->magentoClient->GetAvailableInventoryByType($item->itemno, 2));
        } catch (\Exception $e) {
            echo "Error getting type 2 available for item " . $item->itemno . " - " . $e->getMessage() . "<br>";
            $available2 = null;
        }

        // Call total available 3
        try {
            $available3 = intval($this->magentoClient->GetAvailableInventoryByType($item->itemno, 3));
        } catch (\Exception $e) {
            echo "Error getting type 3 available for item " . $item->itemno . " - " . $e->getMessage() . "<br>";
            $available3 = null;
        }

        echo "Gettings from nav [". $available1 . " / " . $available2 . " / " . $available3 . "]<br><br>";

        $changed = false;
        $oldQuantity = $item->quantity;
        $oldAvailable = $item->available;
        $oldNoBlanket = $item->noblanket;

        if($available1 !== null) {
            if($item->available != $available1) $changed = true;
            $item->available = $available1;
        }

        if($available2 !== null) {
            if($item->noblanket != $available2) $changed = true;
            $item->noblanket = $available2;
        }

        if($available3 !== null) {
            if($item->quantity != $available3) $changed = true;
            $item->quantity = $available3;
        }

        if($changed) {

            echo "WAS CHANGED; CREATE STOCK CHANGE<br><br>";
            $item->updated_date = date('d-m-Y H:i:s');
            $item->save();

            // Create change log
            $stockChange = new \MagentoStockChange();
            $stockChange->itemno = $item->itemno;
            $stockChange->created_date = date('d-m-Y H:i:s');
            $stockChange->old_quantity = $oldQuantity;
            $stockChange->new_quantity = $available3;
            $stockChange->old_available = $oldAvailable;
            $stockChange->new_available = $available1;
            $stockChange->old_noblanket = $oldNoBlanket;
            $stockChange->new_noblanket = $available2;
            $stockChange->save();

        }


    }


    private function mailProblem($problemMessage) {
        $modtager = 'sc@interactive.dk';
        //mailgf($modtager, "Magento stock sync problem", $problemMessage."\r\n<br>\r\n<br>\r\n<br>\r\n<br><pre></pre>");
    }



}