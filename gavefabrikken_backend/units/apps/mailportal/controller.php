<?php

namespace GFUnit\apps\mailportal;
use GFBiz\units\UnitController;

// Inkluder service klasser
require_once __DIR__ . '/services/EmployeeService.php';
require_once __DIR__ . '/services/TemplateService.php';
require_once __DIR__ . '/services/SendingService.php';
require_once __DIR__ . '/services/EmailService.php';
require_once __DIR__ . '/services/StatisticsService.php';
require_once __DIR__ . '/dummy_data.php';

use GFUnit\apps\mailportal\services\EmployeeService;
use GFUnit\apps\mailportal\services\TemplateService;
use GFUnit\apps\mailportal\services\SendingService;
use GFUnit\apps\mailportal\services\EmailService;
use GFUnit\apps\mailportal\services\StatisticsService;
use DummyData;

// Håndter statiske filer først
$request_uri = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($request_uri);
$path = $parsed_url['path'];

// Server statiske filer direkte
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/', $path)) {
    // Map units/apps/mailportal/ URLs til faktiske filer i root
    if (strpos($path, '/units/apps/mailportal/') !== false) {
        // Fjern units/apps/mailportal prefix og map til root
        $actual_path = str_replace('/units/apps/mailportal/', '/', $path);
        $file_path = __DIR__ . $actual_path;
    } else {
        $file_path = __DIR__ . $path;
    }
    
    if (file_exists($file_path)) {
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $mime_type = $mime_types[$extension] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mime_type);
        readfile($file_path);
        exit;
    } else {
        http_response_code(404);
        echo "File not found: " . $file_path . " (mapped from: " . $path . ")";
        exit;
    }
}

/**
 * Tynd Controller klasse - delegerer forretningslogik til service klasser
 */
class Controller extends UnitController
{
    private $employeeService;
    private $templateService;
    private $sendingService;
    private $emailService;
    private $statisticsService;

    public function __construct()
    {
        parent::__construct(__FILE__);
        
        // Extract shop context from request
        $shopType = $_POST['shopType'] ?? $_GET['shopType'] ?? null;
        $shopId = $_POST['shopID'] ?? $_GET['shopID'] ?? null;
        $companyId = $_POST['companyID'] ?? $_GET['companyID'] ?? null;
        
        // Initialiser services with shop context
        $this->employeeService = new EmployeeService($shopType, $shopId, $companyId);
        $this->templateService = new TemplateService($shopType, $shopId, $companyId);
        $this->sendingService = new SendingService();
        $this->emailService = new EmailService();
        $this->statisticsService = new StatisticsService();
    }

    /**
     * Test service endpoint
     */
    public function testservice() 
    {
        $this->jsonResponse(1, "MailPortal service is running!");
    }

    // =====================
    // Employee endpoints
    // =====================
    
    public function getEmployees()
    {
        $employees = $this->employeeService->getAll();
        $this->jsonResponse(1, null, $employees);
    }

    public function updateEmployee()
    {
        // Only allow name and email updates (following MailPortal restrictions)
        $data = [];
        
        if (isset($_POST["name"]) && !empty(trimgf($_POST["name"]))) {
            $data['name'] = trimgf($_POST["name"]);
        }
        
        if (isset($_POST["email"]) && !empty(trimgf($_POST["email"]))) {
            $data['email'] = trimgf($_POST["email"]);
        }
        
        // Validate required fields
        if (empty($data)) {
            $this->jsonResponse(0, "No valid data provided for update");
            return;
        }
        
        $id = intval($_POST["id"]);
        if ($id <= 0) {
            $this->jsonResponse(0, "Invalid user ID");
            return;
        }
        
        // Validate email format if provided
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(0, "Invalid email format");
            return;
        }
        
        try {
            $result = $this->employeeService->update($id, $data);
            $this->jsonResponse($result['success'] ? 1 : 0, $result['message']);
            
        } catch (Exception $e) {
            error_log("Controller updateEmployee error: " . $e->getMessage());
            $this->jsonResponse(0, "Update failed");
        }
    }

    public function deleteEmployee()
    {
        $result = $this->employeeService->delete($_POST["id"]);
        $this->jsonResponse($result['success'] ? 1 : 0, $result['message']);
    }

    // =====================
    // Email endpoints
    // =====================
    
    public function sendEmail()
    {
        $employee_id = $_POST["employee_id"];
        $template_id = isset($_POST["template_id"]) ? $_POST["template_id"] : null;
        
        $result = $this->emailService->send($employee_id, $template_id);
        
        if ($result['success']) {
            $this->jsonResponse(1, $result['message'], ['sent_date' => $result['sent_date']]);
        } else {
            $this->jsonResponse(0, $result['message'], ['error' => $result['error']]);
        }
    }

    public function resendEmail()
    {
        $employee_id = $_POST["employee_id"];
        $template_id = isset($_POST["template_id"]) ? $_POST["template_id"] : null;
        
        $result = $this->emailService->resend($employee_id, $template_id);
        
        if ($result['success']) {
            $this->jsonResponse(1, $result['message'], ['sent_date' => $result['sent_date']]);
        } else {
            $this->jsonResponse(0, $result['message'], ['error' => $result['error']]);
        }
    }

    public function sendBulkEmails()
    {
        $employee_ids = json_decode($_POST["employee_ids"], true);
        $template_id = $_POST["template_id"];
        
        $result = $this->emailService->sendBulk($employee_ids, $template_id);
        $this->jsonResponse(1, null, $result['data']);
    }

    public function sendTestEmail()
    {
        $data = [
            'template_name' => $_POST["template_name"],
            'language' => $_POST["language"],
            'subject' => $_POST["subject"],
            'body' => $_POST["body"],
            'test_email' => $_POST["test_email"]
        ];
        
        $result = $this->emailService->sendTest($data);
        
        if ($result['success']) {
            $this->jsonResponse(1, $result['message'], ['sent_date' => $result['sent_date']]);
        } else {
            $this->jsonResponse(0, $result['message'], ['error' => $result['error']]);
        }
    }

    public function previewEmail()
    {
        $template_id = $_POST["template_id"];
        $employee_id = $_POST["employee_id"];
        
        $result = $this->emailService->preview($template_id, $employee_id);
        
        if ($result['success']) {
            $this->jsonResponse(1, null, $result['data']);
        } else {
            $this->jsonResponse(0, $result['message']);
        }
    }

    public function getEmailHistory()
    {
        $employee_id = isset($_POST["employee_id"]) ? $_POST["employee_id"] : null;
        $history = $this->emailService->getHistory($employee_id);
        $this->jsonResponse(1, null, $history);
    }

    // =====================
    // Template endpoints
    // =====================
    
    public function getTemplates()
    {
        error_log("Controller getTemplates called");
        try {
            $templates = $this->templateService->getAll();
            error_log("Controller: TemplateService returned " . count($templates) . " templates");
            $this->jsonResponse(1, null, $templates);
        } catch (\Exception $e) {
            error_log("Controller getTemplates error: " . $e->getMessage());
            $this->jsonResponse(0, $e->getMessage(), []);
        }
    }

    public function getTemplate()
    {
        $template = $this->templateService->getById($_POST["id"]);
        $this->jsonResponse(1, null, $template);
    }

    public function saveTemplate()
    {
        error_log("saveTemplate started");
        error_log("POST data: " . print_r($_POST, true));
        
        $data = [
            'id' => isset($_POST["id"]) ? $_POST["id"] : null,
            'name' => $_POST["name"],
            'language' => $_POST["language"],
            'subject' => $_POST["subject"],
            'body' => $_POST["body"],
            'is_default' => isset($_POST["is_default"]) ? $_POST["is_default"] : 0
        ];
        
        error_log("saveTemplate data: " . print_r($data, true));
        
        $result = $this->templateService->save($data);
        
        error_log("templateService->save result: " . print_r($result, true));
        
        // Commit database changes
        error_log("About to call commit()");
        \System::connection()->commit();
        error_log("Commit() called successfully");
        
        $this->jsonResponse(1, $result['message'], $result['data']);
        error_log("jsonResponse sent");
    }

    public function createTemplate()
    {
        error_log("createTemplate started");
        error_log("POST data: " . print_r($_POST, true));
        
        $data = [
            'group_name' => $_POST["group_name"],
            'type' => isset($_POST["type"]) ? $_POST["type"] : 'custom',
            'languages' => isset($_POST["languages"]) ? json_decode($_POST["languages"], true) : ['da']
        ];
        
        error_log("createTemplate data: " . print_r($data, true));
        
        $result = $this->templateService->createGroup($data);
        
        error_log("templateService->createGroup result: " . print_r($result, true));
        
        // Commit database changes
        error_log("About to call commit() in createTemplate");
        \System::connection()->commit();
        error_log("Commit() called successfully in createTemplate");
        
        $this->jsonResponse(1, $result['message'], $result['data']);
        error_log("jsonResponse sent in createTemplate");
    }

    public function deleteTemplate()
    {
        $result = $this->templateService->delete($_POST["id"]);
        
        // Commit database changes
        \System::connection()->commit();
        
        $this->jsonResponse($result['success'] ? 1 : 0, $result['message']);
    }

    // =====================
    // Sending endpoints
    // =====================
    
    public function getSendings()
    {
        $sendings = $this->sendingService->getAll();
        $this->jsonResponse(1, null, $sendings);
    }

    public function createSending()
    {
        $template_name = $_POST["template_name"];
        $recipient_ids = json_decode($_POST["recipient_ids"], true);
        
        $result = $this->sendingService->create($template_name, $recipient_ids);
        $this->jsonResponse(1, $result['message'], $result['data']);
    }

    public function processSending()
    {
        $sending_id = $_POST["sending_id"];
        $current_progress = isset($_POST["current_progress"]) ? intval($_POST["current_progress"]) : 0;
        
        $result = $this->sendingService->process($sending_id, $current_progress);
        $this->jsonResponse(1, null, $result['data']);
    }

    public function getSendingDetails()
    {
        $sending_id = $_POST["sending_id"];
        
        $result = $this->sendingService->getDetails($sending_id);
        
        if ($result['success']) {
            $this->jsonResponse(1, null, $result['data']);
        } else {
            $this->jsonResponse(0, $result['message']);
        }
    }

    // =====================
    // Statistics endpoints
    // =====================
    
    public function getStatistics()
    {
        $stats = $this->statisticsService->getStatistics();
        $this->jsonResponse(1, null, $stats);
    }

    // =====================
    // Support endpoints
    // =====================
    
    public function getLanguages()
    {
        $languages = DummyData::getLanguages();
        $this->jsonResponse(1, null, $languages);
    }

    public function getShopInfo()
    {
        $shopInfo = DummyData::getShopInfo();
        $this->jsonResponse(1, null, $shopInfo);
    }

    // =====================
    // Helper metoder
    // =====================
    
    /**
     * Send JSON response
     */
    private function jsonResponse($status, $message = null, $data = null)
    {
        $response = ['status' => $status];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
    }
}

// Initialiser controller
new Controller();