<?php
namespace GFUnit\pim\sync;
use GFBiz\units\UnitController;

class Dashboard extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function syncOutput($data){
        $html = "<table width='100%'>
<tr><th>Billede</th><th>updated_at</th><th>PIM ID</th><th>Varenr.</th><th>epr name</th><th>Overskrift</th><th>kort beskrivelse</th><th>lang beskrivelse</th><th></th></tr>

            ";
        foreach($data as $item){
            $imgPath = sizeof($item["img"]) > 0 ? "<img width=120 src='".$item["img"][0]."'" : "";

            $input = $item["updated_at"];
            $date = strtotime($input);
// <td>".date('d/m/Y h:i:s', $date)."</td>

            $beskrivelse =  $item["description_da"];
            $needle = ":</strong></p>";
            $pos =  strrpos($beskrivelse,$needle);
            $substr =  substr($beskrivelse,$pos);
            $substr = str_replace("<p>", "<br />", $substr);
            $substr = str_replace("</p>", "", $substr);
            $beskrivelse = substr($beskrivelse, 0, $pos).$substr;


            $html.="
                <tr><td>".$imgPath."</td>
                <td>".date('d/m/Y', $date)."</td>
                <td>".$item["id"]."</td>
                <td>".$item["itemnr"]."</td>
                <td>".$item["erp_product_name"]."</td>
                <td>".$item["product_name_da"]."</td>
                <td>".$item["short_description_da"]."</td>
                <td class='tableLongDescription'>".$beskrivelse."</td>
                <td><button class='do-sync' data-id='".$item["id"]."'>Sync</button></td>
                </tr>
                
                ";
        }
    return $html."</table>";
    }
}
?>