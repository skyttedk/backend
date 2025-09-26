<?php
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class earlypresentController Extends baseController {
  public function Index() {
      $this->registry->template->show('earlyPresent_view');
  }
  public function create() {
    $earlyPresent = EarlyPresent::createEarlyPresent ($_POST);
    response::success(make_json("early", $earlyPresent));
  }
  public function read() {
    $earlyPresent = EarlyPresent::all(array('conditions' => array(" active= 1 order by language")));
    $options = array();
    response::success(make_json("early", $earlyPresent, $options));
  }
  public function update() {
    $earlyPresent = EarlyPresent::updateEarlyPresent ($_POST);
    response::success(make_json("early", $earlyPresent));
  }
  public function delete() {
    $earlyPresent = EarlyPresent::deleteEarlyPresent ($_POST['id']);
    response::success(make_json("early", $earlyPresent));
  }
  //Create Variations of readAll
  public function readAll() {
    //$medias = media::all(array('order' => 'id desc'));
    //$options = array();
    //response::success(make_json("medias", $medias, $options));
  }
//---------------------------------------------------------------------------------------
// Custom Controller Actions
//---------------------------------------------------------------------------------------



}

?>
