<?php

namespace GFUnit\apps\mailportal\services;

require_once dirname(__DIR__) . '/dummy_data.php';
use DummyData;

class EmailService
{
    private $templateService;
    private $employeeService;
    
    public function __construct()
    {
        $this->templateService = new TemplateService();
        $this->employeeService = new EmployeeService();
    }
    
    /**
     * Send email til enkelt medarbejder
     */
    public function send($employee_id, $template_id = null)
    {
        // Simuler email afsendelse med tilfældig succes/fejl
        $success = rand(0, 10) > 1; // 90% succes rate
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'sent_date' => date('Y-m-d H:i:s')
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send email',
                'error' => 'SMTP connection timeout'
            ];
        }
    }
    
    /**
     * Gensend email
     */
    public function resend($employee_id, $template_id = null)
    {
        // Simuler email gensendelse
        $success = rand(0, 10) > 2; // 80% succes rate for gensendelse
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Email resent successfully',
                'sent_date' => date('Y-m-d H:i:s')
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to resend email',
                'error' => 'SMTP connection failed'
            ];
        }
    }
    
    /**
     * Send bulk emails
     */
    public function sendBulk($employee_ids, $template_id)
    {
        $results = [];
        
        foreach ($employee_ids as $id) {
            $success = rand(0, 10) > 1; // 90% succes rate
            $results[] = [
                'employee_id' => $id,
                'success' => $success,
                'message' => $success ? 'Email sent' : 'Failed to send',
                'error' => $success ? null : 'SMTP error'
            ];
        }
        
        return [
            'success' => true,
            'data' => $results
        ];
    }
    
    /**
     * Send test email
     */
    public function sendTest($data)
    {
        $template_name = $data['template_name'];
        $language = $data['language'];
        $subject = $data['subject'];
        $body = $data['body'];
        $test_email = $data['test_email'];
        
        // Simuler test email afsendelse
        $success = rand(0, 10) > 1; // 90% succes rate
        
        if ($success) {
            return [
                'success' => true,
                'message' => "Test email sendt til " . $test_email,
                'sent_date' => date('Y-m-d H:i:s')
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Fejl ved afsendelse af test email',
                'error' => 'SMTP connection timeout'
            ];
        }
    }
    
    /**
     * Forhåndsvis email
     */
    public function preview($template_id, $employee_id)
    {
        $template = $this->templateService->getById($template_id);
        $employee = $this->employeeService->getById($employee_id);
        
        if (!$template || !$employee) {
            return [
                'success' => false,
                'message' => 'Template or employee not found'
            ];
        }
        
        // TODO: Implement placeholder replacement when needed
        $processed = [
            'subject' => $template['subject'],
            'body' => $template['body']
        ];
        
        return [
            'success' => true,
            'data' => [
                'subject' => $processed['subject'],
                'body' => $processed['body'],
                'to' => $employee['email']
            ]
        ];
    }
    
    /**
     * Hent email historik
     */
    public function getHistory($employee_id = null)
    {
        return DummyData::getEmailHistory($employee_id);
    }
}