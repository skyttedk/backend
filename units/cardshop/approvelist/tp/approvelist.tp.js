export default class ApprovelistTp {

    static Main(){
         return ` <div>main</div>`
    }
    static filterList()
    {



        return `<input style="margin-left:5px" type="radio" id="filterListAlle-tp" name="filterList_tp" class="filterList-tp"  value="alle" checked > <label for="filterListAlle-tp" style="margin-right:5px">Alle</label>
              <input type="radio" id="filterListCompany-tp" class="filterList-tp" name="filterList_tp"  value="company"> <label for="filterListCompany-tp" style="margin-right:5px">Company</label>
              <div style="display: none;"><input type="radio" id="filterListShipment-tp" class="filterList-tp" name="filterList_tp"  value="shipment"> <label for="filterListShipment-tp"  style="margin-right:5px">Shipment</label></div>
              <input type="radio" id="filterListOrder-tp" class="filterList-tp" name="filterList_tp"  value="order"> <label for="filterListOrder-tp">Order</label>`;
    }
    static  showTech()
    {
        return `<span style="margin-left:35px; display: none;">(</span>
                 <input type="checkbox" id="techFilterListShow-tp" name="techFilterList" class="techFilterList" value="show" > <label for="techFilterListShow-tp" >Vis tech</label>


              <span> )</span> `
    }
    static localisation(){
        return `<select  id="localisation-tp">
                    <option value="1">Danmark</option>
                    <option value="4">Norge</option>
                    <option value="5">Sverige</option>
                </select>`
    }


}