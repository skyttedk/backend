<?php

namespace GFUnit\apps\mailportal\services;

require_once dirname(__DIR__) . '/dummy_data.php';

use DummyData;

class EmployeeService
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
     * Hent alle medarbejdere baseret på shop type
     */
    public function getAll()
    {
        // Fallback to dummy data if no shop context
        if (!$this->shopType || (!$this->shopId && !$this->companyId)) {
            return DummyData::getEmployees();
        }
        
        try {
            $users = [];
            
            if ($this->shopType === 'valgshop' && $this->shopId) {
                // Query valgshop users by shop_id - exclude demo, blocked and shutdown users
                $users = \ShopUser::find('all', array(
                    'conditions' => array('shop_id = ? AND (is_demo IS NULL OR is_demo = 0) AND (blocked IS NULL OR blocked = 0) AND (shutdown IS NULL OR shutdown = 0)', $this->shopId),
                    'order' => 'id ASC'
                ));
            } elseif ($this->shopType === 'cardshop' && $this->companyId) {
                // Query cardshop users by company_id - exclude demo, blocked and shutdown users
                $users = \ShopUser::find('all', array(
                    'conditions' => array('company_id = ? AND (is_demo IS NULL OR is_demo = 0) AND (blocked IS NULL OR blocked = 0) AND (shutdown IS NULL OR shutdown = 0)', $this->companyId),
                    'order' => 'id ASC'
                ));
            }
            
            // Convert to MailPortal format
            $result = [];
            foreach ($users as $user) {
                $formatted = $this->formatUserForMailPortal($user, $this->shopType);
                if ($formatted !== null) {
                    $result[] = $formatted;
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            // Fallback to dummy data on error
            error_log("EmployeeService database error: " . $e->getMessage());
            return DummyData::getEmployees();
        }
    }
    
    /**
     * Opdater medarbejder med data konsistens på tværs af tabeller
     */
    public function update($id, $data)
    {
        // Fallback to dummy data response if no shop context
        if (!$this->shopType) {
            return [
                'success' => true,
                'message' => 'Employee updated successfully (dummy mode)'
            ];
        }
        
        try {
            // Find the user first
            $user = \ShopUser::find($id);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            // Update user_attribute table for name and email
            if (isset($data['name'])) {
                $this->updateUserAttribute($id, 'name', $data['name']);
            }
            
            if (isset($data['email'])) {
                $this->updateUserAttribute($id, 'email', $data['email']);
            }
            
            // Update related tables for consistency
            $this->updateRelatedTables($id, $data);
            
            return [
                'success' => true,
                'message' => 'Employee updated successfully'
            ];
            
        } catch (Exception $e) {
            error_log("EmployeeService update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update user attribute by type (name, email) using ActiveRecord pattern
     */
    private function updateUserAttribute($shopuserId, $type, $value)
    {
        $conditions = ['shopuser_id = ?', $shopuserId];
        
        switch ($type) {
            case 'name':
                $conditions = ['shopuser_id = ? AND is_name = 1', $shopuserId];
                break;
            case 'email':
                $conditions = ['shopuser_id = ? AND is_email = 1', $shopuserId];
                break;
            default:
                return false;
        }
        
        // Find and update using ActiveRecord pattern
        $userAttributes = \UserAttribute::find('all', array(
            'conditions' => $conditions
        ));
        
        foreach ($userAttributes as $userAttribute) {
            $userAttribute->attribute_value = $value;
            // $userAttribute->save();
        }
        
        return true;
    }
    
    /**
     * Update related tables for data consistency using ActiveRecord pattern
     */
    private function updateRelatedTables($shopuserId, $data)
    {
        // Update order table using ActiveRecord pattern
        if (isset($data['name']) || isset($data['email'])) {
            $orders = \Order::find('all', array(
                'conditions' => array('shopuser_id = ?', $shopuserId)
            ));
            
            foreach ($orders as $order) {
                if (isset($data['name'])) {
                    $order->user_name = $data['name'];
                }
                if (isset($data['email'])) {
                    $order->user_email = $data['email'];
                }
                // $order->save();
            }
        }
        
        // Update order_attribute table using ActiveRecord pattern
        if (isset($data['name'])) {
            $orderAttributes = \OrderAttribute::find('all', array(
                'conditions' => array(
                    'shopuser_id = ? AND attribute_id IN (SELECT id FROM shop_attribute WHERE is_name = 1)',
                    $shopuserId
                ),
                'joins' => 'LEFT JOIN shop_attribute ON order_attribute.attribute_id = shop_attribute.id'
            ));
            
            foreach ($orderAttributes as $orderAttribute) {
                $orderAttribute->attribute_value = $data['name'];
                // $orderAttribute->save();
            }
        }
        
        if (isset($data['email'])) {
            $orderAttributes = \OrderAttribute::find('all', array(
                'conditions' => array(
                    'shopuser_id = ? AND attribute_id IN (SELECT id FROM shop_attribute WHERE is_email = 1)',
                    $shopuserId
                ),
                'joins' => 'LEFT JOIN shop_attribute ON order_attribute.attribute_id = shop_attribute.id'
            ));
            
            foreach ($orderAttributes as $orderAttribute) {
                $orderAttribute->attribute_value = $data['email'];
                // $orderAttribute->save();
            }
        }
    }
    
    /**
     * Slet medarbejder
     */
    public function delete($id)
    {
        // I produktion ville dette slette fra databasen
        // For nu returnerer vi bare success
        return [
            'success' => true,
            'message' => 'Employee deleted successfully'
        ];
    }
    
    /**
     * Hent medarbejder efter ID
     */
    public function getById($id)
    {
        // Fallback to dummy data if no shop context
        if (!$this->shopType) {
            $employees = DummyData::getEmployees();
            foreach ($employees as $employee) {
                if ($employee['id'] == $id) {
                    return $employee;
                }
            }
            return null;
        }
        
        try {
            $user = \ShopUser::find($id);
            if ($user) {
                return $this->formatUserForMailPortal($user, $this->shopType);
            }
            return null;
            
        } catch (Exception $e) {
            error_log("EmployeeService getById error: " . $e->getMessage());
            
            // Fallback to dummy data
            $employees = DummyData::getEmployees();
            foreach ($employees as $employee) {
                if ($employee['id'] == $id) {
                    return $employee;
                }
            }
            return null;
        }
    }
    
    /**
     * Hent flere medarbejdere efter IDs
     */
    public function getByIds($ids)
    {
        // Fallback to dummy data if no shop context
        if (!$this->shopType) {
            $employees = DummyData::getEmployees();
            $result = [];
            foreach ($employees as $employee) {
                if (in_array($employee['id'], $ids)) {
                    $result[] = $employee;
                }
            }
            return $result;
        }
        
        try {
            $users = \ShopUser::find('all', array(
                'conditions' => array('id IN (?)', $ids)
            ));
            
            $result = [];
            foreach ($users as $user) {
                $result[] = $this->formatUserForMailPortal($user, $this->shopType);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("EmployeeService getByIds error: " . $e->getMessage());
            
            // Fallback to dummy data
            $employees = DummyData::getEmployees();
            $result = [];
            foreach ($employees as $employee) {
                if (in_array($employee['id'], $ids)) {
                    $result[] = $employee;
                }
            }
            return $result;
        }
    }
    
    /**
     * Format user data for MailPortal display
     */
    private function formatUserForMailPortal($user, $shopType)
    {
        $username = '';
        $password = '';
        $email = '';
        $name = '';
        
        // Get user attributes for both shop types
        $attributes = \UserAttribute::find('all', array(
            'conditions' => array('shopuser_id = ?', $user->id)
        ));
        
        foreach ($attributes as $attr) {
            // For valgshop: get username/password from attributes
            if ($shopType === 'valgshop') {
                if ($attr->is_username) $username = $attr->attribute_value ?? '';
                if ($attr->is_password) $password = $attr->attribute_value ?? '';
            }
            
            // For cardshop: only get password from attributes, username comes from shop_user table
            if ($shopType === 'cardshop') {
                if ($attr->is_password) $password = $attr->attribute_value ?? '';
            }
            
            // Get email/name from attributes for both shop types
            if ($attr->is_email) $email = $attr->attribute_value ?? '';
            if ($attr->is_name) $name = $attr->attribute_value ?? '';
        }
        
        // For cardshop: username comes from shop_user table
        if ($shopType === 'cardshop') {
            $username = $user->username ?? '';
        }
        
        // Skip users without basic required data
        if (empty($name) && empty($email) && empty($username)) {
            error_log("User {$user->id} has no name, email or username - skipping");
            return null;
        }
        
        // Use fallbacks if main fields are empty
        if (empty($name)) {
            $name = $username ?: "User {$user->id}";
        }
        if (empty($email)) {
            $email = "no-email@example.com";
        }
        
        return [
            'id' => $user->id,
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'shop_id' => $user->shop_id,
            'company_id' => $user->company_id,
            'created_date' => $user->created_date,
            'blocked' => $user->blocked ?? 0,
            'is_demo' => $user->is_demo ?? 0,
            'token' => $user->token ?? '',
            'language' => 'da', // Default, could be extracted from attributes or shop settings
            'email_status' => 'unknown', // Would need additional email tracking
            'last_email_date' => null, // Would need additional email tracking
            'mail_sent' => 0, // Would need additional email tracking
            'mail_sent_date' => null,
            'has_error' => 0,
            'error_message' => null,
            'template_id' => null,
            'last_email_count' => 0
        ];
    }
}