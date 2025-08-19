
<?php
include(GFConfig::BACKEND_PATH."thirdparty/pdf/fpdf.php");
// https://gavefabrikken.dk//kundepanel/printcards.php?id=23504&token=FJAJWfeWY4aSEWoGF21HlGcHsHD4oU

class CardShopPDF
{
    private $data;
    private $pdf;
    private $zipList = [];
    public function __construct() {
        $this->pdf = new PDF();
    }
    public function setData($data){
        $this->data =$data;
    }
    public function build(){
        foreach($this->data as $card){
            $this->pdf->AddPage('P', 'A4');

            $this->makePage($card["username"],$card["password"],$card["shop_id"],$card["expire_date"],$card["is_delivery"]);
        }
    }
    public function buildToFile(){
        foreach($this->data as $card){
            $this->pdf = new PDF();
            $this->pdf->AddPage('P', 'A4');

            $this->makePage($card["username"],$card["password"],$card["shop_id"],$card["expire_date"],$card["is_delivery"]);
            $filename = $card["username"].".pdf";
            $this->renderToFile($filename);
            $this->zipList[] = $card["username"];
        }


    }
    public function makeZip()
    {

        $zipfilename = "zip/".generateRandomString().".zip";
        // fopen($zipfilename, "w");


        $zip = new ZipArchive;
        if ($zip->open($zipfilename, ZipArchive::CREATE) === TRUE)
        {
            foreach($this->zipList as $card){
                $zip->addFile("cards/".$card.".pdf",$card.".pdf");
            }
            $zip->close();
        }
        return $zipfilename;

    }




    public function render(){
        //$this->pdf->Output();
        $this->pdf->Output('d','cards.pdf');
    }
    public function renderToFile($filename){
        $filename="cards/".$filename;
        $this->pdf->Output($filename,'F');
    }

    private function makePage($username,$password,$shopId,$deadline,$is_delivery){


        $pageWidth = 210;
        $pageHeight = 297;
        $shopDA = array("52","53","54","55","56","290","310","575","2548","2395","9321","2960","2961","2962","2963","4662","4668","6989","7121","7122");
        $shopNO = array("57","58","59","272","574","2550","2549","4740",'8355', '8356', '8357', '8358', '8359', '8360', '8361', '8362', '8363', '8364', '8365', '8366');
        $shopSV = array("1832","1981","2558","4793","5117");
        $sommerSV = array("8271");
        $datoObjekt = new DateTime($deadline);
        $deadline = $datoObjekt->format('d-m-Y');

        if(in_array($shopId, $sommerSV)) {
            $this->pdf->Image(GFConfig::BACKEND_PATH . 'files/img/' . $this->getCardImgPath($shopId),0,0,$pageWidth, $pageHeight);

            $this->pdf->SetFont('Arial','',12);
            $this->pdf->Text(60,183,$username);
            $this->pdf->Text(132,183,$password);

            return;
        }
        if(in_array($shopId, $shopSV)) {
            if($is_delivery == 1){
                $this->pdf->Image(GFConfig::BACKEND_PATH.'files/img/'.$this->getCardImgPath($shopId."_home"),0,0,$pageWidth, $pageHeight);
            } else {
                $this->pdf->Image(GFConfig::BACKEND_PATH.'files/img/'.$this->getCardImgPath($shopId),5,50,100);
            }

        } else {
            if($shopId == "2960" || $shopId == "2961" || $shopId == "2962" || $shopId == "2963" || $shopId == "2964"){
                $this->pdf->Image(GFConfig::BACKEND_PATH.'files/img/'.$this->getCardImgPath($shopId),5,30,200);
                $this->pdf->SetFont('Arial','',14);
                $this->pdf->Text(72,112,'https://www.gavevalg.dk');
                $this->pdf->SetFont('Arial','',12);
                $this->pdf->Text(23,149,$username);
                $this->pdf->Text(94,149,$password);
                $this->pdf->Text(65,136,$deadline);
            } else {


                if ($is_delivery == 1 and in_array($shopId, $shopDA)) {
                    $this->pdf->Image(GFConfig::BACKEND_PATH . 'files/img/' . $this->getCardImgPath("is_delivery"),0,0,$pageWidth, $pageHeight);
                } else {
                    $this->pdf->Image(GFConfig::BACKEND_PATH . 'files/img/' . $this->getCardImgPath($shopId),0,0,$pageWidth, $pageHeight);
                }
                if(in_array($shopId, $shopDA)) {
                    $this->pdf->SetFont('Arial','',16);
                    $this->pdf->Text(88,119.5,'https://www.gavevalg.dk');
                    $this->pdf->SetFont('Arial','',12);
                    $this->pdf->Text(50,201,$username);
                    $this->pdf->Text(118,201,$password);
                    $this->pdf->Text(79,169.2,$deadline);
                }
            }
        }




        if(in_array($shopId, $shopNO)) {
            $this->pdf->SetFont('Arial','',16);
            $this->pdf->Text(97,119.5,GFConfig::SHOP_URL_NO);

            $this->pdf->SetFont('Arial','',12);
            $this->pdf->Text(50,201,$username);
            $this->pdf->Text(118,201,$password);
            $this->pdf->Text(92,176.2,$deadline);
        }
        if(in_array($shopId, $shopSV)) {
            $this->pdf->SetFont('Arial','',16);
            $this->pdf->Text(97,113.8,GFConfig::SHOP_URL_SE);

            $this->pdf->SetFont('Arial','',12);
            $this->pdf->Text(50,201,$username);
            $this->pdf->Text(118,201,$password);
            $this->pdf->Text(85,177.1,$deadline);
        }
    }
    private function getCardImgPath($shopId){
        return array(
            "8271"=>"SESommarPresentkort 2025online.jpg",
            "52"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "53"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "2395"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "9321"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "2548"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "4662"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "4668"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "6989"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "7121"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "7122"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "54"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "55"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "56"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "290"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "310"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "575"=>"Gavekort_Gavevalg_2024_DK.jpg",
            "57"=>"Gavekort_Gavevalg_20244.jpg",
            "58"=>"Gavekort_Gavevalg_20244.jpg",
            "59"=>"Gavekort_Gavevalg_20244.jpg",
            "272"=>"Gavekort_Gavevalg_20244.jpg",
            "574"=>"Gavekort_Gavevalg_20244.jpg",
            "2550"=>"Gavekort_Gavevalg_20244.jpg",
            "2549"=>"Gavekort_Gavevalg_20244.jpg",
            "4740"=>"Gavekort_Gavevalg_20244.jpg",
            "1832"=>"sv_24klap_new.jpg",
            "1981"=>"sv_24klap_new.jpg",
            "2558"=>"sv_24klap_new.jpg",
            "4793"=>"sv_24klap_new.jpg",
            "5117"=>"sv_24klap_new.jpg",

            "2960"=>"luks.jpg",
            "2961"=>"luks.jpg",
            "2962"=>"luks.jpg",
            "2963"=>"luks.jpg",
            "is_delivery"=>"Gavekort_Gavevalg_2024.jpg",

            "1832_home"=>"Gavekort_Gavevalg_20243.jpg",
            "1981_home"=>"Gavekort_Gavevalg_20243.jpg",
            "4793_home"=>"Gavekort_Gavevalg_20243.jpg",
            "5117_home"=>"Gavekort_Gavevalg_20243.jpg",
            "2558_home"=>"Gavekort_Gavevalg_20243.jpg",

            "8355"=>"no_gull.jpg",
            "8356"=>"no_gull.jpg",
            "8357"=>"no_gull.jpg",
            "8358"=>"no_gull.jpg",
            "8359"=>"no_gull.jpg",
            "8360"=>"no_gull.jpg",
            "8361"=>"no_gull.jpg",
            "8362"=>"no_gull.jpg",
            "8363"=>"no_gull.jpg",
            "8364"=>"no_gull.jpg",
            "8365"=>"no_gull.jpg",
            "8366"=>"no_gull.jpg"


        )[$shopId];
    }
}
Class PDF extends FPDF
{

}
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}



?>