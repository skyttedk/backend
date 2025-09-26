
import companyValidationClass from '../js/companyValidation.class.js?v=123';

export default class CompanyForm  {

  constructor() {

   // this.company = new companyClass;
    this.validation = new companyValidationClass;
  }


  InsetFormValue(data,fields,unique="",ele="#"){
     

    if(unique != "") { unique = "."+unique }

    console.log(fields);
    if(fields == "billToFields"){

        $(unique+ele+"cvr").val(data.cvr);
        $(unique+ele+"name").val(data.name);
        $(unique+ele+"ean").val(data.ean);
        console.log(data.cvr)
        // if source are from NAV
        if(data.hasOwnProperty('blocked')){
          $(unique+ele+"bill_to_address").val(data.address);
          $(unique+ele+"bill_to_address_2").val(data.address2);
          let language_code = window.LANGUAGE;
          if(data.country == "NO") language_code = 4;
          if(data.country == "SE") language_code = 5;
          $(unique+ele+"language_code").val(window.LANGUAGE);
          $(unique+ele+"bill_to_postal_code").val(data.postcode);
          $(unique+ele+"bill_to_city").val(data.city);
          $(unique+ele+"bill_to_email").val(data.bill_to_email);
        } else {
          $(unique+ele+"bill_to_address").val(data.bill_to_address);
          $(unique+ele+"bill_to_address_2").val(data.bill_to_address_2);
          $(unique+ele+"language_code").val(window.LANGUAGE);
          $(unique+ele+"bill_to_postal_code").val(data.bill_to_postal_code);
          $(unique+ele+"bill_to_city").val(data.bill_to_city);
          $(unique+ele+"ean").val(data.ean);
          $(unique+ele+"bill_to_email").val(data.bill_to_email);
        }
    }
    if(fields == "contactPerson"){
        $(unique+ele+"contact_name").val(data.contact_name);
        $(unique+ele+"contact_phone").val(data.contact_phone);
        $(unique+ele+"contact_email").val(data.contact_email);
    }
    if(fields == "shippingForm"){
 
          if(data.ship_to_country == "NO") data.ship_to_country = 4;
          if(data.ship_to_country == "SE") data.ship_to_country = 5;

        $(unique+ele+"ship_to_country").val(window.LANGUAGE);
        $(unique+ele+"ship_to_company").val(data.ship_to_company);
        $(unique+ele+"ship_to_attention").val(data.ship_to_attention);

        $(unique+ele+"ship_to_address").val(data.ship_to_address);
        $(unique+ele+"ship_to_address_2").val(data.ship_to_address_2);
        $(unique+ele+"ship_to_postal_code").val(data.ship_to_postal_code);
        $(unique+ele+"ship_to_city").val(data.ship_to_city);
    }
    if(fields == "shippingFormOrBill"){

        let ship_to_company;
        let ship_to_attention;
        let ship_to_address;
        let ship_to_address_2;
        let ship_to_postal_code;
        let ship_to_city;
        let language_code = window.LANGUAGE;



        if(data.hasOwnProperty('blocked')){
          ship_to_address = data.address;
          ship_to_address_2 = data.address2;

          ship_to_postal_code = data.postcode;
          ship_to_city = data.city
        } else {
          ship_to_address = data.bill_to_address
          ship_to_address_2 = data.bill_to_address_2
          language_code = window.LANGUAGE; //data.language_code
          ship_to_postal_code = data.bill_to_postal_code
          ship_to_city = data.bill_to_city
        }


        data.ship_to_country == ""      ?  $(unique+ele+"ship_to_country").val(window.LANGUAGE ) : $(unique+ele+"ship_to_country").val(window.LANGUAGE )
        data.ship_to_company == ""      ?  $(unique+ele+"ship_to_company").val(data.name) : $(unique+ele+"ship_to_company").val(data.ship_to_company)
        data.ship_to_attention == ""    ?  $(unique+ele+"ship_to_attention").val(data.contact_name)  : $(unique+ele+"ship_to_attention").val(data.ship_to_attention)
        data.ship_to_address == ""      ?  $(unique+ele+"ship_to_address").val(ship_to_address) : $(unique+ele+"ship_to_address").val(data.ship_to_address)
        data.ship_to_address_2 == ""    ?  $(unique+ele+"ship_to_address_2").val(ship_to_address_2)   : $(unique+ele+"ship_to_address_2").val(data.ship_to_address_2)
        data.ship_to_postal_code == ""  ?  $(unique+ele+"ship_to_postal_code").val(ship_to_postal_code) : $(unique+ele+"ship_to_postal_code").val(data.ship_to_postal_code)
        data.ship_to_city == ""         ?  $(unique+ele+"ship_to_city").val(ship_to_city)  : $(unique+ele+"ship_to_city").val(data.ship_to_city)

    }

        if(data.pid > 0) {
            $(unique+ele+"ship_to_phone").val(data.contact_phone).closest('.row').show();
        }

    if(fields == "earlyOrder"){
        $(unique+ele+"cardto_mobile").val(data.cardto_mobile);
        $(unique+ele+"cardto_gls").val(data.cardto_gls);
    }

    $("#dialog_Link").attr("href","https://system.gavefabrikken.dk/kundeside/?token="+data.token)
    $("#dialog_url").val("https://system.gavefabrikken.dk/kundeside/?token="+data.token)
  }


  ReadFormValues(fields,unique="",ele="#")
  {
    if(unique != "") { unique = "."+unique }

    let returnObj = {};
    if(fields == "billToFields"){
        returnObj.cvr                  = $(unique+ele+"cvr").val();
        returnObj.name                 = $(unique+ele+"name").val();
        returnObj.bill_to_address      = $(unique+ele+"bill_to_address").val();
        returnObj.bill_to_address_2    = $(unique+ele+"bill_to_address_2").val();
        returnObj.language_code        = window.LANGUAGE //$(unique+ele+"language_code").val();
        returnObj.bill_to_postal_code  = $(unique+ele+"bill_to_postal_code").val();
        returnObj.bill_to_city         = $(unique+ele+"bill_to_city").val();
        returnObj.ean                  = $(unique+ele+"ean").val();
        returnObj.bill_to_email        = $(unique+ele+"bill_to_email").val();

    }
    if(fields == "contactPerson"){
        returnObj.contact_name         =  $(unique+ele+"contact_name").val();
        returnObj.contact_phone        =  $(unique+ele+"contact_phone").val();
        returnObj.contact_email        =  $(unique+ele+"contact_email").val();
    }
    if(fields == "shippingForm"){
        returnObj.ship_to_country      = window.LANGUAGE //$(unique+ele+"ship_to_country").val();
        returnObj.ship_to_company      = $(unique+ele+"ship_to_company").val();
        returnObj.ship_to_attention    = $(unique+ele+"ship_to_attention").val();
        returnObj.ship_to_address      = $(unique+ele+"ship_to_address").val();
        returnObj.ship_to_address_2    = $(unique+ele+"ship_to_address_2").val();
        returnObj.ship_to_postal_code  = $(unique+ele+"ship_to_postal_code").val();
        returnObj.ship_to_city         = $(unique+ele+"ship_to_city").val();
        returnObj.ship_to_phone         = $(unique+ele+"ship_to_phone").val();
    }
    if(fields == "cardShippingForm"){
        returnObj.cardto_name          = $(unique+ele+"cardto_name").val();
        returnObj.cardto_address       = $(unique+ele+"cardto_address").val();
        returnObj.cardto_address2      = $(unique+ele+"cardto_address2").val();
        returnObj.cardto_postal_code   = $(unique+ele+"cardto_postal_code").val();
        returnObj.cardto_city          = $(unique+ele+"cardto_city").val();
    }
    if(fields == "earlyOrder"){
        returnObj.cardto_mobile        = $(unique+ele+"cardto_mobile").val();
        returnObj.cardto_gls           = $(unique+ele+"cardto_gls").val();
    }
    return returnObj;
  }

  validateformData(data,showError=false,unique="",ele="#")
  {
    let error = false;
    $(".error-field").removeClass("error-field");
    Object.entries(data).forEach(([key, value]) => {
        if(this.validation.ValidateField(key,value) == false){
            error = true;
            if(showError == true){
                $(unique+ele+key).addClass("error-field");
            } 
        }
    }); 
    return error;
   }





  /***  Layout logic   */



  /***  Bizz logic   */


}

