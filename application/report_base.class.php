<?php

Abstract Class reportBaseController {

protected $objPHPExcel;

function __construct() {
	
	  //PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
	
	  $this->objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	  $this->objPHPExcel->getProperties()->setCreator("Bitworks");
	$this->objPHPExcel->getProperties()->setLastModifiedBy("Bitworks");
	$this->objPHPExcel->getProperties()->setTitle("");
	$this->objPHPExcel->getProperties()->setSubject("");
	$this->objPHPExcel->getProperties()->setDescription("");
	$this->objPHPExcel->getProperties()->setKeywords("");
	$this->objPHPExcel->getProperties()->setCategory("");

}


public function save($filename) {


	//Redirect output to a client’s web browser (Excel2007)

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
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




}


?>
