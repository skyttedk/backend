<?php

    $GLOBALS_PATH = \GFConfig::BACKEND_URL."units/cardshop/";
    $sysU =  \router::$systemUser == null ? 0 : \router::$systemUser->id;
    /*
     if(
        $_SERVER['REMOTE_ADDR'] == "83.90.172.100" ||
        $_SERVER['REMOTE_ADDR'] == "80.208.0.34" ||
        $_SERVER['REMOTE_ADDR'] == "194.31.54.58" ||
        $sysU == "63" ){

    } else { die("Under opdatering"); };
    */

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
        <link href="http://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />
        <script src="http://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
        <script src="http://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <link href="<?php echo $GLOBALS_PATH ?>main/css/main.css?<?php echo rand(1,9999); ?>" rel="stylesheet">
        <link href="<?php echo $GLOBALS_PATH ?>companylist/css/companylist.css?<?php echo rand(1,9999); ?>" rel="stylesheet">
        <link href="<?php echo $GLOBALS_PATH ?>companyform/css/company.base.css?<?php echo rand(5, 9999); ?>" rel="stylesheet">
        <link href="<?php echo $GLOBALS_PATH ?>companyform/css/company.form.css?<?php echo rand(5, 9999); ?>" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    </head>

    <body>
        <div id="main"></div>

    </body>

    </html>
    <script type="text/javascript">
    var USERID = "<?php echo \router::$systemUser == null ? 0 : \router::$systemUser->id ?>";

    </script>
    <script type="module" src="<?php echo $GLOBALS_PATH ?>approvelist/js/approvelist.class.js?<?php echo rand(1,9999); ?>"></script>



