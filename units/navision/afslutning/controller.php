<?php

namespace GFUnit\navision\afslutning;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\OrderWS;
use GFUnit\navision\synccompany\CompanySync;
use GFUnit\navision\syncorder\OrderSync;
use GFUnit\navision\syncshipment\ShipmentSync;
use GFUnit\navision\syncexternalshipment\ExternalShipmentSync;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index() {

        echo "AFSLUTNINGSCONTROLLER";

    }

    public function dashboard() {

        $helper = new AfslutHelper();
        $this->view("dashboard",array("helper"=>$helper));

    }

    public function runafslut() {

        $afslutModel = new RunAfslut();
        $afslutModel->runafslut();
        

    }



}

