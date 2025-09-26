
window.BASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/";
var JSBASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/unit/pim/kontainerutilities/";
var KU_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/kontainerutilities/";

import Base from '../../main/js/base.js';

export default class KontainerUtil extends Base {

    constructor() {
        super();
        this.init();
        this.setEvents();

    }
    async init(){
        $("#ku-copy").html("Kopiere varen med følgende varenr.: "+ITEMNO)
        $("#title-current-itemno").html("Itemno: "+ITEMNO)
        await this.getNav();
        await this.getNavSaleprice();
    }
    setEvents(){
        let self = this;
        $("#ku-copy").unbind("click").click(
            function(){
                $(".copy-container").html("System Arbejder");
                self.copy();
            }
        )
        $("#ku-sync").unbind("click").click(
            function(){
                $(".sync-container").html("System Arbejder");
                self.sync();
            }
        )

        $("#tab2-tab").unbind("click").click(
            function(){
                self.removeSyncLog();
                let postData = {
                    PIMID:PIMID
                }
                $.post( KU_AJAX_URL+"gavevalgItemActiveState",postData ,function(res ) {
                    let resData = JSON.parse(res);
                    
                    if(resData.length == 0){

                    } else {

                    }
                })
            }
        )
        $("#tab3-tab").unbind("click").click(
            function(){
                $("#tab3").html("<iframe src='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/asdkfjlfdh8uryreyt78erfifgy3i678' />");

            }
        )

        $(".closeBrowser").unbind("click").click(
            function(){
                window.close();
            }
        )
        $("#magento-sync").unbind("click").click(
            function(){
                let online = 0;
                let price = 0;
                if ($('#magento-sync-online').is(':checked')) {
                    online = 1;
                }
                if ($('#magento-sync-price').is(':checked')) {
                    price = 1;
                }

                let postData = {
                    PIMID:PIMID,
                    ONLINE:online,
                    PRICE:price
                }
                $.post( window.BASEURL+"pim/magento/test",postData ,function(res ) {
                    let resData = JSON.parse(res);
                        $(".sync-magento").html("sync")
                    if(resData.length == 0){

                    } else {

                    }
                })

            }
        )



    }
    removeSyncLog(){
        $("#tab3").html("");
    }

    gavevalgItemActiveState(){

    }

    preview(){
       // $("#tab2").html("<iframe src='https://presentation.gavefabrikken.dk/presentation2024/?user=PmTCeKTiRSauXymSB83RNutsUw5h2i&singleShow=89950#' ></iframe>");
      //  let url = "<br><button type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='window.close()'>Tryk for at lukke browser-fan</button><iframe src='https://presentation.gavefabrikken.dk/presentation2024/?user=PmTCeKTiRSauXymSB83RNutsUw5h2i&singleShow=go&pimid="+PIMID+"' ></iframe>";
      //  $("#tab2").html(url);
        /*
        let postData = {
            PIMID:PIMID,
            ITEMNO:ITEMNO
        }
        $.post( KU_AJAX_URL+"preview",postData ,function(res ) {
            let resData = JSON.parse(res);
            let closeHtml = "window.close();"
            if(resData.length == 0){
                $("#tab2").html("<br><button type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='window.close()'>Tryk for at lukke browser-fan</button><div>Preview kan ikke vises, varen ikke ligger i gaveAdmin! </div>");
            } else {
                let id = resData.id;
                let url = "<br><button type=\" button\"  class=\" btn btn-warning mr-2 \" onclick='window.close()'>Tryk for at lukke browser-fan</button><iframe src='https://presentation.gavefabrikken.dk/presentation2024/?user=PmTCeKTiRSauXymSB83RNutsUw5h2i&singleShow="+id+"&pimid="+PIMID+"' ></iframe>";

            }
        })
        */

    }

    async getNav(){
        let postData = {
            ITEMNO:ITEMNO
        }
        return new Promise(resolve => {
            let html = "<h3>Standard priser</h3><table  class='nav-price-list'  border='1' ><tr><th>Land</th><th>Budget pris</th><th>Vejl. pris</th></tr>";
            $.post( KU_AJAX_URL+"getNAV",postData ,function(res ) {
                let resData = JSON.parse(res)
                resData.forEach(function(element) {
                    let lang = "Danmark";
                    if(element.attributes.language_id == 4) {
                        lang= "Norge";
                    }
                    if(element.attributes.language_id == 5) {
                        lang= "Sverige";
                    }
                    html+= `
                    <tr><td>${lang}</td><td>${element.attributes.unit_price}</td><td>${element.attributes.vejl_pris}</td></tr>
                    `
                })
                html+= "</table>";
                $(".magenta-price").html(html);
                resolve();

            })
        })
    }

    async getNavSaleprice(){
        let postData = {
            ITEMNO:ITEMNO
        }
        return new Promise(resolve => {
            let html = "<br><h3>Debitor priser</h3><table class='nav-price-list' border='1'><tr><th>Land</th><th>Debitor kode</th><th>Pris</th></tr>";
            $.post( KU_AJAX_URL+"getNavSaleprice",postData ,function(res ) {
                let resData = JSON.parse(res)
                resData.forEach(function(element) {
                    let lang = "Danmark";
                    if(element.attributes.language_id == 4) {
                        lang= "Norge";
                    }
                    if(element.attributes.language_id == 5) {
                        lang= "Sverige";
                    }
                    html+= `
                    <tr><td>${lang}</td><td>${element.attributes.sales_code}</td><td>${element.attributes.unit_price}</td></tr>
                    `
                })
                html+= "</table>";
                $(".magenta-price").append(html);
                resolve();

            })
        })
    }



    sync(){
        let postData = {
            PIMID:PIMID,
            ITEMNO:ITEMNO
        }
        $.post( KU_AJAX_URL+"syncManuelItem",postData ,function(res ) {
            let resData = JSON.parse(res)
            $(".sync-container").html(" <br>Varen er nu synkronisere med følgende status: "+JSON.stringify(resData.msg) +" <br><button type=\" button\"  id=\"close-ku-sync\" class=\" btn btn-warning mr-2\">Tryk for at lukke browser-fan</button>");
            $("#close-ku-sync").unbind("click").click(
                function(){
                    window.close();
                }
            )
        })
    }
    copy(){
        let postData = {
            PIMID:PIMID,
            ITEMNO:ITEMNO
        }
        $.post( KU_AJAX_URL+"copyitem",postData ,function(res ) {
            let resData = JSON.parse(res)
            if (typeof resData.errors !== "undefined") {
                $(".copy-container").html("Der er opstået en fejl");
            } else {
                $(".copy-container").html(" <br> <button type=\" button\"  id=\"close-ku-copy\" class=\" btn btn-warning mr-2\">Varen er nu kopieret- tryk for at lukke browser-fan</button>");
                $("#close-ku-copy").unbind("click").click(
                    function(){
                        window.close();
                    }
                )
            }

        });
    }



}


$( document ).ready(function() {
    var KU = new KontainerUtil();

});