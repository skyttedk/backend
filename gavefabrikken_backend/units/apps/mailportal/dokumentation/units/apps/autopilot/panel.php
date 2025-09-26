<?php
/*
vis nuværende reservation
vis nuværende valgte
vis andel der har valgt i shoppen
Aktivere foreslået handling
Husk at sætte nej nej nej opdatering knappen på


*/
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopilot dashboard</title>
    <link href="<?php echo $assetPath; ?>/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/assets/jquery.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/popper.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>/assets/fontawesome.css">
    <style>
        body {
            padding: 20px;
        }
        #shopConpleteProcent{
            float: right;
            margin-right: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4">Model Data</h1>
    <button id="autopilot" class="btn btn-primary mb-3">Autopilot</button><label id="shopConpleteProcent"></label>
    <table class="table table-bordered">
        <thead class="thead-dark">
        <tr>
            <th>Present ID</th>
            <th>Autopilot</th>
            <th>Reserverede</th>
            <th>Antal valgte</th>
            <th>Model ID</th>
            <th>Model Name</th>
            <th>Model Present No</th>

        </tr>
        </thead>
        <tbody id="data-table">
        <!-- Data will be appended here by jQuery -->
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/autopilot/";
    $(document).ready(function() {
        $.ajax({
            url: APPR_AJAX_URL + 'getCurrentData',
            method: 'POST',
            dataType: 'json',
            success: function(data) {

                var rows = '';
                data.data.forEach(function(item) {
                    rows += '<tr>';
                    rows += '<td>' + item.present_id + '</td>';
                    rows += '<td id="modelID-'+item.present_model_id+'"></td>';
                    rows += '<td>' + item.reserved_quantity + '</td>';
                    rows += '<td>' + item.order_count + '</td>';
                    rows += '<td>' + item.present_model_id + '</td>';
                    rows += '<td>' + item.model_present_name + '</td>';
                    rows += '<td>' + item.model_present_no + '</td>';

                    rows += '</tr>';
                });
                $('#data-table').html(rows);
            },
            error: function(err) {
                console.error('Error fetching data', err);
            }
        });
        $('#autopilot').on('click', function() {
            autopilot();
        });
        shopCompleted();
    });

    function shopCompleted(){
        $.ajax({
            url: APPR_AJAX_URL + 'getShopCompleted',
            method: 'POST',
            dataType: 'json',
            success: function(data) {
                let countOrders = data.data[0].attributes.count;
                let countSU = data.data[1].attributes.count;
                let p = Math.round((countOrders / countSU) * 100,2);
             //   console.log(p +"-"+countSU+ "-"+countOrders)
                $("#shopConpleteProcent").html(p+" % har valgt")
            },
            error: function(err) {
                console.error('Error fetching data', err);
            }
        });
    }

    function autopilot(){
        $.ajax({
            url: APPR_AJAX_URL + 'autopilot',
            method: 'POST',
            dataType: 'json',
            success: function(data) {
                updateAutopilot(data)
            },
            error: function(err) {
                console.error('Error fetching data', err);
            }
        });
    }
    function updateAutopilot(data)
    {

        data.data.forEach(function(item) {
            console.log(item)
            let forecast = item.forecast == "N/A" ? 0: item.forecast;
            
            $("#modelID-"+item.modelID).html(forecast);

        });
        /*
        data.forEach(function(item) {
            console.log(item);
        }

         */
    }
</script>
</body>
</html>





