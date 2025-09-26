<?php

namespace GFUnit\cardshop\pluklister;

use GFCommon\Model\Navision\OrderStatusWS;

class PrepaymentList extends PlukReport
{

    public function run() {



        $sql = "SELECT 
       company.nav_customer_no as nav_debitor_nr,
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
    company.contact_name,
    company.contact_phone,
    company.contact_email,
	company_order.shop_name as sales_shop,
	company_order.salesperson as sales_person,
	company_order.salenote as sales_note,
	company_order.quantity as sales_quantity,
	company_order.expire_date as sales_expiredate,
	company_order.certificate_no_begin,
	company_order.certificate_no_end,
	company_order.is_email,
	sum(IF(blocked=0,1,0)) as cards_activecount,
	sum(IF(blocked=1,1,0)) as cards_closedcount
FROM company, company_order, shop_user WHERE company.id != 19502 &&
	company_order.is_cancelled = 0 && order_state not in (7,8) &&
	shop_user.company_order_id = company_order.id &&
	((shop_user.shop_id = ".intval($this->shopid)." && shop_user.expire_date = '".$this->expire."') || (company_order.shop_id = ".intval($this->shopid)." && company_order.expire_date = '".$this->expire."')) &&
	is_giftcertificate = 1 &&
	company_order.company_id = company.id
GROUP BY company_order.id 
ORDER BY company_order.order_no ASC";

        $results = \Dbsqli::getSql2($sql);


        if(!is_array($results) || countgf($results) == 0) {

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=NULLLIST-'.$this->shopid.'-'.$this->expire.'-'.date("dmYHi").'.csv');
            echo "Ingen resultater";
            exit();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=prepaymentlist-'.$this->shopid.'-'.$this->expire.'-'.date("dmYHi").'.csv');

        foreach($results[0] as $key => $val) {
            echo $key.";";
        }

        echo "Faktura dokumenter;Aconto faktureret;Aconto restance;";

        echo "\n";


        $navClient = new OrderStatusWS($this->shopSettings->language_code);

        foreach($results as $row)
        {

            $showOrder = true;

            $prepaymentAmount = "";
            $prepaymentRestance = "";
            $prepaymentDocs = 0;

            try {

                $status = $navClient->getStatus($row["order_no"]);
                if($status == null) {

                    $prepaymentDocs = "Ukendt!";

                } else {

                    $prepaymentAmount = $status->getPrepaymentAmount();
                    $prepaymentRestance = $status->getRemPrepaymentAmountLCY();
                    $prepaymentDocs = $status->getPrepaymentEntryCountTotal();

                    if(intval($prepaymentRestance) == 0) {
                        $showOrder = false;
                    }

                }



            } catch (\Exception $e) {

            }

            $row["dokumenter"] = $prepaymentDocs;
            $row["aconto faktureret"] = $prepaymentAmount;
            $row["aconto restance"] = $prepaymentRestance;

            /*
            echo "<hr><br>Order: ".$row["order_no"];
            echo "<br>Prepayment amount: ".$status->getPrepaymentAmount()."<br>";
            echo "Rem prepayment amount: ".$status->getRemPrepaymentAmountLCY()."<br>";
            echo "Prepayment count: ".$status->getPrepaymentEntryCountTotal()."<br>";
            */

            if($showOrder) {
                foreach($row as $key => $val) {
                    echo utf8_decode(trimgf(str_replace(array("\r","\n",";"),array(""," ",""),$val)).";");
                }
                echo "\n";
            }

        }

    }

}