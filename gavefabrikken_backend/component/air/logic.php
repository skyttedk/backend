<?php
set_time_limit(3000);
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sæt header til JSON output
header('Content-Type: application/json');

include("../sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

try {
    // Valider om serienummer eksisterer
    if (!isset($_POST['serialNumber'])) {
        throw new Exception('Intet serienummer modtaget');
    }

    // Valider serienummer format
    $serialNumber = filter_var($_POST['serialNumber'], FILTER_VALIDATE_INT);
    if ($serialNumber === false || strlen($_POST['serialNumber']) !== 6) {
        throw new Exception('Serienummeret skal være 6 tal');
    }

    // Tjek om airfryer eksisterer og status
    $sql = "SELECT * FROM airfryer WHERE label_id = " . $serialNumber;
    $rs = $db->get($sql);

    if (sizeof($rs["data"]) > 0) {
        // Airfryer findes i systemet
        if ($rs["data"][0]["status"] == 2) {
            // Allerede registreret
            echo json_encode([
                'status' => 'already_registered',
                'date' => date('d/m/Y H:i', strtotime($rs["data"][0]["updated_at"]))
            ]);
        } else {
            // Opdater status til returneret
            $sql = "UPDATE airfryer SET status = 2, updated_at = NOW() WHERE label_id = " . $serialNumber;
            $updateResult = $db->set($sql);

            if ($updateResult) {
                echo json_encode([
                    'status' => 'success'
                ]);
            } else {
                throw new Exception('Kunne ikke opdatere status');
            }
        }
    } else {
        // Airfryer findes ikke i systemet
        throw new Exception('Serienummer ikke fundet i systemet');
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}