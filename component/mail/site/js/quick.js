$( document ).ready(function() {
    $('#smsTxt').on('keyup', function(event) {
        var len = $(this).val().length;
        if (len >= 160) {
            alert("Du har mere end 160 tegn")
        }
        var txt = len+" ud af 160 tegn er benyttet";
        $("#chrNumber").html(txt);

    });
});


var sms ={
   send:function(){
       var jqxhr = $.post( "page/callHandler.php",{
           "action":"smsController",
           "function":"send",
           "txt":$('#smsTxt').val(),
           "nr":$("#tlf").val(),
           "smsTitle":$("#smsTitle").val()}, function(result) {
           alert( result );
       })
           .fail(function() {
               alert( "error" );
           })

   }
}