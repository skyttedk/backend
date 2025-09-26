<?php

namespace GFUnit\valgshop\orderconf;
use GFBiz\units\UnitController;
use GFBiz\valgshop\ShopOrderModel;
use GFBiz\valgshop\OrderBuilder;

class Controller extends UnitController
{


    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function test()
    {
        
        $shoporder = new ShopOrderModel(5109);
        $exporter = OrderBuilder::buildOrderHtml($shoporder);
        echo $exporter->export();
        

    }


    public function testwarnings()
    {

        $shopid = 7230;
        $shoporder = new ShopOrderModel($shopid);
        $exporter = OrderBuilder::buildOrderHtml($shoporder);

        echo "Shop ".$shopid." has ".$exporter->countErrors()." errors and ".$exporter->countWarnings()." warnings<br>";

        echo "<br>Errors:<br>";
        foreach($exporter->getErrors() as $error) {
            echo $error["message"]."<br>";
        }
        
        echo "<br></br>Warnings:<br>";
        foreach($exporter->getWarnings() as $warning) {
            echo $warning["message"]."<br>";
        }


    }

    public function html($shopid=0) {

        try {
            $shoporder = new ShopOrderModel($shopid);
            $exporter = OrderBuilder::buildOrderHtml($shoporder);
            echo $exporter->export();
        }
        catch (\Exception $e) {
            echo "FEJL I DATA: ";
            echo $e->getMessage();
            echo "<br>";
            echo $e->getFile()." @ ".$e->getLine();
        }


    }

    public function testxml()
    {

        $shoporder = new ShopOrderModel(5109);
        $exporter = OrderBuilder::buildOrderXML($shoporder);

        echo "<pre>".htmlentities($exporter->export())."</pre>";


    }

    public function testxmlraw()
    {

        $shoporder = new ShopOrderModel(5109);
        $exporter = OrderBuilder::buildOrderXML($shoporder);

        echo $exporter->export();


    }

}