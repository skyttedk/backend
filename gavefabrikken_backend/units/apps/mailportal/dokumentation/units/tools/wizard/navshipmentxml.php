<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Navision Pay Status tool</title>
    <link href="<?php echo $assetPath; ?>/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/assets/jquery.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/popper.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/bootstrap.min.js"></script>
    <script>

        var servicePath = '<?php echo $servicePath; ?>';

    </script>
    <style>
        .wizard-step {
            display: none;
        }
        .wizard-step.active {
            display: block;
        }
    </style>

</head>
<body>

<div class="container mt-5">
    <div id="wizard-container" class="card">
        <div class="card-header">
            <h2>Navision shipment XML værktøj</h2>
            <p>
                Indtast ID på en shipment for at danne xml
            </p>
        </div>
        <div class="card-body">
            <div class="wizard-step" data-init-function="initShopIdStep">
                <div class="form-group">
                    <label for="shipmentid">Shipment ID</label>
                    <input type="text" class="form-control" id="shipmentid" placeholder="Shipment ID">
                </div>

                <div class="form-group">
                    <button class="btn btn-primary" id="test">Dan xml</button>
                </div>

                <textarea id="shipmentxml" style="width: 100%; height: auto; min-height: 500px;">

                </textarea>

                <script>
                    function initShopIdStep() {
                        $('#paystatusresponse').html("Danner xml");
                        document.getElementById('test').addEventListener('click', async function() {
                            const shipmentid = document.getElementById('shipmentid').value;
                            if (shipmentid) {
                                try {
                                    const data = await fetchJsonData('servicenavshipmentxml', { shipmentid: shipmentid });
                                    if (data) {

                                        logMessage('Shipment dannet for '+shipmentid, true);
                                        $('#shipmentxml').val(data.xml);

                                    } else {
                                        $('#shipmentxml').val('Shipment ikke fundet');
                                        logMessage('Shipment ikke fundet', true);
                                    }
                                } catch (error) {
                                    $('#shipmentxml').val("Fejl under hentning");
                                    logMessage('Fejl under hentning af shop information.', true);
                                }
                            } else {
                                $$('#shipmentxml').val("Indtast id");
                                logMessage('Indtast venligst id', true);
                            }
                        });
                    }
                </script>
            </div>
            <div class="mt-3">
                <button id="prev-button" class="btn btn-secondary" style="display: none;">Tilbage</button>
                <button id="next-button" class="btn btn-primary">Næste</button>
            </div>
        </div>
    </div>
    <div id="log" class="mt-4 p-2 border rounded"></div> <!-- Log vindue -->
</div>
<script src="<?php echo $assetPath; ?>/assets/wizardhelpers.js"></script>
</body>
</html>
