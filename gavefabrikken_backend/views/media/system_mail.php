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
<?php

// Load errors
$hourLogs = SystemLog::find_by_sql("SELECT * FROM system_log WHERE error_message IS NOT NULL AND error_trace IS NOT NULL AND error_message NOT LIKE '%Ugyldig login.%' AND created_datetime >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
$todayLogs = SystemLog::find_by_sql("SELECT * FROM system_log WHERE error_message IS NOT NULL AND error_trace IS NOT NULL AND error_message NOT LIKE '%Ugyldig login.%' AND DATE(created_datetime) = CURRENT_DATE");

// Find mailqueue data
$sentToday = MailQueue::find_by_sql("SELECT count(*) as count FROM mail_queue WHERE sent = 1 AND DATE(created_datetime) = CURRENT_DATE");
$waiting =  MailQueue::find_by_sql("SELECT count(*) as count FROM mail_queue WHERE sent =0 && error = 0");
$errors =  MailQueue::find_by_sql("SELECT count(*) as count FROM mail_queue WHERE error = 1 && created_datetime >= DATE_SUB(NOW(),INTERVAL 48 HOUR)");

echo "<h3>Status på mailkø:</h3>";
echo "<table>
          <tr>
            <td>Sendt i dag</td><td>".$sentToday[0]->count."</td>
            </tr><tr>
            <td>Venter</td><td>".$waiting[0]->count."</td>
            </tr><tr>
            <td>Mailkø færdig om ca.</td><td>".($waiting[0]->count/15)." minutter</td>
            </tr><tr>
            <td>Fejl de sidste 48 timer</td><td>".$errors[0]->count."</td>
            </tr>
        </table><br>";

echo "<h3>Fejllog status:</h3>";
echo "Fejl seneste time: ".countgf($hourLogs)."<br>";
echo "Fejl i dag: ".countgf($todayLogs)."<br>";