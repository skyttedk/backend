/**
 * Form handling functionality for Finalbilling
 */
export default class FinalbillingForm {
    constructor() {}

    showDataSourceIndication(dataSource, isApproved) {
        // Remove existing indicators
        $('.data-source-indicator').remove();
        
        var indicator = '';
        
        if (isApproved) {
            indicator = '<div class="alert alert-info data-source-indicator"><i class="fas fa-lock"></i> <strong>Godkendt:</strong> Dette orderskema er godkendt og kan kun læses. Fjern godkendelse for at redigere.</div>';
            this.setFormReadOnly(true);
        } else {
            switch(dataSource) {
                case 'shop_metadata':
                    indicator = '<div class="alert alert-warning data-source-indicator"><i class="fas fa-exclamation-triangle"></i> <strong>Bemærk:</strong> Dette orderskema er ikke færdiggjort. Data vises fra shop_metadata.</div>';
                    break;
                case 'shop_invoice2':
                    indicator = '<div class="alert alert-success data-source-indicator"><i class="fas fa-check-circle"></i> <strong>Færdiggjort:</strong> Dette orderskema er gemt i shop_invoice2.</div>';
                    break;
                case 'new':
                    indicator = '<div class="alert alert-info data-source-indicator"><i class="fas fa-info-circle"></i> <strong>Nyt orderskema:</strong> Der oprettes et nyt orderskema for denne adresse.</div>';
                    break;
            }
            this.setFormReadOnly(false);
        }
        
        if (indicator) {
            $('#finalbillingForm').prepend(indicator);
        }
    }

    setFormReadOnly(readonly) {
        if (readonly) {
            // Disable all form inputs
            $('#finalbillingForm input, #finalbillingForm select, #finalbillingForm textarea').prop('disabled', true);
            // Hide save buttons
            $('#save-finalbilling-btn, .save_finalbilling_button').hide();
        } else {
            // Enable all form inputs
            $('#finalbillingForm input, #finalbillingForm select, #finalbillingForm textarea').prop('disabled', false);
            // Show save buttons
            $('#save-finalbilling-btn, .save_finalbilling_button').show();
        }
    }

    populateFormFields(data) {
        for (var key in data) {
            var node = $('#finalbillingForm').find('#'+key);
            var val = data[key];

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
                else if(node.attr('type') == 'date'){
                    var dateVal;
                    if(val != null) {
                        if (val.hasOwnProperty && val.hasOwnProperty('date')) {
                            dateVal = val.date.split(' ')[0];
                        } else if(typeof val === 'string') {
                            dateVal = val.split(' ')[0];
                        } else {
                            dateVal = val;
                        }
                        node.val(dateVal);
                    }
                }
                else {
                    node.val(val);
                }

                // Trigger change event to show/hide conditional fields
                node.trigger('change');
            }
        }

        // Manually trigger change events for conditional fields to set initial state
        setTimeout(function() {
            $('#payment_special').trigger('change');
            $('#invoice_fee').trigger('change');
            $('#discount_option').trigger('change');
            $('#deliveryprice_option').trigger('change');
            $('#dot_use').trigger('change');
            $('#carryup_use').trigger('change');
            $('#present_nametag').trigger('change');
            $('#present_papercard').trigger('change');
            $('#present_wrap').trigger('change');
            $('#handling_special').trigger('change');
            $('#loan_use').trigger('change');
            $('#multiple_deliveries').trigger('change');
        }, 100);
    }

    collectFormData() {
        var formData = {};

        try {
            // Gennemgå alle input, select og textarea elementer i finalbilling formularen
            $('#finalbillingForm input, #finalbillingForm select, #finalbillingForm textarea').each(function() {
                var id = $(this).attr('id');
                var value = $(this).val();

                if ($(this).attr('type') === 'checkbox') {
                    value = $(this).is(':checked') ? 1 : 0;
                }

                // Tilføj feltets værdi til formData objektet
                if(id != undefined && id != '' && id != null && id != 'undefined') {
                    formData[id] = value;
                }
            });

            // Tilføj hidden fields - fjern invoice_id da vi ikke bruger det længere
            formData['shop_id'] = $('#invoice-shop-id').val();
            formData['address_id'] = $('#invoice-address-id').val();

        } catch (e) {
            throw new Error('Der opstod en fejl ved indsamling af finalbilling data: ' + e.message);
        }

        return formData;
    }

    setupEventHandlers() {
        var self = this;

        // Toggle conditional fields
        $(document).on('change', '#multiple_deliveries', function() {
            $('#multiple_deliveries_data_group').toggle(this.value == '1');
        });

        $(document).on('change', '#dot_use', function() {
            $('#dot_fields').toggle(this.value == '1');
        });

        $(document).on('change', '#carryup_use', function() {
            $('#carryup_fields').toggle(this.value == '1');
        });

        $(document).on('change', '#present_nametag', function() {
            $('#present_nametag_price_group').toggle(this.value == '1');
        });

        $(document).on('change', '#present_papercard', function() {
            $('#present_papercard_price_group').toggle(this.value == '1');
        });

        $(document).on('change', '#present_wrap', function() {
            $('#present_wrap_price_group').toggle(this.value == '1');
        });

        $(document).on('change', '#deliveryprice_option', function() {
            $('#deliveryprice_amount_group').toggle(this.value == '1');
        });

        $(document).on('change', '#discount_option', function() {
            $('#discount_value_group').toggle(this.value == '1');
        });

        $(document).on('change', '#payment_special', function() {
            $('#payment_special_note_group').toggle(this.value == '1');
        });

        $(document).on('change', '#handling_special', function() {
            $('#handling_special_group').toggle(this.value == '1');
        });

        $(document).on('change', '#loan_use', function() {
            $('#loan_fields').toggle(this.value == '1');
        });

        $(document).on('change', '#invoice_fee', function() {
            $('#invoice_fee_value_group').toggle(this.value == '1');
        });
    }
}