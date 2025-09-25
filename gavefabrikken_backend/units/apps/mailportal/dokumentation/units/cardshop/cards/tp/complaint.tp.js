export default class Complaint {
    static demo(){
        return `

        `;
    }
    static element(masterData){

      return `
         <br><textarea id="complaintTxt" rows="10" cols="80">${masterData}</textarea>  <br>
        `;
    }
    static save(){
      return `
         <button class="btn btn-primary shadow-none" id="saveComplaint">Save</button>
        `;
    }
}