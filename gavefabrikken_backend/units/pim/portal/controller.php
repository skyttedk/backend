<?php

namespace GFUnit\pim\portal;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }
    public function getLanguage(){
        $systemuser = \SystemUser::readSystemUser($_POST['id']);
        \response::success(make_json("systemuser", $systemuser));
    }

    public function getPresent($id){
        $item = new item();
        \response::success(make_json("res", $item->getPresent($id)));
    }
    public function getPresentDescription($id){
        $item = new item();
        \response::success(make_json("res", $item->getPresentDescription($id)));
    }
    public function getPresentMedia ($id){
        $item = new item();
        \response::success(make_json("res", $item->getPresentMedia($id)));
    }
    public function getPresentModel($id){
        $item = new item();
        \response::success(make_json("res", $item->getPresentModel($id)));
    }



    public function table($param=""){
       $item = new item($_POST);
       echo json_encode($item->loadItemList());


    /*
    echo $param;
        //print_R($_POST);
        $returndata = array(
            "recordsTotal"=>3,
            "recordsFiltered"=>3,
            "data" => [
                ["first_name"=>"Zorita"],
                ["first_name"=>"Zenaida"],
                ["first_name"=>"Yuri"]
            ]

        );
        echo json_encode($returndata);
    */
    }


    /**
     * SERVICES
     * $('#myTable').DataTable( {
    serverSide: true,
    ajax: {
    url: '/api/data',
    dataFilter: function(data){
    var json = jQuery.parseJSON( data );
    json.recordsTotal = json.total;
    json.recordsFiltered = json.total;
    json.data = json.list;

    return JSON.stringify( json ); // return JSON string
    }
    }
    } );
     */



}