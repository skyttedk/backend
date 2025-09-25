var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";
//wh-portal-container

function WarehousePortal() {
    let self = this;
    this.init = async function(){

        $("#login-form").hide();
        this.initModal();

        let data = await this.loadData();

        $("#wh-portal-container").html(this.mainTemplate(data,_warehousename));
        this.setEvents();
    };
    this.loadData = async function (){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"warehousePortal/readShopData", {token: _token}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    };
    this.loadShopDownloadData = async function (token){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"warehousePortal/readShopDownloadData", {token: token}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    };
    this.setEvents = function (){
        let self = this
        $(".hent-filer").click( async function(){
            $("#myModal").html("");
            let token = $(this).attr("data-id");
            // Her åbner vi modalen
            $("#myModal").dialog("open");

            let data = await self.loadShopDownloadData(token);
            $("#myModal").html(self.fileDowonloadTemplate(data));
            let status = await self.readStatus(token);
            self.setPackagingStatus(status)
            self.setNoteToGF(status)
            self.setModalEvents(token)
        });
        $("input[name='status']").change(function() {
            const selectedValue = $(this).val();
            $(".all-shops").hide()
            $("."+selectedValue).show()
        });


    }
    this.setModalEvents = function(shopToken){
        let self = this;
        $(".swh-download").unbind("click").click(
            function(){
                console.log($("#packaging-status").val())
                if($("#packaging-status").val() < 4){
                    alert("HUSK at ændre STATUS til 'Pakkeri igang', hvis der skal pakkes");
                }
                let token = $(this).parent().parent().attr("data-id")
                window.open(BASE_AJAX_URL + 'warehousePortal/download&token=' + token, '_blank');
            }
        );
        $("#update-packaging-status").unbind("click").click(function(){
            let packaging_status = $("#packaging-status").val();
            $.post(BASE_AJAX_URL+"warehousePortal/updateStatus", {token: shopToken, packaging_status: packaging_status})
                .done(function(returnMsg) {
                    console.log(returnMsg)
                    if(returnMsg.status == 0){
                        alert("Der er opståen en fejl")
                        return;
                    }
                    alert("Status opdateret");
                    self.init()
                })
                .fail(function() {
                    alert("alert_problem");
                });
        });
        $("#save-note-to-gf").unbind("click").click(function(){

            let note =  $("#note-to-gf").val();
            $.post(BASE_AJAX_URL+"warehousePortal/updateNoteToGf", {token: shopToken, note_from_warehouse_to_gf: note})
                .done(function(returnMsg) {
                    if(returnMsg.status == 0){
                        alert("Der er opståen en fejl")
                        return;
                    }
                    alert("Note gemt");
                    //self.init()
                })
                .fail(function() {
                    alert("alert_problem");
                });


        });

    }

    this.readStatus = async function (token){
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"warehousePortal/readStatus", {token: token}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    }
    this.setNoteToGF = function (data){

        $("#note-to-gf").val("");
        let note_from_warehouse_to_gf = data.data[0].attributes.note_from_warehouse_to_gf;
        note_from_warehouse_to_gf = note_from_warehouse_to_gf || "";
        $("#note-to-gf").val(note_from_warehouse_to_gf);


    }
    this.setPackagingStatus = function (status){
        if(status.data.length == 0){
            $("#packaging_status").val(0);
        } else {
            let packaging_status = status.data[0].attributes.packaging_status;
            self.presentStatus = packaging_status;
            $("#packaging-status").val(packaging_status);
            if($("#packaging-status").val() != packaging_status){
                alert("Der er et problem med dropdown med status")
            }

        }
    }

    this.initModal = function (){
        $( "#myModal" ).dialog({
            autoOpen: false,
            width: 'auto',
            height: 'auto',
            buttons: {
                "Luk": function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    this.fileDowonloadTemplate = function(data){
        console.log(data)
        return `
  <select  id="packaging-status">
    <option value="0" >Ingen status sat</option>
    <option value="1" >lister ikke klar</option>
    <option value="3">lister godkendt</option>
 
    <option value="5">Pakkeri igang</option>
    <option value="6">Pakkeri færdig</option>
  </select><button  id="update-packaging-status" style="color: red;">Opdatere Status</button>

          <table class="styled-table"> <tr><th>Filnavn</th><th>Størrelse</th><th>Uploaded d.</th><th></th></tr>`+

            data.data.map((i) => {

                let filename = i.attributes.real_filename;
                let token = i.attributes.token;

                let size = i.attributes.file_size == 0 ? 0 : Math.round((i.attributes.file_size*1)/1000000)
                size = size < 1 ? "<1" :size;
                let uploadTime = i.attributes.created_at.date.replace(/\.0+$/, "");;
                return `
             <tr data-id="${token}">
                <td>${filename}</td>
                <td>${size} MB</td>
                <td>${uploadTime}</td>
                <td>
                    <button class="swh-download">DOWNLOAD</button>
                </td>
             </tr>
             `
            }).join('') +  `</table><br> <div>
                <label>Note fra larger til GaveFabrikken</label> <textarea id="note-to-gf" rows="6" cols="50" placeholder="Skriv note til GaveFabrikken" ></textarea><br>
                <button id="save-note-to-gf" >Gem note til GaveFabrikken</button>
            </div> `
    };

    this.mainTemplate = function(data,warehouse) {
        console.log(data);

        return `<div class="wh-portal-top">
            <h3>${warehouse} DEV</h3>
        
<div class="wh-portal-radio-group">
  <label class="wh-portal-radio-option">
    <input type="radio" name="status" checked="checked" value="all-shops">
    <span>Vis alle</span>
  </label>

  <label class="wh-portal-radio-option">
    <input type="radio" name="status" value="released">
    <span>Godkendt</span>
  </label>

  <label class="wh-portal-radio-option">
    <input type="radio" name="status" value="not-released">
    <span>Vis ikke godkendt</span>
  </label>
</div>
            </div>
        <br><br><br><br><br><br><br><br>
            
            <hr>
         <table class="styled-table" id="warehouse-data"> 
              <thead>
            <tr>
             <th>SO-NR</th>
            <th width="50">Shop navn</th>
            <th>Ansvarlig</th>
            <th>Antal varenr</th>
            <th>Antal gaver</th>
            <th>Status</th>
            <th >Noter</th>
            <th width="150">Flyt</th>
            <th>Flere lev.</th>
            <th>Udland</th>
            <th>Leveringsdato</th>
            <th>Action</th>
        
            </tr>  </thead>`+
            data.data.map((i) => {
                let statList = ["Ingen status sat","lister ikke klar","","lister godkendt","","Pakkeri igang","Pakkeri færdig"];

                let packaging_status = i.attributes.packaging_status == null || i.attributes.packaging_status == "" ? 0 :i.attributes.packaging_status;
                let name = i.attributes.name;
                name = name.replace(/-/g, " - ");
                let Ansvarlig = i.attributes.valgshopansvarlig || "";
                let itemno = i.attributes.order_no_count;
                let order_count = i.attributes.order_count;
                let udland = i.attributes.udland || "";
                let moreDelevery = i.attributes.flere_leveringsadresser == 1 ? "Flere" : "";
                let note = i.attributes.noter || "";
                let note_move_order = i.attributes.note_move_order || "";
                note_move_order = note_move_order.replace(/\n/g, '<br>');
                note = note.replace(/\n/g, '<br>');
                note = note.replace(/,/g, ", ");
                let deleveri = i.attributes.levering || "";
                let token = i.attributes.token;
                let stat = statList[packaging_status]
                let pack_status = packaging_status > 2 ? "released":"not-released";
                let so_on = i.attributes.so_no || "";

                return `
              <tbody>
    <tr data-id="${token}" class="${pack_status} all-shops ">
                <td>${so_on}</td>
                <td width="50">${name}</td>
                <td>${Ansvarlig}</td>
                <td>${itemno}</td>
                <td>${order_count}</td>
                <td width="150">${stat}</td>
                <td >${note}</td>
                <td>${note_move_order}</td>
                <td>${moreDelevery}</td>
                <td>${udland}</td>                
                <td>${deleveri}</td>
                <td>
                    <button data-id="${token}" class="hent-filer ${pack_status} " >Hent filer</button>
                </td>
             </tr>
               </tbody>
             `
            }).join('') +  `</table>  `


    }







}