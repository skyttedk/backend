 appSA.action = (function () {
    self = this;
    self.init = () => {
        alert("action")
    };
    self.readAll =  () => {
         return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"admin/readAll", function(res, status) {
                if(res.status == 0) {  StreamError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }
    self.create =  (id,company,dateStr,time) => {
        if(time == ""){ time = "00:00:00"; }
        let token = Math.random().toString(36).substr(2)+Math.random().toString(36).substr(2);
        temp = dateStr.split("/");
         dateStr = temp[2]+"-"+temp[0]+"-"+temp[1];
         let dateTime = dateStr +" "+ time+":00";
         var data = {
            id:id,
            company_name:company,
            link:token,
            time_to_show:dateTime,
            active:0
         };
         return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"admin/create",data, function(res, status) {
                if(res.status == 0) {  StreamError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })

    }
    self.deleteElement = (id) => {
            return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"admin/deleteElement",{id:id}, function(res, status) {
                if(res.status == 0) {  StreamError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }
    self.updateStatus = (id,active) => {
            return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"admin/updateStatus",{id:id,active:active}, function(res, status) {
                if(res.status == 0) {  StreamError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }






});
