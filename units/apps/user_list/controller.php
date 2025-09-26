<?php

namespace GFUnit\apps\user_list;
use GFBiz\units\UnitController;

class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * Get all system users
     */
    public function getAll()
    {
        $sql = "SELECT id, name, username, userlevel, active, deleted, last_login FROM system_user WHERE deleted = 0";
        $users = \Dbsqli::getSql2($sql);
        echo json_encode(array("status" => 1, "data" => $users));
    }

    /**
     * Get a single system user by ID
     */
    public function getOne()
    {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        if ($id <= 0) {
            echo json_encode(array("status" => 0, "message" => "Invalid user ID"));
            return;
        }

        $sql = "SELECT id, name, username, userlevel, active, deleted, last_login FROM system_user WHERE id = " . $id;
        $user = \Dbsqli::getSql2($sql);

        if (count($user) > 0) {
            echo json_encode(array("status" => 1, "data" => $user[0]));
        } else {
            echo json_encode(array("status" => 0, "message" => "User not found"));
        }
    }

    /**
     * Create a new system user
     */
    public function create()
    {
        // Validate input data
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
        $userlevel = isset($_POST["userlevel"]) ? intval($_POST["userlevel"]) : 1;
        $active = isset($_POST["active"]) ? intval($_POST["active"]) : 1;

        if (empty($username)) {
            echo json_encode(array("status" => 0, "message" => "Username is required"));
            return;
        }

        if (empty($password)) {
            echo json_encode(array("status" => 0, "message" => "Password is required"));
            return;
        }

        // Check if username already exists
        $checkSql = "SELECT id FROM system_user WHERE username = '" . $this->escapeString($username) . "' AND deleted = 0";
        $existing = \Dbsqli::getSql2($checkSql);
        if (count($existing) > 0) {
            echo json_encode(array("status" => 0, "message" => "Username already exists"));
            return;
        }

        // Use SystemUser model to create user (handles password hashing)
        try {
            $userData = array(
                'name' => $name,
                'username' => $username,
                'password' => $password,
                'userlevel' => $userlevel,
                'active' => $active,
                'deleted' => 0
            );

            $user = SystemUser::createSystemUser($userData);

            if ($user && $user->id) {
                echo json_encode(array("status" => 1, "message" => "User created successfully", "id" => $user->id));
            } else {
                echo json_encode(array("status" => 0, "message" => "Failed to create user"));
            }
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Error creating user: " . $e->getMessage()));
        }
    }

    /**
     * Update an existing system user
     */
    public function update()
    {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        if ($id <= 0) {
            echo json_encode(array("status" => 0, "message" => "Invalid user ID"));
            return;
        }

        // Check if user exists
        $sql = "SELECT * FROM system_user WHERE id = " . $id . " AND deleted = 0";
        $existingUser = \Dbsqli::getSql2($sql);

        if (count($existingUser) === 0) {
            echo json_encode(array("status" => 0, "message" => "User not found"));
            return;
        }

        // Validate input data
        $name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
        $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : "";
        $userlevel = isset($_POST["userlevel"]) ? intval($_POST["userlevel"]) : 1;
        $active = isset($_POST["active"]) ? intval($_POST["active"]) : 1;

        if (empty($username)) {
            echo json_encode(array("status" => 0, "message" => "Username is required"));
            return;
        }

        // Check if username already exists for another user
        $checkSql = "SELECT id FROM system_user WHERE username = '" . $this->escapeString($username) . "' AND id != " . $id . " AND deleted = 0";
        $existing = \Dbsqli::getSql2($checkSql);
        if (count($existing) > 0) {
            echo json_encode(array("status" => 0, "message" => "Username already exists"));
            return;
        }

        // Use SystemUser model to update user
        try {
            $userData = array(
                'id' => $id,
                'name' => $name,
                'username' => $username,
                'userlevel' => $userlevel,
                'active' => $active
            );

            // Only update password if provided
            if (!empty($password)) {
                $userData['password'] = $password;
            }

            $user = SystemUser::updateSystemUser($userData);

            if ($user) {
                echo json_encode(array("status" => 1, "message" => "User updated successfully"));
            } else {
                echo json_encode(array("status" => 0, "message" => "Failed to update user"));
            }
        } catch (Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Error updating user: " . $e->getMessage()));
        }
    }

    /**
     * Delete a system user (soft delete)
     */
    public function delete()
    {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        if ($id <= 0) {
            echo json_encode(array("status" => 0, "message" => "Invalid user ID"));
            return;
        }

        // Check if user exists
        $sql = "SELECT id FROM system_user WHERE id = " . $id . " AND deleted = 0";
        $user = \Dbsqli::getSql2($sql);

        if (count($user) === 0) {
            echo json_encode(array("status" => 0, "message" => "User not found"));
            return;
        }

        // Soft delete the user
        $sql = "UPDATE system_user SET deleted = 1 WHERE id = " . $id;
        $result = \Dbsqli::setSql2($sql);

        if ($result) {
            echo json_encode(array("status" => 1, "message" => "User deleted successfully"));
        } else {
            echo json_encode(array("status" => 0, "message" => "Failed to delete user"));
        }
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