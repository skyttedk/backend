// en del af logikken ligger i alfabet-search, skal flyttes hertil ....
AppPcms.present = (function () {
    self = this;
    self.imgPath = "https://system.gavefabrikken.dk//gavefabrikken_backend/views/media/user/";
    self.data;
    self.init =  (data) => {
        this.data = data;
    };
    self.loadMyCreatePresent = () =>{

    }
    self.showList = (data) => {

        this.data = data;
        var rowCount = 0;
        var html = "";
        data.data.forEach((item,index) => {
            html+= '<br><br><div class="row">'
            html+= self.cardMarkup(item)
            html+= '</div><br><br><br><br><hr>';
        } ) ;

       $(".detail-present-content").html(html);
        $('.detail-present').modal('show');

        self.setEvents();
    }
    self.getSimple = (data ) => {
           return  `
           <div class="col-12 ">
            <div class="card budget-card-${pt_price.budget}">
                <!-- Card image -->
                <div class="presentation-view overlay">

                    <img src="https://system.gavefabrikken.dk/fjui4uig8s8893478/${ data.pt_img }" alt="" />
                </div>
                <!-- Card content -->
                <div class="card-body" >
                    <!-- Button -->
                    <a  class="btn btn-primary front-detail make-pdf" data-id=${ data.id }>pdf</a>
                    <a  class="btn btn-primary front-detail add-to-pdf-list" data-id=${ data.id } data-img=${ data.pt_img } >Tilføj</a>
                </div>
               </div>
            </div>  `;

    }


    self.cardMarkup  = (data) => {
//              <a  class="btn btn-primary front-detail make-pdf" data-id=${ data.id }>pdf</a>
  //                  <a  class="btn btn-primary front-detail add-to-pdf-list" data-id=${ data.id } data-img=${ data.pt_img } >Tilf�j</a>
      let pt_price = {};
      let langFile = "_2705";  
      if(_lang == 1 ){
        pt_price = JSON.parse(data.pt_price);
      }
      if (_lang == 4 ) {
        langFile = "_no";
        pt_price = JSON.parse(data.pt_price_no);
      }
      return  `
           <div class="col-12 ">
            <div class="card budget-card-${pt_price.budget}">
                <div>
                    <iframe src="https://system.gavefabrikken.dk/presentation/pdf${langFile}.php?u=1&isSalePreview=${ data.id }" width="100%" height="650" frameBorder="0" style="border:1px solid gray;"></iframe>
                    <a  class="btn btn-primary front-detail make-pdf" data-id=${ data.id }>pdf</a>
                    <a  class="btn btn-primary front-detail add-to-pdf-list" data-id=${ data.id } data-img=${ data.pt_img } >Tilføj</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="float:right; margin-right:15px;">Close</button>
                </div>
               </div>
            </div>  `;
      }
      self.setEvents = () => {

  



        $(".make-pdf").click( async function(){
            message("Opretter pdf af den valgte slide, vent venligt")
            if($(this).html() != "Arbejder.."){
              $(this).html("Arbejder..")
              let id = $(this).attr("data-id");
              let pdf = new AppPcms.pdf;
              let presentation_id = Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7);
              await pdf.make(id,presentation_id);
              SaveToDisk(presentation_id);
              $(this).html("PDF");

            }

        });
        $(".add-to-pdf-list").click(function(){

            let part = {id:$(this).attr("data-id"),title:$(this).attr("data-img")}
             message("Slide tilføjet")
             let img = "https://system.gavefabrikken.dk/fjui4uig8s8893478//"+$(this).attr("data-img");

             let html =   '<li data-id='+$(this).attr("data-id")+' class="presentation-elememt-set" class="ui-state-default">'+
             '<img   src='+img+'><i data-id='+$(this).attr("data-id")+' class="fas fa-trash-alt presentation-elememt-set-trash"></i>'+
             '<i data-id='+$(this).attr("data-id")+' class="fas fa-edit presentation-elememt-set-edit"></i>'+
             '</li>';
             $("#sortable").append(html);
             $(".presentation-elememt-set-trash").unbind( "click" );
             $(".presentation-elememt-set-trash").click(function(){
                  $(this).parent().remove();
                  AppPcmsPresentSetting.remove($(this).attr("data-id"));                  
             })
             $(".presentation-elememt-set-edit").unbind( "click" );
             $(".presentation-elememt-set-edit").click(function(){
                AppPcmsPresentSetting.show($(this).attr("data-id"))
             })


        });
      }
})

