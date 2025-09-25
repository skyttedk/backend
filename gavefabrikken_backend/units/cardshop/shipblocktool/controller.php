<?php

namespace GFUnit\cardshop\shipblocktool;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CountryHelper;
use GFCommon\Model\Navision\OrderXML;
use GFCommon\Model\Navision\SalesHeaderWS;
use GFCommon\Model\Navision\SalesLineWS;
use GFCommon\Model\Navision\Shipment2XML;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function shipmentblocklistprovider()
    {
        $blocklistModel = new BlockListModel();
        $blocklistModel->runhtmllist();
        
    }

    public function changehandler($shipmentid)
    {

        // Find shipment
        $shipment = \Shipment::find(intvalgf($shipmentid));
        if($shipment == null) {
            echo json_encode(array("status" => 0,"error" => "Kunne ikke finde leverance"));
            return;
        }

        if($shipment->shipment_state == 2) {
            echo json_encode(array("status" => 0,"error" => "Leverance er allerede sendt"));
            return;
        }

        // Get handler
        $handler = trimgf($_POST['handler']);

        // Check handler
        if($handler != "dpse" && $handler != "navision") {
            echo json_encode(array("status" => 0,"error" => "Ukendt handler"));
            return;
        }

        // Log
        if($handler != $shipment->handler) {

            $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
            $shopUser = null;
            $isPrivateDelivery = ($shipment->shipment_type == 'privatedelivery' || $shipment->shipment_type == 'directdelivery');
            if($isPrivateDelivery) {
                $order = \Order::find($shipment->to_certificate_no);
                $shopUser = \ShopUser::find($order->shopuser_id);
            }

            \ActionLog::logAction("ShipmentBlockSupport","Leverance håndtering ændret","Ændret fra ".$shipment->handler." til ".$handler.".",0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,$shopUser == null ? 0 : $shopUser->id,$order == null ? 0 : $order->id,$shipment->id,'',false);
        }

        // Update handler
        $shipment->handler = $handler;
        $shipment->save();

        echo json_encode(array("status" => 1));
        \system::connection()->commit();

    }

    public function modal($id=0) {

        $shipment = \Shipment::find(intvalgf($id));

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        $company = \Company::find($companyOrder->company_id);
        $cardshopSettings = \CardshopSettings::getByShopID($companyOrder->shop_id);

        $isGiftcard = $shipment->shipment_type == 'giftcard';
        $isEarlyorder = $shipment->shipment_type == 'earlyorder';
        $isPrivateDelivery = ($shipment->shipment_type == 'privatedelivery' || $shipment->shipment_type == 'directdelivery');
        $isUnknown = !($isGiftcard || $isEarlyorder || $isPrivateDelivery);

        $typeName = $isGiftcard ? 'Gavekort' : ($isEarlyorder ? 'Tidlig ordre' : ($isPrivateDelivery ? 'Privatlevering' : 'Ukendt'));

        $order = null;
        $shopUser = null;
        if($isPrivateDelivery) {
            $order = \Order::find($shipment->to_certificate_no);
            $shopUser = \ShopUser::find($order->shopuser_id);
        }

        $blockMessages = \BlockMessage::find_by_sql("select * from blockmessage where shipment_id > 0 && shipment_id = ".$shipment->id." order by id desc");
        $isSilent = true;
        foreach($blockMessages as $blockMessage) {
            if($blockMessage->silent == 0) $isSilent = false;
        }


        ob_start();
        ?><div class="container-fluid" style="font-size: 0.8em;">
            <div class="row">
                <div class="col-md-4">

                    <!-- Kunde boks -->
                    <div class="card mb-3">
                        <div class="card-header">Kunde</div>
                        <div class="card-body">

                            <b><?php echo $company->name; ?></b><br>
                            <?php echo $company->bill_to_address; ?><br>
                            <?php echo $company->bill_to_postal_code; ?> <?php echo $company->bill_to_city; ?><br>
                            CVR: <?php echo $company->cvr; ?><br>
                            EAN: <?php echo $company->ean; ?><br><br>

                            <b>Levering:</b><br>
                            <?php echo $company->ship_to_company; ?><br>
                            <?php echo $company->ship_to_address; ?><br>
                            <?php echo $company->ship_to_postal_code; ?> <?php echo $company->ship_to_city; ?>
                            <br><br>
                            <b>Kontaktperson</b><br>
                            <?php echo $company->contact_name; ?><br>
                            <?php echo $company->contact_email; ?><br>
                            <?php echo $company->contact_phone; ?>

                        </div>
                    </div>

                    <!-- Detaljer boks -->
                    <div class="card mb-3">
                        <div class="card-header">Detaljer</div>
                        <div class="card-body">
                            Leverancetype: <?php echo $typeName; ?><br>
                            Leverance oprettet: <?php echo $shipment->created_date->format("Y-m-d H:i"); ?><br>
                            Leverance håndteres af: <?php echo $shipment->handler; ?><br>
                            <?php if($shipment->handler == "dpse") {

                                // make button "Skift til Hedehusene (navision)"
                                echo "<button type='button' class='btn btn-sm btn-primary' onclick='changeHandler(".$shipment->id.",\"navision\")'>Skift til Hedehusene (navision)</button><br>";

                                ?><script>

                                    function changeHandler(shipmentid,handler)
                                    {
                                        $.post('index.php?rt=unit/cardshop/shipblocktool/changehandler/'+shipmentid,{handler:handler},function(response) {
                                            console.log(response);
                                            if(response.status == 1) {
                                                openShipmentModal(shipmentid);
                                            } else {
                                                showShipmentError(response.error || 'Der skete en fejl under handlingen');
                                            }
                                        },'json');
                                    }

                                </script><?php

                            }?>
                            <br>

                            <b>Ordre:</b><br>
                            Cardshop ordre: <?php echo $companyOrder->order_no; ?><br>
                            Koncept: <?php echo $companyOrder->shop_name; ?><br>
                            Deadline: <?php echo $companyOrder->expire_date->format("Y-m-d"); ?><br>
                            Ordre status: <?php echo $companyOrder->getStateText(); ?><br><br>

                            <?php if($isGiftcard) {

                                echo "<b>Kort informationer</b><br>";
                                echo "Kortnr: ".$shipment->from_certificate_no." - ".$shipment->to_certificate_no."<br>";
                                echo "Antal: ".$shipment->quantity."<br>";

                            } else if($isPrivateDelivery) {

                                echo "<b>Kort informationer</b><br>";
                                echo "Kortnr: ".$shipment->from_certificate_no."<br>";

                                if($shopUser == null) {
                                    echo "Kan ikke finde kortet, kontakt teknisk support!<br>";
                                } else {
                                    echo "Kortstatus: ";

                                    if($shopUser->blocked == 1) echo "krediteret";
                                    else if($shopUser->shutdown == 1) echo "blokkeret";
                                    else echo "aktivt";
                                    echo "<br>";

                                }

                            }

                            echo "<br>";


                            if($order != null) {

                                echo "<b>Ordre informationer</b><br>";
                                echo "Ordrenr: ".$order->order_no."<br>";
                                echo "Tidspunkt for valg: ".$order->order_timestamp->format("Y-m-d H:i:s")."<br>";
                                echo "Valg: ".$shipment->itemno.": ".$shipment->description."<br>";

                            }

                            echo "<br><b>Leverance indhold:</b><br>";

                            if($isGiftcard) {
                                echo $shipment->quantity." stk. fysiske gavekort";
                            } else {

                                $itemLines = [];

                                if(trimgf($shipment->itemno) != "" && intvalgf($shipment->quantity) > 0) {
                                    $itemLines[] = intvalgf($shipment->quantity)." stk. ".trimgf($shipment->itemno);
                                }
                                if(trimgf($shipment->itemno2) != "" && intvalgf($shipment->quantity2) > 0) {
                                    $itemLines[] = intvalgf($shipment->quantity2)." stk. ".trimgf($shipment->itemno2);
                                }

                                if(trimgf($shipment->itemno3) != "" && intvalgf($shipment->quantity3) > 0) {
                                    $itemLines[] = intvalgf($shipment->quantity3)." stk. ".trimgf($shipment->itemno3);
                                }

                                if(trimgf($shipment->itemno4) != "" && intvalgf($shipment->quantity4) > 0) {
                                    $itemLines[] = intvalgf($shipment->quantity4)." stk. ".trimgf($shipment->itemno4);
                                }

                                if(trimgf($shipment->itemno5) != "" && intvalgf($shipment->quantity5) > 0) {
                                    $itemLines[] = intvalgf($shipment->quantity5)." stk. ".trimgf($shipment->itemno5);
                                }

                                echo implode("<br>",$itemLines);

                            }

                            ?>
                            <br><br>

                            <?php if($shopUser != null) { ?>
                            <?php echo $shopUser->getDeliveryStatus(true); ?>
                            <?php } ?>

                        </div>
                    </div>

                   

                </div>

                <!-- Venstre kolonne -->
                <div class="col-md-4">

                    <!-- Modtager boks -->
                    <div class="card mb-3">
                        <div class="card-header">Modtager</div>
                        <div class="card-body">
                            <form>
                                <div class="form-group">
                                    <label for="recipientName">Navn</label>
                                    <input type="text" class="form-control" id="recipientName" placeholder2="Indtast navn" value="<?php echo $shipment->shipto_name; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="recipientAddress">Adresse</label>
                                    <input type="text" class="form-control" id="recipientAddress" placeholder2="Indtast adresse" value="<?php echo $shipment->shipto_address; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="recipientAddress2">Adresse2</label>
                                    <input type="text" class="form-control" id="recipientAddress2" placeholder2="Indtast adresse2" value="<?php echo $shipment->shipto_address2; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="recipientZip">Postnummer</label>
                                    <input type="text" class="form-control" id="recipientZip" placeholder2="Indtast postnummer" value="<?php echo $shipment->shipto_postcode; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="recipientCity">By</label>
                                    <input type="text" class="form-control" id="recipientCity" placeholder2="Indtast by" value="<?php echo $shipment->shipto_city; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="recipientCity">Land</label>

                                    <?php

                                    $languageMap = CountryHelper::getLanguageMap();
                                    ?><select id="recipientCountry" class="form-control">
                                        <?php

                                        if(trim(strtolower($shipment->shipto_country)) == "danmark") $shipment->shipto_country = "DK";
                                        if(trim(strtolower($shipment->shipto_country)) == "sverige") $shipment->shipto_country = "SE";
                                        if(trim(strtolower($shipment->shipto_country)) == "norge") $shipment->shipto_country = "NO";
                                        
                                        if(trimgf($shipment->shipto_country) == "") {
                                            if($cardshopSettings->language_code == 1) $shipment->shipto_country = "DK";
                                            else if($cardshopSettings->language_code == 4) $shipment->shipto_country = "NO";
                                            else if($cardshopSettings->language_code == 5) $shipment->shipto_country = "SE";
                                        }

                                        $hasValue = false;
                                        foreach($languageMap as $value) {
                                            ?><option value="<?php echo $value[0]; ?>" <?php if(trim(strtolower($shipment->shipto_country)) == trim(strtolower($value[0]))) echo "selected"; ?>><?php echo $value[1]; ?></option><?php
                                            if(trim(strtolower($shipment->shipto_country)) == trim(strtolower($value[0]))) $hasValue = true;
                                        }

                                        if(!$hasValue) {
                                            ?><option value="<?php echo $shipment->shipto_country; ?>" selected><?php echo $shipment->shipto_country; ?> (manuelt angivet)</option><?php
                                        }

                                        ?>
                                    </select>

                                    <?php if($shipment->handler == "dpse") echo "<div class='alert alert-warning'>DistributionPlus sender kun til Sverige, skift til nav hvis der skiftes land.</div>" ?>
                                    
                                </div>
                                <div class="form-group">
                                    <label for="recipientZip">E-mail</label>
                                    <input type="text" class="form-control" id="recipientEmail" placeholder2="Indtast e-mail" value="<?php echo $shipment->shipto_email; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="recipientZip">Telefon</label>
                                    <input type="text" class="form-control" id="recipientPhone" placeholder2="Indtast telefon" value="<?php echo $shipment->shipto_phone; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="recipientZip">Kontakt</label>
                                    <input type="text" class="form-control" id="recipientContact" placeholder2="Indtast kontakt" value="<?php echo $shipment->shipto_contact; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="recipientZip">Leveringsnote (er ikke garanteret at komme på label)</label>
                                    <input type="text" class="form-control" id="shipmentNote" placeholder="Leveringsnote" value="<?php echo $shipment->shipment_note; ?>">
                                </div>

                                <button type="button" class="btn btn-primary" onclick="saveReceiverInfo()">Gem leverance oplysninger</button>

                            </form>
                            <script>

                                var isProcessingShipment = false;

                                function saveReceiverInfo() {

                                    if(isProcessingShipment) return;
                                    isProcessingShipment = true;

                                    var shipmentId = document.getElementById('shipmentId').value;
                                    var recipientName = document.getElementById('recipientName').value;
                                    var recipientAddress = document.getElementById('recipientAddress').value;
                                    var recipientAddress2 = document.getElementById('recipientAddress2').value;
                                    var recipientZip = document.getElementById('recipientZip').value;
                                    var recipientCity = document.getElementById('recipientCity').value;
                                    var recipientCountry = document.getElementById('recipientCountry').value;
                                    var recipientEmail = document.getElementById('recipientEmail').value;
                                    var recipientPhone = document.getElementById('recipientPhone').value;
                                    var recipientContact = document.getElementById('recipientContact').value;
                                    var shipmentNote = document.getElementById('shipmentNote').value;

                                    var data = {
                                        shipment_id: shipmentId,
                                        recipientName: recipientName,
                                        recipientAddress: recipientAddress,
                                        recipientAddress2: recipientAddress2,
                                        recipientZip: recipientZip,
                                        recipientCity: recipientCity,
                                        recipientCountry: recipientCountry,
                                        recipientEmail: recipientEmail,
                                        recipientPhone: recipientPhone,
                                        recipientContact: recipientContact,
                                        shipmentNote: shipmentNote
                                    };

                                    $.ajax({
                                        url: '?rt=unit/cardshop/shipblocktool/saveshipmentinfo',
                                        type: 'POST',
                                        data: data,
                                        success: function(response) {
                                            isProcessingShipment = false;
                                            responseData = JSON.parse(response);
                                            if(responseData == null || responseData.status == 0) {

                                                showShipmentError(responseData.error || 'Der skete en fejl under gemning af leverance oplysninger');
                                            } else {
                                                showShipmentSuccess('Leverance oplysninger gemt');
                                            }
                                        },
                                        error: function(response) {
                                            isProcessingShipment = false;
                                            console.log('error');
                                            console.log(response);
                                            showShipmentError('Der skete en fejl under gemning af leverance oplysninger');
                                        }
                                    });

                                }

                                function savesupportnote()
                                {
                                    if(isProcessingShipment) return;
                                    isProcessingShipment = true;

                                    var supportnote = document.getElementById('supportnote').value;
                                    var supportsilent = document.getElementById('supportsilent').checked;
                                    var shipmentId = document.getElementById('shipmentId').value;
                                    var data = {
                                        shipment_id: shipmentId,
                                        supportnote: supportnote,
                                        supportsilent: supportsilent
                                    };

                                    $.ajax({
                                        url: '?rt=unit/cardshop/shipblocktool/savesupportnote',
                                        type: 'POST',
                                        data: data,
                                        success: function(response) {
                                            isProcessingShipment = false;
                                            responseData = JSON.parse(response);
                                            if(responseData == null || responseData.status == 0) {

                                                showShipmentError(responseData.error || 'Der skete en fejl under gemning af support oplysninger');
                                            } else {
                                                showShipmentSuccess('Support oplysninger gemt');
                                            }
                                        },
                                        error: function(response) {
                                            isProcessingShipment = false;
                                            console.log('error');
                                            console.log(response);
                                            showShipmentError('Der skete en fejl under gemning af support oplysninger');
                                        }
                                    });
                                }

                                function showShipmentError(message) {
                                    var toast = document.getElementById('shipmentmodalToast');

                                    message = '<div class="alert alert-danger" role="alert">' + message + '</div>';

                                    toast.innerHTML = message;
                                    toast.style.display = 'block';
                                    setTimeout(function() {
                                        toast.style.display = 'none';
                                    }, 5000);
                                }

                                function showShipmentSuccess(message) {
                                    var toast = document.getElementById('shipmentmodalToast');

                                    // put message into a success container (bootstrap)
                                    message = '<div class="alert alert-success" role="alert">' + message + '</div>';

                                    toast.innerHTML = message;
                                    toast.style.display = 'block';
                                    setTimeout(function() {
                                        toast.style.display = 'none';
                                    }, 5000);
                                }


                            </script>

                        </div>
                    </div>
                </div>


                <?php

                $openMessages = 0;
                $closedMessage = 0;

                foreach($blockMessages as $blockMessage) {
                    if($blockMessage->release_status == 0) {

                       $openMessages++;

                    } else {
                        $closedMessage++;
                    }
                }

                $actionLogMessages = \ActionLog::find_by_sql("SELECT * FROM actionlog WHERE (shipment_id > 0 && shipment_id = ".$shipment->id.") or (shop_user_id > 0 && shop_user_id = ".($shopUser == null ? 0 : $shopUser->id).") order by id desc");



                ?>

                <!-- Højre kolonne -->
                <div class="col-md-4">

                    <!-- Fejl og historik boks -->
                    <div class="card mb-3">
                        <div class="card-header">Fejl og Historik</div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="errorTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="open-errors-tab" data-toggle="tab" href="#open-errors" role="tab" aria-controls="open-errors" aria-selected="false">Åbne fejl (<?php echo $openMessages; ?>)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="resolved-errors-tab" data-toggle="tab" href="#resolved-errors" role="tab" aria-controls="resolved-errors" aria-selected="false">Løste fejl (<?php echo $closedMessage; ?>)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " id="history-tab" data-toggle="tab" href="#history" role="tab" aria-controls="history" aria-selected="true">Historik (<?php echo count($actionLogMessages); ?>)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="history" aria-selected="true">Info og hjælp</a>
                                </li>

                            </ul>
                            <div class="tab-content" id="errorTabContent">
                                <div class="tab-pane fade show active" id="open-errors" role="tabpanel" aria-labelledby="open-errors-tab" style="height: 600px; overflow: auto;">
                                    <table style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Dato</th>
                                                <th>Type</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php



                                            foreach($blockMessages as $blockMessage) {
                                                if($blockMessage->release_status == 0) {

                                                    $details = \BlockMessage::getBlockType($blockMessage->block_type);
                                                    $typeDescription = $details['description'] ?? $blockMessage->block_type;

                                                    ?><tr style="font-weight: bold;">
                                                        <td><?php echo $blockMessage->created_date->format("d-m-Y H:i"); ?></td>
                                                        <td><?php echo $typeDescription; if($blockMessage->tech_block == 1) echo " (tech)"; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2"><?php echo nl2br(htmlentities(str_replace("<br>","\n",$blockMessage->description))); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" style="text-align: right;">
                                                            <?php

                                                            $shownActions = 0;
                                                            if(countgf($details["actions"]) > 0)  {
                                                                foreach($details["actions"] as $action) {

                                                                    $actionDetails = \BlockMessage::getAction($action);
                                                                    if($actionDetails != null) {

                                                                        $btnType = "primary";
                                                                        if($action == "blockship") $btnType = "danger";

                                                                        echo " <button type='button' class='btn btn-".$btnType."' title='".$actionDetails["description"]."' onclick=\"shipmentAction(".$blockMessage->id.",'".$action."','".$actionDetails['name']."')\">".$actionDetails['name']."</button>&nbsp; ";

                                                                    }

                                                                }
                                                            }

                                                            ?>
                                                        </td>
                                                    </tr><?php

                                                }
                                            }

                                            ?>
                                        </tbody>
                                    </table>


                                    <script>

                                        function shipmentAction(blockid,action,actionText) {

                                            if(!confirm("Er du sikker på at du vil udføre handlingen '"+actionText+"'?")) {
                                                return;
                                            }

                                            console.log("Action: "+action+" on block "+blockid);
                                            $.post('index.php?rt=unit/cardshop/approvelist/approve/'+blockid,{action:action},function(response) {
                                                console.log(response);
                                                if(response.status == 1) {
                                                    openShipmentModal(<?php echo $shipment->id; ?>);
                                                    updateTable();
                                                } else {
                                                    showShipmentError(response.error || 'Der skete en fejl under handlingen');
                                                }
                                            });
                                        }

                                    </script>

                                    <div class="alert alert-warning" role="alert">
                                        <p><b>Husk</b>: Husk at opdater og gem leverance hvis der er behov for det, <b>før</b> fejlen løses.</p>
                                    </div>

                                </div>
                                <div class="tab-pane fade" id="resolved-errors" role="tabpanel" aria-labelledby="resolved-errors-tab" style="height: 600px; overflow: auto;">
                                    <table style="width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>Dato</th>
                                            <th>Type</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php

                                        foreach($blockMessages as $blockMessage) {
                                            if($blockMessage->release_status == 1) {

                                                ?><tr style="font-weight: bold;">
                                                <td><?php echo $blockMessage->created_date->format("d-m-Y  H:i"); ?></td>
                                                <td><?php echo $blockMessage->block_type; if($blockMessage->tech_block == 1) echo " (tech)"; ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><?php echo nl2br(htmlentities(str_replace("<br>","\n",$blockMessage->description))); ?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><i>Løst af: <?php

                                                            try {
                                                        if(intvalgf($blockMessage->release_user) > 0) {
                                                            $user = \SystemUser::find(intvalgf($blockMessage->release_user));
                                                            echo $user->name;
                                                        } else echo "Ukendt bruger";
                                                    } catch(Exception $e) {
                                                                echo "Ukendt bruger";
                                                            }

                                                            ?>, d. <?php echo $blockMessage->release_date == null ? "Ukendt dato" : $blockMessage->release_date->format("d-m-Y H:i"); ?></i></td>
                                                </tr><?php

                                            }
                                        }

                                        ?>
                                        </tbody>
                                    </table>


                                </div>
                                <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab" style="height: 600px; overflow: auto;">
                                    <table style="width: 100%;">
                                        <thead>
                                        <tr>
                                            <th>Dato</th>
                                            <th>Beskrivelse</th>

                                        </tr>
                                        </thead>
                                        <tbody>

                                        <?php

                                        if(count($actionLogMessages) == 0) {
                                            echo "<tr><td colspan='2'>Ingen historik fundet</td></tr>";
                                        } else {
                                            foreach($actionLogMessages as $actionLogMessage) {
                                                echo "<tr style='font-weight: bold;'>
                                                    <td>".$actionLogMessage->created->format("d-m-Y  H:i")."</td>
                                                    <td>".$actionLogMessage->headline."</td>
                                                </tr>";
                                                if(trimgf($actionLogMessage->details)) {
                                                    echo "<tr><td colspan='2'>".$actionLogMessage->details."</td></tr>";
                                                }
                                            }
                                        }

                                        ?>

                                        </tbody>
                                    </table>
                                </div>

                                <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="info-tab" style="height: 600px; overflow: auto;">

                                    <h3>Leverance Fejlliste Hjælpevejledning</h3>

                                    <h4>Oversigt over Leverance Fejlliste</h4>
                                    <p>Leverance fejllisten viser fejl, der er opstået under tjek af leveringsinformationer eller afsendelse, og som kræver manuel håndtering. Typiske fejl inkluderer adresseændringer eller ændringer i forsendelsesmetode. Du kan søge på ordre- og modtagerinformationer, filtrere efter koncept og vælge at se skjulte fejl. Klik på "Vis fejl" for at åbne en fejlbesked.</p>

                                    <h4>Visning af en Leverance</h4>
                                    <p>Når en leverance vises, er der flere sektioner:</p>
                                    <ul>
                                        <li><strong>Kunde:</strong> Viser information om virksomheden, der har købt kortet, og kontaktpersonen.</li>
                                        <li><strong>Detaljer:</strong> Indeholder ordre- og leverancedetaljer som type, oprettelsesdato, forsendelsesmetode, kortinformation og nuværende track and trace status.</li>
                                        <li><strong>Modtager:</strong> Viser modtagerens navn, adresse, by, land, e-mail og telefonnummer. Her kan du rette eventuelle fejl og gemme ændringerne. Det er vigtigt at opdatere modtagerinformationer før frigivelse af fejlen.</li>
                                    </ul>

                                    <h4>Fejl og Historik</h4>
                                    <p>Inddelt i tre faner:</p>
                                    <ul>
                                        <li><strong>Åbne fejl:</strong> Viser aktuelle fejl, der skal løses før afsendelse.</li>
                                        <li><strong>Løste fejl:</strong> Viser tidligere løste fejl, som kan indikere tidligere problemer.</li>
                                        <li><strong>Historik:</strong> Viser handlinger som login, valg og opdateringer på ordren.</li>
                                    </ul>

                                    <h4>Support Noter</h4>
                                    <p>Der er et felt til supportnoter, hvor du kan skrive noter til leverancen, som kun er synlige i dette værktøj. Noterne kan være til dig selv eller andre, og kan indeholde aftaler med kunden eller lignende. Du kan også markere leverancen som skjult. Noter gemmes med en separat knap.</p>

                                    <h4>Om Skjulte Fejl</h4>
                                    <p>Skjulte fejl er dem, der afventer svar. Du kan markere en leverance som skjult, så den ikke vises i fejllisten som standard, men kan tilgås senere.</p>

                                    <h4>Normal Proces for Løsning af Fejl</h4>
                                    <p>Ved fejl:</p>
                                    <ol>
                                        <li>Start med at tjekke under "Åbne fejl" for at identificere problemet.</li>
                                        <li>Opdater og gem leveringsinformationerne, hvis nødvendigt.</li>
                                        <li>Tjek forsendelsesmetoden, og skift om nødvendigt.</li>
                                        <li>Når oplysningerne er opdateret, kan fejlen løses, og systemet kan behandle leverancen igen.</li>
                                    </ol>

                                    <h4>Muligheder for Fejl</h4>
                                    <ul>
                                        <li><strong>Godkend:</strong> Godkender, at problemet er løst, og sender leverancen til afsendelse igen.</li>
                                        <li><strong>Bloker:</strong> Fjerner fejlbeskeden og markerer leverancen som inaktiv, så den ikke sendes.</li>
                                    </ul>

                                    <h4>Support</h4>
                                    <p>Er du i tvivl om, hvordan en leverance skal håndteres, eller oplever du fejl, kan du kontakte Søren på <a href="mailto:sc@interactive.dk">sc@interactive.dk</a>.</p>



                                </div>
                            </div>
                        </div>


                        
                    </div>

                    <!-- Detaljer boks -->
                    <div class="card mb-3">
                        <div class="card-header">Support noter</div>
                        <div class="card-body">
                            <textarea style="width: 100%; height: 60px;" placeholder="Til noter om ordren fra support." id="supportnote"><?php echo $shipment->support_note; ?></textarea><br>
                            <label><input type="checkbox" <?php if($isSilent) echo "checked"; ?> id="supportsilent"> Skjul sag fra listen (findes under vis skjulte)</label><br>
                            <button type="submit" class="btn btn-primary" onclick="savesupportnote()">Gem support oplysninger</button>
                        </div>
                    </div>
                    
                </div>
            </div>


            <input type="hidden" name="shipment_id" id="shipmentId" value="<?php echo $shipment->id; ?>">

        </div><?php

        $bodyContent = ob_get_contents();
        ob_end_clean();

       
        $footerContent = "<div style='float: left; position: absolute; left: 0px; display: none;' id='shipmentmodalToast'></div><button type='button' class='btn btn-secondary' data-dismiss='modal'>Luk</button>";
                  //<button type='button' class='btn btn-primary'>Foretag handling</button>";


        $footerContent .= "<button type='button' class='btn btn-primary' onclick='window.open(\"index.php?rt=unit/cardshop/main&shopid=".$company->id."\",\"_blank\")'>Åben i cardshop</button>";


// Opret et array med body og footer
        $response = [
            'body' => $bodyContent,
            'footer' => $footerContent
        ];

// Sæt header til JSON
        header('Content-Type: application/json');

// Returner JSON-respons
        echo json_encode($response);

    }

    public function savesupportnote()
    {

        // Find shipment id and load shipment, output error if not found
        $shipmentId = intvalgf($_POST['shipment_id']);
        $shipment = \Shipment::find($shipmentId);
        if($shipment == null) {
            echo json_encode(array("status" => 0,"error" => "Kunne ikke finde leverance"));
            return;
        }

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        $company = \Company::find($companyOrder->company_id);
        $order = null;
        $shopUser = null;
        if(($shipment->shipment_type == 'privatedelivery' || $shipment->shipment_type == 'directdelivery')) {
            $order = \Order::find($shipment->to_certificate_no);
            $shopUser = \ShopUser::find($order->shopuser_id);
        }

        $logMessage = "";

        // Update log message if note is updated, add new note
        if(trimgf($shipment->support_note) != trimgf($_POST['supportnote'])) {
            $logMessage = "Support note ændret til '".trimgf($_POST['supportnote'])."'";
        }

        // Update support note on shipment
        $shipment->support_note = trimgf($_POST['supportnote']);
        $shipment->save();

        // Is silent
        $isSilent = intvalgf($_POST['supportsilent']);
        $wasSilent = true;

        // Load block messages
        $blockMessages = \BlockMessage::find_by_sql("select * from blockmessage where shipment_id > 0 && shipment_id = ".$shipment->id." order by id desc");
        foreach($blockMessages as $blockMessage) {
            if($blockMessage->release_status == 0) {
                if($blockMessage->silent == 0) $wasSilent = false;

                $bm = \BlockMessage::find($blockMessage->id);
                $bm->silent = $isSilent;
                $bm->save();
            }
        }

        // Add to log
        if($isSilent != $wasSilent) {
            $logMessage .= ($isSilent ? "Sag skjult" : "Sag vises igen");
        }

        // Log action
        if($logMessage != "") {
            \ActionLog::logAction("ShipmentBlockSupport","Support detaljer gemt (support værktøj)",$logMessage,0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,$shopUser == null ? 0 : $shopUser->id,$order == null ? 0 : $order->id,$shipment->id,'',false);
        }

        echo json_encode(array("status" => 1,"message" => "Support noter gemt!"));
        \system::connection()->commit();

    }

    public function saveshipmentinfo() {

        // Find shipment id and load shipment, output error if not found
        $shipmentId = intvalgf($_POST['shipment_id']);
        $shipment = \Shipment::find($shipmentId);
        if($shipment == null) {
            echo json_encode(array("status" => 0,"error" => "Kunne ikke finde leverance"));
            return;
        }

        $companyOrder = \CompanyOrder::find($shipment->companyorder_id);
        $company = \Company::find($companyOrder->company_id);
        $order = null;
        $shopUser = null;
        if(($shipment->shipment_type == 'privatedelivery' || $shipment->shipment_type == 'directdelivery')) {
            $order = \Order::find($shipment->to_certificate_no);
            $shopUser = \ShopUser::find($order->shopuser_id);
        }

        // Construct a log message with the changed data (do not log if values are the same, show old and new value)
        $logMessage = "";
        if(trimgf($shipment->shipto_name) != trimgf($_POST['recipientName'])) {
            $logMessage .= "Navn: ".$shipment->shipto_name." ændret til ".trimgf($_POST['recipientName']).". ";
        }
        if(trimgf($shipment->shipto_address) != trimgf($_POST['recipientAddress'])) {
            $logMessage .= "Adresse: ".$shipment->shipto_address." ændret til ".trimgf($_POST['recipientAddress']).". ";
        }
        if(trimgf($shipment->shipto_address2) != trimgf($_POST['recipientAddress2'])) {
            $logMessage .= "Adresse2: ".$shipment->shipto_address2." ændret til ".trimgf($_POST['recipientAddress2']).". ";
        }
        if(trimgf($shipment->shipto_postcode) != trimgf($_POST['recipientZip'])) {
            $logMessage .= "Postnummer: ".$shipment->shipto_postcode." ændret til ".trimgf($_POST['recipientZip']).". ";
        }
        if(trimgf($shipment->shipto_city) != trimgf($_POST['recipientCity'])) {
            $logMessage .= "By: ".$shipment->shipto_city." ændret til ".trimgf($_POST['recipientCity']).". ";
        }
        if(trimgf($shipment->shipto_country) != trimgf($_POST['recipientCountry'])) {
            $logMessage .= "Land: ".$shipment->shipto_country." ændret til ".trimgf($_POST['recipientCountry']).". ";
        }
        if(trimgf($shipment->shipto_email) != trimgf($_POST['recipientEmail'])) {
            $logMessage .= "E-mail: ".$shipment->shipto_email." ændret til ".trimgf($_POST['recipientEmail']).". ";
        }
        if(trimgf($shipment->shipto_phone) != trimgf($_POST['recipientPhone'])) {
            $logMessage .= "Telefon: ".$shipment->shipto_phone." ændret til ".trimgf($_POST['recipientPhone']).". ";
        }
        if(trimgf($shipment->shipto_contact) != trimgf($_POST['recipientContact'])) {
            $logMessage .= "Kontakt: ".$shipment->shipto_contact." ændret til ".trimgf($_POST['recipientContact']).". ";
        }
        if(trimgf($shipment->shipment_note) != trimgf($_POST['shipmentNote'])) {
            $logMessage .= "Leveringsnote: ".$shipment->shipment_note." ændret til ".trimgf($_POST['shipmentNote']).". ";
        }

        if($logMessage == "") {
            echo json_encode(array("status" => 0,"error" => "Ingen værdier ændret i modtager informationerne."));
            return;
        }

        // Update shipment with new data
        $shipment->shipto_name = trimgf($_POST['recipientName']);
        $shipment->shipto_address = trimgf($_POST['recipientAddress']);
        $shipment->shipto_address2 = trimgf($_POST['recipientAddress2']);
        $shipment->shipto_postcode = trimgf($_POST['recipientZip']);
        $shipment->shipto_city = trimgf($_POST['recipientCity']);
        $shipment->shipto_country = trimgf($_POST['recipientCountry']);
        $shipment->shipto_email = trimgf($_POST['recipientEmail']);
        $shipment->shipto_phone = trimgf($_POST['recipientPhone']);
        $shipment->shipto_contact = trimgf($_POST['recipientContact']);
        $shipment->shipment_note = trimgf($_POST['shipmentNote']);
        $shipment->save();

        // Update shopuser data on shipment
        $debugData = "";
        try {
            $logData = $shipment->updateUserDataFromShipment();
            $debugData = json_encode($logData);
        } catch (\Exception $e) {
            $debugData = json_encode(array("error" => $e->getMessage()));
        }

        // Save log
        $logMessage = "Forsendelses id ".$shipmentId." har fået opdateret leveringsinformationer. ".$logMessage;
        \ActionLog::logAction("ShipmentBlockUpdate","Informationer gemt (support værktøj)",$logMessage,0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,$shopUser == null ? 0 : $shopUser->id,$order == null ? 0 : $order->id,$shipment->id,'',$debugData);

        echo json_encode(array("status" => 1,"message" => "Leveringsinformationer gemt!"));
        \system::connection()->commit();


    }


    /**
     * IN THE START SHOPUSERS WHERE NOT UPDATED WITH THE NEW DATA
     * THIS FUNCTION WAS BORN TO FIX THAT!
     */
    public function afterupdateshopusers()
    {

        // no need to run this again
        return;

        // Find all actionslogs with empty debugdata and type set to ShipmentBlockUpdate
        $actionlogList = \ActionLog::find('all',array('conditions' => 'debugdata = "" and type = "ShipmentBlockUpdate"'));
        echo "FOUND: ".count($actionlogList)."<br>";

        foreach($actionlogList as $index => $actionLog) {

            // Load shipment
            try {
                $shipment = \Shipment::find($actionLog->shipment_id);
                if ($shipment == null) {
                    echo "Shipment not found for actionlog id " . $actionLog->id . "<br>";
                    continue;
                }
            }
            catch(\Exception $e) {
                echo "Shipment not found for actionlog id ".$actionLog->id."<br>";
                continue;
            }

            // Update shopuser data on shipment
            try {
                $logData = $shipment->updateUserDataFromShipment();
                $debugData = json_encode($logData);
            } catch (\Exception $e) {
                $debugData = json_encode(array("error" => $e->getMessage()));
            }

            // Print debug data
            echo "<b>Processing shipment ".$shipment->id."</b><br>";
            echo $actionLog->details."<br>";
            echo "<pre>".print_r(json_decode($debugData,true),true)."</pre>";
            echo "<br><br>";

            // Save debutdata to actionlog
            $actionLog->debugdata = $debugData;
            $actionLog->save();

            if($index >= 50) {
                break;
            }

        }

        \system::connection()->commit();

    }

}
