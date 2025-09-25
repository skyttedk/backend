
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;


            height: 100vh;
            margin: 0;
        }
        #wh-portal-container{
            margin-left: 20px;
            margin-top: 50px;
        }
        #login-form {
            margin: auto;  /* Tilføjet */
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
            margin-bottom: 20px;
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

        /* Basic table styling */
        .styled-table {
            width: 100%;
            max-width: 1400px;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.8em;
            text-align: left;
        }

        /* Table header styling */
        .styled-table thead tr {
            background-color: #009879;
            color: white;
            text-align: left;
        }

        /* Table body styling */
        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        /* Alternate row color */
        .styled-table tbody tr:nth-of-type(even) {
            background-color: #E6E6E6;
        }

        /* Hover effect */
        .styled-table tbody tr:hover {
            background-color: white;
        }

        /* Cells styling */
        .styled-table th, .styled-table td {
            padding: 5px 5px;
        }
        .ui-dialog {
            width: 80vw !important;
            height: 80vh !important;
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

        /* Tilføj yderligere stil her for at fremhæve, når en radio er valgt */
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
            background-color: #fff;  /* eller enhver anden farve */
            z-index: 100;  /* så den ligger over andre elementer */
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);  /* valgfri skygge for at skille den fra indholdet nedenfor */
        }

    </style>

</head>
<body>
<center><div id="wh-portal-container"></div></center>
<div class="center-container">
    <div id="login-form">
        <h5>GaveFabrikken A/S / Papirvalg</h5>
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
<div id="myModal" title="Hent filer" style="display: none;">

</div>
<script>
    _token = "";
    _warehousename = ""
    $(document).ready(function() {
        $("#login-form").draggable();

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