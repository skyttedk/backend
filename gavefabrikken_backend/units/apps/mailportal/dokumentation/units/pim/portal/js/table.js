var dtable;

import Base from '../../main/js/base.js';
export default class table extends Base {
    constructor() {
        super();
        let Budget = [{"da":[100,200,400,560,640,800,960]}];
    }






    init(){
        let self = this;
        this.buildTable();
        $(".pim-budget-search").unbind("click").click(
            function(){
                dtable.draw();
            }
        )
        $(".pim-budget-reset").unbind("click").click(
            function(){
                $("#pim-budget-from").val(0);
                $("#pim-budget-to").val(0);
                dtable.draw();
            }
        )
        self.templateBuildBudget();

    }
    buildTable(){
        let self = this;
        $(".pim-main-container").html( this.template());


        dtable = $('#pimtable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            "scrollY": "800px",
            "scrollCollapse": true,
            ajax: {
                url: 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/portal/table',
                type: 'POST',
                data: function ( d ) {
                    d.costpriceStart = $("#pim-budget-from").val();
                    d.costpriceTo = $("#pim-budget-to").val();
                    d.budget = $("#pim-budget-budget").val();
                }
            },
            "drawCallback": function( settings ) {
                self.setEvent()
                $(".dataTables_scrollBody").scrollTop( 0 );
            },
                columns: [
                {   data: 'imgPath' },
                {   data: 'itemnr' },
                {   data: 'caption'},
                {   data: 'nav_name'},
                    {   data: 'vendor'},

                {   data: 'short_description'},
                {   data: 'action'}

            ],
        });
    }
    setEvent(){
        let self = this;



        $(".pim-table-add").unbind("click").click(
            function(){
                alert("Varen er tilføjet")
            }
        )

        $(".pim-table-img").unbind("click").click(
            function(){
                alert("hej hej")
            }
        )
        $(".pim-table-show").unbind("click").click(
            function(){
                self.showItem($(this).attr("data-id"));
            }
        )
    }
    async showItem(id){


        let present = await super.post("pim/portal/getPresent/"+id);
        let presentDescription = await super.post("pim/portal/getPresentDescription/"+id);
        let presentMedia = await super.post("pim/portal/getPresentMedia/"+id);
        let presentModel = await super.post("pim/portal/getPresentModel/"+id);

        //let html = ;
        //let html = this.templatePresentDescription(presentDescription);
        $(".modal-present-header").html(this.templatePresent(present));
        this.templatePresentDescription(presentDescription);
        $(".modal-present-img").html(this.templatePresentMedia(presentMedia));
        this.templatePresentModel(presentModel)

        $('#exampleModal').modal('toggle');
    }


    templatePresentDescription(data){
            data.data.res.map((i) => {
            let short_description = Base64.decode(i.short_description);
            let long_description = Base64.decode(i.long_description);

                let html = `<table width=600>
                    <tr><td width="150"><b>Overskrift</b></td><td>${i.caption}<br><br></td></tr>
                    <tr><td ><b>Kort beskrivelse</b></td><td>${short_description}<br></td></tr>
                    <tr><td ><b>Lang bekrivelse</b></td><td>${long_description}<br></td></tr>
                </table>`;
                $("#tab-pin-discription-"+i.language_id).html(html)

            })
    }
    templatePresent(data){
        return `<table width=600>` +
            data.data.res.map((i) => {
                return `
                <tr><td width="150"><b>NAV Varenavn</b></td><td>${i.nav_name}</td></tr>
                <tr><td ><b>Gave status</b></td><td>${i.state}</td></tr>
                <tr><td ><b>Leverandør</b></td><td>${i.vendor}</td></tr>
                <tr><td ><b>Varepris</b></td><td>${i.price}</td></tr>
                <tr><td ><b>budgetpris</b></td><td>${i.price_group}</td></tr>
                <tr><td ><b>Vejl. Pris</b></td><td>${i.indicative_price}</td></tr>
                <tr><td ><b>Moms sats</b></td><td>${i.moms}</td></tr>
                <tr><td ><b>Logo</b></td><td><img width="200"  src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/${i.logo}" /></td></tr>
            `;
            }).join('') +  `</table>`;
    }
    templatePresentMedia(data){
        return `<table width=100%><tr>` +
            data.data.res.map((i) => {
                return `
                <td ><img  width="150" src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/user/${i.media_path}.jpg"</td>
            `;
            }).join('') +  `</tr></table>`;
    }

    templatePresentModel(data){
        $(".tab-pin-model").html("");

        data.data.res.map((i) => {
             let html = `<div style="border: 1px solid lightgray;padding: 5px;"><table width=600 >
                    <tr><td width="200"><b>Varenr./sampaknr.</b></td><td>${i.model_present_no}<br><br></td></tr>
                    <tr><td ><b>Produktnavn</b></td><td>${i.model_name}<br></td></tr>
                    <tr><td ><b>Variant / farve (Valgfri)</b></td><td>${i.model_no}<br><br></td></tr>
                    <tr><td ><td ><img  width="150" src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/${i.media_path}"</td></tr>
                </table></div>`;
            $("#tab-pin-model-"+i.language_id).append(html)
        })
    }
    templateBuildBudget(){
        console.log(this.Budget);
        /*
        <option style="display:none">Budget:</option>
        <option value="">ingen</option>
        <option value="">100</option>
        <option>200</option>
        <option>400</option>
        <option>560</option>
        <option>640</option>
        <option>800</option>
        <option>960</option>
*/

    }


    template(){
        return `
        <table id="pimtable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Produktbillede</th>
                <th>Varenr.</th>
                <th>Title</th>
                <th>NAV Title</th>
                <th>Leverandør</th>
                <th>Beskrivelse</th>
                <th></th>
            </tr>
        </thead>

    </table>

        `
    }

}

var Base64 = {


    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",


    encode: function(input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output + this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },


    decode: function(input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    _utf8_encode: function(string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    _utf8_decode: function(utftext) {
        var string = "";
        var i = 0;
        var c = 0;
        var c1 = 0;
        var c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}
