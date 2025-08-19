# Frontend Architecture - Adapt Accuracy Analysis

## 🏗️ Architecture Overview

The frontend uses a modular JavaScript architecture with a main `AdaptAccuracyAnalyzer` class that manages all user interactions, API calls, and data visualization.

### Technology Stack
- **JavaScript ES6+**: Modern JavaScript features with async/await
- **Chart.js**: Data visualization and charting
- **Bootstrap 4**: Responsive UI framework
- **CSS Grid/Flexbox**: Layout management
- **Fetch API**: HTTP requests to backend
- **URLSearchParams**: Query parameter handling
- **Progress API**: Real-time progress tracking

---

## 📁 File Structure

```
adaptaccuracy/
├── view.php                    # Main HTML template
├── css/
│   └── adaptaccuracy.css      # Custom styling
└── js/
    ├── adaptaccuracy.js       # Main analyzer class
    └── adaptaccuracy.tp.js    # Template/utility functions
```

---

## 🎯 Main Components

### 1. AdaptAccuracyAnalyzer Class

**Location:** `js/adaptaccuracy.js`

Main orchestrator class that handles all frontend functionality.

```javascript
class AdaptAccuracyAnalyzer {
    constructor() {
        this.currentShopId = null;
        this.currentData = null;
        this.charts = {};
        this.allShops = [];                 // All shops for filtering
        this.comparisonData = [];           // Progressive comparison data
        this.isComparing = false;           // Comparison state lock
        
        this.baseUrl = window.CONTROLLER_URL;
        this.init();
    }
}
```

**Key Methods:**
- `init()`: Initialize event listeners and load initial data
- `loadShops()`: Fetch and populate shop dropdown with search capability
- `filterShops(searchTerm)`: Real-time shop filtering
- `analyzeShop(shopId)`: Perform single shop analysis
- `compareAllShopsProgressive()`: Progressive multi-shop comparison with progress bar
- `analyzeSingleShopForComparison(shopId)`: Individual shop analysis for comparison
- `generateComparisonReport(shopData)`: Frontend aggregation of comparison data
- `updateProgress()`: Real-time progress bar updates
- `createCharts()`: Generate Chart.js visualizations
- `populateProductTable()`: Fill product details table

### 2. Event Management System

```javascript
setupEventListeners() {
    // Shop selection
    document.getElementById('shopSelect').addEventListener('change', (e) => {
        this.currentShopId = e.target.value;
        document.getElementById('analyzeBtn').disabled = !e.target.value;
    });

    // Analysis buttons
    document.getElementById('analyzeBtn').addEventListener('click', () => {
        if (this.currentShopId) this.analyzeShop(this.currentShopId);
    });

    document.getElementById('compareAllBtn').addEventListener('click', () => {
        this.compareAllShops();
    });

    // Product table interactions
    this.setupProductTableEvents();
    this.setupModalEvents();
    this.setupFilterEvents();
}
```

---

## 📊 Data Visualization Components

### Chart Configuration System

```javascript
createCharts(analysisData) {
    this.charts = {};
    
    // Accuracy Comparison Chart
    this.charts.accuracy = this.createAccuracyChart(analysisData);
    
    // Hit Rate Distribution
    this.charts.hitRate = this.createHitRateChart(analysisData);
    
    // Scatter Plot (Predicted vs Actual)
    this.charts.scatter = this.createScatterChart(analysisData);
}

createAccuracyChart(data) {
    const ctx = document.getElementById('accuracyChart').getContext('2d');
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Adapt 1', 'Adapt 2', 'Adapt 3'],
            datasets: [
                {
                    label: 'Original Accuracy',
                    data: [
                        data.summary_stats.adapt_1.avg_accuracy,
                        data.summary_stats.adapt_2.avg_accuracy,
                        data.summary_stats.adapt_3.avg_accuracy
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Autopilot Accuracy',
                    data: [
                        data.summary_stats.autopilot_adapt_1.avg_accuracy,
                        data.summary_stats.autopilot_adapt_2.avg_accuracy,
                        data.summary_stats.autopilot_adapt_3.avg_accuracy
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                        }
                    }
                }
            }
        }
    });
}
```

### Chart Types and Purposes

| Chart Type | Purpose | Data Source |
|------------|---------|-------------|
| Bar Chart | Compare accuracy across adapt fields | summary_stats |
| Doughnut | Hit rate distribution by category | categories count |
| Scatter Plot | Predicted vs Actual correlation | products array |
| Line Chart | Shop comparison ranking | comparison data |

---

## 🎨 UI Components Architecture

### 1. Summary Cards System

```javascript
updateSummaryCards(analysisData) {
    const stats = analysisData.summary_stats;
    
    // Original accuracy cards
    document.getElementById('totalProducts').textContent = stats.total_products;
    document.getElementById('adapt1Accuracy').textContent = stats.adapt_1.avg_accuracy.toFixed(1) + '%';
    document.getElementById('adapt2Accuracy').textContent = stats.adapt_2.avg_accuracy.toFixed(1) + '%';
    document.getElementById('adapt3Accuracy').textContent = stats.adapt_3.avg_accuracy.toFixed(1) + '%';
    
    // Autopilot accuracy cards
    this.updateAutopilotAccuracyCard(1, stats.autopilot_adapt_1);
    this.updateAutopilotAccuracyCard(2, stats.autopilot_adapt_2);
    this.updateAutopilotAccuracyCard(3, stats.autopilot_adapt_3);
    
    // Shop info card
    this.updateShopInfoCard(analysisData.shop_info);
}

updateAutopilotAccuracyCard(adaptNum, accuracyData) {
    const accuracyEl = document.getElementById(`autopilotAdapt${adaptNum}Accuracy`);
    const statsEl = document.getElementById(`autopilotAdapt${adaptNum}Stats`);
    
    accuracyEl.textContent = accuracyData.avg_accuracy.toFixed(1) + '%';
    statsEl.textContent = `${accuracyData.total} produkter, ${accuracyData.accurate} præcise`;
    
    // Color coding based on improvement
    const improvement = accuracyData.avg_accuracy - accuracyData.original_avg_accuracy;
    if (improvement > 0) {
        accuracyEl.style.color = '#28a745'; // Green for improvement
    } else if (improvement < 0) {
        accuracyEl.style.color = '#dc3545'; // Red for degradation
    }
}
```

### 2. Dynamic Table System

```javascript
populateProductTable(products) {
    const tbody = document.getElementById('productTableBody');
    tbody.innerHTML = '';
    
    products.forEach(product => {
        const row = this.createProductRow(product);
        tbody.appendChild(row);
    });
    
    this.attachProductRowEvents();
}

createProductRow(product) {
    const row = document.createElement('tr');
    row.dataset.productId = product.id;
    row.dataset.presentId = product.present_id;
    row.dataset.modelId = product.model_id;
    
    // Determine product type and status
    const type = product.is_external > 0 ? 'Ekstern' : 'Intern';
    const status = this.getProductStatus(product);
    
    row.innerHTML = `
        <td>
            <div class="product-name">${product.present_name}</div>
            <div class="product-model">${product.model_name || 'Standard'}</div>
        </td>
        <td><span class="type-badge ${product.is_external > 0 ? 'external' : 'internal'}">${type}</span></td>
        <td><span class="status-badge ${status.class}">${status.text}</span></td>
        <td>${product.order_count}</td>
        <td>${product.quantity}</td>
        <td>${product.order_count}</td>
        <td>${this.formatAccuracyCell(product.adapt_1, product.accuracies?.adapt_1?.original)}</td>
        <td>${this.formatAccuracyCell(product.adapt_2, product.accuracies?.adapt_2?.original)}</td>
        <td>${this.formatAccuracyCell(product.adapt_3, product.accuracies?.adapt_3?.original)}</td>
        <td>${this.formatBestForecast(product.best_forecast)}</td>
    `;
    
    // Make row clickable for details modal
    row.style.cursor = 'pointer';
    row.addEventListener('click', () => this.showProductDetails(product));
    
    return row;
}

getProductStatus(product) {
    if (product.is_close == 1) {
        return { class: 'closed', text: 'Lukket' };
    } else if (product.autotopilot == 1) {
        return { class: 'autopilot', text: 'Autopilot' };
    } else {
        return { class: 'active', text: 'Aktiv' };
    }
}
```

### 3. Filter and Search System

```javascript
setupFilterEvents() {
    const searchInput = document.getElementById('productSearch');
    const accuracyFilter = document.getElementById('accuracyFilter');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    const applyFilters = () => {
        if (!this.currentAnalysisData) return;
        
        let filteredProducts = this.currentAnalysisData.products.filter(product => {
            // Search filter
            const searchTerm = searchInput.value.toLowerCase();
            const matchesSearch = !searchTerm || 
                product.present_name.toLowerCase().includes(searchTerm);
            
            // Accuracy filter
            const accuracyValue = accuracyFilter.value;
            let matchesAccuracy = true;
            if (accuracyValue) {
                const bestAccuracy = this.getBestAccuracy(product);
                matchesAccuracy = this.matchesAccuracyCategory(bestAccuracy, accuracyValue);
            }
            
            // Type filter
            const typeValue = typeFilter.value;
            let matchesType = true;
            if (typeValue === 'internal') matchesType = product.is_external == 0;
            if (typeValue === 'external') matchesType = product.is_external > 0;
            
            // Status filter
            const statusValue = statusFilter.value;
            let matchesStatus = true;
            if (statusValue === 'active') matchesStatus = product.is_close == 0 && product.autotopilot == 0;
            if (statusValue === 'closed') matchesStatus = product.is_close == 1;
            if (statusValue === 'autopilot') matchesStatus = product.autotopilot == 1;
            
            return matchesSearch && matchesAccuracy && matchesType && matchesStatus;
        });
        
        this.populateProductTable(filteredProducts);
    };
    
    // Attach event listeners with debouncing for search
    let searchTimeout;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    });
    
    accuracyFilter.addEventListener('change', applyFilters);
    typeFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
}
```

---

## 🎭 Modal System

### Product Details Modal

```javascript
showProductDetails(product) {
    const modal = document.getElementById('productModal');
    const modalTitle = document.getElementById('modalProductTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.textContent = `${product.present_name} - Detaljer`;
    
    modalBody.innerHTML = this.generateProductDetailsHTML(product);
    
    modal.style.display = 'block';
    
    // Add charts to modal if needed
    this.createProductDetailCharts(product);
}

generateProductDetailsHTML(product) {
    return `
        <div class="product-detail-grid">
            <div class="detail-section">
                <h4>📊 Grundlæggende Information</h4>
                <table class="detail-table">
                    <tr><td>Produkt ID:</td><td>${product.present_id}</td></tr>
                    <tr><td>Model ID:</td><td>${product.model_id}</td></tr>
                    <tr><td>Reserveret:</td><td>${product.quantity}</td></tr>
                    <tr><td>Faktiske ordrer:</td><td>${product.order_count}</td></tr>
                    <tr><td>Type:</td><td>${product.is_external > 0 ? 'Ekstern gave' : 'Intern gave'}</td></tr>
                    <tr><td>Status:</td><td>${this.getProductStatus(product).text}</td></tr>
                </table>
            </div>
            
            <div class="detail-section">
                <h4>🎯 Original Forudsigelser</h4>
                <table class="detail-table">
                    <tr><td>Adapt 1:</td><td>${product.adapt_1 || 'N/A'}</td></tr>
                    <tr><td>Adapt 2:</td><td>${product.adapt_2 || 'N/A'}</td></tr>
                    <tr><td>Adapt 3:</td><td>${product.adapt_3 || 'N/A'}</td></tr>
                </table>
            </div>
            
            <div class="detail-section">
                <h4>🤖 Autopilot Justeringer</h4>
                <table class="detail-table">
                    <tr>
                        <td>Adapt 1:</td>
                        <td>${product.autopilot_adapt_1 || 'N/A'}</td>
                        <td class="change ${this.getChangeClass(product.adapt_1, product.autopilot_adapt_1)}">
                            ${this.formatChange(product.adapt_1, product.autopilot_adapt_1)}
                        </td>
                    </tr>
                    <tr>
                        <td>Adapt 2:</td>
                        <td>${product.autopilot_adapt_2 || 'N/A'}</td>
                        <td class="change ${this.getChangeClass(product.adapt_2, product.autopilot_adapt_2)}">
                            ${this.formatChange(product.adapt_2, product.autopilot_adapt_2)}
                        </td>
                    </tr>
                    <tr>
                        <td>Adapt 3:</td>
                        <td>${product.autopilot_adapt_3 || 'N/A'}</td>
                        <td class="change ${this.getChangeClass(product.adapt_3, product.autopilot_adapt_3)}">
                            ${this.formatChange(product.adapt_3, product.autopilot_adapt_3)}
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="detail-section full-width">
                <h4>📈 Nøjagtighedssammenligning</h4>
                <canvas id="productDetailChart" width="400" height="200"></canvas>
            </div>
        </div>
    `;
}
```

---

## 🎨 Styling Architecture

### CSS Organization

```css
/* 1. Layout Components */
.container { /* Main container */ }
.header { /* Page header */ }
.controls-section { /* Control buttons area */ }
.analysis-section { /* Analysis results area */ }

/* 2. Card System */
.summary-cards { 
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.summary-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* 3. Adaptive Color System */
.summary-card.adapt-1 { background: linear-gradient(135deg, #ff6b6b, #ee5a24); }
.summary-card.adapt-2 { background: linear-gradient(135deg, #4ecdc4, #44a08d); }
.summary-card.adapt-3 { background: linear-gradient(135deg, #45b7d1, #96c93d); }

/* 4. Status Indicators */
.status-badge.active { color: #28a745; }
.status-badge.closed { color: #dc3545; }
.status-badge.autopilot { color: #17a2b8; }

.type-badge.internal { background: #e7f3ff; color: #0066cc; }
.type-badge.external { background: #fff3e0; color: #ff6600; }

/* 5. Shop Search and Selection */
.shop-select-container {
    width: 100%;
}

#shopSearch {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

#shopSelect {
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 6px;
}

/* 6. Progress Bar System */
.progress-container {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    text-align: center;
}

.progress-bar {
    width: 100%;
    height: 30px;
    background-color: #f0f0f0;
    border-radius: 15px;
    overflow: hidden;
    position: relative;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 15px;
}

.progress-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    text-align: left;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
}
```

### Responsive Design

```css
/* Mobile First Approach */
@media (max-width: 768px) {
    .summary-cards {
        grid-template-columns: 1fr;
    }
    
    .charts-section {
        flex-direction: column;
    }
    
    .chart-container {
        width: 100%;
        margin-bottom: 20px;
    }
    
    .table-container {
        overflow-x: auto;
    }
}

@media (max-width: 576px) {
    .control-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .control-group select,
    .control-group button {
        width: 100%;
        margin-bottom: 10px;
    }
}
```

---

## 🔄 State Management

### Application State Structure

```javascript
class AdaptAccuracyAnalyzer {
    constructor() {
        this.state = {
            currentShopId: null,
            currentAnalysisData: null,
            currentComparisonData: null,
            charts: {},
            filters: {
                search: '',
                accuracy: '',
                type: '',
                status: ''
            },
            ui: {
                loading: false,
                error: null,
                activeTab: 'single-shop'
            }
        };
    }
    
    setState(newState) {
        this.state = { ...this.state, ...newState };
        this.render();
    }
    
    render() {
        this.updateUI();
        this.updateCharts();
        this.updateTables();
    }
}
```

### Error Handling Strategy

```javascript
async apiCall(endpoint, data = null) {
    try {
        this.setState({ ui: { ...this.state.ui, loading: true, error: null } });
        
        const response = await this.makeRequest(endpoint, data);
        
        if (response.status !== 1) {
            throw new Error(response.message || 'Unknown API error');
        }
        
        this.setState({ ui: { ...this.state.ui, loading: false } });
        return response.data;
        
    } catch (error) {
        console.error(`API Error in ${endpoint}:`, error);
        this.setState({ 
            ui: { 
                ...this.state.ui, 
                loading: false, 
                error: error.message 
            } 
        });
        this.showError(error.message);
        throw error;
    }
}

showError(message) {
    const errorEl = document.getElementById('errorMessage');
    errorEl.textContent = message;
    errorEl.style.display = 'block';
    
    setTimeout(() => {
        errorEl.style.display = 'none';
    }, 5000);
}
```

---

## 🚀 Performance Optimizations

### Lazy Loading and Debouncing

```javascript
// Chart lazy loading
createChartsLazy(data) {
    // Only create charts when they're visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const chartId = entry.target.id;
                if (!this.charts[chartId]) {
                    this.createChart(chartId, data);
                }
            }
        });
    });
    
    document.querySelectorAll('.chart-container canvas').forEach(canvas => {
        observer.observe(canvas);
    });
}

// Search debouncing
setupDebouncedSearch() {
    let searchTimeout;
    document.getElementById('productSearch').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            this.applyFilters();
        }, 300);
    });
}
```

### Memory Management

```javascript
cleanup() {
    // Destroy existing charts
    Object.values(this.charts).forEach(chart => {
        if (chart && typeof chart.destroy === 'function') {
            chart.destroy();
        }
    });
    this.charts = {};
    
    // Remove event listeners
    this.removeEventListeners();
    
    // Clear data references
    this.currentAnalysisData = null;
    this.currentComparisonData = null;
}
```