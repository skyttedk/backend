<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company actionlogs</title>
    <link href="/gavefabrikken_backend/units/assets/libs/bootstrap.min.css" rel="stylesheet">
    <script src="/gavefabrikken_backend/units/assets/libs/jquery.min.js"></script>
    <script src="/gavefabrikken_backend/units/assets/libs/popper.min.js"></script>
    <script src="/gavefabrikken_backend/units/assets/libs/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/gavefabrikken_backend/units/assets/fontawesome.css">
    <script src="<?php echo $assetPath; ?>js/actionlogmanager.js"></script>

    <style>

        .actionlogheader { padding: 15px; background-color: #B8B8B8; }
        .actionlogsubheader { padding: 15px; background-color: #f1f1f1; padding-top: 10px; padding-bottom: 10px; }
        .actionlogform { padding: 15px;}

        .actionlogitem {  border-bottom: 2px solid #A0A0A0; border-left: 6px solid #A0A0A0; }
        .actionlogitem-mine { border-left: 6px solid #6895D2; }
        .actionlogitem-priority { border-left: 6px solid #D04848; }
        .actionlogitem-date { border-left: 6px solid #FDE767; }
        .actionlogitem-solved { border-left: 6px solid #BFEA7C; }

        .actionlogitemhead { padding: 9px; padding-right: 15px; padding-top: 5px; padding-bottom: 5px; background: #FAFAFA;}
        .actionlogitembody {padding: 9px; padding-right: 15px;padding-top: 8px; padding-bottom: 8px; }
        .actionlogitemfooter { padding: 9px; padding-top: 10px; padding-right: 15px; padding-bottom: 10px; background: #FCFCFC; }

        .label {
            display: inline-block;
            padding: 0.2em 0.6em 0.3em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25em;
        }

        .label-default {
            background-color: #777;
        }

        .label-primary {
            background-color: #337ab7;
        }

        .label-secondary {
            background-color: #6c757d;
        }

        .label-info {
            background-color: #5bc0de;
        }

        .label-success {
            background-color: #5cb85c;
        }

        .label-warning {
            background-color: #f0ad4e;
        }

        .label-danger {
            background-color: #d9534f;
        }


    </style>
</head>
<body>
<div class="actionlogheader">
    <div style="float: right; margin-right: 10px;"><form action="<?php echo $servicePath.$endpoint; ?>" method="post">
        Søg i logs: <input type="text" name="query" id="actionlogquery" value="<?php echo $_POST["query"] ?? ""; ?>"> <button type="submit">søg</button>
        </form>
    </div>
    Viser logs for <b><?php echo $type.": ".$typename; ?></b>

</div>
<div class="actionlogbody" style="margin: 20px; margin-top: 0px;">

    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Dato</th>
            <th>Type</th>
            <th>Beskrivelse</th>
            <th>Oprindelse</th>
            <th>Referencer</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody id="actionloglist">
        <?php
        // Loop through all logs
        foreach($loglist as $log) {


            echo "<tr>";

            echo "<td>".$log->id."</td>";
            echo "<td>".$log->created->format("d/m/Y H:i")."</td>";
            echo "<td>".$log->getTypeName()."</td>";
            echo "<td>".$log->headline."</td>";

            // Oprindelse
            echo "<td>";

            if (!empty($log->author_systemuser_name)) {
                echo "<span class='label label-primary'>Systembruger: ".$log->author_systemuser_name."</span> ";
            }
            if (!empty($log->author_shopuser_username)) {
                echo "<span class='label label-secondary'>Gavemodtager: ".$log->author_shopuser_username."</span> ";
            }
            if($showTech) echo "<span class='label label-default'>".$log->ip."</span> ";
            echo "</td>";

            // Referencer
            echo "<td>";
            if (!empty($log->shop_name)) {
                echo "<span class='label label-info'>Shop: ".$log->shop_name."</span> ";
            }
            if (!empty($log->company_name)) {
                echo "<span class='label label-success'>Virksomhed: ".$log->company_name."</span> ";
            }
            if (!empty($log->company_order_orderno)) {
                echo "<span class='label label-warning'>BS nr: ".$log->company_order_orderno."</span> ";
            }
            if (!empty($log->shop_user_username)) {
                echo "<span class='label label-danger'>Brugernavn: ".$log->shop_user_username."</span> ";
            }
            if (!empty($log->order_orderno)) {
                echo "<span class='label label-primary'>Valg ordrenr: ".$log->order_orderno."</span> ";
            }
            echo "</td>";

            // Handlinger
            echo "<td style='text-align: right;'>";
            if (!empty($log->details)) {
                echo "<button class='btn btn-info btn-sm' onclick='toggleDetails(".$log->id.")'>Detaljer</button> ";
            }
            if ($showTech && (!empty($log->debugdata) || !empty($log->shipment_id) || !empty($log->system_log_id))) {
                echo "<button class='btn btn-warning btn-sm' onclick='toggleTechInfo(".$log->id.")'>Teknisk Info</button>";
            }
            echo "</td>";

            echo "</tr>";

            // Skjulte rækker til detaljer og teknisk info
            if (!empty($log->details)) {

                // Check if valid json
                if (json_decode($log->details)) {
                    $details = json_decode($log->details);
                    $details = json_encode($details, JSON_PRETTY_PRINT);
                    $log->details = $details;
                }

                echo "<tr id='details-".$log->id."' class='details-row' style='display:none;'>";
                echo "<td colspan='7'><pre>".$log->details."</pre></td>";
                echo "</tr>";
            } else {
                echo "<tr id='details-".$log->id."' class='details-row' style='display:none;'>";
                echo "<td colspan='7'>Ingen detaljer</td>";
                echo "</tr>";
            }
            if (!empty($log->debugdata) || !empty($log->shipment_id) || !empty($log->system_log_id)) {
                echo "<tr id='techinfo-".$log->id."' class='techinfo-row' style='display:none;'>";
                echo "<td colspan='7'>";
                echo !empty($log->debugdata) ? "Debug Data: ".$log->debugdata."<br>" : "";
                echo !empty($log->shipment_id) ? "Shipment ID: ".$log->shipment_id."<br>" : "";
                echo !empty($log->system_log_id) ? "System Log ID: ".$log->system_log_id."<br>" : "";
                echo "</td>";
                echo "</tr>";
            } else {
                echo "<tr id='techinfo-".$log->id."' class='techinfo-row' style='display:none;'>";
                echo "<td colspan='7'>Ingen teknisk info</td>";
                echo "</tr>";
            }
        }
        ?>
        </tbody>
    </table>

    <script>
        function toggleDetails(id) {
            var detailsRow = document.getElementById('details-' + id);
            if (detailsRow.style.display === 'none') {
                detailsRow.style.display = '';
            } else {
                detailsRow.style.display = 'none';
            }
        }

        function toggleTechInfo(id) {
            var techInfoRow = document.getElementById('techinfo-' + id);
            if (techInfoRow.style.display === 'none') {
                techInfoRow.style.display = '';
            } else {
                techInfoRow.style.display = 'none';
            }
        }
    </script>

    <style>
        .techinfo-row {
            background-color: lightyellow; /* Ensuring hidden rows don't affect the striping */
        }
        .techinfo-row td{
            background-color: lightyellow; /* Ensuring hidden rows don't affect the striping */
        }

        .details-row {
            background-color: lightblue; /* Ensuring hidden rows don't affect the striping */
        }
        .details-row td{
            background-color: lightblue; /* Ensuring hidden rows don't affect the striping */
        }
    </style>



</div>

<script>



</script>

</body>
</html>
