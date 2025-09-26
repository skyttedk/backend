<script>
var smsQueue = {
    run:function(){

         var jqxhr = $.post( "page/callHandler.php",{
                    "action":"queueController",
                    "function":"handleQueue"}, function(result) {
                        $("#stats").html( result );
                        antal = $("#antal").html();
                        antal = (antal*1) + 200;
                        $("#antal").html(antal);
                        smsQueue.run();
                })
                .fail(function() {
                    alert( "error" );
                })
        }


}


</script>

<div>
<button onclick="smsQueue.run()">Run1</button>
<div id="antal">0</div>
<div id="stats" style="width:400px; height: 400px; overflow: auto; border:1px solid black;"></div>
</div>