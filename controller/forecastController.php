<?php
include("./service/calculateItemProjection.php");


class forecastController  {
  public function Index() {

  }

  public function test(){
    $ItemProjection = new ItemProjection();
    $ItemProjection->run();
  }
}


