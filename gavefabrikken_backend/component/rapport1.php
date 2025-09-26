<?php

namespace GFUnit\rapport\rap0603;

use GFBiz\units\UnitController;

class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function run()
    {
        // Render frontend side i stedet for at køre hele processen
        $this->renderFrontend();
    }

    private function renderFrontend()
    {
        // Basisurl til API-endepunkter
        $baseUrl = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/rapport/rap0603';
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Shop Data Export</title>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <style>
                .container { max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; }
                .progress-container { margin: 20px 0; border: 1px solid #ccc; border-radius: 3px; }
                .progress-bar { height: 20px; background-color: #4CAF50; width: 0%; }
                .status { margin: 10px 0; }
                button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
                button:disabled { background-color: #cccccc; }
                .log { height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-top: 20px; }
            </style>
        </head>
        <body>
        <div class="container">
            <h1>Shop Data Export</h1>
            <p>Eksporterer shop-data til CSV fil</p>

            <button id="startProcess">Start Eksport</button>

            <div class="progress-container">
                <div class="progress-bar" id="progressBar"></div>
            </div>

            <div class="status" id="status">Klar til at begynde...</div>

            <div class="log" id="log"></div>
        </div>

        <script>
            $(document).ready(function() {
                // Basisurl til API-endepunkter
                const baseUrl = '<?php echo $baseUrl; ?>';
                let shopData = [];
                let allShops = [];
                let currentIndex = 0;
                let totalShops = 0;

                function logMessage(message) {
                    $("#log").prepend('<div>' + new Date().toLocaleTimeString() + ': ' + message + '</div>');
                }

                function updateProgress(current, total) {
                    const percent = Math.round((current / total) * 100);
                    $("#progressBar").css("width", percent + "%");
                    $("#status").text('Behandler ' + current + ' af ' + total + ' butikker (' + percent + '%)');
                }

                function processNextShop() {
                    if (currentIndex >= totalShops) {
                        // Alle butikker er processeret
                        $("#status").text('Genererer CSV fil...');
                        generateCSV(shopData);
                        return;
                    }

                    const shop = allShops[currentIndex];
                    logMessage('Henter debitornummer for butik: ' + shop.name);

                    $.ajax({
                        url: baseUrl + '/getSODebitorNO',
                        type: 'POST',
                        data: { soNo: shop.so_no },
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.success) {
                                const orderHeader = response.data;
                                const debitornr = orderHeader.Bill_to_Customer_No || '';
                                const debitorname = orderHeader.Bill_to_Name || '';

                                shopData.push({
                                    id: shop.id,
                                    nameSys: shop.name,
                                    name: debitorname,
                                    soNo: shop.so_no,
                                    debitorNo: debitornr
                                });

                                logMessage('Behandlet butik #' + shop.id + ': ' + shop.name);
                            } else {
                                logMessage('Fejl ved hentning af data for butik: ' + shop.name + ' - fortsætter...');
                            }

                            // Gå til næste butik
                            currentIndex++;
                            updateProgress(currentIndex, totalShops);

                            // Fortsæt med næste efter en kort pause for at undgå at overbelaste serveren
                            setTimeout(processNextShop, 100);
                        },
                        error: function(xhr, status, error) {
                            logMessage('Fejl: ' + error + ' - Springer over butik: ' + shop.name);
                            currentIndex++;
                            updateProgress(currentIndex, totalShops);
                            setTimeout(processNextShop, 100);
                        }
                    });
                }

                function generateCSV(data) {
                    logMessage('Genererer CSV fil...');

                    $.ajax({
                        url: baseUrl + '/generateCSV',
                        type: 'POST',
                        data: { shopData: JSON.stringify(data) },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        success: function(blob) {
                            logMessage('CSV fil genereret. Starter download...');

                            // Opret en midlertidig URL til blob-objektet
                            const url = window.URL.createObjectURL(blob);

                            // Opret et usynligt a-element og klik på det for at starte download
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = 'shop_data_export.csv';
                            document.body.appendChild(a);
                            a.click();

                            // Ryd op
                            window.URL.revokeObjectURL(url);
                            $("#status").text('CSV fil er downloadet!');
                            $("#startProcess").prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            logMessage('Fejl ved generering af CSV: ' + error);
                            $("#status").text('Fejl ved CSV generering!');
                            $("#startProcess").prop('disabled', false);
                        }
                    });
                }

                $("#startProcess").click(function() {
                    $(this).prop('disabled', true);
                    shopData = [];
                    currentIndex = 0;

                    $("#status").text('Henter butikker...');
                    logMessage('Starter processen...');

                    // Hent alle shops først
                    $.ajax({
                        url: baseUrl + '/getShopList',
                        type: 'POST',  // Ændret til POST
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.success) {
                                allShops = response.data;
                                totalShops = allShops.length;

                                if (totalShops === 0) {
                                    logMessage('Ingen butikker fundet!');
                                    $("#status").text('Ingen butikker fundet!');
                                    $("#startProcess").prop('disabled', false);
                                    return;
                                }

                                logMessage('Fandt ' + totalShops + ' butikker. Starter behandling...');
                                updateProgress(0, totalShops);
                                processNextShop();
                            } else {
                                logMessage('Fejl ved hentning af butikker: ' + (response.message || 'Ukendt fejl'));
                                $("#status").text('Fejl ved hentning af butikker!');
                                $("#startProcess").prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            logMessage('Fejl ved hentning af butikker: ' + error);
                            $("#status").text('Fejl ved hentning af butikker!');
                            $("#startProcess").prop('disabled', false);
                        }
                    });
                });
            });
        </script>
        </body>
        </html>
        <?php
    }

    // AJAX endpoints
    public function getShopList()
    {
        $shops = $this->_getShopList(1, 1);

        // Convert to simple array for JSON response
        $result = [];
        foreach ($shops as $shop) {
            $result[] = [
                'id' => $shop->id,
                'name' => $shop->name,
                'so_no' => $shop->so_no,
                'antal' => $shop->antal
            ];
        }

        $this->sendJsonResponse(['success' => true, 'data' => $result]);
    }

    public function getSODebitorNO()
    {
        $soNo = $_POST['soNo'] ?? '';

        if (empty($soNo)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Manglende SO nummer']);
            return;
        }

        $orderHeader = $this->_getSODebitorNO($soNo);

        if ($orderHeader) {
            $this->sendJsonResponse(['success' => true, 'data' => $orderHeader]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Kunne ikke finde debitornr']);
        }
    }

    public function generateCSV()
    {
        $shopDataJson = $_POST['shopData'] ?? '';

        if (empty($shopDataJson)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Ingen data modtaget']);
            return;
        }

        $shopData = json_decode($shopDataJson, true);

        if (!$shopData) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Ugyldig JSON data']);
            return;
        }

        // Konverter data til CSV format
        $data = [];
        foreach ($shopData as $shop) {
            $data[] = [
                $shop['id'],
                $shop['nameSys'],
                $shop['name'],
                $shop['soNo'],
                $shop['debitorNo']
            ];
        }

        $this->_genererOgDownloadCSV($data);
    }

    private function sendJsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Interne metoder med underscore prefix for at undgå navnekonflikter
    private function _getShopList($shopType, $shopLang)
    {
        return \Shop::find_by_sql("
        SELECT 
            shop.id,
            shop.name,
            COUNT(`order`.`shop_id`) as antal,
            shop_metadata.so_no
        FROM `shop` 
        INNER JOIN `order` ON `order`.`shop_id` = shop.id
        INNER JOIN shop_metadata ON shop_metadata.shop_id = shop.id
        WHERE 
            `is_gift_certificate` = 0 
            AND `is_company` = 1 
            AND shop_mode = 1
            AND localisation = 1
        GROUP BY shop.id
        HAVING antal > 10  
        ORDER BY `shop_metadata`.`so_no` ASC;
        ");
    }

    private function _getSODebitorNO($SONo) {
        try {
            $client = new \GFCommon\Model\Navision\SalesShipmentHeadersWS();
            $result = $client->getByOrderNo($SONo);
            if($result != null && count($result) > 0) {
                $orderHeader = $result[0]->getDataArray();
                return $orderHeader;
            }
        }
        catch (\Exception $e) {
            return null;
        }
    }

    private function _genererOgDownloadCSV($dataArray) {
        $filename = 'data_export_' . date('Y-m-d_H-i-s') . '.csv';

        // Åbn output stream
        $output = fopen('php://output', 'w');

        // Send relevante HTTP-headers for at starte download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Skriv CSV-header (kolonne-navne)
        fputcsv($output, array('ID', 'NavnSys', 'Navn', 'SO-nummer', 'Debitornummer'), ';');

        // Skriv alle data-rækker fra arrayet
        foreach ($dataArray as $row) {
            fputcsv($output, $row, ';');
        }

        // Luk output stream
        fclose($output);

        // Afslut for at undgå ekstra output der kan korruptere filen
        exit;
    }
}