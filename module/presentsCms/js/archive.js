 AppPcms.archive = (function () {
    self = this;

    self.init = async () => {
        self.setActions();

    };
    self.loadAll = () => {
               $("#archiveTxt").val("");
               $.post(_ajaxPath+"presentation/getAllArchive",{lang:_lang,userId:_userId}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { AppArchiveArchive.buildUI(res) }
                }, "json");
    };
    self.search = () => {

    txt = $("#archiveTxt").val() == "" ? "" : $("#archiveTxt").val();
               $.post(_ajaxPath+"presentation/searchAllArchive",{txt:txt,lang:_lang,userId:_userId}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { AppArchiveArchive.buildUI(res) }
                }, "json");
    };

    self.setActions = () => {


        $(".menu-archive").click(  function () {
            AppArchiveArchive.loadAll();
        })

        $("#archiveListSearchAll").click(  function () {
            AppArchiveArchive.loadAll();
        })
        $("#archiveListSearch").click(  function () {
            AppArchiveArchive.search();
        })
    };



    self.buildUI = (data) => {
                   $("#archiveList").html("");
                   let html = `<center><table class="pcms" width=90%><tr><th>Oplæg navn</th><th>Link til oplæg</th></tr>`
                   html+= data.data.map(function(item){
                        return `<tr><td><b>${item.name}</b></td><td><a target='blank' href="https://system.gavefabrikken.dk/presentation/slideshow.php?token=${item.id}&nosplash">åben præsentationen</a></td><td><button data-name="${item.name}" data-id="${item.id}" class="archiveCreatePresentationCopy">Opret din egen kopi</button></td></tr>
                        `;
                    }).join('')+"</table></center>";
                    $("#archiveList").html(html);


        $(".archiveCreatePresentationCopy").unbind( "click" ).click(async function(){
            let id = $(this).attr("data-id");
            let name = "copy of "+$(this).attr("data-name");
            AppPcmsPresentation.copy(name,id);

        })

    }



 })