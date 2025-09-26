<?php

namespace GFUnit\cardshop\pluklister;

use GFBiz\Model\Cardshop\OrderFreightState;
use GFCommon\Model\Navision\FreightCalculator;

class CompanyOrderList extends PlukReport
{

    public function run() {

        
        
        $sql = "SELECT 
	company_order.order_no,
	company.name as company_name,
	company.cvr as company_cvr,
	company.ean as company_ean,
	company.bill_to_address,
	company.bill_to_address_2,
	company.bill_to_postal_code,
	company.bill_to_city,
	company.bill_to_country,
	company.bill_to_email,
	company_order.company_name as sales_company,
	company_order.shop_name as sales_shop,
	company_order.salesperson as sales_person,
	company_order.salenote as sales_note,
	company.internal_note,
	company.rapport_note,
	company_order.quantity as sales_quantity,
	company_order.expire_date as sales_expiredate,
	company_order.certificate_no_begin,
	company_order.certificate_no_end,
	company_order.certificate_value,
	company_order.is_email,
	company_order.is_appendix_order,
	company_order.giftwrap as gift_wrap,
	company_order.name_label as name_label,
	company_order.gift_spe_lev as gift_carryup,
	company_order.earlyorderList as earlypresents,
	company_order.created_datetime,
	company.ship_to_company,
	company.ship_to_attention,
	company.ship_to_address,
	company.ship_to_address_2,
	company.ship_to_postal_code,
	company.ship_to_city,
	company.ship_to_country,
	company_order.spdealtxt as ship_dealtext,
	company.contact_name,
	company.contact_email,
	company.contact_phone,
	company_order.navsync_status,
	company.nav_customer_no as navsync_debitorid,
	count(shop_user.id) as cards_totalcount,
	sum(IF(blocked=0,1,0)) as cards_activecount,
	sum(IF(blocked=1,1,0)) as cards_closedcount,
	sum(IF(shop_user.expire_date!=company_order.expire_date,1,0)) as cards_moveddeadline,
	sum(IF(shop_user.company_id!=company_order.company_id,1,0)) as cards_movedcompany,
    IF(company_order.order_freight is null, '*',company_order.order_freight) as order_freight
FROM company, company_order, shop_user WHERE 
	company_order.is_cancelled = 0 && order_state not in (7,8) &&
	shop_user.company_order_id = company_order.id &&
	((shop_user.shop_id = ".intval($this->shopid)." && shop_user.expire_date = '".$this->expire."') || (company_order.shop_id = ".intval($this->shopid)." && company_order.expire_date = '".$this->expire."')) &&
	is_giftcertificate = 1 &&
	company_order.company_id = company.id
GROUP BY company_order.id 
ORDER BY company_order.order_no ASC";

        //echo $sql;

        $results = \Dbsqli::getSql2($sql);

        if(!is_array($results) || countgf($results) == 0) {

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=NULLLIST-'.$this->shopid.'-'.$this->expire.'-'.date("dmYHi").'.csv');
            echo "Ingen resultater";
            exit();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=customlist-'.$this->shopid.'-'.$this->expire.'-'.date("dmYHi").'.csv');

        foreach($results[0] as $key => $val) {
            echo $key.";";
        }
        //echo $this->getSpecialLabel();
        echo "\n";

        foreach($results as $row)
        {
            foreach($row as $key => $val) {
                echo utf8_decode(trimgf(str_replace(array("\r","\n",";"),array(""," ",""),trimgf($val))).";");
            }
            //echo $this->getSpecialValue($row);
            echo "\n";
        }
        
    }

    private function getSpecialLabel() { return "DK Fragt"; }
    private function getSpecialValue($data)
    {

        //return "#";

        $value = "";

        $isPrivatedelivery = ($data["sales_expiredate"] == "2023-04-01");

        // Calculate freight on company delivery
        if(!$isPrivatedelivery) {

                $companyOrder = \CompanyOrder::find("first",array("conditions" => array("order_no" => $data["order_no"])));

                // Update freight on companyorder
                $freightState = OrderFreightState::updateOrderFreightState($companyOrder);

                $value .= "State ".$freightState.", ";

                // Calculate freight price on this order
                if($freightState == 1) {

                    // Load freigh addresses and active cards on this company, shop_id and expire_date
                    $cardFreightList = \ShopUser::find_by_sql("SELECT shop_user.company_id, count(shop_user.id) as activecards, company.name, company.ship_to_company, company.ship_to_address FROM `shop_user`, company WHERE shop_user.company_id = company.id && shop_user.blocked = 0 && shop_user.is_demo = 0 && shop_user.company_order_id IN (SELECT id  FROM `company_order` WHERE company_id = ".$companyOrder->company_id." && expire_date = '".$companyOrder->expire_date->format("Y-m-d")."' && shop_id = ".$companyOrder->shop_id.") GROUP BY shop_user.company_id");

                    // For each add invoice line
                    foreach($cardFreightList as $cfr) {

                        // Calculate price
                        $cards = $cfr->activecards;
                        $freighAmount = FreightCalculator::calculateFreight($companyOrder->shop_id,$cards,$isPrivatedelivery);
                        $value .= ", Calc freight: ".$freighAmount;
                    }

                }

                // Add fixed price freight to this order
                else if($freightState == 3) {

                    $companyShippingCost = \companyshippingcost::find("first",array("conditions" => array("company_id" => $companyOrder->company_id)));
                    if($companyShippingCost != null && $companyShippingCost->company_id == $companyOrder->company_id && $companyShippingCost->cost >= 0) {
                        $value .= ", Static: ".$companyShippingCost->cost;
                    } else {
                        throw new \Exception("Order freight state set to fixed price, but price could not be found: company_id = ".$companyOrder->company_id);
                    }

                }

                else {
                    $value .= "Other state no ship";
                }

        } else {
            $value = "PD - No freight fee";
        }

        return $value;

    }

}