<?php

namespace GFUnit\navision\syncvalgshop;


use GFBiz\valgshop\OrderBuilder;
use GFBiz\valgshop\ShopOrderModel;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\VSOrderWS;


class ValgshopSync
{

    public function __construct()
    {

    }

    private function log($message) {
        echo $message."<br>";
    }

    public function test() {


        NavClient::setNavDevMode(true);

        // Create a shop order
        try {

            $shoporder = new ShopOrderModel(5109);
            $exporter = OrderBuilder::buildOrderXML($shoporder);
            $xml = $exporter->export();

            echo "<pre>";
            echo htmlentities($xml);
            echo "</pre>";
        }
        catch (\Exception $e) {
            echo "Could not create VS order xml: ".$e->getMessage();
            exit();
        }




        // Send the order to Navision
        $orderClient = new VSOrderWS(1);
        $response = $orderClient->uploadOrderDoc($xml);

        echo "<br>RESPONSE:<br>";
        var_dump($response);

        echo "<br>RESPONSE DATE:<br>";
        var_dump($orderClient->getLastOrderResponse());

        \system::connection()->commit();
    }

}

/*

Create company from company id
$company = \Company::find(54545);

        // Sync company
        // Generate company xml
        try {
            $companyXML = new \GFCommon\Model\Navision\CustomerXML($company);
            $this->log(" - Company xml:<br><pre>".htmlentities($companyXML->getXML())."</pre><br>");
        }
        catch (\Exception $e) {
            $this->log(" - Error creating customer xml document: ".$e->getMessage());
            return;
        }

        try {

            $client = new \GFCommon\Model\Navision\OrderWS($company->language_code);
            $client->uploadCustomerDoc($companyXML->getXML());

            // Update company
            $company->company_state = 5;
            $company->nav_customer_no = $client->getLastCustomerNo();
            $company->save();

            // Update last company number if web order
            if($companyXML->getCustomerNumber() > 0) {
                \NavisionCompanyNo::setUsedCompanyNo($company->language_code,$companyXML->getCustomerNumber());
            }


        } catch (\Exception $e) {
            $this->log(" - Error creating customer in navision: ".$e->getMessage());
            $company->company_state = 6;
            $company->save();

        }


        \system::connection()->commit();
 */

/*
$customerService = new CustomerWS(1);


$result = $customerService->searchByName("gavefabrikken");

echo "<pre>";
var_dump($result);
echo "</pre>";

for($i=59700;$i<=59999;$i++) {
    $result = $customerService->getByCustomerNo($i);

    echo "<br><pre>";
    var_dump($result);
    echo "</pre><br>";
}



// Debitor no: 59783
*/
