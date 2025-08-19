<?php

class shipmentreport Extends reportBaseController {


    private static $dataMap = array();
    private static $startTime;
    private static $endTime;

    private static $salesData = array();
    
    private static function addDataPoint($langCode,$isShipment,$shipmentType,$handler,$quantity,$shipments,$syncdate,$startDate,$endDate) {

        $label = $shipmentType."_".$isShipment."_".$handler;
        $timestamp = $syncdate == null ? 0 : strtotime($syncdate);
        if(!isset(self::$dataMap[$langCode])) self::$dataMap[$langCode] = array();
        if(!isset(self::$dataMap[$langCode][$label])) self::$dataMap[$langCode][$label] = array("beforeperiodcount" => 0,"inperiodcount" => 0, "afterperiodcount" => 0, "waitingcount" => 0,"beforeperiodsum" => 0,"inperiodsum" => 0, "afterperiodsum" => 0, "waitingsum" => 0,"periodcountlist" => array(),"periodsumlist" => array());

        if($timestamp == null) {
            self::$dataMap[$langCode][$label]["waitingcount"] += $shipments;
            self::$dataMap[$langCode][$label]["waitingsum"] += $quantity;
        } else if($timestamp < $startDate) {
            self::$dataMap[$langCode][$label]["beforeperiodcount"] += $shipments;
            self::$dataMap[$langCode][$label]["beforeperiodsum"] += $quantity;
        } else if($timestamp > $endDate) {
            self::$dataMap[$langCode][$label]["afterperiodcount"] += $shipments;
            self::$dataMap[$langCode][$label]["afterperiodsum"] += $quantity;
        } else {
            self::$dataMap[$langCode][$label]["inperiodcount"] += $shipments;
            self::$dataMap[$langCode][$label]["inperiodsum"] += $quantity;
            self::$dataMap[$langCode][$label]["periodcountlist"][$syncdate] = $shipments;
            self::$dataMap[$langCode][$label]["periodsumlist"][$syncdate] = $quantity;
        }

    }

    private static function addOrderDataPoint($langCode,$orders,$quantity,$orderdate,$is_email,$startDate,$endDate) {


        $label = $is_email == 1 ? "E-mail kort" : "Fysiske kort";
        $timestamp = $orderdate == null ? 0 : strtotime($orderdate);

        if(!isset(self::$salesData[$langCode])) self::$salesData[$langCode] = array();
        if(!isset(self::$salesData[$langCode][$label])) self::$salesData[$langCode][$label] = array("beforeperiodcount" => 0,"inperiodcount" => 0, "afterperiodcount" => 0, "waitingcount" => 0,"beforeperiodsum" => 0,"inperiodsum" => 0, "afterperiodsum" => 0, "waitingsum" => 0,"periodcountlist" => array(),"periodsumlist" => array());

        if($timestamp < $startDate) {
            self::$salesData[$langCode][$label]["beforeperiodcount"] += $orders;
            self::$salesData[$langCode][$label]["beforeperiodsum"] += $quantity;
        } else if($timestamp > $endDate) {
            self::$salesData[$langCode][$label]["afterperiodcount"] += $orders;
            self::$salesData[$langCode][$label]["afterperiodsum"] += $quantity;
        } else {
            self::$salesData[$langCode][$label]["inperiodcount"] += $orders;
            self::$salesData[$langCode][$label]["inperiodsum"] += $quantity;
            self::$salesData[$langCode][$label]["periodcountlist"][$orderdate] = $orders;
            self::$salesData[$langCode][$label]["periodsumlist"][$orderdate] = $quantity;
        }

    }
    
    public static function dispatchShipmentReport()
    {

        // Check token
        if(!isset($_GET["token"]) || $_GET["token"] != "dsS3ofSrtmkS") {
            echo "Invalid token";
            exit();
        }

        // Process date
        $startDate = time()-60*60*24*7;
        $endDate = time();
        if(isset($_POST["action"]) && $_POST["action"] == "update") {
            $startDate = strtotime($_POST["from"]);
            $endDate = strtotime($_POST["to"]);
        }

        self::$startTime = $startDate;
        self::$endTime = $endDate;

        // Load data
        $sql = "SELECT cardshop_settings.language_code, company_order.is_email, shipment.isshipment, shipment.shipment_type, shipment.handler, sum(shipment.quantity+shipment.quantity2+shipment.quantity3+shipment.quantity4+shipment.quantity5) as quantity, count(shipment.id) as shipments, date(shipment_sync_date) as sync_date FROM `shipment`, company_order, cardshop_settings where company_order.company_name not like 'replacement%' && shipment.shipment_state != 4 && cardshop_settings.shop_id = company_order.shop_id && shipment.companyorder_id = company_order.id GROUP BY shipment.handler, language_code, shipment.isshipment, shipment_type, sync_date order by sync_date asc";
        $shipmentList = \Shipment::find_by_sql($sql);
        
        // Process list
        foreach($shipmentList as $shipment) {

            $language_code = $shipment->language_code;
            $isshipment	 = $shipment->isshipment;
            $shipment_type = $shipment->shipment_type;
            $quantity = $shipment->quantity;
            $shipments = $shipment->shipments;
            $syncdate = $shipment->sync_date;
            $handler = $shipment->handler;

            self::addDataPoint($language_code,$isshipment,$shipment_type,$handler,$quantity,$shipments,$syncdate,$startDate,$endDate);

        }

        // Load sales data
        $sql = "SELECT date(created_datetime) as orderdate, is_email, sum(quantity) as quantity, count(company_order.id) as orders, language_code FROM `company_order`, cardshop_settings where cardshop_settings.shop_id = company_order.shop_id && company_order.company_name not like 'replacement%' && order_state not in (7,8) GROUP BY language_code, date(created_datetime) order by date(created_datetime) ASC;";
        $orderlist = \Shipment::find_by_sql($sql);

        // Process list
        foreach($orderlist as $order) {

            $orderdate = $order->orderdate;
            $quantity	 = $order->quantity;
            $orders = $order->orders;
            $language_code = $order->language_code;
            $is_email = $order->is_email;

            self::addOrderDataPoint($language_code,$orders,$quantity,$orderdate,$is_email,$startDate,$endDate);

        }

        //echo "<pre>";
        //print_r(self::$dataMap);
        //print_r(self::$salesData);
        //echo "</pre>";

        $langCards = array(
            1 => array(
                "name" => "Danmark",
                "types" => array(
                    "giftcard_1_navision" => "Gavekort fysisk",
                    "giftcard_0_navision" => "Gavekort e-mail",
                    "earlyorder_1_navision" => "Earlyorder",
                    "privatedelivery_1_glsexport" => "Privatlevering lister",
                    "privatedelivery_1_foreigngls" => "Privatlevering udland",
                    "directdelivery_1_navision" => "Direkte levering navision (privat uden ordrenr)",
                    "privatedelivery_1_navision" => "Privatlevering navision"
                )
            ),
            4 => array(
                "name" => "Norge",
                "types" => array(
                    "giftcard_1_navision" => "Gavekort fysisk",
                    "giftcard_0_navision" => "Gavekort e-mail",
                    "earlyorder_1_navision" => "Earlyorder",
                    "privatedelivery_1_glsexport" => "Privatlevering lister"
                )
            ),
            5 => array(
                "name" => "Sverige",
                "types" => array(
                    "giftcard_1_navision" => "Gavekort fysisk",
                    "giftcard_0_navision" => "Gavekort e-mail",
                    "earlyorder_1_navision" => "Earlyorder",
                    "privatedelivery_1_glsexport" => "Privatlevering lister"
                )
            )
        );
        
        ?><style>
        table { border-collapse: collapse; }
        td { padding: 5px; font-size: 13px; font-family: arial; border: 1px solid #F0F0F0; border-bottom: 1px solid #A0A0A0;  }

    </style>
        <h2>Overblik over forsendelser pr land, type og periode</h2>
        <form method="post" action="//<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
            <table>
                <tr>
                    <td>Fra</td>
                    <td><input name="from" type="date" value="<?php echo date("Y-m-d",$startDate); ?>"></td>
                </tr>
                <tr>
                    <td>Til</td>
                    <td><input name="to" type="date" value="<?php echo date("Y-m-d",$endDate); ?>"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="opdater"></td>
                </tr>
            </table>
            <input type="hidden" name="action" value="update">
        </form>

        <table class="shiptable"><?php

        foreach($langCards as $langCode => $langData) {
            self::outputLangTable($langCode,$langData,self::$dataMap[$langCode] ?? array(),self::$salesData[$langCode] ?? array());
        }

        ?></table><?php

    }

    private static function showDatesInPeriod() {
        return ((self::$endTime - self::$startTime)/(60*60*24) < 14);
    }

    private static function getDatesList() {

        $start = new DateTime(date('Y-m-d', self::$startTime));
        $end = new DateTime(date('Y-m-d', self::$endTime));

        $end->modify('+1 day');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        $dates = [];

        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }

    private static function outputLangTable($langCode,$langData,$data,$salesData)
    {

        $dateList = self::getDatesList();
        $cols = 6 + (self::showDatesInPeriod() ? count($dateList) : 0);

        ?><thead>
    <tr>
        <td colspan="<?php echo $cols; ?>" style="padding-top: 10px; font-size: 16px; font-weight: bold;"><?php echo $langData["name"]; ?></td>
    </tr>
    </thead>
        <tbody>
        <tr class="subhead">
            <td>Leverancer</td>
            <td>Før perioden</td>
            <?php if(self::showDatesInPeriod()) {
                foreach($dateList as $date) {
                    echo "<td>".$date."</td>";
                }
            } ?>
            <td>I perioden</td>
            <td>Efter perioden</td>
            <td>Total</td>
            <td>Afventer</td>
        </tr>
        <?php

        $usedTypes = array();

        foreach($langData["types"] as $typeCode => $typeName) {

            if(isset($data[$typeCode])) {

            $typeData = $data[$typeCode];
            $usedTypes[] = $typeCode;


            ?><tr class="data">
                <td><?php echo $typeName; ?></td>
                <td><?php echo $typeData["beforeperiodsum"]." (".$typeData["beforeperiodcount"].")"; ?></td>
                <?php if(self::showDatesInPeriod()) {
                    foreach($dateList as $date) {

                        if(isset($typeData["periodcountlist"][$date])) {
                            echo "<td>".$typeData["periodsumlist"][$date]." (".$typeData["periodcountlist"][$date].")</td>";
                        } else {
                            echo "<td></td>";
                        }


                    }
                } ?>

            <td><?php echo $typeData["inperiodsum"]." (".$typeData["inperiodcount"].")"; ?></td>
            <td><?php echo $typeData["afterperiodsum"]." (".$typeData["afterperiodcount"].")"; ?></td>
            <td><?php echo ($typeData["inperiodsum"]+$typeData["beforeperiodsum"]+$typeData["afterperiodsum"])." (".($typeData["inperiodcount"]+$typeData["beforeperiodcount"]+$typeData["afterperiodcount"]).")"; ?></td>
            <td><?php echo $typeData["waitingsum"]." (".$typeData["waitingcount"].")"; ?></td>

            </tr><?php

            }
        }

        $notUsedTypes = array_diff(array_keys($data), $usedTypes);
        if(count($notUsedTypes) > 0) {
            ?><tr class="data">
                <td>Ikke brugte typer</td>
                <td colspan="<?php echo $cols-1; ?>"><?php echo implode(", ", $notUsedTypes); ?></td>
            </tr><?php
        }

        ?>
        <tr class="subhead">
            <td>Salg</td>
            <td>Før perioden</td>
            <?php if(self::showDatesInPeriod()) {
                foreach($dateList as $date) {
                    echo "<td>".$date."</td>";
                }
            } ?>
            <td>I perioden</td>
            <td>Efter perioden</td>
            <td>Total</td>
            <td>Afventer</td>
        </tr>
        <?php

        $types = array(
            "Fysiske kort","E-mail kort"
        );

        foreach($types as $type)
        {
            $typeData = $salesData[$type] ?? array("beforeperiodcount" => 0,"inperiodcount" => 0, "afterperiodcount" => 0, "waitingcount" => 0,"beforeperiodsum" => 0,"inperiodsum" => 0, "afterperiodsum" => 0, "waitingsum" => 0,"periodcountlist" => array(),"periodsumlist" => array());


            ?><tr class="data">
            <td><?php echo $type; ?></td>
            <td><?php echo $typeData["beforeperiodsum"]." (".$typeData["beforeperiodcount"].")"; ?></td>
            <?php if(self::showDatesInPeriod()) {
                foreach($dateList as $date) {

                    if(isset($typeData["periodcountlist"][$date])) {
                        echo "<td>".$typeData["periodsumlist"][$date]." (".$typeData["periodcountlist"][$date].")</td>";
                    } else {
                        echo "<td></td>";
                    }


                }
            } ?>

            <td><?php echo $typeData["inperiodsum"]." (".$typeData["inperiodcount"].")"; ?></td>
            <td><?php echo $typeData["afterperiodsum"]." (".$typeData["afterperiodcount"].")"; ?></td>
            <td><?php echo ($typeData["inperiodsum"]+$typeData["beforeperiodsum"]+$typeData["afterperiodsum"])." (".($typeData["inperiodcount"]+$typeData["beforeperiodcount"]+$typeData["afterperiodcount"]).")"; ?></td>
            <td><?php echo $typeData["waitingsum"]." (".$typeData["waitingcount"].")"; ?></td>

            </tr><?php
        }

        ?>
        </tbody><?php

    }

}
