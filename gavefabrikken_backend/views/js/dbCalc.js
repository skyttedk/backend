
var dbCalcMain = (function (targetHtml)
	{
		var self = this;
		self.targetHtml = targetHtml;
        self.shopId =  _shopId;
        self.data;
        self.shopData;

		self.init = async () => {

			self.data = await self.loadData();
            self.shopData = await self.loadShopData();
            self.insetShopData();
            self.buildFront();
            self.setEvents();

		}
		self.loadData = () => {
			return new Promise(resolve =>
				{
                    $.ajax(
						{
						url: 'index.php?rt=dbcalc/getDbCalcValgshop',
						type: 'POST',
						dataType: 'json',
						data: {shopID:self.shopId}
						}
					).done(function(res) {
                            resolve(res);
                        }
					)
				}
			);
		};
        // getShopData
        self.loadShopData = () => {
			return new Promise(resolve =>
				{
                    $.ajax(
						{
						url: 'index.php?rt=dbcalc/getShopData',
						type: 'POST',
						dataType: 'json',
						data: {shopID:self.shopId}
						}
					).done(function(res) {
							resolve(res);
                        }
					)
				}
			);
        };

        // edit option menu
        self.setEvents = () => {
            $(".dbcalc-option-edit").unbind("click").click(
                function(){
                    self.setEditMode( this );
                }
            );
        };
        self.updateOptions = (ele) => {
            var targetEle = $(ele).attr("data-id");
            var cancelElement =  $(ele).next();

            var data = {};
            var value = $("#"+targetEle).val();
            switch(targetEle) {
              case "dbcalc-saleperson":
                    data = {"saleperson":value};
              break;
              case "dbcalc-budget":
                    value = value*1;
                    if(!Number.isInteger(value*1)){
                      alert("Det indtastede er ikke et hel tal!")
                      $("#"+targetEle).val(0);
                      return;
                    }
                    data = {"dbcalc_budget":value};
              break;
              case "dbcalc-stadardgift":
                    data = {"dbcalc_standard":value};
              break;
            }

            data["id"] = self.shopId;
            $.ajax(
                {
                    url: 'index.php?rt=dbcalc/updateOptions',
                    type: 'POST',
                    dataType: 'json',
                    data: data
                }
            ).done(function() {
                   self.cancelEditMode(cancelElement);
                }
            )


        }


        // Front-end
        self.insetShopData = () => {
            var sd = self.shopData.data.shop[0];
            var sp = self.data.data.standardPresent[0]
            $("#dbcalc-saleperson").val(sd.saleperson);
            $("#dbcalc-budget").val(sd.dbcalc_budget);
            $("#dbcalc-stadardgift").val(sd.dbcalc_standard);
            $("#dbcalc-stadardgift-name").html(sp.description);


        }

        self.setEditMode = (ele) => {
            var inputID = $(ele).attr("data-id");
            $("#"+inputID ).prop("disabled", false);
            $(ele).html("Opdatere")
            $(ele).after( ' <button class="dbcalc-option-cancel" data-id="'+inputID+'">Cancel</button>');

            $(ele).unbind("click").click(
                function(){
                    self.updateOptions( this );
                }
            );
            $(".dbcalc-option-cancel").unbind("click").click(
                function(){
                    self.cancelEditMode( this );
                }
            );

        }
        self.cancelEditMode = (ele) => {
            var inputID = $(ele).attr("data-id");
            $("#"+inputID ).prop("disabled", true)
            var actionElement = $(ele).prev();
            actionElement.html("Edit");
            $(ele).remove();
            actionElement.unbind("click").click(
                function(){
                    self.setEditMode( this );
                }
            );

        }



		self.buildFront = () => {
            var sd = self.shopData.data.shop[0];
            var totalDB = 0;
            var totalAntal = 0;
            var budget = sd.dbcalc_budget*1;
            alert(budget)
            var sp = self.data.data.selectedPresent;
            var html = `<table class="customTable" width=100%>
            <tr><th>Varenr</th><th>Navn</th><th>Model</th><th>Standart pris</th><th>Antal</th><th>DB pr. stk.</th><th>DB</th></tr>
            `;
            for (let i = 0; i < sp.length; i++) {
                var sum_standard = sp[i].sum_standard_cost*1;
                var antal = sp[i].c_order*1;
                var db =  budget -  sum_standard;
                var dbSum = db * antal;
                totalDB += dbSum;
                totalAntal+= antal;
                html += `
                <tr>
                    <td>${sp[i].model_present_no}</td>
                    <td>${sp[i].model_name}</td>
                    <td>${sp[i].model_no}</td>
                    <td>${sp[i].sum_standard_cost}</td>
                    <td>${sp[i].c_order}</td>
                    <td>${db}</td>
                    <td>${dbSum}</td>
                </tr>
                `;
			}
            // standard

            var standardPresent = self.data.data.standardPresent[0];
            var totalPresentsCount =  self.data.data.totalPresentInShop[0].total * 1;
            var standardPresentCount = totalPresentsCount-totalAntal;
            var standardPresentDB =  budget - standardPresent.standard_cost*1;
            var standardPresentDBTotal = standardPresentCount* standardPresentDB;
            totalDB+= standardPresentDBTotal;
            var htmlStandardPresent = `
                <tr>
                        <td>${sd.dbcalc_standard }(Standard gaven)</td>
                        <td>${standardPresent.description}</td>
                        <td></td>
                        <td>${standardPresent.standard_cost}</td>
                        <td>${standardPresentCount}</td>
                        <td>${standardPresentDB}</td>
                        <td>${standardPresentDBTotal}</td>
                </tr>`;



             html += htmlStandardPresent



            html += `
               <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><b>Total DB</b></td>
                    <td><b>${totalDB}</b></td>
                </tr>

            </table>`;





            $("#dbcalc-data").html(html)



        }
	}
);



