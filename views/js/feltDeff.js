var  _feltIndex = 0;
var _feltDataContainer;
var _feltControllIndex = 0;
var _feltData;
var _langList = ["En","De","No","Sv"];
var feltDeff = {

    handleBudgetToShopuser: async function (){
        let targetAttID;
        let shopAtt = await feltDeff.loadShopAtt();
        $.each(shopAtt.data.attributes, function( index, value ) {
            if(value.name.toLowerCase() == "budget"){
                targetAttID = value.id;

            }
        })
        let responce = await feltDeff.addBudgetToShopuser(targetAttID)
        alert("Brugerne er blevet opdateret med deres budget")
        console.log(responce);





    },
    addBudgetToShopuser: async function (targetAttID){
        return new Promise(resolve =>
            {
                $.ajax(
                    {
                        url: 'index.php?rt=shopuser/addBudgetToShopuser',
                        type: 'POST',
                        dataType: 'json',
                        data: {shop_id:_shopId,target_att:targetAttID}
                    }
                ).done(function(res) {
                        resolve(res);
                    }
                )
            }
        );
    },

    loadShopAtt : async function(){
        return new Promise(resolve =>
            {
                $.ajax(
                    {
                        url: 'index.php?rt=shop/getShopAttributes',
                        type: 'POST',
                        dataType: 'json',
                        data: {id:_shopId}
                    }
                ).done(function(res) {
                        resolve(res);
                    }
                )
            }
        );
    },


    addNew : function(){
        var html = "<li class=\"ui-state-default \" id=\"feltIndex"+_feltIndex+"\" data-id=\"\"><table width=100% height=60 border=1 BORDERCOLOR=white ><tr>"
        html+= "<td width=100 align=center><input  type=\"text\" style=\"width:90%;\" class=\"feltNameText\" ></td> "
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" ></td>"
        html+= "<td width=200 align=center><textarea style=\"width:90%; height:90%\" class=\"feltTextArea\"></textarea></td>"
        html+= "<td width=10 align=center><img class=\"icon\" src=\"views/media/icon/1373253296_delete_64.png\"  onclick=\"feltDeff.deleteItem('"+_feltIndex+"')\" height=\"25\" width=\"25\" /></td>"
        html+= "<td width=10 align=center><img class=\"icon\" src=\"views/media/icon/move.png\"  width=\"25\" /></td>"
        html+= "</tr></table></li>";

        $("#feltDeffContainer").append(html)

        var langHtmlEn = "<tr id=\"feltIndex"+_feltIndex+"En\"  ><td cellspacing=20 height=40><input  type=\"text\" style=\"width:90%;\" class=\"felt\" ></td></tr>";
        var langHtmlDe = "<tr id=\"feltIndex"+_feltIndex+"De\" ><td cellspacing=20 height=40><input  type=\"text\" style=\"width:90%;\" class=\"felt\" ></td></tr>";
        var langHtmlNo = "<tr id=\"feltIndex"+_feltIndex+"No\"><td cellspacing=20 height=40><input  type=\"text\" style=\"width:90%;\" class=\"felt\" ></td></tr>";
        var langHtmlSv = "<tr id=\"feltIndex"+_feltIndex+"Sv\" ><td cellspacing=20 height=40><input  type=\"text\" style=\"width:90%;\" class=\"felt\" ></td></tr>";
        $("#feltdeffEn").append(langHtmlEn)
        $("#feltdeffDe").append(langHtmlDe)
        $("#feltdeffNo").append(langHtmlNo)
        $("#feltdeffSv").append(langHtmlSv)
        _feltIndex++;
    },
    update : function(){
        var i = 0;
        var langHtmlEn = ""
        var langHtmlDe = ""
        var langHtmlNo = ""
        var langHtmlSv = ""
        var id= "";
        $('#feltDeffContainer').children('li').each(function () {
            id =  $(this).attr('id')
            langHtmlEn+= "<tr id=\"feltIndex"+id+"En\" ><td cellspacing=20 height=40><input value=\""+$('#'+id+'En input').val()+"\"  type=\"text\" style=\"width:90%;\" class=\"felt\" ></td></tr>";
            langHtmlDe+= "<tr id=\"feltIndex"+id+"De\"><td cellspacing=20 height=40><input value=\""+$('#'+id+'De input').val()+"\"  type=\"text\" style=\"width:90%;\" class=\"felt\" ></td></tr>";
            langHtmlNo+= "<tr id=\"feltIndex"+id+"No\"><td cellspacing=20 height=40><input value=\""+$('#'+id+'No input').val()+"\"  type=\"text\" style=\"width:90%;\" class=\"felt\" ></td></tr>";
            langHtmlSv+= "<tr id=\"feltIndex"+id+"Sv\"><td cellspacing=20 height=40><input value=\""+$('#'+id+'Sv input').val()+"\"  type=\"text\" style=\"width:90%;\" class=\"felt\" ></td></tr>";
        });

        $("#feltdeffEn").html(langHtmlEn)
        $("#feltdeffDe").html(langHtmlDe)
        $("#feltdeffNo").html(langHtmlNo)
        $("#feltdeffSv").html(langHtmlSv)



    },
    deleteItem : function(id){
         if(confirm("Vil du slette felt") == true){
            $("#feltIndex"+id).remove();
            $("#feltIndex"+id+"En").remove();
            $("#feltIndex"+id+"De").remove();
            $("#feltIndex"+id+"No").remove();
            $("#feltIndex"+id+"Sv").remove();
         }



    },
    deleteStaticItem : function(id)
    {

        if(confirm("Vil du slette felt") == true){
            ajax({"id":id},"shop/removeAttribute","feltDeff.deleteStaticItemResponce","");
        }

    },
    deleteStaticItemResponce : function()
    {
       feltDeff.runDataUpdata();
    },


    loaditem : function(dataToLoad){

        var htmlCheck = "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\" checked ></td>" ;
        var htmlNotCheck =  "<td width=25 align=center><input  type=\"checkbox\" class=\"felt\"  ></td>";
        for(var i=0;dataToLoad.length > i; i++){
           var html = "<li class=\"ui-state-default \" id=\"feltIndex"+_feltIndex+"\" data-id=\""+dataToLoad[i].id+"\"><table width=100% height=60 border=1 BORDERCOLOR=white ><tr>"
            html+= "<td width=100 align=center><input  type=\"text\" style=\"width:90%;\" class=\"feltNameText\" value=\""+dataToLoad[i].name+"\" ></td> "

            dataToLoad[i].is_username == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            dataToLoad[i].is_password == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            dataToLoad[i].is_email == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            dataToLoad[i].is_name == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            dataToLoad[i].is_locked == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            dataToLoad[i].is_mandatory == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            dataToLoad[i].is_visible == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            dataToLoad[i].is_searchable == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            dataToLoad[i].is_visible_on_search == 1 ?  html+= htmlCheck : html+= htmlNotCheck
            var feltTextArea = (dataToLoad[i].list_data == null ? "" : dataToLoad[i].list_data);
            html+= "<td width=200 align=center><textarea style=\"width:90%; height:90%\" class=\"feltTextArea\">"+feltTextArea+"</textarea></td>"
            html+= "<td width=10 align=center><a href=\"javascript:onclick=feltDeff.deleteStaticItem('"+dataToLoad[i].id+"') \" ><img class=\"icon\" src=\"views/media/icon/1373253296_delete_64.png\" height=\"25\" width=\"25\" /></a></td>"
            html+= "<td width=10 align=center><img class=\"icon\" src=\"views/media/icon/move.png\"  width=\"25\" /></td>"
            html+= "</tr></table></li>";
            $("#feltDeffContainer").append(html)
            _feltIndex++;
        }
            $(".safeLayerTimer").hide();
            $(".safeLayer").hide();

    },
    runDataUpdata : function(){
      ajax({"id":_editShopID},"shop/read","feltDeff.runDataUpdateResponce","");

    },
    runDataUpdateResponce : function(responce)
    {
         $("#feltDeffContainer").html("")
        feltDeff.loaditem(responce.data.shop[0].attributes_)
        feltDeff.showLangFields();
        feltDeff.loadFeltDefWarnings();
    },


    saveItem : function(){
            $(".safeLayerTimer").show();
            $(".safeLayer").show();
            var saveData = [];
            var sortIndex = 0;
            var checkBoxList = ["is_username","is_password","is_email","is_name","is_locked","is_mandatory","is_visible","is_searchable","is_visible_on_search"];

            $('#feltDeffContainer').children('li').each(function () {
                var temp = {};
                temp["shop_id"] = _editShopID;
                id =  $(this).attr('id')
                if($(this).attr('data-id') != ""){
                    temp["id"] = $(this).attr('data-id');
                } else {
                    temp["id"] = "";
                }

                temp["index"] = sortIndex;
                temp["name"]  = $("#"+id+" .feltNameText" ).val()
                temp["data_type"] = 1;

                $("#"+id+" .felt" ).each(function (index, element) {
                    var eleName = checkBoxList[index]
                    temp[eleName] = $(element).is(':checked') ? 1 : 0
                })


               if( $("#"+id+" .feltTextArea" ).val() != "" ){
                    temp["is_list"] = 1
                    temp["list_data"] = $("#"+id+" .feltTextArea" ).val();
                } else {
                    temp["is_list"] = 0
                    temp["list_data"] = "";
                }
                // gemmer data fra lang felter
                var tempLandData = {};
                if(temp["is_list"] == 0){
                    for(var j=0;_langList.length > j;j++){
                        var obj = {};
                        obj.name = $("#feltdeff"+_langList[j]+""+temp["id"]).val();
                        obj.id = temp["id"];
                        tempLandData[_langList[j]] = obj;

                    }
                }
              
                if(temp["is_list"] != 0){
                    for(var j=0;_langList.length > j;j++){
                        var obj = {};
                        obj.name = $("#feltdeff"+_langList[j]+""+temp["id"]).val();
                        obj.id = temp["id"];
                        tempLandData[_langList[j]] = obj;

                    }
                }


                temp["languages"] =  JSON.stringify(tempLandData);

                saveData[sortIndex] = temp;
                sortIndex++;
            })
            _feltDataContainer = saveData;
            _feltControllIndex = 0;
            $(".feltdeffLang").html("");

            feltDeff.saveControl();

    },
    saveControl : function()
    {
        if(_feltDataContainer.length > _feltControllIndex){
            if(_feltDataContainer[_feltControllIndex].id == ""){
                ajax(_feltDataContainer[_feltControllIndex],"shop/addAttribute","feltDeff.saveControlResponce","");
            } else {
                ajax(_feltDataContainer[_feltControllIndex],"shop/updateAttribute","feltDeff.saveControlResponce","");
            }

        } else {
            _feltControllIndex = 0;
            feltDeff.runDataUpdata();
        }
    },
    saveControlResponce : function(responce)
    {
        _feltControllIndex++;
        console.log(responce)
        feltDeff.saveControl();
    },
    showLangFields : function(lang){
        //$("#feltdeff"+lang).html(lang)
                $(".safeLayerTimer").show();
        $(".safeLayer").show();
        ajax({"id":_editShopID},"shop/getShopAttributes","feltDeff.showLangFieldsResponse","");

    },
    showLangFieldsResponse : function(responce){

        for(var j=0;_langList.length > j;j++){
                if($("#feltdeff"+_langList[j]).html() == ""){
                    var html ="<table border=1 width=60%>";
                    $.each(responce.data.attributes, function( index, value ) {
                        var obj = JSON.parse(value.languages);
                        var inputVal = "";
                        if(isEmpty(obj) ){
                            inputVal = "";
                        } else {
                            inputVal = obj[_langList[j]]["name"];
                            if(inputVal == undefined ){
                                inputVal = "";
                            }
                        }
                        if(value.name != "Gaveklubben tilmelding"){
                            html+="<tr><td width=100>"+value.name+"</td><td><input  id='feltdeff"+_langList[j]+""+value.id+"' type=\"text\"  value='"+inputVal+"' /></td></td>";
                        }

                        /*
                        var obj = JSON.parse(value.languages);
                        if(value.list_data == ""){
                            var inputVal = obj[_langList[j]]["name"]
                            if(inputVal == undefined ){
                                inputVal = "";
                            }
                            html+="<tr><td width=100>"+value.name+"</td><td><input  id='feltdeff"+_langList[j]+""+value.id+"' type=\"text\"  value='"+inputVal+"' /></td></td>";
                        }
                        */


                    });
                    html+="</table>";
                    $("#feltdeff"+_langList[j]).html(html);
                }
        }
        $(".safeLayerTimer").hide();
        $(".safeLayer").hide();
        feltDeff.loadFeltDefWarnings();
    },
    loadFeltDefWarnings() {
        ajax({"id":_editShopID},"shop/getShopAttributeWarnings","feltDeff.showFeltDefWarning","");
    },
    showFeltDefWarning(response) {
        system.endWork();
        if(response != undefined && response != null && response.hasOwnProperty("status") && response.status == 1 && response.hasOwnProperty("message") && $.trim(response.message) != "") {
            $('#feltdefWarnMessage').html(response.message).show();
        } else {
            $('#feltdefWarnMessage').hide();
        }
    }
}

 function isEmpty(obj) {
    for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
            return false;
    }

    return true;
}










