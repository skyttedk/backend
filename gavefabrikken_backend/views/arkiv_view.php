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
<div id="tabs">
  <ul>
      <li><a href="#tabs-5" >2024</a></li>
      <li><a href="#tabs-4" >2023</a></li>
      <li><a href="#tabs-0" >2022</a></li>

    <span style="float:right; margin-right:15px; font-size: 16px;" ><u>GF-System Arkiv</u></span>
  </ul>

    <div class="tab" id="tabs-5" style="height: 400px;">
        <iframe src="<?php echo GFConfig::ARCHIVE_2024_URL; ?>index.php?rt=login" width=100% height=100% frameBorder="0"></iframe>
    </div>
    <div class="tab" id="tabs-4" style="height: 400px;">
        <iframe src="<?php echo GFConfig::ARCHIVE_2023_URL; ?>index.php?rt=login" width=100% height=100% frameBorder="0"></iframe>
    </div>
    <div class="tab" id="tabs-0" style="height: 400px;">
        <iframe src="<?php echo GFConfig::ARCHIVE_2022_URL; ?>index.php?rt=login" width=100% height=100% frameBorder="0"></iframe>
    </div>


</div>

 <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="/resources/demos/style.css">



</body>
</html>