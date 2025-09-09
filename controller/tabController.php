<?php

class tabController Extends baseController {
    public function Index() {
    }
    public function createPermission(){
        $data = $_POST;
        $result = UserTabPermission::createUserTabPermission($data);
        response::success(json_encode($result));
        //$result= UserTabPermission::find_by_systemuser_id(6);
        //$result = UserTabPermission::createUserTabPermission($data);
        /*
        if($result){
            $result = UserTabPermission::updateUserTabPermission($data);
        } else {
            $result = UserTabPermission::createUserTabPermission($data);
        }
        */

        //$order = Order::find_by_shopuser_id($_POST['user_id']);
    }
    public function loadPermission(){
        $data = $_POST;

         $result= UserTabPermission::all(array('conditions' => 'systemuser_id = '.$data["systemuser_id"])); //find_by_systemuser_id($data["systemuser_id"]);
           $result= UserTabPermission::all(array('conditions' => 'systemuser_id = '.$data["systemuser_id"])); //find_by_systemuser_id($data["systemuser_id"]);
        response::success(json_encode($result));
    }


    public function getId(){
        $data = $_POST;
        $result = UserTabPermission::all(array('conditions' => array('systemuser_id = '.$data["systemuser_id"].' AND tap_id = '.$data["tap_id"])));
        response::success(json_encode($result));

//                UserTabPermission::deleteUserTabPermission


//
    }
    public function removePermission(){
        $data = $_POST;
        $result = UserTabPermission::deleteUserTabPermission($data["id"]);
        response::success(json_encode($result));
    }
    public function loadFrontPermission()
    {

        $data = $_POST;


        $result= UserTabPermission::all(array('conditions' => 'systemuser_id = '.$data["systemuser_id"],'order'=>'tap_id desc' ));
            $returnHtml = "";


            $searchbarVisible = false;
            foreach($result as $item){

              switch ($item->tap_id) {


               case "100":
                    $searchbarVisible = true;
                    $returnHtml.='<input type="radio"  id="radio1" name="radio" checked  ><label for="radio1" onclick="showValgshop()">Valgshops</label>';
                break;
                case "70":
             //       $returnHtml.='<input type="radio" id="radio3" name="radio"><label  for="radio3" onclick="bizType.trail(\'tilbud\')">Tilbud</label>';
                break;
                case "80":
                    $returnHtml.='<input type="radio" id="radio4" name="radio"><label  for="radio4"  onclick="gaveAdmin.show()">GaveAdmin</label>';
                break;
                case "60":
                    $returnHtml.='<input type="radio"  id="radio5" name="radio"><label " for="radio5"  onclick="bizType.trail(\'systemUser\')">System</label>';
                break;
                case "50":
           //         $returnHtml.='<input type="radio" id="radio6" name="radio"><label for="radio6"  onclick="bizType.trail(\'lager\')">Lager-admin</label>';
                break;
                case "90":
                    $returnHtml.='<input type="radio"  id="radio2" name="radio" ><label for="radio2" onclick="bizType.trail(\'kort\')">Gavekort-shops</label> ';
                break;
                case "45":
                    $returnHtml.='<input type="radio" id="radio7" name="radio" ><label for="radio7" onclick="showArkiv()">Arkiv</label>';
                break;
                case "110":
                    $searchbarVisible = true;
                    $returnHtml.='<input type="radio"  id="radio0" name="radio" ><label for="radio0" onclick="bizType.trail(\'infoBoard\')">Infoboard</label>';
                break;
                case "120":
                    $searchbarVisible = true;
                    $returnHtml.='<input type="radio"  id="radio8" name="radio" ><label for="radio8" onclick="bizType.trail(\'shopboard\')">Shopboard</label>';
                break;
                case "41":
                   // $searchbarVisible = false;
                    $returnHtml.='<input type="radio" id="radio10" name="radio" ><label for="radio10" onclick="bizType.trail(\'showSuperAdmin\')">Early gave admin</label>';
                break;


           }
          }


          $returnHtml.='<input type="radio" id="radio69" name="radio" ><label for="radio69" onclick="bizType.trail(\'showMyPage\')">Min side</label>';
          
          // Only show Reklamationer menu for user ID 340
          
              $returnHtml.='<input type="radio" id="radio11" name="radio" ><label for="radio11" onclick="bizType.trail(\'presentComplaint\')">Reklamationer</label>';
          
          if($searchbarVisible){
            echo $returnHtml.='<script>mainTabControlResponse("show")</script>';
          } else {
            echo $returnHtml.='<script>mainTabControlResponse("hide")</script>';
          }



    }


    public function loadGiftshopPermission(){
        $data = $_POST;

        $result= UserTabPermission::all(array('conditions' => 'systemuser_id = '.$data["systemuser_id"],'order'=>'tap_id ' ));

        $returnHtml = '<input type="radio" id="radio2" name="radio"  ><label for="radio2" onclick="goToCardShop()" >Shops</label>';
        foreach($result as $item){
           switch ($item->tap_id) {
                case "10":
                    $returnHtml.='<input type="radio" id="radio3" name="radio" ><label for="radio3"  onclick="goToImport()" >Gavekort - ventene Bestillinger </label>';
                break;
                case "11":
                    $returnHtml.='<input type="radio" id="radio4" name="radio" ><label for="radio4" onclick="goTosale()">Gavekort - salgsstatistik / lagerstyring</label>';
                break;
                case "12":
                    $returnHtml.='<input type="radio" id="radio5" name="radio"  ><label for="radio5" onclick="goToPluk()">Gavekort - plukliste</label>';
                break;
            }
        }
            echo $returnHtml."<script>initTabs()</script>";
          //  echo "hej med dig"; //$returnHtml;//."<script>initTabs()</script>";

    }




    public function loadShopPermission()
    {
        $data = $_POST;
        $result= UserTabPermission::all(array('conditions' => 'systemuser_id = '.$data["systemuser_id"],'order'=>'tap_id ' ));
        $returnHtml = "";
           switch ($item->tap_id) {
                case "1":
                    $searchbarVisible = true;
                    $returnHtml.='<li><a href="#tabs-1" class="headline">Stamdata</a></li>';
                break;
                case "2":
                    $returnHtml.='<li><a href="#tabs-2" class="headline">Forside</a></li>';
                break;
                case "3":
                    $returnHtml.='<li><a href="#tabs-3" class="headline">Gaver</a></li>';
                break;
                case "4":
                    $returnHtml.='<li><a href="#tabs-4" class="headline">Indstillinger</a></li>   ';
                break;
                case "5":
                    $returnHtml.='	<li><a href="#tabs-5" class="headline" id="menuFeltDeff001" onclick="feltDeff.showLangFields()">Felt definition</a></li>';
                break;
                case "6":
                    $returnHtml.='<li><a href="#tabs-6" class="headline" id="menuUserAdmin001" onclick="userData.openApp()">Brugerindl&oelig;sning</a></li>';
                break;
                case "7":
                    $returnHtml.='<li><a href="#tabs-7" class="headline" onclick="rapport.updateList()" >Rapporter</a></li> ';
                break;
                case "8":
                    $returnHtml.='<li><a href="#tabs-8" class="headline" id="menuGavevalg001" onclick="gavevalg.goto()">Gavevalg</a></li> ';
                break;
                case "9":
                    $returnHtml.='	<li><a href="#tabs-9" class="headline">Lageroverv&aring;gning</a></li> ';
                break;
            }
            echo $returnHtml.='<script>mainTabControlResponse("show")</script>';

    }



}
?>






