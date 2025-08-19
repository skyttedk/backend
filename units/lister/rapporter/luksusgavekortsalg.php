<?php

namespace GFUnit\lister\rapporter;

class Luksusgavekortsalg extends BaseReport
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getReportName() { return "Luksusgavekortsalg"; }
    public function getReportCode() { return "Luksusgavekortsalg"; }
    public function getReportDescription() { return "Hent salg af luksusgavekort efter ugenr"; }

    public function defineParameters()
    {
        return array(

        );
    }

    // Genererer rapporten
    public function generateReport($parameters)
    {

        $sql = "SELECT 
    YEAR(created_datetime - INTERVAL (WEEKDAY(created_datetime) + 1) DAY) AS Aar,
    WEEK(created_datetime, 1) AS UgeNr,
    CONCAT(
        COUNT(CASE WHEN shop_name = 'Luksusgavekortet 200' THEN id END), 
        ' (', 
        SUM(CASE WHEN shop_name = 'Luksusgavekortet 200' THEN quantity ELSE 0 END),
        ')'
    ) AS 'Luksusgavekortet 200',
    CONCAT(
        COUNT(CASE WHEN shop_name = 'Luksusgavekortet 400' THEN id END), 
        ' (', 
        SUM(CASE WHEN shop_name = 'Luksusgavekortet 400' THEN quantity ELSE 0 END),
        ')'
    ) AS 'Luksusgavekortet 400',
    CONCAT(
        COUNT(CASE WHEN shop_name = 'Luksusgavekortet 640' THEN id END), 
        ' (', 
        SUM(CASE WHEN shop_name = 'Luksusgavekortet 640' THEN quantity ELSE 0 END),
        ')'
    ) AS 'Luksusgavekortet 640',
    CONCAT(
        COUNT(CASE WHEN shop_name = 'Luksusgavekortet 800' THEN id END), 
        ' (', 
        SUM(CASE WHEN shop_name = 'Luksusgavekortet 800' THEN quantity ELSE 0 END),
        ')'
    ) AS 'Luksusgavekortet 800',
    CONCAT(
        COUNT(id), 
        ' (', 
        SUM(quantity),
        ')'
    ) AS Total
FROM 
    company_order 
WHERE 
    shop_id IN (SELECT shop_id FROM `cardshop_settings` WHERE `concept_parent` LIKE 'LUKS') 
    AND order_state IN (4,5,9,10) 
    AND created_datetime >= DATE_SUB(NOW(), INTERVAL 10 WEEK)
GROUP BY 
    Aar,
    UgeNr
ORDER BY 
    created_datetime ASC
";

        $exporter = new ExportCSVSimple("luksusgavekort-salg-til-".date("d-m-Y").".csv");
        $exporter->exportSql($sql);

    }

}
