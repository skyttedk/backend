<?php

//namespace GFUnit\tools\imagecleanup;

if (php_sapi_name() !== 'cli') {
    exit();
}

$site_path = "/var/www/backend/public_html/gavefabrikken_backend";
define ('__SITE_PATH', $site_path);
set_include_path($site_path);

include $site_path."/includes/config.php";
include $site_path."/includes/init.php";

ini_set('max_execution_time', 3000); //300 seconds = 5 minutes
ini_set('memory_limit','4048M');

class Controller
{

    public function __construct()
    {
       // parent::__construct(__FILE__);
    }

    public function run()
    {

        echo "Running cleanup script\r\n\r\n";

        // Load images from db
        //$imageMap = $this->getImageMap();

        $imageMap = $this->loadImageMapFromFile();
        echo "Found files in map: ".count($imageMap);

        // Load images from folders
        $this->processFolder("/var/www/backend/public_html/fjui4uig8s8893478/",$imageMap);
        //$this->processFolder("/var/www/backend/public_html/gavefabrikken_backend/views/media/user/",$imageMap);

    }

    private function loadImageMapFromFile() {

        $mapFilename = $this->getImageMapFile();
        if(!file_exists($mapFilename)) {
            echo "Could not find filemap";
            exit();
        }

        $content = file_get_contents($mapFilename);
        return explode("\n",$content);

    }

    private function processFolder($folderPath,$fileMap)
    {

        echo "Process folder: ".$folderPath."\r\n";

        $fileList = $this->loadFilesFromFolder($folderPath);
        $removeFiles = 0;
        $removeDiskspace = 0;

        foreach($fileList as $index => $file) {

            $isMatch = in_array($file["filematch"],$fileMap);

            echo "Checking file: ".$file["filematch"]." - ".($isMatch ? "KEEP" : "DELETE")."\r\n";
            if(in_array($file["filematch"],$fileMap)) {
                $removeFiles++;
                $removeDiskspace += $file["size"];

                // Remove file
                if(file_exists($file["path"])) {
                    if(!unlink($file["path"])) {
                        echo "Could not remove file - error in unlink: ".$file["filematch"]." - ".$file["path"]."\r\n";
                    }
                } else {
                    echo "Could not remove file - not file: ".$file["filematch"]." - ".$file["path"]."\r\n";
                }

            }

        }

        echo "Removing ".$removeFiles." out of ".count($fileList)." for ".($removeDiskspace/1024/1024)." Mb\r\n";

    }

    private function loadFilesFromFolder($folder) {

        $notFile = 0;
        $today = 0;

        echo "Looking for files:\r\n";

        if ($handle = opendir($folder)) {

            $files = array();

            while (false !== ($file = readdir($handle))) {

                if ($file != "." && $file != "..") {
                    $path = $folder . $file;
                    if (is_file($path)) {

                        // Hent dato for sidste ændring af filen
                        $last_modified = filemtime($path);
                        $todayDate = date("Y-m-d");
                        $changeDate = date("Y-m-d", $last_modified);

                        if ($changeDate != $todayDate) {
                            $files[] = array(
                                "file" => $file,
                                "path" => $path,
                                "size" => filesize($path),
                                "time" => filemtime($path),
                                "filematch" => trim(strtolower(pathinfo($file, PATHINFO_FILENAME)))
                            );
                        } else {
                            $today++;
                        }

                    } else {
                        $notFile++;
                    }
                }

            }

            closedir($handle);

            //foreach ($files as $file) {
            //    echo $file['navn'] . " - " . $file['sti'] . " - " . $file['stoerrelse'] . " bytes - " . date("d.m.Y H:i:s", $file['modified']) . "\r\n";
           // }


        }

        echo "Found ".count($files).", ".$notFile." was not files, ".$today." was from today\r\n";
        return $files;
    }

    private function getImageMapFromPresentMedia($imageMap) {

        $duplicates = 0;
        $notSet = 0;

        // Load present_media
        $presentMediaList = \PresentMedia::find_by_sql('select media_path from present_media group by media_path');
        echo "Found presentmedia: ".count($presentMediaList)."\r\n";

        foreach($presentMediaList as $pm) {
            $filepath = $pm->media_path;
            $filename = $this->pathToFilename($filepath);

            if($filename == "") {
                $notSet++;
            } else if(in_array($filename, $imageMap)) {
                $duplicates++;
            } else {
                $imageMap[] = $filename;
            }

        }

        echo "Done, loaded: ".count($imageMap)." in map, ".$duplicates." duplicates, ".$notSet." not set\r\n";
        return $imageMap;

    }

    private function getImageMapFromPresentModel($fileMap) {

        $duplicates = 0;
        $notSet = 0;

        // Load present_model
        $presentModelList= \PresentModel::find_by_sql('select media_path from present_model group by media_path');
        echo "Found present model: ".count($presentModelList)."\r\n";

        foreach($presentModelList as $pm) {

            $filepath = $pm->media_path;
            $filename = $this->pathToFilename($filepath);

            if($filename == "") {
                $notSet++;
            } else if(in_array($filename, $fileMap)) {
                $duplicates++;
            } else {
                $fileMap[] = $filename;
            }
        }

        echo "Done, loaded: ".count($fileMap)." in map, ".$duplicates." duplicates, ".$notSet." not set\r\n";
        return $fileMap;

    }

    private function getImageMapFromPresent($fileMap) {

        $duplicates = 0;
        $notSet = 0;

        // Present
        $presentList = \PresentModel::find_by_sql('select pt_img, pt_img_small from present group by pt_img, pt_img_small');
        echo "Found present: ".count($presentList)."\r\n";

        foreach($presentList as $present) {

            $filepath = $present->pt_img;
            $filename = $this->pathToFilename($filepath);

            if($filename == "") {
                $notSet++;
            } else if(in_array($filename, $fileMap)) {
                $duplicates++;
            } else {
                $fileMap[] = $filename;
            }

            $filepath = $present->pt_img_small;
            $filename = $this->pathToFilename($filepath);

            if($filename == "") {
                $notSet++;
            } else if(in_array($filename, $fileMap)) {
                $duplicates++;
            } else {
                $fileMap[] = $filename;
            }
        }

        echo "Done, loaded: ".count($fileMap)." in map, ".$duplicates." duplicates, ".$notSet." not set\r\n";
        return $fileMap;

    }

    private function getImageMap() {
        $fileMap = array("noimg");
        $fileMap = $this->getImageMapFromPresentMedia($fileMap);
        $fileMap = $this->getImageMapFromPresentModel($fileMap);
        $fileMap = $this->getImageMapFromPresent($fileMap);
        file_put_contents($this->getImageMapFile(), implode("\n", $fileMap));
        return $fileMap;
    }

    private function getImageMapFile() {
        return "/var/www/backend/public_html/gavefabrikken_backend/units/tools/imagecleanup/files.txt";
    }

    private function pathToFilename($path) {

        if($path == null || trim($path) == "") return "";

        $folderParts = explode("/", $path);
        $filename = $folderParts[count($folderParts)-1];
        if(trim($filename) == "" || trim($filename) == "###") return "";

        $filenameparts = explode(".",$filename);
        if(count($filenameparts) == 1) return trim(strtolower($filename));

        array_pop($filenameparts);
        return trim(strtolower(implode(".",$filenameparts)));
    }

}

$controller = new Controller();
$controller->run();



