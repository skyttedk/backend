var serverRoute = "http://40.113.94.34/gf2016/";
var templateRoute = serverRoute + "views/tph/";
var loader = '<img src="views/media/system/ajax-loader.gif"/>';
var isLoading;
/*
User.prototype = {
    constructor: User,
    saveScore:function (theScoreToAdd)  {
        this.quizScores.push(theScoreToAdd)
    },
    showNameAndScores:function ()  {
        var scores = this.quizScores.length > 0 ? this.quizScores.join(",") : "No Scores Yet";
        return this.name + " Scores: " + scores;
    },
    changeEmail:function (newEmail)  {
        this.email = newEmail;
        return "New Email Saved: " + this.email;
    }
}
*/

function submitForm(divID, dataRoute, callBack) {
  var i = 0;
  var formData = new FormData();
  if($('input[type="file"]')[0]) {
    var file_data = $('input[type="file"]')[0].files;
    for(i = 0; i<file_data.length; i++) {
      formData.append("file_"+i, file_data[i]);
    }
  }

  var div = $("#"+divID);
  $.each(div.find("input").serializeArray(), function(i, field)
    {
      formData.append(field.name, field.value);
    }
  );
  loadPage(formData, dataRoute, "", callBack);
}

function loadPage(data, dataRoute, templateName, callBack)
{
  if(!isLoading)
  {
    $("#error").empty();
    setWaitCursor();
    var route = serverRoute+"index.php?rt="+dataRoute;
    this.templateName = templateName;
    this.callBack = callBack;
    var self = this;
    $.ajax(
      {
      url: route,
      type: 'POST',
      data:  data,
      contentType: false,
      processData: false,
      context: this,
      success: function (response) {
          onJsonLoaded(response, self.templateName, self.callBack);
        },
      error: function (xhr, ajaxOptions, thrownError) {
          setDefaultCursor();
          alert(thrownError);
        }
      }
    );
  }
}

function onJsonLoaded(response, templateName, callBack) {
  try {
    var reponseObject = JSON.parse(response.slice(1));
    if(reponseObject.status=="1") {
      loadTemplate(reponseObject.data, templateName, callBack)
    } else {
      alert(reponseObject.message);
      setDefaultCursor();
    }
  } catch (e) {
    $("#error").html(response);
    setDefaultCursor();
  }
}

function loadTemplate(jsonData, templateName, callBack) {
  if(templateName=="")
  {
    setDefaultCursor();
    if(callBack)
        eval(callBack)(jsonData);

  } else {
    $.get(templateRoute+this.templateName+".tph").then(function(dataTemplate)
      {
        var renderer = Handlebars.compile(dataTemplate);
        var html = renderer(jsonData);
        eval(callBack)(html);
        setDefaultCursor();
      }
    );
  }
}

function setWaitCursor() {
  isLoading = true;
  $('html,body').css('cursor', 'wait');
  $(':button').css('cursor', 'wait');
}
function setDefaultCursor() {
  isLoading = false;
  $('html,body').css('cursor', 'default');
  $(':button').css('cursor', 'default');
}






