 var elem;
import Base from '../../main/js/base.js';
import tpValgshop from '../tp/valgshop.tp.js';
import ValgshopOptions from '../../cards/js/cardoptions.class.js';

export default class Valgshop extends Base {
    constructor() {
        super();
        this.timer;
        this.activeElementID
        this.working = false;

    }
    start(){
      super.OpenRightPanel("VALGSHOP ERSTATNINGSKORT",tpValgshop.stucture(),"fuldpage")
      this.setInitEvents();
     
    }
    setInitEvents(){
        let self = this;
        $("#vs-search").unbind("keyup").keyup(
            () => {
                clearTimeout(self.timer );
                self.timer = setTimeout(function(){
                  self.search()
                }, 500);
            }
        )
    }
    setSearchListEvents(){
        let self = this;
        $(".vs-search-result-element").unbind("click").click(
            function(){
                self.activeElementID = $(this).attr("id");
                $(".vs-search-result-element ").removeClass("companylist-selected");
                $(this).addClass("companylist-selected");
                self.showCompanyEmployees( $(this).attr("id") );

            }
        )
    }

    setEmployeesTableEvent(){
        let self = this;
       $('#company-view').DataTable({
                "scrollY":        "calc(100vh - 200px)",
                "scrollCollapse": true,
                "paging":         false
       });

        $(".valgshopOption").unbind("click").click(
            function(){
                self.openOption( $(this).attr("data-id"),true );
            }
        )
        $(".valgshopShowReplacement").unbind("click").click(
            function(){
                self.openOption( $(this).attr("data-id"),"valgshopReplacement" );
            }
        )


    }
    callback(){
      this.showCompanyEmployees( this.activeElementID);
    }

    openOption(shopuser,option){

     new ValgshopOptions("",shopuser,option,this);
    }

    async search(){
        if(this.working == true){
            alert("Systemet arbejder")
            return;
        }
        let searchTxt = $("#vs-search").val();
        if(searchTxt.length < 2 ){
            return;
        }

        //this.working = true;
        let postData = {
          searchTxt:searchTxt
        }
        let list = await super.post("cardshop/valgshop/searchCompany",postData);

        this.working == false;
        if(list.data.length > 0){
          $("#vs-search-list").html(tpValgshop.searchList(list));
          this.setSearchListEvents();

        } else {
          $("#vs-search-list").html("<div>Intet resultat macther din s&oslash;gning</div>");
        }
    }
    async showCompanyEmployees(companyID)
    {

      $(".vs-main").html("<h3>Systemet arbejder</h3>");
      let companyList = await super.post("cardshop/valgshop/getCompanyEmployees/"+companyID);
      if(companyList.data.length > 0){
         $(".vs-main").html(tpValgshop.employeesDataList(companyList));
         this.setEmployeesTableEvent();
      } else {
        $(".vs-main").html("<h3>Ingen data at vise</h3>");
      }


    }





}