<?php
// /gavefabrikken_backend/index.php?rt=myPage&login=dsfkjsadhferuifghriuejf3434fhsudif

// https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/finalbilling&shopID=9352
//  https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=myPage&login=dsfkjsadhferuifghriuejf3434fhsudif units/valgshop/finalbilling/view.php?shopID=9352


namespace GFUnit\valgshop\finalbilling;
use GFBiz\units\UnitController;

class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * Escape SQL values to prevent SQL injection
     */
    private function escapeSqlValue($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        
        // Remove or escape dangerous characters
        $value = str_replace("'", "''", $value); // Escape single quotes
        $value = str_replace("\\", "\\\\", $value); // Escape backslashes
        $value = str_replace("\0", "", $value); // Remove null bytes
        $value = str_replace("\n", "\\n", $value); // Escape newlines
        $value = str_replace("\r", "\\r", $value); // Escape carriage returns
        $value = str_replace("\x1a", "\\Z", $value); // Escape substitute character
        
        return "'{$value}'";
    }

    /**
     * Hent finalbilling data for en specifik shop
     */
    public function getShopsForFinalbilling()
    {
        if (!isset($_GET["shopID"]) || !(int)$_GET["shopID"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Ugyldig eller manglende shop-ID"
            ]);
            exit;
        }

        $shop_id = (int)$_GET["shopID"];

        try {
            $sql = "
                SELECT
                    s.id as shop_id,
                    s.name as shop_name,
                    s.end_date,
                    sm.delivery_date,
                    sm.user_count,
                    sm.present_count,
                    c.name as company_name
                FROM shop s
                INNER JOIN shop_metadata sm ON s.id = sm.shop_id
                INNER JOIN company_shop cs ON s.id = cs.shop_id
                INNER JOIN company c ON cs.company_id = c.id
                WHERE s.id = ?
                AND s.active = 1
            ";

            $shops = \Shop::find_by_sql($sql, array($shop_id));

            if (empty($shops)) {
                echo json_encode([
                    "status" => 0,
                    "error" => "Shop ikke fundet eller ikke aktiv"
                ]);
                return;
            }

            $shop = $shops[0];

            $addresses_sql = "
                SELECT
                    sa.id as address_id,
                    sa.name as address_name,
                    sa.address,
                    sa.zip,
                    sa.city,
                    sa.country,
                    si2.id as invoice2_id,
                    COALESCE(si2.approved, 0) as approved,
                    si2.approved_date
                FROM shop_address sa
                LEFT JOIN shop_invoice_2 si2 ON sa.shop_id = si2.shop_id AND sa.id = si2.invoice_index
                WHERE sa.shop_id = ?
                ORDER BY sa.index
            ";

            $addresses = \ShopAddress::find_by_sql($addresses_sql, array($shop_id));

            // Kan ikke tildele til readonly ActiveRecord objekt
            // I stedet lav en ny struktur
            $result = array(
                'shop_id' => $shop->shop_id,
                'shop_name' => $shop->shop_name,
                'end_date' => $shop->end_date,
                'delivery_date' => $shop->delivery_date,
                'user_count' => $shop->user_count,
                'present_count' => $shop->present_count,
                'company_name' => $shop->company_name,
                'addresses' => $addresses
            );

            echo json_encode([
                "status" => 1,
                "data" => $result
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved hentning af shop: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Hent finalbilling data for en specifik shop
     */
    public function getShopFinalbillingData()
    {
        $shop_id = intval($_POST["shop_id"]);

        try {
            // Implementer hentning af shop specifik data
            echo json_encode([
                "status" => 1,
                "data" => ["shop_id" => $shop_id]
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved hentning af finalbilling data: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Hent eksisterende shop_invoice2 data for en adresse, eller fallback til shop_metadata
     */
    public function getShopInvoiceData()
    {
        $shop_id = intval($_POST["shop_id"]);
        $address_id = intval($_POST["address_id"]);

        try {
            // Hent address for validering
            $address = \ShopAddress::find($address_id);
            if (!$address || $address->shop_id != $shop_id) {
                throw new \Exception("Ugyldig adresse ID");
            }

            // Tjek om der findes shop_invoice2 data for denne adresse (using ActiveRecord pattern from main)
            $invoice2_results = \ShopInvoice2::find_by_sql("SELECT * FROM shop_invoice_2 WHERE shop_id = ? AND invoice_index = ?", array($shop_id, $address_id));
            
            $data_source = 'shop_invoice2';
            $invoice_data = null;
            
            if (!empty($invoice2_results)) {
                // Brug shop_invoice2 data
                $invoice_data = $invoice2_results[0]->attributes;
            } else {
                // Fallback til shop_metadata
                $metadata_results = \ShopMetadata::find_by_sql("SELECT * FROM shop_metadata WHERE shop_id = ?", array($shop_id));
                
                if (!empty($metadata_results)) {
                    $invoice_data = $metadata_results[0]->attributes;
                    $data_source = 'shop_metadata';
                } else {
                    // Opret tom struktur
                    $invoice_data = array();
                    $data_source = 'new';
                }
            }

            echo json_encode([
                "status" => 1,
                "data" => $invoice_data,
                "data_source" => $data_source,
                "is_approved" => ($data_source === 'shop_invoice2' && !empty($invoice2_results) && $invoice2_results[0]->approved == 1)
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved hentning af faktureringdata: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Gem shop_invoice2 data
     */
    public function saveShopInvoiceData()
    {
        $shop_id = intval($_POST["shop_id"]);
        $address_id = intval($_POST["address_id"]);

        try {
            // Tjek om der allerede findes shop_invoice2 data for denne adresse
            $existing_results = \ShopInvoice2::find_by_sql("SELECT * FROM shop_invoice_2 WHERE shop_id = ? AND invoice_index = ?", array($shop_id, $address_id));
            
            // Tjek om finalbilling er godkendt - hvis ja, skal godkendelse fjernes først
            if (!empty($existing_results) && $existing_results[0]->approved == 1) {
                throw new \Exception("Finalbilling er godkendt og kan ikke ændres. Fjern godkendelse først for at gemme ændringer.");
            }
            
            // Definer hvilke felter der kan opdateres - alle felter fra shop_invoice på nær invoice_index og invoice_state
            $allowed_fields = [
                'is_foreign',
                'payment_terms',
                'payment_special',
                'payment_special_note',
                'payment_note',
                'invoice_fee',
                'invoice_fee_value',
                'environment_fee',
                'discount_option',
                'discount_value',
                'valgshop_fee',
                'delivery_date',
                'handover_date',
                'multiple_deliveries',
                'multiple_deliveries_data',
                'private_delivery',
                'privatedelivery_price',
                'foreign_delivery',
                'foreign_delivery_names',
                'foreign_delivery_date',
                'foreign_names',
                'delivery_terms',
                'deliveryprice_option',
                'deliveryprice_amount',
                'deliveryprice_note',
                'delivery_note_internal',
                'delivery_note_external',
                'dot_use',
                'dot_amount',
                'dot_price',
                'dot_note',
                'carryup_use',
                'carryup_amount',
                'carryup_price',
                'carryup_note',
                'user_count',
                'present_count',
                'autogave_use',
                'autogave_itemno',
                'plant_tree',
                'plant_tree_price',
                'budget',
                'flex_budget',
                'present_nametag',
                'present_nametag_price',
                'present_papercard',
                'present_papercard_price',
                'present_wrap',
                'present_wrap_price',
                'handling_special',
                'handling_notes',
                'loan_use',
                'loan_deliverydate',
                'loan_pickupdate',
                'loan_notes',
                'other_notes',
                'otheragreements_note',
                'nav_debitor_no',
                'requisition_no'
            ];

            // Samle data og parametre
            $field_data = array();
            $date_fields = ['delivery_date', 'handover_date', 'foreign_delivery_date', 'loan_deliverydate', 'loan_pickupdate'];
            
            foreach ($allowed_fields as $field) {
                if (isset($_POST[$field])) {
                    $value = $_POST[$field];
                    // Handle empty date fields by converting to NULL
                    if (in_array($field, $date_fields) && empty($value)) {
                        $field_data[$field] = null;
                    } else {
                        $field_data[$field] = $value;
                    }
                }
            }
            
            if (empty($field_data)) {
                throw new \Exception("Ingen data at gemme");
            }

            if (!empty($existing_results)) {
                // UPDATE existing record using Dbsqli::setSql2
                $update_parts = array();
                
                foreach ($field_data as $field => $value) {
                    $escaped_value = $this->escapeSqlValue($value);
                    $update_parts[] = "`{$field}` = {$escaped_value}";
                }
                
                $update_sql = "UPDATE shop_invoice_2 SET " . implode(", ", $update_parts) . ", updated_datetime = NOW() WHERE shop_id = {$shop_id} AND invoice_index = {$address_id}";
                \Dbsqli::setSql2($update_sql);
                
            } else {
                // INSERT new record using Dbsqli::setSql2
                $field_names = array_merge(['shop_id', 'invoice_index'], array_keys($field_data));
                $field_values = array($shop_id, $address_id);
                
                foreach ($field_data as $value) {
                    $field_values[] = $this->escapeSqlValue($value);
                }
                
                $insert_sql = "INSERT INTO shop_invoice_2 (`" . implode("`, `", $field_names) . "`) VALUES (" . implode(", ", $field_values) . ")";
                \Dbsqli::setSql2($insert_sql);
            }


            echo json_encode([
                "status" => 1,
                "message" => "Finalbilling gemt succesfuldt i shop_invoice2"
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved gemning af finalbilling: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Hent shop_metadata som default værdier for finalbilling
     */
    public function getShopMetadataForDefaults()
    {
        $shop_id = intval($_POST["shop_id"]);

        try {
            $sql = "SELECT * FROM shop_metadata WHERE shop_id = ?";
            $metadata = \ShopMetadata::find_by_sql($sql, array($shop_id));

            echo json_encode([
                "status" => 1,
                "data" => !empty($metadata) ? $metadata[0] : null
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved hentning af metadata: " . $e->getMessage()
            ]);
        }
    }
    public function getFinalbillingRapporter()
    {
        try {
            // Implementer rapport logik
            echo json_encode([
                "status" => 1,
                "data" => []
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved hentning af rapporter: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Godkend finalbilling for en adresse
     */
    public function approveFinalbilling()
    {
        $shop_id = intval($_POST["shop_id"]);
        $address_id = intval($_POST["address_id"]);

        try {
            // Tjek om der findes shop_invoice2 data for denne adresse
            $existing_results = \ShopInvoice2::find_by_sql("SELECT * FROM shop_invoice_2 WHERE shop_id = ? AND invoice_index = ?", array($shop_id, $address_id));
            
            if (!empty($existing_results)) {
                // UPDATE existing record
                $update_sql = "UPDATE shop_invoice_2 SET approved = 1, approved_date = NOW() WHERE shop_id = {$shop_id} AND invoice_index = {$address_id}";
                \Dbsqli::setSql2($update_sql);
            } else {
                // INSERT new record with approval
                $insert_sql = "INSERT INTO shop_invoice_2 (shop_id, invoice_index, approved, approved_date) VALUES ({$shop_id}, {$address_id}, 1, NOW())";
                \Dbsqli::setSql2($insert_sql);
            }


            echo json_encode([
                "status" => 1,
                "message" => "Finalbilling godkendt succesfuldt"
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved godkendelse af finalbilling: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Fjern godkendelse af finalbilling for en adresse
     */
    public function removeApprovalFinalbilling()
    {
        $shop_id = intval($_POST["shop_id"]);
        $address_id = intval($_POST["address_id"]);

        try {
            // UPDATE existing record
            $update_sql = "UPDATE shop_invoice_2 SET approved = 0, approved_date = NULL WHERE shop_id = {$shop_id} AND invoice_index = {$address_id}";
            \Dbsqli::setSql2($update_sql);


            echo json_encode([
                "status" => 1,
                "message" => "Godkendelse fjernet succesfuldt"
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "status" => 0,
                "error" => "Fejl ved fjernelse af godkendelse: " . $e->getMessage()
            ]);
        }
    }
}