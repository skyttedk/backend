<?php

namespace GFUnit\navision\syncreservations;

use ActiveRecord\DateTime;
use GFCommon\DB\DBAccess;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\OrderWS;

class BalanceCheck
{


    public function runCheck() {

        return;
        \GFCommon\DB\CronLog::startCronJob("ReservationBalanceCheck");

        echo "RUN BALANCE CHECK2!";

        // Load items
        echo "Looking up reservations...<br>";
        $reservations = $this->getReservations();
        echo "Found ".count($reservations)." reservations.<br>";
        $navDeltaMap = array();


        // Check reservations and print if total_balance is different from total_delta
        $errors = array();
        foreach($reservations as $index => $reservation) {

            $reservation["location"] = trimgf($reservation["location"]);
            $reservation["language_id"] = intval($reservation["language_id"]);
            $reservation["itemno"] = strtoupper(trimgf($reservation["itemno"]));

            if($reservation["total_balance"] != $reservation["total_delta"]) {
                echo "<p>Error on reservation for item: ".$reservation["itemno"]." on shop ".$reservation["shop_id"]." at location: ".$reservation["location"]." on lang ".$reservation["language_id"].". Total balance: ".$reservation["total_balance"].". Total delta: ".$reservation["total_delta"].".<br>SELECT * FROM `navision_reservation_log` WHERE `language_id` = 1 AND `itemno` LIKE '".$reservation["itemno"]."' AND `location` LIKE '".$reservation["location"]."' && shop_id = ".$reservation["shop_id"]." ORDER BY `navision_reservation_log`.`shop_id` ASC;</p>";
                $errors[] = "Error on reservation for item: ".$reservation["itemno"]." on shop ".$reservation["shop_id"]." at location: ".$reservation["location"]." on lang ".$reservation["language_id"].". Total balance: ".$reservation["total_balance"].". Total delta: ".$reservation["total_delta"].".";
            }

            // Make sure indexes are set in navDeltaMap
            if(!isset($navDeltaMap[intval($reservation["language_id"])])) $navDeltaMap[intval($reservation["language_id"])] = array();
            if(!isset($navDeltaMap[intval($reservation["language_id"])][trimgf($reservation["location"])])) $navDeltaMap[intval($reservation["language_id"])][trimgf($reservation["location"])] = array();
            if(!isset($navDeltaMap[intval($reservation["language_id"])][trimgf($reservation["location"])][$reservation["itemno"]])) $navDeltaMap[intval($reservation["language_id"])][trimgf($reservation["location"])][strtoupper(trimgf($reservation["itemno"]))] = 0;
            $navDeltaMap[intval($reservation["language_id"])][trimgf($reservation["location"])][strtoupper(trimgf($reservation["itemno"]))] += $reservation["total_delta"];

            //if(in_array(strtoupper(trimgf($reservation["itemno"])),array("30-LGWINESET"))) {
                //echo "<p>DUMP: <pre>".print_r($reservation,true)."<br>".intval($reservation["language_id"])."-".trimgf($reservation["location"])."-".strtoupper(trimgf($reservation["itemno"]))."<br>".print_r($navDeltaMap[intval($reservation["language_id"])][trimgf($reservation["location"])][strtoupper(trimgf($reservation["itemno"]))],true)."</br></p>";
            //}

        }

        // On errors mail and exit
        if(count($errors)) {
            $this->mailLog("Fandt ".count($errors)." fejl ved delta tjek ".implode("<br><br>", $errors));
           echo "Found ".count($errors)." errors"; exit();
        }


        $navCheckCount = 0;
        foreach($navDeltaMap as $language_id => $locationMap) {
            foreach ($locationMap as $location => $itemMap) {
                foreach ($itemMap as $itemno => $delta) {
                    $navCheckCount++;
                }
            }
        }

        echo "Nav checks to do: ".$navCheckCount."<br>";

        $maxChecks = 2500;
        $maxTime = 40;
        $navStart = time();

        $navisionErrors = array();
        $startTime = time();

        echo "<br><br>Checking reservations in navision...<br>";

        $count = 0;
        // For each delta in navDeltaMap
        foreach($navDeltaMap as $language_id => $locationMap) {
            foreach($locationMap as $location => $itemMap) {
                foreach($itemMap as $itemno => $delta) {

                    if($maxChecks < $count) {
                        echo "Max checks reached, exiting HARD";
                        exit();
                    }


                    if(time()-$navStart > $maxTime+10) {
                        echo "Reached max time, exiting HARD";
                        exit();
                    }

                    // Rand number max 100
                    $rand = rand(0,100);

                    if($rand > 80) {

                        $count++;
                        try {
                            echo "CHECK  ".$itemno." at ".$location." on lang ".$language_id." with delta ".$delta."<br>";
                            $navisionBalance = $this->getItemBalance($itemno, $location,$language_id);
                            if($navisionBalance != $delta) {
                                echo "<p>Error on reservation for item: ".$itemno." at location: ".$location." on lang ".$language_id.". Total balance: ".$delta.". Navision balance: ".$navisionBalance." (diff: ".($navisionBalance-$delta).").</p>";
                                $navisionErrors[] = "Error on reservation for item: ".$itemno." at location: ".$location." on lang ".$language_id.". Total balance: ".$delta.". Navision balance: ".$navisionBalance." (diff: ".($navisionBalance-$delta).").";
                            }
                        } catch (\Exception $e) {
                            if($delta != 0) {
                                echo "<p>Service error ".$language_id.": ".$e->getMessage().": ".$itemno." has local balance of ".$delta."</p>";
                                $navisionErrors[] = "Service error on ".$language_id.": ".$e->getMessage().": ".$itemno." has local balance of ".$delta."<br>";
                            }
                        }

                    }

                    if($maxChecks <= $count) {
                        echo "Max checks reached, exiting";
                       break 3;
                    }

                    if(time()-$navStart > $maxTime) {
                        echo "Reached max time, exiting";
                        break 3;
                    }

                }
            }
        }

        echo "DONE CHECKING NAV!<br>";

        // Mail home with number of errors, error messages and run-time
        if(count($navisionErrors) > 0 || time()-$startTime > 60*60*2) {
            $this->mailLog("Fandt ".count($navisionErrors)." fejl ved navision tjek ".implode("<br><br>", $navisionErrors)."<br>Runtime: ".(time() - $startTime)." seconds");
        }

        echo "Found ".count($navisionErrors)." errors";

        \GFCommon\DB\CronLog::endCronJob(count($navisionErrors) > 0 ? 3 : 1,count($navisionErrors)." fejl fundet i check");

/*
        echo "<br><br>Checking reservations in navision...<br>";

        // Check reservations in navision
        foreach($reservations as $reservation) {

            try {
                $navisionBalance = $this->getItemBalance($reservation["itemno"], $reservation["location"],$reservation["language_id"]);
                if($navisionBalance != $reservation["total_balance"]) {
                    echo "Error on reservation for item: ".$reservation["itemno"]." at location: ".$reservation["location"].". Total balance: ".$reservation["total_balance"].". Navision balance: ".$navisionBalance.".<br>";
                    $navisionErrors[] = "Error on reservation for item: ".$reservation["itemno"]." at location: ".$reservation["location"].". Total balance: ".$reservation["total_balance"].". Navision balance: ".$navisionBalance.".";
                }
            } catch (\Exception $e) {
                $navisionErrors[] = "Service error: ".$e->getMessage()."<br>";
            }
        }

        echo "DONE CHECKING NAV!<br>";

        // Mail home with number of errors, error messages and run-time
        if(count($navisionErrors) > 0 || time()-$startTime > 60*60*2) {
            $this->mailLog("Fandt ".count($navisionErrors)." fejl ved navision tjek ".implode("<br><br>", $navisionErrors)."<br>Runtime: ".(time() - $startTime)." seconds");
        }

        echo "Found ".count($navisionErrors)." errors";
*/
    }

    private function getReservations() {

        /*
        $sql = "SELECT nrl_summary.itemno, nrl_summary.location, nrl_summary.total_balance, delta_summary.total_delta
FROM (
    SELECT nrl.itemno, nrl.location, SUM(nrl.balance) as total_balance
    FROM navision_reservation_log nrl
    INNER JOIN (
        SELECT itemno, location, shop_id, MAX(created) as latest_created
        FROM navision_reservation_log
        GROUP BY itemno, location, shop_id
    ) latest_entries
    ON nrl.shop_id = latest_entries.shop_id
    AND nrl.itemno = latest_entries.itemno
    AND nrl.location = latest_entries.location
    AND nrl.created = latest_entries.latest_created
    GROUP BY nrl.itemno, nrl.location
) nrl_summary
JOIN (
    SELECT itemno, location, SUM(delta) as total_delta
    FROM navision_reservation_log
    GROUP BY itemno, location
) delta_summary
ON nrl_summary.itemno = delta_summary.itemno AND nrl_summary.location = delta_summary.location;";
*/
/*
        // Same call where shop_reservation_state = 1 is checked
        $sql = "SELECT nrl_summary.language_id, nrl_summary.itemno, nrl_summary.location, nrl_summary.total_balance, delta_summary.total_delta
FROM (
    SELECT nrl.language_id, nrl.itemno, nrl.location, SUM(nrl.balance) as total_balance
    FROM navision_reservation_log nrl
    INNER JOIN (
        SELECT navision_reservation_log.language_id, itemno, location, shop_id, MAX(created) as latest_created
        FROM navision_reservation_log
        INNER JOIN shop ON navision_reservation_log.shop_id = shop.id
        WHERE shop.reservation_state = 1
        GROUP BY itemno, location, shop_id
    ) latest_entries
    ON nrl.shop_id = latest_entries.shop_id
    AND nrl.itemno = latest_entries.itemno
           AND nrl.language_id = latest_entries.language_id
    AND nrl.location = latest_entries.location
    AND nrl.created = latest_entries.latest_created
    GROUP BY nrl.itemno, nrl.location
) nrl_summary
JOIN (
    SELECT navision_reservation_log.language_id, itemno, location, SUM(delta) as total_delta
    FROM navision_reservation_log
    INNER JOIN shop ON navision_reservation_log.shop_id = shop.id
    WHERE shop.reservation_state = 1
    GROUP BY itemno, location
) delta_summary
ON nrl_summary.language_id = delta_summary.language_id AND nrl_summary.itemno = delta_summary.itemno AND nrl_summary.location = delta_summary.location;
";
*/

        $sql = "SELECT nrl_summary.language_id, nrl_summary.itemno, nrl_summary.location, nrl_summary.shop_id, nrl_summary.total_balance, delta_summary.total_delta
FROM (
    SELECT nrl.language_id, nrl.itemno, nrl.location, nrl.shop_id, nrl.balance as total_balance
    FROM navision_reservation_log nrl
    INNER JOIN (
        SELECT navision_reservation_log.language_id, itemno, location, shop_id, MAX(created) as latest_created
        FROM navision_reservation_log
        INNER JOIN shop ON navision_reservation_log.shop_id = shop.id
        WHERE shop.reservation_state = 1
        GROUP BY itemno, location, shop_id, language_id
    ) latest_entries
    ON nrl.shop_id = latest_entries.shop_id
    AND nrl.itemno = latest_entries.itemno
    AND nrl.language_id = latest_entries.language_id
    AND nrl.location = latest_entries.location
    AND nrl.created = latest_entries.latest_created
) nrl_summary
JOIN (
    SELECT navision_reservation_log.language_id, itemno, location, shop_id, SUM(delta) as total_delta
    FROM navision_reservation_log
    INNER JOIN shop ON navision_reservation_log.shop_id = shop.id
    WHERE shop.reservation_state = 1
    GROUP BY itemno, location, shop_id, language_id
) delta_summary
ON nrl_summary.language_id = delta_summary.language_id 
AND nrl_summary.itemno = delta_summary.itemno 
AND nrl_summary.location = delta_summary.location
AND nrl_summary.shop_id = delta_summary.shop_id";

        $list = array();
        $reservationObjects = \PresentReservation::find_by_sql($sql);
        foreach($reservationObjects as $reservation) {
            $list[] = $reservation->attributes();
        }

        return $list;

    }
    
    

    /**
     * @return int
     */
    private function getItemBalance($itemno,$location,$language_id) {
        $client = new OrderWS($language_id);
        $success = $client->getReservationBalance($itemno,$location);
        if($success) {
            return $client->getLastReservationBalance();
        } else {
            var_dump($success);
            return throw new \Exception("Error getting reservation balance for item: ".$itemno." at location: ".$location);
        }
    }


    protected function mailLog($message) {
        $modtager = 'sc@interactive.dk';
        mailgf($modtager, "Reservation check error", $message."\r\n<br>\r\n<br>\r\n<br>\r\n<br><pre></pre>");
    }

}
