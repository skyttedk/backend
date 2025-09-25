<?php

namespace GFUnit\monitor\giftcertificate;
use GFBiz\units\UnitController;
use GFUnit\navision\syncprivatedelivery\ErrorCodes;

class Controller extends UnitController
{


    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function checkcertificates() {

        \GFCommon\DB\CronLog::startCronJob("GiftcertificateCheck");

        $warningList = array();
        $warningPercent = 90;
        $warningReceivers = array("sc@interactive.dk");
        $expireDates = array();

        // Get expire dates
        $expireDateList = \ExpireDate::find_by_sql("SELECT * FROM expire_date ORDER BY `expire_date`.`expire_date` ASC");
        foreach($expireDateList as $exDate) {
            $deadline = $exDate->expire_date->format("Y-m-d");
            $expireDates[] = $deadline;
        }

        // Get expire dates
        $reservationGroupList = \GiftCertificate::find_by_sql("SELECT * FROM `reservation_group`");
        $reservationGroupListWithEmail = array_merge(array(json_decode(json_encode(array("id" => 0, "name" => "E-mails")))),$reservationGroupList);

        // Stats
        $usageStats = \GFCommon\DB\GiftCertificate::getUsageStats();
        $usageMap = array();
        foreach($usageStats as $stat)
        {
            if(!isset($usageMap[$stat["reservation_group"]])) $usageMap[$stat["reservation_group"]] = array();
            if(!isset($usageMap[$stat["reservation_group"]][$stat["expire_date"]])) $usageMap[$stat["reservation_group"]][$stat["expire_date"]] = array("count" => $stat["Created"], "used" => $stat["Used"],"exported" => $stat["Exported"]);
        }

        foreach($reservationGroupListWithEmail as $reservationGroup) {
            foreach($expireDates as $date)
            {
                $stats = array();
                if(isset($usageMap[$reservationGroup->id]) && isset($usageMap[$reservationGroup->id][$date])) {
                    $stats = $usageMap[$reservationGroup->id][$date];
                }
                if(count($stats) == 0) $stats = array("count" => 0, "used" => 0,"exported" => 0);

                $cardsCreated = intval($stats["count"]);
                $cardsUsed = intval($stats["used"]);
                $usedPercentage = $cardsCreated == 0 ? 0 : intval((($cardsUsed/$cardsCreated))*100);

                $cardsExported = intval($stats["exported"]);
                $exportUsedPercentage = $cardsExported == 0 ? 0 : intval(($cardsUsed/$cardsExported)*100);

                if($cardsCreated > 0 && ($exportUsedPercentage >= $warningPercent || $usedPercentage >= $warningPercent)) {
                    $warningList[] = array($reservationGroup->name,$date,$cardsCreated,$cardsUsed,$usedPercentage,$cardsExported,$exportUsedPercentage);
                }

            }
        }

        // If any problems, generate report
        if(count($warningList) > 0 && count($warningReceivers)) {

            // Generate report
            $report = "<h2>Der er fundet ".count($warningList)." advarsler på gavekort</h2><br>";
            $report .= "<b>Kørsel:</b> ".date("d-m-Y H:i")."<br><br>";
            $report .= "<table cellpadding='5' cellspacing='5'><tr>";

            $headlines = array("Koncept","Deadline","Kort oprettet","Kort solgt","Solgt %<br>(solg ud af oprettet)","Printet","Printet %<br>(solgte ud af printet)");
            foreach($headlines as $headline) {
                $report .= "<td valign='top'><b>".$headline."</b></td>";
            }
            $report .= "</tr>";


            foreach($warningList as $warn) {

                $report .= "<tr>
                    <td>".$warn[0]."</td>
                    <td>".$warn[1]."</td>
                    <td>".$warn[2]."</td>
                    <td>".$warn[3]."</td>
                    <td>".$warn[4]."%</td>
                    <td>".$warn[5]."</td>
                     <td>".$warn[6]."%</td>
                </tr>";

            }

            $report .= "</table>";
            echo $report;

            // Send report
            foreach($warningReceivers as $mail) {
                mailgf($mail,"ADVARSEL: Tjek af antal gavekort",$report);
            }

            \GFCommon\DB\CronLog::endCronJob(2,count($warningList)." advarsler fundet");

        } else {

            \GFCommon\DB\CronLog::endCronJob(1,"OK");

        }

    }


}