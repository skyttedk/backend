<?php

namespace GFUnit\lister\reportcenter;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct() {
        parent::__construct(__FILE__);
    }



    public function dashboard() {

        $this->view("dashboard");

    }

    public function download()
    {
        try {

            $reportCenter = ReportCenter::getInstance();

            // Hent rapportkode og format fra POST
            $reportCode = $_POST['report_code'] ?? '';
            $format = 'csv';

            // Tjek om brugeren har adgang (her bruges admin som standard)
            $userRoles = $reportCenter->getUserRoles();

            // Generer og eksporter rapporten
            $report = $reportCenter->getReport($reportCode);
            $parameters = $report->processParameters($_POST);
            $result = $reportCenter->generateReport($reportCode, $parameters);
            $reportCenter->exportReport($result, $format, $reportCode . '_' . date('Ymd'));


        } catch (\Exception $e) {
            // Ved fejl, redirect tilbage til dashboard med fejlbesked
            $_SESSION['report_error'] = $e->getMessage();
            echo $e->getMessage();
            var_dump($e);
            exit;
        }
    }


}