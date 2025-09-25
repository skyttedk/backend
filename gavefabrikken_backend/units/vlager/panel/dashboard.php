<?php

namespace GFUnit\vlager\panel;
use GFBiz\units\UnitController;
use GFUnit\vlager\utils;
use GFUnit\vlager\utils\Template;
use GFUnit\vlager\utils\VLager;

class Dashboard
{

    private $lagerList;

    public function __construct()
    {

        $this->lagerList = VLager::getLagerList();


    }
    


    public function show()
    {


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
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <a class="navbar-brand" href="#">Cardshop virtuel lagerstyring - Admin panel</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="https://system.gavefabrikken.dk/vlager/" target="_blank">Lager frontend</a>
                    </li>
                </ul>
            </div>
        </nav>
        <br>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <?php $this->outputLagerList(); ?>
                </div>
                <div class="col-md-4">
                    <?php $this->outputTransfers(); ?>
                </div>
                <div class="col-md-4">
                    <?php $this->outputItemlist(); ?>
                </div>
            </div>
        </div>
        <?php
        Template::templateBottom();

    }

    private function outputLagerList()
    {

        // Lager lokationer
        ?><h3>Lager Lokationer</h3>
        <?php foreach($this->lagerList as $lager) {

            ?><div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo $lager->name; ?></h5>
                <p class="card-text">
                    <span class="badge badge-info">Kode: <?php echo $lager->code; ?></span>
                    <span class="badge badge-info">Lokation: <?php echo $lager->location; ?></span>
                    <?php if($lager->active == 1) { ?><span class="badge badge-success">Front: AKTIV</span><?php }
                    else { ?><span class="badge badge-danger">Front: LUKKET</span><?php } ?>
                </p>
                <button class="btn btn-primary" onClick="openOrderTransferModal()">Opret overflytning</button>
                <a class="btn btn-primary" href="/gavefabrikken_backend/index.php?rt=unit/vlager/panel/front/<?php echo $lager->id; ?>" target="_blank">Lager front</a>
                <a class="btn btn-primary" href="/gavefabrikken_backend/index.php?rt=unit/vlager/panel/liste/<?php echo $lager->id; ?>" target="_blank">Lager oversigt</a>
                <a class="btn btn-primary" href="/gavefabrikken_backend/index.php?rt=unit/vlager/shipment/report/<?php echo $lager->id; ?>" target="_blank">Økonomi rapport</a>
            </div>
            </div><?php

        }


        // Overflytningsordre
        ?><!-- Modal -->
        <div class="modal fade" id="transferOrderModal" tabindex="-1" role="dialog" aria-labelledby="transferOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="transferOrderModalLabel">Registrer Overflytningsordre</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="transferOrderForm">
                            <div class="form-group">
                                <label for="lager">Lager</label>
                                <select class="form-control" id="lager">
                                    <?php foreach($this->lagerList as $lager) echo "<option value='".$lager->id."'>".$lager->name."</option>"; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="soNr">SO nr</label>
                                <input type="text" class="form-control" id="soNr" placeholder="Indtast SO nr">
                            </div>
                            <div class="form-group" id="contentSection" style="display: none;">
                                <label for="content">Tilbagemelding</label>
                                <textarea class="form-control" id="content" style="height: 300px;"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuller</button>
                        <button type="button" class="btn btn-primary" id="checkButton">Tjek</button>
                        <button type="button" class="btn btn-success" id="saveButton" disabled>Gem</button>
                    </div>
                </div>
            </div>
        </div>
    <script>

        function openOrderTransferModal() {
            $('#transferOrderModal').modal('show');
        }

        $(document).ready(function() {

            // Tjek funktion
            $('#checkButton').click(function() {

                $('#contentSection').show();
                $('#saveButton').prop('disabled', true);

                $.post(BASEURL+'services/checkso',{sono: $('#soNr').val(),vlagerid: $('#lager').val()}, function(response) {

                    if(response.status == 1) {

                        $('#saveButton').prop('disabled', false);
                        $('#content').val(response.message);

                    } else {
                        if(response.message) {
                            $('#content').val(response.message);
                        } else {
                            $('#content').val('Der opstod et problem, kunne ikke hente SO nr information');
                        }
                    }

                },'json').fail(function() {
                    $('#content').val('Der opstod et problem, kunne ikke hente SO nr information');
                });

            });

            // Gem funktion
            $('#saveButton').click(function() {

                $.post(BASEURL+'services/importso',{sono: $('#soNr').val(),vlagerid: $('#lager').val()}, function(response) {

                    if(response.status == 1) {
                        $('#transferOrderModal').modal('hide');
                        alert('Overflytning oprettet');
                        location.reload();
                    } else {
                        if(response.message) {
                            $('#content').val(response.message);
                        } else {
                            $('#content').val('Der opstod et problem, kunne ikke hente SO nr information');
                        }
                    }
                },'json').fail(function() {
                    $('#content').val('Der opstod et problem, kunne ikke hente SO nr information');
                });

            });
        });

    </script>


        <?php
    }

    private function outputTransfers() {


        $incomingList = \VLagerIncoming::find_by_sql("SELECT vlager_incoming.*, sum(vlager_incoming_line.quantity_order) as ordercount, sum(vlager_incoming_line.quantity_received) as receivedcount, count(distinct vlager_incoming_line.id) as linecount FROM `vlager_incoming`, vlager_incoming_line where vlager_incoming.id = vlager_incoming_line.vlager_incoming_id group by vlager_incoming.id");


        ?><h3>Seneste Overflytninger</h3>


        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Ordre nr</th>

                    <th scope="col">Oprettet</th>
                    <th scope="col">Modtaget</th>
                    <th scope="col">Linjer</th>
                    <th scope="col">Varer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($incomingList as $incoming) {
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
                    </tr><?php
                } ?>
            </tbody>
        </table>
       <?php


    }

    private function outputItemlist() {
        ?> <h3>Varenr Liste</h3>
        <ul class="list-group">
            <li class="list-group-item">Varenr 001 - Status: På lager</li>
            <!-- Flere listepunkter kan tilføjes her -->
            LAV NÅR DER ER INDLÆST VARER FRA LAGER!
        </ul><?php
    }


}