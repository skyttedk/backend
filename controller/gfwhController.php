<?php

/**
 * GAVEFABRIKKEN WEBHOOK CONTROLLER
 * Controller for public webhooks
 */

class GfWhController Extends baseController
{

    public function Index()
    {
        echo "NO ACTION";
    }


    /**
     * HOMERUNNER WEBHOOKS
     */

    public function hrtracking() {
        \GFUnit\navision\homerunner\WebhookRunner::saveWebhookData("tracking");
    }

    public function hrorder() {
        \GFUnit\navision\homerunner\WebhookRunner::saveWebhookData("order");
    }

    public function hrstock() {
        \GFUnit\navision\homerunner\WebhookRunner::saveWebhookData("stock");
    }

}