<?php



class KontainerSyncJob extends BaseModel {
    static $table_name  = "kontainer_sync_job";
    static $primary_key = "id";



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createKontainerSyncJob($data) {

        $kontainerSyncJob = new KontainerSyncJob($data);
        $kontainerSyncJob->save();
        return($kontainerSyncJob);
    }

    static public function readKontainerSyncJob($id) {
        $kontainerSyncJob = KontainerSyncJob::find($id);
        return($kontainerSyncJob);
    }

    static public function updateKontainerSyncJob($data) {
        $kontainerSyncJob = KontainerSyncJob::find($data['id']);
        $kontainerSyncJob->update_attributes($data);
        $kontainerSyncJob->save();
        return($kontainerSyncJob);
    }
    static public function deleteKontainerSyncJob($data) {
        $kontainerSyncJob = KontainerSyncJob::find($data['id']);
        $kontainerSyncJob->status = 3;
        $kontainerSyncJob->save();
        return($kontainerSyncJob);
    }





}
?>