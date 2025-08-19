<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shop Warehouse</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- ShopWarehouse Script -->
    <script src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/js/shopWarehouse.js"></script>
    <style>
        #dropZone {
            width: 100%;
            height: 200px;
            border: 3px dashed #ccc;
            text-align: center;
            line-height: 200px;
            font-size: 24px;
            transition: border-color 0.3s;
        }
        #dropZone.border-primary {
            border-color: #007bff;
        }
        .modal-dialog-scrollable {
            max-height: 90vh;
        }
        #infoIframe {
            width: 100%;
            height: 70vh;
            border: none;
        }
        .container-fluid {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 8px;
        }
        .card-header {
            border-radius: 8px 8px 0 0 !important;
        }
        .badge {
            font-weight: normal;
            font-size: 0.9em;
            padding: 0.5em 0.8em;
        }
    </style>
</head>
<body>
<div class="card shadow-sm p-3 mb-3">
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Vælg Warehouse
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" data-warehouse="MjJiMzE4MmZkNGI1MWZkYjUxZTYzZDhiZDdmZDZhOGMxNjk4Nj">Bech Distribution A/S (BECH)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="ec0c5127697fc5152077277edfd46dc3f2b721e4b8">Becher Madsen Service (BMS KORT)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="0e0af9336c4143b3520f5d7b9dfd5001dee450332f">Becher Madsen Service (BMS-PAK)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="f68a2f8fa001a7d8227c0475371ce6ab5d5a622605">Distribution Plus (DP-PAK)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="75d3fc49f519a11c3ced357ae96da4b706e8a9fe4e">DSV (DSV KORT)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="ZTBmMzUzMzY1OTI0MmQxYmE0MTY4MGE2NzE2MDdkMTYxNjk4Nj">DSV (DSV-PAK)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="e889588fe7864a11407f270c1c9df52f8c5ef50314">Godshotellet Sjælland (GODS-H)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="80bd0c005fc994296818b8dc10ba3504cedf7df4dd">Hedehusene Lagersalg (H - LAGERS)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="2615ccf25e8f7ab92a1e04f67fb94711ab90d06a37b">Hede2 (HEDE)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="e93295a7dff57e57c5098d500897bce9eecd60634a">Lindstrøm Lager (LIND-KORT)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="ccbcec2966716815a95608102cef29f188716db00d">Lindstrøm Lager (LIND-PAK)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="30ea9c88920e0ce0f4c801ebed9ea1a67e3675eb31">Skanlog A/S (SKANLOG-GL)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="e1e9764d283d43aeafe70bbb04ba120b0e2928ed0c">Skanlog A/S (SKANLOG-IS)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="47c29c9659f61c6553fd59c916f0687f6ee1f1d9ad">GaveFabrikkens lager (HEDEHUSENE)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="98168fc85c9093e3c86e77f73868f5dc9a1131b9c9">Logistikkhuset AS (LOG)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="2ad6ea73efd3624d83733bdb60e5336e4017866e17">Norsk Bibliotektransport (NBT)</a></li>
                <li><a class="dropdown-item" href="#" data-warehouse="10bc996082cea8469819a114ad04ae7c097ec13b46">Nettvarehotellet AS (NETTVARE)</a></li>
            </ul>
        </div>
        <button class="btn btn-primary" id="getAddressBtn">Hent Adresser</button>
    </div>
</div>
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <button id="showModalInfoBtn" class="btn btn-info">LeveringsInfo</button>
        </div>
        <div class="col text-end">
            <button id="showModalBtn" class="btn btn-primary">Upload File</button>
        </div>
    </div>


    <div id="shopWarehouseContainer"></div>
</div>





<div class="container-fluid p-4 bg-light">
    <div class="row">
        <!-- System handlinger -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">System handlinger</h5>
                </div>
                <div class="card-body bg-white p-0">
                    <div class="p-3 border-bottom bg-light">
                        <div class="d-flex justify-content-between align-items-center" id="filer-status">
                            <span class="fw-bold">Filer/status</span>
                            <div>
                                <span class="badge bg-warning me-2">Afventer</span>
                                <span class="text-muted">Ikke udført endnu</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center" id="levering-status">
                            <span class="fw-bold">Leveringsinfo</span>
                            <div>
                                <span class="badge bg-warning me-2">Afventer</span>
                                <span class="text-muted">Ikke udført endnu</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-bottom bg-light">
                        <div class="d-flex justify-content-between align-items-center" id="download-status">
                            <span class="fw-bold">Download</span>
                            <div>
                                <span class="badge bg-warning me-2">Afventer</span>
                                <span class="text-muted">Ikke udført endnu</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-center" id="opdater-status">
                            <span class="fw-bold">Opdatere status</span>
                            <div>
                                <span class="badge bg-warning me-2">Afventer</span>
                                <span class="text-muted">Ikke udført endnu</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Godkendelser -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0">Godkendelser</h5>
                </div>
                <div class="card-body bg-white p-0">
                    <div class="p-3 border-bottom bg-light">
                        <div class="d-flex justify-content-between align-items-center" id="gift-check-status">
                            <span class="fw-bold">Godkendt via tjekliste (pakkemodul)</span>
                            <div>
                                <span class="badge bg-warning me-2">Afventer</span>
                                <span class="text-muted">Ikke godkendt endnu</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center" id="guide-check-status">
                            <span class="fw-bold">Pakkevejledning læst/fulgt</span>
                            <div>
                                <span class="badge bg-warning me-2">Afventer</span>
                                <span class="text-muted">Ikke godkendt endnu</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-bottom bg-light">
                        <div class="d-flex justify-content-between align-items-center" id="time-check-status">
                            <span class="fw-bold">Pakket og sendt til tiden</span>
                            <div>
                                <span class="badge bg-warning me-2">Afventer</span>
                                <span class="text-muted">Ikke godkendt endnu</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-center" id="approved-by-status">
                            <span class="fw-bold">Godkendt af</span>
                            <span class="text-muted">Ikke godkendt endnu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="dropZone" class="mb-3">
                        Drop files here
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">LeveringsInfo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="infoIframe" src=""></iframe>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    class StatusHandler {
        constructor(shopId) {
            this.shopId = shopId;
            this.init();
        }

        init() {
            this.fetchStatus();
            setInterval(() => this.fetchStatus(), 60000); // Opdaterer hver 60 sekunder
        }

        fetchStatus() {
            let self = this;
            $.post(BASE_AJAX_URL + "shopWarehouse/readStatus", {shop_id: this.shopId}, function(response, textStatus) {
                self.updateStatus(response.data[0].attributes);
            }, "json")
                .fail(function() {
                    alert("Der opstod en fejl ved hentning af status.");
                });
        }

        updateStatus(data) {
            // System handlinger
            this.updateStatusElement('filer', data.log_menu?.date);
            this.updateStatusElement('levering', data.log_info?.date);
            this.updateStatusElement('download', data.log_download?.date);
            this.updateStatusElement('opdater', data.log_status?.date);

            // Godkendelser
            // For 'Godkendt via tjekliste (pakkemodul)', bruger vi 'approved_count_date'
            const approvedCountDate = data.approved_count_date?.date;
            const isGiftCheckApproved = approvedCountDate !== null && approvedCountDate !== undefined;

            this.updateApprovalElement('gift-check', {
                status: isGiftCheckApproved,
                date: approvedCountDate
            });

            this.updateApprovalElementNoDate('guide-check', data.approved_package_instructions === 1);
            this.updateApprovalElementNoDate('time-check', data.approved_ontime === 1);

            if (data.approved_by) {
                const approvedByText = data.approved_by;
                $('#approved-by-status .text-muted').text(approvedByText);
            } else {
                $('#approved-by-status .text-muted').text('Ikke godkendt endnu');
            }
        }

        updateStatusElement(key, timestamp) {
            const $container = $(`#${key}-status`);

            if (timestamp) {
                $container.find('.badge')
                    .removeClass('bg-warning')
                    .addClass('bg-success')
                    .text('Udført');
                $container.find('.text-muted').text(this.formatTimestamp(timestamp));
            } else {
                $container.find('.badge')
                    .removeClass('bg-success')
                    .addClass('bg-warning')
                    .text('Afventer');
                $container.find('.text-muted').text('Ikke udført endnu');
            }
        }

        updateApprovalElement(key, data) {
            const $container = $(`#${key}-status`);

            if (data.status) {
                $container.find('.badge')
                    .removeClass('bg-warning')
                    .addClass('bg-success')
                    .text('Godkendt');
                $container.find('.text-muted').text(data.date ? this.formatTimestamp(data.date) : 'Godkendt');
            } else {
                $container.find('.badge')
                    .removeClass('bg-success')
                    .addClass('bg-warning')
                    .text('Afventer');
                $container.find('.text-muted').text('Ikke godkendt endnu');
            }
        }

        updateApprovalElementNoDate(key, isApproved) {
            const $container = $(`#${key}-status`);

            if (isApproved) {
                $container.find('.badge')
                    .removeClass('bg-warning')
                    .addClass('bg-success')
                    .text('Godkendt');
                $container.find('.text-muted').text('Godkendt');
            } else {
                $container.find('.badge')
                    .removeClass('bg-success')
                    .addClass('bg-warning')
                    .text('Afventer');
                $container.find('.text-muted').text('Ikke godkendt endnu');
            }
        }

        formatTimestamp(timestamp) {
            if (!timestamp) return '';

            const date = new Date(timestamp);
            return date.toLocaleString('da-DK', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        handleError(error) {
            console.error('Error in StatusHandler:', error);
            const errorHtml = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Der opstod en fejl ved hentning af status
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('.container-fluid').prepend(errorHtml);
        }
    }

    $(document).ready(function(){
        const shopId = <?php echo $_GET["shopID"]; ?>;

        // Definer BASE_AJAX_URL hvis den ikke allerede er defineret
        const BASE_AJAX_URL = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=';

        // Initialiser ShopWarehouse
        const sw = new ShopWarehouse(shopId);
        sw.init();

        // Initialiser StatusHandler
        const statusHandler = new StatusHandler(shopId);

        // Modal handlers
        $("#showModalBtn").click(function() {
            new bootstrap.Modal(document.getElementById('uploadModal')).show();
        });

        $("#showModalInfoBtn").click(function() {
            $("#infoIframe").attr("src", `https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=warehousePortal/showDeleveryDetail&shopid=${shopId}`);
            new bootstrap.Modal(document.getElementById('infoModal')).show();
        });

        // Drag and Drop handlers
        var dropZone = $('#dropZone');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone[0].addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone[0].addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone[0].addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.addClass('border-primary');
        }

        function unhighlight(e) {
            dropZone.removeClass('border-primary');
        }

        // Handle dropped files
        dropZone[0].addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            var dt = e.dataTransfer;
            var files = dt.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            var formData = new FormData();

            for (var i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            $.ajax({
                url: `https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=shopWarehouse/multiFileupload&shop_id=${shopId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                    sw.init();
                }
            });
        }
    });
</script>
</body>
</html>
