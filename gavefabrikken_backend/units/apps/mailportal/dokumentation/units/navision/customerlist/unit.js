
class navCustomerList
{

    constructor(serviceUrl) {
        this.serviceUrl = serviceUrl;
    }

    /**
     * SEARCH DATA
     */


    searchhtml(cvr,ean,responseSelect) {

        if($.trim(ean) != "") {
            this.searchEANHTML(ean,responseSelect);
        } else {
            this.searchCVRHTML(cvr,responseSelect);
        }
    }

    searchCVRHTML(cvr,responseSelect) {

        this.htmlElement = responseSelect;
        $(this.htmlElement).show().html('<div>Henter kunder..</div>');

        var s = this;
        $.get(this.serviceUrl+"searchcvr/"+cvr,function(response) {
            console.log(response);
            s.showCustomerHTML(response.customers);
        },'json');

    }

    searchEANHTML(ean,responseSelect) {

        this.htmlElement = responseSelect;
        $(this.htmlElement).show().html('<div>Henter kunder..</div>');

        var s = this;
        $.get(this.serviceUrl+"searchean/"+ean,function(response) {
            console.log(response);
            s.showCustomerHTML(response.customers);
        },'json');
    }

    showCustomerHTML(customerList,responseSelect) {

        var html = "<table class='navcustomerlist' style='width: 100%;'><thead><tr><th>&nbsp;</th><th>Nr.</th><th>Navn</th><th>Adresse</th><th>CVR</th><th>EAN</th></tr></thead><tbody>";

        for(var i = 0; i < customerList.length; i++) {
            var customer = customerList[i];
            html += '<tr><td><input type="radio" name="navcustomerno" value="'+customer.No+'"></td><td>'+customer.No+'</td><td>'+customer.Name+'</td><td>'+customer.Address+', '+customer.Post_Code+' '+customer.City+', '+customer.Country_Region_Code+'</td><td>'+customer.VAT_Registration_No+'</td><td>'+customer.EAN_No+'</td></tr>'
        }

        html += "</tbody></table>";

        $(this.htmlElement).show().html(html);
    }

    /**
     * SEARCH DATA
     */

    search(cvr,ean) {
        if($.trim(ean) != "") {
            this.searchEAN(ean);
        } else {
            this.searchCVR(cvr);
        }
    }

    searchCVR(cvr) {
        console.log('SEARCH CVR: '+cvr);

        $.get(this.serviceUrl+"searchcvr/"+cvr,function(response) {
           console.log(response);
        },'json');

    }

    searchEAN(ean) {
        console.log('SEARCH EAN: '+ean);
        $.get(this.serviceUrl+"searchean/"+cvr,function(response) {
            console.log(response);
        },'json');
    }

}

// Fire loader
if(typeof window.navCustomerListReady == "function") {
    navCustomerListReady();
}