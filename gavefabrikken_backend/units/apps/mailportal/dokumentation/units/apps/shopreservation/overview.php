<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender med Shops</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f0f0f0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 20px;
        }

        #calendar {
            width: 95%;
            max-width: 1200px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        #calendar header {
            background: #007bff;
            color: #fff;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #calendar header .d-flex {
            flex-grow: 1;
            justify-content: center;
        }

        #calendar header .fixed-width {
            width: 300px; /* Set a fixed width for consistent layout */
        }

        #calendar header .d-flex > .btn-link {
            margin: 0 10px;
        }

        #calendar header .btn-outline-light {
            margin-left: 10px;
        }

        #calendar table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        #calendar th, #calendar td {
            padding: 0px;
            text-align: center;
            border: 1px solid #ddd;
            vertical-align: top;
            height: 30px; /* Ensuring fixed height for stability */
        }

        #calendar th {
            background: #f9f9f9;
            color: #333;
        }

        #calendar td:hover {
            background: #f1f1f1;
        }

        #calendar td div {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 50%;
        }

        #calendar td label {
            font-size: 1.2em;
            font-weight: bold;
        }

        #calendar td small {
            font-size: 0.9em;
            color: #555;
            display: flex;
            align-items: center;
        }

        .text-red {
            color: red;
        }
        .text-yellow {
            color: #ffdd57;
        }
        .text-green {
            color: green;
        }

        .shop-list {
            list-style-type: none;
            padding: 0;
            margin-top: 10px;
            max-height: calc(100vh - 250px); /* Allows scrolling */
            overflow-y: auto;
        }

        .shop-list li {
            display: flex;
            flex-direction: column;
            margin-bottom: 10px;
        }

        .shop-list a {
            margin-bottom: 5px;
            text-decoration: none;
            color: #007bff;
        }

        .shop-list a:hover {
            text-decoration: underline;
        }

        .shop-list small {
            color: #555;
        }

        .search-bar {
            position: -webkit-sticky; /* For Safari */
            position: sticky;
            top: 0;
            z-index: 1;
            background-color: #fff;
            padding: 10px 0;
        }

        .modal-body {
            padding: 15px;
            max-height: calc(100vh - 20px); /* Adjusting for scroll */
            overflow-y: auto;
        }
        #monthYear{
            width: 140px !important;
        }
        #nextMonth, #prevMonth{
            font-size: 30px;
        }
        #languageSelect {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1000;
        }
    </style>
</head>
<body>
<div id="calendar" class="card">
    <header class="card-header d-flex justify-content-between align-items-center">
        <select id="languageSelect" class="form-select form-select-sm" style="width: auto;">
            <option value="1">Dansk/Svensk</option>
            <option value="4">Norsk</option>
        </select>
        <div class="d-flex align-items-center justify-content-center w-100">
            <button id="prevMonth" class="btn btn-link text-white">«</button>
            <div id="monthYear" class="fw-bold mx-3"></div>
            <button id="nextMonth" class="btn btn-link text-white">»</button>
        </div>
        <button id="downloadCSV" class="btn btn-outline-light">Download CSV</button>
    </header>
    <table class="table table-bordered mb-0">
        <thead>
        <tr>
            <th>Man</th>
            <th>Tir</th>
            <th>Ons</th>
            <th>Tor</th>
            <th>Fre</th>
            <th>Lør</th>
            <th>Søn</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Modal for Showing Shop List -->
<div class="modal fade" id="shopListModal" tabindex="-1" aria-labelledby="shopListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shopListModalLabel">Shops</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="search-bar">
                    <input type="text" id="searchBar" class="form-control" placeholder="Søg shops...">
                </div>
                <ul id="shopList" class="shop-list"></ul>
            </div>
        </div>
    </div>
</div>

<script>
    var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/shopreservation/";
    var date = new Date();
    var currentMonth = date.getMonth();
    var currentYear = date.getFullYear();
    var customData = { };
    var allShopData = []; // This will store data for CSV download

    var months = [
        "Januar", "Februar", "Marts", "April", "Maj", "Juni",
        "Juli", "August", "September", "Oktober", "November", "December"
    ];

    function getColorClass(count) {
        if (count > 46) {
            return 'text-red';
        } else if (count > 30) {
            return 'text-yellow';
        } else {
            return 'text-green';
        }
    }

     function loadData() {
        return new Promise((resolve, reject) => {
            let postData = {};
            $.post(APPR_AJAX_URL + "getResevations&lang="+currentLang , postData, function(res) {
                if (res) {
                    resolve(res.data); // Returner data korrekt
                } else {
                    reject("No data received");
                }
            }, "json").fail(function(jqXHR, textStatus, errorThrown) {
                reject(errorThrown);
            });
        });
    }

    async  function  renderCalendar(month, year) {

        var firstDay = new Date(year, month).getDay();
        var daysInMonth = new Date(year, month + 1, 0).getDate();
        customData = await loadData()
        $('#monthYear').text(`${months[month]} ${year}`);

        var tbody = $('#calendar tbody');
        tbody.empty();
        var date = 1;

        // Adjust for ISO-8601 (start week on Monday)
        var firstDayAdjusted = (firstDay === 0) ? 6 : firstDay - 1;

        for (var i = 0; i < 6; i++) {
            var row = $('<tr></tr>');

            for (var j = 0; j < 7; j++) {
                if (i === 0 && j < firstDayAdjusted) {
                    row.append('<td></td>');
                } else if (date > daysInMonth) {
                    break;
                } else {
                    var formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                    var dayData = customData[formattedDate] || { openCount: 0, closeCount: 0, pickupCount: 0 };

                    // Only show if at least one count is greater than 0
                    if (dayData.openCount > 0 || dayData.closeCount > 0 || dayData.pickupCount > 0) {
                        var openColorClass = getColorClass(dayData.openCount);
                        var closeColorClass = getColorClass(dayData.closeCount);
                        var pickupColorClass = getColorClass(dayData.pickupCount);

                        var htmlCellContent = `
                        <div>
                            <div class="${openColorClass}" style="margin-bottom:5px; font-size:12px;">Åben: ${dayData.openCount}</div>
                            <div class="${closeColorClass}" style="margin-bottom:5px;font-size:12px;">Luk: ${dayData.closeCount}</div>
                            <div class="${pickupColorClass}" style="font-size:12px;">Hent: ${dayData.pickupCount}</div>
                        </div>`;

                        var cellContent = `
                        <div>
                            <label>${date}</label>
                            ${htmlCellContent}
                        </div>`;
                        var cell = $(`<td>${cellContent}</td>`);
                        cell.data('date', formattedDate);
                        row.append(cell);
                    } else {
                        row.append('<td></td>');
                    }
                    date++;
                }
            }

            tbody.append(row);
        }

        $('#prevMonth').off('click').on('click', function () {
            currentMonth = (currentMonth - 1 + 12) % 12;
            if (currentMonth === 11) {
                currentYear--;
            }
            renderCalendar(currentMonth, currentYear);
        });

        $('#nextMonth').off('click').on('click', function () {
            currentMonth = (currentMonth + 1) % 12;
            if (currentMonth === 0) {
                currentYear++;
            }
            renderCalendar(currentMonth, currentYear);
        });

        // Cell click event to fetch and show shop data
        $('#calendar td').off('click').on('click', function () {
            var selectedDate = $(this).data('date');
            fetchShopData(selectedDate);
        });
    }

    function fetchShopData(date) {
        // Simulating an AJAX call
        if(!date){
            return;
        }
        $.ajax({
            url: APPR_AJAX_URL + 'getShopOnDate&lang='+currentLang,
            type: 'POST',
            data: { date: date },
            dataType: 'json', // Specify the data type you expect
            success: function(response) {
                displayShopData(response.data);
            },
            error: function() {
                // Handle error
                alert('Fejl ved hentning af shops.');
            }
        });
    }
    function convertDateToDanishFormat(dateTimeString) {
        var dateTimeRegex = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(\.\d+)?$/;
        var dateOnlyRegex = /^\d{4}-\d{2}-\d{2}$/;

        let datePart;

        if (dateTimeRegex.test(dateTimeString)) {
            // Extract the date part from the datetime string
            datePart = dateTimeString.split(' ')[0]; // "2024-06-07"
        } else if (dateOnlyRegex.test(dateTimeString)) {
            // Handle date-only format
            datePart = dateTimeString; // Already in "2024-06-07" format
        } else {
            throw new Error('Invalid date-time format');
        }

        // Split the date into year, month, day
        var [year, month, day] = datePart.split('-');

        // Return the date in DD-MM-YYYY format
        return `${day}-${month}-${year}`;
    }
    function displayShopData(shops) {
        var shopList = $('#shopList');
        shopList.empty();

        if (shops.length === 0) {
            shopList.append('<li>Ingen shops tilgængelige.</li>');
        } else {
            shops.forEach(function(shop) {

                shop = shop.attributes;
                let start_date = shop?.start_date?.date ? convertDateToDanishFormat(shop.start_date.date) : "";
                let end_date = shop?.end_date?.date ? convertDateToDanishFormat(shop.end_date.date) : "";
                let delivery_date = shop?.delivery_date ? convertDateToDanishFormat(shop.delivery_date) : "";

                var listItem = `
                <li>
                    <a href="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=mainaa&editShopID=${shop.id}" target="_blank">${shop.name}</a>
                    <small>Start: ${start_date}</small>
                    <small>Slut:  ${end_date}</small>
                    <small>Hent: ${delivery_date}</small>
                </li>`;
                shopList.append(listItem);
            });
        }
        $('#shopListModal').modal('show');
    }

    function setupEventListeners() {
        // Search filter functionality
        $('#searchBar').on('input', function() {
            var filter = $(this).val().toLowerCase();
            $('#shopList li').each(function() {
                var shopName = $(this).find('a').text().toLowerCase();
                if (shopName.indexOf(filter) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Download CSV functionality
        $('#downloadCSV').on('click', function() {
            // Collect data for all days with shops
            allShopData = [];
                $.ajax({
                        url: APPR_AJAX_URL + 'getAll&lang='+currentLang,
                        type: 'POST',
                        dataType: 'json', // Specify the data type you expect
                        data: { },
                        async: false, // Ensure each AJAX call completes before proceeding
                        success: function(response) {
                            console.log(response)
                            response.data.forEach(function(shop) {
                                shop = shop.attributes;
                                let start_date = shop?.start_date?.date ? convertDateToDanishFormat(shop.start_date.date) : "";
                                let end_date = shop?.end_date?.date ? convertDateToDanishFormat(shop.end_date.date) : "";
                                let delivery_date = shop?.delivery_date ? convertDateToDanishFormat(shop.delivery_date) : "";

                                allShopData.push({
                                    id: shop.id,
                                    name: shop.name,
                                    startDate: start_date,
                                    endDate: end_date,
                                    deliveryDate: delivery_date
                                });
                            });
                        },
                        error: function() {
                            console.error('Fejl ved hentning af shops.');
                        }
                    });
                    console.log(allShopData)


            // Generate CSV content
            let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; // Adding BOM for UTF-8
            csvContent += "Id;Shop Name;Start Date;End Date;Delivery Date\n";
            allShopData.forEach(function(shop) {
                var row = `${shop.id};${shop.name};${shop.startDate};${shop.endDate};${shop.deliveryDate}\n`;
                csvContent += row;
            });

            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "all_shops_data.csv");
            document.body.appendChild(link); // Required for FF

            link.click();
            document.body.removeChild(link);
        });
        $('#languageSelect').on('change', function() {
            var selectedValue = $(this).val();
            changeLanguage(selectedValue);
        });
    }

    $(document).ready(function () {
        setInitialLanguage();
        renderCalendar(currentMonth, currentYear);
        setupEventListeners();
    });
    var currentLang = 1;
    function changeLanguage(value) {

        console.log('Language changed to:', value === '1' ? 'Dansk/Svensk' : 'Norsk');

        // Add or update the 'lang' parameter in the URL
        let currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('lang', value);

        // Reload the page with the new URL
        window.location.href = currentUrl.toString();
    }

    // Add this function to set the correct language on page load
    function setInitialLanguage() {
        let urlParams = new URLSearchParams(window.location.search);
        let lang = urlParams.get('lang');
        if (lang === null) {
            lang = 1;
        }
        currentLang = lang
        if (lang) {
            $('#languageSelect').val(lang);
        }
    }
</script>
</body>
</html>
