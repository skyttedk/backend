<?php

class forgetpasswordController Extends baseController
{
    public function Index()
    {

    }

    public function resendPassword()
    {
        // Hent email og token fra POST data
        $email = $_POST['email'] ?? '';
        $token = $_POST['token'] ?? '';
        if($token != "K7bXp2Rt9Lfdserf4545gdffgmN4qF8sH6vY"){
            $this->sendJsonResponse(['error' => 'Email og token er påkrævet'], 400);
            return;
        }

        // Simuler afsendelse af nyt password
        $result = $this->simulateSendNewPassword($email);

        if ($result) {
            $this->sendJsonResponse(['message' => 'Nyt password er sendt til din email']);
        } else {
            $this->sendJsonResponse(['error' => 'Der opstod en fejl ved afsendelse af nyt password'], 500);
        }
    }

    private function simulateSendNewPassword($email)
    {
        $this->makeNewPassword($email);
        return true;
    }
    private function makeNewPassword($email){

        $user = ShopUser::find('first', array(
            'conditions' => array(
                'username = ? AND shop_id = ? ',
                $email, 6279
            )
        ));

        $newPassword = $this->generatePassword();
        $user->password = $newPassword;

        $res = $user->save();
        System::connection()->commit();
        System::connection()->transaction();
        $this->sendMail($email,$newPassword);

        $this->sendJsonResponse($res);

    }
    private function sendJsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
    private function generatePassword()
    {
        $length = 8;
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $specialChars = '!$)(';

        $password = '';

        // Tilføj mindst et specialtegn
        $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];

        // Fyld resten med tilfældige bogstaver og tal
        for ($i = 0; $i < $length - 1; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        // Bland tegnene for at sikre, at specialtegnet ikke altid er først
        return str_shuffle($password);
    }
    private function sendMail($mail,$newPassword){

        $body = "<p>Hej</p>
                <p>Vi har modtaget en anmodning om at generere og sende et nyt password.<br />Dit nye password er: ".$newPassword."</p>
                <p>Hvis du ikke har anmodet om denne &aelig;ndring, bedes du kontakte Trine Doktor (TRS) fra JN Datas Management Support & Strategy afdeling. </p>
                <p>Med venlig hilsen,<br />GaveFabrikken A/S</p>";

        $mailqueue = new MailQueue();
        $mailqueue->sender_name  = 'Gavefabrikken A/S';
        $mailqueue->sender_email = 'info@gavefabrikken.net';
        $mailqueue->recipent_email = $mail;
        $mailqueue->subject ="Din kode fra GaveFabrikken";
        $mailqueue->body = $body;
        $mailqueue->save();
        $mailqueue->priority = 2;
        System::connection()->commit();
        System::connection()->transaction();
    }


}