// Configuration and Constants
var MAILPORTAL_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/mailportal/";
var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";

// Global shop context variables
var SHOP_TYPE = null; // 'valgshop' or 'cardshop'
var SHOP_ID = null;   // For valgshop
var COMPANY_ID = null; // For cardshop
var ACCESS_TOKEN = null; // Required access token

// Initialize shop context from URL parameters
function initializeShopContext() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Check for required token first
    ACCESS_TOKEN = urlParams.get('token');
    if (!ACCESS_TOKEN || ACCESS_TOKEN !== 'dslfkj32498yr') {
        console.error('❌ Access denied: Invalid or missing token');
        document.body.innerHTML = `
            <div class="container mt-5">
                <div class="alert alert-danger text-center">
                    <i class="fas fa-lock fa-3x mb-3"></i>
                    <h3>Adgang Nægtet</h3>
                    <p>Du har ikke tilladelse til at tilgå denne applikation.</p>
                    <p>Kontakt administratoren for at få adgang.</p>
                </div>
            </div>
        `;
        return false;
    }
    
    // Detect shop type and extract relevant ID
    if (urlParams.has('shopID')) {
        SHOP_TYPE = 'valgshop';
        SHOP_ID = urlParams.get('shopID');
        console.log('🏪 Initialized as VALGSHOP with shopID:', SHOP_ID);
    } else if (urlParams.has('companyID')) {
        SHOP_TYPE = 'cardshop';
        COMPANY_ID = urlParams.get('companyID');
        console.log('🏢 Initialized as CARDSHOP with companyID:', COMPANY_ID);
    } else {
        console.warn('⚠️ No shopID or companyID found in URL parameters');
        // Default fallback - you may want to handle this differently
        SHOP_TYPE = 'valgshop';
    }
    
    console.log('✅ Access granted with valid token');
    return true;
}

// Application Configuration
const MAILPORTAL_CONFIG = {
    defaultTab: 'sendinges',
    summernoteHeight: 300,
    previewTimeout: 1000,
    tabSwitchDelay: 500,
    
    // Supported languages
    supportedLanguages: [
        { code: 'da', name: 'Dansk' },
        { code: 'en', name: 'English' },
        { code: 'sv', name: 'Svenska' },
        { code: 'no', name: 'Norsk' },
        { code: 'de', name: 'Deutsch' }
    ],

    // Summernote toolbar configuration
    summernoteToolbar: [
        ['style', ['style']],
        ['font', ['bold', 'italic', 'underline']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link']],
        ['view', ['codeview']],
        ['misc', ['placeholder']]
    ],

    // Placeholder definitions
    placeholders: [
        { value: '{{name}}', label: 'Navn' },
        { value: '{{username}}', label: 'Brugernavn' },
        { value: '{{password}}', label: 'Adgangskode' },
        { value: '{{link}}', label: 'Link' },
        { value: '{{start_date}}', label: 'Start Dato' },
        { value: '{{end_date}}', label: 'Slut Dato' }
    ]
};