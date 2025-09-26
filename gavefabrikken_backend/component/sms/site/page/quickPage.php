<table border=0 width=800 >
<tr>
<td width="320" valign="top" style="color:white;">
    <fieldset style="width:300px">
        <legend>TEST TLF.</legend>
        <input id="tlf" style="width:150px;" value="" /><button onclick="sms.send();">Send test</button>
    </fieldset>
    <br />

    <fieldset style="width:300px;">
        <legend>SMS TITEL:</legend>

        <input id="smsTitle" style="width:280px;" >
         <br />
    </fieldset>
    <br />
    <fieldset style="width:300px;">
        <legend>SMS TEKST:</legend>

        <textarea id="smsTxt" rows="10" style="width: 280px;"></textarea>
        <div><label id="chrNumber" style="font-size:12px;">0 ud af 160 tegn er benyttet</label></div>
    </fieldset>
    <br /><br />

</td>
    <td width="30"></td>
<td  width="300" valign="top">
    <div style="width: 100%; text-align: right; height: 30px;">
        <button style="float:right;" onclick="quick.sendAll()">SEND ALLE</button>
    </div>

    <hr />
    <div id="hot" style="color:black;"></div>
</td>
</tr>
</table>
<script src="js/quick.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>t>
<script>


    var dataObject = [
        {tlf:53746555 , navn: 'ulrich'}
    ];
    var hotElement = document.querySelector('#hot');
    var hotSettings = {
        data: dataObject,
        columns: [
            {
                data: 'tlf',
                type: 'text'
            },
            {
                data: 'navn',
                type: 'text'
            }],
        colWidths: [80,260],
        height: 441,
        minSpareRows: 1,

        rowHeaders: true,
        colHeaders: [
            'Telefonnr',
            'navn'
        ]
    };
    var hot = new Handsontable(hotElement, hotSettings);
    var quick = {
        numberOfRows:0,
        sendIndex:0,

        sendAll:function(){
            var r = confirm("Er du sikker på du vil sende sms");
            if(r){
              this.numberOfRows = hot.countRows()-2;
              alert(this.numberOfRows)
              this.sendIndex = -1;
              this.sendController();

            }
        },
        sendController:function(){


            if(this.numberOfRows > this.sendIndex){
                this.doSendSms()
            } else {
                alert("all sendt2")
            }
        },
        doSendSms:function(){
            smsTxt =  $('#smsTxt').val().toString();
            smsTxt = smsTxt.trim();

            this.sendIndex++;
            row = hot.getDataAtRow(this.sendIndex)
            tlf =  row[0].toString();
            navn = row[1].toString();

            tlf = tlf.trim();
            navn = navn.trim();
             console.log(tlf)
            if(navn != ""){ smsTxt = smsTxt.replace("#navn#", navn); }

/*
            setTimeout(function(){
                    quick.sendController();
            }, 2000)
            */

            var jqxhr = $.post( "page/callHandler.php",{
                "action":"smsController",
                "function":"send",
                "txt":smsTxt,
                "nr":tlf,
                "smsTitle":$("#smsTitle").val()}, function(result) {
                    setTimeout(function(){
                        quick.sendController();
                    }, 100)
                })
           .fail(function() {
               alert( "error i linje:"+ this.sendIndex);
           })



        }





    }


</script>