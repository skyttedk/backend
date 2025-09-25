<?php
// Aktivér error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function findNearestParcelShop($street, $zipcode, $country = 'DK', $amount = 5) {


    // SOAP request XML
    $xml = '<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <SearchNearestParcelShops xmlns="http://gls.dk/webservices/">
            <street>' . htmlspecialchars($street) . '</street>
            <zipcode>' . htmlspecialchars($zipcode) . '</zipcode>
            <countryIso3166A2>' . htmlspecialchars($country) . '</countryIso3166A2>
            <Amount>' . htmlspecialchars($amount) . '</Amount>
        </SearchNearestParcelShops>
    </soap:Body>
</soap:Envelope>';

    // Debug: Vis den genererede XML
  //  echo "Generated XML Request:<br>";
  //  echo "<pre>" . htmlspecialchars($xml) . "</pre><br>";

    $ch = curl_init('https://www.gls.dk/webservices_v3/wsShopFinder.asmx');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://gls.dk/webservices/SearchNearestParcelShops"',
            'Content-Length: ' . strlen($xml)
        ]
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    // Vis raw response

  //  echo "<pre>" . htmlspecialchars($response) . "</pre><br>";

    return $response;
}

// HTML form
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GLS Pakkeshop Finder</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <form method="post">
        <input type="text" name="street" placeholder="Gade" value="' . (isset($_POST['street']) ? htmlspecialchars($_POST['street']) : '') . '">
        <input type="text" name="zipcode" placeholder="Postnummer" value="' . (isset($_POST['zipcode']) ? htmlspecialchars($_POST['zipcode']) : '') . '">
        <input type="submit" value="Søg">
    </form>
    <hr>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['street']) && isset($_POST['zipcode'])) {
    try {
        $result = findNearestParcelShop($_POST['street'], $_POST['zipcode']);




        $xml = new SimpleXMLElement($result);

// Registrer namespaces
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns', 'http://gls.dk/webservices/');

// Find PakkeshopData med korrekt namespace-sti
        $shopData = $xml->xpath('//ns:PakkeshopData');

        if (!empty($shopData)) {
            echo '<table border="1" style="border-collapse: collapse; width: 100%; margin: 20px 0;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 10px;">Butik</th>
                <th style="padding: 10px;">Adresse</th>
                <th style="padding: 10px;">Postnr & By</th>
                <th style="padding: 10px;">Telefon</th>
                <th style="padding: 10px;">Afstand</th>
                <th style="padding: 10px;">Åbningstider</th>
            </tr>
        </thead>
        <tbody>';

            foreach($shopData as $shop) {
                echo '<tr>';

                // Butiksnavn og nummer
                echo '<td style="padding: 10px;">
                <strong>' . $shop->CompanyName . '</strong><br>
                <small>Shop nr: ' . $shop->Number . '</small>
              </td>';

                // Adresse
                echo '<td style="padding: 10px;">' . $shop->Streetname . '</td>';

                // Postnr & By
                echo '<td style="padding: 10px;">' . $shop->ZipCode . ' ' . $shop->CityName . '</td>';

                // Telefon
                echo '<td style="padding: 10px;">' . ($shop->Telephone != '-' ? $shop->Telephone : 'Ikke angivet') . '</td>';

                // Afstand
                echo '<td style="padding: 10px;">' .
                    number_format((float)$shop->DistanceMetersAsTheCrowFlies/1000, 1, ',', '.') . ' km</td>';

                // Åbningstider
                echo '<td style="padding: 10px;">';
                foreach($shop->OpeningHours->Weekday as $weekday) {
                    echo '<strong>' . $weekday->day . ':</strong> ' .
                        $weekday->openAt->From . ' - ' .
                        $weekday->openAt->To . '<br>';
                }
                echo '</td>';

                echo '</tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>Ingen pakkeshops fundet i responset.</p>';
        }

    } catch (Exception $e) {
        echo "Der opstod en fejl: " . $e->getMessage();
    }
}

echo '</body></html>';
?>