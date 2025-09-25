AppPcms.presentPrice = (function () {
    self = this;
    self.data = {};
    self.showPrice ={};
    self.init = async () => {
      $("#modalChangeMultiPriceView").modal('show');
      self.data.customConfig = await self.loadData();

      self.showPrice = await self.loadShowPrice();

      self.buildUI();
      self.event();
    }
    self.event = () => {
      $(".changeMultiPrice-save").unbind('click').click( async function () {
          await self.save();
          $("#modalChangeMultiPriceView").modal('hide');
          message("Priserne er blevet opdateret")
      })

      $(".slider").unbind('click').click( async function () {
          await self.updateShowPrice();
      })
      $("#csv").unbind('click').click( async function () {
            const rows = [
    ["name1", "city1", "some other info"],
    ["name2", "city2", "more info"]
];

            exportToCsv("test.csv",rows);
      })


    }
    self.save = () => {
       return new Promise(function(resolve, reject) {
         let returnI = 1;

        $(".present-item" ).each( async function( index ) {
           let pt_special_no = "", pt_pris_no = "", pt_budget_no = "";
           let doUpdate = false;
           let presentId = $( this ).attr("item-data");


           if( $( this ).find($(".pt_special_no")).val() !=  $( this ).find($(".pt_special_no")).attr("cv-data") ){
               doUpdate = true;
               pt_special_no =  $( this ).find($(".pt_special_no")).val();
           }
           if($( this ).find($(".pt_pris_no")).val() != $( this ).find($(".pt_pris_no")).attr("cv-data")){
               doUpdate = true;
               pt_pris_no =  $( this ).find($(".pt_pris_no")).val();
           }

           if($( this ).find($(".pt_budget_no")).val() != $( this ).find($(".pt_budget_no")).attr("cv-data")){
               doUpdate = true;
               pt_budget_no = $( this ).find($(".pt_budget_no")).val();
           }
           if(doUpdate == true){
                let show = $("#use_custon_price:checked").length > 0 ? "1":"none";
                let obj = {
                    "pris":pt_pris_no ,
                    "budget":pt_budget_no,
                    "special":pt_special_no,
                    "show":"1"
            }
            _presentSetting.set(presentId, obj);
            await self.doSave(presentId,obj);
           }
           if($(".present-item" ).length > returnI){
             resolve();
           }
        });
     })
    }
    self.doSave = (id,data) => {
              return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"presentSetting/update",{presentationId:_presentationId,id:id,config:data}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
    }
    self.loadShowPrice  = () => {
         return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"presentSetting/loadShowPrice",{id:_presentationId}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
    }
    self.updateShowPrice = () => {
              let showPrice =  ($("#showPrices").is(':checked') ? 0 : 1 );
              return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"presentSetting/updateShowPrice",{id:_presentationId,showPrice:showPrice}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
    }

    self.loadData = () => {
         return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"presentSetting/load",{id:_presentationId}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
     }
     self.loadCustomConfig = (id) => {
            return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"present/getById",{id:id,lang:_lang}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
     }
     self.buildUI = () => {
        $(".modalChangeMultiPrice").html("");

        if(self.showPrice.data[0].show_price == 1){
                document.getElementById("showPrices").checked = true;
        }


        $.each(self.data.customConfig.data, async function(index, value) {
            let pt_special_no = "", pt_pris_no = "", pt_budget_no = "";
            let pt_special_sys = "", pt_pris_sys = "", pt_budget_sys = "";
            let systemPrice = {}, settings = {};
            let customPrice = JSON.parse(value.setting);
            let present = await self.loadCustomConfig(value.present_id);


            if(_lang==1){ systemPrice = JSON.parse(present.data[0].pt_price);           }
            if(_lang==4){ systemPrice = JSON.parse(present.data[0].pt_price_no);           }

            if(value.setting.length > 10){
                settings = JSON.parse(value.setting)
                pt_special_no = settings.special;
                pt_pris_no    = settings.budget;
                pt_budget_no  = settings.pris;
            }

            if(Object.keys(systemPrice).length > 5){
                pt_special_sys = ( systemPrice.special == "" ) ? "Ingen pris sat": systemPrice.special;
                pt_pris_sys = ( systemPrice.budget == "" ) ? "Ingen pris sat": systemPrice.budget;
                pt_budget_sys = ( systemPrice.pris == "" ) ? "Ingen pris sat": systemPrice.pris;
            }

            let html = "<tr><td colspan=3><br><b><u>"+present.data[0].caption+"</u></b></td></tr>";
                html+="<tr class='present-item' item-data='"+value.present_id+"'><td><img src='../../../fjui4uig8s8893478/"+present.data[0].pt_img+"' width=60  /></td>";
                html+="<td   ><label style='font-size:12px;'>Specialaftale:</label><br><input class='pt_special_no' cv-data='"+pt_special_no+"'  type='text' size=20  value='"+pt_special_no+"' placeholder='Pris ej sat' /><br><label><i>"+pt_special_sys+"</i></label></td>";
                html+="<td ><label style='font-size:12px;'>Vejl. udsalgspris:</label><br><input class='pt_pris_no' cv-data='"+pt_pris_no+"' type='text' size=20 value='"+pt_pris_no+"' placeholder='Pris ej sat' /><br><label><i>"+pt_pris_sys+"</i></label></td>";
                html+="<td ><label style='font-size:12px;'>Budget:</label><br><input class='pt_budget_no' cv-data='"+pt_budget_no+"' type='text' size=20 value='"+pt_budget_no+"' placeholder='Pris ej sat' /><br><label><i>"+pt_budget_sys+"</i></label></td>";
                html+="</tr>";
            $(".modalChangeMultiPrice").append(html);
        });
     }


})

function exportToCsv(filename, rows) {
    var processRow = function (row) {
        var finalVal = '';
        for (var j = 0; j < row.length; j++) {
            var innerValue = row[j] === null ? '' : row[j].toString();
            if (row[j] instanceof Date) {
                innerValue = row[j].toLocaleString();
            };
            var result = innerValue.replace(/"/g, '""');
            if (result.search(/("|,|\n)/g) >= 0)
                result = '"' + result + '"';
            if (j > 0)
                finalVal += ',';
            finalVal += result;
        }
        return finalVal + '\n';
    };

    var csvFile = '';
    for (var i = 0; i < rows.length; i++) {
        csvFile += processRow(rows[i]);
    }

    var blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, filename);
    } else {
        var link = document.createElement("a");
        if (link.download !== undefined) { // feature detection
            // Browsers that support HTML5 download attribute
            var url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
}
