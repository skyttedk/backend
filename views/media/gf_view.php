<?php
if(isset($_GET["token"])){
  if($_GET["token"] != "dsfkjsadhferuifghsdfssudif"){
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
  <title>GF-arkiv</title>
     <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>


  $( function() {
        $( "#tabs" ).tabs();
            resizeContent();

        $(window).resize(function() {
            resizeContent();
        });
  } );

 function resizeContent() {
    $height = $(window).height() - 100;
    $('.tab').height($height);
 }


  </script>



</head>

<body>
<div style="position: absolute; right: 50px; top:5px; z-index: 999;"> <h3>GaveFabrikken STATS</h3> </div>
<div id="tabs">
  <ul>
  <li><a href="#tabs-2" >2021 Kort</a></li>
  <li><a href="#tabs-4" >2021 S�lger/db</a></li>
  <li><a href="#tabs-3" >2020 Kort</a></li>
<!--  <li><a href="#tabs-2" >2018</a></li>      -->

<!--    <li><a href="#tabs-2" >cardshop 2016</a></li>
    <li><a href="#tabs-3" >2015</a></li>    -->


  </ul>
  <div class="tab" id="tabs-2" style="height: 400px;">
      <iframe src="<?php echo GFConfig::BACKEND_URL; ?>views/stats2021.php?token=fsdklj43dfgiDFGo90HFHDFG5g_4eu8" width=100% height=100% frameBorder="0"></iframe>
  </div>
  <div class="tab" id="tabs-4" style="height: 400px;">
      <iframe src="<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=bi&token=ldiuhfkjgrby" width=100% height=100% frameBorder="0"></iframe>
  </div>
  <div class="tab" id="tabs-3" style="height: 400px;">
      <iframe src="<?php echo GFConfig::BACKEND_URL; ?>views/stats2020.php?token=fsdklj43dfgiDFGo90HFHDFG5g_4eu8" width=100% height=100% frameBorder="0"></iframe>
  </div>



 <!-- <div class="tab" id="tabs-2" style="height: 400px;">
     <iframe src="http://gavefabrikken2016.dk/gavefabrikken_backend/index.php?rt=page/cardShop&token=asdf43sdha4f34o" width=100% height=100% frameBorder="0"></iframe>
  </div>

  <div class="tab" id="tabs-3" style="height: 400px;">
     <iframe src="http://gfarkiv.dk/gavefabrikken_backend/index.php?rt=mainaa" width=100% height=100% frameBorder="0"></iframe>
  </div> -->
</div>

 <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">



</body>
</html>