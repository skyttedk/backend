<?php

namespace GFUnit\cardshop\freight;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public function testhelper()
    {
        $helper = new CSFreightHelper(12778);
        $items = $helper->getFreightItemsForCompany(true);
        foreach($items as $item) {
            $editor = new EditorElement($item);
            echo $editor->renderEditor();
        }
    }

    public function companyfreightsave()
    {
        $freightEditor = new FreightEditor();
        $freightEditor->saveEditor();
    }


    public function companyfreightform($companyid,$includechilds)
    {

        $freightEditor = new FreightEditor2();
        $freightEditor->dispatchEditor($companyid,$includechilds);
        
        // FreightEditor 2 has been replaced by editor 2 on august 2024
        //$freightEditor = new FreightEditor();
        //$freightEditor->dispatchEditor($companyid,$includechilds);
    }

    public function companyfreightform2($companyid,$includechilds)
    {
        $freightEditor = new FreightEditor2();
        $freightEditor->dispatchEditor($companyid,$includechilds);
    }


}