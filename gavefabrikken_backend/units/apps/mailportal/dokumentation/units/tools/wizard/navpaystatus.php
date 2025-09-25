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
            <h2>Navision betalings status værktøj</h2>
            <p>
                Indtast BS nr og tjek betalingsstatus i navision.
            </p>
        </div>
        <div class="card-body">
            <div class="wizard-step" data-init-function="initShopIdStep">
                <div class="form-group">
                    <label for="shopid">BS nr</label>
                    <input type="text" class="form-control" id="bsno" placeholder="BS nr.">
                </div>

                <div class="form-group">
                    <button class="btn btn-primary" id="test">Tjek</button>
                </div>

                <div id="paystatusresponse">

                </div>


                <script>
                    function initShopIdStep() {
                        $('#paystatusresponse').html("Tjekker status");
                        document.getElementById('test').addEventListener('click', async function() {
                            const bsno = document.getElementById('bsno').value;
                            if (bsno) {
                                try {
                                    const data = await fetchJsonData('checkbspaystatus', { bsno: bsno });
                                    if (data) {

                                        $('#paystatusresponse').html(createTableFromJson(data));

                                    } else {
                                        $('#paystatusresponse').html("BS nr ikke fundet");
                                        logMessage('BS nr ikke fundet.', true);
                                    }
                                } catch (error) {
                                    $('#paystatusresponse').html("Fejl under hentning");
                                    logMessage('Fejl under hentning af shop information.', true);
                                }
                            } else {
                                $('#paystatusresponse').html("Indtast bs nr");
                                logMessage('Indtast venligst BS nr.', true);
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
