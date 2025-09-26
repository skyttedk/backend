<?php

namespace GFUnit\apps\mailportal\services;

require_once dirname(__DIR__) . '/dummy_data.php';
use DummyData;

class StatisticsService
{
    /**
     * Hent system statistikker
     */
    public function getStatistics()
    {
        $employees = DummyData::getEmployees();
        
        $total = count($employees);
        $sent = 0;
        $errors = 0;
        $pending = 0;
        
        foreach ($employees as $emp) {
            if ($emp['mail_sent']) {
                if ($emp['has_error']) {
                    $errors++;
                } else {
                    $sent++;
                }
            } else {
                $pending++;
            }
        }
        
        $stats = [
            'total_employees' => $total,
            'emails_sent' => $sent,
            'emails_pending' => $pending,
            'emails_failed' => $errors,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 1) : 0
        ];
        
        return $stats;
    }
    
    /**
     * Hent sprog statistikker
     */
    public function getLanguageStatistics()
    {
        $employees = DummyData::getEmployees();
        $languages = DummyData::getLanguages();
        
        $lang_stats = [];
        foreach ($languages as $lang) {
            $lang_stats[$lang['code']] = [
                'name' => $lang['name'],
                'count' => 0
            ];
        }
        
        foreach ($employees as $emp) {
            if (isset($lang_stats[$emp['language']])) {
                $lang_stats[$emp['language']]['count']++;
            }
        }
        
        return $lang_stats;
    }
    
    /**
     * Hent forsendelse statistikker
     */
    public function getSendingStatistics()
    {
        $sendings = DummyData::getSendings();
        
        $total_sendings = count($sendings);
        $completed = 0;
        $in_progress = 0;
        $failed = 0;
        $total_recipients = 0;
        $total_sent = 0;
        $total_errors = 0;
        
        foreach ($sendings as $sending) {
            switch ($sending['status']) {
                case 'completed':
                    $completed++;
                    break;
                case 'in_progress':
                    $in_progress++;
                    break;
                case 'failed':
                    $failed++;
                    break;
            }
            
            $total_recipients += $sending['total_recipients'];
            $total_sent += $sending['sent_count'];
            $total_errors += $sending['error_count'];
        }
        
        return [
            'total_sendings' => $total_sendings,
            'completed' => $completed,
            'in_progress' => $in_progress,
            'failed' => $failed,
            'total_recipients' => $total_recipients,
            'total_sent' => $total_sent,
            'total_errors' => $total_errors,
            'success_rate' => $total_recipients > 0 ? round(($total_sent / $total_recipients) * 100, 1) : 0
        ];
    }
}