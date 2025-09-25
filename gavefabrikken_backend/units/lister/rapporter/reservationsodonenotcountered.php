<?php

namespace GFUnit\lister\rapporter;

class ReservationSODoneNotCountered extends BaseReport
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getReportName() { return "Reservationer - SO ikke udlignet"; }
    public function getReportCode() { return "ReservationSODoneNotCountered"; }
    public function getReportDescription() { return "Viser alle de varenr og SOer hvor der ikke er lavet fuld udligning mod reservationer."; }

    public function defineParameters()
    {
        return array(
            "cardshops"
        );
    }

    // Genererer rapporten
    public function generateReport($parameters)
    {

        $sql = "SELECT shop.id as ShopID, shop.name as Shop, GROUP_CONCAT(sono) as SOno, itemno as Varenr, SUM(quantity) as AntalPaaSO, SUM(done) AntalUdlignet, SUM(quantity)-SUM(done) as Dif FROM `navision_reservation_done`, shop where shop.id = navision_reservation_done.shop_id && quantity-done != 0  group by shop.id, navision_reservation_done.itemno ORDER BY `navision_reservation_done`.`shop_id` ASC";

        $exporter = new ExportCSVSimple("cs-reservation-notdone.csv");
        $exporter->exportSql($sql);

    }

}
