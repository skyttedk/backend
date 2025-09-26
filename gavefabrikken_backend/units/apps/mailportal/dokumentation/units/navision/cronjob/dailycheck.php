<?php

namespace GFUnit\navision\cronjob;

use GFUnit\navision\syncexternalshipment\ExternalShipmentSync;

class DailyCheck
{

    public function runDailyCheck($output=0) {


        \GFCommon\DB\CronLog::startCronJob("CSDaily");

        echo "Run block message suppresions<br>";

        try {

            // Run custom sql statements - mute errors sending items to se that does not exist, no current solution from TK
            $sql = "UPDATE `blockmessage` set silent = 1 WHERE debug_data like '%Prepare nav sync to 5%' and debug_data like '%Unknown <itemno>, on <item> line 1%' && release_status = 0 && silent = 0";
            \Dbsqli::setSql2($sql);

            // Run custom sql statements - mute errors sending items to se that does not exist, no current solution from TK
            $sql = "UPDATE `blockmessage` set silent = 1 WHERE debug_data like '%Prepare nav sync to 4%' and debug_data like '%<processed_externally> for <shipmenttype> = 3/Direct Delivery%' && release_status = 0 && silent = 0";
            \Dbsqli::setSql2($sql);

            $sql = "UPDATE `blockmessage` set silent = 1 WHERE debug_data like '%Prepare nav sync to 5%' and debug_data like '%<processed_externally> for <shipmenttype> = 3/Direct Delivery%' && release_status = 0 && silent = 0";
            \Dbsqli::setSql2($sql);

            // Cleanup in test cronjob
            $sql = "DELETE FROM cronlog WHERE created < NOW() - INTERVAL 1 MONTH";
            \Dbsqli::setSql2($sql);

            $sql = "UPDATE cronlog SET output = '' WHERE created < NOW() - INTERVAL 14 DAY";
            \Dbsqli::setSql2($sql);

            $sql = "UPDATE cronlog SET debugdata = '' WHERE created < NOW() - INTERVAL 30 DAY";
            \Dbsqli::setSql2($sql);

        } catch (\Exception $e) {
            echo "ERROR: ".$e->getMessage();
        }

        // Update shopuser delivery_state
        $sql = "UPDATE shop_user su SET su.delivery_state = 2 WHERE EXISTS (  SELECT 1  FROM shipment s  WHERE su.username = s.from_certificate_no  AND s.shipment_state = 2  AND s.shipment_type IN ('privatedelivery', 'directdelivery') ) AND su.is_giftcertificate = 1 AND su.blocked = 0 AND su.is_delivery = 1 AND su.company_order_id > 0 and su.delivery_state = 1";
        \Dbsqli::setSql2($sql);

        // RUN LUKSUS CLOSE ON DATE
        $this->closeLuksusGavekortOnExpireDate();

        // RUN LUKSUS CLOSE ON TIME
        $this->closeLuksusGavekortOnAllClosed();

        // Run SE CARDS CLOSE ON ALL SELECTED
        //$this->closeSECardsOnAllSelected();

        // External shipments
        $externalSyncModel = new ExternalShipmentSync();
        $externalSyncModel->runcheck(intval($output) == 1);

        // Run freight check
        $this->checkCardshopFreights();

        \GFCommon\DB\CronLog::endCronJob(1,"OK");
    }
    
    private function checkIsDelivery() {
        // This check looks for expire_dates and shop_users with same expire_date but difference in is_delivery, that should not happen
        $mismatches = \ShopUser::find_by_sql("select shop_user.expire_date, shop_user.is_delivery, expire_date.expire_date, expire_date.is_delivery from shop_user, expire_date where shop_user.expire_date = expire_date.expire_date && shop_user.is_giftcertificate = 1 && shop_user.is_delivery != expire_date.is_delivery");
        if(countgf($mismatches) > 0) {
            mailgf("sc@interactive.dk", "IsDelivery mismatch","Daily run of isdelivery mismatch shows ".countgf($mismatches)." problems, run check to verify!");
        }
    }

    private function checkCardshopFreights()
    {

        $details = "";

        $details .= $this->checkCardshopFreightGroupMul("MULTIPLE FREIGHTS", "SELECT group_concat(id) as ids, company_order_id, company_id FROM `cardshop_freight` group by company_order_id, company_id having count(id) > 1");
        $details .= $this->checkCardshopFreightGroup("DISABLED", "select * from cardshop_freight where dot = 0 and carryup = 0 and (note IS NULL or note = '')");
        $details .= $this->checkCardshopFreightGroup("NO DOT DATE", "select * from cardshop_freight where dot = 1 and (dot_date is null)");
        $details .= $this->checkCardshopFreightGroup("INVALID DOT PRICETYPE", "select * from cardshop_freight where dot = 1 and dot_pricetype NOT IN (1,2,3)");
        $details .= $this->checkCardshopFreightGroup("NEGATIVE DOT PRICE", "select * from cardshop_freight where dot = 1 and dot_price < 0");
        $details .= $this->checkCardshopFreightGroup("DOT PRICE OVER MAX", "select * from cardshop_freight where dot = 1 and dot_price > 1000000");
        $details .= $this->checkCardshopFreightGroup("CARRYUP TYPE INVALID", "select * from cardshop_freight where carryup = 1 and carryuptype NOT IN (1,2,3)");
        $details .= $this->checkCardshopFreightGroup("INVALID CARRYUP PRICETYPE", "select * from cardshop_freight where carryup = 1 and carryup_pricetype NOT IN (1,2,3)");
        $details .= $this->checkCardshopFreightGroup("NEGATIVE CARRYUP PRICE", "select * from cardshop_freight where carryup = 1 and carryup_price < 0");
        $details .= $this->checkCardshopFreightGroup("CARRYUP PRICE OVER MAX", "select * from cardshop_freight where carryup = 1 and carryup_price > 1000000");

        if($details != "") {
            mailgf("sc@interactive.dk", "Cardshop freight monitor errors",$details);
        }

    }

    private function checkCardshopFreightGroupMul($name,$freightSQL)
    {
        $message = "";
        $freights = \CardshopFreight::find_by_sql($freightSQL);
        if(countgf($freights) > 0) {
            $message .= "<h2>".$name."</h2>";
            foreach($freights as $freight) {
                $message .= "<p>ID: ".$freight->ids."</p>";
            }
        }
        return $message;
    }

    private function checkCardshopFreightGroup($name,$freightSQL)
    {
        $message = "";
        $freights = \CardshopFreight::find_by_sql($freightSQL);
        if(countgf($freights) > 0) {
            $message .= "<h2>".$name."</h2>";
            foreach($freights as $freight) {
                $message .= "<p>ID: ".$freight->id." - <br>DOT: ".$freight->dot." - DOT PRICE: ".$freight->dot_price." - DOT PRICETYPE: ".$freight->dot_pricetype." - DOT DATE: ".($freight->dot_date == null ? "ingen dato" : $freight->dot_date->format('Y-m-d\TH:i'))."<br>CARRYUP: ".$freight->carryup." - CARRYUP PRICE: ".$freight->carryup_price." - CARRYUP PRICETYPE: ".$freight->carryup_pricetype."<br>NOTE: ".$freight->note."</p>";
            }
        }
        return $message;
    }


    public function testsecardclose() {

        $this->closeSECardsOnAllSelected();

    }

    private  function closeSECardsOnAllSelected() {

        echo "Close SE ordre on count:<br> ";

        $sql = "SELECT company_order.id, company_order.order_no, company_order.company_name, count(shop_user.id), count(order.id), max(order.order_timestamp) FROM company_order, shop_user left join `order` on order.shopuser_id = shop_user.id where company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) && company_order.order_state in (4,5) && company_order.expire_date in (select expire_date from expire_date where is_delivery = 1) && company_order.id = shop_user.company_order_id && shop_user.blocked = 0 group by company_order.id having max(order.order_timestamp) <= DATE_SUB(NOW(), INTERVAL 1 MONTH) && count(order.id) >= count(shop_user.id);";
        $companyOrders = \CompanyOrder::find_by_sql($sql);


        var_dump($companyOrders);
        if($companyOrders == null || count($companyOrders) == 0) {
            echo " - No se card orders to close on count";
            return;
        }

        exit();
        echo "found ".count($companyOrders)." orders to close on count<br>";

        foreach($companyOrders as $order) {
            $companyOrder = \CompanyOrder::find($order->id);
            if($companyOrder != null && $order->id == $companyOrder->id && in_array($companyOrder->order_state, array(4,5))) {
                echo "CLOSE ".$companyOrder->order_no."<br>";
                $companyOrder->order_state = 9;
                $companyOrder->nav_synced = 0;
                $companyOrder->save();
            }
        }

        \system::connection()->commit();
        \System::connection()->transaction();
        
    }

    private function closeLuksusGavekortOnExpireDate()
    {

        echo "Close luksusgavekort ordre on date: ";

        $sql = "SELECT * FROM company_order where shop_id in (select shop_id from cardshop_settings where concept_parent = 'LUKS') && order_state in (4,5) && floating_expire_date <= DATE_SUB(NOW(), INTERVAL 1 MONTH);";
        $companyOrders = \CompanyOrder::find_by_sql($sql);

        if($companyOrders == null || count($companyOrders) == 0) {
            echo " - No luksuscard orders to close on date";
            return;
        }

        echo "found ".count($companyOrders)." orders to close on date<br>";
/*
        $this->mailLog("READY TO CLOSE LUKSUSCARD", "LUKSUS CARD READY TO CLOSE ON DATE - CHECK unit/navision/cronjob/dailycheck and run it manually and check this first time!!");
        return;
*/
        foreach($companyOrders as $order) {
            $companyOrder = \CompanyOrder::find($order->id);
            if($companyOrder != null && $order->id == $companyOrder->id && in_array($order->order_state, array(4,5))) {
                $companyOrder->order_state = 9;
                $companyOrder->nav_synced = 0;
                $companyOrder->save();
            }
        }

        \system::connection()->commit();
        \System::connection()->transaction();

    }

    private function closeLuksusGavekortOnAllClosed()
    {

        echo "Close luksusgavekort ordre on count: ";

        $sql = "SELECT company_order.id, company_order.order_no, company_order.company_name, count(shop_user.id), count(order.id), max(order.order_timestamp) FROM company_order, shop_user left join `order` on order.shopuser_id = shop_user.id where company_order.shop_id in (select shop_id from cardshop_settings where concept_parent = 'LUKS') && company_order.order_state in (4,5) && company_order.id = shop_user.company_order_id && shop_user.blocked = 0 group by company_order.id having max(order.order_timestamp) <= DATE_SUB(NOW(), INTERVAL 1 MONTH) && count(order.id) >= count(shop_user.id);";
        $companyOrders = \CompanyOrder::find_by_sql($sql);

        if($companyOrders == null || count($companyOrders) == 0) {
            echo " - No luksuscard orders to close on count";
            return;
        }

        echo "found ".count($companyOrders)." orders to close on count<br>";

        foreach($companyOrders as $order) {
            $companyOrder = \CompanyOrder::find($order->id);
            if($companyOrder != null && $order->id == $companyOrder->id && in_array($companyOrder->order_state, array(4,5))) {
                echo "CLOSE ".$companyOrder->order_no."<br>";
                $companyOrder->order_state = 9;
                $companyOrder->nav_synced = 0;
                $companyOrder->save();
            }
        }

        \system::connection()->commit();
        \System::connection()->transaction();

    }


    protected function mailLog($subject,$content)
    {

        $modtager = "sc@interactive.dk";
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";
        $result = mailgf($modtager,"nav/cron/daily: ".$subject, $content, $headers);

    }

}

