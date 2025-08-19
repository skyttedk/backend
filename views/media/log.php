<table style="border:solid;border-width:1px">

<tr>
<td>entry no.</td>
<td>user id</td>
<td>controller</td>
<td>action</td>
<td>data</td>
<td>timestamp</td>


</tr>

<?php

foreach ($log as $logEntry)
{
    echo '<tr>';
	echo '<td style="border:solid;border-width:1px">'.$logEntry->id.'</td>';
	echo '<td style="border:solid;border-width:1px">'.$logEntry->user_id.'</td>';
	echo '<td style="border:solid;border-width:1px">'.$logEntry->controller.'</td>';
	echo '<td style="border:solid;border-width:1px">'.$logEntry->action.'</td>';
   	echo '<td style="border:solid;border-width:1px">'.$logEntry->data.'</td>';
   	echo '<td style="border:solid;border-width:1px">'.$logEntry->time_stamp->format('short').'</td>';
    echo '</tr>';

}


?>


</table>