<?php

namespace GFUnit\navision\syncorder;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CountryHelper;
use GFCommon\Model\Navision\CustomerObj;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\CustomerXML;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\OrderWS;
use GFCommon\Model\Navision\OrderXML;

class OrderLineSync
{


    public function runOrderLineSync() {



        $maxTimeSeconds = 30;
        $maxOrders = 1000;

        // Load the next order to process
        $companyOrderList = \NavisionOrderDoc::find_by_sql("SELECT company_order_id FROM navision_order_doc WHERE company_order_id > 0 and parsed IS NULL GROUP BY company_order_id ORDER BY company_order_id ASC");

        echo "Found " . count($companyOrderList) . " orders to process\n";
        $startTime = time();
        $processed = 0;

        foreach($companyOrderList as $orderDoc) {

            // Check if we have been running for too long
            if(time() - $startTime > $maxTimeSeconds) {
                echo "Time limit reached<br>";
                break;
            }

            // Check if we have processed enough orders
            if($processed >= $maxOrders) {
                echo "Max orders reached<br>";
                break;
            }

            // Process
            $this->processOrder($orderDoc->company_order_id);
            $processed++;

        }

        // Commit and return shipment
        \system::connection()->commit();


        // Output script to reload the page
        echo "<script>setTimeout(function(){location.reload();},1000);</script>";


    }

    private function processOrder($companyorderid) {

        // Load companyorder
        $companyOrder = \CompanyOrder::find($companyorderid);
        echo "Process ".$companyorderid.": ".$companyOrder->order_no."<br>";

        // Load all navision order docs for company order
        $navisionOrderDoc = \NavisionOrderDoc::find('all', array('conditions' => array('company_order_id = ?', $companyorderid), 'order' => 'id DESC'));
        $syncOrderDoc = $navisionOrderDoc[0];
        echo "- Found ".count($navisionOrderDoc)." order docs for order<br>";

        // Load existing lines from
        $orderLines = \NavisionOrderLine::find('all', array('conditions' => array('company_order_id = ?', $companyorderid), 'order' => 'id DESC'));
        echo "- Found ".count($orderLines). " ordre linjer<br>";
        foreach($orderLines as $orderLine) {
            $orderLine->delete();
        }

        // Process the order
        $docData = json_decode($this->xml_to_json($syncOrderDoc->xmldoc),true);
        $docData = $docData["order"];

        $docOrderNo = $docData["orderno"];
        $docCustomerNo = $docData["customerno"];
        $docRevision = $docData["version"];
        $isCancelled = $docData["rollback"] == "true" ? 1 :0;
        $isFinalVersion = ($companyOrder->order_state == 10 || $companyOrder->order_state == 8) ? 1 : 0;

        if(is_array($docData["lines"]["line"])) {
            foreach($docData["lines"]["line"] as $line) {

                echo "<pre>".json_encode($line,JSON_PRETTY_PRINT)."</pre>";
                
                $type = $line["type"];
                $code = $line["code"];
                if(is_array($code)) $code = "";
                $description = $line["description"];
                if(is_array($description)) $description = "";
                $quantity = $line["quantity"];
                $price = $line["price"];

                // Get important data from the object
                $nol = new \NavisionOrderLine();
                $nol->company_order_id = $companyorderid;
                $nol->order_no = $docOrderNo;
                $nol->customer_no = $docCustomerNo;
                $nol->is_cancelled = $isCancelled;
                $nol->final_version = $isFinalVersion;
                $nol->revision = $docRevision;
                $nol->navision_order_doc_id = $syncOrderDoc->id;
                $nol->type = $type;
                $nol->code = $code;
                $nol->description = $description;
                $nol->quantity = $quantity;
                $nol->price = $price;
                $nol->created = date('d-m-Y H:i:s');
                $nol->synced = $syncOrderDoc->created;
                $nol->save();

            }
        }

        // Process order doc
        echo "Marking all orderdocs as parsed<br>";
        foreach($navisionOrderDoc as $orderDoc) {
            $orderDoc->parsed = date('d-m-Y H:i:s');
            $orderDoc->save();
        }

        echo "<br>";

    }

    function xml_to_json($xml_string) {
        $xml = simplexml_load_string($xml_string);
        if ($xml === false) {
            return json_encode(['error' => 'Failed to parse XML string']);
        }

        return json_encode($xml, JSON_PRETTY_PRINT);
    }



}
