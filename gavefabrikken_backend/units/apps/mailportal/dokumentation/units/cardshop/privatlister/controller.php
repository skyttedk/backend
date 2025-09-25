<?php

namespace GFUnit\cardshop\privatlister;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {

        ini_set('memory_limit','512M');
        parent::__construct(__FILE__);

    }

    public function index() {

        $this->dashboard();

    }

    public function dashboard() {

        $model = new DashboardData();
        $this->view("dashboardview",array("model" => $model));

    }

    public function menudiv() {

        $input = isset($_POST["category"]) ? trimgf($_POST["category"]) : "";
        if($input != "") {
            $parts = explode("_",$input);
            if(count($parts) != 2 || ($parts[0] != "shop" && $parts[0] != "lang")) {
                echo "Fejl i valg af kategori"; exit();
            }
        } else {
            $parts = array("all",0);
        }

        $model = new DashboardData();

        echo "<div class='pdcathead'>Kategorier</div>";
        echo "<div>";

        // Pull all privatedelivery
        $groups = $model->getPrivateDeliveryGroups($parts[0],$parts[1]);
        $totalCount = 0;
        foreach($groups as $group) {
            echo "<div class='pdmenuitem pdmenuitemstate".$group->delivery_state."' onclick='selectState(".$group->delivery_state.")'><div class='pdcatlabel'>".$group->pdcount."</div>".$group->delivery_state.": ".\GFUnit\navision\syncprivatedelivery\ErrorCodes::getRetryText($group->delivery_state)."</div>";
            $totalCount += $group->pdcount;
        }

        echo "</div>";
        echo "<div class='pdcathead'>Optælling</div>";
        echo "<div>";



        $totalCards = $model->getPrivateDeliveryNoChoiceCount($parts[0],$parts[1]);
        echo "<div class='pdcatcount'><div class='pdcatlabel'>".$totalCount."</div>Kort med valg</div>";
        echo "<div class='pdcatcount'><div class='pdcatlabel'>".$totalCards."</div>Kort uden valg</div>";
        echo "<div class='pdcatcount'><div class='pdcatlabel'>".($totalCards > 0 ? intval($totalCount*100/($totalCount+$totalCards)) : "-")."%</div>Andel valgt</div>";

        echo "</div>";

    }

    public function pdlist() {

        $state = isset($_POST["state"]) ? intval($_POST["state"]) : 0;
        $input = isset($_POST["category"]) ? trimgf($_POST["category"]) : "";
        if($input != "") {
            $parts = explode("_",$input);
            if(count($parts) != 2 || ($parts[0] != "shop" && $parts[0] != "lang")) {
                echo "Fejl i valg af kategori"; exit();
            }
        } else {
            $parts = array("all",0);
        }

        // Load shop users
        $model = new DashboardData();
        $dataList = $model->getPrivateDeliveryData($parts[0],$parts[1],$state);

        ?><div class="pdlistheader">
            <div style="float: right;">
                <button onClick="startExport(<?php echo "'".$parts[0]."','".$parts[1]."','".$state."'"; ?>)">Eksporter</button>
            </div>
            <span>Viser privatleveringer med typen
                <?php echo \GFUnit\navision\syncprivatedelivery\ErrorCodes::getRetryText($state); ?> i <?php
                if($parts[0] == "lang") {
                    echo " landet ".\GFCommon\Model\Navision\CountryHelper::countryToCode($parts[1]);
                } else if($parts[0] == "shop") {
                    $shop = \Shop::find($parts[1]);
                    echo " konceptet ".$shop->name;
                } else echo "alle koncepter";
            ?></span>
        </div>
        <table style="width: 100%;" class="pddata">
            <tr class="pdheader">
                <th>Kortnr</th>
                <th>BS nr</th>
                <th>Ordre nr</th>
                <th>Virksomhed</th>
                <th>Koncept</th>
                <th>Dato for valg</th>
                <th>Navn</th>
                <th>Adresse</th>
                <th>Adresse 2</th>
                <th>Postnr</th>
                <th>By</th>
                <th>Land</th>
                <th>Email</th>
                <th>Telefon</th>
                <th>Varenr.</th>
                <th>Gave</th>
            </tr><?php

            foreach($dataList as $row) {

                ?><tr>
                    <td><?php echo $row["shopuser"]->username; ?></td>
                    <td><?php echo $row["companyorder"]->order_no; ?></td>
                    <td><?php echo $row["shopuser"]->order_no; ?></td>
                    <td><?php echo $row["companyorder"]->company_name; ?></td>
                    <td><?php echo $row["companyorder"]->shop_name; ?></td>
                    <td><?php echo $row["shopuser"]->order_timestamp; ?></td>
                    <td><?php echo $row["userdata"]["name"]; ?></td>
                    <td><?php echo $row["userdata"]["address"]; ?></td>
                    <td><?php echo $row["userdata"]["address2"]; ?></td>
                    <td><?php echo $row["userdata"]["postnr"]; ?></td>
                    <td><?php echo $row["userdata"]["bynavn"]; ?></td>
                    <td><?php echo $row["userdata"]["land"]; ?></td>
                    <td><?php echo $row["userdata"]["email"]; ?></td>
                    <td><?php echo $row["userdata"]["telefon"]; ?></td>
                    <td><?php echo $row["presentmodel"]->model_present_no; ?></td>
                    <td><?php echo $row["presentmodel"]->model_name; ?></td>
                </tr><?php
            }


        ?></table><?php

    }

    public function pdexport($type="",$id=0,$state=0)
    {

        // Load shop users
        $model = new DashboardData();
        $dataList = $model->getPrivateDeliveryData($type,$id,$state);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=privatlev-'.$type.'-'.$id.'-'.$state.'-'.date("dmYHi").'.csv');

        $headers = array("Kortnr","BS nr","Ordre nr","Virksomhed","Koncept","Dato for valg","Navn","Adresse","Adresse 2","Postnr","By","Land","Email","Telefon","Varenr.","Gave");


        foreach($headers as $key => $val) {
            echo $val.";";
        }
        echo "\n";



        foreach($dataList as $row)
        {

            $data = array($row["shopuser"]->username,
                $row["companyorder"]->order_no,
                $row["shopuser"]->order_no,
                $row["companyorder"]->company_name,
                $row["companyorder"]->shop_name,
                $row["shopuser"]->order_timestamp,
                $row["userdata"]["name"],
                $row["userdata"]["address"],
                $row["userdata"]["address2"],
                $row["userdata"]["postnr"],
                $row["userdata"]["bynavn"],
                $row["userdata"]["land"],
                $row["userdata"]["email"],
                $row["userdata"]["telefon"],
                $row["presentmodel"]->model_present_no,
                $row["presentmodel"]->model_name);

            foreach($data as $key => $val) {
                echo utf8_decode(trimgf(str_replace(array("\r","\n",";"),array(""," ",""),$val)).";");
            }
            echo "\n";
        }


    }

}