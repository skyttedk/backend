// Template Management Module - Redesigned for proper functionality
class TemplateManager {
    constructor(mailPortal) {
        this.mailPortal = mailPortal;
        this.currentEditingTemplate = null;
        this.editorInstances = new Map();
        this.isInitialized = false;
    }

    init() {
        if (this.isInitialized) return;
        
        // Bind global event handlers
        this.bindEventHandlers();
        this.isInitialized = true;
        
        console.log('TemplateManager initialized');
    }

    bindEventHandlers() {
        // Modal cleanup when closed
        $('#templateEditorModal').on('hidden.bs.modal', () => {
            this.cleanupEditors();
            this.currentEditingTemplate = null;
        });

        // Tab switching handler
        $(document).on('click', '[data-bs-toggle="tab"]', (e) => {
            const target = $(e.target).attr('data-bs-target');
            if (target && target.startsWith('#pane-')) {
                const language = target.replace('#pane-', '');
                setTimeout(() => this.initializeEditorForLanguage(language), 100);
            }
        });
    }

    showTemplateGroupEditor(groupName) {
        console.log('Opening template editor for:', groupName);
        
        const template = this.mailPortal.dataManager.findTemplateByName(groupName);
        if (!template) {
            this.showErrorMessage(`Skabelon "${groupName}" ikke fundet`);
            return;
        }

        this.currentEditingTemplate = JSON.parse(JSON.stringify(template)); // Deep copy
        
        // Set modal title and template name
        $('#templateEditorTitle').text(`Rediger: ${groupName}`);
        $('#templateName').val(groupName);
        
        // Create language tabs
        this.createLanguageTabs(this.currentEditingTemplate);
        
        // Show modal
        $('#templateEditorModal').modal('show');
        
        // Initialize first language tab when modal is fully shown
        $('#templateEditorModal').one('shown.bs.modal', () => {
            if (this.currentEditingTemplate.languages.length > 0) {
                const firstLang = this.currentEditingTemplate.languages[0].language;
                this.activateLanguageTab(firstLang);
            }
        });
    }

    createLanguageTabs(template) {
        const tabsContainer = $('#languageTabs');
        const contentContainer = $('#languageTabContent');
        
        // Clear existing content
        tabsContainer.empty();
        contentContainer.empty();
        this.cleanupEditors();

        template.languages.forEach((langData, index) => {
            const isActive = index === 0;
            const languageName = this.mailPortal.dataManager.getLanguageName(langData.language);
            const langCode = langData.language;
            
            // Create tab button
            const tabButton = $(`
                <li class="nav-item" role="presentation">
                    <button class="nav-link ${isActive ? 'active' : ''}" 
                            id="tab-${langCode}" 
                            data-bs-toggle="tab" 
                            data-bs-target="#pane-${langCode}" 
                            type="button" 
                            role="tab" 
                            aria-controls="pane-${langCode}"
                            aria-selected="${isActive}">
                        ${languageName}
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                onclick="event.stopPropagation(); window.mailPortal.templateManager.removeLanguage('${langCode}')"
                                title="Fjern sprog" style="padding: 1px 4px; font-size: 10px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </button>
                </li>
            `);
            
            // Create tab pane
            const tabPane = $(`
                <div class="tab-pane fade ${isActive ? 'show active' : ''}" 
                     id="pane-${langCode}" 
                     role="tabpanel" 
                     aria-labelledby="tab-${langCode}">
                    
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label for="subject-${langCode}" class="form-label">
                                <i class="fas fa-envelope"></i> Email Emne (${languageName})
                            </label>
                            <input type="text" 
                                   id="subject-${langCode}"
                                   class="form-control template-subject" 
                                   value="${this.escapeHtml(langData.subject)}" 
                                   placeholder="Indtast email emne...">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label">
                                <i class="fas fa-code"></i> HTML Indhold
                            </label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-info" 
                                        onclick="window.mailPortal.templateManager.insertPlaceholder('${langCode}', '{{name}}')"
                                        title="Indsæt navn placeholder">
                                    {{name}}
                                </button>
                                <button type="button" class="btn btn-outline-info" 
                                        onclick="window.mailPortal.templateManager.insertPlaceholder('${langCode}', '{{username}}')"
                                        title="Indsæt brugernavn placeholder">
                                    {{username}}
                                </button>
                                <button type="button" class="btn btn-outline-info" 
                                        onclick="window.mailPortal.templateManager.insertPlaceholder('${langCode}', '{{password}}')"
                                        title="Indsæt kodeord placeholder">
                                    {{password}}
                                </button>
                            </div>
                        </div>
                        <div id="editor-container-${langCode}">
                            <textarea id="editor-${langCode}" 
                                     class="form-control template-editor"
                                     rows="15">${this.escapeHtml(langData.body)}</textarea>
                        </div>
                    </div>
                </div>
            `);
            
            tabsContainer.append(tabButton);
            contentContainer.append(tabPane);
        });
    }

    activateLanguageTab(language) {
        console.log('Activating language tab:', language);
        
        // Activate the tab
        $(`#tab-${language}`).tab('show');
        
        // Initialize editor for this language
        setTimeout(() => {
            this.initializeEditorForLanguage(language);
        }, 150);
    }

    initializeEditorForLanguage(language) {
        const editorId = `editor-${language}`;
        const $editor = $(`#${editorId}`);
        
        if (!$editor.length) {
            console.error('Editor element not found:', editorId);
            return;
        }

        // Check if this editor is already initialized
        if (this.editorInstances.has(language)) {
            console.log(`Quill editor already exists for ${language}, skipping initialization`);
            return;
        }

        // Hide the textarea and create Quill editor container
        $editor.hide();
        
        // Create Quill container after the textarea
        const quillContainerId = `quill-${language}`;
        if ($(`#${quillContainerId}`).length === 0) {
            $editor.after(`<div id="${quillContainerId}" style="min-height: 300px;"></div>`);
        } else {
            // Container exists, check if it already has a Quill editor
            console.log(`Quill container already exists for ${language}`);
            return;
        }
        
        // Initialize Quill editor
        const quill = new Quill(`#${quillContainerId}`, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }]
                ]
            },
            placeholder: `Skriv email indhold for ${language}...`,
        });
        
        // Set initial content if any
        const initialContent = $editor.val();
        if (initialContent) {
            quill.root.innerHTML = initialContent;
        }
        
        // Sync Quill content back to textarea on change
        quill.on('text-change', () => {
            $editor.val(quill.root.innerHTML);
        });
        
        // Store Quill instance for later access
        this.editorInstances.set(language, quill);
        console.log(`Quill editor ready for ${language}`);
    }

    cleanupEditors() {
        // Remove all Quill editor containers
        this.editorInstances.forEach((quillInstance, language) => {
            const quillContainerId = `quill-${language}`;
            $(`#${quillContainerId}`).remove();
            
            // Show original textarea
            const $editor = $(`#editor-${language}`);
            $editor.show();
        });
        
        // Clear the tracking
        this.editorInstances.clear();
        console.log('Cleaned up all Quill editors');
    }

    insertPlaceholder(language, placeholder) {
        if (this.editorInstances.has(language)) {
            const quill = this.editorInstances.get(language);
            const currentLength = quill.getLength();
            quill.insertText(currentLength - 1, placeholder);
        }
    }

    removeLanguage(language) {
        if (!this.currentEditingTemplate) return;
        
        if (this.currentEditingTemplate.languages.length <= 1) {
            this.showErrorMessage('Du kan ikke fjerne det sidste sprog');
            return;
        }
        
        if (confirm(`Er du sikker på at du vil fjerne ${this.mailPortal.dataManager.getLanguageName(language)}?`)) {
            // Remove from current template
            this.currentEditingTemplate.languages = this.currentEditingTemplate.languages.filter(
                lang => lang.language !== language
            );
            
            // Recreate tabs
            this.createLanguageTabs(this.currentEditingTemplate);
            
            // Activate first remaining language
            if (this.currentEditingTemplate.languages.length > 0) {
                setTimeout(() => {
                    this.activateLanguageTab(this.currentEditingTemplate.languages[0].language);
                }, 100);
            }
        }
    }

    async saveTemplate() {
        if (!this.currentEditingTemplate) {
            this.showErrorMessage('Ingen skabelon at gemme');
            return;
        }

        try {
            console.log('Saving template:', this.currentEditingTemplate.group_name);
            
            // Get current template name from input field
            const currentTemplateName = $('#templateName').val();
            
            // Save each language separately via API (without direct DB calls)
            const apiClient = new APIClient();
            let allSaved = true;
            let saveErrors = [];
            
            for (const langData of this.currentEditingTemplate.languages) {
                const language = langData.language;
                
                // Get CURRENT values from form inputs (not cached data)
                const subjectInput = $(`#subject-${language}`);
                const editorElement = $(`#editor-${language}`);
                
                let currentSubject = subjectInput.val() || '';
                let currentBody = '';
                
                // Get content from editor or textarea
                if (this.editorInstances.has(language)) {
                    try {
                        currentBody = editorElement.val() || '';
                    } catch (e) {
                        currentBody = editorElement.val() || '';
                    }
                } else {
                    currentBody = editorElement.val() || '';
                }
                
                try {
                    const saveData = {
                        id: this.currentEditingTemplate.id || null,
                        name: currentTemplateName,
                        language: language,
                        subject: currentSubject,
                        body: currentBody,
                        is_default: 0
                    };
                    
                    console.log('Calling saveTemplate API with data:', saveData);
                    
                    const response = await apiClient.saveTemplate(saveData);
                    
                    console.log('saveTemplate API response:', response);
                    
                    if (response.status !== 1) {
                        console.error('saveTemplate failed for language:', langData.language, 'Response:', response);
                        allSaved = false;
                        saveErrors.push(`${langData.language}: ${response.message || 'Ukendt fejl'}`);
                    } else {
                        console.log('saveTemplate successful for language:', langData.language);
                    }
                } catch (error) {
                    allSaved = false;
                    saveErrors.push(`${language}: ${error.message}`);
                    console.error(`Error saving language ${language}:`, error);
                }
            }
            
            if (allSaved) {
                // Update local data manager with new values
                const originalTemplate = this.mailPortal.dataManager.findTemplateByName(this.currentEditingTemplate.group_name);
                if (originalTemplate) {
                    // Update template name if changed
                    originalTemplate.group_name = currentTemplateName;
                    
                    // Update all language data with current form values
                    originalTemplate.languages = this.currentEditingTemplate.languages.map(langData => {
                        const language = langData.language;
                        const subjectInput = $(`#subject-${language}`);
                        const editorElement = $(`#editor-${language}`);
                        
                        let subject = subjectInput.val() || '';
                        let body = '';
                        
                        if (this.editorInstances.has(language)) {
                            try {
                                body = editorElement.val() || '';
                            } catch (e) {
                                body = editorElement.val() || '';
                            }
                        } else {
                            body = editorElement.val() || '';
                        }
                        
                        return {
                            language: language,
                            subject: subject,
                            body: body,
                            shop_type: langData.shop_type || 'valgshop'
                        };
                    });
                }
                
                this.showSuccessMessage(`Skabelon "${this.currentEditingTemplate.group_name}" blev gemt`);
                
                // Refresh templates list
                this.renderTemplatesList();
                
                console.log('Template saved successfully');
                
            } else {
                this.showErrorMessage('Fejl ved gemning: ' + saveErrors.join(', '));
            }
            
        } catch (error) {
            console.error('Save template error:', error);
            this.showErrorMessage('Fejl ved gemning af skabelon');
        }
    }

    updateLivePreview() {
        // Update live preview if it's visible
        // This would integrate with the preview system
        console.log('Updating live preview...');
    }

    renderTemplatesList() {
        const tbody = $('#templatesTableBody');
        tbody.empty();
        
        this.mailPortal.dataManager.templates.forEach(template => {
            const languageNames = template.languages.map(l => 
                this.mailPortal.dataManager.getLanguageName(l.language)
            ).join(', ');
            
            const row = $(`
                <tr class="template-row">
                    <td>
                        <strong>${this.escapeHtml(template.group_name)}</strong>
                        <br>
                        <small class="text-muted">${languageNames}</small>
                    </td>
                    <td>
                        ${template.languages.map(l => 
                            `<div><span class="badge badge-sm bg-secondary">${this.mailPortal.dataManager.getLanguageName(l.language)}</span> ${this.escapeHtml(l.subject || 'Ingen emne')}</div>`
                        ).join('')}
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" 
                                    onclick="window.mailPortal.templateManager.showTemplateGroupEditor('${this.escapeHtml(template.group_name)}')" 
                                    title="Rediger">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-success" 
                                    onclick="window.mailPortal.templateManager.previewTemplate('${this.escapeHtml(template.group_name)}')" 
                                    title="Forhåndsvis">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-danger" 
                                    onclick="window.mailPortal.templateManager.deleteTemplate('${this.escapeHtml(template.group_name)}')" 
                                    title="Slet">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
            tbody.append(row);
        });
    }

    async previewTemplate(groupName) {
        const template = this.mailPortal.dataManager.findTemplateByName(groupName);
        if (!template) {
            this.showErrorMessage('Skabelon ikke fundet');
            return;
        }

        // Create preview modal content
        const previewContent = template.languages.map(lang => {
            const languageName = this.mailPortal.dataManager.getLanguageName(lang.language);
            let processedBody = lang.body
                .replace(/\{\{name\}\}/g, 'John Doe')
                .replace(/\{\{username\}\}/g, 'john.doe')
                .replace(/\{\{password\}\}/g, 'TempPass123!')
                .replace(/\{\{link\}\}/g, '<a href="https://example.com">https://example.com</a>')
                .replace(/\{\{date\}\}/g, new Date().toLocaleDateString('da-DK'));

            return `
                <div class="mb-4">
                    <h6><span class="badge bg-primary">${languageName}</span></h6>
                    <div class="border p-3 rounded" style="background: #f8f9fa;">
                        <div class="mb-2">
                            <strong>Emne:</strong> ${this.escapeHtml(lang.subject)}
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;">
                            ${processedBody}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Show preview in modal
        const modalHtml = `
            <div class="modal fade" id="templatePreviewModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-eye"></i> Forhåndsvisning: ${this.escapeHtml(groupName)}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${previewContent}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Luk
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing and show new
        $('#templatePreviewModal').remove();
        $('body').append(modalHtml);
        $('#templatePreviewModal').modal('show');
    }

    async deleteTemplate(groupName) {
        if (!confirm(`Er du sikker på at du vil slette skabelonen "${groupName}"?\n\nDenne handling kan ikke fortrydes.`)) {
            return;
        }

        try {
            const apiClient = new APIClient();
            const response = await apiClient.deleteTemplate(groupName);
            
            if (response.status === 1) {
                // Remove from local data
                this.mailPortal.dataManager.templates = this.mailPortal.dataManager.templates.filter(
                    template => template.group_name !== groupName
                );
                
                this.renderTemplatesList();
                this.showSuccessMessage(`Skabelonen "${groupName}" blev slettet`);
                
            } else {
                this.showErrorMessage('Fejl ved sletning: ' + (response.message || 'Ukendt fejl'));
            }
        } catch (error) {
            console.error('Delete template error:', error);
            this.showErrorMessage('Fejl ved sletning af skabelon');
        }
    }

    // Helper methods
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showSuccessMessage(message) {
        const alert = $(`
            <div class="alert alert-success alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 350px;">
                <i class="fas fa-check-circle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        setTimeout(() => alert.alert('close'), 4000);
    }

    showErrorMessage(message) {
        const alert = $(`
            <div class="alert alert-danger alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 350px;">
                <i class="fas fa-exclamation-triangle me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        setTimeout(() => alert.alert('close'), 5000);
    }
}