export default class OrderlistTp {

static main(data)
{
    let path = "";
    let prefix = "";

    return `<table class="customTable">` +
        data.map((i) => {
            path = "https://findgaven.dk/gavevalg/";
            prefix = "DK-";

            if(i.language == 4) {
                path = "https://gavevalg.no/gavevalg/";
                prefix = "NO-";
            }
            if(i.language == 5) {
                path = "https://dinjulklapp.se/gavevalg/";
                prefix = "SE-";
            }

                return `<tr><td>${prefix}${i.title}</td><td><a href="${path}${i.link}" target="_blank">Link</a></td><td><input  onClick="this.select();" style="width:450px !important" type="text" value="${path}${i.link}"/></td></tr>`

        }).join('')+ `</table>`;
 
}



}