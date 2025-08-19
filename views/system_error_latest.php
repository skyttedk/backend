
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
    <div style="border:1px solid #C0C0C0; padding: 5px; font-size: 1.2em; font-weight: bold; background:#C0C0C0; display: inline-block;">Seneste PHP fejlbeskeder</div>
</div>
<table class="systemErrorTable">
	<thead>
	<tr>
  	    <th>ID</th>
	    <th>Bruger</th>
		<th>Controller</th>
		<th>Action</th>
        <th>Data</th>
        <th>Tidspunkt</th>

        <th>Fejlbesked</th>
        <th>Trace</th>
	</tr>
	</thead>
    <tbody>
    <?php



    /** @var $error \GFCommon\DB\SystemLog */
    foreach ($systemLog as $error) {

        if(!(strstr($error->getErrorMessage(),"Ugyldig token") || strstr($error->getErrorMessage(),"Ugyldig login"))) {

          ?><tr>
              <td><?php echo $error->getId(); ?></td>
              <td><?php echo $error->getUserId(); ?></td>
                <td><?php echo $error->getController(); ?></td>
                <td><?php echo $error->getAction(); ?></td>
          <td style="padding: 0px;" valign="top">
              <table style="width: 100%; font-size: 0.9em;">
                  <?php

                    $inputs = json_decode($error->getData(),true);
                    if(!is_array($inputs)) $inputs = array();
                    foreach($inputs as $key => $val) {
                        ?><tr><td><?php echo $key; ?></td><td><?php echo is_array($val) ? print_r($val,true) : $val; ?></td></tr><?php
                    }
                  ?>

              </table>
          <td><?php echo $error->getCreatedDatetime(); ?></td>

          <td><?php echo $error->getErrorMessage(); ?></td>
          <td style="font-size: 0.7em;"><pre><?php echo trimgf($error->getErrorTrace()); ?></pre></td>
          </tr><?php

      }
    }

    ?>
	</tbody>


</table>