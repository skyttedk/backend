<?php
namespace GFUnit\pim\gavevalg;
use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;

class present extends UnitController
{


    public function __construct()
    {
       parent::__construct(__FILE__);
    }
    public function test()
    {
        return "det vierker";
    }
    public function getAllPresentsData($presentID)
    {
        return array(
            "present"=>$this->getPresent($presentID),
            "presentModels"=>$this->getPresent($presentID),
            "medias"=>$this->getPresentMedia($presentID),
            "centents"=>$this->getPresentContent($presentID)
        );
    }

    public function getPresent($presentID)
    {
       return \Present::find($presentID);
    }
    public function getPresentModel($presentID)
    {
        return \PresentModel::all(array('conditions' => 'present_id = '.$presentID ));
    }
    public function getPresentMedia($presentID)
    {
        return \Media::find_by_sql('select * from present_media where present_id='.$presentID.' order by `index` ASC');
    }
    public function getPresentContent($presentID)
    {
       return \PresentDescription::all(array('conditions' => 'present_id = '.$presentID.' and language_id != 3' ));

    }
    public function syncSingle($presentID)
    {
        $product_type = 175817;
        $postData = "{\n  \"data\": {\n  \t\"type\": \"category_item\",\n  \t\"attributes\": {";
        $postData.= "\n  \t\t\"product_type\": {\n  \t\t\t\"value\": \"" . $product_type . "\"\n  \t\t},";

        // post data present
        $postData.= $this->getPostStrengPresent($presentID);

        $kontainer = new KontainerCom;
        $res = $kontainer->createNewItem($postData);
        var_dump($res);

    }
    private function getPostStrengPresent($presentID){
        $lang = ["","da","en","","no","se"];
        $product_no = "import_test";
        $present = $this->getPresent($presentID);
        // erp navn
        $postData = "";

        // overskrift og indhold
        $pd = $this->getPresentContent($presentID);
        foreach ($pd as $c)
        {
            $description = utf8_encode( base64_decode($c->attributes["long_description"]) );
            $description = html_entity_decode($description);
            $description = addslashes($description);
            $description = str_replace(array("\n","\r"), '', $description);
            $postData.= "\n  \t\t\"product_name_".$lang[$c->attributes["language_id"]]."\": {\n  \t\t\t\"value\": \"" . $c->attributes["caption"] . "\"\n  \t\t},";
            $postData.= "\n  \t\t\"description_".$lang[$c->attributes["language_id"]]."\": {\n  \t\t\t\"value\": \"" . $description . "\"\n  \t\t},";
        }
        $postData.= "\n  \t\t\"product_no\": {\n  \t\t\t\"value\": \"".$product_no."\"\n  \t\t}";
        $postData.= "\n\n  \t}\n\n  }\n\n}";

        $pm = $this->getPresentModel($presentID);
        foreach ($pm as $model){
            $erpName = addslashes(trim($model->attributes["model_name"]." ".$model->attributes["model_no"]));
            $postData.= "\n  \t\t\"erp_product_name_".$lang[$model->attributes["language_id"]]."\": {\n  \t\t\t\"value\": \"" . $erpName . "\"\n  \t\t},";
        }





        die($postData);
        return $postData;

    }



















}