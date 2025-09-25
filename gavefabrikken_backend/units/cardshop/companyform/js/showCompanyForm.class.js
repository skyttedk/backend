var _ShowCompanyForm;
import CompanyForm from '../js/companyForm.class.js?v=123';
import tpCompanyForm from '../tp/companyForm.tp.js?v=123';
import Companylist from '../../companylist/js/companylist.class.js?v=123';
import Company from '../../company/js/company.class.js?v=123';
import Base from '../../main/js/base.js';

export default class ShowCompanyForm  extends Base {
  constructor(companyID) {
        super()
        _ShowCompanyForm = this;
        _ShowCompanyForm.CompanyForm = new CompanyForm();
        _ShowCompanyForm.data = {};
        this.ramdom = super.RamdomString()
        this.invoiceData;
        this.isChild = false;
        this.timer;
        this.NAV = true;


        // local routing

  }
  async Init(companyID){

    this.NAVtest(companyID);
    this.companyID = companyID;
    let companyData = await super.post("cardshop/companyform/get/"+companyID);
    _ShowCompanyForm.data = companyData;
   // console.log(companyData)
      try {
          this.invoiceData = await super.post("cardshop/companyform/invoicedata/" + companyID, {}, false);
      }
      catch(err) {
          this.NAV = false;
      }
      clearTimeout(this.timer)
      this.preLayout()



  }
  NAVtest(){
      var self = this;
      this.timer = setTimeout(function(companyID){
          self.NAV = false;
          $(".cardshop-main-content").html("<h4>Navision serveren er ikke tilgængelig, derfor kan du ikke benytte CardShop. Prøv igen senere.</h4>");

      }, 5000);
  }
  preLayout(){
// Handle call to action
      $("#cardshop-tabs-action").html('');
      $("#cardshop-tabs-action-stamdata").show();
      $("#cardshop-tabs-action").hide();

      this.Layout(_ShowCompanyForm.data);
  }
  Layout(data){
    if(this.NAV == false){
       return;
    }
    let  isChild = false;
    if(data.data.result[0].pid == 0){
        $(".tab-masterdata").html(tpCompanyForm.showMasterdataForm());
        this.invoiceData.status == 0 ? _ShowCompanyForm.CompanyForm.InsetFormValue(data.data.result[0],"billToFields") : _ShowCompanyForm.CompanyForm.InsetFormValue(this.invoiceData.data.result,"billToFields");
        _ShowCompanyForm.CompanyForm.InsetFormValue(data.data.result[0],"contactPerson");
        _ShowCompanyForm.CompanyForm.InsetFormValue(data.data.result[0],"shippingFormOrBill");
        $("#cardshop-tabs-action-stamdata").html('<button id="cardshop-add-delivery-address-btn" type="button" class="btn btn-outline-primary" >Opret ny leveringsadress (child)</button>');
    } else {
         isChild = true;
         $($("#cardshop-tabs").find("li")[5]).hide();
         $($("#cardshop-tabs").find("li")[4]).hide();
         $($("#cardshop-tabs").find("li")[3]).hide()
         $($("#cardshop-tabs").find("li")[2]).hide()
         $("#cardshop-tabs-action-stamdata").html('<button id="masterData-child-delevery-edit-btn" type="button" class="btn btn-outline-primary" >Redigere adresse</button>');
        
        // let pdf_token =  _ShowCompanyForm.data.data.result[0].token;
         let pdf_company_id =  _ShowCompanyForm.data.data.result[0].id;
         let pdfhtml = `<button class="btn btn-outline-info" style="float:left; margin-right:10px;"  ><a href="https://system.gavefabrikken.dk/kundepanel/printcardszip.php?id=${pdf_company_id}&token=child" target="_blank">pdf koder(ZIP enkeltvis)</a></button><br>`
         $(".tab-masterdata").html(pdfhtml+"<div id='showMasterdataForm'>"+tpCompanyForm.shippingForm(_ShowCompanyForm.data)+"</div>");
         $(".tab-masterdata").html( _ShowCompanyForm.CompanyForm.InsetFormValue(data.data.result[0],"shippingFormOrBill"));

            $('#showMasterdataForm').find('.freightnotesframe').remove();
         $('#showMasterdataForm').append("<div class='freightnotesframe'><br><br><label><b>Leverancer og fragt opsætning</b></label><hr><iframe src='index.php?rt=unit/cardshop/freight/companyfreightform/"+ _ShowCompanyForm.data.data.result[0].id+"/1' style='width: 100%; height: 800px; border: 0px;'></iframe></div>");

    }
    $("#showMasterdataForm").find("input").prop( "disabled", true );
    $("#showMasterdataForm").find("select").prop( "disabled", true );

    data.data.result[0].shutdown == 1 ? $("#shutdown").prop( "checked", true ): $("#shutdown").prop( "checked", false );

    // Show navision data
    if(data.data.result[0].id > 0) {
        let companyStates = {0: '0: Arkiveret (ikke i brug)', 1: '1: Afventer overførsel til nav',2: '2: Virksomhed på fejlliste', 3:'3: Afventer overførsel efter blokkering', 4: '4: Blokkeret', 5: '5: Synkroniseret til nav', 6: '6: Synkronisering fejlet', 7: '7: Child virksomhed (synkroniseres ikke)'}
        let companyStatus = 'Ukendt status';
        if(companyStates.hasOwnProperty(data.data.result[0].company_state)) companyStatus = companyStates[data.data.result[0].company_state];
        $("#company_nav_status").val(companyStatus);
        $("#company_nav_debitor").val(data.data.result[0].nav_customer_no);
        $('#navstatuspanel').show()
    }

    if($('#cardshop-supersearch-btn').is(':visible')) {
        $(".searchMails").each(function() {
            let email = $(this).closest('.row').find('input[type=text]').val();
            if($.trim(email) != "") {
                $(this).show()
            }
        })
    }

    _ShowCompanyForm.SetEvents();

  }
  SetEvents(isChild=false){
        let ME = this
        $("#cardshop-add-delivery-address-btn").unbind("click").click(
            () => {
               // super.Toast("Masterdata has been updated")
                ME.AddNewDeliveryAddress();
                $('.modal .ship_to_phone').closest('.row').show();
//                new ShowCompanyForm(_ShowCompanyForm.companyID);
            }
        )

        $("#masterData-invoice-edit-btn").unbind("click").click(
            () => {
               ME.EditInvoice();
            }
        )
        $("#masterData-contact-edit-btn").unbind("click").click(
            () => {
               ME.EditContact();
            }
        )
        $("#masterData-child-delevery-edit-btn").unbind("click").click(
            () => {
               // super.Toast("Masterdata has been updated")

               ME.isChild = true;
               ME.EditDeliveryAddress();
            }
        )
        $("#masterData-delevery-edit-btn").unbind("click").click(
            () => {
               // super.Toast("Masterdata has been updated")
               ME.EditDeliveryAddress();
            }
        )
        $("#shutdown").unbind("click").click(
            function(){
              ME.updateShutdown();
            }
        )

      $(".searchMails").unbind("click").click(
          function(){
            ME.SearchMailHistory(this);
          }
      )

      $('.navstopsync').unbind("click").click(
          function(){
              ME.StopNavSync();
          }
      )

      $('.navchangeno').unbind("click").click(function(){
           $('#navchangeno').show();
      })

      $('.navchangenosubmit').unbind("click").click(function(){
          ME.ChangeNavNo();
      })



  }

    async ChangeNavNo(){

        let response = await super.post("cardshop/companyform/changenavno",{ companyID:this.companyID,navno:$('#navchangenoval').val() });
        this.Toast(response.message,null,response.status == 0 ? true : false);
        if(response.status == 1) {
            $('#navchangeno').hide();
            $('#navchangenoval').val('')
        }

    }

    async StopNavSync(){
      let self = this;
      let message = prompt("Er du sikker på at du vil stoppe navision synkronisering for denne kunde? Det vil oprette en fejlbesked som skal godkendes før synkronisering genoptages. Skriv hvorfor synkronisering skal stoppes her:");
      if(message === null) {
          return;
      }
      else if($.trim(message) == "") {
          alert("Kunne ikke oprette blokkering, der skal skrives en besked.");
          return;
      }
      let respons = await super.post("cardshop/companyform/manblock/"+this.companyID,{msg:message})
        if(respons.status == 1) {
            alert('Manuel blokkering er oprettet');
        }

  }
  SearchMailHistory(elm) {
      var email = $(elm).closest('.row').find('input[type=text]').val();
      if($.trim(email) != "" && $('#cardshop-supersearch-btn').is(':visible')) {
          $('#cardshop-supersearch-btn').trigger('click')
          $('#textSupersearch').val(email)
          $('#doSuperMail').trigger('click');
      }
  }
  EditInvoice(){
    let self = this;
    super.OpenModal("Redigere Faktura adresse",tpCompanyForm.billForm(false,this.ramdom ),tpCompanyForm.save(this.ramdom));
    let ele = "."+this.ramdom+".conpanyFormSave";
    self.invoiceData.status == 0 ? _ShowCompanyForm.CompanyForm.InsetFormValue(_ShowCompanyForm.data.data.result[0],"billToFields",self.ramdom) : _ShowCompanyForm.CompanyForm.InsetFormValue(self.invoiceData.data.result,"billToFields",self.ramdom);

    $(ele).unbind("click").click(
        function(){
          let formData = _ShowCompanyForm.CompanyForm.ReadFormValues("billToFields",self.ramdom)
          let html = "<table>";
          for (const [key, value] of Object.entries(formData)) {
              html+= `<tr><td>${key}</td><td>${value}</td></tr>`;
          }
          html+="</table>";
          self.UpdateEditInvoice(encodeURIComponent(html),formData);
        }
    )
  }


  EditContact(){
    let self = this;
    super.OpenModal("Redigere kontaktperson",tpCompanyForm.contactForm(this.ramdom ),tpCompanyForm.save(this.ramdom));
    let ele = "."+this.ramdom+".conpanyFormSave";
    // inset val             data,fields,unique="",ele="#"

    _ShowCompanyForm.CompanyForm.InsetFormValue(_ShowCompanyForm.data.data.result[0],"contactPerson",self.ramdom);
    $(ele).unbind("click").click(
        function(){
          let formData = _ShowCompanyForm.CompanyForm.ReadFormValues("contactPerson",self.ramdom)
          self.UpdateDeliveryAddress(formData);
        }
    )
  }


  EditDeliveryAddress(){

    let self = this;
    super.OpenModal("Redigere leveringsadresse",tpCompanyForm.shippingForm(this.ramdom ),tpCompanyForm.save(this.ramdom));
    let ele = "."+this.ramdom+".conpanyFormSave";
    // inset val             data,fields,unique="",ele="#"
    _ShowCompanyForm.CompanyForm.InsetFormValue(_ShowCompanyForm.data.data.result[0],"shippingFormOrBill",self.ramdom);
    $(ele).unbind("click").click(
        function(){
          let formData = _ShowCompanyForm.CompanyForm.ReadFormValues("shippingForm",self.ramdom,".")
          self.UpdateDeliveryAddress(formData);
        }
    )
  }
  async UpdateDeliveryAddress(formData){

    let respons = await super.post("cardshop/companyform/update/"+this.companyID,{companydata:formData})
        if(respons.status == 0) {
            super.Toast("Der opstod en fejl","FEJL",true);
            return;
        }
        super.CloseModal();
        super.Toast("Data er blevet oprettet");
        if(this.isChild == true ){
            this.isChild = false;
           // console.log(formData)
            //$(".companylist-selected").html("asdf�lkasjd")
            $(".companylist-companyname").html("<b>"+formData.ship_to_address+"</b>")
            $(".companylist-companyadress").html(formData.ship_to_company);
            let company = new Company();
            company.ShowCompany(this.companyID);

        } else {
            let InsetInSearch = $(".cardshop #companylist-search").val()
            let cl = new Companylist;
            cl.InsetInSearch(InsetInSearch);
            cl.SearchAndSelect(this.companyID);
        }
  }
  async UpdateEditInvoice(formDataHtml,formDataObj){
        let self = this;
        if(this.invoiceData.status == 0){
            let respons = await super.post("cardshop/companyform/update/"+this.companyID,{companydata:formDataObj},false)
            if(respons.status == 0) {
                super.Toast(respons.message,"Der opstod en fejl",true);
                return;
            } else {
                super.Toast("Leveringsadressen er opdateret");
                super.CloseModal();
            }
        } else {
           let respons = await super.post("cardshop/companyform/updateinvoicedata/"+this.companyID,{invoicedetails:formDataHtml},false)
            if(respons.status == 0) {
                super.Toast(respons.message,"Der opstod en fejl",true);
                return;
            } else {
                super.Toast("Den nye leveringsadresse er sendt til godkendelse.");
                super.OpenModal("V&oelig;r opm&oelig;rksom p&aring;","Den nye leveringsadresse er sendt til godkendelse.","<button class='close-modal'>Luk</button>");
                $(".close-modal").unbind("click").click( function(){ self.CloseModal() } ) ;
            }
        }
        let InsetInSearch = $(".cardshop #companylist-search").val()
        let cl = new Companylist;
        cl.InsetInSearch(InsetInSearch);
        cl.SearchAndSelect(this.companyID);
  }
  CloseModal(){
    super.CloseModal();
  }
  async AddNewDeliveryAddress(){
    let self = this;
    super.OpenModal("Opret ny leveringsadresse",tpCompanyForm.shippingForm(this.ramdom ),tpCompanyForm.save(this.ramdom));
    $("."+this.ramdom+".ship_to_country" ).val(window.LANGUAGE)

    let ele = "."+this.ramdom+".conpanyFormSave";

    $(ele).unbind("click").click(
        function(){
          let formData = _ShowCompanyForm.CompanyForm.ReadFormValues("shippingForm",self.ramdom,".")
          self.CreateNewLev(formData);
        }
    )
  }
  async CreateNewLev(formData){
        formData.pid = this.companyID;
        formData.cvr = 11111111
        formData.name = "child"
        formData.bill_to_address= "11111111"
        formData.bill_to_address_2 = ""
        formData.language_code = 1
        formData.bill_to_postal_code = 11111111
        formData.bill_to_city = "11111111"
        formData.ean = "11111111"
        formData.bill_to_email ="11111111"
        formData.contact_name = "11111111"
        formData.contact_phone = 11111111
        formData.contact_email = "11111111@11111111.dk"
        let InsetInSearch = $(".cardshop #companylist-search").val()
        let respons = await super.post("cardshop/companyform/create",{companydata:formData})
        if(respons.status == 0) {
            super.Toast("Der opstod en fejl","FEJL",true);
            return;
        }
        super.CloseModal();
        super.Toast("Den nye leveringsadresse er blevet oprettet");
        // updata companylist
        let cl = new Companylist;
        cl.InsetInSearch(InsetInSearch);
        cl.SearchAndSelect(this.companyID,window.LANGUAGE );
  }
  async updateShutdown(){
     let selected =  $('#shutdown').prop('checked') == true ? 1:0;
     let response = await super.post("cardshop/companyform/shutdown",{ companyID:this.companyID,settings:selected })
   //  console.log(response)
  }






}

/*

        console.log(data);
        let companyData = await super.post("cardshop/companyform/get/"+data.companyId);
        if(companyData.result.length == 0 ){
            // clear tabs
        }

        console.log(result);
        // load master data
        // load template
        // show tabs
        // open company master data tab
        // insert data
        // set edit mode
        // Sync from NAV
        //

        // request change ?

        */