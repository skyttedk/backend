<?php

$GLOBALS_PATH = \GFConfig::BACKEND_URL."units/valgshop/";
$sysU =  \router::$systemUser == null ? 0 : \router::$systemUser->id;
$shopID = $_GET["shopID"];
$saleperson = $_GET["saleperson"];

$admin = isset($_GET["saleperson"]) ? 1:0;
$localisation = $_GET["localisation"] ?? 1;

/*
 if(

    $_SERVER['REMOTE_ADDR'] == "83.90.172.100" ||
    $_SERVER['REMOTE_ADDR'] == "80.208.0.34" ||
    $_SERVER['REMOTE_ADDR'] == "194.31.54.58" ||
    $sysU == "631" ){

} else { die("System er ved at blive opdateret, lukker op kl. 7, mandag"); };
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>jQuery Right Panel Navigation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $GLOBALS_PATH ?>main/css/orderdata.css?<?php echo rand(1,9999); ?>"  rel="stylesheet"  />
<style>
    #navLinks a {
        font-size: 0.8rem;
    }
    #main-container{
        font-size: 12px  !important;
    }

    #orderOpenCloseChopEventInfo .orderOpenCloseChopAlert {
        margin-top: 10px;
    }
    .orderOpenCloseChopEventRed .ui-state-default {
        background-color: red !important;
        color: white !important;
    }
    .orderOpenCloseChopEventYellow .ui-state-default {
        background-color: yellow !important;
        color: black !important;
    }
    .orderOpenCloseChopEventGreen .ui-state-default {
        background-color: green !important;
        color: white !important;
    }
    .orderOpenCloseChopAlertRed {
        background-color: red !important;
        color: white !important;
    }
    .orderOpenCloseChopAlertYellow {
        background-color: #ffdd57 !important;
        color: black !important;
    }
    .orderOpenCloseChopAlertGreen {
        background-color: green !important;
        color: white !important;
    }
    .orderOpenCloseChopTooltipText {
        display: block;
        font-size: 0.8em;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
   .ui-state-highlight {
        background-color: blue !important; /* Ændrer farven for dagens dato */
        color: white !important;
    }
    #orderDataForm .description {

        margin-bottom: 10px;
        padding: 10px;
        border: 1px solid #ccc; /* Lysegrå ramme */
        border-radius: 5px; /* Afrundede hjørner */
        background-color: #f9f9f9; /* Lys baggrund */
    }

    #orderDataForm .dot {
        height: 10px;
        width: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    #orderDataForm .green {
        background-color: green;
    }

    #orderDataForm .yellow {
        background-color: #ffdd57 ;
    }

    #orderDataForm .red {
        background-color: red;
    }
    #orderDataForm .gray {
        background-color: #454545;
    }
    #orderDataForm p{
        margin-bottom:2px;
    }


    /*  style godkendelses skema */

.newsuggestion{
    width: 50px;
}
</style>

</head>
<body>
<div class="container-fluid" id="main-container">
    <div class="row">
        <div class="col-md-9 order-md-2">
            <!-- Main content area -->
            <div id="mainContent">

                <div class="vgshop-contentSection" id="vgshop_section1Content" style="display:none;">


                </div>
                <div class="vgshop-contentSection" id="vgshop_section2Content" style="display:none;">

                </div>
                <div class="vgshop-contentSection" id="vgshop_section4Content" style="display:none;">
                </div>
                <div class="vgshop-contentSection" id="vgshop_section3Content" style="display:none;">
                    <div>Antal medarbejdere: <input id="ReservationSoldPresent" type="int">
                        <button id="ReservationSoldPresentBtn">Opdaterer antal</button>
                    </div>
                    <div>Lagerlokation: "HEDEHUSENE" </div>
                    <br>
                    <button id="approvalFinalBtn" type="button" class="btn btn-success">Godkend og send reservationerne til Navision</button>
                    <div class="d-flex justify-content-between align-items-center">

                        <h4 id="resevation-headline">Reservation Godkendelse</h4>

                    </div>
                    <div style="width: 100%" id="vgshop_approval"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 order-md-1" id="navLinksPanel">
            <!-- Right panel with navigation -->
            <div class="list-group" id="navLinks">
                <a href="#" id="nav-vs-kundedata"  class="list-group-item list-group-item-action active" data-section="vgshop_section1Content">Ordredata</a>
                <a href="#" id="nav-vs-orderconf"  class="list-group-item list-group-item-action" data-section="vgshop_section4Content">Tjek ordrebekræftelse</a>
                <a href="#" id="nav-vs-none" class="list-group-item list-group-item-action" data-section="vgshop_section2Content"> -- </a>
                <a href="#" id="nav-vs-approval"  class="list-group-item list-group-item-action" data-section="vgshop_section3Content">Godkendelse</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="module" src="<?php echo $GLOBALS_PATH ?>approval/js/main.js?<?php echo rand(1,9999); ?>"></script>
<script type="module">
    import ApprovalMain from '<?php echo $GLOBALS_PATH ?>approval/js/main.js?<?php echo rand(1,9999); ?>';
    import Orderdata from '<?php echo $GLOBALS_PATH ?>main/js/orderdata.js?<?php echo rand(1,9999); ?>';
    var approval,orderdata;
    var shop = <?php echo $shopID; ?>;
    var saleperson = <?php echo $saleperson; ?>;
    var admin = <?php echo $admin; ?>;
    var _localisation = <?php echo $localisation; ?>;


    // jQuery code for handling navigation clicks and changing content
    $(document).ready(function() {

        $("#navLinks .list-group-item").click(function(event) {
            event.preventDefault();
            if( $(this).attr("id") == "nav-vs-approval"){
                runApproval();
            }
            if( $(this).attr("id") == "nav-vs-kundedata"){
                runOrderData();
            }

            if( $(this).attr("id") == "nav-vs-orderconf"){
                $('#vgshop_section4Content').html('<iframe style="width: 100%; height: 600px; border: none;" src="index.php?rt=unit/valgshop/orderconf/html/'+shop+'"></iframe>');
            }

            // Remove active class from all links and add it to the clicked link
            $("#navLinks .list-group-item").removeClass("active");
            $(this).addClass("active");

            // Get the data-section attribute to identify the section to display
            const sectionId = $(this).attr("data-section");

            // Hide all content sections
            $(".vgshop-contentSection").hide();

            // Show the selected content section
            $("#" + sectionId).show();
        });
        if(saleperson == 1){
            $( "#nav-vs-kundedata" ).click ();
        }
        if(saleperson == 2){
            $( "#nav-vs-approval" ).click ();
        }
        
    });
    function runApproval(){
        let showMode = saleperson == 2 ? 1:0;
        approval = new ApprovalMain;
        approval.init(shop,showMode,0);
        // status på alle udfyldt styrker
        // status om antal er sat

        // Opbyg vareliste
        // hent reservationsliste data
        // vis/gem
    }
    function runOrderData()
    {
        let showMode = saleperson == 1 ? 1:0;
        orderdata = new Orderdata;

        orderdata.init(shop,showMode,admin,_localisation);
    }
</script>

</body>
</html>
