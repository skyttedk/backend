<?php
// Controller GiftCertificate
// Date created  Mon, 11 Jul 2016 11:35:04 +0200
// Created by Bitworks
class GiftCertificateController Extends baseController {

    public function Index() {
    }
    public function create() {
        $giftcertificate = GiftCertificate::createGiftCertificate($_POST);
        response::success(make_json("giftcertificate", $giftcertificate));
    }
    public function read() {
        $giftcertificate = GiftCertificate::readGiftCertificate($_POST['id']);
        response::success(make_json("giftcertificate", $giftcertificate));
    }
    public function update() {
        $giftcertificate = GiftCertificate::updateGiftCertificate($_POST);
        response::success(make_json("giftcertificate", $giftcertificate));
    }
    public function delete() {
        $giftcertificate = GiftCertificate::deleteGiftCertificate($_POST['id']);
        response::success(make_json("giftcertificate", $giftcertificate));
    }
//Create Variations of readAll
    public function readAll() {
        $giftcertificates = GiftCertificate::all();
//$options = array('only' => array('id', 'name', 'username', 'admin', 'active'));
        $options = array();
        response::success(make_json("giftcertificates", $giftcertificates, $options));
    }

    public function dashboard()
    {
        $this->registry->template->show('system_giftcertificate');
    }



    //---------------------------------------------------------------------------------------
    // Custom Controller Actions
    //---------------------------------------------------------------------------------------
    public function addToShop() {
        $giftCertificateId = $_POST['certificate_id'];
        $shopId = $_POST['shop_id'];
        $companyId = $_POST['company_id'];
        GiftCertificate::addToShop($giftCertificateId, $shopId, $companyId);
        $dummy = array();
        response::success(make_json("result", $dummy));
    }
    public function removeFromShop() {
        $giftCertificateId = $_POST['certificate_id'];
        GiftCertificate::removeFromShop($giftCertificateId);
        $dummy = array();
        response::success(make_json("result", $dummy));
    }

    public function exportBatch() {

        // Parse json data
        $data = isset($_GET["data"]) ? $_GET["data"] : "";
        $data = json_decode($data,true);

        // Quantity
        $quantity = intval($data["quantity"]);
        $reservation_group = intval($data["reservation_group"]);
        $expire_date = trimgf($data["expire_date"]);

        $filename = "ukendt-kortserie";
        $resgroupName = "Ukendt gruppe";

        $reservationGroupList = GiftCertificate::find_by_sql("SELECT * FROM `reservation_group` WHERE id = ".intval($reservation_group));
        if(count($reservationGroupList) > 0) {
            $filename = $reservationGroupList[0]->name."-".$expire_date;
            $resgroupName = $reservationGroupList[0]->name;
        }

        // Start csv content - headers
        $csvContent = "Kortnr;Adgangskode;Udløbsdato;Kortserie\r\n";
        $exportDate = time();

        // Pull data
        $giftcertificateList = GiftCertificate::find_by_sql("SELECT * FROM `gift_certificate` WHERE reservation_group = ".intval($reservation_group)." && expire_date = \"".addslashes(trimgf($expire_date))."\" && is_emailed = 0 && export_date IS NULL ORDER BY certificate_no ASC LIMIT ".intval($quantity));

        if(count($giftcertificateList) == 0) {
            $csvContent .= "Ingen kort fundet på kortserie ".$reservation_group." - dato: ".$expire_date;
            $filename = "fejl-ingenkort-".$filename;
        }
        else if(count($giftcertificateList) != $quantity) {
            $csvContent .= "Antal stemmer ikke, der blev bedt om ".$quantity." kort, der er kun fundet ".countgf($giftcertificateList)." ".$reservation_group." - dato: ".$expire_date;
            $filename = "fejl-antal-".$filename;
        }
        else {
            foreach($giftcertificateList as $giftCertificate) {

                // Add csv row
                $csvContent .= $giftCertificate->certificate_no.";".$giftCertificate->password.";".$giftCertificate->expire_date->format("Y-m-d").";".$resgroupName."\r\n";

                // Update card
                $gf = GiftCertificate::find($giftCertificate->id);
                if($gf->id == $giftCertificate->id) {
                    $gf->export_date = date('d-m-Y H:i:s',$exportDate);
                    $gf->save();
                } else {
                    echo "ID MISMATCH";
                    exit();
                }


            }
        }

        // Commit
        system::connection()->commit();

        // Output csv file
        header('Content-Encoding: UTF-8');
        header('Content-Type: application/csv;charset=UTF-8');
        header('Content-Disposition: attachement; filename="presentlist-' . $filename . '.csv"');
        echo "\xEF\xBB\xBF";
        echo ($csvContent);

    }

    public function createSeasonBatches()
    {
/*
        ini_set('max_execution_time', 60*60);
        ini_set("session.gc_maxlifetime",8640);
        ini_set('memory_limit','850M');
*/

        if(!isset($_GET["token"]) || $_GET["token"] != "yes-season-batch") {
            echo "Invalid token - be aware, a lot of cards will be generated!";
            exit();
        }

        $seasonBatches = array(
            // EMAIL CARDS
/*
            0 => array(
                "2021-10-31" => array("quantity" => 100000,"print" => 0,"delivery" => 0),
                "2021-11-07" => array("quantity" => 100000,"print" => 0,"delivery" => 0),
                "2021-11-14" => array("quantity" => 100000,"print" => 0,"delivery" => 0),
                "2021-11-21" => array("quantity" => 100000,"print" => 0,"delivery" => 0),
                "2021-11-28" => array("quantity" => 100000,"print" => 0,"delivery" => 0),
                "2021-12-31" => array("quantity" => 100000,"print" => 0,"delivery" => 0),
                "2022-04-01" => array("quantity" => 100000,"print" => 0,"delivery" => 1),
                "2022-12-31" => array("quantity" => 100000,"print" => 0,"delivery" => 1),
            )
*/

            // JULEGAVEKORT DK
/*
            1 => array(
                "2021-10-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-07" => array("quantity" => 100000,"print" => 1,"delivery" => 0),
                "2021-11-14" => array("quantity" => 50000,"print" => 1,"delivery" => 0),
                //"2021-11-21" => array("quantity" => 100000,"print" => 1,"delivery" => 0),
                "2021-11-28" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2021-12-31" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                //"2022-04-01" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
                //"2022-12-31" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
            )
*/
/*
            // DESIGN / GULL / JULEGAVEKORT NO
            8 => array(
                "2021-10-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-07" => array("quantity" => 100000,"print" => 1,"delivery" => 0),
                "2021-11-14" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-21" => array("quantity" => 100000,"print" => 1,"delivery" => 0),
                //"2021-11-28" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2021-12-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2022-04-01" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
                //"2022-12-31" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
            ),
            9 => array(
                "2021-10-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-07" => array("quantity" => 100000,"print" => 1,"delivery" => 0),
                "2021-11-14" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-21" => array("quantity" => 100000,"print" => 1,"delivery" => 0),
                //"2021-11-28" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2021-12-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2022-04-01" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
                //"2022-12-31" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
            ),
            4 => array(
                "2021-10-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-07" => array("quantity" => 100000,"print" => 1,"delivery" => 0),
                "2021-11-14" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-21" => array("quantity" => 100000,"print" => 1,"delivery" => 0),
                //"2021-11-28" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2021-12-31" => array("quantity" => 40000,"print" => 1,"delivery" => 0),
                //"2022-04-01" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
                //"2022-12-31" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
            )
*/
            /*
             // 24 GAVER / GULD
             2 => array(
                //"2021-10-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2021-11-07" => array("quantity" => 40000,"print" => 1,"delivery" => 0),
                //"2021-11-14" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2021-11-21" => array("quantity" => 50000,"print" => 1,"delivery" => 0),
                //"2021-11-28" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2021-12-31" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2022-04-01" => array("quantity" => 20000,"print" => 1,"delivery" => 1),
                //"2022-12-31" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
            ),
            3 => array(
                //"2021-10-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2021-11-07" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-14" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2021-11-21" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-28" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2021-12-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2022-04-01" => array("quantity" => 20000,"print" => 1,"delivery" => 1),
                //"2022-12-31" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
            )
            */

            // DRØMME / JULLKLAPPAR SE
            5 => array(
                //"2021-10-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2021-11-07" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-14" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2021-11-21" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-28" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2021-12-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2022-04-01" => array("quantity" => 20000,"print" => 1,"delivery" => 1),
                //"2022-12-31" => array("quantity" => 100000,"print" => 1,"delivery" => 1),
            ),
            10 => array(
                //"2021-10-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2021-11-07" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-14" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                "2021-11-21" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2021-11-28" => array("quantity" => 30000,"print" => 1,"delivery" => 0),
                "2021-12-31" => array("quantity" => 20000,"print" => 1,"delivery" => 0),
                //"2022-04-01" => array("quantity" => 20000,"print" => 1,"delivery" => 1),
                "2022-12-31" => array("quantity" => 50000,"print" => 1,"delivery" => 1),
            )
            
        );

        foreach($seasonBatches as $resGroupID => $batchWeeks) {
            foreach($batchWeeks as $expireDate => $batchInfo) {

                $quantity = intval($batchInfo["quantity"]);
                $isprint = $batchInfo["print"] == 1;
                $isdelivery = $batchInfo["delivery"] == 1 ? 1 : 0;

                for ($i = 1; $i <= $quantity; $i++) {
                    $giftcertificate = new GiftCertificate();
                    if ($isprint) {
                        $giftcertificate->is_emailed = 0;
                        $giftcertificate->is_printed = 1;
                    }
                    else {
                        $giftcertificate->is_emailed = 1;
                        $giftcertificate->is_printed = 0;
                    }

                    $giftcertificate->is_delivery = $isdelivery;
                    if ($giftcertificate->is_printed == "1")
                        $giftcertificate->no_series = 2;
                    else
                        $giftcertificate->no_series = 3;

                    $giftcertificate->certificate_no = Numberseries::getNextNumber($giftcertificate->no_series);

                    $pwdUnique = false;
                    while (!$pwdUnique) {
                        $giftcertificate->password = $this->generateStrongPassword(6, false, 'ld');
                        $pwdUnique = !$this->pwdExists($giftcertificate->password);
                    }


                    $giftcertificate->expire_date = $expireDate;
                    $giftcertificate->week_no = 0;
                    $giftcertificate->value = 0;

                    if ($isprint) {
                        $giftcertificate->reservation_group = $resGroupID;
                    } else {
                        $giftcertificate->reservation_group = 0;
                    }

                    $giftcertificate->shop_id = 0;
                    $giftcertificate->blocked = 0;
                    $giftcertificate->save();
                }

                echo "Created ".$quantity." cards on ".$resGroupID." - ".$expireDate." - ".($isprint ? "print" : "email")." - ".($isdelivery ? "private delivery" : "company delivery")."<br>";

            }
        }

        system::connection()->commit();
    }

// NB en del ting er hardcoded i denne funktion
    public function createBatch() {

        if(!\GFCommon\Model\Access\BackendPermissions::session()->hasPermission(\GFCommon\Model\Access\BackendPermissions::PERMISSION_SYSTEM))
        {
            throw new exception('Du har ikke rettighed til denne funktion!');
        }
/*
        ini_set("session.gc_maxlifetime",8640);
        ini_set('memory_limit','850M');
*/

        $expiredate = ExpireDate::find_by_expire_date_and_blocked($_POST['expire_date'], 0);
        if(!isset($expiredate))
          throw new exception('Invalid expire date: '.$_POST['expire_date']);

        if($_POST['is_delivery']==1)
          if($expiredate->is_delivery==0)
            throw new exception('Denne dato underst�tter ikke levering');


        if (!$expiredate)
            throw new exception('Ugyldig dato');

        if(!isset($_POST["reservation_group"]))
            throw new exception('Reservation group not set.');

        $reservationGroup = intval($_POST['reservation_group']);
        if ($_POST['is_print'] == "1") {
            $reservationGroupList = GiftCertificate::find_by_sql("SELECT * FROM `reservation_group` WHERE id = ".$reservationGroup);
            if(!is_array($reservationGroupList) || countgf($reservationGroupList) == 0) {
                throw new exception('Invalid reservation group: '.$reservationGroup);
            }
        }


        //Create a batch of gift certificates
        $quantity = $_POST['quantity'];
        for ($i = 1; $i <= $quantity; $i++) {
            $giftcertificate = new GiftCertificate();
            if ($_POST['is_print'] == "1") {
                $giftcertificate->is_emailed = 0;
                $giftcertificate->is_printed = 1;
            }
            else {
                $giftcertificate->is_emailed = 1;
                $giftcertificate->is_printed = 0;
            }
            $giftcertificate->is_delivery = $_POST['is_delivery'];
            if ($giftcertificate->is_printed == "1")
                $giftcertificate->no_series = 2;
            else
                $giftcertificate->no_series = 3;

            $giftcertificate->certificate_no = Numberseries::getNextNumber($giftcertificate->no_series);

            $pwdUnique = false;
            while (!$pwdUnique) {
                $giftcertificate->password = $this->generateStrongPassword(6, false, 'ld');
                $pwdUnique = !$this->pwdExists($giftcertificate->password);
            }
            $giftcertificate->expire_date = $_POST['expire_date'];
            $giftcertificate->week_no = 0;
            $giftcertificate->value = 0;

            if ($_POST['is_print'] == "1") {
               $giftcertificate->reservation_group = $reservationGroup;
            } else {
                $giftcertificate->reservation_group = 0;
            }

            $giftcertificate->shop_id = 0;
            $giftcertificate->blocked = 0;
            $giftcertificate->save();
        }
        $dummy = array();
        response::success(make_json("result", $dummy));
    }

    //Used by batch function
    private function pwdExists($pwd) {
        $giftcertificate = GiftCertificate::find_by_password($pwd);
        if ($giftcertificate)
            return (true);
        else
            return (false);
    }

    //b�r ligge p� shopuser
    public function unblockGiftCertificate() {
        $shopuser = ShopUser::find($_POST['id']);
        $shopuser->blocked = false;
        $shopuser->save();
        $dummy[] = [];
        response::success(json_encode($dummy));
    }

    //b�r ligge p� shopuser
    public function blockGiftCertificate() {
        $shopuser = ShopUser::find($_POST['id']);
        $shopuser->blocked = true;
        $shopuser->save();
        $dummy[] = [];
        response::success(json_encode($dummy));
    }

    public function changeExpireDate() {
        $shopuser = ShopUser::find($_POST['id']);
        $shopuser->expire_date = $_POST['expire_date'];
        if($_POST['expire_date'] ==  "2022-01-01" || $_POST['expire_date'] ==  "2021-04-01" || $_POST['expire_date'] ==  "2021-01-03" || $_POST['expire_date'] ==  "2020-11-07"  ){
            $shopuser->is_delivery = 1;
        } else {
            $shopuser->is_delivery = 0;
        }
        
        // der skal tjekkes p� at expire_date findes i expire_date tabellen.
        $shopuser->save();
        $dummy[] = [];
        response::success(json_encode($dummy));
    }

    //Used by batch function
    function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds') {
        $sets = array();
        if (strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjknpqrstuvxyz';
        if (strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if (strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if (strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?';
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - countgf($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if (!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }
}
?>