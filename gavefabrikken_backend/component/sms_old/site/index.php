<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SMS-SYSTEM</title>
  <link rel="stylesheet" href="components/jqueryui/jquery-ui.css">

  <script src="components/jqueryui/external/jquery/jquery.js"></script>
  <script src="components/jqueryui/jquery-ui.js"></script>
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
    <li><a href="#tabs-1">Job status</a></li>
    <li><a href="lib/job.php">Quick Send</a></li>
  </ul>
  <div id="tabs-1">
    <?php include("lib/quick.php") ?>
  </div>
</div>


</body>
</html>