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
                button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; margin-right: 10px; }
                button:disabled { background-color: #cccccc; }
                button.stop-button { background-color: #f44336; }
                .log { height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-top: 20px; }
                .options { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; }
                .option-group { margin-bottom: 15px; }
                label { display: inline-block; margin-right: 15px; }
                .error-report { display: none; margin-top: 20px; }
                .error-button { background-color: #f44336; }
            </style>
        </head>
        <body>
        <div class="container">
            <h1>Shop Data Export</h1>
            <p>Eksporterer shop-data til CSV fil</p>

            <div class="options">
                <div class="option-group">
                    <h3>Vælg butik type:</h3>
                    <label><input type="radio" name="shopType" value="1" checked> Valgshops</label>
                    <label><input type="radio" name="shopType" value="6"> Papirvalg</label>
                </div>

                <div class="option-group">
                    <h3>Vælg land:</h3>
                    <label><input type="radio" name="country" value="1" checked> Danmark</label>
                    <label><input type="radio" name="country" value="4"> Norge</label>
                    <label><input type="radio" name="country" value="5"> Sverige</label>
                </div>
            </div>

            <div>
                <button id="startProcess">Start Eksport</button>
                <button id="stopResetProcess" class="stop-button" disabled>Stop/Nulstil</button>
                <button id="exportErrors" class="error-button" style="display: none;">Eksportér Fejlrapport</button>
            </div>

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
                let errorData = []; // Array til at gemme fejlede butikker
                let allShops = [];
                let currentIndex = 0;
                let totalShops = 0;
                let hasErrors = false;
                let totalSold = 0;
                let isProcessing = false;
                let shouldStop = false;
                let selectedCountry = 1;

                function logMessage(message, isError = false) {
                    const logClass = isError ? 'text-danger' : '';
                    $("#log").prepend('<div class="' + logClass + '">' + new Date().toLocaleTimeString() + ': ' + message + '</div>');

                    if (isError) {
                        hasErrors = true;
                    }
                }

                function updateProgress(current, total) {
                    const percent = Math.round((current / total) * 100);
                    $("#progressBar").css("width", percent + "%");
                    $("#status").text('Behandler ' + current + ' af ' + total + ' butikker (' + percent + '%)');
                }

                function processNextShop() {
                    if (shouldStop) {
                        logMessage('Processen stoppet af bruger.');
                        $("#status").text('Processen stoppet!');
                        $("#startProcess").prop('disabled', false);
                        $("#stopResetProcess").prop('disabled', false);
                        isProcessing = false;

                        // Vis fejlrapport knap hvis der var fejl
                        if (hasErrors && errorData.length > 0) {
                            $("#exportErrors").show();
                        }
                        return;
                    }

                    if (currentIndex >= totalShops) {
                        // Alle butikker er processeret
                        $("#status").text('Genererer CSV fil...');
                        isProcessing = false;
                        $("#stopResetProcess").text('Nulstil').prop('disabled', false);  // Fixed from stopProcess to stopResetProcess

                        // Vis fejlrapport knap hvis der var fejl
                        if (hasErrors && errorData.length > 0) {
                            $("#exportErrors").show();
                            logMessage('Processen afsluttet med ' + errorData.length + ' fejl. Klik på "Eksportér Fejlrapport" for at downloade fejlrapporten.', true);
                        }

                        generateCSV(shopData);
                        return;
                    }

                    const shop = allShops[currentIndex];

                    // Kun hent debitornummer for danske butikker (country = 1)
                    if (selectedCountry === 1) {
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
                                        debitorNo: debitornr,
                                        antalSolgt: shop.antal
                                    });

                                    logMessage('Behandlet butik #' + shop.id + ': ' + shop.name);
                                } else {
                                    const errorMsg = 'Fejl ved hentning af data for butik: ' + shop.name;
                                    logMessage(errorMsg + ' - fortsætter...', true);

                                    // Gem fejldata
                                    errorData.push({
                                        id: shop.id,
                                        nameSys: shop.name,
                                        soNo: shop.so_no,
                                        errorMsg: 'Kunne ikke hente debitordata'
                                    });
                                }

                                // Gå til næste butik
                                currentIndex++;
                                updateProgress(currentIndex, totalShops);

                                // Fortsæt med næste efter en kort pause for at undgå at overbelaste serveren
                                setTimeout(processNextShop, 100);
                            },
                            error: function(xhr, status, error) {
                                const errorMsg = 'Fejl: ' + error + ' - Springer over butik: ' + shop.name;
                                logMessage(errorMsg, true);

                                // Gem fejldata
                                errorData.push({
                                    id: shop.id,
                                    nameSys: shop.name,
                                    soNo: shop.so_no,
                                    errorMsg: error || 'Netværksfejl eller servertimeout'
                                });

                                currentIndex++;
                                updateProgress(currentIndex, totalShops);
                                setTimeout(processNextShop, 100);
                            }
                        });
                    } else {
                        // For Norge og Sverige, tilføj butikken uden debitor information
                        shopData.push({
                            id: shop.id,
                            nameSys: shop.name,
                            name: shop.name, // Bruger bare systemnavnet som navn
                            soNo: shop.so_no,
                            debitorNo: '', // Tom debitorNo for ikke-danske butikker
                            antalSolgt: shop.antal
                        });

                        logMessage('Behandlet butik #' + shop.id + ': ' + shop.name + ' (uden debitorinfo)');

                        // Gå til næste butik
                        currentIndex++;
                        updateProgress(currentIndex, totalShops);

                        // Fortsæt med næste efter en kort pause
                        setTimeout(processNextShop, 50);
                    }
                }

                function generateCSV(data) {
                    logMessage('Genererer CSV fil...');

                    // Beregn total antal solgt
                    totalSold = data.reduce((sum, shop) => sum + (parseInt(shop.antalSolgt) || 0), 0);

                    $.ajax({
                        url: baseUrl + '/generateCSV',
                        type: 'POST',
                        data: {
                            shopData: JSON.stringify(data),
                            totalSold: totalSold
                        },
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

                            if (hasErrors) {
                                $("#status").text('CSV fil er downloadet! (Med ' + errorData.length + ' fejl)');
                            } else {
                                $("#status").text('CSV fil er downloadet!');
                            }

                            $("#startProcess").prop('disabled', false);
                            $("#stopResetProcess").text('Nulstil').prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            logMessage('Fejl ved generering af CSV: ' + error, true);
                            $("#status").text('Fejl ved CSV generering!');
                            $("#startProcess").prop('disabled', false);

                            // Vis fejlrapport knappen hvis der var fejl under processen
                            if (hasErrors && errorData.length > 0) {
                                $("#exportErrors").show();
                            }
                        }
                    });
                }

                // Handler for fejlrapport eksport
                $("#exportErrors").click(function() {
                    if (errorData.length === 0) {
                        logMessage('Ingen fejl at eksportere.', true);
                        return;
                    }

                    $(this).prop('disabled', true);
                    logMessage('Genererer fejlrapport...');

                    $.ajax({
                        url: baseUrl + '/generateErrorReport',
                        type: 'POST',
                        data: { errorData: JSON.stringify(errorData) },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        success: function(blob) {
                            logMessage('Fejlrapport genereret. Starter download...');

                            // Opret en midlertidig URL til blob-objektet
                            const url = window.URL.createObjectURL(blob);

                            // Opret et usynligt a-element og klik på det for at starte download
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = 'shop_error_report.csv';
                            document.body.appendChild(a);
                            a.click();

                            // Ryd op
                            window.URL.revokeObjectURL(url);
                            logMessage('Fejlrapport er downloadet!');
                            $("#exportErrors").prop('disabled', false);
                        },
                        error: function(xhr, status, error) {
                            logMessage('Fejl ved generering af fejlrapport: ' + error, true);
                            $("#exportErrors").prop('disabled', false);
                        }
                    });
                });

                // Combined Stop/Reset button handler
                $("#stopResetProcess").click(function() {
                    if (isProcessing) {
                        // Stop functionality
                        shouldStop = true;
                        $(this).text('Nulstil');
                        logMessage('Stopper processen...');
                        $("#status").text('Stopper...');
                    } else {
                        // Reset functionality
                        $(this).prop('disabled', true).text('Stop/Nulstil');
                        $("#progressBar").css("width", "0%");
                        $("#log").empty();
                        $("#exportErrors").hide();
                        $("#status").text('Klar til at begynde...');
                        shopData = [];
                        errorData = [];
                        currentIndex = 0;
                        hasErrors = false;
                        totalSold = 0;
                        totalShops = 0;
                        allShops = [];
                        logMessage('Processen er nulstillet.');
                        $("#startProcess").prop('disabled', false);
                    }
                });

                $("#startProcess").click(function() {
                    $(this).prop('disabled', true);
                    $("#stopResetProcess").text('Stop/Nulstil').prop('disabled', false);
                    $("#exportErrors").hide();
                    shopData = [];
                    errorData = [];
                    currentIndex = 0;
                    hasErrors = false;
                    totalSold = 0;
                    isProcessing = true;
                    shouldStop = false;

                    // Hent valgte optioner
                    const shopType = $('input[name="shopType"]:checked').val();
                    selectedCountry = parseInt($('input[name="country"]:checked').val());

                    $("#status").text('Henter butikker...');
                    logMessage('Starter processen for shop type: ' + shopType + ' og land: ' + selectedCountry);

                    // Hent alle shops med valgte filtre
                    $.ajax({
                        url: baseUrl + '/getSoldPrShop',
                        type: 'POST',
                        data: {
                            shopType: shopType,
                            country: selectedCountry
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.success) {
                                allShops = response.data;
                                totalShops = allShops.length;

                                if (totalShops === 0) {
                                    logMessage('Ingen butikker fundet med de valgte kriterier!', true);
                                    $("#status").text('Ingen butikker fundet!');
                                    $("#startProcess").prop('disabled', false);
                                    $("#stopResetProcess").text('Nulstil').prop('disabled', false);
                                    isProcessing = false;
                                    return;
                                }

                                logMessage('Fandt ' + totalShops + ' butikker. Starter behandling...');
                                updateProgress(0, totalShops);
                                processNextShop();
                            } else {
                                const errorMsg = 'Fejl ved hentning af butikker: ' + (response.message || 'Ukendt fejl');
                                logMessage(errorMsg, true);
                                $("#status").text('Fejl ved hentning af butikker!');
                                $("#startProcess").prop('disabled', false);
                                $("#stopResetProcess").text('Nulstil').prop('disabled', false);
                                isProcessing = false;
                            }
                        },
                        error: function(xhr, status, error) {
                            logMessage('Fejl ved hentning af butikker: ' + error, true);
                            $("#status").text('Fejl ved hentning af butikker!');
                            $("#startProcess").prop('disabled', false);
                            $("#stopResetProcess").text('Nulstil').prop('disabled', false);
                            isProcessing = false;
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
        $shopType = isset($_POST['shopType']) ? intval($_POST['shopType']) : 1;
        $country = isset($_POST['country']) ? intval($_POST['country']) : 1;

        $shops = $this->_getShopList($shopType, $country);

        // Convert to simple array for JSON response
        $result = [];
        foreach ($shops as $shop) {
            $result[] = [
                'id' => $shop->id,
                'name' => $shop->name,
                'so_no' => $shop->so_no,
                'antal' => $shop->antal,
                'reservation_code' => $shop->reservation_code
            ];
        }

        $this->sendJsonResponse(['success' => true, 'data' => $result]);
    }

    // Ny endpoint til at erstatte getShopList og inkludere 'antal solgt'
    public function getSoldPrShop()
    {
        $shopType = isset($_POST['shopType']) ? intval($_POST['shopType']) : 1;
        $country = isset($_POST['country']) ? intval($_POST['country']) : 1;

        $shops = $this->_getSoldPrShop($shopType, $country);

        // Convert to simple array for JSON response
        $result = [];
        foreach ($shops as $shop) {
            $result[] = [
                'id' => $shop->shop_id,
                'name' => $shop->name,
                'so_no' => $shop->so_no,
                'antal' => $shop->user_count
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
        $totalSold = $_POST['totalSold'] ?? 0;

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

        // Tilføj total sum som første række
        $data[] = [
            '',
            'Total antal solgt',
            $totalSold,
            '',
            ''
        ];

        // Tilføj hver butiks data
        foreach ($shopData as $shop) {
            $data[] = [
                $shop['id'],
                $shop['nameSys'],
                $shop['name'],
                $shop['soNo'],
                $shop['debitorNo'],
                $shop['antalSolgt']
            ];
        }

        $this->_genererOgDownloadCSV($data, 'data_export_', ['ID', 'NavnSys', 'Navn', 'SO-nummer', 'Debitornummer', 'Antal solgt']);
    }

    public function generateErrorReport()
    {
        $errorDataJson = $_POST['errorData'] ?? '';

        if (empty($errorDataJson)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Ingen fejldata modtaget']);
            return;
        }

        $errorData = json_decode($errorDataJson, true);

        if (!$errorData) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Ugyldig JSON fejldata']);
            return;
        }

        // Konverter fejldata til CSV format
        $data = [];
        foreach ($errorData as $errorItem) {
            $data[] = [
                $errorItem['id'],
                $errorItem['nameSys'],
                $errorItem['soNo'],
                $errorItem['errorMsg'],
                date('Y-m-d H:i:s')
            ];
        }

        $this->_genererOgDownloadCSV($data, 'error_report_', ['ID', 'NavnSys', 'SO-nummer', 'Fejlbeskrivelse', 'Tidspunkt']);
    }

    private function sendJsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Interne metoder med underscore prefix for at undgå navnekonflikter
    private function _getShopList($shopType, $country)
    {
        $orderCheck =  $shopType == "6" ? "":"HAVING antal > 10";

        return \Shop::find_by_sql("
        SELECT 
            shop.id,
            shop.name,
            COUNT(`order`.`shop_id`) as antal,
            shop_metadata.so_no
        FROM `shop` 
        left JOIN `order` ON `order`.`shop_id` = shop.id
        left JOIN shop_metadata ON shop_metadata.shop_id = shop.id
        WHERE 
            `is_gift_certificate` = 0 
            AND `is_company` = 1 
            AND shop_mode = {$shopType}
            AND localisation = {$country}
        
        GROUP BY shop.id
        {$orderCheck}
        ORDER BY `shop_metadata`.`so_no` ASC
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

    private function _genererOgDownloadCSV($dataArray, $filePrefix = 'data_export_', $headers = ['ID', 'NavnSys', 'Navn', 'SO-nummer', 'Debitornummer']) {
        $filename = $filePrefix . date('Y-m-d_H-i-s') . '.csv';

        // Åbn output stream
        $output = fopen('php://output', 'w');

        // Tilføj BOM for at sikre korrekt håndtering af specialtegn
        fputs($output, "\xEF\xBB\xBF");

        // Send relevante HTTP-headers for at starte download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Skriv CSV-header (kolonne-navne)
        fputcsv($output, $headers, ';');

        // Skriv alle data-rækker fra arrayet
        foreach ($dataArray as $row) {
            // Konverter specialtegn til UTF-8 for at sikre korrekt visning
            foreach ($row as &$field) {
                if (is_string($field)) {
                    $field = mb_convert_encoding($field, 'UTF-8', 'auto');
                }
            }
            fputcsv($output, $row, ';');
        }

        // Luk output stream
        fclose($output);

        // Afslut for at undgå ekstra output der kan korruptere filen
        exit;
    }

    private function _getSoldPrShop($shopType, $country)
    {
        $shopList = [];
        $shops = $this->_getShopList($shopType, $country);
        foreach ($shops as $shop){
            $shopList[] = $shop->id;
        }
        $shopList = implode(", ", $shopList);
        $sql = "SELECT shop_user.shop_id, shop.name, shop_metadata.so_no, COUNT(DISTINCT shop_user.id) AS user_count
            FROM `shop_user` 
            INNER JOIN shop ON shop.id = shop_user.shop_id
            LEFT JOIN shop_metadata ON shop_metadata.shop_id = shop.id
            WHERE 
              shop_user.`shop_id` IN (".$shopList.") AND
              shop_user.`is_demo` = 0 AND
              shop_user.`blocked` = 0 
            GROUP BY shop_id, shop.name, shop_metadata.so_no";
        return \Shop::find_by_sql($sql);
    }
}