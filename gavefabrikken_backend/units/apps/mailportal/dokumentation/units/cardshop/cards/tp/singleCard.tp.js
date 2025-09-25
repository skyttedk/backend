export default class SingleCard {
    static demo(){
        return `

        `;
    }
    static cardInfo(cardData,orderData){
    let cd = cardData.data[0];
    return `<table width=600 class="customTable">
            <tr><td>Kort type</td><td>${cd.shopalias}</td></tr>
            <tr><td>Kortnr.</td><td>${cd.username}</td></tr>
            <tr><td>Adgangskode</td><td>${cd.password}</td></tr>
            <tr><td>Deadline</td><td>${cd.expire_date}</td></tr>
            ` + `</table><hr>
            <table width=600 class="customTable">


            ` +

        orderData.data.map((i) => {
            return `


                <tr><td colspan=2><b>Gavevalg info</b></td></tr>
                <tr><td>Tidspunkt</td><td>${i.order_timestamp}</td></tr>
                <tr><td >Ordrenummer</td><td>${i.order_no}</td></tr>
                <tr><td>Navn</td><td>${i.user_name}</td></tr>
                <tr><td>Email</td><td>${i.user_email}</td></tr>
                <tr><td>Gave</td><td>${i.model_name} - ${i.present_no}</td></tr>
                <tr><td colspan=2><img src="${i.media_path}" alt="" width=200 /> </td></tr>
                <tr><td colspan=2><hr> </td></tr>


            `;
        }).join('')  + "</table>";



    }

}