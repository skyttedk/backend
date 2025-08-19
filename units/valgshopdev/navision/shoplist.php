<?php

namespace GFUnit\valgshop\navision;
use GFBiz\units\UnitController;


class ShopList
{

    private $output = false;
    private $logs = [];

    public function __construct()
    {

    }

    public function run()
    {
/*
        $sql = "SELECT
s.id AS shop_id,
s.name AS shop_name,
s.link AS shop_link,
s.start_date AS shop_start,
s.end_date AS shop_end,
s.reservation_state,
s.reservation_language,
s.reservation_code,
s.reservation_foreign_language,
s.reservation_foreign_code,
s.language_id,
sm.order_no AS vs_order_no,
sm.so_no AS vs_so_no,
sm.order_type AS shop_type,
sm.salesperson_code,
sm.user_count AS users,
sm.nav_debitor_no,
sa.orderdata_approval,
sa.invoice_approval,
nv.state AS sync_state,
nv.last_run_date AS nav_last_run,
nv.last_run_error AS nav_last_run_iserror,
nv.last_run_message AS nav_last_run_message,
nv.on_hold AS nav_on_hold,
nv.finished AS nav_finished_date,
MAX(nvv.version) AS nav_last_version,
COUNT(DISTINCT su.id) AS shop_user_count,
COUNT(DISTINCT `o`.id) AS order_count,
COUNT(DISTINCT pr.model_id) AS present_count,
SUM(pr.quantity) AS reservation_quantity,
SUM(pr.sync_quantity) AS reserved_quantity,
SUM(pr.quantity_done) AS done_quantity,
MAX(pr.sync_time) AS reservation_last_sync_time,
MAX(pr.sync_note) AS reservation_last_sync_note
FROM
shop s
JOIN
shop_metadata sm ON s.id = sm.shop_id
JOIN
shop_approval sa ON s.id = sa.shop_id
JOIN
navision_vs_state nv ON s.id = nv.shop_id
LEFT JOIN
navision_vs_version nvv ON s.id = nvv.shop_id
LEFT JOIN
shop_user su ON s.id = su.shop_id
LEFT JOIN
`order` `o` ON s.id = `o`.shop_id
LEFT JOIN
present_reservation pr ON s.id = pr.shop_id
GROUP BY
s.id;
";
*/

        $sql = "SELECT 
    s.id AS shop_id, 
    s.name AS shop_name, 
    s.link AS shop_link, 
    s.start_date AS shop_start, 
    s.end_date AS shop_end, 
    s.reservation_state, 
    s.reservation_language, 
    s.reservation_code, 
    s.reservation_foreign_language, 
    s.reservation_foreign_code, 
    s.language_id, 
    sm.order_no AS vs_order_no, 
    sm.so_no AS vs_so_no, 
    sm.order_type AS shop_type, 
    sm.salesperson_code, 
    sm.user_count AS users, 
    sm.nav_debitor_no, 
    sa.orderdata_approval, 
    sa.invoice_approval, 
    nv.state AS sync_state, 
    nv.last_run_date AS nav_last_run, 
    nv.last_run_error AS nav_last_run_iserror, 
    nv.last_run_message AS nav_last_run_message, 
    nv.on_hold AS nav_on_hold, 
    nv.finished AS nav_finished_date, 
    (SELECT MAX(nvv.version) FROM navision_vs_version nvv WHERE s.id = nvv.shop_id) AS nav_last_version, 
    (SELECT COUNT(DISTINCT su.id) FROM shop_user su WHERE s.id = su.shop_id) AS shop_user_count, 
    (SELECT COUNT(DISTINCT `o`.id) FROM `order` `o` WHERE s.id = `o`.shop_id) AS order_count, 
    (SELECT COUNT(DISTINCT pr.model_id) FROM present_reservation pr WHERE s.id = pr.shop_id) AS present_count, 
    (SELECT SUM(pr.quantity) FROM present_reservation pr WHERE s.id = pr.shop_id) AS reservation_quantity, 
    (SELECT SUM(pr.sync_quantity) FROM present_reservation pr WHERE s.id = pr.shop_id) AS reserved_quantity, 
    (SELECT SUM(pr.quantity_done) FROM present_reservation pr WHERE s.id = pr.shop_id) AS done_quantity, 
    (SELECT MAX(pr.sync_time) FROM present_reservation pr WHERE s.id = pr.shop_id) AS reservation_last_sync_time, 
    (SELECT MAX(pr.sync_note) FROM present_reservation pr WHERE s.id = pr.shop_id) AS reservation_last_sync_note 
FROM 
    shop s 
JOIN 
    shop_metadata sm ON s.id = sm.shop_id 
JOIN 
    shop_approval sa ON s.id = sa.shop_id 
JOIN 
    navision_vs_state nv ON s.id = nv.shop_id 
GROUP BY 
    s.id;";

        $shopList = \Shop::find_by_sql($sql);

        $sync = new NavSyncJob();
        $syncList = $sync->loadWaitingShops();

        echo '<style>';
        echo 'table {';
        echo '    width: 100%;';
        echo '    border-collapse: collapse;';
        echo '}';
        echo 'th, td {';
        echo '    padding: 8px;';
        echo '    text-align: left;';
        echo '    border-bottom: 1px solid #ddd;';
        echo '}';
        echo 'th {';
        echo '    background-color: #f2f2f2;';
        echo '}';
        echo 'tr:hover {';
        echo '    background-color: #f5f5f5;';
        echo '}';
        echo '</style>';

        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th colspan="3">Alm. Informationer</th>';
        echo '<th colspan="7">Ordre</th>';
        echo '<th colspan="2">Approval</th>';
        echo '<th colspan="7">Navision</th>';

        echo '<th colspan="5">Reservation</th>';
        echo '<th colspan="5">Reserveret</th>';



        echo '</tr>';

        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Navn</th>';
        echo '<th>Land</th>';
        echo '<th>Sælger</th>';
        echo '<th>Debitor no</th>';
        echo '<th>VS nr</th>';
        echo '<th>SO nr</th>';
        echo '<th>Brugere</th>';
        echo '<th>ShopUsers</th>';
        echo '<th>Orders (%)</th>';

        echo '<th>Orderdata Approval</th>';
        echo '<th>Invoice Approval</th>';

        echo '<th>Afventer sync</th>';
        echo '<th>Sync State</th>';
        echo '<th>Last Run</th>';
        echo '<th>Last Run Error</th>';
        echo '<th>Last Run Message</th>';
        echo '<th>On Hold</th>';
        echo '<th>Finished</th>';

        echo '<th>State</th>';
        echo '<th>Language</th>';
        echo '<th>Code</th>';
        echo '<th>Foreign Language</th>';
        echo '<th>Foreign Code</th>';

        echo '<th>Reservationer</th>';
        echo '<th>Reserveret</th>';
        echo '<th>Afsluttet</th>';
        echo '<th>Sidste sync</th>';
        echo '<th>Sidste sync note</th>';

        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($shopList as $shop) {
            echo '<tr>';

            // Stamdata
            echo '<td>' . htmlspecialcharsgf($shop->shop_id) . '</td>';
            echo '<td><a href="'.\GFConfig::BACKEND_URL.'index.php?rt=mainaa&editShopID='.$shop->shop_id.'" target="_blank">' . htmlspecialcharsgf($shop->shop_name) . '</a></td>';
            echo '<td style="">' . $this->valueToText($shop->language_id,array(1 => "Danmark",4 => "Norge", 3=> "Sverige"),"Ukendt") . '</td>';
            // Order
            echo '<td>' . htmlspecialcharsgf($shop->salesperson_code) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->nav_debitor_no) . '</td>';
            echo '<td><a href="'.\GFConfig::BACKEND_URL.'index.php?rt=unit/valgshop/orderconf/html/'.$shop->shop_id.'" target="_blank">' . htmlspecialcharsgf($shop->vs_order_no) . '</a></td>';
            echo '<td>' . htmlspecialcharsgf($shop->vs_so_no) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->users) . '</td>';

            $userPercentage = $shop->users > 0 ? round($shop->shop_user_count / $shop->users * 100, 2) : 0;
            echo '<td>' . htmlspecialcharsgf($shop->shop_user_count) . ' ('.intval($userPercentage).'%)</td>';

            $orderPercentage = $shop->shop_user_count > 0 ? round($shop->order_count / $shop->shop_user_count * 100, 2) : 0;
            echo '<td>' . htmlspecialcharsgf($shop->order_count) . ' ('.intval($orderPercentage).'%)</td>';

            // Approval
            echo '<td style="background: '.$this->valueToColor($shop->orderdata_approval, array(0 => "info",1 => "warning", 2=> "success", 3 => "danger")).'">' . $this->valueToText($shop->orderdata_approval,array(0 => "Afventer sælger",1 => "Godkendt af sælger", 2=> "Godkendt", 3 => "Afvist"),"Ukendt") . '</td>';
            echo '<td style="background: '.$this->valueToColor($shop->invoice_approval, array(0 => "info",1 => "warning", 2=> "success", 3 => "danger")).'">' . $this->valueToText($shop->invoice_approval,array(0 => "Afventer sælger",1 => "Godkendt af sælger", 2=> "Godkendt", 3 => "Afvist"),"Ukendt") . '</td>';


            // Sync state
            echo '<td>' . (in_array($shop->shop_id,$syncList) ? "JA" : "NEJ") . '</td>';
            echo '<td style="background: '.$this->valueToColor($shop->sync_state, array(0 => "warning",1 => "success", 2=> "danger", 3 => "#D0D0D0", 4 => "info", 5 => "#444444")).'">' . $this->valueToText($shop->sync_state,array(0 => "Ikke synkroniseret",1 => "Ordre synkroniseret", 2=> "Ordre fejl", 3 => "Skal annulleres", 4 => "Ordre annulleret", 5 => "Under afslutning", 6 => "Afsluttet"),"Ukendt") . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->nav_last_run) . '</td>';
            echo '<td style="background: '.$this->valueToColor($shop->nav_last_run_iserror, array(0 => "success",1 => "danger")).'">' . $this->valueToText($shop->nav_last_run_iserror,array(0 => "OK",1 => "Fejl"),"Ukendt") . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->nav_last_run_message) . '</td>';
            echo '<td style="background: '.$this->valueToColor($shop->nav_on_hold, array(0 => "success",1 => "danger")).'">' . $this->valueToText($shop->nav_on_hold,array(0 => "Nej",1 => "Ja"),"Ukendt") . '</td>';

            echo '<td>' . htmlspecialcharsgf($shop->nav_finished_date) . '</td>';

            // Reservation
            echo '<td style="border-left: 1px solid black; background: '.$this->valueToColor($shop->reservation_state, array(0 => "info",1 => "success")).'">' . $this->valueToText($shop->reservation_state,array(0 => "Ikke startet",1 => "Startet"),"Ukendt") . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->reservation_language) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->reservation_code) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->reservation_foreign_language) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->reservation_foreign_code) . '</td>';

            echo '<td>' . htmlspecialcharsgf($shop->reservation_quantity) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->reserved_quantity) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->done_quantity) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->reservation_last_sync_time) . '</td>';
            echo '<td>' . htmlspecialcharsgf($shop->reservation_last_sync_note) . '</td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';


    }

    private function valueToText($value,$options,$default) {

        if($value !== null && isset($options[$value])) return $options[$value];
        return $default." (".$value.")";

    }

    private function valueToColor($value,$options,$default="#A0A0A0") {

        $color = $default;
        if($value !== null && isset($options[$value])) $color = $options[$value];
        
        $predefinedColors = array(
            "success" => "#4CAF50",
            "info" => "#2196F3",
            "warning" => "#ff9800",
            "danger" => "#f44336"
        );
        
        if(isset($predefinedColors[$color])) $color = $predefinedColors[$color];
        return $color;



    }

}