<?php
$GLOBALS_PATH = \GFConfig::BACKEND_URL."units/valgshop/";
$shopID = $_GET["shopID"] ?? 0;
$localisation = $_GET["localisation"] ?? 1;
    ?>
<!DOCTYPE html>
<html lang="da">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dækningsbidrag Beregner</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $GLOBALS_PATH ?>contributionmargin/css/contributionmargin.css?<?php echo rand(1,9999); ?>" rel="stylesheet" />
    <style>
        .loading {
        display: none;
    }
        .calculation-row {
        border-bottom: 1px solid #eee;
        padding: 8px 0;
    }
        .sam-component {
        background-color: #f8f9fa;
        margin-left: 20px;
        padding: 5px;
        border-left: 3px solid #007bff;
    }
        .summary-box {
        background-color: #e9ecef;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        .neutral { color: #6c757d; }
    </style>
</head>
<body>
<div class="container-fluid" id="main-container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dækningsbidrag Beregner</h2>
            </div>

            <!-- Shop Information -->
            <div class="card mb-4" id="shopInfoCard" style="display: none;">
                <div class="card-header">
                    <h5 class="mb-0">Shop Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Shop:</strong> <span id="shopName"></span></p>
                            <p><strong>Antal medarbejdere:</strong> <span id="employeeCount"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Budget pr. gave:</strong> <span id="shopBudget"></span></p>
                            <p><strong>Total budget:</strong> <span id="totalBudget">Ikke beregnet</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Sprog:</strong> <span id="shopLanguage"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="summary-box" id="summaryBox" style="display: none;">
                <h4>Sammendrag</h4>
                <div class="row">
                    <div class="col-md-3">
                        <h6>Reservationer</h6>
                        <p><strong>Total reserveret:</strong> <span id="totalReservations"></span></p>
                        <p><strong>Justeret antal:</strong> <span id="adjustedTotal"></span></p>
                        <p><strong>Justeringsratio:</strong> <span id="adjustmentRatio"></span></p>
                    </div>
                    <div class="col-md-3">
                        <h6>Økonomi</h6>
                        <p><strong>Total kostpris:</strong> <span id="totalCost"></span> kr.</p>
                        <p><strong>Total salgspris:</strong> <span id="totalSaleValue"></span> kr.</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Dækningsbidrag</h6>
                        <p><strong>Total DB:</strong> <span id="totalContribution"></span> kr.</p>
                        <p><strong>DB%:</strong> <span id="totalMarginPercent"></span>%</p>
                    </div>
                    <div class="col-md-3">
                        <h6>Data Kvalitet</h6>
                        <p><strong>Varer med manglende data:</strong> <span id="missingDataCount">-</span></p>
                        <p><strong>Usikre kostpriser:</strong> <span id="unreliableCostCount">-</span></p>
                    </div>
                </div>
            </div>

            <!-- Detailed Calculations -->
            <div class="card" id="calculationsCard" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detaljeret Beregning</h5>
                    <div>
                        <button id="exportBtn" class="btn btn-sm btn-success">Export Excel</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                            <tr>
                                <th>Varenr.</th>
                                <th>Beskrivelse</th>
                                <th>Type</th>
                                <th>Opr. Antal</th>
                                <th>Just. Antal</th>
                                <th>Kostpris/stk</th>
                                <th>Budget pris/stk</th>
                                <th>Total Kost</th>
                                <th>Total Salg</th>
                                <th>DB</th>
                                <th>DB%</th>
                            </tr>
                            </thead>
                            <tbody id="calculationTableBody">
                            <!-- Data vil blive indsat her via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SAM Details Modal -->
            <div class="modal fade" id="samDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">SAM Komponenter</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h6 id="samItemName"></h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Komponent</th>
                                        <th>Beskrivelse</th>
                                        <th>Antal pr. SAM</th>
                                        <th>Kostpris/stk</th>
                                        <th>Total pr. SAM</th>
                                    </tr>
                                    </thead>
                                    <tbody id="samComponentsBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $GLOBALS_PATH ?>contributionmargin/js/contributionmargin.js?<?php echo rand(1,9999); ?>"></script>
<script>
    var contributionMargin;
    var shop = <?php echo $shopID; ?>;
    var localisation = <?php echo $localisation; ?>;

    $(document).ready(function() {
    contributionMargin = new ContributionMargin();
    contributionMargin.init(shop, localisation);
});
</script>

</body>
</html>