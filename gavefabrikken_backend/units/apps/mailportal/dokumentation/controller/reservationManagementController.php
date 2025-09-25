<?php

include($_SERVER["DOCUMENT_ROOT"]."/gavefabrikken_backend/component/frem.php");


Class reservationManagementController Extends baseController {
    public function index() {

    }
    public function searchItemNr(){

        $itemNr = $_POST["itemNr"];
        $sql = "SELECT shop_present.id,
                    shop_present.shop_id,
                    company.name,
                    `order`.`shop_is_gift_certificate`,
                    shop.name as shop_name,
                    present_model.model_present_no, 
                    present_model.model_name,
                    present_model.model_no,
                    COUNT( `order`.`present_model_id`) as antal,
                    `present_reservation`.id as pr_id,
                    `present_reservation`.quantity,
                    present_model.is_deleted,
                    shop.end_date
                FROM `shop_present`
                left join `present_model` on  `shop_present`.`present_id` = present_model.present_id
                left join `order` on `present_model`.`model_id` =  `order`.`present_model_id`
                left join `present_reservation` on `present_model`.`model_id` = `present_reservation`.model_id
                left join shop on `order`.`shop_id` = shop.id
                left join company on `order`.`company_id` = company.id
                where
                	( present_model.model_present_no = '".$itemNr."' or 
			        present_model.model_present_no in	(SELECT parent_item_no FROM `navision_bomitem` where `no` = '".$itemNr."'  and `deleted` IS NULL) )
  				and
                    present_model.language_id = 1
                and
                    shop.name IS NOT NULL
                and 
                    shop.final_finished = 0    
                    
                GROUP BY
                   `order`.`present_model_id`  order by `order`.`shop_is_gift_certificate`  
                   
                   ";

                $rs =  ShopPresent::find_by_sql($sql);

                for($i=0;count($rs)>$i;$i++){

                    $shopID = $rs[$i]->attributes["shop_id"];
                    $itemID = $rs[$i]->attributes["model_present_no"];
                    $forcast = new CalcForecast($shopID,$itemID);
                    $res = $forcast->calcPercentage();
                    $rs[$i]->attributes["forcast"] = $res;
                }

                /*
                foreach ($rs as $present){
                    echo $present->attributes["shop_id"]."--".$present->attributes["model_present_no"];
                    $forcast = new CalcForecast($present->attributes["shop_id"],$present->attributes["model_present_no"]);
                    $res = $forcast->calcPercentage();
                    print_R($res);

                }
*/



                //$forcast = new CalcForecast(55,"200134");
                //$res = $forcast->calcPercentage();
                //print_R($res);

                response::success(json_encode($rs));


    }

    public function updateQuantity(){
        $id = $_POST["id"];
        $PresentReservation = PresentReservation::find($id);
        $PresentReservation->update_attributes($_POST);
        $PresentReservation->save();
        response::success(json_encode($PresentReservation));
    }
    public function getGlobalItemNrStatus(){
        $sql ="SELECT * FROM `rm_job` where `done` = 1 ORDER BY `rm_job`.`id`  DESC limit 1";
        $rs =  ShopPresent::find_by_sql($sql);
        $jobID =  $rs[0]->id;
        $sql ="SELECT * FROM `rm_data` where job_id = ".$jobID." and (is_exceeded = 1 or is_exceeded_forecast = 1) order by id ";
        $rs =  ShopPresent::find_by_sql($sql);
        response::success(json_encode($rs));
    }
    public function jobStatus(){
        $sql ="SELECT *,count(*) as c FROM `rm_job` where `done` = 0 ";
        $rsJob =  ShopPresent::find_by_sql($sql);
        $sql ="select count(*) as c FROM `rm_data` where `done` = 0 ";
        $rsData =  ShopPresent::find_by_sql($sql);
        $returnData = array("job"=>$rsJob,"data"=>$rsData);
        response::success(json_encode($returnData));
    }





    public function searchItemNr_old(){

        $sql = "SELECT shop_present.id,
                    shop_present.shop_id,
                    cardshop_settings.concept_code, 
                    present_model.model_present_no, 
                    present_model.model_name,
                    present_model.model_no,
                    COUNT( `order`.`present_model_id`) as antal,
                    `present_reservation`.id as pr_id,
                    `present_reservation`.quantity,
                    present_model.is_deleted
                FROM `shop_present`
                inner join cardshop_settings on  `shop_present`.`shop_id` = cardshop_settings.shop_id
                left join `present_model` on  `shop_present`.`present_id` = present_model.present_id
                left join `order` on `present_model`.`model_id` =  `order`.`present_model_id`
                left join `present_reservation` on `present_model`.`model_id` = `present_reservation`.model_id
                left join (SELECT parent_item_no FROM `navision_bomitem` where )
                where 
                    cardshop_settings.language_code = 1 and
                    present_model.language_id = 1
                GROUP BY
                    `order`.`present_model_id,present_model.model_present_no`";
        $rs =  ShopPresent::find_by_sql($sql);
        response::success(json_encode($rs));


    }
    public function getCardshop(){

        $sql = "SELECT shop_present.id,
                    shop_present.shop_id,
                    cardshop_settings.concept_code, 
                    present_model.model_present_no, 
                    present_model.model_name,
                    present_model.model_no,
                    COUNT( `order`.`present_model_id`) as antal,
                    `present_reservation`.id as pr_id,
                    `present_reservation`.quantity,
                    present_model.is_deleted
                FROM `shop_present`
                inner join cardshop_settings on  `shop_present`.`shop_id` = cardshop_settings.shop_id
                left join `present_model` on  `shop_present`.`present_id` = present_model.present_id
                left join `order` on `present_model`.`model_id` =  `order`.`present_model_id`
                left join `present_reservation` on `present_model`.`model_id` = `present_reservation`.model_id 
                where 
                    cardshop_settings.language_code = 1 and
                    present_model.language_id = 1
                GROUP BY
                    `order`.`present_model_id` order by shop_present.shop_id";

        $rs =  ShopPresent::find_by_sql($sql);
        response::success(json_encode($rs));


    }

    public function getValgshop(){
        $sql = "SELECT shop_present.id,
                    shop_present.shop_id,
                    shop.name as concept_code,
                    present_model.model_present_no, 
                    present_model.model_name,
                    present_model.model_no,
                    COUNT( `order`.`present_model_id`) as antal,
                    `present_reservation`.id as pr_id,
                    `present_reservation`.quantity,
                    present_model.is_deleted
                FROM `shop_present`
              
                left join `present_model` on  `shop_present`.`present_id` = present_model.present_id
                left join `order` on `present_model`.`model_id` =  `order`.`present_model_id`
                left join `present_reservation` on `present_model`.`model_id` = `present_reservation`.model_id 
                left join shop on `order`.`shop_id` = shop.id
                where 
                    
                    present_model.language_id = 1 and
                    shop_present.shop_id not in (SELECT shop_id FROM cardshop_settings)
                GROUP BY
                    `order`.`present_model_id` order by shop_present.shop_id";

        $rs =  ShopPresent::find_by_sql($sql);
        response::success(json_encode($rs));
    }
    public function getExceeded_old2(){

        $sql = "  SELECT shop_present.id,
                    present_model.model_present_no, 
                    present_model.model_name,
                    present_model.model_no,
                    COUNT( `order`.`present_model_id`) as antal,
                    `present_reservation`.id as pr_id,
                    `present_reservation`.quantity,
                    present_model.is_deleted
                FROM `shop_present`
              
                left join `present_model` on  `shop_present`.`present_id` = present_model.present_id
                left join `order` on `present_model`.`model_id` =  `order`.`present_model_id`
                left join `present_reservation` on `present_model`.`model_id` = `present_reservation`.model_id 
               
                where 
                    
                    present_model.language_id = 1 
               
                GROUP BY
                    present_model.model_present_no
                      HAVING  quantity < antal
                      order by shop_present.shop_id";
        $rs =  ShopPresent::find_by_sql($sql);
        response::success(json_encode($rs));

    }




    public function getExceeded(){
        $sql = " select  distinct(aa.model_present_no),model_name,model_no,is_deleted from ( SELECT shop_present.id,
                    shop_present.shop_id,
                    shop.name as concept_code,
                    present_model.model_present_no, 
                    present_model.model_name,
                    present_model.model_no,
                    COUNT( `order`.`present_model_id`) as antal,
                    `present_reservation`.id as pr_id,
                    `present_reservation`.quantity,
                    present_model.is_deleted
                FROM `shop_present`
              
                left join `present_model` on  `shop_present`.`present_id` = present_model.present_id
                left join `order` on `present_model`.`model_id` =  `order`.`present_model_id`
                left join `present_reservation` on `present_model`.`model_id` = `present_reservation`.model_id 
                left join shop on `order`.`shop_id` = shop.id
                where 
                    
                    present_model.language_id = 1 and
                    final_finished = 0
               
                GROUP BY
                    `order`.`present_model_id` 
                      HAVING  quantity < antal
                      order by shop_present.shop_id ) aa
                      
                      group by aa.model_present_no
                      
                      ";
                  $rs =  ShopPresent::find_by_sql($sql);
            response::success(json_encode($rs));
    }






}
