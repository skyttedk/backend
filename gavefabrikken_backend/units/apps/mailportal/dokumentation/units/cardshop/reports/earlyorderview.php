<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earlyorderliste</title>
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

<div style="position: fixed; top: 200px; bottom: 90px; left: 0px; width: 100%; ">
    <div class="scrollable-table" style="width: 100%; height: 100%; overflow: auto;">
        <div class="loading-overlay" style="">Vælg indstillinger og klik opdater for at søge..</div>
        <table class="table table-bordered" id="earlyorderlisttable" style="font-size: 0.8em;"></table>
        <div style="height: 200px;">&nbsp;</div>
    </div>
</div>
<div style="position: fixed; top: 0px; left: 0px; padding: 15px; background: #F5F5F5; width: 100%; height: 200px;">
    <div class="row sticky-top">
        <div class="col-md-6">
            <h2>Cardshop earlyordre</h2>
        </div>
        <div class="col-md-6 text-right" style="padding-top: 8px; padding-right: 8px;">
            <button class="btn btn-secondary" onclick="window.document.location.reload();">Nulstil søgning</button>
            <button class="btn btn-success" onClick="downloadAsCSV()">Download som CSV</button>
        </div>
        <div class="col-12 mt-3 d-flex flex-wrap">
            <div class="p-1 flex-fill">
                Fritekst søgning:<br>
                <input type="text" class="form-control mb-2" placeholder="Fritekst søgning" id="text-search">
            </div>
            <div class="p-1 flex-fill">
                Leverance ID:<br>
                <input type="text" class="form-control mb-2" placeholder="Ordre nr" id="leverance-id">
            </div>
            <div class="p-1 flex-fill">
                Koncept:<br>
                <select class="form-control selectpicker mb-2" multiple data-live-search="true" id="concept">
                    <?php foreach($shops as $shop) {
                        echo '<option value="'.$shop['value'].'">'.$shop['text'].'</option>';
                    } ?>
                </select>
            </div>
            <div class="p-1 flex-fill">
                Ordrenr:<br>
                <input type="text" class="form-control mb-2" placeholder="Ordre nr" id="order-number">
            </div>
            <div class="p-1 flex-fill">
                Varenr:<br>
                <input type="text" class="form-control mb-2" placeholder="Vare nr" id="itemno">
            </div>

            <div class="p-1 flex-fill">
                Leverance status:<br>
                <select class="form-control selectpicker mb-2" multiple data-live-search="true" id="shipment-status">
                    <?php
                    $shipmentStateList = \Shipment::getStateList();
                    foreach($shipmentStateList as $value => $orderState) {
                        echo '<option value="'.$value.'">'.$value.': '.$orderState.'</option>';
                    } ?>
                </select>
            </div>
            <div class="p-1 flex-fill" style="display: none;">
                Specielle søgninger<br>
                <select class="form-control selectpicker mb-2" data-live-search="true" id="special-search">
                    <option>Der er pt. ingen specialsøgninger</option>
                </select>
            </div>
            <div class="p-1">
                &nbsp;<br>
                <button class="btn btn-primary">Opdater søgning</button>
            </div>
        </div>
    </div>
</div>

<div style="position: fixed; text-align: center; padding: 10px; background: #F5F5F5; bottom: 0px; left: 0px; width: 100%; height: 90px;">
    <div id="pageLabel"></div>
    <div>
        <button class="btn btn-outline-secondary" id="pagePrev">Forrige</button>
        <button class="btn btn-outline-secondary" id="pageNext">Næste</button>
    </div>
</div>

<script>

    function collectSearchCriteria() {
        return {
            'action': 'earlyorderlistprovider',
            textSearch: $('#text-search').val(),
            orderNumber: $('#order-number').val(),
            leveranceId: $('#leverance-id').val(),
            shipmentStatus: $('#shipment-status').val(),
            concept: $('#concept').val(),
            specialSearch: $('#special-search').val(),
            itemno: $('#itemno').val(),
            sortField: sortField,
            sortDir: sortDir,
            page: page
        };
    }

    function updateTable() {
        var searchData = collectSearchCriteria();
        $('.loading-overlay').show().html('Henter data..');
        $.ajax({
            url: '<?php echo $servicePath; ?>earlyorderlistprovider',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(searchData),
            success: function(response) {

                var data = JSON.parse(response);
                $('#earlyorderlisttable').html(data.content);
                $('.loading-overlay').hide();

                $('#earlyorderlisttable th').bind('click',function(e) {
                    updateSort(e)
                })

                // Find data-sortfield=sortField and add class sorted sort+sortDir
                $('#earlyorderlisttable th').removeClass('sorted sortedasc sorteddesc');
                $('#earlyorderlisttable th[data-sortfield="'+sortField+'"]').addClass('sorted sorted'+sortDir);

                if(page == 1) {
                    $('#pagePrev').attr('disabled',true);
                } else {
                    $('#pagePrev').attr('disabled',false);
                }

                if(page == data.maxPage) {
                    $('#pageNext').attr('disabled',true);
                } else {
                    $('#pageNext').attr('disabled',false);
                }

                page = data.page;
                $('#pageLabel').html('Viser side '+page+' af '+data.maxPage+' - Totalt '+data.totalRows+' elementer');


            },
            error: function() {
                $('.loading-overlay').hide();
                alert('Der opstod en fejl under hentning af data.');
            }
        });
    }

    var sortField = 'id';
    var sortDir = 'desc';
    var page = 1;

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
        window.location.href = '<?php echo $servicePath; ?>earlyorderlistcsv&'+$.param(searchData);
    }

    $(document).ready(function() {

        //updateTable();
        $('.btn-primary').click(updateTable);

        $('#pagePrev').bind('click',function() {
            page--;
            updateTable();
        });

        $('#pageNext').bind('click',function() {
            page++;
            updateTable();
        });


    });

</script>

</body>
</html>
