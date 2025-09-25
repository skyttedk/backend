// Sending Management Module
class SendingManager {
    constructor(api, dataManager) {
        this.api = api;
        this.dataManager = dataManager;
        this.selectedTemplate = null;
        this.selectedRecipients = [];
        this.currentStep = 1;
        this.currentSending = null;
        
        this.initializeEventHandlers();
    }

    initializeEventHandlers() {
        // Main tab navigation
        $(document).on('click', '#sendingesTab', () => {
            this.showSendingsTab();
        });

        $(document).on('click', '#contactsTab', () => {
            this.showContactsTab();
        });

        // Create sending modal
        $(document).on('click', '#createSendingBtn', () => {
            this.openCreateSendingModal();
        });

        // Modal step navigation
        $(document).on('click', '#nextToStep2Btn', () => {
            this.goToStep(2);
        });

        $(document).on('click', '#nextToStep3Btn', () => {
            this.goToStep(3);
        });

        $(document).on('click', '#backToStep1Btn', () => {
            this.goToStep(1);
        });

        $(document).on('click', '#backToStep2Btn', () => {
            this.goToStep(2);
        });

        $(document).on('click', '#nextToStep4Btn', () => {
            this.goToStep(4);
        });

        $(document).on('click', '#backToStep3Btn', () => {
            this.goToStep(3);
        });

        // Template card selection - updated to use select button
        $(document).on('click', '.template-select-btn', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const templateName = $(e.currentTarget).data('template');
            const card = $(e.currentTarget).closest('.template-card');
            this.selectTemplate(card);
        });

        // Template preview button
        $(document).on('click', '.template-preview-btn', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const templateName = $(e.currentTarget).data('template');
            this.showTemplatePreviewInSending(templateName);
        });

        // Test edited template button
        $(document).on('click', '.test-edited-template-btn', (e) => {
            e.preventDefault();
            const language = $(e.currentTarget).data('language');
            this.showEditedTemplateTestModal(language);
        });

        // Recipient selection
        $(document).on('change', '#selectAllContacts', (e) => {
            this.toggleAllRecipients(e.target.checked);
        });

        $(document).on('change', '.recipient-checkbox', () => {
            this.updateRecipientSelection();
        });

        // Language selection
        $(document).on('change', '#sendingLanguageSelect', () => {
            this.selectedLanguage = $('#sendingLanguageSelect').val();
            this.updateRecipientSelection();
        });

        // Mail server selection change
        $(document).on('change', '#sendingMailServerSelect', () => {
            this.selectedMailServer = $('#sendingMailServerSelect').val();
            this.selectMailServer(this.selectedMailServer);
            this.updateRecipientSelection();
        });
        
        // Save draft
        $(document).on('click', '#saveDraftBtn', () => {
            this.saveDraft();
        });

        // Confirm sending
        $(document).on('click', '#createSendingConfirmBtn', () => {
            this.createSending();
        });

        // Test email from sending creation
        $(document).on('click', '#sendingTestEmailBtn', () => {
            this.showSendingTestEmailModal();
        });

        // Sending actions
        $(document).on('click', '.view-sending-details', (e) => {
            const sendingId = $(e.currentTarget).data('sending-id');
            this.showSendingDetails(sendingId);
        });

        // Delete sending
        $(document).on('click', '.delete-sending', (e) => {
            const sendingId = $(e.currentTarget).data('sending-id');
            this.deleteSending(sendingId);
        });

        // Resume draft
        $(document).on('click', '.resume-draft', (e) => {
            const sendingId = $(e.currentTarget).data('sending-id');
            this.resumeDraft(sendingId);
        });

        // Refresh buttons
        $(document).on('click', '#refreshSendingsBtn', () => {
            this.loadSendings();
        });

        $(document).on('click', '#refreshContactsBtn', () => {
            this.loadContacts();
        });

        // Show employee sendings
        $(document).on('click', '.show-employee-sendings', (e) => {
            const employeeId = $(e.currentTarget).data('employee-id');
            this.showEmployeeSendings(employeeId);
        });

        // Edit employee
        $(document).on('click', '.edit-employee-btn', (e) => {
            const employeeId = $(e.currentTarget).data('employee-id');
            this.showEditEmployeeModal(employeeId);
        });

        // Save employee changes
        $(document).on('click', '#saveContactBtn', () => {
            this.saveEmployeeChanges();
        });

    }

    showSendingsTab() {
        $('.nav-link').removeClass('active');
        $('#sendingesTab').addClass('active');
        $('.tab-content').removeClass('active').hide();
        $('#sendingesContent').addClass('active').show();
        
        this.loadSendings();
    }

    showContactsTab() {
        $('.nav-link').removeClass('active');
        $('#contactsTab').addClass('active');
        $('.tab-content').removeClass('active').hide();
        $('#contactsContent').addClass('active').show();
        
        this.loadContacts();
    }

    async loadSendings() {
        await this.dataManager.loadSendings();
        this.renderSendingsTable();
        this.initializeSendingsTableFeatures();
    }

    async loadContacts() {
        await this.dataManager.loadEmployees();
        this.renderContactsTable();
        this.initializeContactsTableFeatures();
    }

    renderSendingsTable(sortBy = '', sortOrder = 'asc') {
        const tbody = $('#sendingsTableBody');
        tbody.empty();

        // Clone sendings array for sorting
        let sortedSendings = [...this.dataManager.sendings];

        // Apply sorting if requested
        if (sortBy) {
            sortedSendings.sort((a, b) => {
                let valueA, valueB;
                
                switch (sortBy) {
                    case 'id':
                        valueA = a.id;
                        valueB = b.id;
                        break;
                    case 'template':
                        valueA = a.template_name.toLowerCase();
                        valueB = b.template_name.toLowerCase();
                        break;
                    case 'date':
                        valueA = a.created_date;
                        valueB = b.created_date;
                        break;
                    case 'status':
                        valueA = a.status;
                        valueB = b.status;
                        break;
                    default:
                        return 0;
                }
                
                if (sortOrder === 'asc') {
                    return valueA < valueB ? -1 : valueA > valueB ? 1 : 0;
                } else {
                    return valueA > valueB ? -1 : valueA < valueB ? 1 : 0;
                }
            });
        }

        sortedSendings.forEach(sending => {
            const statusBadge = this.getStatusBadge(sending.status);
            
            const row = `
                <tr>
                    <td>#${sending.id}</td>
                    <td>${sending.template_name}</td>
                    <td>${this.formatDate(sending.created_date)}</td>
                    <td>${sending.total_recipients}</td>
                    <td><span class="badge bg-success">${sending.sent_count}</span></td>
                    <td><span class="badge bg-danger">${sending.error_count}</span></td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${this.getDraftButton(sending)}
                            <button class="btn btn-outline-primary view-sending-details" data-sending-id="${sending.id}">
                                <i class="fas fa-eye"></i> Detaljer
                            </button>
                            ${this.getDeleteButton(sending)}
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    initializeSendingsTableFeatures() {
        // Store current sort state for sendings
        if (!this.sendingsSort) {
            this.sendingsSort = { column: '', order: 'asc' };
        }
        
        // Initialize column sorting for sendings table
        $('#sendingsTable .sortable-header').off('click').on('click', (e) => {
            const header = $(e.currentTarget);
            const sortColumn = header.data('sort');
            
            // Determine sort order - Toggle between asc, desc, and no sort
            let sortOrder = 'asc';
            if (this.sendingsSort.column === sortColumn) {
                if (this.sendingsSort.order === 'asc') {
                    sortOrder = 'desc';
                } else if (this.sendingsSort.order === 'desc') {
                    // Reset to no sorting
                    sortOrder = '';
                }
            }
            
            // Update current sort state
            if (sortOrder === '') {
                this.sendingsSort = { column: '', order: 'asc' };
            } else {
                this.sendingsSort = { column: sortColumn, order: sortOrder };
            }
            
            // Update UI - remove all existing sort classes and icons
            $('#sendingsTable .sortable-header').removeClass('sort-asc sort-desc');
            $('#sendingsTable .sortable-header .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
            
            // Add current sort class and update icon if sorting
            if (sortOrder !== '') {
                header.addClass(`sort-${sortOrder}`);
                const iconClass = sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                header.find('.sort-icon').removeClass('fa-sort').addClass(iconClass);
            }
            
            // Re-render table with sort
            this.renderSendingsTable(sortOrder !== '' ? sortColumn : '', sortOrder || 'asc');
        });
    }

    renderContactsTable(searchTerm = '', sortBy = '', sortOrder = 'asc') {
        console.log('🔄 Rendering contacts table with search:', searchTerm, 'sort:', sortBy, sortOrder);
        
        const tbody = $('#contactsTableBody');
        tbody.empty();

        // Get and filter employees
        let filteredEmployees = [...this.dataManager.employees];

        // Apply search filter
        if (searchTerm) {
            const searchLower = searchTerm.toLowerCase();
            filteredEmployees = filteredEmployees.filter(employee => {
                const name = employee.name.toLowerCase();
                const email = employee.email.toLowerCase();
                const lastEmail = employee.sent_date ? this.formatDate(employee.sent_date).toLowerCase() : '';
                
                return name.includes(searchLower) || 
                       email.includes(searchLower) || 
                       lastEmail.includes(searchLower);
            });
        }

        // Apply sorting
        if (sortBy) {
            filteredEmployees.sort((a, b) => {
                let valueA, valueB;
                
                switch (sortBy) {
                    case 'name':
                        valueA = a.name.toLowerCase();
                        valueB = b.name.toLowerCase();
                        break;
                    case 'email':
                        valueA = a.email.toLowerCase();
                        valueB = b.email.toLowerCase();
                        break;
                    case 'date':
                        valueA = a.sent_date || '0000-00-00';
                        valueB = b.sent_date || '0000-00-00';
                        break;
                    default:
                        return 0;
                }
                
                if (sortOrder === 'asc') {
                    return valueA < valueB ? -1 : valueA > valueB ? 1 : 0;
                } else {
                    return valueA > valueB ? -1 : valueA < valueB ? 1 : 0;
                }
            });
        }

        // Render filtered and sorted employees
        filteredEmployees.forEach(employee => {
            // Use last_email_date if available, otherwise fall back to sent_date
            const lastEmailDate = employee.last_email_date || employee.sent_date;
            const lastEmail = lastEmailDate ? this.formatDate(lastEmailDate) : '-';
            
            // Show different columns based on shop type
            let rowContent = '';
            if (SHOP_TYPE === 'cardshop') {
                // Cardshop: show username, name, email
                rowContent = `
                    <td>${employee.username || '-'}</td>
                    <td>${employee.name}</td>
                    <td>${employee.email}</td>
                    <td>${lastEmail}</td>`;
            } else {
                // Valgshop: show only name and email
                rowContent = `
                    <td>${employee.name}</td>
                    <td>${employee.email}</td>
                    <td>${lastEmail}</td>`;
            }
            
            const row = `
                <tr>
                    ${rowContent}
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary edit-employee-btn" data-employee-id="${employee.id}" title="Rediger kontakt">
                                <i class="fas fa-edit"></i> Rediger
                            </button>
                            <button class="btn btn-outline-info show-employee-sendings" data-employee-id="${employee.id}" title="Vis forsendelser">
                                <i class="fas fa-paper-plane"></i> Forsendelser
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Show results count
        const totalCount = this.dataManager.employees.length;
        const filteredCount = filteredEmployees.length;
        
        if (searchTerm && filteredCount !== totalCount) {
            tbody.append(`
                <tr class="table-info">
                    <td colspan="4" class="text-center">
                        <small>Viser ${filteredCount} af ${totalCount} kontakter</small>
                    </td>
                </tr>
            `);
        }

        // Bind event handlers for the forsendelser buttons
        $('.show-employee-sendings').off('click').on('click', (e) => {
            e.preventDefault();
            const employeeId = parseInt($(e.currentTarget).data('employee-id'));
            this.showEmployeeSendings(employeeId);
        });

        console.log('✅ Contacts table rendered with', filteredEmployees.length, 'contacts');
    }

    initializeContactsTableFeatures() {
        console.log('🎯 Initializing contacts table search and sorting');
        
        // Store current sort state
        this.currentSort = { column: '', order: 'asc' };
        
        // Initialize search functionality
        this.initializeContactsSearch();
        
        // Initialize column sorting
        this.initializeColumnSorting();
        
        console.log('✅ Contacts table features initialized');
    }

    initializeContactsSearch() {
        const searchInput = $('#contactSearchInput');
        const clearBtn = $('#clearSearchBtn');
        
        // Search on input
        searchInput.on('input', (e) => {
            const searchTerm = e.target.value.trim();
            console.log('🔍 Searching for:', searchTerm);
            this.renderContactsTable(searchTerm, this.currentSort.column, this.currentSort.order);
        });
        
        // Clear search
        clearBtn.on('click', () => {
            console.log('🧹 Clearing search');
            searchInput.val('');
            this.renderContactsTable('', this.currentSort.column, this.currentSort.order);
        });
        
        // Clear search with Escape key
        searchInput.on('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.val('');
                this.renderContactsTable('', this.currentSort.column, this.currentSort.order);
            }
        });
    }

    initializeColumnSorting() {
        $('.sortable-header').on('click', (e) => {
            const header = $(e.currentTarget);
            const sortColumn = header.data('sort');
            
            console.log('🔀 Sorting by column:', sortColumn);
            
            // Determine sort order - Toggle between asc, desc, and no sort
            let sortOrder = 'asc';
            if (this.currentSort.column === sortColumn) {
                if (this.currentSort.order === 'asc') {
                    sortOrder = 'desc';
                } else if (this.currentSort.order === 'desc') {
                    // Reset to no sorting
                    sortOrder = '';
                }
            }
            
            // Update current sort state
            if (sortOrder === '') {
                this.currentSort = { column: '', order: 'asc' };
            } else {
                this.currentSort = { column: sortColumn, order: sortOrder };
            }
            
            // Update UI - remove all existing sort classes and icons
            $('.sortable-header').removeClass('sort-asc sort-desc');
            $('.sortable-header .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
            
            // Add current sort class and update icon if sorting
            if (sortOrder !== '') {
                header.addClass(`sort-${sortOrder}`);
                const iconClass = sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                header.find('.sort-icon').removeClass('fa-sort').addClass(iconClass);
            }
            
            // Re-render table with sort
            const searchTerm = $('#contactSearchInput').val().trim();
            this.renderContactsTable(searchTerm, sortOrder !== '' ? sortColumn : '', sortOrder || 'asc');
        });
    }

    getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning">Afventer</span>',
            'in_progress': '<span class="badge bg-primary">Sender</span>',
            'completed': '<span class="badge bg-success">Afsluttet</span>',
            'failed': '<span class="badge bg-danger">Fejlet</span>',
            'draft': '<span class="badge bg-info"><i class="fas fa-save"></i> Klade</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">Ukendt</span>';
    }

    getEmailStatusBadge(status) {
        const badges = {
            'sent': '<span class="badge bg-success"><i class="fas fa-check"></i> Sendt</span>',
            'pending': '<span class="badge bg-warning"><i class="fas fa-clock"></i> Afventer</span>',
            'error': '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Fejl</span>',
            'failed': '<span class="badge bg-danger"><i class="fas fa-times"></i> Fejlet</span>',
            'unknown': '<span class="badge bg-secondary"><i class="fas fa-question"></i> Ukendt</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">-</span>';
    }

    getDeleteButton(sending) {
        // Only allow deletion of sendings that haven't been sent yet
        const deletableStatuses = ['draft', 'pending', 'failed'];
        
        if (deletableStatuses.includes(sending.status)) {
            return `<button class="btn btn-outline-danger delete-sending" data-sending-id="${sending.id}" title="Slet forsendelse">
                        <i class="fas fa-trash"></i>
                    </button>`;
        } else {
            return `<button class="btn btn-outline-secondary" disabled title="Kan ikke slettes - allerede sendt">
                        <i class="fas fa-ban"></i>
                    </button>`;
        }
    }

    getDraftButton(sending) {
        // Only show "Klade" button for draft status and non-sent statuses
        if (sending.status === 'draft') {
            return `<button class="btn btn-warning resume-draft" data-sending-id="${sending.id}" title="Genoptag klade">
                        <i class="fas fa-edit"></i> Klade
                    </button>`;
        }
        return '';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('da-DK') + ' ' + date.toLocaleTimeString('da-DK', {hour: '2-digit', minute: '2-digit'});
    }

    openCreateSendingModal() {
        this.resetModal();
        this.loadTemplateSelector();
        this.loadRecipientSelector();
        this.loadMailServerSelector();
        $('#createSendingModal').modal('show');
    }

    resetModal() {
        this.selectedTemplate = null;
        this.selectedRecipients = [];
        this.selectedLanguage = null;
        this.selectedMailServer = null;
        this.editingTemplate = null; // Reset editing template
        this.resumingDraftId = null; // Reset draft resuming flag
        this.currentStep = 1;
        this.goToStep(1);
        $('.template-card').removeClass('selected');
        $('.recipient-checkbox').prop('checked', false);
        $('#selectAllContacts').prop('checked', false);
        // Clear and reset language dropdown
        $('#sendingLanguageSelect').empty().append('<option value="">Vælg sprog...</option>');
        // Clear and reset mail server dropdown
        $('#sendingMailServerSelect').empty().append('<option value="">Vælg mail server...</option>');
        $('#mailServerStatus').removeClass('bg-success bg-warning bg-danger').addClass('bg-secondary').html('<i class="fas fa-circle"></i> Vælg server');
        // Clear editing interface
        $('#editingLanguageTabs').empty();
        $('#editingLanguageTabContent').empty();
        $('#editingTemplateName').text('Skabelon');
        $('#nextToStep2Btn').prop('disabled', true);
        $('#nextToStep3Btn').prop('disabled', true);
        $('#nextToStep4Btn').prop('disabled', true);
        // Ensure back button is visible for new sendings
        $('#backToStep1Btn').show();
    }

    goToStep(step) {
        this.currentStep = step;
        $('.sending-step').hide();
        $(`#step${step}`).show();
        
        // Load template editing interface when navigating to step 2
        if (step === 2) {
            this.loadTemplateEditingInterface();
            // Enable next button since template editing is always valid
            $('#nextToStep3Btn').prop('disabled', false);
            
            // Hide or disable back button if resuming a draft
            if (this.resumingDraftId) {
                $('#backToStep1Btn').hide();
            } else {
                $('#backToStep1Btn').show();
            }
        }
        
        // Populate language dropdown when navigating to step 3 (recipients)
        if (step === 3) {
            this.populateLanguageDropdown();
            this.loadMailServerSelector();
            this.loadRecipientSelector();
            
            // If resuming a draft, restore selections
            if (this.resumingDraftId) {
                setTimeout(() => {
                    // Restore language selection
                    if (this.selectedLanguage) {
                        $('#sendingLanguageSelect').val(this.selectedLanguage);
                    }
                    
                    // Restore mail server selection
                    if (this.selectedMailServer) {
                        $('#sendingMailServerSelect').val(this.selectedMailServer);
                        this.selectMailServer(this.selectedMailServer);
                    }
                    
                    // Restore recipient selections
                    this.selectedRecipients.forEach(recipientId => {
                        $(`.recipient-checkbox[value="${recipientId}"]`).prop('checked', true);
                    });
                    
                    this.updateRecipientSelection();
                }, 200);
            }
        }
        
        // Update step 4 preview when navigating to it (confirmation)
        if (step === 4) {
            this.updateStep3Preview(); // Rename to updateConfirmationPreview later
            
            // Update button text if resuming a draft
            if (this.resumingDraftId) {
                $('#saveDraftBtn').html('<i class="fas fa-save"></i> Opdater Klade');
            } else {
                $('#saveDraftBtn').html('<i class="fas fa-save"></i> Gem som Klade');
            }
        }
    }

    async loadTemplateSelector() {
        await this.dataManager.loadTemplates();
        const container = $('#templateSelector');
        container.empty();

        // Group templates by name
        const templateGroups = {};
        this.dataManager.templates.forEach(template => {
            if (!templateGroups[template.group_name]) {
                templateGroups[template.group_name] = template;
            }
        });

        Object.values(templateGroups).forEach(template => {
            const card = `
                <div class="col-md-6 mb-3">
                    <div class="card template-card" data-template="${template.group_name}">
                        <div class="card-body text-center">
                            <h6 class="card-title">${template.group_name}</h6>
                            <p class="card-text">
                                <small class="text-muted">${template.languages.length} sprog tilgængelige</small>
                            </p>
                            <div class="template-languages mb-3">
                                ${template.languages.map(lang => 
                                    `<span class="badge bg-secondary me-1">${this.dataManager.getLanguageName(lang.language)}</span>`
                                ).join('')}
                            </div>
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-outline-info btn-sm template-preview-btn" 
                                        data-template="${template.group_name}" title="Forhåndsvis skabelon">
                                    <i class="fas fa-eye"></i> Forhåndsvis
                                </button>
                                <button type="button" class="btn btn-primary btn-sm template-select-btn" 
                                        data-template="${template.group_name}">
                                    <i class="fas fa-check"></i> Vælg
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    async loadRecipientSelector() {
        await this.dataManager.loadEmployees();
        const tbody = $('#recipientSelector');
        tbody.empty();

        this.dataManager.employees.forEach(employee => {
            const row = `
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input recipient-checkbox" value="${employee.id}">
                    </td>
                    <td>${employee.name}</td>
                    <td>${employee.email}</td>
                    <td>${employee.password || 'N/A'}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    populateLanguageDropdown() {
        const languageSelect = $('#sendingLanguageSelect');
        languageSelect.empty();
        languageSelect.append('<option value="">Vælg sprog...</option>');
        
        if (!this.selectedTemplate) {
            console.warn('No template selected for language population');
            return;
        }

        const template = this.dataManager.findTemplateByName(this.selectedTemplate);
        if (!template) {
            console.warn('Template not found:', this.selectedTemplate);
            return;
        }

        // Add only languages that exist in the selected template
        template.languages.forEach(lang => {
            const languageName = this.dataManager.getLanguageName(lang.language);
            languageSelect.append(`<option value="${lang.language}">${languageName} - ${lang.subject}</option>`);
        });

        console.log('Populated language dropdown with', template.languages.length, 'languages for template:', this.selectedTemplate);
        
        // Call validation check after population
        setTimeout(() => {
            this.updateRecipientSelection();
        }, 100);
    }

    async loadMailServerSelector() {
        const mailServers = this.dataManager.getMailServers();
        const serverSelect = $('#sendingMailServerSelect');
        serverSelect.empty();
        serverSelect.append('<option value="">Vælg mail server...</option>');
        
        mailServers.forEach(server => {
            const statusIcon = server.status === 'active' ? '🟢' : '🔴';
            const isDefault = server.is_default ? ' (Standard)' : '';
            serverSelect.append(`<option value="${server.id}" data-status="${server.status}">${statusIcon} ${server.name}${isDefault}</option>`);
        });

        // Add change event handler
        serverSelect.off('change').on('change', (e) => {
            this.selectMailServer(e.target.value);
        });
        
        console.log('Populated mail server dropdown with', mailServers.length, 'servers');
        
        // Call validation check after population
        setTimeout(() => {
            this.updateRecipientSelection();
        }, 100);
    }

    selectMailServer(serverId) {
        this.selectedMailServer = serverId;
        
        if (!serverId) {
            $('#mailServerStatus').removeClass('bg-success bg-warning bg-danger').addClass('bg-secondary')
                .html('<i class="fas fa-circle"></i> Vælg server');
            return;
        }
        
        const server = this.dataManager.getMailServers().find(s => s.id == serverId);
        if (server) {
            const statusClass = server.status === 'active' ? 'bg-success' : 'bg-danger';
            const statusText = server.status === 'active' ? 'Aktiv' : 'Inaktiv';
            $('#mailServerStatus').removeClass('bg-success bg-warning bg-danger bg-secondary').addClass(statusClass)
                .html(`<i class="fas fa-circle"></i> ${statusText}`);
        }
        
        // Update step 3 preview if we're on step 3
        if (this.currentStep === 3) {
            this.updateStep3Preview();
        }
        
        console.log('Selected mail server:', serverId);
    }

    loadTemplateEditingInterface() {
        if (!this.selectedTemplate) {
            console.warn('No template selected for editing');
            return;
        }

        const template = this.dataManager.findTemplateByName(this.selectedTemplate);
        if (!template) {
            console.warn('Template not found for editing:', this.selectedTemplate);
            return;
        }

        // Set template name with indication if resuming draft
        if (this.resumingDraftId) {
            $('#editingTemplateName').text(`${this.selectedTemplate} (Genoptaget Klade)`);
        } else {
            $('#editingTemplateName').text(this.selectedTemplate);
        }

        // Create a copy of the template for editing (so we don't modify the original)
        if (!this.editingTemplate) {
            this.editingTemplate = JSON.parse(JSON.stringify(template));
        }

        this.createEditingLanguageTabs(this.editingTemplate);
        
        // Initialize first tab immediately and others on demand
        setTimeout(() => {
            if (this.editingTemplate.languages.length > 0) {
                const firstLang = this.editingTemplate.languages[0].language;
                // Activate first tab
                $(`#editing-tab-${firstLang}`).tab('show');
                
                // Initialize first editor after tab is shown
                setTimeout(() => {
                    const firstEditorId = `editing-body-${firstLang}`;
                    const $firstTextarea = $(`#${firstEditorId}`);
                    if ($firstTextarea.length) {
                        this.initializeSingleEditor($firstTextarea, firstLang);
                    }
                    
                    // Fallback: Initialize all editors after a longer delay if they haven't been initialized
                    setTimeout(() => {
                        this.initializeEditingEditors();
                    }, 1000);
                }, 300);
            }
        }, 200);
        
        console.log('Template editing interface loaded for:', this.selectedTemplate);
    }

    createEditingLanguageTabs(template) {
        const tabsContainer = $('#editingLanguageTabs');
        const contentContainer = $('#editingLanguageTabContent');
        
        tabsContainer.empty();
        contentContainer.empty();
        
        template.languages.forEach((lang, index) => {
            const isActive = index === 0;
            const languageName = this.dataManager.getLanguageName(lang.language);
            
            // Create tab
            const tab = $(`
                <li class="nav-item" role="presentation">
                    <button class="nav-link ${isActive ? 'active' : ''}" 
                            id="editing-tab-${lang.language}" 
                            data-bs-toggle="tab" 
                            data-bs-target="#editing-pane-${lang.language}" 
                            type="button" 
                            role="tab">
                        ${languageName}
                    </button>
                </li>
            `);
            
            // Create content pane
            const pane = $(`
                <div class="tab-pane fade ${isActive ? 'show active' : ''}" 
                     id="editing-pane-${lang.language}" 
                     role="tabpanel">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Email Emne (${languageName})</label>
                                <input type="text" class="form-control editing-subject" value="${lang.subject}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">HTML Indhold</label>
                            <button type="button" class="btn btn-outline-success btn-sm test-edited-template-btn" 
                                    data-language="${lang.language}" title="Send test email med redigeret skabelon">
                                <i class="fas fa-paper-plane"></i> Test Email
                            </button>
                        </div>
                        <textarea class="form-control editing-body" id="editing-body-${lang.language}" rows="12">${lang.body}</textarea>
                    </div>
                </div>
            `);
            
            tabsContainer.append(tab);
            contentContainer.append(pane);
            
            // Add tab activation handler with multiple event types
            tab.find('button').on('shown.bs.tab click', (e) => {
                console.log('Tab event triggered:', e.type, lang.language);
                // Initialize Summernote for this tab if not already done
                setTimeout(() => {
                    const editorId = `editing-body-${lang.language}`;
                    const $textarea = $(`#${editorId}`);
                    if ($textarea.length && !$textarea.hasClass('summernote-initialized')) {
                        console.log('Initializing editor for tab:', lang.language);
                        this.initializeSingleEditor($textarea, lang.language);
                    }
                }, 200);
            });
        });
    }

    captureEditedTemplateContent() {
        if (!this.editingTemplate) return;

        // Update each language with the current form values
        this.editingTemplate.languages.forEach(lang => {
            const pane = $(`#editing-pane-${lang.language}`);
            if (pane.length) {
                const subject = pane.find('.editing-subject').val();
                
                // Get content from Summernote editor
                const editorId = `editing-body-${lang.language}`;
                const $editor = $(`#${editorId}`);
                let body;
                
                if ($editor.hasClass('summernote-initialized')) {
                    body = $editor.summernote('code');
                } else {
                    body = $editor.val();
                }
                
                lang.subject = subject;
                lang.body = body;
            }
        });

        console.log('Captured edited template content:', this.editingTemplate);
    }

    initializeEditingEditors() {
        if (!this.editingTemplate) return;

        this.editingTemplate.languages.forEach(lang => {
            const editorId = `editing-body-${lang.language}`;
            const $textarea = $(`#${editorId}`);
            
            if ($textarea.length && !$textarea.hasClass('summernote-initialized')) {
                console.log('Initializing Summernote for:', editorId);
                
                // Destroy any existing summernote instance
                if ($textarea.summernote) {
                    $textarea.summernote('destroy');
                }
                
                // Get toolbar configuration - fixed for Summernote compatibility
                const toolbar = [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                    ['view', ['fullscreen', 'codeview']]
                ];

                $textarea.summernote({
                    height: 300,
                    toolbar: toolbar,
                    placeholder: 'Skriv dit indhold her...',
                    disableDragAndDrop: true,
                    callbacks: {
                        onInit: function() {
                            $textarea.addClass('summernote-initialized');
                            console.log('Summernote initialized for:', editorId);
                        },
                        onImageUpload: function(files) {
                            // Disable image upload
                            return false;
                        }
                    }
                });

                // Add placeholder functionality
                this.addPlaceholderButtons($textarea, lang.language);
            }
        });
        
        console.log('Initialized Summernote editors for template editing');
    }

    initializeSingleEditor($textarea, language) {
        const editorId = $textarea.attr('id');
        console.log('Initializing single Summernote editor for:', editorId);
        
        // Destroy any existing summernote instance
        if ($textarea.summernote) {
            $textarea.summernote('destroy');
        }
        
        // Get toolbar configuration - fixed for Summernote compatibility
        const toolbar = [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'hr']],
            ['view', ['fullscreen', 'codeview']]
        ];

        $textarea.summernote({
            height: 300,
            toolbar: toolbar,
            placeholder: 'Skriv dit indhold her...',
            disableDragAndDrop: true,
            callbacks: {
                onInit: function() {
                    $textarea.addClass('summernote-initialized');
                    console.log('Single Summernote initialized for:', editorId);
                },
                onImageUpload: function(files) {
                    // Disable image upload
                    return false;
                }
            }
        });

        // Add placeholder functionality
        this.addPlaceholderButtons($textarea, language);
    }

    addPlaceholderButtons($editor, language) {
        const placeholders = [
            { text: 'Navn', value: '{{name}}' },
            { text: 'Brugernavn', value: '{{username}}' },
            { text: 'Password', value: '{{password}}' },
            { text: 'Link', value: '{{link}}' },
            { text: 'Start Dato', value: '{{start_date}}' },
            { text: 'Slut Dato', value: '{{end_date}}' }
        ];

        // Add placeholder buttons to the editor toolbar  
        setTimeout(() => {
            const $toolbar = $editor.next('.note-editor').find('.note-toolbar');
            if ($toolbar.length) {
                // Remove any existing placeholder group
                $toolbar.find('.placeholder-group').remove();
                
                const placeholderGroup = $(`
                    <div class="note-btn-group btn-group placeholder-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="Placeholders" aria-expanded="false">
                            <i class="fas fa-code"></i> Placeholders
                        </button>
                        <ul class="dropdown-menu">
                            ${placeholders.map(ph => `
                                <li><a class="dropdown-item placeholder-btn" href="#" data-placeholder="${ph.value}">${ph.text}</a></li>
                            `).join('')}
                        </ul>
                    </div>
                `);
            
                $toolbar.append(placeholderGroup);
                
                // Add click handlers for placeholder buttons
                placeholderGroup.find('.placeholder-btn').on('click', function(e) {
                    e.preventDefault();
                    const placeholder = $(this).data('placeholder');
                    $editor.summernote('insertText', placeholder);
                });
            }
        }, 500);
    }

    showEditedTemplateTestModal(language) {
        if (!this.editingTemplate) {
            alert('Ingen skabelon tilgængelig for test');
            return;
        }

        // Get the edited content for the specific language
        const languageData = this.editingTemplate.languages.find(l => l.language === language);
        if (!languageData) {
            alert('Sprog ikke fundet i skabelon');
            return;
        }

        // Capture current form values before testing
        this.captureEditedTemplateContent();

        // Show test email modal with edited content
        const languageName = this.dataManager.getLanguageName(language);
        
        // Get first employee for test data
        const firstEmployee = this.dataManager.employees[0];
        if (!firstEmployee) {
            alert('Ingen medarbejder data tilgængelig for test');
            return;
        }

        const subject = languageData.subject
            .replace(/\{\{name\}\}/g, firstEmployee.name)
            .replace(/\{\{username\}\}/g, firstEmployee.username)
            .replace(/\{\{password\}\}/g, firstEmployee.password)
            .replace(/\{\{link\}\}/g, 'https://shop.gavefabrikken.dk/demo/')
            .replace(/\{\{start_date\}\}/g, '01/12/2024')
            .replace(/\{\{end_date\}\}/g, '31/12/2024');

        // Show the modal
        $('#testTemplateName').text(this.editingTemplate.group_name + ' (Redigeret)');
        $('#testTemplateLanguage').text(languageName);
        $('#testTemplateSubject').text(subject);
        
        // Store current test data
        this.currentTestData = {
            language: language,
            subject: subject,
            template: this.editingTemplate,
            employee: firstEmployee
        };
        
        $('#testEmailModal').modal('show');
    }

    async showTemplatePreviewInSending(groupName) {
        console.log('Preview template in sending modal:', groupName);
        
        const template = this.dataManager.findTemplateByName(groupName);
        if (!template) {
            alert('Skabelon ikke fundet');
            return;
        }

        // Show preview modal with template content
        const previewContent = template.languages.map(lang => {
            const languageName = this.dataManager.getLanguageName(lang.language);
            let processedBody = lang.body
                .replace(/\{\{name\}\}/g, 'John Doe')
                .replace(/\{\{username\}\}/g, 'john.doe')
                .replace(/\{\{password\}\}/g, 'TempPass123!')
                .replace(/\{\{link\}\}/g, '<a href="https://example.com">https://example.com</a>')
                .replace(/\{\{start_date\}\}/g, '01/12/2024')
                .replace(/\{\{end_date\}\}/g, '31/12/2024');

            return `
                <div class="mb-4">
                    <h6><span class="badge bg-secondary">${languageName}</span></h6>
                    <div class="border p-3" style="background: #f8f9fa;">
                        <strong>Emne:</strong> ${lang.subject}<br>
                        <strong>Indhold:</strong>
                        <div class="mt-2" style="background: white; padding: 15px; border-radius: 5px;">
                            ${processedBody}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        const modalHtml = `
            <div class="modal fade" id="sendingTemplatePreviewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Template Preview: ${groupName}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${previewContent}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                            <button type="button" class="btn btn-primary" onclick="$('#sendingTemplatePreviewModal').modal('hide'); $('.template-select-btn[data-template=\\'${groupName}\\']').click();">
                                <i class="fas fa-check"></i> Vælg Denne Skabelon
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing preview modal
        $('#sendingTemplatePreviewModal').remove();
        
        // Add and show modal
        $('body').append(modalHtml);
        $('#sendingTemplatePreviewModal').modal('show');
    }

    selectTemplate(card) {
        $('.template-card').removeClass('selected');
        card.addClass('selected');
        this.selectedTemplate = card.data('template');
        $('#nextToStep2Btn').prop('disabled', false);
    }

    toggleAllRecipients(checked) {
        $('.recipient-checkbox').prop('checked', checked);
        this.updateRecipientSelection();
    }

    updateRecipientSelection() {
        this.selectedRecipients = [];
        $('.recipient-checkbox:checked').each((i, checkbox) => {
            this.selectedRecipients.push(parseInt($(checkbox).val()));
        });

        // Check if recipients, language and mail server are selected
        const selectedLanguage = $('#sendingLanguageSelect').val();
        const selectedMailServer = $('#sendingMailServerSelect').val();
        const canProceed = this.selectedRecipients.length > 0 && selectedLanguage && selectedMailServer;
        
        console.log('Validation check:', {
            recipientsCount: this.selectedRecipients.length,
            selectedLanguage: selectedLanguage,
            selectedMailServer: selectedMailServer,
            canProceed: canProceed
        });
        
        $('#nextToStep4Btn').prop('disabled', !canProceed);
        
        // Update select all checkbox
        const totalCheckboxes = $('.recipient-checkbox').length;
        const checkedCheckboxes = $('.recipient-checkbox:checked').length;
        $('#selectAllContacts').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
        $('#selectAllContacts').prop('checked', checkedCheckboxes === totalCheckboxes);

        // Update step 4 preview
        if (this.currentStep === 4) {
            this.updateStep3Preview();
        }
    }

    updateStep3Preview() {
        // Update template info
        const template = this.dataManager.findTemplateByName(this.selectedTemplate);
        const selectedLanguage = $('#sendingLanguageSelect').val();
        const languageName = this.dataManager.getLanguageName(selectedLanguage);
        
        // Get mail server info
        const mailServerInfo = this.selectedMailServer ? 
            this.dataManager.getMailServers().find(s => s.id == this.selectedMailServer) : null;
        
        $('#selectedTemplateInfo').html(`
            <h6>${this.selectedTemplate}</h6>
            <p class="mb-1"><strong>Valgt sprog:</strong> ${languageName}</p>
            <p class="mb-1"><strong>Mail server:</strong> ${mailServerInfo ? mailServerInfo.name : 'Ikke valgt'}</p>
            <p class="mb-1"><strong>Tilgængelige sprog:</strong> ${template.languages.map(l => this.dataManager.getLanguageName(l.language)).join(', ')}</p>
            <p class="mb-0"><small class="text-muted">Alle emails sendes på ${languageName} via ${mailServerInfo ? mailServerInfo.name : 'valgte server'}</small></p>
        `);

        // Update recipients info
        const selectedEmployees = this.dataManager.employees.filter(emp => 
            this.selectedRecipients.includes(emp.id)
        );
        
        $('#selectedRecipientsInfo').html(`
            <p><strong>Antal modtagere:</strong> ${this.selectedRecipients.length}</p>
            <div class="recipient-list" style="max-height: 150px; overflow-y: auto;">
                ${selectedEmployees.map(emp => 
                    `<div class="d-flex justify-content-between mb-1">
                        <span>${emp.name}</span>
                        <small class="text-muted">${this.dataManager.getLanguageName(emp.language)}</small>
                    </div>`
                ).join('')}
            </div>
        `);
    }

    async createSending() {
        try {
            $('#createSendingConfirmBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Opretter...');
            
            // Get selected language and mail server
            const selectedLanguage = $('#sendingLanguageSelect').val();
            const selectedMailServer = this.selectedMailServer;
            
            // Capture edited template content if available
            let templateToUse = this.selectedTemplate;
            if (this.editingTemplate) {
                // Update editing template with current form values
                this.captureEditedTemplateContent();
                templateToUse = this.editingTemplate.group_name;
            }
            
            // Create sending via API (dummy)
            const sendingData = {
                template_name: templateToUse,
                recipient_ids: this.selectedRecipients,
                language: selectedLanguage,
                mail_server_id: selectedMailServer,
                custom_template: this.editingTemplate // Include edited template if available
            };

            // Create or update sending record
            let sending;
            if (this.resumingDraftId) {
                // Update existing draft
                sending = this.dataManager.updateSending(this.resumingDraftId, templateToUse, this.selectedRecipients, selectedLanguage, selectedMailServer, this.editingTemplate);
                sending.status = 'pending'; // Change from draft to pending
                sending.sent_date = new Date().toISOString().slice(0, 19).replace('T', ' '); // Set sent date
            } else {
                // Create new sending
                sending = this.dataManager.createSending(templateToUse, this.selectedRecipients, selectedLanguage, selectedMailServer, this.editingTemplate);
                sending.sent_date = new Date().toISOString().slice(0, 19).replace('T', ' '); // Set sent date
            }
            this.currentSending = sending;

            // Close modal and show progress
            $('#createSendingModal').modal('hide');
            this.showSendingProgress(sending);

            // Start processing simulation
            this.processSendingProgress(sending.id);

        } catch (error) {
            console.error('Error creating sending:', error);
            alert('Fejl ved oprettelse af forsendelse');
        } finally {
            $('#createSendingConfirmBtn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Opret Forsendelse');
        }
    }

    async saveDraft() {
        try {
            $('#saveDraftBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Gemmer...');
            
            // Get selected language and mail server
            const selectedLanguage = $('#sendingLanguageSelect').val();
            const selectedMailServer = this.selectedMailServer;
            
            // Validate that we have the minimum required data
            if (!this.selectedTemplate) {
                alert('Vælg venligst en skabelon før du gemmer kladen');
                return;
            }
            
            if (this.selectedRecipients.length === 0) {
                alert('Vælg venligst mindst én modtager før du gemmer kladen');
                return;
            }
            
            // Create draft data
            const draftData = {
                template_name: this.selectedTemplate,
                recipient_ids: this.selectedRecipients,
                language: selectedLanguage,
                mail_server_id: selectedMailServer,
                status: 'draft'
            };

            // Create or update draft record
            let draft;
            if (this.resumingDraftId) {
                // Update existing draft
                draft = this.dataManager.updateSending(this.resumingDraftId, this.selectedTemplate, this.selectedRecipients, selectedLanguage, selectedMailServer, this.editingTemplate);
            } else {
                // Create new draft
                draft = this.dataManager.createDraft(this.selectedTemplate, this.selectedRecipients, selectedLanguage, selectedMailServer, this.editingTemplate);
            }
            
            // Show success message
            this.showSuccessMessage(`Klade gemt succesfuldt! Du kan finde den under forsendelser med status "Klade".`);
            
            // Close modal and refresh sendings
            $('#createSendingModal').modal('hide');
            this.loadSendings();

        } catch (error) {
            console.error('Error saving draft:', error);
            alert('Fejl ved gemning af klade');
        } finally {
            $('#saveDraftBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Gem som Klade');
        }
    }

    showSuccessMessage(message) {
        const alert = $(`
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-check-circle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }

    showSendingProgress(sending) {
        $('#sendingProgressCard').show();
        $('#sendingProgressBar').css('width', '0%');
        $('#sendingStatus').text('Starter forsendelse...');
    }

    async processSendingProgress(sendingId) {
        let progress = 0;
        
        while (progress < 100) {
            await new Promise(resolve => setTimeout(resolve, 1000)); // Wait 1 second
            
            progress += 25; // Simulate 25% progress steps
            const sentCount = Math.floor((progress / 100) * this.selectedRecipients.length);
            const errorCount = Math.floor(Math.random() * 2); // Random 0-1 errors
            
            // Update progress
            this.dataManager.updateSendingProgress(sendingId, progress, sentCount, errorCount);
            
            // Update UI
            $('#sendingProgressBar').css('width', progress + '%');
            $('#sendingStatus').text(`Sender emails... ${progress}% fuldført (${sentCount}/${this.selectedRecipients.length})`);
            
            if (progress >= 100) {
                $('#sendingStatus').text('Forsendelse afsluttet!');
                setTimeout(() => {
                    $('#sendingProgressCard').hide();
                    this.loadSendings(); // Refresh table
                }, 2000);
                break;
            }
        }
    }

    async showSendingDetails(sendingId) {
        const sending = this.dataManager.getSendingById(sendingId);
        if (!sending) {
            alert('Forsendelse ikke fundet');
            return;
        }

        // Get recipient details
        const recipients = this.dataManager.employees.filter(emp => 
            sending.recipients.includes(emp.id)
        );

        // Calculate sent date based on status
        let sentDateInfo = 'Ikke sendt';
        if (sending.status === 'completed' && sending.sent_count > 0) {
            // For completed sendings, estimate sent date (created + some processing time)
            const createdDate = new Date(sending.created_date);
            const estimatedSentDate = new Date(createdDate.getTime() + (sending.total_recipients * 30000)); // 30 sec per recipient
            sentDateInfo = this.formatDate(estimatedSentDate.toISOString().slice(0, 19).replace('T', ' '));
        } else if (sending.status === 'in_progress') {
            sentDateInfo = 'Sender...';
        } else if (sending.sent_date) {
            sentDateInfo = this.formatDate(sending.sent_date);
        }

        const content = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Forsendelse Information</h6>
                    <table class="table table-sm">
                        <tr><td><strong>ID:</strong></td><td>#${sending.id}</td></tr>
                        <tr><td><strong>Skabelon:</strong></td><td>${sending.template_name}</td></tr>
                        <tr><td><strong>Oprettet:</strong></td><td>${this.formatDate(sending.created_date)}</td></tr>
                        <tr><td><strong>Sendt:</strong></td><td>${sentDateInfo}</td></tr>
                        <tr><td><strong>Status:</strong></td><td>${this.getStatusBadge(sending.status)}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Statistik</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Total modtagere:</strong></td><td>${sending.total_recipients}</td></tr>
                        <tr><td><strong>Sendt:</strong></td><td><span class="badge bg-success">${sending.sent_count}</span></td></tr>
                        <tr><td><strong>Fejl:</strong></td><td><span class="badge bg-danger">${sending.error_count}</span></td></tr>
                        <tr><td><strong>Progress:</strong></td><td>${sending.progress}%</td></tr>
                    </table>
                </div>
            </div>
            <hr>
            <h6>Modtagere</h6>
            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Navn</th>
                            <th>Email</th>
                            <th>Sprog</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${recipients.map(recipient => `
                            <tr>
                                <td>${recipient.name}</td>
                                <td>${recipient.email}</td>
                                <td>${this.dataManager.getLanguageName(recipient.language)}</td>
                                <td>${this.getEmailStatusBadge(recipient.email_status)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        $('#sendingDetailsContent').html(content);
        $('#sendingDetailsModal').modal('show');
    }

    async deleteSending(sendingId) {
        const sending = this.dataManager.getSendingById(sendingId);
        if (!sending) {
            alert('Forsendelse ikke fundet');
            return;
        }

        // Check if sending can be deleted
        const deletableStatuses = ['draft', 'pending', 'failed'];
        if (!deletableStatuses.includes(sending.status)) {
            alert('Denne forsendelse kan ikke slettes da den allerede er blevet behandlet eller sendt.');
            return;
        }

        // Confirm deletion
        const confirmMessage = `Er du sikker på at du vil slette forsendelsen "${sending.template_name}"?\n\nDenne handling kan ikke fortrydes.`;
        if (!confirm(confirmMessage)) {
            return;
        }

        try {
            // Remove from data manager
            const success = this.dataManager.deleteSending(sendingId);
            
            if (success) {
                // Show success message
                this.showSuccessMessage(`Forsendelsen "${sending.template_name}" blev slettet.`);
                
                // Refresh sendings table
                this.loadSendings();
                
                console.log('Sending deleted successfully:', sendingId);
            } else {
                alert('Fejl ved sletning af forsendelse');
            }
            
        } catch (error) {
            console.error('Error deleting sending:', error);
            alert('Fejl ved sletning af forsendelse. Prøv igen.');
        }
    }

    async resumeDraft(sendingId) {
        const sending = this.dataManager.getSendingById(sendingId);
        if (!sending) {
            alert('Klade ikke fundet');
            return;
        }

        if (sending.status !== 'draft') {
            alert('Kun kladder kan genoptages');
            return;
        }

        console.log('Resuming draft:', sending);

        // Restore the draft data to the modal
        this.selectedTemplate = sending.template_name;
        this.selectedRecipients = [...sending.recipients];
        this.selectedLanguage = sending.language;
        this.selectedMailServer = sending.mail_server_id;
        this.editingTemplate = sending.custom_template ? JSON.parse(JSON.stringify(sending.custom_template)) : null;
        this.resumingDraftId = sendingId; // Track that we're resuming a draft

        // Start from step 2 (template editing) since template is already selected
        this.currentStep = 2;
        
        // Open modal and go to step 2
        $('#createSendingModal').modal('show');
        this.goToStep(2);

        // Mark template as selected
        $(`.template-card[data-template="${this.selectedTemplate}"]`).addClass('selected');

        console.log('Draft resumed, starting at step 2');
    }

    showEmployeeSendings(employeeId) {
        console.log('🎯 CORRECT METHOD - showEmployeeSendings called for employee:', employeeId);
        
        const employee = this.dataManager.employees.find(emp => emp.id === employeeId);
        if (!employee) {
            alert('Medarbejder ikke fundet');
            return;
        }

        // Find all sendings that include this employee
        const employeeSendings = this.dataManager.sendings.filter(sending => 
            sending.recipients.includes(employeeId)
        );

        console.log('📧 Found sendings for employee:', employeeSendings.length);

        const content = `
            <div class="mb-3">
                <h6><i class="fas fa-user"></i> ${employee.name}</h6>
                <p class="text-muted">${employee.email} • ${this.dataManager.getLanguageName(employee.language)}</p>
            </div>
            <hr>
            <h6>Forsendelser (${employeeSendings.length})</h6>
            ${employeeSendings.length === 0 ? 
                '<div class="text-center text-muted p-4"><i class="fas fa-inbox fa-2x mb-2"></i><br>Ingen forsendelser endnu</div>' :
                `<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Skabelon</th>
                                <th>Oprettet</th>
                                <th>Afsendt</th>
                                <th>Email Status</th>
                                <th>Handlinger</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${employeeSendings.map(sending => {
                                // Get specific email status for this employee in this sending
                                const recipientStatus = sending.recipient_status && sending.recipient_status[employeeId];
                                const emailStatus = recipientStatus ? recipientStatus.status : employee.email_status || 'unknown';
                                const statusBadge = this.getEmailStatusBadge(emailStatus);
                                
                                // Get sent date - use recipient-specific sent date if available, otherwise use employee's last_email_date
                                let sentDate = 'Ikke sendt';
                                if (emailStatus === 'sent') {
                                    const sentTimestamp = recipientStatus?.sent_date || employee.last_email_date;
                                    if (sentTimestamp) {
                                        sentDate = this.formatDate(sentTimestamp);
                                    }
                                }
                                
                                return `
                                <tr>
                                    <td>#${sending.id}</td>
                                    <td>${sending.template_name}</td>
                                    <td>${this.formatDate(sending.created_date)}</td>
                                    <td><small class="text-muted">${sentDate}</small></td>
                                    <td>${statusBadge}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-mail-btn" data-employee="${employeeId}" data-sending="${sending.id}">
                                            <i class="fas fa-eye"></i> Vis Mail
                                        </button>
                                    </td>
                                </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>`
            }
        `;

        console.log('🔧 Setting content in modal');
        $('#sendingDetailsContent').html(content);
        $('#sendingDetailsModal .modal-title').text('Medarbejder Forsendelser');
        $('#sendingDetailsModal').modal('show');
        
        console.log('✅ Modal should now show with updated table');

        // Bind click handlers
        setTimeout(() => {
            $('.view-mail-btn').on('click', (e) => {
                console.log('👁️ View mail clicked');
                const empId = $(e.currentTarget).data('employee');
                const sendingId = $(e.currentTarget).data('sending');
                this.viewEmailDetails(empId, sendingId);
            });
            
            console.log('🔗 View mail event handler bound successfully');
        }, 100);
    }

    showEmployeeSendingsEnhanced(employeeId) {
        console.log('🎯 NEW METHOD - showEmployeeSendingsEnhanced called for employee:', employeeId);
        
        const employee = this.dataManager.employees.find(emp => emp.id === employeeId);
        if (!employee) {
            alert('Medarbejder ikke fundet');
            return;
        }

        // Find all sendings that include this employee
        const employeeSendings = this.dataManager.sendings.filter(sending => 
            sending.recipients.includes(employeeId)
        );
        
        console.log('📧 Found sendings:', employeeSendings.length);

        // Build simple, clean HTML
        const html = `
            <div class="mb-3">
                <h6><i class="fas fa-user"></i> ${employee.name}</h6>
                <p class="text-muted">${employee.email} • ${this.dataManager.getLanguageName(employee.language)}</p>
            </div>
            <hr>
            <h6>Forsendelser (${employeeSendings.length})</h6>
            
            ${employeeSendings.length === 0 ? 
                '<div class="alert alert-info">Ingen forsendelser fundet for denne kontakt.</div>' 
                : 
                `<table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Skabelon</th>
                            <th>Dato</th>
                            <th>Email Status</th>
                            <th>Handlinger</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${employeeSendings.map(sending => {
                            // Get specific email status for this employee in this sending
                            const recipientStatus = sending.recipient_status && sending.recipient_status[employeeId];
                            const emailStatus = recipientStatus ? recipientStatus.status : employee.email_status || 'unknown';
                            const statusBadge = this.getEmailStatusBadge(emailStatus);
                            
                            return `
                            <tr>
                                <td>#${sending.id}</td>
                                <td>${sending.template_name}</td>
                                <td>${this.formatDate(sending.created_date)}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-2 view-mail-btn" data-employee="${employeeId}" data-sending="${sending.id}">
                                        <i class="fas fa-eye"></i> Vis Mail
                                    </button>
                                    <button class="btn btn-sm btn-success resend-mail-btn" data-employee="${employeeId}" data-sending="${sending.id}" data-template="${sending.template_name}">
                                        <i class="fas fa-redo"></i> Gensend
                                    </button>
                                </td>
                            </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>`
            }
        `;

        console.log('🔧 Built HTML, length:', html.length);
        
        // Clear and set content
        $('#sendingDetailsContent').empty().html(html);
        $('#sendingDetailsModal .modal-title').text('Medarbejder Forsendelser');
        
        // Show modal
        $('#sendingDetailsModal').modal('show');
        
        console.log('✅ Modal shown with new content');

        // Bind click handlers with timeout to ensure DOM is ready
        setTimeout(() => {
            $('.view-mail-btn').on('click', (e) => {
                console.log('👁️ View mail clicked');
                const empId = $(e.currentTarget).data('employee');
                const sendingId = $(e.currentTarget).data('sending');
                this.viewEmailDetails(empId, sendingId);
            });

            $('.resend-mail-btn').on('click', (e) => {
                console.log('🔄 Resend mail clicked');
                const empId = $(e.currentTarget).data('employee');
                const sendingId = $(e.currentTarget).data('sending');
                const template = $(e.currentTarget).data('template');
                this.showResendDialog(empId, sendingId, template);
            });
            
            console.log('🔗 Event handlers bound to buttons');
        }, 100);
    }


    viewEmailDetails(employeeId, sendingId) {
        const employee = this.dataManager.employees.find(emp => emp.id === employeeId);
        const sending = this.dataManager.getSendingById(sendingId);
        
        if (!employee || !sending) {
            alert('Email detaljer ikke fundet');
            return;
        }

        // Create email preview content
        const emailContent = `
            <div class="card">
                <div class="card-header">
                    <h6><i class="fas fa-envelope"></i> Email Detaljer</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Til:</strong> ${employee.name} (${employee.email})
                        </div>
                        <div class="col-md-6">
                            <strong>Dato:</strong> ${this.formatDate(sending.created_date)}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Skabelon:</strong> ${sending.template_name}
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong> ${this.getStatusBadge(sending.status)}
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Emne:</strong> Login Credentials - ${employee.name}
                    </div>
                    <hr>
                    <div class="mb-3">
                        <strong>Email Indhold:</strong>
                    </div>
                    <div class="border p-3" style="background-color: #f8f9fa; max-height: 400px; overflow-y: auto;">
                        <div style="font-family: Arial, sans-serif; max-width: 600px;">
                            <h3>Velkommen til systemet</h3>
                            <p>Hej ${employee.name},</p>
                            <p>Du har nu fået adgang til vores system. Dine login oplysninger er:</p>
                            <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0;">
                                <strong>Brugernavn:</strong> ${employee.username}<br>
                                <strong>Adgangskode:</strong> ${employee.password}
                            </div>
                            <p>Du kan logge ind på: <a href="#">https://system.gavefabrikken.dk</a></p>
                            <p>Med venlig hilsen,<br>IT-teamet</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-success btn-sm" onclick="mailPortal.sendingManager.showResendDialog(${employeeId}, ${sendingId}, '${sending.template_name}')">
                        <i class="fas fa-redo"></i> Gensend denne Email
                    </button>
                    <button class="btn btn-secondary btn-sm float-end" onclick="mailPortal.sendingManager.showEmployeeSendingsEnhanced(${employeeId})">
                        <i class="fas fa-arrow-left"></i> Tilbage til Oversigt
                    </button>
                </div>
            </div>
        `;

        $('#sendingDetailsContent').html(emailContent);
        $('#sendingDetailsModal .modal-title').text('Email Forhåndsvisning');
        $('#sendingDetailsModal').modal('show');
    }

    showResendDialog(employeeId, sendingId = null, templateName = null) {
        const employee = this.dataManager.employees.find(emp => emp.id === employeeId);
        if (!employee) {
            alert('Medarbejder ikke fundet');
            return;
        }

        // Fill modal with employee information
        $('#resendEmailRecipient').text(`${employee.name} (${employee.email})`);
        
        if (templateName) {
            $('#resendEmailTemplate').text(templateName);
            $('#resendEmailSubject').text('Login Credentials');
        } else {
            // Use last template or default
            const lastSending = this.dataManager.sendings.find(sending => 
                sending.recipients.includes(employeeId)
            );
            const template = lastSending ? lastSending.template_name : 'Login Credentials';
            $('#resendEmailTemplate').text(template);
            $('#resendEmailSubject').text('Login Credentials');
        }

        // Store data for confirmation
        $('#confirmResendBtn').data('employee-id', employeeId);
        $('#confirmResendBtn').data('sending-id', sendingId);
        $('#confirmResendBtn').data('template-name', templateName || 'Login Credentials');

        // Bind confirm button
        $('#confirmResendBtn').off('click').on('click', () => {
            this.confirmResendEmail();
        });

        $('#resendEmailModal').modal('show');
    }

    async confirmResendEmail() {
        const employeeId = parseInt($('#confirmResendBtn').data('employee-id'));
        const templateName = $('#confirmResendBtn').data('template-name');
        
        try {
            // Show loading state
            const originalText = $('#confirmResendBtn').text();
            $('#confirmResendBtn').html('<i class="fas fa-spinner fa-spin"></i> Gensender...').prop('disabled', true);
            
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Update employee's last email date
            const employee = this.dataManager.employees.find(emp => emp.id === employeeId);
            if (employee) {
                employee.sent_date = new Date().toISOString();
                employee.email_status = 'sent';
            }
            
            // Refresh contacts table
            this.renderContactsTable();
            
            // Close modal and show success
            $('#resendEmailModal').modal('hide');
            
            // Show success message
            this.showSuccessMessage(`Email blev sendt til ${employee.name}`);
            
        } catch (error) {
            console.error('Error resending email:', error);
            alert('Fejl ved gensendelse af email. Prøv igen.');
        } finally {
            $('#confirmResendBtn').html('<i class="fas fa-paper-plane"></i> Gensend Email').prop('disabled', false);
        }
    }

    showSuccessMessage(message) {
        const alert = $(`
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-check-circle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        // Auto-dismiss after 4 seconds
        setTimeout(() => {
            alert.alert('close');
        }, 4000);
    }

    showSendingTestEmailModal() {
        if (!this.selectedTemplate) {
            alert('Ingen skabelon er valgt');
            return;
        }

        const template = this.dataManager.findTemplateByName(this.selectedTemplate);
        if (!template) {
            alert('Skabelon ikke fundet');
            return;
        }

        // Get selected language from sending
        const selectedLanguage = $('#sendingLanguageSelect').val();
        if (!selectedLanguage) {
            alert('Vælg venligst et sprog først');
            return;
        }
        
        // Get saved email from session storage
        const savedEmail = sessionStorage.getItem('mailportal_test_email') || '';

        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="sendingTestEmailModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Send Test Email - Forsendelse</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="sendingTestEmailAddress" class="form-label">Test Email Adresse</label>
                                <input type="email" class="form-control" id="sendingTestEmailAddress" 
                                       placeholder="din@email.dk" value="${savedEmail}" required>
                                <div class="form-text">Email adressen gemmes for denne session</div>
                            </div>
                            <div class="mb-3">
                                <label for="sendingTestLanguage" class="form-label">Sprog Version</label>
                                <select class="form-select" id="sendingTestLanguage">
                                    ${template.languages.map(lang => 
                                        `<option value="${lang.language}" ${lang.language === selectedLanguage ? 'selected' : ''}>${this.dataManager.getLanguageName(lang.language)} - ${lang.subject}</option>`
                                    ).join('')}
                                </select>
                                <div class="form-text">Standard valgt sprog: ${this.dataManager.getLanguageName(selectedLanguage)}</div>
                            </div>
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Forsendelse Detaljer</h6>
                                <p class="mb-1"><strong>Skabelon:</strong> ${this.selectedTemplate}</p>
                                <p class="mb-1"><strong>Modtagere:</strong> ${this.selectedRecipients.length} valgt</p>
                                <p class="mb-0"><strong>Test email vil bruge samme indhold som forsendelsen</strong></p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                            <button type="button" class="btn btn-success" id="sendSendingTestEmailBtn">
                                <i class="fas fa-paper-plane"></i> Send Test Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal
        $('#sendingTestEmailModal').remove();
        
        // Add and show modal
        $('body').append(modalHtml);
        $('#sendingTestEmailModal').modal('show');

        // Add event handler for send button
        $('#sendSendingTestEmailBtn').click(() => {
            this.sendSendingTestEmail();
        });
    }

    async sendSendingTestEmail() {
        const testEmail = $('#sendingTestEmailAddress').val().trim();
        const selectedLanguage = $('#sendingTestLanguage').val();
        
        if (!testEmail) {
            alert('Indtast venligst en test email adresse');
            return;
        }

        // Save email to session storage
        sessionStorage.setItem('mailportal_test_email', testEmail);

        try {
            console.log('Sending test email from sending to:', testEmail);
            
            // Show loading state
            const btn = $('#sendSendingTestEmailBtn');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Sender...').prop('disabled', true);
            
            // Get template and language data
            const template = this.dataManager.findTemplateByName(this.selectedTemplate);
            const languageData = template.languages.find(l => l.language === selectedLanguage);
            
            if (!languageData) {
                alert('Sprog version ikke fundet');
                return;
            }

            const apiClient = new APIClient();
            const response = await apiClient.sendTestEmail(
                this.selectedTemplate, 
                selectedLanguage, 
                languageData.subject, 
                languageData.body, 
                testEmail
            );
            
            if (response.status === 1) {
                alert(`Test email sendt successfully til ${testEmail}`);
                $('#sendingTestEmailModal').modal('hide');
            } else {
                alert('Fejl ved afsendelse af test email: ' + (response.message || 'Ukendt fejl'));
            }
            
        } catch (error) {
            console.error('Error sending test email:', error);
            alert('Fejl ved afsendelse af test email');
        } finally {
            $('#sendSendingTestEmailBtn').html('<i class="fas fa-paper-plane"></i> Send Test Email').prop('disabled', false);
        }
    }

    showEditEmployeeModal(employeeId) {
        const employee = this.dataManager.employees.find(emp => emp.id === employeeId);
        if (!employee) {
            alert('Medarbejder ikke fundet');
            return;
        }

        // Populate modal fields - kun navn og email
        $('#editContactId').val(employee.id);
        $('#editContactName').val(employee.name);
        $('#editContactEmail').val(employee.email);

        // Show modal
        $('#editContactModal').modal('show');
    }

    async saveEmployeeChanges() {
        const employeeId = parseInt($('#editContactId').val());
        const name = $('#editContactName').val().trim();
        const email = $('#editContactEmail').val().trim();

        // Validation
        if (!name) {
            alert('Navn er påkrævet');
            return;
        }

        if (!email) {
            alert('Email er påkrævet');
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Indtast en gyldig email adresse');
            return;
        }

        try {
            // Show loading state
            const saveBtn = $('#saveContactBtn');
            const originalText = saveBtn.html();
            saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Gemmer...').prop('disabled', true);

            // Prepare data for API call - kun navn, email og brugernavn
            const updateData = {
                id: employeeId,
                name: name,
                email: email
            };

            // Call API via APIClient
            const apiClient = new APIClient();
            const response = await apiClient.updateEmployee(updateData);

            if (response.status === 1) {
                // Update local data - kun navn og email
                const employee = this.dataManager.employees.find(emp => emp.id === employeeId);
                if (employee) {
                    employee.name = name;
                    employee.email = email;
                }

                // Refresh contacts table
                await this.loadContacts();

                // Close modal
                $('#editContactModal').modal('hide');

                // Show success message
                this.showSuccessMessage(`Kontakt "${name}" blev opdateret`);

            } else {
                alert('Fejl ved opdatering: ' + (response.message || 'Ukendt fejl'));
            }

        } catch (error) {
            console.error('Error updating employee:', error);
            alert('Fejl ved opdatering af kontakt. Prøv igen.');
        } finally {
            // Restore button
            $('#saveContactBtn').html('<i class="fas fa-save"></i> Gem Ændringer').prop('disabled', false);
        }
    }
}