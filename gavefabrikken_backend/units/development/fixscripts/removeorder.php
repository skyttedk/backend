<?php

namespace GFUnit\development\fixscripts;

class RemoveOrder
{

    private $stats;

    public function run()
    {

        if(isset($_POST["companyorderid"]) && intval($_POST["companyorderid"]) > 0) {

            echo "<b>Start sletning af ordre</b><br>";

            $model = new \GFUnit\cardshop\orderform\Controller();
            $model->destroyorder();
            
            echo "<br><br>";
        }

        ?><form method="post" action="">
            companyorder id: <input type="text" value="" name="companyorderid">  <button>Slet ordre</button>
        </form><?php

    }

}
