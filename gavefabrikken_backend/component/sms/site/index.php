<?php
if($_GET["token"] != "ASDFHFGH23489dksfghsdf"){
   die("Ingen adgang");
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SYSTEM</title>
  <link rel="stylesheet" href="components/jqueryui/jquery-ui.css">

  <script src="components/jqueryui/external/jquery/jquery.js"></script>
  <script src="components/jqueryui/jquery-ui.js"></script>
    <style>
        body{
            font-size: 10px;
        }
        legend{color:white;}

    </style>


    <script>
  $( function() {
    $( "#tabs" ).tabs({
      beforeLoad: function( event, ui ) {
        ui.jqXHR.fail(function() {
          ui.panel.html(
            "Couldn't load this tab. We'll try to fix this as soon as possible. " +
            "If this wouldn't be a demo." );
        });
      }
    });
  } );
  </script>
</head>
<body>

<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Quick SMS</a></li>
    <li><a href="#tabs-2">Opret sms job</a></li>
    <li><a href="#tabs-3">Admin sms-grupper</a></li>
  </ul>
  <div id="tabs-1">
    <?php include("page/quickPage.php") ?>
  </div>
    <div id="tabs-2">
    <?php include("page/jobPage.php") ?>
    </div>
    <div id="tabs-3">
         <?php include("page/queuePage.php") ?> 
    </div>

</div>
<?php include("page/dialogTemplate.php") ?>

</body>
</html>