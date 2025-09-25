<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
class overbookedApprovalController Extends baseController {
    public function Index() {

    }
    public function approv() {

        if($_GET["token"] != "sdafoijhiousadfy8a9asudkhasdf89asdf89DSAFAFSFAD!!fadsfklas"){
            die("No access");
            return;
        }
        $shopId =  $_GET["shopid"];
        $action = $_GET["action"];
        $shopApproval = ShopApproval::find('first', [
            'conditions' => ['shop_id = ?', $shopId]
        ]);

        if ($shopApproval) {
            $shopApproval->$action = 3;
            $rs =$shopApproval->save();
            \system::connection()->commit();
            echo "Godkendelse registeret";
        } else {
            echo "Ingen ShopApproval fundet for dette shop_id";
        }
    }
    public function noApprov() {

        if($_GET["token"] != "sdafoijhiousadfy8a9asudkhasdf89asdf89DSAFAFSFAD!!fadsfklas"){
            die("No access");
            return;
        }
        $shopId =  $_GET["shopid"];
        $sa =  $_GET["sa"];
        $action = $_GET["action"];
        $shopId =  $_GET["shopid"];
        $email = $this->findSalespersonEmail($sa);
        $shopName = $this->findShopName($shopId);
        $felt = "";
        if($action == "shop_start") $felt = "Shop start";
        if($action == "shop_end") $felt = "Shop luk";
        if($action == "shop_delivery") $felt = "Leveringsdato";

        $txt = "<br><br><div>Den valgte dato for ".$felt." i shop: ".$shopName. " er ikke blevet godkendt</div>";
        $this->sendApprovalMail($txt,$email);


    }

    public function sendApprovalMail($mailTxt,$email){
        $mailqueue = new \MailQueue();
        $mailqueue->sender_name  = 'Gavefabrikken';
        $mailqueue->sender_email = 'Gavefabrikken@gavefabrikken.dk';
        $mailqueue->recipent_email = $email;
        $mailqueue->subject ='Ordre overbooket dato ejgodkendt';
        $mailqueue->body =$mailTxt;
        $mail = $mailqueue->save();
        \system::connection()->commit();
        echo "Mail sendt til: ".$email;

    }


    private function findShopName($id) {
        try {
            $shop = Shop::find('first', [
                'conditions' => ['id = ?', $id],
                'select' => 'name'  // Vi henter kun name-feltet for effektivitet
            ]);

            if ($shop) {
                return $shop->name;
            } else {
                return null; // Eller du kan kaste en exception her, afhængigt af dine præferencer
            }
        } catch (Exception $e) {
            // Log fejlen
            error_log("Fejl ved søgning efter shop navn: " . $e->getMessage());
            // Returner null eller kast exceptionen videre, afhængigt af hvordan du vil håndtere fejl
            return null;
        }
    }

    private function findSalespersonEmail($code) {
        try {
            $salesperson = NavisionSalesperson::find('first', [
                'conditions' => ['code = ?', $code],
                'select' => 'email'  // Vi henter kun email-feltet for effektivitet
            ]);

            if ($salesperson) {
                return $salesperson->email;
            } else {
                return null; // Eller du kan kaste en exception her, afhængigt af dine præferencer
            }
        } catch (Exception $e) {
            // Log fejlen
            error_log("Fejl ved søgning efter salesperson email: " . $e->getMessage());
            // Returner null eller kast exceptionen videre, afhængigt af hvordan du vil håndtere fejl
            return null;
        }
    }


}
?>