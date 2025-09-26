<?php

namespace GFUnit\lister\rapporter;

class CardshopGiftwrapSum extends BaseReport
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getReportName() { return "Cardshop - Sum antal med/uden indpakning"; }
    public function getReportCode() { return "CardshopGiftwrapSum"; }
    public function getReportDescription() { return "Rapport der viser fordelingen mellem antal kort med og uden indpakning"; }

    public function defineParameters()
    {
        return array(
            "cardshops"
        );
    }

    // Genererer rapporten
    public function generateReport($parameters)
    {

        $sql = "SELECT 
    cardshop_settings.concept_code as Concept,
    SUM(CASE WHEN company_order.giftwrap = 0 THEN 1 ELSE 0 END) -SUM(CASE WHEN company_order.name_label = 1 THEN 1 ELSE 0 END) AS 'Uden indpak/label',
    SUM(CASE WHEN company_order.giftwrap = 1 THEN 1 ELSE 0 END) AS 'Med indpak',
    SUM(CASE WHEN company_order.name_label = 1 THEN 1 ELSE 0 END) AS 'Navnelabel'
FROM 
    `company_order`, cardshop_settings, shop_user 
WHERE 
    shop_user.company_order_id = company_order.id 
    && shop_user.blocked = 0 
        && company_order.shop_id in (".implode(",",ParameterInputs::CSShopGetMultipleID()).")
    && company_order.shop_id = cardshop_settings.shop_id 
    && company_order.order_state in (4,5,9,10) 
GROUP BY 
    cardshop_settings.concept_code;";

        $exporter = new ExportCSVSimple("cs-giftwrap.csv");
        $exporter->exportSql($sql);

    }

}
