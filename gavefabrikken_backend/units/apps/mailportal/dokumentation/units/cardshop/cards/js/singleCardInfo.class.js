import Base from '../../main/js/base.js';
import tpSingleCard from '../tp/singleCard.tp.js';


export default class SingleCard extends Base {
    constructor(shopuserID) {
        super();
        this.shopuserID = shopuserID;

    }
    async showCardInfo(){
        //alert(this.shopuserID)
        let shopuser = await super.post("cardshop/cards/getShopuserData/"+this.shopuserID);
        let orderHistory = await super.post("cardshop/cards/getCardOrderHistory/"+this.shopuserID);
        let html = tpSingleCard.cardInfo(shopuser,orderHistory);
        super.OpenRightPanel("Det oprindelige kort",html,"");


    }
}