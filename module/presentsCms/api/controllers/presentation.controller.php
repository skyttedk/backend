<?php
include "model/presentation.model.php";
class presentationController
{
    /*
    public function getAll(){
         $option = isset($_POST["option"]) ? $_POST["option"] : "";
         return present::readAll($option);
    }
    */
    public function getById(){
        return presentation::getById($_POST);
    }



    public function getPresentList(){
        return presentation::getPresentList($_POST);
    }
    public function getByConfig(){
        return presentation::getByConfig($_POST);
    }
    public function create(){
        return presentation::create($_POST);
    }
    public function remove(){
        return presentation::remove($_POST);
    }
    public function getAll(){
        return presentation::getAll($_POST);
    }
    public function removeById(){
        return presentation::removeById($_POST);
    }
    public function updateConfig(){
        return presentation::updateConfig($_POST);
    }
    public function getAllWithPresent(){
        return presentation::getAllWithPresent($_POST);
    }
    public function createShop(){
        return presentation::createShop($_POST);
    }
    public function updateSort(){
        return presentation::updateSort($_POST);
    }
    public function getAllArchive(){
        return presentation::getAllArchive($_POST);
    }
    public function searchAllArchive(){
        return presentation::searchAllArchive($_POST);
    }
    public function copy(){
        return presentation::copy($_POST);
    }

}

?>