<?php

namespace GFUnit\cardshop\orderlist;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * SERVICES
     */

    public function company($companyid = 0) {




        $sql = "SELECT company_order.id, company_order.order_no, company_order.company_id, company_order.shop_id, shop.name, company_order.salesperson, company_order.quantity, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date, company_order.is_email, company_order.certificate_no_begin,
        company_order.certificate_no_end, company_order.certificate_value, company_order.ship_to_company, company_order.contact_name, company_order.giftwrap, company_order.gift_spe_lev as carryup, company_order.dot, 
        company_order.order_state, company_order.nav_on_hold, company_order.free_cards, company_order.spdealtxt, company_order.prepayment, company_order.shipment_on_hold, count(suactive.id) as active_cards, (company_order.quantity - count(suactive.id)) as blocked_cards,
        COUNT(DISTINCT suactive.company_id) as card_childcompany_count, group_concat(DISTINCT suactive.company_id) as card_childcompany_ids
        FROM shop, shop_user suactive, `company_order` WHERE company_order.company_id = ".intval($companyid)." && company_order.shop_id = shop.id && suactive.company_order_id = company_order.id && suactive.blocked = 0 GROUP BY company_order.id";

        $companyOrderList = \Dbsqli::getSql2($sql);

        foreach($companyOrderList as $index => $companyOrder) {

            $companyOrder["card_childcompany_ids"] = explode(",",$companyOrder["card_childcompany_ids"]);
            if(in_array($companyOrder["company_id"],$companyOrder["card_childcompany_ids"])) {
                if (($key = array_search($companyOrder["company_id"], $companyOrder["card_childcompany_ids"])) !== false) {
                    unset($companyOrder["card_childcompany_ids"][$key]);
                }
                $companyOrder["card_childcompany_ids"] = array_values($companyOrder["card_childcompany_ids"]);
                $companyOrder["card_childcompany_count"] = countgf($companyOrder["card_childcompany_ids"]);
                $companyOrder["state_text"] = \CompanyOrder::stateTextList($companyOrder["order_state"]);
            }

            $companyOrderList[$index] = $companyOrder;

        }

        echo json_encode(array("status" => 1,"orderlist" => $companyOrderList),JSON_PRETTY_PRINT);

    }
    public function getCompanyToken(){
       $companyid = intval($_POST["companyid"]);
       $company = \Company::find($companyid);
       echo json_encode(array("status" => 1, "data" => $company));
    }




    public function reissueinvoices($companyid = 0)
    {

        $company = \Company::find($companyid);
        $companyorderlist = \CompanyOrder::find("all",array("conditions" => array("company_id" => $company->id,"order_state IN (4,5,6)","prepayment" => 1)));

        if(count($companyorderlist) == 0) {
            throw new \Exception("Ingen faktura er klar til at blive gen-udstedt.");
        }

        foreach($companyorderlist as $companyorder)  {
            $companyorder->force_orderconf = 2;
            $companyorder->nav_synced = 0;
            $companyorder->save();
        }

        echo json_encode(array("status" => 1, "updated" => countgf($companyorderlist)));
        \System::connection()->commit();

    }



}