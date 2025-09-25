<?php

namespace GFUnit\cardshop\pluklister;
use GFBiz\units\UnitController;
use GFUnit\lister\rapporter\CardshopReminder;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $model = new DashboardData();
        $this->view("dashboardview",array("model" => $model));
    }

    public function plukdispatch() {
        if(!isset($_POST["action"])) $this->dashboard();
        else if($_POST["action"] == "fetch") $this->plukliste();
        else if($_POST["action"] == "presentlist") $this->presentlist();
        else if($_POST["action"] == "reminderlist") $this->reminderlist();
        else if($_POST["action"] == "reminderlistdk") $this->reminderlistCountry("c1");
        else if($_POST["action"] == "reminderlistno") $this->reminderlistCountry("c4");
        else if($_POST["action"] == "reminderlistse") $this->reminderlistCountry("c5");
        else if($_POST["action"] == "customlist") $this->customlist();
        else if($_POST["action"] == "prepaymentlist") $this->prepaymentlist();
        else if($_POST["action"] == "privatlevering") $this->privatlevering();
        else $this->dashboard();
    }


    public function reminderlistCountry($country) {

        $postCopy = $_POST;
        unset($_POST);

        $_POST = array("cardshops" => array($country),"expiredate" => $postCopy["expire"]);
        $reminderList = new CardshopReminder();
        $reminderList->generateReport(null);
    }

    public function presentshoplist() {
        $_POST["shopid"] = $_GET["shopid"];
        $report = new PresentList();
        $report->run();
    }

    public function gavecheck($lang=0) {
        $model = new GaveCheck();
        $model->run($lang);
    }

    public function plukliste() {

        $report = new PlukListe();
        $report->run();
    }

    public function presentlist() {
        $report = new PresentList();
        $report->run();
    }

    public function reminderlist() {
        $report = new ReminderList();
        $report->run();
    }

    public function customlist() {
        $report = new CompanyOrderList();
        $report->run();

    }


    public function privatlevering() {
        $report = new Privatlevering();
        $report->run();

    }

    public function prepaymentlist() {
        $report = new PrepaymentList();
        $report->run();

    }

}