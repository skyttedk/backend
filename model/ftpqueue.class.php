<?php

class ftpqueue extends BaseModel {
    static $table_name = "ftp_queue";
    static $primary_key = "id";



    //---------------------------------------------------------------------------------------
    // Static CRUD Methods
    //---------------------------------------------------------------------------------------


    static public function createFtpQueue($data) {
        $error = false;
        if(isset($data["ftpserver_id"]) == false || is_int($data["ftpserver_id"]) == false ) $error = false;
        if(
            empty($data["file_name"]) ||
            empty($data["file_content"]) ||
            empty($data["file_type"])
          ){$error = true;}
        if($error) {
            throw new exception("Error in variables");
        }
        $ftpqueue = new Ftpqueue($data);
        $ftpqueue->sent = 0;
        $ftpqueue->error = 0;
        $ftpqueue->save();
        System::connection()->commit();
        System::connection()->transaction();
        return $ftpqueue;
    }


}








