<?php

require_once "/var/www/backend/public_html/gavefabrikken_backend/includes/config.php";

include(GFConfig::BACKEND_PATH."model/dbsqli.class.php");

if(isset($_GET["token"]) && isset($_GET["user"]) && isset($_GET["id"])   )
{
    $token = $_GET["token"];
    $pos = strpos($token, "-");
    if ($pos === false) {
        header("Location: ".GFConfig::BACKEND_URL."app/qrscanner/index/login.php");
        die();
    } else {
        $sql = "select * from app_users where token = '".$token."' and shop_id = ".intval($_GET["id"])." and  id = ".intval($_GET["user"]);
        $rs = Dbsqli::getSql2($sql );
        if(sizeofgf($rs) == 0) {
            header("Location: ".GFConfig::BACKEND_URL."app/qrscanner/index/login.php");
            die();
        }
    }
} else {
    die("<h1>Access denied</h1>");
}
?>

<html>
<head>
    <title>Qrscanner</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />


    <style>
        .approve{
            font-size: 1.3em;
        }
        #qr-reader{
            margin-top:-30px;
        }
        #action{
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50px;
            background-color: #D6D6D6;
        }
        #action button{
            font-size: 1.3em;

        }
        #qr-reader-results{
            margin-bottom: 50px;
        }
        input, select { font-size: 100%; }


        td,  th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        tr:nth-child(even){background-color: #f2f2f2;}



        th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #04AA6D;
            color: white;
        }
        #status{
            position:fixed;
            top:0px;
            left: 0;
            right: 0;
            color:red;
            z-index: 9999;
        }
        #camp{
            position: absolute;
            top:0px;
            right: 0;
        }
        .modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
        }
        .modal-content {
            max-width: 400px;
            margin: 0 auto;
        }
        @media (min-width: 576px) {
            .modal-dialog-centered {
                min-height: calc(100% - 3.5rem);
            }
        }
    </style>
    <link rel="stylesheet" href="./css/main.css">
</head>
<body>

<center>
    <img id="camp" src="<?php echo GFConfig::BACKEND_URL; ?>views/media/icon/Integrated Webcam-48.png" width=50 alt="" />
    <div id="autoConfirmContainer">
        <label class="switch">
            <input  id="autoConfirm" type="checkbox" >
            <span class="slider round"></span>
        </label>
    </div>
    <div id="status"></div>
    <div id="qr-sog-res" style="display:none;">
        <br>
        <div><input id="soginfoTxt" type="text" value="" /><button  class="soginfo button button1">SØG</button></div>
        <div id="soginfoContainer"></div>

    </div>

    <div id="action" ><button class=" logout button button3">Log ud</button><button class=" sog button button2">Søg</button><button class="oversigt button button1">Scan</button><button class=" button button5 onoff">Pause</button></div>
    <div id="scanner">
        <div id="qr-reader" style="width:80%"></div>
        <button class="reject button button3">Registrerer ikke</button><button  class="approve button button1">Godkend udlevering <br> og gem noten</button> <button class="newScan button button1" style="display:none">Scan ny gave</button><button id="undo" class="button button3" style="display:none;">Undo Lev</button>
        <div class="qr-specialmsg" style="display:none; color:red; padding:3px; border:1px solid red;margin:10px;"></div>
        <div id="qr-reader-results"><b>SCAN NY KVITTERING</b></div>

    </div>
</center>
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                <!-- Modal content will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmModalYes">Udfør</button>
            </div>
        </div>
    </div>
</div>
</body>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="<?php echo GFConfig::BACKEND_URL; ?>app/qrscanner/index/js/html5-qrcode.min.js"></script>
<script>

    var state = 0;
    var app_username = <? echo $_GET["user"]; ?> ;
    var company_id   =  0;
    var shop_id      = <? echo $_GET["id"]; ?> ;
    var token      = '<? echo $_GET["token"]; ?>' ;
    var devicesID = '<? echo $_GET["camp"]; ?>' ;
    var deviceslength;
    var html5QrCode;
    var cameraId;
    var onoff = "on";
    /*
        var shopuser_id
        var order_id
        var log_description
    */

    var _orderID;

    function areCookiesEnabled() {
        try {
            document.cookie = "testcookie=1";
            var ret = document.cookie.indexOf("testcookie=") != -1;
            document.cookie = "testcookie=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";
            return ret;
        }
        catch (e) {
            return false;
        }
    }
    function setAutoConfirmStatus(value, hours) {
        try {
            if (areCookiesEnabled()) {
                setCookie('autoConfirm_' + shop_id + '_' + app_username, value, hours);
            } else {
                localStorage.setItem('autoConfirm_' + shop_id + '_' + app_username, value);
                // Gem udløbstidspunkt i localStorage
                if (hours) {
                    var expiry = new Date();
                    expiry.setTime(expiry.getTime() + (hours*60*60*1000));
                    localStorage.setItem('autoConfirm_' + shop_id + '_' + app_username + '_expiry', expiry.getTime());
                }
            }
        } catch (e) {
            console.log("Kunne ikke gemme auto-confirm status");
        }
    }

    function getAutoConfirmStatus() {
        try {
            if (areCookiesEnabled()) {
                return getCookie('autoConfirm_' + shop_id + '_' + app_username);
            } else {
                // Check udløbstidspunkt hvis det findes
                var expiry = localStorage.getItem('autoConfirm_' + shop_id + '_' + app_username + '_expiry');
                if (expiry && new Date().getTime() > expiry) {
                    localStorage.removeItem('autoConfirm_' + shop_id + '_' + app_username);
                    localStorage.removeItem('autoConfirm_' + shop_id + '_' + app_username + '_expiry');
                    return null;
                }
                return localStorage.getItem('autoConfirm_' + shop_id + '_' + app_username);
            }
        } catch (e) {
            return null;
        }
    }

    function deleteAutoConfirmStatus() {
        try {
            if (areCookiesEnabled()) {
                deleteCookie('autoConfirm_' + shop_id + '_' + app_username);
            }
            // Slet altid fra localStorage også
            localStorage.removeItem('autoConfirm_' + shop_id + '_' + app_username);
            localStorage.removeItem('autoConfirm_' + shop_id + '_' + app_username + '_expiry');
        } catch (e) {
            console.log("Kunne ikke slette auto-confirm status");
        }
    }
    function setCookie(name, value, hours) {
        var expires = "";
        if (hours) {
            var date = new Date();
            date.setTime(date.getTime() + (hours*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    function deleteCookie(name) {
        document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }


    function adjustModalPosition() {
        const viewportHeight = window.visualViewport.height;
        const windowHeight = window.innerHeight;

        $('.modal-dialog-centered').css({
            'transform': `translate(-50%, -${windowHeight/2}px)`,
            'top': '50%'
        });
    }
    window.visualViewport.addEventListener('resize', adjustModalPosition);
    window.visualViewport.addEventListener('scroll', adjustModalPosition);

    function updateNote(orderID)
    {
        $(".updateNote").text("Noten opdateres...");
        var postData = {
            orderID:orderID,
            token:token,
            note:$("#note").val()
        }

        $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/updateNote",postData, function( res ) {

            res = JSON.parse(res);
            $("#note").val(res.data)
            $(".updateNote").text("Opdater noten");
            let newText = res.data.replace(/\n/g, "<br />");
            $(".noteTop").html("<b>Note:</b> "+newText);
        })

    }


    function showAlertModal(message) {
        $('#confirmModalLabel').text('');
        $('#confirmModalBody').text(message);
        $('#confirmModalYes').hide();
        $('.modal-footer .btn-secondary').text('OK')
        $('#confirmModal').modal({
            backdrop: 'static',
            keyboard: false
        });
        $('#confirmModal').on('hidden.bs.modal', function (e) {
            resetModal();
        });
    }
    function resetModal() {
        $('#confirmModalLabel').text('');
        $('#confirmModalBody').text('');
        $('#confirmModalYes').show().text('Udfør');
        $('.modal-footer .btn-secondary').text('Cancel').removeClass('btn-primary').addClass('btn-secondary');
    }
    // Function to show a confirmation modal
    function showConfirmModal(message, callback) {
        $('#confirmModalLabel').text('');
        $('#confirmModalBody').text(message);
        $('#confirmModalYes').show().text('Udfør');
        $('.modal-footer .btn-secondary').text('Cancel').removeClass('btn-primary').addClass('btn-secondary');
        adjustModalPosition();
        $('#confirmModalYes').off('click').on('click', function() {
            $('#confirmModal').modal('hide');
            callback(true);
        });
        $('.modal-footer .btn-secondary').off('click').on('click', function() {
            callback(false);
        });
        $('#confirmModal').modal({
            backdrop: 'static',
            keyboard: false
        });
    }

    function docReady(fn) {

        var autoConfirmStatus = getAutoConfirmStatus();
        if (autoConfirmStatus === 'true') {
            $('#autoConfirm').prop('checked', true);
        }
        // see if DOM is already available
        if (document.readyState === "complete"
            || document.readyState === "interactive") {
            // call on next available tick
            setTimeout(fn, 1);
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    docReady(function () {
        $(".approve").hide();
        $(".qr-specialmsg").html("").hide();
        $(".approve").click(function(){
            approve()
            $('#undo').hide();
        })
        $("#autoConfirm").change(function(){
            if ($('#autoConfirm').is(":checked")) {
                setAutoConfirmStatus('true', 8);
                showAlertModal("Auto udlevering aktiveret");
            } else {
                showConfirmModal("Ønsker du at alslutte automatisk udlevering", function(result) {
                    if (!result) {
                        $('#autoConfirm').prop('checked', true);
                        setAutoConfirmStatus('true', 8);
                    } else {
                        deleteAutoConfirmStatus();
                    }
                });
            }
        });
        $(".newScan").click(function(){
            if( $('#autoConfirm').is(":checked") == true){
                $("#qr-reader-results").prepend("<b>Udlevering registeret</b> <br><b>SCAN NY KVITTERING</b><br>");

            } else {
                $("#qr-reader-results").html("<b>SCAN NY KVITTERING</b>");
                $(".qr-specialmsg").html("").hide();
            }


            $(".approve").hide();
            $(".reject").hide();
            $(".newScan").hide();
            $('#undo').hide();
            state = 0
        })

        $("#camp").click(function(){


            devicesID++

            if( devicesID >= deviceslength ){
                devicesID = 0;
            }
            alert("Camera: "+devicesID)
            window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>app/qrscanner/index/view.php?token="+token+"&id="+shop_id+"&user="+app_username+"&camp="+devicesID;
        })
        $(".testtest").click(function(){
            alert("sadfsa")
        })

        $(".sog").click(function(){
            $("#scanner").hide();
            $("#qr-sog-res").show();
        })
        $(".onoff").click(function(){

            if(onoff == "on"){
                html5QrCode.stop()
                onoff = "off";
                $(".onoff").hide();
            }
        })

        $(".logout").click(function(){
            showConfirmModal("Ønsker du at logge ud", function(result) {
                if (result) {
                    deleteAutoConfirmStatus();
                    window.location.replace("<?php echo GFConfig::BACKEND_URL; ?>app/qrscanner/index/login.php");
                }
            });
        });
        $(".oversigt").click(function(){

            if(onoff == "off"){
                Html5Qrcode.getCameras().then(devices => {
                    /**
                     * devices would be an array of objects of type:
                     * { id: "id", label: "label" }
                     */

                    // originalt 1
                    dostart(cameraId)
                    // .. use this to start scanning.

                }).catch(err => {
                    // handle err
                });

                onoff = "on";
                $(".onoff").show();
            }
            $("#scanner").show();
            $("#qr-sog-res").hide();
        })

        $(".reject").hide();
        $(".reject").click(function(){
            _orderID = "";
            $(".qr-specialmsg").html("").hide();
            $("#qr-reader-results").html("<b>SCAN NY KVITTERING</b>");
            $(".reject").hide();
            $(".approve").hide();
            $(".newScan").hide();
            $('#undo').hide();
            state = 0

        })

        $('#undo').click(function(){

            $(".qr-specialmsg").html("").hide();
            showConfirmModal("Godkend at gaven udleveringsstatus nulstilles", function(result) {
                if (result) {
                    undoReg();
                }
            });
        });
        $('.soginfo').click(function(){
            searchDispatcher()
        })

        updateDeleveryStatus();
        setInterval(function(){
            updateDeleveryStatus()
        }, 30000);

        var resultContainer = document.getElementById('qr-reader-results');
        var lastResult, countResults = 0;
        function onScanSuccess(qrCodeMessage) {
            LoadPresent(qrCodeMessage);
        }
        Html5Qrcode.getCameras().then(devices => {
            /**
             * devices would be an array of objects of type:
             * { id: "id", label: "label" }
             */
            deviceslength = devices.length;
            if (devices && devices.length) {
                cameraId = devices[devicesID].id;    // originalt 1
                dostart(cameraId)
                // .. use this to start scanning.
            }
        }).catch(err => {
            // handle err
        });


    });
    function updateDeleveryStatus(){
        let postData = {
            token:token,
            shop_id:shop_id
        }

        $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/getDeliveryStatus",postData, function( data ) {
            let res = JSON.parse(data);
            $("#status").html("<label>Udleverede "+res.data[0].antal+" </label> / <label>Total "+res.data[0].total+"</label>")
        })
    }


    function dostart(cameraId){
        $(".reject").hide();
        $(".approve").hide();
        $(".newScan").hide();
        $('#undo').hide();
        html5QrCode = new Html5Qrcode("qr-reader");
        html5QrCode.start(
            cameraId, // retreived in the previous step.
            {
                fps: 10,    // sets the framerate to 10 frame per second
                qrbox: 280  // 250 sets only 250 X 250 region of viewfinder to
                // scannable, rest shaded.
            },
            qrCodeMessage => {
                // do something when code is read. For example:
                registerDelevery(qrCodeMessage)
                // console.log(`QR Code detected: ${qrCodeMessage}`);
            },
            errorMessage => {
                // parse error, ideally ignore it. For example:
                // console.log(`QR Code no longer in front of camera.`);
            })
            .catch(err => {
                // Start failed, handle it. For example,
                //console.log(`Unable to start scanning, error: ${err}`);
            });
    }
    function undoReg(){

        state = 1;
        $('#undo').hide();
        $(".approve").show();
        $(".reject").show();
        $('#newScan').hide();
        $("#qr-reader-results").html("SYSTEMET ARBEJDER");


        var postData = {
            data:_orderID,
            app_username :app_username,
            shop_id   :shop_id
        }

        $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/undoReg",postData, function( data ) {
            $("#qr-reader-results").html(data);
            updateDeleveryStatus()
            $("#qr-reader-results").html("<b>SCAN NY KVITTERING</b>");
            $(".approve").hide();
            $(".reject").hide();
            $('#undo').hide();
            $(".newScan").hide();
            state = 0
            _orderID = "";

        });
    }
    function registerDelevery(qrCodeMessage){


        if(state == 0){
            if(_orderID == qrCodeMessage){
                return;
            }
            state = 1;
            $('#undo').hide();
            $('#newScan').hide();
            $(".approve").show();
            $(".reject").show();
            $("#qr-reader-results").html("SYSTEMET ARBEJDER");

            _orderID =  qrCodeMessage;
            var postData = {
                data:qrCodeMessage,
                token:token,
                shop_id:shop_id,
                app_username: app_username,
                token:token
            }

            $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/getOrder",postData, function( res ) {

                res = JSON.parse(res);

                if(res.status == -1){
                    $(".qr-specialmsg").html("VIGTIGT! denne kvittering stammer ikke fra din virksomhed!")
                    $("#qr-reader-results").html("");
                    $(".qr-specialmsg").show();
                    $('.reject').hide();
                    $('.approve').hide();
                    $('.newScan').show();
                    $('#undo').hide();
                    $('#note').hide();
                }
                if(res.status == -2){
                    $(".qr-specialmsg").html("VIGTIGT! Din virksomhed har ikke aktiveret gaveudlevering!")
                    $("#qr-reader-results").html("");
                    $(".qr-specialmsg").show();
                    $('.reject').hide();
                    $('.approve').hide();
                    $('.newScan').show();
                    $('#undo').hide();
                    $('#note').hide();
                }
                if(res.status == 0){
                    $("#qr-reader-results").html(res.html);
                }
                if(res.status == 1){
                    $("#qr-reader-results").html(res.html);
                    if( $('#autoConfirm').is(":checked") == true){
                        approve()

                    }
                    $('#undo').hide();
                }
                if(res.status == 2){

                    $(".qr-specialmsg").html("VIGTIGT! personen har foretaget et andet valg end det der står på kvitteringen. Det aktuelle valg kan du se nedenfor. Ved accept kan du trykke 'Godkend' ellers tryk 'Afvis' og henvend dig til din kontaktperson.")
                    $("#qr-reader-results").html(res.html);
                    $(".qr-specialmsg").show();
                    _orderID = "=="+res.orderNr
                }
                if(res.status == 3){
                    $("#qr-reader-results").html(res.html);
                    $('.reject').hide();
                    $('.approve').hide();
                    $('.newScan').show();
                    $('#undo').show();
                    $('#note').show();
                }
                if(res.status == 4){
                    $(".qr-specialmsg").html("VIGTIGT! personen har foretaget et andet valg end det der står på kvitteringen. Det aktuelle valg kan du se nedenfor og denne gave er den udleverede, du har mulighed for at undo udleveringen")
                    $("#qr-reader-results").html(res.html);
                    $('.reject').hide();
                    $('.approve').hide();
                    $('.newScan').show();
                    $('#undo').show();
                    $('#note').show();
                    $(".qr-specialmsg").show();
                    _orderID = "=="+res.orderNr
                }
                /*
               if(res.status == 1){
                    $(".approve").hide();
                      $(".reject").hide();
                      $('#newScan').hide();
                      setTimeout(function(){
                        state = 0
                      }, 1000)
                  $("#qr-reader-results").html("<h3>Kvitteringen blev ikke fundet</h3>");
               }
                  */
            });
        }

    }
    // ***** LOGIC ******

    function sogUndo(id){
        showConfirmModal("Godkend at gaven udleveringsstatus nulstilles", function(result) {
            if (result) {
                let note = $("#note_"+id).html();
                console.log(note)
                var postData = {
                    data:'=='+id,
                    app_username: app_username,
                    shop_id:shop_id,
                    note:note,
                }

                $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/undoReg",postData, function( data ) {
                    updateDeleveryStatus();
                    $(".soginfo").click();
                });
            }
        });
    }
    function sogApprove(id){
        let note =  $(".sogNote_"+id).val();
        var postData = {
            data:'=='+id,
            app_username: app_username,
            shop_id:shop_id,
            notedata:note
        }
        $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/reg",postData, function( res ) {
            updateDeleveryStatus()
            $(".soginfo").click();
        });
    }



    function approve(){


        $(".qr-specialmsg").html("").hide();
        let note = $('#note').val();
        $('#undo').hide();
        if( $('#autoConfirm').is(":checked") == false) {
            $("#qr-reader-results").html("SYSTEMET ARBEJDER");
        }
        var postData = {
            data:_orderID,
            app_username: app_username,
            shop_id:shop_id,
            notedata:note
        }


        $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/reg",postData, function( res ) {
            // $("#qr-reader-results").html(res);
            //   updateDeleveryStatus()
            if(res==1){
                if( $('#autoConfirm').is(":checked") == true){

                    $(".approve").hide();
                    $(".reject").hide();
                    state = 0
                    $( ".newScan" ).trigger( "click" );
                } else {
                    $("#qr-reader-results").html("<b>SCAN NY KVITTERING</b>");
                    $(".approve").hide();
                    $(".reject").hide();
                    state = 0
                }


            }
        });
    }
    function searchDispatcher(){
        let searchType = $("#soginfoTxt").val() //$("input[name='supersearchChoise']:checked").val();
        $("#soginfoContainer").html("Systemet arbejder");
        //         718929    6
        //          31102585  8

        if(searchType.indexOf("@") !== -1){
            if(searchType.length > 1){
                this.doEmailSearch()
            } else {
                $("#soginfoContainer").html("Du skal minimum benytte 2 karakterer");
            }
        } else if ( searchType.match(/^[0-9]+$/) ) {
            this.doInvoiceSearch();
        } else {
            if(searchType.length > 2){
                this.doNameSearch()
            } else {
                $("#soginfoContainer").html("Du skal minimum benytte 3 karakterer");
            }
        }

    }
    function doInvoiceSearch(){
        let postData = {
            token:token,
            shop_id:shop_id,
            app_username: app_username,
            data:$("#soginfoTxt").val(),
            token:token
        }

        $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/doInvoiceSearch",postData, function( res ) {
            $("#soginfoContainer").html(res);
        });
    }

    function doEmailSearch(){
        let postData = {
            token:token,
            shop_id:shop_id,
            app_username: app_username,
            data:$("#soginfoTxt").val(),
            token:token
        }
        $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/doEmailSearch",postData, function( res ) {
            $("#soginfoContainer").html(res);
        });
    }
    function doNameSearch(){
        let postData = {
            token:token,
            shop_id:shop_id,
            app_username: app_username,
            data:$("#soginfoTxt").val(),
            token:token
        }
        $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/doNameSearch",postData, function( res ) {
            $("#soginfoContainer").html(res);
        });
    }

</script>

</html>
