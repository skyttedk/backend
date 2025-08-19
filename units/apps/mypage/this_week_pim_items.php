<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget dashboard</title>
    <link href="<?php echo $assetPath; ?>/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/assets/jquery.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/popper.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/bootstrap.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/helpers.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>/assets/fontawesome.css">
    <style>
        body {
            font-size: 12px;
        }
        .highlight {
            background-color: #f0ad4e;
        }
        .sticky-container {
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 1000;
            background-color: white;
            padding: 10px 0;
        }
        .scroll-btn, .download-btn {
            margin: 5px;
        }
        .wrap-text {
            word-wrap: break-word;
            white-space: normal;
        }
        table {
            width: auto;
            border-collapse: collapse;
            table-layout: fixed;
        }
        td {
            max-width: 100px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            border: 1px solid #000;
            padding: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="sticky-container">
        <input type="text" id="searchInput" class="form-control search-bar" placeholder="Søg i tabellen...">
        <button class="btn btn-primary scroll-btn" id="scrollToCurrentWeek">Scroll til denne uge</button>
        <button class="btn btn-secondary scroll-btn" id="scrollToLastWeek">Scroll til sidste uge</button>
        <button class="btn btn-success download-btn" id="downloadCsv">Download CSV</button>
    </div>
    <div id="content">Henter Data</div>
</div>

<script>
    var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/mypage/";
    var globalData = {}; // Global variable to store data

    $(document).ready(function() {
        run();
        $('#searchInput').on('input', function() {
            filterTable($(this).val());
        });

        $('#scrollToCurrentWeek').on('click', function() {
            $('html, body').animate({
                scrollTop: $("#currentWeek").offset().top
            }, 1000);
        });

        $('#scrollToLastWeek').on('click', function() {
            $('html, body').animate({
                scrollTop: $("#lastWeek").offset().top
            }, 1000);
        });

        $('#downloadCsv').on('click', function() {
            downloadCsv();
        });
    });

    async function run() {
        try {
            let data = await loadData();
            globalData = {
                thisWeek: data.data.thisWeek,
                lastWeek: data.data.lastWeek
            };
            let currentWeek = getCurrentWeekNumber();
            let lastWeek = currentWeek - 1;

            let htmlThisWeek = buildGUI(globalData.thisWeek, "Denne uges oprettede gaver", currentWeek, "currentWeek");
            let htmlLastWeek = buildGUI(globalData.lastWeek, "Sidste uges oprettede gaver", lastWeek, "lastWeek");
            $("#content").html(htmlThisWeek + htmlLastWeek);
        } catch (error) {
            console.error("Error loading stats:", error);
        }
    }

    function buildGUI(data, title, uge, anchorId) {
        let initID = data[0]?.attributes.id || '';
        let cssGroupClass = "highlight";
        let tbody = "";
        $.each(data, function(index, item) {
            if (initID != item.attributes.id) {
                cssGroupClass = cssGroupClass == "highlight" ? "" : "highlight";
            }
            initID = item.attributes.id;
            let showData = formatDateTime(item.attributes.created_datetime.date);
            let saleVisible = item.attributes.show_to_saleperson == 1 ? "Synlig":"nej";
            tbody += `<tr class="${cssGroupClass}" data-id="${item.attributes.id}">
                <td>${uge}</td>
                <td>${initID}</td>
                <td class="wrap-text">${item.attributes.model_present_no}</td>
                <td class="wrap-text">${item.attributes.nav_name}</td>
                <td class="wrap-text">${item.attributes.model_name}</td>
                <td>${item.attributes.price_group}</td>
                <td>${item.attributes.prisents_nav_price}</td>
                <td>${item.attributes.indicative_price}</td>
                <td>${saleVisible}</td>
                <td>${showData}</td>
            </tr>`;
        });

        return `<div class="table-responsive" id="${anchorId}">
                    <h3>${title}</h3>
                    <hr>
                    <table class="table table-bordered table-hover" id="dataTable-${uge}">
                        <thead class="table-dark">
                            <tr>
                                <th>Ugenr.</th>
                                <th>GaveID</th>
                                <th>Varenr.</th>
                                <th>NAV navn</th>
                                <th>Gave navn</th>
                                <th>Budget</th>
                                <th>Kostpris</th>
                                <th>Vejl.</th>
                                <th>Sælger</th>
                                <th>Dato</th>
                            </tr>
                        </thead>
                        <tbody>${tbody}</tbody>
                    </table>
                </div>`;
    }

    function loadData() {
        return $.ajax({
            url: APPR_AJAX_URL + "fetchNewlyCreatedGifts",
            type: 'POST',
            dataType: 'json'
        });
    }

    function filterTable(query) {
        query = query.toLowerCase();
        $('table[id^="dataTable-"] tbody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            $(this).toggle(rowText.includes(query));
        });
    }

    function downloadCsv() {
        let csvContent = "\uFEFF"; // BOM (Byte Order Mark) for UTF-8

        // Add headers
        csvContent += "Ugenr.;GaveID;Varenr.;NAV navn;Gave navn;Budget;Kostpris;Vejl.;Sælger;Dato\n";

        // Get visible data from both tables
        $('table[id^="dataTable-"] tbody tr:visible').each(function() {
            let rowId = $(this).data('id');
            let weekNumber = $(this).find('td:first').text();
            let item = globalData.thisWeek.find(i => i.attributes.id === rowId) ||
                globalData.lastWeek.find(i => i.attributes.id === rowId);

            if (item) {
                let row = [
                    weekNumber,
                    item.attributes.id,
                    item.attributes.model_present_no,
                    item.attributes.nav_name,
                    item.attributes.model_name,
                    item.attributes.price_group,
                    item.attributes.prisents_nav_price,
                    item.attributes.indicative_price,
                    item.attributes.show_to_saleperson == 1 ? "Synlig" : "nej",
                    formatDateTime(item.attributes.created_datetime.date)
                ];
                csvContent += row.map(cell =>
                    cell.toString()
                        .replace(/;/g, ',')
                        .replace(/#/g, '')
                        .replace(/\n/g, ' ') // Replace newlines with spaces
                        .replace(/\r/g, '') // Remove carriage returns
                ).join(';') + "\n";
            }
        });

        // Remove all "#" from the csvContent
        csvContent = csvContent.replace(/#/g, '');

        // Create download link and trigger download
        let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement("a");
        if (link.download !== undefined) {
            let url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "Nyoprettede_gaver_pim.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }


    }

    function getCurrentWeekNumber() {
        const now = new Date();
        const onejan = new Date(now.getFullYear(), 0, 1);
        return Math.ceil((((now - onejan) / 86400000) + onejan.getDay() + 1) / 7);
    }

    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleString('da-DK', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
</script>
</body>
</html>