<?php

require "pdfcrowd.php";

Class paperValgController Extends baseController
{
    private $serverUrl = "https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/user/";
    public function index()
    {
        echo "index";
    }


    public function norge(){
        $shop_id = 2549;
        $kort = "BRA";




        $presentlist = Present::find_by_sql("SELECT present_model.model_present_no, norge.`model_name`,norge.`model_no`, present_model.media_path ,`present_model`.fullalias FROM `present_model` 

INNER JOIN present on present_model.present_id = present.id

left join ( SELECT * from present_model WHERE language_id = 4 ) norge on present_model.model_id = norge.model_id
WHERE 
`present`.shop_id = ".$shop_id." AND
present_model.language_id = 1 and
`present_model`.`present_id`  in ( SELECT present_id from shop_present WHERE shop_present.shop_id = ".$shop_id." and is_deleted = 0 ) 


ORDER BY CAST(present_model.fullalias AS UNSIGNED ) ASC limit 100;

");


        $outputHtml = "<center><h1>".$kort."</h1></center><div>";
        $pageCounter = 0;

        foreach ($presentlist as $p){

            if($pageCounter == 6){
                $outputHtml.="</div><div style='page-break-before:always'><hr></div><div>";
                $pageCounter=0;
            }
            $pageCounter++;
            $p = $p->attributes;
            $mediaArr = explode("/",$p["media_path"]);
            $media = is_array($mediaArr) == true ? $mediaArr[sizeof($mediaArr)-1] : $mediaArr;
            $outputHtml.= "<div style='width: 330px; height: 310px; border: 1px solid black; float: left; margin:5px'>
                <div style='width: 100%; height: 45px;text-align: center; font-size: 40px;'>".$p["fullalias"]."</div>
                <div style='width: 100%; height: 160px; background-size: contain; background-repeat: no-repeat;background-position: top;  background-image:url(https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/$media); ' ></div>
                <hr>
                <div style='width: 100%; height: 95px'>
                <table>
                    <tr><td>Itemnr: </td><td>".$p["model_present_no"]."</td></tr>
                    <tr><td>Name: </td><td>".$p["model_name"]."</td></tr>
                    <tr><td>Model: </td><td>".$p["model_no"]."</td></tr>
                </table>
                
                
                 </div>
             </div>";
        }
        $outputHtml.="</div>";


 $outputHtml;
//        $outputHtml = "<div>hej med dig</div>";

//            die("");
        $papervalgHelper = new papervalgHelper();
        $nr = $papervalgHelper->generateRandomString();
        $toPdf = "<!DOCTYPE HTML>

<html>

<head>
  <title>Untitled</title>
</head>

<body>
".$outputHtml."
</body>

</html>";


        $filename = "norge_".$nr;
        try
        {
            // create the API client instance
            $client = new \Pdfcrowd\HtmlToPdfClient("bundy0909", "c16c892a0e8a507b747419350431df64");

            // run the conversion and write the result to a file
            $res = $client->convertStringToFile($toPdf, "./files/norge/".$filename.$kort.".pdf");
            print_R($res);
            echo  "<a href='https://system.gavefabrikken.dk/gavefabrikken_backend/files/norge/".$filename.$kort.".pdf'  target='_blank'>Download</a>";
            echo "<br><br>";
            echo "https://system.gavefabrikken.dk/gavefabrikken_backend/files/norge/".$filename.$kort.".pdf";
        }
        catch(\Pdfcrowd\Error $why)
        {
            // report the error
            echo error_log("Pdfcrowd Error: {$why}\n");

            // rethrow or handle the exception
            echo "fejl";
            response::success(json_encode([]));

        }

    }
    public function norgeLink(){
        echo  "<a href='https://system.gavefabrikken.dk/gavefabrikken_backend/files/norge/".$filename.".pdf'  target='_blank'>Download</a>";
    }





    public function make()
    {
        //$presentModels = Presentmodel::all( array(
        //  'conditions' => array(' present_id = ? and language_id = ? ',$presentId,1)));
        $shopID = $_POST["shopID"];
        $languageID = $_POST["languageID"];

        $papervalgHelper = new papervalgHelper;
        $shopName =   $papervalgHelper->getShop($shopID)[0]["name"];




        $presentlist = Present::find_by_sql("SELECT
present.id,  
present.logo,
present.alias
FROM `present` 
inner join shop_present on present.id = shop_present.present_id
WHERE 
present.`shop_id` = ".$shopID." and

present.deleted = 0 and
shop_present.active = 1 and
shop_present.shop_id =  ".$shopID."  

order by shop_present.index_

");



        $medialist = Media::find_by_sql("SELECT 
present.id,
present_media.media_path,
present_media.index

FROM `present` 
inner join present_media on present_media.present_id = present.id

WHERE 
present.`shop_id` =  ".$shopID."  and

present.deleted = 0 order by present.id, present_media.index");
        $returnData = [
            "presentlist" => $presentlist,
            "medialist" => $medialist
        ];
        $papervalgHelper->medialist = $medialist;
        /* ***** template  ****** */
        $titleExtra = " - Julegavevalg ";
        if($shopID == "4075" ){
            $titleExtra = " - Christmas gift ";
        }


        $html = '<body><div class="wrapper"><center><div class="head"><div id="shop-name">'.$shopName.' </div></div><div class="ele-list">';

        if($shopID == "3117"){
            $html = '<body><div class="wrapper"><center><div class="head"><div id="shop-name">METRO THERM - Julegavevalg '.date("Y").' <br><b style="font-size: 20px">Bemærk afleveres til Pia Kolind senest mandag d. 24/10 2022</b></div></div><div class="ele-list">';
        }


//$res = $papervalgHelper->findImg("70123");

        $pageCounter = 0;
        $counter = 0;
        $presentCounter = 0;
        foreach ($presentlist as $ele){

            // model
            $modelHtml = "";
            $modelList = $papervalgHelper->getModels($ele->attributes["id"]);
            if(is_array($modelList)){
                if(sizeof($modelList) > 1){
                    foreach ($modelList as $item) {
                        $modelHtml.=' <div class="ele-checkout-box"></div> <span>'.$item["model_no"].'</span>';
                    }

                }
            }



            $sidebreak = "";
            if($pageCounter == 15){
                $sidebreak ='<div style="page-break-before:always"></div>';
                $pageCounter=0;
            }
            if($counter == 3){
                $html.='</div>'.$sidebreak.'<div class="ele-list">';
                $counter = 0;
            }
            $counter++;
            $pageCounter++;
            $presentCounter++;

            $html.='
    <div class="ele-border">
        <div  style="background-image: url('.$this->serverUrl.$papervalgHelper->findImg($ele->attributes["id"]).'.jpg)" class="ele-img">
            <div class="ele-logo"></div>
        </div>
        <div class="ele-options">
            <div class="ele-option-title" ><div style="margin-right: 5px;"><b>'.$presentCounter.'. </b></div><div> '.$papervalgHelper->getCaption($ele->attributes["id"]).'</div></div>
            <div class="ele-option">'.$modelHtml.'</div>
        </div>
    </div>
';






        }

        $txt_navn = "Navn: ";
        $txt_gavevalg = "Gavevalg: ";
        $txt_nr = "L&oslash;nnummer: ";
        if($shopID == "4075" ){
            $txt_navn = "Name: ";
            $txt_gavevalg = "Gift selection: ";
            $txt_nr = "Salary no: ";
        }


        $html.='</div>
   <br>
    <table width=780 border=0>
    <tr>
    <td >'.$txt_navn.'<span style="width: 100px">______________________ </span></td>
    <td>'.$txt_gavevalg.' <span style="width: 100px">______________________ </span>  </td>';
        if($shopID != "3822" && $shopID != "3674") {
            $html.='<td>'.$txt_nr.'<span style="width: 100px">______________________ </span></td>';
        }
        $html.='
  
    </tr>

    </table>
      <br>
</center></div></body></html>';
//print_R($presentlist);
        $outputHtml ='
  <!DOCTYPE HTML>

<html>

<head>
  <title>Papirvalg</title>
  
<style>
@import url("https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&display=swap");
    body,html{
        padding:0px;
    margin:0px;
   font-family: "Source Sans Pro", sans-serif;

}
.new-page{
page-break-after: always;
}
.wrapper{

        width:1000px;



}
.ele-border{


        padding: 10px;
  height: 240px;
  width:  320px;

 display: table-cell

}
.ele-img{


        background-repeat: no-repeat;
  background-size: contain;
  background-position: center;
  height: 172px;
}


.ele-list{
        width: 100%;
        display: table-row;

    }
.ele-main{
        text-align: center;
  width: 100%;

}
.ele-checkout-box{
        height: 15px;
  width: 15px;
  display: inline-block;

  margin-left: 10px;
  background-color: #A9A9A9;
}
.ele-options{
        width: 100%;
        height: 60px;

}

.ele-border{
        border:1px solid #B7B7B7;
}

.ele-option {
        width: 100%;
        height: 24px;
  font-size: 11px;
  padding-top:4px;
}
.ele-option-title{
 display: flex;
 text-align: left;
        width: 300px;
        height: 35px;
  font-size: 14px;
  padding-top:4px;
}
.ele-option-title p{
        margin:0px;
    padding: 0px;
    
}

.ele-option > * {
        vertical-align: middle;
    line-height: normal;
}
.head{
        height: 100px;
  width: 100%;
  background-image: url(https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/icon/papir_topbanner.jpg);
  background-repeat: no-repeat;
  background-size: cover;

}
#shop-name{
    position: absolute ;
    left: 50px;
    font-size: 2em;
    top: 35px;
    
}
.logo{
        float: right;
        margin-top:8px;
    margin-right:8px;
    height: 60px;
}

   



</style>

</head>';


        if(strlen($shopName > 25)){
            $outputHtml.= "<style> #shop-name{ font-size: 1.7em !important;font-weight: bold; } </style>";
        }
        $outputHtml.=$html;



        /*
         $html.='
            <div class="ele-list">
                <div class="ele-border">
                <div class="ele-img">
                    <div class="ele-logo"></div>
                </div>
                <div class="ele-options">
                    <div class="ele-option"><p>sdfsd</p></div>
                    <div class="ele-option"><p>sdfsasdfasdd</p></div>

                </div>

            </div>
        ';
        */

        if($shopID == "3117222"){
            echo $outputHtml;
            die("asdf");
        }


        $filename = "papirvalg_".$papervalgHelper->generateRandomString(15);
        if (strlen($outputHtml) > 50000){
            die("to long");
        }

        $returnData = [
            "filename" => $filename
        ];

        try
        {
            // create the API client instance
            $client = new \Pdfcrowd\HtmlToPdfClient("bundy0909", "c16c892a0e8a507b747419350431df64");

            // run the conversion and write the result to a file
            $client->convertStringToFile($outputHtml, "./files/papirvalg/".$filename.".pdf");
            response::success(json_encode($returnData));
        }
        catch(\Pdfcrowd\Error $why)
        {
            // report the error
            error_log("Pdfcrowd Error: {$why}\n");

            // rethrow or handle the exception
            echo "fejl";
            response::success(json_encode([]));
        }




        //  response::success(json_encode($returnData));





    }


}

class papervalgHelper{
    public $medialist;

    public function findImg($presentID){
        foreach ($this->medialist as $media){
            if($media->attributes["id"] == $presentID ){
                return $media->attributes["media_path"];
            }
        }
    }
    public function getCaption($presentID){

        $sql = "select caption,caption_paper from present_description where present_id = ".$presentID." and language_id = 1";
        $res = Dbsqli::getSql2($sql);
        if($res[0]["caption_paper"] == "" || $res[0]["caption_paper"] == "###"){
            return $res[0]["caption"];
        } else {
            return $res[0]["caption_paper"];
        }

    }
    public function getModels($presentID){

        $sql = "select * from present_model where present_id = ".$presentID." and language_id = 1 order by fullalias  ";
        return Dbsqli::getSql2($sql);

    }
    public function getShop($shopID){
        $sql = "select * from shop where id = ".$shopID;
        return Dbsqli::getSql2($sql);
    }
    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}


?>


