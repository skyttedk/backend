
var voucher =
{
test:function(){
  alert("asdfasdf")
},
assign: function(company_id)
  {
     return new Promise(function(resolve, reject)
      {
          if(company_id == "")  return ;
        $.ajax(
          {
          url: 'index.php?rt=voucher/assign',
          type: 'POST',
          dataType: 'json',
          data:{company_id: company_id}
          }
        ).done(function(res)
          {
                console.log(res);
                resolve(res)
          }
        )
      })
  },
  companyHasVoucher:function(company_id){
      return new Promise(function(resolve, reject)
      {
          if(company_id == "")  return ;
        $.ajax(
          {
          url: 'index.php?rt=voucher/companyHasVoucher',
          type: 'POST',
          dataType: 'json',
          data:{company_id: company_id}
          }
        ).done(function(res)
          {
            resolve(res)
          }
        )
      })
  }

}