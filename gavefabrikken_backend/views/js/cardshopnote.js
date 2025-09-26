
var shopNote = {


  getAddAllnote:function(){
      ajax({"company_id":_selectedCompany},"company/getNotes","shopNote.getAddAllnoteResponse","");
  },
  getAddAllnoteResponse:function(response){
       if(response.data.result[0].rapport_note == null) response.data.result[0].rapport_note = "";
       if(response.data.result[0].internal_note == null) response.data.result[0].internal_note = "";

       $("#rapport_note").val(decodeURIComponent(response.data.result[0].rapport_note));
       $("#internal_note").val(decodeURIComponent(response.data.result[0].internal_note));
       //shopNote.getSpDeals();
  },
  getSpDeals:function(){
      ajax({"company_id":_selectedCompany},"company/getSpDealsOrders","shopNote.getSpDealsResponse","");

  },
  getSpDealsResponse:function(response){
        var obj =  response.data.result;
        for(var i=0;i < obj.length;i++){
             if(obj[i].spdealtxt.trim() != "" && obj[i].spdealtxt != null){
                $("#spDeal").append(obj[i].spdealtxt+"<hr />")
            }
        }
  },
  saveRapportNote:function(){
        var sendData =  $("#rapport_note").val();
      ajax({"company_id":_selectedCompany,"rapport_note":sendData},"company/saveRapportNote","shopNote.saveRapportNoteResponse","");
  },
  saveRapportNoteResponse:function(response){
    alert("Tillæg aftale gemt");
  },
  saveInternalNote:function(){
    var sendData = $("#internal_note").val();
    ajax({"company_id":_selectedCompany,"internal_note":sendData},"company/saveInternalNote","shopNote.saveInternalNoteResponse","");
  },
  saveInternalNoteResponse:function(){
    alert("Note gemt");
  }
}
