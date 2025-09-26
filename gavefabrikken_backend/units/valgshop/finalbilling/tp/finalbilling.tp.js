export default class TpFinalbilling {

    static oversigtsTemplate() {
        return `
            <div class="finalbilling-oversigt">
                <div class="header-section">
                    <h2><i class="fas fa-file-invoice-dollar"></i> Finalbilling Oversigt</h2>
                    <button id="refresh-data-btn" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Opdater data
                    </button>
                </div>

                <div id="loading-spinner" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Indlæser...</span>
                    </div>
                    <p class="mt-2">Indlæser shop data...</p>
                </div>

                <div id="shops-container">
                    <div id="shops-list"></div>
                </div>

                ${this.finalbillingModalTemplate()}
            </div>
        `;
    }

    static faktureringTemplate() {
        return `
            <div class="finalbilling-detail">
                <h2>Finalbilling for Shop</h2>
                <div id="finalbilling-container">
                    <p>Indlæser finalbilling data...</p>
                </div>
            </div>
        `;
    }

    static rapportTemplate() {
        return `
            <div class="finalbilling-rapporter">
                <h2>Finalbilling Rapporter</h2>
                <div id="rapporter-container">
                    <p>Indlæser rapport data...</p>
                </div>
            </div>
        `;
    }

    static shopCardTemplate(data) {
        const { shop, statusCounts, addresses } = data;

        return `
            <div class="card shop-card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-store"></i> ${shop.shop_name}
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2">
                                <span class="badge bg-warning">${statusCounts.pending} afventer</span>
                                <span class="badge bg-success">${statusCounts.approved} godkendt</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        ${this.addressItemsTemplate(addresses, shop.shop_id)}
                    </div>
                </div>
            </div>
        `;
    }

    static addressItemsTemplate(addresses, shopId) {
        if (!addresses || addresses.length === 0) {
            return '<div class="col-12"><p class="text-muted">Ingen adresser fundet</p></div>';
        }

        return addresses.map(address => {
            const isApproved = address.attributes.approved == 1;
            const statusText = isApproved ? 'Godkendt' : 'Afventer';
            const statusClass = isApproved ? 'bg-success' : 'bg-warning';
            
            const approvedDate = address.attributes.approved_date ? 
                ` (${new Date(address.attributes.approved_date).toLocaleDateString('da-DK')})` : '';

            return `
                <div class="col-md-6 mb-3">
                    <div class="card address-item h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-1">${address.attributes.address_name}</h6>
                                <span class="badge ${statusClass}">${statusText}${approvedDate}</span>
                            </div>
                            <p class="card-text text-muted small mb-2">
                                ${address.attributes.address}<br>
                                ${address.attributes.zip} ${address.attributes.city}
                            </p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm edit-finalbilling-btn" 
                                        data-shop-id="${shopId}" 
                                        data-address-id="${address.attributes.address_id}">
                                    <i class="fas fa-edit"></i> Rediger finalbilling
                                </button>
                                ${!isApproved ? `
                                    <button class="btn btn-success btn-sm approve-finalbilling-btn" 
                                            data-shop-id="${shopId}" 
                                            data-address-id="${address.attributes.address_id}">
                                        <i class="fas fa-check"></i> Godkend
                                    </button>
                                ` : `
                                    <button class="btn btn-outline-secondary btn-sm remove-approval-btn" 
                                            data-shop-id="${shopId}" 
                                            data-address-id="${address.attributes.address_id}">
                                        <i class="fas fa-times"></i> Fjern godkendelse
                                    </button>
                                `}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    static finalbillingModalTemplate() {
        return `
            <div class="modal fade" id="finalbilling-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-file-invoice"></i> Finalbilling
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${this.finalbillingFormTemplate()}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                            <button type="button" id="save-finalbilling-btn" class="btn btn-primary">
                                <i class="fas fa-save"></i> Gem finalbilling
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    static finalbillingFormTemplate() {
        return `
            <button style="float: right;margin:10px; background-color: chartreuse;" type="button" class="save_finalbilling_button">Gem</button>
            <form id="finalbillingForm" style="max-width: 700px;">
                <div class="accordion finalbilling-accordion" id="finalbillingFormAccordion">

                    <!-- Fakturainfo Section -->
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="heading-fakturainfo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-fakturainfo" aria-expanded="false" aria-controls="collapse-fakturainfo">
                                Fakturainfo
                            </button>
                        </h4>
                        <div id="collapse-fakturainfo" class="accordion-collapse collapse" aria-labelledby="heading-fakturainfo" data-bs-parent="#finalbillingFormAccordion">
                            <div class="accordion-body">
                                <!-- NAV debitornummer -->
                                <div class="form-group">
                                    <label for="nav_debitor_no">Kundenr i navision</label>
                                    <input type="text" class="form-control" id="nav_debitor_no">
                                </div>
                                <br>
                                
                                <!-- Fakturagebyr -->
                                <div class="form-group">
                                    <label for="invoice_fee">Fakturagebyr</label>
                                    <select class="form-control" id="invoice_fee">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="invoice_fee_value_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="invoice_fee_value">Indtast beløb</label>
                                                    <input type="number" class="form-control" id="invoice_fee_value">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <div class="form-group">
                                    <label for="user_count">Antal medarbejdere</label>
                                    <input type="number" class="form-control" id="user_count">
                                </div>
                                <br>
                                <div class="form-group">
                                     <label for="present_count">Antal gavevalg (Inkl. undervalg og donationer)</label>
                                    <input type="number" class="form-control" id="present_count">
                                </div>
                                <br>

                                <!-- Udenlandsk handel -->
                                <div class="form-group">
                                    <label for="is_foreign">Udenlandsk handel</label>
                                    <select class="form-control" id="is_foreign">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="group_is_foreign" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="foreign_names">Indtast land(e)</label>
                                                    <input type="text" class="form-control" id="foreign_names">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <label for="payment_terms">Betalingsbetingelser</label>
                                <div class="form-group">
                                    <textarea class="form-control" id="payment_terms" placeholder="Vælg betalingsbetingelser i dropdown eller skriv dem her"></textarea>
                                </div>
                                <br>
                                
                                <div class="form-group">
                                    <label for="requisition_no">Kundens reference</label><span> (PO eller lignende)</span>
                                    <input type="text" class="form-control" id="requisition_no">
                                </div>
                                <br>
                                
                                <!-- Særlige krav til fakturering -->
                                <div class="form-group">
                                    <label for="payment_special">Særlige krav til fakturering</label>
                                    <select class="form-control" id="payment_special">
                                       <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="payment_special_note_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="payment_special_note">Beskriv særlige krav</label>
                                                    <textarea class="form-control" id="payment_special_note" rows="3" placeholder="Indtast særlige krav til fakturering her..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <!-- Kontantrabat -->
                                <div class="form-group">
                                    <label for="discount_option">Kontantrabat</label>
                                    <select class="form-control" id="discount_option">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="discount_value_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="discount_value">Indtast rabat i %</label>
                                                    <input type="number" class="form-control" id="discount_value">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <div class="form-group">
                                    <label for="valgshop_fee">Valgshop gebyr</label>
                                    <input type="text" class="form-control" id="valgshop_fee">
                                </div>
                                <br>
                                
                                <div class="form-group">
                                    <label for="environment_fee">Miljøgebyr</label>
                                    <select class="form-control" id="environment_fee">
                                          <option value="1">Ja</option>
                                        <option value="0">Nej</option>
                                    </select>
                                </div>
                                <br>

                                <div class="form-group">
                                    <label for="payment_note">Betalings note</label>
                                    <textarea class="form-control" id="payment_note" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Levering og fragt Section -->
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="heading-levering">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-levering" aria-expanded="false" aria-controls="collapse-levering">
                                Levering og fragt
                            </button>
                        </h4>
                        <div id="collapse-levering" class="accordion-collapse collapse" aria-labelledby="heading-levering" data-bs-parent="#finalbillingFormAccordion">
                            <div class="accordion-body">
                                <div class="form-group">
                                    <label for="delivery_date">Leveringsdato</label>
                                    <input type="date" class="form-control" id="delivery_date">
                                </div>
                                <br>
                                
                                <div class="form-group">
                                    <label for="handover_date">Udlevering af gaver ved kunden</label>
                                    <input type="date" class="form-control" id="handover_date">
                                </div>
                                <br>
                                
                                <!-- Flere leveringsadresser -->
                                <div class="form-group">
                                    <label for="multiple_deliveries">Flere leveringsadresser</label>
                                    <select class="form-control" id="multiple_deliveries">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="multiple_deliveries_data_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="multiple_deliveries_data">Leveringsadresse data</label>
                                                    <textarea class="form-control" id="multiple_deliveries_data" rows="3" placeholder="Beskriv leveringsadresse detaljer..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <!-- Privatlevering -->
                                <div class="form-group">
                                    <label for="private_delivery">Privatlevering</label>
                                    <select class="form-control" id="private_delivery">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                </div>
                                <br>
                                
                                <div class="form-group">
                                    <label for="privatedelivery_price">Privatlevering pris</label>
                                    <input type="number" class="form-control" id="privatedelivery_price" step="0.01">
                                </div>
                                <br>

                                <!-- Udenlandslevering -->
                                <div class="form-group">
                                    <label for="foreign_delivery">Udenlandslevering</label>
                                    <select class="form-control" id="foreign_delivery" name="foreign_delivery">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                        <option value="2">Delvis</option>
                                    </select>
                                </div>
                                <br>
                                
                                <div class="form-group">
                                    <label for="foreign_delivery_names">Udenlandske navne</label>
                                    <textarea class="form-control" id="foreign_delivery_names" rows="2"></textarea>
                                </div>
                                <br>
                                
                                <div class="form-group">
                                    <label for="foreign_delivery_date">Udenlandsk leveringsdato</label>
                                    <textarea class="form-control" id="foreign_delivery_date" rows="2" placeholder="Beskriv leveringsdatoer for udenlandske adresser..."></textarea>
                                </div>
                                <br>

                                <!-- DOT -->
                                <div class="form-group">
                                    <label for="dot_use">Ønskes DOT</label>
                                    <select class="form-control" id="dot_use">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="dot_fields" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="dot_amount">Antal adresser med DOT</label>
                                                    <input type="number" class="form-control" id="dot_amount">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dot_price">Pris for dot levering (pr levering)</label>
                                                    <input type="number" class="form-control" id="dot_price" step="0.01">
                                                </div>
                                                <div class="form-group">
                                                    <label for="dot_note">Note til dot levering</label>
                                                    <textarea class="form-control" id="dot_note"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <!-- Opbæring -->
                                <div class="form-group">
                                    <label for="carryup_use">Ønskes opbæring</label>
                                    <select class="form-control" id="carryup_use">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="carryup_fields" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="carryup_amount">Antal adresser med opbæring</label>
                                                    <input type="number" class="form-control" id="carryup_amount">
                                                </div>
                                                <div class="form-group">
                                                    <label for="carryup_price">Pris for opbæring (pr levering)</label>
                                                    <input type="number" class="form-control" id="carryup_price" step="0.01">
                                                </div>
                                                <div class="form-group">
                                                    <label for="carryup_note">Note til opbæring</label>
                                                    <textarea class="form-control" id="carryup_note"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <!-- Leveringsbetingelser -->
                                <div class="form-group">
                                    <label for="deliveryprice_option">Leveringsbetingelser</label>
                                    <select class="form-control" id="deliveryprice_option" >
                                        <option value="0">Skal udfyldes</option>
                                        <option value="1">Fastpris</option>
                                        <option value="2">Ab lager</option>
                                        <option value="3">Frit leveret</option>
                                    </select>
                                    
                                    <div id="deliveryprice_amount_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="deliveryprice_amount">Indtast værdi</label>
                                                    <input type="number" class="form-control" id="deliveryprice_amount">
                                                </div>
                                                <div class="form-group">
                                                    <label for="deliveryprice_note">Note/beskrivelse til fast pris</label>
                                                    <textarea class="form-control" id="deliveryprice_note"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>

                                <div class="form-group">
                                    <label for="delivery_terms">Leveringsbetingelser</label>
                                    <textarea class="form-control" id="delivery_terms" rows="2"></textarea>
                                </div>
                                <br>

                                <div class="form-group">
                                    <label for="delivery_note_internal">Vigtigt intern fragtnote</label>
                                    <textarea class="form-control" id="delivery_note_internal"></textarea>
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="delivery_note_external">Vigtigt ekstern fragtnote (til fragtmand / ordrebekræftelse)</label>
                                    <textarea class="form-control" id="delivery_note_external"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gaver Section -->
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="heading-gaver">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-gaver" aria-expanded="false" aria-controls="collapse-gaver">
                                Gaver
                            </button>
                        </h4>
                        <div id="collapse-gaver" class="accordion-collapse collapse" aria-labelledby="heading-gaver" data-bs-parent="#finalbillingFormAccordion">
                            <div class="accordion-body">
                                <!-- Plant et træ -->
                                <div class="form-group">
                                    <label for="plant_tree">Plant et træ</label>
                                    <select class="form-control" id="plant_tree">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="plant_tree_price_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="plant_tree_price">Pris pr. træ</label>
                                                    <input type="number" class="form-control" id="plant_tree_price" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                
                                 <!-- Julekort -->
                                 <div class="form-group">
                                    <label for="present_papercard">Julekort</label>
                                    <select class="form-control" id="present_papercard">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="present_papercard_price_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="present_papercard_price">Aftalt pris på julekort</label>
                                                    <input type="number" class="form-control" id="present_papercard_price" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                
                                <!-- Brug indpakning -->
                                <div class="form-group">
                                    <label for="present_wrap">Brug indpakning</label>
                                    <select class="form-control" id="present_wrap">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="present_wrap_price_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="present_wrap_price">Pris pr. gave</label>
                                                    <input type="number" class="form-control" id="present_wrap_price" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                
                                  <!-- Brug autogave -->
                                  <div class="form-group">
                                    <label for="autogave_use">Brug autogave</label>
                                    <select class="form-control" id="autogave_use">
                                        <option value="-1">Ikke taget stilling</option>
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="autogave_fields" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="autogave_itemno">Autoagave varenr</label>
                                                    <input type="text" class="form-control" id="autogave_itemno">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               <br>
                               
                               <!-- Budget felter -->
                                <div class="form-group">
                                    <label for="budget">Budget</label>
                                    <input type="number" class="form-control" id="budget" step="0.01">
                                </div>
                                <br>
                                
                                <div class="form-group">
                                    <label for="flex_budget">Fleksibelt budget</label>
                                    <select class="form-control" id="flex_budget">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                </div>
                                <br>
                               
                               <!-- Navnelabels -->
                               <div class="form-group">
                                    <label for="present_nametag">Navnelabels</label>
                                    <select class="form-control" id="present_nametag">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="present_nametag_price_group" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="present_nametag_price">Pris pr. gave</label>
                                                    <input type="number" class="form-control" id="present_nametag_price" step="0.01">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               <br>
                               
                      <!-- Vigtig information til pakning -->
                      <div class="form-group">
                        <label for="handling_special">Vigtig information til pakning</label>
                        <select class="form-control" id="handling_special">
                            <option value="0">Nej</option>
                            <option value="1">Ja</option>
                        </select>
                        
                        <div id="group_handling_special" style="display: none; margin-top: 10px;">
                            <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="handling_notes">Note om special håndtering / særlig pak</label>
                                        <textarea class="form-control" id="handling_notes" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                               <br>

                               <!-- Udlån Section -->
                               <hr style="margin: 30px 0; border-top: 2px solid #dee2e6;">

                               <h5 style="color: #6c757d; margin-bottom: 20px;">
                                   <i class="fa fa-handshake-o"></i> Udlån (Dette bør undgås)
                               </h5>

                                <div class="form-group">
                                    <label for="loan_use">Udlån</label>
                                    <select class="form-control" id="loan_use">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                    
                                    <div id="loan_fields" style="display: none; margin-top: 10px;">
                                        <div class="card border-left-primary" style="border-left: 3px solid #007bff;">
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="loan_deliverydate">Udlån, dato for levering ved kunden</label>
                                                    <input type="date" class="form-control" id="loan_deliverydate">
                                                </div>
                                                <div class="form-group">
                                                    <label for="loan_pickupdate">Udlån, dato for afhentning</label>
                                                    <input type="date" class="form-control" id="loan_pickupdate">
                                                </div>
                                                <div class="form-group">
                                                    <label for="loan_notes">Udlåns noter</label>
                                                    <textarea class="form-control" id="loan_notes" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Andre noter Section -->
                    <div class="accordion-item">
                        <h4 class="accordion-header" id="heading-andre">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-andre" aria-expanded="false" aria-controls="collapse-andre">
                                Andre noter
                            </button>
                        </h4>
                        <div id="collapse-andre" class="accordion-collapse collapse" aria-labelledby="heading-andre" data-bs-parent="#finalbillingFormAccordion">
                            <div class="accordion-body">
                                <div class="form-group">
                                    <label for="other_notes">Andre noter</label>
                                    <textarea class="form-control" id="other_notes" rows="4"></textarea>
                                </div>
                                <br>
                                
                                <div class="form-group">
                                    <label for="otheragreements_note">Andre aftaler note</label>
                                    <textarea class="form-control" id="otheragreements_note" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- End of accordion -->

                <input type="hidden" id="invoice-shop-id">
                <input type="hidden" id="invoice-address-id">
                <input type="hidden" id="invoice-id">

            </form>
            <br>
            <button style="float: right;margin:10px; background-color: chartreuse;" type="button" class="save_finalbilling_button">Gem</button>
            <br><br><br>
        `;
    }

    static finalbillingDetailTemplate(data) {
        return `
            <div class="finalbilling-detail-content">
                <h3>Finalbilling for ${data.shop_name}</h3>
                <!-- Detail content here -->
            </div>
        `;
    }

    static rapporterTemplate(data) {
        return `
            <div class="rapporter-content">
                <h3>Finalbilling Rapporter</h3>
                <!-- Rapport tables here -->
            </div>
        `;
    }

    // Hjælpemetoder
    static formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('da-DK');
    }

    static getStatusText(approved) {
        return approved == 1 ? 'Godkendt' : 'Afventer';
    }

    static getStatusClass(approved) {
        return approved == 1 ? 'bg-success' : 'bg-warning';
    }
}