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



}