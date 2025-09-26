<?php

namespace GFUnit\apps\mass_sync_magento;
set_time_limit(100);
use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;
use GFUnit\apps\mass_sync_magento\checkHelpers;


class Controller extends UnitController
{
    private $token = '48s1pd9cstnutyyl1ykncacwvtkn8sf9';
    private $baseUrl = 'https://gavefabrikken.dk';
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
// gavefabrikken_no_store_view)
    public function getAllJobs()
    {
        $options =  array('order' => 'id ASC');
        $data = \KontainerSyncJob::find_by_sql("SELECT 
    kontainer_sync_job.*,
    SUM(CASE WHEN kontainer_queue.done = 1 THEN 1 ELSE 0 END) AS done_count,
    SUM(CASE WHEN kontainer_queue.done = 0 THEN 1 ELSE 0 END) AS not_done_count
FROM 
    `kontainer_sync_job`
LEFT JOIN 
    kontainer_queue ON kontainer_sync_job.id = kontainer_queue.job_id
GROUP BY 
    kontainer_sync_job.id order by id DESC");


        \response::success(json_encode($data));
    }
    public function deleteJob()
    {

        \KontainerSyncJob::deleteKontainerSyncJob($_POST);
        $dummy = [];
        \response::success(json_encode($dummy));

    }

    public function createJob()
    {
        $country =  $_POST["country"];
        $store = $_POST["store"];

        $itemListString =  $_POST["itemList"];
        $itemList = array_filter(explode("\n", $itemListString), 'trim');
        $settings = strval(json_encode([
            'online' => $_POST["online"],
            'price' => $_POST["price"]
        ]));
        $title = $_POST["title"];
        $jobData = [
            'title' => $title,
            'mail' =>  '',
            'settings' => $settings,
            'mode'=>$_POST["mode"],
            'country'=>$country
        ];
        $job = \KontainerSyncJob::createKontainerSyncJob($jobData);

        echo $id = $job->attributes["id"];
        foreach ($itemList as $item){
            if (!empty($item)) {
                $queueData = [
                    'job_id' => $id,
                    'itemno' => $item,
                    'country' =>$country,
                    'store' =>$store
                ];
                \KontainerQueue::createKontainerQueue($queueData);
            }
        }
        $dummy = [];
        \response::success(json_encode($dummy));
    }

    public function handleQueue()
    {
        $dummy = [];
        // tjek om kø er optaget
        $queueStatus = \KontainerSyncJob::find_by_sql("select * from kontainer_queue_status");

        if($queueStatus[0]->attributes["queue_run"] == 1){
        //    echo "is running";
          //  return;
        }

        $params = array(
            'status' => 0
        );
        $job =  \KontainerSyncJob::find('all', $params);


        if(!$job){
            \Dbsqli::setSql2("UPDATE `kontainer_queue_status` set `queue_run` = 0");
            return;
        }
        $country = $job[0]->attributes["country"];
        $jobID = $job[0]->attributes["id"];
        $limit = $job[0]->attributes["mode"] == 1 ? 3:30;
        $settings = $job[0]->attributes["settings"] ;



        $queue =  \KontainerQueue::find('all', array(
            'conditions' => array('done = ? AND job_id = ?', 0, $jobID),
            'limit' => $limit
        ));


        if(!$queue){
            \Dbsqli::setSql2("UPDATE `kontainer_sync_job` set status = 2 where status=0");
            \Dbsqli::setSql2("UPDATE `kontainer_queue_status` set `queue_run` = 0");
            \response::success(json_encode($dummy));
            return;
        }

        \Dbsqli::setSql2("UPDATE `kontainer_queue_status` set `queue_run` = 1");



        foreach($queue as $queueItem) {
            \KontainerQueue::updateKontainerQueue(array(
                "id" => $queueItem->id,
                "done" => 1
            ));
            \System::connection()->commit();
            \System::connection()->transaction();

            // sync magento job
                        if ($job[0]->attributes["mode"] == 1) {
                            $kon = new KontainerCom;

                            $konData = $kon->getDataOnItemnr($queueItem->itemno);
                            $konData = json_decode($konData);

                            if(sizeof($konData->data) == 0){
                                return;
                            }
                            $pimID = $konData->data[0]->id;

                            $responseData = $this->sync($pimID,$country,$settings,$queueItem->store);


                            if ($responseData) {
                                \KontainerQueue::updateKontainerQueue(array(
                                    "id"=>$queueItem->id,
                                    "receive_data" => $responseData,
                                    "has_sync_error" => 1
                                ));

                                \KontainerSyncJob::updateKontainerSyncJob(array(
                                    "id"=>$jobID,
                                    "has_error"=>1
                                ));

                            } else {
                                \KontainerQueue::updateKontainerQueue(array(
                                    "id"=>$queueItem->id,
                                    "receive_data" => $responseData
                                ));
                            }

                        }


            // tjeck job
            if($job[0]->attributes["mode"] == 2){



               $errorStatus = $this->verifyPimData($queueItem->itemno,$country);

                $hasError = in_array(false, $errorStatus, true);
                $responseData = json_encode($errorStatus);
                if($hasError){
                    \KontainerQueue::updateKontainerQueue(array(
                        "id"=>$queueItem->id,
                        "receive_data" => $responseData,
                        "has_pim_error" => 1
                    ));
                    \KontainerSyncJob::updateKontainerSyncJob(array(
                        "id"=>$jobID,
                        "has_error"=>1
                    ));
                }
            }
        }




        \Dbsqli::setSql2("UPDATE `kontainer_queue_status` set `queue_run` = 0");
        \response::success(json_encode($dummy));


    }
    private function getItem($sku,$storeview)
    {
        $productUrl = 'https://gavefabrikken.dk/rest/'.$storeview.'/V1/products/' . $sku;



// Initialize cURL
        $ch = curl_init();

// Set the cURL options
        curl_setopt($ch, CURLOPT_URL, $productUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $this->token,
            "Content-Type: application/json"
        ));

// Execute the request and get the product information
        $productInfo = curl_exec($ch);

// Check if any error occurred
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

// Close cURL resource
        curl_close($ch);

// Display the product information
        return $productInfo;
    }
public function verifyPimData($itemno,$country)
{

    $errorStatus = array(
        "pim_item"=>false,

        "nav_item"=>false,
        "nav_vejl_price"=>false,
        "nav_price"=>false,

        "magento_item_storeview"=>false,
        "magento_price_storeview"=>false,
        "pim_headline"=>false,
        "pim_description"=>false,
        "pim_assets"=>false,
        "pim_billeder_godkendt"=>false,
        "pim_tekst_godkendt"=>false,
        "pim_type"=> false,
        "pim_approvement"=> false,
        "pim_storeview"=>false
);
    // $itemno = "sdafasd"; 210209

// PIM

    $kon = new KontainerCom;
    $konData = $kon->getDataOnItemnr($itemno);

    $konData = json_decode($konData);

    if(sizeof($konData->data) > 0){

        $errorStatus["pim_item"] = true;    // hvis varen ikke er i
        $pim = $konData->data[0]->attributes;

        if($country == 1) {
            $magentoItem =  $this->getItem($itemno,"default");
            $magentoItem = json_decode($magentoItem, true);



            if (isset($magentoItem['id'])) {
                $errorStatus["magento_item_storeview"] = true;
                if (isset($magentoItem['price']) && $magentoItem['price'] != "" && $magentoItem['price'] != 0 ) {
                    $errorStatus["magento_price_storeview"] = true;
                }
            }


            // Storeview
            if (isset($pim->storeview) && is_array($pim->storeview)) {
                foreach ($pim->storeview as $item) {
                    if (isset($item->value) && $item->value === "Shopgavefabrikken") {
                        $errorStatus["pim_storeview"] = true;
                        break;
                    }
                    if (isset($item->value) && $item->value === "Gaveklubben") {
                        $errorStatus["pim_storeview"] = true;
                        break;
                    }
                }
            }

            // Approvement ?
            if (isset($pim->approvement) && is_array($pim->approvement)) {
                foreach ($pim->approvement as $item) {
                    if (isset($item->value) && ($item->value === "MLH '24" || $item->value === "Tullin '24")) {
                        $errorStatus["pim_approvement"] = true;
                        break;
                    }
                }
            }

            // Description DA
            if (isset($pim->description_da) && !empty($pim->description_da->value)) {
                $errorStatus["pim_description"] = true;
            }
            // tjek title
            if (isset($pim->product_name_da) && !empty($pim->product_name_da->value)) {
                $errorStatus["pim_headline"] = true;
            }
        }

        if($country == 4) {
            // Storeview

            $magentoItem =  $this->getItem($itemno,"gavefabrikken_no_store_view");

            $magentoItem = json_decode($magentoItem, true);




            if (isset($magentoItem['id'])) {
                $errorStatus["magento_item_storeview"] = true;
                if (isset($magentoItem['price']) && $magentoItem['price'] != "" && $magentoItem['price'] != 0 ) {
                     $errorStatus["magento_price_storeview"] = true;
                }
            }


            if (isset($pim->storeview) && is_array($pim->storeview)) {
                foreach ($pim->storeview as $item) {
                    if (isset($item->value) && $item->value === "GF NO") {
                        $errorStatus["pim_storeview"] = true;
                        break;
                    }
                }
            }

            // Approvement ?
            if (isset($pim->approvement) && is_array($pim->approvement)) {
                foreach ($pim->approvement as $item) {
                    if (isset($item->value) && ($item->value === "Norge'24" )) {
                        $errorStatus["pim_approvement"] = true;
                        break;
                    }
                }
            }


            // Description DA
            if (isset($pim->description_no) && !empty($pim->description_no->value)) {
                $errorStatus["pim_description"] = true;
            }
            // tjek title
            if (isset($pim->product_name_no) && !empty($pim->product_name_no->value)) {
                $errorStatus["pim_headline"] = true;
            }
        }

        // er der assets
        if (isset($pim->image_1) && !empty($pim->image_1->value) ){
            $errorStatus["pim_assets"] = true;
        }
        //  tekst godkendt
        if (isset($pim->tekst_godkendt) && !empty($pim->tekst_godkendt->value) ){
            $errorStatus["pim_tekst_godkendt"] = true;
        }
// billeder godkendt
        if (isset($pim->billeder_godkendt) && !empty($pim->billeder_godkendt->value) ){
            $errorStatus["pim_billeder_godkendt"] = true;
        }
        // tjek status
        if (isset($pim->product_type->value) && $pim->product_type->value === 'Product'){
            $errorStatus["pim_type"] = true;
        }
    }
    //  ------ PIM end  --------



    // ----------- NAV ----------

    $nav =  \NavisionItem::find('all', array(
        'conditions' => array('no = ? AND language_id = ? and ISNULL(deleted) ', $itemno, $country)
    ));


    if($nav){
        $errorStatus["nav_item"] = true;
        if (isset($nav[0]->attributes["vejl_pris"]) && !empty($nav[0]->attributes["vejl_pris"]) && $nav[0]->attributes["vejl_pris"] > 0 ){
            $errorStatus["nav_vejl_price"] = true;
        }
    }
    if($country == 1){
        $rs = \Dbsqli::getSql2("SELECT unit_price as vejl_pris FROM `navision_salesprice` WHERE language_id = $country  and `item_no` LIKE '$itemno' and sales_code = 'GAVESPEC' and ISNULL(`deleted`) ORDER BY `navision_salesprice`.`starting_date` DESC limit 1 ");
        if(sizeof($rs) >0 ){
            $errorStatus["nav_price"] = true;
        }
    }
    if($country == 4){
        $rs = \Dbsqli::getSql2("SELECT unit_price as vejl_pris FROM `navision_salesprice` WHERE language_id = $country  and `item_no` LIKE '$itemno' and sales_code = 'GAVESPECNO'  and ISNULL(`deleted`) ORDER BY `navision_salesprice`.`starting_date` DESC limit 1 ");

        if(sizeof($rs) > 0 ){
              $errorStatus["nav_price"] = true;
        }
    }



    return $errorStatus;
}




    public function sync($pimID,$country,$settings,$storeview)
    {

        $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/magento/test';

// Data der skal sendes
        $showPrice = json_decode($settings)->price;

        $postData = [
            'PIMID' => $pimID,
            'ONLINE' => '0',
            'PRICE' => $showPrice,
            'country'=>$country,
            'storeview'=>$storeview
        ];

        $authKey = 'e12e917707c96c1ca3cb39ae2018fbcbc6d850ba8986abc4f1adb56c0003';
// Initialiser cURL session
        $ch = curl_init($url);

// Sæt cURL optioner
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $authKey
        ]);
// Udfør cURL anmodning og gem responsen
        $response = curl_exec($ch);

// Tjek for fejl
        if(curl_errno($ch)){
            echo 'cURL Error: ' . curl_error($ch);
        }

// Luk cURL session
        curl_close($ch);

// Udskriv responsen
        var_dump($response);
        return $response;
    }

    function getErrorReport($jobId) {
        // Generer dummy data
        $jobId = $_POST["id"];
        $job = \KontainerSyncJob::find($jobId);


        if($job->attributes["mode"] == 1){
            $errorData = $this->getErrorDataSync($jobId);
        }
        if($job->attributes["mode"] == 2){
            $errorData = $this->getErrorData($jobId);
        }
        // Generer CSV-indhold
        $csvContent = $this->generateCSVContent($errorData);

        // Set headers for plain text (CSV) response
        header('Content-Type: text/plain; charset=UTF-8');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output CSV content
        echo $csvContent;
        exit;
    }
    function getAllReports() {
        // Generer dummy data

        $jobId = $_POST["id"];
        $job = \KontainerSyncJob::find($jobId);

        if($job->attributes["mode"] == 1){
            $Data = $this->getAllDataSync($jobId);
        }
        if($job->attributes["mode"] == 2){
            $Data = $this->getAllData($jobId);
        }
        $csvContent = $this->generateCSVContent($Data);


        // Generer CSV-indhold


        // Set headers for plain text (CSV) response
        header('Content-Type: text/plain; charset=UTF-8');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output CSV content
        echo $csvContent;
        exit;
    }
    function getAllDataSync($jobId) {
        $list = \KontainerQueue::find('all', array(
            'conditions' => array(
                'done = ? AND job_id = ?',
                1, $jobId
            )
        ));
        foreach ($list as $item) {
            $errorEntry = [
                'Indsendt varenummer' => $item->itemno,
                'Sync fejl' => $item->has_sync_error ? 'Ja' : 'Nej',
            ];
            $errorData[] = $errorEntry;
        }
        return $errorData;
    }

    function getAllData($jobId) {

        $list = \KontainerQueue::find('all', array(
            'conditions' => array(
                'done = ? AND job_id = ?',
                1, $jobId
            )
        ));

        $errorData = [];
        $errorDataBottom = [];

        foreach ($list as $item) {
            $errorEntry = [
                'Indsendt varenummer' => $item->itemno
            ];

            if ($item->receive_data !== null) {
                $receiveData = json_decode($item->receive_data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    foreach ($receiveData as $key => $value) {
                        if ($key === 'itemno') {
                            $errorEntry['Oprettet i NAV'] = $value ? 'OK' : 'Fejl';
                        } else {
                            $errorEntry[$this->translateErrorKey($key)] = $value ? 'OK' : 'Fejl';
                        }
                    }
                    $errorData[] = $errorEntry;
                } else {
                    // JSON decoding error
                    $errorEntry['Fejl'] = 'Ugyldig JSON data';
                    $errorDataBottom[] = $errorEntry;
                    error_log("JSON decoding error for item {$item->itemno}: " . json_last_error_msg());
                }
            } else {
                // No receive_data
                $errorEntry['Fejl'] = 'Ingen modtagne data';
                $errorDataBottom[] = $errorEntry;
            }
        }

// Combine the arrays, putting errorDataBottom at the end
        $finalErrorData = array_merge($errorData, $errorDataBottom);

        return $finalErrorData;


    }

    function getErrorDataSync($jobId) {
        $list = \KontainerQueue::find('all', array(
            'conditions' => array(
                '(has_sync_error = ? OR has_pim_error = ?) AND done = ? AND job_id = ?',
                1, 1, 1, $jobId
            )
        ));
        $errorData = [];
        foreach ($list as $item) {
            $errorEntry = [
                'Indsendt varenummer' => $item->itemno,
                'Sync fejl' => $item->has_sync_error ? 'Ja' : 'Nej',
                'Error msg' => $item->receive_data
            ];
            $errorData[] = $errorEntry;
        }
        return $errorData;
    }
    function getErrorData($jobId) {
        $list = \KontainerQueue::find('all', array(
            'conditions' => array(
                '(has_sync_error = ? OR has_pim_error = ?) AND done = ? AND job_id = ?',
                1, 1, 1, $jobId
            )
        ));

        $errorData = [];

        foreach ($list as $item) {
            $receiveData = json_decode($item->receive_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $errorEntry = [
                    'Indsendt varenummer' => $item->itemno
                ];

                foreach ($receiveData as $key => $value) {
                    if ($key === 'itemno') {
                        $errorEntry['Oprettet i NAV'] = $value ? 'OK' : 'Fejl';
                    } else {
                        $errorEntry[$this->translateErrorKey($key)] = $value ? 'OK' : 'Fejl';
                    }
                }

                $errorData[] = $errorEntry;
            }
        }

        return $errorData;
    }

    /*
    $errorStatus = array(
"pim_item"=>false,

"nav_item"=>false,
"nav_vejl_price"=>false,
"nav_price"=>false,

"magento_item_store_view_shopgave"=>false,

"pim_headline"=>false,
"pim_description"=>false,
"pim_assets"=>false,
"pim_billeder_godkendt"=>false,
"pim_tekst_godkendt"=>false,
"pim_type"=> false,
"pim_approvement"=> false,
"pim_storeview"=>false
);
    */

    function translateErrorKey($key) {
        $translations = [
            'pim_item' => 'Pim varenummer',
            'nav_item' => 'Nav varenummer',
            'nav_vejl_price' => 'Nav vejl. pris',
            'nav_price' => 'Nav budget',
            'magento_item_store_view_shopgave' => 'Magento varenummer på storeview',

            'pim_headline' => 'Pim overskrift',
            'pim_description' => 'Pim beskrivelse',
            'pim_assets' => 'Pim billeder',
            'pim_billeder_godkendt' => 'Pim billeder godkendt',
            'pim_tekst_godkendt' => 'Pim tekst godkendt',
            'pim_type' => 'Pim type rigtig',
            'pim_approvement' => 'Pim godkendelser',
            'pim_storeview' => 'Pim storeview sat'
        ];

        return isset($translations[$key]) ? $translations[$key] : $key;
    }

    function generateCSVContent($data) {
        if (empty($data)) {
            return "\xEF\xBB\xBFIngen fejl fundet";
        }

        $output = fopen('php://temp', 'r+');

        // Sæt semikolon som separator
        $delimiter = ';';

        // Skriv CSV-header
        fputcsv($output, array_keys($data[0]), $delimiter);

        // Skriv fejldata
        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // Tilføj BOM for korrekt UTF-8 kodning i Excel
        return "\xEF\xBB\xBF" . $csvContent;
    }

}