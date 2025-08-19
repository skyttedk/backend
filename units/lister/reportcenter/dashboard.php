<?php
// dashboard.php

namespace GFUnit\lister\reportcenter;

// Sørg for at autoloading er sat op korrekt - tilpas efter behov
// require_once __DIR__ . '/../../vendor/autoload.php';

// DEBUG: Vis rapportsøgning
$reportsDir = __DIR__ . '/Reports';
if (is_dir($reportsDir)) {
    $files = glob($reportsDir . '/*.php');
    // Kommenter denne linje ud når du er færdig med debugging
    // echo "Søger efter rapporter i: " . $reportsDir . " | Fandt: " . count($files) . " filer";
}

// Hent rapport center
$reportCenter = ReportCenter::getInstance();

$userRoles = $reportCenter->getUserRoles();
$countryName = $reportCenter->getCountryName();

// Hent rapporter baseret på brugerens roller
$reports = $reportCenter->getReportsForRoles($userRoles);

// Håndter rapport generering, hvis der er en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_code'])) {
    try {
        $reportCode = $_POST['report_code'];
        $format = $_POST['export_format'] ?? 'csv';

        // Tjek om rapporten findes og brugeren har adgang
        if (!$reportCenter->hasReport($reportCode)) {
            throw new \Exception("Ukendt rapport: $reportCode");
        }

        $report = $reportCenter->getReport($reportCode);

        // Validér parametre
        if (!$report->validateParameters($_POST)) {
            throw new \Exception("Ugyldige parametre for rapporten");
        }

        // Process parametre
        $parameters = $report->processParameters($_POST);

        // Generer rapport
        $result = $reportCenter->generateReport($reportCode, $parameters);

        // Eksportér rapport
        $reportCenter->exportReport($result, $format);
        exit; // Eksport håndterer output og afslutter
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

// Sikker måde at få en parameter værdi (med en default værdi)
function getParamValue($param, $default = '') {
    return htmlspecialchars($_POST[$param] ?? $default);
}

// Funktion til at tjekke om en rapport er valgt
function isReportSelected($reportCode) {
    return isset($_POST['report_code']) && $_POST['report_code'] === $reportCode;
}

?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GF Rapporteringscenter</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #dbeafe;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --box-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius: 8px;
            --transition: all 0.2s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--gray-800);
            background-color: #f9fafb;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ======= Header styles ======= */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: white;
            border-bottom: 1px solid var(--gray-200);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            height: 64px;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .location-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            background: var(--gray-100);
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-700);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .location-badge i {
            font-size: 0.625rem;
            color: var(--gray-500);
        }

        .role-pills {
            display: flex;
            gap: 0.375rem;
        }

        .role-pill {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            background: var(--primary-light);
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--primary-color);
        }

        /* ======= Main content ======= */
        .main-content {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* ======= Report list sidebar ======= */
        .report-sidebar {
            width: 40%;
            min-width: 300px;
            max-width: 450px;
            border-right: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            background: white;
        }

        .search-container {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 0.875rem;
        }

        .report-list {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .report-list::-webkit-scrollbar {
            width: 6px;
        }

        .report-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .report-list::-webkit-scrollbar-thumb {
            background-color: var(--gray-300);
            border-radius: 3px;
        }

        .report-card {
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: var(--border-radius);
            background: white;
            border: 1px solid var(--gray-200);
            cursor: pointer;
            transition: var(--transition);
        }

        .report-card:hover {
            box-shadow: var(--box-shadow-hover);
            border-color: var(--gray-300);
        }

        .report-card.active {
            border-left: 4px solid var(--primary-color);
            background-color: var(--primary-light);
        }

        .report-card h3 {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.375rem;
        }

        .report-card p {
            font-size: 0.8125rem;
            color: var(--gray-600);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .no-reports {
            padding: 2rem;
            text-align: center;
            color: var(--gray-500);
        }

        /* ======= Parameters section ======= */
        form {
            display: flex;
            flex-direction: column;
            flex: 1;
            width: 60%;
        }

        .parameters-section {
            width: 100%;
            flex: 1;
            padding: 1rem 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .parameters-section::-webkit-scrollbar {
            width: 6px;
        }

        .parameters-section::-webkit-scrollbar-track {
            background: transparent;
        }

        .parameters-section::-webkit-scrollbar-thumb {
            background-color: var(--gray-300);
            border-radius: 3px;
        }

        .parameter-content {
            flex: 1;
            overflow-y: auto;
        }

        .report-title {
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .report-title h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        .report-title p {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.25rem;
        }

        .parameters-placeholder {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
        }

        .parameters-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-300);
        }

        .parameters-placeholder h3 {
            font-size: 1.125rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .parameters-placeholder p {
            font-size: 0.875rem;
            max-width: 20rem;
        }

        /* ======= Parameter groups ======= */
        .parameter-group {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            overflow: hidden;
            display: inline-block;
            min-width: 400px; max-width: 600px; margin-right: 20px; margin-bottom: 20px;
            vertical-align: top;
        }

        .parameter-header {
            padding: 0.75rem 1rem;
            background: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--gray-700);
            display: flex;
            align-items: center;
        }

        .parameter-header i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        /* ======= Parameter form fields ======= */
        .parameter-body {
            padding: 1.25rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group.date-range-group {
            grid-column: span 2;
        }

        .form-group.multi-select-group,
        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .export-format-group {
            max-width: 300px;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.375rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1rem;
            padding-right: 2rem;
        }

        /* Date ranges */
        .date-range {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .date-range input {
            flex: 1;
        }

        .date-separator {
            font-size: 0.875rem;
            color: var(--gray-500);
        }

        /* ======= Footer ======= */
        .footer {
            padding: 1rem 2rem;
            background: white;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            height: 70px;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-primary:disabled {
            background: var(--gray-300);
            cursor: not-allowed;
        }

        .btn i {
            font-size: 0.875rem;
        }

        /* ======= Message styles ======= */
        .error-message {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .success-message {
            background-color: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ======= Multiselect styles ======= */
        .multi-select-container {
            position: relative;
        }

        .multi-select-header {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .multi-select-header:hover {
            border-color: var(--primary-color);
        }

        .multi-select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 100;
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            box-shadow: var(--box-shadow);
            max-height: 300px;
            overflow-y: auto;
            display: none;
        }

        .multi-select-dropdown.show {
            display: block;
        }

        .selected-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }

        .selected-tag {
            background: var(--primary-light);
            color: var(--primary-color);
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .selected-tag i {
            cursor: pointer;
        }

        /* ======= Responsiveness ======= */
        @media (max-width: 1200px) {
            .parameter-body {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
        }

        @media (max-width: 992px) {
            .main-content {
                flex-direction: column;
                overflow-y: auto;
            }

            .report-sidebar, form {
                width: 100%;
                max-width: none;
            }

            .report-sidebar {
                max-height: 50vh;
            }

            .parameter-body {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .parameter-body {
                grid-template-columns: 1fr;
            }

            .form-group {
                grid-column: 1;
            }

            .form-group.date-range-group {
                grid-column: 1;
            }
        }

        /* ======= Utilities ======= */
        .hidden {
            display: none;
        }

        /* Kategoristil for rapporter */
        .report-categories {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .category-section {
            margin-bottom: 1rem;
        }

        .category-header {
            padding: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: var(--gray-100);
            border-radius: var(--border-radius);
            margin-bottom: 0.5rem;

        }

        .category-reports {
            padding-left: 0.5rem;
        }

        .report-card .report-category-tag {
            float: right;
            display: inline-block;
            font-size: 0.65rem;
            padding: 0.125rem 0.375rem;
            background-color: var(--gray-100);
            color: var(--gray-600);
            border-radius: 9999px;
            margin-bottom: 0.375rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }


    </style>
</head>
<body>
<!-- Header -->
<header class="header">
    <h1>GF - Rapporteringscenter</h1>
    <div class="header-info">
        <div class="location-badge">
            <i class="fas fa-map-marker-alt"></i>
            <span><?php echo $countryName; ?></span>
        </div>
        <div class="role-pills">
            <?php foreach ($userRoles as $role): ?>
                <div class="role-pill"><?= htmlspecialchars(ucfirst($role)) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</header>

<!-- Main content -->
<div class="main-content">
    <!-- Report list sidebar -->
    <div class="report-sidebar">
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="report-search" placeholder="Søg efter rapporter..." autocomplete="off">
            </div>
        </div>
        <div class="report-list" id="report-list">
            <?php if (empty($reports)): ?>
                <div class="no-reports">
                    <p>Ingen rapporter er tilgængelige for din rolle.</p>
                </div>
            <?php else: ?>
                <?php

                // Hent rapporter grupperet efter kategori
                $reportsByCategory = $reportCenter->getReportsByCategory($userRoles);
                ?>
                <ul class="report-categories">
                    <?php foreach ($reportsByCategory as $category => $categoryReports): ?>
                        <li class="category-section">
                            <div class="category-header">
                                <?= htmlspecialchars($category) ?>
                            </div>
                            <div class="category-reports">
                                <?php foreach ($categoryReports as $report): ?>
                                    <div class="report-card <?= isReportSelected($report->getCode()) ? 'active' : '' ?>"
                                         data-report-code="<?= htmlspecialchars($report->getCode()) ?>"
                                         data-report-name="<?= htmlspecialchars($report->getName()) ?>"
                                         data-report-description="<?= htmlspecialchars($report->getDescription()) ?>"
                                         data-report-category="<?= htmlspecialchars($report->getCategory()) ?>">
                                        <span class="report-category-tag"><?= htmlspecialchars($report->getCategory()) ?></span>
                                        <h3><?= htmlspecialchars($report->getName()) ?></h3>
                                        <p><?= htmlspecialchars($report->getDescription()) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </div>
    </div>


    <!-- Parameters section -->
    <form method="post" action="index.php?rt=unit/lister/reportcenter/download">
        <div class="parameters-section">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="parameters-placeholder" id="parameters-placeholder" <?= isset($_POST['report_code']) ? 'style="display:none;"' : '' ?>>
                <i class="far fa-file-alt"></i>
                <h3>Vælg en rapport for at komme i gang</h3>
                <p>Vælg en rapport fra listen til venstre for at se rapportens kriterier og generere din rapport.</p>
            </div>

            <div id="parameter-content" <?= isset($_POST['report_code']) ? '' : 'style="display:none;"' ?>>
                <?php if (isset($_POST['report_code']) && $reportCenter->hasReport($_POST['report_code'])): ?>
                    <?php
                    $selectedReport = $reportCenter->getReport($_POST['report_code']);
                    ?>
                    <div class="report-title">
                        <h2><?= htmlspecialchars($selectedReport->getName()) ?></h2>
                        <p><?= htmlspecialchars($selectedReport->getDescription()) ?></p>
                    </div>

                    <input type="hidden" name="report_code" value="<?= htmlspecialchars($selectedReport->getCode()) ?>">

                    <!-- Standard parametre -->
                    <?php if (!empty($selectedReport->getRequiredParameters())): ?>
                        <?= $selectedReport->renderStandardParameters() ?>
                    <?php endif; ?>

                    <!-- Specielle parametre -->
                    <?php if ($selectedReport->hasSpecialParameters()): ?>
                        <div class="parameter-group">
                            <div class="parameter-header">
                                <i class="fas fa-filter"></i> Specielle kriterier
                            </div>
                            <div class="parameter-body">
                                <?= $selectedReport->renderSpecialParameters() ?>
                            </div>
                        </div>
                    <?php endif; ?>


                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <button type="submit" id="generate-report-btn" class="btn btn-primary" <?= isset($_POST['report_code']) ? '' : 'disabled' ?>>
                <i class="fas fa-download"></i> Hent rapport
            </button>
        </footer>
    </form>
</div>

<!-- JavaScript -->
<!-- JavaScript -->
<script>
    // Vent på at DOM'en er indlæst
    document.addEventListener('DOMContentLoaded', function() {
        // Elementer
        const reportSearch = document.getElementById('report-search');
        const reportList = document.getElementById('report-list');
        const reportCards = document.querySelectorAll('.report-card');
        const parametersPlaceholder = document.getElementById('parameters-placeholder');
        const parameterContent = document.getElementById('parameter-content');
        const generateReportBtn = document.getElementById('generate-report-btn');
        const reportForm = document.querySelector('form');

        // Rapportkort klik
        reportCards.forEach(card => {
            card.addEventListener('click', function() {
                // Fjern aktiv klasse fra alle kort
                reportCards.forEach(c => c.classList.remove('active'));

                // Tilføj aktiv klasse til valgt kort
                this.classList.add('active');

                // Opret form data for at sende til serveren
                const formData = new FormData();
                formData.append('report_code', this.getAttribute('data-report-code'));

                // Send AJAX request til at hente rapport parametre
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(html => {
                        // Indsæt HTML-respons i DOM
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;

                        // Find parameter content i respons
                        const newParameterContent = tempDiv.querySelector('#parameter-content');

                        if (newParameterContent) {
                            // Skjul placeholder
                            parametersPlaceholder.style.display = 'none';

                            // Opdater parameter content
                            parameterContent.innerHTML = newParameterContent.innerHTML;
                            parameterContent.style.display = 'block';

                            // Aktivér hent rapport knap
                            generateReportBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Fejl ved hentning af rapportparametre:', error);
                    });
            });
        });

        // Søgefunktionalitet
        reportSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            reportCards.forEach(card => {
                const title = card.getAttribute('data-report-name').toLowerCase();
                const description = card.getAttribute('data-report-description').toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            // Vis "ingen rapporter" besked hvis ingen resultater
            const visibleCards = Array.from(reportCards).filter(card => card.style.display !== 'none');

            if (visibleCards.length === 0) {
                // Tjek om "ingen rapporter" besked allerede findes
                let noReportsMessage = reportList.querySelector('.no-reports');

                if (!noReportsMessage) {
                    noReportsMessage = document.createElement('div');
                    noReportsMessage.className = 'no-reports';
                    noReportsMessage.innerHTML = '<p>Ingen rapporter matcher din søgning.</p>';
                    reportList.appendChild(noReportsMessage);
                } else {
                    noReportsMessage.style.display = 'block';
                }
            } else {
                // Skjul "ingen rapporter" besked hvis den findes
                const noReportsMessage = reportList.querySelector('.no-reports');
                if (noReportsMessage) {
                    noReportsMessage.style.display = 'none';
                }
            }
        });

        // Form submit med AJAX download
        reportForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop normal form submission

            // Deaktiver knappen og vis spinner
            generateReportBtn.disabled = true;
            generateReportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Genererer...';

            // Få form data
            const formData = new FormData(this);

            // Opret en skjult iframe til download
            let downloadFrame = document.getElementById('download-frame');
            if (!downloadFrame) {
                downloadFrame = document.createElement('iframe');
                downloadFrame.id = 'download-frame';
                downloadFrame.name = 'download-frame';
                downloadFrame.style.display = 'none';
                document.body.appendChild(downloadFrame);
            }

            // Ændr form target til iframe og submit
            this.target = 'download-frame';
            this.submit();

            // Genaktiver knappen efter en kort forsinkelse
            setTimeout(function() {
                generateReportBtn.disabled = false;
                generateReportBtn.innerHTML = '<i class="fas fa-download"></i> Hent rapport';
            }, 1500);

            // Tilføj dette efter timeout i submit eventlistener
            const successMessage = document.createElement('div');
            successMessage.className = 'success-message';
            successMessage.innerHTML = '<i class="fas fa-check-circle"></i> Rapporten blev genereret og download skulle starte automatisk.';
            parameterContent.insertBefore(successMessage, parameterContent.firstChild);

// Fjern success-meddelelsen efter 5 sekunder
            setTimeout(function() {
                if (successMessage.parentNode) {
                    successMessage.parentNode.removeChild(successMessage);
                }
            }, 5000);

        });
    });
</script>

<!-- Skjult iframe til downloads -->
<iframe id="download-frame" name="download-frame" style="display:none;"></iframe>

</body>
</html>
