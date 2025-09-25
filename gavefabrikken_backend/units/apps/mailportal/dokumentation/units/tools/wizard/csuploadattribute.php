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
            <h2>Upload shop attribute</h2>
            <p>
                Tilføj ekstra data til en valgshops attribut/felt. Attribut skal være oprettet i forvejen.
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

                                        $('#attributeid').html('');
                                        var attributes = data.attributes;

                                        // Add to select
                                        for (var i = 0; i < attributes.length; i++) {
                                            var attribute = attributes[i];
                                            $('#attributeid').append($('<option>', {
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
                    <label for="shopid">Attribut/Felt der skal indlæses til</label>
                    <select class="form-control" id="attributeid"></select>
                </div>
                <script>
                    async function initAttributeStep() {
                        if ($('#attributeid').is(':empty')) {
                            disableNextButton();
                            logMessage('Ingen attributter fundet.', true);
                            return;
                        } else {
                            enableNextButton();
                        }
                    }
                </script>
            </div>

            <div class="wizard-step" data-init-function="initTextStep">
                <div class="form-group">

                    <p>Kopier 2 kolonner fra excel, først username og så det felt der skal indlæses. Skal kopieres fra excel så der er tab imellem data, og kun de 2 kolonner.</p>

                    <div class="form-group">
                        <label for="shopid">Default værdi</label>
                        <input type="text" class="form-control" id="defaultvalue" placeholder="Angiv en standard værdi">
                    </div>


                    <label for="shopid">Data der skal kobles</label>
                    <textarea style="width: 100%; height: 500px;" id="importinput"></textarea>
                </div>
                <script>
                    function initTextStep() {

                    }
                </script>
            </div>

            <div class="wizard-step" data-init-function="finishTextStep">
                <div class="form-group">

                    <p>Herunder SQL der skal køres for at lave import. Husk at teste først!</p>
                    <textarea style="width: 100%; height: 500px;" id="importoutput"></textarea>
                </div>
                <script>


                    function finishTextStep() {
                        // Hent værdierne fra input felterne og cast dem til tal
                        var shopId = parseInt($('#shopid').val());
                        var attributeId = parseInt($('#attributeid').val());
                        var defaultValue = $('#defaultvalue').val().trim(); // Hent og trim standardværdien

                        // Hent tekst fra textarea og opdel i linjer
                        var lines = $('#importinput').val().split('\n');
                        var sqlCommands = [];

                        // Behandl hver linje
                        lines.forEach(function(line) {
                            // Fjern whitespace og tjek om linjen ikke er tom
                            line = line.trim();
                            if (line) {
                                // Opdel linjen i tabs og trim hver del
                                var parts = line.split('\t').map(function(part) {
                                    return part.trim();
                                });

                                // Brug standardværdien hvis værdien er tom
                                var value = parts[1] || defaultValue;
                                var username = parts[0];

                                // Escape værdier for at undgå SQL injection
                                value = escapeSqlValue(value);
                                username = escapeSqlValue(username);

                                // Konstruer SQL kommandoen
                                var sql = `UPDATE \`shop_user\`, user_attribute SET attribute_value = '${value}' WHERE shop_user.username LIKE '${username}' && shop_user.shop_id = ${shopId} && user_attribute.shop_id = ${shopId} && user_attribute.attribute_id = ${attributeId} && shop_user.id = user_attribute.shopuser_id && user_attribute.attribute_value = '';`;

                                // Tilføj SQL kommandoen til listen
                                sqlCommands.push(sql);
                            }
                        });

                        // Output alle SQL kommandoer til textarea #importoutput
                        $('#importoutput').val(sqlCommands.join('\n'));
                    }

                    // Hjælpefunktion til at escape SQL værdier
                    function escapeSqlValue(value) {
                        if (value === null || value === undefined) {
                            return '';
                        }
                        // Simpel escape mekanisme - til mere avanceret brug, overvej en rigtig SQL escape funktion
                        return value.replace(/[\0\x08\x09\x1a\n\r"'\\\%]/g, function (char) {
                            switch (char) {
                                case "\0":
                                    return "\\0";
                                case "\x08":
                                    return "\\b";
                                case "\x09":
                                    return "\\t";
                                case "\x1a":
                                    return "\\z";
                                case "\n":
                                    return "\\n";
                                case "\r":
                                    return "\\r";
                                case "\"":
                                case "'":
                                case "\\":
                                case "%":
                                    return "\\"+char; // foranstiller en backslash til backslash, procenttegn,
                                                      // og dobbelt/enkelt citationstegn
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
