<?php

namespace GFUnit\apps\salesstatistics;

use GFBiz\units\UnitController;

class Controller extends UnitController
{
    private $databases = [
        'gavefabrikken2024' => 'gavefabrikken2024',
        'gavefabrikken2025' => 'gavefabrikken2025'
    ];

    public function __construct()
    {
        parent::__construct(__FILE__);
    }
    
    public function test()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Controller fungerer korrekt',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function getSalesData($database = 'gavefabrikken2024', $startDate = null, $endDate = null)
    {
        header('Content-Type: application/json');
        
        if (!isset($this->databases[$database])) {
            echo json_encode(['error' => 'Invalid database']);
            return;
        }

        if (!$startDate) {
            $startDate = date('Y-m-01', strtotime('-6 months'));
        }
        
        if (!$endDate) {
            $endDate = date('Y-m-t');
        }
        
        $dbName = $this->databases[$database];
        
        try {
            $sql = "SELECT 
                CASE 
                    WHEN `{$dbName}`.cardshop_settings.language_code = 1 THEN 'Danmark' 
                    WHEN `{$dbName}`.cardshop_settings.language_code = 4 THEN 'Norge' 
                    WHEN `{$dbName}`.cardshop_settings.language_code = 5 THEN 'Sverige' 
                    ELSE CONCAT('Ukendt (', `{$dbName}`.cardshop_settings.language_code, ')')
                END AS land,
                DATE_FORMAT(`{$dbName}`.company_order.created_datetime, '%Y-%m') AS month_year,
                `{$dbName}`.cardshop_settings.concept_code,
                COALESCE(`{$dbName}`.cardshop_settings.concept_name, `{$dbName}`.cardshop_settings.concept_code) AS concept_name,
                (`{$dbName}`.cardshop_settings.card_price / 100) AS pris_kr,
                COUNT(`{$dbName}`.shop_user.id) AS total_sold,
                SUM(`{$dbName}`.cardshop_settings.card_price / 100) AS total_omsaetning
            FROM `{$dbName}`.company_order
            INNER JOIN `{$dbName}`.cardshop_settings ON `{$dbName}`.cardshop_settings.shop_id = `{$dbName}`.company_order.shop_id
            INNER JOIN `{$dbName}`.shop_user ON `{$dbName}`.shop_user.company_order_id = `{$dbName}`.company_order.id
            WHERE
                `{$dbName}`.company_order.order_state not IN (8,6,7) 
                AND `{$dbName}`.company_order.created_datetime >= ?
                AND `{$dbName}`.company_order.created_datetime < ?
                AND `{$dbName}`.company_order.company_name NOT LIKE '%replacement%' 
                AND `{$dbName}`.shop_user.is_demo = 0 
                AND `{$dbName}`.shop_user.shutdown = 0 
                AND `{$dbName}`.shop_user.blocked = 0 
                AND `{$dbName}`.shop_user.is_giftcertificate = 1 
                AND `{$dbName}`.company_order.salesperson = 'import' 
            GROUP BY
                `{$dbName}`.cardshop_settings.language_code,
                `{$dbName}`.cardshop_settings.concept_code,
                `{$dbName}`.cardshop_settings.concept_name,
                `{$dbName}`.cardshop_settings.card_price,
                DATE_FORMAT(`{$dbName}`.company_order.created_datetime, '%Y-%m')
            ORDER BY
                month_year DESC, land DESC, concept_name";

            $results = \CompanyOrder::find_by_sql($sql, [$startDate, $endDate]);
            
            $dataArray = [];
            if ($results) {
                foreach ($results as $row) {
                    $dataArray[] = [
                        'land' => isset($row->land) ? $row->land : 'Ukendt',
                        'month_year' => isset($row->month_year) ? $row->month_year : '',
                        'concept_code' => isset($row->concept_code) ? $row->concept_code : '',
                        'concept_name' => isset($row->concept_name) ? $row->concept_name : '',
                        'pris_kr' => isset($row->pris_kr) ? floatval($row->pris_kr) : 0,
                        'total_sold' => isset($row->total_sold) ? intval($row->total_sold) : 0,
                        'total_omsaetning' => isset($row->total_omsaetning) ? floatval($row->total_omsaetning) : 0
                    ];
                }
            }
            
            $formattedData = $this->formatDataForCharts($dataArray);
            
            // Replace placeholders with actual values for debug display
            $debugSql = $sql;
            $debugSql = preg_replace('/\?/', "'$startDate'", $debugSql, 1); // Replace first ?
            $debugSql = preg_replace('/\?/', "'$endDate'", $debugSql, 1);   // Replace second ?
            
            echo json_encode([
                'success' => true,
                'data' => $formattedData,
                'rawData' => $dataArray,
                'database' => $database,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'debug' => [
                    'sql' => $debugSql,
                    'params' => [$startDate, $endDate],
                    'resultCount' => count($dataArray)
                ]
            ]);
            
        } catch (\Exception $e) {
            $debugSql = 'SQL not available';
            if (isset($sql)) {
                $debugSql = $sql;
                $debugSql = preg_replace('/\?/', "'$startDate'", $debugSql, 1); // Replace first ?
                $debugSql = preg_replace('/\?/', "'$endDate'", $debugSql, 1);   // Replace second ?
            }
            
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => [
                    'sql' => $debugSql,
                    'params' => [$startDate, $endDate],
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'database' => $database
                ]
            ]);
        }
    }

    private function formatDataForCharts($data)
    {
        $monthlyRevenue = [];
        $countryRevenue = [];
        $conceptRevenue = [];
        $monthlySales = [];
        $conceptDetails = [];
        
        foreach ($data as $row) {
            $month = $row['month_year'];
            $country = $row['land'];
            $concept = $row['concept_name'];
            $revenue = floatval($row['total_omsaetning']);
            $sold = intval($row['total_sold']);
            
            if (!isset($monthlyRevenue[$month])) {
                $monthlyRevenue[$month] = 0;
                $monthlySales[$month] = 0;
            }
            $monthlyRevenue[$month] += $revenue;
            $monthlySales[$month] += $sold;
            
            if (!isset($countryRevenue[$country])) {
                $countryRevenue[$country] = 0;
            }
            $countryRevenue[$country] += $revenue;
            
            if (!isset($conceptRevenue[$concept])) {
                $conceptRevenue[$concept] = 0;
            }
            $conceptRevenue[$concept] += $revenue;
            
            if (!isset($conceptDetails[$concept])) {
                $conceptDetails[$concept] = [
                    'months' => [],
                    'countries' => []
                ];
            }
            
            if (!isset($conceptDetails[$concept]['months'][$month])) {
                $conceptDetails[$concept]['months'][$month] = 0;
            }
            $conceptDetails[$concept]['months'][$month] += $revenue;
            
            if (!isset($conceptDetails[$concept]['countries'][$country])) {
                $conceptDetails[$concept]['countries'][$country] = 0;
            }
            $conceptDetails[$concept]['countries'][$country] += $revenue;
        }
        
        ksort($monthlyRevenue);
        ksort($monthlySales);
        arsort($conceptRevenue);
        
        return [
            'monthlyRevenue' => $monthlyRevenue,
            'monthlySales' => $monthlySales,
            'countryRevenue' => $countryRevenue,
            'conceptRevenue' => array_slice($conceptRevenue, 0, 10, true),
            'conceptDetails' => $conceptDetails,
            'totalRevenue' => array_sum($monthlyRevenue),
            'totalSales' => array_sum($monthlySales)
        ];
    }

    public function exportCSV($database = 'gavefabrikken2024', $startDate = null, $endDate = null)
    {
        if (!isset($this->databases[$database])) {
            echo json_encode(['error' => 'Invalid database']);
            return;
        }

        if (!$startDate) {
            $startDate = date('Y-m-01', strtotime('-6 months'));
        }
        
        if (!$endDate) {
            $endDate = date('Y-m-t');
        }
        
        $dbName = $this->databases[$database];
        
        try {
            $sql = "SELECT 
                CASE 
                    WHEN `{$dbName}`.cardshop_settings.language_code = 1 THEN 'Danmark' 
                    WHEN `{$dbName}`.cardshop_settings.language_code = 4 THEN 'Norge' 
                    WHEN `{$dbName}`.cardshop_settings.language_code = 5 THEN 'Sverige' 
                    ELSE CONCAT('Ukendt (', `{$dbName}`.cardshop_settings.language_code, ')')
                END AS land,
                DATE_FORMAT(`{$dbName}`.company_order.created_datetime, '%Y-%m') AS month_year,
                `{$dbName}`.cardshop_settings.concept_code,
                COALESCE(`{$dbName}`.cardshop_settings.concept_name, `{$dbName}`.cardshop_settings.concept_code) AS concept_name,
                (`{$dbName}`.cardshop_settings.card_price / 100) AS pris_kr,
                COUNT(`{$dbName}`.shop_user.id) AS total_sold,
                SUM(`{$dbName}`.cardshop_settings.card_price / 100) AS total_omsaetning
            FROM `{$dbName}`.company_order
            INNER JOIN `{$dbName}`.cardshop_settings ON `{$dbName}`.cardshop_settings.shop_id = `{$dbName}`.company_order.shop_id
            INNER JOIN `{$dbName}`.shop_user ON `{$dbName}`.shop_user.company_order_id = `{$dbName}`.company_order.id
            WHERE
                `{$dbName}`.company_order.order_state not IN (8,6,7) 
                AND `{$dbName}`.company_order.created_datetime >= ?
                AND `{$dbName}`.company_order.created_datetime < ?
                AND `{$dbName}`.company_order.company_name NOT LIKE '%replacement%' 
                AND `{$dbName}`.shop_user.is_demo = 0 
                AND `{$dbName}`.shop_user.shutdown = 0 
                AND `{$dbName}`.shop_user.blocked = 0 
                AND `{$dbName}`.shop_user.is_giftcertificate = 1 
                AND `{$dbName}`.company_order.salesperson = 'import' 
            GROUP BY
                `{$dbName}`.cardshop_settings.language_code,
                `{$dbName}`.cardshop_settings.concept_code,
                `{$dbName}`.cardshop_settings.concept_name,
                `{$dbName}`.cardshop_settings.card_price,
                DATE_FORMAT(`{$dbName}`.company_order.created_datetime, '%Y-%m')
            ORDER BY
                month_year DESC, land DESC, concept_name";

            $results = \CompanyOrder::find_by_sql($sql, [$startDate, $endDate]);
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="sales_statistics_' . $database . '_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($output, ['Land', 'Måned', 'Koncept Kode', 'Koncept Navn', 'Pris (kr)', 'Antal Solgt', 'Total Omsætning'], ';');
            
            foreach ($results as $row) {
                fputcsv($output, [
                    $row->land,
                    $row->month_year,
                    $row->concept_code,
                    $row->concept_name,
                    number_format($row->pris_kr, 2, ',', '.'),
                    $row->total_sold,
                    number_format($row->total_omsaetning, 2, ',', '.')
                ], ';');
            }
            
            fclose($output);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}