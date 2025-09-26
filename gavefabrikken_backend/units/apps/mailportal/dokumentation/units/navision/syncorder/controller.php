<?php

namespace GFUnit\navision\syncorder;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }


    public function syncid($orderid=0)
    {
        echo "Start sync order id: ".intval($orderid);
        $order = \CompanyOrder::find(intval($orderid));
        $model = new OrderSync(true);
        $model->syncCompanyOrder($order);
    }

    public function runsync()
    {
        echo "RUN ORDER SYNC";
        $model = new OrderSync(true);
        $model->syncAll();
    }

    public function check()
    {
        // CHECK FOR PROBLEMS
    }

    public function dashboard()
    {
        $dashboard = new Dashboard();
        $dashboard->dashboard();

    }

    public function blocklist($type="",$id=0)
    {

        $blockList = new BlockMessage();

        if($type == "order" && intval($id) > 0) {
            $blockList->releaseOrder(intval($id));
        }

        $blockList->messageList();

    }

    public function next()
    {
        $model = new OrderSync(true);
        $model->showNext();
    }



}