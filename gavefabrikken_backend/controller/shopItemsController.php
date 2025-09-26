<?php


include("model/dbsqli.class.php");
Class shopItemsController Extends baseController {

    public function index() {}

public function validate11(){
    $userID = router::$systemUser->attributes["id"];

    // Sikkerhedstjek for salesperson
    $isSalePerson = Dbsqli::getSql2("SELECT * FROM `user_tab_permission` WHERE systemuser_id = $userID AND tap_id = 1000");
    if(sizeof($isSalePerson) == 0) {
        // Kommenteret ud som i din kode
        // response::success(json_encode(true));
        // return;
    }

    $lang = 1;
    $shopID = $_POST['shop_id'];
    //$this->syncItemsNo($shopID);

    // Behold SQL-forespørgslen fra din kode som henter både hovedprodukter og child-produkter
    $sql = "SELECT pm.* FROM `present_model` pm
            WHERE pm.`present_id` IN (
                SELECT p.id FROM present p
                LEFT JOIN shop_present sp ON sp.present_id = p.id
                WHERE (
                    (sp.shop_id = ".$shopID." AND sp.active = 1 AND sp.is_deleted = 0)
                    OR
                    (p.shop_id = ".$shopID.")
                    OR
                    (p.shop_id = ".($shopID*-1)." AND p.active = 1 AND p.deleted = 0)
                )
            )
            AND pm.language_id = ".$lang."
            AND pm.active = 0
            ORDER BY pm.present_id";

    $list = Dbsqli::getSql2($sql);
    $errorList = [];

    // Ændr logikken, så ALLE produkter tilføjes til fejllisten
    foreach ($list as $item) {
        // Tilføj alle elementer til fejllisten
        array_push($errorList, $item);
    }

    // Returner selve fejllisten i stedet for størrelsen
    // Hvis listen er tom, returner true
    $returnData = sizeof($errorList) > 0 ? $errorList : true;
    response::success(json_encode($returnData));
}

    public function validate(){
        $userID = router::$systemUser->attributes["id"];

        $isSalePerson = Dbsqli::getSql2("SELECT * FROM `user_tab_permission` where  systemuser_id  = $userID  and tap_id = 1000");
        if(sizeof($isSalePerson) == 0) {
            response::success(json_encode(true));
            return;
        }

        $lang = 1;
        $shopID = $_POST['shop_id'];
       // $this->syncItemsNo($shopID);
        $sql = "SELECT * FROM `present_model` WHERE `present_id` in (
        SELECT present_id   FROM `shop_present`  WHERE 
        `shop_id` = ".$shopID." AND
        active = 1 AND
        is_deleted = 0)
        and 
        language_id = ".$lang." and
        active = 0 order by present_id";
        $list = Dbsqli::getSql2($sql);
        $errorList = [];
        foreach ($list as $item) {
            if ($item["model_present_no"] == "" || $this->itemnrExist($item["model_present_no"]) == false){
                array_push($errorList,$item);
            }
        }
        $returnData =  sizeof($errorList) > 0 ? sizeof($errorList) : true;
        response::success(json_encode($returnData));
    }
    private function syncItemsNo($shopID){

        $sql = "SELECT * FROM `present_model` WHERE `present_id` in (SELECT present.id FROM `present` WHERE `shop_id` = ".$shopID.")  and `language_id` =1";
        $localPresents = Dbsqli::getSql2($sql);
        foreach ($localPresents as $localP){
            $masterPresent = Dbsqli::getSql2("SELECT * FROM `present_model` WHERE `model_id` = ".$localP["original_model_id"]." and `language_id` =1");
            if(sizeof($masterPresent)) {
                if ($masterPresent[0]["model_present_no"] != $localP["model_present_no"]) {
                    $sql = "update present_model set model_present_no = '" . $masterPresent[0]["model_present_no"] . "' where model_id =" . $localP["model_id"];
                    Dbsqli::setSql2($sql);
                }
            }
        }
        //response::success();
    }
    public function sendTaskMissingItemnr(){
        $shopID = $_POST['shop_id'];
        $shop = Dbsqli::getSql2("select name from shop where  id=".$shopID);


        
        $mailqueue = new MailQueue();
        $mailqueue->sender_name = "Gavefabrikken";
        $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
        $mailqueue->recipent_email = "indkob@gavefabrikken.dk";
        $mailqueue->mailserver_id = 1;
        $mailqueue->subject = 'Manglende varenr på oplæg - '.$shop[0]["name"];
        $mailqueue->body = "<html><head></head><body>";

        $mailqueue->body .= "<div><a href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=rapport/salepersonlist&shop_id=".$shopID."'>Hent shoppens vareliste</a> </div>";
        $mailqueue->body .= "</body></html>";
        $mailqueue->save();
        response::success(make_json("response", $mailqueue));

    }

    private function itemnrExist($itemnr)
    {
        $sql = "SELECT * FROM `navision_item` where no = '$itemnr' and deleted is null";
        if(sizeof(Dbsqli::getSql2($sql)) > 0) return true;
        return false;

    }


}