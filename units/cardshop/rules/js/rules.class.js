import Base from '../../main/js/base.js?v=123';


var _apiPath = "https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt="

export default class Rules extends Base {
    constructor(companyID) {
        super();
        this.conpanyID = companyID
        this.LoadCardList();

        this.token = super.token();

         $('#card-view').DataTable().remove()
    }
    async LoadCardList()
    {
      let self = this
      var res = await apiCall("shopPresentRules/getPresentList",{"company_id":this.conpanyID,"token":"dsf984gh58b2i23t4g8"});
      var html = "<table id='card-view' class='display "+this.token +"'><thead><tr><th>Id</th><th>Kort type</th><th>Img</th><th>Gave</th><th>Model</th><th>Altid &aring;ben</th><th>Altid lukket</th></tr> </thead> <tbody>";

      for(var i=0;res.data.length>i;i++){

        html+="<tr id='line_"+res.data[i].attributes.present_id+"_"+res.data[i].attributes.model_id+"'><td>"+res.data[i].attributes.model_present_no+"</td><td>"+res.data[i].attributes.alias+"</td><td><img width=100 src='"+res.data[i].attributes.media_path+"' /></td><td>"+res.data[i].attributes.model_name+"</td><td>"+res.data[i].attributes.model_no+"</td>"
        html+="<td><input class='ShowHidePresent'       data-id='"+res.data[i].attributes.present_id+","+res.data[i].attributes.model_id+"' id='show_hide_"+res.data[i].attributes.present_id+"_"+res.data[i].attributes.model_id+"' type='checkbox' > </td>"
        html+="<td><input class='ShowHideAlwaysUpdata' data-id='"+res.data[i].attributes.present_id+","+res.data[i].attributes.model_id+"'  id='show_hide_always_"+res.data[i].attributes.present_id+"_"+res.data[i].attributes.model_id+"' type='checkbox' > </td></tr>"

      }
      $("#cardshop-tabs-5").html(html+"</tbody></table>");
      this.SetSelected();
      setTimeout(function() {self.SetEvents() }, 400)
    }
    SetEvents(){
        let self = this
        $(".ShowHidePresent").unbind("click").click( function(){
                let param = $(this).attr("data-id").split(",");
                self.ShowHideUpdata( param[0],param[1] );
            }
        )
        $(".ShowHideAlwaysUpdata").unbind("click").click( function() {
                let param = $(this).attr("data-id").split(",");
                self.ShowHideAlwaysUpdata(param[0],param[1] );
            }
        )


     $('.'+this.token ).DataTable({
            "paging": false,
       });
    }

    async SetSelected()
    {
       var res = await apiCall("shopPresentRules/getRules",{company_id:this.conpanyID,token:"dsf984gh58b2i23t4g8"});
       for(var i=0;res.data.length>i;i++){
         if(res.data[i].attributes.rules == 1){
            $("#show_hide_"+res.data[i].attributes.present_id+"_"+res.data[i].attributes.model_id).prop('checked', true);
         }
         if(res.data[i].attributes.rules == 2){
            $("#show_hide_always_"+res.data[i].attributes.present_id+"_"+res.data[i].attributes.model_id).prop('checked', true);
         }
      }

    }

  async ShowHideUpdata(present_id,model_id){

   if( $('#show_hide_' + present_id+"_"+model_id).is(":checked") ){
        $("#show_hide_always_"+present_id+"_"+model_id).prop('checked', false);
        var formData = {present_id:present_id,model_id:model_id,company_id:this.conpanyID,token:"dsf984gh58b2i23t4g8",action:"1"};
        alert("Present added")
        var res = await apiCall("shopPresentRules/updateRulesV2",formData);
   } else {
       var formData = {present_id:present_id,model_id:model_id,company_id:this.conpanyID,token:"dsf984gh58b2i23t4g8",action:"0"};
        alert("Present removed")
       var res = await apiCall("shopPresentRules/updateRulesV2",formData);
   }
  }

  async ShowHideAlwaysUpdata(present_id,model_id){

     $("#show_hide_"+present_id+"_"+model_id).prop('checked', false);
   if( $('#show_hide_always_' + present_id+"_"+model_id).is(":checked") ){
        var formData = {present_id:present_id,model_id:model_id,company_id:this.conpanyID,token:"dsf984gh58b2i23t4g8",action:"2"};
        alert("Present added")
        var res = await apiCall("shopPresentRules/updateRulesV2",formData);
   } else {
       var formData = {present_id:present_id,model_id:model_id,company_id:this.conpanyID,token:"dsf984gh58b2i23t4g8",action:"0"};
        alert("Present removed")
       var res = await apiCall("shopPresentRules/updateRulesV2",formData);
   }
  }

}


function apiCall(api,data){
 return new Promise(function(resolve, reject) {
    $.post( _apiPath+api,data, function( res ) {
        resolve($.parseJSON( res ));
    });
});
}