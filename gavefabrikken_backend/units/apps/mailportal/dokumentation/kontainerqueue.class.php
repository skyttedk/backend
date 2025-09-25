<?php



class KontainerQueue extends BaseModel {
    static $table_name  = "kontainer_queue";
    static $primary_key = "id";



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createKontainerQueue($data) {
        $kontainerQueue = new KontainerQueue($data);
        $kontainerQueue->save();
        return($kontainerQueue);
    }

    static public function readKontainerQueue($id) {
        $kontainerQueue = KontainerQueue::find($id);
        return($kontainerQueue);
    }

    static public function updateKontainerQueue($data) {
        $kontainerQueue = KontainerQueue::find($data['id']);
        $kontainerQueue->update_attributes($data);
        $kontainerQueue->save();
        return($kontainerQueue);
    }

}
?>