<?php

namespace GFUnit\apps\salgstats;
use GFBiz\units\UnitController;

class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * Get sales statistics with filters
     */
    public function getSalesStats()
    {
        $dateFrom = isset($_POST["date_from"]) ? $_POST["date_from"] : date('Y-m-01');
        $dateTo = isset($_POST["date_to"]) ? $_POST["date_to"] : date('Y-m-t');
        $salesperson = isset($_POST["salesperson"]) ? $_POST["salesperson"] : 'all';
        $groupBy = isset($_POST["group_by"]) ? $_POST["group_by"] : 'month';
        $conceptCode = isset($_POST["concept_code"]) ? $_POST["concept_code"] : 'all';

        try {
            $results = $this->getDataAcrossYears($dateFrom, $dateTo, $salesperson, $groupBy, $conceptCode);
            echo json_encode(array("status" => 1, "data" => $results));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Database error: " . $e->getMessage()));
        }
    }

    /**
     * Get language data across multiple years
     */
    private function getLanguageDataAcrossYears($dateFrom, $dateTo, $salesperson)
    {
        $fromYear = intval(substr($dateFrom, 0, 4));
        $toYear = intval(substr($dateTo, 0, 4));

        $languageResults = array();

        for ($year = $fromYear; $year <= $toYear; $year++) {
            $yearDateFrom = $dateFrom;
            $yearDateTo = $dateTo;

            if ($year > $fromYear) {
                $yearDateFrom = $year . '-01-01';
            }
            if ($year < $toYear) {
                $yearDateTo = $year . '-12-31';
            }

            $databaseName = $this->getDatabaseName($year);

            $sql = "SELECT
                        CASE
                            WHEN cardshop_settings.language_code = 1 THEN 'Danmark'
                            WHEN cardshop_settings.language_code = 4 THEN 'Norge'
                            WHEN cardshop_settings.language_code = 5 THEN 'Sverige'
                            ELSE CONCAT('Ukendt (', cardshop_settings.language_code, ')')
                        END as land,
                        COUNT(shop_user.id) AS total_sold,
                        SUM(cardshop_settings.card_price) AS total_revenue
                    FROM `{$databaseName}`.`company_order`
                    INNER JOIN `{$databaseName}`.`cardshop_settings` ON cardshop_settings.shop_id = company_order.shop_id
                    INNER JOIN `{$databaseName}`.`shop_user` ON shop_user.company_order_id = company_order.id
                    WHERE company_order.order_state IN(10, 11)
                        AND company_order.created_datetime >= '" . $this->escapeString($yearDateFrom) . "'
                        AND company_order.created_datetime <= '" . $this->escapeString($yearDateTo) . " 23:59:59'
                        AND company_order.company_name NOT LIKE '%replacement%'
                        AND shop_user.is_demo = 0
                        AND shop_user.shutdown = 0
                        AND shop_user.blocked = 0
                        AND shop_user.is_giftcertificate = 1";

            if ($salesperson !== 'all') {
                $sql .= " AND company_order.salesperson = '" . $this->escapeString($salesperson) . "'";
            }

            $sql .= " GROUP BY cardshop_settings.language_code";

            try {
                $yearResults = \Dbsqli::getSql2($sql);
                // Merge results by country
                foreach ($yearResults as $result) {
                    $found = false;
                    for ($i = 0; $i < count($languageResults); $i++) {
                        if ($languageResults[$i]['land'] === $result['land']) {
                            $languageResults[$i]['total_sold'] += intval($result['total_sold']);
                            $languageResults[$i]['total_revenue'] += floatval($result['total_revenue']);
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $languageResults[] = $result;
                    }
                }
            } catch (Exception $e) {
                error_log("Error querying language data for year $year: " . $e->getMessage());
            }
        }

        // Sort by total_sold desc
        usort($languageResults, function($a, $b) {
            return intval($b['total_sold']) - intval($a['total_sold']);
        });

        return $languageResults;
    }

    /**
     * Get concept data across multiple years
     */
    private function getConceptDataAcrossYears($dateFrom, $dateTo, $salesperson)
    {
        $fromYear = intval(substr($dateFrom, 0, 4));
        $toYear = intval(substr($dateTo, 0, 4));

        $conceptResults = array();

        for ($year = $fromYear; $year <= $toYear; $year++) {
            $yearDateFrom = $dateFrom;
            $yearDateTo = $dateTo;

            if ($year > $fromYear) {
                $yearDateFrom = $year . '-01-01';
            }
            if ($year < $toYear) {
                $yearDateTo = $year . '-12-31';
            }

            $databaseName = $this->getDatabaseName($year);

            $sql = "SELECT
                        cardshop_settings.concept_code,
                        COALESCE(cardshop_settings.concept_name, cardshop_settings.concept_code) as concept_name,
                        AVG(cardshop_settings.card_price) as avg_card_price,
                        COUNT(shop_user.id) AS total_sold,
                        SUM(cardshop_settings.card_price) AS total_revenue
                    FROM `{$databaseName}`.`company_order`
                    INNER JOIN `{$databaseName}`.`cardshop_settings` ON cardshop_settings.shop_id = company_order.shop_id
                    INNER JOIN `{$databaseName}`.`shop_user` ON shop_user.company_order_id = company_order.id
                    WHERE company_order.order_state IN(10, 11)
                        AND company_order.created_datetime >= '" . $this->escapeString($yearDateFrom) . "'
                        AND company_order.created_datetime <= '" . $this->escapeString($yearDateTo) . " 23:59:59'
                        AND company_order.company_name NOT LIKE '%replacement%'
                        AND shop_user.is_demo = 0
                        AND shop_user.shutdown = 0
                        AND shop_user.blocked = 0
                        AND shop_user.is_giftcertificate = 1
                        AND cardshop_settings.concept_code IS NOT NULL
                        AND cardshop_settings.concept_code != ''";

            if ($salesperson !== 'all') {
                $sql .= " AND company_order.salesperson = '" . $this->escapeString($salesperson) . "'";
            }

            $sql .= " GROUP BY cardshop_settings.concept_code, cardshop_settings.concept_name";

            try {
                $yearResults = \Dbsqli::getSql2($sql);
                // Merge results by concept
                foreach ($yearResults as $result) {
                    $found = false;
                    for ($i = 0; $i < count($conceptResults); $i++) {
                        if ($conceptResults[$i]['concept_code'] === $result['concept_code']) {
                            $conceptResults[$i]['total_sold'] += intval($result['total_sold']);
                            $conceptResults[$i]['total_revenue'] += floatval($result['total_revenue']);
                            // Recalculate average price
                            $conceptResults[$i]['avg_card_price'] = $conceptResults[$i]['total_revenue'] / $conceptResults[$i]['total_sold'];
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $conceptResults[] = $result;
                    }
                }
            } catch (Exception $e) {
                error_log("Error querying concept data for year $year: " . $e->getMessage());
            }
        }

        // Sort by total_sold desc
        usort($conceptResults, function($a, $b) {
            return intval($b['total_sold']) - intval($a['total_sold']);
        });

        return $conceptResults;
    }
    private function getDataAcrossYears($dateFrom, $dateTo, $salesperson, $groupBy, $conceptCode)
    {
        $fromYear = intval(substr($dateFrom, 0, 4));
        $toYear = intval(substr($dateTo, 0, 4));

        $allResults = array();

        // If spanning multiple years, query each database separately
        for ($year = $fromYear; $year <= $toYear; $year++) {
            $yearDateFrom = $dateFrom;
            $yearDateTo = $dateTo;

            // Adjust dates for current year
            if ($year > $fromYear) {
                $yearDateFrom = $year . '-01-01';
            }
            if ($year < $toYear) {
                $yearDateTo = $year . '-12-31';
            }

            $sql = $this->buildSalesQuery($yearDateFrom, $yearDateTo, $salesperson, $groupBy, $conceptCode, $year);

            try {
                $yearResults = \Dbsqli::getSql2($sql);
                $allResults = array_merge($allResults, $yearResults);
            } catch (Exception $e) {
                // Log error but continue with other years
                error_log("Error querying year $year: " . $e->getMessage());
            }
        }

        return $allResults;
    }

    /**
     * Get the correct database name for a given year
     */
    private function getDatabaseName($year)
    {
        return "gavefabrikken" . $year;
    }
    public function getConcepts()
    {
        $sql = "SELECT DISTINCT concept_code, concept_name
                FROM cardshop_settings
                WHERE concept_code IS NOT NULL AND concept_code != ''
                ORDER BY concept_name";

        try {
            $results = \Dbsqli::getSql2($sql);
            echo json_encode(array("status" => 1, "data" => $results));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Database error: " . $e->getMessage()));
        }
    }

    /**
     * Get sales by language
     */
    public function getSalesByLanguage()
    {
        $dateFrom = isset($_POST["date_from"]) ? $_POST["date_from"] : date('Y-m-01');
        $dateTo = isset($_POST["date_to"]) ? $_POST["date_to"] : date('Y-m-t');
        $salesperson = isset($_POST["salesperson"]) ? $_POST["salesperson"] : 'all';

        $sql = "SELECT
                    CASE
                        WHEN cardshop_settings.language_code = 1 THEN 'Danmark'
                        WHEN cardshop_settings.language_code = 4 THEN 'Norge'
                        WHEN cardshop_settings.language_code = 5 THEN 'Sverige'
                        ELSE CONCAT('Ukendt (', cardshop_settings.language_code, ')')
                    END as land,
                    COUNT(shop_user.id) AS total_sold
                FROM `company_order`
                INNER JOIN cardshop_settings ON cardshop_settings.shop_id = company_order.shop_id
                INNER JOIN shop_user ON shop_user.company_order_id = company_order.id
                WHERE company_order.order_state IN(10, 11)
                    AND company_order.created_datetime >= '" . $this->escapeString($dateFrom) . "'
                    AND company_order.created_datetime <= '" . $this->escapeString($dateTo) . " 23:59:59'
                    AND company_order.company_name NOT LIKE '%replacement%'
                    AND shop_user.is_demo = 0
                    AND shop_user.shutdown = 0
                    AND shop_user.blocked = 0
                    AND shop_user.is_giftcertificate = 1";

        if ($salesperson !== 'all') {
            $sql .= " AND company_order.salesperson = '" . $this->escapeString($salesperson) . "'";
        }

        $sql .= " GROUP BY cardshop_settings.language_code
                  ORDER BY total_sold DESC";

        try {
            $results = \Dbsqli::getSql2($sql);
            echo json_encode(array("status" => 1, "data" => $results));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Database error: " . $e->getMessage()));
        }
    }

    /**
     * Get sales by concept
     */
    public function getSalesByConcept()
    {
        $dateFrom = isset($_POST["date_from"]) ? $_POST["date_from"] : date('Y-m-01');
        $dateTo = isset($_POST["date_to"]) ? $_POST["date_to"] : date('Y-m-t');
        $salesperson = isset($_POST["salesperson"]) ? $_POST["salesperson"] : 'all';

        $sql = "SELECT
                    cardshop_settings.concept_code,
                    COALESCE(cardshop_settings.concept_name, cardshop_settings.concept_code) as concept_name,
                    COUNT(shop_user.id) AS total_sold
                FROM `company_order`
                INNER JOIN cardshop_settings ON cardshop_settings.shop_id = company_order.shop_id
                INNER JOIN shop_user ON shop_user.company_order_id = company_order.id
                WHERE company_order.order_state IN(10, 11)
                    AND company_order.created_datetime >= '" . $this->escapeString($dateFrom) . "'
                    AND company_order.created_datetime <= '" . $this->escapeString($dateTo) . " 23:59:59'
                    AND company_order.company_name NOT LIKE '%replacement%'
                    AND shop_user.is_demo = 0
                    AND shop_user.shutdown = 0
                    AND shop_user.blocked = 0
                    AND shop_user.is_giftcertificate = 1
                    AND cardshop_settings.concept_code IS NOT NULL
                    AND cardshop_settings.concept_code != ''";

        if ($salesperson !== 'all') {
            $sql .= " AND company_order.salesperson = '" . $this->escapeString($salesperson) . "'";
        }

        $sql .= " GROUP BY cardshop_settings.concept_code, cardshop_settings.concept_name
                  ORDER BY total_sold DESC";

        try {
            $results = \Dbsqli::getSql2($sql);
            echo json_encode(array("status" => 1, "data" => $results));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Database error: " . $e->getMessage()));
        }
    }

    /**
     * Build dynamic sales query based on filters
     */
    private function buildSalesQuery($dateFrom, $dateTo, $salesperson, $groupBy, $conceptCode, $year = null)
    {
        // Determine year from dateFrom if not provided
        if ($year === null) {
            $year = intval(substr($dateFrom, 0, 4));
        }

        $databaseName = $this->getDatabaseName($year);

        // Base SELECT and GROUP BY based on groupBy parameter
        switch ($groupBy) {
            case 'week':
                $selectDate = "CONCAT(YEAR(company_order.created_datetime), '-W', LPAD(WEEK(company_order.created_datetime, 1), 2, '0')) as period";
                $groupByDate = "YEAR(company_order.created_datetime), WEEK(company_order.created_datetime, 1)";
                break;
            case 'day':
                $selectDate = "DATE(company_order.created_datetime) as period";
                $groupByDate = "DATE(company_order.created_datetime)";
                break;
            case 'year':
                $selectDate = "YEAR(company_order.created_datetime) as period";
                $groupByDate = "YEAR(company_order.created_datetime)";
                break;
            default: // month
                $selectDate = "DATE_FORMAT(company_order.created_datetime, '%Y-%m') as period";
                $groupByDate = "DATE_FORMAT(company_order.created_datetime, '%Y-%m')";
                break;
        }

        $sql = "SELECT
                    $selectDate,
                    CASE
                        WHEN cardshop_settings.language_code = 1 THEN 'Danmark'
                        WHEN cardshop_settings.language_code = 4 THEN 'Norge'
                        WHEN cardshop_settings.language_code = 5 THEN 'Sverige'
                        ELSE CONCAT('Ukendt (', cardshop_settings.language_code, ')')
                    END as land,
                    cardshop_settings.concept_code,
                    COALESCE(cardshop_settings.concept_name, cardshop_settings.concept_code) as concept_name,
                    cardshop_settings.card_price,
                    COUNT(shop_user.id) AS total_sold,
                    (COUNT(shop_user.id) * cardshop_settings.card_price) AS total_revenue
                FROM `{$databaseName}`.`company_order`
                INNER JOIN `{$databaseName}`.`cardshop_settings` ON cardshop_settings.shop_id = company_order.shop_id
                INNER JOIN `{$databaseName}`.`shop_user` ON shop_user.company_order_id = company_order.id
                WHERE company_order.order_state IN(10, 11)
                    AND company_order.created_datetime >= '" . $this->escapeString($dateFrom) . "'
                    AND company_order.created_datetime <= '" . $this->escapeString($dateTo) . " 23:59:59'
                    AND company_order.company_name NOT LIKE '%replacement%'
                    AND shop_user.is_demo = 0
                    AND shop_user.shutdown = 0
                    AND shop_user.blocked = 0
                    AND shop_user.is_giftcertificate = 1";

        if ($salesperson !== 'all') {
            $sql .= " AND company_order.salesperson = '" . $this->escapeString($salesperson) . "'";
        }

        if ($conceptCode !== 'all') {
            $sql .= " AND cardshop_settings.concept_code = '" . $this->escapeString($conceptCode) . "'";
        }

        $sql .= " GROUP BY $groupByDate, cardshop_settings.language_code, cardshop_settings.concept_code, cardshop_settings.card_price
                  ORDER BY period DESC, land, concept_name";

        return $sql;
    }

    /**
     * Get available salespersons
     */
    public function getSalespersons()
    {
        $currentYear = date('Y');
        $databaseName = $this->getDatabaseName($currentYear);

        $sql = "SELECT DISTINCT salesperson
                FROM `{$databaseName}`.`company_order`
                WHERE salesperson IS NOT NULL AND salesperson != ''
                ORDER BY salesperson";

        try {
            $results = \Dbsqli::getSql2($sql);
            echo json_encode(array("status" => 1, "data" => $results));
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Database error: " . $e->getMessage()));
        }
    }

    /**
     * Export sales data to Excel
     */
    public function exportExcel()
    {
        $dateFrom = isset($_POST["date_from"]) ? $_POST["date_from"] : date('Y-m-01');
        $dateTo = isset($_POST["date_to"]) ? $_POST["date_to"] : date('Y-m-t');
        $salesperson = isset($_POST["salesperson"]) ? $_POST["salesperson"] : 'all';
        $groupBy = isset($_POST["group_by"]) ? $_POST["group_by"] : 'month';
        $conceptCode = isset($_POST["concept_code"]) ? $_POST["concept_code"] : 'all';

        try {
            $results = $this->getDataAcrossYears($dateFrom, $dateTo, $salesperson, $groupBy, $conceptCode);

            // Set headers for Excel download
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename=salgstats_' . date('Y-m-d') . '.xls');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Create Excel XML content
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

            // Styles
            echo '<Styles>' . "\n";
            echo '<Style ss:ID="Header">' . "\n";
            echo '<Font ss:Bold="1" ss:Size="12"/>' . "\n";
            echo '<Interior ss:Color="#667eea" ss:Pattern="Solid"/>' . "\n";
            echo '<Font ss:Color="#FFFFFF"/>' . "\n";
            echo '</Style>' . "\n";
            echo '<Style ss:ID="Currency">' . "\n";
            echo '<NumberFormat ss:Format="Currency"/>' . "\n";
            echo '</Style>' . "\n";
            echo '</Styles>' . "\n";

            // Worksheet
            echo '<Worksheet ss:Name="Salgstats">' . "\n";
            echo '<Table>' . "\n";

            // Headers
            echo '<Row>' . "\n";
            echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Periode</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Land</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Koncept Kode</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Koncept Navn</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Kortpris</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Antal Solgt</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Header"><Data ss:Type="String">Total Omsætning</Data></Cell>' . "\n";
            echo '</Row>' . "\n";

            // Data rows
            foreach ($results as $row) {
                echo '<Row>' . "\n";
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['period']) . '</Data></Cell>' . "\n";
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['land']) . '</Data></Cell>' . "\n";
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['concept_code']) . '</Data></Cell>' . "\n";
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['concept_name']) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . floatval($row['card_price']) . '</Data></Cell>' . "\n";
                echo '<Cell><Data ss:Type="Number">' . intval($row['total_sold']) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="Currency"><Data ss:Type="Number">' . floatval($row['total_revenue']) . '</Data></Cell>' . "\n";
                echo '</Row>' . "\n";
            }

            echo '</Table>' . "\n";
            echo '</Worksheet>' . "\n";
            echo '</Workbook>' . "\n";

            exit;

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Export error: " . $e->getMessage()));
        }
    }
    public function exportCSV()
    {
        $dateFrom = isset($_POST["date_from"]) ? $_POST["date_from"] : date('Y-m-01');
        $dateTo = isset($_POST["date_to"]) ? $_POST["date_to"] : date('Y-m-t');
        $salesperson = isset($_POST["salesperson"]) ? $_POST["salesperson"] : 'all';
        $groupBy = isset($_POST["group_by"]) ? $_POST["group_by"] : 'month';
        $conceptCode = isset($_POST["concept_code"]) ? $_POST["concept_code"] : 'all';

        try {
            $results = $this->getDataAcrossYears($dateFrom, $dateTo, $salesperson, $groupBy, $conceptCode);

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=salgstats_' . date('Y-m-d') . '.csv');

            // Create CSV content
            $output = fopen('php://output', 'w');

            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add headers
            fputcsv($output, ['Periode', 'Land', 'Koncept Kode', 'Koncept Navn', 'Kortpris', 'Antal Solgt', 'Total Omsætning'], ';');

            // Add data
            foreach ($results as $row) {
                fputcsv($output, [
                    $row['period'],
                    $row['land'],
                    $row['concept_code'],
                    $row['concept_name'],
                    number_format($row['card_price'], 2, ',', '.'),
                    $row['total_sold'],
                    number_format($row['total_revenue'], 2, ',', '.')
                ], ';');
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Export error: " . $e->getMessage()));
        }
    }

    /**
     * Escape string for SQL query
     */
    private function escapeString($str)
    {
        return addslashes($str);
    }
}