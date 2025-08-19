<?php

namespace GFUnit\navision\syncorder;

class BlockMessage
{

    public function __construct()
    {

    }

    public function releaseOrder($companyOrderId)
    {

        $blockMessages = \BlockMessage::find_by_sql("SELECT * FROM blockmessage WHERE company_order_id > 0 && shipment_id = 0 && release_status = 0 && company_order_id = ".intval($companyOrderId));
        foreach($blockMessages as $blockMessage) {
            $message = \BlockMessage::find($blockMessage->id);
            $message->release_status = 1;
            $message->release_date = date('d-m-Y H:i:s');
            $message->release_user = \router::$systemUser->id;
            $message->release_message = "Sync dashboard";
            $message->save();
        }

        $companyOrder = \CompanyOrder::find($companyOrderId);
        if($companyOrder->order_state == 2) {
            $companyOrder->order_state = 3;
            $companyOrder->save();
        }

        \System::connection()->commit();

    }

    public function messageList()
    {

        // Load company block messages
        $blockMessages = \BlockMessage::find_by_sql("SELECT * FROM blockmessage WHERE company_order_id > 0 && shipment_id = 0 && release_status = 0");
        $companyOrderMap = array();

        // Load messages into company map
        foreach($blockMessages as $message) {
            if(!isset($companyOrderMap[$message->company_order_id])) $companyOrderMap[$message->company_order_id] = array();
            $companyOrderMap[$message->company_order_id][] = $message;
        }

        // Output company
        foreach($companyOrderMap as $companyOrderId => $messages) {
            $companyOrder = \CompanyOrder::find($companyOrderId);
            $this->outputOrder($companyOrder,$messages);
        }

    }

    public function outputOrder($companyOrder,$messages)
    {

        echo "<h3>".$companyOrder->order_no.": ".$companyOrder->company_name." - ".$companyOrder->quantity." x ".$companyOrder->shop_name."</h3>";
        echo "<table style='width: 100%;'>";
        foreach($messages as $message) {

            echo "<tr>
                <td>".$message->block_type."</td>
                <td>".$message->created_date->format("d-m-Y H:i")."</td>
                <td>".($message->tech_block == 1 ? "TECH" : "") ."</td>
                <td style='text-align: right;'><button type='button' onClick='document.location=\"".\GFConfig::BACKEND_URL."index.php?rt=unit/navision/syncorder/blocklist/order/".$companyOrder->id."\"'>release</button></td>
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