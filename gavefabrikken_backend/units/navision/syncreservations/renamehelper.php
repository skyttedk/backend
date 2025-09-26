<?php

namespace GFUnit\navision\syncreservations;

use ActiveRecord\DateTime;
use GFCommon\DB\DBAccess;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\OrderWS;

class RenameHelper
{

    public function dispatch()
    {





        $this->form();

    }



    private function lookupItemNo($itemNo,$languageId)
    {

        $itemNo = trim($itemNo);
        $languageId = intval($languageId);

        ?><div>
        <h3>Tjekker varenr: <?php echo $itemNo; ?></h3>
        </div><?php

        // Navision status
        $item = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE language_id = ".$languageId." && `no` LIKE '".$itemNo."' ORDER BY DELETED DESC LIMIT 1");
        if(count($item) == 0) {
            ?><div style="padding-top: 15px; padding-bottom: 15px;color: red;">NAVISION STATUS: Varenr findes ikke i NAV</div><?php
        } else if($item[0]->deleted != NULL) {
            ?><div style="padding-top: 15px; padding-bottom: 15px;color: red;">NAVISION STATUS: Varenr slettet i NAV</div><?php
        } else if($item[0]->blocked == 1) {
            ?><div style="padding-top: 15px; padding-bottom: 15px;color: yellow;">NAVISION STATUS: Varenr blokkeret i NAV</div><?php
        } else {
            ?><div style="padding-top: 15px; padding-bottom: 15px;">NAVISION STATUS: Varenr er OK!</div><?php
        }

        // Parent items
        $bomList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE `no` LIKE '".$itemNo."' or `parent_item_no` LIKE '".$itemNo."' && language_id = ".$languageId);
        if(count($bomList) == 0) {
            ?><div style="padding-top: 15px; padding-bottom: 15px;color: red;">NAVISION BOM: Ikke en del af SAMPAK</div><?php
        } else {
            ?><b>NAVISION BOM:</b><br><table style="width: 100%;">
            <tr>
                <th>SAMPAK</th>
                <th>Varenr</th>
                <th>Quantity</th>
                <th>Deleted</th>
            </tr>
            <?php

            foreach($bomList as $rename) {
                ?><tr>
                <td><?php echo $rename->parent_item_no; ?></td>
                <td><?php echo $rename->no; ?></td>
                <td><?php echo $rename->quantity_per; ?></td>
                <td><?php echo $rename->deleted == null ? "Aktiv" : $rename->deleted->format('Y-m-d H:i'); ?></td>
                </tr><?php
            }

            ?>
            </table><br><?php

        }

        // Rename LOG
        $renameItems = \NavisionItemRename::find_by_sql("SELECT * FROM `navision_itemrename` where old_no LIKE '".$itemNo."' && sync_reservation IS NULL ORDER BY renamed_at DESC");
        if(count($renameItems) == 0) {
            ?><div style="color: red;">OMDØB STATUS: Ingen omdøbninger mangler!</div><?php
        } else {
            ?><b>Omdøbninger:</b><br><table style="width: 100%;">
            <tr>
                <th>Gammelt varenr</th>
                <th>Nyt varenr</th>
                <th>Sprog</th>
                <th>Dato</th>
                <th>Initialer</th>
            </tr>
            <?php

            foreach($renameItems as $rename) {
                ?><tr>
                <td><?php echo $rename->old_no; ?></td>
                <td><?php echo $rename->new_no; ?></td>
                <td><?php echo $rename->language_id; ?></td>
                <td><?php echo $rename->renamed_at->format('Y-m-d H:i'); ?></td>
                <td><?php echo $rename->renamed_by; ?></td>
                </tr><?php
            }

            ?>
            </table><?php
        }

        // Reservation logs
        $reservationLogList = \NavisionReservationLog::find_by_sql("SELECT * FROM `navision_reservation_log` WHERE `itemno` LIKE '230323' ORDER BY `navision_reservation_log`.`shop_id` ASC, created ASC");
        $reservationLogMap = array();
        foreach($reservationLogList as $resLog) {
            $reservationLogMap[$resLog->shop_id][] = $resLog;
        }

        if(count($renameItems) == 0) {
            ?><div style="color: red;">RESERVATIONS LOG: Ingen reservations logs!</div><?php
        } else {

            foreach($reservationLogMap as $shopID => $logList) {

                $shops = \Shop::find_by_sql("select * from shop where id = ".intval($shopID));
                $shopName = count($shops) == 0 ? "SHOP IKKE FUNDET" : $shops[0]->name;
                $resids = array();

                ?><br><br><h2><?php echo $shopName; ?></h2><b>Reservations logs:</b><br><table style="width: 100%;">
                    <tr>
                        <th>Varenr</th>
                        <th>Lokation</th>
                        <th>Oprettet</th>
                        <th>Delta</th>
                        <th>Balance</th>
                        <th>Noter</th>
                        <th>Res id's</th>
                        <th>Rename felt</th>
                    </tr><?php

                    foreach($logList as $logItem) {
                        ?><tr>
                            <td><?php echo $logItem->itemno; ?></td>
                        <td><?php echo $logItem->location; ?></td>
                        <td><?php echo $logItem->created->format('Y-m-d H:i'); ?></td>
                        <td><?php echo $logItem->delta; ?></td>
                        <td><?php echo $logItem->balance; ?></td>
                        <td><?php echo $logItem->notes; ?></td>
                        <td><?php echo $logItem->present_reservations_ids; ?></td>
                        <td><?php echo $logItem->rename_itemno; ?></td>
                        </tr><?php

                        $idsplit = explode(",",$logItem->present_reservations_ids);
                        foreach($idsplit as $id) {
                            if(trim($id) != "") {
                                $resids[] = $id;
                            }
                        }

                    }

                ?></table><br><?php

                if(count($resids) > 0) {

                    $presentResList = \PresentReservation::find_by_sql("SELECT * FROM present_reservation WHERE id IN (".implode(",",$resids).") ORDER BY id ASC");

                    ?><b>Present reservation</b><br><table style="width: 100%;">
                        <tr>
                            <th>Varenr</th>
                            <th>Nav status</th>
                            <th>Antal</th>
                            <th>Sync time</th>
                            <th>Sync antal</th>
                        </tr><?php

                        foreach($presentResList as $presentRes) {

                            $itemNo = "";
                            $navStatus = "";

                            $presentModel = \PresentModel::find_by_sql("SELECT * FROM present_model where language_id = 1 && model_id = ".intval($presentRes->model_id));
                            if(count($presentModel) == 0) {
                                $itemNo = "Kan ikke finde model";
                            } else {
                                $itemNo = $presentModel[0]->model_present_no;
                                $item = \NavisionItem::find_by_sql("SELECT * FROM navision_item WHERE language_id = ".$languageId." && no LIKE '".$itemNo."'");
                                if(count($item) == 0) {
                                    $navStatus = "Varenr findes ikke i NAV";
                                } else if($item[0]->deleted != NULL) {
                                    $navStatus = "Varernr slettet i NAV";
                                } else if($item[0]->blocked == 1) {
                                    $navStatus = "Varenr blokkeret i NAV";
                                } else {
                                    $navStatus = "Varenr er OK!";
                                }

                            }


                            

                            ?><tr>
                            <td><?php echo $itemNo; ?></td>
                            <td><?php echo $navStatus; ?></td>
                            <td><?php echo $presentRes->quantity; ?></td>
                            <td><?php echo $presentRes->sync_time->format('Y-m-d H:i'); ?></td>
                            <td><?php echo $presentRes->sync_quantity; ?></td>
                            </tr><?php

                        }

                        ?></table><br><?php

                } else {
                    ?><div style="padding-top: 15px; padding-bottom: 15px;color: yellow;">RESERVATIONER: Der er ikke reservationer tilknyttet.</div><?php
                }


                echo "<br><br>";
             

            }




            /*
            ?>
            <tr>
                <th>Gammelt varenr</th>
                <th>Nyt varenr</th>
                <th>Sprog</th>
                <th>Dato</th>
                <th>Initialer</th>
            </tr>
            <?php

            foreach($renameItems as $rename) {
                ?><tr>
                <td><?php echo $rename->old_no; ?></td>
                <td><?php echo $rename->new_no; ?></td>
                <td><?php echo $rename->language_id; ?></td>
                <td><?php echo $rename->renamed_at->format('Y-m-d H:i'); ?></td>
                <td><?php echo $rename->renamed_by; ?></td>
                </tr><?php
            }

            ?>
            </table><?php
            */
        }






    }

    private function form()
    {
        
        $renameItems = \NavisionItemRename::find_by_sql("SELECT * FROM `navision_itemrename` where sync_reservation IS NULL ORDER BY renamed_at DESC");

        ?><html><style>
        body { font-family: verdana; font-size: 10px;}
        table { font-family: verdana; font-size: 10px;  border-collapse: collapse; }

        td,th { padding: 5px; padding-right: 15px; border-bottom: 1px solid #A0A0A0; }
        td:first-child, th:first-child { text-align: left;}
        th { background: #F0F0F0; font-weight: bold; font-size: 14px; }
    </style>

        <h2>Reservations - rename tool</h2>
        <table style="width: 100%;">
        <tr>
            <td valign="top" style="width: 33%; padding: 20px;" valign="top">
                <form method="post" action="index.php?rt=unit/navision/syncreservations/rename">
                    Varenr: <input type="text" value="<?php if(isset($_POST["itemno"])) echo $_POST["itemno"]; ?>" name="itemno">
                    <select name="language_id"><option value="1">DK</option></select>
                    <input type="hidden" name="action" value="rename">
                    <input type="submit" value="Tjek">
                </form>

                <?php

                if(isset($_POST["action"]) && $_POST["action"] == "rename") {
                    $this->lookupItemNo($_POST["itemno"],$_POST["language_id"]);
                }

                ?>

            </td>
            <td valign="top" style="width: 33%; padding: 20px;" valign="top">

                <h2>Tjek reservation balance</h2>
                <form method="post" action="index.php?rt=unit/navision/syncreservations/rename">
                    Varenr: <input type="text" value="<?php if(isset($_POST["itemno"])) echo $_POST["itemno"]; ?>" name="itemno">
                    <select name="language_id"><option value="1">DK</option></select>
                    <input type="hidden" name="checkaction" value="balance">
                    <input type="submit" value="Tjek">
                </form>

                <?php if(isset($_POST["checkaction"]) && $_POST["checkaction"] == "balance") {

                    echo "Checking item no: ".$_POST["itemno"]." on ".$_POST["language_id"]."<br>";

                    $orderWS = new OrderWS($_POST["language_id"]);
                    if($orderWS->getReservationBalance($_POST["itemno"])) {
                        $balance = $orderWS->getLastReservationBalance();
                        echo "Balance: ".$balance."<br>";
                    }

                    $locationSum = array();
                    $locationShopSum = array();
                    $locationShopList = array();
                    $totalSum = 0;

                    // Load present reservations
                    $reservationLog = \NavisionReservationLog::find_by_sql("SELECT * FROM `navision_reservation_log` WHERE language_id = ".intval($_POST["language_id"])." && `itemno` LIKE '".$_POST["itemno"]."' ORDER BY created ASC, id DESC");
                    foreach($reservationLog as $log) {
                        $locationShopSum[trim($log->location)][$log->shop_id] = $log->balance;
                        $locationShopList[trim($log->location)][$log->shop_id][] = $log;
                    }

                    foreach($locationShopSum as $location => $shopSum) {
                        $locationSum[trim($location)] = array_sum($shopSum);
                        $totalSum += array_sum($shopSum);
                    }

                    $problemLocations = array();

                    echo "Total i database: ".$totalSum."<br>";
                    echo "<h3>Fordelt på lokation</h3>   ";
                    echo "<table style='width: 100%;'><tr><td>Lokation</td><td>CS</td><td>NAV</td><td>DIF</td></tr>";

                    var_dump($locationSum);
                    foreach($locationSum as $location => $sum) {
                        $navSum = "-";
                        if($orderWS->getReservationBalance($_POST["itemno"],$location)) {
                            $navSum = $orderWS->getLastReservationBalance();
                        }
                        echo "<tr><td>".$location."</td><td>".$sum."</td><td>".$navSum."</td><td>".($navSum-$sum)."</td></tr>";
                        if($sum != $navSum) {
                            $problemLocations[] = $location;
                        }
                    }
                    echo "</table>";

                    $navcallogids = array();
                    foreach($problemLocations as $location)  {
                        echo "<h3>Lokation: ".$location."</h3>";
                        $shops = $locationShopSum[$location];
                        foreach($shops as $shop => $sum) {


                            $shopObj = \Shop::find($shop);

                            echo "<div style='padding-top: 10px; padding-bottom: 10px;'><h4>[".$shop."] ".$shopObj->name.": ".$sum."</h4></div>";
                            echo "<table>";

                            foreach($locationShopList[$location][$shop] as $log) {
                                $navcallogids[] = $log->navision_call_log_id;
                                echo "<tr><td>".$log->itemno."</td><td>".$log->shop_id."</td><td>".$log->location."</td><td>".$log->delta."</td><td>".$log->balance."</td></tr>";
                            }

                            echo "</table>";

                        }
                    }

                    echo implode(",",$navcallogids);




                    
                } ?>

            </td>
            <td valign="top" style="width: 33%; padding: 20px;" valign="top">
                <h3>Seneste omdøbte varenr</h3>
                <table style="width: 100%;">
                    <tr>
                        <th>Gammelt varenr</th>
                        <th>Nyt varenr</th>
                        <th>Sprog</th>
                        <th>Dato</th>
                        <th>Initialer</th>
                    </tr>
                    <?php

                    foreach($renameItems as $rename) {
                        ?><tr>
                            <td><?php echo $rename->old_no; ?></td>
                            <td><?php echo $rename->new_no; ?></td>
                            <td><?php echo $rename->language_id; ?></td>
                            <td><?php echo $rename->renamed_at->format('Y-m-d H:i'); ?></td>
                            <td><?php echo $rename->renamed_by; ?></td>
                        </tr><?php
                    }

                    ?>
                </table>
            </td>
        </tr>
        </table></html><?php

    }

}