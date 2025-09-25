<?php

namespace GFUnit\jobs\usersync;
use GFBiz\units\UnitController;
use GFUnit\navision\syncprivatedelivery\PrivateDeliverySync;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {
        echo "NO ACTION HERE!";
    }

    public function syncsystemusers()
    {

        \GFCommon\DB\CronLog::startCronJob("SystemUserSync");

        $db_current = \GFConfig::DB_DATABASE;
        $db_previous = ["gavefabrikken2022", "gavefabrikken2023","gavefabrikken2024"];

        $this->logMessage("Starting user sync");
        $this->logMessage("- Sync from $db_current");
        $this->logMessage("- Sync to " . implode(", ", $db_previous));

        try {
            
            // Connect
            $conn = new \PDO("mysql:host=".\GFConfig::DB_HOST.";dbname=".\GFConfig::DB_DATABASE, \GFConfig::DB_USERNAME, \GFConfig::DB_PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->logMessage("Connected to database $db_current", 'debug');

            // For each previous database
            foreach ($db_previous as $db) {
                try {
                    
                    $this->logMessage("Start syncing to $db");

                    // Truncate tables in previous databases
                    $conn->exec("TRUNCATE TABLE $db.system_user");
                    $conn->exec("TRUNCATE TABLE $db.user_permission");
                    $conn->exec("TRUNCATE TABLE $db.user_tab_permission");
                    $this->logMessage("TRUNCATE TABLE $db.system_user","SQL");
                    $this->logMessage("TRUNCATE TABLE $db.user_permission","SQL");
                    $this->logMessage("TRUNCATE TABLE $db.user_tab_permission","SQL");
                    $this->logMessage("Truncated tables in $db", 'debug');

                    // Copy data from current database to previous databases
                    $conn->exec("INSERT INTO $db.system_user SELECT * FROM $db_current.system_user");
                    $conn->exec("INSERT INTO $db.user_permission SELECT * FROM $db_current.user_permission");
                    $conn->exec("INSERT INTO $db.user_tab_permission SELECT * FROM $db_current.user_tab_permission");
                    $this->logMessage("INSERT INTO $db.system_user SELECT * FROM $db_current.system_user","SQL");
                    $this->logMessage("INSERT INTO $db.user_permission SELECT * FROM $db_current.user_permission","SQL");
                    $this->logMessage("INSERT INTO $db.user_tab_permission SELECT * FROM $db_current.user_tab_permission","SQL");

                    $this->logMessage("Copied data to $db", 'debug');

                } catch (Exception $e) {

                    $errorMessage = "Error copying data to $db: " . $e->getMessage();
                    $this->sendError("Database Sync Error", $errorMessage);
                    $this->logMessage($errorMessage, 'error');
                    echo "ERROR";
                    exit();
                }
            }

            $this->logMessage("Data successfully copied to previous databases.");

        } catch (\Exception $e) {

            $errorMessage = "Connection failed: " . $e->getMessage();
            $this->sendError("Database Connection Error", $errorMessage);
            $this->logMessage($errorMessage, 'error');

            \GFCommon\DB\CronLog::endCronJob(1,$e->getMessage());
            echo "ERROR!";
            return;

        }

        $conn = null;

        $this->logMessage("User sync is finished!");
        \GFCommon\DB\CronLog::endCronJob(1,"OK");
        echo "OK!";
    }

    private function sendError($subject, $details) {
        mailgf("sc@interactive.dk", "Usersync: ".$subject, $details);
    }

    private function logMessage($message, $type = 'info') {
       //echo "[$type] $message<br>";
    }


}