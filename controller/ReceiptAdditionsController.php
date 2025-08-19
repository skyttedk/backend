<?php

Class ReceiptAdditionsController Extends baseController
{
    public function index()
    {
        echo "Receipt Additions Controller Active";
    }

    /**
     * Create a new receipt addition
     */
    public function create()
    {
        try {
            $receiptAddition = new ReceiptAdditions();
            $receiptAddition->company_id = isset($_POST['company_id']) && $_POST['company_id'] !== '' ? $_POST['company_id'] : null;
            $receiptAddition->shop_id = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? $_POST['shop_id'] : null;
            $receiptAddition->language = $_POST['language'];
            $receiptAddition->top_text = isset($_POST['top_text']) ? $_POST['top_text'] : '';
            $receiptAddition->standard_text = isset($_POST['standard_text']) ? $_POST['standard_text'] : '';
            $receiptAddition->delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : '';
            $receiptAddition->bottom_text = isset($_POST['bottom_text']) ? $_POST['bottom_text'] : '';
            $receiptAddition->active = isset($_POST['active']) ? $_POST['active'] : 1;

            $receiptAddition->save();

            response::success(json_encode([
                'success' => true,
                'message' => 'Receipt addition created successfully',
                'id' => $receiptAddition->id,
                'data' => $receiptAddition->attributes
            ]));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Read a specific receipt addition
     */
    public function read()
    {
        try {
            $receiptAddition = ReceiptAdditions::find($_POST['id']);
            if (!$receiptAddition) {
                response::error("Receipt addition not found");
                return;
            }
            response::success(json_encode($receiptAddition->attributes));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Update an existing receipt addition
     */
    public function update()
    {
        try {
            $receiptAddition = ReceiptAdditions::find($_POST['id']);
            if (!$receiptAddition) {
                response::error("Receipt addition not found");
                return;
            }

            if (isset($_POST['company_id'])) {
                $receiptAddition->company_id = $_POST['company_id'] !== '' ? $_POST['company_id'] : null;
            }
            if (isset($_POST['shop_id'])) {
                $receiptAddition->shop_id = $_POST['shop_id'] !== '' ? $_POST['shop_id'] : null;
            }
            if (isset($_POST['language'])) $receiptAddition->language = $_POST['language'];
            if (isset($_POST['top_text'])) $receiptAddition->top_text = $_POST['top_text'];
            if (isset($_POST['standard_text'])) $receiptAddition->standard_text = $_POST['standard_text'];
            if (isset($_POST['delivery_date'])) $receiptAddition->delivery_date = $_POST['delivery_date'];
            if (isset($_POST['bottom_text'])) $receiptAddition->bottom_text = $_POST['bottom_text'];
            if (isset($_POST['active'])) $receiptAddition->active = $_POST['active'];

            $receiptAddition->save();

            response::success(json_encode([
                'success' => true,
                'message' => 'Receipt addition updated successfully',
                'data' => $receiptAddition->attributes
            ]));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Delete a receipt addition
     */
    public function delete()
    {
        try {
            $receiptAddition = ReceiptAdditions::find($_POST['id']);
            if (!$receiptAddition) {
                response::error("Receipt addition not found");
                return;
            }

            $receiptAddition->delete();
            response::success(json_encode(['message' => 'Receipt addition deleted successfully']));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive()
    {
        try {
            $receiptAddition = ReceiptAdditions::find($_POST['id']);
            if (!$receiptAddition) {
                response::error("Receipt addition not found");
                return;
            }

            $receiptAddition->active = $receiptAddition->active ? 0 : 1;
            $receiptAddition->save();

            response::success(json_encode([
                'success' => true,
                'message' => 'Status updated successfully',
                'active' => $receiptAddition->active,
                'data' => $receiptAddition->attributes
            ]));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Get receipt additions by language (for DeliveryModule.js)
     * Fixed for PHP ActiveRecord
     */
    /**
     * Get receipt additions by language (for DeliveryModule.js)
     * ÆNDRET: Opretter IKKE automatisk nye records
     */
    public function getByLanguage()
    {
        try {
            $language = intval($_POST['language']);
            $companyId = isset($_POST['company_id']) && $_POST['company_id'] !== '' ? intval($_POST['company_id']) : null;
            $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : null;

            // Build conditions array for PHP ActiveRecord
            $conditions = array('language = ? AND active = ?', $language, 1);

            // Add company condition
            if ($companyId !== null) {
                $conditions[0] .= ' AND company_id = ?';
                $conditions[] = $companyId;
            } else {
                $conditions[0] .= ' AND company_id IS NULL';
            }

            // Add shop condition
            if ($shopId !== null) {
                $conditions[0] .= ' AND shop_id = ?';
                $conditions[] = $shopId;
            } else {
                $conditions[0] .= ' AND shop_id IS NULL';
            }

            $receiptAdditions = ReceiptAdditions::all(array(
                'conditions' => $conditions,
                'order' => 'updated_at DESC',
                'limit' => 1
            ));

            // ÆNDRET: Opret IKKE automatisk nye records
            // Returner tomt array hvis ingen records eksisterer
            $result = array();
            foreach ($receiptAdditions as $receipt) {
                $result[] = $receipt->attributes;
            }

            // Returner altid success, selvom array er tomt
            response::success(json_encode($result));

        } catch (Exception $e) {
            error_log("getByLanguage error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }
    /**
     * Create default record if none exists
     */
    private function createDefaultRecord($companyId, $shopId, $language)
    {
        // Standard tekster baseret på sprog
        $defaultTexts = $this->getDefaultTexts($language);

        $receiptAddition = new ReceiptAdditions();
        $receiptAddition->company_id = $companyId;
        $receiptAddition->shop_id = $shopId;
        $receiptAddition->language = $language;
        $receiptAddition->top_text = $defaultTexts['top_text'];
        $receiptAddition->standard_text = $defaultTexts['standard_text'];
        $receiptAddition->delivery_date = $defaultTexts['delivery_date'];
        $receiptAddition->bottom_text = $defaultTexts['bottom_text'];
        $receiptAddition->active = 1;

        $receiptAddition->save();
        return $receiptAddition;
    }

    /**
     * Get default texts based on language
     */
    private function getDefaultTexts($language)
    {
        $defaults = array(
            1 => array( // Dansk
                'top_text' => '<p><strong>Velkommen til vores gaveshop!</strong></p>',
                'standard_text' => '<p>Tak for dit køb. Vi håber du bliver glad for din gave.</p>',
                'delivery_date' => 'Leveres inden 3-5 hverdage',
                'bottom_text' => '<p><em>Med venlig hilsen<br>Gaveshop teamet</em></p>'
            ),
            2 => array( // Engelsk
                'top_text' => '<p><strong>Welcome to our gift shop!</strong></p>',
                'standard_text' => '<p>Thank you for your purchase. We hope you enjoy your gift.</p>',
                'delivery_date' => 'Delivered within 3-5 business days',
                'bottom_text' => '<p><em>Best regards<br>Gift shop team</em></p>'
            ),
            3 => array( // Tysk
                'top_text' => '<p><strong>Willkommen in unserem Geschenkshop!</strong></p>',
                'standard_text' => '<p>Vielen Dank für Ihren Kauf. Wir hoffen, Sie freuen sich über Ihr Geschenk.</p>',
                'delivery_date' => 'Lieferung innerhalb von 3-5 Werktagen',
                'bottom_text' => '<p><em>Mit freundlichen Grüßen<br>Ihr Geschenkshop-Team</em></p>'
            ),
            4 => array( // Norsk
                'top_text' => '<p><strong>Velkommen til vår gavebutikk!</strong></p>',
                'standard_text' => '<p>Takk for ditt kjøp. Vi håper du blir glad for gaven din.</p>',
                'delivery_date' => 'Leveres innen 3-5 virkedager',
                'bottom_text' => '<p><em>Med vennlig hilsen<br>Gavebutikk-teamet</em></p>'
            ),
            5 => array( // Svensk
                'top_text' => '<p><strong>Välkommen till vår gåvobutik!</strong></p>',
                'standard_text' => '<p>Tack för ditt köp. Vi hoppas du blir glad för din gåva.</p>',
                'delivery_date' => 'Levereras inom 3-5 arbetsdagar',
                'bottom_text' => '<p><em>Med vänliga hälsningar<br>Gåvobutiksteamet</em></p>'
            )
        );

        return isset($defaults[$language]) ? $defaults[$language] : $defaults[1]; // Fallback til dansk
    }

    /**
     * Update by language - creates if not exists (for DeliveryModule.js)
     * Fixed for PHP ActiveRecord
     */
    public function updateByLanguage()
    {
        try {
            $language = intval($_POST['language']);
            $companyId = isset($_POST['company_id']) && $_POST['company_id'] !== '' ? intval($_POST['company_id']) : null;
            $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : null;

            // Build conditions array
            $conditions = array('language = ?', $language);

            if ($companyId !== null) {
                $conditions[0] .= ' AND company_id = ?';
                $conditions[] = $companyId;
            } else {
                $conditions[0] .= ' AND company_id IS NULL';
            }

            if ($shopId !== null) {
                $conditions[0] .= ' AND shop_id = ?';
                $conditions[] = $shopId;
            } else {
                $conditions[0] .= ' AND shop_id IS NULL';
            }

            $receiptAddition = ReceiptAdditions::first(array(
                'conditions' => $conditions
            ));

            if ($receiptAddition) {
                // Update existing record
                if (isset($_POST['top_text'])) $receiptAddition->top_text = $_POST['top_text'];
                if (isset($_POST['standard_text'])) $receiptAddition->standard_text = $_POST['standard_text'];
                if (isset($_POST['delivery_date'])) $receiptAddition->delivery_date = $_POST['delivery_date'];
                if (isset($_POST['bottom_text'])) $receiptAddition->bottom_text = $_POST['bottom_text'];
                if (isset($_POST['active'])) $receiptAddition->active = $_POST['active'];

                $receiptAddition->save();

                response::success(json_encode(array(
                    'success' => true,
                    'action' => 'updated',
                    'id' => $receiptAddition->id,
                    'data' => $receiptAddition->attributes
                )));
            } else {
                // Create new record
                $receiptAddition = new ReceiptAdditions();
                $receiptAddition->company_id = $companyId;
                $receiptAddition->shop_id = $shopId;
                $receiptAddition->language = $language;
                $receiptAddition->top_text = isset($_POST['top_text']) ? $_POST['top_text'] : '';
                $receiptAddition->standard_text = isset($_POST['standard_text']) ? $_POST['standard_text'] : '';
                $receiptAddition->delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : '';
                $receiptAddition->bottom_text = isset($_POST['bottom_text']) ? $_POST['bottom_text'] : '';
                $receiptAddition->active = isset($_POST['active']) ? $_POST['active'] : 1;

                $receiptAddition->save();

                response::success(json_encode(array(
                    'success' => true,
                    'action' => 'created',
                    'id' => $receiptAddition->id,
                    'data' => $receiptAddition->attributes
                )));
            }
        } catch (Exception $e) {
            error_log("updateByLanguage error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Get active receipt content for receipt generation
     * Kun aktive records bruges til kvitteringer
     */
    public function getActiveReceiptContent()
    {
        try {
            $language = isset($_POST['language']) ? intval($_POST['language']) : 1;
            $companyId = isset($_POST['company_id']) && $_POST['company_id'] !== '' ? intval($_POST['company_id']) : null;
            $shopId = isset($_POST['shop_id']) && $_POST['shop_id'] !== '' ? intval($_POST['shop_id']) : null;

            $receiptAddition = null;

            // Try most specific first (company + shop)
            if ($companyId !== null && $shopId !== null) {
                $receiptAddition = ReceiptAdditions::first(array(
                    'conditions' => array('language = ? AND active = ? AND company_id = ? AND shop_id = ?',
                        $language, 1, $companyId, $shopId),
                    'order' => 'updated_at DESC'
                ));
            }

            // Fallback to company only
            if (!$receiptAddition && $companyId !== null) {
                $receiptAddition = ReceiptAdditions::first(array(
                    'conditions' => array('language = ? AND active = ? AND company_id = ? AND shop_id IS NULL',
                        $language, 1, $companyId),
                    'order' => 'updated_at DESC'
                ));
            }

            // Final fallback to global (no company, no shop)
            if (!$receiptAddition) {
                $receiptAddition = ReceiptAdditions::first(array(
                    'conditions' => array('language = ? AND active = ? AND company_id IS NULL AND shop_id IS NULL',
                        $language, 1),
                    'order' => 'updated_at DESC'
                ));
            }

            if ($receiptAddition) {
                response::success(json_encode($receiptAddition->attributes));
            } else {
                // Create default if nothing exists
                $defaultRecord = $this->createDefaultRecord(null, null, $language);
                response::success(json_encode($defaultRecord->attributes));
            }

        } catch (Exception $e) {
            error_log("getActiveReceiptContent error: " . $e->getMessage());
            response::error($e->getMessage());
        }
    }

    /**
     * Get all receipt additions with optional filtering
     */
    public function readAll()
    {
        try {
            $conditions = array('1=1');

            if (isset($_POST['company_id']) && $_POST['company_id'] !== '') {
                $conditions[0] .= ' AND company_id = ?';
                $conditions[] = intval($_POST['company_id']);
            }

            if (isset($_POST['shop_id']) && $_POST['shop_id'] !== '') {
                $conditions[0] .= ' AND shop_id = ?';
                $conditions[] = intval($_POST['shop_id']);
            }

            if (isset($_POST['language']) && $_POST['language'] !== '') {
                $conditions[0] .= ' AND language = ?';
                $conditions[] = intval($_POST['language']);
            }

            if (isset($_POST['active']) && $_POST['active'] !== '') {
                $conditions[0] .= ' AND active = ?';
                $conditions[] = intval($_POST['active']);
            }

            $receiptAdditions = ReceiptAdditions::all(array(
                'conditions' => $conditions,
                'order' => 'created_at DESC'
            ));

            // Convert to array format
            $result = array();
            foreach ($receiptAdditions as $receipt) {
                $result[] = $receipt->attributes;
            }

            response::success(json_encode($result));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Get standard texts for receipts (legacy support for original JavaScript)
     */
    public function getStandardText()
    {
        try {
            $conditions = array('active = ?', 1);

            if (isset($_POST['company_id']) && $_POST['company_id'] !== '') {
                $conditions[0] .= ' AND company_id = ?';
                $conditions[] = intval($_POST['company_id']);
            }

            if (isset($_POST['shop_id']) && $_POST['shop_id'] !== '') {
                $conditions[0] .= ' AND shop_id = ?';
                $conditions[] = intval($_POST['shop_id']);
            }

            if (isset($_POST['language']) && $_POST['language'] !== '') {
                $conditions[0] .= ' AND language = ?';
                $conditions[] = intval($_POST['language']);
            }

            $receiptAdditions = ReceiptAdditions::all(array(
                'conditions' => $conditions,
                'order' => 'created_at DESC'
            ));

            // Format for JavaScript consumption
            $data = array();
            foreach ($receiptAdditions as $addition) {
                $data[] = array(
                    'id' => $addition->id,
                    'title' => $this->generateTitle($addition),
                    'da' => $addition->standard_text,
                    'top_text' => $addition->top_text,
                    'delivery_date' => $addition->delivery_date,
                    'bottom_text' => $addition->bottom_text,
                    'company_id' => $addition->company_id,
                    'shop_id' => $addition->shop_id,
                    'language' => $addition->language,
                    'active' => $addition->active
                );
            }

            $response = array(
                'data' => array_map(function($item) {
                    return array('attributes' => $item);
                }, $data)
            );

            response::success(json_encode($response));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Update standard text selection for a present model (legacy support)
     */
    public function updateStandardText()
    {
       /*
        try {
            $textId = intval($_POST['msg1']);
            $modelId = intval($_POST['id']);

            $sql = "UPDATE present_model SET msg1 = " . $textId . " WHERE id = " . $modelId;
            Dbsqli::setSql2($sql);

            response::success(json_encode(array('message' => 'Standard text updated successfully')));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
       */
    }

    /**
     * Search receipt additions
     */
    public function search()
    {
        try {
            $search = $_POST['search'];
            $pieces = explode(" ", $search);
            $conditions = array('1=1');

            foreach ($pieces as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $conditions[0] .= ' AND (top_text LIKE ? OR standard_text LIKE ? OR bottom_text LIKE ? OR delivery_date LIKE ?)';
                    $likePattern = '%' . $part . '%';
                    $conditions[] = $likePattern;
                    $conditions[] = $likePattern;
                    $conditions[] = $likePattern;
                    $conditions[] = $likePattern;
                }
            }

            if (isset($_POST['company_id']) && $_POST['company_id'] !== '') {
                $conditions[0] .= ' AND company_id = ?';
                $conditions[] = intval($_POST['company_id']);
            }

            if (isset($_POST['shop_id']) && $_POST['shop_id'] !== '') {
                $conditions[0] .= ' AND shop_id = ?';
                $conditions[] = intval($_POST['shop_id']);
            }

            if (isset($_POST['language']) && $_POST['language'] !== '') {
                $conditions[0] .= ' AND language = ?';
                $conditions[] = intval($_POST['language']);
            }

            if (isset($_POST['active']) && $_POST['active'] !== '') {
                $conditions[0] .= ' AND active = ?';
                $conditions[] = intval($_POST['active']);
            }

            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;

            $receiptAdditions = ReceiptAdditions::all(array(
                'conditions' => $conditions,
                'order' => 'created_at DESC',
                'limit' => $limit
            ));

            // Convert to array format
            $result = array();
            foreach ($receiptAdditions as $receipt) {
                $result[] = $receipt->attributes;
            }

            response::success(json_encode($result));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Generate a title for the receipt addition
     */
    private function generateTitle($addition)
    {
        $title = "";

        if ($addition->company_id) {
            $title .= "Company " . $addition->company_id;
        }

        if ($addition->shop_id) {
            if ($title) $title .= " - ";
            $title .= "Shop " . $addition->shop_id;
        }

        if ($addition->delivery_date) {
            if ($title) $title .= " - ";
            $title .= $addition->delivery_date;
        }

        $status = $addition->active ? "Aktiv" : "Inaktiv";
        if ($title) $title .= " - ";
        $title .= $status;

        if (empty($title)) {
            $title = "Receipt Template #" . $addition->id;
        }

        return $title;
    }

    /**
     * Get available languages for a specific context
     */
    public function getAvailableLanguages()
    {
        try {
            $conditions = array('active = ?', 1);

            if (isset($_POST['company_id']) && $_POST['company_id'] !== '') {
                $conditions[0] .= ' AND company_id = ?';
                $conditions[] = intval($_POST['company_id']);
            }

            if (isset($_POST['shop_id']) && $_POST['shop_id'] !== '') {
                $conditions[0] .= ' AND shop_id = ?';
                $conditions[] = intval($_POST['shop_id']);
            }

            $receiptAdditions = ReceiptAdditions::all(array(
                'conditions' => $conditions,
                'select' => 'DISTINCT language',
                'order' => 'language'
            ));

            // Extract just the language values
            $languages = array();
            foreach ($receiptAdditions as $receipt) {
                $languages[] = array('language' => $receipt->language);
            }

            response::success(json_encode($languages));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }

    /**
     * Create sample data for testing
     */
    public function createSampleData()
    {
        try {
            $languages = array(1, 2, 3, 4, 5); // da, en, de, no, sv
            $created = array();

            foreach ($languages as $lang) {
                // Check if already exists
                $existing = ReceiptAdditions::first(array(
                    'conditions' => array('language = ? AND company_id IS NULL AND shop_id IS NULL', $lang)
                ));

                if (!$existing) {
                    $defaultTexts = $this->getDefaultTexts($lang);

                    $receiptAddition = new ReceiptAdditions();
                    $receiptAddition->company_id = null;
                    $receiptAddition->shop_id = null;
                    $receiptAddition->language = $lang;
                    $receiptAddition->top_text = $defaultTexts['top_text'];
                    $receiptAddition->standard_text = $defaultTexts['standard_text'];
                    $receiptAddition->delivery_date = $defaultTexts['delivery_date'];
                    $receiptAddition->bottom_text = $defaultTexts['bottom_text'];
                    $receiptAddition->active = 1;

                    $receiptAddition->save();
                    $created[] = $receiptAddition->attributes;
                } else {
                    $created[] = array('language' => $lang, 'status' => 'already_exists');
                }
            }

            response::success(json_encode(array(
                'message' => 'Sample data processed for all languages',
                'created' => $created
            )));
        } catch (Exception $e) {
            response::error($e->getMessage());
        }
    }
}
?>