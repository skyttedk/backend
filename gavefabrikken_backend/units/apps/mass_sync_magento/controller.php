<?php

namespace GFUnit\apps\mass_sync_magento;
set_time_limit(100);
use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;
use GFUnit\apps\mass_sync_magento\checkHelpers;


class Controller extends UnitController
{
    private $token = '48s1pd9cstnutyyl1ykncacwvtkn8sf9';

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
// gavefabrikken_no_store_view)


    // https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/mass_sync_magento/Usalgspris_Beregning
    public function Usalgspris_Beregning()
    {
        $list = [
            "18294", "14549", "999410014", "999410052", "240195",
            "240113", "240116", "CC006292-001", "240194", "220160",
            "210132", "240129", "240163", "210161", "18384",
            "240112", "240115", "240174", "CC006284-001", "220158",
            "CC006283-001", "230163", "240196", "220156", "230205",
            "240182", "240175", "240108", "3001", "230133",
            "CC006291-001", "CC006285-001", "240183", "18362", "230204",
            "210135", "240169", "210169", "200107", "230153",
            "18365", "240151", "14652", "220146", "220147",
            "B-22-12", "240170", "230196", "94302436", "999310014",
            "240168", "240162", "CC006637-001", "210166", "190109",
            "210110", "240127", "230202", "CC006293-001", "CC006286-001",
            "240106", "240305", "230306", "230304", "230131",
            "240152", "5924COW-BLA", "5924COW-TAN", "220148", "1062516",
            "210101", "230140", "30-LG0119OAK", "240142", "240147",
            "210156", "240125", "10811-MB", "240105", "3586847",
            "210138", "220131", "230193", "230187", "11076",
            "12026", "800302500", "30-HSSKCS5", "220137", "220136",
            "240146", "10832-200", "10832-220", "230154", "230101",
            "230104", "220157", "240132", "230127", "230164",
            "240209", "240176", "30-HSSCSKF3", "220152", "230132",
            "230201", "240121", "240104", "210102", "240141",
            "10833", "220117", "220128", "220166", "240164",
            "240179", "240181", "34449", "30-HSSCKF2", "240306",
            "15791", "230136", "230156", "220111", "10019593",
            "240167", "571623", "210117", "9160", "240171",
            "KO06-0130LS000", "KO15-0120LS041-02", "KO15-0120LS037-02", "KO15-0120LS038-02", "200129",
            "230105", "3587583", "230206", "240107", "230155",
            "240193", "240120", "KFJD89", "230194", "240352",
            "230151", "230126", "10829-GB-240", "10735", "689497",
            "220124", "220174", "220119", "KFTE89", "240133",
            "220145", "220154", "637137", "BS210603", "220162",
            "230160", "230142", "210160", "220127", "9151",
            "3586663", "10019721", "10019720", "230168", "10020705",
            "10018211", "10783", "689511", "5703957200183", "1052-1",
            "18290", "5795RPV", "230106", "230134", "230191",
            "KFLP89", "240173", "34447", "240150", "220163",
            "230167", "230170", "10784", "246625", "240109",
            "240137", "230192", "230141", "1051", "19213",
            "230123", "210171", "34472", "34448", "599999961",
            "210137", "230186", "689512", "200480", "240166",
            "11075", "240153", "230150", "220132", "18385",
            "240117", "5922COW-BLA", "220108", "230128", "34495",
            "240134", "240208", "240110", "201460", "230146",
            "240206", "240204", "240205", "240172", "10020708",
            "220112", "230137", "230157", "210154", "240154",
            "200142", "1064", "220121", "AB273-A219", "B-21-21",
            "7261", "220122", "246628", "XKFXHT02", "210134",
            "30697", "210209", "240210", "240144", "230190",
            "1050", "210140", "9008", "18296", "840340",
            "CC006412-001", "10020258", "705222103038", "7007-1", "705223105028",
            "705222103028", "KO15-0101LS041-02", "KO15-0101LS037-02", "KO15-0101LS038-02", "230118",
            "230116", "230114", "FFC424H", "FFC424N", "FFC427N",
            "39541", "39543", "39543", "39542", "240178",
            "26325", "230158", "10782", "210111"
        ];

        foreach ($list as $sku){
            $rs = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE language_id = 4 AND `no` LIKE '$sku'");

            if (empty($rs)) {
                continue;
            }

            $vejl_pris = $rs[0]["vejl_pris"];

            // Byg URL
            $url = "https://gavefabrikken.dk/aa9.php?sku=".$sku."&price=".$vejl_pris."&token=dfklasdjf23fd[!#!sdSDFE534gdfdfgD";

            // Kald curl funktionen
            $response = $this->callMagentoSql($url);

            // Output response (kan tilpasses efter behov)
            echo "SKU: $sku, Pris: $vejl_pris, Response: $response";
            //echo  $response;
            echo "<br>";
        }
    }
    public function callMagentoSql($url)
    {
        // Opret cURL session
        $ch = curl_init();

        // Sæt cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Deaktiver SSL verifikation hvis nødvendigt

        // Udfør cURL kald
        $response = curl_exec($ch);

        // Check for fejl
        if(curl_errno($ch)){
            echo 'Curl error: ' . curl_error($ch);
        }

        // Luk cURL session
        curl_close($ch);

        return $response;
    }






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

        $return = ["status" => 1];
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
        \response::success(json_encode($return));


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