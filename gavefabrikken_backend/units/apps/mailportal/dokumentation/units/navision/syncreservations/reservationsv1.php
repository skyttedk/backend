<?php

namespace GFUnit\navision\syncreservations;
use ActiveRecord\Model;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\OrderWS;

class ControllerV1 extends UnitController
{

    private $output = false;

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function dashboard() {


        ?><style>
            body { font-family: verdana; font-size: 14px;}
            table { font-family: verdana; font-size: 14px;  border-collapse: collapse; }

            td,th { padding: 5px; padding-right: 15px; border-bottom: 1px solid #A0A0A0; text-align: right; }
            td:first-child, th:first-child { text-align: left;}
            th { background: #F0F0F0; font-weight: bold;}
        </style><?php

        $reservationList = $this->findReservationShops();
        foreach($reservationList as $reservationGroup) {

            ?><h2>Reservationer til <?php echo $reservationGroup->reservation_code; ?></h2><?php
            $this->reservationGroupDashboard($reservationGroup->reservation_code);
            echo "<br><hr><br>";
        }

    }

    private function reservationGroupDashboard($reservationGroup) {

        // Find shops with reservation code
        $shopList = \Shop::find_by_sql("SELECT * FROM shop WHERE reservation_code = '".$reservationGroup."'");

        // Get reservations
        $reservationSQL = "SELECT model_present_no, SUM(quantity) as quantity, shop_id, min(present_model.model_name) as modelname FROM shop, present_reservation, present_model WHERE present_reservation.present_id = present_model.present_id && present_reservation.model_id = present_model.model_id && present_model.language_id = 1 && shop.id = present_reservation.shop_id && shop.reservation_code = '".$reservationGroup."' group by model_present_no, shop_id;";
        $reservationList = \PresentReservation::find_by_sql($reservationSQL);


        $presentMap = array();
        $presentNumberList = array();
        $presentNameMap = array();
        $presentModelTotal = array();
        $shopTotal = array();
        $totalTotal = 0;

        foreach($reservationList as $presentRes) {

            if(!isset($presentMap[$presentRes->model_present_no])) $presentMap[$presentRes->model_present_no]  = array();
            $presentMap[$presentRes->model_present_no][$presentRes->shop_id] = $presentRes->quantity;

            if(!isset($presentNameMap[$presentRes->model_present_no])) {
                $presentNameMap[$presentRes->model_present_no] = $presentRes->modelname;
                $presentNumberList[] = $presentRes->model_present_no;
            }

            if(!isset($shopTotal[$presentRes->shop_id])) $shopTotal[$presentRes->shop_id] = 0;
            $shopTotal[$presentRes->shop_id] += $presentRes->quantity;

            if(!isset($presentModelTotal[$presentRes->model_present_no])) $presentModelTotal[$presentRes->model_present_no] = 0;
            $presentModelTotal[$presentRes->model_present_no] += $presentRes->quantity;

            $totalTotal += $presentRes->quantity;
        }

        sort($presentNumberList);

        ?><table>
        <thead>
        <tr>
            <th>Varenr</th>
            <th style="text-align: left !important;">Navn</th>
            <?php foreach ($shopList as $shop) {
                echo "<th>".$shop->name."</th>";

            } ?>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($presentNumberList as $presentNo) { if(trim($presentNo) != "") {

            $varenrList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$presentNo."' AND `deleted` IS NULL");
            $varenr = countgf($varenrList) > 0 ? $varenrList[0] : null;
            $validNumber = true;
            if($varenr === null) {
                $validNumber = false;
            }


            ?>
            <tr style="<?php if($validNumber == false) echo "background: yellow; -webkit-print-color-adjust:exact;"; ?>">
                <td><?php echo $presentNo; ?></td>
                <td style="text-align: left !important;"><?php echo $presentNameMap[$presentNo]; ?></td>
                <?php foreach ($shopList as $shop) {
                    echo "<td>".(isset($presentMap[$presentNo][$shop->id]) ? $presentMap[$presentNo][$shop->id] : 0)."</td>";
                } ?>
                <td><?php echo $presentModelTotal[$presentNo]; ?></td>
            </tr>
        <?php } } ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="2">Total</th>

            <?php foreach ($shopList as $shop) {
                echo "<th>".($shopTotal[$shop->id])."</th>";

            } ?>
            <th><?php echo $totalTotal; ?></th>
        </tr>
        </tfoot>
        </table><?php

    }

    public function sync($output=0) {


        // Do not run navision jobs in this period
        if(in_array(intval(date("H")),array(3,4))) {
            exit();
        }


        if($output == 1) {
            $this->output = true;
        }

        $this->log("Staring reservations sync");

        $reservationList = $this->findShopsToSync();
        foreach($reservationList as $reservationGroup) {
            $this->log("Starting reservation group:<br> - Reservation code: ".$reservationGroup->reservation_code."<br> - Shops: ".$reservationGroup->shop_list."<br> - Updated presents: ".$reservationGroup->update_reservations."<br>");
            $this->syncReservationLocation($reservationGroup->reservation_code);
            $this->log("<hr>");
        }

        \response::silentsuccess();

    }

    private function findShopsToSync() {
        $sql = "SELECT shop.reservation_code, GROUP_CONCAT(distinct shop.id) as shop_list, count(present_reservation.id) as update_reservations FROM shop, present_reservation WHERE shop.id = present_reservation.shop_id && (sync_time IS NULL || (update_time IS NOT NULL AND sync_time < update_time)) && (shop.reservation_code IS NOT NULL AND shop.reservation_code != '') GROUP BY shop.reservation_code;";
        return \PresentReservation::find_by_sql($sql);
    }

    private function findReservationShops() {
        $sql = "SELECT shop.reservation_code, GROUP_CONCAT(distinct shop.id) as shop_list, count(present_reservation.id) as update_reservations FROM shop, present_reservation WHERE shop.id = present_reservation.shop_id && (shop.reservation_code IS NOT NULL AND shop.reservation_code != '') GROUP BY shop.reservation_code;";
        return \PresentReservation::find_by_sql($sql);
    }

    private function syncReservationLocation($location) {


        $this->log("-- Start syncing location: ".$location);

        if(trim($location) == "") {
            $this->log("Invalid location, abort");
            return;
        }

        // Get reservations
        $reservationSQL = "SELECT model_present_no, SUM(quantity) as quantity FROM shop, present_reservation, present_model WHERE present_reservation.present_id = present_model.present_id && present_reservation.model_id = present_model.model_id && present_model.language_id = 1 && shop.id = present_reservation.shop_id && shop.reservation_code = '".$location."' group by model_present_no;";
        $reservationList = \PresentReservation::find_by_sql($reservationSQL);

        // Generate xml
        $this->startXML();
        $hasInvalidItems = false;
        foreach($reservationList as $reservation) {
            try {
                $this->log(" --- ".$reservation->model_present_no." - ".$reservation->quantity);
                $this->addXMLReservation($reservation->model_present_no,$location,$reservation->quantity);
            } catch (\Exception $e) {
                $this->log("---- Unknown item no: ".$reservation->model_present_no." - ".$e->getMessage());

                $varenrMissingSql = "select shop.name, present_model.model_name, present_model.model_present_no from shop, present_reservation, present_model WHERE present_model.model_present_no = '".$reservation->model_present_no."' && present_reservation.present_id = present_model.present_id && present_reservation.model_id = present_model.model_id && present_model.language_id = 1 && shop.id = present_reservation.shop_id && shop.reservation_code = '".$location."'";
                $varenrMissingList = \PresentModel::find_by_sql($varenrMissingSql);

                foreach($varenrMissingList as $missingVarenr) {
                    $this->log("- ".$missingVarenr->name.": varenr. ".$missingVarenr->model_present_no.", navn: ".$missingVarenr->model_name);
                }

                $hasInvalidItems = true;
            }
        }

        if($hasInvalidItems == true) {
            $this->log(" ---- Abort due to unknown items");
        }

        $xml = $this->getXML();
        /*
           echo "<br><pre>";
           echo htmlentities($xml);
           echo "</pre><br>";
   */
        // Send to navision
        // Get nav client
        try {
            $this->log(" --- Create nav client");
            $client = $this->getOrderWS(1);
        } catch(\Exception $e) {
            $this->log(" ---- Coult not create nav client: ".$e->getMessage());
            return false;
        }

        // Send order to navision
        try {

            $this->log(" --- Send to navision");

            $reservationResponse = $client->uploadReservationDoc($xml);

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

        } catch (Exception $e) {

            // Possivle false positive (version has increased)
            NavDebugTools::mailProblem("NavClient error syncing reservation doc","Exception during upload of reservation document<br><pre>".$e->getMessage()."\r\n\r\nXML\r\n".$xml."</pre>");
            return;

        }

        // Update sql
        try {
            $sql = "UPDATE shop, present_reservation SET last_change = update_time, sync_time = NOW() WHERE shop.id = present_reservation.shop_id && shop.reservation_code = '".$location."'";
            \ActiveRecord\ConnectionManager::get_connection("default")->query($sql);
            $this->log(" --- Updated sync_time on reservations");
        }
        catch (\Exception $e) {
            $this->log(" --- Error updating reservation sync times for location ".$location);
        }


    }


    private $orderWs = array();

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

    private function log($message) {
        if($this->output) {
            echo $message."<br>";
        }
    }

    /***
     * XML FUNCTIONALITY
     */

    private $xml = "";

    private function startXML() {
        $this->xml = "";
    }

    private function addXMLReservation($itemNo,$locationCode,$quantity) {

        $varenrList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$itemNo."' AND `deleted` IS NULL");
        $varenr = countgf($varenrList) > 0 ? $varenrList[0] : null;

        if($varenr === null) {
            throw new \Exception("Could not find navision item no ".$itemNo);
        }

        $this->xml .= '<reservation>
            <item_no>'.$itemNo.'</item_no>
            <location_code>'.$locationCode.'</location_code>
            <reservation_qty>'.$quantity.'</reservation_qty>
        </reservation>
        ';
    }

    private function getXML() {

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<reservations>
    ';

        $xml .= $this->xml;

        $xml .= '
</reservations>';

        return $xml;

    }

}