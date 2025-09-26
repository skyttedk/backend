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
        .card {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="mb-4">GF Tools 'n Wizards</h1>

    <div class="row">
        <div class="col-12 col-md-6 col-lg-4" style="display:none;">
            <h3>Cardshop</h3>

        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <h3>Valgshop</h3>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"></i> Trim lokations attribut</h5>
                    <p class="card-text">Ved fejl på adressetildeling kan der trimmes og erstattes newlines på lokations-attributten.</p>
                    <a href="<?php echo $servicePath; ?>trimshopattribute" class="btn btn-primary">Gå til værktøj</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"></i> Sammenlæg 2 kolonner</h5>
                    <p class="card-text">Sammenlæg 2 kolonner til et adresse ID</p>
                    <a href="<?php echo $servicePath; ?>csmerge2" class="btn btn-primary">Gå til værktøj</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"></i> Upload attribut</h5>
                    <p class="card-text">Tilføj data til en specifik attribut på en valgshop.</p>
                    <a href="<?php echo $servicePath; ?>csuploadattribute" class="btn btn-primary">Gå til værktøj</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"></i> Flyt data</h5>
                    <p class="card-text">Flyt data til en anden kolonne.</p>
                    <a href="<?php echo $servicePath; ?>movefield" class="btn btn-primary">Gå til værktøj</a>
                </div>
            </div>

        </div>

        <div class="col-12 col-md-6 col-lg-4" style="display:none;">
            <h3>Rapporter</h3>

        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <h3>Navision</h3>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"></i> Tjek betalingsstatus</h5>
                    <p class="card-text">Tjek betalingsstatus på et BS nr.</p>
                    <a href="<?php echo $servicePath; ?>navpaystatus" class="btn btn-primary">Gå til værktøj</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"></i> Generer order XMl</h5>
                    <p class="card-text">Generer xml på en bs ordre</p>
                    <a href="<?php echo $servicePath; ?>navorderxml" class="btn btn-primary">Gå til værktøj</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"></i> Generer shipment XMl</h5>
                    <p class="card-text">Generer xml på en shipment</p>
                    <a href="<?php echo $servicePath; ?>navshipmentxml" class="btn btn-primary">Gå til værktøj</a>
                </div>
            </div>

        </div>



    </div>
</div>

</body>
</html>
