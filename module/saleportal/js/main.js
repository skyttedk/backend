$( window ).resize(function() {
    console.log($( window  ).height())
    $("#iframe").height($( window  ).height()-8)
});
$(document).ready(function () {
     $("#iframe").height($( window  ).height()-8)
})


var msgTimer;
function message(msg){
   clearTimeout(msgTimer);
    $("#message").html(msg);

    $("#message").fadeIn(500, function(){
        setTimeout(function(){
            $("#message").fadeOut(1000)
        }, 3000)

    })


}

