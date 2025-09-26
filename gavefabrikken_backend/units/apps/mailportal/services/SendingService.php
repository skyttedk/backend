<?php

namespace GFUnit\apps\mailportal\services;

require_once dirname(__DIR__) . '/dummy_data.php';
use DummyData;

class SendingService
{
    private $employeeService;
    
    public function __construct()
    {
        $this->employeeService = new EmployeeService();
    }
    
    /**
     * Hent alle forsendelser
     */
    public function getAll()
    {
        return DummyData::getSendings();
    }
    
    /**
     * Opret ny forsendelse
     */
    public function create($template_name, $recipient_ids)
    {
        // Opret ny forsendelse record
        $sending = [
            'id' => time(),
            'template_name' => $template_name,
            'created_date' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'total_recipients' => count($recipient_ids),
            'sent_count' => 0,
            'error_count' => 0,
            'recipients' => $recipient_ids,
            'progress' => 0
        ];
        
        return [
            'success' => true,
            'data' => $sending,
            'message' => 'Sending created successfully'
        ];
    }
    
    /**
     * Proces forsendelse (simuleret)
     */
    public function process($sending_id, $current_progress = 0)
    {
        // Simuler behandling trin
        $new_progress = min($current_progress + 25, 100); // Stigning med 25%
        $sent_count = intval(($new_progress / 100) * 4); // Antag 4 modtagere for demo
        $error_count = rand(0, 1); // Tilfældig fejl
        
        $result = [
            'sending_id' => $sending_id,
            'progress' => $new_progress,
            'sent_count' => $sent_count,
            'error_count' => $error_count,
            'status' => $new_progress >= 100 ? 'completed' : 'in_progress',
            'message' => $new_progress >= 100 ? 'Sending completed' : 'Processing...'
        ];
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * Hent forsendelse detaljer
     */
    public function getDetails($sending_id)
    {
        $sendings = DummyData::getSendings();
        
        $sending = null;
        foreach ($sendings as $s) {
            if ($s['id'] == $sending_id) {
                $sending = $s;
                break;
            }
        }
        
        if (!$sending) {
            return [
                'success' => false,
                'message' => 'Sending not found'
            ];
        }
        
        // Tilføj modtager detaljer
        $employees = $this->employeeService->getByIds($sending['recipients']);
        $recipient_details = [];
        
        foreach ($employees as $emp) {
            $recipient_details[] = [
                'id' => $emp['id'],
                'name' => $emp['name'],
                'email' => $emp['email'],
                'status' => rand(0, 10) > 8 ? 'error' : 'sent', // 20% fejl rate
                'sent_date' => $sending['created_date'],
                'error_message' => rand(0, 10) > 8 ? 'SMTP connection failed' : ''
            ];
        }
        
        $sending['recipient_details'] = $recipient_details;
        
        return [
            'success' => true,
            'data' => $sending
        ];
    }
    
    /**
     * Hent forsendelse efter ID
     */
    public function getById($id)
    {
        $sendings = DummyData::getSendings();
        foreach ($sendings as $sending) {
            if ($sending['id'] == $id) {
                return $sending;
            }
        }
        return null;
    }
}