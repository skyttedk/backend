<?php

namespace GFUnit\cardshop\reports;
use GFBiz\units\UnitController;

class EarlyOrderModel
{

    private $languageCodes;

    private $shopSettingsList;

    private $isService;

    private $shopIDList;

    public function __construct($isService)
    {

        $this->languageCodes = [];
        $this->isService = $isService;
        $this->loadData();

    }

    private function loadData()
    {

        // Check user
        if(\router::$systemUser == null || \router::$systemUser->id == 0) {
            $this->abortError("You are not logged in!");
        }

        // Load language
        $languageCode = \router::$systemUser->language;
        if($languageCode > 0) {
            $this->languageCodes[] = $languageCode;
        }

        if(in_array(\router::$systemUser->id,array(50))) {
            $this->languageCodes = array_merge($this->languageCodes, array(1,4,5));
            $this->languageCodes = array_unique($this->languageCodes);
        }

        if(count($this->languageCodes) == 0) {
            $this->abortError("No languages found!");
        }

        // Load shops
        $this->shopSettingsList = \CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings where language_code in (".implode(",",$this->languageCodes).") ORDER BY language_code ASC, concept_code ASC");
        $this->shopIDList = [];

        foreach($this->shopSettingsList as $settings) {
            $this->shopIDList[] = $settings->shop_id;
        }

        if(count($this->shopIDList) == 0) {
            $this->abortError("No shops found!");
        }


    }

    private function abortError($error) {
        if($this->isService) {
            echo json_encode(array("status" => 0, "error" => $error));
            exit();
        } else {
            echo $error;
            exit();
        }
    }

    public function listProvider($isCSV=false)
    {

        // Read raw input as json to php array
        if($isCSV) {
            $data = $_GET;
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        if(!isset($data["action"]) || ($data["action"]) != "earlyorderlistprovider") {
            $this->abortError("Fejl i kriterier!!");
        }

        // Create filter criterias

        $searchSQL = " ";


        // text
        $filterSearch = trimgf($data["textSearch"] ?? "");
        if($filterSearch != "") {

            // Make filtersearch sql injection safe
            $filterSearch = str_replace("'","",$filterSearch);
            $filterSearch = str_replace('"',"",$filterSearch);
            $filterSearch = "%".$filterSearch."%";

            $searchSQL .= " AND (
        s.consignor_labelno LIKE '".$filterSearch."' OR
        s.nav_order_no LIKE '".$filterSearch."' OR
        s.shipto_contact LIKE '".$filterSearch."' OR
        s.shipto_email LIKE '".$filterSearch."' OR
        s.shipto_phone LIKE '".$filterSearch."' OR
        s.shipto_name LIKE '".$filterSearch."' OR
        s.shipto_address LIKE '".$filterSearch."' OR
        s.shipto_address2 LIKE '".$filterSearch."' OR
        s.shipto_postcode LIKE '".$filterSearch."' OR
        s.shipto_city LIKE '".$filterSearch."' OR
        u.itemno LIKE '".$filterSearch."' OR
        co.company_name LIKE '".$filterSearch."' OR
        co.order_no LIKE '".$filterSearch."'
    ) ";

        }

        // Order no
        $orderNoFilter = trimgf($data["orderNumber"] ?? "");
        if($orderNoFilter != "") {

            // replace all special characters with ,
            $orderNoFilter = str_replace(array(" ",".",";","-","/","#"),",",$orderNoFilter);
            $orderNoFilterList = explode(",",$orderNoFilter);
            $orderNoSQL = array();

            foreach($orderNoFilterList as $orderNo) {
                $orderNo = trim($orderNo);
                if($orderNo != "" && strtoupper(substr($orderNo, 0,2)) == "BS") {
                    $orderNoSQL[] = "co.order_no like '".$orderNo."'";
                }
            }

            if(count($orderNoSQL) > 0) {
                $searchSQL .= " and (".implode(" OR ",$orderNoSQL).") ";
            }

        }

        // Leverance id
        $leveranceIDFilter = trimgf($data["leveranceId"] ?? "");
        if($leveranceIDFilter != "") {

            // replace all special characters with ,
            $leveranceIDFilter = str_replace(array(" ",".",";","-","/","#"),",",$leveranceIDFilter);
            $leveranceIDFilterList = explode(",",$leveranceIDFilter);
            $leveranceIDSql = array();

            foreach($leveranceIDFilterList as $leveranceID) {
                $leveranceID = intval($leveranceID);
                if($leveranceID > 0) {
                    $leveranceIDSql[] = "s.id = ".intval($leveranceID)."";
                }
            }

            if(count($leveranceIDSql) > 0) {
                $searchSQL .= " and (".implode(" OR ",$leveranceIDSql).") ";
            }

        }

        // Item no
        $itemNoFilter = trimgf($data["itemno"] ?? "");
        if($itemNoFilter != "") {

            // replace all special characters with ,
            $itemNoFilter = str_replace(array(" ","",";"),",",$itemNoFilter);
            $orderNoFilterList = explode(",",$itemNoFilter);
            $itemNoSql = array();

            foreach($orderNoFilterList as $orderNo) {
                $orderNo = trim($orderNo);
                if($orderNo != "") {
                    $itemNoSql[] = "u.itemno like '".$orderNo."'";
                }
            }

            if(count($itemNoSql) > 0) {
                $searchSQL .= " and (".implode(" OR ",$itemNoSql).") ";
            }

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


        /*


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

                */

        // Order state
        $orderStateList = $data["orderStatus"] ?? array();
        if(count($orderStateList) > 0) {
            $orderStateSQLList = [];

            foreach($orderStateList as $orderState)
            {
                $orderState = intval($orderState);
                if($orderState > 0) {
                    $orderStateSQLList[] = $orderState;
                }
            }

            if(count($orderStateSQLList) > 0) {
                $searchSQL .= " and co.order_state IN (".implode(",",$orderStateSQLList).") ";
            }

        }


        // Sorting
        $sortFieldSQL = "s.id";
        $sortDir = ($data["sortDir"] == "asc") ? "ASC" : "DESC";

        $sortField = $data["sortField"] ?? "";
        switch($sortField) {
            case "id":
                $sortFieldSQL = "s.id";
                break;
            case "orderno":
                $sortFieldSQL = "co.order_no";
                break;
            case "company":
                $sortFieldSQL = "co.company_name";
                break;
            case "orderstate":
                $sortFieldSQL = "co.order_state";
                break;
            case "linjenr":
                $sortFieldSQL = "u.n";
                break;
            case "itemno":
                $sortFieldSQL = "u.itemno";
                break;
            case "quantity":
                $sortFieldSQL = "u.quantity";
                break;
            case "status":
                $sortFieldSQL = "s.shipment_state";
                break;
            case "samleordre":
                $sortFieldSQL = "CASE WHEN (NULLIF(s.itemno, '') IS NOT NULL) + (NULLIF(s.itemno2, '') IS NOT NULL) + 
                         (NULLIF(s.itemno3, '') IS NOT NULL) + (NULLIF(s.itemno4, '') IS NOT NULL) + 
                         (NULLIF(s.itemno5, '') IS NOT NULL) > 1 THEN 'Ja' ELSE 'Nej' END";
                break;
            case "linjer":
                $sortFieldSQL = "(NULLIF(s.itemno, '') IS NOT NULL) + (NULLIF(s.itemno2, '') IS NOT NULL) + 
                         (NULLIF(s.itemno3, '') IS NOT NULL) + (NULLIF(s.itemno4, '') IS NOT NULL) + 
                         (NULLIF(s.itemno5, '') IS NOT NULL)";
                break;
            case "created":
                $sortFieldSQL = "s.created_date";
                break;
            case "modtager":
                $sortFieldSQL = "s.shipto_name";
                break;
            case "address1":
                $sortFieldSQL = "s.shipto_address";
                break;
            case "address2":
                $sortFieldSQL = "s.shipto_address2";
                break;
            case "postcode":
                $sortFieldSQL = "s.shipto_postcode";
                break;
            case "city":
                $sortFieldSQL = "s.shipto_city";
                break;
            case "country":
                $sortFieldSQL = "s.shipto_country";
                break;
            case "contact":
                $sortFieldSQL = "s.shipto_contact";
                break;
            case "email":
                $sortFieldSQL = "s.shipto_email";
                break;
            case "phone":
                $sortFieldSQL = "s.shipto_phone";
                break;
            case "handler":
                $sortFieldSQL = "s.handler";
                break;
            case "syncdate":
                $sortFieldSQL = "s.shipment_sync_date";
                break;
            case "consignor":
                $sortFieldSQL = "s.consignor_labelno";
                break;
            case "navorder":
                $sortFieldSQL = "s.nav_order_no";
                break;
            case "deleted":
                $sortFieldSQL = "s.deleted_date";
                break;
        }

        $sortSQL = " ORDER BY ".$sortFieldSQL." ".$sortDir." ";

        // Load company order
        $sqlFields = "SELECT 
    s.id,
    co.order_no ,
    co.company_name ,
    co.order_state ,
    u.n ,
    u.itemno ,
    u.quantity,
    s.shipment_state ,
    CASE 
        WHEN (NULLIF(s.itemno, '') IS NOT NULL) + (NULLIF(s.itemno2, '') IS NOT NULL) +
             (NULLIF(s.itemno3, '') IS NOT NULL) + (NULLIF(s.itemno4, '') IS NOT NULL) +
             (NULLIF(s.itemno5, '') IS NOT NULL) > 1 THEN 'Ja'
        ELSE 'Nej'
    END AS 'samleordre',
    (NULLIF(s.itemno, '') IS NOT NULL) + (NULLIF(s.itemno2, '') IS NOT NULL) +
    (NULLIF(s.itemno3, '') IS NOT NULL) + (NULLIF(s.itemno4, '') IS NOT NULL) +
    (NULLIF(s.itemno5, '') IS NOT NULL) AS 'linjer',
    s.created_date,
    s.shipto_name ,
    s.shipto_address,
    s.shipto_address2 ,
    s.shipto_postcode ,
    s.shipto_city ,
    s.shipto_country ,
    s.shipto_contact ,
    s.shipto_email ,
    s.shipto_phone ,
    s.handler,
    s.shipment_sync_date,
    s.consignor_labelno,
    s.nav_order_no ,
    s.deleted_date ";

        $sql = " FROM shipment s
JOIN company_order co ON s.companyorder_id = co.id
JOIN (
    SELECT id, 1 AS n, itemno, quantity FROM shipment WHERE itemno != '' 
    UNION ALL
    SELECT id, 2, itemno2, quantity2 FROM shipment WHERE itemno2 != '' 
    UNION ALL
    SELECT id, 3, itemno3, quantity3 FROM shipment WHERE itemno3 != '' 
    UNION ALL
    SELECT id, 4, itemno4, quantity4 FROM shipment WHERE itemno4 != '' 
    UNION ALL
    SELECT id, 5, itemno5, quantity5 FROM shipment WHERE itemno5 != ''
) u ON s.id = u.id
WHERE co.shop_id in (".implode(",",$this->shopIDList).") and s.shipment_type = 'earlyorder' ".$searchSQL." ".$sortSQL;


        // Get total count
        $countSQL = "SELECT COUNT(s.id) as totalRows ".$sql;
        $totalRowsResult = \Dbsqli::getSql2($countSQL);
        $totalRows = isset($totalRowsResult[0]) ? $totalRowsResult[0]["totalRows"] : 0;

        // Pagination
        $page = intval($data["page"] ?? 1);
        if($isCSV) {
            $page = 1;
            $pageSize = 1000000;
        } else {
            $pageSize = 250;
        }

        $offset = ($page-1) * $pageSize;

//        echo $sqlFields." ".$sql." LIMIT ".$offset.",".$pageSize;
 //       exit();

        // Get orders
        $shipmentList = \Shipment::find_by_sql($sqlFields." ".$sql." LIMIT ".$offset.",".$pageSize);

        if($isCSV) {

            // Define the headers
            $headers = [
                'Leverance ID', 'Fra ordre', 'Virksomhed',  'Linjenr', 'Varenr', 'Antal',
                'Leverancestatus','Ordrestatus', 'Samleordre', 'Linjer', 'Oprettet', 'Modtager', 'Adresse1', 'Adresse 2',
                'Postnr', 'By', 'Land', 'Kontakt', 'E-mail', 'Mobil', 'Handler', 'Synkroniseret',
                'Consignor label', 'Nav ordrenr.', 'Slettet'
            ];

            // Set headers for CSV output
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="output.csv"');

            // Output BOM for Excel to recognize UTF-8
            echo "\xEF\xBB\xBF";

            // Create a file pointer connected to the output stream
            $output = fopen('php://output', 'w');

            // Set the separator to semicolon for better Excel compatibility
            $separator = ';';

            // Output the headers
            fputcsv($output, $headers, $separator);

            foreach($shipmentList as $order) {
                $this->getOrderListCSVRow($order,$output,$separator);
            }

            // Close the file pointer
            fclose($output);


        }
        else {

            ob_start();

            ?><thead class="thead-light">
            <tr>
                <th data-sortfield="id">Leverance ID</th>
                <th data-sortfield="orderno">Fra ordre</th>
                <th data-sortfield="company">Virksomhed</th>

                <th data-sortfield="linjenr">Linjenr</th>
                <th data-sortfield="itemno">Varenr</th>
                <th data-sortfield="quantity">Antal</th>
                <th data-sortfield="status">Leverancestatus</th>
                <th data-sortfield="orderstate">Ordrestatus</th>
                <th data-sortfield="samleordre">Samleordre</th>
                <th data-sortfield="linjer">Linjer</th>
                <th data-sortfield="created">Oprettet</th>
                <th data-sortfield="modtager">Modtager</th>
                <th data-sortfield="address1">Adresse1</th>
                <th data-sortfield="address2">Adresse 2</th>
                <th data-sortfield="postcode">Postnr</th>
                <th data-sortfield="city">By</th>
                <th data-sortfield="country">Land</th>
                <th data-sortfield="contact">Kontakt</th>
                <th data-sortfield="email">E-mail</th>
                <th data-sortfield="phone">Mobil</th>
                <th data-sortfield="handler">Handler</th>
                <th data-sortfield="syncdate">Synkroniseret</th>
                <th data-sortfield="consignor">Consignor label</th>
                <th data-sortfield="navorder">Nav ordrenr.</th>
                <th data-sortfield="deleted">Slettet</th>
            </tr>

            </thead>
            <tbody><?php

            /*
             <tr>
                <td colspan="20"><pre><?php print_r($data); ?></pre><br>Found: <?php echo countgf($orderList); ?><br><pre><?php echo $sql; ?></pre></td>
            </tr>
             */

            foreach($shipmentList as $order) {
                $this->getOrderListRow($order);
            }

            ?></tbody><?php

            $content = ob_get_contents();
            ob_end_clean();

            echo json_encode(array("status" => 1, "content" => $content,"page" => $page,"totalRows" => $totalRows,"pageSize" => $pageSize,"maxPage" => ceil($totalRows/$pageSize)));

        }

    }

    public function getOrderListCSVRow($order,$output,$separator)
    {

        $row = [
            $order->id,                     // Leverance ID
            $order->order_no,               // Fra ordre
            $order->company_name,           // Virksomhed

            $order->n,                      // Linjenr
            $order->itemno,                 // Varenr
            $order->quantity,               // Antal
            \Shipment::stateTextList($order->shipment_state), // Status
            \CompanyOrder::stateTextList($order->order_state), // Ordrestatus
            $order->samleordre,             // Samleordre
            $order->linjer,                 // Linjer
            $order->created_date->format('Y-m-d H:i:s'), // Oprettet
            $order->shipto_name,            // Modtager
            $order->shipto_address,         // Adresse1
            $order->shipto_address2,        // Adresse 2
            $order->shipto_postcode,        // Postnr
            $order->shipto_city,            // By
            $this->countryString($order->shipto_country),         // Land
            $order->shipto_contact,         // Kontakt
            $order->shipto_email,           // E-mail
            $order->shipto_phone,           // Mobil
            $order->handler,                // Handler
            $order->shipment_sync_date ? $order->shipment_sync_date->format('Y-m-d H:i:s') : '', // Synkroniseret
            $order->consignor_labelno,      // Consignor label
            $order->nav_order_no,           // Nav ordrenr.
            $order->deleted_date ? $order->deleted_date->format('Y-m-d H:i:s') : '' // Slettet
        ];


        fputcsv($output, $row, $separator);

    }

    public function getOrderListRow($order)
    {

        ?><tr>
        <td><?php echo htmlspecialcharsgf($order->id); ?></td>
        <td><?php echo htmlspecialcharsgf($order->order_no); ?></td>
        <td><?php echo htmlspecialcharsgf($order->company_name); ?></td>

        <td><?php echo htmlspecialcharsgf($order->n); ?></td>
        <td><?php echo htmlspecialcharsgf($order->itemno); ?></td>
        <td><?php echo htmlspecialcharsgf($order->quantity); ?></td>
        <td><?php echo htmlspecialcharsgf(\Shipment::stateTextList($order->shipment_state)); ?></td>
        <td><?php echo htmlspecialcharsgf(\CompanyOrder::stateTextList($order->order_state)); ?></td>
        <td><?php echo htmlspecialcharsgf($order->samleordre); ?></td>
        <td><?php echo htmlspecialcharsgf($order->linjer); ?></td>
        <td><?php echo htmlspecialcharsgf($order->created_date->format('Y-m-d H:i:s')); ?></td>
        <td><?php echo htmlspecialcharsgf($order->shipto_name); ?></td>
        <td><?php echo htmlspecialcharsgf($order->shipto_address); ?></td>
        <td><?php echo htmlspecialcharsgf($order->shipto_address2); ?></td>
        <td><?php echo htmlspecialcharsgf($order->shipto_postcode); ?></td>
        <td><?php echo htmlspecialcharsgf($order->shipto_city); ?></td>
        <td><?php echo htmlspecialcharsgf($this->countryString($order->shipto_country)); ?></td>
        <td><?php echo htmlspecialcharsgf($order->shipto_contact); ?></td>
        <td><?php echo htmlspecialcharsgf($order->shipto_email); ?></td>
        <td><?php echo htmlspecialcharsgf($order->shipto_phone); ?></td>
        <td><?php echo htmlspecialcharsgf($order->handler); ?></td>
        <td><?php echo $order->shipment_sync_date ? htmlspecialcharsgf($order->shipment_sync_date->format('Y-m-d H:i:s')) : ''; ?></td>
        <td><?php echo htmlspecialcharsgf($order->consignor_labelno); ?></td>
        <td><?php echo htmlspecialcharsgf($order->nav_order_no); ?></td>
        <td><?php echo $order->deleted_date ? htmlspecialcharsgf($order->deleted_date->format('Y-m-d H:i:s')) : ''; ?></td>
        </tr><?php


    }

    private function countryString($country) {
        if($country == 1) return "DK";
        else if($country == 4) return "NO";
        else if($country == 5) return "SE";
        return $country;
    }

    public function getOrderListData()
    {

        $shoplist = [];
        foreach($this->shopSettingsList as $settings) {
            $shoplist[] = array("value" => $settings->shop_id,"text" => $settings->concept_code);
        }

        return array(
            "shops" => $shoplist,

            "orderStateList" => \CompanyOrder::getOrderStateList()
        );


    }

}