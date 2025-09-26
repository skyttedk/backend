<?php

namespace GFUnit\navision\syncprivatedelivery;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function cronjob() {


        // Do not run navision jobs in this period
        if(in_array(intval(date("H")),array(3,4))) {
            echo "EXIT!";
            exit();
        }

        \GFCommon\DB\CronLog::startCronJob("NavCSPrivateDelivery");

        $syncModel = new PrivateDeliverySync(false);
        $syncModel->runSync();

        \GFCommon\DB\CronLog::endCronJob(1,"OK");

    }

    public function runtest()
    {

        $syncModel = new PrivateDeliverySync(true);
        $syncModel->runSync();

    }

}