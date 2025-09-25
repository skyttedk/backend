<?php

class EarlyPresent extends BaseModel {
    static $table_name  = "early_present";
    static $primary_key = "id";

  	static public function readEarlyPresent() {
		$EarlyPresent = EarlyPresent::all();
        return($EarlyPresent);
	}
	static public function createEarlyPresent($data) {
		$EarlyPresent = new EarlyPresent($data);
        $EarlyPresent->save();
        return($EarlyPresent);
	}
	static public function deleteEarlyPresent($id) {
            $media = EarlyPresent::find($id);
            $media->active = 0;
		    $media->save();
    }
	static public function updateEarlyPresent($data) {
		$media = EarlyPresent::find($data['id']);
		$media->update_attributes($data);
        $media->save();
        return($media);
	}


}
?>