<?php

namespace GFUnit\development\fixscripts;

class CheckCardAmount
{

    private $stats;


    public function run()
    {

        echo "Check card amount<br>";

        // Get all orders that are closed
        $companyOrderList = \CompanyOrder::find_by_sql("select * from company_order where order_state = 10 && shop_id in (select shop_id from cardshop_settings where language_code = 5)");
        echo "Found ".countgf($companyOrderList)." orders to check<br><br>";

        foreach($companyOrderList as $i => $companyOrder) {

            $orderQuantity = $companyOrder->quantity;

            // Find active users
            $shopUsers = \ShopUser::find_by_sql("select * from shop_user where company_order_id = ".$companyOrder->id." && blocked = 0");
            $activeUsers = countgf($shopUsers);

            // Find active at close
            $lastDoc = \NavisionOrderDoc::find_by_sql("SELECT * FROM navision_order_doc WHERE company_order_id = ".$companyOrder->id." ORDER BY revision DESC LIMIT 1");
            if($lastDoc == null || countgf($lastDoc) == 0) {
                echo "Could not find nav docs for order ".$companyOrder->id;

            }
            else {
                $closeCards = null;

                // Parse xml document
                try {

                    $documentData = $this->xmlToArray($lastDoc[0]->xmldoc);
                    foreach ($documentData["order"]["lines"]["line"] as $line) {
                        if ($line["type"] == 0) {
                            $closeCards = intval($line["quantity"]);
                        }
                    }

                } catch (\Exception $e) {
                    echo "Error parsing xml document " . $e->getMessage();
                    exit();
                }

                $color = "#FFFFFF";
                //if($orderQuantity != $activeUsers) $color = "yellow";
                if ($activeUsers != $closeCards) {

                    echo "<span style='background: " . $color . ";'>" . $companyOrder->order_no . ": bestilt: " . $orderQuantity . " kort, aktuelle: " . $activeUsers . " kort, ordre lukket i nav med: " . $closeCards . " kort - (" . ($closeCards - $activeUsers) . " lukket efter navision afslutning)</span>";
                    echo "<br>";
                }
                //if($i > 1000) exit();
            }
        }

        echo "Done..<br>";
        echo "<pre>".print_r($this->stats,true)."</pre>";

    }

    private function xmlToArray($xml) {

        $xmldata = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xmldata);
        return json_decode($json,TRUE);

    }

}