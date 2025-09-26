<?php

namespace GFUnit\lister\rapporter;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct() {
        parent::__construct(__FILE__);
    }

    public function gavevalg() {


        $model = new Gavevalg();
        $model->gavevalgrapport(5,"2021-05-01 00:00:00","2022-05-01 00:00:00");

    }

    public function salesperson() {

        $model = new Salesperson();
        $model->dispatch();

    }

    public function dashboard() {
        
        if(isset($_POST["action"]) && $_POST["action"] == "export") {

            try {

                $reportType = $_POST["reporttype"];
                $rf = new ReportFactory();
                $report = $rf->createReport($reportType);
                $report->generateReport($_POST);

            } catch (\Exception $e) {
                echo "<h2>Fejl i rapport</h2>";
                echo $e->getMessage();
            }
        }
        else {
            $dashboad = new Dashboard();
        }

    }


}