<?php
// Salgstats View - Main Interface
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salgs Statistik</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js CSS -->
    <link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }

        .btn-excel {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .btn-excel:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .spinner-border-custom {
            width: 3rem;
            height: 3rem;
            border-width: 0.3em;
        }

        .table-stats {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .alert-custom {
            border-radius: 10px;
            border: none;
        }

        .form-select, .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .form-select:focus, .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
            border-color: #667eea;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }

        .price-badge {
            background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .revenue-highlight {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 12px;
            border-radius: 15px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .stats-number {
                font-size: 2rem;
            }

            .filter-section {
                padding: 15px;
            }

            .chart-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="display-4 text-center mb-4">
                <i class="fas fa-chart-line text-primary"></i>
                Salgs Statistik
            </h1>
        </div>
    </div>

    <!-- Message Container -->
    <div id="message-container"></div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="dateFrom" class="form-label">Fra Dato</label>
                <input type="date" class="form-control" id="dateFrom" name="dateFrom">
            </div>
            <div class="col-md-3 mb-3">
                <label for="dateTo" class="form-label">Til Dato</label>
                <input type="date" class="form-control" id="dateTo" name="dateTo">
            </div>
            <div class="col-md-2 mb-3">
                <label for="salesperson" class="form-label">Sælger</label>
                <select class="form-select" id="salesperson" name="salesperson">
                    <option value="all">Alle</option>
                    <option value="import">Import/Web</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="groupBy" class="form-label">Gruppering</label>
                <select class="form-select" id="groupBy" name="groupBy">
                    <option value="day">Dag</option>
                    <option value="week">Uge</option>
                    <option value="month" selected>Måned</option>
                    <option value="year">År</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label for="conceptCode" class="form-label">Koncept</label>
                <select class="form-select" id="conceptCode" name="conceptCode">
                    <option value="all">Alle Koncepter</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <button id="updateStats" class="btn btn-custom">
                    <i class="fas fa-sync-alt"></i> Opdater Statistik
                </button>
                <button id="exportCSV" class="btn btn-outline-secondary">
                    <i class="fas fa-file-csv"></i> Eksportér CSV
                </button>
                <button id="exportExcel" class="btn btn-excel">
                    <i class="fas fa-file-excel"></i> Eksportér Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" class="loading">
        <div class="spinner-border spinner-border-custom text-primary" role="status">
            <span class="visually-hidden">Indlæser...</span>
        </div>
        <p class="mt-3 text-muted">Indlæser salgsdata...</p>
    </div>

    <!-- Stats Overview -->
    <div id="statsOverview" class="row">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number" id="totalSales">0</div>
                <div class="stats-label">Total Salg</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number" id="totalRevenue">0 kr</div>
                <div class="stats-label">Total Omsætning</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number" id="avgPerPeriod">0</div>
                <div class="stats-label">Gennemsnit pr. Periode</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number" id="avgPrice">0 kr</div>
                <div class="stats-label">Gennemsnitspris</div>
            </div>
        </div>
    </div>

    <!-- Charts Container -->
    <div id="chartsContainer">
        <!-- Sales Over Time Chart -->
        <div class="chart-container">
            <div class="card-header-custom">
                <i class="fas fa-line-chart"></i> Salg Over Tid
            </div>
            <canvas id="salesTimeChart" width="400" height="150"></canvas>
        </div>

        <div class="row">
            <!-- Sales by Country Chart -->
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="card-header-custom">
                        <i class="fas fa-globe"></i> Salg pr. Land
                    </div>
                    <canvas id="countryChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Revenue by Country Chart -->
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="card-header-custom">
                        <i class="fas fa-money-bill-wave"></i> Omsætning pr. Land
                    </div>
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Sales by Concept Chart -->
        <div class="chart-container">
            <div class="card-header-custom">
                <i class="fas fa-tags"></i> Salg pr. Koncept
            </div>
            <canvas id="conceptChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Data Table -->
    <div class="chart-container">
        <div class="card-header-custom">
            <i class="fas fa-table"></i> Detaljeret Data
        </div>
        <div class="table-responsive">
            <table id="salesTable" class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Periode</th>
                        <th>Land</th>
                        <th>Koncept</th>
                        <th>Antal Solgt</th>
                        <th>Kortpris</th>
                        <th>Omsætning</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>

<!-- Load and initialize the SalgStats.js module -->
<script type="module">
    import SalgStats from '/gavefabrikken_backend/units/apps/salgstats/js/SalgStats.js';

    $(document).ready(function() {
        window.salgStats = new SalgStats();
        window.salgStats.init();
    });
</script>
</body>
</html>