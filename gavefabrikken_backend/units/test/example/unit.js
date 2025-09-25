
class exampleUnit
{

    run(assetsPath, servicePath) {

        this.assetsPath = assetsPath;
        this.servicePath = servicePath;
        var s = this;

        document.querySelector('#exampleButton').addEventListener('click', function (event) {
            $.get(s.servicePath+"addnumbers/"+$("#number1").val()+"/"+$('#number2').val(),function(response) {
                $('#numberresult').val(response);
            });
        });

    }

}

// Fire loader
if(typeof window.exampleUnitReady == "function") {
    exampleUnitReady();
}