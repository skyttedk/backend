<?php


include("model/dbsqli.class.php");
Class shopItemsController Extends baseController {

    public function index() {}

    public function validate(){
        $userID = router::$systemUser->attributes["id"];

        $isSalePerson = Dbsqli::getSql2("SELECT * FROM `user_tab_permission` where  systemuser_id  = $userID  and tap_id = 1000");
        if(sizeof($isSalePerson) == 0) {
            response::success(json_encode(true));
            return;
        }

        $lang = 1;
        $shopID = $_POST['shop_id'];

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