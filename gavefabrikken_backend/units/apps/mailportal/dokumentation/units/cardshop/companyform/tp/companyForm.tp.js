
export default class CompanyformTp {
    static showMasterdataForm(){
     let lang = this.translation(1);
        return `

        <div id="showMasterdataForm">
         <div class="row ">
            <div class="col-12">
                 <span class="cardAccess" style="margin-left:0px; border:1px solid red; padding:2px;">BLOKERE FOR GAVEVALG <input type="checkbox" id="shutdown"> </span>
                Link til kundens backend <span >
                    <a id="dialog_Link" href="" target="_blank"> tryk her ---&gt;
                    </a></span>
                    <br>
                    <input id="dialog_url" type="text" readonly="" style="width:80%; max-width:630px;" />
            </div>
        </div>

        <br>

        <label><b>Faktura adresse</b></label> <i id="masterData-invoice-edit-btn" class="bi bi-pencil-square"></i>
         <hr>
        <div class="row ">
            <div class="col-12">
                ${this.billForm(false) }
            </div>
        </div>
        <br>
        <label><b>Kontaktperson informationer</b></label> <i id="masterData-contact-edit-btn" class="bi bi-pencil-square"></i>
        <hr>
        <div class="row">
            <div class="col-12">
                ${this.contactForm() }
            </div>
        </div>
        <br>
        <label><b>Leveringsadresse</b></label> <i id="masterData-delevery-edit-btn" class="bi bi-pencil-square"></i>
        <hr>
        <div class="row">
            <div class="col-12">
                ${this.shippingForm() }
            </div>
        </div>
        </div>
        <br>
        
        ${this.navisionForm() }`;
    }



    static createform(){
        $(".modal-footer").html(this.save())
        let lang = this.translation(1);
        return `
        <div class="row">
            <div class="col-1"></div>
            <div class="col-10">
                ${this.billForm(true) }
            </div>
            <div class="col-1"></div>
        </div>
        <hr>
        <div class="row">
            <div class="col-1"></div>
            <div class="col-10">
                ${this.contactForm() }
            </div>
            <div class="col-1"></div>


        </div>
        <hr>
        <div class="row">
            <div class="col-1"></div>
            <div class="col-10">
                <label class="companyform-switch" >
                    <input type="checkbox">
                    <span class="companyform-slider companyform-round" id="gaveLevAdressTitelOptions"></span>
                </label>
                <label id="gaveLevAdressTitel" class="text-deactivated"><b>${lang.gaveLevAdressTitel} </b></label>
                <div style='font-size:10px;'>(Udfyldes kun hvis forskellig fra virksomhedsadresse)</div>
                <div id="shippingForm" class="hide">
                    ${this.shippingForm() }
                </div>
            </div>
            <div class="col-1"></div>
        </div>

        `;

        
    }

    static lookupBillForm(id,datafak,datalev) {
        let lang = this.translation(1);
        return `

            <table id="${id}" class="lookupForm">
                <tr><td><b>Faktura addresse</b></td><td></td></tr>
                <tr><td>${lang.virksomhed}:</td><td id="datafak-virksomhed">${datafak.virksomhed}</td></tr>
                <tr><td>${lang.bynavn}:</td><td id="datafak-bynavn">${datafak.bynavn}</td></tr>
                <tr><td>${lang.postnr}: </td><td id="datafak-postnr">${datafak.postnr}</td></tr>
                <tr><td>${lang.adress1}:</td><td id="datafak-adress1">${datafak.adress1}</td></tr>
                <tr><td>${lang.adress2}:</td><td id="datafak-adress2">${datafak.adress2}</td></tr>
                <tr><td>${lang.ean}:</td><td id="datafak-ean">${datafak.ean}</td></tr>
                <tr><td></td><td ><button class="lookupTransferData" data-id="${id}">Overfør informationer</button></td></tr>
            </table><hr>
        `
    }


    static billForm(showLookup = true,unique = "") {
        let lang = this.translation(1);
        return `
            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.land}:</div>
                <div class="col-9">
                    <select name="country" id="language_code" class="${unique}">
                        <option value="1" selected>${lang.land_1}</option>
                        <option value="4">${lang.land_4}</option>
                        <option value="5">${lang.land_5}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-3 justify-content-center align-self-center">
                    ${lang.cvr}:
                </div>
                <div class="col-4">
                    <input type="text" class="form-control-custom ${unique}" id="cvr" validate="cvr" placeholder="${lang.cvr}">
                </div>
                ${ showLookup  ?  '<div class="col-1"><button class="btn btn-secondary" id="lookupCvr">Søg</button></div>' :'' }

                <div class="col-4"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.virksomhed}:</div>
                <div class="col-6"><input type="text" class="form-control-custom mandatory ${unique}" id="name" placeholder="${lang.virksomhed}"></div>
                ${ showLookup  ?  '<div class="col-1"><button class="btn btn-secondary ${unique}" id="lookupCompany">Søg</button></div>' :'' }

                <div class="col-2"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.adress1}:</div>
                <div class="col-9"><input type="text" class="form-control-custom mandatory ${unique}" id="bill_to_address" placeholder="${lang.adress1}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.adress2}:</div>
                <div class="col-9"><input type="text" class="form-control-custom ${unique}" id="bill_to_address_2" placeholder="${lang.adress2}"></div>
            </div>
            
            <div class="row">
                <div class="col-3 justify-content-center align-self-center">
                    ${lang.postnr}:
                </div>
                <div class="col-4">
                    <input type="text" class="form-control-custom mandatory ${unique}" validate="zipcode" id="bill_to_postal_code" placeholder="${lang.postnr}">
                </div>
                <div class="col-5"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.bynavn}:</div>
                <div class="col-9"><input type="text" class="form-control-custom mandatory ${unique}" id="bill_to_city" validate="onlyLetters" placeholder="${lang.bynavn}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.ean}:</div>
                <div class="col-9"><input type="text" class="form-control-custom ${unique}" id="ean" placeholder="${lang.ean}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center"><div class="searchMails" style="display: none; float: right; margin-right: -20px; cursor: pointer;" title="Se sendte e-mails"><img src="views/media/icon/1373253286_letter_64.png" style="height: 18px;"></div>${lang.billToEmail}:</div>
                <div class="col-9"><input type="text" class="form-control-custom ${unique}" id="bill_to_email" placeholder="${lang.billToEmail}"></div>
            </div>
        `
    }
    static contactForm(unique = ""){
        let lang = this.translation(1);
        return `
        <div class="row">
            <div class="col-3 justify-content-center align-self-center">${lang.kontaktperson}:</div>
            <div class="col-9"><input type="text" class="form-control-custom mandatory ${unique}" id="contact_name" placeholder="${lang.kontaktperson}"></div>
        </div>

        <div class="row">
            <div class="col-3 justify-content-center align-self-center">${lang.kontaktpersonTlf}:</div>
            <div class="col-9"><input type="text" class="form-control-custom mandatory ${unique}" validate="tele" id="contact_phone" placeholder="${lang.kontaktpersonTlf}"></div>
        </div>            

        <div class="row">
            <div class="col-3 justify-content-center align-self-center"><div class="searchMails" style="display: none; float: right; margin-right: -20px; cursor: pointer;" title="Se sendte e-mails"><img src="views/media/icon/1373253286_letter_64.png" style="height: 18px;"></div>${lang.kontaktpersonMail}:</div>
            <div class="col-9"><input type="text" class="form-control-custom mandatory ${unique}" validate="email" id="contact_email" placeholder="${lang.kontaktpersonMail}"></div>
        </div>
        `
    }
    static shippingForm(unique = ""){
        let lang = this.translation(1);
        return `
            <br>
            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.land}:</div>
                <div class="col-9">
                    <select name="ship_to_country" id="ship_to_country" class="${unique} ship_to_country">
                        <option value="1">${lang.land_1}</option>
                        <option value="4">${lang.land_4}</option>
                        <option value="5">${lang.land_5}</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.virksomhed}:</div>
                <div class="col-9"><input type="text" class="form-control-custom ${unique} ship_to_company madatory-text" id="ship_to_company" placeholder="${lang.virksomhed}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.shipToAttention}:</div>
                <div class="col-9"><input type="text" class="form-control-custom ${unique} ship_to_attention madatory-text" id="ship_to_attention" placeholder="${lang.shipToAttention}"></div>
            </div>
            
             <div class="row" style="display: none;">
                <div class="col-3 justify-content-center align-self-center">${lang.kontaktpersonTlf}:</div>
                <div class="col-9"><input type="text" class="form-control-custom ${unique} ship_to_phone" id="ship_to_phone" placeholder="${lang.kontaktpersonTlf}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.adress1}:</div>
                <div class="col-9"><input type="text" class="form-control-custom mandatory ${unique} ship_to_address madatory-text" id="ship_to_address" placeholder="${lang.adress1}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.adress2}:</div>
                <div class="col-9"><input type="text" class="form-control-custom ${unique} ship_to_address_2" id="ship_to_address_2" placeholder="${lang.adress2}"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">
                    ${lang.postnr}:
                </div>
                <div class="col-4">
                    <input type="text" class="form-control-custom mandatory ${unique} ship_to_postal_code madatory-text" id="ship_to_postal_code" validate="zipcode" placeholder="${lang.postnr}">
                </div>
                <div class="col-5"></div>
            </div>

            <div class="row">
                <div class="col-3 justify-content-center align-self-center">${lang.bynavn}:</div>
                <div class="col-9"><input type="text" class="form-control-custom mandatory ${unique} ship_to_city madatory-text" id="ship_to_city" placeholder="${lang.bynavn}"></div>
            </div>
        `
    }
    static navisionForm(unique = ""){
        let lang = this.translation(1);


        return `<div id="navstatuspanel" style="display: none;"><div style="float: right; padding-right: 70px;"><button type="button" style="display: none;" class="navchangeno">Skift kundenr</button> <button type="button" class="navstopsync">Stop synkronisering</button></div>
        <label><b>Navision status</b></label>
        <hr>
       
            <div class="row" id="navchangeno" style="display: none;">
                <div class="col-12"><div style="padding-bottom: 20px; padding-right: 50px;">Navision kundenr kan kun ændres hvis ikke kunden har en ordre i systemet, som er synkroniseret til navision. Hvis der er ordre i navision vil det melde fejl. Ændringen skal godkendes før det opdateres.</div></div>
                <div class="col-3 justify-content-center align-self-center">Nyt navision kundenr:</div>
                <div class="col-6"><input type="text" id="navchangenoval" class="form-control-custom" value=""></div>
                <div class="col-3 justify-content-center align-self-center"><button class="navchangenosubmit">Gem skift</button></div>
                <div class="col-12"><hr></div>
            </div>
       
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-3 justify-content-center align-self-center">Navision status:</div>
                        <div class="col-9"><input type="text" id="company_nav_status" class="form-control-custom" disabled value=""></div>
                    </div>
                    <div class="row">
                        <div class="col-3 justify-content-center align-self-center">Navision debitor nr:</div>
                        <div class="col-9"><input type="text" id="company_nav_debitor" class="form-control-custom" disabled value=""></div>
                    </div>
                    <div class="row" style="display: none;">
                        <div class="col-3 justify-content-center align-self-center">Sæt sync på pause:</div>
                        <div class="col-9"><button id="companyPauseBlock">sæt kunde / kundens ordre på pause</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>`

    }
    static cardShippingForm(unique = ""){
        let lang = this.translation(1);
        return `


            <div class="row">
                <div class="col-4 justify-content-center align-self-center">${lang.virksomhed}:</div>
                <div class="col-8"><input type="text" class="form-control-custom ${unique}" id="cardto_name" placeholder="${lang.virksomhed}"></div>
            </div>

            <div class="row">
                <div class="col-4 justify-content-center align-self-center">${lang.adress1}:</div>
                <div class="col-8"><input type="text" class="form-control-custom mandatory ${unique}" id="cardto_address" placeholder="${lang.adress1}"></div>
            </div>

            <div class="row">
                <div class="col-4 justify-content-center align-self-center">${lang.adress2}:</div>
                <div class="col-8"><input type="text" class="form-control-custom ${unique}" id="cardto_address2" placeholder="${lang.adress2}"></div>
            </div>
            
            <div class="row">
                <div class="col-4 justify-content-center align-self-center">
                    ${lang.postnr}:
                </div>
                <div class="col-4">
                    <input type="text" class="form-control-custom mandatory ${unique}" id="cardto_postal_code" validate="zipcode" placeholder="${lang.postnr}">
                </div>
                <div class="col-4"></div>
            </div>

            <div class="row">
                <div class="col-4 justify-content-center align-self-center">${lang.bynavn}:</div>
                <div class="col-8"><input type="text" class="form-control-custom mandatory ${unique}" id="cardto_city" placeholder="${lang.bynavn}"></div>
            </div>
        `
    }
    static save(unique = "",type=""){
        let lang = this.translation(1);
        let orderType = (type == 'new') ? '<button  class="btn btn-primary shadow-none '+unique+' conpanyFormSave" id="conpanyFormSave">Gem uden send earlyorder</button>':''
        return `
        <div class="row">
                <div class="col-6"></div>
                <div class="col-5 ">${orderType}<button  class="btn btn-primary shadow-none ${unique} conpanyFormSave" id="conpanyFormSave">${lang.save}</button></div>
                <div class="col-1 "></div>
        </div>
        `
    }

    static translation(language){
        if(language == 1){
            return  {
                cvr:"Cvr",
                sog:"Søg",
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
  
/*
    static lookupBillForm(id,datafak,datalev) {
        let lang = this.translation(1);
        return `

            <table id="${id}" class="lookupForm">
                <tr><td><b>Faktura addresse</b></td><td></td></tr>
                <tr><td>${lang.virksomhed}:</td><td id="datafak-virksomhed">${datafak.virksomhed}</td></tr>
                <tr><td>${lang.bynavn}:</td><td id="datafak-bynavn">${datafak.bynavn}</td></tr>
                <tr><td>${lang.postnr}: </td><td id="datafak-postnr">${datafak.postnr}</td></tr>
                <tr><td>${lang.adress1}:</td><td id="datafak-adress1">${datafak.adress1}</td></tr>
                <tr><td>${lang.adress2}:</td><td id="datafak-adress2">${datafak.adress2}</td></tr>
                <tr><td>${lang.ean}:</td><td id="datafak-ean">${datafak.ean}</td></tr>
                <tr><td><b>Leveringsadresse</b></td><td></td></tr>
                <tr><td>${lang.bynavn}:</td><td id="datalev-bynavn">${datalev.bynavn}</td></tr>
                <tr><td>${lang.postnr}: </td><td id="datalev-postnr">${datalev.postnr}</td></tr>
                <tr><td>${lang.adress1}:</td><td id="datalev-adress1">${datalev.adress1}</td></tr>
                <tr><td>${lang.adress2}:</td><td id="datalev-adress2">${datalev.adress2}</td></tr>
                <tr><td></td><td ><button class="lookupTransferData" data-id="${id}">Overfør informationer</button></td></tr>
            </table><hr>
        `
    }

 props.map( prop => {
            return `<div >
            <input class="catList" data-id="${prop.docId}" type="checkbox" /> <label>${prop.docData.catName} </label>
                    </div>`
        }).join('')+

*/