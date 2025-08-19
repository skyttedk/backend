<?php

namespace GFUnit\lister\rapporter;

class Salesperson
{

    const DEBUG = true;


    public function dispatch() {

        if(!isset($_GET["token"]) || $_GET["token"] != "dsfsdjewjlfhl3rnfdhj4tgfsdahfjkghsfldjd") {
            echo "Sorry, you need admin access";
            exit();
        }

        if(isset($_POST["action"]) && $_POST["action"] == "download" && isset($_POST["language"]) && intval($_POST["language"]) > 0) {
            $this->downloadReport();
        }
        else if(isset($_POST["action"]) && $_POST["action"] == "salesreport" && isset($_POST["language"]) && intval($_POST["language"]) > 0 && isset($_POST["salesperson"]) && trim($_POST["salesperson"]) != "") {
            $this->downloadSalespersonReport();
        } else {
            $this->showForm();
        }

    }

    public function showForm()
    {
        ?><html>
            <head>

            </head>
            <body>

                <form method="post" action="">
                    <h1>Sælger rapport</h1>
                    <p>
                        Vælg land:
                        <select name="language">
                            <option value="1">Danmark</option>
                            <option value="4">Norge</option>
                            <option value="5">Sverige</option>
                        </select>
                    </p>
                    <p>
                        <input type="hidden" name="action" value="download">
                        <button>Hent rapport</button>
                    </p>

                </form>


                <form method="post" action="">
                    <h1>Sælger rapport</h1>
                    <p>
                        Vælg land:
                        <select name="language">
                            <option value="1">Danmark</option>
                            <option value="4">Norge</option>
                            <option value="5">Sverige</option>
                        </select>
                    </p>
                    <p>
                        Sælger kode:
                        <input type="text" style="width: 100px;" name="salesperson">
                    </p>
                    <p>
                        <input type="hidden" name="action" value="salesreport">
                        <button>Hent sælger rapport</button>
                    </p>

                </form>

            </body>
    </html><?php

    }


    /* MEMBERS */

    private $salesPersonData;

    private $cardshopSettingsMap;

    private $companyOrderItemMap;

    private $expireDateMap;

    private $reportSalesperson = "";
    private $reportSalespersonContent = array();

    public function downloadSalespersonReport() {

        $this->reportSalesperson = trim($_POST["salesperson"]);
        $this->generateReportData();

        echo "Sælger rapport for ".$this->reportSalesperson."<br><br>";
        foreach($this->reportSalespersonContent as $line) {
            echo $line."<br>";
        }



    }

    public function downloadReport() {

        //echo "<pre>".print_r($this->salesPersonData,true)."</pre>";

        $this->generateReportData();

        // Generate report
        $this->generateReport();
    }

    private function generateReportData() {

        $this->salesPersonData = array();

        $language = intval($_POST["language"]);
        $this->debugLog("Download rapport with language id: ".$language);

        // Load all cardshop settings
        $cardshopSettingsList = \CardshopSettings::find('all');
        $this->cardshopSettingsMap = array();
        foreach($cardshopSettingsList as $cs) {
            $this->cardshopSettingsMap[$cs->shop_id] = $cs;
        }
        $this->debugLog("Loaded ".count($this->cardshopSettingsMap)." cardshop settings rows");

        // Load all orders
        $companyOrderList = \CompanyOrder::find_by_sql("SELECT * FROM `company_order` where order_state > 3 && shop_id in (select shop_id from cardshop_settings where language_code = ".$language.") && salesperson not in ('','IMPORT','US') && company_name NOT LIKE '%TEST%' ORDER BY `company_order`.`salesperson` ASC");
        $companyOrderIDList = array();

        foreach($companyOrderList as $co) {
            $companyOrderIDList[] = $co->id;
        }

        $this->loadCompanyOrderData($companyOrderIDList);

        foreach($companyOrderList as $companyOrder) {
            $this->processOrder($companyOrder);
        }

        // Post process
        $this->postProcessSalespersons();

    }

    private function loadCompanyOrderData($companyOrderIDList) {

        // Early orders
        $earlyOrderSQL = "SELECT company_order.salesperson, SUM(shipment.quantity+shipment.quantity2+shipment.quantity3+shipment.quantity4+shipment.quantity5) as earlyorderitems, COUNT(DISTINCT company_order.id) as earlyorderorders  FROM `shipment`, company_order where shipment.companyorder_id = company_order.id && company_order.id in (".implode(",",$companyOrderIDList).") && shipment_type = 'earlyorder' && shipment_state = 2 GROUP BY company_order.salesperson";
        $earlyOrderList = \Shipment::find_by_sql($earlyOrderSQL);
        foreach($earlyOrderList as $eo) {
            $this->addSalespersonData($eo->salesperson,"EARLYORDERITEMS",$eo->earlyorderitems);
            $this->addSalespersonData($eo->salesperson,"EARLYORDERORDERS",$eo->earlyorderorders);
        }

        // Load items
        $companyOrderItems = \CompanyOrderItem::find_by_sql("SELECT * FROM company_order_item WHERE company_order_item.companyorder_id in (".implode(",",$companyOrderIDList).")");
        $this->companyOrderItemMap = array();
        foreach($companyOrderItems as $coi) {
            $this->companyOrderItemMap[$coi->companyorder_id][$coi->type] = array("active" => $coi->quantity > 0, "quantity" => $coi->quantity, "price" => $coi->quantity == 0 ? 0 : $coi->price);
        }

        // Load expire dates
        $expireDateList = \ExpireDate::find('all');
        foreach($expireDateList as $expireDate) {
            $this->expireDateMap[$expireDate->expire_date->format('Y-m-d')] = $expireDate;
        }
    }

    private function isPrivateDelivery($expireDate) {
        if(!isset($this->expireDateMap[$expireDate])) return false;
        return $this->expireDateMap[$expireDate]->is_delivery == 1;
    }

    private function processOrder(\CompanyOrder $companyOrder)
    {


        $sp = $companyOrder->salesperson;

        $logSalesPerson = false;
        if($sp == $this->reportSalesperson) {
            //$this->reportSalespersonContent[] = "Ordre ".$companyOrder->order_no.": ".$companyOrder->company_name;
            $this->reportSalespersonContent[] = "Ordre ".$companyOrder->order_no.": ";
            $logSalesPerson = true;
        }

        $items = $this->companyOrderItemMap[$companyOrder->id];
        $settings = $this->cardshopSettingsMap[$companyOrder->shop_id];

        // Add count
        $this->addSalespersonData($sp,"ORDERCOUNT");

        // Add quantity
        $this->addSalespersonData($sp,"ORDERQUANTITY",$companyOrder->quantity);
        if($logSalesPerson) $this->reportSalespersonContent[] = "Antal kort: ".$companyOrder->quantity;

        // Cancelled orders
        if($companyOrder->order_state == 8) {
            $this->addSalespersonData($sp, "ORDERCANCELLED");
            if($logSalesPerson) $this->reportSalespersonContent[] = "Ordren er annulleret";
        }

        // Mail of physical
        if($companyOrder->is_email == 0) {
            $this->addSalespersonData($sp,"ORDERPHYSICAL");
            if($logSalesPerson) $this->reportSalespersonContent[] = "Fysiske kort";
        }

        // Certificate value
        $this->addSalespersonData($sp,"ORDERCERTIFICATEVALUETOTALSUM",$companyOrder->quantity*$companyOrder->certificate_value);
        $this->addSalespersonData($sp,"ORDERCERTIFICATEVALUESUM",$companyOrder->certificate_value);
        if($logSalesPerson) $this->reportSalespersonContent[] = "Kort værdi: ".$companyOrder->certificate_value." total værdi af ordre: ".$companyOrder->quantity*$companyOrder->certificate_value;

        if($items["CONCEPT"]["price"] < $companyOrder->certificate_value*100) {
            $this->addSalespersonData($sp,"ORDERCERTIFICATEDISCOUNTS");
            $this->addSalespersonData($sp,"ORDERCERTIFICATEDISCOUNTPRICE",($companyOrder->certificate_value-$items["CONCEPT"]["price"]/100)*$companyOrder->quantity);
            if($logSalesPerson) $this->reportSalespersonContent[] = "Der er givet rabat på kortene, pris sat til: ".$items["CONCEPT"]["price"]/100;
        }

        // Giftwrap
        if($companyOrder->giftwrap == 1) {
            $this->addSalespersonData($sp, "USEGIFTWRAP");
            if($logSalesPerson) $this->reportSalespersonContent[] = "Med indpakning";

            if($items["GIFTWRAP"]["price"] == 0 || !$items["GIFTWRAP"]["active"]) {
                $this->addSalespersonData($sp,"GIFTWRAPFREE");
                $this->addSalespersonData($sp,"GIFTWRAPDISCOUNTPRICE",$items["GIFTWRAP"]["price"]/100*$companyOrder->quantity);
                if($logSalesPerson) $this->reportSalespersonContent[] = "Gratis indpakning: ";
            } else if($settings->giftwrap_price > $items["GIFTWRAP"]["price"]) {
                $this->addSalespersonData($sp,"GIFTWRAPDISCOUTNS");
                $this->addSalespersonData($sp,"GIFTWRAPDISCOUNTPRICE",($settings->giftwrap_price-$items["GIFTWRAP"]["price"])/100*$companyOrder->quantity);
                if($logSalesPerson) $this->reportSalespersonContent[] = "Rabat på indpakning: ".$items["GIFTWRAP"]["price"]/100;
            }

        }

        // Special delivery
        if($companyOrder->gift_spe_lev == 1 || $companyOrder->dot == 1) {

            $this->addSalespersonData($sp, "USESPECIALDELIVERY");
            if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre er med speciallevering";

            if($companyOrder->gift_spe_lev == 1 && ($settings->carryup_price > $items["CARRYUP"]["price"] || !$items["CARRYUP"]["active"])) {
                if($items["CARRYUP"]["active"] == 0) $items["CARRYUP"]["price"] = 0;
                $this->addSalespersonData($sp, "CARRYUPDISCOUNTS");
                $this->addSalespersonData($sp, "CARRYUPDISCOUNTPRICE",($settings->carryup_price-$items["CARRYUP"]["price"])/100);
                if($logSalesPerson) $this->reportSalespersonContent[] = "Rabat på opbæring: ".$items["CARRYUP"]["price"];
            }

            if($companyOrder->dot == 1 && ($settings->dot_price > $items["DOT"]["price"] || !$items["DOT"]["active"])) {
                if($items["DOT"]["active"] == 0) $items["DOT"]["price"] = 0;
                $this->addSalespersonData($sp, "DOTDISCOUNTS");
                $this->addSalespersonData($sp, "DOTDISCOUNTPRICE",($settings->dot_price-$items["DOT"]["price"])/100);
                if($logSalesPerson) $this->reportSalespersonContent[] = "Rabat på DOT: ".$items["DOT"]["price"];
            }

        }

        // Free cards
        if($companyOrder->free_cards > 0) {
            $this->addSalespersonData($sp,"FREECARDORDERS");
            $this->addSalespersonData($sp,"FREECARDSTOTAL",$companyOrder->free_cards);
            if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har gratis kort: ".$companyOrder->free_cards;
        }

        // Prepayment
        $noInvoiceFeeCounted = false;
        if($companyOrder->prepayment == 0) {
            $this->addSalespersonData($sp,"NOPREPAYMENT");
            if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har ikke forudfakturering";
        } else if($settings->invoiceinitial_use > 0 && $settings->invoiceinitial_price > 0) {
            if($settings->invoiceinitial_price > $items["INVOICEFEEINITIAL"]["price"]) {

                if($items["INVOICEFEEINITIAL"]["price"] == 0) {
                    $this->addSalespersonData($sp,"NOINVOICEFEE");
                    $noInvoiceFeeCounted = true;
                    if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har ikke fakturagebyr";
                } else {
                    $this->addSalespersonData($sp, "INVOICEFEEINITIALDISCOUNTS");
                    $this->addSalespersonData($sp, "INVOICEFEEINITIALDISCOUNTPRICE",($settings->invoiceinitial_price-$items["INVOICEFEEINITIAL"]["price"])/100);
                    if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre rabat på fakturagebyr: ".(($settings->invoiceinitial_price-$items["INVOICEFEEINITIAL"]["price"])/100);
                }


            }
        }

        if($settings->invoicefinal_use > 0 && $settings->invoicefinal_price > 0) {
            if($settings->invoicefinal_price > $items["INVOICEFEEFINAL"]["price"]) {

                if($items["INVOICEFEEFINAL"]["price"] == 0) {
                    $this->addSalespersonData($sp,"NOINVOICEFEE");
                    $noInvoiceFeeCounted = true;
                    if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har ikke fakturagebyr";
                } else {
                    $this->addSalespersonData($sp, "INVOICEFEEFINALDISCOUNTS");
                    $this->addSalespersonData($sp, "INVOICEFEEFINALDISCOUNTPRICE",($settings->invoicefinal_price-$items["INVOICEFEEFINAL"]["price"])/100);
                    if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har rabat på fakturagebyr: ".$items["INVOICEFEEFINAL"]["price"]/100;
                }

            }
        }

        // Excempt invoicefee
        if(!$noInvoiceFeeCounted && $companyOrder->excempt_invoicefee == 1) {
            $this->addSalespersonData($sp,"NOINVOICEFEE");
            if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har ikke fakturagebyr";
        }

        // Excempt environment fee
        if($companyOrder->excempt_envfee == 1) {
            $this->addSalespersonData($sp,"NOENVFEE");
            if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har ikke miljøgebyr";
        }

        // Private delivery
        if($settings->privatedelivery_use > 0 && $this->isPrivateDelivery($companyOrder->expire_date->format('Y-m-d'))) {

            $this->addSalespersonData($sp, "PRIVATEDELIVERYORDERS");
            $this->addSalespersonData($sp, "PRIVATEDELIVERYCARDS",$companyOrder->quantity);
            if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre er privatlevering";

            if(($settings->privatedelivery_price > $items["PRIVATEDELIVERY"]["price"] || !$items["PRIVATEDELIVERY"]["active"])) {
                if(!$items["PRIVATEDELIVERY"]["active"]) $items["PRIVATEDELIVERY"]["price"] = 0;
                $this->addSalespersonData($sp, "PRIVATEDELIVERYDISCOUNTS");
                $this->addSalespersonData($sp, "PRIVATEDELIVERYDISCOUNTPRICE",($settings->privatedelivery_price-$items["PRIVATEDELIVERY"]["price"])/100*$companyOrder->quantity);
                if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har rabat på privatlevering: ".($items["PRIVATEDELIVERY"]["price"])/100;
            }

        }

        // Cardfee


        // Carddelivery
        if($companyOrder->is_email == 0 && $settings->carddelivery_use > 0) {
            if($settings->carddelivery_price > $items["CARDDELIVERY"]["price"]) {
                $this->addSalespersonData($sp, "CARDDELIVERYDISCOUNTS");
                $this->addSalespersonData($sp, "CARDDELIVERYDISCOUNTPRICE",($settings->carddelivery_price-$items["CARDDELIVERY"]["price"])/100);
                if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har rabat på kortlevering: ".($items["CARDDELIVERY"]["price"])/100;
            }
        }

        if($companyOrder->is_email == 0 && $settings->cardfee_use > 0) {

            if($settings->cardfee_price > $items["CARDFEE"]["price"]) {
                $this->addSalespersonData($sp, "CARDFEEDISCOUNTS");
                $this->addSalespersonData($sp, "CARDFEEDISCOUNTPRICE",($settings->cardfee_price-$items["CARDFEE"]["price"])/100*($settings->cardfee_percard == 1 ? $companyOrder->quantity : 1));
                if($logSalesPerson) $this->reportSalespersonContent[] = "Ordre har rabat på kortgebyr: ".($items["CARDFEE"]["price"])/100;
            }
        }

        if($logSalesPerson) $this->reportSalespersonContent[] = "";
    }

    private $fields = array();

    private function addSalespersonData($salesperson,$key,$value=1)
    {

        $this->fields[$key] = true;
        $salesperson = trim(strtoupper($salesperson));

        if(!isset($this->salesPersonData[$salesperson])) {
            $this->salesPersonData[$salesperson] = array();
        }

        if(!isset($this->salesPersonData[$salesperson][$key])) {
            $this->salesPersonData[$salesperson][$key] = 0;
        }

        $this->salesPersonData[$salesperson][$key] += $value;

    }

    private function postProcessSalespersons() {

        foreach($this->salesPersonData as $key => $data) {
            $this->postProcessSalesPerson($key);
        }
    }

    private function postProcessSalesPerson($salesperson) {

        if($this->salesPersonData[$salesperson]["ORDERCOUNT"] > 0) {
            $this->salesPersonData[$salesperson]["ORDERCERTIFICATEVALUEAVG"] = ($this->salesPersonData[$salesperson]["ORDERCERTIFICATEVALUESUM"] ?? 0) / $this->salesPersonData[$salesperson]["ORDERCOUNT"];
            $this->salesPersonData[$salesperson]["ORDERQUANTITYAVG"] = ($this->salesPersonData[$salesperson]["ORDERQUANTITY"] ?? 0) / $this->salesPersonData[$salesperson]["ORDERCOUNT"];
            $this->salesPersonData[$salesperson]["GIFTWRAPPERCENTAGE"] = (($this->salesPersonData[$salesperson]["USEGIFTWRAP"] ?? 0) / $this->salesPersonData[$salesperson]["ORDERCOUNT"])*100;
            $this->salesPersonData[$salesperson]["PRIVATEDELIVERYPERCENTAGE"] = (($this->salesPersonData[$salesperson]["PRIVATEDELIVERYORDERS"] ?? 0) / $this->salesPersonData[$salesperson]["ORDERCOUNT"])*100;
            $this->salesPersonData[$salesperson]["ORDERPHYSICALPERCENTAGE"] = (($this->salesPersonData[$salesperson]["ORDERPHYSICAL"] ?? 0) / $this->salesPersonData[$salesperson]["ORDERCOUNT"])*100;
        } else {
            $this->salesPersonData[$salesperson]["ORDERCERTIFICATEVALUEAVG"] = 0;
            $this->salesPersonData[$salesperson]["ORDERQUANTITYAVG"] = 0;
            $this->salesPersonData[$salesperson]["GIFTWRAPPERCENTAGE"] = 0;
            $this->salesPersonData[$salesperson]["PRIVATEDELIVERYPERCENTAGE"] = 0;
            $this->salesPersonData[$salesperson]["ORDERPHYSICALPERCENTAGE"] = 0;
        }



    }

    /* GENERATE REPORT */

    private function generateReport() {

     /*
        echo "<pre>";
        print_r($this->fields);
        echo "</pre>";
        exit();
*/
        $this->reportHeader();

        // For each
        foreach($this->salesPersonData as $salesPerson => $personData) {
            $this->outputSalesperson($salesPerson,$personData);
        }

    }

    private function reportHeader() {

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="cards.csv"');


        $headers = array("","Kortsalg generelt","","","","","","","Kort rabat","","Gebyrer","","","","","","Gratis kort","","Earlyordre","","Privatlevering","","","","","Indpakning","","","","","Levering af fysiske kort","","Speciallevering","","","","","Andet","");
        echo utf8_decode(implode(";",$headers)."\r\n");

        $headers = array("Salesperson","Antal ordre","Antal kort solgt","Gns antal kort pr. ordre","Gns kort pris","Total kort beløb solgt for","Annullerede ordre","% ordre som er fysiske kort","Antal med rabat på kortpris","Total rabat på kort","Ingen fakturagebyr","Antal med rabat på fakturagebyr","Total fakturagebyr rabat","Ingen miljøgebyr","Ordre med kortgebyr rabat","Total rabat på kort gebyrer","Antal ordre med gratis kort","Total antal gratis kort","Antal ordre med earlyorders","Total antal earlyorder gaver","Antal privatleveringsordre","Antal privatleveringskort","% ordre med privatlevering","Antal med rabat på privatlevering","Total rabat på privatlevering","Med indpakning","% ordre med indpakning","Antal med gratis indpakning","Antal med rabat på indpakning","Total rabat på indpakning","Antal ordre med rabat på levering","Total rabat på levering af kort","Ordre med speciallevering","Antal med DOT rabat","Total DOT rabat","Antal med opbæringsrabat","Total opbæringsrabat","Ordre uden forudfakturering");
        echo utf8_decode(implode(";",$headers)."\r\n");

    }

    private function outputSalesperson($salesperson,$personData) {

        $dataKeys = array("ORDERCOUNT","ORDERQUANTITY","ORDERQUANTITYAVG","ORDERCERTIFICATEVALUEAVG","ORDERCERTIFICATEVALUETOTALSUM","ORDERCANCELLED","ORDERPHYSICALPERCENTAGE","ORDERCERTIFICATEDISCOUNTS","ORDERCERTIFICATEDISCOUNTPRICE","NOINVOICEFEE","INVOICEFEEINITIALDISCOUNTS","INVOICEFEEINITIALDISCOUNTPRICE","NOENVFEE","CARDFEEDISCOUNTS","CARDFEEDISCOUNTPRICE","FREECARDORDERS","FREECARDSTOTAL","EARLYORDERORDERS","EARLYORDERITEMS","PRIVATEDELIVERYORDERS","PRIVATEDELIVERYCARDS","PRIVATEDELIVERYPERCENTAGE","PRIVATEDELIVERYDISCOUNTS","PRIVATEDELIVERYDISCOUNTPRICE","USEGIFTWRAP","GIFTWRAPPERCENTAGE","GIFTWRAPFREE","GIFTWRAPDISCOUTNS","GIFTWRAPDISCOUNTPRICE","CARDDELIVERYDISCOUNTS","CARDDELIVERYDISCOUNTPRICE","USESPECIALDELIVERY","DOTDISCOUNTS","DOTDISCOUNTPRICE","CARRYUPDISCOUNTS","CARRYUPDISCOUNTPRICE","NOPREPAYMENT");

        $data = array(
            $salesperson,
        );

        foreach($dataKeys as $keyVal) {
            $data[] = isset($personData[$keyVal]) ? intval($personData[$keyVal]) : "0";
        }

        echo implode(";",$data)."\r\n";


    }


    /* HELPERS */


    private function debugLog($text) {
        if(self::DEBUG) {
            echo $text."<br>";
        }
    }

    /**
     * DEV NOTES
     *
    EARLYORDERITEMS
    EARLYORDERORDERS
    ORDERCOUNT
    ORDERQUANTITY
    ORDERCERTIFICATEVALUETOTALSUM
    ORDERCERTIFICATEVALUESUM
    NOINVOICEFEE
    USEGIFTWRAP
    PRIVATEDELIVERYORDERS
    PRIVATEDELIVERYCARDS
    ORDERCANCELLED
    ORDERPHYSICAL
    NOENVFEE
    GIFTWRAPDISCOUTNS
    GIFTWRAPDISCOUNTPRICE
    FREECARDORDERS
    FREECARDSTOTAL
    CARDDELIVERYDISCOUNTS
    CARDDELIVERYDISCOUNTPRICE
    CARDFEEDISCOUNTS
    CARDFEEDISCOUNTPRICE
    USESPECIALDELIVERY
    DOTDISCOUNTS
    DOTDISCOUNTPRICE
    GIFTWRAPFREE
    ORDERCERTIFICATEDISCOUNTS
    ORDERCERTIFICATEDISCOUNTPRICE
    PRIVATEDELIVERYDISCOUNTS
    PRIVATEDELIVERYDISCOUNTPRICE
    INVOICEFEEINITIALDISCOUNTS
    INVOICEFEEINITIALDISCOUNTPRICE
    CARRYUPDISCOUNTS
    CARRYUPDISCOUNTPRICE
    NOPREPAYMENT 
     *
     *
     *
     *
     *
    SELECT * FROM `company_order` where shop_id in (select shop_id from cardshop_settings where language_code = 1) && salesperson not in ('','IMPORT','US') && company_name NOT LIKE '%TEST%' ORDER BY `company_order`.`salesperson` ASC


    Hent ordre ud ift. sælger, skjul import
    - Sælger kode *
    - Antal ordre *
    - Total antal kort *
    - Gns antal kort *
    - Gns kortpris værdi *
    - Annullerede ordre *
    - % med indpak *
    - % fysiske kort *
    - % uden fakturagebyr *
    - % reduceret kort pris *
    - % reduceret
    - Totalt salg
    - Gratis levering
    - Gratis kort
    - Early ordre
    - Earlyordre %
    - Uden forudbetaling
    - Uden miljøbidrag
    - Privatlevering gratis
    - Privatlevering reduceret pris
    - Faktrua gebyr gratis
    - Faktura gebyr reduceret pris
    - Kortlevering gratis
    - Kortlevering reduceret pris
    - Opbæring/dot gratis
    - Opbæring/dot reduceret pris
    - Indpakning gratis
    - Indpakning reduceret pris

     */

}