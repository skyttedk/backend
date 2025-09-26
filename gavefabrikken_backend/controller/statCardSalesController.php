<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gavekort Salgs Dashboard 2025</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 30px;
        }

        .stats-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .metric-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            border-color: var(--secondary-color);
            transform: scale(1.02);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .metric-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
            margin: 20px 0;
        }

        .chart-wrapper {
            position: relative;
            height: 350px;
            width: 100%;
        }

        canvas {
            max-height: 350px !important;
        }

        .country-section {
            margin-bottom: 40px;
        }

        .country-header {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .country-flag {
            width: 40px;
            height: 30px;
            border-radius: 5px;
        }

        .year-tabs {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
        }

        .year-tab {
            background: transparent;
            border: 2px solid transparent;
            color: #666;
            border-radius: 8px;
            padding: 10px 20px;
            margin: 0 5px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .year-tab.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }

        .year-tab:hover {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }

        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--secondary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .data-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
        }

        .table td {
            padding: 12px 15px;
            border-color: #eee;
        }

        .comparison-chart {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .filter-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .export-btn {
            background: linear-gradient(135deg, var(--success-color) 0%, #229954 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Original Layout Styling */
        .original-layout-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .flex-container-original {
            display: flex;
            flex-wrap: nowrap;
            overflow: auto;
            gap: 10px;
        }

        .v-flex-original {
            min-width: 200px;
            flex: 1;
        }

        .v-flex-original > div {
            text-align: center;
            padding: 8px;
            font-size: 13px;
            margin: 2px 0;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .v-flex-original > div:nth-child(even) {
            background-color: #f8f9fa;
        }

        .v-flex-original > div:hover {
            background-color: #e3f2fd;
            transform: scale(1.02);
        }

        .header-title-original {
            font-size: 14px !important;
            font-weight: bold;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
            color: white !important;
            padding: 12px 8px !important;
            border-radius: 8px !important;
            margin-bottom: 5px !important;
        }

        .concept-label {
            color: var(--secondary-color);
            font-weight: bold;
            font-size: 12px;
        }

        .total-row {
            background: var(--success-color) !important;
            color: white !important;
            font-weight: bold !important;
        }

        @media (max-width: 768px) {
            .flex-container-original {
                flex-direction: column;
            }

            .v-flex-original {
                min-width: 100%;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="dashboard-container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-chart-line text-primary"></i>
                    Gavekort Salgs Dashboard 2025
                </h1>

                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label class="form-label">Vælg tidsperiode:</label>
                            <select id="timeFilter" class="form-select">
                                <option value="all">Alle tidsperioder</option>
                                <option value="today">I dag</option>
                                <option value="month">Denne måned</option>
                                <option value="year">Dette år</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sammenlign med:</label>
                            <select id="compareFilter" class="form-select">
                                <option value="none">Ingen sammenligning</option>
                                <option value="previous_year">Samme periode sidste år</option>
                                <option value="previous_month">Forrige måned</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="export-buttons">
                                <button class="btn export-btn" onclick="exportData('csv')">
                                    <i class="fas fa-download"></i> CSV
                                </button>
                                <button class="btn export-btn" onclick="exportData('pdf')">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-value text-primary" id="totalSales">0</div>
                    <div class="metric-label">
                        <i class="fas fa-chart-line"></i> Total Salg
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-value text-success" id="todaySales">0</div>
                    <div class="metric-label">
                        <i class="fas fa-calendar-day"></i> Salg I Dag
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-value text-warning" id="monthSales">0</div>
                    <div class="metric-label">
                        <i class="fas fa-calendar-alt"></i> Denne Måned
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="metric-card">
                    <div class="metric-value text-info" id="growthRate">0%</div>
                    <div class="metric-label">
                        <i class="fas fa-trending-up"></i> Vækstrate
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Charts Section -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="comparison-chart">
                    <h4 class="mb-3">
                        <i class="fas fa-chart-area text-primary"></i>
                        Salgs Trend Sammenligning
                    </h4>
                    <div class="chart-wrapper">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="comparison-chart">
                    <h4 class="mb-3">
                        <i class="fas fa-chart-pie text-success"></i>
                        Lande Fordeling
                    </h4>
                    <div class="chart-wrapper">
                        <canvas id="countryDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Country Sections -->
        <div id="countrySections">
            <!-- Danmark Section -->
            <div class="country-section" data-country="dk">
                <div class="country-header">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 37 28'%3E%3Crect width='37' height='28' fill='%23c8102e'/%3E%3Crect x='12' y='0' width='5' height='28' fill='white'/%3E%3Crect x='0' y='11.5' width='37' height='5' fill='white'/%3E%3C/svg%3E" alt="DK" class="country-flag">
                    <h3>Danmark</h3>
                    <div class="ms-auto">
                        <span class="badge bg-light text-dark" id="dk-total">0 kort solgt</span>
                    </div>
                </div>

                <div class="year-tabs">
                    <button class="year-tab active" data-year="2025" data-country="dk">2025</button>
                    <button class="year-tab" data-year="2024" data-country="dk">2024</button>
                    <button class="year-tab" data-year="2023" data-country="dk">2023</button>
                    <button class="year-tab" data-year="2022" data-country="dk">2022</button>
                </div>

                <!-- Original Layout Style Flexbox Container -->
                <div class="original-layout-container mb-4" id="dk-original-layout">
                    <div class="flex-container-original">
                        <div class="v-flex-original">
                            <div class="header-title-original">Shop</div>
                            <div id="dk-shop-labels"></div>
                            <div><b>TOTAL</b></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte totalt <span id="dk-year-label">2025</span></div>
                            <div id="dk-total-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte i dag <span id="dk-year-label-day">2025</span></div>
                            <div id="dk-day-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte indeværende måned <span id="dk-year-label-month">2025</span></div>
                            <div id="dk-month-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte totalt til og med samme dag <span id="dk-year-label-total-day">2025</span></div>
                            <div id="dk-total-day-values"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar"></i> Shop Sammenligning</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-wrapper">
                                    <canvas id="dkChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line"></i> År Sammenligning</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-wrapper">
                                    <canvas id="dkYearChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Norge Section -->
            <div class="country-section" data-country="no">
                <div class="country-header">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 22 16'%3E%3Crect width='22' height='16' fill='%23ef2b2d'/%3E%3Crect x='6' y='0' width='2' height='16' fill='white'/%3E%3Crect x='0' y='6' width='22' height='4' fill='white'/%3E%3Crect x='7' y='0' width='1' height='16' fill='%23002868'/%3E%3Crect x='0' y='7' width='22' height='2' fill='%23002868'/%3E%3C/svg%3E" alt="NO" class="country-flag">
                    <h3>Norge</h3>
                    <div class="ms-auto">
                        <span class="badge bg-light text-dark" id="no-total">0 kort solgt</span>
                    </div>
                </div>

                <div class="year-tabs">
                    <button class="year-tab active" data-year="2025" data-country="no">2025</button>
                    <button class="year-tab" data-year="2024" data-country="no">2024</button>
                    <button class="year-tab" data-year="2023" data-country="no">2023</button>
                    <button class="year-tab" data-year="2022" data-country="no">2022</button>
                </div>

                <!-- Original Layout Style Flexbox Container -->
                <div class="original-layout-container mb-4" id="no-original-layout">
                    <div class="flex-container-original">
                        <div class="v-flex-original">
                            <div class="header-title-original">Shop</div>
                            <div id="no-shop-labels"></div>
                            <div><b>TOTAL</b></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte totalt <span id="no-year-label">2025</span></div>
                            <div id="no-total-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte i dag <span id="no-year-label-day">2025</span></div>
                            <div id="no-day-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte indeværende måned <span id="no-year-label-month">2025</span></div>
                            <div id="no-month-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte totalt til og med samme dag <span id="no-year-label-total-day">2025</span></div>
                            <div id="no-total-day-values"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar"></i> Shop Sammenligning</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-wrapper">
                                    <canvas id="noChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line"></i> År Sammenligning</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-wrapper">
                                    <canvas id="noYearChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sverige Section -->
            <div class="country-section" data-country="se">
                <div class="country-header">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 10'%3E%3Crect width='16' height='10' fill='%23006aa7'/%3E%3Crect x='5' y='0' width='2' height='10' fill='%23fecc00'/%3E%3Crect x='0' y='4' width='16' height='2' fill='%23fecc00'/%3E%3C/svg%3E" alt="SE" class="country-flag">
                    <h3>Sverige</h3>
                    <div class="ms-auto">
                        <span class="badge bg-light text-dark" id="se-total">0 kort solgt</span>
                    </div>
                </div>

                <div class="year-tabs">
                    <button class="year-tab active" data-year="2025" data-country="se">2025</button>
                    <button class="year-tab" data-year="2024" data-country="se">2024</button>
                    <button class="year-tab" data-year="2023" data-country="se">2023</button>
                    <button class="year-tab" data-year="2022" data-country="se">2022</button>
                </div>

                <!-- Original Layout Style Flexbox Container -->
                <div class="original-layout-container mb-4" id="se-original-layout">
                    <div class="flex-container-original">
                        <div class="v-flex-original">
                            <div class="header-title-original">Shop</div>
                            <div id="se-shop-labels"></div>
                            <div><b>TOTAL</b></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte totalt <span id="se-year-label">2025</span></div>
                            <div id="se-total-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte i dag <span id="se-year-label-day">2025</span></div>
                            <div id="se-day-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte indeværende måned <span id="se-year-label-month">2025</span></div>
                            <div id="se-month-values"></div>
                        </div>
                        <div class="v-flex-original">
                            <div class="header-title-original">Antal solgte totalt til og med samme dag <span id="se-year-label-total-day">2025</span></div>
                            <div id="se-total-day-values"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-bar"></i> Shop Sammenligning</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-wrapper">
                                    <canvas id="seChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="stats-card">
                            <div class="card-header">
                                <h5><i class="fas fa-chart-line"></i> År Sammenligning</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-wrapper">
                                    <canvas id="seYearChart"></canvas>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Global variables
    let salesData = {};
    let charts = {};
    let debugMode = true; // Set to false in production

    function debugLog(message, data = null) {
        if (debugMode) {
            console.log(`[DEBUG] ${message}`, data || '');
        }
    }

    // Mock data structure (complete with ALL concepts from original PHP)
    const mockData = {
        dk: {
            2025: {
                total: {
                    shops: {
                        'JGV': 1250,
                        'JK5-560': 987,
                        'JK7-720': 756,
                        'G8-800': 1100,
                        'G9-1040': 890,
                        '24G-400': 1050,
                        '24G-560': 920,
                        '24G-640': 1080,
                        'Drøm-200': 680,
                        'Drøm-300': 750,
                        'DESIGN-640': 620,
                        'DESIGN-960': 540,
                        'GRØN(Udgået)': 150,
                        'L1-': 230,
                        'L2-': 190,
                        'L4-': 210,
                        'L6-': 180,
                        'L8-': 200
                    },
                    total: 10833
                },
                day: {
                    shops: {
                        'JGV': 45,
                        'JK5-560': 32,
                        'JK7-720': 28,
                        'G8-800': 41,
                        'G9-1040': 35,
                        '24G-400': 38,
                        '24G-560': 33,
                        '24G-640': 40,
                        'Drøm-200': 25,
                        'Drøm-300': 28,
                        'DESIGN-640': 22,
                        'DESIGN-960': 19,
                        'GRØN(Udgået)': 5,
                        'L1-': 8,
                        'L2-': 7,
                        'L4-': 9,
                        'L6-': 6,
                        'L8-': 8
                    },
                    total: 430
                },
                month: {
                    shops: {
                        'JGV': 350,
                        'JK5-560': 287,
                        'JK7-720': 256,
                        'G8-800': 310,
                        'G9-1040': 290,
                        '24G-400': 305,
                        '24G-560': 275,
                        '24G-640': 320,
                        'Drøm-200': 180,
                        'Drøm-300': 195,
                        'DESIGN-640': 165,
                        'DESIGN-960': 140,
                        'GRØN(Udgået)': 45,
                        'L1-': 65,
                        'L2-': 55,
                        'L4-': 70,
                        'L6-': 50,
                        'L8-': 60
                    },
                    total: 3278
                },
                total_day: {
                    shops: {
                        'JGV': 950,
                        'JK5-560': 750,
                        'JK7-720': 576,
                        'G8-800': 840,
                        'G9-1040': 680,
                        '24G-400': 800,
                        '24G-560': 700,
                        '24G-640': 825,
                        'Drøm-200': 520,
                        'Drøm-300': 575,
                        'DESIGN-640': 475,
                        'DESIGN-960': 415,
                        'GRØN(Udgået)': 115,
                        'L1-': 175,
                        'L2-': 145,
                        'L4-': 160,
                        'L6-': 135,
                        'L8-': 155
                    },
                    total: 8296
                }
            },
            2024: {
                total: {
                    shops: {
                        'JGV': 1150,
                        'JK5-560': 887,
                        'JK7-720': 656,
                        'G8-800': 1000,
                        'G9-1040': 790,
                        '24G-400': 950,
                        '24G-560': 820,
                        '24G-640': 980,
                        'Drøm-200': 630,
                        'Drøm-300': 695,
                        'DESIGN-640': 570,
                        'DESIGN-960': 490,
                        'GRØN(Udgået)': 135,
                        'L1-': 210,
                        'L2-': 175,
                        'L4-': 195,
                        'L6-': 165,
                        'L8-': 185
                    },
                    total: 9883
                },
                day: {
                    shops: {
                        'JGV': 42,
                        'JK5-560': 30,
                        'JK7-720': 26,
                        'G8-800': 38,
                        'G9-1040': 32,
                        '24G-400': 35,
                        '24G-560': 30,
                        '24G-640': 37,
                        'Drøm-200': 23,
                        'Drøm-300': 26,
                        'DESIGN-640': 20,
                        'DESIGN-960': 17,
                        'GRØN(Udgået)': 4,
                        'L1-': 7,
                        'L2-': 6,
                        'L4-': 8,
                        'L6-': 5,
                        'L8-': 7
                    },
                    total: 393
                },
                month: {
                    shops: {
                        'JGV': 320,
                        'JK5-560': 267,
                        'JK7-720': 236,
                        'G8-800': 290,
                        'G9-1040': 270,
                        '24G-400': 285,
                        '24G-560': 255,
                        '24G-640': 300,
                        'Drøm-200': 165,
                        'Drøm-300': 180,
                        'DESIGN-640': 150,
                        'DESIGN-960': 125,
                        'GRØN(Udgået)': 40,
                        'L1-': 60,
                        'L2-': 50,
                        'L4-': 65,
                        'L6-': 45,
                        'L8-': 55
                    },
                    total: 2998
                },
                total_day: {
                    shops: {
                        'JGV': 850,
                        'JK5-560': 650,
                        'JK7-720': 476,
                        'G8-800': 740,
                        'G9-1040': 580,
                        '24G-400': 700,
                        '24G-560': 600,
                        '24G-640': 725,
                        'Drøm-200': 465,
                        'Drøm-300': 520,
                        'DESIGN-640': 420,
                        'DESIGN-960': 365,
                        'GRØN(Udgået)': 100,
                        'L1-': 155,
                        'L2-': 130,
                        'L4-': 145,
                        'L6-': 120,
                        'L8-': 140
                    },
                    total: 7461
                }
            },
            2023: {
                total: {
                    shops: {
                        'JGV': 1050,
                        'JK5-560': 787,
                        'JK7-720': 556,
                        'G8-800': 900,
                        'G9-1040': 690,
                        '24G-400': 850,
                        '24G-560': 720,
                        '24G-640': 880,
                        'Drøm-200': 530,
                        'Drøm-300': 595,
                        'DESIGN-640': 470,
                        'DESIGN-960': 390,
                        'GRØN(Udgået)': 115,
                        'L1-': 190,
                        'L2-': 155,
                        'L4-': 175,
                        'L6-': 145,
                        'L8-': 165
                    },
                    total: 8863
                },
                day: {
                    shops: {
                        'JGV': 39,
                        'JK5-560': 27,
                        'JK7-720': 23,
                        'G8-800': 35,
                        'G9-1040': 29,
                        '24G-400': 32,
                        '24G-560': 27,
                        '24G-640': 34,
                        'Drøm-200': 20,
                        'Drøm-300': 23,
                        'DESIGN-640': 17,
                        'DESIGN-960': 14,
                        'GRØN(Udgået)': 3,
                        'L1-': 6,
                        'L2-': 5,
                        'L4-': 7,
                        'L6-': 4,
                        'L8-': 6
                    },
                    total: 351
                },
                month: {
                    shops: {
                        'JGV': 290,
                        'JK5-560': 237,
                        'JK7-720': 206,
                        'G8-800': 260,
                        'G9-1040': 240,
                        '24G-400': 255,
                        '24G-560': 225,
                        '24G-640': 270,
                        'Drøm-200': 135,
                        'Drøm-300': 150,
                        'DESIGN-640': 120,
                        'DESIGN-960': 95,
                        'GRØN(Udgået)': 30,
                        'L1-': 50,
                        'L2-': 40,
                        'L4-': 55,
                        'L6-': 35,
                        'L8-': 45
                    },
                    total: 2688
                },
                total_day: {
                    shops: {
                        'JGV': 750,
                        'JK5-560': 550,
                        'JK7-720': 376,
                        'G8-800': 640,
                        'G9-1040': 480,
                        '24G-400': 600,
                        '24G-560': 500,
                        '24G-640': 625,
                        'Drøm-200': 365,
                        'Drøm-300': 420,
                        'DESIGN-640': 320,
                        'DESIGN-960': 265,
                        'GRØN(Udgået)': 80,
                        'L1-': 135,
                        'L2-': 110,
                        'L4-': 125,
                        'L6-': 100,
                        'L8-': 120
                    },
                    total: 6561
                }
            },
            2022: {
                total: {
                    shops: {
                        'JGV': 950,
                        'JK5-560': 687,
                        'JK7-720': 456,
                        'G8-800': 800,
                        'G9-1040': 590,
                        '24G-400': 750,
                        '24G-560': 620,
                        '24G-640': 780,
                        'Drøm-200': 430,
                        'Drøm-300': 495,
                        'DESIGN-640': 370,
                        'DESIGN-960': 290,
                        'GRØN(Udgået)': 95,
                        'L1-': 170,
                        'L2-': 135,
                        'L4-': 155,
                        'L6-': 125,
                        'L8-': 145
                    },
                    total: 7643
                },
                day: {
                    shops: {
                        'JGV': 36,
                        'JK5-560': 24,
                        'JK7-720': 20,
                        'G8-800': 32,
                        'G9-1040': 26,
                        '24G-400': 29,
                        '24G-560': 24,
                        '24G-640': 31,
                        'Drøm-200': 17,
                        'Drøm-300': 20,
                        'DESIGN-640': 14,
                        'DESIGN-960': 11,
                        'GRØN(Udgået)': 2,
                        'L1-': 5,
                        'L2-': 4,
                        'L4-': 6,
                        'L6-': 3,
                        'L8-': 5
                    },
                    total: 309
                },
                month: {
                    shops: {
                        'JGV': 260,
                        'JK5-560': 207,
                        'JK7-720': 176,
                        'G8-800': 230,
                        'G9-1040': 210,
                        '24G-400': 225,
                        '24G-560': 195,
                        '24G-640': 240,
                        'Drøm-200': 105,
                        'Drøm-300': 120,
                        'DESIGN-640': 90,
                        'DESIGN-960': 65,
                        'GRØN(Udgået)': 20,
                        'L1-': 40,
                        'L2-': 30,
                        'L4-': 45,
                        'L6-': 25,
                        'L8-': 35
                    },
                    total: 2318
                },
                total_day: {
                    shops: {
                        'JGV': 650,
                        'JK5-560': 450,
                        'JK7-720': 276,
                        'G8-800': 540,
                        'G9-1040': 380,
                        '24G-400': 500,
                        '24G-560': 400,
                        '24G-640': 525,
                        'Drøm-200': 265,
                        'Drøm-300': 320,
                        'DESIGN-640': 220,
                        'DESIGN-960': 165,
                        'GRØN(Udgået)': 60,
                        'L1-': 115,
                        'L2-': 90,
                        'L4-': 105,
                        'L6-': 80,
                        'L8-': 100
                    },
                    total: 5241
                }
            }
        },
        no: {
            2025: {
                total: {
                    shops: {
                        'NO-Jgk-300': 450,
                        'NO-Jgk-400': 380,
                        'NO-Jgk-600': 520,
                        'NO-Jgk-800': 620,
                        'NO-GULL-1000': 750,
                        'NO-GULL-1200': 680,
                        'NO-GULL-2000': 890,
                        'BRA(Udgået)': 120
                    },
                    total: 4410
                },
                day: {
                    shops: {
                        'NO-Jgk-300': 15,
                        'NO-Jgk-400': 12,
                        'NO-Jgk-600': 18,
                        'NO-Jgk-800': 22,
                        'NO-GULL-1000': 28,
                        'NO-GULL-1200': 25,
                        'NO-GULL-2000': 32,
                        'BRA(Udgået)': 4
                    },
                    total: 156
                },
                month: {
                    shops: {
                        'NO-Jgk-300': 120,
                        'NO-Jgk-400': 95,
                        'NO-Jgk-600': 135,
                        'NO-Jgk-800': 165,
                        'NO-GULL-1000': 195,
                        'NO-GULL-1200': 175,
                        'NO-GULL-2000': 225,
                        'BRA(Udgået)': 30
                    },
                    total: 1140
                },
                total_day: {
                    shops: {
                        'NO-Jgk-300': 320,
                        'NO-Jgk-400': 270,
                        'NO-Jgk-600': 370,
                        'NO-Jgk-800': 440,
                        'NO-GULL-1000': 535,
                        'NO-GULL-1200': 485,
                        'NO-GULL-2000': 635,
                        'BRA(Udgået)': 85
                    },
                    total: 3140
                }
            },
            2024: {
                total: {
                    shops: {
                        'NO-Jgk-300': 420,
                        'NO-Jgk-400': 350,
                        'NO-Jgk-600': 490,
                        'NO-Jgk-800': 590,
                        'NO-GULL-1000': 720,
                        'NO-GULL-1200': 650,
                        'NO-GULL-2000': 860,
                        'BRA(Udgået)': 110
                    },
                    total: 4190
                },
                day: {
                    shops: {
                        'NO-Jgk-300': 14,
                        'NO-Jgk-400': 11,
                        'NO-Jgk-600': 17,
                        'NO-Jgk-800': 21,
                        'NO-GULL-1000': 26,
                        'NO-GULL-1200': 23,
                        'NO-GULL-2000': 30,
                        'BRA(Udgået)': 3
                    },
                    total: 145
                },
                month: {
                    shops: {
                        'NO-Jgk-300': 110,
                        'NO-Jgk-400': 85,
                        'NO-Jgk-600': 125,
                        'NO-Jgk-800': 155,
                        'NO-GULL-1000': 185,
                        'NO-GULL-1200': 165,
                        'NO-GULL-2000': 215,
                        'BRA(Udgået)': 25
                    },
                    total: 1065
                },
                total_day: {
                    shops: {
                        'NO-Jgk-300': 290,
                        'NO-Jgk-400': 240,
                        'NO-Jgk-600': 340,
                        'NO-Jgk-800': 410,
                        'NO-GULL-1000': 505,
                        'NO-GULL-1200': 455,
                        'NO-GULL-2000': 605,
                        'BRA(Udgået)': 75
                    },
                    total: 2920
                }
            },
            2023: {
                total: {
                    shops: {
                        'NO-Jgk-300': 390,
                        'NO-Jgk-400': 320,
                        'NO-Jgk-600': 460,
                        'NO-Jgk-800': 560,
                        'NO-GULL-1000': 690,
                        'NO-GULL-1200': 620,
                        'NO-GULL-2000': 830,
                        'BRA(Udgået)': 100
                    },
                    total: 3970
                },
                day: {
                    shops: {
                        'NO-Jgk-300': 13,
                        'NO-Jgk-400': 10,
                        'NO-Jgk-600': 16,
                        'NO-Jgk-800': 20,
                        'NO-GULL-1000': 25,
                        'NO-GULL-1200': 22,
                        'NO-GULL-2000': 29,
                        'BRA(Udgået)': 3
                    },
                    total: 138
                },
                month: {
                    shops: {
                        'NO-Jgk-300': 100,
                        'NO-Jgk-400': 75,
                        'NO-Jgk-600': 115,
                        'NO-Jgk-800': 145,
                        'NO-GULL-1000': 175,
                        'NO-GULL-1200': 155,
                        'NO-GULL-2000': 205,
                        'BRA(Udgået)': 20
                    },
                    total: 990
                },
                total_day: {
                    shops: {
                        'NO-Jgk-300': 260,
                        'NO-Jgk-400': 210,
                        'NO-Jgk-600': 310,
                        'NO-Jgk-800': 380,
                        'NO-GULL-1000': 475,
                        'NO-GULL-1200': 425,
                        'NO-GULL-2000': 575,
                        'BRA(Udgået)': 65
                    },
                    total: 2700
                }
            },
            2022: {
                total: {
                    shops: {
                        'NO-Jgk-300': 360,
                        'NO-Jgk-400': 290,
                        'NO-Jgk-600': 430,
                        'NO-Jgk-800': 530,
                        'NO-GULL-1000': 660,
                        'NO-GULL-1200': 590,
                        'NO-GULL-2000': 800,
                        'BRA(Udgået)': 90
                    },
                    total: 3750
                },
                day: {
                    shops: {
                        'NO-Jgk-300': 12,
                        'NO-Jgk-400': 9,
                        'NO-Jgk-600': 15,
                        'NO-Jgk-800': 19,
                        'NO-GULL-1000': 24,
                        'NO-GULL-1200': 21,
                        'NO-GULL-2000': 28,
                        'BRA(Udgået)': 2
                    },
                    total: 130
                },
                month: {
                    shops: {
                        'NO-Jgk-300': 90,
                        'NO-Jgk-400': 65,
                        'NO-Jgk-600': 105,
                        'NO-Jgk-800': 135,
                        'NO-GULL-1000': 165,
                        'NO-GULL-1200': 145,
                        'NO-GULL-2000': 195,
                        'BRA(Udgået)': 15
                    },
                    total: 915
                },
                total_day: {
                    shops: {
                        'NO-Jgk-300': 230,
                        'NO-Jgk-400': 180,
                        'NO-Jgk-600': 280,
                        'NO-Jgk-800': 350,
                        'NO-GULL-1000': 445,
                        'NO-GULL-1200': 395,
                        'NO-GULL-2000': 545,
                        'BRA(Udgået)': 55
                    },
                    total: 2480
                }
            }
        },
        se: {
            2025: {
                total: {
                    shops: {
                        'SE-300': 280,
                        'SE-400': 420,
                        'SE-440': 180,
                        'SE-600': 380,
                        'SE-800': 520,
                        'SE-som': 310,
                        'SE-1200(udgået)': 95
                    },
                    total: 2185
                },
                day: {
                    shops: {
                        'SE-300': 8,
                        'SE-400': 15,
                        'SE-440': 6,
                        'SE-600': 12,
                        'SE-800': 18,
                        'SE-som': 11,
                        'SE-1200(udgået)': 3
                    },
                    total: 73
                },
                month: {
                    shops: {
                        'SE-300': 65,
                        'SE-400': 105,
                        'SE-440': 45,
                        'SE-600': 95,
                        'SE-800': 130,
                        'SE-som': 78,
                        'SE-1200(udgået)': 20
                    },
                    total: 538
                },
                total_day: {
                    shops: {
                        'SE-300': 185,
                        'SE-400': 285,
                        'SE-440': 125,
                        'SE-600': 265,
                        'SE-800': 365,
                        'SE-som': 215,
                        'SE-1200(udgået)': 65
                    },
                    total: 1505
                }
            },
            2024: {
                total: {
                    shops: {
                        'SE-300': 260,
                        'SE-400': 390,
                        'SE-440': 170,
                        'SE-600': 350,
                        'SE-800': 490,
                        'SE-som': 290,
                        'SE-1200(udgået)': 85
                    },
                    total: 2035
                },
                day: {
                    shops: {
                        'SE-300': 7,
                        'SE-400': 14,
                        'SE-440': 5,
                        'SE-600': 11,
                        'SE-800': 17,
                        'SE-som': 10,
                        'SE-1200(udgået)': 2
                    },
                    total: 66
                },
                month: {
                    shops: {
                        'SE-300': 60,
                        'SE-400': 95,
                        'SE-440': 40,
                        'SE-600': 85,
                        'SE-800': 120,
                        'SE-som': 70,
                        'SE-1200(udgået)': 15
                    },
                    total: 485
                },
                total_day: {
                    shops: {
                        'SE-300': 165,
                        'SE-400': 255,
                        'SE-440': 115,
                        'SE-600': 235,
                        'SE-800': 335,
                        'SE-som': 195,
                        'SE-1200(udgået)': 55
                    },
                    total: 1355
                }
            },
            2023: {
                total: {
                    shops: {
                        'SE-300': 240,
                        'SE-400': 360,
                        'SE-440': 150,
                        'SE-600': 320,
                        'SE-800': 460,
                        'SE-som': 260,
                        'SE-1200(udgået)': 75
                    },
                    total: 1865
                },
                day: {
                    shops: {
                        'SE-300': 6,
                        'SE-400': 13,
                        'SE-440': 4,
                        'SE-600': 10,
                        'SE-800': 16,
                        'SE-som': 9,
                        'SE-1200(udgået)': 2
                    },
                    total: 60
                },
                month: {
                    shops: {
                        'SE-300': 55,
                        'SE-400': 85,
                        'SE-440': 35,
                        'SE-600': 75,
                        'SE-800': 110,
                        'SE-som': 60,
                        'SE-1200(udgået)': 10
                    },
                    total: 430
                },
                total_day: {
                    shops: {
                        'SE-300': 145,
                        'SE-400': 225,
                        'SE-440': 95,
                        'SE-600': 205,
                        'SE-800': 305,
                        'SE-som': 165,
                        'SE-1200(udgået)': 45
                    },
                    total: 1185
                }
            },
            2022: {
                total: {
                    shops: {
                        'SE-300': 220,
                        'SE-400': 330,
                        'SE-440': 130,
                        'SE-600': 290,
                        'SE-800': 430,
                        'SE-som': 230,
                        'SE-1200(udgået)': 65
                    },
                    total: 1695
                },
                day: {
                    shops: {
                        'SE-300': 5,
                        'SE-400': 12,
                        'SE-440': 3,
                        'SE-600': 9,
                        'SE-800': 15,
                        'SE-som': 8,
                        'SE-1200(udgået)': 1
                    },
                    total: 53
                },
                month: {
                    shops: {
                        'SE-300': 50,
                        'SE-400': 75,
                        'SE-440': 30,
                        'SE-600': 65,
                        'SE-800': 100,
                        'SE-som': 50,
                        'SE-1200(udgået)': 5
                    },
                    total: 375
                },
                total_day: {
                    shops: {
                        'SE-300': 125,
                        'SE-400': 195,
                        'SE-440': 75,
                        'SE-600': 175,
                        'SE-800': 275,
                        'SE-som': 135,
                        'SE-1200(udgået)': 35
                    },
                    total: 1015
                }
            }
        }
    };

    // Initialize dashboard
    $(document).ready(function() {
        // Wait a bit for all elements to be ready
        setTimeout(function() {
            loadData(); // Load data first
            initializeDashboard(); // Then initialize
            setupEventListeners();
        }, 100);
    });

    function loadData() {
        try {
            debugLog('Starting data load...');

            // In a real implementation, this would make AJAX calls to PHP backend
            salesData = mockData;

            // Verify data structure
            const countries = Object.keys(salesData);
            debugLog('Countries loaded:', countries);

            countries.forEach(country => {
                const years = Object.keys(salesData[country]);
                debugLog(`${country} years:`, years);

                years.forEach(year => {
                    const data = salesData[country][year];
                    debugLog(`${country} ${year} structure:`, {
                        hasTotal: !!data.total,
                        hasDay: !!data.day,
                        hasMonth: !!data.month,
                        hasTotalDay: !!data.total_day,
                        shopCount: data.total ? Object.keys(data.total.shops || {}).length : 0
                    });
                });
            });

            debugLog('Data loaded successfully, structure verified');
            return true;

        } catch (error) {
            console.error('Error loading data:', error);

            // Fallback to empty data structure
            salesData = {
                dk: { 2025: { total: { shops: {}, total: 0 }, day: { shops: {}, total: 0 }, month: { shops: {}, total: 0 }, total_day: { shops: {}, total: 0 } } },
                no: { 2025: { total: { shops: {}, total: 0 }, day: { shops: {}, total: 0 }, month: { shops: {}, total: 0 }, total_day: { shops: {}, total: 0 } } },
                se: { 2025: { total: { shops: {}, total: 0 }, day: { shops: {}, total: 0 }, month: { shops: {}, total: 0 }, total_day: { shops: {}, total: 0 } } }
            };

            debugLog('Fallback data structure created');
            return false;
        }
    }

    function initializeDashboard() {
        debugLog('Initializing dashboard...');

        try {
            // Verify data is loaded
            if (!salesData || Object.keys(salesData).length === 0) {
                console.error('No data available, retrying in 200ms...');
                setTimeout(initializeDashboard, 200);
                return;
            }

            if (!salesData.dk || !salesData.dk['2025']) {
                console.error('Data structure incomplete, retrying in 200ms...', salesData);
                setTimeout(initializeDashboard, 200);
                return;
            }

            debugLog('Data verification passed, proceeding with initialization');

            // Initialize components in order
            debugLog('Updating overview cards...');
            updateOverviewCards();

            debugLog('Creating main charts...');
            createMainCharts();

            debugLog('Creating country charts...');
            createCountryCharts();

            debugLog('Updating all charts...');
            updateAllCharts();

            debugLog('Updating tables...');
            updateTables();

            console.log('✅ Dashboard initialized successfully');

        } catch (error) {
            console.error('❌ Error initializing dashboard:', error);

            // Show user-friendly error message
            const errorMsg = `
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Dashboard Initialization Error</h4>
                        <p>There was an error loading the dashboard. Please refresh the page or contact support.</p>
                        <hr>
                        <p class="mb-0"><small>Error: ${error.message}</small></p>
                    </div>
                `;
            $('.dashboard-container').prepend(errorMsg);
        }
    }

    function setupEventListeners() {
        // Year tab clicks
        $('.year-tab').click(function() {
            const year = $(this).data('year');
            const country = $(this).data('country');

            $(this).siblings().removeClass('active');
            $(this).addClass('active');

            updateCountryChart(country, year);
            updateOriginalLayout(country, year);
            updateYearChart(country);
        });

        // Filter changes
        $('#timeFilter, #compareFilter').change(function() {
            updateAllCharts();
        });

        // Window resize handler for charts
        $(window).on('resize', function() {
            setTimeout(function() {
                Object.values(charts).forEach(chart => {
                    if (chart && typeof chart.resize === 'function') {
                        chart.resize();
                    }
                });
            }, 100);
        });
    }

    function updateYearChart(country) {
        if (!charts[country + 'Year']) return;

        const years = ['2022', '2023', '2024', '2025'];
        const totalsData = years.map(year => {
            if (salesData[country] && salesData[country][year]) {
                return salesData[country][year].total.total;
            }
            return 0;
        });

        charts[country + 'Year'].data.datasets[0].data = totalsData;
        charts[country + 'Year'].update('active');
    }

    function updateOverviewCards() {
        try {
            // Check if data exists
            if (!salesData || Object.keys(salesData).length === 0) {
                console.warn('No sales data available for overview cards');
                return;
            }

            let totalSales = 0;
            let todaySales = 0;
            let monthSales = 0;

            // Calculate totals from all countries for 2025
            Object.keys(salesData).forEach(country => {
                if (salesData[country] && salesData[country]['2025']) {
                    const data = salesData[country]['2025'];
                    totalSales += data.total ? data.total.total || 0 : 0;
                    todaySales += data.day ? data.day.total || 0 : 0;
                    monthSales += data.month ? data.month.total || 0 : 0;
                }
            });

            // Calculate growth rate (comparing 2025 vs 2024)
            let total2024 = 0;
            Object.keys(salesData).forEach(country => {
                if (salesData[country] && salesData[country]['2024']) {
                    total2024 += salesData[country]['2024'].total ? salesData[country]['2024'].total.total || 0 : 0;
                }
            });

            const growthRate = total2024 > 0 ? ((totalSales - total2024) / total2024 * 100).toFixed(1) : 0;

            // Animate the numbers
            animateNumber('#totalSales', totalSales);
            animateNumber('#todaySales', todaySales);
            animateNumber('#monthSales', monthSales);
            $('#growthRate').text(growthRate + '%');

            // Update country totals
            ['dk', 'no', 'se'].forEach(country => {
                if (salesData[country] && salesData[country]['2025']) {
                    const total = salesData[country]['2025'].total ? salesData[country]['2025'].total.total || 0 : 0;
                    $(`#${country}-total`).text(total.toLocaleString() + ' kort solgt');
                }
            });

            // Update original layout for all countries
            ['dk', 'no', 'se'].forEach(country => {
                if (salesData[country] && salesData[country]['2025']) {
                    updateOriginalLayout(country, '2025');
                }
            });

            console.log('Overview cards updated successfully');
        } catch (error) {
            console.error('Error updating overview cards:', error);
        }
    }

    function updateOriginalLayout(country, year) {
        try {
            if (!salesData[country] || !salesData[country][year]) {
                console.warn(`No data available for ${country} ${year}`);
                return;
            }

            const data = salesData[country][year];
            if (!data.total || !data.total.shops) {
                console.warn(`Incomplete data for ${country} ${year}`);
                return;
            }

            const shops = Object.keys(data.total.shops);

            // Update year labels
            $(`#${country}-year-label`).text(year);
            $(`#${country}-year-label-day`).text(year);
            $(`#${country}-year-label-month`).text(year);
            $(`#${country}-year-label-total-day`).text(year);

            // Update shop labels
            let shopLabelsHtml = '';
            shops.forEach(shop => {
                shopLabelsHtml += `<div class="concept-label">${shop}</div>`;
            });
            $(`#${country}-shop-labels`).html(shopLabelsHtml);

            // Update total values
            let totalValuesHtml = '';
            let totalSum = 0;
            shops.forEach(shop => {
                const value = data.total.shops[shop] || 0;
                totalSum += value;
                totalValuesHtml += `<div>${value.toLocaleString()}</div>`;
            });
            totalValuesHtml += `<div class="total-row">${totalSum.toLocaleString()}</div>`;
            $(`#${country}-total-values`).html(totalValuesHtml);

            // Update day values
            let dayValuesHtml = '';
            let daySum = 0;
            shops.forEach(shop => {
                const value = data.day && data.day.shops ? data.day.shops[shop] || 0 : 0;
                daySum += value;
                dayValuesHtml += `<div>${value.toLocaleString()}</div>`;
            });
            dayValuesHtml += `<div class="total-row">${daySum.toLocaleString()}</div>`;
            $(`#${country}-day-values`).html(dayValuesHtml);

            // Update month values
            let monthValuesHtml = '';
            let monthSum = 0;
            shops.forEach(shop => {
                const value = data.month && data.month.shops ? data.month.shops[shop] || 0 : 0;
                monthSum += value;
                monthValuesHtml += `<div>${value.toLocaleString()}</div>`;
            });
            monthValuesHtml += `<div class="total-row">${monthSum.toLocaleString()}</div>`;
            $(`#${country}-month-values`).html(monthValuesHtml);

            // Update total day values
            let totalDayValuesHtml = '';
            let totalDaySum = 0;
            shops.forEach(shop => {
                const value = data.total_day && data.total_day.shops ? data.total_day.shops[shop] || 0 : 0;
                totalDaySum += value;
                totalDayValuesHtml += `<div>${value.toLocaleString()}</div>`;
            });
            totalDayValuesHtml += `<div class="total-row">${totalDaySum.toLocaleString()}</div>`;
            $(`#${country}-total-day-values`).html(totalDayValuesHtml);

        } catch (error) {
            console.error(`Error updating original layout for ${country} ${year}:`, error);
        }
    }

    function animateNumber(selector, finalValue) {
        const element = $(selector);
        const startValue = 0;
        const duration = 1500;
        const startTime = Date.now();

        function updateNumber() {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const currentValue = Math.floor(startValue + (finalValue - startValue) * progress);

            element.text(currentValue.toLocaleString());

            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        }

        updateNumber();
    }

    function createMainCharts() {
        try {
            // Verify data exists before creating charts
            if (!salesData || !salesData.dk || !salesData.dk['2025'] || !salesData.no || !salesData.se) {
                console.error('Sales data not available for chart creation');
                return;
            }

            // Destroy existing charts if they exist
            if (charts.salesTrend) charts.salesTrend.destroy();
            if (charts.countryDistribution) charts.countryDistribution.destroy();

            // Sales Trend Chart
            const ctx1 = document.getElementById('salesTrendChart');
            if (ctx1) {
                charts.salesTrend = new Chart(ctx1.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni'],
                        datasets: [{
                            label: '2025',
                            data: [2500, 4200, 6800, 9500, 12800, 16500],
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        }, {
                            label: '2024',
                            data: [2200, 3800, 6200, 8800, 11500, 14200],
                            borderColor: '#e74c3c',
                            backgroundColor: 'rgba(231, 76, 60, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        }, {
                            label: '2023',
                            data: [2000, 3500, 5800, 8200, 10800, 13100],
                            borderColor: '#27ae60',
                            backgroundColor: 'rgba(39, 174, 96, 0.1)',
                            tension: 0.4,
                            fill: false,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                borderColor: '#3498db',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw.toLocaleString() + ' kort';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString() + ' kort';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Country Distribution Chart with safe data access
            const dkTotal = salesData.dk && salesData.dk['2025'] ? salesData.dk['2025'].total.total : 0;
            const noTotal = salesData.no && salesData.no['2025'] ? salesData.no['2025'].total.total : 0;
            const seTotal = salesData.se && salesData.se['2025'] ? salesData.se['2025'].total.total : 0;

            const ctx2 = document.getElementById('countryDistributionChart');
            if (ctx2) {
                charts.countryDistribution = new Chart(ctx2.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Danmark', 'Norge', 'Sverige'],
                        datasets: [{
                            data: [dkTotal, noTotal, seTotal],
                            backgroundColor: ['#c8102e', '#ef2b2d', '#006aa7'],
                            borderWidth: 3,
                            borderColor: '#fff',
                            hoverBorderWidth: 5,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + context.raw.toLocaleString() + ' kort (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            console.log('Main charts created successfully');
        } catch (error) {
            console.error('Error creating main charts:', error);
        }
    }

    function createCountryCharts() {
        // Destroy existing charts if they exist
        if (charts.dk) charts.dk.destroy();
        if (charts.no) charts.no.destroy();
        if (charts.se) charts.se.destroy();
        if (charts.dkYear) charts.dkYear.destroy();
        if (charts.noYear) charts.noYear.destroy();
        if (charts.seYear) charts.seYear.destroy();

        // Denmark Chart
        const dkCtx = document.getElementById('dkChart');
        if (dkCtx) {
            charts.dk = new Chart(dkCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Total salg',
                        data: [],
                        backgroundColor: '#c8102e',
                        borderRadius: 8,
                        borderSkipped: false
                    }, {
                        label: 'Salg i dag',
                        data: [],
                        backgroundColor: '#ff6b9d',
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw.toLocaleString() + ' kort';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Denmark Year Comparison Chart
        const dkYearCtx = document.getElementById('dkYearChart');
        if (dkYearCtx) {
            charts.dkYear = new Chart(dkYearCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['2022', '2023', '2024', '2025'],
                    datasets: [{
                        label: 'Total salg',
                        data: [7643, 8863, 9883, 10833],
                        borderColor: '#c8102e',
                        backgroundColor: 'rgba(200, 16, 46, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' kort';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Norway Chart
        const noCtx = document.getElementById('noChart');
        if (noCtx) {
            charts.no = new Chart(noCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Total salg',
                        data: [],
                        backgroundColor: '#ef2b2d',
                        borderRadius: 8,
                        borderSkipped: false
                    }, {
                        label: 'Salg i dag',
                        data: [],
                        backgroundColor: '#ff6b9d',
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw.toLocaleString() + ' kort';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Norway Year Comparison Chart
        const noYearCtx = document.getElementById('noYearChart');
        if (noYearCtx) {
            charts.noYear = new Chart(noYearCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['2022', '2023', '2024', '2025'],
                    datasets: [{
                        label: 'Total salg',
                        data: [3750, 3970, 4190, 4410],
                        borderColor: '#ef2b2d',
                        backgroundColor: 'rgba(239, 43, 45, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' kort';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Sweden Chart
        const seCtx = document.getElementById('seChart');
        if (seCtx) {
            charts.se = new Chart(seCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Total salg',
                        data: [],
                        backgroundColor: '#006aa7',
                        borderRadius: 8,
                        borderSkipped: false
                    }, {
                        label: 'Salg i dag',
                        data: [],
                        backgroundColor: '#fecc00',
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw.toLocaleString() + ' kort';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Sweden Year Comparison Chart
        const seYearCtx = document.getElementById('seYearChart');
        if (seYearCtx) {
            charts.seYear = new Chart(seYearCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['2022', '2023', '2024', '2025'],
                    datasets: [{
                        label: 'Total salg',
                        data: [1695, 1865, 2035, 2185],
                        borderColor: '#006aa7',
                        backgroundColor: 'rgba(0, 106, 167, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' kort';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    function updateCountryChart(country, year) {
        if (!salesData[country] || !salesData[country][year] || !charts[country]) return;

        try {
            const data = salesData[country][year];
            const shopLabels = Object.keys(data.total.shops);
            const totalData = Object.values(data.total.shops);
            const dayData = Object.values(data.day.shops);

            // Update chart data safely
            charts[country].data.labels = shopLabels;
            charts[country].data.datasets[0].data = totalData;
            charts[country].data.datasets[1].data = dayData;

            // Update with animation
            charts[country].update('active');
        } catch (error) {
            console.error(`Error updating ${country} chart:`, error);
        }
    }

    function updateCountryTable(country, year) {
        // This function is kept for compatibility but not used in new layout
        // Data is now shown in the original layout format
    }

    function updateAllCharts() {
        updateOverviewCards();

        // Update country charts with default year (2025)
        ['dk', 'no', 'se'].forEach(country => {
            updateCountryChart(country, '2025');
            updateOriginalLayout(country, '2025');
            updateYearChart(country);
        });

        // Update main charts data
        updateMainChartsData();
    }

    function updateMainChartsData() {
        // Update country distribution chart
        if (charts.countryDistribution) {
            const dkTotal = salesData.dk['2025'].total.total;
            const noTotal = salesData.no['2025'].total.total;
            const seTotal = salesData.se['2025'].total.total;

            charts.countryDistribution.data.datasets[0].data = [dkTotal, noTotal, seTotal];
            charts.countryDistribution.update('active');
        }

        // Update sales trend chart with actual data
        if (charts.salesTrend) {
            const months = ['Januar', 'Februar', 'Marts', 'April', 'Maj', 'Juni'];
            const data2025 = [2500, 4200, 6800, 9500, 12800, 16500]; // Cumulative data
            const data2024 = [2200, 3800, 6200, 8800, 11500, 14200]; // Cumulative data

            charts.salesTrend.data.datasets[0].data = data2025;
            charts.salesTrend.data.datasets[1].data = data2024;
            charts.salesTrend.update('active');
        }
    }

    function updateTables() {
        // Update original layout for all countries - this replaces table functionality
        ['dk', 'no', 'se'].forEach(country => {
            updateOriginalLayout(country, '2025');
        });
    }

    function exportData(format) {
        if (format === 'csv') {
            // Generate comprehensive CSV data
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Land,Shop,År,Total Salg,Salg I Dag,Salg Denne Måned,Salg Til Dato\n";

            Object.keys(salesData).forEach(country => {
                const countryName = country === 'dk' ? 'Danmark' : country === 'no' ? 'Norge' : 'Sverige';
                Object.keys(salesData[country]).forEach(year => {
                    const data = salesData[country][year];
                    Object.keys(data.total.shops).forEach(shop => {
                        const totalSale = data.total.shops[shop];
                        const daySale = data.day.shops[shop];
                        const monthSale = data.month.shops[shop];
                        const totalDaySale = data.total_day.shops[shop];
                        csvContent += `${countryName},${shop},${year},${totalSale},${daySale},${monthSale},${totalDaySale}\n`;
                    });

                    // Add totals row
                    csvContent += `${countryName},TOTAL,${year},${data.total.total},${data.day.total},${data.month.total},${data.total_day.total}\n`;
                });
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "gavekort_detaljeret_salg_data.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else if (format === 'pdf') {
            // Create a summary report for PDF
            let reportContent = `
                <h1>Gavekort Salgs Rapport ${new Date().getFullYear()}</h1>
                <h2>Oversigt</h2>
                <p>Total salg alle lande 2025: ${(salesData.dk['2025'].total.total + salesData.no['2025'].total.total + salesData.se['2025'].total.total).toLocaleString()} kort</p>
                `;

            ['dk', 'no', 'se'].forEach(country => {
                const countryName = country === 'dk' ? 'Danmark' : country === 'no' ? 'Norge' : 'Sverige';
                reportContent += `
                    <h3>${countryName}</h3>
                    <table border="1" style="border-collapse: collapse; width: 100%;">
                        <tr>
                            <th>Shop</th>
                            <th>2025 Total</th>
                            <th>2024 Total</th>
                            <th>Vækst</th>
                        </tr>`;

                Object.keys(salesData[country]['2025'].total.shops).forEach(shop => {
                    const total2025 = salesData[country]['2025'].total.shops[shop];
                    const total2024 = salesData[country]['2024'] ? salesData[country]['2024'].total.shops[shop] || 0 : 0;
                    const growth = total2024 > 0 ? ((total2025 - total2024) / total2024 * 100).toFixed(1) : 'N/A';
                    reportContent += `
                        <tr>
                            <td>${shop}</td>
                            <td>${total2025.toLocaleString()}</td>
                            <td>${total2024.toLocaleString()}</td>
                            <td>${growth}%</td>
                        </tr>`;
                });
                reportContent += '</table><br>';
            });

            // Open in new window for printing/saving as PDF
            const newWindow = window.open('', '_blank');
            newWindow.document.write(`
                    <html>
                        <head>
                            <title>Gavekort Salgs Rapport</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                table { border-collapse: collapse; width: 100%; margin: 10px 0; }
                                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                th { background-color: #f2f2f2; }
                                h1, h2, h3 { color: #333; }
                            </style>
                        </head>
                        <body>
                            ${reportContent}
                            <p><small>Genereret: ${new Date().toLocaleString()}</small></p>
                        </body>
                    </html>
                `);
            newWindow.document.close();

            // Prompt user to print or save as PDF
            setTimeout(() => {
                newWindow.print();
            }, 1000);
        }
    }

    // Auto-refresh data every 5 minutes
    setInterval(function() {
        loadData();
    }, 300000);
</script>
</body>
</html>