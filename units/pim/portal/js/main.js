window.BASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/";
var JSBASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/units/pim/";
window.LANGUAGE = "";
window.VERSION = "1.1.1";
window.USERID = USERID;
window.SHOPID = SHOPID;
import Base from '../../main/js/base.js';
import TableComponent from './table.js';
export default class PimMain extends Base {
    constructor() {
        super();
        this.Table = new TableComponent;
        this.InitTable();
    }
    InitTable(){
        this.Table.init();
    }

}


$( document ).ready(function() {
    var PIM = new PimMain();
});