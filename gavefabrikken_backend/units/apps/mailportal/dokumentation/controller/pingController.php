<?php

Class pingController Extends baseController {

    public function index() {
        $ping = array("status"=>"1","data"=>"ping");
        echo json_encode($ping);
    }
    public function saveInPresentLog(){
        $presentId = $_POST["presentId"];
        $data = $_POST["logData"];
       Dbsqli::setSql2("INSERT INTO present_log (present_id,log) VALUES (".$presentId.", '".$data."')");

    }








}

?>
