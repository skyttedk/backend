
import tpTabs from '../tp/tabs.tp.js?v=123';
import Cards from '../../cards/js/cards.class.js?v=123';
import Freight from '../../freight/js/freight.class.js?v=123';
import Orderlist from '../../orderlist/js/orderlist.class.js?v=123';
import Earlyorder from '../../earlyorder/js/earlyorder.class.js?v=123';
import Rules from '../../rules/js/rules.class.js?v=123';


export default class TabsControl {
    constructor(companyID) {
        this.companyID = companyID;
        this.BuildTabs();
        this.SetEvents();

    }

    BuildTabs(){
        $(".cardshop-main-content").html( tpTabs.initTabs() );
        $( "#cardshop-tabs" ).tabs();
    }

    SetEvents(){
        let ME = this;
        $(".cardshop-tabs").click(
            function(){
                ME.TabsHandler( $(this).attr("tab-id") );
            }
        )
    }                            

    ShowTabs() {
        $("#cardshop-tabs-1").html("<b>System is working</b>");
        $("#cardshop-tabs").tabs("option", "active", 0);
        $("#cardshop-tabs").show();
    }
    TabsHandler(tabID){
        // reset tab specifik options
        $("#cardshop-tabs-action").html('');
        $("#cardshop-tabs-action-stamdata").hide();
        $("#cardshop-tabs-action").hide();

        console.log('click: '+tabID);

        switch(tabID) {
            case "masterdata":
                $("#cardshop-tabs-action-stamdata").show();
            break;
            case "cards":
                $("#cardshop-tabs-action").show();
                new Cards(this.companyID)
            break;
            case "orders":
                $("#cardshop-tabs-action").show();
                new Orderlist(this.companyID);
            break;
            case "earlyorders":
                new Earlyorder(this.companyID);
            break;
            case "notes":
                new Freight(this.companyID);
            break;
            case "rules":
                new Rules(this.companyID);
            break;

            case "companynotes":
                this.initNotesTab(this.companyID)
                break;

            case "actions":
                this.initActionsTab(this.companyID)
                break;


            default:
    // code block

        }

    }

    initNotesTab(companyId) {
        if($('#cardshop-tabs-7 iframe').length > 0) return;
        var iframeHtml = '<iframe src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/cardshop/usernotes/noteview/'+companyId+'" style="width: 100%; height: 90vh; border: none; outline: none;"></iframe>';
        $('#cardshop-tabs-7').html(iframeHtml)
    }

    initActionsTab(companyId) {
        if($('#cardshop-tabs-8 iframe').length > 0) return;
        var iframeHtml = '<iframe src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/tools/actionlog/company/'+companyId+'" style="width: 100%; height: 90vh; border: none; outline: none;"></iframe>';
        $('#cardshop-tabs-8').html(iframeHtml)
    }




}

