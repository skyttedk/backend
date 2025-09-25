
// /*** var der local router tilbage til gave når der editeret gave  ***/
var _localRouteToShopsOwnGift = false;
/*
function getLocalRouteToShopsOwnGift(){
  return _localRouteToShopsOwnGift;
}
function setLocalRouteToShopsOwnGift(val){
    _localRouteToShopsOwnGift = val;
}
*/

var _shopMode = false;
var _enterFocus="";
var _toCall = "";
var _sysToken = "";
function setEnterFocus(val){
setInterKeyAction(val)
 
}

function serverCall(obj) {
    _toCall =  obj.callUrl; 
   // obj.data.token = _sysToken;
   
    $.ajax({
        url: obj.serverUrl+obj.callUrl,
        method: "POST",
        data: obj.data,
		timeout: 1000000
   })
  .done(function(response) {
   // if(reponseObj.status=="1") {

        if(obj.returnElement == null || obj.returnElement == ""){

			//KSS>>
			//var reponseObject = JSON.parse(decodeURIComponent(response.trim()));
			var reponseObject = JSON.parse(response.trim());
			//KSS<<

            if(reponseObject.status=="1"  || reponseObject.status=="2" || _toCall == "shop/addShopUser" ) {
                eval( obj.returnCall+"(reponseObject)" )
            } else {
              $(".safeLayerTimer").hide();
              $(".safeLayer").hide();
                alert(reponseObject.message);

            }
        } else {
            var htmlResponce = response.trim();
            if(obj.returnElement == "html"){
                eval( obj.returnCall+"(htmlResponce)" )
            } else {                                   
                $(obj.returnElement).html(htmlResponce)
            }
        }

  })
  .fail(function(xhr, ajaxOptions, thrownError) {
        alert(thrownError);
  })
  .always(function() {
     // myVar = setTimeout(hideSafeLayer, 500)

   // alert( "complete" ); // måske skal vi bruge det til en extern logning
  });
}
function hideSafeLayer()
{
    //$(".safeLayerTimer").hide();
    //$(".safeLayer").hide();
}


var template = function(obj)
{
    $.get(obj.templateUrl+obj.template+".tph").then(function(dataTemplate) {
    var renderer = Handlebars.compile(dataTemplate);
    var html = renderer(obj.returnVal);

    if(obj.templateReturnCall != "" && obj.templateReturnCall != null ){
        eval( obj.returnCall+"(html)" )
        } else {
            $(obj.returnElement).html(html)
        }
    });
}




var system = function(data,callUrl,returnCall,returnElement,template,templateReturnCall)
{
    this.serverUrl = "index.php?rt=";
    this.data = data;
    this.callUrl =  callUrl;
    this.returnCall = returnCall;
    this.returnElement = returnElement;
    this.template = template;
    this.templateReturnCall = templateReturnCall;
    this.serverUrl = "index.php?rt=";
    this.templateUrl =  "views/tph/";
    return this;
}


var ajax = function(data,callUrl,returnCall,returnElement)
{
  //  $(".safeLayerTimer").show();
  //  $(".safeLayer").show();
    this.serverUrl = "index.php?rt=";
    this.data = data;
    this.callUrl =  callUrl;
    this.returnCall = returnCall;
    this.returnElement = returnElement;
    serverCall(this)
}



var system = {
    randomStr : function(lenght){
       var text = "";
       var possible = "abcdefghijklmnopqrstuvwxyz0123456789";
       for( var i=0; i < lenght; i++ )
            text += possible.charAt(Math.floor(Math.random() * possible.length));
            return text;
    },
    work:function(){
       $(".safeLayerTimer").show();
       $(".safeLayer").show();
    },
    endWork:function(){
//        clearTimeout(_systemTimer)
//        _systemTimer = setTimeout(myFunction, 500)
        $(".safeLayerTimer").hide();
        $(".safeLayer").hide();
    },
    doEndWork:function(){

    }


}

function showModal(content, title,height,width)
{
    $( "#dialog-message" ).html("");
    $( "#dialog-message" ).html(content)
    $( "#dialog-message" ).dialog({
      title:title,
      modal: true,
      height:height,
      width:width,
      buttons: {
        Luk: function() {
          $( this ).dialog( "close" );
        }
      }
    });
}
function closeMedal()
{
    $( "#dialog-message" ).dialog( "close" );
}


/*

function handelbar_(jsonData,templateName,returnFunc,returnComponent)
{
    this.jsonData = jsonData;
    this.templateName = templateName;

    this.build = function()
    {
        $.get("http://40.113.94.34/gf2016/views/tph/"+this.templateName+".tph").then(function(dataTemplate) {
            var renderer = Handlebars.compile(dataTemplate);
            var html = renderer(jsonData);
            this.responce = html;
            if(returnComponent == ""){
                eval( returnFunc+"(this)" )
            } else {
              $("#"+returnComponent).html(html)
            }

		});

    };
}
*/

