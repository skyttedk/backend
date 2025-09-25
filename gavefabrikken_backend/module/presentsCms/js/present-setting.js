/*
{"pris":"","vis_pris":"true","budget":"","vis_budget":"true","special":"","vis_special":"false"}
*/
AppPcms.presentSetting = (function () {
    self = this;
    self.selectedPresent = "";
    self.init = () => {
      $("#presentUpdateSetting").click(function(){
        console.log("save")
        AppPcmsPresentSetting.save();
      })
    }

    self.show = async (presentId) => {
               console.log(presentId);
         this.selectedPresent = presentId;
         $("#use_custon_price").prop('checked', false);
         $("#pt_pris_no").val("");
         $("#pt_special_no").val("");
         $("#pt_budget_no").val("");
         this.insetDataInForm(presentId);
         $("#settingPresent").modal('show');
        // let data = await this.load();
         //await this.insetDataInForm(data);
    }
    self.loadData = (id) => {
         _presentSetting = new Map();
        return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"presentSetting/load",{id:id}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else {
                        res.data.forEach(ele => {
                                if(ele.setting.length > 10){
                                  _presentSetting.set(String(ele.present_id), JSON.parse(ele.setting));
                                }
                        })


                    resolve(res) }
                }, "json");
        })
    }
    self.insetDataInForm = (presentId) =>{
       console.log(_presentSetting.get(presentId) )
       console.log("--"+presentId)
         if(_presentSetting.has(presentId)){
//            {"pris":"","vis_pris":"true","budget":"","vis_budget":"true","special":"","vis_special":"false"}
            let data = _presentSetting.get(presentId);

            $("#pt_pris_no").val(data.pris);
            $("#pt_special_no").val(data.special);
            $("#pt_budget_no").val(data.budget);
            if(data.show == 1){
                $("#use_custon_price").prop('checked', true);
            }
         }
    }
    self.remove = (id) => {
       _presentSetting.delete(id);
    }
    self.save = async () => {
        let show = $("#use_custon_price:checked").length > 0 ? "1":"none";
        let obj = {
            "pris":$("#pt_pris_no").val(),
            "budget":$("#pt_budget_no").val(),
            "special":$("#pt_special_no").val(),
            "show":show
        }
        _presentSetting.set(this.selectedPresent, obj);
        await this.doSave(this.selectedPresent,obj);

       $("#settingPresent").modal('hide');
          message("Indstillinger gemt");
    }
    self.doSave = (id,data) => {
              return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"presentSetting/update",{presentationId:_presentationId,id:id,config:data}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
    }
    self.getData = (id) => {
        if(_presentSetting.has(id)){
            return _presentSetting.get(id);
        } else {
          return "";
        }

    }

})

