<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Salgsstatistik - Gavekort (Webordre)</h2>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-2 mb-3">
                    <label for="databaseSelect" class="form-label">Database:</label>
                    <select id="databaseSelect" class="form-select">
                        <option value="gavefabrikken2024">Gavefabrikken 2024</option>
                        <option value="gavefabrikken2025" selected>Gavefabrikken 2025</option>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="startDate" class="form-label">Fra dato:</label>
                    <input type="date" id="startDate" class="form-control" value="2025-01-01">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="endDate" class="form-label">Til dato:</label>
                    <input type="date" id="endDate" class="form-control" value="2025-12-31">
                </div>
                
                <div class="col-md-2 mb-3">
                    <button id="loadDataBtn" class="btn btn-primary w-100">
                        <i class="fas fa-sync"></i> Hent data
                    </button>
                </div>
                
                <div class="col-md-2 mb-3">
                    <button id="exportCSVBtn" class="btn btn-success w-100">
                        <i class="fas fa-download"></i> Eksporter CSV
                    </button>
                </div>
                
                <div class="col-md-2 mb-3">
                    <button id="toggleDebugBtn" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-code"></i> Debug
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Danmark omsætning</div>
                    <div id="danmarkRevenue" class="h5 mb-0 font-weight-bold text-gray-800">-</div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Norge omsætning</div>
                    <div id="norgeRevenue" class="h5 mb-0 font-weight-bold text-gray-800">-</div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sverige omsætning</div>
                    <div id="sverigeRevenue" class="h5 mb-0 font-weight-bold text-gray-800">-</div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total omsætning</div>
                    <div id="totalRevenue" class="h5 mb-0 font-weight-bold text-gray-800">-</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Antal solgte gavekort</div>
                    <div id="totalSales" class="h5 mb-0 font-weight-bold text-gray-800">-</div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Aktiv database</div>
                    <div id="activeDatabase" class="h5 mb-0 font-weight-bold text-gray-800">-</div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Periode</div>
                    <div id="activePeriod" class="h6 mb-0 font-weight-bold text-gray-800">-</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Månedlig omsætning</h6>
                    <select id="monthlyRevenueCountryFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="all">Alle lande</option>
                        <option value="Danmark">Danmark</option>
                        <option value="Norge">Norge</option>
                        <option value="Sverige">Sverige</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Antal solgt per måned</h6>
                    <select id="monthlySalesCountryFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="all">Alle lande</option>
                        <option value="Danmark">Danmark</option>
                        <option value="Norge">Norge</option>
                        <option value="Sverige">Sverige</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Omsætning per land</h6>
                </div>
                <div class="card-body">
                    <canvas id="countryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Alle koncepter</h6>
                    <div>
                        <div class="btn-group btn-group-sm me-3" role="group">
                            <input type="radio" class="btn-check" name="conceptBarMode" id="conceptBarRevenue" autocomplete="off" checked>
                            <label class="btn btn-outline-primary btn-sm" for="conceptBarRevenue">Omsætning</label>
                            
                            <input type="radio" class="btn-check" name="conceptBarMode" id="conceptBarCount" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="conceptBarCount">Antal kort</label>
                        </div>
                        <select id="conceptBarCountryFilter" class="form-select form-select-sm d-inline-block" style="width: auto;">
                            <option value="Danmark" selected>Danmark</option>
                            <option value="Norge">Norge</option>
                            <option value="Sverige">Sverige</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="conceptBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4" id="debugCard" style="display: none;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">Debug Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>SQL Query:</h6>
                    <pre id="debugSql" style="font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 4px;"></pre>
                </div>
                <div class="col-md-6">
                    <h6>Parameters:</h6>
                    <pre id="debugParams" style="font-size: 12px; background: #f8f9fa; padding: 10px; border-radius: 4px;"></pre>
                    <h6>Result Count:</h6>
                    <span id="debugCount" class="badge bg-info"></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Detaljeret data tabel</h6>
            <select id="dataTableCountryFilter" class="form-select form-select-sm" style="width: auto;">
                <option value="all">Alle lande</option>
                <option value="Danmark">Danmark</option>
                <option value="Norge">Norge</option>
                <option value="Sverige">Sverige</option>
            </select>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="dataTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="land">Land ↕</th>
                            <th class="sortable" data-sort="month_year">Måned ↕</th>
                            <th class="sortable" data-sort="concept_code">Koncept ↕</th>
                            <th class="sortable text-end" data-sort="pris_kr">Pris (kr) ↕</th>
                            <th class="sortable text-end" data-sort="total_sold">Antal solgt ↕</th>
                            <th class="sortable text-end" data-sort="total_omsaetning">Omsætning (kr) ↕</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; padding: 30px; border-radius: 8px; text-align: center;">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mb-0">Henter data...</p>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $assetPath; ?>css/salesstatistics.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo GFConfig::BACKEND_URL; ?>views/lib/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var salesStatisticsObj = null;
    function salesStatisticsReady() {
        console.log('Sales Statistics unit ready');
        salesStatisticsObj = new salesStatisticsUnit();
        salesStatisticsObj.run('<?php echo $assetPath; ?>', '<?php echo $servicePath; ?>');
    }
</script>
<script src="<?php echo $assetPath ?>js/salesstatistics.js"></script>