<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company notes</title>
    <link href="/gavefabrikken_backend/units/assets/libs/bootstrap.min.css" rel="stylesheet">
    <script src="/gavefabrikken_backend/units/assets/libs/jquery.min.js"></script>
    <script src="/gavefabrikken_backend/units/assets/libs/popper.min.js"></script>
    <script src="/gavefabrikken_backend/units/assets/libs/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/gavefabrikken_backend/units/assets/fontawesome.css">
    <script src="<?php echo $assetPath; ?>js/notemanager.js"></script>
    <style>
        <?php echo $styles ?? ""; ?>
    </style>
</head>
<body>
<div>
    <div style="width: 100%; padding: 10px; background: #6895D2; color: white;">
        Valgt ordre: <b><?php echo $order->order_no; ?></b><br>Virksomhed: <?php echo $order->company_name." (cvr: ".$order->cvr."), Shop: ".$order->shop_name.", Antal: ".$order->quantity; ?>
    </div>
    <div style="padding: 10px;">
        <?php echo $content ?? ""; ?>
    </div>
</div>

</body>
</html>
