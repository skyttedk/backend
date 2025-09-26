<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// https://system.gavefabrikken.dk/gavefabrikken_backend/component/warehouseOverview.php?token=dfskljas9pioy84r89ohfgiuedfgsdfgsdl54ktgFSETgs
if(!isset($_GET["token"]) || $_GET["token"] != "dfskljas9pioy84r89ohfgiuedfgsdfgsdl54ktgFSETgs"){
    die("Ingen adgang");
}

include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();


$sql = "SELECT 
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

?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />


    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
</head>
<body>
<table id="myTable" class="display">
    <thead>
        <tr>
            <th>Shop Name</th>
            <th>Lager kode</th>
            <th>Packaging Status</th>
            <th>Sale Noter</th>
            <th>Note Move Order</th>
            <th>Note from Warehouse to GF</th>
        </tr>
    </thead>
    <tbody>
        <!-- Data rækker vil blive indsat her -->
    </tbody>
</table>
</body>
</html>

<script>
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


