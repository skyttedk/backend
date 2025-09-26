<?php

namespace GFUnit\cardshop\freight;

class CSFreightHelper {
    private $companyId;

    // Constructor to initialize the helper class
    public function __construct($companyId) {
        $this->companyId = $companyId;
    }

    // Helper function to run the query with optional filters
    private function runQuery($filters = [], $includeInactive = false) {
        $sql = "SELECT 
    c.id AS company_id, 
    c.name, 
    c.pid AS parent_id, 
    c.ship_to_company, 
    c.ship_to_attention, 
    c.ship_to_address, 
    c.ship_to_address_2, 
    c.ship_to_postal_code, 
    c.ship_to_city, 
    c.ship_to_country, 
    c.contact_name, 
    c.contact_phone, 
    c.contact_email, 
    co.expire_date, 
    co.shop_id, 
    co.company_id AS billing_company_id, 
    cf.id AS cardshopfreight_id, 
    cf.note, 
    CASE WHEN su.id IS NULL THEN 1 ELSE 0 END AS inactive, 
    GROUP_CONCAT(DISTINCT co.order_no) AS order_numbers, 
    GROUP_CONCAT(DISTINCT co.id) AS order_ids, 
    COUNT(DISTINCT co.id) AS order_count, 
    COUNT(DISTINCT su.id) AS total_quantity, 
    CASE WHEN co.company_id = su.company_id THEN 'parent' ELSE 'child' END AS delivery_type 
FROM 
    company_order co 
LEFT JOIN 
    shop_user su ON co.id = su.company_order_id 
    AND su.blocked = 0 
    AND su.is_demo = 0 
    AND su.shutdown = 0 
    AND su.is_delivery = 0 
JOIN 
    company c ON c.id = su.company_id 
LEFT JOIN 
    (SELECT 
        cf.id, 
        cf.company_id, 
        fco.expire_date, 
        fco.shop_id, 
        cf.note 
     FROM 
        cardshop_freight cf 
     JOIN 
        company_order fco ON cf.company_order_id = fco.id) cf 
    ON co.expire_date = cf.expire_date 
    AND co.shop_id = cf.shop_id 
    AND su.company_id = cf.company_id 
     
WHERE 
    co.order_state NOT IN (7, 8) ";

        // Add filters to the query
        foreach ($filters as $key => $value) {

            if(is_array($value)) {
                $sql .= " AND $key IN (" . implode(',', $value) . ")";
            } else {
                $sql .= " AND $key = '$value'";
            }


        }

        // Include or exclude inactive items
        if (!$includeInactive) {
            $sql .= " AND su.id IS NOT NULL ";
        }

        $sql .= " GROUP BY c.id, co.expire_date, co.shop_id, co.company_id, cf.id, cf.note, su.company_id ORDER BY company_id ASC ";

        //echo "<div>".$sql."</div>";

        //echo $sql;
        // Execute the query and return the results
        return \CompanyOrder::find_by_sql($sql);
    }


    // Method to fetch CSFreightItem objects for a specific company
    public function getFreightItemsForCompany($includeChildren = false, $includeInactive = false) {
        $filters = ['su.company_id' => $this->companyId];
        if ($includeChildren) {
            // Add logic to include child companies
            $childCompanyIds = $this->getChildCompanyIds($this->companyId);
            $filters['su.company_id'] = array_merge([$this->companyId], $childCompanyIds);
        }
        $results = $this->runQuery($filters, $includeInactive);
        return $this->createFreightItems($results);
    }

    // Method to fetch CSFreightItem objects for a specific order
    public function getFreightItemsForOrder($orderId, $includeInactive = false) {
        $filters = ['co.id' => $orderId];
        $results = $this->runQuery($filters, $includeInactive);
        return $this->createFreightItems($results);
    }

    // Method to fetch CSFreightItem objects for a specific expire_date and shop_id combination
    public function getFreightItemsForExpireDateAndShop($expireDate, $shopId, $includeInactive = false) {
        $filters = ['co.expire_date' => $expireDate, 'co.shop_id' => $shopId];
        $results = $this->runQuery($filters, $includeInactive);
        return $this->createFreightItems($results);
    }

    // Method to update or create a CardshopFreight object
    public function updateOrCreateFreight($cardshopFreightItem) {
        // Check if a CardshopFreight object already exists for the given combination
        $existingFreight = \CardshopFreight::find_by_sql("
            SELECT * FROM cardshop_freight 
            WHERE company_order_id IN (" . implode(',', $cardshopFreightItem->getCompanyOrderList()) . ")
            AND company_id = " . $cardshopFreightItem->getCompanyId() . "
            AND shop_id = " . $cardshopFreightItem->getShopId() . "
            AND expire_date = '" . $cardshopFreightItem->getExpireDate() . "'
        ");

        if ($existingFreight) {
            // Update the existing CardshopFreight object
            $existingFreight->note = $cardshopFreightItem->getCardshopFreight()->note;
            $existingFreight->save();
        } else {
            // Create a new CardshopFreight object
            $newFreight = new CardshopFreight();
            $newFreight->company_order_id = $cardshopFreightItem->getCompanyOrderList()[0]; // Assuming the first order ID
            $newFreight->company_id = $cardshopFreightItem->getCompanyId();
            $newFreight->shop_id = $cardshopFreightItem->getShopId();
            $newFreight->expire_date = $cardshopFreightItem->getExpireDate();
            $newFreight->note = $cardshopFreightItem->getCardshopFreight()->note;
            $newFreight->save();
        }
    }

    // Method to delete an inactive CardshopFreight object
    public function deleteInactiveFreight($cardshopFreightItem) {
        if ($cardshopFreightItem->isInactive()) {
            $cardshopFreight = $cardshopFreightItem->getCardshopFreight();
            if ($cardshopFreight) {
                $cardshopFreight->delete();
            }
        }
    }

    // Helper method to create CSFreightItem objects from query results
    private function createFreightItems($results) {
        $freightItems = [];
        foreach ($results as $row) {
            $cardshopFreight = null;
            if ($row->cardshopfreight_id) {
                $cardshopFreight = \CardshopFreight::find($row->cardshopfreight_id);
                $cardshopFreight->note = $row->note;
            }
            $freightItem = new CSFreightItem(
                $cardshopFreight,
                $row->delivery_type === 'child',
                explode(',', $row->order_numbers),
                $row->shop_id,
                $row->expire_date,
                $row->company_id,
                $row->inactive,
                $row->name,
                $row->parent_id,
                $row->ship_to_company,
                $row->ship_to_attention,
                $row->ship_to_address,
                $row->ship_to_address_2,
                $row->ship_to_postal_code,
                $row->ship_to_city,
                $row->ship_to_country,
                $row->contact_name,
                $row->contact_phone,
                $row->contact_email,
                $row->billing_company_id,
                $row->order_count,
                $row->total_quantity,
                $row->delivery_type
            );
            $freightItems[] = $freightItem;
        }
        return $freightItems;
    }

    // Helper method to get child company IDs
    private function getChildCompanyIds($parentId) {
        $childCompanies = \Company::find_by_sql("SELECT id FROM company WHERE pid = $parentId");
        return array_map(function($company) {
            return $company->id;
        }, $childCompanies);
    }
}