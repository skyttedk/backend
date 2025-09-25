<?php

namespace GFUnit\lister\ReportCenter;

/**
 * ReportCenter
 *
 * Central klasse for hele rapporteringssystemet. Denne klasse fungerer som
 * en kombination af registry og factory, samt giver nem adgang til at registrere
 * og håndtere rapporter.
 */
class ReportCenter
{
    /**
     * Singleton instance
     */
    private static ?self $instance = null;

    /**
     * Mapping af rapport-koder til klassenavne
     * @var array<string, string>
     */
    private array $reportClasses = [];

    /**
     * Cache af rapportinstanser
     * @var array<string, AbstractReport>
     */
    private array $reportInstances = [];

    /**
     * Privat konstruktør (singleton pattern)
     */
    private function __construct()
    {
        // Registrer standard rapporter ved opstart
        $this->registerDefaultReports();
    }

    /**
     * Henter singleton-instansen
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Resolve userRoles
     */

    public function getUserRoles() :array
    {

        $systemUserID = \router::$systemUser->id;
        $userRoles = array();

        // Admins
        if(in_array($systemUserID, array(50,304,305,5,51,86,147,153,223,225,300))) {
            $userRoles[] = 'admin';
        }

        return $userRoles;
    }

    public function getCountryName() {

        // Find country
        $countryName = "UKENDT";
        $langNames = array(0 => "Alle lande",1 => "Dannmark", 4 => "Norge",5 => "Sverige");
        if(isset($langNames[\router::$systemUser->language_id])) {
            $countryName = $langNames[\router::$systemUser->language_id];
        }
        return $countryName;

    }


    /**
     * Registrer standard rapporter. Dette er den centrale metode, hvor alle
     * rapporter skal registreres - gør det meget nemt at tilføje nye rapporter.
     */
    private function registerDefaultReports(): void
    {
        // Her tilføjer du alle dine rapporter
        // Kun ét sted at vedligeholde, når du tilføjer nye rapporter!

        // Eksempel:
         //$this->registerReport(Reports\CardshopReminderReport::class);
        // $this->registerReport(Reports\FinancialReport::class);

        // Alternativ måde med auto-discovery fra en mappe:
        $this->discoverAndRegisterReports();
    }

    /**
     * Automatisk opdagelse og registrering af rapporter fra en bestemt mappe.
     * Dette gør det endnu nemmere at tilføje nye rapporter - du skal blot
     * placere dem i rapporten-mappen, og de bliver automatisk registreret.
     */
    private function discoverAndRegisterReports(): void
    {
        // Definér stien til rapportmappen
        $reportsDir = __DIR__ . '/reports';

        // Hvis mappen findes, gennemgå alle PHP-filer
        if (is_dir($reportsDir)) {
            $files = glob($reportsDir . '/*.php');

            foreach ($files as $file) {
                                
                // Udled klassenavn fra filnavn
                $className = basename($file, '.php');
                $fullyQualifiedClassName = __NAMESPACE__ . '\\Reports\\' . $className;

                // Tjek om klassen eksisterer og er en rapport
                if (class_exists($fullyQualifiedClassName) &&
                    is_subclass_of($fullyQualifiedClassName, AbstractReport::class)) {
                    $this->registerReport($fullyQualifiedClassName);
                }
            }
        }
    }

    /**
     * Registrerer en rapport manuelt
     *
     * @param string $reportClass Fuldt kvalificeret klassenavn
     * @return bool True hvis registreringen lykkedes
     */
    public function registerReport(string $reportClass): bool
    {
        try {
            // Valider klassen
            if (!class_exists($reportClass)) {
                throw new \InvalidArgumentException("Rapportklasse '{$reportClass}' eksisterer ikke");
            }

            if (!is_subclass_of($reportClass, AbstractReport::class)) {
                throw new \InvalidArgumentException("Klasse '{$reportClass}' skal nedarve fra AbstractReport");
            }

            // Opret en instans for at få koden
            $instance = new $reportClass();
            $code = $instance->getCode();

            // Tjek for duplikater
            if (isset($this->reportClasses[$code])) {
                throw new \InvalidArgumentException("En rapport med koden '{$code}' er allerede registreret");
            }

            // Registrer rapporten
            $this->reportClasses[$code] = $reportClass;
            $this->reportInstances[$code] = $instance; // Cache første instans

            return true;
        } catch (\Exception $e) {
            // Log fejlen
            error_log("Kunne ikke registrere rapport: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Afregistrerer en rapport
     *
     * @param string $code Rapportkoden
     */
    public function unregisterReport(string $code): void
    {
        unset($this->reportClasses[$code]);
        unset($this->reportInstances[$code]);
    }

    /**
     * Tjekker om en rapport findes
     *
     * @param string $code Rapportkoden
     * @return bool True hvis rapporten findes
     */
    public function hasReport(string $code): bool
    {
        return isset($this->reportClasses[$code]);
    }

    /**
     * Henter en rapportinstans baseret på kode
     *
     * @param string $code Rapportkoden
     * @param bool $useCache Brug cache hvis muligt
     * @return AbstractReport Rapportinstansen
     * @throws \InvalidArgumentException Hvis rapporten ikke findes
     */
    public function getReport(string $code, bool $useCache = true): AbstractReport
    {
        if (!$this->hasReport($code)) {
            throw new \InvalidArgumentException("Ukendt rapportkode: '{$code}'");
        }

        if ($useCache && isset($this->reportInstances[$code])) {
            return $this->reportInstances[$code];
        }

        $className = $this->reportClasses[$code];
        $report = new $className();

        if ($useCache) {
            $this->reportInstances[$code] = $report;
        }

        return $report;
    }

    /**
     * Henter alle tilgængelige rapporter
     *
     * @param bool $useCache Brug cache hvis muligt
     * @return AbstractReport[] Array af rapportinstanser
     */
    public function getAllReports(bool $useCache = true): array
    {
        $reports = [];
        foreach (array_keys($this->reportClasses) as $code) {
            $reports[] = $this->getReport($code, $useCache);
        }
        return $reports;
    }

    /**
     * Henter alle rapporter, filtreret efter brugerens roller
     *
     * @param array $userRoles Brugerens roller
     * @param bool $useCache Brug cache hvis muligt
     * @return AbstractReport[] Array af rapportinstanser
     */
    public function getReportsForRoles(array $userRoles, bool $useCache = true): array
    {
        // Hvis 'admin' er i rollerne, returner alle rapporter
        if (in_array('admin', $userRoles)) {
            return $this->getAllReports($useCache);
        }

        // Ellers filtrer rapporter baseret på roller
        $reports = [];
        foreach (array_keys($this->reportClasses) as $code) {
            $report = $this->getReport($code, $useCache);

            // Tjek om brugeren har adgang til rapporten
            $hasAccess = false;
            foreach ($report->getAllowedRoles() as $role) {
                if (in_array($role, $userRoles)) {
                    $hasAccess = true;
                    break;
                }
            }

            if ($hasAccess) {
                $reports[] = $report;
            }
        }

        return $reports;
    }

    /**
     * Genererer en rapport baseret på kode og parametre
     *
     * @param string $code Rapportkoden
     * @param array $parameters Rapportparametre
     * @return ReportResult Rapportresultatet
     * @throws \InvalidArgumentException Hvis rapporten ikke findes
     */
    public function generateReport(string $code, array $parameters): ReportResult
    {
        $report = $this->getReport($code);
        return $report->generateReport($parameters);
    }

    /**
     * Eksporterer en rapport til CSV
     *
     * @param ReportResult $result Rapportresultatet
     * @param string $filename Filnavn (uden extension)
     */
    public function exportToCsv(ReportResult $result, string $filename): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $fp = fopen('php://output', 'w');
        fputs($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        // Få data direkte via reflection (kun for debugging)
        $reflection = new \ReflectionClass($result);
        $dataProperty = $reflection->getProperty('data');
        $dataProperty->setAccessible(true);
        $columnsProperty = $reflection->getProperty('columns');
        $columnsProperty->setAccessible(true);

        $rawData = $dataProperty->getValue($result);
        $columns = $columnsProperty->getValue($result);

        // Skriv kolonneoverskrifter
        fputcsv($fp, array_values($columns), ';');

        // Skriv data med rå tilgang
        foreach ($rawData as $row) {
            $values = [];
            foreach ($row as $column) {
                $values[] = $column;
            }
            fputcsv($fp, $values, ';');
        }

        fclose($fp);
        exit;
    }

    /**
     * Eksporterer en rapport til Excel
     *
     * @param ReportResult $result Rapportresultatet
     * @param string $filename Filnavn (uden extension)
     */
    public function exportToExcel(ReportResult $result, string $filename): void
    {
        // Du kan implementere Excel-eksport her, hvis du har PhpSpreadsheet eller lignende
        // For simplicitet bruger vi bare CSV-eksport i dette eksempel
        $this->exportToCsv($result, $filename);
    }

    /**
     * Eksporterer en rapport til det angivne format
     *
     * @param ReportResult $result Rapportresultatet
     * @param string $format Eksportformat ('csv' eller 'excel')
     * @param string|null $filename Filnavn (uden extension)
     */
    public function exportReport(ReportResult $result, string $format, ?string $filename = null): void
    {
        // Generer filnavn hvis ikke angivet
        if ($filename === null) {
            $filename = $result->getTitle() . '_' . date('Y-m-d');
        }

        // Fjern ulovlige tegn fra filnavn
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);

        // Eksporter til det angivne format
        switch (strtolower($format)) {
            case 'excel':
                $this->exportToExcel($result, $filename);
                break;

            case 'csv':
            default:
                $this->exportToCsv($result, $filename);
                break;
        }
    }

    /**
     * Henter alle rapporter grupperet efter kategori
     *
     * @param array $userRoles Brugerens roller
     * @param bool $useCache Brug cache hvis muligt
     * @return array Rapporter grupperet efter kategori
     */
    public function getReportsByCategory(array $userRoles, bool $useCache = true): array
    {
        $reports = $this->getReportsForRoles($userRoles, $useCache);
        $categorized = [];

        foreach ($reports as $report) {
            $category = $report->getCategory();
            if (!isset($categorized[$category])) {
                $categorized[$category] = [];
            }
            $categorized[$category][] = $report;
        }

        // Sorter kategorierne alfabetisk
        ksort($categorized);

        return $categorized;
    }

    /**
     * Henter antal registrerede rapporter
     *
     * @return int Antal rapporter
     */
    public function getReportCount(): int
    {
        return count($this->reportClasses);
    }

    /**
     * Rydder rapportcachen
     */
    public function clearCache(): void
    {
        $this->reportInstances = [];
    }
}


