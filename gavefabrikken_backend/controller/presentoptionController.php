<?php
// Controller Order
// Date created  Thu, 26 May 2016 19:44:28 +0200
// Created by Bitworks
class presentOptionController Extends baseController
{

    public function Index()
    {

        echo "hej ";
    }
    public function godkendelse(){
        $options = array('present_id' => $_POST['present_id'],'option_name' =>'approval');
        $PresentOption = PresentOption::find('all', $options);

        // create new approval option
        if(sizeofgf($PresentOption)==0){
            echo "new";
            $PresentOption = new PresentOption;
            $PresentOption->present_id =  $_POST['present_id'];
            $PresentOption->option_name = 'approval';
            $PresentOption->option_value = $_POST['option_value'];
            $PresentOption->save();
       } else {
            $sql = "update present_options set option_value = '".$_POST['option_value']."' where present_id= ".$_POST['present_id']." and  option_name = 'approval'";
            $rs = Dbsqli::setSql2($sql);
        }
        response::success(make_json("result", []));

    }
    public function getGodkendelse(){
        $options = array('present_id' => $_POST['present_id'],'option_name' =>'approval');
        $PresentOption = PresentOption::find('all', $options);
        response::success(make_json("result", $PresentOption));
    }
}

