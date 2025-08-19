<?php

namespace GFUnit\development\fixscripts;

use GFCommon\Model\Navision\OrderWS;

class NavCommandFix
{

    private $orderWs = array();

    public function run()
    {

        return;
        // THIS SCRIPT IS MADE TO SEND CUSTOM DATA TO NAVISION
        //return;

        $languageid = 1;

        // Load xml to send
        $orderdoc = \NavisionOrderDoc::find(55837);
        $xml = $orderdoc->xmldoc;

        // Output
        echo "<pre>";
        echo htmlentities($xml);
        echo "</pre>";


        // Cal service
        $client = $this->getOrderWS($languageid);


        try {
            $response = $client->uploadOrderDoc($xml);
            echo "NAVISION CALL OK - RESPONSE:";
            echo "<pre>";
            echo htmlentities($xml);
            echo "</pre>";
        }
        catch (\Exception $e) {
            echo "CALL EXCEPTION: ".$e->getMessage();
        }
    }

    /**
     * @param $countryCode
     * @return OrderWS|mixed
     * @throws \Exception
     */
    private function getOrderWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create order service with no nav country");
        }
        if(isset($this->orderWs[intval($countryCode)])) {
            return $this->orderWs[intval($countryCode)];
        }
        $this->orderWs[intval($countryCode)] = new \GFCommon\Model\Navision\OrderWS(intval($countryCode));
        return $this->orderWs[intval($countryCode)];
    }


}
