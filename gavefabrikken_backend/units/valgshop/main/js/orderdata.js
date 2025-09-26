
var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/main/";
var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";
import Base from 'https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/main/js/base.js';
import tp_orderdata from '../tp/orderdata.tp.js';
import DocumentManager from './documentmanager.js';
import AutoSaveManager from './autosavemanager.js?12';
var shop;

export default class Orderdata extends Base {
    constructor() {
        super();
        this.autoSaveManager = null; // Tilføj denne linje
    }
    async init(shopid,showMode,admin,lang=1){
        this.lang = lang;
        this.dontSave = false;
        this.state = 0;
        this.admin = admin;
        this.shopid = shopid;
        this.initLayout(showMode);
        this.eventhandler();
        this.initCompanySearch();
        let OrderOpenClose = await this.getOrderOpenCloseChopEvents();
        this.orderOpenCloseChopEventsStart = this.procesOpenCloseData(OrderOpenClose.data.start);
        this.orderOpenCloseChopEventsEnd = this.procesOpenCloseData(OrderOpenClose.data.end);

        this.orderOpenCloseChopSetupDatepicker('#orderOpenCloseChopStartDate', '#orderOpenCloseChopStartDateInfo', this.orderOpenCloseChopEventsStart);
        this.orderOpenCloseChopSetupDatepicker('#orderOpenCloseChopEndDate', '#orderOpenCloseChopEndDateInfo', this.orderOpenCloseChopEventsEnd);

        let deliveryDatesData = await this.getdeliveryDates();
        this.deliveryDates = this.procesOpenCloseData(deliveryDatesData.data);
        //  this.orderOpenCloseChopSetupDatepicker('#flex_start_delivery_date', '#flex_start_delivery_dateInfo', this.deliveryDates);

        // TILFØJ DENNE LINJE - Setup samme datepicker for delivery_date som flex_start_delivery_date
        this.orderOpenCloseChopSetupDatepicker('#delivery_date', '#delivery_dateInfo', this.deliveryDates);

        this.initDocumentManagers();
        this.initAutoSave();
    }
    initDocumentManagers() {
        // Payment special documents
        if (document.getElementById('payment_special_documents')) {
            this.paymentSpecialDocs = new DocumentManager();
            this.paymentSpecialDocs.init(
                this.shopid,
                'payment_special',
                'payment_special_documents',
                {
                    category: 'invoice',
                    onUpload: (data) => {
                        console.log('Payment document uploaded:', data);
                    },
                    onDelete: (docId) => {
                        console.log('Payment document deleted:', docId);
                    }
                }
            );
        }

        // Handling special documents - NY
        if (document.getElementById('handling_special_documents')) {
            this.handlingSpecialDocs = new DocumentManager();
            this.handlingSpecialDocs.init(
                this.shopid,
                'handling_special',
                'handling_special_documents',
                {
                    category: 'handling',
                    onUpload: (data) => {
                        console.log('Handling document uploaded:', data);
                    },
                    onDelete: (docId) => {
                        console.log('Handling document deleted:', docId);
                    }
                }
            );
        }
    }
    getOrderOpenCloseChopEvents() {
        return new Promise(resolve => {
            $.get( APPR_AJAX_URL+"orderOpenCloseChopEvents&lang="+this.lang, function(res ) {
                resolve(res);
            }, "json")
        })
    }
    addDeliveryAddress() {
        const addressName = $('#new_delivery_address').val().trim();
        const addressDate = $('#new_delivery_date').val();

        if (!addressName) {
            alert('Indtast venligst en leveringsadresse');
            return;
        }

        if (!addressDate) {
            alert('Vælg venligst en leveringsdato');
            return;
        }

        // Generer unikt ID for adresse
        const addressId = 'delivery_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        // Opret adresse HTML
        const addressHtml = `
        <div class="delivery-address-item border rounded p-3 mb-2" data-address-id="${addressId}">
            <div class="row align-items-center">
                <div class="col-8">
                    <strong>${this.escapeHtml(addressName)}</strong>
                    <br>
                    <small class="text-muted">Leveringsdato: ${this.formatDisplayDate(addressDate)}</small>
                </div>
                <div class="col-4 text-right">
                    <button type="button" class="btn btn-sm btn-danger remove-delivery-address-btn" data-address-id="${addressId}">
                        <i class="fa fa-trash"></i> Fjern
                    </button>
                </div>
            </div>
            <input type="hidden" class="delivery-address-name" value="${this.escapeHtml(addressName)}">
            <input type="hidden" class="delivery-address-date" value="${addressDate}">
        </div>
    `;

        // Tilføj til listen
        $('#delivery_addresses_list').append(addressHtml);

        // Ryd input felter
        $('#new_delivery_address').val('');
        $('#new_delivery_date').val('');

        // Fokuser på navn felt for hurtig tilføjelse af flere
        $('#new_delivery_address').focus();
    }

// Hjælpefunktion til at få alle leveringsadresser som JSON
    getMultipleDeliveries() {
        const deliveries = [];
        $('.delivery-address-item').each(function() {
            const name = $(this).find('.delivery-address-name').val();
            const date = $(this).find('.delivery-address-date').val();
            if (name && date) {
                deliveries.push({
                    name: name,
                    date: date
                });
            }
        });
        return deliveries;
    }

// Hjælpefunktion til at sætte leveringsadresser fra JSON
    setMultipleDeliveries(deliveriesJson) {
        if (!deliveriesJson) return;

        try {
            const deliveries = JSON.parse(deliveriesJson);
            $('#delivery_addresses_list').empty();

            deliveries.forEach(delivery => {
                if (delivery.name && delivery.date) {
                    const addressId = 'delivery_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    const addressHtml = `
                    <div class="delivery-address-item border rounded p-3 mb-2" data-address-id="${addressId}">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <strong>${this.escapeHtml(delivery.name)}</strong>
                                <br>
                                <small class="text-muted">Leveringsdato: ${this.formatDisplayDate(delivery.date)}</small>
                            </div>
                            <div class="col-4 text-right">
                                <button type="button" class="btn btn-sm btn-danger remove-delivery-address-btn" data-address-id="${addressId}">
                                    <i class="fa fa-trash"></i> Fjern
                                </button>
                            </div>
                        </div>
                        <input type="hidden" class="delivery-address-name" value="${this.escapeHtml(delivery.name)}">
                        <input type="hidden" class="delivery-address-date" value="${delivery.date}">
                    </div>
                `;
                    $('#delivery_addresses_list').append(addressHtml);
                }
            });
        } catch (e) {
            console.error('Fejl ved parsing af leveringsadresser:', e);
        }
    }
    getdeliveryDates() {
        return new Promise(resolve => {
            $.get( APPR_AJAX_URL+"getDeliveryDates&lang="+this.lang, function(res ) {
                resolve(res);
            }, "json")
        })
    }
    procesOpenCloseData(data){
        let self = this;
        let returData = {};

        $.each(data, function(key, value) {
            if(self.lang == 1 || self.lang == 5 ) {
                let color = "green";
                let info = "Ingen problemer dk \n "


                if (value.attributes.count_per_day > 30) {
                    color = "yellow";
                    info = "1Der ligger en del shops på denne dato, men det går an \n ";

                }
                if (value.attributes.count_per_day > 39) {
                    color = "red";
                    info = "Hvis du vælger denne dato skal den godkendes af Susanne.  Hvis du gemmer med denne dato vil der bliver sent en godkendelsesmail til Susanne.\n ";
                    self.dontSave = true;
                }

                let obj = {
                    info: info,
                    shops: value.attributes.count_per_day,
                    gifts: value.attributes.gifts_per_day || 'n/a',
                    color: color
                };

                // Brug den formaterede dato som nøglen
                let formattedDate = self.formatDate(value.attributes.date);
                returData[formattedDate] = obj;
            }
            if(self.lang == 4) {
                let color = "green";
                let info = "Ingen problemer for norge \n "


                if (value.attributes.count_per_day > 30) {
                    color = "yellow";
                    info = "1Der ligger en del shops på denne dato, men det går an \n ";

                }
                if (value.attributes.count_per_day > 39) {
                    color = "red";
                    info = "Hvis du vælger denne dato skal den godkendes af din leder.  Hvis du gemmer med denne dato vil der bliver sent en godkendelsesmail til Susanne.\n ";
                    self.dontSave = true;
                }

                let obj = {
                    info: info,
                    shops: value.attributes.count_per_day,
                    gifts: value.attributes.gifts_per_day || 'n/a',
                    color: color
                };

                // Brug den formaterede dato som nøglen
                let formattedDate = self.formatDate(value.attributes.date);
                returData[formattedDate] = obj;
            }
        });
        return returData;

    }
    formatDate(dateStr) {
        let date = new Date(dateStr);
        let year = date.getFullYear();
        let month = (date.getMonth() + 1).toString().padStart(2, '0');
        let day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    salespersonCode() {
        return new Promise(resolve => {

            $.get( APPR_AJAX_URL+"getSalespersonCodeSold&lang="+this.lang, function(res ) {
                resolve(res);
            }, "json")
        })
    }

    loadShopMetadata() {
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"getshopmetadata",{shop_id:this.shopid}, function(res ) {
                resolve(res);
            }, "json")
        })
    }

    postShopMetadata(data) {
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"saveShopMetadata/"+this.shopid, data ,function(res ) {
                resolve(res);
            }, "json")
        })
    }
    getApprovalState() {
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"getApprovalState/"+this.shopid, {shop_id:this.shopid} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }

    eventhandler(){
        let self = this;

        $('#multiple_deliveries').change(function() {
            if ($(this).val() == '1') {
                $('#multiple_deliveries_details').show();
            } else {
                $('#multiple_deliveries_details').hide();
                // Ryd alle adresser når "Nej" vælges
                $('#delivery_addresses_list').empty();
            }
        });

        $('#add_delivery_address_btn').click(function() {
            self.addDeliveryAddress();
        });

// Enter key support for tilføjelse af adresse
        $('#new_delivery_address, #new_delivery_date').keypress(function(e) {
            if (e.which == 13) { // Enter key
                self.addDeliveryAddress();
            }
        });

// Event delegation for slet knapper (da de oprettes dynamisk)
        $(document).on('click', '.remove-delivery-address-btn', function() {
            const addressId = $(this).data('address-id');
            $(`.delivery-address-item[data-address-id="${addressId}"]`).remove();
        });




        $('#deliverydate_receipt_toggle').change(function() {
            if ($(this).val() === '1') {
                // Vis dato, skjul tekst
                $('#deliverydate_receipt_date_field').show();
                $('#deliverydate_receipt_text_field').hide();
                // Ryd tekstfelt når dato vælges
                $('#deliverydate_receipt_custom_text').val('');
            } else {
                // Vis tekst, skjul dato
                $('#deliverydate_receipt_date_field').hide();
                $('#deliverydate_receipt_text_field').show();
                // Ryd datofelt når tekst vælges
                $('#deliverydate_receipt').val('');
            }
        });


        $('#orderDataForm').on('submit', function(e) {
            // Kun tillad submit hvis det er gem knappen
            if (!$(e.originalEvent.submitter).hasClass('save_button')) {
                e.preventDefault();
                return false;
            }
        });

        $('#add_budget_btn').click(function() {
            const amount = $('#new_budget_amount').val();

            if (!amount || parseFloat(amount) <= 0) {
                alert('Indtast venligst et gyldigt budget beløb');
                return;
            }

            // Generer unikt ID for budget item
            const budgetId = 'budget_' + Date.now();

            // Opret budget item HTML
            const budgetHtml = `
        <div class="budget-item border rounded p-2 mb-2" data-budget-id="${budgetId}">
            <div class="row align-items-center">
                <div class="col-10">
                    <strong>${parseFloat(amount).toFixed(2)} </strong>
                </div>
                <div class="col-2 text-right">
                    <button type="button" class="btn btn-sm btn-danger remove-budget-btn" data-budget-id="${budgetId}">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" class="budget-amount" value="${amount}">
        </div>
    `;
            $(document).on('click', '.remove-budget-btn', function() {
                const budgetId = $(this).data('budget-id');
                $(`.budget-item[data-budget-id="${budgetId}"]`).remove();
            });

// Enter key support for tilføjelse af budget
            $('#new_budget_amount').keypress(function(e) {
                if (e.which == 13) { // Enter key
                    $('#add_budget_btn').click();
                }
            });





            // Tilføj til listen
            $('#budgets_list').append(budgetHtml);

            // Ryd input felt
            $('#new_budget_amount').val('');
        });


// Flere budgetter toggle
        $('#multiple_budgets_toggle').change(function() {
            if ($(this).val() === '1') {
                // Vis multiple budgets, skjul single budget
                $('#single_budget_field').hide();
                $('#multiple_budgets_field').show();
                // Ryd single budget felt
                $('#budget').val('');
                $('#flex_budget').prop('checked', false);
            } else {
                // Vis single budget, skjul multiple budgets
                $('#single_budget_field').show();
                $('#multiple_budgets_field').hide();
                // Ryd multiple budgets
                $('#budgets_list').empty();
                $('#new_budget_amount').val('');
            }
        });


        $('#delivery_type').change(function() {
            if ($(this).val() === 'fast') {
                $('#fast_delivery_group').show();
                $('#flex_delivery_group').hide();

                // Clear flex dates when switching to fast
                $('#flex_start_delivery_date').val('');
                $('#flex_end_delivery_date').val('');

                // Clear any info divs for flex dates
                $('#flex_start_delivery_dateInfo').html('');
            } else {
                $('#fast_delivery_group').hide();
                $('#flex_delivery_group').show();

                // Clear fast date when switching to flexible
                $('#delivery_date').val('');

                // RETTELSE: Clear info div for delivery_date
                $('#delivery_dateInfo').html('');
            }
        });


// IMPORTANT: Trigger the change event to set initial visibility
// This should be called after all event handlers are set up
// You can add this at the end of your eventhandler() function

        $('#foreign_delivery').change(function() {
            if ($(this).val() == "1" || $(this).val() == "2") {
                $('#foreign_countries').show();
            } else {
                $('#foreign_countries').hide();
            }
        });

        $('#is_foreign').on('change', function() {
            if (this.value == '1') {
                $('#group_is_foreign').show();
            } else {
                $('#group_is_foreign').hide();
            }
        });

        $('#plant_tree').on('change', function() {
            if (this.value == '1') {
                $('#plant_tree_price_group').show();
            } else {
                $('#plant_tree_price_group').hide();
            }
        });



        $('#dot_use').change(function() {
            if ($(this).val() == "1") {
                $('#dot_fields').show();
            } else {
                $('#dot_fields').hide();
            }
        });
        $('#carryup_use').change(function() {
            if ($(this).val() == "1") {
                $('#carryup_fields').show();
            } else {
                $('#carryup_fields').hide();
            }
        });
        $('#autogave_use').change(function() {
            if ($(this).val() == "1") {
                $('#autogave_fields').show();
            } else {
                $('#autogave_fields').hide();
            }
        });
        $('#loan_use').change(function() {
            if ($(this).val() == "1") {
                $('#loan_fields').show();
            } else {
                $('#loan_fields').hide();
            }
        });
        $('#discount_option').change(function() {
            if ($(this).val() == '1') {
                $('#discount_value_group').show();
            } else {
                $('#discount_value_group').hide();
            }
        });
        $('#payment_special').change(function() {
            if ($(this).val() == '1') {
                $('#payment_special_note_group').show();
            } else {
                $('#payment_special_note_group').hide();
            }
        });
        $('#economy_info').change(function() {
            if ($(this).val() == '1') {
                $('#economy_info_note_group').show();
            } else {
                $('#economy_info_note_group').hide();

            }
        });

        $('#order_info').change(function() {
            if ($(this).val() == '1') {
                $('#order_info_note_group').show();
            } else {
                $('#order_info_note_group').hide();
            }
        });
        /*
                $('#invoice_fee').change(function() {
                    if ($(this).val() == '1') {
                        $('#invoice_fee_value_group').show();
                    } else {
                        $('#invoice_fee_value_group').hide();
                    }
                });
        */
        $('#deliveryprice_option').on('change', function() {
            if (this.value == '1') {
                $('#deliveryprice_amount_group').show();
            } else {
                $('#deliveryprice_amount_group').hide();
            }
        });



        // Hvis du vil skjule gruppen fra starten, hvis værdien er "Nej"
        if ($('#deliveryprice_option').val() == '0') {
            $('#deliveryprice_amount_group').hide();
        }
        /*
                $('#resetPrivateReturType').click(function() {
                    event.preventDefault();
                    $("#privateReturVirksomhedAdress").hide()
                    $('input[name="privateReturType"]').prop('checked', false);
                });

         */
        $('#privateReturVirksomhedAdress').click(function() {
            event.preventDefault();
            self.privateReturVirksomhedAdress();

        });

        $('input[name="privateReturType"]').change(function(event) {
            var selectedValue = $(this).val();
            $("#privateReturVirksomhedAdress").hide()
            if (selectedValue === "virksomhed") {
                $("#privateReturVirksomhedAdress").show()
            }

        });


        $('#present_papercard').on('change', function() {

            if (this.value == '1') {
                $('#present_papercard_price_group').show();
            } else {
                $('#present_papercard_price_group').hide();
            }
        });

        $('#present_wrap').on('change', function() {
            if (this.value == '1') {
                $('#present_wrap_price_group').show();
            } else {
                $('#present_wrap_price_group').hide();
            }
        });

        $('#present_nametag').on('change', function() {
            if (this.value == '1') {
                $('#present_nametag_price_group').show();
            } else {
                $('#present_nametag_price_group').hide();
            }
        });

        $('#private_delivery').on('change', function() {
            if (this.value == '1') {
                $('#private_delivery_price_group').show();
            } else {
                $('#private_delivery_price_group').hide();
            }
        });


        $('#handling_special').on('change', function() {
            if (this.value == '1') {
                $('#group_handling_special').show();
                // Geninitialisér document manager hvis den ikke er initialiseret
                if (self.handlingSpecialDocs && document.getElementById('handling_special_documents')) {
                    self.handlingSpecialDocs.loadDocuments();
                }
            } else {
                $('#group_handling_special').hide();
            }
        });


        // Skjul gruppen fra starten, hvis værdien er "Nej"
        if ($('#present_papercard').val() == '0') {
            $('#present_papercard_price_group').hide();
        }
        $('#language').change(function() {
            if ($(this).val() == '1') {
                $('#group_language').show();
            } else {
                $('#group_language').hide();
            }
        });

        $('#reminder_use').change(function() {
            if ($(this).val() == '1') {
                $('#group_reminder_use').show();
            } else {
                $('#group_reminder_use').hide();
            }
        });
        $('#payment_terms_proposed').change(function() {
            if(this.value ==0){
                $("#payment_terms").val("");
            } else {
                let selectedText = $(this).find('option:selected').text();
                $("#payment_terms").val(selectedText);
            }


        });



        $('.save_button').click(function() {
            // Reset autogem timer når brugeren manuelt gemmer
            if (self.autoSaveManager) {
                self.autoSaveManager.reset();
            }
            self.saveShopMetadata();
        });
        $('.order_approval').click(function() {
            // Reset autogem timer ved godkendelse
            if (self.autoSaveManager) {
                self.autoSaveManager.reset();
            }
            let c = confirm("Ønsker du at sende de indtastede ordredata til godkendelse?")
            if(c)
                self.doConfirmOrderApproval()
        });
        $(document).on('click', '.modal-backdrop', function() {
            $('#addressModal').modal('hide');
        });

    }

    /*

           $.post( APPR_AJAX_URL+"updateSold", {shop_id:shop,present_count:count} ,function(res ) {
                self.init(shop);
            }, "json")
     */
    validateFields(){

    }
    async privateReturVirksomhedAdress(){
        let self = this;
        let data = await this.loadPrivateReturVirksomhedAdress()
        if(data != null){
            var resdata = data.attributes;
            $('#street').val(resdata.address);
            $('#street2').val(resdata.address2);
            $('#postalCode').val(resdata.postal_code);
            $('#city').val(resdata.city);
            $('#country').val(resdata.country);
            $('#contactPerson').val(resdata.contact_name);
            $('#contactPhone').val(resdata.contact_phone);
            $('#contactEmail').val(resdata.contact_email);
        }
        $('#addressModal').modal('show');
        // Luk modal når der klikkes på luk-knappen eller krydset
        $('.close, [data-dismiss="modal"]').click(function() {
            $('#addressModal').modal('hide');
        });

        // Håndter gem-knap i modalen
        $('#saveAddress').unbind("click").click( async function() {
            // Validér form
            if ($('#addressForm')[0].checkValidity()) {
                // Indsaml adressedata
                var addressData = {
                    shop_id:self.shopid,
                    street: $('#street').val(),
                    postalCode: $('#postalCode').val(),
                    city: $('#city').val(),
                    country: $('#country').val(),
                    street2:$('#street2').val(),
                    contactPerson:$('#contactPerson').val(),
                    contactPhone:$('#contactPhone').val(),
                    contactEmail:$('#contactEmail').val()
                };


                // Her kan du gøre noget med addressData, f.eks. sende det til en server

                await self.saveUpdatePrivateReturVirksomhedAdress(addressData)
                // Luk modal
                $('#addressModal').modal('hide');

                // Nulstil form
                $('#addressForm')[0].reset();
            } else {
                // Hvis formen ikke er valid, tving browser til at vise valideringsfejl
                $('#addressForm')[0].reportValidity();
            }
        });
    }
    async loadPrivateReturVirksomhedAdress(){
        let self = this;
        return new Promise(resolve => {
            $.get( APPR_AJAX_URL+"loadPrivateReturVirksomhedAdress&shop_id="+self.shopid, function(res ) {
                resolve(res.data);
            }, "json")
        })
    }
    async saveUpdatePrivateReturVirksomhedAdress(formData){
        let self = this;
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"saveUpdatePrivateReturVirksomhedAdress/"+self.shopid, formData ,function(res ) {
                resolve(res);
            }, "json")

        })
    }


    async saveShopMetadata() {

        console.log("Gemmer ordre")
        var formData = {};

        try {

            // Gennemgår alle input, select og textarea elementer i formularen med ID "orderDataForm"
            $('#orderDataForm input, #orderDataForm select, #orderDataForm textarea').each(function() {


                var id = $(this).attr('id');

                // Skip utility felter
                if(id == "delivery_date1") return true;
                if(id == "resetPrivateReturType") return true;
                if(id == "privateReturVirksomhedAdress") return true;
                if(id == "privateReturVirksomhed") return true;


                var value = $(this).val();


                if ($(this).attr('type') === 'radio' ) {
                    var name = $(this).attr('name');
                    value = $('input[name="' + name + '"]:checked').val();
                    return true;
                }


                if ($(this).attr('type') === 'checkbox') {
                    value = $(this).is(':checked') ? 1 : 0;
                }

                // Tilføjer feltets værdi til formData objektet
                if(id != undefined && id != '' && id != null && id != 'undefined') {
                    formData[id] = value;
                }
            });

            // TILFØJELSE: Indsaml multiple budgets
            const multipleBudgets = this.getMultipleBudgets();
            if (multipleBudgets.length > 0) {
                formData['multiple_budgets_data'] = JSON.stringify(multipleBudgets);
            }


            if($("#foreign_delivery").val() == 1 || $("#foreign_delivery").val() == 2){
                formData['is_foreign'] = 1
            } else {
                formData['is_foreign'] = 0
            }

            formData['foreign_delivery_names'] = this.formToJson('foreign_countries');
            formData['language_names'] = this.formToJson('language_names_list');
            formData['private_retur_type'] = "none";

            const checkedPrivateRetur = $('input[name="privateReturType"]:checked').val();
            if (checkedPrivateRetur) {
                formData['private_retur_type'] = checkedPrivateRetur;
            }

            let foreignDatesObj = {};
            $('.foreign_dev_dates').each(function() {
                const $this = $(this);
                if ($this.val()) {
                    foreignDatesObj[$this.attr('id')] = $this.val();
                }
            });
            formData['foreign_delivery_date'] = JSON.stringify(foreignDatesObj);

            // NY KODE - Clear de ikke-brugte leveringsdato felter baseret på valgt type
            if($('#delivery_type').val() === 'fast') {
                formData['flex_start_delivery_date'] = '';
                formData['flex_end_delivery_date'] = '';
            } else {
                formData['delivery_date'] = '';
            }
            const multipleDeliveries = this.getMultipleDeliveries();
            if (multipleDeliveries.length > 0) {
                formData['multiple_deliveries_data'] = JSON.stringify(multipleDeliveries);
            } else {
                formData['multiple_deliveries_data'] = null;
            }
        } catch (e) {
            alert('Der opstod en fejl ved indsamling af ordredata. Kontakt venligst support.');
            console.log(e);
            return;
        }

        console.log('Sender følgende data:', formData);

        let response = await this.postShopMetadata(formData);

        if(response.status == 1) {
            this.checkOverbooking(formData)
            location.reload();
            alert('Ordren blev gemt');

        } else {
            alert('Der opstod en fejl ved gemning af ordren: '+response.message);
        }

//    console.log(response);
    }
    async checkOverbooking(formData){

        $.post( APPR_AJAX_URL+"overbooked&lang="+this.lang, formData ,function(res ) {

        }, "json")

    }


    async doConfirmOrderApproval() {
        let isAdmin = this.admin;

        $.post( APPR_AJAX_URL+"doConfirmOrderApproval/"+this.shopid, {shop_id:this.shopid,state:this.state} ,function(res ) {



            $("#order_approval_state").html("Send til godkendelse ")
            $(".save_button").hide();
            $(".order_approval").hide();
            if(isAdmin == 1){
                $(".save_button").show();
            }

        }, "json")

    }

    formToJson(formId) {
        var inputs = $('#' + formId).find('input');
        var result = {};

        inputs.each(function() {
            var input = $(this);

            // Tjekker om input er en checkbox
            if (input.attr('type') === 'checkbox') {
                result[input.attr('name')] = input.is(':checked');
            }
            // Tjekker om input er en tekstboks
            else if (input.attr('type') === 'text') {
                result[input.attr('name')] = input.val();
            }
        });

        return JSON.stringify(result);
    }

    jsonToForm(formId, jsonData) {

        if(jsonData == null || jsonData == "") return;

        //   console.log(jsonData);
        var data = JSON.parse(jsonData);
        var form = $('#' + formId);

        //    console.log(formId)
        //    console.log(data)

        $.each(data, function(key, value) {
            var input = form.find('input[name=' + key + ']');

            // Tjekker om input er en checkbox
            if (input.attr('type') === 'checkbox') {
                input.prop('checked', value);
            }
            // Tjekker om input er en tekstboks
            else if (input.attr('type') === 'text') {
                input.val(value);
            }
        });
    }


    // UI
    async initLayout(showMode){
        $(".order_approval").html("Godkend Ordredata");
        let self = this;
        if(showMode == 0){
            $("#vgshop_section1Content").html(tp_orderdata.mainTemplate());
        } else {
            $("#main-container").html(tp_orderdata.mainTemplate());
        }

        let personCode = await this.salespersonCode();
        $("#salesperson_code").html(tp_orderdata.UIinsertPersonCode(personCode));

        let orderData = await this.loadShopMetadata();

        try {
            setTimeout(() => {
                this.populateForm(orderData.data.metadata[0].attributes);
            }, 200);


        } catch (error) {
            alert('Der opstod en fejl ved indlæsning af ordredata til valgshoppen '+this.shopid+'. Kontakt support.');
            console.log(error);
        }
        let ApprovalState = await this.getApprovalState()
        let state = 0
        if(ApprovalState?.data?.metadata?.length > 0){
            state = ApprovalState.data.metadata[0].attributes.orderdata_approval
            self.state = state;
        } else {
            state = 0;
            self.state = state;

        }

        if(state == 0){
            $("#order_approval_state").html("<br>Mangler sælger godkendelse<hr>")
        }
        if(state == 1){
            $("#order_approval_state").html("<br>Sælger godkendelse ikke behandlet<hr> ")
            $(".order_approval").html("Gensend godkend Ordredata");
        }
        if(state == 2 ){
            $("#order_approval_state").html("<br>Godkendt<hr> ")
            $(".order_approval").html("Gensend godkend Ordredata");
        }
        if(state == 5){
            $("#order_approval_state").html("<br>Godkendt, manuelt opretet i NAV<hr> ")
            $(".order_approval").html("Gensend godkend Ordredata");
        }
        if(state == 3){
            let orderdata_approval_note = ApprovalState.data.metadata[0].attributes.orderdata_approval_note;
            orderdata_approval_note = orderdata_approval_note.replace(/(\r\n|\r|\n)/g, '<br>');
            $("#order_approval_state").html("<br>Ej godkendt med følgende begrundelse:<br><div style='font-size: 12px;color: black'><em>"+orderdata_approval_note+"</em></div><hr> ")
            $(".order_approval").html("Gensend godkend Ordredata");
        }
        if(state == 4){
            let orderdata_approval_note = ApprovalState.data.metadata[0].attributes.orderdata_approval_note;
            orderdata_approval_note = orderdata_approval_note.replace(/(\r\n|\r|\n)/g, '<br>');
            $("#order_approval_state").html("<br>Sælger godkendelse er sendt til genbehandlet<hr> ")
            $(".order_approval").html("Gensend godkend Ordredata");
        }
        if(this.admin == 1){
            $(".save_button").show();
        }

    }

// I populateForm() funktionen, ret håndteringen af delivery_date:

    populateForm(metadataAttributes) {

        if (metadataAttributes['delivery_date'] != null) {
            if(metadataAttributes['flex_start_delivery_date'] == null || metadataAttributes['delivery_type'] == "fast" ){
                metadataAttributes['delivery_type'] = 'fast';
            }
        }
        setTimeout(() => {
            if (metadataAttributes['delivery_type']) {
                $('#delivery_type').val(metadataAttributes['delivery_type']).trigger('change');
            }
        }, 300);

        // Håndter multiple budgets
        if (metadataAttributes['multiple_budgets_data'] && metadataAttributes['multiple_budgets_data'] !== null && metadataAttributes['multiple_budgets_data'] !== '') {
            // Hvis der er multiple budgets data - sæt til "Ja" og indlæs budgetterne
            $('#multiple_budgets_toggle').val('1').trigger('change');
            this.setMultipleBudgets(metadataAttributes['multiple_budgets_data']);
        } else if (metadataAttributes['budget'] && metadataAttributes['budget'] !== null && metadataAttributes['budget'] !== '') {
            // Hvis der kun er et enkelt budget - sæt til "Nej" og udfyld budget feltet
            $('#multiple_budgets_toggle').val('0').trigger('change');
            $('#budget').val(metadataAttributes['budget']);
        } else {
            // Hvis ingen budget data - sæt default til "Nej"
            $('#multiple_budgets_toggle').val('0').trigger('change');
        }

        // Håndter multiple deliveries - TILFØJET
        if (metadataAttributes['multiple_deliveries_data'] && metadataAttributes['multiple_deliveries_data'] !== null && metadataAttributes['multiple_deliveries_data'] !== '') {
            $('#multiple_deliveries').val('1').trigger('change');
            this.setMultipleDeliveries(metadataAttributes['multiple_deliveries_data']);
        } else {
            $('#multiple_deliveries').val('0').trigger('change');
        }

        for (var key in metadataAttributes) {
            var node = $('#orderDataForm').find('#'+key);
            let val = metadataAttributes[key];

            if(node.length > 0){
                // If checkbox
                if(node.attr('type') == 'checkbox'){
                    if(val == 1){
                        node.prop('checked', true);
                    } else {
                        node.prop('checked', false);
                    }
                }

                // If date parse value first
                if(node.attr('type') == 'date'){
                    let dateVal;
                    if(val != null) {
                        if (val.hasOwnProperty('date')) {
                            dateVal = val.date.split(' ')[0];
                        } else {
                            dateVal = val.split(' ')[0];
                        }
                        node.val(dateVal);
                    }
                    console.log(key)
                }
                else {
                    // RETTELSE: Håndter delivery_date som datepicker (samme som før)
                    if(key == 'delivery_date') {
                        if(val != null) {
                            val = this.changeDateFormat(val.date || val);
                        }
                    }
                    node.val(val);
                }

                node.trigger('change');
            }
            // Specielle felter der ikke er almindelige form inputs
            else if(key == 'foreign_delivery_names') {
                this.jsonToForm('foreign_countries', val);
            } else if(key == 'language_names') {
                this.jsonToForm('language_names_list', val);
            } else if(key == 'start_date') {
                if(val != null) {
                    val = this.changeDateFormat(val);
                    $('#orderOpenCloseChopStartDate').val(val)
                }
            } else if(key == 'end_date') {
                console.log(key)
                if(val != null) {
                    val = this.changeDateFormat(val);
                    $('#orderOpenCloseChopEndDate').val(val);
                }
            } else if(key == 'private_retur_type') {
                $('input[name="privateReturType"][value="'+val+'"]').prop('checked', true);
                $("#privateReturVirksomhedAdress").hide();
                if(val == "virksomhed") {
                    $("#privateReturVirksomhedAdress").show();
                }
            } else if(key == 'foreign_delivery_date') {
                try {
                    const FDDobj = JSON.parse(val);
                    for (const [key, value] of Object.entries(FDDobj)) {
                        $(`#${key}`).val(value);
                    }
                } catch (error) {
                    // Ignorer parsing fejl
                }
                console.log(val)
            }
                // TILFØJET: Håndtering af multiple_deliveries_data er allerede gjort ovenfor
            // så vi springer den over her for at undgå dobbelt behandling
            else if(key == 'multiple_deliveries_data') {
                // Allerede håndteret ovenfor - spring over
                continue;
            }
        }
    }

    populateForeignDates(foreignDatesString) {
        if (!foreignDatesString) return;

        const dateEntries = foreignDatesString.split('|');

        dateEntries.forEach(entry => {
            const [id, date] = entry.split(':');
            $(`#${id}`).val(date);
        });

    }
    orderOpenCloseChopGetEventClass(color) {
        switch(color) {
            case 'red':
                return 'orderOpenCloseChopEventRed';
            case 'yellow':
                return 'orderOpenCloseChopEventYellow';
            case 'green':
                return 'orderOpenCloseChopEventGreen';
            default:
                return '';
        }
    }

    orderOpenCloseChopGetAlertClass(color) {
        switch(color) {
            case 'red':
                return 'orderOpenCloseChopAlertRed';
            case 'yellow':
                return 'orderOpenCloseChopAlertYellow';
            case 'green':
                return 'orderOpenCloseChopAlertGreen';
            default:
                return '';
        }
    }
    orderOpenCloseChopFormatDate(date) {
        const day = ('0' + date.getDate()).slice(-2);
        const month = ('0' + (date.getMonth() + 1)).slice(-2);
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    }
    orderOpenCloseChopSetupDatepicker(selector, infoSelector, events) {
        let self = this;

        // Ændre input type til text hvis det er en date input
        if ($(selector).attr('type') === 'date') {
            $(selector).attr('type', 'text').attr('readonly', true);
        }

        var _gotoToday = $.datepicker._gotoToday;

        // Resten af funktionen forbliver den samme...
        $.datepicker._gotoToday = function(id) {
            var target = $(id);
            var inst = this._getInst(target[0]);
            _gotoToday.call(this, id);
            this._setDate(inst, new Date(), true);
        };

        $(selector).datepicker({
            dateFormat: 'dd-mm-yy',
            showButtonPanel: true,
            beforeShowDay: function(date) {
                const dateString = $.datepicker.formatDate('yy-mm-dd', date);
                const todayString = $.datepicker.formatDate('yy-mm-dd', new Date());
                if (dateString === todayString) {
                    return [true, 'orderOpenCloseChopToday', ''];
                }
                if (events[dateString]) {
                    const event = events[dateString];
                    const tooltipText = `Shop antal: ${event.shops}`;
                    const eventClass = self.orderOpenCloseChopGetEventClass(event.color);
                    return [true, eventClass, tooltipText];
                }
                return [true, '', ''];
            },
            onSelect: function(dateText) {
                const formattedDate = self.changeDateFormat(dateText);
                const event = events[formattedDate];
                const eventInfo = event ? `${event.info}\nShops på denne dato: ${event.shops}` : 'Ingen problemer \nIngen valgte shops på denne dag.';
                const alertClass = event ? self.orderOpenCloseChopGetAlertClass(event.color) : '';
                $(infoSelector).html(`<div class="alert ${alertClass}" style="white-space: pre-wrap;">${eventInfo}</div>`);
            },
            onAfterUpdate: function(inst) {
                setTimeout(function() {
                    var buttonPanel = $(inst.dpDiv).find('.ui-datepicker-buttonpane');
                    if (buttonPanel.find('.gotoToday').length === 0) {
                        var todayButton = $('<button>', {
                            text: 'Gå til i dag',
                            click: function() {
                                $.datepicker._gotoToday(selector);
                            }
                        }).addClass('gotoToday ui-datepicker-current ui-state-default ui-priority-primary ui-corner-all');
                        buttonPanel.empty().append(todayButton);
                    }
                }, 1);
            }
        });
    }
    changeDateFormat(dateTimeStr) {
        // Check if the input contains a space, indicating a datetime format
        let datePart;
        if (dateTimeStr.includes(' ')) {
            // Extract the date part from the datetime string
            datePart = dateTimeStr.split(' ')[0];
        } else {
            // Use the input directly as the date part
            datePart = dateTimeStr;
        }

        // Split the date part into components
        const [year, month, day] = datePart.split('-');

        // Rearrange the components into DD-MM-YYYY format
        const newDateStr = `${day}-${month}-${year}`;

        return newDateStr;
    }



    // ========================================
// KOMPLET CVR SØGEFUNKTION - RETTET VERSION
// ========================================
// Erstat din eksisterende initCompanySearch() og relaterede funktioner med denne kode

// Initialiser søgefunktionen (kald denne i init metoden)

    searchByCVR(cvr) {
        return new Promise(resolve => {
            $.get(BASE_AJAX_URL + "unit/external/cvrsearch/cvr/1/" + cvr, function(res) {
                resolve(res);
            }, "json").fail(function() {
                resolve({ status: 0, error: "CVR ikke fundet" });
            });
        });
    }

// Firma navn søgning
    searchByCompanyName(name) {
        return new Promise(resolve => {
            const encodedName = encodeURIComponent(name);
            $.get(BASE_AJAX_URL + "unit/navision/customerlist/searchname/1/" + encodedName, function(res) {
                resolve(res);
            }, "json").fail(function() {
                resolve({ status: 0, error: "Ingen resultater fundet" });
            });
        });
    }


    initCompanySearch() {
        let self = this;

        // Tilføj diskret knap før accordions
        const toggleButtonHTML = `
        <div style="text-align: right; margin-bottom: 10px;">
            <button type="button" id="toggle-company-search" class="btn btn-sm btn-outline-info">
                <i class="fa fa-search"></i> Søg virksomhed
            </button>
        </div>
    `;

        // Tilføj HTML til søgning lige før første accordion item - starter skjult
        const searchHTML = `
        <div class="accordion-item" id="company-search-section" style="margin-bottom: 20px; display: none;">
            <div class="accordion-header" style="background-color: #17a2b8;">
                <h4 style="margin: 0; padding: 15px; color: white;">
                    <i class="fa fa-search"></i> Søg virksomhed
                    <button type="button" id="close-company-search" class="btn btn-sm btn-light float-right">
                        <i class="fa fa-times"></i>
                    </button>
                </h4>
            </div>
            <div class="accordion-body" style="background-color: #f8f9fa; padding: 20px; border: 1px solid #17a2b8;">
                <div class="form-group" id="company-search-container">
                    <label>Søg efter CVR nummer eller firma navn</label>
                    <form id="company-search-form" autocomplete="off" onsubmit="return false;">
                    <div class="input-group">
                        <input type="text" id="company-search-input" class="form-control" 
                               placeholder="Indtast CVR nummer eller firma navn..."
                               autocomplete="nope" autocorrect="off" autocapitalize="off" spellcheck="false">
                        <div class="input-group-append">
                            <button class="btn btn-secondary" id="company-clear-btn" type="button" style="display:none;">
                                <i class="fa fa-times"></i>
                            </button>
                            <button class="btn btn-info" id="company-search-btn" type="button">
                                <i class="fa fa-search"></i> Søg
                            </button>
                        </div>
                    </div>
                    </form>
                    <div id="search-results" class="mt-3" style="display:none;">
                        <label>Søgeresultater:</label>
                        <small class="text-muted d-block mb-2">Klik for at vælge - Dobbelt-klik for hurtig overførsel</small>
                        <div id="search-results-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; background: white; border-radius: 4px;">
                        </div>
                    </div>
                    <button class="btn btn-success btn-block mt-2" id="apply-company-data" type="button" style="display:none;" onclick="return false;">
                        <i class="fa fa-check"></i> Overfør valgte data til formularen
                    </button>
                    <div id="search-error" class="alert alert-warning mt-2" style="display:none;"></div>
                </div>
            </div>
        </div>
    `;

        // Indsæt toggle knap og søgefelt
        $('#orderFormAccordion').before(toggleButtonHTML);
        $('#orderFormAccordion').prepend(searchHTML);

        // Toggle knap handler
        $('#toggle-company-search').click(function() {
            $('#company-search-section').slideToggle(300, function() {
                if ($(this).is(':visible')) {
                    $('#company-search-input').focus();
                }
            });
        });

        // Luk knap handler
        $('#close-company-search').click(function() {
            $('#company-search-section').slideUp();
            // Ryd søgning
            $('#company-search-input').val('');
            $('#search-results').hide();
            $('#search-error').hide();
            $('#apply-company-data').hide();
            $('#company-clear-btn').hide();
        });

        // Event handlers
        $('#company-search-btn').click(function(e) {
            e.preventDefault();
            self.performCompanySearch();
            return false;
        });

        $('#company-search-input').on('input', function() {
            if ($(this).val()) {
                $('#company-clear-btn').show();
            } else {
                $('#company-clear-btn').hide();
            }
        });

        $('#company-clear-btn').click(function(e) {
            e.preventDefault();
            $('#company-search-input').val('');
            $('#search-results').hide();
            $('#search-error').hide();
            $('#apply-company-data').hide();
            $(this).hide();
            return false;
        });

        $('#company-search-input').keypress(function(e) {
            if (e.which == 13) { // Enter key
                e.preventDefault();
                self.performCompanySearch();
                return false;
            }
        });

        // ESC tast lukker søgning
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && $('#company-search-section').is(':visible')) {
                $('#close-company-search').click();
            }
        });

        // Ny event handler for resultat valg
        $(document).on('click', '.search-result-item', function() {
            $('.search-result-item').removeClass('selected');
            $(this).addClass('selected');
            $('#apply-company-data').show();
        });

        // Dobbelt-klik for hurtig overførsel
        $(document).on('dblclick', '.search-result-item', function() {
            $('.search-result-item').removeClass('selected');
            $(this).addClass('selected');
            self.applySelectedCompanyData();
        });

        $('#apply-company-data').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            self.applySelectedCompanyData();
            return false;
        });

        // Forhindre form submit
        $('#company-search-form').on('submit', function(e) {
            e.preventDefault();
            return false;
        });
    }

// Udfør søgning
    async performCompanySearch() {
        const searchValue = $('#company-search-input').val().trim();

        if (!searchValue) {
            this.showSearchError('Indtast venligst et CVR nummer eller firma navn');
            return;
        }

        // Skjul tidligere resultater og fejl
        $('#search-results').hide();
        $('#search-error').hide();
        $('#apply-company-data').hide();

        // Vis loading
        $('#company-search-btn').html('<i class="fa fa-spinner fa-spin"></i> Søger...').prop('disabled', true);

        try {
            let results = [];

            // Check om det er et CVR nummer (8 cifre evt. med mellemrum eller bindestreger)
            const cleanedValue = searchValue.replace(/[\s-]/g, '');
            if (/^\d{8}$/.test(cleanedValue)) {
                // CVR søgning
                const cvrResult = await this.searchByCVR(cleanedValue);
                if (cvrResult.status === 1 && cvrResult.company) {
                    results.push({
                        type: 'cvr',
                        data: cvrResult.company
                    });
                }
            }

            // Søg efter firma navn
            // Søg altid efter navn, medmindre det er præcis 8 cifre (CVR)
            const nameResult = await this.searchByCompanyName(searchValue);
            if (nameResult.status === 1 && nameResult.customers && nameResult.customers.length > 0) {
                // Begræns til max 10 resultater for overskuelighed
                const limitedCustomers = nameResult.customers.slice(0, 10);
                limitedCustomers.forEach(customer => {
                    results.push({
                        type: 'navision',
                        data: customer
                    });
                });

                // Hvis der er flere end 10 resultater, vis info
                if (nameResult.customers.length > 10) {
                    $('#search-error').text(`Viser ${limitedCustomers.length} af ${nameResult.customers.length} resultater. Prøv at være mere specifik i din søgning.`).show();
                    setTimeout(() => {
                        $('#search-error').fadeOut();
                    }, 5000);
                }
            }

            if (results.length > 0) {
                this.displaySearchResults(results);
            } else {
                this.showSearchError('Ingen resultater fundet. Prøv at søge på andet navn eller CVR nummer.');
            }

        } catch (error) {
            console.error('Søgefejl:', error);
            this.showSearchError('Der opstod en fejl under søgningen. Prøv igen senere.');
        } finally {
            $('#company-search-btn').html('<i class="fa fa-search"></i> Søg').prop('disabled', false);
        }
    }

// Hjælpe funktioner til HTML escaping
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    formatDisplayDate(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('da-DK');
        } catch (e) {
            return dateString;
        }
    }
// Vis søgeresultater
// Erstat din displaySearchResults funktion med denne udvidede version:

// Vis søgeresultater med udvidet information
    displaySearchResults(results) {
        const resultsContainer = $('#search-results-list');
        resultsContainer.empty();

        results.forEach((result, index) => {
            let resultHTML = '';
            // Escape JSON for HTML attribute
            const escapedResult = JSON.stringify(result).replace(/"/g, '&quot;');
            const uniqueId = `result-${index}-${Date.now()}`; // Unik ID for hver resultat

            if (result.type === 'cvr') {
                const company = result.data;
                resultHTML = `
                <div class="search-result-item" data-result="${escapedResult}">
                    <div class="result-main-content">
                        <div class="result-type-badge cvr">CVR</div>
                        <div class="result-content">
                            <div class="result-name">${this.escapeHtml(company.name)}</div>
                            <div class="result-details">
                                <span class="result-cvr">CVR: ${company.vat}</span>
                                <span class="result-address">${this.escapeHtml(company.address)}, ${company.zipcode} ${this.escapeHtml(company.city)}</span>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-link toggle-details" data-target="${uniqueId}">
                            <i class="fa fa-chevron-down"></i> Vis mere
                        </button>
                    </div>
                    <div id="${uniqueId}" class="result-expanded-details" style="display: none;">
                        <table class="table table-sm table-striped">
                            <tbody>
                                <tr><td><strong>CVR nummer:</strong></td><td>${company.vat}</td></tr>
                                <tr><td><strong>Firmanavn:</strong></td><td>${this.escapeHtml(company.name)}</td></tr>
                                <tr><td><strong>Adresse:</strong></td><td>${this.escapeHtml(company.address)}</td></tr>
                                <tr><td><strong>Postnr/By:</strong></td><td>${company.zipcode} ${this.escapeHtml(company.city)}</td></tr>
                                ${company.phone ? `<tr><td><strong>Telefon:</strong></td><td>${company.phone}</td></tr>` : ''}
                                ${company.email ? `<tr><td><strong>Email:</strong></td><td>${company.email}</td></tr>` : ''}
                                ${company.protected !== undefined ? `<tr><td><strong>Beskyttet:</strong></td><td>${company.protected ? 'Ja' : 'Nej'}</td></tr>` : ''}
                            </tbody>
                        </table>
                        ${company.productionunits && company.productionunits.length > 0 ? `
                            <h6>Produktionsenheder:</h6>
                            <div class="production-units">
                                ${company.productionunits.map(unit => `
                                    <div class="unit-item">
                                        <strong>${this.escapeHtml(unit.name)}</strong>
                                        ${unit.pno ? `<small> (P-nr: ${unit.pno})</small>` : ''}<br>
                                        ${unit.address}, ${unit.zipcode} ${this.escapeHtml(unit.city)}<br>
                                        ${unit.phone ? `Tlf: ${unit.phone}<br>` : ''}
                                        ${unit.main ? '<span class="badge badge-primary">Hovedadresse</span>' : ''}
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            } else if (result.type === 'navision') {
                const customer = result.data;
                resultHTML = `
                <div class="search-result-item" data-result="${escapedResult}">
                    <div class="result-main-content">
                        <div class="result-type-badge navision">NAV</div>
                        <div class="result-content">
                            <div class="result-name">${this.escapeHtml(customer.name)}</div>
                            <div class="result-details">
                                <span class="result-customer-no">Kundenr: ${customer.customer_no}</span>
                                ${customer.salesperson_code ? `<span class="result-salesperson">(${this.escapeHtml(customer.salesperson_code)})</span>` : ''}
                                <span class="result-address">${this.escapeHtml(customer.address)}, ${customer.postcode} ${this.escapeHtml(customer.city)}</span>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-link toggle-details" data-target="${uniqueId}">
                            <i class="fa fa-chevron-down"></i> Vis mere
                        </button>
                    </div>
                    <div id="${uniqueId}" class="result-expanded-details" style="display: none;">
                        <table class="table table-sm table-striped">
                            <tbody>
                                <tr><td width="40%"><strong>Kundenummer:</strong></td><td>${customer.customer_no}</td></tr>
                                <tr><td><strong>Firmanavn:</strong></td><td>${this.escapeHtml(customer.name)}</td></tr>
                                <tr><td><strong>Adresse:</strong></td><td>${this.escapeHtml(customer.address)}</td></tr>
                                ${customer.address2 ? `<tr><td><strong>Adresse 2:</strong></td><td>${this.escapeHtml(customer.address2)}</td></tr>` : ''}
                                <tr><td><strong>Postnr/By:</strong></td><td>${customer.postcode} ${this.escapeHtml(customer.city)}</td></tr>
                                ${customer.country ? `<tr><td><strong>Land:</strong></td><td>${customer.country}</td></tr>` : ''}
                                ${customer.cvr ? `<tr><td><strong>CVR:</strong></td><td>${customer.cvr}</td></tr>` : ''}
                                ${customer.ean ? `<tr><td><strong>EAN:</strong></td><td>${customer.ean}</td></tr>` : ''}
                                ${customer.gln ? `<tr><td><strong>GLN:</strong></td><td>${customer.gln}</td></tr>` : ''}
                                ${customer.contact ? `<tr><td><strong>Kontaktperson:</strong></td><td>${this.escapeHtml(customer.contact)}</td></tr>` : ''}
                                ${customer.phone ? `<tr><td><strong>Telefon:</strong></td><td>${customer.phone}</td></tr>` : ''}
                                ${customer.email ? `<tr><td><strong>Email:</strong></td><td>${customer.email}</td></tr>` : ''}
                                ${customer.bill_to_email ? `<tr><td><strong>Faktura email:</strong></td><td>${customer.bill_to_email}</td></tr>` : ''}
                                ${customer.invoice_email && customer.invoice_email !== customer.bill_to_email ? `<tr><td><strong>Alternativ faktura email:</strong></td><td>${customer.invoice_email}</td></tr>` : ''}
                                ${customer.salesperson_code ? `<tr><td><strong>Sælgerkode:</strong></td><td>${customer.salesperson_code}</td></tr>` : ''}
                                ${customer.payment_method_code ? `<tr><td><strong>Betalingsmetode:</strong></td><td>${customer.payment_method_code}</td></tr>` : ''}
                                ${customer.currency_code ? `<tr><td><strong>Valuta:</strong></td><td>${customer.currency_code}</td></tr>` : ''}
                                ${customer.credit_limit && customer.credit_limit !== "0" ? `<tr><td><strong>Kreditgrænse:</strong></td><td>${customer.credit_limit}</td></tr>` : ''}
                                ${customer.blocked && customer.blocked !== "_blank_" ? `<tr><td><strong>Blokeret:</strong></td><td><span class="text-danger">${customer.blocked}</span></td></tr>` : ''}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            }

            resultsContainer.append(resultHTML);
        });

        // Tilføj click handlers for fold-ud
        $('.toggle-details').off('click').on('click', function(e) {
            e.stopPropagation(); // Forhindre at parent click handler aktiveres
            const targetId = $(this).data('target');
            const $target = $(`#${targetId}`);
            const $icon = $(this).find('i');

            if ($target.is(':visible')) {
                $target.slideUp();
                $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $(this).html('<i class="fa fa-chevron-down"></i> Vis mere');
            } else {
                $target.slideDown();
                $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $(this).html('<i class="fa fa-chevron-up"></i> Vis mindre');
            }
        });

        $('#search-results').show();

        // Tilføj info om antal resultater
        const infoText = `Fundet ${results.length} resultat${results.length !== 1 ? 'er' : ''}`;
        if ($('#search-results-info').length === 0) {
            $('<div id="search-results-info" class="text-muted small mb-2">' + infoText + '</div>')
                .insertBefore('#search-results-list');
        } else {
            $('#search-results-info').text(infoText);
        }
    }
// Overfør valgte data til formularen - simpel version
    applySelectedCompanyData() {
        const selectedItem = $('.search-result-item.selected');
        if (selectedItem.length === 0) {
            alert('Vælg venligst et resultat først');
            return;
        }

        // Hent og decode HTML entities
        let selectedValue = selectedItem.attr('data-result');
        selectedValue = selectedValue.replace(/&quot;/g, '"');

        try {
            const result = JSON.parse(selectedValue);

            if (result.type === 'cvr') {
                const company = result.data;
                // Simpel udfyldning med jQuery - kun hvis felt findes og har værdi
                if (company.vat && $('#cvr').length) {
                    $('#cvr').val(company.vat);

                }
                if (company.name && $('#name').length) {
                    $('#name').prop('disabled', false).val(company.name);
                }
                if (company.address && $('#ship_to_address').length) {
                    $('#ship_to_address').val(company.address);
                }
                if (company.zipcode && $('#ship_to_postal_code').length) {
                    $('#ship_to_postal_code').val(company.zipcode);
                }
                if (company.city && $('#ship_to_city').length) {
                    $('#ship_to_city').val(company.city);
                }
                if (company.phone && $('#contact_phone').length) {
                    $('#contact_phone').prop('disabled', false).val(company.phone);
                }
                if (company.email) {
                    if ($('#contact_email').length) {
                        $('#contact_email').prop('disabled', false).val(company.email);
                    }
                    if ($('#bill_to_email').length) {
                        $('#bill_to_email').val(company.email);
                    }
                }

            } else if (result.type === 'navision') {
                const customer = result.data;
                if (customer.cvr && $('#cvr').length) {
                    $('#cvr').val(customer.cvr);

                }
                // Simpel udfyldning med jQuery - kun hvis felt findes og har værdi
                if (customer.customer_no && $('#nav_debitor_no').length) {
                    $('#nav_debitor_no').val(customer.customer_no);
                }
                if (customer.name && $('#name').length) {
                    $('#name').prop('disabled', false).val(customer.name);
                }
                if (customer.address && $('#ship_to_address').length) {
                    $('#ship_to_address').val(customer.address);
                }
                if (customer.address2 && $('#ship_to_address_2').length) {
                    $('#ship_to_address_2').val(customer.address2);
                }
                if (customer.postcode && $('#ship_to_postal_code').length) {
                    $('#ship_to_postal_code').val(customer.postcode);
                }
                if (customer.city && $('#ship_to_city').length) {
                    $('#ship_to_city').val(customer.city);
                }
                if (customer.phone && $('#contact_phone').length) {
                    $('#contact_phone').prop('disabled', false).val(customer.phone);
                }
                if (customer.email && $('#contact_email').length) {
                    $('#contact_email').prop('disabled', false).val(customer.email);
                }
                if (customer.contact && $('#contact_name').length) {
                    $('#contact_name').prop('disabled', false).val(customer.contact);
                }
                if (customer.salesperson_code && $('#salesperson_code').length) {
                    $('#salesperson_code').val(customer.salesperson_code);
                }
                if ((customer.bill_to_email || customer.invoice_email) && $('#bill_to_email').length) {
                    $('#bill_to_email').val(customer.bill_to_email || customer.invoice_email);
                }
            }

            // Skjul søgeresultater og luk søge sektionen
            $('#company-search-section').slideUp();
            $('#search-results').hide();
            $('#search-error').hide();
            $('#company-search-input').val('');
            $('#company-clear-btn').hide();
            $('#apply-company-data').hide();

            // Simpel success besked
            alert('Data overført til formularen');

        } catch (error) {
            console.error('Fejl:', error);
            alert('Der opstod en fejl ved overførsel af data');
        }
    }

// Vis fejlbesked
    showSearchError(message) {
        $('#search-error').text(message).show();
        setTimeout(() => {
            $('#search-error').fadeOut();
        }, 5000);
    }

// Hjælpefunktion til at få alle budgetter som JSON
    getMultipleBudgets() {
        const budgets = [];
        $('.budget-item').each(function() {
            const amount = $(this).find('.budget-amount').val();
            if (amount && parseFloat(amount) > 0) {
                budgets.push({
                    amount: parseFloat(amount)
                });
            }
        });
        return budgets;
    }

// Hjælpefunktion til at sætte multiple budgetter fra JSON
    setMultipleBudgets(budgetsJson) {
        if (!budgetsJson) return;

        try {
            const budgets = JSON.parse(budgetsJson);
            $('#budgets_list').empty();

            budgets.forEach(budget => {
                const budgetId = 'budget_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const budgetHtml = `
                <div class="budget-item border rounded p-2 mb-2" data-budget-id="${budgetId}">
                    <div class="row align-items-center">
                        <div class="col-10">
                            <strong>${parseFloat(budget.amount).toFixed(2)} </strong>
                        </div>
                        <div class="col-2 text-right">
                            <button type="button" class="btn btn-sm btn-danger remove-budget-btn" data-budget-id="${budgetId}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" class="budget-amount" value="${budget.amount}">
                </div>
            `;
                $('#budgets_list').append(budgetHtml);
            });
        } catch (e) {
            console.error('Fejl ved parsing af budgetter:', e);
        }
    }
    initAutoSave() {
        const self = this;

        // Opret ny AutoSaveManager instance
        this.autoSaveManager = new AutoSaveManager();

        // Initialiser med konfiguration og callbacks
        this.autoSaveManager.init({
            // Konfiguration
            idleThreshold: 15000,        // 30 sekunder før countdown
            autoSaveInterval: 300000,    // 5 minutter total
            position: 'top-right',       // Position på siden
            showDebugInfo: false,        // Sæt til true for debug mode

            // Påkrævet callback - denne funktion gemmer dataene
            onSave: async () => {
                return await self.saveShopMetadataQuiet();
            },

            // Optional callbacks
            onSuccess: (result) => {
                console.log('Autogem succesfuld:', result);
            },

            onError: (error) => {
                console.error('Autogem fejlede:', error);
                // Du kan tilføje fejl logging her
            },

            onIdleStart: () => {
                console.log('Bruger er blevet idle - starter countdown');
            },

            onActivity: () => {
                // Køres ved hver bruger aktivitet
                // console.log('Bruger aktivitet registreret');
            }
        });
    }

// Tilføj denne metode til Orderdata klassen - samme som saveShopMetadata men uden UI feedback
    async saveShopMetadataQuiet() {
        console.log("Autogem ordre")
        var formData = {};

        try {
            // Samme logik som i saveShopMetadata, men uden alerts og reload
            $('#orderDataForm input, #orderDataForm select, #orderDataForm textarea').each(function() {
                var id = $(this).attr('id');

                if(id == "delivery_date1") return true;
                if(id == "resetPrivateReturType") return true;
                if(id == "privateReturVirksomhedAdress") return true;
                if(id == "privateReturVirksomhed") return true;

                var value = $(this).val();

                if ($(this).attr('type') === 'radio' ) {
                    var name = $(this).attr('name');
                    value = $('input[name="' + name + '"]:checked').val();
                    return true;
                }

                if ($(this).attr('type') === 'checkbox') {
                    value = $(this).is(':checked') ? 1 : 0;
                }

                if(id != undefined && id != '' && id != null && id != 'undefined') {
                    formData[id] = value;
                }
            });

            // Samme data processing som i original saveShopMetadata
            const multipleBudgets = this.getMultipleBudgets();
            if (multipleBudgets.length > 0) {
                formData['multiple_budgets_data'] = JSON.stringify(multipleBudgets);
            }

            if($("#foreign_delivery").val() == 1 || $("#foreign_delivery").val() == 2){
                formData['is_foreign'] = 1
            } else {
                formData['is_foreign'] = 0
            }

            formData['foreign_delivery_names'] = this.formToJson('foreign_countries');
            formData['language_names'] = this.formToJson('language_names_list');
            formData['private_retur_type'] = "none";

            const checkedPrivateRetur = $('input[name="privateReturType"]:checked').val();
            if (checkedPrivateRetur) {
                formData['private_retur_type'] = checkedPrivateRetur;
            }

            let foreignDatesObj = {};
            $('.foreign_dev_dates').each(function() {
                const $this = $(this);
                if ($this.val()) {
                    foreignDatesObj[$this.attr('id')] = $this.val();
                }
            });
            formData['foreign_delivery_date'] = JSON.stringify(foreignDatesObj);

            if($('#delivery_type').val() === 'fast') {
                formData['flex_start_delivery_date'] = '';
                formData['flex_end_delivery_date'] = '';
            } else {
                formData['delivery_date'] = '';
            }

            const multipleDeliveries = this.getMultipleDeliveries();
            if (multipleDeliveries.length > 0) {
                formData['multiple_deliveries_data'] = JSON.stringify(multipleDeliveries);
            } else {
                formData['multiple_deliveries_data'] = null;
            }


        } catch (e) {
            throw new Error('Der opstod en fejl ved indsamling af ordredata: ' + e.message);
        }

        console.log('Autogem sender følgende data:', formData);

        let response = await this.postShopMetadata(formData);

        if(response.status != 1) {
            throw new Error('Der opstod en fejl ved autogem af ordren: ' + response.message);
        }

        return response;
    }

}

