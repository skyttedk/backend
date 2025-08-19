<?php
include "model/admin.model.php";
class adminController
{
    public function go()
    {
       $res = array(
            'status' => 1,
            'msg' => "virker");
       return json_encode($res);
    }
    public function checkToken(){
         return admin::checkToken($_POST);
    }

    public function readAll()
    {

        return admin::readAll();
    }
    public function create()
    {
        if($_POST["id"] == null){
            return admin::create($_POST);
        } else {
            $this->update();
        }
    }
    public function updateStatus()
    {
        return admin::updateStatus($_POST);
    }
    public function update()
    {
        return admin::update($_POST);
    }
    public function deleteElement()
    {
        return admin::deleteElement($_POST);
    }

    public function getAdmin()
    {
        return admin::getAdmin($_POST);
    }



}


?>