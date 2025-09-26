<?php

class ItemProjection{

public function run(){

    $sql = "SELECT * FROM `present_model` ORDER BY `present_model`.`id` limit 100";

    $rs3 = Dbsqli::getSql2($sql);
    print_R($rs3);

}





}

?>
