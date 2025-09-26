<?php

namespace GFUnit\valgshop\reports;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function proformafakturaform() {

        $model = new ProformaFaktura(intvalgf($_POST["shopid"]));
        $model->showDownloadForm();

    }

    public function proformafakturadownload() {

        $model = new ProformaFaktura(intvalgf($_GET["shopid"]));
        $model->downloadFaktura();

    }

    public function proformatool()
    {
        $model = new ProformaPrivatLevering();
        $model->dispatch("index.php?rt=unit/valgshop/reports/proformatool",false);
    }

    public function reminderlist($date = "",$shopid=0)
    {

        // If date maches yyyy-mm-dd format, use it, otherwise use today's date
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', trimgf($date)) ? $date : null;

        $shopid = intvalgf($shopid);

        $rl = new ReminderList();

        if($date == null) {
            $rl->showoverview();
        } else {
            if($shopid > 0) {
                $rl->showshop($date,$shopid);
            } else {
                $rl->showdate($date);
            }

        }

    }


}