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
            <h2>Trim shop attributter</h2>
            <p>
                Hvis der er problemer med en shops attributter, fx newlines, så kan denne lave sql der trimmer attributterne.
            </p>
        </div>
        <div class="card-body">
            <div class="wizard-step" data-init-function="initShopIdStep">
                <div class="form-group">
                    <label for="shopid">Shop ID</label>
                    <input type="text" class="form-control" id="shopid" placeholder="Indtast Shop ID">
                </div>

                <div class="form-group">
                    <button class="btn btn-primary" id="test">Test</button>
                </div>

                <div class="form-group">
                    <label for="shopname">Shop navn</label>
                    <input type="text" class="form-control" id="shopname" readonly>
                </div>

                <script>
                    function initShopIdStep() {
                        disableNextButton();
                        document.getElementById('test').addEventListener('click', async function() {
                            const shopId = document.getElementById('shopid').value;
                            if (shopId) {
                                try {
                                    const data = await fetchJsonData('checkshop', { shopid: shopId });
                                    if (data && data.name) {
                                        document.getElementById('shopname').value = data.name;
                                        enableNextButton();
                                    } else {
                                        logMessage('Shop med ID ikke fundet.', true);
                                    }
                                } catch (error) {
                                    logMessage('Fejl under hentning af shop information.', true);
                                }
                            } else {
                                logMessage('Indtast venligst et Shop ID.', true);
                            }
                        });
                    }
                </script>
            </div>



            <div class="wizard-step" data-init-function="initStep2">
                <h2>Trin 2</h2>
                <p>Indhold af trin 2.</p>
                <!-- yderligere indhold -->
                <script>
                    function initStep2() {
                        // Kode til at initialisere Trin 2
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
