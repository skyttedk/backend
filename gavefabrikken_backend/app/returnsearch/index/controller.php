<?php

namespace GFApp\returnsearch\index;
use GFBiz\app\AppController;

class Controller extends AppController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {
        header("Location: https://www.gavefabrikken.dk");
    }

    public function tjdnrgkldkerrndjhfdewrds() {

        ?><!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GF Retursøgning</title>
    <style>
        body { background: white; color: #333333; font-family: Arial; font-size: 14px; margin: 10px; }
        table { border-collapse: collapse; }
        table td { border: 1px solid #cccccc; padding: 5px; }
    </style>
</head>
<body>

<h2>Retursøgning</h2>
<p>Søg på person/adresse informationer, op til 3 forskellige felter.</p>
<form method="post" action="index.php?rt=app/returnsearch/index/tjdnrgkldkerrndjhfdewrds">
    <table>
        <tr>
            <td>
                Kode<br>
                <input type="password" size="20" name="password" value="<?php if(isset($_POST["password"])) echo $_POST["password"]; ?>">
            </td>
            <td>
                Felt 1<br>
                <input type="text" size="20" name="query1" value="<?php if(isset($_POST["query1"])) echo $_POST["query1"]; ?>">
            </td>
            <td>
                Felt 2<br>
                <input type="text" size="20" name="query2" value="<?php if(isset($_POST["query2"])) echo $_POST["query2"]; ?>">
            </td>
            <td>
                Felt 3<br>
                <input type="text" size="20" name="query3" value="<?php if(isset($_POST["query3"])) echo $_POST["query3"]; ?>">
            </td>
            <td>
                &nbsp;<br>
                <button type="submit" style="padding-left: 10px; padding-right: 10px;">Søg</button> <button type="button">ryd</button>
            </td>
        </tr>
        <input type="hidden" name="action" value="search">
    </table>
</form>

<script>

    function clearQueryFields()
    {
        document.querySelector("input[name='query1']").value = "";
        document.querySelector("input[name='query2']").value = "";
        document.querySelector("input[name='query3']").value = "";
        document.querySelector("input[name='query1']").focus();
    }

</script>

<?php

function searchUsers($query1 = null, $query2 = null, $query3 = null) {
    // Base SQL
    $sql = "SELECT ua.shopuser_id";

    // Initialiser WHERE-klausuler array
    $wheres = [];

    // Tilføj søgekriterier for hvert felt, hvis det er angivet
    if (!empty($query1)) {
        $sql .= ", ua1.attribute_id as attribute_id1, ua1.attribute_value as attribute_value1";
        $wheres[] = "(ua1.attribute_value LIKE ? AND ua1.attribute_id != 0)";
    }
    if (!empty($query2)) {
        $sql .= ", ua2.attribute_id as attribute_id2, ua2.attribute_value as attribute_value2";
        $wheres[] = "(ua2.attribute_value LIKE ? AND ua2.attribute_id != 0)";
    }
    if (!empty($query3)) {
        $sql .= ", ua3.attribute_id as attribute_id3, ua3.attribute_value as attribute_value3";
        $wheres[] = "(ua3.attribute_value LIKE ? AND ua3.attribute_id != 0)";
    }

    // Fortsæt kun hvis der er mindst én ikke-tom query
    if (!empty($wheres)) {
        $sql .= " FROM user_attribute AS ua";

        // Tilføj JOINs og WHERE-klausuler, hvis nødvendigt
        if (!empty($query1)) {
            $sql .= " INNER JOIN user_attribute AS ua1 ON ua.shopuser_id = ua1.shopuser_id";
        }
        if (!empty($query2)) {
            $sql .= " INNER JOIN user_attribute AS ua2 ON ua.shopuser_id = ua2.shopuser_id";
        }
        if (!empty($query3)) {
            $sql .= " INNER JOIN user_attribute AS ua3 ON ua.shopuser_id = ua3.shopuser_id";
        }

        // Tilføj WHERE-klausuler
        $sql .= " WHERE " . implode(' AND ', $wheres);
        $sql .= " GROUP BY ua.shopuser_id"; // Tilføj GROUP BY for at sikre unikke bruger-id'er
    } else {
        // Ingen søgekriterier angivet, returner tom SQL
        return "";
    }

    return $sql;
}

 function getSqlBind($sql,$values) {

    try {
        $conn = new \mysqli(\GFConfig::DB_HOST, \GFConfig::DB_USERNAME, \GFConfig::DB_PASSWORD, \GFConfig::DB_DATABASE);
    } catch (\Exception $e) {
        echo "Database connection failed.";
        exit();
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    catch (\Exception $e) {
        echo "Database query failed.";
        exit();
    }

}


if(isset($_POST["action"]) && $_POST["action"] == "search") {

    // Get query values if defines
    $query1 = $_POST["query1"] ?? "";
    $query2 = $_POST["query2"] ?? "";
    $query3 = $_POST["query3"] ?? "";

    $query1 = trim($query1);
    $query2 = trim($query2);
    $query3 = trim($query3);

    if($query1 == "" && $query2 == "" && $query3 == "") {
        echo "Der er ikke angivet en søgning, afbryder!";
        exit();
    }

    if($_POST["password"] != "juletid") {
        echo "Forkert kode, afbryder!";
        exit();
    }


    $sql = searchUsers($query1, $query2, $query3);
    $params = array();
    if(trim($query1) != "") $params[] = "%".$query1."%";
    if(trim($query2) != "") $params[] = "%".$query2."%";
    if(trim($query3) != "") $params[] = "%".$query3."%";

    $results = getSqlBind($sql,$params);
    $orgResults = count($results);

    // Show message
    echo "<p>Fandt ".$orgResults." resultater. ";
    if($orgResults > 10) {
        $results = array_slice($results, 0, 10);
        echo " Viser kun de første 10 resultater.";
    }
    echo "</p>";

    // Process results
    $data = array();

    $filter = 0;

    foreach($results as $row) {

        // Find shopuser
        $shopuser = \ShopUser::find($row["shopuser_id"]);

        // Find shopuser order
        $order = \Order::find_by_shopuser_id($shopuser->id);

        if($order != null) {
            // Company
            $company = \Company::find($shopuser->company_id);


            $shop = \Shop::find($shopuser->shop_id);

            $attrName1 = "";
            $attrName2 = "";
            $attrName3 = "";

            if (isset($row["attribute_id1"]) && $row["attribute_id1"] > 0) {
                $attr = \ShopAttribute::find(intval($row["attribute_id1"]));
                $attrName1 = $attr->name;
            }

            if (isset($row["attribute_id2"]) && $row["attribute_id2"] > 0) {
                $attr = \ShopAttribute::find(intval($row["attribute_id2"]));
                $attrName2 = $attr->name;
            }

            if (isset($row["attribute_id3"]) && $row["attribute_id3"] > 0) {
                $attr = \ShopAttribute::find(intval($row["attribute_id3"]));
                $attrName3 = $attr->name;
            }

            $data[] = array(
                "shopuserid" => $shopuser->id,
                "username" => $shopuser->username,
                "attribute1" => $row["attribute_value1"] ?? "",
                "attribute2" => $row["attribute_value2"] ?? "",
                "attribute3" => $row["attribute_value3"] ?? "",
                "company" => $company->ship_to_company ?? $company->name,
                "companyadress" => $company->ship_to_address,
                "companyzipcity" => $company->ship_to_postal_code . " " . $company->ship_to_city,
                "present" => str_replace("###", ", ", $order->present_model_name),
                "shop" => $shop->name,
                "attrname1" => $attrName1,
                "attrname2" => $attrName2,
                "attrname3" => $attrName3,
            );
        } else {
            $filter++;
        }

    }

    if($filter > 0) {
        echo "<p>".$filter." resultater uden ordre, som er fjernet!</p>";
    }

    ?><table>
        <tr>
            <td>Felt 1</td>
            <td>Felt 2</td>
            <td>Felt 3</td>
            <td>Shop</td>
            <td>Username</td>
            <td>Gave</td>
            <td>Virksomhed</td>
        </tr>

        <?php

    foreach($data as $row) {

            ?><tr>
                <td><?php echo $row["attribute1"]; ?><br><span style="font-size: 0.8em;"><?php echo $row["attrname1"]; ?></span></td>
        <td><?php echo $row["attribute2"]; ?><br><span style="font-size: 0.8em;"><?php echo $row["attrname2"]; ?></span></td>
                <td><?php echo $row["attribute3"]; ?><br><span style="font-size: 0.8em;"><?php echo $row["attrname3"]; ?></span></td>
                <td><?php echo $row["shop"]; ?></td>
                <td><?php echo $row["username"]; ?></td>
                <td><?php echo $row["present"]; ?></td>
                <td><?php echo $row["company"]."<br>".$row["companyadress"]."<br>".$row["companyzipcity"]; ?></td>
            </tr><?php
    }

    ?>

    </table><?php

} ?>

<table>

</table>

</body>
</html>
<?php

    }

    public function search() {


    }

}
