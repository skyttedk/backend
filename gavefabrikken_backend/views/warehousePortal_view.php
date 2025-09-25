


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/js/warehousePortal.js" ></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            height: calc(100vh - 60px);
            margin: 0;
        }
        #wh-portal-container{
            margin-left: 20px;
            margin-right: 20px;
            margin-top: 50px;
            width: calc(100% - 40px);
        }
        #logout{
            position: absolute;
            left: 10px;
            top:10px;
            background-color: gray;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .dataTables_filter{
            position: absolute;
            top:-40px;
            right: 0px;
            z-index: 100;
        }
        #login-form {
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
            text-align: center;
            height: 300px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
        }
        input[type="text"],
        input[type="password"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 10px 20px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* Updated table styling */
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.8em;
            text-align: left;
        }

        .styled-table thead tr {
            background-color: #009879;
            color: white;
            text-align: left;
        }

        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        .styled-table tbody tr:nth-of-type(even) {
            background-color: #E6E6E6;
        }

        /* Ny hover effekt */
        .styled-table tbody tr:hover {
            background-color: #CCE5FF !important; /* Lysblå farve */

            transition: background-color 0.3s ease;
        }

        /* Cells styling */
        .styled-table th, .styled-table td {
            padding: 5px 5px;
        }
        .ui-dialog {
            width: 90vw !important;
            height: 90vh !important;
        }
        .hent-filer {
            cursor: pointer;
        }
        .info {
            cursor: pointer;
        }
        .hent-filer.not-released{
            background-color: #FFCCCC;
        }
        .hent-filer.released{
            background-color: #98FB98;
        }
        .wh-portal-radio-group {
            display: flex;
            justify-content: flex-start;
        }

        .wh-portal-radio-option {
            margin-right: 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .wh-portal-radio-option input {
            margin-right: 5px;
        }

        .wh-portal-radio-option input:checked + span {
            font-weight: bold;
            text-decoration: underline;
        }
        .center-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            text-align: center;
        }

        .wh-portal-top{
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #fff;
            z-index: 100;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        }
        .refresh-button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }

        .refresh-button:hover {
            background-color: #0056b3;
        }

        .refresh-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        /* Added styles for table responsiveness */
        .dataTables_wrapper {
            width: 100%;
            margin: 0 auto;
        }

        table.dataTable {
            width: 100% !important;
        }

        .button-column button {
            position: relative;
            overflow: hidden;
        }

        .button-column button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, .5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .button-column button:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            20% {
                transform: scale(25, 25);
                opacity: 0.3;
            }
            100% {
                opacity: 0;
                transform: scale(40, 40);
            }
        }
        .gf-modal-content button {
            position: relative;
            overflow: hidden;
        }

        .gf-modal-content button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, .5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .gf-modal-content button:focus:not(:active)::after {
            animation: gf-ripple 1s ease-out;
        }

        @keyframes gf-ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            20% {
                transform: scale(25, 25);
                opacity: 0.3;
            }
            100% {
                opacity: 0;
                transform: scale(40, 40);
            }
        }

        /* Disabled states for modal elements */
        .gf-modal-content button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            box-shadow: none;
        }

        .gf-modal-content select:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f5f5f5;
        }

        /* Specific button types in modal */
        .gf-modal-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            margin: 0 4px;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 90px;
            height: 32px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .gf-modal-button--update {
            background-color: #ff0000;
            color: white;
        }

        .gf-modal-button--update:hover:not(:disabled) {
            background-color: #cc0000;
        }

        .gf-modal-button--download {
            background-color: #4CAF50;
            color: white;
        }

        .gf-modal-button--download:hover:not(:disabled) {
            background-color: #45a049;
        }

        .gf-modal-button--save {
            background-color: #2196F3;
            color: white;
        }

        .gf-modal-button--save:hover:not(:disabled) {
            background-color: #1976D2;
        }

        .gf-modal-button--approve {
            background-color: #ff0000;
            color: white;
        }

        .gf-modal-button--approve:hover:not(:disabled) {
            background-color: #cc0000;
        }

        .gf-modal-select {
            padding: 6px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 8px;
            height: 32px;
            background-color: white;
        }
    </style>

</head>
<body>
<center><div id="wh-portal-container"></div></center>
<div class="center-container">
<div id="login-form">
    <h5>GaveFabrikken A/S / Lagerportal</h5>
    <h4>LOGIN</h4>


    <form >
        <label for="username">Username:</label>
        <input type="text" id="username" name="username">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password">
        <input type="submit" id="submit" value="Login">
    </form>
</div>
</div>
<div id="myModal" title="Hent filer" style="display: none;"></div>
<div id="myModalInfo" title="Leveringsinformationer" style="display: none;"></div>

<script>
    _token = "";
    _warehousename = ""
    $(document).ready(function() {
        $("#login-form").draggable();
        if ($.cookie('gfware')) {
            const cookieData = JSON.parse($.cookie('gfware'));
            _token = cookieData.token;
            _warehousename = cookieData.warehousename;
            loadPortal();
        }
        $("#submit").click(function(e) {
            e.preventDefault();

            var username = $("#username").val();
            var password = $("#password").val();

            if(username === "" || password === "") {
                alert("Both fields are required!");
            } else {
                // Implement your AJAX login logic here
                var jqxhr = $.post(BASE_AJAX_URL+"warehousePortal/login", {username: username, password: password}, function(returnMsg, textStatus)
                {
                    if(returnMsg.data.length == 0){
                        alert("Forkert login")
                    } else {
                        _token = returnMsg.data.token;
                        _warehousename = returnMsg.data.name;
                        $.cookie('gfware', JSON.stringify({
                            token: _token,
                            warehousename: _warehousename
                        }), {
                            expires: 7,
                            path: '/',
                            secure: true
                        });

                        loadPortal()
                    }
                }, "json")
                .fail(function()
                {
                     alert("alert_problem");
                })

            }
        });
    });


    function loadPortal(){
        let wp = new WarehousePortal;
        wp.init();
    }
</script>
</body>
</html>