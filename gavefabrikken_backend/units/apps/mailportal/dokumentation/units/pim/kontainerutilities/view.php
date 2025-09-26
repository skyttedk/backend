<?php
$GLOBALS_PATH = \GFConfig::BACKEND_URL."unit/pim/kontainerutilities/";
$sysU =  \router::$systemUser == null ? 0 : \router::$systemUser->id;
$lang = 0;

if (isset($_SESSION['lang'])) {
 // $lang = $_SESSION['lang'];
}


?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <link href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.min.css" rel="stylesheet" />
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity= "sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous">  </script>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <style>
        iframe {
            width: 100%;
            height: calc(100vh - 230px); /* 100% of the viewport height */
        }

        table.nav-price-list {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #000; /* Single-line border */
        }

        /* Target the table cells within the table with the class nav-price-list */
        table.nav-price-list th,
        table.nav-price-list td {
            padding: 8px; /* Add space inside cells */
            text-align: left;
            border: 1px solid #000; /* Single-line border */
        }

        /* Alternate row background color */
        table.nav-price-list tr:nth-child(even) {
            background-color: #f2f2f2;
        }

    </style>

</head>

<body>
<br>
<h2  style="margin-left: 20px;" id="title-current-itemno"></h2>
<hr>
<div class="container mt-3 pt-3">
    <div class="row">
        <div class="col-12">
            <!-- Opretter tre faner -->
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab">Preview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="tab2-tab" data-toggle="tab" href="#tab2" role="tab">Actions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab">Sync-log</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab1" role="tabpanel">


                </div>
                <div class="tab-pane fade" id="tab2" role="tabpanel">
                    <div class="container mt-3 pt-3">
                        <div class="row">
                            <div class="col-12  justify-content-start">
                                <!-- Definerer to Bootstrap knapper -->
                                <button type=" button"  class=" btn btn-warning mr-2 closeBrowser">Tryk for at lukke browser-fan</button><br><br>

                                <div class="copy-container ">
                                    <button type="button" id="ku-copy" class="btn btn-primary mr-2"></button>
                                </div>
                                <div class="sync-container ">
                                    <button type="button"  id="ku-sync" class="btn btn-secondary  mt-3">Synkronisere varen</button>
                                </div>
                                <hr>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="sync-magento ">
                                            <button type="button"  id="magento-sync" class="btn btn-secondary  mt-3">Magento Sync</button>
                                        </div><br>
                                        <div>
                                            <input id="magento-sync-online" type="checkbox" /> <label> Online</label><br>
                                            <input id="magento-sync-price" type="checkbox" /> <label> Med priser</label><br>
                                        </div>
                                    </div>
                                    <div class="col-6 magenta-price"></div>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
                <div class="tab-pane fade" id="tab3" role="tabpanel">


                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>

<script type="text/javascript">
    var USERID = "<?php echo \router::$systemUser == null ? 0 : \router::$systemUser->id ?>";

    var PIMID ="<?php echo $_GET["pimid"]; ?>";
    var ITEMNO ="<?php echo $_GET["sku"]; ?>";
    var lang = "<?php echo $lang; ?>";
    function iframeRefresh(lang){
        let url = "<br><button type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='window.close()'>Tryk for at lukke browser-fan</button><button style='float: right' type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='iframeRefresh(0)'>Genindlæs</button> <button style='float: right' type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='iframeRefresh(2)'>ENG</button> <button style='float: right' type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='iframeRefresh(5)'>SE</button> <button style='float: right' type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='iframeRefresh(4)'>NO</button>  <button style='float: right' type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='iframeRefresh(1)'>DK</button><iframe src='https://presentation.gavefabrikken.dk/presentation2024/?user=PmTCeKTiRSauXymSB83RNutsUw5h2i&singleShow=go&pimid="+PIMID+"&lang="+lang+"' ></iframe>";
        $("#tab1").html(url);
    }
    iframeRefresh(lang)
</script>

<script type="module" src="/gavefabrikken_backend/units/pim/kontainerutilities/js/main.js?<?php echo rand(1,9999); ?>"></script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
