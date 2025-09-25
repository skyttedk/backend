<?php

namespace GFUnit\vlager\incoming;
use GFBiz\units\UnitController;


class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {
    }

    public function runjob()
    {
        $checkJob = new CheckIncomingJob();
        $checkJob->checkIncomingJob();
    }

}
