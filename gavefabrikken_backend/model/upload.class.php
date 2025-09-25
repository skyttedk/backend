<?php
include ("thirdparty/php-image-magician/php_image_magician.php");

class Upload {
    public static function rawFile($source,$targetFile) {

        $filename = self::generateRandomString(20).$source['CSMCfile']['name'];
        $tempFile = $source['CSMCfile']['tmp_name'];
        $targetPath = $targetFile.$filename;
        try {
            move_uploaded_file($tempFile, $targetPath);
            return $targetPath;
        } catch (Exception $e) {
            return false;
        }


    }


    public static function uploadFileGifts($source, $storeFolder, $newWidth = "") {
        $ration = null;
        $ds = "/";
        $filename = self::generateRandomString(20);
        $fileRandom = $filename;
        if (!empty($source)) {

            $prodfolder = "gavefabrikken_backend/views/media/user";
            $storeSmallFolder = "gavefabrikken_backend/views/media/small";


            $tempFile = $source['file']['tmp_name'];
            $targetPath = dirname(__FILE__) . $ds . $storeFolder . $ds;

            $targetPathSmall = $_SERVER["DOCUMENT_ROOT"]  . $ds . $storeSmallFolder . $ds;
            $targetPathProd = $_SERVER["DOCUMENT_ROOT"]  . $ds . $prodfolder . $ds;

            $targetFile = $targetPath . $filename . ".jpg";
            $targetFileProd = $targetPathProd . $filename . ".jpg";
            $targetFileSmall = $targetPathSmall . $filename . "_small.jpg";

            move_uploaded_file($tempFile, $targetFile);
            self::resize_new(1000,$targetFile,$targetFileProd);
            self::resize_new(300,$targetFile,$targetFileSmall);





            /*
            if ($newWidth != "") {
                $imgInfo = getimagesize($targetFile);
                $width = $imgInfo[0];
                $height = $imgInfo[1];
                $ration = $width / $height;
                $newHeight = $newWidth / $ration;
                $magicianObj = new imageLib($targetFile);
                $magicianObjSmall = new imageLib($targetFile);
                $magicianObj->resizeImage($newWidth, $newHeight);
                $magicianObj->saveImage($targetFile, 100);
                $smallHeight = 300 / $ration;
                $magicianObjSmall->resizeImage(300, $smallHeight);
                $magicianObjSmall->saveImage($targetFileSmall, 100);
            }*/
            return array("newName" => $filename, "filename" => $_FILES["file"]["name"], "ration" => $ration, "random" => $fileRandom);
        }
        else {
            return false;
        }
    }
    public static function resizeImage($imagePath, $new_width, $new_height)
    {
        $fileName = pathinfo($imagePath, PATHINFO_FILENAME);
        $fullPath = pathinfo($imagePath, PATHINFO_DIRNAME) . "/" . $fileName . "_small.jpg";
        if (file_exists($fullPath)) {
            return $fullPath;
        }

        $image = @imagecreatefromjpeg($imagePath);
        if (!$image) {
            return null;
        }
        $width = imagesx($image);
        $height = imagesy($image);
        $imageResized = imagecreatetruecolor($width, $height);
        if (!$imageResized) {
            return null;
        }
        $image = imagecreatetruecolor($width, $height);
        $imageResized = imagescale($image, $new_width, $new_height);
        touch($fullPath);
        $write = imagejpeg($imageResized, $fullPath);
        imagedestroy($imageResized);
        if (!$write) {
            return null;
        }
        return $fullPath;
    }



    public static function uploadFile($source, $storeFolder, $newWidth = "") {

        $ds = "/";
        $filename = self::generateRandomString(10);
        if (!empty($source)) {
            $tempFile = $source['file']['tmp_name'];
            $targetPath = $_SERVER["DOCUMENT_ROOT"] . $ds . $storeFolder . $ds;
            $targetFile = $targetPath . $filename . ".jpg"; //$source['file']['name'];
           // move_uploaded_file($tempFile, $targetFile);
           // $imgInfo = getimagesize($targetFile);
            self::resize_new($newWidth,$tempFile,$targetFile);

            return json_encode(array("newName" => $filename, "filename" => $_FILES["file"]["name"]));
        }
        else {
            return false;
        }
    }

    public static function uploadFileP($source, $storeFolder, $newWidth = "") {
        $ds = "/";
        $filename = self::generateRandomString(10);
     

        if (!empty($source)) {
            $tempFile = $source['file']['tmp_name'];
            $targetPath = dirname(__FILE__)."/../../fjui4uig8s8893478/";
            $targetFile = $targetPath . $filename . ".jpg"; //$source['file']['name'];
            move_uploaded_file($tempFile, $targetFile);
            $imgInfo = getimagesize($targetFile);
            /*
            if($imgInfo[0] > "2000" || $imgInfo[1] > "2000"){
                if ($newWidth != "") {
                    $imgInfo = getimagesize($targetFile);
                    $width = $imgInfo[0];
                    $height = $imgInfo[1];
                    $ration = $width / $height;
                    $newHeight = $newWidth / $ration;
                    self::resize($newWidth, $newHeight, $targetFile);

                }
            }
            */
            $imgInfo = getimagesize($targetFile);
            $width = $imgInfo[0];
            $height = $imgInfo[1];
            $ration = $width / $height;
            $newHeight = 200 / $ration;
            $smallPath = $targetPath."/small/". $filename . ".jpg";
           // self::resizeSmall(100, $newHeight, $targetFile, $smallPath);



            return json_encode(array("newName" => $filename, "filename" => $_FILES["file"]["name"]));
        }
        else {
            return false;
        }
    }

    public static function uploadPdf($source, $storeFolder, $shopId) {
        $ds = "/";
        $filename = self::generateRandomString(6).$shopId;
        if (!empty($source)) {
            $tempFile = $source['file']['tmp_name'];
            $targetPath = dirname(__FILE__)."/../../fjui4uig8s8893478/";
            $targetFile = $targetPath . $filename . ".pdf"; //$source['file']['name'];
            move_uploaded_file($tempFile, $targetFile);
            return json_encode(array("newName" => $filename, "filename" => $_FILES["file"]["name"]));
        }
        else {
            return false;
        }
    }
    public static function resizeSmall($width, $height, $path, $newPath) {
        $magicianObj = new imageLib($path);
        $magicianObj->resizeImage($width, $height);
        $magicianObj->saveImage($newPath, 75); // de 100 er kvaliteten
    }

    public static function resize($width, $height, $path) {
        $magicianObj = new imageLib($path);
        $magicianObj->resizeImage($width, $height);
        $magicianObj->saveImage($path, 100); // de 100 er kvaliteten
    }
    public static function resize_new($newWidth,$source,$target) {
        $image = @imagecreatefromjpeg($source);
        if (!$image) {
            return null;
        }
        if($newWidth == ""){
            $new_width = imagesx($image);
            $new_height = imagesy($image);


        } else {
            $width = imagesx($image);
            $height = imagesy($image);
            $ration = $width / $height;
            $new_height = $newWidth / $ration;
            $new_height = (int) $new_height;
            $new_width = $newWidth;

        }

        $imageResized = imagescale($image, $new_width, $new_height);
        $write = imagejpeg($imageResized, $target);
        imagedestroy($imageResized);
        return $write;
    }






    private static function generateRandomString($length) {
        $characters = '123456789bcdfghjklrstvxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString.time();
    }

}
?>