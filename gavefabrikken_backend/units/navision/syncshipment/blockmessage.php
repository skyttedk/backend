<?php

namespace GFUnit\navision\syncshipment;

class BlockMessage
{

    public function __construct()
    {

    }

    public function releaseShipment($shipmentID)
    {

        $blockMessages = \BlockMessage::find_by_sql("SELECT * FROM blockmessage WHERE shipment_id > 0 && release_status = 0 && shipment_id = ".intval($shipmentID));
        foreach($blockMessages as $blockMessage) {
            $message = \BlockMessage::find($blockMessage->id);
            $message->release_status = 1;
            $message->release_date = date('d-m-Y H:i:s');
            $message->release_user = \router::$systemUser->id;
            $message->release_message = "Sync dashboard";
            $message->save();
        }

        \System::connection()->commit();

    }

    public function messageList()
    {

        // Load company block messages
        $blockMessages = \BlockMessage::find_by_sql("SELECT * FROM blockmessage WHERE shipment_id > 0 && release_status = 0");
        $shipmentMap = array();

        // Load messages into company map
        foreach($blockMessages as $message) {
            if(!isset($shipmentMap[$message->shipment_id])) $shipmentMap[$message->shipment_id] = array();
            $shipmentMap[$message->shipment_id][] = $message;
        }

        // Output company
        foreach($shipmentMap as $shipmentId => $messages) {
            $shipment = \Shipment::find($shipmentId);
            $this->outputShipment($shipment,$messages);
        }

    }

    public function outputShipment($shipment,$messages)
    {

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);

        echo "<h3>".$shipment->id.": ".$shipment->shipment_type." - ".$companyOrder->order_no."</h3>";
        echo "<table style='width: 100%;'>";
        foreach($messages as $message) {

            echo "<tr>
                <td>".$message->block_type."</td>
                <td>".$message->created_date->format("d-m-Y H:i")."</td>
                <td>".($message->tech_block == 1 ? "TECH" : "") ."</td>
                <td style='text-align: right;'><button type='button' onClick='document.location=\"".\GFConfig::BACKEND_URL."index.php?rt=unit/navision/syncshipment/blocklist/shipment/".$shipment->id."\"'>release</button></td>
            </tr>";

            echo "<tr>
                <td colspan='4'>
                <div style='float: right;'><button type='button' onClick=\"document.getElementById('".$message->id."-debug').style.display = 'block';this.style.display = 'none';\">show debug data</button></div>
                ".$message->description."
                </td>
            </tr>";

            echo "<tr style='display: none;' id='".$message->id."-debug'>
                <td colspan='4'>".nl2br($message->debug_data)."</td>
            </tr>";

        }
        echo "</table>";
        echo "<hr>";



    }


}