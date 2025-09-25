<?php

namespace GFUnit\privatedelivery\sedsv;


class Dashboard
{

    private $helper;

    public function __construct()
    {

        $this->helper = new Helpers();

    }

    public function dispatch() {

        $vareNrList = $this->helper->getVarenrList();

        
        ?><html>
        <head>
            <style>

                body { margin: 0px; padding: 0px; font-family: verdana; font-size: 14px; line-height: 120%; }

                * {
                    box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    -webkit-box-sizing: border-box;
                }

                .header { background: #7895B2; width: 100%; height: 50px; overflow: hidden; padding: 15px; font-size: 24px; font-weight: bold; color: white; }


                .dsvtable { width: 100%; }


                .dsvTable thead {
                    position: sticky; top: 0; z-index: 1;
                }

                .dsvtable thead tr th {
                    background: #AEBDCA; color: white; padding: 5px; border-bottom: 1px solid #AEBDCA; font-size: 14px; font-weight: bold; text-align: left;
                }

                .dsvtable tbody tr td {
                    padding: 5px; border-bottom: 1px solid #AEBDCA; font-size: 14px; text-align: left;
                }

                .dsvtable tbody tr.sampak td {
                    background: #E8DFCA; padding: 5px; border-bottom: 1px solid #AEBDCA; font-size: 14px; text-align: left; font-weight: bold;
                }

                th.num { text-align: right !important;}
                td.num { text-align: right !important;}

                tr.usedbefore { background: #FFD4B2 !important; }

                .calcerr { background: red !important; color: white; }
                .calcok { background: #86C8BC !important; color: white; }
                .calczero { background: #CEEDC7 !important; color: black; }
                .calcempty {  }

                tr.hidden { display: none; }

                .footerplaceholder { height: 50px; background: blue; }
                .footer { height: 50px; width: 100%; position: fixed; bottom: 0px;background: #7895B2; }

                .footer a { color: white !important; }

            </style>
            <script src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/lib/jquery.min.js"></script>
        </head>
        <body>
        <div class="header">
            <div style="float: right;">
                <button type="button" onclick="document.location='index.php?rt=unit/privatedelivery/sedsv/lagerrapport'">Træk lager rapport</button>
                <button type="button" onclick="document.location='index.php?rt=unit/privatedelivery/sedsv/index'">Gå til fil-oversigt</button>
            </div>
            DSV Vareoversigt
        </div>
        
        
        <?php
        
        if(!DSVLager::checkStocklistUpdated()) {
            echo "<div style='background: red; color: white; padding: 10px;'>Lagerlisten er ikke opdateret. <a href='index.php?rt=unit/privatedelivery/sedsv/index'>Klik her for at opdatere</a></div>";
        }

        if(!DSVLager::checkOrderlistUpdated()) {
            echo "<div style='background: lightcoral; color: white; padding: 10px;'>Ordrelisten er ikke opdateret. <a href='index.php?rt=unit/privatedelivery/sedsv/index'>Klik her for at opdatere</a></div>";
        }

        DSVLager::getOrderLines();
        
        
        ?>

        <form method="post" id="sedvsplukform" action="index.php?rt=unit/privatedelivery/sedsv/createpluk">
        <table class="dsvtable" style="width: 100%" cellspacing="0" cellpadding="0">

            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>Varenr</th>
                    <th>Beskrivelse</th>
                    <th class="num">Antal pr. valg</th>
                    <th class="num">Venter ved GF</th>
                    <th class="num">Venter ved DSV</th>
                    <th class="num">På lager DSV</th>
                    <th class="num">DSV Status</th>
                    <th class="num">Til træk</th>
                    <th class="num">Efter træk</th>
                    <th class="num">Forecast</th>
                    <th class="num">Valg 7 / 14 dage</th>
                    <th class="num">Venter frigivelse</th>
                    <th class="num">Antal sendt</th>

                </tr>
            </thead>
            <tbody>

            <?php // Output sampak
            foreach($vareNrList as $varenr) {
                if($this->helper->isSampakVarenr($varenr)) {
                    $this->outputVarenrSampak($varenr);
                }
            } ?></tbody>
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Varenr</th>
                <th>Beskrivelse</th>
                <th class="num">Antal pr. valg</th>
                <th class="num">Venter ved GF</th>
                <th class="num">Venter ved DSV</th>
                <th class="num">På lager DSV</th>
                <th class="num">DSV Status</th>
                <th class="num">Til træk</th>
                <th class="num">Efter træk</th>
                <th class="num">Forecast</th>
                <th class="num">Valg 7 / 14 dage</th>
                <th class="num">Venter frigivelse</th>
                <th class="num">Antal sendt</th>

            </tr>
            </thead><tbody>
            <?php

            foreach($vareNrList as $varenr) {
                if(!$this->helper->isSampakVarenr($varenr)) {
                    $this->outputVarenrLine($varenr,null);
                }
            }

            ?></tbody>

        </table>
        </form>

        <div class="footerplaceholder"></div>
        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 20%;padding: 10px;"><button type="button" onclick="showHiddenLines()">Vis skjulte linjer</button></td>
                    <td style="width: 20%;padding: 10px; color: white;">Antal varenr valgt: <div style="display: inline-block;" id="varenrcount"></div></td>
                    <td style="width: 20%;padding: 10px; color: white;">Antal varer valgt: <div style="display: inline-block;" id="varercount"></div></td>
                    <td style="width: 20%;padding: 10px;"><a href="javascript:selectAll()">vælg alle</a> | <a href="javascript:selectNone()">vælg ingen</a></td>
                    <td style="width: 20%;padding: 10px;"><button type="button" onclick="$('#sedvsplukform').submit();">Opret plukliste</button></td>
                </tr>
            </table>
        </div>

        <script>

            function showHiddenLines() {
                $('tr.hidden').show();
            }

            function selectAll() {
                $('.varecheck').prop('checked','checked')
            }

            function selectNone() {
                $('.varecheck').attr('checked',false)
            }

            function updateInput(elm) {
                console.log('UPDATE CALC');

                var quantity = parseInt($(elm).val());
                var lager = parseInt($(elm).parent().prev().prev().text());
                var lagerwait = parseInt($(elm).parent().prev().prev().prev().text());
                var waiting = parseInt($(elm).parent().prev().prev().prev().prev().text());

                var calcValue = "ERR";
                var calcClass = "calcerr";

                if(isNaN(quantity) || isNaN(lager) || isNaN(waiting)|| isNaN(lagerwait)) {

                } else if(quantity+lagerwait > lager) {
                    calcValue = lager-lagerwait-quantity;
                } else if(quantity == 0) {
                    calcClass = 'calcempty';
                    calcValue = lager-lagerwait-quantity;
                } else if(quantity+lagerwait == lager) {
                    calcClass = 'calczero';
                    calcValue = lager-lagerwait-quantity;
                } else if(lager > quantity+lagerwait) {
                    calcClass = 'calcok';
                    calcValue = lager-lagerwait-quantity;
                }

                if(quantity > waiting) {
                    calcClass = 'calcerr';
                }


                $(elm).parent().next().attr('class',"num "+calcClass).html(calcValue);

                updateCounters();

            }

            function updateCounters() {

                if(!countersActive) return;

                var varenrtotal = 0;
                var varertotal = 0;

                $('.varecheck:checked').each(function() {
                    var quant = parseInt($(this).parent().parent().find('.pullquantity').val());
                    console.log(quant);
                    if(!isNaN(quant) && quant > 0) {
                        varenrtotal++
                        varertotal+= quant;
                    }
                });



                $('#varenrcount').html(varenrtotal);
                $('#varercount').html(varertotal);


            }

            var countersActive = false;

            $(document).ready(function() {

                $('.pullquantity').each(function() {

                    var elm = this;

                    $(this).bind('change',function() { updateInput(elm); });
                    $(this).bind('keyup',function() { updateInput(elm); });
                    $(this).bind('keydown',function() { updateInput(elm); });
                    updateInput(this);

                });

                $('.varecheck').bind('change',function() { updateCounters(); })

                countersActive = true;
                updateCounters();

            });

        </script>

        </body>
        </html>
        <?php

/*
        $lagerLines = DSVLager::getLagerQuantityMap();
        print_r($lagerLines);
*/
        echo "</pre>";


    }

    private $usedVarenr = array();

    private function outputVarenrLine($varenr,$sampakNr=null,$sampakHidden=false,$sampak7=0,$sampak14=0,$samForecast=0)
    {


        $isSingleLine = ($sampakNr == null);

        // Get vare
        try {
            $vare = $this->helper->getNavVare($varenr);
        }
        catch(\Exception $e) {
            echo "Varenr $varenr findes ikke i NAV (".$sampakNr.")<br>";
            return;
        }

        $prShipment = 1;
        if(!$isSingleLine) {

           $prShipment = $this->helper->getBomItemQuantity($sampakNr,$varenr);
          
            // Already sent
            $alreadySent = $this->helper->getShipmentsHandledCount($sampakNr);

          
            // Waiting
            $waitingGFCount = $this->helper->getShipmentsWaitingCount($sampakNr);
            $waitingDSVCount = $this->helper->getShipmentsWaitingAtDSVCount($sampakNr);

           
            $last7Days = $sampak7*$prShipment;
            $last14Days = $sampak14*$prShipment;

            $forecastValue = $samForecast * $prShipment;

            $pipelineValue = $this->helper->getOrderPipelineValue($sampakNr) * $prShipment;

        }
        else {

           
            // Already sent
            $alreadySent = $this->helper->getShipmentsHandledCount($varenr);

            // Waiting
            $waitingGFCount = $this->helper->getShipmentsWaitingCount($varenr);
            $waitingDSVCount = $this->helper->getShipmentsWaitingAtDSVCount($varenr);

            $last7Days = $this->helper->getLast7DaysSelected($varenr);
            $last14Days = $this->helper->getLast14DaysSelected($varenr);

            $forecastValue = $this->helper->getForecastAll($varenr);

            $pipelineValue = $this->helper->getOrderPipelineValue($varenr);

        }


        $usedBefore = in_array($varenr,$this->usedVarenr);
        $this->usedVarenr[] = $varenr;

        $dsvLager = $this->helper->getDSVLagerQuantity($varenr);
        if($dsvLager - $waitingDSVCount <= 0) $canSend = 0;
        else {
            $canSend = $waitingGFCount;
            if($dsvLager- $waitingDSVCount < $canSend) $canSend = $dsvLager- $waitingDSVCount;
        }
        if($canSend < 0) $canSend = 0;



        /*
        if($varenr ==  "96-mec136") {
            print_r(array("dsvlager" => $dsvLager, "canSend" => $canSend, "waitingGFCount" => $waitingGFCount, "waitingDSVCount" => $waitingDSVCount, "alreadySent" => $alreadySent));
            exit();
        }
        */


        $hidden = false;
        if($isSingleLine) {

            if($dsvLager == 0 && $waitingGFCount == 0 && $alreadySent == 0) {
                $hidden = true;
            }
            if($waitingGFCount == 0) $hidden = true;

        } else {
            $hidden = $sampakHidden;
        }


        ?><tr class="<?php if($hidden) echo " hidden "; if($usedBefore) echo " usedbefore"; ?>">
        <td><?php if($isSingleLine) { ?><input type="checkbox" class="varecheck" name="<?php echo $varenr ?>" value="1" <?php if($canSend > 0) echo "checked"; ?>><?php } else echo "&nbsp;"; ?></td>
        <td><?php echo $varenr; ?></td>
        <td><?php echo $vare->description; ?></td>
        <td class="num"><?php echo $prShipment; ?></td>

        <td class="num"><?php echo $prShipment*$waitingGFCount; ?></td>
        <td class="num"><?php echo $prShipment*$waitingDSVCount; ?></td>
        <td class="num"><?php echo $dsvLager; ?></td>
        <td class="num"><?php echo ($dsvLager-($prShipment*$waitingGFCount+$prShipment*$waitingDSVCount)); ?></td>
        <?php if($isSingleLine) { ?><td class="num"><input type="text" class="pullquantity" name="quantity_<?php echo $varenr; ?>" value="<?php echo $canSend; ?>" style="width: 50px;"></td>
            <td class="num afterpull">calc</td><?php }
        else { ?><td class="num">&nbsp;</td><td class="num">&nbsp;</td><?php } ?>

        <td class="num"><?php echo $forecastValue; ?></td>
        <td class="num"><?php echo $last7Days." - ".$last14Days; ?></td>
        <td class="num"><?php echo $pipelineValue; ?></td>
        <td class="num"><?php echo $prShipment*$alreadySent; ?></td>

        </tr><?php

    }

    private function outputVarenrSampak($sampakVarenr) {

        // Get vare
        $vare = $this->helper->getNavVare($sampakVarenr);

        // Already sent
        $alreadySent = $this->helper->getShipmentsHandledCount($sampakVarenr);

        // Waiting
        $waitingGFCount = $this->helper->getShipmentsWaitingCount($sampakVarenr);
        $waitingDSVCount = $this->helper->getShipmentsWaitingAtDSVCount($sampakVarenr);

        // Count max on lager
        $maxNo = null;
        $subVarenr = $this->helper->getItemsInSampak($sampakVarenr);
        foreach($subVarenr as $subNo) {

            $subQuantity = $this->helper->getBomItemQuantity($sampakVarenr,$subNo);
            $subLager = $this->helper->getDSVLagerQuantity($subNo);
            $canMake = $subQuantity == 0 ? 0 : floor($subLager/$subQuantity);
            if($maxNo === null || $maxNo > $canMake) {
                $maxNo = $canMake;
            }

        }

        if($maxNo < 0) $maxNo = 0;

        $canSend = $waitingGFCount;
        if($canSend > $maxNo - $waitingDSVCount) $canSend = $maxNo- $waitingDSVCount;
        if($canSend < 0) $canSend = 0;

        $sentSinceLagerUpdate = $this->helper->getSentSinceLastLagerUpdate($sampakVarenr);
        if($sentSinceLagerUpdate > 0 && $canSend > 0) {
            $canSend -= $sentSinceLagerUpdate;
            $maxNo -= $sentSinceLagerUpdate;
        }
        if($canSend < 0) $canSend = 0;
        if($maxNo < 0) $maxNo = 0;

        $hidden = false;
        if($maxNo == 0 && $waitingGFCount == 0 && $waitingDSVCount == 0 && $alreadySent == 0) {
            $hidden = true;
        }

        if($waitingGFCount == 0 && $waitingDSVCount == 0) $hidden = true;



        $last7Days = $this->helper->getLast7DaysSelected($sampakVarenr);
        $last14Days = $this->helper->getLast14DaysSelected($sampakVarenr);

        $forecastValue = $this->helper->getForecastAll($sampakVarenr,false);
        if(!is_int($forecastValue)) $forecastValue = "";

        $pipelineValue = $this->helper->getOrderPipelineValue($sampakVarenr);


        // Output sampak line
        ?><tr class="sampak <?php if($hidden) echo "hidden"; ?>">
            <td><input type="checkbox" class="varecheck" name="<?php echo $sampakVarenr ?>" <?php if($canSend > 0) echo "checked"; ?> value="1"></td>
            <td><?php echo $sampakVarenr; ?></td>
            <td><?php echo $vare->description; ?></td>
            <td class="num">1</td>
            <td class="num"><?php echo $waitingGFCount; ?></td>
            <td class="num"><?php echo $waitingDSVCount; ?></td>
            <td class="num"><?php echo $maxNo; ?></td>
            <td class="num"><?php echo $maxNo - $waitingGFCount - $waitingDSVCount; ?></td>
            <td class="num"><input type="text" class="pullquantity" name="quantity_<?php echo $sampakVarenr ?>" value="<?php echo $canSend; ?>" style="width: 50px;"></td>
            <td class="num afterpull">calc</td>
            <td class="num"><?php echo $forecastValue;  ?></td>
            <td class="num"><?php echo $last7Days." - ".$last14Days; ?></td>
            <td class="num"><?php echo $pipelineValue; ?></td>
            <td class="num"><?php echo $alreadySent." - ".$sentSinceLagerUpdate; ?></td>

        </tr><?php

        // Output child lines
        foreach ($subVarenr as $varenr) {
            $this->outputVarenrLine($varenr,$sampakVarenr,$hidden,$last7Days,$last14Days,$this->helper->getForecastAll($sampakVarenr,true));
        }

    }


}