// Main MailPortal Application Class - Modular Version
class MailPortal extends SimpleBase {
    constructor() {
        super();
        
        // Initialize managers
        this.dataManager = new DataManager();
        this.editorManager = new EditorManager(this);
        this.templateManager = new TemplateManager(this);
        this.employeeManager = new EmployeeManager(this);
        this.previewManager = new PreviewManager(this);
        this.sendingManager = new SendingManager(new APIClient(), this.dataManager);
        
        // Application state
        this.currentTab = MAILPORTAL_CONFIG.defaultTab;
        
        this.init();
    }

    async init() {
        console.log('MailPortal init() started');
        
        // Initialize shop context from URL parameters with token validation
        if (!initializeShopContext()) {
            // Access denied - initialization stopped
            return;
        }
        
        // Test connection
        await this.testService();
        
        console.log('Loading shop info...');
        await this.dataManager.loadShopInfo();
        this.updateShopTypeIndicators();
        
        console.log('Loading languages...');
        await this.dataManager.loadLanguages();
        console.log('Languages loaded:', this.dataManager.languages.length);
        
        console.log('Loading employees...');
        await this.dataManager.loadEmployees();
        console.log('Employees loaded:', this.dataManager.employees.length);
        
        console.log('Loading templates...');
        await this.dataManager.loadTemplates();
        console.log('Templates loaded:', this.dataManager.templates.length);
        
        console.log('Loading sendings...');
        await this.dataManager.loadSendings();
        console.log('Sendings loaded:', this.dataManager.sendings.length);
        
        console.log('Setting up event listeners...');
        this.setupEventListeners();
        
        // Initialize template manager
        console.log('Initializing template manager...');
        this.templateManager.init();
        
        console.log('Setting up shop-specific UI...');
        this.setupShopSpecificUI();
        
        console.log('Showing default tab...');
        this.showTab('sendinges'); // Start with sendings tab
        
        console.log('MailPortal init() completed');
    }

    setupEventListeners() {
        // Navigation tabs - templates tab handled here, others by SendingManager
        $(document).on('click', '#templatesTab', (e) => {
            e.preventDefault();
            console.log('Templates tab clicked!');
            this.showTab('templates');
        });
        
        // Alternative direct binding for templates tab
        $('#templatesTab').click((e) => {
            e.preventDefault();
            console.log('Direct templates tab clicked!');
            this.showTab('templates');
        });
        
        // Employee operations
        $('#selectAll').change((e) => {
            const checked = e.target.checked;
            $('.employee-select').prop('checked', checked);
            this.employeeManager.updateSelectedEmployees();
        });
        
        $(document).on('change', '.employee-select', () => {
            this.employeeManager.updateSelectedEmployees();
        });
        
        $('#bulkEmailBtn').click(() => this.employeeManager.showBulkEmailModal());
        $('#refreshDataBtn').click(() => this.refreshData());
        
        // Template operations
        $('#addTemplateBtn').click(() => this.showAddTemplateModal());
        $(document).on('click', '#saveTemplateBtn', () => this.templateManager.saveTemplate());
        
        // Test email functionality
        $('#testEmailBtn').click(() => this.showTemplateTestEmailModal());
        $('#sendTemplateTestEmailBtn').click(() => this.sendTemplateTestEmail());
        
        // Preview update button
        $(document).on('click', '#updatePreviewBtn', () => this.previewManager.testPreview());
        
        // Preview toggle button
        $(document).on('click', '#togglePreviewBtn', () => this.togglePreview());
        
        
        // Search and filtering
        $('#templateSearch').on('input', () => this.templateManager.filterTemplates());
        $('#templateLanguageFilter').change(() => this.templateManager.filterTemplates());
    }

    showTab(tabName) {
        console.log('showTab called with:', tabName);
        console.log('Available tabs:', $('.nav-link').length);
        console.log('Available content areas:', $('.tab-content').length);
        
        // Update navigation
        $('.nav-link').removeClass('active');
        $(`#${tabName}Tab`).addClass('active');
        console.log('Navigation updated for:', `#${tabName}Tab`, $(`#${tabName}Tab`).length);
        
        // Show content - only hide main tab containers
        $('#sendingesContent, #contactsContent, #templatesContent').removeClass('active').hide();
        $(`#${tabName}Content`).addClass('active').show();
        console.log('Content shown for:', `#${tabName}Content`, $(`#${tabName}Content`).length);
        
        this.currentTab = tabName;
        
        // Load content for templates tab
        if (tabName === 'templates') {
            console.log('Loading templates tab content');
            console.log('Templates available:', this.dataManager.templates?.length || 0);
            
            // Just render with existing data (loaded at startup)
            this.templateManager.renderTemplatesList();
            console.log('Templates rendered!');
        }
        
        // Load content for sendings tab
        if (tabName === 'sendinges') {
            console.log('Loading sendings tab content');
            this.sendingManager.showSendingsTab();
        }
        
        // Load content for contacts tab
        if (tabName === 'contacts') {
            console.log('Loading contacts tab content');
            this.sendingManager.showContactsTab();
        }
    }

    refreshData() {
        console.log('Refreshing data...');
        this.dataManager.loadEmployees();
        this.dataManager.loadTemplates();
        this.employeeManager.renderEmployeesList();
        this.templateManager.renderTemplatesList();
    }

    showAddTemplateModal() {
        console.log('Show add template modal');
        
        // Create new template modal HTML
        const modalHtml = `
        <div class="modal fade" id="createTemplateModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus"></i> Opret Ny Skabelon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createTemplateForm">
                            <div class="mb-3">
                                <label for="newTemplateName" class="form-label">Skabelon Navn</label>
                                <input type="text" class="form-control" id="newTemplateName" required placeholder="F.eks. Nyt Login, Juleinformation...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sprog Versioner</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="lang_da" checked disabled>
                                            <label class="form-check-label" for="lang_da">Dansk (påkrævet)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input new-template-lang" type="checkbox" id="lang_en" value="en">
                                            <label class="form-check-label" for="lang_en">English</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input new-template-lang" type="checkbox" id="lang_sv" value="sv">
                                            <label class="form-check-label" for="lang_sv">Svenska</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input new-template-lang" type="checkbox" id="lang_no" value="no">
                                            <label class="form-check-label" for="lang_no">Norsk</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input new-template-lang" type="checkbox" id="lang_de" value="de">
                                            <label class="form-check-label" for="lang_de">Deutsch</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                        <button type="button" class="btn btn-success" id="createTemplateBtn">
                            <i class="fas fa-plus"></i> Opret Skabelon
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
        
        // Remove existing modal if present
        $('#createTemplateModal').remove();
        
        // Add modal to body
        $('body').append(modalHtml);
        
        // Show modal
        $('#createTemplateModal').modal('show');
        
        // Handle create button
        $('#createTemplateBtn').click(() => this.createNewTemplate());
    }

    async createNewTemplate() {
        const name = $('#newTemplateName').val().trim();
        
        if (!name) {
            alert('Indtast venligst et skabelon navn');
            return;
        }
        
        // Check if template name already exists
        const exists = this.dataManager.templates.some(t => t.group_name === name);
        if (exists) {
            alert('En skabelon med dette navn findes allerede');
            return;
        }
        
        // Get selected languages
        const languages = ['da']; // Always include Danish
        $('.new-template-lang:checked').each(function() {
            languages.push($(this).val());
        });
        
        try {
            console.log('Creating new template:', name, 'with languages:', languages);
            
            // Call API to create template
            const apiClient = new APIClient();
            const response = await apiClient.createTemplate(name, 'custom', languages);
            
            if (response.status === 1) {
                // Add to local data manager
                this.dataManager.templates.push(response.data);
                
                // Refresh templates list
                this.templateManager.renderTemplatesList();
                
                // Close modal
                $('#createTemplateModal').modal('hide');
                
                // Open editor for new template
                setTimeout(() => {
                    this.templateManager.showTemplateGroupEditor(name);
                }, 500);
                
                console.log('Template created successfully:', response.data);
            } else {
                alert('Fejl ved oprettelse af skabelon');
            }
        } catch (error) {
            console.error('Error creating template:', error);
            alert('Fejl ved oprettelse af skabelon');
        }
    }

    showTestEmailModal() {
        const templateName = $('#templateName').val();
        const activeLanguageTab = $('.nav-tabs .nav-link.active');
        const language = activeLanguageTab.length > 0 ? activeLanguageTab.attr('id').replace('tab-', '') : 'da';
        const subject = $(`.tab-pane.active .template-subject`).val() || 'Test emne';
        
        // Get saved email from session storage
        const savedEmail = sessionStorage.getItem('mailportal_test_email') || '';
        
        // Update modal info
        $('#testTemplateName').text(templateName || 'Ingen skabelon valgt');
        $('#testTemplateLanguage').text(this.dataManager.getLanguageName(language));
        $('#testTemplateSubject').text(subject);
        $('#testEmailAddress').val(savedEmail);
        
        $('#testEmailModal').modal('show');
    }

    async sendTestEmail() {
        const testEmail = $('#testEmailAddress').val().trim();
        const templateName = $('#templateName').val();
        const activeLanguageTab = $('.nav-tabs .nav-link.active');
        const language = activeLanguageTab.length > 0 ? activeLanguageTab.attr('id').replace('tab-', '') : 'da';
        const subject = $(`.tab-pane.active .template-subject`).val() || 'Test emne';
        const body = this.editorManager.getCurrentEditorContent() || '<p>Intet indhold</p>';
        
        if (!testEmail) {
            alert('Indtast venligst en test email adresse');
            return;
        }
        
        if (!templateName) {
            alert('Ingen skabelon er valgt');
            return;
        }
        
        // Save email to session storage
        sessionStorage.setItem('mailportal_test_email', testEmail);
        
        try {
            console.log('Sending test email to:', testEmail);
            
            // Show loading state
            const btn = $('#sendTestEmailBtn');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Sender...').prop('disabled', true);
            
            const apiClient = new APIClient();
            const response = await apiClient.sendTestEmail(templateName, language, subject, body, testEmail);
            
            if (response.status === 1) {
                alert('Test email sendt successfully til ' + testEmail);
                $('#testEmailModal').modal('hide');
            } else {
                alert('Fejl ved afsendelse: ' + (response.message || 'Ukendt fejl'));
            }
            
            // Restore button
            btn.html(originalText).prop('disabled', false);
            
        } catch (error) {
            console.error('Error sending test email:', error);
            alert('Fejl ved afsendelse af test email');
            
            // Restore button
            $('#sendTestEmailBtn').html('<i class="fas fa-paper-plane"></i> Send Test Email').prop('disabled', false);
        }
    }

    togglePreview() {
        const previewSection = $('#previewSection');
        const languageCol = $('#languageContentCol');
        const toggleBtn = $('#togglePreviewBtn');
        
        if (previewSection.is(':visible')) {
            // Hide preview
            previewSection.hide();
            languageCol.removeClass('col-md-6').addClass('col-md-12');
            toggleBtn.html('<i class="fas fa-eye"></i> Vis Live Forhåndsvisning');
        } else {
            // Show preview
            previewSection.show();
            languageCol.removeClass('col-md-12').addClass('col-md-6');
            toggleBtn.html('<i class="fas fa-eye-slash"></i> Skjul Live Forhåndsvisning');
            
            // Update preview content when shown
            setTimeout(() => {
                const content = this.editorManager.getCurrentEditorContent();
                if (content) {
                    this.previewManager.updateLivePreview(content);
                }
            }, 100);
        }
    }

    // Delegate methods to appropriate managers
    showTemplateGroupEditor(groupName) {
        this.templateManager.showTemplateGroupEditor(groupName);
    }

    createLanguageTabs(group) {
        this.templateManager.createLanguageTabs(group);
    }

    activateLanguageTab(language) {
        this.templateManager.activateLanguageTab(language);
    }

    updateLivePreview(htmlContent) {
        this.previewManager.updateLivePreview(htmlContent);
    }

    previewTemplate(groupName) {
        this.templateManager.previewTemplate(groupName);
    }

    deleteTemplate(groupName) {
        this.templateManager.deleteTemplate(groupName);
    }

    // Getter methods for backward compatibility
    get employees() {
        return this.dataManager.employees;
    }

    get templates() {
        return this.dataManager.templates;
    }

    get languages() {
        return this.dataManager.languages;
    }

    get selectedEmployees() {
        return this.employeeManager.selectedEmployees;
    }

    get currentEditor() {
        return this.editorManager.currentEditor;
    }

    showTemplateTestEmailModal() {
        const templateName = $('#templateName').val();
        if (!templateName) {
            alert('Ingen skabelon valgt til test');
            return;
        }

        const template = this.dataManager.findTemplateByName(templateName);
        if (!template) {
            alert('Skabelon ikke fundet');
            return;
        }

        // Get saved email from session storage
        const savedEmail = sessionStorage.getItem('mailportal_test_email') || '';
        $('#templateTestEmailAddress').val(savedEmail);

        // Populate language dropdown
        const languageSelect = $('#templateTestLanguage');
        languageSelect.empty();
        template.languages.forEach(lang => {
            const languageName = this.dataManager.getLanguageName(lang.language);
            languageSelect.append(`<option value="${lang.language}">${languageName} - ${lang.subject}</option>`);
        });

        // Get first employee data for placeholders
        const firstEmployee = this.dataManager.employees[0];
        if (firstEmployee) {
            $('#testDataName').text(firstEmployee.name || 'N/A');
            $('#testDataEmail').text(firstEmployee.email || 'N/A');
            $('#testDataUsername').text(firstEmployee.username || 'N/A');
            $('#testDataPassword').text('****'); // Don't show actual password
        }

        // Show modal
        $('#templateTestEmailModal').modal('show');
    }

    renderPlaceholderButtons() {
        const container = $('#templateTestPlaceholders');
        container.empty();

        MAILPORTAL_CONFIG.placeholders.forEach(placeholder => {
            const button = $(`
                <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2 placeholder-btn" 
                        data-placeholder="${placeholder.value}">
                    ${placeholder.value} <small>(${placeholder.label})</small>
                </button>
            `);
            
            button.click(() => {
                // Insert placeholder at cursor position in active editor if possible
                // Placeholder insertion will be handled by template-manager.js
                console.log('Placeholder selected:', placeholder.value);
                
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(placeholder.value).then(() => {
                    // Show temporary feedback
                    button.text('Kopieret!').removeClass('btn-outline-secondary').addClass('btn-success');
                    setTimeout(() => {
                        button.html(`${placeholder.value} <small>(${placeholder.label})</small>`)
                              .removeClass('btn-success').addClass('btn-outline-secondary');
                    }, 1000);
                });
            });
            
            container.append(button);
        });
    }

    async sendTemplateTestEmail() {
        const testEmail = $('#templateTestEmailAddress').val().trim();
        const selectedLanguage = $('#templateTestLanguage').val();
        const templateName = $('#templateName').val();

        if (!testEmail) {
            alert('Indtast venligst en test email adresse');
            return;
        }

        if (!selectedLanguage) {
            alert('Vælg venligst et sprog');
            return;
        }

        // Save email to session storage
        sessionStorage.setItem('mailportal_test_email', testEmail);

        const template = this.dataManager.findTemplateByName(templateName);
        const languageData = template.languages.find(l => l.language === selectedLanguage);
        
        if (!languageData) {
            alert('Sprog version ikke fundet');
            return;
        }

        // Get first employee for placeholder replacement
        const firstEmployee = this.dataManager.employees[0];
        if (!firstEmployee) {
            alert('Ingen medarbejder data fundet til test');
            return;
        }

        try {
            // Show loading state
            const btn = $('#sendTemplateTestEmailBtn');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Sender...').prop('disabled', true);

            // Replace placeholders with actual data
            let subject = languageData.subject;
            let body = this.editorManager.getCurrentEditorContent() || languageData.body;

            // Replace placeholders
            const replacements = {
                '{{name}}': firstEmployee.name,
                '{{username}}': firstEmployee.username,
                '{{password}}': firstEmployee.password,
                '{{link}}': 'https://shop.gavefabrikken.dk/demo/',
                '{{start_date}}': '01/12/2024',
                '{{end_date}}': '31/12/2024'
            };

            Object.keys(replacements).forEach(placeholder => {
                const regex = new RegExp(placeholder.replace(/[{}]/g, '\\$&'), 'g');
                subject = subject.replace(regex, replacements[placeholder]);
                body = body.replace(regex, replacements[placeholder]);
            });

            const apiClient = new APIClient();
            const response = await apiClient.sendTestEmail(
                templateName,
                selectedLanguage,
                subject,
                body,
                testEmail
            );

            if (response.status === 1) {
                alert(`Test email sendt successfully til ${testEmail} med data fra ${firstEmployee.name}`);
                $('#templateTestEmailModal').modal('hide');
            } else {
                alert('Fejl ved afsendelse: ' + (response.message || 'Ukendt fejl'));
            }

        } catch (error) {
            console.error('Error sending template test email:', error);
            alert('Fejl ved afsendelse af test email');
        } finally {
            $('#sendTemplateTestEmailBtn').html('<i class="fas fa-paper-plane"></i> Send Test Email').prop('disabled', false);
        }
    }

    updateShopTypeIndicators() {
        const shopInfo = this.dataManager.shopInfo;
        if (!shopInfo) return;
        
        const shopTypeIcon = shopInfo.type === 'valgshop' ? 'fas fa-gifts' : 'fas fa-credit-card';
        const shopTypeName = shopInfo.name || (shopInfo.type === 'valgshop' ? 'Valgshop' : 'Kortshop');
        const customerName = shopInfo.customer_short || shopInfo.customer_name || 'Kunde';
        
        // Combine shop type and customer name
        const displayText = `${shopTypeName} - ${customerName}`;
        
        // Update only navbar indicator
        $('#shopTypeName').text(displayText);
        $('#shopTypeIndicator i').attr('class', shopTypeIcon);
    }
    
    setupShopSpecificUI() {
        console.log('🏪 Setting up UI for shop type:', SHOP_TYPE);
        
        // Update contacts table headers based on shop type
        const contactsTableHeader = $('#contactsTable thead tr');
        
        if (SHOP_TYPE === 'cardshop') {
            // Cardshop: Username, Name, Email, Last Email, Actions
            contactsTableHeader.html(`
                <th class="sortable-header" data-sort="username" title="Klik for at sortere efter brugernavn">
                    Brugernavn <i class="fas fa-sort sort-icon"></i>
                </th>
                <th class="sortable-header" data-sort="name" title="Klik for at sortere efter navn">
                    Navn <i class="fas fa-sort sort-icon"></i>
                </th>
                <th class="sortable-header" data-sort="email" title="Klik for at sortere efter email">
                    Email <i class="fas fa-sort sort-icon"></i>
                </th>
                <th class="sortable-header" data-sort="date" title="Klik for at sortere efter dato">
                    Sidste Email <i class="fas fa-sort sort-icon"></i>
                </th>
                <th>Handlinger</th>
            `);
        } else {
            // Valgshop: Name, Email, Last Email, Actions (default from HTML)
            contactsTableHeader.html(`
                <th class="sortable-header" data-sort="name" title="Klik for at sortere efter navn">
                    Navn <i class="fas fa-sort sort-icon"></i>
                </th>
                <th class="sortable-header" data-sort="email" title="Klik for at sortere efter email">
                    Email <i class="fas fa-sort sort-icon"></i>
                </th>
                <th class="sortable-header" data-sort="date" title="Klik for at sortere efter dato">
                    Sidste Email <i class="fas fa-sort sort-icon"></i>
                </th>
                <th>Handlinger</th>
            `);
        }
        
        console.log('✅ Shop-specific UI setup completed for', SHOP_TYPE);
    }
}

// Initialize the application
let mailPortal;
$(document).ready(function() {
    // Initialize app directly - no need to wait for Summernote
    function initializeApp() {
        console.log('Creating MailPortal instance...');
        mailPortal = new MailPortal();
        window.mailPortal = mailPortal; // Make it globally accessible for onclick handlers
        console.log('window.mailPortal assigned:', typeof window.mailPortal);
    }
    
    initializeApp();
});