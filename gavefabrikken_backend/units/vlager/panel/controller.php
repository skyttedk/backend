<?php

namespace GFUnit\vlager\panel;
use GFBiz\units\UnitController;
use GFUnit\vlager\utils;
use GFUnit\vlager\utils\Template;
use GFUnit\vlager\utils\VLagerCounter;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {

        $dash = new Dashboard();
        $dash->show();

    }

    public function front($vlager) {

        $dispatcher = new \GFUnit\vlager\lagerfront\Dispatcher();
        $dispatcher->setSystemUser("/gavefabrikken_backend/index.php?rt=unit/vlager/panel/front/".$vlager,$vlager);
        $dispatcher->dispatch();

    }

    public function liste($vlager,$shopid=0)
    {

       $list = new Liste($vlager,$shopid);
       $list->showList();


    }

    public function download($vlager,$shopid=0)
    {
        $list = new Liste($vlager,$shopid);
        $list->downloadList();
    }



}