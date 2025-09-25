<?php
set_time_limit(4000);
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
include("sms/db/db.php");

// Start output buffering


?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Mail Kø Monitor</title>
        <meta http-equiv="refresh" content="10">
        <style>
            body {
                font-family: Arial, sans-serif;
                text-align: center;
                margin-top: 50px;
            }
            .count {
                font-size: 48px;
                font-weight: bold;
                color: #333;
            }
            .timestamp {
                color: #666;
                margin-top: 10px;
            }
        </style>
    </head>
    <body>
    <h1>Mail Kø Monitor</h1>
    <div class="count">
        <?php
        $db = new Dbsqli();
        $db->setKeepOpen();

        $sql = "SELECT count(*) as antal FROM `mail_queue` WHERE `sent` = 0 AND `error` = 0 ORDER BY `id` DESC";
        $rs = $db->get($sql);
        echo $rs["data"][0]["antal"];

        $db->close();
        ?>
    </div>
    <div class="timestamp">
        Sidst opdateret: <?php echo date('H:i:s'); ?>
    </div>
    </body>
    </html>
<?php
// Flush output buffer

?>