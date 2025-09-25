<?php

$GLOBALS_PATH = \GFConfig::BACKEND_URL."units/cardshop/";
$sysU = \router::$systemUser == null ? 0 : \router::$systemUser->id;

?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Present Complaints Overview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    
    <!-- Load required CSS -->
    <link href="<?php echo $GLOBALS_PATH ?>main/css/main.css?<?php echo rand(1,9999); ?>" rel="stylesheet">
    <link href="<?php echo $GLOBALS_PATH ?>presentcomplaint/css/present-complaint.css?<?php echo rand(5, 9999); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, sans-serif;
            background: #F7F7F7;
            margin: 0;
            padding: 0;
        }
        
        .present-complaint-container {
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }
        
        .cardshop-sidebar {
            width: 100%;
            height: 100%;
        }
        
        #ModalFullscreen{
            z-index: 1990;
        }
    </style>

</head>

<body>
    <div class="toast-container">
        <div class="toast">
            <div class="toast-header"></div>
            <div class="toast-body"></div>
        </div>
    </div>

    <div class="present-complaint-container">
        <div class="cardshop-sidebar"></div>
    </div>

    <!-- Modal for complaint details -->
    <div class="modal fade" id="ModalFullscreen" tabindex="-1" aria-labelledby="ModalFullscreenLabel" aria-hidden="true" style="display: none;">
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

</body>

</html>

<script type="text/javascript">
    var USERID = "<?php echo \router::$systemUser == null ? 0 : \router::$systemUser->id ?>";
    var BASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/";
    var JSBASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/";
    window.LANGUAGE = "";
    window.VERSION = "1.1.1";
    window.USERID = USERID;
</script>

<script type="module">
    import PresentComplaint from '<?php echo $GLOBALS_PATH ?>presentcomplaint/js/present-complaint.class.js?v=<?php echo rand(1,9999); ?>';
    import Ajax from '<?php echo $GLOBALS_PATH ?>main/js/ajax.js?v=<?php echo rand(1,9999); ?>';
    
    // Get user language
    async function getLanguage() {
        try {
            const response = await fetch(BASEURL + "cardshop/main/getLanguage", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + USERID
            });
            const data = await response.json();
            window.LANGUAGE = data.data.systemuser[0].language;
            
            // Initialize Present Complaint module
            new PresentComplaint(window.LANGUAGE);
        } catch (error) {
            console.error('Error getting language:', error);
            // Fallback to default language
            window.LANGUAGE = 1;
            new PresentComplaint(window.LANGUAGE);
        }
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        getLanguage();
    });
</script>