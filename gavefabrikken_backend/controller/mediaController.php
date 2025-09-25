<?php
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class MediaController Extends baseController {
  public function Index() {
  }
  public function create() {
     $media = media::createMedia ($_POST);
     response::success(make_json("media", $media));
  }
  public function read() {
    $media = media::readMedia ($_POST['id']);
    response::success(make_json("media", $media));
  }
  public function update() {
    $media = media::updateMedia ($_POST);
    response::success(make_json("media", $media));
  }
  public function delete() {
    $media = media::deleteMedia ($_POST['id']);
    response::success(make_json("media", $media));
  }
  //Create Variations of readAll
  public function readAll() {
    $medias = media::all(array('order' => 'id desc'));
    $options = array();
    response::success(make_json("medias", $medias, $options));
  }
//---------------------------------------------------------------------------------------
// Custom Controller Actions
//---------------------------------------------------------------------------------------
    public function getAllOnType(){
        $type = $_POST["type"];
         Dbsqli::getSql("select * from media where type=".$type);

    }


}


?>

