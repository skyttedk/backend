<?php

namespace GFUnit\lister\rapporter;

class CardshopReminder extends BaseReport
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getReportName() { return "Cardshop - Reminder liste"; }
    public function getReportCode() { return "CardshopReminder"; }
    public function getReportDescription() { return "Reminderliste for cardshop, for specifikke shops eller land og deadline."; }

    public function defineParameters()
    {
        return array(
            "cardshops",
            "expiredate"
        );
    }

    // Genererer rapporten
    public function generateReport($parameters)
    {

        $sql = "select
	company.id as CompanyID,
	company.name as Virksomhed,
	company.cvr as CVR,
	TRIM( REPLACE(company.bill_to_address, '\n', '') ) as FakturaAdresse,
	company.bill_to_postal_code as FakturaPostnr,
	company.bill_to_city as FakturaBy,
	company.ship_to_company as LeveringVirksomhed,
	company.ship_to_address as LeveringAdresse,
	company.ship_to_postal_code as LeveringPostnr,
	company.ship_to_city as LeveringBy,
	company.contact_name as Kontaktperson,
	company.contact_phone as Telefon,
    company.contact_email as Email,
	count(shop_user.id) as AntalKort,
	count(o.id) as Valgt, 
	count(shop_user.id)-count(o.id) as IkkeValgt,
	company.token,
	GROUP_CONCAT(DISTINCT DATE(company_order.created_datetime)) as Oprettet,
	GROUP_CONCAT(DISTINCT company_order.shop_name) as Koncepter
FROM 
	company,
	company_order,
	shop_user LEFT JOIN 
	`order` as o ON shop_user.id = o.shopuser_id
WHERE 
	company.id = shop_user.company_id && 
	shop_user.company_order_id = company_order.id && 
	shop_user.is_demo = 0 && shop_user.blocked = 0 && shop_user.shutdown = 0 && company_order.expire_date = '".ParameterInputs::CSExpireDateGet()."' && company_order.shop_id in (".implode(",",ParameterInputs::CSShopGetMultipleID()).") && company_order.order_state in (4,5,9,10)
GROUP BY
	shop_user.company_id
ORDER BY
	IF(company.pid=0, company.id, company.pid+1) ASC;";

        $exporter = new ExportCSVSimple("cs-reminderlist-".ParameterInputs::CSExpireDateGet().".csv");
        $exporter->exportSql($sql);

    }

}
