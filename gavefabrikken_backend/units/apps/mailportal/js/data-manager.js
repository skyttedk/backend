// Data Management Module
class DataManager {
    constructor() {
        this.employees = [];
        this.templates = [];
        this.languages = [];
        this.sendings = [];
        this.shopInfo = null;
    }

    async loadEmployees() {
        try {
            const apiClient = new APIClient();
            const response = await apiClient.call('getEmployees');
            
            if (response.status === 1 && response.data) {
                this.employees = response.data;
                console.log('Employees loaded from API:', this.employees.length);
            } else {
                // Fallback data
                this.loadEmployeesFallback();
            }
        } catch (error) {
            console.error('Error loading employees from API:', error);
            // Fallback data
            this.loadEmployeesFallback();
        }
    }

    loadEmployeesFallback() {
        this.employees = [
            { id: 1, name: 'John Doe', email: 'john@example.com', username: 'john.doe', language: 'da', password: 'temp123', email_status: 'sent', sent_date: '2024-01-15', last_email_date: '2024-08-20 14:25:00', error_message: '' },
            { id: 2, name: 'Jane Smith', email: 'jane@example.com', username: 'jane.smith', language: 'en', password: 'temp456', email_status: 'pending', sent_date: '', last_email_date: '2024-08-18 10:30:00', error_message: '' },
            { id: 3, name: 'Bob Johnson', email: 'bob@example.com', username: 'bob.johnson', language: 'da', password: 'temp789', email_status: 'error', sent_date: '2024-01-10', last_email_date: '2024-08-17 16:45:00', error_message: 'Invalid email' },
            { id: 4, name: 'Alice Brown', email: 'alice@example.com', username: 'alice.brown', language: 'sv', password: 'temp101', email_status: 'sent', sent_date: '2024-01-12', last_email_date: '2024-08-19 11:20:00', error_message: '' },
            { id: 5, name: 'Charlie Wilson', email: 'charlie@example.com', username: 'charlie.wilson', language: 'en', password: 'temp202', email_status: 'pending', sent_date: '', last_email_date: '2024-08-16 13:15:00', error_message: '' },
            { id: 6, name: 'Diana Davis', email: 'diana@example.com', username: 'diana.davis', language: 'no', password: 'temp303', email_status: 'sent', sent_date: '2024-01-14', last_email_date: '2024-08-21 09:10:00', error_message: '' }
        ];
    }

    async loadTemplates() {
        try {
            console.log('DataManager: Loading templates from API...');
            const apiClient = new APIClient();
            const response = await apiClient.getTemplates();
            
            if (response.status === 1) {
                this.templates = response.data || [];
                console.log('Templates loaded from database:', this.templates.length);
            } else {
                console.error('Failed to load templates from API:', response);
                this.templates = [];
            }
        } catch (error) {
            console.error('Error loading templates from API:', error);
            this.templates = [];
        }
    }


    async loadLanguages() {
        this.languages = MAILPORTAL_CONFIG.supportedLanguages;
    }

    async loadShopInfo() {
        try {
            const apiClient = new APIClient();
            const response = await apiClient.getShopInfo();
            
            if (response.status === 1) {
                this.shopInfo = response.data;
                console.log('Shop info loaded:', this.shopInfo);
            }
        } catch (error) {
            console.error('Error loading shop info:', error);
            // Fallback shop info
            this.shopInfo = {
                type: 'valgshop',
                name: 'Valgshop',
                customer_name: 'Test Firma ApS',
                customer_short: 'Test Firma',
                description: 'Shop hvor medarbejdere kan vælge mellem forskellige gaver'
            };
        }
    }

    getLanguageName(code) {
        const lang = this.languages.find(l => l.code === code);
        return lang ? lang.name : code;
    }

    findTemplateByName(name) {
        return this.templates.find(t => t.group_name === name);
    }
    
    findTemplateById(id) {
        return this.templates.find(t => t.id === id);
    }

    async loadSendings() {
        this.sendings = [
            {
                id: 1,
                template_name: 'Login Credentials',
                created_date: '2024-01-15 10:30:00',
                status: 'completed',
                total_recipients: 4,
                sent_count: 3,
                error_count: 1,
                recipients: [1, 2, 3, 4],
                sent_date: '2024-01-15 10:33:00',
                progress: 100
            },
            {
                id: 2,
                template_name: 'Christmas Gift Info',
                created_date: '2024-01-16 14:15:00',
                status: 'completed',
                total_recipients: 6,
                sent_count: 6,
                error_count: 0,
                recipients: [1, 2, 3, 4, 5, 6],
                sent_date: '2024-01-16 14:18:00',
                progress: 100
            },
            {
                id: 3,
                template_name: 'Password Reset',
                created_date: '2024-01-17 09:00:00',
                status: 'in_progress',
                total_recipients: 3,
                sent_count: 1,
                error_count: 0,
                recipients: [2, 5, 6],
                progress: 33
            },
            {
                id: 4,
                template_name: 'Login Credentials',
                created_date: '2024-08-22 10:30:00',
                status: 'draft',
                total_recipients: 2,
                sent_count: 0,
                error_count: 0,
                recipients: [1, 3],
                language: 'da',
                mail_server_id: 1,
                custom_template: {
                    group_name: 'Login Credentials',
                    languages: [
                        {
                            language: 'da',
                            subject: 'Dine login oplysninger - Tilpasset',
                            body: '<h2>Velkommen til systemet</h2><p>Hej {{name}},</p><p>Her er dine tilpassede login oplysninger:</p><p><strong>Brugernavn:</strong> {{username}}</p><p><strong>Adgangskode:</strong> {{password}}</p><p><strong>Link:</strong> {{link}}</p><p>Gyldig fra {{start_date}} til {{end_date}}</p>'
                        }
                    ]
                },
                progress: 0
            },
            {
                id: 5,
                template_name: 'Christmas Gift Info',
                created_date: '2024-08-22 14:20:00',
                status: 'draft',
                total_recipients: 4,
                sent_count: 0,
                error_count: 0,
                recipients: [2, 4, 5, 6],
                language: 'en',
                mail_server_id: 2,
                custom_template: {
                    group_name: 'Christmas Gift Info',
                    languages: [
                        {
                            language: 'en',
                            subject: 'Christmas Gifts Information - Modified',
                            body: '<h1>Season Greetings!</h1><p>Dear {{name}},</p><p>This is a customized template for Christmas gift information.</p><p>Valid period: {{start_date}} to {{end_date}}</p>'
                        }
                    ]
                },
                progress: 0
            }
        ];
    }

    createSending(templateName, recipientIds, language, mailServerId, customTemplate = null) {
        const newSending = {
            id: this.sendings.length + 1,
            template_name: templateName,
            created_date: new Date().toISOString().slice(0, 19).replace('T', ' '),
            status: 'pending',
            total_recipients: recipientIds.length,
            sent_count: 0,
            error_count: 0,
            recipients: recipientIds,
            language: language,
            mail_server_id: mailServerId,
            custom_template: customTemplate, // Store edited template if provided
            progress: 0
        };
        
        this.sendings.unshift(newSending);
        return newSending;
    }

    createDraft(templateName, recipientIds, language, mailServerId, customTemplate = null) {
        const newDraft = {
            id: this.sendings.length + 1,
            template_name: templateName,
            created_date: new Date().toISOString().slice(0, 19).replace('T', ' '),
            status: 'draft',
            total_recipients: recipientIds.length,
            sent_count: 0,
            error_count: 0,
            recipients: recipientIds,
            language: language,
            mail_server_id: mailServerId,
            custom_template: customTemplate,
            progress: 0
        };
        
        this.sendings.unshift(newDraft);
        return newDraft;
    }

    updateSending(sendingId, templateName, recipientIds, language, mailServerId, customTemplate = null) {
        const sending = this.sendings.find(s => s.id === parseInt(sendingId));
        if (sending) {
            sending.template_name = templateName;
            sending.recipients = recipientIds;
            sending.language = language;
            sending.mail_server_id = mailServerId;
            sending.custom_template = customTemplate;
            sending.total_recipients = recipientIds.length;
            return sending;
        }
        return null;
    }

    updateSendingProgress(sendingId, progress, sentCount, errorCount) {
        const sending = this.sendings.find(s => s.id === sendingId);
        if (sending) {
            sending.progress = progress;
            sending.sent_count = sentCount;
            sending.error_count = errorCount;
            
            if (progress >= 100) {
                sending.status = 'completed';
            } else if (progress > 0) {
                sending.status = 'in_progress';
            }
        }
        return sending;
    }

    getSendingById(id) {
        return this.sendings.find(s => s.id === parseInt(id));
    }

    deleteSending(id) {
        const index = this.sendings.findIndex(s => s.id === parseInt(id));
        if (index !== -1) {
            this.sendings.splice(index, 1);
            return true;
        }
        return false;
    }

    getMailServers() {
        return [
            {
                id: 1,
                name: 'Primary SMTP Server',
                host: 'smtp.gavefabrikken.dk',
                port: 587,
                username: 'noreply@gavefabrikken.dk',
                encryption: 'tls',
                is_default: true,
                status: 'active'
            },
            {
                id: 2,
                name: 'Backup SMTP Server',
                host: 'smtp-backup.gavefabrikken.dk',
                port: 587,
                username: 'noreply@gavefabrikken.dk',
                encryption: 'tls',
                is_default: false,
                status: 'active'
            },
            {
                id: 3,
                name: 'Development Server',
                host: 'smtp-dev.gavefabrikken.dk',
                port: 587,
                username: 'dev@gavefabrikken.dk',
                encryption: 'tls',
                is_default: false,
                status: 'inactive'
            }
        ];
    }
}