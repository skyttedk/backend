<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );
// Controller shop
// Date created  Sun, 03 Apr 2016 21:00:47 +0200
// Created by Bitworks
class copyShopController Extends baseController
{

    public function Index()
    {


    }


    public function setT(){
        echo "sda";
        echo $_SERVER["HTTP_AUTHORIZATION"];
        $token = $this->registry->jwt->encode(['user_id2' => 1223]);
        $payload = $this->registry->jwt->decode($token);
        print_R($payload);



    }



    public function copyPresents()
    {
        $source = $_POST["sourceShopID"];
        $target = $_POST["targetShopID"];

        if($source == "8483"){
            //  $this->syncPresentOrder($source, $target);
            // $this->syncPresentPrices($source, $target);
            //$this->syncChildPresents($source, $target);
            //$this->syncChildPresentationGroups($source, $target); // Tilføjet ny funktion
            //  die("top slut");
        }

        try {
            // Sikkerhedsvalidering (tilføj mere efter behov)
            if (!is_numeric($source) || !is_numeric($target)) {
                response::error("Ugyldige shop ID'er");
                return;
            }

            // Hent copy_of værdier fra kildeshoppen, som ikke findes i målshoppen
            $sourceCopyOf = Present::find('all', array(
                'select' => 'DISTINCT copy_of',
                'conditions' => array(
                    'shop_id = ? AND copy_of NOT IN (SELECT copy_of FROM present WHERE shop_id = ?)',
                    $source, $target
                )
            ));

            // Kør 'run' funktionen for hver unik copy_of værdi
            foreach ($sourceCopyOf as $present) {
                $this->doCopyPresents($target,$present->copy_of);
            }

            // Return correct JSON format that JavaScript expects
            $response = [
                "status" => "1",
                "message" => "Overførsel gennemført",
                "data" => ["copied_presents" => count($sourceCopyOf)]
            ];

            echo json_encode($response);

        } catch (Exception $e) {
            // Return error in correct format
            $response = [
                "status" => "0",
                "message" => "Fejl: " . $e->getMessage()
            ];

            echo json_encode($response);
        }
    }
    private function doCopyPresents($targetShopID,$presentID){
        $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=present/createUnikPresent_v2';

        // The data to be sent in the POST request
        $data = [
            'present_id' => $presentID,
            'shop_id' => $targetShopID
        ];

        // Initialize cURL session
        $ch = curl_init($url);

        // Set the cURL options
        curl_setopt($ch, CURLOPT_POST, true);                // Set POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Set the POST fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);      // Return the response as a string

        // Execute the POST request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            // Display the response
 //           echo 'Response:' . $response;
        }

        // Close the cURL session
        curl_close($ch);
    }

public function syncPresentOrder()
{
          $sourceShopID = $_POST["sourceShopID"];
          $targetShopID = $_POST["targetShopID"];
    try {
        // Hent alle gaver fra kilde-shoppen med deres shop_present info
        $sql = "SELECT p.*, sp.index_, sp.active, sp.properties
                FROM present p
                JOIN shop_present sp ON p.id = sp.present_id
                WHERE sp.shop_id = ?
                AND sp.is_deleted = 0
                AND p.deleted = 0
                ORDER BY sp.index_ ASC";

        $sourcePresents = Present::find_by_sql($sql, array($sourceShopID));

        if (empty($sourcePresents)) {
            echo "Ingen gaver fundet i kilde-shop: " . $sourceShopID;
            return false;
        }

        // For hver gave i kilde-shoppen, find og opdater den tilsvarende gave i target-shoppen
        foreach ($sourcePresents as $sourcePresent) {
            // Find den tilsvarende gave i target shop
            $targetPresent = Present::find('first', array(
                'conditions' => array(
                    'shop_id = ? AND copy_of = ? AND deleted = 0',
                    $targetShopID,
                    $sourcePresent->copy_of
                )
            ));

            if ($targetPresent) {
                // Brug plain SQL med Dbsqli::getSql2
            echo    $updateSql = "UPDATE shop_present
                             SET index_ = " . intval($sourcePresent->index_) . ",
                                 active = " . intval($sourcePresent->active) . "
                             WHERE shop_id = " . intval($targetShopID) . "
                             AND present_id = " . intval($targetPresent->id);

                $result = Dbsqli::setSql2($updateSql);

                if ($result) {
                    echo "Opdateret present ID: " . $targetPresent->id . " med index: " . $sourcePresent->index_ . "\n";
                }

            }
        }

        // Log resultat
        echo "Rækkefølge synkroniseret fra shop $sourceShopID til shop $targetShopID";
        return true;

    } catch (Exception $e) {
        echo "Fejl ved synkronisering af rækkefølge: " . $e->getMessage();
        return false;
    }
}

public function syncPresentPrices()
{
      $sourceShopID = $_POST["sourceShopID"];
      $targetShopID = $_POST["targetShopID"];

    try {
        // Hent alle gaver fra kilde-shoppen med deres pt_price felter
        $sql = "SELECT p.id, p.copy_of, p.pt_price, p.pt_price_no, p.pt_price_se
                FROM present p
                WHERE p.shop_id = ?
                AND p.deleted = 0
                ORDER BY p.id ASC";

        $sourcePresents = Present::find_by_sql($sql, array($sourceShopID));

        if (empty($sourcePresents)) {
            echo "Ingen gaver fundet i kilde-shop: " . $sourceShopID;
            return false;
        }

        // For hver gave i kilde-shoppen, find og opdater den tilsvarende gave i target-shoppen
        foreach ($sourcePresents as $sourcePresent) {
            // Find den tilsvarende gave i target shop
            $targetPresent = Present::find('first', array(
                'conditions' => array(
                    'shop_id = ? AND copy_of = ? AND deleted = 0',
                    $targetShopID,
                    $sourcePresent->copy_of
                )
            ));

            if ($targetPresent) {
                // Håndter varchar pris felter - brug mysql_real_escape_string eller addcslashes for JSON
                $ptPrice = ($sourcePresent->pt_price !== null) ? "'" . addcslashes($sourcePresent->pt_price, "'\\") . "'" : 'NULL';
                $ptPriceNo = ($sourcePresent->pt_price_no !== null) ? "'" . addcslashes($sourcePresent->pt_price_no, "'\\") . "'" : 'NULL';
                $ptPriceSe = ($sourcePresent->pt_price_se !== null) ? "'" . addcslashes($sourcePresent->pt_price_se, "'\\") . "'" : 'NULL';

                // Brug plain SQL med Dbsqli::getSql2
                 $updateSql = "UPDATE present
                             SET pt_price = " . $ptPrice . ",
                                 pt_price_no = " . $ptPriceNo . ",
                                 pt_price_se = " . $ptPriceSe . "
                             WHERE id = " . intval($targetPresent->id) . " and shop_id=".$targetShopID;

                $result = Dbsqli::setSql2($updateSql);

                if ($result) {
                    echo "Opdateret priser for present ID: " . $targetPresent->id . "\n";
                } else {
                    echo "Fejl ved opdatering af present ID: " . $targetPresent->id . "\n";
                    echo "SQL: " . $updateSql . "\n";
                }

            }
        }

        // Log resultat
        echo "Priser synkroniseret fra shop $sourceShopID til shop $targetShopID";
        return true;

    } catch (Exception $e) {
        echo "Fejl ved synkronisering af priser: " . $e->getMessage();
        return false;
    }
}
    public function syncChildPresents()
    {
        $sourceShopID = $_POST["sourceShopID"];
        $targetShopID = $_POST["targetShopID"];
        try {
            // Find alle child gaver (shop_id er negativt og pchild != 0)
            $sql = "SELECT p.*, parent.copy_of as parent_copy_of
                FROM present p
                JOIN present parent ON p.pchild = parent.id
                WHERE p.shop_id = ?
                AND p.pchild != 0
                AND p.deleted = 0";

            $childPresents = Present::find_by_sql($sql, array(-$sourceShopID));

            if (empty($childPresents)) {
                error_log("Ingen child gaver fundet i kilde-shop: " . $sourceShopID);
                return true; // Ikke en fejl hvis der ikke er child gaver
            }

            $baseUrl = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php';

            foreach ($childPresents as $childPresent) {
                // Find parent present i target shop baseret på copy_of
                $targetParent = Present::find('first', array(
                    'conditions' => array(
                        'shop_id = ? AND copy_of = ? AND deleted = 0',
                        $targetShopID,
                        $childPresent->parent_copy_of
                    )
                ));

                if (!$targetParent) {
                    continue;
                }

                // Check if child present already exists in target shop
                $existingChild = Present::find('first', array(
                    'conditions' => array(
                        'shop_id = ? AND copy_of = ? AND pchild = ? AND deleted = 0',
                        -$targetShopID,  // Child presents have negative shop_id
                        $childPresent->copy_of,
                        $targetParent->id
                    )
                ));

                if ($existingChild) {
                    error_log("Child present allerede eksisterer i target shop: " . $childPresent->copy_of . " (skipping)");
                    continue;
                }

                // Step 1: Tilføj child til target shop via addChildToPresentState1
                $state1Data = $this->callAPI($baseUrl . '?rt=present/addChildToPresentState1', array(
                    'present_id' => $childPresent->copy_of,
                    'shop_id' => $targetShopID,
                    'parentPresent_id' => $targetParent->id
                ));

                if ($state1Data && isset($state1Data['data']['present'][0]['id'])) {
                    $newChildPresentId = $state1Data['data']['present'][0]['id'];

                    // Step 2: Opdater shop_id til negativt via addChildToPresentState2
                    $state2Data = $this->callAPI($baseUrl . '?rt=present/addChildToPresentState2', array(
                        'present_id' => $newChildPresentId,
                        'shop_id' => $targetShopID
                    ));

                    if ($state2Data) {
                        error_log("Child present synkroniseret: " . $childPresent->id . " -> " . $newChildPresentId);
                    } else {
                        error_log("Fejl ved step 2 for child present: " . $newChildPresentId);
                    }
                } else {
                    error_log("Fejl ved tilføjelse af child present: " . $childPresent->id);
                }
            }

            return true;

        } catch (Exception $e) {
            error_log("Fejl ved synkronisering af child gaver: " . $e->getMessage());
            return false;
        }
    }

public function syncChildPresentationGroups()
{
    $sourceShopID = $_POST["sourceShopID"];
    $targetShopID = $_POST["targetShopID"];


    try {
        // Find alle child gaver fra target shop først
        $sql = "SELECT p.id, p.copy_of, p.pchild, parent.copy_of as parent_copy_of
                FROM present p
                JOIN present parent ON p.pchild = parent.id
                WHERE p.shop_id = " . intval(-$targetShopID) . "
                AND p.pchild != 0
                AND p.deleted = 0";

        $targetChildPresents = Present::find_by_sql($sql);

        if (empty($targetChildPresents)) {
            echo "Ingen child gaver fundet i target-shop: " . $targetShopID . "\n";
            return true; // Ikke en fejl hvis der ikke er child gaver
        }

        echo "Fandt " . count($targetChildPresents) . " child gaver i target shop\n";

        $processedParents = array(); // Hold styr på hvilke parent presents vi allerede har behandlet

        foreach ($targetChildPresents as $targetChild) {
            // Skip hvis vi allerede har behandlet denne parent present
            if (in_array($targetChild->pchild, $processedParents)) {
                continue;
            }

            // Tilføj denne parent til listen over behandlede
            $processedParents[] = $targetChild->pchild;

            // Find den tilsvarende source child present baseret på copy_of
            $sourceChildSql = "SELECT p.id, p.copy_of, p.pchild, parent.id as parent_id
                              FROM present p
                              JOIN present parent ON p.pchild = parent.id
                              WHERE p.shop_id = " . intval(-$sourceShopID) . "
                              AND p.copy_of = " . intval($targetChild->copy_of) . "
                              AND p.deleted = 0
                              AND p.pchild != 0
                              LIMIT 1";

            $sourceChildResult = Dbsqli::getSql2($sourceChildSql);

            if (empty($sourceChildResult)) {
                echo "Source child present ikke fundet for copy_of=" . $targetChild->copy_of . "\n";
                continue;
            }

            $sourceChild = $sourceChildResult[0];

            // Nu skal vi finde presentation_group data baseret på source child's parent ID
            // group_id refererer til parent present ID
            $sourcePGSql = "SELECT * FROM presentation_group
                           WHERE group_id = " . intval($sourceChild['parent_id']) . "
                           AND active = 1";

            $sourcePresentationGroups = Dbsqli::getSql2($sourcePGSql);

            if (empty($sourcePresentationGroups)) {
                echo "Ingen presentation_group data fundet for source parent present: " . $sourceChild['parent_id'] . "\n";
                continue;
            }

            // For hver presentation_group record fra source parent present
            foreach ($sourcePresentationGroups as $sourcePG) {
                // Tjek om der allerede eksisterer en record for target child's parent
                $existingPGSql = "SELECT id FROM presentation_group
                                 WHERE group_id = " . intval($targetChild->pchild) . "
                                 LIMIT 1";

                $existingPG = Dbsqli::getSql2($existingPGSql);

                if (!empty($existingPG)) {
                    // Opdater eksisterende record
                    $pricesDa = ($sourcePG['prices_da'] !== null) ? "'" . addcslashes($sourcePG['prices_da'], "'\\") . "'" : 'NULL';
                    $pricesSv = ($sourcePG['prices_sv'] !== null) ? "'" . addcslashes($sourcePG['prices_sv'], "'\\") . "'" : 'NULL';
                    $pricesNo = ($sourcePG['prices_no'] !== null) ? "'" . addcslashes($sourcePG['prices_no'], "'\\") . "'" : 'NULL';
                    $orderIndex = ($sourcePG['order_index'] !== null) ? "'" . addcslashes($sourcePG['order_index'], "'\\") . "'" : 'NULL';

                    $updateSql = "UPDATE presentation_group
                                 SET type = " . intval($sourcePG['type']) . ",
                                     active = " . intval($sourcePG['active']) . ",
                                     prices_da = " . $pricesDa . ",
                                     prices_sv = " . $pricesSv . ",
                                     prices_no = " . $pricesNo . ",
                                     order_index = " . $orderIndex . ",
                                     localisation = " . intval($sourcePG['localisation']) . "
                                 WHERE group_id = " . intval($targetChild->pchild);

                    echo $updateSql . ";\n";
                    Dbsqli::setSql2($updateSql);
                } else {
                    // Indsæt ny record for target parent present
                    $pricesDa = ($sourcePG['prices_da'] !== null) ? "'" . addcslashes($sourcePG['prices_da'], "'\\") . "'" : 'NULL';
                    $pricesSv = ($sourcePG['prices_sv'] !== null) ? "'" . addcslashes($sourcePG['prices_sv'], "'\\") . "'" : 'NULL';
                    $pricesNo = ($sourcePG['prices_no'] !== null) ? "'" . addcslashes($sourcePG['prices_no'], "'\\") . "'" : 'NULL';
                    $orderIndex = ($sourcePG['order_index'] !== null) ? "'" . addcslashes($sourcePG['order_index'], "'\\") . "'" : 'NULL';

                    $insertSql = "INSERT INTO presentation_group
                                 (group_id, type, active, prices_da, prices_sv, prices_no, order_index, localisation)
                                 VALUES (" . intval($targetChild->pchild) . ",
                                        " . intval($sourcePG['type']) . ",
                                        " . intval($sourcePG['active']) . ",
                                        " . $pricesDa . ",
                                        " . $pricesSv . ",
                                        " . $pricesNo . ",
                                        " . $orderIndex . ",
                                        " . intval($sourcePG['localisation']) . ")";

                    echo $insertSql . ";\n";
                    Dbsqli::setSql2($insertSql);
                }
            }
        }

        echo "Presentation groups synkroniseret fra shop $sourceShopID til shop $targetShopID\n";
        return true;

    } catch (Exception $e) {
        echo "Fejl ved synkronisering af presentation groups: " . $e->getMessage() . "\n";
        return false;
    }
}






    /**
     * Helper funktion til at kalde API endpoints
     * @param string $url API endpoint URL
     * @param array $data POST data
     * @return array|false Response data eller false ved fejl
     */
    private function callAPI($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            error_log('CURL Error: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("HTTP Error $httpCode for URL: $url");
            return false;
        }

        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            return false;
        }

        if (isset($responseData['status']) && $responseData['status'] != 1) {
            error_log("API Error response: " . $response);
            return false;
        }

        return $responseData;
    }
}