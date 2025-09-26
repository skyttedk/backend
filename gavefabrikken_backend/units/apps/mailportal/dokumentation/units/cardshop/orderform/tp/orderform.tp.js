import HtmlComponent from '../../main/tp/htmlComponent.tp.js';



export default class OrderformTp extends HtmlComponent{
   constructor() {
        super();
   }

     static orderForm(){ return `
<div id="newOrderForm">
    <fieldset>
        <legend><u>Ordrer</u></legend>
        <div id="OrderFormShopsContainer">
            <label><b>Konsept:</b></label>
            <div class="row ">
                <div class="col-12" id="OrderFormShops">
                </div>
            </div>

        </div>
        <div id="OrderFormDeadlineContainer" class="OrderFormHide">
            <label><b>Deadline:</b></label>
            <div class="row ">
                <div class="col-12" id="OrderFormDeadline">
                </div>
            </div>
            <hr>
        </div>
        
        <div id="OrderFormValuesContainer" class="OrderFormHide">
            <label><b>Beløb:</b> (der kan vælges flere)</label>
            <div class="row ">
                <div class="col-12" id="OrderFormValues">
                </div>
            </div>
            <hr>
        </div>

        <div id="OrderFormShippingMethodContainer" class="OrderFormHide">
            <label><b>Kort forsendelse metode:</b></label>
            <div class="row ">
                <div class="col-12" id="OrdershippingMethod">
                </div>
            </div>

        </div>

        <div id="OrderFormAmountContainer" class="OrderFormHide">
            <hr>
            <div class="row">
                <div class="col-6" id="OrderFormAmount">
                    <label><b>Total antal kort:</b></label><br>
                    <input id="OrderFormAmountInput" type="number" min="0" max="100"/>
                </div>
                <div class="col-6" id="OrderFormFreeAmountInput_UserAccess" style="display:none;">
                    <label><b style="color:red">Heraf gratis kort:</b></label> <br>
                    <input value="0" id="OrderFormFreeAmountInput" type="number" min="0" max="100"/>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12" >
                <label><b>Faktureringsmetode:</b></label><br>
                <div class="row">
                    <div class="col-6" ><input type="radio" checked  name="orderFormPrepayment" value="1" id="automatisk"> <label for="automatisk"> Ja, acontofakturer automatisk</label><br></div>
                  
                </div>
                </div>
            </div>
         
            <div class="row" id="prepaymentdatebuttons" style="margin-top: 10px; ">
                <div class="col-6"><label for="ingen"><input type="radio"  name="orderFormPrepayment" value="0" id="ingen"> Nej, ingen acontofakturering/fakturering ved levering</label></div>
                <div class="col-6"><label for="prepdato"><input type="radio"  name="orderFormPrepayment" value="2" id="prepdato"> Senere fakturering (før leveringsuge):</label> <input type="date" name="prepaymentdate" value="display: none;"></div>
            </div>
            
            <div class="row" id="prepaymentwarn" style="display: none; ">
                <div class="col-12">
                    <div class="warning alert-warning" role="warning">
                        <b>Bemærk:</b> dette valg af forudfakturering bliver sendt til godkendelse ved økonomi før ordren kan gennemføres.
                    </div>
                </div>
            </div>
            
            <hr>
        <div class="row alternativCardShippingContainer " style="display:none;">
         <div class="col-11">

                <label class="companyform-switch"  >
                    <input type="checkbox" id="alternativCardShippingSlider">
                    <span class="companyform-slider companyform-round" id="alternativCardShipping"></span>
                </label>
                <label class="companyform-slider-text text-deactivated" style='font-size:14px;margin-top:-7px;'> Send fysiske kort til en selvvalgt adresse </label>
                <div style='font-size:10px;'>(V&oelig;lges hvis fysiske kort <u>ikke</u> skal sendes til faktura- eller gaveleveringsadressen)</div>
                <div id="alternativCardShippingForm" class="hide" >

                </div>
            </div>
            <div class="col-1"></div>

        </div>







        </div>
    </fieldset>

        <fieldset>
        <legend><u>Order information</u></legend>
            <label><b>Saleperson:</b></label>
            <div class="row ">
                <div class="col-12" id="OrderFormSaleperson">

                </div>
            </div>


            <div class="row ">
                <div class="col-6" >
                        <label><b>Kundens reference:</b></label>
                        <input id="OrderFormReference" type="" maxlength="35" style="width:90%; padding:3px;" />
                </div>
                <div class="col-6" >
                    <label><b>Note til ordrebekræftelsen</b></label><br>
                    <textarea id="OrderFormOrderNotes" rows="4" cols="50" style="width: 100%;"></textarea>
                </div>
            </div>
            
            <div class="row ">
                <div class="col-6" >
                    <label><b>Interne noter:</b></label><br>
                    <textarea id="OrderFormInternnote" rows="4" cols="50" style="width: 100%;"></textarea>
                </div>
                <div class="col-6" >
                    <label><b>Leveringsaftaler/ Fragtnoter:</b></label><br>
                    <textarea id="OrderFormDeliveryAgreements" rows="4" cols="50" style="width: 100%;"></textarea>
                </div>
            </div>

            <div class="row ">
                <div class="col-6" >
                    
                   <div style="display: none;" class="dotdeliverydiv">
                        <label><input type="checkbox" name="dotdelivery" class="usedot"> DOT levering</label>
                        <div class="dotdetails" style="display: none; margin-top: 4px; padding-left: 23px;">
                            <div>Pris: <select class="dotpricetype">
                                    <option value="1" selected="">Standard: 968</option>
                                    <option value="2">Gratis</option>
                                    <option value="3">Anden pris</option>
                                </select><span style="display: none;">: <input class="dotpriceamount" type="text" style="width: 50px; text-align: right;" placeholder="Pris" value="968">,-</span></div>
                            <div style="padding-top: 4px;">DOT dato: <input type="datetime-local" name="dotdescription" class="dotdescription" style="width: 150px;"></div>
                        </div>
                    </div>
                    
                    
                </div>
                <div class="col-6" >
                  
                    <div style="display: none;" class="carryupdiv">
                        <label><input type="checkbox" name="carryup" class="usecarryup"> Opbæring</label>
                        <div class="carryupdetails" style="display: none; line-height: 150%; margin-top: 4px; padding-left: 23px;">
                            Pris: <select class="carryuppricetype">
                                <option value="1">Standard: 968</option>
                                <option value="2">Gratis</option>
                                <option value="3">Anden pris</option>
                            </select><span>: <input class="carryuppriceamount" type="text" style="width: 50px; text-align: right;" placeholder="Pris" value="968">,-</span><br>
                            <label><input type="radio" name="carryuptypenew" class="carryuptype" value="3"> Plads til helpalle</label><br>
                            <label><input type="radio" name="carryuptypenew" class="carryuptype" value="2"> Plads til halvpalle</label><br>
                            <label><input type="radio" name="carryuptypenew" class="carryuptype" value="1" checked=""> Har ikke elevator</label>
                        </div>
                    </div>
                    
                </div>
            </div>
          

        </fieldset>
         <hr>
        <fieldset>
        <legend><u>Produkter</u></legend>
            <div class="row ">
                <div class="col-12" id="OrderFormAdditionalProducts" >
                </div>
            </div>
        </fieldset>

        <hr>
        <fieldset>
        <legend><u>Tilf&oslash;j flere leveringsadresser</u></legend>
        <div class="OrderFormHide" id="AdditionalDeleveryAdress">
               <br>
            <div class="row ">

                <div class="col-12" id="OrderFormAdditionalDeleveryAdress" >
                </div>
            </div>
            <button id="AdditionalDeleveryAdressBtn" type="button" class="btn btn-info">Tilf&oslash;j</button>
        </div>
        </fieldset>

        <hr>
        <fieldset>
        <legend><u>Early orders</u></legend>
        <div class="OrderFormHide" id="AdditionalEarlyordersDeleveryAdress">

            <br>
            <div class="row ">
                <div class="col-12" id="OrderFormEarlyordersDeleveryAdress" >
                </div>
            </div>
            <button id="AdditionalearlyAdressBtn" type="button" class="btn btn-info">Tilf&oslash;j</button>
        </div>
        </fieldset>

</div>
`;
}
    static Saleperson(data){
        return `<select id="OrderFormSalepersonList"  class="madatory-select">
        <option disabled="disabled" selected="" value="0">V&oelig;lg</option>`+
        data.map((i) => {
            if(!i.name) return;
            return `<option value='${i.code}'>${i.name}</option>`
        }) +
        `</select>`;
    }

    static OrderFormShops(data){

        return `<select id="OrderFormShopsSelect" class="madatory-select">
        <option disabled="disabled" selected="" value="0">V&oelig;lg</option>`+
        data.map((i) => {
            return `<option value='${i.shop_id}'>${i.alias}</option>`
        }) +
        `</select>`;
    }

    static DeadlineFormShops(data){
      
         return `<select class="madatory-select" id="OrderFormDeadlineSelect"><option disabled="disabled" selected="" value="0">V&oelig;lg</option>`+
            data.map((i) => {
            if(i.sale_is_open == true || i.websale_is_open == true){
               i.week_no =  i.is_delivery == 1 ? 0 : i.week_no;
               var weekLabel = i.week_no == 0 ? "Hjemmelevering" : "Uge "+i.week_no;
               return `<option class='OrderFormDeadlineOption' value='${i.expire_date}' special= '${i.special}' saleisopen = '${i.sale_is_open}' homedelivery = '${i.week_no}' websaleisopen = '${i.websale_is_open}' useenvfee = '${i.use_envfee}'  > ${weekLabel} - ${i.display_date}</option>`
            }
        }).join('') + `</select>`;
    }

    static ValuesFormShops(data){

        return data.map((value) => {
            return `<div><label >
            <input type="checkbox" class="cardvalues" value="${value}">
            ${value}
        </label></div>`;
        }).join('');
    }

    static shippingMethod(){
    return   `
        <div class="form-check" >
            <input class="form-check-input OrderFormShippingInput madatory-radio"  type="radio" name="flexRadioDefault" id="OrderFormPhysicalShippingInput" value="physical" >
            <label class="form-check-label" for="OrderFormPhysicalShippingInput">
                Fysiske kort
            </label>
        </div>

            <div class="form-check">
                <input class="form-check-input OrderFormShippingInput madatory-radio" type="radio" name="flexRadioDefault" id="OrderFormEmailShippingInput" value="email" >
            <label class="form-check-label" for="OrderFormEmailShippingInput">
                Email
            </label>
        </div> `
    }
    // ************************
    static AdditionalProducts(data,homedelivery,deliveryType) {

       return ` <table > ` +
        data.map((i) => {
            let disabled    = "disabled";
            let checked     = "";
            let ismandatory = "";
            // checked
            if(i.metadata.isdefault == true){
               checked = "checked";
               disabled =  "";
               disabled =  "";
            } else {
               checked = "";
               disabled =  "disabled";
            }


            switch(i.metadata.requireon) {
            case "allways":
                checked = "checked";
                disabled =  "";

            break;
            case "emailcards":
                if(deliveryType != "physical"){
                    checked = "checked";
                    disabled =  "";
                }
            break;
            case "physicalcards":
                if(deliveryType == "physical"){
                    checked = "checked";
                    disabled =  "";
                }
            break;
            case "privatedelivery":
                if(homedelivery == true){
                    checked = "checked";
                    disabled =  "";
                }
            break;
            case "companydelivery":
                if(homedelivery != true){
                    checked = "checked";
                    disabled =  "";
                }
            break;

            default:

            }
            // disabled
            switch(i.metadata.hideon) {

            case "emailcards":

                if(deliveryType != "physical"){
                    return;
                }
            break;
            case "physicalcards":
                if(deliveryType == "physical"){
                    return;
                }
            break;
            case "privatedelivery":
                if(homedelivery == true){
                    return;
                }
            break;
            case "companydelivery":
                if(homedelivery != true){
                    return;
                }
            break;

            default:

            }

            if(i.metadata.ismandatory == true){
                 ismandatory =  "disabled";
                 disabled = "";
            }
            return `<tr>
                    <td width=30><input type="checkbox" ${checked} ${ismandatory} class="AdditionalProductsActivate" data-id="${i.code}" defaultValue="${i.price}" /></td>
                    <td width=150>${i.name}</td>
                    <td width=50><input class="AdditionalProductsItems" ${disabled} type="text"  id="${i.code}" value="${i.price}" /></td>
                </tr>`
        }).join('') +
        `</table>`;
    }
    static save(){

        return `
        <div class="row">
                <div class="col-10"></div>
                <div class="col-2 "><button  class="btn btn-primary shadow-none" id="OrderFormSave">Save</button></div>
        </div>
        `
    }
    static orderAndEarlyOrder(){

        return `
        <div class="row " style="width:100%;text-align: right">
                <div class="col-12" style="text-align: right"><button style="display:inline; " class="btn btn-outline-primary" id="OrderFormSaveWithoutEarly">Opret ordrer</button> <button style="display:inline; " class="btn btn-success" id="OrderFormSave">Opret ordrer og send earlyorder</button></div>

        </div>
        `
    }
    static cardAmountInput(id){
           return `
             <div class="row">
                <div class="col-3 justify-content-center align-self-center"><label><b>Amount of cards:</b></label></div>
                <div class="col-9"><input style="width:100px;" type="number"  class="form-control AdditionalOrderFormAmount madatory-text" data-id="${id}" ></div>
            </div>
        `
    }

    static deliveryOptionsInput(id, products) {
        console.log('shipping form');
        console.log(products);

        const extra = products?.extra || {};
        const dotUse = extra.dot_use === 1;
        const dotPrice = extra.dot_price ? (extra.dot_price / 100) : 968;
        const carryUpUse = extra.carryup_use === 1;
        const carryUpPrice = extra.carryup_price ? (extra.carryup_price / 100) : 968;

        return `
    <div class="row childdeliveryoptionsparent">
        <div class="col-3 justify-content-center align-self-center"><label><b>Leveringsnote:</b></label></div>
        <div class="col-9"><input style="width:100%;" type="text" class="form-control deliverynote"></div>
        
        ${dotUse ? `
        <div class="col-6">
            <div style="padding-top: 12px;" class="childdotdeliverydiv">
                <label><input type="checkbox" name="childdotdelivery" class="childusedot"> DOT levering</label>
                <div class="childdotdetails" style="display: none; margin-top: 4px; padding-left: 23px;">
                    <div>Pris: <select class="childdotpricetype">
                            <option value="1" selected>Standard: ${dotPrice}</option>
                            <option value="2">Gratis</option>
                            <option value="3">Anden pris</option>
                        </select><span style="display: none;">: <input class="childdotpriceamount" type="text" style="width: 50px; text-align: right;" placeholder="Pris" value="${dotPrice}">,-</span></div>
                    <div style="padding-top: 4px;">DOT dato: <input type="datetime-local" name="childdotdescription" class="childdotdescription" style="width: 150px;"></div>
                </div>
            </div>                    
        </div>
        ` : ''}
        
        ${carryUpUse ? `
        <div class="col-6">
            <div style="padding-top: 12px;" class="childcarryupdiv">
                <label><input type="checkbox" name="childcarryup" class="childusecarryup"> Opbæring</label>
                <div class="childcarryupdetails" style="display: none; line-height: 150%; margin-top: 4px; padding-left: 23px;">
                    Pris: <select class="childcarryuppricetype">
                        <option value="1" selected>Standard: ${carryUpPrice}</option>
                        <option value="2">Gratis</option>
                        <option value="3">Anden pris</option>
                    </select><span>: <input class="childcarryuppriceamount" type="text" style="width: 50px; text-align: right;" placeholder="Pris" value="${carryUpPrice}">,-</span><br>
                    <label><input type="radio" name="childcarryuptype${id}" class="childcarryuptype" value="3" checked> Plads til helpalle</label><br>
                    <label><input type="radio" name="childcarryuptype${id}" class="childcarryuptype" value="2"> Plads til halvpalle</label><br>
                    <label><input type="radio" name="childcarryuptype${id}" class="childcarryuptype" value="1"> Har ikke elevator</label>
                </div>
            </div>
        </div>
        ` : ''}
    </div>
    `;
    }



    static childList(data,unique = ""){

         let idCounter=-1
         return `<select data-id="${unique}"  class="OrderFormChildListNewAdress ${unique}"><option data-id="${unique}" selected="" value="none" company-id="" >Ny adresse</option>`+

            data.map((i) => {
                idCounter++;
                return `<option value='${idCounter}' data-id="${unique}" company-id="${i.attributes.id}"  >${i.attributes.ship_to_address} - ${i.attributes.ship_to_postal_code}</option>`

        }).join('') + `</select>`;
    }
    static earlyOrderContact(data,unique = ""){
      console.log(data);
           return `
             <div class="row">
                <div class="col-3 justify-content-center align-self-center"><label><b>Kontakt person:</b></label></div>
                <div class="col-9"><input style="" value="${data.contact_name}" type="email" class="form-control AdditionalOrderFormContact ${unique} madatory-text"  ></div>
            </div>
             <div class="row">
                <div class="col-3 justify-content-center align-self-center"><label><b>Email:</b></label></div>
                <div class="col-9"><input value="${data.contact_email}" type="text" class="form-control AdditionalOrderFormEmail ${unique} madatory-text"  ></div>
            </div>
             <div class="row">
                <div class="col-3 justify-content-center align-self-center"><label><b>Mobilnr:</b></label></div>
                <div class="col-9"><input value="${data.contact_phone}" style="width:200px;" type="text" class="form-control AdditionalOrderFormMobile ${unique} madatory-text"  ></div>
            </div>

        `
    }
    static earlyOrderPresent(data,unique=""){

            return ` <div class="row earlyOrder ${unique}">
                    <div class="col-6 justify-content-center align-self-center">
                        <input class="CustomItemnrSwitch ${unique}" type="checkbox" /> <label> Valgfrit varenr. </label>  <br>
                        <input  class="form-control earlyOrderCustomItemnr ${unique}" type="text"  />
                        <select data-id="${unique}"  class="${unique}"><option  selected="" value="none">V&oelig;lg</option>`+
            data.map((i) => {
                return `
                        <option value='${i.attributes.item_nr}'"   >${i.attributes.description}</option>`
            }).join('') +
            `</select></div><div class="col-4">Antal: <input style="width:100px;" type="number"  class="form-control earlyOrderPresentAmount ${unique}"  ></div>
            </div>`
            ;

    }

} /*

   <div class="col-4" ><input type="radio"  name="orderFormPrepayment" value="2" id="manuel"> <label for="manuel"> Ja, manuel forudfakturering</label><br></div>


<div id="showMasterdataForm">
    <label><b>Invoice address</b></label>
    <hr>
    <div class="row ">
        <div class="col-12">
            ${this.billForm(false) }
        </div>
    </div>
    <br>
    <label><b>Contact person informations</b></label>
    <hr>
    <div class="row">
        <div class="col-12">
            ${this.contactForm() }
        </div>
    </div>
    <br>
    <label><b>Delivery address</b></label>
    <hr>
    <div class="row">
        <div class="col-12">
            ${this.shippingForm() }
        </div>
    </div>
</div>
*/