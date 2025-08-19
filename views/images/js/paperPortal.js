var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";

function PaperPortal(isAdmin) {
    let self = this;
    let template = {};
    let settings = {};
    let data = {};

    this.init = async function () {
        this.template = new PaperPortalTemplate();
        $("#login-form").remove();
        if(isAdmin){
            $("#paper-settings").html(this.template.settings());
        }

        let fieldSettings = await this.getfieldSettings();
        console.log(fieldSettings)

        $("#paper-reg").html(this.template.layout());

        this.workerTable();
        this.giftTable();
    };

    this.getfieldSettings = async function (){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/getfieldSettings", {shopID: _shopID}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    };

    this.loadData = async function (){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/readsettings", {token: _token}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    };

    this.workerTable = () => {
        var table = $('#workerTabel').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/da.json'
            },
            columns: [
                { data: 'navn' },
                { data: 'gave' },
                { data: 'lokation' },
                {
                    data: null,
                    defaultContent: '<button class="btn btn-sm btn-primary worker-update-btn">Opdater</button> ' +
                        '<button class="btn btn-sm btn-danger worker-delete-btn">Slet</button>',
                    orderable: false
                }
            ]
        });

        // Add CSV download and upload buttons
        $('#paper-reg').prepend(`
            <div class="mb-3">
                <button id="downloadCSV" class="btn btn-success me-2">Download skabelon</button>
                <label for="uploadCSV" class="btn btn-primary me-2">Importere skabelon med data</label>
                <input type="file" id="uploadCSV" accept=".csv" style="display: none;">
            </div>
        `);

        // Add modal for CSV preview
        $('#paper-reg').append(`
            <div class="modal fade" id="csvPreviewModal" tabindex="-1" aria-labelledby="csvPreviewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="csvPreviewModalLabel">CSV Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table id="csvPreviewTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr></tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                            <button type="button" class="btn btn-primary" id="importCSVData">Importer Data</button>
                        </div>
                    </div>
                </div>
            </div>
        `);

        // CSV download functionality
        $('#downloadCSV').on('click', function() {
            let csvContent = "data:text/csv;charset=utf-8,\uFEFF";  // Adding BOM for Excel compatibility
            csvContent += "Navn;Lokation;Gave\n";  // CSV header with semicolon separator

            // Get all data from the table
            let data = table.rows().data();

            // Convert data to CSV format with semicolon separator
            data.each(function(row) {
                let csvRow = [row.navn, row.lokation, row.gave].join(";");
                csvContent += csvRow + "\n";
            });

            // Create a download link and trigger the download
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "medarbejder_liste.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // CSV upload functionality
        $('#uploadCSV').on('change', function(e) {
            var file = e.target.files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                var csvData = e.target.result;
                var allTextLines = csvData.split(/\r\n|\n/);
                var headers = allTextLines[0].split(';');
                var lines = [];

                // Populate table headers
                var previewTableHead = $('#csvPreviewTable thead tr');
                previewTableHead.empty();
                headers.forEach(function(header) {
                    previewTableHead.append($('<th>').text(header));
                });

                for (var i=1; i<allTextLines.length; i++) {
                    var data = allTextLines[i].split(';');
                    if (data.length == headers.length) {
                        var tarr = {};
                        for (var j=0; j<headers.length; j++) {
                            tarr[headers[j]] = data[j];
                        }
                        lines.push(tarr);
                    }
                }

                // Populate preview table
                var previewTableBody = $('#csvPreviewTable tbody');
                previewTableBody.empty();
                lines.forEach(function(line) {
                    var row = $('<tr>');
                    headers.forEach(function(header) {
                        row.append($('<td>').text(line[header]));
                    });
                    previewTableBody.append(row);
                });

                // Show modal
                var modal = new bootstrap.Modal(document.getElementById('csvPreviewModal'));
                modal.show();
            };
            reader.readAsText(file, 'UTF-8');  // Specify UTF-8 encoding
        });

        // Import CSV data
        $('#importCSVData').on('click', function() {
            var headers = [];
            $('#csvPreviewTable thead th').each(function() {
                headers.push($(this).text());
            });

            var newData = [];
            $('#csvPreviewTable tbody tr').each(function() {
                var row = $(this);
                var rowData = {};
                row.find('td').each(function(index) {
                    rowData[headers[index]] = $(this).text();
                });
                newData.push(rowData);
            });

            // Map CSV data to table structure
            var mappedData = newData.map(function(item) {
                return {
                    navn: item['Navn'] || '',
                    gave: item['Gave'] || '',
                    lokation: item['Lokation'] || ''
                };
            });

            table.rows.add(mappedData).draw();

            // Close modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('csvPreviewModal'));
            modal.hide();

            // Clear file input
            $('#uploadCSV').val('');
        });

        $('#opretWorker').on('click', function() {
            var navn = $('#workerNavnInput').val();
            var gave = $('#workerGaveSelect').val();
            var lokation = $('#workerLokationSelect').val();

            if(navn && gave && lokation) {
                table.row.add({
                    navn: navn,
                    gave: gave,
                    lokation: lokation
                }).draw();

                $('#workerNavnInput').val('');
                $('#workerGaveSelect').val('');
                $('#workerLokationSelect').val('');
            } else {
                alert('Udfyld venligst alle felter.');
            }
        });

        $('#workerTabel tbody').on('click', '.worker-delete-btn', function() {
            table.row($(this).closest('tr')).remove().draw();
        });

        $('#workerTabel tbody').on('click', '.worker-update-btn', function() {
            var row = $(this).closest('tr');
            var oldData = table.row(row).data();

            var updatedData = {
                navn: row.find('td:eq(0)').text().trim(),
                gave: row.find('td:eq(1) select').val(),
                lokation: row.find('td:eq(2) select').val()
            };

            if (JSON.stringify(oldData) === JSON.stringify(updatedData)) {
                alert('Ingen ændringer at gemme.');
                return;
            }

            if (!updatedData.navn || !updatedData.gave || !updatedData.lokation) {
                alert('Alle felter skal udfyldes.');
                return;
            }

            table.row(row).data(updatedData).draw();
            row.removeClass('worker-editing');
        });

        $('#workerTabel tbody').on('dblclick', 'td:not(:last-child)', function() {
            var cell = $(this);
            var row = cell.closest('tr');
            if (!row.hasClass('worker-editing')) {
                row.addClass('worker-editing');
                var data = table.row(row).data();

                row.find('td:eq(0)').attr('contenteditable', 'true');

                row.find('td:eq(1)').html($('<select>').append(gaveOptions.clone()))
                    .find('select').val(data.gave);

                row.find('td:eq(2)').html($('<select>').append(lokationOptions.clone()))
                    .find('select').val(data.lokation);
            }
        });

        $('#workerTabel thead').on('click', 'th', function(e) {
            if ($('#workerTabel tbody tr.worker-editing').length) {
                e.preventDefault();
                alert('Afslut redigering før sortering.');
            }
        });
    };

    this.giftTable = () => {
        var gaveOptions = $('#giftGaveSelect option:not(:first)').clone();
        var lokationOptions = $('#giftLokationSelect option:not(:first)').clone();

        var table = $('#giftTabel').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/da.json'
            },
            columns: [
                { data: 'gave' },
                { data: 'lokation' },
                { data: 'antal' },
                {
                    data: null,
                    defaultContent: '<button class="btn btn-sm btn-primary gift-update-btn">Opdater</button> ' +
                        '<button class="btn btn-sm btn-danger gift-delete-btn">Slet</button>',
                    orderable: false
                }
            ]
        });

        $('#opretGift').on('click', function() {
            var gave = $('#giftGaveSelect').val();
            var lokation = $('#giftLokationSelect').val();
            var antal = $('#giftAntalInput').val();

            if(gave && lokation && antal) {
                table.row.add({
                    gave: gave,
                    lokation: lokation,
                    antal: antal
                }).draw();

                $('#giftGaveSelect').val('');
                $('#giftLokationSelect').val('');
                $('#giftAntalInput').val('');
            } else {
                alert('Udfyld venligst alle felter.');
            }
        });

        $('#giftTabel tbody').on('click', '.gift-delete-btn', function() {
            table.row($(this).closest('tr')).remove().draw();
        });

        $('#giftTabel tbody').on('click', '.gift-update-btn', function() {
            var row = $(this).closest('tr');
            var oldData = table.row(row).data();

            var updatedData = {
                gave: row.find('td:eq(0) select').val(),
                lokation: row.find('td:eq(1) select').val(),
                antal: row.find('td:eq(2)').text().trim()
            };

            if (JSON.stringify(oldData) === JSON.stringify(updatedData)) {
                alert('Ingen ændringer at gemme.');
                return;
            }

            if (!updatedData.gave || !updatedData.lokation || !updatedData.antal) {
                alert('Alle felter skal udfyldes.');
                return;
            }

            table.row(row).data(updatedData).draw();
            row.removeClass('gift-editing');
        });

        $('#giftTabel tbody').on('dblclick', 'td:not(:last-child)', function() {
            var cell = $(this);
            var row = cell.closest('tr');
            if (!row.hasClass('gift-editing')) {
                row.addClass('gift-editing');
                var data = table.row(row).data();

                row.find('td:eq(0)').html($('<select>').append(gaveOptions.clone()))
                    .find('select').val(data.gave);

                row.find('td:eq(1)').html($('<select>').append(lokationOptions.clone()))
                    .find('select').val(data.lokation);

                row.find('td:eq(2)').attr('contenteditable', 'true');
            }
        });

        $('#giftTabel thead').on('click', 'th', function(e) {
            if ($('#giftTabel tbody tr.gift-editing').length) {
                e.preventDefault();
                alert('Afslut redigering før sortering.');
            }
        });
    };
}

function PaperPortalTemplate() {
    let self = this;

    this.settings = () => {
        return `<div class="container mt-5"><br>
            <h2 class="text-center mb-4">Kundeindstillinger</h2>
               <hr>
            <form id="kundeForm">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Aktivere for kunden</h5>
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" id="arbejdeMulighed">
                                    <label class="form-check-label" for="arbejdeMulighed">Aktivér</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Kunde kan redigere ellers kun kigge adgang</h5>
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" id="kiggeAdgang">
                                    <label class="form-check-label" for="kiggeAdgang">Tillad</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Listetype</h5>
                                <div class="mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listetype" id="kunFordelingsliste" value="kunFordelingsliste" checked>
                                        <label class="form-check-label" for="kunFordelingsliste">
                                            Kun fordelingsliste
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listetype" id="medarbejderListe" value="medarbejderListe">
                                        <label class="form-check-label" for="medarbejderListe">
                                            Medarbejderliste med fordelingsliste kig
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-custom btn-lg">Registrer kundedata i gavevalgsystemet</button>
                </div>
            </form>
        </div><hr>`;
    };

    this.layout = () => {
        return `<div class="container mt-5">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="worker-tab" data-bs-toggle="tab" data-bs-target="#worker" type="button" role="tab" aria-controls="worker" aria-selected="true">Medarbejder Liste</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="gift-tab" data-bs-toggle="tab" data-bs-target="#gift" type="button" role="tab" aria-controls="gift" aria-selected="false">Gave Liste</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="worker" role="tabpanel" aria-labelledby="worker-tab">
                    <br>
                    <div class="row mt-3">
                        <div class="col-md-3">
                             <div id="workerNavnFloating" class="form-floating">
                                <input type="text" id="workerNavnInput" class="form-control" placeholder="Navn">
                                <label id="workerNavnLabel" for="workerNavnInput">Navn</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select id="workerGaveSelect" class="form-select">
                                <option value="">Vælg gave</option>
                                <option value="Vin">Vin</option>
                                <option value="Chokolade">Chokolade</option>
                                <option value="Gavekort">Gavekort</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="workerLokationSelect" class="form-select">
                                <option value="">Vælg lokation</option>
                                <option value="Kontor A">Kontor A</option>
                                <option value="Kontor B">Kontor B</option>
                                <option value="Hjemmekontor">Hjemmekontor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button id="opretWorker" class="btn btn-primary">Opret</button>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-3">
                        <table id="workerTabel" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Navn</th>
                                    <th>Gave</th>
                                    <th>Lokation</th>
                                    <th>Handling</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="gift" role="tabpanel" aria-labelledby="gift-tab">
                    <br>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <select id="giftGaveSelect" class="form-select">
                                <option value="">Vælg gave</option>
                                <option value="Vin">Vin</option>
                                <option value="Chokolade">Chokolade</option>
                                <option value="Gavekort">Gavekort</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="giftLokationSelect" class="form-select">
                                <option value="">Vælg lokation</option>
                                <option value="Kontor A">Kontor A</option>
                                <option value="Kontor B">Kontor B</option>
                                <option value="Hjemmekontor">Hjemmekontor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div id="giftAntalFloating" class="form-floating">
                                <input type="number" id="giftAntalInput" class="form-control" placeholder="Antal">
                                <label id="giftAntalLabel" for="giftAntalInput">Antal</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button id="opretGift" class="btn btn-primary">Opret</button>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-3">
                        <table id="giftTabel" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Gave</th>
                                    <th>Lokation</th>
                                    <th>Antal</th>
                                    <th>Handling</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>`;
    }
}