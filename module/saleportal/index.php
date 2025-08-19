<?php
die("Sælgermodulet er lukket");
if(isset($_GET["demo"]) && $_GET["demo"] == "3"){

} else {
    if(isset($_GET["userId"]) && $_GET["userId"] == "3") {

    } else {
    //    die("<center>Hej, jeg (Ulrich) arbejder på sælger portalen, så den er lige nu under opdateringen. Du kan ringe til mig på tel.: 53746555, hvis der er noget som haster og du har brug for adgang. TAK :-) </center>");
    }
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("page/header_view.php");

?>
<style>
/* The sidebar menu */
.portal .sidenav {
  height: 100%; /* Full-height: remove this if you want "auto" height */
  width: 70px; /* Set the width of the sidebar */
  position: fixed; /* Fixed Sidebar (stay in place on scroll) */
  z-index: 1; /* Stay on top */
  top: 0; /* Stay at the top */
  left: 0;
  background-color: #008acc;
  overflow-x: hidden; /* Disable horizontal scroll */
  padding-top: 20px;
  padding-left:5px;
  color:black;
      z-index: 3000;

}

/* Style page content */
.portal .portal-main {
  margin-left: 75px; /* Same as the width of the sidebar */
  margin-top:0px;


}




.fas{
cursor: pointer;
}
#message{
     position: absolute;
left: 50%;
    top:10px;
    color:red;
    font-weight: bold;
    font-size: 14px;

}
.modal{
  z-index: 9999;
}

iframe{
    overflow:hidden;
}
.login {
  width: 50%;
  margin: auto;
  width: 50%;
}

.login form {border: 3px solid #f1f1f1;}

.login input[type=text], input[type=password] {
  width: 100%;
  padding: 12px 20px;
  margin: 8px 0;
  display: inline-block;
  border: 1px solid #ccc;
  box-sizing: border-box;
}

.login button {
  background-color: #4CAF50;
  color: white;
  padding: 14px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
}

.login button:hover {
  opacity: 0.8;
}

.login .cancelbtn {
  width: auto;
  padding: 10px 18px;
  background-color: #f44336;
}

.login .imgcontainer {
  text-align: center;
  margin: 24px 0 12px 0;
}

.login .container {
  padding: 16px;
}

.login span.psw {
  float: right;
  padding-top: 16px;
}
#welcome{

    font-size:3em;
}
#newsContent{
  display: none;
}

/* On smaller screens, where height is less than 450px, change the style of the sidebar (less padding and a smaller font size) */
@media screen and (max-height: 450px) {

}

</style>

</head>
<body>
<div class="portal">

    <!-- Side navigation -->
    <div class="sidenav">
      <?php   include("page/portalMenu.php");  ?>
    </div>
    <!-- Page content -->
    <div class="portal-main">


    <?php
        if(isset($_GET["user"])){
            echo "<center></center>";
            include("page/portalNews.php");


        }
    ?>

        <?php include("page/portalModule.php"); ?>

    </div>
    </div>


 <?php   include("page/footer_view.php");  ?>
 <?
  if(isset($_GET["user"])){ ?>
  <script>
    setTimeout(function(){
        $("#newsContent").fadeIn(2000);
     }, 500);
  </script>

<?  } ?>

</body>
</html>
