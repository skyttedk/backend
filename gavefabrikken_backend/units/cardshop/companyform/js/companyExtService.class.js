
export default class Company {
    constructor() {
      
  
    }
  
    
    async lookupCvrNAV(cvr){
      $.post( BASEURL+"external/cvrsearch/cvr/dk/"+cvr, function(response){
            console.log(response);
      })  
    }
    async lookupCvrExt(cvr){
        $.post( BASEURL+"navision/customerlist/searchcvr/"+cvr, function(response){
              console.log(response);
        })  
    }
    async createCompany(data){
        $.post( BASEURL+"cardshop/companyform/create",{companydata:data},
            function(response){
                console.log(response);
            }
        )          
    }
  }
