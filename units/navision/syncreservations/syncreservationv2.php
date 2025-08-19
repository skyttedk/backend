<?php

namespace GFUnit\navision\syncreservations;


use ActiveRecord\DateTime;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\OrderWS;

class SyncReservationV2
{

    CONST DEBUG = true;
    CONST SENDTONAV = true;

    /*
     * CLASS MEMBERS
     */

    private $shopid = 0;
    private $output = false;

    private $shop;
    private $reservationState;
    private $reservationLanguage;
    private $reservationCode;
    private $reservationForeignLanguage;
    private $reservationForeignCode;
    private $presentReservations;
    private $itemMap;

    private $xmlLangMap;
    private $logLangMap;
    private $orderWs = array();
    private $statusMap = array();

    private $presentReservationLangMap = array();
    private $chosenItems = array();

    /*
     * CONSTRUCTOR
     */

    public function __construct($shopid, $output = false)
    {
        $this->shopid = intval($shopid);
        $this->output = $output ;
    }

    /*
     * PUBLIC FUNCTIONS
     */

    public function checkForErrors() {

        return !$this->load();

    }

    public function runSync() {

        //$this->log("Starting sync of shop ".$this->shopid);

        // Load shop and reservations
        if(!$this->load()) {
            //$this->log("Abort run after load");
            return false;
        }

        // Process languages
        foreach($this->itemMap as $langID => $items) {
            foreach($items as $item) {
                $this->processItem($item,$langID);
            }
        }

        // Send reservation
        if(!is_array($this->xmlLangMap) || count($this->xmlLangMap) == 0) {
            $this->log("No changes registered, sync completed.");
            return true;
        }

        $langs = array_keys($this->xmlLangMap);
        $this->log("Sending for ".count($langs)." languages: ".implode(", ",$langs));
        foreach($langs as $languageId) {
            $this->sendReservation($languageId);
        }

        $this->log("Reservation sync finished for shop ".$this->shopid);
        return true;

    }

    private function processVarenrChecks()
    {

        $hasErrors = false;

        // Check for errors in case
        foreach($this->itemMap as $langID => $items) {

            $itemNoList = array();

            foreach($items as $item) {

                $preppedItemNo = trim(strtolower($item["itemno"]));

                if(isset($itemNoList[$preppedItemNo])) {

                    $item1 = $itemNoList[$preppedItemNo];
                    $item2 = $item;
                    $hasErrors=true;
                    $this->log("Fejl i varenr case: ".$item["itemno"]." (item1: ".$item1["itemno"]." / item2: ".$item2["itemno"].")");
                    //echo "Fejl i varenr case: ".$item["itemno"]."<br>";
                    //echo "<pre>".print_r(array("item1" => $item1, "item2" => $item2),true)."</pre>";
                    //mailgf("sc@interactive.dk","Shop multiple varene","<pre>".print_r(array("item1" => $item1, "item2" => $item2),true)."</pre>");

                } else {
                    $itemNoList[$preppedItemNo] = $item;
                }

            }
        }

        return !$hasErrors;
    }

    public function viewShop() {

        $this->output = false;

        // Load shop and reservations
        if(!$this->load()) {
            echo "<div style='padding: 20px; text-align: center; color: red;'>Fejl på shop:<br><br>".implode("<br>",$this->logItems)."</div>";
        }

        // Process languages
        foreach($this->itemMap as $langID => $items) {
            foreach($items as $item) {
                $this->processItem($item,$langID);
            }
        }


        // Output page
        ?><html>
            <style>
                body { font-family: verdana; font-size: 10px;}
                table { font-family: verdana; font-size: 10px;  border-collapse: collapse; }
                td,th { padding: 5px; padding-right: 15px; border-bottom: 1px solid #A0A0A0; }
                td:first-child, th:first-child { text-align: left;}
                th { background: #F0F0F0; font-weight: bold; text-align: left;}
            </style>

            <h2><?php echo "Reservationsstatus på ".$this->shop->name." [".$this->shop->id."]</h2>";

            if(isset($this->statusMap["GENERAL"])) {
                echo "Der er detekteret ".count($this->statusMap["GENERAL"])." generelle problemer i opsætningen.";
                echo implode("<br>",$this->statusMap["GENERAL"]);
            }
            
        foreach($this->langs as $langid)
        {

            ?><h3>Land: [<?php echo $langid; ?>] <?php echo $this->countryText($langid); ?></h3>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Varenr</th>
                        <th>Reservation id</th>
                        <th>Navn</th>
                        <th>TYPE</th>
                        <th>SAMPAK</th>
                        <th>Næste sync<br>Reserveret</th>
                        <th>Næste sync<br>Udført</th>
                        <th>Næste sync<br>Nav reservationer</th>
                        <th>Næste sync<br>Valgt</th>
                        <th>Næste sync<br>Antal</th>
                        <th>Næste sync<br>Land</th>
                        <th>Næste sync<br>Lokation</th>
                        <th>Sidste sync<br>Antal</th>
                        <th>Sidste sync<br>Land</th>
                        <th>Sidste sync<br>Lokation</th>
                        <th>Sync delta</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody><?php

                foreach($this->itemMap as  $itemLangID => $itemList) {

                    if($langid == $itemLangID) {
                        foreach($itemList as $itemData) {

                            if($itemData["quantity"] == -1) $itemData["quantity"] = 0;

                            $changes = false;
                            if(($itemData["quantity"]-$itemData["quantity_done"])-$itemData["synced_quantity"] != 0) {
                                $changes = true;
                            }

                            ?><tr style="<?php if($changes) echo "background: lightyellow;"; ?>">
                                <td><?php echo $itemData["itemno"]; ?></td>
                                <td><?php echo is_array($itemData["present_reservation_id"]) ? implode(", ",$itemData["present_reservation_id"]) : $itemData["present_reservation_id"]; ?></td>
                                <td><?php echo $itemData["name"]; ?></td>

                                <td><?php

                                    if($itemData["sampakparent"] && !$itemData["sampakchild"]) {
                                        $type = "SAMPAK-PARENT";
                                    } else if(!$itemData["sampakparent"] && $itemData["sampakchild"]) {
                                        $type = "SAMPAK-VARE";
                                    } else if(!$itemData["sampakparent"] && !$itemData["sampakchild"]) {
                                        $type = "VARE";
                                    } else {
                                        $type = "COMBI";
                                    }

                                    echo $type;

                                    ?></td>
                                <td><?php echo $itemData["sampakitem"]; ?></td>

                                <td style="border-left: 1px solid #777777;"><?php echo $itemData["quantity"]; ?></td>
                                <td style="border-left: 1px solid #777777;"><?php echo $itemData["quantity_done"]; ?></td>
                                <td style="border-left: 1px solid #777777;"><?php echo ($itemData["quantity"]-$itemData["quantity_done"]); ?></td>
                                <td style="border-left: 1px solid #777777;"><?php echo $itemData["chosen"]; ?></td>
                                <td style="border-left: 1px solid #777777;"><?php echo ($itemData["quantity"]-$itemData["quantity_done"])-$itemData["chosen"]; ?></td>
                                <td><?php echo $itemData["language"]; ?></td>
                                <td style="border-right: 1px solid #777777;"><?php echo $itemData["location"]; ?></td>

                                <td><?php echo $itemData["synced_quantity"]; ?></td>
                                <td><?php echo $itemData["synced_language"]; ?></td>
                                <td style="border-right: 1px solid #777777;"><?php echo $itemData["synced_location"] == "" ? "Ikke synkroniseret" : $itemData["synced_location"]; ?></td>

                                <td><?php echo ($itemData["quantity"]-$itemData["quantity_done"])-$itemData["synced_quantity"]; ?></td>

                                <td><?php

                                    if(isset($this->statusMap[$itemData["itemno"]])) {
                                        echo implode("<br>",$this->statusMap[$itemData["itemno"]]);
                                    }

                                    ?></td>
                                <td><?php echo $itemData["note"]; ?></td>

                            </tr><?php

                        }
                    }
                }

            ?></tbody></table><?php

        }

        ?></html><?php

    }

    private function countryText($lang) {
        if($lang == 1) return "DK";
        else if($lang == 4) return "NO";
        else if($lang == 5) return "SE";
        else return "Ingen reservation";
    }


    /*
     * SEND / HANDLE RESERVATIONS
     */

    private function sendReservation($languageId) {

        $callId = 0;
        $this->log("- Processing for language ".$languageId);

        // Generate xml
        $xmlDoc = $this->generateXmlDocument($this->xmlLangMap[$languageId]);

        $this->log("-- XML DOC");
        echo "<pre>".htmlentities($xmlDoc)."</pre>";

        if(count($this->xmlLangMap[$languageId]) == 0) {
            $this->log("-- No changes for language ".$languageId);
            return true;
        }

        if(!strstr($xmlDoc,"<reservation>")) {
            $this->log("-- No reservations in document ignore: ".$languageId);
            return true;
        }
        
        /*
         * // Send reservation
        if(!is_array($this->xmlLangMap) || count($this->xmlLangMap) == 0) {
            $this->log("No changes registered, sync completed.");
            return true;
        }
         */

/*
        return;
        exit();
*/
        if(!self::SENDTONAV) {
            $this->log("-- Send to nav disabled, stopping");
            return true;
        }


        //echo "STOP!"; exit();

        // Send to nav
        try {
            $this->log(" --- Create nav client");
            $client = $this->getOrderWS($languageId);
        } catch(\Exception $e) {
            $this->log(" ---- Coult not create nav client: ".$e->getMessage());
            return false;
        }

        // Send to navision

        try {

            $this->log(" --- Send to navision");

            $reservationResponse = $client->uploadReservationDoc($xmlDoc);
            if($reservationResponse) {
                $this->log(" ---- Nav request seems ok");
                if($client->getLastReservationResponse() != "OK") {
                    throw new \Exception("Reservation synced but navision responded with non ok answer: ".$client->getLastReservationResponse());
                } else {
                    $this->log("Order synced ok: ".$client->getLastReservationResponse());
                }

            } else {
                $this->log(" ---- Error in nav request: ".$client->getLastError());
                throw new \Exception("Could not upload reservation doc: ".$client->getLastError());
            }

            $callId = $client->getLastCallID();

        } catch (Exception $e) {
            NavDebugTools::mailProblem("NavClient error syncing reservation doc","Exception during upload of reservation document<br><pre>".$e->getMessage()."\r\n\r\nXML\r\n".$xml."</pre>");
            return false;
        }



        // Update all log items with call id and save
        foreach($this->logLangMap[$languageId] as $log) {
            $log->navision_call_log_id = $callId;
            $log->save();
        }

        // Update sync time on present_reservation
        $sql = "UPDATE present_reservation SET sync_quantity = IF(quantity=-1,0,quantity-quantity_done), sync_note = '', sync_time = NOW() WHERE shop_id = ".$this->shop->id;
        \ActiveRecord\ConnectionManager::get_connection("default")->query($sql);
        $this->log(" --- Updated sync_time on reservations");

        // Commit
        \system::connection()->commit();
        \System::connection()->transaction();

        return true;

    }

    /*
     * PROCESS ITEMS
     */

    private function processItem($itemData,$langID) {

        if($itemData["quantity"] == -1) $itemData["quantity"] = 0;

        // Get state
        $isSynced = $itemData["synced_language"] > 0 && $itemData["synced_language"] != "" && $itemData["synced_quantity"] != 0;
        $shouldBeSynced = $this->reservationState == 1 && $itemData["language"] > 0 && $itemData["location"] != "" && $itemData["quantity"] != 0;

        $this->log("Processing ".$itemData["itemno"]);

        // Was synced, should not anymore, remove
        if($isSynced && !$shouldBeSynced) {

            $this->log(" - Remove from sync");
            $this->addStatus($itemData["itemno"], "Fjern reservation");
            $this->addReservationChange($itemData["itemno"], $itemData["synced_language"], $itemData["synced_location"], -1*$itemData["synced_quantity"], 0,"Reservation on item stopped",$itemData["present_reservation_id"],$itemData["note"]);

        }

        // Was synced, should still be, check for changed
        else if($isSynced && $shouldBeSynced) {

            $hasMetaChanges = $itemData["language"] != $itemData["synced_language"] || $itemData["location"] != $itemData["synced_location"];
            if($hasMetaChanges) {

                $this->addStatus($itemData["itemno"], "Metadata ændret, fjern reservation og opret ny");
                $this->log(" - Remove old sync and create new (metadata changed)");
                $this->addReservationChange($itemData["itemno"], $itemData["synced_language"], $itemData["synced_location"], -1*$itemData["synced_quantity"], 0,"Metadata change, remove old",$itemData["present_reservation_id"],$itemData["note"]);
                $this->addReservationChange($itemData["itemno"], $itemData["language"], $itemData["location"], ($itemData["quantity"]-$itemData["quantity_done"]), ($itemData["quantity"]-$itemData["quantity_done"]),"Metadata change, new reservation",$itemData["present_reservation_id"],$itemData["note"]);

            } else if($itemData["synced_quantity"] !=($itemData["quantity"]-$itemData["quantity_done"])) {

                $this->addStatus($itemData["itemno"], "Opdater antal");
                $this->log(" - Update sync amount");
                $this->addReservationChange($itemData["itemno"], $itemData["language"], $itemData["location"], ($itemData["quantity"]-$itemData["quantity_done"])-$itemData["synced_quantity"], ($itemData["quantity"]-$itemData["quantity_done"]),"Quantity update",$itemData["present_reservation_id"],$itemData["note"]);

            } else {

                $this->addStatus($itemData["itemno"], "SKIP");
                $this->log(" - Sync but no changes!");

            }

        }

        // Was not synced, should be, create new sync
        else if(!$isSynced && $shouldBeSynced) {

            $this->addStatus($itemData["itemno"], "Ny reservation");
            $this->log(" - Create reservation");
            $this->addReservationChange($itemData["itemno"], $itemData["language"], $itemData["location"], ($itemData["quantity"]-$itemData["quantity_done"]), ($itemData["quantity"]-$itemData["quantity_done"]),"New reservation",$itemData["present_reservation_id"],$itemData["note"]);

        }

        // Was not synced, should not be, do nothing
        else {
            // Easy peasy! - nothing to do here
            $this->addStatus($itemData["itemno"], "No action");
            $this->log(" - No action not synced and should not be");
        }

        //echo "<pre>".print_r($itemData,true)."</pre>";

    }

    private function addReservationChange($itemno,$languageId,$location,$delta,$quantity,$actionNote="",$presentReservationIdList=null,$processNote="")
    {

        // Add to xml
        $reservationXml = $this->generateReservationItemXML($itemno, $location, $delta,$this->shop->name.": ".$actionNote.($processNote != "" ? " - ".$processNote : ""),$languageId);

        if($reservationXml == "") {
            return;
        }

        // Add xml to lang map
        if(!isset($this->xmlLangMap[$languageId])) {
            $this->xmlLangMap[$languageId] = array();
        }
        $this->xmlLangMap[$languageId][] = $reservationXml;

        // Create log object
        $resLog = new \NavisionReservationLog();
        $resLog->language_id = $languageId;
        $resLog->itemno = $itemno;
        $resLog->shop_id = $this->shopid;
        $resLog->location = $location;
        $resLog->created = date('d-m-Y H:i:s');
        $resLog->navision_call_log_id = 0;
        $resLog->delta = $delta;
        $resLog->balance = $quantity;
        $resLog->notes = trim($processNote." ".$actionNote);
        $resLog->present_reservations_ids = is_array($presentReservationIdList) ? implode(",",$presentReservationIdList) : "";

        // Add to lang map
        if(!isset($this->logLangMap[$languageId])) {
            $this->logLangMap[$languageId] = array();
        }
        $this->logLangMap[$languageId][] = $resLog;

        // Add present reservations id's to language map
        if(!isset($this->presentReservationLangMap[$languageId])) {
            $this->presentReservationLangMap[$languageId] = array();
        }
        if(is_array($presentReservationIdList) && count($presentReservationIdList) > 0) {
            foreach($presentReservationIdList as $id) {
                $this->presentReservationLangMap[$languageId][] = $id;
            }
        }

    }

    /*
     * DATA GETTERS
     */

    private function getReservationsByLanguage($languageId) {
        if(!isset($this->presentReservationLangMap[$languageId])) return array();
        $idlist =$this->presentReservationLangMap[$languageId];
        $returnList = array();
        foreach($this->presentReservations as $pr) {
            if(in_array($pr->id, $idlist)) {
                $returnList[] = $pr;
            }
        }
        return $returnList;
    }

    /*
     * DATA LOADER
     */

    private function addStatus($itemNo,$status)
    {
        if(!isset($this->statusMap[$itemNo])) {
            $this->statusMap[$itemNo] = array();
        }
        $this->statusMap[$itemNo][] = $status;
    }

    private $langs = array();
    private $locations = array();

    /**
     * Loads data for shop, returns false on problems
     * @return bool
     * @throws \ActiveRecord\RecordNotFound
     */
    private function load() {

        $hasError = false;

        // Load shop
        $this->shop = \Shop::find($this->shopid);
        if($this->shop == null || $this->shop->id == 0 || $this->shop->id != $this->shopid) {
            $this->log("Kunne ikke finde shop");
            return false;
        }

        // Load languages
        if($this->shop->reservation_language == 0) {
            $this->log("Shop har ikke angivet et primær sprog");
            return false;
        }
        if($this->shop->reservation_code == "") {
            $this->log("Primær sprog ".$this->shop->reservation_language." har ikke angivet en lokation");
            return false;
        }

        $this->langs = array();
        $this->reservationState = $this->shop->reservation_state;

        // Check for secondary language
        if($this->shop->reservation_foreign_language > 0) {

            if($this->shop->reservation_foreign_code == "") {
                $this->log("sekundære sprog ".$this->shop->reservation_foreign_language." har ikke angivet en lokation");
                return false;
            }

            $this->langs[] = $this->shop->reservation_foreign_language;
            $this->locations[$this->shop->reservation_foreign_language] = $this->shop->reservation_foreign_code;

        }

        // Add primary location
        $this->langs[] = $this->shop->reservation_language;
        $this->locations[$this->shop->reservation_language] = $this->shop->reservation_code;

        // Load privatedelivery choices
        $this->chosenItems = $this->loadChosenItems(0);

        // Load present_reservations
        $this->presentReservations = \PresentReservation::find('all',array("conditions" => array("skip_navision" => 0,"shop_id" => intval($this->shopid))));

        // Make map of all items in shop
        $itemList = array();
        $itemMap = array();

        foreach($this->presentReservations as $pr) {

            // Load present from db
            $present = \Present::find($pr->present_id);

            // Find present model
            $presentModels = \PresentModel::find_by_sql("SELECT * FROM present_model WHERE present_id = " . intval($pr->present_id) . " && model_id = " . intval($pr->model_id) . " && language_id = 1");

            if($present->external > 0) {
                //$this->log("Present ".$present->id.": ".$present->name." is external, skipping");
            }
            else if($present->shop_id != $this->shop->id) {
                //$this->log("Present ".$present->id.": ".$present->name." is not on shop, skipping");
            } else if (count($presentModels) == 0) {
                    $this->log("Could not find presentmodel for present reservation " . $pr->id . " (model_id: " . $pr->model_id . "), skipping");
            }
            else {

                    $presentModel = $presentModels[0];
                    $itemNo = $presentModel->model_present_no;

                    if(trimgf($itemNo) == "") {
                        $this->addStatus("GENERAL", "Reservation mangler varenr: ".$pr->id." (model_id: ".$pr->model_id.")");
                        $present = \Present::find($presentModel->present_id);
                        $this->log("<br><b>Model mangler varenr: ".$present->nav_name." - ".$presentModel->model_name."</b> (model id: ".$presentModel->model_id." / present id: ".$presentModel->present_id.")");
                        if(!(($pr->quantity == 0 || $pr->quantity == -1) && $pr->sync_quantity == 0)) {
                            $hasError = true;
                        }
                    }

                    // Find language add to itemlist
                    $foundNavLang = 0;
                    foreach($this->langs as $language_id) {
                        $varenrList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = ".$language_id." AND `no` LIKE '".$itemNo."' AND `deleted` IS NULL");
                        if(count($varenrList) > 0) {
                            $foundNavLang = $language_id;
                            break;
                        }
                    }
                    $itemList[$itemNo] = $foundNavLang;

                    // Check if no nav lang found
                    if($foundNavLang == 0 && (intval($pr->sync_quantity) != 0 || intval($pr->quantity) > 0)) {

                        $this->addStatus($itemNo, "Could not find navision item with no ". $itemNo." in language ".implode(", ",$this->langs)." synced ".intval($pr->sync_quantity)." should sync ".($pr->quantity-$pr->quantity_done));

                        $p = \Present::find($pr->present_id);
                        $pm = \PresentModel::find_by_sql("SELECT * FROM present_model where model_id = ".intval($pr->model_id)." && present_id = ".intval($pr->present_id)." && language_id = 1");
                        $this->log("<b>Kan ikke finde varenr i navision: [".$itemNo."], er reserveret med : ".$pr->quantity." (forbrug ".$pr->quantity_done.")</b> ".$p->nav_name." - ".$pm[0]->model_name." (model id: ".$pm[0]->model_id." / present id: ".$pm[0]->present_id.")");
                        $hasError = true;

                    }

                    // Found nav lang
                    else if($foundNavLang > 0)
                    {

                        $isBomParent = false;

                        // Find bom items
                        $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = ".$foundNavLang." && parent_item_no LIKE '".$itemNo."' && deleted is null");

                        // If vare has bom items
                        if(count($navbomItemList) > 0) {

                            $isBomParent = true;
                            foreach($navbomItemList as $bomChild) {

                                $childItemNo = $bomChild->no;

                                // Get varenrlist
                                $varenrList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = ".$foundNavLang." AND `no` LIKE '".$itemNo."' AND `deleted` IS NULL");
                                if(count($varenrList) == 0) {
                                    $this->addStatus($itemNo, "Could not find navision bom child with no ". $childItemNo." in language ".$foundNavLang."");
                                    $this->log("--- Could not find navision bom child with no ". $childItemNo." in language ".$foundNavLang." for bom parent ".$itemNo);
                                    $hasError = true;
                                }

                                // Add to item map
                                if(!isset($itemMap[$foundNavLang][$childItemNo])) {
                                    $itemMap[$foundNavLang][$childItemNo] = array("itemno" => $childItemNo, "name" => $varenrList[0]->description, "language" => $foundNavLang,"inshop" => $pr->model_id,"inres" => false, "location" => $this->locations[$foundNavLang], "present_reservation_id" => array($pr->id), "quantity" => ($pr->quantity == -1 ? 0 : $pr->quantity) * $bomChild->quantity_per, "quantity_done" => ($pr->quantity_done == -1 ? 0 : $pr->quantity_done) * $bomChild->quantity_per, "sampakparent" => false, "sampakchild" => true, "sampakitem" => $itemNo, "synced_language" => 0, "synced_location" => "", "synced_quantity" => 0,"note" => trimgf($pr->sync_note));
                                } else {
                                    if($itemMap[$foundNavLang][$childItemNo]["language"] != $foundNavLang) {
                                        $this->addStatus($childItemNo, "Item no is already processed in present_reservations: ".$childItemNo." (child of ".$itemNo." has language ".$itemMap[$foundNavLang][$itemNo]["language"]." but also ".$foundNavLang.")");
                                        $this->log("--- Item no is already processed in present_reservations: ".$childItemNo." (child of ".$itemNo." has language ".$itemMap[$foundNavLang][$itemNo]["language"]." but also ".$foundNavLang.")");
                                    } else if($itemMap[$foundNavLang][$childItemNo]["location"] != $this->locations[$foundNavLang]) {
                                        $this->addStatus($childItemNo, "Item no is already processed in present_reservations: ".$childItemNo." (child of ".$itemNo." has location ".$itemMap[$foundNavLang][$itemNo]["location"]." but also ".$this->locations[$foundNavLang].")");
                                        $this->log("--- Item no is already processed in present_reservations: ".$childItemNo." (child of ".$itemNo." has location ".$itemMap[$foundNavLang][$itemNo]["location"]." but also ".$this->locations[$foundNavLang].")");
                                    } else {
                                        $itemMap[$foundNavLang][$childItemNo]["present_reservation_id"][] = $pr->id;
                                        $itemMap[$foundNavLang][$childItemNo]["quantity"] += ($pr->quantity == -1 ? 0 : $pr->quantity) * $bomChild->quantity_per;
                                        $itemMap[$foundNavLang][$childItemNo]["quantity_done"] += ($pr->quantity_done == -1 ? 0 : $pr->quantity_done) * $bomChild->quantity_per;
                                        $itemMap[$foundNavLang][$childItemNo]["sampakparent"] = $isBomParent;
                                        $itemMap[$foundNavLang][$childItemNo]["sampakchild"] = true;
                                        if($itemMap[$foundNavLang][$childItemNo]["note"] == "" && trimgf($pr->sync_note) != "") $itemMap[$foundNavLang][$childItemNo]["note"] = trimgf($pr->sync_note);
                                    }
                                }

                            }

                        }

                        // Not bom, and not seen before
                        else if(!isset($itemMap[$foundNavLang][$itemNo])) {
                            if(!isset($varenrList[0])) $this->addStatus($itemNo, "Ukendt varenr");
                            $itemMap[$foundNavLang][$itemNo] = array("itemno" => $itemNo, "name" => isset($varenrList[0]) ? $varenrList[0]->description : $presentModel->model_name, "language" => $foundNavLang,"inshop" => $pr->model_id,"inres" => false, "location" => $this->locations[$foundNavLang], "present_reservation_id" => array($pr->id), "quantity" => ($pr->quantity == -1 ? 0 : $pr->quantity), "quantity_done" => ($pr->quantity_done == -1 ? 0 : $pr->quantity_done), "sampakparent" => $isBomParent, "sampakchild" => false, "sampakitem" => "", "synced_language" => 0, "synced_location" => "", "synced_quantity" => 0,"note" => trimgf($pr->sync_note));
                        }

                        // Not bom and seen before
                        else {
                            //$this->log("-- Update item ".$itemNo.", seen before");
                            if($itemMap[$foundNavLang][$itemNo]["language"] != $foundNavLang) {
                                $this->addStatus($itemNo, "Item no is already processed in present_reservations: ".$itemNo." (has language ".$itemMap[$foundNavLang][$itemNo]["language"]." but also ".$foundNavLang.")");
                                $this->log("-- Item no is already processed in present_reservations: ".$itemNo." (has language ".$itemMap[$foundNavLang][$itemNo]["language"]." but also ".$foundNavLang.")");
                            } else if($itemMap[$foundNavLang][$itemNo]["location"] != $this->locations[$foundNavLang]) {
                                $this->addStatus($itemNo, "Item no is already processed in present_reservations: ".$itemNo." (has location ".$itemMap[$foundNavLang][$itemNo]["location"]." but also ".$this->locations[$foundNavLang].")");
                                $this->log("-- Item no is already processed in present_reservations: ".$itemNo." (has location ".$itemMap[$foundNavLang][$itemNo]["location"]." but also ".$this->locations[$foundNavLang].")");
                            } else {
                                $itemMap[$foundNavLang][$itemNo]["present_reservation_id"][] = $pr->id;
                                $itemMap[$foundNavLang][$itemNo]["quantity"] += ($pr->quantity == -1 ? 0 : $pr->quantity);
                                $itemMap[$foundNavLang][$itemNo]["quantity_done"] += ($pr->quantity_done == -1 ? 0 : $pr->quantity_done);
                                $itemMap[$foundNavLang][$itemNo]["sampakparent"] = $isBomParent;
                                $itemMap[$foundNavLang][$itemNo]["sampakchild"] = false;
                                if($itemMap[$foundNavLang][$itemNo]["note"] == "" && trimgf($pr->sync_note) != "") $itemMap[$foundNavLang][$itemNo]["note"] = trimgf($pr->sync_note);
                            }
                        }

                    }

                }


        }

        // Load from navision_reservation_log
        $reservationLogItems = \NavisionReservationLog::find_by_sql("SELECT nrl1.* FROM navision_reservation_log nrl1 JOIN (SELECT itemno, MAX(created) AS latest_created FROM navision_reservation_log WHERE shop_id = ".intval($this->shopid)." GROUP BY itemno) nrl2 ON nrl1.itemno = nrl2.itemno AND nrl1.created = nrl2.latest_created WHERE nrl1.shop_id = ".intval($this->shopid));

        foreach($reservationLogItems as $rli) {

            // Find item no
            $itemNo = $rli->itemno;

            // Get varenrlist
            $varenrList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = ".$rli->language_id." AND `no` LIKE '".$itemNo."' AND `deleted` IS NULL");
            if(count($varenrList) == 0) {
                $this->addStatus($itemNo, "Could not find navision item with no ". $itemNo." in language ".$rli->language_id."");
                $this->log("--- Could not find navision item with no ". $itemNo." in language ".$rli->language_id);
                $hasError = true;
            }

            // Load present from db
            $present = \Present::find($pr->present_id);
            if($present->external > 0) {

                $this->log("Present reservation log ".$present->id.": ".$present->name." is external -  check manually!");

            } else {

                // If not in item list
                if(!isset($itemMap[$rli->language_id][$itemNo])) {
                    $itemList[$itemNo] = $rli->language_id;
                    $itemMap[$rli->language_id][$itemNo] = array("itemno" => $itemNo,"name" => "Tidligere reservation på ".$itemNo,"language" => $rli->language_id,"inshop" => false,"inres" => true,"location" => "","present_reservation_id" => trimgf($rli->present_reservations_ids) == "" ? array() : array($rli->present_reservations_ids),"quantity" => 0,"quantity_done" => 0,"sampakparent" => false,"sampakchild" => false,"sampakitem" => "","synced_language" => $rli->language_id,"synced_location" => $rli->location,"synced_quantity" => $rli->balance,"note" => "Not found in present_reservations.");
                } else {
                    $itemMap[$rli->language_id][$itemNo]["synced_language"] = $rli->language_id;
                    $itemMap[$rli->language_id][$itemNo]["synced_location"] = $rli->location;
                    $itemMap[$rli->language_id][$itemNo]["synced_quantity"] = $rli->balance;
                    $itemMap[$rli->language_id][$itemNo]["inres"] = true;
                }
                if(trimgf($rli->present_reservations_ids) != "") {
                    if(!isset($itemMap[$rli->language_id][$itemNo]["present_reservation_id"])) {
                        $itemMap[$rli->language_id][$itemNo]["present_reservation_id"] = array();
                    }
                    if(is_array($itemMap[$rli->language_id][$itemNo]["present_reservation_id"])) {
                        $itemMap[$rli->language_id][$itemNo]["present_reservation_id"][] = $rli->present_reservations_ids;
                    }
                }

            }
        }

        foreach($itemMap as $langid => $items) {
            foreach($items as $itemMapIndex => $item) {
                $itemMap[$langid][$itemMapIndex]["chosen"] = 0;
                if(isset($this->chosenItems[$itemMapIndex])) {
                    $itemMap[$langid][$itemMapIndex]["chosen"] = $this->chosenItems[$itemMapIndex];
                }
            }
        }

        //$this->log("Data loaded ok, loaded ".count($itemList)." items to process!");
        $this->itemMap = $itemMap;

        if(!$this->processVarenrChecks(false)) {
            $hasError = true;
        }

        return !$hasError;
    }

    private function loadChosenItems($date) {

        $items = array();

        $currentYear = $this->loadChosenItemsCurrent();
        $lastYear = array();

        $newList = $currentYear;
        foreach($lastYear as $itemNo => $itemCount) {
            if(isset($newList[$itemNo])) $newList[$itemNo] += $itemCount;
            else $newList[$itemNo] = $itemCount;
        }

        $returnList = array();

        // Divide into single items
        foreach ($newList as $itemNo => $count) {

            $varenrList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` in (".implode(",",$this->langs).") AND `no` LIKE '".$itemNo."' AND `deleted` IS NULL");
            $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id in (".implode(",",$this->langs).") && parent_item_no = '".$itemNo."' && deleted is null");

            if(count($varenrList) == 0) {
                //echo "COULD NOT FIND ITEM: ".$itemNo."<br>";
            }
            else if(count($navbomItemList) > 0) {
                foreach ($navbomItemList as $childItem) {
                    //echo "BOM ITEM ".$childItem->no.":".($childItem->quantity_per*$count)."<br>";
                    if(isset($returnList[$childItem->no])) $returnList[$childItem->no] += $childItem->quantity_per*$count;
                    else $returnList[$childItem->no] = $childItem->quantity_per*$count;
                }
            } else {
                if(isset($returnList[$itemNo])) $returnList[$itemNo] += $count;
                else $returnList[$itemNo] = $count;
                //echo "ITEM SINGLE ".$itemNo.": ".($count)."<br>";
            }

        }

        return $returnList;

    }

    private function loadChosenItemsYear($date,$year) {


        if($date instanceof \DateTime || $date instanceof DateTime) {
            $date = $date->getTimestamp();
        } else if(intvalgf($date) <= 0) {
            $date = 0;
        }

        //SELECT present_model.model_present_no as item_no, count( `order`.id) as order_count FROM gavefabrikken2022.`shop_user`, gavefabrikken2022.`order`, gavefabrikken2022.`present_model` WHERE shop_user.`shop_id` = 2960 && `order`.shopuser_id = shop_user.id && `order`.`present_model_id` = present_model.model_id && present_model.language_id = 1 && shop_user.delivery_state NOT IN (0,100) group by `order`.`company_name`;;
        $chosenItems = \ShopUser::find_by_sql("SELECT present_model.model_present_no as item_no, count( `order`.id) as order_count FROM gavefabrikken".intval($year).".`shop_user`, gavefabrikken".intval($year).".`order`, gavefabrikken".intval($year).".`present_model` WHERE order_timestamp >= '".date("Y-m-d H:i:s",$date)."' && shop_user.`shop_id` = ".$this->shop->id." && `order`.shopuser_id = shop_user.id && `order`.`present_model_id` = present_model.model_id && present_model.language_id = 1 && shop_user.delivery_state NOT IN (0,100) group by `order`.`company_name`");
        $retArray = array();

        foreach($chosenItems as $item) {
            $retArray[$item->item_no] = $item->order_count;
        }

        return $retArray;
    }

    private function loadChosenItemsCurrent() {


        //SELECT present_model.model_present_no as item_no, count( `order`.id) as order_count FROM gavefabrikken2022.`shop_user`, gavefabrikken2022.`order`, gavefabrikken2022.`present_model` WHERE shop_user.`shop_id` = 2960 && `order`.shopuser_id = shop_user.id && `order`.`present_model_id` = present_model.model_id && present_model.language_id = 1 && shop_user.delivery_state NOT IN (0,100) group by `order`.`company_name`;;
        $chosenItems = \ShopUser::find_by_sql("SELECT present_model.model_present_no as item_no, count( `order`.id) as order_count FROM `shop_user`, `order`, `present_model` WHERE `order`.shop_id = ".intval($this->shopid)." && `order`.shopuser_id = shop_user.id && `order`.`present_model_id` = present_model.model_id && present_model.language_id = 1 && shop_user.delivery_state NOT IN (0,100) group by item_no");
        $retArray = array();

        foreach($chosenItems as $item) {
            $retArray[$item->item_no] = $item->order_count;
        }

        return $retArray;
    }


    /*
     * HELPER FUNCTIONS
     */

    /**
     * Generate an reservation xml document from a list of strings containing the reservations
     * @param $reservationXmlList
     * @return string
     */
    public function generateXmlDocument($reservationXmlList) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<reservations>
    ';
        $xml .= implode("",$reservationXmlList);
        $xml .= '
</reservations>';
        return $xml;

    }

    /**
     * Generate a reservation node in a reservation document
     * @param $itemNo
     * @param $locationCode
     * @param $delta
     * @return string
     */
    public function generateReservationItemXML($itemNo,$locationCode,$delta,$note = "",$languageId=1) {

        if(intval($delta) == 0) return "";
        if(strlen($note) > 80) $note = substr($note,0,78)."..";

        try {
            $client = $this->getOrderWS($languageId);
            $client->getReservationBalance($itemNo,$locationCode);
            $reservedNav = $client->getLastReservationBalance();

            $this->log("Nav reservation balance is ".$reservedNav.", new balance ".($reservedNav + $delta).", delta ".$delta." for ".$itemNo." in ".$locationCode." (language ".$languageId.")");

            if($reservedNav + $delta < 0) {
                $delta = -1*$reservedNav;
                $this->log("RESERVATION IS NEGATIVE, ADJUST DELTA SO NEW BALANCE IS 0: ".$delta);

            }

        } catch (\Exception $e) {
            $this->log("Could not get reservation balance for ".$itemNo." in ".$locationCode.": ".$e->getMessage());
            exit();
        }

        if(intval($delta) == 0) return " ";

        $xml = '<reservation>
            <item_no>'.$itemNo.'</item_no>
            <location_code>'.$locationCode.'</location_code>
            <reservation_qty>'.$delta.'</reservation_qty>
            <note>'.htmlspecialchars($note).'</note>
        </reservation>
        ';
        return $xml;
    }

    /**
     * @param $countryCode
     * @return OrderWS|mixed
     * @throws \Exception
     */
    private function getOrderWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create order service with no nav country");
        }
        if(isset($this->orderWs[intval($countryCode)])) {
            return $this->orderWs[intval($countryCode)];
        }
        $this->orderWs[intval($countryCode)] = new \GFCommon\Model\Navision\OrderWS(intval($countryCode));
        return $this->orderWs[intval($countryCode)];
    }


    /**
     * Output log message
     * @param $message
     * @return void
     */

    private $logItems = array();

    private function log($message) {
        $this->logItems[] = $message;
        if($this->output) {
            echo $message."<br>";
        }
    }

    public function getLogItems() {
        return $this->logItems;
    }

}