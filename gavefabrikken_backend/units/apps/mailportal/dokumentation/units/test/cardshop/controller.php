<?php

namespace GFUnit\test\cardshop;

use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function runintegrationtest()
    {
        $test = new IntegrationTest();
        $test->runIntegrationTest();
    }

    public function freightstate()
    {
        $test = new FreightState();
        $test->dispatch();
    }

    public function logintest()
    {
        $test = new LoginTest();
        $test->dispatch();
    }

    public function checkxml()
    {

        $companyOrder = \CompanyOrder::find(38295);
        $orderXML = new \GFCommon\model\navision\OrderXML($companyOrder,2);
        $xml = $orderXML->getXML();
        echo "<pre>";
        echo $xml;
        echo "</pre>";

    }

}