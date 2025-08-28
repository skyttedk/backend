export default class PresentComplaint {

    static searchform() {
        return `
        <div class="cardshop present-complaint-header">
            <div style="position: relative; margin-bottom: 15px;">
                <h4 style="margin: 0; color: #333;">Reklamationsoversigt</h4>
                <small style="color: #666;">Samlet oversigt over alle reklamationer på tværs af butikker</small>
            </div>
            
            <div class="search-controls" style="margin-bottom: 15px;">
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input 
                        autocomplete="off" 
                        id="complaint-search" 
                        type="text" 
                        placeholder="Søg reklamationer (virksomhed, bruger, butik, indhold...)"
                        style="flex: 1; min-width: 200px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                        onClick="this.select();">
                    
                    <button id="clear-search" class="btn btn-secondary btn-sm" title="Ryd søgning">
                        Ryd
                    </button>
                    
                    <button id="refresh-data" class="btn btn-primary btn-sm" title="Opdater data">
                         Opdater
                    </button>
                    
                    <button id="export-csv" class="btn btn-success btn-sm" title="Eksporter til CSV">
                        Eksporter CSV
                    </button>
                </div>
            </div>

            <div class="filter-controls" style="margin-bottom: 15px;">
                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                    <button class="btn btn-outline-secondary btn-sm view-filter active" data-filter="all">
                        Alle Reklamationer
                    </button>
                    <button class="btn btn-outline-secondary btn-sm view-filter" data-filter="recent">
                        Seneste (7 dage)
                    </button>
                </div>
            </div>

            <div id="status-indicator" style="font-size: 12px; color: #666; margin-bottom: 10px;">
                Indlæser reklamationer...
            </div>
        </div>`;
    }

    static complaintsList() {
        return `
        <div class="cardshop present-complaint-content">
            <div id="complaints-list" style="max-height: 70vh; overflow-y: auto;">
                <!-- Complaints will be loaded here -->
            </div>
        </div>`;
    }

    static loadingIndicator() {
        return `
        <div id="loading-indicator" style="display: none; text-align: center; padding: 20px;">
            <div style="color: #666;">
                <i class="fas fa-spinner fa-spin" style="font-size: 18px;"></i>
                <br><small>Indlæser reklamationer...</small>
            </div>
        </div>`;
    }

    static noResults() {
        return `
        <div style="text-align: center; padding: 40px; color: #666;">
            <div style="font-size: 18px; margin-bottom: 10px;">📄</div>
            <div>Ingen reklamationer fundet</div>
            <small style="color: #999;">Prøv at justere dine søgekriterier</small>
        </div>`;
    }

    static shopGroup(shopName, count) {
        return `
        <div class="shop-group" style="margin: 15px 0;">
            <div style="
                background: #f8f9fa; 
                padding: 8px 12px; 
                border-left: 4px solid #007bff; 
                font-weight: bold; 
                color: #333;
                font-size: 14px;
                margin-bottom: 5px;
            ">
                🏪 ${shopName} <span style="color: #666; font-weight: normal;">(${count} reklamationer)</span>
            </div>
        </div>`;
    }

    static complaintItem(complaint) {
        // Better date handling with fallbacks
        let updatedDate = 'N/A';
        if (complaint.updated_date && complaint.updated_date !== '0000-00-00 00:00:00' && complaint.updated_date !== '') {
            try {
                let date = new Date(complaint.updated_date);
                if (!isNaN(date.getTime())) {
                    updatedDate = date.toLocaleDateString('da-DK');
                }
            } catch (e) {
                // Try created_date as fallback
                if (complaint.created_date && complaint.created_date !== '0000-00-00 00:00:00' && complaint.created_date !== '') {
                    try {
                        let date = new Date(complaint.created_date);
                        if (!isNaN(date.getTime())) {
                            updatedDate = date.toLocaleDateString('da-DK');
                        }
                    } catch (e2) {
                        updatedDate = 'Dato ikke tilgængelig';
                    }
                }
            }
        } else if (complaint.created_date && complaint.created_date !== '0000-00-00 00:00:00' && complaint.created_date !== '') {
            try {
                let date = new Date(complaint.created_date);
                if (!isNaN(date.getTime())) {
                    updatedDate = date.toLocaleDateString('da-DK');
                }
            } catch (e) {
                updatedDate = 'Dato ikke tilgængelig';
            }
        }

        let complaintPreview = complaint.complaint_txt ?
            (complaint.complaint_txt.length > 100 ?
                complaint.complaint_txt.substring(0, 100) + '...' :
                complaint.complaint_txt) : 'Ingen reklamationstekst';

        return `
        <div class="cardshop complaint-item" 
             data-user-id="${complaint.user_id}" 
             data-shop-id="${complaint.shop_id}"
             style="
                border: 1px solid #e0e0e0; 
                margin: 5px 0; 
                padding: 12px; 
                cursor: pointer; 
                background: white;
                border-radius: 4px;
                transition: all 0.2s ease;
             "
             onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#007bff';"
             onmouseout="this.style.backgroundColor='white'; this.style.borderColor='#e0e0e0';">
            
            <div style="display: flex; justify-content: between; align-items: flex-start; gap: 10px;">
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <div style="font-weight: bold; color: #333; font-size: 14px;">
                            ${complaint.company_name || 'Ukendt Virksomhed'}
                            ${complaint.cvr ? `<span style="color: #666; font-weight: normal; font-size: 12px;"> (CVR: ${complaint.cvr})</span>` : ''}
                        </div>
                        <div style="font-size: 11px; color: #999;">
                            ${updatedDate}
                        </div>
                    </div>
                    
                    <div style="color: #666; font-size: 13px; margin-bottom: 5px;">
                        👤 ${complaint.username || 'Intet brugernavn tilgængeligt'}
                    </div>
                    
                    <div style="color: #555; font-size: 12px; line-height: 1.4; margin-bottom: 5px;">
                        ${complaintPreview}
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                       <div style="font-size: 11px; color: #999;">
                           Bruger ID: ${complaint.user_id} | Virksomheds ID: ${complaint.company_id}
                       </div>
                   </div>
                </div>
            </div>
        </div>`;
    }

    static complaintDetailModal(complaintData) {
        // Better date handling with Danish locale
        let createdDate = 'N/A';
        if (complaintData.created_date && complaintData.created_date !== '0000-00-00 00:00:00' && complaintData.created_date !== '') {
            try {
                let date = new Date(complaintData.created_date);
                if (!isNaN(date.getTime())) {
                    createdDate = date.toLocaleDateString('da-DK') + ' ' + date.toLocaleTimeString('da-DK', {hour: '2-digit', minute: '2-digit'});
                }
            } catch (e) {
                createdDate = 'Dato ikke tilgængelig';
            }
        }

        let updatedDate = 'N/A';
        if (complaintData.updated_date && complaintData.updated_date !== '0000-00-00 00:00:00' && complaintData.updated_date !== '') {
            try {
                let date = new Date(complaintData.updated_date);
                if (!isNaN(date.getTime())) {
                    updatedDate = date.toLocaleDateString('da-DK') + ' ' + date.toLocaleTimeString('da-DK', {hour: '2-digit', minute: '2-digit'});
                }
            } catch (e) {
                updatedDate = 'Dato ikke tilgængelig';
            }
        }

        return `
        <div class="complaint-detail-modal">
            <div style="margin-bottom: 20px;">
                <h5 style="color: #333; margin-bottom: 15px;">Virksomhedsinformation</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Virksomhed:</strong> ${complaintData.company_name || 'N/A'}<br>
                        <strong>CVR:</strong> ${complaintData.cvr || 'N/A'}<br>
                        <strong>Kontakt:</strong> ${complaintData.contact_name || 'N/A'}<br>
                    </div>
                    <div class="col-md-6">
                        <strong>Telefon:</strong> ${complaintData.contact_phone || 'N/A'}<br>
                        <strong>Email:</strong> ${complaintData.contact_email || 'N/A'}<br>
                        <strong>Butik:</strong> ${complaintData.shop_name || 'N/A'}<br>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h5 style="color: #333; margin-bottom: 15px;">Brugerinformation</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Brugernavn:</strong> ${complaintData.username || 'N/A'}<br>
                        <strong>Bruger ID:</strong> ${complaintData.user_id || 'N/A'}<br>
                    </div>
                    <div class="col-md-6">
                        <strong>Virksomheds ID:</strong> ${complaintData.company_id || 'N/A'}<br>
                        <strong>Butiks ID:</strong> ${complaintData.shop_id || 'N/A'}<br>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h5 style="color: #333; margin-bottom: 15px;">Reklamationsdetaljer</h5>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border-left: 4px solid #dc3545;">
                    <div style="white-space: pre-wrap; line-height: 1.5;">
                        ${complaintData.complaint_txt || 'Ingen reklamationstekst tilgængelig'}
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h5 style="color: #333; margin-bottom: 15px;">Tidslinje</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Oprettet:</strong> ${createdDate}
                    </div>
                    <div class="col-md-6">
                        <strong>Sidst Opdateret:</strong> ${updatedDate}
                    </div>
                </div>
            </div>
        </div>`;
    }

    static modalCloseButton() {
        return `
        <button type="button" class="btn btn-secondary" id="complaint-modal-close">Luk</button>
        `;
    }

    static messageDisplay(message) {
        return `
        <div style="text-align: center; padding: 40px; color: #666;">
            <div style="font-size: 16px; margin-bottom: 10px;">ℹ️</div>
            <div>${message}</div>
        </div>`;
    }

    static errorDisplay(message) {
        return `
        <div style="text-align: center; padding: 40px; color: #dc3545;">
            <div style="font-size: 18px; margin-bottom: 10px;">⚠️</div>
            <div style="font-weight: bold;">Fejl</div>
            <div style="margin-top: 5px;">${message}</div>
        </div>`;
    }

    // Additional utility templates
    static statsCard(title, value, icon = "📊") {
        return `
        <div style="
            background: white; 
            border: 1px solid #e0e0e0; 
            border-radius: 4px; 
            padding: 15px; 
            text-align: center;
            margin: 5px;
        ">
            <div style="font-size: 24px; margin-bottom: 5px;">${icon}</div>
            <div style="font-size: 18px; font-weight: bold; color: #333;">${value}</div>
            <div style="font-size: 12px; color: #666;">${title}</div>
        </div>`;
    }

    static emptyState() {
        return `
        <div style="text-align: center; padding: 60px 20px; color: #666;">
            <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">📋</div>
            <h4 style="color: #666; margin-bottom: 10px;">Ingen Reklamationer Fundet</h4>
            <p style="margin-bottom: 20px;">Der er i øjeblikket ingen reklamationer at vise.</p>
            <button id="refresh-data" class="btn btn-primary">
                🔄 Opdater Data
            </button>
        </div>`;
    }
}