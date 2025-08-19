var _Companylist;
import tpCompanylist from '../tp/companylist.tp.js?v=123';
import Company from '../../company/js/company.class.js?v=123';

import Base from '../../main/js/base.js';
export default class Companylist extends Base {
    constructor(LANGUAGE) {
        super();
        _Companylist = this;
        _Companylist.Layout(".cardshop-sidebar",{});
        _Companylist.SetEvents();
        _Companylist.timer;
        _Companylist._LANGUAGE = LANGUAGE;



    }

    Layout(targetClass,data){
        $(targetClass).html( tpCompanylist.searchform() );
        $(targetClass).append( tpCompanylist.companylist() );
    }
    SetEvents(){


        $(".cardshop #companylist-search").unbind("keyup").keyup(
            () => {
                clearTimeout(_Companylist.timer );
                _Companylist.timer = setTimeout(_Companylist.Search, 500);
            }
        )
        if(window.SHOPID > 0){

            $(".cardshop-sidebar").html("");

            let company = new Company();
            company.ShowCompany(window.SHOPID);

        }
        // ulrich skal fjernet
        if(window.USERID == 86){
            let company = new Company();
            company.ShowCompany("19502");
        }


    }

   /***  Layout logic   */
    ClearSearchList(){
        $("#companylist").html("");
    }
    SetSearchStatus(){
        $("#companylist").html("System is working");
    }
    buildSearchList(data){

        return new Promise(resolve => {
        _Companylist.ClearSearchList();
        // prepare data, find child
        let sortedData = [];
        jQuery.each(data, function() {
            let element = this.attributes
            let tempId = element.id;

            if(element.pid != 0){
                tempId = element.pid;
            }

            if(typeof sortedData["id_"+tempId] === 'undefined') {
                sortedData["id_"+tempId] = [];
            }
            sortedData["id_"+tempId].push(element);

        });
 
        for (var key in sortedData){
            var value = sortedData[key];
            if(value.length > 1){
                for (var keyChild in value){
                    if(value[keyChild].pid == 0){
                        $("#companylist").append( tpCompanylist.companylistElement(value[0]) );
                    } else {
                        $("#companylist").append( tpCompanylist.companylistElementChild(value[keyChild]) );
                    }
                }
            } else {
                $("#companylist").append( tpCompanylist.companylistElement(value[0]) );
            }
        }
        resolve(true);
        })
    }
    InsetInSearch(txt){
        $(".cardshop #companylist-search").val(txt)
    }

  /***  Bizz logic   */
    SearchAndSelect(id){
        _Companylist.Search(id);
    }

    async Search(doSelect=false){

/*  s�gning test
        let formData1 = {text:'test',LANGUAGE:window.LANGUAGE };
        await super.post("cardshop/companylist/search2",formData1);
*/

        _Companylist.ClearSearchList();
        _Companylist.SetSearchStatus(1);

        let formData = {text:$(".cardshop #companylist-search").val(),LANGUAGE:window.LANGUAGE };

        if(formData.text == "" ||  formData.text.length < 3) {
            $("#companylist").html("No results found");
            return;
        }

        let result = await super.post("cardshop/companylist/search",formData);
        if(result.result.length == 0) {
            $("#companylist").html("No results found");
            return;
        }
        await result == false ? super.SystemAjaxErrorMsg() : _Companylist.buildSearchList(result.result);
        // set event on found companyes
        $(".companylist-element").unbind("click").click(function(){
            let companyID = $(this).attr("data-id");
            $(".companylist-selected").removeClass("companylist-selected");
            $(this).addClass("companylist-selected");
            let company = new Company();
            company.ShowCompany(companyID);


        })
        if(doSelect != false){
            $("#companylist").find("[data-id='" + doSelect + "']").click();
        }

    }
}