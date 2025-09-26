import Base from './base.js?v=123';

export default class External extends Base {
    constructor() {
        super();
    }
    async getCvrData(cvr){
        return new Promise(resolve => {
            $.post( BASEURL+"external/cvrsearch/cvr/1/"+cvr, function(response){
                 resolve(response);
            })
        })
    }


}


