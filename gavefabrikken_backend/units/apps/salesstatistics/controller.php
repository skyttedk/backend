<?php

namespace GFUnit\apps\salesstatistics;

use GFBiz\units\UnitController;

class Controller extends UnitController
{
    private $databases = [
        'gavefabrikken2023' => 'gavefabrikken2023',
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

    public function getSalesData($database = 'gavefabrikken2024', $startDate = null, $endDate = null, $salesperson = 'import')
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
        
        // Handle salesperson filter
        $salespersonFilter = '';
        if ($salesperson !== 'all') {
            $salespersonFilter = " AND `{$dbName}`.company_order.salesperson = '{$salesperson}'";
        }
        
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
                {$salespersonFilter}
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

    public function exportCSV($database = 'gavefabrikken2024', $startDate = null, $endDate = null, $salesperson = 'import')
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
        
        // Handle salesperson filter
        $salespersonFilter = '';
        if ($salesperson !== 'all') {
            $salespersonFilter = " AND `{$dbName}`.company_order.salesperson = '{$salesperson}'";
        }
        
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
                {$salespersonFilter}
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

    public function getYearlyComparison($conceptCode = null, $country = null, $salesperson = 'import')
    {
        header('Content-Type: application/json');
        
        $currentYear = date('Y');
        $currentMonth = date('m');
        
        // Debug: log the parameters being received
        error_log("getYearlyComparison called with conceptCode: " . ($conceptCode ?: 'null') . 
                  ", country: " . ($country ?: 'null') . 
                  ", salesperson: " . ($salesperson ?: 'null'));
        
        // Check if salesperson exists in the database and show available salespersons
        foreach ($this->databases as $key => $dbName) {
            $year = substr($key, -4);
            
            // Show what salespersons exist in this database
            $allSalespersonsSql = "SELECT DISTINCT salesperson, COUNT(*) as count FROM `{$dbName}`.company_order WHERE salesperson IS NOT NULL GROUP BY salesperson LIMIT 10";
            $allResults = \CompanyOrder::find_by_sql($allSalespersonsSql);
            error_log("Available salespersons in {$year}:");
            if ($allResults) {
                foreach ($allResults as $row) {
                    error_log("  - '{$row->salesperson}': {$row->count} records");
                }
            }
            
            // Check specific salesperson
            if ($salesperson !== 'all') {
                $testSql = "SELECT COUNT(*) as count FROM `{$dbName}`.company_order WHERE salesperson = '{$salesperson}'";
                $testResults = \CompanyOrder::find_by_sql($testSql);
                $count = $testResults ? $testResults[0]->count : 0;
                error_log("Salesperson '{$salesperson}' found {$count} times in {$year} database");
            }
        }
        
        try {
            $yearlyData = [];
            
            foreach ($this->databases as $key => $dbName) {
                $year = substr($key, -4);
                
                // For current year, only compare up to current month
                $endMonth = ($year == $currentYear) ? $currentMonth : '12';
                $startDate = $year . '-01-01';
                $endDate = $year . '-' . str_pad($endMonth, 2, '0', STR_PAD_LEFT) . '-' . date('t', mktime(0, 0, 0, $endMonth, 1, $year));
                
                $sql = $this->buildYearlyComparisonSQL($dbName, $conceptCode, $country, $salesperson);
                
                // Debug: log the SQL query for the year
                error_log("SQL for year {$year}: " . $sql);
                error_log("Date range: {$startDate} to {$endDate}");
                
                $results = \CompanyOrder::find_by_sql($sql, [$startDate, $endDate]);
                
                // Debug: log result count
                error_log("Results count for year {$year}: " . (is_array($results) ? count($results) : '0'));
                
                $monthlyData = [];
                $totalRevenue = 0;
                $totalSales = 0;
                
                if ($results) {
                    foreach ($results as $row) {
                        $month = $row->month_year;
                        $revenue = floatval($row->total_omsaetning);
                        $sales = intval($row->total_sold);
                        
                        if (!isset($monthlyData[$month])) {
                            $monthlyData[$month] = ['revenue' => 0, 'sales' => 0];
                        }
                        
                        $monthlyData[$month]['revenue'] += $revenue;
                        $monthlyData[$month]['sales'] += $sales;
                        $totalRevenue += $revenue;
                        $totalSales += $sales;
                    }
                }
                
                $yearlyData[$year] = [
                    'monthlyData' => $monthlyData,
                    'totalRevenue' => $totalRevenue,
                    'totalSales' => $totalSales,
                    'endMonth' => $endMonth
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $yearlyData,
                'conceptCode' => $conceptCode,
                'country' => $country,
                'currentYear' => $currentYear,
                'currentMonth' => $currentMonth
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'conceptCode' => $conceptCode,
                'country' => $country
            ]);
        }
    }

    public function getAvailableConcepts($country = null, $salesperson = 'import')
    {
        header('Content-Type: application/json');
        
        try {
            $concepts = [];
            
            // Add country filter if specified
            $countryFilter = '';
            if ($country) {
                switch ($country) {
                    case 'Danmark':
                        $countryFilter = " AND `{DBNAME}`.cardshop_settings.language_code = 1";
                        break;
                    case 'Norge':
                        $countryFilter = " AND `{DBNAME}`.cardshop_settings.language_code = 4";
                        break;
                    case 'Sverige':
                        $countryFilter = " AND `{DBNAME}`.cardshop_settings.language_code = 5";
                        break;
                }
            }
            
            $salespersonFilter = '';
            if ($salesperson !== 'all') {
                $salespersonFilter = " AND `{DBNAME}`.company_order.salesperson = '{$salesperson}'";
            }
            
            foreach ($this->databases as $key => $dbName) {
                // Replace placeholder with actual database name
                $actualCountryFilter = str_replace('{DBNAME}', $dbName, $countryFilter);
                $actualSalespersonFilter = str_replace('{DBNAME}', $dbName, $salespersonFilter);
                
                $sql = "SELECT DISTINCT 
                    `{$dbName}`.cardshop_settings.concept_code,
                    COALESCE(`{$dbName}`.cardshop_settings.concept_name, `{$dbName}`.cardshop_settings.concept_code) AS concept_name
                FROM `{$dbName}`.company_order
                INNER JOIN `{$dbName}`.cardshop_settings ON `{$dbName}`.cardshop_settings.shop_id = `{$dbName}`.company_order.shop_id
                INNER JOIN `{$dbName}`.shop_user ON `{$dbName}`.shop_user.company_order_id = `{$dbName}`.company_order.id
                WHERE
                    `{$dbName}`.company_order.order_state not IN (8,6,7) 
                    AND `{$dbName}`.company_order.company_name NOT LIKE '%replacement%' 
                    AND `{$dbName}`.shop_user.is_demo = 0 
                    AND `{$dbName}`.shop_user.shutdown = 0 
                    AND `{$dbName}`.shop_user.blocked = 0 
                    AND `{$dbName}`.shop_user.is_giftcertificate = 1 
                    {$actualSalespersonFilter}
                    {$actualCountryFilter}
                ORDER BY concept_code";
                
                $results = \CompanyOrder::find_by_sql($sql);
                
                if ($results) {
                    foreach ($results as $row) {
                        $conceptKey = $row->concept_code;
                        if (!isset($concepts[$conceptKey])) {
                            $concepts[$conceptKey] = $row->concept_name;
                        }
                    }
                }
            }
            
            ksort($concepts);
            
            echo json_encode([
                'success' => true,
                'concepts' => $concepts,
                'country' => $country
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'country' => $country
            ]);
        }
    }

    private function buildYearlyComparisonSQL($dbName, $conceptCode = null, $country = null, $salesperson = 'import')
    {
        $conceptFilter = '';
        if ($conceptCode) {
            $conceptFilter = " AND `{$dbName}`.cardshop_settings.concept_code = '{$conceptCode}'";
        }
        
        $countryFilter = '';
        if ($country) {
            $languageCode = '';
            switch ($country) {
                case 'Danmark':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 1";
                    break;
                case 'Norge':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 4";
                    break;
                case 'Sverige':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 5";
                    break;
            }
            $countryFilter = $languageCode;
        }
        
        $salespersonFilter = '';
        if ($salesperson !== 'all') {
            $salespersonFilter = " AND `{$dbName}`.company_order.salesperson = '{$salesperson}'";
        }
        
        return "SELECT 
            DATE_FORMAT(`{$dbName}`.company_order.created_datetime, '%Y-%m') AS month_year,
            COUNT(`{$dbName}`.shop_user.id) AS total_sold,
            SUM(`{$dbName}`.cardshop_settings.card_price / 100) AS total_omsaetning
        FROM `{$dbName}`.company_order
        INNER JOIN `{$dbName}`.cardshop_settings ON `{$dbName}`.cardshop_settings.shop_id = `{$dbName}`.company_order.shop_id
        INNER JOIN `{$dbName}`.shop_user ON `{$dbName}`.shop_user.company_order_id = `{$dbName}`.company_order.id
        WHERE
            `{$dbName}`.company_order.order_state not IN (8,6,7) 
            AND `{$dbName}`.company_order.created_datetime >= ?
            AND `{$dbName}`.company_order.created_datetime <= ?
            AND `{$dbName}`.company_order.company_name NOT LIKE '%replacement%' 
            AND `{$dbName}`.shop_user.is_demo = 0 
            AND `{$dbName}`.shop_user.shutdown = 0 
            AND `{$dbName}`.shop_user.blocked = 0 
            AND `{$dbName}`.shop_user.is_giftcertificate = 1 
            {$salespersonFilter}
            {$conceptFilter}
            {$countryFilter}
        GROUP BY
            DATE_FORMAT(`{$dbName}`.company_order.created_datetime, '%Y-%m')
        ORDER BY
            month_year";
    }

    public function getYearToDateComparison($conceptCode = null, $country = null, $salesperson = 'import')
    {
        header('Content-Type: application/json');
        
        $currentYear = date('Y');
        $currentMonth = date('m');
        $currentDay = date('d');
        
        try {
            $yearToDateData = [];
            
            foreach ($this->databases as $key => $dbName) {
                $year = substr($key, -4);
                
                // For all years, use same end date: current month and day
                $startDate = $year . '-01-01';
                $paddedMonth = str_pad($currentMonth, 2, '0', STR_PAD_LEFT);
                $paddedDay = str_pad($currentDay, 2, '0', STR_PAD_LEFT);
                $endDate = $year . '-' . $paddedMonth . '-' . $paddedDay;
                
                $sql = $this->buildYearToDateSQL($dbName, $conceptCode, $country, $salesperson);
                
                $results = \CompanyOrder::find_by_sql($sql, [$startDate, $endDate]);
                
                $totalRevenue = 0;
                $totalSales = 0;
                
                if ($results && count($results) > 0) {
                    $result = $results[0];
                    $totalRevenue = floatval($result->total_omsaetning);
                    $totalSales = intval($result->total_sold);
                }
                
                $yearToDateData[$year] = [
                    'totalRevenue' => $totalRevenue,
                    'totalSales' => $totalSales,
                    'endDate' => $endDate
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $yearToDateData,
                'conceptCode' => $conceptCode,
                'country' => $country,
                'currentDate' => date('Y-m-d'),
                'comparisonDate' => $currentMonth . '-' . $currentDay
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'conceptCode' => $conceptCode,
                'country' => $country
            ]);
        }
    }

    private function buildYearToDateSQL($dbName, $conceptCode = null, $country = null, $salesperson = 'import')
    {
        $conceptFilter = '';
        if ($conceptCode) {
            $conceptFilter = " AND `{$dbName}`.cardshop_settings.concept_code = '{$conceptCode}'";
        }
        
        $countryFilter = '';
        if ($country) {
            $languageCode = '';
            switch ($country) {
                case 'Danmark':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 1";
                    break;
                case 'Norge':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 4";
                    break;
                case 'Sverige':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 5";
                    break;
            }
            $countryFilter = $languageCode;
        }
        
        $salespersonFilter = '';
        if ($salesperson !== 'all') {
            $salespersonFilter = " AND `{$dbName}`.company_order.salesperson = '{$salesperson}'";
        }
        
        return "SELECT 
            COUNT(`{$dbName}`.shop_user.id) AS total_sold,
            SUM(`{$dbName}`.cardshop_settings.card_price / 100) AS total_omsaetning
        FROM `{$dbName}`.company_order
        INNER JOIN `{$dbName}`.cardshop_settings ON `{$dbName}`.cardshop_settings.shop_id = `{$dbName}`.company_order.shop_id
        INNER JOIN `{$dbName}`.shop_user ON `{$dbName}`.shop_user.company_order_id = `{$dbName}`.company_order.id
        WHERE
            `{$dbName}`.company_order.order_state not IN (8,6,7) 
            AND `{$dbName}`.company_order.created_datetime >= ?
            AND `{$dbName}`.company_order.created_datetime < DATE_ADD(?, INTERVAL 1 DAY)
            AND `{$dbName}`.company_order.company_name NOT LIKE '%replacement%' 
            AND `{$dbName}`.shop_user.is_demo = 0 
            AND `{$dbName}`.shop_user.shutdown = 0 
            AND `{$dbName}`.shop_user.blocked = 0 
            AND `{$dbName}`.shop_user.is_giftcertificate = 1 
            {$salespersonFilter}
            {$conceptFilter}
            {$countryFilter}";
    }

    public function getYearlyComparisonDaily($conceptCode = null, $country = null, $salesperson = 'import')
    {
        header('Content-Type: application/json');
        
        $currentYear = date('Y');
        $currentMonth = date('m');
        $currentDay = date('d');
        
        try {
            $yearlyData = [];
            
            foreach ($this->databases as $key => $dbName) {
                $year = substr($key, -4);
                
                // For current year, only compare up to current date
                $startDate = $year . '-01-01';
                if ($year == $currentYear) {
                    $endDate = $year . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($currentDay, 2, '0', STR_PAD_LEFT);
                } else {
                    $endDate = $year . '-12-31';
                }
                
                $sql = $this->buildYearlyComparisonDailySQL($dbName, $conceptCode, $country, $salesperson);
                
                $results = \CompanyOrder::find_by_sql($sql, [$startDate, $endDate]);
                
                $dailyData = [];
                $totalRevenue = 0;
                $totalSales = 0;
                
                if ($results) {
                    foreach ($results as $row) {
                        $date = $row->order_date;
                        $revenue = floatval($row->total_omsaetning);
                        $sales = intval($row->total_sold);
                        
                        $dailyData[$date] = [
                            'revenue' => $revenue,
                            'sales' => $sales
                        ];
                        
                        $totalRevenue += $revenue;
                        $totalSales += $sales;
                    }
                }
                
                $yearlyData[$year] = [
                    'dailyData' => $dailyData,
                    'totalRevenue' => $totalRevenue,
                    'totalSales' => $totalSales,
                    'endDate' => $endDate
                ];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $yearlyData,
                'conceptCode' => $conceptCode,
                'country' => $country,
                'currentYear' => $currentYear,
                'currentDate' => date('Y-m-d')
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'conceptCode' => $conceptCode,
                'country' => $country
            ]);
        }
    }

    private function buildYearlyComparisonDailySQL($dbName, $conceptCode = null, $country = null, $salesperson = 'import')
    {
        $conceptFilter = '';
        if ($conceptCode) {
            $conceptFilter = " AND `{$dbName}`.cardshop_settings.concept_code = '{$conceptCode}'";
        }
        
        $countryFilter = '';
        if ($country) {
            $languageCode = '';
            switch ($country) {
                case 'Danmark':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 1";
                    break;
                case 'Norge':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 4";
                    break;
                case 'Sverige':
                    $languageCode = " AND `{$dbName}`.cardshop_settings.language_code = 5";
                    break;
            }
            $countryFilter = $languageCode;
        }
        
        $salespersonFilter = '';
        if ($salesperson !== 'all') {
            $salespersonFilter = " AND `{$dbName}`.company_order.salesperson = '{$salesperson}'";
        }
        
        return "SELECT 
            DATE(`{$dbName}`.company_order.created_datetime) AS order_date,
            COUNT(`{$dbName}`.shop_user.id) AS total_sold,
            SUM(`{$dbName}`.cardshop_settings.card_price / 100) AS total_omsaetning
        FROM `{$dbName}`.company_order
        INNER JOIN `{$dbName}`.cardshop_settings ON `{$dbName}`.cardshop_settings.shop_id = `{$dbName}`.company_order.shop_id
        INNER JOIN `{$dbName}`.shop_user ON `{$dbName}`.shop_user.company_order_id = `{$dbName}`.company_order.id
        WHERE
            `{$dbName}`.company_order.order_state not IN (8,6,7) 
            AND `{$dbName}`.company_order.created_datetime >= ?
            AND `{$dbName}`.company_order.created_datetime <= ?
            AND `{$dbName}`.company_order.company_name NOT LIKE '%replacement%' 
            AND `{$dbName}`.shop_user.is_demo = 0 
            AND `{$dbName}`.shop_user.shutdown = 0 
            AND `{$dbName}`.shop_user.blocked = 0 
            AND `{$dbName}`.shop_user.is_giftcertificate = 1 
            {$salespersonFilter}
            {$conceptFilter}
            {$countryFilter}
        GROUP BY
            DATE(`{$dbName}`.company_order.created_datetime)
        ORDER BY
            order_date";
    }

    private function buildSalespersonList()
    {
        $salespersons = [];
        
        foreach ($this->databases as $key => $dbName) {
            $year = substr($key, -4);
            
            $sql = "SELECT DISTINCT salesperson
                    FROM `{$dbName}`.company_order
                    WHERE order_state not IN (8,6,7) 
                    AND company_name NOT LIKE '%replacement%' 
                    AND salesperson IS NOT NULL
                    AND salesperson != ''
                    ORDER BY salesperson";
            
            $results = \CompanyOrder::find_by_sql($sql);
            
            if ($results) {
                foreach ($results as $row) {
                    $salesperson = $row->salesperson;
                    if (!isset($salespersons[$salesperson])) {
                        $salespersons[$salesperson] = [];
                    }
                    if (!in_array($year, $salespersons[$salesperson])) {
                        $salespersons[$salesperson][] = $year;
                    }
                }
            }
        }
        
        return $salespersons;
    }

    public function getAvailableSalespersons()
    {
        header('Content-Type: application/json');
        
        try {
            $salespersons = $this->buildSalespersonList();
            
            // Format the salespersons with their active years
            $formattedSalespersons = [];
            foreach ($salespersons as $salesperson => $years) {
                sort($years);
                if (count($years) > 1) {
                    $yearRange = '(' . min($years) . '-' . max($years) . ')';
                } else {
                    $yearRange = '(' . $years[0] . ')';
                }
                
                $formattedSalespersons[] = [
                    'salesperson' => $salesperson,
                    'display_name' => $salesperson . ' ' . $yearRange,
                    'years' => $years
                ];
            }
            
            // Sort by salesperson name
            usort($formattedSalespersons, function($a, $b) {
                return strcmp($a['salesperson'], $b['salesperson']);
            });
            
            echo json_encode([
                'success' => true,
                'salespersons' => $formattedSalespersons
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}