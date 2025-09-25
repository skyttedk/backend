<?php

namespace GFUnit\jobs\valgshop;
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
    public function priceSync(){
      //  $sync = new PriceSync;
        //$sync->opdateChildsItem();
    }
    public function monitorItemno()
    {
        $txt = "hej";
       $sql = "SELECT * FROM `present_model` WHERE `id` = 960348 ";
       $rs =  \Dbsqli::getSql2($sql);
      echo $txt = $rs[0]["model_present_no"];
       $sql2 = "INSERT INTO monitor_itemno (txt) VALUES ('$txt')";;
        \Dbsqli::setSql2($sql2);
    }




}