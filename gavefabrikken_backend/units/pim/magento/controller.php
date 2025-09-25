<?php

namespace GFUnit\pim\magento;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
set_time_limit(100);
error_reporting(E_ALL);
use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;
use GFUnit\pim\magento\MagentoApi;
class Controller extends UnitController
{
    private $token = '48s1pd9cstnutyyl1ykncacwvtkn8sf9';
    private $baseUrl = 'https://gavefabrikken.dk';
    private $kunhos = '115';
    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public function mapSuppliers()
    {
        $pimSuppliersData = $this->pimSuppliersData;


        foreach ($this->magentoSuppliersData as $magentoSuppliersData){

            $maglabel =  $magentoSuppliersData["label"];
            $magvalue =  $magentoSuppliersData["value"];
            foreach ($pimSuppliersData as $key => $val){
                if( trim(strtolower(($val))) == trim(strtolower( $maglabel)) ){
                    echo $val."<br>";
                }
            }

        }
   }


    public function test()
    {




        $pimID =  $_POST["PIMID"];
        $ONLINE = $_POST["ONLINE"];
        $PRICE = $_POST["PRICE"];
        $country = $_POST["country"];
        $storeview = $_POST["storeview"];
        $error = true;
        $kon = new KontainerCom;
        $konData = $kon->getDataSingle("",$pimID);
        $konData = json_decode($konData);

        $res =  $this->mapPostfields($storeview, $konData->data->attributes, $kon, $ONLINE, $PRICE, $country);
        $data = json_decode($res, true);
        if (!isset($data['id']) || empty($data['id']) ||
            !isset($data['sku']) || empty($data['sku'])) {
            $error = true;
        }

        return $error;
    }

    public function mapPostfields($deployStoreView,$konData,$kon,$online,$showPrice,$country){
      //  $showPrice = true;


        $storeview = $deployStoreView;
        $price = "";


        $status = $online == 1 ? 1:2;

        // tjekker om varenr er der
         $itemno = $konData->product_no->value ?? false ? $konData->product_no->value : "";

        // henter data fra magento
        $productData = $this->getItem($itemno);
        $productData = json_decode($productData, true);
        $website_ids = $productData['extension_attributes']['website_ids'];


        $categoryIds = [];

        // Check if category_ids are available in custom_attributes
        if (isset($productData['custom_attributes'])) {
            foreach ($productData['custom_attributes'] as $attribute) {
                if ($attribute['attribute_code'] === 'category_ids') {
                    $categoryIds = array_merge($categoryIds, $attribute['value']);
                }
            }
        }

        if($country == 5){
            $noCategoryIds = [643,644,645,646];
            $categoryListPresent = array_diff($categoryIds, $noCategoryIds);

            $storeview = $deployStoreView;
            $product_name_da = $konData->product_name_sv->value ?? false ? $konData->product_name_sv->value : "";
            $description_da =  $konData->description_sv->value ?? false ?  $konData->description_sv->value : "";

            $price = $konData->budget_price_sv[0]->value ?? "" ;

            $vejlPrice = $konData->vejl_udsalgspris_tekst_sv->value;
            $vejlPrice = $vejlPrice <= 0 ? "" : $vejlPrice;


            $categoryID = $this->getCategoryIDSE(440);

            if($categoryID){
                $categoryList[] = $categoryID === false ? [] : $categoryID;
            }
            $categoryList[] = 471; // tilføjer web

            $categoryList = array_merge($categoryList, $categoryListPresent);
            $categoryList = array_unique($categoryList);
            $gave_med_omtanke_da =  $konData->gave_med_omtanke_no->value ?? false ? 1 : 0;
            $kun_hos_gavefabrikken_da = $konData->kun_hos_gavefabrikken_no->value ?? false ? $this->kunhos : 0;
        }

        if($country == 4){
            $noCategoryIds = [529,630,631,632,633,636,634,635,471];
            $categoryListPresent = array_diff($categoryIds, $noCategoryIds);

            $storeview = $deployStoreView;
            $product_name_da = $konData->product_name_no->value ?? false ? $konData->product_name_no->value : "";
            $description_da =  $konData->description_no->value ?? false ?  $konData->description_no->value : "";
            if($storeview == "Gaveklubben"){
                //  $description_da = str_replace("Gaven bes", "Produktet indeholder", $description_da);
            }
            //$stock = $this->getNAVStock($itemno);
            //$is_in_stock =  $stock > 4 ? true:false;
            // salgsprisern
            $price = $this->getNAVPrice($itemno,4,$storeview);
            // Vejl pris
            $navItemPrices = $this->getNavItemPrice($itemno,4);
            $navItemPrices["unit_price"] = $price;

            $vejlPrice = $navItemPrices["vejl_pris"];
            $vejlPrice = $vejlPrice <= 0 ? "" : $vejlPrice;

            if((int)$price < 200){
                $navItemPrices["unit_price"] = 199;
            }
           $categoryID = $this->getCategoryIDNo($navItemPrices["unit_price"]);
           if($categoryID){
                $categoryList[] = $categoryID === false ? [] : $categoryID;
           }
            $categoryList[] = 471; // tilføjer web

            $categoryList = array_merge($categoryList, $categoryListPresent);
            $categoryList = array_unique($categoryList);
            $gave_med_omtanke_da =  $konData->gave_med_omtanke_no->value ?? false ? 1 : 0;
            $kun_hos_gavefabrikken_da = $konData->kun_hos_gavefabrikken_no->value ?? false ? $this->kunhos : 0;
        }



        if($country == 1){
            $dkCategoryIds = [523,637,521,513,514,515,524,594,516,543,601];
            $categoryListPresent = array_diff($categoryIds, $dkCategoryIds);
            $product_name_da = $konData->product_name_da->value ?? false ? $konData->product_name_da->value : "";
            $description_da =  $konData->description_da->value ?? false ?  $konData->description_da->value : "";
            if($storeview == "Gaveklubben"){
                $description_da = str_replace("Gaven bes", "Produktet indeholder", $description_da);
            }
            $stock = 998;
            $is_in_stock = true;
            $status = 1;

            $price = $this->getNAVPrice($itemno,1,$storeview);
            $navItemPrices = $this->getNavItemPrice($itemno,1);
            $vejlPrice = $navItemPrices["vejl_pris"];
            $vejlPrice = $vejlPrice <= 0 ? "" : $vejlPrice;
            $categoryID = $this->getCategoryID($navItemPrices["unit_price"]);
            if($categoryID){
                $categoryList[] = $categoryID === false ? [] : $categoryID;
            }
            $categoryList[] = 471; // tilføjer web
            $categoryList = array_merge($categoryList, $categoryListPresent);
            $categoryList = array_unique($categoryList);
            $gave_med_omtanke_da =  $konData->gave_med_omtanke_da->value ?? false ? 1 : 0;
            $kun_hos_gavefabrikken_da = $konData->kun_hos_gavefabrikken_da->value ?? false ? $this->kunhos : 0;
        }



        $stock = 998;
        $is_in_stock = true;
        $status = 1;




        $img1 =  $konData->image_1->value ?? false ? $konData->image_1->value : "";
        if($img1 != ""){
            $obj = $kon->getImgUrl($img1);
            $imgJ = json_decode($obj);
            $imagePaths[] =$imgJ->data->attributes->url;
        }

        $img2 =  $konData->image_2->value ?? false ? $konData->image_2->value : "";
        if($img2 != ""){
            $obj = $kon->getImgUrl($img2);
            $imgJ = json_decode($obj);
            $imagePaths[] = $imgJ->data->attributes->url;
        }

        $logo =  $konData->logo->value ?? false ? $konData->logo->value : "";
        if($logo != ""){
            $id =  $konData->logo->meta->resource_item_id ?? false ? $konData->logo->meta->resource_item_id : "";
            $resLogo = $this->getKontainerLogoID($id);


            $logoData = json_decode($resLogo);

            $logoKontainerID = $logoData->data->attributes->logo->value ?? false ? $logoData->data->attributes->logo->value : 0;

            if($logoKontainerID != 0){
                $obj = $kon->getImgUrl($logoKontainerID);
                $imgJ = json_decode($obj);
                $imagePaths[]  =  $imgJ->data->attributes->url;
            }
        }

        $postData  = [
            'product' => [
                'sku' => $itemno,
                'name' => $product_name_da,
                'status'=>$status,
                'visibility'=>4,
                'attribute_set_id' => 4,
                'type_id' => 'simple',
                'extension_attributes' => [
                    'stock_item' => [
                        'qty' => $stock,
                        'is_in_stock' => $is_in_stock,
                    ],
                ],
                'custom_attributes' => [
                    [
                        'attribute_code' => 'short_description',
                        'value' => $description_da,
                    ],
                    [
                        'attribute_code' => 'description',
                        'value' => "",
                    ],
                    [
                        'attribute_code' => 'disponibel',
                        'value' => $stock,
                    ],
                    [
                        'attribute_code' => 'udsalgspris',
                        'value' => $vejlPrice,
                    ],

                    [
                        'attribute_code'=> 'category_ids',
                        'value' => $categoryList
                    ],
                    [
                        'attribute_code' => 'fokus_pa_baerdygtighed',
                        'value' => $gave_med_omtanke_da,
                    ],

                    [
                        'attribute_code' => 'label',
                        'value' => $kun_hos_gavefabrikken_da,
                    ],
                    [
                        'attribute_code' => 'gavetype',
                        'value' => '',
                    ],
                ],
            ],
        ];


/*
                     [
                        'attribute_code'=> 'category_ids',
                        'value' => $categoryList
                    ],
 */

        if($showPrice){
        //    $postData["product"]["price"] =  $price;
        }

        if($storeview == "gavefabrikken_se_store_view"){

            $postData["product"]["price"] =  $price;
        }

        if($storeview == "gk_no_store_view"){
         /*
            $postData['product']['custom_attributes'][] = array(
                "attribute_code"=> "usalgspris_beregning",
                "value"=> "4.000000"


            );
         */
        }

        $syncRes = $this->testpost2($postData,$deployStoreView);
        $MagentoApi = new magentoapi();

        $MagentoApi::resetImageAttributes($itemno);
        $MagentoApi::deleteAllProductImages($itemno);
        $this->addImg($itemno,$imagePaths,"all");
        $MagentoApi::setMainImageAttributes($itemno);

        $list = $this->getActiveWebsiteIds($itemno);


        $result =  $this->updateWebsiteIds($itemno,$list,$storeview);



       return "sync done";
    }


    private function addImg($sku,$imagePaths,$storeView){
        try {
            $MagentoApi = new magentoapi();
            $results = $MagentoApi::uploadProductImages($sku, $imagePaths, $storeView);

            foreach ($results as $result) {
                if (isset($result['error'])) {
                    echo "Error uploading image at position {$result['position']}: {$result['error']}\n";
                } else {
                    echo "Successfully uploaded image at position {$result['position']}\n";
                }
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
    public function updateWebsiteIds($sku,$storeViewsArray,$storeview){
        $websiteIds = [];
        $MagentoApi = new magentoapi();
        // Extract website_id from the array
        foreach ($storeViewsArray as $storeView) {
            if (isset($storeView['website_id'])) {
                $websiteIds[] = (int)$storeView['website_id'];
            }
        }
        // sikre at gavefabrikken_se_store_view sættes aktiv
        if($storeview == "gavefabrikken_se_store_view") {
            $websiteIds[] = 54;
        }


        if (!in_array(0, $websiteIds)) {
            $websiteIds = array_unique(array_merge([0], $websiteIds));
            sort($websiteIds);
        }
        $result =$MagentoApi::updateProductWebsiteIds($sku, $websiteIds);
        return $result;
    }
    public  function getActiveWebsiteIds($sku)
    {
        try {
            $MagentoApi = new magentoapi();

                $product = $MagentoApi::getProductDetails($sku);

                if (!isset($product['extension_attributes']['website_ids'])) {
                    return [['message' => 'No website IDs found for the product.']];
                }
                $productWebsiteIds = $product['extension_attributes']['website_ids'];

                // Get all store views
                $allStoreViews = $MagentoApi::getAllStoreViews();

                // Filter store views based on product's website IDs
                $relevantStoreViews = array_filter($allStoreViews, function($store) use ($productWebsiteIds) {
                    return in_array($store['website_id'], $productWebsiteIds);
                });
                return $relevantStoreViews;
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }

    }

    private function createImg($itemno,$imagePaths,$deployStoreView)
    {
        $pos = 0;

        foreach ($imagePaths as $imagePath) {

            $type = $pos == 0 ? ['image', 'small_image', 'thumbnail'] : [];
            /*
            $base64Image = base64_encode(file_get_contents($imagePath));
            echo '<img src="data:image/jpeg;base64,' . $base64Image . '" />';
            */

            $name = $this->generateRandomString(10).".jpg";
            $imageData = [
                'entry' => [
                    'media_type' => 'image',
                    'label' => 'Product Image',
                    'position' => $pos,
                    'disabled' => false,
                    'types' => $type,
                    'content' => [
                        'base64_encoded_data' => base64_encode(file_get_contents($imagePath)),
                        'type' => 'image/jpeg',
                        'name' => $name,
                    ],
                ],
            ];
            $pos++;
            $imageUrl = 'https://gavefabrikken.dk/rest/'.$deployStoreView.'/V1/products/' . $itemno . '/media';
            $ch = curl_init($imageUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($imageData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $this->token]);

            $result = curl_exec($ch);
            curl_close($ch);






        }
    }



    private function getItem($sku)
    {
        $productUrl = 'https://gavefabrikken.dk/rest/V1/products/' . $sku;



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
    
    
    
    
    private function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function removeAllMagentoImg($itemno,$storeview){
        $imageUrl = 'https://gavefabrikken.dk/rest/'.$storeview.'/V1/products/' . $itemno . '/media';
        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $this->token]);


        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            $errorCode = curl_errno($ch);
            curl_close($ch);
            die('cURL error (' . $errorCode . '): ' . $error);
        }
        curl_close($ch);
        $images = json_decode($response, true);
        if (array_key_exists('message', $images)) {
            return;
        }
        curl_close($ch);

        foreach ($images as $image) {

            $entryId = $image['id'];
            $imageUrl = 'https://gavefabrikken.dk/rest/'.$storeview.'/V1/products/' . $itemno . '/media/' .$entryId;

            $ch = curl_init($imageUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $this->token]);
            $response = curl_exec($ch);
            curl_close($ch);

        }


    }
/* --------------------- unset */
    public function unsetImgAtt($sku, $storeId)
    {
        $imageTypes = ['image', 'small_image', 'thumbnail', 'swatch_image'];
        $accessToken = $this->token;
        $baseUrl = $this->baseUrl;

        // Hent produktinformation for det specificerede store view
        $productInfo = $this->getProductInfo($sku, $baseUrl, $accessToken, $storeId);

        if (!$productInfo) {
            echo "Failed to get product info for SKU $sku in store view $storeId\n";
            return;
        }



        $attributesToUnset = [];
        foreach ($imageTypes as $imageType) {
            if ($this->hasImageAttribute($productInfo, $imageType)) {
                $attributesToUnset[] = $imageType;
            }
        }

        if (!empty($attributesToUnset)) {
            if ($this->unsetAttributes($sku, $attributesToUnset, $baseUrl, $accessToken, $storeId)) {
                echo "Unset " . implode(', ', $attributesToUnset) . " for SKU $sku in store view $storeId\n";
            } else {
                echo "Failed to unset attributes for SKU $sku in store view $storeId\n";
            }
        } else {
            echo "No image attributes to unset for SKU $sku in store view $storeId\n";
        }
    }

    private function getProductInfo($sku, $baseUrl, $accessToken, $storeId) {
        $ch = curl_init("$baseUrl/rest/V1/products/$sku?fields=custom_attributes&store_id=$storeId");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function hasImageAttribute($productInfo, $imageType) {
        foreach ($productInfo['custom_attributes'] as $attribute) {
            if ($attribute['attribute_code'] === $imageType && !empty($attribute['value'])) {
                return true;
            }
        }
        return false;
    }

    private function unsetAttributes($sku, $attributes, $baseUrl, $accessToken, $storeId) {
        $data = [
            'product' => [
                'custom_attributes' => array_map(function($attr) {
                    return ['attribute_code' => $attr, 'value' => ''];
                }, $attributes)
            ]
        ];

        $ch = curl_init("$baseUrl/rest/V1/products/$sku");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json",
            "Store: $storeId"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
    /* --------------------- unset end */

    /* opdater først billede atribut */

    private function updateProductImageAttributes($sku, $storeCode, $baseUrl, $accessToken,$reset=false)
    {
        // gavefabrikken_no_store_view

       $getMediaEndpoint = "/rest/{$storeCode}/V1/products/{$sku}/media";

        $imgID = 0;

        $imageTypes = ['image', 'small_image', 'thumbnail'];
        if($reset) {
            $imageTypes = [];
        }
        // Initialiser cURL for at hente billede 'file' attributten
        $ch = curl_init();

        // Konfigurer cURL
        curl_setopt($ch, CURLOPT_URL, $baseUrl . $getMediaEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $accessToken,
        ]);

        // Udfør cURL-kaldet og fang responsen
        $response = curl_exec($ch);

        // Tjek for fejl
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }
     //   var_dump($response);
        if ($response === false || trim($response) === "[]" || empty($response)) {
            return;
        }


        // Parse JSON respons
        $mediaEntries = json_decode($response, true);

        // Luk cURL forbindelsen
        curl_close($ch);

        if (isset($mediaEntries['message'])) {
            $mediaEntries = [];
        }


        // Find 'file' attributten for et billede
        $file = null;
        if (is_array($mediaEntries) && count($mediaEntries) > 0) {
            // Tjek om der findes et medie på position 0 og at det er af typen 'image'
            $firstEntry = $mediaEntries[0];

            if (isset($firstEntry['media_type']) && $firstEntry['media_type'] == 'image') {
                $file = $firstEntry['file'];
                $imgID = $firstEntry['id'];
            } else {
                // Hvis det første medie ikke er af typen 'image', kast en fejl
                throw new Exception("Første medie er ikke af typen 'image'.");
            }
        } else {
            // Hvis der ikke er nogle medieoptegnelser, kast en fejl
            return null;
        }
        if (!$file) {
            echo "Ingen billeder fundet for produktet.";
            return null;
        }

        // Data for at opdatere billedattributterne
        $data = [
            'entry' => [
                'id'=>$imgID,
                'media_type' => 'image',
                'label' => 'Updated Image Label', // Optional: Ændre billedets label
                'position' => 0, // Optional: Position på produktbilledlisten
                'disabled' => false, // Optional: Om billedet er deaktiveret
                'types' => $imageTypes, // Aktiver billedtyperne (image, small_image, thumbnail)
                'file' => $file, // Brug den hentede filsti
            ],
        ];


        // Konverter data til JSON
        $jsonData = json_encode($data);

        // Initialiser cURL for at opdatere billedattributterne
        $ch = curl_init();

        // Konfigurer cURL
        $endpoint = $baseUrl .$getMediaEndpoint."/".$imgID;


        curl_setopt($ch, CURLOPT_URL,$endpoint );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $accessToken,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Udfør cURL-kaldet og fang responsen
        $response = curl_exec($ch);

        // Tjek for fejl
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            // Parse JSON respons
            $responseData = json_decode($response, true);

        }

        // Luk cURL forbindelsen
        curl_close($ch);
    }




// Hjælpefunktion til at hente produktinformation

    /* opdater først billede atribut */
    public function getNAVStock($itemno)
    {
        $rs = \Dbsqli::getSql2("select * from magento_stock_total where itemno ='".$itemno."'");
        return sizeof($rs) == 0 ? 0 : $rs[0]["quantity"];


    }
    public function getNAVPrice($itemno,$language_id,$storeView)
    {
        if($storeView == "Gaveklubben"){
            $rs = \Dbsqli::getSql2("SELECT unit_price as vejl_pris FROM `navision_salesprice` WHERE language_id = $language_id  and `item_no` LIKE '$itemno' and sales_code = 'GAVEKLUB' and ISNULL(`deleted`) ORDER BY `navision_salesprice`.`starting_date` DESC limit 1 ");
            return sizeof($rs) == 0 ? 0 : $rs[0]["vejl_pris"];
        }
        if($storeView == "Shopgavefabrikken"){
            $rs = \Dbsqli::getSql2("SELECT unit_price as vejl_pris FROM `navision_salesprice` WHERE language_id = $language_id  and `item_no` LIKE '$itemno' and sales_code = 'GAVESPEC'  and ISNULL(`deleted`) ORDER BY `navision_salesprice`.`starting_date` DESC limit 1 ");
            return sizeof($rs) == 0 ? 0 : $rs[0]["vejl_pris"];
        }
        if($storeView == "gavefabrikken_no_store_view"){
            $rs = \Dbsqli::getSql2("SELECT unit_price as vejl_pris FROM `navision_salesprice` WHERE language_id = $language_id  and `item_no` LIKE '$itemno' and sales_code = 'GAVESPECNO' and ISNULL(`deleted`) ORDER BY `navision_salesprice`.`starting_date` DESC limit 1 ");
            return sizeof($rs) == 0 ? 0 : $rs[0]["vejl_pris"];
        }
        if($storeView == "gk_no_store_view"){
            $rs = \Dbsqli::getSql2("SELECT unit_price as vejl_pris FROM `navision_salesprice` WHERE language_id = $language_id  and `item_no` LIKE '$itemno' and sales_code = 'GAVEKLUBNO' and ISNULL(`deleted`) ORDER BY `navision_salesprice`.`starting_date` DESC limit 1 ");
            return sizeof($rs) == 0 ? 0 : $rs[0]["vejl_pris"];
        }
    }
    private function getNavItemPrice($itemno,$language_id){

        $rs = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE language_id = $language_id  and `no` LIKE '$itemno'");
        if (empty($rs)) {
            return array("vejl_pris" => 0, "unit_price" => 0);
        }

        $vejl_pris = $rs[0]["vejl_pris"];
        $unit_price = $rs[0]["unit_price"];

        return array("vejl_pris" => $vejl_pris, "unit_price" => $unit_price);

    }

    private function getCategoryIndex(array $list){
        return \Dbsqli::getSql2("SELECT * FROM `magento_pim_category_index` WHERE pim_index in( implode(",", $list)  ) ");

    }
    private function getSuppliersIndex($id){
        return \Dbsqli::getSql2("SELECT * FROM `magento_pim_suppliers_index` WHERE pim_index = $id");
    }
        //$deployStoreView = "default";
    //CURLOPT_URL => 'http://shopgavefabrikke.dev.magepartner.net/rest/store1/V1/products/',
    public function testpost2($postData,$deployStoreView)
    {

/*
        $postData = [
            'product' => [
                'sku' => 'KA-030',
                'name' => 'Funktionel KA30 kopkasse fra Kath & Andersen',
                'price' => 500,
                // Andre attributter specifikt for website 0
            ]
        ];
*/
//        $this->updateProductWebsite0($postData);


      echo  $url = 'https://gavefabrikken.dk/rest/'.$deployStoreView.'/V1/products/';

        // Sikrer at produktet oprettes på alle websites (0) og website ID 1
        /*
        if (!isset($postData['product']['extension_attributes']['website_ids'])) {
            $postData['product']['extension_attributes']['website_ids'] = [0, 1];  // 0 betyder alle websites, 1 er det specifikke website ID
        }
*/
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer 48s1pd9cstnutyyl1ykncacwvtkn8sf9',
                'Cookie: PHPSESSID=6uqjmdhii0si2fk2pu286hc84b'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }
        curl_close($curl);

        var_dump($response);
        return $response;
    }
    /* ----------------------------------------------------------------  */



    public function updateProductWebsite0($postData) {
        $sku = $postData['product']['sku'];



        // Hent eksisterende produktdata
        $existingProduct = $this->getExistingProduct($sku);

        if (!$existingProduct) {

            // Hvis produktet ikke eksisterer, opret det med data for website 0, 1 og 2
            $websiteIds = $postData['product']['extension_attributes']['website_ids'] ?? [];
            $websiteIds = array_unique(array_merge($websiteIds, [0]));
            $postData['product']['extension_attributes']['website_ids'] = $websiteIds;

            //return $this->sendApiRequest($this->baseUrl, 'POST', $postData);
        }

        // Bevar eksisterende website_ids
        $existingWebsiteIds = $existingProduct['extension_attributes']['website_ids'] ?? [];

        // Tilføj website 0, 1 og 2, hvis de ikke allerede er der
        $updatedWebsiteIds = array_unique(array_merge($existingWebsiteIds, [0,1,17,32]));

        // Forbered data specifikt for website 0
        $website0Data = $postData['product'];
        $website0Data['extension_attributes']['website_ids'] = $updatedWebsiteIds;

        // Opdater kun attributter, der er specificeret i $postData
        foreach ($existingProduct as $key => $value) {
            if (!isset($website0Data[$key])) {
                $website0Data[$key] = $value;
            }
        }

        // Send opdatering kun for website 0
        $updateData = [
            'product' => $website0Data,
            'saveOptions' => true,
            'storeId' => 0  // Dette sikrer, at vi kun opdaterer for 'admin' store view (website 0)
        ];
      //  print_r($updateData);
       // $response = $this->sendApiRequest($this->baseUrl . $sku, 'PUT', $updateData);

       // return ['website0_update' => $response];
    }

    private function sendApiRequest($url, $method, $data) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer 48s1pd9cstnutyyl1ykncacwvtkn8sf9'
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function getExistingProduct($sku) {
        $url = $this->baseUrl . $sku;
        return json_decode($this->sendApiRequest($url, 'GET', null), true);
    }







    /* ----------------------------------------------------------------  */





    private function getKontainerLogoID($id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10615/items/'.$id);
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

        // var_dump($result);
        // return array("status"=>1,"msg"=>$this->error);
        return $result;
    }

    public function getCategoryIDSE($budget)
    {
        $data = array(
            "300"=>"643",
            "440"=>"644",
            "600"=>"645",
            "800"=>"646"
        );
        return isset($data[$budget]) ? $data[$budget] : false;
    }
    private function getCategoryIDNo($budget)
    {
        $data = array(
            "199"=>"629",
            "200"=>"630",
            "300"=>"631",
            "400"=>"632",
            "600"=>"633",
            "800"=>"636",
            "1000"=>"634",
            "2000"=>"635",
            "1200"=>"639"
        );
        return isset($data[$budget]) ? $data[$budget] : false;
    }
    private function getCategoryID($budget)
    {
        $data = array(
            "200"=>"521",
            "300"=>"513",
            "400"=>"514",
            "560"=>"515",
            "640"=>"524",
            "720"=>"594",
            "800"=>"516",
            "960"=>"543",
            "1040"=>"601",
            "2000"=>"603"
        );
        // 960, 1200, 2000
        return isset($data[$budget]) ? $data[$budget] : false;
    }

    private $magentoSuppliersData =


        [
            [
                "label"=> "Absolut Sport",
                "value"=> "5620"
            ],
            [
                "label"=> "Aida",
                "value"=> "5616"
            ],
            [
                "label"=> "Alfi",
                "value"=> "75"
            ],
            [
                "label"=> "Arne Jacobsen",
                "value"=> "5590"
            ],
            [
                "label"=> "Asserbo",
                "value"=> "76"
            ],
            [
                "label"=> "Atari",
                "value"=> "113"
            ],
            [
                "label"=> "B",
                "value"=> "77"
            ],
            [
                "label"=> "Bahco",
                "value"=> "127"
            ],
            [
                "label"=> "Bali",
                "value"=> "5591"
            ],
            [
                "label"=> "Bamix",
                "value"=> "78"
            ],
            [
                "label"=> "Bang & Olufsen",
                "value"=> "5592"
            ],
            [
                "label"=> "Betterbox",
                "value"=> "149"
            ],
            [
                "label"=> "Bezzerwisser",
                "value"=> "72"
            ],
            [
                "label"=> "Black",
                "value"=> "138"
            ],
            [
                "label"=> "Bodum",
                "value"=> "131"
            ],
            [
                "label"=> "BonCoca",
                "value"=> "123"
            ],
            [
                "label"=> "Bosch",
                "value"=> "5595"
            ],
            [
                "label"=> "Braun",
                "value"=> "79"
            ],
            [
                "label"=> "Broste",
                "value"=> "148"
            ],
            [
                "label"=> "by Lassen",
                "value"=> "51"
            ],
            [
                "label"=> "B\u00f8rge Mogensen",
                "value"=> "142"
            ],
            [
                "label"=> "Cavalet",
                "value"=> "57"
            ],
            [
                "label"=> "Cerruti 1881",
                "value"=> "5593"
            ],
            [
                "label"=> "Christian Bitz",
                "value"=> "80"
            ],
            [
                "label"=> "Cocoture",
                "value"=> "160"
            ],
            [
                "label"=> "Costa Nova",
                "value"=> "5594"
            ],
            [
                "label"=> "Cleanmate",
                "value"=> "74"
            ],
            [
                "label"=> "Comwell",
                "value"=> "43"
            ],
            [
                "label"=> "Day Birger Et Mikkelsen",
                "value"=> "40"
            ],
            [
                "label"=> "Daniel Wellington",
                "value"=> "133"
            ],
            [
                "label"=> "Designletters",
                "value"=> "81"
            ],
            [
                "label"=> "DFDS Seaways",
                "value"=> "44"
            ],
            [
                "label"=> "Dualit",
                "value"=> "83"
            ],
            [
                "label"=> "Dyberg Larsen",
                "value"=> "145"
            ],
            [
                "label"=> "Dyrberg\/Kern",
                "value"=> "61"
            ],
            [
                "label"=> "Egoista",
                "value"=> "73"
            ],
            [
                "label"=> "Eva Solo",
                "value"=> "52"
            ],
            [
                "label"=> "Eva Trio",
                "value"=> "121"
            ],
            [
                "label"=> "Excel",
                "value"=> "137"
            ],
            [
                "label"=> "Fiskars",
                "value"=> "29"
            ],
            [
                "label"=> "Formel T",
                "value"=> "82"
            ],
            [
                "label"=> "Fossil",
                "value"=> "88"
            ],
            [
                "label"=> "Frederik Bagger",
                "value"=> "60"
            ],
            [
                "label"=> "Func",
                "value"=> "122"
            ],
            [
                "label"=> "GaveFabrikken",
                "value"=> "111"
            ],
            [
                "label"=> "Georg Jensen",
                "value"=> "39"
            ],
            [
                "label"=> "Georg Jensen Damask",
                "value"=> "38"
            ],
            [
                "label"=> "Go Dream",
                "value"=> "150"
            ],
            [
                "label"=> "Grillin & Chillin",
                "value"=> "5596"
            ],
            [
                "label"=> "Happy Xmas",
                "value"=> "85"
            ],
            [
                "label"=> "Hair by Soho",
                "value"=> "5597"
            ],
            [
                "label"=> "Halo design",
                "value"=> "147"
            ],
            [
                "label"=> "Hay",
                "value"=> "48"
            ],
            [
                "label"=> "Herman CpH",
                "value"=> "126"
            ],
            [
                "label"=> "Hi5",
                "value"=> "69"
            ],
            [
                "label"=> "Holm",
                "value"=> "118"
            ],
            [
                "label"=> "Holmegaard",
                "value"=> "151"
            ],
            [
                "label"=> "Hoptimist",
                "value"=> "135"
            ],
            [
                "label"=> "Hornb\u00e6k",
                "value"=> "28"
            ],
            [
                "label"=> "House of Chefs",
                "value"=> "132"
            ],
            [
                "label"=> "Hotel Chocolat",
                "value"=> "152"
            ],
            [
                "label"=> "Hugo Boss",
                "value"=> "5598"
            ],
            [
                "label"=> "Huttili Hut",
                "value"=> "68"
            ],
            [
                "label"=> "Iittala",
                "value"=> "66"
            ],
            [
                "label"=> "JBL",
                "value"=> "5619"
            ],
            [
                "label"=> "Kay Bojesen",
                "value"=> "53"
            ],
            [
                "label"=> "K\u00e4hler",
                "value"=> "84"
            ],
            [
                "label"=> "KitchenAid",
                "value"=> "87"
            ],
            [
                "label"=> "Kitchen Master",
                "value"=> "86"
            ],
            [
                "label"=> "Konnerup",
                "value"=> "124"
            ],
            [
                "label"=> "Kreafunk",
                "value"=> "41"
            ],
            [
                "label"=> "Lakrids by B\u00fclow",
                "value"=> "91"
            ],
            [
                "label"=> "Letterwine",
                "value"=> "89"
            ],
            [
                "label"=> "Lind DNA",
                "value"=> "67"
            ],
            [
                "label"=> "Lyngby glas",
                "value"=> "90"
            ],
            [
                "label"=> "Lyngby porcel\u00e6n",
                "value"=> "92"
            ],
            [
                "label"=> "Magasin Chokolade",
                "value"=> "106"
            ],
            [
                "label"=> "Memphis",
                "value"=> "70"
            ],
            [
                "label"=> "Menu",
                "value"=> "54"
            ],
            [
                "label"=> "Meraki",
                "value"=> "130"
            ],
            [
                "label"=> "Mette Ditmer",
                "value"=> "120"
            ],
            [
                "label"=> "Miiego",
                "value"=> "153"
            ],
            [
                "label"=> "Monica Ritterband",
                "value"=> "93"
            ],
            [
                "label"=> "Montana",
                "value"=> "5599"
            ],
            [
                "label"=> "Mors\u00f8",
                "value"=> "94"
            ],
            [
                "label"=> "Mulberry",
                "value"=> "96"
            ],
            [
                "label"=> "Muuto",
                "value"=> "154"
            ],
            [
                "label"=> "Murphy",
                "value"=> "155"
            ],
            [
                "label"=> "M\u00f8rkholt",
                "value"=> "95"
            ],
            [
                "label"=> "Nespresso",
                "value"=> "97"
            ],
            [
                "label"=> "Nilfisk",
                "value"=> "128"
            ],
            [
                "label"=> "Nobili",
                "value"=> "119"
            ],
            [
                "label"=> "Norb\u00e6k",
                "value"=> "98"
            ],
            [
                "label"=> "Normann Copenhagen",
                "value"=> "99"
            ],
            [
                "label"=> "OBH",
                "value"=> "146"
            ],
            [
                "label"=> "Oh! By Kopenhagen Fur",
                "value"=> "5615"
            ],
            [
                "label"=> "Oh!",
                "value"=> "156"
            ],
            [
                "label"=> "Ole Chokolade",
                "value"=> "161"
            ],
            [
                "label"=> "Omhu",
                "value"=> "5614"
            ],
            [
                "label"=> "Orrefors Sweden",
                "value"=> "5600"
            ],
            [
                "label"=> "Peter Beier",
                "value"=> "5613"
            ],
            [
                "label"=> "Peugeot",
                "value"=> "101"
            ],
            [
                "label"=> "Philips",
                "value"=> "139"
            ],
            [
                "label"=> "Pillivuyt",
                "value"=> "157"
            ],
            [
                "label"=> "Pr. Chokolade",
                "value"=> "116"
            ],
            [
                "label"=> "Rains",
                "value"=> "162"
            ],
            [
                "label"=> "Rosendahl",
                "value"=> "102"
            ],
            [
                "label"=> "Royal Copenhagen",
                "value"=> "4"
            ],
            [
                "label"=> "Sackit",
                "value"=> "5617"
            ],
            [
                "label"=> "Sagaform",
                "value"=> "134"
            ],
            [
                "label"=> "Sega",
                "value"=> "112"
            ],
            [
                "label"=> "Silkeborg Uldspinderi",
                "value"=> "5612"
            ],
            [
                "label"=> "Simply Chocolate",
                "value"=> "25"
            ],
            [
                "label"=> "Skagen",
                "value"=> "104"
            ],
            [
                "label"=> "Skagerak (Trip Trap)",
                "value"=> "37"
            ],
            [
                "label"=> "Smartbox",
                "value"=> "144"
            ],
            [
                "label"=> "S\u00f6dahl",
                "value"=> "141"
            ],
            [
                "label"=> "Stanley",
                "value"=> "140"
            ],
            [
                "label"=> "Stantox",
                "value"=> "103"
            ],
            [
                "label"=> "Stelton",
                "value"=> "42"
            ],
            [
                "label"=> "Summerbird",
                "value"=> "136"
            ],
            [
                "label"=> "Sv. Michelsen",
                "value"=> "71"
            ],
            [
                "label"=> "S\u00f8ren S\u00f8g\u00e5rd",
                "value"=> "5601"
            ],
            [
                "label"=> "Tisvilde",
                "value"=> "158"
            ],
            [
                "label"=> "Tivoli",
                "value"=> "159"
            ],
            [
                "label"=> "Tobias Jacobsen",
                "value"=> "105"
            ],
            [
                "label"=> "Ultimate Fitness",
                "value"=> "5602"
            ],
            [
                "label"=> "Verner Panton",
                "value"=> "143"
            ],
            [
                "label"=> "Wally and Whiz",
                "value"=> "36"
            ],
            [
                "label"=> "Weber",
                "value"=> "55"
            ],
            [
                "label"=> "WMF",
                "value"=> "125"
            ],
            [
                "label"=> "XocolatI",
                "value"=> "117"
            ],
            [
                "label"=> "\u00d8koladen",
                "value"=> "100"
            ],
            [
                "label"=> "\u00d8vrige varem\u00e6rker",
                "value"=> "114"
            ],
            [
                "label"=> "Humdakin",
                "value"=> "5641"
            ]
        ];

    private $pimSuppliersData = [
        "8805386" => "Fredericia Furniture",
        "8738531" => "Audo",
        "8717656" => "Kath & Andersen",
        "8667436" => "Safly",
        "8344570" => "DFDS",
        "8132013" => "Solberg&Hansen",
        "8091774" => "Fjåk",
        "8086781" => "Matcompaniet",
        "8084655" => "Devold",
        "6601468" => "Jentene på Tunet",
        "6439680" => "Care By Me",
        "6400561" => "PR Chokolade",
        "6376815" => "LEGO Danmark A/S",
        "6361514" => "Ultimate Nordic",
        "6344314" => "Kay Bojesen",
        "6344192" => "Dewalt",
        "6343833" => "Edblad",
        "6341186" => "Tiny Garden",
        "6224395" => "Kodanska",
        "6119696" => "New Wave",
        "6062700" => "Schou",
        "5982358" => "Amundsen Spesial",
        "5879131" => "Norvigroup Norsk Dun AS",
        "5765306" => "Spilforsyningen",
        "5765299" => "Bornholms Keramikfabrik",
        "5616688" => "Bezzerwizzer Nordic",
        "5567835" => "Dangaard",
        "5500570" => "Pillivuyt",
        "5500554" => "601605000000",
        "5495682" => "JBL",
        "5495679" => "Omhu",
        "5495671" => "Lacoste",
        "5495669" => "Weber",
        "5495668" => "Caterpillar",
        "5495666" => "Coolstuff",
        "5495661" => "Go Dream",
        "5495657" => "GaveFabrikken",
        "5495652" => "Uldplaiden",
        "5495639" => "Eva Trio",
        "5495636" => "Livoo",
        "5495633" => "XOCOLATL",
        "5495631" => "Simply Chocolate",
        "5495628" => "Anker Chokolade",
        "5495626" => "Func",
        "5495624" => "Summerbird",
        "5495621" => "F&H",
        "5495619" => "&TRADITION A/S",
        "5495614" => "GEORG JENSEN DAMASK",
        "5495611" => "Bon Coca",
        "5495606" => "Peter Beier",
        "5495600" => "Ecooking",
        "5495598" => "Design Letters",
        "5495595" => "Ayaida",
        "5495593" => "Søren Søgaard",
        "5495587" => "AIDA",
        "5495485" => "Humdakin",
        "5451666" => "Hamonoya",
        "5433977" => "Howe A/S",
        "5433976" => "Uno Image",
        "5433974" => "Sackit",
        "5433971" => "Summersand ApS",
        "5433970" => "Miiego",
        "5433960" => "Markberg",
        "5433951" => "Pundit Games",
        "5433949" => "Louis Poulsen",
        "5433919" => "Sv.Michelsen",
        "5433918" => "Wallz",
        "5433917" => "Økoladen",
        "5433916" => "Xocolatl",
        "5433914" => "Rosendahl Design Group A/S",
        "5433911" => "OBH",
        "5433909" => "Normann Copenhagen",
        "5433898" => "Le Creuset",
        "5433889" => "Lakrids"];
}




