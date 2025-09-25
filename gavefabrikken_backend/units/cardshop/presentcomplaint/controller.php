<?php

namespace GFUnit\cardshop\presentcomplaint;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * SERVICES
     */

    /**
     * Get all companies with complaints across all shops
     * Returns comprehensive list for present-complaint overview
     */
    public function getAllComplaints()
    {
        // Get all shops first
        $shops = \Shop::find('all', array(
            'conditions' => array('deleted = 0'),
            'order' => 'name'
        ));

        $allComplaints = [];
        
        foreach ($shops as $shop) {
            // Get complaint list for each shop using the existing API pattern
            $shopComplaints = $this->getComplaintListForShop($shop->id);
            
            if (!empty($shopComplaints)) {
                foreach ($shopComplaints as $complaint) {
                    $complaint['shop_name'] = $shop->name;
                    $complaint['shop_id'] = $shop->id;
                    $allComplaints[] = $complaint;
                }
            }
        }

        echo json_encode(array("status" => 1, "result" => $allComplaints), JSON_PRETTY_PRINT);
    }

    /**
     * Get complaint list for a specific shop
     * Integrates with existing cardshop/cards/getComplaintList/{shopID} endpoint logic
     */
    private function getComplaintListForShop($shopID)
    {
        try {
            // Query to get users with complaints for a specific shop using correct table schema
            $sql = "SELECT DISTINCT
                        su.id as user_id,
                        su.username,
                        c.id as company_id,
                        c.name as company_name,
                        c.cvr,
                        opc.complaint_txt,
                        opc.created as created_date,
                        opc.last_update as updated_date
                    FROM shop_user su
                    INNER JOIN company c ON su.company_id = c.id
                    INNER JOIN order_present_complaint opc ON su.id = opc.shopuser_id
                    WHERE su.shop_id = " . intval($shopID) . "
                    AND opc.complaint_txt IS NOT NULL
                    AND opc.complaint_txt != ''
                    AND opc.active = 1
                    AND c.deleted = 0
                    ORDER BY opc.last_update DESC, opc.created DESC, c.name ASC";

            $complaints = \Dbsqli::getSql2($sql);
            
            // Decode complaint text for proper display
            if ($complaints) {
                foreach ($complaints as $index => $complaint) {
                    if (!empty($complaint['complaint_txt'])) {
                        $complaints[$index]['complaint_txt'] = urldecode($complaint['complaint_txt']);
                    }
                }
            }
            
            return $complaints ? $complaints : [];
            
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get detailed complaint data for a specific user
     * Integrates with existing cardshop/cards/getComplaint/{userid} endpoint logic
     */
    public function getComplaintDetail($userID)
    {
        try {
            $sql = "SELECT
                        su.id as user_id,
                        su.username,
                        c.id as company_id,
                        c.name as company_name,
                        c.cvr,
                        c.contact_name,
                        c.contact_phone,
                        c.contact_email,
                        s.id as shop_id,
                        s.name as shop_name,
                        opc.complaint_txt,
                        opc.created as created_date,
                        opc.last_update as updated_date
                    FROM shop_user su
                    INNER JOIN company c ON su.company_id = c.id
                    INNER JOIN shop s ON su.shop_id = s.id
                    LEFT JOIN order_present_complaint opc ON su.id = opc.shopuser_id AND opc.active = 1
                    WHERE su.id = " . intval($userID) . "
                    AND c.deleted = 0";

            $result = \Dbsqli::getSql2($sql);
            
            if ($result && count($result) > 0) {
                // Decode complaint text if it exists and is encoded
                if (!empty($result[0]['complaint_txt'])) {
                    $result[0]['complaint_txt'] = urldecode($result[0]['complaint_txt']);
                }
            }

            echo json_encode(array("status" => 1, "result" => $result), JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "result" => [], "message" => "Fejl ved indlæsning af reklamationsdetaljer"), JSON_PRETTY_PRINT);
        }
    }

    /**
     * Search complaints by various criteria
     */
    public function search()
    {
        $searchText = $_POST['text'] ?? '';
        $language = $_POST['LANGUAGE'] ?? 1;

        if (empty($searchText) || strlen($searchText) < 3) {
            echo json_encode(array("status" => 1, "result" => []), JSON_PRETTY_PRINT);
            return;
        }

        try {
            $sql = "SELECT DISTINCT
                        su.id as user_id,
                        su.username,
                        c.id as company_id,
                        c.name as company_name,
                        c.cvr,
                        s.id as shop_id,
                        s.name as shop_name,
                        opc.complaint_txt,
                        opc.created as created_date,
                        opc.last_update as updated_date
                    FROM shop_user su
                    INNER JOIN company c ON su.company_id = c.id
                    INNER JOIN shop s ON su.shop_id = s.id
                    INNER JOIN order_present_complaint opc ON su.id = opc.shopuser_id
                    WHERE c.deleted = 0
                    AND opc.complaint_txt IS NOT NULL
                    AND opc.complaint_txt != ''
                    AND opc.active = 1
                    AND (
                        c.name LIKE '%" . addslashes($searchText) . "%' OR
                        c.cvr LIKE '%" . addslashes($searchText) . "%' OR
                        c.contact_name LIKE '%" . addslashes($searchText) . "%' OR
                        su.username LIKE '%" . addslashes($searchText) . "%' OR
                        s.name LIKE '%" . addslashes($searchText) . "%' OR
                        opc.complaint_txt LIKE '%" . addslashes($searchText) . "%'
                    )
                    ORDER BY opc.last_update DESC, opc.created DESC, c.name ASC
                    LIMIT 100";

            $results = \Dbsqli::getSql2($sql);

            // Decode complaint text for all results
            if ($results) {
                foreach ($results as $index => $result) {
                    if (!empty($result['complaint_txt'])) {
                        $results[$index]['complaint_txt'] = urldecode($result['complaint_txt']);
                    }
                }
            }

            echo json_encode(array("status" => 1, "result" => $results ? $results : []), JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "result" => [], "message" => "Søgefejl: " . $e->getMessage()), JSON_PRETTY_PRINT);
        }
    }

    /**
     * Export complaints data as CSV
     */
    public function exportCsv()
    {
        try {
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="reklamationer-' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');

            // CSV headers
            fputcsv($output, [
                'Bruger ID', 'Fornavn', 'Efternavn', 'Brugernavn',
                'Virksomheds ID', 'Virksomhedsnavn', 'CVR',
                'Butiks ID', 'Butiksnavn',
                'Reklamationstekst', 'Oprettelsesdato', 'Opdateringsdato'
            ]);

            // Get all complaints for export using correct table schema
            $sql = "SELECT DISTINCT
                        su.id as user_id,
                        su.username,
                        c.id as company_id,
                        c.name as company_name,
                        c.cvr,
                        s.id as shop_id,
                        s.name as shop_name,
                        opc.complaint_txt,
                        opc.created as created_date,
                        opc.last_update as updated_date
                    FROM shop_user su
                    INNER JOIN company c ON su.company_id = c.id
                    INNER JOIN shop s ON su.shop_id = s.id
                    INNER JOIN order_present_complaint opc ON su.id = opc.shopuser_id
                    WHERE c.deleted = 0
                    AND opc.complaint_txt IS NOT NULL
                    AND opc.complaint_txt != ''
                    AND opc.active = 1
                    ORDER BY opc.last_update DESC, opc.created DESC, c.name ASC";

            $results = \Dbsqli::getSql2($sql);

            if ($results) {
                foreach ($results as $row) {
                    // Decode complaint text and clean up for CSV
                    $complaintText = !empty($row['complaint_txt']) ? urldecode($row['complaint_txt']) : '';
                    $complaintText = str_replace(['\r\n', '\n', '\r'], ' ', $complaintText);
                    
                    fputcsv($output, [
                        $row['user_id'],
                        '', // first_name not available
                        '', // last_name not available
                        $row['username'],
                        $row['company_id'],
                        $row['company_name'],
                        $row['cvr'],
                        $row['shop_id'],
                        $row['shop_name'],
                        $complaintText,
                        $row['created_date'],
                        $row['updated_date']
                    ]);
                }
            }

            fclose($output);
            exit;
            
        } catch (Exception $e) {
            // If export fails, return JSON error
            header('Content-Type: application/json');
            echo json_encode(array("status" => 0, "message" => "Eksport mislykkedes: " . $e->getMessage()));
            exit;
        }
    }
}