window.BASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/";
var JSBASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/";
window.LANGUAGE = "";
window.VERSION = "1.1.1";
window.USERID = USERID;
window.SHOPID = SHOPID;
import Access from '../../main/js/access.js';
import Companylist from '../../companylist/js/companylist.class.js?v=123'
import TabsControl from './tabsControl.js?v=123';
import CreateCompanyForm from '../../companyform/js/createCompanyForm.class.js?v=123';
import Approve from '../../approvelist/js/approvelist.class.js?v=123';
import Search from '../../search/js/search.class.js';
import Valgshop from '../../valgshop/js/valgshop.class.js';
import Demoshop from '../../demoshop/js/demoshop.class.js';
import ReminderModal from './reminderModal.js';

import Base from '../../main/js/base.js';
export default class Main extends Base  {
    constructor() {
         super();
        this.access = new Access();
        this.InitCardshop();
        this.timer;
        this.userTimer;
        this.logTimer;
        this.reminderTimer;

    }
    async InitCardshop(){
        let self = this;
        if(SHOPID > 0){
            $("#cardshop-show-approvelist-btn").hide();
            $("#cardshop-create-company-btn").hide();
        }
        let systemUser = await super.post("cardshop/main/getLanguage",{id:USERID});
        window.LANGUAGE  = systemUser.data.systemuser[0].language;
        new Companylist(window.LANGUAGE );

        //let accessList = [86,63,50,110,138,147,64,108,155,144];
        if(this.access.validate(window.USERID,11) == true) {
          //  if (accessList.indexOf(parseInt(window.USERID)) != -1) {
                $("#cardshop-show-approvelist-btn").show();
                this.updateApproveBtnCounter();
                clearInterval(this.timer);
                this.timer = setInterval(function () {
                    self.updateApproveBtnCounter();
                }, 60000);

          //  }
        }


        // hide btn i iframe
        if($(".cardshop-sidebar").html() == "" ){
            $("#cardshop-show-approvelist-btn").hide();
            $("#cardshop-create-company-btn").hide();
        }
        clearInterval(this.userTimer);
        this.userTimer = setInterval(function(){
          self.regUserActivity();
        }, 60000);

        this.reminderTimer = setInterval(function(){
            console.log('check reminder');
            self.checkUserReminders();
        }, 1000*60*2);

        if(parseInt(window.USERID) == 86 || parseInt(window.USERID) == 50  ){
            self.LoadUserActivity();
            $("#sysuserCounter").show();
            clearInterval(this.logTimer);
            this.logTimer = setInterval(function(){
            self.LoadUserActivity();
            }, 60000);

        }


        this.checkUserReminders();
        this.SetEvents();
    }

    SetEvents() {
        $("#cardshop-create-company-btn").unbind("click" ).click( ()=>{
            new CreateCompanyForm();
        })
        $("#cardshop-reminders-btn").unbind("click" ).click( ()=>{
            new ReminderModal();
        })
        $("#cardshop-show-approvelist-btn").unbind("click" ).click( ()=>{
            new Approve();
        })
        $("#cardshop-supersearch-btn").unbind("click" ).click( ()=>{
            new Search();
        })
        $("#cardshop-replace-valgshopcard-btn").unbind("click" ).click( ()=>{
            let valgshop = new Valgshop()
            valgshop.start();
        })
        $("#cardshop-demoshop-btn").unbind("click" ).click( ()=>{
            new Demoshop()
        })


    }

    async updateApproveBtnCounter(){
        let result = await super.post("cardshop/approvelist/opencount/"+window.LANGUAGE);
        $("#cardshop-show-approvelist-btn").html("Fejl: "+result.blockcount)
    }

    async regUserActivity(){
     //   await super.post("cardshop/main/regUserActivity",{user:window.USERID});
    }

    async checkUserReminders(){
        var result = await super.post("cardshop/usernotes/userremindercount",{user:window.USERID});
        if(typeof result === 'object' && result.status === 1 && typeof result.count === 'number'){
            if(result.count > 0){
                $("#reminder-active-count").html("Påmindelser: "+result.count);
            } else {
                $("#reminder-active-count").html("");
            }
        } else {
            console.log('error in checkUserReminders');
            console.log(result);
        }
    }

    async LoadUserActivity(){
    //    let result = await super.post("cardshop/main/getUserActivity",{},false);
      //  $("#sysuserCounter").html(result)
    }


}




















$( document ).ready(function() {
    var APP = new Main()
}); 