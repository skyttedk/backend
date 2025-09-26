<?php


namespace GFUnit\navision\syncreservations;

use GFCommon\Model\Navision\SalesHeaderWS;
use GFCommon\Model\Navision\SalesLineWS;
use GFCommon\Model\Navision\SalesShipmentHeadersWS;
use GFCommon\Model\Navision\SalesShipmentLinesWS;
use ActiveRecord\DateTime;
use GFCommon\DB\DBAccess;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\OrderWS;

class CounterSO
{

    public function __construct()
    {

    }



    public function bscounter() {

        
        if(isset($_GET["shopid"]) && intval($_GET["shopid"]) > 0) {

            echo "RUN FOR SHOP: ".$_GET["shopid"]."<br>";

            $shopid = intval($_GET["shopid"]);

            // FIND ALLE RESERVATION DONE ON BS NR AND REMOVE THEM!

            // Load reservation done
            $sql = "SELECT * FROM navision_reservation_done where shop_id = ".$shopid." && sono LIKE 'BS%'";
            $reservationList = \NavisionReservationDone::find_by_sql($sql);

            echo "Found ".count($reservationList)." reservations<br>";
            $totalDone = 0;

            foreach($reservationList as $reservationDone) {

                // Reservation done item load
                $sql = "SELECT * FROM navision_reservation_done_item where reservation_done_id = ".$reservationDone->id;
                $reservationDoneItems = \NavisionReservationDoneItem::find_by_sql($sql);
                echo "Reservation: ".$reservationDone->id." - ".$reservationDone->sono." - ".$reservationDone->done." - ".count($reservationDoneItems)." items<br>";
                $totalDone += $reservationDone->done;

                foreach($reservationDoneItems as $rdi) {
                    $rdid = \NavisionReservationDoneItem::find($rdi->id);
                    $rdid->delete();
                }

                $rdd = \NavisionReservationDone::find($reservationDone->id);
                $rdd->delete();

            }

            echo "Total done: ".$totalDone."<br>";


            // Find item numbers in shop
            $varenrList = array();
            $presentReservationList = \PresentReservation::find("all",array("conditions" => array("shop_id" => intval($shopid))));
            foreach($presentReservationList as $presentReservation) {

                // Find varenr
                $presentModel = \PresentModel::find_by_sql("SELECT * FROM present_model where language_id = 1 && model_id=" . $presentReservation->model_id);
                if (count($presentModel) == 0) {
                    echo "ERROR: Present model not found: " . $presentReservation->model_id . "<br>";
                    exit();
                }

                $presentModel = $presentModel[0];
                $varenr = $presentModel->model_present_no;
                $varenrList[] = trim(strtolower($varenr));

            }

            echo "Varenr liste: <br><pre>";
            print_r($varenrList);
            echo "</pre><br>";

            // Checking varenr
            echo "<br><br>Checking varenr<br><br>";

            // Go through done and find problems with item nos
            $sql = "SELECT * FROM navision_reservation_done where shop_id = ".$shopid."";
            $reservationDones = \NavisionReservationDone::find_by_sql($sql);
            foreach($reservationDones as $done) {

                $problem = !in_array(trim(strtolower($done->itemno)), $varenrList) && !in_array(trim(strtolower($done->bomno)), $varenrList);

                if($problem) {
                    echo "DONE: ".$done->id." - ".$done->sono." - <b>".$done->itemno."</b> / ".$done->bomno." - ".$done->quantity." - ".$done->done." - ".($problem ? "PROBLEM" : "no problemos")."<br>";


                    $navisionBomItems = \NavisionBomItem::find_by_sql("select * from navision_bomitem where no like '".trim(strtolower($done->itemno))."' && deleted is null && language_id = 1");

                    if(count($navisionBomItems) > 0) {

                        foreach($navisionBomItems as $bomItem) {
                            if(in_array(trim(strtolower($bomItem->parent_item_no)), $varenrList)) {
                                echo "- BOM ITEM FOUND: ".$bomItem->no." -> ".$bomItem->parent_item_no." <br>";
                                $doneObj = \NavisionReservationDone::find($done->id);
                                $doneObj->bomno = $bomItem->parent_item_no;
                                $doneObj->bomquantity = $done->quantity/$bomItem->quantity_per;
                                $doneObj->save();
                                echo " - SET: ".$doneObj->bomno." - ".$doneObj->bomquantity."<br>";
                            }
                        }

                        if($doneObj->bomno == "") {
                            echo "NO BOM ITEM FOUND<br>";
                            exit();
                        }


                    } else {
                        echo "NO BOM ITEM FOUND<br>";
                        exit();
                    }
                }


            }

            // Find done count
            $doneSumMap = array();

            // Find done count
            $sql = "SELECT sum(done) as itemquantity, count(id), itemno, bomno, sum(bomquantity) as bomquantity FROM navision_reservation_done where shop_id = ".$shopid." && sono NOT LIKE 'BS%' group by itemno, bomno";
            $reservationDoneSums = \NavisionReservationDone::find_by_sql($sql);
            foreach($reservationDoneSums as $doneSum) {

                if(trim($doneSum->bomno) != "") {
                    $doneSumMap[trim(strtolower($doneSum->bomno))] = $doneSum->bomquantity;
                } else {
                    $doneSumMap[trim(strtolower($doneSum->itemno))] = $doneSum->itemquantity;
                }

            }

            // Print done sum map with key and value
            echo "<table><tr><td>Itemno</td><td>Done</td></tr>";
            foreach($doneSumMap as $key => $value) {
                echo "<tr><td>".$key."</td><td>".$value."</td></tr>";
            }
            echo "</table><br>";

            echo "<br>Start processing reservations:<br>";

            // Load reservations
            $presentReservationList = \PresentReservation::find("all",array("conditions" => array("shop_id" => intval($shopid))));
            foreach($presentReservationList as $presentReservation) {

                // Find varenr
                $presentModel = \PresentModel::find_by_sql("SELECT * FROM present_model where language_id = 1 && model_id=".$presentReservation->model_id);
                if(count($presentModel) == 0) {
                    echo "ERROR: Present model not found: ".$presentReservation->model_id."<br>";
                    exit();
                }

                $presentModel = $presentModel[0];
                $varenr = $presentModel->model_present_no;

                // Find done
                $done = null;
                if(isset($doneSumMap[trim(strtolower($varenr))])) {
                    $done = $doneSumMap[trim(strtolower($varenr))];
                }

                // Find done count
                echo "Reservation ".$presentReservation->id." - ".$presentReservation->quantity." / <b>".$presentReservation->quantity_done."</b> / ".$presentReservation->sync_quantity." - ".$varenr." - ".($done === null ? "DONE NOT FOUND" : $done)."<br>";
                if($done != $presentReservation->quantity_done) {

                    echo " - UPDATE DONE ".$presentReservation->id.": ".$presentReservation->quantity_done." to ".($done === null ? 0 : $done)."<br>";

                    // Update done count on present reservation
                    $pru = \PresentReservation::find($presentReservation->id);
                    $presentReservation->quantity_done = ($done === null ? 0 : $done);

                    if($presentReservation->quantity_done > $presentReservation->quantity) {
                        $this->mailLog("RES 11: Quantity done is higher than quantity to revert: ".$presentReservation->id." / ".$presentReservation->quantity." / ".$presentReservation->quantity_done."<br><pre>".print_r($revertDoneItem,true)."</pre>");
                        exit();
                    }
                    else if($presentReservation->quantity_done < 0) {
                        $this->mailLog("RES 11: Quantity done is lower than 0: ".$presentReservation->id." / ".$presentReservation->quantity." / ".$presentReservation->quantity_done."<br><pre>".print_r($revertDoneItem,true)."</pre>");
                        exit();
                    }

                    $presentReservation->save();

                }

            }

            // Commit db
            //\System::connection()->commit();


        } else {
            
        
            $sql = "select * from cardshop_settings where language_code = 4";
            $shops = \CardshopSettings::find_by_sql($sql);

            echo "<table><tr><td>Shop</td><td>Name</td><td>Language</td></tr>";
            foreach($shops as $shop) {
                echo "<tr><td>".$shop->shop_id."</td><td>".$shop->concept_code."</td><td>".$shop->language_code."</td><td><a href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/bscounter&shopid=".$shop->shop_id."'>RUN!</a></td></tr>";
            }
            echo "</table>";


        }
        
    }
    
    
    
    

    public function notcountered()
    {

        try {
        $sql = "SELECT shop.id as ShopID, shop.name as Shop, GROUP_CONCAT(sono) as SOno, itemno as Varenr, SUM(quantity) as AntalPaaSO, SUM(done) AntalUdlignet, SUM(quantity)-SUM(done) as Dif FROM `navision_reservation_done`, shop where shop.id = navision_reservation_done.shop_id && quantity - done != 0  group by shop.id, navision_reservation_done.itemno ORDER BY `navision_reservation_done`.`shop_id` ASC;";

        $list = \NavisionReservationDone::find_by_sql($sql);

        echo "<table><tr><td>ID</td><td>Shop</td><td>Itemno</td><td>Antal på SO</td><td>Antal udlignet</td><td>Dif</td><td>SO</td></tr>";

        foreach ($list as $item) {
            echo "<tr><td>" . $item->shopid . "</td><td>" . $item->shop . "</td><td>" . $item->varenr . "</td><td>" . $item->antalpaaso . "</td><td>" . $item->antaludlignet . "</td><td>" . $item->dif . "</td><td>" . $item->sono . "</td></tr>";
        }

        echo "</table>";

        }
        catch(\Exception $e) {
            echo "Exception: ".$e->getMessage()." at ".$e->getFile()." line ".$e->getLine()."";
        }
    }

    public function iscountered()
    {

        try {
            $sql = "SELECT shop.id as ShopID, shop.name as Shop, GROUP_CONCAT(sono) as SOno, itemno as Varenr, SUM(quantity) as AntalPaaSO, SUM(done) AntalUdlignet, SUM(quantity)-SUM(done) as Dif FROM `navision_reservation_done`, shop where shop.id = navision_reservation_done.shop_id && quantity - done = 0  group by shop.id, navision_reservation_done.itemno ORDER BY `navision_reservation_done`.`shop_id` ASC;";

            $list = \NavisionReservationDone::find_by_sql($sql);

            echo "<table><tr><td>ID</td><td>Shop</td><td>Itemno</td><td>Antal på SO</td><td>Antal udlignet</td><td>Dif</td><td>SO</td></tr>";

            foreach ($list as $item) {
                echo "<tr><td>" . $item->shopid . "</td><td>" . $item->shop . "</td><td>" . $item->varenr . "</td><td>" . $item->antalpaaso . "</td><td>" . $item->antaludlignet . "</td><td>" . $item->dif . "</td><td>" . $item->sono . "</td></tr>";
            }

            echo "</table>";

        }
        catch(\Exception $e) {
            echo "Exception: ".$e->getMessage()." at ".$e->getFile()." line ".$e->getLine()."";
        }
    }


    public function rundone() {

        $sql = "SELECT shop_id, count(id) as itemcount, sum(quantity - done) as quantity FROM `navision_reservation_done` WHERE quantity != done GROUP BY shop_id";
        $shops = \NavisionReservationDone::find_by_sql("SELECT concept_code, navision_reservation_done.shop_id, count(navision_reservation_done.id) as itemcount, sum(quantity - done) as quantity FROM `navision_reservation_done`, cardshop_settings WHERE navision_reservation_done.shop_id = cardshop_settings.shop_id && quantity != done GROUP BY shop_id");

        echo "<table><tr><td>Shop</td><td>Items</td><td>Quantity</td></tr>";
        foreach($shops as $shop) {
            echo "<tr><td>".$shop->concept_code."</td><td>".$shop->shop_id."</td><td>".$shop->itemcount."</td><td>".$shop->quantity."</td><td><a href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/counterdone&shopid=".$shop->shop_id."'>RUN!</a></td></tr>";
        }
        echo "</table>";

        if(intval($_GET["shopid"] ?? 0) > 0) {
            foreach($shops as $shop) {
                if(intval($_GET["shopid"]) == $shop->shop_id) {
                    $this->runDoneShop($shop->shop_id);

                    \System::connection()->commit();
                    exit();
                }
            }
        }

    }


    private $doneAvailable;
    private $updatedReservationList = [];

    private function runDoneShop($shopid)
    {

        $supressOutput = isset($_GET["supress"]) && $_GET["supress"] == 1;
        if($supressOutput) ob_start();

        ?><style> td { border-top: 1px solid #AAAAAA; }</style><?php

        echo "<h3>Processing shop ".$shopid."</h3><br>";

        // Available
        $available = \NavisionReservationDone::find_by_sql("SELECT itemno, sum(quantity - done) as available FROM `navision_reservation_done` WHERE `shop_id` = ".$shopid." group by itemno");
        $this->doneAvailable = array();
        echo "<h4>Available items</h4><table>";
        echo "<tr><td>Itemno</td><td>Available</td></tr>";
        foreach($available as $item) {
            echo "<tr><td>".$item->itemno."</td><td>".$item->available."</td></tr>";

            if(!isset($this->doneAvailable[strtolower(trim($item->itemno))])) $this->doneAvailable[strtolower(trim($item->itemno))] = 0;
            $this->doneAvailable[strtolower(trim($item->itemno))] += $item->available;
        }
        echo "</table>";

        // Presents in shop
        $shopPresents = \PresentReservation::find_by_sql("SELECT present_reservation.id as prid, present_reservation.quantity, present_reservation.quantity_done, present_reservation.sync_quantity, present_model.model_id, present_model.model_present_no  FROM `present_reservation`, present_model WHERE present_reservation.`shop_id` = ".$shopid." && present_reservation.model_id = present_model.model_id && present_model.language_id = 1");
        echo "<h4>Shop items</h4><table>";
        echo "<tr><td>Itemno</td><td>Quantity</td><td>Quantity done</td><td>Sync quantity</td><td>Model id</td><td>Model present no</td><td>NAV STATUS</td><td>Available</td></tr>";
        foreach($shopPresents as $item) {

            $update = false;
            $type = "NONE";

            echo "<tr>
                <td>".$item->model_present_no."</td><td>".$item->quantity."</td><td>".$item->quantity_done."</td><td>".$item->sync_quantity."</td><td>".$item->model_id."</td><td>".$item->model_present_no."</td>";

            $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '" . trim($item->model_present_no) . "' && language_id = 1 && deleted is null");
            if (countgf($navisionItem) == 0) {
                echo "<td>MANGLER I NAV</td>";
                $type = "MISSING";
            } else {
                $update = true;
                $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = 1 && parent_item_no = '" . $navisionItem[0]->no . "' && deleted is null");
                if (count($navbomItemList) > 0) {

                    echo "<td>BOM - ".count($navbomItemList)." - ".(count($navbomItemList) == 1 ? "BOM-SINGLE" : "BOM-MULTIPLE")."</td>";
                    $type = (count($navbomItemList) == 1 ? "BOM-SINGLE" : "BOM-MULTIPLE");

                } else {
                    echo "<td>SINGLE</td>";
                    $type = "SINGLE";
                }

            }

            echo "<td>".($this->doneAvailable[$item->model_present_no] ?? "-")."</td>";
            echo "<td>".($update ? "RUN" : "")."</td>";

            echo "<td>";
            if($update) {

                try {
                    if($type == "SINGLE") {
                        $this->updateSingle($item->prid,$navisionItem[0]);
                    } else if($type == "BOM-SINGLE") {
                        $this->updateBomSingle($item->prid,$navisionItem[0],$navbomItemList[0]);
                    } else if($type == "BOM-MULTIPLE") {
                        $this->updateBomMultiple($item->prid,$navisionItem[0],$navbomItemList);
                    }

                } catch (\Exception $e) {
                    echo "Exception: ".$e->getMessage()."";
                    exit();
                }

            }
            echo "</td>";


            echo "</tr>";




        }
        echo "</table>";

        if($supressOutput) ob_end_clean();

        //echo "DO NOT COMMIT BEFORE ERROR IS FIXED!"; exit();



    }

    private function updateSingle($presentReservationID,$navItem) {

        echo "RUN UPDATE ON SINGLE: ".$navItem->no."<br>";

        $presentReservation = \PresentReservation::find($presentReservationID);

        $available = $this->doneAvailable[trim(strtolower($navItem->no))] ?? 0;
        if($available == 0) {
            echo "NO AVAILABLE<br>";
            return;
        } else if($available < 0) {
            throw new \Exception("ERROR: AVAILABLE IS NEGATIVE!");
        }

        echo "Available: ".$available."<br>";
        echo "Reserved: ".($presentReservation->quantity-$presentReservation->quantity_done)."<br>";

        $useQuantity = min($available, $presentReservation->quantity-$presentReservation->quantity_done);
        echo "Use quantity: ".$useQuantity."<br>";
        if($useQuantity == 0) {
            echo "Abort: no use quantity!";
            return;
        }

        // Update balance
        $usedBalance = $this->updateBalance($presentReservation->shop_id, $navItem->no, $useQuantity,$presentReservation->id,$presentReservation->quantity_done);
        if($usedBalance != 0) {
            echo "Used balance: ".$usedBalance."<br>";
            echo "<b>S".$presentReservation->id.": Update reservation done: ".$presentReservation->quantity_done." to ";
            $presentReservation->quantity_done += $usedBalance;
            $presentReservation->sync_note = "Adjust balance from delivery";
            echo $presentReservation->quantity_done."</b><br>";
            $presentReservation->save();

            // Update available
            $this->doneAvailable[trim(strtolower($navItem->no))] -= $usedBalance;

        } else {
            echo "No balance used, do not update reservation!";
        }

    }

    private function updateBomSingle($presentReservationID,$navItem,$bomChild)
    {
        echo "RUN UPDATE ON BOM-SINGLE: ".$navItem->no." -> ".$bomChild->no." x ".$bomChild->quantity_per."<br>";


        $presentReservation = \PresentReservation::find($presentReservationID);

        $availableChilds = $this->doneAvailable[trim(strtolower($bomChild->no))] ?? 0;
        if($availableChilds == 0) {
            echo "NO AVAILABLE<br>";
            return;
        } else if($availableChilds < 0) {
            throw new \Exception("ERROR: AVAILABLE IS NEGATIVE!");
        }
        else if($bomChild->quantity_per <= 0) {
            throw new \Exception("ERROR: Quantity per is zero or negative ".$bomChild->quantity_per."!");
        }


        $available = intval(floor($availableChilds/ $bomChild->quantity_per));

        echo "Available childs: ".$availableChilds."<br>";
        echo "Available boms: ".$available."<br>";
        echo "Reserved: ".($presentReservation->quantity-$presentReservation->quantity_done)."<br>";

        $useQuantity = min($available, $presentReservation->quantity-$presentReservation->quantity_done);
        echo "Use quantity: ".$useQuantity."<br>";
        if($useQuantity == 0) {
            echo "Abort: no use quantity!";
            return;
        }

        $useQuantityChilds = $useQuantity * $bomChild->quantity_per;

        // Update balance
        $usedBalance = $this->updateBalance($presentReservation->shop_id, $bomChild->no, $useQuantityChilds,$presentReservation->id,$presentReservation->quantity_done,$bomChild->quantity_per);
        if($usedBalance != 0) {
            echo "Used balance: ".$usedBalance."<br>";
            echo "<b>BS".$presentReservation->id.": Update reservation done: ".$presentReservation->quantity_done." to ";
            $presentReservation->quantity_done += $usedBalance/$bomChild->quantity_per;
            $presentReservation->sync_note = "Adjust balance from SO";
            echo $presentReservation->quantity_done."</b><br>";
            $presentReservation->save();

            if(in_array($presentReservation->id,$this->updatedReservationList)) {
                echo "PRESENT RESERVATION ".$presentReservation->id." ALREADY UPDATED!";
                exit();
            }
            $this->updatedReservationList[] = $presentReservation->id;

            // Update available
            $this->doneAvailable[trim(strtolower($bomChild->no))] -= $usedBalance;

        } else {
            echo "No balance used, do not update reservation!";
        }


    }

    private function updateBomMultiple($presentReservationID,$navItem,$bomChilds)
    {
        // Start med at udskrive information om, hvad der sker
        echo "RUN UPDATE ON BOM-MULTIPLE: ".$navItem->no." -> ".count($bomChilds)." childs<br>";

        // Find den aktuelle reservation
        $presentReservation = \PresentReservation::find($presentReservationID);

        // Sæt det tilgængelige antal BOMs til det højeste mulige antal for at starte med
        $availableBoms = PHP_INT_MAX;
        $exit = false;

        // Loop gennem hvert BOM-barn
        foreach($bomChilds as $bomChild) {

            $process = true;
            // Find det tilgængelige antal af dette BOM-barn
            $availableChilds = $this->doneAvailable[trim(strtolower($bomChild->no))] ?? 0;

            // Tjek for fejltilstande: ingen tilgængelige, negativt antal tilgængelige, eller nul eller negativ mængde per barn
            if($availableChilds == 0) {
                echo "CHILD: ".$bomChild->no." - NO AVAILABLE<br>";
                $exit = true; $process = false;

            } else if($availableChilds < 0) {
                echo "CHILD: ".$bomChild->no." - AVAILABLE IS NEGATIVE!<br>";
                $exit = true; $process = false;

            } else if($bomChild->quantity_per <= 0) {
                echo "CHILD: ".$bomChild->no." - Quantity per is zero or negative ".$bomChild->quantity_per."!<br>";
                $exit = true; $process = false;

            }

            // Beregn det tilgængelige antal BOMs baseret på dette BOM-barn og opdater det samlede tilgængelige antal, hvis det er lavere
            if($process) {
                $available = intval(floor($availableChilds/ $bomChild->quantity_per));
                $availableBoms = min($availableBoms, $available);
            }

        }

        // Exit on child problems
        if($exit) {
            echo "Problem with child, aborting<br>";
            return;
        }

        // Beregn mængden, der skal bruges, baseret på det samlede tilgængelige antal og det, der er tilbage af reservationen
        $useQuantity = min($availableBoms, $presentReservation->quantity-$presentReservation->quantity_done);
        echo "Use quantity: ".$useQuantity."<br>";
        if($useQuantity == 0) {
            echo "Abort: no use quantity!";
            return;
        }

        // Simmulate how many can be used on each child
        foreach($bomChilds as $bomChild) {

            $useQuantityChilds = $useQuantity * $bomChild->quantity_per;

            // Update balance
            $usedBalance = $this->updateBalanceCheck($presentReservation->shop_id, $bomChild->no, $useQuantityChilds,$presentReservation->id,$presentReservation->quantity_done,$bomChild->quantity_per);
            if($usedBalance < $useQuantityChilds) {
                $prevUseQuantity = $useQuantity;
                $useQuantity = intval(floor($usedBalance/$bomChild->quantity_per));
                echo "Downgrade use quantity from ".$prevUseQuantity." to ".$useQuantity."!<br>";
            }

        }

        if($useQuantity == 0) {
            echo "Abort: no use quantity after preprocessing!";
            return;
        }

        // Process each child
        foreach($bomChilds as $bomChild) {

            $useQuantityChilds = $useQuantity * $bomChild->quantity_per;

            // Update balance
            $usedBalance = $this->updateBalance($presentReservation->shop_id, $bomChild->no, $useQuantityChilds,$presentReservation->id,$presentReservation->quantity_done,$bomChild->quantity_per);

            $this->doneAvailable[trim(strtolower($bomChild->no))] -= $useQuantityChilds;

            if($usedBalance != $useQuantityChilds) {
                throw new \Exception("ERROR: Used balance does not match expected quantity tried $useQuantityChilds but updated ".$usedBalance."!");
            }

        }

        echo "<b>BM".$presentReservation->id.": Update reservation done: ".$presentReservation->quantity_done." to ";
        $presentReservation->quantity_done += $availableBoms;
        $presentReservation->sync_note = "Adjust balance from SO";
        echo $presentReservation->quantity_done."</b><br>";

        if($presentReservation->quantity_done > $presentReservation->quantity) {
            $this->mailLog("RES 22: Quantity done is higher than quantity to revert: ".$presentReservation->id." / ".$presentReservation->quantity." / ".$presentReservation->quantity_done."<br><pre>".print_r($presentReservation,true)."</pre>");
            exit();
        }
        else if($presentReservation->quantity_done < 0) {
            $this->mailLog("RES 22: Quantity done is lower than 0: ".$presentReservation->id." / ".$presentReservation->quantity." / ".$presentReservation->quantity_done."<br><pre>".print_r($presentReservation,true)."</pre>");
            exit();
        }

        $presentReservation->save();

    }


    /**
     * Finds navision_reservation_done where done and quantity is not equal and updates done with the quantity used
     * @param $shopid Shop id
     * @param $itemno Item no to update balance for
     * @param $quantity Quantity of used to update in done
     * @return int Returns the total used
     */
    private function updateBalance($shopid,$itemno,$quantity,$presentReservationID,$reservationQuantity,$bomQuantity = 1) {

        $left = $quantity;
        $usedTotal = 0;
        $resQuantityCounter = $reservationQuantity;

        echo "UPDATE BALANCE: ".$shopid." - ".$itemno." - ".$quantity."<br>";
        $sql = "SELECT * FROM `navision_reservation_done` WHERE `shop_id` = ".$shopid." AND `itemno` LIKE '".trim($itemno)."' && quantity != done ORDER BY `id` ASC;";

        $list = \NavisionReservationDone::find_by_sql($sql);
        if(count($list) > 0) {
            foreach($list as $item) {

                $useNow = min($left, ($item->quantity-$item->done));
                if($useNow != 0) {


                    $updateObj = \NavisionReservationDone::find($item->id);
                    $oldDone = $updateObj->done;
                    echo "<b>DU".$updateObj->id.": from ".$updateObj->done." to ".($updateObj->done + $useNow)."</b><br>";
                    $updateObj->done += $useNow;

                    if($updateObj->done < 0) {
                        $debugString = "Aborting reservation ".$updateObj->id." - done is negative (".$updateObj->done.").<br>In updateBalance with data: ".$shopid." - ".$itemno." - ".$quantity."<br>Present reservation ID: ".$presentReservationID."<br>Reservation quantity: ".$reservationQuantity."<br>BOM quantity: ".$bomQuantity."<br>Left: ".$left."<br>Used total: ".$usedTotal."<br>Res quantity counter: ".$resQuantityCounter."<br>Use now: ".$useNow."<br>Old done: ".$oldDone."<br>Update obj: ".print_r($updateObj,true)."<br>Done available: ".print_r($this->doneAvailable,true)."<br>Updated reservation list: ".print_r($this->updatedReservationList,true)."<br>";
                        $this->mailLog($debugString);
                        echo "ERROR: Done is negative - aborting";
                        exit();
                    }

                    $updateObj->save();

                    $resQuantityCounter += intval(floor($useNow/$bomQuantity));

                    $doneItem = new \NavisionReservationDoneItem();
                    $doneItem->shop_id = $updateObj->shop_id;
                    $doneItem->itemno = $updateObj->itemno;
                    $doneItem->reservation_done_id = $updateObj->id;
                    $doneItem->present_reservation_id = $presentReservationID;
                    $doneItem->quantity = $useNow;
                    $doneItem->oldresdone = $reservationQuantity;
                    $doneItem->newresdone = $resQuantityCounter;
                    $doneItem->olddonebalance = $oldDone;
                    $doneItem->newdonebalance = $updateObj->done;
                    $doneItem->save();

                    $left -= $useNow;
                    $usedTotal += $useNow;

                }

                // All used, return
                if($left == 0) return $usedTotal;


            }
        }

        return $usedTotal;

    }


    /**
     * Finds navision_reservation_done where done and quantity is not equal and updates done with the quantity used
     * @param $shopid Shop id
     * @param $itemno Item no to update balance for
     * @param $quantity Quantity of used to update in done
     * @return int Returns the total used
     */
    private function updateBalanceCheck($shopid,$itemno,$quantity,$presentReservationID,$reservationQuantity,$bomQuantity = 1) {

        $left = $quantity;
        $usedTotal = 0;
        $sql = "SELECT * FROM `navision_reservation_done` WHERE `shop_id` = ".$shopid." AND `itemno` LIKE '".trim($itemno)."' && quantity != done ORDER BY `id` ASC;";
        $list = \NavisionReservationDone::find_by_sql($sql);
        if(count($list) > 0) {
            foreach($list as $item) {
                $useNow = min($left, ($item->quantity-$item->done));
                if($useNow != 0) {
                    $left -= $useNow;
                    $usedTotal += $useNow;
                }
                if($left == 0) return $usedTotal;
            }
        }
        return $usedTotal;
    }


    /**
     * READ SO's
     */

    public function importSO()
    {

        $soNoList = array(

            272 => array('SO444194'), // NO JGK 300
            57 => array('SO444961'), // NO JGK 400
            58 => array('SO448141','SO444198','SO442804'), // NO JGK 600
            59 => array('SO453750','SO448142'), // NO JGK 800

            574 => array('SO454012','SO448143'), // NO Guld 1000
            2550 => array('SO448144','SO444985','SO444983','SO444195','SO443049'), // NO Guld 1200
            4740 => array('SO443053','SO443054') // NO Guld 2000

                /*
            272 => array('SO443736','SO443948'), // NO JGK 300
            57 => array('SO443740'), // NO JGK 400
            58 => array('SO443750','SO443849'), // NO JGK 600
            59 => array('SO443754','SO443856'), // NO JGK 800

            574 => array('SO443758','SO443855'), // NO Guld 1000
            2550 => array('SO443759','SO443854'), // NO Guld 1200
            4740 => array('SO443762') // NO Guld 2000
*/
            /*
            272 => array('SO439618', 'SO439642'),
            57 => array('SO439619', 'SO439691'),
            58 => array('SO439621', 'SO439694'),
            59 => array('SO439622', 'SO439698'),
            574 => array('SO439623', 'SO439700'),
            2550 => array('SO439624', 'SO439701', 'SO440380'),
            4740 => array('SO439625', 'SO439887', 'SO440387')
            */
        );

        foreach ($soNoList as $shopid => $solist) {
            foreach ($solist as $sono) {
                try {

                    $this->processsono($sono, $shopid);
                } catch (\Exception $e) {
                    //echo "<h2>ERROR: ".$e->getMessage()." (".$e->getLine()."@".$e->getFile().")</h2>";
                    echo "<h2>ERROR: " . $e->getMessage() . "</h2>";
                }
            }
            // exit();
        }

        //echo "STOP BEFORE SAVING!";
        \System::connection()->commit();

    }


    public function importCS() {

        if(isset($_GET["deadline"]) && isset($_GET["shopid"])) {

            echo "IMPORT FROM : ".$_GET["deadline"]." - ".$_GET["shopid"]."<br>";
            $this->importCSDeadlineShop($_GET["deadline"],$_GET["shopid"]);
            exit();
        }

        $sql = "SELECT cardshop_settings.concept_code, cardshop_settings.shop_id, expire_date, count(company_order.id) as order_count, sum(quantity) as quantity FROM `company_order`, cardshop_settings where cardshop_settings.language_code != 4 && company_order.shop_id = cardshop_settings.shop_id && order_state = 10 && order_no not in (SELECT sono FROM `navision_reservation_done`) group by shop_id, expire_date order by  `company_order`.`expire_date` DESC";
        echo $sql;
        $orders = \CompanyOrder::find_by_sql($sql);

        echo "<table><tr><td>Shop</td><td>Shop ID</td><td>Expire date</td><td>Ordre</td><td>Kort</td></tr>";
        foreach($orders as $shoporder) {
            echo "<tr><td>".$shoporder->concept_code."</td><td>".$shoporder->shop_id."</td><td>".$shoporder->expire_date->format('Y-m-d')."</td><td>".$shoporder->order_count."</td><td>".$shoporder->quantity."</td><td><a href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/countercs&shopid=".$shoporder->shop_id."&deadline=".$shoporder->expire_date->format('Y-m-d')."'>IMPORT!</a></td></tr>";
        }
        echo "</table>";

    }


    private function importCSDeadlineShop($deadline,$shop)
    {

        $sql = "SELECT * FROM `company_order` where shop_id = ".intval($shop)." && expire_date = '".$deadline."' && order_state = 10 && order_no not in (SELECT sono FROM `navision_reservation_done`)";
        $orders = \CompanyOrder::find_by_sql($sql);

        echo "Found: ".count($orders)." orders<br>";
        echo "<table><tr><td>id</td><td>order_no</td><td>company_name</td><td>shop_name</td><td>quantity</td><td>order_state</td></tr>";
        foreach($orders as $order) {
            echo "<tr style='background: #F0F0F0;'><td>".$order->id."</td><td>".$order->order_no."</td><td>".$order->company_name."</td><td>".$order->shop_name."</td><td>".$order->quantity."</td><td>".$order->order_state."</td></tr>";
            echo "<tr><td colspan='6' style='padding-left: 100px; padding-top: 20px; padding-bottom: 50px;'>";
            $this->importCSCompanyOrder($order);
            echo "</td>";
        }
        echo "</table>";

        \System::connection()->commit();
        exit();
    }

    private function importCSCompanyOrder(\CompanyOrder $order)
    {

        // Find presents selected in order
        $sql = "SELECT present_model.model_present_no, count(o.id) as quantity FROM `order` as o, shop_user, present_model where shop_user.company_order_id = ".$order->id." && shop_user.shutdown = 0 && shop_user.blocked = 0 && o.shopuser_id = shop_user.id && o.present_model_id = present_model.model_id && present_model.language_id = 1 group by present_model.model_present_no;";
        $presentList = \Order::find_by_sql($sql);

        echo $sql;

        echo "<table><tr><td>Model no</td><td>Quantity</td></tr>";
        foreach($presentList as $present) {
            echo "<tr><td>".$present->model_present_no."</td><td>".$present->quantity."</td></tr>";
            $this->importCSCompanyOrderPresent($present->model_present_no, $present->quantity,$order->shop_id,$order->order_no);
        }
        echo "</table>";



    }

    private function importCSCompanyOrderPresent($itemno,$quantity,$shopid,$bsno,) {

        if($itemno == "") throw new \Exception("Itemno is empty!");
        else if($quantity == 0) throw new \Exception("Quantity is zero!");
        $subItemList = array();

        $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = 1 && parent_item_no = '" . $itemno . "' && deleted is null");
        if (count($navbomItemList) > 0) {

            // Process bom childs
            foreach ($navbomItemList as $bomChild) {

                $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '" . $itemno . "' && language_id = 1 && deleted is null");
                if (countgf($navisionItem) == 0) {

                    $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '" . $itemno . "' && language_id = 4 && deleted is null");
                    if (countgf($navisionItem) == 0) {
                        throw new \Exception("BOM child item not found in NAV: " . $itemno);
                    } else {
                        $bomDesc = $navisionItem[0]->description;
                    }
                } else {
                    $bomDesc = $navisionItem[0]->description;
                }

                $subItemList[] = array("no" => $bomChild->no, "quantity" => $quantity * $bomChild->quantity_per, "description" => $bomDesc, "frombom" => 1, "bomitem" => $itemno, "bomquantity" => $quantity);
            }

        } else {

            $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '" . $itemno . "' && language_id = 1 && deleted is null");
            if (countgf($navisionItem) == 0) {

                $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '" . $itemno . "' && language_id = 4 && deleted is null");
                if (countgf($navisionItem) == 0) {
                    throw new \Exception("Item not found in NAV: " . $itemno);
                } else {
                    $description = $navisionItem[0]->description;
                }

            } else {
                $description = $navisionItem[0]->description;
            }

            $subItemList[] = array("no" => $itemno, "quantity" => $quantity, "description" => $description, "frombom" => 0, "bomitem" => "", "bomquantity" => 1);
        }

        foreach ($subItemList as $item) {
            echo "<tr>
                <td>" . $item["no"] . "</td>
                <td>" . $item["description"] . "</td>
                <td>" . $item["quantity"] . "</td>
                <td>" . $item["frombom"] . "</td>
                <td>" . $item["bomitem"] . "</td>
                <td>" . $item["bomquantity"] . "</td>
                </tr>";

            $doneObj = new \NavisionReservationDone();
            $doneObj->shop_id = $shopid;
            $doneObj->itemno = $item["no"];
            $doneObj->sono = $bsno;
            $doneObj->quantity = $item["quantity"];
            $doneObj->isbom = $item["frombom"];
            $doneObj->bomno = $item["bomitem"];
            $doneObj->bomquantity = $item["bomquantity"];
            $doneObj->save();

        }

    }


    private function processsono($sono, $shopid)
    {

        // Find shop
        $shop = \Shop::find($shopid);
        echo "<h2>Shop: " . $shopid . ": " . $shop->name . "</h2>";


        $doneExisting = \NavisionReservationDone::find_by_sql("SELECT * FROM navision_reservation_done WHERE shop_id = ".intval($shopid)." && sono = '".$sono."'");
        if(count($doneExisting) > 0)
        {
            throw new \Exception("Allerede behandlet");
        }

        // Load salesorder
        $client = new SalesHeaderWS();
        $order = $client->getHeader("ORDER", $sono);

        // Check salesorder
        if ($order == null) {

            // No order found, look for invoice
            $invoicClient = new SalesShipmentHeadersWS();
            $invoiceList = $invoicClient->getByOrderNo($sono);
            $invoice = $invoiceList[0] ?? null;

            // No invoice found, throw exception
            if ($invoice == null) {
                throw new \Exception("No salesorder or invoice found on " . $sono);
            } // Invoice found, process lines
            else {

                echo "Found INVOICE: for order " . $sono . " on invoice " . $invoice->getNo() . " to " . $invoice->getBilltoCustomerNo() . ": " . $invoice->getBilltoName() . "<br>Levering: " . $invoice->getShiptoName() . "<br>";
                echo "<br>Extracting lines for invoice " . $invoice->returnKeyString("No") . "<br>";

                $client = new SalesShipmentLinesWS();
                $lines = $client->getLines($invoice->getNo());
                if ($lines == null || count($lines) == 0) {
                    echo "No lines found<br>";
                    return;
                }

            }


        } // Order found, process lines
        else {

            echo "Found SO: " . $order->getNo() . " to " . $order->getBilltoCustomerNo() . ": " . $order->getBilltoName() . "<br>Levering: " . $order->getShiptoName() . "<br>";
            echo "<br>Extracting lines for " . $order->returnKeyString("No") . "<br>";

            $client = new SalesLineWS();
            $lines = $client->getLines($sono);
            if ($lines == null || count($lines) == 0) {
                echo "No lines found<br>";
                return;
            }

        }


        echo "<table><tr><td>varenr</td><td>beskrivelse</td><td>antal</td></tr>";


        $itemlines = array();
        foreach ($lines as $line) {

            $itemno = trim($line->getNo());
            $quantity = $line->getQuantity();
            $description = $line->getDescription();

            if (isset($itemlines[$itemno])) {
                $itemlines[$itemno]["quantity"] += $quantity;
            } else {
                $itemlines[$itemno] = array("itemno" => $itemno, "quantity" => $quantity, "description" => $description);
            }

        }

        foreach ($lines as $line) {

            $itemno = trim($line->getNo());
            $quantity = $line->getQuantity();
            $description = $line->getDescription();

            if ($itemno != "" && $quantity != 0) {

                $subItemList = array();

                $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = 1 && parent_item_no = '" . $itemno . "' && deleted is null");
                if (count($navbomItemList) > 0) {

                    // Process bom childs
                    foreach ($navbomItemList as $bomChild) {

                        $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '" . $itemno . "' && language_id = 1 && deleted is null");
                        if (countgf($navisionItem) == 0) {
                            $bomDesc = "MANGLER I NAV!";
                        } else {
                            $bomDesc = $navisionItem[0]->description;
                        }

                        $subItemList[] = array("no" => $bomChild->no, "quantity" => $quantity * $bomChild->quantity_per, "description" => $bomDesc, "frombom" => 1, "bomitem" => $itemno, "bomquantity" => $quantity);
                    }

                } else {
                    $subItemList[] = array("no" => $itemno, "quantity" => $quantity, "description" => $description, "frombom" => 0, "bomitem" => "", "bomquantity" => 1);
                }

                foreach ($subItemList as $item) {
                    echo "<tr>
                    <td>" . $item["no"] . "</td>
                    <td>" . $item["description"] . "</td>
                    <td>" . $item["quantity"] . "</td>
                    <td>" . $item["frombom"] . "</td>
                    <td>" . $item["bomitem"] . "</td>
                    <td>" . $item["bomquantity"] . "</td>
                    </tr>";

                    $doneObj = new \NavisionReservationDone();
                    $doneObj->shop_id = $shopid;
                    $doneObj->itemno = $item["no"];
                    $doneObj->sono = $sono;
                    $doneObj->quantity = $item["quantity"];
                    $doneObj->isbom = $item["frombom"];
                    $doneObj->bomno = $item["bomitem"];
                    $doneObj->bomquantity = $item["bomquantity"];
                    $doneObj->save();

                }


            }


        }

        echo "</table><br>";


    }


    public function runPrivateDeliveryJob()
    {

        \GFCommon\DB\CronLog::startCronJob("NavReservationCounter");

        $supressOutput = isset($_GET["supress"]) && $_GET["supress"] == 1;

        if(!$supressOutput) echo "Running private delivery release job<br>";
        $sql = "SELECT cardshop_settings.concept_code, cardshop_settings.shop_id, count(shipment.id) as shipmentcount, count(DISTINCT shipment.itemno) as itemcount FROM `shipment`, company_order, cardshop_settings where cardshop_settings.shop_id = company_order.shop_id && shipment.companyorder_id = company_order.id && (shipment_type = 'privatedelivery' || shipment_type = 'directdelivery') && reservation_released IS NULL && shipment_state in (2,5,6) group by company_order.shop_id";
        $shipmentList = \Shipment::find_by_sql($sql);
        foreach ($shipmentList as $shipment) {

            if(!$supressOutput) echo "<h2>Update private deliveries on <br>Update ".$shipment->concept_code.": ".$shipment->shipmentcount." shipments, ".$shipment->itemcount." items</h2><br>";
            $this->importPrivateDeliveryShop($shipment->shop_id);

            if(!$supressOutput) echo "<b>Update reservations</b><br>";
            $this->runDoneShop($shipment->shop_id);

        }

        echo "<br>Done..";
        \System::connection()->commit();

        \GFCommon\DB\CronLog::endCronJob(1,"OK");

    }

    public function counterpd()
    {

        if(isset($_GET["shopid"])) {
            echo "IMPORT FROM : ".$_GET["shopid"]."<br>";
            $this->importPrivateDeliveryShop($_GET["shopid"]);
            \System::connection()->commit();
            exit();
        }

        //$sql = "SELECT cardshop_settings.concept_code, shipment.itemno, count(shipment.id) FROM `shipment`, company_order, cardshop_settings where cardshop_settings.shop_id = company_order.shop_id && shipment.companyorder_id = company_order.id && (shipment_type = 'privatedelivery' || shipment_type = 'directdelivery') && reservation_released IS NULL && shipment_state in (2,5,6) group by company_order.shop_id, shipment.itemno;";
        $sql = "SELECT cardshop_settings.concept_code, cardshop_settings.shop_id, count(shipment.id) as shipmentcount, count(DISTINCT shipment.itemno) as itemcount FROM `shipment`, company_order, cardshop_settings where cardshop_settings.shop_id = company_order.shop_id && shipment.companyorder_id = company_order.id && (shipment_type = 'privatedelivery' || shipment_type = 'directdelivery') && reservation_released IS NULL && shipment_state in (2,5,6) group by company_order.shop_id";

        echo "<table><tr><td>Shop</td><td>Shipments</td><td>ItemNos</td><td>&nbsp;</td></tr>";

        $shipmentList = \Shipment::find_by_sql($sql);
        foreach ($shipmentList as $shipment) {
            echo "<tr>
                <td>".$shipment->concept_code."</td>
                <td>".$shipment->shipmentcount."</td>
                <td>".$shipment->itemcount."</td>
                <td><a href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/counterpd&shopid=".$shipment->shop_id."'>Release reservation</a></td>
            </tr>";
        }

        echo "</table>";



    }

    private function importPrivateDeliveryShop($shopid) {

        $supressOutput = isset($_GET["supress"]) && $_GET["supress"] == 1;
        if($supressOutput) ob_start();

        $sql = "SELECT shipment.* FROM `shipment`, company_order where company_order.shop_id = ".intvalgf($shopid)." && shipment.companyorder_id = company_order.id && (shipment_type = 'privatedelivery' || shipment_type = 'directdelivery') && reservation_released IS NULL && shipment_state in (2,5,6)";
        //$sql = "SELECT shipment.* FROM `shipment`, company_order where company_order.shop_id = ".intvalgf($shopid)." &&  shipment.companyorder_id = company_order.id && (shipment_type = 'privatedelivery' || shipment_type = 'directdelivery')  && shipment_state in (2,5,6)";

        $shipmentList = \Shipment::find_by_sql($sql);
        $itemSumMap = array();

        $processedShipments = array();

        // Make sum of items
        foreach($shipmentList as $shipment) {

            if(in_array($shipment->id, $processedShipments)) {
                echo "Shipment ".$shipment->id." already processed<br>";
                exit();
            }

            $processedShipments[] = $shipment->id;

            // Add sum map
            if(!isset($itemSumMap[$shipment->itemno])) {
                $itemSumMap[$shipment->itemno] = 0;
            }
            $itemSumMap[$shipment->itemno] ++;

            // Set reservation released
            $updateShip = \Shipment::find($shipment->id);
            $updateShip->reservation_released = date('d-m-Y H:i:s');
            $updateShip->save();

        }


        //echo "<pre>".print_r($itemSumMap,true)."</pre>";


        echo "<table><tr><td>Model no</td><td>Quantity</td></tr>";
        echo "<tr>
                <td>ITEMNO</td>
                <td>DESCRIPTION</td>
                <td>QUANTITY</td>
                <td>FROMBOM</td>
                <td>BOMITEM</td>
                <td>BOMQUANTITY</td>
                </tr>";

        foreach($itemSumMap as $itemNo => $quantity) {
            try {
                $this->importPrivateDeliveryPresent($itemNo,$quantity,$shopid,"PD".date('d-m-Y'));
            }
            catch (\Exception $e) {
                echo "<h2>ERROR: ".$e->getMessage()."</h2>";
                exit();
            }
        }

        echo "</table>";


        if($supressOutput) ob_end_clean();


    }

    private function importPrivateDeliveryPresent($itemno,$quantity,$shopid,$bsno) {

        if($itemno == "") throw new \Exception("Itemno is empty!");
        else if($quantity == 0) throw new \Exception("Quantity is zero!");
        $subItemList = array();

        $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = 1 && parent_item_no = '" . $itemno . "' && deleted is null");
        if (count($navbomItemList) > 0) {

            // Process bom childs
            foreach ($navbomItemList as $bomChild) {

                $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '" . $itemno . "' && language_id = 1 && deleted is null");
                if (countgf($navisionItem) == 0) {

                    throw new \Exception("BOM child item not found in NAV: " . $itemno);
                } else {
                    $bomDesc = $navisionItem[0]->description;
                }

                $subItemList[] = array("no" => $bomChild->no, "quantity" => $quantity * $bomChild->quantity_per, "description" => $bomDesc, "frombom" => 1, "bomitem" => $itemno, "bomquantity" => $quantity);
            }

        } else {

            $navisionItem = \NavisionItem::find_by_sql("select * from navision_item where no LIKE '" . $itemno . "' && language_id = 1 && deleted is null");
            if (countgf($navisionItem) == 0) {
                if($itemno == "x") return;
                throw new \Exception("Item not found in NAV: " . $itemno);
            } else {
                $description = $navisionItem[0]->description;
            }

            $subItemList[] = array("no" => $itemno, "quantity" => $quantity, "description" => $description, "frombom" => 0, "bomitem" => "", "bomquantity" => 1);
        }

        foreach ($subItemList as $item) {
            echo "<tr>
                <td>" . $item["no"] . "</td>
                <td>" . $item["description"] . "</td>
                <td>" . $item["quantity"] . "</td>
                <td>" . $item["frombom"] . "</td>
                <td>" . $item["bomitem"] . "</td>
                <td>" . $item["bomquantity"] . "</td>
                </tr>";

            $doneObj = new \NavisionReservationDone();
            $doneObj->shop_id = $shopid;
            $doneObj->itemno = $item["no"];
            $doneObj->sono = $bsno;
            $doneObj->quantity = $item["quantity"];
            $doneObj->isbom = $item["frombom"];
            $doneObj->bomno = $item["bomitem"];
            $doneObj->bomquantity = $item["bomquantity"];
            $doneObj->save();

        }

    }



    /**
     * REVERT COUNTERS
     *
     * New versino, using navision_reservation_log, cant easily be reused
     */

    public function revertCounters()
    {

        $navisionReservationLogRevertList = \NavisionReservationLog::find_by_sql("SELECT * FROM `navision_reservation_log` WHERE shop_id in (select shop_id from cardshop_settings) && ((created >= '2023-12-05 05:00:00' && created < '2023-12-05 06:30:00') OR (created >= '2023-12-06 06:30:00' && created < '2023-12-06 07:00:00'))");

        $sumList = array();


        // Create shop - itemno sumlist
        foreach($navisionReservationLogRevertList as $navRevLog)
        {

            $shop = $navRevLog->shop_id;
            $itemno = $navRevLog->itemno;
            $delta = $navRevLog->delta;

            // Create sumlist of [shop][itemno] + $delta
            if(!isset($sumList[$shop])) {
                $sumList[$shop] = array();
            }
            if(!isset($sumList[$shop][$itemno])) {
                $sumList[$shop][$itemno] = 0;
            }
            $sumList[$shop][$itemno] += -1*$delta;


        }


        // Handle sum
        foreach ($sumList as $shopid => $itemList)
        {
            $shop = \Shop::find($shopid);
            echo "<h2>Shop: ".$shopid.": ".$shop->name."</h2>";
            foreach($itemList as $itemno => $delta)
            {
                $this->handleReverse($shopid,$itemno,$delta);
            }
        }


        exit();

    }

    private function handleReverse($shopid,$itemno,$sum)
    {

        echo "<h3>Item ".$itemno.": ".$sum."</h3>";

        echo "<b>DUMP NAV REV DONE:</b><br>";
        $navRevDoneList = \NavisionReservationDone::find_by_sql("SELECT * FROM `navision_reservation_done` WHERE shop_id = ".$shopid." && itemno = '".$itemno."'");
        foreach($navRevDoneList as $i => $doneItem) {
            echo "<b>".$i. " of ".count($navRevDoneList)."</b><br>";
            echo "SONO: ".$doneItem->sono."<br>";
            echo "QUANTITY: ".$doneItem->quantity."<br>";
            echo "DONE: ".$doneItem->done."<br>";
            echo "REVERT: ".$doneItem->revert."<br>";
        }

        echo "<br>";

    }


    /**
     * REVERT COUNTERS
     *
     * New versino, using navision_reservation_log, cant easily be reused
     */

    public function revertCountersV2()
    {

        $navisionReservationLogRevertList = \NavisionReservationLog::find_by_sql("SELECT * FROM `navision_reservation_log` WHERE shop_id in (select shop_id from cardshop_settings) && ((created >= '2023-12-05 05:00:00' && created < '2023-12-05 06:30:00') OR (created >= '2023-12-06 06:30:00' && created < '2023-12-06 07:00:00'))");

        foreach($navisionReservationLogRevertList as $navRevLog)
        {
            $this->handleRevertNavRevLog($navRevLog);
        }


        echo "WELCOME BAKC!";
        exit();

    }

    private function handleRevertNavRevLogV2(\NavisionReservationLog $navRevLog)
    {

        echo "<br>Handle ".$navRevLog->id.": shop ".$navRevLog->shop_id." / item ".$navRevLog->itemno." / ".$navRevLog->delta." / ".$navRevLog->notes."<br>";

        // Find presen reservation ids
        $prids = explode(",",$navRevLog->present_reservations_ids);

        if(count($prids) == 0) {
            echo "<h1>NO PRESENT RESERVATIONS FOUND</h1>";
            exit();
        }

        echo "Found ".count($prids).": ".implode(", ",$prids)."<br>";

        // Find unique ids in list
        $prids = array_unique($prids);
        if(count($prids) > 1) {
            echo "<h2>Found ".count($prids)." present reservations</h2>";
            //echo "<pre>".print_r($prids,true)."</pre>";
            //exit();
        }

        $type = "UNKNOWN";
        if(count($prids) == 1) {
            $type = "SINGLE";
        }


        $totalQuantityDoneOnReservations = 0;

       foreach($prids as $prid)
       {

           $presentReservation = \PresentReservation::find($prid);
            $totalQuantityDoneOnReservations += $presentReservation->quantity_done;

            $prdata = array(
                "shop_id" => $presentReservation->shop_id,
                "present_id" => $presentReservation->present_id,
                "quantity_done" => $presentReservation->quantity_done,
                "sync_quantity" => $presentReservation->sync_quantity
            );


            echo "<pre>".print_r($prdata,true)."</pre>";

        }

       if($navRevLog->delta == -1*$totalQuantityDoneOnReservations) {
           $type = "MULTI-ZERO-ALL";
       }

       echo "TYPE: ".$type."<br>";

       if($type == "UNKNOWN") {

           echo $navRevLog->delta." / ".$totalQuantityDoneOnReservations;

           $jumpOver = array();

           //if(!in_array($navRevLog->id, array(65876,65877)))
           exit();
       }

    }


    /**
     * REVERT COUNTERS
     *
     * WAS NOT DOINT AS EXPECTED!!!!!!
     */

    public function revertCountersV1()
    {

        // Find reservation done to revert
        $reservationDoneRevertList = \NavisionReservationDone::find_by_sql("SELECT * FROM navision_reservation_done where revert = 1 && revert_date IS NULL");

        foreach($reservationDoneRevertList as $reservationDoneDB) {

            $reservationDone = \NavisionReservationDone::find($reservationDoneDB->id);
            if($reservationDone->id > 0 && $reservationDone->id == $reservationDoneDB->id && $reservationDone->revert = 1 && $reservationDone->revert_date == null) {
                $this->revertReservationDone($reservationDone);
            } else {
                echo "ERROR ON ".$reservationDone->id.": do not run";
                exit();
            }

        }


        echo "WELCOME BAKC!";
        exit();

    }

    private function revertReservationDone(\NavisionReservationDone $reservationDone)
    {

        echo "<br>REVERT: ".$reservationDone->id.": shop ".$reservationDone->shop_id." / sono ".$reservationDone->sono." / itemno ".$reservationDone->itemno." / quantity ".$reservationDone->quantity."<br>";

        $doneItemList = \NavisionReservationDoneItem::find_by_sql("select * from navision_reservation_done_item where reservation_done_id = ".$reservationDone->id." order by id asc");
        echo "- Found ".count($doneItemList)." done items from counter<br>";

        foreach($doneItemList as $doneItem)
        {

            $quantity = $doneItem->newresdone- $doneItem->oldresdone;
            echo " -- ".$doneItem->id." updated reservation ".$doneItem->present_reservation_id." from ".$doneItem->oldresdone." to ".$doneItem->newresdone."  = (".($quantity*-1).")<br>";

            // Find present reservation
            $presentReservation = \PresentReservation::find($doneItem->present_reservation_id);

            // Create new NavisionReservationDoneItem
            $revertDoneItem = new \NavisionReservationDoneItem();
            $revertDoneItem->shop_id = $doneItem->shop_id;
            $revertDoneItem->itemno = $doneItem->itemno;
            $revertDoneItem->reservation_done_id = $doneItem->reservation_done_id;
            $revertDoneItem->present_reservation_id = $doneItem->present_reservation_id;
            $revertDoneItem->quantity = -1*$quantity;
            $revertDoneItem->oldresdone = $presentReservation->quantity_done;
            $revertDoneItem->newresdone = $presentReservation->quantity_done-$quantity;
            $revertDoneItem->olddonebalance = 0;
            $revertDoneItem->newdonebalance = 0;
            $revertDoneItem->is_revert = 1;
            $revertDoneItem->save();

            // Update present reservation


            $presentReservation->quantity_done -= $doneItem->quantity;
            $presentReservation->sync_note = "Revert from reservation error";

            if($presentReservation->quantity_done > $presentReservation->quantity) {
                $this->mailLog("RES 33: Quantity done is higher than quantity to revert: ".$presentReservation->id." / ".$presentReservation->quantity." / ".$presentReservation->quantity_done."<br><pre>".print_r($revertDoneItem,true)."</pre>");
                exit();
            }
            else if($presentReservation->quantity_done < 0) {
                $this->mailLog("RES 33: Quantity done is lower than 0: ".$presentReservation->id." / ".$presentReservation->quantity." / ".$presentReservation->quantity_done."<br><pre>".print_r($revertDoneItem,true)."</pre>");
                exit();
            }

            $presentReservation->save();



        }

        // Update reservation done
        $reservationDone->revert_date = date('Y-m-d H:i:s');
        $reservationDone->save();

    }

    protected function mailLog($message) {
        $modtager = 'sc@interactive.dk';
        mailgf($modtager, "Reservation run error", $message."\r\n<br>\r\n<br>\r\n<br>\r\n<br><pre></pre>");
    }


}




















