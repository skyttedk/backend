<?php

namespace GFUnit\apps\mails;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
    public function employeeWelcomeEmails()
    {
        $this->view("view");
    }
    public function test()
    {
        $this->view("test_view");
    }
    public function test2()
    {
        $this->view("test2");
    }

}
