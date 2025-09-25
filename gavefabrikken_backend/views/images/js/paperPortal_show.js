/*

        // skjuler papir tab
        company.js
        if (!$("#shopState-6").is(":checked")) {
            $("#GavevalgLink").hide();
        }
 */

function PaperPortal(isAdmin) {
    let self = this;
    let template = {};
    let settings = {};
    let shopID = {};
    let data = {};
    let nameIdArray = {};
    let nametoIdArray = {};
    let uploadedData = [];
    let _presentList = {};
    let workerDataTable = {};
    let paperSettings = {};

    this.init = async function (paperSettings) {
        this.paperSettings = paperSettings;
        this.template = new PaperPortalTemplate();
        $("#login-form").remove();

        _companyID = (await this.getCompany()).data.company_id;



        let fieldSettings = await this.getfieldSettings();
        if(_distributionType == "list"){

            fieldSettings = renameField(fieldSettings, "Navn", "Antal");
        }


        let presentList = await this.getPresentList();
        this._presentList = presentList
        let workerList = await this.getWorkerList();

        this.nameIdArray = fieldSettings.data.reduce((acc, item) => {
            acc[item.attributes.id] = item.attributes.name;
            return acc;
        }, {});
        this.nametoIdArray = fieldSettings.data.reduce((acc, item) => {
            acc[item.attributes.name] = item.attributes.id;
            return acc;
        }, {});


        const presentMap = new Map(presentList.data.map(present => [
            present.attributes.model_id,
            present.attributes
        ]));


        // Opret et Map af field settings for hurtig opslag
        const fieldSettingsMap = new Map(fieldSettings.data.map(field => [
            field.attributes.id.toString(),
            field.attributes
        ]));

        // Kombiner data
        const combinedData = workerList.data.map(worker => {
            const modelId = worker.order.model_id; // Ændret fra present_id til model_id
            const presentData = presentMap.get(modelId);

            // Berig attributter med field settings
            const enrichedAttributes = Object.entries(worker.attributes).map(([key, value]) => {
                const fieldSetting = fieldSettingsMap.get(key);
                return [fieldSetting.name , {
                    value: value,
                    id: key,
                    name: fieldSetting ? fieldSetting.name : null,
                    dataType: fieldSetting ? fieldSetting.data_type : null,
                    isList: fieldSetting ? fieldSetting.is_list : null,
                    listData: fieldSetting ? fieldSetting.list_data : null
                }];
            });

            return {
                ...worker,
                attributes: Object.fromEntries(enrichedAttributes),
                present: presentData ? {
                    id: presentData.id,
                    model_id: presentData.model_id,
                    model_name: presentData.model_name,
                    model_present_no: presentData.model_present_no,
                    media_path: presentData.media_path,
                    fullalias: presentData.fullalias
                } : null
            };
        });
        $("#paper-reg").html(this.template.layout(fieldSettings,presentList));

        this.workerTable(fieldSettings,combinedData);

        this.setEvents();
        if (this.paperSettings.isEnabled === "false"  && isAdmin==false)  {
            $("#paper-reg").remove()
            $("#paper-upload").remove()
        }
        if(_distributionType == "list") {
            $("#gift-tab").remove();
            $("#worker-tab").text("Indtast fordelingsliste")
            $("#sum_fordeling_container").prepend('<button id="downloadListCSV" class="btn btn-primary me-3 actionBtn">Download fordelingsliste</button>');

            // Tilføj event listener til den nye knap
            $("#downloadListCSV").on('click', this.downloadListCSV.bind(this));

        }
        if(_distributionType == "list"){
            $("#downloadCSV").hide();
            $("#downloadGavealiasCSV").hide();
            $(".uploadCSV").hide();
        }
        if(_distributionType == "list") {
            $("#sum_fordeling_container").show();
        }
        this.calculateAndUpdateSum();
    };
    this.getCompany = async function() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: BASE_AJAX_URL + "paperPortal/getCompany",
                type: 'POST',
                data: { shopID: _shopID },
                dataType: 'json',
                success: function(response) {
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching company data:", error);
                    reject(error);
                }
            });
        });
    };
    this.generateDistributionList = function() {

        const table = this.workerDataTable;
        if (!table) {
            console.error('Worker table not initialized');
            return;
        }

        // Get all data from the DataTable
        const data = table.rows().data().toArray();

        // Get headers (excluding 'Handling' column, 'Navn', and specified fields)
        const excludeFields = ['user_id', 'model_id', 'present_id', 'Handling', 'Navn'];
        const headers = table.columns().header().toArray()
            .map(th => th.textContent)
            .filter(header => !excludeFields.includes(header));

        // Create a map to store summed up data
        const summedData = new Map();

        // Process the data
        data.forEach(row => {
            // Create a key from all fields including 'Gave'
            const key = headers.map(h => row[h] || '').join('|');

            if (!summedData.has(key)) {
                const filteredRow = {};
                headers.forEach(h => filteredRow[h] = row[h]);
                summedData.set(key, { ...filteredRow, count: 1 });
            } else {
                const existingRow = summedData.get(key);
                existingRow.count += 1;
                summedData.set(key, existingRow);
            }
        });

        // Convert summedData to an array and sort by 'Gave'
        const sortedSummedData = Array.from(summedData.values()).sort((a, b) => {
            const aliasA = a['Gave'].split('-')[0].trim();
            const aliasB = b['Gave'].split('-')[0].trim();
            const numA = parseInt(aliasA);
            const numB = parseInt(aliasB);
            if (!isNaN(numA) && !isNaN(numB)) {
                return numA - numB;
            }
            return aliasA.localeCompare(aliasB, undefined, {numeric: true, sensitivity: 'base'});
        });

        // Generate the distribution list HTML
        let html = '<div class="container mt-4">';

        // Add download button
        html += '<button id="downloadFordelingsliste" class="btn btn-primary mb-3">Download Fordelingsliste</button>';

        // Add summed up list
        html += '<div class="table-responsive mb-5">';
        html += '<table class="table table-striped table-bordered table-hover">';
        html += '<thead class="table-dark"><tr>' + headers.map(h => `<th scope="col">${h}</th>`).join('') + '<th scope="col">Antal</th></tr></thead>';
        html += '<tbody>';

        let totalCount = 0;
        sortedSummedData.forEach(row => {
            html += '<tr>';
            headers.forEach(h => {
                html += `<td>${row[h] || ''}</td>`;
            });
            html += `<td>${row.count}</td>`;
            html += '</tr>';
            totalCount += row.count;
        });

        // Add sum row
        html += '<tr class="table-secondary"><td colspan="' + headers.length + '"><strong>Sum</strong></td><td><strong>' + totalCount + '</strong></td></tr>';

        html += '</tbody></table>';
        html += '</div>';

        // Add gift summary
        html += '<h3 class="mb-3">Samlet opsummering</h3>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-striped table-bordered table-hover">';
        html += '<thead class="table-dark"><tr><th scope="col">Gave</th><th scope="col">Antal</th></tr></thead>';
        html += '<tbody>';

        const giftCounts = new Map();
        sortedSummedData.forEach(row => {
            const gift = row['Gave'];
            giftCounts.set(gift, (giftCounts.get(gift) || 0) + row.count);
        });

        // Sort giftCounts by gift alias
        const sortedGiftCounts = Array.from(giftCounts.entries()).sort((a, b) => {
            const aliasA = a[0].split('-')[0].trim();
            const aliasB = b[0].split('-')[0].trim();
            const numA = parseInt(aliasA);
            const numB = parseInt(aliasB);
            if (!isNaN(numA) && !isNaN(numB)) {
                return numA - numB;
            }
            return aliasA.localeCompare(aliasB, undefined, {numeric: true, sensitivity: 'base'});
        });

        let totalGiftCount = 0;
        sortedGiftCounts.forEach(([gift, count]) => {
            html += `<tr><td>${gift}</td><td>${count}</td></tr>`;
            totalGiftCount += count;
        });

        // Add sum row to gift summary
        html += '<tr class="table-secondary"><td><strong>Sum</strong></td><td><strong>' + totalGiftCount + '</strong></td></tr>';

        html += '</tbody></table>';
        html += '</div>';

        html += '</div><br><br>'; // Close container

        // Update the distribution list container
        document.getElementById('distributionListContainer').innerHTML = html;

        // Add event listener for download button
        document.getElementById('downloadFordelingsliste').addEventListener('click', () => this.downloadFordelingsliste(sortedSummedData, sortedGiftCounts, headers));
    };

    // Opdater downloadFordelingsliste-metoden for at inkludere sum-linjer
    this.downloadFordelingsliste = function(sortedSummedData, sortedGiftCounts, headers) {
        // Start with UTF-8 BOM
        let csv = '\uFEFF';

        csv += 'Summeret Liste\n';
        csv += headers.join(';') + ';Antal\n';

        let totalCount = 0;
        sortedSummedData.forEach(row => {
            csv += headers.map(h => this.escapeCSV(row[h] || '')).join(';') + `;${row.count}\n`;
            totalCount += row.count;
        });

        // Add sum row
        csv += 'Sum;;' + ';'.repeat(headers.length - 2) + `${totalCount}\n`;

        csv += '\nGave Opsummering\n';
        csv += 'Gave;Antal\n';

        let totalGiftCount = 0;
        sortedGiftCounts.forEach(([gift, count]) => {
            csv += `${this.escapeCSV(gift)};${count}\n`;
            totalGiftCount += count;
        });

        // Add sum row to gift summary
        csv += `Sum;${totalGiftCount}\n`;

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "fordelingsliste.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };


    this.downloadFordelingsliste = function(sortedSummedData, sortedGiftCounts, headers) {
        // Start with UTF-8 BOM
        let csv = '\uFEFF';

        csv += 'Summeret Liste\n';
        csv += headers.join(';') + ';Antal\n';

        sortedSummedData.forEach(row => {
            csv += headers.map(h => this.escapeCSV(row[h] || '')).join(';') + `;${row.count}\n`;
        });

        csv += '\nGave Opsummering\n';
        csv += 'Gave;Antal\n';
        sortedGiftCounts.forEach(([gift, count]) => {
            csv += `${this.escapeCSV(gift)};${count}\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "fordelingsliste.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };

    this.downloadListCSV = function() {
        const table = this.workerDataTable;
        if (!table) {
            console.error('Worker table not initialized');
            return;
        }

        const data = table.rows().data().toArray();
        const headers = Object.keys(data[0]).filter(header =>
            !['user_id', 'model_id', 'present_id', 'is_sync'].includes(header)
        );

        let csvContent = "\uFEFF"; // UTF-8 BOM for Excel kompatibilitet
        csvContent += headers.join(';') + '\n';

        data.forEach(row => {
            const rowData = headers.map(header =>
                this.escapeCSV(this.replaceDashWithColon(row[header] || ''))
            );
            csvContent += rowData.join(';') + '\n';
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "fordelingsliste.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    };

    this.escapeCSV = function(str) {
        if (typeof str !== 'string') return str;
        // Hvis strengen indeholder semikolon, linjeskift eller dobbelte anførselstegn, indkapsles den i dobbelte anførselstegn
        if (str.includes(';') || str.includes('\n') || str.includes('"')) {
            return '"' + str.replace(/"/g, '""') + '"';
        }
        return str;
    };

    this.replaceDashWithColon = function(str) {
        if (typeof str === 'string') {
            return str.replace(/-/g, ' ');
        }
        return str;
    };

    this.setEvents = function (){
        let self = this;
        this.setUploadEvent();
        $('#importCSVData').unbind('click').on('click', function() {
            let data = self.replaceNamesWithIds(self.uploadedData,self.nametoIdArray);
            console.log(data)
            if(data )  {

                self.importWorkerList(data);
            } else {
                alert("Der er fejl i de importerede data, tjek om de øverste felter i csv filen har de rigtige værdier")
            }

        })

        $('#paperPDF').on('click', async function() {
            var $btn = $(this);
            var originalText = $btn.text();

            try {
                // Disable button and add spinner
                $btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' +
                        '<span class="visually-hidden">Loading...</span> ' +
                        originalText);

                // Call the async function to get the URL
                let url = await _ptMakePaperPDF.makePaperPdfUrl();

                window.open(url, '_blank');
                // Here you can add code to handle the URL, e.g., open it in a new tab or trigger a download

            } catch (error) {
                console.error('Error generating PDF URL:', error);
                // You might want to show an error message to the user here
            } finally {
                // Re-enable button and remove spinner, regardless of success or failure
                $btn.prop('disabled', false).text(originalText);
            }
        })
        $('#presentPDF').on('click', async function() {
            var $btn = $(this);
            var originalText = $btn.text();
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' +
                    '<span class="visually-hidden">Loading...</span> ' +
                    originalText);


            let url = "https://system.gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&user="+_token+"&print&paper";
            console.log(url);

            $.ajax(
                {
                    url: 'index.php?rt=ptAdmin/uploadPDF',
                    type: 'POST',
                    dataType: 'json',
                    data: {shopId:_shopId,url:url}
                }
            ).done(function(res) {
                var jdata =  res.data.file;
                let url = "https://presentation.gavefabrikken.dk/presentation/pdf/"+jdata+".pdf";
                window.open(url, '_blank');
                $btn.prop('disabled', false).text(originalText);
                }
            )
        })



        $('#downloadGavealiasCSV').on('click', function() {
            if (self.paperSettings.isEditable === "false" && isAdmin == false) {
                return;
            }
            let jsonData = self._presentList;
            var headers = ['Varenummer', 'Gavenavn', 'Gavealias'];
            var csvContent = headers.join(';') + '\n';

            // Sort the data array by fullalias, treating numeric values correctly
            jsonData.data.sort((a, b) => {
                const aliasA = a.attributes.fullalias;
                const aliasB = b.attributes.fullalias;

                // Check if both aliases are numeric
                const numA = parseFloat(aliasA);
                const numB = parseFloat(aliasB);

                if (!isNaN(numA) && !isNaN(numB)) {
                    return numA - numB;
                }

                // If one or both are not numeric, use string comparison
                return aliasA.localeCompare(aliasB, undefined, {numeric: true, sensitivity: 'base'});
            });

            $.each(jsonData.data, function(index, item) {
                var attrs = item.attributes;
                var gavenavn = attrs.model_name + (attrs.model_no ? ' ' + attrs.model_no : '').trim();
                var row = [
                    attrs.model_present_no,
                    gavenavn,
                    attrs.fullalias
                ];
                csvContent += row.join(';') + '\n';
            });

            var encodedUri = encodeURI('data:text/csv;charset=utf-8,\uFEFF' + csvContent);
            var link = $('<a>')
                .attr('href', encodedUri)
                .attr('download', 'gavealias_list.csv')
                .appendTo('body');

            link[0].click();
            link.remove();

        });

}



this.replaceNamesWithIds = function (dataArray,idMap)
{
    let self = this;
    console.log(self._presentList)

    const result = dataArray.map(item => {
        const newItem = {};
        for (let key in item) {
            if (key === "Gave") {
                // Konverter alias til lowercase og trim eventuelle mellemrum
                const lowerCaseAlias = item[key].toLowerCase().trim();
                newItem[key] = lowerCaseAlias;

                // Find matching present baseret på lowercase fullalias
                const matchingPresent = self._presentList.data.find(p =>
                    p.attributes.fullalias.toLowerCase().trim() === lowerCaseAlias
                );

                if (matchingPresent) {
                    newItem['model_id'] = matchingPresent.attributes.model_id;
                    newItem['present_id'] = matchingPresent.attributes.present_id;
                } else {
                    console.log("Kunne ikke finde matching gave for:", lowerCaseAlias);
                    // Hvis du vil, kan du her tilføje en fejlhåndtering eller markering af problematiske rækker
                }
            } else if (idMap.hasOwnProperty(key)) {
                newItem[idMap[key]] = item[key];
            } else {
                console.log("Ukendt nøgle:", key);
                // Hvis en nøgle (andet end Gave) ikke er i idMap, returner false
                return false;
            }
        }
        return newItem;
    });

    // If any item resulted in false, the entire operation failed
    if (result.includes(false)) {
        return false;
    }

    return result;
}


this.setUploadEvent = function (){
        let self = this;
    $('#paper-upload').html(`
            <div class="mb-3 text-center">
                <button id="downloadCSV" class="btn btn-success me-1 actionBtn">Skabelon til indlæsning </button>
                <button id="downloadGavealiasCSV" class="btn btn-success me-1 actionBtn">Excel gaveliste </button>
                <label for="uploadCSV" class="btn btn-primary me-1 actionBtn uploadCSV">Importér gavevalg</label>
                <input type="file" id="uploadCSV" class="uploadCSV" accept=".csv" style="display: none;">
                <button id="paperPDF" class="btn btn-secondary   me-1 actionBtn">Gaveoversigt til udlevering</button>
                 <button id="presentPDF" class="btn btn-secondary  me-1 actionBtn">Præsentation af gaver</button>
            </div>
        `);
    if (self.paperSettings.isEditable === "false" && isAdmin==false) {
        $('#paper-upload').remove();
    }

    $('#uploadCSV').unbind('change').on('change', function(e) {
        var file = e.target.files[0];
        var reader = new FileReader();
        reader.onload = function(e) {
            var csvData = e.target.result;
            var allTextLines = csvData.split(/\r\n|\n/);
            var headers = allTextLines[0].split(';');
            var lines = [];

            // Populate table headers
            var previewTableHead = $('#csvPreviewTable thead tr');
            previewTableHead.empty();
            previewTableHead.append($('<th>').text('#')); // Add line number header
            headers.forEach(function(header) {
                previewTableHead.append($('<th>').text(header));
            });

            for (var i=1; i<allTextLines.length; i++) {
                var data = allTextLines[i].split(';');
                if (data.length == headers.length) {
                    var tarr = {};
                    for (var j=0; j<headers.length; j++) {
                        tarr[headers[j]] = data[j];
                    }
                    lines.push(tarr);
                }
            }

            // Store the parsed data
            self.uploadedData = lines;

            // Populate preview table
            var previewTableBody = $('#csvPreviewTable tbody');
            previewTableBody.empty();
            lines.forEach(function(line, index) {
                var row = $('<tr>');
                row.append($('<td>').text(index + 1)); // Add line number
                headers.forEach(function(header) {
                    row.append($('<td>').text(line[header]));
                });
                previewTableBody.append(row);
            });

            // Show modal
            var modal = new bootstrap.Modal(document.getElementById('csvPreviewModal'));
            modal.show();

            console.log('Data stored:', self.uploadedData);
        };
        reader.readAsText(file, 'UTF-8');  // Specify UTF-8 encoding
        self.setUploadEvent();
    });
    $('#downloadCSV').unbind('click').on('click', function() {
        if (self.paperSettings.isEditable === "false" && isAdmin==false) {
            return;
        }
        let csvContent = "data:text/csv;charset=utf-8,\uFEFF";  // Adding BOM for Excel compatibility

        // Get the header row
        let headers = $('#workerTabel thead th').map(function() {
            return $(this).text();
        }).get();

        // Remove the "Handling" column from headers
        headers.pop();

        csvContent += headers.join(';') + "\n";  // CSV header with semicolon separator


        // Create a download link and trigger the download
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "skabelon.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

}




    this.getWorkerTableHeaderData = function (fieldSettings, workersData) {
        // Sorter feltdefinitioner baseret på deres indeks
        const sortedFieldDefinitions = fieldSettings.data
            .sort((a, b) => a.attributes.index - b.attributes.index)
            .map(field => field.attributes);

        // Generer kolonnedefinitioner for DataTables
        let columns = sortedFieldDefinitions.map(fieldDef => ({
            data: fieldDef.name
        }));

        // Tilføj 'Gave' kolonnen til sidst
        columns.push({ data: 'Gave' });
        columns.push({
            data: null,
            defaultContent: '<button class="btn btn-sm btn-primary worker-edit-btn">Edit</button> ' +
                '<button class="btn btn-sm btn-danger worker-delete-btn">Slet</button>',
            orderable:false
        });
        return columns;
    }





    this.getPresentList = async function (){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/readPresents", {shopID: _shopID}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    };
    this.getfieldSettings = async function (){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/getfieldSettings", {shopID: _shopID}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    };
    this.createWorker = async (data) => {
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/createWorker",data, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")

        });
    }

    this.updateWorker = async (data) => {
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/updateWorker",data, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")

        });
    }
    this.deleteWorker = async (data) => {
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/deleteWorker",data, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")

        });
    }

    this.getWorkerList = async function(){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/readWorkerList", {shopID: _shopID}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    }
    this.importWorkerList = async function(data) {

            let response = await self.saveWorkerList(data);



            if (response.status === "1") {
                $('#csvPreviewModal').modal('hide');
                alert("List er indlæst")
                window.location.reload();
            } else {
                alert("Fejl: " + response.message);
            }

    }


    this.saveWorkerList = async function(data) {
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/importWorkerList", {data:data,shopID: _shopID}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
        });
    }

    this.calculateAndUpdateSum = function() {

        if (_distributionType === "list") {
            const table = this.workerDataTable;
            if (!table) {
                console.error('Worker table not initialized');
                return;
            }

            const data = table.rows().data().toArray();
            const firstColumnSum = data.reduce((sum, row) => {
                // Antager at den første kolonne indeholder antallet
                const firstColumnValue = parseInt(row[Object.keys(row)[0]]) || 0;
                return sum + firstColumnValue;
            }, 0);

            $('#sum_fordeling').text(firstColumnSum);
        }
    }

    this.workerTable = (fieldSettings, combinedData) => {
        let self = this;
        let columns = this.getWorkerTableHeaderData(fieldSettings, combinedData);

        let data = combinedData.map(worker => {
            let rowData = {};
            columns.forEach(col => {
                if (col.data === 'Gave') {
                    rowData[col.data] = worker.present ?
                        `${worker.present.fullalias} - ${worker.present.model_name}`.trim() :
                        'Ingen gave valgt';
                } else if (col.data !== null && col.data !== undefined) {
                    rowData[col.data] = worker.attributes[col.data] ?
                        worker.attributes[col.data].value : '';
                }
            });
            rowData["user_id"] = worker.user_id;
            rowData["model_id"] = worker.present ? worker.present.model_id : '';
            rowData["present_id"] = worker.present ? worker.present.id : '';
            rowData["is_sync"] = worker.is_sync; // Add is_sync to rowData
            return rowData;
        });

        // Tilføj en custom sorteringsfunktion for 'Gave'-kolonnen
        $.fn.dataTable.ext.type.order['gift-pre'] = function (data) {
            // Antager at formatet er "alias - gavenavn"
            var parts = data.split('-');
            if (parts.length > 1) {
                var alias = parts[0].trim();
                return parseInt(alias) || alias;
            }
            return data;
        };

        var table = $('#workerTabel').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/da.json'
            },
            columns: columns.map(col => {
                if (col.data === 'Gave') {
                    return {
                        ...col,
                        type: 'gift'
                    };
                }
                return col;
            }),
            data: data,
            responsive: true,
            autoWidth: false,
            createdRow: function(row, data, dataIndex) {
                $(row).attr('data-user-id', data.user_id);
                $(row).attr('model-id', data.model_id);
                $(row).attr('present-id', data.present_id);
                $(row).attr('is-sync', data.is_sync); // Add is_sync as attribute
            },
            columnDefs: [
                {
                    targets: '_all',
                    type: 'natural'
                },
                {
                    targets: -1, // Last column
                    render: function(data, type, row) {
                        if (row.is_sync === 1) {
                            return '<span class="text-success">Overført</span>';
                        } else {
                            return '<button class="btn btn-sm btn-primary worker-edit-btn">Edit</button> ' +
                                '<button class="btn btn-sm btn-danger worker-delete-btn">Slet</button>';
                        }
                    }
                }
            ]
        });

        self.workerDataTable = table;
        $('#gift-tab').on('shown.bs.tab', function (e) {
            self.generateDistributionList();
        });
        this.calculateAndUpdateSum();
            $('#opretWorker').on('click', async function() {

                if (self.paperSettings.isEditable === "false" && isAdmin==false) {
                    return;
                }
            let user_id = self.generateRandomString(20);
            let rowData = {}
            var formData = {
                shop_id: _shopId,
                user_id: user_id
            };
            var attrData = {};
            var presentData = {};

            $('.newWorker').each(function() {
                var $field = $(this);
                var id = $field.data('id');
                var value = $field.val().trim();

                if (value !== '') {
                    let key = self.nameIdArray[id];
                    rowData[key] = value;
                    attrData[id] = value;
                }
            });

            let $presentSelect = $('#presentsOptions');
            let selectedValue = $presentSelect.val();
            let selectedPresentId = $presentSelect.find('option:selected').attr('presentid');

            if (selectedValue && selectedValue !== '') {
                presentData['model_id'] = selectedValue;
            }
            if (selectedPresentId && selectedPresentId !== '') {
                presentData['presentid'] = selectedPresentId;
            }

            formData["attr"] = attrData;
            formData["present"] = presentData;
            rowData.Gave = $("#presentsOptions option:selected").text();
            rowData.model_id = presentData['model_id']
            rowData.present_id = presentData['presentid']
            rowData.user_id = user_id;

            let worker = await self.createWorker(formData);

                // Forbered data til at tilføje til tabellen



                // Tilføj den nye række til tabellen
                table.row.add(rowData).draw();

                // Ryd input felter
                $('.newWorker').val('');
                $('#presentsOptions').val('');
                self.calculateAndUpdateSum();

        });
        $('#workerTabel tbody').on('click', '.worker-edit-btn', function() {
            if (self.paperSettings.isEditable === "false" && isAdmin==false) {
                return;
            }
            var row = $(this).closest('tr');
            var rowData = table.row(row).data();
            openEditModal(rowData, fieldSettings);
        });

        async function  openEditModal(rowData, fieldSettings) {
            // Create modal HTML
            var modalHTML = `
            <div class="modal fade" id="editWorkerModal" tabindex="-1" aria-labelledby="editWorkerModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editWorkerModalLabel">Rediger Medarbejder</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editWorkerForm">
                                ${fieldSettings.data.map(field => {
                if (field.attributes.is_list == 0) {
                    return `
                                            <div class="mb-3">
                                                <label for="${field.attributes.name}" class="form-label">${field.attributes.name}</label>
                                                <input type="text" class="form-control" id="${field.attributes.name}" name="${field.attributes.name}" value="${rowData[field.attributes.name] || ''}">
                                            </div>
                                        `;
                } else {
                    const options = field.attributes.list_data.split('\n').map(item =>
                        `<option value="${item}" ${item === rowData[field.attributes.name] ? 'selected' : ''}>${item}</option>`
                    ).join('');
                    return `
                                            <div class="mb-3">
                                                <label for="${field.attributes.name}" class="form-label">${field.attributes.name}</label>
                                                <select class="form-select" id="${field.attributes.name}" name="${field.attributes.name}">
                                                    <option value="">Vælg ${field.attributes.name}</option>
                                                    ${options}
                                                </select>
                                            </div>
                                        `;
                }
            }).join('')}
                                <div class="mb-3">
                                    <label for="gave" class="form-label">Gave</label>
                                    <select class="form-select" id="gave" name="gave">
                                        ${$('#presentsOptions').html()}
                                    </select>
                                </div>
                                <input type="hidden" id="user_id" name="user_id" value="${rowData.user_id}">
                                <input type="hidden" id="model_id" name="model_id" value="${rowData.model_id}">
                                <input type="hidden" id="present_id" name="present_id" value="${rowData.present_id}">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                            <button type="button" class="btn btn-primary" id="saveWorkerChanges">Gem ændringer</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

            // Append modal to body if it doesn't exist
            if ($('#editWorkerModal').length === 0) {
                $('body').append(modalHTML);
            }

            // Set values in the form
            Object.keys(rowData).forEach(key => {
                $(`#editWorkerForm [name="${key}"]`).val(rowData[key]);
            });

            // Set the correct gift option as selected
            $(`#editWorkerForm #gave option[value="${rowData.model_id}"]`).prop('selected', true);

            // Show the modal
            var editModal = new bootstrap.Modal(document.getElementById('editWorkerModal'));
            editModal.show();

            // Save changes button click handler
            // Save changes button click handler
            $('#saveWorkerChanges').off('click').on('click', async function() {
                var updatedData = {};
                $('#editWorkerForm').serializeArray().forEach(item => {
                    updatedData[item.name] = item.value;
                });

                // Hent gave-data
                var selectedGift = $('#editWorkerForm #gave option:selected');
                updatedData['Gave'] = selectedGift.text();
                updatedData['model_id'] = selectedGift.val();
                updatedData['present_id'] = selectedGift.attr('presentid');

                // Opdater row data og attributter
                var row = table.row(`[data-user-id="${updatedData.user_id}"]`);
                var rowNode = row.node();

                // Opdater synlige data
                row.data(updatedData).draw();

                // Opdater row attributter
                $(rowNode).attr('data-user-id', updatedData.user_id);
                $(rowNode).attr('model-id', updatedData.model_id);
                $(rowNode).attr('present-id', updatedData.present_id);

                // Forbered data til at sende til serveren
                var saveData = {
                    shop_id: _shopID,
                    user_id: updatedData.user_id,
                    attr: matchObjects(self.nametoIdArray, updatedData),
                    model_id: updatedData.model_id,
                    present_id: updatedData.present_id
                };
                editModal.hide();

                await self.updateWorker(saveData);
                self.calculateAndUpdateSum();


                // Luk modalen

            });
        }


        $('#workerTabel tbody').on('click', '.worker-delete-btn', async function() {
            if (self.paperSettings.isEditable === "false" && isAdmin==false) {
                return;
            }
            var row = $(this).closest('tr');
            var user_id = row.attr('data-user-id');

            if (confirm('Are you sure you want to delete this worker?')) {
                try {
                    await self.deleteWorker({
                        shop_id: _shopID,
                        user_id: user_id
                    });
                    table.row(row).remove().draw();
                    self.calculateAndUpdateSum();
                } catch (error) {
                    alert('Error deleting worker: ' + error);
                }
            }
        });
        function matchObjects(obj1, obj2) {
            const result = {};

            for (const [key, id] of Object.entries(obj1)) {
                if (key in obj2) {
                    result[id] = obj2[key];
                }
            }
            return result;
        }

        // Add modal for CSV preview
        $('#paper-reg').append(`
            <div class="modal fade" id="csvPreviewModal" tabindex="-1" aria-labelledby="csvPreviewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="csvPreviewModalLabel">CSV Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table id="csvPreviewTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr></tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                            <button type="button" class="btn btn-primary" id="importCSVData">Importer Data</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    };


    this.generateRandomString = (length) => {
        var result = '';
        var characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters . length;
        for (var i = 0; i < length; i++) {
            result += characters . charAt(Math . floor(Math . random() * charactersLength));
        }
        return result;
    }

}
function renameField(obj, oldName, newName) {
    // Dybdekopi af objektet for at undgå at ændre det originale objekt
    const newObj = JSON.parse(JSON.stringify(obj));

    // Gennemgå data-arrayet
    newObj.data = newObj.data.map(item => {
        if (item.attributes && item.attributes.name === oldName) {
            return {
                ...item,
                attributes: {
                    ...item.attributes,
                    name: newName
                }
            };
        }
        return item;
    });

    return newObj;
}
function PaperPortalTemplate() {
    let self = this;

    this.layout = (fieldSettings,presentList) => {
 
        let menu = "";
        let presentDropdown = "";
        let workerTableHeader = [];
        const presents = presentList.data.map(item => item.attributes);

        // Sorter presents baseret på fullalias
        presents.sort((a, b) => {
            const aliasA = parseInt(a.fullalias) || a.fullalias;
            const aliasB = parseInt(b.fullalias) || b.fullalias;

            if (typeof aliasA === 'number' && typeof aliasB === 'number') {
                return aliasA - aliasB;
            }

            return String(aliasA).localeCompare(String(aliasB), undefined, {numeric: true, sensitivity: 'base'});
        });

        let presentsOptions = presents.map(attr => `<option value="${attr.model_id}" presentid="${attr.present_id}">${attr.fullalias} - ${attr.model_name}${attr.model_no}</option>`).join('');
        presentsOptions = `<option value="">Vælg gave</option>` + presentsOptions;

        const attributes = fieldSettings.data.map(item => item.attributes);
        attributes.forEach(attr => {
            workerTableHeader.push("<th>" + attr.name + "</th>");
            if (attr.is_list == 0) {
                menu += `<div class="col-md-3 ">
                    <div class="form-floating">
                        <input data-id="${attr.id}" type="text" id="${attr.name}_workerlist" class="form-control newWorker" placeholder="${attr.name}">
                        <label for="${attr.name}_workerlist">${attr.name}</label>
                    </div>
                </div>`;
            } else {
                if (attr.list_data) {
                    const listItems = attr.list_data.split('\n');
                    const options = listItems.map(item => `<option value="${item}">${item}</option>`).join('');

                    menu += `<div class="col-md"><select class="form-select newWorker" data-id="${attr.id}" name="${attr.name}" id="${attr.name.toLowerCase()}">
                        <option value="">Vælg ${attr.name}</option>${options}
                    </select></div>`;
                }
            }
        });
        workerTableHeader.push("<th>Gave</th> <th>Handling</th>");

        return `<div class="container mt-5">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="worker-tab" data-bs-toggle="tab" data-bs-target="#worker" type="button" role="tab" aria-controls="worker" aria-selected="true">Indtast gavevalg </button>
                </li>
                <li class="nav-item" role="presentation">
                    <div class="d-flex align-items-center">
                        <button class="nav-link" id="gift-tab" data-bs-toggle="tab" data-bs-target="#gift" type="button" role="tab" aria-controls="gift" aria-selected="false">Indtast fordelingsliste</button>
                        <div id="sum_fordeling_container" style="display: none;" class="ms-3">
                            <span class="me-2">Total antal gaver:</span><span id="sum_fordeling">0</span>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="worker" role="tabpanel" aria-labelledby="worker-tab">
                    <br>
                        <div class="row mt-3 form-row-small workerNewPanel">
                            ${menu}
                        <div class="col-md">
                        <select class="form-select newWorkerPresent"  name="presentsOptions" id="presentsOptions">
                            ${presentsOptions} 
                        </select>
                        </div>
                        <div class="col-md">
                            <button id="opretWorker" class="btn btn-primary">Opret</button>
                        </div>
                    </div>
                    <hr>
              <div class="table-responsive">
    <table id="workerTabel" class="table table-striped">
        <thead>
            <tr>
              ${workerTableHeader.join("")}
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
                </div>
                
                
                
                
                <div class="tab-pane fade" id="gift" role="tabpanel" aria-labelledby="gift-tab">
                    <div id="distributionListContainer"></div>
                </div>
                
                
                
            </div>
        </div>`;
    }
}