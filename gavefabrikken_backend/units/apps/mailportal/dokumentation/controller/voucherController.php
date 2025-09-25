<?php
// Controller CompanyNotesEx
// Date created  Wed, 11 Oct 2017 14:30:30 +0200
// Created by Bitworks
class VoucherController extends baseController {
    public function Index() {
        //$handle = fopen("rabatkuponer.txt", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $sql = "insert into voucher (voucher) values( '" . trimgf($line) . "')";
                //     Dbsqli::setSql2($sql);
                // process the line read.
            }
            fclose($handle);
        }
    }
    public function companyHasVoucher(){
        if (!is_int($_POST["company_id"]*1) || !isset($_POST["company_id"])) return;
        $sql = "select HasVoucher from company where id=".$_POST["company_id"];
        $companyRS = Dbsqli::getSql2($sql);
        if (sizeofgf($companyRS) == 0) die("Company does not exit");
        response::success(json_encode($companyRS[0]));
    }


    public function assign() {



        /*
        if (!is_int($_POST["company_id"]) || !isset($_POST["company_id"])) {
        return;
        }
         */
        //  KUN GAVEKLAB

     $sql = "SELECT distinct(`user_attribute`.shopuser_id) FROM `user_attribute`
                inner join shop_attribute on
                user_attribute.attribute_id = shop_attribute.id
                WHERE `attribute_value` LIKE 'ja'and
                user_attribute.`company_id` = ".$_POST["company_id"]." and
                shop_attribute.name =  'Gaveklubben tilmelding'";

        $shopuserListRS = Dbsqli::getSql2($sql);
        foreach ($shopuserListRS as $shopuser) {
            $sql = "select id from voucher where shop_user_id = 0 limit 1";
            $voucherIdRS = Dbsqli::getSql2($sql);
            if (sizeofgf($voucherIdRS) == 0) {
                response::success("Out of vouchers numbers");
                die("");
            }
            $sql = "update voucher set shop_user_id =".$shopuser["shopuser_id"].", company_id = ".$_POST["company_id"]." where id = " . $voucherIdRS[0]["id"];
            Dbsqli::setSql2($sql);

        }
       $sql = "update company set HasVoucher = 1 where id=".$_POST["company_id"];
       Dbsqli::setSql2($sql);
       echo json_encode(array("status"=>"1"));

    }
    public function queueVoucher() {

        $sql = "select * from voucher where is_send = 0 and shop_user_id != 0  limit 100";
        $voucherListRS = Dbsqli::getSql2($sql);
        if (sizeofgf($voucherListRS) == 0) {
            die("All voucher send");
        }

        foreach ($voucherListRS as $voucher) {
            // load user email
            echo $voucher["shop_user_id"];
            $sql = "select attribute_value from user_attribute where is_email = 1 and shopuser_id=" . $voucher["shop_user_id"];
            $emailRS = Dbsqli::getSql2($sql);
            // reg
            $sql = "update voucher set is_send = 1 where id =" . $voucher["id"];
            Dbsqli::setSql2($sql);
            // send mail

            $this->sendVoucher($voucher,$emailRS[0]["attribute_value"]);

        }
        system::connection()->commit();
        echo "Vouchers send: " . sizeofgf($voucherListRS);
    }
    private function sendVoucher($voucherData,$mail) {

        $mailTemplate = mailtemplate::find(14);
        $mailTemplate->template_receipt;
        $template = str_replace('{code}', $voucherData["voucher"], $mailTemplate->template_receipt);
      //  $template.=  $mail;

        $maildata = [];
        $maildata['sender_email'] = "no-reply@gavefabrikken.dk";
        $maildata['recipent_email'] = $mail; //"us@gavefabrikken.dk"; //; //;
        $maildata['subject'] = utf8_encode($mailTemplate->subject_receipt);
        $maildata['body'] = utf8_decode($template);
        $maildata['mailserver_id'] = 4;
        MailQueue::createMailQueue($maildata);
    }

}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>