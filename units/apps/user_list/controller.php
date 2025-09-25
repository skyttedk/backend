<?php

namespace GFUnit\apps\sale_profile;
use GFBiz\units\UnitController;

class Controller extends UnitController
{
    // Upload directory for images
    private $uploadDir;

    // Base URL for accessing uploaded images (for display)
    private $imageBaseUrl = 'https://presentation.gavefabrikken.dk/presentation/workers/';

    public function __construct()
    {
        parent::__construct(__FILE__);

        $this->uploadDir =  $_SERVER["DOCUMENT_ROOT"].'/presentation/workers/';

        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Get all sale profiles
     */
    public function getAll()
    {
        $lang = isset($_POST["lang"]) ? intval($_POST["lang"]) : 1;
        $sql = "SELECT * FROM presentation_sale_profile WHERE lang = " . $lang;
        $profiles = \Dbsqli::getSql2($sql);
        echo json_encode(array("status" => 1, "data" => $profiles));
    }

    /**
     * Get a single sale profile by ID
     */
    public function getOne()
    {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        if ($id <= 0) {
            echo json_encode(array("status" => 0, "message" => "Invalid profile ID"));
            return;
        }

        $sql = "SELECT * FROM presentation_sale_profile WHERE id = " . $id;
        $profile = \Dbsqli::getSql2($sql);

        if (count($profile) > 0) {
            echo json_encode(array("status" => 1, "data" => $profile[0]));
        } else {
            echo json_encode(array("status" => 0, "message" => "Profile not found"));
        }
    }

    /**
     * Create a new sale profile
     */
    public function create()
    {
        // Validate input data
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $tel = isset($_POST["tel"]) ? trim($_POST["tel"]) : "";
        $mail = isset($_POST["mail"]) ? trim($_POST["mail"]) : "";
        $lang = isset($_POST["lang"]) ? intval($_POST["lang"]) : 1;

        if (empty($name) || empty($mail)) {
            echo json_encode(array("status" => 0, "message" => "Name and email are required"));
            return;
        }

        // Handle image upload
        $imgFileName = "";
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $result = $this->handleImageUpload($_FILES['profile_image'], $name);
            if ($result === false) {
                echo json_encode(array("status" => 0, "message" => "Failed to upload image"));
                return;
            }
            $imgFileName = $result; // Just the filename

            // For debugging
            error_log("Final image filename for DB: " . $imgFileName);
        }

        // Insert new profile
        $sql = "INSERT INTO presentation_sale_profile (name, title, tel, mail, img, lang) 
                VALUES ('" . $this->escapeString($name) . "', 
                        '" . $this->escapeString($title) . "', 
                        '" . $this->escapeString($tel) . "', 
                        '" . $this->escapeString($mail) . "', 
                        '" . $this->escapeString($imgFileName) . "', 
                        " . $lang . ")";

        $result = \Dbsqli::setSql2($sql);

        // Try to get last insert ID
        $id = 0;
        $checkSql = "SELECT id FROM presentation_sale_profile 
                   WHERE name='" . $this->escapeString($name) . "' 
                   AND mail='" . $this->escapeString($mail) . "' 
                   ORDER BY id DESC LIMIT 1";
        $idResult = \Dbsqli::getSql2($checkSql);
        if (!empty($idResult) && isset($idResult[0]['id'])) {
            $id = $idResult[0]['id'];
        }

        if ($result) {
            echo json_encode(array("status" => 1, "message" => "Profile created successfully", "id" => $id));
        } else {
            echo json_encode(array("status" => 0, "message" => "Failed to create profile"));
        }
    }

    /**
     * Update an existing sale profile
     */
    public function update()
    {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        if ($id <= 0) {
            echo json_encode(array("status" => 0, "message" => "Invalid profile ID"));
            return;
        }

        // Get existing profile to check for image
        $sql = "SELECT * FROM presentation_sale_profile WHERE id = " . $id;
        $existingProfile = \Dbsqli::getSql2($sql);

        if (count($existingProfile) === 0) {
            echo json_encode(array("status" => 0, "message" => "Profile not found"));
            return;
        }

        $existingImgFileName = $existingProfile[0]['img'];
        $existingName = $existingProfile[0]['name'];

        // Validate input data
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $title = isset($_POST["title"]) ? trim($_POST["title"]) : "";
        $tel = isset($_POST["tel"]) ? trim($_POST["tel"]) : "";
        $mail = isset($_POST["mail"]) ? trim($_POST["mail"]) : "";
        $lang = isset($_POST["lang"]) ? intval($_POST["lang"]) : 1;

        if (empty($name) || empty($mail)) {
            echo json_encode(array("status" => 0, "message" => "Name and email are required"));
            return;
        }

        // Handle image upload (if provided)
        $imgFileName = $existingImgFileName;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $newImgFileName = $this->handleImageUpload($_FILES['profile_image'], $name);
            if ($newImgFileName === false) {
                echo json_encode(array("status" => 0, "message" => "Failed to upload new image"));
                return;
            }

            // If we have a new image, delete the old one
            if (!empty($existingImgFileName)) {
                $this->deleteImageFile($existingImgFileName);
            }

            $imgFileName = $newImgFileName;
        }

        // Update profile
        $sql = "UPDATE presentation_sale_profile SET 
                name = '" . $this->escapeString($name) . "', 
                title = '" . $this->escapeString($title) . "', 
                tel = '" . $this->escapeString($tel) . "', 
                mail = '" . $this->escapeString($mail) . "', 
                img = '" . $this->escapeString($imgFileName) . "', 
                lang = " . $lang . " 
                WHERE id = " . $id;

        $result = \Dbsqli::setSql2($sql);

        if ($result) {
            echo json_encode(array("status" => 1, "message" => "Profile updated successfully"));
        } else {
            echo json_encode(array("status" => 0, "message" => "Failed to update profile"));
        }
    }

    /**
     * Delete a sale profile
     */
    public function delete()
    {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        if ($id <= 0) {
            echo json_encode(array("status" => 0, "message" => "Invalid profile ID"));
            return;
        }

        // Get image path before deleting
        $sql = "SELECT img FROM presentation_sale_profile WHERE id = " . $id;
        $profile = \Dbsqli::getSql2($sql);

        if (count($profile) > 0 && !empty($profile[0]['img'])) {
            $imgFileName = $profile[0]['img'];
            $this->deleteImageFile($imgFileName);
        }

        // Delete the profile
        $sql = "DELETE FROM presentation_sale_profile WHERE id = " . $id;
        $result = \Dbsqli::setSql2($sql);

        if ($result) {
            echo json_encode(array("status" => 1, "message" => "Profile deleted successfully"));
        } else {
            echo json_encode(array("status" => 0, "message" => "Failed to delete profile"));
        }
    }

    /**
     * Handle image upload and return a very short filename
     */
    private function handleImageUpload($file, $name = '')
    {
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        // Get file extension
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            $extension = 'jpg';
        }

        // Generate a very short filename (max 20 chars)
        // Use initials from name + timestamp
        $initials = $this->getInitials($name);
        $fileName = $initials . time() . '.' . $extension;

        // Make sure the filename is not too long
        if (strlen($fileName) > 20) {
            $fileName = substr($fileName, 0, 16) . '.' . $extension;
        }

        // For debugging
        error_log("Generated filename: " . $fileName);

        $targetPath = $this->uploadDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $fileName; // Just the short filename
        }

        return false;
    }

    /**
     * Extract initials from a name
     */
    private function getInitials($name)
    {
        $words = preg_split('/\s+/', $name);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word[0])) {
                $initials .= strtoupper($word[0]);
            }
        }

        // Limit to 3 characters
        return substr($initials, 0, 3);
    }

    /**
     * Delete an image file by its filename
     */
    private function deleteImageFile($imgFileName)
    {
        if (empty($imgFileName)) {
            return false;
        }

        $filePath = $this->uploadDir . $imgFileName;

        // Check if file exists and delete it
        if (file_exists($filePath)) {
            @unlink($filePath);
            return true;
        }

        return false;
    }

    /**
     * Escape string for SQL query
     */
    private function escapeString($str)
    {
        // Use PHP's built-in addslashes function as a fallback
        return addslashes($str);
    }
}