<?php
require_once "/var/www/backend/public_html/gavefabrikken_backend/includes/config.php";


?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <style>
        body {font-family: Arial, Helvetica, sans-serif;


        }

        /* Full-width input fields */
        input[type=text], input[type=password] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        /* Set a style for all buttons */
        button {
            background-color: #04AA6D;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            opacity: 0.8;
        }

        /* Extra styles for the cancel button */
        .cancelbtn {
            width: auto;
            padding: 10px 18px;
            background-color: #f44336;
        }

        /* Center the image and position the close button */
        .imgcontainer {
            text-align: center;
            margin: 24px 0 12px 0;
            position: relative;
        }

        img.avatar {
            width: 40%;
            border-radius: 50%;
        }

        .container {
            padding: 16px;
        }

        span.psw {
            float: right;
            padding-top: 16px;
        }

        /* The Modal (background) */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px;
        }

        /* Modal Content/Box */
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto 15% auto; /* 5% from the top, 15% from the bottom and centered */
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }

        /* The Close Button (x) */
        .close {
            position: absolute;
            right: 25px;
            top: 0;
            color: #000;
            font-size: 35px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: red;
            cursor: pointer;
        }
        #banner{
            width: 90%;
            max-height: 300px;
        }

        /* Add Zoom Animation */
        .animate {
            -webkit-animation: animatezoom 0.6s;
            animation: animatezoom 0.6s
        }

        @-webkit-keyframes animatezoom {
            from {-webkit-transform: scale(0)}
            to {-webkit-transform: scale(1)}
        }

        @keyframes animatezoom {
            from {transform: scale(0)}
            to {transform: scale(1)}
        }

        /* Change styles for span and cancel button on extra small screens */
        @media screen and (max-width: 300px) {
            span.psw {
                display: block;
                float: none;
            }
            .cancelbtn {
                width: 100%;
            }
        }
        input[type='text'],
        input[type='number'],
        textarea {
            font-size: 16px;
        }
    </style>
</head>
<body>



<center>
    <img src="./img/GF_logo_dark.png" id="banner" />
    <br><br><br>
    <h2>QR SCANNER</h2>   Udlevering af gaver  <br>
    <br><br><br>
    <button onclick="document.getElementById('id01').style.display='block'" style="width:80%;">Login</button>
    <center>
        <div id="id01" class="modal">

            <form class="modal-content animate" >
                <div class="imgcontainer">
                    <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>

                </div>

                <div class="container">
                    <label for="uname"><b>Username</b></label>
                    <input type="text" placeholder="Enter Username" name="uname" id="uname" required>

                    <label for="psw"><b>Password</b></label>
                    <input type="password" placeholder="Enter Password" name="psw" id="psw" required>

                    <button type="button" id="login">Login</button>
                    <label id="error"style="color:red;"></label>
                </div>

                <div class="container" style="background-color:#f1f1f1">
                    <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>

                </div>
            </form>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script>
            // Get the modal
            var modal = document.getElementById('id01');

            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function(event) {
                if (event.target == modal) {
                    $("#error").html("");
                    modal.style.display = "none";
                }
            }
            $( document ).ready(function() {


                $("#error").html("");
                $("#login").click(function(){
                    login($("#uname").val().toLowerCase(),$("#psw").val().toLowerCase());
                })

            });

            async function login(username,pw){

                let postData = {
                    username:username,
                    pw:pw
                }

                $.post( "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=app/qrscanner/index/login",postData, function( data ) {
                    let res = JSON.parse(data);
                    if(res.status==1){
                        window.location.replace("<?php echo GFConfig::BACKEND_URL; ?>app/qrscanner/index/view.php?token="+res.data[0].app_users_token+"&id="+res.data[0].id+"&user="+res.data[0].app_users_id+"&camp=1");
                    }
                    else if(res.status==0){
                        alert("Wrong password or username")
                    } else {
                        alert("Something went wrong")
                    }
                })
            }
        </script>

</body>
</html>
