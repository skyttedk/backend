<?php

namespace GFUnit\cardshop\status;
use GFBiz\units\UnitController;

class StatusMetrics
{

    public function generateMetrics($languageList)
    {

        if($languageList == null || count($languageList) == 0) $languageList[] = 0;

        /** LOAD ORDERS */

        $orderStats = \CompanyOrder::find_by_sql("SELECT 
    COUNT(*) AS total_orders,
    SUM(CASE WHEN DATE(created_datetime) = CURDATE() THEN 1 ELSE 0 END) AS orders_today,
    SUM(CASE WHEN DATE(created_datetime) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS orders_yesterday,
    SUM(CASE WHEN created_datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS orders_last_7_days
FROM `company_order` 
WHERE order_state NOT IN (7,8) 
AND shop_id IN (SELECT shop_id FROM cardshop_settings WHERE language_code IN (".implode(",",$languageList)."))");

        $orderStats = $orderStats[0] ?? null;


        /** OUTPUT ORDERS */

        ?><!-- Metrics Section -->
<h2 class="section-header">Metrics</h2>
<div class="grid">

    <div class="card">
        <h3>
            <span>Ordre</span>
            <span class="icon">📦</span>
        </h3>
        <div class="data-row">
            <span class="label">I dag</span>
            <span class="value"><?php echo number_format($orderStats->orders_today ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">I går</span>
            <span class="value"><?php echo number_format($orderStats->orders_yesterday ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">Sidste 7 dage</span>
            <span class="value"><?php echo number_format($orderStats->orders_last_7_days ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">Total</span>
            <span class="value"><?php echo number_format($orderStats->total_orders ?? 0,0,",","."); ?></span>
        </div>
    </div><?php

    /** LOAD PRESENT ORDER **/

    $orderStats = \CompanyOrder::find_by_sql("SELECT 
    COUNT(*) AS total_gift_orders,
    SUM(CASE WHEN DATE(order_timestamp) = CURDATE() THEN 1 ELSE 0 END) AS gift_orders_today,
    SUM(CASE WHEN DATE(order_timestamp) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS gift_orders_yesterday,
    SUM(CASE WHEN order_timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS gift_orders_last_7_days
FROM `order` 
WHERE shop_is_gift_certificate = 1 
AND shop_id IN (SELECT shop_id FROM cardshop_settings WHERE language_code IN (".implode(",",$languageList)."));");

    $orderStats = $orderStats[0] ?? null;

    ?><div class="card">
        <h3>
            <span>Valg</span>
            <span class="icon">🔍</span>
        </h3>
        <div class="data-row">
            <span class="label">I dag</span>
            <span class="value"><?php echo number_format($orderStats->gift_orders_today ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">I går</span>
            <span class="value"><?php echo number_format($orderStats->gift_orders_yesterday ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">Sidste 7 dage</span>
            <span class="value"><?php echo number_format($orderStats->gift_orders_last_7_days ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">Total</span>
            <span class="value"><?php echo number_format($orderStats->total_gift_orders ?? 0,0,",","."); ?></span>
        </div>
    </div><?php

    /** LOAD SHIPMENTS **/

    $orderStats = \CompanyOrder::find_by_sql("SELECT 
    COUNT(*) AS total_shipments,
    SUM(CASE WHEN DATE(s.shipment_sync_date) = CURDATE() THEN 1 ELSE 0 END) AS shipments_today,
    SUM(CASE WHEN DATE(s.shipment_sync_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS shipments_yesterday,
    SUM(CASE WHEN s.shipment_sync_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS shipments_last_7_days
FROM `shipment` s
JOIN `company_order` co ON s.companyorder_id = co.id
JOIN `cardshop_settings` cs ON co.shop_id = cs.shop_id
WHERE s.isshipment = 1 
AND cs.language_code IN (".implode(",",$languageList).")
AND s.shipment_sync_date IS NOT NULL");

    $orderStats = $orderStats[0] ?? null;

    ?>
    <div class="card">
        <h3>
            <span>Forsendelser</span>
            <span class="icon">🚚</span>
        </h3>
        <div class="data-row">
            <span class="label">I dag</span>
            <span class="value"><?php echo number_format($orderStats->shipments_today ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">I går</span>
            <span class="value"><?php echo number_format($orderStats->shipments_yesterday ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">Sidste 7 dage</span>
            <span class="value"><?php echo number_format($orderStats->shipments_last_7_days ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">Total</span>
            <span class="value"><?php echo number_format($orderStats->total_shipments ?? 0,0,",","."); ?></span>
        </div>
    </div><?php

    /** LOAD MAILS **/

    $orderStats = \CompanyOrder::find_by_sql("SELECT 
    COUNT(*) AS total_sent_mails,
    SUM(CASE WHEN DATE(sent_datetime) = CURDATE() THEN 1 ELSE 0 END) AS mails_sent_today,
    SUM(CASE WHEN DATE(sent_datetime) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS mails_sent_yesterday,
    SUM(CASE WHEN sent_datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS mails_sent_last_7_days
FROM `mail_queue` 
WHERE sent_datetime IS NOT NULL;");

    $orderStats = $orderStats[0] ?? null;

    ?><div class="card">
        <h3>
            <span title="Alle e-mails sendt også valgshops, velkomstmails, manuelle mails og er ikke begrænset pr. land.">Mails (alt sendt)</span>
            <span class="icon">✉️</span>
        </h3>
        <div class="data-row">
            <span class="label">I dag</span>
            <span class="value"><?php echo number_format($orderStats->mails_sent_today ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">I går</span>
            <span class="value"><?php echo number_format($orderStats->mails_sent_yesterday ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">Sidste 7 dage</span>
            <span class="value"><?php echo number_format($orderStats->mails_sent_last_7_days ?? 0,0,",","."); ?></span>
        </div>
        <div class="data-row">
            <span class="label">Total</span>
            <span class="value"><?php echo number_format($orderStats->total_sent_mails ?? 0,0,",","."); ?></span>
        </div>
    </div>
</div><?php

    }

}