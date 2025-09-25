<?php

namespace GFUnit\reservation\balancecompare;

use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index() {
        
        $this->view("front");
        
    }

    public function adjustform()
    {

        if (isset($_POST['action']) && $_POST['action'] == 'sendadjust') {
            $itemNo = $_POST['item_no'] ?? '';
            $languageid = $_POST['language'] ?? '';
            $locationcode = $_POST["location"] ?? '';
            $delta = $_POST['adjustment'] ?? '';
            $note = $_POST['description'] ?? '';

            $errors = [];

            if (empty($itemNo)) {
                $errors[] = "Item no is required.";
            }
            if (empty($languageid)) {
                $errors[] = "Language ID is required.";
            }
            if (empty($locationcode)) {
                $errors[] = "Location is required.";
            }
            if (empty($delta)) {
                $errors[] = "Adjustment is required.";
            }
            if (empty($note)) {
                $errors[] = "Description is required.";
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<p style='color: red;'>$error</p>";
                }
            } else {

                $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<reservations>
    ';
                $xml .= '<reservation>
        <item_no>' . htmlspecialchars($itemNo) . '</item_no>
        <location_code>' . htmlspecialchars($locationcode) . '</location_code>
        <reservation_qty>' . htmlspecialchars($delta) . '</reservation_qty>
        <note>' . htmlspecialchars($note) . '</note>
    </reservation>
    ';
                $xml .= '
</reservations>';

                try {


                    $client = new \GFCommon\Model\Navision\OrderWS(intval($languageid));

                    $reservationResponse = $client->uploadReservationDoc($xml);
                    if($reservationResponse) {

                        if($client->getLastReservationResponse() != "OK") {
                            throw new \Exception("Reservation synced but navision responded with non ok answer: ".$client->getLastReservationResponse());
                        } else {
                             echo "<p>Order synced ok: ".$client->getLastReservationResponse()."</p>";
                        }

                    } else {
                        echo " ---- Error in nav request: ".$client->getLastError();
                        throw new \Exception("Could not upload reservation doc: ".$client->getLastError()." @ ".$e->getFile()." : ".$e->getLine()."");
                    }

                    $callId = $client->getLastCallID();
                    echo "<br></br>OK!<br>";
                    echo '<pre>' . htmlentities($xml) . '</pre>';



                } catch (\Exception $e) {
                    echo "<p style='color: red;'>FEJL: " . $e->getMessage() . " @ ".$e->getFile()." : ".$e->getLine()."</p>";
                }

            }

        }

        // Output form
        ?><form method="post" action="index.php?rt=unit/reservation/balancecompare/adjustform">
        <label for="language">Language ID:</label>
        <select id="language" name="language">
            <option value="1" <?php if(($_POST["language"] ?? 0) == 1 ) echo "selected"; ?>>Danmark</option>
            <option value="4" <?php if(($_POST["language"] ?? 0) == 4 ) echo "selected"; ?>>Norge</option>
            <option value="5" <?php if(($_POST["language"] ?? 0) == 5 ) echo "selected"; ?>>Sverige</option>
        </select><br>

        <label for="item_no">Item no:</label>
        <input type="text" id="item_no" name="item_no" value="<?php echo $_POST["item_no"] ?? ""; ?>"><br>

        <label for="item_no">Location:</label>
        <input type="text" id="location" name="location" value="<?php echo $_POST["location"] ?? ""; ?>"><br>

        <label for="adjustment">Adjustment:</label>
        <input type="text" id="adjustment" name="adjustment" value="<?php echo $_POST["adjustment"] ?? ""; ?>"><br>

        <label for="description">Description:</label>
        <input type="text" id="description" name="description" value="<?php echo $_POST["description"] ?? ""; ?>"><br>

        <input type="hidden" name="action" value="sendadjust">
        <button type="submit">Send</button>
    </form>
        <?php

    }

    private $orderWs = array();

    private function getOrderWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create order service with no nav country");
        }
        if(isset($this->orderWs[intval($countryCode)])) {
            return $this->orderWs[intval($countryCode)];
        }
        $this->orderWs[intval($countryCode)] = new \GFCommon\Model\Navision\OrderWS(intval($countryCode));
        return $this->orderWs[intval($countryCode)];
    }


    public function reservationsearch() {
        $check = new BalanceCheck($_POST["itemno"],$_POST["languageid"],$_POST["location"]);
        $check->outputBalance();
    }

    public function reservationsearchnav() {
        $check = new SearchNav($_POST["itemno"],$_POST["languageid"],$_POST["location"]);
        $check->output();
    }

    public function reservationsearchcs() {
        $check = new SearchCS($_POST["itemno"],$_POST["languageid"],$_POST["location"]);
        $check->output();
    }

}