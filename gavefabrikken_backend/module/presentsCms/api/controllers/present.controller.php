<?php
include "model/present.model.php";
class presentController
{
    public function go()
    {
       $dummy = array();
       return json_encode($dummy);
    }
    public function getAll(){
       
         return present::readAll($_POST);
    }
    public function addPresent(){
        return present::addPresent($_POST);
    }

    public function getAllItemnr(){
         return present::getAllItemnr();
    }
    public function getItemnr(){
         return present::getItemnr($_POST);
    }
    public function getByLetterGroup(){
        return present::getByLetterGroup($_POST);
    }
    public function getById(){
        return present::getById($_POST);
    }
    public function freeTextSearch(){
       //  return present::searchItemNumber($_POST);
        return present::freeTextSearch($_POST);
    }
    public function itemnrSearchOnCountry(){
        return present::itemnrSearchOnCountry($_POST);
    }
    public function getRange(){
        return present::getRange($_POST);
    }
    public function getNavPrice(){
        return present::getNavPrice($_POST);
    }
    public function doDelete(){
        return present::doDelete($_POST);
    }
    public function create(){
       if($_POST["id"] == 0){
           return present::create($_POST);
       } else {
           return present::update($_POST);
       }

    }


}
?>