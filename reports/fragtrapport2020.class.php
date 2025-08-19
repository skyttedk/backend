<?php

class fragtRapport2020 Extends reportBaseController
{


    public function run()
    {

        // Init
        $this->init("fragtjournal-");

        // Start output
        fwrite($this->output,utf8_decode("Ordrenr;Antal;Shop ID;Udløb uge;Gavekortnr. start;Gavekortnr. slut;Gavekort værdi;Virksomhedsnavn;CVR;Attention;Faktura adresse;Faktura adresse 2;Faktura postnr.;Faktura by;Faktura land;Levering adresse;Levering adresse 2;Levering postnr.;Levering by;Levering land;Email;Telefon;Elektronisk kort;Tillægsordre;EAN;Levering virksomhed;Total antal;Fragt;Moms 25;Moms 15;Moms 0;Donation;Samlagt med;højt match\n"));


        // Process each order that have been synced and is not cancelled for companies that are active
        $companyOrderList = $this->getCompanyOrders();
        foreach($companyOrderList as $mData)
        {

            // Init counters
            $moms25 = 0; $moms15 = 0; $moms0 = 0; $momsTotal = 0; $momsSpecial = 0;
            $countInCompany = 0; $countOutsideCompany = 0; $activeShopusers = "";

            // Find sub companies
            $subCompanyIDList = $this->getSubcompanyIDList(intval($mData["company_id"]));

            // looper igennem  shop_user med bs-nummer og henter bruger antal og id'er
            $shopusermap = $this->getShopUserInOrderCount($mData["company_order_id"]);
            foreach($shopusermap as $shopuserresult)
            {
                if($shopuserresult["company_id"] == $mData["company_id"] || in_array($shopuserresult["company_id"],$subCompanyIDList))
                {
                    $countInCompany++;
                    $activeShopusers .= ($activeShopusers == "" ? "" : ",").$shopuserresult["id"];
                }
                else
                {
                    $countOutsideCompany++;
                }
            }

            // If active users in company, count moms
            if($countInCompany > 0)
            {

                // For each moms type, save present count
                $momsRs = $this->getShopUserMomsCount($activeShopusers);
                foreach($momsRs as $momsData){
                    if($momsData["moms"] == "0"){
                        $moms0 = $momsData["antal"];
                    }
                    if($momsData["moms"] == "25"){
                        $moms25 = $momsData["antal"];
                    }
                    if($momsData["moms"] == "15"){
                        $moms15 = $momsData["antal"];
                    }
                    if($momsData["moms"] == "-1"){
                        $momsSpecial = $momsData["antal"];
                    }
                    $momsTotal+= $momsData["antal"];
                }

            }
                                                                                                                                                     /*
                        if($mData["order_no"] == "BS54200") {
                var_dump(array("moms25" => $moms25, "moms15" => $moms15,"moms0" =>$moms0,"momsspec" => $momsSpecial,"activeusers" => $activeShopusers)); exit();
            }                                                                                                                                          */
  
            // Find those who has not selected present and add to moms 25 (default)
            $t = $countInCompany - $momsTotal;
            $moms25+= $t;

            // Output if active cards
            if($countInCompany > 0)
            {
                $str = $mData["order_no"].";".
                    ";".
                    $mData["company_order_shop_id"].";".
                    utf8_decode($this->expiredate).";".
                    ";".
                    ";".
                    ";".
                    $this->csvString($mData["name"]).";".
                    $this->csvString($mData["cvr"]).";".
                    $this->csvString($mData["contact_name"]).";".
                    $this->csvString($mData["bill_to_address"]).";".
                    $this->csvString($mData["bill_to_address_2"]).";".
                    $this->csvString($mData["bill_to_postal_code"]).";".
                    $this->csvString($mData["bill_to_city"]).";".
                    $this->csvString($mData["bill_to_country"]).";".
                    $this->csvString($mData["ship_to_address"]).";".
                    $this->csvString($mData["ship_to_address_2"]).";".
                    $this->csvString($mData["ship_to_postal_code"]).";".
                    $this->csvString($mData["ship_to_city"]).";".
                    ";".
                    $this->csvString($mData["contact_email"]).";".
                    $this->csvString($mData["contact_phone"]).";".
                    ";".
                    ";".
                    $this->csvString($mData["ean"]).";".
                    $this->csvString($mData["ship_to_company"]).";".
                    $countInCompany.";".
                    $this->calculatefreight($mData["company_id"],$countInCompany,($this->isdk && $this->isdromme)).";".
                    $moms25.";".
                    $moms15.";".
                    $moms0.";".
                    $momsSpecial.";;;".$mData["company_id"].";".$countOutsideCompany;
                //".$countOutsideCompany."
                // Save data in list
                $this->masterData[] = explode(";",$str);

            }

        }


        // Restore fragtaftaler for second run
        $this->fragtaftaleMap = $this->fragtaftaleMapOrg;



        //  samler all ordre på første bs kort
        /*
          8=cvr
          10= fakture add
          12= fakture post
          15= lev add
          17= lev post
        */

        // Samle bs under 1 KUN hvis dansk liste
        if($this->isdk) {

            foreach ($this->masterData as $key => $val) {
                foreach ($this->masterData as $key2 => $val2) {

                    $isMacth = 0;

                    if (trimgf($val[2]) == trimgf($val2[2])) {
                        $isMacth++;
                    }
                    if (trimgf($val[8]) == trimgf($val2[8])) {
                        $isMacth++;
                    }
                    if (trimgf($val[10]) == trimgf($val2[10])) {
                        $isMacth++;
                    }
                    if (trimgf($val[12]) == trimgf($val2[12])) {
                        $isMacth++;
                    }
                    if (trimgf($val[15]) == trimgf($val2[15])) {
                        $isMacth++;
                    }
                    if (trimgf($val[17]) == trimgf($val2[17])) {
                        $isMacth++;
                    }
                    if (trimgf($val[0]) == trimgf($val2[0])) {
                        $isMacth--;
                    }

                    if ($isMacth == 6 && $this->masterData[$key][26] != 0) {

                        // total
                        $this->masterData[$key][26] += $this->masterData[$key2][26];
                        $this->masterData[$key2][26] = 0;

                        // ny fragt beregn
                        $this->masterData[$key][27] = $this->calculatefreight($this->masterData[$key][34], $this->masterData[$key][26], ($this->isdk && $this->isdromme));
                        $this->masterData[$key2][27] = 0;

                        // moms 25
                        $this->masterData[$key][28] += $this->masterData[$key2][28];
                        $this->masterData[$key2][28] = 0;

                        // moms 15
                        $this->masterData[$key][29] += $this->masterData[$key2][29];
                        $this->masterData[$key2][29] = 0;

                        // moms 0
                        $this->masterData[$key][30] += $this->masterData[$key2][30];
                        $this->masterData[$key2][30] = 0;

                        // moms special
                        $this->masterData[$key][31] += $this->masterData[$key2][31];
                        $this->masterData[$key2][31] = 0;

                        // antal samlagt
                        $this->masterData[$key][32] = $this->masterData[$key][32] . "/" . $this->masterData[$key2][0];
                        $this->masterData[$key2][32] = $this->masterData[$key][0];

                    } else {
                        if ($val[8] == $val2[8] && $this->masterData[$key][26] != 0 && $val[0] != $val2[0] && $isMacth > 4) {
                            if ($val[2] == $val2[2]) {
                                $bs = str_replace("BS", "", $this->masterData[$key2][0]);
                                $this->masterData[$key][33] = $this->masterData[$key][33] . " " . $bs . "(" . $isMacth . ") ";
                            }
                        }
                    }

                }

            }
        }

        // Output lines
        foreach($this->masterData as $key=>$val){
            //$this->masterData[$val][34] = "";
            fwrite($this->output, implode(";",$val)."\n");
        }

    }


    private function csvString($val) {
        return utf8_decode(str_replace(array("\r","\n",";"),array(""," ",","),$val));
    }


    /********************************************* COMMON FUNCTIONS *************************************/

    private $debugMode;
    private $expiredate;
    private $isdromme;
    private $isdk;
    private $shops;
    private $filename;
    private $output;
    private $masterData;

    private function init($filenamePrepend="") {

        // Check token / debug mode
        $this->debugMode = (isset($_GET["debug"]) && intval($_GET["debug"]) == 1);
        if($_GET["token"] != "324jlh2345jkFSd12jcvmcpq463q"){  die("Ingen adgang"); }

        // Get inputs
        $this->expiredate = $_GET["expiredate"];
        $this->isdromme = (isset($_GET["isdrom"]) && $_GET["isdrom"] == "1");
        $this->isdk = (isset($_GET["isdk"]) && $_GET["isdk"] == "1");

        // Prepare other vars
        $this->masterData = [];

        // Load shops
        $this->shopLoad();

        // Load
        $this->loadFragtAftaler();

        // Start output
        if($this->debugMode == false) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename='.$filenamePrepend.$this->filename.'');
        }

        $this->output = fopen('php://output', 'w');

    }

    private function shopLoad()
    {
        // Select shops based on language
        if(!$this->isdk)
        {
            $this->shops = array(272,57,58,59,574);
            $this->filename = "".$this->expiredate."_no.csv";
        }
        else if($this->isdromme)
        {
            $this->shops = array(290,310);
            $this->filename = "".$this->expiredate."_dromme.csv";
        }
        else
        {
            $this->shops = array(52,575,54,55,56,53);
            $this->filename = "".$this->expiredate."_dk.csv";
        }
    }


    private function getCompanyOrders()
    {
        $sqlCompanyOrder = "SELECT
        company.*, certificate_no_begin, certificate_no_end, company_id, order_no, company_order.id as company_order_id, company_order.shop_id as company_order_shop_id
        FROM `company_order`, company
        WHERE
            company_order.company_id = company.id && navsync_status = 3 && is_cancelled = 0 &&
            company_order.id IN (
                select DISTINCT (company_order_id) from shop_user
                where expire_date = '".$this->expiredate."' and
                shop_id in(".implode(",",$this->shops).") and is_demo = 0 and blocked = 0
            ) &&
            company.deleted = 0 && company.active = 1 && company.onhold = 0  order by cvr,order_no ";

        $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);
        
        return $companyOrderList;
    }

    private function getSubcompanyIDList($companyid)
    {
        $subCompanyIDList = array();
        $sqlSubCompanies = "SELECT id FROM company WHERE pid = ".intval($companyid);
        $subCompanyResult = Dbsqli::getSql2($sqlSubCompanies);
        if(count($subCompanyResult) > 0) {
            foreach($subCompanyResult as $subCompanyID) {
                if($subCompanyID["id"] > 0) $subCompanyIDList[] = $subCompanyID["id"];
            }
        }
        return $subCompanyIDList;
    }

    /* OLD BUGGY FUNCTION */
    /*
    private function getShopUserInOrderCount($company_order_id) {
        $sqlShopUser = "SELECT count(id) as antal, group_concat(id) as userlist, company_id
            FROM `shop_user`
            WHERE
                is_giftcertificate = 1 &&
                company_order_id = ".intval($company_order_id)." &&
                is_demo = 0 &&
                blocked = 0 &&
                is_delivery = 0 group by company_id";

        // Shopuser results
        $shopusermap = Dbsqli::getSql2($sqlShopUser);
        return $shopusermap;
    }
    */

    private function getShopUserInOrderCount($company_order_id) {
    
        $deliverySQL = "";
        if($this->isdk) $deliverySQL = " && is_delivery = 0 ";
    
        $sqlShopUser = "SELECT id, company_id
            FROM `shop_user`
            WHERE
                is_giftcertificate = 1 &&
                company_order_id = ".intval($company_order_id)." &&
                is_demo = 0 &&
                blocked = 0".$deliverySQL;

        // Shopuser results
        $shopusermap = Dbsqli::getSql2($sqlShopUser);
        return $shopusermap;
    }

    private function getShopUserMomsCount($activeShopusers)
    {
        // Get present / moms count
        $momsSql = "select count(*) as antal, moms
                from present inner join `order` on present.id =  `order`.present_id
                where `order`.present_id = present.id && `order`.shopuser_id in (".$activeShopusers.") group by moms";
        $momsRs = Dbsqli::getSql2($momsSql);
        return $momsRs;
    }

    private $fragtaftaleMap;
    private $fragtaftaleMapOrg;

    private function loadFragtAftaler()
    {

        // Load already processed company id's
        $usedCompanyIDs = "SELECT company_id FROM `shop_user` WHERE blocked = 0 && expire_date IN (SELECT expire_date FROM `expire_date` WHERE expire_date < '".$this->expiredate."') GROUP BY company_id";
        $usedCompanyList = Dbsqli::getSql2($usedCompanyIDs);
        $usedCompanyIDList = array();

        foreach($usedCompanyList as $usedCompanyID) {
            $usedCompanyIDList[] = intval($usedCompanyID["company_id"]);
        }

        $this->fragtaftaleMap = array();
        $fragtAftaleSQL = "SELECT cost, company_id FROM `company_shipping_cost` ORDER BY created DESC";
        $fragtAftaleList = Dbsqli::getSql2($fragtAftaleSQL);

        foreach($fragtAftaleList as $fragtAftale) {
            if(!isset($this->fragtaftaleMap[$fragtAftale["company_id"]])) {
                if(in_array($fragtAftale["company_id"],$usedCompanyIDList)) {
                    $this->fragtaftaleMap[$fragtAftale["company_id"]] = 0;
                } else {
                    $this->fragtaftaleMap[$fragtAftale["company_id"]] = $fragtAftale["cost"];
                }
            }
        }


        $this->fragtaftaleMapOrg = $this->fragtaftaleMap;

    }

    private function hasFragtAftale($company_id)
    {
        if(isset($this->fragtaftaleMap[$company_id])) {
            $pris = $this->fragtaftaleMap[$company_id];
            return $pris >= 0;
        } else {
            return false;
        }
    }


    private function getFragtAftalePris($company_id)
    {
        if(isset($this->fragtaftaleMap[$company_id])) {
            $pris = $this->fragtaftaleMap[$company_id];

            // Set aftale to 0 so other lines for same company dont get freight
            $this->fragtaftaleMap[$company_id] = 0;

            return $pris >= 0 ? $pris : "fejl 1";
        } else {
            return "fejl 2";
        }
    }

    private function calculatefreight($companyid,$quantity,$isDrommegavekortet=false) {

        // Check for fragtaftale
        if($this->hasFragtAftale($companyid)) {
            return $this->getFragtAftalePris($companyid);
        }


        // Drommegavekort
        if($isDrommegavekortet)
        {
            if($quantity >=  0  && $quantity <= 1)        { return(188.0); }
            elseif($quantity >=  2  && $quantity <= 4)    { return(268.0); }
            elseif($quantity >=  5  && $quantity <= 10)   { return(528.0); }
            elseif($quantity >=  11 && $quantity <= 20)   { return(748.0); }
            elseif($quantity >=  21 && $quantity <= 40)   { return(1496.0); }
            elseif($quantity >=  41 && $quantity <= 60)   { return(2244.0); }
            elseif($quantity >=  61 && $quantity <= 80)   { return(2992.0); }
            elseif($quantity >=  81 && $quantity <= 100)  { return(3740.0); }
            elseif($quantity >=  101 && $quantity <= 120) { return(4488.0); }
            elseif($quantity >=  121 && $quantity <= 140) { return(5236.0); }
            elseif($quantity >=  141 && $quantity <= 160) { return(5984.0); }
            elseif($quantity >=  161 && $quantity <= 180) { return(6732.0); }
            elseif($quantity >=  181 && $quantity <= 200) { return(7480.0); }
            elseif($quantity >=  201 && $quantity <= 220) { return(8228.0); }
            elseif($quantity >=  221 && $quantity <= 240) { return(8976.0); }
            elseif($quantity >=  241 && $quantity <= 260) { return(9724.0); }
            elseif($quantity >=  261 && $quantity <= 280) { return(10472.0); }
            elseif($quantity >=  281 && $quantity <= 300) { return(11220.0); }
            elseif($quantity >=  301 && $quantity <= 320) { return(11968.0); }
            elseif($quantity >=  321 && $quantity <= 340) { return(12716.0); }
            elseif($quantity >=  341 && $quantity <= 360) { return(13464.0); }
            elseif($quantity >=  361 && $quantity <= 380) { return(14212.0); }
            elseif($quantity >=  381 && $quantity <= 400) { return(14960.0); }
            elseif($quantity >400 ) { return(15708.0); }
        }

        // Other dk freight
        else
        {
            if($quantity >=  0  && $quantity <= 2)        { return(268.0); }
            elseif($quantity >=  3  && $quantity <= 8)    { return(528.0); }
            elseif($quantity >=  9 && $quantity <= 14)    { return(748.0); }
            elseif($quantity >=  15 && $quantity <= 20)   { return(1122.0); }
            elseif($quantity >=  21 && $quantity <= 30)   { return(1496.0); }
            elseif($quantity >=  31 && $quantity <= 40)   { return(1870.0); }
            elseif($quantity >=  41 && $quantity <= 60)   { return(2244.0); }
            elseif($quantity >=  61 && $quantity <= 80)   { return(2992.0); }
            elseif($quantity >=  81 && $quantity <= 100)  { return(3740.0); }
            elseif($quantity >=  101 && $quantity <= 120) { return(4488.0); }
            elseif($quantity >=  121 && $quantity <= 140) { return(5236.0); }
            elseif($quantity >=  141 && $quantity <= 160) { return(5984.0); }
            elseif($quantity >=  161 && $quantity <= 180) { return(6732.0); }
            elseif($quantity >=  181 && $quantity <= 200) { return(7480.0); }
            elseif($quantity >=  201 && $quantity <= 220) { return(8228.0); }
            elseif($quantity >=  221 && $quantity <= 240) { return(8976.0); }
            elseif($quantity >=  241 && $quantity <= 260) { return(9724.0); }
            elseif($quantity >=  261 && $quantity <= 280) { return(10472.0); }
            elseif($quantity >=  281 && $quantity <= 300) { return(11220.0); }
            elseif($quantity >=  301 && $quantity <= 320) { return(11968.0); }
            elseif($quantity >=  321 && $quantity <= 340) { return(12716.0); }
            elseif($quantity >=  341 && $quantity <= 360) { return(13464.0); }
            elseif($quantity >=  361 && $quantity <= 380) { return(14212.0); }
            elseif($quantity >=  381 && $quantity <= 400) { return(14960.0); }
            elseif($quantity >400 ) { return(15708.0); }
        }
    }



    /******************************************** KORTVALG LISTE *******************************************/


    public function runGavevalg()
    {


        // Init
        $this->init("gavevalg-");

        // Start output
        fwrite($this->output,utf8_decode("BS-nummer;Gave navn;Model navn;Varenr;Antal valgte\n"));

        // Process each order that have been synced and is not cancelled for companies that are active
        $companyOrderList = $this->getCompanyOrders();
        
        
        ///////// TEMP - GET BY BS NUMBERS ////////////////
        /*
         $sqlCompanyOrder = "SELECT
        company.*, certificate_no_begin, certificate_no_end, company_id, order_no, company_order.id as company_order_id, company_order.shop_id as company_order_shop_id
        FROM `company_order`, company
        WHERE
            company_order.company_id = company.id &&
			company_order.order_no IN ('BS45774','BS46310','BS42814','BS45773','BS41468','BS40184','BS47328','BS51293','BS51521','BS43314','BS43799','BS44557','BS40542','BS44094','BS40658','BS43818','BS46896','BS40290','BS42383','BS42111','BS46601','BS49088','BS48103','BS40044','BS46169','BS42848','BS47683','BS47329','BS49469','BS40096','BS46170','BS47158','BS47417','BS40271','BS50113','BS41596','BS44029','BS46069','BS43188','BS44750','BS47553','BS45014','BS41091','BS42254','BS51523','BS50019','BS40273','BS40568','BS42277','BS47363','BS49288','BS50105','BS42030','BS44157','BS45555','BS45775','BS45896','BS47219','BS49375','BS42874','BS47143','BS47628','BS49272','BS50072','BS50864','BS42632','BS43897','BS46539','BS47364','BS51003','BS40541','BS44636','BS45771','BS47409','BS47413','BS47888','BS48456','BS49737','BS50194','BS43226','BS43289','BS45772','BS49430','BS51456','BS45167','BS47264','BS47411','BS48312','BS50289','BS42189','BS44158','BS46998','BS47221','BS47410','BS45168','BS45169','BS46550','BS46887','BS47268','BS50393','BS50503','BS51038','BS51609','BS40590','BS42630','BS42636','BS43052','BS43954','BS44160','BS44165','BS45562','BS46533','BS47414','BS47416','BS48274','BS48572','BS48578','BS49278','BS51416','BS44407','BS45409','BS47542','BS42634','BS43955','BS40016','BS41256','BS42635','BS44356','BS45770','BS48457','BS42764','BS44234','BS45272','BS46438','BS47539','BS47541')
            order by cvr,order_no ";

        $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);
        */
        ///////// TEMP DONE ////////////////
        
        
        foreach($companyOrderList as $mData)
        {

            // Init counters
            $countInCompany = 0; $countOutsideCompany = 0; $activeShopusers = "";

            // Find sub companies
            $subCompanyIDList = $this->getSubcompanyIDList(intval($mData["company_id"]));

            // looper igennem  shop_user med bs-nummer og henter bruger antal og id'er
            $shopusermap = $this->getShopUserInOrderCount($mData["company_order_id"]);
            foreach($shopusermap as $shopuserresult)
            {
                //if($shopuserresult["company_id"] == $mData["company_id"] || in_array($shopuserresult["company_id"],$subCompanyIDList))
                //{
                $countInCompany++;
                $activeShopusers .= ($activeShopusers == "" ? "" : ",").$shopuserresult["id"];
                //}
                //else
                //{
                //    $countOutsideCompany++;
                //}
            }

            // Output if active cards
            if($countInCompany > 0)
            {
                $str = $mData["order_no"].";".
                    ";".
                    $mData["company_order_shop_id"].";".
                    utf8_decode($this->expiredate).";".
                    ";".
                    ";".
                    ";".
                    utf8_decode($mData["name"]).";".
                    utf8_decode($mData["cvr"]).";".
                    utf8_decode($mData["contact_name"]).";".
                    utf8_decode($mData["bill_to_address"]).";".
                    utf8_decode($mData["bill_to_address_2"]).";".
                    utf8_decode($mData["bill_to_postal_code"]).";".
                    utf8_decode($mData["bill_to_city"]).";".
                    utf8_decode($mData["bill_to_country"]).";".
                    utf8_decode($mData["ship_to_address"]).";".
                    utf8_decode($mData["ship_to_address_2"]).";".
                    utf8_decode($mData["ship_to_postal_code"]).";".
                    utf8_decode($mData["ship_to_city"]).";".
                    ";".
                    utf8_decode($mData["contact_email"]).";".
                    utf8_decode($mData["contact_phone"]).";".
                    ";".
                    ";".
                    utf8_decode($mData["ean"]).";".
                    utf8_decode($mData["ship_to_company"]).";".
                    $countInCompany.";".
                    $this->calculatefreight($mData["company_id"],$countInCompany,($this->isdk && $this->isdromme)).";".
                    ";".
                    ";".
                    ";".
                    ";;;".$mData["company_id"].";".$activeShopusers.";";

                // Save data in list
                $this->masterData[] = explode(";",$str);


            }

        }
        
        //$validOrdreNrList = array('BS40044','BS40096','BS40184','BS40271','BS40273','BS40290','BS40542','BS40892','BS41468','BS42030','BS42111','BS42254','BS42277','BS42630','BS42632','BS42634','BS42635','BS42636','BS42764','BS42814','BS42848','BS42874','BS43052','BS43083','BS43083','BS43289','BS43457','BS43700','BS43799','BS43807','BS43818','BS43818','BS43844','BS44029','BS44043','BS44094','BS44234','BS44356','BS44407','BS44557','BS44636','BS44750','BS45014','BS45167','BS45168','BS45169','BS45272','BS45409','BS45591','BS46069','BS46169','BS46170','BS46178','BS46533','BS46539','BS46539','BS46550','BS46601','BS46642','BS46887','BS46896','BS46961','BS46963','BS46998','BS47008','BS47158','BS47219','BS47221','BS47328','BS47329','BS47363','BS47364','BS47409','BS47410','BS47411','BS47413','BS47414','BS47416','BS47417','BS47628','BS48274','BS48572','BS48578','BS50105','BS51268','BS51270','BS51299','BS51389');

        foreach($this->masterData as $key=>$val) {


            //if(in_array($val[0],$validOrdreNrList)) { 
            // BS no
            $bsnumber = $val[0];
            $shopid = $val[2];
            $currentBSList = "";
            $bsSelectedCount = 0;

            // Prepare userids
            $useridTmpList = explode(",",$val[35]);
            $useridlist = array();
            if(count($useridTmpList) > 0) {
                foreach($useridTmpList as $id) {
                    if(trimgf($id) != "" && intval(trimgf($id)) > 0) {
                        $useridlist[] = intval(trimgf($id));
                    }
                }
            }

            // Split and count userids
            $userids = implode(",",$useridlist);
            $bsTotalCount = countgf($useridlist);

            // If any userids
            if(count($useridlist) > 0) {

                // Select present counts
                $orderSelectsSQL = "SELECT count(`order`.id) as ordercount, present_model.model_name, present_model.model_no, present_model.model_present_no, GROUP_CONCAT(`order`.shopuser_id), present_model.model_id, `order`.shop_id 
                FROM  `order`, `present_model` 
                WHERE `order`.shopuser_id IN (".$userids.") && `order`.present_model_id = present_model.model_id && present_model.language_id = 1 
                GROUP BY present_model.model_id, `order`.shop_id ORDER BY present_model.model_present_no";

                $orderData = Dbsqli::getSql2($orderSelectsSQL);
                if(is_array($orderData) && countgf($orderData) > 0) {
                    foreach($orderData as $orderRow) {
                        if($shopid != 0 && $shopid != $orderRow["shop_id"]) { echo "ERROR - SHOPID conflict"; exit(); }
                        $currentBSList .= $bsnumber.";".$orderRow["model_name"].";".$orderRow["model_no"].";".$orderRow["model_present_no"].";".$orderRow["ordercount"].";\n";
                        $bsSelectedCount += $orderRow["ordercount"];
                    }
                }


                // Make autogave list
                if(($bsTotalCount-$bsSelectedCount) > 0) {

                    if($shopid == 0) {
                        //$sql = "SELECT shop_id FROM shop_user WHERE id IN (".$userids.")";
                        //$shopusershop = Dbsqli::getSql2($orderSelectsSQL);
                        //if(is_array($shopusershop) && countgf($shopusershop) > 0) $shopid = $shopusershop[0]["shop_id"];
                        echo "NO SHOP FOUND"; exit();
                    }

                    $standardGaveVareNr = $this->getAutogaveVarenr($shopid);
                    $standardGaveNavn = $this->getAutogaveNavn($shopid);
                    $standardGaveModel = "";
                    $currentBSList = $bsnumber.";".$standardGaveNavn.";".$standardGaveModel.";".$standardGaveVareNr.";".($bsTotalCount-$bsSelectedCount).";\n".$currentBSList;
                }

                // Output last line
                fwrite($this->output, utf8_decode($currentBSList));

            }
          }
        //}
        /*
                // Output lines
                foreach($this->masterData as $key=>$val){
                    //$this->masterData[$val][34] = "";
                    fwrite($this->output, implode(";",$val)."\n");
                }
        */
    }

    private function getAutogaveNavn($shopid) {
        $autogave = $this->getAutogave(intval($shopid));
        return utf8_encode($autogave[1]);
    }

    private function getAutogaveVarenr($shopid) {
        $autogave = $this->getAutogave(intval($shopid));
        return utf8_encode($autogave[0]);
    }

    private function getAutogave($shopid) {

        $list = array(
            53 => array("58892-1","Fabrikkens juleæske - giga inkl. Moèt"),
            575 => array("20201540","Sv. Michelsen 2020 gavepakke 6"),
            56 => array("58887-1","Fabrikkens juleæske - giga"),
            55 => array("58888-1","Fabrikkens juleæske - mega"),
            54 => array("58889-1","Fabrikkens juleæske - large"),
            52 => array("58888-1","Fabrikkens juleæske - mega"),
            290 => array("58891","Fabrikkens juleæske jul2"),
            310 => array("58890-1","Fabrikkens juleæske - jul 3")
        );

        if(isset($list[$shopid])) return $list[$shopid];
        return array("autogave","Autogave");

    }


}