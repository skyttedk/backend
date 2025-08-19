
var _apiPath = "https://gavefabrikken.dk//gavefabrikken_backend/index.php?rt="
var shopRules = {


  init:function(){
      this.loadCardList();

  },
  loadCardList: async function() {
      var res = await apiCall("shopPresentRules/getPresentList",{"company_id":_selectedCompany,"token":"dsf984gh58b2i23t4g8"});
      var html = "";

      for(var i=0;res.data.length>i;i++){
        html+="<tr id='line_"+res.data[i].attributes.present_id+"_"+res.data[i].attributes.model_id+"'><td>"+res.data[i].attributes.model_id+"</td><td>"+res.data[i].attributes.name+"</td><td><img width=100 src='"+res.data[i].attributes.media_path+"' /></td><td>"+res.data[i].attributes.model_name+"</td><td>"+res.data[i].attributes.model_no+"</td>"
        html+="<td><input onclick='shopRules.showHideUpdata("+res.data[i].attributes.present_id+","+res.data[i].attributes.model_id+")' id='show_hide_"+res.data[i].attributes.present_id+"_"+res.data[i].attributes.model_id+"' type='checkbox' > </td></tr>"
      }
      $("#spr-container").html(html);
      this.setSelected();
  },
  showHideUpdata: async function(present_id,model_id){
   if( $('#show_hide_' + present_id+"_"+model_id).is(":checked") ){
        var formData = {present_id:present_id,model_id:model_id,company_id:_selectedCompany,token:"dsf984gh58b2i23t4g8",action:"set"};
        alert("Present added")
        var res = await apiCall("shopPresentRules/updateRules",formData);
   }else {
       var formData = {present_id:present_id,model_id:model_id,company_id:_selectedCompany,token:"dsf984gh58b2i23t4g8",action:"remove"};
        alert("Present removed")
       var res = await apiCall("shopPresentRules/updateRules",formData);
   }
  },
  setSelected: async function(){
     var res = await apiCall("shopPresentRules/getRules",{company_id:_selectedCompany,token:"dsf984gh58b2i23t4g8"});
       for(var i=0;res.data.length>i;i++){
         $("#show_hide_"+res.data[i].attributes.present_id+"_"+res.data[i].attributes.model_id).prop('checked', true);
      }

  },


  buildCardList:function(res){

  }
}
shopRules.init();



function apiCall(api,data){
 return new Promise(function(resolve, reject) {
    $.post( _apiPath+api,data, function( res ) {
        resolve($.parseJSON( res ));
    });
});

}

    /*  else {
       var formData = {present_id:present_id,model_id:model_id,company_id:_selectedCompany,token:"dsf984gh58b2i23t4g8",action:"remove"};
        alert("Present removed")
       var res = await apiCall("shopPresentRules/updateRules",formData);
   }  */