


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery and Shipping Presentation</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-size: 0.9rem;
        }
        h1 {
            font-size: 1.8rem;
        }
        h2 {
            font-size: 1.5rem;
        }
        h3 {
            font-size: 1.3rem;
        }
        h4 {
            font-size: 1.1rem;
        }
        .btn {
            font-size: 0.9rem;
        }
        .info-item {
            margin-bottom: 0.8rem;
        }
        .info-label {
            font-weight: bold;
        }
        .card {
            margin-bottom: 1.2rem;
        }
        .nested-info {
            margin-left: 1.2rem;
        }
        .flex-delivery-value {
            text-decoration: underline;
            font-weight: bold;
        }
        @media print {
            body {
                font-size: 0.8rem;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .address-card {
                page-break-inside: avoid;
                page-break-after: always;
            }
            .address-card:last-child {
                page-break-after: auto;
            }
            .no-print-detail-btn{
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Levering og fragt - <span id="company-name"></span></h2>
    <div class="mb-4 no-print">
        <button class="btn btn-primary" onclick="window.print()">Udskriv alt</button>
        <!-- <button class="btn btn-secondary ms-2" onclick="printDetailedDelivery()">Udskriv alle Detaljeret levering</button> -->
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">SO-nummer</h2>
        </div>
        <div class="card-body">
            <div class="info-item">
                <h3 class="info-label" id="so_no"></h3>
            </div>
        </div>
    </div>

        <div class="card">
            <div class="card-header">
                <h2 class="h4 mb-0">Leveringsdato</h2>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <span class="info-label">Leveringsdato:</span> <span id="deliveryDate"></span>
                </div>
            </div>
        </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">Fleksibel levering</h2>
        </div>
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Første dag:</span> <span id="flexStartDate"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Sidste dag:</span> <span id="flexEndDate"></span>
            </div>

        </div>
    </div>

    <div class="card">

        <div class="card-body">
            <!-- Tilføj to separate cards i en row -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="h4 mb-0">Salgssupport - note</h2>
                        </div>
                        <div class="card-body">
                            <span id="salesSupport"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="h4 mb-0">Overflytningsordre - note</h2>
                        </div>
                        <div class="card-body">
                            <span id="transferOrder"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">Leveringsdetaljer</h2>
        </div>
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Udlevering af gaver ved kunden:</span> <span id="handoverDate"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Hvis pakningen er klar før tid, må sælger/supporter kontaktes for evt. tidlig levering:</span> <span id="earlyDelivery"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Flere leveringsadresser:</span> <span id="multipleDeliveries"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Privatlevering:</span> <span id="privateDelivery"></span>
                <div class="nested-info" id="privateDeliveryDetails">
                    <div class="info-item">
                        <span class="info-label">Retur type:</span> <span id="returnType"></span>
                    </div>
                </div>
            </div>
            <div class="info-item">
                <span class="info-label">Julekort:</span> <span id="presentPapercard"></span>

            </div>
            <div class="info-item">
                <span class="info-label">Indpakning:</span> <span id="presentWrap"></span>

            </div>
            <div class="info-item">
                <span class="info-label">Navnelabels:</span> <span id="presentNametag"></span>
            </div>

            <div class="info-item">
                <span class="info-label">Speciel håndtering / særlig pak:</span> <span id="specielDelivery"></span>
                <div class="nested-info" id="specielDeliveryDetails">
                    <div class="info-item">
                        <span class="info-label">Note:</span> <span id="specielDeliveryNote"></span>
                    </div>
                </div>
            </div>


        </div>

    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">Udenlandslevering</h2>
        </div>
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Udenlandslevering:</span> <span id="foreignDeliveryStatus"></span>
            </div>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Land/Region</th>
                    <th>Fritekst</th>
                    <th>Afsendelsesdato</th>
                </tr>
                </thead>
                <tbody id="foreignDeliveryTable">
                <!-- Content will be dynamically inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">DOT</h2>
        </div>
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Ønskes DOT:</span> <span id="dotUse"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Antal adresser med DOT:</span> <span id="dotAddresses"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Note til dot levering:</span> <span id="dotNote"></span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">Opbæring</h2>
        </div>
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Ønskes opbæring:</span> <span id="carryUpUse"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Antal adresser med opbæring:</span> <span id="carryUpAddresses"></span>
            </div>

            <div class="info-item">
                <span class="info-label">Note til opbæring:</span> <span id="carryUpNote"></span>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">Fragtnote</h2>
        </div>
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Intern fragtnote:</span>
                <p id="delivery_note_internal"></p>
            </div>
            <div class="info-item">
                <span class="info-label">Ekstern fragtnote (til fragtmand / ordrebekræftelse):</span>
                <p id="delivery_note_external"></p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h2 class="h4 mb-0">Detaljeret levering</h2>
        </div>
        <div class="card-body">
            <div id="detailedDeliveryInfo">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>
    </div>

</div>

<script>
    var shopID = <?php echo $id; ?>;
    $(document).ready(function() {
        // AJAX kald for at hente data
        $.ajax({
            url: 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=warehousePortal/getshopmetadata',

            method: 'POST',
            data: {
                shop_id: shopID
            },
            dataType: 'json',
            success:  function(response) {
                if (response.status === 1 && response.data.metadata && response.data.metadata.length > 0) {
                    const data = response.data.metadata[0].attributes;

                    // Funktion til at formatere dato
                    function formatDate(dateObject) {
                        if (dateObject && dateObject.date) {
                            const date = new Date(dateObject.date);
                            return date.toLocaleDateString('da-DK', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit'
                            });
                        }
                        return 'N/A';
                    }

                    // Funktion til at få returtype tekst
                    function getReturnTypeText(type) {
                        switch(type) {
                            case 'gf':
                                return 'Retur til GF';
                            case 'virksomhed':
                                return 'Retur til virksomheden';
                            default:
                                return 'Ej taget stilling';
                        }
                    }

                    // Udfyld felterne
                    $('#salesSupport').html(formatText(data.w_noter) || 'Ingen noter');
                    $('#transferOrder').html(formatText(data.w_note_move) || 'Ingen noter');
                    $('#company-name').text(data.name);
                    $('#so_no').text(data.so_no);
                    $('#deliveryDate').text(formatDate(data.delivery_date));
                    $('#flexStartDate').text(formatDate(data.flex_start_delivery_date));
                    $('#flexEndDate').text(formatDate(data.flex_end_delivery_date));
                    $('#handoverDate').text(formatDate(data.handover_date));
                    $('#earlyDelivery').text(data.early_delivery ? 'Ja' : 'Nej');
                    $('#multipleDeliveries').text(data.multiple_deliveries ? 'Ja' : 'Nej');
                    $('#privateDelivery').text(data.private_delivery ? 'Ja' : 'Nej');
                    $('#specielDelivery').text(data.handling_special ? 'Ja' : 'Nej');


                    $('#delivery_note_internal').html(formatText(data.delivery_note_internal) || 'Ingen intern fragtnote');
                    $('#delivery_note_external').html(formatText(data.delivery_note_external) || 'Ingen ekstern fragtnote');
                    $('#presentPapercard').text(data.present_papercard ? 'Ja' : 'Nej');
                    $('#presentWrap').text(data.present_wrap ? 'Ja' : 'Nej');
                    $('#presentNametag').text(data.present_nametag ? 'Ja' : 'Nej');

                    // Vis eller skjul private levering detaljer baseret på private_delivery værdi
                    if (data.handling_special) {
                        $('#specielDeliveryDetails').show();
                        $('#specielDeliveryNote').html(formatText(data.handling_notes));
                    } else {
                        $('#specielDeliveryDetails').hide();
                    }


                    // Vis eller skjul private levering detaljer baseret på private_delivery værdi
                    if (data.private_delivery) {
                        $('#privateDeliveryDetails').show();
                        $('#returnType').text(getReturnTypeText(data.private_retur_type));
                    } else {
                        $('#privateDeliveryDetails').hide();
                    }

                    $('#foreignDeliveryStatus').text(data.foreign_delivery ? 'Ja' : 'Nej');
                    $('#dotUse').text(data.dot_use ? 'Ja' : 'Nej');
                    $('#dotAddresses').text(data.dot_amount);
                    $('#dotNote').html(formatText(data.dot_note));
                    $('#carryUpUse').text(data.carryup_use ? 'Ja' : 'Nej');
                    $('#carryUpAddresses').text(data.carryup_amount);
                    $('#carryUpNote').html(formatText(data.carryup_note));

                    // Forbered og sortér udenlandsk levering data SO130759
                    let deliveryData = [];
                    const foreignDeliveryNames = JSON.parse(data.foreign_delivery_names);
                    const foreignDeliveryDates = JSON.parse(data.foreign_delivery_date);
          
                    if(data.foreign_delivery == 1 || data.foreign_delivery == 2) {
                        for (const [key, value] of Object.entries(foreignDeliveryNames)) {
                            if (value === true || (typeof value === 'string' && value.length > 0)) {
                                if (key.endsWith('_freetext')) continue;  // Spring freetext entries over

                                let displayName = key.charAt(0).toUpperCase() + key.slice(1);
                                let dateKey = `foreign_${key}_date`;
                                const date = foreignDeliveryDates && foreignDeliveryDates[dateKey]
                                    ? new Date(foreignDeliveryDates[dateKey]).toLocaleDateString('da-DK')
                                    : 'Ikke angivet';
                                let freetext = foreignDeliveryNames[`${key}_freetext`] || '';

                                deliveryData.push({
                                    name: displayName,
                                    freetext: freetext,
                                    date: date,
                                    isAmerica: key === 'amerika'
                                });
                            }
                        }
                    }
                    // Sortér levering data
                    deliveryData.sort((a, b) => {
                        if (a.isAmerica) return -1;  // Amerika altid først
                        if (b.isAmerica) return 1;   // Amerika altid først
                        return new Date(a.date) - new Date(b.date);  // Sortér efter dato
                    });

                    // Generér tabel indhold
                    let tableContent = deliveryData.map(item => `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.freetext}</td>
                        <td>${item.date}</td>
                    </tr>
                `).join('');

                    $('#foreignDeliveryTable').html(tableContent);
                } else {
                    console.error('Invalid data structure received');
                    alert('Der opstod en fejl ved behandling af data. Kontakt venligst support.');
                }




            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error fetching data:', textStatus, errorThrown);
                alert('Der opstod en fejl ved hentning af data. Prøv venligst igen senere.');
            }
        });
        addDetailDelivery()
    });



    async function addDetailDelivery() {
        try {
            let shopAddress = await getShopAddress(shopID);
            if (shopAddress.status === 1 && shopAddress.data) {
                let detailedHtml = '';
                shopAddress.data.reverse().forEach((address, index) => {



                    detailedHtml += `
                        <div class="card mb-3 address-card" id="address-card-${index}">
                            <div class="card-header">
                                <h3 class="h5 mb-0">Leveringsadresse ${index + 1}</h3>
                                <button class="btn btn-sm btn-outline-secondary no-print no-print-detail-btn" onclick="printSingleAddress(${index})">Udskriv denne adresse</button>
                            </div>
                            <div class="card-body" id="address-${index}">
                                <div class="address-section mb-3">
                                    <h4 class="h6 mb-2">Adresseoplysninger</h4>
                                    <p class="mb-1"><strong>Navn:</strong> ${address.attributes.name}</p>
                                    <p class="mb-1"><strong>Adresse:</strong> ${address.attributes.address}</p>
                                    <p class="mb-1"><strong>Postnummer:</strong> ${address.attributes.zip}</p>
                                    <p class="mb-1"><strong>By:</strong> ${address.attributes.city}</p>
                                    <p class="mb-1"><strong>Land:</strong> ${address.attributes.country}</p>
                                    <p class="mb-1"><strong>Att:</strong> ${address.attributes.att || 'Ikke angivet'}</p>
                                    <p class="mb-1"><strong>Telefon:</strong> ${address.attributes.phone || 'Ikke angivet'}</p>
                                    <p class="mb-1"><strong>VAT:</strong> ${address.attributes.vatno || 'Ikke angivet'}</p>
                                </div>

                                <hr class="my-3">

                                <div class="additional-info-section">
                                    ${address.attributes.dot ? `
                                    <p class="mb-1"><strong>DOT Dato:</strong> ${formatDate(address.attributes.dot_date)}</p>
                                    ` : ''}

                                    ${address.attributes.carryup ? `
                                    <p class="mb-1"><strong>Opbæringstype:</strong> ${getCarryupTypeText(address.attributes.carryup_type)}</p>
                                    ` : ''}

                                    ${address.attributes.freight_note ? `
                                    <p class="mb-1"><strong>Fragtnoter:</strong> ${formatText(address.attributes.freight_note)}</p>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#detailedDeliveryInfo').html(detailedHtml);
            } else {
                console.error('Invalid data structure received for shop address');
                $('#detailedDeliveryInfo').html('<p>Der opstod en fejl ved hentning af detaljerede leveringsoplysninger.</p>');
            }
        } catch (error) {
            console.error('Error in addDetailDelivery:', error);
            $('#detailedDeliveryInfo').html('<p>Der opstod en fejl ved hentning af detaljerede leveringsoplysninger.</p>');
        }
    }


    function getCarryupTypeText(type) {
        switch(type) {
            case 3:
                return 'Plads til helpalle';
            case 2:
                return 'Plads til halvpalle';
            case 1:
                return 'Har ikke elevator';
            default:
                return 'VÆLG';
        }
    }
    function getShopAddress(shopID){
        return new Promise(function(resolve, reject) {
            $.ajax(
                {
                    url: 'index.php?rt=warehousePortal/getShopAddress',
                    type: 'POST',
                    dataType: 'json',
                    data: {shopID:shopID}
                }).done(function(res) {
                if(res.status == 0) { resolve(res) }
                else { resolve(res) }
            })
        })
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('da-DK', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function printDetailedDelivery() {
        $('.container > *').addClass('no-print');
        $('#detailedDeliveryInfo').closest('.card').removeClass('no-print');
        $('#detailedDeliveryInfo .no-print').removeClass('no-print').addClass('temp-print');

        window.print();

        $('.container > *').removeClass('no-print');
        $('.temp-print').addClass('no-print').removeClass('temp-print');
    }

    function printSingleAddress(index) {
        console.log('Printing address with index:', index);

        // Hide everything
        $('.container > *').addClass('no-print');
        // Show only the specific address card
        const addressCard = $(`#address-card-${index}`);
        if (addressCard.length) {
            addressCard.removeClass('no-print').addClass('print-only');
            // Temporarily remove the 'no-print' class from elements within this address card
            addressCard.find('.no-print').removeClass('no-print').addClass('temp-print');
            // Create a new print-only wrapper
            const printWrapper = $('<div>').addClass('print-only').css('display', 'none').appendTo('body');
            const clonedCard = addressCard.clone();
            clonedCard.find('.temp-print').remove(); // Remove print buttons from the clone
            printWrapper.append(clonedCard);
            window.print();
            printWrapper.remove();

        } else {
            alert('Der opstod en fejl ved udskrivning af adressen. Prøv venligst igen.');
        }

        // Restore the original classes
        $('.container > *').removeClass('no-print');
        $('.temp-print').addClass('no-print').removeClass('temp-print');
        $('.print-only').removeClass('print-only');

    }
    function formatText(text) {
        if (!text) return ''; // Handle null/undefined text
        return text.toString().replace(/\n/g, '<br>');
    }
</script>
</body>
</html>