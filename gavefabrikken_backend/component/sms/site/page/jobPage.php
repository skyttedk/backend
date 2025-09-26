<script>

var smsjob = {
   showNewJobModal:function(){
        $('#job_smsTxt').val("");
        $("#job_tlf").val("");
        $("#job_smsTitle").val("");

        $( "#modalNewSmsJob" ).dialog({
          resizable: false,
          height: "auto",
          width: 400,
          modal: true,
          buttons: {
            "Opret job": function() {
                if( $( "#job_grp option:selected" ).val() == "" ){
                    alert("Du mangler at vælge gruppe")
                } else {
                    smsjob.createJob();
                }
            },
            Cancel: function() {
              $( this ).dialog( "close" );
            }
          }
        });
        $('#job_smsTxt').on('keyup', function(event) {
            var len = $(this).val().length;
            if (len >= 160) {
                //alert("Du har mere end 160 tegn")
            }
            var txt = len+" ud af 160 tegn er benyttet";
            $("#job_chrNumber").html(txt);
        });
   },
   createJob:function(){
                var jqxhr = $.post( "page/callHandler.php",{
                    "action":"jobController",
                    "function":"create",
                    "jobNavn":$("#job_navn").val(),
                    "grp":$( "#job_grp option:selected" ).val(),
                    "body":$('#job_smsTxt').val(),
                    "title":$("#job_smsTitle").val()}, function(result) {
                        alert( result );
                     //   $("#modalNewSmsJob").dialog( "close" );
                })
                .fail(function() {
                    alert( "error" );
                })


   },
   sendTest:function(){
            var jqxhr = $.post( "page/callHandler.php",{
                "action":"smsController",
                "function":"send",
                "txt":$('#job_smsTxt').val(),
                "nr":$("#job_tlf").val(),
                "smsTitle":$("#job_smsTitle").val()}, function(result) {
                   alert( result );
                })
                .fail(function() {
                    alert( "error" );
                })
   }
}

</script>
<div>
<button onclick="smsjob.showNewJobModal()">Opret ny job</button>
</div>




