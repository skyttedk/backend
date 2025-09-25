var ptMakePaperPDF = (function() {
    var self = this;

    self.data = {};
    self.init = () => {
        self.setActions();
    }
    self.setActions = () => {
        console.log("papir init")
        $('#ptMakePaperPDF').on('click', self.makePaperPdf);
    }
    self.makePaperPdfUrl = async () => {
        $("#ptMakePaperPDF").hide();
        $("#papirvalgInGen").show();
        let res = await self.loadData();
        let html = 'https://system.gavefabrikken.dk/gavefabrikken_backend/files/papirvalg/'+res.data.filename+'.pdf';
        return html;

    }

    self.makePaperPdf = async () => {
        $("#ptMakePaperPDF").hide();
        $("#papirvalgInGen").show();
        let res = await self.loadData();
        let html = '<a href="https://system.gavefabrikken.dk/gavefabrikken_backend/files/papirvalg/'+res.data.filename+'.pdf" download="https://system.gavefabrikken.dk/gavefabrikken_backend/files/papirvalg/'+res.data.filename+'.pdf" target="_blank">Download</a>';
        $("#downloadPapirPDF").html(html);
        $("#ptMakePaperPDF").show();
        $("#papirvalgInGen").hide();


    }
     self.loadData = async () => {
        return new Promise(function(resolve, reject) {
            $.ajax(
                {
                    url: 'index.php?rt=paperValg/make',
                    type: 'POST',
                    dataType: 'json',
                    data: {shopID: _shopId, languageID: 1 }
                }).done(function(res) {
                resolve(res);
            })

        })
    }



})





