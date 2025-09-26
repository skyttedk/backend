<?php

namespace GFUnit\apps\mypage;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
    public function statusStats() {
        $this->view("status_stats");
    }
    public function showThisWeekCreatePimItems()
    {
        $this->view("this_week_pim_items");
    }

    public function fetchNewlyCreatedGifts()
    {
        $sqlLastWeek = " SELECT present.*,present_model.model_present_no,present_model.model_name
        FROM present
        INNER join present_model on present_model.present_id = present.id
        
        WHERE created_datetime >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 7 DAY) AND created_datetime < DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)  and present.shop_id = 0 and copy_of = 0 and present_model.language_id = 1 
        ORDER BY present_model.present_id, `present`.`created_datetime` ASC  ";
        $lastWeek = \Present::find_by_sql($sqlLastWeek);


        $sqlThisWeek = "SELECT present.*,present_model.model_present_no,present_model.model_name
            FROM present
            INNER join present_model on present_model.present_id = present.id
            
            WHERE created_datetime >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
              AND created_datetime < DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) - 6 DAY) + INTERVAL 1 DAY  and present.shop_id = 0 and copy_of = 0 and present_model.language_id = 1
            ORDER BY present_model.present_id, `present`.`created_datetime`  ASC";
        $thisWeek = \Present::find_by_sql($sqlThisWeek);

        $returnData = array("lastWeek"=>$lastWeek,"thisWeek"=>$thisWeek);
        echo json_encode(array("status" => 1,"data"=>$returnData));
    }

    public function shopStatusStats()
    {

        $sql = "SELECT
                `shop_mode`,
                COUNT(id) AS antal,
                localisation
            FROM
                `shop`
            WHERE
                `is_gift_certificate` = 0 AND `is_company` = 1 AND active = 1 AND deleted = 0 AND `created_datetime` > CONCAT(YEAR(CURDATE()), '-01-01 12:49:33')
            GROUP BY
                shop_mode,
                localisation
            ORDER BY
                localisation,
                shop_mode";
        $res = \Shop::find_by_sql($sql);
        echo json_encode(array("status" => 1,"data"=>$res));
    }
    public function shopStatusStatsDetail()
    {
        $sql = "SELECT
        shop.id,
     
        CASE 
            WHEN shop.localisation = 1 THEN 'dk'
            WHEN shop.localisation = 4 THEN 'no'
            WHEN shop.localisation = 5 THEN 'se'
            ELSE 'unknown'
        END AS localisation,
        shop.name,
        CASE
            WHEN shop.shop_mode = 1 THEN 'Solgt valgshop'
            WHEN shop.shop_mode = 2 THEN 'Valgshop oplæg'
            WHEN shop.shop_mode = 3 THEN 'Tabt'
            WHEN shop.shop_mode = 4 THEN 'Papirvalg oplæg'
            WHEN shop.shop_mode = 5 THEN 'Andet oplæg'
            WHEN shop.shop_mode = 6 THEN 'Solgt papirvalg'
            ELSE 'unknown'
        END AS shop_mode,
        DATE_FORMAT(shop.created_datetime, '%Y-%m-%d %H:%i:%s') AS created_datetime_str,
        company.sales_person,
        company.gift_responsible
    FROM 
        shop
    INNER JOIN 
        company_shop ON shop.id = company_shop.shop_id
    INNER JOIN 
        company ON company.id = company_shop.company_id
    WHERE
        shop.is_gift_certificate = 0 
        AND shop.is_company = 1 
        AND shop.active = 1 
        AND shop.deleted = 0 
        AND shop.created_datetime > CONCAT(YEAR(CURDATE()), '-01-01 12:49:33')
    group by shop.id
    ORDER BY 
        localisation,
        shop_mode,
        sales_person,
        created_datetime_str";

        $data = array();
        $res = \Dbsqli::getSql2($sql);

        // Definér kolonneoverskrifter
        $headers = array('id', 'land', 'shopnavn', 'oplæg status', 'oprettet', 'sælger', 'shopansvarlig');
        $headers = array_map('utf8_decode', $headers);
        // Angiv HTTP-headers til download af CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="shops.csv"');

        // Åbn en fil pointer i skrivemodus til output stream
        $output = fopen('php://output', 'w');

        // Skriv kolonneoverskrifter til CSV med semikolon separator
        fputcsv($output, $headers, ';');

        // Skriv hver række af data til CSV
        foreach ($res as $row) {
            $utf8_row = array_map('utf8_decode', $row);
            fputcsv($output, $utf8_row, ';');
        }

        // Luk fil pointer
        fclose($output);
        exit();
    }


}

?>


