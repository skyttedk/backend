
var shopWelcomeMail = {
    init:function() {
        ajax({shop_id:_editShopID},"unit/valgshop/mails/sendStatus","shopWelcomeMail.sendStatusRes");
    },
    sendStatusRes:function(res){
        let sendStatus;
        if (res.send == null){
            sendStatus = null;
        } else {
            const timestamp = res.send.date;
            const parts = timestamp.split(".");
            sendStatus = parts[0];
        }
         $("#shopWelcomeMailAction").html(this.buildUI(sendStatus));
         this.initEvent();
    },
    sendMail:function(){
        let recipient_email = $("#velcomeEmail").val();
        let subject = $("#velcomeSubject").val();
        let content = $("#velcomeBody").val();
        let postBody = {
            language_id:1,
            shop_id:_editShopID,
            recipient_email:recipient_email,
            subject:subject,
            content:content,
            resend:1
        };
        r = confirm("Ønsker du at sende mailen?")
        if(!r) return;
        $("#shopWelcomeMailAction").html("Mailen sendes...");
        ajax(postBody,"unit/valgshop/mails/valgshopwelcome","shopWelcomeMail.isSend");

    },
    isSend:function(res){
        shopWelcomeMail.init();
    },
    initEvent:function(){
        let self = this;
        $("#openVelkomstMail").on("click", function() {
            $( "#dialog-send-mail" ).dialog({
                height: 450,
                width: 550,
                modal: true,
                buttons: {
                    Cancel: function() {
                        $( this ).dialog( "close" );
                    },
                    Send: function(){

                        $( this ).dialog( "close" );
                        self.sendMail();
                    }
                }
            });
        });

    },

    buildUI:function (initVal){
        $("#velcomeEmail").val($("#shopEmail").val());
        let bodyContent = this.bodyContent();
        $("#velcomeBody").val(bodyContent)
        $("#velcomeShowBody").html(bodyContent)

        let html = "";
         if(initVal == null){
            return `
                <button id="openVelkomstMail">Opret velkomst mail</button>
            `
        } else {
             return `
                <p>Sendt D. ${initVal}</p>
            `
         }


    },
    textToHtml:function(text) {
        return text.replace(/\n/g, "<br>");
    },
    bodyContent:function(){
        let shopName = $("#shopName").val();
        return `
        <p>K&aelig;re ${shopName}</p>
     
        <p>Tak fordi I har valgt os som leverand&oslash;r af jeres julegaver i &aring;r. Vi gl&aelig;der os til at hj&aelig;lpe jer gennem processen og selvf&oslash;lgelig til at levere gaverne.&nbsp;</p>
        <p>Jeres bestilling er nu overg&aring;et til vores salgssupportafdeling, som sikrer ops&aelig;tning af gaveshoppen samt hele forl&oslash;bet. N&aring;r vi n&aelig;rmer os tiden for jeres julegaveshop, bliver I kontaktet af en supportmedarbejder, der bliver tilknyttet som jeres personlige kontaktperson, der vil hj&aelig;lpe jer gennem hele forl&oslash;bet.&nbsp;</p>
        <p>For at kunne f&aelig;rdigg&oslash;re jeres gaveshop, har vi brug for nedenst&aring;ende:&nbsp;</p>
        <ul>
        <li>En medarbejderliste (se vedh&aelig;ftede skabelon til udfyldning)</li>
        <li>Evt. velkomsttekst til forsiden af shoppen (standardtekst er vedh&aelig;ftet til inspiration)</li>
        <li>Faktureringsoplysninger pr. faktura, hvis faktura skal splittes op.&nbsp;</li>
        </ul>
        <p>Ovenst&aring;ende vil jeres kontaktperson informere jer om, n&aring;r vi n&aelig;rmer os. Listen og vedh&aelig;ftninger er blot til forel&oslash;big information.&nbsp;</p>
        <p>Skulle I have sp&oslash;rgsm&aring;l inden I bliver kontaktet af jeres supportkontaktperson, er I selvf&oslash;lgelig meget velkomne til at kontakte vores salgssupportafdeling p&aring; <a href="mailto:support@gavefabrikken.dk">support@gavefabrikken.dk</a> eller jeres salgskonsulent.&nbsp;</p>
        <p>Vi gl&aelig;der os til samarbejdet.&nbsp;</p>
        <p>Med venlig hilsen<br /> <strong>GaveFabrikken A/S</strong></p>`
    }
}




