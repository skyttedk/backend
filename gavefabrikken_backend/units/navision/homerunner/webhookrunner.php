<?php

namespace GFUnit\navision\homerunner;

class WebhookRunner {


    public static function saveWebhookData($type)
    {

        try {

            // Get php raw input
            $input = file_get_contents('php://input');

            // Full url
            $url = $_SERVER['REQUEST_URI'];

            // Save webhook data
            $wh = new \HomerunnerWebhook();
            $wh->type = $type;
            $wh->created = new \DateTime();
            $wh->ip = $_SERVER['REMOTE_ADDR'];
            $wh->data = $input;
            $wh->url = $url;

            $wh->save();

            \system::connection()->commit();

            echo "OK";

        }
        catch (\Exception $e) {

            // Output 500 status code
            http_response_code(500);
            echo "ERROR: ".$e->getMessage();

        }

    }
}
