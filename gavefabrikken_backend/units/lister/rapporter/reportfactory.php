<?php

namespace GFUnit\lister\rapporter;

class ReportFactory
{
    private $reportTypes = [
        'CardshopNotSelected',
        'CardshopGiftwrapSum',
        'CardshopReminder',
        'ReservationSODoneNotCountered',
        'ReservationSODoneIsCountered',
        'EarlyOrderReady',
        'Luksusgavekortsalg'
    ];

    public function createReport($reportType)
    {
        if (!in_array($reportType, $this->reportTypes)) {
            throw new Exception("Ugyldig rapporttype: $reportType");
        }

        switch ($reportType) {
            case 'Luksusgavekortsalg':
                return new Luksusgavekortsalg();
                break;
            case 'CardshopNotSelected':
                return new CardshopNotSelected();
                break;

            case 'CardshopGiftwrapSum':
                return new CardshopGiftwrapSum();
                break;

            case 'CardshopReminder':
                return new CardshopReminder();
                break;

            case 'ReservationSODoneNotCountered':
                return new ReservationSODoneNotCountered();
                break;

            case 'ReservationSODoneIsCountered':
                return new ReservationSODoneIsCountered();
                break;
                
            case 'EarlyOrderReady':
                return new EarlyOrderReady();
                break;                
                
            default:
                throw new Exception("Ugyldig rapporttype: $reportType");
                break;
        }
    }

    public function getAvailableReportTypes()
    {
        return $this->reportTypes;
    }

}