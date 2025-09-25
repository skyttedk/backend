<?php
include "model/presentSetting.model.php";
class presentSettingController
{
    /*
    public function getAll(){
         $option = isset($_POST["option"]) ? $_POST["option"] : "";
         return present::readAll($option);
    }
    */
    public function load(){
        return presentSetting::load($_POST);
    }
    public function update(){
        return presentSetting::update($_POST);
    }
    public function loadShowPrice(){
        return presentSetting::loadPriceShow($_POST);
    }
    public function updateShowPrice(){
        return presentSetting::updatePriceShow($_POST);
    }
}

?>
