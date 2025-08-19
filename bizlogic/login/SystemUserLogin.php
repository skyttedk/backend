<?php

namespace GFBiz\Login;


class SystemUserLogin
{

    public function __construct()
    {

    }

    public function performLogin() {

        // Extract data from post
        $username = trimgf($_POST["username"]);
        $password = trimgf($_POST["password"]);
        $deviceToken = trimgf($_POST["device_token"]);
        $authCode = trimgf($_POST["auth_code"]);

        // Check username and password
        if($username == "") return $this->loginError("Brugernavn ikke angivet");
        if($password == "") return $this->loginError("Adgangskode ikke angivet");

        // Find user
        $systemusers = \SystemUser::find('all', array('conditions' => array('username=? and active = ? and deleted = ?', $username, 1, 0)));
        if (count($systemusers) == 0)  {
            return $this->loginError("Ugyldigt login");
        }

        // Check password
        $systemUser = $systemusers[0];
        if (!\SystemUser::hash_equals($systemUser->hash, crypt($password, $systemUser->hash))) {
            return $this->loginError("Ugyldigt login");
        }

        // Check if 2-factor auth should be used
        $hasEmail = trim($systemUser->email) != "";
        $hasPhone = trim($systemUser->phone) != "";
        $use2Factor = $hasEmail || $hasPhone;

        // If use disables, set to false
        if($systemUser->use_2fa == 0) $use2Factor = false;


        // Using 2factor auth
        if($use2Factor) {

            // If no device token, request new
            if($deviceToken == "") {
                return $this->requestNewToken($systemUser,"New login");
            }

            // Find existing token
            try {

                // Find device
                $device = \SystemUserDevice::find('first',array("conditions" => array("system_user_id" => $systemUser->id,"token" => $deviceToken)));

            } catch(Exception $e) {
                return $this->requestNewToken($systemUser,"Device not found, exception");
            }

            // Device not found, request new
            if($device == null) {
                return $this->requestNewToken($systemUser,"Device not found, null");
            }

            // If expired, request new
            if($device->expire->getTimestamp() < time()) {
                return $this->requestNewToken($systemUser,"Existing token expired");
            }

            // Not logged in before
            if($device->login_count == 0) {

                // Too many retries
                if($device->code_tries >= 10) {
                    return $this->loginError("For mange forsøg, luk fanen og start login forfra for at prøve igen.");
                }

                // No auth code provided
                if($authCode == "") {
                    return $this->loginError("Angiv sikkerhedskoden du har modtaget på e-mail eller sms.");
                }

                // Auth code provided
                if($authCode != $device->code) {
                    $device->code_tries++;
                    $device->save();
                    return $this->loginError("Forkert sikkerhedskode.");
                }

            }

            // Update device
            $device->last_login = date('d-m-Y H:i:s');
            $device->login_count++;
            $device->save();

            $deviceToken = $device->token;

        }

        // Login ok
        $systemUser->token = trimgf(NewGUID(), '{}');
        $systemUser->token_created = date('d-m-Y H:n:s');
        $systemUser->save();

        // Creat eaccess token
        $accessToken = \GFCommon\Model\Tokens\SystemUserToken::createFromUserID($systemUser->id);

        $_SESSION["systemuser_login".\GFConfig::SALES_SEASON] = true;
        $_SESSION["systemuser_token".\GFConfig::SALES_SEASON] = $accessToken->getToken();
        $_SESSION["syslogin".\GFConfig::SALES_SEASON] = $systemUser->id;

        // Success
        echo json_encode(array("status" => 1, "data" => array("token" => $systemUser->token, "id" => $systemUser->id, "device" => $deviceToken)));
        \System::connection()->commit();
        exit();

    }

    private function loginError($message) {

        // Commit to db
        \System::connection()->commit();

        // Output error
        echo json_encode(array("status" => 0, "data" => array(), "message" => $message));
        exit();
    }

    private function requestNewToken($systemUser,$reason) {

        // Find device name
        $deviceName = trimgf($_POST["device_name"]);

        // Find IP
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Check if 2-factor auth should be used
        $hasEmail = trim($systemUser->email) != "";
        $hasPhone = trim($systemUser->phone) != "";

        $sendMethod = "";
        if($hasPhone) $sendMethod = "phone:".$systemUser->phone;
        else if($hasEmail) $sendMethod = "email:".$systemUser->email;

        // Create new device
        $device = new \SystemUserDevice();
        $device->system_user_id = $systemUser->id;
        $device->token = bin2hex(random_bytes(32));
        $device->code = rand(100000,999999);
        $device->created = date('d-m-Y H:i:s');
        $device->expire = date('d-m-Y H:i:s',time()+60*60*24*14);
        $device->login_count = 0;
        $device->device_name = $deviceName;
        $device->ip = $ip;
        $device->sent = $sendMethod;
        $device->code_tries = 0;
        $device->reason = $reason;
        $device->save();
        
        // Send sms
        if($hasPhone) {
            if(!$this->send2FASMS($device->code,$systemUser->phone,$systemUser->language)) {
                $hasPhone = false;
            } else {
                $method = "sms";
            }
        }

        // Send e-mail
        if($hasEmail && !$hasPhone) {
            $this->send2FAEmail($systemUser,$device);
            $method = "e-mail";
        }

        // Commit to db
        \System::connection()->commit();

        // Success
        echo json_encode(array("status" => 2, "data" => array("method" => $method, "device" => $device->token)));
        exit();

    }

    private function send2FASMS($code,$phone,$language)
    {

        $phone = str_replace(array(" ","-","+"),"",$phone);

        // Not valid number
        if(strlen($phone) < 8) return false;

        // Danish number
        if($language == 1) {

            if(strlen($phone) == 8) {
                $phone = "45".$phone;
            }

        }

        // Norweigan number
        if($language == 4) {

            if(strlen($phone) == 8) {
                $phone = "47".$phone;
            }

        }

        // Swedish number
        if($language == 5) {

            if(strlen($phone) == 10) {
                $phone = "46".$phone;
            }

        }

        // Test send sms
        try{

            $query = http_build_query(array(
                'token' => 'rhSukWyyQLyFyMRXlbNBmW3LFHb15wfLXwACjUIEWnn4DBU_jrZMAORCeSCrotwf',
                'sender' => 'GF Login',
                'message' => "Sikkerhedskode til nyt login er: ".$code,
                'recipients.0.msisdn' => $phone,
            ));

            // Send it
            $results = file_get_contents('https://gatewayapi.com/rest/mtsms?' . $query);
            return true;

        } catch (Exception $e) {
            return false;
        }

    }

    private function send2FAEmail($systemUser,$device)
    {
        $mailqueue = new \MailQueue();
        $mailqueue->sender_name = "Gavefabrikken";
        $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
        $mailqueue->recipent_email = $systemUser->email;
        $mailqueue->mailserver_id = 4;
        $mailqueue->subject = "Nyt login: sikkerhedskode";
        $mailqueue->body = "<html><head><meta charset='UTF-8'></head><body>";
        $mailqueue->body .= "Der er registreret et nyt login-forsøg på din bruger i gavevalgssystemet: ".$device->device_name." (".$device->ip."):<br><br>Sikkerhedskode til login: ".$device->code."<br><br>Har du ikke foretaget dig et login så kontakt gerne administrator, og giv ikke sikkerhedskoden til andre.";
        $mailqueue->body .= "</body></html>";
        $mailqueue->priority = 9999;
        $mailqueue->save();
    }

}