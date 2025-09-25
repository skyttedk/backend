
var dbCalcCardMain = (function (targetHtml)
    {
        var self = this;
        self.targetHtml = targetHtml;
        self.data;
        self.init = async () => {
            self.data = await self.loadData();
            let html =   await self.buildTemplate()
            $(self.targetHtml).html(html);
            self.setEvent();
        }
        self.loadData = () => {
        			return new Promise(resolve =>
				{
                    $.ajax(
						{
						url: 'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=dbcalc/getShopSettings',
						type: 'POST',
						dataType: 'json',
						data: {}
						}
					).done(function(res) {
                            resolve(res);
                        }
					)
				}
			);


        };
        self.setEvent = () => {
          let me = self;
          $(".cs-card").on("keyup", function(){
             me.calcCard($(this).attr("data-id"), $(this).val(),$(this).attr("data-price"),$(this).attr("data-db") )
          })
        }
        self.calcCard = (id,count,price,db) => {

            let totalPrise = count*(price/100);
            let totalDb =  count*(db*1);

            $("#csSum_"+id).html(totalPrise);
            $("#csSumDB_"+id).html(totalDb);
            self.updateCalc();
        }
        self.updateCalc = () =>{
            var totalSum = 0;
            var totalDB = 0;
            $('.itempris').each(function(i, obj) {
                totalSum+=$(obj).html()*1;
            });
            $('.itemdb').each(function(i, obj) {
                totalDB+=$(obj).html()*1;
            });
            $("#csSum").html(totalSum);
            $("#csSumDB").html(totalDB);
        }


        ///
        self.buildTemplate = () => {
            let me = self;
            return new Promise(resolve =>
				{
                let html = `<table><tr><th>Kort</td><td>Antal<td >Total pris</td><td>Total DB</td></td></tr> `
                 html+=  me.data.data.map((i) => {
                        return  `<tr><td>${i.name}</td><td><input data-id="${i.id}" data-price="${i.card_price}" data-db="${i.card_db}" class="cs-card" type="number" /></td><td class="itempris"  id="csSum_${i.id}">0</td><td class="itemdb" id="csSumDB_${i.id}">0</td></tr> `

                    }).join('')+`<tr><td></td><td></td><td id="csSum" style="font-weight:bold">0</td><td id="csSumDB" style="font-weight:bold">0</td></tr></table>`
                    resolve(html);


				}
			);

        }




    }
);



