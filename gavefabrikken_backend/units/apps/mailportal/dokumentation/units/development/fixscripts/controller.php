<?php

namespace GFUnit\development\fixscripts;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);


    }

    public function index() {

        echo "SELECT SCRIPT TO RUN!";

    }


    public function updateshipmentstatus() {

        $model = new UpdateShipmentStatus();
        $model->run();

    }


    public function reopenorder() {
        $model = new ReopenOrder();
        $model->run();
    }

    public function createmissinggfship() {
        $model = new CreateMissingGFShip();
        $model->run();
    }



    public function navcommand() {
        $model = new NavCommandFix();
        $model->run();
    }



    public function checkprivatedelivery() {
        $model = new CheckPrivateDelivery();
        $model->run();
    }


    public function checkcardamount() {
        $model = new CheckCardAmount();
        $model->run();
    }


    public function contactemail() {
        $model = new ContactEmail();
        $model->run();
    }


    public function envfeeprep() {
        /*
        $model = new EnvFeePrep();
        $model->run();
        */
    }

    public function shipmentcheck()
    {
        /*
        $script = new ShipmentCheck();
        $script->run();
        */
    }

    public function fixnavorderdocs()
    {
        /*
        $script = new FixNavOrderDoc();
        $script->run();
        */
    }


    public function addmissingorderitem()
    {
        $model = new AddMissingOrderItem();
        $model->run();
    }
    /*
        public function parseweberror() {
            $model = new WebOrderErrorParse();
            $model->run();
        }

        public function missingorderitems()
        {
            $script = new MissingOrderitems();
            $script->run();
        }



        public function checkorders()
        {
            $script = new CheckOrders();
            $script->run();
        }

        public function shipmentchildcheck()
        {
            $script = new ShipmentChildCheck();
            $script->run();
        }
        */

    public function removeorder() 
    {
        $script = new RemoveOrder();
        $script->run();
    }


    public function reviveorder()
    {
        $script = new ReviveOrder();
        $script->run();
    }

    public function createearly()
    {
        $script = new CreateEarly();
        $script->createEarly();
    }


}