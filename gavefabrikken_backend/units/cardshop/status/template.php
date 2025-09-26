<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? "";  ?></title>
    <link href="<?php echo $assetPath ?? ""; ?>/assets/status.css" rel="stylesheet">
    <script src="<?php echo $assetPath ?? ""; ?>/assets/jquery.min.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPath ?? ""; ?>/assets/fontawesome.css">
</head>
<body>
<div class="container">
    <h1>
        <span><?php echo $title ?? "";  ?></span>
    </h1>

    <?php echo $body??""; ?>

</div>

<script src="<?php echo $assetPath ?? ""; ?>/assets/statusscripts.js"></script>
</body>
</html>