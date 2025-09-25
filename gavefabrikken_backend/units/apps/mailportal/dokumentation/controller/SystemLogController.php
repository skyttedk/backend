<?php

// Controller SystemLog
// Date created  Wed, 13 Apr 2016 20:48:50 +0200
// Created by Bitworks

use GFCommon\Model\Systemlog\PHPErrorLogs;

class SystemLogController Extends baseController
{

    public function Index()
    {
    }

    public function ErrorLogLatest()
    {
        $this->registry->template->systemLog = PHPErrorLogs::getLatestErrorList();
        $this->registry->template->show('system_error_latest');
    }

    public function ErrorLogStats()
    {
        $this->registry->template->errorStats = PHPErrorLogs::getErrorStatsList();
        $this->registry->template->show('system_error_stats');
    }

    public function MailLogStats()
    {
        $this->registry->template->show('system_mail');
    }

    public function ObjectFactory()
    {
        \GFCommon\Utils\ObjectFactory::runObjectFactory();
    }

    public function read()
    {
        $systemlog = SystemLog::readSystemLog($_POST['id']);
        response::success(make_json("systemlog", $systemlog));
    }

    public function readAll()
    {
        $systemlog = SystemLog::all();
        response::success(make_json("systemlogs", $systemlog));
    }

    public function readLast10()
    {
        $systemlog = SystemLog::all(array('limit' => 100, 'order' => 'id DESC'));

        response::success(make_json("systemlogs", $systemlog));
    }

    public function readErrors()
    {
        $systemlog = SystemLog::all(array('conditions' => array('committed' => 0), 'order' => 'id DESC'));
        response::success(make_json("systemlogs", $systemlog));
    }

    //B�r egentlig ligge i sin egen system controller, men nu ligger den her
    public function isProduction()
    {
        $system = system::first();
        //smid noget mere data over
        response::success(make_json("data", $system));
    }

    public function deleteAll()
    {
        foreach (SystemLog::all() as $systemlog) {
            $systemlog->delete();
        }
        $dummy = [];
        $options = array();
        response::success(make_json("systemlogs", $dummy, $options));
    }

    public function deleteErrors()
    {
        $systemlogs = SystemLog::all(array('committed' => 0));
        foreach ($systemlogs as $systemlog) {
            $systemlog->delete();
        }
        $options = array();
        response::success(make_json("systemlogs", $systemlogs, $options));
    }

    public function getLoginActivity()
    {
        $dateToTest = date('Y-m-d H:i:s', strtotime('-1 minutes'));
        $systemlogs = SystemLog::find('all', array(
            'conditions' => array("created_datetime > '$dateToTest'"),
        ));
        $dummy = [];
        $dummy['logincount'] = countgf($systemlogs);
        response::success(json_encode($dummy));
    }

    public function rollBackGiftCertificates($from, $to)
    {
        $shopusers = ShopUser::find('all', array(
            'conditions' => array('username BETWEEN  ? AND ? ', $from, $to)));
        foreach ($shopusers as $shopuser) {
            $shop = Shop::find($shopuser->shop_id);
            Shop::removeShopUser($shopuser->id);
        }
        // throw new exception(count($shopusers));
    }


    //  hj�lpe funktion til at oprettet en flok nye brugere til en valgshop.
    //
    public function CreateNewUsers($amount)
    {

        for ($i = 0; $i < $amount; $i++) {
            $userno = Numberseries::getNextNumber(20);
            $password = generateStrongPassword(6, false, 'ld');
            echo $userno . ';' . $password . '<br>';
        }

        $dummy = [];
        response::success(json_encode($dummy));

    }

    public function resendOrderMail()
    {
        return;
        //$company=new CompanyController();
        $this->createOrderMail(52, 2525);
        $dummy = [];
        response::success(json_encode($dummy));
    }

    // Trace
    public function enableFullTrace()
    {
        $system = System::first();
        $system->full_trace = 1;
        $system->save();
        response::success(make_json("system", $system));
    }

    public function disableFullTrace()
    {
        $system = System::first();
        $system->full_trace = 0;
        $system->save();
        response::success(make_json("system", $system));
    }

    /*
    public function removeOrderData()
    {
        $shopUser = ShopUser::find($_POST['id']);
        $shopUser->removeOrderData();
        $options = array('only' => array('id', 'username', 'has_orders'));
        response::success(make_json("ShopUser", $shopUser, $options));
    }

    //*** Clean-Up Script ***
    public function prepareDatabase()
    {
        ExecuteSQL("DELETE FROM mail_queue WHERE id > -1 ");
        ExecuteSQL("DELETE FROM system_log WHERE id > -1 ");
        response::success(make_json("result", "{}"));
    }

    */

}

?>

