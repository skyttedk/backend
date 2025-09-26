<?php
// Model MailServer
// Date created  Mon, 16 Jan 2017 15:27:08 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) name                          varchar(30)         YES
//   (   ) server_name                   varchar(200)        YES
//   (   ) username                      varchar(200)        YES
//   (   ) password                      varchar(200)        YES
//   (   ) sender_email                  varchar(200)        YES
//   (   ) sender_name                   varchar(200)        YES
//***************************************************************

class FtpServer extends ActiveRecord\Model {
    static $table_name  = "ftp_server";
    static $primary_key = "id";



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
    static public function readFtpServer($id) {
        $ftpServer= FtpServer::find($id);
        return($ftpServer);
    }



}
?>

