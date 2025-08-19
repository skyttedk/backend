// Alt aktiv kode ligger i filen pimPresentSync.js


class PimPresentSyncLayout {
   constructor() {   }
   main(props){


          return  ``+
            props.data.map( prop => {
               return `<div class="pim container"  id="pim-container-${prop.id}">
                            <div style='width:300px; height:50px;'>
                                <div  style=" width:230px; height:40px" class="pim shop-title inline" data-id="${prop.id}">${prop.name}</div>
                                 <div class="pim noInsyncState  syncStatus-${prop.isInSync}" data-id="${prop.id}" ></div>
                            </div>




                          <div class="pim sync-status" data-id="${prop.id}" id="pim-content-${prop.id}">
                                <table class="pim-table">

                                    <tr ><td>Gave med omtanke</td><td colspan="3"><div class="pim sync gmo status-${prop.sync.oko_present}" field-name="gmo" data-id="${prop.id}"></div></td></tr>
                                    <tr ><td>Logo</td><td colspan="3"><div class="pim sync logo status-${prop.sync.present}" field-name="logo" data-id="${prop.id}"></div></td></tr>
                                    <tr data-id="${prop.id}"><td>Billeder</td><td colspan="3"><div data-id="${prop.id}" class="pim sync images status-${prop.sync.presentMedia}" field-name="img"></div></td></tr>
                                    <tr><th>Sprog</th><th>Overskrift</th><th>Kort tekst</th><th>Lang tekst</th></tr>`+
                                        prop.sync.presentDescription.map( ele => {
                                            return `
                                                <tr lang-id="${ele.language_id}" data-id="${prop.id}">
                                                    <td>${ele.language}</td>
                                                    <td><div  class="pim sync text status-${ele.caption}" field-name="caption"></div></td>
                                                    <td><div class="pim sync text status-${ele.short_description}" field-name="short_description"></div></td>
                                                    <td><div class="pim sync text status-${ele.long_description}" field-name="long_description"></div></td>
                                                </tr> `
                                        }).join('') +`
                                    <tr >
                                   </table>
                                   <div><br><b>---- MODELLER / VARIANTER -----</b><br></div>
                                   `+
                                         prop.sync.model.map( eleModel => {
                                            if(eleModel.is_new == false){
                                             return `
                                                   <fieldset><legend>${eleModel.model_name}</legend><div>Varenr: ${eleModel.itemnr}</div><table class="pim-table"><tr><th>Sprog</th><th>Navn</th><th>Variant / farve </th><th>varenr</th><th>Billede</th></tr>` + eleModel.status.map( eleStatus => {
                                                    return `
                                                    <tr lang-id="${eleStatus.language_id}" present-id="${prop.id}" org-model-id="${eleModel.id}">
                                                    <td>${eleStatus.language}</td>
                                                    <td><div  class="pim sync model  status-${eleStatus.model_name.isSync}" field-name="model_name"></div></td>
                                                    <td><div class="pim sync model status-${eleStatus.model_no.isSync}" field-name="model_no"></div></td>
                                                    <td><div class="pim sync model status-${eleStatus.model_present_no.isSync}" field-name="model_present_no"></div></td>
                                                    <td><div class="pim sync model status-${eleStatus.media_path.isSync}" field-name="media_path"></div></td>
                                                    </tr>
                                                    `;
                                                }).join('') + `</table> </fieldset>  `

                                            } else {
                                               return `<fieldset><legend><span style='color:red;'>NY MODEL</span></legend><div>
                                                    <table class="pim-table">
                                                        <tr><td>Navn</td><td>${eleModel.data[0].model_name}</td></tr>
                                                        <tr><td>Variant / farve</td></td><td>${eleModel.data[0].model_no}</td></tr>
                                                        <tr><td>Varenr.:</td><td>${eleModel.data[0].model_present_no}</td></tr>
                                                        <tr><td></td><td><button class="pim newModel" data-id=${prop.id} org-model-id="${eleModel.data[0].model_id}">Opret Model</button></td></tr>
                                                    </table>

                                               </div></fieldset> `
                                            }


                                        }).join('') +`


                          </div></div> `
            }).join('')
   }

   searchMenu(){
        return '<div><input class="pim search" id="search" type="text" placeholder="Søg" /></div>';
   }
}

//return