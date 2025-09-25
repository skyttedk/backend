<?php

namespace GFUnit\development\fixscripts;

class FindeOrdersAfterClose
{

    public function run()
    {

        // Find autoselect
        $autoselectList = \SystemLog::find_by_sql("SELECT data, min(created_datetime) as mindate, max(created_datetime) as maxdate FROM `system_log` WHERE `controller` LIKE 'order' AND `action` LIKE 'autoSelectSpecificPresents' group by data");
        $autoMap = [];
        foreach($autoselectList as $auto) {

            $dataContent = json_decode($auto->data, true);
            if($dataContent["shopid"] > 0) {
                $autoMap[$dataContent["shopid"]][$dataContent["model_id"]] = array("min" => $auto->mindate, "max" => $auto->maxdate);
            }

        }

        //echo "<pre>".print_r($autoMap,true)."</pre>";

        $sql = "SELECT s.id as shop_id, s.name, o.id as order_id, o.order_no, o.user_username, o.user_email, o.shopuser_id, s.close_date, o.order_timestamp, o.present_model_id as model_id, o.present_id FROM `shop` s, `order` o where close_date is not null and close_date < order_timestamp and s.id = o.shop_id order by s.id desc, o.order_timestamp desc;";

        $orders = \Order::find_by_sql($sql);

        ?><table style="width: 100%;">
            <tr>
                <td>ShopID</td>
                <td>Shop name</td>
                <td>Order ID</td>
                <td>Order No</td>
                <td>Username</td>
                <td>Email</td>
                <td>ShopUser ID</td>
                <td>Present ID</td>
                <td>Model ID</td>
                <td>Close date</td>
                <td>Order timestamp</td>
                <td>Reason</td>
            </tr>
        <?php

        $orderProcessed = 0;

        foreach($orders as $order) {

            $isRealOrder = true;
            $foundReason = false;
            $orderProcessed++;
            $reason = "NOT FOUND";

            $isStandardData = false;
            if(isset($autoMap[$order->shop_id][$order->model_id])) {
                $isStandardData = true;
                $isRealOrder = false;
                $foundReason = true;
                $reason = "Autovalg";
            }

            if($isRealOrder) {

                $orderTimestamp = $order->order_timestamp;
                $start = $orderTimestamp->modify('-10 seconds')->format("Y-m-d H:i:s");
                $end = $orderTimestamp->modify('+15 seconds')->format("Y-m-d H:i:s");

                $logSql = "SELECT * FROM system_log where created_datetime >= '" . $start . "' and created_datetime <= '" . $end . "'";
                $logMessage = \SystemLog::find_by_sql($logSql);

                foreach($logMessage as $log) {

                    // Update customer panel
                    if($log->controller == "order" && $log->action == "updateShopUserCustomerPanel" && strstr($log->data, $order->shopuser_id) && strstr($log->data, $order->shop_id)) {
                        $foundReason = true;
                        $isRealOrder = false;
                        $reason = "CustomerPanelUpdate";
                        break;
                    }

                    // Update customer panel
                    if($log->controller == "order" && $log->action == "changePresent" && strstr($log->data, $order->shopuser_id) && strstr($log->data, $order->model_id)) {
                        $foundReason = true;
                        $isRealOrder = false;
                        $reason = "ChangePresent";
                        break;
                    }

                    // Update customer panel
                    if($log->controller == "order" && $log->action == "orderUseAlias" && strstr($log->data, $order->shopuser_id) && strstr($log->data, $order->shop_id)) {
                        $foundReason = true;
                        $isRealOrder = false;
                        $reason = "UseAlias";
                        break;
                    }

                    // Resend order mail
                    if($log->controller == "order" && $log->action == "resendOrderMail" && strstr($log->data, $order->shopuser_id)) {
                        $foundReason = true;
                        $isRealOrder = false;
                        $reason = "ResendOrderMail";
                        break;
                    }

                    // Created real order
                    if($log->controller == "order" && $log->action == "create" && strstr($log->data, $order->shopuser_id) && strstr($log->data, $order->model_id)) {
                        $foundReason = true;
                        $isRealOrder = true;
                        $reason = "ORDER";
                        break;
                    }

                }

            }
/*
            if($orderProcessed > 1000) {
                echo "reached end!";
                break;
            }
*/
            if($isRealOrder || !$foundReason) {

                ?><tr>
                    <td><?php echo $order->shop_id; ?></td>
                    <td><?php echo $order->name; ?></td>
                    <td><?php echo $order->order_id; ?></td>
                    <td><?php echo $order->order_no; ?></td>
                    <td><?php echo $order->user_username; ?></td>
                    <td><?php echo $order->user_email; ?></td>
                    <td><?php echo $order->shopuser_id; ?></td>
                    <td><?php echo $order->present_id; ?></td>
                    <td><?php echo $order->model_id; ?></td>
                    <td><?php echo $order->close_date; ?></td>
                    <td><?php echo $order->order_timestamp->format("Y-m-d H:i:s"); ?></td>
                    <td><?php echo $reason; ?></td>
                </tr><?php

            }

            if(!$foundReason) {
/*
                echo "<tr><td colspan='9'>
".$logSql."
                    <table style='width: 100%;'>
                    <tr><td>id</td><td>user_id</td><td>controller</td><td>action</td><td>data</td><td>created_datetime</td></tr>";

                foreach($logMessage as $log) {
                    echo "<tr>
                            <td>".$log->id."</td>
                            <td>".$log->user_id."</td>
                            <td>".$log->controller."</td>
                            <td>".$log->action."</td>
                            <td>".$log->data."</td>
                            <td>".$log->created_datetime->format("Y-m-d H:i:s")."</td>
                        </tr>";
                }

                echo "</table>
                </td></tr>";

                exit();
*/
            }


        }

        ?></table><?php

    }

}