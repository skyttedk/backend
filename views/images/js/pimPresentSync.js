        //



function syncShopPresent(shopID)
{

        $("#PimShopPresentSync").html('<div><input autocomplete="off" class="pim search" id="search" type="text" placeholder="Search" /></div>');
        addSearch();
        $.ajax(
		{
		    url: 'index.php?rt=pimShopPresentSync/loadSyncPresentOnShop',
					type: 'GET',
					dataType: 'json',
					data: {shopID: shopID}
					}
				).done(function(res) {

                    res.data.map( prop => {
                        var p = new PimShopPresentSync()
                        p.syncSinglePresent(prop.present_id)
                    })
                    openSyncDialog()


					}
				)

}
 var searchTimer;
function addSearch()
{
  $(".pim.search").unbind("keyup").keyup(function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function(){
                doSearch()
            }, 300);
        });
}
function doSearch()
{
    $(".pim.shop-title").parent().parent().hide();
        var searchTxt = $(".pim.search").val().toLowerCase();
        if(searchTxt == ""){
            $(".pim.shop-title").parent().parent().show();
            return;
        }
        $(".pim.shop-title").each(function( index ) {
           let title  = $( this ).text().toLowerCase()

           if(title.indexOf(searchTxt) !== -1){
               console.log(title)
                $( this ).parent().parent().show();
           }
        });
}





function openSyncDialog()
{

           $( "#dialog-sync" ).dialog({
              height: 800,
              width: 700,
              modal: true,
             buttons: {
            "LUK": function() {
              $( this ).dialog( "close" );

            }}
            });
//           $("#PimShopPresentSync").html("sadfas")



}




class PimShopPresentSync {
	constructor() {
		this.data =[];
	   //	this.run();

	}

    addSearch()
    {
        this.template = new PimPresentSyncLayout();
        $("#PimShopPresentSync").append(this.template.searchMenu())
    }

    async syncSinglePresent(presentID)
    {
        this.data = await this.loadData(presentID);
        this.template = new PimPresentSyncLayout();
       // $("#PimShopPresentSync").append(this.template.searchMenu())
        this.layout();
        this.setEvent();
    }



	loadData(presentID) {
		return new Promise(async function (resolve, reject)
			{
				$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/loadSyncStatus',
					type: 'GET',
					dataType: 'json',
					data: {id: presentID}
					}
				).done(function(res) {
						resolve(res);
					}
				)
			}
		)
	}
    layout() {

        $("#PimShopPresentSync").append(this.template.main(this.data))
    }
    setEvent(){
        var self = this;

        // vis gaven detajler
        $(".pim.shop-title").unbind("click").click(function () {
            $("#pim-content-"+$(this).attr("data-id")).slideToggle()
        });
        // total opdatering af gave
        $(".syncStatus-0").unbind("click").click(function () {
             event.stopPropagation();
            if($(this).hasClass("lock") ) {
                if(!confirm("Vi vil opdatere hele gaven, selvom synkroniseringen er deaktivet")){
                  alert("Gaven blev IKKE synkroniseret")
                  return;
                }
            }

            self.updatePresent($(this).attr("data-id"));
        });
        $(".pim.sync.presentation.status-0").unbind("click").click(function () {
            if($(this).hasClass("lock") ){
                if(!confirm("Vi vil opdatere, selvom synkroniseringen er deaktivet")){
                  return;
                }
            }
            $(this).removeClass("status-0").addClass("status-1");
            self.updatePresentation($(this).attr("data-id"),$(this).attr("field-name"));
        });





        $(".pim.sync.gmo.status-0").unbind("click").click(function () {
            if($(this).hasClass("lock") ){
                if(!confirm("Vi vil opdatere, selvom synkroniseringen er deaktivet")){
                  return;
                }
            }
            $(this).removeClass("status-0").addClass("status-1");
            self.updateGmo($(this).attr("data-id"));
        });
        $(".pim.sync.logo.status-0").unbind("click").click(function () {
            if($(this).hasClass("lock") ){
                if(!confirm("Vi vil opdatere, selvom synkroniseringen er deaktivet")){
                  return;
                }
            }
            $(this).removeClass("status-0").addClass("status-1");
            self.updateLogo($(this).attr("data-id"));
        });
        $(".pim.sync.images.status-0").unbind("click").click(function () {
            if($(this).hasClass("lock") ){
                if(!confirm("Vi vil opdatere, selvom synkroniseringen er deaktivet")){
                  return;
                }
            }
            $(this).removeClass("status-0").addClass("status-1");
            self.updateImages($(this).attr("data-id"));
        });
        $(".pim.sync.text.status-0").unbind("click").click(function () {
            if($(this).hasClass("lock") ){
                if(!confirm("Vi vil opdatere, selvom synkroniseringen er deaktivet")){
                  return;
                }
            }
            $(this).removeClass("status-0").addClass("status-1");
            var postData = {
                id:$(this).parent().parent().attr("data-id"),
                lang:$(this).parent().parent().attr("lang-id"),
                field:$(this).attr("field-name")
            }
            self.updateTextField(postData);   //singleTextUpdate
        });
        $(".pim.newModel").unbind("click").click(function () {
            if($(this).hasClass("lock") ){
                if(!confirm("Vi vil opdatere, selvom synkroniseringen er deaktivet")){
                  return;
                }
            }
            $(this).removeClass("status-0").addClass("status-1");
            var postData = {
                id:$(this).attr("data-id"),
                orgmodel:$(this).attr("org-model-id")
            }
            self.copyModelFromMaster(postData);
        });

        $(".pim.sync.model.status-0").unbind("click").click(function () {
            if($(this).hasClass("lock") ){
                if(!confirm("Vi vil opdatere, selvom synkroniseringen er deaktivet")){
                  return;
                }
            }
            $(this).removeClass("status-0").addClass("status-1");
            var postData = {
                id:$(this).parent().parent().attr("present-id"),
                lang:$(this).parent().parent().attr("lang-id"),
                field:$(this).attr("field-name"),
                orgmodel:$(this).parent().parent().attr("org-model-id")
            }
            self.updateModelField(postData);
        });
    /*
      $(".pim.search").unbind("keyup").keyup(function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function(){
                self.search()
            }, 300);
        });
     */




    }
    openSyncDialog(){
        var html ="<p>Hej</p>"
        $("#dialog-sync").html(html).dialog();
    }

    feildIsUpdatet(ele){

    }


    search(){
        $(".pim.shop-title").parent().hide();
        var searchTxt = $(".pim.search").val().toLowerCase();
        if(searchTxt == ""){
            $(".pim.shop-title").parent().show();
            return;
        }
        $(".pim.shop-title").each(function( index ) {
           let title  = $( this ).text()

           if(title.indexOf(searchTxt) !== -1){
               console.log(title)
                $( this ).parent().show();
           }
        });
    }


    updatePresentation(id,target){
        var self = this;
		$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/updatePresentation',
					type: 'GET',
					dataType: 'json',
					data: {id: id,target:target}
					}
				).done(function(res) {
						console.log(res)
                        self.updatePresentFinish()
					}
				)
    }

    copyModelFromMaster(postData){
        var self = this;
		$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/copyModelFromMaster',
					type: 'GET',
					dataType: 'json',
					data: postData
					}
				).done(function(res) {
                        self.updateAllPresentFinish()
					}
				)
    }

    updateTextField(postData){
        var self = this;
		$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/singleTextUpdate',
					type: 'POST',
					dataType: 'json',
					data: postData
					}
				).done(function(res) {
						console.log(res)
                        self.updatePresentFinish()
					}
				)
    }

    updateModelField(postData){
        var self = this;
		$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/updateModelField',
					type: 'POST',
					dataType: 'json',
					data: postData
					}
				).done(function(res) {
						console.log(res)
                        self.updatePresentFinish()
					}
				)
    }

    updateImages(id,field,languagesID){
        var self = this;
		$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/singleImagesUpdate',
					type: 'GET',
					dataType: 'json',
					data: {id: id}
					}
				).done(function(res) {
						console.log(res)
                        self.updatePresentFinish()
					}
				)
    }

    updateLogo(id){
        var self = this;
		$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/singleLogoUpdate',
					type: 'GET',
					dataType: 'json',
					data: {id: id}
					}
				).done(function(res) {
						console.log(res)
                        self.updatePresentFinish()
					}
				)
    }
    updateGmo(id){
        var self = this;
		$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/singleGmoUpdate',
					type: 'GET',
					dataType: 'json',
					data: {id: id}
					}
				).done(function(res) {
						console.log(res)
                        self.updatePresentFinish()
					}
				)
    }

    updatePresent(id){
        var self = this;
		$.ajax(
					{
					url: 'index.php?rt=pimShopPresentSync/updatePresent',
					type: 'GET',
					dataType: 'json',
					data: {id: id}
					}
				).done(function(res) {
						console.log(res)
                        self.updateAllPresentFinish()
					}
				)
    }

    updatePresentFinish(id){
           
    }

    updateAllPresentFinish()
    {
        syncShopPresent(_editShopID)
    }
}
class PimPresentSyncLayout {
   constructor() {   }
   main(props){
       console.log(props)
          return  ``+
            props.data.map( prop => {
                let lock = prop.lock == 1 ? "lock":"";

                if(prop.name.length > 80) prop.name = prop.name.substring(0,80)+"...";

                return `<div class="pim container"  id="pim-container-${prop.id}">
                            <div style='width:100%; height:25px;'>
                                <div  style=" width:100%; height:25px" class="pim shop-title ${lock} inline" data-id="${prop.id}">${prop.name} <div class="pim noInsyncState ${lock} syncStatus-${prop.isInSync}" data-id="${prop.id}" ></div>  </div>
                            </div>
                          <div class="pim sync-status" data-id="${prop.id}" id="pim-content-${prop.id}">
                                <table class="pim-table">
                                    <tr ><td>Gave med omtanke</td><td colspan="3"><div class="pim sync gmo status-${prop.sync.oko_present} ${lock}" field-name="gmo" data-id="${prop.id}"></div></td></tr>
                                    <tr ><td>Logo</td><td colspan="3"><div class="pim sync logo status-${prop.sync.present} ${lock}" field-name="logo" data-id="${prop.id}"></div></td></tr>
                                    <tr data-id="${prop.id}"><td>Billeder valgshop</td><td colspan="3"><div data-id="${prop.id}" class="pim sync images status-${prop.sync.presentMedia} ${lock}" field-name="img"></div></td></tr>
                                    <tr><th>Sprog</th><th>Overskrift</th><th>Kort tekst</th><th>Lang tekst</th></tr>`+
                                        prop.sync.presentDescription.map( ele => {
                                            return `
                                                <tr lang-id="${ele.language_id}" data-id="${prop.id}">
                                                    <td>${ele.language}</td>
                                                    <td><div  class="pim sync text status-${ele.caption} ${lock}" field-name="caption"></div></td>
                                                    <td><div class="pim sync text status-${ele.short_description} ${lock}" field-name="short_description"></div></td>
                                                    <td><div class="pim sync text status-${ele.long_description} ${lock}" field-name="long_description"></div></td>
                                                </tr> `
                                        }).join('') +`
                                    <tr >
                                   </table>
                                   <div><br><b>---- MODELLER / VARIANTER -----</b><br></div>
                                   `+
                                         prop.sync.model.map( eleModel => {
                                            if(eleModel.is_new == false){
                                             return `
                                                   <fieldset><legend>${eleModel.model_name}</legend><div>Varenr: ${eleModel.itemnr}</div><table class="pim-table"><tr><th>Sprog</th><th>Navn</th><th>Variant / farve </th><th>varenr</th><th>Billede</th></tr>` + eleModel.status.map( eleStatus => {
                                                    return `
                                                    <tr lang-id="${eleStatus.language_id}" present-id="${prop.id}" org-model-id="${eleModel.id}">
                                                    <td>${eleStatus.language}</td>
                                                    <td><div  class="pim sync model ${lock} status-${eleStatus.model_name.isSync}" field-name="model_name"></div></td>
                                                    <td><div class="pim sync model ${lock}  status-${eleStatus.model_no.isSync}" field-name="model_no"></div></td>
                                                    <td><div class="pim sync model ${lock} status-${eleStatus.model_present_no.isSync}" field-name="model_present_no"></div></td>
                                                    <td><div class="pim sync model ${lock} status-${eleStatus.media_path.isSync}" field-name="media_path"></div></td>
                                                    </tr>
                                                    `;
                                                }).join('') + `</table> </fieldset>  `

                                            } else {
                                               return `<fieldset><legend><span style='color:red;'>NY MODEL</span></legend><div>
                                                    <table class="pim-table">
                                                        <tr><td>Navn</td><td>${eleModel.data[0].model_name}</td></tr>
                                                        <tr><td>Variant / farve</td></td><td>${eleModel.data[0].model_no}</td></tr>
                                                        <tr><td>Varenr.:</td><td>${eleModel.data[0].model_present_no}</td></tr>
                                                        <tr><td></td><td><button class="pim newModel ${lock}" data-id=${prop.id} org-model-id="${eleModel.data[0].model_id}">Opret Model</button></td></tr>
                                                    </table>

                                               </div></fieldset> `
                                            }


                                        }).join('') +`
                                        <div><br><b>---- Pr&oelig;sentation -----</b><br></div>
                                        <table class="pim-table">

                                            <tr ><td>Store billede</td><td width=25><div class="pim sync presentation status-${prop.sync.presentation.pt_img} ${lock}" field-name="pt_img" data-id="${prop.id}"></div></td></tr>
                                            <tr ><td>Lille billede</td><td width=25><div class="pim sync presentation  status-${prop.sync.presentation.pt_img_small} ${lock}" field-name="pt_img_small" data-id="${prop.id}"></div></td></tr>
                                            <tr ><td>Skabelon</td><td width=25><div class="pim sync presentation  status-${prop.sync.presentation.pt_layout} ${lock}" field-name="pt_layout" data-id="${prop.id}"></div></td></tr>
                                            <tr ><td>Kunhos</td><td width=25><div class="pim sync presentation  status-${prop.sync.presentation.kunhos} ${lock}" field-name="kunhos" data-id="${prop.id}"></div></td></tr>
                                            <tr ><td>Pris dk</td><td width=25><div class="pim sync  presentation status-${prop.sync.presentation.pt_price} ${lock}" field-name="pt_price" data-id="${prop.id}"></div></td></tr>
                                            <tr ><td>Pris Norge</td><td width=25><div class="pim sync presentation   status-${prop.sync.presentation.pt_price_no} ${lock}" field-name="pt_price_no" data-id="${prop.id}"></div></td></tr>
                                        </table>




                          </div></div> `
            }).join('')
   }

   searchMenu(){
        return '<div><input autocomplete="off" class="pim search" id="search" type="text" placeholder="Search" /></div>';
   }
}

