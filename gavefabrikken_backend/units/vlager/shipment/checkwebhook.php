<?php

namespace GFUnit\vlager\shipment;


class CheckWebhook 
{

    public function runWebhookCheck($output = 0)
    {

        $unprocessedHooks = \HomerunnerWebhook::find_by_sql("select * from homerunner_webhook where handled_time is null order by created ASC LIMIT 5000");

        // Make table header with data
        if($output == 1) {
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th>Webhook ID</th>";
            echo "<th>URL</th>";
            echo "<th>Created</th>";
            echo "<th>Package Number</th>";
            echo "<th>Link</th>";
            echo "<th>Reference</th>";
            echo "<th>Carrier</th>";
            echo "<th>Shipment ID</th>";
            echo "<th>Event Type</th>";
            echo "<th>Event Event</th>";
            echo "<th>JSON</th>";
            echo "</tr>";
        }


        $statusMap = [];

        foreach($unprocessedHooks as $hook) {

            $data = json_decode($hook->data,true);

            //echo "<h3>Webhook ID: ".$hook->id." - ".$hook->url." at ".$hook->created->format("Y-m-d H:i:s")."</h3>";

            $packageNumber = $data["package_number"] ?? "";
            $link = $data["link"] ?? "";
            $reference = $data["reference"] ?? "";
            $carrier = $data["carrier"] ?? "";
            $event = $data["events"][0] ?? null;

            $eventType = "Unknown";
            if($event !== null && isset($event["code"])) {
                $eventType = $event["code"];
            }

            $eventEvent = "Unknown";
            if($event !== null && isset($event["subcode"])) {
                $eventEvent = $event["subcode"];
            }

            $statusMap[$eventType.": ".$eventEvent]=1;
            $shipmentid = 0;

            if(strstr($reference,"Order no: ")) {
                $orderid = intvalgf(trimgf(str_replace("Order no: ","", $reference)));
                $shipment = null;

                try {
                    $shipment = \Shipment::find($orderid);
                } catch (\Exception $e) {
                    echo "Could not find shipment id: ".$orderid;
                }

                if($shipment != null && $packageNumber != $shipment->consignor_labelno) {
                    echo "Shipment ID: ".$orderid." does not match package number: ".$packageNumber;
                }
                else if($shipment->shipment_state < 2) {
                    echo "Shipment ID: ".$orderid." is not in state 2 or higher";
                }
                else {
                    $shipmentid = $orderid;

                }

            }


            // Update webhook
            $updateHook = \HomerunnerWebhook::find($hook->id);

            // Could not find order
            if($shipmentid == 0) {
                $updateHook->handled_time = date("Y-m-d H:i:s");
                $updateHook->handled_state = 5;
                $updateHook->message = "Unknown shipment id";
                $updateHook->save();
            }
            else {

                // Update shipment
                $shipment = \Shipment::find($shipmentid);

                $isPacked = strstr($eventType.": ".$eventEvent, "TRANSIT");
                $isDelivered = strstr($eventType.": ".$eventEvent, "DELIVERED");
                $message = "Shipment ID: " . $shipmentid." - ".$eventType.": ".$eventEvent;

                if($shipment->shipped_date == null && $isPacked) {
                    $shipment->shipped_date = $hook->created;
                    $shipment->ttlink = $link;
                    $shipment->save();
                    $message .= " - is shipped";
                }

                if($shipment->delivered_date == null && $isDelivered) {
                    $shipment->delivered_date = $hook->created;
                    $shipment->ttlink = $link;
                    $shipment->save();
                    $message .= " - is delivered";
                }

                if(trimgf($shipment->ttlink) == "" && trimgf($link) != "") {
                    $shipment->ttlink = $link;
                    $shipment->save();
                    $message .= " - link updated";
                }

                // Update webhook
                $updateHook->handled_time = date("Y-m-d H:i:s");
                $updateHook->handled_state = 1;
                $updateHook->message = $message;
                $updateHook->shipment_id = $shipmentid;
                $updateHook->save();

            }


/*
            echo "<h4>Package Number: ".$packageNumber."</h4>";
            echo "<h4>Link: ".$link."</h4>";
            echo "<h4>Reference: ".$reference."</h4>";
            echo "<h4>Carrier: ".$carrier."</h4>";
            echo "<h4>Event Type: ".$eventType."</h4>";
            echo "<h4>Event Event: ".$eventEvent."</h4>";
            //echo "<h4>Event: ".$event."</h4>";


            // show json in browser with pretty print
            echo "<pre>";
            print_r($data);
            echo "</pre>";

            echo "<br><br>";
*/

            if($output == 1) {
                echo "<tr>";
                echo "<td>" . $hook->id . "</td>";
                echo "<td>" . $hook->url . "</td>";
                echo "<td>" . $hook->created->format("Y-m-d H:i:s") . "</td>";
                echo "<td>" . $packageNumber . "</td>";
                echo "<td>" . $link . "</td>";
                echo "<td>" . $reference . "</td>";
                echo "<td>" . $carrier . "</td>";
                echo "<td>" . $shipmentid . "</td>";
                echo "<td>" . $eventType . "</td>";
                echo "<td>" . $eventEvent . "</td>";
                //echo "<td><pre>".json_encode($data,JSON_PRETTY_PRINT)."</pre></td>";
                echo "</tr>";
            }


        }

        if($output == 1) {
            echo "</table>";

            echo "<pre>";

            print_r($statusMap);
            echo "</pre>";
        }

        \System::connection()->commit();
        exit();
    }

}