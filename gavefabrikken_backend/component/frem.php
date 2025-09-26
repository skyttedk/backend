<?php

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");




class queue{
    private $db;
    public function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }

    public function createQueueShopItems()
    {
       /*
        $sql = "SELECT max(job_id) as job FROM rm_shop_data";
        $rs =  $this->db->get($sql);
      echo   $jobID = sizeof($rs["data"]) == 0 ? 0 : $rs["data"][0]["job"]*1+1;

        $sql = "SELECT present_model.`model_present_no`,shop_id FROM `shop_present` 
                left join present_model on present_model.present_id = shop_present.present_id
                where `shop_id` in (SELECT shop_id FROM `cardshop_settings` WHERE `language_code` = 5) &&
                present_model.language_id = 1";
        $rs =  $this->db->get($sql);

        foreach ($rs["data"] as $ele){
            $sql = "INSERT INTO `rm_shop_data` (`job_id`,`item_nr`, `shop_id`) VALUES ( ".$jobID.", ' ".$ele["model_present_no"]."',".$ele["shop_id"]." )";
            $this->db->set($sql);
        }
       */
    }



    public function queueShopItems()
    {
        $sql = "select distinct max(job_id) as jobID from rm_shop_data";
        $rs =  $this->db->get($sql);
        if(sizeof($rs["data"]) == 0) return;
        $jobID = $rs["data"][0]["jobID"];

    echo    $sql ="SELECT * FROM `rm_shop_data` WHERE `job_id` = ".$jobID." and done = 0 limit 3";
    echo "<br>";
        $rs =  $this->db->get($sql);
        if(sizeof($rs["data"]) == 0) die("done no job");
        $item = new ItemNumber;
        foreach ($rs["data"] as $ele){
            $res = $item->searchItemNr(trim($ele["item_nr"]),trim($ele["shop_id"]));

            if(sizeof($res["data"]) == 0){
            echo    $sql = "UPDATE `rm_shop_data` SET `done` = '1' WHERE `rm_shop_data`.`id` = ".$ele["id"];
                echo "<br>";
            } else {
                $res = $res["data"][0];
              echo  $sql = "UPDATE `rm_shop_data` SET `done` = '1',`forecast` = '".$res["forcast"]["forecast"]."', `data` = '".json_encode($res)."' WHERE `rm_shop_data`.`id` = ".$ele["id"];
                echo "<br>";
            }
            //echo $sql;
            json_encode($this->db->set($sql));
        }

        echo "done";
    }


    public function run(){

        $sql = "SELECT id,item_nr FROM `rm_data` where done = 0 and job_id in (SELECT job_id FROM `rm_job` where done = 0 ORDER BY `id` )  ORDER BY `id` limit 6";
        $rs =  $this->db->get($sql);
        // hvis antal er = 0 alle varenr i et job er tjekket og alle jobs sættes som udført
        if(count($rs["data"]) == 0){
          echo  $sql ="UPDATE `rm_job` set `done` = 1";
            $this->db->set($sql);
            return;
        }

        foreach($rs["data"] as $queueElement){

            $id = $queueElement["id"];
            $itemNr = $queueElement["item_nr"];


            $item = new ItemNumber;
            $res = $item->searchItemNr($itemNr);

            $sql = "SELECT * FROM `navision_bomitem` WHERE `no` LIKE '".$itemNr."'";
            $RSbomitem =  $this->db->get($sql);


            $newArray = [];
            foreach ($RSbomitem['data'] as $ele) {
                $key = $ele['parent_item_no'];
                $value = $ele['quantity_per'];
                $newArray[$key] = $value;
            }
            $RSbomitem = $newArray;

            if(is_array($res)) {

                      for($i=0;sizeof($res["data"]) > $i ;$i++ ){
                            if (strpos(strtolower($res["data"][$i]["model_present_no"]), 'sam') !== false) {
                              $multiplier = isset($RSbomitem[$res["data"][$i]['model_present_no']]) ? $RSbomitem[$res["data"][$i]['model_present_no']] : 1;
                              $res["data"][$i]["antal"] = $res["data"][$i]["antal"]* $multiplier;
                              $res["data"][$i]["quantity"] = $res["data"][$i]["quantity"]* $multiplier;
                              $res["data"][$i]["forcast"]["antal"] = $res["data"][$i]["forcast"]["antal"] * $multiplier;
                              $res["data"][$i]["forcast"]["forecast"] = $res["data"][$i]["forcast"]["forecast"] * $multiplier;
                              $res["data"][$i]["forcast"]["totalSelected"] = $res["data"][$i]["forcast"]["totalSelected"] * $multiplier;
                              $res["data"][$i]["forcast"]["totalPresent"] = $res["data"][$i]["forcast"]["totalPresent"] * $multiplier;
                          }
                      }
                $anaylize = $item->analyzeExceedance($res);
                $json = ""; //json_encode($anaylize);



                $sql = "UPDATE `rm_data` SET
                     `data` = '".$json."', 
                     `forecast` = ".$anaylize["forecast"].", 
                     `reserved` = ".$anaylize["reserved"].", 
                     `selected` = ".$anaylize["selected"].", 
                     `done` = '1', 
                     `is_exceeded` = ".$anaylize["isExceededOrder"].",
                     `is_exceeded_forecast` = ".$anaylize["isExceededForecast"].",
                     model_name = '".base64_encode($anaylize["model_name"])."',
                     model_no = '".base64_encode($anaylize["model_no"])."'
                     WHERE `rm_data`.`id` = ".$id;
                $this->db->set($sql);

            } else {
                $sql = "UPDATE `rm_data` SET 
                     `data` = '{}', 
                     `done` = '1', 
                     WHERE `rm_data`.`id` = ".$id;
                $this->db->set($sql);
            }
        }
        echo date("h:i:sa");
    }
}

class job{
    private $db;
    public function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }
    public function createJob(){

        $sql = "SELECT job_id FROM `rm_job` ORDER BY `rm_job`.`job_id` DESC  limit 1";
        $rs =  $this->db->get($sql);
        $jobID = ($rs["data"][0]["job_id"])*1+1;
        // update job
        $sql = "INSERT INTO `rm_job` ( job_id) VALUES (".$jobID.")";
        $this->db->set($sql);

        $item = new ItemNumber;
        $res = $item->getAllItemNr();
        foreach ($res["data"] as $itemnr){
            $sql = "INSERT INTO `rm_data` ( `job_id`, `item_nr`) VALUES (".$jobID.", '".$itemnr["model_present_no"]."') ";
            $this->db->set($sql);
        }
        echo "done";
    }
}


class ItemNumber
{
    private $db;
    public function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }
    public function analyzeExceedance($data){

        //array("itemnr"=>"","forecast"=>0,"reserved"=>0,"selected" =>0);
        $sumSelectede = 0;
        $sumForecast = 0;
        $sumReserved = 0;
        $model_present_no = "";
        $model_name = "";
        $model_no = "";


        $first = true;
        if(count($data) > 0 ) {
            foreach ($data["data"] as $item) {
                if($first == true){
                    $model_present_no = $item["model_present_no"];
                    $model_name = $item["model_name"];
                    $model_no = $item["model_no"];
                    $first = false;
                }



                if($item["forcast"]["closed"] == 0){
                    $sumReserved+= empty($item["quantity"]) ? 0 : $item["quantity"];
                    $sumForecast+= empty($item["forcast"]["forecast"]) ? 0 : $item["forcast"]["forecast"];
                    $sumSelectede+= empty($item["antal"]) ? 0 : $item["antal"];
                }
            }
        } else {
            return [];
        }
        $isExceededForecast = $sumReserved < $sumForecast ? 1 : 0;
        $isExceededOrder  = $sumReserved < $sumSelectede ? 1 : 0;
        return array("itemnr"=>$model_present_no,
            "forecast"=>$sumForecast,
            "reserved"=>$sumReserved,
            "selected" =>$sumSelectede,
            "model_name" =>$model_name,
            "model_no" =>$model_no,
            "isExceededForecast"=>$isExceededForecast,
            "isExceededOrder" => $isExceededOrder,

        );
    }



    public function getAllItemNr(){




         $sql = "SELECT 
             
                present_model.model_present_no 
             
                FROM `shop_present`
                left join `present_model` on  `shop_present`.`present_id` = present_model.present_id
                left join `order` on `present_model`.`model_id` =  `order`.`present_model_id`
                left join `present_reservation` on `present_model`.`model_id` = `present_reservation`.model_id
                left join shop on `order`.`shop_id` = shop.id
                left join company on `order`.`company_id` = company.id
                INNER JOIN ( 
                    SELECT shop.id as shop_id,order_count,shopuser_count FROM `shop` inner join 
                        (SELECT shop_id, COUNT(DISTINCT `order`.`id`) AS order_count FROM `order` GROUP BY shop_id) order_c on shop.id = order_c.shop_id 
                        inner join (SELECT shop_id,COUNT(*) as shopuser_count FROM `shop_user` WHERE `is_demo` = 0 AND `blocked` = 0 AND `shutdown` = 0 GROUP BY shop_id) shopuser_c on shop.id = shopuser_c.shop_id where final_finished = 0 and shopuser_count > 30  HAVING (order_count/shopuser_count)*100 > 10 ) minus on shop_present.shop_id = minus.shop_id
            
                where
                    present_model.language_id = 1
                    and
                    shop.name IS NOT NULL
                  and 
                    final_finished = 0
        and
        present_model.model_present_no not LIKE '%***%'
                GROUP BY
                   present_model.model_present_no; ";
        return  $this->db->get($sql);

    }


    public function searchItemNr($itemNr,$shopID = ""){
        $shopSql = "";
        if($shopID != ""){
            $shopSql = " and shop_present.shop_id = ".$shopID ." ";
        }

        $sql = "SELECT shop_present.id,
                    shop_present.shop_id,
                    company.name,
                    `order`.`shop_is_gift_certificate`,
                    shop.name as shop_name,
                    present_model.model_present_no, 
                    present_model.model_name,
                    present_model.model_no,
                     COUNT(DISTINCT `order`.`id`) AS antal,
              
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
			        present_model.model_present_no in	(SELECT parent_item_no FROM `navision_bomitem` where language_id = 1 and `no` = '".$itemNr."'  and `deleted` IS NULL) )
  				and
                    present_model.language_id = 1
                and
                    shop.shop_mode = 1 
                and
                    shop.name IS NOT NULL
                and
                    final_finished = 0
                " .$shopSql. "
                GROUP BY
                   `order`.`present_model_id`    
              
               order by `order`.`shop_is_gift_certificate`
                   ";

        $rs =  $this->db->get($sql);




        for($i=0;count($rs["data"])>$i;$i++){

            $shopID = $rs["data"][$i]["shop_id"];
            $itemID = $rs["data"][$i]["model_present_no"];
            // tjekker om der er nok valgte mere end 30

            $forcast = new CalcForecast($shopID, $itemID);
            $res = $forcast->calcPercentage();
            $rs["data"][$i]["forcast"] = $res;
        }
        return $rs;

    }



}




class CalcForecast
{
    private $cast;
    private $targetPresentNO;
    private $shopID;
    private $shopList;
    private $totalPresent;
    private $totalSelected;
    private $correctionNumber;

    public function __construct($shopID,$presentNO) {

        $this->targetPresentNO = $presentNO;
        $this->shopID = $shopID;

        $this->cast = new Forecast($this->shopID,$this->targetPresentNO );
        if($this->cast == false){
            return false;
        }

      ///  print_R($this->cast);


        $this->shopList = $this->cast->getOrderOnItem();  // tager højde for id
        $this->correctionNumber =  $this->cast->correctionNumber(); // alle gaver valg før gaven kom i shoppen
        $this->totalPresent = $this->cast->getTotalPresent();  // tager alle gaver
        $this->totalSelected = $this->cast->getTotalOrder();  // tager højde for id

    }

    public function calcPercentage(){
        $returnVal = [];

        foreach ($this->shopList["data"] as $shop){

            // print_R($shop);
            if($shop["model_present_no"] == $this->targetPresentNO ) {
                $returnVal["closed"] = 0;
                $returnVal["model_id"] = $shop["model_id"];
                $returnVal["model_present_no"] = $shop["model_present_no"];
                $returnVal["antal"] = $shop["antal"];
                $antal = $shop["antal"] * 1;
                if ($antal * 1 > 0) {
                    $returnVal["percentage"] = round($antal / ($this->totalSelected * 1) * 100);
                } else {

                    $returnVal["percentage"] = 0;
                }
                $returnVal["totalPercentageSelected"] = round(($this->totalSelected / $this->totalPresent)*100);

                if($shop["present_model_active"] == 1 || $shop["shop_present_is_deleted"] == 1 || $shop["shop_present_active"] == 0 ){
                    $returnVal["forecast"] = $returnVal["antal"];

                    $returnVal["closed"] = 1;
                } else {
                    if($returnVal["totalPercentageSelected"] >= 5){

                        $returnVal["forecast"] = round((($this->totalPresent) - ($this->correctionNumber*1))*($returnVal["percentage"]/100));
                        if($returnVal["forecast"]*1 < $antal*1){
                            $returnVal["forecast"] = $antal;
                        }

                    } else {
                        $returnVal["forecast"] = $returnVal["antal"] ;
                    }

                }

                $returnVal["totalSelected"] = $this->totalSelected ;
                $returnVal["totalPresent"] = $this->totalPresent ;



            }
        }
        return $returnVal;
    }


}





class Forecast
{
    private $db;

    private $shopID;
    private $presentNO;
    private $firstOrderID;


    public function __construct($shopID,$presentNO) {
        $this->presentNO = $presentNO;
        $this->shopID = $shopID;
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
        if($this->setFirstOrderID() == false) return;

    }
    public function getFirstOrderID(){
        return $this->firstOrderID;
    }
    public function setFirstOrderID()
    {
        $sql = "SELECT id  FROM `order` WHERE `present_model_id` in(SELECT `model_id` FROM `present_model` WHERE `present_id` in ( SELECT `present_id`  FROM `shop_present` WHERE `shop_id` = " . $this->shopID . ")
        and `language_id` = 1
        and present_model.model_present_no = '" . $this->presentNO . "') ORDER BY `order`.`id`   ASC LIMIT 1";
        $rs =  $this->db->get($sql);
        $this->firstOrderID = ($rs["data"][0]["id"]*1)-1;


    }

    public function getTotalOrder()
    {
        $sql = "SELECT 
    
        COUNT(DISTINCT `order`.`id`) AS antal from `order` 
    inner join shop_user on shop_user.id = `order`.`shopuser_id`
    WHERE  
        `order`.id > " . $this->firstOrderID . " AND
        shop_user.is_demo = 0 AND
        shop_user.blocked = 0 AND
        shop_user.shop_id = " . $this->shopID . " and
        shop_user.shutdown = 0";
        $rs =  $this->db->get($sql);
        return $rs["data"][0]["antal"];
    }
    public function correctionNumber()
    {
        $sql = "SELECT COUNT(DISTINCT `order`.`id`) AS antal from `order` 
    inner join shop_user on shop_user.id = `order`.`shopuser_id`
    WHERE  
        `order`.id < " . $this->firstOrderID . " AND
        shop_user.is_demo = 0 AND
        shop_user.blocked = 0 AND
        shop_user.shop_id = " . $this->shopID . " and
        shop_user.shutdown = 0";
        $rs =  $this->db->get($sql);
        return $rs["data"][0]["antal"];

    }



    public function getOrderOnItem()
    {
        $sql = "SELECT `model_id`,model_present_no,  COUNT(DISTINCT `order`.`id`) AS antal ,
       shop_present.is_deleted as shop_present_is_deleted,  
       shop_present.active as shop_present_active,
       present_model.active as present_model_active
       FROM `present_model` 
inner JOIN shop_present on present_model.present_id = shop_present.present_id 
Inner join `order` on `order`.`present_model_id` = present_model.model_id
inner join shop_user on shop_user.id = `order`.`shopuser_id`
                                                                                       
WHERE  shop_present.`shop_id` = " . $this->shopID . "
    and present_model.`language_id` = 1 AND
    `order`.id > " . $this->firstOrderID . " AND
    shop_user.is_demo = 0 AND
    shop_user.blocked = 0 AND
    shop_user.shop_id = " . $this->shopID . " and
    shop_user.shutdown = 0  
GROUP BY `order`.`present_model_id`";

        return $this->db->get($sql);
    }




    public function getTotalPresent()
    {
        $sql = "SELECT COUNT(id) as antal FROM `shop_user` WHERE 
    `shop_id` = " . $this->shopID . " and 
    `blocked` = 0 and 
    `shutdown` = 0";
        $rs =  $this->db->get($sql);
        return $rs["data"][0]["antal"];
    }




}
