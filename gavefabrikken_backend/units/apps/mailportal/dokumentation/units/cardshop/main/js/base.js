
import Ajax from './ajax.js?v=123';
import SystemMsg from './systemMsg.js?v=123';
import Components from './components.js?v=123';

export default class Base extends Classes([SystemMsg,Ajax,Components]) {
    constructor() {
        super();
        this.characters ='abcdefghijklmnopqrstuvwxyzjklmnopqrstuvwxyzabcdefghijklmno';
    }
    RamdomString(length = 16){
       let result = ' ';
        const charactersLength = this.characters.length;
        for ( let i = 0; i < length; i++ ) {
            result += this.characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result.replace(/\s/g, '');
    }
}
function Classes(bases) {
    class Bases {
      constructor() {
        bases.forEach(base => Object.assign(this, new base()));
      }
    }
    bases.forEach(base => {
      Object.getOwnPropertyNames(base.prototype)
      .filter(prop => prop != 'constructor')
      .forEach(prop => Bases.prototype[prop] = base.prototype[prop])
    })
    return Bases;
  }