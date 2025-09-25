<?php

namespace GFUnit\apps\mailportal\services;

require_once dirname(__DIR__) . '/dummy_data.php';

use DummyData;

class TemplateService
{
    private $shopType;
    private $shopId;
    private $companyId;
    
    public function __construct($shopType = null, $shopId = null, $companyId = null) 
    {
        $this->shopType = $shopType;
        $this->shopId = $shopId;
        $this->companyId = $companyId;
    }
    
    /**
     * Hent alle skabeloner fra database via ActiveRecord models
     */
    public function getAll()
    {
        $this->log('info', 'template', 'TemplateService getAll called', 'shopId: ' . ($this->shopId ?? 'null') . ', companyId: ' . ($this->companyId ?? 'null'));
        
        try {
            $templates = [];
            
            // Query templates using ActiveRecord similar to EmployeeService pattern
            if ($this->shopType === 'valgshop' && $this->shopId) {
                // Query valgshop templates by shop_id
                $templates = \MailportalTemplates::find('all', array(
                    'conditions' => array('shop_id = ? AND is_active = 1', $this->shopId),
                    'order' => 'sort_order ASC, name ASC'
                ));
            } elseif ($this->shopType === 'cardshop' && $this->companyId) {
                // Query cardshop templates by company_id
                $templates = \MailportalTemplates::find('all', array(
                    'conditions' => array('company_id = ? AND is_active = 1', $this->companyId),
                    'order' => 'sort_order ASC, name ASC'
                ));
            }
            
            $this->log('info', 'template', 'Templates loaded successfully', 'Found ' . count($templates) . ' templates');
            
            // Format for frontend
            $result = [];
            foreach ($templates as $template) {
                // Get language content using ActiveRecord
                $contents = \MailPortalTemplateContent::find('all', array(
                    'conditions' => array('template_id = ?', $template->id)
                ));
                
                $languages = [];
                foreach ($contents as $content) {
                    $languages[] = [
                        'language' => $content->language_code,
                        'subject' => $content->subject_localized,
                        'body' => $content->content_html
                    ];
                }
                
                $result[] = [
                    'id' => $template->id,
                    'group_name' => $template->name,
                    'type' => $template->template_type,
                    'languages' => $languages
                ];
            }
            
            $this->log('info', 'template', 'Templates formatted for frontend', 'Returning ' . count($result) . ' templates');
            return $result;
            
        } catch (\Exception $e) {
            $this->log('error', 'template', 'TemplateService getAll error', $e->getMessage());
            return [];
        }
    }
    
    
    /**
     * Hent skabelon efter ID fra database
     */
    public function getById($id)
    {
        try {
            $template = \MailportalTemplates::find($id);
            
            if (!$template) {
                return null;
            }
            
            // Get language content using ActiveRecord
            $contents = \MailPortalTemplateContent::find('all', array(
                'conditions' => array('template_id = ?', $template->id)
            ));
            
            $languages = [];
            foreach ($contents as $content) {
                $languages[] = [
                    'language' => $content->language_code,
                    'subject' => $content->subject_localized,
                    'body' => $content->content_html
                ];
            }
            
            return [
                'id' => $template->id,
                'group_name' => $template->name,
                'type' => $template->template_type,
                'languages' => $languages
            ];
            
        } catch (\Exception $e) {
            $this->log('error', 'template', 'TemplateService getById error', $e->getMessage());
            return null;
        }
    }
    
    /**
     * Gem skabelon (opret eller opdater) - kun ActiveRecord
     */
    public function save($data)
    {
        try {
            $id = isset($data['id']) ? intval($data['id']) : null;
            $language = isset($data['language']) ? $data['language'] : 'da';
            
            if ($id) {
                // Update existing template using ActiveRecord
                $template = \MailportalTemplates::find($id);
                
                if (!$template) {
                    throw new \Exception("Template not found");
                }
                
                // Update template metadata
                if (isset($data['name'])) {
                    $template->name = $data['name'];
                    $template->updated_date = date('Y-m-d H:i:s');
                    $template->save();
                }
                
                // Find or create language content
                $content = \MailPortalTemplateContent::find('first', [
                    'conditions' => ['template_id = ? AND language_code = ?', $id, $language]
                ]);
                
                if (!$content) {
                    // Create new language content
                    $content = new \MailPortalTemplateContent();
                    $content->template_id = $id;
                    $content->language_code = $language;
                }
                
                // Update content
                $content->subject_localized = $data['subject'];
                $content->content_html = $data['body'];
                $content->content_text = strip_tags($data['body']);
                $content->save();
                
            } else {
                // Create new template using ActiveRecord
                $template = new \MailportalTemplates();
                $template->shop_id = intval($this->shopId ?? 0);
                $template->company_id = intval($this->companyId ?? 0);
                $template->name = $data['name'];
                $template->subject = $data['subject'];
                $template->template_type = 'custom';
                $template->template_source = 'shop';
                $template->is_active = 1;
                $template->is_default = isset($data['is_default']) ? $data['is_default'] : 0;
                $template->sort_order = 0;
                $template->created_by = \router::$systemUser ? \router::$systemUser->id : null;
                $template->created_date = date('Y-m-d H:i:s');
                $template->updated_date = date('Y-m-d H:i:s');
                $template->save();
                
                $id = $template->id;
                
                // Create language content
                $content = new \MailPortalTemplateContent();
                $content->template_id = $id;
                $content->language_code = $language;
                $content->subject_localized = $data['subject'];
                $content->content_html = $data['body'];
                $content->content_text = strip_tags($data['body']);
                $content->save();
            }
            
            return [
                'success' => true,
                'data' => [
                    'id' => $id,
                    'name' => $data['name'],
                    'language' => $language,
                    'subject' => $data['subject'],
                    'body' => $data['body']
                ],
                'message' => 'Template saved successfully'
            ];
            
        } catch (\Exception $e) {
            $this->log('error', 'template', 'Template save failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to save template: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Opret ny skabelon gruppe med database integration
     */
    public function createGroup($data)
    {
        $group_name = $data['group_name'];
        $type = isset($data['type']) ? $data['type'] : 'custom';
        $languages = isset($data['languages']) ? $data['languages'] : ['da'];
        
        try {
            $this->log('info', 'template', 'Creating template group', 'Name: ' . $group_name . ', Languages: ' . json_encode($languages));
            
            // Create main template record using ActiveRecord
            $template = new \MailportalTemplates();
            $template->shop_id = intval($this->shopId ?? 0);
            $template->company_id = intval($this->companyId ?? 0);
            $template->name = $group_name;
            $template->subject = 'Nyt emne';
            $template->template_type = $type;
            $template->template_source = 'shop';
            $template->parent_template_id = null;
            $template->is_customized = 0;
            $template->is_active = 1;
            $template->is_default = 0;
            $template->sort_order = 0;
            $template->created_by = \router::$systemUser ? \router::$systemUser->id : null;
            $template->created_date = date('Y-m-d H:i:s');
            $template->updated_date = date('Y-m-d H:i:s');
            
            // Save template using ActiveRecord
            $template->save();
            
            $this->log('info', 'template', 'Template created successfully', 'Template ID: ' . $template->id);
            
            // Create language content versions
            $language_versions = [];
            foreach ($languages as $lang) {
                $placeholders = json_encode([
                    'name' => '{{name}}',
                    'username' => '{{username}}',
                    'password' => '{{password}}',
                    'link' => '{{link}}',
                    'date' => '{{date}}'
                ]);
                
                $content = new \MailPortalTemplateContent();
                $content->template_id = $template->id;
                $content->language_code = $lang;
                $content->subject_localized = 'Nyt emne';
                $content->content_html = '<p>Skriv dit indhold her...</p>';
                $content->content_text = 'Skriv dit indhold her...';
                $content->placeholders = $placeholders;
                
                // Save content using ActiveRecord
                $content->save();
                
                $language_versions[] = [
                    'language' => $lang,
                    'subject' => 'Nyt emne',
                    'body' => '<p>Skriv dit indhold her...</p>'
                ];
                
                $this->log('debug', 'template', 'Language content created', 'Language: ' . $lang . ', Template ID: ' . $template->id);
            }
            
            // Return format matching frontend expectations
            $new_template = [
                'id' => $template->id,
                'group_name' => $group_name,
                'type' => $type,
                'languages' => $language_versions
            ];
            
            return [
                'success' => true,
                'data' => $new_template,
                'message' => 'Template created successfully'
            ];
            
        } catch (\Exception $e) {
            $this->log('error', 'template', 'Template creation failed', $e->getMessage());
            
            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to create template: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Soft delete skabelon (deaktiver template)
     * INGEN fysisk DELETE - kun UPDATE til is_active = 0
     */
    public function delete($id)
    {
        try {
            $id = intval($id);
            
            // Find template using ActiveRecord
            $template = \MailportalTemplates::find($id);
            
            if (!$template) {
                return [
                    'success' => false,
                    'message' => 'Template not found'
                ];
            }
            
            // Soft delete via UPDATE operation (following security constraints)
            $template->is_active = 0;
            $template->updated_date = date('Y-m-d H:i:s');
            $template->save();
            
            return [
                'success' => true,
                'message' => 'Template deactivated successfully'
            ];
            
        } catch (\Exception $e) {
            $this->log('error', 'template', 'Template deletion failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to deactivate template: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Log to database via MailportalLogs model
     */
    private function log($level, $category, $message, $details = null)
    {
        try {
            $log = new \MailportalLogs();
            $log->shop_id = $this->shopId ?? 0;
            $log->company_id = $this->companyId ?? 0;
            $log->system_user_id = \router::$systemUser ? \router::$systemUser->id : null;
            $log->level = $level;
            $log->category = $category;
            $log->message = $message;
            $log->details = $details;
            $log->context_data = null;
            $log->ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $log->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $log->request_uri = $_SERVER['REQUEST_URI'] ?? null;
            $log->created_date = date('Y-m-d H:i:s');
            
            $log->save();
        } catch (\Exception $e) {
            // Fallback to error_log if database logging fails
            error_log("MailportalLogs failed: " . $e->getMessage() . " | Original: [$level] $category: $message");
        }
    }
}