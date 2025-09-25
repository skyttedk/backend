\router::$systemUser
https://github.com/php-activerecord/activerecord

$faneId =  $_POST["tabId"];
$userId =  $_POST["user"];
if($userId == "alle"){
$options = array('fane' => $faneId);
} else {
$options = array('fane' => $faneId,'valgshopansvarlig' =>$userId,);
}
$shopboard = Shopboard::find('all', $options);
response::success(make_json("shop", $shopboard));
----------------------------------------------------------------
$mailqueue = new MailQueue($data);
$mailqueue->sender_name =  'Gavefabrikken';
$mailqueue->sender_email = 'Gavefabrikken@gavefabrikken.dk';
$mailqueue->category = $category;
$mailqueue->save();


// ****************** sikkerhed
$this->registry->template->userPermission = "";
$this->registry->template->userPermission = UserTabPermission::find('all', array('conditions' => "systemuser_id = ".intval($_SESSION["syslogin".GFConfig::SALES_SEASON])));
$this->registry->template->show('shop_view');

// **********************mail
$i=0;
$orders = Order::all(array('shop_id' => $_POST['shop_id'],'registered' => 0));

foreach($orders as $order)
{
//find email
$userattributes = UserAttribute::all(array('shopuser_id' => $order->shopuser_id));
foreach($userattributes as $attribute)
{
if($attribute->is_email)
$email = $attribute->attribute_value;
}
//dan link
$shop = Shop::find($order->shop_id);
$link ='http://system.gavefabrikken.dk/gavevalg/'.$shop->link;
// opret mail
$mailqueue = new MailQueue();
$mailqueue->sender_name  = 'Gavefabrikken';
$mailqueue->sender_email = 'info@gavefabrikken.one';
$mailqueue->recipent_email = $email;
$mailqueue->subject ='Du skal hente din gave';
$mailqueue->user_id =  $order->shopuser_id;
$mailqueue->body ="Du mangler at afhente din gave.";
$mailqueue->save();

}



$dummy = [];
response::success(make_json("result",$dummy));
response::error
System::connection()->commit();
System::connection()->transaction();
-----------------------------------------
$list = PresentModel::find('all', array(
'joins' => array('INNER JOIN present ON present.id = present_model.present_id'),
'conditions' => array(
'present.shop_id = ? AND present_model.language_id = ? AND present_model.model_present_no = ?',
7376, 1, '139157A210'
),
'select' => 'present_model.*'
));
return $list;

------------------------------------------------
return new Promise(function(resolve, reject) {
$.ajax(
{
url: 'index.php?rt=bi/loadDataForChart',
type: 'POST',
dataType: 'json',
data: {}
}).done(function(res) {
if(res.status == 0) { resolve(res) }
else { resolve(res) }
})
})

--------------
} catch (\Exception $e) {
echo "Exception during token check: ".$e->getMessage()."<br>File: ".$e->getFile()." @ ".$e->getLine();
}

\response::success(make_json("result", $cards,array("except" => array("shop_attributes","order","order_details","user_attributes"))));

----------------------------

$token = $_POST['token']; // Antager at token kommer fra POST

$list = ShopAddress::find('all', array(
'select' => 'shop_address.*',
'joins' => array(
'INNER JOIN shop ON shop.id = shop_address.shop_id',
'INNER JOIN navision_location ON navision_location.code = shop.reservation_code'
),
'conditions' => array(
'navision_location.token = ?',
$token
),
'order' => 'shop_address.shop_id ASC'
));
-------------------------
backend:
public function update()
{
    try {
        $groupId = $_POST['group_id'];           // f.eks. '564'
        // Find record
        $record = PresentationGroup::find_by_group_id($groupId);
        if (!$record) {
                $record = new PresentationGroup();
                $record->group_id    = $groupId;
            }
       foreach ($_POST as $field => $value) {
            if ($field != 'group_id' ) {
            $record->$field = $value;
            }
        }
        response::success(json_encode($record));
    } catch (Exception $e) {
        response::error("Fejl ved opdatering: " . $e->getMessage());
    }
}
Frontend:
    _this.updatePresentationChildType = function(type){
        $.post("index.php?rt=presentationGroup/update", {group_id: _this.presentId,type:type}, function(returData, status){
            if(returData.status==0){ alert(returData.message); return; }
            alert("jepper")
        },"json")
    }

     $sourceCopyOf = Present::find('all', array(
            'select' => 'DISTINCT copy_of',
            'conditions' => array(
                'shop_id = ? AND copy_of NOT IN (SELECT copy_of FROM present WHERE shop_id = ?)',
                $source, $target
            )
        ));
------------------
det er kvitterings add on der har dynamisk includerieng af js fil