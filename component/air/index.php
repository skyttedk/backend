<?php
// https://system.gavefabrikken.dk//gavefabrikken_backend/component/air
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airfryer Retur Registrering</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .input-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 1rem;
        }

        #serialNumber {
            font-size: 1.5rem;
            height: 60px;
        }

        #registerButton {
            height: 60px;
            font-size: 1.2rem;
            margin-top: 1rem;
        }

        .message {
            margin-top: 2rem;
            padding: 1rem;
            display: none;
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .input-container {
                padding: 1rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="input-container">
        <h1 class="text-center mb-4">Airfryer Retur Registrering</h1>

        <div class="form-group">
            <input type="text"
                   class="form-control"
                   id="serialNumber"
                   placeholder="Indtast 6-cifret serienummer"
                   maxlength="6"
                   pattern="\d*"
                   inputmode="numeric">
        </div>

        <button id="registerButton"
                class="btn btn-primary w-100">
            Registrer Retur
        </button>

        <div class="alert message" role="alert"></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        const messageElement = $('.message');
        const serialInput = $('#serialNumber');
        const registerBtn = $('#registerButton');

        // Fokuser på input felt ved start
        serialInput.focus();

        // Kun tillad numerisk input
        serialInput.on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Håndter Enter tast
        serialInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                registerBtn.click();
            }
        });

        // Vis loading state på knap
        function setLoadingState(loading) {
            if (loading) {
                registerBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registrerer...');
            } else {
                registerBtn.prop('disabled', false).text('Registrer Retur');
            }
        }

        // Vis besked funktion
        function showMessage(type, text) {
            messageElement
                .removeClass('alert-success alert-warning alert-danger')
                .addClass('alert-' + type)
                .text(text)
                .show();
        }

        registerBtn.click(function() {
            const serialNumber = serialInput.val();

            // Valider input
            if (serialNumber.length !== 6) {
                showMessage('danger', 'Serienummeret skal være 6 cifre.');
                serialInput.focus();
                return;
            }

            // Nulstil besked og vis loading
            messageElement.hide();
            setLoadingState(true);

            // Send request til logic.php
            $.ajax({
                url: 'logic.php',
                method: 'POST',
                data: { serialNumber: serialNumber },
                dataType: 'json'
            })
                .done(function(response) {
                    switch(response.status) {
                        case 'already_registered':
                            showMessage('warning', `Denne airfryer er allerede registreret den ${response.date}`);
                            break;
                        case 'success':
                            showMessage('success', 'Airfryer er nu registreret som returneret!');
                            serialInput.val('').focus();
                            break;
                        case 'error':
                            showMessage('danger', response.message || 'Der skete en ukendt fejl. Prøv venligst igen.');
                            break;
                        default:
                            showMessage('danger', 'Der skete en uventet fejl. Prøv venligst igen.');
                    }
                })
                .fail(function(jqXHR) {
                    let errorMsg = 'Der skete en fejl ved forbindelse til serveren. Prøv venligst igen.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMsg = jqXHR.responseJSON.message;
                    }
                    showMessage('danger', errorMsg);
                })
                .always(function() {
                    setLoadingState(false);
                    serialInput.focus();
                });
        });
    });
</script>
</body>
</html>