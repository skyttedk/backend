<?php

class gateway
{
    public $text;
    public $nr;

    public function send($text,$nr,$title)
    {
        $query = http_build_query(array(
           'token' => 'rhSukWyyQLyFyMRXlbNBmW3LFHb15wfLXwACjUIEWnn4DBU_jrZMAORCeSCrotwf',
            'sender' => $title,
            'message' => $text,
            'recipients.0.msisdn' => '0045'.$nr
        ));
        // Send it
        $result = file_get_contents('https://gatewayapi.com/rest/mtsms?' . $query);
        // Get SMS ids (optional)
        var_dump($result); //->ids;
        echo "Test SMS sendt";
    }

    public function send2($text, $nr, $title)
    {
        $query = http_build_query([
            'token' => 'rhSukWyyQLyFyMRXlbNBmW3LFHb15wfLXwACjUIEWnn4DBU_jrZMAORCeSCrotwf',
            'sender' => $title,
            'message' => $text,
            'recipients.0.msisdn' => '0045'.$nr,
            'encoding' => 'UCS2'  // Tilføjet for at bruge UCS-2 encoding
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\n"
            ]
        ]);

        $url = 'https://gatewayapi.com/rest/mtsms?' . $query;

        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            echo "Der opstod en fejl ved afsendelse af SMS";
        } else {
            $responseData = json_decode($result, true);
            echo "SMS sendt. Resultat: ";
            print_r($responseData);
        }
    }
}


?>