<?php

if(!router::isRouted()) exit();
if(!\GFCommon\Model\Access\BackendPermissions::session()->hasPermission(\GFCommon\Model\Access\BackendPermissions::PERMISSION_SYSTEM))
{
    throw new exception('Du har ikke rettighed til denne funktion!');
}

include("system_nav.php");


$shopidlist = array();
$expireDates = array();
$statMatrix = array();

// Get shops
$shoplist = Shop::find_by_sql("SELECT * FROM `shop` where is_gift_certificate = 1 && id NOT IN (262,569,287,247,248,264,263,265,251) ORDER BY `shop`.`name` ASC");
$shopidlist = array();
foreach($shoplist as $shop)
{
    $shopidlist[] = $shop->id;
}

// Get dates
$shopUserDeadlines = ShopUser::find_by_sql("SELECT expire_date, shop_id, COUNT(id) as usercount FROM `shop_user` WHERE shop_id IN (".implode(",",$shopidlist).") && blocked = 0 && is_demo = 0 && expire_date IS NOT NULL GROUP BY expire_date, shop_id ORDER BY expire_date ASC");
foreach($shopUserDeadlines as $shopusercount)
{
    $deadline = $shopusercount->expire_date->format("Y-m-d");
   /*
    if(!in_array($deadline,$expireDates))
    {
        $expireDates[] = $deadline;
    }
    */
    $statMatrix[$shopusercount->shop_id][$deadline] = $shopusercount->attributes["usercount"];

}

// Get expire dates
$expireDateList = ExpireDate::find_by_sql("SELECT * FROM expire_date ORDER BY `expire_date`.`expire_date` ASC");
$expireDateMap = array();
foreach($expireDateList as $exDate) {
    $deadline = $exDate->expire_date->format("Y-m-d");
    $expireDates[] = $deadline;
    $expireDateMap[$exDate->expire_date->format("Y-m-d")] = $exDate->week_no;
}

// Get expire dates
$reservationGroupList = GiftCertificate::find_by_sql("SELECT * FROM `reservation_group`");
$reservationGroupListWithEmail = array_merge(array(json_decode(json_encode(array("id" => 0, "name" => "E-mails")))),$reservationGroupList);

// Stats
$usageStats = \GFCommon\DB\GiftCertificate::getUsageStats();
$usageMap = array();
foreach($usageStats as $stat)
{
    if(!isset($usageMap[$stat["reservation_group"]])) $usageMap[$stat["reservation_group"]] = array();
    if(!isset($usageMap[$stat["reservation_group"]][$stat["expire_date"]])) $usageMap[$stat["reservation_group"]][$stat["expire_date"]] = array("count" => $stat["Created"], "used" => $stat["Used"],"exported" => $stat["Exported"]);
}
/*
echo "<div style='text-align: left;'><pre>";
print_r($usageMap);
echo "</pre></div>";
*/
?>

<style>
    .carddashtable td { padding: 5px; font-size: 11px; border: 1px solid #A0A0A0; background: white;}
    .carddashtable tr:first-child td {background: #F0F0F0; font-weight: bold; }
    .carddashtable td:first-child { background: #F0F0F0; font-weight: bold; }
</style>


<div style="width: 300px; margin-left: auto; margin-right: auto; display: none;" id="giftcertcreateform">
    <div style="padding: 10px; margin: 10px; margin-top: 0px; border: 1px solid #CCCCCC; background: #F0F0F0;">
        <h3>Opret gavekort</h3>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 4px;">Antal</td>
                <td style="padding: 4px;"><input type="text" id="newcertificates_amount" style="width: 100%;"></td>
            </tr>
            <tr>
                <td style="padding: 4px;">Koncept</td>
                <td style="padding: 4px;"><select id="newcertificates_reservationgroup"  class="reservationgroupselect" style="width: 100%;"><?php
                        foreach($reservationGroupList as $reservationGroup)
                        {
                            echo "<option value='".$reservationGroup->id."'>".$reservationGroup->name."</option>";
                        }
                        ?></select></td>
            </tr>
            <tr>
                <td style="padding: 4px;">Deadline</td>
                <td style="padding: 4px;"><select id="newcertificates_expire"  class="expiredateselect" style="width: 100%;"><?php
                        foreach($expireDates as $date)
                        {
                            echo "<option value='".$date."'>".$date." (".(isset( $expireDateMap[$date]) ? "uge ". $expireDateMap[$date] : "ukendt uge").")</option>";
                        }
                        ?></select></td>
            </tr>
            <tr>
                <td style="padding: 4px;">Print</td>
                <td style="padding: 4px;"><input type="checkbox" value="1" id="newcertificates_isprint"></td>
            </tr>
            <tr>
                <td style="padding: 4px;">Levering</td>
                <td style="padding: 4px;"><input type="checkbox" value="1" id="newcertificates_isdelivery" ></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td style="padding: 4px;"><button id="giftcertificate_submit" onclick="giftcertificateBatch.createCertificates()">Opret gavekort</button></td>
            </tr>

        </table>
    </div>
</div>


<div style="width: 300px; margin-left: auto; margin-right: auto; display: none;" id="giftcertexportform">
    <div style="padding: 10px; margin: 10px; margin-top: 0px; border: 1px solid #CCCCCC; background: #F0F0F0;">
        <h3>Eksporter gavekort</h3>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 4px;">Antal</td>
                <td style="padding: 4px;"><input type="text" id="exportcertificates_amount" style="width: 100%;"></td>
            </tr>
            <tr>
                <td style="padding: 4px;">Koncept</td>
                <td style="padding: 4px;"><select id="exportcertificates_reservationgroup" class="reservationgroupselect" style="width: 100%;"><?php
                        foreach($reservationGroupList as $reservationGroup)
                        {
                            echo "<option value='".$reservationGroup->id."'>".$reservationGroup->name."</option>";
                        }
                        ?></select></td>
            </tr>
            <tr>
                <td style="padding: 4px;">Deadline</td>
                <td style="padding: 4px;"><select id="exportcertificates_expire" class="expiredateselect" style="width: 100%;"><?php
                        foreach($expireDates as $date)
                        {
                            echo "<option value='".$date."'>".$date." (".(isset( $expireDateMap[$date]) ? "uge ". $expireDateMap[$date] : "ukendt uge").")</option>";
                        }
                        ?></select></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td style="padding: 4px;"><button id="giftcertificateexport_submit" onclick="giftcertificateBatch.exportCertificates()">Eksporter gavekort</button></td>
            </tr>

        </table>
    </div>
</div>

<table class="carddashtable" style="width: 100%;">

    <tr>
        <td style="text-align: center;">
            <?php if(\GFCommon\Model\Access\BackendPermissions::isDeveloper()) { ?><a href="javascript:$('#giftcertcreateform').slideDown()">opret nye gavekort</a> | <a href="javascript:$('#giftcertexportform').slideDown()">hent gavekort</a><?php } ?>
            
        </td>
        <?php
        foreach($expireDates as $date)
        {
            echo "<td>".$date."</td>";
        }
        ?>
    </tr>
    <?php foreach($reservationGroupListWithEmail as $reservationGroup) { ?>
        <tr>
            <td><?php echo $reservationGroup->name ?> (<?php echo $reservationGroup->id; ?>)</td>
            <?php
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
                $exportUsedPercentage = $cardsCreated == 0 ? 0 : intval((($cardsExported/$cardsCreated))*100);



                $exportUsedPercentage = $cardsExported == 0 ? 0 : intval(($cardsUsed/$cardsExported)*100);

                if($cardsCreated == 0) {
                    echo "<td  ondblclick='giftcertificateBatch.selectCardCell(\"".$date."\",\"".$reservationGroup->id."\")'>&nbsp;</td>";
                }
                else {

                    $soldWarnStyle = "";
                    if($usedPercentage > 90) $soldWarnStyle = "background:  red; color: white;";
                    else if($usedPercentage > 80) $soldWarnStyle = "background: yellow;";
                    else if($usedPercentage > 60) $soldWarnStyle = "background: lightyellow;";

                    $exportWarnStyle = "";
                    if($exportUsedPercentage > 90) $exportWarnStyle = "background:  red; color: white;";
                    else if($exportUsedPercentage > 80) $exportWarnStyle = "background: yellow;";
                    else if($exportUsedPercentage > 60) $exportWarnStyle = "background: lightyellow;";

                    echo "<td ondblclick='giftcertificateBatch.selectCardCell(\"".$date."\",\"".$reservationGroup->id."\")'>";
                    echo "<div style='padding: 4px;'>Antal: ".$cardsCreated."</div>";
                    echo "<div style='padding: 4px; ".$soldWarnStyle."'>Solgt: ".$cardsUsed." - ".intval($usedPercentage)."%</div>";
                    if($reservationGroup->id > 0) echo "<div style='padding: 4px; ".$exportWarnStyle."'>Printet: ".$cardsExported." - ".intval($exportUsedPercentage)."%</div>";

/*                    if($usedPercentage > 90) echo "background:  red; color: white;";
                    else if($usedPercentage > 80) echo "background: yellow;";
                    else if($usedPercentage > 60) echo "background: lightyellow;";
                    echo "'>Antal: ".$cardsCreated."<br>Printet:<br>Solgt: ".$cardsUsed." - ".$usedPercentage."%
*/
                    echo "</td>";

                }

            }
            ?>
        </tr>
    <?php } ?>

</table>

