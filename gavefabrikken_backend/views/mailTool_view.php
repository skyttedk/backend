﻿<?php
if($_GET["token"] != "dsf4klh43kfhzlk"){
    die("ingen adgang");
}

?>

<!DOCTYPE html>

<html>

<head>
  <title>Mail mail mail</title>

<script src="lib/jquery.min.js"></script>


 <script>
 $( document ).ready(function() {

});
var _ajaxPath = "../../gavefabrikken_backend/index.php?rt=";
var list = [];
var _i = 0;
function go()
{
    var lines = $('#senderList').val().split('\n');
        for(var i = 0;i < lines.length;i++){
        list.push(lines[i]);
    }
    sendController()
}
function sendController()
{
    if(list.length > _i){
        action(list[_i])
    } else {
      alert("fine");
    }


}
function action(id)
{



    var subject = $("#subject").val();
    var body = $("#mailTemplate").val();

    body =  Base64.encode(body);

    var formdata = { "body":body, "email":id,"subject":subject }
    $.post(_ajaxPath+"mail/createMailToEmailRecipent",formdata, function(data, textStatus) {
//    $.post(_ajaxPath+"mail/createMailToCompanyResponsible",formdata, function(data, textStatus) {
//   $.post(_ajaxPath+"mail/createMailToUsername",formdata, function(data, textStatus) {
        console.log(data);
        if(data.status == "1"){
            $("#counter").html(_i);
            _i++
            //$("#log").append("<div>"+id+"</div>");
            sendController();
        } else {
            $("#log").append("<h3>Fejl</h3><div>"+id+"</div>");
            alert("fejl");
        }


    }, "json");


}


function testmail()
{

    var subject = $("#subject").val();
    var body = $("#mailTemplate").val();

    body =  Base64.encode(body);

    var formdata = { "body":body,"subject":subject }
    $.post(_ajaxPath+"mail/createMailTest",formdata, function(data, textStatus) {

        if(data.status == "1"){
            alert("demo mail sendt")

        } else {
            alert("fejl i demo mail");
        }
    }, "json");
}




var Base64 = {


    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",


    encode: function(input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output + this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },


    decode: function(input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    _utf8_encode: function(string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    _utf8_decode: function(utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}

 </script>
</head>

<body>
<div><button onclick="testmail()">Send TEST MAIL</button> </div>
<br /><br /><br />
<div><button onclick="go()">Send</button> </div> <span id="counter"></span>
<table  width="1000" border=1 height="600">
<tr>
    <td width=50% id="log" valign="top">
        <div id="log" style="width:490px;  overflow: auto; border:1xp"> </div>
    </td>
    <td width=50% valign="top">
        <table border=1 width="480" height="580">
        <tr>
            <td >
                <div>Sender list</div>
                <textarea rows="10" cols="50" id="senderList"></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <label>Subject</label><input id="subject" type="text" /><br /><br />
                <div>Mail template</div>
                <textarea rows="10" cols="50" id="mailTemplate"></textarea>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>





</body>
</html>