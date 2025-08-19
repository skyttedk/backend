var tinymce;
var CardCompanyLayout = {
  init:function() {
     this.initHtml();
     this.initEditor();
  },
  initHtml:function(){
        var u = "Let the fun begin"
        var html = `

            <textarea id='cardLayoutTxt'> ${u} </textarea><br>
            <fieldset><legend>Logo</legend></fieldset>

        `;
        $("#cardCompanyLayoutContainer").html(html);
  },
  initEditor:function() {
    // init editor
    tinymce.init({
          selector: 'textarea#cardLayoutTxt',
          plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak code',
          toolbar_mode: 'floating'
    });



  },
  killEditor:function(){
    tinymce.remove("#cardLayoutTxt");
  }


}