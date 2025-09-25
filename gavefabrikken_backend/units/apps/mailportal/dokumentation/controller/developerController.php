<?php
// Controller company
// Date created  Mon, 14 Mar 2016 14:11:11 +0100
// Created by Bitworks
class developerController Extends baseController {
  public function Index() {
    $this->registry->template->show('kss_test');
//   $this->CreatePresentForm();
//   $this->createTestPresent();
  }
  public function GetSystemUsers() {
    try {
        if(isset($_POST['name'])) {
        if($_POST['name']=="kim")
          throw new Exception('error by choice!');
    }
      $systemusers = systemUser::readSystemUsers();
      $options = array('only' => array('id', 'name', 'username', 'admin', 'active'));
      response::success(make_json("users",$systemusers, $options));
    }
    catch (Exception $ex) {
      response::error($ex->getMessage());
    }
  }
  public function CreatePresentForm() {
    try {
      system::connection()->transaction();
      echo company::first()->to_json() . '<br><br><br><br>';
      $companies = company::all();
      echo json_encode(array_values($companies));
      //throw new Exception('error by choice!');
      system::connection()->commit();
    }
    catch (Exception $ex) {
      system::connection()->rollback();
      echo $ex->getMessage();
      var_dump(debug_backtrace());
    }

  }
  public function createTestPresent() {
    try {
      system::connection()->transaction();
      $present = new present();
      $present->name = 'Test Present';
      $present->internal_name = 'Test Present';
      $present->save();
      $present->name = 'Test Present ' . $present->id;
      $present->save();
      for ($i = 0; $i < 10; $i++) {
        $present->addPicture('some picture');
      }
      $present->addDescription(1, 'some description');
      $present->addDescription(2, 'some description');
      $present->addDescription(3, 'some description');
      system::connection()->commit();
      echo 'Created test present:' . $present->to_string();
    }
    catch (Exception $ex) {
      system::connection()->rollback();
      echo $ex->getMessage();
    }
  }


    public function testorders()
    {

        if(!isset($_GET["token"]) || $_GET["token"] != "df4jhdjsksdfjh3jfj2hjdjsbndnqqq") {
            echo "No access"; return;
        }

        return;

        $shopid = 574;
        $companyid = 299;
        $noSelectionPercent = 10;

        // Load shopusers
        $shopusers = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE shop_id = ".intval($shopid)." && is_demo = 0 && blocked = 0 && company_id = ".intval($companyid));

        // Load presents
        $presentmodellist = PresentModel::find_by_sql("SELECT present.*, present_model.* FROM `present`, present_model WHERE present_model.present_id = present.id && shop_id = ".intval($shopid)." && language_id = 1");

        // Load user attributes
        $userattributes = UserAttribute::find_by_sql("SELECT * FROM user_attribute WHERE shop_id = ".intval($shopid));
        $userdata = array();
        foreach($userattributes as $userattribute) {
            if(!isset($userdata[$userattribute->shopuser_id])) $userdata[$userattribute->shopuser_id] = array();
            $userdata[$userattribute->shopuser_id][] = $userattribute;
        }

        //echo "<pre>".print_r($shopusers,true)."</pre>";

        // Process each shop user
        foreach($shopusers as $shopuser)
        {

            echo "<br>Processing shopuser: ".$shopuser->id." - ".$shopuser->username." - ";

            // Do not select present
            if(rand(1,100) <= $noSelectionPercent) {

                echo "<br>DO NOT SELECT";
                Order::table()->delete(array('shopuser_id' => $shopuser->id));

            }

            // Select present
            else
            {

                $presentindex = array_rand($presentmodellist);
                $presentmodel = $presentmodellist[$presentindex];

                echo "<br>SELECT PRESENT - ".$presentmodel->nav_name." - ".$presentmodel->model_name;

                // Construct data
                $orderData = array(
                    "shopId" => $shopid,
                    "userId" => $shopuser->id,
                    "presentsId" => $presentmodel->present_id,
                    "model_id" => $presentmodel->model_id,
                    "model" => $presentmodel->model_name.'###'.$presentmodel->model_no,
                    "modelData" => $presentmodel->model_present_no,
                    "_attributes" => array()
                );

                foreach($userdata[$shopuser->id] as $userattribute) {
                    if($userattribute->is_email && $userattribute->attribute_value == "") $userattribute->attribute_value = $shopuser->id."@gavefabrikken.dk";
                    $orderData["_attributes"][] = array("feltKey" => $userattribute->attribute_id,"feltVal" => $userattribute->attribute_value);
                }
                $orderData["_attributes"] = json_encode($orderData["_attributes"]);

                echo "<pre>".print_r($orderData,true)."</pre>";

                // Create order
                $order = Order::createOrder($orderData);
                //echo "<pre>".print_r($order,true)."</pre>";


            }


            echo "<br><br>";


            /*
                      array(7) {
                              ["shopId"]=>
                      string(3) "899"
                              ["userId"]=>
                      string(6) "796291"
                              ["presentsId"]=>
                      string(4) "4878"
                              ["model_id"]=>
                      string(1) "9"
                              ["model"]=>
                      string(13) "gave2###gave2"
                              ["modelData"]=>
                      string(3) "123"
                              ["_attributes"]=>
                      string(131) "[{"feltKey":4823,"feltVal":"test3"},{"feltKey":4824,"feltVal":"test3"},{"feltKey":4825,"feltVal":""},{"feltKey":4826,"feltVal":""}]"
                    }
            */


        }

        System::connection()->commit();

    }

  public function testorderform()
  {
      if(!isset($_GET["token"]) || $_GET["token"] != "df4jhdjsksdfjh3jfj2hjdjsbndnqqq") {
          echo "No access"; return;
      }

      if(isset($_POST["action"]) && $_POST["action"] == "create") {

          var_dump($_POST);
          echo "CREATE";

          // Get input data
          $shopid = intval($_POST["shopid"]);
          $companylock = trimgf($_POST["comapnylock"]);
          $expiredate = trimgf($_POST["expiredate"]);
          $autovalg = intval($_POST["shopid"]);
          $maxorders = intval($_POST["maxorders"]);



          return;
      }

      ?><form action="index.php?rt=developer/testorderform&token=df4jhdjsksdfjh3jfj2hjdjsbndnqqq" method="post">
      <table>
          <tr><td>Shop</td><td><input type="text" name="shopid" size="10"></td><td>id</td></tr>
          <tr><td>Company id </td><td><input type="text" name="companylock" size="10"></td><td>(id liste hvis der skal låses)</td></tr>
          <tr><td>Deadline</td><td><input type="text" name="expiredate" size="10"></td><td>deadline hvis der skal låses til deadline på en cardshop</td></tr>
          <tr><td>%-del autoval</td><td><input type="text" name="autovalg" size="10" value="10"></td><td>hvor mange skal ikke have valgt gave</td></tr>
          <tr><td>max valg</td><td><input type="text" name="maxorders" size="10" value="100"></td><td>antal valg der max skal laves</td></tr>
          <tr><td>&nbsp;<input type="hidden" name="action" value="create"></td><td><input type="submit" value="Kør test"></td><td>&nbsp;</td></tr>
      </table>
      </form><?php

  }
}