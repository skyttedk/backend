<?php
namespace GFUnit\cron\jobi;

use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);


    }

    public function index() {

        echo "SELECT SCRIPT TO RUN!";

    }
    public function magento_order_stock()
    {
        $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=MagentoOrderStock/run';

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        } else {
            echo 'Response: ' . $response;
        }

        curl_close($ch);
    }


}