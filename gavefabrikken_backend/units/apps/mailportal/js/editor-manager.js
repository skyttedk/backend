// Editor Management Module
class EditorManager {
    constructor(mailPortal) {
        this.mailPortal = mailPortal;
        this.currentEditor = null;
    }

    initializeEditorForLanguage(language) {
        console.log('Initializing Summernote for language:', language);
        
        const textarea = $(`#pane-${language} .template-body`);
        
        if (textarea.length === 0) {
            console.warn('Textarea not found for language:', language);
            return;
        }
        
        // Destroy existing editor if it exists
        if (textarea.data('summernote')) {
            textarea.summernote('destroy');
        }
        
        // Initialize Summernote
        textarea.summernote({
            height: MAILPORTAL_CONFIG.summernoteHeight,
            toolbar: MAILPORTAL_CONFIG.summernoteToolbar,
            buttons: {
                placeholder: this.createPlaceholderButton()
            },
            dialogsInBody: true,
            dialogsFade: true,
            disableDragAndDrop: true,
            disableResizeEditor: true,
            airMode: false,
            popover: {
                image: [],
                link: [],
                air: []
            },
            callbacks: {
                onChange: (contents, $editable) => {
                    console.log('Manual summernote.change event triggered');
                    this.mailPortal.updateLivePreview(contents);
                },
                onInit: () => {
                    console.log('Summernote onInit triggered');
                    setTimeout(() => {
                        this.mailPortal.updateLivePreview(textarea.summernote('code'));
                    }, 100);
                }
            }
        });
        
        console.log('Summernote initialized for', language);
        this.currentEditor = textarea;
        
        // Fix dropdown click events in modal
        this.setupDropdownFixes(textarea);
        
        // Fix link dialog issues
        this.setupLinkDialogFixes(textarea);
        
        // Bind multiple event listeners for live preview
        textarea.on('summernote.change', (we, contents, $editable) => {
            console.log('Manual summernote.change event triggered');
            this.mailPortal.updateLivePreview(contents);
        });
        
        textarea.on('summernote.keyup summernote.mouseup summernote.input summernote.paste', () => {
            setTimeout(() => {
                const content = textarea.summernote('code');
                console.log('Summernote input event - updating preview');
                this.mailPortal.updateLivePreview(content);
            }, 100);
        });
        
        // Initial preview update - wait longer for modal to be fully visible
        setTimeout(() => {
            try {
                const initialContent = textarea.summernote('code');
                console.log('Initial content for preview:', initialContent ? initialContent.substring(0, 100) + '...' : 'No content');
                console.log('Attempting to update preview...');
                this.mailPortal.updateLivePreview(initialContent || '');
            } catch (error) {
                console.error('Error getting summernote content:', error);
                this.mailPortal.updateLivePreview('<p>Fejl ved indlæsning af indhold</p>');
            }
        }, MAILPORTAL_CONFIG.previewTimeout);
    }

    createPlaceholderButton() {
        return function(context) {
            var ui = $.summernote.ui;
            
            // Create dropdown options
            const dropdownItems = MAILPORTAL_CONFIG.placeholders.map(placeholder => 
                `<a class="dropdown-item" href="#" data-placeholder="${placeholder.value}">
                    <code>${placeholder.value}</code> - ${placeholder.label}
                </a>`
            ).join('');
            
            // Create dropdown button
            var button = ui.buttonGroup([
                ui.button({
                    className: 'dropdown-toggle',
                    contents: '<i class="fas fa-plus"/> Placeholders',
                    tooltip: 'Insert Placeholder',
                    data: {
                        toggle: 'dropdown'
                    },
                    click: function(e) {
                        e.stopPropagation();
                        
                        // Remove existing dropdown
                        $('.placeholder-dropdown').remove();
                        
                        // Create dropdown menu
                        const dropdown = $(`
                            <div class="dropdown-menu placeholder-dropdown show" style="position: absolute; z-index: 1050;">
                                <h6 class="dropdown-header">Vælg Placeholder</h6>
                                ${dropdownItems}
                            </div>
                        `);
                        
                        // Position dropdown
                        const $button = $(this);
                        const buttonOffset = $button.offset();
                        const buttonHeight = $button.outerHeight();
                        
                        dropdown.css({
                            position: 'fixed',
                            top: buttonOffset.top + buttonHeight + 2,
                            left: buttonOffset.left,
                            zIndex: 2050
                        });
                        
                        // Add to body
                        $('body').append(dropdown);
                        
                        // Handle placeholder clicks
                        dropdown.find('.dropdown-item').click(function(e) {
                            e.preventDefault();
                            const placeholderValue = $(this).data('placeholder');
                            context.invoke('editor.insertText', placeholderValue);
                            dropdown.remove();
                        });
                        
                        // Close on outside click
                        $(document).one('click', function() {
                            dropdown.remove();
                        });
                    }
                })
            ]);
            return button.render();
        };
    }

    getCurrentEditorContent() {
        if (this.currentEditor && this.currentEditor.length) {
            try {
                return this.currentEditor.summernote('code');
            } catch (error) {
                console.error('Error getting content from current editor:', error);
                return '';
            }
        }
        return '';
    }

    setupDropdownFixes(textarea) {
        // Wait for editor to be fully initialized
        setTimeout(() => {
            const noteEditor = textarea.next('.note-editor');
            console.log('Setting up dropdown fixes for editor:', noteEditor.length);

            // Handle dropdown button clicks
            noteEditor.find('.note-btn[data-toggle="dropdown"]').each(function() {
                const $btn = $(this);
                const $parent = $btn.closest('.note-btn-group');
                
                $btn.off('click.custom').on('click.custom', function(e) {
                    e.stopPropagation();
                    console.log('Custom dropdown click:', $parent.attr('class'));
                    
                    // Close other dropdowns
                    noteEditor.find('.note-btn-group').removeClass('show open');
                    
                    // Toggle this dropdown
                    if ($parent.hasClass('show')) {
                        $parent.removeClass('show open');
                    } else {
                        $parent.addClass('show open');
                    }
                });
            });

            // Close dropdowns when clicking outside
            $(document).off('click.dropdown-fix').on('click.dropdown-fix', function(e) {
                if (!$(e.target).closest('.note-editor').length) {
                    noteEditor.find('.note-btn-group').removeClass('show open');
                }
            });

            // Close dropdowns when pressing ESC
            $(document).off('keyup.dropdown-fix').on('keyup.dropdown-fix', function(e) {
                if (e.keyCode === 27) { // ESC key
                    console.log('ESC pressed - closing dropdowns');
                    noteEditor.find('.note-btn-group').removeClass('show open');
                }
            });
            
            console.log('Dropdown fixes applied');
        }, 500);
    }

    setupLinkDialogFixes(textarea) {
        // Fix link dialog z-index and prevent auto-close
        setTimeout(() => {
            const editor = textarea.next('.note-editor');
            
            // Override link button click behavior
            editor.find('.note-btn[data-original-title*="Link"], .note-btn[title*="Link"], .note-link .note-btn').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Create custom link dialog
                const linkDialog = $(`
                    <div class="modal fade note-modal" id="linkDialog" tabindex="-1" style="z-index: 2200;">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Indsæt Link</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="linkUrl" class="form-label">URL</label>
                                        <input type="url" class="form-control" id="linkUrl" placeholder="http://example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="linkText" class="form-label">Link tekst</label>
                                        <input type="text" class="form-control" id="linkText" placeholder="Link tekst">
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="linkNewWindow" checked>
                                        <label class="form-check-label" for="linkNewWindow">
                                            Åbn i nyt vindue
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                                    <button type="button" class="btn btn-primary" id="insertLinkBtn">Indsæt Link</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                // Remove existing dialogs
                $('.note-modal, #linkDialog').remove();
                
                // Add to body
                $('body').append(linkDialog);
                
                // Handle insert link
                linkDialog.find('#insertLinkBtn').on('click', function() {
                    const url = $('#linkUrl').val().trim();
                    const text = $('#linkText').val().trim() || url;
                    const newWindow = $('#linkNewWindow').is(':checked');
                    
                    if (url) {
                        let linkHTML = `<a href="${url}"`;
                        
                        if (newWindow) {
                            linkHTML += ' target="_blank" rel="noopener noreferrer"';
                        }
                        
                        linkHTML += `>${text}</a>`;
                        
                        // Insert the link HTML directly
                        textarea.summernote('pasteHTML', linkHTML);
                    }
                    
                    linkDialog.modal('hide');
                });
                
                // Show dialog
                linkDialog.modal('show');
                
                // Focus URL input after modal is shown
                linkDialog.on('shown.bs.modal', function() {
                    $('#linkUrl').focus();
                });
                
                // Clean up after modal is hidden
                linkDialog.on('hidden.bs.modal', function() {
                    linkDialog.remove();
                });
            });
            
        }, 500);
    }
}