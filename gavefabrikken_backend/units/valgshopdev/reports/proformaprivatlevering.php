<?php

namespace GFUnit\valgshop\reports;
use GFBiz\units\UnitController;
use GFBiz\valgshop\ValgshopFordeling;
use GFCommon\Model\Navision\SalesHeaderWS;

class ProformaPrivatLevering
{
    private const VALIDATE_PASSWORD = "gr2Yd75j";

    public function dispatch($url, $validate)
    {
        //echo "PROFORMA DISPATCHER: " . $url . " / " . ($validate ? "VALIDATE" : "NO VALIDATE") . "\n";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            if(!$this->validatePassword($url)) {
                return;
            }
        }

        if ($validate) {
            if (!isset($_SESSION["proformafaktura-validated"]) || $_SESSION["proformafaktura-validated"] !== true) {
                $this->showValidationForm($url);
                return;
            }
        }

        // If action is search
        if(isset($_POST["action"]) && $_POST["action"] == "search") {
            $this->search();
            return;
        }

        // If action is sethandled
        if(isset($_POST["action"]) && $_POST["action"] == "sethandled") {

            $key = trimgf($_POST["key"] ?? "");
            $checked = $_POST["checked"] == 1;

            if($key == "") return;

            $existing = \LookupData::find_by_sql("SELECT *  FROM lookupdata where handle = '".addslashes($key)."'");
            if(count($existing) == 0) {
                $lookupData = new \LookupData();
                $lookupData->handle = $key;
                $lookupData->data = $checked ? "1" : "0";
                $lookupData->save();
                echo "created";

            } else {
                $existing = \LookupData::find($existing[0]->id);
                $existing->data = $checked ? "1" : "0";
                $existing->save();
                echo "updated";

            }

            \system::connection()->commit();

            return;
        }

        // If action is download
        if(isset($_POST["action"]) && $_POST["action"] == "download") {
            $orderid = isset($_POST["shopOrderNumber"]) ? $_POST["shopOrderNumber"] : "";
            $ordersplit = explode(":",$orderid);

            if(count($ordersplit) != 2) {
                echo "Ukendt ordreformat"; return;
            } else if($ordersplit[0] == "s") {
                $shipmentid = intval($ordersplit[1]);
                $this->downloadProformaShipment($shipmentid);
                return;
            } else if($ordersplit[0] == "o") {
                $orderid = intval($ordersplit[1]);
                $this->downloadProformaOrder($orderid);
            } else {
                echo "Ukendt ordreformat"; return;
            }


            return;
        }



        // Show proforma form
        $this->showProformaForm($url);
    }

    /************** DOWNLOAD PROFORMA **************/

    private function downloadProformaShipment($shipmentid)
    {

    $shipment = \Shipment::find(intvalgf($shipmentid));
    $order = \CompanyOrder::find($shipment->companyorder_id);
    $company = \Company::find($order->company_id);
    $shop = \Shop::find($order->shop_id);

    // Get leverancenr
    $forsendelsesnr = isset($_POST["forsendelsesnr"]) ? $_POST["forsendelsesnr"] : "udenforsnr";
    $soNr = $order->order_no;

    $filename = trim($soNr." ".$forsendelsesnr).".pdf";

    $urlPath = "https://system.gavefabrikken.dk/gavefabrikken_backend/units/valgshop/reports/gflogo.jpg";

    // Varenr list
    $varenrlist = [];

    if($shipment->quantity > 0 && trim($shipment->itemno) != "") {
        $varenrlist[] = array("quantity" => $shipment->quantity,"itemno" => $shipment->itemno);
    }
    if($shipment->quantity2 > 0 && trim($shipment->itemno2) != "") {
        $varenrlist[] = array("quantity" => $shipment->quantity2,"itemno" => $shipment->itemno2);
    }
    if($shipment->quantity3 > 0 && trim($shipment->itemno3) != "") {
        $varenrlist[] = array("quantity" => $shipment->quantity3,"itemno" => $shipment->itemno3);
    }
    if($shipment->quantity4 > 0 && trim($shipment->itemno4) != "") {
        $varenrlist[] = array("quantity" => $shipment->quantity4,"itemno" => $shipment->itemno4);
    }
    if($shipment->quantity5 > 0 && trim($shipment->itemno5) != "") {
        $varenrlist[] = array("quantity" => $shipment->quantity5,"itemno" => $shipment->itemno5);
    }


    // Generate html
    ob_start();

    ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Proforma Invoice</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 10px; }
            .header { text-align: right; margin-bottom: 20px; }
            .address { width: 100%; margin-bottom: 20px; }
            .address td { vertical-align: top; padding: 5px; }
            .bold { font-weight: bold; }
            .delivery-box { width: 80%; border: 1px solid #000; padding: 0px; }
            .delivery-box td { padding: 4px; }
            .delivery-box td:first-child { background: #F0F0F0;  }
            .invoice-details, .items { width: 100%; margin-bottom: 20px; }
            .items { border-collapse: collapse; }
            .items th { text-align: left; border-bottom: 1px solid #000; padding: 5px; }
            .items td { padding: 5px; text-align: left;}
            .right { text-align: right !important; }
        </style>
    </head>
    <body>

    <div class="header">
        <img src="<?php echo $urlPath ?>" alt="Gavefabrikken Logo" style="height: 80px;">
    </div>

    <table class="address">
        <tr>
            <td class="bold" style="width: 50%; font-size: 1.2em;">Invoice Address</td>
            <td class="bold" style="width: 50%; font-size: 1.2em;">Delivery Address</td>
        </tr>
        <tr>
            <td>
                GaveFabrikken - Norge / IC<br>
                Enebakkveien 117<br>
                <br>
                0680 Oslo<br>
                <br>
                Norge

                <?php

                $cvr = $company->cvr;

                if($cvr != "") {
                    echo " <br><br><br>VAT: ".$cvr;
                }

                if($soNr != "") {
                    echo "<br><br>".$soNr;
                }

                $countryName = $shipment->shipto_country;
                if($countryName == 1) $countryName = "Danmark";
                if($countryName == 4) $countryName = "Norge";
                if($countryName == 5) $countryName = "Sverige";


                ?>

            </td>
            <td>
                <table class="delivery-box" cellpadding="0" cellspacing="0">
                    <tr><td style="background: #F0F0F0;">Name1:</td><td><?php echo $shipment->shipto_name; ?></td></tr>
                    <tr><td style="background: #F0F0F0;">Address1:</td><td><?php echo $shipment->shipto_address; ?></td></tr>
                    <tr><td style="background: #F0F0F0;">Address2:</td><td><?php echo $shipment->shipto_address2; ?></td></tr>
                    <tr><td style="background: #F0F0F0;">Region:</td><td></td></tr>
                    <tr><td style="background: #F0F0F0;">County:</td><td><?php echo $countryName; ?></td></tr>
                    <tr><td style="background: #F0F0F0;">District:</td><td></td></tr>
                    <tr><td style="background: #F0F0F0;">Province:</td><td></td></tr>
                    <tr><td style="background: #F0F0F0;">Place:</td><td><?php echo $shipment->shipto_city; ?> <?php echo $shipment->shipto_postcode; ?></td></tr>
                    <tr><td style="background: #F0F0F0;">Phone:</td><td><?php echo $shipment->shipto_phone; ?></td></tr>
                </table><br>

            </td>
        </tr>
    </table>


    <div style="width: 40%; float: right;">
        <table class="invoice-details">
            <tr>
                <td colspan="2" class="bold">Invoice</td>

            </tr>
            <tr>
                <td  colspan="2">PINV0003 - For Customs purpose only<br><br><br><br></td>
            </tr>
            <tr>
                <td>Date</td>
                <td class="right"><?php echo date("d-m-Y"); ?></td>
            </tr>
            <tr>
                <td>Customer nr.</td>
                <td class="right"></td>
            </tr>
            <tr>
                <td>Leveringskode</td>
                <td class="right">Delivery Duty Paid</td>
            </tr>
        </table>
    </div><br>

    <div style="clear: both;">
        <table class="items" style="text-align: left;">
            <tr>
                <th>Nummer</th>
                <th>Item Description</th>
                <th class="right" style="text-align: right;">Qty</th>
                <th class="right" style="text-align: right;">Price</th>
                <th class="right" style="text-align: right;">Amount</th>
            </tr>

            <?php

            $netWeightTotal = 0;
            $grossWeightTotal = 0;
            $totalUnitCost = 0;

            foreach($varenrlist as $vare) {


                $navisionItemList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` in (1,5) AND `no` LIKE '".$vare["itemno"]."' AND `deleted` IS NULL order by language_id ASC, id DESC");

                if(count($navisionItemList) > 0) {

                    $item = $navisionItemList[0];
                    $itemDesc = $item->description;
                    $weightGross = $item->gross_weight+0.4;
                    $weightNet = $item->net_weight;
                    $tariffNo = $item->tariff_no;
                    $countryOfOrigin = $item->countryoforigin;
                    $unitCost = $item->unit_cost;

                    $netWeightTotal += $weightNet;
                    $grossWeightTotal += $weightGross+0.4;
                    $totalUnitCost += $unitCost*$vare["quantity"];

                } else {
                    if($vare["itemno"] == "") {
                        $vare["itemno"] = "IKKE-VALGT";
                    }
                    $itemDesc = "Not in nav:";
                    $weightGross = "";
                    $weightNet = "";
                    $tariffNo = "";
                    $countryOfOrigin = "";
                    $unitCost = 0;
                }

                ?><tr>
                <td><?php echo $vare["itemno"]; ?></td>
                <td><?php echo $itemDesc; ?><br>Oprindelsesland: <?php echo $countryOfOrigin; ?> Varekode <?php echo $tariffNo; ?></td>
                <td class="right" style="text-align: right;"><?php echo $vare["quantity"]; ?></td>
                <td class="right" style="text-align: right;"><?php echo number_format($unitCost,2,",","."); ?></td>
                <td class="right" style="text-align: right;"><?php echo number_format($unitCost*$vare["quantity"],2,",","."); ?></td>
                </tr><?php


            }

            ?>



        </table>
    </div>

    <?php if(intvalgf($netWeightTotal) != 0 && intvalgf($grossWeightTotal) != 0 ) { ?>
        <div style="padding: 15px; text-align: center;">
            Netto vægt: <?php echo number_format($netWeightTotal,2,",","."); ?> kg - Brutto vægt: <?php echo number_format($grossWeightTotal,2,",","."); ?> kg
        </div>
    <?php } ?>

    <table class="invoice-details">
        <tr>
            <td class="right bold">Netto</td>
            <td class="right"><?php echo number_format($totalUnitCost,2,",","."); ?></td>
        </tr>
        <tr>
            <td class="right bold">Total DKK</td>
            <td class="right"><?php echo number_format($totalUnitCost,2,",","."); ?></td>
        </tr>
    </table>
    <br>
    <div class="footer" style="text-align: center;">
        <p>Tlf.: +45 70 70 20 27 - Mail: info@gavefabrikken.dk - Web: www.gavefabrikken.dk</p>
        <p>Carl Jacobsens Vej 16, 2500 Valby, DK</p>
        <p>IBAN: DK6380750001744790 - SWIFT: SYBKDK22</p>
    </div>

    </body>
    </html>


    <?php

    $content = ob_get_contents();
    ob_end_clean();

    //echo $content; exit();


    // Generate pdf
    $mpdf = new \Mpdf\Mpdf();
    //$mpdf->setFooter("Side {PAGENO} / {nb}");
    $mpdf->WriteHTML(($content));
    $mpdf->Output($filename,"D");
    }

    private function downloadProformaOrder($orderid) {

        // Print post vars
//        print_r($_POST);



        $order = \Order::find(intvalgf($orderid));
        $shop = \Shop::find($order->shop_id);
        $addressInfo = $this->getOrderAddressLines($orderid,$shop->localisation);
        $shopMetadata = \ShopMetadata::find_by_shop_id($order->shop_id);

        
        // Get leverancenr
        $forsendelsesnr = isset($_POST["forsendelsesnr"]) ? $_POST["forsendelsesnr"] : "udenforsnr";
        $soNr = "udenso";

        $soNr = $shop->company[0]->so_no;

        $filename = trim($soNr." ".$forsendelsesnr).".pdf";

        $urlPath = "https://system.gavefabrikken.dk/gavefabrikken_backend/units/valgshop/reports/gflogo.jpg";


        $presentModel = \PresentModel::find_by_sql("SELECT * FROM present_model where language_id = 1 and model_id = ".$order->present_model_id);
        $varenr = isset($presentModel[0]) ? $presentModel[0]->model_present_no : "";
        $navisionItemList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$varenr."' AND `deleted` IS NULL");

        if(count($navisionItemList) > 0) {

            $item = $navisionItemList[0];
            $itemDesc = $item->description;
            $weightGross = $item->gross_weight+0.4;
            $weightNet = $item->net_weight;
            $tariffNo = $item->tariff_no;
            $countryOfOrigin = $item->countryoforigin;
            $unitCost = $item->unit_cost;

        } else {
            if($varenr == "") {
                $varenr = "IKKE-VALGT";
            }
            $itemDesc = "Not in nav:";
            $weightGross = "";
            $weightNet = "";
            $tariffNo = "";
            $countryOfOrigin = "";
            $unitCost = 0;
        }




// Generate html
        ob_start();

        ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proforma Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: right; margin-bottom: 20px; }
        .address { width: 100%; margin-bottom: 20px; }
        .address td { vertical-align: top; padding: 5px; }
        .bold { font-weight: bold; }
        .delivery-box { width: 80%; border: 1px solid #000; padding: 0px; }
        .delivery-box td { padding: 4px; }
        .delivery-box td:first-child { background: #F0F0F0;  }
        .invoice-details, .items { width: 100%; margin-bottom: 20px; }
        .items { border-collapse: collapse; }
        .items th { text-align: left; border-bottom: 1px solid #000; padding: 5px; }
        .items td { padding: 5px; text-align: left;}
        .right { text-align: right !important; }
    </style>
</head>
<body>

<div class="header">
    <img src="<?php echo $urlPath ?>" alt="Gavefabrikken Logo" style="height: 80px;">
</div>

<table class="address">
    <tr>
        <td class="bold" style="width: 50%; font-size: 1.2em;">Invoice Address</td>
        <td class="bold" style="width: 50%; font-size: 1.2em;">Delivery Address</td>
    </tr>
    <tr>
        <td>
            GaveFabrikken - Norge / IC<br>
            Enebakkveien 117<br>
            <br>
            0680 Oslo<br>
            <br>
            Norge

            <?php

                $cvr = "";

                try {
                    $company = \Company::find($order->company_id);
                    $cvr = $company->cvr;
                }
                catch (\Exception $e) {

                }

                if($cvr != "") {
                    echo " <br><br><br>VAT: ".$cvr;
                }

            if($soNr != "") {
                echo "<br><br>".$soNr;
            }
            ?>

        </td>
        <td>
            <table class="delivery-box" cellpadding="0" cellspacing="0">
                <tr><td style="background: #F0F0F0;">Name1:</td><td><?php echo $order->user_name; ?></td></tr>
                <tr><td style="background: #F0F0F0;">Address1:</td><td><?php echo $addressInfo["address"]; ?></td></tr>
                <tr><td style="background: #F0F0F0;">Address2:</td><td></td></tr>
                <tr><td style="background: #F0F0F0;">Region:</td><td></td></tr>
                <tr><td style="background: #F0F0F0;">County:</td><td><?php echo $addressInfo["country"]; ?></td></tr>
                <tr><td style="background: #F0F0F0;">District:</td><td></td></tr>
                <tr><td style="background: #F0F0F0;">Province:</td><td></td></tr>
                <tr><td style="background: #F0F0F0;">Place:</td><td><?php echo $addressInfo["zip"]; ?> <?php echo $addressInfo["city"]; ?></td></tr>
                <tr><td style="background: #F0F0F0;">Phone:</td><td><?php echo $addressInfo["phone"]; ?></td></tr>
            </table><br>

        </td>
    </tr>
</table>


<div style="width: 40%; float: right;">
<table class="invoice-details">
    <tr>
        <td colspan="2" class="bold">Invoice</td>

    </tr>
    <tr>
        <td  colspan="2">PINV0003 - For Customs purpose only<br><br><br><br></td>
    </tr>
    <tr>
        <td>Date</td>
        <td class="right"><?php echo date("d-m-Y"); ?></td>
    </tr>
    <tr>
        <td>Customer nr.</td>
        <td class="right"></td>
    </tr>
    <tr>
        <td>Leveringskode</td>
        <td class="right">Delivery Duty Paid</td>
    </tr>
</table>
</div><br>

<div style="clear: both;">
<table class="items" style="text-align: left;">
    <tr>
        <th>Nummer</th>
        <th>Item Description</th>
        <th class="right" style="text-align: right;">Qty</th>
        <th class="right" style="text-align: right;">Price</th>
        <th class="right" style="text-align: right;">Amount</th>
    </tr>
    <tr>
        <td><?php echo $varenr; ?></td>
        <td><?php echo $itemDesc; ?><br>Oprindelsesland: <?php echo $countryOfOrigin; ?> Varekode <?php echo $tariffNo; ?></td>
        <td class="right" style="text-align: right;">1</td>
        <td class="right" style="text-align: right;"><?php echo number_format($unitCost,2,",","."); ?></td>
        <td class="right" style="text-align: right;"><?php echo number_format($unitCost,2,",","."); ?></td>
    </tr>

</table>
</div>

<?php if(trimgf($weightNet) != "" && trimgf($weightGross) != "") { ?>
<div style="padding: 15px; text-align: center;">
    Netto vægt: <?php echo number_format($weightNet,2,",","."); ?> kg - Brutto vægt: <?php echo number_format($weightGross,2,",","."); ?> kg
</div>
<?php } ?>

<table class="invoice-details">
    <tr>
        <td class="right bold">Netto</td>
        <td class="right"><?php echo number_format($unitCost,2,",","."); ?></td>
    </tr>
    <tr>
        <td class="right bold">Total DKK</td>
        <td class="right"><?php echo number_format($unitCost,2,",","."); ?></td>
    </tr>
</table>
<br>
<div class="footer" style="text-align: center;">
    <p>Tlf.: +45 70 70 20 27 - Mail: info@gavefabrikken.dk - Web: www.gavefabrikken.dk</p>
    <p>Carl Jacobsens Vej 16, 2500 Valby, DK</p>
    <p>IBAN: DK6380750001744790 - SWIFT: SYBKDK22</p>
</div>

</body>
</html>


<?php

    $content = ob_get_contents();
    ob_end_clean();

    //echo $content; exit();


        // Generate pdf
         $mpdf = new \Mpdf\Mpdf();
        //$mpdf->setFooter("Side {PAGENO} / {nb}");
        $mpdf->WriteHTML(($content));
        $mpdf->Output($filename,"D");

    }

    /************************ SEARCH FUNCTION *********************/

private function search()
{
    // Udtræk POST-værdier
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $sonr = isset($_POST['sonr']) ? trim($_POST['sonr']) : '';

    $useLimit = true;
    if($sonr != "") $useLimit = false;

    // Tjek om alle søgefelter er tomme
    if (empty($name) && empty($address) && empty($phone) && empty($email)  && empty($sonr)) {
        echo "<tr><td colspan='10' style='padding: 20px; text-align: center; color: red;'>Indtast venligst mindst ét søgekriterie.</td></tr>";
        return;
    }

    // Forbind til database og udfør SQL-kald (eksempel - tilpas efter behov)
    // $results = $this->database->query("SELECT * FROM your_table WHERE name LIKE ? AND address LIKE ? AND phone LIKE ? AND email LIKE ?", ["%$name%", "%$address%", "%$phone%", "%$email%"]);

    $orderFilters = "";
    $shipmentFilters = "";
    $filterCount = 0;
    $showMany = false;

    // Search name
    if(trim($name) != "") {
        $filterCount++;
        $orderFilters .= " && o.user_name LIKE '%".addslashes($name)."%' ";
        $shipmentFilters .= " && (s.shipto_name LIKE '%".addslashes($name)."%' OR s.shipto_contact) ";
    }

    // Search email
    if(trim($email) != "") {
        $filterCount++;
        $orderFilters .= " && o.user_email LIKE '%".addslashes($email)."%' ";
        $shipmentFilters .= " && s.shipto_email LIKE '%".addslashes($email)."%' ";
    }

    // Search phone
    if(trim($phone) != "") {
        $filterCount++;
        $orderFilters .= " && o.id in (SELECT order_id FROM `order_attribute` where is_username = 0 and is_password = 0 and is_name = 0 and is_email = 0 and attribute_value like '%".addslashes($phone)."%')";
        $shipmentFilters .= " && s.shipto_phone LIKE '%".addslashes($phone)."%' ";
    }

    // Search address
    if(trim($address) != "") {
        $filterCount++;
        $orderFilters .= " && o.id in (SELECT order_id FROM `order_attribute` where is_username = 0 and is_password = 0 and is_name = 0 and is_email = 0 and attribute_value like '%".addslashes($address)."%')";
        $shipmentFilters .= " && (s.shipto_address LIKE '%".addslashes($address)."%' OR s.shipto_address2 LIKE '%".addslashes($address)."%' OR s.shipto_city LIKE '%".addslashes($address)."%' OR s.shipto_postcode LIKE '%".addslashes($address)."%') ";
    }


    // Search sonr
    if(trim($sonr) != "") {
        $filterCount++;
        $orderFilters .= " && sm.so_no LIKE '%".addslashes($sonr)."%' ";
        $shipmentFilters .= " && s.id < 0 ";
        if($filterCount == 1) $showMany = true;
    }

    $sql = "SELECT o.id, o.user_name, s.localisation, o.user_email, o.present_model_name, s.name, sm.so_no, o.shop_id FROM `order` o, shop s, shop_metadata sm where s.is_company = 1 and sm.shop_id = s.id and o.shop_id = s.id ".$orderFilters." ".($useLimit ? "LIMIT 100" : "");
    $orderlist = \Order::find_by_sql($sql);


    $shipmentSql = "select * from shipment s where s.shipment_type in ('directdelivery','earlyorder') and s.shipment_state not in (0,1,4) ".$shipmentFilters." LIMIT 100";

    $shipmentList = \Shipment::find_by_sql($shipmentSql);


    if(count($orderlist) == 0 && count($shipmentList) == 0) {
        echo "<tr><td colspan='11' style='padding: 20px; text-align: center;'>Ingen resultater fundet..</td></tr>";
        return;
    } else if((count($orderlist) >= 100 || count($shipmentList) >= 100) && !$showMany) {
        echo "<tr><td colspan='11' style='padding: 20px; text-align: center;'>Der er fundet mere end 100 resultater. Gør søgningen mere specifik.</td></tr>";
        return;
    }



    $handledMap = [];
    $handledKeys = array();


    foreach($orderlist as $order) {
        $handledKeys[] = "proformahandled-o-".$order->id;
    }

    foreach($shipmentList as $shipment) {
        $handledKeys[] = "proformahandled-s-".$shipment->id;
    }


    if(count($handledKeys) > 0) {
        $handledResults = \LookupData::find_by_sql("SELECT * FROM lookupdata where data = '1' and handle in ('" . implode("','", $handledKeys) . "')");
        foreach($handledResults as $handled) {
            $handledMap[$handled->handle] = true;
        }
    }



    foreach($orderlist as $order) {

        $adressInfo = $this->getOrderAddressLines($order->id,$order->localisation);


        $shop = \Shop::find($order->shop_id);
        $soNr = $shop->company[0]->so_no;

        $key = "proformahandled-o-".$order->id;
        $isHandled = isset($handledMap[$key]);

        echo "<tr style='font-size: 0.8em;'>
            <td><input type='radio' name='selected' value='o:".$order->id."'></td>
            <td><input type='checkbox' class='handledcheck' data-key='".$key."' ".($isHandled ? "checked" : "")."></td>
            <td>".$order->name."</td>
            <td>".$order->user_name."</td>
            <td>".$adressInfo["address"]."</td>
            <td>".$adressInfo["zip"]." ".$adressInfo["city"]."</td>
            <td>".$adressInfo["country"]."</td>
            <td>".$order->user_email."</td>
            <td>".$adressInfo["phone"]."</td>
            <td>".$order->present_model_name."</td>
            <td>".$order->so_no."</td>
            
          </tr>";

    }

    foreach($shipmentList as $shipment) {

        $order = \CompanyOrder::find($shipment->companyorder_id);
        $shop = \Shop::find($order->shop_id);
        $soNr = $order->order_no;

        $presentList = [];
        if($shipment->quantity > 0 && trim($shipment->itemno) != "") {
            $presentList[] = $shipment->quantity." x ".$shipment->itemno;
        }

        if($shipment->quantity2 > 0 && trim($shipment->itemno2) != "") {
            $presentList[] = $shipment->quantity2." x ".$shipment->itemno2;
        }

        if($shipment->quantity3 > 0 && trim($shipment->itemno3) != "") {
            $presentList[] = $shipment->quantity3." x ".$shipment->itemno3;
        }

        if($shipment->quantity4 > 0 && trim($shipment->itemno4) != "") {
            $presentList[] = $shipment->quantity4." x ".$shipment->itemno4;
        }

        if($shipment->quantity5 > 0 && trim($shipment->itemno5) != "") {
            $presentList[] = $shipment->quantity5." x ".$shipment->itemno5;
        }

        $key = "proformahandled-s-".$shipment->id;
        $isHandled = isset($handledMap[$key]);

        echo "<tr style='font-size: 0.8em;'>
            <td><input type='radio' name='selected' value='s:".$shipment->id."'></td>
            <td><input type='checkbox' class='handledcheck' data-key='".$key."' ".($isHandled ? "checked" : "")."></td>
            <td>".$shop->name." (".$shipment->shipment_type.")</td>
            <td>".$shipment->shipto_name."</td>
            <td>".$shipment->shipto_address."</td>
            <td>".$shipment->shipto_city." ".$shipment->shipto_postcode."</td>
            <td>".$shipment->shipto_country."</td>
            <td>".$shipment->shipto_email."</td>
            <td>".$shipment->shipto_phone."</td>
            <td>".implode("<br>",$presentList)."</td>
            <td>".$order->order_no."</td>
            
          </tr>";

    }

}

private function getOrderAddressLines($orderid,$localisation=0) {

    $attributeList = \OrderAttribute::find_by_sql("SELECT * FROM order_attribute WHERE order_id = ".$orderid." AND is_username = 0 AND is_password = 0 AND is_name = 0 AND is_email = 0");

    $adressInfo = array(
        "address" => "",
        "zip" => "",
        "city" => "",
        "country" => "",
        "phone" => ""
    );

    foreach($attributeList as $attribute) {

        $attributeName = strtolower($attribute->attribute_name);
        $value = $attribute->attribute_value;

        if (preg_match('/adresse|address|gatuadress|leveringsadresse|leveringsadresse1|adresse 1|adresse 2|gade|gateadresse|gadenavn/i', $attributeName)) {
            $adressInfo["address"] .= $value . " ";
        } elseif (preg_match('/postnummer|postnr|post nr|zip|post number|poststed|post nr|poststed/i', $attributeName)) {
            $adressInfo["zip"] = $value;
        } elseif (preg_match('/by|city|ort|poststed/i', $attributeName)) {
            $adressInfo["city"] = $value;
        } elseif (preg_match('/land|country|firmaland|firma land/i', $attributeName)) {
            $adressInfo["country"] = $value;
        } elseif (preg_match('/telefonnummer|telefon|mobilnummer|mobil|telefonnr|telefonnummer \(privatlevering\)|mobil nr|mobilnummer|mobilnummer/i', $attributeName)) {
            $adressInfo["phone"] .= $value . " ";
        }
    }

    if($adressInfo["country"] == "") {
        if($localisation == 1) $adressInfo["country"] = "Danmark";
        if($localisation == 4) $adressInfo["country"] = "Norge";
        if($localisation == 5) $adressInfo["country"] = "Sverige";
    }

    return $adressInfo;

}



    /******************* PROFORMA FORM *******************/

    private function showProformaForm($url)
    {


        ?><!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valgshop proformafaktra - privatlevering</title>
    <link href="units/tools/wizard/assets/bootstrap.min.css" rel="stylesheet">
    <script src="units/tools/wizard/assets/jquery.min.js"></script>
    <script src="units/tools/wizard/assets/popper.min.js"></script>
    <script src="units/tools/wizard/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="units/tools/wizard/assets/fontawesome.css">
    <style>

    </style>
</head>
<body>

<div class="container mt-5" style="max-width: 1600px !important;">
    <h1 class="mb-4">Valgshop proformafaktra - privatlevering</h1>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2>Søg</h2>

        </div>
        <div class="card-body">
            <form>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="name">Navn</label>
                        <input type="text" class="form-control" id="name" placeholder="Indtast navn">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="address">Adresse</label>
                        <input type="text" class="form-control" id="address" placeholder="Indtast adresse">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="phone">Telefon</label>
                        <input type="text" class="form-control" id="phone" placeholder="Indtast telefon">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" placeholder="Indtast e-mail">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sonr">SO nr</label>
                        <input type="sonr" class="form-control" id="sonr" placeholder="Indtast so nr.">
                    </div>
                    <div class="form-group col-md-1">
                        <label for="search"> &nbsp; </label><br>
                        <button type="button" class="btn btn-primary">Søg</button>
                    </div>
                </div>
            </form>

        </div>

    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2>Søgeresultater</h2>
        </div>
        <div class="card-body" style="height: 500px; overflow-y: auto; padding: 0px !important;">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th></th>
                    <th>!</th>
                    <th>Shop</th>
                    <th>Navn</th>
                    <th>Adresse</th>
                    <th>Postnr og by</th>
                    <th>Land</th>
                    <th>E-mail</th>
                    <th>Telefon</th>
                    <th>Gavevalg</th>
                    <th>SO nr.</th>

                </tr>
                </thead>
                <tbody>
                <!-- Her kan søgeresultaterne indsættes -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-group">
            <label for="forsendelsesnr">Forsendelsesnr.</label>
            <input type="text" class="form-control" id="forsendelsesnr" placeholder="Indtast forsendelsesnr.">
        </div>
        <div>
            <button type="button" class="btn btn-secondary" onclick="document.location='<?php echo $url; ?>'">Nulstil</button>
            <button type="button" class="btn btn-success" onClick="downloadProforma()">Hent proforma faktura</button>
        </div>
    </div>
</div>
<script>
    // Funktion til at lave en søgning
    function search() {
        const url = '<?php echo $url; ?>';  // Erstat med den rigtige URL
        const name = document.getElementById('name').value;
        const address = document.getElementById('address').value;
        const phone = document.getElementById('phone').value;
        const email = document.getElementById('email').value;
        const sonr = document.getElementById('sonr').value;

        const tableBody = document.querySelector('table tbody');
        tableBody.innerHTML = '<tr><td colspan="11" class="text-center">Søger...</td></tr>';

        $.post(url, { name:name, address:address, phone: phone, email: email, sonr, sonr, action: 'search' }, function(data) {
            tableBody.innerHTML = data;
            updateDownloadButtonState();
        }).fail(function() {
            tableBody.innerHTML = '<tr><td colspan="11" class="text-center">Fejl i søgning</td></tr>';
        });
    }

    // Funktion til at downloade proforma faktura
    function downloadProforma() {
        const url = '<?php echo $url; ?>';  // Erstat med den rigtige URL
        const forsendelsesnr = document.getElementById('forsendelsesnr').value;
        const selectedRadio = document.querySelector('input[name="selected"]:checked');

        if (!selectedRadio) {
            alert('Vælg venligst en række i søgeresultaterne.');
            return;
        }

        const shopOrderNumber = selectedRadio.value;  // Forvent at radioens værdi er SO nr.

        $('<form>', {
            "method": "post",
            "action": url,
            "html": `<input type="hidden" name="forsendelsesnr" value="${forsendelsesnr}">
                     <input type="hidden" name="shopOrderNumber" value="${shopOrderNumber}">
                     <input type="hidden" name="action" value="download">`
        }).appendTo(document.body).submit();
    }

    // Opdaterer tilstanden af download-knappen
    function updateDownloadButtonState() {
        const downloadButton = document.querySelector('.btn-success');
        const selectedRadio = document.querySelector('input[name="selected"]:checked');
        const shipmentNumberInput = document.querySelector('#forsendelsesnr');

        <?php if(($_GET["hide"] ?? "") == "shipno") {
            ?>downloadButton.disabled = !selectedRadio;<?php
        } else {
            ?>downloadButton.disabled = !selectedRadio || !shipmentNumberInput || shipmentNumberInput.value.trim() === '';<?php
        }?>


    }



    // Event listeners til knapperne
    document.querySelector('.btn-primary').addEventListener('click', search);
    document.querySelector('.btn-success').disabled = true;  // Deaktiver download-knappen som standard

    const shipmentNumberInput = document.querySelector('#forsendelsesnr');
    shipmentNumberInput.addEventListener('input', updateDownloadButtonState);

    // Lyt efter ændringer i radio-knapper
    document.querySelector('table tbody').addEventListener('change', function(event) {
        if (event.target.name === 'selected') {
            updateDownloadButtonState();
        }
    });

    // Lyt efter enter-tast i søgefelterne
    document.querySelectorAll('input[type="text"], input[type="email"]').forEach(input => {
        input.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();  // Forhindre formularens standard submit
                search();
            }
        });
    });


    document.addEventListener('change', function(event) {
        if (event.target.classList.contains('handledcheck')) {
            const dataKey = event.target.getAttribute('data-key');
            callWebService(dataKey, event.target.checked);
        }
    });

    function callWebService(dataKey, isChecked) {

        const formData = new URLSearchParams();
        formData.append('key', dataKey);
        formData.append('checked', isChecked ? 1 : 0);
        formData.append('action', 'sethandled');

        fetch('<?php echo $url; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }



</script>




</body>
</html>
<?php
    }






    /******** VALIDATION ********/

    private function showValidationForm($url,$error="")
    {
        echo '<style>
            .form-container {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f7f7f7;
            }
            .form-box {
                padding: 20px;
                border: 1px solid #ccc;
                background-color: white;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
            }
            .form-box label {
                display: block;
                margin-bottom: 8px;
            }
            .form-box input[type="password"] {
                width: 100%;
                padding: 8px;
                margin-bottom: 12px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .form-box button {
                width: 100%;
                padding: 10px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .form-box button:hover {
                background-color: #0056b3;
            }
        </style>';

        echo '<div class="form-container">';
        echo '<div class="form-box">';
        echo '<form method="post" action="' . htmlspecialchars($url) . '">';

        if($error != "") {
            echo '<p style="color:red;">' . $error . '</p>';
        }

        echo '<label for="password">Angiv adgangskode til værktøj:</label>';
        echo '<input type="password" id="password" name="password" required>';
        echo '<button type="submit">Godkend</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
    }

    public function validatePassword($url)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            if ($_POST['password'] === self::VALIDATE_PASSWORD) {
                $_SESSION["proformafaktura-validated"] = true;
                return true;
            } else {
                $this->showValidationForm($url,"Invalid password try again");
                return false;
            }
        }
    }
}