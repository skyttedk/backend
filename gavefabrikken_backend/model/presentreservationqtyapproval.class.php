<?php

class PresentReservationQtyApproval extends BaseModel
{
    public static $table_name = 'present_reservation_qty_approval';
    public static $primary_key = 'id';

    public static function create_approval_request($group_token, $shop_id, $salesperson_code, $language_id, $items)
    {
        $approvals = [];
        
        foreach ($items as $item) {
            $approval = new self();
            $approval->group_token = $group_token;
            $approval->shop_id = $shop_id;
            $approval->salesperson_code = $salesperson_code;
            $approval->language_id = $language_id;
            $approval->itemno = $item['itemno'];
            $approval->nav_stock = $item['nav_stock'];
            $approval->requested_qty = $item['requested_qty'];
            $approval->is_external = $item['is_external'];
            $approval->approved = 0;
            $approval->email_sent = 0;
            
            if ($approval->save()) {
                $approvals[] = $approval;
            }
        }
        
        return $approvals;
    }
    
    public static function find_by_group_token($group_token)
    {
        return self::find('all', array(
            'conditions' => array('group_token = ?', $group_token),
            'order' => 'created_at ASC'
        ));
    }
    
    public static function has_pending_approvals($shop_id)
    {
        $count = self::count(array(
            'conditions' => array(
                'shop_id = ? AND approved = 0', 
                $shop_id
            )
        ));
        
        return $count > 0;
    }
}