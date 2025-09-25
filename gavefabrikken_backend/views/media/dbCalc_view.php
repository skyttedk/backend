<link rel="stylesheet" type="text/css" href="views/css/dbCalc.css?v=<?php echo rand(0, 100); ?>">

<script  src="views/js/dbCalc.js?v=<?php echo rand(0, 100); ?>"></script>
<script>
var DBCALC;
function initDbCalc()
{
       DBCALC = new dbCalcMain(".dbcalc")
       DBCALC.init();
}

 </script>
<div class="dbcalc">
<table width=600 >
    <tr><td width=150><label><b>S&oelig;lger:</b></label></td><td width=150><input type="text" id="dbcalc-saleperson" value="ej sat" disabled /></td><td width=150><button class="dbcalc-option-edit" data-id="dbcalc-saleperson">Edit</button></td></tr>
    <tr><td width=150><label><b>Budget:</b></label></td><td width=150><input type="text" id="dbcalc-budget" value="ej sat" disabled /></td><td width=150><button class="dbcalc-option-edit" data-id="dbcalc-budget">Edit</button></td></tr>
    <tr><td><label><b>Varenr. til ej valgte:</b></label></td><td ><label id="dbcalc-stadardgift-name"></label><input type="text" id="dbcalc-stadardgift" value="ej sat" disabled />  </td><td><button class="dbcalc-option-edit" data-id="dbcalc-stadardgift">Edit</button></td></tr>
</table>


<hr>
<div id="dbcalc-data">

</div>


</div>



