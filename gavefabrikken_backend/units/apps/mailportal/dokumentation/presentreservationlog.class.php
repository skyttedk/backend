<?php
// Model CompanyNotesEx
// Date created  Wed, 11 Oct 2017 14:30:29 +0200
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) company_id                    int(11)             YES
//   (   ) note                          text                YES
//   (   ) created_datetime              datetime            YES
//***************************************************************

class presentreservationlog extends ActiveRecord\Model {
    static $table_name  = "present_reservation_log";
    static $primary_key = "id";



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
    static public function createPresentreservationlog($data) {
        $presentreservationlog = new presentreservationlog($data);
        $presentreservationlog->save();
        return($presentreservationlog);
    }


//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------






}
?>

