<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
class MagentoOrderStockController Extends baseController
{
    private $token = "48s1pd9cstnutyyl1ykncacwvtkn8sf9"; //"i2cmpqapk7n0sjkeu6l6v463qqdoxp5f";

    public function Index()
    {
        $this->registry->template->show('magento_order_stock_view');
        //  https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=MagentoOrderStock/index
        //https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=MagentoOrderStock/run
    }



    public function run(){

        $storeID= $_GET["storeid"];

        //$baseUrl = "http://shopgavefabrikke.dev.magepartner.net";
        $baseUrl = "https://gavefabrikken.dk";
        $token = $this->token; // Erstat dette med din faktiske access token
        $customCriteria = [

            'searchCriteria[pageSize]' => '300'

        ];
        // henter data fra Magento
        $result = $this->fetchMagentoOrders($baseUrl, $token,$storeID, $customCriteria);
        //print_R($result);

        // procss output
        if (isset($result['error'])) {
            echo "Der opstod en fejl: " . $result['error'];
            if (isset($result['response'])) {
                print_r($result['response']);
            }
        } else {
            // udtrækker relevante data
            $extractedData =   $this->extractOrderData($result);
        }




        foreach ($extractedData['orders'] as $order) {
            $store_id = $order['store_id'];

            $order_id = $order['order_id'];


            foreach ($order['items'] as $item) {


                $sku = $item['sku'];
                $qty = $item['qty'];

                // gemmer ordre i log
                // hardcode mapping mellem valgshop og storeview
                $valgshopID = "";
                if($store_id == 25){  // Tjellesen
                    $valgshopID = 6924;
                }
                if($store_id == 20){  // Rockwool
                    $valgshopID = 8005;
                }
                if($store_id == 58){  // dsv
                    $valgshopID = 7909;
                }
                if($store_id == 15){ // ALK Abello
                    $valgshopID = 7911;
                }
                if($store_id == 41){  // PWC
                    $valgshopID = 7912;
                }
                if($store_id == 19){  // SOLAR
                     $valgshopID = 7916;
                }
                if($store_id == 29){  // Lundbeck
                    $valgshopID = 8007;
                }






                $rs = $this->getPresent($sku,$valgshopID);
                if(!$rs){
                    $model_id =  $order_id;
                    $model_name =  "none";

                } else {
                    $model_id =  $rs[0]->attributes["model_id"];
                    $model_name =  $rs[0]->attributes["model_name"];
                }


                $this->logSale($sku,$qty,$store_id,$order_id,$model_id,$model_name);

            }
        }

        //$presentModelData = $this->getPresent('139157A210',7376);
        //$res = $this->extractPresentModelData($presentModelData);
        //print_R($res);

        /*
        $result = $this->changePresent(
            7376,        // shopId
            3874828,     // userId
            195760,      // presentsId
            243818,      // modelId
            'Lego Tusindårsfalken V29 - lille',  // modelName
            0            // skipEmail
        );

        print_r($result);
        */
    }

    public function updateActiveStatus()
    {

        $valgshop_model_id = $_POST["valgshop_model_id"];
        $active = $_POST["active"];
    

    }

    public function analyzeStockStatusAndSendEmail() {
        $storeview_id = $_GET["storeid"];

        $shopnavn = "";
        if($storeview_id == 20){
            $shopnavn = "Rockwool";
        }
        if($storeview_id == 25){
            $shopnavn = "Tjellesen";
        }
        if($storeview_id == 57){
            $shopnavn = "dsv";
        }
        if($storeview_id == 15){
            $shopnavn = "ALK Abello";
        }
        if($storeview_id == 41){
            $shopnavn = "PWC";
        }
        if($storeview_id == 19){
            $shopnavn = "SOLAR";
        }
        if($storeview_id == 29){
            $shopnavn = "Lundbeck";
        }




        $stockStatus = $this->getStockStatusComparison($storeview_id);
        $criticalItems = [];

        foreach ($stockStatus as $item) {
            // Skip items with "none" as the model name
            if ($item->attributes["valgshop_model_name"] === "none") {
                continue;
            }

            // Check if difference is 0 or negative
            if ($item->attributes["difference"] <= 0) {
                $criticalItems[] = [
                    'shopnavn' => $shopnavn,
                    'sku' => $item->attributes["sku"],
                    'valgshop_model_id' => $item->attributes["valgshop_model_id"],
                    'valgshop_model_name' => $item->attributes["valgshop_model_name"],
                    'total' => $item->attributes["total"],
                    'reserved_quantity' => $item->attributes["reserved_quantity"],
                    'difference' => $item->attributes["difference"]
                ];
            }
        }

        if (!empty($criticalItems)) {
        //    $this->sendCriticalStockEmail($criticalItems);
        }

        return $criticalItems;
    }

    private function sendCriticalStockEmail($criticalItems) {
        $to = "recipient@example.com"; // Replace with actual recipient email
        $subject = "Magento VIP overskredet reservation";

        // Build HTML table
        $tableHtml = "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>
        <tr>
            <th>Navn</th>
            <th>SKU</th>
            <th>Model ID</th>
            <th>Model Name</th>
            <th>Total Stock</th>
            <th>Reserved Quantity</th>
            <th>Difference</th>
        </tr>";

        foreach ($criticalItems as $item) {
            $tableHtml .= "<tr>
            <td>{$item['shopnavn']}</td>
            <td>{$item['sku']}</td>
            <td>{$item['valgshop_model_id']}</td>
            <td>{$item['valgshop_model_name']}</td>
            <td>{$item['total']}</td>
            <td>{$item['reserved_quantity']}</td>
            <td>{$item['difference']}</td>
        </tr>";
        }

        $tableHtml .= "</table>";

        $message = "
    <html>
    <head>
        <title>Critical Stock Alert</title>
    </head>
    <body>
        <h2>Critical Stock Alert</h2>
        <p>The following items have critically low stock:</p>
        $tableHtml
    </body>
    </html>
    ";
        //echo $message;
        //$headers = "MIME-Version: 1.0" . "\r\n";
        //$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        //$headers .= "From: sender@example.com" . "\r\n"; // Replace with actual sender email

        // Send email
    //    mail($to, $subject, $message, $headers);1017366

        $mailqueue = new MailQueue();
        $mailqueue->sender_name  = 'Gavefabrikken';
        $mailqueue->sender_email = 'info@gavefabrikken.net';
        $mailqueue->recipent_email = "us@gavefabrikken.dk";
        $mailqueue->subject = $subject;
        $mailqueue->body = $message;
        $mailqueue->save();

        $mailqueue = new MailQueue();
        $mailqueue->sender_name  = 'Gavefabrikken';
        $mailqueue->sender_email = 'info@gavefabrikken.net';
        $mailqueue->recipent_email = "bi@gavefabrikken.dk";
        $mailqueue->subject = $subject;
        $mailqueue->body = $message;
        $mailqueue->save();
        System::connection()->commit();
        System::connection()->transaction();
    }



    public function updateReservedQuantity()
    {
        $valgshop_model_id = $_POST["valgshop_model_id"];
        $shop_id =  $_POST["shop_id"];
        $presentReservation = PresentReservation::find('first', array(
            'conditions' => array(
                'shop_id = ? AND model_id = ?',
                $shop_id, $valgshop_model_id
            )
        ));
        if ($presentReservation) {
            $presentReservation->quantity = $_POST["reserved_quantity"];
            $presentReservation->save();
        } else {
            // Handle the case when no matching record is found
            // Maybe create a new record or log an error
        }

        System::connection()->commit();
        System::connection()->transaction();
        response::success(make_json("result", $presentReservation));
    }

    public function getStockStatus()
    {

        $storeview_id = $_POST["storeview"];
        $stock_status = $this->getStockStatusComparison($storeview_id);
        response::success(make_json("result", $stock_status));

/*
        foreach ($stock_status as $item) {
            echo "Storeview ID: " . $storeview_id .
                ", Valgshop Model ID: " . $item->valgshop_model_id .
                ", Total Number: " . $item->total .
                ", Reserved Quantity: " . $item->reserved_quantity .
                ", Is Over Reserved: " . $item->is_over_reserved.
                ", Difference: " . $item->difference .
                ", Model ID: " . $item->model_id .
                ", Shop ID: " . $item->shop_id . "\n";
        }
*/
  //      echo "hej";
    }
    function getStockStatusComparison($storeview_id) {
        $result = MagentoOrderStock::find('all', array(
            'select' => 'magento_vip_stock.valgshop_model_id, 
                    SUM(magento_vip_stock.number) as total, 
                    present_reservation.quantity as reserved_quantity,
                    present_reservation.model_id,
                    present_reservation.shop_id,
                    magento_vip_stock.sku,
                    magento_vip_stock.valgshop_model_name,
                    magento_vip_stock.active
                    
                    ',
            'joins' => array(
                'LEFT JOIN present_reservation ON present_reservation.model_id = magento_vip_stock.valgshop_model_id'
            ),
            'conditions' => array(
                'magento_vip_stock.storeview_id = ? AND magento_vip_stock.valgshop_model_id IS NOT NULL',
                $storeview_id
            ),
            'group' => 'magento_vip_stock.valgshop_model_id, present_reservation.model_id, present_reservation.shop_id'
        ));

        // Process the results to add the comparison


        foreach ($result as $item) {
            $item->attributes["is_over_reserved"] = ($item->attributes["reserved_quantity"] <= $item->attributes["total"] ) ? 'yes' : 'no';
            $item->attributes["difference"] = $item->attributes["reserved_quantity"] - $item->attributes["total"]  ;
        }


        return $result;
    }

    private function getValgshopModelTotals($storeview_id) {


        $result = MagentoOrderStock::find('all', array(
            'select' => 'valgshop_model_id, SUM(number) as total_number',
            'conditions' => array(
                'storeview_id = ? AND valgshop_model_id IS NOT NULL',
                $storeview_id
            ),
            'group' => 'valgshop_model_id'
        ));

        return $result;
    }

    private function getPresent($sku,$shop_id){
        $list = PresentModel::find('all', array(
            'joins' => array('INNER JOIN present ON present.id = present_model.present_id'),
            'conditions' => array(
                'present.shop_id = ? AND present_model.language_id = ? AND present_model.model_present_no = ?',
                $shop_id, 1, $sku
            ),
            'select' => 'present_model.*'
        ));

        // Check if the result count is exactly 1
        if (count($list) !== 1) {
            return false;
        }

        // If exactly one result, return the list (which contains one item)
        return $list;
    }
    private function logSale($sku,$qty,$store_id,$order_id,$model_id,$model_name)
    {

        $existing_record = MagentoOrderStock::find('all', array(
            'conditions' => array('order_id = ? AND sku = ?', $order_id, $sku)
        ));

        if ($existing_record) {
            // Record already exists, no need to update
            echo "<br>Record for SKU $sku in Order $order_id already exists. Skipping.\n";
        } else {
            // Create new record
            $new_record = new MagentoOrderStock();
            $new_record->sku = $sku;
            $new_record->number = $qty;
            $new_record->storeview_id = $store_id;
            $new_record->order_id = $order_id;
            $new_record->valgshop_model_id = $model_id;
            $new_record->valgshop_model_name = $model_name;


            $new_record->save();

            echo "Created new record for SKU $sku in Order $order_id.\n";
        }

        System::connection()->commit();
        System::connection()->transaction();
    }

    private function changePresent($shopId, $userId, $presentsId, $modelId, $modelName, $skipEmail = 0) {
        // URL til API endpoint
        $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=order/changePresent';

        // Forbered POST data
        $postData = [
            'shopId' => $shopId,
            'userId' => $userId,
            'presentsId' => $presentsId,
            'modelId' => $modelId,
            'model_id' => $modelId, // Dette ser ud til at være en duplikation baseret på din tidligere data
            'modelName' => $modelName,
            'skip_email' => $skipEmail
        ];

        // Initialiser cURL session
        $ch = curl_init($url);

        // Sæt cURL optioner
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bemærk: Dette bør kun bruges i testmiljøer
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Bemærk: Dette bør kun bruges i testmiljøer

        // Udfør cURL anmodning
        $response = curl_exec($ch);

        // Tjek for fejl
        if(curl_errno($ch)) {
            $error = 'cURL Error: ' . curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        // Luk cURL session
        curl_close($ch);

        // Dekod JSON respons
        $decodedResponse = json_decode($response, true);

        // Tjek om dekodet respons er gyldig
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Ugyldig JSON respons: ' . json_last_error_msg()];
        }

        return $decodedResponse;
    }
    private function extractPresentModelData($presentModelArray) {
        // Tjek om arrayet er tomt
        if (empty($presentModelArray) || !isset($presentModelArray[0])) {
            return array('error' => 'Ingen data fundet');
        }

        $presentModel = $presentModelArray[0];

        // Tjek om objektet har de forventede egenskaber
        if (!isset($presentModel->attributes)) {
            return array('error' => 'Ugyldigt dataobjekt');
        }

        $attributes = $presentModel->attributes;

        // Udtrækker de ønskede data
        $result = array(
            'presentId' => isset($attributes['present_id']) ? $attributes['present_id'] : null,
            'modelId' => isset($attributes['model_id']) ? $attributes['model_id'] : null,
            'model_name' => isset($attributes['model_name']) ? $attributes['model_name'] : null
        );

        return $result;
    }

    public function addNewUser()
    {
        $data = [
            'attributes_' => [
                ['id' => '42317', 'value' => 'us7'],
                ['id' => '42315', 'value' => 'us7'],
                ['id' => '42316', 'value' => 'us7'],
                ['id' => '42318', 'value' => 'us7'],
                ['id' => '42319', 'value' => ''],
                ['id' => '43892', 'value' => ''],
                ['id' => '44308', 'value' => '']
            ],
            'data' => [
                'userId' => null,
                'shopId' => '7376',
                'companyId' => 61801
            ]
        ];
    //    $res = $this->addShopUser($data);
    //    print_r($res);

    }



    private function fetchMagentoOrders($baseUrl, $token,$storeID, $searchCriteria = []) {
        // Opret cURL-sessionen
        $ch = curl_init();
        $yesterday = date('Y-m-d H:i:s', strtotime('-10 day'));
        // Opsætning af standard søgekriterier
        $defaultCriteria = [
            'searchCriteria[filter_groups][0][filters][0][field]' => 'status',
            'searchCriteria[filter_groups][0][filters][0][condition_type]' => 'eq',
            'searchCriteria[filter_groups][0][filters][0][value]' => 'processing',
            'searchCriteria[filter_groups][1][filters][0][field]' => 'updated_at',
            'searchCriteria[filter_groups][1][filters][0][condition_type]' => 'gt',
            'searchCriteria[filter_groups][1][filters][0][value]' => $yesterday,
            'searchCriteria[filter_groups][2][filters][0][field]' => 'store_id',
            'searchCriteria[filter_groups][2][filters][0][condition_type]' => 'eq',
            'searchCriteria[filter_groups][2][filters][0][value]' => $storeID,
            'searchCriteria[pageSize]' => '300',
            'searchCriteria[sortOrders][0][field]' => 'updated_at',
            'searchCriteria[sortOrders][0][direction]' => 'DESC'
        ];


        // Kombiner standard søgekriterier med eventuelt tilpassede kriterier
        $finalCriteria = array_merge($defaultCriteria, $searchCriteria);

        // Opbyg den fulde URL med søgekriterier
        $url = $baseUrl . '/rest/V1/orders?' . http_build_query($finalCriteria);

        // Opsætning af cURL-indstillinger
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Tilføj token til header
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);

        // Udfør cURL-anmodningen
        $response = curl_exec($ch);
    //    print_r($response);


        // Tjek for fejl
        if (curl_errno($ch)) {
           echo  $error = 'cURL-fejl: ' . curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        // Hent HTTP-statuskoden
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Luk cURL-sessionen
        curl_close($ch);

        // Dekod JSON-responsen
        $decodedResponse = json_decode($response, true);

        // Hvis statuskoden ikke er 200, returner fejl
        if ($httpCode !== 200) {
            return [
                'error' => 'HTTP-fejl: ' . $httpCode,
                'response' => $decodedResponse
            ];
        }

        // Returner det dekodede svar
        return $decodedResponse;
    }

    private function extractOrderData($ordersData) {
        $result = [];
        $newestUpdatedAt = null; // Ændret fra oldestUpdatedAt til newestUpdatedAt

        foreach ($ordersData['items'] as $order) {
            $orderData = [
                'order_id' => $order['increment_id'],
                'updated_at' => $order['updated_at'],
                'store_id' => $order['store_id'],
                'items' => []
            ];

            foreach ($order['items'] as $item) {
                $orderData['items'][] = [
                    'sku' => $item['sku'],
                    'qty' => (int)$item['qty_ordered']
                ];
            }

            $result[] = $orderData;

            // Opdater newestUpdatedAt
            if ($newestUpdatedAt === null || $order['updated_at'] > $newestUpdatedAt) {
                $newestUpdatedAt = $order['updated_at'];
            }
        }

        return [
            'orders' => $result,
            'newest_updated_at' => $newestUpdatedAt // Ændret fra oldest_updated_at til newest_updated_at
        ];
    }
    private static function addShopUser($data)
    {
        try {
            // Valider input data
            if (!isset($data['data']['shopId']) || !isset($data['data']['companyId'])) {
                throw new Exception("Manglende påkrævede felter");
            }

            // Konverter attributes_ til JSON, hvis det ikke allerede er en streng
            if (isset($data['attributes_']) && is_array($data['attributes_'])) {
                $data['attributes_'] = json_encode($data['attributes_']);
            }

            // Konverter data til JSON, hvis det ikke allerede er en streng
            if (isset($data['data']) && is_array($data['data'])) {
                $data['data'] = json_encode($data['data']);
            }

            // Tilføj shop bruger via Shop model
            $shopUser = Shop::addShopUser($data);

            // Hvis Shop::addShopUser returnerer false eller null, kast en undtagelse
            if (!$shopUser) {
                throw new Exception("Kunne ikke oprette shop bruger");
            }

            // Definer indstillinger for JSON-responsen
            $options = array('include' => array('attributes_'));

            // Generer og send success respons
            return response::success(make_json("shopuser", $shopUser, $options));
        } catch (Exception $e) {
            // Håndter fejl og send fejlrespons
            return response::error($e->getMessage());
        }
    }

}