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
            <h2>Merge 2 shop felter til et adresse ID</h2>
            <p>
                Vælg 2 felter der skal merges og hvor det skal merges hen
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

                                        $('#attrdest').html('');
                                        $('#attr1').html('');
                                        $('#attr2').html('');
                                        var attributes = data.attributes;

                                        // Add to select
                                        for (var i = 0; i < attributes.length; i++) {
                                            var attribute = attributes[i];
                                            $('#attrdest').append($('<option>', {
                                                value: attribute.id,
                                                text: attribute.name + " ("+attribute.id+")"
                                            }));

                                            $('#attr1').append($('<option>', {
                                                value: attribute.id,
                                                text: attribute.name + " ("+attribute.id+")"
                                            }));

                                            $('#attr2').append($('<option>', {
                                                value: attribute.id,
                                                text: attribute.name + " ("+attribute.id+")"
                                            }));
                                        }

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

            <div class="wizard-step" data-init-function="initAttributeStep">
                <div class="form-group">
                    <label for="attrdest">Attribut/felt der skal skrives til</label>
                    <select class="form-control" id="attrdest"></select>
                    <br><br><br>

                    <label for="attr1">Felt 1</label>
                    <select class="form-control" id="attr1"></select>
                    <br><br>


                    <label for="attr2">Felt 2</label>
                    <select class="form-control" id="attr2"></select>
                    <br><br>

                </div>
                <script>
                    async function initAttributeStep() {
                        if ($('#attrdest').is(':empty')) {
                            disableNextButton();
                            logMessage('Ingen attributter fundet.', true);
                            return;
                        } else {
                            enableNextButton();
                        }
                    }
                </script>
            </div>

            <div class="wizard-step" data-init-function="finishTextStep">
                <div class="form-group">

                    <p>SQL til tjek af forespørgel</p>
                    <textarea style="width: 100%; height: 400px;" id="checkoutput"></textarea>

                    <p>SQL til opdatering</p>
                    <textarea style="width: 100%; height: 400px;" id="updateoutput"></textarea>

                </div>
                <script>


                    function finishTextStep() {
                        const shopId = parseInt(document.getElementById('shopid').value, 10);
                        const attrDest = parseInt(document.getElementById('attrdest').value, 10);
                        const attr1 = parseInt(document.getElementById('attr1').value, 10);
                        const attr2 = parseInt(document.getElementById('attr2').value, 10);

                        const checkOutputSql = `
SELECT
    u1.shopuser_id as valid1, u2.shopuser_id as valid2, u3.shopuser_id as destid,
    u1.attribute_value as val1, u2.attribute_value as val2, u3.attribute_value as destbefore,
    CONCAT(u1.attribute_value,', ', u2.attribute_value) as destafter
FROM
    \`user_attribute\` u1, user_attribute u2, user_attribute u3
WHERE u1.shopuser_id NOT IN (SELECT id FROM shop_user WHERE shop_id = ${shopId} && is_demo=1) &&
    u1.shopuser_id = u2.shopuser_id && u2.shopuser_id = u3.shopuser_id && u3.shopuser_id = u1.shopuser_id &&
    u1.attribute_id = ${attr1} && u2.attribute_id = ${attr2} && u3.attribute_id = ${attrDest} &&
    u1.shop_id = ${shopId} && u2.shop_id = ${shopId} && u3.shop_id = ${shopId}
ORDER BY u1.shopuser_id`;

                        const updateOutputSql = `
UPDATE
    \`user_attribute\` u1, user_attribute u2, user_attribute u3
SET
    u3.attribute_value = CONCAT(u1.attribute_value,', ', u2.attribute_value)
WHERE u1.shopuser_id NOT IN (SELECT id FROM shop_user WHERE shop_id = ${shopId} && is_demo=1) &&
    u1.shopuser_id = u2.shopuser_id && u2.shopuser_id = u3.shopuser_id && u3.shopuser_id = u1.shopuser_id &&
    u1.attribute_id = ${attr1} && u2.attribute_id = ${attr2} && u3.attribute_id = ${attrDest} &&
    u1.shop_id = ${shopId} && u2.shop_id = ${shopId} && u3.shop_id = ${shopId} && u3.attribute_value = ''`;

                        document.getElementById('checkoutput').value = checkOutputSql;
                        document.getElementById('updateoutput').value = updateOutputSql;
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
