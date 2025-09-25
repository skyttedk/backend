<?php

    $GLOBALS_PATH = \GFConfig::BACKEND_URL."units/cardshop/";
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
        <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />
        <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <link href="<?php echo $GLOBALS_PATH ?>main/css/main.css?<?php echo rand(1,9999); ?>" rel="stylesheet">
        <link href="<?php echo $GLOBALS_PATH ?>main/css/valgshop.css?<?php echo rand(1,9999); ?>" rel="stylesheet">
        <link href="<?php echo $GLOBALS_PATH ?>companylist/css/companylist.css?<?php echo rand(1,9999); ?>" rel="stylesheet">
        <link href="<?php echo $GLOBALS_PATH ?>companyform/css/company.base.css?<?php echo rand(5, 9999); ?>" rel="stylesheet">
        <link href="<?php echo $GLOBALS_PATH ?>companyform/css/company.form.css?<?php echo rand(5, 9999); ?>" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <style>
        #ModalFullscreen{
            z-index: 1990;
        }


    </style>

    </head>

    <body>
    <div class="toast-container">
      <div class="toast">
    <div class="toast-header">

    </div>
    <div class="toast-body">

    </div>
</div>
</div>
        <div class="cardshop-sidebar"></div>
        <div class="cardshop-main">
            <div class="cardshop-main-menu">
            <div id= "cardshop-tabs-action-stamdata"></div>
            <div id= "cardshop-tabs-action"></div>
            <span id="sysuserCounter" style="display:none;"></span>
            <button id="cardshop-supersearch-btn" type="button" class="btn btn-success">-S&oslash;g-</button>
            <img id="cardshop-replace-valgshopcard-btn" style="display:block; cursor: pointer" src="/gavefabrikken_backend/units/assets/icon/replace_valg_shop.png" width=30 alt="" title="Valgshop erstatningkort" />

            <button id="cardshop-show-approvelist-btn" type="button" class="btn btn-danger" style="display:none;">Fejl</button>

            <button id="cardshop-reminders-btn" type="button" class="btn btn-primary" style="font-size:12px !important; margin-top: 10px; margin-right: 10px;float: right;" title="Dine noter med dato"><i class="bi bi-calendar"></i> <span id="reminder-active-count"></span></button>
                
            <button id="cardshop-demoshop-btn" type="button" class="btn btn-info" style="display:block" title="Link til demo shops">DemoLink</button>

            <button id="cardshop-create-company-btn" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalFullscreen" title="Opret ny virksomshed">
                <i class="bi bi-plus-square"></i>

            </button>

            </div>
            <div class="cardshop-main-content">
            </div>
        </div>
        <div class="modal fade " id="ModalFullscreen" tabindex="-1" aria-labelledby="ModalFullscreenLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title h4" id="ModalFullscreenLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>

        <div class="cardshop-sidebar-right">
            <div class="cardshop-sidebar-right-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" style="margin:15px; " class="bi bi-x-lg cardshop-sidebar-right-close modal-title h5" viewBox="0 0 16 16">
          <path d="M1.293 1.293a1 1 0 0 1 1.414 0L8 6.586l5.293-5.293a1 1 0 1 1 1.414 1.414L9.414 8l5.293 5.293a1 1 0 0 1-1.414 1.414L8 9.414l-5.293 5.293a1 1 0 0 1-1.414-1.414L6.586 8 1.293 2.707a1 1 0 0 1 0-1.414z"/>
        </svg>
        <div class="cardshop-sidebar-right-title"></div>

            </div>
            <hr>
            <div class="cardshop-sidebar-right-content"></div>
        </div>

    </body>

    </html>
    <script type="text/javascript">
    var USERID = "<?php echo \router::$systemUser == null ? 0 : \router::$systemUser->id ?>";
    var SHOPID =" <?php echo $shopID; ?>";

    </script>



    <script type="module" src="<?php echo $GLOBALS_PATH ?>main/js/main.js?<?php echo rand(1,9999); ?>"></script>
   <!--    <script src="<?php echo $GLOBALS_PATH ?>main/js/companylist.class.js?<?php echo rand(1,9999); ?>"></script>  -->