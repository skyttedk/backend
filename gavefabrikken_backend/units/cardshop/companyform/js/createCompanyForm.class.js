import CompanyForm from '../js/companyForm.class.js?v=123';
import tpCompanyForm from '../tp/companyForm.tp.js?v=123';
import NAV from '../../main/js/nav.js?v=123';
import External from '../../main/js/external.js?v=123';
import Company from '../../company/js/company.class.js?v=123';
import Companylist from '../../companylist/js/companylist.class.js?v=123';

import Base from '../../main/js/base.js';
export default class CreateCompanyForm  extends Base {
  constructor() {
        super()
        this.Company = new Company();
        this.CompanyForm = new CompanyForm();
        this.Nav = new NAV();
        this.Ext = new External();
//        this.cvr = new CompanyFormCvr();
        // local routing
        this.Layout();
        this.SetEvents();
  }

  Layout() {
    $(".modal-body").html("System is working");
    $("#ModalFullscreenLabel").html("Create new company");
    $(".cardshop-main-content").html("");
    $("#cardshop-tabs-action-stamdata").html("");
    $(".modal-body").html(tpCompanyForm.createform());
    $("#language_code").val(window.LANGUAGE)
  }


  SetEvents() {
    let me = this;

    // setup form logic
    $("#gaveLevAdressTitelOptions").unbind("click").click(()=>
      {
        if($("#shippingForm").hasClass("hide") == true) {
          $("#shippingForm").slideDown("hide").removeClass("hide");
          $("#gaveLevAdressTitel").removeClass("text-deactivated")
        } else {
          $("#shippingForm").slideUp("hide").addClass("hide");
          $("#gaveLevAdressTitel").addClass("text-deactivated")
        }
      }
    );
    // bind save methode
    $("#conpanyFormSave").unbind("click").click( () => {

        // read billToFields group
        let formData = me.CompanyForm.ReadFormValues("billToFields");
        // read contactPerson group
        Object.assign(formData,me.CompanyForm.ReadFormValues("contactPerson"))
        // read shippingForm group if active
        if(!$("#shippingForm").hasClass("hide")){
            Object.assign(formData,me.CompanyForm.ReadFormValues("shippingForm"))
        }
        // validate active form group, if verifid go save
        me.CompanyForm.validateformData(formData,true) == false ? me.createCompany(formData) : alert("fejl");


    })
    // setting up cvr
    $("#lookupCvr").unbind("click").click(
        function(){
            $(".cardshop-sidebar-right").addClass("zzz")
             me.LookupCvr();
    });
    $("#lookupCompany").unbind("click").click(
        function(){
             me.LookupCompany();
    });

  }
  async LookupCompany(){
     let ME = this;
     let navn = $("#name").val();
     if(navn.length == "") {
        super.Toast("Feltet skal udfyldes","FEJL",true);
        return;
     }
     super.OpenRightPanel("OPSLAG VIA NAVN","Systemet arbejder");
     let navList = await this.Nav.SearchNameFromNAV(navn);
    let html = "";
    let NavListHtml = this.HandleNavList(navList);
    if(NavListHtml == "") NavListHtml = "Intet resultat";
    html = "<div><h3>Fra NAVISION</h3></div><hr>"+NavListHtml;
    super.OpenRightPanel("OPSLAG VIA NAVN",html);
    $(".lookupTransferData").unbind("click").click(
        function(){
            if(confirm("godkend") == false) return;
            let id = $(this).attr("data-id");
            $("#name").val( $("#"+id).find("#datafak-virksomhed").html() );
            $("#bill_to_city").val( $("#"+id).find("#datafak-bynavn").html() );
            $("#bill_to_postal_code").val( $("#"+id).find("#datafak-postnr").html() );
            $("#bill_to_address").val( $("#"+id).find("#datafak-adress1").html() );
            let ern = $("#"+id).find("#datafak-adress2").html() == null ? $("#"+id).find("#datafak-adress2").html() : "";
            $("#bill_to_address_2").val( ern );
            ME.doCloseRightPanel();
        }

    )

  }



  async LookupCvr(){
     let ME = this;

     let cvr = $("#cvr").val();
     if(window.LANGUAGE == 1){
        if(cvr.length != 8) {
         super.Toast("Cvr er ikke 8 cifre","FEJL",true);
         return;
        }
     }
     if(window.LANGUAGE == 4){
        if(cvr.length != 9) {
         super.Toast("Cvr er ikke 9 cifre","FEJL",true);
         return;
        }
     }


     super.OpenRightPanel("CVR OPSLAG","Systemet arbejder","not");
     let navList = await this.Nav.getCvrDataFromNAV(cvr);
     let extList = await this.Ext.getCvrData(cvr);

     // handle navList
    let html = "";
    let NavListHtml = "Intet resultat";
    let ExtListHtml = "Intet resultat";
    let JnavList = JSON.parse(navList);
    let JextList = JSON.parse(extList);
    if(JnavList.customers.length > 0){
        NavListHtml = this.HandleNavList(navList);
    }
    if(JextList.status == 1){
        ExtListHtml = this.HandleExtList(extList);
    }
    html = "<div><h3>Fra NAVISION</h3></div><hr>"+NavListHtml+"<div><h3>Fra CVR</h3></div><hr>"+ExtListHtml;
    super.OpenRightPanel("CVR OPSLAG",html,"not");

    $(".lookupTransferData").unbind("click").click(
        function(){
            if(confirm("godkend") == false) return;
            let id = $(this).attr("data-id");
            $("#name").val( $("#"+id).find("#datafak-virksomhed").html() );
            $("#bill_to_city").val( $("#"+id).find("#datafak-bynavn").html() );
            $("#bill_to_postal_code").val( $("#"+id).find("#datafak-postnr").html() );
            $("#bill_to_address").val( $("#"+id).find("#datafak-adress1").html() );
            let ern = $("#"+id).find("#datafak-adress2").html() == null ? $("#"+id).find("#datafak-adress2").html() : "";
            $("#bill_to_address_2").val( ern );
            ME.doCloseRightPanel();
        }

    )
  }
  doCloseRightPanel(){
    super.CloseRightPanel()
  }
  HandleNavList(data){
    let jdata = JSON.parse(data).customers ;
    let html = [];
    jdata.map(function(ele){
        let datafak = {};
        datafak.virksomhed = ele.name
        datafak.bynavn  = ele.city
        datafak.postnr   = ele.postcode
        datafak.adress1   = ele.address
        datafak.adress2   = ""
        datafak.ean   = ele.ean
        const rand=()=>Math.random(0).toString(36).substr(2);
        const token=(length)=>(rand()+rand()+rand()+rand()).substr(0,length);
        let id = token(20);
        html+= tpCompanyForm.lookupBillForm(id,datafak);
    }).join('');
    return html
  }
  HandleExtList(data){
    let jdata = JSON.parse(data).company ;
    let html = [];
    jdata.productionunits.map(function(ele){
        let datafak = {};
        datafak.virksomhed = ele.name
        datafak.bynavn  = ele.city
        datafak.postnr   = ele.zipcode
        datafak.adress1   = ele.address
        datafak.adress2   = ele.addressco
        datafak.ean   = ""
        const rand=()=>Math.random(0).toString(36).substr(2);
        const token=(length)=>(rand()+rand()+rand()+rand()).substr(0,length);
        let id = token(20);
        html+= tpCompanyForm.lookupBillForm(id,datafak);
    });
    return html
  }
  /***  Layout logic   */



  /***  Bizz logic   */
   async createCompany(formData)
   {

        let respons = await this.Company.createCompany(formData);
        let obj = JSON.parse(respons);
        if(obj.status == 0) {
            super.Toast("Der opstod en fejl","FEJL",true);
            return;
        }

        super.CloseModal();
        super.Toast("Det nye firma er blevet oprettet");
        let cl = new Companylist;
        cl.InsetInSearch(formData.cvr);
        cl.SearchAndSelect(obj.data.result[0].id);







   }

}
$.fn.hasAttr = function(name) {
    return this.attr(name) !== undefined;
};