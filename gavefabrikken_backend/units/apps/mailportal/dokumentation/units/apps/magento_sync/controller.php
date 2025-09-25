<?php

namespace GFUnit\apps\magento_sync;
use GFBiz\units\UnitController;
use GFUnit\apps\magento_sync\MagentoApi;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
public function setAllActiveimgatt()
{
    try {
        $sku = 'new-product1-005';  // Replace with your product SKU

        $results = magentoapi::setMainImageAttributes($sku);

        foreach ($results as $result) {
            if (isset($result['error'])) {
                echo "Error setting main image attributes: {$result['error']}\n";
            } elseif (isset($result['message'])) {
                echo $result['message'] . "\n";
            } else {
                echo "Successfully set main image attributes for store view {$result['store_view']} (Website ID: {$result['website_id']}), media ID {$result['media_id']}\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
    public function setmailimgatt()
    {
        try {
            $sku = 'new-product1-005';  // Replace with your product SKU

            $results = magentoapi::setMainImageAttributesForAllStoreView($sku);

            foreach ($results as $result) {
                if (isset($result['error'])) {
                    echo "Error setting main image attributes: {$result['error']}\n";
                } elseif (isset($result['message'])) {
                    echo $result['message'] . "\n";
                } else {
                    echo "Successfully set main image attributes for store view 'all', media ID {$result['media_id']}\n";
                }
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }


public function sletimg(){
    // Usage example
    try {
        $sku = 'new-product1-005';  // Replace with your product SKU

        $results = magentoapi::deleteAllProductImages($sku);

        foreach ($results as $result) {
            if (isset($result['error'])) {
                echo "Error deleting images: {$result['error']}\n";
            } elseif (isset($result['message'])) {
                echo $result['message'] . "\n";
            } else {
                echo "Successfully deleted image with media ID {$result['media_id']}\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

    public function resetimg()
    {
        try {
            $sku = 'new-product1-005';  // Replace with your product SKU

            $results = magentoapi::resetImageAttributes($sku);

            foreach ($results as $result) {
                if (isset($result['error'])) {
                    echo "Error resetting image attributes: {$result['error']}\n";
                } else {
                    echo "Successfully reset image attributes for store view {$result['store_view']}, media ID {$result['media_id']}\n";
                }
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
public function img()
{
    try {
        $sku = 'new-product1-005';  // Replace with your product SKU
        $imagePaths = [
            'https://system.gavefabrikken.dk//gavefabrikken_backend/views/media/user/0B2HlqDoGwoUvTNrM6yQgjZ7iNSK9b.jpg',
            'https://system.gavefabrikken.dk//gavefabrikken_backend/views/media/user/HErSnrcEdMzGIO6UBBgZefHUqBSpCu.jpg',
            'https://system.gavefabrikken.dk//gavefabrikken_backend/views/media/user/7zxxwdXq4y4sSO9jJYJoxbhvSNgaFI.jpg'
        ];
        $storeView = 'default';  // Replace with your store view code if different

        $results = magentoapi::uploadProductImages($sku, $imagePaths, $storeView);

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
    public function price()
    {
        try {
            $sku = 'new-product-001';  // Replace with actual SKU
            $newPrice = 1111;  // Replace with the desired price

            $result = magentoapi::updateProductWebsiteAndPrice($sku, $newPrice);

            if ($result !== null) {
                echo "Product updated successfully to be active only on website ID 0 with new price $newPrice.\n";
            } else {
                echo "Failed to update product website ID and price.\n";
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
public function ider()
{
    try {
        $sku = 'new-product1-005';  // Replace with actual SKU
        $websiteIds = [0, 1, 2];  // The website IDs where the product should be active

        $result = magentoapi::updateProductWebsiteIds($sku, $websiteIds);

        if ($result !== null) {
            echo "Product website IDs updated successfully.\n";
        } else {
            echo "Failed to update product website IDs.\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
    public function test()
    {
        echo "test";
        $MagentoApi = new magentoapi();
//        $websites = $MagentoApi::getWebsites();


        $newProductData = [
            'sku' => 'new-product1-005',
            'name' => 'New Product5',
            'price' => 5525,
            'status' => 1,
            'attribute_set_id' => 4,
            'type_id' => 'simple'
        ];
        $websiteIds = [0, 1,2]; // Associate with website IDs 1 and 2
        $result = $MagentoApi::createOrUpdateProduct($newProductData, $websiteIds);
        var_dump($result);
// Update an existing product
        /*
        $updateProductData = [
            'name' => 'Updated Product Name',
            'price' => 39.99
        ];
        $websiteIds = [1, 2, 3]; // Update website associations
        $result = MagentoApi::createOrUpdateProduct($updateProductData, $websiteIds, 'existing-product-sku');
        */


    }
    public function ping()
    {
        echo "adsfsad";
    }

}