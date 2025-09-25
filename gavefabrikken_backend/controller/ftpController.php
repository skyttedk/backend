<?php
  ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class ftpController Extends baseController {

    public function Index() {

    }

    /***
     * 11/11 2022 ALLE POSTNOR FTP FUNKTIONER HAR FÅET EN RETURN I STARTEN FOR AT VÆRE SIKKER PÅ AT DE IKKE KØRES
     * POST NORD SKAL IKKE BRUGES I 2022!
     */

    // Cronjob function, this function is called to both download and upload in same process
    public function cronjob() {

        return;
        echo "Start upload<br>";
        $this->runQueue();
        echo "<br>Upload completed<br>";

        System::connection()->transaction();

        echo "<br>Start download:<br>";
        $this->download();
        echo "<br>Download finished<br>";

    }

    public function postnordCheck()
    {
        return;

      echo "--- post => db -----<br>";
      $postnordList = [];
      $conn = $this->ftpConn(2);
      $list = $this->getFileList($conn,"/../Prod/From_PN/Temp");
      foreach($list as $file){

         $filepathArr = explode("/", $file);
         $filename = $filepathArr[sizeofgf($filepathArr)-1 ];
         $file = explode(".", $filename);
         $postnordList[] = $file[0];
         $sql =  "select * from ftp_download where file_name = '".$file[0]."'" ;
         $rs = Dbsqli::getSql2($sql);
         if(sizeofgf($rs)  == 0 ){
            echo $file[0]."<br>";
         }
      }
      echo "--- db => post -----<br>";
      $sql =  "select * from ftp_download where webhook = 'postnord'" ;
      $rs = Dbsqli::getSql2($sql);

      foreach($rs as $record){

        if(!in_array($record["file_name"], $postnordList) == true){
           echo $record["file_name"]."<br>";
        }
      }
      echo "--- checking for dublet -----<br>";
      $sql =  "select count(file_name) as antal from ftp_download where webhook = 'postnord' group by file_name having antal > 1" ;
      $rs = Dbsqli::getSql2($sql);
      if(sizeofgf($rs) > 0){
        echo "Problem with dublet<br>";
      }


      echo "done";

    }



    public function download(){

        return;

        $error = false;
        $conn = $this->ftpConn(2);
        $list = $this->getFileList($conn,"/../Prod/From_PN");
        if(!$list){
             throw new exception("Couldn't read directory");
             return;
        }
        if(sizeofgf($list) >0){
            $result = $this->doDownload($conn,$list[0]);
        }

        /*
        foreach($list as $path){
            $result = $this->doDownload($conn,$path);
            if($result != true){
              throw new exception("Error");
              return;
            }
        }
        */
        $dummy = array();
        response::success(make_json("result", $dummy));

    }



    public function doDownload($conn,$remote_filename){

        return;

        $error = false;

        $filepathArr = explode("/", $remote_filename);
        $filename = $filepathArr[sizeofgf($filepathArr)-1 ];

        $local_file = "upload/postnord/".$filename;

        if(!$this->downloadfile($conn,$local_file, $remote_filename )){
            $error[] = "Couldn't download file: ".$remote_filename;
        }
        if($error == false){
            if(!$this->movefile($conn,$remote_filename, "/../Prod/From_PN/Temp/".$filename)){
                $error[] = "Couldn't move file: ".$remote_filename;
            }
        }
        if($error == false){
            $handle = fopen($local_file, "r");
            $contents = fread($handle, filesize($local_file));
            fclose($handle);
            $filenameArr = explode(".", $filename);

            $data = array(
            "ftpserver_id"=>2,
            "file_name" => $filenameArr[0],
            "file_content" => $contents,
            "file_type" => "xml",
            "webhook" => "postnord"
            );
            $status = $this->createDownload($data);
            return true;
        } else {
            $data = array(
                "ftpserver_id"=>2,
                "file_name" => $filenameArr[0],
                "file_content" => $contents,
                "file_type" => "xml",
                "webhook" => "postnord"
            );
            return $data;
        }
    }
    public function createDownload($data)
    {
        return;

        ftpDownload::createFtpDownload($data);
    }


    public function runQueue(){
        return;
      $this->parseQueue();
    }
    public function addQueue($data=[]){
        return;
       return Ftpqueue::createFtpQueue($data);
    }

    public function parseQueue()
    {
        return;
       $ftpqueue = Ftpqueue::all(array('conditions' => array('sent = 0 && error = 0 '), 'limit' => 1, 'order' => 'id ASC'));

       if(sizeofgf($ftpqueue) > 0){
            $filename = $ftpqueue[0]->file_name.".".$ftpqueue[0]->file_type;

            $conn = $this->ftpConn($ftpqueue[0]->ftpserver_id);
            if($conn == false){
                $this->setError($ftpqueue[0]->id,"Error in connection");
                return;
            }
            $writeFileResult = $this->writeFile($conn,$filename,$ftpqueue[0]->file_content);
            if($writeFileResult != false){
                $this->setError($ftpqueue[0]->id,$writeFileResult);
                return;
            }
            // check if webhook_success
            if($ftpqueue[0]->webhook_success != ""){
                switch ($ftpqueue[0]->webhook_success) {
                    case"postnord":
                        $postnordTest = $this->postnordTest($conn,$filename);
                        if($postnordTest != false){
                            $this->setError($ftpqueue[0]->id,$postnordTest);
                            return;
                        }
                    break;
                   default:
                        $this->setError($ftpqueue[0]->id,"error in webhook_success:".$filename);
                }
            }
            $ftp = Ftpqueue::find($ftpqueue[0]->id);
            $ftp->sent = 1;
            $ftp->sent_datetime = date('d-m-Y H:i:s');
            $ftp->error_message = '';
            $ftp->save();
            System::connection()->commit();
            System::connection()->transaction();
            $dummy = array();
            response::success(make_json("result", $dummy));
       } else {
         echo "done";
           System::connection()->commit();
       }
    }
    private function setError($recordID,$errorMsg){
      $ftp = Ftpqueue::find($recordID);
      $ftp->sent  = 1;
      $ftp->error = 1;
      $ftp->sent_datetime = date('d-m-Y H:i:s');
      $ftp->error_message = $errorMsg;
      $ftp->save();
      System::connection()->commit();
      System::connection()->transaction();
      throw new exception($errorMsg);
    }

    private function writeFile($conn,$filename,$fileContent)
    {
        return;
        $error = false;
        try {
            $fp = fopen('php://temp', 'r+');
            fwrite($fp, $fileContent);
            rewind($fp);
            ftp_fput($conn,$filename, $fp, FTP_BINARY);
        }
        catch(Exception $e) {
          $error = $e->getMessage();
        }
        return  $error;

    }
    private function getFileList($conn,$directory){
      // get contents of the current directory
      return ftp_nlist($conn, $directory);
    }
    private function movefile($conn, $old_file, $new_file)
    {
        return ftp_rename($conn, $old_file, $new_file);
    }
    private function downloadfile($conn,$local_file="usdemo.xml", $remote_filename)
    {
      return ftp_get($conn, $local_file, $remote_filename, FTP_BINARY);


    }


    private function ftpConn($serverID)
    {
        $ftpServer = FtpServer::readFtpServer($serverID);
        $ftpConn = ftp_connect($ftpServer->host);
        $login = ftp_login($ftpConn,$ftpServer->user,$ftpServer->password);
        // connect to ftp server
        if ((!$ftpConn) || (!$login)) {
            return false;
        }
        if($ftpServer->path != ""){
         if(ftp_chdir($ftpConn, $ftpServer->path)){
             return $ftpConn;
         } else {
             throw new exception("Couldn't change directory");
             return;
         }
       } else {
            return $ftpConn;
       }
    }

    private function postnordTest($conn,$filename)
    {
        $error = false;
        try {
            ftp_rename($conn, $filename, '../'.$filename);
        }
        catch(Exception $e) {
          $error = $e->getMessage();
        }
        return  $error;
    }
    private function postnord()
    {
        ftp_rename($conn, $filename, '../'.$filename);
    }
}

?>