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
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <style>
        #ModalFullscreen{
            z-index: 1990;
        }
        td {
            vertical-align: top;
        }
        .pim-budget{
            position: relative;
            top:35px;
            left: 200px;
            z-index: 999;
            width: 350px;

        }
        .dataTables_filter{
            margin-right: 20px;
        }
        //
    </style>

</head>

<body>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">VAREINFORMATION:</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-present-header"></div>

                <hr>
                <h1 class="modal-title fs-5">BESKRIVELSER:</h1><br>
                <ul class="nav nav-tabs" id="myTab" role="tablist" >
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-discription-1" type="button" role="tab" aria-controls="home" aria-selected="true">Dansk</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-discription-2" type="button" role="tab" aria-controls="profile" aria-selected="false">Engelsk</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-discription-4" type="button" role="tab" aria-controls="contact" aria-selected="false">Norsk</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-discription-5" type="button" role="tab" aria-controls="contact" aria-selected="false">Svensk</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-discription-3" type="button" role="tab" aria-controls="contact" aria-selected="false">Tysk</button>
                    </li>
                </ul><br>
                <div class="tab-content" id="myTabContent" style="min-height: 300px;">
                    <div class="tab-pane fade show active" id="tab-pin-discription-1" role="tabpanel" aria-labelledby="home-tab">DK</div>
                    <div class="tab-pane fade" id="tab-pin-discription-2" role="tabpanel" aria-labelledby="profile-tab">ENG</div>
                    <div class="tab-pane fade" id="tab-pin-discription-4" role="tabpanel" aria-labelledby="contact-tab">NO</div>
                    <div class="tab-pane fade" id="tab-pin-discription-5" role="tabpanel" aria-labelledby="contact-tab">SE</div>
                    <div class="tab-pane fade" id="tab-pin-discription-3" role="tabpanel" aria-labelledby="contact-tab">DE</div>
                </div>

                <hr>
                <h1 class="modal-title fs-5">PRODUKT BILLEDER:</h1>
                <br>
            <div class="modal-present-img"></div><br>

            <hr>
                <h1 class="modal-title fs-5">MODELLER:</h1><br>
            <ul class="nav nav-tabs" id="myTab2" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-model-1" type="button" role="tab" aria-controls="home" aria-selected="true">Dansk</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-model-2" type="button" role="tab" aria-controls="profile" aria-selected="false">Engelsk</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-model-4" type="button" role="tab" aria-controls="contact" aria-selected="false">Norsk</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-model-5" type="button" role="tab" aria-controls="contact" aria-selected="false">Svensk</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#tab-pin-model-3" type="button" role="tab" aria-controls="contact" aria-selected="false">Tysk</button>
                </li>
            </ul><br>
            <div class="tab-content" id="myTabContent2" style="min-height: 200px;" >
                <div class="tab-pane fade tab-pin-model show  active" id="tab-pin-model-1" role="tabpanel" aria-labelledby="home-tab">DK</div>
                <div class="tab-pane tab-pin-model fade" id="tab-pin-model-2" role="tabpanel" aria-labelledby="profile-tab">ENG</div>
                <div class="tab-pane tab-pin-model fade" id="tab-pin-model-4" role="tabpanel" aria-labelledby="contact-tab">NO</div>
            <div class="tab-pane fade tab-pin-model" id="tab-pin-model-5" role="tabpanel" aria-labelledby="contact-tab">SE</div>
            <div class="tab-pane fade tab-pin-model" id="tab-pin-model-3" role="tabpanel" aria-labelledby="contact-tab">DE</div>
        </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

            </div>
        </div>
    </div>
</div>

<div class="pim-budget" style="font-size: 12px;">
    <fieldset>
        <legend style="font-size: 12px;">Kostpris:</legend>
    <table >
        <tr>
            <td>Fra</td>
            <td><input id="pim-budget-from" type="number" onClick="this.select();"  min="0" /></td>
            <td>Til</td>
            <td><input id="pim-budget-to" type="number" onClick="this.select();" min="0" /></td>
            <td><select name="pim-budget-budget" id="pim-budget-budget">
   

                </select></td>
            <td><button class='pim-budget-search' type=button>Søg</button></td>
            <td><button class='pim-budget-reset' type=button>Nulstil</button></td>
        </tr>

    </table>
    </fieldset>

</div>
<div class="pim-main-container">



</div>



</body>

</html>

<script type="text/javascript">
    var USERID = "<?php echo \router::$systemUser == null ? 0 : \router::$systemUser->id ?>";
    var SHOPID =" <?php echo $shopID; ?>";


</script>

<script type="module" src="<?php echo $GLOBALS_PATH ?>portal/js/main.js?<?php echo rand(1,9999); ?>"></script>


