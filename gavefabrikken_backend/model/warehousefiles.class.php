<?php


class WarehouseFiles extends BaseModel {
    static $table_name  = "warehouse_files";
    static $primary_key = "id";

    static public function createFiles($data) {
        $system = new WarehouseFiles($data);
        $system->save();
        return($system);
    }

    static public function readFiles($id) {
        $system = WarehouseFiles::find($id);
        return($system);
    }

    static public function updateFiles($data) {
        $system = WarehouseFiles::find($data['id']);
        $system->update_attributes($data);
        $system->save();
        return($system);
    }
}
?>