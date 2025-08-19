<?php

namespace GFUnit\valgshop\contributionmargin;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * Hent shop data med medarbejder antal og budget
     */
    public function getShopData()
    {
        // Set correct content type for JSON
        header('Content-Type: application/json');

        try {
            if (!isset($_GET["shop_id"]) || !(int)$_GET["shop_id"]) {
                echo json_encode([
                    "status" => 0,
                    "error" => "Manglende eller ugyldigt shop ID"
                ]);
                exit;
            }

            $shop_id = (int)$_GET["shop_id"];

            // Hent shop data med budget og medarbejder antal
            $sql = "
                SELECT 
                    s.id,
                    s.name,
                    s.localisation as language_id,
                    sm.user_count,
                    sm.budget,
                    sm.multiple_budgets_data
                FROM shop s
                LEFT JOIN shop_metadata sm ON s.id = sm.shop_id
                WHERE s.id = " . $shop_id;

            $shopData = \Shop::find_by_sql($sql);

            if (empty($shopData)) {
                echo json_encode([
                    "status" => 0,
                    "error" => "Shop ikke fundet med ID: " . $shop_id
                ]);
                exit;
            }
         
            // Check if shop_metadata exists
            $data = $shopData[0]->attributes;
            if (empty($data['user_count']) || empty($data['budget'])) {
                echo json_encode([
                    "status" => 0,
                    "error" => "Der mangler enten at blive sat budget i ordreskemaet eller angive antal medarbejdere!"
                ]);
                exit;
            }

            echo json_encode([
                "status" => 1,
                "data" => $data
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Database fejl: " . $e->getMessage(),
                "debug_info" => [
                    "shop_id" => $_GET["shop_id"] ?? 'ikke sat',
                    "file" => $e->getFile(),
                    "line" => $e->getLine()
                ]
            ]);
        }
    }

    /**
     * Hent alle reservationer for en shop med varenummer og priser
     */
    public function getReservationData()
    {
        // Set correct content type for JSON
        header('Content-Type: application/json');

        if (!isset($_GET["shop_id"]) || !(int)$_GET["shop_id"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Manglende eller ugyldigt shop ID"
            ]);
            exit;
        }

        $shop_id = (int)$_GET["shop_id"];

        // Hent language_id fra shop
        $shopLang = \Shop::find_by_sql("SELECT localisation FROM shop WHERE id = " . $shop_id);
        $language_id = !empty($shopLang) ? $shopLang[0]->attributes['localisation'] : 1;
        $language_id = ($language_id === 5) ? 1 : $language_id; // Svensk = dansk

        $sql = "
            SELECT 
                pr.id,
                pr.present_id,
                pr.model_id,
                pr.quantity,
                pm.model_present_no as item_no,
                pm.model_name,
                pm.price as model_price,
                ni.unit_cost,
                ni.sale_price,
                ni.description as item_description,
                ni.assembly_bom,
                CASE 
                    WHEN ni.assembly_bom = 1 THEN 'SAM'
                    ELSE 'ENKELT'
                END as item_type
            FROM present_reservation pr
            LEFT JOIN present_model pm ON pr.model_id = pm.model_id AND pm.language_id = " . $language_id . "
            LEFT JOIN navision_item ni ON pm.model_present_no = ni.no AND ni.language_id = " . $language_id . "
            WHERE pr.shop_id = " . $shop_id . "
            AND pr.quantity > 0
            ORDER BY pm.model_name ASC";

        $reservations = \PresentReservation::find_by_sql($sql);

        $data = [];
        if ($reservations) {
            foreach ($reservations as $reservation) {
                $data[] = $reservation->attributes;
            }
        }

        echo json_encode([
            "status" => 1,
            "data" => $data,
            "language_id" => $language_id
        ]);
    }

    /**
     * Hent BOM komponenter for et SAM varenummer
     */
    public function getBomComponents()
    {
        // Set correct content type for JSON
        header('Content-Type: application/json');

        if (!isset($_GET["item_no"]) || !isset($_GET["language_id"])) {
            echo json_encode([
                "status" => 0,
                "error" => "Manglende parametre"
            ]);
            exit;
        }

        $item_no = $_GET["item_no"];
        $language_id = (int)$_GET["language_id"];
        $language_id = ($language_id === 5) ? 1 : $language_id;

        $sql = "
            SELECT 
                nb.no as component_no,
                nb.description as component_description,
                nb.quantity_per,
                ni.unit_cost as component_unit_cost,
                ni.sale_price as component_sale_price,
                ni.description as nav_description
            FROM navision_bomitem nb
            LEFT JOIN navision_item ni ON nb.no = ni.no AND ni.language_id = " . $language_id . "
            WHERE nb.parent_item_no = '" . $item_no . "'
            AND nb.language_id = " . $language_id . "
            AND nb.deleted IS NULL
            ORDER BY nb.no ASC";

        $components = \NavisionBomitem::find_by_sql($sql);

        $data = [];
        if ($components) {
            foreach ($components as $component) {
                $data[] = $component->attributes;
            }
        }

        echo json_encode([
            "status" => 1,
            "data" => $data
        ]);
    }

    /**
     * Beregn dækningsbidrag for hele shoppen
     */
    public function calculateContributionMargin()
    {
        // Set correct content type for JSON
        header('Content-Type: application/json');

        if (!isset($_POST["shop_id"]) || !(int)$_POST["shop_id"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Manglende shop ID"
            ]);
            exit;
        }

        $shop_id = (int)$_POST["shop_id"];
        $simple_mode = isset($_POST["simple"]) ? true : false;

        try {
            // Hent shop data
            $shopData = $this->getShopDataInternal($shop_id);
            if (empty($shopData)) {
                throw new \Exception("Shop data ikke fundet for shop ID: " . $shop_id);
            }

            $reservationData = $this->getReservationDataInternal($shop_id);
            if (empty($reservationData)) {
                if ($simple_mode) {
                    echo json_encode([
                        "status" => 0,
                        "error" => "Ingen reservationer fundet"
                    ]);
                    exit;
                } else {
                    echo json_encode([
                        "status" => 0,
                        "error" => "Ingen reservationer fundet for shop ID: " . $shop_id
                    ]);
                    exit;
                }
            }

            $total_employees = $shopData['user_count'] ?? 0;
            $budget = $shopData['budget'] ?? 0;

            // Beregn total budget (budget er pr. gave)
            $total_budget = $budget * $total_employees;

            $total_reservations = 0;
            foreach ($reservationData as $item) {
                $total_reservations += ($item['quantity'] ?? 0);
            }

            // Beregn ratio hvis reservationer overstiger medarbejdere
            $ratio = 1;
            if ($total_reservations > $total_employees && $total_employees > 0) {
                $ratio = $total_employees / $total_reservations;
            }

            $calculations = [];
            $total_cost = 0;
            $total_sale_value = 0;

            foreach ($reservationData as $item) {
                $adjusted_quantity = round(($item['quantity'] ?? 0) * $ratio);

                if ($adjusted_quantity == 0) continue;

                // Brug budget som salgspris pr. gave
                $sale_price_per_item = $budget; // Budget er salgspris pr. gave
                $item_sale_value = $sale_price_per_item * $adjusted_quantity;

                $item_cost = 0;
                $cost_calculation_reliable = true;

                // Hvis det er en SAM vare, beregn kostpris fra komponenter
                if (($item['assembly_bom'] ?? 0) == 1) {
                    $components = $this->getBomComponentsInternal($item['item_no'] ?? '', $shopData['language_id'] ?? 1);
                    $sam_cost_per_unit = 0;

                    foreach ($components as $component) {
                        $component_unit_cost = $component['component_unit_cost'] ?? 0;
                        $quantity_per = $component['quantity_per'] ?? 1;

                        if ($component_unit_cost == 0) {
                            $cost_calculation_reliable = false;
                        }

                        $component_cost = $component_unit_cost * $quantity_per;
                        $sam_cost_per_unit += $component_cost;
                    }

                    $item_cost = $sam_cost_per_unit * $adjusted_quantity;
                } else {
                    $unit_cost = $item['unit_cost'] ?? 0;
                    if ($unit_cost == 0) {
                        $cost_calculation_reliable = false;
                    }
                    $item_cost = $unit_cost * $adjusted_quantity;
                }

                $contribution_margin = $item_sale_value - $item_cost;
                $margin_percent = $item_sale_value > 0 ? ($contribution_margin / $item_sale_value) * 100 : 0;

                $calculations[] = [
                    'present_id' => $item['present_id'] ?? null, // Ensure present_id is always set
                    'item_no' => $item['item_no'] ?? '',
                    'model_name' => $item['model_name'] ?? '',
                    'original_quantity' => $item['quantity'] ?? 0,
                    'adjusted_quantity' => $adjusted_quantity,
                    'item_type' => $item['item_type'] ?? 'ENKELT',
                    'unit_cost' => round($item['unit_cost'] ?? 0, 2),
                    'budget_price' => round($budget, 2), // Budget som salgspris
                    'total_cost' => round($item_cost, 2),
                    'total_sale_value' => round($item_sale_value, 2),
                    'contribution_margin' => round($contribution_margin, 2),
                    'margin_percent' => round($margin_percent, 2),
                    'cost_reliable' => $cost_calculation_reliable,
                    'has_missing_data' => !$cost_calculation_reliable ||
                        empty($item['item_no']) ||
                        empty($item['model_name'])
                ];

                $total_cost += $item_cost;
                $total_sale_value += $item_sale_value;
            }

            $total_contribution_margin = $total_sale_value - $total_cost;
            $total_margin_percent = $total_sale_value > 0 ? ($total_contribution_margin / $total_sale_value) * 100 : 0;

            // Count items with missing data
            $items_with_missing_data = 0;
            $unreliable_cost_items = 0;
            foreach ($calculations as $calc) {
                if ($calc['has_missing_data']) {
                    $items_with_missing_data++;
                }
                if (!$calc['cost_reliable']) {
                    $unreliable_cost_items++;
                }
            }

            // Data quality error indicator (1 if any problems, 0 if all good)
            $data_quality_error = ($items_with_missing_data > 0 || $unreliable_cost_items > 0) ? 1 : 0;

            if ($simple_mode) {
                // Simple JSON output
                echo json_encode([
                    "status" => 1,
                    "okonomi" => [
                        "total_kostpris" => round($total_cost, 2),
                        "total_salgspris" => round($total_sale_value, 2)
                    ],
                    "daekningsbidrag" => [
                        "total_db" => round($total_contribution_margin, 2),
                        "db_procent" => round($total_margin_percent, 2)
                    ],
                    "datakvalitet_fejl" => $data_quality_error
                ]);
            } else {
                // Full response
                echo json_encode([
                    "status" => 1,
                    "data" => [
                        "shop_info" => $shopData,
                        "calculations" => $calculations,
                        "summary" => [
                            "total_employees" => $total_employees,
                            "total_reservations" => $total_reservations,
                            "adjustment_ratio" => round($ratio, 4),
                            "budget" => round($budget, 2),
                            "total_budget" => round($total_budget, 2),
                            "total_cost" => round($total_cost, 2),
                            "total_sale_value" => round($total_sale_value, 2),
                            "total_contribution_margin" => round($total_contribution_margin, 2),
                            "total_margin_percent" => round($total_margin_percent, 2),
                            "budget_vs_sale_value" => round($total_budget - $total_sale_value, 2),
                            "items_with_missing_data" => $items_with_missing_data,
                            "unreliable_cost_items" => $unreliable_cost_items,
                            "total_calculation_items" => count($calculations)
                        ]
                    ]
                ]);
            }

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved beregning: " . $e->getMessage(),
                "debug_info" => [
                    "shop_id" => $shop_id,
                    "file" => $e->getFile(),
                    "line" => $e->getLine()
                ]
            ]);
        }
    }

    /**
     * Interne hjælpefunktioner
     */
    private function getShopDataInternal($shop_id)
    {
        $sql = "
            SELECT 
                s.id,
                s.name,
                s.localisation as language_id,
                sm.user_count,
                sm.budget
            FROM shop s
            LEFT JOIN shop_metadata sm ON s.id = sm.shop_id
            WHERE s.id = " . $shop_id;

        $result = \Shop::find_by_sql($sql);
        return !empty($result) ? $result[0]->attributes : [];
    }

    private function getReservationDataInternal($shop_id)
    {
        $shopData = $this->getShopDataInternal($shop_id);
        $language_id = $shopData['language_id'] ?? 1;
        $language_id = ($language_id === 5) ? 1 : $language_id;

        $sql = "
            SELECT 
                pr.quantity,
                pr.present_id,
                pm.model_present_no as item_no,
                pm.model_name,
                pm.price as model_price,
                ni.unit_cost,
                ni.assembly_bom,
                CASE 
                    WHEN ni.assembly_bom = 1 THEN 'SAM'
                    ELSE 'ENKELT'
                END as item_type
            FROM present_reservation pr
            LEFT JOIN present_model pm ON pr.model_id = pm.model_id AND pm.language_id = " . $language_id . "
            LEFT JOIN navision_item ni ON pm.model_present_no = ni.no AND ni.language_id = " . $language_id . "
            WHERE pr.shop_id = " . $shop_id . "
            AND pr.quantity > 0";

        $result = \PresentReservation::find_by_sql($sql);
        $data = [];
        if ($result) {
            foreach ($result as $item) {
                $data[] = $item->attributes;
            }
        }
        return $data;
    }

    private function getBomComponentsInternal($item_no, $language_id)
    {
        $language_id = ($language_id === 5) ? 1 : $language_id;

        $sql = "
            SELECT 
                nb.no as component_no,
                nb.quantity_per,
                ni.unit_cost as component_unit_cost
            FROM navision_bomitem nb
            LEFT JOIN navision_item ni ON nb.no = ni.no AND ni.language_id = " . $language_id . "
            WHERE nb.parent_item_no = '" . $item_no . "'
            AND nb.language_id = " . $language_id . "
            AND nb.deleted IS NULL";

        $result = \NavisionBomitem::find_by_sql($sql);
        $data = [];
        if ($result) {
            foreach ($result as $item) {
                $data[] = $item->attributes;
            }
        }
        return $data;
    }
}