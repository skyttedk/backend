<?php



class ShopPresentCategory extends BaseModel {
    static $table_name  = "shop_present_category";
    static $primary_key = "id";



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
/*
    static public function ShopPresentCategory($data) {
        $kontainerQueue = new KontainerQueue($data);
        $kontainerQueue->save();
        return($kontainerQueue);
    }

    static public function readShopPresentCategory($id) {
        $kontainerQueue = KontainerQueue::find($id);
        return($kontainerQueue);
    }

    static public function updateKontainerQueue($data) {
        $kontainerQueue = KontainerQueue::find($data['id']);
        $kontainerQueue->update_attributes($data);
        $kontainerQueue->save();
        return($kontainerQueue);
    }
*/
}
?>