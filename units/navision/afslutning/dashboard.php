<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardshop afslutning</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #34495e;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: var(--primary);
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 2000px;
            margin: 0 auto;
        }

        h1, h2, h3 {
            color: var(--dark);
            margin-bottom: 12px;
        }

        h1 {
            font-size: 20px;
            font-weight: 600;
            text-align: left;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .country-flags {
            display: flex;
            gap: 10px;
        }

        .country-flag {
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 14px;
            background-color: var(--light);
            padding: 5px 10px;
            border-radius: 4px;
        }

        .country-flag-selected {
            background-color: var(--secondary);
            color: white;
        }

        .country-flag span {
            margin-left: 5px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .distribution-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 12px;
        }

        .card h3 {
            font-size: 15px;
            margin-bottom: 8px;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card h3 .icon {
            color: var(--secondary);
        }

        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #f0f0f0;
        }

        .data-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .data-row .label {
            font-size: 13px;
            color: #666;
        }

        .data-row .value {
            font-weight: 600;
            font-size: 13px;
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
            font-size: 13px;
        }

        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background-color: var(--secondary);
            color: white;
            font-weight: 500;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-open {
            background-color: var(--success);
            color: white;
        }

        .status-closed {
            background-color: var(--danger);
            color: white;
        }

        .status-reserved {
            background-color: #622d80;
            color: white;
        }

        .pie-chart {
            width: 100%;
            height: 150px;
            margin-top: 10px;
        }

        .distribution-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .distribution-item .bar {
            flex-grow: 1;
            margin: 0 10px;
            height: 6px;
            background-color: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
            position: relative;
        }

        .distribution-item .bar-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background-color: var(--secondary);
        }

        .distribution-item .label {
            min-width: 180px;
        }

        .distribution-item .value {
            min-width: 70px;
            text-align: right;
            font-weight: 600;
        }

        .bg-pending {
            background-color: var(--warning);
        }

        .bg-sync {
            background-color: var(--success);
        }

        .bg-credited {
            background-color: var(--secondary);
        }

        .bg-finished {
            background-color: var(--dark);
        }

        .bg-other {
            background-color: var(--primary);
        }

        .text-danger {
            color: var(--danger);
        }

        .section-header {
            font-size: 16px;
            font-weight: 600;
            margin: 18px 0 12px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ddd;
        }

        .tools-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .tool-link {
            display: flex;
            align-items: center;
            padding: 10px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .tool-link:hover {
            background-color: var(--light);
        }

        .tool-link .icon {
            margin-right: 8px;
            color: var(--secondary);
            font-size: 16px;
        }

        .error-list {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3px;
        }

        .error-category {
            font-weight: 600;
            color: var(--primary);
            min-width: 100px;
        }

        .error-items {
            flex-grow: 1;
        }

        .error-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }

        .status-label {
            display: inline-block;
            align-items: center;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            color: white;
            margin: 2px;
            white-space: nowrap;
            width: 100%;
        }

        .status-godkendelse {
            background-color: #ffa726; /* Orange - proces under godkendelse */
        }

        .status-aktive {
            background-color: #4caf50; /* Grøn - aktive ordrer */
        }

        .status-annulleret {
            background-color: #f44336; /* Rød - annullerede ordrer */
        }

        .status-afsluttes {
            background-color: #9c27b0; /* Lilla - ordrer der skal afsluttes */
        }

        .status-afsluttet {
            background-color: #2196f3; /* Blå - afsluttede ordrer */
        }

        .status-leveret {
            background-color: #009688; /* Turkis - leverede ordrer */
        }

        .status-andre {
            background-color: #607d8b; /* Gråblå - andre ordrer */
        }

        .status-label input[type="checkbox"] {
            margin-right: 4px;
        }

        .action-button {
            background-color: #2196f3; /* Blå - samme som afsluttet */
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-button:hover {
            background-color: #1976d2; /* Mørkere blå ved hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .action-button:active {
            background-color: #0d47a1; /* Endnu mørkere ved klik */
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

    </style>
</head>
<body>
<div class="container">
    <h1>
        <span>Cardshop afslutning</span>

    </h1>

    <p>
        Du kan herunder se alle cardshops og deadlines. Sæt kryds i dem der skal sendes til afslutning. Pas på med privatleveringer da de kan køre specielt.
    </p>
    <p>
        Ift. Sverige så skal deres privatleveringer sættes til afslutning på et tidspunkt i sæsonen og så vil de gå over i status leveret, det er indført for at holde styr på hvornår vi har sendt alle gavevalg til navision efter afslutning, for at navision også afslutter ordren korrekt.
    </p>

    <?php
    $languages = array(1 => "Danmark", 4 => "Norge", 5 => "Sverige");
    $expireDates = $helper->getExpireDateList();
    ?>

    <form id="csafslutform" method="post" action="index.php?rt=unit/navision/afslutning/runafslut">
    <div class="table-container">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Butik</th>
                <?php
                foreach($expireDates as $expireDate) {
                    echo "<th>" . $expireDate . "</th>";
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            // Output lande og deres shops
            foreach ($languages as $languageCode => $languageName) {

                // Output land som hovedkategori
                echo '<tr class="table-primary">';
                echo '<td style="background: var(--secondary); color: white;"><strong>' . htmlspecialchars($languageName) . '</strong></td>';

                // Tomme celler for hver dato i landerækken

                foreach($expireDates as $expireDate) {
                    echo "<td style=\"background: var(--secondary); color: white;\">" . $expireDate . "</td>";
                }

                echo '</tr>';

                // Hent shops for det specifikke sprog
                $shops = $helper->getCardshops($languageCode);

                // Output hver shop i dette land
                foreach ($shops as $shop) {
                    echo '<tr>';
                    echo '<td class="ps-4">' . htmlspecialchars($shop->concept_code) . '</td>';

                    // For hver dato, vis ordercount med orderstate 5
                    foreach ($expireDates as $expireDate) {

                        echo '<td>';

                        $inProcessCount = $helper->getOrderCount($shop->shop_id, $expireDate, [0,1,2,3]);
                        $activeCount = $helper->getOrderCount($shop->shop_id, $expireDate, [4,5]);
                        $cancelledCount = $helper->getOrderCount($shop->shop_id, $expireDate, [7,8]);
                        $toCompleteCount = $helper->getOrderCount($shop->shop_id, $expireDate, [9]);
                        $completeCount = $helper->getOrderCount($shop->shop_id, $expireDate, [10]);
                        $deliveredCount = $helper->getOrderCount($shop->shop_id, $expireDate, [12]);
                        $otherCount = $helper->getOrderCount($shop->shop_id, $expireDate, [20]);

                        $reservationOrders = $helper->getReservationOrders($shop->shop_id,$expireDate);
                        $reservationQuantity = $helper->getReservationQuantity($shop->shop_id,$expireDate);

                        // Vis kun labels for status med antal > 0
                        if ($inProcessCount > 0) {
                            echo '<span class="status-label status-godkendelse">Godkendelse ' . $inProcessCount . '</span>';
                        }

                        if ($activeCount > 0) {
                            $checkbox = '<input type="checkbox" name="active_orders[]" value="'.$shop->shop_id.'_'.$expireDate.'">';
                            if(strstr($shop->concept_code, "LUKS")) $checkbox = "";
                            echo '<span class="status-label status-aktive"> '.$checkbox.' Aktive ' . $activeCount . '</span>';
                        }

                        if ($cancelledCount > 0) {
                            echo '<span class="status-label status-annulleret">Annulleret ' . $cancelledCount . '</span>';
                        }

                        if ($toCompleteCount > 0) {
                            echo '<span class="status-label status-afsluttes">Afsluttes ' . $toCompleteCount . '</span>';
                        }

                        if ($completeCount > 0) {
                            echo '<span class="status-label status-afsluttet">Afsluttet ' . $completeCount . '</span>';
                        }

                        if ($deliveredCount > 0) {
                            echo '<span class="status-label status-leveret">Leveret ' . $deliveredCount . '</span>';
                        }

                        if ($otherCount > 0) {
                            echo '<span class="status-label status-andre">Andre ' . $otherCount . '</span>';
                        }

                        if($reservationOrders > 0 && ($completeCount+$deliveredCount > 0)) {


                            echo '<span class="status-label status-reserved" style="padding-top: 8px; padding-bottom: 8px;">Reserverede ' . $reservationOrders . " / ". $reservationQuantity . ' - <a style="color: white;" target="_blank" href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/countercs&shopid='.$shop->shop_id.'&deadline='.$expireDate.'">KØR</a></span>';
                        }

                        echo '</td>';
                    }

                    echo '</tr>';
                }
            }
            ?>
            </tbody>
        </table>

        <div style="text-align: right; padding-top: 25px; padding-bottom: 25px;">

            <button type="button" class="action-button" onclick="runCSFinish()">Send valgte ordre til afslutning</button>
        </div>

    </div>

        <input type="hidden" name="runafslut" value="1">
        <input type="hidden" name="runreservation" value="1">

    </form>

    <script>

        function runCSReservation() {
            if(!confirm('Er du sikker på at du vil afslutte reservationer?')) {
                return;
            }
            document.querySelector('input[name="runafslut"]').value = "0";
            document.querySelector('input[name="runreservation"]').value = "1";
            document.getElementById('csafslutform').submit();

        }
        
        function runCSFinish() {
            if(!confirm('Er du sikker på at du vil køre afslutning?')) {
                return;
            }

            document.getElementById('csafslutform').submit();

        }

    </script>



</div>

<script src="<?php echo $assetPath; ?>/assets/statusscripts.js"></script>
</body>
</html>