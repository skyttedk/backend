<?php

namespace GFUnit\valgshop\reports;
use GFBiz\units\UnitController;
use GFBiz\valgshop\ValgshopFordeling;
use GFCommon\Model\Navision\SalesHeaderWS;

class ProformaPrivatLevering
{
    private const VALIDATE_PASSWORD = "vsdfvVDFgfwfFDcvdRgsqq";

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

        // If action is download
        if(isset($_POST["action"]) && $_POST["action"] == "download") {
            $this->downloadProforma();
            return;
        }



        // Show proforma form
        $this->showProformaForm($url);
    }

    /************** DOWNLOAD PROFORMA **************/

    private function downloadProforma() {

        // Print post vars
//        print_r($_POST);

$urlPath = "https://system.gavefabrikken.dk/gavefabrikken_backend/units/valgshop/reports/gflogo.jpg";

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
            Enebakkveien<br>
            <br>
            0680 Oslo<br>
            <br>
            Norge
        </td>
        <td>
            <table class="delivery-box" cellpadding="0" cellspacing="0">
                <tr><td style="background: #F0F0F0;">Name1:</td><td>[Navn]</td></tr>
                <tr><td style="background: #F0F0F0;">Address1:</td><td>[Adresse1]</td></tr>
                <tr><td style="background: #F0F0F0;">Address2:</td><td>[Adresse2]</td></tr>
                <tr><td style="background: #F0F0F0;">Region:</td><td>[Region]</td></tr>
                <tr><td style="background: #F0F0F0;">County:</td><td>[County]</td></tr>
                <tr><td style="background: #F0F0F0;">District:</td><td>[District]</td></tr>
                <tr><td style="background: #F0F0F0;">Province:</td><td>[Province]</td></tr>
                <tr><td style="background: #F0F0F0;">Place:</td><td>5032 Bergen</td></tr>
            </table><br>
            Norge
        </td>
    </tr>
</table>


<div style="width: 40%; float: right;">
<table class="invoice-details">
    <tr>
        <td colspan="2" class="bold">Proforma Invoice</td>

    </tr>
    <tr>
        <td  colspan="2">PINV0003 - For Customs purpose only<br><br><br><br></td>
    </tr>
    <tr>
        <td>Date</td>
        <td class="right">16-12-2023</td>
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
        <td>210130</td>
        <td>Tobias Jacobsen kuffertsæt Sort<br>Oprindelsesland: CN Varekode 4202 12 99</td>
        <td class="right" style="text-align: right;">1</td>
        <td class="right" style="text-align: right;">300,15</td>
        <td class="right" style="text-align: right;">300,15</td>
    </tr>
</table>
</div>

<table class="invoice-details">
    <tr>
        <td class="right bold">Netto</td>
        <td class="right">300,15</td>
    </tr>
    <tr>
        <td class="right bold">Total DKK</td>
        <td class="right">300,15</td>
    </tr>
</table>
<br>
<div class="footer" style="text-align: center;">
    <p>Tlf.: +45 70 70 20 27 - Mail: info@gavefabrikken.dk - Web: www.gavefabrikken.dk</p>
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
        $mpdf->WriteHTML(utf8_encode($content));
        $mpdf->Output("proformatest.pdf","D");

    }

    /************************ SEARCH FUNCTION *********************/

private function search()
{
    // Udtræk POST-værdier
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Tjek om alle søgefelter er tomme
    if (empty($name) && empty($address) && empty($phone) && empty($email)) {
        echo "<tr><td colspan='10' style='padding: 20px; text-align: center; color: red;'>Indtast venligst mindst ét søgekriterie.</td></tr>";
        return;
    }

    // Forbind til database og udfør SQL-kald (eksempel - tilpas efter behov)
    // $results = $this->database->query("SELECT * FROM your_table WHERE name LIKE ? AND address LIKE ? AND phone LIKE ? AND email LIKE ?", ["%$name%", "%$address%", "%$phone%", "%$email%"]);

    $filters = "";

    // Search name
    if(trim($name) != "") {
        $filters .= " && o.user_name LIKE '%".addslashes($name)."%' ";
    }

    // Search email
    if(trim($email) != "") {
        $filters .= " && o.user_email LIKE '%".addslashes($email)."%' ";
    }

    // Search phone
    if(trim($phone) != "") {
        $filters .= " && o.id in (SELECT order_id FROM `order_attribute` where is_username = 0 and is_password = 0 and is_name = 0 and is_email = 0 and attribute_value like '%".addslashes($phone)."%')";
    }

    // Search address
    if(trim($address) != "") {
        $filters .= " && o.id in (SELECT order_id FROM `order_attribute` where is_username = 0 and is_password = 0 and is_name = 0 and is_email = 0 and attribute_value like '%".addslashes($address)."%')";
    }

    $sql = "SELECT o.id, o.user_name, s.localisation, o.user_email, o.present_model_name, s.name, sm.so_no FROM `order` o, shop s, shop_metadata sm where s.is_company = 1 and sm.shop_id = s.id and o.shop_id = s.id ".$filters." LIMIT 100";
    $orderlist = \Order::find_by_sql($sql);


        if(count($orderlist) == 0) {
            echo "<tr><td colspan='10' style='padding: 20px; text-align: center;'>Ingen resultater fundet..</td></tr>";
            return;
        } else if(count($orderlist) >= 100) {
            echo "<tr><td colspan='10' style='padding: 20px; text-align: center;'>Der er fundet mere end 100 resultater. Gør søgningen mere specifik.</td></tr>";
            return;
        }

    foreach($orderlist as $order) {



        $adressInfo = $this->getOrderAddressLines($order->id);

        if($adressInfo["country"] == "") {

            if($order->localisation == 1) $adressInfo["country"] = "Danmark";
            if($order->localisation == 4) $adressInfo["country"] = "Norge";
            if($order->localisation == 5) $adressInfo["country"] = "Sverige";


        }

        echo "<tr style='font-size: 0.8em;'>
            <td><input type='radio' name='selected' value='".$order->id."'></td>
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

}

private function getOrderAddressLines($orderid) {

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
    <title>Widget dashboard</title>
    <link href="units/tools/wizard/assets/bootstrap.min.css" rel="stylesheet">
    <script src="units/tools/wizard/assets/jquery.min.js"></script>
    <script src="units/tools/wizard/assets/popper.min.js"></script>
    <script src="units/tools/wizard/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="units/tools/wizard/assets/fontawesome.css">
    <style>

    </style>
</head>
<body>

<div class="container mt-5">
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
                    <div class="form-group col-md-3">
                        <label for="address">Adresse</label>
                        <input type="text" class="form-control" id="address" placeholder="Indtast adresse">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="phone">Telefon</label>
                        <input type="text" class="form-control" id="phone" placeholder="Indtast telefon">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" placeholder="Indtast e-mail">
                    </div>
                    <div class="form-group col-md-1">
                        <label for="email"> &nbsp; </label><br>
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
        <div class="card-body" style="height: 500px; overflow-y: auto;">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th></th>
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

        const tableBody = document.querySelector('table tbody');
        tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Søger...</td></tr>';

        $.post(url, { name:name, address:address, phone: phone, email: email, action: 'search' }, function(data) {
            tableBody.innerHTML = data;
            updateDownloadButtonState();
        }).fail(function() {
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Fejl i søgning</td></tr>';
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

        downloadButton.disabled = !selectedRadio || !shipmentNumberInput || shipmentNumberInput.value.trim() === '';
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