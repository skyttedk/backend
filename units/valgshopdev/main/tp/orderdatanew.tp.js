export default class TpOrderData {
    static demo() {
        return `
            asdfasd
        `;
    }
    static UIinsertPersonCode(data){
        let html = '<option value="-1">Vælg</option>'
        data.data.forEach(function(ele) {
            html+=`<option value='${ele.attributes.salespersoncode}'>${ele.attributes.salespersoncode}</option>`;
        });
        return html;
    }
    static mainTemplate(){
        return `
            <button style="float: right;margin:10px; background-color: chartreuse;" type="button" class="save_button">Gem</button>
            <button style="float: right;margin:10px;" type="button" class="order_approval">Godkend Ordredata</button>
         
            <form id="orderDataForm" style="max-width: 700px;">
                        <!-- add other fields -->
                       <br><br>
                       <h3>Godkendelse status: </h3>
                       <div id="order_approval_state"></div>
                        
                        
                       <br><br>
                    
                        <h4>Generelt</h4>
                        <div class="form-group">
                            <label for="order_type">Ordretype</label>
                            <select class="form-control" id="order_type">
                                 <option value="valgshop">Valgshop</option>
                                <option value="papirvalg">Papirvalg</option>
                            </select>
                        </div>
                        <br>
                        <h4>Fakturainfo</h4>
                        <hr>
                        <div class="form-group">
                            <label for="salesperson_code">Kundenr i navision</label>
                            <input type="text" class="form-control" id="nav_debitor_no">
                            </input>
                        </div>
                        <div class="form-group">
                            <label for="salesperson_code">Sælgerkode i Navision</label>
                            <select class="form-control" id="salesperson_code">
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="budget">Budget ekskl. moms (Fremgår på gaver)</label>
                            <input type="number" class="form-control" id="budget" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="user_count">Antal medarbejdere</label>
                            <input type="number" class="form-control" id="user_count">
                        </div>
                        <div class="form-group">
                            <label for="present_count">Antal gavevalg (Inkl. undervalg og donationer)</label>
                            <input type="number" class="form-control" id="present_count">
                        </div>
                        
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                   <label for="is_foreign">Udenlandsk kunde</label>
                        <select class="form-control" id="is_foreign">
                            <option value="0">Nej</option>
                            <option value="1">Ja</option>
                        </select>
                                </div>
                            </div>
                            <div class="col-9" id="group_is_foreign" style="display: none;">
                                <div class="form-group">
                         <label for="foreign_names">Indtast land(e)</label>
                        <input type="text" class="form-control" id="foreign_names">
                                </div>
                            </div>
                        </div>  
                        
                        <div class="form-group" style="display: none;">
                            <label for="payment_terms">Betalingsbetingelser - fjernet af SC d. 14/9 - de skal køre debitorens standard betingelser fra nav</label>
                            <textarea class="form-control" id="payment_terms"></textarea>
                    
                        </div>
                        <div class="form-group">
                            <label for="requisition_no">Kundens reference</label><span> (PO eller lignende)</span>
                            <input type="text" class="form-control" id="requisition_no"></input>
                   
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_special">Særlige krav til fakturering</label>
                            <select class="form-control" id="payment_special">
                                <option value="1">Ja</option>
                                <option value="0">Nej</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="contract">Kontrakt/samhandelsaftale</label>
                            <select class="form-control" id="has_contract">
                                <option value="0">Nej</option>
                                <option value="1">Ja</option>
                           
                            </select>
                        </div>                        
                        
                        <div class="form-group" style="display: none;">
                            <label for="invoice_fee">Fakturagebyr - fjernet af SC d. 14/9 - de skal køre debitorens valg mht. fakturagebyr fra nav.</label>
                            <select class="form-control" id="invoice_fee">
                                <option value="1">Ja</option>
                                <option value="0">Nej</option>
                            </select>                            
                    
                        </div>
                       
                 <br>      
         
                    <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                   <label for="discount_option">Kontantrabat</label>
                        <select class="form-control" id="discount_option">
                            <option value="0">Nej</option>
                            <option value="1">Ja</option>
                        </select>
                                </div>
                            </div>
                            <div class="col-9" id="discount_value_group" style="display: none;">
                                <div class="form-group">
                         <label for="discount_value">Indtast rabat i %</label>
                        <input type="number" class="form-control" id="discount_value">
                                </div>
                            </div>
                        </div>                       
                       
                          <br>   
                       
                       
                        <div class="form-group">
                            <label for="valgshop_fee">Valgshop gebyr</label>
                            <input type="number" class="form-control" id="valgshop_fee">
                        </div>
                        <br>
          
                        
                        
                        <h4>Levering og fragt</h4>
                        <hr>
                        <div class="form-group">
                            <label for="delivery_date">Leveringsdato</label>
                            <input type="date" class="form-control" id="delivery_date">
                        </div>

                        <div class="form-group">
                            <label for="flex_delivery_date">Fleksibel levering (Første dag og sidste dag )</label>
                            <div class="row">
                                 <div class="col-2"></div>
                                <div class="col-5">Første dag</div>
                                <div class="col-5">Sidste dag</div>
                            </div>
                            <div class="row">
                                <div class="col-2"></div>
                                <div class="col-5"><input type="date" class="form-control" id="flex_start_delivery_date"></div>
                                <div class="col-5"><input type="date" class="form-control" id="flex_end_delivery_date"></div>
                            </div>
                            
                            
                        </div>
                        <div class="form-group">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="early_delivery">Tidlig levering (Må kunden kontaktes hvis der gaver er klar før tid)</label>
                                    <select class="form-control" id="early_delivery">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                </div>
                            </div>
                            
                        </div>
                        </div>





                        <div class="form-group">
                            <label for="handover_date">Udlevering af gaver ved kunden</label>
                            <input type="date" class="form-control" id="handover_date">
                        </div>
                        <div class="form-group">
                            <label for="multiple_deliveries">Flere leveringsadresser</label>
                            <select class="form-control" id="multiple_deliveries">
                                <option value="0">Nej</option>
                                <option value="1">Ja</option>
                            </select>
                        </div>
                        
                        
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="private_delivery">Privatlevering</label>
                                    <select class="form-control" id="private_delivery">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-9" id="private_delivery_price_group" style="display: none;">
                                <div class="form-group">
                                    <label for="private_delivery_price">Pris pr. gave</label>
                                    <input type="number" class="form-control" id="privatedelivery_price" step="0.01">
                                </div>
                            </div>
                        </div>
                        
                        
                        
                        
  <div class="row">
            <div class="col-3 form-group">
            <br>
                <label for="foreign_delivery">Udenlandslevering</label>
                <select class="form-control" id="foreign_delivery" name="foreign_delivery">
                    <option value="0">Nej</option>
                    <option value="1">Ja</option>
                    <option value="2">Delvis</option>
                </select>
            </div>
            <div class="col-9 form-group" id="foreign_countries" style="display: none;">
             <br> 
                <label><input type="checkbox" name="norge"> Norge</label><br>
                <label><input type="checkbox" name="sverige"> Sverige</label><br>
                <label><input type="checkbox" name="tyskland"> Tyskland</label><br>
                <label><input type="checkbox" name="england"> England</label><br>
                <div class="row align-items-center mb-2">
                    <div class="col-3">
                        <label class="mb-0"><input type="checkbox" class="foreign_countries-freetext-toggle" name="eu"> EU - Fritekst</label>
                    </div>
                    <div class="col-9">
                        <input type="text" name="eu_freetext" class="form-control foreign_countries-freetext-toggle" >
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-3">
                        <label class="mb-0"><input type="checkbox" class="foreign_countries-freetext-toggle" name="amerika"> Amerika - Fritekst</label>
                    </div>
                    <div class="col-9">
                        <input type="text" name="amerika_freetext" class="form-control foreign_countries-freetext-toggle" >
                    </div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-3">
                        <label class="mb-0"><input type="checkbox" class="foreign_countries-freetext-toggle" name="andre"> Andre - Fritekst</label>
                    </div>
                    <div class="col-9">
                        <input type="text" name="andre_freetext" class="form-control foreign_countries-freetext-toggle" >
                    </div>
                </div>
            </div>
        </div>
                            <br><br>
                        </div>                        
       <!--         
                        <div class="form-group">
                            <label for="delivery_terms">Leveringsbetingelser</label>
                            <textarea class="form-control" id="delivery_terms"></textarea>
                        </div>
           -->             
                        
          <br>             
<div class="row">
    <div class="col-3">
        <div class="form-group">
            <label for="deliveryprice_option">Leveringsbetingelser</label>
            <select class="form-control" id="deliveryprice_option">
                <option value="0">Nej</option>
                <option value="1">Fastpris</option>
                <option value="2">Ab lager</option>
                <option value="3">Frit leveret</option>
               
            </select>
        </div>
    </div>
    <div class="col-9" id="deliveryprice_amount_group" style="display: none;">
            <div class="form-group">
                         <label for="deliveryprice_amount">Indtast værdi</label>
                        <input type="number" class="form-control" id="deliveryprice_amount">
                                </div>
             <div class="form-group">
                            <label for="deliveryprice_note">Note/beskrivelse til fast pris</label>
                            <textarea class="form-control" id="deliveryprice_note"></textarea>                            
                        </div>
                        
    </div>
</div>
                        
              <br>          
                        
                        
           
                        <div class="form-group">
                            <label for="delivery_note_internal">Intern fragtnote</label>
                            <textarea class="form-control" id="delivery_note_internal"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="delivery_note_external">Ekstern fragtnote (til fragtmand / ordrebekræftelse)</label>
                            <textarea class="form-control" id="delivery_note_external"></textarea>
                        </div>
                                                             <br>
                        <h4>DOT (Dette bør undgås)</h4>
                        <hr>
    <div class="row">
        <div class="col-3">
         <br>
            <div class="form-group">
                <label for="dot_use">Ønskes DOT </label>
                <select class="form-control" id="dot_use">
                    <option value="0">Nej</option>
                    <option value="1">Ja</option>
                </select>
            </div>
        </div>
        <div class="col-9" id="dot_fields" style="display: none;">
          <br> <br>
            <div class="form-group">
                <label for="dot_amount">Antal adresser med DOT</label>
                <input type="number" class="form-control" id="dot_amount">
            </div>
            <div class="form-group">
                <label for="dot_price">Pris for dot levering (pr levering)</label>
                <input type="number" class="form-control" id="dot_price" step="0.01">
            </div>
            <div class="form-group">
                <label for="dot_note">Note til dot levering</label>
                <textarea class="form-control" id="dot_note"></textarea>
            </div>
        </div>
    </div>
                                         <br>
                        <h4>Opbæring (Dette bør undgås)</h4>
                        <hr>
      <div class="row">
        <div class="col-3">
           <br>
            <div class="form-group">
                <label for="carryup_use">Ønskes opbæring</label>
                <select class="form-control" id="carryup_use">
                    <option value="0">Nej</option>
                    <option value="1">Ja</option>
                </select>
            </div>
        </div>
        <div class="col-9" id="carryup_fields" style="display: none;">
            <br> <br>
            <div class="form-group">
                <label for="carryup_amount">Antal adresser med opbæring</label>
                <input type="number" class="form-control" id="carryup_amount">
            </div>
            <div class="form-group">
                <label for="carryup_price">Pris for opbæring (pr levering)</label>
                <input type="number" class="form-control" id="carryup_price" step="0.01">
            </div>
            <div class="form-group">
                <label for="carryup_note">Note til opbæring</label>
                <textarea class="form-control" id="carryup_note"></textarea>
            </div>
        </div>
    </div>
<br>


                                <br>
                        <h4>Gaver</h4>
                        <hr>
                            <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="autogave_use">Brug autogave</label>
                <select class="form-control" id="autogave_use">
                    <option value="-1">Ikke taget stilling</option>
                    <option value="0">Nej</option>
                    <option value="1">Ja</option>
                </select>
            </div>
        </div>
        <div class="col-9" id="autogave_fields" style="display: none;">
            <div class="form-group">
                <label for="autogave_itemno">Autoagave varenr</label>
                <input type="text" class="form-control" id="autogave_itemno">
            </div>
        </div>
    </div>
                        <div class="form-group">
                            <label for="plant_tree">Plant et træ</label>
                            <select class="form-control" id="plant_tree">
                                <option value="0">Nej</option>
                                <option value="1">Ja</option>
                            </select>
                        </div>
                        
                        <br>
                         <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="present_papercard">Julekort</label>
                                    <select class="form-control" id="present_papercard">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-9" id="present_papercard_price_group" style="display: none;">
                                <div class="form-group">
                                    <label for="present_papercard_price">Aftalt pris på julekort</label>
                                    <input type="number" class="form-control" id="present_papercard_price" step="0.01">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="present_wrap">Brug indpakning</label>
                                    <select class="form-control" id="present_wrap">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-9" id="present_wrap_price_group" style="display: none;">
                                <div class="form-group">
                                    <label for="present_wrap_price">Pris pr. gave</label>
                                    <input type="number" class="form-control" id="present_wrap_price" step="0.01">
                                </div>
                            </div>
                        </div>
                        
                       <br> 
                       <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="present_nametag">Navnelabels</label>
                                    <select class="form-control" id="present_nametag">
                                        <option value="0">Nej</option>
                                        <option value="1">Ja</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-9" id="present_nametag_price_group" style="display: none;">
                                <div class="form-group">
                                    <label for="present_nametag_price">Pris pr. gave</label>
                                    <input type="number" class="form-control" id="present_nametag_price" step="0.01">
                                </div>
                            </div>
                        </div>
                        
                       
                        
                       <br>
                        <div class="row">
        <div class="col-3">
           <div class="form-group">
                            <label for="handling_special">Speciel håndtering / særlig pak</label><span>(Dette bør undgås)</span>
                            <select class="form-control" id="handling_special">
                                <option value="0">Nej</option>
                                <option value="1">Ja</option>
                            </select>
                        </div>
        </div>
        <div class="col-9" id="group_handling_special" style="display: none;">
         
            <div class="form-group">
                <label for="handling_notes">Note om special håndtering / særlig pak</label>
                <textarea class="form-control" id="handling_notes" rows="3"></textarea>
            </div>
        </div>
    </div>
    
    
    
    
                                             <br>
                        <h4>Udlån (Dette bør undgås)</h4>
                        <hr>
    <div class="row">
        <div class="col-3">
            <div class="form-group">
                <label for="loan_use">Udlån</label>
                <select class="form-control" id="loan_use">
                    <option value="0">Nej</option>
                    <option value="1">Ja</option>
                </select>
            </div>
        </div>
        <div class="col-9" id="loan_fields" style="display: none;">
            <div class="form-group">
                <label for="loan_deliverydate">Udlån, dato for levering ved kunden</label>
                <input type="date" class="form-control" id="loan_deliverydate">
            </div>
            <div class="form-group">
                <label for="loan_pickupdate">Udlån, dato for afhentning</label>
                <input type="date" class="form-control" id="loan_pickupdate">
            </div>
            <div class="form-group">
                <label for="loan_notes">Udlåns noter</label>
                <textarea class="form-control" id="loan_notes" rows="3"></textarea>
            </div>
        </div>
    </div>
                     <br>
                        <h4>Vigtige deadlines</h4>
                        <hr>
                          <div class="form-group">
                            <label for="">Shop åben og luk</label>
                            <div class="row">
                                 <div class="col-2"></div>
                                <div class="col-5">Åben</div>
                                <div class="col-5">Luk</div>
                            </div>
                            <div class="row">
                                <div class="col-2"></div>
                                <div class="col-5"><input type="date" class="form-control " id="start_date"></div>
                                <div class="col-5"><input type="date" class="form-control " id="end_date"></div>
                            </div>
                          </div>       
                        
                        
                        
                        <div class="form-group">
                            <label for="deadline_testshop">Deadline testshop</label>
                            <input type="date" class="form-control" id="deadline_testshop">
                        </div>
                        <div class="form-group">
                            <label for="deadline_changes">Deadline rettelser</label>
                            <input type="date" class="form-control" id="deadline_changes">
                        </div>
                        <div class="form-group">
                            <label for="deadline_customerdata">Deadline materiale fra kunde</label>
                            <input type="date" class="form-control" id="deadline_customerdata">
                        </div>
                        <div class="form-group">
                            <label for="deadline_listconfirm">Deadline godkendelse af fordelingslister</label>
                            <input type="date" class="form-control" id="deadline_listconfirm">
                        </div>
                        <div class="form-group">
                            <label for="reminder_use">Brug reminders</label>
                            <select class="form-control" id="reminder_use">
                                <option value="0">Nej</option>
                                <option value="1">Ja</option>
                            </select>
                        </div>
                        <div id="group_reminder_use">
                        <div class="form-group" >
                            <label for="reminder_date">Dato for reminders</label>
                            <input type="date" class="form-control" id="reminder_date">
                        </div>
                        <div class="form-group" >
                            <label for="reminder_note">Reminder noter</label>
                            <textarea class="form-control" id="reminder_note" rows="3"></textarea>
                        </div>
                        
</div>
                        
                        <br>
                         <h4>Specifikationer</h4>
                        <hr>
                        <div class="form-group">
    <label for="user_username">Log på med (brugernavn)</label>
    <input type="text" class="form-control" id="user_username">
</div>
<div class="form-group">
    <label for="user_username_note">Brugernavn bemærkning</label>
    <textarea class="form-control" id="user_username_note" rows="3"></textarea>
</div>
<div class="form-group">
    <label for="user_password">Log på med (adgangskode)</label>
    <input type="text" class="form-control" id="user_password">
</div>
<div class="form-group">
    <label for="user_password_note">Adgangskode bemærkning</label>
    <textarea class="form-control" id="user_password_note" rows="3"></textarea>
</div>
<div class="form-group">
    <label for="deliverydate_receipt">Leveringsdato på kvittering</label>
    <input type="date" class="form-control" id="deliverydate_receipt">
</div>
<div class="form-group">
    <label for="gaveklubben_link">Mulighed for link til Gaveklubben.dk</label>
    <select class="form-control" id="gaveklubben_link">
        <option value="0">Nej</option>
        <option value="1">Ja</option>
    </select>
</div>
<br>
<div class="row">
    <div class="col-3">
        <div class="form-group">
            <label for="language">Sprognavne</label>
            <select class="form-control" id="language">
                <option value="0">Nej</option>
                <option value="1">Ja</option>
            </select>
        </div>
    </div>
    <br><br>
    <div class="col-9" id="group_language" style="display: none;">
        <div class="form-group">
            <label for="language_names">Skriv sprog</label>
            <textarea class="form-control" id="language_names"></textarea>
        </div>
         <br>
    </div>
       <br>
    <div class="form-group">

    <label for="otheragreements_note">Øvrige aftaler med kunden</label>
    <textarea class="form-control" id="otheragreements_note" rows="3"></textarea>
</div>           
</div>
    <input type="hidden" id="shop_id">
    <input type="hidden" id="id">

                           </form>
           <br>
                       

 <button style="float: right;margin:10px; background-color: chartreuse;" type="button" class="save_button">Gem</button><br><br><br><br><br><br>
        `;
    }
}