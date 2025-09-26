<?php

namespace GFBiz\valgshop;



class ValgshopFordeling
{

    private $shop;
    private $warningList;

    private $company;

    private $primaryAddress = null;


    public function __construct($shopid)
    {
        $this->loadShopData($shopid);
    }

    private function loadShopData($shopid)
    {

        // Init members
        $this->warningList = array();

        // LOAD SHOP
        $this->shop = \Shop::find($shopid);
        if(!($this->shop instanceof \Shop) || $this->shop->id == 0) {
            throw new \Exception("Could not find shop: ".$shopid.".");
        }

        // LOAD COMPANY
        $companyList = \Company::find_by_sql("SELECT company.* FROM company, company_shop WHERE company_shop.shop_id = ".intval($this->shop->id)." AND company_shop.company_id = company.id");
        if(count($companyList) > 0 ) $this->company = $companyList[0];

        // Check adress choice
        if($this->shop->location_type == 0) return $this->setError("Der er ikke foretaget et lokations/adresse valg pa denne shop.");

        // LOAD USERS
        $this->userMap = array();
        $users = \ShopUser::all(array('shop_id' => $this->shop->id));
        foreach($users as $user) {
            $this->userMap[$user->id] = $user;
        }

        // Load user attributes and process them
        $this->attributeList = \ShopAttribute::all(array('conditions' => array('shop_id' => $this->shop->id), 'order' => '`index` asc'));
        $this->processAttributes();

        // Load user data
        $dataList = \UserAttribute::find('all',array('conditions' => array('shop_id' => $this->shop->id)));
        $this->userData = array();
        foreach($dataList as $ua) $this->userData[$ua->shopuser_id][$ua->attribute_id] = $ua->attribute_value;

        // Load locations
        $addressList = \ShopAddress::find('all', array('conditions' => "shop_id = ".intval($this->shop->id)));
        $this->locationMap = array();
        foreach($addressList as $address)
        {
            if($this->primaryAddress == null)  $this->primaryAddress = new reportLocation($address,$this->company);
            if(trimgf($address->locations) != "")
            {
                $lines = explode("\n",$address->locations);
                for($i=0;$i<count($lines);$i++)
                {
                    if(trimgf($lines[$i]) != "")
                    {
                        if(isset($this->locationMap[trimgf($lines[$i])])) $this->addWarning("Lokationen ".$lines[$i]." er tilknyttet mere end 1 adresse");
                        $this->locationMap[trimgf($lines[$i])] = new reportLocation($address,$this->company);
                    }
                }
            }
        }

        // Load presents
        $sortOrder = "DESC";

        $this->presentMap = array();
        $presentList = \Present::find_by_sql("SELECT * FROM `present` WHERE shop_id = ".intval($this->shop->id)." ORDER BY id ".$sortOrder);
        $presentIDList = array();
        foreach($presentList as $present) {
            $this->presentMap[$present->id] = $present;
            $presentIDList[] = $present->id;
        }

        // Load present models
        $this->modelMap = array();
        $this->modelMap2 = array();

        if(count($presentIDList) == 0) { throw new \Exception("No presents on the shop"); }

        $modelList = \PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE present_id IN (".implode(",",$presentIDList).") ORDER BY language_id DESC");
        foreach($modelList as $model)
        {
            if($model->language_id == 1)
            {
                $modelNameList = array();
                $this->modelMap[$model->present_id][$model->model_id] = $model;
            }

            // Add model id's to map
            if(!isset($this->modelMap2[$model->model_id])) $this->modelMap2[$model->model_id] = array();
            $this->modelMap2[$model->model_id][] = $model->id;

        }

        // Load orders
        $this->orderList = \Order::all(array('shop_id' => $this->shop->id,"is_demo" => 0));
        $this->sumData = array();

        // Process orders and split them into location map
        $orderLocationMap = $this->compileOrderList("location");

        return true;

    }

    /**
     * COMPILE ORDER LIST FROM DATA
     */

    private function compileOrderList($split=null)
    {

        $this->sumData = array();
        $dataList = array();
        $processedUsers = array();

        foreach($this->orderList as $order)
        {

            $hasErrors = false;
            $processedUsers[] = $order->shopuser_id;
            $pml = array();
            $alias = "";

            // Check user
            if(!isset($this->userMap[$order->shopuser_id]) && !$this->usePartial)
            {
                $this->addWarning("Kunne ikke finde brugeren ".$order->shopuser_id." tilknyttet ordre ".$order->id);
                $hasErrors = true;
            }

            if(isset($this->userMap[$order->shopuser_id])) {

                // Find alias
                $alias = "";
                $presentName = "";
                $presentModel = "";
                $varenr = "";

                if(!isset($this->presentMap[$order->present_id]))
                {
                    $this->addWarning("Kunne ikke finde gaven med id ".$order->present_id." som er tilknyttet ordre ".$order->id);
                    $hasErrors = true;
                }

                else
                {
                    $present = $this->presentMap[$order->present_id];
                    $presentName = $present->nav_name;
                    $model_id = $order->present_model_id;

                    // NO ALIAS JSON DEFINED
                    if(intval($present->alias) == 0)
                    {
                        $this->addWarning("Kunne ikke finde gavealias pa gaven ".$present->name." (".$present->id.") som er tilknyttet ordre ".$order->id);
                        $hasErrors = true;
                    }

                    // ALIAS DEFINED
                    else
                    {
                        $alias = $present->alias;
                        if(!isset($this->modelMap[$present->id]) || !isset($this->modelMap[$present->id][$model_id]))
                        {
                            $this->addWarning("Kunne ikke finde model ".$model_id." pa gaven ".$present->name." (".$present->id.") som er tilknyttet ordre ".$order->id);
                            $hasErrors = true;
                        }
                        else
                        {
                            $model = $this->modelMap[$present->id][$model_id];
                            if(trimgf($model->fullalias) == "")
                            {
                                $this->addWarning("Kunne ikke finde gavealias pa model ".$model_id." til gaven ".$present->name." (".$present->id.") som er tilknyttet ordre ".$order->id);
                                $hasErrors = true;
                            }
                            else
                            {
                                $alias = $model->fullalias;
                                $varenr = $model->model_present_no;

                                $name = array();
                                if(trimgf($model->model_name) != "") $name[] = $model->model_name;
                                if(trimgf($model->model_no) != "") $name[] = $model->model_no;

                                if(count($name) == 2)
                                {
                                    $presentName = $name[0];
                                    $presentModel = $name[1];
                                }
                                else $presentName = implode(", ",$name);

                            }
                        }




                    }

                }

            }

            $alias = strtolower($alias);


            // Find location
            $location = $this->userLocation($order->shopuser_id);
            if($location == null) $hasErrors = true;

            // If no problems, add to list
            if($hasErrors == false && isset($this->userMap[$order->shopuser_id]))
            {

                if(trimgf($alias) == "") {
                    $this->addWarning("Gaven ".$presentName." ".$presentModel." mangler alias.");
                }

                // Compile data
                $row = array(
                    "user_id" => $order->shopuser_id,
                    "order" => $order,
                    "user" => $this->userMap[$order->shopuser_id],
                    "presentAlias" => strtolower($alias),
                    "presentName" => $presentName,
                    "presentModel" => $presentModel,
                    "location" => $location,
                    "modelParts" => countgf($pml),
                    "timestamp" => $order->order_timestamp,
                    "varenr" => $varenr
                );


                $description = $presentName.", ".$presentModel;

                // Add to sum
                if(!isset($this->sumData[$alias])) $this->sumData[$alias] = array("count" => 0,"varenr" => $varenr,"description" => $description,"locationmap" => array());
                $this->sumData[$alias]["count"]++;

                if(!isset($this->sumData[$alias]["locationmap"][$location->string]))  $this->sumData[$alias]["locationmap"][$location->string] = 0;
                $this->sumData[$alias]["locationmap"][$location->string]++;

                if(!isset($this->sumData[$alias]["invoicemap"][$location->invoiceid]))  $this->sumData[$alias]["invoicemap"][$location->invoiceid] = 0;
                $this->sumData[$alias]["invoicemap"][$location->invoiceid]++;

                if(!isset($this->sumData[$alias]["adressmap"][$location->adressid]))  $this->sumData[$alias]["adressmap"][$location->adressid] = 0;
                $this->sumData[$alias]["adressmap"][$location->adressid]++;


                // Add to location
                if($split == "location") {
                    $dataList[$location->string][] = $row;
                }
                else $dataList[] = $row;

            }

        }

        foreach($this->userMap as $userid => $user)
        {

            $hasErrors = false;

            if(!in_array($userid,$processedUsers) && $user->is_demo == 0)
            {

                $location = $this->userLocation($userid);
                if($location == null) $hasErrors = true;

                // If no problems, add to list
                if($hasErrors == false)
                {
                    $alias = "00";
                    $presentName = "IKKE VALGT";
                    $presentModel = "";

                    // Compile data
                    $row = array(
                        "user_id" => $userid,
                        "order" => null,
                        "user" => $user,
                        "presentAlias" => strtolower($alias),
                        "presentName" => $presentName,
                        "presentModel" => $presentModel,
                        "location" => $location,
                        "modelParts" => 0,
                        "timestamp" => null
                    );

                    // Add to sum list
                    $description = $presentName.", ".$presentModel;
                    if($row["modelParts"] == 0) $description = $presentName;
                    else if($row["modelParts"] == 2) $description = $presentModel;

                    // Add to sum
                    if(!isset($this->sumData[$alias])) $this->sumData[$alias] = array("count" => 0,"varenr" => "","description" => $description,"locationmap" => array());
                    $this->sumData[$alias]["count"]++;

                    if(!isset($this->sumData[$alias]["locationmap"][$location->string]))  $this->sumData[$alias]["locationmap"][$location->string] = 0;
                    $this->sumData[$alias]["locationmap"][$location->string]++;

                    if(!isset($this->sumData[$alias]["invoicemap"][$location->invoiceid]))  $this->sumData[$alias]["invoicemap"][$location->invoiceid] = 0;
                    $this->sumData[$alias]["invoicemap"][$location->invoiceid]++;

                    if(!isset($this->sumData[$alias]["adressmap"][$location->adressid]))  $this->sumData[$alias]["adressmap"][$location->adressid] = 0;
                    $this->sumData[$alias]["adressmap"][$location->adressid]++;


                    // Add to location
                    if($split == "location") $dataList[$location->string][] = $row;
                    else $dataList[] = $row;

                }
            }
        }

        ksort($this->sumData);

//        echo "<pre>".json_encode($this->sumData,JSON_PRETTY_PRINT)."</pre>";

        return $dataList;

    }

    public function getPresentDataForInvoiceID($invoiceid) {

        $presentData = array();
        foreach($this->sumData as $alias => $data) {
            foreach($data["invoicemap"] as $sumInvoiceID => $count) {
                if($sumInvoiceID == $invoiceid) {
                    $presentData[] = array("alias" => $alias, "varenr" => $data["varenr"],"description" => $data["description"],"count" => $count);
                }
            }
        }
        return $presentData;

    }

    public function getPresentDataForAdressID($addressid) {
        $presentData = array();
        foreach($this->sumData as $alias => $data) {
            foreach($data["adressmap"] as $sumInvoiceID => $count) {
                if($sumInvoiceID == $addressid) {
                    $presentData[] = array("alias" => $alias, "varenr" => $data["varenr"],"description" => $data["description"],"count" => $count);
                }
            }
        }
        return $presentData;
    }
    
    /**
     * ERROR / WARNING FUNCTIONALITY
     */

    private function setError($error)
    {
        $this->error = $error;
        return false;
    }

    public function getWarnings() {
        return $this->warningList;
    }
    
    private function addWarning($message)
    {
        if($message == false || $message == null) return;
        if(trimgf("".$message) != "" && strlen(trimgf("".$message)) > 1)
            $this->warningList[] = $message;
        
    }

    /**
     * ADRESS / LOCATION FUNCTIONALITY
     */

    private function userLocation($userid)
    {
        // Location from dropdown
        if($this->shop->location_type == 1)
        {
            $locAttr = $this->getUserAttribute($userid,$this->shop->location_attribute_id);
            if(isset($this->locationMap[$locAttr]))
            {
                return $this->locationMap[$locAttr];
            }
            else
            {
                //if(trimgf($locAttr) == "") $this->addWarning("Ingen lokation angivet for: bruger ".$this->getName($userid)." -".$userid." - type 1.");
                //else $this->addWarning("Der findes ikke en adresse for lokationen: ".$locAttr." (bruger ".$this->getName($userid)." -".$userid." - type 1).");
                return new reportLocation("",$this->company);
            }
        }
        // Location from freetext
        else if($this->shop->location_type == 2)
        {
            $locAttr = $this->getUserAttribute($userid,$this->shop->location_attribute_id);
            if(isset($this->locationMap[$locAttr]))
            {
                return $this->locationMap[$locAttr];
            }
            else
            {
                //if(trimgf($locAttr) == "") $this->addWarning("Ingen lokation angivet for: bruger ".$this->getName($userid)." -".$userid." - type 2.");
                //else $this->addWarning("Der findes ikke en adresse for lokationen: ".$locAttr." (bruger ".$this->getName($userid)." -".$userid." - type 2).");
                return new reportLocation("",$this->company);
            }

        }
        // Location from text
        else if($this->shop->location_type == 3)
        {
            $location = $this->getUserAttribute($userid,$this->shop->location_attribute_id);
            //if($location == "") $this->addWarning("Brugeren ".$this->getName($userid)." (".$userid.") har ikke nogen lokation tilknyttet (type 3).");
            return new reportLocation($location,$this->company);
        }
        else if($this->shop->location_type == 4)
        {
            return $this->primaryAddress == null ? new reportLocation("",$this->company) : $this->primaryAddress;
            //foreach($this->locationMap as $loc) return  $loc;
            //return new reportLocation("");
        }
        else  return $this->primaryAddress == null ? new reportLocation("",$this->company) : $this->primaryAddress;
    }


    /**
     * USER ATTRIBUTE FUNCTIONALITY
     */

    private $attrUsername;
    private $attrEmail;
    private $attrName;
    private $attrUseEmail;

    private function processAttributes()
    {

        // Prepare input
        $attributeIDs = array();

        $this->selectedAttributes = array();
        foreach($attributeIDs as $id)
        {
            if(intval($id) > 0) $this->selectedAttributes[] = intval($id);
        }

        foreach($this->attributeList as $at)
        {
            if($at->is_username == 1)
            {
                $this->attrUsername = $at->id;
                if(in_array($at->id,$attributeIDs))
                {
                    $this->extraAttributes[] = array("id" => $at->id, "name" => $at->name);
                }
            }
            else if($at->is_email == 1)
            {
                $this->attrEmail = $at->id;
                $this->attrUseEmail = in_array($at->id,$attributeIDs);
            }
            else if($at->is_name == 1) $this->attrName = $at->id;
            else {
                if(in_array($at->id,$attributeIDs))
                {
                    $this->extraAttributes[] = array("id" => $at->id, "name" => $at->name);
                }
            }
        }

    }

    protected function getUserAttribute($user_id,$attribute_id)
    {
        if($attribute_id > 0 && isset($this->userData[$user_id]) && isset($this->userData[$user_id][$attribute_id])) return $this->userData[$user_id][$attribute_id];
        else return "";
    }

    protected function getName($user_id)
    {
        if($this->attrName != null && isset($this->userData[$user_id]) && isset($this->userData[$user_id][$this->attrName]))
        {
            if($this->shop->id == 1575) {
                return $this->userData[$user_id][$this->attrName]." ".$this->getUserAttribute($user_id,10737);
            }
            return $this->userData[$user_id][$this->attrName];
        }
        else return "";
    }

    protected function getEmail($user_id)
    {
        if($this->attrName != null && isset($this->userData[$user_id]) && isset($this->userData[$user_id][$this->attrEmail])) return $this->userData[$user_id][$this->attrEmail];
        else return "";
    }

    protected function getUsername($user_id)
    {
        if($this->attrName != null && isset($this->userData[$user_id]) && isset($this->userData[$user_id][$this->attrUsername])) return $this->userData[$user_id][$this->attrUsername];
        else return "";
    }

}


/**
 *  REPORT LOCATION CLASS
 */
class reportLocation
{


    public $adressid = 0;

    public $invoiceid = 0;

    public $string = "";
    public $att;
    public $phone;
    public $company;
    public $adress;
    public $zipcity;
    public $country;
    public $valid;
    private $companyObj;
    public $sortOrder;

    public function getZip() {
        $parts = explode(" ",$this->zipcity);
        return isset($parts[0]) ? $parts[0] : "";
    }

    public function getCity() {
        $parts = explode(" ",$this->zipcity,2);
        return isset($parts[1]) ? $parts[1] : "";
    }

    public function __construct($input,$company=null)
    {

        $this->companyObj = $company;
        if($input === null) $input = "";

        $this->valid = false;
        if($input instanceof \ShopAddress)
        {
            $this->adressid = $input->id;
            $this->att = $input->att;
            $this->phone = $input->phone;
            $this->company = $input->name;
            $this->adress = $input->address;
            $this->zipcity = $input->zip." ".$input->city;
            $this->country = $input->country;
            $this->valid = true;
            $this->sortOrder = $input->index;
            $this->string =  $this->company.", ".$this->adress.", ".$this->zipcity.", ".$this->country.", ".$this->att;
            $this->invoiceid = $input->shop_invoice_id;
        }
        else
        {
            $this->string = $input;
            $lines = explode(",",$input);
            if(count($lines) > 0 && trimgf($input) != "") $this->valid = true;
            if(count($lines) > 0) $this->company = $lines[0];
            if(count($lines) > 1) $this->adress = $lines[1];
            if(count($lines) > 2) $this->zipcity = $lines[2];
            if(count($lines) > 3) $this->country = $lines[3];
            if(count($lines) > 4) $this->att = $lines[4];
            if(count($lines) > 5) $this->phone = $lines[5];

        }

        if(trimgf($this->phone) == "" && $this->companyObj != null) {
            $this->phone = $this->companyObj->contact_phone;
        }

        if($this->valid == true)
        {
            self::$knownLocations[trimgf($this->string)] = $this;
        }


    }

    private static $knownLocations = array();
    public static function getByString($string)
    {
        if(isset(self::$knownLocations[trimgf($string)])) return self::$knownLocations[trimgf($string)];
        else return new reportLocation(null);
    }


}
