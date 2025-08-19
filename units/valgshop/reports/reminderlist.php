<?php

namespace GFUnit\valgshop\reports;
use GFBiz\units\UnitController;
use GFBiz\valgshop\ValgshopFordeling;
use GFCommon\Model\Navision\SalesHeaderWS;

class ReminderList
{

    public function showshop($date, $shop_id)
    {

        $sql = "SELECT 
  su.id AS user_id,
  su.username,
  ua.attribute_value AS email,
  mq.created_datetime AS email_sent_time,
  o.order_timestamp AS choice_time,
  CASE 
    WHEN o.shopuser_id IS NOT NULL AND (o.order_timestamp < mq.created_datetime OR mq.created_datetime IS NULL) THEN 'Choice Made Before Email'
    WHEN o.shopuser_id IS NOT NULL THEN 'Choice Made After Email'
    ELSE 'No Choice Made'
  END AS choice_status
FROM 
  shop_user su
LEFT JOIN 
  user_attribute ua ON su.id = ua.shopuser_id AND ua.is_email = 1
LEFT JOIN 
  mail_queue mq ON su.id = mq.user_id 
  AND DATE(mq.created_datetime) = '".$date."'
  AND mq.subject = 'Gavevalg deadline'
LEFT JOIN 
  `order` o ON su.id = o.shopuser_id
WHERE 
  su.shop_id = ".intvalgf($shop_id)."
  AND su.is_demo = 0;";



?><!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders</title>
    <link href="units/tools/wizard/assets/bootstrap.min.css" rel="stylesheet">
    <script src="units/tools/wizard/assets/jquery.min.js"></script>
    <script src="units/tools/wizard/assets/popper.min.js"></script>
    <script src="units/tools/wizard/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="units/tools/wizard/assets/fontawesome.css">
    <style>

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            position: sticky;
            top: 0;
            background-color: #f1f1f1;
            z-index: 1;
        }

    </style>
</head>
<body><?php

$shop  =\Shop::find($shop_id);

?><div style="background-color: #343a40; padding: 10px; color: #fff; display: flex; justify-content: space-between; align-items: center;">
    <h1 style="margin: 0; font-size: 24px;">Reminders på  <?php echo $shop->name." d. ".$date; ?></h1>
    <button style="background-color: #007bff; color: #fff; border: none; padding: 8px 16px; cursor: pointer;" onclick="history.back()">Tilbage</button>
</div>
<?php

// Start HTML table
echo "<table class='table table-striped'>";
echo "<tr>
        <th>Bruger ID</th>
        <th>Brugernavn</th>
        <th>E-mail</th>
        <th>E-mail Sendt</th>
        <th>Valg Tidspunkt</th>
        <th>Status</th>
      </tr>";

$userList = \Shop::find_by_sql($sql);

// Iterate over the results and print them in table rows
foreach ($userList as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars((string)$row->user_id) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->username) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->email) . "</td>";
    echo "<td>" . ($row->email_sent_time ? htmlspecialchars($row->email_sent_time) : 'Ingen mail') . "</td>";
    echo "<td>" . ($row->choice_time ? htmlspecialchars($row->choice_time) : 'Intet valg') . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->choice_status) . "</td>";
    echo "</tr>";
}


echo "</table>";

?></body>
</html><?php



}


    public function showdate($date) {



        $sql = "SELECT 
  s.id AS shop_id,
  s.name as shop_name,
  s.expire_warning_date,
  COUNT(DISTINCT su.id) AS total_users,
  COUNT(DISTINCT mq.user_id) AS users_emailed,
  SUM(CASE 
    WHEN o.shopuser_id IS NOT NULL AND (o.order_timestamp < mq.created_datetime OR mq.created_datetime IS NULL) THEN 1
    ELSE 0
  END) AS users_with_choice_before_email,
  SUM(CASE 
    WHEN o.shopuser_id IS NOT NULL AND o.order_timestamp >= mq.created_datetime THEN 1
    ELSE 0
  END) AS users_with_choice_after_email,
  SUM(CASE 
    WHEN o.shopuser_id IS NULL OR o.order_timestamp >= mq.created_datetime THEN 1
    ELSE 0
  END) AS users_without_choice_when_email_sent,
  SUM(CASE 
    WHEN o.shopuser_id IS NULL AND mq.user_id IS NULL THEN 1
    ELSE 0
  END) AS users_without_choice_and_no_email
FROM 
  shop_user su
LEFT JOIN 
  shop s ON su.shop_id = s.id
LEFT JOIN 
  mail_queue mq ON su.id = mq.user_id 
  AND DATE(mq.created_datetime) = '".$date."'
  AND mq.subject = 'Gavevalg deadline'
LEFT JOIN 
  `order` o ON su.id = o.shopuser_id
WHERE 
  s.expire_warning_date = '".$date."'
  AND su.is_demo = 0
GROUP BY 
  s.id, s.expire_warning_date
ORDER BY 
  s.expire_warning_date DESC;";



?><!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders</title>
    <link href="units/tools/wizard/assets/bootstrap.min.css" rel="stylesheet">
    <script src="units/tools/wizard/assets/jquery.min.js"></script>
    <script src="units/tools/wizard/assets/popper.min.js"></script>
    <script src="units/tools/wizard/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="units/tools/wizard/assets/fontawesome.css">
    <style>

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            position: sticky;
            top: 0;
            background-color: #f1f1f1;
            z-index: 1;
        }

    </style>
</head>
<body><?php

?><div style="background-color: #343a40; padding: 10px; color: #fff; display: flex; justify-content: space-between; align-items: center;">
    <h1 style="margin: 0; font-size: 24px;">Alle shops med reminders d. <?php echo $date; ?></h1>
    <button style="background-color: #007bff; color: #fff; border: none; padding: 8px 16px; cursor: pointer;" onclick="history.back()">Tilbage</button>
</div>
<?php

    // Start HTML table
    echo "<table class='table table-striped'>";
    echo "<tr>
            <th>Shop id</th>
            <th>Shop navn</th>
            <th>Reminder dato</th>
            <th>Total antal brugere</th>
            <th>Total antal e-mails</th>
            <th>Brugere der ikke har fået mail</th>
            <th>Antal uden valg da e-mail blev sendt</th>
            <th>Valg før e-mail</th>
            <th>Valg efter e-mail</th>
            <th>&nbsp;</th>
          </tr>";

    $reminderList = \Shop::find_by_sql($sql);

    // Iterate over the results and print them in table rows
foreach ($reminderList as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars((string)$row->shop_id) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->shop_name) . "</td>"; // Antag at shop_name er tilgængelig
    echo "<td>" . htmlspecialchars($row->expire_warning_date->format('Y-m-d')) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->total_users) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->users_emailed) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->users_without_choice_and_no_email) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->users_without_choice_when_email_sent) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->users_with_choice_before_email) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->users_with_choice_after_email) . "</td>";
    echo "<td><a href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/reports/reminderlist/".$date."/".$row->shop_id."'>undersøg</a></td>";
    echo "</tr>";
}


        echo "</table>";

        ?></body>
        </html><?php


    }

    public function showoverview() {

$sql = "SELECT s.expire_warning_date, COUNT(DISTINCT s.id) AS shop_count, COUNT(su.id) AS user_count, COUNT(o.shopuser_id) AS choice_count, (COUNT(su.id) - COUNT(o.shopuser_id)) AS users_without_choice FROM shop s LEFT JOIN shop_user su ON s.id = su.shop_id LEFT JOIN `order` o ON su.id = o.shopuser_id WHERE s.expire_warning_date IS NOT NULL GROUP BY s.expire_warning_date ORDER BY s.expire_warning_date DESC;";
$reminderList = \Shop::find_by_sql($sql);

?><!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders</title>
    <link href="units/tools/wizard/assets/bootstrap.min.css" rel="stylesheet">
    <script src="units/tools/wizard/assets/jquery.min.js"></script>
    <script src="units/tools/wizard/assets/popper.min.js"></script>
    <script src="units/tools/wizard/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="units/tools/wizard/assets/fontawesome.css">
    <style>

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            position: sticky;
            top: 0;
            background-color: #f1f1f1;
            z-index: 1;
        }

    </style>
</head>
<body><?php

?><div style="background-color: #343a40; padding: 10px; color: #fff; display: flex; justify-content: space-between; align-items: center;">
    <h1 style="margin: 0; font-size: 24px;">Alle datoer med reminder mails</h1>

</div>
<?php

// Start HTML table
echo "<table class='table table-striped'>";
echo "<tr>
        <th>Udløbsdato</th>
        <th>Antal Shops</th>
        <th>Antal Brugere</th>
        <th>Antal Valg</th>
        <th>Brugere Uden Valg</th>
        <th>Sendte Mails</th>
        <th>Brugere Uden Mail</th>
        <th>&nbsp;</th>
      </tr>";

// Iterate over the results and print them in table rows
$sql = "SELECT 
  s.expire_warning_date, 
  COUNT(DISTINCT s.id) AS shop_count, 
  COUNT(su.id) AS user_count, 
  COUNT(o.shopuser_id) AS choice_count, 
  (COUNT(su.id) - COUNT(o.shopuser_id)) AS users_without_choice,
  COUNT(DISTINCT mq.user_id) AS emails_sent,
  SUM(CASE 
    WHEN mq.user_id IS NULL AND (o.shopuser_id IS NULL OR o.order_timestamp >= DATE_ADD(s.expire_warning_date, INTERVAL 8 HOUR)) THEN 1
    ELSE 0
  END) AS users_without_email
FROM 
  shop s 
LEFT JOIN 
  shop_user su ON s.id = su.shop_id 
LEFT JOIN 
  `order` o ON su.id = o.shopuser_id 
  AND o.order_timestamp < DATE_ADD(s.expire_warning_date, INTERVAL 8 HOUR)
LEFT JOIN 
  mail_queue mq ON su.id = mq.user_id 
  AND DATE(mq.created_datetime) = s.expire_warning_date
  AND mq.subject = 'Gavevalg deadline'
WHERE 
  s.expire_warning_date IS NOT NULL 
  AND su.is_demo = 0
GROUP BY 
  s.expire_warning_date 
ORDER BY 
  s.expire_warning_date DESC;
";
$reminderList = \Shop::find_by_sql($sql);

// Get today's date
$today = (new \DateTime())->format('Y-m-d');



// Iterate over the results and print them in table rows
foreach ($reminderList as $row) {
    $rowDate = $row->expire_warning_date->format('Y-m-d');

    $isToday = $rowDate === $today;
    $style = $isToday ? "style='font-weight: bold; background-color: #f0f8ff;'" : "";

    echo "<tr $style>";
    echo "<td>" . htmlspecialchars($rowDate) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->shop_count) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->user_count) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->choice_count) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->users_without_choice) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->emails_sent) . "</td>";
    echo "<td>" . htmlspecialchars((string)$row->users_without_email) . "</td>";
    echo "<td><a href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/reports/reminderlist/".$rowDate."'>undersøg</a></td>";
    echo "</tr>";

}


echo "</table>";

?></body>
</html><?php

    }

}
