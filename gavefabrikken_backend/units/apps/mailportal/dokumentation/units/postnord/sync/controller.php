<?php

namespace GFUnit\postnord\sync;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        exit();
        parent::__construct(__FILE__);
    }


    // [BACKENDURL]/index.php?rt=unit/postnord/sync/syncrun/1
    public function syncrun($output=0)
    {

        return;
        $runner = new SyncRunner(false,$output == 1);
        $runner->runSync();

    }
    
    public function processdownloads() 
    {
        return;
        $runner = new HandleDownloads();
        $runner->runHandler();
        
    }


}