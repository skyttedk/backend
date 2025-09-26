<?php

namespace GFUnit\magento\vip;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function testservice()
    {
        echo "I am a test service!";
    }

    public function cronjob(){
        $currentHour = (int)date('H');
        if ($currentHour >= 22 || $currentHour < 7) {
            echo "Cronjob is not allowed to run between 22:00 and 07:00.";
            return;
        }
     //   $storeList = [25,20,58,15,41,19,29];
        $storeList = [36];
        foreach ($storeList as $storeID){
            $this->job($storeID);
        }
    }

    public function job($storeID)
    {
        // Check if current time is between 22:00 and 07:00



       $urls = [
            'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=MagentoOrderStock/run&storeid='.$storeID,
            'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=MagentoOrderStock/analyzeStockStatusAndSendEmail&storeid='.$storeID
        ];

        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            if ($response === false) {
                echo 'cURL Error for ' . $url . ': ' . curl_error($ch) . "\n";
            } else {
                echo 'Response for ' . $url . ': ' . $response . "\n";
            }

            curl_close($ch);
        }
    }
}