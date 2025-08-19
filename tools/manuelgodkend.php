<!DOCTYPE HTML>

<html>

<head>
  <title>Untitled</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script>
var _ajaxPath = "../index.php?rt=";

doReg();

function doReg()
{

        //var _importId ="5980";   // denne skal udfyld
        var  exsistingCompanyId = "";   //denne skal ikke udfyldes

          $.post(_ajaxPath+"company/createCompanyOrder",{ "company_import_id":_importId,"company_id":exsistingCompanyId }, function(data, textStatus) {


          }, "json");
}



</script>



</head>

<body>

</body>

</html>