export default class SearchTp {


    static superSearchLayout(){
        return `
        <center>
        <input id="textSupersearch" type="text" style="width:300px;padding:3px;" /> <button style="padding:3px;" id="doSupersearch">S&oslash;g</button> <button style="padding:3px;" id="doSuperMail">S&oslash;g i mails</button>
            <br><br>
            <div id="supersearchResult"></div>
        </center>`;
    }
    static superSearchEmail(data){
      //let lengthWarning = data.data.length > 30 ?  "Ikke alle resultater vises da der max vises 30 i listen <br>":"";
      if(data.data.length == 0) return "<p>Der blev ikke fundet p&aring; den indtastede email. <br> Du skal skrive den eksakste email f&oslash;r for at systemet kan finde den. </p>"


      return `<table class="customTable">` +
        `<tr><th>Dato</th><th>Subject</th><th>Send status</th><th>Fejl</th><th></th></tr>`+
        data.data.map((i) => {
            let att = i.attributes;
            console.log(att);
            let sendStatus = att.sent == 1 ? "Sendt":"Ej sendt";

            let sendError =  att.error == 1 ? att.error_message : "";
            let sent_datetime_date =  att.sent_datetime == null ? "" : att.sent_datetime.date;


            return `<tr><td>${sent_datetime_date}</td><td>${att.subject}</td><td>${sendStatus}</td><td>${sendError}</td><td><button data-id="${att.id}" class="search-mail-show" style="width:70px;">L&oelig;s mail</button></td></tr>`

        }).join('')+ `</table>`;



    }

    static onlyCompany(data){
        let lengthWarning = data.data.length > 30 ?  "Ikke alle resultater vises da der max vises 30 i listen <br>":"";
        return `<h3 style="color:red;">${lengthWarning}</h3><table width=600>` +
        data.data.map((i) => {
            i.present_no = i.present_no == "none" ? "":i.present_no;
            return `
                <tr><td colspan=2><h3>Virksomhed info</h3></td></tr>
                <tr><td>Virksomhedsnavn</td><td>${i.name}</td></tr>
                <tr><td>cvr / ean</td><td>${i.cvr} / ${i.ean}</td></tr>
                <tr><td>Kontaktperson</td><td>${i.contact_name}</td></tr>
                <tr><td>Kontaktperson mobil</td><td>${i.contact_phone}</td></tr>
                <tr><td>Kontaktperson email</td><td>${i.contact_email}</td></tr>
                <tr><td colspan=2><hr> </td><td></td></tr>
                <tr><td colspan=2> </td><td></td></tr>
            `;
        }).join('') +  `</table>`

    }
    static companyLayout(data){

        let lengthWarning = data.data.length > 30 ?  "Ikke alle resultater vises da der max vises 30 i listen <br>":"";
        let partHtml = "";

        return `<h3 style="color:red;">${lengthWarning}</h3><table width=600>` +
        data.data.map((i) => {
            let loadCompanyData = "";
            i.blocked = i.blocked == "1" ? "Blokeret / slettet":"Aktivt";
            i.welcome_mail_is_send = i.welcome_mail_is_send == "1" ? "Sendt":"Ikke sendt";
            if (i.is_email == 1) i.welcome_mail_is_send = "Fysisk kort, ingen send status"
            console.log(i)
            if (i.pid != 0){
               i.contact_email = "";
               i.contact_phone = "";
               i.contact_name = "";
               i.ean  = "";
               i.cvr  = "";
               i.name = "";
               loadCompanyData =  "<tr><td colspan=2> </td><td></td></tr>"
            }

            let returnHtml = `
                <table width=600>
                <tr><td >Kort Status</td><td>${i.blocked}</td></tr>
                <tr><td >Gave</td><td>Ingen gave valgt p&aring; kortet</td></tr>
                <tr><td>Kortnr.</td><td><b>${i.username}</b></td></tr>
                <tr><td width=200>password</td><td>${i.password}</td></tr>
                <tr><td>Udl&oslash;bsdato</td><td>${i.expire_date}</td></tr>
                <tr><td>Kort type</td><td>${i.shop_name} - ${i.certificate_value}</td></tr>
                <tr><td>Velkomst mail sendt</td><td>${i.welcome_mail_is_send}</td></tr>
                <tr><td>BS-nummer</td><td>${i.order_no}</td></tr>
                <tr><td>Numre-range</td><td>${i.certificate_no_begin} - ${i.certificate_no_end}</td></tr>
                <tr><td>S&oelig;lger</td><td>${i.salesperson}</td></tr>
                <tr><td colspan=2><h3>Virksomhed info</h3></td></tr>
                <tr><td>Virksomhedsnavn</td><td>${i.name}</td></tr>
                <tr><td>cvr / ean</td><td>${i.cvr} / ${i.ean}</td></tr>
                <tr><td>Kontaktperson</td><td>${i.contact_name}</td></tr>
                <tr><td>Kontaktperson mobil</td><td>${i.contact_phone}</td></tr>
                <tr><td>Kontaktperson email</td><td>${i.contact_email}</td></tr>
                <tr><td><button data-id="${i.company_id}" class="search-goto-company">G&aring; til Virksomheden</button></td></tr>
                ${loadCompanyData} `;

            if('replacement_id' in i){
                if(i.replacement_id > 0 ){
                returnHtml =  `
                <table width=600>
                <tr><td >Kort Status</td><td>${i.blocked}</td></tr>
                <tr><td >Gave</td><td>Ingen gave valgt p&aring; kortet</td></tr>
                <tr><td>Kortnr.</td><td><b>${i.username}</b></td></tr>
                <tr><td width=200>password</td><td>${i.password}</td></tr>
                <tr><td>Udl&oslash;bsdato</td><td>${i.expire_date}</td></tr>
                <tr><td>Kort type</td><td>${i.shop_name} - ${i.certificate_value}</td></tr>
                <tr><td colspan=2><b style="color:red">Kortet er et erstatningskort</b> </td></tr>
                <tr><td><button data-id="${i.replacement_id}" class="search-goto-company-replacement">G&aring; til Virksomheden</button></td><td><button data-id="${i.replacement_id}" class="search-show-org">Vis det oprindelige kort</button></td></tr> `
                }
            } else {
                returnHtml+=  `<tr>
             `;
            }
            returnHtml+=  `<tr><td colspan=2><hr> </td><td></td></tr>
            <tr><td colspan=2> </td><td></td></tr>`
            console.log(returnHtml)
            return ` ${returnHtml}  `
        }).join('')  +  `</table>`
    }

    static replacementLastYear(data){
        console.log(data);
    }


    static invoiceLayout(data){
          console.log("invice")
        let lengthWarning = data.data.length > 30 ?  "Ikke alle resultater vises da der max vises 30 i listen <br>":"";

        return `<h3 style="color:red;">${lengthWarning} </h3><table width=600>` +
        data.data.map((i) => {
            let isDelivery =  i.is_delivery == "1" ? `<div ><br><button data-id="${i.su_shopuser_id}" class="search-track-trace">Track and trace</button></div><br>`:``;
            let warningReplace = i.is_replaced == "1" ? `<div style="color:red;">Kortet er blevet erstattet <br><button data-id="${i.su_shopuser_id}" class="search-show-replacement">Vis erstatningskort info</button></div><br>`:``;
            let warningType = i.shop_is_company == "1" ?  "Kortet er fra en valgshop":"";
            i.present_no = i.present_no == "none" ? "":i.present_no;
            let parentCompany =  ` <tr><td colspan=2><h3>Virksomhed info </h3></td></tr>
                <tr><td>Virksomhedsnavn</td><td>${i.name}</td></tr>
                <tr><td>cvr / ean</td><td>${i.cvr} / ${i.ean}</td></tr>
                <tr><td>Kontaktperson</td><td>${i.contact_name}</td></tr>
                <tr><td>Kontaktperson mobil</td><td>${i.contact_phone}</td></tr>
                <tr><td>Kontaktperson email</td><td>${i.contact_email}</td></tr>
                <tr><td><button data-id="${i.company_id}" class="search-goto-company">G&aring; til virksomheden</button> </td><td></td></tr>
                `;
            let childCompany =  `
                <tr><td colspan=2><button data-id="${i.pid}" class="search-show-company-info">Vis virksomhed informationer</button></td></tr>
                <tr><td colspan=2><h3>Leveringsadresse (Child)</h3></td></tr>
                <tr><td>Leveringsvirksomhed Navn</td><td>${i.ship_to_company}</td></tr>
                <tr><td>Kontaktperson</td><td>${i.ship_to_attention}</td></tr>
                <tr><td>Adresse </td><td>${i.ship_to_address} </td></tr>
                <tr><td>Adresse 2</td><td>${i.ship_to_address_2}</td></tr>
                <tr><td>Postnr.</td><td>${i.ship_to_postal_code}</td></tr>
                <tr><td>Bynavn</td><td>${i.ship_to_city}</td></tr>
                <tr><td><button data-id="${i.company_id}" class="search-goto-company">G&aring; til leveringsadressen</button> </td><td></td></tr>
                `;
           let companyData = i.pid == 0 ?  parentCompany:childCompany;
           if('replacement_id' in i){
                if(i.replacement_id > 0  ){
                   companyData =  `<tr><td colspan=2><b>Kortet er et erstatningskort</b> </td></tr>
                   <tr><td><button data-id="${i.replacement_id}" class="search-goto-company-replacement">G&aring; til Virksomheden</button></td><td> </td></tr>
                   `
                }
           }


           let shipmentInfo = "";

           console.log(i);
              if(i.hasOwnProperty('shipment_id') && i.shipment_id > 0) {
                  shipmentInfo = '<tr><td >Leverance nr</td><td>' + i.shipment_id + '</td></tr>'
              }

            return `
                <tr><td colspan=2><b>${warningReplace}</b></td></tr>
                <tr><td colspan=2><b>${warningType}</b></td></tr>
                <tr><td >Ordrenummer</td><td>${i.order_no}</td></tr>
                ${shipmentInfo}
                <tr><td>Kort</td><td><b>${i.user_username}</b></td></tr>
                <tr><td colspan=2><b>Gavevalg info <i data-id="${i.shopuser_id}" class="bi bi-pencil-square search-quck-masterdata-edit"></i></b></td><td></td></tr>
                <tr><td>Tidspunkt</td><td>${i.order_timestamp}</td></tr>
                <tr><td>Navn</td><td>${i.user_name}</td></tr>
                <tr><td>Email</td><td>${i.user_email}</td></tr>
                <tr><td>Gave</td><td>${i.model_name} - ${i.present_no}</td></tr>
                <tr><td colspan=2><img src="${i.media_path}" alt="" width=100 /> </td></tr>
                <tr><td colspan=2>${isDelivery} <hr> </td></tr>

                ${companyData}


                <tr><td colspan=2><hr> </td><td></td></tr>
                <tr><td colspan=2> </td><td></td></tr>
            `;
        }).join('') +  `</table>`




    }
    static cardLayout(data){
        console.log(data)
        return ``;
    }
    static cardMasterData(data,shopuserID,orderstate){
        let bn = "";

        if(orderstate > 2 ){
          bn = ` <button data-id=${shopuserID} class="btn btn-outline-primary searchUpdateMasterData" style="float:right; margin-right:10px"  >Opdatere data</button> `;
        } else {
           bn = `<button data-id=${shopuserID} class="btn btn-outline-danger searchUpdateMasterData" style="float:right; margin-right:10px"  >Opdatere data (Gaven er sendt, &oelig;ndringerne vil kun blive gemt i CS og ikke komme over i NAV)</button>`;
        }

        return `<div >
        <button class="btn btn-outline-primary searchGoBack" style="float:left; margin-left:10px"  >TILBAGE TIL S&Oslash;GERESULTATER</button><br><br><hr>
        <table class="customTable" width=100%>` +
         data.data.map((i) => {
           return `<tr><td width=30%>${i.name}</td><td width=70%><input class="searchMasterDataElement" style="width:90%" type="text" attr-id="${i.attribute_id}"  org-val="${i.attribute_value}" is-name="${i.is_name}" is-email="${i.is_email}" value="${i.attribute_value}"  /></td></tr>`
        }).join('') + `</table>
        <hr>
        ${bn}
        <br> <br>
        </div>`;
    }

}

/*
        <div style="display:inline; margin-right:10px;">
            <input type="radio" id="supersearchCardnr" name="supersearchChoise" value="cardnr" checked> <label for="supersearchCardnr">Kortnr</label>
        </div>
        <div style="display:inline; margin-right:10px;">
            <input type="radio" id="supersearchInvoice" name="supersearchChoise" value="invoice"> <label for="supersearchInvoice">Kvitteringsnr</label>
        </div>

*/
