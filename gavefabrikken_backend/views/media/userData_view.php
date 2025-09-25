<!DOCTYPE html>

<html>

<head>
  <title>Hello!</title>
  <link rel="stylesheet" type="text/css" href="http://docs.handsontable.com/pro/bower_components/handsontable-pro/dist/handsontable.full.min.css">
<script src="http://docs.handsontable.com/pro/bower_components/handsontable-pro/dist/handsontable.full.min.js"></script>
<script src="lib/jquery.min.js"></script>
<script src="js/main.js"></script>
<script src="js/userData.js?53"></script>
 <script>
 $( document ).ready(function() {
   userData.resetApp()
});

 </script>
</head>

<body>
<button class="uploadGf" onclick="userData.closeApp()">Close</button><button  class="uploadGf" onclick="userData.saveItem()">Save</button><button  class="uploadGf" onclick="userData.tester()">Test</button>
<br />
<div id="userUploadContainer" >

 
</div>
</body>
</html>