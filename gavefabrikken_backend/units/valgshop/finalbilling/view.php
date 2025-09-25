<?php

$GLOBALS_PATH = \GFConfig::BACKEND_URL."units/valgshop/";
$sysU =  \router::$systemUser == null ? 0 : \router::$systemUser->id;
$shopID = $_GET["shopID"];
$localisation = $_GET["localisation"] ?? 1;

?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalbilling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo $GLOBALS_PATH ?>finalbilling/css/finalbilling.css?<?php echo rand(1,9999); ?>"  rel="stylesheet"  />
<style>
    #main-container{
        font-size: 12px  !important;
    }
    .finalbilling-contentSection {
        display: block;
    }
</style>

</head>
<body>
<div class="container-fluid" id="main-container">
    <!-- Main content area - full width -->
    <div id="mainContent">
        <div class="finalbilling-contentSection" id="finalbilling_section1Content">
            <!-- Oversigt over shops -->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module">
    import TpFinalbilling from '<?php echo $GLOBALS_PATH ?>finalbilling/tp/finalbilling.tp.js?<?php echo rand(1,9999); ?>';
    import Finalbilling from '<?php echo $GLOBALS_PATH ?>finalbilling/js/finalbilling.js?<?php echo rand(1,9999); ?>';
    
    // Make TpFinalbilling available globally
    window.TpFinalbilling = TpFinalbilling;
    
    var finalbilling;
    var shop = <?php echo $shopID; ?>;
    var _localisation = <?php echo $localisation; ?>;

    // Start direkte med oversigt
    $(document).ready(function() {
        runFinalbillingOversigt();
    });

    function runFinalbillingOversigt(){
        finalbilling = new Finalbilling();
        finalbilling.init(shop, _localisation, 'oversigt');
    }

    // Kun oversigt bruges nu
</script>

</body>
</html>