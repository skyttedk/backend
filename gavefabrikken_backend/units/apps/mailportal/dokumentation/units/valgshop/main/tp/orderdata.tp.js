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
            <h3 style="color: blue" id="order_approval_state"></h3>
            <form id="orderDataForm" style="max-width: 700px;">
                        <!-- add other fields -->
                        <h4>Generelt</h4>
                        <div class="form-group">
                            <label for="order_type">Ordretype</label>
                            <select class="form-control mandatory" id="order_type">
                                 <option value="valgshop">Valgshop</option>
                                <option value="papirvalg">Papirvalg</option>
                            </select>
                        </div><br>
                        
                        <div style="">
                        <div class="form-group">
                            <label for="salesperson_code">Virksomhed navn</label>
                            <input type="text" class="form-control" id="name" disabled>
                            </input>
                        </div><br>                            
                        
                        <div class="form-group">
                            <label for="ship_to_address">Adresse</label>
                            <input type="text" class="form-control" id="ship_to_address">
                            </input>
                        </div><br>
                        
                        <div class="form-group">
                            <label for="ship_to_address_2">Adresse 2</label>
                            <input type="text" class="form-control" id="ship_to_address_2">
                            </input>
                        </div><br>
                        
                        <div class="form-group">
                            <label for="ship_to_postal_code">Postnr.</label>
                            <input type="number" class="form-control" id="ship_to_postal_code">
                            </input>
                        </div><br>
                        
                        <div class="form-group">
                            <label for="ship_to_city">By</label>
                            <input type="text" class="form-control" id="ship_to_city">
                            </input>
                        </div>
                        <br>                                                                                                
                        <div class="form-group">
                            <label for="contact_name">Kontaktperson</label>
                            <input type="text" class="form-control" id="contact_name" disabled>
                            </input>
                        </div>    
                        <br>                                                                                                
                        <div class="form-group">
                            <label for="contact_email">Kontaktperson-mail</label>
                            <input type="text" class="form-control" id="contact_email" disabled>
                            </input>
                        </div>
                           <br>    
                        <div class="form-group">
                            <label for="contact_phone">Kontaktperson-tlf.</label>
                            <input type="text" class="form-control" id="contact_phone" disabled>
                            </input>
                        </div>    
                                            
                                        
                        
                        
                        </div>
                        
                        
                        
                        
                        
                        <br>
                        <h4>Fakturainfo</h4>
                        <hr>
                        <div class="form-group">
                            <label for="salesperson_code">Kundenr i navision</label>
                            <input type="text" class="form-control" id="nav_debitor_no">
                            </input>
                        </div>
                        <br />
                        <div class="form-group">
                            <label for="bill_to_email">Faktura-mail</label>
                            <input type="text" class="form-control" id="bill_to_email">
                            </input>
                        </div>
                        <br />                        
                        <div class="form-group">
                            <label for="salesperson_code">Sælgerkode i Navision</label>
                            <select class="form-control required-field" id="salesperson_code">
                            </select>
                        </div>
                        
                  
                  <br>
                          <div class="form-group">
                            <label for="has_contract">Forudbetaling</label>
                            <select class="form-control" id="prepayment">
                                <option value="1">Ja</option>
                                <option value="0">Nej</option>
                            </select>
                        </div> 
                      <br>  
                    <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                   <label for="invoice_fee">Fakturagebyr</label>
                        <select class="form-control" id="invoice_fee">
                            <option value="0">Nej</option>
                            <option value="1">Ja</option>
                        </select>
                                </div>
                            </div>
                            <div class="col-9" id="invoice_fee_value_group" style="display: none;">
                                <div class="form-group">
                         <label for="invoice_fee_value">Indtast beløb</label>
                        <input type="number" class="form-control" id="invoice_fee_value">
                                </div>
                            </div>
                        </div>                            
                        <br>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                       <label for="budget">Budget ekskl. moms (Fremgår på gaver)</label>
                                    <input type="number" class="form-control" id="budget" step="0.01">
                                </div>    
                                    
                            </div>
                               <div class="col-6">
                                <div class="form-group">
                                <br>
                                    <label for="flex_budget">Afkryds hvis der benytte variable budget</label>
                                     <input class="form-check-input" type="checkbox" value="" id="flex_budget" style=" transform: scale(2);margin: 10px;">  
                                     </div>
                                </div>
                         </div>
                            
                        
                        
                         <br>
                        
                        
                        
                        
                        <div class="form-group">
                            <label for="user_count">Antal medarbejdere</label>
                            <input type="number" class="form-control" id="user_count">
                        </div>
                         <br>
                        <div class="form-group">
                             <label for="present_count">Antal gavevalg (Inkl. undervalg og donationer)</label>
                            <input type="number" class="form-control" id="present_count">
                        </div>
                         <br>
                        
                        
                        <!-- Anja har bedt mig at genne feltet d. 11/6 -->
                        <div class="row" style="display: none">
                            <div class="col-3">
                                <div class="form-group">
                                   <label for="is_foreign">Udenlandsk kunde</label>
                         <select class="form-control is_foreign"  id="is_foreign">
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
                        
                        
                        
                          
                         <br>
                         <br>
                          <label for="payment_terms">Betalingsbetingelser</label>
                        <div class="form-group" >
                           
                      
                            <select class="form-control" id="payment_terms_proposed" style="max-width: 200px; font-size: 12px;">
                                <option  value="0">Betalingsbetingelser forslag</option>
                                <option value="1">8 dage netto</option>
                                <option value="2">10 dage netto</option>
                                <option value="3">14 dage netto</option>
                                <option value="4">30 dage netto</option>
                                <option value="5">60 dage netto</option>                          
                           </select>
                      
                            <textarea class="form-control" id="payment_terms" placeholder="Vælg betalingsbetingelser i dropdown eller skriv dem her"></textarea>
                    
                        </div>
                         <br>
                        <div class="form-group">
                              <label for="requisition_no">Kundens reference</label><span> (PO eller lignende)</span>
                            <input type="text" class="form-control" id="requisition_no"></input>
                   
                        </div>
                         <br>
                          <div class="form-group">
                            <label for="has_contract">Kontrakt/samhandelsaftale</label>
                            <select class="form-control" id="has_contract">
                                <option value="0">Nej</option>
                                <option value="1">Ja</option>
                           
                            </select>
                        </div> 
                         <br>  
                        <div class="form-group">
                            <label for="payment_special">Særlige krav til fakturering</label>
                            <select class="form-control" id="payment_special">
                               <option value="0">Nej</option>
                                <option value="1">Ja</option>
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
                            <input type="number" class="form-control required-field" id="valgshop_fee">
                        </div>
                        <br>
                         <div class="form-group">
                            <label for="environment_fee">Miljøgebyr</label>
                            <select class="form-control" id="environment_fee">
                                  <option value="1">Ja</option>
                                <option value="0">Nej</option>
                            </select>
                        </div>
                          <br>
                        
                        <h4>Levering og fragt</h4>
                        <hr>
                <!--
                        <div class="form-group">
                            <label for="delivery_date">Leveringsdato</label>
                            <input type="date" class="form-control" id="delivery_date">
                        </div>
             <br/ >
             -->
               <br/ >
               
            <div class="form-group">
                            <label for="delivery_date">Leveringsdato</label>
                                            <div class="description">
                              
        <p><span class="dot gray"></span> Denne dato er ledig, Ingen shops registeret endnu. </p>
        <p><span class="dot green"></span> Denne dato er ledig, flere shop er registeret. </p>
        <p><span class="dot yellow"></span> Denne dato er ledig, dog tæt på max antal.</p>
        <p><span class="dot red"></span> Hvis du vælger denne dato skal den godkendes af Susanne. Hvis du gemmer med denne dato vil der blive sendt en godkendelsesmail til Susanne.</p>
  <hr>              
                            <input type="text" readonly  class="form-control" id="delivery_date" placeholder="Vælg dato">
                            <div id="delivery_date1Info" class="mt-2"></div>                            
                        </div>
                             </div>
             <br/ >
               <br/ >
                  <div class="form-group">
                            <label for="flex_delivery_date">Fleksibel levering (Første dag og sidste dag )</label>
                            <div class="row">
                                 
                                <div class="col-6"><b>Første dag</b></div>
                                <div class="col-6"><b>Sidste dag</b></div>
                                
                            </div>
                            
                            <div class="row">
                                
                                <div class="col-6"><input type="date" class="form-control required-field" id="flex_start_delivery_date"></div>
                                <div class="col-6"><input type="date" class="form-control required-field" id="flex_end_delivery_date"></div>
                                
                            </div>
                            
                            
                        </div> 
                        <br>           
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
     <br/ >
                        
                        <div class="form-group">
                            <label for="handover_date">Udlevering af gaver ved kunden</label>
                            <input type="date" class="form-control" id="handover_date">
                        </div>
                        <br/ >
                        <div class="form-group">
                            <label for="multiple_deliveries">Flere leveringsadresser</label>
                            <select class="form-control" id="multiple_deliveries">
                                <option value="0">Nej</option>
                                <option value="1">Ja</option>
                            </select>
                        </div>
                       <br/ >  
                      
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
                               <div class="form-group">
                                <label>Retur type:</label>
                           <!--     <button id="resetPrivateReturType" class=" btn-secondary mt-2">Nulstil valg</button>  -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="privateReturType"  checked value="none">
                                    <label class="form-check-label" for="returGF">
                                        Ej taget stilling
                                    </label>
                                </div>
        
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="privateReturType"  value="gf">
                                    <label class="form-check-label" for="returGF">
                                        Retur til GF
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="privateReturVirksomhed" name="privateReturType" value="virksomhed" >
                                    <label class="form-check-label" for="returVirksomhed">
                                        Retur til virksomheden
                                    </label>
                                </div>
                                 <button id="privateReturVirksomhedAdress" class=" btn-secondary mt-2">Lev. adresse</button>
                            </div>
                            
                            
                            </div>
                            
                            
                            
                        </div>
                        
                        <br/ >
                        
                        
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
                            <br>
                        </div>                        
                
       <!--         
                        <div class="form-group">
                            <label for="delivery_terms">Leveringsbetingelser</label>
                            <textarea class="form-control" id="delivery_terms"></textarea>
                        </div>
           -->             
                        
           
<div class="row">
    <div class="col-3">
        <div class="form-group">
            <label for="deliveryprice_option">Leveringsbetingelser</label>
            <select class="form-control" id="deliveryprice_option" >
                <option value="0">Skal udfyldes</option>
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
                           <br>   
                        <div class="form-group">
                            <label for="delivery_note_external">Ekstern fragtnote (til fragtmand / ordrebekræftelse)</label>
                            <textarea class="form-control" id="delivery_note_external"></textarea>
                        </div>
                                                             <br>
                        <h4>DOT</h4>
                        <hr>
    <div class="row">
        <div class="col-3">
         <br>
            <div class="form-group">
                <label for="dot_use">Ønskes DOT</label>
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
                        <h4>Opbæring</h4>
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
                                <br>
                        <h4>Gaver</h4>
                        <hr>
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
                            <label for="handling_special">Speciel håndtering / særlig pak (Dette bør undgås)</label>
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
                 <!--  deaktiveret og erstattet af nedenstående komponent                   
                        <div class="form-group">
                            <label for="">Shop åben og luk</label>
                            <div class="row">
                                 
                                <div class="col-6"><b>Åben</b></div>
                                <div class="col-6"><b>Luk</b></div>
                            </div>
                            <div class="row">
                                
                                <div class="col-6"><input type="date" class="form-control " id="start_date"></div>
                                <div class="col-6"><input type="date" class="form-control " id="end_date"></div>
                            </div>
                          </div>
                        <br>
                       -->  
                          <div class="form-group">
                          <label for="">Shop åben og luk </label>
                              <div class="description">
                              
        <p><span class="dot gray"></span> Denne dato er ledig, Ingen shops registeret endnu. </p>
        <p><span class="dot green"></span> Denne dato er ledig, flere shop er registeret. </p>
        <p><span class="dot yellow"></span> Denne dato er ledig, dog tæt på max antal.</p>
        <p><span class="dot red"></span> Hvis du vælger denne dato skal den godkendes af Susanne. Hvis du gemmer med denne dato vil der blive sendt en godkendelsesmail til Susanne.</p>
  <hr>
                            <div class="form-group">
                                
                                <div class="row">
                                    <div class="col-6"><b>Åben</b></div>
                                    <div class="col-6"><b>Luk</b></div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="text" readonly  class="form-control" id="orderOpenCloseChopStartDate" placeholder="Vælg dato">
                                        <div id="orderOpenCloseChopStartDateInfo" class="mt-2"></div>
                                        
                                    </div>
                                    <div class="col-6">
                                        <input type="text" readonly class="form-control" id="orderOpenCloseChopEndDate" placeholder="Vælg dato">
                                        <div id="orderOpenCloseChopEndDateInfo"   class="mt-2" blace></div> 
                                    </div>
                                </div>
                            </div>
                            <div id="orderOpenCloseChopEventInfo" class="mt-3"></div>
                        </div>
                          </div>
          <br>              
                   
                        <div class="form-group">
                            <label for="deadline_testshop">Deadline testshop</label>
                            <input type="date" class="form-control" id="deadline_testshop">
                        </div>
                          <br>
                        <div class="form-group">
                            <label for="deadline_changes">Deadline rettelser</label>
                            <input type="date" class="form-control" id="deadline_changes">
                        </div>
                          <br>
                        <div class="form-group">
                            <label for="deadline_customerdata">Deadline materiale fra kunde</label>
                            <input type="date" class="form-control" id="deadline_customerdata">
                        </div>
                          <br>
                        <div class="form-group">
                            <label for="deadline_listconfirm">Deadline godkendelse af fordelingslister</label>
                            <input type="date" class="form-control" id="deadline_listconfirm">
                        </div>
                          <br>
                        <div class="form-group">
                            <label for="reminder_use">Brug reminders</label>
                            <select class="form-control" id="reminder_use">
                                <option value="0">Nej</option>
                                <option value="1">Ja</option>
                            </select>
                        </div>
                          <br>
                        <div id="group_reminder_use">
                        <div class="form-group" >
                            <label for="reminder_date">Dato for reminders</label>
                            <input type="date" class="form-control" id="reminder_date">
                        </div>
                          <br>
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
  <br>
<div class="form-group">
    <label for="user_username_note">Brugernavn bemærkning</label>
    <textarea class="form-control" id="user_username_note" rows="3"></textarea>
</div>
  <br>
<div class="form-group">
    <label for="user_password">Log på med (adgangskode)</label>
    <input type="text" class="form-control" id="user_password">
</div>
  <br>
<div class="form-group">
    <label for="user_password_note">Adgangskode bemærkning</label>
    <textarea class="form-control" id="user_password_note" rows="3"></textarea>
</div>
  <br>
<div class="form-group">
    <label for="deliverydate_receipt">Leveringsdato på kvittering</label>
    <input type="date" class="form-control" id="deliverydate_receipt">
</div>
  <br>
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
        <div class="form-group" id="language_names_list">
            <label for="">Skriv sprog</label><br>
            <label><input type="checkbox" name="norge"> Norge</label><br>
                <label><input type="checkbox" name="sverige"> Sverige</label><br>
                <label><input type="checkbox" name="tyskland"> Tyskland</label><br>
                <label><input type="checkbox" name="england"> England</label><br>
        </div>
         <br>
    </div>
       <br>
         <br>
    <div class="form-group">

    <label for="otheragreements_note">Øvrige aftaler med kunden</label>
    <textarea class="form-control" id="otheragreements_note" rows="3"></textarea>
     <button style="float: right;margin:10px; background-color: chartreuse;" type="button" class="save_button">Gem</button>
</div>           
</div>
    <input type="hidden" id="shop_id">
    <input type="hidden" id="id">

                           </form>
           <br>
                       

<div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addressModalLabel">Indtast Adresse og Kontaktinformation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="addressForm">
          <div class="form-group">
            <label for="street">Vej og nummer</label>
            <input type="text" class="form-control" id="street" required>
          </div>
            <div class="form-group">
           <label for="street">Adresse2</label>
            <input type="text" class="form-control" id="street2" >
          </div>
          <div class="form-group">
            <label for="postalCode">Postnummer</label>
            <input type="text" class="form-control" id="postalCode" required>
          </div>
          <div class="form-group">
            <label for="city">By</label>
            <input type="text" class="form-control" id="city" required>
          </div>
          <div class="form-group">
            <label for="country">Land</label>
            <input type="text" class="form-control" id="country" >
          </div>
          <div class="form-group">
            <label for="contactPerson">Kontaktperson</label>
            <input type="text" class="form-control" id="contactPerson" >
          </div>
          <div class="form-group">
            <label for="contactPhone">Kontaktperson telefon</label>
            <input type="tel" class="form-control" id="contactPhone" >
          </div>
          <div class="form-group">
            <label for="contactEmail">Kontaktperson email</label>
            <input type="email" class="form-control" id="contactEmail" >
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Luk</button>
        <button type="button" class="btn btn-primary" id="saveAddress">Gem</button>
      </div>
    </div>
  </div>
</div>

`;
    }
}