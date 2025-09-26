<?php

namespace GFUnit\navision\cronjob;

use GFUnit\cardshop\earlyorder\EarlyOrderSplit;
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

            $sql = "UPDATE `blockmessage` set silent = 1 where description like 'Itemno 240102 is blocked, aborting!' and silent = 0 and release_status = 0";
            \Dbsqli::setSql2($sql);

            $sql = "DELETE FROM blockmessage where shipment_id > 0 and shipment_id not in (select id from shipment)";
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
        $sql = "UPDATE shop_user su JOIN shipment s ON su.username = s.from_certificate_no SET su.delivery_state = 2 WHERE s.shipment_state = 2 AND s.shipment_type IN ('privatedelivery', 'directdelivery') AND su.is_giftcertificate = 1 AND su.blocked = 0 AND su.is_delivery = 1 AND su.company_order_id > 0 AND su.delivery_state = 1";
        \Dbsqli::setSql2($sql);

        // RUN LUKSUS CLOSE ON DATE
        $this->closeLuksusGavekortOnExpireDate();

        // RUN LUKSUS CLOSE ON TIME
        $this->closeLuksusGavekortOnAllClosed();

        // Run SE CARDS CLOSE ON ALL SELECTED
        $this->closeSECardsOnAllSelected();

        // Run card values monitor
        $this->checkCardValues();

        // Check double freight items
        $this->checkDoubleFreightItems();

        // External shipments
        $externalSyncModel = new ExternalShipmentSync();
        $externalSyncModel->runcheck(intval($output) == 1);

        // Run freight check
        $this->checkCardshopFreights();

        // Run earlyorder splitter
        $split = new EarlyOrderSplit();
        $split->setSplitterAll();
        $split->runSplitter();

        \GFCommon\DB\CronLog::endCronJob(1,"OK");
    }

    private function checkDoubleFreightItems()
    {
        $sql = "SELECT count(cardshop_freight.id) as items, group_concat(cardshop_freight.id) as fidlist, company_order_id, cardshop_freight.company_id, company_order.shop_id, company_order.expire_date FROM cardshop_freight, company_order WHERE cardshop_freight.company_order_id = company_order.id group by cardshop_freight.company_id, company_order.shop_id, company_order.expire_date having count(cardshop_freight.id) > 1;";
        $freightItems = \CardshopFreight::find_by_sql($sql);
        $mailText = "";
        foreach($freightItems as $fi) {
            $mailText .= "Company: ".$fi->company_id." - Shop: ".$fi->shop_id." - Freight items: ".$fi->items." - Freight IDs: ".$fi->fidlist."<br>";
        }
        if($mailText != "") {
            echo $mailText;
            mailgf("sc@interactive.dk", "Double FreightItem match","Same customer has freight_items for same delivery on different orders:<br>".$mailText);
        }
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

        //exit();
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





    /********************* CARD VALUES MONITOR ********************/

    protected function checkCardValues()
    {

        $errors = array();


        // Der ikke er card_values på ordre der ikke bør have det
        $sql = "select * from company_order where shop_id in (SELECT shop_id FROM `cardshop_settings` WHERE card_values is null and bonus_presents is null) and card_values is not null;";
        $colist = \CompanyOrder::find_by_sql($sql);
        if(count($colist) > 0) {
            $errors[] = "Der er " . count($colist) . " ordre der har card_values som ikke bør have det";
        }

        //Tjek om ordre mangler card_values sat
        $sql = "select * from company_order where shop_id not in (SELECT shop_id FROM `cardshop_settings` WHERE card_values is null and bonus_presents is null) and card_values is null;";
        $collist = \CompanyOrder::find_by_sql($sql);
        if(count($collist) > 0) {
            $errors[] = "Der er " . count($collist) . " ordre der mangler card_values";
        }

        //Tjek om der er company_order og shop_user values der ikke stemmer overens:
        $sql = "SELECT * FROM `company_order`, shop_user WHERE shop_user.company_order_id > 0 and shop_user.company_order_id = company_order.id and company_order.card_values != shop_user.card_values;";
        $colist = \CompanyOrder::find_by_sql($sql);
        if(count($colist) > 0) {
            $errors[] = "Der er " . count($colist) . " ordre der har forkerte card_values";
        }

        //Tjek at der ikke er nogen der har valgt forkerte gaver
        $sql = "SELECT present.present_list, shop_user.card_values FROM shop_user JOIN `order` ON shop_user.id = `order`.`shopuser_id` JOIN present ON `order`.`present_id` = present.id WHERE shop_user.shop_id != 8826 and shop_user.card_values IS NOT NULL AND shop_user.card_values != '' AND NOT FIND_IN_SET(CAST(present.present_list AS CHAR CHARACTER SET utf8mb3), CAST(shop_user.card_values AS CHAR CHARACTER SET utf8mb3));";
        $prlist = \Present::find_by_sql($sql);
        if(count($prlist) > 0) {
            $errors[] = "Der er " . count($prlist) . " ordre der har forkerte gaver";
        }

        // Tjek at der ikke er nogen der har forkerte værdier på ordren:
        $sql = "SELECT co.card_values, cs.card_values
FROM company_order co
JOIN cardshop_settings cs ON co.shop_id = cs.shop_id
WHERE cs.card_values IS NOT NULL
    AND EXISTS (
        SELECT 1
    FROM (
        SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(co.card_values, ',', numbers.n), ',', -1)) AS value
        FROM (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
        ) numbers
        WHERE numbers.n <= 1 + LENGTH(co.card_values) - LENGTH(REPLACE(co.card_values, ',', ''))
    ) AS split_values
    WHERE NOT FIND_IN_SET(value, cs.card_values)
);";
        $colist = \CompanyOrder::find_by_sql($sql);
        if(count($colist) > 0) {
            $errors[] = "Der er " . count($colist) . " ordre der har forkerte værdier fra cardshop settings";
        }


        //Tjek at der ikke er forkerte værdier fra bonus gaver
        $sql = "SELECT co.card_values, cs.bonus_presents, cs.card_price
FROM company_order co
JOIN cardshop_settings cs ON co.shop_id = cs.shop_id
WHERE cs.bonus_presents IS NOT NULL
    AND NOT EXISTS (
        SELECT 1
    FROM (
        SELECT TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(co.card_values, ',', numbers.n), ',', -1)) AS value
        FROM (
            SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
        ) numbers
        WHERE numbers.n <= 1 + LENGTH(co.card_values) - LENGTH(REPLACE(co.card_values, ',', ''))
    ) AS split_values
    WHERE NOT (
        FIND_IN_SET(value, CONCAT(cs.card_price / 100, ',', cs.card_price / 100 + cs.bonus_presents))
    )
);";
        $colist = \CompanyOrder::find_by_sql($sql);
        if(count($colist) > 0) {
            $errors[] = "Der er " . count($colist) . " ordre der har forkerte værdier fra bonus gaver";
        }

        // Send e-mail
        if(count($errors) > 0) {

            $content = "<h1>Card values monitor</h1>";
            $content .= "<p>Der er fundet fejl i card values:</p>";
            foreach($errors as $error) {
                $content .= "<p>".$error."</p>";
            }
            $this->mailLog("Card values monitor",$content);

        }

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

