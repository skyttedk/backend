<?php

namespace GFUnit\vlager\lagerfront;
use GFBiz\units\UnitController;
use GFUnit\vlager\utils\Template;
use GFUnit\vlager\utils\VLager;
class LogPage
{


    private static $processMessage = "";
    private static $processWarning = "";


    public static function outputLogPage($url, \VLager $vlager,$itemno)
    {


        Template::templateTop();
        Template::outputFrontendHeader($url, $vlager);

        $sql = "SELECT vlager_item.itemno, vlager_item_log.log_time, vlager_item_log.quantity, vlager_item_log.balance, description, shipment_id  FROM `vlager_item_log`, vlager_item WHERE vlager_item_log.`vlager_item_id` = vlager_item.id and vlager_item.itemno = '".htmlentities($itemno)."' ORDER BY `log_time`  ASC";
        $result = \VLagerItemLog::find_by_sql($sql);

        // Make header with log and itemno and back button

        echo "<div class='container mt-4'>";
        echo "<div style='float: right;'>";
        echo "<button class='btn btn-secondary' type='button' onClick='document.location=\"$url\"'>Tilbage</button>";
        echo "</div>";
        echo "<h3>Log for varenummer: $itemno</h3><br>";

        // Make table with log
        echo "<table class='table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Varenr</th>";
        echo "<th>Beskrivelse</th>";
        echo "<th>Tidspunkt</th>";
        echo "<th>Antal</th>";
        echo "<th>Balance</th>";
        echo "<th>Ordre ID</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        foreach($result as $row) {



            echo "<tr style='background: ".($row->shipment_id > 0 ? "#E8F9FF" : "#F4FFC3")."'>";
            echo "<td>".$row->itemno."</td>";
            echo "<td>".$row->description."</td>";
            echo "<td>".$row->log_time->format("Y-m-d H:i")."</td>";
            echo "<td>".$row->quantity."</td>";
            echo "<td>".$row->balance."</td>";
            echo "<td>".$row->shipment_id."</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";

        Template::templateBottom();

    }

}