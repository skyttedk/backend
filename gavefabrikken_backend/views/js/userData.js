 var _excel;
var _colHeaders =[];
var _columns=[];
var _colHeaderId = [];
var _sourceData;
var _recordLength;
var _saveIndex = 0;
var _userDataShopId;
var _userDataCompanyId;
var _renderDB = [];
var _dataObject = [];
var _dataToLoad = [];
var _colHeaderName = [];
var hotElement;
var hotElementContainer;
var  hotSettings;
var _index;
var _currentCell;
var _errorFrom=-1;
var _userIdControl = "";
var _allDeletet = false;
var _pause = false;
var _pauseCounter = 0;
var _repuestErrorCallCounter = 0;
var _presentData = [];
var _hasShowWarning = false;


var userData = {
    resetApp : function(){
     $("#userUploadMsg").html("");
       $("#userErrorMsg").html("")
        $("#userErrorMsg").hide();
        $("#uploadSave").show()
        $("#userUploadMsg").show();


         $("#goon").hide();

     _errorFrom = -1;
     _excel.destroy()
      _excel;
_colHeaders =[];
_columns=[];
_colHeaderId = [];
_sourceData;
_recordLength;
_saveIndex = 0;
_userDataShopId;
_userDataCompanyId;
_renderDB = [];
_dataObject = [];
_dataToLoad = [];
_colHeaderName = [];
hotElement;
hotElementContainer;
hotSettings = {};
        userData.initHandsomController("loadfields");
      $("#userUploadMsg").html();

    },
    openApp : function(){

_excel;
_colHeaders =[];
_columns=[];
_colHeaderId = [];
_sourceData;
_recordLength;
_saveIndex = 0;
_userDataShopId;
_userDataCompanyId;
_renderDB = [];
_dataObject = [];
_dataToLoad = [];
_colHeaderName = [];
hotElement;
hotElementContainer;
hotSettings = {};
        $(".main").hide();
        $("#userUploadContainer").html("")
        $("#userUploadContainer").show();
        $(".uploadGf").show();
            $("#userUploadMsg").show();



        userData.initHandsomController("loadfields");
    },
    closeApp : function(){
        _excel.destroy()
        $("#userUploadContainer").html("")
        $("#userUploadContainer").hide();
        $("#shopTabs").tabs("option", "active", 0);
        $(".uploadGf").hide();
        $(".main").show();
         $("#userUploadMsg").hide();
    },
    initHandsomController : function(action){
        if(action == "loadfields"){
            _userDataShopId = "";
            _userDataCompanyId = "";
        /////   // ajax({"id":_editShopID},"shop/getUsers","userData.makeInitData","");


         ajax({"id":_editShopID},"shop/getShopAttributes","userData.makeInitData","");
        }



        if(action == "init"){
    //    alert("hej jeg er ved at arbejde her, er done kl 11.30")

   /*
           if(typeof _excel !== "undefined"){
                _excel.destroy();
           }
     */
    		hotSettings = {
    			data: _dataObject,
    			wordWrap : false,
    			startRows: 0,
    			startCols: 9,
    			minRows: 0,
    			minCols: 9,
    			maxRows: 100000,
    			width: "100%",
    			rowHeights: 23,
    			rowHeaders: true,
    			colHeaders: true,
    			minSpareRows: 1,
    			contextMenu: ["row", "remove_row"],
                afterChange: function(changes, source) {
                    userData.updateCellData(changes, source);
                }
    		};
    		hotElement = document.querySelector('#userUploadContainer');
    		hotElementContainer = hotElement.parentNode;

            hotSettings.colHeaders = _colHeaders;
    		hotSettings.columns = _columns;
    		_excel = new Handsontable(hotElement, hotSettings);





            _excel.updateSettings({
                contextMenu: {
                    items: {"row":
                        { name: 'Slet , ok?',
                            callback:function(){
                                r = confirm("Vil du at slette?");
                                if (r == true) {
                                    var row = _excel.getSelected()[0]
                                    var rowData = _excel.getDataAtRow(row)
                                    if(rowData[0] != null){
                                         ajax({"user_id":rowData[0]},"shop/removeShopUser","","");
                                         _excel.alter('remove_row', row);
                                    } else {
                                       _excel.alter('remove_row', row);
                                    }


                                    //console.log(row);
                                }
                            }
                        },
                          "remove_row":{
                         name:'fjern linjen',callback:function(){
                            var row = _excel.getSelected()[0];
                            _excel.alter('remove_row', row);
                         }
                     }


                    }

                }
            })

            $(".safeLayerTimer").hide();
            $(".safeLayer").hide();
                     if(_allDeletet == true){ // retter en fejl hvor brugerid bliver vist forkert ved at slette alt
                _allDeletet = false;
                 _excel.alter('remove_row', 0);
             ///   _excel.getDataAtCell(0,0,"")
            }


        }
        if(action == "loadUserData"){
            $(".safeLayerTimer").show();
            $(".safeLayer").show();


      // når man ikke skal indlæse brugere  userData.initHandsomController("init");



                      ajax({"id":_editShopID},"order/getUsersOrderAndAlias","userData.loadOrderDataResponce","");

//                  ajax({"id":_editShopID},"shop/getUsersSQL","userData.debugData","");
        }
    },

    debugData : function(responce){


    },
    loadOrderData: function(){

    },
    loadOrderDataResponce : function(responce){

        $.each(responce.data, function( index, value ) {
           _presentData[value.attributes.shopuser_id] = {"id":value.attributes.id,"alias":value.attributes.fullalias}
        })

    userData.loadUserData()
    },

    loadUserData:function(){
         ajax({"id":_editShopID},"shop/getUsersSQL","userData.insetUserData2","");
    },

    insetUserData2 : function(responce){

        var tempArr = [];
        var firstRun = true;

        $.each(responce.data, function( index, value ) {
            if(firstRun == true){
               _userIdControl = value.attributes.shopuser_id;
               _userDataCompanyId = value.attributes.company_id
               _userDataShopId    = value.attributes.shop_id
               firstRun = false;

            }
            if(_userIdControl == value.attributes.shopuser_id  ){

            } else {
                userData.putUserInDataObj(tempArr,_userIdControl);
                _userIdControl = value.attributes.shopuser_id;
                tempArr = []
            }
            tempArr["id_"+value.attributes.attribute_id] = value.attributes.attribute_value;

        })
        userData.putUserInDataObj(tempArr,_userIdControl);
        userData.initHandsomController("init");
        if(responce.data.length == 0){

           _excel.alter('remove_row', 0);
        }
    },
    putUserInDataObj : function(tempArr,shopuser_id){

        var tempData = [];
        tempData.push(shopuser_id);
        if(_presentData[shopuser_id]){
           tempData.push(_presentData[shopuser_id].alias)
        } else {
           tempData.push("");
        }
        for(var h=0;_colHeaderId.length>h;h++){
            tempData.push( tempArr["id_"+_colHeaderId[h] ] );
        }
    // herherher
    if(_editShopID == "282" || _editShopID == "280"){

    } else {
      _dataObject.push( tempData );
    }

    },
    insetUserData : function(responce){
     // console.log(responce.data[0].attributes.attribute_id)
      //var rowData = responce.data.users[1].user_attributes;
      var rowData = responce.data

     // console.log(rowData)

      $.each(responce.data, function( index, value ) {
       // console.log(value.attributes.attribute_value)

        var innerValue = value;
        var tempArr = [];
        var shopuser_id = "";
        for(var j=0;innerValue.length > j;j++)
        {
            tempArr["id_"+innerValue[j].attributes.attribute_id] = innerValue[j].attributes.attribute_value;
        }

        var tempData = [];

       // tempData.push(innerValue[0].attributes.shopuser_id);

        for(var h=0;_colHeaderId.length>h;h++){
            tempData.push( tempArr["id_"+_colHeaderId[h] ] );
        }
        _dataObject.push( tempData );

      });

      userData.initHandsomController("init");
    },



makeInitData: function(responce) {
    _colHeaders =["BrugerId","Gave"];
    _columns =[{readOnly: true},{}];
    _colHeaderId =[];
    _colHeaderId["BrugerId"] = -1;
    _colHeaderId["Gave"] = -2;
    var load = responce.data.attributes;
    _userDataShopId = responce.data.shop_id;
    _userDataCompanyId = responce.data.company_id;

    for(var i = 0; load.length > i; i++) {
      _colHeaders.push(load[i].name);
      _colHeaderId[i] = load[i].id;

      _columns.push({});
    }

    userData.initHandsomController("loadUserData");
  },
    deleteAllCheck : function()
    {
        var check = prompt("Indtast kode for at slette alle","");

        if (check == "detskalnokvirke") {
          _allDeletet = true;
            userData.deleteAll();
        } else {
          alert("forkert kode / eller annuleret")
        }

    },
    deleteAll : function(){
        _sourceData = _excel.getData();
       // console.log(_sourceData)
        _index = _sourceData.length-1
        _recordLength = _sourceData.length
        userData.deleteAllControler();

    },
    deleteAllControler : function(){
        var progressNumber = (   _index / (_recordLength-1) ) * 100;
        progressNumber = Math.round(progressNumber);
        $("#userUploadMsg").html(progressNumber+ " %");
        if(_index > 0){
            _index--;
            ajax({"user_id":_sourceData[_index][0]},"shop/removeShopUser","userData.deleteAllControler","");
        } else {
            alert("Done");
            userData.resetApp()
        }
    },



    saveItem : function(){
           _hasShowWarning = false;
            _sourceData = _excel.getData();
            _recordLength = _sourceData.length




            if(_errorFrom == -1){
                _saveIndex = 0;
                if(_recordLength > 100){
                    _sourceData[_saveIndex]

                    for(var i=0;_recordLength >i;i++){

                        if(_sourceData[_saveIndex][0] != null ){

                            _saveIndex++;

                        } else {

                        }
                    }

                }




            } else {
                _saveIndex = _errorFrom;
            }
           userData.saveController()

    },
    contiuenFromPause : function(){
         userData.doSave(_sourceData[_saveIndex])
    },
    saveController : function(){
        if(_recordLength > (_saveIndex+1) )
        {
                var progressNumber = (   _saveIndex / (_recordLength-1) ) * 100;
                progressNumber = Math.round(progressNumber);
                $("#userUploadMsg").html(progressNumber+ " %");
                //$( "#progressbar" ).progressbar( "option", "value", progressNumber );
                //$( ".progress-label" ).html(progressNumber+" %");

                userData.doSave(_sourceData[_saveIndex])

        } else {
            alert("Alt er nu indl&oelig;st")
            userData.resetApp()
        }

    },
    doSave : function(data){
        var rowData = {};
        var attribute = [];

        if(data[0] == null || data[0] == ""){


                var gavealias = data[1];

            for(var i=0;(data.length-2) >i;i++){

                var valueData = data[i+2];
                if(valueData == undefined){
                    valueData = "";
                }

                attribute.push({"id":_colHeaderId[i],"value":valueData});
            }
            rowData["userId"] = _sourceData[_saveIndex][0];
            rowData["shopId"] = _userDataShopId;
            rowData["companyId"] = _userDataCompanyId;

            var formData = {
                'gavealias':gavealias,
                'data':JSON.stringify(rowData),
                'attributes_':JSON.stringify(attribute)
            };


              $.ajax({
                   method: "POST",
                   url: "index.php?rt=shop/addShopUser",
                  data: formData
              })
              .done(function(responce) {
                  var responceObj = JSON.parse(responce);
                 if(responceObj.status != 1 ){

                   if(responceObj.message == "dublet"){
                      alert("Import er stoppet: dublet i linjenummer: "+(_saveIndex+1))
                        $("#uploadSave").hide();

                            $("#goon").show();
                    } else {
                        if(_repuestErrorCallCounter > 5){
                            _repuestErrorCallCounter = 0;
                            alert("importen er stoppet, den har forsøgt at indlæse linjenummer: "+(_saveIndex+1)+" 5 gange uden held")
                            $("#uploadSave").hide();
                            $("#goon").show();
                        } else {
                            _repuestErrorCallCounter++
                            _pause = true;
                            _saveIndex;

                            userData.doSave(data)
                        }
                    }
                 } else {

                    _pause = true;
                    _saveIndex++;
                    _pauseCounter++;

                    if(_pauseCounter == 500){
                         _pauseCounter = 0;
                         console.log("pause")
                        userData.doPause();
                    } else {
                          if(gavealias != null && gavealias != "" && gavealias != "null" ){
                                   var formData = {
                                   '_saveIndex':_saveIndex,
                                   'gavealias':gavealias,
                                   'user_id':responceObj.data.shopuser[0].id,
                                   'shop_id':responceObj.data.shopuser[0].shop_id}
                                   if(_hasShowWarning == false){
                                        _hasShowWarning = true;
                                            var r = confirm("vil du oprette en order for linje"+_saveIndex+"med gavealias "+gavealias);
                                    if (r == true) {
                                         ajax(formData,"order/orderUseAlias","userData.doSavePresentResponse","");
                                    } else {

                                          userData.saveController()
                                    }



                                   } else {
                                       ajax(formData,"order/orderUseAlias","userData.doSavePresentResponse","");


                                   }





                                //

                          } else {
                            userData.saveController()
                          }
                       //
                    }
                 }

              })
              .fail(function( msg ) {
                 alert( "Data save problem: " + JSON.stringify(msg) );
              })


           //ajax(formData,"shop/addShopUser","userData.doSaveResponce","");
        } else {

             _pause = true;
            _saveIndex++;
            var t =  setTimeout(userData.saveController, 200)
        }
    },

    doSavePresentResponse:function(response){
          if(response.status=="1"){
              userData.saveController()
          } else {
            alert("fejl prøv igen")
          }
    },


    doPause:function(){
           var t =  setTimeout(userData.saveController, 3000)
    },

    doSaveResponce : function(responce){
        if(responce.status=="2" || responce.status=="0"){
            alert("Import er stoppet: fejl i linjenummer: "+(_saveIndex+1)+"\nFejlbesked: "+responce.message)
            $("#userErrorMsg").html("Import er stoppet: fejl i linjenummer: "+(_saveIndex+1)+"\nFejlbesked: "+responce.message)

             $("#uploadSave").hide();
             $("#userErrorMsg").show();
            $("#goon").show();
        } else {
            _pause = true;
            _saveIndex++;
             userData.saveController()
        }
    },
    goon : function(){
        $("#userErrorMsg").html("")
        $("#userErrorMsg").hide();
        _errorFrom = _saveIndex;
        $("#goon").hide();
        this.saveItem();
    },

    clearCell : function()
    {

        var len = _excel.countRows()
        _excel.alter('remove_row',0 ,len);
        _excel.clear();
    },
    updateCellStatus : function()
    {
    	for(var i=0;_renderDB.length > i;i++)
        {
           	$(_renderDB[i]).addClass("redColor")
        }

    },
    updateCellData : function(changes, source)
    {
      //  console.log(changes)
        var attributes = [];







        if(source == "edit" || source == "Autofill.fill"){
            system.work()
            var rowId = changes[0][0];
            var rowData = _excel.getDataAtRow(rowId)
            console.log(changes)
            if(changes[0][1] == 1){
             var rowId = changes[0][0];
             var rowData = _excel.getDataAtRow(rowId)
              var formData = {
                                   '_saveIndex':changes[0][0]+1,
                                   'gavealias':changes[0][3],
                                   'user_id':rowData[0],
                                   'shop_id':_editShopID
                }
                 ajax(formData,"order/orderUseAlias","userData.updateCellDataResponse","");


            } else  if(rowData[0] != null){

                var tempData = {}
                for(var i=2;rowData.length>i;i++)
                {
                    tempData = {"attribute_id":_colHeaderId[i-2],"attribute_value":rowData[i]}
                    attributes.push(tempData);
                }

                var formData = {
                    'shop_id':_editShopID,
                    'user_id':rowData[0],
                    'attributes':JSON.stringify(attributes)
                };
                ajax(formData,"shop/updateShopUser","userData.updateCellDataResponse","");
            }
        }

    },
    updateCellDataResponse:function()
    {
      system.endWork();
    },

    helperLoadData : function(toFind,data){

    }









}

/************  hjælpe funktioner ***********/






