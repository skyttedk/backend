<?php

namespace GFUnit\navision\synccompany;

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

        echo "<div style='font-size: 18px; font-weight: bold; margin-top: 15px; margin-bottom: 5px;'>".$langName."</div>";
        $states = \Company::find_by_sql("SELECT company_state, count(id) as company_count FROM company WHERE language_code = ".$langCode." GROUP BY company_state");

        $stateCount = array();
        foreach($states as $state) {
            $stateCount[$state->company_state] = $state->company_count;
        }

        echo "<table style='width: 100%; font-size: 12px;'><tr>";
        $stateList = \Company::stateTextList();
        foreach($stateList as $stateIndex => $stateName) {
            echo "<td style='text-align:center; padding: 5px;'>".$stateIndex.": ".$stateName."</td>";
        }
        echo "</tr>";

        foreach($stateList as $stateIndex => $stateName) {
            $count = (isset($stateCount[$stateIndex]) ? $stateCount[$stateIndex] : 0);
            echo "<td style='text-align:center; padding: 10px; ".($count > 0 ? "background: #CACACA;" : "")."'>".$count."</td>";
        }

        echo "</table>";

        //echo "<pre>".print_r($states,true)."</pre>";



    }


}