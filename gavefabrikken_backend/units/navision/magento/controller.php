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

    public function runsync()
    {

        \GFCommon\DB\CronLog::startCronJob("NavMagentoSync");

        $this->runsyncLanguage(1);
        $this->runsyncLanguage(4);

        \GFCommon\DB\CronLog::endCronJob(1," items refreshed");
        \system::connection()->commit();

    }




    public function bootstock() {

        $language = 4;

        $this->magentoClient = new MagentoWS($language);
        $items = \NavisionItem::find_by_sql("select * from navision_item where language_id = ".$language);
        echo "FANDT ".count($items)." VARENR!<br>";

        $checkMax = 2000;
        $checked = 0;

        foreach ($items as $ind => $item) {



            // Get navision stock
            $navStockItemList = \NavisionStockTotal::find("all",array("conditions" => array("language_id" => $language,"itemno" => $item->no)));
            $navStockItem = null;

            if(count($navStockItemList) == 0) {

                echo "[$ind/".count($items)."] check ".$item->no."<br>";

                // Call total available
                $response = $this->magentoClient->getAvailableInventory($item->no,true);
                $available = intval($response);

                // Call total available 1
                try {
                    $available1 = intval($this->magentoClient->GetAvailableInventoryByType($item->no, 1));
                } catch (\Exception $e) {
                    echo "Error getting type 1 available for item " . $item->no . " - " . $e->getMessage() . "<br>";
                    $available1 = null;
                }

                // Call total available 2
                try {
                    $available2 = intval($this->magentoClient->GetAvailableInventoryByType($item->no, 2));
                } catch (\Exception $e) {
                    echo "Error getting type 2 available for item " . $item->no . " - " . $e->getMessage() . "<br>";
                    $available2 = null;
                }

                //if($available != 0 && $available1 != 0 && $available2 != 0) {

                    $navStockItem = new \NavisionStockTotal();
                    $navStockItem->itemno = $item->no;
                    $navStockItem->created_date = date('d-m-Y H:i:s');

                    $navStockItem->language_id = $language;
                    $navStockItem->quantity = $available;
                    $navStockItem->available = $available1;
                    $navStockItem->noblanket = $available2;
                    $navStockItem->updated_date = date('d-m-Y H:i:s');
                    $navStockItem->save();



                //}

                $checked++;
                if($checked >= $checkMax) {
                    echo "STOP!";
                    break;
                }

            }

        }

        \system::connection()->commit();



    }

    public function runsyncLanguage($languageid) {

        // Call nav service to get updated items
        $client = new MagItemRefreshWS($languageid);
        $list = $client->getAllItems();
        echo "Found ".count($list)." items to refresh<br>";

        // Magento ws client
        $this->magentoClient = new MagentoWS($languageid);
        
        foreach($list as $item) {
            $this->syncItemStock($item,$languageid);
            break;
        }
        
    }

    private function syncItemStock($item,$languageid) {

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

            // Get navision stock
            $navStockItemList = \NavisionStockTotal::find("all",array("conditions" => array("language_id" => $languageid,"itemno" => $item->getItemNo())));
            $navStockItem = null;

            if(count($navStockItemList) == 0) {
                $message .= "not found in db (create new stock row). ";
                $navStockItem = new \NavisionStockTotal();
                $navStockItem->itemno = $item->getItemNo();
                $navStockItem->created_date = date('d-m-Y H:i:s');

            } else if(count($navStockItemList) > 1) {
                $message .= "multiple stock rows found.";
                $this->mailProblem("Multiple items with item no".$item->getItemNo()." in stock table");
                return;
            } else {
                $navStockItem = $navStockItemList[0];
                $message .= "stock row found, updating from ".$navStockItem->quantity;
            }

            $oldQuantity = $navStockItem->quantity;
            $oldAvailable = $navStockItem->available;
            $oldNoBlanket = $navStockItem->noblanket;

            $navStockItem->language_id = $languageid;
            $navStockItem->quantity = $available;
            $navStockItem->available = $available1;
            $navStockItem->noblanket = $available2;
            $navStockItem->updated_date = date('d-m-Y H:i:s');
            $navStockItem->save();
            
            // Update magento stock
            if($languageid == 1) {

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


            }

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


    /**
     * OFFSYNC IS SYNCING THE ITEMS WE HAVE NOT SEEN IN THE NAV SERVICE
     */


    public function runoffsync() {

        // Get items to sync

        $this->runoffsyncLanguage(1);

    }

    private function runoffsyncLanguage($languageid) {

        // Magento ws client
        $this->magentoClient = new MagentoWS($languageid);

        $items = \NavisionReservationLog::find_by_sql("SELECT itemno, language_id, COUNT(id) AS antal_poster, MIN(created) AS tidligste_dato, MAX(created) AS seneste_dato FROM navision_reservation_log WHERE itemno not in (select itemno from navision_stock_total where ) itemno NOT IN ( SELECT itemno FROM magento_stock_change WHERE created_date > DATE_SUB(NOW(), INTERVAL 1 HOUR) ) and language_id = ".intval($languageid)." GROUP BY itemno, language_id ORDER BY COUNT(id) DESC;");
        echo "Found ".count($items)." items to check<br>";

        // order randomly
        shuffle($items);

        $processed = 0;
        foreach($items as $item) {

            echo "Processing item ".$item->itemno." (".$item->antal_poster." reservations) from ".$item->tidligste_dato." to ".$item->seneste_dato."<br>";
            $processed++;

            $this->syncOffItemStock($item->itemno,$languageid);
            sleep(1);

            if($processed > 50) {
                echo "Stopping after 50 items processed.<br>";
                break;
            }

        }

        \system::connection()->commit();

            /*

        // Get items to sync
        $items = \MagentoStockTotal::find("all",array("conditions" => array("language_id" => $languageid)));

        echo "Found ".count($items)." items to check<br>";

        foreach($items as $item) {
            $this->runOffCheckSync($item->itemno,$languageid);
            usleep(100);
        }

        \system::connection()->commit();
*/


    }

    private function syncOffItemStock($itemno,$languageid) {

        if(trim($itemno) == "") {
            $this->mailProblem("Item with empty itemno: ".$itemno);
            return;
        }

        try {

            $message = "Checkign item ".$itemno." ";

            // Call total available
            $response = $this->magentoClient->getAvailableInventory($itemno,true);
            $available = intval($response);

            // Call total available 1
            try {
                $available1 = intval($this->magentoClient->GetAvailableInventoryByType($itemno, 1));
            } catch (\Exception $e) {
                echo "<span style='color: red;'>Error getting type 1 available for item " . $itemno . " - " . $e->getMessage() . "</span><br>";
                $available1 = null;
            }

            // Call total available 2
            try {
                $available2 = intval($this->magentoClient->GetAvailableInventoryByType($itemno, 2));
            } catch (\Exception $e) {
                echo "<span style='color: red;'>Error getting type 2 available for item " . $itemno . " - " . $e->getMessage() . "</span><br>";
                $available2 = null;
            }

            $message .= "has ".$available.", ".$available1.", ".$available2." available, ";

            // Get navision stock
            $navStockItemList = \NavisionStockTotal::find("all",array("conditions" => array("language_id" => $languageid,"itemno" => $itemno)));
            $navStockItem = null;

            if(count($navStockItemList) == 0) {
                $message .= "not found in db (create new stock row). ";
                $navStockItem = new \NavisionStockTotal();
                $navStockItem->itemno = $itemno;
                $navStockItem->created_date = date('d-m-Y H:i:s');

            } else if(count($navStockItemList) > 1) {
                $message .= "multiple stock rows found.";
                $this->mailProblem("Multiple items with item no".$itemno." in stock table");
                return;
            } else {
                $navStockItem = $navStockItemList[0];
                $message .= "stock row found, updating from ".$navStockItem->quantity;
            }

            $oldQuantity = $navStockItem->quantity;
            $oldAvailable = $navStockItem->available;
            $oldNoBlanket = $navStockItem->noblanket;

            if($oldQuantity != $available || $oldAvailable != $available1 || $oldNoBlanket != $available2) {
                echo "WAS CHANGED; CREATE STOCK CHANGE (".($oldQuantity-$available).",".($oldAvailable-$available1).",".($oldNoBlanket-$available2).")<br><br>";
            }

            $navStockItem->language_id = $languageid;
            $navStockItem->quantity = $available;
            $navStockItem->available = $available1;
            $navStockItem->noblanket = $available2;
            $navStockItem->updated_date = date('d-m-Y H:i:s');
            $navStockItem->last_offsync_date = date('d-m-Y H:i:s');
            $navStockItem->save();

            // Update magento stock
            if($languageid == 1) {

                // Get current stock
                $stockItemList = \MagentoStockTotal::find("all",array("conditions" => array("itemno" => $itemno)));
                $stockItem = null;

                if(count($stockItemList) == 0) {
                    $message .= "not found in db (create new stock row). ";
                    $stockItem = new \MagentoStockTotal();
                    $stockItem->itemno = $itemno;
                    $stockItem->created_date = date('d-m-Y H:i:s');

                } else if(count($stockItemList) > 1) {
                    $message .= "multiple stock rows found.";
                    $this->mailProblem("Multiple items with item no".$itemno." in stock table");
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
                $stockChange->itemno = $itemno;
                $stockChange->created_date = date('d-m-Y H:i:s');
                $stockChange->old_quantity = $oldQuantity;
                $stockChange->new_quantity = $available;
                $stockChange->old_available = $oldAvailable;
                $stockChange->new_available = $available1;
                $stockChange->old_noblanket = $oldNoBlanket;
                $stockChange->new_noblanket = $available2;
                $stockChange->save();


            }

            $message .= " Save stock and stock change.<br>";
            echo $message;

        } catch (\Exception $e) {
            $this->mailProblem("Problem syncing magento item ".$itemno." - ".$e->getMessage()."<br>".print_r($e,true));
        }



    }


    public function balancecheck() {

        $checkItems = 50;
        echo "BALANCE CHECK ON ".$checkItems." ITEMS<br>";

        $client = new MagentoWS(1);
        $this->magentoClient = new MagentoWS(1);

        $items = \NavisionStockTotal::find_by_sql("SELECT * FROM navision_stock_total where language_id = 1 ORDER BY RAND() LIMIT " . intval($checkItems));
        echo "FOUND ".count($items)." ITEMS<br>";

        foreach($items as $item) {

            echo "Checking item ".$item->itemno." with stock ".$item->quantity."<br>";

            $available = intval($client->getAvailableInventory($item->itemno,true));
            echo "Magento ws says available is ".$available."<br>";

            if($available != $item->quantity) {

                echo "BALANCE ERROR - UPDATING STOCK<br>";
                $this->syncOffItemStock($item->itemno, 1);

            } else {
                echo "Balance OK<br>";
            }



            echo "<br>";

        }

        \system::connection()->commit();

    }

    public function shopbalancecheck() {

        // Magento ws client
        $this->magentoClient = new MagentoWS(1);


        $sql = "select * from present_model where language_id =1 and present_id in (select id from present where shop_id in (8169,8246,8263,8280,8309,8335,8390,8493,8571,8806,8826,8857,8940,8947,8964,9033,9130,9142,9247,9479,9544))";
        $models = \PresentModel::find_by_sql($sql);

        echo "Fandt ".count($models)." varer i shop balance check<br>";

        $client = new MagentoWS(1);

        foreach($models as $index => $model) {


            $vareNr = $model->model_present_no;
            echo "Checking item ".$vareNr."<br>";

            // Find navision stock total
            $stockTotals = \NavisionStockTotal::find_by_sql("select * from navision_stock_total where itemno LIKE '".$vareNr."' and language_id = 1");
            if(count($stockTotals) == 0) {
                echo "<span style='color: red;'>No navision stock total found for item ".$vareNr."</span><br>";
                $this->syncOffItemStock($vareNr,1);

            } else if(count($stockTotals) > 1) {

                $stockTotal = $stockTotals[0];
                echo " - Navision stock total says available is ".$stockTotal->available.", ".$stockTotal->quantity.", ".$stockTotal->noblanket."<br>";

                $available = intval($client->getAvailableInventory($vareNr,true));

                // Call total available 1
                try {
                    $available1 = intval($client->GetAvailableInventoryByType($vareNr, 1));
                } catch (\Exception $e) {
                    echo "<span style='color: red;'>Error getting type 1 available for item " . $vareNr . " - " . $e->getMessage() . "</span><br>";
                    $available1 = null;
                }

                // Call total available 2
                try {
                    $available2 = intval($client->GetAvailableInventoryByType($vareNr, 2));
                } catch (\Exception $e) {
                    echo "<span style='color: red;'>Error getting type 2 available for item " . $vareNr . " - " . $e->getMessage() . "</span><br>";
                    $available2 = null;
                }

                echo "- Magento ws says available is ".$available."<br>";
                echo "- Magento ws says available1 is ".$available1."<br>";
                echo "- Magento ws says available2 is ".$available2."<br>";

                if($available != $stockTotal->quantity || $available1 != $stockTotal->available || $available2 != $stockTotal->noblanket) {

                    $this->syncOffItemStock($vareNr,1);
                    echo "<span style='color: red;'>BALANCE ERROR - UPDATING STOCK</span><br>";
                    //

                } else {
                    echo "Balance OK<br>";
                }


            }




        }

        \system::connection()->commit();

    }

}