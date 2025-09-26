<?php
$shopID = "";
if(isset($_GET["shopId"])){
    $shopID = $_GET["shopId"];
}

$is_gf_user = "0";
if(isset($_GET["is_gf_user"])){
    $is_gf_user = "1";
}

$token = "";
if(isset($_GET["token"])){
   $token = $_GET["token"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>


    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/js/paperPortal_show.js"></script>
    <script src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/js/paperPortalSettings.js"></script>
    <script src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/js/ptShopPaper.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            height: 100vh;
            margin: 0;

            align-items: center;
            justify-content: center;
        }
        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-floating {
            position: relative;
        }
        .form-floating > label {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            padding: 1rem 0.75rem;
            pointer-events: none;
            border: 1px solid transparent;
            transform-origin: 0 0;
            transition: opacity .1s ease-in-out, transform .1s ease-in-out, color .1s ease-in-out;
            color: rgba(108, 117, 125, 0.5);  /* Mere transparent farve for tom placeholder */
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            opacity: 1;
            transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
            color: #495057;  /* Mørkere farve for aktiv label */
        }
        .form-floating > .form-control:-webkit-autofill ~ label {
            opacity: 1;
            transform: scale(.85) translateY(-0.5rem) translateX(0.15rem);
        }
        .form-control::placeholder {
            color: transparent;  /* Gør den indbyggede placeholder usynlig */
        }


        .hent-filer.not-released {
            background-color: #FFCCCC;
        }
        .hent-filer.released {
            background-color: #98FB98;
        }
        .custom-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-custom {
            background-color: #0d6efd;
            border-color: #0d6efd;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }
        /* Custom styles for gray tabs */
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
        }

        .nav-tabs .nav-link {
            margin-bottom: -1px;
            background: none;
            border: 1px solid transparent;
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
            color: #495057;
            transition: all 0.2s ease-in-out;
        }

        .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #dee2e6;
            isolation: isolate;
            background-color: #f8f9fa;
        }

        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }

        /* Gray background for inactive tabs */
        .nav-tabs .nav-item .nav-link:not(.active) {
            background-color: #e9ecef;
        }

        /* Slightly darker gray for hover state */
        .nav-tabs .nav-item .nav-link:not(.active):hover {
            background-color: #dee2e6;
        }
        .newWorker, .newWorkerPresent{
            font-size: 12px !important;
        }
        .header {
            position: relative;
            background-color: #f8f9fa;
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 90px;
        }

        .logo {
            position: absolute;
            left: 20px;
            top:-10px;
            width: 170px;
            height: 100px;
            background-image: url('./views/images/y569bvkjxf1568123658.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;

        }
        .actionBtn{
            font-size: 11px !important;
        }
        #paper-settings{
            background-color:#E0E0E0  ;
          
        }
        #sum_fordeling_container {
            display: flex;
            align-items: center;
        }

        #downloadListCSV {
            margin-right: 1rem;
        }

        #sum_fordeling {
            font-weight: bold;
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="logo"></div>
            </div>
            <div class="col text-center">
                <h2 class="mb-0">GaveFabrikken A/S - Kundeportal</h2>
            </div>
        </div>
    </div>
</header>

<div id="paper-settings"></div>
<div style="margin-top: 20px;" id="paper-upload"></div>
<div id="paper-reg"></div>
<br><br>
<div id="login-form" class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; margin-top: -100px">
    <div class="login-container text-center">

        <h4 class="mb-4">LOGIN</h4>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <button  class="btn btn-primary" id="login">Login</button>

    </div>
</div>



<!-- Bootstrap Modal -->
<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Hent filer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Modal content will be dynamically inserted here -->
            </div>
        </div>
    </div>
</div>

<script>
    var _isGfUser = '<?php echo $is_gf_user; ?>';
    var _token = '<?php echo $token; ?>';
    var _shopID = '<?php echo $shopID; ?>';
    var _shopId = '<?php echo $shopID; ?>'; // pga ptShopPaper.js
    var _ptMakePaperPDF =  new ptMakePaperPDF();
    var _distributionType ="";
    var _companyID = {};
    var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";
    $(document).ready(function() {

        $('#login').on('click', function() {


            var username = $("#username").val();
            var password = $("#password").val();

            if(username === "" || password === "") {
                alert("Both fields are required!");
            } else {
                // Implement your AJAX login logic here
                $.post(BASE_AJAX_URL + "paperPortal/login", {username: username, password: password}, function(returnMsg) {
                  if(returnMsg.data.length == 0){
                        alert("Forkert login");
                    } else {
                        _token = returnMsg.data.token;
                        _shopID = returnMsg.data.shopID;
                        _shopId = returnMsg.data.shopID;
                        loadPortal();
                    }
                }, "json")
                    .fail(function() {
                        alert("alert_problem");
                    });
            }
        });

        if(_isGfUser == '1'){
            loadPortal(true);
        }

    });

    function readSettings() {
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL + "paperPortal/readSettings", {shopID: _shopID}, function (returnMsg, textStatus) {
                resolve(returnMsg);
            }, "json")

        });
    }
    function updateSettings(postData) {
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL + "paperPortal/updateSettings", postData, function (returnMsg, textStatus) {
                resolve(returnMsg);
            }, "json")

        });
    }

    async function loadPortal(isAdmin=false){
        let settings = await readSettings();
        let  paperSettings = {};
        $("#login-form").remove();

        if (!settings || !settings.data || typeof settings.data.paper_settings === 'undefined' || settings.data.paper_settings == '') {
            const paperSettings = {
                isEnabled: 0,
                isEditable: 0,
                entityType: 'list',
                isImported: 0,
                shopID: _shopID
            };
            await updateSettings(paperSettings);

        } else {
            paperSettings = JSON.parse(settings.data.paper_settings);
            _distributionType = paperSettings.entityType

        }
        let pp = new PaperPortal(isAdmin );
        pp.init(paperSettings);
        <?php if($is_gf_user == 1){ ?>
            let Settings = new PaperPortalSettings(isAdmin)
            Settings.init(paperSettings);
        <?php } ?>



    }
</script>
</body>
</html>