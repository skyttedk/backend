<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<div style="font-family:verdana;font-size:small;width:500px">

<!--
{{#companies}}
    {{name}}
{{/companies}}
-->

<?php
foreach ($companies as $company)
{
	echo $company->name;
	echo '<br>';
}
?>

