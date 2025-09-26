
import Base from '../../main/js/base.js';
import tpDemoshop from '../tp/demoshop.tp.js';



export default class Demoshop extends Base {
    constructor() {
        super();
        this.layout();

    }
    layout()
    {
        super.OpenModal("Demo adgang",tpDemoshop.main(this.data()),"");
    }
    data(){
        //    https://gavevalg.no/
        let dataDk = [
            {title:'Luksusgavekortet 200',link:'d-lux200',language:"1"},
            {title:'Luksusgavekortet 400',link:'d-lux400',language:"1"},
            {title:'Luksusgavekortet 640',link:'d-lux640',language:"1"},
            {title:'Luksusgavekortet 800',link:'d-lux800',language:"1"},
            {title:'Julegavekortet 560',link:'d-jgk560',language:"1"},
            {title:'Julegavekortet 720',link:'d-jgk720',language:"1"},
            {title:'24gaver 400',link:'d-24g400',language:"1"},
            {title:'24gaver 560',link:'d-24g560',language:"1"},
            {title:'24gaver 640',link:'d-24g640',language:"1"},
            {title:'Guldgavekortet 800',link:'d-guld800',language:"1"},
            {title:'Guldgavekortet 1040',link:'d-guld1040',language:"1"},
            {title:'Dr&oslash;mmegavekortet 200',link:'d-drom200',language:"1"},
            {title:'Dr&oslash;mmegavekortet 300',link:'d-drom300',language:"1"},
            {title:'Designjulegaven 640',link:'d-design640',language:"1"},
            {title:'Designjulegaven 960',link:'d-design960',language:"1"},
            {title:'Julegavekortet 300',link:'n-demo300',language:"4"},
            {title:'Julegavekortet 400',link:'n-demo400',language:"4"},
            {title:'Julegavekortet 640',link:'n-demo600',language:"4"},
            {title:'Julegavekortet 800',link:'n-demo800',language:"4"},
            {title:'Gullgavekortet 1000',link:'n-demo1000',language:"4"},
            {title:'Gullgavekortet 1200',link:'n-demo1200',language:"4"},
            {title:'Gullgavekortet 2000',link:'n-demo2000',language:"4"},
            {title:'24julklappar 300',link:'julklapp300demo',language:"5"},
            {title:'24julklappar 400',link:'julklapp400demo',language:"5"},
            {title:'24julklappar 600',link:'julklapp600demo',language:"5"},
            {title:'24julklappar 800',link:'julklapp800demo',language:"5"},


        ];
        let dataNO = [
            {title:'Julegavekortet 300',link:'n-demo300',language:"4"},
            {title:'Julegavekortet 400',link:'n-demo400',language:"4"},
            {title:'Julegavekortet 640',link:'n-demo600',language:"4"},
            {title:'Julegavekortet 800',link:'n-demo800',language:"4"},
            {title:'Gullgavekortet 1000',link:'n-demo1000',language:"4"},
            {title:'Gullgavekortet 1200',link:'n-demo1200',language:"4"},
            {title:'Gullgavekortet 2000',link:'n-demo2000',language:"4"}

        ];
        let dataSE = [
            {title:'24julklappar 300',link:'julklapp300demo',language:"5"},
            {title:'24julklappar 400',link:'julklapp400demo',language:"5"},
            {title:'24julklappar 600',link:'julklapp600demo',language:"5"},
            {title:'24julklappar 800',link:'julklapp800demo',language:"5"},

        ]
        switch(window.LANGUAGE) {
            case 1:
                return dataDk;
            break;
            case 4:
                return dataNO;
            break;
            case 5:
                return dataSE;
            break;
            default:
                return [];
        }


   

        return dataDk;
    }

  /***  Layout logic   */


  /***  Bizz logic   */


}
