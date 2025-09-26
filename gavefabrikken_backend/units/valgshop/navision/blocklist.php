<?php

namespace GFUnit\valgshop\navision;
use GFBiz\Model\Cardshop\BlockMessageLogic;
use GFBiz\units\UnitController;
use GFBiz\valgshop\OrderBuilder;
use GFBiz\valgshop\ShopOrderModel;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\VSOrderWS;


class BlockList
{

    public function dispatch()
    {

        if(isset($_POST["action"]) && isset($_POST["id"]) && trimgf($_POST["action"])) {

            if($_POST["action"] == "movenontech") {

                $id = intval($_POST["id"]);
                $blockMessage = \ShopBlockMessage::find($id);

                if($blockMessage == null || $blockMessage->tech_block != 1 || $blockMessage->release_status != 0) {
                    echo json_encode(array("status" => 0, "message" => "Could not find block"));
                    return;
                }

                $blockMessage->tech_block = 0;
                $blockMessage->save();
                \system::connection()->commit();

                echo json_encode(array("status" => 1, "message" => "Moved to non tech"));
                exit();

            }

            if($_POST["action"] == "movetech") {

                $id = intval($_POST["id"]);
                $blockMessage = \ShopBlockMessage::find($id);

                if($blockMessage == null || $blockMessage->release_status != 0) {
                    echo json_encode(array("status" => 0, "message" => "Could not find block"));
                    return;
                }

                $blockMessage->tech_block = 1;
                $blockMessage->save();
                \system::connection()->commit();

                echo json_encode(array("status" => 1, "message" => "Moved to non tech"));
                exit();

            }

            if($_POST["action"] == "fixtech") {

                $id = intval($_POST["id"]);
                $blockMessage = \ShopBlockMessage::find($id);

                if($blockMessage == null || $blockMessage->release_status != 0) {
                    echo json_encode(array("status" => 0, "message" => "Could not find block"));
                    return;
                }

                $vsState = \NavisionVSState::find_by_shop_id($blockMessage->shop_id);

                if($vsState->state == 2) {
                    if($vsState->last_run_date == null) {
                        $vsState->state = 0;
                    } else {
                        $vsState->state = 1;
                    }
                }
                $vsState->last_run_error = 0;
                $vsState->last_run_message = null;
                $vsState->needs_sync = 1;
                $vsState->save();

                // Update block
                $blockMessage->release_status = 1;
                $blockMessage->release_date = date('d-m-Y H:i:s');
                $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
                $blockMessage->release_message = "Action: fixed";
                $blockMessage->save();
                \system::connection()->commit();

                echo json_encode(array("status" => 1, "message" => "Block approved"));
                exit();
            }
        }

        $this->showList();

    }


    public function showList()
    {

        ?><style>
            body { padding: 0px; margin: 0px; font-size: 12px; font-family: verdana; }
            tr:first-child td { font-weight: bold;border-top: 1px solid #999999;  }
            td { padding: 5px; border-bottom: 1px solid #999999; font-size: 12px; }
            .debugwindow { position: fixed; top: 0%; left: 0px; width: 100%; height: 100%; }
        </style>

        <script src="views/lib/jquery.min.js"></script><?php

        // `tech_block` = 1
        $blockList = \ShopBlockMessage::find_by_sql("SELECT shop_block_message.*, shop.name as shop_name, shop_metadata.order_no  FROM `shop_block_message`, shop, shop_metadata WHERE shop.id = shop_metadata.shop_id and shop.id = shop_block_message.shop_id and  shop_block_message.`release_status` = 0 AND shop_block_message.silent = 0 order by shop_block_message.shop_id desc");

        echo "<div style='padding: 5px; font-size: 16px; font-weight: bold;'>Found ".countgf($blockList)." tech blocks</div>";

        ?><table cellspacing="0" cellpadding="0">
        <tr>
            <td>id</td><td>shop</td><td>order no</td><td>order</td><td>type</td><td>date</td><td>description</td><td>-</td>
        </tr><?php

        $debugData = "";

        foreach($blockList as $blockmessage) {
            ?><tr id="blockrow-<?php echo $blockmessage->id; ?>">
            <td><?php echo $blockmessage->id; ?></td>
            <td><?php echo $blockmessage->shop_id; ?></td>
            <td><?php echo $blockmessage->order_no; ?></td>
            <td><?php echo $blockmessage->shop_name; ?></td>
            <td><?php echo $blockmessage->block_type; ?></td>
            <td><?php echo $blockmessage->created_date->format("d-m-Y H:i:s"); ?></td>
            <td><?php echo htmlspecialchars($blockmessage->description); ?></td>
            <td>
                <button onclick="showDebugMessage(<?php echo $blockmessage->id; ?>)">debug</button>
                <button onclick="fixblockMessage(this,<?php echo $blockmessage->id; ?>)">fix</button>
                <?php if($blockmessage->tech_block == 1) { ?><button onclick="movetoNonTech(this,<?php echo $blockmessage->id; ?>)">TECH - move to nontech</button><?php } ?>
                <?php if($blockmessage->tech_block == 0) { ?><button onclick="movetoTech(this,<?php echo $blockmessage->id; ?>)">NONTECH - move to tech</button><?php } ?>
            </td>
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
            $.post('index.php?rt=unit/valgshop/navision/blocklist',{action: 'movenontech',id: id},function(response) {
                if(response.status == 1) {
                    $('#blockrow-'+id).hide();
                } else {
                    alert('Error: '+response.message);
                }
                console.log(response);
            },'json');
        }

        function movetoTech(elm,id) {
            if(!confirm("Are you sure you want to mark as non tech block?")) return;
            $.post('index.php?rt=unit/valgshop/navision/blocklist',{action: 'movetech',id: id},function(response) {
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
            $.post('index.php?rt=unit/valgshop/navision/blocklist',{action: 'fixtech', id: id},function(response) {
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


}