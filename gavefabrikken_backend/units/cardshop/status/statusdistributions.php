<?php

namespace GFUnit\cardshop\status;
use GFBiz\units\UnitController;

class StatusDistributions
{

public function generateDistributions($languageList)
{

    $sql = "SELECT 
    COUNT(c.id) as companycount, 
    c.company_state 
FROM company c
INNER JOIN company_order co ON c.id = co.company_id
INNER JOIN cardshop_settings cs ON co.shop_id = cs.shop_id
WHERE co.order_state NOT IN (7,8) 
AND cs.language_code in (".implode(",",$languageList).")
GROUP BY c.company_state;";

    $companyStats = \Company::find_by_sql($sql);

    $stateList = array(
        array("title" => "Afventer nav sync", "states" => array(1,3), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "På fejlliste", "states" => array(2), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Synkroniseret", "states" => array(5), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Blokkeret", "states" => array(4), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Sync fejlet", "states" => array(6), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Andre / ukendte", "states" => null, "count" => 0, "percentage" => null,"class" => "bg-pending"),
    );

    // Foreach stats, find in state list and add to count, keep track of total
    $totalCount = 0;
    foreach($companyStats as $stat) {
        // Antag at $stat->count_id (eller lignende) indeholder antal og $stat->company_state indeholder status-id
        $count = $stat->companycount;
        $state = $stat->company_state;
        $totalCount += $count;

        $found = false;
        foreach($stateList as &$stateItem) {
            if ($stateItem["states"] !== null && in_array($state, $stateItem["states"])) {
                $stateItem["count"] += $count;
                $found = true;
                break;
            }
        }

        // Hvis status ikke er fundet i nogen kategori, tilføj til "Andre / ukendte"
        if (!$found) {
            foreach($stateList as &$stateItem) {
                if ($stateItem["states"] === null) {
                    $stateItem["count"] += $count;
                    break;
                }
            }
        }
    }

    // For each state list calculate percentage
    foreach($stateList as &$stateItem) {
        $stateItem["percentage"] = $totalCount > 0 ? round(($stateItem["count"] / $totalCount) * 100, 2) : 0;
    }

    // For at undgå problemer med reference til array-element
    unset($stateItem);

?>
    <!-- Distribution Section -->
    <h2 class="section-header">Fordelinger</h2>
    <div class="distribution-grid">
        <!-- Kunde fordeling -->
        <div class="card">
            <h3>Kunde fordeling</h3>
            <?php

            // Print resultater
            foreach($stateList as $state) {
                ?><div class="distribution-item">
                <span class="label"><?php echo htmlspecialchars($state["title"]); ?></span>
                <div class="bar">
                    <div class="bar-fill <?php echo $state["class"]; ?>" style="width: <?php echo $state["percentage"]; ?>%;"></div>
                </div>
                <span class="value"><?php echo number_format($state["count"], 0, ',', '.'); ?></span>
                </div><?php
            }

            ?>
        </div>

    <?php


    $sql = "SELECT co.order_state, COUNT(*) as order_count 
    FROM company_order co
    INNER JOIN cardshop_settings cs ON co.shop_id = cs.shop_id
    WHERE cs.language_code in (".implode(",",$languageList).")
    GROUP BY co.order_state ORDER BY co.order_state ASC;";

    $orderStats = \CompanyOrder::find_by_sql($sql);

    $stateList = array(
        array("title" => "Afventer nav sync", "states" => array(0,1,3), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "På fejlliste", "states" => array(2,6), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Synkroniseret og åben", "states" => array(4,5), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Krediteret", "states" => array(7,8), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Afsluttet", "states" => array(9,10,11,12), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Skal ikke faktureres", "states" => array(20,21), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Andre / ukendte", "states" => null, "count" => 0, "percentage" => null,"class" => "bg-pending"),
    );

    // Foreach stats, find in state list and add to count, keep track of total
    $totalCount = 0;
    foreach($orderStats as $stat) {
        // Antag at $stat->count_id (eller lignende) indeholder antal og $stat->company_state indeholder status-id
        $count = $stat->order_count;
        $state = $stat->order_state;
        $totalCount += $count;

        $found = false;
        foreach($stateList as &$stateItem) {
            if ($stateItem["states"] !== null && in_array($state, $stateItem["states"])) {
                $stateItem["count"] += $count;
                $found = true;
                break;
            }
        }

        // Hvis status ikke er fundet i nogen kategori, tilføj til "Andre / ukendte"
        if (!$found) {
            foreach($stateList as &$stateItem) {
                if ($stateItem["states"] === null) {
                    $stateItem["count"] += $count;
                    break;
                }
            }
        }
    }

    // For each state list calculate percentage
    foreach($stateList as &$stateItem) {
        $stateItem["percentage"] = $totalCount > 0 ? round(($stateItem["count"] / $totalCount) * 100, 2) : 0;
    }

    // For at undgå problemer med reference til array-element
    unset($stateItem);


    ?>

        <!-- Ordre fordeling -->
        <div class="card">
            <h3>Ordre fordeling</h3>
            <?php

            // Print resultater
            foreach($stateList as $state) {
                ?><div class="distribution-item">
                <span class="label"><?php echo htmlspecialchars($state["title"]); ?></span>
                <div class="bar">
                    <div class="bar-fill <?php echo $state["class"]; ?>" style="width: <?php echo $state["percentage"]; ?>%;"></div>
                </div>
                <span class="value"><?php echo number_format($state["count"], 0, ',', '.'); ?></span>
                </div><?php
            }

            ?>
        </div>


    <?php


    $sql = "SELECT 
    COUNT(s.id) as shipmentcount, 
    s.shipment_state 
FROM shipment s
INNER JOIN company_order co ON s.companyorder_id = co.company_id
INNER JOIN cardshop_settings cs ON co.shop_id = cs.shop_id
WHERE co.order_state NOT IN (7,8) 
AND cs.language_code in (".implode(",",$languageList).")
GROUP BY s.shipment_state;";

    $companyStats = \Shipment::find_by_sql($sql);

    $stateList = array(
        array("title" => "Afventer", "states" => array(0,1), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "På fejlliste", "states" => array(3,9), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Sendt", "states" => array(2,5,6), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Annulleret", "states" => array(4), "count" => 0, "percentage" => null,"class" => "bg-pending"),
        array("title" => "Andre / ukendte", "states" => null, "count" => 0, "percentage" => null,"class" => "bg-pending"),
    );

    // Foreach stats, find in state list and add to count, keep track of total
    $totalCount = 0;
    foreach($companyStats as $stat) {
        // Antag at $stat->count_id (eller lignende) indeholder antal og $stat->company_state indeholder status-id
        $count = $stat->shipmentcount;
        $state = $stat->shipment_state;
        $totalCount += $count;

        $found = false;
        foreach($stateList as &$stateItem) {
            if ($stateItem["states"] !== null && in_array($state, $stateItem["states"])) {
                $stateItem["count"] += $count;
                $found = true;
                break;
            }
        }

        // Hvis status ikke er fundet i nogen kategori, tilføj til "Andre / ukendte"
        if (!$found) {
            foreach($stateList as &$stateItem) {
                if ($stateItem["states"] === null) {
                    $stateItem["count"] += $count;
                    break;
                }
            }
        }
    }

    // For each state list calculate percentage
    foreach($stateList as &$stateItem) {
        $stateItem["percentage"] = $totalCount > 0 ? round(($stateItem["count"] / $totalCount) * 100, 2) : 0;
    }

    // For at undgå problemer med reference til array-element
    unset($stateItem);

    ?>

        <!-- Forsendelses fordeling -->
        <div class="card">
            <h3>Forsendelses fordeling</h3>
            <?php

            // Print resultater
            foreach($stateList as $state) {
                ?><div class="distribution-item">
                <span class="label"><?php echo htmlspecialchars($state["title"]); ?></span>
                <div class="bar">
                    <div class="bar-fill <?php echo $state["class"]; ?>" style="width: <?php echo $state["percentage"]; ?>%;"></div>
                </div>
                <span class="value"><?php echo number_format($state["count"], 0, ',', '.'); ?></span>
                </div><?php
            }

            ?>
            <div style="font-size: 12px; padding-top: 16px;"><i>Indeholder privatleveringer, gavekort forsendelser, erstatningskort og earlyordre.</i></div>
        </div>


        <?php

        $sql = "SELECT 
    CASE
        WHEN `silent` > 0 THEN 'silent'
        WHEN `tech_block` > 0 THEN 'tech'
        WHEN `shipment_id` > 0 THEN 'shipment'
        WHEN `company_order_id` > 0 THEN 'order'
        WHEN `company_id` > 0 THEN 'company'
        ELSE 'unknown'
    END AS kategori,
    COUNT(*) AS antal
FROM 
    `blockmessage`
WHERE 
    `release_status` = 0 && company_id in (select id from company where id in (select company_id from company_order where shop_id in (select shop_id from cardshop_settings where language_code in (".implode(",",$languageList)."))))
GROUP BY 
    kategori
ORDER BY
    FIELD(kategori, 'silent', 'tech', 'shipment', 'order', 'company', 'unknown');";

        $blockList = \BlockMessage::find_by_sql($sql);

        $stateList = array(
            array("title" => "Silent", "state" => "silent", "count" => 0, "percentage" => null,"class" => "bg-pending","tech" => true),
            array("title" => "Tech", "state" => "tech", "count" => 0, "percentage" => null,"class" => "bg-pending","tech" => true),
            array("title" => "Virksomhed", "state" => "company", "count" => 0, "percentage" => null,"class" => "bg-pending","tech" => false),
            array("title" => "Ordre", "state" => "order", "count" => 0, "percentage" => null,"class" => "bg-pending","tech" => false),
            array("title" => "Forsendelse", "state" => "shipment", "count" => 0, "percentage" => null,"class" => "bg-pending","tech" => false),
        );


        // Foreach stats, find in state list and add to count, keep track of total
        $totalCount = 0;

        foreach($blockList as $stat) {
            // Antag at $stat->count_id (eller lignende) indeholder antal og $stat->company_state indeholder status-id
            $count = $stat->antal;
            $state = $stat->kategori;
            $totalCount += $count;

            $found = false;
            foreach($stateList as &$stateItem) {
                if ($stateItem["state"] == $state) {
                    $stateItem["count"] += $count;
                    $found = true;
                    break;
                }
            }

        }

        // For each state list calculate percentage
        foreach($stateList as &$stateItem) {
            $stateItem["percentage"] = $totalCount > 0 ? round(($stateItem["count"] / $totalCount) * 100, 2) : 0;
        }


        ?>

        <!-- Kunder Fejl -->
        <div class="card">
            <h3>
                <span>Antal fejlbeskeder</span>
                <span class="icon text-danger">⚠️</span>
            </h3>
            <?php

            $techUser = array(50);
            $isTechUser = in_array(\router::$systemUser->id, $techUser);

            foreach($stateList as $state) {

                if(!$state["tech"] || $isTechUser) {

                ?><div class="distribution-item">
                <span class="label"><?php echo htmlspecialchars($state["title"]); ?></span>
                <div class="bar">
                    <div class="bar-fill <?php echo $state["class"]; ?>" style="width: <?php echo $state["percentage"]; ?>%;"></div>
                </div>
                <span class="value"><?php echo number_format($state["count"], 0, ',', '.'); ?></span>
                </div><?php
                }
            }

            ?>
        </div>
    </div><?php

}

}