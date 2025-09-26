
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
        const response = await fetch(servicePath+url, {
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


function createTableFromJson(jsonData) {
    // Opretter en tabel
    let table = document.createElement('table');
    table.className = 'table table-bordered';

    // Tilføjer en række til tabellen for hver nøgle-værdi par
    function addRows(data, table, level = 0) {
        Object.keys(data).forEach(key => {
            let row = table.insertRow(-1);
            let cellKey = row.insertCell(0);
            let cellValue = row.insertCell(1);

            // Tilføj indrykning for niveau
            cellKey.style.paddingLeft = `${level * 20}px`;

            // Tjekker om værdien er et objekt eller et array
            if (typeof data[key] === 'object' && data[key] !== null) {
                // Tilføj en fed skrifttype til nøglen for at markere det som en overskrift
                cellKey.innerHTML = `<strong>${key}</strong>`;
                cellKey.colSpan = 2; // Flet celler for overskrift
                addRows(data[key], table, level + 1); // Rekursivt tilføj rækker for indlejret objekt/array
            } else {
                // Simpel nøgle-værdi par
                cellKey.innerHTML = key;
                cellValue.innerHTML = data[key];
            }
        });
    }

    addRows(jsonData, table); // Start rekursion med rodniveau

    return table;
}