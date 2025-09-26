<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leverance fejlliste</title>
    <link href="<?php echo $assetPath ?>/assets/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $assetPath ?>/assets/bootstrap-select.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath ?>/assets/jquery-3.5.1.min.js"></script>
    <script src="<?php echo $assetPath ?>/assets/boostrap.bundle.min.js"></script>
    <script src="<?php echo $assetPath ?>/assets/bootstrap-select.min.js"></script>
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        .wrapper {
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
        }
        .footer {
            flex-shrink: 0;
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .sticky-top {
            background-color: #F5F5F5;
            padding-bottom: 10px;
            z-index: 1020;
        }

        .table {
            width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }

        .table td {
            padding: 8px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }

        @media screen and (max-width: 600px) {
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }

            .table tr {
                margin-bottom: 15px;
            }

            .table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }

            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                text-align: left;
                font-weight: bold;
            }
        }


        .table th, .table td {
            white-space: nowrap;
        }


        .table thead th {


            cursor: pointer;
            background-color: #fff;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
        }

        .table th.sorted {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .table th.sortedasc:after {
            content: ' ▲';
        }
        .table th.sorteddesc:after {
            content: ' ▼';
        }


        .bootstrap-select .btn-light { background: white !important; }

    </style>
</head>
<body>

<div style="position: fixed; top: 200px; bottom: 0px; left: 0px; width: 100%; ">
    <div class="scrollable-table" style="width: 100%; height: 100%; overflow: auto;">
        <div class="loading-overlay" style="">Vælg indstillinger og klik opdater for at søge..</div>
        <table class="table table-bordered" id="shipmentblocklisttable" style="font-size: 0.8em;"></table>

    </div>
</div>
<div style="position: fixed; top: 0px; left: 0px; padding: 15px; background: #F5F5F5; width: 100%; height: 200px;">
    <div class="row sticky-top">
        <div class="col-md-6">
            <h2>Leverancer - fejlliste</h2>
        </div>
        <div class="col-md-6 text-right" style="padding-top: 8px; padding-right: 8px;">
            <button class="btn btn-secondary" onclick="window.document.location.reload();">Nulstil søgning</button>
            <button class="btn btn-success" style="display: none;" onClick="downloadAsCSV()">Download som CSV</button>
        </div>

        <div class="col-12 mt-2 d-flex flex-wrap">

            <div class="p-1 flex-fill">
                Fritekst søgning:<br>
                <input type="text" class="form-control mb-2" placeholder="Fritekst søgning" id="text-search">
            </div>
            <div class="p-1 flex-fill">
                Koncept:<br>
                <select class="form-control selectpicker mb-2" multiple data-live-search="true" id="concept">
                    <?php

                    // Load language
                    $languageCode = \router::$systemUser->language;
                    $languageCodes = [];
                    if($languageCode > 0) {
                        $languageCodes[] = $languageCode;
                    }

                    if(in_array(\router::$systemUser->id,array(50))) {
                        $languageCodes = array_merge($languageCodes, array(1,4,5));
                        $languageCodes = array_unique($languageCodes);
                    }
                    
                    $shopSettingsList = \CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings where language_code in (".implode(",",$languageCodes).") ORDER BY language_code ASC, concept_code ASC");
                    $shoplist = [];
                    foreach($shopSettingsList as $settings) {
                        $shoplist[] = array("value" => $settings->shop_id,"text" => $settings->concept_code);
                    }
                    
                    foreach($shoplist as $shop) {
                        echo '<option value="'.$shop['value'].'">'.$shop['text'].'</option>';
                    } ?>
                </select>
            </div>
            <div class="p-1 flex-fill">
                <div style="padding-top: 8px; padding-left: 8px;">Skjulte fejlbeskeder<br>
                <label><input type="checkbox" id="showhidden"> Vis skjulte</label><br>
                <label style="display: none;"><input type="checkbox" id="showsolved"> Vis løste fejl</label>
                </div>
            </div>
            <div class="p-1 flex-fill" style="<?php echo in_array(\router::$systemUser->id,array(50)) ? "" : "display: none;"; ?>">Tekniske fejl<br>

                <label <?php if(\router::$systemUser->id != 50) { ?>style="display: none;"<?php } ?>><input type="checkbox" id="showtech"> Vis tekniske fejl</label><br>

            </div>

            <div class="p-1 flex-fill">
                <div style="padding-top: 8px; padding-left: 8px;">
                    Antal viste leverancer<br>
                    <span id="shipmentcounter"></span>
                </div>
            </div>

            <div class="p-1">
                &nbsp;<br>
                <button class="btn btn-primary">Opdater søgning</button>
            </div>
        </div>

    </div>
</div>


<script>

    function collectSearchCriteria() {
        return {
            'action': 'shipmentblocklistprovider',
            textSearch: $('#text-search').val(),
            concept: $('#concept').val(),
            showhidden: $('#showhidden').is(':checked') ? 1 : 0,
            showsolved: $('#showsolved').is(':checked') ? 1 : 0,
            showtech: $('#showtech').is(':checked') ? 1 : 0,
            sortField: sortField,
            sortDir: sortDir
           
        };
    }

    function updateTable() {
        var searchData = collectSearchCriteria();
        $('.loading-overlay').show().html('Henter data..');
        $.ajax({
            url: '<?php echo $servicePath; ?>shipmentblocklistprovider',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(searchData),
            success: function(response) {




                var data = JSON.parse(response);

                $('#shipmentblocklisttable').html(data.content);
                $('.loading-overlay').hide();

                $('#shipmentblocklisttable th').bind('click',function(e) {
                    updateSort(e)
                })

                // Find data-sortfield=sortField and add class sorted sort+sortDir
                $('#shipmentblocklisttable th').removeClass('sorted sortedasc sorteddesc');
                $('#shipmentblocklisttable th[data-sortfield="'+sortField+'"]').addClass('sorted sorted'+sortDir);

                $('#shipmentcounter').html(data.totalRows);

            },
            error: function() {
                $('.loading-overlay').hide();
                alert('Der opstod en fejl under hentning af data.');
            }
        });
    }

    var sortField = 'id';
    var sortDir = 'desc';
   

    function updateSort(e) {

        var sort = $(e.target).attr('data-sortfield');
        if(sortField == sort) {
            if(sortDir == 'asc') {
                sortDir = 'desc';
            } else {
                sortDir = 'asc';
            }
        } else {
            sortField = sort;
            sortDir = 'asc';
        }

        updateTable();
    }


    function downloadAsCSV() {
        var searchData = collectSearchCriteria();
        window.location.href = '<?php echo $servicePath; ?>shipmentblocklistcsv&'+$.param(searchData);
    }

    $(document).ready(function() {

        //updateTable();
        $('.btn-primary').click(updateTable);
        updateTable();


    });

</script>


<!-- Modal HTML -->
<!-- Modal HTML -->
<div class="modal fade" id="shipmentModal" tabindex="-1" role="dialog" aria-labelledby="shipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 90%;"> <!-- Brug modal-xl for ekstra stor modal -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shipmentModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="shipmentModalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer" id="shipmentModalFooter">
                <!-- Footer content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
    function openShipmentModal(id) {
        var shipmentUrl = 'index.php?rt=unit/cardshop/shipblocktool/modal/' + id;

        // Set the modal title
        $('#shipmentModalLabel').text('Detaljer, fejl og handlinger på forsendelse ' + id);

        // Reset the body and footer with loading messages
        $('#shipmentModalBody').html('<div class="text-center">Henter data..</div>');
        $('#shipmentModalFooter').html('');

        // Load the content from the URL
        $.get(shipmentUrl, function(data) {
            // Assuming the response includes both body and footer content
            $('#shipmentModalBody').html(data.body);
            $('#shipmentModalFooter').html(data.footer);
        });

        // Show the modal
        $('#shipmentModal').modal('show');
    }
</script>



</body>
</html>
