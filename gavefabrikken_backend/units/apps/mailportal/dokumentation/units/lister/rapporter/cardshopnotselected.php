<?php

namespace GFUnit\lister\rapporter;

class CardshopNotSelected extends BaseReport
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getReportName() { return "Cardshop - Antal kort uden valg"; }
    public function getReportCode() { return "CardshopNotSelected"; }
    public function getReportDescription() { return "Rapport med antal kort pr. shop og deadline hvor der ikke er valg."; }

    public function defineParameters()
    {
        return array(
            "cardshops"
        );
    }

    // Genererer rapporten
    public function generateReport($parameters)
    {

        $sql = "select company_order.shop_id, company_order.shop_name, count(shop_user.id) 
        from company_order, shop_user 
        where shop_user.blocked = 0 && shop_user.shutdown = 0 && company_order.id = shop_user.company_order_id && company_order.shop_id in (".implode(",",ParameterInputs::CSShopGetMultipleID()).") && order_state in (4,5,9,10) && shop_user.id not in (select shopuser_id from `order`) 
        group by company_order.shop_id;";

        $exporter = new ExportCSVSimple("cs-not-selected.csv");
        $exporter->exportSql($sql);

    }

}
