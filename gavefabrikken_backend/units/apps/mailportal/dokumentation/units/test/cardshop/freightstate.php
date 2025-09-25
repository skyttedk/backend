<?php

namespace GFUnit\test\cardshop;
use GFBiz\Model\Cardshop\OrderFreightState;
use GFCommon\Model\Navision\OrderXML;

class FreightState
{

    public function __construct()
    {


    }

    public function dispatch()
    {

        if(isset($_POST["companyorderid"]) && intval($_POST["companyorderid"]) > 0) {
            $this->runCompanyOrderID(intval($_POST["companyorderid"]));
        }

        if(isset($_POST["companyid"]) && intval($_POST["companyid"]) > 0) {
            $this->runCompanyID(intval($_POST["companyid"]));
        }

        $this->showTestForm();
        \system::connection()->commit();
    }

    private function runCompanyOrderID($companyorderid)
    {
        echo "<hr>";
        echo "RUN COMPANY ORDER WITH ID: ".$companyorderid."<br>";
        try {

            $companyorder = \CompanyOrder::find($companyorderid);
            echo "Found company order no: ".$companyorder->order_no."<br>";
            echo "Run freight state update on order ".$companyorder->order_no.":<br>";

            $this->outputCompanyList($companyorder->company_id,"Before freight state update");
            echo "Current state: ".$companyorder->freight_state."<br>";

            $newState = OrderFreightState::updateOrderFreightState($companyorder);
            echo "New state: ".$companyorder->freight_state."<br>";

            $this->outputCompanyList($companyorder->company_id,"After freight state update");

            $orderxml = new OrderXML($companyorder,1,OrderXML::STATE_OVERRIDE_MOMSFRAGT);
            $xml = $orderxml->getXML();

            echo "<hr><h3>Ordre xml som lukket</h3><pre>
".htmlentities($xml)."
            </pre>";


        } catch (\Exception $e) {
            echo "GOT EXCEPTION: ".$e->getMessage()." (".$e->getFile()." : ".$e->getLine().")";
        }
        echo "<hr>";
    }



    private function runCompanyID($companyid)
    {
        echo "<hr>";
        echo "RUN COMPANY WITH ID: ".$companyid."<br>";
        try {

            $company = \Company::find($companyid);
            echo "Found company order no: ".$company->id."<br>";
            echo "Run freight state update on company ".$company->id.":<br>";

            $this->outputCompanyList($company->id,"Before freight state update");
            OrderFreightState::updateCompanyFreightState($companyid);
            $this->outputCompanyList($company->id,"After freight state update");


        } catch (\Exception $e) {
            echo "GOT EXCEPTION: ".$e->getMessage()." (".$e->getFile()." : ".$e->getLine().")";
        }
        echo "<hr>";
    }

    private function outputCompanyList($companyid,$title="")
    {

        $companyOrderList = \CompanyOrder::find_by_sql("select * FROM company_order WHERE company_id = ".intval($companyid)." ORDER BY shop_id ASC, expire_date ASC");

        echo "<br>";
        if($title != "") echo "<h3>".$title."</h3>";
        echo "<table style='width: 600px;'><tr><td>id</td><td>order no</td><td>quantity</td><td>shop id</td><td>expire date</td><td>freight state</td></tr>";
        foreach($companyOrderList as $companyOrder) {
            echo "<tr><td>".$companyOrder->id."</td><td>".$companyOrder->order_no."</td><td>".$companyOrder->quantity."</td><td>".$companyOrder->shop_id."</td><td>".$companyOrder->expire_date->format("Y-m-d")."</td><td>".$companyOrder->freight_state."</td></tr>";
        }
        echo "</table>";
        echo "<br>";
    }

    private function showTestForm()
    {

        ?>
        <h2>Kør fragt synkronisering på ordre eller company</h2><br>
        <form method="post" action="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/test/cardshop/freightstate">
            Company order id:
            <input type="text" name="companyorderid" size="6" value="<?php echo isset($_POST["companyorderid"]) ? $_POST["companyorderid"] : ""; ?>">
            <button type="submit">kør</button>
        </form><br>
        <form method="post" action="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/test/cardshop/freightstate">
        Company id:
        <input type="text" name="companyid" size="6" value="<?php echo isset($_POST["companyid"]) ? $_POST["companyid"] : ""; ?>">
        <button type="submit">kør</button>
        </form><?php

    }


}