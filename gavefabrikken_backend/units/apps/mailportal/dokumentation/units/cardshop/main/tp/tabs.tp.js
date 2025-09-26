export default class tabs {

    static initTabs(){
        return  `
        <div id="cardshop-tabs">
        <ul>
          <li><a href="#cardshop-tabs-1" tab-id ="masterdata" class="cardshop-tabs">Stamdata</a></li>
          <li><a href="#cardshop-tabs-2" tab-id ="cards" class="cardshop-tabs cardscards">Gavekort</a></li>

          <li><a href="#cardshop-tabs-6" tab-id ="earlyorders" class="cardshop-tabs">Early orders</a></li>
          <li><a href="#cardshop-tabs-4" tab-id ="notes" class="cardshop-tabs">Noter/fragt</a></li>
          <li><a href="#cardshop-tabs-3" tab-id ="orders" class="cardshop-tabs">Faktura</a></li>
          <li><a href="#cardshop-tabs-5" tab-id ="rules" class="cardshop-tabs">Regler</a></li>
          <li><a href="#cardshop-tabs-7" tab-id ="companynotes" class="cardshop-tabs">Noter</a></li>
          <li><a href="#cardshop-tabs-8" tab-id ="actions" class="cardshop-tabs">Historik</a></li>
        </ul>
        <div id="cardshop-tabs-1" class="cardshop-tabs-content tab-masterdata" >
        1
        </div>
        <div id="cardshop-tabs-2" class="cardshop-tabs-content tab-cards" >

        </div>
        <div id="cardshop-tabs-3" class="cardshop-tabs-content tab-orders" >
        </div>
        <div id="cardshop-tabs-4" class="cardshop-tabs-content tab-notes" >
        4
        </div>
        <div id="cardshop-tabs-5" class=" cardshop-tabs-content tab-rules" >

        </div>
        <div id="cardshop-tabs-6" class=" cardshop-tabs-content tab-earlyorders" >

        </div>
        <div id="cardshop-tabs-7" class=" cardshop-tabs-content tab-companynotes" >

        </div>
        <div id="cardshop-tabs-8" class=" cardshop-tabs-content tab-actions" >

        </div>
      </div>
        `;
    }


}