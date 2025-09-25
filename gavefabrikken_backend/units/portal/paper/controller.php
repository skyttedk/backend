<?php

namespace GFUnit\portal\paper;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
    public function login()
    {
        $this->view("login");
    }
}