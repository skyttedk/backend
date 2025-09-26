<?php

namespace GFUnit\vlager\lagerfront;
use GFBiz\units\UnitController;
use GFUnit\vlager\utils\Template;
use GFUnit\vlager\utils\VLager;

class Dispatcher
{

    private $baseUrl;
    private $isPublic = true;

    private $vlagerid  = null;
    private $vlager = null;

    public function __construct()
    {

    }

    public function setPublic($url) {
        $this->baseUrl = $url;
        $this->isPublic = true;
    }

    public function setSystemUser($url,$vlagerid) {
        $this->baseUrl = $url;
        $this->isPublic = false;
        $this->vlagerid = intvalgf($vlagerid);
    }

    public function dispatch()
    {

        // Authorize user
        $this->authorize();

        // Login if vlager not set
        if($this->vlager == null) {
            $this->loginpage();
            exit();
        }

        if(($_POST["action"] ?? "") == "updatestock") {

            try {
                // Get data
                $itemno = $_POST["productNumber"];
                $newStock = $_POST["newStock"];
                $existingStock = $_POST["existingStock"];
                $note = $_POST["note"] ?? "";
            }
            catch(\Exception $e) {
                echo json_encode(array("status" => 0,"error" => "Kan ikke finde data."));
                exit();
            }

            // Load vlager item stock
            $vlagerItem = \VLagerItem::find('all',array('conditions' => array('vlager_id = ? AND itemno = ?', $this->vlager->id, $itemno)));
            if(count($vlagerItem) == 0) {
                echo json_encode(array("status" => 0,"error" => "Kan ikke finde vare."));
                exit();
            }

            if(count($vlagerItem) > 1) {
                echo json_encode(array("status" => 0,"error" => "Fandt mere end 1 vare, kan ikke opdaterer"));
                exit();
            }

            $vlagerItem = $vlagerItem[0];

            // Check existing
            if($vlagerItem->quantity_available != $existingStock) {
                echo json_encode(array("status" => 0,"error" => "Lagerbeholdning er blevet ændret indlæs siden igen og prøv igen."));
                exit();
            }

            if($newStock < 0) {
                echo json_encode(array("status" => 0,"error" => "Lagerbeholdning kan ikke være negativ."));
                exit();
            }

            if($newStock > 10000) {
                echo json_encode(array("status" => 0,"error" => "Lagerbeholdning kan ikke være over 10000."));
                exit();
            }


            // Delta
            $delta = $newStock - $existingStock;

            if(VLager::updateLagerItem($this->vlager->id, $itemno, $delta, "Manuel justering: ".$note)) {
                echo json_encode(array("status" => 1,"error" => "OK"));
                // Commit and ok
                if(\router::$systemUser != null) {
                    \System::connection()->commit();
                }

                $mailDescription = "Justering af lager ".$this->vlager->code.": ".$itemno." fra ".$existingStock." til ".$newStock." stk.<br>";
                $mailDescription .= "Note: ".$note;
                mailgf("sc@interactive.dk","Justering af lager ".$this->vlager->code.": ".$itemno,nl2br($mailDescription));


            } else {
                echo json_encode(array("status" => 0,"error" => "Kunne ikke opdatere lagerbeholdning."));
            }

            exit();
        }

        // Output log
        if(isset($_GET["log"]) && trimgf($_GET["log"]) != "") {
            LogPage::outputLogPage($this->baseUrl, $this->vlager, trimgf($_GET["log"]));
            exit();
        }
        
        // Output order
        if(isset($_GET["order"]) && intvalgf($_GET["order"]) > 0) {



            if((($_POST["action"] ?? "") == "saveorder")) {
                Orderpage::saveOrderpage($this->baseUrl, $this->vlager, intvalgf($_GET["order"]));
            } else if(isset($_GET["vla"]) && $_GET["vla"] == "extradelivery") {

                try {


                    // Find order
                    $vlagerIncoming = \VLagerIncoming::find(intvalgf($_GET["order"]));

                    $vlnew = new \VLagerIncoming();
                    $vlnew->vlager_id = $vlagerIncoming->vlager_id;
                    $vlnew->sono = $vlagerIncoming->sono;
                    $vlnew->created = date("Y-m-d H:i:s");
                    $vlnew->received = null;
                    $vlnew->sender_note = "Ekstra dellevering<br>".$vlagerIncoming->sender_note;
                    $vlnew->receiver_note = "";
                    $vlnew->save();

                    // Find lines
                    $vlagerIncomingLines = \VLagerIncomingLine::find('all',array('conditions' => array('vlager_incoming_id = ?', $vlagerIncoming->id)));
                    foreach($vlagerIncomingLines as $vlagerIncomingLine) {
                        $vlnewLine = new \VLagerIncomingLine();
                        $vlnewLine->vlager_id = $vlnew->vlager_id;
                        $vlnewLine->vlager_incoming_id = $vlnew->id;
                        $vlnewLine->itemno = $vlagerIncomingLine->itemno;
                        $vlnewLine->quantity_order = $vlagerIncomingLine->quantity_order;
                        $vlnewLine->quantity_received = 0;
                        $vlnewLine->save();
                    }

                    // Commit and ok
                if(\router::$systemUser != null) {
                    \System::connection()->commit();
                }
                    echo $vlnew->id;
                    exit();

                } catch (\Exception $e) {
                    echo "Kan ikke finde ordre der skal kopieres (".$_GET["order"]."). ".$e->getMessage()." (".$e->getLine().")";
                    exit();
                }

            } else {
                Orderpage::outputOrderpage($this->baseUrl, $this->vlager, intvalgf($_GET["order"]));
            }
        }

        // Output frontpage
        else {
            Frontpage::outputFrontpage($this->baseUrl, $this->vlager);
        }



    }

    private function authorize()
    {

        if($this->isPublic) {

            if(isset($_POST["username"]) && isset($_POST["password"])) {
                $this->login();
            }

            if (!isset($_SESSION["vlagerfront"]) || !is_array($_SESSION["vlagerfront"])) {
                $this->loginpage();
                exit();
            }

            try {
                $vlager = \VLager::find('all',array('conditions' => array('username = ? AND password = ?', $_SESSION["vlagerfront"]["username"], $_SESSION["vlagerfront"]["password"])));
            } catch (\Exception $e) {
                $this->loginpage("Forkert brugernavn eller adgangskode.");
                exit();
            }

            if(count($vlager) == 0) {
                $this->loginpage("Forkert brugernavn eller adgangskode.");
                exit();
            }

            $vlager = $vlager[0];

            if($vlager->id == 0 || $vlager->username != $_SESSION["vlagerfront"]["username"] || $vlager->password != $_SESSION["vlagerfront"]["password"]) {
                $this->loginpage("Forkert brugernavn eller adgangskode.");
                exit();
            }

            if($vlager->active == 0) {
                $this->loginpage("Lager er ikke aktivt.");
                exit();
            }

            $this->vlagerid = $vlager->id;
            $this->vlager = $vlager;


        } else {

            if(\router::$systemUser == null || \router::$systemUser->id == 0) {
                Template::outputError("Du er ikke verificeret","Du er ikke logget ind.");
                exit();
            }

            try {

                $vlager = \VLager::find($this->vlagerid);
                if($vlager == null) {
                    Template::outputError("Du er ikke verificeret","Kan ikke finde lager.");
                    exit();
                }
                if($vlager->id == 0) {
                    Template::outputError("Du er ikke verificeret","Kan ikke finde lager.");
                    exit();
                }
                if($vlager->active != 1) {
                    Template::outputError("Du er ikke verificeret","Lager er ikke aktivt.");
                    exit();
                }

                $this->vlager = $vlager;

            }
            catch(\Exception $e) {
                Template::outputError("Du er ikke verificeret","Kan ikke finde lager.");
                exit();
            }

        }

    }

    private function login()
    {

        $username = $_POST["username"];
        $password = $_POST["password"];

        try {
            $vlager = \VLager::find('all',array('conditions' => array('username = ? AND password = ?', $username, $password)));
        } catch (\Exception $e) {
            $this->loginpage("Forkert brugernavn eller adgangskode.");
            exit();
        }

        if(count($vlager) == 0) {
            $this->loginpage("Forkert brugernavn eller adgangskode.");
            exit();
        }

        $vlager = $vlager[0];

        if($vlager->id == 0 || $vlager->username != $username || $vlager->password != $password) {
            $this->loginpage("Forkert brugernavn eller adgangskode.");
            exit();
        }

        if($vlager->active == 0) {
            $this->loginpage("Lager er ikke aktivt.");
            exit();
        }

        $_SESSION["vlagerfront"] = array("id" => $vlager->id, "username" => $vlager->username, "password" => $vlager->password);
        return true;
    }

    private function loginpage($error="") {

        Template::templateTop();

        echo '<style>
            .form-container {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-color: #f7f7f7;
            }
            .form-box {
                padding: 20px;
                border: 1px solid #ccc;
                background-color: white;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
            }
            .form-box label {
                display: block;
                margin-bottom: 8px;
            }
            .form-box input {
                width: 100%;
                padding: 8px;
                margin-bottom: 12px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .form-box button {
                width: 100%;
                padding: 10px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .form-box button:hover {
                background-color: #0056b3;
            }
        </style>';


        echo '<div class="form-container">';



        echo '<div class="form-box">';
        echo "<h2>Gavefabrikken VLager styring</h2>";
        echo '<form method="post" action="' . htmlspecialchars($this->baseUrl) . '">';

        if($error != "") {
            echo '<p style="color:red;">' . $error . '</p>';
        }

        echo '<label for="username">Brugernavn</label>';
        echo '<input type="text" id="username" name="username" required>';

        echo '<label for="password">Adgangskode</label>';
        echo '<input type="password" id="password" name="password" required>';
        echo '<button type="submit">Log ind</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';

        Template::templateBottom();
    }

}