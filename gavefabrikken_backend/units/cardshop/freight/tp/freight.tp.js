export default class FreightTp {

    static freightMain(){
            return `<div id="freightContent"></div>`;
    }

    static freightPrice(data){
        console.log(data)
       const hasDeal = data.has_deal ? "inline" : "none";
       const price = data.cost;
       const checked = data.has_deal ? "checked" : "";
       return    `  <br>
            <label><b>Fragtpris aftale</b></label>
            <hr>
            <div id="freightContainerTp">
                <div class="row ">
                    <div class="col-12">
                        <input type="checkbox" ${checked} id="freightPriceOmOff_Tp" /> <label class="form-check-label" >Fragt pris aftale:</label> <input id="freightPrice_tp" type="number" style="display:${hasDeal}" value ="${price}" /> <button id="freightPriceSave_tp" style="display:${hasDeal}"  class="btn btn-primary">Opdaterer</button>
                    </div>
                </div>
            </div>
         `;
    }

    static freightManualHandle(data){
        console.log(data)
        const hasManualHandle = (data.hasOwnProperty('handle_manual') && data.handle_manual) ? "checked" : "";

        return    `<br>
            <label><b>Beregn fragt manuelt</b></label>
            <hr>
            <div id="freightHandleContainerTp">
                <div class="row ">
                    <div class="col-12">
                        <input type="checkbox" ${hasManualHandle} id="freightManHand_Tp" /> <label class="form-check-label" >Fragt skal beregnes manuelt</label> 
                    </div>
                </div>
            </div>
         `;
    }
    static freightNote(data,companyID){

        return "<br><label><b>Leverancer og fragt opsætning</b></label><hr><iframe src='index.php?rt=unit/cardshop/freight/companyfreightform/"+companyID+"/1' style='width: 100%; height: 690px; border: 0px;'></iframe>";

            let html=  `
                <br><br>
                <label><b>Fragt noter</b></label>
                <hr>
                <div id="freightNotesTp">
            `;

            data.orderlist.map(function(list){
                html+= `<div>${list.order_no} - ${list.name} -  ${list.certificate_value} - ${list.certificate_no_begin} - ${list.certificate_no_end}</div>
                        <div><textarea  rows="4" cols="50">${list.spdealtxt}</textarea><br>
                            <button id="${list.id}" class="freightNoteOpdate_Tp">Opdaterer</button>
                        </div>
                        <br><br>
                 `
            })
           return html+= `</div> `





    }
}