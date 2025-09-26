<?php

namespace GFUnit\cardshop\pluklister;

class ReminderList extends PlukReport
{

    private function findShopList($shopinput) {
        // Get shops to run report for
        $shoplist = array();
        if($shopinput != null) {
            $inputsplit = explode(",", $shopinput);
            if (count($inputsplit) > 0) {
                foreach ($inputsplit as $input) {
                    if (intval($input) > 0) {
                        $shoplist[] = intval($input);
                    }
                }
            }
        } else {
            $shoplist[] = $this->shopid;
        }
        return $shoplist;
    }

    public function run($shopinput=null) {


        // Find shop list
        $shops = $this->findShopList($shopinput);
        if (count($shops) == 0) {
            return $this->setError("Ingen shops angivet");
        }

        // Check deadline
        $expireDate = \ExpireDate::getByExpireDate($this->expire);
        if($expireDate == null) return $this->setError("Ugyldig deadline");

        $shopName = "unknown";
        if(countgf($shops) == 1) {
            $shop = \Shop::find(intval($shops[0]));
            $shopName = $shop->name;
        } else {
            $shopName = implode("_",$shops);
        }


        // Headers
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="reminder-'.$shopName.'-' .$this->expire . '.csv"');

        // Prepare data
        $removeDuplicateMails = false;
        $sumCompany = 0;
        $sumUsers = 0;
        $sumSelected = 0;
        $sumNotSelected = 0;
        $usedEmails = array();

        // Define headers
        $header = array("CompanyID", "Virksomhed", "CVR", "Faktura adresse", "Faktura postnr", "Faktura by", "Levering virksomhed", "Levering adresse", "Levering postnr", "Levering by", "Kontaktperson", "Telefon", "E-mail", "Antal kort", "Antal valgt", "Antal ikke valgt", "Token","Bestilt");
        echo implode(';', $header) . "\n";

        // Find companies
        $companylist = \ShopUser::find_by_sql("SELECT company_id, count(id) as users, UNIX_TIMESTAMP(min(created_date)) as lowestdate, UNIX_TIMESTAMP(max(created_date)) as highestdate FROM shop_user WHERE shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $this->expire . "' && is_giftcertificate = 1 && blocked = 0 && shutdown = 0  GROUP BY company_id");
        //echo "SELECT company_id, count(id) as users FROM shop_user WHERE shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $deadline . "' && is_giftcertificate = 1 && blocked = 0 && company_id NOT IN (12441,12456,12468,12480,12500,12501,12505,12510,12512,12514,12515,12519,12520,12522,12523,12529,12530,12531,12532,12533,12535,12536,12537,12538,12539,12540,12541,12542,12543,12544,12546,12547,12549,12550,12551,12552,12553,12554,12555,12556,12557,12558,12559,12561,12562,12563,12564,12565,12566,12568,12569,12571,12572,12573,12574,12575,12576,12577,12578,12579,12580,12581,12583,12587,12588,12618,12689,12702,12994,13014,13037,13095,13098,13441,13521,13554,13555,13700,13088,18581,19308,19309,19310,12957) GROUP BY company_id";
        //echo "<br>".countgf($companylist); exit();

        // Go through companies
        foreach ($companylist as $companycount) {

            // Load company and orders
            $c = \Company::find($companycount->company_id);
            $orders = \ShopUser::find_by_sql("SELECT * FROM shop_user WHERE id NOT IN (SELECT shopuser_id FROM `order`) && company_id = " . $c->id . " && shop_id IN (" . implode(",", $shops) . ") && is_demo = 0 && expire_date = '" . $this->expire . "' && is_giftcertificate = 1 && shutdown = 0 && blocked = 0");

            // Get count
            $totalUsers = $companycount->users;
            $totalNotSelected = countgf($orders);
            $totalOrders = $totalUsers - $totalNotSelected;

            // Check negative number
            if ($totalOrders < 0) {
                echo "COUNT ERROR IN  " . $c->id;
                exit();
            }

            // Update total sum
            $sumCompany++;
            $sumUsers += $totalUsers;
            $sumSelected += $totalOrders;
            $sumNotSelected += $totalNotSelected;

            $dateLowest = date("d/m",$companycount->lowestdate);
            $dateHighest = date("d/m",$companycount->highestdate);
            $dateString = "";

            if($dateLowest == $dateHighest) $dateString = $dateLowest;
            else $dateString = $dateLowest." - ".$dateHighest;

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
                    $c->token,
                    $dateString

                );

                // Fix encoding and add
                foreach ($data as $key => $val) {
                    $data[$key] = str_replace(";",",",utf8_decode(trim($val)));
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

}