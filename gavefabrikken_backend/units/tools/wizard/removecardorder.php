<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Bootstrap Wizard</title>
    <link href="<?php echo $assetPath; ?>/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/assets/jquery.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/popper.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/bootstrap.min.js"></script>

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
            <h2>Fjern gavevalg</h2>
            <p>
               Hvis der er et gavevalg der skal fjernes.
            </p>
        </div>
        <div class="card-body">
            <div class="wizard-step" data-init-function="initShopIdStep">
                <div class="form-group">
                    <label for="shopid">Gavekort nr</label>
                    <input type="text" class="form-control" id="cardno" placeholder="Indtast gavekort nr">
                </div>

                <div class="form-group">
                    <button class="btn btn-primary" id="test">Dan SQL</button>
                </div>

                <textarea id="sql" style="width: 100%; height: 400px;"></textarea>

                <script>
                    function initShopIdStep() {
                        disableNextButton();
                        document.getElementById('test').addEventListener('click', async function() {

                            var cardNo = $('#cardno').val();

                            var sql = "#Tjek om den er sendt\r\nSELECT * FROM shipment WHERE  from_certificate_no = '"+cardNo+"' and shipment_type in ('privatedelivery', 'directdelivery');\r\n\r\n";
                            sql += "#Slet leverance hvis den ikke er sendt\r\nDELETE FROM shipment WHERE  from_certificate_no = '"+cardNo+"' and shipment_type in ('privatedelivery', 'directdelivery');\r\n\r\n";
                            sql += "#Opdater shopuser\r\nUPDATE shop_user set delivery_printed = 0, delivery_print_date = null, delivery_state = 0, navsync_date = null, navsync_status = 0  where is_giftcertificate = 1 and username LIKE '"+cardNo+"';\r\n\r\n";
                            sql += "#Slet ordre attributter\r\nDELETE FROM order_attribute WHERE order_id in (SELECT id FROM `order` WHERE user_username LIKE '"+cardNo+"' and shop_is_gift_certificate = 1) ;\r\n\r\n";
                            sql += "#Slet ordre\r\nDELETE FROM `order` WHERE user_username LIKE '"+cardNo+"' and shop_is_gift_certificate = 1;\r\n\r\n";

                            $('#sql').val(sql);

                        });
                    }
                </script>

            </div>

        </div>
    </div>

    <div id="log" class="mt-4 p-2 border rounded"></div> <!-- Log vindue -->
</div>

<script>

    var steps = $('.wizard-step');
    var currentStepIndex = 0;
    var prevButton = $('#prev-button');
    var nextButton = $('#next-button');


    // Funktion til at aktivere "Næste" knappen
    function enableNextButton() {
        nextButton.prop('disabled', false);
    }

    // Funktion til at deaktivere "Næste" knappen
    function disableNextButton() {
        nextButton.prop('disabled', true);
    }

    async function fetchJsonData(url, data) {
        try {
            const response = await fetch('<?php echo $servicePath; ?>'+url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Fortæller serveren at vi sender JSON
                },
                body: JSON.stringify(data), // Konverterer data-objektet til en JSON streng
            });

            if (!response.ok) {
                throw new Error(`HTTP fejl! status: ${response.status}`);
            }

            const jsonData = await response.json();
            return jsonData;

        } catch (error) {
            console.error('Fejl under hentning af JSON data:', error);
            return null;
        }
    }

    function logMessage(message, isError) {
        var logEntry = $('<div>').text(message);
        if (isError) {
            logEntry.addClass('error');
        }
        $('#log').append(logEntry);
    }

    function showStep(index) {
        var step = steps.eq(index);
        step.addClass('active').siblings().removeClass('active');

        prevButton.toggle(index > 0);
        nextButton.toggle(index < steps.length - 1);

        var initFunctionName = step.data('init-function');
        if (initFunctionName && typeof window[initFunctionName] === 'function') {
            window[initFunctionName]();
        }
    }

    prevButton.on('click', function() {
        if (currentStepIndex > 0) {
            currentStepIndex--;
            showStep(currentStepIndex);
        }
    });

    nextButton.on('click', function() {
        if (currentStepIndex < steps.length - 1) {
            currentStepIndex++;
            showStep(currentStepIndex);
        }
    });

    showStep(currentStepIndex); // Initialiserer visningen af første trin

    window.logMessage = logMessage; // Gør logMessage globalt tilgængelig



</script>
</body>
</html>
