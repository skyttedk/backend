<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations tjek</title>
    <link href="<?php echo $assetPath; ?>/../../../tools/wizard/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/../../../tools/wizard/assets/jquery.min.js"></script>
    <script src="<?php echo $assetPath; ?>/../../../tools/wizard/assets/popper.min.js"></script>
    <script src="<?php echo $assetPath; ?>/../../../tools/wizard/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>/../../../tools/wizard/assets/fontawesome.css">
    <style>

    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <form class="form-inline my-2 my-lg-0">
        <input class="form-control mr-sm-2" type="text" name="itemno" placeholder="Varenr" aria-label="Varenr">
        <select class="form-control mr-sm-2" name="location" type="text" placeholder="Lokation" aria-label="Lokation">
            <option value="">Alle lokationer</option>
            <?php

            $locationList = \NavisionLocation::find('all');
            foreach ($locationList as $location) {
                echo "<option value='{$location->code}'>{$location->code}</option>";
            }
            
            ?>
        </select>
        <select name="languageid" class="form-control mr-sm-2" type="text" placeholder="Land" aria-label="Land">
            <option value="1">Danmark</option>
            <option value="4">Norge</option>
            <option value="5">Sverige</option>
        </select>
        <button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="updateReservationSearch()">Opdater</button> &nbsp;
        <button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="updateReservationSearchNav()">Nav liste</button>
        &nbsp;
        <button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="updateReservationSearchCS()">CS liste</button>
    </form>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mt-4">Tjek reservations balance</h2>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>NAV: ID</th>
                    <th>NAV: Location</th>
                    <th>NAV: Dato</th>
                    <th>NAV: Adjustment</th>
                    <th>NAV: Sum</th>
                    <th>NAV: Balance</th>
                    <th>NAV: Note</th>
                    <th>Balance</th>
                    <th>CS: Location</th>
                    <th>CS: Dato</th>
                    <th>CS: Adjustment</th>
                    <th>CS: Sum</th>
                    <th>CS: Balance</th>
                    <th>CS: Note</th>
                    <th>CS: Shop</th>
                    <th>CS: ID</th>
                </tr>
                </thead>
                <tbody id="reservationbody">

                </tbody>
            </table>
        </div>
    </div>
</div>

<script>

    function updateReservationSearchCS() {
        var itemno = $('input[name="itemno"]').val();
        var location = $('select[name="location"]').val();
        var languageid = $('select[name="languageid"]').val();
        $.ajax({
            url: '<?php echo $servicePath; ?>reservationsearchcs',
            type: 'POST',
            data: {
                itemno: itemno,
                location: location,
                languageid: languageid
            },
            success: function (data) {
                $('#reservationbody').html(data);
            }
        });
    }

    function updateReservationSearchNav() {
        var itemno = $('input[name="itemno"]').val();
        var location = $('select[name="location"]').val();
        var languageid = $('select[name="languageid"]').val();
        $.ajax({
            url: '<?php echo $servicePath; ?>reservationsearchnav',
            type: 'POST',
            data: {
                itemno: itemno,
                location: location,
                languageid: languageid
            },
            success: function (data) {
                $('#reservationbody').html(data);
            }
        });
    }

    function updateReservationSearch() {
        var itemno = $('input[name="itemno"]').val();
        var location = $('select[name="location"]').val();
        var languageid = $('select[name="languageid"]').val();
        $.ajax({
            url: '<?php echo $servicePath; ?>reservationsearch',
            type: 'POST',
            data: {
                itemno: itemno,
                location: location,
                languageid: languageid
            },
            success: function (data) {
                $('#reservationbody').html(data);
            }
        });
    }

</script>

</body>
</html>