<?php
class shopPresentRulesController Extends baseController {

    public function Index() {
         $this->registry->template->show('shopPresentRules_view');
        /*
        $all = shop_present_rules::find('all');
        response::success(json_encode($all));
        */
    }
    public function company(){
      $all = shop_present_company_rules::find('all');
      response::success(json_encode($all));
    }
    public function showAllRules(){
        if($this->verifyToken($_POST["token"])){
    $sql = "select
	    shop_present_company_rules.company_id,
     	shop_present_company_rules.present_id,
    	shop_present_company_rules.model_id,
    	shop_present_company_rules.rules,
    	company.name,
    	company.cvr,
    	company.ship_to_company,
    	company.ship_to_address,
	    company.ship_to_postal_code,
    	company.contact_name,
    	company.ship_to_city,
    	present_model.model_name,
    	present_model.model_no
    from shop_present_company_rules
        inner join company on shop_present_company_rules.company_id = company.id
        inner join present_model on
        	shop_present_company_rules.present_id = present_model.present_id and
        	shop_present_company_rules.model_id = present_model.model_id
    where
        present_model.language_id = 1

    order by
    	shop_present_company_rules.company_id,
        shop_present_company_rules.rules,
        shop_present_company_rules.present_id,
    	shop_present_company_rules.model_id ";
               $res = shop_present_company_rules::find_by_sql($sql);
               response::success(json_encode($res));
          }
    }




    public function getCardList(){
          if($this->verifyToken($_POST["token"])){
               $sql = "select shop_user.id,shop_user.shop_id, shop_user.username, shop.name from shop_user
               inner join shop on shop_user.shop_id = shop.id
               where shop_user.company_id = ".$_POST["company_id"]." and shop_user.blocked = 0 order by shop_user.shop_id, shop_user.username";
               $res = shopUser::find_by_sql($sql);
               response::success(json_encode($res));
          }
    }

    public function updateRulesV2(){
    if($this->verifyToken($_POST["token"])){
      if($_POST["action"] == 0){
          Dbsqli::setSql2("delete from shop_present_company_rules where company_id = ".$_POST["company_id"]." and present_id = ".$_POST["present_id"]." and model_id = ".$_POST["model_id"]);
          $dummy[] = [];
          response::success(json_encode($dummy));
      } else {
          $sql = "delete from shop_present_company_rules where company_id = ".$_POST["company_id"]." and present_id = ".$_POST["present_id"]." and model_id = ".$_POST["model_id"];
          Dbsqli::setSql2($sql);
          $sql_set = "insert into shop_present_company_rules (company_id,present_id,model_id,rules) values (".$_POST["company_id"].",".$_POST["present_id"].",".$_POST["model_id"].",".$_POST["action"].")";
          Dbsqli::setSql2($sql_set);
          $dummy[] = [];
          response::success(json_encode($dummy));
      }
    }
    }





    public function updateRules(){
    if($this->verifyToken($_POST["token"])){
      if($_POST["action"] == "remove"){
          Dbsqli::setSql2("delete from shop_present_company_rules where company_id = ".$_POST["company_id"]." and present_id = ".$_POST["present_id"]." and model_id = ".$_POST["model_id"]);
          $dummy[] = [];
          response::success(json_encode($dummy));
      } else {
          echo $sql = "delete from shop_present_company_rules where company_id = ".$_POST["company_id"]." and present_id = ".$_POST["present_id"]." and model_id = ".$_POST["model_id"];
          Dbsqli::setSql2($sql);
          echo $sql_set = "insert into shop_present_company_rules (company_id,present_id,model_id,rules) values (".$_POST["company_id"].",".$_POST["present_id"].",".$_POST["model_id"].",1)";
          Dbsqli::setSql2($sql_set);
          $dummy[] = [];
          response::success(json_encode($dummy));
      }
    }
    }
    public function getRules()
    {
      if($this->verifyToken($_POST["token"])){
        $res = shop_present_company_rules::find_by_sql("select * from  shop_present_company_rules where company_id = ".$_POST["company_id"]);
        response::success(json_encode($res));
      }
    }

    public function getPresentListOnShop(){
          $shop_id = $_POST["shop_id"];
          if($this->verifyToken($_POST["token"])){
               $sql = "SELECT present_model.model_id,present_model.present_id, model_name,present_model.model_no,present_model.media_path , shop.name FROM `present_model`
                    inner JOIN present on present_model.present_id = present.id
                    inner join shop on  present.shop_id = shop.id
                    WHERE shop_id = ".$shop_id." and  present_model.language_id = 1 order by present.shop_id, present_model.present_id, present_model.model_id";
                  $res = PresentModel::find_by_sql($sql);
                   response::success(json_encode($res));
          }
    }



    public function getPresentList(){
          if($this->verifyToken($_POST["token"])){
               $sql = "SELECT present_model.model_id,present_model.present_id,model_present_no, model_name,present_model.model_no,present_model.media_path , shop.alias FROM `present_model`
                    inner JOIN present on present_model.present_id = present.id
                    inner join shop on  present.shop_id = shop.id
                    WHERE shop_id in ( SELECT DISTINCT shop_id from shop_user where company_id = ".$_POST["company_id"].") and  present_model.language_id = 1 order by present.shop_id, present_model.present_id, present_model.model_id";
                  $res = PresentModel::find_by_sql($sql);
                   response::success(json_encode($res));
          }
    }
    public function getPresentListOnCartType(){

    }


    public function getRuleForAll(){
        if($this->verifyToken($_POST["token"])){
            $sql = $_POST["company_id"];
            $res = shop_present_company_rules::find_by_sql($sql);
            response::success(json_encode($res));
        }
    }


    private function verifyToken($token){
       if($token == "dsf984gh58b2i23t4g8"){
         return true;
       } else {
          $dummy = [];
          response::error(json_encode($dummy));
       }
    }



}



?>