<?php

namespace GFUnit\navision\syncorder;

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

        $navSyncList = \CompanyOrder::find_by_sql("SELECT id FROM company_order WHERE company_id IN (SELECT id FROM company WHERE language_code = ".$langCode.") && nav_synced = 3");
        $navSyncErrors = countgf($navSyncList);

        $navSyncList = \CompanyOrder::find_by_sql("SELECT id FROM company_order WHERE company_id IN (SELECT id FROM company WHERE language_code = ".$langCode.") && nav_synced = 4");
        $navSyncRetries = countgf($navSyncList);

        $onHoldList = \CompanyOrder::find_by_sql("SELECT id FROM company_order WHERE company_id IN (SELECT id FROM company WHERE language_code = ".$langCode.") && nav_on_hold > 0 && order_state >= 2");
        $navOnHold = countgf($onHoldList);

        $onHoldList = \CompanyOrder::find_by_sql("SELECT id FROM company_order WHERE company_id IN (SELECT id FROM company WHERE language_code = ".$langCode.") && shipment_on_hold > 0 && order_state >= 2");
        $shipmentOnHold = countgf($onHoldList);

        echo "<div style='margin-top: 10px; margin-bottom: 5px;'><div style='float: right; padding-top: 8px;'>Nav on hold: ".$navOnHold." | Shipment on hold: ".$shipmentOnHold." | Sync errors ".$navSyncErrors." | Sync retries: ".$navSyncRetries."</div>";

        echo "<span style='font-size: 18px; font-weight: bold;'>".$langName."</span></div>";
        $states = \CompanyOrder::find_by_sql("SELECT order_state, count(id) as order_count FROM company_order WHERE company_id IN (SELECT id FROM company WHERE language_code = ".$langCode.") GROUP BY order_state");

        $stateCount = array();
        foreach($states as $state) {
            $stateCount[$state->order_state] = $state->order_count;
        }

        echo "<table style='width: 100%; font-size: 12px;'><tr>";
        $stateList = \CompanyOrder::stateTextList();
        foreach($stateList as $stateIndex => $stateName) {
            echo "<td style='text-align:center; padding: 5px;'>".$stateIndex.": ".$stateName."</td>";
        }
        echo "</tr>";

        foreach($stateList as $stateIndex => $stateName) {
            $count = (isset($stateCount[$stateIndex]) ? $stateCount[$stateIndex] : 0);
            echo "<td style='text-align:center;  padding: 10px; ".($count > 0 ? "background: #CACACA;" : "")."'>".$count."</td>";
        }

        echo "</table>";

        //echo "<pre>".print_r($states,true)."</pre>";



    }


}