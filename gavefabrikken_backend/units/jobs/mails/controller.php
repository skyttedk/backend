<?php

namespace GFUnit\jobs\mails;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {
        echo "NO ACTION HERE!";
    }

    public function rundomaincheck()
    {
        $model = new CheckDomainBlock();
        $model->runDomainBlock();
    }

    public function blockmonitor()
    {
        
        $model = new CheckDomainBlock();
        $model->runDomainBlockMonitor();

    }


}