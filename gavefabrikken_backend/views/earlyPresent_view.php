<?php
if(isset($_GET["login"])){
  if($_GET["login"] != "dsfkjsadhferuifghriuejf3434fhsudif"){
      echo "Ingen adgang";
       die();
  }
} else {
    echo "ingen adgang";
    die();
}

?>

<!DOCTYPE html>

<html>

<head>
  <title>Early present</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>


<style>
body {
    padding:0px;
    margin:0px;
    width: 100%;
    height: 100%;
  	font-family: "Helvetica Neue", Helvetica, sans-serif;

    font-size: 1em;
}
#ep-module{
  height: 600px;
  overflow-y: auto;
}
.saveEP{
  margin-left: 225px;
}

</style>

<script src="views/lib/jquery.min.js"></script>
<script src="views/lib/jquery-ui/jquery-ui.js"></script>
<link href="views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">
<script src="views/js/earlyPresent.js?2"></script>

<script>
$( document ).ready(function() {
    var ep =    new EarlyPresent;
    ep.init("#ep-module",1);

});



</script>

</head>

<body>
<h3><u>Early present admin</u></h3>
<div id="ep-module"></div>
</body>

</html>