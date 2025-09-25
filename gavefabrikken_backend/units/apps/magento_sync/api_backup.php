<?php

namespace GFUnit\apps\magento_sync;
use GFBiz\units\UnitController;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class magentoapi extends UnitController {
    private static $baseUrl = 'http://shopgavefabrikke.dev.magepartner.net/rest/';
    private static $token = 'i2cmpqapk7n0sjkeu6l6v463qqdoxp5f';
    private static $defaultStoreView = '';

    public function __construct() {
        parent::__construct(__FILE__);
    }

    public static function setMainImageAttributes($sku)
    {
        $results = [];

        try {
            // Get all active store views
            $storeViews = self::getActiveStoreViews();

            // Get existing product media entries
            $mediaEntries = self::getProductMediaEntries($sku);

            if (empty($mediaEntries)) {
                return [['message' => 'No images found for the product.']];
            }

            // Get the first image
            $firstImage = $mediaEntries[0];

            foreach ($storeViews as $storeView) {
                $updatedEntry = [
                    'id' => $firstImage['id'],
                    'media_type' => $firstImage['media_type'],
                    'label' => $firstImage['label'],
                    'position' => $firstImage['position'],
                    'disabled' => $firstImage['disabled'],
                    'types' => ['image', 'small_image', 'thumbnail']
                ];

                $endpoint = 'products/' . $sku . '/media/' . $firstImage['id'];
                $result = self::callApi($endpoint, 'PUT', ['entry' => $updatedEntry], $storeView);

                $results[] = [
                    'store_view' => $storeView,
                    'media_id' => $firstImage['id'],
                    'result' => $result
                ];
            }
        } catch (\Exception $e) {
            $results[] = [
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    public static function deleteAllProductImages($sku)
    {
        $results = [];

        try {
            // Get existing product media entries
            $mediaEntries = self::getProductMediaEntries($sku);

            foreach ($mediaEntries as $entry) {
                $endpoint = 'products/' . $sku . '/media/' . $entry['id'];

                // Use 'all' store view to delete the image across all store views
                $result = self::callApi($endpoint, 'DELETE', null, 'all');

                $results[] = [
                    'media_id' => $entry['id'],
                    'result' => $result
                ];
            }

            if (empty($results)) {
                $results[] = [
                    'message' => 'No images found for the product.'
                ];
            }
        } catch (\Exception $e) {
            $results[] = [
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    public static function resetImageAttributes($sku)
    {
        $results = [];

        try {
            // Get all active store views
            $storeViews = self::getActiveStoreViews();

            // Get existing product media entries
            $mediaEntries = self::getProductMediaEntries($sku);

            foreach ($storeViews as $storeView) {
                foreach ($mediaEntries as $entry) {
                    $updatedEntry = [
                        'id' => $entry['id'],
                        'media_type' => $entry['media_type'],
                        'label' => $entry['label'],
                        'position' => $entry['position'],
                        'disabled' => $entry['disabled'],
                        'types' => [] // Reset image types
                    ];

                    $endpoint = 'products/' . $sku . '/media/' . $entry['id'];
                    $result = self::callApi($endpoint, 'PUT', ['entry' => $updatedEntry], $storeView);

                    $results[] = [
                        'store_view' => $storeView,
                        'media_id' => $entry['id'],
                        'result' => $result
                    ];
                }
            }
        } catch (\Exception $e) {
            $results[] = [
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }
    private static function getProductMediaEntries($sku)
    {
        $endpoint = 'products/' . $sku . '/media';
        return self::callApi($endpoint, 'GET');
    }

    private static function getActiveStoreViews()
    {
        // This method should return an array of active store view codes
        // You'll need to implement this based on your Magento configuration
        $storeViews = self::callApi('store/storeViews', 'GET');
        return array_map(function($store) {
            return $store['code'];
        }, $storeViews);
    }

    public static function uploadProductImages($sku, $imagePaths, $storeView = 'all')
    {
        $results = [];
        $pos = 0;

        foreach ($imagePaths as $imagePath) {
            try {
                $type = $pos == 0 ? ['image', 'small_image', 'thumbnail'] : [];

                $name = self::generateRandomString(10) . ".jpg";
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

                // Corrected endpoint construction
                $endpoint = 'products/' . $sku . '/media';

                $result = self::callApi($endpoint, 'POST', $imageData, $storeView);

                $results[] = [
                    'position' => $pos,
                    'result' => $result
                ];

                $pos++;
            } catch (\Exception $e) {
                $results[] = [
                    'position' => $pos,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    private static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function updateProductWebsiteAndPrice($sku, $price) {
        try {
            $endpoint = 'products/' . $sku;

            $data = [
                'product' => [
                    'price' => $price,
                    'extension_attributes' => [
                        'website_ids' => [0,1,2]
                    ]
                ]
            ];

            echo "Calling API with endpoint: $endpoint\n";
            echo "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";

            $result = self::callApi($endpoint, 'PUT', $data);

            echo "API Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";

            return $result;
        } catch (\Exception $e) {
            echo "Error in updateProductWebsiteAndPrice: " . $e->getMessage() . "\n";
            return null;
        }
    }


    public static function updateProductWebsiteIds($sku, $websiteIds) {
        try {
            $endpoint = 'products/' . $sku;

            $data = [
                'product' => [
                    'extension_attributes' => [
                        'website_ids' => $websiteIds
                    ]
                ]
            ];

            echo "Calling API with endpoint: $endpoint\n";
            echo "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";

            $result = self::callApi($endpoint, 'PUT', $data);

            echo "API Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";

            return $result;
        } catch (\Exception $e) {
            echo "Error in updateProductWebsiteIds: " . $e->getMessage() . "\n";
            return null;
        }
    }
    public static function callApi($endpoint, $method = 'GET', $data = null, $storeView = null) {
        $storeView = $storeView ?: self::$defaultStoreView;
        echo    $url = self::$baseUrl . $storeView . '/V1/' . $endpoint;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . self::$token,
                'Content-Type: application/json'
            ],
        ]);

        if ($data !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        var_dump($response);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new \Exception("cURL Error: " . $error);
        }

        curl_close($curl);
        return json_decode($response, true);
    }

    public static function getWebsites() {
        return self::callApi('store/websites', 'GET');
    }

    public static function createOrUpdateProduct($productData, $websiteIds, $sku = null) {
        $method = $sku ? 'PUT' : 'POST';
        $endpoint = 'products' . ($sku ? '/' . $sku : '');

        //  $productData['extension_attributes']['website_ids'] = $websiteIds;

        $data = ['product' => $productData];
        print_r($data);
        return self::callApi($endpoint, $method, $data,"default");
    }

    public static function updateProductPrice($sku, $price, $updateWebsiteId, $allWebsiteIds) {
        try {
            $endpoint = 'products/' . $sku;

            $data = [
                'product' => [
                    'price' => $price,
                    'extension_attributes' => [
                        'website_ids' => $allWebsiteIds
                    ]
                ]
            ];

            // Adding scope information to the request body



            echo "Calling API with endpoint: $endpoint\n";
            echo "Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";

            $result = self::callApi($endpoint, 'PUT', $data);

            echo "API Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
            var_dump($result);
            return $result;
        } catch (\Exception $e) {
            echo "Error in updateProductPrice: " . $e->getMessage() . "\n";
            return null;
        }
    }
}