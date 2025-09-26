var _SystemMsg;
export default class SystemMsg {
    constructor() {
        _SystemMsg = this;
    }
    SystemAjaxErrorMsg()
    {
        alert("Message from system");
    }
    Toast(msg,title="Ny besked",error=false){
        error == false ? $(".toast-header").html("<b>"+title+"</b>") : $(".toast-header").html("<b style='color:red;'>"+title+"</b>");
        $(".toast-body").html(msg);
        $(".toast").toast({ delay: 4000 });
        $('.toast').toast('show');
    }

}

