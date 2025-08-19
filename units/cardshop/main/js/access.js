/*
Oversig hvor access benyttes
1: orderform.class.js => OrderFormSalespersons
2: orderform.class.js => OrderFormAdditionalProducts
3: orderform.class.js => OrderFormAdditionalProducts
4: orderform.class.js => CreateOrder
5: orderform.class.js => CreateOrder
6: orderform.class.js => CreateOrder
7: orderform.class.js => CreateOrder
8: orderform.class.js => CreateOrder
9: orderform.class.js => CreateOrder
10 cards.class.js => accessControl

//if(window.USERID == 86 || window.USERID == 63 || window.USERID == 50 || window.USERID == 110 || window.USERID == 138 || window.USERID == 155){
 */





export default class Access {
    constructor() {   }
    validate(userID,functionID){
        const accessSettings = {
            "u86": [1,2,3,4,5,6,7,8,9,10,11],
            "u63": [1,2,3,4,5,6,7,8,9,10,11],
            "u50": [1,2,3,4,5,6,7,8,9,10,11],
            "u110": [1,2,3,4,5,6,7,8,9,10,11],
            "u138": [1,2,3,4,5,6,7,8,9,10,11],
            "u155": [1,2,3,4,5,6,7,8,9,10,11],
            "u144": [1,2,3,4,5,6,7,8,9,10,11],
            "u196": [1,2,3,4,5,6,7,8,9,10,11],
            "u139": [1,2,3,4,5,6,7,8,9,10,11],
            "u66": [1,2,3,4,5,6,7,8,9,10,11],
            "u175":[1,2,3,4,5,6,7,8,9,10,11],
            "u146":[1,2,3,4,5,6,7,8,9,10,11],
            "u81":[1,2,3,4,5,6,7,8,9,10,11],
            "u145":[1,2,3,4,5,6,7,8,9,10,11],
            "u190":[1,2,3,4,5,6,7,8,9,10,11],
            "u70":[1,2,3,4,5,6,7,8,9,10,11],
            "u116":[1,2,3,4,5,6,7,8,9,10,11],
            "u142":[1,2,3,4,5,6,7,8,9,10,11],
            "u178":[1,2,3,4,5,6,7,8,9,10,11],
            "u188":[1,2,3,4,5,6,7,8,9,10,11],
            "u191":[1,2,3,4,5,6,7,8,9,10,11],
            "u203":[1,2,3,4,5,6,7,8,9,10,11],
            "u268":[1,2,3,4,5,6,7,8,9,10,11],
            "u179":[1,2,3,4,5,6,7,8,9,10,11],
            "u124":[1,2,3,4,5,6,7,8,9,10,11],
            "u162":[1,2,3,4,5,6,7,8,9,10,11],
            "u244":[1,2,3,4,5,6,7,8,9,10,11],
            "u248":[1,2,3,4,5,6,7,8,9,10,11],
            "u199":[1,2,3,4,5,6,7,8,9,10,11],
            "u289":[1,2,3,4,5,6,7,8,9,10,11],
            "u288":[1,2,3,4,5,6,7,8,9,10,11],
            "u287":[1,2,3,4,5,6,7,8,9,10,11],
            "u286":[1,2,3,4,5,6,7,8,9,10,11],
            "u285":[1,2,3,4,5,6,7,8,9,10,11],
            "u277":[1,2,3,4,5,6,7,8,9,10,11],
            "u68":[1,2,3,4,5,6,7,8,9,10,11],
            "319":[1,2,3,4,5,6,7,8,9,10,11],
            "u338": [1,2,3,4,5,6,7,8,9,10,11]

        };

        let returnVal = false;
        if(accessSettings["u"+userID] != undefined){
           returnVal = accessSettings["u"+userID].includes(functionID);
        }
        return returnVal;

    }

}