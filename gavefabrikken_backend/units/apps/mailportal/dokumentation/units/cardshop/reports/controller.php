<?php

namespace GFUnit\cardshop\reports;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {

        ini_set('memory_limit', '512M');
        parent::__construct(__FILE__);

    }

    public function index()
    {

        header("Location: gavefabrikken_backend/index.php?rt=mainaa");

    }

    /*
     * ORDERLIST
     */

    public function orderlist()
    {
        $model = new OrderListModel(false);
        $this->view("orderlistview",$model->getOrderListData());
    }

    
    public function orderlistcsv()
    {
        $model = new OrderListModel(false);
        $model->listProvider(true);
    }

    public function orderlistprovider()
    {
        $model = new OrderListModel(true);
        $model->listProvider();
    }

    /*
     * EARLYORDERLIST
     */

    public function earlyorderlist() {
        $model = new EarlyOrderModel(false);
        $this->view("earlyorderview",$model->getOrderListData());
    }

    public function earlyorderlistprovider()
    {
        $model = new EarlyOrderModel(false);
        $model->listProvider(false);
    }

    public function earlyorderlistcsv()
    {
        $model = new EarlyOrderModel(true);
        $model->listProvider(true);
    }


}