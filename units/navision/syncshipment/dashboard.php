<?php

namespace GFUnit\navision\syncshipment;

class Dashboard
{

    public function __construct()
    {

    }

    public function dashboard()
    {
        $this->outputLanguage(1,"Danmark");
        $this->outputLanguage(4,"Norge");
        $this->outputLanguage(5,"Sverige");
        $this->outputLanguage(0,"Ingen sprog");
    }

    public function outputLanguage($langCode,$langName)
    {
/*
        $onHoldList = \CompanyOrder::find_by_sql("SELECT id FROM company_order WHERE company_id IN (SELECT id FROM company WHERE language_code = ".$langCode.") && nav_on_hold > 0");
        $navOnHold = countgf($onHoldList);

        $onHoldList = \CompanyOrder::find_by_sql("SELECT id FROM company_order WHERE company_id IN (SELECT id FROM company WHERE language_code = ".$langCode.") && shipment_on_hold > 0");
        $shipmentOnHold = 0;
*/

        $shipmentBlocked = 0;
        
        echo "<div style='margin-top: 5px; margin-bottom: 2px;'><div style='float: right; padding-top: 5px;'>Needs approval: ".$shipmentBlocked."</div>";
        echo "<span style='font-size: 18px; font-weight: bold;'>".$langName."</span></div><br>";

        $states = \Shipment::find_by_sql("SELECT shipment_state, shipment_type, count(id) as shipment_count FROM shipment WHERE companyorder_id IN (SELECT id FROM company_order WHERE company_id IN (SELECT id FROM company WHERE language_code = ".$langCode.")) GROUP BY shipment_type, shipment_state");

        $stateCount = array();
        foreach($states as $state) {
            if(!isset($stateCount[$state->shipment_type])) $stateCount[$state->shipment_type] = array();
            $stateCount[$state->shipment_type][$state->shipment_state] = $state->shipment_count;
        }

        echo "<table style='width: 100%; font-size: 12px;'><tr><td>&nbsp;</td>";
        $stateList = \Shipment::stateTextList();
        foreach($stateList as $stateIndex => $stateName) {
            echo "<td style='text-align:center; padding: 5px;'>".$stateIndex.": ".$stateName."</td>";
        }
        echo "</tr>";

        foreach($stateCount as $shipmentType => $stateMap) {
            echo "<tr><td>".$shipmentType."</td>";
            foreach($stateList as $stateIndex => $stateName) {
                $count = (isset($stateMap[$stateIndex]) ? $stateMap[$stateIndex] : 0);
                echo "<td style='text-align:center;  padding: 8; ".($count > 0 ? "background: #CACACA;" : "")."'>".$count."</td>";
            }
            echo "</tr>";
        }

        echo "</table><br>";

        //echo "<pre>".print_r($states,true)."</pre>";



    }


}