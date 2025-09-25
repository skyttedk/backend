
export default class EarlyorderTab {
    static earlyOrderPresent(data,unique="",i=""){

            return ` <div class="row earlyorderOrderList${unique}">
                    <div class="col-6 justify-content-center align-self-center">
                        <input class="CustomItemnrSwitch ${unique}${i}" type="checkbox" /> <label> Valgfrit varenr. </label>  <br>
                        <input  class="form-control earlyOrderCustomItemnr ${unique}${i}" type="text"  />
                        <select data-id="${unique}"  class="${unique}${i}"><option  selected="" value="">V&oelig;lg</option>`+
            data.map((i) => {
                return `
                        <option value='${i.attributes.item_nr}'"   >${i.attributes.description}</option>`
            }).join('') +
            `</select></div><div class="col-4">Antal: <input style="width:100px;" type="text" value="0" class="form-control earlyOrderPresentAmount ${unique}${i}"  ></div>
            </div>`
            ;

    }
    
    static earlyOrderMetadata(unique = "",data={}){

        var buttons = '';

        if(data.shipment_state == 0 || data.shipment_state == 1 || data.shipment_state == 4) {
            if(data.hasOwnProperty('deleted_date') && data.deleted_date != null) {
                buttons += ' <button class="btn btn-warning btn-sm earlyorder-reopen">gendan earlyordre</button> ';
            }
            else {
                buttons += ' <button class="btn btn-warning btn-sm earlyorder-delete">slet earlyordre</button> ';
            }
        }

        if(data.shipment_state != 2 && data.shipment_state != 5 && data.shipment_state != 6 && data.shipment_state != 7) {
            buttons += '<button class="btn btn-primary btn-sm earlyorder-edit">rediger</button> ';
            buttons += '<button class="btn btn-sm earlyorder-cancel" style="display: none;">annuller</button> ';
            buttons += '<button class="btn btn-sm btn-primary earlyorder-save" style="display: none;">opdater</button> ';
        }

        var statusHtml = '<div style="float: right;">'+buttons+'</div><div style="padding-bottom: 5px; font-size: 18px; font-weight: bold;">Earlyordre leverance #'+data.id+'</div><div class="row" style="clear: both; padding: 10px; margin-bottom: 10px; border-radius: 10px;background: #FAFAFA;">';

        if(data.hasOwnProperty('order_no')) {
            statusHtml += `
                <div class="col-3 justify-content-center align-self-center" style="font-weight: bold; padding-bottom: 5px;">Tilknyttet ordre :</div>
                <div class="col-3" style="padding-bottom: 5px;"><input type="text" class="ordernrinput" disabled value="${data.order_no}"></div>
            `;
        }

        if(data.hasOwnProperty('created_date')) {
            statusHtml += `
                <div class="col-3 justify-content-center align-self-center" style="font-weight: bold; padding-bottom: 5px;">Oprettet d. :</div>
                <div class="col-3" style="padding-bottom: 5px;"><input type="text" disabled value="${data.created_date}"></div>
           `;
        }

        if(data.hasOwnProperty('deleted_date') && data.deleted_date != null) {
            statusHtml += `
                <div class="col-3 justify-content-center align-self-center" style="font-weight: bold; padding-bottom: 5px;">Slettet d. :</div>
                <div class="col-3" style="padding-bottom: 5px;"><input type="text" disabled value="${data.deleted_date}"></div>
           `;
        }

        if(data.hasOwnProperty('shipment_state')) {
            var shipStates = {0: '0: Afventer', 1: '1: Klar til afsendelse', 2: '2: Sendt til navision', 3: '3: Fejl i overførsel', 4: '4: Blokkeret'};
            var shipmentStatus = 'Ukendt status ('+data.shipment_state+')';
            if(shipStates.hasOwnProperty(data.shipment_state)) shipmentStatus = shipStates[data.shipment_state];
            statusHtml += `
                <div class="col-3 justify-content-center align-self-center" style="font-weight: bold; padding-bottom: 5px;">Status : </div>
                <div class="col-3" style="padding-bottom: 5px;"><input type="text" disabled value="${shipmentStatus}"></div>
            `;
        }

        if(data.hasOwnProperty('shipment_sync_date') && data.shipment_sync_date != null) {
            statusHtml += `
                <div class="col-3 justify-content-center align-self-center" style="font-weight: bold; padding-bottom: 5px;">Sendt til navision d. :</div>
                <div class="col-3" style="padding-bottom: 5px;"><input type="text" disabled value="${data.shipment_sync_date}"></div>
            `;
        }

        if(data.hasOwnProperty('consignor_created') && data.consignor_created != null) {
            statusHtml += `
                <div class="col-3 justify-content-center align-self-center" style="font-weight: bold; padding-bottom: 5px;">Sendt fra navision d. :</div>
                <div class="col-3" style="padding-bottom: 5px;"><input type="text" disabled value="${data.consignor_created}"></div>
            `;
        }


        
        return statusHtml + '</div>';
        
    }
    
    static earlyOrderContact(unique = "",data={}){
        
        return `
             <div class="row">
                <div class="col-3 justify-content-center align-self-center"><label><b>Kontakt person:</b></label></div>
                <div class="col-9"><input style="" value="${data.shipto_contact}" type="email" class="form-control AdditionalOrderFormContact ${unique}"  ></div>
            </div>
             <div class="row">
                <div class="col-3 justify-content-center align-self-center"><label><b>Email:</b></label></div>
                <div class="col-9"><input value="${data.shipto_email}"  type="text" class="form-control AdditionalOrderFormEmail ${unique}"  ></div>
            </div>
             <div class="row">
                <div class="col-3 justify-content-center align-self-center"><label><b>Mobilnr:</b></label></div>
                <div class="col-9"><input value="${data.shipto_phone}" style="width:200px;" type="text" class="form-control AdditionalOrderFormMobile ${unique}"  ></div>
            </div>

        `;
    }
   static shippingForm(unique = "",data={}){

        let lang = this.translation(1);
        return `
            <br>
            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.land}:</div>
                <div class="col-9">
                    <select name="ship_to_country" id="language_code" class="language_code ${unique} ">
                        <option value="1">${lang.land_1}</option>
                        <option value="4">${lang.land_4}</option>
                        <option value="5">${lang.land_5}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.virksomhed}:</div>
                <div class="col-9"><input type="text" value="${data.shipto_name}"  class="form-control ${unique} ship_to_company" id="ship_to_company" placeholder="${lang.virksomhed}"></div>
            </div>


            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.adress1}:</div>
                <div class="col-9"><input type="text" value="${data.shipto_address}"  class="form-control mandatory ${unique} ship_to_address" id="ship_to_address" placeholder="${lang.adress1}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.adress2}:</div>
                <div class="col-9"><input type="text" value="${data.shipto_address2}"  class="form-control ${unique} ship_to_address_2" id="ship_to_address_2" placeholder="${lang.adress2}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">
                    ${lang.postnr}:
                </div>
                <div class="col-4">
                    <input type="text" value="${data.shipto_postcode}"  class="form-control mandatory ${unique} ship_to_postal_code" id="ship_to_postal_code" validate="zipcode" placeholder="${lang.postnr}">
                </div>
                <div class="col-5"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.bynavn}:</div>
                <div class="col-9"><input type="text" value="${data.shipto_city}"  class="form-control mandatory ${unique} ship_to_city" id="ship_to_city" placeholder="${lang.bynavn}"></div>
            </div>
        `
    }
        static translation(language){
        if(language == 1){
            return  {
                cvr:"Cvr",
                sog:"S�g",
                virksomhed:"Virksomhed",
                adress1:"Adresse 1",
                adress2:"Adresse 2",
                postnr:"Postnr.",
                bynavn:"By",
                ean:"EAN",
                billToEmail:"Faktura email",
                save:"Gem",
                kontaktperson:"Kontaktperson",
                kontaktpersonTlf:"Telefonnummer",
                kontaktpersonMail:"E-maildresse",
                gaveLevAdressTitel:"Gavernes Leveringsadresse",
                kortLevAdressTitel:"Gavekort Leveringsadresse",
                shipToAttention:"Att.",
                land:"Land",
                land_1:"Danmark",
                land_4:"Norge",
                land_5:"Sverige"

            }
        }
     }
}