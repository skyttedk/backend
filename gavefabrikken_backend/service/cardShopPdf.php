
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
            $this->pdf->AddPage();
            $this->makePage($card["username"],$card["password"],$card["shop_id"],$card["expire_date"],$card["is_delivery"]);
        }
    }
    public function buildToFile(){
        foreach($this->data as $card){
            $this->pdf = new PDF();
            $this->pdf->AddPage();
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
            die("asdf");
            $shopDA = array("52","53","54","55","56","290","310","575","2548","2395","9321","2960","2961","2962","2963","4662","4668","6989","7121","7122");
            $shopNO = array("57","58","59","272","574","2550","2549","4740",'8355', '8356', '8357', '8358', '8359', '8360', '8361', '8362', '8363', '8364', '8365', '8366');
            $shopSV = array("1832","1981","2558","4793","5117","8271","9495");
            $sommerSV = array("8271");
            if(in_array($shopId, $shopSV)) {
                if($is_delivery == 1){
                    $this->pdf->Image(GFConfig::BACKEND_PATH.'files/img/'.$this->getCardImgPath($shopId."_home"),5,50,200);
                } else {
                    $this->pdf->Image(GFConfig::BACKEND_PATH.'files/img/'.$this->getCardImgPath($shopId),5,50,200);
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
                        $this->pdf->Image(GFConfig::BACKEND_PATH . 'files/img/' . $this->getCardImgPath("is_delivery"), 5, 30, 200);
                    } else {
                        $this->pdf->Image(GFConfig::BACKEND_PATH . 'files/img/' . $this->getCardImgPath($shopId), 5, 30, 200);
                    }
                    if(in_array($shopId, $shopDA)) {
                        $this->pdf->SetFont('Arial','',12);
                        $this->pdf->Text(78,86.8,'https://www.gavevalg.dk');
                        $this->pdf->SetFont('Arial','',12);
                        $this->pdf->Text(44,118,$username);
                        $this->pdf->Text(115,118,$password);
                        $this->pdf->Text(64,110,$deadline);
                    }
                }
            }
            if(in_array($shopId, $sommerSV)) {
                    die("asdf");
            }


            if(in_array($shopId, $shopNO)) {
              $this->pdf->SetFont('Arial','',20);
              $this->pdf->Text(31,87,GFConfig::SHOP_URL_NO);
              $this->pdf->SetFont('Arial','',15);
              $this->pdf->Text(30,164,$username);
              $this->pdf->Text(117,164,$password);
              $this->pdf->Text(113,143,$deadline);
            }
            if(in_array($shopId, $shopSV)) {
               // $this->pdf->SetFont('Arial','',20);
               // $this->pdf->Text(31,87,GFConfig::SHOP_URL_SE);
               $this->pdf->SetFont('Arial','',15);
               $this->pdf->Text(25,169,$username);
               $this->pdf->Text(97,169,$password);
               $this->pdf->Text(70,156,$deadline);
            }
    }
    private function getCardImgPath($shopId){
        return array(
            "52"=>"alm_dk.jpg",
            "53"=>"alm_dk.jpg",
            "2395"=>"alm_dk.jpg",
            "9321"=>"alm_dk.jpg",
            "2548"=>"alm_dk.jpg",
            "4662"=>"alm_dk.jpg",
            "4668"=>"alm_dk.jpg",
            "6989"=>"alm_dk.jpg",
            "7121"=>"alm_dk.jpg",
            "7122"=>"alm_dk.jpg",
            "54"=>"alm_dk.jpg",
            "55"=>"alm_dk.jpg",
            "56"=>"alm_dk.jpg",
            "290"=>"alm_dk.jpg",
            "310"=>"alm_dk.jpg",
            "575"=>"alm_dk.jpg",
            "57"=>"no_jgk.jpg",
            "58"=>"no_jgk.jpg",
            "59"=>"no_jgk.jpg",
            "272"=>"no_jgk.jpg",
            "574"=>"no_gull.jpg",
            "2550"=>"no_gull.jpg",
            "2549"=>"no_gull.jpg",
            "4740"=>"no_gull.jpg",
            "1832"=>"sv_24klap_new.jpg",
            "9495"=>"sv_24klap_new.jpg",
            "1981"=>"sv_24klap_new.jpg",
            "2558"=>"sv_24klap_new.jpg",
            "4793"=>"sv_24klap_new.jpg",
            "5117"=>"sv_24klap_new.jpg",
            "8271"=>"sv_24klap_new.jpg",
            "2960"=>"luks.jpg",
            "2961"=>"luks.jpg",
            "2962"=>"luks.jpg",
            "2963"=>"luks.jpg",
            "is_delivery"=>"home_dk.jpg",

            "1832_home"=>"sv_24klap_new_home.jpg",
            "9495_home"=>"sv_24klap_new_home.jpg",
            "1981_home"=>"sv_24klap_new_home.jpg",
            "4793_home"=>"sv_24klap_new_home.jpg",
            "5117_home"=>"sv_24klap_new_home.jpg",
            "8271_home"=>"sv_24klap_new_home.jpg",
            "2558_home"=>"sv_24klap_new_home.jpg",

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