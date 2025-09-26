
import Base from '../../main/js/base.js?v=123';
import tpApprovelist from '../tp/approvelist.tp.js?v=123';


export default class Approvelist extends Base {
    constructor() {
        super();
        this.getopenList = {};
        this.run();
        this.approveOptions = [];
        this.ran;
    }
    async run()
    {
        this.Layout();
        this.SetEvents();
        // init overview
        await this.LoadApprovelist("all",window.LANGUAGE,0);
        $("#overviewContainer ").html( this.MakeOverview() );
        this.SetOverviewEvent();


    }


    Layout(){
        $(".cardshop-main-content").html( tpApprovelist.localisation() + "<span style='margin-left:10px;'></span>" +tpApprovelist.filterList() + "<span style='margin-left:10px;'></span>" + tpApprovelist.showTech()+"<br><div id='shipmentblocktoolContainer' style='padding: 8px; background: #FAFAFA; border-radius: 8px; text-align: center; margin-top: 8px; margin-bottom: 8px;'><a href='index.php?rt=unit/cardshop/shipblocktool/' target='_blank'>Nyt værktøj til leverance fejl kan findes her.</a> - Antal leverance fejl: <span id='shipblocktoolcount'>-</span></div><div id='overviewContainer'></div>" )
        $("#localisation-tp").val()

    }
    SetEvents(){
        let self = this;
        $("#localisation-tp").unbind("change").change(
           function()
           {
               self.updateOverview();
           }
       )
       $(".filterList-tp").unbind("change").change(
           function()
           {                 ;
               self.updateOverview();
           }
       )
       $("#techFilterListShow-tp").unbind("change").change(
           function()
           {
               self.updateOverview();
           }
       )
       $("#localisation-tp").val(window.LANGUAGE);


    }

    async updateOverview(){
        this.approveOptions = [];
        let localisation =    $("#localisation-tp option:selected").val();
        let filterList   =    $("input:radio[name ='filterList_tp']:checked").val();
        let techFilter   =    $('#techFilterListShow-tp' ).is(":checked") == true ? 1:0;
        await this.LoadApprovelist(filterList,localisation,techFilter);
        $("#overviewContainer ").html( this.MakeOverview() );
        this.SetOverviewEvent();

    }

    FixView(shopID,approveID){
        let html = '<div class="approveActionContainer"></div><iframe class="frame" id="KortShopApp" width="100%" height="500" src="/gavefabrikken_backend/index.php?rt=unit/cardshop/main&token=asdf43sdha4f34o&systemuser_id=86&ram=7732&shopid='+shopID+'" style="height: 1037px;"></iframe>'
        super.OpenRightPanel("Godkendelsesv&oelig;rkt&oslash;jet",html,"fuldpage")

        this.setApproveAction(approveID);
    }

    async LoadApprovelist(filter,localisation,tech){
        return new Promise(async resolve => {
            this.getopenList = await super.post("cardshop/approvelist/getopen/"+filter+"/"+tech+"/"+localisation );
            resolve(true);
        })

    }

    MakeOverview(){
       let self = this;
       let html = "<table id='approvelist-view' > <thead> <tr> <th>Type</th> <th>Dato</th> <th>Firma</th><th>BS-nummer</th><th>Block navn</th> <th>Beskrivelse</th> <th></th> </tr> </thead> <tbody> ";

       $('#shipblocktoolcount').html(this.getopenList.shipmentblock);

       jQuery.each(this.getopenList.blocklist, function() {

           var description = this.description;
           try {
               description = decodeURIComponent(this.description)
           } catch (e) {

           }

            self.approveOptions["a"+this.id] = this  // {actions:this.actions,};
            html+="<tr>";
            html+="<td>"+this.object_type+"</td>"
            html+="<td>"+this.created_date+"</td>"
            html+="<td>"+this.company_name+"</td>"
            html+="<td>"+this.order_no+"</td>"
            html+="<td>"+this.block_type_name+"</td>"
            html+="<td>"+ description +"</td>"
            html+="<td><button class = 'approvelist-fix' company-id='"+this.company_id+"' data-id='"+this.id+"'  >FIX</button></td>"
            html+="</tr>"

       });
      return html+="</tbody></table>";

    }
    SetOverviewEvent(){
       let self = this;
       $('#approvelist-view').DataTable({
            "paging": false,
            "order": [[1, "asc"]]
       });
       $(".approvelist-fix").unbind("click").click(
           function()
           {
                self.FixView($(this).attr("company-id"),$(this).attr("data-id") );
           }
       )
    }
/// Setup approve actions
    setApproveAction(id)
    {
        let self= this;
        let html = "<br><h3>Handlinger</h3><hr><br><div class='approveInfo'  >"
        html+="<b>Firma:</b>"+this.approveOptions["a"+id].company_name+"<br><br>"
        html+="<b>BS-nummer:</b>"+this.approveOptions["a"+id].order_no+"<br><br>"
        html+="<b>Fejlbeskrivelse:</b><br>"
        html+="<div>"+this.approveOptions["a"+id].description+"</div></div><hr><hr>";



//        html+= "<p>"+this.approveOptions["a"+id].description+"</p>"


        html+="<br>";

        console.log(this.approveOptions["a"+id].description )
        let data = this.approveOptions["a"+id].actions;
        html+=
                data.map(function(ele){
                    return `<div>
                    <div><b>L&oslash;sning: </b></div>
                    <div>${ele.description}</div>
                    <button data-code='${ele.code}' data-id='${id}' class='approveAction'>${ele.name}</button>
                    <br><hr></div>
             `
            }).join(' ');
            html+="<br><b style='color:red'>Debug</b><br>"+this.approveOptions["a"+id].debug_data;

            $(".approveActionContainer").html(html+"<br><br><br>");

           $(".approveAction").unbind("click").click(
            function(){
               self.doApproveAction($(this).attr("data-id"),$(this).attr("data-code"),this);
            }
           )


      //
    }
    async doApproveAction(id,code,objElement){
       let result = super.post("cardshop/approvelist/approve/"+id,{action:code})
       $(objElement).parent().parent().html("Handling er udf&oslash;rt")
       this.updateOverview();
    }










}



