
var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/main/";
var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";
import Base from 'https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/main/js/base.js';
import tp_orderdata from '../tp/orderdata.tp.js';
var shop;

export default class Orderdata extends Base {
    constructor() {
        super();
    }
    async init(shopid,showMode,admin,lang=1){
        this.lang = lang;
        this.dontSave = false;
        this.state = 0;
        this.admin = admin;
        this.shopid = shopid;
        this.initLayout(showMode);
        this.eventhandler();

        let OrderOpenClose = await this.getOrderOpenCloseChopEvents();
        this.orderOpenCloseChopEventsStart = this.procesOpenCloseData(OrderOpenClose.data.start);
        this.orderOpenCloseChopEventsEnd = this.procesOpenCloseData(OrderOpenClose.data.end);

        this.orderOpenCloseChopSetupDatepicker('#orderOpenCloseChopStartDate', '#orderOpenCloseChopStartDateInfo', this.orderOpenCloseChopEventsStart);
        this.orderOpenCloseChopSetupDatepicker('#orderOpenCloseChopEndDate', '#orderOpenCloseChopEndDateInfo', this.orderOpenCloseChopEventsEnd);

        let deliveryDatesData = await this.getdeliveryDates();
        this.deliveryDates = this.procesOpenCloseData(deliveryDatesData.data);
        this.orderOpenCloseChopSetupDatepicker('#delivery_date', '#delivery_date1Info', this.deliveryDates);
    }

    getOrderOpenCloseChopEvents() {
        return new Promise(resolve => {
            $.get( APPR_AJAX_URL+"orderOpenCloseChopEvents&lang="+this.lang, function(res ) {
                resolve(res);
            }, "json")
        })
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
            self.saveShopMetadata();

        });
        $('.order_approval').click(function() {
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
                if(id == "delivery_date1"){
                    return true;
                }
                if(id == "resetPrivateReturType"){
                    return true;
                }
                if(id == "privateReturVirksomhedAdress"){
                    return true;
                }

                if(id == "privateReturVirksomhed"){
                    return true;
                }
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

            formData['foreign_delivery_names'] = this.formToJson('foreign_countries');
            formData['language_names'] = this.formToJson('language_names_list');
            formData['private_retur_type'] = "none";
            formData['private_retur_type'] = $('input[name="privateReturType"]:checked').val();



        } catch (e) {
            alert('Der opstod en fejl ved indsamling af ordredata. Kontakt venligst support.');
            console.log(e);
            return;
        }

        console.log('Send data');
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

            this.populateForm(orderData.data.metadata[0].attributes);

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

    populateForm(metadataAttributes) {

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
                    if(key == 'delivery_date') {
                        if(val != null) {
                            val = this.changeDateFormat(val.date);
                        }
                    }

                    node.val(val);


                }

                node.trigger('change');


            }
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
            }


        }

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

        var _gotoToday = $.datepicker._gotoToday;

        // Udvid _gotoToday funktionaliteten
        $.datepicker._gotoToday = function(id) {
            var target = $(id);
            var inst = this._getInst(target[0]);
            _gotoToday.call(this, id);
            this._setDate(inst, new Date(), true);
        };
        $(selector).datepicker({
            dateFormat: 'dd-mm-yy', // Ændre datoformatet til dd-mm-yy
            showButtonPanel: true, // Vis knappen 'Gå til i dag'
            beforeShowDay: function(date) {
                const dateString = $.datepicker.formatDate('yy-mm-dd', date);
                const todayString = $.datepicker.formatDate('yy-mm-dd', new Date());
                if (dateString === todayString) {
                    return [true, 'orderOpenCloseChopToday', '']; // Tilføj klasse for dagens dato
                }
                if (events[dateString]) {
                    const event = events[dateString];
                    const tooltipText = `Shop antal: ${event.shops}`;
                    //const tooltipText = `Info: ${event.info}\nShop antal: ${event.shops}\nGifts: ${event.gifts}`;
                    const eventClass = self.orderOpenCloseChopGetEventClass(event.color);
                    return [true, eventClass, tooltipText];
                }
                return [true, '', ''];
            },
            onSelect: function(dateText) {
                console.log(dateText)
                const formattedDate = self.changeDateFormat(dateText);
                const event = events[formattedDate];
                const eventInfo = event ? `${event.info}\nShops på denne dato: ${event.shops}` : 'Ingen problemer \nIngen valgte shops på denne dag.';
                const alertClass = event ? self.orderOpenCloseChopGetAlertClass(event.color) : '';
                $(infoSelector).html(`<div class="alert ${alertClass}" style="white-space: pre-wrap;">${eventInfo}</div>`);
                //if(event.shops >39){alert("Hvis du vælger denne dato skal den godkendes af Susanne.  Hvis du gemmer med denne dato vil der bliver sent en godkendelsesmail til Susanne")}
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
}