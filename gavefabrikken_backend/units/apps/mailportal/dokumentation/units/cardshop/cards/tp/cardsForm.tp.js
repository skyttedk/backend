
export default class Cardsform {
    static showMasterdataForm(){

        return `

        `;
    }
    static changeshopsaleweeks(data){
     return `<select id="changeshopsaleweeks_pt"><option disabled="disabled" selected="" value="0">V&oelig;lg</option>`+
            data.map((i) => {
            if(i.sale_is_open == true || i.websale_is_open == true){
                return `<option  value='${i.expire_date}' saleisopen = '${i.sale_is_open}' homedelivery = '${i.week_no}' websaleisopen = '${i.websale_is_open}'  >Uge ${i.week_no} - ${i.display_date}</option>`
            }
        }).join('') + `</select>`;

    }
    static saveChangeshopsaleweeks(){

        return `

                <div class="col-9"></div>
                <div class="col-3 "><button  class="btn btn-primary shadow-none" id="saveChangeshopsaleweeks">Save</button></div>

        `
    }

    static childList(data,unique = ""){

         let idCounter=-1
         return `<select data-id="${unique}"  id="childList_tp"><option data-id="${unique}" selected="" value="none" company-id="" >Ny adresse</option>`+

            data.map((i) => {
                idCounter++;
                return `<option value='${idCounter}' data-id="${unique}" company-id="${i.attributes.id}"  >${i.attributes.ship_to_address} - ${i.attributes.ship_to_postal_code}</option>`

        }).join('') + `</select>`;
    }

}