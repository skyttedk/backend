<?php
class paperPortalController  Extends baseController
{

    public function index()
    {
        $this->registry->template->show('paperPortal_view');
    }


    public function getCompany()
    {
        $shopId = $_POST["shopID"];

        try {
            // Find the company_id associated with the given shop_id
            $companyShop = CompanyShop::find('first', array(
                'conditions' => array('shop_id = ?', $shopId),
                'select' => 'company_id'
            ));

            if ($companyShop) {
                $result = array(
                    'company_id' => $companyShop->company_id
                );
                response::success(json_encode($result));
            } else {
                response::error("No company associated with the given shop_id");
            }
        } catch (Exception $e) {
            response::error("Error fetching company data: " . $e->getMessage());
        }
    }

    public function updateSyncStatus()
    {
        $userId = $_POST["userId"];

        try {
            // Update paper_order table
            $paperOrders = Paperorder::find('all', array(
                'conditions' => array('user_id = ?', $userId)
            ));

            $updatedOrders = 0;
            foreach ($paperOrders as $order) {
                $order->is_sync = 1;
                if ($order->save()) {
                    $updatedOrders++;
                }
            }

            // Update paper_user_attribute table
            $paperUserAttributes = PaperUserAttribute::find('all', array(
                'conditions' => array('user_id = ?', $userId)
            ));

            $updatedAttributes = 0;
            foreach ($paperUserAttributes as $attribute) {
                $attribute->is_sync = 1;
                if ($attribute->save()) {
                    $updatedAttributes++;
                }
            }

            // Check if any records were updated
            if ($updatedOrders > 0 || $updatedAttributes > 0) {
                response::success(json_encode([
                    'message' => 'Sync status updated successfully',
                    'updated_orders' => $updatedOrders,
                    'updated_attributes' => $updatedAttributes
                ]));
            } else {
                response::error("No records found for the given user_id");
            }
        } catch (Exception $e) {
            response::error("Error updating sync status: " . $e->getMessage());
        }
    }


    public function updateSettings(){
        $settings =  json_encode($_POST);
        $shopID = $_POST["shopID"];

        $shop = Shop::find($shopID);
        $shop->paper_settings = $settings;
        $res = $shop->save();
        response::success(json_encode($res));
    }
    public function readSettings(){
        $shopID = $_POST["shopID"];

        $shop = Shop::find($shopID);

        if ($shop) {
            $result = [
                'shop_id' => $shopID,
                'paper_settings' => $shop->paper_settings
            ];
            response::success(json_encode($result));
        } else {
            response::error("Shop ikke fundet");
        }
    }


    public function readData11(){
        $shopID = 6490;
        $PaperUserAttribute = PaperUserAttribute::find('all', array(
            'conditions' => array(
                'shop_id = ?', $shopID
            )
        ));
        response::success(json_encode($PaperUserAttribute));
    }
    public function readPresents(){
        $shopID = $_POST["shopID"];
        $presentModels = PresentModel::find('all', array(
            'joins' => 'JOIN `present` p ON present_model.present_id = p.id',
            'conditions' => array(
                'p.shop_id = ? AND p.active = ? AND p.deleted = ? AND present_model.active = ? AND present_model.is_deleted = ? AND present_model.language_id = ?',
                $shopID, 1, 0, 0, 0, 1
            ),
            'select' => 'present_model.*',
             'order' => 'present_model.fullalias ASC'
        ));
        response::success(json_encode($presentModels));
    }

    public function createWorker() {
        $userId = $_POST["user_id"];
        $shopId = $_POST["shop_id"];

        try {
            // Log POST data
            $workerLog = new Paperlog();
            $workerLog->shop_id = $shopId;
            $workerLog->user_id = $userId;
            $workerLog->operation = "createWorker";
            $workerLog->data = json_encode($_POST);
            $workerLog->save();

            // save attr
            foreach ($_POST["attr"] as $key => $value) {
                $PaperUserAttribute = new PaperUserAttribute();
                $PaperUserAttribute->user_id = $userId;
                $PaperUserAttribute->attribute_id = $key;
                $PaperUserAttribute->attribute_value = $value;
                $PaperUserAttribute->shop_id = $shopId;
                $PaperUserAttribute->save();
            }

            // save present
            $Paperorder = new Paperorder();
            $Paperorder->user_id    = $userId;
            $Paperorder->shop_id    = $shopId;
            $Paperorder->present_id = $_POST["present"]["presentid"];
            $Paperorder->model_id   = $_POST["present"]["model_id"];
            $res = $Paperorder->save();

            response::success(json_encode($res));
        } catch (Exception $e) {
            response::error("Fejl ved oprettelse af worker: " . $e->getMessage());
        }
    }

    public function updateWorker() {
        $userId = $_POST["user_id"];
        $shopId = $_POST["shop_id"];

        try {
            // Log POST data
            $workerLog = new Paperlog();
            $workerLog->shop_id = $shopId;
            $workerLog->user_id = $userId;
            $workerLog->operation = "updateWorker";
            $workerLog->data = json_encode($_POST);
            $workerLog->save();

            // Opdater eller opret attributter
            foreach ($_POST["attr"] as $attributeId => $attributeValue) {
                $paperUserAttribute = PaperUserAttribute::find(array(
                    'conditions' => array(
                        'user_id = ? AND shop_id = ? AND attribute_id = ?',
                        $userId, $shopId, $attributeId
                    )
                ));

                if (!$paperUserAttribute) {
                    $paperUserAttribute = new PaperUserAttribute();
                    $paperUserAttribute->user_id = $userId;
                    $paperUserAttribute->shop_id = $shopId;
                    $paperUserAttribute->attribute_id = $attributeId;
                }

                $paperUserAttribute->attribute_value = $attributeValue;
                $paperUserAttribute->save();
            }

            // Opdater eller opret order
            $paperOrder = Paperorder::find(array(
                'conditions' => array('user_id = ? AND shop_id = ?', $userId, $shopId)
            ));

            if (!$paperOrder) {
                $paperOrder = new Paperorder();
                $paperOrder->user_id = $userId;
                $paperOrder->shop_id = $shopId;
            }

            $paperOrder->present_id = $_POST["present_id"];
            $paperOrder->model_id = $_POST["model_id"];
            $paperOrder->save();

            response::success(json_encode($paperOrder));
        } catch (Exception $e) {
            response::error("Fejl ved opdatering af worker: " . $e->getMessage());
        }
    }
    public function deleteWorker() {
        $userId = $_POST["user_id"];
        $shopId = $_POST["shop_id"];

        try {
            // Log deletion attempt
            $workerLog = new Paperlog();
            $workerLog->shop_id = $shopId;
            $workerLog->user_id = $userId;
            $workerLog->operation = "delete";
            $workerLog->data = json_encode([
                'action' => 'delete',
                'user_id' => $userId,
                'shop_id' => $shopId
            ]);
            $workerLog->save();

            // Find and delete PaperUserAttributes
            $attributes = PaperUserAttribute::find('all', [
                'conditions' => ['user_id = ? AND shop_id = ?', $userId, $shopId]
            ]);
            foreach ($attributes as $attribute) {
                $attribute->delete();
            }

            // Find and delete Paperorder
            $order = Paperorder::find('first', [
                'conditions' => ['user_id = ? AND shop_id = ?', $userId, $shopId]
            ]);
            if ($order) {
                $order->delete();
            }

            // Check if anything was actually deleted
            if (empty($attributes) && !$order) {
                response::error("Ingen worker fundet med de angivne kriterier.");
                return;
            }

            response::success(json_encode([
                'message' => 'Worker slettet succesfuldt',
                'deleted_attributes' => count($attributes),
                'deleted_order' => $order ? 1 : 0
            ]));
        } catch (Exception $e) {
            response::error("Fejl ved sletning af worker: " . $e->getMessage());
        }
    }

    public function importWorkerList()
    {
        $data = $_POST["data"];
        $shopId = $_POST["shopID"];

        try {
            foreach ($data as $index => $workerData) {
                $userId = $this->generateRandomString();

                // Log worker creation
                $workerLog = new Paperlog();
                $workerLog->shop_id = $shopId;
                $workerLog->user_id = $userId;
                $workerLog->operation = "createWorker";
                $workerLog->data = json_encode($workerData);
                if (!$workerLog->save()) {
                    throw new Exception("Kunne ikke gemme, fejl i lineje" . ($index + 1));
                }

                // Save attributes
                foreach ($workerData as $key => $value) {
                    if ($key !== 'Gave' && $key !== 'model_id' && $key !== 'present_id') {
                        $PaperUserAttribute = new PaperUserAttribute();
                        $PaperUserAttribute->user_id = $userId;
                        $PaperUserAttribute->attribute_id = $key;
                        $PaperUserAttribute->attribute_value = $value;
                        $PaperUserAttribute->shop_id = $shopId;
                        if (!$PaperUserAttribute->save()) {
                            throw new Exception("Kunne ikke gemme  fejl i linje #" . ($index + 1));
                        }
                    }
                }

                // Save order
                $Paperorder = new Paperorder();
                $Paperorder->user_id    = $userId;
                $Paperorder->shop_id    = $shopId;
                $Paperorder->alias    =  $workerData["Gave"] ?? null;

                if (!isset($workerData["present_id"])) {
                    throw new Exception("Fejl i alias linje " . ($index + 1));
                }
                $Paperorder->present_id = $workerData["present_id"];

                if (!isset($workerData["model_id"])) {
                    throw new Exception("Fejl i alias linje" . ($index + 1));
                }
                $Paperorder->model_id   = $workerData["model_id"];

                if (!$Paperorder->save()) {
                    throw new Exception("Kunne ikke gemme ordre, fejl i linje" . ($index + 1));
                }
            }

            response::success(json_encode(["message" => "Alle blev importeret succesfuldt"]));
        } catch (Exception $e) {
            response::error("Fejl ved import  " . $e->getMessage());
        }
    }

    public function readWorkerList()
    {
        $shopID = $_POST["shopID"];
        $results = PaperUserAttribute::find('all', array(
            'select' => 'paper_user_attribute.user_id, 
                 paper_user_attribute.shop_id, 
                 paper_user_attribute.attribute_id, 
                 paper_user_attribute.attribute_value,
                  paper_order.is_sync,
                 paper_order.present_id, 
                 paper_order.model_id',
            'joins' => array(
                'LEFT JOIN paper_order ON paper_user_attribute.user_id = paper_order.user_id AND paper_user_attribute.shop_id = paper_order.shop_id'
            ),
            'conditions' => array('paper_user_attribute.shop_id = ?', $shopID),
            'order' => 'paper_user_attribute.user_id ASC, paper_order.id DESC'
        ));

        $restructuredData = [];

        foreach ($results as $result) {
            $userId = $result->user_id;

            if (!isset($restructuredData[$userId])) {
                $restructuredData[$userId] = [
                    'user_id' => $userId,
                    'shop_id' => $result->shop_id,
                    'is_sync' => $result->is_sync,
                    'attributes' => [],
                    'order' => [
                        'present_id' => $result->present_id,
                        'model_id' => $result->model_id

                    ]
                ];
            }

            $restructuredData[$userId]['attributes'][$result->attribute_id] = $result->attribute_value;
        }

        // Konverter til numerisk indekseret array, hvis nødvendigt
        $restructuredData = array_values($restructuredData);

        // Returner de omstrukturerede data som JSON
        response::success(json_encode($restructuredData));
    }
    public function readWorkerListNoSync()
    {
        $shopID = $_POST["shopID"];
        $results = PaperUserAttribute::find('all', array(
            'select' => 'paper_user_attribute.user_id, 
                 paper_user_attribute.shop_id, 
                 paper_user_attribute.attribute_id, 
                 paper_user_attribute.attribute_value, 
                 paper_order.present_id, 
                 paper_order.model_id',
            'joins' => array(
                'LEFT JOIN paper_order ON paper_user_attribute.user_id = paper_order.user_id AND paper_user_attribute.shop_id = paper_order.shop_id'
            ),
            'conditions' => array('paper_user_attribute.shop_id = ? and paper_user_attribute.is_sync = 0 and paper_order.is_sync = 0 ', $shopID),
            'order' => 'paper_user_attribute.user_id ASC, paper_order.id DESC'
        ));

        $restructuredData = [];

        foreach ($results as $result) {
            $userId = $result->user_id;

            if (!isset($restructuredData[$userId])) {
                $restructuredData[$userId] = [
                    'user_id' => $userId,
                    'shop_id' => $result->shop_id,
                    'attributes' => [],
                    'order' => [
                        'present_id' => $result->present_id,
                        'model_id' => $result->model_id
                    ]
                ];
            }

            $restructuredData[$userId]['attributes'][$result->attribute_id] = $result->attribute_value;
        }

        // Konverter til numerisk indekseret array, hvis nødvendigt
        $restructuredData = array_values($restructuredData);

        // Returner de omstrukturerede data som JSON
        response::success(json_encode($restructuredData));
    }

    public function getfieldSettings(){
        $shopID = $_POST["shopID"];

        $ShopAttribute = ShopAttribute::find('all', array(
            'conditions' => array(
                'shop_id = ? AND 
                is_username = ? AND
                is_password = ? AND
                is_email = ? AND
                name != ?',

                $shopID,
                0,0,0,
                'Gaveklubben tilmelding'
            ),
            'order' => '`index` ASC'
        ));
        response::success(json_encode($ShopAttribute));
    }
    public function getAllfieldSettings(){
        $shopID = $_POST["shopID"];

        $ShopAttribute = ShopAttribute::find('all', array(
            'conditions' => array(
                'shop_id = ?',$shopID
            )
        ));
        response::success(json_encode($ShopAttribute));
    }




    public function login()
    {
        $password = $_POST["password"];
        $username = $_POST["username"];


        $options = array('username' => $username, 'password' => $password);
        $company = Company::find('all', $options);
        if (sizeof($company) == 0) {
            response::success(json_encode([]));
        } else {
            $companyID = $company[0]->attributes["id"];

            $options = array('company_id' => $companyID);
            $CompanyShop = CompanyShop::find('all', $options);
            $shopID = $CompanyShop[0]->attributes["shop_id"];
            $Shop = Shop::find($shopID);
            $token = $Shop->attributes["token"];

            response::success(json_encode(["token" => $token, "shopID" => $shopID]));

        }
    }
    private function generateRandomString($length = 20) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}