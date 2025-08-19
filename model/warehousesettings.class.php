<?php


class WarehouseSettings extends BaseModel {
    static $table_name  = "warehouse_settings";
    static $primary_key = "id";

    static public function createFiles($data) {
        $system = new WarehouseSettings($data);
        $system->save();
        return($system);
    }

    static public function readFiles($id) {
        $system = WarehouseSettings::find($id);
        return($system);
    }

    static public function updateFiles($data) {
        $system = WarehouseSettings::find($data['id']);
        $system->update_attributes($data);
        $system->save();
        return($system);
    }
}
?>