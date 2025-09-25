<?php

namespace GFUnit\test\onetime;

use GFBiz\units\UnitController;
use GFCommon\Model\Navision\SalesPersonWS;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }


    public function testmail() {

        //mailgf('sc@interactive.dk', 'Test mail', 'Dette er en test e-mail.');
        //echo "ok";

    }

    public function systemuser()
    {

        // Test send sms
        try{

            $query = http_build_query(array(
                'token' => 'rhSukWyyQLyFyMRXlbNBmW3LFHb15wfLXwACjUIEWnn4DBU_jrZMAORCeSCrotwf',
                'sender' => 'Test',
                'message' => "Test message",
                'recipients.0.msisdn' => '4523952820',
            ));

            // Send it
            $results = file_get_contents('https://gatewayapi.com/rest/mtsms?' . $query);
            var_dump($results);

        } catch (Exception $e) {
            echo "Error sending sms: ".$e->getMessage();
        }

        return;

        // Import nav data to system users
        echo "SYSTEMUSER UPDATE<br>";

        $salespersonList = array();

        $client = new SalesPersonWS(1);
        $splist = $client->getAllSalesPerson();
        foreach($splist as $sp) {
            $salespersonList[] = array(1,$sp);
        }

        $client = new SalesPersonWS(4);
        $splist = $client->getAllSalesPerson();
        foreach($splist as $sp) {
            $salespersonList[] = array(4,$sp);
        }

        $client = new SalesPersonWS(5);
        $splist = $client->getAllSalesPerson();
        foreach($splist as $sp) {
            $salespersonList[] = array(5,$sp);
        }

        echo "Found: ".count($salespersonList)." persons<br>";

        $systemuserList = \SystemUser::find('all');

        echo "Found: ".count($systemuserList)." system users<br>";

        foreach($systemuserList as $systemUser) {

            $salespersons = array();
            foreach($salespersonList as $sp) {
                if($sp[0] == $systemUser->language && trim(strtolower($sp[1]->getCode())) == trim(strtolower($systemUser->salespersoncode))) {
                    $salespersons[] = $sp[1];
                }
            }

            echo "<br>".$systemUser->name." [".$systemUser->username."] - ";

            if(count($salespersons) == 0) {
                echo "No salespersons found";
            } else if(count($salespersons) > 1) {
                echo "<b>Multiple salespersons found</b>";
            } else {
                echo "Single salesperson found";

                $systemUser->email = $salespersons[0]->getEmail();
                $systemUser->phone = $salespersons[0]->getPhone();
                $systemUser->save();

            }

        }

        \system::connection()->commit();

    }

}