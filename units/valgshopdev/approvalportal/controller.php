<?php

namespace GFUnit\valgshop\approvalportal;


use GFBiz\units\UnitController;


class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {


    }
    public function getAllShops(){
        $state = $_POST["state"];
        $country = $_POST["country"];
        $stateSql = "`orderdata_approval` > 0";
        if($state != ""){
            $stateSql = "`orderdata_approval` =".$state;
        }
        $ShopData =  \SystemUser::find_by_sql("
            SELECT * , shop_metadata.salesperson_code FROM `shop` 
                inner join `shop_approval` on shop_approval.shop_id = shop.id 
                inner join shop_metadata on shop_metadata.shop_id = shop.id     
                     WHERE localisation = ".$country." and " .$stateSql);
        echo json_encode(array("status" => 1,"data"=>$ShopData));
    }
    public function updateState(){
        $shopID = $_POST["shopID"];
        $status = $_POST["status"];
        $comment = $_POST["comment"];
        $sql = "update shop_approval set orderdata_approval = ".$status.", orderdata_approval_note = '".$comment."' where shop_id= $shopID ";
        $res =  \Dbsqli::setSql2($sql);

        if($status == 3){
            $ShopMetadata = \ShopMetadata::find_by_shop_id($shopID);

            if($ShopMetadata->attributes["salesperson_code"] == ""){
                \response::error("The status has been updated. No email was sent because no salesperson code was set.");
                return;
            };
            $systemUser = \SystemUser::find_by_username($ShopMetadata->attributes["salesperson_code"]);
            if($systemUser->attributes["email"] == ""){
                \response::error("The status was updated. No email was sent as the user's email address couldn't be located.");
                return;
            };
            $shop = \Shop::find($shopID);
            $shopName = $shop->attributes["name"];
            $recipent_email = $systemUser->attributes["email"];
            $comment = nl2br($comment);
            $mailcontent = "<div>Shoppen ".$shopName." er blevet afvist i godkendelsen med følgende begrundelse:</div><<br><div><em>".$comment."</em></div><br>";

            $maildata = [];
            $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
            $maildata['recipent_email'] = $recipent_email;
            $maildata['subject']= "Godkendelse af orderbekræftigelse afvist";
            $maildata['body'] = $mailcontent;
            $maildata['mailserver_id'] = 4;
            $sendmail =  \MailQueue::createMailQueue($maildata);
        }
        \response::success(make_json("response", []));
    }

}



/*
 *
 * $sql = "SELECT
    JSON_ARRAYAGG(
        JSON_OBJECT(
            'shop_name', LOWER(shop.name),
            'packaging_status', warehouse_settings.packaging_status,
            'note_move_order', LOWER(warehouse_settings.note_move_order),
            'noter', LOWER(warehouse_settings.noter),
            'note_from_warehouse_to_gf', LOWER(warehouse_settings.note_from_warehouse_to_gf),
            'reservation_code', LOWER(shop.reservation_code)
        )
    ) AS json_result
FROM
    warehouse_settings
INNER JOIN
    shop ON shop.id = warehouse_settings.shop_id
ORDER BY
    warehouse_settings.packaging_status;
";
$rs =  $db->get($sql);
$tableJsonData = $rs["data"][0]["json_result"];
 */



/*
 *
 *
 *
 * <script>
    let data = <? echo $tableJsonData; ?>;

    $(document).ready( function () {
        $('#myTable').DataTable({
            data:data,
            responsive: true,
            paging: false,
            columns: [
                { data: 'shop_name' },
                { data: 'reservation_code' },
                {
                    data: 'packaging_status',
                    render: function(data, type, row) {
                        switch(data) {
                            case 0: return 'Ingen status sat';
                            case 1: return 'lister ikke klar';
                            case 3: return 'lister godkendt';
                            case 5: return 'Pakkeri igang';
                            case 6: return 'Pakkeri færdig';
                            default: return 'Ukendt status'; // Håndterer uventede værdier
                        }
                    }
                },
                { data: 'noter', defaultContent: "" },
                { data: 'note_move_order', defaultContent: "" },
                { data: 'note_from_warehouse_to_gf', defaultContent: "" }
            ]
        });
    } );
</script>



 *
 *
 *
 */