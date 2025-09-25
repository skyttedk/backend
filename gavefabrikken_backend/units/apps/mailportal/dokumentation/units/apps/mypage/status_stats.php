<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget dashboard</title>
    <link href="<?php echo $assetPath; ?>/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/assets/jquery.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/popper.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>/assets/fontawesome.css">
    <style>
        body{
            font-size: 12px;
        }
    </style>


</head>
<body>
<div class="container my-4">
    <button id="downloadBtn" class="btn btn-primary mb-3">Download detaljeret liste</button>
</div>
<div id="content">Henter Data</div>

<script>
    var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/mypage/";
    $(document).ready(function() {
        run();
        $("#downloadBtn").click(function() {
            startDownload();
        });
    });

    async function run() {
        try {
            let data = await loadData();
            buildGUI(data.data);
        } catch (error) {
            console.error("Error loading stats:", error);
        }
    }
    function startDownload() {
        window.location.href = APPR_AJAX_URL +'shopStatusStatsDetail';

    }
    function buildGUI(data) {


        let tbody = "";
        let total = 0;
        data.forEach(function(item) {
            let land = getCountryName(item.attributes.localisation);
            let status = getStatusName(item.attributes.shop_mode);
            total+=item.attributes.antal;
            tbody += `<tr>
                <td>${land}</td>
                <td>${status}</td>
                <td>${item.attributes.antal}</td>
            </tr>`;
        });
        tbody += `<tr>
                <td></td>
                <td>Total antal shops</td>
                <td>${total}</td>
            </tr>`;
        let html =
            `    <div class="container my-4">

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                            <tr>
                                <th>Land</th>
                                <th>Shop Status</th>
                                <th>Antal</th>
                            </tr>
                            </thead>
                         <tbody>${tbody}
                        </tbody>
                    </table>
                </div>
                </div>`;
        $("#content").html(html);

    }

    function loadData() {
        return new Promise((resolve, reject) => {
            let postData = {};
            $.post(APPR_AJAX_URL + "shopStatusStats", postData, function(res) {
                if (res) {
                    resolve(res); // Returner data korrekt
                } else {
                    reject("No data received");
                }
            }, "json").fail(function(jqXHR, textStatus, errorThrown) {
                reject(errorThrown);
            });
        });
    }
    function getCountryName(id){
        switch(id) {
            case 1:
                return "DK";
            case 4:
                return "NO";
            case 5:
                return "SV";
            default:
                return "Invalid";
        }
    }
    function getStatusName(id){
        switch(id) {
            case 1:
                return "Solgt – valgshop";
            case 2:
                return "Valgshop oplæg";
            case 3:
                return "Tabt";
            case 4:
                return "Papirvalg oplæg";
            case 5:
                return "Andet oplæg";
            case 6:
                return "Solgt - papirvalg";
            default:
                return "Invalid";
        }
    }


</script>
</body>
</html>



