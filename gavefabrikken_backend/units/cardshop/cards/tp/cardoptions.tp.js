export default class Cardsoptions {
    static demo(){
        return `

        `;
    }
    static stucture(){
    return `
    <div id="tabsMasterData" style="font-size:11px;">
        <ul>
           <li><a href="#tabsMasterData-2">Historik</a></li>
           <li><a href="#tabsMasterData-1">Order stamdata</a></li>
           <li><a href="#tabsMasterData-3">Erstatningskort</a></li>
        </ul>
        <div id="tabsMasterData-2"></div>
        <div id="tabsMasterData-1"></div>
        <div id="tabsMasterData-3"></div>
     </div> `
    }
    static showReplacementCardData(data)
    {
          return `<div >
               
                <p style="color:red;">Koden bliver ikke sendt automatisk til kunden!</p>
                <table class="customTable" width=100%>
                <tr><th>Brugernavn</th><th>Adgangskode</th><th>Deadline</th></tr>
                <tr><td>${data.username}</td><td>${data.password}</td><td>${data.expire_date}</td></tr>
                </table>
            </div>
          `;
    }

    static cardInfo(data,shopuserID,orderstate,cardData){

/*
        console.trace('CARD INFO');
        console.log(data);
        console.log(shopuserID)
        console.log(orderstate);
        console.log(cardData.data[0]);
        console.log(cardData);
*/
        let bn = "";

        let countryInput = '';
        if(cardData.data[0].is_delivery == 1 && cardData.data[0].delivery_state == 1 && cardData.hasOwnProperty('countries') && cardData.hasOwnProperty('country')) {

            var countryOptions = '<option value="">Standard - samme som shop</option>';
            for(var cc in cardData.countries) {
                countryOptions += `<option value="${cardData.countries[cc][1]}" ${cardData.countries[cc][1] == cardData.country ? 'selected' : ''}>${cardData.countries[cc][1]}</option>`;
            }

            countryInput = '<div style="padding: 10px; font-size: 12px;">Land: <select class="updateCountry">'+countryOptions+'</select></div><br>';
        } else {
            console.log('does not hav ecountry');
        }

        var removeUserids = ['50','5','51','110','124','162','248','301'];
        if(orderstate != 2){

            bn = `<button data-id=${shopuserID} class="btn btn-outline-primary updateMasterData" style="float:right; margin-right:10px"  >Opdatere data</button> `;

            if(removeUserids.includes(USERID)) {
                bn += `<button data-id=${shopuserID} class="btn btn-outline-danger removeMasterData" style="float:right; margin-right:10px"  >Slet ordre</button>`;
            }

        } else {
            bn = `<button data-id=${shopuserID} class="btn btn-outline-primary updateMasterData" style="float:right; margin-right:10px"  >Opdatere data (Gaven er sendt, &oelig;ndringerne vil kun blive gemt i CS og ikke komme over i NAV)</button>`;
        }

        return `<div ><table class="customTable" width=100%>` +
         data.map((i) => {
           return `<tr><td width=30%>${i.name}</td><td width=70%><input class="masterDataElement" style="width:90%" type="text" attr-id="${i.attribute_id}"  org-val="${i.attribute_value}" is-name="${i.is_name}" is-email="${i.is_email}" value="${i.attribute_value}"  /></td></tr>`
        }).join('') + `</table>
        ${countryInput}
        <hr>
        ${bn}
        <br> <br>
        </div>`;
    }
    static invoiceLayout(data){
        let lengthWarning = data.data.length > 30 ?  "Ikke alle resultater vises da der max vises 30 i listen <br>":"";

        return `<h3 style="color:red;">${lengthWarning} </h3><table width=600>` +
        data.data.map((i) => {
            let warningType = i.shop_is_company == "1" ?  "Kortet er fra en valgshop":"";
            i.present_no = i.present_no == "none" ? "":i.present_no;
            return `
                <tr><td colspan=2><b>${warningType}</b></td></tr>
                <tr><td >Ordrenummer</td><td>${i.order_no}</td></tr>
                <tr><td>Kort</td><td><b>${i.user_username}</b></td></tr>
                <tr><td colspan=2><b>Gavevalg info</b></td><td></td></tr>
                <tr><td>Tidspunkt</td><td>${i.order_timestamp}</td></tr>
                <tr><td>Navn</td><td>${i.user_name}</td></tr>
                <tr><td>Email</td><td>${i.user_email}</td></tr>
                <tr><td>Gave</td><td>${i.model_name} - ${i.present_no}</td></tr>
                <tr><td colspan=2><img src="${i.media_path}" alt="" width=120 /> </td><td></td></tr>
                <tr><td colspan=2><hr> </td><td></td></tr>
                <tr><td colspan=2> </td><td></td></tr>
            `;
        }).join('') +  `</table>`
    }

    static ReplacementList(data){



        return `<table width=600 class="customTable"><tr><th>Konsept</th><th>Antal ledige kort</th><th></th></tr>` +
        data.data.map((i) => {
            return `
                <tr><td >${i.alias}</td><td>${i.antal}</td><td><button data-id="${i.shop_id}" class="btn btn-primary shadow-none card-replacement" >Erstat kort med dette</button> </td></tr>

            `;
        }).join('') +  `</table>`
    }

}