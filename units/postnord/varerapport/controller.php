<?php

namespace GFUnit\postnord\varerapport;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        exit();
        parent::__construct(__FILE__);
    }

    // [BACKENDURL]/index.php?rt=unit/postnord/varerapport

    public function download()
    {
        return;
        $vareRapport = new Varerapport();
        $vareRapport->downloadReport();
    }
    
    
}