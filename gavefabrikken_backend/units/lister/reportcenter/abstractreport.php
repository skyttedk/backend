<?php

namespace GFUnit\lister\reportcenter;

abstract class AbstractReport
{
    /**
     * Unikke kode for rapporten, bruges til identifikation
     */
    protected string $code;

    /**
     * Rapportens navn, vises i brugergrænsefladen
     */
    protected string $name;

    /**
     * Beskrivelse af rapporten, vises i brugergrænsefladen
     */
    protected string $description;

    protected string $category = 'Generel'; // Standard kategori hvis ikke angivet


    /**
     * Array af roller der har adgang til rapporten
     */
    protected array $allowedRoles = [];

    /**
     * Array af standardparametre som rapporten kræver
     * F.eks.: ['cardshops', 'expiredate', 'date_range']
     */
    protected array $requiredParameters = [];

    /**
     * Returnerer rapportens unikke kode
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Henter rapportens kategori
     */
    public function getCategory(): string
    {
        return $this->category;
    }
    
    /**
     * Returnerer rapportens navn
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returnerer rapportens beskrivelse
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returnerer de roller, der har adgang til rapporten
     */
    public function getAllowedRoles(): array
    {
        return $this->allowedRoles;
    }

    /**
     * Returnerer de standardparametre, som rapporten kræver
     */
    public function getRequiredParameters(): array
    {
        return $this->requiredParameters;
    }

    /**
     * Renderer HTML for rapportens standardparametre
     */
    public function renderStandardParameters(): string
    {
        $html = '';
        foreach ($this->getRequiredParameters() as $param) {
            $html .= $this->renderParameter($param);
        }
        return $html;
    }

    /**
     * Renderer HTML for rapportens specialparametre (hvis nogen)
     * Skal overskrives af konkrete rapporter med specialparametre
     */
    public function renderSpecialParameters(): string
    {
        return '';
    }

    /**
     * Tjekker om rapporten har specialparametre
     */
    public function hasSpecialParameters(): bool
    {
        return $this->renderSpecialParameters() !== '';
    }


    protected function exportSql($sql,$columns=null,$postProcessCallback=null) {

        $data = \Dbsqli::GetSql2($sql);

        foreach($data as $key=>$row) {
            if ($postProcessCallback) {
                $data[$key] = $postProcessCallback($row);
            }
        }


        // Check if $data is empty
        if (empty($data)) {
            return new ReportResult([], [], $this->name);
        }
        
        return new ReportResult($data, $columns == null ? array_keys($data[0]) : $columns, $this->name);

    }

    /**
     * Renderer HTML for en specifik parameter
     */
    protected function renderParameter(string $paramCode): string
    {
        switch ($paramCode) {
            case 'date_range':
                return $this->renderDateRangeParameter();
            case 'cardshops':
                return ParameterInputs::CSShopSelect();
            case 'expiredates':
                return ParameterInputs::CSExpireDatesSelect();
            case 'expiredate':
                return ParameterInputs::CSExpireDateSelect();
            default:
                return '';
        }
    }

    /**
     * Validerer parametre fra POST-data
     */
    public function validateParameters(array $postData): bool
    {
        // Validér standardparametre
        foreach ($this->getRequiredParameters() as $param) {
            if (!$this->validateParameter($param, $postData)) {
                return false;
            }
        }

        // Validér specialparametre (skal implementeres af konkrete rapporter)
        return $this->validateSpecialParameters($postData);
    }

    /**
     * Validerer en specifik parameter
     */
    protected function validateParameter(string $paramCode, array $postData): bool
    {
        switch ($paramCode) {
            case 'date_range':
                return isset($postData['date_range_from'], $postData['date_range_to']) &&
                    strtotime($postData['date_range_from']) !== false &&
                    strtotime($postData['date_range_to']) !== false;

            case 'cardshops':
                return isset($postData['cardshops']) &&
                    (is_array($postData['cardshops']) ? count($postData['cardshops']) > 0 : trim($postData['cardshops']) !== '');

            case 'expiredate':
                return isset($postData['expiredate']) && trim($postData['expiredate']) !== '';

            // Valider andre standardparametre på samme måde
            default:
                // For andre parametre, tjek blot om de findes
                return isset($postData[$paramCode]);
        }
    }

    /**
     * Validerer specialparametre (skal implementeres af konkrete rapporter)
     */
    protected function validateSpecialParameters(array $postData): bool
    {
        return true; // Standard er at der ikke er specialparametre at validere
    }

    /**
     * Processerer parametre fra POST-data til et array der kan bruges i rapporten
     */
    public function processParameters(array $postData): array
    {
        $processed = [];

        // Process standardparametre
        foreach ($this->getRequiredParameters() as $param) {
            $processed[$param] = $this->processParameter($param, $postData);
        }

        // Process specialparametre (skal implementeres af konkrete rapporter)
        $specialParams = $this->processSpecialParameters($postData);
        if (!empty($specialParams)) {
            $processed = array_merge($processed, $specialParams);
        }

        return $processed;
    }

    /**
     * Processerer en specifik parameter
     */
    protected function processParameter(string $paramCode, array $postData)
    {
        switch ($paramCode) {
            case 'date_range':
                return [
                    'from' => $postData['date_range_from'] ?? date('Y-m-d', strtotime('-30 days')),
                    'to' => $postData['date_range_to'] ?? date('Y-m-d')
                ];

            case 'cardshops':
                // Konverter til array hvis det er en kommasepareret streng
                if (isset($postData['cardshops']) && !is_array($postData['cardshops'])) {
                    return explode(',', $postData['cardshops']);
                }
                return $postData['cardshops'] ?? [];

            case 'expiredate':
                return $postData['expiredate'] ?? '';

            // Process andre standardparametre på samme måde
            default:
                return $postData[$paramCode] ?? null;
        }
    }

    /**
     * Processerer specialparametre (skal implementeres af konkrete rapporter)
     */
    protected function processSpecialParameters(array $postData): array
    {
        return []; // Standard er at der ikke er specialparametre at processere
    }

    /**
     * Renderer HTML for datointerval-parameter
     */
    protected function renderDateRangeParameter(): string
    {
        $fromDate = date('Y-m-d', strtotime('-30 days'));
        $toDate = date('Y-m-d');

        return <<<HTML
    <div class="form-group date-range-group">
        <label for="date_range_from">Datointerval</label>
        <div class="date-range">
            <input type="date" id="date_range_from" name="date_range_from" class="form-control" value="{$fromDate}">
            <span class="date-separator">til</span>
            <input type="date" id="date_range_to" name="date_range_to" class="form-control" value="{$toDate}">
        </div>
    </div>
    HTML;
    }


    /**
     * Genererer rapporten baseret på de processerede parametre
     * Skal implementeres af konkrete rapporter
     */
    abstract public function generateReport(array $parameters): ReportResult;
}
