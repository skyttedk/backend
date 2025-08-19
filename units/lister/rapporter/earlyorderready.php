<?php

namespace GFUnit\lister\rapporter;

class EarlyOrderReady extends BaseReport
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getReportName() { return "Earlyorder - klar til pluk"; }
    public function getReportCode() { return "EarlyorderReady"; }
    public function getReportDescription() { return "Trækker antal varer fordelt på varenr der er klar til træk."; }

    public function defineParameters()
    {
        return array(
            "cardshops"
        );
    }

    // Genererer rapporten
    public function generateReport($parameters)
    {

        $sql = "SELECT itemno, 'description' as description, SUM(quantity) AS total_quantity
FROM (
    SELECT itemno,  quantity
    FROM shipment
    WHERE shipment_type = 'earlyorder' AND shipment_state = 1 
    AND companyorder_id IN (
        SELECT id FROM company_order 
        WHERE order_state IN (4,5,9,10) 
        AND shop_id IN (".implode(",",ParameterInputs::CSShopGetMultipleID()).")
    )
    UNION ALL
    SELECT itemno2 AS itemno, quantity2 AS quantity
    FROM shipment
    WHERE shipment_type = 'earlyorder' AND shipment_state = 1 
    AND companyorder_id IN (
        SELECT id FROM company_order 
        WHERE order_state IN (4,5,9,10) 
        AND shop_id IN (".implode(",",ParameterInputs::CSShopGetMultipleID()).")
    )
    UNION ALL
    SELECT itemno3 AS itemno, quantity3 AS quantity
    FROM shipment
    WHERE shipment_type = 'earlyorder' AND shipment_state = 1 
    AND companyorder_id IN (
        SELECT id FROM company_order 
        WHERE order_state IN (4,5,9,10) 
        AND shop_id IN (".implode(",",ParameterInputs::CSShopGetMultipleID()).")
    )
) AS combined
WHERE itemno <> '' AND quantity <> 0
GROUP BY itemno;
";


        // Definer en inline funktion (closure) for postprocessing
        $postProcessFunction = function($row) {

            $itemno = $row["itemno"];

            $navisionItem = \NavisionItem::find_by_sql("SELECT * FROM navision_item WHERE no LIKE '".$itemno."' ORDER BY deleted ASC, language_id ASC LIMIT 1");
            if(count($navisionItem) != 0) {
                $row['description'] = $navisionItem[0]->description;
            } else {
                $row['description'] = "UKENDT VARENR: ".$itemno."";
            }


            return $row;
        };

        $exporter = new ExportCSVSimple("earlyorder-ready.csv");
        $exporter->exportSql($sql,$postProcessFunction);

    }

}
