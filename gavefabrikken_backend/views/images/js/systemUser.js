//system user
var selectedUserId = "";

var systemUser = {
    selectedUserId:"",
    readAll : function() {
        ajax(null,"present/readAll","systemLog.onLogReplayed","");
    }  ,
    show : function(){
    try {
        $( "#systemUserDialog").dialog('destroy')
        } catch(e) {}
        ajax({},"systemUser/Index","","#content");
    },
    createNew : function(){
        var data = {};
        data['name']          =  $('tr[id^="0"] #systemUserName').val();
        data['username']      =  $('tr[id^="0"] #systemUserUsername').val();
        data['password']      =  $('tr[id^="0"] #systemUserPassword').val();
        data['salespersoncode']      =  $('tr[id^="0"] .salespersoncode').val();
        data['userlevel']     =  1;
        data['active']        =  1;
//        data['active']        =  $('tr[id^="0"] #systemUserActive').val();
        ajax(data,"systemUser/create","systemUser.show","");
    },
    delete : function (id) {
        var r = confirm("Er du sikker, vil du slette brugeren? ");
        if (r == true) {
            var data = {};
            data['id'] =id;
            ajax(data,"systemUser/delete","systemUser.show","");
        }
    },
    save : function(id){
        var data = {};
        data['id'] =id;
        data['name']          =  $('tr[id^="'+id+'"] #systemUserName').val();
        data['username']      =  $('tr[id^="'+id+'"] #systemUserUsername').val();
        data['password']      =  $('tr[id^="'+id+'"] #systemUserPassword').val();
        data['salespersoncode']      =  $('tr[id^="'+id+'"] .salespersoncode').val();
        data['userlevel']     =  1;
        data['active']        =  1;
//        data['active']        =  $('tr[id^="'+id+'"] #systemUserActive').val();
        ajax(data,"systemUser/update","systemUser.show","");
    },
    permissionController : function(id){
        var data = {};
        data['systemuser_id'] = systemUser.selectedUserId;
        data['tap_id'] = id;
        if ($('#tabAccess_'+id).is(':checked')) {
            ajax(data,"tab/createPermission","systemUser.createPermissionResponse","");
        } else {
            ajax(data,"tab/getId","systemUser.removePermission","");
        }


    },
    createPermissionResponse : function(response){


    },

    removePermission : function(response){
        var data = {};
        data['id'] = response.data[0].attributes.id;
        ajax(data,"tab/removePermission","systemUser.removePermissionResponse","");
        //  ajax(data,"tab/removePermission","systemUser.doRemovePermissionResponse","");
    },
    removePermissionResponse : function(){



    },
    showPermission : function(id){
        systemUser.selectedUserId = id;
        $( ".systemUserAccess" ).prop('checked', false);
        $( "#systemUserDialog" ).dialog({
            modal: true,
            width:500,
            height:500,
            buttons: {
                Luk: function() {
                    $( this ).dialog( "close" );
                }
            },
            open : function(){
                systemUser.loadPermission();
            }
        });



    },
    loadPermission : function(){
        data['systemuser_id'] = systemUser.selectedUserId;
        ajax(data,"tab/loadPermission","systemUser.updatePermissionForm","");
    },
    updatePermissionForm : function(response){
        console.log(response)
        $.each( response.data, function( key, value ) {
            var chechboxId = "#tabAccess_"+value.attributes.tap_id;
            $(chechboxId).prop('checked', true);

        });

    }

}

var systemErrorLog = {
    showLatest: function() {
        ajax({},"SystemLog/ErrorLogLatest","","#content");
    },
    showStats: function() {
        if(confirm('Bemærk, dette kald kan være krævende for serveren og bør ikke foretages under høj belastning. Vil du fortsætte?'))
        {
            ajax({},"SystemLog/ErrorLogStats","","#content");
        }
    },
    showObjectFactory: function() {
        $('#content').html('<iframe style="width: 100%; height: 400px;" src="index.php?rt=SystemLog/ObjectFactory"></iframe>')
    },
    showMailStats: function() {
        ajax({},"SystemLog/MailLogStats","","#content");
    },

};

// card batch
var giftcertificateBatch = {
    showDashboard: function() {
        ajax({},"giftcertificate/dashboard","","#content");
    },
    createCertificates: function() {

        var postData = {'expire_date': $('#newcertificates_expire').val(), 'is_delivery': ($('#newcertificates_isdelivery').is(':checked') ? 1 : 0),'is_print': ($('#newcertificates_isprint').is(':checked') ? 1 : 0), 'reservation_group': $('#newcertificates_reservationgroup').val(), 'quantity': $('#newcertificates_amount').val()}

        var quantity = parseInt(postData.quantity)
        if(isNaN(quantity) || quantity <= 0) {
            alert('Der er ikke angivet et antal kort!');
            return;
        }

        $('#giftcertificate_submit').attr('disabled',true)

        $.post('index.php?rt=giftcertificate/createBatch',postData,function(response) {
            $('#giftcertificate_submit').attr('disabled',false)
            if(response.status == 1) {
                alert('Kortene er oprettet, siden vil blive genindlæst.');
                giftcertificateBatch.showDashboard();
            }
            else {
                alert('Der opstod en fejl: '+response.message);
            }

        },'json');
    },
    exportCertificates: function() {

        var postData = {'expire_date': $('#exportcertificates_expire').val(), 'is_delivery': ($('#exportcertificates_isdelivery').is(':checked') ? 1 : 0),'is_print': ($('#exportcertificates_isprint').is(':checked') ? 1 : 0), 'reservation_group': $('#exportcertificates_reservationgroup').val(), 'quantity': $('#exportcertificates_amount').val()}

        var quantity = parseInt(postData.quantity)
        if(isNaN(quantity) || quantity <= 0) {
            alert('Der er ikke angivet et antal kort!');
            return;
        }

        document.location = 'index.php?rt=giftcertificate/exportBatch&data='+encodeURIComponent(JSON.stringify(postData));

    },
    selectCardCell: function(expiredate,resgroup) {
        $('.expiredateselect').val(expiredate);
        $('.reservationgroupselect').val(resgroup);
    }
};

// card batch
var cardshopSettings = {
    showDashboard: function() {
        ajax({},"unit/cardshop/settings/matrix","","#content");
    }
};

//log
var systemLog = {
    replay : function(id){
        var data = {};
        data['id'] =id;
        ajax(data,"SystemLog/read","systemLog.onLogLoaded","");
    },
    onLogLoaded : function(result) {
        var action = result.data.systemlog[0].action;
        var controller = result.data.systemlog[0].controller;
        var jsonData =JSON.parse(result.data.systemlog[0].data);

        $("#request").empty();
        $("#request").html('<pre>'+JSON.stringify(jsonData,null,2)+'</pre>')
        ajax(jsonData,controller+"/"+action,"onResult","");
    }
}
