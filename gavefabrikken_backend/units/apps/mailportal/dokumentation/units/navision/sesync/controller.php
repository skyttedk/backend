<?php

namespace GFUnit\navision\sesync;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function runsync()
    {
        echo "RUN SE SYNC:";
return;
        $syncModel = new SESync(true);
        //$syncModel->showNext();
        $syncModel->syncAll();
    }

/*
    public function runsync()
    {
        echo "RUN SHIPMENT SYNC";
        $model = new ShipmentSync(true);
        $model->syncAll();
    }


    public function syncid($shipmentid=0)
    {
        echo "Start sync shipment id: ".intval($shipmentid);
        $shipment = \Shipment::find(intval($shipmentid));
        $model = new ShipmentSync(true);
        $model->syncShipment($shipment);

        // Commit database
        \system::connection()->commit();
        \System::connection()->transaction();

    }

    public function check()
    {
        // CHECK FOR PROBLEMS
    }

    public function shiptocheck()
    {
        // Preprocess checks
        $shiptoCheck = new ShiptoCheck();
        $shiptoCheck->run(true);

    }

    public function dashboard()
    {
        $dashboard = new Dashboard();
        $dashboard->dashboard();
    }

    public function blocklist($type="",$id=0)
    {
        $blockList = new BlockMessage();
        if($type == "shipment" && intval($id) > 0) {
            $blockList->releaseShipment(intval($id));
        }
        $blockList->messageList();
    }

    public function next()
    {
        $model = new ShipmentSync(true);
        $model->showNext();
    }
*/


}