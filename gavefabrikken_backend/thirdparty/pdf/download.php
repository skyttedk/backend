<?php
require('fpdf.php');

class PDF extends FPDF
{
// Page header
function Header()
{
    // Logo

}
function Content($username,$password,$cardType){
    $this->Image($cardType.'.jpg',5,30,200);
    $this->SetFont('Arial','',20);
    $this->Text(31,87,GFConfig::SHOP_URL_PRIMARY);
    $this->SetFont('Arial','',15);
    $this->Text(30,164,$username);
    $this->Text(117,164,$password);
}
// Page footer
function Footer()
{

}
}

// Instanciation of inherited class
$pdf = new PDF();
$pdf->AddPage();
$pdf->Content("30107385","4nz4yp","24");
$pdf->AddPage();
$pdf->Content("30107386","fnf3yp","design");
$pdf->AddPage();
$pdf->Content("30107387","fng3yp","jgk");
$pdf->AddPage();
$pdf->Content("30107388","fnh3yp","onske");
$pdf->AddPage();
$pdf->Content("30107389","fnj3yp","gold");





$pdf->Output('d','cards.pdf');

?>