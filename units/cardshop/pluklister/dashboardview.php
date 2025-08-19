<html>
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
    <h2>Hent lister for gavekort-shops</h2>
</div>

<form method="post" action="<?php echo $servicePath; ?>plukdispatch">

    <?php /* if($report->getError() != "") echo "<div style='color: red; font-weight: bold; padding: 20px;'>".$report->getError()."</div>"; */ ?>

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

                                    $lang = 0;
                                    foreach($model->getShopSettingsList() as $shop)
                                    {

                                        if($lang != $shop->language_code) {
                                            if($lang != 0) echo "</optgroup>";
                                            echo "<optgroup label=\"".GFCommon\Model\Navision\CountryHelper::countryToCode($shop->language_code)."\">";
                                        }
                                        $lang =  $shop->language_code;
                                        echo "<option value='".$shop->shop_id."'>".utf8_encode($shop->concept_code)." (beløb: ".$shop->card_price.", id: ".$shop->shop_id.")</option>";
                                    }
                                    ?></optgroup></select>
                            </td>
                        </tr>
                        <tr>
                            <td>Deadline</td>
                            <td style="text-align: left;">
                                <select name="expire"><?php
                                    foreach($model->getExpireDates() as $date)
                                    {
                                        echo "<option value='".$date->expire_date->format("Y-m-d")."'>".$date->expire_date->format("Y-m-d")." (uge ".$date->week_no.") ".($date->is_delivery == 1 ? "- PRIVAT" : "")."</option>";
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
                                    <option value="1">Ja</option>
                                    <option value="2">Navnelapper</option>
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
                        <tr>
                            <td>Over / under 40 gaver</td>
                            <td style="text-align: left;">
                                <select name="largesmall"> 
                                    <option value="">-</option>
                                    <option value="0">Under 40 gaver</option>
                                    <option value="1">40 gaver eller flere</option>'
                                </select>
                            </td>
                        </tr>
                    </table>
                </td>
                <td valign="top" style="text-align: left; width: 33%; padding: 25px;">
                    <h3>Rapport typer</h3>
                    <table style="width: 100%;">
                        <tr>
                            <td  style="text-align: center;">
                                <button name="action" value="fetch" style="width: 100%; max-width: 300px; margin-bottom: 5px;" id="shortcutbtn">Hent plukliste</button>        <br>
                                <button name="action" value="presentlist" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent gaveliste</button>   <br>
                                <button name="action" value="reminderlist" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent reminderliste for valgte shop</button><br>

                                <button name="action" value="customlist" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent BS liste</button><br>
                                <button name="action" value="prepaymentlist" onclick="alert('Henter listen, dette kan tage noget tid da alle ordre skal tjekkes i navision, tryk derfor ikke på knappen flere gange i træk!');" style="width: 100%; max-width: 300px; margin-bottom: 5px;">Hent betalingsliste</button><br>
                                <button name="action" value="privatlevering" style="width: 100%; max-width: 300px; margin-bottom: 5px;">Hent privatleveringsliste</button>
                            </td>
                            <td  style="text-align: center;" valign="top">
                                <button name="action" value="reminderlistdk" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent reminderliste - alle danske</button><br>
                                <button name="action" value="reminderlistse" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent reminderliste - alle svenske</button><br>
                                <button name="action" value="reminderlistno" style="width: 100%;  max-width: 300px; margin-bottom: 5px;">Hent reminderliste - alle norske</button><br>
                            </td>
                        </tr>
                    </table>
                    <script>

                        document.addEventListener('keydown', function(event) {
                            console.log(event.key.toLowerCase());
                            if (event.shiftKey && event.key.toLowerCase() === 'd') {
                                document.getElementById('shortcutbtn').click();
                            }
                        });
                    </script>
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
                <?php foreach($model->getExpireDates() as $date) echo "<th style='cursor: pointer' onclick='cardPlukSelDate(\"".$date->expire_date->format("Y-m-d")."\")'> uge ".$date->week_no." ".($date->is_delivery == 1 ? "(priv)" : "")."<br>".$date->expire_date->format("Y-m-d")."</th>"; ?>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $dateTotals = array();
            $statMatrix = $model->getStatMatrix();
            foreach($model->getShopSettingsList() as $companyshop)
            {
                $total = 0;
                ?><tr>
                <td style="text-align: left; cursor: pointer;" ondblclick="openPlukShop(<?php echo $companyshop->shop_id ?>)" onclick="cardPlukSelShop('<?php echo $companyshop->shop_id; ?>')"><?php echo utf8_encode($companyshop->concept_code). "(beløb: ".$companyshop->card_price." / id: ".$companyshop->shop_id.")"; ?></td>
                <?php
                foreach($model->getExpireDates() as $date)
                {
                    $dateText = $date->expire_date->format("Y-m-d");
                    $amount = isset($statMatrix[$companyshop->shop_id][$dateText]) ? intval($statMatrix[$companyshop->shop_id][$dateText]) : 0;
                    $total+= $amount;
                    echo "<td style='cursor: pointer;' onclick='cardPlukSelShopDate(\"".$companyshop->shop_id."\",\"".$dateText."\")'>".$amount."</td>";
                    if(!isset($dateTotals[$dateText])) $dateTotals[$dateText] = $amount;
                    else $dateTotals[$dateText] += $amount;
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
                foreach($model->getExpireDates() as $date)
                {
                    $dateText = $date->expire_date->format("Y-m-d");
                    $total += $dateTotals[$dateText];
                    echo "<th style='cursor: pointer' onclick='cardPlukSelDate(\"".$dateText."\")'>".$dateTotals[$dateText]."</th>";

                }

                ?>
                <th><?php echo $total; ?></th>
            </tr>
            <tr style="display: none;">
                <th valign="top" style="background: white !important; font-weight: normal; font-size: 0.8em;">Reminderlister<br><span style="font-size: 0.8em;">Hent for alle shops pr deadline fordelt på sprog</span></th>
                <?php foreach($model->getExpireDates() as $date) {
                    $dateText = $date->expire_date->format("Y-m-d");
                    ?><th valign="top" style="background: white !important; font-weight: normal; font-size: 12px; line-height: 150%;">
                        <a href="index.php?rt=cardshoppluk/reminder&token=sdfkfk3DD3kdsj3xSS34Df&shops=54,55,56,640,290,310,53,52&deadline=<?php echo $dateText; ?>">Danmark</a><br>
                        <a href="index.php?rt=cardshoppluk/reminder&token=sdfkfk3DD3kdsj3xSS34Df&shops=574,57,272,58,59&deadline=<?php echo $dateText; ?>">Norge</a><br>
                        <a href="index.php?rt=cardshoppluk/reminder&token=sdfkfk3DD3kdsj3xSS34Df&shops=1832,1981,4793,5117,8271&deadline=<?php echo $dateText; ?>">Sverige</a><br>
                    </th><?php
                } ?>

            </tr>
            <tr style="display: none;">
                <th valign="top" style="background: white !important; font-weight: normal; font-size: 0.8em;">Fragtjournal</th>
                <?php foreach($model->getExpireDates() as $date) {
                    $dateText = $date->expire_date->format("Y-m-d");
                    ?><th valign="top" style="background: white !important; font-weight: normal; font-size: 12px; line-height: 150%;">
                        <a href="index.php?rt=report/fragt2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $dateText; ?>&isdk=1">DK - excl drøm</a><br>
                        <a href="index.php?rt=report/fragt2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $dateText; ?>&isdk=1&isdrom=1">DK - kun drøm</a><br>
                        <a href="index.php?rt=report/fragt2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $dateText; ?>&isdk=0">NO - alle norske</a><br>
                        <i>SE - ikke klar</i>
                    </th><?php
                } ?>
            </tr>
            <tr style="display: none;">
                <th valign="top" style="background: white !important; font-weight: normal; font-size: 0.8em;">Gavevalg</th>
                <?php foreach($model->getExpireDates() as $date) {
                    $dateText = $date->expire_date->format("Y-m-d");
                    ?><th valign="top" style="background: white !important; font-weight: normal; font-size: 12px; line-height: 150%;">
                        <a href="index.php?rt=report/valg2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $dateText; ?>&isdk=1">DK - excl drøm</a><br>
                        <a href="index.php?rt=report/valg2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $dateText; ?>&isdk=1&isdrom=1">DK - kun drøm</a><br>
                        <?php /* ?><a href="index.php?rt=report/fragt2020Rapport&token=324jlh2345jkFSd12jcvmcpq463q&expiredate=<?php echo $date; ?>&isdk=0">NO - alle norske</a><br><?php */ ?>
                        <?php /* ?><i>SE - ikke klar</i><?php */ ?>
                    </th><?php
                } ?>

            </tr>
            <tr style="display: none;">
                <th valign="top" style="background: white !important; font-weight: normal; font-size: 0.8em;">Privatlevering</th>
                <?php foreach($model->getExpireDates() as $date) {
                    $dateText = $date->expire_date->format("Y-m-d");
                    ?><th valign="top" style="background: white !important; font-weight: normal; font-size: 12px; line-height: 150%;">
                        <?php if($date->is_delivery == 1) { ?>
                            <a href="index.php?rt=report/gavekortLeveringTestRapport&lang=da&token=fj4kdVd21&deadline=<?php echo $dateText; ?>">Danmark</a><br>
                            <a href="index.php?rt=report/gavekortLeveringTestRapport&lang=se&token=fj4kdVd21&deadline=<?php echo $dateText; ?>">Sverige</a><br>
                            <a href="index.php?rt=report/gavekortLeveringTestRapport&lang=no&token=fj4kdVd21&deadline=<?php echo $dateText; ?>">Norge</a><br>
                        <?php } else { ?>&nbsp;<?php } ?>
                    </th><?php
                } ?>


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