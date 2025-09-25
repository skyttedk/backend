<?php
set_time_limit ( 4000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();



$sql = "select pt_pdf from gavefabrikken2023.shop where pt_pdf != '' ";
$rs = $db->get($sql);

foreach ($rs["data"] as $pdf){
  echo  $file =  $pdf["pt_pdf"].".pdf";
    echo "<br>";
    $fileManager = new FileManager(
        '../../presentation/pdf/'.$file,
        '../../presentation2025/pdf/'
    );
    echo $fileManager->copyFile();
    echo "<br>";

}




$sql = "select pt_pdf from shop where pt_pdf != '' ";
$rs = $db->get($sql);

foreach ($rs["data"] as $pdf){
    echo  $file =  $pdf["pt_pdf"].".pdf";
    echo "<br>";
    $fileManager = new FileManager(
        '../../presentation/pdf/'.$file,
        '../../presentation2025/pdf/'
    );
    echo $fileManager->copyFile();
    echo "<br>";

}




class FileManager {
    private $sourceFile;
    private $destinationFolder;

    /**
     * Constructor to initialize source file and destination folder
     *
     * @param string $sourceFile Path to the source file
     * @param string $destinationFolder Path to the destination folder
     */
    public function __construct($sourceFile, $destinationFolder) {
        $this->sourceFile = $sourceFile;
        // Ensure destination folder has trailing slash
        $this->destinationFolder = rtrim($destinationFolder, '/') . '/';
    }

    /**
     * Copy file from source to destination
     *
     * @return string Status message
     */
    public function copyFile() {
        // Check if source file exists
        if (!file_exists($this->sourceFile)) {
            return "Source file does not exist";
        }

        // Make sure destination folder exists
        if (!file_exists($this->destinationFolder)) {
            if (!mkdir($this->destinationFolder, 0777, true)) {
                return "Failed to create destination folder";
            }
        }

        // Create destination path
        $destinationFile = $this->destinationFolder . basename($this->sourceFile);

        // Copy the file
        if (copy($this->sourceFile, $destinationFile)) {
            return "File copied successfully";
        } else {
            return "Failed to copy file";
        }
    }

    /**
     * Set a new source file
     *
     * @param string $sourceFile Path to the source file
     */
    public function setSourceFile($sourceFile) {
        $this->sourceFile = $sourceFile;
    }

    /**
     * Set a new destination folder
     *
     * @param string $destinationFolder Path to the destination folder
     */
    public function setDestinationFolder($destinationFolder) {
        $this->destinationFolder = rtrim($destinationFolder, '/') . '/';
    }

    /**
     * Get the source file path
     *
     * @return string Source file path
     */
    public function getSourceFile() {
        return $this->sourceFile;
    }

    /**
     * Get the destination folder path
     *
     * @return string Destination folder path
     */
    public function getDestinationFolder() {
        return $this->destinationFolder;
    }
}
