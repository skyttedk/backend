<?php

namespace GFUnit\tools\fordelingslist;
use GFBiz\units\UnitController;

class Controller extends UnitController
{



    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function testservice()
    {

        return;
        $shopList = \Shop::find_by_sql("SELECT * FROM `shop` where end_date is not null && end_date < now() && id not in (select shop_id from cardshop_settings) ORDER BY `shop`.`end_date`  ASC");
        foreach($shopList as $index => $shop) {

            echo "<br><h3>Shop ".$index." [".$shop->id."]: ".$shop->name."</h3><br>";
            $this->generateFordelingslist($shop->id);
            $this->generateSumlist($shop->id);
            echo "<br>";

            //if($index >= 100) exit();
        }

        exit();

    }

    function clean_filename($filename) {
        $invalid_chars = array("/", "\\", ":", "*", "?", "\"", "<", ">", "|");
        return str_replace($invalid_chars, "", $filename);
    }


    private function generateFordelingslist($shopid)
    {


        // Find shop
        $shop = \Shop::find($shopid);
        echo "Generating fordelingsrapport for shopid: " . $shopid . " - ".$shop->name."<br>";

        $filename = $this->clean_filename($shop->id . "_" . $shop->name . "_" . "fordelingsrapport" . "_" . date("d-m-Y").".xlsx");
        $filePath = dirname(__FILE__).DIRECTORY_SEPARATOR."files/".$filename;

        // Find parameters
        $_GET = array(
            "uea" => $shop->report_attributes,
            "supressheaders" => true
        );

        // Fordelingsrapport
        try {

            // Generate rapport
            ob_start();
            $rapport = new \shopForedelingRapport();
            $rapport->run($shop->id,"");
            $content = ob_get_contents();
            ob_end_clean();

            if(strstr($content, "[message:protected]")) {
                throw new \Exception("Looks like file has exception, cancel fordelingsreport");
            }

            // Save file
            file_put_contents($filePath,$content);

        } catch (\Exception $e) {
            echo $e->getMessage()."<br>";
        }

        //file_put_contents($filePath,"testdata");

    }

    private function generateSumlist($shopid)
    {


        // Find shop
        $shop = \Shop::find($shopid);
        echo "Generating sumliste for shopid: " . $shopid . " - ".$shop->name."<br>";

        $filename = $this->clean_filename($shop->id . "_" . $shop->name . "_" . "sumliste" . "_" . date("d-m-Y").".pdf");
        $filePath = dirname(__FILE__).DIRECTORY_SEPARATOR."files/".$filename;

        // Find parameters
        $_GET = array(
            "uea" => $shop->report_attributes,
            "supressheaders" => true,
            "type" => "sum"
        );

        // Fordelingsrapport
        try {

            // Generate rapport
            ob_start();
            $rapport = new \shopForedelingRapport();
            $rapport->run($shop->id,"sum");
            $content = ob_get_contents();
            ob_end_clean();

            if(strstr($content, "[message:protected]")) {
                throw new \Exception("Looks like file has exception, cancel sumliste");
            }

            // Save file
            file_put_contents($filePath,$content);

        } catch (\Exception $e) {
            echo $e->getMessage()."<br>";
        }

        //file_put_contents($filePath,"testdata");

    }

}