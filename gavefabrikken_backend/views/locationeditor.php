<style>

  .location-placeholder { width: 100%; padding: 5px; height: 20px; background: #eeeeee; }

</style>

<div style="text-align: left;">
<br>

<table style="width: 100%; border: none; clear: both;">
  <tr>
      <td valign=top style="width: 40%;">
      
        <h3>Rapporter</h3>
      
      <table style="width: 400px;">
        <tr>
             <td valign=top>
                  
              <div style="padding: 10px;">
                <button type="button" onClick="downloadFordelingRapport()">Hent shop fordelingsliste</button>
              </div>
              
              <div style="padding: 10px;">
                <button type="button" onClick="downloadSumRapport()">Hent shop sumliste (pdf)</button>
              </div>

             <div style="padding: 10px;">
                 <button type="button" onClick="downloadSumRapportExcel()">Hent shop sumliste (excel)</button>
             </div>
              
             <div style="padding: 10px;">
                 <button type="button" onClick="downloadLabelRapport()">Hent shop labelliste</button>
             </div>

                 <?php if(router::$systemUser != null && router::$systemUser->language == 4) { ?>
                 <div style="padding: 10px;">
                     <button type="button" onClick="downloadLabelRapportWItemno()">Hent labelliste m. varenr</button>
                 </div>
                 <?php } ?>

                 <div style="padding: 10px;">
                     <button type="button" onClick="downloadFordelingRapportCS()">Hent shop plukliste</button>
                 </div>

                 <?php /*
                 <div style="padding: 10px;">
                    <button type="button" onClick="downloadKlubMails()">Hent mail liste (ja til klub)</button>
                </div>*/ ?>



                 <div style="padding: 10px;">
                     <button type="button" onClick="downloadLeveringsAdresser()">Hent leveringsadresser</button>
                 </div>

                 <div style="padding: 10px;">
                     <button type="button" onClick="downloadLeveringsAdresserImport()">Hent adresseimport liste</button>
                 </div>


                 <div style="padding: 10px;">
                     <button type="button" onClick="downloadGaveliste()">Hent gaveliste</button>
                 </div>

                 <div style="padding: 10px;">
                     <button type="button" onClick="downloadProformaFaktura()">Hent proformafaktura</button>
                 </div>
                 


                 <div style="padding: 10px; display: none;" class="privatedeliverybutton">
                     <button type="button" onClick="downloadPartialPrivateDelivery()">Hent privatleveringsliste</button><br>
                     <button type="button" onClick="downloadPartialPrivateDeliverySum()">Hent privat sumliste</button>
                     <script>

                         //|| _editShopID == 4346
                         if(_editShopID ==4163 ) {
                             $('.privatedeliverybutton').show();
                         }

                         function downloadPartialPrivateDelivery() {
                             document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+'&type=privatlevering';
                         }

                         function downloadPartialPrivateDeliverySum() {
                             document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+'&type=privatleveringsum';
                         }


                     </script>
                 </div>

             </td>
             <td valign=top>
             
                <div>
                  <b>Bruger-felter i fordelingsliste</b>
                  <div id="reportuserfields"></div>
                </div><br>
                
             </td>
        </tr>
      </table>  <br>
      
        <h3>Lokationer og addresser</h3>
               
        <div>
  
      	<div>
      		Adresse type:   <br>
      		<select id="locationType" onChange="updateAddressType()">
      			<option value="0">0: Adresse ikke valgt</option>
      			<option value="1">1: Lokation i dropdown felt (ikke hele adresser)</option>
      			<option value="2">2: Lokation i fritekst felt (ikke hele adresser)</option>
      			<option value="3">3: Hele adresse i felt (brug ikke lokationer)</option>
            <option value="4">4: Angiv 1 adresse for alle brugere</option>
      		</select>
      	</div><br>
        <div class="locfield locfield1 locfield2 locfield3">
      	  Vælg lokationsfelt:<br>
          <select id="locationAttributeSelect"></select>
          <button class="locfield locfield1 locfield2" type="button" onclick="loadAttributeTexts()">hent lokationer</button>
      	</div>
      </div>
      
      <div id="locAvailableLocations" class="locfield locfield1 locfield2" style="display: none; margin-top: 15px; margin-bottom: 15px;">
          <b>Lokationer der ikke er tilknyttet addresse</b>
          <div style="background: white; border: 1px solid #E0E0E0;">
            <div class="noloc" style="padding: 10px; text-align: center;">Ingen lokationer mangler tildeling..</div>
            <div class="locationsort" style="width: 100%; min-height: 25px;"></div>
            <div style="border-top: 1px solid #e0e0e0; background: #FAFAFA; padding-top: 4px;padding-bottom: 4px; margin-top: 4px; font-size: 11px;"><b>vælg:</b> <a href="#" onClick="rowCheckSelectAll(this)">alle</a> / <a href="#" onClick="rowCheckSelectNone(this)">ingen</a> | 
                  <b>handling:</b> <a href="#" onClick="rowCheckMoveToAvailable(this)">fjern valgte fra adresser</a>
            </div>
          </div>
          
          <div style="text-align: center;"><i>træk lokationer til adresser i højre side, eller brug afkrydsning og hent / fjern for at flytte flere ad gangen..</i></div>
      </div>
      <div>
      <div id="partialDeliveryContainer" style="display: none;">
          <hr>

          <input id="partialDelivery" onclick="rapport.setPartialDeliveryList()" type="button" value="Opret dellevering" />
          <div>Oprettede deleleveringer:</div>
          <div id="partialDeliveryList" style="width: 200px">

          </div>

      </div>

      </div>

      
      </td>
      <td valign=top style="width: 2%;">&nbsp;</td>
      <td valign=top style="width: 58%;">
      
      
        <div style="width: 100%; height: 700px; overflow-y: auto; overflow-x: hidden;">
        
          <div class="locfield locfield1 locfield2 locfield4">
              <div style="float: right; " class="locfield locfield1 locfield2">
                  <button type="button" onClick="showAdresseImport()">indlæs adresser</button>
                  <button type="button" onClick="locationAddAddress()">ny adresse</button>
              </div>
              <h3>
                  Adresseliste
              </h3>
          </div>
          
          <div id="adresseimportdiv" style="display: none; width: 95%; margin-bottom: 12px;">
            Kopier adresser ind og klik på indlæs, for at oprette flere adresser samtidig:<br>
            <textarea id="adresseimporttext" style="width: 100%; height: 80px;"></textarea><br>
            <button type=button onClick="hideAdresseImport()">Annuller</button> <button type=button onClick="performAdresseImport()">Indlæs adresser</button>
          </div>
          
          <script>
          
          function locationReplaceAll(str, find, replace) {
            return str.replace(new RegExp(find, 'g'), replace);
          }

            function showAdresseImport()
            {
                 $('#adresseimportdiv').show();
            }
          
            function hideAdresseImport()
            {
                 $('#adresseimportdiv').hide();
            }
          
            function performAdresseImport()
            {
              var importtext = $.trim($('#adresseimporttext').val());
              var splitlines = importtext.split("\n");
              
              var importCreated = 0;
              var importExisted = 0;
              
              for(var i=0;i<splitlines.length;i++)
              {
                splitlines[i] = locationReplaceAll($.trim(splitlines[i]),"\t",";");
                
              
              
                if(splitlines[i] != "")
                {
                  var parts = splitlines[i].split(';');

                  var adress = null;
                  if(parts.length == 8)
                  {
                     adress = {name: parts[0], adresse: parts[1], zip: parts[2], city: parts[3], country: parts[4], att: parts[5], phone: parts[6], vatno: parts[7]};
                  }
                  else if(parts.length == 7)
                  {
                     adress = {name: parts[0], adresse: parts[1], zip: parts[2], city: parts[3], country: parts[4], att: parts[5], phone: parts[6]};
                  }
                  else if(parts.length == 6)
                  {
                    adress = {name: parts[0], adresse: parts[1], zip: parts[2], city: parts[3], country: parts[4], att: parts[5], phone: ""};
                  }
                  else if(parts.length == 5)
                  {
                      adress = {name: parts[0], adresse: parts[1], zip: parts[2], city: parts[3], country: parts[4], att: "", phone: ""};
                  }
                  else if(parts.length == 4)
                  {
                      adress = {name: parts[0], adresse: parts[1], zip: parts[2], city: parts[3], country: "", att: "", phone: ""};
                  }

                  if(adress != null)
                  {

                      console.log('log 1');
                      console.log(adress);
                    var inAdressList = false;
                    
                    $('#locationAddressList .addressTable').each(function() {
                      
                      var adrName = $.trim($(this).find('.address_name').val()).toLowerCase();
                      var adrAtt = $.trim($(this).find('.address_att').val()).toLowerCase();
                      var adrPhone = $.trim($(this).find('.address_phone').val()).toLowerCase();
                        var adrVatno = $.trim($(this).find('.address_vatno').val()).toLowerCase();
                      var adrAdress = $.trim($(this).find('.address_address').val()).toLowerCase();
                      var adrZip = $.trim($(this).find('.address_zip').val()).toLowerCase();
                      var adrCity = $.trim($(this).find('.address_city').val()).toLowerCase();
                      var adrCountry = $.trim($(this).find('.address_country').val()).toLowerCase();
                      
                      if($.trim(adress['name'].toLowerCase()) == adrName && $.trim(adress['att'].toLowerCase()) == adrAtt && $.trim(adress['adresse'].toLowerCase()) == adrAdress && $.trim(adress['zip'].toLowerCase()) == adrZip && $.trim(adress['city'].toLowerCase()) == adrCity)
                      {
                        inAdressList = true;
                      }
                      
                    });
                    
                     if(inAdressList == true)
                     {
                      importExisted++;
                     }
                     else 
                     {
                        importCreated++;
                        locationAddAddress();
                        var newAdress = $('#locationAddressList').find('.addressTable:first-child');
                        newAdress.find('.address_name').val(adress["name"]);
                        newAdress.find('.address_att').val(adress["att"]);
                         newAdress.find('.address_phone').val(adress["phone"]);
                         newAdress.find('.address_vatno').val(adress["vatno"]);
                        newAdress.find('.address_address').val(adress["adresse"]);
                        newAdress.find('.address_zip').val(adress["zip"]);
                        newAdress.find('.address_city').val(adress["city"]);
                        newAdress.find('.address_country').val(adress["country"]);
                     }
                    
                  
                  }
              
                }
                  
                
                
              }
              
              hideAdresseImport()
              if(importCreated+importExisted > 0) $('#adresseimporttext').val('')
              if(importCreated == 0 && importExisted == 0) return alert('Der kunne ikke findes nogen adresser i den angivne tekst.');
              else if(importCreated == 0) return alert('Der er genkendt '+importExisted+' adresser, men de er allerede oprettet.');
              else if(importExisted == 0) return alert('Der oprettet '+importCreated+' adresser.');
              else return alert('Der er oprettet '+importCreated+' adresser, '+importExisted+' adresser fandtes allerede.');
            }
          
          </script>
          
          <div class="locfield locfield1 locfield2 locfield4" id="locationAddressList">
          
          </div>

        </div>
      </td>
  </tr>
</table>

<br>



<div id="addressTableTemplate" style="display: none;">
    <table class="addressTable" style="margin-bottom: 15px; width: 100%;">
    <tr>
                <td colspan="2" class="label" style="background: #ccc; padding: 4px;">
    
                    <div style="float: right;" class="locfield locfield1 locfield2">
                    
    										<button class="moveup" onClick="addressElmMove(this,true)" title="Flyt op"><span class="ui-icon ui-icon-circle-triangle-n" ></span></button>
    										<button class="movedown" onClick="addressElmMove(this,false)" title="Flyt ned"><span class="ui-icon ui-icon-circle-triangle-s" ></span></button>
    										<button class="delelm" onClick="addressElmDelete(this)" title="Fjern"><span class="ui-icon ui-icon-trash" ></span></button>
    										<button class="copyelm" onClick="addressElmCopy(this)" title="Kopier"><span class="ui-icon ui-icon-copy" ></span></button>
    										
    										
                    </div>
    
                    Adresse <span class="addressindexlabel"></span>
                    <input type=hidden class="address_id" value="0">
                </td>
            </tr>
      <tr>
        <td valign=top style="width:50%;">
          <table style="width: 100%;">
              
              
              
            <tr>
                <td valign="top" style="width: 85px; padding-top: 3px;">Navn</td>
                <td style=" padding-top: 3px;"><textarea class="address_name" cols="30" rows="2" style="width: 97%;"></textarea></td>
            </tr>
            
            <tr>
                <td valign="top">Adresse</td>
                <td><textarea cols="30" rows="2" class="address_address" style="width: 97%;"></textarea></td>
            </tr>
            <tr>
                <td>Postnr</td>
                <td><input type="text" class="address_zip" size="5"></td>
            </tr>
            <tr>
                <td>By</td>
                <td><input class="address_city" type="text" size="25"></td>
            </tr>
            <tr>
                <td>Land</td>
                <td><input type="text" class="address_country" style="width: 97%;"></td>
            </tr>
            
            <tr>
                <td valign="top" style="width: 85px; padding-top: 3px;">Att</td>
                <td style=" padding-top: 3px;"><input type="text" class="address_att" style="width: 97%;"></td>
            </tr>

          <tr>
              <td valign="top" style="width: 85px; padding-top: 3px;">Telefon nr</td>
              <td style=" padding-top: 3px;"><input type="text" class="address_phone" style="width: 97%;"></td>
          </tr>

              <tr>
                  <td valign="top" style="width: 85px; padding-top: 3px;">VAT nr</td>
                  <td style=" padding-top: 3px;"><input type="text" class="address_vatno" style="width: 97%;"></td>
              </tr>


              <tr style="display: none;" class="addrdotrow">
                  <td valign="top" style="width: 85px; padding-top: 3px;"><label><input type="checkbox" value="1" class="address_dot" onChange="updateFreightData()"> DOT</label></td>
                  <td style=" padding-top: 3px;">
                      <div class="dotdetails">
                          Dato og tid: <input type="datetime-local" name="address_dotdate" class="address_dotdate" value="" style="min-width: 100px;">
                      </div>
                  </td>
              </tr>
              <tr style="display: none;" class="addrcarryuprow">
                  <td valign="top" style="width: 85px; padding-top: 3px;"><label><input type="checkbox" value="1" class="address_carryup" onChange="updateFreightData()"> Opbæring</label></td>
                  <td style=" padding-top: 3px;">
                      <div class="carryupdetails">
                          <select class="address_carryuptype">
                              <option value="0">VÆLG</option>
                              <option value="3">Plads til helpalle</option>
                              <option value="2">Plads til halvpalle</option>
                              <option value="1">Har ikke elevator</option>
                          </select>
                      </div>
                  </td>
              </tr>
              <tr style="display: none;" class="addrnoterow">
                  <td valign="top" style="width: 85px; padding-top: 3px;">Fragtnoter</td>
                  <td style=" padding-top: 3px;">
                        <textarea class="address_freightnote" style="width: 100%; height: 40px;"></textarea>
                  </td>
              </tr>


            
          </table>
        </td>
        <td valign=top style="width: 50%;">
          <table style="width: 100%;">
             <tr class="locfield locfield1 locfield2">
                <td colspan="2" style="padding-top: 10px; padding-bottom: 4px; font-weight: bold;">Tilknyttede lokationer</td>
            </tr>
            <tr class="locfield locfield1 locfield2">
                <td colspan="2" style="background: white; border: 1px solid #E0E0E0;">
                  <div class="noloc" style="text-align: center; padding: 10px;">Ingen lokationer tilknyttet..</div>
                  <div class="locationsort" style="width: 100%; min-height: 145px; max-height: 200px; overflow-y: auto;"></div>
                  
                  <div style="border-top: 1px solid #e0e0e0; background: #FAFAFA; padding-top: 4px; margin-top: 4px; font-size: 11px;"><b>vælg:</b> <a href="#" onClick="rowCheckSelectAll(this)">alle</a> / <a href="#" onClick="rowCheckSelectNone(this)">ingen</a> | 
                  <b>handling:</b> <a href="#" onClick="rowCheckImport(this)">hent</a> / <a href="#" onClick="rowCheckRemove(this)">fjern</a></div>
                  
                </td>
            </tr>
          </table>
        </td>
      </tr>
      
    
    
        
      
    </table>
</div>

</div>

<script>


  function rowCheckSelectAll(elm)
  {
    $(elm).closest('tr').find('.locationsort').find('.loccheck').prop('checked',true);
  }


  function rowCheckSelectNone(elm)
  {
    $(elm).closest('tr').find('.locationsort').find('.loccheck').prop('checked',false);
  }

  function rowCheckImport(elm)
  {
    rowCheckSelectNone(elm);
    var parent = $(elm).closest('tr').find('.locationsort');
    var checked = $('.loccheck:checked').closest('.locationitem').appendTo(parent);
    rowCheckSelectNone(elm);
                             console.log(parent.get(0));
    if(parent.find('.loccheck').size() == 0) parent.parent().find('.noloc').show();
    else parent.parent().find('.noloc').hide();
    
  }

  function rowCheckRemove(elm)
  {
      var parent = $(elm).closest('tr').find('.locationsort');
      $(elm).closest('tr').find('.loccheck:checked').prop('checked',false).closest('.locationitem').appendTo($('#locAvailableLocations .locationsort'));
      if(parent.find('.loccheck').size() == 0) parent.parent().find('.noloc').show();
      else parent.parent().find('.noloc').hide();
  }
  
  function rowCheckMoveToAvailable()
  {
    $('#locationAddressList .loccheck:checked').prop('checked',false).closest('.locationitem').appendTo($('#locAvailableLocations .locationsort'));
  }
  
    function downloadFordelingRapport()
    {
      document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+getReportUserCheckUrl();
    }

  function downloadFordelingRapportCS()
  {
      document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+getReportUserCheckUrl()+'&likecs=1';
  }
    
    function downloadSumRapport()
    {
      document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+'&type=sum';
    }

  function downloadSumRapportExcel()
  {
      document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+'&type=sumexcel';
  }

    function downloadLeveringsAdresser() {
        document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+'&type=adresser';
    }

  function downloadLeveringsAdresserImport() {
      document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+'&type=adresseimport';
  }

    function downloadGaveliste() {
        document.location = 'index.php?rt=unit/cardshop/pluklister/presentshoplist&shopid='+_editShopID;
    }
    
    function downloadProformaFaktura() {

      var shopid = _editShopID;
        $('#gavealiasDialog').html('<div style="padding: 20px; text-align: center;">Henter adresser.</div>');
        $.post('index.php?rt=unit/valgshop/reports/proformafakturaform',{shopid: shopid},function(response) {
            $('#gavealiasDialog').html(response);
        });

        $('#gavealiasDialog').dialog({
            title: 'Hent proformafaktura',
            modal: true,
            width:700,
            height:500,
            buttons: {

                Annuller: function() {
                    $( this ).dialog( "close" );
                },
                Hent: function() {
                    document.location = 'index.php?rt=unit/valgshop/reports/proformafakturadownload&'+returnProformaFormValuesAsURL();
                }
            }
        });
    }


    
  function downloadLabelRapport()
  {
      document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+'&type=label';
  }

  function downloadLabelRapportWItemno() {
      document.location = 'index.php?rt=shop/fordelingreport&shopID='+_editShopID+'&type=label&witemno=1';
  }

     function downloadKlubMails()
    {
      document.location = 'index.php?rt=shop/klubmails&shopID='+_editShopID+'&type=sum';
    }
    
    function getReportUserCheckUrl()
    {
      var str = "&uea=";
      $('.userattr_reportcheck:checked').each(function() {
        str += ','+$(this).val();
      });
      return str;
    }

		// SERIALIZE ADDRESSES 
		
		function getAllAddressData()
		{
			var list = [];
			$('#locationAddressList .addressTable').each(function() {
				list.push(getAddressData(this));
			});
			return list;
		}

		function getAddressData(elm)
		{
			var locations = [];
			$(elm).find('.locationsort').each(function() {
				$(this).find('.locationitem .name').each(function() {
					locations.push($(this).text());
				})
			});
	
			var addressData = {};
			addressData['id'] = $(elm).find('.address_id').val();
			addressData['name'] = $(elm).find('.address_name').val();
			addressData['address'] = $(elm).find('.address_address').val();
			addressData['zip'] = $(elm).find('.address_zip').val();
			addressData['city'] = $(elm).find('.address_city').val();
            addressData['att'] = $(elm).find('.address_att').val();
            addressData['phone'] = $(elm).find('.address_phone').val();
            addressData['vatno'] = $(elm).find('.address_vatno').val();

            addressData['dot'] = $(elm).find('.address_dot').is(':checked') ? 1 : 0;
            addressData['dotdate'] = $(elm).find('.address_dotdate').val();
            addressData['carryup'] = $(elm).find('.address_carryup').is(':checked') ? 1 : 0;
            addressData['carryuptype'] = $(elm).find('.address_carryuptype').val();
            addressData['freightnote'] = $(elm).find('.address_freightnote').val();

            addressData['country'] = $(elm).find('.address_country').val();
			addressData['locations'] = locations.join('\n');
			return addressData;
		}

		// MANUPULATE ADDRESS

    function locationAddAddress()
    {
        $('#locationAddressList').prepend($('#addressTableTemplate').html());

        if($('#locationAddressList .addressTable').length > 1) {
            var newAddress = $('#locationAddressList').find('.addressTable:first-child');
            var prevAddress = $('#locationAddressList').find('.addressTable:nth-child(2)');

            if($.trim(newAddress.find('.address_vatno').val()) == "" && $.trim(prevAddress.find('.address_vatno').val()) != "") {
                newAddress.find('.address_vatno').val(prevAddress.find('.address_vatno').val());
            }

        }

        updateLocationTables();
        updateLocationSortables();
        updateAddressLabels();
        updateFreightData()
    }
    
    function getLocationRowHTML(name)
    {
      return "<div class='locationitem' style='padding: 4px; border-bottom: 1px solid #f0f0f0;'><div style='display: inline-block;'><span class='ui-icon ui-icon-arrow-4 locsort ui-sortable-handle'></span></div> <input type=checkbox class='loccheck'> <span class='name'>"+name+"</span></div>"; 
    }
    
    function addressElmDelete(elm)
    {
    	var parent = $(elm).closest('.addressTable')
			parent.find('.locationsort .locationitem .name').each(function() {
        $('#locAvailableLocations').find('.locationsort').append(getLocationRowHTML($(this).text()));
			});			

			parent.remove();
    	updateAddressLabels();
    	updateLocationTables();
        updateFreightData()
    }
    
    function addressElmCopy(elm)
    {
    	var parent = $(elm).closest('.addressTable')
    	parent.clone().insertAfter(parent).find('.locationsort').html('');
    	updateAddressLabels();
    	updateLocationTables();
     	updateLocationSortables();
        updateFreightData()
    }
    
    function addressElmMove(elm,up)
    {
    	var parent = $(elm).closest('.addressTable');
			if(up == true)
			{
				if(parent.prev().size() == 0) return;
				parent.insertBefore(parent.prev());	
				updateAddressLabels()
			}
			else
			{
				if(parent.next().size() == 0) return;
				parent.insertAfter(parent.next());	
				updateAddressLabels()
			}
		}    
		
		function updateAddressLabels()
		{
			var count = 1;
			$('#locationAddressList .addressTable').each(function() {
				$(this).find('.addressindexlabel').text(count);
				count++;
			
				if($(this).find('.locationsort').find('tr').size() == 0) $(this).find('.delelm').prop('disabled',false)
				else $(this).find('.delelm').prop('disabled',true)
				
				if($(this).prev().size() == 0) $(this).find('.moveup').prop('disabled',true);
				else $(this).find('.moveup').prop('disabled',false)
				
				if($(this).next().size() == 0) $(this).find('.movedown').prop('disabled',true);
				else $(this).find('.movedown').prop('disabled',false)
				
			});	
		}

		// UPDATE LOCATION FIED

		function updateAddressType()
    {
    	var type = parseInt($('#locationType').val());
    	if(isNaN(type) || type < 0 || type > 4) type = 0;
    	$('.locfield').hide();
     	$('.locfield'+type).show();   	
      
      if(type== 4 && $('#locationAddressList').find('.addressTable').size() == 0) locationAddAddress();
      
    }

		var loceditAttributes = null;

		function updateLocationForm(response)
    {
                                 
    		$('#locationType').val(response['data']['shop'][0]['location_type']);
    		updateAddressType();
    
    		// LOAD ATTRIBUTES
        var attributeselect = $('#locationAttributeSelect').html('');
        var attributes = response['data']['shop'][0]['attributes_'];
        loceditAttributes = attributes;

        attributeselect.append('<option value=0>Lokationsfelt ikke valgt</option>')

        var selectedAttributes = [];
        if(response.data.shop[0]['report_attributes'] != null) selectedAttributes = response.data.shop[0]['report_attributes'].split(',');
         
        for(var i=0;i<attributes.length;i++)
        {
            if(attributes[i].hasOwnProperty('list_data') ) // && $.trim(attributes[i]['list_data']) != ""
            {
                attributeselect.append('<option class="locfield locfield2 '+($.trim(attributes[i]['list_data']) ? 'locfield1' : '')+' locfield3" value="'+attributes[i]['id']+'"> '+attributes[i]['name']+'</option>');
            }
            
            if(attributes[i].is_name == 0 )
            {
              var isSelected = false;
              for(var a = 0; a < selectedAttributes.length; a++)
              {
                if(attributes[i].id == parseInt(selectedAttributes[a]))
                {
                  isSelected = true;    
                }
              }
              $('#reportuserfields').append('<div><label><input type="checkbox" class="userattr_reportcheck" value="'+attributes[i].id+'" '+(isSelected ? 'checked' : '')+'> '+attributes[i].name+'</label></div>');
            }
        }
        
        // LOAD ADDRESS
        var addressList = response['data']['shop'][0]['addresses'];
        $('#locationAddressList').html('');
        for(var i = 0; i <addressList.length;i++)
        {

     		   $('#locationAddressList').append($('#addressTableTemplate').html());
     		   var newAddress = $('#locationAddressList').find('.addressTable:last-child');
     		   newAddress.find('.address_id').val(addressList[i]['id']);
     		   newAddress.find('.address_name').val(addressList[i]['name']);
     		   newAddress.find('.address_address').val(addressList[i]['address']);
     		   newAddress.find('.address_zip').val(addressList[i]['zip']);
           newAddress.find('.address_country').val(addressList[i]['country']);
           newAddress.find('.address_att').val(addressList[i]['att']);
            newAddress.find('.address_phone').val(addressList[i]['phone']);
            newAddress.find('.address_vatno').val(addressList[i]['vatno']);
     		   newAddress.find('.address_city').val(addressList[i]['city']);

            newAddress.find('.address_dot').prop('checked', addressList[i]['dot'] === 1);

            if(addressList[i]['dot_date'] != null && addressList[i]['dot_date'] != '') {
                newAddress.find('.address_dotdate').val(addressList[i]['dot_date']);
            } else {
                newAddress.find('.address_dotdate').val('');
            }


            newAddress.find('.address_carryup').prop('checked', addressList[i]['carryup'] === 1);
            newAddress.find('.address_carryuptype').val(addressList[i]['carryup_type']);
            newAddress.find('.address_freightnote').val(addressList[i]['freight_note']);

     		   
     		   if(addressList[i]['locations'] != "")
     		   {
     		   		var locations = addressList[i]['locations'].split('\n');
							for(var j = 0 ; j < locations.length; j++)
							{
                newAddress.find('.locationsort').append(getLocationRowHTML(locations[j]));
							}     		   
     		   }
                            
        }
        
        // Load existing
        if(response['data']['shop'][0]['location_attribute_id'] > 0)
        {
	        	currentLocationAttribute = response['data']['shop'][0]['location_attribute_id']
            $('#locationAttributeSelect').val(response['data']['shop'][0]['location_attribute_id'])
            loadAttributeTexts();
        }      
        
        updateLocationTables();
        updateLocationSortables();
        updateFreightData()
    }
    
    var currentLocationAttribute;
    
    function loadAttributeTexts()
    {
                 
				$('.locationsort').find('.locationitem').addClass('locationsortRemove');
					     
    		if(parseInt($('#locationType').val()) == 1)
    		{
    		
	        var id = $('#locationAttributeSelect').val();
	        if(id != currentLocationAttribute)
	        {
	        	$('#locAvailableLocations .locationsort').html('');
	        }
	            
	        var text = "";
	        $('#locAvailableLocations').show();
	        for(var i=0;i<loceditAttributes.length;i++)
	        {
	            if(loceditAttributes[i]['id'] == id && loceditAttributes[i]['list_data'] != null)
	            {
	                var locations = loceditAttributes[i]['list_data'].split('\n');
             	   	$('#locAvailableLocations').find('.locationsort').html();
	                for(var j=0;j<locations.length;j++)
	                {
	                	if($.trim(locations[j]) != "")
	                	{
		                	var itemExists = false;
		               		$('.locationsort').find('.name').each(function() {
									 			if($(this).text() == locations[j]) 
												 {
												 		$(this).closest('.locationitem').removeClass('locationsortRemove')
												 		itemExists = true;
												 }
									 		})
		                
		                	if(itemExists == false)
		                	{
                          $('#locAvailableLocations').find('.locationsort').append(getLocationRowHTML(locations[j]));
		                	}
	                	}   
	                }
	                updateLocationSortables();
	            }
	        }
	        
					$('.locationsortRemove').remove();
      	  updateLocationTables();
        
        }
        else if(parseInt($('#locationType').val()) == 2)
        {

			$('#locAvailableLocations').find('.locationsort').html('<div style="text-align: center; padding: 20px;">Henter unikke lokationer..</div>');
            console.log('LE:Start load');

            $.post('index.php?rt=shop/locationattributes', {id: _editShopID, attribute_id: $('#locationAttributeSelect').val()}, function (response) {
                console.log('LE: Done loading, start processing');

                var locations = response['list'];
                var $locationSort = $('#locAvailableLocations').find('.locationsort');

                $locationSort.html('');

                var existingLocationsSet = new Set();

                function encodeHtmlEntities(text) {
                    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#x27;');
                }

                $('.locationsort .name').each(function() {
                    var locationText = $(this).text();
                    var encodedLocationText = encodeHtmlEntities(locationText);
                    console.log('1: '+locationText + ' -> ' + encodedLocationText);
                    existingLocationsSet.add(encodedLocationText);
                });
                
                $.each(locations, function(index, location) {
                    if (!existingLocationsSet.has(location)) {
                        console.log('2: '+location);
                        $locationSort.append(getLocationRowHTML(location));
                    }
                });

                console.log('LE: Done processing, start updating');


                updateLocationSortables();
                updateLocationTables();

                console.log('LE: done updating');
            }, 'json');

        }
    }
    
    

    function updateLocationSortables()
    {
    
        $( ".locationsort" ).sortable({           placeholder: "location-placeholder",
            connectWith: ".locationsort",forcePlaceholderSize: true,dropOnEmpty: true,start: function() {

              $('.locationsort').show();
              $('.noloc').hide();

            }, stop: function() {
                updateLocationTables();
            }
        }).disableSelection();
    }

    function updateLocationTables()
    {
        $('.locationsort').each(function() {
            if($(this).find('.locationitem').size() == 0)
            {
                $(this).parent().find('.noloc').show();
            }
            else {
                $(this).show().parent().find('.noloc').hide();
            }

        })
    }


  var useFreightDot = 0;
  var useFreightCarryup = 0;


  function updateFreightData() {

      $('#locationAddressList .addressTable').each(function() {

          var $this = $(this);

          // Handle DOT visibility
          if (useFreightDot == 1) {
              $this.find('.addrdotrow').show();
              if ($this.find('.address_dot').is(':checked')) {
                  $this.find('.dotdetails').show();
              } else {
                  $this.find('.dotdetails').hide();
              }
          } else {
              $this.find('.addrdotrow').hide();
          }

          // Handle Carryup visibility
          if (useFreightCarryup == 1) {
              $this.find('.addrcarryuprow').show();
              if ($this.find('.address_carryup').is(':checked')) {
                  $this.find('.carryupdetails').show();
              } else {
                  $this.find('.carryupdetails').hide();
              }
          } else {
              $this.find('.addrcarryuprow').hide();
          }

          // Always show freight note row if either DOT or Carryup is used
          if (useFreightDot == 1 || useFreightCarryup == 1) {
              $this.find('.addrnoterow').show();
          } else {
              $this.find('.addrnoterow').hide();
          }
      });
  }

  function updateFreightSettings() {
      setTimeout(function() {
          updateFreightSettings();
      }, 30000);
      $.post('index.php?rt=shop/loadfreightdata',{id: _editShopID},function(response) {

          if(response.hasOwnProperty('use_dot')) {
              useFreightDot = response['use_dot'];
          }

          if(response.hasOwnProperty('use_carryup')) {
              useFreightCarryup = response['use_carryup'];
          }

          updateFreightData();

      },'json');
  }


    $(document).ready(function() {

        updateFreightSettings();



    })
    
    

</script>