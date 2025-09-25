/**
 * Adapt Accuracy Analysis Template Processor
 * Contains the HTML template and handles initialization
 */

class AdaptAccuracyTP {
    constructor() {
        this.init();
    }

    init() {
        // Check if we're in the adaptaccuracy module
        if (this.isAdaptAccuracyPage()) {
            this.initializeModule();
        }
    }

    isAdaptAccuracyPage() {
        // Check URL or page indicators
        const url = window.location.href;
        return url.includes('adaptaccuracy') ||
            document.querySelector('.adapt-accuracy-container') !== null ||
            document.getElementById('adaptAccuracyContent') !== null;
    }

    initializeModule() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.setupModule();
            });
        } else {
            this.setupModule();
        }
    }

    setupModule() {
        // Render the main template
        this.renderTemplate();

        // Initialize the main analyzer after template is loaded
        setTimeout(() => {
            if (typeof AdaptAccuracyAnalyzer !== 'undefined') {
                window.adaptAccuracyAnalyzer = new AdaptAccuracyAnalyzer();
            }
            this.setupURLHandling();
            this.setupKeyboardShortcuts();
            this.setupExportFunctionality();
        }, 100);
    }

    renderTemplate() {
        // Find the main content container
        const container = document.getElementById('adaptAccuracyContent') ||
            document.querySelector('.adapt-accuracy-container') ||
            document.body;

        // Inject the main HTML template
        container.innerHTML = this.getHTMLTemplate();

        // Load required CSS and external scripts if not already loaded
        this.loadDependencies();
    }

    loadDependencies() {
        // Load Chart.js if not already loaded
        if (typeof Chart === 'undefined') {
            const chartScript = document.createElement('script');
            chartScript.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            document.head.appendChild(chartScript);
        }

        // Load CSS if not already loaded
        if (!document.querySelector('link[href*="adaptaccuracy.css"]')) {
            const cssLink = document.createElement('link');
            cssLink.rel = 'stylesheet';
            cssLink.href = `${window.GLOBALS_PATH || ''}units/valgshop/adaptaccuracy/css/adaptaccuracy.css?${Math.random()}`;
            document.head.appendChild(cssLink);
        }
    }

    getHTMLTemplate() {
        return `
        <div class="container">
            <div class="header">
                <h1>🎯 Adapt Forecast Accuracy Analysis</h1>
                <div class="subtitle">Analyser hvor præcise jeres adapt forudsigelser ramte i forhold til faktiske ordrer</div>
            </div>

            <div class="controls-section">
                <div class="control-group">
                    <label for="shopSelect">Vælg Shop:</label>
                    <select id="shopSelect">
                        <option value="">Indlæser shops...</option>
                    </select>
                </div>
                
                <div class="control-group">
                    <label for="dateFrom">Fra dato:</label>
                    <input type="date" id="dateFrom">
                </div>
                
                <div class="control-group">
                    <label for="dateTo">Til dato:</label>
                    <input type="date" id="dateTo">
                </div>
                
                <div class="control-group">
                    <button id="analyzeBtn" class="btn-primary" disabled>Analyser Shop</button>
                    <button id="compareAllBtn" class="btn-secondary">Sammenlign Alle Shops</button>
                </div>
            </div>

            <div class="loading" id="loading" style="display: none;">
                <div class="loading-spinner"></div>
                <div>Analyserer data...</div>
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
                    </div>
                    <div class="summary-card adapt-1">
                        <div class="card-title">Adapt 1 Nøjagtighed</div>
                        <div class="card-value" id="adapt1Accuracy">-</div>
                        <div class="card-subtitle" id="adapt1Stats">-</div>
                    </div>
                    <div class="summary-card adapt-2">
                        <div class="card-title">Adapt 2 Nøjagtighed</div>
                        <div class="card-value" id="adapt2Accuracy">-</div>
                        <div class="card-subtitle" id="adapt2Stats">-</div>
                    </div>
                    <div class="summary-card adapt-3">
                        <div class="card-title">Adapt 3 Nøjagtighed</div>
                        <div class="card-value" id="adapt3Accuracy">-</div>
                        <div class="card-subtitle" id="adapt3Stats">-</div>
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
                    </div>
                    <div class="table-container">
                        <table id="productTable">
                            <thead>
                                <tr>
                                    <th>Produkt</th>
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
                        <h3>🏪 Shop Performance Ranking</h3>
                        <canvas id="shopComparisonChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <h3>📊 Adapt Field Performance</h3>
                        <canvas id="adaptComparisonChart"></canvas>
                    </div>
                </div>

                <div class="shop-ranking-section">
                    <h3>🥇 Detaljeret Shop Ranking</h3>
                    <div class="table-container">
                        <table id="shopRankingTable">
                            <thead>
                                <tr>
                                    <th>Rang</th>
                                    <th>Shop</th>
                                    <th>Gennemsnitlig Nøjagtighed</th>
                                    <th>Adapt 1</th>
                                    <th>Adapt 2</th>
                                    <th>Adapt 3</th>
                                    <th>Produkter</th>
                                </tr>
                            </thead>
                            <tbody id="shopRankingBody">
                            </tbody>
                        </table>
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
        `;
    }

    setupURLHandling() {
        // Handle URL parameters for direct linking
        const urlParams = new URLSearchParams(window.location.search);
        const shopId = urlParams.get('shop_id');
        const action = urlParams.get('action');

        if (shopId) {
            // Pre-select shop if specified in URL
            setTimeout(() => {
                const shopSelect = document.getElementById('shopSelect');
                if (shopSelect) {
                    shopSelect.value = shopId;
                    shopSelect.dispatchEvent(new Event('change'));

                    // Auto-analyze if action is specified
                    if (action === 'analyze') {
                        setTimeout(() => {
                            document.getElementById('analyzeBtn')?.click();
                        }, 500);
                    }
                }
            }, 1000);
        }

        if (action === 'compare') {
            // Auto-run comparison
            setTimeout(() => {
                document.getElementById('compareAllBtn')?.click();
            }, 1000);
        }
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // ESC to close modal
            if (e.key === 'Escape') {
                const modal = document.getElementById('productModal');
                if (modal && modal.style.display === 'block') {
                    modal.style.display = 'none';
                }
            }

            // Ctrl+Enter to analyze selected shop
            if (e.ctrlKey && e.key === 'Enter') {
                const analyzeBtn = document.getElementById('analyzeBtn');
                if (analyzeBtn && !analyzeBtn.disabled) {
                    analyzeBtn.click();
                }
            }

            // Ctrl+Shift+Enter to compare all shops
            if (e.ctrlKey && e.shiftKey && e.key === 'Enter') {
                document.getElementById('compareAllBtn')?.click();
            }
        });
    }

    setupExportFunctionality() {
        // Add export buttons if they don't exist
        const controlsSection = document.querySelector('.controls-section');
        if (controlsSection && !document.getElementById('exportControls')) {
            const exportDiv = document.createElement('div');
            exportDiv.id = 'exportControls';
            exportDiv.className = 'control-group';
            exportDiv.innerHTML = `
                <button id="exportJson" class="btn-secondary" style="font-size: 12px; padding: 8px 16px;">Export JSON</button>
                <button id="exportCsv" class="btn-secondary" style="font-size: 12px; padding: 8px 16px;">Export CSV</button>
            `;
            controlsSection.appendChild(exportDiv);

            // Add event listeners
            document.getElementById('exportJson').addEventListener('click', () => {
                this.exportAnalysisData('json');
            });

            document.getElementById('exportCsv').addEventListener('click', () => {
                this.exportAnalysisData('csv');
            });
        }
    }

    // Utility method to generate shareable URLs
    generateShareableURL(shopId = null, action = null) {
        const baseURL = window.location.origin + window.location.pathname;
        const params = new URLSearchParams();

        if (shopId) {
            params.set('shop_id', shopId);
        }
        if (action) {
            params.set('action', action);
        }

        return baseURL + (params.toString() ? '?' + params.toString() : '');
    }

    // Method to export current analysis data
    exportAnalysisData(format = 'json') {
        if (!window.adaptAccuracyAnalyzer || !window.adaptAccuracyAnalyzer.currentData) {
            alert('Ingen data at eksportere. Kør en analyse først.');
            return;
        }

        const data = window.adaptAccuracyAnalyzer.currentData;
        const timestamp = new Date().toISOString().slice(0, 10);

        if (format === 'json') {
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            this.downloadFile(blob, `adapt_accuracy_${timestamp}.json`);
        } else if (format === 'csv') {
            const csv = this.convertToCSV(data);
            const blob = new Blob([csv], { type: 'text/csv' });
            this.downloadFile(blob, `adapt_accuracy_${timestamp}.csv`);
        }
    }

    convertToCSV(data) {
        if (!data.analysis || !data.analysis.product_analyses) {
            return '';
        }

        const headers = [
            'Present ID',
            'Present Name',
            'Model Name',
            'Model Present No',
            'Reservation Quantity',
            'Actual Quantity',
            'Adapt 1 Predicted',
            'Adapt 1 Accuracy %',
            'Adapt 2 Predicted',
            'Adapt 2 Accuracy %',
            'Adapt 3 Predicted',
            'Adapt 3 Accuracy %'
        ];

        const rows = data.analysis.product_analyses.map(product => [
            product.present_id,
            product.present_name || '',
            product.model_name || '',
            product.model_present_no || '',
            product.reservation_quantity,
            product.actual_quantity,
            product.accuracy_analysis.adapt_1.predicted || '',
            Math.round(product.accuracy_analysis.adapt_1.accuracy_percent || 0),
            product.accuracy_analysis.adapt_2.predicted || '',
            Math.round(product.accuracy_analysis.adapt_2.accuracy_percent || 0),
            product.accuracy_analysis.adapt_3.predicted || '',
            Math.round(product.accuracy_analysis.adapt_3.accuracy_percent || 0)
        ]);

        return [headers, ...rows].map(row =>
            row.map(cell => `"${cell}"`).join(',')
        ).join('\n');
    }

    downloadFile(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // Add utility method to share current analysis
    shareCurrentAnalysis() {
        const shopSelect = document.getElementById('shopSelect');
        if (!shopSelect || !shopSelect.value) {
            alert('Vælg en shop først');
            return;
        }

        const shareUrl = this.generateShareableURL(shopSelect.value, 'analyze');

        if (navigator.share) {
            navigator.share({
                title: 'Adapt Accuracy Analysis',
                text: 'Se denne shop analyse',
                url: shareUrl
            });
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(shareUrl).then(() => {
                alert('Link kopieret til clipboard');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = shareUrl;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Link kopieret til clipboard');
        }
    }
}

// Initialize template processor when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adaptAccuracyTP = new AdaptAccuracyTP();
});

// Also initialize immediately if DOM is already ready
if (document.readyState !== 'loading') {
    window.adaptAccuracyTP = new AdaptAccuracyTP();
}