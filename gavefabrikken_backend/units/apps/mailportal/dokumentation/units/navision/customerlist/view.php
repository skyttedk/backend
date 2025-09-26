
<div style="margin: 20px; width: 600px; border: 1px solid black; padding: 10px;">
    <table>
        <tr>
            <td>Navision opslag</td>
            <td>CVR: <input type="text" id="navsearchcvr" size="20"></td>
            <td>EAN: <input type="text" id="navsearchean" size="20"></td>
            <td><button type="button" id="navSearchBtn">Soeg</button></td>
        </tr>
    </table>
    <div style="display: none;" id="navcustomerdiv"></div>
</div>

<style>

    .navcustomerlist {
        font-size: 14px; margin-top: 10px;
        border-collapse: collapse;
    }

    .navcustomerlist thead th {
        padding: 5px; text-align: left;
    }

    .navcustomerlist tbody td {
        padding: 5px; text-align: left;
        border-top: 1px solid #333333;
    }

    .navcustomerlist tbody tr:nth-child(even) {background: #EEE}
    .navcustomerlist tbody tr:nth-child(odd) {background: #FFF}


</style>

<script>

    var navCList = null;
    function navCustomerListReady() {
        navCList = new navCustomerList('<?php echo $servicePath; ?>');
        $('#navSearchBtn').bind('click',function() { navCList.searchhtml($('#navsearchcvr').val(),$('#navsearchean').val(),"#navcustomerdiv"); });
        
    }

</script>
<script src="<?php echo $assetPath ?>unit.js"></script>
