<?php
/*
vis nuværende reservation
vis nuværende valgte
vis andel der har valgt i shoppen
Aktivere foreslået handling
Husk at sætte nej nej nej opdatering knappen på


*/

echo $shopID =  $_GET["id"];
die("asdf");
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autopilot dashboard</title>
    <link href="<?php echo $assetPath; ?>/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/assets/jquery.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/popper.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>/assets/fontawesome.css">
    <style>
        body {
            padding: 20px;
        }
        #shopConpleteProcent {
            float: right;
            margin-right: 20px;
        }
        #save {
            display: none;
        }
        .container {
            max-width: 100%;
            padding: 0;
        }
        .table-container {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            width: 100%;
        }
        .table-container table {
            width: 100%;
            table-layout: fixed;
        }
        .table-container thead th {
            position: sticky;
            top: 0;
            background-color: #343a40;
            z-index: 1;
        }
        .table-container th,
        .table-container td {

            overflow: hidden;

        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4">Model Data</h1>
    <button id="autopilot" class="btn btn-primary mb-3">Autopilot</button> <button id="save"  class="btn btn-success mb-3">GEM</button>
    <label id="shopConpleteProcent"></label>
    <label id="stats"></label>

    <div class="table-container">
        <table class="table table-bordered">
            <thead class="thead-dark">
            <tr>
                <th>Present ID</th>
                <th>Model ID</th>
                <th>Model Present No</th>
                <th>Model Name</th>
                <th>Antal valgte</th>
                <th>Reserverede</th>
                <th>Forrecast</th>
                <th>Diff</th>
                <th>Autopilot</th>
                <th>Lagerstatus</th>
                <th>External</th>
                <th>Luk markeret</th>
                <th>LUKKET</th>
                <th>Beskyttet </th>
            </tr>
            </thead>
            <tbody id="data-table">
            <!-- Data will be appended here by jQuery -->
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    let shopID = '<?php echo $shopID; ?>';
    let _procentSelected = 0;
    let _totalOrders = 0;
    let _totalForrecast = 0;
    let _reserveret = 0;
    let _total = 0;
    let _newResavation = [];
    let _adapt = 0;
    let _initreserveret = 0;
    var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/autopilot/";
    $(document).ready(function() {
        $.ajax({
            url: APPR_AJAX_URL + 'getCurrentData',
            method: 'POST',
            dataType: 'json',
            data:{shop_id:shopID},
            success: function(data) {

                var rows = '';
                data.data.forEach(function(item) {
                    rows += '<tr>';
                    rows += '<td>' + item.present_id + '</td>';
                    rows += '<td>' + item.present_model_id + '</td>';
                    rows += '<td>' + item.model_present_no + '</td>';
                    rows += '<td>' + item.model_present_name + '</td>';
                    rows += '<td id="order-'+item.present_model_id+'">' + item.order_count + '</td>';
                    rows += '<td id="quantity-'+item.present_model_id+'" style="font-weight: bold;">' + item.reserved_quantity + '</td>';
                    rows += '<td id="modelID-'+item.present_model_id+'"></td>';
                    rows += '<td id="modelIDDif-'+item.present_model_id+'"></td>';
                    rows += '<td class="autopilot" id="autopilot-'+item.present_model_id+'" data-id="'+item.present_model_id+'" style="font-weight: bold;"></td>';
                    rows += '<td id="store-'+item.present_model_id+'" ></td>';
                    rows += '<td id="isExternal-'+item.present_model_id+'" ></td>';
                    rows += '<td >' + item.do_close + '</td>';
                    rows += '<td id="close-'+item.present_model_id+'" >' + item.present_is_active + '</td>';
                    rows += '<td id="protected-'+item.present_model_id+'">' + item.autotopilot + '</td>';
                    rows += '</tr>';
                    item.reserved_quantity = item.reserved_quantity == "" ? 0:item.reserved_quantity;
                    if(item.reserved_quantity != -1){
                        _initreserveret+= parseInt(item.reserved_quantity);
                    }
                });
                $('#data-table').html(rows);
            },
            error: function(err) {
                console.error('Error fetching data', err);
            }
        });
        $('#autopilot').on('click', function() {
            autopilot();
            $("#autopilot").text("Systemet arbejder");
        });
        $('#save').on('click', function() {
            let r = confirm("Opdatere reservationer!")
            if(!r) return;
            doSave()
        });
        shopCompleted();
    });
    function doSave(){

        $.ajax({
            url: APPR_AJAX_URL + 'updateReservationer',
            method: 'POST',
            data:{shop_id:shopID,data:_newResavation,adapt:_adapt},
            dataType: 'json',
            success: function(data) {
                alert("Autopilot udført");
                location.reload();

            },
            error: function(err) {
                console.error('Error fetching data', err);
            }
        });
    }
    function shopCompleted(){
        $.ajax({
            url: APPR_AJAX_URL + 'getShopCompleted',
            method: 'POST',
            data:{shop_id:shopID},
            dataType: 'json',
            success: function(data) {
                let countOrders = data.data[0].attributes.count;
                let countSU = data.data[1].attributes.count;
                let p = Math.round((countOrders / countSU) * 100,2);
             //   console.log(p +"-"+countSU+ "-"+countOrders)
                _procentSelected = p;
                _totalOrders = countOrders;
                _total = countSU
                $("#shopConpleteProcent").html(p+" % har valgt | Res: "+_initreserveret)
            },
            error: function(err) {
                console.error('Error fetching data', err);
            }
        });
    }

    function autopilot(){
        $.ajax({
            url: APPR_AJAX_URL + 'autopilot',
            method: 'POST',
            dataType: 'json',
            data:{shop_id:shopID,total_orders:_totalOrders,procent_selected:_procentSelected},
            success: function(data) {
                if(data.status == 0){
                    alert("der er opstået en fejl")
                    return;
                }

                $("#autopilot").text("Autopilot");
                updateAutopilot(data)

            },
            error: function(err) {
                $("#autopilot").text("Autopilot");
                console.error('Error fetching data', err);
            }
        });
    }
    function updateAutopilot(data)
    {
        /*
            action = 0: lukkes ophæves
            action = 1: lukkes
            action = 2: lukke markeres


         */
        data.data.forEach(function(item) {

            let forecast = item.forecast == "N/A" ? 0 : item.forecast;
            let quantity = parseInt($("#quantity-" + item.modelID).text());
            let orderCount = parseInt($("#order-" + item.modelID).text());
            let protectedAuto = parseInt($("#protected-" + item.modelID).text());
            let isClose = parseInt($("#close-" + item.modelID).text());
            forecast = parseInt(forecast);
            quantity = parseInt(quantity);
            orderCount = parseInt(orderCount);





            if(orderCount == 0 && item.isExternal == 0 ){
                forecast = zeroSelectedForecast();

            }


            $("#modelID-" + item.modelID).html(forecast);
            $("#store-" + item.modelID).html(item.stockavailable);
            $("#isExternal-" + item.modelID).html(item.isExternal);

            // Get the quantity value


            // Calculate the difference


            let difference = quantity - forecast;

            item["forecast"] = forecast;
            item["quantity"] = quantity;
            item["orderCount"] = orderCount;
            item["difference"] = difference;
            item["do_close"] = item.do_close;

                    // Update the modelIDDif element
            $("#modelIDDif-" + item.modelID).html(difference);
            if((orderCount >= quantity) && item.stockavailable <= 0 && item.isExternal == 0 && difference < 0 && isClose == "0" && protectedAuto == "0" ){
                $("#autopilot-" + item.modelID).html("lukkes");
                _reserveret+=quantity;
                _newResavation.push( {modelID: item.modelID,quantity : quantity,action:1} );
                return;
            }




            let responce = "Beskyttet";
            if(protectedAuto == "0" && quantity != -1 && isClose == "0" ) {
                responce = calcAuto(item)
            }

            $("#autopilot-" + item.modelID).html(responce);


        });
        let status = "Total gaver: "+_total+ " | total res: "+_reserveret+" | total forecast: "+_totalForrecast;
        $("#stats").html(status);
        $("#save").show();
    }
    function zeroSelectedForecast(){
        let zeroforecast = 0;
        if( _totalOrders < 500 ){
            if(_procentSelected > 20 ){
                zeroforecast = 5;
            }
            if(_procentSelected > 40 ){
                zeroforecast = 3;
            }
            if(_procentSelected > 50 ){
                zeroforecast = 2;
            }
        } else if(_totalOrders >= 500 && _totalOrders < 1000 ){
            if(_procentSelected > 15 ){
                zeroforecast = 7;
            }
            if(_procentSelected > 30 ){
                zeroforecast = 5;
            }
            if(_procentSelected > 50 ){
                zeroforecast = 3;
            }
        } else if(_totalOrders > 1000){
            if(_procentSelected > 10 ){
                zeroforecast = 10;
            }
            if(_procentSelected > 20 ){
                zeroforecast = 8;
            }
            if(_procentSelected > 50 ){
                zeroforecast = 5;
            }
        }
        return zeroforecast;
    }


    function calcAuto(data){
        // tjek hvis er extern eller sat til -1 i reserverede så er den også externe
        let forecastProcent = 1.3;
        let action = "";
         data["forecast"] = data["forecast"] == "N/A" ? 0 : data["forecast"];
         data["forecast"] = parseInt(data["forecast"]);

        _totalForrecast+=data["forecast"];
        if( _totalOrders < 500 ){
            if(_procentSelected > 20 ){
                forecastProcent = 1.2;
                _adapt = 1
            }
            if(_procentSelected > 40 ){
                forecastProcent = 1.1;
                _adapt = 2
            }
            if(_procentSelected > 50 ){
                forecastProcent = 1.05;
                _adapt = 3
            }

        } else if(_totalOrders >= 500 && _totalOrders < 1000 ){
            if(_procentSelected > 15 ){
                forecastProcent = 1.2;
                _adapt = 1
            }
            if(_procentSelected > 30 ){
                forecastProcent = 1.1;
                _adapt = 2
            }
            if(_procentSelected > 50 ){
                forecastProcent = 1.05;
                _adapt = 3
            }

        } else if(_totalOrders > 1000){
            if(_procentSelected > 10 ){
                forecastProcent = 1.2;
                _adapt = 1
            }
            if(_procentSelected > 20 ){
                forecastProcent = 1.1;
                _adapt = 2
            }
            if(_procentSelected > 50 ){
                forecastProcent = 1.05;
                _adapt = 3
            }

        }

        //let _procentSelected = 0;
        //let _totalOrders = 0;

        let responce = 0;
        if(data["isExternal"] == 1){
            return  "Beskyttet";
        }
        if(data["forecast"] == 0 && data["isExternal"] == 0){
            data["forecast"] = data["quantity"];
            _reserveret+=data["forecast"];
            _newResavation.push( {modelID: data["modelID"],quantity : data["forecast"],action:0} );
            return data["forecast"];
        }
        // hvis forrecast er større end antal reservede, der flere varer til, opskrivning af reservationer
        let realStockavailable = 0
        if(data["stockavailable"] > 0){
            let stockbuffer =  Math.ceil(data["stockavailable"]*0.05) > 5 ? Math.ceil(data["stockavailable"]*0.05) : 5;
            realStockavailable   = data["stockavailable"] - stockbuffer;
        }

        let minimumForecast =  zeroSelectedForecast();
        data["forecast"] =  data["forecast"] < minimumForecast ? minimumForecast : data["forecast"];


        if(data["difference"] <= 0){

            let difference = (data["difference"]*-1);
            // hvor mange varer kan der tages fra lager

            if(realStockavailable > 0){
                 totalAvailable =  realStockavailable - difference;

                if(data["modelID"] == "197171"){
                //    alert( totalAvailable)
                }

                // kan ikke få det ønskede antal forecast
                if(totalAvailable <= 0){
                    let gab = realStockavailable - Math.ceil(data["forecast"]);
                    console.log(data["modelID"])
                    if(gab < 0){
                        totalAvailable = Math.ceil(data["forecast"]-(gab*-1))
                    }
                    totalAvailable = totalAvailable < data["quantity"] ? data["quantity"] : totalAvailable ;
                    action = 2;
                } else {
                    let max = Math.ceil(data["forecast"] * forecastProcent)
                    totalAvailable = (difference  * forecastProcent)  > realStockavailable ? data["forecast"]:max;
                    totalAvailable = totalAvailable < data["quantity"] ? data["quantity"] : totalAvailable ;
                    action = 0;
                }
                responce =  totalAvailable;
            } else {
                // der var ikke nok på lager

                action = 2;
                responce = data["quantity"];

            }

        }
        // hvis forrecast er mindre end antal reservede, der kan frigivis varer
        if(data["difference"] > 0){
            if(data["do_close"] == 1 ){

                if(_procentSelected > 50 ){
                    action = 0;
                    responce = data["forecast"];
                } else {
                    action = 0;
                    responce = Math.ceil(data["forecast"] * forecastProcent)
                }


            } else {
                responce = Math.ceil(data["forecast"] * forecastProcent)
                if(data["modelID"] == 197064){
                  //  alert(data["forecast"])
                }
                if(_procentSelected < 27 ){
                    responce = (responce * 1.5) > data["quantity"] ? data["quantity"] : (responce * 1.5);
                    responce = Math.ceil(responce );
                }
                if(data["modelID"] == 197064){
                  //  alert(responce)
                }
                action = 0;
            }
            // tjekker om vi med forecastProcent ikke kommer over max lager antal
            if( (data["quantity"] - responce) <= 0){
                responce = data["quantity"];
            }
        }
        /*
        if(data["difference"] == 0){


            // tjek om den skal lukke markeres
            if(data["quantity"] < data["forecast"]){
                action = 2;
            } else {
                action = 0;
            }
            responce = data["quantity"];

        }

         */
        _reserveret+=responce;
        _newResavation.push( {modelID: data["modelID"],quantity : responce,action: action} );

        if(action == 2){
            responce = responce + "(Overvåg.)";
        }
        return responce;


    }


</script>
</body>
</html>





