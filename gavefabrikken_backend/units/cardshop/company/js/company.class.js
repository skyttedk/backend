
import Base from '../../main/js/base.js';
import ShowCompanyForm from '../../companyform/js/showCompanyForm.class.js?v=123';
import TabsControl from '../../main/js/tabsControl.js?v=123';


export default class Company extends Base {
    constructor() {
        super();

    }


  /***  Layout logic   */


  /***  Bizz logic   */
    async createCompany(data){
        return new Promise(resolve => {
         $.post( BASEURL+"cardshop/companyform/create",{companydata:data},
            function(response){
                resolve(response);
            }
        )})
    }
    async ShowCompany(companyID){


        let tabs = new TabsControl(companyID);
        tabs.ShowTabs();
        new ShowCompanyForm().Init(companyID);
    }

}
