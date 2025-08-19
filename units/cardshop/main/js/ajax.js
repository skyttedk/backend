let _Ajax = null;
export default class Ajax {
    constructor() {
        if(!_Ajax){
            _Ajax = this;
        }
        return _Ajax;


    }
    async post(url,postValue,validate=true){
        let ME = this;
        let notLoggedIn = false;

// us special access
         if(window.USERID == 861){
            notLoggedIn = false
         }

        if(notLoggedIn == true){
            window.location.href = "https://system.gavefabrikken.dk/gavefabrikken_backend/units/logout.php";
            return;
        }
        return new Promise(resolve => {
            $.post( BASEURL+url,postValue,
                (response) => {
                    if(validate ? _Ajax.validateResponse(response) : resolve(response)){
                        resolve(response);
                    } else {
                        if(validate == true){
                            alert("Error, something went wrong \n '-- "+response.message+" --'");
                        }
                        resolve(false);
                    }
                }
            , "json");  
        });
    }
    validateResponse(response){
        return response.status == 1 ? true : false;
    }
}

