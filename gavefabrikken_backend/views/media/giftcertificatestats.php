<!DOCTYPE HTML>

<html>

<head>

<style>
body {
  font-size: 12px;
}

table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    text-align: left;
    padding: 8px;
}

tr:nth-child(even){background-color: #f2f2f2}
</style>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
</head>
<body>

<?php
  echo  "<table border=1 width=700>";
  echo  "<tr><th>Shop</th> <th>Udløbsdato</th> <th>Antal Udstedt</th>   <th>Antal Solgt</th>  <th>Antal Faktureret</th>  <th>Antal Tilbage</th>     <th>Sidst Udstedt nr.</th> </tr>";
  $stats =  GiftCertificate::getStats();
  foreach ($stats as $stat) {
      echo "<tr>";
      echo "<td>".$stat['shop']."</td>";
      $d = $stat['expire_date']->format('Y-m-d');
      if($d=='2018-01-01')
        echo "<td>m. levering</td>";
      else
        echo "<td>".$d."</td>";

      echo "<td>".$stat['total']."</td>";
      echo "<td>".$stat['total_issued']."</td>";
      echo "<td>".$stat['invoiced']."</td>";

      echo "<td>".$stat['remaining']."</td>";
      echo "<td>".$stat['last_issued']."</td>";

     echo "</tr>";
   }
 echo "</table>";
?>



</body>