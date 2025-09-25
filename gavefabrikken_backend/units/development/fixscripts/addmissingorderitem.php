<?php

namespace GFUnit\development\fixscripts;

class AddMissingOrderItem
{

    private $stats;

    public function run()
    {
        // JOB DISABLED, TURN ON WHEN NEEDED!
        return;

        echo "RUN SCRIPT ADD ORDER ITEM<br>";

        $shopid = array(1981,1832,2558,4793,5117);
        $productType = "CARDFEE";
        $ischosen = false;
        $price = 2000;

        // Get orders
        $sql = "SELECT * FROM company_order where shop_id IN (".implode(",",$shopid).") && id not in (select companyorder_id FROM company_order_item where type = '".$productType."')";
        $orderList = \CompanyOrder::find_by_sql($sql);

        echo "FOUND ".countgf($orderList)." orders<br>";
        foreach($orderList as $companyOrder) {

            echo "Create for order id: ".$companyOrder->id."<br>";
            $orderItem = new \CompanyOrderItem();
            $orderItem->companyorder_id = $companyOrder->id;
            $orderItem->quantity = ($ischosen == true ? 1 : 0);
            $orderItem->type = $productType;
            $orderItem->price = $price;
            $orderItem->isdefault = 0;
            $orderItem->created_by = 0;
            $orderItem->updated_by = 0;
            $orderItem->created_date = date('d-m-Y H:i:s');
            $orderItem->updated_date = date('d-m-Y H:i:s');
             $orderItem->save();

        }

        \System::connection()->commit();

    }

}