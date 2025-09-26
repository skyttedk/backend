AppPcms.pdf = (function () {
    self = this;

    self.init =  () => {

    };
    self.make = async (list,presentation_id) => {
          if(typeof list == "string"){
            var slide = await this.saveSlide(list,presentation_id,0);
          } else {
                let i = 0;
                list.forEach(async ele => {
                    await this.saveSlide(ele,presentation_id,i)
                    i++;
              })
          }
          await AppPcmsPresentation.saveConfig(presentation_id);
          var b = await this.build(presentation_id);
    }
    self.saveSlide = (id,presentation_id,sort) => {
         return new Promise(function(resolve, reject) {
            let setting =  AppPcmsPresentSetting.getData(id);
            setting = JSON.stringify(setting);
    
            $.post(_ajaxPath+"pdf/createSlide",{id:id,presentation_id:presentation_id,setting:setting,sort:sort}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }
    self.build = (presentation_id) => {
         return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"pdf/build",{presentation_id:presentation_id,lang:_lang}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }
})