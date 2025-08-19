<?php
require('fpdf.php');

class PDF extends FPDF
{
// Page header
function Header()
{
    // Logo

}
function Content($username,$password,$cardType,$deadline){
    $this->Image($cardType.'.jpg',5,30,200);
    $this->SetFont('Arial','',20);
    $this->Text(31,87,GFConfig::SHOP_URL_PRIMARY);
    $this->SetFont('Arial','',15);
    $this->Text(30,164,$username);
    $this->Text(117,164,$password);
    $this->Text(95,144,$deadline);
}
// Page footer
function Footer()
{

}
}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AddPage();
$pdf->Content("30107385","4nz4yp","24","01-01-2020");
$pdf->AddPage();
$pdf->Content("30107386","fnf3yp","design","01-01-2020");
$pdf->AddPage();
$pdf->Content("30107387","fng3yp","jgk","01-01-2020");
$pdf->AddPage();
$pdf->Content("30107388","fnh3yp","onske","01-01-2020");
$pdf->AddPage();
$pdf->Content("30107389","fnj3yp","gold","01-01-2020");




$pdf->Output();
$pdf->Output('d','cards.pdf');

?>