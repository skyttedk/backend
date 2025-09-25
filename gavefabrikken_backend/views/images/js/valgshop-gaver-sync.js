console.log("valgshop-gaver-sync.js")


function syncGetUpdateCount()
{
    $.post("index.php?rt=shopItemSync/countSyncItemsNo", {shopID: _shopId}, function(returData, status){
        console.log(returData)
    },"json")
}