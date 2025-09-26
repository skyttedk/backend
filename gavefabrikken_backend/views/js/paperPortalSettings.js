/*

{
  "isEnabled": true,
  "isEditable": true,
  "entityType": "list" // or "worker"

}


 */

function PaperPortalSettings(isAdmin) {
    let self = this;
    let template = {};
    let jobQueue = {};
    let allfieldSettings = {};
    this.init = async function () {
        this.template = new PaperSettingsTemplate();
        let settings = await self.readSettings();
        this.allfieldSettings = await self.getAllfieldSettings()
        if (isAdmin) {
            $("#paper-settings").html(this.template.settings());
            this.setupEventHandlers();
            this.populateForm(settings);
            this.addSpinner();
            this.addToastContainer();
        } 

    }

    this.setupEventHandlers = function () {
        let self = this;
        // Add change event listeners to all relevant form elements
        $('#arbejdeMulighed, #kiggeAdgang, input[name="listetype"]').on('change', function () {
            self.saveSettings();
        });

        $("#registreDataInGaveSystem").on('click', async function () {
            let input = prompt("Skriv 'sync' for at starte synkroniseringen");
            if (input.toLowerCase() !== "sync") {
                self.showToast('Fejl du skrev ikke sync', 'danger');
                return
            }
            self.showToast('Sync start', 'success');

            $('#registreDataInGaveSystem').prop('disabled', true); // Disable the button
            $('#spinner').show(); // Show the spinner

            try {
                await self.importHandler();
                location.reload();

            } catch (error) {
                console.error('Import failed:', error);
                self.showToast('Der opstod en fejl under importen. Prøv igen senere.', 'danger');
            } finally {
                $('#registreDataInGaveSystem').prop('disabled', false); // Re-enable the button
                $('#spinner').hide(); // Hide the spinner
                location.reload();

            }
        });

    }
    this.addSpinner = function() {
        // Add spinner HTML after the button
        $('#registreDataInGaveSystem').after('<div id="spinner" class="spinner-border text-primary mt-3" role="status" style="display: none;"><span class="visually-hidden">Loading...</span></div>');
    }
    this.addToastContainer = function() {
        // Add toast container to the body
        $('body').append(`
            <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1050;"></div>
        `);
    }

    this.showToast = function(message, type = 'success') {
        const toast = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        $('#toastContainer').append(toast);
        $('.toast').toast('show');

        // Remove the toast after it's hidden
        $('.toast').on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }

    this.importHandler = async function() {
        let jobQueueResponse = await this.getWorkerList();
        console.log('Job kø status:', jobQueueResponse.status);

        if (jobQueueResponse.status !== "1") {
            console.error('Fejl i at hente job kø:', jobQueueResponse.message);
            return;
        }

        let jobQueue = jobQueueResponse.data;
        console.log(`Antal jobs i køen: ${jobQueue.length}`);

        // Find the ID of the 'Navn' attribute
        let navnAttributeId = this.findNavnAttributeId();

        let results = [];
        for (let job of jobQueue) {
            try {
                await this.updateGavevalgImport(job.user_id);

                let result;
                if (_distributionType === "list" && navnAttributeId) {
                    
                    // For "list" type, use 'antal' from the 'Navn' attribute
                    const antal = parseInt(job.attributes[navnAttributeId] || "1", 10);
                    for (let i = 0; i < antal; i++) {
                        result = await this.processJob(job);
                        let gavevalgUserId = extractShopuserId(result);
                        let resultGaveCreate = await this.createGift(
                            job.shop_id,
                            gavevalgUserId,
                            job.order.present_id,
                            "papirimport",
                            job.order.model_id
                        );
                        results.push({ success: true, job: job, result: result });
                    }
                } else {
                    // For "worker" type or any other type, process individually
                    result = await this.processJob(job);
                    let gavevalgUserId = extractShopuserId(result);
                    let resultGaveCreate = await this.createGift(
                        job.shop_id,
                        gavevalgUserId,
                        job.order.present_id,
                        "papirimport",
                        job.order.model_id
                    );
                    results.push({ success: true, job: job, result: result });
                }

                console.log(`Job completed for user ${job.user_id}:`, result);
            } catch (error) {
                results.push({ success: false, job: job, error: error });
                console.error(`Fejl i job for user ${job.user_id}:`, error);
            }
        }

        console.log('Alle jobs er fuldført');
        return results;
    };

    this.findNavnAttributeId = function() {
        if (!this.allfieldSettings || !this.allfieldSettings.data) {
            console.error('allfieldSettings is not properly initialized');
            return null;
        }

        for (let field of this.allfieldSettings.data) {
            if (field.attributes && field.attributes.name === "Navn") {
                return field.attributes.id.toString();
            }
        }

        console.error('Navn attribute not found in allfieldSettings');
        return null;
    };

    this.updateGavevalgImport = async function(userId) {


        const formdata = {
            userId: userId,
        };

        try {
            const response = await $.ajax({
                url: BASE_AJAX_URL + 'paperPortal/updateSyncStatus',
                type: 'POST',
                data: formdata,
                dataType: 'json'
            });

            console.log('Gavevalg import updated successfully:', response);
            return response;
        } catch (error) {
            console.error('Error updating gavevalg import:', error);
            throw error;
        }
    };

    this.createGift = async function(shopId, userId, presentsId, modelName, modelId) {
        console.log('Creating gift:', { shopId, userId, presentsId, modelName, modelId });

        const formdata = {
            shopId: shopId,
            userId: userId,
            presentsId: presentsId,
            modelName: modelName,
            modelId: modelId,
            model: '',
            model_id: modelId,
            skip_email: 1
        };

        try {
            const response = await $.ajax({
                url: 'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=order/changePresent',
                type: 'POST',
                data: formdata,
                dataType: 'json'
            });

            console.log('Gift created successfully:', response);
            return response;
        } catch (error) {
            console.error('Error creating gift:', error);
            throw error;
        }
    };





    this.processJob = async function(job) {
        console.log('Processing job:', job);

        const attributeSettings = this.allfieldSettings;

        // Create a map of attribute settings for easy lookup
        const attributeSettingsMap = new Map(
            attributeSettings.data.map(item => [item.attributes.id.toString(), item.attributes])
        );

        // Process attributes
        let attributes = attributeSettingsMap.size > 0 ?
            Array.from(attributeSettingsMap.entries()).map(([id, settings]) => {
                let value = job.attributes[id] || "";

                // If value is empty, generate a default value
                if (!value) {
                    if (settings.name === "Gaveklubben tilmelding") {
                        value = "nej";
                    } else {
                        value = this.generateRandomString();
                    }
                }

                return {
                    id: id,
                    value: value
                };
            }) :
            Object.entries(job.attributes).map(([id, value]) => ({
                id: id,
                value: value || this.generateRandomString()
            }));

        // Log the attributes for debugging
        console.log("Processed attributes:", attributes);

        // Construct the formdata object
        let formdata = {
            "attributes_": JSON.stringify(attributes),
            "data": JSON.stringify({
                "userId": null,
                "shopId": job.shop_id,
                "companyId": _companyID
            })
        };

        try {
            const response = await $.ajax({
                url: BASE_AJAX_URL + "shop/addShopUser",
                type: 'POST',
                data: formdata,
                dataType: 'json'
            });

            return response;
        } catch (error) {
            console.error("Error processing job:", error);
            throw error;  // Re-throw the error to be caught in importHandler
        }
    };
    this.generateRandomString = function(length = 20) {
        const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        return Array.from({length}, () => characters.charAt(Math.floor(Math.random() * characters.length))).join('');
    };




    this.getWorkerList = async function(){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/readWorkerListNoSync", {shopID: _shopID}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    }
    this.getAllfieldSettings = async function(){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"paperPortal/getAllfieldSettings", {shopID: _shopID}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    }


    this.populateForm = function (settingsData) {
        if (settingsData.status === "1" && settingsData.data && settingsData.data.paper_settings) {
            const paperSettings = JSON.parse(settingsData.data.paper_settings);

            // Populate 'Aktivere for kunden'
            $('#arbejdeMulighed').prop('checked', paperSettings.isEnabled === "true");

            // Populate 'Kunde kan redigere ellers kun kigge adgang'
            $('#kiggeAdgang').prop('checked', paperSettings.isEditable === "true");

            // Populate 'Listetype'
            if (paperSettings.entityType === "list") {
                $('#kunFordelingsliste').prop('checked', true);
            } else {
                $('#medarbejderListe').prop('checked', true);
            }
        }
    }
    this.readSettings = function () {
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL + "paperPortal/readSettings", {shopID: _shopID}, function (returnMsg, textStatus) {
                resolve(returnMsg);
            }, "json")

        });
    }

    this.saveSettings = function () {
        const settings = {
            isEnabled: $('#arbejdeMulighed').is(':checked'),
            isEditable: $('#kiggeAdgang').is(':checked'),
            entityType: $('input[name="listetype"]:checked').val() === 'kunFordelingsliste' ? 'list' : 'worker',
            isImported: 0,
            shopID: _shopID
        };

        // Log the settings (for debugging purposes)
        // Send the updated settings to the backend

        var jqxhr = $.post(BASE_AJAX_URL + "paperPortal/updateSettings", settings, function (returnMsg, textStatus) {
            location.reload();
        }, "json")
    }
}


function extractShopuserId(response) {
    try {
        // Parse the JSON string if it's not already an object
        const data = typeof response === 'string' ? JSON.parse(response) : response;

        // Navigate through the object structure
        const shopuserId = data.data.shopuser[0].id;

        // Check if the ID is present
        if (shopuserId) {
            console.log("Extracted shopuser ID:", shopuserId);
            return shopuserId;
        } else {
            console.error("Shopuser ID not found in the response");
            return null;
        }
    } catch (error) {
        console.error("Error extracting shopuser ID:", error.message);
        return null;
    }
}
function PaperSettingsTemplate() {
    let self = this;

    this.settings = () => {
        return `
        <style>
            .wrap-url {
                word-break: break-word;
                overflow-wrap: break-word;
            }
        </style>
        <a href="javascript:void(0);" onclick="history.back();" class="btn btn-outline-primary" style="position: absolute; top: 90px; left: 10px;">
            <i class="bi bi-arrow-left"></i> Tilbage
        </a>
        <div class="container mt-5">
            <div id="kundeForm">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Info</h5>
                                <div class="mt-3">
                                    <label class="wrap-url">Kundelink: <a href="https://system.gavefabrikken.dk/portal" target="_blank">https://system.gavefabrikken.dk/portal</a></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Aktivere for kunden</h5>
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" id="arbejdeMulighed">
                                    <label class="form-check-label" for="arbejdeMulighed">Aktivér</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Kunde kan redigere ellers kun kigge adgang</h5>
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" id="kiggeAdgang">
                                    <label class="form-check-label" for="kiggeAdgang">Tillad</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card custom-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Fordelingstype</h5>
                                <div class="mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listetype" id="kunFordelingsliste" value="kunFordelingsliste" checked>
                                        <label class="form-check-label" for="kunFordelingsliste">
                                            Fordeling pr. gaveantal
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="listetype" id="medarbejderListe" value="medarbejderListe">
                                        <label class="form-check-label" for="medarbejderListe">
                                            Fordeling pr. medarbejdernavn
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button id="registreDataInGaveSystem" class="btn btn-custom btn-lg">Registrer kundedata i gavevalgsystemet</button>
                </div>
            </div>
        </div><br>`;
    };
}