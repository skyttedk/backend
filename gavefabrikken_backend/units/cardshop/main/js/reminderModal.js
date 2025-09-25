

import Base from '../../main/js/base.js';
import ShowCompanyForm from '../../companyform/js/showCompanyForm.class.js?v=123';
import TabsControl from './tabsControl.js?v=123';

export default class ReminderModal extends Base {
    constructor(companyID) {
        super();
        this.companyID = companyID;
        this.openModal();

    }

    async openModal() {

        $(".modal-body").html("System is working");
        $("#ModalFullscreenLabel").html('Note påmindelser');
        $(".modal-body").html('<div style="padding: 50px; text-align: center;">Henter dine påmindelser..</div>');
        $(".modal-footer").html('');
        $('#ModalFullscreen').modal('show');
        this.updateView();
    }

    async updateView()
    {

        let modalContent = await super.post("cardshop/usernotes/remindermodal",{reminderviewtype: $('#reminderviewtype').val(), reminderviewuser: $('#reminderviewuser').val()});
        if(modalContent === false || modalContent === null || modalContent.status != 1) {
            $(".modal-body").html('<div style="padding: 50px; text-align: center;">Der opstod en fejl, kunne ikke hente påmindelser.</div>');
            $(".modal-footer").html('');
            return;
        }
        $(".modal-body").html(modalContent.body);
        $(".modal-footer").html(modalContent.footer);
        this.updateReminderCount(modalContent);

        $(".modal-footer .closebtn").bind('click', (e) => {
            $('#ModalFullscreen').modal('hide');
        });

        $(".modal-footer .refreshbtn").bind('click', (e) => {
            this.updateView();
        });

        $(".modal-body .reminder-go-to-company").bind('click', (e) => {
            this.gotoCompany($(e.currentTarget).data('id'));
        });

    }

    gotoCompany(companyID)
    {
        let tabs = new TabsControl(companyID);
        tabs.ShowTabs();
        new ShowCompanyForm().Init(companyID);
        $("#companylist-search").val("");
        $("#companylist").html("");
        $('#ModalFullscreen').modal('hide');
    }

    updateReminderCount(data) {
        if(typeof data !== 'object' || data.remindercount === undefined || isNaN(data.remindercount)) {
            return;
        }
        if(data.remindercount > 0){
            $("#reminder-active-count").html("Påmindelser: "+data.remindercount);
        } else {
            $("#reminder-active-count").html("");
        }
    }

}