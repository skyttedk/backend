
<style>
	.systemErrorTable {
		border:1px solid #C0C0C0;
		border-collapse:collapse;
		padding:5px;
        width: 90%; margin-left: auto; margin-right: auto; text-align: left;
	}
	.systemErrorTable th {
		border:1px solid #C0C0C0;
		padding:5px;
		background:#F0F0F0;
	}
	.systemErrorTable td {
		border:1px solid #C0C0C0;
		padding:5px;
	}

    .systemErrorTable tr:nth-child(even) {background: #F0F0F0}
    .systemErrorTable tr:nth-child(odd) {background: #FFF}

</style>

<?php include("system_nav.php"); ?>

<div style=" text-align: left; width: 90%; margin-left: auto; margin-right: auto; text-align: left;">
    <div style="border:1px solid #C0C0C0; padding: 5px; font-size: 1.2em; font-weight: bold; background:#C0C0C0; display: inline-block;">PHP Fejllog statistik</div>
</div>
<table class="systemErrorTable">
	<thead>
	<tr>
  	    <th>Antal fejl</th>
	    <th>Fejlbesked</th>
		<th>Controller</th>
		<th>Action</th>
        <th>Seneste fejl</th>
        <th>Første fejl</th>
        <th>Trace</th>
	</tr>
	</thead>
    <tbody>
    <?php

      foreach ($errorStats as $error) {

          ?><tr>
              <td><?php echo $error["errorcount"]; ?></td>
          <td><?php echo $error["error_message"]; ?></td>
          <td><?php echo $error["controller"]; ?></td>
          <td><?php echo $error["action"]; ?></td>
          <td><?php echo $error["LastDate"]; ?></td>
          <td><?php echo $error["FirstDate"]; ?></td>
          <td style="font-size: 0.7em;"><pre><?php echo trimgf($error["trace"] ); ?></pre></td>
          </tr><?php

      }
    ?>
	</tbody>


</table>