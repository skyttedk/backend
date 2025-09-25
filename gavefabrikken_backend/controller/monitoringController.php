<?php
class monitoringController extends baseController
{
    public function Index()
    {

    }

    public function checkcards()
    {

        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $isMonitor = false;
        $monitorWarn = false;

        if($token == "dfkj4jhkfhj3jddncnk44") { $isMonitor = true; }
        else if($token == "dkfj3k4nnjdjkfdsjdf") { $isMonitor = false; }
        else { echo "No access"; exit(); }

        // FIND PROBLEM ORDERS: SELECT * FROM `company_order` WHERE certificate_no_end-certificate_no_begin+1 != quantity
        $cardlist = GiftCertificate::find_by_sql("SELECT count(id) as cardcount, reservation_group, expire_date FROM `gift_certificate` WHERE shop_id = 0 group by reservation_group, expire_date having cardcount < 200");
        if(count($cardlist) > 0) {
            foreach($cardlist as $cards) {

                $cardcount = $cards->cardcount;
                $reservationGroup = $cards->reservation_group;
                $expireDate = $cards->expire_date;

                if($isMonitor) $monitorWarn = true;
                else echo "<h2>WARNING</h2>";

                if(!$isMonitor) {
                    echo "MISSING CARDS: resgroup ".$reservationGroup." - date ".$expireDate->format('d-m-Y')." - cards left ".$cardcount."<br><br>";
                }

            }
        }
        else
        {
            if(!$isMonitor) echo "<h3>OK</h3>";
        }

        if($isMonitor) {
            if($monitorWarn)
            {
                header("HTTP/1.0 500 Service down");
                echo "ERROR";
                exit();
            }
            else echo "OK";
        }

    }

    public function checkintervals()
    {

        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $isMonitor = false;
        $monitorWarn = false;

        if($token == "dfkj4jhkfhj3jddncnk44") { $isMonitor = true; }
        else if($token == "dkfj3k4nnjdjkfdsjdf") { $isMonitor = false; }
        else { echo "No access"; exit(); }

        // FIND PROBLEM ORDERS: SELECT * FROM `company_order` WHERE certificate_no_end-certificate_no_begin+1 != quantity
        $intervals = GiftCertificate::find_by_sql("SELECT count(id) as cardcount, max(certificate_no)-min(certificate_no)+1 as intervalcount, min(certificate_no) as intstart, max(certificate_no) as intend, reservation_group, expire_date FROM `gift_certificate` WHERE shop_id = 0 group by reservation_group, expire_date having cardcount != intervalcount");
        foreach($intervals as $interval) {

            $cardlist = GiftCertificate::find_by_sql("SELECT * FROM gift_certificate WHERE shop_id = 0 && reservation_group = ".$interval->reservation_group." && expire_date = '".$interval->expire_date->format('Y-m-d')."' ORDER BY certificate_no ASC LIMIT 1000");


            $currentNumber = $cardlist[0]->certificate_no;
            $intervalCount = 1;

            for($i=1;$i<count($cardlist);$i++) {

                if($cardlist[$i]->certificate_no != $currentNumber+1) {
                    break;
                }
                else {
                    $currentNumber++;
                    $intervalCount++;
                }


            }

            if($intervalCount <= 100) {
                if($isMonitor) $monitorWarn = true;
                else echo "<h2>WARNING</h2>";
            }
            else {
                if(!$isMonitor) echo "<h3>OK</h3>";
            }

            if(!$isMonitor) {
                echo "INTERVAL: resgroup ".$interval->reservation_group." - date ".$interval->expire_date->format('d-m-Y')." - interval ".$interval->intstart." to ".$interval->intend." is span of ".$interval->intervalcount." but only has ".$interval->cardcount." cards<br>";
                echo $intervalCount." left of current interval ".$cardlist[0]->certificate_no." - ".$currentNumber."<br>";
                echo "ALL CARDS: SELECT * FROM gift_certificate WHERE shop_id = 0 && reservation_group = ".$interval->reservation_group." && expire_date = '".$interval->expire_date->format('Y-m-d')."' ORDER BY certificate_no ASC<br>";
                echo "ONLY INTERVAL: SELECT * FROM gift_certificate WHERE shop_id = 0 && certificate_no >= ".$cardlist[0]->certificate_no." && certificate_no <= ".$currentNumber."  && reservation_group = ".$interval->reservation_group." && expire_date = '".$interval->expire_date->format('Y-m-d')."' ORDER BY certificate_no ASC<br>";
                echo "RESERVE INTERVAL: UPDATE gift_certificate SET company_id = 1, shop_id = 1 WHERE shop_id = 0 && certificate_no >= ".$cardlist[0]->certificate_no." && certificate_no <= ".$currentNumber."  && reservation_group = ".$interval->reservation_group." && expire_date = '".$interval->expire_date->format('Y-m-d')."' ORDER BY certificate_no ASC<br><br>";
            }


        }

        if($isMonitor) {
            if($monitorWarn)
            {
                header("HTTP/1.0 500 Service down");
                echo "ERROR";
                exit();
            }
            else echo "OK";
        }


    }
    
    public function warningMail()
    {
        $warningList = $this->warning("52,54,55,56,53,290,310,575,2395,9321,4662,4668,6989,7121,7122", "dk");
        if (sizeofgf($warningList) > 0) {
            $this->sendNotificationMail("dk", "Advarsel: Kort-gaver (DK) ", $warningList);
        }

        $warningList = $this->warning("57,58,59,272,574,2550,4740,8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366", "no");
        if (sizeofgf($warningList) > 0) {
            $this->sendNotificationMail("no", "Advarsel: Kort-gaver (NO) ", $warningList);
        }

        $hasToCloseList = $this->hasToClose("52,54,55,56,53,290,310,575,2395,9321,4662,4668,6989,7121,7122", "dk");
        if (sizeofgf($hasToCloseList) > 0) {
            $this->sendNotificationMail("dk", "Advarsel: Kort-gaver som er blevet lukket", $warningList);
        }

        $hasToCloseList = $this->hasToClose("57,58,59,272,574,2550,4740,8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366", "no");
        if (sizeofgf($hasToCloseList) > 0) {
            $this->sendNotificationMail("no", "Advarsel: Kort-gaver som er blevet lukket", $warningList);
        }

    }

    public function runCardShop()
    {
//        $this->cardShops(1);

        $this->cardShops2(" 52,54,55,56,53,290,310,575,2395,9321,4662,4668,6989,7121,7122", "dk");
        $this->cardShops2("57,58,59,272,574,2550,4740,8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366", "no");
        $this->cardShops2("1832,1981,2558,4793,5117,8271,9495", "se");

        echo "done";
//$this->cardShopsNO();

    }
    public function norgeSaleShop(){
        $sql = 'SELECT id FROM `shop` WHERE (`name` like ("NO %") || rapport_email = "th@gavefabrikken.no") and shop.is_demo = 0 AND shop.active = 1 AND shop.deleted = 0 and shop.soft_close = 0';
        $listNoRS = Dbsqli::SetSql2($sql);
        $listToSql = [];
        foreach($listNoRS as $ele){
            array_push($listToSql,$ele["id"]);
        }
        echo "tore";

        $warningList = $this->warning(implode(",",$listToSql), "dk");
        if (sizeofgf($warningList) > 0) {
            $this->sendNotificationMail("tore", "Advarsel: valgshops ", $warningList);
        }

    }


    public function missingshipmentcheck() {

        $companyOrderList = CompanyOrder::find_by_sql("SELECT * FROM `company_order` where company_name not like '%replacement%' && id not in (select companyorder_id from shipment where shipment_type = 'giftcard') && order_state != 8");
        if(countgf($companyOrderList) == 0) {
            echo "OK";
        } else {
            header("HTTP/1.0 500 Service down");
            echo "Error, ".countgf($companyOrderList)." orders is missing shipments";
            exit();
        }

    }


    public function checkwaitingprivatedelivery() {

        $output = isset($_GET["output"]);

        $okShipments = array(193778,220284,221520,239968,286548);

        $sql = "select 
	shipment.id, shop_user.username, shipment.itemno, `order`.order_no, present_model.model_present_no, shop_user.shutdown, shop_user.blocked
from 
	shipment, `order`, shop_user, present_model 
where ".(count($okShipments) > 0 ? "shipment.id NOT IN (".implode(",",$okShipments).") && " : "")."
    shipment.shipment_type = 'privatedelivery' && 
    shipment.shipment_state = 1 && 
    shipment.to_certificate_no = `order`.id && 
    `order`.shopuser_id = shop_user.id && 
    `order`.`present_model_id` = present_model.model_id && 
    present_model.language_id = 1 && 
    (shop_user.blocked = 1 || shop_user.shutdown = 1 || (shipment.itemno != present_model.model_present_no && shipment.itemno != concat(present_model.model_present_no,'-EFTERLEV') && concat(shipment.itemno,'-EFTERLEV') != present_model.model_present_no))";

       
        $listProblems = \Dbsqli::getSql2($sql);

        if(countgf($listProblems) == 0) {
            echo "OK";
        } else {

            if($output) {
                echo "<table>";

                echo "<tr>";
                foreach($listProblems[0] as $field => $problem) {
                    echo "<td>".$field."</td>";
                }
                echo "</tr>";


                foreach($listProblems as $problemRow) {
                    echo "<tr>";
                    foreach($problemRow as $field => $problem) {
                        echo "<td>".$problem."</td>";
                    }
                    echo "</tr>";
                }


                echo "</table>";
            }
            else {
                header("HTTP/1.0 500 Service down");
                echo "Error, ".countgf($listProblems)." problems with privatedelivery";
                exit();
            }

        }

    }

    public function navisionruncheck() {

        // Do not run navision jobs in this period
        if(in_array(intval(date("H")),array(3,4,5))) {
            echo "Monitoring is paused!";
            exit();
        }

        $filepath = "units/navision/cronjob/last-nav-cron-run.log";

        if(!file_exists($filepath)) {
            header("HTTP/1.0 500 Service down");
            echo "Navision run check file does not exist.";
            exit();
        }

        $content = file_get_contents($filepath);
        $lastRunTime = intval($content);

        if($lastRunTime < time()-60*15) {
            header("HTTP/1.0 500 Service down");
            echo "Navision cron job is not running!";
            exit();
        }

        echo "Running ok, last ". (time()-$lastRunTime)." seconds ago (".((time()-$lastRunTime)/60)." minutes)";

    }


    public function runSaleShop()
    {
        $this->shops("2105","dk");

    }

    public function warningValgShop()
    {
      //  $listOfIntresenterRS =
    }





    private function warning($shopList, $contrie)
    {
        $list = PresentReservation::find_by_sql("
         select *,present_reservation.id as present_reservation_id, shop.name as shop_name, shop.is_gift_certificate  from present_reservation
         INNER JOIN (SELECT `present_model_id`,`shop_id`,count(id) c FROM `order` GROUP by `shop_id`,`present_model_id`)`orderNy` on `orderNy`.shop_id = present_reservation.shop_id and `orderNy`.present_model_id = present_reservation.model_id
         left join present_model on `present_model`.model_id = present_reservation.model_id inner join shop ON shop.id = present_reservation.shop_id
         inner join shop_present on present_reservation.present_id = shop_present.present_id WHERE present_model.`model_id` != 0 and present_model.language_id = '1' and present_model.active = 0 and shop.is_demo = 0 AND shop.active = 1 AND shop.deleted = 0 and shop.soft_close = 0 and present_reservation.quantity > -1 and
         shop.id in (" . $shopList . ")
          and present_model.active = 0 and shop_present.active = 1 and shop_present.is_deleted = 0
            and ( CAST(warning_level + `orderNy`.c as signed) > quantity )
            ORDER BY present_reservation.`shop_id`, `present_reservation`.`model_id` DESC ");

        return $list;
    }
    private function hasToClose($shopList, $contrie)
    {
        $list = PresentReservation::find_by_sql("
         select *,present_reservation.id as present_reservation_id, shop.name as shop_name, shop.is_gift_certificate  from present_reservation
         INNER JOIN (SELECT `present_model_id`,`shop_id`,count(id) c FROM `order` GROUP by `shop_id`,`present_model_id`)`orderNy` on `orderNy`.shop_id = present_reservation.shop_id and `orderNy`.present_model_id = present_reservation.model_id
         left join present_model on `present_model`.model_id = present_reservation.model_id inner join shop ON shop.id = present_reservation.shop_id
         inner join shop_present on present_reservation.present_id = shop_present.present_id WHERE present_model.`model_id` != 0 and present_model.language_id = '1' and present_model.active = 0 and shop.is_demo = 0 AND shop.active = 1 AND shop.deleted = 0 and shop.soft_close = 0 and present_reservation.quantity > -1 and
         shop.id in (" . $shopList . ")
          and present_model.active = 0 and shop_present.active = 1 and shop_present.is_deleted = 0
            and ( CAST(`orderNy`.c as signed) > quantity )
            ORDER BY present_reservation.`shop_id`, `present_reservation`.`model_id` DESC ");

        return $list;
    }
    private function sendNotificationMail($contrie, $title, $data)
    {
        $html = "<br><table border=1><tr><th>Shopnavn</th><th>Gave</th><th>Model</th><th>Valgte</th><th>Reserverede</th><th>Advarsel</th></tr>";
        foreach ($data as $value) {

            $html .= "<tr>";
            $html .= "<td>" . $value->attributes["shop_name"] . "</td>";
            $html .= "<td>" . $value->attributes["model_name"] . "</td>";
            $html .= "<td>" . $value->attributes["model_no"] . "</td>";
            $html .= "<td>" . $value->attributes["c"] . "</td>";
            $html .= "<td>" . $value->attributes["quantity"] . "</td>";
            $html .= "<td>" . $value->attributes["warning_level"] . "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";

        if ($contrie == "tore") {
            echo "send no";
            $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','" . $title . "','" . $html . "' )");
            $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','th@gavefabrikken.no','" . $title . "','" . $html . "' )");
        }

        if ($contrie == "dk") {
            echo "send no";
            $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','" . $title . "','" . $html . "' )");
            //   $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id, sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','sse@gavefabrikken.dk','".$title."','".$html."' )");
            //$rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,    sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 2, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','sse@gavefabrikken.dk','KortShops: -- Advarsel  --','".$html."' )");
            //$rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id, sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 2, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','th@gavefabrikken.no','KortShops: -- Advarsel  --','".$html."' )");
        }
        if ($contrie == "no") {
            echo "send dk";
            $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','" . $title . "','" . $html . "' )");
            //     $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id, sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','sse@gavefabrikken.dk','".$title."','".$html."' )");
            //     $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id, sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','th@gavefabrikken.no','".$title."','".$html."' )");
            //$rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id, sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 2, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','sse@gavefabrikken.dk','KortShops: -- Advarsel  --','".$html."' )");
        }
    }

    public function cardShops2($shopList, $contrie)
    {
        $list = PresentReservation::find_by_sql("select *, shop.name as shop_name from present_reservation
          INNER JOIN (SELECT `present_model_id`, present_model_present_no, `shop_id`,count(id) c FROM `order` GROUP by `shop_id`,`present_model_id`) `orderNy`
          on
              `orderNy`.shop_id =  present_reservation.shop_id and
              `orderNy`.present_model_id = present_reservation.model_id

          left join (SELECT * FROM `present_model` WHERE `model_id` != 0 and present_model.language_id = '1' and active='0') `present_modelNy`
          on
          `present_modelNy`.model_id = present_reservation.model_id
                    left join shop_present on
            `present_modelNy`.present_id =   shop_present.present_id


          inner join shop
          ON
          shop.id = present_reservation.shop_id
          WHERE
          shop.is_demo = 0 AND
                          shop_present.is_deleted = 0 and
                          shop_present.active = 1 and
                          shop.is_gift_certificate = 1 AND
                          shop.active = 1 AND
                          shop.deleted = 0 and
                          shop.soft_close = 0 and
                          present_reservation.quantity > 0 and

                          shop.id in (" . $shopList . ")


          HAVING (present_reservation.quantity > 0 and ( quantity  <= `orderNy`.c ))
          ORDER BY present_reservation.`shop_id`,  `present_reservation`.`model_id` DESC");

        $html = "status<br><table border=1><tr><th>Shopnavn</th><th>ShopId</th><th>Gave</th><th>Model</th><th>Valgte</th><th>Reserverede</th><th>Varenr</th><th>Advarsel</th><th>Luk status</th><th>presentId</th><th>modelId</th></tr>";
        foreach ($list as $value) {
            $closeStatus = "Ej lukket";
            // tjek om der skal tvinges til at lukke ved 97%
            if ($value->attributes["do_close"] == 1) {
                $closeStatus = "Auto lukkes";
            }

            if (($value->attributes["quantity"] * 1) <= $value->attributes["c"]) {
                $closeStatus = "Tvunget auto luk ";
                $value->attributes["do_close"] = 3;
            }

            $html .= "<tr>";
            $html .= "<td>" . $value->attributes["shop_name"] . "</td>";
            $html .= "<td>" . $value->attributes["shop_id"] . "</td>";
            $html .= "<td>" . $value->attributes["model_name"] . "</td>";
            $html .= "<td>" . $value->attributes["model_no"] . "</td>";
            $html .= "<td>" . $value->attributes["c"] . "</td>";
            $html .= "<td>" . $value->attributes["quantity"] . "</td>";
            $html .= "<td>" . $value->attributes["present_model_present_no"] . "</td>";
            $html .= "<td>" . $value->attributes["warning_level"] . "</td>";
            $html .= "<td>" . $closeStatus . "</td>";
            $html .= "<td>" . $value->attributes["present_id"] . "</td>";
            $html .= "<td>" . $value->attributes["present_model_id"] . "</td>";
            // close and deactivate presents
            // check status for alle models

            // Luk modellen

            // active = 0 så er gaven aktiv. det er en fejl men svær at rettet nu
            //         if($value->attributes["do_close"] == 1 || $value->attributes["do_close"] == 3 ) {
            if ($value->attributes["do_close"] == 3) {
                $sql1 = "update present_model set active = 1 where present_id = " . $value->attributes["present_id"] . " and model_id = " . $value->attributes["present_model_id"] . " and language_id = 1 ";

                Dbsqli::SetSql2($sql1);
                // check om hele gaven skal deaktiveres
                $countActiveModels = 0;

                $sql2 = "select * from present_model where present_id = " . $value->attributes["present_id"] . " and language_id = 1 ";
                $presentModelCheck = Dbsqli::GetSql2($sql2);

                foreach ($presentModelCheck as $key2 => $value2) {
                    // active = 0 så er gaven aktiv. det er en fejl men svær at rettet nu
                    if ($value2["active"] == 0) {
                        $countActiveModels++;
                    }
                }

                // hvis count er lig 0, så er der ikke flere aktive modeller i gaven og hele gave deaktiveres
                if ($countActiveModels == 0) {
                    $sql3 = " update shop_present set active = 0, is_deleted = 0 where present_id = " . $value->attributes["present_id"] . " and shop_id = " . $value->attributes["shop_id"];
                    Dbsqli::SetSql2($sql3);
                }

                //  $html.="<td>".sizeofgf($presentModelCheck)."</td>";

            }
            $html .= "</tr>";

        }
        if ($contrie == "dk") {
           // Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','KortShops: tjek','" . json_encode($list) . "' )");
        }
        $html .= "</table>";
        $html = $html;

        if (sizeofgf($list) > 0) {
          if ($contrie == "no") {
                echo "send no";

                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','KortShops: Autoluk no','" . $html . "' )");
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','kt@gavefabrikken.dk','KortShops: Autoluk no','" . $html . "' )");
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','th@gavefabrikken.no','KortShops: Autoluk no','" . $html . "' )");
            }
            if ($contrie == "dk") {
                echo "send dk";
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','sse@gavefabrikken.dk','KortShops: Autoluk dk','" . $html . "' )");
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','rbo@gavefabrikken.dk','KortShops: Autoluk dk','" . $html . "' )");
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','KortShops: Autoluk dk','" . $html . "' )");
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','kt@gavefabrikken.dk','KortShops: Autoluk dk','" . $html . "' )");

            }
            if ($contrie == "se") {
                echo "send se";
               $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','sse@gavefabrikken.dk','KortShops: Autoluk se','" . $html . "' )");
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','rbo@gavefabrikken.dk','KortShops: Autoluk se','" . $html . "' )");
               $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','KortShops: Autoluk se','" . $html . "' )");
               $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','kt@gavefabrikken.dk','KortShops: Autoluk se','" . $html . "' )");

            }

        }

    }

    public function shops($shopList, $contrie)
    {
            $list = PresentReservation::find_by_sql("select *, shop.name as shop_name from present_reservation
          INNER JOIN (SELECT `present_model_id`, present_model_present_no, `shop_id`,count(id) c FROM `order` GROUP by `shop_id`,`present_model_id`) `orderNy`
          on
              `orderNy`.shop_id =  present_reservation.shop_id and
              `orderNy`.present_model_id = present_reservation.model_id

          left join (SELECT * FROM `present_model` WHERE `model_id` != 0 and present_model.language_id = '1' and active='0') `present_modelNy`
          on
          `present_modelNy`.model_id = present_reservation.model_id
                    left join shop_present on
            `present_modelNy`.present_id =   shop_present.present_id


          inner join shop
          ON
          shop.id = present_reservation.shop_id
          WHERE
          shop.is_demo = 0 AND
                          shop_present.is_deleted = 0 and
                          shop_present.active = 1 and
                          do_close = 1 and
                          shop.active = 1 AND
                          shop.deleted = 0 and
                          shop.soft_close = 0 and
                          present_reservation.quantity > 0 and

                          shop.id in (" . $shopList . ")


          HAVING (present_reservation.quantity > 0 and ( quantity  <= `orderNy`.c ))
          ORDER BY present_reservation.`shop_id`,  `present_reservation`.`model_id` DESC");



        $html = "status<br><table border=1><tr><th>Shopnavn</th><th>ShopId</th><th>Gave</th><th>Model</th><th>Valgte</th><th>Reserverede</th><th>Varenr</th><th>Advarsel</th><th>Luk status</th><th>presentId</th><th>modelId</th></tr>";
        foreach ($list as $value) {

            // tjek om der skal tvinges til at lukke ved 97%
            $closeStatus = "Auto lukkes";


            $html .= "<tr>";
            $html .= "<td>" . $value->attributes["shop_name"] . "</td>";
            $html .= "<td>" . $value->attributes["shop_id"] . "</td>";
            $html .= "<td>" . $value->attributes["model_name"] . "</td>";
            $html .= "<td>" . $value->attributes["model_no"] . "</td>";
            $html .= "<td>" . $value->attributes["c"] . "</td>";
            $html .= "<td>" . $value->attributes["quantity"] . "</td>";
            $html .= "<td>" . $value->attributes["present_model_present_no"] . "</td>";
            $html .= "<td>" . $value->attributes["warning_level"] . "</td>";
            $html .= "<td>" . $closeStatus . "</td>";
            $html .= "<td>" . $value->attributes["present_id"] . "</td>";
            $html .= "<td>" . $value->attributes["present_model_id"] . "</td>";
            // close and deactivate presents
            // check status for alle models

            // Luk modellen

            // active = 0 så er gaven aktiv. det er en fejl men svær at rettet nu
            //         if($value->attributes["do_close"] == 1 || $value->attributes["do_close"] == 3 ) {

                 $sql1 = "update present_model set active = 1 where present_id = " . $value->attributes["present_id"] . " and model_id = " . $value->attributes["present_model_id"] . " and language_id = 1 ";

                Dbsqli::SetSql2($sql1);
                // check om hele gaven skal deaktiveres
                $countActiveModels = 0;

                $sql2 = "select * from present_model where present_id = " . $value->attributes["present_id"] . " and language_id = 1 ";
                $presentModelCheck = Dbsqli::GetSql2($sql2);

                foreach ($presentModelCheck as $key2 => $value2) {
                    // active = 0 så er gaven aktiv. det er en fejl men svær at rettet nu
                    if ($value2["active"] == 0) {
                        $countActiveModels++;
                    }
                }

                // hvis count er lig 0, så er der ikke flere aktive modeller i gaven og hele gave deaktiveres
                if ($countActiveModels == 0) {
                    $sql3 = " update shop_present set active = 0, is_deleted = 0 where present_id = " . $value->attributes["present_id"] . " and shop_id = " . $value->attributes["shop_id"];
                    Dbsqli::SetSql2($sql3);
                }

                //  $html.="<td>".sizeofgf($presentModelCheck)."</td>";


            $html .= "</tr>";

        }

        $html .= "</table>";
        $html = $html;

        if (sizeofgf($list) > 0) {
          if ($contrie == "no") {
                echo "send no";
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','KortShops: Autoluk','" . $html . "' )");
//                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','sse@gavefabrikken.dk','KortShops: Autoluk','" . $html . "' )");
//                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','th@gavefabrikken.no','KortShops: Autoluk','" . $html . "' )");
            }
            if ($contrie == "dk") {
                echo "send dk";
                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','KortShops: Autoluk','" . $html . "' )");
//                $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','sse@gavefabrikken.dk','KortShops: Autoluk','" . $html . "' )");
            }

        }
        echo "ok";

    }
     // not in production
    private function cardShops($shopType)
    {
        $shopMailName = "KortShop";
        if ($shopType == 0) {$shopMailName = "ValgShop";}

        $list = PresentReservation::find_by_sql("select *,shop.name as shop_name,present.copy_of as present_copy_of,  present.name as present_name, shop_present.properties,shop.name from present_reservation
          INNER JOIN (SELECT `present_id`,`present_model_id`,`shop_id`,count(id) c FROM `order` GROUP by `shop_id`,`present_id`,`present_model_id`) `orderNy`
          on
              `orderNy`.shop_id =  present_reservation.shop_id and
              `orderNy`.present_id = present_reservation.present_id and
              `orderNy`.present_model_id = present_reservation.model_id
          INNER JOIN present on
            present.id = present_reservation.present_id

          left join (SELECT * FROM `present_model` WHERE `model_id` != 0 and present_model.language_id = '1') `present_modelNy`
          on
          `present_modelNy`.model_id = present_reservation.model_id
          inner JOIN shop_present
          ON
          shop_present.shop_id = present_reservation.shop_id AND
          shop_present.present_id = present_reservation.present_id
          inner join shop
          ON
          shop.id = present_reservation.shop_id
          WHERE
          shop.is_demo = 0 AND
                        shop.is_gift_certificate = " . $shopType . " AND
                          shop.active = 1 AND
                          shop.deleted = 0 and
                          shop_present.active = 1 and
                          shop.soft_close = 0

      HAVING (present_reservation.quantity > 0 and (( quantity * (90 /100)) < `orderNy`.c )) or present_copy_of > 0
          ORDER BY present_reservation.`shop_id`,  `present_reservation`.`present_id` DESC");

        $data = [];
        $copy_of = [];
        $add_antal = [];

        foreach ($list as $item) {
            /*
            var_dump($item->attributes["shop_id"]);
            var_dump($item->attributes["shop_name"]);
            var_dump($item->attributes["present_name"]);
            var_dump($item->attributes["model_name"]);
            var_dump($item->attributes["model_present_no"]);
            var_dump($item->attributes["model_no"]);
            var_dump($item->attributes["c"]);
            var_dump($item->attributes["quantity"]);
            var_dump($item->attributes["warning_level"]);
            var_dump($item->attributes["copy_of"]);
             */
            if ($item->attributes["copy_of"] != 0) {
                $copy_of[$item->attributes["shop_id"]][$item->attributes["present_id"]] = $item->attributes["copy_of"];
            }
        }
        $keys = array_keys($copy_of);

        for ($i = 0; $i < sizeofgf($keys); $i++) {
            $imploded = implode(',', $copy_of[$keys[$i]]);

            $rs = PresentReservation::find_by_sql("SELECT `present_id`,`present_model_id`,`shop_id`,count(id) c FROM `order` where `shop_id` = " . $keys[$i] . " and `present_id` in (" . $imploded . ") GROUP by `present_id`,`present_model_id`");
            foreach ($rs as $rsItem) {
                $presentId = $rsItem->attributes["present_id"];
                $present_model_id = $rsItem->attributes["present_model_id"];
                $c = $rsItem->attributes["c"];
                $add_antal[$keys[$i]][$presentId][$present_model_id] = $c;
            }
        }
        // print_r($add_antal);

        $saleShop90 = [];
        $saleShop100 = [];
        foreach ($list as $item) {
            /*
            echo ($item->attributes["shop_id"])."\n";
            echo ($item->attributes["present_model_id"])."\n";
            echo ($item->attributes["present_id"])."\n";
            echo ($item->attributes["copy_of"])."\n\n\n\n\n\n\n";
             */
            try {
                if ($add_antal[$item->attributes["shop_id"]][$item->attributes["copy_of"]][$item->attributes["present_model_id"]]) {
                    $newAntal = $add_antal[$item->attributes["shop_id"]][$item->attributes["copy_of"]][$item->attributes["present_model_id"]];
                    $item->attributes["c"] += $newAntal . "\n";
                }
            } catch (Exception $e) {}

        }
        /*
        print_R($list);
        foreach($list as $ele){
        if( ($ele->attributes["quantity"]) * 0.  <  ($ele->attributes["c"])*1  ){

        }
        }

         */

        $headCount = 0;
        // finder gaver > 90 % og laver liste til mail ---------------------------------------------------------------------------------------------------------------------------
        $mailHtml = "<table border=1><th>Shop</th><th>Gavenavn</th><th>Model</th><th>Antal valgte</th><th>Antal på lager</th>";
        foreach ($list as $ele) {
            if (($ele->attributes["quantity"]) * 0.9 < ($ele->attributes["c"]) * 1) {
                if ($ele->attributes["quantity"] * 1 > 0) {
                    $sogString = "\"" . $ele->attributes["model_id"] . "\"";
                    if (strpos($ele->attributes["properties"], $sogString) !== false && $ele->attributes["properties"] != "") {
                        $headCount++;
                        $mailHtml .= "<tr><td>" . $ele->attributes["shop_name"] . "</td> <td>" . $ele->attributes["present_id"] . "</td><td>" . $ele->attributes["model_id"] . "</td>  <td>" . $ele->attributes["present_name"] . "</td> <td>" . $ele->attributes["model_name"] . " - " . $ele->attributes["model_no"] . " - " . $ele->attributes["model_present_no"] . " </td> <td>" . $ele->attributes["c"] . "</td><td>" . $ele->attributes["quantity"] . "</td> <td>" . round(($ele->attributes["c"] / $ele->attributes["quantity"]) * 100) . "</td> </tr>";
                        $saleShop90[$ele->attributes["shop_id"]][] = "<tr><td>" . $ele->attributes["shop_name"] . "</td> <td>" . $ele->attributes["present_name"] . "</td> <td>" . $ele->attributes["model_name"] . " - " . $ele->attributes["model_no"] . " - " . $ele->attributes["model_present_no"] . " </td> <td>" . $ele->attributes["c"] . "</td> <td>" . $ele->attributes["quantity"] . "</td></tr>";
                    }
                    if ($ele->attributes["properties"] == "") {
                        $sendMail = true;
                        $headCount++;
                        $mailHtml .= "<tr><td>" . $ele->attributes["shop_name"] . "</td> <td>" . $ele->attributes["present_id"] . "</td><td>" . $ele->attributes["model_id"] . "</td>  <td>" . $ele->attributes["present_name"] . "</td> <td>" . $ele->attributes["model_name"] . " - " . $ele->attributes["model_no"] . " - " . $ele->attributes["model_present_no"] . " </td> <td>" . $ele->attributes["c"] . "</td><td>" . $ele->attributes["quantity"] . "</td><td>" . round(($ele->attributes["c"] / $ele->attributes["quantity"]) * 100) . "</td> </tr>";
                        $saleShop100[$ele->attributes["shop_id"]][] = "<tr><td>" . $ele->attributes["shop_name"] . "</td> <td>" . $ele->attributes["present_name"] . "</td> <td>" . $ele->attributes["model_name"] . " - " . $ele->attributes["model_no"] . " - " . $ele->attributes["model_present_no"] . " </td> <td>" . $ele->attributes["c"] . "</td> <td>" . $ele->attributes["quantity"] . "</td> </tr>";
                    }
                }
            }
        }
        $mailHtml .= "</table>";
        $mailHtml = $mailHtml;
        if ($shopType == 1) {
            echo $mailHtml;
            // $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,  sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 2, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','mv@gavefabrikken.dk','KortShops: Gaver over 90%','".$mailHtml."' )");
            $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,    sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 2, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','" . $headCount . " KortShops: Gaver over 90%','" . $mailHtml . "' )");
        }

        // finder gaver > 97 % og laver liste til mail -----------------------------------------------------------------------------------------------------------------------------------
        $mailHtml = "<table border=1><th>Shop</th><th>Gavenavn</th><th>Model</th><th>Antal valgte</th><th>Antal på lager</th>";
        $sendMail = false;
        foreach ($list as $ele) {
            if (($ele->attributes["quantity"]) * 0.97 < ($ele->attributes["c"]) * 1) {
                if ($ele->attributes["quantity"] * 1 > 0) {
                    $sogString = "\"" . $ele->attributes["model_id"] . "\"";
                    if (strpos($ele->attributes["properties"], $sogString) !== false && $ele->attributes["properties"] != "") {
                        $sendMail = true;
                        $mailHtml .= "<tr><td>" . $ele->attributes["shop_name"] . "</td> <td>" . $ele->attributes["present_id"] . "</td><td>" . $ele->attributes["model_id"] . "</td>  <td>" . $ele->attributes["present_name"] . "</td> <td>" . $ele->attributes["model_name"] . " - " . $ele->attributes["model_no"] . " - " . $ele->attributes["model_present_no"] . " </td> <td>" . $ele->attributes["c"] . "</td><td>" . $ele->attributes["quantity"] . "</td> </tr>";
                        $saleShop100[$ele->attributes["shop_id"]][] = "<tr><td>" . $ele->attributes["shop_name"] . "</td> <td>" . $ele->attributes["present_name"] . "</td> <td>" . $ele->attributes["model_name"] . " - " . $ele->attributes["model_no"] . " - " . $ele->attributes["model_present_no"] . " </td> <td>" . $ele->attributes["c"] . "</td> <td>" . $ele->attributes["quantity"] . "</td> </tr>";
                    }
                    if ($ele->attributes["properties"] == "") {
                        $sendMail = true;
                        $mailHtml .= "<tr><td>" . $ele->attributes["shop_name"] . "</td> <td>" . $ele->attributes["present_id"] . "</td><td>" . $ele->attributes["model_id"] . "</td>  <td>" . $ele->attributes["present_name"] . "</td> <td>" . $ele->attributes["model_name"] . " - " . $ele->attributes["model_no"] . " - " . $ele->attributes["model_present_no"] . " </td> <td>" . $ele->attributes["c"] . "</td><td>" . $ele->attributes["quantity"] . "</td> </tr>";
                        $saleShop100[$ele->attributes["shop_id"]][] = "<tr><td>" . $ele->attributes["shop_name"] . "</td> <td>" . $ele->attributes["present_name"] . "</td> <td>" . $ele->attributes["model_name"] . " - " . $ele->attributes["model_no"] . " - " . $ele->attributes["model_present_no"] . " </td> <td>" . $ele->attributes["c"] . "</td> <td>" . $ele->attributes["quantity"] . "</td> </tr>";
                    }
                }
            }
        }
        $mailHtml .= "</table>";
        $mailHtml = $mailHtml;
        // sender mails til dem over 97% ---------------------------------------------------------------------------------------------
        if ($shopType == 1 && $sendMail == true) {
            //        $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,  sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 2, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','mv@gavefabrikken.dk','KortShops: Gaver over 100%','".$mailHtml."' )");
            $mailHtml;
            $rs = Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,  sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 2, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','KortShops: LUK Gaver over 197%','" . $mailHtml . "' )");
        }

        // sender mails til dem over 90% ---------------------------------------------------------------------------------------------
        if ($shopType == 0) {
            ///  print_R($saleShop90);
            $saleShop90Keys = array_keys($saleShop90);
            for ($j = 0; $j < sizeofgf($saleShop90Keys); $j++) {

                $rs = Dbsqli::getSql2("select rapport_email from shop where id = " . $saleShop90Keys[$j]);
                if ($rs[0]["rapport_email"] != "") {
                    $mailHtml = implode(" ", $saleShop90[$saleShop90Keys[$j]]);
                    Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id, sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 2, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','" . $rs[0]["rapport_email"] . "','ValgShops: Gaver over 90%','" . $mailHtml . "' )");

                }
            }

        }

    }
    private function closepresent($presentId, $modelId)
    {

    }

}
?>