<?php

namespace GFUnit\cardshop\shipblocktool;

class BlockListModel
{

    public function __construct()
    {

    }

    private function abortError($error) {
       
            echo json_encode(array("status" => 0, "error" => $error));
            exit();
       
    }
    
    public function runhtmllist($isCSV = false)
    {

        // Check user
        if(\router::$systemUser == null || \router::$systemUser->id == 0) {
            $this->abortError("You are not logged in!");
        }

        // Load language
        $languageCode = \router::$systemUser->language;
        if($languageCode > 0) {
            $languageCodes[] = $languageCode;
        }

        if(in_array(\router::$systemUser->id,array(50))) {
            $languageCodes = array_merge($languageCodes, array(1,4,5));
            $languageCodes = array_unique($languageCodes);
        }

        if(count($languageCodes) == 0) {
            $this->abortError("No languages found!");
        }


        // Read raw input as json to php array
        if($isCSV) {
            $data = $_GET;
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        if(!isset($data["action"]) || ($data["action"]) != "shipmentblocklistprovider") {
            $this->abortError("Fejl i kriterier!");
        }

        // Create filter criterias

        $searchSQL = " ";
        $havingSQL = " ";

        // text
        $filterSearch = trimgf($data["textSearch"] ?? "");
        if($filterSearch != "") {

            // Make filtersearch sql injection safe
            $filterSearch = str_replace("'","",$filterSearch);
            $filterSearch = str_replace('"',"",$filterSearch);
            $filterSearch = "%".$filterSearch."%";

            $searchSQL .= " AND (
    company_order.order_no LIKE '".$filterSearch."' OR 
    company_order.company_name LIKE '".$filterSearch."' OR 
    company_order.cvr LIKE '".$filterSearch."' OR 
    shipment.itemno LIKE '".$filterSearch."' OR 
    shipment.itemno2 LIKE '".$filterSearch."' OR 
    shipment.itemno3 LIKE '".$filterSearch."' OR 
    shipment.itemno4 LIKE '".$filterSearch."' OR 
    shipment.itemno5 LIKE '".$filterSearch."' OR 
    shipment.from_certificate_no LIKE '".$filterSearch."' OR 
    shipment.to_certificate_no LIKE '".$filterSearch."' OR 
    shipment.shipto_name LIKE '".$filterSearch."' OR 
    shipment.shipto_address LIKE '".$filterSearch."' OR 
    shipment.shipto_address2 LIKE '".$filterSearch."' OR 
    shipment.shipto_postcode LIKE '".$filterSearch."' OR 
    shipment.shipto_city LIKE '".$filterSearch."' OR 
    shipment.shipto_country LIKE '".$filterSearch."' OR 
    shipment.shipto_contact LIKE '".$filterSearch."' OR 
    shipment.shipto_email LIKE '".$filterSearch."' OR 
    shipment.shipto_phone LIKE '".$filterSearch."' OR 
    shipment.shipment_note LIKE '".$filterSearch."' OR 
    shipment.support_note LIKE '".$filterSearch."' OR 
    blockmessage.description LIKE '".$filterSearch."'
)";

        }

        // Shop id
        $shopIDList = $data["concept"] ?? array();
        if(count($shopIDList) > 0) {

            $shopSQLList = array();
            foreach($shopIDList as $shopID) {
                if(intval($shopID) > 0) {
                    $shopSQLList[] = intval($shopID);
                }
            }

            if(count($shopSQLList) > 0) {
                $searchSQL .= " and (co.shop_id in (".implode(" , ",$shopSQLList).")) ";
            }

        }

        // Show hidden
        if(intvalgf($data["showhidden"]) == 0) {
            $searchSQL .= " and silent = 0 ";
        }

        // Show solved
        if(intvalgf($data["showsolved"]) == 0) {
            $havingSQL .= " SUM(CASE WHEN blockmessage.release_status = 0 THEN 1 ELSE 0 END) > 0 ";
        }

        // Show tech
        if(intvalgf($data["showtech"]) == 0) {
            $searchSQL .= " and blockmessage.tech_block = 0 ";
        } else {
            $searchSQL .= " and blockmessage.tech_block = 1 ";
        }



        // Construct sql
        $sqlFields = "";
        $sql = "";
        $offset = "";
        $pageSize = 9999999;

        // Sorting
        $sortFieldSQL = "co.order_no";
        $sortDir = ($data["sortDir"] == "asc") ? "ASC" : "DESC";


        $sortField = $data["sortField"] ?? "";
        if($sortField == "id") {
            $sortFieldSQL = "shipment.id";
        } else if($sortField == "customer") {
            $sortFieldSQL = "company_order.order_no";
        } else if($sortField == "shipment") {
            $sortFieldSQL = "shipment.from_certificate_no";
        } else if($sortField == "handler") {
            $sortFieldSQL = "shipment.handler";
        } else if($sortField == "itemno") {
            $sortFieldSQL = "shipment.itemno";
        } else if($sortField == "receiver") {
            $sortFieldSQL = "shipment.shipto_name";
        } else if($sortField == "address") {
            $sortFieldSQL = "shipment.shipto_address";
        } else if($sortField == "openblock") {
            $sortFieldSQL = "SUM(CASE WHEN blockmessage.release_status = 0 THEN 1 ELSE 0 END)";
        } else if($sortField == "closedblock") {
            $sortFieldSQL = "SUM(CASE WHEN blockmessage.release_status = 1 THEN 1 ELSE 0 END)";
        } else if($sortField == "created") {
            $sortFieldSQL = "shipment.created_date";
        } else if($sortField == "hidden") {
            $sortFieldSQL = "shipment.silent";
        } else if($sortField == "note") {
            $sortFieldSQL = "shipment.support_note";
        } else if($sortField == "blocktype") {
            $sortFieldSQL = "blockmessage.block_type";
        }

        $sortSQL = " ORDER BY ".$sortFieldSQL." ".$sortDir." ";


        $sql = "SELECT 
    cardshop_settings.concept_code, 
    cardshop_settings.language_code,
    company_order.order_no, 
    company_order.company_name, 
    company_order.order_state,
    company_order.expire_date,
    shipment.id, 
    shipment.shipment_type, 
    shipment.created_date, 
    shipment.handler, 
    shipment.itemno, shipment.quantity,
    shipment.itemno2, shipment.quantity2,
    shipment.itemno3, shipment.quantity3,
    shipment.itemno4, shipment.quantity4,
    shipment.itemno5, shipment.quantity5,
    shipment.from_certificate_no, 
    shipment.to_certificate_no,
    shipment.shipto_name, 
    shipment.shipto_address, 
    shipment.shipto_address2, 
    shipment.shipto_postcode, 
    shipment.shipto_city, 
    shipment.shipto_country, 
    shipment.shipto_contact, 
    shipment.shipto_email, 
    shipment.shipto_phone, 
    shipment.shipment_note, 
    shipment.shipment_state,
    shipment.support_note,
    COUNT(blockmessage.id) AS total_blockmessages,
    SUM(CASE WHEN blockmessage.release_status = 0 THEN 1 ELSE 0 END) AS open_blockmessages,
    SUM(CASE WHEN blockmessage.release_status = 1 THEN 1 ELSE 0 END) AS closed_blockmessages,
    MIN(blockmessage.created_date) AS first_blockmessage_date, 
    GROUP_CONCAT(distinct block_type) AS block_types, 
    MIN(release_status) AS min_release_status, 
    MAX(release_message) AS max_release_message, 
    tech_block, 
    min(silent) as silent,
    blockmessage.description
FROM 
    shipment
JOIN 
    blockmessage ON shipment.id = blockmessage.shipment_id
JOIN 
    company_order ON shipment.companyorder_id = company_order.id
JOIN 
    cardshop_settings ON company_order.shop_id = cardshop_settings.shop_id
WHERE 
    shipment.deleted_date IS NULL 
    AND company_order.order_state NOT IN (7,8) 
    AND shipment.shipment_state NOT IN (4) 
    AND cardshop_settings.language_code IN (".implode(",",$languageCodes).")
".$searchSQL."
GROUP BY 
    shipment.id ".(trim($havingSQL) != "" ? " HAVING ".$havingSQL : "");


        // Get orders
        $shipmentList = \Shipment::find_by_sql($sqlFields." ".$sql." ".$sortSQL." LIMIT ".$pageSize);

        ob_start();

        ?><thead class="thead-light">
    <tr>
        <th data-sortfield="id">Leverance ID</th>
        <th data-sortfield="customer">Kunde</th>
        <th data-sortfield="shipment">Om leverancen</th>
        <th data-sortfield="itemno">Varer</th>
        <th data-sortfield="receiver">Modtager</th>
        <?php /* ?><th data-sortfield="address">Modtager adresse</th><?php */ ?>
        <?php /* ?><th data-sortfield="openblock">Fejl</th><?php */ ?>
        <?php /* ?><th data-sortfield="closedblock">Løst</th><?php */ ?>
        <th data-sortfield="blocktype">Fejl type</th>
        <th data-sortfield="created">Om fejlen</th>
        <?php /* ?><th data-sortfield="hidden">Skjult</th><?php */ ?>
        <th data-sortfield="note">Kundeservice note</th>
        <th>&nbsp;</th>

    </tr>

    </thead>
        <tbody><?php

        foreach($shipmentList as $order) {
            $this->getOrderListRow($order);
        }

        ?></tbody><?php

        $content = ob_get_contents();
        ob_end_clean();

        echo json_encode(array("status" => 1, "content" => $content,"page" => 1,"totalRows" => count($shipmentList),"pageSize" => 9999999,"maxPage" => 1));

    }

    private function getOrderListRow($shipdata)
    {

        ?><tr>
            <td><?php echo $shipdata->id; ?></td>
            <td>
                <?php echo $shipdata->company_name; ?><br>
                <?php echo $shipdata->order_no; ?><br>
                <?php echo $shipdata->concept_code; ?>: <?php echo $shipdata->expire_date; ?><br>
            </td>
            <td>
                <?php

                    $shipmentType = "";
                    $shipmentDetails = "";

                    if($shipdata->shipment_type == "privatedelivery") {
                        $shipmentType = "Privatlevering";
                        $shipmentDetails = "Kortnr: ".$shipdata->from_certificate_no;
                    } else if($shipdata->shipment_type == "directdelivery") {
                        $shipmentType = "Direkte levering";
                        if($shipdata->from_certificate_no > 0) {
                            $shipmentDetails = "Kortnr: ".$shipdata->from_certificate_no;
                        } else {
                            $shipmentDetails = "Ukendt oprindelse";
                        }
                    } else if($shipdata->shipment_type == "earlyorder") {
                        $shipmentType = "Earlyordre";
                    } else if($shipdata->shipment_type == "giftcard") {
                        $shipmentType = "Fysiske gavekort";
                        $shipmentDetails = $shipdata->from_certificate_no." - ".$shipdata->to_certificate_no;
                    } else {
                        $shipmentType = "Ukendt: ".$shipdata->shipment_type;
                    }

                    echo $shipmentType."<br>".$shipmentDetails;

                    echo "<br>Via: ";
                $handlerName = "Ukendt: ".$shipdata->handler;

                if($shipdata->handler == "navision") $handlerName = "Navision";
                else if($shipdata->handler == "dpse") $handlerName = "DistributionPlus";
                else if($shipdata->handler == "glsexport") $handlerName = "Manuel export";
                else if($shipdata->handler == "manual") $handlerName = "Manuel udtræk";
                else if($shipdata->handler == "sespahotel") $handlerName = "SE Spa/hotel liste";

                echo $handlerName;

                ?>
            </td>

            <td>
                <?php

                    if($shipdata->shipment_type == "giftcard") {
                        echo $shipdata->quantity." stk. fysiske gavekort";
                    } else {

                        $itemLines = [];

                        if(trimgf($shipdata->itemno) != "" && intvalgf($shipdata->quantity) > 0) {
                            $itemLines[] = intvalgf($shipdata->quantity)." stk. ".trimgf($shipdata->itemno);
                        }
                        if(trimgf($shipdata->itemno2) != "" && intvalgf($shipdata->quantity2) > 0) {
                            $itemLines[] = intvalgf($shipdata->quantity2)." stk. ".trimgf($shipdata->itemno2);
                        }

                        if(trimgf($shipdata->itemno3) != "" && intvalgf($shipdata->quantity3) > 0) {
                            $itemLines[] = intvalgf($shipdata->quantity3)." stk. ".trimgf($shipdata->itemno3);
                        }

                        if(trimgf($shipdata->itemno4) != "" && intvalgf($shipdata->quantity4) > 0) {
                            $itemLines[] = intvalgf($shipdata->quantity4)." stk. ".trimgf($shipdata->itemno4);
                        }

                        if(trimgf($shipdata->itemno5) != "" && intvalgf($shipdata->quantity5) > 0) {
                            $itemLines[] = intvalgf($shipdata->quantity5)." stk. ".trimgf($shipdata->itemno5);
                        }

                        echo implode("<br>",$itemLines);

                    }

                ?>
            </td>
            <td><?php

                echo $shipdata->shipto_name."<br>".$shipdata->shipto_email."<br>".$shipdata->shipto_phone;

            ?></td>

            <?php /* ?><td><?php
            echo $shipdata->shipto_address."<br>".$shipdata->shipto_address2."<br>".$shipdata->shipto_postcode." ".$shipdata->shipto_city."<br>".$shipdata->shipto_country;
            ?></td><?php */ ?>


        <td>
            <?php

                $blockTypes = explode(",",$shipdata->block_types);
                $blockTypeNames = array();

                foreach($blockTypes as $blockType) {
                    $blockTypeNames[] = $blockType;
                }

                echo implode(", ",$blockTypeNames);

            ?>
        </td>
            <td>
                Fra d. <?php echo $shipdata->first_blockmessage_date; ?><br>
                Åbne fejl: <?php echo $shipdata->open_blockmessages; ?>, tidligere fejl: <?php echo $shipdata->closed_blockmessages; ?><br>
                Status: <?php echo $shipdata->silent == 1 ? "Skjult" : "Aktiv"; ?>
            </td>

            <td><?php echo $shipdata->support_note; ?></td>
            <td>
                <?php if($shipdata->open_blockmessages > 0) { ?>
                    <button type="button" class="btn btn-sm btn-primary" onClick="openShipmentModal(<?php echo $shipdata->id; ?>)">vis fejl</button>
                <?php } ?>
            </td>
        </tr><?php

    }



}