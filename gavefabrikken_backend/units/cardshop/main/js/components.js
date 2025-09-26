export default class Components {
    constructor() {
        this.SetEvents();
    }
    SetEvents() {
        $(".cardshop-sidebar-right-close").unbind("click").click(
            function(){
                $(".cardshop-sidebar-right").removeClass("zzz")
                 $(".cardshop-sidebar-right").hide("slide", { direction: "right" }, 0);
            }
        )
    }
    OpenRightPanel(title="",html="",page="fuldpage",ontop=false){



        $(".cardshop-sidebar-right-title").html(title);
        $(".cardshop-sidebar-right-content").html(html);
        if(page == "fuldpage"){
            $(".cardshop-sidebar-right").css({ 'width': 'calc(100vw)' });
            $(".cardshop-sidebar-right").show("slide", { direction: "right" }, 500);
        } else {
            $(".cardshop-sidebar-right").css({ 'width': 'calc(100vw * 0.40)' });
            $(".cardshop-sidebar-right").show("slide", { direction: "right" }, 500);
        }
        $(".cardshop-sidebar-right").removeClass('ontop');
        if(ontop==true){
            $(".cardshop-sidebar-right").addClass('ontop');
        }



    }
    CloseRightPanel(){
        $(".cardshop-sidebar-right").hide("slide", { direction: "right" }, 0);
        $(".cardshop-sidebar-right-title").html("");
        $(".cardshop-sidebar-right-content").html("");
    }
    ShowRightPanelStatus(){
      $(".modal-body").html("System is working");
      $("#ModalFullscreenLabel").html("Create new company");
      $(".modal-body").html(tpCompanyForm.createform());
    }
    OpenModal(title="",content="",footer=""){
      $(".modal-body").html("System is working");
      $("#ModalFullscreenLabel").html(title);
      $(".modal-body").html(content);
      $(".modal-footer").html(footer);
      $('#ModalFullscreen').modal('show');
    }

    



    CloseModal(){
      $(".modal-body").html("");
      $("#ModalFullscreenLabel").html("");
      $(".modal-body").html("");
      $(".modal-footer").html("");
      $('#ModalFullscreen').modal('hide');
    }
    token(){
       return this.rand() + this.rand();
    }
    rand(){
       return Math.random().toString(36).substr(2);
    }


}