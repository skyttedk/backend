
export default class Valgshop {
    static demo(){

        return `

        `;
    }
    static stucture(){
        return `
        <div class="vs-sidenav">
            <input type="text" id="vs-search" autocomplete="off" placeholder="S&oslash;g valgshop" />
            <hr>
            <div id="vs-search-list"></div>

        </div>
        <div class="vs-main"></div>
        `;
    }
    static searchList(data){
            return  ``+
            data.data.map((i) => {
              return `<div class="vs-search-result-element companylist-element" id='${i.id}'>${i.name}</div>`
            }).join('')
    }
    static employeesDataList(data)
    {
        let hasReplaced;
        return  `<table id="company-view" class="display">
            <thead>
                <tr>
                    <th>Brugernavn</th>
                    <th>Navn</th>
                    <th>Email</th>
                    <th>Gave</th>
                    <th>Model</th>
                    <th>Alias</th>
                    <th>Varenr</th>
                    <th ></th>
                    <th ></th>
                </tr>
            </thead>
    <tbody>`+
      data.data.map(function(obj){
        let options;
        if(obj.user_name == null) obj.user_name = "";
        if(obj.user_email == null) obj.user_email = "";
        if(obj.model_name == null) obj.model_name = "";
        if(obj.model_no == null) obj.model_no = "";
        if(obj.fullalias == null) obj.fullalias = "";
        if(obj.model_present_no == null) obj.model_present_no = "";
        if(obj.is_replaced == 1){
            options = `<td class="tabel-replace"><div style="width:70px;">
                <button data-id="${obj.r_id}" class="valgshopShowReplacement" title="Gavevalg / option">Se erstatningskort)</button>
                </div></td><td><div>USER: ${obj.r_username}</div><div>PW: ${obj.r_password}</div></td>`;

        } else {
            options = `<td><div style="width:70px;">
                <button data-id="${obj.shopuser_id}" class="valgshopOption" title="Gavevalg / option"><i class="bi bi-gift"></i></button>
                </div></td><td></td>`;
        }


        return `
        <tr >
            <td>${obj.user_username}</td>
            <td>${obj.user_name}</td>
            <td>${obj.user_email}</td>
            <td>${obj.model_name}</td>
            <td>${obj.model_no}</td>
            <td>${obj.fullalias}</td>
            <td>${obj.model_present_no}</td>
            ${options}
        </tr>
        `}).join('') +`
    </tbody>
</table>`;
    }


}