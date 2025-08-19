<?php

namespace GFUnit\lister\efterlevering;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct() {
        parent::__construct(__FILE__);
    }

    public function index() {
        
        $efterlevering = new Efterlevering();
        $efterlevering->dispatch();
        
    }

    

}