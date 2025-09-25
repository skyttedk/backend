<?php
    $uname = $_POST["uname"];
    $psw = $_POST["psw"];


    $uname = strtolower($uname);
    if($uname == "salg" && $psw =="gf1308"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Salg&userId=1&lang=1");
        die();
    } else if($uname == "torben" && $psw =="gave1234"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Torben&userId=2&lang=1");
        die();
    } else  if($uname == "ulle" && $psw =="funckey"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Ulrich&userId=3&lang=1");
        die();

    } else if($uname == "cat" && $psw =="gave1234"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Catharina&userId=4&lang=1");
        die();
    } else  if($uname == "rikke" && $psw =="gave2412"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Rikke&userId=5&lang=1");
        die();

    }  else if(strtolower($uname) == "te" && $psw ==strtolower("finegaver2412")){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Trine&userId=6&lang=1");
        die();

    } else if($uname == "no" && $psw =="no"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Norge&userId=7&lang=4");
        die();

    }  else if($uname == "ps" && $psw =="finegaver100"){
    //    header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Pia&userId=8&lang=4");
        die();

    }  else if($uname == "elm" && $psw =="julercool5678"){
 //       header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Ellen&userId=9&lang=4");
        die();

    }  else if($uname == "kristin" && $psw =="julercool1234"){
  //      header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Kristin&userId=10&lang=4");
        die();

    }  else if($uname == "lt" && $psw =="julercool2345"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Lena&userId=11&lang=4");
        die();

    }   else if($uname == "marie" && $psw =="finegaver0987"){
     //   header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Marie-Louise&userId=14&lang=4");
        die();

    }  else if($uname == "bmo" && $psw =="finegaverbianca1"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Bianca&userId=15&lang=1");
        die();

    }  else if($uname == "km" && $psw =="finegaverkirsten2"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Kirsten&userId=16&lang=1");
        die();

    }  else if($uname == "leg" && $psw =="finegaverlasse3"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Lasse&userId=17&lang=1");
        die();

    }  else if($uname == "bje" && $psw =="finegaverbirgitte17"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Lasse&userId=174&lang=1");
        die();

    }else  if($uname == "mm" && $psw =="finegavermette4"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Mette&userId=18&lang=1");
        die();

    }  else  if($uname == "te" && $psw =="finegavertrine5"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Trine&userId=19&lang=1");
        die();

    }  else  if($uname == "mvo" && $psw =="finegavermartin6"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Martin&userId=20&lang=1");
        die();

    }  else  if($uname == "sg" && $psw =="finegaversusanne7"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Susanne&userId=21&lang=1");
        die();

    }  else  if($uname == "ldw" && $psw =="velkommentilbagelone"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Lone&userId=22&lang=1");
        die();

    }  else  if($uname == "mw" && $psw =="finegavermiriam9"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Miriam&userId=231&lang=1");
        die();

    }  else  if($uname == "pt" && $psw =="finegaverpia10"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Pia&userId=23&lang=1");
        die();

    }  else  if(strtolower($uname) == "vzs" && $psw =="finegaver105"){  // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Veronika&userId=232&lang=4");
        die();

    }  else  if($uname == "mas" && $psw =="finegaver102"){
//        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Margrethe&userId=233&lang=4");
        die();

    }  else  if($uname == "th" && $psw =="julercool101"){
    //    header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Tore Holtet&userId=234&lang=4");
        die();

    } else  if($uname == "pm" && $psw =="finegaver400"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Per&userId=400&lang=4");
        die();

 /*   }  else  if($uname == "sk" && $psw =="finegaver405"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Slaman&userId=401&lang=4");
        die();
*/
    }  else  if($uname == "jl" && $psw =="finegaver101"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Jeanette&userId=235&lang=4");
        die();

    }
    else  if($uname == "kt" && $psw =="finegaverkristina100"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Kristina&userId=177&lang=1");
        die();

    }
    else  if($uname == "gp" && $psw =="finegaver123"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Gritt&userId=1000&lang=1");
        die();
    }
    else  if($uname == "tg" && $psw =="finegaver5544"){
   //     header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Trygve&userId=1001&lang=4");
        die();
    }
    else  if($uname == "ghs" && $psw =="finegaver290"){
   //     header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Gyri&userId=1002&lang=4");
        die();
    }
    else  if($uname == "lsp" && $psw =="gaverisverige"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Lizette&userId=1003&lang=1");
        die();
    }
    else  if($uname == "ap" && $psw =="finegaveranders145"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Anders&userId=1004&lang=1");
        die();
    }
    else  if($uname == "tk" && $psw =="finegavertrine10"){
    //    header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Trine&userId=1005&lang=4");
        die();
    }
    else  if($uname == "at" && $psw =="finegaver2604"){ // okay
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Annett&userId=1006&lang=4");
        die();
    }

    else  if($uname == "mhb" && $psw =="finegaver645"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Marlene&userId=1007&lang=1");
        die();
    }

    else  if($uname == "drg" && $psw =="finegaver546"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Dorte&userId=1008&lang=1");
        die();
    }

    else  if($uname == "cjn" && $psw =="finegaver697"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Christina&userId=10010&lang=1");
        die();
    }
    else  if(strtolower($uname)== "jm" && strtolower($psw) =="finegaver537"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Jorunn&userId=1009&lang=4");
        die();
    }
    else  if(strtolower($uname)== "salma" && strtolower($psw) =="salmasgaver565"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Salma&userId=1010&lang=1");
        die();
    }
    else  if(strtolower($uname)== "mg" && strtolower($psw) =="marcusgaver258"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Marcus&userId=1011&lang=4");
        die();
    }
    else  if(strtolower($uname)== "cfl" && strtolower($psw) =="flood156"){
   //     header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Caroline&userId=1012&lang=4");
        die();
    }
    else  if(strtolower($uname)== "ve" && strtolower($psw) =="cooljul555"){
 //       header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Vegard&userId=1013&lang=4");
        die();
    }

    else  if(strtolower($uname)== "jlk" && strtolower($psw) =="jette0302"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Jette&userId=20221&lang=1");
        die();
    }
    else  if(strtolower($uname)== "ana" && strtolower($psw) =="ana0404"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Anette&userId=20222&lang=1");
        die();
    }
    else  if(strtolower($uname)== "spe" && strtolower($psw) =="spe0404"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Stine&userId=20223&lang=1");
        die();
    }
    else  if(strtolower($uname)== "ajo" && strtolower($psw) =="ajo0404"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Anne-Marie&userId=20224&lang=1");
        die();
    }
    else  if(strtolower($uname)== "cjj" && strtolower($psw) =="cathinka123"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Cathinka&userId=20225&lang=4");
        die();
    }
    else  if(strtolower($uname)== "jal" && strtolower($psw) =="jacob123"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Jacob&userId=20226&lang=1");
        die();
    }
    else  if(strtolower($uname)== "jhc" && strtolower($psw) =="jhcchj357"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Joan&userId=20226&lang=1");
        die();
    }
    else  if(strtolower($uname)== "cs" && strtolower($psw) =="csjul5278"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Camilla&userId=20227&lang=1");
        die();
    }

    else  if(strtolower($uname)== "sv2022" && strtolower($psw) =="finegaver2022"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Sverige&userId=20230&lang=1");
        die();
    }
    else  if(strtolower($uname)== "wt" && strtolower($psw) =="flood357"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Wenche&userId=20231&lang=4");
        die();
    }

    else  if(strtolower($uname)== "dla" && strtolower($psw) =="ditte135"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Ditte&userId=20232&lang=1");
        die();
    }



    else  if(strtolower($uname)== "nato" && $psw =="D85Em6"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Nadalin&userId=20240&lang=4");
        die();
    }
    else  if(strtolower($uname)== "armu" && $psw =="VzNLka"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Armend&userId=20241&lang=4");
        die();
    }
    else  if(strtolower($uname)== "lljo" && $psw =="TSaLxS"){ //ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Lill&userId=20242&lang=4");
        die();
    }
    else  if(strtolower($uname)== "arro" && $psw =="zbqMTD"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Arianne&userId=20243&lang=4");
        die();
    }
    else  if(strtolower($uname)== "libj" && $psw =="DyxuWn"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Lisbeth&userId=20244&lang=4");
        die();
    }
    else  if(strtolower($uname)== "bh" && $psw =="DyxuW2"){
 //       header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Brage&userId=20245&lang=4");
        die();
    }
    else  if(strtolower($uname)== "mhm" && $psw =="Dy2uWh"){
 //       header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Mia&userId=20246&lang=4");
        die();
    }

    else  if(strtolower($uname)== "csl" && $psw =="csl153"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Mia&userId=20247&lang=1");
        die();
    }


    else  if(strtolower($uname)== "dummy" && $psw =="dummy"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Mia&userId=20248&lang=4");
        die();
    }
    else  if(strtolower($uname)== "hi" && $psw =="L2hPxeKn4"){ // ok
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=aasa&userId=20250&lang=4");
        die();
    }
// sverige -----
    else  if(strtolower($uname)== "vh" && $psw =="vh5294"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Venessa&userId=30001&lang=1");
        die();
    }
    else  if(strtolower($uname)== "saj" && $psw =="saj5472"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=aasa&userId=30002&lang=1");
        die();
    }
    else  if(strtolower($uname)== "maa" && $psw =="maa3594"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Maria&userId=30003&lang=1");
        die();
    }
    else  if(strtolower($uname)== "bet" && $psw =="bet2891"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=bettina&userId=30004&lang=1");
        die();
    }
    else  if(strtolower($uname)== "anz" && $psw =="anz4567"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Antigona&userId=30005&lang=1");
        die();
    }
    else  if(strtolower($uname)== "fta" && $psw =="fta4562"){
        header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?token=lksdfjh8794ty378fgsoi437tyrf809g4&user=Farid&userId=30006&lang=1");
        die();
    }







// -------------














    else {
       header("Location: ".GFConfig::BACKEND_URL."module/saleportal/index.php?login=no");
       die();
    }



?>