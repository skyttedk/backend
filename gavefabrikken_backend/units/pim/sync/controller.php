<?php

namespace GFUnit\pim\sync;
set_time_limit(500);
use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;
use GFUnit\pim\sync\magento;
use GFUnit\pim\sync\nav;
use GFUnit\pim\sync\gavevalgPresentModel;


class Controller extends UnitController
{
    private $updateID;

    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public function syncMagento()
    {
        echo "syncMagento";
        //$magento = new Magento;
        //$magento->syncCategories();
    }



    public function encodeStr(){
        $filePath =  __DIR__ . '/import_itemsNO_newULR-1.csv';

// Kontroller, om filen eksisterer
        if (file_exists($filePath)) {
            // Indlæs filindholdet
            $fileContent = file_get_contents($filePath);

            // Encode indholdet med base64
            $encodedContent = $fileContent;

            // Udskriv det encodede indhold
            echo $encodedContent;
        } else {
            echo "Filen findes ikke.";
        }
    }



    public function magentoImportNo()
    {
        $filepath = __DIR__ . '/bearbejdet.csv';
        $lines = file($filepath);

        for($i = 0; $i < sizeof($lines); $i++) {


            try {

                $data = array();
                $lines[$i] = str_replace(array("\n", "\r"), '', $lines[$i]);

                $line = explode(";", $lines[$i]);

                $kunhos = $line[8] == "" ? "" : "kun shopGavefabrikken";
                $omtanke = $line[9] == "" ? "" : "Yes";
                $categories = $this->CSVServiceCategory($line[5]);


                $data["sku"] = $line[0];
                $data["name"] = $line[1];
                $data["short_description"] = $line[2];
                $data["producent"] = $line[4];
                $data["categories"] = $categories;
                $data["product_type"] = "simple";
                $data["attribute_set_code"] = "Default";
                $data["price"] = $line[6];
                $data["additional_attributes"] = "disponibel=999,elasticsearchcore_ignore=No,fokus_pa_baerdygtighed=" . $omtanke . ",gavetype=,google_robots=Default,label=" . $kunhos . ",long_delivery=,producent=" . $line[4] . ",search_weight=0,udsalgspris=" . $line[3] . ",usalgspris_beregning=";
                $data["store_view_code"] = "default";
                $data["qty"] = "999";
                $data["product_websites"] = "base";
                $data["visibility"] = "Catalog, Search";
                $data["product_online"] = "1";
                $totalData[] = $data;
                //       echo  $line[1]."<hr><br><br>";  // debug line
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }
       // print_r($totalData);
        foreach ($totalData as $ele){
            echo implode(";", $ele)."\n";
        }

    }

    public function magentoImportDk()
    {
        $totalData = array();
        $filepath = __DIR__ . '/source_dk.csv';


        $lines = file($filepath);

        for($i = 0; $i < sizeof($lines); $i++) {


            try {

                $data = array();
                $lines[$i] = str_replace(array("\n", "\r"), '', $lines[$i]);

                $line = explode(";", $lines[$i]);

                $kunhos = $line[66] == "" ? "" : "kun shopGavefabrikken";
                $omtanke = $line[68] == "" ? "" : "Yes";
                $categories = "";// $this->CSVServiceCategory($line[35]);


                $data["sku"] = $line[1];
                $data["name"] = $line[15];
                $data["short_description"] = $line[22];
                $data["producent"] = $line[32];
                $data["categories"] = $categories;
                $data["product_type"] = "simple";
                $data["attribute_set_code"] = "Default";
                $data["price"] = $line[39];
                $data["additional_attributes"] = "disponibel=999,elasticsearchcore_ignore=No,fokus_pa_baerdygtighed=" . $omtanke . ",gavetype=,google_robots=Default,label=" . $kunhos . ",long_delivery=,producent=" . $line[26] . ",search_weight=0,udsalgspris=" . $line[50] . ",usalgspris_beregning=";
                $data["store_view_code"] = "default";
                $data["qty"] = "999";
                $data["product_websites"] = "base";
                $data["visibility"] = "Catalog, Search";
                $data["product_online"] = "1";
                $totalData[] = $data;
          //       echo  $line[1]."<hr><br><br>";  // debug line
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
        }

        foreach ($totalData as $ele){
            echo implode(";", $ele)."\n";
        }

    }


    private function CSVServiceCategory($string)
    {
// Splitter strengen ved semikolon
        $parts = explode(", ", $string);

// Sorterer arrayet så "GAVEIDEER" kommer først hvis den er til stede
        usort($parts, function($a, $b) {
            if ($a === "GAVEIDEER") return -1;
            if ($b === "GAVEIDEER") return 1;
            return 0;
        });

// Sammensætter delelementerne adskilt af "/"
        return count($parts) > 1 ? "Home/" . implode("/", $parts) : "Home/" . $parts[0];


    }


    public function imgCSVExport()
    {
        $filepathOutput = __DIR__ . '/output.csv';
        $filepath = __DIR__ . '/itemno.csv';
        $lines = file($filepath, FILE_IGNORE_NEW_LINES);  // læs alle linjer ind i en array
        for($i = 0; $i < count($lines); $i++) {


            $res = $this->csvLine($lines[$i]);
         /*
            if($res){
               $lines[$i] = $res;
            } else {
                continue;
            }
*/
        }
   //     file_put_contents($filepathOutput , implode("\n", $lines));


    }
    public function csvLine($itemno){
        $additional = [];
        $prime = "";
        $k = new KontainerCom;
        $result =$k->getDataOnItemnr($itemno);
        $result = json_decode($result);
        $img1 =  $result->data[0]->attributes->image_1->value ?? false ? $result->data[0]->attributes->image_1->value : "";
        $img2 =  $result->data[0]->attributes->image_2->value ?? false ? $result->data[0]->attributes->image_2->value : "";
        $img3 =  $result->data[0]->attributes->image_3->value ?? false ? $result->data[0]->attributes->image_3->value : "";
        $img4 =  $result->data[0]->attributes->image_4->value ?? false ? $result->data[0]->attributes->image_4->value : "";
        if($img1 == "") {
          return false;
        }
        if($img1 != ""){
            $imgObj = json_decode($this->getImgUrl($img1));
            $prime =  $imgObj->data->attributes->url;
        }
        if($img2 != ""){
            $imgObj = json_decode($this->getImgUrl($img2));
            $url =  $imgObj->data->attributes->url;
            array_push($additional,$url);
        }
        if($img3 != ""){
            $imgObj = json_decode($this->getImgUrl($img3));
            $url =  $imgObj->data->attributes->url;
            array_push($additional,$url);
        }
        if($img4 != ""){
            $imgObj = json_decode($this->getImgUrl($img4));
            $url =  $imgObj->data->attributes->url;
            array_push($additional,$url);
        }
        echo  $itemno.";".$prime.";".$prime.";".$prime.";".implode(",", $additional)."<br>";
    }

    public function model()
    {
       //$res = GavevalgPresentModel::updateChildItemnr(5810235,"SAM5018");
       // GavevalgPresentModel::updateSamItem(1111,"SAM5009");
        // var_dump($res."controller");
    }

    public function lookup()
    {
        $pimID = $_GET["pimid"];
        $k = new KontainerCom;
        $data = json_decode($k->getDataSingle("",$pimID));
        var_dump($data);
    }

    public function all()
    {
        die("asdf");
        $k = new KontainerCom;
        $result =$k->testf();
        print_R($result);
    }

    public function navImportToPim()
    {
        $navItemNo = $_POST["itemno"];
        $nav = new Nav;
        $retunData = $nav->createNewPimItem($navItemNo);
        $nav->removeFromQueue($navItemNo);
        echo json_encode(array("status" => 1,"total" => $retunData));
    }



    public function getNavList(){

        $itemno = trim($_POST["itemno"]);
        $nav = new Nav;
        $retunData = $nav->loadNewNAVItems($itemno);

        echo json_encode(array("status" => 1,"total" => $retunData));
    }
    public function doMergeNavElementToPim()
    {
        $nav = new Nav;
        $navItemNo = $_POST["navItemNo"];
        $kontainerID = $_POST["kontainerID"];
        $nav->doMergeNavElementToPim($navItemNo,$kontainerID);
        $nav->removeFromQueue($navItemNo);
    }

    public function navImportGetPimData()
    {
        $itemno = trim($_POST["itemno"]);
        $k = new KontainerCom;
        $result = json_decode($k->getDataOnItemnr($itemno));

        if(sizeof($result->data) == 0){
            echo json_encode(array("status" => 0));
        } else {
            $returnData = [];
            foreach ($result->data as $att){


            $kantainerID = $att->id;

            $group_product_nos = $att->attributes->group_product_nos->value ?? false ? $att->attributes->group_product_nos->value : "";
            $group_product_nos = str_replace("\r", "", $group_product_nos);
            $group_product_nos = str_replace("\n", ", ", $group_product_nos);
            //$modelList = explode("\n", $group_product_nos);

            $att = $att->attributes;
            $type = $att->product_type->value ?? false ? $att->product_type->value : "";
            if($type != "Product") { continue; }

            // erp
            $erp_product_name_da =  $att->erp_product_name_da->value ?? false ? $att->erp_product_name_da->value : "";
            $erp_product_name_en =  $att->erp_product_name_en->value ?? false ? $att->erp_product_name_en->value : "";
            $erp_product_name_no =  $att->erp_product_name_no->value ?? false ? $att->erp_product_name_no->value : "";
            $erp_product_name_se =  $att->erp_product_name_se->value ?? false ? $att->erp_product_name_se->value : "";

            // overskrift
            $product_name_da = $att->product_name_da->value ?? false ? $att->product_name_da->value : "";
            $product_name_en = $att->product_name_en->value ?? false ? $att->product_name_en->value : "";
            $product_name_no = $att->product_name_no->value ?? false ? $att->product_name_no->value : "";
            $product_name_se = $att->product_name_se->value ?? false ? $att->product_name_se->value : "";
            $returnDataTemp = array(
                "kontainerID" =>$kantainerID,
                "group_product_nos" => $group_product_nos,
                "erp"=>array(
                    "da" => $erp_product_name_da,
                    "en" => $erp_product_name_en,
                    "no" => $erp_product_name_no,
                    "se" => $erp_product_name_se
                ),
                "headline"=>array(
                    "da" => $product_name_da,
                    "en" => $product_name_en,
                    "no" => $product_name_no,
                    "se" => $product_name_se
                )
            );
                $returnData[] = $returnDataTemp;
            }
            echo json_encode(array("status" => 0,"data"=>$returnData));




        }

    }




// kørsel der opdatere alle priser
    public function runprice()
    {

        $p = new Price;
        $syncData = $p->syncNavAndGavevalg(4);
        print_R($syncData);
      $p->doSyncNavAndGavevalg($syncData);

    }


    public function asdkfjlfdh8uryreyt78erfifgy3i678()
    {
       $sql = " SELECT item_nr,pim_id,`type`,sync_dato,nav_name_da,  error,   error_msg,   sync_start,    sync_end,   is_handled   FROM `pim_sync_queue` ORDER BY `id` DESC limit 200";
       $res = \Dbsqli::getSql2($sql);

       echo "<div>PIM SYNC LOG version: 0.1 (Opdaterer hver 5. sekund, viser sidste 50 sync. varer) Opdateret: ".date("H:i:s", time())."</div><table border='1' cellpadding='5'><tr><th>Varenr</th><th>pim_id</th><th>NAV-da navn</th><th>type</th><th>Registeret i køen</th><th>error</th><th>Besked</th><th>sync_start</th><th>sync_end</th><th>Håndteret</th></tr>";
       for ($i=0;$i<sizeof($res);$i++){
           echo "<tr>
            <td>".$res[$i]["item_nr"]."</td>
            <td>".$res[$i]["pim_id"]."</td>
            <td>".$res[$i]["nav_name_da"]."</td>
            <td>".$res[$i]["type"]."</td>
            <td>".$res[$i]["sync_dato"]."</td>
            <td>".$res[$i]["error"]."</td>
            <td>".$res[$i]["error_msg"]."</td>
            <td>".$res[$i]["sync_start"]."</td>
            <td>".$res[$i]["sync_end"]."</td>
            <td>".$res[$i]["is_handled"]."</td>
            
            
            </tr>";
       }
        echo "</table><script>setInterval(function() {
  location.reload();
}, 5000); </script>";

    }


    public function testimg()
    {
        $gavevalg = new Gavevalg;
        $result = $gavevalg->doUpdateImg(390785, 5607399, 0);
        var_dump($result);
    }
    public function test3(){

        $token = 'b54w4c03eriqqn6y1v8rtwyoqcqairpn';
        $sku = 'Hoptimisttest1'; // Erstat med SKU for det produkt du ønsker at opdatere

        $productData = [
            'product' => [
                'sku' => 'SKU1234',
                'name' => 'Hoptimisttest1',
                'price' => 10,
                'status' => 1,

                'type_id' => 'simple',
            ]
        ];

        $requestUrl ='http://shopgavefabrikke.dev.magepartner.net/rest/V1/products/' . $sku;

        $ch = curl_init($requestUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);

        $result = curl_exec($ch);
        var_dump($result);
        if(curl_error($ch)){
            echo 'Error:', curl_error($ch);
        } else {
            echo "Product updated successfully.";
        }
    }

    public function test()
    {

        // Opret en ny vare
        $data = array(
            'product' => array(
                'sku' => 'SKU1234us',
                'name' => 'My Product1',
                'price' => 10,
                'status' => 1,
                'type_id' => 'simple'
            )
        );

        $token = 'b54w4c03eriqqn6y1v8rtwyoqcqairpn';
        $baseUrl = 'http://shopgavefabrikke.dev.magepartner.net';

        $ch = curl_init($baseUrl . '/rest/V1/products');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));

        $response = curl_exec($ch);
        curl_close($ch);

// Håndtering af svar
        if ($response) {
            $result = json_decode($response, true);
            if (isset($result['sku'])) {
                echo 'Varen med SKU ' . $result['sku'] . ' er blevet oprettet.';
            } else {
                echo 'Fejl ved oprettelse af varen: ' . $result['message'];
            }
        } else {
            echo 'Der opstod en fejl under anmodningen.';
        }
    }

    public function testPOST(){
        // Opret en ny vare
        $data = array(
            'product' => array(
                'sku' => 'SKUsss1234us',
                'name' => 'My Product1',
                'price' => 10,
                'status' => 1,
                'type_id' => 'simple'
            )
        );

        $token = 'b54w4c03eriqqn6y1v8rtwyoqcqairpn';
        $baseUrl = 'http://shopgavefabrikke.dev.magepartner.net';

        $ch = curl_init($baseUrl . '/rest/V1/products');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));

        $response = curl_exec($ch);
        if ($response === false) {
            echo 'cURL-fejl: ' . curl_error($ch);
        } else {
            $result = json_decode($response, true);
            if (isset($result['sku'])) {
                echo 'Varen med SKU ' . $result['sku'] . ' er blevet oprettet.';
            } else {
                // Sjekk om det er en feilmelding tilgjengelig i svaret
                $errorMessage = isset($result['message']) ? $result['message'] : 'Ukjent feil';
                echo 'Fejl ved oprettelse af varen: ' . $errorMessage;
            }
        }
        curl_close($ch);

    }

    public function testpost2()
    {
     
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://shopgavefabrikke.dev.magepartner.net/rest/store1/V1/products/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
  "product": {
    "sku": "birgitte10",
    "name": "birgitte10",
    "price":"22",
    "attribute_set_id": 4,
    "type_id": "simple",
    "extension_attributes": {
        "stock_item": {
            "qty": 20,
            "is_in_stock":true
        }
    },    
    "custom_attributes": [
      {
        "attribute_code": "short_description",
        "value": "<h1>det virker</h1>"
      },{
        "attribute_code": "description",
        "value": "<h1>det virkerdsafasdfasdf</h1>"
      },{
         "attribute_code": "producent",
        "value": 131
      },{
         "attribute_code": "category_ids",
        "value": [311,179]
      },{
            "attribute_code": "disponibel",
            "value": "40"
        },{
            "attribute_code": "ean",
            "value": "123"
        },{
            "attribute_code": "udsalgspris",
            "value": "100"
        },{
            "attribute_code": "fokus_pa_baerdygtighed",
            "value": 1
        },{
            "attribute_code":"Usalgspris_Beregning",
            "value":125
        },{
            "attribute_code":"label",
            "value":1
        },{
            "attribute_code": "gavetype",
            "value": "63,64,65"
        }
     
    ]
  }
}

',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer b54w4c03eriqqn6y1v8rtwyoqcqairpn',
                'Cookie: PHPSESSID=6uqjmdhii0si2fk2pu286hc84b'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    // --------------------------------------------------------





    }



public function test2()
{
    $token = 'your_token_here';
    $productData = [
        'product' => [
            'sku' => 'my_sku',
            'name' => 'My Product1',
            'attribute_set_id' => 4,
            'price' => 100,
            'status' => 1,
            'visibility' => 4,
            'type_id' => 'simple',
            'weight' => 1,
            'extension_attributes' => [
                'stock_item' => [
                    'qty' => '100',
                    'is_in_stock' => true,
                ],
                'category_links' => [
                    [
                        'position' => 0,
                        'category_id' => '3',
                    ],
                ],
            ],
        ],
        'saveOptions' => true,
    ];
    $productUrl = 'http://your_magento_url/rest/V1/products';
    $ch = curl_init($productUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);

    $result = curl_exec($ch);

// Decode the result
    $result = json_decode($result, 1);
    $productId = $result['id'];

    $imagePaths = ['path_to_image_1', 'path_to_image_2', 'path_to_image_3'];

    foreach ($imagePaths as $imagePath) {
        $imageData = [
            'entry' => [
                'media_type' => 'image',
                'label' => 'Product Image',
                'position' => 0,
                'disabled' => false,
                'types' => ['image', 'small_image', 'thumbnail'],
                'content' => [
                    'base64_encoded_data' => base64_encode(file_get_contents($imagePath)),
                    'type' => 'image/jpeg',
                    'name' => 'image.jpg',
                ],
            ],
        ];

        $imageUrl = 'http://your_magento_url/rest/V1/products/' . $productId . '/media';
        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($imageData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);

        $result = curl_exec($ch);
    }
}

    public function getSyncLog(){
        $dato = $_POST["dato"];

        $arrDato =   explode("-",$dato);
        $newDato = $arrDato[2]."-".$arrDato[0]."-".$arrDato[1];
        $rs = \Dbsqli::getSql2("SELECT *,  FROM_BASE64(CAST(`body` AS CHAR(10000) CHARACTER SET utf8)) as body2 FROM `pim_sync_queue` WHERE `created` > '2023-04-05'");
        echo json_encode($rs);
        //$kontainer = new kontainerCom;
       //$mapper = new Datamapping;
    }




    public function runSyncJobIkkerun(){
        die("end");
        $channelID = 17195;
        $newDato = "2023-04-18";
        $kontainer = new kontainerCom;

        $updateDate =  \PresentModel::find_by_sql("SELECT * FROM `pim_sync` WHERE `id` = 1");
        $syncDato = $updateDate[0]->attributes["sync_update"];


        $res = $kontainer->getData($channelID,"2023-04-20",100);
        $data = json_decode($res);

        for($i=0;$i<sizeof($data->data);$i++) {
            $itemnr =  $data->data[$i]->attributes->product_no->value ?? false ? $data->data[$i]->attributes->product_no->value : "";
          $itemSyncDato =  $data->data[$i]->attributes->updated_at->value;
            $itemSyncDato = date('Y-m-d H:i:s', strtotime($itemSyncDato. ' + 0 hours'));

          if($syncDato < $itemSyncDato){

              $id = $data->data[$i]->id;
            //  echo $syncDato . " ### ".$itemSyncDato ." ### @" .$itemnr. "@ <br>";
              echo $id."<br><br><br>";
              echo $syncDato . " ### ".$itemSyncDato ." ### @" .$itemnr. "@ <br>";
              $this->doSingleSync($id);


              $updateSync = "update pim_sync set sync_update=now() where id= 1 ";
              \Dbsqli::setSql2($updateSync);
          }
        }  // 2023-03-29 09:27:29
        $updateSync = "update pim_sync set sync_update=now() where id= 1 ";
        \Dbsqli::setSql2($updateSync);
        echo "done";
    }

    public function runSyncQueue()
    {
        $queue = \PresentModel::find_by_sql("SELECT * FROM `pim_sync_queue` WHERE is_handled = 0 limit 3");

        foreach ($queue as $qItem){
           $syncStart = date('Y-m-d H:i:s');
         \Dbsqli::setSql2("update pim_sync_queue set is_handled = 1, sync_start='$syncStart' where id=".$qItem->attributes["id"]);
            $res =  $this->doSingleSync($qItem->attributes["pim_id"]);
            $syncEnd = date('Y-m-d H:i:s');
           \Dbsqli::setSql2("update pim_sync_queue set sync_end='$syncEnd', error = ".$res["status"].",error_msg= '".json_encode($res["msg"])."'  where id=".$qItem->attributes["id"]);
        }
        echo "done";
    }


    public function syncAddQueue()
    {
        $channelID = 10614;
        $kontainer = new kontainerCom;


        $updateDate =  \PresentModel::find_by_sql("SELECT * FROM `pim_sync` WHERE `id` = 2");


        $syncDato = $updateDate[0]->attributes["sync_update"];
        $SyncDatoApi = date('Y-m-d', strtotime($syncDato. ' + 0 hours'));
        $syncDato = date('Y-m-d H:i:s', strtotime($syncDato. ' 0 hours'));
        $newSyncDato = $syncDato;


        //$SyncDatoApi = "2023-06-26T09:30:11.000000Z"; //$SyncDatoApi."T00:00:00Z";
        $res = $kontainer->getData($channelID,$SyncDatoApi,300);
        
        $data = json_decode($res);


        if($data == Null) return;
        for($i=0;$i<sizeof($data->data);$i++) {

                $itemnr = $data->data[$i]->attributes->product_no->value ?? false ? $data->data[$i]->attributes->product_no->value : "";
                $type = $data->data[$i]->attributes->product_type->value ?? false ? $data->data[$i]->attributes->product_type->value : "";
                $erp_product_name_da =  $data->data[$i]->attributes->erp_product_name_da->value ?? false ? $data->data[$i]->attributes->erp_product_name_da->value : "";
                $itemSyncDato = $data->data[$i]->attributes->updated_at->value;

                $itemSyncDato =  date('Y-m-d H:i:s', strtotime($itemSyncDato));
                $id = $data->data[$i]->id;
/*
                echo $id."<br>";
                echo $itemnr."<br>";
                echo $itemSyncDato."<br>";
                echo "<br>-------------------";
*/
                if($syncDato < $itemSyncDato) {
                        if($newSyncDato <= $itemSyncDato){
                            $newSyncDato = $itemSyncDato;
                        }

                      $jsonBody = base64_encode(json_encode($data->data[$i]));
                        $sql = "select * from pim_sync_queue where pim_id =".$id." and is_handled = 0";
                        $test =  \Dbsqli::getSql2($sql);

                            echo "<br>-----<br>";
                            echo $itemSyncDato;
                            echo "<br>";
                            echo $syncDato;
                            echo "<br>";
                            echo  $id."--id--";
                            echo "<br>-----<br>";

                            $erp_product_name_da = str_replace(array("'", "´"), "", $erp_product_name_da);
                           if($type == "Product"){
                               $sql = "insert into pim_sync_queue ( pim_id,nav_name_da,item_nr,type,sync_dato,body) values ($id,'$erp_product_name_da','$itemnr',1,'$itemSyncDato','$jsonBody')";
                           }
                           if($type == "Group"){
                               $sql = "insert into pim_sync_queue ( pim_id,nav_name_da,item_nr,type,sync_dato,body) values ($id,'$erp_product_name_da','',2,'$itemSyncDato','$jsonBody')";
                           }
                           \Dbsqli::setSql2($sql);
                            echo "<br>-- end ".$id."---<br>";

                }

        }
        echo "<br>new sync data".$newSyncDato;
        \Dbsqli::setSql2("update pim_sync set sync_update='".$newSyncDato."' where id= 2");
        sleep(2);

        $this->runSyncQueue();
        echo json_encode(["status"=>1]);
    }


    public function gavevalg()
    {


        $res =  \PresentModel::find_by_sql("SELECT present.id FROM `present`
inner JOIN
(SELECT COUNT(id) as antal, present_id FROM `present_model` WHERE `language_id` = 1
GROUP by present_id HAVING antal = 1) p on p.present_id = present.id

WHERE `active` = 1 and `deleted` = 0 and `copy_of` = 0 and `created_datetime` > '2023-02-01 08:42:56'");
// 88314 88332
        $masterData = array();
        foreach ($res as $pID){
            $tempArr = [];
            $ID = $pID->attributes["id"];
            // present
            $presentRes =  \PresentModel::find_by_sql("SELECT * FROM `present` WHERE `id` =". $ID);
            $tempArr["id"] = $ID;
            $tempArr["vendor"] = utf8_decode($presentRes[0]->attributes["vendor"]);
            $tempArr["kunhos"] = $presentRes[0]->attributes["kunhos"];
            $tempArr["omtanke"] = $presentRes[0]->attributes["omtanke"];
            $erp_product_name_se =  $att->erp_product_name_se->value ?? false ? $att->erp_product_name_se->value : "";
            if($presentRes[0]->attributes["pt_price"] ?? false ){
                $pt_price = json_decode($presentRes[0]->attributes["pt_price"]);
                $tempArr["Budget_priceDK"] =  $pt_price->pris;
                $tempArr["Vejl_Udsalgspris_tekstDK"] = $pt_price->budget;
            }
            if($presentRes[0]->attributes["pt_price_no"] ?? false ){
                $pt_price_no = json_decode($presentRes[0]->attributes["pt_price_no"]);
                $tempArr["Budget_priceNO"] =  $pt_price_no->pris;
                $tempArr["Vejl_Udsalgspris_tekstNO"] = $pt_price_no->budget;
            }




            $tempArr["show_to_saleperson"] = $presentRes[0]->attributes["show_to_saleperson"];
            $tempArr["nav_name_da"] = utf8_decode($presentRes[0]->attributes["nav_name"]);

            // model
            $modelRes = \PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE `present_id` =".$ID." and language_id != 3");
            foreach ($modelRes as $modelItem){
                $langID = $modelItem->attributes["language_id"];
                if($langID == 1) {
                    $tempArr["Product no"] = $modelItem->attributes["model_present_no"];
                }
            }
            // discription
            $discriptionRes = \PresentModel::find_by_sql("SELECT * FROM `present_description` WHERE `present_id` =".$ID." and language_id != 3");
            foreach ($discriptionRes as $disItem){
                $langID = $disItem->attributes["language_id"];
                $lang = "";
                if($langID == 1){  $lang="da"; }
                if($langID == 2){  $lang="en"; }
                if($langID == 4){  $lang="no"; }
                if($langID == 5){  $lang="sv"; }
                if($langID == 3) { continue; }
                // overskrift
                $tempArr["Product_name".$lang] = utf8_decode($disItem->attributes["caption"]);
                // beskrivelse
                $string = base64_decode($disItem->attributes["long_description"]);
                $tempArr["description_".$lang] = trim(preg_replace('/\s+/', ' ', $string));
                $tempArr["description_".$lang] =  utf8_decode(html_entity_decode($tempArr["description_".$lang] ));

            }
            $masterData[] = $tempArr;

        }

        $myfile = fopen("data.csv", "w") or die("Unable to open file!");
        $line = implode(";", array_keys($masterData[0]))."\n";

        fwrite($myfile, $line);

        foreach ($masterData as $ele){
            $line = implode(";", $ele)."\n";
            fwrite($myfile, $line);
        }
        fclose($myfile);

    }





    public function syncLogo()
    {
        $res = $this->getLogo();
        $data = json_decode($res);
        $this->updateID = time();



        // echo "\n".$imgID =  $data->data[5]->attributes->logo->value;
       $total = 0;
//
        for ($i=0;$i<sizeof($data->data);$i++)
        {
            if($data->data[$i]->attributes->logo ?? false) {
                try {
                    $total++;
                    $filename =  $data->data[$i]->attributes->logo->meta->file_name ?? false;
                    $fileSize =  $data->data[$i]->attributes->logo->meta->dimensions->fullscreen->size ?? false;
                    $this->saveLogo($data->data[$i]->attributes->logo->value,$filename,$fileSize);
                } catch (Exception $e) {
                    $status = 1; $msg = $e->getMessage();
                    $imgID = $data->data[$i]->attributes->logo->value;
                    $body = json_encode(array("logo_id"=>$imgID,"filename"=>$filename,"filesize"=>$fileSize));
                    \Dbsqli::setSql2("insert into pim_logo_log (logo_id,body,update_id,status,msg) values ($imgID,'$body',$this->updateID,$status,'$msg')");
                    echo '\nMessage: ' . $e->getMessage()."<br>";
                }
            }
        //    sleep(1);
        }
        echo json_encode(array("status" => 1,"total" => $total));

    }
    private function saveLogo($imgID,$filename,$fileSize){
        $status = 1;
        $msg = "error";
        // write to log

        // \Dbsqli::setSql2("insert into pim_logo_log (logo_id,body,update_id) values ($imgID,'$body',$this->updateID)");
        // check if img has to update

        $filename = urlencode($filename);
// $check = \Dbsqli::getSql2("select * from pim_logo where logo_id = $imgID  and file_name='$filename' and size= $fileSize");
        $check = \Dbsqli::getSql2("select * from pim_logo where logo_id = $imgID ");
        if(sizeof($check) > 0){
            // exists
            $updateCheck = \Dbsqli::getSql2("select * from pim_logo where logo_id = $imgID  and file_name='$filename' and size= $fileSize");
            if(sizeof($updateCheck) == 0){
                // do update
                $status = 0; $msg = "Update";
                \Dbsqli::setSql2("update pim_logo set file_name='$filename', size= $fileSize where logo_id =". $imgID);
                $this->doUploadLogo($imgID);
            } else {
                // no update
                $status = 0; $msg = "No change";
            }
        } else {
            // create new
            $status = 0; $msg = "Createt";
            $this->doUploadLogo($imgID);
            \Dbsqli::setSql2( "insert into pim_logo(logo_id,file_name,size) values ($imgID,'$filename',$fileSize)");
        }
        $body = json_encode(array("logo_id"=>$imgID,"filename"=>$filename,"filesize"=>$fileSize));
        \Dbsqli::setSql2("insert into pim_logo_log (logo_id,body,update_id,status,msg) values ($imgID,'$body',$this->updateID,$status,'$msg')");
        // write to log


        /*

        */
    }
    private function doUploadLogo($imgID)
    {
        try {
            $imgRes = $this->getImgUrl($imgID);
            $imgObj = json_decode($imgRes);
            $imgSource =  $imgObj->data->attributes->url;
            $content = file_get_contents($imgSource);
            //Store in the filesystem.
            $path = "klogo_".$imgID.".jpg";

            $fp = fopen("./views/media/logo/".$path, "w");
            fwrite($fp, $content);
            fclose($fp);
            //echo "\nhttps://presentation.gavefabrikken.dk/gavefabrikken_backend/views/media/logo/".$path;
        }
        catch(Exception $e) {
            $msg = $e->getMessage();
            \Dbsqli::setSql2("insert into pim_logo_log (logo_id,update_id,status,msg) values ($imgID,$this->updateID,2,'$msg')");
        }
    }

    private function getLogo(){
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/17196/items?page[size]=1&filter[released_on][gt]=2023-02-19');
        $today = date('Y-m-d');



        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10615/items?filter[updated_at][gt]='.$today.'%2000:00:11');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    public function sync(){


        $kontainer = new kontainerCom;
        $kontainer->test();

        die("asdfasdf");

        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/17196/items?page[size]=1&filter[released_on][gt]=2023-02-19');
        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/17195/items?page[size]=5&filter[updated_at][gt]=2022-10-20');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);


        $this->updatesync($result);

    }
    public function doSingleSync($id){

        $kontainer = new kontainerCom;
        $gavevalg  = new Gavevalg;
        $res = $kontainer->getDataSingle(17195,$id);
        $result = $gavevalg->singleSync($res,$id);
        return $result;

        //$mapperData = $mapper->channelsItems($res);
        //var_dump($mapperData);
        //$res = $kontainer->getData(17195,"2023-03-11",100);


        //$mapperData = $mapper->channelsItems($res);
        //echo $dashboard->syncOutput($mapperData);


        //echo $id;
    }


    private function updatesync($data)
    {
        $data = json_decode($data);

        for($i=1;$i<sizeof($data->data);$i++){
            if($i > 1) return;
            $itemnr =  $data->data[$i]->attributes->product_no->value ?? false ? $data->data[$i]->attributes->product_no->value : "";
            $product_name_da = $data->data[$i]->attributes->product_name_da->value ?? false ? $data->data[$i]->attributes->product_name_da->value : "";
            $description_da =  $data->data[$i]->attributes->description_da->value ?? false ? $data->data[$i]->attributes->description_da->value : "";
            $short_description_da =  $data->data[$i]->attributes->short_description_da->value ?? false ? $data->data[$i]->attributes->short_description_da->value : "";
            $erpname =  $data->data[$i]->attributes->erp_product_name->value ?? false ? $data->data[$i]->attributes->erp_product_name->value : "";

            $sql = "select id from present where pim_id = ".$data->data[$i]->id;
            $res =  \PresentModel::find_by_sql($sql);
            $targetID = $res[0]->attributes["id"];


            $img =  $data->data[$i]->attributes->image_1->value ?? false ? $data->data[$i]->attributes->image_1->value : "";
            if($img != ""){
                $obj = $this->getImgUrl($img);
                $imgJ = json_decode($obj);
                $img =  $imgJ->data->attributes->url;

                //Get the file
                $content = file_get_contents($img);
                //Store in the filesystem.
                $randomnr = $this->generateRandomString();
                $path = $randomnr.".jpg";
                $fp = fopen("./views/media/user/".$path, "w");
                fwrite($fp, $content);
                fclose($fp);
                \Dbsqli::setSql2("UPDATE `present_media` SET `media_path` = '".$randomnr."' WHERE `present_media`.`present_id` = ".$targetID." and `index` = 0");
            }


            \Dbsqli::setSql2("update present set nav_name = '".$erpname."' where pim_id = ".$targetID);
            \Dbsqli::setSql2("UPDATE `present_description` SET `long_description` = '".base64_encode($description_da)."', short_description = '".base64_encode($short_description_da)."', caption= '".$product_name_da."'  WHERE language_id = 1  AND `present_description`.`present_id` =".$targetID);
            \Dbsqli::setSql2( "UPDATE `present_model` SET `model_present_no` = '".$itemnr."'  WHERE `present_id` = ". $targetID);
                echo "Sync done";




        }
    }
    private function generateRandomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getSyncList(){

        $dato = $_POST["dato"];
        $channelID = $_POST["grupppe"] == 1 ? 17196 : 17195;
        $arrDato =   explode("-",$dato);
        $newDato = $arrDato[2]."-".$arrDato[0]."-".$arrDato[1];

        $kontainer = new kontainerCom;
        $mapper = new Datamapping;
        $dashboard = new Dashboard;
        $res = $kontainer->getData($channelID,$newDato,100);
        $mapperData = $mapper->channelsItems($res);
        echo $dashboard->syncOutput($mapperData);



/*
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/17196/items?page[size]=1&filter[released_on][gt]=2023-02-19');
        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/17195/items?page[size]=5&filter[updated_at][gt]=2022-10-20');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        echo $this->processOutput($result);
*/
    }
    private function processOutput($res){
        $data = json_decode($res);
        $html = $img = "";

        for($i=0;$i<sizeof($data->data);$i++){

            $itemnr =  $data->data[$i]->attributes->product_no->value ?? false ? $data->data[$i]->attributes->product_no->value : "";

            $product_name_da = $data->data[$i]->attributes->product_name_da->value ?? false ? $data->data[$i]->attributes->product_name_da->value : "";
            $description_da =  $data->data[$i]->attributes->description_da->value ?? false ? $data->data[$i]->attributes->description_da->value : "";
            $short_description_da =  $data->data[$i]->attributes->short_description_da->value ?? false ? $data->data[$i]->attributes->short_description_da->value : "";
            $itemnr =  $data->data[$i]->attributes->product_no->value ?? false ? $data->data[$i]->attributes->product_no->value : "";


            $erpname =  $data->data[$i]->attributes->erp_product_name->value ?? false ? $data->data[$i]->attributes->erp_product_name->value : "";
            $img =  $data->data[$i]->attributes->image_1->value ?? false ? $data->data[$i]->attributes->image_1->value : "";
            if($img != ""){
               $obj = $this->getImgUrl($img);
               $imgJ = json_decode($obj);
               $img =  $imgJ->data->attributes->url;
               $img = "<img width='150px' src='".$img."'  />";
            }

            //  echo $data->data[$i]->attributes->updated_at->value;

           // echo $data->data[$i]->attributes->product_no->value;
           $html.="<table  >
                <tr><td width='100'>Img</td><td>".$img."</td></tr>
                <tr><td width='100'>Varenr</td><td>".$itemnr."</td></tr>
                <tr><td width='100'>ERP navn</td><td>".$erpname."</td></tr>
                <tr><td width='100'>Overskrift</td><td>".$product_name_da."</td></tr>
                <tr><td width='100'>Kort beskrivelse</td><td>".$short_description_da."</td></tr>
                <tr><td width='100'>Lang beskrivelse</td><td>".$description_da."</td></tr>

                </table>";
        }
        echo $html."<hr>";





        //echo $data->data[0]->attributes->updated_at->value;
        //echo $data->data[0]->attributes->product_no->value;
        //echo $data->data[0]->id;
        //echo $data->data[0]->attributes->description_da->value;
        //$data->data[0]->attributes->erp_product_name->value;
        //$data->data[0]->attributes->short_description_da->value;
        //print_r($data->data[0]->attributes->storeview);
        // $data->data[0]->attributes->supplier->value;

    }
    private function getImgUrl($id){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/dam/files/'.$id.'/cdn');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"data\": {\n    \"type\": \"cdn\"\n  }\n}");

        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'Content-Type: application/vnd.api+json';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

}
