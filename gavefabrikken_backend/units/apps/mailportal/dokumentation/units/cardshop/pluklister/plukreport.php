<?php

namespace GFUnit\cardshop\pluklister;

use GFBiz\Model\Cardshop\ShopMetadata;

class PlukReport
{

    /*************** STATIC ENVIRONMENT HELPERS *************/

    private static $envPrepared = false;
    public static function prepareEnv() {
        if(self::$envPrepared == false) {
            ini_set('memory_limit', '2000M');
            set_time_limit(40 * 60);
            self::$envPrepared = true;
        }
    }

    /*************** CLASS MEMBERS *************/

    protected $shop;
    protected $shopSettings;
    protected $shopid;
    protected $expire;
    protected $wrapped;
    protected $useWrapped;
    protected $isWrapped;
    protected $isLabels;

    protected $carryup;
    protected $useCarryup;
    protected $isCarryup;

    protected $largeSmall;
    protected $useLargeSmall;
    protected $isLarge;


    /*************** CLASS CONSTURCT *************/

    public function __construct() {

        self::prepareEnv();
        $this->shopid = intval(isset($_POST["shopid"]) ? $_POST["shopid"] : "");
        $this->expire = trimgf(isset($_POST["expire"]) ? $_POST["expire"] : "");

        $this->wrapped = trimgf(isset($_POST["wrapped"]) ? $_POST["wrapped"] : "");
        $this->useWrapped = ($this->wrapped === "1" || $this->wrapped === "0"  || $this->wrapped === "2");
        $this->isWrapped = ($this->wrapped === "1");
        $this->isLabels = ($this->wrapped === "2");

        $this->carryup = trimgf(isset($_POST["carryup"]) ? $_POST["carryup"] : "");
        $this->useCarryup = ($this->carryup === "1" || $this->carryup === "0");
        $this->isCarryup = ($this->carryup === "1");

        $this->shop = \Shop::find($this->shopid);
        $this->shopSettings = \CardshopSettings::find('first',array('conditions' => array("shop_id" => $this->shopid)));

        $this->largeSmall = trimgf(isset($_POST["largesmall"]) ? $_POST["largesmall"] : "");
        $this->useLargeSmall = ($this->largeSmall === "1" || $this->largeSmall === "0");
        $this->isLarge = ($this->largeSmall === "1");

    }

    /************ DEBUG ********/
    protected $isDebug = false;
    protected function debugLog($string) {
        if($this->isDebug == true) {
            echo $string."<br>";
        }
    }


    /************ ERROR HANDLING ********/
    private $error = "";
    public function getError() { return $this->error; }
    protected function setError($error) { $this->error = $error; return false; }


    /**************** HELPERS *************************/

    protected function shoptolang($shopid=0) {
        return ShopMetadata::getShopLangCode($shopid);
    }

    protected function fullalias($shopid, $alias) {
        return ShopMetadata::getShopValueAlias($shopid) . (strlen(intval($alias)) == 1 ? "0" : "") . $alias;
    }
    
    protected function getValueAlias($shopid) {
        return ShopMetadata::getShopValueAlias($shopid);
    }

    protected function getAutovalgName() {
        return $this->shopSettings->default_present_name;
    }

    protected function getAutovalgVarenr() {
        return $this->shopSettings->default_present_itemno;
    }
    
    protected function getSampakVarenrList($varenr) {
        $list = array();

        if($this->shopSettings != null) {
            $languageid = intval($this->shopSettings->language_code);
            if(substr(trimgf(strtolower($varenr)),0,3) == "sam" && $languageid == 4) {
                $languageid = 1;
            }
        } else $languageid = 1;

        if($languageid == 5) {
            $languageid = 1;
        }

        $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = ".$languageid." && parent_item_no = '".$varenr."' && deleted is null");
        foreach($navbomItemList as $item) {
            if(!in_array($item->no,$list)) {
                $list[] = ($item->quantity_per > 1 ? "(".$item->quantity_per." ".$item->unit_of_measure_code.")" : "").$item->no;
            }
        }

        return $list;
    }

    protected function formatPostalCode($postalCode) {
        if($this->shopSettings->language_code == 5) {
            if(strlen(trimgf($postalCode)) == 5) {
                $postalCode = trimgf($postalCode);
                $postalCode = substr($postalCode,0,3)." ".substr($postalCode,3);
            }
            return $postalCode;
        }
        return $postalCode;
    }


    /************* REMINDER list *****************/

    private function findtoken()
    {
        $token = NewGUID();
        $company = Company::find_by_sql("SELECT * FROM company WHERE token LIKE '" . $token . "'");
        if (count($company) > 0) {
            return findtoken();
        } else {
            return $token;
        }

    }

    public function settokens()
    {

        $companylist = Company::find_by_sql("SELECT * FROM company WHERE token = '' OR Token IS NULL");
        $set = array();
        foreach ($companylist as $company) {
            $c = Company::find($company->id);
            if (trimgf($c->token) == "" && $c->id > 0) {

                $c->token = $this->findtoken();
                $c->save();
                $set[] = $c->id;

            }
        }

        System::connection()->commit();
        echo json_encode($set);

    }

    public function remindermailfromquery()
    {
        $shopinput = isset($_GET["shops"]) ? trimgf($_GET["shops"]) : "";
        $deadline = isset($_GET["deadline"]) ? trimgf($_GET["deadline"]) : "";
        $this->remindermaillist($shopinput, $deadline);
    }

    public function remindermaillist($shopinput, $deadline)
    {

        $shoplist = array();
        $inputsplit = explode(",", $shopinput);
        if (count($inputsplit) > 0) {
            foreach ($inputsplit as $input) {
                if (intval($input) > 0) {
                    $shoplist[] = intval($input);
                }
            }
        }

        if (count($shoplist) == 0) {echo "Ingen shops angivet..";return;}

        $shops = $shoplist;

        // Check deadline
        $expireDate = expireDate::getByExpireDate($deadline);
        if ($expireDate == null) {echo "Ugyldig deadline";return;}

        $removeDuplicateMails = false;

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="reminderlist-' . $deadline . '.csv"');

        $sumCompany = 0;
        $sumUsers = 0;
        $sumSelected = 0;
        $sumNotSelected = 0;
        $usedEmails = array();

        // Define headers
        $header = array("CompanyID", "Virksomhed", "CVR", "Faktura adresse", "Faktura postnr", "Faktura by", "Levering virksomhed", "Levering adresse", "Levering postnr", "Levering by", "Kontaktperson", "Telefon", "E-mail", "Antal kort", "Antal valgt", "Antal ikke valgt", "Token");
        echo implode(';', $header) . "\n";

        // Find companies
        $companylist = ShopUser::find_by_sql("SELECT company_id, count(id) as users FROM shop_user WHERE shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0 && company_id NOT IN (12441,12456,12468,12480,12500,12501,12505,12510,12512,12514,12515,12519,12520,12522,12523,12529,12530,12531,12532,12533,12535,12536,12537,12538,12539,12540,12541,12542,12543,12544,12546,12547,12549,12550,12551,12552,12553,12554,12555,12556,12557,12558,12559,12561,12562,12563,12564,12565,12566,12568,12569,12571,12572,12573,12574,12575,12576,12577,12578,12579,12580,12581,12583,12587,12588,12618,12689,12702,12994,13014,13037,13095,13098,13441,13521,13554,13555,13700,13088,18581,19308,19309,19310,12957) GROUP BY company_id");

        //echo "SELECT company_id, count(id) as users FROM shop_user WHERE shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0 && company_id NOT IN (12441,12456,12468,12480,12500,12501,12505,12510,12512,12514,12515,12519,12520,12522,12523,12529,12530,12531,12532,12533,12535,12536,12537,12538,12539,12540,12541,12542,12543,12544,12546,12547,12549,12550,12551,12552,12553,12554,12555,12556,12557,12558,12559,12561,12562,12563,12564,12565,12566,12568,12569,12571,12572,12573,12574,12575,12576,12577,12578,12579,12580,12581,12583,12587,12588,12618,12689,12702,12994,13014,13037,13095,13098,13441,13521,13554,13555,13700,13088,18581,19308,19309,19310,12957) GROUP BY company_id";
        //echo "<br>".countgf($companylist); exit();

        // Go through companies
        foreach ($companylist as $companycount) {

            // Load company and orders
            $c = Company::find($companycount->company_id);
            $orders = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE id NOT IN (SELECT shopuser_id FROM `order`) && company_id = " . $c->id . " && shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0");

            // Get cound
            $totalUsers = $companycount->users;
            $totalNotSelected = countgf($orders);
            $totalOrders = $totalUsers - $totalNotSelected;

            // Check negative number
            if ($totalOrders < 0) {echo "COUNT ERROR IN  " . $c->id;exit();}

            // Update total sum
            $sumCompany++;
            $sumUsers += $totalUsers;
            $sumSelected += $totalOrders;
            $sumNotSelected += $totalNotSelected;

            // Find mail and check
            $mail = mb_strtolower(trimgf($c->contact_email));
            $isUsed = in_array($mail, $usedEmails);
            if (!$isUsed) {
                $usedEmails[] = $mail;
            }
            //  if($isUsed == false){
            if ($removeDuplicateMails == false || $isUsed == false) {

                // Add data to file
                $data = array(
                    $c->id,
                    $c->name, $c->cvr, $c->bill_to_address, $c->bill_to_postal_code, $c->bill_to_city, $c->ship_to_company, $c->ship_to_address, $c->ship_to_postal_code, $c->ship_to_city,
                    $c->contact_name, $c->contact_phone, $c->contact_email,
                    $totalUsers,
                    $totalOrders,
                    $totalNotSelected,
                    $c->token
                );

                // Fix encoding and add
                foreach ($data as $key => $val) {
                    $data[$key] = utf8_decode($val);
                }

                echo implode(';', $data) . "\n";

            } else {
                //echo "REMOVED DUPLICATE: ".$c->id." / ".$c->contact_email." / ".$c->token;
            }

        }

        $sum = array(
            "TOTAL SUM", "ANTAL KUNDER: $sumCompany", "ANTAL KORT: $sumUsers", "ANTAL VALGT: $sumSelected", "ANTAL IKKE VALGT: $sumNotSelected"
        );

        echo implode(';', $sum) . "\n";

    }

    /*
     * HELPERS
     */

    const CONTROLLER = "cardshoppluk";
    public function getUrl($method = "")
    {return "../gavefabrikken_backend/index.php?rt=" . self::CONTROLLER . "/" . $method . "&token=dfk4dkfSdvj3fj3j4Fgnjafdopd643&";}


    










    /**
     * CUSTOM LIST
     */

    private function customList($shopid, $expire)
    {

        /*
        // HAS SAME BS number multiple times, each per company / deadline
            $sql = "SELECT
        company_order.order_no,
        company.name as company_name,
        company.cvr as company_cvr,
        company.ean as company_ean,
        company.bill_to_address,
        company.bill_to_address_2,
        company.bill_to_postal_code,
        company.bill_to_city,
        company.bill_to_country,
        company.bill_to_email,
        company_order.company_name as sales_company,
        company_order.shop_name as sales_shop,
        company_order.salesperson as sales_person,
        company_order.salenote as sales_note,
        company.internal_note,
        company.rapport_note,
        company_order.quantity as sales_quantity,
        company_order.expire_date as sales_expiredate,
        company_order.certificate_no_begin,
        company_order.certificate_no_end,
        company_order.certificate_value,
        company_order.is_email,
        company_order.is_appendix_order,
        company_order.giftwrap as gift_wrap,
        company_order.gift_spe_lev as gift_carryup,
        company_order.earlyorderList as earlypresents,
        company.ship_to_company,
        company.ship_to_attention,
        company.ship_to_address,
        company.ship_to_address_2,
        company.ship_to_postal_code,
        company.ship_to_city,
        company.ship_to_country,
        company_order.spdealtxt as ship_dealtext,
        company.contact_name,
        company.contact_email,
        company.contact_phone,
        company_order.navsync_status,
        company_order.navsync_response as navsync_debitorid,
        shop_user.expire_date as card_expiredate,
        IF(company_order.expire_date = shop_user.expire_date,0,1) as has_moved_deadline,
        IF(company_order.company_id = company.id,0,1) as has_moved_company,
        count(shop_user.id) as cards_totalcount,
        sum(IF(blocked=0,1,0)) as cards_activecount,
        sum(IF(blocked=1,1,0)) as cards_closedcount
    FROM company, company_order, shop_user WHERE
        company_order.is_cancelled = 0 &&
        shop_user.company_order_id = company_order.id &&
        ((shop_user.shop_id = ".intval($shopid)." && (shop_user.expire_date = '".$expire."')) || (company_order.shop_id = ".intval($shopid)." && (company_order.expire_date = '".$expire."'))) &&
        is_giftcertificate = 1 &&
        shop_user.company_id = company.id
    GROUP BY shop_user.expire_date, company_order.expire_date, shop_user.company_id, shop_user.company_order_id
    ORDER BY company_order.order_no ASC";

    */

        $sql = "SELECT 
	company_order.order_no,
	company.name as company_name,
	company.cvr as company_cvr,
	company.ean as company_ean,
	company.bill_to_address,
	company.bill_to_address_2,
	company.bill_to_postal_code,
	company.bill_to_city,
	company.bill_to_country,
	company.bill_to_email,
	company_order.company_name as sales_company,
	company_order.shop_name as sales_shop,
	company_order.salesperson as sales_person,
	company_order.salenote as sales_note,
	company.internal_note,
	company.rapport_note,
	company_order.quantity as sales_quantity,
	company_order.expire_date as sales_expiredate,
	company_order.certificate_no_begin,
	company_order.certificate_no_end,
	company_order.certificate_value,
	company_order.is_email,
	company_order.is_appendix_order,
	company_order.giftwrap as gift_wrap,
	company_order.name_label as name_label,
	company_order.gift_spe_lev as gift_carryup,
	company_order.earlyorderList as earlypresents,
	company.ship_to_company,
	company.ship_to_attention,
	company.ship_to_address,
	company.ship_to_address_2,
	company.ship_to_postal_code,
	company.ship_to_city,
	company.ship_to_country,
	company_order.spdealtxt as ship_dealtext,
	company.contact_name,
	company.contact_email,
	company.contact_phone,
	company_order.navsync_status,
	company_order.navsync_response as navsync_debitorid,
	count(shop_user.id) as cards_totalcount,
	sum(IF(blocked=0,1,0)) as cards_activecount,
	sum(IF(blocked=1,1,0)) as cards_closedcount,
	sum(IF(shop_user.expire_date!=company_order.expire_date,1,0)) as cards_moveddeadline,
	sum(IF(shop_user.company_id!=company_order.company_id,1,0)) as cards_movedcompany
FROM company, company_order, shop_user WHERE 
	company_order.is_cancelled = 0 &&
	shop_user.company_order_id = company_order.id &&
	((shop_user.shop_id = ".intval($shopid)." && shop_user.expire_date = '".$expire."') || (company_order.shop_id = ".intval($shopid)." && company_order.expire_date = '".$expire."')) &&
	is_giftcertificate = 1 &&
	company_order.company_id = company.id
GROUP BY company_order.id 
ORDER BY company_order.order_no ASC";

        $results = Dbsqli::getSql2($sql);

        if(!is_array($results) || countgf($results) == 0) {

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=NULLLIST-'.$shopid.'-'.$expire.'-'.date("dmYHi").'.csv');
            echo "Ingen resultater";
            exit();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=customlist-'.$shopid.'-'.$expire.'-'.date("dmYHi").'.csv');

        foreach($results[0] as $key => $val) {
            echo $key.";";
        }
        //echo $this->getSpecialLabel();
        echo "\n";

        foreach($results as $row)
        {
            foreach($row as $key => $val) {
                echo utf8_decode(trimgf(str_replace(array("\r","\n",";"),array(""," ",""),$val)).";");
            }
            //echo $this->getSpecialValue();
            echo "\n";
        }

    }



}