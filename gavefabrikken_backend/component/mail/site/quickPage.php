<table border=0 width=1200 >
<tr>
<td width="400" valign="top" style="color:white;">
    <fieldset style="width:300px">
    mc:disable-tracking
        <legend>TEST MAIL.</legend>
        <input id="testmail" style="width:150px;" value="" />
        <button onclick="quick.sendTest();">Send test</button><br /><br /><br />
        <label>Test navn via "#navn#" </label><input id="testnavn" style="width:150px;" value="" />
        <label>Test email via "#email#" </label><input id="testemail" style="width:150px;" value="" />
        <label>Test data1 via "#data1#" </label><input id="testdata1" style="width:150px;" value="" />
        <label>Test data2 via "#data2#" </label><input id="testdata2" style="width:150px;" value="" />
        <label>Test data3 via "#data3#" </label><input id="testdata3" style="width:150px;" value="" />
    </fieldset>
    <br />
    <fieldset style="width:300px;">
        <legend>Group ID:</legend>

        <input id="group-id" style="width:380px;" >
         <br />
    </fieldset>   <br>
    <fieldset style="width:390px;">
        <legend>MAIL SERVER:</legend>
        <select id="mailserver" style="width: 100%;">
            <option value="4">noreply@gavefabrikken.net</option>
            <option value="5">noreply@presentbolaget.net</option>
            <option value="8">gavefabrikken/presentbolaget.com</option>

        </select>
         <br />
    </fieldset><br>
    <fieldset style="width:390px;">
    <legend><label><input type=checkbox id="prioritet"  value=1> PRIORITET:</label></legend>
    </fieldset>
    <br />

    <fieldset style="width:390px;">
        <legend><label><input type=checkbox id="sendtimecheck" onChange="updateSendTimeCheck()" value=1> UDSKYD AFSENDELSE:</label></legend>

        <input type=text style="display: none; width: 100%;" id="sendtime" value="<?php echo date("Y-m-d H:i:s"); ?>" onChange="validateSendDate()" onKeyUp="validateSendDate()">
        <script>
        
            function updateSendTimeCheck() {
                if($('#sendtimecheck').is(':checked')) $('#sendtime').show(); 
                else { $('#sendtime').hide();  }
            }
        
            function validateSendDate() {
                var valid = true;
                var value = $.trimgf($('#sendtime').val());
                if(value.length != 19) valid = false;
                else if(value.charAt(4) != "-") valid = false;
                else if(value.charAt(7) != "-") valid = false;
                else if(value.charAt(10) != " ") valid = false;
                else if(value.charAt(13) != ":") valid = false;
                else if(value.charAt(16) != ":") valid = false;
                if(valid) $('#sendtime').css('background','#99FFA8');
                else $('#sendtime').css('background','#FD7EA1')
            }
        
        </script>
         <br />
    </fieldset>
    <br />
    <fieldset style="width:300px;">
        <legend>MAIL SUBJECT:</legend>
        <input id="title" style="width:380px;" >
         <br />
    </fieldset>
    <br />
    <fieldset style="width:300px;">
        <legend>MAIL BODY :</legend>
        <textarea id="txt" rows="30" style="width: 380px;"></textarea>
    </fieldset>
    <br /><br />

</td>
    <td width="30"></td>
<td  width="600" valign="top">
    <div style="width: 100%; text-align: right; height: 30px;">
        <button id="sendAlle" style="float:right;" onclick="quick.sendAll()">SEND ALLE</button>
        <button id="stopSend" style="display:none;" onclick="quick.doStopSend();">STOP</button>
    </div>

    <hr />
    <div id="hot" style="color:black;"></div>
</td>
</tr>
</table>
<script src="js/quick.js"></script>
 <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
<script>
    var _ajaxPath = "../../../index.php?rt=";


    var dataObject = [
        {mail:'us@gavefabrikken.dk' , navn: 'ulrich'}
    ];
    var hotElement = document.querySelector('#hot');
    var hotSettings = {
        data: dataObject,
        columns: [
            {
                data: 'mail',
                type: 'text'
            },
            {
                data: 'navn',
                type: 'text'
            },
            {
                data: 'data1',
                type: 'text'
            },
            {
                data: 'data2',
                type: 'text'
            },
            {
                data: 'data3',
                type: 'text'
            }],
        colWidths: [180,160],
        height: 441,
        minSpareRows: 1,

        rowHeaders: true,
        colHeaders: [
            'Email',
            'Navn',
            'Data1',
            'Data2',
            'Data3'
        ]
    };
    var hot = new Handsontable(hotElement, hotSettings);



    var MailTemplate = {
        init:function(){

        },
        loadData:function(){
          $.post(_ajaxPath+"mail/createMail ",{
                  "email": mail,
                  "mailserver": $("#mailserver").val(),
                  "subject": $("#title").val(),
                  "body": Base64.encode(txt)
                }, function(data, textStatus) {
                    if(data.status == 1){
                        quick.sendController();
                     } else {
                        alert("Fejl: "+data.message);
                    }
              }, "json");


        },
        buildHtml:function(data){

        },
        selectTemplate:function(){

        }


    };



    var quick = {
        numberOfRows:0,
        sendIndex:0,
        stopSend:false,
        sendAll:function(){
            var r = confirm("Er du sikker paa du vil sende mail");
            if(r){
              $("#sendAlle").hide()
              $("#stopSend").show()
              this.stopSend = false;
              this.numberOfRows = 0;
              this.sendIndex = 0;
              this.numberOfRows = hot.countRows()-2;
              this.sendIndex = -1;
              this.sendController();
            }
        },
        sendController:function(){
            if(this.stopSend == false ){
                if(this.numberOfRows > this.sendIndex){
                    var  statTxt = "Sender: ("+(this.numberOfRows+1)+" ud af "+(this.sendIndex+1)+")";
                    $("#status").html(statTxt);
                    this.doSendMail()
                } else {
                    var  statTxt = "Sender: ("+(this.numberOfRows+1)+" ud af "+(this.sendIndex+1)+")";
                    $("#status").html(statTxt);
                    alert("alle sendt")
                    $("#sendAlle").show()
                    $("#stopSend").hide()
                }
            }
        },
        doSendMail:function(){
            var sendGroup = "a1";
            txt =  $('#txt').val().toString();
            txt = txt.trim();

            this.sendIndex++;
            row = hot.getDataAtRow(this.sendIndex)
            mail =  row[0].toString();
            mail = mail.trim();

            var subject = $("#title").val();

            var navn = row[1];
            if(navn == null) navn = "";
            navn = ampEncode(ampEncode(navn.trim()));
            if(navn != ""){ txt = txt.replaceAll("#navn#", navn); }
            
            var email = row[0]; 
            if(email == null) email = "";
            email = ampEncode(ampEncode(email.toString().trim()));
            if(email != ""){ txt = txt.replaceAll("#email#", email); }
                 
            var data1 = row[2]; 
            if(data1 == null) data1 = "";
            subject = subject.replaceAll("#data1#", data1);
            data1 = ampEncode(ampEncode(data1.toString().trim()));
             txt = txt.replaceAll("#data1#", data1);


            var data2 = row[3]; 
            if(data2 == null) data2 = "";
            subject = subject.replaceAll("#data2#", data2);
            data2 = ampEncode(ampEncode(data2.toString().trim()));
             txt = txt.replaceAll("#data2#", data2);

                                                        
            var data3 = row[4]; 
            if(data3 == null) data3 = "";
            subject = subject.replaceAll("#data3#", data3);
            data3 = ampEncode(ampEncode(data3.toString().trim()));
             txt = txt.replaceAll("#data3#", data3);

            
            $.post(_ajaxPath+"mail/createMail",{
                  "send_group":$("#group-id").val(),
                  "mailserver": $("#mailserver").val(),
                  "email": mail,
                  "subject": subject,
                  "body": Base64.encode(txt),
                  "sendtime": ($('#sendtimecheck').is(':checked') ? $('#sendtime').val() : ""),
                 "priority": ($('#prioritet').is(':checked') ? $('#prioritet').val() : 0),

                }, function(data, textStatus) {
                    if(data.status == 1){
                        quick.sendController();
                     } else {
                        alert("Fejl: "+data.message);
                    }
              }, "json");
         },
        doStopSend:function(){
            this.stopSend = true;
                         alert("Stoppet")
                $("#sendAlle").show()
            $("#stopSend").hide()
        },
        sendTest:function(){
            var sendGroup = "1";
            var txt =  $('#txt').val().toString();
            txt = txt.trim();

            var subject = $("#title").val()

            var testnavn = $("#testnavn").val();
            testnavn = ampEncode(testnavn);
            testnavn = ampEncode(testnavn);
            if(testnavn != ""){ txt = txt.replaceAll("#navn#", testnavn); }
            
            var testemail = $("#testemail").val();
            testemail = ampEncode(testemail);
            testemail = ampEncode(testemail);
            if(testemail != ""){ txt = txt.replaceAll("#email#", testemail); }
            
            var testdata1 = $("#testdata1").val();
            testdata1 = ampEncode(testdata1);
            testdata1 = ampEncode(testdata1);
            txt = txt.replaceAll("#data1#", testdata1);
            subject = subject.replaceAll("#data1#", $("#testdata1").val());
            
            var testdata2 = $("#testdata2").val();
            testdata2 = ampEncode(testdata2);
            testdata2 = ampEncode(testdata2);
            txt = txt.replaceAll("#data2#", testdata2);
            subject = subject.replaceAll("#data2#", $("#testdata2").val());


            var testdata3 = $("#testdata3").val();
            testdata3 = ampEncode(testdata3);
            testdata3 = ampEncode(testdata3);
            txt = txt.replaceAll("#data3#", testdata3);
            subject = subject.replaceAll("#data3#", $("#testdata3").val());

            var mail =  $('#testmail').val().toString();
            mail = mail.trim();

            $.post(_ajaxPath+"mail/createMail ",{
                  "email": mail,
                  "send_group":$("#group-id").val(), 
                  "mailserver": $("#mailserver").val(),
                  "subject": subject,
                  "body": Base64.encode(txt),
                  "sendtime": ($('#sendtimecheck').is(':checked') ? $('#sendtime').val() : ""),
                "priority": ($('#prioritet').is(':checked') ? $('#prioritet').val() : 0)

                }, function(data, textStatus) {
                    if(data.status == 1){
                        alert("Test mail sendt")

                    } else {
                        alert("Fejl: "+data.message);
                    }
                }, "json");
        }
    }

String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

function ampEncode(str)
{

var str2 = str.replace("æ", "&aelig;");
var str3 = str2.replace("ø", "&oslash;");
var str4 = str3.replace("å", "&aring;");
var str5 = str4.replace("Æ", "&AElig;");
var str6 = str5.replace("Ø", "&Oslash;");
var str7 = str6.replace("Å", "&Aring;");

var str8 = str7.replace("Ö", "&Ouml;");
var str9 = str8.replace("ö", "&ouml;");
var str10 = str9.replace("ä", "&auml;");
var str11 = str10.replace("Ä", "&Auml;");

var str12 = str11.replace("ü", "&uuml;");
var str13 = str12.replace("Ü", "&Uuml;");
var str14 = str13.replace("é", "&eacute;");
var str15 = str14.replace("á", "&aacute;");

var str16 = str15.replace("æ", "&aelig;");
var str17 = str16.replace("ø", "&oslash;");
var str18 = str17.replace("å", "&aring;");
var str19 = str18.replace("Æ", "&AElig;");
var str20 = str19.replace("Ø", "&Oslash;");
var str21 = str20.replace("Å", "&Aring;");

var str22 = str21.replace("Ö", "&Ouml;");
var str23 = str22.replace("ö", "&ouml;");
var str24 = str23.replace("ä", "&auml;");
var str25 = str24.replace("Ä", "&Auml;");

var str26 = str25.replace("ü", "&uuml;");
var str27 = str26.replace("Ü", "&Uuml;");
var str28 = str27.replace("é", "&eacute;");
var str29 = str28.replace("á", "&aacute;");
var str30 = str29.replace("ö", "&ouml;");
return str30;


}



    /**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/
var Base64 = {

// private property
_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

// public method for encoding
encode : function (input) {
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

        output = output +
        this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
        this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

    }

    return output;
},

// public method for decoding
decode : function (input) {
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

// private method for UTF-8 encoding
_utf8_encode : function (string) {
    string = string.replace(/\r\n/g,"\n");
    var utftext = "";

    for (var n = 0; n < string.length; n++) {

        var c = string.charCodeAt(n);

        if (c < 128) {
            utftext += String.fromCharCode(c);
        }
        else if((c > 127) && (c < 2048)) {
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

// private method for UTF-8 decoding
_utf8_decode : function (utftext) {
    var string = "";
    var i = 0;
    var c = c1 = c2 = 0;

    while ( i < utftext.length ) {

        c = utftext.charCodeAt(i);

        if (c < 128) {
            string += String.fromCharCode(c);
            i++;
        }
        else if((c > 191) && (c < 224)) {
            c2 = utftext.charCodeAt(i+1);
            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
            i += 2;
        }
        else {
            c2 = utftext.charCodeAt(i+1);
            c3 = utftext.charCodeAt(i+2);
            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }

    }

    return string;
}

}

/*

                                 var jqxhr = $.post( "callHandler.php",{
           "action":"mailController",
           "function":"send",
           "email": mail,
           "subject": $("#title").val(),
            "body": Base64.encode(txt)
           }, function(result) {

       })
           .fail(function() {
               alert( "error" );
           })

*/

</script>