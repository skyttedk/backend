<?php

Class mailTemplateCustomController Extends baseController
{
    public function index()
    {
        echo "Mail Template Custom Controller Active";
    }

    /**
     * Get mail template by language and shop (excluding deleted templates with negative shop_id)
     */
    public function getByLanguageAndShop()
    {
        try {
            $languageId = intval($_POST['language_id']);
            $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : 0;

            // Get base template from mail_template
            $baseTemplate = $this->getTemplateExcludingDeleted($shopId, $languageId);

            if (!$baseTemplate) {
                response::success(json_encode(array()));
                return;
            }

            // Get custom content from mailportal system (fallback if tables don't exist)
            $customContent = $this->getMailportalContent($shopId, $languageId);

            // Combine base template info with custom content from mailportal
            $result = array(
                'id' => $baseTemplate->id,
                'shop_id' => $shopId,
                'language_id' => $languageId,
                'sender_receipt' => $baseTemplate->sender_receipt,
                'subject_receipt' => $customContent['subject'] ?: $baseTemplate->subject_receipt,
                'template_receipt' => $baseTemplate->template_receipt,
                // Custom content from mailportal tables
                'custom_text1' => $customContent['text1'],
                'custom_delivery_info' => $customContent['delivery_info'],
                'custom_text2' => $customContent['text2'],
                'custom_receipt_pos1' => $customContent['receipt_pos1']
            );

            response::success(json_encode($result));

        } catch (Exception $e) {
            error_log("getByLanguageAndShop error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Save custom template (unified method for create/update)
     */
    public function saveCustomTemplate()
    {
        try {
            $languageId = intval($_POST['language_id']);
            $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : 0;

            // Save to mailportal system (when tables exist)
            $customContent = array(
                'subject' => $_POST['subject_receipt'] ?? '',
                'text1' => $_POST['custom_text1'] ?? '',
                'delivery_info' => $_POST['custom_delivery_info'] ?? '',
                'text2' => $_POST['custom_text2'] ?? '',
                'receipt_pos1' => $_POST['custom_receipt_pos1'] ?? ''
            );

            $this->saveToMailportal($shopId, $languageId, $customContent);

            // Sync to mail_template for mail program
            $this->syncToMailTemplate($shopId, $languageId, $customContent);

            response::success(json_encode([
                'success' => true,
                'message' => 'Template saved successfully',
                'shop_id' => $shopId,
                'language_id' => $languageId
            ]));

        } catch (Exception $e) {
            error_log("saveCustomTemplate error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Create a new custom mail template (legacy method)
     */
    public function createCustomTemplate()
    {
        try {
            $languageId = intval($_POST['language_id']);
            $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : 0;

            // Check if template already exists for this shop/language combination
            $existingTemplate = MailTemplate::find_by_shop_id_and_language_id($shopId, $languageId);

            if ($existingTemplate) {
                // Update existing template instead
                return $this->updateExistingTemplate($existingTemplate);
            }

            // Get base template to copy structure from
            $baseTemplate = MailTemplate::getTemplate(0, $languageId);
            if (!$baseTemplate) {
                throw new Exception("No base template found for language ID: " . $languageId);
            }

            // Create new template based on the base template
            $newTemplate = new MailTemplate();
            $newTemplate->shop_id = $shopId;
            $newTemplate->language_id = $languageId;
            $newTemplate->sender_receipt = $_POST['sender_receipt'] ?? 'info@gavefabrikken.dk';
            $newTemplate->subject_receipt = $_POST['subject_receipt'] ?? $baseTemplate->subject_receipt;

            // Build the customized template_receipt by inserting custom content
            $customizedTemplate = $this->buildCustomizedTemplate(
                $baseTemplate->template_receipt,
                $_POST['custom_text1'] ?? '',
                $_POST['custom_delivery_info'] ?? '',
                $_POST['custom_text2'] ?? '',
                $_POST['custom_receipt_pos1'] ?? ''
            );

            $newTemplate->template_receipt = $customizedTemplate;
            $newTemplate->template_receipt_model = $baseTemplate->template_receipt_model;
            $newTemplate->template_receipt_exists = $baseTemplate->template_receipt_exists;

            // Copy other template fields from base
            $newTemplate->sender_reminder_deadline = $baseTemplate->sender_reminder_deadline;
            $newTemplate->subject_reminder_deadline = $baseTemplate->subject_reminder_deadline;
            $newTemplate->template_reminder_deadline = $baseTemplate->template_reminder_deadline;
            $newTemplate->sesnder_reminder_pickup = $baseTemplate->sesnder_reminder_pickup;
            $newTemplate->subject_reminder_pickup = $baseTemplate->subject_reminder_pickup;
            $newTemplate->template_reminder_pickup = $baseTemplate->template_reminder_pickup;
            $newTemplate->sender_company_order = $baseTemplate->sender_company_order;
            $newTemplate->subject_company_order = $baseTemplate->subject_company_order;
            $newTemplate->template_company_order = $baseTemplate->template_company_order;
            $newTemplate->sender_order_confirmation = $baseTemplate->sender_order_confirmation;
            $newTemplate->subject_order_confirmation = $baseTemplate->subject_order_confirmation;
            $newTemplate->template_order_confirmation = $baseTemplate->template_order_confirmation;
            $newTemplate->subject_reminder_giftcertificate = $baseTemplate->subject_reminder_giftcertificate;
            $newTemplate->template_reminder_giftcertificate = $baseTemplate->template_reminder_giftcertificate;
            $newTemplate->template_reminder_giftcertificate_list = $baseTemplate->template_reminder_giftcertificate_list;
            $newTemplate->subject_overwritewarn = $baseTemplate->subject_overwritewarn ?? null;
            $newTemplate->template_overwritewarn = $baseTemplate->template_overwritewarn ?? null;

            // Set internal description
            $newTemplate->internal_description = "Custom template for shop {$shopId}, language {$languageId}";

            $newTemplate->save();

            response::success(json_encode([
                'success' => true,
                'message' => 'Custom mail template created successfully',
                'id' => $newTemplate->id,
                'data' => $newTemplate->attributes
            ]));

        } catch (Exception $e) {
            error_log("createCustomTemplate error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Update existing custom mail template
     */
    public function updateCustomTemplate()
    {
        try {
            $templateId = intval($_POST['id']);
            $template = MailTemplate::find($templateId);

            if (!$template) {
                // Try to find by shop_id and language_id if no ID provided
                $languageId = intval($_POST['language_id']);
                $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : 0;
                $template = MailTemplate::find_by_shop_id_and_language_id($shopId, $languageId);
            }

            if (!$template) {
                // Create new template if none exists
                return $this->createCustomTemplate();
            }

            return $this->updateExistingTemplate($template);

        } catch (Exception $e) {
            error_log("updateCustomTemplate error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Update an existing template record
     */
    private function updateExistingTemplate($template)
    {
        try {
            // Update subject if provided
            if (isset($_POST['subject_receipt'])) {
                $template->subject_receipt = $_POST['subject_receipt'];
            }

            // Update sender if provided
            if (isset($_POST['sender_receipt'])) {
                $template->sender_receipt = $_POST['sender_receipt'];
            }

            // Get base template to maintain structure
            $baseTemplate = MailTemplate::getTemplate(0, $template->language_id);
            if (!$baseTemplate) {
                throw new Exception("No base template found for language ID: " . $template->language_id);
            }

            // Build the customized template_receipt
            $customizedTemplate = $this->buildCustomizedTemplate(
                $baseTemplate->template_receipt,
                $_POST['custom_text1'] ?? '',
                $_POST['custom_delivery_info'] ?? '',
                $_POST['custom_text2'] ?? '',
                $_POST['custom_receipt_pos1'] ?? ''
            );

            $template->template_receipt = $customizedTemplate;
            $template->save();

            response::success(json_encode([
                'success' => true,
                'message' => 'Custom mail template updated successfully',
                'id' => $template->id,
                'data' => $template->attributes
            ]));

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Build customized template by replacing placeholders with custom content
     */
    private function buildCustomizedTemplate($baseTemplate, $text1, $deliveryInfo, $text2, $receiptPos1)
    {
        // Start with the base template
        $customTemplate = $baseTemplate;

        // Replace or inject custom content into placeholders

        // Replace {text1} with custom content or remove if empty
        if (!empty($text1)) {
            $customTemplate = str_replace('{text1}', $text1, $customTemplate);
        } else {
            // Remove the entire text1 section if no custom content
            $customTemplate = preg_replace('/<tr>\s*<td[^>]*>\s*{text1}[^<]*<\/td>\s*<\/tr>/i', '', $customTemplate);
            $customTemplate = str_replace('{text1}', '', $customTemplate);
        }

        // Replace {DELIVERY_INFO} with custom content or leave placeholder
        if (!empty($deliveryInfo)) {
            $deliveryHtml = '<tr><td colspan="2">' . $deliveryInfo . '</td></tr>';
            $customTemplate = str_replace('{DELIVERY_INFO}', $deliveryHtml, $customTemplate);
        }
        // Note: Keep {DELIVERY_INFO} placeholder if empty - it will be replaced by preview or mail system

        // Replace {text2} with custom content or remove if empty
        if (!empty($text2)) {
            $customTemplate = str_replace('{text2}', $text2, $customTemplate);
        } else {
            // Remove the entire text2 section if no custom content
            $customTemplate = preg_replace('/<tr>\s*<td[^>]*>\s*{text2}[^<]*<\/td>\s*<\/tr>/i', '', $customTemplate);
            $customTemplate = str_replace('{text2}', '', $customTemplate);
        }

        // Replace {RECEIPT_POS1} with custom content as table row or remove if empty
        if (!empty($receiptPos1)) {
            $receiptHtml = '<tr><td colspan="2" style="padding:10px 0;">' . $receiptPos1 . '</td></tr>';
            $customTemplate = str_replace('{RECEIPT_POS1}', $receiptHtml, $customTemplate);
        } else {
            $customTemplate = str_replace('{RECEIPT_POS1}', '', $customTemplate);
        }

        return $customTemplate;
    }

    /**
     * Get all custom templates with optional filtering (excluding deleted templates with negative shop_id)
     */
    public function getAllCustomTemplates()
    {
        try {
            $conditions = array('shop_id >= 0'); // Exclude deleted templates

            if (isset($_POST['shop_id']) && $_POST['shop_id'] !== '') {
                $shopId = intval($_POST['shop_id']);
                if ($shopId >= 0) { // Only allow non-negative shop_id in search
                    $conditions[0] .= ' AND shop_id = ?';
                    $conditions[] = $shopId;
                }
            }

            if (isset($_POST['language_id']) && $_POST['language_id'] !== '') {
                $conditions[0] .= ' AND language_id = ?';
                $conditions[] = intval($_POST['language_id']);
            }

            $templates = MailTemplate::all(array(
                'conditions' => $conditions,
                'order' => 'shop_id, language_id'
            ));

            $result = array();
            foreach ($templates as $template) {
                $result[] = $template->attributes;
            }

            response::success(json_encode($result));

        } catch (Exception $e) {
            error_log("getAllCustomTemplates error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Delete a custom template by setting shop_id to negative value
     */
    public function deleteCustomTemplate()
    {
        try {
            $templateId = intval($_POST['id']);
            $template = MailTemplate::find($templateId);

            if (!$template) {
                response::error("Template not found");
                return;
            }

            // Don't allow deletion of base templates (shop_id = 0)
            if ($template->shop_id == 0) {
                response::error("Cannot delete base template");
                return;
            }

            // Set shop_id to negative to mark as deleted (soft delete)
            $template->shop_id = -abs($template->shop_id);
            $template->save();

            response::success(json_encode([
                'success' => true,
                'message' => 'Custom template deleted successfully (shop_id set to negative)'
            ]));

        } catch (Exception $e) {
            error_log("deleteCustomTemplate error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Get template for actual receipt generation (with fallback logic, excluding deleted templates)
     */
    public function getTemplateForReceipt()
    {
        try {
            $languageId = isset($_POST['language_id']) ? intval($_POST['language_id']) : 1;
            $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : 0;

            $template = $this->getCustomTemplateExcludingDeleted($shopId, $languageId);

            if ($template) {
                response::success(json_encode([
                    'subject' => $template->subject_receipt,
                    'sender' => $template->sender_receipt,
                    'template' => $template->template_receipt,
                    'template_model' => $template->template_receipt_model,
                    'template_exists' => $template->template_receipt_exists
                ]));
            } else {
                response::error("No template found for the specified language and shop");
            }

        } catch (Exception $e) {
            error_log("getTemplateForReceipt error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Extract custom content from existing template for editing
     */
    public function extractCustomContent()
    {
        try {
            $templateId = intval($_POST['id']);
            $template = MailTemplate::find($templateId);

            if (!$template) {
                response::error("Template not found");
                return;
            }

            // Get base template to compare against
            $baseTemplate = MailTemplate::getTemplate(0, $template->language_id);

            $extractedContent = array(
                'subject' => $template->subject_receipt,
                'text1' => $this->extractPlaceholderContent($template->template_receipt, $baseTemplate->template_receipt, 'text1'),
                'delivery_info' => $this->extractPlaceholderContent($template->template_receipt, $baseTemplate->template_receipt, 'DELIVERY_INFO'),
                'text2' => $this->extractPlaceholderContent($template->template_receipt, $baseTemplate->template_receipt, 'text2'),
                'receipt_pos1' => $this->extractPlaceholderContent($template->template_receipt, $baseTemplate->template_receipt, 'RECEIPT_POS1')
            );

            response::success(json_encode($extractedContent));

        } catch (Exception $e) {
            error_log("extractCustomContent error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Get template excluding deleted ones (negative shop_id)
     */
    private function getTemplateExcludingDeleted($shopId, $languageId)
    {
        // Use the existing getTemplate method from MailTemplate class
        $template = MailTemplate::getTemplate($shopId, $languageId);

        // Exclude deleted templates (negative shop_id)
        return ($template && $template->shop_id >= 0) ? $template : null;
    }

    /**
     * Save custom content to mailportal system
     */
    private function saveToMailportal($shopId, $languageId, $content)
    {
        try {
            // Check if mailportal tables exist
            if (!class_exists('MailportalTemplates')) {
                error_log("MailportalTemplates class not found - skipping mailportal save");
                return; // Skip mailportal save if tables don't exist
            }

            // Convert language_id to language_code
            $languageCode = $this->getLanguageCode($languageId);

            // Find or create template in mailportal_templates (no language_id field)
            $template = MailportalTemplates::first(array(
                'conditions' => array('shop_id = ? AND template_type = ?',
                    $shopId, 'custom')
            ));

            if (!$template) {
                // Create new template record
                $template = new MailportalTemplates();
                $template->shop_id = $shopId;
                $template->company_id = 0; // Default company
                $template->name = "Custom Receipt Template - Shop {$shopId}";
                $template->subject = $content['subject'] ?: 'Kvittering for gavevalg';
                $template->template_type = 'custom';
                $template->template_source = 'shop';
                $template->is_customized = 1;
                $template->is_active = 1;
                $template->created_date = date('Y-m-d H:i:s');
                $template->updated_date = date('Y-m-d H:i:s');
                $template->save();

                // Log creation
                $this->logMailportalAction('create_template', $template->id, $shopId, $languageId);
            } else {
                // Update existing template
                $template->subject = $content['subject'] ?: $template->subject;
                $template->updated_date = date('Y-m-d H:i:s');
                $template->save();

                // Log update
                $this->logMailportalAction('update_template', $template->id, $shopId, $languageId);
            }

            // Save content to mailportal_template_content with language_code
            $contentRecord = MailportalTemplateContent::first(array(
                'conditions' => array('template_id = ? AND language_code = ?',
                    $template->id, $languageCode)
            ));

            if (!$contentRecord) {
                // Create new content record
                $contentRecord = new MailportalTemplateContent();
                $contentRecord->template_id = $template->id;
                $contentRecord->language_code = $languageCode;
                $contentRecord->created_date = date('Y-m-d H:i:s');

                $this->logMailportalAction('create_content', $template->id, $shopId, $languageId, $languageCode);
            } else {
                $this->logMailportalAction('update_content', $template->id, $shopId, $languageId, $languageCode);
            }

            // Build content HTML from custom content
            $contentHtml = $this->buildMailportalContent($content);

            $contentRecord->subject_localized = $content['subject'];
            $contentRecord->content_html = $contentHtml;
            $contentRecord->content_text = strip_tags($contentHtml);
            $contentRecord->placeholders = json_encode($content);
            $contentRecord->updated_date = date('Y-m-d H:i:s');
            $contentRecord->save();

        } catch (Exception $e) {
            error_log("Failed to save to mailportal: " . $e->getMessage());
            $this->logMailportalAction('error', null, $shopId, $languageId, null, $e->getMessage());
            // Don't throw - let it fail gracefully
        }
    }

    /**
     * Sync custom content to mail_template for mail program compatibility
     */
    private function syncToMailTemplate($shopId, $languageId, $customContent)
    {
        // Get base template using the existing method
        $baseTemplate = MailTemplate::getTemplate(0, $languageId);

        if (!$baseTemplate) {
            throw new Exception("No base template found");
        }

        // Only create shop-specific template if shopId > 0 and there's custom content
        if ($shopId > 0 && $this->hasCustomContent($customContent)) {
            // Check if shop-specific template exists
            $shopTemplate = MailTemplate::getTemplate($shopId, $languageId);

            // Check if it's actually shop-specific (not fallback to base)
            if ($shopTemplate && $shopTemplate->shop_id != $shopId) {
                $shopTemplate = null; // It was a fallback, not actual shop template
            }

            // Build customized template by inserting custom content into placeholders
            $customizedTemplate = $this->buildCustomTemplate($baseTemplate->template_receipt, $customContent);

            if ($shopTemplate && $shopTemplate->shop_id == $shopId) {
                // Update existing shop template
                $shopTemplate->subject_receipt = !empty($customContent['subject']) ? $customContent['subject'] : $baseTemplate->subject_receipt;
                $shopTemplate->template_receipt = $customizedTemplate;
                $shopTemplate->save();
            } else {
                // Create new shop-specific template
                $newTemplate = new MailTemplate();
                $newTemplate->shop_id = $shopId;
                $newTemplate->language_id = $languageId;
                $newTemplate->sender_receipt = $baseTemplate->sender_receipt;
                $newTemplate->subject_receipt = !empty($customContent['subject']) ? $customContent['subject'] : $baseTemplate->subject_receipt;
                $newTemplate->template_receipt = $customizedTemplate;
                $newTemplate->template_receipt_model = $baseTemplate->template_receipt_model;
                $newTemplate->template_receipt_exists = $baseTemplate->template_receipt_exists;
                $newTemplate->internal_description = "Custom template for shop {$shopId}, language {$languageId}";

                // Copy all other fields from base template
                $this->copyOtherTemplateFields($newTemplate, $baseTemplate);

                $newTemplate->save();
            }
        }
        // Note: Skip soft delete logic for now since we can't reliably query shop-specific templates
    }

    /**
     * Check if there's any custom content
     */
    private function hasCustomContent($content)
    {
        foreach ($content as $value) {
            if (!empty(trim($value))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build custom template by replacing placeholders
     */
    private function buildCustomTemplate($baseTemplate, $content)
    {
        $template = $baseTemplate;

        // Replace {text1} placeholder
        if (!empty($content['text1'])) {
            $template = str_replace('{text1}', $content['text1'], $template);
        }

        // Replace {DELIVERY_INFO} placeholder - use exact content without labels
        if (!empty($content['delivery_info'])) {
            $deliveryHtml = '<tr><td colspan="2">' . $content['delivery_info'] . '</td></tr>';
            $template = str_replace('{DELIVERY_INFO}', $deliveryHtml, $template);
        }

        // Replace {text2} placeholder
        if (!empty($content['text2'])) {
            $template = str_replace('{text2}', $content['text2'], $template);
        }

        // Replace {RECEIPT_POS1} placeholder as table row
        if (!empty($content['receipt_pos1'])) {
            $receiptHtml = '<tr><td colspan="2" style="padding:10px 0;">' . $content['receipt_pos1'] . '</td></tr>';
            $template = str_replace('{RECEIPT_POS1}', $receiptHtml, $template);
        }

        return $template;
    }

    /**
     * Copy other template fields from base to new template
     */
    private function copyOtherTemplateFields($newTemplate, $baseTemplate)
    {
        $newTemplate->sender_reminder_deadline = $baseTemplate->sender_reminder_deadline;
        $newTemplate->subject_reminder_deadline = $baseTemplate->subject_reminder_deadline;
        $newTemplate->template_reminder_deadline = $baseTemplate->template_reminder_deadline;
        $newTemplate->sesnder_reminder_pickup = $baseTemplate->sesnder_reminder_pickup;
        $newTemplate->subject_reminder_pickup = $baseTemplate->subject_reminder_pickup;
        $newTemplate->template_reminder_pickup = $baseTemplate->template_reminder_pickup;
        $newTemplate->sender_company_order = $baseTemplate->sender_company_order;
        $newTemplate->subject_company_order = $baseTemplate->subject_company_order;
        $newTemplate->template_company_order = $baseTemplate->template_company_order;
        $newTemplate->sender_order_confirmation = $baseTemplate->sender_order_confirmation;
        $newTemplate->subject_order_confirmation = $baseTemplate->subject_order_confirmation;
        $newTemplate->template_order_confirmation = $baseTemplate->template_order_confirmation;
        $newTemplate->subject_reminder_giftcertificate = $baseTemplate->subject_reminder_giftcertificate;
        $newTemplate->template_reminder_giftcertificate = $baseTemplate->template_reminder_giftcertificate;
        $newTemplate->template_reminder_giftcertificate_list = $baseTemplate->template_reminder_giftcertificate_list;
        $newTemplate->subject_overwritewarn = $baseTemplate->subject_overwritewarn ?? null;
        $newTemplate->template_overwritewarn = $baseTemplate->template_overwritewarn ?? null;
    }

    /**
     * Extract custom content by comparing customized template with base template
     */
    private function extractPlaceholderContent($customTemplate, $baseTemplate, $placeholder)
    {
        // This is a simplified extraction - in practice, you might need more sophisticated logic
        // to extract the exact custom content that was inserted

        // For now, return empty string - the JavaScript will need to build this content progressively
        return '';
    }

    /**
     * Convert language_id to language_code
     */
    private function getLanguageCode($languageId)
    {
        $mapping = array(
            1 => 'da', // Danish
            2 => 'en', // English
            3 => 'de', // German
            4 => 'no', // Norwegian
            5 => 'sv'  // Swedish
        );
        return $mapping[$languageId] ?? 'da';
    }

    /**
     * Build HTML content for mailportal storage
     */
    private function buildMailportalContent($content)
    {
        $html = '';
        if (!empty($content['text1'])) {
            $html .= '<div class="custom-text1">' . $content['text1'] . '</div>';
        }
        if (!empty($content['delivery_info'])) {
            $html .= '<div class="delivery-info">' . $content['delivery_info'] . '</div>';
        }
        if (!empty($content['text2'])) {
            $html .= '<div class="custom-text2">' . $content['text2'] . '</div>';
        }
        if (!empty($content['receipt_pos1'])) {
            $html .= '<div class="receipt-pos1">' . $content['receipt_pos1'] . '</div>';
        }
        return $html;
    }

    /**
     * Log mailportal actions
     */
    private function logMailportalAction($action, $templateId, $shopId, $languageId, $contentKey = null, $details = null)
    {
        try {
            // Skip logging if mailportal tables don't exist
            if (!class_exists('MailportalLogs')) {
                return;
            }

            $log = new MailportalLogs();
            $log->shop_id = $shopId;
            $log->company_id = 0;
            $log->level = 'info';
            $log->category = 'template_management';
            $log->message = "Template action: {$action}";
            $log->details = json_encode(array(
                'action' => $action,
                'template_id' => $templateId,
                'language_id' => $languageId,
                'content_key' => $contentKey,
                'details' => $details
            ));
            $log->created_date = date('Y-m-d H:i:s');
            $log->save();
        } catch (Exception $e) {
            error_log("Failed to log mailportal action (tables may not exist): " . $e->getMessage());
        }
    }

    /**
     * Get custom content from mailportal_templates and mailportal_template_content
     */
    private function getMailportalContent($shopId, $languageId)
    {
        $content = array(
            'subject' => '',
            'text1' => '',
            'delivery_info' => '',
            'text2' => '',
            'receipt_pos1' => ''
        );

        try {
            // Check if mailportal tables exist
            if (!class_exists('MailportalTemplates')) {
                error_log("MailportalTemplates class not found - tables may not exist yet");
                return $content;
            }

            // Convert language_id to language_code
            $languageCode = $this->getLanguageCode($languageId);

            // Find template in mailportal_templates (no language field)
            $template = MailportalTemplates::first(array(
                'conditions' => array('shop_id = ? AND template_type = ?',
                    $shopId, 'custom')
            ));

            if (!$template && $shopId > 0) {
                // Fallback to global template
                $template = MailportalTemplates::first(array(
                    'conditions' => array('shop_id = ? AND template_type = ?',
                        0, 'custom')
                ));
            }

            if ($template) {
                // Get content from mailportal_template_content using language_code
                $contentRecord = MailportalTemplateContent::first(array(
                    'conditions' => array('template_id = ? AND language_code = ?',
                        $template->id, $languageCode)
                ));

                if ($contentRecord) {
                    // Extract content from JSON placeholders
                    if (!empty($contentRecord->placeholders)) {
                        $placeholders = json_decode($contentRecord->placeholders, true);
                        if ($placeholders) {
                            $content['subject'] = $placeholders['subject'] ?? '';
                            $content['text1'] = $placeholders['text1'] ?? '';
                            $content['delivery_info'] = $placeholders['delivery_info'] ?? '';
                            $content['text2'] = $placeholders['text2'] ?? '';
                            $content['receipt_pos1'] = $placeholders['receipt_pos1'] ?? '';
                        }
                    }

                    // Use subject_localized if available
                    if (!empty($contentRecord->subject_localized)) {
                        $content['subject'] = $contentRecord->subject_localized;
                    }
                }
            }

        } catch (Exception $e) {
            error_log("Failed to get mailportal content (tables may not exist): " . $e->getMessage());
            // Return empty content - fallback gracefully
        }

        return $content;
    }

    /**
     * Preview the customized template
     */
    public function previewTemplate()
    {
        try {
            $languageId = intval($_POST['language_id']);
            $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : 0;

            // Build preview template with custom content
            $baseTemplate = MailTemplate::getTemplate(0, $languageId);
            if (!$baseTemplate) {
                throw new Exception("No base template found");
            }

            $previewTemplate = $this->buildCustomizedTemplate(
                $baseTemplate->template_receipt,
                $_POST['custom_text1'] ?? '',
                $_POST['custom_delivery_info'] ?? '',
                $_POST['custom_text2'] ?? '',
                $_POST['custom_receipt_pos1'] ?? ''
            );

            // Replace common placeholders with sample data for preview
            $previewTemplate = str_replace('{DATE}', date('d-m-Y H:i:s'), $previewTemplate);
            $previewTemplate = str_replace('{ORDERNO}', '10001234', $previewTemplate);
            $previewTemplate = str_replace('{MODEL_NAME}', 'Eksempel Gave - Preview', $previewTemplate);
            $previewTemplate = str_replace('{MODEL_IMAGE}', '<div style="width:80px;height:60px;background:#f0f0f0;border:1px dashed #ccc;display:flex;align-items:center;justify-content:center;">📦</div>', $previewTemplate);
            // Replace DELIVERY_INFO placeholder - only if still exists (empty field)
            if (strpos($previewTemplate, '{DELIVERY_INFO}') !== false) {
                // If placeholder still exists, just remove it (no sample data)
                $previewTemplate = str_replace('{DELIVERY_INFO}', '', $previewTemplate);
            }
            $previewTemplate = str_replace('{USER_DETAILS}',
                '<tr><td align="left">Navn</td><td align="right">Eksempel Kunde</td></tr>
                 <tr><td align="left">Email</td><td align="right">eksempel@email.dk</td></tr>
                 <tr><td align="left">Telefon</td><td align="right">12345678</td></tr>'
                , $previewTemplate);
            $previewTemplate = str_replace('{EXTRA}', '', $previewTemplate);
            $previewTemplate = str_replace('{qr}', '<tr><td colspan="2" style="text-align:center;padding:20px;"><div style="border:1px dashed #ccc;padding:20px;background:#f9f9f9;">📱 QR Code</div></td></tr>', $previewTemplate);

            // Replace RECEIPT_POS1 with empty or actual content (no test data)
            if (empty($_POST['custom_receipt_pos1'])) {
                $previewTemplate = str_replace('{RECEIPT_POS1}', '', $previewTemplate);
            }

            response::success(json_encode([
                'subject' => $_POST['subject_receipt'] ?? $baseTemplate->subject_receipt,
                'template' => $previewTemplate
            ]));

        } catch (Exception $e) {
            error_log("previewTemplate error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }
}
?>