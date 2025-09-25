// units/valgshop/main/js/documentmanager.js
var DOC_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/main/";

export default class DocumentManager {
    constructor() {
        this.shopId = null;
        this.documentType = null;
        this.containerId = null;
        this.uploadCallback = null;
        this.deleteCallback = null;
    }

    /**
     * Initialize document manager
     * @param {number} shopId - Shop ID
     * @param {string} documentType - Type of document (e.g. 'payment_special', 'contract', etc.)
     * @param {string} containerId - ID of the container element
     * @param {object} callbacks - Callback functions for events
     */
    init(shopId, documentType, containerId, callbacks = {}) {
        this.shopId = shopId;
        this.documentType = documentType;
        this.containerId = containerId;
        this.uploadCallback = callbacks.onUpload || null;
        this.deleteCallback = callbacks.onDelete || null;

        this.render();
        this.loadDocuments();
        this.bindEvents();
    }

    render() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="document-manager">
                <div class="upload-section mb-3">
                    <div class="input-group">
                        <input type="file" class="form-control doc-file-input" id="file_${this.documentType}" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                        <button class="btn btn-primary doc-upload-btn" type="button">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>
                    <small class="text-muted">Tilladt: PDF, Word, Excel, Billeder (maks 10MB pr. fil)</small>
                </div>
                
                <div class="documents-list" id="docs_list_${this.documentType}">
                    <div class="text-center">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Indlæser...</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    bindEvents() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        // Upload button click
        container.querySelector('.doc-upload-btn').addEventListener('click', () => {
            this.uploadFiles();
        });

        // File input change (auto-upload)
        container.querySelector('.doc-file-input').addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.uploadFiles();
            }
        });
    }

    async uploadFiles() {
        const fileInput = document.querySelector(`#file_${this.documentType}`);
        const files = fileInput.files;

        if (files.length === 0) {
            alert('Vælg venligst mindst én fil');
            return;
        }

        const uploadBtn = document.querySelector(`#${this.containerId} .doc-upload-btn`);
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploader...';

        let uploadPromises = [];

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                alert(`Filen "${file.name}" er for stor. Maksimum størrelse er 10MB.`);
                continue;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('shop_id', this.shopId);
            formData.append('document_type', this.documentType);
            formData.append('document_category', this.documentCategory);

            // Opret promise for hver upload
            const uploadPromise = new Promise((resolve, reject) => {
                $.ajax({
                    url: DOC_AJAX_URL + 'uploadDocument/' + this.shopId,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: (result) => {
                        if (result.status === 1) {
                            if (this.uploadCallback) {
                                this.uploadCallback(result.data);
                            }
                            resolve(result);
                        } else {
                            alert(`Fejl ved upload af "${file.name}": ${result.message || 'Ukendt fejl'}`);
                            reject(result);
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('Upload error:', error);
                        alert(`Fejl ved upload af "${file.name}"`);
                        reject(error);
                    }
                });
            });

            uploadPromises.push(uploadPromise);
        }

        // Vent på at alle uploads er færdige
        try {
            await Promise.all(uploadPromises);
        } catch (error) {
            console.error('En eller flere uploads fejlede:', error);
        }

        // Reset
        fileInput.value = '';
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = 'Upload';

        // Reload documents list EFTER alle uploads er færdige
        setTimeout(() => {
            this.loadDocuments();
        }, 500); // Lille delay for at sikre database er opdateret
    }

    async loadDocuments() {
        const listContainer = document.getElementById(`docs_list_${this.documentType}`);
        if (!listContainer) return;

        $.ajax({
            url: DOC_AJAX_URL + 'getDocuments',
            type: 'GET',
            data: {
                shop_id: this.shopId,
                document_type: this.documentType
            },
            dataType: 'json',
            success: (result) => {
                if (result.status === 1 && result.data.length > 0) {
                    this.renderDocumentsList(result.data);
                } else {
                    listContainer.innerHTML = '<p class="text-muted mb-0">Ingen dokumenter uploadet endnu</p>';
                }
            },
            error: (xhr, status, error) => {
                console.error('Load documents error:', error);
                listContainer.innerHTML = '<p class="text-danger">Fejl ved indlæsning af dokumenter</p>';
            }
        });
    }

    renderDocumentsList(documents) {
        const listContainer = document.getElementById(`docs_list_${this.documentType}`);
        if (!listContainer) return;

        let html = '<div class="list-group">';

        documents.forEach(doc => {
            const fileIcon = this.getFileIcon(doc.file_type);
            const fileSize = this.formatFileSize(doc.file_size);
            const uploadDate = new Date(doc.upload_date).toLocaleDateString('da-DK');

            html += `
            <div class="list-group-item d-flex justify-content-between align-items-center" data-doc-id="${doc.id}">
                <div class="d-flex align-items-center">
                    <span class="${fileIcon} me-2"></span>
                    <div>
                        <div class="fw-bold">${doc.original_filename}</div>
                        <small class="text-muted">${fileSize} - Uploadet ${uploadDate}</small>
                    </div>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-sm btn-primary doc-download-btn" data-id="${doc.id}" title="Download">
                        Download
                    </button>
                    <button class="btn btn-sm btn-danger doc-delete-btn ms-1" data-id="${doc.id}" title="Slet">
                        Slet
                    </button>
                </div>
            </div>
        `;
        });

        html += '</div>';
        listContainer.innerHTML = html;

        // Bind events to new buttons
        this.bindDocumentEvents();
    }

    bindDocumentEvents() {
        // Download buttons
        document.querySelectorAll(`#docs_list_${this.documentType} .doc-download-btn`).forEach(btn => {
            btn.addEventListener('click', (e) => {
                const docId = e.currentTarget.dataset.id;
                this.downloadDocument(docId);
            });
        });

        // Delete buttons
        document.querySelectorAll(`#docs_list_${this.documentType} .doc-delete-btn`).forEach(btn => {
            btn.addEventListener('click', (e) => {
                const docId = e.currentTarget.dataset.id;
                this.deleteDocument(docId);
            });
        });
    }

    downloadDocument(docId) {
        window.open(DOC_AJAX_URL + 'downloadDocument/' + docId, '_blank');
    }

    async deleteDocument(docId) {
        if (!confirm('Er du sikker på at du vil slette dette dokument?')) {
            return;
        }

        $.ajax({
            url: DOC_AJAX_URL + 'deleteDocument/' + docId,
            type: 'POST',
            data: {
                id: docId,  // Tilføj id her
                shop_id: this.shopId
            },
            dataType: 'json',
            success: (result) => {
                if (result.status === 1) {
                    if (this.deleteCallback) {
                        this.deleteCallback(docId);
                    }
                    // Remove from DOM
                    const docElement = document.querySelector(`[data-doc-id="${docId}"]`);
                    if (docElement) {
                        docElement.remove();
                    }

                    // Check if list is empty
                    const listContainer = document.getElementById(`docs_list_${this.documentType}`);
                    if (listContainer && listContainer.querySelectorAll('.list-group-item').length === 0) {
                        listContainer.innerHTML = '<p class="text-muted mb-0">Ingen dokumenter uploadet endnu</p>';
                    }
                } else {
                    alert('Fejl ved sletning af dokument: ' + (result.message || 'Ukendt fejl'));
                }
            },
            error: (xhr, status, error) => {
                console.error('Delete error:', error);
                alert('Fejl ved sletning af dokument');
            }
        });
    }

    getFileIcon(fileType) {
        if (!fileType) return 'fas fa-file';

        if (fileType.includes('pdf')) return 'fas fa-file-pdf text-danger';
        if (fileType.includes('word') || fileType.includes('document')) return 'fas fa-file-word text-primary';
        if (fileType.includes('sheet') || fileType.includes('excel')) return 'fas fa-file-excel text-success';
        if (fileType.includes('image') || fileType.includes('png') || fileType.includes('jpg') || fileType.includes('jpeg')) return 'fas fa-file-image text-info';

        return 'fas fa-file';
    }

    formatFileSize(bytes) {
        if (!bytes) return 'Ukendt størrelse';

        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        if (bytes === 0) return '0 Bytes';

        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    }
}