<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include($_SERVER["DOCUMENT_ROOT"]."/gavefabrikken_backend/component/frem.php");
use GFCommon\Model\Navision\MagentoWS;

Class reservationManagementController Extends baseController {
    public function index() {

    }

    public function searchItemNrAuto(){
        $shopIDsql = "";
        $itemNr = $_POST["itemNr"];
        if(isset($_POST["shopID"])){
            $shopIDsql = " and shop.id=".$_POST["shopID"]." ";
        }



        $sql = "SELECT

    shop.end_date,
    shop.start_date,
    `present_reservation`.id AS pr_id,
    `present_reservation`.quantity,
    present_reservation.do_close,
    present_reservation.autotopilot,
    present_reservation.is_close,
    PM.model_present_no,
    PM.model_name,
    PM.model_no,
    PM.id as pm_id,
    `order`.`shop_is_gift_certificate`,
 COUNT(DISTINCT `order`.`id`) AS antal,
    P.shop_id,
    shop.name as shop_name,
    PM.is_deleted,
    shop_metadata.salesperson_code,
    shop_board.valgshopansvarlig,
    shop_metadata.so_no
FROM
    present AS P
JOIN present_model AS PM ON P.id = PM.present_id
LEFT JOIN `present_reservation` ON PM.`model_id` = `present_reservation`.model_id
LEFT JOIN `order` ON PM.`model_id` = `order`.`present_model_id`
LEFT JOIN shop ON P.`shop_id` = shop.id
left join shop_board on shop.id = shop_board.fk_shop
left join shop_metadata on shop.id = shop_metadata.shop_id
WHERE
    PM.language_id = 1 AND
    PM.is_deleted = 0 AND 
    P.copy_of > 0 AND 
    P.shop_id > 0 ".$shopIDsql." and
    shop.shop_mode = 1 and
     shop.final_finished = 0 AND(
        PM.model_present_no = '".$itemNr."' OR PM.model_present_no IN(
        SELECT
            parent_item_no
        FROM
            `navision_bomitem`
        WHERE
            `no` = '".$itemNr."' AND `deleted` IS NULL
    ) OR PM.model_present_no IN(
    SELECT NO
FROM
    `navision_bomitem`
WHERE
    `parent_item_no` = '".$itemNr."' AND `deleted` IS NULL
)
    ) 
GROUP BY
    P.shop_id
  
ORDER BY
    quantity;
                   ";

        $rs =  ShopPresent::find_by_sql($sql);

        for($i=0;count($rs)>$i;$i++){

            $shopID = $rs[$i]->attributes["shop_id"];
            $itemID = $rs[$i]->attributes["model_present_no"];

            if($rs[$i]->attributes["antal"] == 0 ){
                $zero = [
                    'closed' => -1,
                    'model_id' => $rs[$i]->attributes["pm_id"],
                    'model_present_no' => $itemID,
                    'antal' => 0,
                    'percentage' => "N/A",
                    'totalPercentageSelected' => "N/A",
                    'forecast' => "N/A",
                    'totalSelected' => "N/A",
                    'totalPresent' => "N/A"
                ];
                $rs[$i]->attributes["forcast"] = $zero;
            } else {
                $forcast = new CalcForecast($shopID,$itemID);
                $res = $forcast->calcPercentage();
                $rs[$i]->attributes["forcast"] = $res;
            }
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

        // get quantity_per list to calc total
        $sql = "SELECT * FROM `navision_bomitem` WHERE `no` LIKE '".$itemNr."'";
        $RSbomitem = NavisionBomItem::find_by_sql($sql);

        $sql = "SELECT * FROM `magento_stock_total` WHERE `itemno` LIKE '".$itemNr."'";
        $stockTotal = NavisionBomItem::find_by_sql($sql);
        $postData = [
            "searchData"=>$rs,
            "bomitemInfo"=>$RSbomitem,
            "stockTotal"=>$stockTotal
        ];


        response::success(json_encode($postData));


    }




    public function searchItemNr(){


/*

        $shopIDsql = "";
        $itemNr = $_POST["itemNr"];
        if(isset($_POST["shopID"])){
           $shopIDsql = " and shop.id=".$_POST["shopID"]." ";
        }



  $sql = "SELECT

    shop.end_date,
    shop.start_date,
    `present_reservation`.id AS pr_id,
    `present_reservation`.quantity,
    present_reservation.do_close,
    present_reservation.is_close,
    PM.model_present_no,
    PM.model_name,
    PM.model_no,
    PM.id as pm_id,
    shop.`is_gift_certificate` as shop_is_gift_certificate,
    COUNT(DISTINCT `order`.`id`) AS antal,
    P.shop_id,
    shop.name as shop_name,
    PM.is_deleted,
    shop_metadata.salesperson_code,
    shop_board.valgshopansvarlig,
    shop_metadata.so_no
FROM
    present AS P
JOIN present_model AS PM ON P.id = PM.present_id
LEFT JOIN `present_reservation` ON PM.`model_id` = `present_reservation`.model_id
LEFT JOIN `order` ON PM.`model_id` = `order`.`present_model_id`
LEFT JOIN shop ON P.`shop_id` = shop.id
left join shop_board on shop.id = shop_board.fk_shop
left join shop_metadata on shop.id = shop_metadata.shop_id
WHERE
    
    PM.language_id = 1 AND
    PM.is_deleted = 0 AND 
    P.copy_of > 0 AND 
    P.shop_id > 0 ".$shopIDsql." and
    shop.shop_mode = 1 and
     shop.final_finished = 0 AND(
        PM.model_present_no = '".$itemNr."' OR PM.model_present_no IN(
        SELECT
            parent_item_no
        FROM
            `navision_bomitem`
        WHERE
        navision_bomitem.language_id = 1 and  `no` = '".$itemNr."'  AND `deleted` IS NULL
    ) OR PM.model_present_no IN(
    SELECT NO
FROM
    `navision_bomitem`
WHERE
    `parent_item_no` = '".$itemNr."' AND navision_bomitem.language_id = 1 and `deleted` IS NULL
)
    ) 
GROUP BY
    P.shop_id   "; */

        $shopIDsql = "";
        $itemNr = $_POST["itemNr"];
        if(isset($_POST["shopID"])){
            $shopIDsql = " and shop.id=".$_POST["shopID"]." ";
        }

        $sql = "SELECT
    shop.end_date,
    shop.start_date,
    `present_reservation`.id AS pr_id,
    `present_reservation`.quantity,
    present_reservation.do_close,
       present_reservation.autotopilot,
    present_reservation.is_close,
    PM.model_present_no,
    PM.model_name,
    PM.model_no,
    PM.id as pm_id,
    shop.`is_gift_certificate` as shop_is_gift_certificate,
    (SELECT COUNT(DISTINCT o.id) 
     FROM `order` o 
     WHERE o.present_model_id = PM.model_id) AS antal,
    P.shop_id,
    shop.name as shop_name,
    PM.is_deleted,
    shop_metadata.salesperson_code,
    shop_board.valgshopansvarlig,
    shop_metadata.so_no
FROM
    present AS P
JOIN present_model AS PM ON P.id = PM.present_id
LEFT JOIN `present_reservation` ON PM.`model_id` = `present_reservation`.model_id
LEFT JOIN shop ON P.`shop_id` = shop.id
LEFT JOIN shop_board ON shop.id = shop_board.fk_shop
LEFT JOIN shop_metadata ON shop.id = shop_metadata.shop_id
WHERE
    PM.language_id = 1 
    AND PM.is_deleted = 0 
    AND P.copy_of > 0 
    AND P.shop_id > 0 ".$shopIDsql."
    AND shop.shop_mode = 1 
    AND shop.final_finished = 0 
    AND (
        PM.model_present_no = '".$itemNr."' 
        OR PM.model_present_no IN (
            SELECT parent_item_no
            FROM `navision_bomitem`
            WHERE navision_bomitem.language_id = 1 
            AND `no` = '".$itemNr."'  
            AND `deleted` IS NULL
        ) 
        OR PM.model_present_no IN (
            SELECT NO
            FROM `navision_bomitem`
            WHERE `parent_item_no` = '".$itemNr."' 
            AND navision_bomitem.language_id = 1 
            AND `deleted` IS NULL
        )
    ) 
GROUP BY
    P.shop_id";


        $rs =  ShopPresent::find_by_sql($sql);

        for($i=0;count($rs)>$i;$i++){

            $shopID = $rs[$i]->attributes["shop_id"];
            $itemID = $rs[$i]->attributes["model_present_no"];

            if($rs[$i]->attributes["antal"] == 0 ){
                $zero = [
                    'closed' => -1,
                    'model_id' => $rs[$i]->attributes["pm_id"],
                    'model_present_no' => $itemID,
                    'antal' => 0,
                    'percentage' => "N/A",
                    'totalPercentageSelected' => "N/A",
                    'forecast' => "N/A",
                    'totalSelected' => "N/A",
                    'totalPresent' => "N/A"
                ];
                $rs[$i]->attributes["forcast"] = $zero;
            } else {

                try {
                    $forcast = new CalcForecast($shopID,$itemID);
                    $res = $forcast->calcPercentage();
                    $rs[$i]->attributes["forcast"] = $res;
                } catch (Exception $e) {

                    $zero = [
                        'closed' => -1,
                        'model_id' => $rs[$i]->attributes["pm_id"],
                        'model_present_no' => $itemID,
                        'antal' => 0,
                        'percentage' => "N/A",
                        'totalPercentageSelected' => "N/A",
                        'forecast' => "N/A",
                        'totalSelected' => "N/A",
                        'totalPresent' => "N/A"
                    ];
                    $rs[$i]->attributes["forcast"] = $zero;

                }





            }
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



        // get quantity_per list to calc total
        $sql = "SELECT * FROM `navision_bomitem` WHERE `no` LIKE '".$itemNr."'";
        $RSbomitem = NavisionBomItem::find_by_sql($sql);

        $sql = "SELECT * FROM `magento_stock_total` WHERE `itemno` LIKE '".$itemNr."'";
        $stockTotal = NavisionBomItem::find_by_sql($sql);

        $navStock = "N/A";
        try {
            $magentoClient = new MagentoWS();
            $navStock = $magentoClient->GetAvailableInventoryByType($itemNr, 1);
        } catch (SoapFault $e) {
            $navStock = "N/A";
        }




        $postData = [
            "searchData"=>$rs,
            "bomitemInfo"=>$RSbomitem,
            "stockTotal"=>$stockTotal,
            "navStock" => $navStock
        ];


        response::success(json_encode($postData));


    }
public function samMultiple(){
    $itemNr = $_POST["itemNr"];
    $sql = "SELECT * FROM `navision_bomitem` WHERE `no` LIKE '".$itemNr."'";
    $rs = NavisionBomItem::find_by_sql($sql);
    response::success(json_encode($rs));
}



    public function searchItemNr_v2(){

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
                `present_reservation`.id =64274 and
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

        foreach ($rs as $obj){
            $sku = $obj->attributes["item_nr"];
            $sql = "SELECT * FROM `magento_stock_total` WHERE `itemno` LIKE '".$sku."'";
            $stockTotal = NavisionBomItem::find_by_sql($sql);

            if(sizeof($stockTotal) > 0){
                $available = $stockTotal[0]->attributes["available"];
            } else {
                $available = "N/A";
            }
            $obj->attributes["available"] = $available;

        }
        response::success(json_encode($rs));
    }
    public function jobStatus(){
        $sql ="SELECT *,count(*) as c FROM `rm_job` where `done` = 0 ";
        $rsJob =  ShopPresent::find_by_sql($sql);
        $sql ="select count(*) as c FROM `rm_data` where `done` = 0 and  job_id in ( select job_id from rm_job where done = 0) ";
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

        $language_code = $_POST["lang"] ?? 1;


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
                    cardshop_settings.language_code = ".$language_code." and
                    present_model.language_id = 1 and 
                    `present_reservation`.quantity != -1
                GROUP BY
                    `order`.`present_model_id` order by shop_present.shop_id";

        $rs =  ShopPresent::find_by_sql($sql);
        response::success(json_encode($rs));


    }

    public function getValgshop(){
        $shopID = $_POST["shopID"];
        $sql = "SELECT 
    shop_present.id,
    shop_present.shop_id,
    shop.name as concept_code,
    `present_reservation`.id as pr_id,
    `present_reservation`.quantity,
    present_model.active,
    present_model.autopilot,
    present_model.autopilot_lock,
    present_model.id as present_model_id,
  
    present_model.model_present_no, 
    present_model.model_name,
    present_model.model_no,
    (
        SELECT COUNT(*)
        FROM `order`
        WHERE `order`.`present_model_id` = present_model.model_id
    ) as antal,
    present_model.is_deleted
FROM `shop_present`
LEFT JOIN `present_model` ON `shop_present`.`present_id` = present_model.present_id
left join shop on shop_present.shop_id = shop.id
left join `present_reservation` on `present_model`.`model_id` = `present_reservation`.model_id 
WHERE 
    present_model.language_id = 1 
    AND shop_present.shop_id NOT IN (SELECT shop_id FROM cardshop_settings)
    AND shop_present.shop_id = ".$shopID;

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
    public function doSetCloseOpenAll()
    {
        $list = $_POST["list"];
        $savedIds = [];

        foreach ($list as $id) {
            $presentR = PresentReservation::find($id);

            if ($presentR) {
                $presentR->do_close = 1;
                if (!$presentR->save()) {
                    throw new Exception("Kunne ikke gemme PresentReservation med id: " . $id);
                }
                $savedIds[] = $id;
            } else {
                throw new Exception("PresentReservation med id: " . $id . " blev ikke fundet");
            }
        }

        response::success(json_encode($savedIds));
    }
    public function doUpdateModelData(){
        try {
            // Kontroller om der er sendt data
            if (empty($_POST)) {
                throw new Exception("Ingen data modtaget");
            }

            // Hent den eksisterende model
            $presentModel = PresentModel::find($_POST["id"]);


            // Liste over tilladte felter til opdatering
            $allowedFields = ['active', 'is_deleted','autopilot_lock','autopilot'];

            // Filtrer $_POST data for kun at inkludere tilladte felter
            $updateData = array_intersect_key($_POST, array_flip($allowedFields));

            // Opdater modellen med de filtrerede data
            $presentModel->update_attributes($updateData);

            // Valider modellen før gem
            if (!$presentModel->is_valid()) {
                throw new Exception("Validering fejlede: " . implode(", ", $presentModel->errors()->full_messages()));
            }

            // Gem ændringerne
            if ($presentModel->save()) {
                // Log opdateringen


                response::success(json_encode([
                    "status" => "1",
                    "message" => "Model opdateret succesfuldt",
                    "data" => $presentModel->attributes()
                ]));
            } else {
                throw new Exception("Kunne ikke gemme ændringer");
            }
        } catch (Exception $e) {
            response::error(json_encode([
                "status" => "0",
                "message" => $e->getMessage()
            ]));
        }
    }
    public function searchShopBoard()
    {


            // Search for all shops
            $shops = Shop::find('all', array(
                'select' => 'id, name',
                'conditions' => array(
                    'is_company = ? AND localisation = ? AND active = ? AND shop_mode = ? and in_shopboard = ?',
                    1, 1, 1, 1,1
                ),
                'order' => 'name ASC'
            ));
        

        response::success(json_encode($shops));

    }

    public function searchShops()
    {
        $searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

        if ($searchTerm === '*') {
            // Search for all shops
            $shops = Shop::find('all', array(
                'select' => 'id, name',
                'conditions' => array(
                    'is_company = ? AND localisation = ? AND active = ? AND shop_mode = ?',
                    1, 1, 1, 1
                ),
                'order' => 'name ASC'
            ));
        } else {
            // Normal search
            $shops = Shop::find('all', array(
                'select' => 'id, name',
                'conditions' => array(
                    'name LIKE ? AND is_company = ? AND localisation = ? AND active = ? AND shop_mode = ?',
                    '%' . $searchTerm . '%', 1, 1, 1, 1
                ),
                'order' => 'name ASC'
            ));
        }

        response::success(json_encode($shops));

    }




}
