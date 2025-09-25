
var MonitoringItemnr = {
    load:function(searchOption){
          $(".itemnrSearch").hide();
          $(".itemnrSearchWorking").show();

          let self = this;
          $('#monitoringItem').dataTable().fnClearTable();
          $('#monitoringItem').dataTable().fnDestroy();
          $.ajax(
            {
            url: 'index.php?rt=monitoringItemnr/load',
            type: 'POST',
            dataType: 'json',
            data: {}
            }
          ).done(function(res) {

              self.loadPresentation(res.data[searchOption])
            }
          )
    },
    loadPresentation:function(data){
        let self = this;
        $(".itemnrSearch").show();
        $(".itemnrSearchWorking").hide();
        $('#monitoringItem').DataTable( {
            pagination: "bootstrap",
            filter:true,
            data: data,
            destroy: true,
            lengthMenu:[5,10,25],
            paging: false,
                "columns":[
                    {     "data"     :     "itemnr" },
                    {     "data"     :     "reserved"},
                    {     "data"     :     "order"},
                    {     "data"     :     "procent"},
                    {     "data"     :     "info"},


               ]
        } );
        $(".monitoringItemInfo").unbind("click").click( function(){
            self.loadInfo($(this).attr("data-id"));
        })


    },
    loadInfo(id){
        let self = this;
        $.ajax(
            {
            url: 'index.php?rt=monitoringItemnr/freeSearch',
            type: 'POST',
            dataType: 'json',
             data: {itemnr:id }
            }
          ).done(function(res) {
            $("#dialog-options").dialog({
                modal: true,
                width: "610px"
            });
             $("#dialog-options").html("Systemet arbejder")
            setTimeout(function(){
                self.showInfo(res)
            }, 500)

            }
          )
    },
    showInfo(data){
        let totalRes = 0;
        let totalOrder = 0;

        let html = "<table class='customTable' width=600><tr><th>Firma</th><th>Varenr.</th><th>Gave</th><th>Reserverede</th><th>Valgte</th><th>Kort-shop</th></tr>";



         html+=   data.data.map(function(obj){
          if(obj.quantity != null) totalRes+= parseInt(obj.quantity);
          if(obj.antal != null) totalOrder+= parseInt(obj.antal);
        return `
        <tr>
            <td>${obj.name}</td>
            <td>${obj.model_present_no}</td>
            <td>${obj.model_name} - ${obj.model_no}</td>
            <td>${obj.quantity}</td>
            <td>${obj.antal}</td>
            <td>${obj.shop_is_gift_certificate}</td>
        </tr>
        `}).join('') + `</table>`;
        $("#dialog-options").html("<div><b>Antal Reserverede: "+totalRes+" --- Antal valgte: "+totalOrder+"</b></div><br>"+html);

    },
    itemnrSearch(){

            $.ajax(
            {
            url: 'index.php?rt=monitoringItemnr/freeSearch',
            type: 'POST',
            dataType: 'json',
            data: {itemnr:$("#itemnrSearchField").val() }
            }
          ).done(function(res) {
            $("#dialog-options").dialog({
                modal: true,
                width: "610px"
            });
             $("#dialog-options").html("Systemet arbejder")
            setTimeout(function(){
                MonitoringItemnr.showInfo(res)
            }, 500)


            }
          )


    },
    itemnrInfo(element){
            let itemnr =  $(element).attr("data-id");
             $(".itemnrInfoBtn").removeClass("itemnrInfoBtnSelected");
            $(element).addClass("itemnrInfoBtnSelected");

            $.ajax(
            {
            url: 'index.php?rt=monitoringItemnr/freeSearch',
            type: 'POST',
            dataType: 'json',
            data: {itemnr:itemnr }
            }
          ).done(function(res) {
            $("#dialog-options").dialog({
                modal: true,
                width: "610px"
            });
             $("#dialog-options").html("Systemet arbejder")
            setTimeout(function(){
                MonitoringItemnr.showInfo(res)
            }, 500)


            }
          )


    },
    rapport(){
            $("#testRapport").html("<tbody><tr><th>Kilde</th><th>Varenr</th><th>Navn</th><th>Antal Reserverede</th><th>Antal valgte</th><th></th></tr> ");
            $.ajax(
            {
            url: 'index.php?rt=monitoringItemnr/getAllreserved',
            type: 'POST',
            dataType: 'json',
            data: {}
            }
          ).done(function(res) {
            MonitoringItemnr.rapportController(res)
          })
    },
    async rapportController(res)
    {
        let total = res.data.length;
        let iterator = 0;
        for (let i in res.data){
             let r = await MonitoringItemnr.doMakeRepport(res.data[i].model_present_no);
             let totalRes = 0;
             let totalOrder = 0;
             let name;
             let onlyReserve = "";
             if(r.data.length > 0){
                 r.data.map(function(obj){
                    if(obj.warning_level == null) {
                         onlyReserve = "(-)";
                    }
                    if(obj.quantity != null) totalRes+= parseInt(obj.quantity);
                    if(obj.antal != null) totalOrder+= parseInt(obj.antal);

                    name = obj.model_name+" - "+obj.model_no;
                 })
                 if( totalRes <= totalOrder && totalRes !=0){
                   let html ="<tr><td>"+onlyReserve+"</td><td>"+res.data[i].model_present_no+"</td><td>"+name+"</td><td>"+totalRes+"</td><td>"+totalOrder+"</td><td><button class='itemnrInfoBtn' data-id='"+res.data[i].model_present_no+"' onclick='MonitoringItemnr.itemnrInfo(this)'>Info</button></td></tr>"
                   $("#testRapport").append(html);
                 }
             }
             iterator++
             $("#rapportStatus").html(total+" : "+iterator)
         }
    },
    async doMakeRepport(item)
    {
        return new Promise(resolve => {
          $.ajax(
            {
            url: 'index.php?rt=monitoringItemnr/freeSearch',
            type: 'POST',
            dataType: 'json',
            data: {itemnr:item }
            }
          ).done(function(res) {
                setTimeout(function(){
                     resolve(res)
                }, 200)

            }
          )
       })
    }



}