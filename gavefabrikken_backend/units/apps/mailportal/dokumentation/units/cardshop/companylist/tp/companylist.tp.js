export default class companylist {

    static searchform(){
        let land = "Dansk bruger";
        if(window.LANGUAGE == 4){
            land = "Norsk bruger";
        }
        if(window.LANGUAGE == 5){
            land = "Svensk bruger";
        }


        return  `
        <div class="cardshop">
            <div style=' position: absolute;top: 0px; left: 10px;z-index: 999;'> ${land} -- Version: ${window.VERSION }</div>
            <input  style='margin-top:10px; 'autocomplete="off"  id="companylist-search" type="text"  placeholder="Search company" onClick="this.select();">

        </div>`;
    }
    static companylist(){
        return  `<div class="cardshop" id="companylist"></div><br>`;
    }
    static companylistElement(data){
        return  `
        <div class="cardshop">
            <div data-id="${data.id}" class="companylist-element">
                <div class="companylist-companyname"><b>${data.name} - <span> ${data.cvr}</span></b> </div>
                <div class="companylist-companyadress">${data.ship_to_address}</div>
                <div class="companylist-activecards">Active cards: ${data.hascard}</div>
            </div>
        </div>`;
    }
    static companylistElementChild(data){
            return  `
        <div class="cardshop">
            <div data-id="${data.id}" class="companylist-element companylist-child">
                <div class="companylist-companyname"><b>${data.ship_to_company} </span></b> </div>
                <div class="companylist-companyadress">${data.ship_to_address}</div>
                <div class="companylist-activecards">Active cards: ${data.hascard}</div>
            </div>
        </div>`;
    }

}