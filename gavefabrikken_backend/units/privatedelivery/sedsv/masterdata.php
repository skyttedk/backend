<?php

namespace GFUnit\privatedelivery\sedsv;


class Masterdata
{

    private $dashError = "";
    private $selectedShops = array();
    private $allShops = false;
    private $includeItemNos = array("GF2301","220136","200149","30-LG0024OAKK");
    private $excludeItemNos = array();
    private $addSamNrs = array();
    private $objPHPExcel;

    public function __construct($allShops=false, $selectedShopList=null)
    {

        $this->allShops = $allShops;
        $this->shopIDList = $selectedShopList;
        if(!is_array($this->shopIDList)) {
            $this->shopIDList = array();
        }

    }

    public function loadFromPost() {

        $shopList = \CardshopSettings::find_by_sql("select * from cardshop_settings where language_code = 5");
        $this->allShops = false;
        $this->shopIDList = array();
        foreach($shopList as $shop) {
            if(isset($_POST["shop_".$shop->shop_id]) && intval($_POST["shop_".$shop->shop_id]) == 1) {
                $this->shopIDList[] = $shop->shop_id;
            }
        }

    }

    private function generateReport() {

        // Load all se shops
        $shopList = \CardshopSettings::find_by_sql("select * from cardshop_settings where language_code = 5");

        // Find shops to use
        $shopidlist = array();
        foreach($shopList as $shop) {
            if($this->allShops || in_array($shop->shop_id,$this->shopIDList)) {
                $shopidlist[] = $shop->shop_id;
            }
        }

        // Check shops
        if(count($shopidlist) == 0) {
            throw new \Exception("No shops selected");
        }

        // Load presents
        $itemNoList = $this->loadPresentDataFromShops($shopidlist,true);
        foreach($this->includeItemNos as $extraNo) {
            $itemNoList[] = $extraNo;
        }

        // Exclude from list
        $newItemList = array();
        foreach($itemNoList as $itemNo) {
            if(!in_array($itemNo,$this->excludeItemNos)) {
                $newItemList[] = $itemNo;
            }
        }
        $itemNoList = $newItemList;

        $uniqueItems = $this->findUniqueItems($itemNoList,true);

        foreach($this->addSamNrs as $samnr) {
            $samItem = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$samnr."' && deleted is null");
            if(countgf($samItem) > 0) {
                $uniqueItems[] = $samItem[0];
            }
        }

        // Output excel
        $this->objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $this->objPHPExcel->getProperties()->setCreator("Gavefabrikken");
        $this->objPHPExcel->getProperties()->setLastModifiedBy("Gavefabrikken");
        $this->objPHPExcel->getProperties()->setTitle("");
        $this->objPHPExcel->getProperties()->setSubject("");
        $this->objPHPExcel->getProperties()->setDescription("");
        $this->objPHPExcel->getProperties()->setKeywords("");
        $this->objPHPExcel->getProperties()->setCategory("");

        $this->objPHPExcel->removeSheetByIndex(0);
        $sheet = $this->objPHPExcel->createSheet();
        $sheet->setTitle("Sheet1");

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(17);
        $sheet->getColumnDimension('D')->setWidth(44);
        $sheet->getColumnDimension('E')->setWidth(17);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(17);
        $sheet->getColumnDimension('M')->setWidth(17);
        $sheet->getColumnDimension('N')->setWidth(17);


        $sheet->setCellValueByColumnAndRow(1, 1, "");
        $sheet->setCellValueByColumnAndRow(2, 1, "");
        $sheet->setCellValueByColumnAndRow(3, 1, "");
        $sheet->setCellValueByColumnAndRow(4, 1, "");
        $sheet->setCellValueByColumnAndRow(5, 1, "");
        $sheet->setCellValueByColumnAndRow(6, 1, "CM");
        $sheet->setCellValueByColumnAndRow(7, 1, "CM");
        $sheet->setCellValueByColumnAndRow(8, 1, "CM");
        $sheet->setCellValueByColumnAndRow(9, 1, "KG");
        $sheet->setCellValueByColumnAndRow(10, 1, "m3");
        $sheet->setCellValueByColumnAndRow(11, 1, "Y/N");
        $sheet->setCellValueByColumnAndRow(12, 1, "Y/N");
        $sheet->setCellValueByColumnAndRow(13, 1, "Y/N");
        $sheet->setCellValueByColumnAndRow(14, 1, "Y/N");

        $sheet->setCellValueByColumnAndRow(1, 2, "");
        $sheet->setCellValueByColumnAndRow(2, 2, "SKU_ID");
        $sheet->setCellValueByColumnAndRow(3, 2, "EAN");
        $sheet->setCellValueByColumnAndRow(4, 2, "DESCRIPTION");
        $sheet->setCellValueByColumnAndRow(5, 2, "PRODUCT_GROUP");
        $sheet->setCellValueByColumnAndRow(6, 2, "EACH_WIDTH");
        $sheet->setCellValueByColumnAndRow(7, 2, "EACH_DEPTH");
        $sheet->setCellValueByColumnAndRow(8, 2, "EACH_HEIGHT");
        $sheet->setCellValueByColumnAndRow(9, 2, "EACH_WEIGHT");
        $sheet->setCellValueByColumnAndRow(10, 2, "EACH_VOLUME");
        $sheet->setCellValueByColumnAndRow(11, 2, "EXPIRY_REQD");
        $sheet->setCellValueByColumnAndRow(12, 2, "SERIAL_NUMBER");
        $sheet->setCellValueByColumnAndRow(13, 2, "BATCH");
        $sheet->setCellValueByColumnAndRow(14, 2, "SPECIAL HANDLING");

        $row = 3;

        foreach($uniqueItems as $item) {

            if (!in_array($item->no, $this->excludeItemNos)) {
                $sheet->setCellValueByColumnAndRow(2, $row, $item->no);
                $sheet->setCellValueByColumnAndRow(3, $row, $item->crossreference_no . "");
                $sheet->setCellValueByColumnAndRow(4, $row, $item->description);


                if ($item->width > 3 || $item->length > 3 || $item->height > 3) {

                    $width = $item->width;
                    if ($width == 0) {
                        $width = "";
                    }
                    $sheet->setCellValueByColumnAndRow(6, $row, $width);

                    $length = $item->length;
                    if ($length == 0) {
                        $length = "";
                    }
                    $sheet->setCellValueByColumnAndRow(7, $row, $length);

                    $height = $item->height;
                    if ($height == 0) {
                        $height = "";
                    }
                    $sheet->setCellValueByColumnAndRow(8, $row, $height);

                    $volume = $item->cubage;
                    if ($volume == 0) {
                        $volume = "";
                    }
                    $sheet->setCellValueByColumnAndRow(10, $row, $volume / (100 * 100 * 100));

                } else {


                    $width = $item->width;
                    if ($width == 0) {
                        $width = "";
                    }
                    $sheet->setCellValueByColumnAndRow(6, $row, ($width === "" ? "" : $width * 100));


                    $length = $item->length;
                    if ($length == 0) {
                        $length = "";
                    }
                    $sheet->setCellValueByColumnAndRow(7, $row, ($length === "" ? "" : $length * 100));

                    $height = $item->height;
                    if ($height == 0) {
                        $height = "";
                    }
                    $sheet->setCellValueByColumnAndRow(8, $row, ($height === "" ? "" : $height * 100));

                    $volume = $item->cubage;
                    if ($volume == 0) {
                        $volume = "";
                    }
                    $sheet->setCellValueByColumnAndRow(10, $row, $volume === "" ? "" : $volume);


                }

                $weight = $item->gross_weight;
                if ($weight == 0) {
                    $weight == $item->net_weight;
                }
                if ($weight == 0) {
                    $weight = "";
                }
                $sheet->setCellValueByColumnAndRow(9, $row, $weight);

                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $row++;

            }
        }
        //$sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');

    }

    public function output()
    {

        $shoplist = "";
        if(count($this->shopIDList) > 0) {
            $shoplist = "-".implode("-",$this->shopIDList);
        }

        $this->generateReport();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="masterdata-dsv'.$shoplist.'-'.date('dmY').'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
        //$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

        $objWriter->save("php://output");
    }

    public function save($filename,$forceCSV=false) {

        $this->generateReport();
        if($forceCSV) {
            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Csv($this->objPHPExcel);
        } else {
            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
        }
        $objWriter->save($filename);
    }

    /**
     * UNPACK NAV ITEM NOS FROM ITEMS IN SHOP
     */

    private function findUniqueItems($itemNoList,$returnObjects=true) {

        $uniqueItemNoList = array();
        $itemObjList = array();

        foreach($itemNoList as $itemNo) {

            // Load item from nav
            $itemData = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$itemNo."' && deleted is null");
            $bomItemList = \NavisionBomItem::find_by_sql("select * from navision_bomitem where language_id = 1 && parent_item_no like '".$itemNo."' && deleted is null");

            // Has bom items
            if(countgf($bomItemList) > 0) {
                foreach($bomItemList as $bomItem) {
                    if(!in_array($bomItem->no,$uniqueItemNoList)) {

                        //$uniqueItemNoList[] = $bomItem->no;

                        $itemSubData = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$bomItem->no."' && deleted is null");
                        if(countgf($itemSubData) > 0) {
                            if(countgf($itemSubData) > 1) {
                                $this->dashError .= "WARNING: Multiple child items with no ".$bomItem->no."<br>";
                            }
                            foreach($itemSubData as $navItem) {
                                if(!in_array($navItem->no,$uniqueItemNoList)) {
                                    $uniqueItemNoList[] = $navItem->no;
                                    $itemObjList[] = $navItem;
                                }
                            }
                        } else {
                            $this->dashError .= "WARNING: Could not find child item no in nav: ".$bomItem->no."<br>";
                        }

                    }
                }
            }
            // Has items
            else if(countgf($itemData) > 0) {
                if(countgf($itemData) > 1) {
                    $this->dashError .= "WARNING: Multiple items with no ".$itemNo."<br>";
                }
                foreach($itemData as $navItem) {
                    if(!in_array($navItem->no,$uniqueItemNoList)) {
                        $uniqueItemNoList[] = $navItem->no;
                        $itemObjList[] = $navItem;
                    }
                }
            }
            // Other
            else {
                $this->dashError .= "WARNING: Could not find item no in nav: ".$itemNo."<br>";
            }

        }

        return $returnObjects ? $itemObjList : $uniqueItemNoList;
    }

    /**
     * Load present data from shop
     */

    private function loadPresentDataFromShops($shopidlist,$itemNoOnly=false) {

        $onlyWithOrders = false;

        if(countgf($shopidlist) == 0) return array();
        $sql = "SELECT present.name, present.nav_name, present.internal_name, present_model.model_id, present_model.present_id, present_model.model_present_no, present_model.model_name, present_model.model_no, present_model.fullalias FROM `present_model`, present where present.id = present_model.present_id && present_model.language_id = 1 && present.shop_id in (".implode(",",$shopidlist).") ";
        // && present_model.fullalias != ''
        if($onlyWithOrders) $sql .= " && present_model.model_id in (SELECT present_model_id from `order` where shop_id in (".implode(",",$shopidlist)."))";
        
        $presentmodellist = \PresentModel::find_by_sql($sql);

        if($itemNoOnly == false) {
            return $presentmodellist;
        }

        $modelNoList = array();
        foreach($presentmodellist as $presentmodel) {
            $itemNo = $presentmodel->model_present_no;
            if(!in_array($itemNo,$modelNoList)) {
                $modelNoList[] = $itemNo;
            }
        }

        return $modelNoList;

    }
}
