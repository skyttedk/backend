<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Portal - Password Distribution</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Quill CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="units/apps/mailportal/css/main.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-envelope"></i> Mail Portal</a>
            <div class="navbar-text d-none d-md-block me-3">
                <span class="badge bg-light text-dark" id="shopTypeIndicator">
                    <i class="fas fa-store"></i> <span id="shopTypeName">Indlæser...</span>
                </span>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="sendingesTab">Forsendelser</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="templatesTab">Skabeloner</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="contactsTab">Kontakter</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-3 mt-4">

        <!-- Main Content Area -->
        <div id="mainContent">
            <!-- MailPortal main container -->
            <div id="mailportal_main"></div>
            
            <!-- Sendinges Tab -->
            <div id="sendingesContent" class="tab-content active">
                <!-- Create New Sending Header -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-paper-plane"></i> Mail Forsendelser</h5>
                        <button class="btn btn-success" id="createSendingBtn">
                            <i class="fas fa-plus"></i> Opret Mail-forsendelse
                        </button>
                    </div>
                </div>

                <!-- Sending Progress Section (hidden by default) -->
                <div class="card mb-4" id="sendingProgressCard" style="display: none;">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-hourglass-half"></i> Sender Emails...</h6>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="sendingProgressBar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="sendingStatus">Forbereder forsendelse...</div>
                    </div>
                </div>

                <!-- Sendings List -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Alle Forsendelser</h6>
                        <button class="btn btn-info btn-sm" id="refreshSendingsBtn">
                            <i class="fas fa-sync-alt"></i> Opdater
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-striped table-hover" id="sendingsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable-header" data-sort="id" title="Klik for at sortere efter ID">
                                            ID <i class="fas fa-sort sort-icon"></i>
                                        </th>
                                        <th class="sortable-header" data-sort="template" title="Klik for at sortere efter skabelon">
                                            Skabelon <i class="fas fa-sort sort-icon"></i>
                                        </th>
                                        <th class="sortable-header" data-sort="date" title="Klik for at sortere efter dato">
                                            Oprettet <i class="fas fa-sort sort-icon"></i>
                                        </th>
                                        <th>Modtagere</th>
                                        <th>Sendt</th>
                                        <th>Fejl</th>
                                        <th class="sortable-header" data-sort="status" title="Klik for at sortere efter status">
                                            Status <i class="fas fa-sort sort-icon"></i>
                                        </th>
                                        <th>Handlinger</th>
                                    </tr>
                                </thead>
                                <tbody id="sendingsTableBody">
                                    <!-- Sending rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contacts Tab (renamed from Employees) -->
            <div id="contactsContent" class="tab-content">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Kontakter</h5>
                        <button class="btn btn-info btn-sm" id="refreshContactsBtn">
                            <i class="fas fa-sync-alt"></i> Opdater
                        </button>
                    </div>
                    <div class="card-body">
                        
                        <!-- Search and Filter Section -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="contactSearchInput" placeholder="Søg på navn, email, eller sidste email...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">Klik på kolonne-headere for at sortere</small>
                            </div>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-striped table-hover" id="contactsTable">
                                <thead class="table-light">
                                    <tr>
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
                                    </tr>
                                </thead>
                                <tbody id="contactsTableBody">
                                    <!-- Contact rows will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resend Email Modal -->
            <div class="modal fade" id="resendEmailModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-redo"></i> Gensend Email
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Er du sikker på, at du vil gensende denne email?
                            </div>
                            <div class="mb-3">
                                <strong>Til:</strong> <span id="resendEmailRecipient"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Skabelon:</strong> <span id="resendEmailTemplate"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Emne:</strong> <span id="resendEmailSubject"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                            <button type="button" class="btn btn-primary" id="confirmResendBtn">
                                <i class="fas fa-paper-plane"></i> Gensend Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates Tab -->
            <div id="templatesContent" class="tab-content">
                <!-- Template Management Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4><i class="fas fa-file-alt"></i> Email Skabeloner</h4>
                        <p class="text-muted mb-0">Administrer email templates med HTML editor og live preview</p>
                    </div>
                    <button class="btn btn-success" id="addTemplateBtn">
                        <i class="fas fa-plus"></i> Opret Ny Skabelon
                    </button>
                </div>

                <!-- Search and Filter Bar -->
                <div class="card mb-4">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="templateSearch" placeholder="Søg i skabeloner...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="templateLanguageFilter">
                                    <option value="">Alle sprog</option>
                                    <option value="da">Dansk</option>
                                    <option value="en">English</option>
                                    <option value="sv">Svenska</option>
                                    <option value="no">Norsk</option>
                                    <option value="de">Deutsch</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Templates Table View -->
                <div class="row" id="templatesContainer">
                    <div class="col-12">
                        <!-- Templates Table -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Skabeloner</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-striped table-hover mb-0" id="templatesTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="sortable-header" data-sort="name" title="Klik for at sortere efter navn">
                                                    Navn <i class="fas fa-sort sort-icon"></i>
                                                </th>
                                                <th class="sortable-header" data-sort="subject" title="Klik for at sortere efter emne">
                                                    Emne <i class="fas fa-sort sort-icon"></i>
                                                </th>
                                                <th>Handlinger</th>
                                            </tr>
                                        </thead>
                                        <tbody id="templatesTableBody">
                                            <!-- Template rows will be inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Template Editor Modal -->
                <div class="modal fade" id="templateEditorModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-fullscreen">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="templateEditorTitle">Rediger Skabelon</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeTemplateEditorBtn"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Template Info Section -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="templateName" class="form-label">Skabelon Navn</label>
                                        <input type="text" class="form-control" id="templateName" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sprog Versioner</label>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="addLanguageBtn" onclick="addLanguageHandler()">
                                                <i class="fas fa-plus"></i> Tilføj Sprog
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Global Template Settings -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-cog"></i> Global Indstillinger (gælder alle sprog)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="headerImage" class="form-label">Header Billede</label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control" id="headerImage" accept="image/*">
                                                    <button class="btn btn-outline-secondary" type="button" id="removeHeaderBtn">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div id="headerImagePreview" class="mt-2"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="footerImage" class="form-label">Footer Billede</label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control" id="footerImage" accept="image/*">
                                                    <button class="btn btn-outline-secondary" type="button" id="removeFooterBtn">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                <div id="footerImagePreview" class="mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Language Tabs -->
                                <ul class="nav nav-tabs mb-3" id="languageTabs" role="tablist">
                                    <!-- Language tabs will be inserted here -->
                                </ul>

                                <!-- Preview Toggle Button -->
                                <div class="mb-3">
                                    <button class="btn btn-outline-info btn-sm" id="togglePreviewBtn">
                                        <i class="fas fa-eye"></i> Vis Live Forhåndsvisning
                                    </button>
                                </div>

                                <!-- Content Row: Editor + Preview -->
                                <div class="row">
                                    <!-- Language Content -->
                                    <div class="col-md-12" id="languageContentCol">
                                        <div class="tab-content" id="languageTabContent">
                                            <!-- Language tab panes will be inserted here -->
                                        </div>
                                    </div>
                                    
                                    <!-- Live Preview Section -->
                                    <div class="col-md-6" id="previewSection" style="display: none;">
                                        <div class="card">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Live Forhåndsvisning</h6>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" id="updatePreviewBtn">
                                                        <i class="fas fa-sync"></i> Opdater
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="p-2 bg-light border-bottom">
                                                    <small><strong>Til:</strong> test@example.com</small><br>
                                                    <small><strong>Emne:</strong> <span id="previewSubject">-</span></small>
                                                </div>
                                                <div class="preview-container" style="padding: 20px; background: #f8f9fa;">
                                                    <div id="emailPreviewFrame" style="width: 100%; max-width: 500px; background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                        <!-- Email content will be inserted here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelTemplateBtn">
                                    <i class="fas fa-times"></i> Annuller
                                </button>
                                <button type="button" class="btn btn-success" id="testEmailBtn">
                                    <i class="fas fa-paper-plane"></i> Send Test Email
                                </button>
                                <button type="button" class="btn btn-primary" id="saveTemplateBtn">
                                    <i class="fas fa-save"></i> Gem Skabelon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Test Email Modal -->
                <div class="modal fade" id="templateTestEmailModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-paper-plane"></i> Send Test Email - Skabelon
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="templateTestEmailAddress" class="form-label">Test Email Adresse</label>
                                            <input type="email" class="form-control" id="templateTestEmailAddress" 
                                                   placeholder="din@email.dk" required>
                                            <div class="form-text">Email adressen hvor testen sendes til</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="templateTestLanguage" class="form-label">Sprog Version</label>
                                            <select class="form-select" id="templateTestLanguage">
                                                <!-- Populated by JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-user"></i> Test Data - Første Modtager</h6>
                                            <div id="templateTestUserData">
                                                <p><strong>Navn:</strong> <span id="testDataName">Indlæser...</span></p>
                                                <p><strong>Email:</strong> <span id="testDataEmail">Indlæser...</span></p>
                                                <p><strong>Brugernavn:</strong> <span id="testDataUsername">Indlæser...</span></p>
                                                <p><strong>Password:</strong> <span id="testDataPassword">****</span></p>
                                            </div>
                                            <div class="form-text">Disse data indsættes i placeholders</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                                <button type="button" class="btn btn-success" id="sendTemplateTestEmailBtn">
                                    <i class="fas fa-paper-plane"></i> Send Test Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Language Modal -->
                <div class="modal fade" id="addLanguageModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Tilføj Sprog Version</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="newLanguageSelect" class="form-label">Vælg Sprog</label>
                                    <select class="form-select" id="newLanguageSelect" required>
                                        <option value="">Vælg sprog...</option>
                                        <option value="da">Dansk</option>
                                        <option value="en">English</option>
                                        <option value="sv">Svenska</option>
                                        <option value="no">Norsk</option>
                                        <option value="de">Deutsch</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="copyFromLanguage" class="form-label">Kopier Indhold Fra (valgfrit)</label>
                                    <select class="form-select" id="copyFromLanguage">
                                        <option value="">Start med tomt indhold</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                                <button type="button" class="btn btn-primary" id="confirmAddLanguageBtn" onclick="confirmAddLanguageHandler()">Tilføj Sprog</button>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
    </div>

    <!-- Email History Modal -->
    <div class="modal fade" id="emailHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailHistoryModalTitle">Email Historie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Dato</th>
                                    <th>Emne</th>
                                    <th>Forsøg #</th>
                                    <th>Fejl</th>
                                </tr>
                            </thead>
                            <tbody id="emailHistoryTableBody">
                                <!-- Email history will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Preview Modal -->
    <div class="modal fade" id="emailPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Email Forhåndsvisning</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Til:</label>
                        <div id="previewTo" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Emne:</label>
                        <div id="previewSubject" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Besked:</label>
                        <div id="previewBody" class="form-control-plaintext" style="white-space: pre-wrap; background-color: #f8f9fa; padding: 1rem; border-radius: 0.375rem;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Sending Modal -->
    <div class="modal fade" id="createSendingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Opret Mail-forsendelse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Select Template -->
                    <div id="step1" class="sending-step">
                        <h6 class="mb-3"><i class="fas fa-file-alt"></i> Trin 1: Vælg Skabelon</h6>
                        <div class="row" id="templateSelector">
                            <!-- Template cards will be inserted here -->
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-primary" id="nextToStep2Btn" disabled>
                                Næste <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Edit Template -->
                    <div id="step2" class="sending-step" style="display: none;">
                        <h6 class="mb-3"><i class="fas fa-edit"></i> Trin 2: Rediger Skabelon</h6>
                        
                        <!-- Template editing interface -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-file-alt"></i> <span id="editingTemplateName">Skabelon</span></h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Bemærk:</strong> Ændringer her gælder kun for denne forsendelse og påvirker ikke den originale skabelon.
                                </div>
                                
                                <!-- Language tabs for editing -->
                                <ul class="nav nav-tabs" id="editingLanguageTabs" role="tablist">
                                    <!-- Language tabs will be inserted here -->
                                </ul>
                                
                                <div class="tab-content mt-3" id="editingLanguageTabContent">
                                    <!-- Language tab content will be inserted here -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary" id="backToStep1Btn">
                                <i class="fas fa-arrow-left"></i> Tilbage
                            </button>
                            <button type="button" class="btn btn-primary" id="nextToStep3Btn">
                                Næste <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Select Recipients -->
                    <div id="step3" class="sending-step" style="display: none;">
                        <h6 class="mb-3"><i class="fas fa-users"></i> Trin 3: Vælg Modtagere og Sprog</h6>
                        
                        <!-- Language Selection -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-language"></i> Vælg Sprog for Forsendelse</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-select" id="sendingLanguageSelect" required>
                                            <option value="">Vælg sprog...</option>
                                            <!-- Languages will be populated based on selected template -->
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Kun sprog som er tilgængelige i den valgte skabelon kan vælges. Alle emails sendes på det valgte sprog.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mail Server Selection -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-server"></i> Vælg Mail Server</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <select class="form-select" id="sendingMailServerSelect" required>
                                            <option value="">Vælg mail server...</option>
                                            <!-- Mail servers will be populated here -->
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center h-100">
                                            <span id="mailServerStatus" class="badge bg-secondary">
                                                <i class="fas fa-circle"></i> Vælg server
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <small class="text-muted">Alle emails i denne forsendelse sendes via den valgte mail server.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllContacts">
                                <label class="form-check-label" for="selectAllContacts">
                                    <strong>Vælg alle kontakter</strong>
                                </label>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 50px;">Vælg</th>
                                        <th>Navn</th>
                                        <th>Email</th>
                                        <th>Password/Kortnr</th>
                                    </tr>
                                </thead>
                                <tbody id="recipientSelector">
                                    <!-- Recipient checkboxes will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary" id="backToStep2Btn">
                                <i class="fas fa-arrow-left"></i> Tilbage
                            </button>
                            <button type="button" class="btn btn-primary" id="nextToStep4Btn" disabled>
                                Næste <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 4: Confirm and Send -->
                    <div id="step4" class="sending-step" style="display: none;">
                        <h6 class="mb-3"><i class="fas fa-check"></i> Trin 4: Bekræft Forsendelse</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Valgt Skabelon</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="selectedTemplateInfo">
                                            <!-- Selected template info -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Modtagere</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="selectedRecipientsInfo">
                                            <!-- Selected recipients info -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary" id="backToStep3Btn">
                                <i class="fas fa-arrow-left"></i> Tilbage
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-info" id="sendingTestEmailBtn">
                                    <i class="fas fa-paper-plane"></i> Send Test Email
                                </button>
                                <button type="button" class="btn btn-outline-warning" id="saveDraftBtn">
                                    <i class="fas fa-save"></i> Gem som Klade
                                </button>
                                <button type="button" class="btn btn-success" id="createSendingConfirmBtn">
                                    <i class="fas fa-paper-plane"></i> Opret Forsendelse
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sending Details Modal -->
    <div class="modal fade" id="sendingDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Forsendelse Detaljer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="sendingDetailsContent">
                        <!-- Sending details will be inserted here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Contact Modal -->
    <div class="modal fade" id="editContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit"></i> Rediger Kontakt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editContactForm">
                        <input type="hidden" id="editContactId">
                        <div class="mb-3">
                            <label for="editContactName" class="form-label">Navn</label>
                            <input type="text" class="form-control" id="editContactName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editContactEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editContactEmail" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Bemærk:</strong> Kun navn og email kan redigeres. Brugernavn og password administreres separat.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                    <button type="button" class="btn btn-primary" id="saveContactBtn">
                        <i class="fas fa-save"></i> Gem Ændringer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="position-fixed top-50 start-50 translate-middle" style="display: none; z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Indlæser...</span>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <!-- MailPortal JS Modules -->
    <script src="units/apps/mailportal/js/config.js"></script>
    <script src="units/apps/mailportal/js/api.js"></script>
    <script src="units/apps/mailportal/js/data-manager.js"></script>
    <script src="units/apps/mailportal/js/editor-manager.js"></script>
    <script src="units/apps/mailportal/js/template-manager.js"></script>
    <script src="units/apps/mailportal/js/employee-manager.js"></script>
    <script src="units/apps/mailportal/js/preview-manager.js"></script>
    <script src="units/apps/mailportal/js/sending-manager.js"></script>
    <script src="units/apps/mailportal/js/main.js"></script>
    
    <!-- Add Language Helper Functions -->
    <script>
    function addLanguageHandler() {
        if (!window.mailPortal || !window.mailPortal.templateManager) {
            alert('System ikke klar. Prøv igen om lidt.');
            return;
        }
        
        const currentTemplate = window.mailPortal.templateManager.currentEditingTemplate;
        if (!currentTemplate) {
            alert('Ingen skabelon valgt');
            return;
        }
        
        // Find template by ID instead of name (in case name was changed)
        const template = window.mailPortal.dataManager.findTemplateById(currentTemplate.id);
        if (!template) {
            alert('Skabelon ikke fundet: ' + currentTemplate.id);
            return;
        }
        
        // Get available languages that aren't already used
        const existingLanguages = template.languages.map(l => l.language);
        const availableLanguages = window.mailPortal.dataManager.languages.filter(
            lang => !existingLanguages.includes(lang.code)
        );
        
        if (availableLanguages.length === 0) {
            alert('Alle sprog er allerede tilføjet til denne skabelon');
            return;
        }
        
        // Populate language selector
        const languageSelect = $('#newLanguageSelect');
        const copyFromSelect = $('#copyFromLanguage');
        
        languageSelect.empty();
        copyFromSelect.empty();
        
        languageSelect.append('<option value="">Vælg sprog...</option>');
        copyFromSelect.append('<option value="">Start med tomt indhold</option>');
        
        // Add available languages
        availableLanguages.forEach(lang => {
            languageSelect.append(`<option value="${lang.code}">${lang.name}</option>`);
        });
        
        // Add existing languages as copy sources
        template.languages.forEach(lang => {
            const langName = window.mailPortal.dataManager.getLanguageName(lang.language);
            copyFromSelect.append(`<option value="${lang.language}">Kopier fra ${langName}</option>`);
        });
        
        $('#addLanguageModal').modal('show');
    }
    
    function confirmAddLanguageHandler() {
        const newLanguageCode = $('#newLanguageSelect').val();
        const copyFromLanguage = $('#copyFromLanguage').val();
        const templateName = $('#templateName').val();
        
        if (!newLanguageCode) {
            alert('Vælg venligst et sprog');
            return;
        }
        
        if (!window.mailPortal?.templateManager?.currentEditingTemplate) {
            alert('Ingen skabelon redigeres i øjeblikket');
            return;
        }
        
        const template = window.mailPortal.templateManager.currentEditingTemplate;
        
        // Check if language already exists
        if (template.languages.some(l => l.language === newLanguageCode)) {
            alert('Dette sprog eksisterer allerede');
            return;
        }
        
        let newLanguageData = {
            language: newLanguageCode,
            subject: '',
            body: '<p>Skriv dit indhold her...</p>',
            shop_type: 'valgshop'
        };
        
        // Copy from existing language if selected
        if (copyFromLanguage) {
            const sourceLanguage = template.languages.find(l => l.language === copyFromLanguage);
            if (sourceLanguage) {
                newLanguageData.subject = sourceLanguage.subject;
                newLanguageData.body = sourceLanguage.body;
            }
        }
        
        // Add to template
        template.languages.push(newLanguageData);
        
        // Recreate tabs and activate new language
        window.mailPortal.templateManager.createLanguageTabs(template);
        $('#addLanguageModal').modal('hide');
        
        // Activate the new language tab
        setTimeout(() => {
            window.mailPortal.templateManager.activateLanguageTab(newLanguageCode);
        }, 200);
        
        console.log('Language added successfully:', newLanguageCode);
    }
    </script>
    
    <!-- Test Email Modal -->
    <div class="modal fade" id="testEmailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Send Test Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="testEmailAddress" class="form-label">Test Email Adresse</label>
                        <input type="email" class="form-control" id="testEmailAddress" 
                               placeholder="din@email.dk" required>
                        <div class="form-text">Email adressen gemmes for denne session</div>
                    </div>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Test Email Detaljer</h6>
                        <p class="mb-1"><strong>Skabelon:</strong> <span id="testTemplateName">-</span></p>
                        <p class="mb-1"><strong>Sprog:</strong> <span id="testTemplateLanguage">-</span></p>
                        <p class="mb-0"><strong>Emne:</strong> <span id="testTemplateSubject">-</span></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                    <button type="button" class="btn btn-success" id="sendTestEmailBtn">
                        <i class="fas fa-paper-plane"></i> Send Test Email
                    </button>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>