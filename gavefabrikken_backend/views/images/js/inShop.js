function addToShop(imgId,id,varerNr,varerNavn)
{
    console.log("her")
   $("#"+id).fadeOut(200,function(){
       ajax({"id":id},"present/makeUnikVariant","");
   });



}
function testtest(response){

    var present = {"id":"3131","name":"","nav_name":"","shop_id":"267","price":"0","price_group":"0","vendor":"uwsDemo","indicative_price":"0"}
    var media = [{"media_path":"q4b4evxdx8qh6dbjq76p","index":0}]
    var logo = [{"logo":"logo/intet.jpg"}]
    var descriptions = [{"id":14792,"language_id":1,"caption":"uwsDemo","short_description":"PHA+dXdzRGVtbzwvcD4=","long_description":"###"},{"id":14793,"language_id":2,"caption":"###","short_description":"###","long_description":"###"},{"id":14794,"language_id":3,"caption":"###","short_description":"###","long_description":"###"},{"id":14795,"language_id":4,"caption":"###","short_description":"###","long_description":"###"},{"id":14796,"language_id":5,"caption":"###","short_description":"###","long_description":"###"}]
    var variant = [{"language_id":"1","feltData":[{"variantId":"1634"},{"variant":"test"},{"variantSub":""},{"variantNr":"test"},{"variantImg":"https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/blank.jpg?lrkik"}],"sortOrder":0},{"language_id":"2","feltData":[{"variantId":"1634"},{"variant":"test"},{"variantSub":"test"},{"variantNr":"test"},{"variantImg":"https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/blank.jpg?lrkik"}],"sortOrder":0},{"language_id":"3","feltData":[{"variantId":"1634"},{"variant":"test"},{"variantSub":"test"},{"variantNr":"test"},{"variantImg":"https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/blank.jpg?lrkik"}],"sortOrder":0},{"language_id":"4","feltData":[{"variantId":"1634"},{"variant":"test"},{"variantSub":"test"},{"variantNr":"test"},{"variantImg":"https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/blank.jpg?lrkik"}],"sortOrder":0},{"language_id":"5","feltData":[{"variantId":"1634"},{"variant":"test"},{"variantSub":"test"},{"variantNr":"test"},{"variantImg":"https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/blank.jpg?lrkik"}],"sortOrder":0}]

      var formData = {
            'present':JSON.stringify(present),
            'media':JSON.stringify(media),
            'logo':JSON.stringify(logo),
            'descriptions':JSON.stringify(descriptions),
            'variant':JSON.stringify(variant)

        };
        ajax(formData,"present/createShopVariant","","");
}

function addToShopResponce(response)
{

   $("#"+response.data.present[0].present_id).hide();

var html="";
html+= '<li class="ui-state-default"  data-id="'+response.data.present[0].present_id+'" data-shopPresentsId="'+response.data.present[0].id+'" data-unik="0" style="margin-left:15px;margin-top:40px; width:150px;"><div class="sort-img presentAdminImg"  style="background-image: url(views/media/user/'+response.data.present[0].present.attributes.first_image_media_path+'.jpg);"> ';
html+= '<img  data-id="'+response.data.present[0].id+'" style="z-index:100; position: relative;  right: -82px; top: -60px;" class="icon" src="views/media/icon/1373253296_delete_64.png"  onclick="removeFromShop(this,\''+response.data.present[0].present_id+'\')" height="25" width="25" />'
html+= '<img  data-id="'+response.data.present[0].present_id+'" style="z-index:100; position: relative;  left: -60px; top: -60px;" class="icon" src="views/media/icon/1373253256_gear_48.png"  onclick="presentsOptions.options(\''+response.data.present[0].id+'\',\''+response.data.present[0].present_id+'\')" height="25" width="25" />'
html+= '<img  data-id="'+response.data.present[0].id+'" style="z-index:100; position: relative;  left: -60px; top: -60px;" class="icon" src="views/media/icon/1373253282_pencil_64.png"  onclick="unikPresentInShop.show(this)" height="25" width="25" /></div>'
html+= "<div style=\"background-color: white; color:black; font-size:10px;font-weight: normal; border-top:1px solid black; \">"+response.data.present[0].present.attributes.present_no+"</div><div style=\"background-color: white; color:black; font-size:10px;font-weight: normal; \">"+response.data.present[0].present.attributes.name+"</div></li>";


    $("#sortable").append(html);
    $( "#sortable" ).sortable();
    $( "#sortable" ).disableSelection();
    updateSelectedPresentsIndex()
}
function removeFromShop(element,id)
{
    var listID = $(element).attr("data-id");

    $("#"+id).show()
    $(element).parent().parent().remove()
    ajax({"shop_id":_editShopID,"present_id":id},"shop/removePresent");
}


function toogleSelectPresentView(action){
    var html_toogle="";
    if(action=="selectGift"){
        html_toogle = "<div><h3 style=\"float: left; margin-left: 5px;\">Valgte gaver i valgshoppen</h3><button onclick=\"toogleSelectPresentView('')\"> Vis gaveliste</button></div><hr />";
        $("#shopPresentSelect").hide();
        $("#selectPresent").show();

    } else {
        html_toogle = "<button onclick=\"toogleSelectPresentView('selectGift')\">Vis valgte gaver</button>";
        $("#selectPresent").hide();
        $("#shopPresentSelect").show();
    }
    $("#toogleSelectPresentView").html(html_toogle);
}

_shopLogo = "";

function setDropOption()
{
    _dropTarget = "forside_tab";
}

function controlDropElemetForsiden(activeElementName)
{

    var obj = JSON.parse(activeElementName);
    _shopLogo = obj.newName;


    var interator = 1;
    var j = $(".dz-preview").length;
    if(j > 1){
        $('.dz-preview').each(function(i, obj) {
              if(interator<j){
                  $(obj).remove();
              }
              interator++;
        });
      }
      $("#selectedLogo").css("background-image", "url(views/media/logo/"+_shopLogo+".jpg)");
     
    //_tempLogoFilename = obj.newName;
}


