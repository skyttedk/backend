<?php

namespace GFUnit\development\fixscripts;

class CheckOrders
{

    private $stats;

    public function run() {

        $this->stats = array("cancel" => 0, "cancellist" => array(),"xmlerror" => array(), "xmlerrorlist" => array(),"nomatch" => 0, "nomatchlist" => array(),"new_order_conf" => 0, "no_order_conf" => 0);

        echo "CHECK ORDERS<br>";

        $list = \CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE order_state > 3 && order_state < 7 && shop_id in (select shop_id from cardshop_settings where language_code = 1) order by id DESC");
        echo "FOUND ".countgf($list)."<br>";

        $syncAgainList = array();

        $processed = 0;
        foreach($list as $companyOrder) {

            $company = \Company::find($companyOrder->company_id);
            echo "<br>Processing ".$companyOrder->order_no."<br>";

            // Find last document
            $lastSync = \GFCommon\model\navision\OrderSync::getLastSyncDocument($companyOrder);

            // Generate order document
            try {

                $orderXML = new \GFCommon\model\navision\OrderXML($companyOrder,$lastSync->revision);
                $xml = $orderXML->getXML();

                if($orderXML->getActiveCards() == 0 && $companyOrder->order_state != 7) {
                    echo "ACTION: ORDER SHOULD BE CANCELLED NOW!<br>";
                    $this->stats["cancel"]++;
                    $this->stats["cancellist"][] = $companyOrder->order_no;
                }


            } catch (\Exception $e) {

                echo "ERROR: Could not generate xml".$e->getMessage()."<br>";
                $this->stats["xmlerror"]++;
                $this->stats["xmlerrorlist"][] = $companyOrder->order_no;

            }

            // Check if same as last revision
            $lastXML = "";
            $isMatch = false;

            $orderdocs = \NavisionOrderDoc::find_by_sql("SELECT * FROM navision_order_doc WHERE company_order_id = ".intval($companyOrder->id)." ORDER BY revision DESC LIMIT 1");
            if(is_array($orderdocs) && countgf($orderdocs) > 0) {
                if(!in_array($orderdocs[0]->status,array(0,2,3))) {
                    $lastXML = $orderdocs[0]->xmldoc;

$search = "<type>2</type>
        <code>EMAILCARD</code>
        <description>*</description>
        <quantity>1.00</quantity>
        <price>0.00</price>";
$replace = "<type>2</type>
        <code>EMAILCARD</code>
        <description>*</description>
        <quantity>1.00</quantity>
        <price>-999</price>";

                    $lastXML = str_replace($search,$replace,$lastXML);
                    $lastXML = str_replace("-999.00","-999",$lastXML);
                    $lastXML = str_replace("<suppressconfirmation>true</suppressconfirmation>","<suppressconfirmation>false</suppressconfirmation>",$lastXML);

                    if($xml == $lastXML ) {
                        $isMatch = true;
                        echo "XML DOC MATCH<br>";
                    }
                }
            }


            if($isMatch == false) {
                echo "XML doc is not a match<br>";
                $this->stats["nomatch"]++;
                $this->stats["nomatchlist"][] = $companyOrder->order_no;

                ?><table style="width: 1000px">
                    <tr>
                        <td style="width: 50%;" valign="top">LAST SYNC DOC:<br><pre><?php echo htmlentities($lastXML); ?></pre></td>
                        <td style="width: 50%;" valign="top">NEW DOC:<br><pre><?php echo htmlentities( $xml); ?></pre></td>
                    </tr>
                </table><?php
                
                $sendOrderConf = $this->checkSendOrderConfirmation($lastXML,$xml);
                if($sendOrderConf) {
                    echo "<b>SEND NEW ORDER CONFIRMATION</b><br>";
                    $this->stats["new_order_conf"]++;
                }
                else {
                    echo "NO NEW ORDER CONFIRMATION!<br>";
                    $this->stats["no_order_conf"]++;
                }

                $syncAgainList[] = $companyOrder->id;

/*
                echo "LAST SYNC DOC:<br><pre>".htmlentities($lastXML)."</pre><br><br>NEW DOC:<pre>".htmlentities($xml)."</pre>";
                break;
*/
            }


            $processed++;
            //if($processed > 500) break;
        }

        echo implode(",",$syncAgainList)."<br>";
        echo "UPDATE company_order SET nav_synced = 0 WHERE id IN (".implode(",",$syncAgainList).")<br>";


        echo "<pre>".print_r($this->stats,true)."</pre>";


    }

    private function checkSendOrderConfirmation($lastXML,$currentXML)
    {
        try {

            $lastData = $this->xmlToArray($lastXML);
            $currentData = $this->xmlToArray($currentXML);

            $triggerFields = array("week", "shop_id", "private_delivery");
            foreach($triggerFields as $field) {
                if(isset($lastData["order"][$field]) != isset($currentData["order"][$field])) {
                    echo "DIFF on isset ".$field;
                    return true;
                } else if(isset($lastData["order"][$field]) && $lastData["order"][$field] != $currentData["order"][$field]) {
                    echo "DIFF on value ".$field;
                    return true;
                }
            }

            // Sum amount on last
            $lastAmount = 0;
            foreach($lastData["order"]["lines"]["line"] as $line) {
                if($line["price"] != "-999.00") {
                    $lastAmount += floatval($line["price"])*floatval($line["quantity"]);
                }
            }

            // Sum amount on current
            $currentAmount = 0;
            foreach($currentData["order"]["lines"]["line"] as $line) {
                if($line["price"] != "-999.00") {
                    $currentAmount += floatval($line["price"])*floatval($line["quantity"]);
                }
            }

            echo "Amount check ".$lastAmount." - ".$currentAmount."\r\n";
            if($lastAmount != $currentAmount) {
                echo "DIFF on amount ".$lastAmount." - ".$currentAmount."\r\n";
                return true;
            }

            return false;

        }
        catch(\Exception $e) {
            echo "FEJL I CHECK ORDRE BEKRÆFTELSES: ".$e->getMessage();
            return true;
        }
    }

    private function xmlToArray($xml) {

        $xmldata = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xmldata);
        return json_decode($json,TRUE);

    }

}