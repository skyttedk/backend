        <div style="text-align: right; margin-top:-30px; margin-right: 40px;">
            <button id="finishBtn" style="background-color:red;" onclick="shopSettings.setShopFinish()">AFSLUT SHOPPEN</button>
        </div><fieldset><legend><b>Shop indstiller</b></legend>

        <div style="width:98%;float:left;padding:2px; text-align: left;">
            
             <div id="finish_shop" style="color:red; font-weight: bold;"></div>


            <table width=900 border=0>
            <tr>
            <td  width=40%>
                <div><label style="margin-right:14px;">SHOP AKTIV:</label><input id="closeShop"  type="checkbox" onchange="shopSettings.updateShopIsActive(this)"></div> <br  />
                <div ><label style="margin-right:10px;">PR&Oslash;VE SHOP</label><input type="checkbox" id="testShop" onchange="shopSettings.updateShopIsTryout(this)" /></div>
            </td>
            <td  width=60%>
                <fieldset><legend style="color:red;"><b>Shop N&oslash;dstop</b></legend>
                <div style="width:400px;float:left;padding:2px; text-align: left;">
                    <div><label>Kryds sat ved n&oslash;dstop: </label><input id="nodStopBtn"  type="checkbox" onchange="shopSettings.updateShopIsBlock(this)" /></div><br />
                    <textarea cols="50" rows="5" id="nodStopText">SYSTEMET ER UNDER OPDATERING</textarea>
                </div>
                </fieldset>
            </td>

            </tr>
            </table>

        </div>

        </fieldset>

         <fieldset id="langliste" > <legend><b>Sprog indstillinger</b></legend>
         <table width=500 border=0 style="float:left">
         <tr>
            <td height=30 width=60><label>Dansk:</label></td><td><input  class="lang"  type="checkbox" id="lang_dk" onchange="shopSettings.updateLanguage()" /></td>
            <td width="40"></td>
            <td height=30 width=70><label>Engelsk:</label></td><td><input class="lang" type="checkbox" id="lang_eng"  onchange="shopSettings.updateLanguage()" /></td>
            <td width="40"></td>
            <td height=30 width=60><label>Norsk:</label></td><td><input class="lang" type="checkbox" id="lang_no"  onchange="shopSettings.updateLanguage()" /></td>
            <td width="40"></td>
            <td height=30 width=65><label>Svensk:</label></td><td><input class="lang" type="checkbox" id="lang_se"  onchange="shopSettings.updateLanguage()" /></td>
            <td width="40"></td>
            <td height=30 width=50><label>Tysk:</label></td><td><input class="lang" type="checkbox" id="lang_de"  onchange="shopSettings.updateLanguage()" /></td>
         </tr>
         </table>
        </fieldset>


        <fieldset><legend><b>Periode</b></legend>

            <div style="width:800px;float:left;padding:2px;  text-align: left;">
            <table width=800 border=0>
            <tr>
            <td> </td><td><label>Mails der skal advares om shop lukning</label></td>
            </tr>


            <tr>
             <td><label for="shopFrom2">Start dato</label>
            <input type="text"  style="width:100px" id="shopFrom2" name="from2"  />
            <label   for="shopTo2">Slut dato</label>
            <input type="text"  style="width:100px" id="shopTo2" name="to2"  />
             </td>

             <td><textarea cols="30" rows="3" id="periodeMail"></textarea></td>

            </tr>
             </table>


            </div>
        </fieldset>
        <fieldset><legend><b>Generelle indstillinger</b></legend>
        <div style="width: 40%; display: inline-block;">
        <table width=100% border=0 >
        <tr>
            <td width=300 height=30>Leveringsdato <span style="font-size: 10px;"> (Vises på kundens kvittering)</span></td>
            <td><input style="margin-left:10px;" id="showDeleveryDateOnReceipt"  type="text"  /></td>
        </tr>

        <tr>
            <td height=20>Slå kundeændringer fra</td>
            <td><input style="margin-left:10px;" type="checkbox" id="allowCustomerToMakeChange"  /></td>
        </tr>
        <tr>
            <td height=20>Aktivere tilmelding til gaveklubben</td>
            <td><input style="margin-left:10px;" type="checkbox" id="activateGaveklubben"  /></td>
        </tr>
        <tr>
            <td height=20>Vis pris på gaverne</td>
            <td><input style="margin-left:10px;" type="checkbox" id="showPresentPrice"  /></td>
        </tr>


        </table>
        </div>
        <div style="width: 5%; display: inline-block; "></div>
        <div style="width: 40%; display: inline-block; ">
        <table width=100% border=0 >
        <tr>
            <td width=250 height=30><button id="shop-voucher">Assign Voucher</button></td>

        </tr>
        </table>
        <table width=100% border=0 >
        <tr>
            <td width=250 height=15>Aktivere default design (Logo nederst / gave)</td>
            <td><input style="margin-left:10px;" type="checkbox" id="layoutDefault" class="gf-layout" /></td>
        </tr>
        <tr>
            <td width=250 height=15>Aktivere 2019 design (Det r&oslash;de)</td>
            <td><input style="margin-left:10px;" type="checkbox" id="newLoginDesign" class="gf-layout" /></td>
        </tr>
        <tr>
            <td width=250 height=15>Aktivere 2021 login (guld design) </td>
            <td><input style="margin-left:10px;" type="checkbox" id="layoutGuld" class="gf-layout" /></td>
        </tr>
        </table>
        </div>

     <br />

        </fieldset>

        <fieldset><legend><b>Mail til kunder der mangler gavevalg</b></legend>
        <div style="width:600px;float:left;padding:2px; text-align: left;">
             <div style="width:300px;float:left"><label>Dato hvor der sendes: <span style="font-size: 10px;"> (blank, ingen mail sendes)</span> </label></div><div style="float:left;width:200px;height:30px;"><input id="sendMailToCustomer"  type="text" onchange="shopSettings.updateMailToCustomer(this)"  /></div>

        </div><br />

        </fieldset>