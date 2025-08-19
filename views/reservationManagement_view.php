<?php
if($_GET["token"] != "fsdklj43dfgiDFGo90HFHDFG5g_4eu8" ){
    die("Ingen adgang");
}
/*
if (session_status() == PHP_SESSION_NONE) session_start();
$_SESSION["syslogin".GFConfig::SALES_SEASON] = 40;

echo "<h1>Søgning er rettet, jeg arbejder på 'overskredet listen'</h1>";
*/



?>



<!DOCTYPE html>

<html>

<head>
    <title>Cross-STATS</title>


    <style>
        body {
            font-size: 12px !important;
            display: none;
        }
.color-red{
    color:red;
}
.odd{
    background-color: #ddd !important;
}
.even:hover{
    background-color: #ddd !important;
}
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            padding-top: 100px; /* Location of the box */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            z-index: 9999;
        }

        /* Modal Content */
        .modal-content {
            position: relative;
            background-color: #fefefe;
            margin: auto;
            padding: 0;
            border: 1px solid #888;
            width: 80%;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
            -webkit-animation-name: animatetop;
            -webkit-animation-duration: 0.4s;
            animation-name: animatetop;
            animation-duration: 0.4s
        }

        /* Add Animation */
        @-webkit-keyframes animatetop {
            from {top:-300px; opacity:0}
            to {top:0; opacity:1}
        }

        @keyframes animatetop {
            from {top:-300px; opacity:0}
            to {top:0; opacity:1}
        }

        /* The Close Button */
        .close {
            color: white;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-header {
            padding: 2px 16px;
            background-color: #5cb85c;
            color: white;

        }

        .modal-body {padding: 2px 16px;}

        .modal-footer {
            padding: 2px 16px;
            background-color: #5cb85c;
            color: white;
        }
.doUpdate{
    float: right;
    display: none;
    color:green;
    font-size: 16px;
    border: 1px solid green;
    padding: 3px;
    cursor: pointer;
}
.doUpdateSearchItem{
            color:green;
            font-size: 16px;
            border: 1px solid green;
            padding: 3px;
            cursor: pointer;
            float: right;
    margin-bottom: 10px;
    margin-right: 10px;
}

.setCloseOpenAll{
    color:red;
    font-size: 16px;
    border: 1px solid green;
    padding: 3px;
    cursor: pointer;
    float: right;
    margin-bottom: 10px;
}

.markChanges{
    outline: none !important;
    border:1px solid red;
}
        .doUpdate:hover,.doUpdateSearchItem:hover, .setCloseOpenAll:hover{
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }
.expired{
    background: #F75D59 !important;
}
.warning{
    background: yellow  !important;;
}
#totalStatsHeader-ItemSearch{

    position: absolute;
    top: 70px;
    left: 10px;
}
.not-valid-search{
    display: none;
}



        .largeCheckbox {
            width: 20px;
            height: 20px;
        }

        .autoLockCheckbox {
            display: none;
        }

        .padlock-label {
            display: inline-block;
            width: 30px;
            height: 30px;
            text-align: center;
            line-height: 30px;
            cursor: pointer;
        }

        .padlock-icon {
            width: 24px;
            height: 24px;
            opacity: 0.3;
            transition: opacity 0.3s ease, fill 0.3s ease, stroke 0.3s ease;
        }

        .padlock-body {
            fill: none;
            stroke: #ff0000;
            stroke-width: 2;
        }

        .padlock-keyhole {
            fill: #ff0000;
        }

        .autoLockCheckbox:checked + .padlock-label .padlock-icon {
            opacity: 1;
        }

        .autoLockCheckbox:checked + .padlock-label .padlock-body {
            fill: #ff0000;
        }

        .autoLockCheckbox:checked + .padlock-label .padlock-keyhole {
            fill: white;
        }

        .padlock-label:hover .padlock-icon {
            opacity: 0.7;
        }

        .autoLockCheckbox:checked + .padlock-label:hover .padlock-icon {
            opacity: 0.9;
        }

        .padlock-label:hover .padlock-body {
            stroke: #cc0000;
        }

        .autoLockCheckbox:checked + .padlock-label:hover .padlock-body {
            fill: #cc0000;
        }

        .padlock-label:hover .padlock-keyhole {
            fill: #cc0000;
        }

        .autoLockCheckbox:checked + .padlock-label:hover .padlock-keyhole {
            fill: #ffcccc;
        }


        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 300px;
            z-index: 9999;
        }
        .toast-message {
            background-color: #333;
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 10px;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .toast-message.show {
            opacity: 1;
        }
        .toast-message.success {
            background-color: #28a745;
        }
        .toast-message.error {
            background-color: #dc3545;
        }
        .toast-message.warning {
            background-color: #ffc107;
            color: #333;
        }
        .toast-message.info {
            background-color: #17a2b8;
        }
        .toast-counter {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8em;
            margin-left: 10px;
        }

/*-----------------------------*/

        /* Sidebar base styles */
        .sidebar {
            position: fixed;
            left: -250px;
            top: 0;
            width: 250px;
            height: 100%;
            background-color: #f0f0f0;
            transition: left 0.3s ease-in-out;
            z-index: 1000;
            border-right: 1px solid #ccc;
            overflow-y: auto;
        }

        .sidebar.open {
            left: 0;
        }

        .toggle-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #333;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            z-index: 1001;
        }

        .sidebar-content {
            margin-top: 30px;
            padding: 20px;
            z-index: 500;
        }

        .sidebar-content h2, .sidebar-content h3 {
            margin-bottom: 20px;
            color: #333;
        }

        /* Search container styles */
        .search-container {
            display: flex;
            margin-bottom: 20px;
        }

        #shopSearch {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-right: none;
            border-radius: 4px 0 0 4px;
            font-size: 14px;
        }

        #searchShopBtn {
            padding: 8px 10px;
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
            transition: background-color 0.3s;
        }

        #searchShopBtn:hover {
            background-color: #45a049;
        }

        /* Search results styles */
        #searchResults {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 10px;
            padding: 0;
            background-color: #fff;
        }

        #searchResults li {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        #searchResults li:last-child {
            border-bottom: none;
        }

        #searchResults li:hover {
            background-color: #f0f0f0;
        }

        #searchResults li.selected {
            background-color: #e0e0e0;
        }

        /* Main content adjustment */
        #tabs-2 {
            transition: margin-left 0.3s ease-in-out;
        }

        #tabs-2.sidebar-open {
            margin-left: 250px;
        }

        /* Action button styles */
        #valgshopsSidebar .valgshop-action-btn {
            display: block;
            width: 100%;
            padding: 12px 15px;
            margin-top: 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            white-space: normal;
            line-height: 1.4;
        }

        #valgshopsSidebar .valgshop-action-btn:hover {
            background-color: #2980b9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
        }

        #valgshopsSidebar .valgshop-action-btn:active {
            background-color: #2573a7;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            transform: translateY(1px);
        }

        #valgshopsSidebar .valgshop-action-btn.selected {
            background-color: #e74c3c;
        }

        #valgshopsSidebar .valgshop-action-btn.selected:hover {
            background-color: #c0392b;
        }
        #searchResults .result-count {
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
    </style>

    <script src="lib/jquery.min.js"></script>
    <script src="lib/jquery-ui/jquery-ui.js"></script>

    <link href="lib/jquery-ui/jquery-ui.css" rel="stylesheet">


    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet" />
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>




    <script src="js/reservationManagement.js?v=<?php echo rand(); ?>"></script>
    <script src="js/base64.js?v1"></script>
    <script>
        var _ajaxPath = "../../gavefabrikken_backend/index.php?rt=reservationManagement/";
        var _sysId   = '40';
        var RM = new rm();
        $(function() {
            var data = {};
            $( "#tabs-main" ).tabs();


            data['systemuser_id'] = _sysId;
            $("body").fadeIn(500);
            RM.initCardshop();
            loadExceeded()
        });
        function loadCardshop(){
            RM.getCardshop();
        }
        function loadShops()
        {

         //   RM.getValgshop();
        }
        function loadExceeded()
        {
            RM.getExceeded();
        }
    </script>



</head>

<body>

<h3>Lageradministration DK 2024</h3>

<br />
<input id="searchItem" type="text" size="20" value=""><button id="searchItemBtn">Søg Varenr</button>
<br><br>
<div id="tabs-main">
    <ul style="font-size: 11px;">
        <li ><a href="#tabs-0" onclick="loadExceeded()">Overskredet</a></li>
        <li ><a href="#tabs-1" onclick="loadCardshop()">CARDSHOP</a></li>
        <li ><a href="#tabs-2" onclick="loadShops()" >VALGSHOPS</a></li>
        <div class="doUpdate">Updatere</div>
    </ul>
    <div id="tabs-0">
         <div id="exceededDataFrom" style="color: red; display: inline-block; margin-right: 40px;"> </div><div  style="display: inline-block" id="jobState" > </div>
        <div id="exceededDataContainer"></div>
    </div>
    <div id="tabs-1">


        <div style="display: inline-block;margin-left: 20px;"><label style="color:red">Vis overskredet</label> <input class="searchStatus" type="checkbox" id="showWarning" disabled="disabled" /></div>
        <div  style="display: inline-block;margin-left: 20px;"><label >Vis tæt på</label> <input class="searchStatus" type="checkbox" id="showExpired" disabled="disabled" /></div>
        <button id="languageSwitch" style="margin-left: 20px;">Skift til norsk</button>
         <div style="display: inline-block; margin-left: 20px;"><label>Konsept:</label> <select id="conceptFilter" style="margin-left: 10px; padding: 5px;"><option value="">Alle</option></select></div>
        <!-- <div  style="display: inline-block;margin-left: 20px;"><label >Vis ej reserverede (er under udvikling)</label> <input class="searchStatus" type="checkbox" id="showNoRes" disabled="disabled" /></div>  -->
        <div id="cardshopOverview"></div>
        <div id="cardshopDataContainer"></div>


    </div>
    <div id="tabs-2">
        <div style="display: inline-block;margin-left: 20px;"><label style="color:red">Vis overskredet</label> <input class="searchStatus" type="checkbox" id="showWarningValgshop"  /></div>
        <div  style="display: inline-block;margin-left: 20px;"><label >Vis tæt på</label> <input class="searchStatus" type="checkbox" id="showExpiredValgshop" disabled="disabled" /></div>

        <!--  <div  style="display: inline-block;margin-left: 20px;"><label >Vis ej reserverede (er under udvikling)</label> <input class="searchStatus" type="checkbox" id="showNoResValgshop" disabled="disabled" /></div>  -->
        <div id="valgshopOverview"></div>
        <div id="valgshopDataContainer"></div>


    </div>
</div>


<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <div class="modal-header">

            <span class="modal-close close">&times;</span>
            <h2  class="modal-title"></h2>
        </div>
        <div class="modal-body">  </div>

        <div class="modal-footer">

        </div>
    </div>

</div>
<div id="simpleToast" class="toast-container">
    <div class="toast-message">
        <span class="toast-text"></span>
        <span class="toast-counter"></span>
    </div>
</div>
</body>
</html>