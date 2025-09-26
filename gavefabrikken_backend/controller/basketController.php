<?php

class basketController Extends baseController
{

    public function index() {

    }
    public function read(){
        $userID = $_POST["userID"];
        $companyPresent = presentModel::find_by_sql("SELECT * FROM `present_model` WHERE `model_id` in ( SELECT `present_model_id` FROM `order` WHERE `shopuser_id` = $userID ) and `language_id` = 1 order by model_id");
        $userPresent = presentModel::find_by_sql("SELECT * FROM `shop_user` inner JOIN `order` on shop_user.id = `order`.`shopuser_id` inner join `present_model` on `order`.`present_model_id` = present_model.model_id WHERE `basket_id` = $userID and `shop_user`.blocked = 0 and present_model.language_id = 1");

        $returnData = [
          "companyPresent" => $companyPresent,
          "userPresent" => $userPresent
        ];
        response::success(json_encode($returnData));
    }
    public function remove(){
        $userID = $_POST["userID"];

        $result = Dbsqli::setSql2("update shop_user set blocked = 1 where id = ".$userID);

        response::success(json_encode($result));

    }






}