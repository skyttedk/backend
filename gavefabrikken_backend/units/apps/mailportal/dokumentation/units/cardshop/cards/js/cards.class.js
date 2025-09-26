
import Base from '../../main/js/base.js';
import Access from '../../main/js/access.js';
import ShowOrderForm from '../../orderform/js/orderform.class.js?v=123';
import tpCardForm from '../tp/cardsForm.tp.js?v=123';
import Cardoptions from './cardoptions.class.js';
import Complaint from './complaint.class.js';
import singleCard from './singleCardInfo.class.js';


export default class Cards extends Base {
    constructor(companyID) {
        super();
        this.access = new Access();
        this.companyID = parseInt($.trim(companyID));
        this.companyPID;
        this.Layout();
        this.conceptGroup =  {
          shop52:"group1",
          shop53:"group2",
          shop54:"group3",
          shop55:"group3",
          shop56:"group3",
          shop290:"group4",
          shop310:"group4",
          shop575:"group5",
          shop574:"group6",
          shop272:"group7",
          shop57:"group7",
          shop58:"group7",
          shop59:"group7",
          shop1832:"group8",
          shop1981:"group8",
            shop5117:"group8",
            shop4793:"group8"
        };
        this.selectedWeek = 0;
        this.isChild = false;

    }

    async Layout() {
        let hasReplaced = false;
        let result;


        //let html = "<table id='card-view'><tr><th>Kort Type</th><th>Nr</th><th>Kode</th><th>Deadline</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Varenr</th></tr></table>"


        //  let result = await super.post("cardshop/cards/loadCardsByCompanyID/"+this.companyID);


        //  let result = await super.post("cardshop/cards/companycards/"+this.companyID);

        //   return;
        result = await super.post("cardshop/cards/companycards/" + this.companyID);



        let html = `<table id="card-view" class="display">
    <thead>
        <tr>
            <th>BS nr</th>
            <th>Kort Type</th>
            <th>Nr</th>
            <th>Kode</th>
            <th>dato</th>
            <th>Navn</th>
            <th>Email</th>
            <th>Gave</th>
            <th>Model</th>
            <th>Alias</th>
            <th>Varenr</th>
            <th>Lev</th>
            <th></th>
            <th ></th>
        </tr>
    </thead>
    <tbody>` +
            result.data.result.map(function (obj) {
                if (obj.is_replaced == 1) {
                    hasReplaced = true;
                    return;
                }
                if (obj.user_name == null) obj.user_name = "";
                if (obj.user_email == null) obj.user_email = "";
                if (obj.model_name == null) obj.model_name = "";
                if (obj.model_no == null) obj.model_no = "";
                if (obj.fullalias == null) obj.fullalias = "";
                if (obj.model_present_no == null) obj.model_present_no = "";
                let options = `<td><div style="width:70px;">
                <button data-id="${obj.shopuser_id}" class="cardOpBtn" title="Gavevalg / option"><i class="bi bi-gift"></i></button>
                </div></td>`;
                if (obj.model_present_no != "") {
                    options = `<td ><div style="width:70px;">
                <button data-id="${obj.shopuser_id}" class="cardOpBtn" title="Gavevalg / option"><i class="bi bi-gift"></i></button>
                <button data-id="${obj.shopuser_id}" class="complaintBtn" title="Reklamation" ><i class="bi bi-exclamation-lg"></i></button>
                </div>
                </td>`;
                }
                let hasMail = obj.user_email == "" ? 0 : 1;

                let shutdownStyle = '';
                if(obj.shutdown == 1){ shutdownStyle = 'background:#ffb3b3 !important'; }

                let style = ''
                if (shutdownStyle != '') {
                    style = ' style="' + shutdownStyle + ' "';
                }

                let deliveryNote = "";
                  if(obj.is_delivery == 1) {

                      if(obj.order_id > 0) {
                      if(obj.shipment_state == 0) deliveryNote = '0: Afventer';
                      else if(obj.shipment_state == 1) deliveryNote = '1: Klar';
                      else if(obj.shipment_state == 2) deliveryNote = '2: Sendt til pak';
                      else if(obj.shipment_state == 3) deliveryNote = '3: Fejl';
                      else if(obj.shipment_state == 4) deliveryNote = '4: Blokkeret';
                      else if(obj.shipment_state == 5) deliveryNote = '5: Behandlet eksternt';
                      else if(obj.shipment_state == 6) deliveryNote = '6: Synkroniseret';
                      else if(obj.shipment_state == 7) deliveryNote = '7: Ukendt';
                      else if(obj.shipment_state == 9) deliveryNote = '9: Fejl i land';
                      else if(obj.shipment_state == null) deliveryNote = 'Ingen status';
                      else deliveryNote = obj.shipment_state+': Ukendt status';
                      if(obj.consignor_created != null && obj.consignor_created != "") {
                            deliveryNote = 'Sendt '+obj.consignor_created;
                      }
                      deliveryNote = '<span class="deliverynote" title="Klik for track and trace på levering!" data-cardusername="'+obj.username+'">'+deliveryNote+'</span>';

                      } else {
                        deliveryNote = 'Privat lev.';
                      }
                  } else {
                      deliveryNote = 'Uge lev.';
                  }






                return `
        <tr>
            <td ${style}>${obj.bsno}</td>
            <td ${style}>${obj.shopalias}</td>
            <td ${style}>${obj.username}</td>
            <td ${style}>${obj.password}</td>
            <td ${style}>${obj.expire_date}</td>
            <td ${style}>${obj.user_name}</td>
            <td ${style}>${obj.user_email}</td>
            <td ${style}>${obj.model_name}</td>
            <td ${style}>${obj.model_no}</td>
            <td ${style}>${obj.fullalias}</td>
            <td ${style}>${obj.model_present_no}</td>
           <td ${style}>${deliveryNote}</td>
            <td ${style}><input class="cardshop-cards-select" shop-id ="${obj.shop_id}" order-id="${obj.company_order_id}" card-number="${obj.username}" user-id="${obj.shopuser_id}"  has-mail="${hasMail}"  type="checkbox" /></td>
            ${options}
        </tr>
        `
            }).join('') + `
    </tbody>
</table>`;

        /*
              <td><img src="https://gavefabrikken.dk/2021/gavefabrikken_backend/views/media/icon/history.png" alt="" width="20" /></td>
            <td><img src="https://gavefabrikken.dk/2021/gavefabrikken_backend/views/media/icon/gave.png" alt="" width="20" /></td>
        */
        let optionBtn = '<button class="btn btn-outline-info pdfcards" style="float:right; margin-right:10px;">Download pdf koder</button><button class="btn btn-outline-info multi-present-select" style="float:right; margin-right:10px;">Skift gavevalg</button>';

        optionBtn += '<button class="btn btn-outline-info proforma" style=" float:right; margin-right:10px;">Hent proforma faktura</button>';


        $(".tab-cards").html(optionBtn + html);
        $("#cardshop-tabs-action").html('<button id="cardshop-newOrder-btn" type="button" class="btn btn-warning">New Order</button> <button id="cardshop-changeDateV2-btn" type="button" class="btn btn-outline-primary">&OElig;ndre dato</button> <button id="cardshop-transferCards-btn" type="button" class="btn btn-outline-primary">Flyt kort</button>  <button id="cardshop-delete-btn" type="button" class="btn btn-outline-danger">Slet</button> <button id="cardshop-block-btn" type="button" class="btn btn-outline-danger">Bloker</button>');


        let complaintList = await super.post("cardshop/cards/getComplaintList/" + this.companyID);
        complaintList.data.map((i) => {
            $('.complaintBtn[data-id="' + i.shopuser_id + '"]').css('color', 'red');
        })
        //

        var isAdmin = isAdmin = result?.data?.result?.[0]?.is_admin === 1 ?? false;
        this.accessControl(isAdmin);

        //style="color:red"
        if (hasReplaced == true) {
            this.hasReplacedElement();
        } else {
            this.SetEvents();
        }


    }

    accessControl(isAdmin){
        //if(this.access.validate(window.USERID,10) == false){
        if(!isAdmin) {
            $("#cardshop-changeDateV2-btn").hide();
            //$("#cardshop-transferCards-btn").hide();
            $("#cardshop-delete-btn").hide();
            $("#cardshop-block-btn").hide();
        }


    }
    async hasReplacedElement()
    {
        let replacementList = await super.post("cardshop/cards/getReplacementCard/"+this.companyID);
                 let html  =
        replacementList.data.result.map(function(obj){


            let shutdownStyle = '';
            if(obj.shutdown == 1){ shutdownStyle = 'background:#ffb3b3 !important'; }

            let style = ''
            if (shutdownStyle != '') {
                style = ' style="' + shutdownStyle + ' "';
            }

            let deliveryNote = "";
            if(obj.is_delivery == 1) {

                if(obj.order_id > 0) {
                    if(obj.shipment_state == 0) deliveryNote = '0: Afventer';
                    else if(obj.shipment_state == 1) deliveryNote = '1: Klar';
                    else if(obj.shipment_state == 2) deliveryNote = '2: Sendt til pak';
                    else if(obj.shipment_state == 3) deliveryNote = '3: Fejl';
                    else if(obj.shipment_state == 4) deliveryNote = '4: Blokkeret';
                    else if(obj.shipment_state == 5) deliveryNote = '5: Behandlet eksternt';
                    else if(obj.shipment_state == 6) deliveryNote = '6: Synkroniseret';
                    else if(obj.shipment_state == 7) deliveryNote = '7: Ukendt';
                    else if(obj.shipment_state == 9) deliveryNote = '9: Fejl i land';
                    else if(obj.shipment_state == null) deliveryNote = 'Ingen status';
                    else deliveryNote = obj.shipment_state+': Ukendt status';
                    if(obj.consignor_created != null && obj.consignor_created != "") {
                        deliveryNote = 'Sendt '+obj.consignor_created;
                    }
                    deliveryNote = '<span class="deliverynote" title="Klik for track and trace på levering!" data-cardusername="'+obj.username+'">'+deliveryNote+'</span>';

                } else {
                    deliveryNote = 'Privat lev.';
                }
            } else {
                deliveryNote = 'Uge lev.';
            }


        if(obj.user_name == null) obj.user_name = "";
        if(obj.user_email == null) obj.user_email = "";
        if(obj.model_name == null) obj.model_name = "";
        if(obj.model_no == null) obj.model_no = "";
        if(obj.fullalias == null) obj.fullalias = "";
        if(obj.model_present_no == null) obj.model_present_no = "";
        let options = '<td></td>';
        if(obj.model_present_no != ""){
            options =`<td ><div style="width:70px;">
                <button data-id="${obj.shopuser_id}" class="cardOpBtn" title="Gavevalg / option"><i class="bi bi-gift"></i></button>
                <button data-id="${obj.shopuser_id}" class="complaintBtn" title="Reklamation" ><i class="bi bi-exclamation-lg"></i></button>
                <button data-id="${obj.replacement_id}" class="OrgCardInfoBtn" title="Se oprindelige kort info" ><img src="/gavefabrikken_backend/units/assets/icon/Find-replace-01.png" alt="" width="15" />${obj.repl_username}</button>

                </div>
                </td>`;
        } else {
           options =`<td ><div style="width:70px;">

                <button data-id="${obj.replacement_id}" class="OrgCardInfoBtn" title="Se oprindelige kort info" ><img src="/gavefabrikken_backend/units/assets/icon/Find-replace-01.png" alt="" width="15" />${obj.repl_username} </button>

                </div>
                </td>`;
        }
        let hasMail = obj.user_email == "" ? 0:1;
        return `
        <tr class="tabel-replace">
            <td ${style} title="${obj.company_order_id}">ERSTATNING</td>
            <td ${style}>${obj.shopalias}</td>
            <td ${style}>${obj.username}</td>
            <td ${style}>${obj.password}</td>
            <td ${style}>${obj.expire_date}</td>
            <td ${style}>${obj.user_name}</td>
            <td ${style}>${obj.user_email}</td>
            <td ${style}>${obj.model_name}</td>
            <td ${style}>${obj.model_no}</td>
            <td ${style}>${obj.fullalias}</td>
            <td ${style}>${obj.model_present_no}</td>
            <td ${style}>${deliveryNote}</td>
            <td ${style}><button type="button" class="btn btn-outline-danger replace-delete-btn" data-id="${obj.id}" card-nr="${obj.username}">Slet</button></td>
            ${options}
        </tr>
        `}).join('');
        $("#card-view tbody").append(html);
         this.SetEvents();
    }

     
  async  SetEvents(){
        let self = this;

        // New order


      $('.deliverynote').unbind('click').click( function(){

          self.openTrackTrace($(this).attr("data-cardusername"));
          }
      )

        $(".OrgCardInfoBtn").unbind("click").click(
            function(){
                self.openSingleCardInfo($(this).attr("data-id"));
            }
        )

        $(".replace-delete-btn").unbind("click").click(
            function(){
                let r = confirm("Vi du slette kort: "+$(this).attr("card-nr"))
                if(r) self.doRemoveSingle($(this).attr("data-id"));
            }
        )

        $(".cardOpBtn").unbind("click").click(
            function(){
                self.openCardOption( $(this).attr("data-id") );
            }
        )
        $(".complaintBtn").unbind("click").click(
            function(){
                self.openComplaint( $(this).attr("data-id") );
            }
        )



        $(".multi-present-select").unbind("click").click(
            function(){
                self.multiPresentSelectShow()
            }
        )


        $("#cardshop-newOrder-btn").unbind("click").click(
            function(){
                self.NewOrder();
            }
        )
       // Show cards
       $('#card-view').DataTable({
             "scrollY":        "calc(100vh - 280px)",
                "scrollCollapse": true,
                "paging":         false,
            dom: 'Bfrtip',
            buttons: [
            {
                text: 'Marker alle',
                action: function ( e, dt, node, config ) {
                    $(".cardshop-cards-select").prop('checked', true);
                }
            },
            {
                text: 'Frav&oelig;lg alle',

                action: function ( e, dt, node, config ) {
                    $(".cardshop-cards-select").prop('checked', false);
                }
            },
            {
                text: 'Vis slettede kort',
                action: function ( e, dt, node, config ) {
                    self.ShowDeletedCards();
                }
            },
        ]
       });
       // hide paging in datatable component
       $("#card-view_length").hide();
       // move card
       $("#cardshop-transferCards-btn").unbind("click").click(
        function(){
            let cardList = [];
            $(".cardshop-cards-select").each(function( index ) {
                if($(this).is(":checked") ) {
                    cardList.push({cardId:$(this).attr("card-number"),companyorderid:$(this).attr("order-id")});
                }
            })
            if(cardList.length > 0 ) {
                self.PrepareMoveCard(cardList);
            }
       })

       $(".pdfcards").unbind("click").click(
        function(){
            let cardList = [];
            $(".cardshop-cards-select").each(function( index ) {
                if($(this).is(":checked") ) {
                    cardList.push($(this).attr("card-number"));
                }
            })
            if(cardList.length > 0 ) {
                self.buildCards(cardList);
            }
       })

      $(".proforma").unbind("click").click(
          function(){
              let cardList = [];
              $(".cardshop-cards-select").each(function( index ) {
                  if($(this).is(":checked") ) {
                      cardList.push($(this).attr("card-number"));
                  }
              })
              if(cardList.length > 0 ) {
                  self.proformaCards(cardList);
              }
          })




       // change dato
       $("#cardshop-changeDateV2-btn").unbind("click").click(
            function(){
                let companyOrderList = [];
                let cardshop = -1;
                let selectedShopGroup = "";
                $(".cardshop-cards-select").each(function( index ) {
                    if($(this).is(":checked") ) {
                      if(selectedShopGroup == ""){
                        self.selectedWeek = $(this).attr("shop-id");
                        selectedShopGroup =  eval("self.conceptGroup.shop"+$(this).attr("shop-id"));
                        companyOrderList = [...companyOrderList,$(this).attr("order-id")];
                      } else
                            if( selectedShopGroup == eval("self.conceptGroup.shop"+$(this).attr("shop-id")) ) {
                                companyOrderList = [...companyOrderList,$(this).attr("order-id")];
                            } else {
                                companyOrderList = false;
                                return;
                            }
                    }
                });
                if(companyOrderList.length > 0 ||companyOrderList == false) {
                    self.PrepareChangeDeadline(companyOrderList);
                }
             }
        )
        // delete
        $("#cardshop-delete-btn").unbind("click").click(

            function(index){
                let companyOrderList = [];

                $(".cardshop-cards-select").each(function( index ) {
                    if($(this).is(":checked")){
                      companyOrderList.push({
                          orderID:$(this).attr("order-id"),
                          cardnr:$(this).attr("card-number"),
                      });
                    }
                });
                if (!confirm("Du sletter nu "+companyOrderList.length +" kort, de vil blive krediteret kunden.") ) return
                self.Remove(companyOrderList);

            }
        )
      $("#cardshop-block-btn").unbind("click").click(

          function(index){
              let companyOrderList = [];

              $(".cardshop-cards-select").each(function( index ) {
                  if($(this).is(":checked")){
                      companyOrderList.push({
                          orderID:$(this).attr("order-id"),
                          cardnr:$(this).attr("card-number"),
                      });
                  }
              });
              if (!confirm("Vil du ændre blokering på "+companyOrderList.length +" kort? Ikke blokerede kort blokkeres, blokkerede kort åbnes igen. Blokkerede kort bliver ikke krediteret men der kan ikke vælges på dem og de trækkes ikke ud til afsendelse.") ) return
              self.Shutdown(companyOrderList);

          }
      )
        let companyData = await super.post("cardshop/companyform/get/"+this.companyID);
        this.companyPID = companyData.data.result[0].pid;
        if(companyData.data.result[0].pid == 0){
              $("#cardshop-newOrder-btn").show();
              this.isChild = false;
        } else {
              $("#cardshop-newOrder-btn").hide();
              this.isChild = true;
        }
    }

    //************  Card option ********************* //
   openCardOption(shopuser){
     new Cardoptions(this.companyID,shopuser);
   }
   openComplaint(shopuser){
     new Complaint(this.companyID,shopuser)
   }
   openSingleCardInfo(shopuserID) {
      let sc = new singleCard(shopuserID);
      sc.showCardInfo();
   }

    openTrackTrace(username) {
        $('#cardshop-supersearch-btn').trigger('click');
        $('#textSupersearch').val(username);
        $('#doSupersearch').trigger('click');
        setTimeout(function() {  $('.search-track-trace').trigger('click')},1000);
    }

   //******************* multi gave valg



   async multiPresentSelectShow(){
    let self = this;
      // find shop
    let shopid = true;
    $(".cardshop-cards-select").each(function( index ) {
        if($(this).is(":checked") && shopid != false ) {
            if(shopid == true) {
              shopid = $(this).attr("shop-id")
            } else {
                if(shopid != $(this).attr("shop-id")  ){
                    alert("ikke samme kort type")
                    shopid = false;
                }
            }

        }
    })
    if(shopid == false) return;
   /*
    if(shopid == "") {
        alert("Du mangler at afkrydse de kort, du vil foretage gavevalg p")
    return;
    }
    */
    let presentList = await this.loadShopPresents(shopid);
    presentList =  JSON.parse(presentList);
     let presentsHtml = "<div></div><center><table class='customTable' border=1 >";
     var tempHtml = "";

     for(var i=0;presentList.data.length >i;i++){
            var rd = presentList.data[i].attributes;
            tempHtml+="<tr><td height=30 width=200><img width=60 src='"+rd.media_path+"' /></td><td width=200>"+rd.model_present_no+"</td><td width=200>"+rd.model_name+"</td><td width=200 id='model_"+rd.model_id+"'>"+rd.model_no+"</td><td><button class='regPresens'  data-modeltext='"+rd.model_no+"' data-model_name='"+rd.model_name+"' data-present_id='"+rd.present_id+"' data-model_id='"+rd.model_id+"'  \">V&oelig;lg</button></td></tr>";

     }

     presentsHtml+=tempHtml ;
     presentsHtml+="</table></center>"
     super.OpenModal("Multi gavevalg",presentsHtml,"");

       $(".regPresens").unbind("click").click(

       function(){
          let r = confirm("Vil du lave gavevalget om for kunden, der bliver sendt en ny kvittering til kunden")
          if(r == false) return;
          let presentData = {
            shopId: shopid,
            presentsId: $(this).attr("data-present_id"),
            modelName: $(this).attr("data-model_name"),
            modelId: $(this).attr("data-model_id"),
            model:   $(this).attr("data-modeltext"),
            model_id: $(this).attr("data-model_id"),
            skip_email: 0
          }
          self.selectPresentController(presentData)
       })

   }
   async selectPresentController(presentData)
   {
        let self = this;
        let presentDataObj = presentData;
        let giftproces = 0;
        //for (let i in this.earlyorderData.shipmentlist){
        let nrArray = [];
        $(".cardshop-cards-select").each(async function( index ) {
            if($(this).is(":checked") ) {
               nrArray.push({id:$(this).attr("user-id"),hasEmail:$(this).attr("has-mail")});
            }
        })
        super.OpenModal("Multi gavevalg","<div>Gaver oprettet</div><div><label>Total:"+nrArray.length+" : </label> <label id='giftproces'>0</labet></div>","");
        for (let i in nrArray){
            if(nrArray[i].hasEmail == 0){
                await super.post("cardshop/cards/insetDummyEmail",{id:nrArray[i].id});
            }
            await self.doOrderPresent(presentDataObj,nrArray[i].id);
            giftproces++;
            $("#giftproces").html(giftproces);

        }
        new Cards(this.companyID);
        super.CloseModal()
   }
   async doOrderPresent(data,userID)
   {
        data.userId = userID;
        return new Promise(resolve => {
            $.post("index.php?rt=order/changePresent", data, function(returData, status){

                var returData = JSON.parse(returData);
                if(returData.status == "0") {

                    var extraMessage = "";
                    if(returData.message == 'closed') {
                        extraMessage = " - det ser ud til at forsendelse af gaven er behandlet!";
                    }

                    if($('.cardshop-cards-select[user-id='+data.userId+']').closest('tr').find('td').length > 2) {
                        alert('Fejl ved ændring af gave på '+$($('.cardshop-cards-select[user-id='+data.userId+']').closest('tr').find('td').get(2)).text()+' - '+returData.message+extraMessage);
                    } else {
                        alert('Fejl ved ændring af gave til bruger id '+data.userId);
                    }
                }

              setTimeout(function(){
                resolve("");
              }, 500)
            })
        })
   }




   loadShopPresents(shopid){
        return new Promise(resolve => {
            $.post("index.php?rt=shop/getShopPresentsNew", {"shop_id":shopid}, function(returData, status){
                resolve(returData);
            })
        })
   }




    proformaCards(cardList){

        var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
            action: 'index.php?rt=unit/cardshop/cards/proformainvoice'
        }).appendTo(document.body);
        $('<input type="hidden" />').attr({
            name: "data",
            value: cardList.join(',')
        }).appendTo(f);
        f.submit();
        f.remove();
    }
    buildCards(cardList){
console.log(cardList)
       var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
        action: 'https://system.gavefabrikken.dk/kundepanel/printCustonCards.php'
        }).appendTo(document.body);
        $('<input type="hidden" />').attr({
                name: "data",
                value: cardList.join(',')
            }).appendTo(f);
        f.submit();
        f.remove();
    }


    async PrepareMoveCard(data){
        let self = this;
        let childData;
        if(this.isChild == false){
            childData = await super.post("cardshop/companylist/childs/"+this.companyID);
        } else {
            childData = await super.post("cardshop/companylist/childs/"+this.companyPID);
        }
        super.OpenModal("V&oelig;lg leveringsadressen",tpCardForm.childList(childData.result),tpCardForm.saveChangeshopsaleweeks());
        if(this.isChild == true){
          $("#childList_tp").append("<option value='100' data-id='' company-id='"+this.companyPID+"'>-- Hovedadressen --</option>")
        }


        $("#saveChangeshopsaleweeks").unbind("click").click(
            function(){
                let targetCompany = $("#childList_tp").find(':selected').attr('company-id');
                self.MoveCard(data,targetCompany)
            }
        )
        $("#saveChangeshopsaleweeks").html("Flyt "+data.length +" kort");
    }
    async MoveCard(data,targetCompany){
        let self = this;
        data.map(async card => {
            await self.DoMoveCard(card,targetCompany);
        })
        
        super.CloseModal();
        super.Toast("Alle kort er nu flyttet");
        this.ReloadCards();
    }

    async DoMoveCard(card,targetCompany){
        return new Promise(async resolve => {
            await super.post("cardshop/cards/movecards/"+card.companyorderid,{
                companyid:targetCompany,
                startcertificate:card.cardId,
                endcertificate:card.cardId
            });
            resolve();
        })
    }


    async PrepareChangeDeadline(companyOrderList)
    {
      let self = this;
      if(companyOrderList == false) {
          super.Toast("De valgte kort skal stamme fra samme konsept, handlingen kan ikke udf&oslash;res","FEJL",true)
          return;
      }

      for (let i in companyOrderList) {
          $("#card-view").find(`[order-id='${companyOrderList[i]}']`).prop('checked', true);
      }
      await this.timer();

      let r = confirm("Alle kort som bliver aendre er nu markeret, vil du forsaette ? ")
      if(r){
        let weeks =  await super.post("cardshop/orderform/shopweeks/"+this.selectedWeek,{});
        super.OpenModal("V&oelig;lg deadline dato",tpCardForm.changeshopsaleweeks(weeks.result),tpCardForm.saveChangeshopsaleweeks());
        $("#saveChangeshopsaleweeks").unbind("click").click(
            function(){
                if($("#changeshopsaleweeks_pt").val() == "0"){
                 self.ChangeDeadlineMsg("FEJL","Du skal v&oelig;lge en dato",true);
                } else {
                    self.ChangeDeadline($("#changeshopsaleweeks_pt").val(),companyOrderList )

                }

            }
        )
        $("#saveChangeshopsaleweeks").html("Du flytter nu  "+companyOrderList.length +" kort");
      }
    }
    ChangeDeadlineMsg(msg,title,option){
        super.Toast(msg,title,option);
    }
    async ChangeDeadline(week,companyOrderList) {
        let self = this;
        companyOrderList.map(async ordernr => {
            await self.DoChangeDeadline(week,ordernr)
        })
        super.CloseModal();
        super.Toast("&OElig;ndring af deadline er nu &oelig;ndret");
        this.ReloadCards();
    }
    async DoChangeDeadline(week,ordernr){
        return new Promise(async resolve => {
            console.log(week,ordernr);
            await super.post("cardshop/orderform/moveexpiredate/"+ordernr,{expire_date:week});
            resolve();
        })

        //index.php?rt=unit/cardshop/orderform/moveexpiredate/[company_order_id]
    }



    async Remove(companyOrderList){
       let self = this;
      for (let i in companyOrderList){
            console.log(companyOrderList[i])
            await self.doRemove(companyOrderList[i])
       };


       /*
        companyOrderList.map(async card => {
            console.log(card)
            await self.doRemove(card)
        })
         */
       // await this.timer();
        this.ReloadCards();
    }

    async doRemove(card){
        return new Promise(async resolve => {
            await super.post("cardshop/cards/block/"+card.orderID,{certificatelist:[card.cardnr]});
            resolve();
        })
    }

    async Shutdown(companyOrderList){
        let self = this;
        for (let i in companyOrderList){
            console.log(companyOrderList[i])
            await self.doShutdown(companyOrderList[i])
        };

        // await this.timer();
        this.ReloadCards();
    }

    async doShutdown(card){
        return new Promise(async resolve => {
            await super.post("cardshop/cards/shutdown/"+card.orderID,{certificatelist:[card.cardnr]});
            resolve();
        })
    }




    async doRemoveSingle(shopuserID)
    {
        await super.post("cardshop/cards/blockSingleCard/"+shopuserID);
        super.Toast("Kort slettet");
        this.ReloadCards();
    }

    NewOrder() {
        new ShowOrderForm(this.companyID  );
    }
    async ChangeDateShowDate(companyOrderList,shopId){
      if(companyOrderList.length > 1){
          super.Toast("Kan ikke &oelig;ndre dato p&aring; flere ordre p&aring; &egrave;n gang","Fejl",true)
          return;
      }
      let week = await post("orderform/companyshops/"+shopId)
      console.log(week);
      //alert(companyOrderList[0])
    }

    // show delete cards
       async ShowDeletedCards(){
        let self = this;
        var table = $('#card-view').DataTable();
        let result = await super.post("cardshop/cards/loadBlockedCardsByCompanyID/"+this.companyID);
        let html  = `<br><table id="card-view" class="display">
    <thead>
        <tr>
            <th>BS nr</th>
            <th>Kort Type</th>
            <th>Nr</th>
            <th>Kode</th>
            <th>dato</th>
            <th>Navn</th>
            <th>Email</th>
            <th>Gave</th>
            <th>Model</th>
            <th>Varenr</th>
            <th></th>

        </tr>
    </thead>
    <tbody>`+
      result.data.result.map(function(obj){
        return `
        <tr>
            <td>${obj.bsno}</td>
            <td>${obj.alias}</td>
            <td>${obj.username}</td>
            <td>${obj.password}</td>
            <td>${obj.expire_date}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><input class="cardshop-cards-select" shop-id ="${obj.shop_id}" order-id="${obj.company_order_id}" card-number="${obj.username}" type="checkbox" /></td>

        </tr>
        `}).join('') +`
    </tbody>
</table>`;

        /*
              <td><img src="https://gavefabrikken.dk/2021/gavefabrikken_backend/views/media/icon/history.png" alt="" width="20" /></td>
            <td><img src="https://gavefabrikken.dk/2021/gavefabrikken_backend/views/media/icon/gave.png" alt="" width="20" /></td>
        */

        $(".tab-cards").html(html);
      //  $("#cardshop-tabs-action").html('<button id="cardshop-newOrder-btn" type="button" class="btn btn-warning">New Order</button> <button id="cardshop-changeDateV2-btn" type="button" class="btn btn-outline-primary">&OElig;ndre dato</button> <button id="cardshop-transferCards-btn" type="button" class="btn btn-outline-primary">Flyt kort</button>  <button id="cardshop-delete-btn" type="button" class="btn btn-outline-danger">Slet</button>');
    $("#cardshop-tabs-action").html('<button id="cardshop-reactive-btn" type="button" class="btn btn-outline-danger">Aktivere Slettede kort</button>');

    // set Events
    table.destroy();
    $('#card-view').DataTable({
            "paging": false,
            dom: 'Bfrtip',
            buttons: [
            {
                text: 'Marker alle',
                action: function ( e, dt, node, config ) {
                    $(".cardshop-cards-select").prop('checked', true);
                }
            },
            {
                text: 'Frav&oelig;lg alle',

                action: function ( e, dt, node, config ) {
                    $(".cardshop-cards-select").prop('checked', false);
                }
            },
            {
                text: 'Vis aktive kort',
                action: function ( e, dt, node, config ) {
                    self.ReloadCards();
                }
            },
        ]
       });

       $("#cardshop-reactive-btn").unbind("click").click( function(index){
                let companyOrderList = [];

                $(".cardshop-cards-select").each(function( index ) {
                    if($(this).is(":checked")){
                      companyOrderList.push({
                          orderID:$(this).attr("order-id"),
                          cardnr:$(this).attr("card-number"),
                      });
                    }
                });
                if (!confirm("Du aktivere nu "+companyOrderList.length +" slettede kort") ) return
                self.Reactivate(companyOrderList);


       } )



    }


    async Reactivate(companyOrderList){
        let self = this;
        companyOrderList.map(async card => {
            await self.doReactivate(card)
        })

       // await this.timer();
        this.ReloadCards();
    }
    async doReactivate(card){
        return new Promise(async resolve => {
            await super.post("cardshop/cards/unblock/"+card.orderID,{certificatelist:[card.cardnr]});
            resolve();
        })
    }













    ReloadCards() {

        new Cards(this.companyID);
    }
    timer(){
            return new Promise(async resolve => {
            setTimeout(function(){
                resolve();
            }, 300)

        })
    }


  /***  Layout logic   */


  /***  Bizz logic   */



}