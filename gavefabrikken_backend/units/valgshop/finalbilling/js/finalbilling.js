import FinalbillingData from './finalbilling-data.js';
import FinalbillingForm from './finalbilling-form.js';

function Finalbilling() {
    this.currentShopData = null;
    this.currentMode = null;
    this.dataManager = new FinalbillingData();
    this.formManager = new FinalbillingForm();
    this.templates = window.TpFinalbilling || TpFinalbilling; // Use global TpFinalbilling
}

Finalbilling.prototype.init = function(shopId, mode) {
    mode = mode || 'oversigt';
    this.shopId = shopId;
    this.currentMode = mode;

    // Convert numeric mode to string if needed
    var convertedMode = mode;
    if (mode === 1 || mode === '1') convertedMode = 'oversigt';
    if (mode === 2 || mode === '2') convertedMode = 'fakturering';
    if (mode === 3 || mode === '3') convertedMode = 'rapporter';

    this.currentMode = convertedMode;

    this.initLayout();
    this.eventHandlers();

    var self = this;
    switch(convertedMode) {
        case 'oversigt':
            self.loadShopsOversigt();
            break;
        case 'fakturering':
            self.loadFinalbilling();
            break;
        case 'rapporter':
            self.loadRapporter();
            break;
    }
};

Finalbilling.prototype.initLayout = function() {
    switch(this.currentMode) {
        case 'oversigt':
            var section1 = $("#finalbilling_section1Content");
            if (section1.length > 0) {
                section1.html(this.templates.oversigtsTemplate());
            }
            break;
        case 'fakturering':
            var section2 = $("#finalbilling_section2Content");
            if (section2.length > 0) {
                section2.html(this.templates.faktureringTemplate());
            }
            break;
        case 'rapporter':
            var section3 = $("#finalbilling_section3Content");
            if (section3.length > 0) {
                section3.html(this.templates.rapportTemplate());
            }
            break;
    }
};

Finalbilling.prototype.eventHandlers = function() {
    var self = this;

    // Refresh data knap
    $(document).on('click', '#refresh-data-btn', function() {
        if (self.currentMode === 'oversigt') {
            self.loadShopsOversigt();
        }
    });

    // Åbn finalbilling modal
    $(document).on('click', '.edit-finalbilling-btn', function() {
        var shopId = $(this).data('shop-id');
        var addressId = $(this).data('address-id');
        self.openFinalbillingModal(shopId, addressId);
    });

    // Gem finalbilling data
    $(document).on('click', '#save-finalbilling-btn', function() {
        self.saveFinalbillingData();
    });

    // Approve finalbilling
    $(document).on('click', '.approve-finalbilling-btn', function() {
        var shopId = $(this).data('shop-id');
        var addressId = $(this).data('address-id');
        self.approveFinalbilling(shopId, addressId);
    });

    // Remove approval
    $(document).on('click', '.remove-approval-btn', function() {
        var shopId = $(this).data('shop-id');
        var addressId = $(this).data('address-id');
        self.removeApprovalFinalbilling(shopId, addressId);
    });

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

    // Save finalbilling button
    $(document).on('click', '.save_finalbilling_button', function() {
        self.saveFinalbillingData();
    });
};

Finalbilling.prototype.loadShopsOversigt = function() {
    var self = this;
    $('#loading-spinner').show();
    $('#shops-container').hide();

    this.dataManager.getShopsForFinalbilling(this.shopId).then(function(response) {
        if (response.status === 1) {
            self.renderShopsOversigt(response.data);
        } else {
            self.showError("Fejl ved indlæsning af shop: " + response.error);
        }
        $('#loading-spinner').hide();
        $('#shops-container').show();
    }).catch(function(error) {
        self.showError("Netværksfejl ved indlæsning af data");
        $('#loading-spinner').hide();
        $('#shops-container').show();
    });
};

Finalbilling.prototype.loadFinalbilling = function() {
    var self = this;
    if (this.shopId) {
        this.dataManager.getShopFinalbillingData(this.shopId).then(function(response) {
            if (response.status === 1) {
                self.renderFinalbilling(response.data);
            }
        }).catch(function(error) {
            self.showError("Fejl ved indlæsning af finalbilling data");
        });
    }
};

Finalbilling.prototype.loadRapporter = function() {
    var self = this;
    this.dataManager.getFinalbillingRapporter().then(function(response) {
        if (response.status === 1) {
            self.renderRapporter(response.data);
        }
    }).catch(function(error) {
        self.showError("Fejl ved indlæsning af rapporter");
    });
};

// API methods moved to FinalbillingData module

// Render methods
Finalbilling.prototype.renderShopsOversigt = function(shop) {
    var container = $('#shops-list');
    container.empty();

    if (!shop) {
        container.html('<div class="alert alert-info">Ingen shop fundet til finalbilling</div>');
        return;
    }

    var shopHtml = this.createShopCard(shop);
    container.append(shopHtml);
};

Finalbilling.prototype.renderFinalbilling = function(data) {
    var container = $('#finalbilling-container');
    container.html(this.templates.finalbillingDetailTemplate(data));
};

Finalbilling.prototype.renderRapporter = function(data) {
    var container = $('#rapporter-container');
    container.html(this.templates.rapporterTemplate(data));
};

Finalbilling.prototype.createShopCard = function(shop) {
    var statusCounts = this.calculateStatusCounts(shop.addresses);

    return this.templates.shopCardTemplate({
        shop: shop,
        statusCounts: statusCounts,
        addresses: shop.addresses
    });
};

Finalbilling.prototype.calculateStatusCounts = function(addresses) {
    var counts = { pending: 0, approved: 0 };

    if (addresses) {
        addresses.forEach(function(addr) {
            var approved = addr.attributes.approved || 0;
            if (approved == 1) {
                counts.approved++;
            } else {
                counts.pending++;
            }
        });
    }

    return counts;
};

Finalbilling.prototype.openFinalbillingModal = function(shopId, addressId) {
    var self = this;
    this.dataManager.getShopInvoiceData(shopId, addressId).then(function(response) {
        if (response.status === 1) {
            self.populateFinalbillingModal(shopId, addressId, response.data, response.data_source, response.is_approved);
            $('#finalbilling-modal').modal('show');
        } else {
            self.showError("Fejl ved hentning af faktureringdata: " + response.error);
        }
    }).catch(function(error) {
        self.showError("Netværksfejl ved hentning af faktureringdata");
    });
};

// getShopInvoiceData moved to FinalbillingData module

Finalbilling.prototype.populateFinalbillingModal = function(shopId, addressId, invoiceData, dataSource, isApproved) {
    // Set hidden fields
    $('#invoice-shop-id').val(shopId);
    $('#invoice-address-id').val(addressId);
    $('#invoice-id').val(invoiceData.id || '');

    // Show data source indication with approval status
    this.formManager.showDataSourceIndication(dataSource, isApproved);

    var self = this;
    if (dataSource === 'shop_metadata' || dataSource === 'new') {
        // Brug shop_metadata direkte eller load metadata hvis new
        if (dataSource === 'new') {
            this.dataManager.loadShopMetadataForDefaults(shopId).then(function(metadata) {
                self.formManager.populateFormFields(metadata ? metadata.attributes : {});
            });
        } else {
            self.formManager.populateFormFields(invoiceData);
        }
    } else {
        // Brug shop_invoice2 data direkte
        self.formManager.populateFormFields(invoiceData);
    }
};

// loadShopMetadataForDefaults moved to FinalbillingData module

// Form methods moved to FinalbillingForm module

// populateFormFields moved to FinalbillingForm module

Finalbilling.prototype.saveFinalbillingData = function() {
    var self = this;
    var formData = this.formManager.collectFormData();

    $('#save-finalbilling-btn').prop('disabled', true).text('Gemmer...');
    $('.save_finalbilling_button').prop('disabled', true).text('Gemmer...');

    this.dataManager.postFinalbillingData(formData).then(function(response) {
        if (response.status === 1) {
            self.showSuccess("Finalbilling gemt succesfuldt");
            $('#finalbilling-modal').modal('hide');

            // Refresh current view
            if (self.currentMode === 'oversigt') {
                self.loadShopsOversigt();
            } else if (self.currentMode === 'fakturering') {
                self.loadFinalbilling();
            }
        } else {
            self.showError("Fejl ved gemning: " + response.error);
        }

        $('#save-finalbilling-btn').prop('disabled', false).text('Gem finalbilling');
        $('.save_finalbilling_button').prop('disabled', false).text('Gem');
    }).catch(function(error) {
        self.showError("Netværksfejl ved gemning af data");
        $('#save-finalbilling-btn').prop('disabled', false).text('Gem finalbilling');
        $('.save_finalbilling_button').prop('disabled', false).text('Gem');
    });
};

Finalbilling.prototype.approveFinalbilling = function(shopId, addressId) {
    var self = this;
    
    this.dataManager.approveFinalbilling(shopId, addressId).then(function(response) {
        if (response.status === 1) {
            self.showSuccess("Finalbilling godkendt succesfuldt");
            
            // Refresh current view
            if (self.currentMode === 'oversigt') {
                self.loadShopsOversigt();
            }
        } else {
            self.showError("Fejl ved godkendelse: " + response.error);
        }
    }).catch(function(error) {
        self.showError("Netværksfejl ved godkendelse");
    });
};

Finalbilling.prototype.removeApprovalFinalbilling = function(shopId, addressId) {
    var self = this;
    
    if (confirm('Er du sikker på, at du vil fjerne godkendelsen? Dette vil kræve en ny godkendelse efter eventuelle ændringer.')) {
        this.dataManager.removeApprovalFinalbilling(shopId, addressId).then(function(response) {
            if (response.status === 1) {
                self.showSuccess("Godkendelse fjernet succesfuldt");
                
                // Refresh current view
                if (self.currentMode === 'oversigt') {
                    self.loadShopsOversigt();
                }
            } else {
                self.showError("Fejl ved fjernelse af godkendelse: " + response.error);
            }
        }).catch(function(error) {
            self.showError("Netværksfejl ved fjernelse af godkendelse");
        });
    }
};

// collectFormData moved to FinalbillingForm module

// Utility methods
Finalbilling.prototype.formatDate = function(dateString) {
    if (!dateString) return '-';
    var date = new Date(dateString);
    return date.toLocaleDateString('da-DK');
};

Finalbilling.prototype.showError = function(message) {
    this.showToast(message, 'error');
};

Finalbilling.prototype.showSuccess = function(message) {
    this.showToast(message, 'success');
};

Finalbilling.prototype.showToast = function(message, type) {
    type = type || 'info';
    console.log(type + ': ' + message);
    alert(message); // Simple fallback
};

// Make class available globally
if (typeof window !== 'undefined') {
    window.Finalbilling = Finalbilling;
}

// ES6 module export
export default Finalbilling;