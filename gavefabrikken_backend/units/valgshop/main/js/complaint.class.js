import Base from 'https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/main/js/base.js';
import tpComplaint from '../tp/complaint.tp.js';


export default class Complaint extends Base {
    constructor(shopID, shopuserID) {
        super();
        this.masterData;

        this.shopID = shopID;
        this.shopuserID = shopuserID;

        this.LoadData();
    }
    
    async LoadData(){
        let result = await super.post("cardshop/cards/getComplaint/"+this.shopuserID);
        this.masterData = result.data.length > 0 ? decodeURIComponent(result.data[0].complaint_txt) : "" ;

        this.Layout();
    }

    async Layout(){
       super.OpenModal("Gave reklamation",tpComplaint.element(this.masterData),tpComplaint.save);
       this.setEvent();
    }
    
    async setEvent(){
        let self = this;
        $("#saveComplaint").unbind("click").click(
            function(){
                self.Update()
            }
        )
    }
    
    async Update()
    {
      if($("#complaintTxt").val() == "") return;
      let postData = {
        shopuserID:this.shopuserID,
        shopID:this.shopID,
        isNew:this.isNew,
        msg:encodeURIComponent($("#complaintTxt").val())
      }
      super.CloseModal();
      let result = await super.post("cardshop/cards/saveComplaint",postData);
      $('.complaintBtn[data-id="'+this.shopuserID+'"]').css('color', 'red');
      super.Toast("Reklamation er gemt")
    }
}