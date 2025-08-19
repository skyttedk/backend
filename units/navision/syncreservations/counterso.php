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


    /**
     * Behandler reservationer for en specifik butik.
     *
     * - Kontrollerer om `shopid` er sat og større end 0.
     * - Finder og sletter reservationer, der starter med "BS" for den angivne `shopid`.
     * - Henter varenumre fra `PresentReservation` for den angivne `shopid`.
     * - Tjekker om varenummeret eller bomnummeret i `NavisionReservationDone` matcher de fundne varenumre.
     * - Opdaterer `bomno` i `NavisionReservationDone` hvis nødvendigt.
     * - Beregner summen af `done` for reservationer, der ikke starter med "BS".
     * - Opdaterer `quantity_done` i `PresentReservation` baseret på de beregnede `done` værdier.
     * - Viser en liste over butikker, hvis `shopid` ikke er sat.
     *
     * @return void
     */
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




    /**
     * Viser en liste over varer med ikke-udlignede reservationer.
     *
     * - Udfører en SQL-forespørgsel for at finde forskelle mellem `quantity` og `done` i `navision_reservation_done`.
     * - Grupperer resultaterne efter butik og varenummer.
     * - Viser resultaterne i en HTML-tabel.
     * - Håndterer eventuelle undtagelser ved at vise en fejlmeddelelse.
     *
     * @return void
     */
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

    /**
     * Viser en liste over varer med ikke-udlignede reservationer.
     *
     * - Udfører en SQL-forespørgsel for at finde forskelle mellem `quantity` og `done` i `navision_reservation_done`.
     * - Grupperer resultaterne efter butik og varenummer.
     * - Viser resultaterne i en HTML-tabel.
     * - Håndterer eventuelle undtagelser ved at vise en fejlmeddelelse.
     *
     * @return void
     */
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


    /**
     * Viser butikker med ikke-udlignede reservationer og muliggør manuel kørsel af udligningsprocessen.
     *
     * - Udfører en SQL-forespørgsel for at finde butikker med forskelle mellem `quantity` og `done`.
     * - Præsenterer resultaterne i en HTML-tabel med links til at køre en udligningsfunktion for hver butik.
     * - Hvis `shopid` er angivet i `$_GET`, kaldes `runDoneShop` for den specifikke butik, og ændringerne gemmes.
     *
     * @return void
     */
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

        if($presentReservation->quantity_done > $presentReservation->quantity && $presentReservation->quantity_done > 0) {
            $presentReservation->quantity = $presentReservation->quantity_done;
            //$this->mailLog("RES 22: Quantity done is higher than quantity to revert: ".$presentReservation->id." / ".$presentReservation->quantity." / ".$presentReservation->quantity_done."<br><pre>".print_r($presentReservation,true)."</pre>");
            //exit();
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










    /************************** COUNTER SO BATCH *******************************/

    private $batchItemSOList = array();

    public function importSOBatch() {

        $this->runBatchFromJSON();
        return;

        // Define shops to look at
        $shopList = [574,2550,4740,272,57,58,59];

        // Define so no to look at
        $soliststr = "SO563888,SO563878,SO563670,SO562699,SO562716,SO563750,SO560803,SO560854,SO563657,SO563668,SO563773,SO563774,SO563849,SO563910,SO563736,SO563775,SO563887,SO563897,SO563863";
        $solist = explode(",",$soliststr);

        $dryRun = true;

        $shopPresentList = array();
        $itemsfromBom = array();

        // Process all presents and presentmodels in each shop
        $presentList = \PresentReservation::find_by_sql("SELECT shop_id, model_present_no, quantity-quantity_done as reserved FROM `present_reservation`, present_model where present_model.language_id = 1 and quantity > 0 and skip_navision = 0 and present_reservation.model_id = present_model.model_id and shop_id in (574,2550,4740,272,57,58,59) order by present_reservation.shop_id asc;");
        foreach($presentList as $present) {

            $itemno = trim(strtolower($present->model_present_no));
            $shopid = $present->shop_id;

            if(!isset($shopPresentList[$itemno])) {
                $shopPresentList[$itemno] = array();
            }

            if(!isset($shopPresentList[$itemno][$shopid])) {
                $shopPresentList[$itemno][$shopid] = 0;
            }

            $shopPresentList[$itemno][$shopid] += $present->reserved;

        }

        $bomData = array();

        // For each item no check for boms
        foreach($shopPresentList as $itemno => $shops) {

            $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = 4 && parent_item_no LIKE '" . $itemno . "' && deleted is null");
            if (count($navbomItemList) > 0) {

                // Process bom childs
                foreach ($navbomItemList as $bomChild) {

                    $shopData = array();
                    foreach($shops as $shopid => $quantity) {
                        $shopData[$shopid] = $quantity*$bomChild->quantity_per;
                        $bomData[$shopid][$bomChild->no] = array("parent" => $itemno, "quantityper" => $bomChild->quantity_per);
                    }
                    $itemsfromBom[] = trim(strtolower($bomChild->no));
                    $shopPresentList[trim(strtolower($bomChild->no))] = $shopData;



                }

            } else {
                $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = 1 && parent_item_no LIKE '" . $itemno . "' && deleted is null");
                if (count($navbomItemList) > 0) {

                    // Process bom childs
                    foreach ($navbomItemList as $bomChild) {

                        $shopData = array();
                        foreach($shops as $shopid => $quantity) {
                            $shopData[$shopid] = $quantity*$bomChild->quantity_per;
                        }
                        $itemsfromBom[] = trim(strtolower($bomChild->no));
                        $shopPresentList[trim(strtolower($bomChild->no))] = $shopData;

                    }

                }
            }

        }


        // Output large textarea with json of shopPresentList
        echo "<textarea style='width: 100%; height: 500px;'>";
        echo json_encode($shopPresentList);
        echo "</textarea>";

        // Output large textarea with json of bomdata
        echo "<textarea style='width: 100%; height: 500px;'>";
        echo json_encode($bomData);
        echo "</textarea>";




        // Print
        echo "RUNNING COUNTER SO BATCH<br>";
        echo "Shops [".count($shopList)."]: ".implode(", ",$shopList)."<br>";
        foreach($shopList as $shopid) {
            $shop = \Shop::find($shopid);
            echo " - ".$shop->name."<br>";
        }
        echo "SO [".count($solist)."]: ".implode(", ",$solist)."<br>";
        echo "DRY RUN: ".($dryRun ? "YES" : "NO")."<br>";

        echo "<p>READING SO NUMBERS</p>";

        foreach($solist as $sono) {

            try {
                $this->processBatchSONO($sono);
            }
            catch(\Exception $e) {
                echo "<h2>ERROR: ".$e->getMessage()."</h2>";
                exit();
            }

        }

        // Output large textarea with json of $this->batchItemSOList
        echo "<textarea style='width: 100%; height: 500px;'>";
        echo json_encode($this->batchItemSOList);
        echo "</textarea>";


    }

    private function processBatchSONO($sono) {



        echo "Processing so no: ".$sono."<br>";

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

            echo "<pre>".print_r($order,true)."</pre>";

            echo "<br>Extracting lines for " . $order->returnKeyString("No") . "<br>";

            $client = new SalesLineWS();
            $lines = $client->getLines($sono);
            if ($lines == null || count($lines) == 0) {
                echo "No lines found<br>";
                return;
            }

        }

        echo " - Processing ".count($lines)." lines<br><br>";
        foreach ($lines as $line) {

            $itemno = trim($line->getNo());
            $quantity = $line->getQuantity();
            $description = $line->getDescription();

            if (isset($itemlines[$itemno])) {
                $this->batchItemSOList[$itemno]["quantity"] += $quantity;
            } else {
                $this->batchItemSOList[$itemno] = array("itemno" => $itemno, "quantity" => $quantity, "description" => $description);
            }

        }

    }




    /**
     * RUN FROM JSON
     */

    private function runBatchFromJSON() {

        echo "NO NO RUN NEW SO NUMBERS FIRST!";
        return;

        $batchno = "NOBATCH0401";

        $presentDataString = '{"16060":{"57":70},"14754":{"59":60},"14755":{"59":60},"230177":{"57":60},"210146":{"57":0},"9009":{"57":80},"203320":{"2550":14},"240151":{"57":140},"230306":{"57":100},"240319":{"57":20},"240152":{"57":60},"ka-011":{"574":0},"39362":{"57":7},"690147":{"2550":22},"cc006637-001":{"59":300},"sam5352":{"57":110},"26325":{"574":65},"1017349":{"4740":2},"30-hssckf2":{"57":90},"220146":{"57":138,"58":94},"220147":{"57":101,"58":49},"240108":{"57":106,"59":60},"n24j113397":{"59":80},"n24j113400":{"57":63},"n24j113398":{"59":80},"n24j113401":{"57":54},"n24j2047801":{"57":200},"nosam24j2044901":{"57":150},"n24j61500":{"57":100},"18362":{"57":70},"1017365":{"4740":21},"10020705":{"574":40},"11075":{"57":7,"58":27},"sam5351":{"57":175},"1027459":{"57":45},"693801":{"59":3},"240139":{"57":0},"240179":{"57":175},"240181":{"57":140},"240164":{"2550":9,"4740":4},"240170":{"2550":0},"240320":{"57":13},"240330":{"57":20},"240337":{"57":15},"18294":{"57":84},"230201":{"57":90},"230167":{"57":90},"203324":{"2550":14},"230150":{"57":45},"200107":{"59":10},"4343502":{"59":25},"230198":{"57":0},"230199":{"57":0},"30-lg0013":{"57":20},"cc007055-001":{"57":85},"cc007054-001":{"57":75},"20670":{"57":45},"sam3805":{"57":40},"1017406":{"57":0},"4413399":{"57":44},"btb-10090":{"2550":49},"gf0001":{"57":0},"n24j20444hb2":{"57":300},"n24j20444mat02":{"57":300},"n24j20444sjok02":{"57":300},"n24j20138-1":{"58":3},"n24jgf2411":{"57":300},"n24jgf2426":{"57":300,"272":300},"n24jgf2427":{"57":300,"58":300},"1017380":{"57":18},"230176":{"57":129},"nosam24jnolosweeds-3":{"57":200},"240106":{"57":175},"sam5626":{"57":5},"ffc424h":{"57":25},"ffc424n":{"57":20},"ffc427n":{"57":25},"sam5406":{"57":20},"sam5407":{"57":20},"sam5408":{"57":20},"sam5409":{"57":20},"sam5410":{"57":20},"10001":{"4740":15},"10002":{"4740":15},"10003":{"4740":15},"240121":{"57":124},"n23j116131":{"57":203},"n23j116132":{"57":333},"n23j116129":{"57":162},"n23j116130":{"57":162},"n23j115002":{"57":20},"bs210601":{"57":20},"n210115":{"57":30},"5924cow-bla":{"4740":25},"5924cow-tan":{"4740":25},"n-core8016":{"57":70},"n-220148":{"57":93},"240124-1":{"57":14,"58":13},"n222017":{"2550":2},"n222018":{"2550":2},"n-39278":{"57":50},"240321":{"58":25},"sam5378":{"58":26},"14580":{"58":60},"9160":{"58":95},"9123":{"58":0},"10020708":{"2550":20},"27823":{"58":0},"26324":{"574":65},"240306":{"2550":46},"240338":{"58":15},"32319":{"58":40},"n24j96040":{"58":200},"200134":{"58":0},"240153":{"4740":0},"220145":{"58":310},"240133":{"58":220},"240142":{"58":0},"sam4057":{"58":55},"240174":{"58":225},"30-lg0119oak":{"58":40},"sam5357":{"58":110},"kfte89":{"58":59},"240210":{"58":60},"240196":{"58":85},"230147":{"58":0},"230129":{"58":70},"999310014":{"58":0},"999310052":{"58":0},"246625":{"58":70},"10811-mb":{"58":132},"sam5393":{"58":150},"1050":{"58":35},"21107":{"574":9},"25608":{"4740":5},"sam5350":{"58":100},"1016894":{"58":15},"1056621":{"58":25},"240141":{"58":0},"240144":{"58":105},"240175":{"58":120},"240166":{"2550":9,"4740":4},"240344":{"58":30},"210110":{"58":45},"220152":{"58":140},"sam2031":{"58":25},"7261":{"58":45},"203328":{"2550":14},"220121":{"58":55},"230152":{"58":66},"220136":{"58":85},"220163":{"58":75},"230159":{"58":40},"4343500":{"58":50},"230190":{"58":65},"sam5659":{"58":2},"210169":{"58":20},"220174":{"58":20},"689497":{"58":35},"637137":{"58":25},"lr-147":{"58":0},"220119":{"58":130},"30-lgwineset":{"58":35},"cc006294-001":{"58":55},"cc006284-001":{"2550":61},"sam3804":{"58":30},"1061058":{"574":26},"210153":{"58":125},"220154":{"58":35},"kfew09":{"58":0},"gf0002":{"58":0},"240348":{"58":24},"dt0109-qz":{"4740":25},"94887404":{"58":25},"29329":{"58":20},"miecopak1":{"58":25},"240163":{"58":0},"30735":{"574":0},"n24j2047802":{"58":200},"n24j87207":{"58":200},"n24j20444hb3":{"58":300},"n24j20444mat03":{"58":300},"n24j20444sjok03":{"58":300},"n23j40131":{"58":30},"94887406":{"58":30},"230165":{"58":0,"574":1},"n24j45414":{"58":200},"nosam24jsweeds-2":{"58":200},"nosam24jmatc-nolo-1":{"58":3},"240207":{"58":0},"n24jgf2406":{"58":200},"n24jgf2412":{"58":200},"34693":{"2550":46},"34694":{"58":15},"34695":{"58":19},"10020":{"4740":15},"10021":{"4740":15},"10022":{"4740":15},"200000206":{"58":35},"200000207":{"58":35},"c12":{"58":15},"c13":{"58":15},"240203":{"58":121},"240201":{"58":75},"240202":{"58":75},"ndbtgr":{"58":15},"ndtobr-b":{"58":15},"ndyegr-b":{"58":15},"fab326a":{"58":15},"fab326y":{"58":34},"nosam24j2048001":{"58":200,"59":200},"nosam23j1009":{"58":140},"nosam23j1010":{"58":200},"n23p117451":{"58":15},"n222002":{"58":28},"599000":{"58":2},"n222003":{"58":50},"n23j116105":{"2550":100},"n23j116107":{"2550":100},"240192-efterlev":{"58":300,"2550":100},"240159-efterlev":{"58":120},"cc006283-001":{"58":420},"240124":{"58":0},"sam5728":{"58":25},"sam5727":{"58":30},"230140":{"58":2},"32470":{"59":100},"sam5328":{"59":3},"sam5368":{"59":60},"sam5420":{"59":15},"sam5419":{"59":15},"sam5418":{"59":20},"sam5417":{"59":15},"sam5416":{"59":15},"9455":{"59":11},"9008":{"59":130},"sam3242":{"59":25},"sam5384":{"59":50},"1051":{"59":10},"19213":{"59":155},"230192":{"59":70},"9151":{"59":200},"30-hsr213":{"59":1},"sam5347":{"59":20},"240105":{"59":30},"11076":{"59":200},"12026":{"59":125},"240305":{"2550":16},"sam5427":{"59":150},"240125":{"59":20,"574":82},"230186":{"59":220},"sam4171":{"59":180},"n24jgo155244a421bs":{"59":30},"n24jgo155244a421bm":{"59":40},"n24jgo155240a950al":{"59":50},"n24jgo155240a950axl":{"59":30},"190109":{"59":50,"574":27},"230131":{"59":80},"240131":{"59":22},"10782":{"2550":49},"sam3838":{"59":40},"220137":{"59":135},"210132":{"59":160},"230106":{"59":20},"240209":{"59":90,"2550":2},"43357":{"59":170},"246628":{"59":150},"10020707":{"2550":20},"10784":{"59":20},"689512":{"59":20},"sam5356":{"59":5},"sam5422":{"59":50},"599999961":{"59":50},"sam5388":{"59":100},"sam5403":{"59":60},"1071674":{"4740":2},"sam5330":{"59":40},"240194":{"59":80},"32317":{"59":25},"240150":{"59":70},"240154":{"4740":0},"240120":{"59":100,"574":270},"30-hsskcs5":{"59":80},"240182":{"4740":14},"240345":{"59":20},"240322":{"59":25},"240323":{"59":25},"240331":{"59":25},"210117":{"59":35},"220109":{"59":3},"220132":{"59":50},"230163":{"59":50},"230174":{"59":200,"574":23},"sam3824":{"59":20},"230304":{"59":80},"220131":{"59":45},"220128":{"59":65},"230141":{"59":50},"230193":{"59":310},"230187":{"59":65},"230126":{"59":50},"220112":{"59":60,"272":234},"220138":{"59":25},"230123":{"59":50},"cc006292-001":{"4740":45},"cc006285-001":{"2550":61},"230137":{"59":20},"800302500":{"59":40},"10005":{"59":25},"230157":{"59":10},"sam3806":{"59":20},"210166":{"59":75},"210154":{"59":60},"210171":{"59":50},"220160":{"59":160},"do-cy-23-12-008":{"59":15},"do-cy-23-12-007":{"59":15},"4817429":{"59":15},"4817169":{"59":20},"fabercastellpakke4":{"59":20},"miecopak2":{"59":25},"240162":{"59":75},"sam5442":{"59":10},"nosam24j20172-01":{"59":40},"nosam24j20172-03":{"59":40},"n24j114997":{"59":70},"n24j20444mat04":{"59":300},"n24j20444sjok04":{"59":300},"n24j20138-3":{"59":200},"29969":{"59":20},"29970":{"59":2},"29971":{"59":20},"230114":{"59":20},"230116":{"59":10},"230118":{"59":20},"ko15-0120ls041-02":{"59":40,"2550":47},"ko15-0120ls038-02":{"59":40,"2550":47},"ko15-0120ls037-02":{"59":25,"2550":46},"btb-10084":{"59":16},"btb-10085":{"59":15},"btb-10121":{"59":10},"btb-10122":{"59":12},"btb-10123":{"59":10},"nosam24jnolosweeds-4":{"59":200},"10004":{"59":45},"230175":{"59":10},"139157a216":{"59":65,"574":29},"230194":{"59":50,"574":23},"sam5441":{"59":20},"n23j116126":{"4740":50},"n23j116127":{"4740":40},"n23j116109":{"59":128},"240103":{"59":20},"240111":{"59":100},"240198":{"574":40},"240200":{"59":90},"210111":{"59":31},"210138":{"59":50},"705223105028":{"59":15,"2550":2},"705222103028":{"59":15,"2550":2},"ko15-0101ls041-02":{"59":40},"ko15-0101ls037-02":{"59":60},"ko15-0101ls038-02":{"59":100},"13334":{"58":26},"sam5369":{"272":100},"14662":{"272":75},"240129":{"272":75},"999410014":{"272":135},"999410052":{"272":290},"220117":{"272":110},"240138":{"272":0},"230168":{"272":0},"10019915":{"272":40},"240316":{"2550":16},"240329":{"272":25},"240102":{"272":0},"kfyi09":{"272":27},"kflp89":{"272":75},"240183":{"272":90},"34447":{"574":65},"30697":{"574":9},"25603":{"4740":5},"30-hsscskf3":{"272":50},"n24j76442":{"272":200},"n24j87206":{"272":200},"n24j20444mat01":{"272":300},"n24jgo396900a020a58":{"4740":150},"230132":{"272":46},"240148":{"574":0},"230158":{"272":51},"5796rpv":{"272":30},"n24j501148":{"272":75},"13490":{"58":26},"13250":{"2550":122},"13252":{"2550":122},"999530500":{"272":30},"10783":{"2550":49},"240184":{"272":0},"1017404":{"2550":16},"43356":{"272":75},"240178":{"272":90},"240176":{"272":55},"240168":{"272":185},"240169":{"2550":0},"14549":{"58":50},"12001":{"272":20},"230135":{"272":30},"220171":{"272":85},"230161":{"272":50},"220162":{"272":50},"230160":{"272":50},"220168":{"272":30},"210101":{"272":40},"230134":{"272":100},"230191":{"272":21},"25263":{"272":0},"cc007052-001":{"272":50},"cc007053-001":{"272":75},"cc006288-001":{"58":55},"230143":{"272":70},"220125":{"272":35},"200142":{"272":50},"kfyi19":{"272":30},"n24j501195":{"272":75},"n24j20444hb1":{"272":300},"n24j20444sjok01":{"272":300},"n24jgf2404":{"272":300},"n24jgf2408":{"272":300},"n24jgf2410":{"272":300},"n24jgf2428":{"272":300},"sam5415":{"272":13},"sam5414":{"272":20},"sam5413":{"272":13},"sam5412":{"272":16},"sam5411":{"272":20},"10020706":{"272":95},"n24jgf2438":{"272":9},"n24jgf2403":{"272":300},"n24jgf2437":{"272":25},"230170":{"272":30},"230307":{"272":50},"n23s116085":{"272":15},"n170105":{"272":25},"n24j118383":{"272":51},"n24j118385":{"272":75},"bs210604":{"272":40},"nosam24jnolosweeds-2":{"272":200},"nosam24jnolosweeds-1":{"272":200},"n24j65402":{"272":200},"n24jgf2429":{"272":300},"240307":{"272":50},"n24j55407":{"272":60},"nosam24jsweeds-1":{"272":200},"240173-efterlev":{"272":100},"240157":{"574":62},"240156":{"574":40},"n-3402white":{"272":60},"nosam24j20200-1":{"272":100},"nosam24j20200-2":{"272":100},"nosam24j20200-3":{"272":100},"sam5355":{"574":30},"90100113pearl":{"574":10},"10010":{"4740":15},"10011":{"4740":15},"10012":{"4740":15},"sam5404":{"574":65},"sam2021":{"574":13},"210134":{"574":20},"1064":{"574":10},"sam5382":{"574":30},"139157a209":{"574":10},"139157a214":{"574":0},"139157a210":{"574":5},"240128":{"574":0},"sam5387":{"574":25},"240143":{"574":1},"sam5423":{"574":65},"240132":{"574":15},"sam5396":{"574":75},"sam5392":{"574":75},"sam5391":{"574":0},"sam5344":{"574":10},"240332":{"574":5},"sam6014":{"574":0},"3001":{"574":25},"sam6017":{"574":40},"14017274":{"574":15},"230202":{"59":180},"gp181132134102-1121m":{"574":25},"210156":{"574":55},"18296":{"574":36},"10019595":{"574":25},"10829-gb-240":{"574":25},"sam5402":{"574":0},"sam5448":{"574":9},"240118":{"574":20},"210161":{"574":15},"10020258":{"574":5},"230196":{"574":40},"230156":{"574":29},"94887280":{"574":30},"osoebcs908":{"574":15},"n24j35403":{"574":200},"n24j20138-4":{"574":200},"230133":{"574":0},"cc006286-001":{"4740":30},"kfjd89":{"574":45},"84720472":{"574":10},"84720308":{"574":0},"14017172":{"574":0},"240112":{"574":120},"240115":{"574":75},"139161a209":{"574":25},"139161a216":{"574":15},"240339":{"574":5},"sam6016":{"574":62},"n24j117665":{"4740":80},"10735":{"574":25},"n24j2047803":{"574":200},"10806-w-mg-200":{"574":1},"d106a300110":{"574":25},"230153":{"574":70},"xkfxht02":{"574":15},"1062516":{"574":45},"230204":{"574":11},"sam6041":{"574":0},"230127":{"574":30},"n24j117664":{"4740":80},"sam6015":{"574":20},"3586847":{"574":0},"sam5428":{"574":35},"689511":{"574":5},"1052-1":{"574":30},"1018213":{"574":5},"sam5335":{"574":10},"sam5336":{"574":10},"sam5337":{"574":10},"1040":{"574":35},"502903":{"574":10},"571623":{"574":50},"10019764":{"574":15},"220108":{"574":50},"230166":{"574":0},"220306":{"574":5},"230101":{"574":40},"230104":{"574":60},"220157":{"574":45},"230145":{"574":0},"39261":{"574":10},"39286":{"574":35},"201462":{"574":30},"5795rpv":{"574":35},"cc006293-001":{"4740":45},"15791":{"574":0},"230128":{"574":40},"sam3210":{"574":16},"1061048":{"574":15},"230136":{"574":15},"230151":{"574":69,"2550":1},"200139":{"574":0},"210135":{"574":20},"tdwgb2131802":{"574":18},"btb-10117":{"574":15},"240349":{"574":10},"1072197":{"574":5},"gf0003":{"574":0},"miecopak3":{"574":5},"10019648":{"574":40},"n24j20444hb4":{"574":300},"n24j20444mat05":{"574":300},"gf2441":{"574":200},"246624":{"574":25},"840025":{"4740":15},"840026":{"4740":15},"10832-200":{"4740":45},"10832-220":{"4740":40},"sam5394":{"574":30},"240317":{"574":15},"90603005gold":{"574":10},"220129":{"574":20},"sam4079":{"574":6},"sam4108":{"574":29},"18290":{"574":20},"1017358":{"574":5},"1067350":{"574":5},"8208":{"574":8},"689510":{"574":5},"39256":{"574":35},"230164":{"574":35},"1063":{"574":15},"n24jgf2441":{"574":300},"223-1":{"574":25},"223-2":{"574":20},"220118":{"574":80},"230173":{"574":0},"nosam23j1003":{"574":35},"1068623":{"2550":10},"sam5354":{"2550":22},"34495":{"2550":50},"39335":{"2550":20},"39258":{"2550":30},"tdwgb0041005":{"2550":45},"90100114pearl":{"2550":20},"693800":{"2550":5},"8209":{"2550":10},"sam5349":{"2550":6},"840340":{"2550":10},"134553":{"2550":20},"7007-1":{"2550":30},"34625":{"2550":25},"b-21-21":{"2550":20},"201460":{"2550":5},"200480":{"2550":35},"sam5370":{"2550":61},"34472":{"2550":55},"ka-030":{"2550":0},"sam3839":{"2550":67},"sam6020":{"2550":20},"230155":{"2550":25},"d1061300110":{"2550":0},"220111":{"2550":44},"10019593":{"2550":10},"230105":{"2550":40},"20759901btb":{"2550":11},"10018211":{"2550":10},"sam4141":{"2550":14},"94302436":{"2550":44},"230205":{"2550":259},"18373":{"2550":31},"84720772":{"2550":0},"sam5429":{"2550":49},"240109":{"2550":15},"240110":{"2550":84},"240195":{"2550":100},"240107":{"2550":35},"230206":{"2550":30},"10019721":{"2550":0},"10019720":{"2550":15},"3586663":{"2550":85},"ko06-0130ls000":{"2550":110},"sam3843":{"2550":61},"220156":{"2550":0},"b-22-12":{"2550":25},"230125":{"2550":0},"240350":{"2550":9},"gf0004":{"2550":4},"ospak5-gf":{"2550":15},"240208":{"2550":20},"140144":{"2550":25},"nosam24j2048003":{"2550":200},"n24jtc396390a020am":{"4740":150},"n24jtc396390a020al":{"4740":150},"n24jtc396390a020axl":{"4740":150},"230142":{"2550":20},"18386":{"2550":50},"14018274":{"2550":15},"84720608":{"2550":0},"240134":{"2550":0},"220122":{"2550":15},"140145":{"2550":40},"sam6018":{"2550":4},"220155":{"2550":30},"230146":{"2550":35},"sam5395":{"2550":16},"dwmt73801-1":{"2550":0},"240171":{"2550":70},"sam5331":{"2550":17},"18365":{"2550":0},"240113":{"2550":125,"4740":50},"240116":{"2550":55,"4740":50},"32315":{"2550":25},"sam5440":{"2550":9,"4740":4},"sam3018":{"2550":20},"10019522":{"574":6},"230162":{"2550":0},"230139":{"2550":0},"210160":{"2550":15},"200129":{"2550":60},"cc006291-001":{"4740":30},"sam5443":{"2550":20},"n24j117666":{"2550":100},"n24j20444mat06":{"2550":300},"sam5439":{"2550":23},"sam5383":{"2550":50},"240347":{"2550":10},"210140":{"2550":40},"sam5431":{"2550":0},"29331":{"2550":22},"n24jgf2442":{"2550":300},"20753401btb":{"2550":25},"240160":{"2550":13,"4740":5},"240197":{"2550":78},"220127":{"2550":10,"4740":5},"sh9608-4":{"2550":5},"sh9608-5":{"2550":0},"sh9608-6":{"2550":0},"705223105038":{"2550":12},"705222103038":{"2550":12},"sam5389":{"2550":0},"sam5390":{"2550":23},"nosam222001":{"2550":2},"n23j10806-w-mg-200":{"2550":95},"n23j10806-w-mg-220":{"2550":110},"nosam23j1002":{"2550":50},"nosam23j1001":{"2550":50},"velg mellom lengde georg jensen damasksenget\u00f8y walnut":{"2550":275},"n24j10828-wa-200":{"2550":275},"n24j10828-wa-220":{"2550":275},"240137":{"2550":20},"n24j2047804":{"2550":50},"240205":{"2550":27},"240206":{"2550":83},"240204":{"2550":0},"139157a268":{"2550":100},"sam5334":{"4740":15},"sam5333":{"4740":15},"sam5332":{"4740":15},"20739501btb":{"4740":15},"240336":{"4740":5},"sam5348":{"4740":13},"240135":{"4740":15},"18297":{"4740":20},"14023172":{"4740":4},"sam5361":{"4740":10},"10019713":{"4740":20},"3587583":{"4740":20},"10019823":{"4740":20},"sam5400":{"4740":42},"240155":{"4740":0},"240117":{"4740":100},"240172":{"4740":95},"240167":{"4740":75},"20811794btb":{"4740":35},"230178":{"4740":25},"sam3778":{"4740":10},"sam5433":{"4740":0},"282828":{"4740":4},"ka-032":{"4740":1},"32042":{"4740":20},"240146":{"4740":50},"5744167110":{"4740":1},"n24j20444mat07":{"4740":300},"18385":{"4740":40},"sam5360":{"4740":15},"sam5399":{"4740":45},"sam5398":{"4740":65},"sam5397":{"4740":40},"sam3777":{"4740":10},"5744167178":{"4740":40},"5744167152":{"4740":5},"sam5385":{"4740":5},"sam5386":{"4740":2},"1025897":{"4740":10},"sam5345":{"4740":7},"240193":{"4740":35},"200405":{"4740":30},"bb02-bbl":{"4740":10},"17287":{"4740":5},"5744166661":{"4740":15},"5744168339":{"4740":25},"sam5432":{"4740":14},"sam5436":{"4740":5},"sam5437":{"4740":30},"840109":{"4740":15},"210209":{"4740":0},"33958":{"4740":5},"sam5438":{"4740":25},"dcd771d2-qw":{"4740":25},"nosam24j2044902":{"4740":150},"nosam24j2044903":{"4740":150},"nosam24j2044904":{"4740":150},"nosam24j20172-05":{"4740":40},"nosam24j20172-06":{"4740":40},"n24j118871":{"4740":150},"n24j118872":{"4740":150},"nosam24j20172-09":{"4740":40},"nosam24j20172-10":{"4740":50},"sam4058":{"4740":45},"sam5664":{"4740":25},"sam5709":{"4740":25},"240119":{"4740":50},"25450":{"4740":5},"25455":{"4740":5},"n24jgp141840b287a":{"57":150},"n24jgp141900b287a":{"57":150},"25602":{"4740":5},"20853":{"574":32},"20813":{"574":32},"n24j1002-1":{"59":200},"n24j33115":{"59":200},"1017403":{"4740":21},"13609":{"272":52},"13607":{"272":80},"13605":{"272":52},"13601":{"272":64},"13600":{"272":80},"690148":{"2550":22},"240304":{"574":75},"25601":{"4740":5},"30-hvc90":{"58":2},"20833":{"574":32},"n24j33114":{"272":200},"n24j33116":{"58":200},"n24j33117":{"272":200},"n24j1008-1":{"58":3},"n24j45214":{"58":200,"59":200},"n24j45215":{"58":200,"59":200},"n24j45216":{"58":200,"59":200},"n24j45217":{"58":200,"59":200},"n23j116135":{"4740":80},"n23j116134":{"4740":100},"1309-01":{"58":25},"1310-07":{"58":25},"1309-02":{"58":30},"1310-08":{"58":30},"693804":{"59":3},"4343501":{"59":25},"230203":{"59":180},"693075":{"2550":201},"693076":{"2550":201},"692633":{"2550":201},"690149":{"2550":22},"34448":{"574":65},"34449":{"574":65},"693802":{"2550":102},"1062831":{"59":20},"1003477":{"59":20},"1051601":{"59":20},"1027043":{"59":20},"1027044":{"59":20},"20843":{"574":32},"n24j1001-1":{"272":200},"n24j1005-1":{"59":200},"n24j33119":{"59":200},"240165":{"2550":9,"4740":4},"83440":{"272":100},"83441":{"272":100},"n24j1007-1":{"272":200},"n24j33118":{"272":200},"n24jtixkh0011":{"272":100},"n24jtibn002":{"272":100},"n24jtixkh0012":{"272":100},"n24jtixkh0013":{"272":100},"10833":{"4740":40},"240301":{"574":0},"240302":{"574":0},"ka-010":{"574":0},"240199":{"574":62},"30740":{"574":0},"10014933":{"574":6},"n23j116123":{"574":70},"1071673":{"4740":2},"n24j66510":{"2550":800},"210193":{"2550":20},"210194":{"2550":20},"1017366":{"4740":52},"240303":{"2550":0},"220307":{"4740":10},"220335":{"4740":10},"220336":{"4740":10},"220308":{"4740":10},"220334":{"4740":10},"240180":{"4740":14},"cc006290-001":{"4740":30},"dcd710d2-qw":{"4740":25},"n24jgo396630a020al":{"4740":150},"n24jsc516063a422a36-":{"4740":150},"n24jsc516063a422a41-":{"4740":150},"n24jgo396630a020axl":{"4740":150},"n23j117653":{"4740":80},"n23j116553":{"4740":40},"n23j116552":{"4740":50},"5923cow-tan":{"4740":25},"5923cow-bla":{"4740":25}}';
        $presentData = json_decode($presentDataString,true);

        $soItemString = '{"240113":{"itemno":"240113","quantity":"66","description":"Urban Copenhagen kuffert 3 s\u00e6t beige"},"KO06-0130LS000":{"itemno":"KO06-0130LS000","quantity":"6","description":"Lounge lampe med sound by JBL H26"},"139157A268":{"itemno":"139157A268","quantity":"19","description":"Verner Panton VP9 portable led messing belagt"},"240171":{"itemno":"240171","quantity":"29","description":"Laguiole by House of Chefs r\u00f8d, hvid og vand 18 st"},"18386":{"itemno":"18386","quantity":"43","description":"Cavalluzzi rygs\u00e6k sort"},"94302436":{"itemno":"94302436","quantity":"20","description":"Eva Trio gryder collection box st\u00e5l 4 dele genbrug"},"18373":{"itemno":"18373","quantity":"20","description":"Cavalluzzi rygs\u00e6k cognac"},"240107":{"itemno":"240107","quantity":"25","description":"Vex\u00f8 tallerkens\u00e6t 18 dele"},"7007-1":{"itemno":"7007-1","quantity":"25","description":"Spring Copenhagen uno ora b\u00e6nk"},"240116":{"itemno":"240116","quantity":"15","description":"Urban Copenhagen kuffert 3 s\u00e6t gul"},"220111":{"itemno":"220111","quantity":"14","description":"Absolute Sport airhockey bord"},"220155":{"itemno":"220155","quantity":"14","description":"Jernv\u00e6rket by House of Chefs elektrisk bordgrill"},"34693":{"itemno":"34693","quantity":"12","description":"Alfi Econscious termokande beige"},"692633":{"itemno":"692633","quantity":"30","description":"Hammersh\u00f8i vandglas 37cl klar 2 stk"},"693075":{"itemno":"693075","quantity":"30","description":"Hammersh\u00f8i hvidvinsglas 35cl klar 2 stk"},"693076":{"itemno":"693076","quantity":"30","description":"Hammersh\u00f8i r\u00f8dvinsglas 49cl klar 2 stk"},"690147":{"itemno":"690147","quantity":"30","description":"K\u00e4hler Omaggio circulare vase H20"},"690148":{"itemno":"690148","quantity":"30","description":"K\u00e4hler Omaggio circulare vase H12,5"},"690149":{"itemno":"690149","quantity":"1","description":"K\u00e4hler Omaggio circulare vase H31"},"140145":{"itemno":"140145","quantity":"10","description":"Frandsen Grasp portable bordlampe sort H47"},"1017366":{"itemno":"1017366","quantity":"40","description":"Royal Copenhagen bl\u00e5 mega tallerken 27cm"},"10020707":{"itemno":"10020707","quantity":"9","description":"Georg Jensen Bloom vase guld H22"},"10020708":{"itemno":"10020708","quantity":"9","description":"Georg Jensen Bloom sk\u00e5l guld \u00d816"},"200129":{"itemno":"200129","quantity":"8","description":"Tisvilde terrassevarmer"},"230155":{"itemno":"230155","quantity":"8","description":"CPH opustelig lounge madras"},"230206":{"itemno":"230206","quantity":"8","description":"Absolute Sport elektrisk skateboard 150W, 2000mAh"},"210140":{"itemno":"210140","quantity":"7","description":"Workforz DAB arbejdsradio"},"230105":{"itemno":"230105","quantity":"7","description":"Tobias Jacobsen terrassevarmer sort"},"140144":{"itemno":"140144","quantity":"6","description":"Frandsen Grasp portable bordlampe hvid H47"},"10018211":{"itemno":"10018211","quantity":"6","description":"Georg Jensen Bernadotte pitcher 2,2L"},"210193":{"itemno":"210193","quantity":"5","description":"Absolute Sport cykel hometrainer"},"B-21-21":{"itemno":"B-21-21","quantity":"1","description":"Stelton RIG-TIG Foodie toaster, elkedel og kaffe"},"32315":{"itemno":"32315","quantity":"3","description":"Zone Firefly LED lanterne sort"},"134553":{"itemno":"134553","quantity":"3","description":"Verner Panton VP11 wire skammel "},"230146":{"itemno":"230146","quantity":"3","description":"Tisvilde barvogn "},"240209":{"itemno":"240209","quantity":"20","description":"Tisvilde h\u00e6ngek\u00f8je"},"240316":{"itemno":"240316","quantity":"23","description":"GJD Noble h\u00e5nds\u00e6be og h\u00e5ndcreme i gavepose"},"240110":{"itemno":"240110","quantity":"22","description":"House of Chefs slowjuicer "},"240120":{"itemno":"240120","quantity":"0","description":"Jesper Koch airfryer 10L"},"240125":{"itemno":"240125","quantity":"55","description":"Murphy Copenhagen plet-og t\u00e6pperenser"},"230153":{"itemno":"230153","quantity":"40","description":"Hi5 party h\u00f8jttaler X Large sort"},"246624":{"itemno":"246624","quantity":"25","description":"Eva Trio multi mosaic sauterpande 24cm "},"10833":{"itemno":"10833","quantity":"4","description":"GJD hovedpude recycled andedun 60x63"},"210156":{"itemno":"210156","quantity":"35","description":"House of Chefs multi cooker "},"240112":{"itemno":"240112","quantity":"23","description":"Urban Copenhagen kuffert 2 s\u00e6t beige"},"26325":{"itemno":"26325","quantity":"7","description":"Rosendahl GC solar sand H18,5"},"230156":{"itemno":"230156","quantity":"20","description":"Tobias Jacobsen sodavandsmaskine "},"220108":{"itemno":"220108","quantity":"23","description":"House of Chefs bagemaskine"},"26324":{"itemno":"26324","quantity":"1","description":"Rosendahl GC solar sand H25"},"571623":{"itemno":"571623","quantity":"9","description":"Eva Solo Radiant LED lampe 24cm sort"},"10020705":{"itemno":"10020705","quantity":"30","description":"Georg Jensen Bernadotte sk\u00e5l guld \u00d810"},"10019595":{"itemno":"10019595","quantity":"1","description":"Georg Jensen Bernadotte pitcher 1L"},"139157A216":{"itemno":"139157A216","quantity":"0","description":"Verner Panton VP9 portable led i blank hvid"},"230151":{"itemno":"230151","quantity":"15","description":"Tisvilde udend\u00f8rs pejs sort"},"230196":{"itemno":"230196","quantity":"8","description":"Skagen bambus dyne 200x220"},"D106A300110":{"itemno":"D106A300110","quantity":"7","description":"FDB D106 Philip Bro skammel eg natur"},"10832-220":{"itemno":"10832-220","quantity":"34","description":"GJD dyne recycled andedun 140x220"},"201462":{"itemno":"201462","quantity":"3","description":"Lyngby Tura vase H22,5"},"10832-200":{"itemno":"10832-200","quantity":"44","description":"GJD dyne recycled andedun 140x200"},"25602":{"itemno":"25602","quantity":"10","description":"Rosendahl GC ovnfast fad glas 24x24"},"230164":{"itemno":"230164","quantity":"3","description":"Stockholm kontorstol gr\u00e5"},"25601":{"itemno":"25601","quantity":"10","description":"Rosendahl GC ovnfast fad glas 24x12,5 cm"},"25455":{"itemno":"25455","quantity":"10","description":"Rosendahl GC sk\u00e5l glas \u00d824,5"},"25450":{"itemno":"25450","quantity":"10","description":"Rosendahl GC glassk\u00e5le 4 stk \u00d815"},"240170":{"itemno":"240170","quantity":"29","description":"Laguiole by House of Chefs stegepande 28cm "},"240106":{"itemno":"240106","quantity":"101","description":"Edge kaffemaskine"},"25603":{"itemno":"25603","quantity":"10","description":"Rosendahl GC ovnfast fad glas 38x25"},"240179":{"itemno":"240179","quantity":"70","description":"Laguiole Noir by House of Chefs forsk\u00e6rers\u00e6t"},"220147":{"itemno":"220147","quantity":"15","description":"Skagen bambus dyne 140x220cm"},"220146":{"itemno":"220146","quantity":"10","description":"Skagen bambus dyne 140x200cm"},"240108":{"itemno":"240108","quantity":"14","description":"Vex\u00f8 sk\u00e5les\u00e6t 3 dele"},"240151":{"itemno":"240151","quantity":"50","description":"Explorer rygs\u00e6k 35L"},"9009":{"itemno":"9009","quantity":"40","description":"Dyberg Larsen Stockholm bordlampe hvid"},"18294":{"itemno":"18294","quantity":"60","description":"Cavalluzzi toilettaske cognac"},"240181":{"itemno":"240181","quantity":"50","description":"Laguiole Noir by House of Chefs osteknive"},"230167":{"itemno":"230167","quantity":"45","description":"Edge elkedel"},"5924COW-BLA":{"itemno":"5924COW-BLA","quantity":"12","description":"Markberg Ryder toilettaske sort"},"1017349":{"itemno":"1017349","quantity":"9","description":"Royal Copenhagen bl\u00e5 mega m\u00e6lkekande 38cl"},"693801":{"itemno":"693801","quantity":"7","description":"Sofie Linde Poppery vase H12"},"220112":{"itemno":"220112","quantity":"20","description":"Kitchenmaster blender 1L sort "},"240183":{"itemno":"240183","quantity":"55","description":"Laguiole Noir by House of Chefs knivs\u00e6t 3 stk "},"999410052":{"itemno":"999410052","quantity":"50","description":"Cotton Lover h\u00e5ndkl\u00e6der dusty green 2+2+2"},"999410014":{"itemno":"999410014","quantity":"38","description":"Cotton Lover h\u00e5ndkl\u00e6der abricot 2+2+2"},"240168":{"itemno":"240168","quantity":"35","description":"Laguiole by House of Chefs stegepande 20cm "},"10020706":{"itemno":"10020706","quantity":"35","description":"Georg Jensen Bernadotte sk\u00e5l \u00d818"},"34447":{"itemno":"34447","quantity":"30","description":"Mors\u00f8 sort Fossil pande 20cm"},"230143":{"itemno":"230143","quantity":"20","description":"Murphy elektrisk reng\u00f8ringsb\u00f8rste hvid 10 b\u00f8rster"},"220125":{"itemno":"220125","quantity":"10","description":"Tobias Jacobsen solcelle havelampe"},"240157":{"itemno":"240157","quantity":"2","description":"Urban Copenhagen tumbler gr\u00f8n"},"230108":{"itemno":"230108","quantity":"55","description":"Cavalluzzi stor trolley sort"},"240169":{"itemno":"240169","quantity":"18","description":"Laguiole by House of Chefs stegepande 24cm "},"9008":{"itemno":"9008","quantity":"41","description":"Dyberg Larsen Stockholm gulvlampe hvid"},"240180":{"itemno":"240180","quantity":"101","description":"Laguiole Noir by House of Chefs knivblok "},"29969":{"itemno":"29969","quantity":"1","description":"Margrethe sk\u00e5ls\u00e6t 8 dele nordic green"},"246628":{"itemno":"246628","quantity":"30","description":"Eva Trio multi mosaic pande 28cm "},"230304":{"itemno":"230304","quantity":"8","description":"GJD h\u00e5ndkl\u00e6der stripe 2+2+2 BCI"},"32470":{"itemno":"32470","quantity":"10","description":"Peugeot Paris Nature salt & peber "},"693802":{"itemno":"693802","quantity":"12","description":"Sofie Linde Poppery krus 27cl"},"220132":{"itemno":"220132","quantity":"13","description":"Brewmaster fad\u00f8lsanl\u00e6g"},"25608":{"itemno":"25608","quantity":"10","description":"Rosendahl Grand Cru cocotte glas 4L med l\u00e5g "},"240150":{"itemno":"240150","quantity":"15","description":"Explorer duffeltaske 40L"},"240154":{"itemno":"240154","quantity":"6","description":"Stockholm bord egetr\u00e6sfiner stor"},"220128":{"itemno":"220128","quantity":"23","description":"House of Chefs sous vide gryde"},"9151":{"itemno":"9151","quantity":"30","description":"Dyberg Larsen ARCH gulvlampe sort med messing"},"12026":{"itemno":"12026","quantity":"35","description":"Miiego Twister h\u00f8jttaler"},"CC006637-001":{"itemno":"CC006637-001","quantity":"10","description":"Mauviel bestik 16 dele"},"11076":{"itemno":"11076","quantity":"42","description":"Miiego headphone moove 45i pro sort"},"230123":{"itemno":"230123","quantity":"15","description":"Kitchenmaster pasta maskine sort"},"230192":{"itemno":"230192","quantity":"16","description":"House of Chefs p\u00e5l\u00e6gsmaskine"},"210138":{"itemno":"210138","quantity":"18","description":"Explorer sovepose med andedun"},"210132":{"itemno":"210132","quantity":"49","description":"Tobias Jacobsen kuffert og carryon sort"},"230193":{"itemno":"230193","quantity":"85","description":"Jesper Koch panini grill"},"14672":{"itemno":"14672","quantity":"51","description":"Aida Raw creative bestik 48 dele st\u00e5l"},"94887406":{"itemno":"94887406","quantity":"8","description":"Eva Solo Legio Nova 2 salatsk\u00e5le 1,4 l + 2,5 l"},"230129":{"itemno":"230129","quantity":"24","description":"Bodycare \u00f8jenmassage maske hvid"},"240305":{"itemno":"240305","quantity":"1","description":"GJD h\u00e5ndkl\u00e6der 2+2+2 light oak BCI"},"240160":{"itemno":"240160","quantity":"64","description":"Explorer udsigtskikkert "},"240124-1":{"itemno":"240124-1","quantity":"15","description":"Explorer h\u00e6ngek\u00f8je med myggenet og presenning V2"},"240182":{"itemno":"240182","quantity":"101","description":"Laguiole Noir by House of Chefs knivs\u00e6t 6 stk "},"240178":{"itemno":"240178","quantity":"42","description":"Laguiole by House of Chefs forsk\u00e6rers\u00e6t  "},"KFYI19":{"itemno":"KFYI19","quantity":"3","description":"Kreafunk Beam lampe sand"},"43356":{"itemno":"43356","quantity":"30","description":"Caterpillar bits\u00e6t 45 pcs"},"KFLP89":{"itemno":"KFLP89","quantity":"30","description":"Kreafunk Theo Sport clip in ear sand"},"220171":{"itemno":"220171","quantity":"25","description":"Explorer termo flaske & kopper"},"230191":{"itemno":"230191","quantity":"25","description":"iiFUN oplader"},"200107":{"itemno":"200107","quantity":"20","description":"Explorer rygs\u00e6k 20L"},"230159":{"itemno":"230159","quantity":"20","description":"Explorer termokande 2,3 l"},"230307":{"itemno":"230307","quantity":"18","description":"GJD h\u00e5ndkl\u00e6der med vaskeklude green 2V+2 BCI"},"14662":{"itemno":"14662","quantity":"16","description":"Aida Raw creative bestik 16 dele st\u00e5l"},"220117":{"itemno":"220117","quantity":"15","description":"Cru vins\u00e6t"},"230158":{"itemno":"230158","quantity":"15","description":"Explorer madbeholder"},"240156":{"itemno":"240156","quantity":"15","description":"Urban Copenhagen tumbler beige"},"CC007053-001":{"itemno":"CC007053-001","quantity":"15","description":"Mauviel k\u00f8dkniv"},"230134":{"itemno":"230134","quantity":"14","description":"Hi5 mini h\u00f8jttaler"},"10783":{"itemno":"10783","quantity":"0","description":"GJD toilettaske Herringbone varm beige BCI"},"210101":{"itemno":"210101","quantity":"10","description":"Hi5 bordlampe med indbygget oplader"},"230132":{"itemno":"230132","quantity":"10","description":"Absolute Sport lille bordtenniss\u00e6t"},"13490":{"itemno":"13490","quantity":"2","description":"Aida Confetti salatsk\u00e5l pistachio"},"200142":{"itemno":"200142","quantity":"9","description":"Vex\u00f8 Pro strygest\u00e5l"},"10019915":{"itemno":"10019915","quantity":"7","description":"Georg Jensen Bernadotte porcel\u00e6nsk\u00e5l 2 stk. \u00d87,4"},"CC007052-001":{"itemno":"CC007052-001","quantity":"7","description":"Mauviel gr\u00f8nsagskniv"},"240307":{"itemno":"240307","quantity":"6","description":"GJD Abild viskestykke og karklude walnut BCI"},"13252":{"itemno":"13252","quantity":"17","description":"Aida Karim Rashid hvidvin krystalglas 2 stk"},"240129":{"itemno":"240129","quantity":"5","description":"Coleman shaver unoblade"},"BS210604":{"itemno":"BS210604","quantity":"5","description":"Bj\u00f8rn Borg duffle toilettaske sort"},"220168":{"itemno":"220168","quantity":"4","description":"Grillhandske l\u00e6der "},"230161":{"itemno":"230161","quantity":"4","description":"Explorer termo kop 450ml"},"12001":{"itemno":"12001","quantity":"3","description":"By Lassen Kubus micro sort 2 stk"},"230135":{"itemno":"230135","quantity":"3","description":"Coleman travel mini shaver"},"230160":{"itemno":"230160","quantity":"2","description":"Explorer termoflaske 1L"},"240329":{"itemno":"240329","quantity":"2","description":"Gosh makeup pakke 2 - 3x lip balms"},"1017404":{"itemno":"1017404","quantity":"2","description":"Royal Copenhagen hvid riflet tallerken 27cm"},"13250":{"itemno":"13250","quantity":"26","description":"Aida Karim Rashid bourgogne krystalglas 2 stk"},"14755":{"itemno":"14755","quantity":"22","description":"Aida Raw sk\u00e6rebr\u00e6ts\u00e6t 2 stk teak"},"230201":{"itemno":"230201","quantity":"60","description":"Champagne sabel "},"240164":{"itemno":"240164","quantity":"50","description":"Jesper Koch pande Hexagon 20cm"},"18362":{"itemno":"18362","quantity":"45","description":"Tobias Jacobsen toilettaske sort"},"240319":{"itemno":"240319","quantity":"4","description":"Gosh skin care pakke 2A - Travel"},"CC007055-001":{"itemno":"CC007055-001","quantity":"40","description":"Mauviel kokkekniv 20cm"},"20813":{"itemno":"20813","quantity":"22","description":"Rosendahl GC Take krus 30cl gr\u00e5 2 stk"},"20853":{"itemno":"20853","quantity":"4","description":"Rosendahl GC Take sk\u00e5l \u00d815 cm gr\u00e5 2 stk"},"16060":{"itemno":"16060","quantity":"30","description":"Aida Raw Colour krus 30cl 6 stk"},"CC007054-001":{"itemno":"CC007054-001","quantity":"30","description":"Mauviel santoku kniv 18cm"},"30-HSSCKF2":{"itemno":"30-HSSCKF2","quantity":"29","description":"Santo forsk\u00e6rers\u00e6t"},"13609":{"itemno":"13609","quantity":"24","description":"Karim Rashid d\u00e6kkeserviet 45x30 pistachio"},"230150":{"itemno":"230150","quantity":"20","description":"Explorer outdoor lampe sort"},"13605":{"itemno":"13605","quantity":"6","description":"Karim Rashid d\u00e6kkeserviet 45x30 olive"},"13607":{"itemno":"13607","quantity":"18","description":"Karim Rashid d\u00e6kkeserviet 45x30 aqua"},"1017380":{"itemno":"1017380","quantity":"10","description":"Royal Copenhagen hvid riflet krus 33cl 2 stk"},"5924COW-TAN":{"itemno":"5924COW-TAN","quantity":"24","description":"Markberg Ryder toilettaske tan"},"BTB-10090":{"itemno":"BTB-10090","quantity":"10","description":"GJD computersleeve Herringbone varm beige BCI"},"240330":{"itemno":"240330","quantity":"9","description":"Gosh makeup pakke 3 - Tik Tok 3x uppers"},"1027459":{"itemno":"1027459","quantity":"9","description":"Royal Copenhagen bl\u00e5 mega sk\u00e5l 10,5cm"},"240320":{"itemno":"240320","quantity":"8","description":"Gosh skin care pakke 2B - Hydration line to share"},"4343502":{"itemno":"4343502","quantity":"7","description":"Holmegaard DWL lanterne klar glas 16cm"},"FFC427N":{"itemno":"FFC427N","quantity":"3","description":"Festina silke slips prestige navy"},"10001":{"itemno":"10001","quantity":"0","description":"Safly brandt\u00e6ppe coffee table book hvid"},"20670":{"itemno":"20670","quantity":"2","description":"Rosendahl birds foderstation p\u00e5 spyd H107 gr\u00f8n"},"10002":{"itemno":"10002","quantity":"1","description":"Safly brandt\u00e6ppe coffee table book gr\u00f8n"},"1017365":{"itemno":"1017365","quantity":"1","description":"RC bl\u00e5 megamussel frokosttallerken 22cm"},"FFC424H":{"itemno":"FFC424H","quantity":"1","description":"Festina silke slips classicals grey"},"240345":{"itemno":"240345","quantity":"1","description":"Selvtid pakke 4 DIY st\u00f8be kit oval bakke "},"240331":{"itemno":"240331","quantity":"1","description":"Gosh makeup pakke 4 - Trendy makeup"},"BTB-10122":{"itemno":"BTB-10122","quantity":"2","description":"GJD pyjamas soft grey M\/L"},"230157":{"itemno":"230157","quantity":"2","description":"Play backgammon akryl med to rafleb\u00e6gre "},"DO-CY-23-12-007":{"itemno":"DO-CY-23-12-007","quantity":"1","description":"Small Revolution Baby Donna vase terrazzo"},"BTB-10123":{"itemno":"BTB-10123","quantity":"3","description":"GJD pyjamas soft grey L\/XL"},"800302500":{"itemno":"800302500","quantity":"3","description":"Omhu \u00f8ko quiltet senget\u00e6ppe 200x250 brun gr\u00e5"},"BTB-10084":{"itemno":"BTB-10084","quantity":"4","description":"GJD badek\u00e5be i frotte walnut s\/m"},"230194":{"itemno":"230194","quantity":"1","description":"Murphy Copenhagen antibakteriel h\u00e5ndst\u00f8vsuger"},"230163":{"itemno":"230163","quantity":"4","description":"Caffe Lusso kaffemaskine sort 8-10 kopper "},"20843":{"itemno":"20843","quantity":"4","description":"Rosendahl GC Take tallerken \u00d819,5cm gr\u00e5 2 stk"},"20833":{"itemno":"20833","quantity":"22","description":"Rosendahl GC Take tallerken \u00d826cm gr\u00e5 2 stk"},"KO15-0101LS037-02":{"itemno":"KO15-0101LS037-02","quantity":"5","description":"Sensa Play mini h\u00f8jttaler med sound by JBL orange"},"230118":{"itemno":"230118","quantity":"5","description":"Tobias Jacobsen Chubby led lampe blank hvid "},"240322":{"itemno":"240322","quantity":"5","description":"Gosh skin care pakke 4A - Anti age basic"},"240105":{"itemno":"240105","quantity":"5","description":"CPH Lounge stol oppustelig"},"230141":{"itemno":"230141","quantity":"5","description":"Jesper Koch grills\u00e6t sort 5 stk"},"32317":{"itemno":"32317","quantity":"5","description":"Zone Firefly lanterne til lys sort 35cm"},"230137":{"itemno":"230137","quantity":"6","description":"Murphy pet feeder hvid "},"10004":{"itemno":"10004","quantity":"7","description":"By Lassen Kubus 4 sort 14x14cm "},"599999961":{"itemno":"599999961","quantity":"8","description":"Omhu h\u00e5ndkl\u00e6der  4+2+2 bl\u00e5 strib"},"210171":{"itemno":"210171","quantity":"10","description":"Tobias Jacobsen In-ear sort"},"230126":{"itemno":"230126","quantity":"10","description":"Jesper Koch salt & peber s\u00e6t"},"210154":{"itemno":"210154","quantity":"13","description":"Stantox v\u00e6rkt\u00f8jss\u00e6t 90 dele"},"KO15-0101LS038-02":{"itemno":"KO15-0101LS038-02","quantity":"8","description":"Sensa Play mini h\u00f8jttaler med sound by JBL sort"},"230174":{"itemno":"230174","quantity":"17","description":"Cavalluzzi 2 s\u00e6t, carry on + medium sort"},"240200":{"itemno":"240200","quantity":"18","description":"Urban Copenhagen in-ears sort"},"230131":{"itemno":"230131","quantity":"20","description":"Hair by Soho f\u00f8nt\u00f8rrer med airwrap"},"14754":{"itemno":"14754","quantity":"22","description":"Aida Raw salt og peber med bakke teak"},"10782":{"itemno":"10782","quantity":"0","description":"GJD duffel bag herringbone varm beige BCI"},"230187":{"itemno":"230187","quantity":"25","description":"Jesper Koch pasta gryde med si indsats og l\u00e5g"},"CC006292-001":{"itemno":"CC006292-001","quantity":"2","description":"Mauviel chefs pande kobber"},"30-HSSKCS5":{"itemno":"30-HSSKCS5","quantity":"38","description":"Santo knivs\u00e6t 5 stk"},"220137":{"itemno":"220137","quantity":"40","description":"Stantox h\u00f8jtryksrenser "},"19213":{"itemno":"19213","quantity":"40","description":"KitchenAid mini-foodprocessor sort 830 ml"},"43357":{"itemno":"43357","quantity":"49","description":"Caterpillar bor- og bits\u00e6t 201 dele"},"220160":{"itemno":"220160","quantity":"50","description":"Tobias Jacobsen kuffert og carryon champagne"},"CC006285-001":{"itemno":"CC006285-001","quantity":"55","description":"Mauviel pande 28cm"},"240174":{"itemno":"240174","quantity":"60","description":"Laguiole by HOC bestik black stone 24 dele"},"220119":{"itemno":"220119","quantity":"30","description":"Kitchenmaster tr\u00e5dl\u00f8s minihakker sort"},"240304":{"itemno":"240304","quantity":"23","description":"GJD h\u00e5ndkl\u00e6der 2V+2 light oak BCI"},"220152":{"itemno":"220152","quantity":"22","description":"Ambience du Nord h\u00e5ndkl\u00e6depakke bl\u00e5 2+2"},"230190":{"itemno":"230190","quantity":"6","description":"House of Chefs el fondue"},"240175":{"itemno":"240175","quantity":"20","description":"Laguiole by HOC bestik grey stone 24 dele"},"220163":{"itemno":"220163","quantity":"20","description":"Explorer termoboks"},"240153":{"itemno":"240153","quantity":"6","description":"Stockholm bord egetr\u00e6sfiner mellem"},"9160":{"itemno":"9160","quantity":"10","description":"Dyberg Larsen 2 ways led bordlampe "},"240143":{"itemno":"240143","quantity":"2","description":"Absolute Sport basket "},"20811794BTB":{"itemno":"20811794BTB","quantity":"24","description":"Arne Jacobsen Bellevue AJ8 bordlampe sort"},"18385":{"itemno":"18385","quantity":"42","description":"Cavalluzzi weekendtaske sort"},"D1061300110":{"itemno":"D1061300110","quantity":"1","description":"FDB D106 Philip Bro bakkebord eg natur \u00d838"},"240303":{"itemno":"240303","quantity":"1","description":"GJD h\u00e5ndkl\u00e6der 2+2 walnut BCI"},"30-HVC90":{"itemno":"30-HVC90","quantity":"2","description":"H\u00c2WS ledningsfri h\u00e5ndst\u00f8vsuger "},"230140":{"itemno":"230140","quantity":"2","description":"House of Chefs vaffeljern sort"},"230205":{"itemno":"230205","quantity":"18","description":"Tsuno knivs\u00e6t og strygest\u00e5l 8 dele"},"240172":{"itemno":"240172","quantity":"42","description":"Jesper Koch blackline 6 dele "},"5744168339":{"itemno":"5744168339","quantity":"4","description":"Louis Poulsen Panthella portable bordlampe sort"},"240137":{"itemno":"240137","quantity":"11","description":"Murphy Copenhagen strygebr\u00e6t med steamer"},"230101":{"itemno":"230101","quantity":"0","description":"Hyldest til farfar bordlampe blank bl\u00e5"},"240195":{"itemno":"240195","quantity":"28","description":"Tobias Jacobsen kuffert 3 s\u00e6t sort"},"240196":{"itemno":"240196","quantity":"2","description":"Tobias Jacobsen kuffert kabine sort"},"230204":{"itemno":"230204","quantity":"1","description":"Tsuno knivs\u00e6t 5 dele"},"240117":{"itemno":"240117","quantity":"55","description":"House of Chefs airfryer 3i1 11L"},"5744167178":{"itemno":"5744167178","quantity":"40","description":"Louis Poulsen Panthella bordlampe \u00d832 messing"},"240167":{"itemno":"240167","quantity":"58","description":"Laguiole by House of Chefs grydes\u00e6t 8 dele"},"240193":{"itemno":"240193","quantity":"38","description":"Ontime Aviator kuffert "},"240146":{"itemno":"240146","quantity":"30","description":"Caffe Lusso 2i1 kaffemaskine med m\u00e6lkeskummer"},"5923COW-TAN":{"itemno":"5923COW-TAN","quantity":"24","description":"Markberg Milo weekendstaske tan"},"18297":{"itemno":"18297","quantity":"20","description":"Cavalluzzi weekendtaske cognac"},"5923COW-BLA":{"itemno":"5923COW-BLA","quantity":"12","description":"Markberg Milo weekendstaske sort"},"5744166661":{"itemno":"5744166661","quantity":"6","description":"Louis Poulsen Panthella portable bordlampe hvid"},"220145":{"itemno":"220145","quantity":"100","description":"Murphy Pro Copenhagen steamer gr\u00e5"},"CC006283-001":{"itemno":"CC006283-001","quantity":"80","description":"Mauviel pande 20cm"},"KFTE89":{"itemno":"KFTE89","quantity":"39","description":"Kreafunk toCHARGE GO sand"},"CC006288-001":{"itemno":"CC006288-001","quantity":"32","description":"Mauviel ostebr\u00e6t \u00d835"},"CC006294-001":{"itemno":"CC006294-001","quantity":"32","description":"Mauviel osteknivs\u00e6t 3 stk"},"220136":{"itemno":"220136","quantity":"31","description":"Explorer rygs\u00e6k sort\/gr\u00e5 35L"},"210153":{"itemno":"210153","quantity":"20","description":"Stantox v\u00e6rkt\u00f8jss\u00e6t 46 dele"},"240144":{"itemno":"240144","quantity":"20","description":"Hair by Soho h\u00e5rtrimmer"},"240203":{"itemno":"240203","quantity":"20","description":"Urban Copenhagen in-ears sport sort"},"240201":{"itemno":"240201","quantity":"20","description":"Urban Copenhagen in-ears sport beige"},"240202":{"itemno":"240202","quantity":"20","description":"Urban Copenhagen in-ears sport gr\u00f8n"},"10811-MB":{"itemno":"10811-MB","quantity":"10","description":"GJD Case plaid m\u00f8rkebrun 130x180 BCI"},"14580":{"itemno":"14580","quantity":"10","description":"Aida Raw Unique 6 glas og karaffel "},"21107":{"itemno":"21107","quantity":"10","description":"Pillivuyt t\u00e6rtefade 25 og 27cm"},"30735":{"itemno":"30735","quantity":"12","description":"S\u00f6dahl juledug med hjerter 140x320"},"FAB326Y":{"itemno":"FAB326Y","quantity":"10","description":"Festina button b\u00e6lte brun 1250x33"},"32319":{"itemno":"32319","quantity":"10","description":"Zone Firefly lanterne til lys sort 25cm"},"1310-08":{"itemno":"1310-08","quantity":"3","description":"Stelton Made in Denmark kande smoke"},"1309-02":{"itemno":"1309-02","quantity":"3","description":"Stelton Made in Denmark bakke smoke"},"4343500":{"itemno":"4343500","quantity":"5","description":"Holmegaard DWL lanterne klar glas 29,5cm"},"NDTOBR-B":{"itemno":"NDTOBR-B","quantity":"5","description":"MW solbrille new depp bioacetat brown"},"1050":{"itemno":"1050","quantity":"5","description":"Kintobe cross body chrome grey"},"240321":{"itemno":"240321","quantity":"5","description":"Gosh skin care pakke 3 - Day routine anti age"},"240338":{"itemno":"240338","quantity":"5","description":"Gosh herre pakke 3 - Daily basic"},"30-LGWINESET":{"itemno":"30-LGWINESET","quantity":"5","description":"Laguiole vins\u00e6t"},"FAB326A":{"itemno":"FAB326A","quantity":"5","description":"Festina button b\u00e6lte sort 1250x33"},"689497":{"itemno":"689497","quantity":"5","description":"Juna Antonia morgenk\u00e5be b\u00e6k&b\u00f8lge bl\u00e5\/hvid one siz"},"13334":{"itemno":"13334","quantity":"2","description":"Aida Confetti ovalt fad apricot"},"14549":{"itemno":"14549","quantity":"2","description":"Bitz hurricane gr\u00f8n 17 cm"},"240344":{"itemno":"240344","quantity":"2","description":"Selvtid pakke 3 DIY st\u00f8be kit musling"},"B-24-1-1":{"itemno":"B-24-1-1","quantity":"2","description":"Stelton Made in Denmark bakke og kande gr\u00f8n"},"240109":{"itemno":"240109","quantity":"3","description":"Hi5 airdrum"},"DT0109-QZ":{"itemno":"DT0109-QZ","quantity":"5","description":"Dewalt bor og bitss\u00e6t 109 dele"},"246625":{"itemno":"246625","quantity":"6","description":"Eva Trio multi mosaic pande 24cm "},"39335":{"itemno":"39335","quantity":"6","description":"Kay Bojesen N\u00f8rgaard abe hvid\/sort"},"230142":{"itemno":"230142","quantity":"8","description":"House of Chefs r\u00f8remaskine sort 4L 675W"},"CC006284-001":{"itemno":"CC006284-001","quantity":"4","description":"Mauviel pande 24cm"},"CC006293-001":{"itemno":"CC006293-001","quantity":"30","description":"Mauviel pande kobber 24cm"},"29331":{"itemno":"29331","quantity":"8","description":"Peugeot Line proptr\u00e6kker med foliesk\u00e6rer"},"C12":{"itemno":"C12","quantity":"1","description":"Premium Stenduffuser Terracotta C12"},"1017403":{"itemno":"1017403","quantity":"0","description":"Royal Copenhagen hvid riflet frokosttallerken 22cm"},"240206":{"itemno":"240206","quantity":"9","description":"Urban Copenhagen over-ears sort"},"CC006291-001":{"itemno":"CC006291-001","quantity":"22","description":"Mauviel gryde 5.7L af st\u00e5l"},"3586663":{"itemno":"3586663","quantity":"17","description":"Georg Jensen Henning Koppel kande 1.2L"},"200480":{"itemno":"200480","quantity":"16","description":"Comwell gavekort (med tilk\u00f8b af morgenmad)"},"34495":{"itemno":"34495","quantity":"13","description":"Caterpillar rugged wireless h\u00f8jtaler"},"KO15-0120LS038-02":{"itemno":"KO15-0120LS038-02","quantity":"2","description":"Sensa Play h\u00f8jttaler med sound by JBL sort"},"TDWGB0041005":{"itemno":"TDWGB0041005","quantity":"10","description":"Timberland Trumbull armb\u00e5ndsur bl\u00e5 urskive"},"B-22-12":{"itemno":"B-22-12","quantity":"9","description":"Stelton EM bestik 24 dele"},"10019593":{"itemno":"10019593","quantity":"5","description":"Georg Jensen Kay Fisker kande 1,5l"},"14018274":{"itemno":"14018274","quantity":"5","description":"Fritz Hansen Calabash pendel P2 guld 3m ledning"},"210194":{"itemno":"210194","quantity":"5","description":"Absolute Sport Hastighed & kadence sensor"},"1068623":{"itemno":"1068623","quantity":"3","description":"Royal Copenhagen hvid riflet serveringss\u00e6t 4 dele"},"240205":{"itemno":"240205","quantity":"1","description":"Urban Copenhagen over-ears gr\u00f8n"},"GF0004":{"itemno":"GF0004","quantity":"1","description":"Me and my box pakke 5"},"KO15-0120LS041-02":{"itemno":"KO15-0120LS041-02","quantity":"1","description":"Sensa Play h\u00f8jttaler med sound by JBL hvid"},"200405":{"itemno":"200405","quantity":"10","description":"Comwell gavekort (formidlet af GaveFabrikken)"},"840025":{"itemno":"840025","quantity":"10","description":"Fritz Hansen x Aiayu pude anthracite"},"32042":{"itemno":"32042","quantity":"9","description":"Alfi juwel termokande kobber"},"10019823":{"itemno":"10019823","quantity":"8","description":"Georg Jensen Sky gulvvase H45,9"},"DCD710D2-QW":{"itemno":"DCD710D2-QW","quantity":"5","description":"Dewalt bore-skruemaskine med 2 batterier 12V "},"840026":{"itemno":"840026","quantity":"4","description":"Fritz Hansen x Aiayu pude oat"},"10019713":{"itemno":"10019713","quantity":"2","description":"Georg Jensen Cobra rund lysestage til 4 lys guld"},"3587583":{"itemno":"3587583","quantity":"2","description":"Georg Jensen Henning Koppel v\u00e6gur 30cm"},"230127":{"itemno":"230127","quantity":"0","description":"Stantox h\u00f8jtryksrenser Micheli5,5L 1400W "},"223-1":{"itemno":"223-1","quantity":"0","description":"Stelton Amphora elkedel sort 1,2"},"240306":{"itemno":"240306","quantity":"0","description":"GJD h\u00e5ndkl\u00e6der 2+2 light oak BCI"},"210161":{"itemno":"210161","quantity":"0","description":"Ambience du Nord senget\u00f8j egypt. bomuld hvid"},"1062516":{"itemno":"1062516","quantity":"0","description":"Fiskars Norr knivs\u00e6t 5 dele"},"10829-GB-240":{"itemno":"10829-GB-240","quantity":"0","description":"GJD Snowflakes dug 140x240 gentle beige"},"KFJD89":{"itemno":"KFJD89","quantity":"0","description":"Kreafunk Orion ANC h\u00f8retelefoner sand"},"18290":{"itemno":"18290","quantity":"0","description":"KitchenAid Classic toaster sort 2-skiver"},"94887280":{"itemno":"94887280","quantity":"0","description":"Eva Solo Legio nova 3 ovnfaste fade"},"139157A209":{"itemno":"139157A209","quantity":"10","description":"Verner Panton VP9 portable led blank grey beige"},"230128":{"itemno":"230128","quantity":"9","description":"Murphy Pro automatisk stryget\u00f8rre med luft"},"1052-1":{"itemno":"1052-1","quantity":"0","description":"Kintobe rolltop rygs\u00e6k chrome grey"},"GP181132134102-1121M":{"itemno":"GP181132134102-1121M","quantity":"0","description":"FinaMill gaves\u00e6t genopladeligt sort"},"39256":{"itemno":"39256","quantity":"0","description":"Kay Bojesen abe lille eg"},"39286":{"itemno":"39286","quantity":"0","description":"Kay Bojesen bj\u00f8rn reworked jubil\u00e6um "},"190109":{"itemno":"190109","quantity":"0","description":"Explorer Rygs\u00e6k, sort\/gr\u00e5 75+10L 30x40x85cm"},"210134":{"itemno":"210134","quantity":"0","description":"Stantox v\u00e6rkt\u00f8jss\u00e6t 171 dele"},"5795RPV":{"itemno":"5795RPV","quantity":"0","description":"Markberg Jordan weekendtaske recycled polyester"},"10020258":{"itemno":"10020258","quantity":"0","description":"Georg Jensen triple snack bowl forgyldt"},"139157A210":{"itemno":"139157A210","quantity":"0","description":"Verner Panton VP9 portable led light blue"},"OSOEBCS908":{"itemno":"OSOEBCS908","quantity":"0","description":"OSRAM battery charge 908 12V\/24V"},"1040":{"itemno":"1040","quantity":"0","description":"Dyberg Larsen DL20 solar spyd 2 stk."},"220157":{"itemno":"220157","quantity":"0","description":"Hyldest til farfar bordlampe blank sort"},"139161A216":{"itemno":"139161A216","quantity":"0","description":"Verner Panton VP10 pendel blank hvid"},"TDWGB2131802":{"itemno":"TDWGB2131802","quantity":"0","description":"Timberland Bernardston armb\u00e5ndsur lys urskive"},"3001":{"itemno":"3001","quantity":"0","description":"Noble Denmark Paris bord med puf"},"230136":{"itemno":"230136","quantity":"2","description":"Skagen \u00f8kologisk h\u00f8rdug 140 x 270cm"},"240115":{"itemno":"240115","quantity":"2","description":"Urban Copenhagen kuffert 2 s\u00e6t gul"},"90100113PEARL":{"itemno":"90100113PEARL","quantity":"0","description":"Design Letters perleblomst halsk\u00e6de "},"90603005GOLD":{"itemno":"90603005GOLD","quantity":"2","description":"Design Letters perleblomst ring"},"240199":{"itemno":"240199","quantity":"2","description":"Urban Copenhagen in-ears gr\u00f8n"},"1064":{"itemno":"1064","quantity":"0","description":"The Organic Company meditations madras m\u00f8rk gr\u00e5"},"10012":{"itemno":"10012","quantity":"0","description":"Safly brandslukker beige"},"10735":{"itemno":"10735","quantity":"0","description":"GJD snowflakes dug hvid 140x240"},"39261":{"itemno":"39261","quantity":"0","description":"Kay Bojesen abe m\u00f8rkbejset eg lille "},"230104":{"itemno":"230104","quantity":"0","description":"Hyldest til farfar bordlampe blank hvid"},"240339":{"itemno":"240339","quantity":"0","description":"Gosh herre pakke 4 - Daily luxury care"},"502903":{"itemno":"502903","quantity":"0","description":"Eva Solo pumpetermokande rustfrit st\u00e5l 1,8l"},"1067350":{"itemno":"1067350","quantity":"0","description":"Royal Copenhagen history mix termokrus 3 stk"},"10019764":{"itemno":"10019764","quantity":"0","description":"Georg Jensen HK fl\u00f8dekande 0.2l"},"XKFXHT02":{"itemno":"XKFXHT02","quantity":"0","description":"Kreafunk bWEAR  h\u00f8retelefoner sort"},"10020":{"itemno":"10020","quantity":"0","description":"Safly f\u00f8rstehj\u00e6lpskasse hvid"}}';
        $soItems = json_decode($soItemString,true);

        $bomDataString = '{"57":{"25450":{"parent":"sam5352","quantityper":1},"25455":{"parent":"sam5352","quantityper":1},"N24JGP141840B287A":{"parent":"nosam24j2044901","quantityper":1},"N24JGP141900B287A":{"parent":"nosam24j2044901","quantityper":1},"25603":{"parent":"sam5351","quantityper":1},"25602":{"parent":"sam5351","quantityper":1},"20853":{"parent":"sam3805","quantityper":2},"20813":{"parent":"sam3805","quantityper":2},"N24J1002-1":{"parent":"nosam24jnolosweeds-3","quantityper":2},"N24J33115":{"parent":"nosam24jnolosweeds-3","quantityper":1},"1017403":{"parent":"sam5626","quantityper":2},"13609":{"parent":"sam5406","quantityper":6},"13607":{"parent":"sam5407","quantityper":6},"13605":{"parent":"sam5408","quantityper":6},"13601":{"parent":"sam5409","quantityper":6},"13600":{"parent":"sam5410","quantityper":6}},"58":{"13334":{"parent":"sam5378","quantityper":1},"13490":{"parent":"sam5378","quantityper":1},"CC006294-001":{"parent":"sam4057","quantityper":1},"CC006288-001":{"parent":"sam4057","quantityper":1},"690147":{"parent":"sam5357","quantityper":1},"690148":{"parent":"sam5357","quantityper":1},"240316":{"parent":"sam5393","quantityper":1},"240304":{"parent":"sam5393","quantityper":1},"25603":{"parent":"sam5350","quantityper":1},"25602":{"parent":"sam5350","quantityper":1},"25601":{"parent":"sam5350","quantityper":1},"14549":{"parent":"sam2031","quantityper":2},"20813":{"parent":"sam3804","quantityper":2},"20833":{"parent":"sam3804","quantityper":2},"N24J33114":{"parent":"nosam24jsweeds-2","quantityper":1},"N24J33116":{"parent":"nosam24jsweeds-2","quantityper":1},"N24J33117":{"parent":"nosam24jsweeds-2","quantityper":1},"N24J33115":{"parent":"nosam24jsweeds-2","quantityper":1},"N24J1008-1":{"parent":"nosam24jmatc-nolo-1","quantityper":1},"N24J20138-1":{"parent":"nosam24jmatc-nolo-1","quantityper":1},"N24J45214":{"parent":"nosam24j2048001","quantityper":1},"N24J45215":{"parent":"nosam24j2048001","quantityper":1},"N24J45216":{"parent":"nosam24j2048001","quantityper":1},"N24J45217":{"parent":"nosam24j2048001","quantityper":1},"N23J116135":{"parent":"nosam23j1009","quantityper":2},"N23J116134":{"parent":"nosam23j1010","quantityper":2},"1309-01":{"parent":"sam5728","quantityper":1},"1310-07":{"parent":"sam5728","quantityper":1},"1309-02":{"parent":"sam5727","quantityper":1},"1310-08":{"parent":"sam5727","quantityper":1}},"59":{"N24J45214":{"parent":"nosam24j2048001","quantityper":1},"N24J45215":{"parent":"nosam24j2048001","quantityper":1},"N24J45216":{"parent":"nosam24j2048001","quantityper":1},"N24J45217":{"parent":"nosam24j2048001","quantityper":1},"693804":{"parent":"sam5328","quantityper":1},"693801":{"parent":"sam5328","quantityper":1},"14754":{"parent":"sam5368","quantityper":1},"14755":{"parent":"sam5368","quantityper":1},"13609":{"parent":"sam5420","quantityper":12},"13607":{"parent":"sam5419","quantityper":12},"13605":{"parent":"sam5418","quantityper":12},"13601":{"parent":"sam5417","quantityper":12},"13600":{"parent":"sam5416","quantityper":12},"4343502":{"parent":"sam3242","quantityper":1},"4343501":{"parent":"sam3242","quantityper":1},"25608":{"parent":"sam5384","quantityper":1},"25450":{"parent":"sam5384","quantityper":1},"25455":{"parent":"sam5388","quantityper":1},"1017365":{"parent":"sam5347","quantityper":2},"CC006637-001":{"parent":"sam5427","quantityper":2},"230203":{"parent":"sam4171","quantityper":1},"230202":{"parent":"sam4171","quantityper":1},"693075":{"parent":"sam3838","quantityper":2},"693076":{"parent":"sam3838","quantityper":2},"692633":{"parent":"sam3838","quantityper":2},"690149":{"parent":"sam5356","quantityper":1},"690148":{"parent":"sam5356","quantityper":1},"34448":{"parent":"sam5422","quantityper":1},"34449":{"parent":"sam5422","quantityper":1},"25601":{"parent":"sam5388","quantityper":1},"25603":{"parent":"sam5388","quantityper":1},"25602":{"parent":"sam5388","quantityper":1},"26325":{"parent":"sam5403","quantityper":2},"693802":{"parent":"sam5330","quantityper":4},"1062831":{"parent":"sam3824","quantityper":1},"1003477":{"parent":"sam3824","quantityper":1},"1051601":{"parent":"sam3824","quantityper":1},"1027043":{"parent":"sam3824","quantityper":1},"1027044":{"parent":"sam3824","quantityper":1},"20833":{"parent":"sam3806","quantityper":2},"20843":{"parent":"sam3806","quantityper":2},"20853":{"parent":"sam3806","quantityper":2},"240148":{"parent":"sam5442","quantityper":1},"200107":{"parent":"sam5442","quantityper":1},"N24J113397":{"parent":"nosam24j20172-01","quantityper":2},"N24J113398":{"parent":"nosam24j20172-03","quantityper":2},"N24J1001-1":{"parent":"nosam24jnolosweeds-4","quantityper":1},"N24J1002-1":{"parent":"nosam24jnolosweeds-4","quantityper":1},"N24J1005-1":{"parent":"nosam24jnolosweeds-4","quantityper":1},"N24J33117":{"parent":"nosam24jnolosweeds-4","quantityper":1},"N24J33119":{"parent":"nosam24jnolosweeds-4","quantityper":1},"N24J33115":{"parent":"nosam24jnolosweeds-4","quantityper":1},"240165":{"parent":"sam5441","quantityper":1},"240166":{"parent":"sam5441","quantityper":1}},"272":{"83440":{"parent":"sam5369","quantityper":1},"83441":{"parent":"sam5369","quantityper":1},"13609":{"parent":"sam5415","quantityper":4},"13607":{"parent":"sam5414","quantityper":4},"13605":{"parent":"sam5413","quantityper":4},"13601":{"parent":"sam5412","quantityper":4},"13600":{"parent":"sam5411","quantityper":4},"N24J1007-1":{"parent":"nosam24jnolosweeds-2","quantityper":1},"N24J33118":{"parent":"nosam24jsweeds-1","quantityper":1},"N24J1001-1":{"parent":"nosam24jnolosweeds-1","quantityper":1},"N24J33114":{"parent":"nosam24jnolosweeds-1","quantityper":1},"N24J33117":{"parent":"nosam24jsweeds-1","quantityper":1},"N24JTIXKH0011":{"parent":"nosam24j20200-1","quantityper":1},"N24JTIBN002":{"parent":"nosam24j20200-3","quantityper":1},"N24JTIXKH0012":{"parent":"nosam24j20200-2","quantityper":1},"N24JTIXKH0013":{"parent":"nosam24j20200-3","quantityper":1}},"574":{"690147":{"parent":"sam5355","quantityper":1},"690149":{"parent":"sam5355","quantityper":1},"26325":{"parent":"sam5404","quantityper":1},"26324":{"parent":"sam5404","quantityper":1},"1061058":{"parent":"sam2021","quantityper":2},"25608":{"parent":"sam5387","quantityper":1},"25455":{"parent":"sam5382","quantityper":1},"25450":{"parent":"sam5382","quantityper":1},"25603":{"parent":"sam5387","quantityper":1},"25601":{"parent":"sam5387","quantityper":1},"25602":{"parent":"sam5387","quantityper":1},"34447":{"parent":"sam5423","quantityper":1},"34448":{"parent":"sam5423","quantityper":1},"34449":{"parent":"sam5423","quantityper":1},"10833":{"parent":"sam5396","quantityper":2},"240304":{"parent":"sam5392","quantityper":1},"240305":{"parent":"sam5392","quantityper":1},"240301":{"parent":"sam5391","quantityper":1},"240302":{"parent":"sam5391","quantityper":1},"1017403":{"parent":"sam5344","quantityper":4},"240148":{"parent":"sam6014","quantityper":2},"240198":{"parent":"sam6017","quantityper":1},"240156":{"parent":"sam6017","quantityper":1},"KA-011":{"parent":"sam5402","quantityper":1},"KA-010":{"parent":"sam5402","quantityper":1},"30697":{"parent":"sam5448","quantityper":1},"21107":{"parent":"sam5448","quantityper":1},"240157":{"parent":"sam6016","quantityper":1},"240199":{"parent":"sam6016","quantityper":1},"30740":{"parent":"sam6041","quantityper":2},"30735":{"parent":"sam6041","quantityper":1},"10020705":{"parent":"sam6015","quantityper":2},"10782":{"parent":"sam5428","quantityper":1},"10783":{"parent":"sam5428","quantityper":1},"10020":{"parent":"sam5335","quantityper":1},"10001":{"parent":"sam5335","quantityper":1},"10021":{"parent":"sam5336","quantityper":1},"10002":{"parent":"sam5336","quantityper":1},"10022":{"parent":"sam5337","quantityper":1},"10003":{"parent":"sam5337","quantityper":1},"20833":{"parent":"sam3210","quantityper":2},"20843":{"parent":"sam3210","quantityper":2},"20853":{"parent":"sam3210","quantityper":2},"20813":{"parent":"sam3210","quantityper":2},"240316":{"parent":"sam5394","quantityper":1},"240306":{"parent":"sam5394","quantityper":1},"10014933":{"parent":"sam4079","quantityper":1},"10019522":{"parent":"sam4079","quantityper":1},"203324":{"parent":"sam4108","quantityper":1},"203328":{"parent":"sam4108","quantityper":1},"N23J116123":{"parent":"nosam23j1003","quantityper":2}},"2550":{"690147":{"parent":"sam5354","quantityper":1},"690148":{"parent":"sam5354","quantityper":1},"690149":{"parent":"sam5354","quantityper":1},"1071673":{"parent":"sam5349","quantityper":1},"1071674":{"parent":"sam5349","quantityper":1},"13252":{"parent":"sam5370","quantityper":2},"13250":{"parent":"sam5370","quantityper":2},"693075":{"parent":"sam3839","quantityper":3},"693076":{"parent":"sam3839","quantityper":3},"692633":{"parent":"sam3839","quantityper":3},"10020707":{"parent":"sam6020","quantityper":1},"10020708":{"parent":"sam6020","quantityper":1},"203320":{"parent":"sam4141","quantityper":1},"203324":{"parent":"sam4141","quantityper":1},"203328":{"parent":"sam4141","quantityper":1},"10782":{"parent":"sam5429","quantityper":1},"10783":{"parent":"sam5429","quantityper":1},"BTB-10090":{"parent":"sam5429","quantityper":1},"CC006284-001":{"parent":"sam3843","quantityper":1},"CC006285-001":{"parent":"sam3843","quantityper":1},"N24J66510":{"parent":"nosam24j2048003","quantityper":4},"1017404":{"parent":"sam6018","quantityper":4},"240316":{"parent":"sam5395","quantityper":1},"240305":{"parent":"sam5395","quantityper":1},"693802":{"parent":"sam5331","quantityper":6},"240166":{"parent":"sam5440","quantityper":1},"240165":{"parent":"sam5440","quantityper":1},"240164":{"parent":"sam5440","quantityper":1},"210193":{"parent":"sam3018","quantityper":1},"210194":{"parent":"sam3018","quantityper":1},"1017366":{"parent":"sam5443","quantityper":2},"34693":{"parent":"sam5439","quantityper":2},"25608":{"parent":"sam5383","quantityper":1},"25450":{"parent":"sam5383","quantityper":1},"25455":{"parent":"sam5383","quantityper":1},"25602":{"parent":"sam5383","quantityper":1},"25601":{"parent":"sam5383","quantityper":1},"240169":{"parent":"sam5431","quantityper":1},"240170":{"parent":"sam5431","quantityper":1},"240303":{"parent":"sam5389","quantityper":2},"240306":{"parent":"sam5390","quantityper":2},"N222017":{"parent":"nosam222001","quantityper":1},"N222018":{"parent":"nosam222001","quantityper":1},"N23J116107":{"parent":"nosam23j1002","quantityper":2},"N23J116105":{"parent":"nosam23j1001","quantityper":2}},"4740":{"240166":{"parent":"sam5440","quantityper":1},"240165":{"parent":"sam5440","quantityper":1},"240164":{"parent":"sam5440","quantityper":1},"10022":{"parent":"sam5334","quantityper":1},"10003":{"parent":"sam5334","quantityper":1},"10012":{"parent":"sam5334","quantityper":1},"10021":{"parent":"sam5333","quantityper":1},"10002":{"parent":"sam5333","quantityper":1},"10011":{"parent":"sam5333","quantityper":1},"10020":{"parent":"sam5332","quantityper":1},"10001":{"parent":"sam5332","quantityper":1},"10010":{"parent":"sam5332","quantityper":1},"1017366":{"parent":"sam5348","quantityper":4},"840025":{"parent":"sam5360","quantityper":1},"10832-200":{"parent":"sam5399","quantityper":1},"220307":{"parent":"sam3778","quantityper":1},"220335":{"parent":"sam3778","quantityper":1},"220336":{"parent":"sam3777","quantityper":1},"240153":{"parent":"sam5433","quantityper":1},"240154":{"parent":"sam5433","quantityper":1},"840026":{"parent":"sam5360","quantityper":1},"10833":{"parent":"sam5397","quantityper":1},"10832-220":{"parent":"sam5397","quantityper":1},"220308":{"parent":"sam3777","quantityper":1},"220334":{"parent":"sam3777","quantityper":1},"25455":{"parent":"sam5385","quantityper":1},"25603":{"parent":"sam5385","quantityper":1},"25602":{"parent":"sam5385","quantityper":1},"25601":{"parent":"sam5385","quantityper":1},"25450":{"parent":"sam5385","quantityper":1},"25608":{"parent":"sam5385","quantityper":1},"1071673":{"parent":"sam5386","quantityper":1},"1071674":{"parent":"sam5386","quantityper":1},"1017349":{"parent":"sam5386","quantityper":1},"1017403":{"parent":"sam5345","quantityper":3},"1017365":{"parent":"sam5345","quantityper":3},"240182":{"parent":"sam5432","quantityper":1},"240180":{"parent":"sam5432","quantityper":1},"CC006290-001":{"parent":"sam5437","quantityper":1},"CC006291-001":{"parent":"sam5437","quantityper":1},"CC006286-001":{"parent":"sam5437","quantityper":1},"DT0109-QZ":{"parent":"sam5438","quantityper":1},"DCD710D2-QW":{"parent":"sam5438","quantityper":1},"N24JTC396390A020AM":{"parent":"nosam24j2044902","quantityper":1},"N24JGO396630A020AL":{"parent":"nosam24j2044903","quantityper":1},"N24JGO396900A020A58":{"parent":"nosam24j2044904","quantityper":1},"N24JSC516063A422A36-":{"parent":"nosam24j2044902","quantityper":1},"N24JTC396390A020AL":{"parent":"nosam24j2044903","quantityper":1},"N24JSC516063A422A41-":{"parent":"nosam24j2044904","quantityper":1},"N24JTC396390A020AXL":{"parent":"nosam24j2044904","quantityper":1},"N24JGO396630A020AXL":{"parent":"nosam24j2044904","quantityper":1},"N24J117664":{"parent":"nosam24j20172-05","quantityper":2},"N23J117653":{"parent":"nosam24j20172-06","quantityper":2},"N24J117665":{"parent":"nosam24j20172-06","quantityper":2},"N23J116127":{"parent":"nosam24j20172-09","quantityper":1},"N23J116135":{"parent":"nosam24j20172-09","quantityper":2},"N23J116553":{"parent":"nosam24j20172-09","quantityper":1},"N23J116126":{"parent":"nosam24j20172-10","quantityper":1},"N23J116134":{"parent":"nosam24j20172-10","quantityper":2},"N23J116552":{"parent":"nosam24j20172-10","quantityper":1},"CC006292-001":{"parent":"sam4058","quantityper":1},"CC006293-001":{"parent":"sam4058","quantityper":1},"5923COW-TAN":{"parent":"sam5664","quantityper":1},"5924COW-TAN":{"parent":"sam5664","quantityper":1},"5923COW-BLA":{"parent":"sam5709","quantityper":1},"5924COW-BLA":{"parent":"sam5709","quantityper":1}}}';
        $bomData = json_decode($bomDataString,true);

        $notInShops = array();
        $empty = array();

        foreach($soItems as $soItem) {

            $itemno = trim(strtolower($soItem["itemno"]));
            $itemamount = $soItem["quantity"];

            if($itemno != "" && $itemamount > 0) {
                echo "Processing SO ITEM: ".$itemno." [".$itemamount."]<br>";
                if(isset($presentData[$itemno]))  {
                    echo "- FOUND IN ".json_encode($presentData[$itemno])."<br>";

                    if($itemamount > 0) {

                    $fordeling = $this->getShopFordeling($presentData[$itemno],$itemamount);

                    foreach($fordeling as $shopid => $quantity) {
                        echo "-- SHOP: ".$shopid." - QTY: ".$quantity."<br>";

                        $isBom = 0;
                        $bomNo = "";
                        $bomQuantity = 0;

                        if(isset($bomData[$shopid][$itemno])) {
                            $isBom = 1;
                            $bomNo = $bomData[$shopid][$itemno]["parent"];
                            $bomQuantity = $quantity/$bomData[$shopid][$itemno]["quantityper"];
                        }
                        echo "-- BOM DATA: ".$isBom." - ".$bomNo." - ".$bomQuantity."<br>";

                        $doneObj = new \NavisionReservationDone();
                        $doneObj->shop_id = $shopid;
                        $doneObj->itemno = $itemno;
                        $doneObj->sono = $batchno;
                        $doneObj->quantity = $quantity;
                        $doneObj->isbom = $isBom;
                        $doneObj->bomno = $bomNo;
                        $doneObj->bomquantity = $bomQuantity;
                        $doneObj->save();

                    }

                    }


                } else {
                    echo "- Not found in any shops";
                    $notInShops[] = $itemno.": ".$itemamount;
                }
            } else {
                echo "Error processing SO ITEM: ".$itemno." [".$itemamount."]<br>";
                $empty[] = $itemno.": ".$itemamount;
            }

        }

        echo "<br>Uden indhold:<br>";
        echo json_encode($empty)."<br>";

        echo "<br>Ikke fundet i butikker:<br>";
        echo json_encode($notInShops)."<br>";

        \System::connection()->commit();

    }

    private function getShopFordeling($key, $quantity) {
        if (!is_array($key) || empty($key)) {
            throw new \Exception("Input skal være et ikke-tomt array.");
        }

        if (count($key) === 1) {
            // Hvis der kun er én shop, giv den hele quantity
            $id = array_key_first($key);
            return [$id => $quantity];
        }
        $totalRatio = array_sum($key);
        if ($totalRatio == 0) {
            throw new \Exception("Summen af fordelingsnøglerne kan ikke være nul.");
        }

        $fordeling = [];
        $totalAllocated = 0;

        foreach ($key as $id => $ratio) {
            $allocated = floor(($ratio / $totalRatio) * $quantity);
            $fordeling[$id] = $allocated;
            $totalAllocated += $allocated;
        }

        // Juster for afrundingsfejl
        $remaining = $quantity - $totalAllocated;
        if ($remaining > 0) {
            arsort($key); // Sorter nøglerne så dem med størst ratio får tilføjet først
            foreach ($key as $id => $ratio) {
                if ($remaining <= 0) break;
                $fordeling[$id]++;
                $remaining--;
            }
        }

        return $fordeling;
    }





}




















