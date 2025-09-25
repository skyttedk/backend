<?php

namespace GFUnit\valgshop\adaptaccuracy;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * Hent shop data til dropdown
     */
    public function getShopList()
    {
        header('Content-Type: application/json');

        try {
            $sql = "
                SELECT 
                    s.id,
                    s.name,
                    COUNT(pr.id) as reservation_count
                FROM gavefabrikken2024.shop s
                LEFT JOIN gavefabrikken2024.present_reservation pr ON s.id = pr.shop_id 
                WHERE s.active = 1 
                AND (pr.adapt_1 IS NOT NULL OR pr.adapt_2 IS NOT NULL OR pr.adapt_3 IS NOT NULL)
                GROUP BY s.id, s.name
                HAVING reservation_count > 0
                ORDER BY s.name";

            $shops = \Shop::find_by_sql($sql);

            $data = [];
            if ($shops) {
                foreach ($shops as $shop) {
                    $data[] = $shop->attributes;
                }
            }

            echo json_encode([
                "status" => 1,
                "data" => $data
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Database fejl: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Analyser adapt nøjagtighed for en shop
     */
    public function analyzeAdaptAccuracy()
    {
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
            $date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : null;
            $date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : null;

            // Hent shop info
            $shop_info = $this->getShopInfo($shop_id);

            // Hent reservations med adapt data
            $reservations = $this->getReservationsWithAdapt($shop_id);

            // Hent faktiske ordrer
            $actual_orders = $this->getActualOrders($shop_id, $date_from, $date_to);

            // Analyser nøjagtighed
            $analysis = $this->performAccuracyAnalysis($reservations, $actual_orders, $shop_info);

            // Generer grafikdata
            $chart_data = $this->generateChartData($analysis);

            echo json_encode([
                "status" => 1,
                "data" => [
                    "shop_info" => $shop_info,
                    "analysis" => $analysis,
                    "chart_data" => $chart_data,
                    "analysis_period" => [
                        "from" => $date_from,
                        "to" => $date_to
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved analyse: " . $e->getMessage(),
                "debug_info" => [
                    "file" => $e->getFile(),
                    "line" => $e->getLine()
                ]
            ]);
        }
    }

    /**
     * Sammenlign alle shops
     */
    public function compareAllShops()
    {
        header('Content-Type: application/json');

        try {
            $date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : null;
            $date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : null;

            // Hent alle relevante shops
            $shops_sql = "
                SELECT DISTINCT s.id, s.name
                FROM gavefabrikken2024.shop s
                INNER JOIN gavefabrikken2024.present_reservation pr ON s.id = pr.shop_id
                WHERE s.active = 1 
                AND (pr.adapt_1 IS NOT NULL OR pr.adapt_2 IS NOT NULL OR pr.adapt_3 IS NOT NULL)
                ORDER BY s.name";

            $shops = \Shop::find_by_sql($shops_sql);

            $comparison_data = [];
            $summary_stats = [
                'total_shops' => 0,
                'total_products' => 0,
                'overall_accuracy' => [
                    'adapt_1' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0],
                    'adapt_2' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0],
                    'adapt_3' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0]
                ]
            ];

            if ($shops) {
                foreach ($shops as $shop) {
                    $shop_data = $shop->attributes;
                    $shop_id = $shop_data['id'];

                    $shop_info = $this->getShopInfo($shop_id);
                    $reservations = $this->getReservationsWithAdapt($shop_id);
                    $actual_orders = $this->getActualOrders($shop_id, $date_from, $date_to);
                    $analysis = $this->performAccuracyAnalysis($reservations, $actual_orders, $shop_info);

                    $comparison_data[] = [
                        'shop_id' => $shop_id,
                        'shop_name' => $shop_data['name'],
                        'analysis' => $analysis
                    ];

                    // Akkumuler summary statistikker
                    $summary_stats['total_shops']++;
                    $summary_stats['total_products'] += count($analysis['product_analyses']);

                    foreach (['adapt_1', 'adapt_2', 'adapt_3'] as $adapt) {
                        $summary_stats['overall_accuracy'][$adapt]['correct'] += $analysis['overall_accuracy'][$adapt]['correct'];
                        $summary_stats['overall_accuracy'][$adapt]['total'] += $analysis['overall_accuracy'][$adapt]['total'];
                    }
                }
            }

            // Beregn overall accuracy percentages
            foreach ($summary_stats['overall_accuracy'] as $adapt => &$stats) {
                if ($stats['total'] > 0) {
                    $stats['accuracy_percent'] = round(($stats['correct'] / $stats['total']) * 100, 2);
                }
            }

            // Generer sammenligning grafikdata
            $comparison_chart_data = $this->generateComparisonChartData($comparison_data);

            echo json_encode([
                "status" => 1,
                "data" => [
                    "comparison_data" => $comparison_data,
                    "summary_stats" => $summary_stats,
                    "chart_data" => $comparison_chart_data,
                    "analysis_period" => [
                        "from" => $date_from,
                        "to" => $date_to
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved sammenligning: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Hent detaljeret produktanalyse
     */
    public function getProductDetails()
    {
        header('Content-Type: application/json');

        try {
            if (!isset($_GET["shop_id"]) || !isset($_GET["present_id"])) {
                echo json_encode([
                    "status" => 0,
                    "error" => "Manglende parametre"
                ]);
                exit;
            }

            $shop_id = (int)$_GET["shop_id"];
            $present_id = (int)$_GET["present_id"];
            $model_id = isset($_GET["model_id"]) ? (int)$_GET["model_id"] : 0;

            $product_details = $this->getProductDetailAnalysis($shop_id, $present_id, $model_id);

            echo json_encode([
                "status" => 1,
                "data" => $product_details
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved hentning af produktdetaljer: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Interne hjælpefunktioner
     */
    private function getShopInfo($shop_id)
    {
        $sql = "
            SELECT 
                s.id,
                s.name,
                s.localisation as language_id,
                sm.user_count,
                sm.budget,
                -- Beregn procent valgt og total ordrer for autopilot logik
                (
                    SELECT COUNT(DISTINCT o.id) 
                    FROM gavefabrikken2024.order o 
                    WHERE o.shop_id = s.id AND o.is_demo = 0
                ) as total_orders,
                (
                    SELECT COUNT(DISTINCT su.id) 
                    FROM gavefabrikken2024.shop_user su 
                    WHERE su.shop_id = s.id
                ) as total_users,
                CASE 
                    WHEN (SELECT COUNT(DISTINCT su.id) FROM gavefabrikken2024.shop_user su WHERE su.shop_id = s.id) > 0
                    THEN ROUND(
                        (COUNT(DISTINCT o.shopuser_id) * 100.0) / 
                        (SELECT COUNT(DISTINCT su.id) FROM gavefabrikken2024.shop_user su WHERE su.shop_id = s.id), 2
                    )
                    ELSE 0
                END as procent_selected
            FROM gavefabrikken2024.shop s
            LEFT JOIN gavefabrikken2024.shop_metadata sm ON s.id = sm.shop_id
            LEFT JOIN gavefabrikken2024.order o ON s.id = o.shop_id AND o.is_demo = 0
            WHERE s.id = " . $shop_id . "
            GROUP BY s.id, s.name, s.localisation, sm.user_count, sm.budget";

        $result = \Shop::find_by_sql($sql);
        return !empty($result) ? $result[0]->attributes : [];
    }

    private function getReservationsWithAdapt($shop_id)
    {
        $sql = "
            SELECT 
                pr.id,
                pr.shop_id,
                pr.present_id,
                pr.model_id,
                pr.quantity,
                pr.adapt_1,
                pr.adapt_2,
                pr.adapt_3,
                pr.update_time,
                pr.is_close,
                pr.warning_level,
                pr.current_level,
                pr.autotopilot,
                pm.model_name,
                pm.model_present_no,
                p.name as present_name,
                ni.description as nav_name,
                ni.is_external,
                ni.type as navision_type,
                (
                    SELECT COUNT(*) 
                    FROM gavefabrikken2024.order o 
                    WHERE o.present_id = pr.present_id 
                    AND o.present_model_id = pr.model_id 
                    AND o.shop_id = pr.shop_id 
                    AND o.is_demo = 0
                ) as order_count
            FROM gavefabrikken2024.present_reservation pr
            LEFT JOIN gavefabrikken2024.present_model pm ON pr.model_id = pm.model_id AND pm.language_id = 1
            LEFT JOIN gavefabrikken2024.present p ON pr.present_id = p.id
            LEFT JOIN gavefabrikken2024.navision_item ni ON pm.model_present_no = ni.no AND ni.language_id = 1 AND ni.deleted IS NULL
            WHERE pr.shop_id = " . (int)$shop_id . "
            AND (pr.adapt_1 IS NOT NULL OR pr.adapt_2 IS NOT NULL OR pr.adapt_3 IS NOT NULL)
            HAVING order_count >= 5
            ORDER BY pr.present_id, pr.model_id";

        $result = \PresentReservation::find_by_sql($sql);

        $data = [];
        if ($result) {
            foreach ($result as $item) {
                $data[] = $item->attributes;
            }
        }
        return $data;
    }

    private function getActualOrders($shop_id, $date_from = null, $date_to = null)
    {
        $date_filter = "";
        if ($date_from && $date_to) {
            $date_filter = "AND o.order_timestamp BETWEEN '" . $date_from . " 00:00:00' AND '" . $date_to . " 23:59:59'";
        } elseif ($date_from) {
            $date_filter = "AND o.order_timestamp >= '" . $date_from . " 00:00:00'";
        } elseif ($date_to) {
            $date_filter = "AND o.order_timestamp <= '" . $date_to . " 23:59:59'";
        }

        $sql = "
            SELECT 
                o.present_id,
                o.present_model_id as model_id,
                COUNT(*) as order_count,
                o.present_name,
                o.present_model_name,
                MIN(o.order_timestamp) as first_order,
                MAX(o.order_timestamp) as last_order
            FROM gavefabrikken2024.`order` o
            WHERE o.shop_id = " . (int)$shop_id . "
            AND o.is_demo = 0
            " . $date_filter . "
            GROUP BY o.present_id, o.present_model_id
            ORDER BY o.present_id, o.present_model_id";

        $result = \Order::find_by_sql($sql);

        $data = [];
        if ($result) {
            foreach ($result as $item) {
                $data[] = $item->attributes;
            }
        }
        return $data;
    }

    private function performAccuracyAnalysis($reservations, $actual_orders, $shop_info = [])
    {
        $product_analyses = [];
        $overall_accuracy = [
            'adapt_1' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0],
            'adapt_2' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0],
            'adapt_3' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0]
        ];
        $autopilot_overall_accuracy = [
            'adapt_1' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0],
            'adapt_2' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0],
            'adapt_3' => ['correct' => 0, 'total' => 0, 'accuracy_percent' => 0]
        ];

        foreach ($reservations as $reservation) {
            $present_id = $reservation['present_id'];
            $model_id = $reservation['model_id'];

            // Find faktiske ordrer for dette produkt
            $actual_quantity = $this->getActualQuantityForProduct($actual_orders, $present_id, $model_id);

            $product_analysis = [
                'present_id' => $present_id,
                'model_id' => $model_id,
                'present_name' => $reservation['present_name'] ?? '',
                'model_name' => $reservation['model_name'] ?? '',
                'model_present_no' => $reservation['model_present_no'] ?? '',
                'reservation_quantity' => $reservation['quantity'],
                'actual_quantity' => $actual_quantity,
                'is_external' => $reservation['is_external'] ?? 0,
                'is_close' => $reservation['is_close'] ?? 0,
                'autotopilot' => $reservation['autotopilot'] ?? 0,
                'warning_level' => $reservation['warning_level'] ?? 0,
                'current_level' => $reservation['current_level'] ?? 0,
                'order_count' => $reservation['order_count'] ?? 0,
                'navision_type' => $reservation['navision_type'] ?? '',
                'adapt_predictions' => [
                    'adapt_1' => $reservation['adapt_1'],
                    'adapt_2' => $reservation['adapt_2'],
                    'adapt_3' => $reservation['adapt_3']
                ],
                'accuracy_analysis' => [],
                'autopilot_analysis' => []
            ];

            // Analyser nøjagtighed for hver adapt med original og autopilot-justeret forecast
            foreach (['adapt_1', 'adapt_2', 'adapt_3'] as $adapt_field) {
                $original_predicted = $reservation[$adapt_field];
                
                // Original accuracy analyse
                $accuracy_analysis = $this->calculateAccuracy($original_predicted, $actual_quantity);
                $product_analysis['accuracy_analysis'][$adapt_field] = $accuracy_analysis;

                // Autopilot justeret forecast analyse
                if ($original_predicted !== null && !empty($shop_info)) {
                    $autopilot_data = $this->getAutopilotAdjustedForecast($original_predicted, $shop_info, $reservation);
                    $adjusted_predicted = $autopilot_data['adjusted_forecast'];
                    $autopilot_accuracy = $this->calculateAccuracy($adjusted_predicted, $actual_quantity, true);
                    
                    $product_analysis['autopilot_analysis'][$adapt_field] = [
                        'original_predicted' => $original_predicted,
                        'adjusted_predicted' => $adjusted_predicted,
                        'adjustment_reason' => $autopilot_data['adjustment_reason'],
                        'forecast_procent' => $autopilot_data['forecast_procent'],
                        'adapt_stage' => $autopilot_data['adapt_stage'],
                        'zero_forecast' => $autopilot_data['zero_forecast'],
                        'accuracy' => $autopilot_accuracy
                    ];

                    // Opdater autopilot overall statistikker
                    $autopilot_overall_accuracy[$adapt_field]['total']++;
                    if ($autopilot_accuracy['is_accurate']) {
                        $autopilot_overall_accuracy[$adapt_field]['correct']++;
                    }
                }

                // Opdater original overall statistikker
                if ($original_predicted !== null) {
                    $overall_accuracy[$adapt_field]['total']++;
                    if ($accuracy_analysis['is_accurate']) {
                        $overall_accuracy[$adapt_field]['correct']++;
                    }
                }
            }

            $product_analyses[] = $product_analysis;
        }

        // Beregn overall accuracy percentages
        foreach ($overall_accuracy as $adapt => &$stats) {
            if ($stats['total'] > 0) {
                $stats['accuracy_percent'] = round(($stats['correct'] / $stats['total']) * 100, 2);
            }
        }

        foreach ($autopilot_overall_accuracy as $adapt => &$stats) {
            if ($stats['total'] > 0) {
                $stats['accuracy_percent'] = round(($stats['correct'] / $stats['total']) * 100, 2);
            }
        }

        return [
            'product_analyses' => $product_analyses,
            'overall_accuracy' => $overall_accuracy,
            'autopilot_overall_accuracy' => $autopilot_overall_accuracy,
            'total_products_analyzed' => count($product_analyses),
            'shop_info' => $shop_info
        ];
    }

    private function getActualQuantityForProduct($orders, $present_id, $model_id)
    {
        foreach ($orders as $order) {
            if ($order['present_id'] == $present_id && $order['model_id'] == $model_id) {
                return $order['order_count'];
            }
        }
        return 0;
    }

    private function calculateAccuracy($predicted, $actual, $is_autopilot = false)
    {
        if ($predicted === null) {
            return [
                'predicted' => null,
                'actual' => $actual,
                'difference' => null,
                'accuracy_percent' => null,
                'is_accurate' => false,
                'accuracy_category' => 'no_prediction',
                'hit_rate' => 'N/A'
            ];
        }

        $difference = $actual - $predicted;
        $accuracy_percent = 0;

        if ($predicted > 0) {
            // For autopilot calculations, we use a buffer-aware accuracy
            if ($is_autopilot) {
                // For autopilot, we consider it successful if actual demand was met
                // The goal is to have enough inventory to satisfy demand + reasonable buffer
                if ($actual <= $predicted) {
                    // We had enough - calculate based on overallocation efficiency
                    $overallocation = $predicted - $actual;
                    $efficiency_ratio = $actual / $predicted;
                    $accuracy_percent = round($efficiency_ratio * 100, 2);
                    
                    // Bonus for minimal waste (<=20% overallocation)
                    if ($efficiency_ratio >= 0.8) {
                        $accuracy_percent = min(100, $accuracy_percent + (($efficiency_ratio - 0.8) * 50));
                    }
                } else {
                    // We didn't have enough - penalty for stockout
                    $stockout_ratio = $actual / $predicted;
                    $accuracy_percent = round(max(0, 100 - (($stockout_ratio - 1) * 100)), 2);
                }
            } else {
                // Original accuracy calculation - direct comparison
                $accuracy_percent = round((1 - abs($difference) / max($predicted, $actual)) * 100, 2);
            }
        } elseif ($actual == 0 && $predicted == 0) {
            $accuracy_percent = 100;
        }

        // Definer accuracy kategorier
        $accuracy_category = 'poor';
        if ($accuracy_percent >= 90) {
            $accuracy_category = 'excellent';
        } elseif ($accuracy_percent >= 75) {
            $accuracy_category = 'good';
        } elseif ($accuracy_percent >= 50) {
            $accuracy_category = 'fair';
        }

        // Beregn hit rate - for autopilot we focus on service level
        $hit_rate = 'miss';
        if ($is_autopilot) {
            if ($actual <= $predicted && $predicted <= $actual * 1.3) {
                $hit_rate = 'excellent'; // Good service level with reasonable buffer
            } elseif ($actual <= $predicted) {
                $hit_rate = 'good'; // No stockout but maybe too much buffer
            } elseif ($predicted >= $actual * 0.8) {
                $hit_rate = 'fair'; // Minor stockout
            }
        } else {
            if ($predicted == $actual) {
                $hit_rate = 'exact';
            } elseif (abs($difference) <= 1) {
                $hit_rate = 'close';
            } elseif ($accuracy_percent >= 75) {
                $hit_rate = 'good';
            }
        }

        // For autopilot, accuracy threshold is different (focus on service level)
        $is_accurate = $is_autopilot ? 
            ($actual <= $predicted && $accuracy_percent >= 70) : 
            ($accuracy_percent >= 85) || ($predicted == $actual);

        return [
            'predicted' => $predicted,
            'actual' => $actual,
            'difference' => $difference,
            'accuracy_percent' => max(0, $accuracy_percent),
            'is_accurate' => $is_accurate,
            'accuracy_category' => $accuracy_category,
            'hit_rate' => $hit_rate
        ];
    }

    /**
     * Autopilot beregningsfunktioner baseret på autopanel.php logik
     */
    private function getAdaptStage($total_orders, $procent_selected)
    {
        $adapt = 0;
        if ($total_orders < 500) {
            if ($procent_selected > 20) $adapt = 1;
            if ($procent_selected > 40) $adapt = 2;
            if ($procent_selected > 50) $adapt = 3;
        } elseif ($total_orders >= 500 && $total_orders < 1000) {
            if ($procent_selected > 15) $adapt = 1;
            if ($procent_selected > 30) $adapt = 2;
            if ($procent_selected > 50) $adapt = 3;
        } elseif ($total_orders > 1000) {
            if ($procent_selected > 10) $adapt = 1;
            if ($procent_selected > 20) $adapt = 2;
            if ($procent_selected > 50) $adapt = 3;
        }
        return $adapt;
    }

    private function getForecastProcent($total_orders, $procent_selected)
    {
        $forecastProcent = 1.3; // Standard
        
        if ($total_orders < 500) {
            if ($procent_selected > 20) $forecastProcent = 1.2;
            if ($procent_selected > 40) $forecastProcent = 1.1;
            if ($procent_selected > 50) $forecastProcent = 1.05;
            if ($procent_selected > 75) $forecastProcent = 1.05;
        } elseif ($total_orders >= 500 && $total_orders < 1000) {
            if ($procent_selected > 15) $forecastProcent = 1.2;
            if ($procent_selected > 30) $forecastProcent = 1.1;
            if ($procent_selected > 50) $forecastProcent = 1.05;
        } elseif ($total_orders > 1000) {
            if ($procent_selected > 10) $forecastProcent = 1.2;
            if ($procent_selected > 20) $forecastProcent = 1.1;
            if ($procent_selected > 50) $forecastProcent = 1.05;
        }
        
        return $forecastProcent;
    }

    private function getZeroSelectedForecast($total_orders, $procent_selected)
    {
        $zeroforecast = 0;
        if ($total_orders < 500) {
            if ($procent_selected > 20) $zeroforecast = 5;
            if ($procent_selected > 40) $zeroforecast = 3;
            if ($procent_selected > 50) $zeroforecast = 2;
        } elseif ($total_orders >= 500 && $total_orders < 1000) {
            if ($procent_selected > 15) $zeroforecast = 7;
            if ($procent_selected > 30) $zeroforecast = 5;
            if ($procent_selected > 50) $zeroforecast = 3;
        } elseif ($total_orders > 1000) {
            if ($procent_selected > 10) $zeroforecast = 10;
            if ($procent_selected > 20) $zeroforecast = 8;
            if ($procent_selected > 50) $zeroforecast = 5;
        }
        return $zeroforecast;
    }

    private function calculateStockBuffer($stock_available)
    {
        if ($stock_available <= 0) return 0;
        $buffer = ceil($stock_available * 0.05);
        return $buffer > 5 ? $buffer : 5;
    }

    private function getAutopilotAdjustedForecast($original_predict, $shop_info, $product_data)
    {
        $total_orders = $shop_info['total_orders'] ?? 0;
        $procent_selected = $shop_info['procent_selected'] ?? 0;
        $is_external = $product_data['is_external'] ?? 0;
        $order_count = $product_data['order_count'] ?? 0;
        
        // Eksterne gaver er beskyttede
        if ($is_external > 0) {
            return [
                'adjusted_forecast' => $original_predict,
                'adjustment_reason' => 'protected_external',
                'forecast_procent' => 1.0,
                'stock_buffer' => 0,
                'zero_forecast' => 0,
                'adapt_stage' => 0
            ];
        }

        // Zero selected forecast for produkter med 0 ordrer
        $zero_forecast = 0;
        if ($order_count == 0 && $is_external == 0) {
            $zero_forecast = $this->getZeroSelectedForecast($total_orders, $procent_selected);
        }

        $forecast_procent = $this->getForecastProcent($total_orders, $procent_selected);
        $adapt_stage = $this->getAdaptStage($total_orders, $procent_selected);
        
        // Stock buffer beregning (hvis vi havde stock data)
        $stock_buffer = 0; // Vi har ikke stock data i accuracy analysen
        
        $adjusted_forecast = $original_predict;
        $adjustment_reason = 'none';
        
        // Juster forecast baseret på autopilot logik
        if ($order_count == 0 && $is_external == 0) {
            $adjusted_forecast = $zero_forecast;
            $adjustment_reason = 'zero_selected';
        } elseif ($original_predict > 0) {
            $adjusted_forecast = ceil($original_predict * $forecast_procent);
            $adjustment_reason = 'forecast_procent';
            
            // Ekstra 1.5x multiplier hvis under 27% har valgt
            if ($procent_selected < 27) {
                $adjusted_forecast = ceil($adjusted_forecast * 1.5);
                $adjustment_reason = 'low_selection_boost';
            }
        }

        return [
            'adjusted_forecast' => $adjusted_forecast,
            'adjustment_reason' => $adjustment_reason,
            'forecast_procent' => $forecast_procent,
            'stock_buffer' => $stock_buffer,
            'zero_forecast' => $zero_forecast,
            'adapt_stage' => $adapt_stage
        ];
    }

    private function generateChartData($analysis)
    {
        // Accuracy by adapt field chart
        $accuracy_chart = [];
        foreach ($analysis['overall_accuracy'] as $adapt => $stats) {
            $accuracy_chart[] = [
                'adapt' => $adapt,
                'accuracy' => $stats['accuracy_percent'],
                'total' => $stats['total']
            ];
        }

        // Hit rate distribution
        $hit_rate_data = ['exact' => 0, 'close' => 0, 'good' => 0, 'miss' => 0];
        foreach ($analysis['product_analyses'] as $product) {
            foreach (['adapt_1', 'adapt_2', 'adapt_3'] as $adapt) {
                if (isset($product['accuracy_analysis'][$adapt]['hit_rate'])) {
                    $hit_rate = $product['accuracy_analysis'][$adapt]['hit_rate'];
                    if (isset($hit_rate_data[$hit_rate])) {
                        $hit_rate_data[$hit_rate]++;
                    }
                }
            }
        }

        // Prediction vs actual scatter plot data
        $scatter_data = [];
        foreach ($analysis['product_analyses'] as $product) {
            foreach (['adapt_1', 'adapt_2', 'adapt_3'] as $adapt) {
                $acc = $product['accuracy_analysis'][$adapt];
                if ($acc['predicted'] !== null) {
                    $scatter_data[] = [
                        'adapt' => $adapt,
                        'predicted' => $acc['predicted'],
                        'actual' => $acc['actual'],
                        'product_name' => $product['present_name']
                    ];
                }
            }
        }

        return [
            'accuracy_by_adapt' => $accuracy_chart,
            'hit_rate_distribution' => $hit_rate_data,
            'prediction_vs_actual' => $scatter_data
        ];
    }

    private function generateComparisonChartData($comparison_data)
    {
        $shop_accuracy_data = [];
        $adapt_performance = ['adapt_1' => [], 'adapt_2' => [], 'adapt_3' => []];

        foreach ($comparison_data as $shop_data) {
            $shop_name = $shop_data['shop_name'];
            $analysis = $shop_data['analysis'];

            // Shop overall accuracy
            $avg_accuracy = 0;
            $valid_adapts = 0;

            foreach (['adapt_1', 'adapt_2', 'adapt_3'] as $adapt) {
                $acc_percent = $analysis['overall_accuracy'][$adapt]['accuracy_percent'];
                if ($analysis['overall_accuracy'][$adapt]['total'] > 0) {
                    $avg_accuracy += $acc_percent;
                    $valid_adapts++;

                    $adapt_performance[$adapt][] = [
                        'shop' => $shop_name,
                        'accuracy' => $acc_percent
                    ];
                }
            }

            if ($valid_adapts > 0) {
                $avg_accuracy = round($avg_accuracy / $valid_adapts, 2);
            }

            $shop_accuracy_data[] = [
                'shop' => $shop_name,
                'accuracy' => $avg_accuracy,
                'products' => $analysis['total_products_analyzed']
            ];
        }

        return [
            'shop_comparison' => $shop_accuracy_data,
            'adapt_performance' => $adapt_performance
        ];
    }

    private function getProductDetailAnalysis($shop_id, $present_id, $model_id)
    {
        // Hent detaljeret produktinfo med historik
        $sql = "
            SELECT 
                pr.*,
                pm.model_name,
                pm.model_present_no,
                p.name as present_name,
                ni.description as nav_name,
                ni.is_external,
                ni.type as navision_type,
                ni.unit_price,
                ni.description as navision_description
            FROM gavefabrikken2024.present_reservation pr
            LEFT JOIN gavefabrikken2024.present_model pm ON pr.model_id = pm.model_id AND pm.language_id = 1
            LEFT JOIN gavefabrikken2024.present p ON pr.present_id = p.id
            LEFT JOIN gavefabrikken2024.navision_item ni ON pm.model_present_no = ni.no AND ni.language_id = 1 AND ni.deleted IS NULL
            WHERE pr.shop_id = " . (int)$shop_id . "
            AND pr.present_id = " . (int)$present_id . "
            AND pr.model_id = " . (int)$model_id;

        $reservation = \PresentReservation::find_by_sql($sql);

        if (empty($reservation)) {
            return null;
        }

        $reservation_data = $reservation[0]->attributes;

        // Hent adapt historik fra log tabellen
        $adapt_history_sql = "
            SELECT 
                adapt_0, adapt_1, adapt_2, adapt_3,
                quantity, old_quantity,
                autotopilot, warning_level, current_level,
                created
            FROM gavefabrikken2024.present_reservation_log
            WHERE shop_id = " . (int)$shop_id . "
            AND present_id = " . (int)$present_id . "
            AND model_id = " . (int)$model_id . "
            ORDER BY created DESC
            LIMIT 20";

        $adapt_history = \PresentReservationLog::find_by_sql($adapt_history_sql);
        $adapt_history_data = [];
        if ($adapt_history) {
            foreach ($adapt_history as $history) {
                $adapt_history_data[] = $history->attributes;
            }
        }

        // Hent ordrehistorik for dette produkt
        $orders_sql = "
            SELECT 
                DATE(order_timestamp) as order_date,
                COUNT(*) as daily_orders,
                order_timestamp
            FROM gavefabrikken2024.`order`
            WHERE shop_id = " . (int)$shop_id . "
            AND present_id = " . (int)$present_id . "
            AND present_model_id = " . (int)$model_id . "
            AND is_demo = 0
            GROUP BY DATE(order_timestamp)
            ORDER BY order_date";

        $order_history = \Order::find_by_sql($orders_sql);

        $history_data = [];
        if ($order_history) {
            foreach ($order_history as $order) {
                $history_data[] = $order->attributes;
            }
        }

        return [
            'reservation' => $reservation_data,
            'order_history' => $history_data,
            'adapt_history' => $adapt_history_data
        ];
    }
}