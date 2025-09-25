var _ajaxPath = "https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=";
var _lang = 1;
var EarlyPresent = (function () {
    self = this;
    self.deployContainer;
    self.init = async (container,lang) => {
        _lang = lang;
        self.deplayContainer = container;
        let data = await self.loadData();
        let htmlData = await self.buildUI(data.data.early);
        await self.deployUI(htmlData);
        self.setEvent();
        self.setLang();
    }
    self.setEvent = () => {
      $(".createNewEP").unbind('click').click( async function () {
           await self.create();
           self.rebuild();
      });
      $(".deleteEP").unbind('click').click( async function () {
        if (confirm("Confirm to delete item")) {
           await self.deactive(this.id);
           self.rebuild();
        }
      })
      $(".saveEP").unbind('click').click( async function () {
            await self.update();
            alert("Data updated")
            self.rebuild();
      })
      $(".lang").unbind('change').change(function() {
        _lang = this.value;
        self.changeLang();

      })

    }
    self.setLang = () => {
       $("#show_"+_lang).prop("checked", true);
       self.changeLang();
    }
    self.changeLang = () => {
      $(".ep-item").hide();
      $(".lang_"+_lang).show();
    }
    self.rebuild = () => {
        var ep = new EarlyPresent;
        ep.init("#ep-module",_lang);
    }
    self.deactive = (id) => {
          return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"earlypresent/delete",{id:id}, function(res, status) {
                    if(res.status == 0) {  return; }
                    else { resolve(res) }
                }, "json");
       })
    }
    self.update = () => {
           let returnI = 1;
           $(".ep-item" ).each( async function( index ) {
           returnI++;
           let doUpdate = false;
           let presentId = $( this ).attr("id");


           if( $( this ).find($(".item_nr")).val() !=  $( this ).find($(".item_nr")).attr("org-data") ){
               doUpdate = true;
           }
           if( $( this ).find($(".description")).val() !=  $( this ).find($(".description")).attr("org-data") ){
               doUpdate = true;
           }
           let data = {
            id:presentId,
            item_nr:$( this ).find($(".item_nr")).val(),
            description:$( this ).find($(".description")).val()
           }
           if(doUpdate == true){
                await self.doUpdate(data);
           }


           if($(".ep-item" ).length > returnI){
             resolve();
           }

           })


    }

    self.doUpdate = (data) => {
       return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"earlypresent/update",data, function(res, status) {
                    if(res.status == 0) {  return; }
                    else { resolve(res) }
                }, "json");
       })

    }
    self.create = () => {
       let data = {
          item_nr:$(".new_item_nr").val(),
          description:$(".new_description").val(),
          language:_lang
       };
       return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"earlypresent/create",data, function(res, status) {
                    if(res.status == 0) {  return; }
                    else { resolve(res) }
                }, "json");
       })
    }


    self.loadData = () => {
              return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"earlypresent/read",{}, function(res, status) {
                    if(res.status == 0) {  return; }
                    else { resolve(res) }
                }, "json");
        })
    }

    self.buildUI = (data) => {
      console.log(data)
         return new Promise(function(resolve, reject) {
        let html = "";
        html+="<tr><th>Varenr</th><th>Beskrivelse</th><th></th></tr>";
        html+= "<tr id='newEP'> <td><input class='new_item_nr' type='text' /></td> <td><input class='new_description' type='text' /> <td><button class='createNewEP' type='button'>Opret ny</button></td> </tr>";

        $.each(data, async function(index, value) {

             html+= "<tr class='ep-item lang_"+value.language+"' id='"+value.id+"'><td><input class='item_nr' org-data='"+value.item_nr+"' value='"+value.item_nr+"'  type='text' /></td><td><input class='description' org-data='"+value.description+"' value='"+value.description+"' type='text' /></td>";
             html+= "<td><button class='deleteEP' id='"+value.id+"' type='button'>Slet</button></td></tr>"
        })
        let htmlMenu = " <input id='show_1' class='lang' type='radio' name='ep' value='1'> Dansk   <input class='lang' id='show_4' type='radio' name='ep' value='4'> Norsk <input class='lang' id='show_5' type='radio' name='ep' value='5'> Svensk";
        htmlMenu+= "<button class='saveEP' type='button'>Gem &oelig;ndringer</button><hr>";
        var returnHtml =  htmlMenu+"<table>"+html+"</table>";
            resolve(returnHtml)
        });
    }
    self.deployUI = (htmlData) => {
        return new Promise(function(resolve, reject) {
            $(self.deplayContainer).html(htmlData);
            resolve();
        })
    }



})
