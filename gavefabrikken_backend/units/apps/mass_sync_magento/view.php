<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Tabel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .job-beskrivelse-textarea {
            height: 300px; /* Juster denne værdi efter behov */
        }
        #lastUpdateTime {
            cursor: pointer;
            user-select: none;
        }
        #lastUpdateTime:hover {
            text-decoration: underline;
        }
        .checkbox-label {
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .large-checkbox {
            width: 20px;
            height: 20px;
            margin-right: 20px;
            margin-left: 5px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">Job Oversigt</h2>
    <p id="lastUpdateTime" class="text-muted mb-2">Sidst opdateret: <span id="updateTimestamp">-</span></p>
    <button id="nytJobBtn" class="btn btn-primary mb-3">Nyt Job</button>
    <table id="jobTabel" class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Job Title</th>
            <th>Dato</th>
            <th>Status</th>
            <th>Mode</th>
            <th>Handlinger</th>
        </tr>
        </thead>
        <tbody>
        <!-- Rækker vil blive tilføjet dynamisk -->
        </tbody>
    </table>
</div>

<!-- Nyt Job Modal -->
<div class="modal fade" id="nytJobModal" tabindex="-1" aria-labelledby="nytJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nytJobModalLabel">Nyt Job</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Luk"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="jobTitle" placeholder="Indtast job titel">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-3" style="display: none;" >
                            <input class="form-check-input" type="checkbox" id="onlineCheck">
                            <label class="form-check-label" for="onlineCheck">Online</label>
                        </div>
                        <div class="form-check" style="display: none">
                            <input class="form-check-input" type="checkbox" id="priserCheck">
                            <label class="form-check-label" for="priserCheck">Med priser</label>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Sprog:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="dansk" name="language" value="1">
                                <label class="form-check-label" for="dansk">Dansk</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="norsk" name="language" value="4">
                                <label class="form-check-label" for="norsk">Norsk</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="svensk" name="language" value="5">
                                <label class="form-check-label" for="svensk">Svensk</label>
                            </div>
                        </div>
                        <div id="danskStores" class="mt-3" style="display: none;">
                            <label class="form-label">Vælg </label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="allStores" name="danskStore" value="all">
                                <label class="form-check-label" for="allStores">All stores</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="shopgavefabrikkenDK" name="danskStore" value="default">
                                <label class="form-check-label" for="shopgavefabrikkenDK">shopgavefabrikken</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="vipShoppen" name="danskStore" value="store1">
                                <label class="form-check-label" for="Gaveklubben">Gaveklubben</label>
                            </div>
                        </div>
                        <div id="norskStores" class="mt-3" style="display: none;">
                            <label class="form-label">Velg :</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="shopgavefabrikkenNO" name="norskStore" value="gavefabrikken_no_store_view">
                                <label class="form-check-label" for="shopgavefabrikkenNO">shopgavefabrikken</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="gaveklubbenNO" name="norskStore" value="gk_no_store_view">
                                <label class="form-check-label" for="gaveklubbenNO">Gaveklubben(ikke klar)</label>
                            </div>
              
                        </div>
                        <div id="svenskStores" class="mt-3" style="display: none;">
                            <label class="form-label">Välj :</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="shopgavefabrikkenSE" name="svenskStore" value="gavefabrikken_se_store_view">
                                <label class="form-check-label" for="shopgavefabrikkenSE">gavefabrikken_se_store_view</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control mb-3 job-beskrivelse-textarea" id="itemList" rows="16" placeholder="Varenummer"></textarea>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button id="tjekImportBtn" class="btn btn-secondary me-2 mb-2">Tjek PIM data</button>
                        <button id="opretJobBtn" class="btn btn-primary mb-2">Sync til Magento</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle med Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/mass_sync_magento/";
    var hasActiveJob = false;
    var jobTable;
    var timer;

    $(document).ready(function() {

        initializeJobTable();
        loadJobs();
        startTimer();
        $('#lastUpdateTime').on('click', function() {
            loadJobs();
        });
        $('#nytJobBtn').on('click', function() {
            if (hasActiveJob) {
                alert('Du kan ikke oprette et nyt job, da der allerede er et aktivt job. Afslut det nuværende job først.');
            } else {
                stopTimer();
                $('#nytJobModal').modal('show');
            }
        });
        $('#nytJobModal').on('hidden.bs.modal', function () {
            $('input[name="language"][value="1"]').prop('checked', true);
            console.log("timer start")
            startTimer(); // Start timeren igjen når modalen lukkes
        });
        $('#tjekImportBtn').on('click', function() {
            const jobTitle = $('#jobTitle').val();
            const online = $('#onlineCheck').is(':checked');
            const medPriser = $('#priserCheck').is(':checked');
            const itemList = $('#itemList').val();
            const country = $('input[name="language"]:checked').val();
            createNewJob(jobTitle, online, medPriser, itemList,2,country);
        });


        $('input[name="language"]').on('change', function() {
            if (this.id === 'dansk') {
                $('#danskStores').show();
                $('#norskStores').hide();
                $('#svenskStores').hide();
            } else if (this.id === 'norsk') {
                $('#danskStores').hide();
                $('#norskStores').show();
                $('#svenskStores').hide();
            } else if (this.id === 'svensk') {
                $('#danskStores').hide();
                $('#norskStores').hide();
                $('#svenskStores').show();
            }
        });

        $('#tjekImportBtn').on('click', function() {
            const jobTitle = $('#jobTitle').val();
            const online = $('#onlineCheck').is(':checked');
            const medPriser = $('#priserCheck').is(':checked');
            const itemList = $('#itemList').val();
            const country = $('input[name="language"]:checked').val();
            createNewJob(jobTitle, online, medPriser, itemList, 2, country);
        });

        $('#opretJobBtn').on('click', function() {
            const jobTitle = $('#jobTitle').val();
            const online = $('#onlineCheck').is(':checked');
            const medPriser = $('#priserCheck').is(':checked');
            const itemList = $('#itemList').val();
            const country = $('input[name="language"]:checked').val();
            createNewJob(jobTitle, online, medPriser, itemList, 1, country);
        });
    });
    function updateLastUpdateTime() {
        const now = new Date();
        const formattedTime = now.toLocaleString('da-DK', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        $('#updateTimestamp').text(formattedTime);
    }
    function startTimer() {
        clearInterval(timer);
        timer = setInterval(loadJobs, 5000);
    }

    function stopTimer() {
        clearInterval(timer);
    }
    function initializeJobTable() {
        jobTable = $('#jobTabel').DataTable({
            columns: [
                {
                    data: 'attributes.id',
                    title: 'ID'
                },
                { data: 'attributes.title', title: 'Job Titel' },
                {
                    data: 'attributes.created_date.date',
                    title: 'Oprettet Dato',
                    render: function(data) {
                        return new Date(data).toLocaleString('da-DK');
                    }
                },
                {
                    data: 'attributes.status',
                    title: 'Status',
                    render: function(data, type, row) {
                        let status = parseInt(data);
                        let statusBadge = '';
                        switch(status) {
                            case 0: statusBadge = '<span class="badge bg-success">Kører</span>'; break;
                            case 1: statusBadge = '<span class="badge bg-warning">Pause</span>'; break;
                            case 2: statusBadge = '<span class="badge bg-info">Færdig</span>'; break;
                            case 3: statusBadge = '<span class="badge bg-danger">Slettet</span>'; break;
                            default: statusBadge = '<span class="badge bg-secondary">Ukendt</span>';
                        }

                        let totalCount = parseInt(row.attributes.done_count) + parseInt(row.attributes.not_done_count);
                        let countInfo = `${totalCount} / ${row.attributes.done_count}`;

                        if(status == 0 || status == 1){
                            return `${statusBadge} <br> ${countInfo}`;
                        }
                        if(status == 2){
                            return `${statusBadge} ${totalCount}`;
                        }
                        if(status == 3) return statusBadge;
                        return statusBadge;
                    }
                },
                {
                    data: 'attributes.mode',
                    title: 'Mode',
                    render: function(data) {
                        switch(parseInt(data)) {
                            case 1:
                                return 'Sync';
                            case 2:
                                return 'PIM Check';
                            default:
                                return 'Ukendt';
                        }
                    }
                },
                {
                    data: null,
                    title: 'Handlinger',
                    render: function (data, type, row) {
                        const status = parseInt(row.attributes.status);
                        const has_error = parseInt(row.attributes.has_error);
                        const jobId = row.attributes.id;
                        let actions = '';

                        switch(status) {
                            case 0: // Kører
                                actions = `
                            <button class="btn btn-sm btn-warning me-2 pause-btn" data-id="${jobId}">Pause</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${jobId}">Slet</button>
                        `;
                                break;
                            case 1: // Pause
                                actions = `
                            <button class="btn btn-sm btn-success me-2 resume-btn" data-id="${jobId}">Genoptag</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${jobId}">Slet</button>
                        `;
                                break;
                            case 2: // Fejlrapport
                                    actions = `
                                        <button class="btn btn-sm btn-primary me-2 hent-alle-btn" data-id="${jobId}">Hent alle</button>
                                    `;
                                if(has_error ) {
                                    actions += `
                                        <button class="btn btn-sm btn-danger me-2 rapport-btn" data-id="${jobId}">Hent fejlrapport</button>
                                    `;
                                }
                                break;
                            case 3: // Slettet
                                actions = '<span class="text-muted">Ingen handlinger tilgængelige</span>';
                                break;
                            default:
                                actions = '<span class="text-muted">Ukendt status</span>';
                        }

                        return actions;
                    }
                }
            ],
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Danish.json'
            }
        });

        $('#jobTabel').on('click', '.pause-btn', function() {
            const jobId = $(this).data('id');
            pauseJob(jobId);
        });

        $('#jobTabel').on('click', '.resume-btn', function() {
            const jobId = $(this).data('id');
            resumeJob(jobId);
        });

        $('#jobTabel').on('click', '.delete-btn', function() {
            const jobId = $(this).data('id');
            deleteJob(jobId);
        });

        $('#jobTabel').on('click', '.rapport-btn', function() {
            const jobId = $(this).data('id');
            errorRapport(jobId);
        });
        $('#jobTabel').on('click', '.hent-alle-btn', function() {
            const jobId = $(this).data('id');
            hentAlleRapporter(jobId);
        });

    }

    function updateJobTable(jobs) {
        jobTable.clear();
        jobTable.rows.add(jobs);
        jobTable.draw();
    }

    function pauseJob(jobId) {
        console.log('Pause job med id:', jobId);
        // Implementer pause-funktionalitet her
    }

    function resumeJob(jobId) {
        console.log('Genoptag job med id:', jobId);
        // Implementer genoptag-funktionalitet her
    }

    function deleteJob(jobId) {
        if (confirm('Er du sikker på, at du vil slette dette job?')) {
            $.ajax({
                url: APPR_AJAX_URL + 'deleteJob',
                method: 'POST',
                data: {
                    id: jobId
                },
                dataType: 'json',  // Specificerer at vi forventer JSON tilbage
                success: function(response) {
                    console.log(response);

                    // Parse JSON-svaret hvis det ikke allerede er et objekt
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Kunne ikke parse server-svaret:', e);
                            alert('Der opstod en fejl ved behandling af server-svaret.');
                            return;
                        }
                    }

                    if (response.status === "1") {
                        alert('Job slettet succesfuldt!');
                        loadJobs(); // Genindlæs job-listen for at opdatere tabellen
                    } else {
                        alert('Kunne ikke slette jobbet: ' + (response.message || 'Ukendt fejl'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Fejl ved sletning af job:', error);
                    alert('Der opstod en fejl ved sletning af jobbet. Prøv igen.');
                }
            });
        }
    }

    function errorRapport(jobId) {
        console.log('Henter fejlrapport for job med id:', jobId);
        $.ajax({
            url: APPR_AJAX_URL + 'getErrorReport',
            method: 'POST',
            data: {
                id: jobId
            },
            dataType: 'text', // Vi forventer CSV-data som tekst
            success: function(csvData) {
                // Generer og download CSV-fil
                downloadCSV(csvData, 'fejlrapport_' + jobId + '.csv');
            },
            error: function(xhr, status, error) {
                console.error('Fejl ved hentning af fejlrapport:', error);
                alert('Der opstod en fejl ved hentning af fejlrapporten. Prøv igen.');
            }
        });

    }
    function hentAlleRapporter(jobId) {
        console.log('Henter alle rapporter for job med id:', jobId);
        $.ajax({
            url: APPR_AJAX_URL + 'getAllReports',
            method: 'POST',
            data: {
                id: jobId
            },
            dataType: 'text', // Vi forventer CSV-data som tekst
            success: function(csvData) {
                // Generer og download CSV-fil
                
                downloadCSV(csvData, 'alle_rapporter_' + jobId + '.csv');
            },
            error: function(xhr, status, error) {
                console.error('Fejl ved hentning af alle rapporter:', error);
                alert('Der opstod en fejl ved hentning af rapporterne. Prøv igen.');
            }
        });
    }

    function downloadCSV(csvContent, fileName) {
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        if (navigator.msSaveBlob) { // IE 10+
            navigator.msSaveBlob(blob, fileName);
        } else {
            var link = document.createElement("a");
            if (link.download !== undefined) { // feature detection
                // Browsers that support HTML5 download attribute
                var url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", fileName);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    }
    function validateJobData(isImportCheck) {
        let errors = [];

        // Check if itemList is not empty
        if ($('#itemList').val().trim() === '') {
            errors.push('Varenummer må ikke være tom.');
        }

        // Check if language is selected
        if (!$('input[name="language"]:checked').val()) {
            errors.push('Vælg venligst et sprog (Dansk eller Norsk).');
        }

        // If it's not an import check, validate store selection
        if (!isImportCheck) {
            let selectedLanguage = $('input[name="language"]:checked').val();
            if (selectedLanguage === '1' && !$('input[name="danskStore"]:checked').val()) {
                errors.push('Vælg venligst en dansk butik.');
            } else if (selectedLanguage === '4' && !$('input[name="norskStore"]:checked').val()) {
                errors.push('Velg en norsk butikk.');
            }
        }

        return errors;
    }

    function displayErrors(errors) {
        let errorMessage = 'Følgende fejl blev fundet:\n\n';
        errors.forEach(error => {
            errorMessage += '- ' + error + '\n';
        });
        alert(errorMessage);
    }

    function createNewJob(jobTitle, online, price, itemList, mode, country) {
        let isImportCheck = (mode === 2);
        let errors = validateJobData(isImportCheck);

        if (errors.length > 0) {
            displayErrors(errors);
            return;
        }

        let selectedStore = '';
        if (country === '1') { // Dansk
            selectedStore = $('input[name="danskStore"]:checked').val();
        } else if (country === '4') { // Norsk
            selectedStore = $('input[name="norskStore"]:checked').val();
        } else if (country === '5') { // svensk
            selectedStore = $('input[name="svenskStore"]:checked').val();
        }

        $.ajax({
            url: APPR_AJAX_URL + 'createJob',
            method: 'POST',
            data: {
                title: jobTitle,
                online: online ? 1 : 0,
                price: price ? 1 : 0,
                itemList: itemList,
                mode: mode,
                country: country,
                store: selectedStore
            },
            success: function(response) {
                $('#nytJobModal').modal('hide');
                $('#onlineCheck').prop('checked', false);
                $('#priserCheck').prop('checked', false);
                $('#jobTitle').val('');
                $('#itemList').val('');
                $('input[name="language"]').prop('checked', false);
                $('input[name="danskStore"]').prop('checked', false);
                $('input[name="norskStore"]').prop('checked', false);
                $('#danskStores').hide();
                $('#norskStores').hide();
                alert('Job oprettet succesfuldt!');
                loadJobs();
                startTimer();
            },
            error: function(xhr, status, error) {
                console.error('Fejl ved oprettelse af job:', error);
                alert('Der opstod en fejl ved oprettelse af jobbet. Prøv igen.');
                startTimer();
            }
        });
    }
    async function loadJobs() {
        try {
            const response = await fetch(APPR_AJAX_URL + 'getAllJobs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseData = await response.json();

            if (responseData.status === "1" && Array.isArray(responseData.data)) {

                updateJobTable(responseData.data);
                hasActiveJob = checkForActiveJobs(responseData.data);
                updateNewJobButtonState();
                updateLastUpdateTime();
            } else {
                console.error('Uventet dataformat modtaget');
            }

        } catch (error) {
            console.error('Der opstod en fejl ved hentning af jobs:', error);
        }
    }

    function checkForActiveJobs(jobs) {
        return jobs.some(job => {
            const status = parseInt(job.attributes.status);
            return status === 0 || status === 1; // 0 = kører, 1 = pause
        });
    }

    // Replace the existing nytJobBtn click handler with this code
    $('#nytJobBtn').on('click', function() {
        if (hasActiveJob) {
            alert('Du kan ikke oprette et nyt job, da der allerede er et aktivt job. Afslut det nuværende job først.');
        } else {
            stopTimer();

            // Pre-select Danish by default
            $('input[name="language"][value="1"]').prop('checked', true);

            // Show Danish stores and hide Norwegian stores
            $('#danskStores').show();
            $('#norskStores').hide();

            $('#nytJobModal').modal('show');
        }
    });
</script>
</body>
</html>