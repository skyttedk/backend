<?php

namespace GFUnit\vlager\lagerfront;
use GFBiz\units\UnitController;
use GFUnit\vlager\utils\Template;
use GFUnit\vlager\utils\VLagerCounter;

class Frontpage
{

    public static function outputFrontpage($url, \VLager $vlager)
    {

        $incomingList = \VLagerIncoming::find_by_sql("SELECT vlager_incoming.*, sum(vlager_incoming_line.quantity_order) as ordercount, sum(vlager_incoming_line.quantity_received) as receivedcount, count(distinct vlager_incoming_line.id) as linecount FROM vlager_incoming, vlager_incoming_line where vlager_incoming.id = vlager_incoming_line.vlager_incoming_id and  vlager_incoming.vlager_id = ".$vlager->id." group by vlager_incoming.id ORDER BY received DESC, created ASC");



        Template::templateTop();
        ?>
        <style>
            body {
                padding-top: 56px;
            }
            .card {
                margin-bottom: 20px;
            }
        </style>

        <!-- Top Navigation Bar -->
        <?php Template::outputFrontendHeader($url, $vlager); ?>
        <br>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">

                    <h3>Varer i transit</h3>
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">Ordre nr</th>

                            <th scope="col">Oprettet</th>
                            <th scope="col">Modtaget</th>
                            <th scope="col">Linjer</th>
                            <th scope="col">Varer</th>
                            <th scope="col">-</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php

                        $intransit = 0;

                        foreach($incomingList as $incoming) {
                            if($incoming->received == null) {
                                ?><tr>
                                <td><?php echo $incoming->sono; ?></td>
                                <td><?php echo $incoming->created == null ? "Ikke modtaget" : $incoming->created->format("Y-m-d H:i"); ?></td>
                                <td><?php echo $incoming->received == null ? "Ikke modtaget" : $incoming->received->format("Y-m-d H:i"); ?></td>
                                <td><?php echo $incoming->linecount; ?></td>
                                <td><?php
                                    echo $incoming->ordercount;
                                    if($incoming->ordercount < $incoming->receivedcount) echo " (+".($incoming->receivedcount-$incoming->ordercount).")";
                                    else if($incoming->ordercount > $incoming->receivedcount) echo " (".($incoming->receivedcount-$incoming->ordercount).")";
                                    ?></td>
                                <td><a href="<?php echo $url."&order=".$incoming->id; ?>">Modtag ordre</a></td>
                                </tr><?php

                                $intransit += $incoming->ordercount;
                            }
                        }

                        if($intransit == 0) {
                            ?><tr><td colspan="6">Ingen varer i transit</td></tr><?php
                        }

                        ?>
                        </tbody>
                    </table>

                    <h3>Varer modtaget</h3>
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">Ordre nr</th>

                            <th scope="col">Oprettet</th>
                            <th scope="col">Modtaget</th>
                            <th scope="col">Linjer</th>
                            <th scope="col">Varer</th>
                            <th scope="col">-</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php

                        $received = 0;

                        foreach($incomingList as $incoming) {
                            if($incoming->received != null) {
                                ?><tr>
                                <td><?php echo $incoming->sono; ?></td>
                                <td><?php echo $incoming->created == null ? "Ikke modtaget" : $incoming->created->format("Y-m-d H:i"); ?></td>
                                <td><?php echo $incoming->received == null ? "Ikke modtaget" : $incoming->received->format("Y-m-d H:i"); ?></td>
                                <td><?php echo $incoming->linecount; ?></td>
                                <td><?php
                                    echo $incoming->ordercount;
                                    if($incoming->ordercount < $incoming->receivedcount) echo " (+".($incoming->receivedcount-$incoming->ordercount).")";
                                    else if($incoming->ordercount > $incoming->receivedcount) echo " (".($incoming->receivedcount-$incoming->ordercount).")";
                                    ?></td>
                                <td><a href="<?php echo $url."&order=".$incoming->id; ?>">Vis ordre</a></td>
                                </tr><?php

                                $received += $incoming->ordercount;
                            }
                        }

                        if($received == 0) {
                            ?><tr><td colspan="6">Ingen varer modtaget</td></tr><?php
                        }

                        ?>
                        </tbody>
                    </table>

                </div>
                <div class="col-md-8">
                    <h3>Status</h3>
                <?php echo self::varenrlist($vlager,$url); ?>
                </div>

            </div>
        </div>


        <!-- Bootstrap Modal -->
        <div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="inventoryModalLabel">Justering af lagerbeholdning</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="inventoryForm">
                            <div class="mb-3">
                                <label for="productNumber" class="form-label">Varenr</label>
                                <input type="text" class="form-control" id="productNumber" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="productName" class="form-label">Varenavn</label>
                                <input type="text" class="form-control" id="productName" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="currentStock" class="form-label">Nuværende Beholdning</label>
                                <input type="number" class="form-control" id="currentStock" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="newStock" class="form-label">Ny Beholdning</label>
                                <input type="number" class="form-control" id="newStock" oninput="updateDifference()">
                            </div>
                            <div class="mb-3">
                                <label for="stockDifference" class="form-label">Difference</label>
                                <input type="text" class="form-control" id="stockDifference" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="stockNote" class="form-label">Note</label>
                                <input type="text" class="form-control" id="stockNote">
                            </div>
                        </form>



                        <div class="alert alert-danger" role="alert" id="adjustError" style="display: none;">

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onClick="$('#inventoryModal').modal('hide');">Annuller</button>
                        <button type="button" class="btn btn-primary" onclick="saveUpdate()">Opdater</button>
                    </div>
                </div>
            </div>
        </div>

        <script>

            function openModal(productNumber, productName, currentStock) {
                $('#adjustError').hide();
                document.getElementById('productNumber').value = productNumber;
                document.getElementById('productName').value = productName;
                document.getElementById('currentStock').value = currentStock;
                document.getElementById('newStock').value = currentStock;
                document.getElementById('stockDifference').value = 0;

                // Show the modal
                $('#inventoryModal').modal();
            }

            function updateDifference() {
                const currentStock = parseInt(document.getElementById('currentStock').value);
                const newStock = parseInt(document.getElementById('newStock').value);
                const difference = newStock - currentStock;
                document.getElementById('stockDifference').value = difference;
            }

            function saveUpdate() {
                $('#adjustError').hide();
                const productNumber = document.getElementById('productNumber').value;
                const productName = document.getElementById('productName').value;
                const newStock = document.getElementById('newStock').value;

                const updateData = {
                    productNumber: productNumber,
                    productName: productName,
                    newStock: newStock,
                    existingStock: document.getElementById('currentStock').value,
                    action: 'updatestock',
                    note: document.getElementById('stockNote').value
                };

                console.log(JSON.stringify(updateData));
                $.post('', updateData, function(response) {
                    console.log(response);

                    // if has status and it is 1 reload page
                    if(response.status && response.status == 1) {
                        alert('Lagerbehodlning opdaterer, indlæser siden igen!');
                        location.reload();
                    }

                    // Show error message  in response.error is available and set, otherwise a default error
                    if(response.error) {
                        $('#adjustError').text(response.error);
                        $('#adjustError').show();
                    } else {
                        $('#adjustError').text('Der skete en fejl, prøv igen!');
                        $('#adjustError').show();
                    }

                },'json');

            }

        </script>


        <?php
        Template::templateBottom();


    }

    private static function varenrlist($vlager,$url)
    {

        $vlCounter = new VLagerCounter($vlager->id);
       ?><table class="table table-bordered table-hover">
        <thead>
        <tr>
            <th>Varenr</th>
            <th>Beskrivelse</th>
            <th>På vej ind</th>
            <th>På vej ud</th>
            <th>På lager</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php

        $primaryItems = $vlCounter->getTotalVarenrList();
        foreach ($primaryItems as $itemno) {
            if(!$vlCounter->isBOMItem($itemno)) {
                self::printVarenrLine($vlCounter,$itemno,$url);
            }
        }

        ?>
        </tbody>
        </table><?php
    }

    private static function printVarenrLine($vlCounter,$itemno,$url) {

        ?><tr>
            <td><?php echo $vlCounter->getRealItemNo($itemno); ?></td>
            <td><?php echo $vlCounter->getItemName($itemno); ?></td>

            <td><?php echo $vlCounter->getIncoming($itemno); ?></td>
            <td><?php echo $vlCounter->getOutgoing($itemno); ?></td>
            <td><?php echo $vlCounter->getAvailable($itemno); ?></td>
            <td>

                <button type="button" class="btn btn-primary btn-sm" onClick="openModal('<?php echo $vlCounter->getRealItemNo($itemno); ?>', '<?php echo $vlCounter->getItemName($itemno); ?>', <?php echo $vlCounter->getAvailable($itemno); ?>)">
                    juster
                </button>
                <button type="button" class="btn btn-primary btn-sm" onClick="document.location='<?php echo $url."&log=".$itemno; ?>';">
                    log
                </button>



            </td>
        </tr><?php

    }

}

