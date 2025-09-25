<style>
    .demo {
        border:1px solid #C0C0C0;
        border-collapse:collapse;
        padding:5px;
    }
    .demo th {
        border:1px solid #C0C0C0;
        padding:5px;
        background:#F0F0F0;
    }
    .demo td {
        border:1px solid #C0C0C0;
        padding:5px;
    }
    .sudSpacer{
        width: 100px;
    }
    .demo img{
        cursor: pointer;
    }

</style>

<?php include("system_nav.php"); ?>

<?php

    $systemUserOptions = "<option value=\"\">Ikke angivet</option>";
    $salesPersonService = new GFCommon\Model\Navision\SalesPersonWS();                    
    $salespersonList = $salesPersonService->getAllSalesPerson();
    foreach($salespersonList as $salesPerson) {
        $systemUserOptions .= "<option value=\"".$salesPerson->getCode()."\" data-name=\"".$salesPerson->getName()."\"  data-email=\"".$salesPerson->getEmail()."\">".$salesPerson->getCode().": ".$salesPerson->getName()."</option>";
    }

?>

<table class="demo">
    <caption>Systembrugere</caption>
    <thead>
    <tr>
        <th>Navn</th>
        <th>Brugernavn</th>
        <th>Kodeord</th>
        <th>Saelgerkode</th>
        <th>Brugeradgang</th>
        

        <th></th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php

    foreach ($systemUsers as $systemUser)
    {

        echo " <tr id='$systemUser->id'> ";
        echo "   <td><input type='text' id='systemUserName' name='name' value='$systemUser->name'></td>";
        echo "   <td><input type='text' id='systemUserUsername' name='username' value='$systemUser->username'></td>";
        echo "   <td><input type='text' id='systemUserPassword' name='password' value='$systemUser->password'></td>";
        echo "   <td><select class='salespersoncode'>".str_replace("value=\"".$systemUser->salespersoncode."\"","value=\"".$systemUser->salespersoncode."\" selected",$systemUserOptions)."</select></td>";
        echo "    <td align=center><img src='views/media/icon/1373247310_User1.png ' onclick='systemUser.showPermission($systemUser->id)' height='16' width='16'></td>";

        echo "    <td align=center><img src='views/media/icon/1373253284_save_64.png' onclick='systemUser.save($systemUser->id);' height='16' width='16'></td>";
        echo "    <td align=center><img src='views/media/icon/1373253296_delete_64.png' onclick='systemUser.delete($systemUser->id);' height='16' width='16'></td>";
        echo "    </tr>";
    }
    echo "<tr><td colspan='7'>Opret ny bruger</td></tr>";
    echo "<tr id ='0'>";
    echo "   <td ><input type='text' id='systemUserName' name='name' value=''></td>";
    echo "   <td><input type='text' id='systemUserUsername' name='username' value=''></td>";
    echo "   <td><input type='text' id='systemUserPassword' name='password' value=''></td>";
    echo "   <td><select class='salespersoncode'>".$systemUserOptions."</select></td>";

    echo "   <td align=center><input type='image' src='views/media/icon/1373253494_plus_64.png' onclick='systemUser.createNew()' height='16' width='16'/></td>";
    echo "   <td></td>";
    echo "</tr>";


    ?>
    </tbody>


</table>

<div id="systemUserDialog" style="display:none;">
    <table class = "demo" width=280>
        <tr>
            <td><b>Valgshop</b> </td><td width=10><input type="checkbox" class="systemUserAccess" id="tabAccess_100" data-id="100" /></td>
        </tr>

        <tr>
            <td><b>Gavekort-shops</b> </td><td width=10><input type="checkbox" class="systemUserAccess" id="tabAccess_90" data-id="90" /></td>
        </tr>
        <tr>
            <td><b>GaveAdmin</b></td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_80" data-id="80" /></td>
        </tr>
        <tr>
            <td><b>Tilbud</b> </td><td width=10><input type="checkbox" class="systemUserAccess" id="tabAccess_70" data-id="70" /></td>
        </tr>
        <tr>
            <td><b>Salgsportal</b> </td><td width=10><input type="checkbox" class="systemUserAccess" id="tabAccess_71" data-id="71" /></td>
        </tr>
        <tr>
            <td><b>System</b></td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_60" data-id="60" /></td>
        </tr>
        <tr>
            <td><b>Lager-admin</b></td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_50" data-id="50" /></td>
        </tr>
        <tr>
            <td><b>Shopboard</b></td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_120" data-id="120" /></td>
        </tr>
        <tr>
            <td><b>Infoboard</b></td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_110" data-id="110" /></td>
        </tr>



        <tr>
            <td><b>Arkiv</b></td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_45" data-id="45" /></td>
        </tr>
    </table>
    <hr />

    <fieldset>
        <legend><b>Valgshop</b></legend>
        <table class = "demo">
            <tr>
                <td>Stamdata</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_1" data-id="1" /></td>
                <td class="sudSpacer"></td>
                <td>Forside</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_2" data-id="2" /></td>
            </tr>
            <tr>
                <td>Gaver</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_3" data-id="3" /></td>
                <td  class="sudSpacer"></td>
                <td>Indstillinger</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_4" data-id="4" /></td>
            </tr>
            <tr>
                <td>felt Definition</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_5" data-id="5" /></td>
                <td  class="sudSpacer"></td>
                <td>Brugerindl&oelig;sning</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_6" data-id="6" /></td>
            </tr>
            <tr>
                <td>Rapporter</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_7" data-id="7" /></td>
                <td  class="sudSpacer"></td>
                <td>Lageroverv&aring;gning</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_8" data-id="8" /></td>
            </tr>
        </table>
    </fieldset>


    <fieldset>
        <legend><b>Gavekor-shops</b></legend>
        <table class = "demo">
            <tr>
                <td>Gavekort - ventene Bestillinger </td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_10" data-id="10" /></td>
            </tr>
            <tr>
                <td>Gavekort - salgsstatistik / lagerstyring</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_11" data-id="11" /></td>
            </tr>
            <tr>
                <td>Gavekort - plukliste</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_12" data-id="12"  /></td>
            </tr>
        </table>
    </fieldset>
    <br />




</div>

<script>
    $( function() {
        $( ".systemUserAccess" ).change(function() {
            systemUser.permissionController($(this).attr("data-id") );
        });

    } );
</script>
