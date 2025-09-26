<?php

$GLOBALS_PATH = \GFConfig::BACKEND_URL."units/pim/";
$sysU =  \router::$systemUser == null ? 0 : \router::$systemUser->id;
/*
 if(

    $_SERVER['REMOTE_ADDR'] == "83.90.172.100" ||
    $_SERVER['REMOTE_ADDR'] == "80.208.0.34" ||
    $_SERVER['REMOTE_ADDR'] == "194.31.54.58" ||
    $sysU == "631" ){

} else { die("System er ved at blive opdateret, lukker op kl. 7, mandag"); };
 */
$shopID = 0;
if(isset($_GET["shopid"])){
    $shopID = $_GET["shopid"];

}
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <link href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.min.css" rel="stylesheet" />
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity= "sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous">  </script>

    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <style>
        #ModalFullscreen{
            z-index: 1990;
        }
        td {
            vertical-align: top;
        }
        //
        label{margin-left: 20px;}
        #datepicker{width:180px;}
        #datepicker > span:hover{cursor: pointer;}
        .form-select{
            font-size: 14px;
        }
        table {
            font-size:16px;
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
            border-collapse: collapse;

        }
        td,  th {
            text-align: left;
            padding: 8px;
        }
         th {
            padding-top: 11px;
            padding-bottom: 11px;
            background-color: #04AA6D;
            color: white;
        }


        table,  th, td {
            border: 1px solid;
            border: 1px solid black;

        }

        table {
            width: 80%;
            max-width: 1000px;
            border: 1px solid;

            border-collapse: collapse;
        }
        #top-panel{
            position: absolute;
            top:0px;
            padding: 5px;
            font-size: 14px;
            text-align: center;
            background-color: #989898;
            color: white;
            border: solid 1px #666;
            border-radius: 3px;
            height:100vh;
            width: 100%;
            display: none;
        }

        #top-panel-header {
            height: 50px;

        }
    </style>

</head>

<body>

<!-- Modal -->
<div class="sync-main-container">

</div>
<center>
    <br><br>
<div class="sync-main-container-out">

</div>
</center>

<div id="top-panel">
    <div id="top-panel-header">
        <button onclick="closeTopPanel()" class="btn btn-primary" style="float: right;padding: 10px;">LUK</button>
    </div>
    <hr>
    <div id="top-panel-content">


    </div>


</div>
</body>

</html>

<script type="text/javascript">
    var USERID = "<?php echo \router::$systemUser == null ? 0 : \router::$systemUser->id ?>";
    var SHOPID =" <?php echo $shopID; ?>";

function closeTopPanel(){
    $("#top-panel").slideUp(300);
}
</script>

<script type="module" src="<?php echo $GLOBALS_PATH ?>sync/js/main.js?<?php echo rand(1,9999); ?>"></script>


