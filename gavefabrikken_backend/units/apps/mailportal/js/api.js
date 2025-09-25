// API and Base functionality
class SimpleBase {
    apiCall(endpoint, data = {}) {
        // Add shop context to all API calls
        const requestData = {
            ...data,
            shopType: SHOP_TYPE,
            shopID: SHOP_ID,
            companyID: COMPANY_ID,
            token: ACCESS_TOKEN
        };

        return new Promise((resolve, reject) => {
            $.post(
                MAILPORTAL_AJAX_URL + endpoint,
                requestData,
                function(response) {
                    if (response && response.status === 1) {
                        resolve(response);
                    } else {
                        reject(response);
                    }
                },
                "json"
            ).fail(function() {
                reject({message: 'Network error'});
            });
        });
    }

    async testService() {
        try {
            const response = await this.apiCall('testservice');
            console.log('MailPortal service:', response);
        } catch (error) {
            console.warn('MailPortal service not available, using offline mode:', error);
            // Continue with dummy data even if service is not available
        }
    }
}

// API Client for Backend Communication
class APIClient {
    constructor() {
        this.baseUrl = MAILPORTAL_AJAX_URL;
    }

    makeRequest(action, data = {}) {
        console.log(`makeRequest called for action: ${action}`);
        console.log('makeRequest data:', data);
        
        // Add shop context to all API calls
        const requestData = {
            ...data,
            shopType: SHOP_TYPE,
            shopID: SHOP_ID,
            companyID: COMPANY_ID,
            token: ACCESS_TOKEN
        };

        console.log('makeRequest requestData:', requestData);
        console.log('makeRequest URL:', this.baseUrl + action);

        return new Promise((resolve, reject) => {
            $.post(this.baseUrl + action, requestData, function(response) {
                console.log(`makeRequest response for ${action}:`, response);
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    console.log(`makeRequest parsed response for ${action}:`, response);
                    resolve(response);
                } catch (error) {
                    console.error(`JSON parse error for ${action}:`, error);
                    console.error('Raw response:', response);
                    reject(error);
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error(`Request failed for ${action}:`, xhr.responseText);
                reject(error);
            });
        });
    }

    async call(endpoint, data = {}) {
        return this.makeRequest(endpoint, data);
    }

    // Sending endpoints
    async getSendings() {
        return this.call('getSendings');
    }

    async createSending(templateName, recipientIds) {
        return this.call('createSending', {
            template_name: templateName,
            recipient_ids: JSON.stringify(recipientIds)
        });
    }

    async processSending(sendingId, currentProgress = 0) {
        return this.call('processSending', {
            sending_id: sendingId,
            current_progress: currentProgress
        });
    }

    async getSendingDetails(sendingId) {
        return this.call('getSendingDetails', {
            sending_id: sendingId
        });
    }

    async getTemplates() {
        return this.call('getTemplates');
    }

    async createTemplate(groupName, type = 'custom', languages = ['da']) {
        return this.call('createTemplate', {
            group_name: groupName,
            type: type,
            languages: JSON.stringify(languages)
        });
    }

    async saveTemplate(templateData) {
        console.log('API.saveTemplate called with:', templateData);
        
        const postData = {
            id: templateData.id,
            name: templateData.name,
            language: templateData.language || 'da',
            subject: templateData.subject || '',
            body: templateData.body || '',
            is_default: templateData.is_default || 0
        };
        
        console.log('API.saveTemplate sending POST data:', postData);
        
        return this.call('saveTemplate', postData);
    }

    async deleteTemplate(groupName) {
        return this.call('deleteTemplate', {
            group_name: groupName
        });
    }

    async sendTestEmail(templateName, language, subject, body, testEmail) {
        return this.call('sendTestEmail', {
            template_name: templateName,
            language: language,
            subject: subject,
            body: body,
            test_email: testEmail
        });
    }

    async getShopInfo() {
        return this.call('getShopInfo');
    }

    async updateEmployee(employeeData) {
        return this.call('updateEmployee', employeeData);
    }
}