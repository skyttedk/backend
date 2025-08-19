import Base from './base.js?v=123';


export default class Nav extends Base {
    constructor() {
        super();
    }
    async getCvrDataFromNAV(cvr){
        return new Promise(resolve => {
                $.post( BASEURL+"navision/customerlist/searchcvr/"+window.LANGUAGE+"/"+cvr, function(response){
                    resolve(response);
                })
        })
    }
    async SearchNameFromNAV(name){
        return new Promise(resolve => {
                $.post( BASEURL+"navision/customerlist/searchname/"+window.LANGUAGE+"/"+name, function(response){
                    resolve(response);
                })
        })
    }


}





