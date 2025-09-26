<?php
// cardsale_layout_no_charts.php - Backup version uden Chart.js
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Card Sale Statistics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .stats-container {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px 0;
        }

        .country-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .country-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .tab-content {
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stat-title {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .nav-tabs .nav-link {
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
            border: none;
            background: #f8f9fa;
            color: #495057;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            background: #007bff;
            color: white;
            border: none;
        }

        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .comparison-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }

        .growth-indicator {
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .growth-positive {
            color: #28a745;
        }

        .growth-negative {
            color: #dc3545;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .detailed-table {
            margin-top: 20px;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            text-align: center;
        }

        .dashboard-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .dashboard-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .chart-placeholder {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            color: #6c757d;
            margin-bottom: 20px;
            border: 2px dashed #dee2e6;
        }

        .chart-placeholder h5 {
            margin-bottom: 10px;
            color: #495057;
        }

        .stats-summary {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="stats-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1 class="dashboard-title">📊 Card Sale Statistics</h1>
            <p class="dashboard-subtitle">Oversigt over kortsalg på tværs af Danmark, Norge og Sverige</p>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Navigation Tabs -->
        <div class="row mb-4">
            <div class="col-12">
                <ul class="nav nav-tabs justify-content-center" id="countryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="denmark-tab" data-bs-toggle="tab" data-bs-target="#denmark" type="button" role="tab">
                            🇩🇰 Danmark
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="norway-tab" data-bs-toggle="tab" data-bs-target="#norway" type="button" role="tab">
                            🇳🇴 Norge
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sweden-tab" data-bs-toggle="tab" data-bs-target="#sweden" type="button" role="tab">
                            🇸🇪 Sverige
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="comparison-tab" data-bs-toggle="tab" data-bs-target="#comparison" type="button" role="tab">
                            📊 Sammenligning
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="countryTabContent">
            <!-- Denmark Tab -->
            <div class="tab-pane fade show active" id="denmark" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="country-section">
                            <h2 class="country-header">🇩🇰 Danske Shops</h2>
                            <div class="tab-content">
                                <div id="denmark-stats" class="loading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Indlæser...</span>
                                    </div>
                                    <p class="mt-3">Indlæser data...</p>
                                </div>

                                <div id="denmark-summary" class="stats-summary" style="display:none;">
                                    <h5>Sammenfatning - Danmark</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Total 2025:</strong> <span id="dk-total-2025">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Til dato 2025:</strong> <span id="dk-today-2025">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Total 2024:</strong> <span id="dk-total-2024">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Vækst:</strong> <span id="dk-growth">0%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-grid">
                                    <div class="chart-placeholder">
                                        <h5>📊 Årlig sammenligning</h5>
                                        <p>Grafer vil blive tilføjet senere</p>
                                        <small>Chart.js integration under udvikling</small>
                                    </div>
                                    <div class="chart-placeholder">
                                        <h5>📈 Til og med samme dag</h5>
                                        <p>Trend analyse kommer snart</p>
                                        <small>Data visualisering under forberedelse</small>
                                    </div>
                                </div>

                                <div class="detailed-table">
                                    <h5>Detaljeret oversigt - Danmark</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="denmark-detailed-table">
                                            <thead>
                                            <tr>
                                                <th>Shop</th>
                                                <th>2025 Total</th>
                                                <th>2025 Til dato</th>
                                                <th>2024 Total</th>
                                                <th>2024 Til dato</th>
                                                <th>Vækst %</th>
                                            </tr>
                                            </thead>
                                            <tbody id="denmark-table-body">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Norway Tab -->
            <div class="tab-pane fade" id="norway" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="country-section">
                            <h2 class="country-header">🇳🇴 Norske Shops</h2>
                            <div class="tab-content">
                                <div id="norway-stats" class="loading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Indlæser...</span>
                                    </div>
                                    <p class="mt-3">Indlæser data...</p>
                                </div>

                                <div id="norway-summary" class="stats-summary" style="display:none;">
                                    <h5>Sammenfatning - Norge</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Total 2025:</strong> <span id="no-total-2025">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Til dato 2025:</strong> <span id="no-today-2025">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Total 2024:</strong> <span id="no-total-2024">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Vækst:</strong> <span id="no-growth">0%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-grid">
                                    <div class="chart-placeholder">
                                        <h5>📊 Årlig sammenligning</h5>
                                        <p>Grafer vil blive tilføjet senere</p>
                                    </div>
                                    <div class="chart-placeholder">
                                        <h5>📈 Til og med samme dag</h5>
                                        <p>Trend analyse kommer snart</p>
                                    </div>
                                </div>

                                <div class="detailed-table">
                                    <h5>Detaljeret oversigt - Norge</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>Shop</th>
                                                <th>2025 Total</th>
                                                <th>2025 Til dato</th>
                                                <th>2024 Total</th>
                                                <th>2024 Til dato</th>
                                                <th>Vækst %</th>
                                            </tr>
                                            </thead>
                                            <tbody id="norway-table-body">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sweden Tab -->
            <div class="tab-pane fade" id="sweden" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="country-section">
                            <h2 class="country-header">🇸🇪 Svenske Shops</h2>
                            <div class="tab-content">
                                <div id="sweden-stats" class="loading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Indlæser...</span>
                                    </div>
                                    <p class="mt-3">Indlæser data...</p>
                                </div>

                                <div id="sweden-summary" class="stats-summary" style="display:none;">
                                    <h5>Sammenfatning - Sverige</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Total 2025:</strong> <span id="se-total-2025">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Til dato 2025:</strong> <span id="se-today-2025">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Total 2024:</strong> <span id="se-total-2024">0</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Vækst:</strong> <span id="se-growth">0%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-grid">
                                    <div class="chart-placeholder">
                                        <h5>📊 Årlig sammenligning</h5>
                                        <p>Grafer vil blive tilføjet senere</p>
                                    </div>
                                    <div class="chart-placeholder">
                                        <h5>📈 Til og med samme dag</h5>
                                        <p>Trend analyse kommer snart</p>
                                    </div>
                                </div>

                                <div class="detailed-table">
                                    <h5>Detaljeret oversigt - Sverige</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>Shop</th>
                                                <th>2025 Total</th>
                                                <th>2025 Til dato</th>
                                                <th>2024 Total</th>
                                                <th>2024 Til dato</th>
                                                <th>Vækst %</th>
                                            </tr>
                                            </thead>
                                            <tbody id="sweden-table-body">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comparison Tab -->
            <div class="tab-pane fade" id="comparison" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <div class="country-section">
                            <h2 class="country-header">📊 Sammenligning mellem lande</h2>
                            <div class="tab-content">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="chart-placeholder">
                                            <h5>🌍 Total salg 2025</h5>
                                            <p>Sammenligning mellem alle lande</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="chart-placeholder">
                                            <h5>📅 Til og med samme dag 2025</h5>
                                            <p>Cross-country progress tracking</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="detailed-table">
                                            <h5>Sammenligning - Alle lande</h5>
                                            <div class="table-responsive">
                                                <table class="table table-striped" id="comparison-table">
                                                    <thead>
                                                    <tr>
                                                        <th>Land</th>
                                                        <th>2025 Total</th>
                                                        <th>2025 Til dato</th>
                                                        <th>2024 Total</th>
                                                        <th>2024 Til dato</th>
                                                        <th>Vækst % (Total)</th>
                                                        <th>Vækst % (Til dato)</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="comparison-table-body">
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
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

<script>
    // Global data storage
    let salesData = {
        denmark: {},
        norway: {},
        sweden: {}
    };

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Dashboard initialized without charts');
    });

    function updateSalesData(country, data) {
        salesData[country] = data;
        updateCountrySummary(country, data);
        updateComparisonTable();
    }

    function updateCountrySummary(country, data) {
        const prefix = country === 'denmark' ? 'dk' : (country === 'norway' ? 'no' : 'se');

        const totalElement = document.getElementById(prefix + '-total-2025');
        const todayElement = document.getElementById(prefix + '-today-2025');
        const total2024Element = document.getElementById(prefix + '-total-2024');
        const growthElement = document.getElementById(prefix + '-growth');

        if (totalElement) totalElement.textContent = formatNumber(data.total_2025 || 0);
        if (todayElement) todayElement.textContent = formatNumber(data.total_day_2025 || 0);
        if (total2024Element) total2024Element.textContent = formatNumber(data.total_2024 || 0);

        if (growthElement) {
            const growth = calculateGrowthPercent(data.total_day_2025, data.total_day_2024);
            growthElement.textContent = growth;
            growthElement.className = getGrowthClass(data.total_day_2025, data.total_day_2024);
        }

        // Show summary
        const summaryElement = document.getElementById(country + '-summary');
        if (summaryElement) {
            summaryElement.style.display = 'block';
        }
    }

    function displayStatsGrid(containerId, data) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const statsHtml = `
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-title">Total 2025</div>
                <div class="stat-value">${formatNumber(data.total_2025 || 0)}</div>
                <div class="growth-indicator ${getGrowthClass(data.total_2025, data.total_2024)}">
                    ${getGrowthText(data.total_2025, data.total_2024)}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-title">I dag 2025</div>
                <div class="stat-value">${formatNumber(data.day_2025 || 0)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Måned 2025</div>
                <div class="stat-value">${formatNumber(data.month_2025 || 0)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Til samme dag 2025</div>
                <div class="stat-value">${formatNumber(data.total_day_2025 || 0)}</div>
                <div class="growth-indicator ${getGrowthClass(data.total_day_2025, data.total_day_2024)}">
                    ${getGrowthText(data.total_day_2025, data.total_day_2024)}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total 2024</div>
                <div class="stat-value">${formatNumber(data.total_2024 || 0)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Til samme dag 2024</div>
                <div class="stat-value">${formatNumber(data.total_day_2024 || 0)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Total 2023</div>
                <div class="stat-value">${formatNumber(data.total_2023 || 0)}</div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Til samme dag 2023</div>
                <div class="stat-value">${formatNumber(data.total_day_2023 || 0)}</div>
            </div>
        </div>
    `;

        container.innerHTML = statsHtml;
    }

    function updateComparisonTable() {
        const tableBody = document.getElementById('comparison-table-body');
        if (!tableBody) return;

        const countries = [
            { name: 'Danmark', flag: '🇩🇰', data: salesData.denmark },
            { name: 'Norge', flag: '🇳🇴', data: salesData.norway },
            { name: 'Sverige', flag: '🇸🇪', data: salesData.sweden }
        ];

        let html = '';
        countries.forEach(country => {
            const data = country.data;
            const totalGrowth = calculateGrowthPercent(data.total_2025, data.total_2024);
            const dayGrowth = calculateGrowthPercent(data.total_day_2025, data.total_day_2024);

            html += `
            <tr>
                <td>${country.flag} ${country.name}</td>
                <td>${formatNumber(data.total_2025 || 0)}</td>
                <td>${formatNumber(data.total_day_2025 || 0)}</td>
                <td>${formatNumber(data.total_2024 || 0)}</td>
                <td>${formatNumber(data.total_day_2024 || 0)}</td>
                <td class="${getGrowthClass(data.total_2025, data.total_2024)}">${totalGrowth}</td>
                <td class="${getGrowthClass(data.total_day_2025, data.total_day_2024)}">${dayGrowth}</td>
            </tr>
        `;
        });

        tableBody.innerHTML = html;
    }

    function formatNumber(num) {
        if (num === 0 || num === null || num === undefined) return '0';
        return new Intl.NumberFormat('da-DK').format(num);
    }

    function getGrowthClass(current, previous) {
        if (!current || !previous) return '';
        return current > previous ? 'growth-positive' : 'growth-negative';
    }

    function getGrowthText(current, previous) {
        if (!current || !previous) return '';
        const diff = current - previous;
        const percent = ((diff / previous) * 100).toFixed(1);
        const arrow = diff > 0 ? '↗' : '↘';
        return `${arrow} ${Math.abs(percent)}%`;
    }

    function calculateGrowthPercent(current, previous) {
        if (!current || !previous) return 'N/A';
        const diff = current - previous;
        const percent = ((diff / previous) * 100).toFixed(1);
        const arrow = diff > 0 ? '↗' : '↘';
        return `${arrow} ${Math.abs(percent)}%`;
    }

    // Function to be called from PHP
    function loadCountryData(country, data) {
        updateSalesData(country, data);
        displayStatsGrid(`${country}-stats`, data);
    }
</script>