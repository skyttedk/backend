<?php

namespace GFUnit\cardshop\reports;
use GFBiz\units\UnitController;

class OrderListModel
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

        // Load deadlines
        $this->expireDates = \ExpireDate::find_by_sql("SELECT * FROM `expire_date` where id in (select expire_date_id from cardshop_expiredate where shop_id in (".implode(",",$this->shopIDList).")) order by expire_date asc");
        $this->expireDateList = [];
        foreach($this->expireDates as $expireDate) {
            $this->expireDateList[] = $expireDate->expire_date->format("Y-m-d");
        }

        // Load salesperson
        $salesPersons = \CompanyOrder::find_by_sql("SELECT salesperson FROM company_order where shop_id in (SELECT shop_id FROM cardshop_settings where language_code in (".implode(",",$this->languageCodes).")) GROUP BY salesperson ORDER BY salesperson ASC");
        $this->salesPersonList = [];
        foreach($salesPersons as $salesPerson) {
            $this->salesPersonList[] = $salesPerson->salesperson;
        }

    }

    
    private $salesPersonList;
    private $expireDates;
    private $expireDateList;
    
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

        if(!isset($data["action"]) || ($data["action"]) != "orderlistprovider") {
            $this->abortError("Fejl i kriterier!");
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

            $searchSQL .= " and (co.order_no LIKE '".$filterSearch."' OR c.name LIKE '".$filterSearch."' OR c.cvr LIKE '".$filterSearch."' OR c.contact_name LIKE '".$filterSearch."' OR c.contact_phone LIKE '".$filterSearch."' OR c.contact_email LIKE '".$filterSearch."' OR s.name LIKE '".$filterSearch."') ";

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

        // Expire date
        $expireDateList = $data["deadline"] ?? array();
        if(count($expireDateList) > 0) {
            $expireSQLList = [];

            foreach($expireDateList as $expireDate)
            {

                $expireDate = trimgf($expireDate);

                if(in_array($expireDate,$this->expireDateList)) {
                    $expireSQLList[] = "'".$expireDate."'";
                }

            }

            if(count($expireSQLList) > 0) {
                $searchSQL .= " and co.expire_date IN (".implode(",",$expireSQLList).") ";
            }

        }

        // Salesperson
        $salesPersonList = $data["seller"] ?? array();
        if(count($salesPersonList) > 0) {
            $salesPersonSQLList = [];

            foreach($salesPersonList as $salesPerson)
            {

                $salesPerson = trimgf($salesPerson);

                if(in_array($salesPerson,$this->salesPersonList)) {
                    $salesPersonSQLList[] = "'".$salesPerson."'";
                }

            }

            if(count($salesPersonSQLList) > 0) {
                $searchSQL .= " and co.salesperson IN (".implode(",",$salesPersonSQLList).") ";
            }

        }

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
        $sortFieldSQL = "co.order_no";
        $sortDir = ($data["sortDir"] == "asc") ? "ASC" : "DESC";


        $sortField = $data["sortField"] ?? "";
        if($sortField == "id") {
            $sortFieldSQL = "co.id";
        } else if($sortField == "orderno") {
            $sortFieldSQL = "co.order_no";
        } else if($sortField == "cvr") {
            $sortFieldSQL = "c.cvr";
        } else if($sortField == "company") {
            $sortFieldSQL = "c.name";
        } else if($sortField == "seller") {
            $sortFieldSQL = "co.salesperson";
        } else if($sortField == "concept") {
            $sortFieldSQL = "s.name";
        } else if($sortField == "quantity") {
            $sortFieldSQL = "co.quantity";
        } else if($sortField == "expiredate") {
            $sortFieldSQL = "co.expire_date";
        } else if($sortField == "type") {
            $sortFieldSQL = "co.is_email";
        } else if($sortField == "start") {
            $sortFieldSQL = "co.certificate_no_begin";
        } else if($sortField == "end") {
            $sortFieldSQL = "co.certificate_no_end";
        } else if($sortField == "created") {
            $sortFieldSQL = "co.created_datetime";
        } else if($sortField == "state") {
            $sortFieldSQL = "co.order_state";
        }  else if($sortField == "delivery") {
            $sortFieldSQL = "is_delivery ".$sortDir.", (SELECT COUNT(DISTINCT su2.company_id) 
     FROM shop_user su2 
     WHERE su2.company_order_id = co.id AND su2.blocked = 0)";
        } else if($sortField == "contact") {
            $sortFieldSQL = "c.contact_name";
        } else if($sortField == "email") {
            $sortFieldSQL = "c.contact_email";
        } else if($sortField == "phone") {
            $sortFieldSQL = "c.contact_phone";
        } else if($sortField == "orders") {
            $sortFieldSQL = " (SELECT COUNT(DISTINCT o.id) 
     FROM shop_user su2 
     LEFT JOIN `order` o ON o.shopuser_id = su2.id 
     WHERE su2.company_order_id = co.id AND su2.blocked = 0)";
        } else if($sortField == "blocked") {
            $sortFieldSQL = "(SELECT COUNT(DISTINCT su2.id) 
     FROM shop_user su2 
     WHERE su2.company_order_id = co.id AND su2.blocked = 1)";
        } else if($sortField == "noorder") {
            $sortFieldSQL = "co.quantity - (SELECT COUNT(DISTINCT o.id) 
     FROM shop_user su2 
     LEFT JOIN `order` o ON o.shopuser_id = su2.id 
     WHERE su2.company_order_id = co.id AND su2.blocked = 0) - (SELECT COUNT(DISTINCT su2.id) 
     FROM shop_user su2 
     WHERE su2.company_order_id = co.id AND su2.blocked = 1)";
        } else if($sortField == "earlyorder") {
            $sortFieldSQL = "(SELECT COUNT(DISTINCT sh.id) 
     FROM shipment sh 
     WHERE sh.companyorder_id = co.id AND sh.shipment_type = 'earlyorder' AND sh.shipment_state IN (1,2,3,5,6))";
        }

        $sortSQL = " ORDER BY ".$sortFieldSQL." ".$sortDir." ";

        // Load company order

        $sqlFields = "SELECT co.id as company_order_id, 
    co.order_no, 
    co.shop_id, 
    co.salesperson, 
    co.quantity, 
    co.expire_date, 
    co.is_email, 
    co.certificate_no_begin, 
    co.certificate_no_end, 
    co.floating_expire_date, 
    co.created_datetime, 
    co.order_state, 
    c.name, 
    c.cvr as company_cvr, 
    c.contact_name as company_contact_name, 
    c.contact_phone as company_contact_phone, 
    c.contact_email as company_contact_email, 
    s.name as shop_name,
    ed.is_delivery,
    co.welcome_mail_is_send,
    co.certificate_value,
    co.card_values,
    (SELECT COUNT(DISTINCT o.id) 
     FROM shop_user su2 
     LEFT JOIN `order` o ON o.shopuser_id = su2.id 
     WHERE su2.company_order_id = co.id AND su2.blocked = 0) as active_orders_count,
    (SELECT COUNT(DISTINCT su2.id) 
     FROM shop_user su2 
     WHERE su2.company_order_id = co.id AND su2.blocked = 1) as blocked_shopusers_count,
    (SELECT COUNT(DISTINCT su2.company_id) 
     FROM shop_user su2 
     WHERE su2.company_order_id = co.id AND su2.blocked = 0) as distinct_delivery_addresses_count,
    (SELECT COUNT(DISTINCT sh.id) 
     FROM shipment sh 
     WHERE sh.companyorder_id = co.id AND sh.shipment_type = 'earlyorder' AND sh.shipment_state IN (1,2,3,5,6)) as earlyorder_count,
    (SELECT COUNT(DISTINCT sh.id) 
     FROM shipment sh 
     WHERE sh.companyorder_id = co.id AND sh.shipment_type = 'giftcard' AND sh.shipment_state IN (2,5,6)) as giftcard_sent_count ";

        $sql = " FROM 
    `company_order` co
    INNER JOIN company c ON c.id = co.company_id
    INNER JOIN shop s ON s.id = co.shop_id
    INNER JOIN expire_date ed on ed.expire_date = co.expire_date
WHERE co.shop_id in (".implode(",",$this->shopIDList).") ".$searchSQL." ".$sortSQL;

        // Get total count
        $countSQL = "SELECT COUNT(co.id) as totalRows ".$sql;
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

        // Get orders
        $orderList = \CompanyOrder::find_by_sql($sqlFields." ".$sql." LIMIT ".$offset.",".$pageSize);

        if($isCSV) {

            // Define the headers
            $headers = [
                'ID', 'BS nr', 'Virksomhed', 'CVR', 'Sælger', 'Koncept', 'Antal', 'Deadline', 'Type',
                'Start nr', 'Slut nr', 'Oprettet', 'Ordre status', 'Koder sendt', 'Leveringsadresser',
                'Kontakt navn', 'Kontakt e-mail', 'Kontakt telefon', 'Gaver valgt', 'Lukkede kort',
                'Uden valg', 'Earlyordre'
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

            foreach($orderList as $order) {
                $this->getOrderListCSVRow($order,$output,$separator);
            }

            // Close the file pointer
            fclose($output);


        }
        else {

        ob_start();

        ?><thead class="thead-light">
    <tr>
        <th data-sortfield="id">ID</th>
        <th data-sortfield="orderno">BS nr</th>
        <th data-sortfield="company">Virksomhed</th>
        <th data-sortfield="cvr">CVR</th>
        <th data-sortfield="seller">Sælger</th>
        <th data-sortfield="concept">Koncept</th>
        <th data-sortfield="value">Beløb</th>
        <th data-sortfield="quantity">Antal</th>
        <th data-sortfield="expiredate">Deadline</th>
        <th data-sortfield="type">Type</th>
        <th data-sortfield="start">Start nr</th>
        <th data-sortfield="end">Slut nr</th>
        <th data-sortfield="created">Oprettet</th>
        <th data-sortfield="state">Ordre status</th>
        <th data-sortfield="sent">Koder sendt</th>
        <th data-sortfield="delivery">Leveringsadresser</th>
        <th data-sortfield="contact">Kontakt navn</th>
        <th data-sortfield="email">Kontakt e-mail</th>
        <th data-sortfield="phone">Kontakt telefon</th>
        <th data-sortfield="orders">Gaver valgt</th>
        <th data-sortfield="blocked">Lukkede kort</th>
        <th data-sortfield="noorder">Uden valg</th>
        <th data-sortfield="earlyorder">Earlyordre</th>
    </tr>
    </thead>
        <tbody><?php

        /*
         <tr>
            <td colspan="20"><pre><?php print_r($data); ?></pre><br>Found: <?php echo countgf($orderList); ?><br><pre><?php echo $sql; ?></pre></td>
        </tr>
         */

        foreach($orderList as $order) {
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
            $order->company_order_id,
            $order->order_no,
            $order->name,
            $order->company_cvr,
            $order->salesperson,
            $order->shop_name,
            $order->card_values == null ? $order->certificate_value : $order->card_values,
            $order->quantity,
            $order->expire_date->format('Y-m-d'),
            $order->is_email == 1 ? "E-mail" : "Fysisk",
            $order->certificate_no_begin,
            $order->certificate_no_end,
            $order->created_datetime->format('Y-m-d'),
            \CompanyOrder::stateTextList($order->order_state),
            (($order->is_email == 1) ? $order->welcome_mail_is_send : ($order->giftcard_sent_count > 0)) ? "Sendt" : "Ikke sendt",
            $order->is_delivery == 1 ? "Privatlevering" : $order->distinct_delivery_addresses_count,
            $order->company_contact_name,
            $order->company_contact_email,
            $order->company_contact_phone,
            $order->active_orders_count,
            $order->blocked_shopusers_count,
            $order->quantity - $order->blocked_shopusers_count - $order->active_orders_count,
            $order->earlyorder_count
        ];

        fputcsv($output, $row, $separator);

    }

    public function getOrderListRow($order)
    {

        ?><tr>
            <td><?php echo $order->company_order_id; ?></td>
            <td><?php echo $order->order_no; ?></td>
            <td><?php echo $order->name; ?></td>
            <td><?php echo $order->company_cvr; ?></td>
            <td><?php echo $order->salesperson; ?></td>
            <td><?php echo $order->shop_name; ?></td>
            <td><?php echo $order->card_values == null ? $order->certificate_value : $order->card_values; ?></td>
            <td><?php echo $order->quantity; ?></td>
            <td><?php echo $order->expire_date->format('Y-m-d'); ?></td>
            <td><?php echo $order->is_email == 1 ? "E-mail" : "Fysisk"; ?></td>
            <td><?php echo $order->certificate_no_begin; ?></td>
            <td><?php echo $order->certificate_no_end; ?></td>
            <td><?php echo $order->created_datetime->format('Y-m-d'); ?></td>
            <td><?php echo \CompanyOrder::stateTextList($order->order_state); ?></td>
            <td><?php echo (($order->is_email == 1) ? $order->welcome_mail_is_send : ($order->giftcard_sent_count > 0)) ? "Sendt" : "Ikke sendt"; ?></td>
            <td><?php echo $order->is_delivery == 1 ? "Privatlevering" : $order->distinct_delivery_addresses_count; ?></td>
            <td><?php echo $order->company_contact_name; ?></td>
            <td><?php echo $order->company_contact_email; ?></td>
            <td><?php echo $order->company_contact_phone; ?></td>
            <td><?php echo $order->active_orders_count; ?></td>
            <td><?php echo $order->blocked_shopusers_count; ?></td>
            <td><?php echo $order->quantity-$order->blocked_shopusers_count-$order->active_orders_count; ?></td>
            <td><?php echo $order->earlyorder_count; ?></td>
        </tr><?php

    }

    public function getOrderListData()
    {

        $shoplist = [];
        foreach($this->shopSettingsList as $settings) {
            $shoplist[] = array("value" => $settings->shop_id,"text" => $settings->concept_code);
        }

        return array(
            "shops" => $shoplist,
            "expireDates" => $this->expireDates,
            "orderStateList" => \CompanyOrder::getOrderStateList(),
            "salespersons" => $this->salesPersonList,
        );


    }


}