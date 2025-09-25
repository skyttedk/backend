<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardshop overblik</title>
    <link href="<?php echo $assetPath; ?>/assets/status.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/assets/jquery.min.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>/assets/fontawesome.css">

</head>
<body>
<div class="container">
    <h1>
        <span>Cardshop værktøjer og overblik</span>
        <div class="country-flags">
            <?php echo $model->renderLanguageBar(); ?>
        </div>
    </h1>

    <!-- System Tools Links -->
    <h2 class="section-header">Systemværktøjer</h2>
    <?php $model->renderToolsContainer(); ?>


    <?php $model->renderMetrics(); ?>
    <?php $model->renderDistribution(); ?>
    <?php $model->renderShopList(); ?>

</div>

    <script src="<?php echo $assetPath; ?>/assets/statusscripts.js"></script>
</body>
</html>