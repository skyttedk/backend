<?php

namespace GFUnit\cardshop\status;
use GFBiz\units\UnitController;

class StatusDashboardModel
{

    public function __construct()
    {

    }


    /**
     * USER MANAGEMENT
     */

    public function renderLanguageBar() {

        $available = $this->getAvailableLanguages();
        $selected = $this->getSelectedLanguages();

        foreach($available as $lang) {
            $active = in_array($lang, $selected);
            $text = $this->getLangCodeToText()[$lang];
            $flag = $this->getLangCodeToFlag()[$lang];
            echo "<div class=\"country-flag ".($active ? "country-flag-selected" : "")."\" data='".$lang."'>".$flag." <span>$text</span></div>";
        }
    }

    public function getLangCodeToFlag() {
        return [
            1 => "DK",
            2 => "EN",
            3 => "DE",
            4 => "NO",
            5 => "SE",
        ];
    }

    public function getLangCodeToText() {
        return [
            1 => "Danmark",
            2 => "England",
            3 => "Tyskland",
            4 => "Norge",
            5 => "Sverige",
        ];

    }

    public function getAvailableLanguages() {

        $langList = array(\router::$systemUser->language);
        if($this->isSC()) {
            $langList[] = 4;
            $langList[] = 5;
        }

        return $langList;

    }

    public function getSelectedLanguages() {

        $available = $this->getAvailableLanguages();
        $selected = [];
        if(isset($_GET["lang"])) {
            $langlist = explode("-",$_GET["lang"]);

            foreach($langlist as $lang) {
                if(in_array($lang, $available)) {
                    $selected[] = $lang;
                }
            }

        }

        if(count($selected) == 0) {
            $selected = $available;
        }

        return $selected;

    }

    public function isSC() {
        return \router::$systemUser->id == 50;
    }


    /**
     * TOOLS SECTION
     */
    private function createToolLink($url, $icon, $name,$title="") {
        return sprintf(
            '<a href="%s" class="tool-link" title="%s">
                <span class="icon">%s</span>
                <span>%s</span>
            </a>',
            $url,
            $title,
            $icon,
            $name
        );
    }

    public function renderToolsContainer() {
        echo '<div class="tools-container">';

        if($this->isSC()) {
            echo $this->createToolLink(\GFConfig::BACKEND_URL.'/index.php?rt=unit/development/syncdashboard', '🔄', 'CS Synkronisering',"Synkroniseringsværktøj, kunder, order og forsendelser til navision.");
            echo $this->createToolLink(\GFConfig::BACKEND_URL.'/index.php?rt=unit/tools/wizard/dashboard', '⚙️', 'Wiz tools','Interne værktøjer til udvikling og supportsager');
            echo $this->createToolLink(\GFConfig::BACKEND_URL.'/index.php?rt=unit/navision/afslutning/dashboard', '⚙️', 'CS Afslutning','Afslutningsværktøj til cardshop ordre');
        }
        
        echo $this->createToolLink(\GFConfig::BACKEND_URL.'/index.php?rt=unit/cardshop/status/toolshopsetup', '⚙️', 'Shop opsætninger','Liste med alle cardshop ordre');
        echo $this->createToolLink(\GFConfig::BACKEND_URL.'/index.php?rt=unit/cardshop/reports/orderlist', '📦', 'Ordrelister','Liste med alle cardshop ordre');
        echo $this->createToolLink(\GFConfig::BACKEND_URL.'/index.php?rt=unit/cardshop/reports/earlyorderlist', '📦', 'Earlyorderlist','Liste med alle earlyordre');

        echo $this->createToolLink(\GFConfig::BACKEND_URL.'/index.php?rt=unit/cardshop/pluklister/index', '📦', 'Pluklister','Hent pluklister til cardshop');
        echo $this->createToolLink('https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/lister/efterlevering/index', '📦', 'Efterleveringslister','Efterleveringslister');
        echo $this->createToolLink('https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/cardshop/shipblocktool/', '🔍', 'Leverance fejlliste','Fejlliste til leverancer.');

        echo $this->createToolLink("https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/cardshop/status/toolfreightmatrix", "📦", "Fragtmatricer");

        echo $this->createToolLink(\GFConfig::BACKEND_URL.'/index.php?rt=unit/lister/reportcenter/dashboard', '📊', 'Rapporter','Rapporteringscenter med rapporter');

        /*
        // Her kan du tilføje dine brugerrettigheds-tjek før hver linje
        echo $this->createToolLink('#', '', 'Sync Manager');
        echo $this->createToolLink('#', '📊', 'Rapporterz');
        echo $this->createToolLink('#', '⚙️', 'Konfiguration');
        echo $this->createToolLink('#', '🔍', 'Log Søgning');
        echo $this->createToolLink('#', '👤', 'Brugeradministration');
        echo $this->createToolLink('#', '📦', 'Produkt Administration');
        echo $this->createToolLink('#', '💰', 'Betalingsmodul');
        echo $this->createToolLink('#', '🚚', 'Forsendelsesadministration');
        */

        echo '</div>';
    }

    public function renderMetrics()
    {

        $metrics = new StatusMetrics();
        $metrics->generateMetrics($this->getSelectedLanguages());

    }


    public function renderDistribution()
    {
        $metrics = new StatusDistributions();
        $metrics->generateDistributions($this->getSelectedLanguages());

    }

    public function renderShopList()
    {
        $metrics = new StatusShopList();
        $metrics->generateShopList($this->getSelectedLanguages());
    }


}
