<?php

namespace GFUnit\apps\shopreservation;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
    public function overview()
    {
        $this->view("overview");
    }

    public function getResevations()
    {
        if (!isset($_GET["lang"]) || !(int)$_GET["lang"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Ugyldig eller manglende sprog-ID"
            ]);
            exit;
        }
        $language_id = (int)$_GET["lang"];
        $language_id = ($language_id === 5) ? 1 : $language_id;




        $sql = "
        SELECT DATE(start_date) AS date, 'open' AS type, COUNT(*) AS count_per_day 
        FROM shop 
        WHERE  start_date IS NOT NULL and localisation = ".$language_id." AND start_date > '2024-04-01 14:12:30' 
        GROUP BY DATE(start_date)

        UNION ALL 

        SELECT DATE(end_date) AS date, 'close' AS type, COUNT(*) AS count_per_day 
        FROM shop 
        WHERE end_date IS NOT NULL  and localisation = ".$language_id." AND end_date > '2024-04-01 14:12:30' 
        GROUP BY DATE(end_date)

        UNION ALL 

        SELECT DATE(delivery_date) AS date, 'pickup' AS type, COUNT(*) AS count_per_day 
        FROM shop_metadata
           inner join shop on shop.id = shop_metadata.shop_id                                     
                where localisation = ".$language_id." 
        and delivery_date IS NOT NULL 
        GROUP BY DATE(delivery_date)
    ";



        $resultSet = \Shop::find_by_sql($sql);
        //print_R($resultSet);
        // Aggregate the results by date
        $aggregatedData = [];
        foreach ($resultSet as $row) {
            $date = $row->date;
            $type = $row->type;
            $count = $row->count_per_day;

            if (!isset($aggregatedData[$date])) {
                $aggregatedData[$date] = [
                    'openCount' => 0,
                    'closeCount' => 0,
                    'pickupCount' => 0
                ];
            }

            switch ($type) {
                case 'open':
                    $aggregatedData[$date]['openCount'] += $count;
                    break;
                case 'close':
                    $aggregatedData[$date]['closeCount'] += $count;
                    break;
                case 'pickup':
                    $aggregatedData[$date]['pickupCount'] += $count;
                    break;
            }
        }

        echo json_encode([
            "status" => 1,
            "data" => $aggregatedData
        ]);
    }
    function getShopOnDate()
    {
        if (!isset($_GET["lang"]) || !(int)$_GET["lang"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Ugyldig eller manglende sprog-ID"
            ]);
            exit;
        }
        $language_id = (int)$_GET["lang"];
        $language_id = ($language_id === 5) ? 1 : $language_id;

        $searchDate = $_POST["date"];


       $sql = "SELECT DISTINCT 
    shop.id,
    shop.name,
    shop.start_date,
    shop.end_date,
    shop_metadata.delivery_date,
    shop.localisation,
    CASE 
        WHEN  shop.localisation = ".$language_id."  THEN 'Correct'
        ELSE 'Incorrect'
    END AS localisation_check
FROM `shop`
left JOIN shop_metadata ON shop_metadata.shop_id = shop.id
WHERE 
    (
        DATE(shop.start_date) = '".$searchDate."' OR
        DATE(shop.end_date) = '".$searchDate."' OR
        shop_metadata.delivery_date = '".$searchDate."'
    )
    AND shop.localisation = ".$language_id
    ;
        $resultSet = \Shop::find_by_sql($sql);



        echo json_encode([
            "status" => 1,
            "data" => $resultSet
        ]);
    }
    function getAll()
    {
        if (!isset($_GET["lang"]) || !(int)$_GET["lang"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Ugyldig eller manglende sprog-ID"
            ]);
            exit;
        }
        $language_id = (int)$_GET["lang"];
        $language_id = ($language_id === 5) ? 1 : $language_id;


        $sql = "
            SELECT distinct shop.id ,shop.name, start_date,end_date,delivery_date FROM `shop`
            left join shop_metadata on shop_metadata.shop_id = shop.id WHERE `is_gift_certificate` = 0 and shop.localisation = ".$language_id."  AND shop.active = 1 and `is_company` = 1 AND shop.`created_datetime` > '2024-04-01 02:56:46' order by id
    ";
        $resultSet = \Shop::find_by_sql($sql);



        echo json_encode([
            "status" => 1,
            "data" => $resultSet
        ]);
    }
}
