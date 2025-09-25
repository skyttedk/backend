<?php
// Controller SystemUser
// Date created  Tue, 12 Apr 2016 21:01:51 +0200
// Created by Bitworks
class SystemUserController Extends baseController
{
    public function Index()
    {
          if(\GFCommon\Model\Access\BackendPermissions::isAdmin()) {
        $this->registry->template->systemUsers = SystemUser::all();
        $this->registry->template->show('system_user');
        }
        else {
            $this->registry->template->show('system_nav');
        }
    }

    public function create()
    {
        $systemuser = new SystemUser();
        $data['name'] = $_POST['name'];
        $data['username'] = $_POST['username'];
        $data['password'] = $_POST['password'];
        $data['userlevel'] = $_POST['userlevel'];
        $data['active'] = $_POST['active'];
        $data['salespersoncode'] = $_POST['salespersoncode'];
        $systemuser = SystemUser::createSystemUser($data);
        response::success(make_json("systemuser", $systemuser));
        //$this->Index();
    }

    public function read()
    {
        $systemuser = SystemUser::readSystemUser($_POST['id']);
        response::success(make_json("systemuser", $systemuser));
    }

    public function update()
    {

        $systemuser = new SystemUser();
        $data['id'] = $_POST['id'];

        $data['name'] = $_POST['name'];
        $data['username'] = $_POST['username'];
        $data['password'] = $_POST['password'];
        $data['userlevel'] = $_POST['userlevel'];
        $data['active'] = $_POST['active'];
        $data['salespersoncode'] = $_POST['salespersoncode'];

        $systemuser = SystemUser::updateSystemUser($data);
        response::success(make_json("systemuser", $systemuser));
    }

    public function delete()
    {
        $data['id'] = $_POST['id'];
        $systemuser = SystemUser::deleteSystemUser($data);
        response::success(make_json("systemuser", $systemuser));
    }

    //Create Variations of readAll
    public function readAll()
    {
        $systemusers = SystemUser::all();
        //$options = array('only' => array('id', 'name', 'username', 'admin', 'active'));
        $options = array();
        response::success(make_json("systemusers", $systemusers, $options));
    }
//---------------------------------------------------------------------------------------
// Custom Controller Actions
//---------------------------------------------------------------------------------------

    public function changeLanguage()
    {
        // Get user ID and language from POST data
        $userId = $_POST['user_id'];
        $newLanguage = $_POST['language'];
        
        // Security check: Only allow user ID 340 to change languages in production
        if($userId != 340) {
            $result = array(
                "success" => false,
                "error" => "Access denied",
                "message" => "Language change not allowed for this user"
            );
            header('Content-Type: application/json');
            echo json_encode($result);
            return;
        }
        
        // First, check if we need to backup the original language
        $sqlCheck = "SELECT language, language_original FROM system_user WHERE id = ".$userId;
        $checkResult = \Dbsqli::getSql2($sqlCheck);
        
        $backupOriginal = false;
        if($checkResult && count($checkResult) > 0) {
            $currentData = $checkResult[0];
            $currentLanguage = $currentData['language'];
            $languageOriginal = $currentData['language_original'];
            
            // If language_original is 0 or null, backup current language
            if($languageOriginal == 0 || $languageOriginal == null || $languageOriginal == '') {
                $sqlBackup = "UPDATE system_user SET language_original = ".$currentLanguage." WHERE id = ".$userId;
                $backupResult = \Dbsqli::setSql2($sqlBackup);
                $backupOriginal = true;
            }
        }
        
        // Update current language using direct SQL
        $sql2 = "UPDATE system_user SET language = ".$newLanguage." WHERE id = ".$userId;
        $u2 = \Dbsqli::setSql2($sql2);
        
        // Return simple JSON response
        $result = array(
            "success" => true,
            "user_id" => $userId,
            "language" => $newLanguage,
            "original_backed_up" => $backupOriginal,
            "message" => "Language updated successfully" . ($backupOriginal ? " (original language backed up)" : "")
        );
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}

?>

