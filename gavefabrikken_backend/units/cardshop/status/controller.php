<?php

namespace GFUnit\cardshop\status;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function mainpage() {

        $dashboardModel = new StatusDashboardModel();
        $this->view("statusfront",array("model" => $dashboardModel));

    }

    public function toolfreightmatrix()
    {

        ob_start();
        $tool = new ToolFreightMatrix();
        $tool->showMatrix();

        $content = ob_get_contents();
        ob_end_clean();

        $this->view("template",array("title" => "Cardshop fragt matricer","body" => $content));

    }
    
    public function toolshopsetup()
    {

        $dashboardModel = new StatusDashboardModel();
        
        ob_start();
        $tool = new ToolShopSetup();
        $tool->showSetup($dashboardModel->getAvailableLanguages());

        $content = ob_get_contents();
        ob_end_clean();

        $this->view("template",array("title" => "Cardshop opsætning","body" => $content));
        
    }

}
