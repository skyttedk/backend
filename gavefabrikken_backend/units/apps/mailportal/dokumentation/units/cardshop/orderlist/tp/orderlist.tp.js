export default class OrderlistTp {


    static editOrder(data){
        let d = data.data.result[0];
        let checked = ["","",""];


        let prepaymentReadonly = "";
        let prepaymentNote = "";
        console.log(d);
        if(d.prepayment == 1 && d.order_state >= 5){
            prepaymentNote = "forudfakturering sendt til nav, kan ikke ændres";
            prepaymentReadonly = "disabled";
        }

        console.log(d.prepayment_date);
        if(d.prepayment_date != null && d.prepayment_date != "") {
            d.prepayment = 2;

        }
        checked[d.prepayment] = "checked";

        return `
        <br><div>
            <label><b>Ordrer indstillinger:</b></label>
            <hr>
            <div class="row ">
                <div class="col-6" >
                    <label style="color:red">Antal gratis kort:</label>
                    <input value="${d.free_cards}" id="editOrderFreeAmountInput" type="number" min="0" max="100">
                </div>
                <div class="col-6" style="display: none;" >
                    <label style="">Sælger:</label>
                    <input value="${d.salesperson}" type="text" disabled size="6"> <button id="changesalespersonshow">skift</button>
                    <div style="display: none; padding-top: 5px; margin-top: 5px; padding-bottom: 5px; margin-bottom: 5px; border-top: 1px solid #999999; border-bottom: 1px solid #999999;">
                        Anmod om skift af sælger (skal godkendes)<br>
                        <select id="changesalesperson"></select><br>
                        Kommentar: <input type="text" id="changesalespersoncomment" size="20"><br>
                        <button id="changesalespersonsave">skift</button>
                    </div>
                </div>
            </div>
            <br>
            <div class="col-12">
                <label>Faktureringsmetode: <i>${prepaymentNote}</i></label><br>
                <div class="row">
                    <div class="col-6"><label for="automatisk"><input type="radio" ${checked[1]} ${prepaymentReadonly} name="editOrderPrepayment" value="1" id="automatisk">  Ja, acontofakturer automatisk</label><br></div>
                   
                </div>
            </div>
        
            <div class="row"  style="margin-top: 10px; ">
                <div class="col-6"><label for="ingen"><input type="radio" ${checked[0]} ${prepaymentReadonly} name="editOrderPrepayment" value="0" id="ingen">  Nej, ingen acontofakturering/fakturering ved levering</label><br></div>
                <div class="col-6"><label for="prepdato"><input type="radio" ${checked[2]} name="editOrderPrepayment" value="2" id="prepdato"> Senere fakturering (før leveringsuge):</label> <input type="date" name="prepaymentdate" value="${d.prepayment_date}"></div>
            </div>
            
            <div class="row" id="prepaymenteditwarn" style="display: none; ">
                <div class="col-12">
                    <div class="warning alert-warning" role="warning">
                        <b>Bemærk:</b> ændring i valg af fakturering som ikke er alm. acontofakturering skal godkendes af økonomi.
                    </div>
                </div>
            </div>
          
            <br>
            <div class="row ">
                <div class="col-4">
                    <label>Kundens reference:</label><br>
                    <input id="editOrderReference" type="text" maxlength="35" value="${d.requisition_no}" style="width:90%; padding:3px;">
                </div>
                 <div class="col-2"></div>
                 <div class="col-4">

                    
                </div>
            </div><br>
            <div class="row ">
                <div class="col-4">
                    <label>Note på ordrebekræftelse:</label><br>
                    <textarea id="ordernote" rows="4" cols="40">${d.ordernote}</textarea>
                    
                </div>
                <div class="col-2"></div>
                <div class="col-4">
                    <label>Interne noter:</label><br>
                    <textarea id="internenoter" rows="4" cols="40">${d.salenote}</textarea>
                </div>
            </div>


            <br><br>
        </div>
        `;

    }
    static mailOptions(id,btnNavn){
        return `
        <div>
            <button data-id=${id} class="btn btn-outline-primary editOrderNewCodeMail"  >${btnNavn}</button>
            <hr>
        </div>
        `
    }
    static pdfOptionsSingle(id,token){
        return `
             <button class="btn btn-outline-info" style="float:left; margin-right:10px;"  ><a href="https://system.gavefabrikken.dk/kundepanel/printcardszip.php?id=${id}&token=${token}" target="_blank">pdf koder(ZIP enkeltvis)</a></button>
        `
    }
    static pdfOptions(id,token){
        return `
             <button class="btn btn-outline-info" style="float:left; margin-right:10px;"  ><a href="https://system.gavefabrikken.dk/kundepanel/printcards.php?id=${id}&token=${token}" target="_blank">pdf koder</a></button>
            
        `
    }
    static csvOptions(orderid){
        return `
             <button class="btn btn-outline-info" style="float:left; margin-right:10px;"  ><a href="https://system.gavefabrikken.dk/kundepanel/jhfgdtyfe345sadaj356.php?orderid=${orderid}" target="_blank">Download csv koder</a></button>
        `
    }

}

