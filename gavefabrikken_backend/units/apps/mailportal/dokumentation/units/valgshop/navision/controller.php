<?php

namespace GFUnit\valgshop\navision;
use GFBiz\units\UnitController;


class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }


    public function scvstools()
    {
        ?><h2>SC VS TOOLS</h2>

        <p>
            <b>Shop list and nav status</b><br>
            <a href="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/valgshop/navision/shoplist" target="_blank"><?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/valgshop/navision/shoplist</a>
        </p>

        <p>
            <b>Block message list</b><br>
            <a href="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/valgshop/navision/blocklist" target="_blank"><?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/valgshop/navision/blocklist</a>
        </p>

        <p>
            <b>View waiting for sync</b><br>
            <a href="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/valgshop/navision/waiting/" target="_blank"><?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/valgshop/navision/waiting/</a>
        </p>

        <p>
            <b>Navision sync job</b><br>
            <a href="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/valgshop/navision/navsync/1" target="_blank"><?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/valgshop/navision/navsync/1</a>
        </p>

        <p>
            <b>Trigger test mail</b><br>
            <a href="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/mailservice/rundev/1" target="_blank"><?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/mailservice/rundev/1</a>
        </p>

        <?php

    }

    /****************************
     * REPORTS
     ****************************/



    public function shoplist() {
        $list = new ShopList();
        $list->run();
    }

    public function blocklist() {
        $list = new BlockList();
        $list->dispatch();
    }

    /****************************
     * TOOLS
     ****************************/

    /****************************
     * SYNC JOBS
     ****************************/

    public function navsync($output=0) {
        $navsync = new NavSyncJob();
        $navsync->run($output==1);
    }

    public function waiting() {
        $navsync = new NavSyncJob();
        $navsync->showWaiting();

    }


}