<?php
//ini_set('max_execution_time', 300); //300 seconds = 5 minutes
//ini_set('memory_limit','2048M');
if($_GET["token"] != "dsfhjdsafgkj324gks" && $_GET["token"] != "dsfhuweif4637f634" ){
  die("Ingen adgang");
  return;
}

include("sms/db/db.php");

$localisation = 1;

$db = new Dbsqli();
$db->setKeepOpen();
if($_GET["token"] == "dsfhuweif4637f634"){
    $sql = "select order_no,company_name,salesperson,shop_name,quantity,expire_date,is_email,certificate_value,created_datetime,contact_name,contact_email,contact_phone from company_order where shop_id in( select id from shop where localisation = 4 ) order by created_datetime DESC";
}
if($_GET["token"] == "dsfhjdsafgkj324gks"){
    $sql = "select order_no,company_name,salesperson,shop_name,quantity,expire_date,is_email,certificate_value,created_datetime,contact_name,contact_email,contact_phone from company_order order by created_datetime DESC";
}


$rs = $db->get($sql);


$html = "<table id='sale' border=1> <thead>
        <tr>
            <th>order_no</th>
            <th>company_name</th>
            <th>salesperson</th>
            <th>shop_name</th>
            <th>certificate_value</th>
            <th>quantity</th>
            <th>expire_date</th>
            <th>contact_name</th>
            <th>contact_email</th>
            <th>contact_phone</th>
            <th>is_email</th>
            <th>created_datetime</th>
        </tr>
    </thead><tbody>";
foreach($rs["data"] as $data){
    $html.="<tr><td>".$data["order_no"]."</td><td>".$data["company_name"]."</td><td>".$data["salesperson"]."</td><td>".$data["shop_name"]."</td><td>".$data["certificate_value"]."</td><td>".$data["quantity"]."</td><td>".$data["expire_date"]."</td><td>".$data["contact_name"]."</td><td>".$data["contact_email"]."</td><td>".$data["contact_phone"]."</td><td>".$data["is_email"]."</td><td>".$data["created_datetime"]."</td></tr>";
}
  $html.="</tbody></table>";




?>

<!DOCTYPE HTML>

<html>

<head>
  <title>Sale</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title>Portal</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />
        <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
        <script src="https//cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
</head>

<body>
 <div style="padding:20px;">
   <?php  echo $html; ?>
 </div>
<script type="text/javascript">
  $(document).ready( function () {
    $('#sale').DataTable({
      "paging": false,
       "order": [[ 8, "desc" ]]
    });
} );

</script>
</body>

</html>