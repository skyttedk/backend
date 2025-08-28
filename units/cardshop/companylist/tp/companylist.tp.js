export default class companylist {

    static searchform(){
        let land = "Dansk bruger";
        let flagButtons = "";
        
        // Only show language flags for user ID 340
        if(window.USERID == 340) {
            // Show flags for available language switches
            if(window.LANGUAGE == 1) {
                // Currently Danish, show Norwegian and Swedish flags
                flagButtons = `<span class="language-flag" data-lang="4" title="Switch to Norwegian" style="cursor:pointer;margin-left:10px;">🇳🇴</span>
                              <span class="language-flag" data-lang="5" title="Switch to Swedish" style="cursor:pointer;margin-left:5px;">🇸🇪</span>`;
            } else if(window.LANGUAGE == 4) {
                land = "Norsk bruger";
                // Currently Norwegian, show Danish and Swedish flags
                flagButtons = `<span class="language-flag" data-lang="1" title="Switch to Danish" style="cursor:pointer;margin-left:10px;">🇩🇰</span>
                              <span class="language-flag" data-lang="5" title="Switch to Swedish" style="cursor:pointer;margin-left:5px;">🇸🇪</span>`;
            } else if(window.LANGUAGE == 5) {
                land = "Svensk bruger";
                // Currently Swedish, show Danish and Norwegian flags
                flagButtons = `<span class="language-flag" data-lang="1" title="Switch to Danish" style="cursor:pointer;margin-left:10px;">🇩🇰</span>
                              <span class="language-flag" data-lang="4" title="Switch to Norwegian" style="cursor:pointer;margin-left:5px;">🇳🇴</span>`;
            }
        }
        
        // Set language text for all users based on their language
        if(window.LANGUAGE == 4) {
            land = "Norsk bruger";
        } else if(window.LANGUAGE == 5) {
            land = "Svensk bruger";
        }

        return  `
        <div class="cardshop">
            <div style=' position: absolute;top: 0px; left: 10px;z-index: 999;'> ${land}${flagButtons}</div>
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

    // Set language flag click events (called after layout is created)
    static setLanguageFlagEvents(classInstance) {
        console.log("Setting up language flag events...");
        console.log("Found flags:", document.querySelectorAll(".language-flag").length);
        
        document.querySelectorAll(".language-flag").forEach(function(flag) {
            flag.addEventListener('click', function() {
                console.log("Flag clicked! Language:", this.getAttribute('data-lang'));
                const newLanguage = this.getAttribute('data-lang');
                classInstance.switchLanguage(newLanguage);
            });
        });
    }

}