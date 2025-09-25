<html>
<head>

</head>
<body style="padding: 0px; margin: 0px; font-family: verdana;">

<table style="width: 100%; height: 100%; border: none; border-collapse: collapse;" cellpadding="0" cellspacing="0">

    <tr>
        <td valign="middle" style="width: 33%; height: 3%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #F0F0F0;">
            <div style="font-size: 20px; padding: 10px; font-weight: bold;">
                <div style="float: right; padding: 2px;"><button type="button" onclick="updateCompanyDashboard();">refresh</button></div>
                Company status overview
            </div>
        </td>
        <td valign="middle" style="width: 33%; height: 3%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #F0F0F0;">
            <div style="font-size: 20px; padding: 10px; font-weight: bold;">
                <div style="float: right; padding: 2px;"><button type="button" onclick="updateOrderDashboard();">refresh</button></div>
                Company order overview
            </div>
        </td>
        <td valign="middle" style="width: 33%; height: 3%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #F0F0F0;">
            <div style="font-size: 20px; padding: 10px; font-weight: bold;">
                <div style="float: right; padding: 2px;"><button type="button" onclick="updateShipmentDashboard();">refresh</button></div>
                Shipment overview
            </div>
        </td>

    </tr>
    <tr>
        <td valign="top" style="width: 33%; height: 30%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #FFFFFF; padding: 0px; margin: 0px;">
            <iframe style="width: 100%; height: 100%; border:none;" id="companydash" src="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/synccompany/dashboard"></iframe>
        </td>
        <td valign="top" style="width: 33%; height: 30%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #FFFFFF; padding: 0px; margin: 0px;">
            <iframe style="width: 100%; height: 100%; border:none;" id="orderdash" src="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncorder/dashboard"></iframe>
        </td>
        <td valign="top" style="width: 33%; height: 30%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #FFFFFF; padding: 0px; margin: 0px;">
            <iframe style="width: 100%; height: 100%; border:none;" id="shipmentdash" src="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncshipment/dashboard"></iframe>
        </td>

    </tr>
    <tr>
        <td valign="middle" style="width: 33%; height: 3%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #F0F0F0;">
            <div style="font-size: 20px; padding: 10px; font-weight: bold;">
                <div style="float: right; padding: 2px;"><input type="text" style="width: 50px;" value="" name="gs_sync_company_id" autocomplete="false"  id="companysyncid"> <button type="button" onclick="runCompanySync();">run sync</button></div>
                Company sync
            </div>
        </td>
        <td valign="middle" style="width: 33%; height: 3%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #F0F0F0;">
            <div style="font-size: 20px; padding: 10px; font-weight: bold;">
                <div style="float: right; padding: 2px;"><input type="text" style="width: 50px;" value="" name="gs_sync_order_id" autocomplete="false"  id="ordersyncid"> <button type="button" onclick="runOrderSync();">run sync</button></div>
                Company order sync
            </div>
        </td>
        <td valign="middle" style="width: 33%; height: 3%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #F0F0F0;">
            <div style="font-size: 20px; padding: 10px; font-weight: bold;">
                <div style="float: right; padding: 2px;"> <button type="button" onclick="runShipmentPreprocess();">run shipto check</button> &nbsp; <input type="text" style="width: 50px;" value="" name="gs_sync_shipment_id" autocomplete="false"  id="shipmentsyncid"> <button type="button" onclick="runShipmentSync();">run sync</button></div>
                Shipment sync
            </div>
        </td>

    </tr>
    <tr>
        <td valign="top" style="width: 33%; height: 63%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #FFFFFF; padding: 0px; margin: 0px;">
            <iframe style="width: 100%; height: 100%; border:none; background: white;" src="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/synccompany/next" id="companysync"></iframe>
        </td>
        <td valign="top" style="width: 33%; height: 63%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #FFFFFF; padding: 0px; margin: 0px;">
            <iframe style="width: 100%; height: 100%; border:none; background: white;" src="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncorder/next" id="ordersync"></iframe>
        </td>
        <td valign="top" style="width: 33%; height: 63%; overflow: hidden; border: 2px solid #333333; padding: 0px; margin: 0px; background: #FFFFFF; padding: 0px; margin: 0px;">
            <iframe style="width: 100%; height: 100%; border:none; background: white;" src="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncshipment/next" id="shipmentsync"></iframe>
        </td>

    </tr>


</table>

<script>

    function runCompanySync() {

        var syncid = document.getElementById('companysyncid').value.trim();
        var url = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/synccompany/runsync&t="+Math.random();
        if(syncid != "") {
            url = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/synccompany/syncid/"+syncid+"&t="+Math.random();
        }

        document.getElementById('companysync').setAttribute("src",url);
        setTimeout(function() { updateCompanyDashboard(); },2000);
        setTimeout(function() { updateCompanyDashboard(); },5000);
    }

    function updateCompanyDashboard() {
        document.getElementById('companydash').contentWindow.document.location.reload()
    }

    function updateCompanyBlocks() {
        document.getElementById('companyblocks').contentWindow.document.location.reload()
    }


    function runOrderSync() {

        var syncid = document.getElementById('ordersyncid').value.trim();
        var url = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncorder/runsync&t="+Math.random();
        if(syncid != "") {
            url = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncorder/syncid/"+syncid+"&t="+Math.random();
        }

        document.getElementById('ordersync').setAttribute("src",url);
        setTimeout(function() { updateOrderDashboard(); },2000);
        setTimeout(function() { updateOrderDashboard(); },5000);

    }

    function updateOrderDashboard() {
        document.getElementById('orderdash').contentWindow.document.location.reload()
    }

    function updateOrderBlocks() {
        document.getElementById('orderblocks').contentWindow.document.location.reload()
    }


    function runShipmentSync() {

        var syncid = document.getElementById('shipmentsyncid').value.trim();
        var url = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncshipment/runsync&t="+Math.random();
        if(syncid != "") {
            url = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncshipment/syncid/"+syncid+"&t="+Math.random();
        }

        document.getElementById('shipmentsync').setAttribute("src",url);
        setTimeout(function() { updateShipmentDashboard(); },2000);
        setTimeout(function() { updateShipmentDashboard(); },5000);
    }

    function runShipmentPreprocess() {
        var url = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=unit/navision/syncshipment/shiptocheck&t="+Math.random();
        document.getElementById('shipmentsync').setAttribute("src",url);
        setTimeout(function() { updateShipmentDashboard(); },2000);
        setTimeout(function() { updateShipmentDashboard(); },5000);
    }

    function updateShipmentDashboard() {
        document.getElementById('shipmentdash').contentWindow.document.location.reload()
    }

    function updateShipmentBlocks() {
        document.getElementById('shipmentblocks').contentWindow.document.location.reload()
    }


</script>

</body>
</html>