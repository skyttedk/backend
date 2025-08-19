<?php
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class Media2Controller  {
  public function Index() {
  }

    public function getAllOnType(){
        $type = $_POST["type"];
         Dbsqli::getSql("select * from media where type=".$type." and active = 1");

    }


}
