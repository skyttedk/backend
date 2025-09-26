<?php

namespace GFUnit\monitor\shipment;
use GFBiz\Model\Cardshop\BlockMessageLogic;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }


    public function checkgiftcardshipments($output=0) {

        // Load all orders
        $companyOrder = \CompanyOrder::find_by_sql('select * from company_order where created_datetime > now() - INTERVAL 10 DAY && id not in (37532,37143) && (id not in (select companyorder_id from shipment) or id in (select companyorder_id from shipment where shipto_state != 10))');
        if($output == 1) echo "FOUND: ".count($companyOrder)." orders<br>";

        $success = 0;
        $errors = 0;

        foreach($companyOrder as $companyOrder) {
            if($this->checkOrderShipments($companyOrder,$output)) {
                $success++;
            } else {
                $errors++;
            }
        }

        if($output == 0) {

            if($errors > 0) {
                header("HTTP/1.0 500 Service down");
                echo "There is currently ".$errors." problems with giftcard shipments";
            } else {
                echo "Everything is ok!";
            }

        } else {
            echo $errors." fejl, ".$success." ok";
        }

    }

    private function checkOrderShipments($companyOrder,$output) {


        $nextNumber = $companyOrder->certificate_no_begin;
        $shipmentCount = 0;
        $problems = 0;
        $problemText = "";

        // Load shipments
        $shipmentList = \Shipment::find_by_sql("SELECT * FROM shipment WHERE shipment_type = 'giftcard' && shipment_state != 7 && companyorder_id = ".$companyOrder->id." order by from_certificate_no");
        foreach($shipmentList as $shipment) {

            if($shipment->from_certificate_no == $nextNumber) {
                $problemText .= "Shipment ".$shipment->id." OK: ".$shipment->from_certificate_no." - ".$shipment->to_certificate_no."<br>";
            } else {
                $problemText .=  "Shipment ".$shipment->id." ERROR: ".$shipment->from_certificate_no." - ".$shipment->to_certificate_no." - EXPECTED ".$nextNumber."<br>";
                $problems++;
            }

            $shipmentCount += $shipment->quantity;
            $nextNumber = $shipment->to_certificate_no + 1;

        }

        if($output > 0) {


            if($output == 2 || $problems > 0) {

                echo "<hr>";
                echo $companyOrder->order_no." (".$companyOrder->id.") - ".$companyOrder->company_name."<br>";
                echo $companyOrder->certificate_no_begin." - ".$companyOrder->certificate_no_end."<br>";
                echo $problemText;

            }

        }

        return $problems == 0;

    }



    public function techblocklist()
    {

        ?><style>
            body { padding: 0px; margin: 0px; font-size: 12px; font-family: verdana; }
            tr:first-child td { font-weight: bold;border-top: 1px solid #999999;  }
            td { padding: 5px; border-bottom: 1px solid #999999; font-size: 12px; }
            .debugwindow { position: fixed; top: 0%; left: 0px; width: 100%; height: 100%; }
    </style>

        <script src="views/lib/jquery.min.js"></script><?php

        $blockList = \BlockMessage::find_by_sql("SELECT * FROM `blockmessage` WHERE `release_status` = 0 AND silent = 0 AND `tech_block` = 1 order by id desc");

        echo "<div style='padding: 5px; font-size: 16px; font-weight: bold;'>Found ".countgf($blockList)." tech blocks</div>";

        ?><table cellspacing="0" cellpadding="0">
        <tr>
            <td>id</td><td>company</td><td>order</td><td>shipment</td><td>type</td><td>date</td><td>description</td><td>-</td>
        </tr><?php

        $debugData = "";

        foreach($blockList as $blockmessage) {
            ?><tr id="blockrow-<?php echo $blockmessage->id; ?>">
                <td><?php echo $blockmessage->id; ?></td>
                <td><?php echo $blockmessage->company_id; ?></td>
                <td><?php echo $blockmessage->company_order_id; ?></td>
            <td><?php echo $blockmessage->shipment_id; ?></td>
            <td><?php echo $blockmessage->block_type; ?></td>
            <td><?php echo $blockmessage->created_date->format("d-m-Y H:i:s"); ?></td>
            <td><?php echo htmlspecialchars($blockmessage->description); ?></td>
            <td><button onclick="showDebugMessage(<?php echo $blockmessage->id; ?>)">debug</button> <button onclick="fixblockMessage(this,<?php echo $blockmessage->id; ?>)">fix</button> <button onclick="movetoNonTech(this,<?php echo $blockmessage->id; ?>)">non-tech</button></td>
            </tr><?php

            $debugData .= "<div style='display: none;' ondblclick='closeDebugMessage()' id='debugwindow-".$blockmessage->id."' class='debugwindow'><textarea style='width: 100%; height: 100%;'>".$blockmessage->debug_data."</textarea></div>";
        }

        ?></table><?php


        echo $debugData;

        ?><script>

            function closeDebugMessage() {
                $('.debugwindow').hide();
            }

            function showDebugMessage(id) {
                $('#debugwindow-'+id).show();
            }

            function movetoNonTech(elm,id) {
                if(!confirm("Are you sure you want to mark as non tech block?")) return;
                $.post('index.php?rt=unit/monitor/shipment/movenontech',{id: id},function(response) {
                    if(response.status == 1) {
                        $('#blockrow-'+id).hide();
                    } else {
                        alert('Error: '+response.message);
                    }
                    console.log(response);
                },'json');
            }

            function fixblockMessage(elm,id) {
                if(!confirm("Are you sure you want to mark as fix and continue processing?")) return;
                $.post('index.php?rt=unit/monitor/shipment/fixtech',{id: id},function(response) {
                    if(response.status == 1) {
                        $('#blockrow-'+id).hide();
                    } else {
                        alert('Error: '+response.message);
                    }
                    console.log(response);
                },'json');
            }

        </script><?php

    }

    public function movenontech()
    {

        $id = intval($_POST["id"]);
        $blockMessage = \BlockMessage::find($id);

        if($blockMessage == null || $blockMessage->tech_block != 1 || $blockMessage->release_status != 0) {
            echo json_encode(array("status" => 0, "message" => "Could not find block"));
            return;
        }

        $blockMessage->tech_block = 0;
        $blockMessage->save();
        \system::connection()->commit();

        echo json_encode(array("status" => 1, "message" => "Moved to non tech"));

    }

    public function fixtech()
    {

        $id = intval($_POST["id"]);
        $blockMessage = \BlockMessage::find($id);

        if($blockMessage == null || $blockMessage->tech_block != 1 || $blockMessage->release_status != 0) {
            echo json_encode(array("status" => 0, "message" => "Could not find block"));
            return;
        }

        // Find action
        $action = "";
        if($blockMessage->shipment_id > 0) $action = "approveship";
        else if($blockMessage->company_order_id > 0) $action = "approveorder";
        else if($blockMessage->company_id > 0) $action = "approvecompany";
        if($action == "") throw new \Exception("Could not find approve type");

        BlockMessageLogic::approveBlockMessage($blockMessage->id,$action);
        //echo json_encode(array("status" => 1, "message" => "Block approved"));

    }

}