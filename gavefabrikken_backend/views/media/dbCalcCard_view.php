<link rel="stylesheet" type="text/css" href="<?php echo GFConfig::BACKEND_URL; ?>views/css/dbcalcCard.css?v=<?php echo rand(0, 100); ?>">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script  src="<?php echo GFConfig::BACKEND_URL; ?>views/js/dbcalcCard.js?v=<?php echo rand(0, 100); ?>"></script>
<style>
#dbcalcCard {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#dbcalcCard td, #dbcalcCard th {
  border: 1px solid #ddd;
  padding: 8px;
}

#dbcalcCard tr:nth-child(even){background-color: #f2f2f2;}

#dbcalcCard tr:hover {background-color: #ddd;}

#dbcalcCard th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>

<script>
var DbCalcCard;

function init()
{
       DbCalcCard = new dbCalcCardMain("#dbcalcCard")
       DbCalcCard.init();
}
  init()
 </script>

<div id="dbcalcCard">

</div>






