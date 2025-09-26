
<?php

if(isset($_GET["token"])){
    if($_GET["token"] != "fdsio4879sdfsdf3gwiu" ){
        die("Ingen adgang");
    }
} else {
    die("Ingen adgang");
}


include("page/header_view.php");

?>
<style>
    .frontnotShow{
        display: none;
    }
.pcms td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

.pcms tr:nth-child(even){background-color: #f2f2f2;}

.pcms tr:hover {background-color: #ddd;}

.pcms th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}


/* The sidebar menu */
.pcms .sidenav {
  font-size: 0.8em;
  height: 100%; /* Full-height: remove this if you want "auto" height */
  width: 200px; /* Set the width of the sidebar */
  position: fixed; /* Fixed Sidebar (stay in place on scroll) */
  z-index: 1; /* Stay on top */
  top: 0; /* Stay at the top */
  left: 0;
  background-color: #C6C6C6;
  overflow-x: hidden; /* Disable horizontal scroll */
  padding-top: 20px;
  padding-left:5px;
  color:black;
      z-index: 3000;

}

/* Style page content */
.pcms .pcms-main {
  margin-left: 200px; /* Same as the width of the sidebar */
  margin-top:40px;
  padding: 0px 10px;
  max-width:1500px;
}
.pcms .card {
  max-width: 1000px;
  height: 700px;
}
.pcms .presentation-view{
  height: 700px;
}

.pcms .letter-search-container{
  margin-top:30px;
}
.pcms .fulltxtsearch{

    position: absolute;
    left: 218px;
    top:6px;

    padding:5px;
    width: 300px;
    border:1px solid RGBA(214,6,86,0.9);
}

   .pcms .letter-search-title{
  font-size: 2em;
  color:#800000;
  margin-left:2px;
  cursor: pointer;

}
.pcms .letter-search-title:hover{
padding-left: 5px;
font-weight: bold;

}
 .letter-show-all-b{
  font-size: 1.0em;
  color:#800000;
  margin-left:2px;
  cursor: pointer;

}
.letter-show-all-b:hover{
padding-left: 5px;
font-weight: bold;

}


.pcms .letter-search{
    background-color: #EDEDED;
    font-size: 0.9em;
    padding:3px;
    margin-top: 5px;
     cursor: pointer;
}
.pcms .letter-search:hover{
padding-left: 5px;
 font-weight: bold;
}
.pcms .selected-search{
    padding-left: 5px;
    font-weight: bold;
}
.pcms .card-text{
  font-size: 11px;
}
.pcms .card-title{
    font-size: 12px;
    font-weight: bold;
}
.pcms .presentation-set-build{
  position: fixed;
  top:0px;
  right:10px;
  z-index: 1000;
}
.pcms .top-bar{
    position: fixed;
    top:0px;
    height: 50px;
    width: 100%;
    background-color: #C6C6C6;
    z-index: 2000;
  }
.pcms .my-file{
      position: absolute;
    right: 10px;
    top:5px;
    cursor: pointer;
    display:none;
}

.presentation-copy{
  float:right;
  margin-right:10px;
      cursor: pointer;
    display:none;
}

.pcms .fa-shopping-cart.menu {
    position: absolute;
    right: 10px;
    top:5px;
    cursor: pointer;
}
.pcms .fa-folder{
   position: absolute;
    right: 70px;
    top:5px;
    cursor: pointer;
}

.pcms .fa-glasses{
    position: absolute;
    right: 120px;
    top:5px;
    cursor: pointer;
}
.pcms .menu-present{
    position: absolute;
    right: 130px;
    top:5px;
    cursor: pointer;
}

#busy-fa-file-download{
  display: none;
}

.presentation-elememt-set{
    border:1px solid #A6A6A6;
    padding:5px;
    height:130px;
}
.presentation-elememt-set img{
    max-width: 150px;
    max-height: 115px;

}
.presentation-elememt-set-trash{
  float:right;
  cursor: pointer;

}
.presentation-elememt-set-edit{
  float:right;
  cursor: pointer;
  margin-right: 10px;
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

  #sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
  #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em;  }
.simple{
  width: 250px;
  height: 285px;
  float: left;
  margin:10px;
  border:1px solid #A7A7A7;
  text-align: center;

}
.pt_price{
  width: 100%;
  height: 24px;
  text-align: center;
  background-color: #D7D7D7;
  z-index: 9999;
}
.simpleImg{
  background-repeat: no-repeat;
  background-size: contain;
  width: 100%;
  height: 200px;
  background-position: center;
}
.simpleTitle{
  font-size: 12px;
  padding: 5px;
  width: 100%;
  position: relative;
  bottom: 5px;
  height: 40px;
 background-color:RGBA(214,6,86,0.9);
  color:white;
}
.simpleAction{
  border-top: 1px solid #C6C6C6;

  padding-top:5px;
  height: 25px;
  color:black;
}
.simple-edit{
  margin-left:10px;
  cursor: pointer;
}

.simpleAction .fa-file-pdf{
    float: left;
    margin-left:10px;
    cursor: pointer;
}
.simpleAction .fa-plus-square{
    float: right;
    margin-right:10px;
}
.show-detail-present-event{
  cursor: pointer;
}

@media (min-width: 992px) { .modal .modal-full-height { height: 100%; } }

/* On smaller screens, where height is less than 450px, change the style of the sidebar (less padding and a smaller font size) */
@media screen and (max-height: 450px) {

}
.kostpris{
   margin-left:10px;
   margin-right: 10px;
   font-size: 1.3em;
}
.salesman tr {
    outline: thin solid;
}
.salesman .selected{
    background-color: #CCC11C;
}
.layout tr {
    outline: thin solid;
}
.layout .selected{
    background-color: #CCC11C;
}
legend{
  font-weight: bold;
}
.selected{
  background-color: #CACC1C;
}
#slider-range{
width: 95%;
}
.kostprisContainer{
  margin-left: 20px;
}
.presentation-list{
  border-bottom: 1px solid black;
  height: 40px;
  padding:3px;
  margin-bottom: 3px;
}

.presentation-list-element{
  padding:5px;
  cursor: pointer;
  width: 90%;
  float: left;
}
.presentation-list-element:hover{
 font-weight: bold;

}
.presentation-list-delete{
    float:right;
    margin-right:10px;
    margin-top:7px;
    cursor: pointer;

}
.presentation-list-delete:hover{
   color:red;
}
.closePresentation{
  display: none;
}
 .modal-full-height { height: 100%; }
.b{
  border:2px solid blue;
}
.menu-shop{
 cursor: pointer;
}


/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
#csv{
    float: right;
}

</style>
 <script>
 var _userId = "<?php echo $_GET["userId"]; ?>"
 var _lang = "<?php echo $_GET["lang"]; ?>"

 </script>
</head>
<body>
<div class="pcms">



<!-- Side navigation -->

<div class="sidenav">
<h3 style="color:#800000; font-size: 1.5em;">Kostpris:</h3>
<div class="kostprisContainer">
<label class="kostpris" id="start-kost-pris" ></label>
<label > - </label>
<label class="kostpris" id="slut-kost-pris" ></label>
</div>
<div id="slider-range"></div>
<hr>

<h3 style="color:#800000; font-size: 1.5em;">Budget:</h3>
<div class="budget-filter"></div>
  <div class="letter-search-container"></div>
</div>
<!-- top bar -->
<?php   include("page/topmenu_view.php");  ?>
<!-- Page content -->
<div class="pcms-main">

</div>
</div>
<?php   include("page/cart.php");  ?>
<?php   include("page/preview.php");  ?>
<?php   include("page/footer_view.php");  ?>
<?php   include("page/detail_present_view.php");  ?>
<?php   include("page/my_presentation_view.php");  ?>
<?php   include("page/create_presentation.php");  ?>
<?php   include("page/setting_present_view.php");  ?>
<?php   include("page/modal_present_view.php");  ?>
<?php   include("page/modal_newshop_view.php");  ?>
<?php   include("page/modal_change_multi_price_view.php");  ?>
<?php   include("page/modal_view.php");  ?>
</body>
</html>
