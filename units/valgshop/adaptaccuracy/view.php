<?php
$GLOBALS_PATH = \GFConfig::BACKEND_URL."units/valgshop/";
$CONTROLLER_URL = \GFConfig::BACKEND_URL."index.php?rt=unit/valgshop/adaptaccuracy/";
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adapt Accuracy Analysis</title>

    <!-- CSS -->
    <link href="<?php echo $GLOBALS_PATH ?>adaptaccuracy/css/adaptaccuracy.css?<?php echo rand(1,9999); ?>" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>


<div class="container">
    <div class="header">
        <h1>🎯 Adapt Forecast Accuracy Analysis</h1>
        <div class="subtitle">Analyser hvor præcise jeres adapt forudsigelser ramte i forhold til faktiske ordrer</div>
    </div>

    <div class="controls-section">
        <div class="control-group">
            <label for="shopSelect">Vælg Shop:</label>
            <div class="shop-select-container">
                <input type="text" id="shopSearch" placeholder="Søg efter shop..." style="margin-bottom: 5px;">
                <select id="shopSelect" size="8">
                    <option value="">Indlæser shops...</option>
                </select>
            </div>
        </div>



        <div class="control-group">
            <button id="analyzeBtn" class="btn-primary" disabled>Analyser Shop</button>
            <button id="compareAllBtn" class="btn-secondary">Sammenlign Alle Shops</button>
        </div>
    </div>

    <div class="loading" id="loading" style="display: none;">
        <div class="loading-spinner"></div>
        <div id="loadingText">Analyserer data...</div>
    </div>

    <!-- Progress bar for comparison -->
    <div class="progress-container" id="progressContainer" style="display: none;">
        <h3>🔄 Sammenligner alle shops...</h3>
        <div class="progress-bar-wrapper">
            <div class="progress-bar" id="progressBar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-text" id="progressText">0% - Starter analyse...</div>
        </div>
        <div class="progress-details" id="progressDetails">
            <div>Analyserede shops: <span id="analyzedCount">0</span> / <span id="totalCount">0</span></div>
            <div>Nuværende shop: <span id="currentShop">-</span></div>
            <div>Estimeret tid tilbage: <span id="timeRemaining">-</span></div>
        </div>
    </div>

    <div class="error-message" id="errorMessage" style="display: none;"></div>

    <!-- Single Shop Analysis Section -->
    <div class="analysis-section" id="singleShopSection" style="display: none;">
        <div class="shop-info-card">
            <h2 id="shopTitle">Shop Analyse</h2>
            <div id="shopMetadata" class="metadata"></div>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-title">Produkter Analyseret</div>
                <div class="card-value" id="totalProducts">-</div>
                <div class="card-subtitle" id="shopAutopilotInfo">-</div>
            </div>
            <div class="summary-card adapt-1">
                <div class="card-title">Adapt 1 Original</div>
                <div class="card-value" id="adapt1Accuracy">-</div>
                <div class="card-subtitle" id="adapt1Stats">-</div>
            </div>
            <div class="summary-card adapt-2">
                <div class="card-title">Adapt 2 Original</div>
                <div class="card-value" id="adapt2Accuracy">-</div>
                <div class="card-subtitle" id="adapt2Stats">-</div>
            </div>
            <div class="summary-card adapt-3">
                <div class="card-title">Adapt 3 Original</div>
                <div class="card-value" id="adapt3Accuracy">-</div>
                <div class="card-subtitle" id="adapt3Stats">-</div>
            </div>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-title">🤖 Autopilot Info</div>
                <div class="card-value" id="autopilotStage">-</div>
                <div class="card-subtitle" id="autopilotDetails">-</div>
            </div>
            <div class="summary-card adapt-1">
                <div class="card-title">Adapt 1 Autopilot</div>
                <div class="card-value" id="autopilotAdapt1Accuracy">-</div>
                <div class="card-subtitle" id="autopilotAdapt1Stats">-</div>
            </div>
            <div class="summary-card adapt-2">
                <div class="card-title">Adapt 2 Autopilot</div>
                <div class="card-value" id="autopilotAdapt2Accuracy">-</div>
                <div class="card-subtitle" id="autopilotAdapt2Stats">-</div>
            </div>
            <div class="summary-card adapt-3">
                <div class="card-title">Adapt 3 Autopilot</div>
                <div class="card-value" id="autopilotAdapt3Accuracy">-</div>
                <div class="card-subtitle" id="autopilotAdapt3Stats">-</div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-container">
                <h3>📊 Nøjagtighed per Adapt Field</h3>
                <canvas id="accuracyChart"></canvas>
            </div>

            <div class="chart-container">
                <h3>🎯 Hit Rate Distribution</h3>
                <canvas id="hitRateChart"></canvas>
            </div>

            <div class="chart-container full-width">
                <h3>📈 Forudsigelse vs Faktisk</h3>
                <canvas id="scatterChart"></canvas>
            </div>
        </div>

        <div class="product-details-section">
            <h3>📋 Produktdetaljer</h3>
            <div class="table-controls">
                <input type="text" id="productSearch" placeholder="Søg produkter...">
                <select id="accuracyFilter">
                    <option value="">Alle nøjagtigheder</option>
                    <option value="excellent">Excellent (≥90%)</option>
                    <option value="good">Good (≥75%)</option>
                    <option value="fair">Fair (≥50%)</option>
                    <option value="poor">Poor (<50%)</option>
                </select>
                <select id="typeFilter">
                    <option value="">Alle typer</option>
                    <option value="internal">Interne gaver</option>
                    <option value="external">Eksterne gaver</option>
                </select>
                <select id="statusFilter">
                    <option value="">Alle status</option>
                    <option value="active">Aktive</option>
                    <option value="closed">Lukkede</option>
                    <option value="autopilot">Autopilot justeret</option>
                </select>
            </div>
            <div class="table-container">
                <table id="productTable">
                    <thead>
                    <tr>
                        <th>Produkt</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Ordrer</th>
                        <th>Reservation</th>
                        <th>Faktisk</th>
                        <th>Adapt 1</th>
                        <th>Adapt 2</th>
                        <th>Adapt 3</th>
                        <th>Bedste Forecast</th>
                    </tr>
                    </thead>
                    <tbody id="productTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Comparison Section -->
    <div class="comparison-section" id="comparisonSection" style="display: none;">
        <div class="comparison-header">
            <h2>🏆 Sammenligning af Alle Shops</h2>
            <div id="comparisonMetadata" class="metadata"></div>
        </div>

        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-title">Shops Analyseret</div>
                <div class="card-value" id="totalShopsAnalyzed">-</div>
            </div>
            <div class="summary-card">
                <div class="card-title">Total Produkter</div>
                <div class="card-value" id="totalProductsAll">-</div>
            </div>
            <div class="summary-card overall">
                <div class="card-title">Samlet Nøjagtighed</div>
                <div class="card-value" id="overallAccuracy">-</div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-container">
                <h3>📊 Shop Performance Distribution</h3>
                <canvas id="shopDistributionChart"></canvas>
            </div>

            <div class="chart-container">
                <h3>🏆 Top & Bottom Performers</h3>
                <canvas id="topBottomChart"></canvas>
            </div>

            <div class="chart-container">
                <h3>🎁 Top & Bottom Produkter - Forecast Accuracy</h3>
                <canvas id="topBottomProductsChart"></canvas>
            </div>

            <div class="chart-container">
                <h3>📈 Average Adapt Comparison</h3>
                <canvas id="adaptAverageChart"></canvas>
            </div>
        </div>

        <div class="shop-ranking-section">
            <h3>🥇 Detaljeret Shop Ranking</h3>
            
            <!-- Column Filters -->
            <div class="table-filters">
                <div class="filter-group">
                    <label>Filtrer kolonner:</label>
                    <input type="text" id="shopRankingSearch" placeholder="Søg shop navn...">
                    <select id="accuracyRangeFilter">
                        <option value="">Alle nøjagtigheder</option>
                        <option value="excellent">Excellent (≥90%)</option>
                        <option value="good">Good (≥75%)</option>
                        <option value="fair">Fair (≥50%)</option>
                        <option value="poor">Poor (<50%)</option>
                    </select>
                    <select id="productCountFilter">
                        <option value="">Alle produkt antal</option>
                        <option value="high">Mange produkter (≥20)</option>
                        <option value="medium">Medium produkter (10-19)</option>
                        <option value="low">Få produkter (<10)</option>
                    </select>
                    <button id="sortByMaxAdaptBtn" class="btn-secondary" style="margin-left: 10px;">
                        📊 Sorter efter højeste Adapt
                    </button>
                </div>
            </div>
            
            <div class="table-container">
                <table id="shopRankingTable">
                    <thead>
                    <tr>
                        <th>Rang</th>
                        <th>Shop <span class="sortable" data-sort="shop_name">⇅</span></th>
                        <th>Samlet Nøjagtighed <span class="sortable" data-sort="avg_accuracy">⇅</span></th>
                        <th>Adapt 1 <span class="sortable" data-sort="adapt_1_accuracy">⇅</span></th>
                        <th>Adapt 2 <span class="sortable" data-sort="adapt_2_accuracy">⇅</span></th>
                        <th>Adapt 3 <span class="sortable" data-sort="adapt_3_accuracy">⇅</span></th>
                        <th>Produkter <span class="sortable" data-sort="total_products">⇅</span></th>
                    </tr>
                    </thead>
                    <tbody id="shopRankingBody">
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Shop Analysis Sidebar -->
        <div id="shopAnalysisSidebar" class="shop-sidebar" style="display: none;">
            <div class="sidebar-header">
                <h3 id="sidebarShopTitle">Shop Analyse</h3>
                <button class="sidebar-close" id="closeSidebar">&times;</button>
            </div>
            <div class="sidebar-content" id="sidebarContent">
                <div class="loading">Indlæser shop analyse...</div>
            </div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div id="productModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalProductTitle">Produktdetaljer</h3>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- SETUP CONTROLLER URL FOR JAVASCRIPT -->
<script>
    // Set the correct controller URL for the JavaScript
    window.CONTROLLER_URL = '<?php echo $CONTROLLER_URL; ?>';
    console.log('🔧 Controller URL set to:', window.CONTROLLER_URL);
</script>

<!-- Load the main JavaScript -->
<script src="<?php echo $GLOBALS_PATH ?>adaptaccuracy/js/adaptaccuracy.js?<?php echo rand(1,9999); ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the analyzer
        const analyzer = new AdaptAccuracyAnalyzer();
    });
</script>

</body>
</html>