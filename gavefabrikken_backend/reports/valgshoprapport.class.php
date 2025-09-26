<?php
//https://github.com/PHPOffice/PHPExcel/wiki/User%20Documentation%20Overview%20and%20Quickstart%20Guide

/*
 Generisk valgshops rapport

*/
class ValgshopRapport Extends reportBaseController{

	private function getRange($startRow,$startColumn,$endRow,$endColumn) {
		return($this->getCell($startRow,$startColumn).":".$this->getCell($endRow,$endColumn));
	}
	private function getCell($row,$column) {
		$column = PHPExcel_Cell::stringFromColumnIndex($column);
		return($column.$row);
	}
	public function generateSQL($profile_data,$shopid,$newSheetPer) {

		$summed = ($profile_data->rapportCheckboxOption=="sum");
		$primaryGroup = $profile_data->newProPrime;
		$secondaryGroup = $profile_data->newProSec;
		$groupby_attributes = Array();

		if($primaryGroup=="gavevalg")
          $primaryGroup = 'present_id,present_model_present_no';

      	if($secondaryGroup=="gavevalg")
            $secondaryGroup = 'present_id,present_model_present_no';

		if($primaryGroup!="none")
		  $groupby_attributes[] = $primaryGroup;

		if($secondaryGroup!="none")
		  $groupby_attributes[] = $secondaryGroup;

		// Always use orderby_attributes, as order by attributes
        $orderby_attributes = [];

		$selected_attributes = explode ( "," ,$profile_data->fieldsInRapport);

		$sql0 = "";
			foreach($groupby_attributes as $attributeId) {
			    if (!$attributeId=='present_id,present_model_present_no')   {
				$shopAttribute = ShopAttribute::find($attributeId);

  				  $sql0 .= "(SELECT attribute_value FROM `order_attribute` WHERE `order_id` =order.id AND attribute_id=$attributeId order by attribute_index) AS `$shopAttribute->name`,";
                }
			}

		$sql ="
			SELECT ". $sql0 ."
				present_name as gave,
				present_model_name as model,";

		if($summed) {
			$sql .= "count(order.id) as antal,";
		}

		$sql2 = "";
			foreach($selected_attributes as $attributeId) {
			    try {
				  $shopAttribute = ShopAttribute::find($attributeId);
   			  	  $sql2 .= "(SELECT attribute_value FROM `order_attribute` WHERE `order_id` =order.id AND attribute_id=$attributeId order by attribute_index) AS `$shopAttribute->name`,";
                } catch (exception $e) {
                   		$sql = rtrim($sql, ",");
                }
			}

		$sql = $sql.rtrim($sql2, ",").
			" FROM `order` ";

			//WHERE
            $sql.=" WHERE shop_id=$shopid and is_demo = 0";

			//group by
		  	if(count($groupby_attributes)>0)
			{
				$sql.=" GROUP BY ";
				foreach($groupby_attributes as $attributeId) {

    			    if ($attributeId!='present_id,present_model_present_no')   {
    					  $shopAttribute = ShopAttribute::find($attributeId);
    					  $sql.= "`$shopAttribute->name`,";
                      }    else
                			{
            				$sql.=" present_name,present_model_present_no,";
            		  	}
	 			}
				$sql = rtrim($sql, ",");
		  	}else
			{
		  	}

				$sql.=" ORDER BY ";
               if($newSheetPer!="") {
                         $sql.= $newSheetPer.",present_id,present_model_present_no,";
                    }  else {
                         $sql.= "present_id,present_model_present_no,";
                         }

			  	$sql = rtrim($sql, ",");

	return ($sql);
	}
	public function autosizeColumns($sheet,$columncount ) {
	//Size Columns
	$fromCol = 'A';
	for($i=1;$i<=$columncount+1;$i++)
	{
		$sheet->getColumnDimension($fromCol)->setAutoSize(true);
		$fromCol++;
	}

	}
	public function writeHeader($sheet,$company,$columncount) {

		//Caption Column
		$startRow = 2;
		$startColumn = 1;
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), 'Leveringsadresse.');
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), 'Fortroligt attention');
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), 'Firmanavn');
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), 'Adresse');
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), 'Postnummer / by');
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), 'Land');

		//Merge Value Columns
		$startRow = 2;
		$startColumn = 2;
		$sheet->mergeCells($this->getRange($startRow,$startColumn,$startRow++,$startColumn+$columncount-2));
		$sheet->mergeCells($this->getRange($startRow,$startColumn,$startRow++,$startColumn+$columncount-2));
		$sheet->mergeCells($this->getRange($startRow,$startColumn,$startRow++,$startColumn+$columncount-2));
		$sheet->mergeCells($this->getRange($startRow,$startColumn,$startRow++,$startColumn+$columncount-2));
		$sheet->mergeCells($this->getRange($startRow,$startColumn,$startRow++,$startColumn+$columncount-2));
		$sheet->mergeCells($this->getRange($startRow,$startColumn,$startRow++,$startColumn+$columncount-2));

		// Left Align Value Column
		$startRow = 2;
		$startColumn = 2;
		$sheet->getStyle($this->getCell($startRow++,$startColumn))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
	 	$sheet->getStyle($this->getCell($startRow++,$startColumn))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
	 	$sheet->getStyle($this->getCell($startRow++,$startColumn))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
	 	$sheet->getStyle($this->getCell($startRow++,$startColumn))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
	 	$sheet->getStyle($this->getCell($startRow++,$startColumn))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
	 	$sheet->getStyle($this->getCell($startRow++,$startColumn))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

		//Value Column
		$startRow = 2;
		$startColumn = 2;
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), $company->ship_to_address);
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), $company->ship_to_attention);
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), $company->name);
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), $company->ship_to_address);
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), $company->ship_to_postal_code);
		$sheet->setCellValue($this->getCell($startRow++,$startColumn), $company->ship_to_country);

		//Border
		$startRow = 2;
		$startColumn = 1;
		$endRow = 7;
		$endColumn = 1+$columncount-1;
		$sheet->getStyle($this->getRange($startRow,$startColumn,$endRow,$endColumn))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);

	}
	public function writeCaptions($sheet,$orders) {
		$row = 9;
		$column =1;

		foreach($orders[0]->attributes() as $key => $value)
		{
			$sheet->getStyle($this->getCell($row,$column))->getFont()->setBold(true);
			$sheet->setCellValueByColumnAndRow($column, $row, ucfirst($key));
			$column++;
		}
		$sheet->getStyle($this->getRange($row,1,$row,$column-1))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);

	}

	public function writeValues($sheet,$company,$orders,$profile_data,$newSheetPer)  {

        $lineBuffer = [];
        $isFirstSheet = true;
        $newSheetPerGroup = false;

		$summed = ($profile_data->rapportCheckboxOption=="sum");
		$columncount = countgf($orders[0]->attributes());

		$primaryGroup = $profile_data->newProPrime;
		$secondaryGroup = $profile_data->newProSec;

     	if($primaryGroup=="gavevalg")
          $primaryGroup = 'present_id,present_model_present_no';

       	if($secondaryGroup=="gavevalg")
          $secondaryGroup = 'present_id,present_model_present_no';


		$groupby_attributes = Array();
		$primaryGroupName = "Lokation";

		$selected_attributes = explode ( "," ,$profile_data->fieldsInRapport);
		// Construct Custom T-SQL

		$total = 0;
		$row = 10;

		$lastGroupValue = "";
        $newSheet = false;
		foreach ($orders  as $order)
		{
			$column =1;
            $lineBuffer = [];
			foreach($order->attributes() as $key => $value)
			{
                $lineBuffer[] =  str_replace('###',' - ',$value);
                if(strtolower ($key) == strtolower($primaryGroupName)) {
				  if($newSheetPer!="" && ($lastGroupValue != $value)) {
				      $newSheet = true;
            	      $lastGroupValue = $value;
     			  }
                }
            }
             if($newSheet==true) {
        	$this->autosizeColumns($sheet,$columncount);
        	//Set Border on cyrrent Sheet
        	$startRow = 10;
        	$StartColumn =1;
        	$endRow = $row-1;
        	$endColumn =$columncount;
        	$sheet->getStyle($this->getRange($startRow,$StartColumn,$endRow,$endColumn))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
            //Create New Sheet
            $sheet = $this->objPHPExcel->getActiveSheet();

            if($isFirstSheet==false) {    // default side benyttes først
              $sheet = $this->objPHPExcel->createSheet();
            }
            $isFirstSheet = false;
            // Set Sheet Title
            if($value=='') {
         	  $sheet->setTitle('<blank>');
            }    else {
           	  $sheet->setTitle(substr($value,0,30)); //Just set name everytime
            }
        	// Write Header
        	$this->writeHeader($sheet,$company,$columncount);
        	// Write  Captions
        	$this->writeCaptions($sheet,$orders);
            // Set Start Row
        	$row = 10;
            $newSheet = false;
          }

        //Write Current Row
        foreach($lineBuffer as $line){
           $sheet->setCellValueByColumnAndRow($column, $row,str_replace('###',' - ',$line));
  	       $column++;
        }

        $lineBuffer = [];
        $row++;
		if($summed)
		  $total += $order->antal;
		}


    	//Set Border
    	$startRow = 10;
    	$StartColumn =1;
    	$endRow = $row-1;
    	$endColumn =$columncount;
    	$sheet->getStyle($this->getRange($startRow,$StartColumn,$endRow,$endColumn))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);

    	//AutoSize
    	$this->autosizeColumns($sheet,$columncount);

	}


	public function run($reportId) {
	// Report Data
	$report = ShopReport::readShopReport($reportId);
	$shop = Shop::find($report->shop_id);
	$company = Company::find($shop->company_shops[0]->company_id);
    $newSheetPerGroup = false;

	// Profile Data
	$profile_data =json_decode($report->profile_data);
    $newSheetPer  = '';

    if (isset($profile_data->newSheet)) {
        if($profile_data->newSheet!="none")
          $newSheetPer = $profile_data->newSheet;
    }

	// Load Order Data
	$sql = $this->generateSQL($profile_data,$_GET['shop_id'],$newSheetPer);

	$orders = Order::find_by_sql($sql);
	$columncount = countgf($orders[0]->attributes());

	//Create Sheet
	$sheet = $this->objPHPExcel->getActiveSheet();
    if (!$newSheetPerGroup) {
	  $sheet->setTitle('Gavevalg');
      $this->writeHeader($sheet,$company,$columncount);
      $this->writeCaptions($sheet,$orders);
    }
	//Values
  	$this->writeValues($sheet,$company,$orders,$profile_data,$newSheetPer);

 }

}
?>