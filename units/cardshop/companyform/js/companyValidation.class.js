export default class CompanyValidation {

    ValidateField(field,value)
    {
        let htmlElement = $("#"+field);
        let returnBool = true;
        if(htmlElement.hasAttr("validate")) {
            switch(htmlElement.attr("validate")) {
                case "cvr":
                     if( !this.Cvr(value) ) returnBool = false;
                break;
                case "zipcode":
                     if( !this.Zipcode(value) ) returnBool = false;
                break;
                case "email":
                     if( !this.Email(value) ) returnBool = false;
                break;
                case "onlyLetters":
                     if( !this.OnlyLetters(value) ) returnBool = false;
                break;
                case "tele":
                     if( !this.Tele(value) ) returnBool = false;
                break;                                                   
                default:
                  // code block
              }
        } 
            
            
            if(htmlElement.hasClass("mandatory")){
                
                if( htmlElement.prop('nodeName').toLowerCase() == "input" ) {
                   
                    if(htmlElement.attr("type").toLowerCase() == "number"){
                        if( !this.isNumber(value) ) returnBool = false;
                       
                    } else {
                        if( value.length == 0 ) returnBool = false;
                    }
                }
            }
     
        return returnBool;
    }
    Cvr(value){
      if(window.LANGUAGE == 1){
        return ((value.match(/^[0-9]+$/) != null) &&  ( value.length == 8) ) ?  true : false;
      }
      if(window.LANGUAGE == 4){
        return ((value.match(/^[0-9]+$/) != null) &&  ( value.length == 9) ) ?  true : false;
      }
      return true;
    }
    isNumber(value){
      if(window.LANGUAGE == 5){
        return true;
      }
        return (value.match(/^[0-9]+$/) != null ) ?  true : false;
    }
    OnlyLetters(value){
      if(window.LANGUAGE == 5){
        return true;
      }
        var regex = /\d/g;
        return !regex.test(value);
    }
    Zipcode(value){
      if(window.LANGUAGE == 5){
        return true;
      }
      return ((value.match(/^[0-9]+$/) != null) &&  ( value.length == 4) ) ?  true : false;
    }
    Email(value){
        const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(value).toLowerCase());
    }
    Tele(value){
      if(window.LANGUAGE == 5){
        return true;
      }
        return ((value.match(/^[0-9]+$/) != null) &&  ( value.length == 8) ) ?  true : false;
    }
    
    


}

