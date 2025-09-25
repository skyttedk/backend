<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();




$stats = new Stats;








$lang = $_GET["lang"];
$year = $_GET["year"];

$startYear = $year."-01-01 00:00:01";
$slutYear = $year."-12-31 23:59:59";
$dbPrefix = "gavefabrikken".$year;

$correction =  date('Y') - $year;

 $res_total = $stats->getSalePresentYear($dbPrefix,$lang,$startYear,$slutYear);


// same day 2021

 $start = $stats->getSameDayInWeekLastYear(false,$correction)." 00:00:01";

 $end = $stats->getSameDayInWeekLastYear(false,$correction)." 23:59:59";

//
   $res_same_day = $stats->getSalePresentYear($dbPrefix,$lang,$start,$end);

// same mounth 2021
$mounth = date('m');
$currentMonth = date('m');
$currentYear = date('Y');

// Beregner sidste års årstal

$lastYear =  intval(date('Y')) - intval($year);
// Finder antallet af dage i måneden fra sidste år
$daysInLastYearMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $lastYear);

$month_start =  $year."-".$mounth."-01 00:00:01";
$month_end   =  $year."-".$mounth."-".$daysInLastYearMonth." 23:59:59";

 $res_same_month = $stats->getSalePresentYear($dbPrefix,$lang,$month_start,$month_end);



// totalSameDay
$totalSameDay_start =  $year."-02-01 00:00:01 ";
$totalSameDay_end = $stats->getSameDayInWeekLastYear(true,$correction) ;
$res_total_same_day = $stats->getSalePresentYear($dbPrefix,$lang,$totalSameDay_start,$totalSameDay_end);



$returnData = array(


    "total"=> $res_total["data"],
    "day"=>$res_same_day["data"],
    "month"=>$res_same_month["data"],
    "total_day"=>$res_total_same_day["data"]

);

echo base64_encode(serialize($returnData)) ;









class Stats
{
    private $db;
    public function __construct() {
        $this->db =  new Dbsqli();
        $this->db->setKeepOpen();
    }

    public function getSalePresentYear($database,$lang,$start="2022-01-01 00:00:01",$slut="2022-12-31 23:59:59")
    {
        // Check om året er 2024
        $year = date('Y', strtotime($start));
        $is2024 = ($year == 2024);

        // kun se pga 400/440 budget - kun for 2024
        $seSql = ($lang == 5 && $is2024) ? " ,shop_user.card_values ": "";
        $cardValuesSelect = ($lang == 5 && $is2024) ? "c.card_values," : "";

        $sql = "SELECT
            cardshop_settings.shop_id,
            language_code,
            cardshop_settings.concept_code,
            {$cardValuesSelect}
            c.antal
        FROM
            ".$database.".`cardshop_settings`
        LEFT JOIN(
                SELECT shop_user.shop_id" . (($lang == 5 && $is2024) ? ", shop_user.card_values" : "") . ",
                COUNT(shop_user.id) AS antal
            FROM
                ".$database.".shop_user
            LEFT JOIN ".$database.".company_order ON shop_user.company_order_id = company_order.id
            WHERE
                company_order.`created_datetime` > '".$start."' 
                AND company_order.`created_datetime` <= '".$slut."' 
                AND `salesperson` NOT LIKE ('%us%')
                AND company_order.order_state NOT IN(7,8,20) 
                AND company_order.is_cancelled = 0 
                AND company_order_id NOT IN( 
                    SELECT id 
                    FROM ".$database.".company_order 
                    WHERE company_id IN(
                        44780,
                        44794,
                        44795,
                        45363,
                        45364,
                        45365,
                        51821,
                        52468,
                        52469,
                        52470,
                        68774,
                        69437,
                        69439,
                        69451
                    )
                )
                AND shop_user.is_demo = 0 
                AND shop_user.blocked = 0 
                AND shop_user.shutdown = 0
            GROUP BY
                shop_user.shop_id{$seSql}
        ) AS c
        ON
            `cardshop_settings`.shop_id = c.shop_id
        WHERE
            language_code = $lang
        ORDER BY
            language_code,
            cardshop_settings.show_index";

        return $this->db->get($sql);
    }
    public function getSalePresentYear1($database,$lang,$start="2022-01-01 00:00:01",$slut="2022-12-31 23:59:59")
    {
         $sql = "SELECT
                cardshop_settings.shop_id,
                language_code,
                cardshop_settings.concept_code,
                c.antal
            FROM
                ".$database.".`cardshop_settings`
            LEFT JOIN(
                    SELECT shop_user.shop_id,
                    COUNT(shop_user.id) AS antal
                FROM
                    ".$database.".shop_user
                LEFT JOIN ".$database.".company_order ON shop_user.company_order_id = company_order.id
                WHERE
                    company_order.`created_datetime` > '".$start."' AND company_order.`created_datetime` <= '".$slut."' AND company_order.order_state not IN(7,8) AND company_order.is_cancelled = 0 AND company_order.company_id NOT IN(
                44780,
44794,
44795,
45363,
45364,
45365,
51821,
52468,
52469,
52470,
68774,
69437,
69439,
69451



                ) AND shop_user.is_demo = 0 AND shop_user.blocked = 0 AND shop_user.shutdown = 0
                GROUP BY
                    shop_user.shop_id
            ) AS c
            ON
                `cardshop_settings`.shop_id = c.shop_id
            where
                language_code = $lang";

        return  $this->db->get($sql);

    }
    public function getSameDayInWeekLastYear($time=true,$correction=0){
        $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year

        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)
        if($day == 0) {
            $day = 7;
        }
        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - $correction, $week, $day);
        if($time==true){
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }

    }
    public function getMounthLastYear($time=true,$correction=0){
        $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year

        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)
        if($day == 0) {
            $day = 7;
        }
        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - $correction, $week, $day);
        if($time==true){
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }

    }


}