// Preview Management Module
class PreviewManager {
    constructor(mailPortal) {
        this.mailPortal = mailPortal;
    }

    updateLivePreview(htmlContent) {
        console.log('updateLivePreview called with content length:', htmlContent ? htmlContent.length : 0);
        
        // Get preview frame
        const previewFrame = $('#emailPreviewFrame');
        console.log('Preview frame found:', previewFrame.length);
        if (!previewFrame.length) {
            console.warn('Preview frame #emailPreviewFrame not found');
            return;
        }

        // Replace placeholders with sample data
        let previewHtml = htmlContent
            .replace(/\{\{name\}\}/g, 'John Doe')
            .replace(/\{\{username\}\}/g, 'john.doe')
            .replace(/\{\{password\}\}/g, 'TempPass123!')
            .replace(/\{\{link\}\}/g, '<a href="https://example.com">https://example.com</a>')
            .replace(/\{\{date\}\}/g, new Date().toLocaleDateString('da-DK'))
            .replace(/\{\{header_image\}\}/g, '<div style="background:#007bff;color:white;padding:20px;text-align:center;margin-bottom:20px;"><strong>📧 Mail Portal</strong></div>')
            .replace(/\{\{footer_image\}\}/g, '<div style="background:#6c757d;color:white;padding:15px;text-align:center;margin-top:20px;font-size:12px;">Med venlig hilsen<br>Mail Portal Team</div>');

        // Add basic styling and insert content directly into div
        const styledHtml = `
            <style>
                #emailPreviewFrame {
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333;
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    word-break: break-word;
                }
                #emailPreviewFrame h1, #emailPreviewFrame h2, #emailPreviewFrame h3 { 
                    color: #333; 
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                }
                #emailPreviewFrame p, #emailPreviewFrame div, #emailPreviewFrame span {
                    word-wrap: break-word;
                    overflow-wrap: break-word;
                    word-break: break-word;
                    max-width: 100%;
                }
                #emailPreviewFrame a { color: #007bff; text-decoration: none; }
                #emailPreviewFrame a:hover { text-decoration: underline; }
            </style>
            ${previewHtml}
        `;

        // Update div content directly
        previewFrame.html(styledHtml);
        console.log('Preview updated successfully');

        // Update subject preview
        const activeTab = $('.nav-tabs .nav-link.active');
        const activeLanguagePane = $('.tab-pane.active');
        const subjectInput = activeLanguagePane.find('.template-subject');
        if (subjectInput.length) {
            $('#previewSubject').text(subjectInput.val() || 'Ingen emne');
        }
    }

    testPreview() {
        console.log('Manual preview test triggered');
        const content = this.mailPortal.editorManager.getCurrentEditorContent();
        if (content) {
            console.log('Current editor content:', content);
            this.updateLivePreview(content);
        } else {
            console.log('No current editor found');
            // Test with sample content
            this.updateLivePreview('<h2>Test Preview</h2><p>Hej {{name}}, dette er en test.</p>');
        }
    }
}