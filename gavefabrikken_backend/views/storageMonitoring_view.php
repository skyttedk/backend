<script src="views/js/storageMonitoring.js?v_<? echo rand(); ?> "></script>
<script type="text/javascript">
function smTest()
{
    //ajax("","reservation/scheduleHandler","");
    ajax({shop_id:"130"},"shop/shopStorageMonitoringSchedule","");
}
function smTestResponse(response)
{

}


</script>


<style>
#storageMonitoring {
font-size: 12px;

}
#storageMonitoring table {
    border-collapse: collapse;
    width: 100%;

}
#storageMonitoring th, td {
    text-align: left;
    padding: 3px;
}
#storageMonitoring  tr:nth-child(even){
    background-color: #f2f2f2
}




</style>
<!-- <button onclick="smTest()">TESTTEST</button> -->
<div style="height: 550px; overflow: auto;">
<div style="text-align: left;margin-left: 20px;">
    <label>Antal solgte gaver: </label><input id="storage-present-sold" type="number" /><label style="margin-left: 10px;"> </label>
    <label>Antal max reservation: </label><input id="storage-max-res" type="number" disabled /><label style="margin-left: 10px;"> </label>
    <button id="calc-res" onclick="sm.calcReservation()">Vis forslag</button>
    <div style="color: red" id="storage-to-many"></div>

</div>
    <br>
<table id="storageMonitoring" width="96%" border=1>


</table>

</div>