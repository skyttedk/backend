<?php

use GFCommon\Model\Access\BackendPermissions;

router::checkRouted();


$shopidlist = array();
$expireDates = array();
$statMatrix = array();

// Get shops
$shoplist = CardshopSettings::find('all',array("order" => "concept_parent asc, card_price asc"));
//$shoplist = Shop::find_by_sql("SELECT * FROM `shop` where is_gift_certificate = 1 && id NOT IN (262,569,287,247,248,264,263,265,251) ORDER BY shop.mailserver_id ASC, `shop`.`name` ASC");
$shopidlist = array();
foreach($shoplist as $shop)
{
    $shopidlist[] = $shop->shop_id;
}

// Get dates
$shopUserDeadlines = ShopUser::find_by_sql("SELECT expire_date, shop_id, COUNT(id) as usercount FROM `shop_user` WHERE shop_id IN (".implode(",",$shopidlist).") && blocked = 0 && is_demo = 0 && expire_date IS NOT NULL GROUP BY expire_date, shop_id ORDER BY expire_date ASC");
foreach($shopUserDeadlines as $shopusercount)
{
    $deadline = $shopusercount->expire_date->format("Y-m-d");
    if(!in_array($deadline,$expireDates))
    {
        $expireDates[] = $deadline;
    }

    $statMatrix[$shopusercount->shop_id][$deadline] = $shopusercount->attributes["usercount"];

}

// Get expire dates
$expireDateList = ExpireDate::find_by_sql("SELECT * FROM expire_date");
$expireDateMap = array();
$deliveryMap = array();
foreach($expireDateList as $exDate) {

    $deadline = $exDate->expire_date->format("Y-m-d");
    if(!in_array($deadline,$expireDates))
    {
        $expireDates[] = $deadline;
    }

    $expireDateMap[$exDate->expire_date->format("Y-m-d")] = $exDate->week_no;
        $deliveryMap[$exDate->expire_date->format("Y-m-d")] = $exDate->is_delivery;
}

// Init report 
$report = new CardShopPlukReport();

// Start output
?><html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { padding: 0px; font-size: 12px; font-family: verdana; }
        td { font-size: 0.8em; padding: 5px; text-align: right; }
    </style>
    <script src="/gavefabrikken_backend/views/lib/jquery.min.js"></script>
    <script src="/gavefabrikken_backend/views/lib/jquery-ui/jquery-ui.js"></script>
    <link href="/gavefabrikken_backend/views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">
    <style>
        .shoppluktable { width: 100%; min-width: 500px; }
        .shoppluktable thead th { font-weight: bold; padding: 5px; background: #D0D0D0; border-bottom: 1px solid #555555; font-size: 14px !important;; }
        .shoppluktable tfoot th { font-weight: bold; padding: 5px; background: #D0D0D0;  border-top: 1px solid #555555; font-size: 14px !important;;}
        .shoppluktable tbody td { font-weight: normal; padding: 5px; border-bottom: 1px solid #aaaaaa; border-left: 1px solid #C0C0C0; font-size: 13px !important; }

        .shoppluktable th, .shoppluktable td { text-align: right;}
        .shoppluktable th:first-child, .shoppluktable td:first-child { text-align: left; border-left: none;}

    </style>
</head>
<body>

<div style="margin-bottom: 10px; padding-bottom: 5px;">
    <div class="backbtn" style="display: none; float: right;"><button type="button" onClick="window.parent.bizType.trail('kort')">Gå tilbage til kunder</div>
    <h2>Hent lister for gavekort-shops</h2>
</div>

<div style="padding: 20px; text-align: center; color: red; font-size: 20px;">BEMÆRK: Gamle pluklister fra 2020, brug ikke disse til træk i 2021!</div>

<form method="post" action="<?php echo $report->getUrl("pluk"); ?>/login">

    <?php if($report->getError() != "") echo "<div style='color: red; font-weight: bold; padding: 20px;'>".$report->getError()."</div>"; ?>

    <div style="margin-bottom: 20px; padding-bottom: 5px; border-bottom: 1px solid #999999; border-top: 1px solid #999999; background: white;">

        <table style="width: 100%;">
            <tr>
                <td valign="top" style="text-align: left; width: 33%; padding: 25px;">
                    <h3>Vælg shop / deadline</h3>
                    <table style="width: 100%;">
                        <tr>
                            <td>Shop</td>
                            <td style="text-align: left;">
                                <select name="shopid"><?php
                                    foreach($shoplist as $shop)
                                    {
                                        echo "<option value='".$shop->shop_id."'>".$shop->concept_code." (beløb: ".$shop->card_price.", id: ".$shop->shop_id.")</option>";
                                    }
                                    ?></select>
                            </td>
                        </tr>
                        <tr>
                            <td>Deadline</td>
                            <td style="text-align: left;">
                                <select name="expire"><?php
                                    foreach($expireDates as $date)
                                    {
                                        echo "<option value='".$date."'>".$date." (".(isset( $expireDateMap[$date]) ? "uge ". $expireDateMap[$date] : "ukendt uge").")</option>";
                                    }
                                    ?></select>
                            </td>
                        </tr>

                    </table>
                </td>
                <td valign="top" style="text-align: left; width: 33%; padding: 25px;">
                    <h3>Kriterier for udtræk</h3>
                    <table>
                        <tr>
                            <td>Indpakket</td>
                            <td style="text-align: left;">
                                <select name="wrapped">
                                    <option value="">-</option>
                                    <option value="0">Nej</option>
                                    <option value="1">Ja</option>'
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Opbæring / specialaftale</td>
                            <td style="text-align: left;">
                                <select name="carryup">
                                    <option value="">-</option>
                                    <option value="0">Nej</option>
                                    <option value="1">Ja</option>'
                                </select>
                            </td>
                        </tr>
                    </table>
                </td>
                <td valign="top" style="text-align: left; width: 33%; padding: 25px;">
                    <h3>Rapport typer</h3>
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                <button name="action" value="fetch" style="width: 100%; max-width: 300px; margin-bottom: 5px;">Hent plukliste</button>        <br>
                                <button name="action" value="presentlist" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent gaveliste</button>   <br>
                                <button name="action" value="reminderlist" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent reminderliste</button>         <br>
                                <button name="action" value="customlist" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent BS liste</button>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

    </div>
    <div>
        <h3>Fordeling af gavekort på shop / deadline</h3>



        <table class="shoppluktable" style="width: min-width: 500px;" cellpadding="0" cellspacing="0">
            <thead>
            <tr style="">
                <th style="">Shop</th>
                <?php foreach($expireDates as $date) echo "<th style='cursor: pointer' onclick='cardPlukSelDate(\"".$date."\")'>".(isset( $expireDateMap[$date]) ? "Uge ". $expireDateMap[$date] : "Ukendt uge")."<br>".$date."</th>"; ?>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $dateTotals = array();
            foreach($shoplist as $companyshop)
            {
                $total = 0;
                ?><tr>
                <td style="text-align: left; cursor: pointer;" ondblclick="openPlukShop(<?php echo $companyshop->shop_id ?>)" onclick="cardPlukSelShop('<?php echo $companyshop->shop_id; ?>')"><?php echo $companyshop->concept_code. "(beløb: ".$companyshop->card_price." / id: ".$companyshop->shop_id.")"; ?></td>
                <?php
                foreach($expireDates as $date)
                {

                    $amount = isset($statMatrix[$companyshop->shop_id][$date]) ? intval($statMatrix[$companyshop->shop_id][$date]) : 0;
                    $total+= $amount;
                    echo "<td style='cursor: pointer;' onclick='cardPlukSelShopDate(\"".$companyshop->shop_id."\",\"".$date."\")'>".$amount."</td>";

                    if(!isset($dateTotals[$date])) $dateTotals[$date] = $amount;
                    else $dateTotals[$date] += $amount;

                }
                ?>
                <td><?php echo $total; ?></td>
                </tr><?php


            }

            ?></tbody>
            <tfoot>
            <tr style="font-weight: bold;">
                <th  style="text-align: left;">Total</th>
                <?php

                $total = 0;
                foreach($expireDates as $date)
                {
                    $total += $dateTotals[$date];
                    echo "<th style='cursor: pointer' onclick='cardPlukSelDate(\"".$date."\")'>".$dateTotals[$date]."</th>";

                }

                ?>
                <th><?php echo $total; ?></th>
            </tr>
             <tr>
                <th valign="top" style="background: white !important; font-weight: normal; font-size: 0.8em;">Reminderlister<br><span style="font-size: 0.8em;">Hent for alle shops pr deadline fordelt på sprog</span></th>
                <?php foreach($expireDates as $date) { ?>

                    <th valign="top" style="background: white !important; font-weight: normal; font-size: 12px; line-height: 150%;">
                        <a href="index.php?rt=cardshoppluk/reminder&token=sdfkfk3DD3kdsj3xSS34Df&shops=54,55,56,640,290,310,53,52&deadline=<?php echo $date; ?>">Danmark</a><br>
                        <a href="index.php?rt=cardshoppluk/reminder&token=sdfkfk3DD3kdsj3xSS34Df&shops=574,57,272,58,59&deadline=<?php echo $date; ?>">Norge</a><br>
                        <a href="index.php?rt=cardshoppluk/reminder&token=sdfkfk3DD3kdsj3xSS34Df&shops=1832,1981,5117,4793,8271,9495&deadline=<?php echo $date; ?>">Sverige</a><br>
                        
                    </th>

                <?php } ?>


            </tr>
            <tr>
                <th valign="top" style="background: white !important; font-weight: normal; font-size: 0.8em;">Fragtjournal</th>
                <?php foreach($expireDates as $date) { ?>

                    <th valign="top" style="background: white !important; font-weight: normal; font-size: 12px; line-height: 150%;">
                        <a href="index.php?rt=report/fragt2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $date; ?>&isdk=1">DK - excl drøm</a><br>
                        <a href="index.php?rt=report/fragt2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $date; ?>&isdk=1&isdrom=1">DK - kun drøm</a><br>
                        <a href="index.php?rt=report/fragt2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $date; ?>&isdk=0">NO - alle norske</a><br>
                        <i>SE - ikke klar</i>
                    </th>

                <?php } ?>


            </tr>
            <tr>
                <th valign="top" style="background: white !important; font-weight: normal; font-size: 0.8em;">Gavevalg</th>
                <?php foreach($expireDates as $date) { ?>

                    <th valign="top" style="background: white !important; font-weight: normal; font-size: 12px; line-height: 150%;">
                        <a href="index.php?rt=report/valg2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $date; ?>&isdk=1">DK - excl drøm</a><br>
                        <a href="index.php?rt=report/valg2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $date; ?>&isdk=1&isdrom=1">DK - kun drøm</a><br>
                        <?php /* ?><a href="index.php?rt=report/fragt2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $date; ?>&isdk=0">NO - alle norske</a><br><?php */ ?>
                        <?php /* ?><i>SE - ikke klar</i><?php */ ?>
                    </th>

                <?php } ?>


            </tr>
            <tr>
                <th valign="top" style="background: white !important; font-weight: normal; font-size: 0.8em;">Privatlevering</th>
                <?php foreach($expireDates as $date) { ?>

                    <th valign="top" style="background: white !important; font-weight: normal; font-size: 12px; line-height: 150%;">
                        <?php if($deliveryMap[$date] == 1) { ?>
                            <a href="index.php?rt=report/gavekortLeveringTestRapport&lang=da&token=fj4kdVd21&deadline=<?php echo $date; ?>">Danmark</a><br>
                            <a href="index.php?rt=report/gavekortLeveringTestRapport&lang=se&token=fj4kdVd21&deadline=<?php echo $date; ?>">Sverige</a><br>
                            <a href="index.php?rt=report/gavekortLeveringTestRapport&lang=no&token=fj4kdVd21&deadline=<?php echo $date; ?>">Norge</a><br>
                        <?php } else { ?>&nbsp;<?php } ?>

                    </th>

                <?php } ?>


            </tr>
           
            </tfoot>

        </table>

    </div>

    <script>
    
        function openPlukShop(shopid) {
            window.open('index.php?rt=mainaa&editShopID='+shopid, '_blank');
        }

        function cardPlukSelDate(date)
        {
            $('select[name=expire]').val(date);
        }

        function cardPlukSelShop(shop)
        {
            $('select[name=shopid]').val(shop)
        }

        function cardPlukSelShopDate(shopid,date) {
            cardPlukSelDate(date);
            cardPlukSelShop(shopid);
        }

        $(document).ready(function() {

            if(window.hasOwnProperty('parent') && window.parent.hasOwnProperty('bizType')) {
                $('.backbtn').show();
            }

        });

    </script>
</form>
</body>
</html>